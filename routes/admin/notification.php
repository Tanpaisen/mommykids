<?php

use App\Http\Controllers\Admin\Notification\NotificationLogController;
use App\Http\Controllers\Admin\Notification\NotificationCampaignController;

Route::prefix('thong-bao-log')
    ->name('notification-logs.')
    ->group(function () {
        Route::get(
            '/',
            [NotificationLogController::class, 'index']
        )->name('index');
    });

Route::prefix('thong-bao-marketing')->name('notification-campaigns.')->group(function () {
    Route::get('/', [NotificationCampaignController::class, 'index'])->name('index');
    Route::get('/tao-moi', [NotificationCampaignController::class, 'create'])->name('create');
    Route::post('/', [NotificationCampaignController::class, 'store'])->name('store');
    Route::post('/{campaign}/gui-thu', [NotificationCampaignController::class, 'test'])->name('test');
    Route::post('/{campaign}/gui', [NotificationCampaignController::class, 'send'])->name('send');
    Route::post('/{campaign}/dat-lai', [NotificationCampaignController::class, 'reset'])->name('reset');
    Route::post('/{campaign}/nhan-ban', [NotificationCampaignController::class, 'duplicate'])->name('duplicate');
});