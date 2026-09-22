<?php

use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\PlaceholderController;
use App\Http\Controllers\Admin\VoucherController;

Route::get(
    '/khach-hang',
    fn () => (new PlaceholderController)->index('Khách hàng')
)->name('clients.index');

Route::middleware('permission:vouchers.manage')
    ->resource('voucher', VoucherController::class)
    ->parameters(['voucher' => 'voucher'])
    ->names('vouchers')
    ->except(['show']);

Route::middleware('permission:marketing.manage')
    ->get(
        '/banner',
        fn () => (new PlaceholderController)->index('Banner')
    )
    ->name('banners.index');

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
