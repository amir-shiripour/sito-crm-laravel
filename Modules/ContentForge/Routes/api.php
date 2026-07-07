<?php

use Illuminate\Support\Facades\Route;
use Modules\ContentForge\App\Http\Controllers\Api\PostApiController;
use Modules\ContentForge\App\Http\Controllers\Api\CategoryApiController;
use Modules\ContentForge\App\Http\Controllers\Api\EntityApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('content/v1')->group(function () {
    // Public Endpoints
    Route::get('/entities', [EntityApiController::class, 'index']);
    Route::get('/entities/{slug}', [EntityApiController::class, 'show']);
    
    Route::get('/posts', [PostApiController::class, 'index']);
    Route::get('/posts/{slug}', [PostApiController::class, 'show']);
    
    Route::get('/categories', [CategoryApiController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryApiController::class, 'show']);
});
