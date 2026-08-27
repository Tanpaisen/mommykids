<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     */
    public const HOME = '/';

    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            // routes/api.php -> real stateless JSON REST API, prefixed /api, "api" middleware group
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // routes/web.php -> Blade storefront pages + session-based /api/cart used by resources/js/app.js
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // routes/admin.php -> Admin panel (auth:admin guard, permission-gated per module)
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
