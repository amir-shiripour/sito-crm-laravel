<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\SettingsController;
use Modules\Settings\Http\Controllers\GapGPTLogController;
use Modules\Settings\Http\Controllers\PaymentController;
use Modules\Settings\Http\Controllers\ApiKeyController;
use Modules\Settings\Http\Controllers\UserPaymentSettingsController;

Route::prefix('settings')->middleware(['auth'])->group(function () {
    Route::get('/', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/test-gapgpt', [SettingsController::class, 'testGapGPT'])->name('settings.test-gapgpt');

    // روت‌های لاگ هوش مصنوعی
    Route::get('/gapgpt-logs', [GapGPTLogController::class, 'index'])->name('settings.gapgpt-logs.index');
    Route::get('/gapgpt-logs/{log}', [GapGPTLogController::class, 'show'])->name('settings.gapgpt-logs.show');

    // روت‌های درگاه پرداخت
    Route::post('/payment/request', [PaymentController::class, 'request'])->name('settings.payment.request');
    Route::get('/payment/verify/{gateway}', [PaymentController::class, 'verify'])->name('settings.payment.verify');

    // روت‌های مدیریت کلیدهای API
    Route::post('/api-keys', [ApiKeyController::class, 'store'])->name('settings.api-keys.store');
    Route::delete('/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('settings.api-keys.destroy');
    Route::patch('/api-keys/{apiKey}/toggle', [ApiKeyController::class, 'toggleActive'])->name('settings.api-keys.toggle');
    Route::get('/api-keys/{apiKey}/preview', [ApiKeyController::class, 'preview'])->name('settings.api-keys.preview');
});

// روت عمومی مستندات کلید API
Route::get('/external/docs/{token}', [ApiKeyController::class, 'docs'])->name('settings.api-keys.docs');

// مسیرهای تنظیمات پرداخت سمت کاربر
Route::middleware(['web', 'auth'])->prefix('user')->name('user.')->group(function () {
    Route::get('/settings/payment', [UserPaymentSettingsController::class, 'edit'])->name('settings.payment');
    Route::post('/settings/payment', [UserPaymentSettingsController::class, 'update'])->name('settings.payment.update');
});

