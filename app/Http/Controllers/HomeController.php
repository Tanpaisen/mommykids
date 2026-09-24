<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\CampaignService;

class HomeController extends Controller
{
    public function index(
        CampaignService $campaignService
    ) {
        /*
        |--------------------------------------------------------------------------
        | Sản phẩm nổi bật
        |--------------------------------------------------------------------------
        |
        | Không cache giá cuối cùng vì Campaign có thể bắt đầu/kết thúc
        | trong lúc cache vẫn còn hiệu lực.
        |
        */
        $featuredProducts = Product::query()
            ->active()
            ->featured()
            ->withReviewStats()
            ->latest()
            ->limit(10)
            ->get()
            ->map(
                fn (Product $product) =>
                    $this->toCampaignCard(
                        $product,
                        $campaignService
                    )
            );

        /*
        |--------------------------------------------------------------------------
        | Các section sản phẩm theo danh mục
        |--------------------------------------------------------------------------
        |
        | Chỉ giữ Category có sản phẩm để hiển thị các block sản phẩm
        | ở phía dưới trang chủ.
        |
        */
        $sections = Category::query()
            ->active()
            ->with([
                'products' => fn ($query) =>
                    $query
                        ->active()
                        ->withReviewStats()
                        ->latest()
                        ->limit(10),
            ])
            ->get()
            ->filter(
                fn (Category $category) =>
                    $category->products->isNotEmpty()
            )
            ->map(
                function (
                    Category $category
                ) use ($campaignService) {
                    return [
                        'title' => $category->name,

                        'icon' => $category->icon,

                        'url' => route(
                            'category.show',
                            $category->slug
                        ),

                        'products' => $category
                            ->products
                            ->map(
                                fn (Product $product) =>
                                    $this->toCampaignCard(
                                        $product,
                                        $campaignService
                                    )
                            ),
                    ];
                }
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Danh mục riêng cho sidebar Home
        |--------------------------------------------------------------------------
        |
        | Khác $sections:
        |
        | $sections:
        | - phục vụ block sản phẩm
        | - chỉ có category đang có sản phẩm
        |
        | $homeCategories:
        | - phục vụ menu bên trái Home
        | - lấy toàn bộ category active
        |
        */
        $homeCategories = Category::query()
            ->active()
            ->orderBy('id')
            ->limit(10)
            ->get();

        return view('client.home', [
            'featuredProducts' => $featuredProducts,
            'sections' => $sections,
            'homeCategories' => $homeCategories,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Chuyển Product thành dữ liệu card có Campaign
    |--------------------------------------------------------------------------
    */
    private function toCampaignCard(
        Product $product,
        CampaignService $campaignService
    ): array {
        $card = $product->toCardArray();

        $campaign = $campaignService
            ->getBestCampaignForProduct(
                $product
            );

        /*
         * Không có Campaign.
         */
        if (!$campaign) {
            $card['is_campaign'] = false;
            $card['campaign_id'] = null;
            $card['campaign_type'] = null;

            return $card;
        }

        $basePrice = (int) $product->price;

        $effectivePrice = (int) $campaignService
            ->getCampaignPrice(
                $campaign,
                $product
            );

        /*
         * Campaign không thực sự làm giảm giá.
         */
        if (
            $basePrice <= 0 ||
            $effectivePrice >= $basePrice
        ) {
            $card['is_campaign'] = false;
            $card['campaign_id'] = null;
            $card['campaign_type'] = null;

            return $card;
        }

        $discountPercent = (int) round(
            (
                ($basePrice - $effectivePrice)
                / $basePrice
            ) * 100
        );

        /*
         * Card hiện tại hiểu các field:
         *
         * price
         * old_price
         * discount
         * is_campaign
         */
        $card['price'] = $effectivePrice;

        $card['old_price'] = $basePrice;

        $card['discount'] = $discountPercent;

        $card['is_campaign'] = true;

        $card['campaign_id'] = $campaign->id;

        $card['campaign_type'] = $campaign->type;

        return $card;
    }
}