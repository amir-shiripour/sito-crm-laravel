<?php

use Illuminate\Support\Facades\Route;
use Modules\Workflows\Http\Controllers\User\WorkflowController;

Route::middleware(['web', 'auth'])
    ->prefix('user/workflows')
    ->name('user.workflows.')
    ->group(function () {
        Route::get('/', [WorkflowController::class, 'index'])->name('index');
        Route::get('/{workflow}', [WorkflowController::class, 'show'])->name('show');
    });
