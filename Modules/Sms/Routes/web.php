<?php

use Illuminate\Support\Facades\Route;
use Modules\Sms\Http\Controllers\User\SmsSettingsController;
use Modules\Sms\Http\Controllers\User\SmsLogController;

Route::prefix('sms')
    ->name('sms.')
    ->group(function () {
        // /user/sms/settings
        Route::get('settings', [SmsSettingsController::class, 'index'])
            ->name('settings.index');

        Route::post('settings', [SmsSettingsController::class, 'store'])
            ->name('settings.store');

        // /user/sms/logs
        Route::get('logs', [SmsLogController::class, 'index'])
            ->name('logs.index');
    });
