@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm transition-all duration-300";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-800";
@endphp

<div class="space-y-6 pb-24">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                انبارهای من
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">لیست انبارهای اختصاصی فروشگاه شما</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col md:flex-row justify-end items-center gap-4 bg-white dark:bg-gray-800 p-3 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="relative w-full md:w-80">
            <input type="text" class="{{ $inputClass }} pl-10" placeholder="جستجو در انبارها..." wire:model.live.debounce.300ms="search">
            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
        </div>
    </div>

    {{-- Warehouse List Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($warehouses as $warehouse)
            <div class="{{ $cardClass }} p-5 flex flex-col justify-between hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-md">
                <div>
                    <div class="flex items-start justify-between border-b border-gray-100 dark:border-gray-700 pb-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 dark:text-white text-base">{{ $warehouse->name }}</h3>
                                <p class="text-xs text-gray-400 mt-0.5">کد: <span class="font-bold">{{ $warehouse->code }}</span></p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg {{ $warehouse->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-400' }}">
                            {{ $warehouse->is_active ? 'فعال' : 'غیرفعال' }}
                        </span>
                    </div>

                    <div class="space-y-3 mb-6">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">تاریخ ایجاد:</span>
                            <span class="text-gray-700 dark:text-gray-300">{{ \Morilog\Jalali\Jalalian::fromCarbon($warehouse->created_at)->format('Y/m/d') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex pt-4 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route('user.market.vendor.warehouse-stock.index', ['selectedWarehouseId' => $warehouse->id, 'warehouseFilter' => 'vendors', 'currentTab' => 'stock']) }}" class="flex-1 text-center py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/40 dark:text-indigo-400 rounded-xl text-sm font-bold transition-colors">
                        مشاهده و مدیریت موجودی
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white dark:bg-gray-800 rounded-3xl p-12 text-center border border-gray-100 dark:border-gray-700 shadow-sm">
                <div class="w-20 h-20 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">هیچ انباری یافت نشد!</h3>
                <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">شما هنوز انباری در سیستم ثبت نکرده‌اید.</p>
            </div>
        @endforelse
    </div>

    @if($warehouses->hasPages())
        <div class="mt-6">
            {{ $warehouses->links() }}
        </div>
    @endif
</div>
