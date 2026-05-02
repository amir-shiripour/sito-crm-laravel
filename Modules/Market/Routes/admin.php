<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" and "auth" middleware groups. Now create something great!
|
*/

Route::prefix('market')->name('admin.market.')->middleware(['permission:market.manage'])->group(function () {

    // تایید یا رد کردن پروفایل فروشندگان
    // Route::resource('vendors', AdminVendorController::class);

    // مدیریت دسته‌بندی‌های کل سیستم
    // Route::resource('categories', AdminCategoryController::class);

    // تنظیمات مالی و کمیسیون‌های پیش‌فرض
    // Route::get('settings', [MarketSettingsController::class, 'edit'])->name('settings.edit');
});

// Route::group([], function () {
//     Route::get('/', 'MarketController@index')->name('index');
// });
