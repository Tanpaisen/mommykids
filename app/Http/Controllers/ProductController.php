<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Chi tiết sản phẩm
    |--------------------------------------------------------------------------
    */
    public function show(Product $product)
    {
        $product->load([
            'category',
            'tags',
            'stages',
        ]);

        $brand = $product->tags
            ->firstWhere('type', 'brand');

        $attributes = $product->tags
            ->where('type', 'attribute')
            ->values();

        $related = Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->latest()
            ->limit(5)
            ->get()
            ->map
            ->toCardArray();

        return view(
            'client.product',
            compact(
                'product',
                'brand',
                'attributes',
                'related'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Sản phẩm nổi bật
    |--------------------------------------------------------------------------
    |
    | Chỉ lấy sản phẩm đang bán và được Admin đánh dấu is_featured = true.
    | Trang riêng phân trang 12 sản phẩm mỗi trang.
    |
    */
    public function featured()
    {
        $products = Product::query()
            ->active()
            ->featured()
            ->latest()
            ->paginate(12);

        /*
         * Component <x-product-card> hiện đang nhận dữ liệu dạng array
         * từ Product::toCardArray(), nên chuyển collection trong paginator.
         */
        $products->getCollection()->transform(
            fn (Product $product) => $product->toCardArray()
        );

        return view(
            'client.products.featured',
            compact('products')
        );
    }
}
