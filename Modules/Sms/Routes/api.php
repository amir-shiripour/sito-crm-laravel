<?php

use Illuminate\Support\Facades\Route;
use Modules\Sms\Http\Controllers\Api\OtpController;

Route::prefix('sms')->name('sms.')->group(function () {
    Route::post('/otp/send', [OtpController::class, 'send'])
        ->name('otp.send');

    Route::post('/otp/verify', [OtpController::class, 'verify'])
        ->name('otp.verify');
});
