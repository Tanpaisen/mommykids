<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignType;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::query()
            ->with('campaignType')
            ->withCount('products');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $now = now();

        if ($request->filled('status')) {
            match ($request->input('status')) {
                'active' => $query
                    ->where('is_active', true)
                    ->where(function ($q) use ($now) {
                        $q->whereNull('starts_at')
                            ->orWhere('starts_at', '<=', $now);
                    })
                    ->where(function ($q) use ($now) {
                        $q->whereNull('ends_at')
                            ->orWhere('ends_at', '>=', $now);
                    }),

                'scheduled' => $query
                    ->where('is_active', true)
                    ->whereNotNull('starts_at')
                    ->where('starts_at', '>', $now),

                'ended' => $query
                    ->whereNotNull('ends_at')
                    ->where('ends_at', '<', $now),

                'inactive' => $query
                    ->where('is_active', false),

                default => null,
            };
        }

        $campaigns = $query
            ->orderByDesc('priority')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.campaigns.index', [
            'campaigns' => $campaigns,

            // Giữ đúng biến $types mà index hiện tại đang dùng.
            // Nhưng dữ liệu lấy từ DB, không còn const fix cứng.
            'types' => CampaignType::query()
                ->active()
                ->orderBy('name')
                ->pluck('name', 'code')
                ->all(),

            'trashCount' => Campaign::onlyTrashed()->count(),
        ]);
    }

    /**
     * Tìm sản phẩm theo nhu cầu.
     * Tối ưu cho TiDB Cloud: 1 query, tối đa 10 kết quả.
     */
    public function searchProducts(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        if (mb_strlen($search) < 2) {
            return response()->json([
                'data' => [],
            ]);
        }

        $products = Product::query()
            ->leftJoin(
                'categories',
                'products.category_id',
                '=',
                'categories.id'
            )
            ->where('products.is_active', true)
            ->whereNull('products.deleted_at')
            ->where(function ($query) use ($search) {
                $query
                    ->where(
                        'products.name',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'products.slug',
                        'like',
                        '%' . $search . '%'
                    );
            })
            ->orderBy('products.name')
            ->limit(10)
            ->get([
                'products.id',
                'products.name',
                'products.price',
                'products.stock',
                'products.image',
                'categories.name as category_name',
            ])
            ->map(function ($product) {
                $imageUrl = null;

                if ($product->image) {
                    $imageUrl = str_starts_with(
                        $product->image,
                        'http'
                    )
                        ? $product->image
                        : asset('storage/' . $product->image);
                }

                return [
                    'id' => (int) $product->id,
                    'name' => $product->name,
                    'price' => (int) $product->price,
                    'stock' => (int) $product->stock,
                    'image_url' => $imageUrl,
                    'category' => $product->category_name,
                ];
            })
            ->values();

        return response()->json([
            'data' => $products,
        ]);
    }

    public function create()
    {
        $submissionToken = (string) Str::uuid();

        session([
            'campaign_submission_token' => $submissionToken,
        ]);

        return view('admin.campaigns.create', [
            'campaign' => new Campaign([
                'is_active' => true,
                'allow_voucher' => true,
                'priority' => 0,
            ]),

            'campaignTypes' => CampaignType::query()
                ->active()
                ->orderBy('name')
                ->get(),

            'selectedProductRows' =>
                $this->selectedProductRows(),

            'submissionToken' => $submissionToken,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateCampaign($request);

        $request->validate([
            'submission_token' => [
                'required',
                'string',
            ],
        ]);

        $expectedToken = session(
            'campaign_submission_token'
        );

        if (
            !$expectedToken
            || !hash_equals(
                (string) $expectedToken,
                (string) $request->input(
                    'submission_token'
                )
            )
        ) {
            return redirect()
                ->route('admin.campaigns.index')
                ->with(
                    'warning',
                    'Yêu cầu tạo chiến dịch này đã được xử lý trước đó.'
                );
        }

        /*
         * Chỉ consume token sau khi validation đã PASS.
         * Request POST thứ hai dùng cùng form sẽ không thể tạo thêm bản ghi.
         */
        session()->forget(
            'campaign_submission_token'
        );

        DB::transaction(function () use ($validated) {
            $campaignType = $this->resolveCampaignType(
                $validated['campaign_type_name']
            );

            $campaign = Campaign::create(
                $this->campaignAttributes(
                    $validated,
                    $campaignType
                )
            );

            $campaign->products()->sync(
                $this->pivotPayload($validated['products'])
            );
        });

        return redirect()
            ->route('admin.campaigns.index')
            ->with(
                'success',
                'Tạo chiến dịch khuyến mãi thành công.'
            );
    }

    public function edit(Campaign $campaign)
    {
        $campaign->load([
            'campaignType',
            'products.category',
        ]);

        return view('admin.campaigns.edit', [
            'campaign' => $campaign,

            'campaignTypes' => CampaignType::query()
                ->active()
                ->orderBy('name')
                ->get(),

            'selectedProductRows' =>
                $this->selectedProductRows($campaign),
        ]);
    }

    public function update(
        Request $request,
        Campaign $campaign
    ) {
        $validated = $this->validateCampaign($request);

        DB::transaction(function () use (
            $campaign,
            $validated
        ) {
            $campaignType = $this->resolveCampaignType(
                $validated['campaign_type_name']
            );

            $campaign->update(
                $this->campaignAttributes(
                    $validated,
                    $campaignType
                )
            );

            $campaign->products()->sync(
                $this->pivotPayload($validated['products'])
            );
        });

        return redirect()
            ->route('admin.campaigns.index')
            ->with(
                'success',
                'Cập nhật chiến dịch thành công.'
            );
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return redirect()
            ->route('admin.campaigns.index')
            ->with(
                'success',
                'Đã chuyển chiến dịch vào thùng rác.'
            );
    }

    public function trash(Request $request)
    {
        $query = Campaign::onlyTrashed()
            ->with('campaignType')
            ->withCount('products');

        if ($request->filled('search')) {
            $search = trim(
                (string) $request->input('search')
            );

            $query->where(
                'name',
                'like',
                '%' . $search . '%'
            );
        }

        return view('admin.campaigns.trash', [
            'campaigns' => $query
                ->orderByDesc('deleted_at')
                ->paginate(10)
                ->withQueryString(),

            // Giữ tương thích trash.blade.php hiện tại.
            'types' => CampaignType::query()
                ->orderBy('name')
                ->pluck('name', 'code')
                ->all(),
        ]);
    }

    public function restore(string $id)
    {
        $campaign = Campaign::onlyTrashed()
            ->findOrFail($id);

        $campaign->restore();

        return redirect()
            ->route('admin.campaigns.trash')
            ->with(
                'success',
                'Khôi phục chiến dịch thành công.'
            );
    }

    public function forceDelete(string $id)
    {
        $campaign = Campaign::onlyTrashed()
            ->findOrFail($id);

        $campaign->forceDelete();

        return redirect()
            ->route('admin.campaigns.trash')
            ->with(
                'success',
                'Đã xóa vĩnh viễn chiến dịch.'
            );
    }

    private function validateCampaign(
        Request $request
    ): array {
        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'campaign_type_name' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'description' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],

                'starts_at' => [
                    'nullable',
                    'date',
                ],

                'ends_at' => [
                    'nullable',
                    'date',
                    'after:starts_at',
                ],

                'priority' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:1000000',
                ],

                'is_active' => [
                    'nullable',
                    'boolean',
                ],

                'products' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'products.*.product_id' => [
                    'required',
                    'integer',
                    'distinct',
                    'exists:products,id',
                ],

                'products.*.discount_method' => [
                    'required',
                    Rule::in([
                        'fixed',
                        'percent',
                    ]),
                ],

                'products.*.discount_value' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'products.*.stock_limit' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],

                'products.*.max_per_user' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
            ],
            [
                'products.required' =>
                    'Vui lòng chọn ít nhất một sản phẩm.',

                'products.min' =>
                    'Vui lòng chọn ít nhất một sản phẩm.',

                'ends_at.after' =>
                    'Thời gian kết thúc phải sau thời gian bắt đầu.',
            ]
        );

        foreach ($validated['products'] as $row) {
            $product = Product::find(
                $row['product_id']
            );

            if (!$product) {
                continue;
            }

            $method = $row['discount_method'];
            $value = (int) $row['discount_value'];

            if (
                $method === 'percent'
                && ($value < 1 || $value > 100)
            ) {
                throw ValidationException::withMessages([
                    'products' =>
                        "% giảm của {$product->name} phải từ 1 đến 100.",
                ]);
            }

            if (
                $method === 'fixed'
                && $value > (int) $product->price
            ) {
                throw ValidationException::withMessages([
                    'products' =>
                        "Giá khuyến mãi của {$product->name} không được cao hơn giá hiện tại.",
                ]);
            }
        }

        return $validated;
    }

    private function resolveCampaignType(
        string $name
    ): CampaignType {
        $name = trim($name);
        $code = Str::slug($name);

        if ($code === '') {
            throw ValidationException::withMessages([
                'campaign_type_name' =>
                    'Loại chiến dịch không hợp lệ.',
            ]);
        }

        $type = CampaignType::query()
            ->where('name', $name)
            ->orWhere('code', $code)
            ->first();

        if (!$type) {
            $type = CampaignType::create([
                'name' => $name,
                'code' => $code,
                'is_active' => true,
            ]);
        } elseif (!$type->is_active) {
            $type->update([
                'is_active' => true,
            ]);
        }

        return $type;
    }

    /**
     * Campaign và Voucher luôn độc lập.
     * allow_voucher được giữ để tương thích schema cũ,
     * nhưng hệ thống luôn lưu true.
     */
    private function campaignAttributes(
        array $validated,
        CampaignType $campaignType
    ): array {
        return [
            'name' => $validated['name'],
            'campaign_type_id' =>
                $campaignType->id,

            // Legacy / snapshot compatibility.
            'type' => $campaignType->code,

            'description' =>
                $validated['description'] ?? null,

            'starts_at' =>
                $validated['starts_at'] ?? null,

            'ends_at' =>
                $validated['ends_at'] ?? null,

            'is_active' =>
                (bool) ($validated['is_active'] ?? false),

            'allow_voucher' => true,

            'priority' =>
                (int) $validated['priority'],

            'config' => null,
        ];
    }

    private function pivotPayload(
        array $products
    ): array {
        $payload = [];

        foreach ($products as $row) {
            $method = $row['discount_method'];
            $value = (int) $row['discount_value'];

            $payload[(int) $row['product_id']] = [
                'sale_price' =>
                    $method === 'fixed'
                        ? $value
                        : null,

                'discount_percent' =>
                    $method === 'percent'
                        ? $value
                        : null,

                'stock_limit' =>
                    isset($row['stock_limit'])
                    && $row['stock_limit'] !== ''
                        ? (int) $row['stock_limit']
                        : null,

                'max_per_user' =>
                    isset($row['max_per_user'])
                    && $row['max_per_user'] !== ''
                        ? (int) $row['max_per_user']
                        : null,
            ];
        }

        return $payload;
    }

    /**
     * Chỉ đưa sản phẩm đã chọn vào form.
     * Khi validation lỗi thì khôi phục old input.
     */
    private function selectedProductRows(
        ?Campaign $campaign = null
    ) {
        $oldRows = collect(
            session()->getOldInput('products', [])
        );

        if ($oldRows->isNotEmpty()) {
            $ids = $oldRows
                ->pluck('product_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->values();

            $products = Product::query()
                ->whereIn('id', $ids)
                ->with('category:id,name')
                ->get()
                ->keyBy('id');

            return $oldRows
                ->map(function ($row) use ($products) {
                    $product = $products->get(
                        (int) ($row['product_id'] ?? 0)
                    );

                    if (!$product) {
                        return null;
                    }

                    return $this->productFormRow(
                        $product,
                        $row
                    );
                })
                ->filter()
                ->values();
        }

        if (!$campaign) {
            return collect();
        }

        return $campaign->products
            ->map(function (Product $product) {
                $method =
                    $product->pivot->sale_price !== null
                        ? 'fixed'
                        : 'percent';

                $value =
                    $method === 'fixed'
                        ? $product->pivot->sale_price
                        : $product->pivot->discount_percent;

                return $this->productFormRow(
                    $product,
                    [
                        'discount_method' => $method,
                        'discount_value' => $value,
                        'stock_limit' =>
                            $product->pivot->stock_limit,
                        'max_per_user' =>
                            $product->pivot->max_per_user,
                    ]
                );
            })
            ->values();
    }

    private function productFormRow(
        Product $product,
        array $values = []
    ): array {
        $imageUrl = null;

        if ($product->image) {
            $imageUrl = str_starts_with(
                $product->image,
                'http'
            )
                ? $product->image
                : asset(
                    'storage/' . $product->image
                );
        }

        /*
         * Tương thích old input của form V3 nếu validation
         * xảy ra ngay sau khi user vừa chuyển code.
         */
        $method = $values['discount_method'] ?? null;
        $value = $values['discount_value'] ?? null;

        if (!$method) {
            if (
                array_key_exists('sale_price', $values)
                && $values['sale_price'] !== null
                && $values['sale_price'] !== ''
            ) {
                $method = 'fixed';
                $value = $values['sale_price'];
            } else {
                $method = 'percent';
                $value =
                    $values['discount_percent'] ?? null;
            }
        }

        return [
            'id' => (int) $product->id,
            'name' => $product->name,
            'price' => (int) $product->price,
            'stock' => (int) $product->stock,
            'image_url' => $imageUrl,
            'category' => $product->category?->name,

            'discount_method' => $method,
            'discount_value' => $value,

            'stock_limit' =>
                $values['stock_limit'] ?? null,

            'max_per_user' =>
                $values['max_per_user'] ?? null,
        ];
    }
}
