<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\SocialLoginController; // <-- Đã thêm Controller Social Login
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PageController;

// --- CONTROLLERS ADMIN ---
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Payment\ZaloPayController;
use App\Http\Controllers\Payment\StripeController;
use App\Http\Controllers\Client\VoucherController;
use App\Http\Controllers\Payment\PayPalController;
use App\Http\Controllers\Client\OrderController as ClientOrderController;
use App\Http\Controllers\Admin\HandbookCategoryController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Client\HandbookController;

/*
|--------------------------------------------------------------------------
| Client Storefront Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

/*
 * Trang riêng hiển thị toàn bộ sản phẩm được Admin
 * đánh dấu là "Sản phẩm nổi bật".
 */
Route::get('/san-pham-noi-bat', [ProductController::class, 'featured'])
    ->name('products.featured');

Route::get('/danh-muc/{category:slug}', [CategoryController::class, 'show'])
    ->name('category.show');

Route::get('/san-pham/{product:slug}', [ProductController::class, 'show'])
    ->name('product.show');

/*
|--------------------------------------------------------------------------
| Facebook Social Login Routes
|--------------------------------------------------------------------------
*/
Route::get('/auth/facebook', [SocialLoginController::class, 'redirectToFacebook'])
    ->name('auth.facebook');

Route::get('/auth/facebook/callback', [SocialLoginController::class, 'handleFacebookCallback'])
    ->name('auth.facebook.callback');

/*
|--------------------------------------------------------------------------
| Product Review Routes
|--------------------------------------------------------------------------
|
| - Chỉ user đã đăng nhập mới có thể gửi / cập nhật đánh giá.
| - Backend ProductReviewController tiếp tục kiểm tra:
|     + User đã mua đúng sản phẩm.
|     + Order đã delivered.
|     + Mỗi user chỉ có 1 review cho 1 product.
|
*/

/*
 * Tạo đánh giá mới.
 */
Route::post(
    '/san-pham/{product:slug}/danh-gia',
    [ProductReviewController::class, 'store']
)
    ->middleware('auth')
    ->name('products.reviews.store');

/*
 * Cập nhật đánh giá đã tồn tại.
 */
Route::patch(
    '/san-pham/{product:slug}/danh-gia/{review}',
    [ProductReviewController::class, 'update']
)
    ->middleware('auth')
    ->name('products.reviews.update');

/*
|--------------------------------------------------------------------------
| Search / Cart / Notification / Voucher
|--------------------------------------------------------------------------
*/

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

Route::get('/khuyen-mai', [VoucherController::class, 'index'])
    ->name('vouchers.index');


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

    Route::get(
        '/ho-so/don-hang',
        [ClientOrderController::class, 'index']
    )->name('profile.orders.index');

    Route::get(
        '/ho-so/don-hang/{order}',
        [ClientOrderController::class, 'show']
    )->name('profile.orders.show');

    Route::post(
        '/ho-so/don-hang/{order}/yeu-cau-huy',
        [ClientOrderController::class, 'requestCancellation']
    )->name('profile.orders.cancel-request');
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
)
    ->name('checkout.confirm-transfer');

Route::get('/thanh-toan/thanh-cong', [CheckoutController::class, 'success'])
    ->name('checkout.success');

Route::get(
    '/thanh-toan/trang-thai/{code}',
    [CheckoutController::class, 'paymentStatus']
)
    ->name('checkout.payment-status');


/*
|--------------------------------------------------------------------------
| GHN Checkout API
|--------------------------------------------------------------------------
*/

Route::get(
    '/checkout/districts',
    [CheckoutController::class, 'districts']
)
    ->name('checkout.districts');

Route::get(
    '/checkout/wards',
    [CheckoutController::class, 'wards']
)
    ->name('checkout.wards');

Route::post(
    '/checkout/shipping-fee',
    [CheckoutController::class, 'calculateShippingFee']
)->name('checkout.shipping-fee');

Route::post('/checkout/vouchers/apply', [CheckoutController::class, 'applyVoucher'])
    ->name('checkout.vouchers.apply');

Route::post('/checkout/vouchers/remove', [CheckoutController::class, 'removeVoucher'])
    ->name('checkout.vouchers.remove');


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

    Route::post(
        '/vouchers/save',
        [VoucherController::class, 'saveVoucher']
    )
        ->name('api.vouchers.save');
});

Route::get('/payments/zalopay/create', [ZaloPayController::class, 'create'])
    ->name('zalopay.create');

Route::get('/payments/zalopay/return', [ZaloPayController::class, 'result'])
    ->name('zalopay.return');

Route::get(
    '/payments/zalopay/qr',
    [ZaloPayController::class, 'qr']
)->name('zalopay.qr');

Route::get(
    '/payments/zalopay/status',
    [ZaloPayController::class, 'status']
)->name('zalopay.status');

Route::get(
    '/payments/stripe/create',
    [StripeController::class, 'create']
)->name('stripe.create');

Route::get(
    '/payments/stripe/success',
    [StripeController::class, 'success']
)->name('stripe.success');

Route::get('/payments/paypal/create', [PayPalController::class, 'create'])
    ->name('paypal.create');

Route::get('/payments/paypal/capture', [PayPalController::class, 'capture'])
    ->name('paypal.capture');

Route::get('/payments/paypal/cancel', [PayPalController::class, 'cancel'])
    ->name('paypal.cancel');

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