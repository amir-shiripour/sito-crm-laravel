@extends('layouts.user')

@section('title', 'انبار موجودی کالا')

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">
                <a href="{{ route('admin.warehouse.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">داشبورد انبار</a>
                <span>/</span>
                <span class="text-gray-800 dark:text-gray-200">انبار موجودی</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                انبار موجودی کالا
            </h1>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.warehouse.stock-levels.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-indigo-200 dark:border-indigo-500/30 bg-indigo-50 dark:bg-indigo-500/15 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 font-bold text-xs transition-all shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                موجودی لحظه‌ای انبارها
            </a>

            <a href="{{ route('admin.warehouse.inventory.pdf', request()->query()) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-all shadow-xs">
                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                گزارش PDF انبارها
            </a>

            <a href="{{ route('admin.warehouse.transfers.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-indigo-200 dark:border-indigo-500/30 bg-indigo-50 dark:bg-indigo-500/15 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 font-bold text-xs transition-all shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                درخواست و انتقال کالا
            </a>

            <a href="{{ route('admin.warehouse.purchases.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-indigo-200 dark:border-indigo-500/30 bg-indigo-50 dark:bg-indigo-500/15 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 font-bold text-xs transition-all shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                ثبت رسید خرید
            </a>

            <a href="{{ route('admin.warehouse.warehouses.create', ['type' => 'inventory', 'parent_id' => $mainWarehouse?->id]) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ $mainWarehouse ? 'افزودن انبارک فرعی' : 'تعریف انبار جدید' }}
            </a>
        </div>
    </div>

    @include('warehouse::partials.flash_messages')

    {{-- Main Central Warehouse Card (Flat & Modern) --}}
    @if($mainWarehouse)
        @php
            $mainManagers = \Modules\Warehouse\Support\WarehouseUserVisibility::filterVisibleManagers($mainWarehouse->managers ?? collect([]));
            $mainStockSummary = $stockSummaries[$mainWarehouse->id] ?? null;
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-5 sm:p-6 shadow-xs">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
                <div class="space-y-3">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="w-3.5 h-3.5 rounded-full bg-indigo-600 shrink-0 shadow-xs"></span>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">
                            {{ $mainWarehouse->name }}
                        </h2>
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-500/30">
                            انبار مرکزی موجودی
                        </span>
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400 font-sans dir-ltr">
                            {{ $mainWarehouse->code }}
                        </span>
                    </div>

                    <div class="flex items-center gap-6 text-xs text-gray-600 dark:text-gray-300 flex-wrap">
                        @if($mainWarehouse->location)
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="text-gray-400">موقعیت:</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $mainWarehouse->location }}</span>
                            </div>
                        @endif

                        @if($mainManagers->isNotEmpty())
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="text-gray-400">مسئولین:</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">
                                    {{ $mainManagers->pluck('name')->join('، ') }}
                                </span>
                            </div>
                        @elseif($mainWarehouse->manager && $mainWarehouse->managers->isEmpty())
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="text-gray-400">مسئول:</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $mainWarehouse->manager }}</span>
                            </div>
                        @endif

                        @if($mainWarehouse->phone)
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <span class="text-gray-400">تماس:</span>
                                <span class="font-sans dir-ltr text-left">{{ $mainWarehouse->phone }}</span>
                            </div>
                        @endif

                        <div class="flex items-center gap-1.5">
                            <span class="text-slate-600 dark:text-slate-300">ثبت‌کننده:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ $mainWarehouse->creator_name }}</span>
                        </div>

                        @if(is_array($mainStockSummary))
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-200/70 dark:border-indigo-500/30 font-sans">
                                    {{ number_format((int) ($mainStockSummary['distinct_items'] ?? 0)) }} قلم کالا
                                </span>
                                <span class="font-bold text-gray-800 dark:text-gray-200 font-sans">
                                    {{ rtrim(rtrim(number_format((float) ($mainStockSummary['total_units'] ?? 0), 2), '0'), '.') }} واحد موجودی
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0 flex-wrap">
                    <a href="{{ route('admin.warehouse.consumptions.create', ['warehouse_id' => $mainWarehouse->id]) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/15 hover:bg-rose-100 dark:hover:bg-rose-500/25 text-rose-700 dark:text-rose-300 font-bold text-xs transition-colors no-underline">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        ثبت خروج / مصرف
                    </a>

                    <a href="{{ route('admin.warehouse.stock-levels.index', ['warehouse_id' => $mainWarehouse->id]) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-indigo-200 dark:border-indigo-500/30 bg-indigo-50 dark:bg-indigo-500/15 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 font-bold text-xs transition-colors no-underline">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        مشاهده موجودی
                    </a>

                    <a href="{{ route('admin.warehouse.warehouses.edit', $mainWarehouse) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 text-gray-700 dark:text-gray-200 font-bold text-xs transition-colors no-underline">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z"/>
                        </svg>
                        ویرایش مشخصات
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-8 text-center space-y-3">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                هنوز هیچ انبار موجودی در سیستم تعریف نشده است
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                برای شروع مدیریت موجودی، نخستین انبار خود را با عنوان انبار مرکزی تعریف نمایید.
            </p>
            <div class="pt-2">
                <a href="{{ route('admin.warehouse.warehouses.create', ['type' => 'inventory']) }}"
                   class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    تعریف انبار موجودی
                </a>
            </div>
        </div>
    @endif

    {{-- Sub-Warehouses Section --}}
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                انبارک‌های فرعی متصل
                <span class="px-2 py-0.5 text-xs font-bold rounded-lg bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 font-sans">
                    {{ $subWarehouses->total() }}
                </span>
            </h3>

            {{-- Search Bar --}}
            <form action="{{ route('admin.warehouse.inventory.index') }}" method="GET" class="w-full sm:w-64">
                <div class="relative">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="جستجو با نام، کد یا مسئول..."
                           class="w-full pr-9 pl-3.5 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                        </svg>
                    </div>
                </div>
            </form>
        </div>

        @if($subWarehouses->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($subWarehouses as $subW)
                    @include('warehouse::partials.warehouse_card', [
                        'warehouse' => $subW,
                        'stockSummary' => $stockSummaries[$subW->id] ?? null,
                    ])
                @endforeach
            </div>

            <div class="pt-2">
                {{ $subWarehouses->links() }}
            </div>
        @else
            <div class="text-center py-8 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-6 space-y-3 shadow-xs">
                <p class="text-xs font-bold text-gray-600 dark:text-gray-400">
                    @if($search)
                        هیچ انبارکی با عبارت «{{ $search }}» یافت نشد.
                    @else
                        هنوز هیچ انبارک فرعی برای این بخش تعریف نشده است.
                    @endif
                </p>
                @if(!$search)
                    <div>
                        <a href="{{ route('admin.warehouse.warehouses.create', ['type' => 'inventory', 'parent_id' => $mainWarehouse?->id]) }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            ایجاد اولین انبارک
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Linked Market Warehouses Section (If active) --}}
    @if($isMarketIntegrated)
        <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-800">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    انبارهای متصل از ماژول فروشگاه
                    <span class="px-2 py-0.5 text-xs font-bold rounded-lg bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 font-sans">
                        {{ $linkedMarketWarehouses->count() }}
                    </span>
                </h3>

                <a href="{{ route('admin.warehouse.settings.edit') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:hover:bg-amber-500/25 dark:text-amber-300 border border-amber-200/60 dark:border-amber-500/30 transition-colors">
                    <span>مدیریت اتصالات</span>
                    <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            @if($linkedMarketWarehouses->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($linkedMarketWarehouses as $marketW)
                        @include('warehouse::partials.market_warehouse_card', ['marketWarehouse' => $marketW])
                    @endforeach
                </div>
            @else
                <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-800 text-center text-xs text-gray-500 dark:text-gray-400">
                    انباری از فروشگاه متصل نشده است.
                </div>
            @endif
        </div>
    @endif

</div>
@endsection
