<?php

use Illuminate\Support\Facades\Route;
use Modules\Workflows\Http\Controllers\User\WorkflowController;

Route::middleware(['web', 'auth'])
    ->prefix('user/workflows')
    ->name('user.workflows.')
    ->group(function () {
        Route::get('/', [WorkflowController::class, 'index'])->name('index');
        Route::get('/kanban', [WorkflowController::class, 'kanban'])->name('kanban');
        Route::get('/create', [WorkflowController::class, 'create'])->name('create');
        Route::post('/', [WorkflowController::class, 'store'])->name('store');

        // Workflow Instance Actions (Static routes first to prevent wildcard overlap)
        Route::get('/instances', [WorkflowController::class, 'getInstances'])->name('instances.get');
        Route::get('/canvas-data', [WorkflowController::class, 'getCanvasData'])->name('instances.canvas-data');
        Route::post('/instances/start', [WorkflowController::class, 'startInstance'])->name('instances.start');
        Route::post('/instances/{instance}/advance', [WorkflowController::class, 'advanceInstance'])->name('instances.advance');
        Route::post('/instances/{instance}/go-back', [WorkflowController::class, 'goBackInstance'])->name('instances.go-back');
        Route::post('/instances/{instance}/cancel', [WorkflowController::class, 'cancelInstance'])->name('instances.cancel');
        Route::post('/instances/{instance}/restart', [WorkflowController::class, 'restartInstance'])->name('instances.restart');
        Route::post('/tasks/{task}/toggle', [WorkflowController::class, 'toggleTask'])->name('tasks.toggle');

        Route::get('/{workflow}', [WorkflowController::class, 'show'])->name('show');
        Route::get('/{workflow}/edit', [WorkflowController::class, 'edit'])->name('edit');
        Route::get('/{workflow}/designer', [WorkflowController::class, 'designer'])->name('designer');
        Route::post('/{workflow}/save-graph', [WorkflowController::class, 'saveGraph'])->name('save-graph');
        Route::patch('/{workflow}', [WorkflowController::class, 'update'])->name('update');
        Route::delete('/{workflow}', [WorkflowController::class, 'destroy'])->name('destroy');

        Route::post('/{workflow}/stages', [WorkflowController::class, 'storeStage'])->name('stages.store');
        Route::patch('/{workflow}/stages/{stage}', [WorkflowController::class, 'updateStage'])->name('stages.update');
        Route::delete('/{workflow}/stages/{stage}', [WorkflowController::class, 'destroyStage'])->name('stages.destroy');

        Route::post('/{workflow}/stages/{stage}/actions', [WorkflowController::class, 'storeAction'])->name('actions.store');
        Route::patch('/{workflow}/stages/{stage}/actions/{action}', [WorkflowController::class, 'updateAction'])->name('actions.update');
        Route::delete('/{workflow}/stages/{stage}/actions/{action}', [WorkflowController::class, 'destroyAction'])->name('actions.destroy');
    });

