<?php

use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PlaceholderController;

Route::prefix('don-hang')->name('orders.')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::get('/{order:code}', [OrderController::class, 'show'])->name('show');
    Route::patch('/{order:code}/status', [OrderController::class, 'updateStatus'])->name('status');
    Route::post('/tinh-phi-ship', [OrderController::class, 'calcFee'])->name('calc-fee');

    // GHN
    Route::post('/{order:code}/tao-van-don', [OrderController::class, 'createShipment'])->name('shipment.create');
    Route::get('/{order:code}/tra-cuu', [OrderController::class, 'trackShipment'])->name('shipment.track');
    Route::get('/{order:code}/in-van-don', [OrderController::class, 'printLabel'])->name('shipment.print');
    Route::delete('/{order:code}/huy-van-don', [OrderController::class, 'cancelShipment'])->name('shipment.cancel');
});

Route::get('/van-chuyen', fn () => (new PlaceholderController)->index('Vận chuyển (GHN)'))->name('shipments.index');
Route::get('/doi-tra', fn () => (new PlaceholderController)->index('Đổi trả & Hoàn tiền'))->name('refunds.index');