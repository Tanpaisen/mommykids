<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\HandbookCategoryController;

/*
|--------------------------------------------------------------------------
| Module Cẩm nang & Tương tác
|--------------------------------------------------------------------------
*/

// Quản lý Danh mục Cẩm nang
Route::get('/cam-nang', [HandbookCategoryController::class, 'index'])->name('handbook-categories.index');
Route::post('/cam-nang', [HandbookCategoryController::class, 'store'])->name('handbook-categories.store');
Route::delete('/cam-nang/{id}', [HandbookCategoryController::class, 'destroy'])->name('handbook-categories.destroy');

// Bài viết
Route::get('/bai-viet', [ArticleController::class, 'index'])->name('articles.index');
Route::post('/bai-viet', [ArticleController::class, 'store'])->name('articles.store');
Route::delete('/bai-viet/{id}', [ArticleController::class, 'destroy'])->name('articles.destroy');

// Trung tâm Hỏi đáp
Route::get('/hoi-dap', [FaqController::class, 'index'])->name('hoi-dap.index');
Route::post('/hoi-dap', [FaqController::class, 'store'])->name('hoi-dap.store');
Route::delete('/hoi-dap/{id}', [FaqController::class, 'destroy'])->name('hoi-dap.destroy');

// Route tạm cho Quản lý Bình luận
Route::get('/binh-luan', function () {
    return back()->with('info', 'Tính năng bình luận đang được phát triển');
})->name('comments.index');