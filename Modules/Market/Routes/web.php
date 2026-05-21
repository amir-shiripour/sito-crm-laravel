<?php

use Illuminate\Support\Facades\Route;
use Modules\Market\App\Http\Middleware\CheckVendorStatus;
use Modules\Market\App\Http\Middleware\CheckMultiVendorMode;
use Modules\Market\App\Http\Controllers\User\CheckoutController;
use Modules\Market\App\Http\Controllers\User\VendorProductController;
use Modules\Market\App\Http\Controllers\Admin\VendorController;
use Modules\Market\App\Http\Controllers\User\MarketDashboardController;
use Modules\Market\App\Http\Controllers\Admin\MasterProductController;
use Modules\Market\App\Http\Controllers\MarketController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Market Routes
Route::middleware(['web'])->group(function() {
    Route::group(['prefix' => 'shop', 'as' => 'market.public.'], function() {
        Route::get('/', [MarketController::class, 'index'])->name('index');
        Route::get('/category', [MarketController::class, 'category'])->name('category');
        Route::get('/category/{slug}', [MarketController::class, 'category'])->name('category.show');
        Route::get('/product/{slug}', [MarketController::class, 'show'])->name('product.show');
    });
});


// User & Admin Routes
Route::group(['prefix' => 'user', 'as' => 'user.', 'middleware' => ['web', 'auth']], function () {

    Route::prefix('market')->name('market.')->group(function () {

        Route::get('dashboard', [MarketDashboardController::class, 'index'])->name('dashboard');

        // Checkout
        Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');

        // Vendor Section
        Route::prefix('vendor')
            ->name('vendor.')
            ->middleware(['permission:market.products.view', CheckVendorStatus::class])
            ->group(function () {
                Route::resource('products', VendorProductController::class);
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

        // Warehouse Management (WMS)
        Route::view('warehouses', 'market::admin.warehouse.index') // 💡 تغییر به view
            ->name('warehouses.index')
            ->middleware('permission:market.warehouses.view');

        Route::view('warehouse-stock/{warehouseId}', 'market::admin.warehouse.stock') // 💡 تغییر به view
            ->name('warehouse-stock.index')
            ->middleware('permission:market.warehouses.manage');

        // Other Sections
        Route::get('orders', function() { return 'لیست سفارشات (به زودی...)'; })->name('orders.index');
        Route::view('brands', 'market::admin.brands.index')->name('brands.index');
        Route::view('categories', 'market::admin.categories.index')->name('categories.index');
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
