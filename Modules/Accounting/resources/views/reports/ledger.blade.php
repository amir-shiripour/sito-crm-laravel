@extends('layouts.user')

@section('title', 'دفتر معین: ' . $category->title)

@php
    use Morilog\Jalali\Jalalian;
    use Modules\Accounting\App\Services\CurrencyService;

    $currencyLabel = CurrencyService::getBaseCurrency();

    $typeTitles = [
        'asset' => 'دارایی',
        'liability' => 'بدهی',
        'equity' => 'سرمایه',
        'income' => 'درآمد',
        'expense' => 'هزینه',
    ];

    $typeClasses = [
        'asset' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20',
        'liability' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border-amber-200 dark:border-amber-500/20',
        'equity' => 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400 border-purple-200 dark:border-purple-500/20',
        'income' => 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400 border-sky-200 dark:border-sky-500/20',
        'expense' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400 border-rose-200 dark:border-rose-500/20',
    ];

    $statCardClass = "rounded-3xl border p-5 flex items-center gap-4 overflow-hidden shadow-sm backdrop-blur-xl";

    $pageDebitSum = $transactions->sum('debit');
    $pageCreditSum = $transactions->sum('credit');

    $displayPageDebit = CurrencyService::convertForDisplay($pageDebitSum);
    $displayPageCredit = CurrencyService::convertForDisplay($pageCreditSum);
    $displayOpeningBalance = CurrencyService::convertForDisplay($openingBalance);
@endphp

@section('content')
@includeIf('partials.jalali-date-picker')

<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </span>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                            دفتر معین: <span class="text-indigo-600 dark:text-indigo-400">{{ $category->title }}</span>
                        </h1>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $typeClasses[$category->type] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $typeTitles[$category->type] ?? $category->type }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        گزارش جامع گردش حساب سرفصل {{ $category->title }} از تاریخ <span
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
                بازگشت به سرفصل‌ها
            </a>
        </div>
    </div>

    {{-- Summary Cards Strip --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        {{-- Total Transactions --}}
        <div class="{{ $statCardClass }} bg-white dark:bg-gray-800/60 border-gray-100 dark:border-gray-700/50">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-1">تعداد تراکنش‌ها</span>
                <span
                    class="text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums">{{ number_format($transactions->total() ?? $transactions->count()) }}</span>
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
                    class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide block mb-1">گردش بدهکار (این صفحه)</span>
                <div class="flex items-baseline gap-1">
                    <span
                        class="text-xl xl:text-2xl font-black text-emerald-700 dark:text-emerald-400 tabular-nums">{{ number_format($displayPageDebit) }}</span>
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
                <span class="text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wide block mb-1">گردش بستانکار (این صفحه)</span>
                <div class="flex items-baseline gap-1">
                    <span
                        class="text-xl xl:text-2xl font-black text-rose-700 dark:text-rose-400 tabular-nums">{{ number_format($displayPageCredit) }}</span>
                    <span class="text-xs font-bold text-rose-600/70 dark:text-rose-400/70">{{ $currencyLabel }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Date Filter Bar --}}
    <div
        class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
        <form action="{{ route('admin.accounting.reports.ledger', $category) }}" method="GET"
              class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-4">
                <label for="start_date" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">از
                    تاریخ</label>
                <div class="relative">
                    <input type="text" name="start_date" id="start_date" data-jdp-only-date
                           placeholder="1403/01/01"
                           class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white dir-ltr text-center"
                           value="{{ request('start_date') ? preg_replace('/[^0-9\/]/', '', explode(' ', trim(request('start_date')))[0]) : jdate($startDate)->format('Y/m/d') }}">
                </div>
            </div>

            <div class="md:col-span-4">
                <label for="end_date" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">تا
                    تاریخ</label>
                <div class="relative">
                    <input type="text" name="end_date" id="end_date" data-jdp-only-date
                           placeholder="1403/12/29"
                           class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white dir-ltr text-center"
                           value="{{ request('end_date') ? preg_replace('/[^0-9\/]/', '', explode(' ', trim(request('end_date')))[0]) : jdate($endDate)->format('Y/m/d') }}">
                </div>
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
                <a href="{{ route('admin.accounting.reports.ledger', $category) }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-2xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 font-bold text-sm hover:bg-gray-200 transition-all">
                    ماه جاری
                </a>
                @if(request()->hasAny(['start_date', 'end_date']))
                    <a href="{{ route('admin.accounting.reports.ledger', $category) }}" title="غیرفعال کردن فیلتر"
                       class="px-4 py-3 rounded-2xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-sm font-bold hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Ledger Table Card --}}
    <div
        class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                <thead class="bg-gray-50/80 dark:bg-gray-900/40">
                <tr>
                    <th scope="col"
                        class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                        نوع / تاریخ
                    </th>
                    <th scope="col"
                        class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                        حساب خزانه‌داری / منبع
                    </th>
                    <th scope="col"
                        class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                        شرح تراکنش و مستندات
                    </th>
                    <th scope="col"
                        class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                        بدهکار ({{ $currencyLabel }})
                    </th>
                    <th scope="col"
                        class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                        بستانکار ({{ $currencyLabel }})
                    </th>
                    <th scope="col"
                        class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                        مانده حساب ({{ $currencyLabel }})
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                @php
                    $runningBalance = 0;
                    $totalDebit = 0;
                    $totalCredit = 0;
                @endphp

                @forelse($transactions as $transaction)
                    @php
                        $runningBalance += ($transaction->debit - $transaction->credit);
                        $totalDebit += $transaction->debit;
                        $totalCredit += $transaction->credit;

                        $dispDebit = CurrencyService::convertForDisplay($transaction->debit);
                        $dispCredit = CurrencyService::convertForDisplay($transaction->credit);
                        $dispRunning = CurrencyService::convertForDisplay($runningBalance);
                    @endphp
                    <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col items-start gap-1">
                                @if($transaction->debit > 0)
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            بدهکار / ورودی
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-100 dark:border-rose-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            بستانکار / خروجی
                                        </span>
                                    @endif
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 tabular-nums">
                                        {{ jdate($transaction->transaction_date)->format('Y/m/d') }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                      stroke-linejoin="round"
                                                                                      d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H4a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    {{ $transaction->fundAccount->name ?? 'صندوق عمومی' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white font-medium max-w-md">
                                <div
                                    class="text-sm leading-relaxed font-bold text-gray-800 dark:text-gray-100">{{ $transaction->description ?: 'بدون شرح' }}</div>
                                @if($transaction->document && $transaction->document->documentable)
                                    <div class="mt-2 flex items-center gap-2">
                                        @if($transaction->document->documentable_type === \Modules\Accounting\Entities\Invoice::class)
                                            <a href="{{ route('admin.accounting.invoices.show', $transaction->document->documentable->id) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 hover:bg-indigo-100 transition-all border border-indigo-100 dark:border-indigo-500/20">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                فاکتور شماره {{ $transaction->document->documentable->invoice_number }}
                                            </a>
                                        @elseif($transaction->document->documentable_type === \Modules\Accounting\Entities\Cheque::class)
                                            <a href="{{ route('admin.accounting.cheques.show', $transaction->document->documentable->id) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400 hover:bg-purple-100 transition-all border border-purple-100 dark:border-purple-500/20">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H4a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                                </svg>
                                                چک شماره {{ $transaction->document->documentable->cheque_number }}
                                            </a>
                                        @else
                                            <a href="{{ route('admin.accounting.documents.show', $transaction->document->id) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400 hover:bg-sky-100 transition-all border border-sky-100 dark:border-sky-500/20">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                                سند شماره {{ $transaction->document->document_number }}
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($transaction->debit > 0)
                                    <span
                                        class="font-black text-emerald-600 dark:text-emerald-400 text-base tabular-nums">
                                        {{ number_format($dispDebit) }}
                                    </span>
                                    <span class="text-xs text-gray-400 font-normal ms-1">{{ $currencyLabel }}</span>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600 font-mono">---</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($transaction->credit > 0)
                                    <span class="font-black text-rose-600 dark:text-rose-400 text-base tabular-nums">
                                        {{ number_format($dispCredit) }}
                                    </span>
                                    <span class="text-xs text-gray-400 font-normal ms-1">{{ $currencyLabel }}</span>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600 font-mono">---</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex flex-col items-center">
                                    <div class="font-black text-gray-900 dark:text-white text-base tabular-nums">
                                        {{ number_format(abs($dispRunning)) }}
                                        <span
                                            class="text-xs text-gray-400 font-normal ms-0.5">{{ $currencyLabel }}</span>
                                    </div>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold mt-0.5 {{ $runningBalance > 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : ($runningBalance < 0 ? 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300') }}">
                                        {{ $runningBalance > 0 ? 'بد (بدهکار)' : ($runningBalance < 0 ? 'بس (بستانکار)' : 'تسویه') }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div
                                    class="w-20 h-20 rounded-full bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center mb-4 shadow-inner mx-auto">
                                    <svg class="w-9 h-9 text-indigo-400" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">هیچ تراکنشی برای این
                                    سرفصل ثبت نشده است</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">در بازه زمانی انتخاب‌شده تراکنشی
                                    برای سرفصل {{ $category->title }} یافت نشد.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50/90 dark:bg-gray-900/60 border-t-2 border-gray-100 dark:border-gray-700">
                <tr class="font-black text-gray-900 dark:text-white">
                    <td colspan="3" class="px-6 py-5 text-start text-sm font-bold text-gray-700 dark:text-gray-300">
                        مجموع گردش این صفحه ({{ $currencyLabel }})
                    </td>
                    <td class="px-6 py-5 text-center">
                            <span class="font-black text-emerald-600 dark:text-emerald-400 text-base tabular-nums">
                                {{ number_format(CurrencyService::convertForDisplay($totalDebit)) }}
                            </span>
                        <span class="text-xs font-normal text-gray-400 ms-1">{{ $currencyLabel }}</span>
                    </td>
                    <td class="px-6 py-5 text-center">
                            <span class="font-black text-rose-600 dark:text-rose-400 text-base tabular-nums">
                                {{ number_format(CurrencyService::convertForDisplay($totalCredit)) }}
                            </span>
                        <span class="text-xs font-normal text-gray-400 ms-1">{{ $currencyLabel }}</span>
                    </td>
                    <td class="px-6 py-5 text-center">
                            <span class="font-black text-gray-900 dark:text-white text-base tabular-nums">
                                {{ number_format(abs(CurrencyService::convertForDisplay($runningBalance))) }}
                            </span>
                        <span class="text-xs font-normal text-gray-400 ms-1">{{ $currencyLabel }}</span>
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>

        {{-- Pagination --}}
        @if($transactions->hasPages())
            <div class="px-6 py-5 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
