<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Setting;
use App\Services\CartService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
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
        // Ép buộc toàn bộ hệ thống tự động dùng HTTPS khi lên môi trường production (Render)
        if (str_contains(request()->getHost(), 'onrender.com') || $this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Chia sẻ dữ liệu Cài đặt chung ($globalSetting) toàn cục ra TẤT CẢ các file Blade view (Bọc Cache để tối ưu tốc độ)
        if (Schema::hasTable('settings')) {
            $globalSetting = Cache::rememberForever('global_settings', function () {
                return Setting::first() ?? new Setting();
            });

            View::share('globalSetting', $globalSetting);
        }

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