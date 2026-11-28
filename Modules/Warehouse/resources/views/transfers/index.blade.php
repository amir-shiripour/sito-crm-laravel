@extends('layouts.user')

@section('title', 'درخواست و انتقال کالا بین انبارها')

@section('content')
@includeIf('partials.jalali-date-picker')

<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 font-sans">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">
                <a href="{{ route('admin.warehouse.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">داشبورد انبار</a>
                <span>/</span>
                <span class="text-gray-900 dark:text-white">درخواست و انتقال کالا</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                درخواست‌ها و حواله‌های انتقال بین انبارها
            </h1>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.warehouse.transfers.pdf', request()->query()) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-all shadow-xs">
                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                خروجی PDF رسمی
            </a>

            <a href="{{ route('admin.warehouse.transfers.import.form') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-emerald-200 dark:border-emerald-700/60 bg-emerald-50/80 dark:bg-emerald-500/15 hover:bg-emerald-100 dark:hover:bg-emerald-500/25 text-emerald-700 dark:text-emerald-300 font-bold text-xs transition-all shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                درون‌ریزی اکسل درخواست‌ها
            </a>

            <a href="{{ route('admin.warehouse.transfers.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                ثبت درخواست / انتقال جدید
            </a>
        </div>
    </div>

    @include('warehouse::partials.flash_messages')

    {{-- Summary Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-xs font-bold text-slate-600 dark:text-slate-300">کل حواله‌ها و درخواست‌ها</p>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mt-1 font-sans">
                    {{ number_format($metrics['total_count']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">سند</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
            </span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-xs font-bold text-slate-600 dark:text-slate-300">در انتظار تایید و تحویل</p>
                <h3 class="text-lg font-bold {{ $metrics['pending_count'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white' }} mt-1 font-sans">
                    {{ number_format($metrics['pending_count']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">درخواست</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-xs font-bold text-slate-600 dark:text-slate-300">کل تعداد منتقل‌شده</p>
                <h3 class="text-lg font-bold text-indigo-600 dark:text-indigo-300 mt-1 font-sans">
                    {{ number_format($metrics['delivered_units']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">واحد</span>
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
                <p class="text-xs font-bold text-slate-600 dark:text-slate-300">ارزش بهای تمام‌شده انتقال‌ها</p>
                <h3 class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-1 font-sans">
                    {{ number_format($metrics['delivered_cost_value']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">ریال</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                </svg>
            </span>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-4 shadow-xs">
        <form action="{{ route('admin.warehouse.transfers.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3">
            <div class="lg:col-span-2">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="جستجو با شماره حواله/درخواست، درخواست‌کننده یا کالا..."
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2 text-xs placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
            </div>

            <div>
                <select name="destination_warehouse_id"
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3 py-2 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
                    <option value="">همه انبارک‌های مقصد</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" @selected((string)$destinationId === (string)$w->id)>
                            {{ $w->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="status"
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3 py-2 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="pending" @selected($status === 'pending')>در انتظار تحویل</option>
                    <option value="delivered" @selected($status === 'delivered')>تحویل و منتقل‌شده</option>
                    <option value="rejected" @selected($status === 'rejected')>ردشده</option>
                    <option value="cancelled" @selected($status === 'cancelled')>ابطال‌شده</option>
                </select>
            </div>

            <div>
                <input type="text"
                       name="date_from"
                       value="{{ $dateFrom }}"
                       data-jdp-only-date
                       autocomplete="off"
                       placeholder="از تاریخ"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3 py-2 text-xs dir-ltr text-center placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
            </div>

            <div>
                <input type="text"
                       name="date_to"
                       value="{{ $dateTo }}"
                       data-jdp-only-date
                       autocomplete="off"
                       placeholder="تا تاریخ"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3 py-2 text-xs dir-ltr text-center placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all">
                    فیلتر
                </button>
                @if($search || $sourceId || $destinationId || $status || $dateFrom || $dateTo)
                    <a href="{{ route('admin.warehouse.transfers.index') }}"
                       class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all">
                        پاک‌سازی
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table (6 columns, no horizontal scroll, expandable sub-row) --}}
    <div x-data="{ expandedId: null }"
         class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 shadow-xs overflow-hidden">
        @if($transfers->isEmpty())
            <div class="p-12 text-center space-y-3">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                    هنوز هیچ درخواست یا حواله انتقالی ثبت نشده است
                </h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 max-w-md mx-auto">
                    با ثبت درخواست یا انتقال کالا، موجودی از انبار مرکزی به انبارک‌های بخش به صورت خودکار و با حفظ بهای پارت‌ها منتقل می‌شود.
                </p>
                <div class="pt-1">
                    <a href="{{ route('admin.warehouse.transfers.create') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all">
                        ثبت اولین درخواست / انتقال
                    </a>
                </div>
            </div>
        @else
            <table class="w-full text-right text-xs table-fixed">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/60 text-slate-600 dark:text-slate-300 font-bold border-b border-gray-200 dark:border-gray-700">
                        <th class="py-3.5 px-4 w-[16%]">شماره حواله و درخواست</th>
                        <th class="py-3.5 px-4 w-[14%]">تاریخ ثبت / نیاز</th>
                        <th class="py-3.5 px-4 w-[22%]">مسیر انتقال و درخواست‌کننده</th>
                        <th class="py-3.5 px-4 w-[17%] text-center">اقلام و تعداد</th>
                        <th class="py-3.5 px-4 w-[16%] text-center">وضعیت و ارزش تمام‌شده</th>
                        <th class="py-3.5 px-4 w-[15%] text-center">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/70 font-sans">
                    @foreach($transfers as $transfer)
                        @php
                            $registeredTimestamp = $transfer->created_at && (!$transfer->request_date || $transfer->request_date->toDateString() === $transfer->created_at->toDateString())
                                ? (function_exists('jdate') ? jdate($transfer->created_at)->format('Y/m/d H:i') : $transfer->created_at->format('Y-m-d H:i'))
                                : ($transfer->request_date
                                    ? (function_exists('jdate') ? jdate($transfer->request_date)->format('Y/m/d') : $transfer->request_date->format('Y-m-d'))
                                    : '—');
                            $jalaliNeeded = $transfer->needed_date
                                ? (function_exists('jdate') ? jdate($transfer->needed_date)->format('Y/m/d') : $transfer->needed_date->format('Y-m-d'))
                                : null;
                        @endphp
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors {{ in_array($transfer->status, ['cancelled', 'rejected']) ? 'opacity-65' : '' }}">
                            <td class="py-3.5 px-4 align-middle">
                                <a href="{{ route('admin.warehouse.transfers.show', $transfer) }}"
                                   class="font-bold text-indigo-600 dark:text-indigo-300 hover:underline dir-ltr inline-block">
                                    {{ $transfer->transfer_number }}
                                </a>
                                @if($transfer->request_number)
                                    <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5 dir-ltr text-right">
                                        درخواست: {{ $transfer->request_number }}
                                    </div>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 align-middle">
                                <div class="font-bold text-gray-900 dark:text-white">
                                    <span class="text-[11px] font-normal text-slate-600 dark:text-slate-300">ثبت:</span>
                                    <span class="dir-ltr inline-block">{{ $registeredTimestamp }}</span>
                                </div>
                                @if($jalaliNeeded)
                                    <div class="text-[11px] font-bold mt-0.5 {{ $transfer->status === 'pending' && $transfer->needed_date->lte(today()) ? 'text-amber-600 dark:text-amber-400' : 'text-indigo-600 dark:text-indigo-300' }}">
                                        <span class="font-normal text-slate-600 dark:text-slate-300">نیاز:</span>
                                        <span class="dir-ltr inline-block">{{ $jalaliNeeded }}</span>
                                    </div>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 align-middle">
                                <div class="flex items-center gap-1.5 flex-wrap font-bold text-gray-900 dark:text-white">
                                    <span class="truncate">{{ $transfer->sourceWarehouse?->name ?? 'انبار مبدأ' }}</span>
                                    <span class="text-indigo-500">←</span>
                                    <span class="text-indigo-600 dark:text-indigo-300 truncate">{{ $transfer->destinationWarehouse?->name ?? 'مقصد نامشخص' }}</span>
                                </div>
                                <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5 truncate">
                                    @if($transfer->effective_requester_name !== '—')
                                        <span>درخواست‌کننده: <strong class="text-gray-800 dark:text-gray-200">{{ $transfer->effective_requester_name }}</strong></span>
                                        <span class="mx-1">·</span>
                                    @endif
                                    <span>ثبت‌کننده: <strong class="text-gray-800 dark:text-gray-200">{{ $transfer->creator_name }}</strong></span>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 align-middle text-center">
                                <button type="button"
                                        @click="expandedId = (expandedId === {{ $transfer->id }} ? null : {{ $transfer->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold transition-colors border font-sans"
                                        :class="expandedId === {{ $transfer->id }}
                                            ? 'bg-indigo-600 text-white border-indigo-600'
                                            : 'bg-indigo-50 text-indigo-700 border-indigo-200/70 hover:bg-indigo-100 dark:bg-indigo-500/15 dark:text-indigo-300 dark:border-indigo-500/30 dark:hover:bg-indigo-500/25'">
                                    <span>
                                        @if($transfer->status === 'delivered')
                                            {{ number_format($transfer->total_delivered_quantity) }} واحد ({{ $transfer->total_items_count }} قلم)
                                        @else
                                            {{ number_format($transfer->total_requested_quantity) }} واحد ({{ $transfer->total_items_count }} قلم)
                                        @endif
                                    </span>
                                    <svg class="w-3.5 h-3.5 transition-transform"
                                         :class="expandedId === {{ $transfer->id }} ? 'rotate-180' : ''"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </td>

                            <td class="py-3.5 px-4 align-middle text-center space-y-1">
                                @if($transfer->status === 'delivered')
                                    <span class="inline-block px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-200/70 dark:border-emerald-500/30">
                                        تحویل و منتقل‌شده
                                    </span>
                                    <div class="text-[11px] font-bold text-gray-900 dark:text-white">
                                        {{ number_format($transfer->total_cost_value) }} ریال
                                    </div>
                                @elseif($transfer->status === 'pending')
                                    <span class="inline-block px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 border border-amber-200/70 dark:border-amber-500/30">
                                        در انتظار تایید و تحویل
                                    </span>
                                @elseif($transfer->status === 'rejected')
                                    <span class="inline-block px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300 border border-rose-200/70 dark:border-rose-500/30">
                                        ردشده
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-gray-100 text-slate-700 dark:bg-gray-700 dark:text-slate-300 border border-gray-200 dark:border-gray-600">
                                        ابطال‌شده
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 align-middle">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.warehouse.transfers.show', $transfer) }}"
                                       title="{{ $transfer->status === 'pending' ? 'بررسی، تایید و تحویل کالا' : 'مشاهده و چاپ حواله' }}"
                                       class="p-2 rounded-xl text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 dark:text-slate-300 dark:hover:text-indigo-300 dark:hover:bg-indigo-500/15 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    @if($transfer->can_cancel)
                                        <form action="{{ route('admin.warehouse.transfers.destroy', $transfer) }}"
                                              method="POST"
                                              onsubmit="return confirm('آیا از ابطال حواله {{ $transfer->transfer_number }} و بازگرداندن موجودی اطمینان دارید؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    title="ابطال حواله"
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

                        {{-- In-Flow Expandable Items Sub-Table --}}
                        <tr x-show="expandedId === {{ $transfer->id }}" x-cloak>
                            <td colspan="6" class="p-0 bg-slate-50/90 dark:bg-slate-900/60 border-b border-indigo-100 dark:border-indigo-500/20">
                                <div class="p-4 sm:p-5 space-y-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex items-center gap-2 text-xs font-bold text-gray-900 dark:text-white">
                                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                            <span>اقلام حواله {{ $transfer->transfer_number }}</span>
                                            @if($transfer->notes)
                                                <span class="text-slate-600 dark:text-slate-300 font-normal">— {{ $transfer->notes }}</span>
                                            @endif
                                        </div>

                                        <a href="{{ route('admin.warehouse.transfers.show', $transfer) }}"
                                           class="text-xs font-bold text-indigo-600 dark:text-indigo-300 hover:underline">
                                            {{ $transfer->status === 'pending' ? 'باز کردن فرم تایید و تحویل' : 'مشاهده کامل و چاپ حواله' }}
                                        </a>
                                    </div>

                                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                                        <table class="w-full text-right text-xs">
                                            <thead>
                                                <tr class="bg-gray-50 dark:bg-gray-900/70 text-slate-600 dark:text-slate-300 font-bold border-b border-gray-200 dark:border-gray-700">
                                                    <th class="py-2.5 px-3">کد کالا</th>
                                                    <th class="py-2.5 px-3">نام کالا</th>
                                                    <th class="py-2.5 px-3 text-center">تعداد درخواستی</th>
                                                    <th class="py-2.5 px-3 text-center">تعداد تحویل‌شده</th>
                                                    <th class="py-2.5 px-3">بهای تمام‌شده (ریال)</th>
                                                    <th class="py-2.5 px-3">توضیحات</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-sans">
                                                @foreach($transfer->items as $line)
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
                                                        <td class="py-2.5 px-3 text-center font-bold text-gray-900 dark:text-white">
                                                            {{ number_format($line->requested_quantity) }} {{ $line->item?->unit }}
                                                        </td>
                                                        <td class="py-2.5 px-3 text-center font-bold {{ $line->delivered_quantity > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-600 dark:text-slate-300' }}">
                                                            {{ number_format($line->delivered_quantity) }} {{ $line->item?->unit }}
                                                        </td>
                                                        <td class="py-2.5 px-3 font-bold text-gray-900 dark:text-white">
                                                            {{ number_format($line->total_cost_value) }}
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
                {{ $transfers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
