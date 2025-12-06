<?php

use Illuminate\Support\Facades\Route;
use Modules\FollowUps\Http\Controllers\User\FollowUpController;

Route::prefix('user')
    ->as('user.')
    ->middleware(['web', 'auth'])
    ->group(function () {
        Route::get('followups', [FollowUpController::class, 'index'])
            ->name('followups.index')
            ->middleware('can:followups.view');

        Route::get('followups/create', [FollowUpController::class, 'create'])
            ->name('followups.create')
            ->middleware('can:followups.create');

        Route::post('followups', [FollowUpController::class, 'store'])
            ->name('followups.store')
            ->middleware('can:followups.create');

        Route::get('followups/{followUp}', [FollowUpController::class, 'show'])
            ->name('followups.show')
            ->middleware('can:followups.view');

        Route::get('followups/{followUp}/edit', [FollowUpController::class, 'edit'])
            ->name('followups.edit')
            ->middleware('can:followups.edit');

        Route::put('followups/{followUp}', [FollowUpController::class, 'update'])
            ->name('followups.update')
            ->middleware('can:followups.edit');

        Route::delete('followups/{followUp}', [FollowUpController::class, 'destroy'])
            ->name('followups.destroy')
            ->middleware('can:followups.delete');
    });
