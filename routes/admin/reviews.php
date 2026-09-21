<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductReviewController;

Route::controller(ProductReviewController::class)->group(function () {

    Route::get(
        '/danh-gia-san-pham',
        'index'
    )->name('reviews.index');

    Route::delete(
        '/danh-gia-san-pham/{review}',
        'destroy'
    )->name('reviews.destroy');

});