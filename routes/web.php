<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Client (storefront) routes for MommyKids
|--------------------------------------------------------------------------
| All page routes below render views that extend resources/views/layouts/app.blade.php.
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/danh-muc/{category:slug}', [CategoryController::class, 'show'])->name('category.show');

Route::get('/san-pham/{product:slug}', [ProductController::class, 'show'])->name('product.show');

Route::get('/tim-kiem', [SearchController::class, 'index'])->name('search');

Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');

Route::get('/thong-bao', [NotificationController::class, 'index'])
    ->middleware('auth')
    ->name('notifications.index');

/*
|--------------------------------------------------------------------------
| JSON "API" consumed by resources/js/app.js (fetch calls) — e.g. mkAddToCart()
|--------------------------------------------------------------------------
| Deliberately kept on the web middleware group (not routes/api.php) because
| the guest cart is session-based and needs the session + CSRF middleware
| that routes/api.php's default "api" group does not include.
*/
Route::prefix('api')->group(function () {
    Route::post('/cart', [CartController::class, 'store'])->name('api.cart.store');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('api.cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('api.cart.destroy');
});

Route::get('/dang-nhap', function () {
    return 'Trang đăng nhập — coming soon';
})->name('login');

Route::get('/dang-ky', function () {
    return 'Trang đăng ký — coming soon';
})->name('register');

Route::get('/ho-so', function () {
    return 'Hồ sơ — coming soon';
})->middleware('auth')->name('profile.edit');

require __DIR__.'/admin.php';
