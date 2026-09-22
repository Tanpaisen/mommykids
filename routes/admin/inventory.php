<?php

use App\Http\Controllers\Admin\InventoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Quản lý Kho (Inventory)
|--------------------------------------------------------------------------
|
| Đưa file này vào routes/admin/ và require trong web.php:
|   require __DIR__ . '/admin/inventory.php';
|
*/

Route::prefix('kho')->name('inventory.')->group(function () {
    // Dashboard tồn kho (cảnh báo hết hàng, vốn kho, dead stock)
    Route::get('/', [InventoryController::class, 'index'])->name('index');

    // Lịch sử biến động kho
    Route::get('/lich-su', [InventoryController::class, 'movements'])->name('movements');

    // Nhập hàng
    Route::get('/nhap-hang', [InventoryController::class, 'createImport'])->name('import.create');
    Route::post('/nhap-hang', [InventoryController::class, 'storeImport'])->name('import.store');

    // Điều chỉnh tồn kho (kiểm kê)
    Route::get('/dieu-chinh', [InventoryController::class, 'createAdjust'])->name('adjust.create');
    Route::post('/dieu-chinh', [InventoryController::class, 'storeAdjust'])->name('adjust.store');
});
