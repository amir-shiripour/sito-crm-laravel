<?php

use Illuminate\Support\Facades\Route;
use Modules\Reminders\Http\Controllers\User\ReminderController;

Route::prefix('user')
    ->as('user.')
    ->middleware(['web', 'auth'])
    ->group(function () {

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

        // حذف
        Route::delete('reminders/{reminder}', [ReminderController::class, 'destroy'])
            ->name('reminders.destroy')
            ->middleware('can:reminders.delete');
    });
