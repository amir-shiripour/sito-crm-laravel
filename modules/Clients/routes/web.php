<?php

use Illuminate\Support\Facades\Route;
use Modules\Clients\App\Http\Controllers\ClientController;
use Modules\Clients\Middleware\EnsureClientsModuleEnabled;

Route::group([
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
});
