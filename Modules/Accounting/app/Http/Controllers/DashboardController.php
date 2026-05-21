<?php

namespace Modules\Accounting\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Models\Bank;
use Modules\Accounting\App\Models\Document;
use Modules\Accounting\App\Models\Invoice;
use Modules\Accounting\App\Models\Transaction;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:accounting.dashboard.view');
    }

    public function index()
    {
        // === 1. STATS CARDS (Calculated from Documents) ===
        $stats = [
            'income_30d' => Document::where('type', 'income')->where('document_date', '>=', Carbon::now()->subDays(30))->sum('amount'),
            'expense_30d' => Document::where('type', 'expense')->where('document_date', '>=', Carbon::now()->subDays(30))->sum('amount'),
            'profit_30d' => 0, // Will be calculated after fetching income and expense
            'unpaid_invoices' => Invoice::whereNotIn('status', ['paid', 'cancelled'])->sum('total_amount'),
        ];
        $stats['profit_30d'] = $stats['income_30d'] - $stats['expense_30d'];


        // === 2. CHARTS DATA ===
        $charts = [
            'income_expense' => $this->getIncomeExpenseChartData(),
            'expense_categories' => $this->getExpenseCategoryChartData(),
        ];


        // === 3. RECENT TRANSACTIONS (from transactions table, as it represents actual cash flow) ===
        $recentTransactions = Transaction::with(['invoice.client'])
            ->latest('transaction_date')
            ->take(5)
            ->get();


        return view('accounting::dashboard.index', compact(
            'stats',
            'charts',
            'recentTransactions'
        ));
    }

    /**
     * Prepare data for the income vs. expense chart (last 6 months from Documents).
     */
    private function getIncomeExpenseChartData()
    {
        $labels = [];
        $incomeData = [];
        $expenseData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = jdate($date)->format('F');
            $labels[] = $monthName;

            $incomeData[] = Document::where('type', 'income')
                ->whereYear('document_date', $date->year)
                ->whereMonth('document_date', $date->month)
                ->sum('amount');

            $expenseData[] = Document::where('type', 'expense')
                ->whereYear('document_date', $date->year)
                ->whereMonth('document_date', $date->month)
                ->sum('amount');
        }

        return [
            'labels' => $labels,
            'income' => $incomeData,
            'expense' => $expenseData,
        ];
    }

    /**
     * Prepare data for the expense categories pie chart.
     */
    private function getExpenseCategoryChartData()
    {
        $topCategoriesCount = 5; // Show top 5 categories + "Other"

        $data = Document::query()
            ->where('type', 'expense')
            ->whereNotNull('category_id')
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category:id,title')
            ->orderByDesc('total')
            ->get();

        $series = $data->take($topCategoriesCount)->pluck('total')->toArray();
        $labels = $data->take($topCategoriesCount)->map(fn($item) => $item->category->title)->toArray();

        // Group remaining categories into "Other"
        if ($data->count() > $topCategoriesCount) {
            $otherTotal = $data->skip($topCategoriesCount)->sum('total');
            if ($otherTotal > 0) {
                $series[] = $otherTotal;
                $labels[] = 'سایر موارد';
            }
        }

        return [
            'series' => $series,
            'labels' => $labels,
        ];
    }
}
