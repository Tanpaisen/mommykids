<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PageController; // Khai báo PageController

// --- CONTROLLERS ADMIN ---
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\HandbookCategoryController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Client\HandbookController;

/*
|--------------------------------------------------------------------------
| Client Storefront Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/danh-muc/{category:slug}', [CategoryController::class, 'show'])
    ->name('category.show');

Route::get('/san-pham/{product:slug}', [ProductController::class, 'show'])
    ->name('product.show');

Route::get('/tim-kiem', [SearchController::class, 'index'])
    ->name('search');

Route::get('/gio-hang', [CartController::class, 'index'])
    ->name('cart.index');

// ROUTE CẨM NANG PHÍA CLIENT
Route::get('/cam-nang/{slug?}', [HandbookController::class, 'index'])->name('handbook.show');

// ROUTE CÁC TRANG THÔNG TIN & CHÍNH SÁCH FOOTER
Route::get('/gioi-thieu', [PageController::class, 'about'])->name('pages.about');
Route::get('/he-thong-cua-hang', [PageController::class, 'stores'])->name('pages.stores');
Route::get('/tuyen-dung', [PageController::class, 'recruitment'])->name('pages.recruitment');
Route::get('/chinh-sach-doi-tra', [PageController::class, 'returnPolicy'])->name('pages.return');
Route::get('/chinh-sach-van-chuyen', [PageController::class, 'shippingPolicy'])->name('pages.shipping');
Route::get('/chinh-sach-bao-mat', [PageController::class, 'privacyPolicy'])->name('pages.privacy');

Route::get('/thong-bao', [NotificationController::class, 'index'])
    ->middleware('auth')
    ->name('notifications.index');

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/ho-so', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::post('/ho-so', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::get('/ho-tro-nguoi-dung', [ProfileController::class, 'support'])
        ->name('profile.support');

    Route::get('/quy-dinh-chinh-sach', [ProfileController::class, 'policy'])
        ->name('profile.policy');
});

/*
|--------------------------------------------------------------------------
| Checkout Routes
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

Route::get('/thanh-toan/thanh-cong', [CheckoutController::class, 'success'])
    ->name('checkout.success');

Route::get(
    '/thanh-toan/trang-thai/{code}',
    [CheckoutController::class, 'paymentStatus']
)->name('checkout.payment-status');

/*
|--------------------------------------------------------------------------
| GHN Checkout API
|--------------------------------------------------------------------------
*/

Route::get('/checkout/districts', [CheckoutController::class, 'districts'])
    ->name('checkout.districts');

Route::get('/checkout/wards', [CheckoutController::class, 'wards'])
    ->name('checkout.wards');

Route::post(
    '/checkout/shipping-fee',
    [CheckoutController::class, 'calculateShippingFee']
)->name('checkout.shipping-fee');

/*
|--------------------------------------------------------------------------
| Cart & OTP API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('api')->group(function () {
    Route::post('/cart', [CartController::class, 'store'])
        ->name('api.cart.store');

    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])
        ->name('api.cart.update');

    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])
        ->name('api.cart.destroy');

    Route::post('/send-otp', [OtpController::class, 'sendOtp'])
        ->name('api.send-otp');

    Route::post('/verify-otp', [OtpController::class, 'verifyOtp'])
        ->name('api.verify-otp');
});

/*
|--------------------------------------------------------------------------
| Admin Customer Management & Menu Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    // Quản lý khách hàng
    Route::get('/khach-hang', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/khach-hang/{id}', [CustomerController::class, 'show'])->name('customers.show');
    Route::post('/khach-hang/{id}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggleStatus');

    // Quản lý Menu Admin (Gộp chung xử lý về SettingController)
    Route::post('/menus/update-all', [SettingController::class, 'updateMenus'])->name('menus.updateAll');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes Includes
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth/client.php';
require __DIR__ . '/auth/admin.php';

/*
|--------------------------------------------------------------------------
| Admin Module Routes Include
|--------------------------------------------------------------------------
*/
require __DIR__ . '/admin.php';