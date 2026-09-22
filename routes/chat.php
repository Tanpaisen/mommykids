<?php

use App\Http\Controllers\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Client\ChatController as ClientChatController;
use Illuminate\Support\Facades\Route;

Route::prefix('chat')->name('chat.')->group(function () {
    Route::post('/session', [ClientChatController::class, 'session'])->name('session');
    Route::post('/session/new', [ClientChatController::class, 'newSession'])->name('new-session');
    Route::get('/{conversation}/messages', [ClientChatController::class, 'messages'])->name('messages');
    Route::post('/{conversation}/messages', [ClientChatController::class, 'sendMessage'])->name('messages.send');
    Route::post('/{conversation}/request-staff', [ClientChatController::class, 'requestStaff'])->name('request-staff');
});

Route::middleware([
    'auth:admin',
    'permission:crm.view',
])
    ->prefix('admin/chats')
    ->name('admin.chats.')
    ->group(function () {
    Route::get('/', [AdminChatController::class, 'index'])->name('index');
    Route::get('/{conversation}', [AdminChatController::class, 'show'])->name('show');
    Route::get('/{conversation}/messages/live', [AdminChatController::class, 'messages'])->name('messages');
    Route::post('/{conversation}/accept', [AdminChatController::class, 'accept'])->name('accept');
    Route::post('/{conversation}/messages', [AdminChatController::class, 'sendMessage'])->name('messages.send');
    Route::post('/{conversation}/close', [AdminChatController::class, 'close'])->name('close');
});
