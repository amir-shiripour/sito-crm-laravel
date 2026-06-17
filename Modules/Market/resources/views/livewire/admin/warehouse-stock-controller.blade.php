@php
    $inputClass = "w-full rounded-lg border-gray-200 bg-gray-50 px-3 py-1.5 text-sm  text-center focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900/50 dark:text-white transition-all";
    $labelClass = "block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1";
    $currencySetting = \Modules\Market\Entities\MarketSetting::getValue('general.currency', 'toman');
    $currencyLabel = $currencySetting === 'toman' ? 'تومان' : ($currencySetting === 'rial' ? 'ریال' : $currencySetting);
@endphp

<div class="space-y-4 pb-24">
    {{-- Header & Filters --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                انبارگردانی و مدیریت موجودی
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">ویرایش سریع موجودی فیزیکی و آنلاین محصولات در انبارهای مرتبط</p>
        </div>
    </div>

    {{-- WMS Stats Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        {{-- Card 1: Warehouses count --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
            </div>
            <div>
                <span class="block text-[10px] text-gray-400 dark:text-gray-500 font-bold">تعداد انبارها</span>
                <span class="text-lg font-extrabold text-gray-900 dark:text-white ">{{ $this->wmsStats['total_warehouses'] }}</span>
                <span class="text-[9px] text-emerald-500 font-bold">({{ $this->wmsStats['active_warehouses'] }} فعال)</span>
            </div>
        </div>

        {{-- Card 2: Physical stock --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
            </div>
            <div>
                <span class="block text-[10px] text-gray-400 dark:text-gray-500 font-bold">کل موجودی فیزیکی</span>
                <span class="text-lg font-extrabold text-gray-900 dark:text-white ">{{ number_format($this->wmsStats['total_physical']) }}</span>
                <span class="text-[9px] text-gray-400">عدد در قفسه</span>
            </div>
        </div>

        {{-- Card 3: Online stock --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
            </div>
            <div>
                <span class="block text-[10px] text-gray-400 dark:text-gray-500 font-bold">موجودی آنلاین (سایت)</span>
                <span class="text-lg font-extrabold text-indigo-600 dark:text-indigo-400 ">{{ number_format($this->wmsStats['total_online']) }}</span>
                <span class="text-[9px] text-indigo-400">عدد آماده فروش</span>
            </div>
        </div>

        {{-- Card 4: Reserved stock --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
            </div>
            <div>
                <span class="block text-[10px] text-gray-400 dark:text-gray-500 font-bold">موجودی رزرو شده</span>
                <span class="text-lg font-extrabold text-amber-600 dark:text-amber-400 ">{{ number_format($this->wmsStats['total_reserved']) }}</span>
                <span class="text-[9px] text-amber-500">سفارشات باز</span>
            </div>
        </div>

        {{-- Card 5: Net available stock --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/30 flex items-center justify-center text-rose-600 dark:text-rose-400 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
            </div>
            <div>
                <span class="block text-[10px] text-gray-400 dark:text-gray-500 font-bold">موجود خالص واقعی</span>
                <span class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400 ">{{ number_format(max(0, $this->wmsStats['total_online'] - $this->wmsStats['total_reserved'])) }}</span>
                <span class="text-[9px] text-emerald-500">آماده تخصیص جدید</span>
            </div>
        </div>
    </div>

    @if(auth()->user()->hasAnyRole(['super-admin', 'admin']))
        {{-- WMS Scope Selector for Admin --}}
        <div class="bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-700 rounded-2xl p-4 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-800 dark:text-gray-200">مدیریت و نظارت جامع انبارها</h2>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">سوئیچ بین انبار مرکزی سیستم و انبار فروشندگان</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="flex bg-white dark:bg-gray-900 p-1 rounded-xl border border-gray-200 dark:border-gray-700/60 shadow-sm">
                    <button type="button" wire:click="setWarehouseFilter('central')" class="px-4 py-2 rounded-lg text-xs font-bold transition-all {{ $warehouseFilter === 'central' ? 'bg-indigo-600 text-white shadow' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                        انبار مرکزی سیستم
                    </button>
                    <button type="button" wire:click="setWarehouseFilter('vendors')" class="px-4 py-2 rounded-lg text-xs font-bold transition-all {{ $warehouseFilter === 'vendors' ? 'bg-indigo-600 text-white shadow' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                        انبارهای فروشندگان
                    </button>
                    <button type="button" wire:click="setWarehouseFilter('all')" class="px-4 py-2 rounded-lg text-xs font-bold transition-all {{ $warehouseFilter === 'all' ? 'bg-indigo-600 text-white shadow' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                        کل انبارها (ادغام شده)
                    </button>
                </div>

                @if($warehouseFilter === 'vendors')
                    <div class="flex items-center gap-2">
                        <select wire:model.live="selectedVendorId" class="rounded-xl border-gray-200 bg-white px-3 py-2 text-xs font-bold text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-white min-w-[150px] focus:ring-1 focus:ring-indigo-500">
                            <option value="">همه فروشندگان</option>
                            @foreach($this->vendorsList as $vnd)
                                <option value="{{ $vnd->id }}">{{ $vnd->store_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <select wire:model.live="selectedWarehouseId" class="rounded-xl border-gray-200 bg-white px-3 py-2 text-xs font-bold text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-white min-w-[150px] focus:ring-1 focus:ring-indigo-500">
                            <option value="">همه انبارها</option>
                            @foreach($this->allWarehousesList as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Active Warehouse Filter Badge (if single warehouse is selected via tab or dropdown) --}}
    @if(auth()->user()->hasAnyRole(['super-admin', 'admin']) && $selectedWarehouseId)
        @php
            $activeWhName = $this->allWarehousesList->firstWhere('id', $selectedWarehouseId)?->name ?? 'انبار هدف';
        @endphp
        <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/30 p-3 rounded-xl flex items-center justify-between mb-6">
            <span class="text-xs font-bold text-indigo-700 dark:text-indigo-300 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                محدود شده به: <span class="underline underline-offset-4">{{ $activeWhName }}</span>
            </span>
            <button type="button" wire:click="clearWarehouseFilter" class="text-[10px] font-bold text-red-500 hover:text-red-700 dark:hover:text-red-400 bg-white dark:bg-gray-800 px-2.5 py-1 rounded-lg shadow-sm border border-red-100 dark:border-red-900/50 transition-colors">
                لغو فیلتر انبار
            </button>
        </div>
    @endif

    {{-- Tab Switching --}}
    <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6">
        @if(auth()->user()->hasAnyRole(['super-admin', 'admin']) && $warehouseFilter === 'vendors')
            <button wire:click="setTab('warehouses')" class="py-2.5 px-4 text-sm font-bold border-b-2 transition-all {{ $currentTab === 'warehouses' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                لیست انبارهای فروشندگان
            </button>
        @endif
        <button wire:click="setTab('stock')" class="py-2.5 px-4 text-sm font-bold border-b-2 transition-all {{ $currentTab === 'stock' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            موجودی محصولات
        </button>
        <button wire:click="setTab('transactions')" class="py-2.5 px-4 text-sm font-bold border-b-2 transition-all {{ $currentTab === 'transactions' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            لیست اسناد حواله انبار (تراکنش‌ها)
        </button>
        <button wire:click="setTab('transfers')" class="py-2.5 px-4 text-sm font-bold border-b-2 transition-all {{ $currentTab === 'transfers' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            انتقال کالا بین انبارها
        </button>
    </div>

    @if($currentTab === 'warehouses' && auth()->user()->hasAnyRole(['super-admin', 'admin']) && $warehouseFilter === 'vendors')
        {{-- Warehouses List Tab --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            @forelse($this->vendorWarehousesStats as $wh)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-3xl p-5 shadow-sm hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-600 transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3 mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 dark:text-gray-200 text-sm">{{ $wh['name'] }}</h3>
                                    <span class="text-[9px]  text-gray-400">{{ $wh['code'] }}</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $wh['is_active'] ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                                {{ $wh['is_active'] ? 'فعال' : 'غیرفعال' }}
                            </span>
                        </div>

                        <div class="space-y-2 mb-5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-400">مالک (فروشنده):</span>
                                <span class="font-bold text-gray-700 dark:text-gray-300">{{ $wh['vendor_name'] }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-400">تنوع‌های کالایی:</span>
                                <span class="font-bold text-gray-700 dark:text-gray-300 ">{{ $wh['total_products'] }} تنوع</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 bg-gray-50 dark:bg-gray-900/40 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700/50 text-center mt-3">
                                <div>
                                    <span class="block text-[9px] text-gray-400 font-bold mb-0.5">فیزیکی</span>
                                    <span class=" font-extrabold text-sm text-gray-800 dark:text-gray-200">{{ number_format($wh['total_physical']) }}</span>
                                </div>
                                <div>
                                    <span class="block text-[9px] text-indigo-400 font-bold mb-0.5">آنلاین</span>
                                    <span class=" font-extrabold text-sm text-indigo-600 dark:text-indigo-400">{{ number_format($wh['total_online']) }}</span>
                                </div>
                                <div>
                                    <span class="block text-[9px] text-amber-500 font-bold mb-0.5">رزرو شده</span>
                                    <span class=" font-extrabold text-sm text-amber-600 dark:text-amber-500">{{ number_format($wh['total_reserved']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2 border-t border-gray-100 dark:border-gray-700/50 pt-3 mt-auto">
                        <button type="button" wire:click="selectWarehouseAndTab({{ $wh['id'] }}, 'stock')" class="flex-1 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 text-xs font-bold transition-all text-center">
                            موجودی محصولات
                        </button>
                        <button type="button" wire:click="selectWarehouseAndTab({{ $wh['id'] }}, 'transactions')" class="flex-1 py-2 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-xs font-bold transition-all text-center">
                            اسناد و تراکنش‌ها
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white dark:bg-gray-800 rounded-3xl p-10 text-center border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col items-center">
                    <div class="w-16 h-16 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">هیچ انبار فروشنده‌ای یافت نشد!</h3>
                </div>
            @endforelse
        </div>
    @elseif($currentTab === 'stock')
        {{-- Search & Tabs --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
            <div class="lg:col-span-2 relative w-full">
                <input type="text" class="w-full rounded-xl border-gray-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white pl-10" placeholder="جستجو در عنوان محصول، ویژگی‌ها یا کد تنوع..." wire:model.live.debounce.300ms="search">
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
            </div>
            <div class="flex items-center p-1 bg-gray-100/50 dark:bg-gray-800/50 rounded-xl border border-gray-200/50 dark:border-gray-700/50">
                <button wire:click="setStockStatusFilter('all')" class="{{ $stockStatusFilter === 'all' ? 'bg-white dark:bg-gray-700 shadow-sm text-indigo-600 dark:text-indigo-400 font-bold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }} flex-1 py-2 px-3 rounded-lg text-xs sm:text-sm transition-all text-center">همه محصولات</button>
                <button wire:click="setStockStatusFilter('in_stock')" class="{{ $stockStatusFilter === 'in_stock' ? 'bg-white dark:bg-gray-700 shadow-sm text-emerald-600 dark:text-emerald-400 font-bold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }} flex-1 py-2 px-3 rounded-lg text-xs sm:text-sm transition-all text-center">دارای موجودی</button>
                <button wire:click="setStockStatusFilter('out_of_stock')" class="{{ $stockStatusFilter === 'out_of_stock' ? 'bg-white dark:bg-gray-700 shadow-sm text-rose-600 dark:text-rose-400 font-bold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }} flex-1 py-2 px-3 rounded-lg text-xs sm:text-sm transition-all text-center">نیازمند شارژ</button>
            </div>
        </div>

        {{-- Product List --}}
        <div class="space-y-4">
            @forelse($this->products as $variant)
                @php
                    $totalPhysicalStock = $variant->total_physical_stock ?? 0;
                    $totalOnlineStock = $variant->total_online_stock ?? 0;
                    $totalReservedStock = $variant->total_reserved_stock ?? 0;
                    $overallStock = $totalPhysicalStock + $totalOnlineStock;
                    $isNewForVendor = !$variant->vendor_product_id;
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-300 {{ $editingVariantId === $variant->id ? 'ring-2 ring-indigo-500/50' : '' }}">

                    {{-- Product Card Header (Always Visible) --}}
                    <div wire:click="toggleEditStock({{ $variant->id }})" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-xl bg-gray-100 dark:bg-gray-900 flex items-center justify-center overflow-hidden border border-gray-200 dark:border-gray-700 shrink-0">
                                <img src="{{ $variant->masterProduct->main_image_url }}" alt="{{ $variant->masterProduct->title }}" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 dark:text-white text-sm sm:text-base">{{ $variant->masterProduct->title }}</h3>
                                <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                    @if(!empty($variant->variant_attributes))
                                        @foreach($variant->variant_attributes as $attrName => $attrValue)
                                            @if($attrName === 'name' && $attrValue === 'استاندارد') @continue @endif
                                            <span class="text-[10px] font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">{{ $attrName }}: {{ $attrValue }}</span>
                                        @endforeach
                                    @endif
                                    <span class="text-[10px]  bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 px-2 py-0.5 rounded">{{ $variant->variant_code }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto mt-2 sm:mt-0 pt-2 sm:pt-0 border-t sm:border-0 border-gray-100 dark:border-gray-700">
                            <div class="flex gap-4 sm:gap-6">
                                <div class="flex flex-col items-center sm:items-end">
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">فیزیکی کل</span>
                                    <span class=" font-bold text-sm text-gray-700 dark:text-gray-200">{{ $totalPhysicalStock }}</span>
                                </div>
                                <div class="flex flex-col items-center sm:items-end">
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">آنلاین کل</span>
                                    <span class=" font-bold text-sm text-gray-700 dark:text-gray-200">{{ $totalOnlineStock }}</span>
                                </div>
                                <div class="flex flex-col items-center sm:items-end">
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">وضعیت</span>
                                    @if($isNewForVendor)
                                        <span class="text-xs font-bold text-sky-500 dark:text-sky-400 mt-0.5">تنوع جدید</span>
                                    @else
                                        <div class="flex items-center gap-1 mt-1">
                                            <div class="w-2 h-2 rounded-full {{ $overallStock > 0 ? 'bg-emerald-500' : 'bg-rose-500' }}"></div>
                                            <span class="text-xs font-bold {{ $overallStock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ $overallStock > 0 ? 'موجود' : 'ناموجود' }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="p-1 text-gray-400 dark:text-gray-500 transform transition-transform duration-300 {{ $editingVariantId === $variant->id ? 'rotate-180 text-indigo-500' : '' }}">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Accordion Body (Edit Stocks / Transaction Voucher) --}}
                    @if($editingVariantId === $variant->id || $showTxVariantId === $variant->id)
                        <div class="bg-indigo-50/30 dark:bg-gray-900/30 border-t border-indigo-100 dark:border-gray-700 p-4 animate-in slide-in-from-top-2">

                            @if($showTxVariantId === $variant->id)
                                {{-- Manual Transaction Voucher Form --}}
                                <form wire:submit.prevent="submitTransaction({{ $variant->id }})">
                                    <div class="mb-4 flex items-center justify-between">
                                        <h4 class="text-xs font-extrabold text-indigo-700 dark:text-indigo-400 flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            ثبت سند حواله دستی انبار (سیستم انبارداری مجزا)
                                        </h4>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                        {{-- Warehouse Selector --}}
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">انبار هدف</label>
                                            <select wire:model.live="txWarehouseId" class="w-full rounded-lg border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                @foreach($this->relevantWarehouses as $wh)
                                                    <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                                                @endforeach
                                            </select>
                                            @error('txWarehouseId') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- Voucher Type (Intake / Output) --}}
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">نوع حواله</label>
                                            <div class="flex gap-2">
                                                <button type="button" wire:click="$set('txType', 'in')" class="flex-1 py-1.5 px-3 rounded-lg text-xs font-bold transition-all text-center border {{ $txType === 'in' ? 'bg-emerald-50 border-emerald-500 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400' : 'bg-white border-gray-200 text-gray-500 dark:bg-gray-800 dark:border-gray-700' }}">ورود کالا (+)</button>
                                                <button type="button" wire:click="$set('txType', 'out')" class="flex-1 py-1.5 px-3 rounded-lg text-xs font-bold transition-all text-center border {{ $txType === 'out' ? 'bg-rose-50 border-rose-500 text-rose-700 dark:bg-rose-950/20 dark:text-rose-400' : 'bg-white border-gray-200 text-gray-500 dark:bg-gray-800 dark:border-gray-700' }}">خروج کالا (-)</button>
                                            </div>
                                            @error('txType') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- Stock Type Selector (Physical / Online / Both) --}}
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">بخش انبار</label>
                                            <select wire:model="txStockType" class="w-full rounded-lg border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                <option value="both">موجودی فیزیکی و آنلاین (هر دو)</option>
                                                <option value="physical">فقط موجودی فیزیکی (قفسه)</option>
                                                <option value="online">فقط موجودی آنلاین (سایت)</option>
                                            </select>
                                            @error('txStockType') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- Quantity --}}
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">تعداد / مقدار</label>
                                            <input type="number" wire:model="txQuantity" class="w-full rounded-lg border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-white " placeholder="مثال: 5">
                                            @error('txQuantity') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                                        {{-- Unit Price --}}
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">
                                                {{ $txType === 'in' ? "قیمت خرید واحد ($currencyLabel) - اختیاری" : "قیمت فروش واحد ($currencyLabel) - اختیاری" }}
                                            </label>
                                            <input type="text"
                                                   x-data="{ val: @entangle('txUnitPrice') }"
                                                   x-on:input="val = $event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                                                   x-bind:value="val ? val.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',') : ''"
                                                   class="w-full rounded-lg border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-white "
                                                   placeholder="مثال: ۱۵۰,۰۰۰">
                                            @error('txUnitPrice') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- Reason Dropdown --}}
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">علت تراکنش</label>
                                            <select wire:model="txReason" class="w-full rounded-lg border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                @if($txType === 'in')
                                                    <option value="purchase">خرید کالا</option>
                                                    <option value="return">مرجوعی مشتری</option>
                                                    <option value="adjustment_in">تعدیل مثبت (ورود)</option>
                                                    <option value="other">سایر موارد</option>
                                                @else
                                                    <option value="sale">فروش دستی کالا</option>
                                                    <option value="damage">ضایعات / خرابی</option>
                                                    <option value="adjustment_out">تعدیل منفی (خروج)</option>
                                                    <option value="other">سایر موارد</option>
                                                @endif
                                            </select>
                                            @error('txReason') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- Document Ref --}}
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">شماره سند مرجع / فاکتور</label>
                                            <input type="text" wire:model="txDocumentRef" class="w-full rounded-lg border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-white" placeholder="مثال: F-1049">
                                            @error('txDocumentRef') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">توضیحات تکمیلی</label>
                                        <textarea wire:model="txCustomDescription" rows="2" class="w-full rounded-lg border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-white" placeholder="جزئیات بیشتر در مورد این سند حواله دستی..."></textarea>
                                        @error('txCustomDescription') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <button type="button" wire:click="toggleEditStock({{ $variant->id }})" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">
                                            ← بازگشت به ویرایش سریع موجودی
                                        </button>
                                        <div class="flex items-center gap-3">
                                            <button type="button" wire:click="closeTransactionForm" class="px-4 py-2 text-xs font-bold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">بستن</button>
                                            <button type="submit" class="px-5 py-2 rounded-lg bg-indigo-600 text-white text-xs font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2">
                                                <span wire:loading.remove wire:target="submitTransaction({{ $variant->id }})">ثبت سند حواله</span>
                                                <span wire:loading wire:target="submitTransaction({{ $variant->id }})">در حال ثبت...</span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                {{-- Fast Stock Edit Form --}}
                                @if($isNewForVendor)
                                    <div class="mb-4 p-3 bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-800/50 rounded-xl flex items-start gap-3">
                                        <svg class="w-5 h-5 text-sky-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <div>
                                            <p class="text-sm font-bold text-sky-800 dark:text-sky-400">این تنوع برای شما جدید است.</p>
                                            <p class="text-xs text-sky-600 dark:text-sky-500 mt-1">با وارد کردن موجودی و ذخیره، این تنوع به لیست محصولات شما اضافه شده و در انبار ثبت می‌شود.</p>
                                        </div>
                                    </div>
                                @endif

                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
                                        @foreach($this->relevantWarehouses as $warehouse)
                                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-3.5 flex flex-col gap-3 shadow-sm hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors">
                                                <div class="flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-2 flex-wrap">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                                    <span class="font-bold text-sm text-gray-800 dark:text-gray-200">{{ $warehouse->name }}</span>
                                                    @if($warehouse->vendor)
                                                        <span class="text-[9px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/30 px-1.5 py-0.5 rounded">{{ $warehouse->vendor->store_name }}</span>
                                                    @endif
                                                    <span class="text-[9px]  text-gray-400 mr-auto">{{ $warehouse->code }}</span>
                                                </div>

                                                <div class="grid grid-cols-2 gap-3">
                                                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-xl p-2.5 text-center border border-gray-100 dark:border-gray-700">
                                                        <span class="block text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1">فیزیکی (قفسه)</span>
                                                        <span class=" font-extrabold text-base text-gray-800 dark:text-gray-200">
                                                            {{ $variant->warehouseStocks->firstWhere('warehouse_id', $warehouse->id)->physical_stock ?? 0 }}
                                                        </span>
                                                    </div>
                                                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-xl p-2.5 text-center border border-gray-100 dark:border-gray-700">
                                                        <span class="block text-[10px] font-bold text-indigo-500 dark:text-indigo-400 mb-1">آنلاین (سایت)</span>
                                                        <span class=" font-extrabold text-base text-indigo-600 dark:text-indigo-400">
                                                            {{ $variant->warehouseStocks->firstWhere('warehouse_id', $warehouse->id)->online_stock ?? 0 }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="flex justify-between items-center text-[10px] text-gray-500 pt-1 mt-1 border-t border-gray-100/50 dark:border-gray-700/50">
                                                    <span>رزرو شده: <span class="font-bold ">{{ $variant->warehouseStocks->firstWhere('warehouse_id', $warehouse->id)->reserved_stock ?? 0 }}</span></span>
                                                    <div class="flex items-center gap-1.5">
                                                        <button type="button" wire:click="openTransferForm({{ $variant->id }}, {{ $warehouse->id }})" class="text-[10px] font-bold text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300 transition-colors flex items-center gap-1 bg-amber-50 dark:bg-amber-950/40 px-2 py-1 rounded">
                                                            <span>درخواست انتقال</span>
                                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                                        </button>
                                                        <button type="button" wire:click="openTransactionForm({{ $variant->id }}, {{ $warehouse->id }})" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors flex items-center gap-1 bg-indigo-50 dark:bg-indigo-950/40 px-2 py-1 rounded">
                                                            <span>ثبت حواله</span>
                                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <button type="button" wire:click="openTransactionForm({{ $variant->id }})" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            ثبت سند حواله دستی (ورود/خروج)
                                        </button>
                                        <div class="flex items-center gap-3">
                                            <button type="button" wire:click="cancelEdit" class="px-4 py-2 text-xs font-bold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors bg-gray-100 dark:bg-gray-800 rounded-lg">بستن</button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-10 text-center border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col items-center">
                    <div class="w-16 h-16 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">محصولی یافت نشد!</h3>
                    <p class="text-sm text-gray-500 mt-1 max-w-sm">با توجه به فیلترها و کلمات جستجو شده، هیچ محصول یا تنوعی برای نمایش وجود ندارد.</p>
                </div>
            @endforelse
        </div>

        @if($this->products->hasPages())
            <div class="mt-6">
                {{ $this->products->links() }}
            </div>
        @endif
    @elseif($currentTab === 'transactions')
        {{-- Transactions Listing Tab --}}
        <div class="space-y-4 animate-in fade-in-50 duration-200">
            {{-- Search & Filters for Transactions --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="relative w-full">
                    <input type="text" class="w-full rounded-xl border-gray-200 bg-white px-4 py-2 text-xs shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white pl-10" placeholder="جستجو در محصول، کد یا توضیحات..." wire:model.live.debounce.300ms="txSearch">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                </div>

                <div>
                    <select wire:model.live="txFilterWarehouseId" class="w-full rounded-xl border-gray-200 bg-white px-4 py-2 text-xs shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">همه انبارها</option>
                        @foreach($this->relevantWarehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select wire:model.live="txFilterType" class="w-full rounded-xl border-gray-200 bg-white px-4 py-2 text-xs shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="all">همه انواع تراکنش</option>
                        <option value="in">ورود کالا (+)</option>
                        <option value="out">خروج کالا (-)</option>
                        <option value="adjustment">تعدیل موجودی</option>
                        <option value="reserve_add">رزرو شده</option>
                        <option value="reserve_release">آزاد شده</option>
                    </select>
                </div>
            </div>

            {{-- Transactions Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs text-gray-500 dark:text-gray-400">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-[10px] font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">انبار</th>
                                <th class="px-4 py-3">محصول و تنوع</th>
                                <th class="px-4 py-3">نوع سند</th>
                                <th class="px-4 py-3">تعداد / مقدار</th>
                                <th class="px-4 py-3">قیمت واحد</th>
                                <th class="px-4 py-3">شرح / علت</th>
                                <th class="px-4 py-3">ثبت‌کننده</th>
                                <th class="px-4 py-3">تاریخ ثبت</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @forelse($this->warehouseTransactions as $tx)
                                @php
                                    $txQty = $tx->quantity;
                                    $isPositive = $txQty > 0;
                                    $absQty = abs($txQty);
                                @endphp
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">{{ $tx->warehouse->name }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-800 dark:text-gray-200">
                                            {{ $tx->productVariant->masterProduct->title ?? 'محصول نامشخص' }}
                                        </div>
                                        <div class="text-[10px] text-gray-400  mt-0.5">
                                            {{ $tx->productVariant->variant_code ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($tx->type === 'in')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400">
                                                ورود کالا
                                            </span>
                                        @elseif($tx->type === 'out')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400">
                                                خروج کالا
                                            </span>
                                        @elseif($tx->type === 'adjustment')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400">
                                                تعدیل
                                            </span>
                                        @elseif($tx->type === 'reserve_add')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400">
                                                رزرو (+)
                                            </span>
                                        @elseif($tx->type === 'reserve_release')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 dark:bg-purple-950/30 dark:text-purple-400">
                                                آزاد سازی رزرو
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3  font-bold">
                                        <span class="{{ $isPositive ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                            {{ $isPositive ? '+' : '' }}{{ $txQty }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3  font-bold text-gray-700 dark:text-gray-300">
                                        @if($tx->unit_price)
                                            {{ number_format($tx->unit_price) }} <span class="text-[9px] font-normal text-gray-400">{{ $currencyLabel }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300 max-w-[200px] truncate" title="{{ $tx->description }}">{{ $tx->description ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $tx->user->name ?? 'سیستم' }}</td>
                                    <td class="px-4 py-3  text-gray-400 text-[10px]">
                                        {{ \Morilog\Jalali\Jalalian::fromCarbon($tx->created_at)->format('Y/m/d H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">هیچ تراکنش یا سند حواله‌ای یافت نشد.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($this->warehouseTransactions->hasPages())
                <div class="mt-6">
                    {{ $this->warehouseTransactions->links() }}
                </div>
            @endif
        </div>
    @elseif($currentTab === 'transfers')
        {{-- Transfers Listing Tab --}}
        <div class="space-y-4 animate-in fade-in-50 duration-200">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs text-gray-500 dark:text-gray-400">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-[10px] font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">کد درخواست</th>
                                <th class="px-4 py-3">انبار مبدا (فرستنده)</th>
                                <th class="px-4 py-3">انبار مقصد (گیرنده)</th>
                                <th class="px-4 py-3">محصول و تنوع</th>
                                <th class="px-4 py-3">تعداد انتقال</th>
                                <th class="px-4 py-3">وضعیت</th>
                                <th class="px-4 py-3">ثبت‌کننده / تاریخ</th>
                                <th class="px-4 py-3">بررسی‌کننده / جزئیات</th>
                                <th class="px-4 py-3">عملیات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @forelse($this->warehouseTransfers as $tf)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">#{{ $tf->id }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-800 dark:text-gray-200">{{ $tf->sourceWarehouse->name }}</div>
                                        <div class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold">
                                            فروشنده: {{ $tf->sourceWarehouse->vendor->store_name ?? 'بدون فروشنده' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-bold text-gray-800 dark:text-gray-200">
                                        {{ $tf->destinationWarehouse->name }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-800 dark:text-gray-200">
                                            {{ $tf->productVariant->masterProduct->title ?? 'محصول نامشخص' }}
                                        </div>
                                        <div class="text-[10px] text-gray-400 mt-0.5">
                                            کد تنوع: {{ $tf->productVariant->variant_code ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-bold text-gray-950 dark:text-white">{{ $tf->quantity }}</td>
                                    <td class="px-4 py-3">
                                        @if($tf->status === 'pending')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400">
                                                در انتظار تایید
                                            </span>
                                        @elseif($tf->status === 'approved')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400">
                                                تایید شده
                                            </span>
                                        @elseif($tf->status === 'rejected')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400" title="{{ $tf->rejection_reason }}">
                                                رد شده
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                        <div>{{ $tf->user->name ?? 'نامشخص' }}</div>
                                        <div class="text-[10px] text-gray-400 mt-0.5">
                                            {{ \Morilog\Jalali\Jalalian::fromCarbon($tf->created_at)->format('Y/m/d H:i') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                        @if($tf->processed_by)
                                            <div>توسط: {{ $tf->processor->name ?? 'نامشخص' }}</div>
                                            <div class="text-[10px] text-gray-400 mt-0.5">
                                                {{ \Morilog\Jalali\Jalalian::fromCarbon($tf->processed_at)->format('Y/m/d H:i') }}
                                            </div>
                                            @if($tf->status === 'rejected')
                                                <div class="text-[10px] text-red-500 font-bold max-w-[150px] truncate" title="{{ $tf->rejection_reason }}">
                                                    علت: {{ $tf->rejection_reason }}
                                                </div>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($tf->status === 'pending' && auth()->user()->hasAnyRole(['super-admin', 'admin']))
                                            <div class="flex items-center gap-2">
                                                <button type="button" wire:click="approveTransferRequest({{ $tf->id }})" wire:confirm="آیا از تایید این درخواست انتقال و تغییر موجودی انبارها مطمئن هستید؟" class="px-2 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold transition-colors shadow">
                                                    تایید
                                                </button>
                                                <button type="button" wire:click="openRejectTransferForm({{ $tf->id }})" class="px-2 py-1 rounded bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-bold transition-colors shadow">
                                                    رد
                                                </button>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">هیچ درخواست انتقالی یافت نشد.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($this->warehouseTransfers->hasPages())
                <div class="mt-6">
                    {{ $this->warehouseTransfers->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- Modals --}}
    @if($showTransferVariantId)
        @php
            $transferVariant = \Modules\Market\Entities\ProductVariant::find($showTransferVariantId);
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xl max-w-md w-full overflow-hidden p-6 animate-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3 mb-4">
                    <h3 class="font-extrabold text-gray-900 dark:text-white text-base">درخواست انتقال کالا به انبار مرکزی</h3>
                    <button type="button" wire:click="closeTransferForm" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                @if($transferVariant)
                    <div class="mb-4 bg-gray-50 dark:bg-gray-900/40 p-3 rounded-xl border border-gray-100 dark:border-gray-700/50 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-900 flex items-center justify-center overflow-hidden border border-gray-200 dark:border-gray-700 shrink-0">
                            <img src="{{ $transferVariant->masterProduct->main_image_url }}" alt="" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-800 dark:text-gray-200">{{ $transferVariant->masterProduct->title }}</span>
                            <span class="block text-[10px] text-gray-400 mt-0.5">کد: {{ $transferVariant->variant_code }}</span>
                        </div>
                    </div>
                @endif

                <form wire:submit.prevent="submitTransferRequest({{ $showTransferVariantId }})">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">انبار مبدا (انبار فرستنده)</label>
                            <select wire:model.live="tfSourceWarehouseId" class="w-full rounded-lg border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                @foreach($this->relevantWarehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                                @endforeach
                            </select>
                            @error('tfSourceWarehouseId') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">انبار مقصد (انبار مرکزی سیستم)</label>
                            <select wire:model="tfDestinationWarehouseId" class="w-full rounded-lg border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-white" disabled>
                                @php
                                    $centralWh = \Modules\Market\Entities\Warehouse::whereNull('vendor_id')->where('is_active', true)->first();
                                @endphp
                                @if($centralWh)
                                    <option value="{{ $centralWh->id }}">{{ $centralWh->name }} (انبار اصلی)</option>
                                @else
                                    <option value="">انبار مرکزی یافت نشد</option>
                                @endif
                            </select>
                            @error('tfDestinationWarehouseId') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">تعداد انتقال</label>
                            <input type="number" wire:model="tfQuantity" class="w-full rounded-lg border-gray-200 bg-white px-3 py-2 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="مثال: ۱۰">
                            @error('tfQuantity') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" wire:click="closeTransferForm" class="px-4 py-2 text-xs font-bold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">انصراف</button>
                        <button type="submit" class="px-5 py-2 rounded-lg bg-indigo-600 text-white text-xs font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all">
                            ثبت درخواست
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showRejectTransferId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xl max-w-md w-full overflow-hidden p-6 animate-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3 mb-4">
                    <h3 class="font-extrabold text-gray-900 dark:text-white text-base text-red-600 dark:text-red-400">رد درخواست انتقال کالا</h3>
                    <button type="button" wire:click="closeRejectTransferForm" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="submitRejection">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">علت رد درخواست انتقال</label>
                            <textarea wire:model="rejectReason" rows="3" class="w-full rounded-lg border-gray-200 bg-white px-3 py-2 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="لطفاً علت رد درخواست انتقال را بنویسید (مثال: عدم هماهنگی، کسر موجودی فیزیکی و ...)"></textarea>
                            @error('rejectReason') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" wire:click="closeRejectTransferForm" class="px-4 py-2 text-xs font-bold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">انصراف</button>
                        <button type="submit" class="px-5 py-2 rounded-lg bg-rose-600 text-white text-xs font-bold shadow-md shadow-rose-500/20 hover:bg-rose-700 transition-all">
                            رد درخواست
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
</div>
