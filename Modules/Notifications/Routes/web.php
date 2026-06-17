<?php

use Illuminate\Support\Facades\Route;
use Modules\Notifications\Http\Controllers\User\NotificationController;

Route::prefix('user')
    ->as('user.')
    ->middleware(['web', 'auth'])
    ->group(function () {
        
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
            ->middleware('can:notifications.manage');
    });
