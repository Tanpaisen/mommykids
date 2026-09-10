<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\CartService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Sidebar
        |--------------------------------------------------------------------------
        | Luôn truyền danh mục cho menu bên trái ở tất cả trang client.
        */
        View::composer('client.partials.sidebar', function ($view) {
            $categories = Category::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            $view->with('categories', $categories);
        });

        /*
        |--------------------------------------------------------------------------
        | Cart badge
        |--------------------------------------------------------------------------
        | Luôn lấy số lượng giỏ hàng thật cho header.
        */
        View::composer('client.partials.header', function ($view) {
            $cartService = app(CartService::class);

            $view->with('cartCount', $cartService->count());
        });
    }
}