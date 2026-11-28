@extends('layouts.user')

@section('title', 'رسید خرید و ورود کالا به انبار')

@section('content')
@includeIf('partials.jalali-date-picker')

<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 font-sans">

    {{-- Top Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">
                <a href="{{ route('admin.warehouse.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">داشبورد انبار</a>
                <span>/</span>
                <span class="text-gray-900 dark:text-white">رسید خرید کالا</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                رسیدهای خرید و ورود کالا به انبار
            </h1>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.warehouse.purchases.pdf', request()->query()) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-all shadow-xs">
                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                خروجی PDF رسمی
            </a>

            <a href="{{ route('admin.warehouse.purchases.import.form') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-emerald-200 dark:border-emerald-700/60 bg-emerald-50/80 dark:bg-emerald-500/15 hover:bg-emerald-100 dark:hover:bg-emerald-500/25 text-emerald-700 dark:text-emerald-300 font-bold text-xs transition-all shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                درون‌ریزی اکسل خرید
            </a>

            <a href="{{ route('admin.warehouse.purchases.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                ثبت رسید خرید جدید
            </a>
        </div>
    </div>

    @include('warehouse::partials.flash_messages')

    {{-- Summary Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-xs font-bold text-slate-600 dark:text-slate-300">تعداد رسیدهای ثبت‌شده</p>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mt-1 font-sans">
                    {{ number_format($metrics['receipts_count']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">فاکتور</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-xs font-bold text-slate-600 dark:text-slate-300">کل تعداد اقلام وارده</p>
                <h3 class="text-lg font-bold text-indigo-600 dark:text-indigo-300 mt-1 font-sans">
                    {{ number_format($metrics['total_quantity']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">واحد</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-500/15 text-teal-600 dark:text-teal-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-xs font-bold text-slate-600 dark:text-slate-300">مجموع ریالی خریدها</p>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mt-1 font-sans">
                    {{ number_format($metrics['total_amount']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">ریال</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-xs font-bold text-slate-600 dark:text-slate-300">خریدهای ماه جاری</p>
                <h3 class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-1 font-sans">
                    {{ number_format($metrics['month_amount']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">ریال</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </span>
        </div>
    </div>

    {{-- Filters Bar --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-4 shadow-xs">
        <form action="{{ route('admin.warehouse.purchases.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div class="lg:col-span-2">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="جستجو با شماره فاکتور، کد رسید، تأمین‌کننده یا کالا..."
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2 text-xs placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
            </div>

            <div>
                <select name="warehouse_id"
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3 py-2 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
                    <option value="">همه انبارهای مقصد</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" @selected((string)$warehouseId === (string)$w->id)>
                            {{ $w->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <input type="text"
                       name="date_from"
                       value="{{ $dateFrom }}"
                       data-jdp-only-date
                       autocomplete="off"
                       placeholder="از تاریخ (شمسی)"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3 py-2 text-xs dir-ltr text-center placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
            </div>

            <div>
                <input type="text"
                       name="date_to"
                       value="{{ $dateTo }}"
                       data-jdp-only-date
                       autocomplete="off"
                       placeholder="تا تاریخ (شمسی)"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3 py-2 text-xs dir-ltr text-center placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all">
                    اعمال فیلتر
                </button>
                @if($search || $warehouseId || $status || $dateFrom || $dateTo)
                    <a href="{{ route('admin.warehouse.purchases.index') }}"
                       class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all">
                        پاک‌سازی
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Receipts Table (No horizontal scrollbar, in-flow expandable line breakdown) --}}
    <div x-data="{ expandedId: null }"
         class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 shadow-xs overflow-hidden">
        @if($receipts->isEmpty())
            <div class="p-12 text-center space-y-3">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                    هنوز هیچ رسید خریدی ثبت نشده است
                </h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 max-w-md mx-auto">
                    با ثبت فاکتور خرید جدید یا درون‌ریزی فایل اکسل، موجودی انبار و پارت‌های قیمت‌گذاری به‌صورت خودکار ایجاد می‌شوند.
                </p>
                <div class="pt-1 flex items-center justify-center gap-2">
                    <a href="{{ route('admin.warehouse.purchases.create') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all">
                        ثبت اولین رسید خرید
                    </a>
                </div>
            </div>
        @else
            <table class="w-full text-right text-xs table-fixed">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/60 text-slate-600 dark:text-slate-300 font-bold border-b border-gray-200 dark:border-gray-700">
                        <th class="py-3.5 px-4 w-[18%]">شماره رسید و فاکتور</th>
                        <th class="py-3.5 px-4 w-[15%]">تاریخ خرید / ثبت</th>
                        <th class="py-3.5 px-4 w-[18%]">تأمین‌کننده و انبار</th>
                        <th class="py-3.5 px-4 w-[16%] text-center">اقلام و تعداد</th>
                        <th class="py-3.5 px-4 w-[18%]">مبلغ کل فاکتور</th>
                        <th class="py-3.5 px-4 w-[15%] text-center">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/70">
                    @foreach($receipts as $receipt)
                        @php
                            $jalaliDate = $receipt->purchase_date
                                ? (function_exists('jdate') ? jdate($receipt->purchase_date)->format('Y/m/d') : $receipt->purchase_date->format('Y-m-d'))
                                : '—';
                            $jalaliCreatedAt = $receipt->created_at
                                ? (function_exists('jdate') ? jdate($receipt->created_at)->format('Y/m/d H:i') : $receipt->created_at->format('Y-m-d H:i'))
                                : '—';
                            $lineWarehouses = $receipt->items->pluck('warehouse.name')->filter()->unique()->values();
                            $isCancelled = $receipt->status === 'cancelled';
                        @endphp
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors {{ $isCancelled ? 'opacity-65' : '' }}">
                            <td class="py-3.5 px-4 align-middle">
                                <a href="{{ route('admin.warehouse.purchases.show', $receipt) }}"
                                   class="font-bold text-indigo-600 dark:text-indigo-300 hover:underline dir-ltr inline-block font-sans">
                                    {{ $receipt->receipt_number }}
                                </a>
                                @if($receipt->invoice_number)
                                    <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5 dir-ltr text-right font-sans">
                                        فاکتور: {{ $receipt->invoice_number }}
                                    </div>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 align-middle font-sans">
                                <div class="font-bold text-gray-900 dark:text-white">
                                    <span class="text-[11px] font-normal text-slate-600 dark:text-slate-300">خرید:</span>
                                    <span class="dir-ltr inline-block">{{ $jalaliDate }}</span>
                                </div>
                                <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5">
                                    <span>ثبت:</span>
                                    <span class="dir-ltr inline-block">{{ $jalaliCreatedAt }}</span>
                                </div>
                                @if($isCancelled)
                                    <div class="mt-1">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300 border border-rose-200/70 dark:border-rose-500/30">
                                            ابطال‌شده
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 align-middle">
                                <div class="font-bold text-gray-900 dark:text-white truncate">
                                    {{ $receipt->supplier_name ?: 'تأمین‌کننده ثبت‌نشده' }}
                                </div>
                                <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5 truncate">
                                    @if($lineWarehouses->count() === 1)
                                        انبار: {{ $lineWarehouses->first() }}
                                    @elseif($lineWarehouses->count() > 1)
                                        {{ $lineWarehouses->count() }} انبار مقصد
                                    @elseif($receipt->warehouse)
                                        انبار: {{ $receipt->warehouse->name }}
                                    @else
                                        —
                                    @endif
                                    <span class="mx-1">·</span>
                                    <span>ثبت‌کننده: <strong class="text-gray-800 dark:text-gray-200">{{ $receipt->creator_name }}</strong></span>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 align-middle text-center">
                                <button type="button"
                                        @click="expandedId = (expandedId === {{ $receipt->id }} ? null : {{ $receipt->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold transition-colors border font-sans"
                                        :class="expandedId === {{ $receipt->id }}
                                            ? 'bg-indigo-600 text-white border-indigo-600'
                                            : 'bg-indigo-50 text-indigo-700 border-indigo-200/70 hover:bg-indigo-100 dark:bg-indigo-500/15 dark:text-indigo-300 dark:border-indigo-500/30 dark:hover:bg-indigo-500/25'">
                                    <span>{{ number_format($receipt->total_quantity) }} واحد ({{ $receipt->total_items_count }} قلم)</span>
                                    <svg class="w-3.5 h-3.5 transition-transform"
                                         :class="expandedId === {{ $receipt->id }} ? 'rotate-180' : ''"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </td>

                            <td class="py-3.5 px-4 align-middle font-sans">
                                <div class="text-sm font-bold text-gray-900 dark:text-white">
                                    {{ number_format($receipt->total_amount) }}
                                    <span class="text-[11px] font-normal text-slate-600 dark:text-slate-300">ریال</span>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 align-middle">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.warehouse.purchases.show', $receipt) }}"
                                       title="مشاهده و چاپ رسید"
                                       class="p-2 rounded-xl text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 dark:text-slate-300 dark:hover:text-indigo-300 dark:hover:bg-indigo-500/15 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    @if($receipt->can_modify)
                                        <a href="{{ route('admin.warehouse.purchases.edit', $receipt) }}"
                                           title="ویرایش رسید"
                                           class="p-2 rounded-xl text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 dark:text-slate-300 dark:hover:text-indigo-300 dark:hover:bg-indigo-500/15 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        <form action="{{ route('admin.warehouse.purchases.destroy', $receipt) }}"
                                              method="POST"
                                              onsubmit="return confirm('آیا از ابطال رسید {{ $receipt->receipt_number }} و برگشت موجودی آن از انبار اطمینان دارید؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    title="ابطال و برگشت از انبار"
                                                    class="p-2 rounded-xl text-slate-600 hover:text-rose-600 hover:bg-rose-50 dark:text-slate-300 dark:hover:text-rose-300 dark:hover:bg-rose-500/15 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- In-flow Expandable Receipt Lines Sub-Table --}}
                        <tr x-show="expandedId === {{ $receipt->id }}" x-cloak>
                            <td colspan="6" class="p-0 bg-slate-50/90 dark:bg-slate-900/60 border-b border-indigo-100 dark:border-indigo-500/20">
                                <div class="p-4 sm:p-5 space-y-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex items-center gap-2 text-xs font-bold text-gray-900 dark:text-white">
                                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                            <span>ریز اقلام رسید خرید {{ $receipt->receipt_number }}</span>
                                            @if($receipt->notes)
                                                <span class="text-slate-600 dark:text-slate-300 font-normal">— {{ $receipt->notes }}</span>
                                            @endif
                                        </div>

                                        <a href="{{ route('admin.warehouse.purchases.show', $receipt) }}"
                                           class="text-xs font-bold text-indigo-600 dark:text-indigo-300 hover:underline">
                                            مشاهده صفحه کامل فاکتور و چاپ
                                        </a>
                                    </div>

                                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                                        <table class="w-full text-right text-xs">
                                            <thead>
                                                <tr class="bg-gray-50 dark:bg-gray-900/70 text-slate-600 dark:text-slate-300 font-bold border-b border-gray-200 dark:border-gray-700">
                                                    <th class="py-2.5 px-3">کد کالا</th>
                                                    <th class="py-2.5 px-3">نام کالا</th>
                                                    <th class="py-2.5 px-3">انبار مقصد</th>
                                                    <th class="py-2.5 px-3 text-center">تعداد وارده</th>
                                                    <th class="py-2.5 px-3 text-center">باقیمانده پارت</th>
                                                    <th class="py-2.5 px-3">فی خرید (ریال)</th>
                                                    <th class="py-2.5 px-3">جمع کل (ریال)</th>
                                                    <th class="py-2.5 px-3">توضیحات</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-sans">
                                                @foreach($receipt->items as $line)
                                                    @php
                                                        $remQty = $line->batch ? (float)$line->batch->remaining_quantity : 0;
                                                    @endphp
                                                    <tr>
                                                        <td class="py-2.5 px-3 dir-ltr text-right font-bold text-indigo-600 dark:text-indigo-300">
                                                            @if($line->item)
                                                                <a href="{{ route('admin.warehouse.items.show', $line->item) }}" class="hover:underline">
                                                                    {{ $line->item->code }}
                                                                </a>
                                                            @else
                                                                —
                                                            @endif
                                                        </td>
                                                        <td class="py-2.5 px-3 font-bold text-gray-900 dark:text-white">
                                                            {{ $line->item?->name ?? 'کالای حذف‌شده' }}
                                                        </td>
                                                        <td class="py-2.5 px-3 text-slate-700 dark:text-slate-200">
                                                            {{ $line->warehouse?->name ?? '—' }}
                                                        </td>
                                                        <td class="py-2.5 px-3 text-center font-bold text-gray-900 dark:text-white">
                                                            {{ number_format($line->quantity) }} {{ $line->item?->unit }}
                                                        </td>
                                                        <td class="py-2.5 px-3 text-center font-bold {{ $remQty > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-600 dark:text-slate-300' }}">
                                                            {{ number_format($remQty) }} {{ $line->item?->unit }}
                                                        </td>
                                                        <td class="py-2.5 px-3 font-bold text-gray-900 dark:text-white">
                                                            {{ number_format($line->unit_price) }}
                                                        </td>
                                                        <td class="py-2.5 px-3 font-bold text-indigo-600 dark:text-indigo-300">
                                                            {{ number_format($line->total_price) }}
                                                        </td>
                                                        <td class="py-2.5 px-3 text-slate-600 dark:text-slate-300">
                                                            {{ $line->notes ?: '—' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-4 border-t border-gray-200 dark:border-gray-700/80">
                {{ $receipts->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
