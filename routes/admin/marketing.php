<?php

use App\Http\Controllers\Admin\PlaceholderController;

Route::get('/khach-hang', fn () => (new PlaceholderController)->index('Khách hàng'))->name('clients.index');
Route::get('/voucher', fn () => (new PlaceholderController)->index('Voucher'))->name('vouchers.index');
Route::get('/banner', fn () => (new PlaceholderController)->index('Banner'))->name('banners.index');