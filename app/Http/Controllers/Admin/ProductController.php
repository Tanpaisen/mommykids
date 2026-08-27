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

        // Tìm kiếm theo tên hoặc slug
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        // Lọc theo danh mục
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Lọc trạng thái
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            }

            if ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Lọc tồn kho thấp
        if ($request->boolean('low_stock')) {
            $query->where('stock', '<=', 10);
        }

        // Sắp xếp ID tăng dần
        $products = $query
            ->orderBy('id', 'asc')
            ->paginate(10);

        // Giữ nguyên điều kiện lọc khi chuyển trang
        $products->appends($request->query());

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
        $validated = $request->validate([
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

            'image' => [
                'nullable',
                'image',
                'max:2048',
            ],

            'images' => [
                'nullable',
                'array',
                'max:8',
            ],

            'images.*' => [
                'image',
                'max:2048',
            ],

            'stage_ids' => [
                'nullable',
                'array',
            ],

            'stage_ids.*' => [
                'exists:stages,id',
            ],

            'tag_ids' => [
                'nullable',
                'array',
            ],

            'tag_ids.*' => [
                'exists:tags,id',
            ],
        ], [
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'category_id.exists' => 'Danh mục không hợp lệ.',

            'name.required' => 'Vui lòng nhập tên sản phẩm.',

            'slug.unique' => 'Slug sản phẩm đã tồn tại.',

            'price.required' => 'Vui lòng nhập giá sản phẩm.',
            'price.integer' => 'Giá sản phẩm phải là số.',
            'price.min' => 'Giá sản phẩm không được nhỏ hơn 0.',

            'old_price.integer' => 'Giá cũ phải là số.',
            'old_price.min' => 'Giá cũ không được nhỏ hơn 0.',

            'discount_percent.integer' => 'Phần trăm giảm phải là số nguyên.',
            'discount_percent.min' => 'Phần trăm giảm không được nhỏ hơn 0.',
            'discount_percent.max' => 'Phần trăm giảm không được lớn hơn 100.',

            'stock.required' => 'Vui lòng nhập số lượng tồn kho.',
            'stock.integer' => 'Tồn kho phải là số nguyên.',
            'stock.min' => 'Tồn kho không được nhỏ hơn 0.',

            'image.image' => 'Ảnh đại diện không hợp lệ.',
            'image.max' => 'Ảnh đại diện không được vượt quá 2MB.',

            'images.max' => 'Chỉ được upload tối đa 8 ảnh.',
            'images.*.image' => 'Một trong các ảnh tải lên không hợp lệ.',
            'images.*.max' => 'Mỗi ảnh không được vượt quá 2MB.',
        ]);

        $validated['slug'] = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        $validated['is_active'] = $request->boolean('is_active');

        // Upload ảnh đại diện
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('products/main', 'public');
        }

        // Upload nhiều ảnh chi tiết
        $galleryImages = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $galleryImages[] = $file
                    ->store('products/gallery', 'public');
            }
        }

        $validated['images'] = $galleryImages;

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

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
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
                'unique:products,slug,' . $product->id,
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

            'image' => [
                'nullable',
                'image',
                'max:2048',
            ],

            'images' => [
                'nullable',
                'array',
                'max:8',
            ],

            'images.*' => [
                'image',
                'max:2048',
            ],

            'stage_ids' => [
                'nullable',
                'array',
            ],

            'stage_ids.*' => [
                'exists:stages,id',
            ],

            'tag_ids' => [
                'nullable',
                'array',
            ],

            'tag_ids.*' => [
                'exists:tags,id',
            ],
        ]);

        $validated['slug'] = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        $validated['is_active'] = $request->boolean('is_active');

        // Thay ảnh đại diện
        if ($request->hasFile('image')) {

            if (
                $product->image
                && !Str::startsWith($product->image, [
                    'http://',
                    'https://',
                ])
            ) {
                Storage::disk('public')->delete($product->image);
            }

            $validated['image'] = $request->file('image')
                ->store('products/main', 'public');
        }

        // Gallery hiện tại
        $galleryImages = $product->images ?? [];

        // Thêm ảnh gallery mới
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $galleryImages[] = $file
                    ->store('products/gallery', 'public');
            }
        }

        $validated['images'] = $galleryImages;

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
        // Xóa ảnh đại diện local
        if (
            $product->image
            && !Str::startsWith($product->image, [
                'http://',
                'https://',
            ])
        ) {
            Storage::disk('public')->delete($product->image);
        }

        // Xóa gallery local
        foreach ($product->images ?? [] as $image) {
            Storage::disk('public')->delete($image);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Xóa sản phẩm thành công.');
    }
}