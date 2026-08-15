@extends('layouts.user')

@section('title', 'گزارش ترازنامه مالی (Balance Sheet)')

@php
    use Modules\Accounting\App\Services\CurrencyService;use Morilog\Jalali\Jalalian;

    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl";

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $currencyLabel = CurrencyService::getBaseCurrency();

    $toJalali = function ($date) {
        if (!$date) return null;
        $carbon = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
        return Jalalian::fromCarbon($carbon);
    };

    $formattedAsOfDate = $asOfDate ? $toJalali($asOfDate)->format('Y/m/d') : $toJalali(now())->format('Y/m/d');

    $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;
    $balanceDifference = abs($totalAssets - $totalLiabilitiesAndEquity);
    $isBalanced = $balanceDifference < 1;
@endphp

@section('content')
    @includeIf('partials.jalali-date-picker')

    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm font-medium print:hidden" aria-label="Breadcrumb">
            <a href="{{ route('admin.accounting.reports.index') }}"
               class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">
                گزارش‌های حسابداری
            </a>
            <svg class="w-4 h-4 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                 stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="text-gray-900 dark:text-white font-bold">ترازنامه مالی</span>
        </nav>

        {{-- Hero Header --}}
        <div class="{{ $cardClass }}">
            <div class="p-6 sm:p-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="flex items-start gap-5">
                    <div
                        class="shrink-0 flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20">
                            گزارش جامع ترازنامه
                        </span>
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $isBalanced ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20' : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20' }}">
                            <span
                                class="w-2 h-2 rounded-full {{ $isBalanced ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                            {{ $isBalanced ? 'معادله تراز برقرار است' : 'عدم توازن در ترازنامه' }}
                        </span>
                        </div>
                        <h1 class="text-2xl sm:text-4xl font-black text-gray-900 dark:text-white tracking-tight">
                            گزارش ترازنامه (Balance Sheet)
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            وضعیت دارایی‌ها، بدهی‌ها و حقوق صاحبان سهام از تاریخ <span
                                class="font-bold text-gray-800 dark:text-gray-200 dir-ltr inline-block">{{ jdate($startDate)->format('Y/m/d') }}</span>
                            تا تاریخ <span
                                class="font-bold text-gray-800 dark:text-gray-200 dir-ltr inline-block">{{ jdate($endDate)->format('Y/m/d') }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0 print:hidden">
                    <a href="{{ route('admin.accounting.reports.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-bold transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        بازگشت به گزارش‌ها
                    </a>
                </div>
            </div>
        </div>

        {{-- Filter Card --}}
        <div class="{{ $cardClass }} p-5 print:hidden">
            <form action="{{ route('admin.accounting.reports.balance_sheet') }}" method="GET"
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
                    <a href="{{ route('admin.accounting.reports.balance_sheet') }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-2xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 font-bold text-sm hover:bg-gray-200 transition-all">
                        ماه جاری
                    </a>
                    @if(request()->hasAny(['start_date', 'end_date', 'date']))
                        <a href="{{ route('admin.accounting.reports.balance_sheet') }}" title="غیرفعال کردن فیلتر"
                           class="px-4 py-3 rounded-2xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-sm font-bold hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Equation & Summary Cards Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- 1. دارایی‌ها --}}
            <div
                class="{{ $cardClass }} p-6 bg-gradient-to-br from-emerald-50/60 to-transparent dark:from-emerald-900/10">
                <div class="flex items-center gap-3 mb-3">
                    <div
                        class="p-2.5 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-xl">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-extrabold text-gray-500 dark:text-gray-400 uppercase tracking-wider">۱. مجموع دارایی‌ها</span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 tabular-nums">
                    {{ $faNum(number_format($totalAssets)) }}
                    <span class="text-xs font-bold text-emerald-400/80 mr-1">{{ $currencyLabel }}</span>
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 font-medium">شامل تمام دارایی‌های جاری و
                    ثابت</p>
            </div>

            {{-- 2. بدهی‌ها --}}
            <div class="{{ $cardClass }} p-6 bg-gradient-to-br from-amber-50/60 to-transparent dark:from-amber-900/10">
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-2.5 bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 rounded-xl">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h16.5a1.5 1.5 0 011.5 1.5v9a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5v-9a1.5 1.5 0 011.5-1.5z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-extrabold text-gray-500 dark:text-gray-400 uppercase tracking-wider">۲. مجموع بدهی‌ها</span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-amber-600 dark:text-amber-400 tabular-nums">
                    {{ $faNum(number_format($totalLiabilities)) }}
                    <span class="text-xs font-bold text-amber-400/80 mr-1">{{ $currencyLabel }}</span>
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 font-medium">تعهدات و بدهی‌های جاری و
                    غیرجاری</p>
            </div>

            {{-- 3. حقوق صاحبان سهام --}}
            <div
                class="{{ $cardClass }} p-6 bg-gradient-to-br from-violet-50/60 to-transparent dark:from-violet-900/10">
                <div class="flex items-center gap-3 mb-3">
                    <div
                        class="p-2.5 bg-violet-100 dark:bg-violet-500/20 text-violet-600 dark:text-violet-400 rounded-xl">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.5M4.5 21V10.5"/>
                        </svg>
                    </div>
                    <span class="text-xs font-extrabold text-gray-500 dark:text-gray-400 uppercase tracking-wider">۳. سرمایه و سود انباشته</span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-violet-600 dark:text-violet-400 tabular-nums">
                    {{ $faNum(number_format($totalEquity)) }}
                    <span class="text-xs font-bold text-violet-400/80 mr-1">{{ $currencyLabel }}</span>
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 font-medium">سرمایه پایه + سود (زیان) انباشته
                    دوره</p>
            </div>

            {{-- 4. جمع بدهی‌ها و سرمایه --}}
            <div
                class="{{ $cardClass }} p-6 bg-gradient-to-br from-indigo-50/60 to-transparent dark:from-indigo-900/10">
                <div class="flex items-center gap-3 mb-3">
                    <div
                        class="p-2.5 bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 rounded-xl">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m9-6-9 6-9-6"/>
                        </svg>
                    </div>
                    <span class="text-xs font-extrabold text-gray-500 dark:text-gray-400 uppercase tracking-wider">جمع بدهی و سرمایه</span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-indigo-600 dark:text-indigo-400 tabular-nums">
                    {{ $faNum(number_format($totalLiabilitiesAndEquity)) }}
                    <span class="text-xs font-bold text-indigo-400/80 mr-1">{{ $currencyLabel }}</span>
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 font-medium">معادله تراز ترازنامه (بدهی +
                    سرمایه)</p>
            </div>
        </div>

        {{-- Main Balance Sheet Content (2 Columns) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            {{-- COLUMN 1: دارایی‌ها (ASSETS) --}}
            <div class="{{ $cardClass }} self-start">
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-l from-emerald-50 to-transparent dark:from-emerald-500/10 flex items-center justify-between">
                    <h2 class="text-lg font-black text-emerald-700 dark:text-emerald-400 flex items-center gap-3">
                        <div
                            class="p-2 bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 rounded-lg shadow-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        ۱. دارایی‌ها (Assets)
                    </h2>
                    <span
                        class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-3 py-1.5 rounded-lg border border-emerald-100 dark:border-emerald-500/20">
                    {{ $faNum($assets->count()) }} سرفصل
                </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-start border-collapse">
                        <thead
                            class="bg-gray-50/80 dark:bg-gray-900/30 text-gray-500 dark:text-gray-400 font-bold border-b border-gray-100 dark:border-gray-700/50 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3.5 font-bold text-start">عنوان سرفصل دارایی</th>
                            <th class="px-6 py-3.5 font-bold text-center">سهم از دارایی</th>
                            <th class="px-6 py-3.5 font-bold text-end">مانده تراز ({{ $currencyLabel }})</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @forelse($assets as $asset)
                            @php
                                $percent = $totalAssets > 0 ? max(0, min(100, ($asset->balance / $totalAssets) * 100)) : 0;
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-6 py-4 font-bold text-gray-800 dark:text-gray-200">
                                    {{ $asset->title }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-16 bg-gray-100 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                            <div class="bg-emerald-500 h-2 rounded-full"
                                                 style="width: {{ $percent }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400 tabular-nums">{{ $faNum(number_format($percent, 1)) }}٪</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-end tabular-nums font-black text-emerald-600 dark:text-emerald-400 text-base">
                                    {{ $faNum(number_format($asset->balance)) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-gray-400 font-medium">
                                    هیچ سرفصل دارایی با مانده غیرصفر ثبت نشده است.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div
                    class="p-6 bg-emerald-50/50 dark:bg-emerald-900/10 border-t-2 border-dashed border-emerald-200 dark:border-emerald-800/40 flex items-center justify-between">
                    <span class="font-black text-gray-900 dark:text-white text-base">جمع کل دارایی‌ها:</span>
                    <span class="font-black text-emerald-600 dark:text-emerald-400 text-2xl tabular-nums">
                    {{ $faNum(number_format($totalAssets)) }}
                    <span class="text-sm font-bold text-emerald-400/80 mr-1">{{ $currencyLabel }}</span>
                </span>
                </div>
            </div>

            {{-- COLUMN 2: بدهی‌ها و حقوق صاحبان سهام (LIABILITIES & EQUITY) --}}
            <div class="space-y-8">

                {{-- ۲. بدهی‌ها --}}
                <div class="{{ $cardClass }}">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-l from-amber-50 to-transparent dark:from-amber-900/10 flex items-center justify-between">
                        <h2 class="text-lg font-black text-amber-700 dark:text-amber-400 flex items-center gap-3">
                            <div
                                class="p-2 bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 rounded-lg shadow-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h16.5a1.5 1.5 0 011.5 1.5v9a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5v-9a1.5 1.5 0 01-1.5-1.5z"/>
                                </svg>
                            </div>
                            ۲. بدهی‌ها (Liabilities)
                        </h2>
                        <span
                            class="text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-3 py-1.5 rounded-lg border border-amber-100 dark:border-amber-500/20">
                        {{ $faNum($liabilities->count()) }} سرفصل
                    </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-start border-collapse">
                            <thead
                                class="bg-gray-50/80 dark:bg-gray-900/30 text-gray-500 dark:text-gray-400 font-bold border-b border-gray-100 dark:border-gray-700/50 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3.5 font-bold text-start">عنوان سرفصل بدهی</th>
                                <th class="px-6 py-3.5 font-bold text-end">مانده تراز ({{ $currencyLabel }})</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @forelse($liabilities as $liability)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="px-6 py-4 font-bold text-gray-800 dark:text-gray-200">
                                        {{ $liability->title }}
                                    </td>
                                    <td class="px-6 py-4 text-end tabular-nums font-black text-amber-600 dark:text-amber-400 text-base">
                                        {{ $faNum(number_format($liability->balance)) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-6 py-8 text-center text-gray-400 font-medium">
                                        هیچ بدهی برای سیستم ثبت نشده است.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="p-5 bg-amber-50/50 dark:bg-amber-900/10 border-t border-amber-100 dark:border-amber-800/40 flex items-center justify-between">
                        <span class="font-bold text-gray-800 dark:text-gray-200 text-sm">جمع کل بدهی‌ها:</span>
                        <span class="font-black text-amber-600 dark:text-amber-400 text-xl tabular-nums">
                        {{ $faNum(number_format($totalLiabilities)) }}
                        <span class="text-xs font-bold text-amber-400/80 mr-1">{{ $currencyLabel }}</span>
                    </span>
                    </div>
                </div>

                {{-- ۳. حقوق صاحبان سهام --}}
                <div class="{{ $cardClass }}">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-l from-violet-50 to-transparent dark:from-violet-900/10 flex items-center justify-between">
                        <h2 class="text-lg font-black text-violet-700 dark:text-violet-400 flex items-center gap-3">
                            <div
                                class="p-2 bg-violet-100 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400 rounded-lg shadow-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.5M4.5 21V10.5"/>
                                </svg>
                            </div>
                            ۳. حقوق صاحبان سهام (Equity)
                        </h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-start border-collapse">
                            <thead
                                class="bg-gray-50/80 dark:bg-gray-900/30 text-gray-500 dark:text-gray-400 font-bold border-b border-gray-100 dark:border-gray-700/50 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3.5 font-bold text-start">عنوان سرفصل سرمایه / سود</th>
                                <th class="px-6 py-3.5 font-bold text-end">مانده تراز ({{ $currencyLabel }})</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @foreach($equities as $equity)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="px-6 py-4 font-bold text-gray-800 dark:text-gray-200">
                                        {{ $equity->title }}
                                    </td>
                                    <td class="px-6 py-4 text-end tabular-nums font-black text-violet-600 dark:text-violet-400 text-base">
                                        {{ $faNum(number_format($equity->balance)) }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="bg-violet-50/30 dark:bg-violet-900/10 font-bold">
                                <td class="px-6 py-4 text-gray-900 dark:text-white flex items-center gap-2">
                                    <span>سود (زیان) انباشته / این دوره</span>
                                    <span
                                        class="text-xs px-2 py-0.5 rounded-full {{ $retainedEarnings >= 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-400' }}">
                                        {{ $retainedEarnings >= 0 ? 'سود' : 'زیان' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-end tabular-nums font-black {{ $retainedEarnings >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} text-base">
                                    {{ $faNum(number_format($retainedEarnings)) }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="p-5 bg-violet-50/50 dark:bg-violet-900/10 border-t border-violet-100 dark:border-violet-800/40 flex items-center justify-between">
                        <span class="font-bold text-gray-800 dark:text-gray-200 text-sm">جمع کل حقوق صاحبان سهام:</span>
                        <span class="font-black text-violet-600 dark:text-violet-400 text-xl tabular-nums">
                        {{ $faNum(number_format($totalEquity)) }}
                        <span class="text-xs font-bold text-violet-400/80 mr-1">{{ $currencyLabel }}</span>
                    </span>
                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection
