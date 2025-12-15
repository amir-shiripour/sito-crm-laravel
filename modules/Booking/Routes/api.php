<?php

use Illuminate\Support\Facades\Route;

use Modules\Booking\Http\Controllers\Api\ServiceController;
use Modules\Booking\Http\Controllers\Api\CategoryController;
use Modules\Booking\Http\Controllers\Api\FormController;
use Modules\Booking\Http\Controllers\Api\AvailabilityController;
use Modules\Booking\Http\Controllers\Api\AppointmentController;
use Modules\Booking\Http\Controllers\Api\ReportController;

Route::prefix('booking')->group(function () {

    // Public endpoints for online booking
    Route::get('availability/slots', [AvailabilityController::class, 'slots']); // can be public
    Route::post('appointments/online/start', [AppointmentController::class, 'onlineStart']);
    Route::post('appointments/online/confirm', [AppointmentController::class, 'onlineConfirm']);

    // Internal endpoints (requires auth)
    Route::middleware(['auth'])->group(function () {

        // Services
        Route::get('services', [ServiceController::class, 'index']);
        Route::post('services', [ServiceController::class, 'store']);
        Route::patch('services/{service}', [ServiceController::class, 'update']);
        Route::delete('services/{service}', [ServiceController::class, 'destroy']);
        Route::post('services/{service}/providers', [ServiceController::class, 'attachProviders']);

        Route::patch('service-providers/{serviceProvider}', [ServiceController::class, 'updateServiceProvider']);

        // Categories
        Route::get('categories', [CategoryController::class, 'index']);
        Route::post('categories', [CategoryController::class, 'store']);
        Route::patch('categories/{category}', [CategoryController::class, 'update']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

        // Forms
        Route::get('forms', [FormController::class, 'index']);
        Route::post('forms', [FormController::class, 'store']);
        Route::patch('forms/{form}', [FormController::class, 'update']);
        Route::delete('forms/{form}', [FormController::class, 'destroy']);

        // Availability management
        Route::post('availability/rules', [AvailabilityController::class, 'storeRule']);
        Route::post('availability/exceptions', [AvailabilityController::class, 'storeException']);

        // Appointments (operator/admin)
        Route::post('appointments', [AppointmentController::class, 'store']);
        Route::patch('appointments/{appointment}', [AppointmentController::class, 'patch']);

        // Payments (manual/for gateway callbacks - protect accordingly)
        Route::post('payments/{payment}/paid', [AppointmentController::class, 'markPaymentPaid']);

        // Reports
        Route::get('reports/overview', [ReportController::class, 'overview']);
        Route::get('reports/providers', [ReportController::class, 'providers']);
        Route::get('reports/services', [ReportController::class, 'services']);
        Route::get('reports/finance', [ReportController::class, 'finance']);
    });
});
