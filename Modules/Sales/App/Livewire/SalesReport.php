<?php

declare(strict_types=1);

namespace Modules\Sales\App\Livewire;

use Livewire\Component;
use Modules\Sales\App\Models\SalesDeal;
use Modules\Sales\App\Models\SalesPipeline;
use Modules\Sales\App\Models\SalesLossReason;
use Illuminate\Support\Facades\DB;

class SalesReport extends Component
{
    public array $funnelData = [];
    public array $lossReasonsData = [];
    public array $forecastingData = [];
    public array $stats = [];
    public array $agentsPerformance = [];

    // Filters
    public string $dateFilter = 'this_month'; // 'this_month', 'last_month', 'last_3_months', 'all'
    public ?string $customStartDate = null;
    public ?string $customEndDate = null;
    public ?int $selectedUserId = null;

    protected $queryString = ['dateFilter', 'selectedUserId'];

    public function mount()
    {
        $this->loadReportData();
    }

    public function updatedDateFilter()
    {
        $this->loadReportData();
    }

    public function updatedSelectedUserId()
    {
        $this->loadReportData();
    }

    public function loadReportData()
    {
        $user = auth()->user();

        // Build the base deals query with security scope
        $dealsQuery = SalesDeal::query()->visibleForUser($user);

        // Apply Agent Filter (if user is admin/manager and filter is chosen)
        $isAdmin = $user->hasRole('super-admin') || $user->can('sales.manage') || $user->can('sales.deals.view.all');
        if ($isAdmin && $this->selectedUserId) {
            $dealsQuery->where('user_id', $this->selectedUserId);
        }

        // Apply Date Filters
        switch ($this->dateFilter) {
            case 'this_month':
                $dealsQuery->where('created_at', '>=', now()->startOfMonth());
                break;
            case 'last_month':
                $dealsQuery->whereBetween('created_at', [
                    now()->subMonth()->startOfMonth(),
                    now()->subMonth()->endOfMonth()
                ]);
                break;
            case 'last_3_months':
                $dealsQuery->where('created_at', '>=', now()->subMonths(3)->startOfMonth());
                break;
            case 'custom':
                if ($this->customStartDate && $this->customEndDate) {
                    $dealsQuery->whereBetween('created_at', [$this->customStartDate, $this->customEndDate]);
                }
                break;
        }

        $deals = $dealsQuery->get();

        $totalDealsCount = $deals->count();
        $openDeals = $deals->where('status', 'open');
        $openDealsCount = $openDeals->count();

        $wonDeals = $deals->where('status', 'won');
        $wonDealsCount = $wonDeals->count();

        $lostDeals = $deals->where('status', 'lost');
        $lostDealsCount = $lostDeals->count();

        $totalExpectedRevenue = $openDeals->sum('expected_revenue');
        
        $weightedRevenue = 0.0;
        foreach ($openDeals as $deal) {
            $weightedRevenue += ($deal->expected_revenue * (($deal->probability ?? 0) / 100));
        }

        $conversionRate = $totalDealsCount > 0 ? round(($wonDealsCount / $totalDealsCount) * 100, 1) : 0;

        $this->stats = [
            'total_deals' => $totalDealsCount,
            'open_deals' => $openDealsCount,
            'won_deals' => $wonDealsCount,
            'lost_deals' => $lostDealsCount,
            'total_revenue' => $totalExpectedRevenue,
            'weighted_revenue' => $weightedRevenue,
            'conversion_rate' => $conversionRate,
        ];

        // ۲. نرخ ریزش قیف فروش (Funnel Data)
        $stages = SalesPipeline::orderBy('order')->get();
        $funnelLabels = [];
        $funnelActiveDeals = [];
        $funnelLostDeals = [];
        $funnelWonDeals = [];

        foreach ($stages as $stage) {
            $stageDeals = $deals->where('pipeline_stage_id', $stage->id);
            $funnelLabels[] = $stage->name;
            $funnelActiveDeals[] = $stageDeals->where('status', 'open')->count();
            $funnelLostDeals[] = $stageDeals->where('status', 'lost')->count();
            $funnelWonDeals[] = $stageDeals->where('status', 'won')->count();
        }

        $this->funnelData = [
            'labels' => $funnelLabels,
            'active' => $funnelActiveDeals,
            'lost' => $funnelLostDeals,
            'won' => $funnelWonDeals,
        ];

        // ۳. تحلیل دلایل شکست (Loss Reasons)
        $lossCounts = $lostDeals->groupBy('loss_reason_id');
        $lossLabels = [];
        $lossValues = [];

        foreach ($lossCounts as $reasonId => $items) {
            $reason = SalesLossReason::find($reasonId);
            $lossLabels[] = $reason ? $reason->reason_text : 'نامشخص / تعریف‌نشده';
            $lossValues[] = $items->count();
        }

        // اگر هیچ شکست خورده‌ای با دلیل نباشد، مقدار خالی ندهد
        if (empty($lossLabels)) {
            $lossLabels = ['موردی یافت نشد'];
            $lossValues = [0];
        }

        $this->lossReasonsData = [
            'labels' => $lossLabels,
            'values' => $lossValues,
        ];

        // ۴. پیش‌بینی فروش (Forecasting)
        // گروه‌بندی درآمدها بر اساس ۶ ماه آینده
        $months = [];
        $forecastValues = [];

        for ($i = 0; $i < 6; $i++) {
            $targetDate = now()->addMonths($i);
            $monthKey = $targetDate->format('Y-m');
            $months[$monthKey] = [
                'label' => $this->getPersianMonthName($targetDate->month) . ' ' . $targetDate->year,
                'value' => 0.0
            ];
        }

        foreach ($openDeals as $deal) {
            if ($deal->expected_close_date) {
                $closeMonth = $deal->expected_close_date->format('Y-m');
                if (isset($months[$closeMonth])) {
                    $prob = $deal->probability ?? 0;
                    $months[$closeMonth]['value'] += ($deal->expected_revenue * ($prob / 100));
                }
            }
        }

        $this->forecastingData = [
            'labels' => array_column($months, 'label'),
            'values' => array_column($months, 'value'),
        ];

        // ۵. جدول عملکرد کارشناسان (بر اساس پرونده‌های فیلتر شده)
        $agentsData = [];
        $uniqueUserIds = $deals->pluck('user_id')->filter()->unique()->toArray();
        if (!empty($uniqueUserIds)) {
            $agents = \App\Models\User::whereIn('id', $uniqueUserIds)->get();
            foreach ($agents as $agent) {
                $agentDeals = $deals->where('user_id', $agent->id);
                $agentTotal = $agentDeals->count();
                $agentWon = $agentDeals->where('status', 'won')->count();
                $agentWonRevenue = $agentDeals->where('status', 'won')->sum('actual_revenue');
                $agentConv = $agentTotal > 0 ? round(($agentWon / $agentTotal) * 100, 1) : 0;

                $agentsData[] = [
                    'name' => $agent->name,
                    'total_deals' => $agentTotal,
                    'won_deals' => $agentWon,
                    'won_revenue' => $agentWonRevenue,
                    'conversion_rate' => $agentConv,
                ];
            }
        }
        // مرتب‌سازی کارشناسان بر اساس نرخ تبدیل و فروش بیشتر
        usort($agentsData, function ($a, $b) {
            return $b['won_revenue'] <=> $a['won_revenue'];
        });

        $this->agentsPerformance = $agentsData;

        $this->dispatch('reportDataUpdated', [
            'funnelData' => $this->funnelData,
            'lossReasonsData' => $this->lossReasonsData,
            'forecastingData' => $this->forecastingData,
        ]);
    }

    private function getPersianMonthName(int $month): string
    {
        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];
        
        // تبدیل به معادل فارسی
        $jalaliMonths = [
            1 => 'ژانویه',
            2 => 'فوریه',
            3 => 'مارس',
            4 => 'آوریل',
            5 => 'می',
            6 => 'ژوئن',
            7 => 'جولای',
            8 => 'آگوست',
            9 => 'سپتامبر',
            10 => 'اکتبر',
            11 => 'نوامبر',
            12 => 'دسامبر'
        ];

        return $jalaliMonths[$month] ?? '';
    }

    public function render()
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('super-admin') || $user->can('sales.manage') || $user->can('sales.deals.view.all');
        
        $salesAgents = collect();
        if ($isAdmin) {
            $salesAgents = \App\Models\User::orderBy('name')->get();
        }

        return view('sales::livewire.sales-report', [
            'salesAgents' => $salesAgents,
            'isAdmin' => $isAdmin
        ]);
    }
}
