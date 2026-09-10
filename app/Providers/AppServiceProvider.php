<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\CartService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
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
        // Cache categories 1 tiếng — chỉ query 1 lần/giờ thay vì mỗi request
        View::composer(['client.partials.sidebar', 'client.layouts.app'], function ($view) {
            $view->with('categories', Cache::remember('categories_sidebar', 3600, 
                fn () => Category::active()->get()
            ));
        });

        // Cache cart count theo cart_id
        View::composer('client.layouts.app', function ($view) {
            $cartService = app(CartService::class);
            $cart = $cartService->getCart();
            
            $count = Cache::remember('cart_count_' . $cart->id, 300,
                fn () => $cartService->count()
            );
            
            $view->with('cartCount', $count);
        });
    }
}