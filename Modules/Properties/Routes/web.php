<?php

use Illuminate\Support\Facades\Route;
use Modules\Properties\App\Http\Controllers\PropertyController as PublicPropertyController;
use Modules\Properties\App\Http\Controllers\User\PropertyController as UserPropertyController;
use Modules\Properties\App\Http\Controllers\User\OwnerController;
use Modules\Properties\App\Http\Controllers\User\BuildingController;
use Modules\Properties\App\Http\Controllers\User\SettingsController;
use Modules\Properties\App\Http\Controllers\User\AttributesController;
use Modules\Properties\App\Http\Controllers\User\CategoryController;
use Modules\Properties\App\Http\Controllers\User\AIController;
use Modules\Properties\App\Livewire\Settings\PropertyStatusesManager;
use Modules\Properties\App\Http\Controllers\User\ImportController;

// Public Routes
Route::middleware(['web'])->group(function() {
    Route::group(['prefix' => 'properties', 'as' => 'properties.'], function() {
        Route::get('/', [PublicPropertyController::class, 'index'])->name('index');
        Route::get('/map', [PublicPropertyController::class, 'map'])->name('map');
        Route::get('/{slug}', [PublicPropertyController::class, 'show'])->name('show');
    });
});

// User Panel Routes
Route::middleware(['web', 'auth'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        Route::prefix('properties')
            ->name('properties.')
            ->group(function () {
                Route::get('/', [UserPropertyController::class, 'index'])->name('index');
                Route::get('/create', [UserPropertyController::class, 'create'])->name('create');
                Route::post('/', [UserPropertyController::class, 'store'])->name('store');

                // Pricing Step
                Route::get('/{property}/pricing', [UserPropertyController::class, 'pricing'])->name('pricing');
                Route::put('/{property}/pricing', [UserPropertyController::class, 'updatePricing'])->name('pricing.update');

                // Details Step
                Route::get('/{property}/details', [UserPropertyController::class, 'details'])->name('details');
                Route::put('/{property}/details', [UserPropertyController::class, 'updateDetails'])->name('details.update');

                // Features Step
                Route::get('/{property}/features', [UserPropertyController::class, 'features'])->name('features');
                Route::put('/{property}/features', [UserPropertyController::class, 'updateFeatures'])->name('features.update');

                Route::get('/{property}/edit', [UserPropertyController::class, 'edit'])->name('edit');
                Route::put('/{property}', [UserPropertyController::class, 'update'])->name('update');
                Route::delete('/{property}', [UserPropertyController::class, 'destroy'])->name('destroy');

                // Image Deletion
                Route::delete('/image/{image}', [UserPropertyController::class, 'destroyImage'])->name('image.destroy');

                // Owner Management (Quick Store from Modal)
                Route::post('/owners', [UserPropertyController::class, 'storeOwner'])->name('owners.store');

                // Owner Search
                Route::get('/owners/search', [OwnerController::class, 'search'])->name('owners.search');

                // Building Search
                Route::get('/buildings/search', [BuildingController::class, 'search'])->name('buildings.search');

                // Agent Search
                Route::get('/agents/search', [UserPropertyController::class, 'searchAgents'])->name('agents.search');

                // Category Management
                Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);

                // AI Routes
                Route::post('/ai/complete', [AIController::class, 'completeProperty'])->name('ai.complete');
                Route::post('/ai/search', [AIController::class, 'searchProperty'])->name('ai.search');
            });

        // Full Owner Management
        Route::resource('property-owners', OwnerController::class)
            ->except(['show', 'create', 'edit'])
            ->parameters(['property-owners' => 'owner']);

        // Full Building Management
        Route::resource('property-buildings', BuildingController::class)
            ->except(['show', 'create', 'edit'])
            ->parameters(['property-buildings' => 'building']);

        // Settings Routes
        Route::prefix('settings/properties')
            ->name('settings.properties.')
            ->group(function () {
                Route::get('/', [SettingsController::class, 'index'])->name('index');
                Route::post('/', [SettingsController::class, 'update'])->name('update');
                Route::get('/statuses', PropertyStatusesManager::class)->name('statuses');

                // Attributes Management
                Route::get('/attributes', [AttributesController::class, 'index'])->name('attributes.index');
                Route::post('/attributes', [AttributesController::class, 'store'])->name('attributes.store');
                Route::put('/attributes/{attribute}', [AttributesController::class, 'update'])->name('attributes.update');
                Route::delete('/attributes/{attribute}', [AttributesController::class, 'destroy'])->name('attributes.destroy');
                Route::get('/import', [ImportController::class, 'index'])->name('import');
            });
    });
