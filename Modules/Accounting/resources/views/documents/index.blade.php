@php
    use Carbon\Carbon;use Modules\Accounting\App\Services\CurrencyService;
    use Morilog\Jalali\Jalalian;

    $currencySuffix = CurrencyService::getBaseCurrency();

    $toJalali = function ($date) {
        if (!$date) return null;
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        return Jalalian::fromCarbon($carbon);
    };

    $faNum = function($str) {
        if (is_null($str)) return '';
        $datePart = explode(' ', (string)$str)[0];
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, $datePart);
    };

    $sumTotal = CurrencyService::convertForDisplay($documents->sum(function($doc) { return $doc->transactions->sum('debit'); }));
    $sumCount = $documents->total() ?? $documents->count();
    $statCardClass = "rounded-3xl border p-5 flex items-center gap-5 overflow-hidden";
@endphp
@extends('layouts.user')
@section('title', 'اسناد مالی')

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </span>
                دفتر اسناد مالی (روزنامه)
            </h1>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.accounting.receipts.create') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-emerald-500 text-white font-bold text-sm shadow-md shadow-emerald-500/30 hover:bg-emerald-600 hover:shadow-emerald-500/40 transition-all duration-200 active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    ثبت دریافت / درآمد
                </a>

                <a href="{{ route('admin.accounting.expenses.create') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-rose-500 text-white font-bold text-sm shadow-md shadow-rose-500/30 hover:bg-rose-600 hover:shadow-rose-500/40 transition-all duration-200 active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    ثبت پرداختی / هزینه
                </a>

                <a href="{{ route('admin.accounting.documents.create') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-sky-600 text-white font-bold text-sm shadow-md shadow-sky-500/30 hover:bg-sky-700 hover:shadow-sky-500/40 transition-all duration-200 active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    ثبت سند مرکب (چند ردیفی)
                </a>
            </div>
        </div>

        @if(session('success'))
            <div
                class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-500/10 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-400 text-sm font-bold flex items-center gap-3">
            <span class="bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 p-1.5 rounded-full shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path
                        stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </span>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div
                class="rounded-2xl bg-rose-50 p-4 border border-rose-100 dark:bg-rose-500/10 dark:border-rose-500/20 text-rose-800 dark:text-rose-400 text-sm font-bold flex items-center gap-3">
            <span class="bg-rose-100 text-rose-600 dark:bg-rose-500/20 p-1.5 rounded-full shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path
                        stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </span>
                {{ session('error') }}
            </div>
        @endif

        {{-- Summary strip --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
                class="{{ $statCardClass }} bg-white dark:bg-gray-800/60 border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path
                        stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </span>
                <div class="min-w-0 flex-1">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-1 truncate">تعداد اسناد (کل)</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                    <span
                        class="text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums break-all">{{ $faNum(number_format($sumCount)) }}</span>
                    </div>
                </div>
            </div>
            <div
                class="{{ $statCardClass }} bg-white dark:bg-gray-800/60 border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path
                        stroke-linecap="round" stroke-linejoin="round"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </span>
                <div class="min-w-0 flex-1">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-1 truncate">حجم تراکنش‌ها (این صفحه)</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                    <span
                        class="text-xl xl:text-2xl font-black text-gray-900 dark:text-white tabular-nums break-all">{{ $faNum(number_format($sumTotal)) }}</span>
                        <span class="text-[11px] font-medium text-gray-400 ms-1">{{ $currencySuffix }}</span>
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
                    class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide block mb-1 truncate">سیستمی و اتوماتیک</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                    <span
                        class="text-xl xl:text-2xl font-black text-emerald-700 dark:text-emerald-400 tabular-nums break-all">{{ $faNum(number_format($documents->whereNotNull('sourceDocument')->count())) }}</span>
                        <span class="text-[11px] font-medium text-emerald-500/80 ms-1">سند</span>
                    </div>
                </div>
            </div>
            <div
                class="{{ $statCardClass }} bg-amber-50/60 dark:bg-amber-500/5 border-amber-100 dark:border-amber-500/20 shadow-sm">
            <span
                class="flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path
                        stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
                <div class="min-w-0 flex-1">
                <span
                    class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wide block mb-1 truncate">دستی و متفرقه</span>
                    <div class="flex flex-wrap items-baseline gap-1">
                    <span
                        class="text-xl xl:text-2xl font-black text-amber-700 dark:text-amber-400 tabular-nums break-all">{{ $faNum(number_format($documents->whereNull('sourceDocument')->count())) }}</span>
                        <span class="text-[11px] font-medium text-amber-500/80 ms-1">سند</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter bar --}}
        <form method="GET"
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
                           placeholder="جستجو: شماره سند، شرح..."
                           class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 ps-12 pe-4 py-3.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
                </div>
                <div class="xl:col-span-2">
                    <select name="source_type"
                            class="w-full rounded-2xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-3.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="">همه نوع سند</option>
                        <option value="auto" @selected(request('source_type') == 'auto')>همه اتوماتیک (سیستمی)</option>
                        <option value="wallet" @selected(request('source_type') == 'wallet')>کیف پول کاربران</option>
                        <option value="services" @selected(request('source_type') == 'services')>فاکتور سرویس و خدمات
                        </option>
                        <option value="booking" @selected(request('source_type') == 'booking')>نوبت‌دهی</option>
                        <option value="market" @selected(request('source_type') == 'market')>فروشگاه</option>
                        <option value="manual" @selected(request('source_type') == 'manual')>ثبت دستی</option>
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
                    @if(request()->hasAny(['search', 'source_type']))
                        <a href="{{ route('admin.accounting.documents.index') }}" title="پاک کردن فیلترها"
                           class="px-5 py-3.5 rounded-2xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 text-sm font-bold hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div
            class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            شماره سند
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            شرح سند
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            مبلغ (جمع کل)
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            حساب خزانه‌داری / بانک
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            مانده حساب (پس از سند)
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            سیستم مبدأ
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            تاریخ ثبت
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-end">
                            عملیات
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                    @forelse($documents as $document)
                        @php
                            $isDocCancelled = ($document->status === 'cancelled') ||
                                               in_array($document->sourceDocument?->event_type ?? '', ['invoice_cancelled', 'payment_cancelled', 'expense_cancelled']) ||
                                               \Illuminate\Support\Str::contains($document->description, ['لغو', 'برگشت']);
                            $fundBalances = $document->fund_account_balances;
                        @endphp
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors duration-200 @if($isDocCancelled) bg-amber-50/20 dark:bg-amber-900/10 @endif">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.accounting.documents.show', $document->id) }}"
                                   class="font-bold text-indigo-600 dark:text-indigo-400 text-base tabular-nums hover:underline block">
                                    {{ $faNum($document->document_number) }}
                                </a>
                                @if($isDocCancelled)
                                    <span
                                        class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                        برگشتی / لغو
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 max-w-sm">
                                <div class="font-bold text-gray-900 dark:text-white text-sm truncate"
                                     title="{{ $document->description }}">
                                    {{ $document->description }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                            <span
                                class="font-black text-gray-900 dark:text-gray-100 text-base tabular-nums">{{ $faNum(number_format(CurrencyService::convertForDisplay($document->transactions->sum('debit')))) }}</span>
                                <span class="text-[11px] font-medium text-gray-400 block">{{ $currencySuffix }}</span>
                            </td>
                            <td class="px-6 py-4 text-start">
                                @if(!empty($fundBalances))
                                    <div class="flex flex-col gap-1">
                                        @foreach($fundBalances as $fb)
                                            <span class="font-bold text-xs text-gray-800 dark:text-gray-200">
                                                {{ $fb['bank_name'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 font-mono text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-start whitespace-nowrap">
                                @if(!empty($fundBalances))
                                    <div class="flex flex-col gap-1">
                                        @foreach($fundBalances as $fb)
                                            @if(!is_null($fb['balance_after']))
                                                <div
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-sky-50 dark:bg-sky-500/10 text-sky-800 dark:text-sky-300 border border-sky-100 dark:border-sky-500/20 font-bold text-xs">
                                                    <span
                                                        class="tabular-nums font-black">{{ $faNum(number_format(CurrencyService::convertForDisplay($fb['balance_after']))) }}</span>
                                                    <span
                                                        class="text-[10px] text-sky-600/80 dark:text-sky-400/80">{{ $currencySuffix }}</span>
                                                </div>
                                            @else
                                                <span class="text-gray-400 font-mono text-xs">—</span>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 font-mono text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($document->sourceDocument)
                                    @php
                                        $eventType = $document->sourceDocument->event_type ?? '';
                                        $moduleName = 'نامشخص';
                                        $moduleColor = '#6b7280'; // gray

                                        if ($document->sourceDocument->module === 'services') {
                                            if ($eventType === 'invoice_cancelled') {
                                                $moduleName = 'لغو فاکتور خدمات';
                                                $moduleColor = '#ef4444'; // red
                                            } elseif ($eventType === 'payment_cancelled') {
                                                $moduleName = 'برگشت پرداخت خدمات';
                                                $moduleColor = '#f59e0b'; // amber
                                            } elseif ($eventType === 'payment_received') {
                                                $moduleName = 'دریافت وجه خدمات';
                                                $moduleColor = '#10b981'; // emerald
                                            } else {
                                                $moduleName = 'فاکتور سرویس و خدمات';
                                                $moduleColor = '#3b82f6'; // blue
                                            }
                                        } elseif ($document->sourceDocument->module === 'booking') {
                                            $moduleName = 'نوبت‌دهی';
                                            $moduleColor = '#a855f7'; // purple
                                        } elseif ($document->sourceDocument->module === 'market') {
                                            $moduleName = 'فروشگاه';
                                            $moduleColor = '#f59e0b'; // amber
                                        } elseif ($document->sourceDocument->module === 'wallet') {
                                            $moduleName = 'کیف پول کاربران';
                                            $moduleColor = '#3b82f6'; // blue
                                        } elseif ($document->sourceDocument->module === 'accounting') {
                                            if ($eventType === 'document_cancelled') {
                                                $moduleName = 'لغو سند دستی';
                                                $moduleColor = '#ef4444'; // red
                                            } else {
                                                $moduleName = 'سیستم حسابداری';
                                                $moduleColor = '#6366f1'; // indigo
                                            }
                                        }
                                    @endphp
                                    <span
                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold border"
                                        style="background: {{ $moduleColor }}1a; color: {{ $moduleColor }}; border-color: {{ $moduleColor }}33">
                                    <span class="w-2 h-2 rounded-full" style="background: {{ $moduleColor }}"></span>
                                    {{ $moduleName }}
                                </span>
                                    @php
                                        $snapshot = $document->sourceDocument->snapshot_data;
                                        $refNum = $snapshot['invoice_number'] ?? $snapshot['order_id'] ?? $snapshot['appointment_id'] ?? $snapshot['document_number'] ?? (isset($snapshot['uuid']) ? substr($snapshot['uuid'], 0, 8) : null);
                                    @endphp
                                    @if($refNum)
                                        <span
                                            class="block text-[11px] font-bold text-gray-500 mt-1">مرجع: {{ $faNum($refNum) }}</span>
                                    @endif
                                @else
                                    <span
                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold border bg-gray-50 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700">
                                    <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                    ثبت دستی
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-sm font-medium text-gray-500 dark:text-gray-400 dir-ltr whitespace-nowrap tabular-nums">
                                {{ $faNum($toJalali($document->document_date)?->format('Y/m/d') ?? '-') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div
                                    class="flex items-center justify-end gap-2 opacity-100 sm:opacity-40 group-hover:opacity-100 transition-opacity duration-200">
                                    <a href="{{ route('admin.accounting.documents.show', $document->id) }}"
                                       class="p-2.5 rounded-xl text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-all hover:scale-110"
                                       title="مشاهده">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    @if(!$document->sourceDocument && !$isDocCancelled)
                                        <a href="{{ route('admin.accounting.documents.edit', $document->id) }}"
                                           class="p-2.5 rounded-xl text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-all hover:scale-110"
                                           title="ویرایش سند دستی">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>

                                        <form action="{{ route('admin.accounting.documents.cancel', $document->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('آیا از لغو این سند دستی و اصلاح/برگشت مبالغ آن اطمینان دارید؟');"
                                              class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="p-2.5 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-all hover:scale-110"
                                                    title="لغو سند دستی و برگشت مبلغ">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-24 text-center">
                                <div class="max-w-sm mx-auto flex flex-col items-center">
                                    <div
                                        class="w-24 h-24 rounded-full bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center mb-6 shadow-inner">
                                        <svg class="w-12 h-12 text-indigo-300 dark:text-indigo-400" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">هیچ سندی یافت
                                        نشد</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 text-center leading-relaxed">
                                        هنوز هیچ تراکنش مالی در سیستم ثبت نشده است و یا جستجوی شما نتیجه‌ای نداشت.</p>
                                    <div class="flex flex-wrap justify-center items-center gap-4">
                                        <a href="{{ route('admin.accounting.receipts.create') }}"
                                           class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-emerald-500 text-white font-bold text-sm shadow-lg shadow-emerald-500/30 hover:bg-emerald-600 transition-all hover:-translate-y-1">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            </svg>
                                            ثبت دریافت
                                        </a>

                                        <a href="{{ route('admin.accounting.documents.create') }}"
                                           class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-sky-600 text-white font-bold text-sm shadow-lg shadow-sky-500/30 hover:bg-sky-700 transition-all hover:-translate-y-1">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            ثبت سند مرکب
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($documents->hasPages())
                <div
                    class="px-6 py-5 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                    {{ $documents->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
