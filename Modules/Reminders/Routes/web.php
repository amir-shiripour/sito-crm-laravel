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
            ->middleware('can:reminders.edit');

        // به‌روزرسانی (ویرایش)
        Route::put('reminders/{reminder}', [ReminderController::class, 'update'])
            ->name('reminders.update')
            ->middleware('can:reminders.edit');

        // تغییر وضعیت تکی (مثلاً از لیست)
        Route::patch('reminders/{reminder}/status', [ReminderController::class, 'updateStatus'])
            ->name('reminders.update-status')
            ->middleware('can:reminders.edit');

        // تغییر وضعیت گروهی
        Route::match(['post', 'patch'], 'reminders/bulk-status', [ReminderController::class, 'bulkUpdateStatus'])
            ->name('reminders.bulk-status')
            ->middleware('can:reminders.edit');

        // تعویق
        Route::patch('reminders/{reminder}/snooze', [ReminderController::class, 'snooze'])
            ->name('reminders.snooze')
            ->middleware('can:reminders.edit');

        // تاریخچه تعویق
        Route::get('reminders/{reminder}/snooze-history', [ReminderController::class, 'snoozeHistory'])
            ->name('reminders.snooze-history')
            ->middleware('can:reminders.edit');

        // انجام موجودیت مرتبط
        Route::patch('reminders/{reminder}/related-done', [ReminderController::class, 'markRelatedDone'])
            ->name('reminders.related-done')
            ->middleware('can:reminders.edit');

        // درحال انجام کردن موجودیت مرتبط
        Route::patch('reminders/{reminder}/progress-related', [ReminderController::class, 'progressRelated'])
            ->name('reminders.progress-related')
            ->middleware('can:reminders.edit');

        // حذف
        Route::delete('reminders/{reminder}', [ReminderController::class, 'destroy'])
            ->name('reminders.destroy')
            ->middleware('can:reminders.delete');
    });
