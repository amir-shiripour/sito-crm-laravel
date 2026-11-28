@extends('layouts.user')

@section('title', 'مدیریت اموال سازمانی')

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">
                <a href="{{ route('admin.warehouse.dashboard') }}" class="hover:text-teal-600 dark:hover:text-teal-400 transition-colors">داشبورد انبار</a>
                <span>/</span>
                <span class="text-gray-800 dark:text-gray-200">مدیریت اموال</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                مدیریت اموال سازمانی
            </h1>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.warehouse.assets.pdf', request()->query()) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-all shadow-xs">
                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                گزارش PDF اموال
            </a>

            <a href="{{ route('admin.warehouse.warehouses.create', ['type' => 'asset', 'parent_id' => $mainWarehouse?->id]) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ $mainWarehouse ? 'افزودن انبارک اموال' : 'تعریف انبار اموال جدید' }}
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
                        <span class="w-3 h-3 rounded-full bg-teal-600 shrink-0"></span>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">
                            {{ $mainWarehouse->name }}
                        </h2>
                        <span class="px-2 py-0.5 text-[11px] font-bold rounded bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border border-teal-200/60 dark:border-teal-800/60">
                            انبار مرکزی اموال
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

                        @php
                            $mainAssetManagers = \Modules\Warehouse\Support\WarehouseUserVisibility::filterVisibleManagers($mainWarehouse->managers ?? collect([]));
                        @endphp
                        @if($mainAssetManagers->isNotEmpty())
                            <div class="flex items-center gap-1.5">
                                <span class="text-gray-400">مسئول اموال:</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $mainAssetManagers->pluck('name')->join('، ') }}</span>
                            </div>
                        @elseif($mainWarehouse->manager && $mainWarehouse->managers->isEmpty())
                            <div class="flex items-center gap-1.5">
                                <span class="text-gray-400">مسئول اموال:</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $mainWarehouse->manager }}</span>
                            </div>
                        @endif

                        @if($mainWarehouse->phone)
                            <div class="flex items-center gap-1.5">
                                <span class="text-gray-400">تماس:</span>
                                <span class="font-sans dir-ltr text-left">{{ $mainWarehouse->phone }}</span>
                            </div>
                        @endif

                        <div class="flex items-center gap-1.5">
                            <span class="text-slate-600 dark:text-slate-300">ثبت‌کننده:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ $mainWarehouse->creator_name }}</span>
                        </div>
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
    @else
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-8 text-center space-y-3">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                هنوز هیچ انبار یا مکان استقرار اموالی تعریف نشده است
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                برای شروع ثبت و رهگیری دارایی‌های ثابت و اثاثیه سازمانی، نخستین انبار یا واحد استقرار اموال را ثبت کنید.
            </p>
            <div class="pt-2">
                <a href="{{ route('admin.warehouse.warehouses.create', ['type' => 'asset']) }}"
                   class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    تعریف انبار اموال
                </a>
            </div>
        </div>
    @endif

    {{-- Sub-Warehouses Section --}}
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    مکان‌ها و انبارک‌های استقرار اموال
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 font-sans">
                        ({{ $subWarehouses->total() }})
                    </span>
                </h3>
            </div>

            {{-- Search Bar --}}
            <form action="{{ route('admin.warehouse.assets.index') }}" method="GET" class="w-full sm:w-64">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="جستجو با نام، کد یا مسئول..."
                       class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-sans">
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
                        هیچ مکانی با عبارت «{{ $search }}» یافت نشد.
                    @else
                        هنوز هیچ انبارک اموالی تعریف نشده است.
                    @endif
                </p>
                <div>
                    <a href="{{ route('admin.warehouse.warehouses.create', ['type' => 'asset', 'parent_id' => $mainWarehouse?->id]) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        ایجاد اولین انبارک اموال
                    </a>
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
