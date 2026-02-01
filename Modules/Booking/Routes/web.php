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
use Modules\Booking\Http\Controllers\User\CategoryController as UserCategoryController;
use Modules\Booking\Http\Controllers\User\FormController as UserFormController;
use Modules\Booking\Http\Controllers\User\StatementController;


// Public minimal pages (optional)
Route::prefix('booking')->name('booking.')->group(function () {
    Route::get('/', [OnlineBookingController::class, 'index'])->name('public.index');
    Route::get('/service/{service}', [OnlineBookingController::class, 'service'])->name('public.service');
    Route::get('/service/{service}/calendar', [OnlineBookingController::class, 'calendar'])->name('public.calendar');
    Route::get('/service/{service}/slots', [OnlineBookingController::class, 'slots'])->name('public.slots');
    Route::post('/service/{service}/book', [OnlineBookingController::class, 'book'])->name('public.book');

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

        Route::get('categories', [UserCategoryController::class, 'index'])
            ->name('categories.index')
            ->middleware('can:booking.categories.view');

        Route::get('categories/create', [UserCategoryController::class, 'create'])
            ->name('categories.create')
            ->middleware('can:booking.categories.create');

        Route::post('categories', [UserCategoryController::class, 'store'])
            ->name('categories.store')
            ->middleware('can:booking.categories.create');

        Route::get('categories/{category}/edit', [UserCategoryController::class, 'edit'])
            ->name('categories.edit')
            ->middleware('can:booking.categories.edit');

        Route::put('categories/{category}', [UserCategoryController::class, 'update'])
            ->name('categories.update')
            ->middleware('can:booking.categories.edit');

        Route::delete('categories/{category}', [UserCategoryController::class, 'destroy'])
            ->name('categories.destroy')
            ->middleware('can:booking.categories.delete');

        Route::get('forms', [UserFormController::class, 'index'])
            ->name('forms.index')
            ->middleware('can:booking.forms.view');

        Route::get('forms/create', [UserFormController::class, 'create'])
            ->name('forms.create')
            ->middleware('can:booking.forms.create');

        Route::post('forms', [UserFormController::class, 'store'])
            ->name('forms.store')
            ->middleware('can:booking.forms.create');

        Route::get('forms/{form}/edit', [UserFormController::class, 'edit'])
            ->name('forms.edit')
            ->middleware('can:booking.forms.edit');

        Route::put('forms/{form}', [UserFormController::class, 'update'])
            ->name('forms.update')
            ->middleware('can:booking.forms.edit');

        Route::delete('forms/{form}', [UserFormController::class, 'destroy'])
            ->name('forms.destroy')
            ->middleware('can:booking.forms.delete');


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
        Route::get('appointments/create', [UserAppointmentController::class, 'create'])->name('appointments.create');
        Route::post('appointments', [UserAppointmentController::class, 'store'])->name('appointments.store');

        Route::get('appointments/wizard/providers', [UserAppointmentController::class, 'wizardProviders'])
            ->name('appointments.wizard.providers');

        Route::get('appointments/wizard/services', [UserAppointmentController::class, 'wizardServices'])
            ->name('appointments.wizard.services');

        Route::get('appointments/wizard/all-services', [UserAppointmentController::class, 'wizardAllServices'])
            ->name('appointments.wizard.all-services');

        Route::get('appointments/wizard/categories', [UserAppointmentController::class, 'wizardCategories'])
            ->name('appointments.wizard.categories');

        Route::get('appointments/wizard/calendar', [UserAppointmentController::class, 'wizardCalendar'])
            ->name('appointments.wizard.calendar');

        Route::get('appointments/wizard/clients', [UserAppointmentController::class, 'wizardClients'])
            ->name('appointments.wizard.clients');

        Route::get('appointments/wizard/form', [UserAppointmentController::class, 'wizardForm'])
            ->name('appointments.wizard.form');

        Route::get('appointments/{appointment}', [UserAppointmentController::class, 'show'])
            ->name('appointments.show')
            ->middleware('can:booking.appointments.view');

        Route::get('appointments/{appointment}/edit', [UserAppointmentController::class, 'edit'])
            ->name('appointments.edit')
            ->middleware('can:booking.appointments.edit');

        Route::post('appointments/{appointment}', [UserAppointmentController::class, 'update'])
            ->name('appointments.update')
            ->middleware('can:booking.appointments.edit');

        Route::delete('appointments/{appointment}', [UserAppointmentController::class, 'destroy'])
            ->name('appointments.destroy')
            ->middleware('can:booking.appointments.edit');

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

        // Statement of Account
        Route::get('statement', [StatementController::class, 'index'])
            ->name('statement.index')
            ->middleware('can:booking.appointments.view');

        Route::get('statement/search-providers', [StatementController::class, 'searchProviders'])
            ->name('statement.search-providers')
            ->middleware('can:booking.appointments.view');

        Route::get('statement/search-users', [StatementController::class, 'searchUsers'])
            ->name('statement.search-users')
            ->middleware('can:booking.appointments.view');

        Route::get('statement/print', [StatementController::class, 'print'])
            ->name('statement.print')
            ->middleware('can:booking.appointments.view');
    });
});
