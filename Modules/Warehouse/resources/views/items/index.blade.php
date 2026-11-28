@extends('layouts.user')

@section('title', 'شناسنامه کالاها')

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- Top Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">
                <a href="{{ route('admin.warehouse.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">داشبورد انبار</a>
                <span>/</span>
                <span class="text-gray-800 dark:text-gray-200">شناسنامه کالاها</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                شناسنامه و فهرست کالاها
            </h1>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.warehouse.items.pdf', request()->query()) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-all shadow-xs">
                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                گزارش PDF موجودی
            </a>

            <a href="{{ route('admin.warehouse.categories.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-200 font-bold text-xs transition-all shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                دسته‌بندی‌ها
            </a>

            <a href="{{ route('admin.warehouse.items.import.form') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-emerald-200 dark:border-emerald-700/60 bg-emerald-50/70 dark:bg-emerald-500/15 hover:bg-emerald-100 dark:hover:bg-emerald-500/25 text-emerald-700 dark:text-emerald-300 font-bold text-xs transition-all shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                درون‌ریزی اکسل
            </a>

            <a href="{{ route('admin.warehouse.purchases.create') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-indigo-200 dark:border-indigo-500/30 bg-indigo-50 dark:bg-indigo-500/15 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 font-bold text-xs transition-all shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                ثبت رسید خرید
            </a>

            <a href="{{ route('admin.warehouse.items.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                تعریف کالای جدید
            </a>
        </div>
    </div>

    @include('warehouse::partials.flash_messages')

    {{-- Valuation & Stock Metrics Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
        {{-- Total Items --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400">تنوع کالاها</p>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mt-1 font-sans">
                    {{ number_format($metrics['total_items_count']) }} <span class="text-xs font-normal text-gray-400">قلم</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </span>
        </div>

        {{-- Total Units in Stock --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400">کل موجودی فیزیکی</p>
                <h3 class="text-lg font-bold text-indigo-600 dark:text-indigo-400 mt-1 font-sans">
                    {{ number_format($metrics['total_units_in_stock']) }} <span class="text-xs font-normal text-gray-400">واحد</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                </svg>
            </span>
        </div>

        {{-- Cost Value --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400">ارزش بهای تمام‌شده خرید</p>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mt-1 font-sans">
                    {{ number_format($metrics['total_cost_value']) }} <span class="text-xs font-normal text-gray-400">ریال</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-500/15 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                </svg>
            </span>
        </div>

        {{-- Market Value --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400">ارزش موجودی به نرخ روز</p>
                <h3 class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-1 font-sans">
                    {{ number_format($metrics['total_market_value']) }} <span class="text-xs font-normal text-gray-400">ریال</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </span>
        </div>

        {{-- Stock Alerts --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between col-span-2 sm:col-span-1 shadow-xs">
            <div>
                <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400">هشدارهای کسری موجودی</p>
                <div class="flex items-center gap-1.5 mt-1.5">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300 font-sans border border-rose-200/50 dark:border-rose-500/30" title="کسری بحرانی">
                        {{ $metrics['critical_stock_count'] }} بحرانی
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 font-sans border border-amber-200/50 dark:border-amber-500/30" title="نیاز به سفارش">
                        {{ $metrics['reorder_stock_count'] }} سفارش
                    </span>
                </div>
            </div>
            <span class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </span>
        </div>
    </div>

    {{-- Filters & Search Toolbar --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-4 shadow-xs">
        <form action="{{ route('admin.warehouse.items.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            {{-- Search Text --}}
            <div class="lg:col-span-2 relative">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="جستجو با نام کالا، کد، کد سیستمی، برند، بارکد یا تیپ..."
                       class="w-full pr-9 pl-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-xs placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                    </svg>
                </div>
            </div>

            {{-- Category Filter --}}
            <div>
                <select name="category_id"
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2.5 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
                    <option value="" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">همه دسته‌بندی‌ها</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-bold" {{ (string)$categoryId === (string)$cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                        @foreach($cat->children as $sub)
                            <option value="{{ $sub->id }}" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white" {{ (string)$subcategoryId === (string)$sub->id ? 'selected' : '' }}>
                                ↳ {{ $sub->name }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            {{-- Brand Filter --}}
            <div>
                <select name="brand"
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2.5 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
                    <option value="" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">همه برندها</option>
                    @foreach($distinctBrands as $b)
                        <option value="{{ $b }}" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white" {{ $brand === $b ? 'selected' : '' }}>
                            {{ $b }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status Filter --}}
            <div>
                <select name="status"
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2.5 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans">
                    <option value="" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">همه وضعیت‌های موجودی</option>
                    <option value="critical" {{ $status === 'critical' ? 'selected' : '' }}>کسری بحرانی</option>
                    <option value="reorder" {{ $status === 'reorder' ? 'selected' : '' }}>نیاز به سفارش</option>
                    <option value="adequate" {{ $status === 'adequate' ? 'selected' : '' }}>موجودی مطلوب</option>
                </select>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2">
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-colors shadow-xs">
                    اعمال فیلتر
                </button>
                @if($search || $categoryId || $subcategoryId || $brand || $status || $warehouseId)
                    <a href="{{ route('admin.warehouse.items.index') }}"
                       class="px-3 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700/80 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 font-bold text-xs transition-colors shrink-0"
                       title="حذف فیلترها">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Items Table Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 overflow-hidden shadow-xs">
        @if($items->count() > 0)
            <div class="w-full overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700/80 text-gray-600 dark:text-gray-300 font-bold">
                        <tr>
                            <th class="px-5 py-3.5">کالا و مشخصات فنی</th>
                            <th class="px-4 py-3.5">شناسه و کد کالا (SKU)</th>
                            <th class="px-4 py-3.5 text-center">موجودی فیزیکی</th>
                            <th class="px-4 py-3.5">نرخ و بهای خرید</th>
                            <th class="px-4 py-3.5">ارزش موجودی و وضعیت</th>
                            <th class="px-4 py-3.5 text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody x-data="{ expandedId: null }" class="divide-y divide-gray-100 dark:divide-gray-700/60 text-gray-700 dark:text-gray-300 font-sans">
                        @foreach($items as $item)
                            {{-- Main Item Row --}}
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/20 transition-colors">
                                {{-- 1. Name & Specs --}}
                                <td class="px-5 py-4 max-w-sm">
                                    <div class="font-bold text-gray-900 dark:text-white text-sm">
                                        <a href="{{ route('admin.warehouse.items.show', $item) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                            {{ $item->name }}
                                        </a>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-wrap mt-1">
                                        @if($item->brand)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-500/30">
                                                {{ $item->brand }}
                                            </span>
                                        @endif
                                        @if($item->item_type)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300">
                                                {{ $item->item_type }}
                                            </span>
                                        @endif
                                        @if($item->model_spec)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 dir-ltr font-sans">
                                                {{ $item->model_spec }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1 text-[11px] text-slate-600 dark:text-slate-300 mt-1 flex-wrap">
                                        <span>{{ $item->category?->name ?? 'بدون دسته' }}</span>
                                        @if($item->subcategory)
                                            <span class="text-gray-400">↳</span>
                                            <span class="text-gray-700 dark:text-gray-200 font-bold">{{ $item->subcategory->name }}</span>
                                        @endif
                                        <span class="text-gray-400">·</span>
                                        <span>ثبت‌کننده: <strong class="text-gray-700 dark:text-gray-200">{{ $item->creator_name }}</strong></span>
                                    </div>
                                </td>

                                {{-- 2. Codes & SKU --}}
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="font-bold text-gray-900 dark:text-gray-100 font-sans dir-ltr text-right text-xs tracking-wide">
                                        {{ $item->code }}
                                    </div>
                                    <div class="text-[11px] text-indigo-600 dark:text-indigo-400 font-bold font-sans dir-ltr text-right mt-0.5">
                                        {{ $item->system_code }}
                                    </div>
                                    @if($item->barcode)
                                        <div class="text-[10px] text-gray-400 dark:text-gray-400 font-sans dir-ltr text-right mt-0.5">
                                            {{ $item->barcode }}
                                        </div>
                                    @endif
                                </td>

                                {{-- 3. Stock & In-Flow Expand Button --}}
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <button type="button"
                                            @click="expandedId = (expandedId === {{ $item->id }} ? null : {{ $item->id }})"
                                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl font-bold font-sans text-xs transition-all {{ $item->total_stock > 0 ? 'bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:hover:bg-indigo-500/25 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-500/30' : 'bg-slate-100 hover:bg-slate-200 text-slate-600 dark:bg-slate-800/80 dark:hover:bg-slate-700/80 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}"
                                            title="مشاهده پارت‌های خرید در انبار">
                                        <span>{{ number_format($item->total_stock) }}</span>
                                        <span class="text-[10px] font-normal font-sans">{{ $item->unit }}</span>
                                        <svg class="w-3.5 h-3.5 transition-transform duration-200"
                                             :class="{ 'rotate-180 text-indigo-600 dark:text-indigo-400': expandedId === {{ $item->id }} }"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    @if($item->activeBatches->count() > 0)
                                        <div class="text-[10px] text-gray-400 dark:text-gray-400 mt-1 font-sans">
                                            {{ $item->activeBatches->count() }} پارت فعال
                                        </div>
                                    @endif
                                </td>

                                {{-- 4. Price & Cost --}}
                                <td class="px-4 py-4 whitespace-nowrap font-sans">
                                    <div class="font-bold text-gray-900 dark:text-white text-xs">
                                        {{ $item->current_price ? number_format($item->current_price) . ' ریال' : '—' }}
                                    </div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                        آخرین خرید: {{ $item->last_purchase_price ? number_format($item->last_purchase_price) . ' ریال' : '—' }}
                                    </div>
                                </td>

                                {{-- 5. Valuation & Stock Status --}}
                                <td class="px-4 py-4 whitespace-nowrap font-sans">
                                    <div class="font-bold text-teal-600 dark:text-teal-400 text-xs">
                                        {{ number_format($item->total_cost_value) }} ریال
                                    </div>
                                    <div class="mt-1">
                                        @if($item->is_below_min_stock)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300 border border-rose-200/60 dark:border-rose-500/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                کسری بحرانی
                                            </span>
                                        @elseif($item->is_below_reorder_point)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 border border-amber-200/60 dark:border-amber-500/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                نیاز به سفارش
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-500/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                موجودی مطلوب
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- 6. Actions --}}
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('admin.warehouse.items.show', $item) }}"
                                           class="p-2 rounded-xl text-gray-400 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 hover:bg-indigo-50/60 dark:hover:bg-indigo-500/15 transition-colors"
                                           title="مشاهده جزئیات و کاردکس">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        <a href="{{ route('admin.warehouse.items.edit', $item) }}"
                                           class="p-2 rounded-xl text-gray-400 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 hover:bg-indigo-50/60 dark:hover:bg-indigo-500/15 transition-colors"
                                           title="ویرایش کالا">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z" />
                                            </svg>
                                        </a>

                                        <form action="{{ route('admin.warehouse.items.destroy', $item) }}"
                                              method="POST"
                                              onsubmit="return confirm('آیا از حذف این کالا اطمینان دارید؟');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-2 rounded-xl text-gray-400 hover:text-rose-600 dark:text-gray-400 dark:hover:text-rose-400 hover:bg-rose-50/60 dark:hover:bg-rose-500/15 transition-colors"
                                                    title="حذف کالا">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- Expandable Batches Sub-Row (Zero Vertical Scrollbar, Full-Width Professional Sub-Table) --}}
                            <tr x-show="expandedId === {{ $item->id }}" x-cloak class="bg-slate-50/70 dark:bg-slate-900/60 border-b-2 border-indigo-500/30">
                                <td colspan="6" class="p-3 sm:p-5">
                                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700 shadow-md space-y-4">

                                        {{-- Header: Product title, badge, summary stats & close button --}}
                                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 pb-3.5 border-b border-slate-200 dark:border-slate-700/80">
                                            <div class="flex items-center gap-2.5 flex-wrap">
                                                <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-300 flex items-center justify-center shrink-0">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                    </svg>
                                                </span>
                                                <div>
                                                    <h4 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2 flex-wrap">
                                                        <span>ریز پارت‌های خرید و سودآوری موجودی:</span>
                                                        <span class="text-indigo-600 dark:text-indigo-300">{{ $item->name }}</span>
                                                    </h4>
                                                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-300 mt-0.5 flex-wrap">
                                                        <span>کد کالا: <strong class="font-sans dir-ltr text-slate-700 dark:text-slate-200">{{ $item->code }}</strong></span>
                                                        <span>•</span>
                                                        <span>کد سیستمی: <strong class="font-sans dir-ltr text-indigo-600 dark:text-indigo-400">{{ $item->system_code }}</strong></span>
                                                        <span>•</span>
                                                        <span>نرخ روز: <strong class="font-sans text-slate-700 dark:text-slate-200">{{ $item->current_price ? number_format($item->current_price) . ' ریال' : 'ثبت نشده' }}</strong></span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Left: Metric summary pills & Close button --}}
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-900/80 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-sans">
                                                    <span class="text-slate-500 dark:text-slate-400">موجودی:</span>
                                                    <strong class="text-slate-900 dark:text-white">{{ number_format($item->total_stock) }} {{ $item->unit }}</strong>
                                                    <span class="text-slate-300 dark:text-slate-600">|</span>
                                                    <span class="text-slate-500 dark:text-slate-400">بهای خرید:</span>
                                                    <strong class="text-slate-900 dark:text-white">{{ number_format($item->total_cost_value) }} ریال</strong>
                                                    @if($item->current_price && $item->total_stock > 0)
                                                        <span class="text-slate-300 dark:text-slate-600">|</span>
                                                        <span class="text-slate-500 dark:text-slate-400">ارزش روز:</span>
                                                        <strong class="text-teal-600 dark:text-teal-400">{{ number_format($item->total_market_value) }} ریال</strong>
                                                    @endif
                                                </div>

                                                <button type="button"
                                                        @click="expandedId = null"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition-colors">
                                                    <span>بستن</span>
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Body: Full-width Sub-Table --}}
                                        @if($item->activeBatches->count() > 0)
                                            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                                                <table class="w-full text-right text-xs">
                                                    <thead class="bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-200 font-bold border-b border-slate-200 dark:border-slate-700">
                                                        <tr>
                                                            <th class="px-3.5 py-3 text-center w-10">#</th>
                                                            <th class="px-4 py-3">شناسه پارت خرید</th>
                                                            <th class="px-4 py-3">انبار / موقعیت</th>
                                                            <th class="px-4 py-3 text-center">موجودی فعلی</th>
                                                            <th class="px-4 py-3">بهای خرید هر واحد</th>
                                                            <th class="px-4 py-3">ارزش موجودی پارت</th>
                                                            <th class="px-4 py-3">ارزش به نرخ روز</th>
                                                            <th class="px-4 py-3">سود/تفاضل تورمی</th>
                                                            <th class="px-4 py-3 text-center">تاریخ ورود / انقضا</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-sans">
                                                        @foreach($item->activeBatches as $idx => $batch)
                                                            @php
                                                                $unitBuy = (float)$batch->unit_purchase_price;
                                                                $currentP = (float)($item->current_price ?? 0);
                                                                $remQty = (float)$batch->remaining_quantity;
                                                                $costVal = $remQty * $unitBuy;
                                                                $marketVal = $currentP > 0 ? ($remQty * $currentP) : 0;
                                                                $profitPerUnit = $currentP > 0 ? ($currentP - $unitBuy) : 0;
                                                                $totalProfit = $currentP > 0 ? ($marketVal - $costVal) : 0;
                                                                $profitPercent = ($currentP > 0 && $unitBuy > 0) ? round(($profitPerUnit / $unitBuy) * 100, 1) : 0;
                                                            @endphp
                                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                                                {{-- Index --}}
                                                                <td class="px-3.5 py-3.5 text-center text-slate-400 dark:text-slate-400 font-bold">
                                                                    {{ $idx + 1 }}
                                                                </td>

                                                                {{-- Batch Code --}}
                                                                <td class="px-4 py-3.5 whitespace-nowrap">
                                                                    <div class="font-bold text-indigo-700 dark:text-indigo-300 font-sans dir-ltr text-right text-xs">
                                                                        @if($batch->purchase_receipt_id)
                                                                            <a href="{{ route('admin.warehouse.purchases.show', $batch->purchase_receipt_id) }}" class="hover:underline">
                                                                                {{ $batch->batch_code }}
                                                                            </a>
                                                                        @else
                                                                            {{ $batch->batch_code }}
                                                                        @endif
                                                                    </div>
                                                                    @if($batch->supplier)
                                                                        <div class="text-[11px] text-slate-500 dark:text-slate-300 mt-0.5">
                                                                            تأمین‌کننده: {{ $batch->supplier }}
                                                                        </div>
                                                                    @endif
                                                                    @if($batch->transfer_request_id)
                                                                        <div class="mt-0.5">
                                                                            <a href="{{ route('admin.warehouse.transfers.show', $batch->transfer_request_id) }}"
                                                                               class="inline-flex items-center gap-1 text-[10px] font-bold text-teal-600 dark:text-teal-400 hover:underline">
                                                                                انتقال بین انبارها
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                    <div class="text-[10px] text-slate-600 dark:text-slate-300 mt-0.5">
                                                                        ثبت پارت: {{ $batch->creator_name }}
                                                                    </div>
                                                                </td>

                                                                {{-- Warehouse --}}
                                                                <td class="px-4 py-3.5 whitespace-nowrap">
                                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600">
                                                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                                                        {{ $batch->warehouse?->name ?? 'انبار مرکزی' }}
                                                                    </span>
                                                                </td>

                                                                {{-- Remaining / Initial Quantity --}}
                                                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                                                    <div class="font-bold text-slate-900 dark:text-white text-sm">
                                                                        {{ number_format($batch->remaining_quantity) }}
                                                                        <span class="text-xs font-normal text-slate-500 dark:text-slate-400">{{ $item->unit }}</span>
                                                                    </div>
                                                                    @if($batch->initial_quantity > $batch->remaining_quantity)
                                                                        <div class="text-[10px] text-slate-400 dark:text-slate-400 mt-0.5">
                                                                            از {{ number_format($batch->initial_quantity) }} اولیه ({{ number_format($batch->consumed_quantity) }} مصرف شده)
                                                                        </div>
                                                                    @else
                                                                        <div class="text-[10px] text-slate-400 dark:text-slate-400 mt-0.5">
                                                                            دست‌نخورده ({{ number_format($batch->initial_quantity) }} ورودی)
                                                                        </div>
                                                                    @endif
                                                                </td>

                                                                {{-- Unit Purchase Price --}}
                                                                <td class="px-4 py-3.5 whitespace-nowrap font-bold text-slate-900 dark:text-slate-100">
                                                                    {{ number_format($batch->unit_purchase_price) }} ریال
                                                                </td>

                                                                {{-- Cost Value (بهای تمام‌شده موجودی) --}}
                                                                <td class="px-4 py-3.5 whitespace-nowrap font-bold text-slate-900 dark:text-white">
                                                                    {{ number_format($costVal) }} ریال
                                                                </td>

                                                                {{-- Market Value (ارزش روز) --}}
                                                                <td class="px-4 py-3.5 whitespace-nowrap">
                                                                    @if($currentP > 0)
                                                                        <span class="font-bold text-teal-600 dark:text-teal-400">
                                                                            {{ number_format($marketVal) }} ریال
                                                                        </span>
                                                                    @else
                                                                        <span class="text-slate-400 dark:text-slate-500">—</span>
                                                                    @endif
                                                                </td>

                                                                {{-- Profit Margin / Gain --}}
                                                                <td class="px-4 py-3.5 whitespace-nowrap">
                                                                    @if($currentP > 0)
                                                                        @if($totalProfit > 0)
                                                                            <div class="inline-flex items-center gap-1 font-bold text-emerald-600 dark:text-emerald-400">
                                                                                <span>+{{ number_format($totalProfit) }} ریال</span>
                                                                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 font-sans dir-ltr">
                                                                                    +{{ $profitPercent }}%
                                                                                </span>
                                                                            </div>
                                                                            <div class="text-[10px] text-slate-400 dark:text-slate-400 mt-0.5">
                                                                                سود هر واحد: +{{ number_format($profitPerUnit) }} ریال
                                                                            </div>
                                                                        @elseif($totalProfit < 0)
                                                                            <div class="inline-flex items-center gap-1 font-bold text-rose-600 dark:text-rose-400">
                                                                                <span>{{ number_format($totalProfit) }} ریال</span>
                                                                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-rose-50 dark:bg-rose-500/20 text-rose-700 dark:text-rose-300 font-sans dir-ltr">
                                                                                    {{ $profitPercent }}%
                                                                                </span>
                                                                            </div>
                                                                        @else
                                                                            <span class="text-slate-500 dark:text-slate-400 text-xs">بدون تغییر</span>
                                                                        @endif
                                                                    @else
                                                                        <span class="text-slate-400 dark:text-slate-500 text-xs">نرخ روز ثبت نشده</span>
                                                                    @endif
                                                                </td>

                                                                {{-- Dates --}}
                                                                <td class="px-4 py-3.5 text-center whitespace-nowrap text-[11px]">
                                                                    <div class="text-slate-700 dark:text-slate-200 font-sans">
                                                                        @if($batch->purchase_date)
                                                                            <span class="text-slate-400">ورود:</span> {{ function_exists('jdate') ? jdate($batch->purchase_date)->format('Y/m/d') : \Carbon\Carbon::parse($batch->purchase_date)->format('Y/m/d') }}
                                                                        @else
                                                                            —
                                                                        @endif
                                                                    </div>
                                                                    @if($batch->expiry_date)
                                                                        <div class="text-amber-600 dark:text-amber-400 text-[10px] mt-0.5 font-sans">
                                                                            <span>انقضا:</span> {{ function_exists('jdate') ? jdate($batch->expiry_date)->format('Y/m/d') : \Carbon\Carbon::parse($batch->expiry_date)->format('Y/m/d') }}
                                                                        </div>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>

                                                    {{-- Table Footer Summary --}}
                                                    <tfoot class="bg-slate-100/90 dark:bg-slate-900/90 font-bold text-slate-800 dark:text-slate-100 border-t border-slate-200 dark:border-slate-700 font-sans">
                                                        <tr>
                                                            <td colspan="3" class="px-4 py-3 text-left">
                                                                مجموع کل پارت‌های فعال:
                                                            </td>
                                                            <td class="px-4 py-3 text-center text-sm text-indigo-600 dark:text-indigo-400">
                                                                {{ number_format($item->total_stock) }} {{ $item->unit }}
                                                            </td>
                                                            <td class="px-4 py-3 text-slate-400">—</td>
                                                            <td class="px-4 py-3 text-slate-900 dark:text-white">
                                                                {{ number_format($item->total_cost_value) }} ریال
                                                            </td>
                                                            <td class="px-4 py-3 text-teal-600 dark:text-teal-400">
                                                                {{ $item->current_price ? number_format($item->total_market_value) . ' ریال' : '—' }}
                                                            </td>
                                                            <td class="px-4 py-3">
                                                                @if($item->current_price && $item->total_stock > 0)
                                                                    @php
                                                                        $totalDiffAll = $item->total_market_value - $item->total_cost_value;
                                                                    @endphp
                                                                    <span class="{{ $totalDiffAll >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                                                        {{ $totalDiffAll >= 0 ? '+' : '' }}{{ number_format($totalDiffAll) }} ریال
                                                                    </span>
                                                                @else
                                                                    —
                                                                @endif
                                                            </td>
                                                            <td class="px-4 py-3 text-center text-slate-400">—</td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        @else
                                            <div class="py-8 text-center text-xs text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-dashed border-slate-200 dark:border-slate-700">
                                                هیچ پارت خرید فعالی برای این کالا در انبار ثبت نشده است.
                                            </div>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/80">
                {{ $items->links() }}
            </div>
        @else
            <div class="text-center py-12 px-4 space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">هنوز کالایی در انبار تعریف نشده است</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                    می‌توانید کالاها را به‌صورت دستی تعریف کنید یا فایل اکسل را درون‌ریزی نمایید.
                </p>
                <div class="flex items-center justify-center gap-2 pt-2">
                    <a href="{{ route('admin.warehouse.items.import.form') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-emerald-200 dark:border-emerald-700/60 bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-bold text-xs transition-colors">
                        درون‌ریزی فایل اکسل
                    </a>
                    <a href="{{ route('admin.warehouse.items.create') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-colors shadow-sm">
                        + تعریف اولین کالا
                    </a>
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
