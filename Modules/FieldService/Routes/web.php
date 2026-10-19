<?php

use Illuminate\Support\Facades\Route;
use Modules\FieldService\App\Http\Controllers\Admin\BuildingsManagementController;
use Modules\FieldService\App\Http\Controllers\Admin\DashboardController;
use Modules\FieldService\App\Http\Controllers\Admin\FleetMonitoringController;
use Modules\FieldService\App\Http\Controllers\Admin\OrdersManagementController;
use Modules\FieldService\App\Http\Controllers\Admin\SettingsController;
use Modules\FieldService\App\Http\Controllers\Admin\SettlementsController;
use Modules\FieldService\App\Http\Controllers\Admin\TechnicianManagementController;
use Modules\FieldService\App\Http\Controllers\Admin\VendorsManagementController;
use Modules\FieldService\App\Http\Controllers\B2BPortalController;
use Modules\FieldService\App\Http\Controllers\CustomerPortalController;
use Modules\FieldService\App\Http\Controllers\TechnicianPortalController;

/*
|--------------------------------------------------------------------------
| Web Routes for FieldService / Nasab-Bashi
|--------------------------------------------------------------------------
*/

// CRM Authenticated Operator / Admin routes
Route::middleware(['auth'])->prefix('user/fieldservice')->name('user.fieldservice.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/fleet', [FleetMonitoringController::class, 'index'])->name('fleet');
    Route::get('/orders', [OrdersManagementController::class, 'index'])->name('orders.index');
    Route::get('/technicians', [TechnicianManagementController::class, 'index'])->name('technicians.index');
    Route::get('/buildings', [BuildingsManagementController::class, 'index'])->name('buildings.index');
    Route::get('/vendors', [VendorsManagementController::class, 'index'])->name('vendors.index');
    Route::get('/settlements', [SettlementsController::class, 'index'])->name('settlements.index');
    Route::get('/settlements/export-batch', [SettlementsController::class, 'exportBatch'])->name('settlements.export');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
});

// Interactive Web-App Portals (Customer, Technician, B2B)
Route::middleware(['web'])->prefix('fieldservice')->name('fieldservice.')->group(function () {
    Route::get('/customer', [CustomerPortalController::class, 'index'])->name('customer');
    Route::get('/technician', [TechnicianPortalController::class, 'index'])->name('technician');
    Route::get('/b2b', [B2BPortalController::class, 'index'])->name('b2b');
});
