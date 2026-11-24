@extends('layouts.user')

@section('title', 'انبارداری و مدیریت اموال')

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- Top Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-800">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                انبارداری و مدیریت اموال
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                مدیریت متمرکز و یکپارچه انبار کالاهای مصرفی، موجودی‌ها و اموال سازمانی
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.warehouse.settings.edit') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold text-xs transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                تنظیمات انبار
            </a>

            @if($inventoryEnabled)
                <a href="{{ route('admin.warehouse.warehouses.create', ['type' => 'inventory', 'parent_id' => $mainInventoryWarehouse?->id]) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    انبارک موجودی جدید
                </a>
            @endif

            @if($assetsEnabled)
                <a href="{{ route('admin.warehouse.warehouses.create', ['type' => 'asset', 'parent_id' => $mainAssetWarehouse?->id]) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    انبارک اموال جدید
                </a>
            @endif
        </div>
    </div>

    @include('warehouse::partials.flash_messages')

    {{-- Stats Row (Flat, Harmonized) --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400">کل انبارهای فعال</p>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mt-1 font-sans">
                    {{ $totalActiveWarehouses }}
                </h3>
            </div>
            <span class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </span>
        </div>

        @if($inventoryEnabled)
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400">انبار کالا / موجودی فعال</p>
                    <h3 class="text-xl font-bold text-indigo-600 dark:text-indigo-400 mt-1 font-sans">
                        {{ $totalInventoryCount }}
                    </h3>
                </div>
                <span class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </span>
            </div>
        @endif

        @if($assetsEnabled)
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400">انبار اموال فعال</p>
                    <h3 class="text-xl font-bold text-teal-600 dark:text-teal-400 mt-1 font-sans">
                        {{ $totalAssetCount }}
                    </h3>
                </div>
                <span class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
            </div>
        @endif

        @if($inventoryEnabled && $marketIntegrationEnabled)
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400">انبارهای متصل فروشگاه</p>
                    <h3 class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1 font-sans">
                        {{ $linkedMarketCount }}
                    </h3>
                </div>
                <span class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </span>
            </div>
        @endif
    </div>

    {{-- Dynamic Sections (Only active types are rendered) --}}
    <div class="grid grid-cols-1 {{ $inventoryEnabled && $assetsEnabled ? 'lg:grid-cols-2' : '' }} gap-6 items-start">

        {{-- Inventory Warehouses Section (Only if enabled) --}}
        @if($inventoryEnabled)
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">
                            انبار موجودی کالا
                        </h2>
                    </div>
                    <a href="{{ route('admin.warehouse.inventory.index') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60 transition-colors">
                        <span>مدیریت موجودی</span>
                        <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                {{-- Main Inventory Warehouse --}}
                @if($mainInventoryWarehouse)
                    <div class="space-y-3">
                        @include('warehouse::partials.warehouse_card', ['warehouse' => $mainInventoryWarehouse])

                        {{-- Active Sub-warehouses --}}
                        @if($inventorySubWarehouses->count() > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                @foreach($inventorySubWarehouses as $subW)
                                    @include('warehouse::partials.warehouse_card', ['warehouse' => $subW])
                                @endforeach
                            </div>
                        @endif

                        {{-- Active Linked Market Warehouses --}}
                        @if($linkedMarketWarehouses->count() > 0)
                            <div class="pt-2">
                                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">انبارهای متصل فروشگاه:</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($linkedMarketWarehouses as $marketW)
                                        @include('warehouse::partials.market_warehouse_card', ['marketWarehouse' => $marketW])
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-6 rounded-2xl border border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-800 text-center space-y-2">
                        <p class="text-xs font-bold text-gray-600 dark:text-gray-400">انبار مرکزی موجودی ثبت نشده است.</p>
                        <a href="{{ route('admin.warehouse.warehouses.create', ['type' => 'inventory']) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            تعریف انبار موجودی
                        </a>
                    </div>
                @endif
            </div>
        @endif

        {{-- Asset Warehouses Section (Only if enabled) --}}
        @if($assetsEnabled)
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">
                            مدیریت اموال سازمانی
                        </h2>
                    </div>
                    <a href="{{ route('admin.warehouse.assets.index') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-teal-50 hover:bg-teal-100 dark:bg-teal-950/60 dark:hover:bg-teal-900/60 text-teal-700 dark:text-teal-300 border border-teal-200/60 dark:border-teal-800/60 transition-colors">
                        <span>مدیریت اموال</span>
                        <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                {{-- Main Asset Warehouse --}}
                @if($mainAssetWarehouse)
                    <div class="space-y-3">
                        @include('warehouse::partials.warehouse_card', ['warehouse' => $mainAssetWarehouse])

                        {{-- Active Sub-warehouses --}}
                        @if($assetSubWarehouses->count() > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                @foreach($assetSubWarehouses as $subW)
                                    @include('warehouse::partials.warehouse_card', ['warehouse' => $subW])
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-6 rounded-2xl border border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-800 text-center space-y-2">
                        <p class="text-xs font-bold text-gray-600 dark:text-gray-400">انبار مرکزی اموال ثبت نشده است.</p>
                        <a href="{{ route('admin.warehouse.warehouses.create', ['type' => 'asset']) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition-all shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            تعریف انبار اموال
                        </a>
                    </div>
                @endif
            </div>
        @endif

    </div>

</div>
@endsection
