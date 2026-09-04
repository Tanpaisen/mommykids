<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminController;

Route::prefix('admin')->name('admin.')->group(function () {

    // ================= 1. CHƯA ĐĂNG NHẬP (GUEST) =================
    Route::middleware('guest:admin')->group(function () {
        Route::get('/auth/login', [AdminController::class, 'showLoginForm'])->name('auth.login');
        Route::post('/auth/login', [AdminController::class, 'login']);
    });

    // ================= 2. BẮT BUỘC ĐÃ ĐĂNG NHẬP ADMIN =================
    Route::middleware('auth:admin')->group(function () {

        // Trang Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Quản lý tài khoản Admin
        Route::get('/quan-tri-vien', [AdminController::class, 'index'])->name('admins.index');
        Route::get('/quan-tri-vien/create', [AdminController::class, 'create'])->name('admins.create');
        Route::post('/quan-tri-vien', [AdminController::class, 'store'])->name('admins.store');
        Route::patch('/quan-tri-vien/{admin}/role', [AdminController::class, 'updateRole'])->name('admins.updateRole');
        Route::delete('/quan-tri-vien/{admin}', [AdminController::class, 'destroy'])->name('admins.destroy');

        // Đăng xuất
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

        // Load toàn bộ các module quản trị khác (Đã được bảo vệ an toàn)
        require __DIR__ . '/admin/products.php';
        require __DIR__ . '/admin/orders.php';
        require __DIR__ . '/admin/marketing.php';
        require __DIR__ . '/admin/system.php';
        require __DIR__ . '/admin/articles.php';
    });
});