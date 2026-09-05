<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\SettingsController;
use Modules\Settings\Http\Controllers\GapGPTLogController;
use Modules\Settings\Http\Controllers\PaymentController;
use Modules\Settings\Http\Controllers\ApiKeyController;
use Modules\Settings\Http\Controllers\UserPaymentSettingsController;
use Modules\Settings\Http\Controllers\GoogleCalendarController;

Route::prefix('settings')->middleware(['auth'])->group(function () {
    Route::get('/', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/test-gapgpt', [SettingsController::class, 'testGapGPT'])->name('settings.test-gapgpt');
    Route::post('/sync-holidays', [SettingsController::class, 'syncHolidays'])->name('settings.sync-holidays');

    // روت‌های لاگ هوش مصنوعی
    Route::get('/gapgpt-logs', [GapGPTLogController::class, 'index'])->name('settings.gapgpt-logs.index');
    Route::get('/gapgpt-logs/{log}', [GapGPTLogController::class, 'show'])->name('settings.gapgpt-logs.show');

    // روت‌های درگاه پرداخت
    Route::post('/payment/request', [PaymentController::class, 'request'])->name('settings.payment.request');
    Route::match(['get', 'post'], '/payment/verify/{gateway}', [PaymentController::class, 'verify'])->name('settings.payment.verify');
    Route::get('/payment/redirect/behpardakht', [PaymentController::class, 'redirectBehpardakht'])->name('settings.payment.behpardakht.redirect');

    // روت‌های مدیریت کلیدهای API
    Route::post('/api-keys', [ApiKeyController::class, 'store'])->name('settings.api-keys.store');
    Route::delete('/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('settings.api-keys.destroy');
    Route::patch('/api-keys/{apiKey}/toggle', [ApiKeyController::class, 'toggleActive'])->name('settings.api-keys.toggle');
    Route::get('/api-keys/{apiKey}/preview', [ApiKeyController::class, 'preview'])->name('settings.api-keys.preview');

    // روت‌های اتصال، مدیریت و ایمپورت دستی Google Calendar
    Route::prefix('google-calendar')->name('settings.google-calendar.')->group(function () {
        Route::get('/connect', [GoogleCalendarController::class, 'connect'])->name('connect');
        Route::get('/callback', [GoogleCalendarController::class, 'callback'])->name('callback');
        Route::post('/disconnect', [GoogleCalendarController::class, 'disconnect'])->name('disconnect');
        Route::get('/calendars', [GoogleCalendarController::class, 'listCalendars'])->name('calendars');
        Route::post('/calendars', [GoogleCalendarController::class, 'saveCalendars'])->name('calendars.save');

        // ایمپورت دستی فایل iCal / ICS / ZIP
        Route::post('/import', [GoogleCalendarController::class, 'importFile'])->name('import');
        Route::post('/clear-imported', [GoogleCalendarController::class, 'clearImported'])->name('clear-imported');
    });

    // روت‌های مدیریت و شخصی‌سازی منو
    Route::prefix('menu-manager')->name('settings.menu-manager.')->group(function () {
        Route::get('/items', [\Modules\Settings\Http\Controllers\MenuManagerController::class, 'getItems'])->name('items');
        Route::post('/save', [\Modules\Settings\Http\Controllers\MenuManagerController::class, 'save'])->name('save');
        Route::post('/reset', [\Modules\Settings\Http\Controllers\MenuManagerController::class, 'reset'])->name('reset');
        Route::post('/toggle-status', [\Modules\Settings\Http\Controllers\MenuManagerController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/toggle-two-step', [\Modules\Settings\Http\Controllers\MenuManagerController::class, 'toggleTwoStep'])->name('toggle-two-step');
        Route::post('/groups', [\Modules\Settings\Http\Controllers\MenuManagerController::class, 'saveGroup'])->name('groups.save');
        Route::delete('/groups/{group}', [\Modules\Settings\Http\Controllers\MenuManagerController::class, 'deleteGroup'])->name('groups.delete');
    });
});

// روت عمومی مستندات کلید API
Route::get('/external/docs/{token}', [ApiKeyController::class, 'docs'])->name('settings.api-keys.docs');

// مسیرهای تنظیمات پرداخت سمت کاربر
Route::middleware(['web', 'auth'])->prefix('user')->name('user.')->group(function () {
    Route::get('/settings/payment', [UserPaymentSettingsController::class, 'edit'])->name('settings.payment');
    Route::post('/settings/payment', [UserPaymentSettingsController::class, 'update'])->name('settings.payment.update');
});
