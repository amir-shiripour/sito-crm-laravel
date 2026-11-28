<?php

use Illuminate\Support\Facades\Route;
use Modules\Warehouse\App\Http\Controllers\DashboardController;
use Modules\Warehouse\App\Http\Controllers\WarehouseController;
use Modules\Warehouse\App\Http\Controllers\ItemController;
use Modules\Warehouse\App\Http\Controllers\CategoryController;
use Modules\Warehouse\App\Http\Controllers\SettingsController;
use Modules\Warehouse\App\Http\Controllers\PurchaseController;
use Modules\Warehouse\App\Http\Controllers\TransferController;
use Modules\Warehouse\App\Http\Controllers\StockController;
use Modules\Warehouse\App\Http\Controllers\ReorderAlertController;
use Modules\Warehouse\App\Http\Controllers\ConsumptionController;

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
Route::get('inventory/pdf', [WarehouseController::class, 'inventoryPdf'])->name('inventory.pdf');

// Live Warehouse Stock Levels (موجودی لحظه‌ای انبارها)
Route::get('stock-levels', [StockController::class, 'index'])->name('stock-levels.index');
Route::get('stock-levels/pdf', [StockController::class, 'exportPdf'])->name('stock-levels.pdf');

// Reorder Point & Stock Alerts (هشدار نقطه سفارش)
Route::get('reorder-alerts', [ReorderAlertController::class, 'index'])->name('reorder-alerts.index');
Route::get('reorder-alerts/pdf', [ReorderAlertController::class, 'exportPdf'])->name('reorder-alerts.pdf');
Route::patch('reorder-alerts/{item}/thresholds', [ReorderAlertController::class, 'updateThresholds'])->name('reorder-alerts.update-thresholds');

// Asset Warehouses
Route::get('assets', [WarehouseController::class, 'assetsIndex'])->name('assets.index');
Route::get('assets/pdf', [WarehouseController::class, 'assetsPdf'])->name('assets.pdf');

// Warehouse CRUD & Operations
Route::get('users/search', [WarehouseController::class, 'searchUsers'])->name('users.search');
Route::get('warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
Route::post('warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
Route::post('warehouses/{warehouse}/toggle-status', [WarehouseController::class, 'toggleStatus'])->name('warehouses.toggle-status');

// Items & Commodities
Route::get('items/suggest-code', [ItemController::class, 'suggestCode'])->name('items.suggest-code');
Route::get('items/pdf', [ItemController::class, 'exportPdf'])->name('items.pdf');
Route::get('items/{item}/pdf', [ItemController::class, 'showPdf'])->name('items.show-pdf');
Route::get('items/import', [ItemController::class, 'importForm'])->name('items.import.form');
Route::post('items/import', [ItemController::class, 'importSubmit'])->name('items.import.submit');
Route::resource('items', ItemController::class);

// Purchase Receipts (رسید خرید و ورود کالا به انبار)
Route::get('purchases/search-items', [PurchaseController::class, 'searchItems'])->name('purchases.search-items');
Route::get('purchases/pdf', [PurchaseController::class, 'exportPdf'])->name('purchases.pdf');
Route::get('purchases/{purchase}/pdf', [PurchaseController::class, 'showPdf'])->name('purchases.show-pdf');
Route::get('purchases/import', [PurchaseController::class, 'importForm'])->name('purchases.import.form');
Route::post('purchases/import', [PurchaseController::class, 'importSubmit'])->name('purchases.import.submit');
Route::resource('purchases', PurchaseController::class);

// Warehouse Transfers & Requisitions (درخواست و انتقال کالا بین انبارها)
Route::get('transfers/search-items', [TransferController::class, 'searchItems'])->name('transfers.search-items');
Route::get('transfers/pdf', [TransferController::class, 'exportPdf'])->name('transfers.pdf');
Route::get('transfers/{transfer}/pdf', [TransferController::class, 'showPdf'])->name('transfers.show-pdf');
Route::get('transfers/import', [TransferController::class, 'importForm'])->name('transfers.import.form');
Route::post('transfers/import', [TransferController::class, 'importSubmit'])->name('transfers.import.submit');
Route::post('transfers/{transfer}/fulfill', [TransferController::class, 'fulfill'])->name('transfers.fulfill');
Route::post('transfers/{transfer}/reject', [TransferController::class, 'reject'])->name('transfers.reject');
Route::resource('transfers', TransferController::class)->except(['edit', 'update']);

// Warehouse Issues & Consumption (خروج و مصرف کالا)
Route::get('consumptions/search-items', [ConsumptionController::class, 'searchItems'])->name('consumptions.search-items');
Route::get('consumptions/search-clients', [ConsumptionController::class, 'searchClients'])->name('consumptions.search-clients');
Route::get('consumptions/pdf', [ConsumptionController::class, 'exportPdf'])->name('consumptions.pdf');
Route::get('consumptions/{consumption}/pdf', [ConsumptionController::class, 'showPdf'])->name('consumptions.show-pdf');
Route::get('consumptions/import', [ConsumptionController::class, 'importForm'])->name('consumptions.import.form');
Route::post('consumptions/import', [ConsumptionController::class, 'importSubmit'])->name('consumptions.import.submit');
Route::resource('consumptions', ConsumptionController::class)->except(['edit', 'update']);

// Categories & Subcategories
Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);

// Settings
Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
