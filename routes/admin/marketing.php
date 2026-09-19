<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\PlaceholderController;
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
Route::get('/banner', fn () => (new PlaceholderController)->index('Banner'))->name('banners.index');