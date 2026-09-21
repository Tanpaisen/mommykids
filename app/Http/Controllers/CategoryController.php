<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Stage;
use App\Models\Tag;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(Request $request, Category $category)
    {
        $query = $category->products()->active();

        $selectedBrands = array_values(array_filter(
            (array) $request->input('brand', [])
        ));

        $selectedAttributes = array_values(array_filter(
            (array) $request->input('attribute', [])
        ));

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
        | Mốc thấp nhất để 0đ giống cách hiển thị của các website e-commerce.
        | Mốc cao nhất lấy từ sản phẩm active của chính danh mục hiện tại,
        | sau đó làm tròn lên 100.000đ để thanh kéo dễ nhìn và không hard-code.
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
            (int) (ceil(max($highestProductPrice, 1) / 100000) * 100000)
        );

        $priceStep = 10000;

        $minPrice = $request->filled('min_price')
            && is_numeric($request->input('min_price'))
                ? (int) $request->input('min_price')
                : $priceFloor;

        $maxPrice = $request->filled('max_price')
            && is_numeric($request->input('max_price'))
                ? (int) $request->input('max_price')
                : $priceCeiling;

        $minPrice = max($priceFloor, min($minPrice, $priceCeiling));
        $maxPrice = max($priceFloor, min($maxPrice, $priceCeiling));

        if ($minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        $hasPriceFilter =
            $minPrice > $priceFloor
            || $maxPrice < $priceCeiling;

        // Giữ nguyên logic hiện tại: riêng Sữa cho bé không hiển thị / áp dụng Thuộc tính.
        $hideAttributeFilter = $category->slug === 'sua-cho-be';

        if ($hideAttributeFilter) {
            $selectedAttributes = [];
        }

        if (!empty($selectedBrands)) {
            $query->whereHas('tags', function ($tagQuery) use ($selectedBrands) {
                $tagQuery
                    ->where('type', 'brand')
                    ->whereIn('slug', $selectedBrands);
            });
        }

        if (!$hideAttributeFilter && !empty($selectedAttributes)) {
            $query->whereHas('tags', function ($tagQuery) use ($selectedAttributes) {
                $tagQuery
                    ->where('type', 'attribute')
                    ->whereIn('slug', $selectedAttributes);
            });
        }

        if (!empty($selectedStageIds)) {
            $query->whereHas('stages', function ($stageQuery) use ($selectedStageIds) {
                $stageQuery->whereIn('stages.id', $selectedStageIds);
            });
        }

        if ($minPrice > $priceFloor) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice < $priceCeiling) {
            $query->where('price', '<=', $maxPrice);
        }

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

        // 5 sản phẩm / hàng ở desktop, tối đa 3 hàng = 15 sản phẩm / trang.
        $products = $query
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($product) => $product->toCardArray());

        $filterTags = Tag::query()
            ->whereIn('type', ['brand', 'attribute'])
            ->whereHas('products', function ($productQuery) use ($category) {
                $productQuery
                    ->where('category_id', $category->id)
                    ->where('is_active', true);
            })
            ->orderBy('name')
            ->get()
            ->groupBy('type');

        $brandTags = $filterTags->get('brand', collect());

        $attributeTags = $hideAttributeFilter
            ? collect()
            : $filterTags->get('attribute', collect());

        $stages = Stage::query()
            ->where('is_active', true)
            ->whereHas('products', function ($productQuery) use ($category) {
                $productQuery
                    ->where('category_id', $category->id)
                    ->where('is_active', true);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('client.category', [
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
        ]);
    }
}
