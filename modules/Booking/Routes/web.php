<?php

use Illuminate\Support\Facades\Route;
use Modules\Booking\Http\Controllers\Web\OnlineBookingController;
use Modules\Booking\Http\Controllers\User\BookingDashboardController;
use Modules\Booking\Http\Controllers\User\ServiceController as UserServiceController;
use Modules\Booking\Http\Controllers\User\AppointmentController as UserAppointmentController;
use Modules\Booking\Http\Controllers\User\SettingsController as UserSettingsController;
use Modules\Booking\Http\Controllers\User\ServiceAvailabilityController as UserServiceAvailabilityController;
use Modules\Booking\Http\Controllers\User\ProviderAvailabilityController;
use Modules\Booking\Http\Controllers\User\ServiceExceptionController;
use Modules\Booking\Http\Controllers\User\ProviderExceptionController;


// Public minimal pages (optional)
Route::prefix('booking')->name('booking.')->group(function () {
    Route::get('/', [OnlineBookingController::class, 'index'])->name('public.index');
    Route::get('/service/{service}', [OnlineBookingController::class, 'service'])->name('public.service');

    /*Route::get('services/{service}/availability', [UserServiceAvailabilityController::class, 'edit'])
        ->name('services.availability.edit')
        ->middleware('can:booking.availability.manage');

    Route::post('services/{service}/availability', [UserServiceAvailabilityController::class, 'update'])
        ->name('services.availability.update')
        ->middleware('can:booking.availability.manage');

    // برنامه زمانی «ارائه‌دهندگان»
    Route::get('providers', [ProviderAvailabilityController::class, 'index'])
        ->name('providers.index')
        ->middleware('can:booking.availability.manage');

    Route::get('providers/{provider}/availability', [ProviderAvailabilityController::class, 'edit'])
        ->name('providers.availability.edit')
        ->middleware('can:booking.availability.manage');

    Route::post('providers/{provider}/availability', [ProviderAvailabilityController::class, 'update'])
        ->name('providers.availability.update')
        ->middleware('can:booking.availability.manage');*/
});

// User area
Route::prefix('user')->name('user.')->middleware(['web', 'auth'])->group(function () {

    Route::prefix('booking')->name('booking.')->group(function () {
        Route::get('/', [BookingDashboardController::class, 'index'])->name('dashboard')->middleware('can:booking.view');

        Route::get('services', [UserServiceController::class, 'index'])->name('services.index')->middleware('can:booking.services.view');
        Route::get('services/create', [UserServiceController::class, 'create'])->name('services.create')->middleware('can:booking.services.create');
        Route::post('services', [UserServiceController::class, 'store'])->name('services.store')->middleware('can:booking.services.create');
        Route::get('services/{service}/edit', [UserServiceController::class, 'edit'])->name('services.edit')->middleware('can:booking.services.edit');
        Route::post('services/{service}', [UserServiceController::class, 'update'])->name('services.update')->middleware('can:booking.services.edit');
        Route::post('services/{service}/toggle-for-me', [UserServiceController::class, 'toggleForMe'])
            ->name('services.toggleForMe')
            ->middleware('can:booking.services.view');


        // ⚠️ این دو تا از روی middleware سلب میشن تا کنترل دست ServiceAvailabilityController باشه
        Route::get('services/{service}/availability', [UserServiceAvailabilityController::class, 'edit'])
            ->name('services.availability.edit');

        Route::post('services/{service}/availability', [UserServiceAvailabilityController::class, 'update'])
            ->name('services.availability.update');


        // برنامه زمانی «ارائه‌دهندگان» فقط برای ادمین‌ها و کسانی که permission دارند
        Route::get('providers', [ProviderAvailabilityController::class, 'index'])
            ->name('providers.index')
            ->middleware('can:booking.availability.manage');

        Route::get('providers/{provider}/availability', [ProviderAvailabilityController::class, 'edit'])
            ->name('providers.availability.edit')
            ->middleware('can:booking.availability.manage');

        Route::post('providers/{provider}/availability', [ProviderAvailabilityController::class, 'update'])
            ->name('providers.availability.update')
            ->middleware('can:booking.availability.manage');

        Route::get('appointments', [UserAppointmentController::class, 'index'])->name('appointments.index')->middleware('can:booking.appointments.view');
        Route::get('appointments/create', [UserAppointmentController::class, 'create'])->name('appointments.create')->middleware('can:booking.appointments.create');
        Route::post('appointments', [UserAppointmentController::class, 'store'])->name('appointments.store')->middleware('can:booking.appointments.create');

        Route::get('appointments/wizard/providers', [UserAppointmentController::class, 'wizardProviders'])
            ->name('appointments.wizard.providers')
            ->middleware('can:booking.appointments.create');

        Route::get('appointments/wizard/services', [UserAppointmentController::class, 'wizardServices'])
            ->name('appointments.wizard.services')
            ->middleware('can:booking.appointments.create');

        Route::get('appointments/wizard/all-services', [UserAppointmentController::class, 'wizardAllServices'])
            ->name('appointments.wizard.all-services')
            ->middleware('can:booking.appointments.create');

        Route::get('appointments/wizard/categories', [UserAppointmentController::class, 'wizardCategories'])
            ->name('appointments.wizard.categories')
            ->middleware('can:booking.appointments.create');

        Route::get('appointments/wizard/calendar', [UserAppointmentController::class, 'wizardCalendar'])
            ->name('appointments.wizard.calendar')
            ->middleware('can:booking.appointments.create');

        Route::get('appointments/wizard/clients', [UserAppointmentController::class, 'wizardClients'])
            ->name('appointments.wizard.clients')
            ->middleware('can:booking.appointments.create');

        Route::get('settings', [UserSettingsController::class, 'edit'])->name('settings.edit')->middleware('can:booking.settings.manage');
        Route::post('settings', [UserSettingsController::class, 'update'])->name('settings.update')->middleware('can:booking.settings.manage');

        // استثناهای سرویس - فقط مدیریت (ادمین‌ها)
        Route::get('services/{service}/exceptions', [ServiceExceptionController::class, 'index'])
            ->name('services.exceptions.index')
            ->middleware('can:booking.availability.manage');

        Route::post('services/{service}/exceptions', [ServiceExceptionController::class, 'store'])
            ->name('services.exceptions.store')
            ->middleware('can:booking.availability.manage');

        Route::delete('services/{service}/exceptions/{exception}', [ServiceExceptionController::class, 'destroy'])
            ->name('services.exceptions.destroy')
            ->middleware('can:booking.availability.manage');

        // استثناهای ارائه‌دهنده - فقط مدیریت (ادمین‌ها)
        Route::get('providers/{provider}/exceptions', [ProviderExceptionController::class, 'index'])
            ->name('providers.exceptions.index')
            ->middleware('can:booking.availability.manage');

        Route::post('providers/{provider}/exceptions', [ProviderExceptionController::class, 'store'])
            ->name('providers.exceptions.store')
            ->middleware('can:booking.availability.manage');

        Route::delete('providers/{provider}/exceptions/{exception}', [ProviderExceptionController::class, 'destroy'])
            ->name('providers.exceptions.destroy')
            ->middleware('can:booking.availability.manage');

    });
});
