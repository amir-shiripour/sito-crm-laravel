<?php

use Illuminate\Support\Facades\Route;
use Modules\Clients\Middleware\EnsureClientsModuleEnabled;
use Modules\Clients\App\Http\Controllers\User\ClientController as UserClientController;
use Modules\Clients\App\Http\Controllers\User\DashboardController as UserClientDashboardController;
use Modules\Clients\App\Livewire\Settings\ClientFormBuilder;
use Modules\Clients\App\Livewire\Settings\ClientUsernameSettings;
use Modules\Clients\App\Livewire\Settings\ClientStatusesManager;

Route::middleware(['web','auth',EnsureClientsModuleEnabled::class])->prefix('user')->name('user.')->group(function () {

    // صفحه داشبورد اختصاصی ماژول (اگر نیاز باشه)
    Route::get('/clients/dashboard', [UserClientDashboardController::class, 'index'])->name('clients.dashboard');

    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [UserClientController::class, 'index'])->name('index')->middleware('permission:clients.view');
        Route::get('/create', [UserClientController::class, 'create'])->name('create')->middleware('permission:clients.create');
        Route::post('/', [UserClientController::class, 'store'])->name('store')->middleware('permission:clients.create');
        Route::get('/{client}', [UserClientController::class, 'show'])->name('show')->middleware('permission:clients.view');
        Route::get('/{client}/edit', [UserClientController::class, 'edit'])->name('edit')->middleware('permission:clients.edit');
        Route::put('/{client}', [UserClientController::class, 'update'])->name('update')->middleware('permission:clients.edit');
        Route::delete('/{client}', [UserClientController::class, 'destroy'])->name('destroy')->middleware('permission:clients.delete');
    });

    Route::get('/clients/profile', [UserClientController::class, 'profile'])->name('clients.profile')->middleware('permission:clients.view');
});

Route::middleware(['web','auth',EnsureClientsModuleEnabled::class,'permission:clients.manage'])
    ->prefix('user/settings/clients')->name('user.settings.clients.')
    ->group(function () {
        Route::get('/forms', ClientFormBuilder::class)->name('forms');
        Route::get('/username', ClientUsernameSettings::class)->name('username');
        Route::get('/statuses', ClientStatusesManager::class)->name('statuses');
    });
