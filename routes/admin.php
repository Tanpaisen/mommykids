<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PlaceholderController;
use App\Http\Controllers\Admin\ProductController;
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

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/',
            [DashboardController::class, 'index']
        )->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Module 2 — Kiến thức & Sản phẩm
        |--------------------------------------------------------------------------
        */


        /*
        |--------------------------------------------------------------------------
        | GIAI ĐOẠN — THÙNG RÁC
        |--------------------------------------------------------------------------
        */

        // Danh sách giai đoạn đã xóa mềm
        Route::get(
            '/giai-doan-thung-rac',
            [StageController::class, 'trash']
        )->name('stages.trash');

        // Khôi phục giai đoạn
        Route::patch(
            '/giai-doan-thung-rac/{id}/khoi-phuc',
            [StageController::class, 'restore']
        )->name('stages.restore');

        // Xóa vĩnh viễn giai đoạn
        Route::delete(
            '/giai-doan-thung-rac/{id}',
            [StageController::class, 'forceDelete']
        )->name('stages.forceDelete');


        /*
        |--------------------------------------------------------------------------
        | GIAI ĐOẠN — CRUD
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'giai-doan',
            StageController::class
        )
            ->parameters([
                'giai-doan' => 'stage',
            ])
            ->names('stages')
            ->except(['show']);


        /*
        |--------------------------------------------------------------------------
        | DANH MỤC — THÙNG RÁC
        |--------------------------------------------------------------------------
        */

        // Danh sách danh mục đã xóa mềm
        Route::get(
            '/danh-muc-thung-rac',
            [CategoryController::class, 'trash']
        )->name('categories.trash');

        // Khôi phục danh mục
        Route::patch(
            '/danh-muc-thung-rac/{id}/khoi-phuc',
            [CategoryController::class, 'restore']
        )->name('categories.restore');

        // Xóa vĩnh viễn danh mục
        Route::delete(
            '/danh-muc-thung-rac/{id}',
            [CategoryController::class, 'forceDelete']
        )->name('categories.forceDelete');


        /*
        |--------------------------------------------------------------------------
        | DANH MỤC — CRUD
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'danh-muc',
            CategoryController::class
        )
            ->parameters([
                'danh-muc' => 'category',
            ])
            ->names('categories')
            ->except(['show']);


        /*
        |--------------------------------------------------------------------------
        | THUỘC TÍNH / TAG — THÙNG RÁC
        |--------------------------------------------------------------------------
        */

        // Danh sách Tag đã xóa mềm
        Route::get(
            '/thuoc-tinh-thung-rac',
            [TagController::class, 'trash']
        )->name('tags.trash');

        // Khôi phục Tag
        Route::patch(
            '/thuoc-tinh-thung-rac/{id}/khoi-phuc',
            [TagController::class, 'restore']
        )->name('tags.restore');

        // Xóa vĩnh viễn Tag
        Route::delete(
            '/thuoc-tinh-thung-rac/{id}',
            [TagController::class, 'forceDelete']
        )->name('tags.forceDelete');


        /*
        |--------------------------------------------------------------------------
        | THUỘC TÍNH / TAG — CRUD
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/danh-muc/tags',
            [TagController::class, 'store']
        )->name('tags.store');

        Route::put(
            '/danh-muc/tags/{tag}',
            [TagController::class, 'update']
        )->name('tags.update');

        Route::delete(
            '/danh-muc/tags/{tag}',
            [TagController::class, 'destroy']
        )->name('tags.destroy');


        /*
        |--------------------------------------------------------------------------
        | SẢN PHẨM — THÙNG RÁC
        |--------------------------------------------------------------------------
        */

        // Danh sách sản phẩm đã xóa mềm
        Route::get(
            '/san-pham-thung-rac',
            [ProductController::class, 'trash']
        )->name('products.trash');

        // Khôi phục sản phẩm
        Route::patch(
            '/san-pham-thung-rac/{id}/khoi-phuc',
            [ProductController::class, 'restore']
        )->name('products.restore');

        // Xóa vĩnh viễn sản phẩm
        Route::delete(
            '/san-pham-thung-rac/{id}',
            [ProductController::class, 'forceDelete']
        )->name('products.forceDelete');


        /*
        |--------------------------------------------------------------------------
        | SẢN PHẨM — CRUD
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'san-pham',
            ProductController::class
        )
            ->parameters([
                'san-pham' => 'product',
            ])
            ->names('products')
            ->except(['show']);


        /*
        |--------------------------------------------------------------------------
        | Module 3 — Cẩm nang & Tương tác
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/cam-nang',
            fn () =>
                (new PlaceholderController)
                    ->index('Bài viết Cẩm nang')
        )->name('articles.index');

        Route::get(
            '/hoi-dap',
            fn () =>
                (new PlaceholderController)
                    ->index('Trung tâm Hỏi đáp')
        )->name('comments.index');


        /*
        |--------------------------------------------------------------------------
        | Module 4 — Đơn hàng & Dòng tiền
        |--------------------------------------------------------------------------
        */

        Route::prefix('don-hang')
            ->name('orders.')
            ->group(function () {

                // Danh sách đơn hàng
                Route::get(
                    '/',
                    [OrderController::class, 'index']
                )->name('index');


                // Tính phí vận chuyển
                // Đặt trước /{order}
                Route::post(
                    '/tinh-phi-ship',
                    [OrderController::class, 'calcFee']
                )->name('calc-fee');


                // Chi tiết đơn hàng
                Route::get(
                    '/{order}',
                    [OrderController::class, 'show']
                )->name('show');


                // Cập nhật trạng thái đơn hàng
                Route::patch(
                    '/{order}/status',
                    [OrderController::class, 'updateStatus']
                )->name('status');


                // GHN — Tạo vận đơn
                Route::post(
                    '/{order}/tao-van-don',
                    [OrderController::class, 'createShipment']
                )->name('shipment.create');


                // GHN — Tra cứu vận đơn
                Route::get(
                    '/{order}/tra-cuu',
                    [OrderController::class, 'trackShipment']
                )->name('shipment.track');


                // GHN — In vận đơn
                Route::get(
                    '/{order}/in-van-don',
                    [OrderController::class, 'printLabel']
                )->name('shipment.print');


                // GHN — Hủy vận đơn
                Route::delete(
                    '/{order}/huy-van-don',
                    [OrderController::class, 'cancelShipment']
                )->name('shipment.cancel');
            });


        /*
        |--------------------------------------------------------------------------
        | Vận chuyển
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/van-chuyen',
            fn () =>
                (new PlaceholderController)
                    ->index('Vận chuyển (GHN)')
        )->name('shipments.index');


        /*
        |--------------------------------------------------------------------------
        | Đổi trả & Hoàn tiền
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/doi-tra',
            fn () =>
                (new PlaceholderController)
                    ->index('Đổi trả & Hoàn tiền')
        )->name('refunds.index');


        /*
        |--------------------------------------------------------------------------
        | Module 5 — CRM & Marketing
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/khach-hang',
            fn () =>
                (new PlaceholderController)
                    ->index('Khách hàng')
        )->name('clients.index');


        Route::get(
            '/voucher',
            fn () =>
                (new PlaceholderController)
                    ->index('Voucher')
        )->name('vouchers.index');


        Route::get(
            '/banner',
            fn () =>
                (new PlaceholderController)
                    ->index('Banner')
        )->name('banners.index');


        /*
        |--------------------------------------------------------------------------
        | Phân quyền & Quản trị viên
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'roles',
            RoleController::class
        )->except(['show']);


        // Thêm quản trị viên
        Route::get(
            '/quan-tri-vien/them',
            [AdminController::class, 'create']
        )->name('admins.create');


        // Lưu quản trị viên
        Route::post(
            '/quan-tri-vien',
            [AdminController::class, 'store']
        )->name('admins.store');


        // Cập nhật vai trò quản trị viên
        Route::patch(
            '/quan-tri-vien/{admin}/vai-tro',
            [AdminController::class, 'updateRole']
        )->name('admins.updateRole');
    });