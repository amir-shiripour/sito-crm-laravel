<?php

use Illuminate\Support\Facades\Route;
use Modules\Notifications\Http\Controllers\Admin\BroadcastNotificationController;
use Modules\Notifications\Http\Controllers\User\NotificationController;
use Modules\Notifications\Http\Controllers\User\NotificationPreferenceController;

/*
|--------------------------------------------------------------------------
| مسیرهای کاربری اعلان‌ها (User Routes)
|--------------------------------------------------------------------------
*/
Route::prefix('user')
    ->as('user.')
    ->middleware(['web', 'auth'])
    ->group(function () {
        
        // مسیرهای موجود (بدون تغییر نام جهت حفظ ۱۰۰٪ سازگاری)
        Route::get('notifications', [NotificationController::class, 'index'])
            ->name('notifications.index')
            ->middleware('can:notifications.view');

        Route::patch('notifications/mark-as-read', [NotificationController::class, 'markAllAsRead'])
            ->name('notifications.mark-all-read')
            ->middleware('can:notifications.view');

        Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.mark-read')
            ->middleware('can:notifications.view');

        Route::delete('notifications/{id}', [NotificationController::class, 'destroy'])
            ->name('notifications.destroy')
            ->middleware('can:notifications.view');

        // مسیرهای جدید: عملیات دسته‌جمعی و پولینگ زنده
        Route::patch('notifications/bulk/mark-read', [NotificationController::class, 'bulkMarkAsRead'])
            ->name('notifications.bulk-read')
            ->middleware('can:notifications.view');

        Route::delete('notifications/bulk/destroy', [NotificationController::class, 'bulkDestroy'])
            ->name('notifications.bulk-destroy')
            ->middleware('can:notifications.view');

        Route::get('notifications/api/unread-count', [NotificationController::class, 'unreadCount'])
            ->name('notifications.unread-count')
            ->middleware('can:notifications.view');

        Route::get('notifications/api/latest', [NotificationController::class, 'latest'])
            ->name('notifications.latest')
            ->middleware('can:notifications.view');

        // تنظیمات و ترجیحات اعلان‌های کاربر
        Route::get('notifications/preferences/settings', [NotificationPreferenceController::class, 'index'])
            ->name('notifications.settings')
            ->middleware('can:notifications.settings');

        Route::post('notifications/preferences/settings', [NotificationPreferenceController::class, 'update'])
            ->name('notifications.settings.update')
            ->middleware('can:notifications.settings');
    });

/*
|--------------------------------------------------------------------------
| مسیرهای مدیریتی اعلان‌ها و برودکست (Admin Routes)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->as('admin.')
    ->middleware(['web', 'auth'])
    ->group(function () {

        Route::prefix('notifications/broadcast')
            ->as('notifications.broadcast.')
            ->middleware('can:notifications.broadcast')
            ->group(function () {
                Route::get('/', [BroadcastNotificationController::class, 'index'])->name('index');
                Route::get('/create', [BroadcastNotificationController::class, 'create'])->name('create');
                Route::post('/', [BroadcastNotificationController::class, 'store'])->name('store');
                Route::delete('/{id}', [BroadcastNotificationController::class, 'destroy'])->name('destroy');
            });
    });
