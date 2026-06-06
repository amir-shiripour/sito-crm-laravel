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

        Route::get('dashboard', [MarketDashboardController::class, 'index'])->name('dashboard');

        // Vendor Section
        Route::prefix('vendor')
            ->name('vendor.')
            ->middleware(['permission:market.products.view', CheckVendorStatus::class])
            ->group(function () {
                Route::resource('products', VendorProductController::class);
                Route::view('warehouses', 'market::user.warehouses.index')->name('warehouses.index');
            });

        // Admin Section
        Route::middleware([CheckMultiVendorMode::class])->group(function () {
            Route::resource('vendors', VendorController::class);
            Route::view('vendor-products/review', 'market::admin.vendor-products.review')
                ->name('vendor-products.review')
                ->middleware(['permission:market.manage']);
        });

        // Catalog Management
        Route::resource('master-products', MasterProductController::class);
        Route::get('checkout-forms', CheckoutFormManager::class)->name('checkout-forms.index')->middleware('permission:market.manage');


        // Warehouse Management (WMS)
        Route::view('warehouses', 'market::admin.warehouse.index')
            ->name('warehouses.index')
            ->middleware('permission:market.warehouses.view');

        Route::view('warehouse-stock', 'market::admin.warehouse.stock')
            ->name('warehouse-stock.index')
            ->middleware('permission:market.warehouses.manage');

        // Orders Section
        Route::resource('orders', \Modules\Market\App\Http\Controllers\User\OrderController::class);
        Route::view('brands', 'market::admin.brands.index')->name('brands.index');
        Route::view('categories', 'market::admin.categories.index')->name('categories.index');
        Route::view('display-categories', 'market::admin.display-categories.index')->name('display-categories.index');
        Route::view('attributes', 'market::admin.attributes.index')
            ->name('attributes.index')
            ->middleware(['permission:market.manage']);
    });

    // Settings
    Route::prefix('settings/market')
        ->name('settings.market.')
        ->middleware(['permission:market.manage'])
        ->group(function () {
            Route::view('general', 'market::admin.settings.general')->name('general');
        });
});
