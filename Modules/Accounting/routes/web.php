<?php

use Illuminate\Support\Facades\Route;
use Modules\Accounting\App\Http\Controllers\DashboardController;
use Modules\Accounting\App\Http\Controllers\ReportController;
use Modules\Accounting\App\Http\Controllers\ReceiptController;
use Modules\Accounting\App\Http\Controllers\InvoiceController;
use Modules\Accounting\App\Http\Controllers\ChequeController;
use Modules\Accounting\App\Http\Controllers\ProformaController;
use Modules\Accounting\App\Http\Controllers\ExpenseController;
use Modules\Accounting\App\Http\Controllers\FundAccountController;
use Modules\Accounting\App\Http\Controllers\TransactionController;
use Modules\Accounting\App\Http\Controllers\DocumentController;
use Modules\Accounting\App\Http\Controllers\CategoryController;
use Modules\Accounting\App\Http\Controllers\AccountingSettingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Route names will be prefixed by 'admin.accounting.' automatically
| because of the RouteServiceProvider configuration.
|
*/

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Reports
Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('reports/trial-balance', [ReportController::class, 'trialBalance'])->name('reports.trial_balance');
Route::get('reports/ledger/{category}', [ReportController::class, 'ledger'])->name('reports.ledger');
Route::get('reports/profit-and-loss', [ReportController::class, 'profitAndLoss'])->name('reports.profit_and_loss');
Route::get('reports/balance-sheet', [ReportController::class, 'balanceSheet'])->name('reports.balance_sheet');

// Receipts
Route::get('receipts', function () {
    return redirect()->route('admin.accounting.documents.index');
})->name('receipts.index');
Route::get('receipts/create', [ReceiptController::class, 'create'])->name('receipts.create');
Route::post('receipts', [ReceiptController::class, 'store'])->name('receipts.store');

// Invoices
Route::post('invoices/{invoice}/toggle-status', [InvoiceController::class, 'toggleStatus'])->name('invoices.toggle-status');
Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
Route::post('invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');
Route::put('invoices/{invoice}/revert-payment', [InvoiceController::class, 'revertPayment'])->name('invoices.revert-payment');
Route::resource('invoices', InvoiceController::class);

// Cheques
Route::get('cheques/{cheque}/print', [ChequeController::class, 'print'])->name('cheques.print');
Route::post('cheques/{cheque}/deposit', [ChequeController::class, 'deposit'])->name('cheques.deposit');
Route::post('cheques/{cheque}/clear', [ChequeController::class, 'clear'])->name('cheques.clear');
Route::post('cheques/{cheque}/revert-clearance', [ChequeController::class, 'revertClearance'])->name('cheques.revert-clearance');
Route::post('cheques/{cheque}/bounce', [ChequeController::class, 'bounce'])->name('cheques.bounce');
Route::post('cheques/{cheque}/endorse', [ChequeController::class, 'endorse'])->name('cheques.endorse');
Route::post('cheques/{cheque}/return-with-cash', [ChequeController::class, 'returnChequeWithCash'])->name('cheques.return-with-cash');
Route::resource('cheques', ChequeController::class)->except(['edit', 'update']);

// Other Resources
Route::resource('proformas', ProformaController::class);
Route::post('expenses/{expense}/cancel', [ExpenseController::class, 'cancel'])->name('expenses.cancel');
Route::resource('expenses', ExpenseController::class);
Route::resource('fund-accounts', FundAccountController::class);
Route::post('fund-accounts/transfer', [FundAccountController::class, 'transfer'])->name('fund-accounts.transfer');
Route::resource('transactions', TransactionController::class);
Route::post('documents/{document}/cancel', [DocumentController::class, 'cancel'])->name('documents.cancel');
Route::resource('documents', DocumentController::class);
Route::resource('categories', CategoryController::class)->except(['show']);

// Settings
Route::get('settings', [AccountingSettingController::class, 'edit'])->name('settings.edit');
Route::put('settings', [AccountingSettingController::class, 'update'])->name('settings.update');
