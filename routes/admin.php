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