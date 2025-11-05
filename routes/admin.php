<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| این فایل از RouteServiceProvider با prefix('admin') و name('admin.')
| و middleware(['web','auth']) لود می‌شود. پس اینجا دوباره prefix/name
| برای 'admin' نمی‌گذاریم تا آدرس‌ها ثابت بمانند.
|
*/

// داشبورد اصلی ادمین  =>  GET /admin/dashboard   name: admin.dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// --- مدیریت کاربران (هسته) => همه زیر: /admin/users  و name: admin.users.* ---
Route::prefix('users')->name('users.')->group(function () {
    // قبلی‌ها:
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
    Route::put('/{user}', [UserController::class, 'update'])->name('update');

    // 👇 جدیدها: فقط سوپر ادمین می‌تواند بسازد و حذف کند
    Route::middleware(['role:super-admin'])->group(function () {
        Route::get('/create', [UserController::class, 'create'])->name('create');   // GET  /admin/users/create
        Route::post('/', [UserController::class, 'store'])->name('store');          // POST /admin/users
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy'); // DELETE /admin/users/{user}
    });
});

// --- مدیریت ماژول‌ها (هسته) => /admin/modules  و name: admin.modules.* ---
Route::prefix('modules')->name('modules.')->group(function () {
    Route::get('/', [ModuleController::class, 'index'])->name('index');
    Route::post('/toggle', [ModuleController::class, 'toggle'])->name('toggle');
});

// --- مدیریت نقش‌ها => /admin/roles  و name: admin.roles.* ---
Route::prefix('roles')->name('roles.')->middleware(['role:super-admin'])->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('index');           // فهرست نقش‌ها
    Route::get('/create', [RoleController::class, 'create'])->name('create');   // فرم ایجاد
    Route::post('/', [RoleController::class, 'store'])->name('store');          // ذخیره نقش
    Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');  // فرم ویرایش
    Route::put('/{role}', [RoleController::class, 'update'])->name('update');   // بروزرسانی نقش
    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy'); // حذف نقش
});
