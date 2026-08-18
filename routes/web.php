<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Client (storefront) routes for MommyKids
|--------------------------------------------------------------------------
| All views here extend resources/views/layouts/app.blade.php
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/danh-muc/{slug}', [CategoryController::class, 'show'])->name('category.show');

Route::get('/tim-kiem', [SearchController::class, 'index'])->name('search');

Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');

Route::get('/thong-bao', [NotificationController::class, 'index'])
    ->middleware('auth')
    ->name('notifications.index');

Route::get('/dang-nhap', [AuthController::class, 'showLoginForm'])->name('login');
