<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\CartService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Sidebar + mobile drawer categories: available on every view that includes
        // partials.sidebar or layouts.app, so no controller needs to pass it manually.

        // Cart badge (header + floating mobile button) kept in sync everywhere.
        View::composer('layouts.app', function ($view) {
            $view->with('cartCount', app(CartService::class)->count());
        });

        View::composer(['client.partials.sidebar', 'client.layouts.app'], function ($view) {
        $view->with('categories', Category::active()->get());
    });
        
    }
}
