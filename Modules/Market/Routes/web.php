<?php

use Illuminate\Support\Facades\Route;
use Modules\Market\App\Http\Controllers\MarketController;
use Modules\Market\App\Http\Controllers\User\CartController;
use Modules\Market\App\Http\Controllers\User\CheckoutController;
use Modules\Market\App\Http\Middleware\CheckVendorStatus;
use Modules\Market\App\Http\Middleware\CheckMultiVendorMode;
use Modules\Market\App\Http\Controllers\User\VendorProductController;
use Modules\Market\App\Http\Controllers\Admin\VendorController;
use Modules\Market\App\Http\Controllers\User\MarketDashboardController;
use Modules\Market\App\Http\Controllers\Admin\MasterProductController;
use Modules\Market\App\Livewire\Admin\CheckoutFormManager;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Public Facing Routes ---
Route::middleware(['web'])->group(function() {

    // Shop pages (e.g., /shop, /shop/product/slug)
    Route::group(['prefix' => 'shop', 'as' => 'market.public.'], function() {
        Route::get('/', [MarketController::class, 'index'])->name('index');
        Route::get('/category', [MarketController::class, 'category'])->name('category');
        Route::get('/category/{slug}', [MarketController::class, 'category'])->name('category.show');
        Route::get('/product/{slug}', [MarketController::class, 'show'])->name('product.show');
    });

    // Cart page
    Route::get('/cart', [CartController::class, 'index'])->name('market.cart.index');

    // Checkout page - NOTE: Auth middleware is removed, logic is handled in the Livewire component now.
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('market.checkout.index');

    // --- New Checkout Flow Routes ---
    Route::group(['prefix' => 'market/checkout', 'as' => 'market.checkout.'], function() {
        // Route to initiate payment process after order is created by Livewire
        Route::get('/process/{order}', [CheckoutController::class, 'process'])->name('process')->middleware('auth:client');

        // Payment gateway callback
        Route::any('/callback', [CheckoutController::class, 'callback'])->name('callback');

        // Success and Failed pages
        Route::get('/success/{order}', [CheckoutController::class, 'success'])->name('success')->middleware('auth:client');
        Route::get('/failed/{order}', [CheckoutController::class, 'failed'])->name('failed')->middleware('auth:client');
    });

});


// --- Authenticated User & Admin Routes ---
Route::group(['prefix' => 'user', 'as' => 'user.', 'middleware' => ['web', 'auth']], function () {

    Route::prefix('market')->name('market.')->group(function () {

        Route::get('dashboard', [MarketDashboardController::class, 'index'])->name('dashboard')->middleware('permission:market.dashboard.view');

        // Vendor Section
        Route::prefix('vendor')
            ->name('vendor.')
            ->middleware(['permission:market.products.view', CheckVendorStatus::class])
            ->group(function () {
                Route::resource('products', VendorProductController::class);
                Route::view('warehouses', 'market::user.warehouses.index')->name('warehouses.index');
                Route::view('warehouse-stock', 'market::admin.warehouse.stock')->name('warehouse-stock.index');
            });

        // Admin Section
        Route::middleware([CheckMultiVendorMode::class])->group(function () {
            Route::get('vendors', [VendorController::class, 'index'])->name('vendors.index')->middleware('permission:market.vendors.view');
            Route::get('vendors/{vendor}', [VendorController::class, 'show'])->name('vendors.show')->middleware('permission:market.vendors.view');
            Route::resource('vendors', VendorController::class)->except(['index', 'show'])->middleware('permission:market.vendors.manage');
            Route::view('vendor-products/review', 'market::admin.vendor-products.review')
                ->name('vendor-products.review')
                ->middleware(['permission:market.vendors.manage']);
        });

        // Catalog Management
        Route::get('master-products', [MasterProductController::class, 'index'])->name('master-products.index')->middleware('permission:market.products.view');
        Route::get('master-products/create', [MasterProductController::class, 'create'])->name('master-products.create')->middleware('permission:market.master-products.manage');
        Route::post('master-products', [MasterProductController::class, 'store'])->name('master-products.store')->middleware('permission:market.master-products.manage');
        Route::get('master-products/{master_product}/edit', [MasterProductController::class, 'edit'])->name('master-products.edit')->middleware('permission:market.master-products.manage');
        Route::put('master-products/{master_product}', [MasterProductController::class, 'update'])->name('master-products.update')->middleware('permission:market.master-products.manage');
        Route::delete('master-products/{master_product}', [MasterProductController::class, 'destroy'])->name('master-products.destroy')->middleware('permission:market.master-products.manage');

        Route::get('checkout-forms', CheckoutFormManager::class)->name('checkout-forms.index')->middleware('permission:market.checkout-forms.manage');

        // Warehouse Management (WMS)
        Route::view('warehouses', 'market::admin.warehouse.index')
            ->name('warehouses.index')
            ->middleware('permission:market.warehouses.view');

        Route::view('warehouse-stock', 'market::admin.warehouse.stock')
            ->name('warehouse-stock.index')
            ->middleware('permission:market.warehouses.manage');

        Route::view('shipping', 'market::admin.shipping.index')
            ->name('shipping.index')
            ->middleware('permission:market.shipping.manage');

        Route::get('order-statuses', \Modules\Market\App\Livewire\Admin\OrderStatusManager::class)
            ->name('order-statuses.index')
            ->middleware('permission:market.order-statuses.manage');

        Route::get('reviews', \Modules\Market\App\Livewire\Admin\ReviewManager::class)
            ->name('reviews.index')
            ->middleware('permission:market.reviews.manage');

        Route::get('questions', \Modules\Market\App\Livewire\Admin\QuestionManager::class)
            ->name('questions.index')
            ->middleware('permission:market.questions.manage');

        // Orders Section
        Route::get('orders', [\Modules\Market\App\Http\Controllers\User\OrderController::class, 'index'])->name('orders.index')->middleware('permission:market.orders.view');
        Route::get('orders/{order}', [\Modules\Market\App\Http\Controllers\User\OrderController::class, 'show'])->name('orders.show')->middleware('permission:market.orders.view');
        Route::resource('orders', \Modules\Market\App\Http\Controllers\User\OrderController::class)->except(['index', 'show'])->middleware('permission:market.orders.manage');

        Route::view('brands', 'market::admin.brands.index')->name('brands.index')->middleware('permission:market.brands.manage');
        Route::view('categories', 'market::admin.categories.index')->name('categories.index')->middleware('permission:market.categories.manage');
        Route::view('display-categories', 'market::admin.display-categories.index')->name('display-categories.index')->middleware('permission:market.categories.manage');
        Route::view('attributes', 'market::admin.attributes.index')
            ->name('attributes.index')
            ->middleware(['permission:market.attributes.manage']);
    });

    // Settings
    Route::prefix('settings/market')
        ->name('settings.market.')
        ->middleware(['permission:market.settings.manage'])
        ->group(function () {
            Route::view('general', 'market::admin.settings.general')->name('general');
        });
});
