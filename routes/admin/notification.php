<?php

use App\Http\Controllers\Admin\NotificationLogController;


Route::prefix('thong-bao-log')
    ->name('notification-logs.')
    ->group(function () {
        Route::get(
            '/',
            [NotificationLogController::class, 'index']
        )->name('index');
    });