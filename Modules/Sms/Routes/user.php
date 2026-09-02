<?php

use Illuminate\Support\Facades\Route;
use Modules\Sms\Http\Controllers\User\SmsSettingsController;
use Modules\Sms\Http\Controllers\User\SmsLogController;
use Modules\Sms\Http\Controllers\User\SmsSendController;
use Modules\Sms\Http\Controllers\User\SmsTemplateController;

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

        Route::post('/logs/{message}/resend', [SmsLogController::class, 'resend'])
            ->name('logs.resend')
            ->middleware('can:sms.messages.send');


        // ارسال دستی پیامک
        Route::get('/send', [SmsSendController::class, 'create'])
            ->name('send.create')
            ->middleware('can:sms.messages.send');

        Route::post('/send', [SmsSendController::class, 'store'])
            ->name('send.store')
            ->middleware('can:sms.messages.send');

        // الگوها و پیش‌نویس‌های پیامک
        Route::get('/templates', [SmsTemplateController::class, 'index'])
            ->name('templates.index')
            ->middleware('can:sms.messages.send');

        Route::post('/templates', [SmsTemplateController::class, 'store'])
            ->name('templates.store')
            ->middleware('can:sms.messages.send');

        Route::put('/templates/{template}', [SmsTemplateController::class, 'update'])
            ->name('templates.update')
            ->middleware('can:sms.messages.send');

        Route::delete('/templates/{template}', [SmsTemplateController::class, 'destroy'])
            ->name('templates.destroy')
            ->middleware('can:sms.messages.send');

        // استعلام تازه موجودی
        Route::post('/balance/refresh', [SmsTemplateController::class, 'refreshBalance'])
            ->name('balance.refresh')
            ->middleware('can:sms.messages.view');
    });
