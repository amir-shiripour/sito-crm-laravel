<?php

namespace Modules\Accounting\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\App\Models\FundAccount;
use Modules\Accounting\App\Models\Transaction;
use Morilog\Jalali\Jalalian;
use Modules\Accounting\App\Services\CurrencyService;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $categoryQuery = Category::with(['transactions']);

        if ($request->filled('search')) {
            $categoryQuery->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('type')) {
            $categoryQuery->where('type', $request->type);
        }

        $categoryBalances = $categoryQuery->get()->map(function ($category) {
            $rawDebit = (float) $category->transactions->sum('debit');
            $rawCredit = (float) $category->transactions->sum('credit');
            switch ($category->type) {
                case 'asset': case 'expense': $balance = $rawDebit - $rawCredit; break;
                case 'liability': case 'equity': case 'income': case 'revenue': $balance = $rawCredit - $rawDebit; break;
                default: $balance = 0;
            }
            return [
                'id' => $category->id,
                'title' => $category->title,
                'type' => $category->type,
                'balance' => $balance,
                'total_debit' => $rawDebit,
                'total_credit' => $rawCredit
            ];
        });

        $fundAccountBalances = FundAccount::with(['transactions'])->get()->map(function ($account) {
            return [
                'name' => $account->name,
                'type' => $account->type,
                'balance' => $account->current_balance
            ];
        });

        return view('accounting::reports.index', compact('categoryBalances', 'fundAccountBalances'));
    }

    public function trialBalance(Request $request)
    {
        $now = Jalalian::now();

        $startDateCarbon = $this->parseJalaliDate($request->input('start_date'));
        $endDateCarbon = $this->parseJalaliDate($request->input('end_date'));

        $startDate = $startDateCarbon
            ? $startDateCarbon->startOfDay()
            : (new Jalalian($now->getYear(), 1, 1))->toCarbon()->startOfDay();

        $endDate = $endDateCarbon
            ? $endDateCarbon->endOfDay()
            : $now->toCarbon()->endOfDay();

        $query = Category::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $query->withSum(['transactions as total_debit' => function($q) use ($startDate, $endDate) {
            $q->whereBetween('transaction_date', [$startDate, $endDate]);
        }], 'debit');

        $query->withSum(['transactions as total_credit' => function($q) use ($startDate, $endDate) {
            $q->whereBetween('transaction_date', [$startDate, $endDate]);
        }], 'credit');

        $accounts = $query->get();

        $activeAccounts = $accounts->filter(function ($account) {
            return ($account->total_debit ?? 0) > 0 || ($account->total_credit ?? 0) > 0;
        })->map(function ($account) {
            $debit = $account->total_debit ?? 0;
            $credit = $account->total_credit ?? 0;
            if (in_array($account->type, ['asset', 'expense'])) {
                $account->calculated_balance = $debit - $credit;
            } elseif (in_array($account->type, ['liability', 'equity', 'revenue', 'income'])) {
                $account->calculated_balance = $credit - $debit;
            } else {
                $account->calculated_balance = 0;
            }
            return $account;
        });

        $finalTotals = [
            'debit' => $activeAccounts->sum('total_debit'),
            'credit' => $activeAccounts->sum('total_credit')
        ];

        return view('accounting::reports.trial_balance', [
            'accounts' => $activeAccounts,
            'totals' => $finalTotals,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    private function parseJalaliDate(?string $dateInput)
    {
        if (empty($dateInput)) {
            return null;
        }

        // Extract clean YYYY/MM/DD part (ignore any appended times or malformed strings like "1405/05/01 NaN:NaN")
        $datePart = trim(explode(' ', trim($dateInput))[0]);

        // Convert Persian/Arabic digits to English digits
        $datePart = str_replace(
            ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩'],
            [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
            $datePart
        );

        try {
            return Jalalian::fromFormat('Y/m/d', $datePart)->toCarbon();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function ledger(Request $request, Category $category)
    {
        $now = Jalalian::now();

        $startDateCarbon = $this->parseJalaliDate($request->input('start_date'));
        $endDateCarbon = $this->parseJalaliDate($request->input('end_date'));

        $startDate = $startDateCarbon
            ? $startDateCarbon->startOfDay()
            : (new Jalalian($now->getYear(), 1, 1))->toCarbon()->startOfDay();

        $endDate = $endDateCarbon
            ? $endDateCarbon->endOfDay()
            : $now->toCarbon()->endOfDay();

        $query = Transaction::where('category_id', $category->id)
                            ->with(['document.documentable', 'fundAccount'])
                            ->whereBetween('transaction_date', [$startDate, $endDate])
                            ->orderBy('transaction_date', 'asc');

        $transactions = $query->paginate(50);

        $openingBalanceQuery = Transaction::where('category_id', $category->id)
                                          ->where('transaction_date', '<', $startDate);

        $openingDebit = $openingBalanceQuery->sum('debit');
        $openingCredit = $openingBalanceQuery->clone()->sum('credit');

        if (in_array($category->type, ['asset', 'expense'])) {
            $openingBalance = $openingDebit - $openingCredit;
        } else {
            $openingBalance = $openingCredit - $openingDebit;
        }

        return view('accounting::reports.ledger', [
            'category' => $category,
            'transactions' => $transactions,
            'openingBalance' => $openingBalance,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function profitAndLoss(Request $request)
    {
        $now = Jalalian::now();

        $startDateCarbon = $this->parseJalaliDate($request->input('start_date'));
        $endDateCarbon = $this->parseJalaliDate($request->input('end_date'));

        $startDate = $startDateCarbon
            ? $startDateCarbon->startOfDay()
            : (new Jalalian($now->getYear(), 1, 1))->toCarbon()->startOfDay();

        $endDate = $endDateCarbon
            ? $endDateCarbon->endOfDay()
            : $now->toCarbon()->endOfDay();

        $incomes = Category::whereIn('type', ['income', 'revenue'])
            ->withSum(['transactions as total_debit' => function($q) use ($startDate, $endDate) { $q->whereBetween('transaction_date', [$startDate, $endDate]); }], 'debit')
            ->withSum(['transactions as total_credit' => function($q) use ($startDate, $endDate) { $q->whereBetween('transaction_date', [$startDate, $endDate]); }], 'credit')
            ->get()
            ->filter(function ($category) { return ($category->total_debit ?? 0) > 0 || ($category->total_credit ?? 0) > 0; })
            ->map(function ($category) {
                $category->balance = ($category->total_credit ?? 0) - ($category->total_debit ?? 0);
                return $category;
            });

        $totalIncome = $incomes->sum('balance');

        $expenses = Category::where('type', 'expense')
            ->withSum(['transactions as total_debit' => function($q) use ($startDate, $endDate) { $q->whereBetween('transaction_date', [$startDate, $endDate]); }], 'debit')
            ->withSum(['transactions as total_credit' => function($q) use ($startDate, $endDate) { $q->whereBetween('transaction_date', [$startDate, $endDate]); }], 'credit')
            ->get()
            ->filter(function ($category) { return ($category->total_debit ?? 0) > 0 || ($category->total_credit ?? 0) > 0; })
            ->map(function ($category) {
                $category->balance = ($category->total_debit ?? 0) - ($category->total_credit ?? 0);
                return $category;
            });

        $totalExpense = $expenses->sum('balance');

        $netProfit = $totalIncome - $totalExpense;

        return view('accounting::reports.profit_and_loss', compact('incomes', 'totalIncome', 'expenses', 'totalExpense', 'netProfit', 'startDate', 'endDate'));
    }

    public function balanceSheet(Request $request)
    {
        $now = Jalalian::now();

        $startDateCarbon = $this->parseJalaliDate($request->input('start_date'));
        $endDateCarbon = $this->parseJalaliDate($request->input('end_date') ?? $request->input('date'));

        $startDate = $startDateCarbon
            ? $startDateCarbon->startOfDay()
            : (new Jalalian($now->getYear(), 1, 1))->toCarbon()->startOfDay();

        $endDate = $endDateCarbon
            ? $endDateCarbon->endOfDay()
            : $now->toCarbon()->endOfDay();

        $asOfDate = $endDate;

        // Assets
        $assets = Category::where('type', 'asset')
            ->withSum(['transactions as total_debit' => function($q) use ($endDate) { $q->where('transaction_date', '<=', $endDate); }], 'debit')
            ->withSum(['transactions as total_credit' => function($q) use ($endDate) { $q->where('transaction_date', '<=', $endDate); }], 'credit')
            ->get()
            ->map(function ($category) {
                $category->balance = ($category->total_debit ?? 0) - ($category->total_credit ?? 0);
                return $category;
            })->filter(fn($cat) => $cat->balance != 0);

        $totalAssets = $assets->sum('balance');

        // Liabilities
        $liabilities = Category::where('type', 'liability')
            ->withSum(['transactions as total_debit' => function($q) use ($endDate) { $q->where('transaction_date', '<=', $endDate); }], 'debit')
            ->withSum(['transactions as total_credit' => function($q) use ($endDate) { $q->where('transaction_date', '<=', $endDate); }], 'credit')
            ->get()
            ->map(function ($category) {
                $category->balance = ($category->total_credit ?? 0) - ($category->total_debit ?? 0);
                return $category;
            })->filter(fn($cat) => $cat->balance != 0);

        $totalLiabilities = $liabilities->sum('balance');

        // Equity
        $equities = Category::where('type', 'equity')
            ->withSum(['transactions as total_debit' => function($q) use ($endDate) { $q->where('transaction_date', '<=', $endDate); }], 'debit')
            ->withSum(['transactions as total_credit' => function($q) use ($endDate) { $q->where('transaction_date', '<=', $endDate); }], 'credit')
            ->get()
            ->map(function ($category) {
                $category->balance = ($category->total_credit ?? 0) - ($category->total_debit ?? 0);
                return $category;
            })->filter(fn($cat) => $cat->balance != 0);

        // Cumulative Retained Earnings / Current Period Net Profit (Income - Expense up to endDate)
        $totalIncome = Transaction::whereHas('category', fn($q) => $q->whereIn('type', ['income', 'revenue']))
            ->where('transaction_date', '<=', $endDate)
            ->sum(\DB::raw('credit - debit'));

        $totalExpense = Transaction::whereHas('category', fn($q) => $q->where('type', 'expense'))
            ->where('transaction_date', '<=', $endDate)
            ->sum(\DB::raw('debit - credit'));

        $retainedEarnings = (float)$totalIncome - (float)$totalExpense;
        $totalEquity = $equities->sum('balance') + $retainedEarnings;

        return view('accounting::reports.balance_sheet', compact(
            'assets', 'totalAssets', 'liabilities', 'totalLiabilities', 'equities', 'retainedEarnings', 'totalEquity', 'asOfDate', 'startDate', 'endDate'
        ));
    }
}
