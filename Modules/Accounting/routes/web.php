<?php

use Illuminate\Support\Facades\Route;
use Modules\Accounting\App\Http\Controllers\AccountingController;
use Modules\Accounting\App\Http\Controllers\BankController;
use Modules\Accounting\App\Http\Controllers\CategoryController;
use Modules\Accounting\App\Http\Controllers\ChequeController;
use Modules\Accounting\App\Http\Controllers\DashboardController;
use Modules\Accounting\App\Http\Controllers\DocumentController;
use Modules\Accounting\App\Http\Controllers\ExpenseController;
use Modules\Accounting\App\Http\Controllers\InvoiceController;
use Modules\Accounting\App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| All routes are automatically prefixed with 'admin.accounting.'
|
*/

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Invoices
Route::resource('invoices', InvoiceController::class);
Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
Route::post('invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');
Route::put('invoices/{invoice}/revert-payment', [InvoiceController::class, 'revertPayment'])->name('invoices.revert-payment');

// Expenses
Route::resource('expenses', ExpenseController::class);

// Banks
Route::post('banks/transfer', [BankController::class, 'transfer'])->name('banks.transfer');
Route::resource('banks', BankController::class);

// Transactions
Route::resource('transactions', TransactionController::class);

// Documents
Route::resource('documents', DocumentController::class);

// Categories
Route::resource('categories', CategoryController::class);

// Cheques
Route::resource('cheques', ChequeController::class)->except([
    'show'
]);

// Custom Cheque Routes
Route::get('cheques/{cheque}/reconcile', [ChequeController::class, 'showReconcileForm'])->name('cheques.reconcile.form');
Route::put('cheques/{cheque}/reconcile-process', [ChequeController::class, 'reconcile'])->name('cheques.reconcile.process');
Route::put('cheques/{cheque}/cancel-reconcile', [ChequeController::class, 'cancelReconcile'])->name('cheques.cancel-reconcile');


// Settings (Livewire)
Route::get('settings', \Modules\Accounting\App\Livewire\AccountingSettings::class)->name('settings');
