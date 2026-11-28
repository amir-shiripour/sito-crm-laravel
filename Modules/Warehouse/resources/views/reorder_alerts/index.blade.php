@extends('layouts.user')

@section('title', 'هشدارهای موجودی و نقطه سفارش')

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 font-sans"
     x-data="reorderAlertsManager({
         csrfToken: '{{ csrf_token() }}'
     })">

    {{-- Top Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">
                <a href="{{ route('admin.warehouse.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">داشبورد انبار</a>
                <span>/</span>
                <a href="{{ route('admin.warehouse.stock-levels.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">موجودی لحظه‌ای انبارها</a>
                <span>/</span>
                <span class="text-gray-900 dark:text-white">هشدار نقطه سفارش</span>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                    هشدارهای موجودی و نقطه سفارش
                </h1>
                @if($mainWarehouse)
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-500/30">
                        مبنای مرکزی: {{ $mainWarehouse->name }}
                    </span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.warehouse.reorder-alerts.pdf', request()->query()) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-all shadow-xs">
                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                گزارش PDF هشدار سفارش
            </a>

            <a href="{{ route('admin.warehouse.stock-levels.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-indigo-200 dark:border-indigo-500/30 bg-indigo-50 dark:bg-indigo-500/15 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 font-bold text-xs transition-all shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                موجودی لحظه‌ای انبارها
            </a>

            <a href="{{ route('admin.warehouse.purchases.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                ثبت رسید خرید جدید
            </a>
        </div>
    </div>

    @include('warehouse::partials.flash_messages')

    {{-- Live Inline Toast Banner --}}
    <div x-show="toastMessage"
         x-transition
         style="display: none;"
         class="rounded-2xl px-4 py-3 text-xs font-bold border flex items-center justify-between"
         :class="toastError
             ? 'bg-rose-50 dark:bg-rose-500/15 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-500/30'
             : 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-500/30'">
        <span x-text="toastMessage"></span>
        <button type="button" @click="toastMessage = ''" class="opacity-70 hover:opacity-100 px-2">×</button>
    </div>

    {{-- Scope Tabs --}}
    @php
        $baseQuery = collect(request()->query())->except(['scope', 'page'])->toArray();
        $tabs = [
            ['key' => 'alerts_only', 'label' => 'همه هشدارها (نیازمند اقدام)', 'count' => $summary['alerts_only_count'], 'dot' => 'bg-indigo-500'],
            ['key' => 'critical', 'label' => 'بحرانی / ناموجود', 'count' => $summary['critical_count'], 'dot' => 'bg-rose-500'],
            ['key' => 'reorder', 'label' => 'نیازمند سفارش', 'count' => $summary['reorder_count'], 'dot' => 'bg-amber-500'],
            ['key' => 'central_low', 'label' => 'کسری انبار مرکزی', 'count' => $summary['central_low_count'], 'dot' => 'bg-sky-500'],
            ['key' => 'expiring', 'label' => 'هشدار انقضا', 'count' => $summary['expiring_count'], 'dot' => 'bg-orange-500'],
            ['key' => 'adequate', 'label' => 'موجودی کافی', 'count' => $summary['adequate_count'], 'dot' => 'bg-emerald-500'],
            ['key' => 'unconfigured', 'label' => 'بدون نقطه سفارش', 'count' => $summary['unconfigured_count'], 'dot' => 'bg-slate-400'],
            ['key' => 'all', 'label' => 'همه کالاها', 'count' => $summary['all_count'], 'dot' => 'bg-gray-500'],
        ];
    @endphp
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-3 shadow-xs">
        <div class="flex items-center gap-2 flex-wrap">
            @foreach($tabs as $tab)
                @php
                    $isActive = $scope === $tab['key'];
                    $tabUrl = route('admin.warehouse.reorder-alerts.index', array_merge($baseQuery, ['scope' => $tab['key']]));
                @endphp
                <a href="{{ $tabUrl }}"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold transition-all border {{ $isActive
                        ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm'
                        : 'bg-gray-50 dark:bg-gray-900/50 text-slate-700 dark:text-slate-200 border-gray-200 dark:border-gray-700 hover:border-indigo-400' }}">
                    <span class="w-2 h-2 rounded-full shrink-0 {{ $isActive ? 'bg-indigo-200' : $tab['dot'] }}"></span>
                    <span>{{ $tab['label'] }}</span>
                    <span class="px-2 py-0.5 rounded-lg text-[11px] font-sans {{ $isActive
                            ? 'bg-indigo-500/40 text-white'
                            : 'bg-white dark:bg-gray-800 text-slate-700 dark:text-slate-200 border border-gray-200 dark:border-gray-700' }}">
                        {{ number_format($tab['count']) }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Live KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold text-slate-600 dark:text-slate-300">بحرانی / زیر حداقل ایمنی</p>
                <h3 class="text-lg font-bold {{ $summary['critical_count'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white' }} mt-1 font-sans">
                    {{ number_format($summary['critical_count']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">قلم</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold text-slate-600 dark:text-slate-300">نیازمند سفارش کلینیک</p>
                <h3 class="text-lg font-bold {{ $summary['reorder_count'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white' }} mt-1 font-sans">
                    {{ number_format($summary['reorder_count']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">قلم</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold text-slate-600 dark:text-slate-300">کسری انبار مرکزی</p>
                <h3 class="text-lg font-bold {{ $summary['central_low_count'] > 0 ? 'text-sky-600 dark:text-sky-400' : 'text-gray-900 dark:text-white' }} mt-1 font-sans">
                    {{ number_format($summary['central_low_count']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">قلم</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" />
                </svg>
            </span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold text-slate-600 dark:text-slate-300">مجموع کسری نیازمند تامین</p>
                <h3 class="text-lg font-bold text-indigo-600 dark:text-indigo-400 mt-1 font-sans">
                    {{ rtrim(rtrim(number_format((float)$summary['total_deficit_units'], 2), '0'), '.') }}
                    <span class="text-xs font-normal text-slate-600 dark:text-slate-300">واحد</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </span>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700/80 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-[11px] font-bold text-slate-600 dark:text-slate-300">برآورد بودجه رفع کسری</p>
                <h3 class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-1 font-sans">
                    {{ number_format($summary['total_estimated_budget']) }} <span class="text-xs font-normal text-slate-600 dark:text-slate-300">ریال</span>
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
        </div>
    </div>

    {{-- Filters Bar --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-4 shadow-xs">
        <form action="{{ route('admin.warehouse.reorder-alerts.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <input type="hidden" name="scope" value="{{ $scope }}">

            <div class="lg:col-span-2">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="جستجو با کد کالا (SKU)، کد سیستمی، نام کالا یا بارکد..."
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

            <div class="flex items-center gap-2">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all">
                    اعمال فیلتر
                </button>
                @if($search || $categoryId || $scope !== 'alerts_only')
                    <a href="{{ route('admin.warehouse.reorder-alerts.index') }}"
                       class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all">
                        بازنشانی
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Alerts Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 shadow-xs overflow-hidden">
        @if($rows->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/60 border-b border-gray-200 dark:border-gray-700 text-[11px] font-bold text-slate-600 dark:text-slate-300">
                            <th class="px-4 py-3.5">کد کالا / نام کالا</th>
                            <th class="px-4 py-3.5 text-center">موجودی کل کلینیک</th>
                            <th class="px-4 py-3.5 text-center">موجودی مرکزی</th>
                            <th class="px-4 py-3.5 text-center">نقطه سفارش / حداقل ایمنی</th>
                            <th class="px-4 py-3.5 text-center">کسری تا نقطه سفارش</th>
                            <th class="px-4 py-3.5 text-center">وضعیت</th>
                            <th class="px-4 py-3.5 text-left">عملیات سریع</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-xs font-sans">
                        @foreach($rows as $row)
                            @php
                                $item = $row['item'];
                                $statusMeta = match ($row['alert_status']) {
                                    'critical' => [
                                        'label' => $row['clinic_stock'] <= 0 ? 'ناموجود / بحرانی' : 'بحرانی (زیر حداقل)',
                                        'classes' => 'bg-rose-50 dark:bg-rose-500/15 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-500/30',
                                    ],
                                    'reorder' => [
                                        'label' => 'نیازمند سفارش',
                                        'classes' => 'bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-500/30',
                                    ],
                                    'central_low' => [
                                        'label' => 'کسری انبار مرکزی',
                                        'classes' => 'bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-500/30',
                                    ],
                                    'adequate' => [
                                        'label' => 'موجودی کافی',
                                        'classes' => 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-500/30',
                                    ],
                                    default => [
                                        'label' => 'تنظیم‌نشده',
                                        'classes' => 'bg-gray-100 dark:bg-gray-700/60 text-slate-600 dark:text-slate-300 border-gray-200 dark:border-gray-600',
                                    ],
                                };
                                $fmt = fn($val) => rtrim(rtrim(number_format((float)$val, 2), '0'), '.') ?: '0';
                            @endphp
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-900/30 transition-colors">
                                {{-- Item Code & Name --}}
                                <td class="px-4 py-3.5 align-top">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 font-bold text-[11px] dir-ltr font-sans border border-indigo-200/70 dark:border-indigo-500/30">
                                                {{ $item->code }}
                                            </span>
                                            <a href="{{ route('admin.warehouse.items.show', $item) }}"
                                               class="font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-300 transition-colors">
                                                {{ $item->name }}
                                            </a>
                                        </div>

                                        <div class="flex items-center gap-2 flex-wrap text-[11px] text-slate-600 dark:text-slate-300">
                                            <span>{{ $item->category?->name ?? 'بدون دسته' }}</span>
                                            @if($item->subcategory)
                                                <span>/ {{ $item->subcategory->name }}</span>
                                            @endif
                                            @if($row['expired_qty'] > 0)
                                                <span class="px-1.5 py-0.5 rounded bg-rose-50 dark:bg-rose-500/15 text-rose-700 dark:text-rose-300 font-bold">
                                                    {{ $fmt($row['expired_qty']) }} {{ $item->unit }} منقضی
                                                </span>
                                            @elseif($row['expiring_soon_qty'] > 0)
                                                <span class="px-1.5 py-0.5 rounded bg-orange-50 dark:bg-orange-500/15 text-orange-700 dark:text-orange-300 font-bold">
                                                    {{ $fmt($row['expiring_soon_qty']) }} {{ $item->unit }} انقضای نزدیک
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Clinic Total Stock --}}
                                <td class="px-4 py-3.5 text-center align-top">
                                    <div class="font-bold text-sm {{ $row['clinic_stock'] <= 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white' }}">
                                        {{ $fmt($row['clinic_stock']) }}
                                        <span class="text-[11px] font-normal text-slate-600 dark:text-slate-300">{{ $item->unit }}</span>
                                    </div>
                                    @if($row['sub_warehouses_stock'] > 0)
                                        <div class="text-[10px] text-slate-600 dark:text-slate-300 mt-0.5">
                                            در انبارک‌ها: {{ $fmt($row['sub_warehouses_stock']) }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Central Warehouse Stock --}}
                                <td class="px-4 py-3.5 text-center align-top">
                                    <div class="font-bold text-sm {{ $row['central_stock'] <= 0 ? 'text-rose-600 dark:text-rose-400' : ($row['central_deficit'] > 0 ? 'text-sky-600 dark:text-sky-400' : 'text-gray-800 dark:text-gray-200') }}">
                                        {{ $fmt($row['central_stock']) }}
                                        <span class="text-[11px] font-normal text-slate-600 dark:text-slate-300">{{ $item->unit }}</span>
                                    </div>
                                    @if($row['central_deficit'] > 0 && $row['alert_status'] === 'central_low')
                                        <div class="text-[10px] font-bold text-sky-600 dark:text-sky-300 mt-0.5">
                                            کمبود مرکزی: {{ $fmt($row['central_deficit']) }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Threshold / Inline Quick Edit --}}
                                <td class="px-4 py-3.5 text-center align-top">
                                    <div x-show="editingId !== {{ $item->id }}" class="space-y-1">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span class="font-bold text-gray-900 dark:text-white">
                                                {{ $fmt($row['reorder_point']) }}
                                            </span>
                                            <span class="text-[11px] text-slate-600 dark:text-slate-300">
                                                (حداقل: {{ $fmt($row['min_stock']) }})
                                            </span>
                                            <button type="button"
                                                    @click="startEdit({{ $item->id }}, {{ (float)$row['min_stock'] }}, {{ (float)$row['reorder_point'] }}, '{{ route('admin.warehouse.reorder-alerts.update-thresholds', $item) }}')"
                                                    title="ویرایش سریع نقطه سفارش و حداقل ایمنی"
                                                    class="p-1 rounded-lg text-indigo-600 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-500/15 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Inline Edit Form --}}
                                    <div x-show="editingId === {{ $item->id }}"
                                         style="display: none;"
                                         class="bg-gray-50 dark:bg-gray-900/70 rounded-xl p-2 border border-indigo-200 dark:border-indigo-500/40 space-y-2 text-right">
                                        <div class="grid grid-cols-2 gap-1.5">
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-300 mb-0.5">نقطه سفارش</label>
                                                <input type="number"
                                                       min="0"
                                                       step="1"
                                                       x-model="editReorder"
                                                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1 text-xs font-sans">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-300 mb-0.5">حداقل ایمنی</label>
                                                <input type="number"
                                                       min="0"
                                                       step="1"
                                                       x-model="editMinStock"
                                                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1 text-xs font-sans">
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button"
                                                    @click="cancelEdit()"
                                                    class="px-2 py-1 rounded-lg text-[10px] font-bold bg-gray-200 dark:bg-gray-700 text-slate-700 dark:text-slate-200">
                                                انصراف
                                            </button>
                                            <button type="button"
                                                    @click="saveThresholds()"
                                                    :disabled="saving"
                                                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-indigo-600 hover:bg-indigo-700 text-white disabled:opacity-50">
                                                <span x-show="!saving">ذخیره</span>
                                                <span x-show="saving">...</span>
                                            </button>
                                        </div>
                                    </div>
                                </td>

                                {{-- Deficit & Estimated Cost --}}
                                <td class="px-4 py-3.5 text-center align-top">
                                    @if($row['suggested_order_qty'] > 0)
                                        <div class="font-bold text-sm {{ $row['clinic_deficit'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-sky-600 dark:text-sky-400' }}">
                                            {{ $fmt($row['suggested_order_qty']) }}
                                            <span class="text-[11px] font-normal">{{ $item->unit }}</span>
                                        </div>
                                        @if($row['estimated_order_cost'] > 0)
                                            <div class="text-[10px] text-slate-600 dark:text-slate-300 mt-0.5">
                                                ~ {{ number_format($row['estimated_order_cost']) }} ریال
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-slate-600 dark:text-slate-300">—</span>
                                    @endif
                                </td>

                                {{-- Status Badge --}}
                                <td class="px-4 py-3.5 text-center align-top">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $statusMeta['classes'] }}">
                                        {{ $statusMeta['label'] }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-3.5 text-left align-top">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($row['suggested_order_qty'] > 0)
                                            <a href="{{ route('admin.warehouse.purchases.create', ['item_id' => $item->id, 'suggested_qty' => $row['suggested_order_qty']]) }}"
                                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold transition-colors shadow-xs">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                ثبت خرید
                                            </a>
                                        @endif
                                        <a href="{{ route('admin.warehouse.items.show', $item) }}"
                                           class="inline-flex items-center px-2.5 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-200 text-[11px] font-bold transition-colors">
                                            شناسنامه
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700/60">
                {{ $rows->links() }}
            </div>
        @else
            <div class="p-10 text-center space-y-2">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-300 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                    موردی با فیلتر انتخاب‌شده یافت نشد
                </h3>
                <p class="text-xs text-slate-600 dark:text-slate-300">
                    در حال حاضر در این وضعیت کالایی برای نمایش وجود ندارد. می‌توانید سایر تب‌های وضعیت را بررسی کنید.
                </p>
            </div>
        @endif
    </div>

</div>

<script>
function reorderAlertsManager(config) {
    return {
        csrfToken: config.csrfToken,
        editingId: null,
        editUrl: '',
        editMinStock: 0,
        editReorder: 0,
        saving: false,
        toastMessage: '',
        toastError: false,

        startEdit(itemId, minStock, reorderPoint, url) {
            this.editingId = itemId;
            this.editMinStock = minStock;
            this.editReorder = reorderPoint;
            this.editUrl = url;
        },

        cancelEdit() {
            this.editingId = null;
            this.editUrl = '';
        },

        async saveThresholds() {
            if (!this.editUrl || this.saving) return;
            this.saving = true;
            this.toastMessage = '';
            this.toastError = false;

            try {
                const response = await fetch(this.editUrl, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        min_stock: Number(this.editMinStock || 0),
                        reorder_point: Number(this.editReorder || 0),
                    }),
                });
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'خطا در به‌روزرسانی آستانه‌ها');
                }
                this.toastMessage = data.message;
                this.editingId = null;
                setTimeout(() => window.location.reload(), 650);
            } catch (err) {
                this.toastError = true;
                this.toastMessage = err.message || 'خطا در ذخیره تغییرات';
            } finally {
                this.saving = false;
            }
        }
    };
}
</script>
@endsection
