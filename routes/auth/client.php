<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| Client Authentication Routes (OTP Popup Mode)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    // Route tên 'login' bắt buộc phải có để Middleware 'auth' không bị lỗi.
    // Khi người dùng chưa đăng nhập cố vào /ho-so hay /thanh-toan, Laravel sẽ đẩy về trang chủ và mở Popup.
    Route::get('/login', function () {
        return redirect()->route('home', ['open_login' => 1]);
    })->name('login');

});

// Đăng xuất tài khoản Client
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');