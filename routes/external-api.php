<?php

use Illuminate\Support\Facades\Route;

Route::middleware('validate.api.key:properties')->group(function () {
    Route::get('properties', [\Modules\Settings\Http\Controllers\Api\PropertyApiController::class, 'index']);
    Route::get('properties/{id}', [\Modules\Settings\Http\Controllers\Api\PropertyApiController::class, 'show']);
});
