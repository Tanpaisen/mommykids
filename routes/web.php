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
| Checkout Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/thanh-toan', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/thanh-toan', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/thanh-toan/qr', [CheckoutController::class, 'qr'])->name('checkout.qr');
    Route::post('/thanh-toan/xac-nhan-chuyen-khoan', [CheckoutController::class, 'confirmTransfer'])->name('checkout.confirm-transfer');
    Route::get('/thanh-toan/thanh-cong', [CheckoutController::class, 'success'])->name('checkout.success');

    // GHN Checkout API
    Route::get('/checkout/districts', [CheckoutController::class, 'districts'])->name('checkout.districts');
    Route::get('/checkout/wards', [CheckoutController::class, 'wards'])->name('checkout.wards');
    Route::post('/checkout/shipping-fee', [CheckoutController::class, 'calculateShippingFee'])->name('checkout.shipping-fee');
});


/*
|--------------------------------------------------------------------------
| Cart & OTP API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('api')->group(function () {
    // Cart API
    Route::post('/cart', [CartController::class, 'store'])->name('api.cart.store');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('api.cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('api.cart.destroy');

    // OTP API
    Route::post('/send-otp', [OtpController::class, 'sendOtp'])->name('api.send-otp');
    Route::post('/verify-otp', [OtpController::class, 'verifyOtp'])->name('api.verify-otp');
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


/*
|--------------------------------------------------------------------------
| Temporary Fix DB Route (ULID Structure Rebuild)
|--------------------------------------------------------------------------
*/

Route::get('/fix-db', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::statement('DROP TABLE IF EXISTS cart_items;');
        DB::statement('DROP TABLE IF EXISTS carts;');

        DB::statement('
            CREATE TABLE `carts` (
                `id` CHAR(26) NOT NULL,
                `uuid` VARCHAR(255) NOT NULL,
                `user_id` CHAR(26) NULL,
                `status` ENUM("active", "checked_out", "abandoned") DEFAULT "active",
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `carts_uuid_unique` (`uuid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        DB::statement('
            CREATE TABLE `cart_items` (
                `id` CHAR(26) NOT NULL,
                `cart_id` CHAR(26) NOT NULL,
                `product_id` BIGINT UNSIGNED NOT NULL,
                `quantity` INT DEFAULT 1,
                `price` DECIMAL(12,2) DEFAULT 0.00,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk_cart_items_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return '<h1 style="color: green; text-align: center; margin-top: 50px;">THÀNH CÔNG! Đã tái tạo bảng carts & cart_items chuẩn CHAR(26) ULID.</h1>';
    } catch (\Exception $e) {
        return '<h1 style="color: red;">LỖI: ' . $e->getMessage() . '</h1>';
    }
});