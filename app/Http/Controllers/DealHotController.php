<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Product;
use App\Services\CampaignService;
use Illuminate\Support\Collection;

class DealHotController extends Controller
{
    public function index(
        CampaignService $campaignService
    ) {
        /*
        |--------------------------------------------------------------------------
        | Lấy các Campaign đang hoạt động
        |--------------------------------------------------------------------------
        |
        | Campaign::active() đã kiểm tra:
        | - is_active = true
        | - starts_at <= hiện tại
        | - ends_at >= hiện tại
        |
        */
        $campaigns = Campaign::query()
            ->active()
            ->with([
                'campaignType',

                'products' => function ($query) {
                    $query
                        ->active()
                        ->withReviewStats();
                },
            ])
            ->orderByDesc('priority')
            ->orderBy('ends_at')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Chuẩn hóa dữ liệu dành cho trang Deal Hot
        |--------------------------------------------------------------------------
        |
        | Một sản phẩm có thể nằm trong nhiều Campaign.
        |
        | CampaignService::getBestCampaignForProduct()
        | sẽ xác định Campaign thực sự được áp dụng.
        |
        | Vì vậy:
        | - chỉ hiển thị sản phẩm trong Campaign tốt nhất của nó
        | - không hiển thị Campaign hết stock
        | - không hiển thị Campaign không làm giảm giá
        |
        */
        $dealCampaigns = $campaigns
            ->map(
                function (
                    Campaign $campaign
                ) use ($campaignService) {
                    $products = $campaign
                        ->products
                        ->filter(function (
                            Product $product
                        ) use (
                            $campaign,
                            $campaignService
                        ) {
                            /*
                             * Loại sản phẩm đã hết stock Campaign.
                             */
                            $pivot = $product->pivot;

                            if (
                                $pivot->stock_limit !== null
                                && (int) $pivot->sold_quantity
                                    >= (int) $pivot->stock_limit
                            ) {
                                return false;
                            }

                            /*
                             * Xác định Campaign tốt nhất hiện tại
                             * cho sản phẩm.
                             */
                            $bestCampaign = $campaignService
                                ->getBestCampaignForProduct(
                                    $product
                                );

                            if (!$bestCampaign) {
                                return false;
                            }

                            /*
                             * Chỉ giữ sản phẩm ở đúng Campaign
                             * đang thực sự được áp dụng.
                             */
                            if (
                                (int) $bestCampaign->id
                                !== (int) $campaign->id
                            ) {
                                return false;
                            }

                            /*
                             * Deal Hot chỉ hiển thị khi
                             * Campaign thật sự giảm giá.
                             */
                            $campaignPrice = $campaignService
                                ->getCampaignPrice(
                                    $campaign,
                                    $product
                                );

                            return $campaignPrice
                                < (int) $product->price;
                        })
                        ->map(
                            fn (Product $product) =>
                                $this->toCampaignCard(
                                    $product,
                                    $campaign,
                                    $campaignService
                                )
                        )
                        ->values();

                    return [
                        'id' => $campaign->id,

                        'name' => $campaign->name,

                        'description' =>
                            $campaign->description,

                        'type' =>
                            $campaign->type,

                        'type_name' =>
                            $campaign->campaignType?->name,

                        'starts_at' =>
                            $campaign->starts_at,

                        'ends_at' =>
                            $campaign->ends_at,

                        'priority' =>
                            $campaign->priority,

                        'products' =>
                            $products,
                    ];
                }
            )
            /*
             * Không hiện Campaign không còn sản phẩm
             * đủ điều kiện Deal Hot.
             */
            ->filter(
                fn (array $campaign) =>
                    $campaign['products']->isNotEmpty()
            )
            ->values();


        return view(
            'client.deal-hot',
            [
                'dealCampaigns' => $dealCampaigns,

                'dealProductCount' =>
                    $dealCampaigns
                        ->sum(
                            fn (array $campaign) =>
                                $campaign['products']->count()
                        ),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Chuyển Product thành dữ liệu cho <x-product-card>
    |--------------------------------------------------------------------------
    |
    | Dùng cùng format đang được Home sử dụng.
    |
    */
    private function toCampaignCard(
        Product $product,
        Campaign $campaign,
        CampaignService $campaignService
    ): array {
        $card = $product->toCardArray();

        $basePrice = (int) $product->price;

        $effectivePrice = (int) $campaignService
            ->getCampaignPrice(
                $campaign,
                $product
            );

        $discountPercent = 0;

        if (
            $basePrice > 0
            && $effectivePrice < $basePrice
        ) {
            $discountPercent = (int) round(
                (
                    ($basePrice - $effectivePrice)
                    / $basePrice
                ) * 100
            );
        }

        $card['price'] =
            $effectivePrice;

        $card['old_price'] =
            $basePrice;

        $card['discount'] =
            $discountPercent;

        $card['is_campaign'] =
            true;

        $card['campaign_id'] =
            $campaign->id;

        $card['campaign_type'] =
            $campaign->type;

        return $card;
    }
}