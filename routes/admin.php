<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlaceholderController;
use App\Http\Controllers\Admin\RoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin routes — require this file from routes/web.php:
|   require __DIR__.'/admin.php';
| or register it in App\Providers\RouteServiceProvider::boot() alongside web.php.
|--------------------------------------------------------------------------
| Uses the `admin` guard (App\Models\Admin, see config/auth.php) so a
| customer session on the storefront can never reach these routes, and
| Spatie's `permission:` middleware for the per-module gating described
| in the Module 5 spec ("Nhân viên viết bài chỉ thấy Module 3...").
*/

// Route::prefix('admin')->name('admin.')->middleware(['auth:admin'])->group(function () {

//     Route::get('/', [DashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('dashboard');

//     // ---- Module 2: Kiến thức & Sản phẩm ----
//     Route::middleware('permission:catalog.view')->group(function () {
//         Route::get('/giai-doan', fn () => (new PlaceholderController)->index('Giai đoạn của bé'))->name('stages.index');
//         Route::get('/danh-muc', fn () => (new PlaceholderController)->index('Danh mục & Thuộc tính'))->name('categories.index');
//         Route::get('/san-pham', fn () => (new PlaceholderController)->index('Sản phẩm'))->name('products.index');
//     });

//     // ---- Module 3: Cẩm nang & Tương tác ----
//     Route::middleware('permission:handbook.view')->group(function () {
//         Route::get('/cam-nang', fn () => (new PlaceholderController)->index('Bài viết Cẩm nang'))->name('articles.index');
//         Route::get('/hoi-dap', fn () => (new PlaceholderController)->index('Trung tâm Hỏi đáp'))->name('comments.index');
//     });

//     // ---- Module 4: Đơn hàng & Dòng tiền ----
//     Route::middleware('permission:orders.view')->group(function () {
//         Route::get('/don-hang', fn () => (new PlaceholderController)->index('Đơn hàng'))->name('orders.index');
//         Route::get('/van-chuyen', fn () => (new PlaceholderController)->index('Vận chuyển (GHN)'))->name('shipments.index');
//         Route::get('/doi-tra', fn () => (new PlaceholderController)->index('Đổi trả & Hoàn tiền'))->name('refunds.index');
//     });

//     // ---- Module 5: CRM & Marketing ----
//     Route::middleware('permission:crm.view')->group(function () {
//         Route::get('/khach-hang', fn () => (new PlaceholderController)->index('Khách hàng'))->name('clients.index');
//         Route::get('/voucher', fn () => (new PlaceholderController)->index('Voucher'))->name('vouchers.index');
//         Route::get('/banner', fn () => (new PlaceholderController)->index('Banner'))->name('banners.index');
//     });

//     // ---- Nhóm quyền & Tài khoản quản trị (trang chính của yêu cầu này) ----
//     Route::middleware('permission:roles.manage')->group(function () {
//         Route::resource('roles', RoleController::class)->except(['show']);
//         Route::get('/quan-tri-vien/them', [AdminController::class, 'create'])->name('admins.create');
//         Route::post('/quan-tri-vien', [AdminController::class, 'store'])->name('admins.store');
//         Route::patch('/quan-tri-vien/{admin}/vai-tro', [AdminController::class, 'updateRole'])->name('admins.updateRole');
//     });
// });

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/giai-doan', fn () => (new PlaceholderController)->index('Giai đoạn của bé'))->name('stages.index');
    Route::get('/danh-muc', fn () => (new PlaceholderController)->index('Danh mục & Thuộc tính'))->name('categories.index');
    Route::get('/san-pham', fn () => (new PlaceholderController)->index('Sản phẩm'))->name('products.index');
    Route::get('/cam-nang', fn () => (new PlaceholderController)->index('Bài viết Cẩm nang'))->name('articles.index');
    Route::get('/hoi-dap', fn () => (new PlaceholderController)->index('Trung tâm Hỏi đáp'))->name('comments.index');
    Route::get('/don-hang', fn () => (new PlaceholderController)->index('Đơn hàng'))->name('orders.index');
    Route::get('/van-chuyen', fn () => (new PlaceholderController)->index('Vận chuyển (GHN)'))->name('shipments.index');
    Route::get('/doi-tra', fn () => (new PlaceholderController)->index('Đổi trả & Hoàn tiền'))->name('refunds.index');
    Route::get('/khach-hang', fn () => (new PlaceholderController)->index('Khách hàng'))->name('clients.index');
    Route::get('/voucher', fn () => (new PlaceholderController)->index('Voucher'))->name('vouchers.index');
    Route::get('/banner', fn () => (new PlaceholderController)->index('Banner'))->name('banners.index');

    Route::resource('roles', RoleController::class)->except(['show']);
    Route::get('/quan-tri-vien/them', [AdminController::class, 'create'])->name('admins.create');
    Route::post('/quan-tri-vien', [AdminController::class, 'store'])->name('admins.store');
    Route::patch('/quan-tri-vien/{admin}/vai-tro', [AdminController::class, 'updateRole'])->name('admins.updateRole');
});