<?php

use Illuminate\Support\Facades\Route;
use Modules\Services\App\Http\Controllers\{
    ServicesController,
    ProjectController,
    InvoiceController,
    StatusBuilderController,
    ServicesSettingsController,
    ServiceCategoryController,
    OrderController,
};

Route::middleware(['auth', 'verified'])->group(function () {

    // Categories
    Route::prefix('services/categories')
        ->name('services.categories.')
        ->group(function () {
            Route::resource('/', ServiceCategoryController::class)->parameters(['' => 'category']);
        });

    // Services Catalog
    Route::prefix('services/catalog')
        ->name('services.services.')
        ->group(function () {
            Route::resource('/', ServicesController::class)->parameters(['' => 'service']);
            Route::get('/{service}/custom-fields-json', [ServicesController::class, 'getCustomFieldsJson'])->name('custom-fields-json');
        });

    // Global Custom Fields Index
    Route::prefix('services/custom-fields')
        ->name('services.custom-fields.')
        ->group(function () {
            Route::get('/', [ServicesController::class, 'customFieldsIndex'])->name('index');
        });

    // Projects
    Route::prefix('services/projects')
        ->name('services.projects.')
        ->group(function () {
            Route::resource('/', ProjectController::class)->parameters(['' => 'project']);
            Route::post('/{project}/change-status', [ProjectController::class, 'changeStatus'])->name('change-status');
        });

    // Proformas
    Route::get('services/proformas', [InvoiceController::class, 'proformas'])->name('services.proformas.index');
    Route::get('services/proformas/create', [InvoiceController::class, 'createProforma'])->name('services.proformas.create');

    // Invoices
    Route::prefix('services/invoices')
        ->name('services.invoices.')
        ->group(function () {
            // Dedicated create route (registered before the resource's dynamic {invoice} routes)
            Route::get('/create', [InvoiceController::class, 'createInvoice'])->name('create');

            Route::resource('/', InvoiceController::class)->parameters(['' => 'invoice'])->except(['create']);

            Route::post('/store-proforma', [InvoiceController::class, 'storeProforma'])->name('storeProforma');
            Route::post('/{invoice}/convert-to-invoice', [InvoiceController::class, 'convertToInvoice'])->name('convertToInvoice');
            Route::post('/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('cancel');

            Route::get('/{invoice}/payment', [InvoiceController::class, 'createPayment'])->name('payment');
            Route::post('/{invoice}/payment', [InvoiceController::class, 'storePayment'])->name('payment.store');
            Route::post('/{invoice}/payment/{payment}/cancel', [InvoiceController::class, 'cancelPayment'])->name('cancelPayment');

            Route::get('/verify/{gateway}', [InvoiceController::class, 'verify'])->name('verify');
            Route::get('/{invoice}/print-view', [InvoiceController::class, 'printView'])->name('print-view');
            Route::get('/{invoice}/print', [InvoiceController::class, 'downloadPdf'])->name('print');
            Route::patch('/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('updateStatus');
            Route::post('/{invoice}/pay', [InvoiceController::class, 'pay'])->name('pay');
        });

    // Orders
    Route::prefix('services/orders')
        ->name('services.orders.')
        ->group(function () {
            Route::resource('/', OrderController::class)->parameters(['' => 'order']);
        });

    // Status Builder & Settings
    Route::prefix('services/status-builder')
        ->name('services.status-builder.')
        ->group(function () {
            Route::resource('/', StatusBuilderController::class)
                ->parameters(['' => 'status'])
                ->only(['index', 'store', 'update', 'destroy']);
            Route::post('/reorder', [StatusBuilderController::class, 'reorder'])->name('reorder');
        });

    Route::prefix('services/settings')
        ->name('services.settings.')
        ->group(function () {
            Route::get('/', [ServicesSettingsController::class, 'index'])->name('index');
            Route::put('/', [ServicesSettingsController::class, 'update'])->name('update');
            Route::get('/number-preview', [ServicesSettingsController::class, 'previewNumber'])->name('number-preview');
            Route::post('/seed-workflows', [ServicesSettingsController::class, 'seedWorkflows'])->name('seed-workflows');
        });
});
