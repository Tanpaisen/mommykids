<?php

use App\Http\Controllers\Admin\ChatBotScenarioController;
use App\Http\Controllers\Admin\CustomerCareController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:admin',
    'permission:crm.view',
])
    ->prefix('admin/customer-care')
    ->name('admin.customer-care.')
    ->group(function () {

        Route::get(
            '/',
            [CustomerCareController::class, 'index']
        )->name('index');

        Route::post(
            '/scenarios',
            [ChatBotScenarioController::class, 'store']
        )->name('scenarios.store');

        Route::put(
            '/scenarios/{scenario}',
            [ChatBotScenarioController::class, 'update']
        )->name('scenarios.update');

        Route::patch(
            '/scenarios/{scenario}/toggle',
            [ChatBotScenarioController::class, 'toggle']
        )->name('scenarios.toggle');

        Route::delete(
            '/scenarios/{scenario}',
            [ChatBotScenarioController::class, 'destroy']
        )->name('scenarios.destroy');
    });