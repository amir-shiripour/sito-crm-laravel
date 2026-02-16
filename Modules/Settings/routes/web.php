<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\SettingsController;
use Modules\Settings\Http\Controllers\GapGPTLogController;

Route::prefix('settings')->middleware(['auth'])->group(function () {
    Route::get('/', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/test-gapgpt', [SettingsController::class, 'testGapGPT'])->name('settings.test-gapgpt');

    // روت‌های لاگ هوش مصنوعی
    Route::get('/gapgpt-logs', [GapGPTLogController::class, 'index'])->name('settings.gapgpt-logs.index');
    Route::get('/gapgpt-logs/{log}', [GapGPTLogController::class, 'show'])->name('settings.gapgpt-logs.show');
});
