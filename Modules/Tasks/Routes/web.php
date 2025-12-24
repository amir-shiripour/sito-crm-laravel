<?php

use Illuminate\Support\Facades\Route;
use Modules\Tasks\Http\Controllers\User\TaskController;

Route::prefix('user')
    ->as('user.')
    ->middleware(['web', 'auth'])
    ->group(function () {
        Route::get('tasks', [TaskController::class, 'index'])
            ->name('tasks.index')
            ->middleware('can:tasks.view');

        Route::get('tasks/create', [TaskController::class, 'create'])
            ->name('tasks.create')
            ->middleware('can:tasks.create');

        Route::post('tasks', [TaskController::class, 'store'])
            ->name('tasks.store')
            ->middleware('can:tasks.create');

        Route::get('tasks/{task}', [TaskController::class, 'show'])
            ->name('tasks.show')
            ->middleware('can:tasks.view');

        Route::get('tasks/{task}/edit', [TaskController::class, 'edit'])
            ->name('tasks.edit')
            ->middleware('can:tasks.edit');

        Route::put('tasks/{task}', [TaskController::class, 'update'])
            ->name('tasks.update')
            ->middleware('can:tasks.edit');

        Route::delete('tasks/{task}', [TaskController::class, 'destroy'])
            ->name('tasks.destroy')
            ->middleware('can:tasks.delete');
    });
