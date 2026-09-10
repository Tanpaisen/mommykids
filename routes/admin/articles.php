<?php

use App\Http\Controllers\Admin\PlaceholderController;

 /*
        |--------------------------------------------------------------------------
        | Module 3 — Cẩm nang & Tương tác
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/cam-nang',
            fn () =>
                (new PlaceholderController)
                    ->index('Bài viết Cẩm nang')
        )->name('articles.index');

        Route::get(
            '/hoi-dap',
            fn () =>
                (new PlaceholderController)
                    ->index('Trung tâm Hỏi đáp')
        )->name('comments.index');