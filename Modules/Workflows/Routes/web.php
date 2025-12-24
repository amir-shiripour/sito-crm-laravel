<?php

use Illuminate\Support\Facades\Route;
use Modules\Workflows\Http\Controllers\User\WorkflowController;

Route::middleware(['web', 'auth'])
    ->prefix('user/workflows')
    ->name('user.workflows.')
    ->group(function () {
        Route::get('/', [WorkflowController::class, 'index'])->name('index');
        Route::get('/create', [WorkflowController::class, 'create'])->name('create');
        Route::post('/', [WorkflowController::class, 'store'])->name('store');
        Route::get('/{workflow}', [WorkflowController::class, 'show'])->name('show');
        Route::get('/{workflow}/edit', [WorkflowController::class, 'edit'])->name('edit');
        Route::patch('/{workflow}', [WorkflowController::class, 'update'])->name('update');
        Route::delete('/{workflow}', [WorkflowController::class, 'destroy'])->name('destroy');

        Route::post('/{workflow}/stages', [WorkflowController::class, 'storeStage'])->name('stages.store');
        Route::patch('/{workflow}/stages/{stage}', [WorkflowController::class, 'updateStage'])->name('stages.update');
        Route::delete('/{workflow}/stages/{stage}', [WorkflowController::class, 'destroyStage'])->name('stages.destroy');

        Route::post('/{workflow}/stages/{stage}/actions', [WorkflowController::class, 'storeAction'])->name('actions.store');
        Route::patch('/{workflow}/stages/{stage}/actions/{action}', [WorkflowController::class, 'updateAction'])->name('actions.update');
        Route::delete('/{workflow}/stages/{stage}/actions/{action}', [WorkflowController::class, 'destroyAction'])->name('actions.destroy');
    });
