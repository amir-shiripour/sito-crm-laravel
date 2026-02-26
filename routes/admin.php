<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\CustomFieldController;
use App\Http\Controllers\Admin\VersionControlController; // اضافه شدن کنترلر جدید برای مدیریت نسخه‌ها

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
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard')
    ->middleware(['role:super-admin']); // محدودیت دسترسی فقط برای سوپر ادمین

// --- مدیریت کاربران (هسته) => همه زیر: /admin/users  و name: admin.users.* ---
Route::prefix('users')->name('users.')->middleware(['permission:users.view'])->group(function () {
    // لیست کاربران - برای همه کاربرانی که permission دارند
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit')->middleware('permission:users.update');
    Route::put('/{user}', [UserController::class, 'update'])->name('update')->middleware('permission:users.update');

    // عملیات ایجاد و حذف برای کاربرانی که permission دارند
    Route::middleware(['permission:users.create'])->group(function () {
        Route::get('/create', [UserController::class, 'create'])->name('create');   // GET  /admin/users/create
        Route::post('/', [UserController::class, 'store'])->name('store');          // POST /admin/users
    });

    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy')->middleware('permission:users.delete'); // DELETE /admin/users/{user}
});

// --- مدیریت ماژول‌ها (هسته) => /admin/modules  و name: admin.modules.* ---
Route::prefix('modules')->name('modules.')->group(function () {
    Route::get('/', [ModuleController::class, 'index'])->name('index');

    // Toggle قدیمی — برای سازگاری نگه داشته شده
    Route::post('/toggle', [ModuleController::class, 'toggle'])->name('toggle');
    // عملیات جدید ماژول‌ها — دسترسی فقط برای super-admin
    Route::middleware(['role:super-admin'])->group(function () {
        Route::post('/install', [ModuleController::class, 'install'])->name('install');
        Route::post('/enable', [ModuleController::class, 'enableModule'])->name('enable');
        Route::post('/disable', [ModuleController::class, 'disableModule'])->name('disable');
        Route::post('/reset', [ModuleController::class, 'resetModule'])->name('reset');
        Route::post('/uninstall', [ModuleController::class, 'uninstallModule'])->name('uninstall');
        Route::post('/update-package', [ModuleController::class, 'updatePackage'])->name('update-package');
    });
});

// --- مدیریت نقش‌ها => /admin/roles  و name: admin.roles.* ---
Route::prefix('roles')->name('roles.')->middleware(['permission:roles.view'])->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('index');           // فهرست نقش‌ها

    // عملیات ایجاد برای کاربرانی که permission دارند
    Route::middleware(['permission:roles.create'])->group(function () {
        Route::get('/create', [RoleController::class, 'create'])->name('create');   // فرم ایجاد
        Route::post('/', [RoleController::class, 'store'])->name('store');          // ذخیره نقش
    });

    // عملیات ویرایش برای کاربرانی که permission دارند
    Route::middleware(['permission:roles.update'])->group(function () {
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');  // فرم ویرایش
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');   // بروزرسانی نقش
    });

    // عملیات حذف برای کاربرانی که permission دارند
    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy')->middleware('permission:roles.delete'); // حذف نقش
});

// --- مدیریت فیلدهای سفارشی => /admin/custom-fields  و name: admin.custom-fields.* ---
Route::prefix('custom-fields')->name('custom-fields.')->middleware(['permission:custom-fields.view'])->group(function () {
    // لیست فیلدهای سفارشی
    Route::get('/', [CustomFieldController::class, 'index'])->name('index');

    // عملیات ایجاد برای کاربرانی که permission دارند
    Route::middleware(['permission:custom-fields.create'])->group(function () {
        Route::get('/create', [CustomFieldController::class, 'create'])->name('create');
        Route::post('/', [CustomFieldController::class, 'store'])->name('store');
    });

    // عملیات ویرایش برای کاربرانی که permission دارند
    Route::middleware(['permission:custom-fields.update'])->group(function () {
        Route::get('/{field}/edit', [CustomFieldController::class, 'edit'])->name('edit');
        Route::put('/{field}', [CustomFieldController::class, 'update'])->name('update');
    });

    // عملیات حذف برای کاربرانی که permission دارند
    Route::delete('/{field}', [CustomFieldController::class, 'destroy'])->name('destroy')->middleware('permission:custom-fields.delete');
});

// --- مدیریت نسخه‌ها (Version Control) => /admin/version-control و name: admin.version-control.* ---
// دسترسی به این بخش حساس هسته‌ای فقط برای سوپر ادمین تعریف شده است
Route::prefix('version-control')->name('version-control.')->middleware(['role:super-admin'])->group(function () {
    Route::get('/', [VersionControlController::class, 'index'])->name('index');
    Route::get('/create', [VersionControlController::class, 'create'])->name('create');
    Route::post('/', [VersionControlController::class, 'store'])->name('store');
    Route::get('/{versionControl}/edit', [VersionControlController::class, 'edit'])->name('edit');
    Route::put('/{versionControl}', [VersionControlController::class, 'update'])->name('update');
    Route::delete('/{versionControl}', [VersionControlController::class, 'destroy'])->name('destroy');

    // مسیرهای جدید برای گیت‌هاب (Advanced)
    Route::get('/check-remote', [VersionControlController::class, 'checkRemote'])->name('check-remote');
    Route::post('/deploy-update', [VersionControlController::class, 'deployUpdate'])->name('deploy');
});
