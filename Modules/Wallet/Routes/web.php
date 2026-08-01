<?php

use Illuminate\Support\Facades\Route;
use Modules\Wallet\App\Http\Controllers\User\WalletController;

Route::prefix('wallet')->name('wallet.')->group(function () {
    Route::get('/', [WalletController::class, 'index'])->name('index');
    Route::get('/transactions', [WalletController::class, 'transactions'])->name('transactions.index');
    Route::post('/deposit', [WalletController::class, 'deposit'])->name('deposit');
    Route::post('/withdraw', [WalletController::class, 'withdraw'])->name('withdraw');
    Route::post('/{wallet}/toggle-status', [WalletController::class, 'toggleStatus'])->name('toggle-status');
});
