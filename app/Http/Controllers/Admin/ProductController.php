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
            $query->where('category_id', $request->category_id);
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

        return view('admin.products.index', compact(
            'products',
            'categories'
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

        /*
         * Trường hợp người dùng để slug rỗng nhưng slug tự sinh
         * lại trùng với sản phẩm khác.
         */
        if (
            Product::where('slug', $validated['slug'])->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'slug' => 'Slug sản phẩm đã tồn tại.',
                ]);
        }

        $validated['is_active'] = $request->boolean('is_active');

        /*
        |--------------------------------------------------------------------------
        | Ảnh đại diện
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {
            $validated['image'] = $request
                ->file('image')
                ->store('products/main', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Gallery
        |--------------------------------------------------------------------------
        */

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
            $validated['tag_ids']
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
            'unique:products,slug,' . $product->id,
        ];

        $validated = $request->validate(
            $rules,
            $this->messages()
        );

        $validated['slug'] = $this->makeSlug(
            $validated['slug'] ?? null,
            $validated['name']
        );

        $slugExists = Product::query()
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

        $validated['is_active'] = $request->boolean('is_active');

        /*
        |--------------------------------------------------------------------------
        | Xóa ảnh đại diện hiện tại nếu admin yêu cầu
        |--------------------------------------------------------------------------
        */

        if ($request->boolean('remove_image')) {
            $this->deleteLocalImage($product->image);
            $validated['image'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Thay ảnh đại diện
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {

            $this->deleteLocalImage($product->image);

            $validated['image'] = $request
                ->file('image')
                ->store('products/main', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Gallery hiện tại
        |--------------------------------------------------------------------------
        */

        $gallery = $product->images ?? [];

        /*
        |--------------------------------------------------------------------------
        | Xóa ảnh gallery được chọn
        |--------------------------------------------------------------------------
        */

        $removeGallery = $request->input(
            'remove_gallery',
            []
        );

        if (is_array($removeGallery)) {

            foreach ($removeGallery as $imageToRemove) {

                if (in_array($imageToRemove, $gallery, true)) {
                    $this->deleteLocalImage($imageToRemove);
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

        /*
        |--------------------------------------------------------------------------
        | Thêm ảnh gallery mới
        |--------------------------------------------------------------------------
        */

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
            $validated['tag_ids']
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
            ->with('success', 'Cập nhật sản phẩm thành công.');
    }

    public function destroy(Product $product)
    {
        $this->deleteLocalImage($product->image);

        foreach ($product->images ?? [] as $image) {
            $this->deleteLocalImage($image);
        }

        /*
         * Với belongsToMany, detach trước cho rõ ràng.
         * Nếu pivot đã có cascade thì thao tác này vẫn an toàn.
         */
        $product->stages()->detach();
        $product->tags()->detach();

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Xóa sản phẩm thành công.');
    }

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
                'unique:products,slug',
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
            ],

            /*
            |--------------------------------------------------------------------------
            | Ảnh
            |--------------------------------------------------------------------------
            */

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

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

            'slug.unique' =>
                'Slug sản phẩm đã tồn tại.',

            'price.required' =>
                'Vui lòng nhập giá sản phẩm.',

            'price.integer' =>
                'Giá sản phẩm phải là số.',

            'price.min' =>
                'Giá sản phẩm không được nhỏ hơn 0.',

            'old_price.integer' =>
                'Giá cũ phải là số.',

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

    private function deleteLocalImage(
        ?string $image
    ): void {
        if (!$image) {
            return;
        }

        if (
            Str::startsWith(
                $image,
                ['http://', 'https://']
            )
        ) {
            return;
        }

        Storage::disk('public')->delete($image);
    }
}