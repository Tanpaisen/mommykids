<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stage;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // SoftDeletes tự loại sản phẩm deleted_at != NULL.
        $query = Product::query()
            ->with('category');

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('category_id')) {
            $query->where(
                'category_id',
                $request->category_id
            );
        }

        if ($request->status === 'active') {
            $query->where('is_active', true);
        }

        if ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($request->boolean('low_stock')) {
            $query->where('stock', '<=', 10);
        }

        $products = $query
            ->orderBy('id', 'asc')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $trashCount = Product::onlyTrashed()->count();

        return view('admin.products.index', compact(
            'products',
            'categories',
            'trashCount'
        ));
    }

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

        return view('admin.products.create', compact(
            'categories',
            'stages',
            'tags'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->rules(),
            $this->messages()
        );

        $validated['slug'] = $this->makeSlug(
            $validated['slug'] ?? null,
            $validated['name']
        );

        // Kiểm tra cả sản phẩm nằm trong thùng rác.
        if (
            Product::withTrashed()
                ->where('slug', $validated['slug'])
                ->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'slug' => 'Slug sản phẩm đã tồn tại.',
                ]);
        }

        $validated['is_active'] =
            $request->boolean('is_active');

        // Ảnh đại diện.
        if ($request->hasFile('image')) {
            $validated['image'] = $request
                ->file('image')
                ->store('products/main', 'public');
        }

        // Gallery.
        $gallery = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $gallery[] = $file->store(
                    'products/gallery',
                    'public'
                );
            }
        }

        $validated['images'] = $gallery;

        unset(
            $validated['stage_ids'],
            $validated['tag_ids'],
            $validated['remove_image'],
            $validated['remove_gallery']
        );

        $product = Product::create($validated);

        $product->stages()->sync(
            $request->input('stage_ids', [])
        );

        $product->tags()->sync(
            $request->input('tag_ids', [])
        );

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Thêm sản phẩm thành công.');
    }

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

        return view('admin.products.edit', compact(
            'product',
            'categories',
            'stages',
            'tags'
        ));
    }

    public function update(
        Request $request,
        Product $product
    ) {
        $rules = $this->rules();

        $rules['slug'] = [
            'nullable',
            'string',
            'max:255',
        ];

        $validated = $request->validate(
            $rules,
            $this->messages()
        );

        $validated['slug'] = $this->makeSlug(
            $validated['slug'] ?? null,
            $validated['name']
        );

        // Kiểm tra cả sản phẩm đã xóa mềm.
        $slugExists = Product::withTrashed()
            ->where('slug', $validated['slug'])
            ->where('id', '!=', $product->id)
            ->exists();

        if ($slugExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'slug' => 'Slug sản phẩm đã tồn tại.',
                ]);
        }

        $validated['is_active'] =
            $request->boolean('is_active');

        /*
        |--------------------------------------------------------------------------
        | Ảnh đại diện
        |--------------------------------------------------------------------------
        */

        if ($request->boolean('remove_image')) {
            $this->deleteLocalImage($product->image);

            $validated['image'] = null;
        }

        if ($request->hasFile('image')) {
            $this->deleteLocalImage($product->image);

            $validated['image'] = $request
                ->file('image')
                ->store('products/main', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Gallery
        |--------------------------------------------------------------------------
        */

        $gallery = $product->images ?? [];

        $removeGallery = $request->input(
            'remove_gallery',
            []
        );

        if (is_array($removeGallery)) {
            foreach ($removeGallery as $imageToRemove) {
                if (
                    in_array(
                        $imageToRemove,
                        $gallery,
                        true
                    )
                ) {
                    $this->deleteLocalImage(
                        $imageToRemove
                    );
                }
            }

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

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $gallery[] = $file->store(
                    'products/gallery',
                    'public'
                );
            }
        }

        $validated['images'] = $gallery;

        unset(
            $validated['stage_ids'],
            $validated['tag_ids'],
            $validated['remove_image'],
            $validated['remove_gallery']
        );

        $product->update($validated);

        $product->stages()->sync(
            $request->input('stage_ids', [])
        );

        $product->tags()->sync(
            $request->input('tag_ids', [])
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

    public function destroy(Product $product)
    {
        /*
         * Lưu người thực hiện.
         *
         * Hiện middleware auth admin của project đang tắt,
         * nên khi chưa đăng nhập giá trị có thể là NULL.
         */
        $product->update([
            'deleted_by' => auth()->id(),

            // Xóa lần nữa sau khi đã từng restore thì reset.
            'restored_by' => null,
            'restored_at' => null,
        ]);

        /*
         * Chỉ cập nhật deleted_at.
         *
         * KHÔNG xóa ảnh.
         * KHÔNG detach Stage.
         * KHÔNG detach Tag.
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

    public function trash(Request $request)
    {
        $query = Product::onlyTrashed()
            ->with('category');

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
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
            });
        }

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

    public function restore(string $id)
    {
        $product = Product::onlyTrashed()
            ->findOrFail($id);

        /*
         * Laravel sẽ đưa deleted_at về NULL.
         */
        $product->restore();

        /*
         * Ghi lại lịch sử khôi phục.
         *
         * deleted_by vẫn giữ nguyên để biết trước đó ai xóa.
         */
        $product->update([
            'restored_by' => auth()->id(),
            'restored_at' => now(),
        ]);

        return redirect()
            ->route('admin.products.trash')
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

    public function forceDelete(string $id)
    {
        $product = Product::onlyTrashed()
            ->findOrFail($id);

        /*
         * Chỉ lúc xóa vĩnh viễn mới xóa file ảnh.
         */
        $this->deleteLocalImage(
            $product->image
        );

        foreach ($product->images ?? [] as $image) {
            $this->deleteLocalImage($image);
        }

        /*
         * Xóa quan hệ pivot.
         */
        $product->stages()->detach();
        $product->tags()->detach();

        /*
         * Xóa thật khỏi database.
         */
        $product->forceDelete();

        return redirect()
            ->route('admin.products.trash')
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
            'category_id' => [
                'required',
                'exists:categories,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'price' => [
                'required',
                'integer',
                'min:0',
            ],

            'old_price' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'discount_percent' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],

            'stock' => [
                'required',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            // Ảnh đại diện.
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            // Gallery.
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

            'remove_image' => [
                'nullable',
                'boolean',
            ],

            'remove_gallery' => [
                'nullable',
                'array',
            ],

            // Stage.
            'stage_ids' => [
                'nullable',
                'array',
            ],

            'stage_ids.*' => [
                'exists:stages,id',
            ],

            // Tag.
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

            'stock.required' =>
                'Vui lòng nhập tồn kho.',

            'stock.integer' =>
                'Tồn kho phải là số nguyên.',

            'stock.min' =>
                'Tồn kho không được nhỏ hơn 0.',

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

    /*
    |--------------------------------------------------------------------------
    | Xóa ảnh local
    |--------------------------------------------------------------------------
    */

    private function deleteLocalImage(
        ?string $image
    ): void {
        if (!$image) {
            return;
        }

        // Không xóa URL ảnh bên ngoài.
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

        Storage::disk('public')->delete($image);
    }
}