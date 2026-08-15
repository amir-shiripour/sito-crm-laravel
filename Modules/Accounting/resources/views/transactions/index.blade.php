@extends('layouts.user')

@section('title', 'گردش حساب‌ها و تراکنش‌ها')

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

    $sumCredit = CurrencyService::convertForDisplay($transactions->sum('credit'));
    $sumDebit  = CurrencyService::convertForDisplay($transactions->sum('debit'));
    $sumCount  = $transactions->total() ?? $transactions->count();
    $statCardClass = "rounded-3xl border p-5 flex items-center gap-5 overflow-hidden";
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-lg shadow-emerald-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                    گردش تراکنش‌های مالی
                </h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">ریز تراکنش‌های بدهکار و بستانکار ثبت شده در
                    خزانه‌داری سیستم.</p>
            </div>
        </div>

        {{-- Summary Strip --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                class="{{ $statCardClass }} bg-white dark:bg-gray-800/60 border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path
                        stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </span>
                <div class="min-w-0 flex-1">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-1 truncate">تعداد تراکنش‌ها (کل)</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span
                            class="text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums break-all">{{ $faNum(number_format($sumCount)) }}</span>
                    </div>
                </div>
            </div>

            <div
                class="{{ $statCardClass }} bg-emerald-50/60 dark:bg-emerald-500/5 border-emerald-100 dark:border-emerald-500/20 shadow-sm">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path
                        stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </span>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide block mb-1 truncate">ورودی / بستانکار (این صفحه)</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span
                            class="text-xl xl:text-2xl font-black text-emerald-700 dark:text-emerald-400 tabular-nums break-all">{{ $faNum(number_format($sumCredit)) }}</span>
                        <span class="text-[11px] font-medium text-emerald-500/80 ms-1">{{ $currencyLabel }}</span>
                    </div>
                </div>
            </div>

            <div
                class="{{ $statCardClass }} bg-rose-50/60 dark:bg-rose-500/5 border-rose-100 dark:border-rose-500/20 shadow-sm">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path
                        stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </span>
                <div class="min-w-0 flex-1">
                    <span
                        class="text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wide block mb-1 truncate">خروجی / بدهکار (این صفحه)</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <span
                            class="text-xl xl:text-2xl font-black text-rose-700 dark:text-rose-400 tabular-nums break-all">{{ $faNum(number_format($sumDebit)) }}</span>
                        <span class="text-[11px] font-medium text-rose-500/80 ms-1">{{ $currencyLabel }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter bar --}}
        <form method="GET" action="{{ route('admin.accounting.transactions.index') }}"
              class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                {{-- Search Input --}}
                <div class="relative md:col-span-6 lg:col-span-4">
                    <div class="absolute inset-y-0 start-0 ps-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="جستجو: شرح، بابت، نام حساب..."
                           class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 ps-11 pe-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
                </div>

                {{-- Transaction Type --}}
                <div class="md:col-span-3 lg:col-span-2">
                    <select name="type"
                            class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="">همه نوع تراکنش</option>
                        <option value="credit" @selected(request('type') == 'credit')>ورودی / بستانکار</option>
                        <option value="debit" @selected(request('type') == 'debit')>خروجی / بدهکار</option>
                    </select>
                </div>

                {{-- Category / Sarfasal --}}
                <div class="md:col-span-3 lg:col-span-2">
                    <select name="category_id"
                            class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="">همه سرفصل‌ها</option>
                        @foreach($categories as $cat)
                            <option
                                value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->title }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Fund Account --}}
                <div class="md:col-span-6 lg:col-span-2">
                    <select name="fund_account_id"
                            class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="">همه حساب‌های خزانه‌داری</option>
                        @foreach($fundAccounts as $fa)
                            <option
                                value="{{ $fa->id }}" @selected(request('fund_account_id') == $fa->id)>{{ $fa->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter & Reset Buttons --}}
                <div class="md:col-span-6 lg:col-span-2 flex gap-2">
                    <button type="submit"
                            class="flex-1 px-5 py-3 rounded-2xl bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 text-sm font-bold hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        فیلتر
                    </button>
                    @if(request()->hasAny(['search', 'type', 'category_id', 'fund_account_id']))
                        <a href="{{ route('admin.accounting.transactions.index') }}" title="پاک کردن فیلترها"
                           class="px-4 py-3 rounded-2xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-sm font-bold hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Transactions Table Card --}}
        <div class="{{ $cardClass }}">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40">
                    <tr>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            نوع تراکنش
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            مبلغ تراکنش ({{ $currencyLabel }})
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            حساب خزانه‌داری / بانک
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            مانده حساب (پس از تراکنش)
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            سرفصل حساب
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            شرح / بابت
                        </th>
                        <th scope="col"
                            class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            تاریخ ثبت
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                    @forelse($transactions as $record)
                        @php
                            $isCancelled = ($record->document?->status === 'cancelled') || 
                                           in_array($record->document?->sourceDocument?->event_type ?? '', ['invoice_cancelled', 'payment_cancelled', 'expense_cancelled']) ||
                                           \Illuminate\Support\Str::contains($record->description, ['لغو', 'برگشت', 'اصلاح']);
                            $balAfter = $record->account_balance_after;
                        @endphp
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors duration-200 @if($isCancelled) bg-amber-50/20 dark:bg-amber-900/10 @endif">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @if ($record->debit > 0)
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            ورودی / واریز
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-100 dark:border-rose-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            خروجی / برداشت
                                        </span>
                                    @endif

                                    @if($isCancelled)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200/60 dark:border-amber-500/20">
                                            <svg class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                            برگشتی / لغو
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-black text-gray-900 dark:text-white tabular-nums text-base">
                                {{ $faNum(number_format(CurrencyService::convertForDisplay($record->credit > 0 ? $record->credit : $record->debit))) }}
                                <span class="text-xs font-normal text-gray-400 ms-0.5">{{ $currencyLabel }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-800 dark:text-gray-200">
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-black text-gray-900 dark:text-white">
                                        {{ $record->fundAccount?->bank_display_name ?? $record->bank?->bank_name ?? '—' }}
                                    </span>
                                    @if($record->fundAccount?->card_number)
                                        <span class="text-[11px] font-mono text-gray-500 dark:text-gray-400 tracking-wider">
                                            کارت: {{ $faNum(substr($record->fundAccount->card_number, -4)) }}****
                                        </span>
                                    @elseif($record->fundAccount?->account_number)
                                        <span class="text-[11px] font-mono text-gray-500 dark:text-gray-400">
                                            حساب: {{ $faNum($record->fundAccount->account_number) }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(!is_null($balAfter))
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-sky-50 dark:bg-sky-500/10 text-sky-800 dark:text-sky-300 border border-sky-100 dark:border-sky-500/20 font-bold text-sm">
                                        <svg class="w-4 h-4 text-sky-600 dark:text-sky-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H4a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                        <span class="tabular-nums font-black">{{ $faNum(number_format(CurrencyService::convertForDisplay($balAfter))) }}</span>
                                        <span class="text-xs font-normal text-sky-600/80 dark:text-sky-400/80 ms-0.5">{{ $currencyLabel }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400 font-mono">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $record->category->title ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300 font-medium max-w-md">
                                <span class="@if($record->document?->status === 'cancelled') text-gray-500 @endif">{{ $record->description ?: '—' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-gray-500 dark:text-gray-400 font-medium tabular-nums">
                                @php $txDate = $record->transaction_date ?? $record->document?->document_date ?? $record->created_at; @endphp
                                {{ $txDate ? $faNum($toJalali($txDate)->format('Y/m/d')) : '---' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-24 text-center">
                                <div
                                    class="w-24 h-24 rounded-full bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center mb-6 shadow-inner mx-auto">
                                    <svg class="w-10 h-10 text-emerald-500 dark:text-emerald-400" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">هیچ تراکنشی یافت
                                    نشد</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">تراکنش‌های خزانه‌داری به صورت
                                    اتوماتیک با ثبت فاکتور یا وصول چک ایجاد می‌شوند.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($transactions->hasPages())
                <div
                    class="px-6 py-5 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
