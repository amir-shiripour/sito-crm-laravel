@extends('layouts.user')

@section('title', 'خروج و مصرف کالا')

@section('content')
@includeIf('partials.jalali-date-picker')

<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 font-sans"
     x-data="{ expandedId: null }">

    {{-- Top Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">
                <a href="{{ route('admin.warehouse.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">داشبورد انبار</a>
                <span>/</span>
                <a href="{{ route('admin.warehouse.inventory.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">انبار موجودی</a>
                <span>/</span>
                <span class="text-gray-900 dark:text-white">خروج و مصرف کالا</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                اسناد خروج و ثبت مصرف کالا
            </h1>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.warehouse.consumptions.pdf', request()->query()) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-all shadow-xs">
                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                گزارش PDF مصرف
            </a>

            <a href="{{ route('admin.warehouse.consumptions.import.form') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-indigo-200 dark:border-indigo-500/30 bg-indigo-50 dark:bg-indigo-500/15 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 font-bold text-xs transition-all shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                درون‌ریزی اکسل مصرف
            </a>

            <a href="{{ route('admin.warehouse.consumptions.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                ثبت خروج / مصرف جدید
            </a>
        </div>
    </div>

    @include('warehouse::partials.flash_messages')

    {{-- Live KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold text-slate-600 dark:text-slate-300">کل اسناد خروج ثبت‌شده</p>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mt-1 font-sans">
                    {{ number_format($metrics['total_documents']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">سند</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold text-slate-600 dark:text-slate-300">مصرف پرونده / بیماران</p>
                <h3 class="text-lg font-bold text-indigo-600 dark:text-indigo-400 mt-1 font-sans">
                    {{ number_format($metrics['patient_documents']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">پرونده</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold text-slate-600 dark:text-slate-300">تعداد مصرفی ماه جاری</p>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mt-1 font-sans">
                    {{ rtrim(rtrim(number_format((float)$metrics['this_month_units'], 2), '0'), '.') }}
                    <span class="text-xs font-normal text-slate-600 dark:text-slate-300">واحد</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold text-slate-600 dark:text-slate-300">بهای تمام‌شده مصرف ماه</p>
                <h3 class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-1 font-sans">
                    {{ number_format($metrics['this_month_cost']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">ریال</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold text-slate-600 dark:text-slate-300">خروجی‌های امروز</p>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mt-1 font-sans">
                    {{ number_format($metrics['today_documents']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">سند</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700/60 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </span>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-4 shadow-xs">
        <form action="{{ route('admin.warehouse.consumptions.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-8 gap-3">
            <div class="lg:col-span-2">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="جستجو با شماره سند، نام بیمار، شماره پرونده، کد یا نام کالا..."
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2 text-xs placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
            </div>

            <div>
                <select name="warehouse_id"
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3 py-2 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
                    <option value="">همه انبارها و انبارک‌ها</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" @selected((string)$warehouseId === (string)$w->id)>
                            {{ $w->name }} {{ $w->is_main ? '(مرکزی)' : '(انبارک)' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="issue_type"
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3 py-2 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
                    <option value="">همه انواع خروج / مصرف</option>
                    <option value="patient_consumption" @selected($issueType === 'patient_consumption')>مصرف بیمار / پرونده</option>
                    <option value="department_consumption" @selected($issueType === 'department_consumption')>مصرف داخلی بخش</option>
                    <option value="waste_expired" @selected($issueType === 'waste_expired')>ضایعات / منقضی / آسیب‌دیده</option>
                    <option value="other" @selected($issueType === 'other')>سایر خروجی‌ها</option>
                </select>
            </div>

            <div>
                <select name="status"
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3 py-2 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="registered" @selected($status === 'registered')>ثبت‌شده</option>
                    <option value="cancelled" @selected($status === 'cancelled')>ابطال‌شده</option>
                </select>
            </div>

            <div>
                <input type="text"
                       name="date_from"
                       value="{{ $dateFrom }}"
                       data-jdp
                       placeholder="از تاریخ مصرف"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3 py-2 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dir-ltr text-right font-sans">
            </div>

            <div>
                <input type="text"
                       name="date_to"
                       value="{{ $dateTo }}"
                       data-jdp
                       placeholder="تا تاریخ مصرف"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3 py-2 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dir-ltr text-right font-sans">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all">
                    جستجو
                </button>
                @if($search || $warehouseId || $issueType || $status || $dateFrom || $dateTo)
                    <a href="{{ route('admin.warehouse.consumptions.index') }}"
                       class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all no-underline">
                        بازنشانی
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Documents Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 shadow-xs overflow-hidden">
        @if($documents->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/60 border-b border-gray-200 dark:border-gray-700 text-[11px] font-bold text-slate-600 dark:text-slate-300">
                            <th class="px-4 py-3.5">شماره سند و نوع</th>
                            <th class="px-4 py-3.5">تاریخ مصرف / ثبت</th>
                            <th class="px-4 py-3.5">بیمار / شماره پرونده</th>
                            <th class="px-4 py-3.5">انبار / انبارک مبدأ</th>
                            <th class="px-4 py-3.5 text-center">اقلام و تعداد</th>
                            <th class="px-4 py-3.5 text-center">بهای تمام‌شده</th>
                            <th class="px-4 py-3.5">ثبت‌کننده</th>
                            <th class="px-4 py-3.5 text-left">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-xs font-sans">
                        @foreach($documents as $doc)
                            @php
                                $issueJalali = $doc->issue_date ? (function_exists('jdate') ? jdate($doc->issue_date)->format('Y/m/d') : $doc->issue_date->format('Y-m-d')) : '—';
                                $createdJalali = $doc->created_at ? (function_exists('jdate') ? jdate($doc->created_at)->format('Y/m/d H:i') : $doc->created_at->format('Y-m-d H:i')) : '—';
                            @endphp
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-900/30 transition-colors {{ $doc->status === 'cancelled' ? 'opacity-60' : '' }}">
                                <td class="px-4 py-3.5 align-top space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <a href="{{ route('admin.warehouse.consumptions.show', $doc) }}"
                                           class="px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 font-bold text-[11px] dir-ltr font-sans border border-indigo-200/70 dark:border-indigo-500/30 hover:underline">
                                            {{ $doc->issue_number }}
                                        </a>
                                        @if($doc->status === 'cancelled')
                                            <span class="px-2 py-0.5 rounded-lg bg-rose-50 dark:bg-rose-500/15 text-rose-700 dark:text-rose-300 text-[10px] font-bold">
                                                ابطال‌شده
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-600 dark:text-slate-300">
                                        {{ $doc->issue_type_label }}
                                    </div>
                                </td>

                                <td class="px-4 py-3.5 align-top space-y-1">
                                    <div class="font-bold text-gray-900 dark:text-white dir-ltr text-right">
                                        {{ $issueJalali }}
                                    </div>
                                    <div class="text-[10px] text-slate-600 dark:text-slate-300 dir-ltr text-right">
                                        ثبت: {{ $createdJalali }}
                                    </div>
                                </td>

                                <td class="px-4 py-3.5 align-top">
                                    @if($doc->effective_patient_name || $doc->effective_case_number)
                                        <div class="font-bold text-gray-900 dark:text-white">
                                            {{ $doc->effective_patient_name ?? '—' }}
                                        </div>
                                        @if($doc->effective_case_number)
                                            <div class="text-[11px] text-indigo-600 dark:text-indigo-300 mt-0.5">
                                                پرونده: <span class="font-bold">{{ $doc->effective_case_number }}</span>
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-slate-600 dark:text-slate-300">
                                            {{ $doc->effective_consumer_name ?: 'مصرف عمومی بخش' }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5 align-top">
                                    <div class="flex flex-col gap-1">
                                        @if($doc->sourceWarehouse)
                                            <span class="inline-flex items-center gap-1.5 font-bold text-gray-800 dark:text-gray-200">
                                                <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $doc->sourceWarehouse->effective_color }}"></span>
                                                {{ $doc->sourceWarehouse->name }}
                                            </span>
                                        @endif
                                        <div class="text-[10px] text-slate-600 dark:text-slate-300 truncate max-w-xs">
                                            {{ $doc->items->take(2)->map(fn($li) => $li->item?->name)->filter()->join('، ') }}
                                            @if($doc->items->count() > 2)
                                                و {{ $doc->items->count() - 2 }} قلم دیگر
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3.5 text-center align-top">
                                    <button type="button"
                                            @click="expandedId = (expandedId === {{ $doc->id }} ? null : {{ $doc->id }})"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold border transition-colors"
                                            :class="expandedId === {{ $doc->id }}
                                                ? 'bg-indigo-600 text-white border-indigo-600'
                                                : 'bg-indigo-50 text-indigo-700 border-indigo-200/70 hover:bg-indigo-100 dark:bg-indigo-500/15 dark:text-indigo-300 dark:border-indigo-500/30'">
                                        <span>{{ rtrim(rtrim(number_format((float)$doc->total_quantity, 2), '0'), '.') }}</span>
                                        <span class="text-[10px]">({{ $doc->items_count }} ردیف)</span>
                                    </button>
                                </td>

                                <td class="px-4 py-3.5 text-center align-top">
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                        {{ number_format((float)$doc->total_cost_value) }}
                                    </span>
                                    <span class="text-[10px] text-slate-600 dark:text-slate-300">ریال</span>
                                </td>

                                <td class="px-4 py-3.5 align-top">
                                    <div class="font-bold text-gray-800 dark:text-gray-200">
                                        {{ $doc->creator_name }}
                                    </div>
                                </td>

                                <td class="px-4 py-3.5 text-left align-top">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.warehouse.consumptions.show', $doc) }}"
                                           class="inline-flex items-center px-2.5 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-200 text-[11px] font-bold transition-colors no-underline">
                                            مشاهده
                                        </a>
                                        <a href="{{ route('admin.warehouse.consumptions.show-pdf', $doc) }}"
                                           target="_blank"
                                           class="inline-flex items-center px-2.5 py-1.5 rounded-xl border border-indigo-200 dark:border-indigo-500/30 bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 text-[11px] font-bold transition-colors no-underline">
                                            PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            {{-- In-Flow Expandable Item Lines Sub-Row --}}
                            <tr x-show="expandedId === {{ $doc->id }}" x-cloak>
                                <td colspan="8" class="p-0 bg-slate-50/90 dark:bg-slate-900/60 border-b border-indigo-100 dark:border-indigo-500/20">
                                    <div class="p-4 space-y-2.5">
                                        <div class="flex items-center justify-between text-xs font-bold text-gray-900 dark:text-white">
                                            <span>اقلام ثبت‌شده در سند «{{ $doc->issue_number }}»</span>
                                            <a href="{{ route('admin.warehouse.consumptions.show', $doc) }}"
                                               class="text-indigo-600 dark:text-indigo-300 no-underline hover:text-indigo-700">
                                                مشاهده جزئیات کامل ←
                                            </a>
                                        </div>
                                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                                            <table class="w-full text-right text-xs">
                                                <thead>
                                                    <tr class="bg-gray-50 dark:bg-gray-900/70 text-slate-600 dark:text-slate-300 font-bold border-b border-gray-200 dark:border-gray-700">
                                                        <th class="py-2 px-3">کد کالا</th>
                                                        <th class="py-2 px-3">نام کالا</th>
                                                        <th class="py-2 px-3">انبار مبدأ</th>
                                                        <th class="py-2 px-3 text-center">تعداد</th>
                                                        <th class="py-2 px-3 text-center">بهای تمام‌شده (ریال)</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                                    @foreach($doc->items as $subLine)
                                                        <tr>
                                                            <td class="py-2 px-3 font-bold text-indigo-600 dark:text-indigo-300 dir-ltr text-right">{{ $subLine->item?->code }}</td>
                                                            <td class="py-2 px-3 font-bold text-gray-900 dark:text-white">{{ $subLine->item?->name }}</td>
                                                            <td class="py-2 px-3">{{ $subLine->warehouse?->name ?? '—' }}</td>
                                                            <td class="py-2 px-3 text-center font-bold">{{ rtrim(rtrim(number_format((float)$subLine->quantity, 2), '0'), '.') }} {{ $subLine->item?->unit }}</td>
                                                            <td class="py-2 px-3 text-center font-bold text-emerald-600 dark:text-emerald-400">{{ number_format((float)$subLine->total_cost) }}</td>
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
            </div>

            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700/60">
                {{ $documents->links() }}
            </div>
        @else
            <div class="p-10 text-center space-y-2">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                    هنوز هیچ سند خروج و مصرفی ثبت نشده است
                </h3>
                <p class="text-xs text-slate-600 dark:text-slate-300">
                    برای ثبت خروج کالا از انبار مرکزی یا انبارک‌ها (مصرف بیمار، بخش یا ضایعات)، سند جدید ثبت کنید یا از اکسل درون‌ریزی نمایید.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
