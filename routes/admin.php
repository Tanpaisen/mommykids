<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    require __DIR__ . '/admin/products.php';
    require __DIR__ . '/admin/orders.php';
    require __DIR__ . '/admin/marketing.php';
    require __DIR__ . '/admin/system.php';
    require __DIR__ . '/admin/articles.php';
});