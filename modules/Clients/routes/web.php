<?php

use Illuminate\Support\Facades\Route;
use Modules\Clients\App\Http\Controllers\ClientController;
use Modules\Clients\Middleware\EnsureClientsModuleEnabled;
use Modules\Clients\App\Http\Controllers\User\ClientController as UserClientController;
use Modules\Clients\App\Http\Controllers\User\DashboardController as UserDashboardController;

/*Route::group([
    'prefix' => 'clients',
    'as' => 'clients.',
    'middleware' => ['web', EnsureClientsModuleEnabled::class, 'auth']
], function () {
    Route::get('/', [ClientController::class, 'index'])->name('index')->middleware('permission:clients.view');
    Route::get('/create', [ClientController::class, 'create'])->name('create')->middleware('permission:clients.create');
    Route::post('/', [ClientController::class, 'store'])->name('store')->middleware('permission:clients.create');
    Route::get('/{client}/edit', [ClientController::class, 'edit'])->name('edit')->middleware('permission:clients.edit');
    Route::put('/{client}', [ClientController::class, 'update'])->name('update')->middleware('permission:clients.edit');
    Route::delete('/{client}', [ClientController::class, 'destroy'])->name('destroy')->middleware('permission:clients.delete');
});*/


Route::middleware(['web','auth',EnsureClientsModuleEnabled::class])->prefix('user')->name('user.')->group(function () {

    // داشبورد کاربری مرتبط با ماژول (مثال)
    Route::get('/clients/dashboard', [UserDashboardController::class, 'index'])->name('clients.dashboard');

    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [UserClientController::class, 'index'])->name('index')->middleware('permission:clients.view');
        Route::get('/create', [UserClientController::class, 'create'])->name('create')->middleware('permission:clients.create');
        Route::post('/', [UserClientController::class, 'store'])->name('store')->middleware('permission:clients.create');
        Route::get('/{client}', [UserClientController::class, 'show'])->name('show')->middleware('permission:clients.view');
        Route::get('/{client}/edit', [UserClientController::class, 'edit'])->name('edit')->middleware('permission:clients.edit');
        Route::put('/{client}', [UserClientController::class, 'update'])->name('update')->middleware('permission:clients.edit');
        Route::delete('/{client}', [UserClientController::class, 'destroy'])->name('destroy')->middleware('permission:clients.delete');
    });

    // پروفایل کاربر/مشتری (در صورت وجود رابطه)
    Route::get('/clients/profile', [UserClientController::class, 'profile'])->name('clients.profile')->middleware('permission:clients.view');
});
