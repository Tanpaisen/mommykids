<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| Client OTP Authentication Routes (Popup Mode)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // Khi bị middleware 'auth' chặn ở /thanh-toan hoặc /ho-so, Laravel sẽ gọi route này
    Route::get('/login', function () {
        return redirect()->route('home', ['open_login' => 1]);
    })->name('login');
});

// Đăng xuất
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');