@extends('layouts.user')

@section('title', 'گزارش تراز آزمایشی (Trial Balance)')

@php
    use Modules\Accounting\App\Services\CurrencyService;

    $currencyLabel = CurrencyService::getBaseCurrency();
    $statCardClass = "p-5 rounded-3xl border shadow-sm backdrop-blur-xl flex items-center gap-4 transition-all duration-300 hover:shadow-md";

    $typeTitles = [
        'asset' => 'دارایی',
        'liability' => 'بدهی',
        'equity' => 'حقوق صاحبان سهام',
        'income' => 'درآمد',
        'revenue' => 'درآمد',
        'expense' => 'هزینه',
    ];

    $typeClasses = [
        'asset' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20',
        'liability' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
        'equity' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-500/10 dark:text-purple-400 dark:border-purple-500/20',
        'income' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20',
        'revenue' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20',
        'expense' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20',
    ];

    $totalDebit = $totals['debit'] ?? 0;
    $totalCredit = $totals['credit'] ?? 0;
    $diff = abs($totalDebit - $totalCredit);
    $isBalanced = $diff < 1;

    $dispTotalDebit = CurrencyService::convertForDisplay($totalDebit);
    $dispTotalCredit = CurrencyService::convertForDisplay($totalCredit);
@endphp

@section('content')
@includeIf('partials.jalali-date-picker')

<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl text-white shadow-lg shadow-indigo-500/30 shrink-0 bg-indigo-600"
                    style="background-color: #4f46e5;">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 18h12l3-18H3z"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                        گزارش تراز آزمایشی (Trial Balance)
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        خلاصه گردش بدهکار و بستانکار سرفصل‌ها از تاریخ <span
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
        {{-- Total Active Accounts --}}
        <div class="{{ $statCardClass }} bg-white dark:bg-gray-800/60 border-gray-100 dark:border-gray-700/50">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <span
                    class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-1">تعداد سرفصل‌های فعال</span>
                <span
                    class="text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums">{{ number_format($accounts->count()) }}</span>
            </div>
        </div>

        {{-- Total Debit --}}
        <div
            class="{{ $statCardClass }} bg-emerald-50/60 dark:bg-emerald-500/5 border-emerald-100 dark:border-emerald-500/20">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <span
                    class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide block mb-1">مجموع گردش بدهکار</span>
                <div class="flex items-baseline gap-1">
                    <span
                        class="text-xl xl:text-2xl font-black text-emerald-700 dark:text-emerald-400 tabular-nums">{{ number_format($dispTotalDebit) }}</span>
                    <span
                        class="text-xs font-bold text-emerald-600/70 dark:text-emerald-400/70">{{ $currencyLabel }}</span>
                </div>
            </div>
        </div>

        {{-- Total Credit --}}
        <div class="{{ $statCardClass }} bg-rose-50/60 dark:bg-rose-500/5 border-rose-100 dark:border-rose-500/20">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <span class="text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wide block mb-1">مجموع گردش بستانکار</span>
                <div class="flex items-baseline gap-1">
                    <span
                        class="text-xl xl:text-2xl font-black text-rose-700 dark:text-rose-400 tabular-nums">{{ number_format($dispTotalCredit) }}</span>
                    <span class="text-xs font-bold text-rose-600/70 dark:text-rose-400/70">{{ $currencyLabel }}</span>
                </div>
            </div>
        </div>

        {{-- Balance Status --}}
        <div
            class="{{ $statCardClass }} {{ $isBalanced ? 'bg-teal-50/60 dark:bg-teal-500/5 border-teal-100 dark:border-teal-500/20' : 'bg-amber-50/60 dark:bg-amber-500/5 border-amber-100 dark:border-amber-500/20' }}">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-2xl {{ $isBalanced ? 'bg-teal-100 dark:bg-teal-500/20 text-teal-600 dark:text-teal-400' : 'bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400' }} shrink-0">
                @if($isBalanced)
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @else
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                @endif
            </span>
            <div class="min-w-0 flex-1">
                <span
                    class="text-xs font-bold {{ $isBalanced ? 'text-teal-600 dark:text-teal-400' : 'text-amber-600 dark:text-amber-400' }} uppercase tracking-wide block mb-1">وضعیت تراز</span>
                <span
                    class="text-sm font-black {{ $isBalanced ? 'text-teal-700 dark:text-teal-400' : 'text-amber-700 dark:text-amber-400' }}">
                    {{ $isBalanced ? 'معادله تراز برقرار است' : 'عدم توازن تراز' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Filter Card Bar --}}
    <div
        class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
        <form action="{{ route('admin.accounting.reports.trial_balance') }}" method="GET"
              class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-3">
                <label for="search" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">جستجوی
                    سرفصل</label>
                <input type="text" name="search" id="search" placeholder="نام یا عنوان سرفصل..."
                       value="{{ request('search') }}"
                       class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
            </div>

            <div class="md:col-span-3">
                <label for="type" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">نوع
                    سرفصل</label>
                <select name="type" id="type"
                        class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
                    <option value="">همه انواع سرفصل‌ها</option>
                    <option value="asset" @selected(request('type') == 'asset')>دارایی</option>
                    <option value="liability" @selected(request('type') == 'liability')>بدهی</option>
                    <option value="equity" @selected(request('type') == 'equity')>حقوق صاحبان سهام</option>
                    <option value="income" @selected(request('type') == 'income')>درآمد</option>
                    <option value="expense" @selected(request('type') == 'expense')>هزینه</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label for="start_date" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">از
                    تاریخ</label>
                <input type="text" name="start_date" id="start_date" data-jdp-only-date
                       placeholder="1403/01/01"
                       class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white dir-ltr text-center"
                       value="{{ request('start_date') ? preg_replace('/[^0-9\/]/', '', explode(' ', trim(request('start_date')))[0]) : jdate($startDate)->format('Y/m/d') }}">
            </div>

            <div class="md:col-span-2">
                <label for="end_date" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">تا
                    تاریخ</label>
                <input type="text" name="end_date" id="end_date" data-jdp-only-date
                       placeholder="1403/12/29"
                       class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white dir-ltr text-center"
                       value="{{ request('end_date') ? preg_replace('/[^0-9\/]/', '', explode(' ', trim(request('end_date')))[0]) : jdate($endDate)->format('Y/m/d') }}">
            </div>

            <div class="md:col-span-2 flex gap-2">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 shadow-md shadow-indigo-500/30 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    فیلتر
                </button>
                @if(request()->hasAny(['start_date', 'end_date', 'search', 'type']))
                    <a href="{{ route('admin.accounting.reports.trial_balance') }}" title="غیرفعال کردن فیلتر"
                       class="px-4 py-3 rounded-2xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-sm font-bold hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Trial Balance Table Card --}}
    <div
        class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                <thead class="bg-gray-50/80 dark:bg-gray-900/40">
                <tr>
                    <th scope="col"
                        class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                        کد سرفصل
                    </th>
                    <th scope="col"
                        class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                        نام و نوع سرفصل
                    </th>
                    <th scope="col"
                        class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                        جمع بدهکار ({{ $currencyLabel }})
                    </th>
                    <th scope="col"
                        class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                        جمع بستانکار ({{ $currencyLabel }})
                    </th>
                    <th scope="col"
                        class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                        مانده نهایی ({{ $currencyLabel }})
                    </th>
                    <th scope="col"
                        class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                        عملیات
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                @forelse($accounts as $account)
                    @php
                        $dispDebit = CurrencyService::convertForDisplay($account->total_debit ?? 0);
                        $dispCredit = CurrencyService::convertForDisplay($account->total_credit ?? 0);
                        $dispBalance = CurrencyService::convertForDisplay($account->calculated_balance ?? 0);
                    @endphp
                    <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors duration-200">
                        {{-- Code / ID --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="font-mono text-xs font-bold px-2.5 py-1 rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                    #{{ $account->id }}
                                </span>
                        </td>

                        {{-- Title & Type --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.accounting.reports.ledger', $account->id) }}"
                                   class="font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    {{ $account->title }}
                                </a>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $typeClasses[$account->type] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $typeTitles[$account->type] ?? $account->type }}
                                    </span>
                            </div>
                        </td>

                        {{-- Debit --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if(($account->total_debit ?? 0) > 0)
                                    <span class="font-black tabular-nums text-emerald-600 dark:text-emerald-400">
                                        {{ number_format($dispDebit) }}
                                    </span>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600 font-mono">0</span>
                                @endif
                            </td>

                            {{-- Credit --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if(($account->total_credit ?? 0) > 0)
                                    <span class="font-black tabular-nums text-rose-600 dark:text-rose-400">
                                        {{ number_format($dispCredit) }}
                                    </span>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600 font-mono">0</span>
                                @endif
                            </td>

                            {{-- Calculated Balance --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span
                                    class="font-black tabular-nums {{ ($account->calculated_balance ?? 0) >= 0 ? 'text-gray-900 dark:text-white' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ number_format(abs($dispBalance)) }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="{{ route('admin.accounting.reports.ledger', $account->id) }}"
                                   title="مشاهده دفتر معین"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 text-xs font-bold hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-all">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    دفتر معین
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-16">
                                <div class="flex flex-col items-center justify-center">
                                    <div
                                        class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 mb-4">
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white">هیچ گردش حسابی در این
                                        دوره یافت نشد</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">می‌توانید فیلتر تاریخ یا
                                        جستجو را تغییر دهید.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot
                    class="bg-gray-50/80 dark:bg-gray-900/60 font-bold border-t border-gray-200 dark:border-gray-700">
                <tr>
                    <td colspan="2" class="px-6 py-5 text-start font-black text-gray-900 dark:text-white text-base">
                        جمع کل گردش سیستم:
                    </td>
                    <td class="px-6 py-5 text-center font-black tabular-nums text-emerald-700 dark:text-emerald-400 text-base">
                        {{ number_format($dispTotalDebit) }}
                        <span class="text-xs font-normal text-emerald-600/70 ms-0.5">{{ $currencyLabel }}</span>
                    </td>
                    <td class="px-6 py-5 text-center font-black tabular-nums text-rose-700 dark:text-rose-400 text-base">
                        {{ number_format($dispTotalCredit) }}
                        <span class="text-xs font-normal text-rose-600/70 ms-0.5">{{ $currencyLabel }}</span>
                    </td>
                        <td colspan="2" class="px-6 py-5 text-center">
                            @if($isBalanced)
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-100 text-teal-800 dark:bg-teal-500/20 dark:text-teal-300">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M5 13l4 4L19 7"/></svg>
                                    سیستم کاملاً تراز است
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    اختلاف: {{ number_format(CurrencyService::convertForDisplay($diff)) }} {{ $currencyLabel }}
                                </span>
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
