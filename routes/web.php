<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\CheckoutController;

/*
|--------------------------------------------------------------------------
| Client Storefront Routes
|--------------------------------------------------------------------------
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
| Profile Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/ho-so', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/ho-so', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/ho-tro-nguoi-dung', [ProfileController::class, 'support'])->name('profile.support');
    Route::get('/quy-dinh-chinh-sach', [ProfileController::class, 'policy'])->name('profile.policy');
});


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
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegisterForm'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register']);
});


Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');


/*
|--------------------------------------------------------------------------
| Admin Authentication
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [AdminLoginController::class, 'login']);

    Route::post('/logout', [AdminLoginController::class, 'logout'])
        ->name('logout');
});


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

Route::post('/thanh-toan/xac-nhan-chuyen-khoan', [CheckoutController::class, 'confirmTransfer'])
    ->name('checkout.confirm-transfer');

Route::get('/thanh-toan/thanh-cong', [CheckoutController::class, 'success'])
    ->name('checkout.success');


/*
|--------------------------------------------------------------------------
| Admin Sub-routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/admin.php';