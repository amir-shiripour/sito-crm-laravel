@extends('layouts.user')

@section('title', 'جزئیات و کاردکس پارت‌های «' . $item->name . '»')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    {{-- Panel Standard Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-200 dark:border-gray-800">
        <div class="flex items-start sm:items-center gap-3">
            <span class="flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 text-white shadow-sm shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </span>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $item->name }}
                    </h1>
                    @if(!$item->is_active)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300">
                            غیرفعال
                        </span>
                    @endif
                    @if($item->is_below_min_stock)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></span>
                            کسری بحرانی
                        </span>
                    @elseif($item->is_below_reorder_point)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">
                            نیازمند سفارش خرید
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                            موجودی مطلوب
                        </span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400 mt-1">
                    <span>کد سیستمی: <strong class="text-indigo-600 dark:text-indigo-400 dir-ltr inline-block font-sans">{{ $item->system_code }}</strong></span>
                    <span>کد تجاری (SKU): <strong class="text-gray-900 dark:text-gray-200 dir-ltr inline-block font-sans">{{ $item->code }}</strong></span>
                    @if($item->barcode)
                        <span>بارکد: <strong class="text-gray-700 dark:text-gray-300 dir-ltr inline-block font-sans">{{ $item->barcode }}</strong></span>
                    @endif
                    @if($item->category)
                        <span>دسته: <strong class="text-gray-700 dark:text-gray-300">{{ $item->category->name }}</strong></span>
                    @endif
                    @if($item->subcategory)
                        <span>زیردسته: <strong class="text-gray-700 dark:text-gray-300">{{ $item->subcategory->name }}</strong></span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.warehouse.items.edit', $item) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                ویرایش کالا
            </a>

            <a href="{{ route('admin.warehouse.items.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-bold transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت به لیست
            </a>
        </div>
    </div>

    @include('warehouse::partials.flash_messages')

    {{-- Top Key Financial & Inventory Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Total Stock --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-5 shadow-xs">
            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 mb-2">
                <span class="text-xs font-bold">کل موجودی انبارها</span>
                <span class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold text-gray-900 dark:text-white font-sans">
                    {{ number_format($item->total_stock) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $item->unit }}</span>
            </div>
            <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400 flex items-center justify-between border-t border-gray-100 dark:border-gray-700/60 pt-2 font-sans">
                <span>حداقل ایمنی: {{ number_format($item->min_stock) }}</span>
                <span>نقطه سفارش: {{ number_format($item->reorder_point) }}</span>
            </div>
        </div>

        {{-- Card 2: Cost Basis (بهای تمام‌شده خرید) --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-5 shadow-xs">
            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 mb-2">
                <span class="text-xs font-bold">بهای تمام‌شده موجودی فعلی</span>
                <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl font-bold text-gray-900 dark:text-white font-sans">
                    {{ number_format($item->total_cost_value) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">ریال</span>
            </div>
            <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700/60 pt-2 font-sans">
                میانگین وزنی فی خرید: <strong class="text-gray-700 dark:text-gray-200">{{ number_format($item->average_purchase_price) }}</strong> ریال
            </div>
        </div>

        {{-- Card 3: Current Market Value (ارزش به نرخ روز) --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-5 shadow-xs">
            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 mb-2">
                <span class="text-xs font-bold">ارزش موجودی بر مبنای نرخ روز</span>
                <span class="p-2 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </span>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl font-bold text-teal-600 dark:text-teal-400 font-sans">
                    {{ number_format($item->total_market_value) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">ریال</span>
            </div>
            <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700/60 pt-2 font-sans">
                نرخ ثبت شده روز: <strong class="text-gray-700 dark:text-gray-200">{{ number_format($item->current_price ?? 0) }}</strong> ریال
            </div>
        </div>

        {{-- Card 4: Unrealized Profit / Margin --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-5 shadow-xs">
            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 mb-2">
                <span class="text-xs font-bold">حاشیه سود ناشی از تورم/نرخ روز</span>
                <span class="p-2 rounded-xl {{ $item->unrealized_profit >= 0 ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </span>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl font-bold {{ $item->unrealized_profit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} font-sans">
                    {{ ($item->unrealized_profit >= 0 ? '+' : '') . number_format($item->unrealized_profit) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">ریال</span>
            </div>
            <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700/60 pt-2 font-sans">
                @php
                    $profitPct = $item->total_cost_value > 0 ? ($item->unrealized_profit / $item->total_cost_value) * 100 : 0;
                @endphp
                درصد بازدهی سرمایه: <strong class="{{ $profitPct >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ number_format($profitPct, 1) }}%</strong>
            </div>
        </div>
    </div>

    {{-- Main Batch Breakdown Table (تفکیک دقیق پارت‌های خرید و موجودی باقیمانده از هر پارت) --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="w-3.5 h-3.5 rounded-full bg-indigo-600"></span>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">
                        تفکیک موجودی بر اساس پارت‌ها / فاکتورهای خرید (FIFO)
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        مشاهده موجودی باقیمانده از هر خرید، فی خرید اختصاصی، ارزش روز و محاسبه حاشیه سود هر پارت
                    </p>
                </div>
            </div>

            <span class="text-xs font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/60 px-3 py-1.5 rounded-xl border border-indigo-200/60 dark:border-indigo-800/60 font-sans">
                {{ $item->batches->count() }} پارت ثبت‌شده در سیستم
            </span>
        </div>

        @if($item->batches->isEmpty())
            <div class="p-12 text-center text-gray-500 dark:text-gray-400">
                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <p class="text-sm font-bold">هیچ پارت خریدی برای این کالا ثبت نشده است.</p>
                <p class="text-xs mt-1">با ثبت فاکتور ورود به انبار، پارت‌های خرید همراه با فی و موجودی اولیه درج خواهند شد.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/60 text-gray-600 dark:text-gray-300 font-bold border-b border-gray-200 dark:border-gray-700">
                            <th class="py-3.5 px-4">ردیف</th>
                            <th class="py-3.5 px-4">شناسه / شماره فاکتور</th>
                            <th class="py-3.5 px-4">انبار مستقر</th>
                            <th class="py-3.5 px-4">تاریخ خرید / ورود</th>
                            <th class="py-3.5 px-4">تعداد اولیه</th>
                            <th class="py-3.5 px-4 bg-indigo-50/50 dark:bg-indigo-950/20 text-indigo-900 dark:text-indigo-300">
                                موجودی فعلی در انبار
                            </th>
                            <th class="py-3.5 px-4">مصرف‌شده</th>
                            <th class="py-3.5 px-4">فی خرید پارت (ریال)</th>
                            <th class="py-3.5 px-4">بهای خرید باقیمانده</th>
                            <th class="py-3.5 px-4">ارزش روز باقیمانده</th>
                            <th class="py-3.5 px-4">حاشیه سود پارت</th>
                            <th class="py-3.5 px-4">وضعیت پارت</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-sans">
                        @foreach($item->batches as $index => $batch)
                            @php
                                $isExhausted = $batch->remaining_quantity <= 0;
                                $batchCost = $batch->remaining_quantity * $batch->unit_purchase_price;
                                $batchMarket = $batch->remaining_quantity * ($item->current_price ?? 0);
                                $batchProfit = $batchMarket - $batchCost;
                                $batchProfitPct = $batchCost > 0 ? ($batchProfit / $batchCost) * 100 : 0;
                            @endphp
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-750 transition-colors {{ $isExhausted ? 'opacity-60 bg-gray-50/40 dark:bg-gray-900/30' : '' }}">
                                <td class="py-3 px-4 text-gray-500 dark:text-gray-400 font-bold">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-gray-900 dark:text-white dir-ltr text-right">
                                        {{ $batch->batch_code }}
                                    </div>
                                    @if($batch->supplier)
                                        <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                            تأمین‌کننده: {{ $batch->supplier }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $batch->warehouse->color ?? '#4f46e5' }}"></span>
                                        {{ $batch->warehouse->name ?? 'انبار نامشخص' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                                    {{ $batch->purchase_date ?? '—' }}
                                    @if($batch->expiry_date)
                                        <div class="text-[10px] text-amber-600 dark:text-amber-400">
                                            انقضا: {{ $batch->expiry_date }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-gray-700 dark:text-gray-300 font-bold">
                                    {{ number_format($batch->initial_quantity) }} {{ $item->unit }}
                                </td>
                                <td class="py-3 px-4 bg-indigo-50/50 dark:bg-indigo-950/20">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-sm font-bold {{ $batch->remaining_quantity > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400' }}">
                                            {{ number_format($batch->remaining_quantity) }}
                                        </span>
                                        <span class="text-[10px] text-gray-500">{{ $item->unit }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-gray-500 dark:text-gray-400">
                                    {{ number_format($batch->initial_quantity - $batch->remaining_quantity) }} {{ $item->unit }}
                                </td>
                                <td class="py-3 px-4 text-gray-900 dark:text-white font-bold">
                                    {{ number_format($batch->unit_purchase_price) }}
                                </td>
                                <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                                    {{ number_format($batchCost) }}
                                </td>
                                <td class="py-3 px-4 text-teal-600 dark:text-teal-400 font-bold">
                                    {{ number_format($batchMarket) }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($batch->remaining_quantity > 0)
                                        <div class="font-bold {{ $batchProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                            {{ ($batchProfit >= 0 ? '+' : '') . number_format($batchProfit) }}
                                        </div>
                                        <div class="text-[10px] {{ $batchProfitPct >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                            ({{ number_format($batchProfitPct, 1) }}%)
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-[11px]">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($batch->remaining_quantity > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            فعال در انبار
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                            مختوم / تخلیه‌شده
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Bottom Grid: Stock Per Warehouse & Technical Specifications --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Stock Per Warehouse --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/60">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-teal-600"></span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                        موجودی تفکیکی در انبارها و شعب
                    </h3>
                </div>
            </div>

            @if($item->stocks->isEmpty())
                <p class="text-xs text-gray-500 dark:text-gray-400 py-4 text-center">
                    هیچ انباری به عنوان استقرار این کالا ثبت نشده است.
                </p>
            @else
                <div class="space-y-3 font-sans">
                    @foreach($item->stocks as $stock)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/50">
                            <div class="flex items-center gap-3">
                                <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $stock->warehouse->color ?? '#4f46e5' }}"></span>
                                <div>
                                    <p class="text-xs font-bold text-gray-900 dark:text-white">
                                        {{ $stock->warehouse->name ?? 'انبار نامشخص' }}
                                        @if($stock->warehouse && $stock->warehouse->is_main)
                                            <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-normal mr-1">(انبار مرکزی)</span>
                                        @endif
                                    </p>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                        موقعیت فیزیکی: {{ $stock->location_in_warehouse ?: ($item->default_location ?: 'تعیین‌نشده') }}
                                    </p>
                                </div>
                            </div>

                            <div class="text-left">
                                <span class="text-base font-bold text-gray-900 dark:text-white">
                                    {{ number_format($stock->quantity) }}
                                </span>
                                <span class="text-xs text-gray-500 mr-1">{{ $item->unit }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Technical Specifications Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/60">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                        شناسنامه و ویژگی‌های فنی کالا
                    </h3>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-xs font-sans">
                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/50">
                    <span class="text-gray-500 dark:text-gray-400 block mb-1">برند / سازنده:</span>
                    <strong class="text-gray-900 dark:text-white">
                        {{ $item->brand ?: '—' }}
                        @if($item->brand_code)
                            <span class="text-indigo-600 dark:text-indigo-400 text-[11px] dir-ltr inline-block">({{ $item->brand_code }})</span>
                        @endif
                    </strong>
                </div>

                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/50">
                    <span class="text-gray-500 dark:text-gray-400 block mb-1">جنس / نوع / تیپ:</span>
                    <strong class="text-gray-900 dark:text-white">
                        {{ $item->item_type ?: '—' }}
                        @if($item->item_type_code)
                            <span class="text-teal-600 dark:text-teal-400 text-[11px] dir-ltr inline-block">({{ $item->item_type_code }})</span>
                        @endif
                    </strong>
                </div>

                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/50">
                    <span class="text-gray-500 dark:text-gray-400 block mb-1">سایز / رنگ / مشخصه:</span>
                    <strong class="text-gray-900 dark:text-white">{{ $item->model_spec ?: '—' }}</strong>
                </div>

                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/50">
                    <span class="text-gray-500 dark:text-gray-400 block mb-1">واحد اندازه‌گیری:</span>
                    <strong class="text-gray-900 dark:text-white">{{ $item->unit }}</strong>
                </div>

                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/50">
                    <span class="text-gray-500 dark:text-gray-400 block mb-1">موقعیت پیش‌فرض در انبار:</span>
                    <strong class="text-gray-900 dark:text-white">
                        @if($item->defaultWarehouse)
                            {{ $item->defaultWarehouse->name }}
                        @elseif($item->default_location)
                            {{ $item->default_location }}
                        @else
                            —
                        @endif
                    </strong>
                </div>

                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/50">
                    <span class="text-gray-500 dark:text-gray-400 block mb-1">رهگیری انقضا:</span>
                    <strong class="{{ $item->has_expiry ? 'text-amber-600 dark:text-amber-400' : 'text-gray-700 dark:text-gray-300' }}">
                        {{ $item->has_expiry ? 'دارد (دارویی/مصرفی حساس)' : 'ندارد' }}
                    </strong>
                </div>

                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/50">
                    <span class="text-gray-500 dark:text-gray-400 block mb-1">آخرین فی خرید:</span>
                    <strong class="text-gray-900 dark:text-white">
                        {{ $item->last_purchase_price ? number_format($item->last_purchase_price) . ' ریال' : '—' }}
                    </strong>
                </div>
            </div>

            @if($item->description)
                <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/50">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-1.5">یادداشت و مشخصات اختصاصی:</span>
                    <p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed">
                        {{ $item->description }}
                    </p>
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
