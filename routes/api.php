<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| These are loaded by App\Providers\RouteServiceProvider inside a group
| which is assigned the "api" middleware group (see app/Http/Kernel.php).
| It is automatically prefixed with /api, so the full URLs below are e.g.
| GET /api/v1/products.
|
| This is the REAL, stateless JSON API — for mobile apps / a decoupled
| SPA frontend. The Blade storefront itself does NOT need to call these;
| it already renders data server-side via HomeController, CategoryController,
| etc. and only calls the *session*-based /api/cart/* routes registered in
| routes/web.php for the "add to cart" button (see resources/js/app.js).
*/

Route::prefix('v1')->group(function () {

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category:slug}', [CategoryController::class, 'show']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product:slug}', [ProductController::class, 'show']);

    // Token-authenticated cart for mobile app / SPA clients (php artisan install:api to add Sanctum).
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart', [CartController::class, 'store']);
        Route::patch('/cart/{cartItem}', [CartController::class, 'update']);
        Route::delete('/cart/{cartItem}', [CartController::class, 'destroy']);
    });
});
