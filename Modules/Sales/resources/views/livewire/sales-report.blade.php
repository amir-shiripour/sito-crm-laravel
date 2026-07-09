<div class="space-y-6" 
     x-data="{
         funnelData: @entangle('funnelData'),
         lossReasonsData: @entangle('lossReasonsData'),
         forecastingData: @entangle('forecastingData'),
         initCharts() {
             if (window.funnelChartInstance) window.funnelChartInstance.destroy();
             if (window.lossChartInstance) window.lossChartInstance.destroy();
             if (window.forecastChartInstance) window.forecastChartInstance.destroy();

             // 1. Funnel Chart
             const ctxFunnel = document.getElementById('funnelChart').getContext('2d');
             window.funnelChartInstance = new Chart(ctxFunnel, {
                 type: 'bar',
                 data: {
                     labels: this.funnelData.labels,
                     datasets: [
                         {
                             label: 'فعال (باز)',
                             data: this.funnelData.active,
                             backgroundColor: '#6366f1',
                             borderRadius: 8
                         },
                         {
                             label: 'موفق (Won)',
                             data: this.funnelData.won,
                             backgroundColor: '#10b981',
                             borderRadius: 8
                         },
                         {
                             label: 'ناموفق (Lost)',
                             data: this.funnelData.lost,
                             backgroundColor: '#ef4444',
                             borderRadius: 8
                         }
                     ]
                 },
                 options: {
                     responsive: true,
                     maintainAspectRatio: false,
                     scales: {
                         x: { stacked: true, grid: { display: false } },
                         y: { stacked: true }
                     },
                     plugins: {
                         legend: { position: 'bottom', rtl: true }
                     }
                 }
             });

             // 2. Loss Reasons Chart
             const ctxLoss = document.getElementById('lossChart').getContext('2d');
             window.lossChartInstance = new Chart(ctxLoss, {
                 type: 'doughnut',
                 data: {
                     labels: this.lossReasonsData.labels,
                     datasets: [{
                         data: this.lossReasonsData.values,
                         backgroundColor: ['#ef4444', '#f97316', '#3b82f6', '#10b981', '#a855f7', '#64748b'],
                         borderWidth: 0
                     }]
                 },
                 options: {
                     responsive: true,
                     maintainAspectRatio: false,
                     plugins: {
                         legend: { position: 'bottom', rtl: true }
                     }
                 }
             });

             // 3. Forecast Chart
             const ctxForecast = document.getElementById('forecastChart').getContext('2d');
             window.forecastChartInstance = new Chart(ctxForecast, {
                 type: 'line',
                 data: {
                     labels: this.forecastingData.labels,
                     datasets: [{
                         label: 'درآمد تخمینی (Weighted)',
                         data: this.forecastingData.values,
                         borderColor: '#6366f1',
                         backgroundColor: 'rgba(99, 102, 241, 0.1)',
                         fill: true,
                         tension: 0.3,
                         borderWidth: 3,
                         pointBackgroundColor: '#4f46e5'
                      }]
                 },
                 options: {
                     responsive: true,
                     maintainAspectRatio: false,
                     scales: {
                         x: { grid: { display: false } },
                         y: { beginAtZero: true }
                     },
                     plugins: {
                         legend: { display: false }
                     }
                 }
             });
         }
     }"
     x-init="$nextTick(() => initCharts())"
     @report-data-updated.window="funnelData = $event.detail[0].funnelData; lossReasonsData = $event.detail[0].lossReasonsData; forecastingData = $event.detail[0].forecastingData; $nextTick(() => initCharts());"
>
    <!-- Filter Bar Card -->
    <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col md:flex-row items-center justify-between gap-4" dir="rtl">
        <div class="flex flex-wrap items-center gap-3 w-full">
            <span class="text-xs font-bold text-gray-500 dark:text-gray-400">فیلترهای گزارش:</span>
            
            <!-- Date Filter Dropdown -->
            <select wire:model.live="dateFilter" class="text-xs bg-gray-50 dark:bg-gray-900 border-0 rounded-2xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-700 dark:text-300">
                <option value="this_month">این ماه</option>
                <option value="last_month">ماه گذشته</option>
                <option value="last_3_months">سه ماه اخیر</option>
                <option value="all">کل دوره</option>
                <option value="custom">بازه سفارشی</option>
            </select>

            <!-- Custom Date Range picker inputs (if custom selected) -->
            @if($dateFilter === 'custom')
                <div class="flex items-center gap-2 animate-fade-in">
                    <input type="date" wire:model.live="customStartDate" class="text-xs bg-gray-50 dark:bg-gray-900 border-0 rounded-2xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 text-gray-700 dark:text-300">
                    <span class="text-xs text-gray-400">تا</span>
                    <input type="date" wire:model.live="customEndDate" class="text-xs bg-gray-50 dark:bg-gray-900 border-0 rounded-2xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 text-gray-700 dark:text-300">
                </div>
            @endif

            <!-- Sales Agent filter dropdown (if Admin/Manager) -->
            @if($isAdmin)
                <select wire:model.live="selectedUserId" class="text-xs bg-gray-50 dark:bg-gray-900 border-0 rounded-2xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 text-gray-700 dark:text-300">
                    <option value="">همه کارشناسان</option>
                    @foreach($salesAgents as $agent)
                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                    @endforeach
                </select>
            @endif
        </div>
    </div>

    <!-- Top KPI Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" dir="rtl">
        <!-- KPI 1 -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-gray-400 block">ارزش ناخالص خط لوله</span>
                <span class="text-lg font-black text-gray-900 dark:text-white mt-1 block">
                    {{ number_format((float) $stats['total_revenue']) }} ریال
                </span>
            </div>
            <div class="p-3 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 rounded-2xl">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- KPI 2 -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-gray-400 block">ارزش خط لوله (موزون)</span>
                <span class="text-lg font-black text-indigo-600 dark:text-indigo-400 mt-1 block">
                    {{ number_format((float) $stats['weighted_revenue']) }} ریال
                </span>
            </div>
            <div class="p-3 bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 rounded-2xl">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2" />
                </svg>
            </div>
        </div>

        <!-- KPI 3 -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-gray-400 block">پرونده‌های فعال باز</span>
                <span class="text-lg font-black text-gray-900 dark:text-white mt-1 block">
                    {{ number_format($stats['open_deals']) }} پرونده
                </span>
            </div>
            <div class="p-3 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 rounded-2xl">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- KPI 4 -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-gray-400 block">نرخ تبدیل موفقیت</span>
                <span class="text-lg font-black text-emerald-600 dark:text-emerald-400 mt-1 block">
                    {{ $stats['conversion_rate'] }}٪
                </span>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 rounded-2xl">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Charts Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Analysis (Funnel & Forecast) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Funnel Card -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm">
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-4 text-right">نرخ ریزش قیف فروش (مراحل کاریز)</h3>
                <div class="relative h-72" wire:ignore>
                    <canvas id="funnelChart"></canvas>
                </div>
            </div>

            <!-- Forecast Card -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm">
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-2 text-right">پیش‌بینی فروش و جریان درآمد (Weighted Revenue)</h3>
                <p class="text-[10px] text-gray-400 mb-4 text-right">تخمین درآمد احتمالی ۶ ماه آینده بر اساس تاریخ بسته شدن قراردادها</p>
                <div class="relative h-72" wire:ignore>
                    <canvas id="forecastChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Sidebar Analysis (Loss Reasons) -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-4 text-right">تحلیل علل شکست پرونده‌ها</h3>
                <div class="relative h-80" wire:ignore>
                    <canvas id="lossChart"></canvas>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700/50 text-right">
                <span class="text-[10px] text-gray-400 block mb-1 font-bold">بیشترین علت ریزش:</span>
                @php
                    $maxCount = count($lossReasonsData['values']) > 0 ? max($lossReasonsData['values']) : 0;
                    $maxCountIndex = $maxCount > 0 ? array_search($maxCount, $lossReasonsData['values']) : false;
                    $topReason = $maxCountIndex !== false ? ($lossReasonsData['labels'][$maxCountIndex] ?? 'ثبت نشده') : 'ثبت نشده';
                @endphp
                <p class="text-xs font-bold text-red-500">{{ $topReason }}</p>
            </div>
        </div>
    </div>

    <!-- Team Performance Table (Full Width) -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm" dir="rtl">
        <h3 class="text-sm font-bold text-gray-850 dark:text-gray-100 mb-4 text-right">جدول عملکرد کارشناسان فروش</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-bold border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="p-4">نام کارشناس</th>
                        <th class="p-4 text-center">کل پرونده‌ها</th>
                        <th class="p-4 text-center">پرونده‌های موفق (Won)</th>
                        <th class="p-4 text-center">نرخ تبدیل موفقیت</th>
                        <th class="p-4 text-left">مجموع مبلغ فروش موفق</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-gray-900 dark:text-gray-100">
                    @forelse($agentsPerformance as $perf)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/20 transition-colors">
                            <td class="p-4 font-bold text-gray-900 dark:text-white">{{ $perf['name'] }}</td>
                            <td class="p-4 text-center font-semibold">{{ number_format($perf['total_deals']) }}</td>
                            <td class="p-4 text-center text-emerald-600 font-bold">{{ number_format($perf['won_deals']) }}</td>
                            <td class="p-4 text-center">
                                <div class="inline-flex items-center gap-1.5 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 px-2 py-0.5 rounded-full font-bold">
                                    {{ $perf['conversion_rate'] }}٪
                                </div>
                            </td>
                            <td class="p-4 text-left font-black text-emerald-600 dark:text-emerald-400">{{ number_format((float) $perf['won_revenue']) }} ریال</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400">
                                اطلاعات عملکردی برای کارشناسان در این بازه یافت نشد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
