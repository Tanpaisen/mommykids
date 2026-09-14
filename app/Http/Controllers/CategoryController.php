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

        $selectedBrands = array_values(array_filter((array) $request->input('brand', [])));
        $selectedAttributes = array_values(array_filter((array) $request->input('attribute', [])));

        $selectedStageIds = collect((array) $request->input('stage', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $price = $request->get('price');

        // Riêng Sữa cho bé không hiển thị / áp dụng filter Thuộc tính.
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

        switch ($price) {
            case 'under_300':
                $query->where('price', '<', 300000);
                break;
            case '300_500':
                $query->whereBetween('price', [300000, 500000]);
                break;
            case '500_800':
                $query->whereBetween('price', [500000, 800000]);
                break;
            case 'over_800':
                $query->where('price', '>', 800000);
                break;
            default:
                $price = null;
                break;
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
        // hasPages() bên Blade sẽ chỉ hiện phân trang khi có hơn 15 kết quả.
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
            'price' => $price,
            'selectedBrands' => $selectedBrands,
            'selectedAttributes' => $selectedAttributes,
            'selectedStageIds' => $selectedStageIds,
            'brandTags' => $brandTags,
            'attributeTags' => $attributeTags,
            'stages' => $stages,
        ]);
    }
}
