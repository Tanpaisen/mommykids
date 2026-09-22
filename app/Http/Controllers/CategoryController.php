<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stage;
use App\Models\Tag;
use App\Services\CampaignService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(
        Request $request,
        Category $category,
        CampaignService $campaignService
    ) {
        $query = $category
            ->products()
            ->active()
            ->withReviewStats();

        $selectedBrands = array_values(
            array_filter(
                (array) $request->input('brand', [])
            )
        );

        $selectedAttributes = array_values(
            array_filter(
                (array) $request->input('attribute', [])
            )
        );

        $selectedStageIds = collect(
            (array) $request->input('stage', [])
        )
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Khoảng giá cho slider
        |--------------------------------------------------------------------------
        |
        | Giữ nguyên hành vi filter hiện tại:
        | filter và sort vẫn dựa trên Product.price trong DB.
        |
        | Campaign chỉ thay đổi giá hiển thị trên card.
        |
        */
        $priceFloor = 0;

        $highestProductPrice = (int) (
            $category
                ->products()
                ->active()
                ->max('price') ?? 0
        );

        $priceCeiling = max(
            100000,
            (int) (
                ceil(max($highestProductPrice, 1) / 100000)
                * 100000
            )
        );

        $priceStep = 10000;

        $minPrice = (
            $request->filled('min_price')
            && is_numeric($request->input('min_price'))
        )
            ? (int) $request->input('min_price')
            : $priceFloor;

        $maxPrice = (
            $request->filled('max_price')
            && is_numeric($request->input('max_price'))
        )
            ? (int) $request->input('max_price')
            : $priceCeiling;

        $minPrice = max(
            $priceFloor,
            min($minPrice, $priceCeiling)
        );

        $maxPrice = max(
            $priceFloor,
            min($maxPrice, $priceCeiling)
        );

        if ($minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        $hasPriceFilter =
            $minPrice > $priceFloor
            || $maxPrice < $priceCeiling;

        /*
         * Giữ nguyên logic hiện tại:
         * riêng Sữa cho bé không hiển thị / áp dụng Thuộc tính.
         */
        $hideAttributeFilter =
            $category->slug === 'sua-cho-be';

        if ($hideAttributeFilter) {
            $selectedAttributes = [];
        }

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */
        if (!empty($selectedBrands)) {
            $query->whereHas(
                'tags',
                function ($tagQuery) use ($selectedBrands) {
                    $tagQuery
                        ->where('type', 'brand')
                        ->whereIn('slug', $selectedBrands);
                }
            );
        }

        if (
            !$hideAttributeFilter
            && !empty($selectedAttributes)
        ) {
            $query->whereHas(
                'tags',
                function ($tagQuery) use ($selectedAttributes) {
                    $tagQuery
                        ->where('type', 'attribute')
                        ->whereIn('slug', $selectedAttributes);
                }
            );
        }

        if (!empty($selectedStageIds)) {
            $query->whereHas(
                'stages',
                function ($stageQuery) use ($selectedStageIds) {
                    $stageQuery->whereIn(
                        'stages.id',
                        $selectedStageIds
                    );
                }
            );
        }

        if ($minPrice > $priceFloor) {
            $query->where(
                'price',
                '>=',
                $minPrice
            );
        }

        if ($maxPrice < $priceCeiling) {
            $query->where(
                'price',
                '<=',
                $maxPrice
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sort
        |--------------------------------------------------------------------------
        */
        $sort = $request->get('sort', 'default');

        switch ($sort) {
            case 'newest':
                $query->latest();
                break;

            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;

            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;

            default:
                $sort = 'default';
                $query->orderByDesc('created_at');
                break;
        }

        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        |
        | Sau khi query + paginate xong mới map giá Campaign
        | vào dữ liệu card. Không sửa Product.price trong DB.
        |
        */
        $products = $query
            ->paginate(15)
            ->withQueryString()
            ->through(
                fn (Product $product) =>
                    $this->toCampaignCard(
                        $product,
                        $campaignService
                    )
            );

        /*
        |--------------------------------------------------------------------------
        | Filter data
        |--------------------------------------------------------------------------
        */
        $filterTags = Tag::query()
            ->whereIn(
                'type',
                [
                    'brand',
                    'attribute',
                ]
            )
            ->whereHas(
                'products',
                function ($productQuery) use ($category) {
                    $productQuery
                        ->where(
                            'category_id',
                            $category->id
                        )
                        ->where(
                            'is_active',
                            true
                        );
                }
            )
            ->orderBy('name')
            ->get()
            ->groupBy('type');

        $brandTags = $filterTags->get(
            'brand',
            collect()
        );

        $attributeTags =
            $hideAttributeFilter
                ? collect()
                : $filterTags->get(
                    'attribute',
                    collect()
                );

        $stages = Stage::query()
            ->where('is_active', true)
            ->whereHas(
                'products',
                function ($productQuery) use ($category) {
                    $productQuery
                        ->where(
                            'category_id',
                            $category->id
                        )
                        ->where(
                            'is_active',
                            true
                        );
                }
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view(
            'client.category',
            [
                'category' => $category,
                'products' => $products,
                'sort' => $sort,
                'selectedBrands' => $selectedBrands,
                'selectedAttributes' => $selectedAttributes,
                'selectedStageIds' => $selectedStageIds,
                'brandTags' => $brandTags,
                'attributeTags' => $attributeTags,
                'stages' => $stages,
                'priceFloor' => $priceFloor,
                'priceCeiling' => $priceCeiling,
                'priceStep' => $priceStep,
                'minPrice' => $minPrice,
                'maxPrice' => $maxPrice,
                'hasPriceFilter' => $hasPriceFilter,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Map Product -> Product Card có Campaign
    |--------------------------------------------------------------------------
    */
    private function toCampaignCard(
        Product $product,
        CampaignService $campaignService
    ): array {
        $card = $product->toCardArray();

        $card['is_campaign'] = false;
        $card['campaign_id'] = null;
        $card['campaign_type'] = null;

        $campaign = $campaignService
            ->getBestCampaignForProduct($product);

        if (!$campaign) {
            return $card;
        }

        $basePrice = (int) $product->price;

        $campaignPrice = (int) (
            $campaignService
                ->getCampaignPrice(
                    $campaign,
                    $product
                )
        );

        if (
            $basePrice <= 0
            || $campaignPrice >= $basePrice
        ) {
            return $card;
        }

        $discountAmount =
            $basePrice - $campaignPrice;

        $discountPercent =
            (int) round(
                (
                    $discountAmount
                    / $basePrice
                ) * 100
            );

        $card['price'] = $campaignPrice;

        /*
         * Khi có Campaign, giá gạch ngang là
         * Product.price ngay trước Campaign.
         */
        $card['old_price'] = $basePrice;

        $card['discount'] = $discountPercent;
        $card['is_campaign'] = true;
        $card['campaign_id'] = $campaign->id;
        $card['campaign_type'] = $campaign->type;

        return $card;
    }
}
