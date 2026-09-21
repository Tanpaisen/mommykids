<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\PlaceholderController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CRM & Marketing Routes
|--------------------------------------------------------------------------
*/

// Khai báo cả 2 đường dẫn /khach-hang và /clients để khớp với Sidebar
Route::get('/khach-hang', [CustomerController::class, 'index'])->name('customers.index');
Route::get('/clients', [CustomerController::class, 'index'])->name('clients.index');

Route::patch('/khach-hang/{id}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');

// Các module khác
Route::get('/voucher', fn () => (new PlaceholderController)->index('Voucher'))->name('vouchers.index');

// Cài đặt chung (thay thế cho Banner)
Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');