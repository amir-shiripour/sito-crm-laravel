<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ModuleController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| تمام مسیرهای مربوط به پنل مدیریت در این فایل قرار می‌گیرند.
| این مسیرها به صورت خودکار دارای پیشوند /admin و میدل‌ورهای web و auth هستند.
|
*/

//Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// روت‌های مدیریت کاربران (جدید)
// آدرس: /admin/users
Route::get('/users', [UserController::class, 'index'])->name('users.index');

// آدرس: /admin/users/{user}/edit
Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');

// آدرس: /admin/users/{user} (Method: PUT)
Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');

// مثال: مدیریت کاربران (با استفاده از نقش)
// Route::middleware(['role:Super Admin'])->prefix('users')->name('users.')->group(function () {
//     Route::get('/', [UserController::class, 'index'])->name('index');
//     // ...
// });

// مسیرهای ماژول‌ها می‌توانند در اینجا اینکلود شوند
// include base_path('modules/Customer/Routes/admin.php');

// --- مدیریت ماژول‌ها (Module Management) - (جدید) ---
// (آدرس نهایی: /admin/modules)
Route::get('modules', [ModuleController::class, 'index'])->name('modules.index');
// (آدرس نهایی: /admin/modules/{module}/toggle)
Route::post('modules/{module}/toggle', [ModuleController::class, 'toggle'])->name('modules.toggle');
