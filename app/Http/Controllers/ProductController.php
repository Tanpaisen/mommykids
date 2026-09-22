<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\CampaignService;

class ProductController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Chi tiết sản phẩm
    |--------------------------------------------------------------------------
    */
    public function show(
        Product $product,
        CampaignService $campaignService
    ) {
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
        | Campaign đang áp dụng cho sản phẩm hiện tại
        |--------------------------------------------------------------------------
        */
        $campaign = $campaignService
            ->getBestCampaignForProduct($product);

        $campaignBasePrice = (int) $product->price;

        $campaignPrice = $campaign
            ? (int) $campaignService
                ->getCampaignPrice(
                    $campaign,
                    $product
                )
            : $campaignBasePrice;

        $campaignDiscountAmount = $campaign
            ? max(
                0,
                $campaignBasePrice - $campaignPrice
            )
            : 0;

        $campaignDiscountPercent = (
            $campaign
            && $campaignBasePrice > 0
            && $campaignPrice < $campaignBasePrice
        )
            ? (int) round(
                (
                    $campaignDiscountAmount
                    / $campaignBasePrice
                ) * 100
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Sản phẩm liên quan
        |--------------------------------------------------------------------------
        |
        | Mỗi sản phẩm liên quan cũng được tính Campaign riêng.
        | Không thay đổi Product.price trong DB.
        */
        $related = Product::query()
            ->active()
            ->withReviewStats()
            ->where(
                'category_id',
                $product->category_id
            )
            ->where(
                'id',
                '!=',
                $product->id
            )
            ->latest()
            ->limit(5)
            ->get()
            ->map(
                function (
                    Product $relatedProduct
                ) use ($campaignService) {
                    $card =
                        $relatedProduct->toCardArray();

                    $relatedCampaign =
                        $campaignService
                            ->getBestCampaignForProduct(
                                $relatedProduct
                            );

                    if (!$relatedCampaign) {
                        $card['is_campaign'] = false;
                        $card['campaign_id'] = null;
                        $card['campaign_type'] = null;

                        return $card;
                    }

                    $basePrice =
                        (int) $relatedProduct->price;

                    $effectivePrice =
                        (int) $campaignService
                            ->getCampaignPrice(
                                $relatedCampaign,
                                $relatedProduct
                            );

                    if (
                        $effectivePrice >= $basePrice
                        || $basePrice <= 0
                    ) {
                        $card['is_campaign'] = false;
                        $card['campaign_id'] = null;
                        $card['campaign_type'] = null;

                        return $card;
                    }

                    $discountPercent =
                        (int) round(
                            (
                                (
                                    $basePrice
                                    - $effectivePrice
                                )
                                / $basePrice
                            ) * 100
                        );

                    /*
                     * Card hiện tại đã hiểu:
                     * price / old_price / discount
                     *
                     * Vì vậy chỉ cần map Campaign vào
                     * đúng cấu trúc này.
                     */
                    $card['price'] =
                        $effectivePrice;

                    $card['old_price'] =
                        $basePrice;

                    $card['discount'] =
                        $discountPercent;

                    $card['is_campaign'] =
                        true;

                    $card['campaign_id'] =
                        $relatedCampaign->id;

                    $card['campaign_type'] =
                        $relatedCampaign->type;

                    return $card;
                }
            );

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
                (float) $product
                    ->reviews()
                    ->avg('rating'),
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

        for (
            $rating = 5;
            $rating >= 1;
            $rating--
        ) {
            $count = (int) (
                $ratingCounts[$rating] ?? 0
            );

            $ratingDistribution[$rating] = [
                'count' => $count,

                'percentage' =>
                    $reviewCount > 0
                        ? round(
                            (
                                $count
                                / $reviewCount
                            ) * 100
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
            $hasPurchasedProduct =
                Order::query()
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
                'canReview',
                'campaign',
                'campaignBasePrice',
                'campaignPrice',
                'campaignDiscountAmount',
                'campaignDiscountPercent'
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
            ->withReviewStats()
            ->latest()
            ->paginate(12);

        /*
         * Component <x-product-card> hiện đang nhận
         * dữ liệu dạng array từ Product::toCardArray().
         */
        $products
            ->getCollection()
            ->transform(
                fn (Product $product) =>
                    $product->toCardArray()
            );

        return view(
            'client.products.featured',
            compact('products')
        );
    }
}
