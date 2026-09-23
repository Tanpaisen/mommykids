<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CampaignService
{
    public function getBestCampaignForProduct(
        Product $product
    ): ?Campaign {
        $campaigns = Campaign::query()
            ->active()
            ->whereHas(
                'products',
                function ($query) use ($product) {
                    $query
                        ->where(
                            'products.id',
                            $product->id
                        )
                        ->where(function ($stockQuery) {
                            $stockQuery
                                ->whereNull(
                                    'campaign_products.stock_limit'
                                )
                                ->orWhereColumn(
                                    'campaign_products.sold_quantity',
                                    '<',
                                    'campaign_products.stock_limit'
                                );
                        });
                }
            )
            ->with([
                'products' => function ($query) use ($product) {
                    $query->where(
                        'products.id',
                        $product->id
                    );
                },
            ])
            ->orderByDesc('priority')
            ->get();

        if ($campaigns->isEmpty()) {
            return null;
        }

        $topPriority = (int) $campaigns
            ->first()
            ->priority;

        return $campaigns
            ->where('priority', $topPriority)
            ->sortBy(
                fn (Campaign $campaign) =>
                    $this->getCampaignPrice(
                        $campaign,
                        $product
                    )
            )
            ->first();
    }

    public function getCampaignPrice(
        Campaign $campaign,
        Product $product
    ): int {
        $campaignProduct = $campaign
            ->products
            ->firstWhere('id', $product->id);

        if (!$campaignProduct) {
            $campaignProduct = $campaign
                ->products()
                ->where(
                    'products.id',
                    $product->id
                )
                ->first();
        }

        if (!$campaignProduct) {
            return (int) $product->price;
        }

        $pivot = $campaignProduct->pivot;
        $basePrice = (int) $product->price;

        if ($pivot->sale_price !== null) {
            return max(
                0,
                (int) $pivot->sale_price
            );
        }

        if ($pivot->discount_percent !== null) {
            $percent = min(
                100,
                max(
                    0,
                    (int) $pivot->discount_percent
                )
            );

            return max(
                0,
                (int) round(
                    $basePrice * (100 - $percent) / 100
                )
            );
        }

        $configPercent = data_get(
            $campaign->config,
            'discount_percent'
        );

        if ($configPercent !== null) {
            $percent = min(
                100,
                max(
                    0,
                    (int) $configPercent
                )
            );

            return max(
                0,
                (int) round(
                    $basePrice * (100 - $percent) / 100
                )
            );
        }

        return $basePrice;
    }

    public function getDiscountAmount(
        Campaign $campaign,
        Product $product
    ): int {
        return max(
            0,
            (int) $product->price
                - $this->getCampaignPrice(
                    $campaign,
                    $product
                )
        );
    }

    public function reserveCampaignStock(
        Campaign $campaign,
        int $productId,
        int $quantity,
        int|string|null $userId = null
    ): bool {
        if ($quantity <= 0) {
            return false;
        }

        $row = DB::table('campaign_products')
            ->where(
                'campaign_id',
                $campaign->id
            )
            ->where(
                'product_id',
                $productId
            )
            ->lockForUpdate()
            ->first();

        if (!$row) {
            return false;
        }

        if (
            $row->stock_limit !== null
            && (
                (int) $row->sold_quantity
                + $quantity
            ) > (int) $row->stock_limit
        ) {
            return false;
        }

        if (
            $row->max_per_user !== null
            && $userId !== null
        ) {
            $userBought = OrderItem::query()
                ->where(
                    'campaign_id',
                    $campaign->id
                )
                ->where(
                    'product_id',
                    $productId
                )
                ->whereHas(
                    'order',
                    function ($query) use ($userId) {
                        $query
                            ->where(
                                'user_id',
                                $userId
                            )
                            ->whereNotIn(
                                'status',
                                [
                                    'cancelled',
                                    'refunded',
                                ]
                            );
                    }
                )
                ->sum('quantity');

            if (
                (int) $userBought + $quantity
                > (int) $row->max_per_user
            ) {
                return false;
            }
        }

        DB::table('campaign_products')
            ->where('id', $row->id)
            ->update([
                'sold_quantity' =>
                    (int) $row->sold_quantity
                    + $quantity,
                'updated_at' => now(),
            ]);

        return true;
    }

    public function releaseCampaignStock(
        Campaign $campaign,
        int $productId,
        int $quantity
    ): void {
        if ($quantity <= 0) {
            return;
        }

        DB::transaction(function () use (
            $campaign,
            $productId,
            $quantity
        ) {
            $row = DB::table('campaign_products')
                ->where(
                    'campaign_id',
                    $campaign->id
                )
                ->where(
                    'product_id',
                    $productId
                )
                ->lockForUpdate()
                ->first();

            if (!$row) {
                return;
            }

            DB::table('campaign_products')
                ->where('id', $row->id)
                ->update([
                    'sold_quantity' => max(
                        0,
                        (int) $row->sold_quantity
                            - $quantity
                    ),
                    'updated_at' => now(),
                ]);
        });
    }
}
