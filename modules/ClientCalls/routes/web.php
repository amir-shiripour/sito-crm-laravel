<?php

use Illuminate\Support\Facades\Route;
use Modules\Clients\Middleware\EnsureClientsModuleEnabled;
use Modules\ClientCalls\App\Http\Controllers\User\ClientCallController;

Route::middleware(['web', 'auth', EnsureClientsModuleEnabled::class])
    ->prefix('user/clients/{client}')
    ->name('user.clients.')
    ->group(function () {
        Route::get('calls', [ClientCallController::class, 'index'])
            ->name('calls.index')
            ->middleware('permission:client-calls.view');

        Route::get('calls/create', [ClientCallController::class, 'create'])
            ->name('calls.create')
            ->middleware('permission:client-calls.create');

        Route::post('calls', [ClientCallController::class, 'store'])
            ->name('calls.store')
            ->middleware('permission:client-calls.create');

        Route::get('calls/{call}/edit', [ClientCallController::class, 'edit'])
            ->name('calls.edit')
            ->middleware('permission:client-calls.edit');

        Route::put('calls/{call}', [ClientCallController::class, 'update'])
            ->name('calls.update')
            ->middleware('permission:client-calls.edit');

        Route::delete('calls/{call}', [ClientCallController::class, 'destroy'])
            ->name('calls.destroy')
            ->middleware('permission:client-calls.delete');
    });
Route::prefix('user')->name('user.')->middleware('auth')->group(function () {
    // تماس سریع
    Route::post('client-calls/quick-store', [ClientCallController::class, 'quickStore'])->name('client-calls.quick-store');

    // جستجو مشتری‌ها
    Route::get('clients/search', [ClientCallController::class, 'searchClients'])->name('clients.search');
});
