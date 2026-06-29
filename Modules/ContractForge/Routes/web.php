<?php

use Illuminate\Support\Facades\Route;
use Modules\ContractForge\App\Http\Controllers\ContractController;
use Modules\ContractForge\App\Http\Controllers\ContractTemplateController;
use Modules\ContractForge\App\Http\Controllers\ContractRuleController;
use Modules\ContractForge\App\Http\Controllers\ContractSettingController;

Route::prefix('user')->name('user.')->middleware(['web', 'auth'])->group(function () {
    Route::prefix('contracts')->name('contracts.')->group(function () {
        // Core Contracts Routes
        Route::get('/', [ContractController::class, 'index'])->name('index')->middleware('can:contractforge.view');
        Route::get('/{contract}/show', [ContractController::class, 'show'])->name('show')->middleware('can:contractforge.view');
        Route::get('/{contract}/print', [ContractController::class, 'print'])->name('print')->middleware('can:contractforge.view');
        Route::get('/{contract}/pdf', [ContractController::class, 'pdf'])->name('pdf')->middleware('can:contractforge.view');
        Route::post('/{contract}/sign', [ContractController::class, 'sign'])->name('sign')->middleware('can:contractforge.manage');
        Route::post('/{contract}/cancel', [ContractController::class, 'cancel'])->name('cancel')->middleware('can:contractforge.manage');
        Route::delete('/{contract}', [ContractController::class, 'destroy'])->name('destroy')->middleware('can:contractforge.manage');
        Route::post('/generate-manual', [ContractController::class, 'generateManual'])->name('generate_manual')->middleware('can:contractforge.manage');

        // Template Management Routes
        Route::resource('templates', ContractTemplateController::class)->names('templates')->middleware('can:contractforge.manage');

        // Rule Management Routes
        Route::resource('rules', ContractRuleController::class)->names('rules')->middleware('can:contractforge.manage');

        // Settings Route
        Route::get('/settings', [ContractSettingController::class, 'edit'])->name('settings.edit')->middleware('can:contractforge.settings.manage');
        Route::post('/settings', [ContractSettingController::class, 'update'])->name('settings.update')->middleware('can:contractforge.settings.manage');
    });
});
