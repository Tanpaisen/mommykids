<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stage;
use App\Models\Tag;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Illuminate\Support\Facades;
use App\Services;

class ProductController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Danh sách sản phẩm
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        /*
         * Product sử dụng SoftDeletes nên Product::query()
         * tự động loại các bản ghi deleted_at != NULL.
         */
        $query = Product::query()
            ->with('category');

        /*
         * Tìm kiếm.
         */
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where(
                    'products.name',
                    'like',
                    '%' . $search . '%'
                )
                    ->orWhere(
                        'products.slug',
                        'like',
                        '%' . $search . '%'
                    );
            });
        }

        /*
         * Lọc danh mục.
         */
        if ($request->filled('category_id')) {
            $query->where(
                'products.category_id',
                $request->category_id
            );
        }

        /*
         * Lọc trạng thái.
         */
        if ($request->status === 'active') {
            $query->where(
                'products.is_active',
                true
            );
        }

        if ($request->status === 'inactive') {
            $query->where(
                'products.is_active',
                false
            );
        }

        /*
         * Lọc sản phẩm nổi bật.
         */
        if ($request->status === 'featured') {
            $query->where(
                'products.is_featured',
                true
            );
        }

        /*
         * Sắp hết hàng.
         */
        if ($request->boolean('low_stock')) {
            $query->where(
                'products.stock',
                '<=',
                10
            );
        }

        /*
         * Phân trang.
         */
        $products = $query
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.*')
            ->orderBy('categories.sort_order', 'asc')
            ->orderBy('categories.name', 'asc')
            ->orderBy('products.name', 'asc')
            ->paginate(10)
            ->withQueryString();
        /*
         * Danh mục cho filter.
         */
        $categories = Category::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        /*
         * Số sản phẩm trong thùng rác.
         */
        $trashCount = Product::onlyTrashed()
            ->count();

        return view(
            'admin.products.index',
            compact(
                'products',
                'categories',
                'trashCount'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Form thêm sản phẩm
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $categories = Category::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $stages = Stage::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $tags = Tag::orderBy('type')
            ->orderBy('name')
            ->get();

        return view(
            'admin.products.create',
            compact(
                'categories',
                'stages',
                'tags'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Lưu sản phẩm mới
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->rules(),
            $this->messages()
        );

        $validated['highlights'] =
            $this->normalizeHighlights(
                $validated['highlights'] ?? null
            );

        /*
         * Tạo slug.
         */
        $validated['slug'] = $this->makeSlug(
            $validated['slug'] ?? null,
            $validated['name']
        );

        /*
         * Kiểm tra slug cả trong thùng rác.
         */
        if (
            Product::withTrashed()
                ->where(
                    'slug',
                    $validated['slug']
                )
                ->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'slug' =>
                        'Slug sản phẩm đã tồn tại.',
                ]);
        }

        /*
         * Checkbox trạng thái.
         */
        $validated['is_active'] =
            $request->boolean('is_active');

        $validated['is_featured'] =
            $request->boolean('is_featured');

        /*
        |--------------------------------------------------------------------------
        | Ảnh đại diện → Cloudinary
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {
            $validated['image'] =
                $this->uploadToCloudinary(
                    $request->file('image'),
                    'mommykids/products/main'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Gallery → Cloudinary
        |--------------------------------------------------------------------------
        */

        $gallery = [];

        if ($request->hasFile('images')) {
            foreach (
                $request->file('images')
                as $file
            ) {
                $gallery[] =
                    $this->uploadToCloudinary(
                        $file,
                        'mommykids/products/gallery'
                    );
            }
        }

        $validated['images'] = $gallery;

        $validated['sku'] =$request->sku ?: strtoupper(\Str::random(8));

        
        $initialStock = (int) ($validated['stock'] ?? 0);
        $validated['stock'] = 0;

        /*
         * Không đưa các field phụ vào Product::create().
         */
        unset(
            $validated['stage_ids'],
            $validated['tag_ids'],
            $validated['remove_image'],
            $validated['remove_gallery']
        );

        DB::transaction(function () use ($validated, $request, $initialStock) {
            /*
            * Tạo sản phẩm.
            */
            $product = Product::create(
                $validated
            );

            /*
            * Đồng bộ stage.
            */
            $product->stages()->sync(
                $request->input(
                    'stage_ids',
                    []
                )
            );

            /*
            * Đồng bộ tag.
            */
            $product->tags()->sync(
                $request->input(
                    'tag_ids',
                    []
                )
            );

            if ($initialStock > 0) {
                app(InventoryService::class)->import(
                    $product, 
                    $initialStock, 
                    null, 
                    'Nhập kho ban đầu khi tạo sản phẩm'
                );
            }
        });

        return redirect()
            ->route('admin.products.index')
            ->with(
                'success',
                'Thêm sản phẩm thành công.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Form sửa sản phẩm
    |--------------------------------------------------------------------------
    */

    public function edit(Product $product)
    {
        $product->load([
            'category',
            'stages',
            'tags',
        ]);

        $categories = Category::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $stages = Stage::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $tags = Tag::orderBy('type')
            ->orderBy('name')
            ->get();

        return view(
            'admin.products.edit',
            compact(
                'product',
                'categories',
                'stages',
                'tags'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cập nhật sản phẩm
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Product $product
    ) {
        $rules = $this->rules($product->id);

        /*
         * Slug update sẽ tự kiểm tra riêng
         * cả sản phẩm đã soft delete.
         */
        $rules['slug'] = [
            'nullable',
            'string',
            'max:255',
        ];

        $validated = $request->validate(
            $rules,
            $this->messages()
        );

        $validated['highlights'] =
            $this->normalizeHighlights(
                $validated['highlights'] ?? null
            );

        /*
         * Chuẩn hóa slug.
         */
        $validated['slug'] =
            $this->makeSlug(
                $validated['slug'] ?? null,
                $validated['name']
            );

        /*
         * Không cho trùng slug với sản phẩm khác,
         * kể cả sản phẩm đang trong thùng rác.
         */
        $slugExists =
            Product::withTrashed()
                ->where(
                    'slug',
                    $validated['slug']
                )
                ->where(
                    'id',
                    '!=',
                    $product->id
                )
                ->exists();

        if ($slugExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'slug' =>
                        'Slug sản phẩm đã tồn tại.',
                ]);
        }

        /*
         * Checkbox trạng thái.
         */
        $validated['is_active'] =
            $request->boolean('is_active');

        $validated['is_featured'] =
            $request->boolean('is_featured');

        /*
        |--------------------------------------------------------------------------
        | Ảnh đại diện
        |--------------------------------------------------------------------------
        */

        /*
         * Người dùng chủ động xóa ảnh.
         */
        if (
            $request->boolean('remove_image')
        ) {
            $this->deleteStoredImage(
                $product->image
            );

            $validated['image'] = null;
        }

        /*
         * Upload ảnh mới.
         *
         * Upload Cloudinary thành công trước,
         * sau đó mới xóa ảnh cũ để tránh mất ảnh
         * nếu upload thất bại.
         */
        if ($request->hasFile('image')) {
            $newImage =
                $this->uploadToCloudinary(
                    $request->file('image'),
                    'mommykids/products/main'
                );

            /*
             * Xóa ảnh cũ.
             */
            $this->deleteStoredImage(
                $product->image
            );

            /*
             * Lưu URL Cloudinary mới.
             */
            $validated['image'] =
                $newImage;
        }

        /*
        |--------------------------------------------------------------------------
        | Gallery
        |--------------------------------------------------------------------------
        */

        $gallery =
            $product->images ?? [];

        /*
         * Danh sách ảnh muốn xóa.
         */
        $removeGallery =
            $request->input(
                'remove_gallery',
                []
            );

        if (
            is_array($removeGallery)
        ) {
            foreach (
                $removeGallery
                as $imageToRemove
            ) {
                /*
                 * Chỉ xóa nếu ảnh thực sự nằm
                 * trong gallery sản phẩm.
                 */
                if (
                    in_array(
                        $imageToRemove,
                        $gallery,
                        true
                    )
                ) {
                    $this->deleteStoredImage(
                        $imageToRemove
                    );
                }
            }

            /*
             * Loại ảnh đã xóa khỏi array.
             */
            $gallery = array_values(
                array_filter(
                    $gallery,
                    fn ($image) =>
                        !in_array(
                            $image,
                            $removeGallery,
                            true
                        )
                )
            );
        }

        /*
         * Upload thêm gallery mới.
         */
        if (
            $request->hasFile('images')
        ) {
            foreach (
                $request->file('images')
                as $file
            ) {
                $gallery[] =
                    $this->uploadToCloudinary(
                        $file,
                        'mommykids/products/gallery'
                    );
            }
        }

        $validated['images'] =
            $gallery;

        $validated['sku'] =$request->sku ?: strtoupper(\Str::random(8));

        /*
         * Xóa field không thuộc products.
         */
        unset(
            $validated['stage_ids'],
            $validated['tag_ids'],
            $validated['remove_image'],
            $validated['remove_gallery'],
            $validated['stock']
        );

        /*
         * Update database.
         */
        $product->update(
            $validated
        );

        /*
         * Đồng bộ stage.
         */
        $product->stages()->sync(
            $request->input(
                'stage_ids',
                []
            )
        );

        /*
         * Đồng bộ tag.
         */
        $product->tags()->sync(
            $request->input(
                'tag_ids',
                []
            )
        );

        return redirect()
            ->route('admin.products.index')
            ->with(
                'success',
                'Cập nhật sản phẩm thành công.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Xóa mềm
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Product $product
    ) {
        /*
         * Lưu ID người thực hiện.
         *
         * Hiện project của bạn vẫn đang dùng
         * auth web ở một số khu vực admin,
         * nên auth()->id() sẽ lấy user hiện tại.
         */
        $product->update([
            'deleted_by' =>
                auth()->id(),

            /*
             * Nếu sản phẩm từng được restore,
             * xóa lần nữa thì reset thông tin restore.
             */
            'restored_by' => null,
            'restored_at' => null,
        ]);

        /*
         * Soft delete:
         *
         * - chỉ set deleted_at
         * - không xóa ảnh
         * - không detach Stage
         * - không detach Tag
         */
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with(
                'success',
                'Sản phẩm đã được chuyển vào thùng rác.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Thùng rác
    |--------------------------------------------------------------------------
    */

    public function trash(
        Request $request
    ) {
        /*
         * Chỉ lấy sản phẩm đã soft delete.
         */
        $query =
            Product::onlyTrashed()
                ->with('category');

        /*
         * Search.
         */
        if (
            $request->filled('search')
        ) {
            $search =
                trim(
                    $request->search
                );

            $query->where(
                function ($q) use ($search) {
                    $q->where(
                        'name',
                        'like',
                        '%' . $search . '%'
                    )
                        ->orWhere(
                            'slug',
                            'like',
                            '%' . $search . '%'
                        );
                }
            );
        }

        /*
         * Mới xóa hiện trước.
         */
        $products = $query
            ->orderByDesc('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return view(
            'admin.products.trash',
            compact('products')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Khôi phục
    |--------------------------------------------------------------------------
    */

    public function restore(
        string $id
    ) {
        $product =
            Product::onlyTrashed()
                ->findOrFail($id);

        /*
         * Laravel đưa deleted_at về NULL.
         */
        $product->restore();

        /*
         * Ghi lịch sử restore.
         *
         * deleted_by vẫn giữ nguyên để biết
         * trước đó ai đã xóa sản phẩm.
         */
        $product->update([
            'restored_by' =>
                auth()->id(),

            'restored_at' =>
                now(),
        ]);

        return redirect()
            ->route(
                'admin.products.trash'
            )
            ->with(
                'success',
                'Khôi phục sản phẩm thành công.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Xóa vĩnh viễn
    |--------------------------------------------------------------------------
    */

    public function forceDelete(
        string $id
    ) {
        $product =
            Product::onlyTrashed()
                ->findOrFail($id);

        /*
         * Xóa ảnh đại diện.
         *
         * Nếu là Cloudinary → xóa Cloudinary.
         * Nếu là local cũ → xóa storage.
         */
        $this->deleteStoredImage(
            $product->image
        );

        /*
         * Xóa gallery.
         */
        foreach (
            $product->images ?? []
            as $image
        ) {
            $this->deleteStoredImage(
                $image
            );
        }

        /*
         * Xóa quan hệ pivot.
         */
        $product->stages()
            ->detach();

        $product->tags()
            ->detach();

        /*
         * Xóa thật khỏi DB.
         */
        $product->forceDelete();

        return redirect()
            ->route(
                'admin.products.trash'
            )
            ->with(
                'success',
                'Đã xóa vĩnh viễn sản phẩm.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function rules(): array
    {
        return [
            /*
             * Category.
             */
            'category_id' => [
                'required',
                'exists:categories,id',
            ],

            /*
             * Tên.
             */
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            /*
             * Slug.
             */
            'slug' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
             * Mô tả.
             */
            'description' => [
                'nullable',
                'string',
            ],

            /*
             * Nội dung chi tiết sản phẩm.
             */
            'origin' => [
                'nullable',
                'string',
                'max:255',
            ],

            'manufacturer' => [
                'nullable',
                'string',
                'max:255',
            ],

            'ingredients' => [
                'nullable',
                'string',
            ],

            'usage_instructions' => [
                'nullable',
                'string',
            ],

            'storage_instructions' => [
                'nullable',
                'string',
            ],

            'warning' => [
                'nullable',
                'string',
            ],

            /*
             * Các trường định danh và kho
             */
            'sku' => [
                'nullable',
                'string',
                'max:50',
                $productId ? 'unique:products,sku,' . $productId : 'unique:products,sku',
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
            ],
            'cost_price' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'low_stock_alert' => [
                'required',
                'integer',
                'min:0',
            ],

            /*
             * Điểm nổi bật sản phẩm.
             * Lưu dưới dạng JSON trong products.highlights.
             */
            'highlights' => [
                'nullable',
                'array',
            ],

            'highlights.items' => [
                'nullable',
                'array',
                'max:4',
            ],

            'highlights.items.*.title' => [
                'nullable',
                'string',
                'max:100',
            ],

            'highlights.items.*.subtitle' => [
                'nullable',
                'string',
                'max:150',
            ],

            'highlights.items.*.icon' => [
                'nullable',
                'in:shield,brain,digest,heart,bone,eye,check',
            ],

            'highlights.message' => [
                'nullable',
                'string',
                'max:255',
            ],

            'highlights.submessage' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
             * Giá.
             */
            'price' => [
                'required',
                'integer',
                'min:0',
            ],

            /*
             * Giá cũ.
             */
            'old_price' => [
                'nullable',
                'integer',
                'min:0',
            ],

            /*
             * Giảm giá.
             */
            'discount_percent' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],

            /*
             * Tồn kho.
             */
            'stock' => [
                'required',
                'integer',
                'min:0',
            ],

            /*
             * Khối lượng sản phẩm (gram).
             * Dùng để tính tổng khối lượng giỏ hàng và phí ship GHN.
             */
            'weight_grams' => [
                'required',
                'integer',
                'min:1',
            ],

            /*
             * Kích thước đóng gói sản phẩm (cm).
             * Dùng cùng khối lượng để tính phí vận chuyển GHN chính xác hơn.
             */
            'length_cm' => [
                'required',
                'integer',
                'min:1',
            ],

            'width_cm' => [
                'required',
                'integer',
                'min:1',
            ],

            'height_cm' => [
                'required',
                'integer',
                'min:1',
            ],

            /*
             * Status.
             */
            'is_active' => [
                'nullable',
                'boolean',
            ],

            'is_featured' => [
                'nullable',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Ảnh đại diện
            |--------------------------------------------------------------------------
            */

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            /*
            |--------------------------------------------------------------------------
            | Gallery
            |--------------------------------------------------------------------------
            */

            'images' => [
                'nullable',
                'array',
                'max:8',
            ],

            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            /*
             * Remove main image.
             */
            'remove_image' => [
                'nullable',
                'boolean',
            ],

            /*
             * Remove gallery.
             */
            'remove_gallery' => [
                'nullable',
                'array',
            ],

            /*
            |--------------------------------------------------------------------------
            | Stage
            |--------------------------------------------------------------------------
            */

            'stage_ids' => [
                'nullable',
                'array',
            ],

            'stage_ids.*' => [
                'exists:stages,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Tag
            |--------------------------------------------------------------------------
            */

            'tag_ids' => [
                'nullable',
                'array',
            ],

            'tag_ids.*' => [
                'exists:tags,id',
            ],
        ];
    }

    private function messages(): array
    {
        return [
            'category_id.required' =>
                'Vui lòng chọn danh mục.',

            'category_id.exists' =>
                'Danh mục không hợp lệ.',

            'name.required' =>
                'Vui lòng nhập tên sản phẩm.',

            'price.required' =>
                'Vui lòng nhập giá sản phẩm.',

            'price.integer' =>
                'Giá sản phẩm phải là số.',

            'price.min' =>
                'Giá sản phẩm không được nhỏ hơn 0.',

            'old_price.integer' =>
                'Giá cũ phải là số.',

            'old_price.min' =>
                'Giá cũ không được nhỏ hơn 0.',

            'discount_percent.integer' =>
                'Phần trăm giảm phải là số nguyên.',

            'discount_percent.min' =>
                'Phần trăm giảm không được nhỏ hơn 0.',

            'discount_percent.max' =>
                'Phần trăm giảm không được lớn hơn 100.',

            'sku.unique' => 
                'Mã SKU này đã được sử dụng cho một sản phẩm khác.',
            
            'sku.max' => 
                'Mã SKU không được vượt quá 50 ký tự.',
            
            'code.max' => 
                'Mã Barcode không được vượt quá 50 ký tự.',
        
            'cost_price.integer' => 
                'Giá vốn phải là số.',
           
            'cost_price.min' => 
                'Giá vốn không được nhỏ hơn 0.',
           
            'low_stock_alert.required' => 
                'Vui lòng nhập mức cảnh báo sắp hết hàng.',
            
            'low_stock_alert.integer' => 
                'Mức cảnh báo sắp hết hàng phải là số nguyên.',
            
            'low_stock_alert.min' => 
                'Mức cảnh báo sắp hết hàng không được nhỏ hơn 0.',

            'stock.required' =>
                'Vui lòng nhập tồn kho.',

            'stock.integer' =>
                'Tồn kho phải là số nguyên.',

            'stock.min' =>
                'Tồn kho không được nhỏ hơn 0.',

            'weight_grams.required' =>
                'Vui lòng nhập khối lượng sản phẩm.',

            'weight_grams.integer' =>
                'Khối lượng sản phẩm phải là số nguyên.',

            'weight_grams.min' =>
                'Khối lượng sản phẩm phải lớn hơn 0 gram.',

            'length_cm.required' =>
                'Vui lòng nhập chiều dài sản phẩm.',

            'length_cm.integer' =>
                'Chiều dài sản phẩm phải là số nguyên.',

            'length_cm.min' =>
                'Chiều dài sản phẩm phải lớn hơn 0 cm.',

            'width_cm.required' =>
                'Vui lòng nhập chiều rộng sản phẩm.',

            'width_cm.integer' =>
                'Chiều rộng sản phẩm phải là số nguyên.',

            'width_cm.min' =>
                'Chiều rộng sản phẩm phải lớn hơn 0 cm.',

            'height_cm.required' =>
                'Vui lòng nhập chiều cao sản phẩm.',

            'height_cm.integer' =>
                'Chiều cao sản phẩm phải là số nguyên.',

            'height_cm.min' =>
                'Chiều cao sản phẩm phải lớn hơn 0 cm.',

            'highlights.items.max' =>
                'Chỉ được nhập tối đa 4 điểm nổi bật.',

            'highlights.items.*.title.max' =>
                'Tiêu đề điểm nổi bật không được vượt quá 100 ký tự.',

            'highlights.items.*.subtitle.max' =>
                'Nội dung ngắn của điểm nổi bật không được vượt quá 150 ký tự.',

            'highlights.items.*.icon.in' =>
                'Icon điểm nổi bật không hợp lệ.',

            'highlights.message.max' =>
                'Thông điệp nổi bật không được vượt quá 255 ký tự.',

            'highlights.submessage.max' =>
                'Nội dung phụ không được vượt quá 255 ký tự.',

            'image.image' =>
                'Ảnh đại diện không hợp lệ.',

            'image.mimes' =>
                'Ảnh đại diện chỉ nhận JPG, JPEG, PNG hoặc WEBP.',

            'image.max' =>
                'Ảnh đại diện không được lớn hơn 4MB.',

            'images.max' =>
                'Chỉ được tải tối đa 8 ảnh chi tiết.',

            'images.*.image' =>
                'Một ảnh chi tiết không hợp lệ.',

            'images.*.mimes' =>
                'Ảnh chi tiết chỉ nhận JPG, JPEG, PNG hoặc WEBP.',

            'images.*.max' =>
                'Mỗi ảnh chi tiết không được lớn hơn 4MB.',
        ];
    }

    private function normalizeHighlights(?array $highlights): ?array
    {
        if (!$highlights) {
            return null;
        }

        $items = collect($highlights['items'] ?? [])
            ->map(function ($item) {
                return [
                    'title' => filled($item['title'] ?? null)
                        ? trim((string) $item['title'])
                        : null,
                    'subtitle' => filled($item['subtitle'] ?? null)
                        ? trim((string) $item['subtitle'])
                        : null,
                    'icon' => filled($item['icon'] ?? null)
                        ? (string) $item['icon']
                        : 'check',
                ];
            })
            ->filter(fn ($item) => filled($item['title']))
            ->take(4)
            ->values()
            ->all();

        $message = filled($highlights['message'] ?? null)
            ? trim((string) $highlights['message'])
            : null;

        $submessage = filled($highlights['submessage'] ?? null)
            ? trim((string) $highlights['submessage'])
            : null;

        if (empty($items) && !$message && !$submessage) {
            return null;
        }

        return [
            'items' => $items,
            'message' => $message,
            'submessage' => $submessage,
        ];
    }


    private function makeSlug(
        ?string $slug,
        string $name
    ): string {
        return Str::slug(
            filled($slug)
                ? $slug
                : $name
        );
    }


    private function cloudinary(): Cloudinary
    {

        $cloudUrl =
            config(
                'cloudinary.cloud_url'
            );

        if (!$cloudUrl) {
            throw new RuntimeException(
                'CLOUDINARY_URL chưa được cấu hình. '
                . 'Kiểm tra file .env và config/cloudinary.php.'
            );
        }

        return new Cloudinary(
            $cloudUrl
        );
    }

    private function uploadToCloudinary(
        UploadedFile $file,
        string $folder
    ): string {

        $result =
            $this->cloudinary()
                ->uploadApi()
                ->upload(
                    $file->getRealPath(),
                    [
    
                        'folder' =>
                            $folder,


                        'resource_type' =>
                            'image',


                        'use_filename' =>
                            true,


                        'unique_filename' =>
                            true,


                        'overwrite' =>
                            false,
                    ]
                );

        $url =
            $result['secure_url']
            ?? null;

        if (!$url) {
            throw new RuntimeException(
                'Cloudinary upload thành công '
                . 'nhưng không trả về secure_url.'
            );
        }

        return $url;
    }


    private function deleteStoredImage(
        ?string $image
    ): void {
        if (!$image) {
            return;
        }

        if (
            $this->isCloudinaryUrl(
                $image
            )
        ) {
            $publicId =
                $this
                    ->extractCloudinaryPublicId(
                        $image
                    );

            if (!$publicId) {
                return;
            }

            try {

                $this->cloudinary()
                    ->uploadApi()
                    ->destroy(
                        $publicId,
                        [
                            'resource_type' =>
                                'image',

                            'invalidate' =>
                                true,
                        ]
                    );
            } catch (\Throwable $e) {

                report($e);
            }

            return;
        }

        if (
            Str::startsWith(
                $image,
                [
                    'http://',
                    'https://',
                ]
            )
        ) {

            return;
        }

        Storage::disk('public')
            ->delete(
                $image
            );
    }

    private function isCloudinaryUrl(
        string $url
    ): bool {
        $host =
            parse_url(
                $url,
                PHP_URL_HOST
            );

        if (!is_string($host)) {
            return false;
        }

        return
            $host ===
                'res.cloudinary.com'
            ||
            Str::endsWith(
                $host,
                '.cloudinary.com'
            );
    }

    private function extractCloudinaryPublicId(
        string $url
    ): ?string {
        $path =
            parse_url(
                $url,
                PHP_URL_PATH
            );

        if (!is_string($path)) {
            return null;
        }

        $marker =
            '/image/upload/';

        $position =
            strpos(
                $path,
                $marker
            );

        if ($position === false) {
            return null;
        }

        $relativePath =
            substr(
                $path,
                $position
                + strlen($marker)
            );

        $relativePath =
            preg_replace(
                '#^v\d+/#',
                '',
                $relativePath
            );

        if (!$relativePath) {
            return null;
        }

        $publicId =
            preg_replace(
                '/\.[^\.\/]+$/',
                '',
                $relativePath
            );

        if (!$publicId) {
            return null;
        }

        return rawurldecode(
            ltrim(
                $publicId,
                '/'
            )
        );
    }

    /**
     * Tìm kiếm sản phẩm theo tên hoặc SKU
     */
    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));

        $products = Product::query()
            ->select('id', 'name')
            ->where('is_active', 1)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    /**
     * Hiển thị form upload CSV
     */
    public function importForm()
    {
        return view('admin.products.import');
    }

    /**
     * Xử lý file CSV upload lên
     */
    public function importStore(\Illuminate\Http\Request $request, \App\Services\InventoryService $inventory)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        
        // Bỏ qua BOM của file Excel CSV UTF-8
        $bom = "\xef\xbb\xbf";
        if (fgets($handle, 4) !== $bom) {
            rewind($handle);
        }
        
        $header = fgetcsv($handle);
        $success = 0;
        $errors = [];
        $rowNumber = 1;

        // Tối ưu: Lấy sẵn danh sách Category ID hợp lệ
        $validCategories = \App\Models\Category::pluck('id')->toArray();

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            
            // 9 cột: name, category_id, sku, code, price, cost_price, stock, weight_grams, low_stock_alert
            [$name, $categoryId, $sku, $code, $price, $costPrice, $stock, $weight, $lowStock] = $row + array_fill(0, 9, null);

            // Validate
            if (empty($name) || empty($categoryId) || empty($price) || empty($sku)) {
                $errors[] = "Dòng $rowNumber: Thiếu tên/danh mục/giá hoặc SKU.";
                continue;
            }
            if (!in_array((int)$categoryId, $validCategories)) {
                $errors[] = "Dòng $rowNumber: Danh mục ID $categoryId không tồn tại.";
                continue;
            }

            try {
                \Illuminate\Support\Facades\DB::beginTransaction();

                // Tạo mới hoặc cập nhật sản phẩm
                $product = \App\Models\Product::updateOrCreate(
                    ['sku' => $sku], 
                    [
                        'name'            => $name,
                        'slug'            => \App\Models\Product::where('sku', $sku)->value('slug') ?? \Str::slug($name) . '-' . time(),
                        'category_id'     => $categoryId,
                        'code'            => $code ?: null,
                        'price'           => $price,
                        'cost_price'      => $costPrice ?: 0,
                        'weight_grams'    => $weight ?: 0,
                        'low_stock_alert' => $lowStock ?: 5,
                        'is_active'       => true,
                    ]
                );

                // Nếu là sản phẩm mới và có số lượng tồn kho > 0 -> Sinh log nhập kho
                if ($product->wasRecentlyCreated && (int)$stock > 0) {
                    $inventory->import($product, (int)$stock, null, "Import hàng loạt CSV (Dòng $rowNumber)");
                }

                DB::commit();
                $success++;
                
            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = "Dòng $rowNumber: Lỗi hệ thống - " . $e->getMessage();
            }
        }
        fclose($handle);

        return back()->with([
            'success' => "Xử lý thành công $success sản phẩm.",
            'errors'  => $errors,
        ]);
    }
}