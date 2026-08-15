<?php

namespace Modules\Accounting\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\App\Models\FundAccount;
use Modules\Accounting\App\Models\Document;
use Modules\Accounting\Entities\Cheque;
use Modules\Accounting\Entities\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Services\CurrencyService;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Assuming permissions are handled via routes or a central middleware
    }

    public function index()
    {
        // 6. Fund Accounts with active status and calculated balances
        $fundAccounts = FundAccount::all()->map(function ($account) {
            $account->calculated_balance = $account->current_balance;
            return $account;
        });

        // 1. Total Cash & Bank Balance
        $totalCashAndBank = CurrencyService::convertForDisplay($fundAccounts->where('status', true)->sum('calculated_balance'));

        // 2. Total Receivables (مطالبات / حساب‌ها و اسناد دریافتنی)
        $recCategoryIds = Category::where(function ($q) {
            $q->whereIn('title', ['حساب‌های دریافتنی', 'اسناد دریافتنی', 'اسناد در جریان وصول', 'اسناد دریافتنی / چک‌های موجود'])
              ->orWhere(function ($sub) {
                  $sub->where('type', 'asset')->where(function ($s) {
                      $s->where('title', 'like', '%دریافتنی%')->orWhere('title', 'like', '%مطالبات%');
                  });
              });
        })->pluck('id');

        $recTransactionsDebit = (float) DB::table('accounting_transactions')->whereIn('category_id', $recCategoryIds)->sum('debit');
        $recTransactionsCredit = (float) DB::table('accounting_transactions')->whereIn('category_id', $recCategoryIds)->sum('credit');
        $netRecTx = max(0, $recTransactionsDebit - $recTransactionsCredit);

        $pendingRecChequesAmount = (float) Cheque::where('type', 'receivable')
            ->whereIn('status', ['pending', 'deposited'])
            ->sum('amount');

        $calculatedReceivables = $pendingRecChequesAmount > 0 ? $pendingRecChequesAmount : $netRecTx;
        if ($netRecTx > 0 && $pendingRecChequesAmount > 0) {
            $calculatedReceivables = max($netRecTx, $pendingRecChequesAmount);
        }

        $totalReceivables = CurrencyService::convertForDisplay($calculatedReceivables);

        // 3. Total Payables (بدهی‌ها / حساب‌ها و اسناد پرداختنی)
        $payCategoryIds = Category::where(function ($q) {
            $q->whereIn('title', ['حساب‌های پرداختنی', 'اسناد پرداختنی', 'بدهی‌ها', 'بدهی‌های جاری', 'مالیات پرداختنی'])
              ->orWhere(function ($sub) {
                  $sub->where('type', 'liability')->where(function ($s) {
                      $s->where('title', 'like', '%پرداختنی%')->orWhere('title', 'like', '%بدهی%');
                  });
              });
        })->pluck('id');

        $payTransactionsDebit = (float) DB::table('accounting_transactions')->whereIn('category_id', $payCategoryIds)->sum('debit');
        $payTransactionsCredit = (float) DB::table('accounting_transactions')->whereIn('category_id', $payCategoryIds)->sum('credit');
        $netPayTx = max(0, $payTransactionsCredit - $payTransactionsDebit);

        $pendingPayChequesAmount = (float) Cheque::where('type', 'payable')
            ->where('status', 'pending')
            ->sum('amount');

        $calculatedPayables = max($netPayTx, $pendingPayChequesAmount);

        $totalPayables = CurrencyService::convertForDisplay($calculatedPayables);

        // 4. Total Pending Cheques (for stat card)
        $totalPendingCheques = CurrencyService::convertForDisplay(Cheque::where('status', 'pending')->sum('amount'));
        $pendingChequesCount = Cheque::where('status', 'pending')->count();

        // 5. Upcoming Cheques (Due in next 7 days)
        $upcomingCheques = Cheque::where('status', 'pending')
            ->whereBetween('due_date', [Carbon::now(), Carbon::now()->addDays(7)])
            ->orderBy('due_date', 'asc')
            ->get();

        // 7. Recent Accounting Documents
        $recentDocuments = Document::with(['transactions.category', 'transactions.fundAccount'])
            ->latest('document_date')
            ->latest('id')
            ->take(5)
            ->get();

        // 8. Stats
        $documentsCount = Document::count();
        $categoriesCount = Category::count();
        $fundCount = $fundAccounts->count();

        return view('accounting::dashboard.index', compact(
            'totalCashAndBank',
            'totalReceivables',
            'totalPayables',
            'totalPendingCheques',
            'pendingChequesCount',
            'upcomingCheques',
            'fundAccounts',
            'recentDocuments',
            'documentsCount',
            'categoriesCount',
            'fundCount'
        ));
    }
}
