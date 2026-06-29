<?php

use Illuminate\Support\Facades\Route;

Route::middleware('validate.api.key:properties')->group(function () {
    Route::get('properties', [\Modules\Settings\Http\Controllers\Api\PropertyApiController::class, 'index']);
    Route::get('properties/{id}', [\Modules\Settings\Http\Controllers\Api\PropertyApiController::class, 'show']);
});

Route::middleware('validate.api.key:booking')->group(function () {
    Route::get('services', [\Modules\Settings\Http\Controllers\Api\BookingApiController::class, 'index']);
    Route::get('services/{id}', [\Modules\Settings\Http\Controllers\Api\BookingApiController::class, 'show']);
});

