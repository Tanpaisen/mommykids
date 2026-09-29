<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stage;
use App\Models\Tag;
use Illuminate\Http\Request;

trait ProductReadActions
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->with('category')
            ->join(
                'categories',
                'products.category_id',
                '=',
                'categories.id'
            )
            ->select('products.*')
            ->withReviewStats();

        // Tìm kiếm theo tên hoặc slug
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where(
                    'products.name',
                    'like',
                    '%' . $search . '%'
                )->orWhere(
                    'products.slug',
                    'like',
                    '%' . $search . '%'
                );
            });
        }

        // Lọc theo danh mục
        if ($request->filled('category_id')) {
            $query->where(
                'products.category_id',
                $request->category_id
            );
        }

        // Lọc theo trạng thái
        if ($request->status === 'active') {
            $query->where('products.is_active', true);
        }

        if ($request->status === 'inactive') {
            $query->where('products.is_active', false);
        }

        if ($request->status === 'featured') {
            $query->where('products.is_featured', true);
        }

        // Lọc sản phẩm có tồn kho thấp
        if ($request->boolean('low_stock')) {
            $query->where('products.stock', '<=', 10);
        }

        // Sắp xếp sản phẩm
        $sort = (string) $request->input('sort', 'default');

        switch ($sort) {
            case 'newest':
                $query
                    ->orderByDesc('products.created_at')
                    ->orderByDesc('products.id');
                break;

            case 'best_selling':
                $query
                    ->orderByDesc('products.sold_count')
                    ->orderByDesc('reviews_avg_rating')
                    ->orderBy('products.name');
                break;

            case 'rating_desc':
                $query
                    ->orderByDesc('reviews_avg_rating')
                    ->orderByDesc('reviews_count')
                    ->orderByDesc('products.sold_count')
                    ->orderBy('products.name');
                break;

            case 'price_asc':
                $query
                    ->orderBy('products.price')
                    ->orderBy('products.name');
                break;

            case 'price_desc':
                $query
                    ->orderByDesc('products.price')
                    ->orderBy('products.name');
                break;

            default:
                $query
                    ->orderBy('categories.sort_order')
                    ->orderBy('categories.name')
                    ->orderBy('products.name');
                break;
        }

        // Phân trang và giữ tham số tìm kiếm, lọc
        $products = $query
            ->select('products.*')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $trashCount = Product::onlyTrashed()->count();

        return view(
            'admin.products.index',
            compact('products', 'categories', 'trashCount')
        );
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

        return view(
            'admin.products.create',
            compact('categories', 'stages', 'tags')
        );
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

        return view(
            'admin.products.edit',
            compact('product', 'categories', 'stages', 'tags')
        );
    }

    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));

        $products = Product::query()
            ->select('id', 'name')
            ->where('is_active', 1)
            ->where(function ($query) use ($q) {
                $query
                    ->where('name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json($products);
    }
}
