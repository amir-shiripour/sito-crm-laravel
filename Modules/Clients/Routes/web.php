<?php

use Illuminate\Support\Facades\Route;
use Modules\Clients\Middleware\EnsureClientsModuleEnabled;
use Modules\Clients\App\Http\Controllers\User\ClientController as UserClientController;
use Modules\Clients\App\Http\Controllers\User\DashboardController as UserClientDashboardController;
use Modules\Clients\App\Livewire\Settings\ClientFormBuilder;
use Modules\Clients\App\Livewire\Settings\ClientUsernameSettings;
use Modules\Clients\App\Livewire\Settings\ClientStatusesManager;
use Modules\Clients\App\Http\Controllers\Portal\ClientAuthController;
use Modules\Clients\App\Http\Controllers\Portal\ClientDashboardController;
use Modules\Clients\App\Livewire\Settings\ClientAuthSettings;
use Modules\Clients\App\Livewire\Settings\CsvImporter;
use Modules\Clients\App\Http\Controllers\User\ImportController;

Route::middleware(['web', 'auth', EnsureClientsModuleEnabled::class])
    ->prefix('user')
    ->name('user.')
    ->group(function () {

        // صفحه داشبورد اختصاصی ماژول (اگر نیاز باشه)
        Route::get('/clients/dashboard', [UserClientDashboardController::class, 'index'])
            ->name('clients.dashboard');

        Route::prefix('clients')
            ->name('clients.')
            ->group(function () {

                Route::get('/', [UserClientController::class, 'index'])
                    ->name('index')
                    ->middleware('permission:clients.view');

                Route::get('/create', [UserClientController::class, 'create'])
                    ->name('create')
                    ->middleware('permission:clients.create');

                Route::post('/', [UserClientController::class, 'store'])
                    ->name('store')
                    ->middleware('permission:clients.create');

                Route::get('/{client}', [UserClientController::class, 'show'])
                    ->name('show')
                    ->middleware('permission:clients.view');

                Route::get('/{client}/edit', [UserClientController::class, 'edit'])
                    ->name('edit')
                    ->middleware('permission:clients.edit');

                Route::put('/{client}', [UserClientController::class, 'update'])
                    ->name('update')
                    ->middleware('permission:clients.edit');

                Route::delete('/{client}', [UserClientController::class, 'destroy'])
                    ->name('destroy')
                    ->middleware('permission:clients.delete');

                // 🔹 این روت جدید برای "ورود به پنل مشتری در تب جدید" است
                Route::get('/{client}/portal-login', [ClientAuthController::class, 'autoLoginFromAdmin'])
                    ->name('portal-login')
                    ->middleware('permission:clients.view');

                // جستجوی clients برای فیلدهای select
                Route::get('/search', [UserClientController::class, 'search'])
                    ->name('search')
                    ->middleware('permission:clients.view');
            });

        Route::get('/clients/profile', [UserClientController::class, 'profile'])
            ->name('clients.profile')
            ->middleware('permission:clients.view');
    });

Route::post('user/clients/quick-store', [UserClientController::class, 'quickStore'])
    ->name('user.clients.quick-store')
    ->middleware('auth');

Route::middleware(['web', 'auth', EnsureClientsModuleEnabled::class, 'permission:clients.manage'])
    ->prefix('user/settings/clients')
    ->name('user.settings.clients.')
    ->group(function () {
        Route::get('/forms', ClientFormBuilder::class)->name('forms');
        Route::get('/username', ClientUsernameSettings::class)->name('username');
        Route::get('/statuses', ClientStatusesManager::class)->name('statuses');
        Route::get('/auth', ClientAuthSettings::class)->name('auth');
        Route::get('/import', CsvImporter::class)->name('import');
        Route::post('/import/upload', [ImportController::class, 'upload'])->name('import.upload');
    });

Route::prefix('clients')
    ->name('client.')
    ->middleware('web')
    ->group(function () {

        // مهمان‌های کلاینت (کسی که با گارد client لاگین نیست)
        Route::middleware('guest:client')->group(function () {
            Route::get('login', [ClientAuthController::class, 'showLoginForm'])
                ->name('login');

            Route::post('login', [ClientAuthController::class, 'login'])
                ->name('login.submit');

            Route::post('login/otp/send', [ClientAuthController::class, 'sendOtp'])
                ->name('otp.send');

            Route::post('login/otp/verify', [ClientAuthController::class, 'verifyOtp'])
                ->name('otp.verify');
        });

        // کلاینت‌های لاگین کرده
        Route::middleware('auth:client')->group(function () {
            Route::get('dashboard', [ClientDashboardController::class, 'index'])
                ->name('dashboard');

            Route::post('logout', [ClientAuthController::class, 'logout'])
                ->name('logout');
        });
    });
