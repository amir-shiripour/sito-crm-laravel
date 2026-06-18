<?php

use Illuminate\Support\Facades\Route;
use Modules\Reminders\Http\Controllers\User\ReminderController;
use Modules\Reminders\Http\Controllers\User\ReminderSettingsController;

Route::prefix('user')
    ->as('user.')
    ->middleware(['web', 'auth'])
    ->group(function () {

        // تنظیمات یادآوری و تعویق
        Route::get('reminders/settings', [ReminderSettingsController::class, 'index'])
            ->name('reminders.settings.index')
            ->middleware('can:reminders.settings.view');

        Route::put('reminders/settings', [ReminderSettingsController::class, 'update'])
            ->name('reminders.settings.update')
            ->middleware('can:reminders.settings.manage');

        // لیست یادآوری‌ها با فیلترها
        Route::get('reminders', [ReminderController::class, 'index'])
            ->name('reminders.index');

        // ایجاد
        Route::post('reminders', [ReminderController::class, 'store'])
            ->name('reminders.store')
            ->middleware('can:reminders.create');

        // فرم ویرایش
        Route::get('reminders/{reminder}/edit', [ReminderController::class, 'edit'])
            ->name('reminders.edit')
            ->middleware('canAny:reminders.edit,reminders.manage');

        // به‌روزرسانی (ویرایش)
        Route::put('reminders/{reminder}', [ReminderController::class, 'update'])
            ->name('reminders.update')
            ->middleware('canAny:reminders.edit,reminders.manage');

        // تغییر وضعیت تکی (مثلاً از لیست)
        Route::patch('reminders/{reminder}/status', [ReminderController::class, 'updateStatus'])
            ->name('reminders.update-status')
            ->middleware('canAny:reminders.edit,reminders.manage');

        // تغییر وضعیت گروهی
        Route::match(['post', 'patch'], 'reminders/bulk-status', [ReminderController::class, 'bulkUpdateStatus'])
            ->name('reminders.bulk-status')
            ->middleware('canAny:reminders.edit,reminders.manage');

        // تعویق
        Route::patch('reminders/{reminder}/snooze', [ReminderController::class, 'snooze'])
            ->name('reminders.snooze')
            ->middleware('canAny:reminders.edit,reminders.manage');

        // تاریخچه تعویق
        Route::get('reminders/{reminder}/snooze-history', [ReminderController::class, 'snoozeHistory'])
            ->name('reminders.snooze-history')
            ->middleware('canAny:reminders.edit,reminders.manage');

        // انجام موجودیت مرتبط
        Route::patch('reminders/{reminder}/related-done', [ReminderController::class, 'markRelatedDone'])
            ->name('reminders.related-done')
            ->middleware('canAny:reminders.edit,reminders.manage');

        // درحال انجام کردن موجودیت مرتبط
        Route::patch('reminders/{reminder}/progress-related', [ReminderController::class, 'progressRelated'])
            ->name('reminders.progress-related')
            ->middleware('canAny:reminders.edit,reminders.manage');

        // حذف
        Route::delete('reminders/{reminder}', [ReminderController::class, 'destroy'])
            ->name('reminders.destroy')
            ->middleware('can:reminders.delete');
    });
