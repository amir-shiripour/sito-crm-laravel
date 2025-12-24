<?php

use Illuminate\Support\Facades\Route;
use Modules\Clients\App\Http\Controllers\Admin\ClientSettingsController;
use Modules\Clients\App\Http\Controllers\Admin\ClientAdminController;
use Modules\Clients\Middleware\EnsureClientsModuleEnabled;


Route::middleware(['web', 'auth', EnsureClientsModuleEnabled::class, 'role:super-admin|admin', 'permission:modules.manage'])
    ->prefix('admin/clients')
    ->name('admin.clients.')
    ->group(function () {
        Route::get('/settings/label', [ClientSettingsController::class, 'editLabel'])->name('settings.label.edit');
        Route::put('/settings/label', [ClientSettingsController::class, 'updateLabel'])->name('settings.label.update');

        // مسیرهای فعلی شما ...
        Route::get('/settings', [ClientSettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [ClientSettingsController::class, 'update'])->name('settings.update');

        Route::get('/', [\Modules\Clients\App\Http\Controllers\Admin\ClientAdminController::class, 'index'])->name('index');
        Route::get('/create', [\Modules\Clients\App\Http\Controllers\Admin\ClientAdminController::class, 'create'])->name('create')->middleware('permission:clients.create');
        Route::post('/', [\Modules\Clients\App\Http\Controllers\Admin\ClientAdminController::class, 'store'])->name('store')->middleware('permission:clients.create');
        Route::get('/{client}/edit', [\Modules\Clients\App\Http\Controllers\Admin\ClientAdminController::class, 'edit'])->name('edit')->middleware('permission:clients.edit');
        Route::put('/{client}', [\Modules\Clients\App\Http\Controllers\Admin\ClientAdminController::class, 'update'])->name('update')->middleware('permission:clients.edit');
        Route::delete('/{client}', [\Modules\Clients\App\Http\Controllers\Admin\ClientAdminController::class, 'destroy'])->name('destroy')->middleware('permission:clients.delete');
    });
