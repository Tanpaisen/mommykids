<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;

// ── Tài khoản quản trị ──
Route::controller(AdminController::class)
    ->prefix('quan-tri-vien')
    ->name('admins.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/them', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::patch('/{admin}/vai-tro', 'updateRole')->name('updateRole');
        Route::delete('/{admin}', 'destroy')->name('destroy');
    });
    
// ── Nhóm quyền ──
Route::resource('nhom-quyen', RoleController::class)
    ->except(['show'])
    ->parameters(['nhom-quyen' => 'role'])
    ->names([
        'index'   => 'roles.index',
        'create'  => 'roles.create',
        'store'   => 'roles.store',
        'edit'    => 'roles.edit',
        'update'  => 'roles.update',
        'destroy' => 'roles.destroy',
    ]);

// ── Phân quyền ──
Route::controller(PermissionController::class)
    ->prefix('phan-quyen')
    ->name('permissions.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{nhomQuyen}', 'edit')->name('edit');
        Route::put('/{nhomQuyen}', 'update')->name('update');
    });