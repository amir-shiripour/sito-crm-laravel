<?php

use Illuminate\Support\Facades\Route;
use Modules\Booking\Http\Controllers\Admin\BookingSettingsController;

Route::middleware(['web', 'auth', 'role:super-admin|admin', 'permission:modules.manage'])
    ->prefix('admin/booking')
    ->name('admin.booking.')
    ->group(function () {
        Route::get('/settings/label', [BookingSettingsController::class, 'editLabel'])->name('settings.label.edit');
        Route::put('/settings/label', [BookingSettingsController::class, 'updateLabel'])->name('settings.label.update');
    });
