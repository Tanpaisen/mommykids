<?php

use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\PlaceholderController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\VoucherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CRM & Marketing Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. Quản lý Khách hàng
// ==========================================
Route::middleware('permission:crm.view')->group(function () {
    Route::get('/khach-hang', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/clients', [CustomerController::class, 'index'])->name('clients.index');
    Route::patch('/khach-hang/{id}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
});

// ==========================================
// 2. Quản lý Voucher
// ==========================================
Route::middleware('permission:vouchers.manage')
    ->resource('voucher', VoucherController::class)
    ->parameters(['voucher' => 'voucher'])
    ->names('vouchers')
    ->except(['show']);

/*
|--------------------------------------------------------------------------
| Campaign
|--------------------------------------------------------------------------
|
| Tạm thời dùng vouchers.manage vì tài khoản Admin hiện tại
| đã có quyền này. Có thể tách campaigns.manage sau.
|
*/

Route::middleware('permission:vouchers.manage')
    ->group(function () {

        // Phải khai báo trước resource để không bị {campaign} bắt nhầm.
        Route::get(
            '/campaigns/products/search',
            [CampaignController::class, 'searchProducts']
        )->name('campaigns.products.search');

        Route::get(
            '/campaigns/trash',
            [CampaignController::class, 'trash']
        )->name('campaigns.trash');

        Route::patch(
            '/campaigns/{id}/restore',
            [CampaignController::class, 'restore']
        )->name('campaigns.restore');

        Route::delete(
            '/campaigns/{id}/force-delete',
            [CampaignController::class, 'forceDelete']
        )->name('campaigns.force-delete');

        Route::resource(
            'campaigns',
            CampaignController::class
        )->except([
            'show',
        ]);
    });
// ==========================================
// 3. Cài đặt chung (Đã thay thế cho Banner)
// ==========================================
Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
