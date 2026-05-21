@extends('layouts.user')

@section('title', 'داشبورد حسابداری')

@php
    // Define standard classes for reuse
    $cardClass = "bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 flex items-center gap-4 transition-all hover:shadow-lg hover:-translate-y-1";
    $iconWrapperClass = "w-12 h-12 rounded-xl flex items-center justify-center";

    // Helper function for formatting numbers, wrapped to prevent redeclaration.
    if (!function_exists('format_currency_short')) {
        function format_currency_short($number, $currency = 'تومان') {
            if ($number >= 1000000000) {
                return number_format($number / 1000000000, 1) . ' میلیارد ' . $currency;
            }
            if ($number >= 1000000) {
                return number_format($number / 1000000, 1) . ' میلیون ' . $currency;
            }
            if ($number >= 1000) {
                return number_format($number / 1000, 0) . ' هزار ' . $currency;
            }
            return number_format($number) . ' ' . $currency;
        }
    }
@endphp

@section('content')
    <div class="max-w-7xl mx-auto w-full px-2 sm:px-4 lg:px-6 py-8 space-y-8">

        {{-- Header & Quick Actions --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </span>
                    داشبورد حسابداری
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-14 max-w-2xl leading-relaxed">
                    نمای کلی از وضعیت مالی، درآمدها، هزینه‌ها و تراکنش‌های اخیر.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.accounting.invoices.create') }}" class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    <span>فاکتور جدید</span>
                </a>
                <a href="{{ route('admin.accounting.expenses.create') }}" class="px-4 py-2.5 rounded-xl bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    <span>هزینه جدید</span>
                </a>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 animate-in fade-in slide-in-from-bottom-6 duration-700 delay-100">
            <div class="{{ $cardClass }}">
                <div class="{{ $iconWrapperClass }} bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01" /></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">درآمد (30 روز اخیر)</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white dir-ltr">{{ format_currency_short($stats['income_30d']) }}</p>
                </div>
            </div>
            <div class="{{ $cardClass }}">
                <div class="{{ $iconWrapperClass }} bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" /></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">هزینه (30 روز اخیر)</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white dir-ltr">{{ format_currency_short($stats['expense_30d']) }}</p>
                </div>
            </div>
            <div class="{{ $cardClass }}">
                <div class="{{ $iconWrapperClass }} bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">سود خالص (30 روز اخیر)</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white dir-ltr">{{ format_currency_short($stats['profit_30d']) }}</p>
                </div>
            </div>
            <div class="{{ $cardClass }}">
                <div class="{{ $iconWrapperClass }} bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">فاکتورهای پرداخت نشده</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white dir-ltr">{{ format_currency_short($stats['unpaid_invoices']) }}</p>
                </div>
            </div>
        </div>

        {{-- Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 animate-in fade-in slide-in-from-bottom-8 duration-700 delay-200">
            <div class="lg:col-span-3 bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">روند درآمد و هزینه (6 ماه اخیر)</h3>
                <div id="income-expense-chart" class="h-72"></div>
            </div>
            <div class="lg:col-span-2 bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">دسته‌بندی هزینه‌ها</h3>
                <div id="expense-category-chart" class="h-72"></div>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm animate-in fade-in slide-in-from-bottom-10 duration-700 delay-300">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">آخرین تراکنش‌ها</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-xs text-gray-500 dark:text-gray-400 uppercase">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-right font-medium">شرح</th>
                            <th scope="col" class="px-6 py-3 text-center font-medium">نوع</th>
                            <th scope="col" class="px-6 py-3 text-center font-medium">مبلغ</th>
                            <th scope="col" class="px-6 py-3 text-center font-medium">تاریخ</th>
                            <th scope="col" class="px-6 py-3 text-left font-medium">مشتری/گیرنده</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recentTransactions as $tx)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-800 dark:text-gray-200">{{ $tx->description }}</div>
                                    <div class="text-xs text-gray-500">{{ $tx->category?->name ?? 'بدون دسته' }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($tx->type === 'income')
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">درآمد</span>
                                    @else
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">هزینه</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center font-mono font-semibold {{ $tx->type === 'income' ? 'text-green-600' : 'text-red-600' }}">{{ number_format($tx->amount) }}</td>
                                <td class="px-6 py-4 text-center text-gray-500">{{ jdate($tx->transaction_date)->format('Y/m/d') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-left text-gray-500">{{ $tx->client?->full_name ?? $tx->payable?->name ?? '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12 text-gray-500">
                                    هیچ تراکنش اخیری یافت نشد.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const isDarkMode = document.documentElement.classList.contains('dark');

                // Chart 1: Income vs Expense
                const incomeExpenseOptions = {
                    series: [{
                        name: 'درآمد',
                        data: {!! json_encode($charts['income_expense']['income']) !!}
                    }, {
                        name: 'هزینه',
                        data: {!! json_encode($charts['income_expense']['expense']) !!}
                    }],
                    chart: {
                        height: '100%',
                        type: 'area',
                        fontFamily: 'IRANYekanX, sans-serif',
                        toolbar: { show: false },
                        zoom: { enabled: false }
                    },
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 2 },
                    colors: ['#10B981', '#EF4444'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            opacityFrom: isDarkMode ? 0.6 : 0.8,
                            opacityTo: 0,
                        }
                    },
                    xaxis: {
                        categories: {!! json_encode($charts['income_expense']['labels']) !!},
                        labels: {
                            style: {
                                colors: isDarkMode ? '#9CA3AF' : '#6B7280',
                                fontSize: '12px',
                            },
                        },
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: isDarkMode ? '#9CA3AF' : '#6B7280',
                                fontSize: '12px',
                            },
                            formatter: (value) => {
                                if (value >= 1000000) return (value / 1000000) + ' M';
                                if (value >= 1000) return (value / 1000) + ' K';
                                return value;
                            }
                        }
                    },
                    grid: {
                        borderColor: isDarkMode ? '#374151' : '#E5E7EB',
                        strokeDashArray: 5,
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        labels: { colors: isDarkMode ? '#F9FAFB' : '#374151' }
                    },
                    tooltip: {
                        theme: isDarkMode ? 'dark' : 'light',
                        y: {
                            formatter: (value) => {
                                return new Intl.NumberFormat('fa-IR').format(value) + ' تومان';
                            }
                        }
                    }
                };
                const incomeExpenseChart = new ApexCharts(document.querySelector("#income-expense-chart"), incomeExpenseOptions);
                incomeExpenseChart.render();

                // Chart 2: Expense Categories
                const expenseCategoryOptions = {
                    series: {!! json_encode($charts['expense_categories']['series']) !!},
                    chart: {
                        height: '100%',
                        type: 'donut',
                        fontFamily: 'IRANYekanX, sans-serif',
                    },
                    labels: {!! json_encode($charts['expense_categories']['labels']) !!},
                    colors: ['#EF4444', '#F97316', '#F59E0B', '#EAB308', '#84CC16', '#22C55E'],
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '70%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'جمع هزینه‌ها',
                                        color: isDarkMode ? '#9CA3AF' : '#6B7280',
                                        formatter: function (w) {
                                            const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                            return new Intl.NumberFormat('fa-IR').format(total);
                                        }
                                    }
                                }
                            }
                        }
                    },
                    legend: {
                        position: 'bottom',
                        labels: { colors: isDarkMode ? '#F9FAFB' : '#374151' }
                    },
                    tooltip: {
                        theme: isDarkMode ? 'dark' : 'light',
                        y: {
                            formatter: (value) => {
                                return new Intl.NumberFormat('fa-IR').format(value) + ' تومان';
                            }
                        }
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: '100%'
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };
                const expenseCategoryChart = new ApexCharts(document.querySelector("#expense-category-chart"), expenseCategoryOptions);
                expenseCategoryChart.render();

                // Update charts on theme change
                window.addEventListener('theme-changed', (e) => {
                    const newIsDarkMode = e.detail.isDarkMode;
                    incomeExpenseChart.updateOptions({
                        fill: {
                            gradient: { opacityFrom: newIsDarkMode ? 0.6 : 0.8 }
                        },
                        xaxis: { labels: { style: { colors: newIsDarkMode ? '#9CA3AF' : '#6B7280' } } },
                        yaxis: { labels: { style: { colors: newIsDarkMode ? '#9CA3AF' : '#6B7280' } } },
                        grid: { borderColor: newIsDarkMode ? '#374151' : '#E5E7EB' },
                        legend: { labels: { colors: newIsDarkMode ? '#F9FAFB' : '#374151' } },
                        tooltip: { theme: newIsDarkMode ? 'dark' : 'light' }
                    });
                    expenseCategoryChart.updateOptions({
                        plotOptions: { pie: { donut: { labels: { total: { color: newIsDarkMode ? '#9CA3AF' : '#6B7280' } } } } },
                        legend: { labels: { colors: newIsDarkMode ? '#F9FAFB' : '#374151' } },
                        tooltip: { theme: newIsDarkMode ? 'dark' : 'light' }
                    });
                });
            });
        </script>
    @endpush
@endsection
