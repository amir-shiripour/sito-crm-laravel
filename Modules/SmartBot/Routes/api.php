<?php

use Illuminate\Support\Facades\Route;

// API endpoints for SmartBot if needed in the future
Route::prefix('smartbot')->group(function () {
    Route::get('/status', function () {
        return response()->json(['status' => 'online', 'module' => 'SmartBot']);
    });
});
