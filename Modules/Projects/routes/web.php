<?php

use Illuminate\Support\Facades\Route;
use Modules\Projects\App\Http\Controllers\{
    ProjectsController,
    ProjectCategoryController,
    ProjectsTaskController,
    ProjectsChecklistController,
    ProjectsDocumentController,
    ProjectsMessageController,
    ProjectsStatusBuilderController,
    ProjectsSettingsController,
    ProjectsTimeLogController,
    ProjectsTaskCommentController,
    ProjectsChecklistCommentController,
    ProjectsPhaseController,
    ProjectsMessageSseController,
    ProjectsTaskSseController,
    ProjectsRoleController,
    ProjectsTemplateController,
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])
    ->prefix('user')->group(function () {

        // Phase, Group & Task Templates
        Route::prefix('projects/templates')
            ->name('projects.templates.')
            ->group(function () {
                Route::post('/{template}/apply/{project}', [ProjectsTemplateController::class, 'apply'])->name('apply');
                Route::resource('/', ProjectsTemplateController::class)
                    ->parameters(['' => 'template']);
            });

        // Project Categories
        Route::prefix('projects/categories')
            ->name('projects.categories.')
            ->group(function () {
                Route::resource('/', ProjectCategoryController::class)
                    ->parameters(['' => 'category']);
            });

        // Main Projects CRUD & Sub-resources
        Route::prefix('projects/projects')
            ->name('projects.projects.')
            ->group(function () {
                Route::get('/users/search', [ProjectsController::class, 'searchUsers'])->name('users.search');
                Route::get('/{project}/dashboard-data', [ProjectsController::class, 'dashboardData'])->name('dashboardData');
                Route::patch('/{project}/status', [ProjectsController::class, 'updateStatus'])->name('updateStatus');
                Route::post('/{project}/cancel', [ProjectsController::class, 'cancel'])->name('cancel');

                // Tasks
                Route::prefix('/{project}/tasks')->name('tasks.')->group(function () {
                    Route::get('/sse', [ProjectsTaskSseController::class, 'stream'])->name('sse');
                    Route::get('/', [ProjectsTaskController::class, 'index'])->name('index');
                    Route::post('/', [ProjectsTaskController::class, 'store'])->name('store');
                    Route::post('/bulk-destroy', [ProjectsTaskController::class, 'bulkDestroy'])->name('bulkDestroy');
                    Route::put('/{task}', [ProjectsTaskController::class, 'update'])->name('update');
                    Route::delete('/{task}', [ProjectsTaskController::class, 'destroy'])->name('destroy');
                    Route::patch('/{task}/status', [ProjectsTaskController::class, 'updateStatus'])->name('status');
                    Route::post('/{task}/cancel', [ProjectsTaskController::class, 'cancel'])->name('cancel');
                    Route::post('/reorder', [ProjectsTaskController::class, 'reorder'])->name('reorder');

                    // Checklists
                    Route::prefix('/{task}/checklist')->name('checklist.')->group(function () {
                        Route::get('/{item}', [ProjectsChecklistController::class, 'show'])->name('show');
                        Route::post('/', [ProjectsChecklistController::class, 'store'])->name('store');
                        Route::post('/reorder', [ProjectsChecklistController::class, 'reorder'])->name('reorder');
                        Route::post('/{item}/toggle', [ProjectsChecklistController::class, 'toggle'])->name('toggle');
                        Route::patch('/{item}/status', [ProjectsChecklistController::class, 'updateStatus'])->name('status');
                        Route::post('/{item}/cancel', [ProjectsChecklistController::class, 'cancel'])->name('cancel');
                        Route::put('/{item}', [ProjectsChecklistController::class, 'update'])->name('update');
                        Route::delete('/{item}', [ProjectsChecklistController::class, 'destroy'])->name('destroy');
                    });

                    // Checklist Item Comments
                    Route::prefix('/{task}/checklist/{item}/comments')->name('checklist.comments.')->group(function () {
                        Route::post('/', [ProjectsChecklistCommentController::class, 'store'])->name('store');
                        Route::delete('/{comment}', [ProjectsChecklistCommentController::class, 'destroy'])->name('destroy');
                    });

                    // Time Logs for Tasks
                    Route::prefix('/{task}/time-logs')->name('time-logs.')->group(function () {
                        Route::post('/start', [ProjectsTimeLogController::class, 'start'])->name('start');
                        Route::post('/{timeLog}/stop', [ProjectsTimeLogController::class, 'stop'])->name('stop');
                        Route::post('/manual', [ProjectsTimeLogController::class, 'storeManual'])->name('manual');
                    });

                    // Comments for Tasks
                    Route::prefix('/{task}/comments')->name('comments.')->group(function () {
                        Route::post('/', [ProjectsTaskCommentController::class, 'store'])->name('store');
                        Route::delete('/{comment}', [ProjectsTaskCommentController::class, 'destroy'])->name('destroy');
                    });
                });

                // Project-level Time Logs
                Route::prefix('/{project}/time-logs')->name('time-logs.')->group(function () {
                    Route::get('/', [ProjectsTimeLogController::class, 'index'])->name('index');
                    Route::delete('/{timeLog}', [ProjectsTimeLogController::class, 'destroy'])->name('destroy');
                });

                // Phases (فازها و دسته‌بندی کارها)
                Route::prefix('/{project}/phases')->name('phases.')->group(function () {
                    Route::post('/', [ProjectsPhaseController::class, 'store'])->name('store');
                    Route::post('/bulk-destroy', [ProjectsPhaseController::class, 'bulkDestroy'])->name('bulkDestroy');
                    Route::put('/{phase}', [ProjectsPhaseController::class, 'update'])->name('update');
                    Route::delete('/{phase}', [ProjectsPhaseController::class, 'destroy'])->name('destroy');
                });

                // Messages
                Route::prefix('/{project}/messages')->name('messages.')->group(function () {
                    Route::get('/sse', [ProjectsMessageSseController::class, 'stream'])->name('sse');
                    Route::get('/', [ProjectsMessageController::class, 'index'])->name('index');
                    Route::post('/', [ProjectsMessageController::class, 'store'])->name('store');
                    Route::delete('/{message}', [ProjectsMessageController::class, 'destroy'])->name('destroy');
                    Route::post('/{message}/pin', [ProjectsMessageController::class, 'togglePin'])->name('pin');
                });

                // Documents
                Route::prefix('/{project}/documents')->name('documents.')->group(function () {
                    Route::get('/', [ProjectsDocumentController::class, 'index'])->name('index');
                    Route::get('/create', [ProjectsDocumentController::class, 'create'])->name('create');
                    Route::post('/', [ProjectsDocumentController::class, 'store'])->name('store');
                    Route::get('/{document}', [ProjectsDocumentController::class, 'show'])->name('show');
                    Route::get('/{document}/download', [ProjectsDocumentController::class, 'download'])->name('download');
                    Route::delete('/{document}', [ProjectsDocumentController::class, 'destroy'])->name('destroy');
                });

                Route::resource('/', ProjectsController::class)->parameters(['' => 'project']);
            });

        // Status Builder
        Route::prefix('projects/status-builder')
            ->name('projects.status-builder.')
            ->group(function () {
                Route::post('/seed', [ProjectsStatusBuilderController::class, 'seed'])->name('seed');
                Route::post('/reorder', [ProjectsStatusBuilderController::class, 'reorder'])->name('reorder');
                Route::resource('/', ProjectsStatusBuilderController::class)
                    ->parameters(['' => 'status'])
                    ->only(['index', 'store', 'update', 'destroy']);
            });

        // Settings
        Route::prefix('projects/settings')
            ->name('projects.settings.')
            ->group(function () {
                Route::get('/', [ProjectsSettingsController::class, 'index'])->name('index');
                Route::put('/', [ProjectsSettingsController::class, 'update'])->name('update');
                Route::get('/code-preview', [ProjectsSettingsController::class, 'previewCode'])->name('code-preview');
                Route::post('/seed-statuses', [ProjectsSettingsController::class, 'seedStatuses'])->name('seed-statuses');
            });

        // Project Roles & Permissions
        Route::prefix('projects/roles')
            ->name('projects.roles.')
            ->group(function () {
                Route::get('/', [ProjectsRoleController::class, 'index'])->name('index');
                Route::post('/', [ProjectsRoleController::class, 'store'])->name('store');
                Route::put('/{role}', [ProjectsRoleController::class, 'update'])->name('update');
                Route::delete('/{role}', [ProjectsRoleController::class, 'destroy'])->name('destroy');
            });
    });
