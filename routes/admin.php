<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlaceholderController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\StageController;
use App\Http\Controllers\Admin\TagController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
| Hiện permission/auth admin đang tắt để test.
| Khi hoàn thiện module sẽ bật lại middleware auth:admin + permission.
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Module 2 — Kiến thức & Sản phẩm
    |--------------------------------------------------------------------------
    */

    // Giai đoạn của bé
    Route::resource('giai-doan', StageController::class)
        ->parameters([
            'giai-doan' => 'stage',
        ])
        ->names('stages')
        ->except(['show']);

    // Danh mục
    Route::resource('danh-muc', CategoryController::class)
        ->parameters([
            'danh-muc' => 'category',
        ])
        ->names('categories')
        ->except(['show']);

    // Thuộc tính / Tag
    Route::post('/danh-muc/tags', [TagController::class, 'store'])
        ->name('tags.store');

    Route::put('/danh-muc/tags/{tag}', [TagController::class, 'update'])
        ->name('tags.update');

    Route::delete('/danh-muc/tags/{tag}', [TagController::class, 'destroy'])
        ->name('tags.destroy');

    // Sản phẩm
    Route::get(
        '/san-pham',
        fn () => (new PlaceholderController)->index('Sản phẩm')
    )->name('products.index');


    /*
    |--------------------------------------------------------------------------
    | Module 3 — Cẩm nang & Tương tác
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/cam-nang',
        fn () => (new PlaceholderController)->index('Bài viết Cẩm nang')
    )->name('articles.index');

    Route::get(
        '/hoi-dap',
        fn () => (new PlaceholderController)->index('Trung tâm Hỏi đáp')
    )->name('comments.index');


    /*
    |--------------------------------------------------------------------------
    | Module 4 — Đơn hàng & Dòng tiền
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/don-hang',
        fn () => (new PlaceholderController)->index('Đơn hàng')
    )->name('orders.index');

    Route::get(
        '/van-chuyen',
        fn () => (new PlaceholderController)->index('Vận chuyển (GHN)')
    )->name('shipments.index');

    Route::get(
        '/doi-tra',
        fn () => (new PlaceholderController)->index('Đổi trả & Hoàn tiền')
    )->name('refunds.index');


    /*
    |--------------------------------------------------------------------------
    | Module 5 — CRM & Marketing
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/khach-hang',
        fn () => (new PlaceholderController)->index('Khách hàng')
    )->name('clients.index');

    Route::get(
        '/voucher',
        fn () => (new PlaceholderController)->index('Voucher')
    )->name('vouchers.index');

    Route::get(
        '/banner',
        fn () => (new PlaceholderController)->index('Banner')
    )->name('banners.index');


    /*
    |--------------------------------------------------------------------------
    | Phân quyền & Quản trị viên
    |--------------------------------------------------------------------------
    */

    Route::resource('roles', RoleController::class)
        ->except(['show']);

    Route::get(
        '/quan-tri-vien/them',
        [AdminController::class, 'create']
    )->name('admins.create');

    Route::post(
        '/quan-tri-vien',
        [AdminController::class, 'store']
    )->name('admins.store');

    Route::patch(
        '/quan-tri-vien/{admin}/vai-tro',
        [AdminController::class, 'updateRole']
    )->name('admins.updateRole');
});