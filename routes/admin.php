<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\CustomFieldController;
use App\Http\Controllers\Admin\VersionControlController;
use App\Http\Controllers\Admin\RegistrationRequestController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| این فایل از RouteServiceProvider با prefix('admin') و name('admin.')
| و middleware(['web','auth']) لود می‌شود.
| تمامی روت‌های بخش مدیریت در اینجا سازماندهی شده‌اند.
|
*/

// --- داشبورد اصلی ادمین ---
// نکته: مسیر به حالت قدیمی '/dashboard' برگشت تا لینک‌های فعلی شما خراب نشود.
// متد جدید layout هم به آن اضافه شد.
Route::middleware(['role:super-admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/layout', [DashboardController::class, 'updateLayout'])->name('dashboard.update-layout');
});

// --- مدیریت کاربران ---
Route::prefix('users')->name('users.')->middleware(['permission:users.view'])->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit')->middleware('permission:users.update');
    Route::put('/{user}', [UserController::class, 'update'])->name('update')->middleware('permission:users.update');

    Route::middleware(['permission:users.create'])->group(function () {
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
    });

    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy')->middleware('permission:users.delete');

    // امکان جدید: انتساب نقش‌ها به کاربر
    Route::post('/{user}/roles', [UserController::class, 'assignRoles'])
        ->name('assign-roles')
        ->middleware('permission:users.assign-roles');
});

// --- مدیریت نقش‌ها ---
/*
 * توجه: ما دسترسی‌ها (Permissions) را مستقیماً ویرایش نمی‌کنیم و فقط نقش‌ها (Roles) را مدیریت می‌کنیم.
 * دسترسی‌ها توسط توسعه‌دهنده در کدهای سیستم تعریف شده و ثابت می‌مانند.
 */
Route::prefix('roles')->name('roles.')->middleware(['permission:roles.view'])->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('index');

    Route::middleware(['permission:roles.create'])->group(function () {
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
    });

    Route::middleware(['permission:roles.update'])->group(function () {
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
    });

    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy')->middleware('permission:roles.delete');
});

// --- مدیریت فیلدهای سفارشی ---
Route::prefix('custom-fields')->name('custom-fields.')->middleware(['permission:custom-fields.view'])->group(function () {
    Route::get('/', [CustomFieldController::class, 'index'])->name('index');

    Route::middleware(['permission:custom-fields.create'])->group(function () {
        Route::get('/create', [CustomFieldController::class, 'create'])->name('create');
        Route::post('/', [CustomFieldController::class, 'store'])->name('store');
    });

    Route::middleware(['permission:custom-fields.update'])->group(function () {
        Route::get('/{field}/edit', [CustomFieldController::class, 'edit'])->name('edit');
        Route::put('/{field}', [CustomFieldController::class, 'update'])->name('update');
    });

    Route::delete('/{field}', [CustomFieldController::class, 'destroy'])->name('destroy')->middleware('permission:custom-fields.delete');
});

// --- مدیریت درخواست‌های ثبت نام (امکان جدید) ---
Route::prefix('registration-requests')->name('registration-requests.')->middleware(['permission:registration-requests.view'])->group(function () {
    Route::get('/', [RegistrationRequestController::class, 'index'])->name('index');
    Route::post('/{registrationRequest}/approve', [RegistrationRequestController::class, 'approve'])->name('approve')->middleware('permission:registration-requests.approve');
    Route::post('/{registrationRequest}/reject', [RegistrationRequestController::class, 'reject'])->name('reject')->middleware('permission:registration-requests.reject');
});

// --- مدیریت ماژول‌ها ---
Route::prefix('modules')->name('modules.')->group(function () {
    Route::get('/', [ModuleController::class, 'index'])->name('index');

    // روت‌های عمومی‌تر
    Route::post('/toggle', [ModuleController::class, 'toggle'])->name('toggle'); // متد قدیمی حفظ شد

    // امکانات جدید اضافه شده در فایل دوم (با احتیاط)
    Route::post('/upload', [ModuleController::class, 'upload'])->name('upload')->middleware('role:super-admin');
    Route::delete('/{module}', [ModuleController::class, 'destroy'])->name('destroy')->middleware('role:super-admin');

    // عملیات حیاتی ماژول‌ها — برگردانده شد (دسترسی فقط برای super-admin)
    Route::middleware(['role:super-admin'])->group(function () {
        Route::post('/install', [ModuleController::class, 'install'])->name('install');
        Route::post('/enable', [ModuleController::class, 'enableModule'])->name('enable');
        Route::post('/disable', [ModuleController::class, 'disableModule'])->name('disable');
        Route::post('/reset', [ModuleController::class, 'resetModule'])->name('reset');
        Route::post('/uninstall', [ModuleController::class, 'uninstallModule'])->name('uninstall');
        Route::post('/update-package', [ModuleController::class, 'updatePackage'])->name('update-package');
    });
});

// --- مدیریت نسخه‌ها (Version Control) ---
// دسترسی به این بخش حساس هسته‌ای فقط برای سوپر ادمین تعریف شده است
Route::prefix('version-control')->name('version-control.')->middleware(['role:super-admin'])->group(function () {
    // مسیرهای CRUD قدیمی (برگردانده شد)
    Route::get('/', [VersionControlController::class, 'index'])->name('index');
    Route::get('/create', [VersionControlController::class, 'create'])->name('create');
    Route::post('/', [VersionControlController::class, 'store'])->name('store');
    Route::get('/{versionControl}/edit', [VersionControlController::class, 'edit'])->name('edit');
    Route::put('/{versionControl}', [VersionControlController::class, 'update'])->name('update');
    Route::delete('/{versionControl}', [VersionControlController::class, 'destroy'])->name('destroy');

    // مسیرهای قدیمی برای گیت‌هاب
    Route::get('/check-remote', [VersionControlController::class, 'checkRemote'])->name('check-remote');
    Route::post('/deploy-update', [VersionControlController::class, 'deployUpdate'])->name('deploy');

    // امکانات جدید مربوط به آپدیت سیستم
    // نکته: نام متد update در کدهای جدید با متد PUT (برای ویرایش فرم) تداخل داشت!
    // برای جلوگیری از خطا، نام روت جدید را به system-update تغییر دادیم.
    Route::post('/check-updates', [VersionControlController::class, 'checkUpdates'])->name('check-updates');
    Route::post('/run-system-update', [VersionControlController::class, 'update'])->name('system-update');
});
