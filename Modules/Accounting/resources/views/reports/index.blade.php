@php use Modules\Accounting\App\Services\CurrencyService; @endphp
@extends('layouts.user')

@section('title', 'گزارشات و تراز مالی')

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl";

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $currencyLabel = CurrencyService::getBaseCurrency();

    $totalDebitSum  = $categoryBalances->sum('total_debit');
    $totalCreditSum = $categoryBalances->sum('total_credit');
    $accountsCount  = $categoryBalances->count();
    $fundCount      = $fundAccountBalances->count();
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 012 2h2a2 2 0 012-2z"/></svg>
                </span>
                    گزارشات جامع و تراز حسابداری
                </h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">تراز سرفصل‌های حسابداری، ترازنامه مالی، صورت
                    سود و زیان و موجودی‌های خزانه‌داری.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.accounting.reports.profit_and_loss') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 text-sm font-bold hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-all border border-emerald-200 dark:border-emerald-500/20 shadow-sm active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    صورت سود و زیان
                </a>
                <a href="{{ route('admin.accounting.reports.balance_sheet') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 text-sm font-bold hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-all border border-indigo-200 dark:border-indigo-500/20 shadow-sm active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                    </svg>
                    ترازنامه مالی
                </a>
                <a href="{{ route('admin.accounting.reports.trial_balance') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400 text-sm font-bold hover:bg-purple-100 dark:hover:bg-purple-500/20 transition-all border border-purple-200 dark:border-purple-500/20 shadow-sm active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 17v-2m3 2v-4m3 2v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    تراز آزمایشی
                </a>
            </div>
        </div>

        {{-- Summary Strip Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
                class="rounded-3xl border p-5 flex items-center gap-5 bg-white dark:bg-gray-800/60 border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path
                        stroke-linecap="round" stroke-linejoin="round"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 01-2-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </span>
                <div class="min-w-0 flex-1">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-1 truncate">تعداد سرفصل‌ها</span>
                    <span
                        class="text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums">{{ $faNum(number_format($accountsCount)) }}</span>
                </div>
            </div>

            <div
                class="rounded-3xl border p-5 flex items-center gap-5 bg-rose-50/60 dark:bg-rose-500/5 border-rose-100 dark:border-rose-500/20 shadow-sm">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path
                        stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </span>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wide block mb-1 truncate">مجموع بدهکار</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span
                            class="text-xl xl:text-2xl font-black text-rose-700 dark:text-rose-400 tabular-nums">{{ $faNum(number_format($totalDebitSum)) }}</span>
                        <span class="text-[11px] font-medium text-rose-500/80 ms-1">{{ $currencyLabel }}</span>
                    </div>
                </div>
            </div>

            <div
                class="rounded-3xl border p-5 flex items-center gap-5 bg-emerald-50/60 dark:bg-emerald-500/5 border-emerald-100 dark:border-emerald-500/20 shadow-sm">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path
                        stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide block mb-1 truncate">مجموع بستانکار</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span
                            class="text-xl xl:text-2xl font-black text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $faNum(number_format($totalCreditSum)) }}</span>
                        <span class="text-[11px] font-medium text-emerald-500/80 ms-1">{{ $currencyLabel }}</span>
                    </div>
                </div>
            </div>

            <div
                class="rounded-3xl border p-5 flex items-center gap-5 bg-sky-50/60 dark:bg-sky-500/5 border-sky-100 dark:border-sky-500/20 shadow-sm">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-xl bg-sky-100 dark:bg-sky-500/20 text-sky-600 dark:text-sky-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path
                        stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h16.5a1.5 1.5 0 011.5 1.5v9a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5v-9a1.5 1.5 0 011.5-1.5z"/></svg>
            </span>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-xs font-bold text-sky-600 dark:text-sky-400 uppercase tracking-wide block mb-1 truncate">حساب‌های خزانه‌داری</span>
                    <span
                        class="text-xl xl:text-2xl font-black text-sky-700 dark:text-sky-400 tabular-nums">{{ $faNum(number_format($fundCount)) }}</span>
                </div>
            </div>
        </div>

        {{-- Filter bar --}}
        <form method="GET" action="{{ route('admin.accounting.reports.index') }}"
              class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-12 gap-5">
                <div class="relative xl:col-span-8">
                    <div class="absolute inset-y-0 start-0 ps-5 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="جستجو: عنوان سرفصل حسابداری..."
                           class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 ps-12 pe-4 py-3.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
                </div>

                <div class="xl:col-span-2">
                    <select name="type"
                            class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="">همه نوع ماهیت</option>
                        <option value="asset" @selected(request('type') == 'asset')>دارایی</option>
                        <option value="liability" @selected(request('type') == 'liability')>بدهی</option>
                        <option value="equity" @selected(request('type') == 'equity')>سرمایه</option>
                        <option value="income" @selected(request('type') == 'income')>درآمد</option>
                        <option value="expense" @selected(request('type') == 'expense')>هزینه</option>
                    </select>
                </div>

                <div class="xl:col-span-2 flex gap-2">
                    <button type="submit"
                            class="flex-1 px-6 py-3.5 rounded-2xl bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 text-sm font-bold hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        فیلتر
                    </button>
                    @if(request()->hasAny(['search', 'type']))
                        <a href="{{ route('admin.accounting.reports.index') }}" title="پاک کردن فیلترها"
                           class="px-5 py-3.5 rounded-2xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-sm font-bold hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Category Balances Card --}}
        <div class="{{ $cardClass }}">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40">
                    <tr>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            عنوان سرفصل
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            نوع ماهیت
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            گردش بدهکار ({{ $currencyLabel }})
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            گردش بستانکار ({{ $currencyLabel }})
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            مانده نهایی ({{ $currencyLabel }})
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-end">
                            عملیات
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                    @forelse($categoryBalances as $balance)
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900 dark:text-white">
                                {{ $balance['title'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                    @switch($balance['type'])
                                        @case('asset') دارایی @break
                                        @case('liability') بدهی @break
                                        @case('equity') سرمایه @break
                                        @case('income') درآمد @break
                                        @case('expense') هزینه @break
                                        @default {{ $balance['type'] }}
                                    @endswitch
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-rose-600 dark:text-rose-400 tabular-nums">
                                {{ $faNum(number_format($balance['total_debit'])) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                                {{ $faNum(number_format($balance['total_credit'])) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-black tabular-nums {{ $balance['balance'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $faNum(number_format($balance['balance'])) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-end">
                                <div
                                    class="flex items-center justify-end gap-2 opacity-100 sm:opacity-40 group-hover:opacity-100 transition-opacity duration-200">
                                    <a href="{{ route('admin.accounting.reports.ledger', $balance['id']) }}"
                                       class="p-2.5 rounded-xl text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-all hover:scale-110"
                                       title="مشاهده دفتر معین / گردش حساب">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-gray-400 font-medium">
                                هیچ سرفصل حسابی مطابق با الگوی جستجو/فیلتر شما پیدا نشد.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Fund Balances Grid --}}
        <div class="{{ $cardClass }} p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                خلاصه موجودی خزانه‌داری (بانک‌ها و صندوق‌ها)
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($fundAccountBalances as $fund)
                    <div
                        class="rounded-3xl border p-5 flex flex-col justify-between bg-gray-50/50 dark:bg-gray-900/40 border-gray-100 dark:border-gray-700/50">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-2 block">
                        @switch($fund['type'])
                            @case('bank') بانک @break
                            @case('cash') صندوق نقدی @break
                            @case('gateway') درگاه آنلاین @break
                            @default {{ $fund['type'] }}
                        @endswitch
                    </span>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ $fund['name'] }}</h3>
                        <div
                            class="text-xl font-black tabular-nums {{ $fund['balance'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $faNum(number_format($fund['balance'])) }} <span
                                class="text-xs font-normal text-gray-400">{{ $currencyLabel }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
