<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes (mommykids.com/admin/...)
|--------------------------------------------------------------------------
| Các route trong file này đã được tự động thêm tiền tố '/admin' 
| và name prefix 'admin.' từ RouteServiceProvider.
*/

Route::get('/dashboard', function () {
    return "🚀 Chào mừng Quản trị viên đến với trang Dashboard của MommyKids!";
})->name('dashboard');

// Nơi Thành viên 2 (Catalog & Admin) sẽ viết API/Route sau này:
// Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
// Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);