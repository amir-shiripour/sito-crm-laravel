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
            <a href="{{ route('admin.warehouse.warehouses.create', ['type' => 'inventory', 'parent_id' => $mainWarehouse?->id]) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                افزودن انبارک فرعی
            </a>
        </div>
    </div>

    @include('warehouse::partials.flash_messages')

    {{-- Main Warehouse Banner (Flat) --}}
    @if($mainWarehouse)
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="w-3 h-3 rounded-full bg-indigo-600 shrink-0"></span>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">
                            {{ $mainWarehouse->name }}
                        </h2>
                        <span class="px-2 py-0.5 text-[11px] font-bold rounded bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60">
                            انبار مرکزی موجودی
                        </span>
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400 font-sans">
                            {{ $mainWarehouse->code }}
                        </span>
                    </div>

                    <div class="flex items-center gap-5 text-xs text-gray-600 dark:text-gray-300 flex-wrap pt-1">
                        @if($mainWarehouse->location)
                            <div class="flex items-center gap-1.5">
                                <span class="text-gray-400">مکان:</span>
                                <span>{{ $mainWarehouse->location }}</span>
                            </div>
                        @endif

                        @if($mainWarehouse->manager)
                            <div class="flex items-center gap-1.5">
                                <span class="text-gray-400">مسئول:</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $mainWarehouse->manager }}</span>
                            </div>
                        @endif

                        @if($mainWarehouse->phone)
                            <div class="flex items-center gap-1.5">
                                <span class="text-gray-400">تماس:</span>
                                <span class="font-sans dir-ltr text-left">{{ $mainWarehouse->phone }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div>
                    <a href="{{ route('admin.warehouse.warehouses.edit', $mainWarehouse) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 text-gray-700 dark:text-gray-200 font-bold text-xs transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z"/>
                        </svg>
                        ویرایش مشخصات
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Sub-Warehouses Section --}}
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    انبارک‌های فرعی متصل
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 font-sans">
                        ({{ $subWarehouses->total() }})
                    </span>
                </h3>
            </div>

            {{-- Search Bar --}}
            <form action="{{ route('admin.warehouse.inventory.index') }}" method="GET" class="w-full sm:w-64">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="جستجو با نام، کد یا مسئول..."
                       class="w-full px-3.5 py-2 rounded-xl bg-gray-50/70 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
            </form>
        </div>

        @if($subWarehouses->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($subWarehouses as $subW)
                    @include('warehouse::partials.warehouse_card', ['warehouse' => $subW])
                @endforeach
            </div>

            <div class="pt-2">
                {{ $subWarehouses->links() }}
            </div>
        @else
            <div class="text-center py-8 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-3">
                <p class="text-xs font-bold text-gray-600 dark:text-gray-400">
                    @if($search)
                        هیچ انبارکی با عبارت «{{ $search }}» یافت نشد.
                    @else
                        هنوز هیچ انبارک فرعی برای این بخش تعریف نشده است.
                    @endif
                </p>
                <div>
                    <a href="{{ route('admin.warehouse.warehouses.create', ['type' => 'inventory', 'parent_id' => $mainWarehouse?->id]) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        ایجاد اولین انبارک
                    </a>
                </div>
            </div>
        @endif
    </div>

    {{-- Linked Market Warehouses Section (If active) --}}
    @if($isMarketIntegrated)
        <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-800">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    انبارهای متصل از ماژول فروشگاه
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 font-sans">
                        ({{ $linkedMarketWarehouses->count() }})
                    </span>
                </h3>

                <a href="{{ route('admin.warehouse.settings.edit') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:hover:bg-amber-900/60 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/60 transition-colors">
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
