<?php

use Illuminate\Support\Facades\Route;
use Modules\Sms\Http\Controllers\User\SmsSettingsController;
use Modules\Sms\Http\Controllers\User\SmsLogController;
use Modules\Sms\Http\Controllers\User\SmsSendController;

Route::middleware(['can:sms.settings.view'])
    ->prefix('sms')
    ->name('sms.')
    ->group(function () {
        // صفحه تنظیمات
        Route::get('/settings', [SmsSettingsController::class, 'index'])
            ->name('settings.index');

        Route::put('/settings', [SmsSettingsController::class, 'update'])
            ->name('settings.update')
            ->middleware('can:sms.settings.manage');

        // گزارشات و لیست پیامک‌ها
        Route::get('/logs', [SmsLogController::class, 'index'])
            ->name('logs.index')
            ->middleware('can:sms.messages.view');

        // ارسال دستی پیامک
        Route::get('/send', [SmsSendController::class, 'create'])
            ->name('send.create')
            ->middleware('can:sms.messages.send');

        Route::post('/send', [SmsSendController::class, 'store'])
            ->name('send.store')
            ->middleware('can:sms.messages.send');
    });
