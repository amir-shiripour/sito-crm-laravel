<?php

use Illuminate\Support\Facades\Route;
use Modules\Warehouse\App\Http\Controllers\DashboardController;
use Modules\Warehouse\App\Http\Controllers\WarehouseController;
use Modules\Warehouse\App\Http\Controllers\SettingsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Route names are automatically prefixed by 'admin.warehouse.'
| URL prefix is 'admin/warehouse'
|
*/

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Inventory Warehouses
Route::get('inventory', [WarehouseController::class, 'inventoryIndex'])->name('inventory.index');

// Asset Warehouses
Route::get('assets', [WarehouseController::class, 'assetsIndex'])->name('assets.index');

// Warehouse CRUD & Operations
Route::get('warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
Route::post('warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
Route::post('warehouses/{warehouse}/toggle-status', [WarehouseController::class, 'toggleStatus'])->name('warehouses.toggle-status');

// Settings
Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
