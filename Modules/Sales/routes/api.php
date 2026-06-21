<?php

use Illuminate\Support\Facades\Route;

Route::prefix('sales')->middleware('auth:sanctum')->group(function () {
    Route::get('/clients/search', [\Modules\Sales\App\Http\Controllers\Api\SalesClientController::class, 'search']);
    
    // Quick API routes for Cockpit
    Route::post('/calls', [\Modules\Sales\App\Http\Controllers\Api\SalesCallController::class, 'store']);
    Route::put('/calls/{id}', [\Modules\Sales\App\Http\Controllers\Api\SalesCallController::class, 'update']);
    
    Route::post('/followups', [\Modules\Sales\App\Http\Controllers\Api\SalesFollowUpController::class, 'store']);
    Route::put('/followups/{id}', [\Modules\Sales\App\Http\Controllers\Api\SalesFollowUpController::class, 'update']);
    Route::get('/followups', [\Modules\Sales\App\Http\Controllers\Api\SalesFollowUpController::class, 'index']);
});
