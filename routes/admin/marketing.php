<?php

use App\Http\Controllers\Admin\PlaceholderController;
use App\Http\Controllers\Admin\VoucherController;

Route::get('/khach-hang', fn () => (new PlaceholderController)->index('Khách hàng'))->name('clients.index');

Route::resource('voucher', VoucherController::class)
    ->parameters(['voucher' => 'voucher'])
    ->names('vouchers')
    ->except(['show']);

Route::get('/banner', fn () => (new PlaceholderController)->index('Banner'))->name('banners.index');