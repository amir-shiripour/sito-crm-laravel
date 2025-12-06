<?php

use Illuminate\Support\Facades\Route;
use Modules\Reminders\Http\Controllers\User\ReminderController;

Route::prefix('user')
    ->as('user.')
    ->middleware(['web', 'auth'])
    ->group(function () {
        Route::get('reminders', [ReminderController::class, 'index'])
            ->name('reminders.index')
            ->middleware('can:reminders.view');

        Route::post('reminders', [ReminderController::class, 'store'])
            ->name('reminders.store')
            ->middleware('can:reminders.create');

        Route::delete('reminders/{reminder}', [ReminderController::class, 'destroy'])
            ->name('reminders.destroy')
            ->middleware('can:reminders.delete');
    });
