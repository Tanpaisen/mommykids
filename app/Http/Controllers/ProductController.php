<?php

namespace App\Http\Controllers;

use App\Models\Order;
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
        /*
        |--------------------------------------------------------------------------
        | Dữ liệu sản phẩm
        |--------------------------------------------------------------------------
        */
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

        /*
        |--------------------------------------------------------------------------
        | Sản phẩm liên quan
        |--------------------------------------------------------------------------
        */
        $related = Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->latest()
            ->limit(5)
            ->get()
            ->map
            ->toCardArray();

        /*
        |--------------------------------------------------------------------------
        | Review sản phẩm
        |--------------------------------------------------------------------------
        */

        /*
         * Danh sách review.
         *
         * Chỉ lấy 5 review mỗi trang.
         * Eager load user để tránh N+1 query.
         */
        $reviews = $product->reviews()
            ->with('user')
            ->latest()
            ->paginate(
                5,
                ['*'],
                'reviews_page'
            );

        /*
         * Tổng số review.
         */
        $reviewCount = $product->reviews()
            ->count();

        /*
         * Điểm đánh giá trung bình.
         */
        $averageRating = $reviewCount > 0
            ? round(
                (float) $product->reviews()->avg('rating'),
                1
            )
            : 0;

        /*
         * Lấy số review theo từng mức sao.
         */
        $ratingCounts = $product->reviews()
            ->selectRaw(
                'rating, COUNT(*) as total'
            )
            ->groupBy('rating')
            ->pluck(
                'total',
                'rating'
            );

        /*
         * Chuẩn hóa distribution 5 sao → 1 sao.
         */
        $ratingDistribution = [];

        for ($rating = 5; $rating >= 1; $rating--) {
            $count = (int) (
                $ratingCounts[$rating] ?? 0
            );

            $ratingDistribution[$rating] = [
                'count' => $count,

                'percentage' => $reviewCount > 0
                    ? round(
                        ($count / $reviewCount) * 100
                    )
                    : 0,
            ];
        }

        /*
         * Review của user hiện tại.
         */
        $userReview = auth()->check()
            ? $product->reviews()
                ->where(
                    'user_id',
                    auth()->id()
                )
                ->first()
            : null;

        /*
        |--------------------------------------------------------------------------
        | Kiểm tra user đã mua sản phẩm chưa
        |--------------------------------------------------------------------------
        |
        | Chỉ tính là đã mua hợp lệ khi:
        |
        | - Order thuộc user hiện tại
        | - Order.status = delivered
        | - Trong order có OrderItem chứa product_id hiện tại
        |
        */
        $hasPurchasedProduct = false;

        if (auth()->check()) {
            $hasPurchasedProduct = Order::query()
                ->where(
                    'user_id',
                    auth()->id()
                )
                ->where(
                    'status',
                    'delivered'
                )
                ->whereHas(
                    'items',
                    function ($query) use ($product) {
                        $query->where(
                            'product_id',
                            $product->id
                        );
                    }
                )
                ->exists();
        }

        /*
         * User chỉ được phép review nếu:
         *
         * - đã đăng nhập
         * - đã mua sản phẩm
         * - đơn đã giao thành công
         * - chưa từng review sản phẩm này
         */
        $canReview =
            auth()->check()
            && $hasPurchasedProduct
            && !$userReview;

        /*
        |--------------------------------------------------------------------------
        | Trả dữ liệu xuống product detail
        |--------------------------------------------------------------------------
        */
        return view(
            'client.product',
            compact(
                'product',
                'brand',
                'attributes',
                'related',
                'reviews',
                'averageRating',
                'reviewCount',
                'ratingDistribution',
                'userReview',
                'hasPurchasedProduct',
                'canReview'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Sản phẩm nổi bật
    |--------------------------------------------------------------------------
    |
    | Chỉ lấy sản phẩm đang bán và được Admin
    | đánh dấu is_featured = true.
    |
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
         * Component <x-product-card> hiện đang nhận
         * dữ liệu dạng array từ Product::toCardArray().
         */
        $products->getCollection()->transform(
            fn (Product $product) =>
                $product->toCardArray()
        );

        return view(
            'client.products.featured',
            compact('products')
        );
    }
}