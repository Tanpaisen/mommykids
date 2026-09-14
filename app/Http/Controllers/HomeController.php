<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Sản phẩm nổi bật
        |--------------------------------------------------------------------------
        |
        | Chỉ lấy sản phẩm:
        | - đang bán
        | - được Admin tick "Sản phẩm nổi bật"
        |
        | Trang chủ chỉ hiện tối đa 6 sản phẩm.
        |
        */

        $featuredProducts = Product::query()
            ->active()
            ->featured()
            ->latest()
            ->limit(6)
            ->get()
            ->map
            ->toCardArray();

        /*
        |--------------------------------------------------------------------------
        | Các section sản phẩm theo danh mục
        |--------------------------------------------------------------------------
        */

        $sections = Category::active()
            ->with([
                'products' => fn ($q) => $q
                    ->active()
                    ->latest()
                    ->limit(10),
            ])
            ->get()
            ->filter(
                fn (Category $cat) =>
                    $cat->products->isNotEmpty()
            )
            ->map(
                fn (Category $cat) => [
                    'title' => $cat->name,
                    'icon' => $cat->icon,
                    'url' => route(
                        'category.show',
                        $cat->slug
                    ),
                    'products' =>
                        $cat->products
                            ->map
                            ->toCardArray(),
                ]
            );

        return view('client.home', [
            'featuredProducts' => $featuredProducts,
            'sections' => $sections,
        ]);
    }
}