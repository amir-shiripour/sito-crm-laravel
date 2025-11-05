<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| تمام این روت‌ها به صورت خودکار پیشوند /admin را دریافت می‌کنند
| و پیشوند نام admin. را (از RouteServiceProvider) دریافت می‌کنند.
|
*/

// داشبورد اصلی ادمین
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// --- مدیریت کاربران (بخش هسته) ---
// اصلاح شد: .as('admin.') از اینجا حذف شد تا از تداخل (admin.admin) جلوگیری شود
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
    Route::put('/{user}', [UserController::class, 'update'])->name('update');
});

// --- مدیریت ماژول‌ها (بخش هسته) ---
// اصلاح شد: .as('admin.') از اینجا حذف شد
Route::prefix('modules')->name('modules.')->group(function () {
    Route::get('/', [ModuleController::class, 'index'])->name('index');
    // اصلاح شد: روت toggle باید POST باشد تا از خطای 404/CSRF جلوگیری شود
    Route::post('/toggle', [ModuleController::class, 'toggle'])->name('toggle');
});

