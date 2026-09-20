<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\StageController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\ProductController;
// ── Giai đoạn ──
Route::middleware('permission:catalog.manage')->group(function () {
    Route::controller(StageController::class)
    ->group(function () {
        Route::get('/giai-doan-thung-rac', 'trash')->name('stages.trash');
        Route::patch('/giai-doan-thung-rac/{id}/khoi-phuc', 'restore')->name('stages.restore');
        Route::delete('/giai-doan-thung-rac/{id}', 'forceDelete')->name('stages.forceDelete');
    });
    Route::resource('giai-doan', StageController::class)
        ->parameters(['giai-doan' => 'stage'])
        ->names('stages')
        ->except(['show']);
});

// ── Danh mục ──
Route::middleware('permission:catalog.manage')->group(function () {
    Route::controller(CategoryController::class)->group(function () {
        Route::get('/danh-muc-thung-rac', 'trash')->name('categories.trash');
        Route::patch('/danh-muc-thung-rac/{id}/khoi-phuc', 'restore')->name('categories.restore');
        Route::delete('/danh-muc-thung-rac/{id}', 'forceDelete')->name('categories.forceDelete');
    });
    Route::resource('danh-muc', CategoryController::class)
        ->parameters(['danh-muc' => 'category'])
        ->names('categories')
        ->except(['show']);
});

// ── Tag ──
Route::middleware('permission:catalog.manage')->group(function () {
    Route::controller(TagController::class)->group(function () {
        Route::get('/thuoc-tinh-thung-rac', 'trash')->name('tags.trash');
        Route::patch('/thuoc-tinh-thung-rac/{id}/khoi-phuc', 'restore')->name('tags.restore');
        Route::delete('/thuoc-tinh-thung-rac/{id}', 'forceDelete')->name('tags.forceDelete');
        Route::post('/danh-muc/tags', 'store')->name('tags.store');
        Route::put('/danh-muc/tags/{tag}', 'update')->name('tags.update');
        Route::delete('/danh-muc/tags/{tag}', 'destroy')->name('tags.destroy');
    });
});

// ── Sản phẩm ──
Route::middleware('permission:products.manage')->group(function () {
    Route::controller(ProductController::class)->group(function () {
        Route::get('/san-pham-thung-rac', 'trash')->name('products.trash');
        Route::patch('/san-pham-thung-rac/{id}/khoi-phuc', 'restore')->name('products.restore');
        Route::delete('/san-pham-thung-rac/{id}', 'forceDelete')->name('products.forceDelete');
    });
    Route::resource('san-pham', ProductController::class)
        ->parameters(['san-pham' => 'product'])
        ->names('products')
        ->except(['show']);

    Route::get('/san-pham/search', [ProductController::class, 'search'])->name('products.search');
});