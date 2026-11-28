@extends('layouts.user')

@section('title', 'شناسنامه و کاردکس «' . $item->name . '»')

@section('content')
@php
    $fmt = fn($val) => rtrim(rtrim(number_format((float)$val, 2), '0'), '.') ?: '0';
    $createdJalali = $item->created_at
        ? (function_exists('jdate') ? jdate($item->created_at)->format('Y/m/d H:i') : $item->created_at->format('Y-m-d H:i'))
        : '—';
    $profitPct = $item->total_cost_value > 0 ? ($item->unrealized_profit / $item->total_cost_value) * 100 : 0;
    $threshold = (float)($item->reorder_point > 0 ? $item->reorder_point : $item->min_stock);
    $deficitQty = $threshold > 0 ? max(0.0, $threshold - (float)$item->total_stock) : 0.0;
    $today = now()->toDateString();
    $inNinetyDays = now()->addDays(90)->toDateString();
@endphp

<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 font-sans"
     x-data="{
         activeTab: 'batches',
         batchFilter: '{{ $cardexStats['active_batches_count'] > 0 ? 'active' : 'all' }}'
     }">

    {{-- Top Breadcrumb, Title & Action Toolbar --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 border-b border-gray-200 dark:border-gray-800">
        <div class="space-y-2">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300 flex-wrap">
                <a href="{{ route('admin.warehouse.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">داشبورد انبار</a>
                <span>/</span>
                <a href="{{ route('admin.warehouse.items.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">شناسنامه کالاها</a>
                <span>/</span>
                <span class="text-gray-900 dark:text-white truncate max-w-xs sm:max-w-md">{{ $item->name }}</span>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ $item->name }}
                </h1>

                <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-500/30 dir-ltr">
                    {{ $item->code }}
                </span>

                <span class="px-2 py-0.5 rounded-lg text-[11px] font-bold bg-gray-100 dark:bg-gray-700/70 text-slate-700 dark:text-slate-200 dir-ltr">
                    {{ $item->system_code }}
                </span>

                @if(!$item->is_active)
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300 border border-rose-200 dark:border-rose-500/30">
                        غیرفعال
                    </span>
                @endif

                @if($item->is_below_min_stock)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300 border border-rose-200 dark:border-rose-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></span>
                        کسری بحرانی
                    </span>
                @elseif($item->is_below_reorder_point)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">
                        نیازمند سفارش خرید
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                        موجودی مطلوب
                    </span>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-600 dark:text-slate-300">
                @if($item->category)
                    <span>دسته‌بندی: <strong class="text-gray-900 dark:text-white">{{ $item->category->name }}@if($item->subcategory) / {{ $item->subcategory->name }}@endif</strong></span>
                @endif
                @if($item->barcode)
                    <span>بارکد: <strong class="text-gray-900 dark:text-white dir-ltr inline-block">{{ $item->barcode }}</strong></span>
                @endif
                <span>ثبت‌کننده: <strong class="text-gray-900 dark:text-white">{{ $item->creator_name }}</strong></span>
                <span>زمان ثبت: <strong class="text-gray-900 dark:text-white dir-ltr inline-block">{{ $createdJalali }}</strong></span>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <a href="{{ route('admin.warehouse.items.show-pdf', $item) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all shadow-xs">
                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                کاردکس PDF
            </a>

            @if($item->total_stock > 0)
                <a href="{{ route('admin.warehouse.consumptions.create', ['item_id' => $item->id]) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/15 hover:bg-rose-100 dark:hover:bg-rose-500/25 text-rose-700 dark:text-rose-300 text-xs font-bold transition-all shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    ثبت خروج / مصرف
                </a>
            @endif

            <a href="{{ route('admin.warehouse.transfers.create', ['item_id' => $item->id]) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-teal-200 dark:border-teal-500/30 bg-teal-50 dark:bg-teal-500/15 hover:bg-teal-100 dark:hover:bg-teal-500/25 text-teal-700 dark:text-teal-300 text-xs font-bold transition-all shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                انتقال بین انبارها
            </a>

            <a href="{{ route('admin.warehouse.purchases.create', array_filter(['item_id' => $item->id, 'suggested_qty' => $deficitQty > 0 ? $deficitQty : null])) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-indigo-200 dark:border-indigo-500/30 bg-indigo-50 dark:bg-indigo-500/15 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 text-xs font-bold transition-all shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                ثبت خرید
            </a>

            <a href="{{ route('admin.warehouse.items.edit', $item) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                ویرایش کالا
            </a>
        </div>
    </div>

    @include('warehouse::partials.flash_messages')

    {{-- Conditional Operational Alerts Strip --}}
    @if($deficitQty > 0 || $cardexStats['expired_qty'] > 0 || $cardexStats['expiring_soon_qty'] > 0)
        <div class="rounded-2xl border {{ ($item->is_below_min_stock || $cardexStats['expired_qty'] > 0) ? 'border-rose-200 dark:border-rose-500/30 bg-rose-50/70 dark:bg-rose-500/10' : 'border-amber-200 dark:border-amber-500/30 bg-amber-50/70 dark:bg-amber-500/10' }} p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <div class="flex flex-wrap items-center gap-3 font-bold {{ ($item->is_below_min_stock || $cardexStats['expired_qty'] > 0) ? 'text-rose-700 dark:text-rose-300' : 'text-amber-800 dark:text-amber-300' }}">
                @if($deficitQty > 0)
                    <span>
                        کسری تا نقطه سفارش: {{ $fmt($deficitQty) }} {{ $item->unit }}
                    </span>
                @endif
                @if($cardexStats['expired_qty'] > 0)
                    <span>
                        · {{ $fmt($cardexStats['expired_qty']) }} {{ $item->unit }} دارای تاریخ انقضای گذشته
                    </span>
                @endif
                @if($cardexStats['expiring_soon_qty'] > 0)
                    <span>
                        · {{ $fmt($cardexStats['expiring_soon_qty']) }} {{ $item->unit }} در آستانه انقضا (کمتر از ۹۰ روز)
                    </span>
                @endif
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('admin.warehouse.reorder-alerts.index', ['search' => $item->code, 'scope' => 'all']) }}"
                   class="px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-slate-700 dark:text-slate-200 font-bold hover:border-indigo-400 transition-colors">
                    مشاهده در هشدار سفارش
                </a>
                @if($deficitQty > 0)
                    <a href="{{ route('admin.warehouse.purchases.create', ['item_id' => $item->id, 'suggested_qty' => $deficitQty]) }}"
                       class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold transition-colors">
                        سفارش {{ $fmt($deficitQty) }} {{ $item->unit }}
                    </a>
                @endif
            </div>
        </div>
    @endif

    {{-- 5 Key KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-4 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">موجودی کل کلینیک</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ $fmt($item->total_stock) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">{{ $item->unit }}</span>
                </div>
                <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 pt-1.5 border-t border-gray-100 dark:border-gray-700/60">
                    مرکزی: <strong class="text-gray-900 dark:text-white">{{ $fmt($cardexStats['central_stock']) }}</strong>
                    · انبارک‌ها: <strong class="text-gray-900 dark:text-white">{{ $fmt($cardexStats['sub_warehouses_stock']) }}</strong>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-4 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">آستانه‌های کنترلی</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-300 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ $fmt($item->reorder_point) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">نقطه سفارش</span>
                </div>
                <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 pt-1.5 border-t border-gray-100 dark:border-gray-700/60">
                    حداقل ایمنی: <strong class="text-gray-900 dark:text-white">{{ $fmt($item->min_stock) }}</strong> {{ $item->unit }}
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-4 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">بهای تمام‌شده موجودی</span>
                <span class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-300 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ number_format($item->total_cost_value) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">ریال</span>
                </div>
                <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 pt-1.5 border-t border-gray-100 dark:border-gray-700/60">
                    میانگین خرید: <strong class="text-gray-900 dark:text-white">{{ number_format($item->average_purchase_price) }}</strong> ریال
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-4 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">ارزش روز و بازدهی</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-300 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                    {{ number_format($item->total_market_value) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">ریال</span>
                </div>
                <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 pt-1.5 border-t border-gray-100 dark:border-gray-700/60">
                    نرخ روز: <strong class="text-gray-900 dark:text-white">{{ number_format($item->current_price ?? 0) }}</strong>
                    · سود: <strong class="{{ $profitPct >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ number_format($profitPct, 1) }}%</strong>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-4 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">پارت‌ها و انقضا</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-2">
                <div class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ number_format($cardexStats['active_batches_count']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">پارت فعال</span>
                </div>
                <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 pt-1.5 border-t border-gray-100 dark:border-gray-700/60">
                    @if($cardexStats['nearest_expiry'])
                        نزدیک‌ترین انقضا:
                        <strong class="dir-ltr inline-block {{ $cardexStats['expired_qty'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ function_exists('jdate') ? jdate($cardexStats['nearest_expiry'])->format('Y/m/d') : $cardexStats['nearest_expiry']->format('Y-m-d') }}
                        </strong>
                    @else
                        تخلیه‌شده: <strong class="text-gray-900 dark:text-white">{{ number_format($cardexStats['exhausted_batches_count']) }}</strong> پارت
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Live Distribution Pills Bar Across Warehouses --}}
    @if($warehouseDistribution->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-4 shadow-xs flex flex-wrap items-center gap-3">
            <span class="text-xs font-bold text-slate-700 dark:text-slate-200 shrink-0">توزیع فعلی در انبارها:</span>
            <div class="flex flex-wrap items-center gap-2 flex-1">
                @foreach($warehouseDistribution as $dist)
                    @php
                        $w = $dist['warehouse'];
                        $wColor = $w?->effective_color ?? '#4f46e5';
                    @endphp
                    <a href="{{ route('admin.warehouse.stock-levels.index', ['warehouse_id' => $dist['warehouse_id'], 'search' => $item->code]) }}"
                       class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-700 hover:border-indigo-400 dark:hover:border-indigo-500/50 text-xs transition-colors">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $wColor }}"></span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">{{ $w?->name ?? 'انبار' }}</span>
                        <span class="px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 font-bold">
                            {{ $fmt($dist['quantity']) }} {{ $item->unit }}
                        </span>
                        <span class="text-[10px] text-slate-600 dark:text-slate-300">({{ $dist['share_percent'] }}%)</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Interactive Cardex Navigation Tabs --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 shadow-xs overflow-hidden">
        <div class="p-3 sm:px-5 border-b border-gray-200 dark:border-gray-700/80 flex flex-wrap items-center justify-between gap-3 bg-gray-50/70 dark:bg-gray-900/40">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button"
                        @click="activeTab = 'batches'"
                        :class="activeTab === 'batches'
                            ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs'
                            : 'bg-white dark:bg-gray-800 text-slate-700 dark:text-slate-200 border-gray-200 dark:border-gray-700 hover:border-indigo-400'"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold border transition-all">
                    <span>پارت‌های ورود و موجودی</span>
                    <span class="px-1.5 py-0.5 rounded-md text-[11px]"
                          :class="activeTab === 'batches' ? 'bg-indigo-500/40 text-white' : 'bg-gray-100 dark:bg-gray-700 text-slate-700 dark:text-slate-200'">
                        {{ $item->batches->count() }}
                    </span>
                </button>

                <button type="button"
                        @click="activeTab = 'transfers'"
                        :class="activeTab === 'transfers'
                            ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs'
                            : 'bg-white dark:bg-gray-800 text-slate-700 dark:text-slate-200 border-gray-200 dark:border-gray-700 hover:border-indigo-400'"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold border transition-all">
                    <span>تاریخچه انتقال بین انبارها</span>
                    <span class="px-1.5 py-0.5 rounded-md text-[11px]"
                          :class="activeTab === 'transfers' ? 'bg-indigo-500/40 text-white' : 'bg-gray-100 dark:bg-gray-700 text-slate-700 dark:text-slate-200'">
                        {{ $transferItems->count() }}
                    </span>
                </button>

                <button type="button"
                        @click="activeTab = 'consumptions'"
                        :class="activeTab === 'consumptions'
                            ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs'
                            : 'bg-white dark:bg-gray-800 text-slate-700 dark:text-slate-200 border-gray-200 dark:border-gray-700 hover:border-indigo-400'"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold border transition-all">
                    <span>خروج و مصرف (بیمار و بخش)</span>
                    <span class="px-1.5 py-0.5 rounded-md text-[11px]"
                          :class="activeTab === 'consumptions' ? 'bg-indigo-500/40 text-white' : 'bg-gray-100 dark:bg-gray-700 text-slate-700 dark:text-slate-200'">
                        {{ $issueItems->count() }}
                    </span>
                </button>

                <button type="button"
                        @click="activeTab = 'specs'"
                        :class="activeTab === 'specs'
                            ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs'
                            : 'bg-white dark:bg-gray-800 text-slate-700 dark:text-slate-200 border-gray-200 dark:border-gray-700 hover:border-indigo-400'"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold border transition-all">
                    <span>توزیع انبارها و مشخصات فنی</span>
                </button>
            </div>

            {{-- Batch Sub-Filter (only visible when Batches tab is active) --}}
            <div x-show="activeTab === 'batches' && {{ $item->batches->count() }} > 0"
                 class="flex items-center gap-1.5 text-[11px] font-bold">
                <button type="button"
                        @click="batchFilter = 'active'"
                        :class="batchFilter === 'active'
                            ? 'bg-indigo-50 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-500/40'
                            : 'bg-white dark:bg-gray-800 text-slate-600 dark:text-slate-300 border-gray-200 dark:border-gray-700'"
                        class="px-2.5 py-1 rounded-lg border transition-colors">
                    فعال ({{ $cardexStats['active_batches_count'] }})
                </button>
                <button type="button"
                        @click="batchFilter = 'exhausted'"
                        :class="batchFilter === 'exhausted'
                            ? 'bg-indigo-50 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-500/40'
                            : 'bg-white dark:bg-gray-800 text-slate-600 dark:text-slate-300 border-gray-200 dark:border-gray-700'"
                        class="px-2.5 py-1 rounded-lg border transition-colors">
                    تخلیه‌شده ({{ $cardexStats['exhausted_batches_count'] }})
                </button>
                <button type="button"
                        @click="batchFilter = 'all'"
                        :class="batchFilter === 'all'
                            ? 'bg-indigo-50 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-500/40'
                            : 'bg-white dark:bg-gray-800 text-slate-600 dark:text-slate-300 border-gray-200 dark:border-gray-700'"
                        class="px-2.5 py-1 rounded-lg border transition-colors">
                    همه ({{ $item->batches->count() }})
                </button>
            </div>
        </div>

        {{-- TAB 1: Batches Table (8 clean grouped columns, zero horizontal bloat) --}}
        <div x-show="activeTab === 'batches'">
            @if($item->batches->isEmpty())
                <div class="p-12 text-center space-y-2">
                    <p class="text-sm font-bold text-gray-900 dark:text-white">هیچ پارت خریدی برای این کالا ثبت نشده است.</p>
                    <p class="text-xs text-slate-600 dark:text-slate-300">با ثبت رسید خرید یا انتقال بین انبارها، پارت‌های کالا همراه با نرخ خرید در این کاردکس ثبت می‌شوند.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900/60 text-slate-600 dark:text-slate-300 font-bold border-b border-gray-200 dark:border-gray-700">
                                <th class="py-3 px-4"># / کد پارت و مرجع</th>
                                <th class="py-3 px-4">انبار مستقر</th>
                                <th class="py-3 px-4">تاریخ ورود / انقضا</th>
                                <th class="py-3 px-4 text-center">موجودی (باقیمانده از اولیه)</th>
                                <th class="py-3 px-4">فی خرید واحد</th>
                                <th class="py-3 px-4">ارزش باقیمانده (خرید / روز)</th>
                                <th class="py-3 px-4 text-center">بازدهی / وضعیت</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-sans">
                            @foreach($item->batches as $index => $batch)
                                @php
                                    $isExhausted = (float)$batch->remaining_quantity <= 0;
                                    $batchCost = (float)$batch->remaining_quantity * (float)$batch->unit_purchase_price;
                                    $batchMarket = (float)$batch->remaining_quantity * (float)($item->current_price ?? 0);
                                    $batchProfit = $batchMarket - $batchCost;
                                    $batchProfitPct = $batchCost > 0 ? ($batchProfit / $batchCost) * 100 : 0;
                                    $remRatio = (float)$batch->initial_quantity > 0 ? min(100, round(((float)$batch->remaining_quantity / (float)$batch->initial_quantity) * 100)) : 0;
                                    $expStr = $batch->expiry_date ? $batch->expiry_date->toDateString() : null;
                                    $isExpired = $expStr && $expStr < $today && !$isExhausted;
                                    $isExpiringSoon = $expStr && !$isExpired && $expStr <= $inNinetyDays && !$isExhausted;
                                @endphp
                                <tr x-show="batchFilter === 'all' || (batchFilter === 'active' && {{ !$isExhausted ? 'true' : 'false' }}) || (batchFilter === 'exhausted' && {{ $isExhausted ? 'true' : 'false' }})"
                                    class="hover:bg-gray-50/70 dark:hover:bg-gray-900/40 transition-colors {{ $isExhausted ? 'opacity-65' : '' }}">
                                    <td class="py-3.5 px-4 align-top space-y-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">{{ $index + 1 }}.</span>
                                            @if($batch->purchase_receipt_id)
                                                <a href="{{ route('admin.warehouse.purchases.show', $batch->purchase_receipt_id) }}"
                                                   class="font-bold text-indigo-600 dark:text-indigo-300 hover:underline dir-ltr inline-block">
                                                    {{ $batch->batch_code }}
                                                </a>
                                            @else
                                                <span class="font-bold text-gray-900 dark:text-white dir-ltr inline-block">{{ $batch->batch_code }}</span>
                                            @endif
                                        </div>
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] text-slate-600 dark:text-slate-300">
                                            @if($batch->supplier)
                                                <span>تأمین: {{ $batch->supplier }}</span>
                                            @endif
                                            @if($batch->transfer_request_id)
                                                <a href="{{ route('admin.warehouse.transfers.show', $batch->transfer_request_id) }}"
                                                   class="font-bold text-teal-600 dark:text-teal-300 hover:underline">
                                                    حواله انتقال
                                                </a>
                                            @endif
                                            <span>ثبت: {{ $batch->creator_name }}</span>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 align-top">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-gray-100 dark:bg-gray-700/70 text-gray-900 dark:text-white">
                                            <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $batch->warehouse?->effective_color ?? '#4f46e5' }}"></span>
                                            {{ $batch->warehouse?->name ?? 'انبار نامشخص' }}
                                        </span>
                                    </td>

                                    <td class="py-3.5 px-4 align-top space-y-1">
                                        <div class="font-bold text-gray-900 dark:text-white dir-ltr text-right">
                                            {{ $batch->purchase_date ? (function_exists('jdate') ? jdate($batch->purchase_date)->format('Y/m/d') : $batch->purchase_date->format('Y-m-d')) : '—' }}
                                        </div>
                                        @if($batch->expiry_date)
                                            <div class="text-[11px] font-bold dir-ltr text-right {{ $isExpired ? 'text-rose-600 dark:text-rose-400' : ($isExpiringSoon ? 'text-amber-600 dark:text-amber-400' : 'text-slate-600 dark:text-slate-300') }}">
                                                انقضا: {{ function_exists('jdate') ? jdate($batch->expiry_date)->format('Y/m/d') : $batch->expiry_date->format('Y-m-d') }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="py-3.5 px-4 align-top text-center">
                                        <div class="font-bold text-sm {{ !$isExhausted ? 'text-indigo-600 dark:text-indigo-300' : 'text-slate-600 dark:text-slate-300' }}">
                                            {{ $fmt($batch->remaining_quantity) }}
                                            <span class="text-[11px] font-normal text-slate-600 dark:text-slate-300">/ {{ $fmt($batch->initial_quantity) }} {{ $item->unit }}</span>
                                        </div>
                                        <div class="w-24 mx-auto h-1.5 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden mt-1.5">
                                            <div class="h-full rounded-full {{ !$isExhausted ? 'bg-indigo-600 dark:bg-indigo-400' : 'bg-gray-400' }}"
                                                 style="width: {{ $remRatio }}%"></div>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 align-top font-bold text-gray-900 dark:text-white">
                                        {{ number_format($batch->unit_purchase_price) }} <span class="text-[10px] font-normal text-slate-600 dark:text-slate-300">ریال</span>
                                    </td>

                                    <td class="py-3.5 px-4 align-top space-y-0.5">
                                        <div class="font-bold text-gray-900 dark:text-white">
                                            خرید: {{ number_format($batchCost) }} ریال
                                        </div>
                                        @if(!$isExhausted && (float)($item->current_price ?? 0) > 0)
                                            <div class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                                روز: {{ number_format($batchMarket) }} ریال
                                            </div>
                                        @endif
                                    </td>

                                    <td class="py-3.5 px-4 align-top text-center space-y-1">
                                        @if(!$isExhausted)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300 border border-emerald-200/70 dark:border-emerald-500/30">
                                                فعال
                                            </span>
                                            @if($isExpired)
                                                <div class="text-[10px] font-bold text-rose-600 dark:text-rose-400">منقضی‌شده</div>
                                            @elseif($isExpiringSoon)
                                                <div class="text-[10px] font-bold text-amber-600 dark:text-amber-400">انقضای نزدیک</div>
                                            @elseif((float)($item->current_price ?? 0) > 0)
                                                <div class="text-[11px] font-bold {{ $batchProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                                    {{ ($batchProfitPct >= 0 ? '+' : '') . number_format($batchProfitPct, 1) }}%
                                                </div>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-gray-100 text-slate-600 dark:bg-gray-700 dark:text-slate-300">
                                                تخلیه‌شده
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

        {{-- TAB 2: Transfers Between Warehouses --}}
        <div x-show="activeTab === 'transfers'" x-cloak>
            @if($transferItems->isEmpty())
                <div class="p-10 text-center space-y-2 text-xs text-slate-600 dark:text-slate-300">
                    <p class="text-sm font-bold text-gray-900 dark:text-white">هنوز هیچ درخواست یا انتقال بین انبارهایی برای این کالا ثبت نشده است.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs font-sans">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900/60 text-slate-600 dark:text-slate-300 font-bold border-b border-gray-200 dark:border-gray-700">
                                <th class="py-3 px-4">شماره حواله / درخواست</th>
                                <th class="py-3 px-4">تاریخ ثبت / نیاز</th>
                                <th class="py-3 px-4">مسیر (مبدأ ← مقصد)</th>
                                <th class="py-3 px-4">درخواست‌کننده و ثبت‌کننده</th>
                                <th class="py-3 px-4 text-center">درخواستی / تحویلی</th>
                                <th class="py-3 px-4 text-center">بهای تمام‌شده (ریال)</th>
                                <th class="py-3 px-4 text-center">وضعیت</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            @foreach($transferItems as $tLine)
                                @php
                                    $tr = $tLine->transferRequest;
                                    $trDate = $tr?->request_date ? (function_exists('jdate') ? jdate($tr->request_date)->format('Y/m/d') : $tr->request_date->format('Y-m-d')) : '—';
                                    $neededDate = $tr?->needed_date ? (function_exists('jdate') ? jdate($tr->needed_date)->format('Y/m/d') : $tr->needed_date->format('Y-m-d')) : null;
                                @endphp
                                <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-900/40">
                                    <td class="py-3 px-4">
                                        @if($tr)
                                            <a href="{{ route('admin.warehouse.transfers.show', $tr) }}"
                                               class="font-bold text-indigo-600 dark:text-indigo-300 hover:underline dir-ltr inline-block">
                                                {{ $tr->transfer_number }}
                                            </a>
                                            @if($tr->request_number)
                                                <div class="text-[10px] text-slate-600 dark:text-slate-300 dir-ltr text-right">{{ $tr->request_number }}</div>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="dir-ltr text-right font-bold text-gray-900 dark:text-white">{{ $trDate }}</div>
                                        @if($neededDate)
                                            <div class="text-[10px] text-indigo-600 dark:text-indigo-300 dir-ltr text-right">نیاز: {{ $neededDate }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-bold text-gray-800 dark:text-gray-200">{{ $tr?->sourceWarehouse?->name ?? '—' }}</span>
                                        <span class="text-indigo-500 mx-1">←</span>
                                        <span class="font-bold text-indigo-600 dark:text-indigo-300">{{ $tr?->destinationWarehouse?->name ?? '—' }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                                        <div>درخواست‌کننده: <strong class="text-gray-900 dark:text-white">{{ $tr?->effective_requester_name ?? '—' }}</strong></div>
                                        <div class="text-[10px]">ثبت‌کننده: {{ $tr?->creator_name ?? '—' }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold">
                                        <span class="text-gray-900 dark:text-white">{{ $fmt($tLine->requested_quantity) }}</span>
                                        /
                                        <span class="text-emerald-600 dark:text-emerald-400">{{ $fmt($tLine->delivered_quantity) }}</span>
                                        <span class="text-[10px] font-normal text-slate-600 dark:text-slate-300">{{ $item->unit }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-gray-900 dark:text-white">
                                        {{ number_format((float)$tLine->total_cost_value) }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-lg text-[11px] font-bold {{ $tr?->status === 'delivered' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' }}">
                                            {{ $tr?->status_label ?? '—' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- TAB 3: Consumptions & Goods Issue History --}}
        <div x-show="activeTab === 'consumptions'" x-cloak>
            @if($issueItems->isEmpty())
                <div class="p-10 text-center space-y-2 text-xs text-slate-600 dark:text-slate-300">
                    <p class="text-sm font-bold text-gray-900 dark:text-white">هنوز هیچ خروجی یا مصرفی برای این کالا ثبت نشده است.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs font-sans">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900/60 text-slate-600 dark:text-slate-300 font-bold border-b border-gray-200 dark:border-gray-700">
                                <th class="py-3 px-4">شماره سند</th>
                                <th class="py-3 px-4">تاریخ مصرف</th>
                                <th class="py-3 px-4">انبار / انبارک مبدأ</th>
                                <th class="py-3 px-4">بیمار / شماره پرونده / بخش</th>
                                <th class="py-3 px-4 text-center">تعداد کسرشده</th>
                                <th class="py-3 px-4 text-center">بهای تمام‌شده (ریال)</th>
                                <th class="py-3 px-4">ثبت‌کننده</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            @foreach($issueItems as $issLine)
                                @php
                                    $issDoc = $issLine->document;
                                    $issDate = $issDoc?->issue_date ? (function_exists('jdate') ? jdate($issDoc->issue_date)->format('Y/m/d') : $issDoc->issue_date->format('Y-m-d')) : '—';
                                @endphp
                                <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-900/40">
                                    <td class="py-3 px-4">
                                        @if($issDoc)
                                            <a href="{{ route('admin.warehouse.consumptions.show', $issDoc) }}"
                                               class="font-bold text-indigo-600 dark:text-indigo-300 hover:underline dir-ltr inline-block">
                                                {{ $issDoc->issue_number }}
                                            </a>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 dir-ltr text-right font-bold text-gray-900 dark:text-white">{{ $issDate }}</td>
                                    <td class="py-3 px-4 font-bold text-gray-800 dark:text-gray-200">{{ $issLine->warehouse?->name ?? '—' }}</td>
                                    <td class="py-3 px-4">
                                        <span class="font-bold text-gray-900 dark:text-white">
                                            {{ $issDoc?->effective_patient_name ?: ($issDoc?->effective_consumer_name ?: $issDoc?->issue_type_label) }}
                                        </span>
                                        @if($issDoc?->effective_case_number)
                                            <span class="text-[11px] text-indigo-600 dark:text-indigo-300 mr-1">({{ $issDoc->effective_case_number }})</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-rose-600 dark:text-rose-400">
                                        {{ $fmt($issLine->quantity) }} {{ $item->unit }}
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-gray-900 dark:text-white">
                                        {{ number_format((float)$issLine->total_cost) }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 dark:text-slate-300">{{ $issDoc?->creator_name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- TAB 4: Warehouse Distribution + Technical Specifications --}}
        <div x-show="activeTab === 'specs'" x-cloak class="p-5 sm:p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Live Per-Warehouse Breakdown --}}
                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white pb-2 border-b border-gray-100 dark:border-gray-700/60">
                        تفکیک موجودی و ارزش در انبارها و انبارک‌ها
                    </h3>

                    @if($warehouseDistribution->isEmpty())
                        <p class="text-xs text-slate-600 dark:text-slate-300 py-4 text-center">
                            در حال حاضر موجودی فعال برای این کالا در هیچ انباری ثبت نشده است.
                        </p>
                    @else
                        <div class="space-y-3">
                            @foreach($warehouseDistribution as $dist)
                                @php
                                    $wh = $dist['warehouse'];
                                @endphp
                                <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200/80 dark:border-gray-700/60 space-y-2">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $wh?->effective_color ?? '#4f46e5' }}"></span>
                                            <div class="min-w-0">
                                                <div class="text-xs font-bold text-gray-900 dark:text-white truncate">
                                                    {{ $wh?->name ?? 'انبار' }}
                                                    @if($wh?->is_main)
                                                        <span class="text-[10px] text-indigo-600 dark:text-indigo-300 mr-1">(انبار مرکزی)</span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-slate-600 dark:text-slate-300">
                                                    {{ $dist['batches_count'] }} پارت فعال · ارزش خرید: {{ number_format($dist['cost_value']) }} ریال
                                                    @if($dist['location']) · مکان: {{ $dist['location'] }}@endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-left shrink-0">
                                            <span class="text-sm font-bold text-indigo-600 dark:text-indigo-300">
                                                {{ $fmt($dist['quantity']) }}
                                            </span>
                                            <span class="text-[11px] text-slate-600 dark:text-slate-300">{{ $item->unit }}</span>
                                        </div>
                                    </div>

                                    <div class="w-full h-1.5 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                        <div class="h-full rounded-full bg-indigo-600 dark:bg-indigo-400" style="width: {{ min(100, $dist['share_percent']) }}%"></div>
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-0.5 text-[11px] font-bold">
                                        <a href="{{ route('admin.warehouse.consumptions.create', ['item_id' => $item->id, 'warehouse_id' => $dist['warehouse_id']]) }}"
                                           class="text-rose-600 dark:text-rose-300 hover:underline">
                                            ثبت مصرف از این انبار
                                        </a>
                                        @if($wh && !$wh->is_main)
                                            <span class="text-slate-400">·</span>
                                            <a href="{{ route('admin.warehouse.transfers.create', ['item_id' => $item->id, 'destination_warehouse_id' => $dist['warehouse_id']]) }}"
                                               class="text-teal-600 dark:text-teal-300 hover:underline">
                                                درخواست شارژ به این انبارک
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Technical Specifications --}}
                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white pb-2 border-b border-gray-100 dark:border-gray-700/60">
                        شناسنامه و ویژگی‌های فنی کالا
                    </h3>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/50">
                            <span class="text-slate-600 dark:text-slate-300 block mb-1">برند / سازنده:</span>
                            <strong class="text-gray-900 dark:text-white">
                                {{ $item->brand ?: '—' }}
                                @if($item->brand_code)
                                    <span class="text-indigo-600 dark:text-indigo-300 text-[11px] dir-ltr inline-block">({{ $item->brand_code }})</span>
                                @endif
                            </strong>
                        </div>

                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/50">
                            <span class="text-slate-600 dark:text-slate-300 block mb-1">جنس / نوع / تیپ:</span>
                            <strong class="text-gray-900 dark:text-white">
                                {{ $item->item_type ?: '—' }}
                                @if($item->item_type_code)
                                    <span class="text-teal-600 dark:text-teal-300 text-[11px] dir-ltr inline-block">({{ $item->item_type_code }})</span>
                                @endif
                            </strong>
                        </div>

                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/50">
                            <span class="text-slate-600 dark:text-slate-300 block mb-1">سایز / رنگ / مشخصه:</span>
                            <strong class="text-gray-900 dark:text-white">{{ $item->model_spec ?: '—' }}</strong>
                        </div>

                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/50">
                            <span class="text-slate-600 dark:text-slate-300 block mb-1">واحد اندازه‌گیری:</span>
                            <strong class="text-gray-900 dark:text-white">{{ $item->unit }}</strong>
                        </div>

                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/50">
                            <span class="text-slate-600 dark:text-slate-300 block mb-1">انبار / موقعیت پیش‌فرض:</span>
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

                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/50">
                            <span class="text-slate-600 dark:text-slate-300 block mb-1">رهگیری انقضا / آخرین فی خرید:</span>
                            <strong class="text-gray-900 dark:text-white">
                                {{ $item->has_expiry ? 'دارد' : 'ندارد' }}
                                · {{ $item->last_purchase_price ? number_format($item->last_purchase_price) . ' ریال' : '—' }}
                            </strong>
                        </div>
                    </div>

                    @if($item->description)
                        <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/50">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300 block mb-1">توضیحات تکمیلی:</span>
                            <p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed">
                                {{ $item->description }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
