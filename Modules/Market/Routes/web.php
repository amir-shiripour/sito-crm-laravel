<?php

use Illuminate\Support\Facades\Route;
use Modules\Market\App\Http\Middleware\CheckVendorStatus;
use Modules\Market\App\Http\Controllers\User\CheckoutController;
use Modules\Market\App\Http\Controllers\User\VendorProductController;
use Modules\Market\App\Http\Controllers\Admin\VendorController;
use Modules\Market\App\Http\Controllers\User\MarketDashboardController;
use Modules\Market\App\Http\Controllers\Admin\MasterProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'user', 'as' => 'user.', 'middleware' => ['web', 'auth']], function () {

    Route::prefix('market')->name('market.')->group(function () {

        // داشبورد اصلی مارکت (بررسی وضعیت فروشنده)
        Route::get('dashboard', [MarketDashboardController::class, 'index'])
            ->name('dashboard');

        // ==========================================
        // بخش خریداران
        // ==========================================
        Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');

        // ==========================================
        // بخش فروشندگان (Vendors)
        // ==========================================
        Route::prefix('vendor')
            ->name('vendor.')
            ->middleware(['permission:market.products.view', CheckVendorStatus::class]) // کنترل وضعیت KYC
            ->group(function () {
                // مدیریت تنوع محصولات توسط فروشنده
                Route::resource('products', VendorProductController::class);
            });

        // ==========================================
        // مدیریت سیستم توسط پرسنل / ادمین
        // ==========================================
        // مدیریت فروشندگان
        Route::resource('vendors', VendorController::class);

        // مدیریت کاتالوگ اصلی محصولات (Master Products)
        Route::resource('master-products', MasterProductController::class);

        // 💡 بررسی و تایید محصولات ثبت شده توسط فروشندگان (اضافه شد)
        Route::view('vendor-products/review', 'market::admin.vendor-products.review')
            ->name('vendor-products.review')
            ->middleware(['permission:market.manage']);

        // ==========================================
        // سایر بخش‌ها
        // ==========================================
        Route::get('orders', function() { return 'لیست سفارشات (به زودی...)'; })->name('orders.index');

        // مدیریت برندها
        Route::view('brands', 'market::admin.brands.index')->name('brands.index');

        // مدیریت دسته‌بندی‌ها
        Route::view('categories', 'market::admin.categories.index')->name('categories.index');

    });

    // ==========================================
    // تنظیمات فروشگاه
    // ==========================================
    Route::prefix('settings/market')
        ->name('settings.market.')
        ->middleware(['permission:market.manage']) // اعمال دسترسی برای تنظیمات
        ->group(function () {
            // اتصال مستقیم روت general به فایل Blade که کامپوننت Livewire در آن فراخوانی شده است
            Route::view('general', 'market::admin.settings.general')->name('general');
        });

});
