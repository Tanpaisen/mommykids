<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CheckoutController;

/*
|--------------------------------------------------------------------------
| Client storefront
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/danh-muc/{category:slug}', [CategoryController::class, 'show'])
    ->name('category.show');

Route::get('/san-pham/{product:slug}', [ProductController::class, 'show'])
    ->name('product.show');

Route::get('/tim-kiem', [SearchController::class, 'index'])
    ->name('search');

Route::get('/gio-hang', [CartController::class, 'index'])
    ->name('cart.index');

Route::get('/thong-bao', [NotificationController::class, 'index'])
    ->middleware('auth')
    ->name('notifications.index');


/*
|--------------------------------------------------------------------------
| Checkout
|--------------------------------------------------------------------------
*/

Route::get('/thanh-toan', [CheckoutController::class, 'index'])
    ->name('checkout.index');

Route::post('/thanh-toan', [CheckoutController::class, 'store'])
    ->name('checkout.store');

Route::get('/thanh-toan/qr', [CheckoutController::class, 'qr'])
    ->name('checkout.qr');

Route::post(
    '/thanh-toan/xac-nhan-chuyen-khoan',
    [CheckoutController::class, 'confirmTransfer']
)->name('checkout.confirm-transfer');

Route::get(
    '/thanh-toan/thanh-cong',
    [CheckoutController::class, 'success']
)->name('checkout.success');


/*
|--------------------------------------------------------------------------
| GHN Checkout API
|--------------------------------------------------------------------------
*/

Route::get(
    '/checkout/districts',
    [CheckoutController::class, 'districts']
)->name('checkout.districts');

Route::get(
    '/checkout/wards',
    [CheckoutController::class, 'wards']
)->name('checkout.wards');

Route::post(
    '/checkout/shipping-fee',
    [CheckoutController::class, 'calculateShippingFee']
)->name('checkout.shipping-fee');


/*
|--------------------------------------------------------------------------
| Cart API
|--------------------------------------------------------------------------
*/

Route::prefix('api')->group(function () {
    Route::post('/cart', [CartController::class, 'store'])
        ->name('api.cart.store');

    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])
        ->name('api.cart.update');

    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])
        ->name('api.cart.destroy');
});


/*
|--------------------------------------------------------------------------
| Authentication placeholders
|--------------------------------------------------------------------------
*/

Route::get('/dang-nhap', function () {
    return 'Trang đăng nhập — coming soon';
})->name('login');

Route::get('/dang-ky', function () {
    return 'Trang đăng ký — coming soon';
})->name('register');

Route::get('/ho-so', function () {
    return 'Hồ sơ — coming soon';
})
    ->middleware('auth')
    ->name('profile.edit');


require __DIR__.'/admin.php';