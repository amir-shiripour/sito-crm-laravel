<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your module.
|
*/

Route::middleware('auth:sanctum')->prefix('v1/wallet')->name('api.wallet.')->group(function () {
    // API endpoints for future client/mobile app integrations
});
