@extends('layouts.user')

@section('title', 'صورت سود و زیان')

@php
    use Morilog\Jalali\Jalalian;
    use Modules\Accounting\App\Services\CurrencyService;

    $currencyLabel = CurrencyService::getBaseCurrency();

    $displayTotalIncome = CurrencyService::convertForDisplay($totalIncome);
    $displayTotalExpense = CurrencyService::convertForDisplay($totalExpense);
    $displayNetProfit = CurrencyService::convertForDisplay($netProfit);
    $profitMargin = ($totalIncome > 0) ? round(($netProfit / $totalIncome) * 100, 1) : 0;

    $statCardClass = "rounded-3xl border p-5 flex items-center gap-4 overflow-hidden shadow-sm backdrop-blur-xl";
@endphp

@section('content')
@includeIf('partials.jalali-date-picker')

<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl text-white shadow-lg shadow-teal-700/30 shrink-0 bg-teal-700"
                    style="background-color: #0f766e;">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                        صورت سود و زیان
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        گزارش عملکرد مالی از تاریخ <span
                            class="font-bold text-gray-700 dark:text-gray-200 dir-ltr inline-block">{{ jdate($startDate)->format('Y/m/d') }}</span>
                        تا تاریخ <span
                            class="font-bold text-gray-700 dark:text-gray-200 dir-ltr inline-block">{{ jdate($endDate)->format('Y/m/d') }}</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.accounting.reports.index') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gray-100 text-gray-700 font-bold text-sm hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition-all duration-200 active:scale-95">
                <svg class="w-5 h-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                     stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت به گزارش‌ها
            </a>
        </div>
    </div>

    {{-- Summary Cards Strip --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Total Income --}}
        <div
            class="{{ $statCardClass }} bg-emerald-50/60 dark:bg-emerald-500/5 border-emerald-100 dark:border-emerald-500/20">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <span
                    class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide block mb-1">مجموع درآمدها</span>
                <div class="flex items-baseline gap-1">
                    <span
                        class="text-xl xl:text-2xl font-black text-emerald-700 dark:text-emerald-400 tabular-nums">{{ number_format($displayTotalIncome) }}</span>
                    <span
                        class="text-xs font-bold text-emerald-600/70 dark:text-emerald-400/70">{{ $currencyLabel }}</span>
                </div>
            </div>
        </div>

        {{-- Total Expenses --}}
        <div class="{{ $statCardClass }} bg-rose-50/60 dark:bg-rose-500/5 border-rose-100 dark:border-rose-500/20">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"/>
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <span class="text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wide block mb-1">مجموع هزینه‌ها</span>
                <div class="flex items-baseline gap-1">
                    <span
                        class="text-xl xl:text-2xl font-black text-rose-700 dark:text-rose-400 tabular-nums">{{ number_format($displayTotalExpense) }}</span>
                    <span class="text-xs font-bold text-rose-600/70 dark:text-rose-400/70">{{ $currencyLabel }}</span>
                </div>
            </div>
        </div>

        {{-- Net Profit / Loss --}}
        <div
            class="{{ $statCardClass }} {{ $netProfit >= 0 ? 'bg-teal-50/60 dark:bg-teal-500/5 border-teal-100 dark:border-teal-500/20' : 'bg-amber-50/60 dark:bg-amber-500/5 border-amber-100 dark:border-amber-500/20' }}">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-2xl {{ $netProfit >= 0 ? 'bg-teal-100 dark:bg-teal-500/20 text-teal-600 dark:text-teal-400' : 'bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400' }} shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <span
                    class="text-xs font-bold {{ $netProfit >= 0 ? 'text-teal-600 dark:text-teal-400' : 'text-amber-600 dark:text-amber-400' }} uppercase tracking-wide block mb-1">
                    {{ $netProfit >= 0 ? 'سود خالص' : 'زیان خالص' }}
                </span>
                <div class="flex items-baseline gap-1">
                    <span
                        class="text-xl xl:text-2xl font-black {{ $netProfit >= 0 ? 'text-teal-700 dark:text-teal-400' : 'text-amber-700 dark:text-amber-400' }} tabular-nums">{{ number_format(abs($displayNetProfit)) }}</span>
                    <span class="text-xs font-bold opacity-70">{{ $currencyLabel }}</span>
                </div>
            </div>
        </div>

        {{-- Profit Margin --}}
        <div class="{{ $statCardClass }} bg-white dark:bg-gray-800/60 border-gray-100 dark:border-gray-700/50">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-1">حاشیه سود</span>
                <div class="flex items-baseline gap-1">
                    <span class="text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums">{{ $profitMargin }}٪</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Date Filter Bar --}}
    <div
        class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
        <form action="{{ route('admin.accounting.reports.profit_and_loss') }}" method="GET"
              class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-4">
                <label for="start_date" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">از
                    تاریخ</label>
                <input type="text" name="start_date" id="start_date" data-jdp-only-date
                       placeholder="1403/01/01"
                       class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white dir-ltr text-center"
                       value="{{ request('start_date') ? preg_replace('/[^0-9\/]/', '', explode(' ', trim(request('start_date')))[0]) : jdate($startDate)->format('Y/m/d') }}">
            </div>

            <div class="md:col-span-4">
                <label for="end_date" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">تا
                    تاریخ</label>
                <input type="text" name="end_date" id="end_date" data-jdp-only-date
                       placeholder="1403/12/29"
                       class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white dir-ltr text-center"
                       value="{{ request('end_date') ? preg_replace('/[^0-9\/]/', '', explode(' ', trim(request('end_date')))[0]) : jdate($endDate)->format('Y/m/d') }}">
            </div>

            <div class="md:col-span-4 flex gap-2">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 shadow-md shadow-indigo-500/30 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    اعمال فیلتر
                </button>
                <a href="{{ route('admin.accounting.reports.profit_and_loss') }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-2xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 font-bold text-sm hover:bg-gray-200 transition-all">
                    ماه جاری
                </a>
                @if(request()->hasAny(['start_date', 'end_date']))
                    <a href="{{ route('admin.accounting.reports.profit_and_loss') }}" title="غیرفعال کردن فیلتر"
                       class="px-4 py-3 rounded-2xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-sm font-bold hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Main Report Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        {{-- Income Breakdown --}}
        <div
            class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl flex flex-col justify-between">
            <div>
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 flex items-center justify-between bg-emerald-50/50 dark:bg-emerald-500/10">
                    <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        درآمدها (تفکیک سرفصل)
                    </h2>
                    <span
                        class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300">
                        {{ $incomes->count() }} سرفصل
                    </span>
                </div>

                <div class="p-6 space-y-5">
                    @forelse($incomes as $income)
                        @php
                            $dispBal = CurrencyService::convertForDisplay($income->balance);
                            $pct = ($totalIncome > 0) ? min(100, round(($income->balance / $totalIncome) * 100, 1)) : 0;
                        @endphp
                        <div class="space-y-2">
                            <div class="flex justify-between items-center text-sm font-bold">
                                <a href="{{ route('admin.accounting.reports.ledger', $income->id) }}"
                                   class="text-gray-800 dark:text-gray-200 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1.5">
                                    <span>{{ $income->title }}</span>
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                                <div class="tabular-nums font-black text-emerald-600 dark:text-emerald-400 text-base">
                                    {{ number_format($dispBal) }} <span
                                        class="text-xs font-normal text-gray-400 ms-0.5">{{ $currencyLabel }}</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700/50 rounded-full h-2 overflow-hidden flex">
                                <div class="bg-emerald-500 h-full rounded-full transition-all duration-500"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="text-sm font-bold">هیچ درآمدی در این بازه زمانی ثبت نشده است.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div
                class="p-6 bg-gray-50/80 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700/50 flex justify-between items-center">
                <span class="text-base font-bold text-gray-900 dark:text-white">مجموع کل درآمدها</span>
                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 tabular-nums">
                    {{ number_format($displayTotalIncome) }} <span
                        class="text-xs font-bold text-emerald-600/70 dark:text-emerald-400/70">{{ $currencyLabel }}</span>
                </div>
            </div>
        </div>

        {{-- Expense Breakdown --}}
        <div
            class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl flex flex-col justify-between">
            <div>
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 flex items-center justify-between bg-rose-50/50 dark:bg-rose-500/10">
                    <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                        هزینه‌ها (تفکیک سرفصل)
                    </h2>
                    <span
                        class="px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300">
                        {{ $expenses->count() }} سرفصل
                    </span>
                </div>

                <div class="p-6 space-y-5">
                    @forelse($expenses as $expense)
                        @php
                            $dispBal = CurrencyService::convertForDisplay($expense->balance);
                            $pct = ($totalExpense > 0) ? min(100, round(($expense->balance / $totalExpense) * 100, 1)) : 0;
                        @endphp
                        <div class="space-y-2">
                            <div class="flex justify-between items-center text-sm font-bold">
                                <a href="{{ route('admin.accounting.reports.ledger', $expense->id) }}"
                                   class="text-gray-800 dark:text-gray-200 hover:text-rose-600 dark:hover:text-rose-400 transition-colors flex items-center gap-1.5">
                                    <span>{{ $expense->title }}</span>
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                                <div class="tabular-nums font-black text-rose-600 dark:text-rose-400 text-base">
                                    {{ number_format($dispBal) }} <span
                                        class="text-xs font-normal text-gray-400 ms-0.5">{{ $currencyLabel }}</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700/50 rounded-full h-2 overflow-hidden flex">
                                <div class="bg-rose-500 h-full rounded-full transition-all duration-500"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="text-sm font-bold">هیچ هزینه‌ای در این بازه زمانی ثبت نشده است.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div
                class="p-6 bg-gray-50/80 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700/50 flex justify-between items-center">
                <span class="text-base font-bold text-gray-900 dark:text-white">مجموع کل هزینه‌ها</span>
                <div class="text-xl font-black text-rose-600 dark:text-rose-400 tabular-nums">
                    {{ number_format($displayTotalExpense) }} <span
                        class="text-xs font-bold text-rose-600/70 dark:text-rose-400/70">{{ $currencyLabel }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
