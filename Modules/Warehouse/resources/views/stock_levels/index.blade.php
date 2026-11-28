@extends('layouts.user')

@section('title', $selectedWarehouse ? ('موجودی لحظه‌ای ' . $selectedWarehouse->name) : 'موجودی لحظه‌ای انبارها و انبارک‌ها')

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 font-sans">

    {{-- Top Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">
                <a href="{{ route('admin.warehouse.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">داشبورد انبار</a>
                <span>/</span>
                <a href="{{ route('admin.warehouse.inventory.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">انبار موجودی</a>
                <span>/</span>
                <span class="text-gray-900 dark:text-white">موجودی لحظه‌ای انبارها</span>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ $selectedWarehouse ? ('موجودی لحظه‌ای ' . $selectedWarehouse->name) : 'موجودی لحظه‌ای انبارها و انبارک‌ها' }}
                </h1>
                @if($selectedWarehouse)
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-500/30 dir-ltr">
                        {{ $selectedWarehouse->code }}
                    </span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.warehouse.stock-levels.pdf', request()->query()) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-all shadow-xs">
                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                خروجی PDF رسمی
            </a>

            <a href="{{ route('admin.warehouse.reorder-alerts.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/15 hover:bg-amber-100 dark:hover:bg-amber-500/25 text-amber-700 dark:text-amber-300 font-bold text-xs transition-all shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                هشدار نقطه سفارش
            </a>

            @if($selectedWarehouse && !$selectedWarehouse->is_main)
                <a href="{{ route('admin.warehouse.transfers.create', ['destination_warehouse_id' => $selectedWarehouse->id]) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-teal-200 dark:border-teal-500/30 bg-teal-50 dark:bg-teal-500/15 hover:bg-teal-100 dark:hover:bg-teal-500/25 text-teal-700 dark:text-teal-300 font-bold text-xs transition-all shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    درخواست کالا به این انبارک
                </a>
            @else
                <a href="{{ route('admin.warehouse.transfers.create') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-teal-200 dark:border-teal-500/30 bg-teal-50 dark:bg-teal-500/15 hover:bg-teal-100 dark:hover:bg-teal-500/25 text-teal-700 dark:text-teal-300 font-bold text-xs transition-all shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    درخواست و انتقال کالا
                </a>
            @endif

            <a href="{{ route('admin.warehouse.consumptions.create', $selectedWarehouse ? ['warehouse_id' => $selectedWarehouse->id] : []) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/15 hover:bg-rose-100 dark:hover:bg-rose-500/25 text-rose-700 dark:text-rose-300 font-bold text-xs transition-all shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                ثبت خروج / مصرف
            </a>

            <a href="{{ route('admin.warehouse.purchases.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                ثبت رسید خرید
            </a>
        </div>
    </div>

    @include('warehouse::partials.flash_messages')

    {{-- Dynamic Warehouse Selector Tabs --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-3 shadow-xs">
        <div class="flex flex-wrap items-center gap-2">
            {{-- All Warehouses Tab --}}
            @php
                $allQuery = collect(request()->query())->except(['warehouse_id', 'page'])->toArray();
            @endphp
            <a href="{{ route('admin.warehouse.stock-levels.index', $allQuery) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all border {{ !$warehouseId
                    ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm'
                    : 'bg-gray-50 dark:bg-gray-900/50 text-slate-700 dark:text-slate-200 border-gray-200 dark:border-gray-700 hover:border-indigo-400' }}">
                <span class="w-2 h-2 rounded-full {{ !$warehouseId ? 'bg-indigo-200' : 'bg-indigo-500' }}"></span>
                <span>کل انبارها (ماتریس توزیع)</span>
                <span class="px-2 py-0.5 rounded-lg text-[11px] font-sans {{ !$warehouseId
                        ? 'bg-indigo-500/40 text-white'
                        : 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-300 border border-gray-200 dark:border-gray-700' }}">
                    {{ number_format($allWarehousesTotalUnits) }}
                </span>
            </a>

            {{-- Dynamic Tabs for Every Active Inventory Warehouse & Sub-Warehouse --}}
            @foreach($warehouses as $w)
                @php
                    $isSelectedTab = (int)$warehouseId === (int)$w->id;
                    $tabQuery = array_merge($allQuery, ['warehouse_id' => $w->id]);
                    $sumUnits = $warehouseSummaries[$w->id]['total_units'] ?? 0;
                @endphp
                <a href="{{ route('admin.warehouse.stock-levels.index', $tabQuery) }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all border {{ $isSelectedTab
                        ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm'
                        : 'bg-gray-50 dark:bg-gray-900/50 text-slate-700 dark:text-slate-200 border-gray-200 dark:border-gray-700 hover:border-indigo-400' }}">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $w->effective_color }}"></span>
                    <span>{{ $w->name }}</span>
                    <span class="px-2 py-0.5 rounded-lg text-[11px] font-sans {{ $isSelectedTab
                            ? 'bg-indigo-500/40 text-white'
                            : 'bg-white dark:bg-gray-800 text-slate-700 dark:text-slate-200 border border-gray-200 dark:border-gray-700' }}">
                        {{ number_format($sumUnits) }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Live KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold text-slate-600 dark:text-slate-300">تنوع اقلام دارای موجودی</p>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mt-1 font-sans">
                    {{ number_format($metrics['in_stock_items_count']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">قلم</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700/60 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold text-slate-600 dark:text-slate-300">مجموع موجودی فیزیکی</p>
                <h3 class="text-lg font-bold text-indigo-600 dark:text-indigo-300 mt-1 font-sans">
                    {{ number_format($metrics['total_units_in_stock']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">واحد</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                </svg>
            </span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold text-slate-600 dark:text-slate-300">بهای تمام‌شده پارت‌ها</p>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mt-1 font-sans">
                    {{ number_format($metrics['total_cost_value']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">ریال</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-500/15 text-teal-600 dark:text-teal-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                </svg>
            </span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold text-slate-600 dark:text-slate-300">ارزش روز و تفاضل تورمی</p>
                <h3 class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-1 font-sans">
                    {{ number_format($metrics['total_market_value']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">ریال</span>
                </h3>
                <div class="text-[10px] font-bold {{ $metrics['unrealized_profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                    {{ ($metrics['unrealized_profit'] >= 0 ? '+' : '') . number_format($metrics['unrealized_profit']) }} ریال ({{ $metrics['profit_margin_percent'] }}%)
                </div>
            </div>
            <span class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold text-slate-600 dark:text-slate-300">هشدار انقضا (تا ۹۰ روز)</p>
                <h3 class="text-lg font-bold {{ $metrics['expiry_alerts_count'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white' }} mt-1 font-sans">
                    {{ number_format($metrics['expiry_alerts_count']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">پارت</span>
                </h3>
                @if($metrics['expired_batches_count'] > 0)
                    <div class="text-[10px] font-bold text-rose-600 dark:text-rose-400">
                        {{ $metrics['expired_batches_count'] }} پارت منقضی‌شده
                    </div>
                @endif
            </div>
            <span class="w-10 h-10 rounded-xl {{ $metrics['expiry_alerts_count'] > 0 ? 'bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-300' : 'bg-gray-100 dark:bg-gray-700/60 text-slate-600 dark:text-slate-300' }} flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
        </div>
    </div>

    {{-- Filters Bar --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-4 shadow-xs">
        <form action="{{ route('admin.warehouse.stock-levels.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @if($warehouseId)
                <input type="hidden" name="warehouse_id" value="{{ $warehouseId }}">
            @endif

            <div class="lg:col-span-2">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="جستجو با کد کالا (SKU)، کد سیستمی، نام یا بارکد..."
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3.5 py-2 text-xs placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
            </div>

            <div>
                <select name="category_id"
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3 py-2 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
                    <option value="">همه دسته‌بندی‌ها</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((string)$categoryId === (string)$cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="stock_scope"
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/70 text-gray-900 dark:text-white px-3 py-2 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
                    <option value="in_stock" @selected($stockScope === 'in_stock')>اقلام دارای موجودی (> ۰)</option>
                    <option value="all" @selected($stockScope === 'all')>همه کالاهای تعریف‌شده (حتی صفر)</option>
                    <option value="zero" @selected($stockScope === 'zero')>اقلام با موجودی صفر (ناموجود)</option>
                    <option value="expiring" @selected($stockScope === 'expiring')>دارای پارت نزدیک به انقضا / منقضی</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all">
                    اعمال فیلتر
                </button>
                @if($search || $categoryId || $stockScope !== 'in_stock')
                    <a href="{{ route('admin.warehouse.stock-levels.index', $warehouseId ? ['warehouse_id' => $warehouseId] : []) }}"
                       class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all">
                        بازنشانی
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Live Stock Table (6 Columns, Zero Horizontal Scroll, In-Flow Expandable Batch Sub-Table) --}}
    <div x-data="{ expandedId: null }"
         class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 shadow-xs overflow-hidden">
        @if($items->isEmpty())
            <div class="p-12 text-center space-y-3">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                    هیچ کالایی مطابق شرایط انتخاب‌شده در این انبار یافت نشد
                </h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 max-w-md mx-auto">
                    @if($stockScope === 'in_stock')
                        برای مشاهده کالاهایی که موجودی آن‌ها در این انبار صفر است، فیلتر «دامنه موجودی» را به «همه کالاهای تعریف‌شده» تغییر دهید.
                    @else
                        می‌توانید کالای جدید تعریف کنید یا با ثبت رسید خرید و انتقال بین انبارها موجودی این بخش را افزایش دهید.
                    @endif
                </p>
            </div>
        @else
            <table class="w-full text-right text-xs table-fixed">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/60 text-slate-600 dark:text-slate-300 font-bold border-b border-gray-200 dark:border-gray-700">
                        <th class="py-3.5 px-4 w-[17%]">کد کالا</th>
                        <th class="py-3.5 px-4 w-[24%]">نام کالا و دسته‌بندی</th>
                        <th class="py-3.5 px-4 w-[24%] text-center">
                            {{ $selectedWarehouse ? 'تعداد موجودی در این انبار' : 'توزیع و جمع موجودی' }}
                        </th>
                        <th class="py-3.5 px-4 w-[14%]">ارزش بهای خرید / روز</th>
                        <th class="py-3.5 px-4 w-[11%] text-center">تاریخ انقضا</th>
                        <th class="py-3.5 px-4 w-[10%] text-center">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/70 font-sans">
                    @foreach($items as $item)
                        @php
                            $scopedBatches = $item->activeBatches;
                            $scopedQty = (float)$scopedBatches->sum('remaining_quantity');
                            $scopedCost = (float)$scopedBatches->sum(fn($b) => (float)$b->remaining_quantity * (float)$b->unit_purchase_price);
                            $scopedMarket = (float)($item->current_price ?? 0) > 0
                                ? $scopedQty * (float)$item->current_price
                                : $scopedCost;
                            $nearestExpiry = $scopedBatches->whereNotNull('expiry_date')->sortBy('expiry_date')->first()?->expiry_date;
                            $byWarehouse = !$selectedWarehouse ? $scopedBatches->groupBy('warehouse_id') : collect();
                        @endphp
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors">
                            {{-- 1. Item Codes --}}
                            <td class="py-3.5 px-4 align-middle">
                                <div class="font-bold text-gray-900 dark:text-white dir-ltr text-right font-sans">
                                    {{ $item->code }}
                                </div>
                                <div class="text-[11px] font-bold text-indigo-600 dark:text-indigo-300 dir-ltr text-right mt-0.5 font-sans">
                                    {{ $item->system_code }}
                                </div>
                            </td>

                            {{-- 2. Item Name & Category & Creator --}}
                            <td class="py-3.5 px-4 align-middle">
                                <a href="{{ route('admin.warehouse.items.show', $item) }}"
                                   class="font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-300 transition-colors block truncate">
                                    {{ $item->name }}
                                </a>
                                <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5 truncate">
                                    <span>{{ $item->category?->name ?? 'بدون دسته' }}</span>
                                    @if($item->subcategory)
                                        <span>↳ {{ $item->subcategory->name }}</span>
                                    @endif
                                    <span class="mx-1">·</span>
                                    <span>ثبت: {{ $item->creator_name }}</span>
                                </div>
                            </td>

                            {{-- 3. Quantity / Distribution + Expand Batches --}}
                            <td class="py-3.5 px-4 align-middle text-center">
                                <div class="flex flex-col items-center gap-1.5">
                                    <button type="button"
                                            @click="expandedId = (expandedId === {{ $item->id }} ? null : {{ $item->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold text-xs transition-colors border font-sans"
                                            :class="expandedId === {{ $item->id }}
                                                ? 'bg-indigo-600 text-white border-indigo-600'
                                                : '{{ $scopedQty > 0 ? 'bg-indigo-50 text-indigo-700 border-indigo-200/70 hover:bg-indigo-100 dark:bg-indigo-500/15 dark:text-indigo-300 dark:border-indigo-500/30 dark:hover:bg-indigo-500/25' : 'bg-gray-100 text-slate-600 border-gray-200 dark:bg-gray-700 dark:text-slate-300 dark:border-gray-600' }}'">
                                        <span>{{ number_format($scopedQty) }} {{ $item->unit }}</span>
                                        <span class="text-[10px] opacity-80">({{ $scopedBatches->count() }} پارت)</span>
                                        <svg class="w-3.5 h-3.5 transition-transform"
                                             :class="expandedId === {{ $item->id }} ? 'rotate-180' : ''"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    @if(!$selectedWarehouse && $byWarehouse->isNotEmpty())
                                        <div class="flex flex-wrap items-center justify-center gap-1">
                                            @foreach($byWarehouse as $whId => $whBatches)
                                                @php
                                                    $whObj = $whBatches->first()?->warehouse;
                                                    $whSum = (float)$whBatches->sum('remaining_quantity');
                                                @endphp
                                                @if($whObj && $whSum > 0)
                                                    <a href="{{ route('admin.warehouse.stock-levels.index', ['warehouse_id' => $whObj->id]) }}"
                                                       class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-700/70 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors">
                                                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $whObj->effective_color }}"></span>
                                                        <span>{{ $whObj->name }}: {{ number_format($whSum) }}</span>
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- 4. Cost & Market Value --}}
                            <td class="py-3.5 px-4 align-middle">
                                <div class="font-bold text-gray-900 dark:text-white">
                                    {{ number_format($scopedCost) }} <span class="text-[10px] font-normal text-slate-600 dark:text-slate-300">ریال</span>
                                </div>
                                @if((float)$item->current_price > 0)
                                    <div class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">
                                        روز: {{ number_format($scopedMarket) }} ریال
                                    </div>
                                @endif
                            </td>

                            {{-- 5. Nearest Expiry --}}
                            <td class="py-3.5 px-4 align-middle text-center">
                                @if($nearestExpiry)
                                    @php
                                        $isExpired = $nearestExpiry->lt(today());
                                        $isNear = !$isExpired && $nearestExpiry->lte(today()->addDays(90));
                                        $expJalali = function_exists('jdate') ? jdate($nearestExpiry)->format('Y/m/d') : $nearestExpiry->format('Y-m-d');
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 rounded-md text-[11px] font-bold dir-ltr border {{ $isExpired
                                        ? 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-500/15 dark:text-rose-300 dark:border-rose-500/30'
                                        : ($isNear
                                            ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/30'
                                            : 'bg-gray-50 text-slate-700 border-gray-200 dark:bg-gray-700 dark:text-slate-200 dark:border-gray-600') }}">
                                        {{ $expJalali }}
                                    </span>
                                @else
                                    <span class="text-slate-600 dark:text-slate-300">—</span>
                                @endif
                            </td>

                            {{-- 6. Actions --}}
                            <td class="py-3.5 px-4 align-middle">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.warehouse.items.show', $item) }}"
                                       title="مشاهده کاردکس کالا"
                                       class="p-1.5 rounded-lg text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 dark:text-slate-300 dark:hover:text-indigo-300 dark:hover:bg-indigo-500/15 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    <a href="{{ route('admin.warehouse.transfers.create', array_filter(['item_id' => $item->id, 'destination_warehouse_id' => ($selectedWarehouse && !$selectedWarehouse->is_main) ? $selectedWarehouse->id : null])) }}"
                                       title="درخواست / انتقال این کالا"
                                       class="p-1.5 rounded-lg text-slate-600 hover:text-teal-600 hover:bg-teal-50 dark:text-slate-300 dark:hover:text-teal-300 dark:hover:bg-teal-500/15 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                    </a>

                                    @if($scopedQty > 0)
                                        <a href="{{ route('admin.warehouse.consumptions.create', array_filter(['item_id' => $item->id, 'warehouse_id' => $selectedWarehouse?->id])) }}"
                                           title="ثبت خروج / مصرف این کالا"
                                           class="p-1.5 rounded-lg text-slate-600 hover:text-rose-600 hover:bg-rose-50 dark:text-slate-300 dark:hover:text-rose-300 dark:hover:bg-rose-500/15 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- In-Flow Expandable Batches Sub-Row --}}
                        <tr x-show="expandedId === {{ $item->id }}" x-cloak>
                            <td colspan="6" class="p-0 bg-slate-50/90 dark:bg-slate-900/60 border-b border-indigo-100 dark:border-indigo-500/20">
                                <div class="p-4 sm:p-5 space-y-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex items-center gap-2 text-xs font-bold text-gray-900 dark:text-white">
                                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                            <span>ریز پارت‌های موجودی «{{ $item->name }}» {{ $selectedWarehouse ? ('در ' . $selectedWarehouse->name) : 'در کل انبارها' }}</span>
                                        </div>

                                        <a href="{{ route('admin.warehouse.items.show', $item) }}"
                                           class="text-xs font-bold text-indigo-600 dark:text-indigo-300 hover:underline">
                                            مشاهده کاردکس کامل کالا
                                        </a>
                                    </div>

                                    @if($scopedBatches->isEmpty())
                                        <div class="p-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs text-slate-600 dark:text-slate-300 text-center">
                                            هیچ پارت فعالی در این محدوده موجود نیست.
                                        </div>
                                    @else
                                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                                            <table class="w-full text-right text-xs">
                                                <thead>
                                                    <tr class="bg-gray-50 dark:bg-gray-900/70 text-slate-600 dark:text-slate-300 font-bold border-b border-gray-200 dark:border-gray-700">
                                                        <th class="py-2.5 px-3">کد پارت / فاکتور</th>
                                                        <th class="py-2.5 px-3">انبار مستقر</th>
                                                        <th class="py-2.5 px-3 text-center">باقیمانده / اولیه</th>
                                                        <th class="py-2.5 px-3">فی خرید واحد</th>
                                                        <th class="py-2.5 px-3">ارزش خرید باقیمانده</th>
                                                        <th class="py-2.5 px-3">ثبت‌کننده پارت</th>
                                                        <th class="py-2.5 px-3 text-center">تاریخ ورود / انقضا</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-sans">
                                                    @foreach($scopedBatches as $b)
                                                        @php
                                                            $pDate = $b->purchase_date ? (function_exists('jdate') ? jdate($b->purchase_date)->format('Y/m/d') : $b->purchase_date->format('Y-m-d')) : '—';
                                                            $eDate = $b->expiry_date ? (function_exists('jdate') ? jdate($b->expiry_date)->format('Y/m/d') : $b->expiry_date->format('Y-m-d')) : null;
                                                        @endphp
                                                        <tr>
                                                            <td class="py-2.5 px-3">
                                                                <div class="font-bold text-indigo-600 dark:text-indigo-300 dir-ltr text-right">
                                                                    @if($b->purchase_receipt_id)
                                                                        <a href="{{ route('admin.warehouse.purchases.show', $b->purchase_receipt_id) }}" class="hover:underline">
                                                                            {{ $b->batch_code }}
                                                                        </a>
                                                                    @else
                                                                        {{ $b->batch_code }}
                                                                    @endif
                                                                </div>
                                                                @if($b->transfer_request_id)
                                                                    <a href="{{ route('admin.warehouse.transfers.show', $b->transfer_request_id) }}"
                                                                       class="text-[10px] font-bold text-teal-600 dark:text-teal-400 hover:underline">
                                                                        انتقال بین انبارها
                                                                    </a>
                                                                @endif
                                                            </td>
                                                            <td class="py-2.5 px-3 text-slate-700 dark:text-slate-200 font-bold">
                                                                {{ $b->warehouse?->name ?? '—' }}
                                                            </td>
                                                            <td class="py-2.5 px-3 text-center font-bold text-gray-900 dark:text-white">
                                                                {{ number_format($b->remaining_quantity) }}
                                                                <span class="text-slate-600 dark:text-slate-300 font-normal">/ {{ number_format($b->initial_quantity) }} {{ $item->unit }}</span>
                                                            </td>
                                                            <td class="py-2.5 px-3 text-gray-900 dark:text-white font-bold">
                                                                {{ number_format($b->unit_purchase_price) }} ریال
                                                            </td>
                                                            <td class="py-2.5 px-3 font-bold text-indigo-600 dark:text-indigo-300">
                                                                {{ number_format($b->remaining_quantity * $b->unit_purchase_price) }} ریال
                                                            </td>
                                                            <td class="py-2.5 px-3 text-slate-600 dark:text-slate-300">
                                                                {{ $b->creator_name }}
                                                            </td>
                                                            <td class="py-2.5 px-3 text-center">
                                                                <div class="dir-ltr text-gray-900 dark:text-white">{{ $pDate }}</div>
                                                                @if($eDate)
                                                                    <div class="text-[10px] font-bold text-amber-600 dark:text-amber-400 dir-ltr">انقضا: {{ $eDate }}</div>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-4 border-t border-gray-200 dark:border-gray-700/80">
                {{ $items->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
