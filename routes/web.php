<?php

use App\Http\Controllers\InstallController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\DashboardController as UserDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// --- بخش نصب‌کننده ---
// این بخش دارای میدل‌ور 'prevent.if.installed' است تا پس از نصب، این روت‌ها در دسترس نباشند
// همچنین نیاز به middleware 'web' دارد برای session و CSRF
Route::prefix('install')->middleware(['web', 'prevent.if.installed'])->group(function () {

    // --- اصلاح شد: از 'showStep1' به 'step1' تغییر کرد ---
    Route::get('/', [InstallController::class, 'step1'])->name('install.step1');
    Route::post('/', [InstallController::class, 'processStep1'])->name('install.processStep1');

    Route::get('/step2', [InstallController::class, 'step2'])->name('install.step2');
    Route::post('/step2', [InstallController::class, 'processStep2'])->name('install.processStep2');

    Route::get('/step3', [InstallController::class, 'step3'])->name('install.step3');
    Route::post('/step3', [InstallController::class, 'processStep3'])->name('install.processStep3');
});


// --- بخش اصلی سایت (عمومی و داشبورد) ---
// این روت‌ها دارای میدل‌ور 'redirect.if.not.installed' هستند تا قبل از نصب، در دسترس نباشند
Route::middleware([
    'redirect.if.not.installed',
    'web', // اطمینان از اجرای میدل‌ورهای وب (مخصوصاً سشن)
])->group(function () {

    // روت صفحه اصلی سایت
    Route::get('/', [PageController::class, 'home'])->name('home');

    // روت‌های Jetstream (لاگین، پروفایل و ...)
    // Jetstream روت‌های خود را به صورت خودکار در 'boot' (در RouteServiceProvider) ثبت می‌کند
    // و آنها نیز تحت تاثیر این گروه میدل‌ور قرار می‌گیرند.

    // روت خروج (برای دکمه خروج در داشبورد ادمین)
    // Jetstream از روت خروج متفاوتی استفاده می‌کند، اما پنل ادمین ما به این نیاز دارد
    /*Route::post('logout', function (Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout')->middleware('auth');*/
});


Route::middleware(['web', 'auth'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    // سایر روت‌های مربوط به Jetstream / profile اینجا هستند
});
