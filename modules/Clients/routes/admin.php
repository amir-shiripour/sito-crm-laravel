<?php

use Illuminate\Support\Facades\Route;
use Modules\Clients\App\Http\Controllers\Admin\ClientSettingsController;
use Modules\Clients\App\Http\Controllers\Admin\ClientAdminController;
use Modules\Clients\Middleware\EnsureClientsModuleEnabled;


Route::middleware(['web', 'auth', EnsureClientsModuleEnabled::class, 'role:super-admin|admin', 'permission:modules.manage'])
    ->prefix('admin/clients')
    ->name('admin.clients.')
    ->group(function () {
        // تنظیمات ماژول
        Route::get('/settings', [ClientSettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [ClientSettingsController::class, 'update'])->name('settings.update');

        // مدیریت clients در پنل ادمین
        Route::get('/', [ClientAdminController::class, 'index'])->name('index');
        Route::get('/create', [ClientAdminController::class, 'create'])->name('create')->middleware('permission:clients.create');
        Route::post('/', [ClientAdminController::class, 'store'])->name('store')->middleware('permission:clients.create');
        Route::get('/{client}/edit', [ClientAdminController::class, 'edit'])->name('edit')->middleware('permission:clients.edit');
        Route::put('/{client}', [ClientAdminController::class, 'update'])->name('update')->middleware('permission:clients.edit');
        Route::delete('/{client}', [ClientAdminController::class, 'destroy'])->name('destroy')->middleware('permission:clients.delete');
    });
