@php
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm font-sans text-center focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/60 dark:text-slate-100 transition-all";
    $labelClass = "block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1";
    $currencySetting = \Modules\Market\Entities\MarketSetting::getValue('general.currency', 'toman');
    $currencyLabel = $currencySetting === 'toman' ? 'تومان' : ($currencySetting === 'rial' ? 'ریال' : $currencySetting);
    $isAdmin = $this->isAdminUser();
    $isVendorRoute = request()->routeIs('user.market.vendor.*') || request()->is('user/market/vendor/*');
    $hasActiveProductFilters = !empty($search) || $stockStatusFilter !== 'all' || !empty($brandFilter) || !empty($categoryFilter) || $sortBy !== 'latest';
@endphp

<div class="space-y-5 pb-24 font-sans">
    {{-- Header & Quick Navigation --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-500/15 border border-indigo-100 dark:border-indigo-500/30 flex items-center justify-center text-indigo-600 dark:text-indigo-300 shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
                انبارگردانی و مدیریت موجودی (WMS)
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1.5">
                کنترل یکپارچه موجودی فیزیکی قفسه، موجودی آنلاین سایت، ثبت اسناد حواله و انتقال بین انبارها
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @if($isVendorRoute || !$isAdmin)
                <a href="{{ route('user.market.vendor.warehouses.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-bold text-slate-700 dark:text-slate-200 hover:border-indigo-400 dark:hover:border-indigo-500 transition-all shadow-sm">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    انبارهای من
                </a>
                <a href="{{ route('user.market.vendor.products.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 border border-indigo-200/70 dark:border-indigo-500/30 text-xs font-bold text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    محصولات و قیمت‌ها
                </a>
            @else
                <a href="{{ route('user.market.warehouses.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-bold text-slate-700 dark:text-slate-200 hover:border-indigo-400 dark:hover:border-indigo-500 transition-all shadow-sm">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    مدیریت انبارها
                </a>
            @endif
        </div>
    </div>

    {{-- WMS Stats Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        {{-- Card 1: Warehouses count --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 flex items-center justify-center text-indigo-600 dark:text-indigo-300 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
            </div>
            <div>
                <span class="block text-[11px] text-slate-500 dark:text-slate-300 font-bold">انبارهای هدف</span>
                <div class="flex items-baseline gap-1.5 mt-0.5">
                    <span class="text-lg font-extrabold text-gray-900 dark:text-white">{{ number_format($this->wmsStats['total_warehouses']) }}</span>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">({{ number_format($this->wmsStats['active_warehouses']) }} فعال)</span>
                </div>
            </div>
        </div>

        {{-- Card 2: Physical stock --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 flex items-center justify-center text-emerald-600 dark:text-emerald-300 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
            </div>
            <div>
                <span class="block text-[11px] text-slate-500 dark:text-slate-300 font-bold">کل موجودی فیزیکی</span>
                <div class="flex items-baseline gap-1.5 mt-0.5">
                    <span class="text-lg font-extrabold text-gray-900 dark:text-white">{{ number_format($this->wmsStats['total_physical']) }}</span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400">عدد در قفسه</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Online stock --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-500/15 flex items-center justify-center text-blue-600 dark:text-blue-300 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
            </div>
            <div>
                <span class="block text-[11px] text-slate-500 dark:text-slate-300 font-bold">موجودی آنلاین (سایت)</span>
                <div class="flex items-baseline gap-1.5 mt-0.5">
                    <span class="text-lg font-extrabold text-indigo-600 dark:text-indigo-400">{{ number_format($this->wmsStats['total_online']) }}</span>
                    <span class="text-[10px] text-indigo-500 dark:text-indigo-300">قابل فروش</span>
                </div>
            </div>
        </div>

        {{-- Card 4: Reserved stock --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-500/15 flex items-center justify-center text-amber-600 dark:text-amber-300 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
            </div>
            <div>
                <span class="block text-[11px] text-slate-500 dark:text-slate-300 font-bold">موجودی رزرو شده</span>
                <div class="flex items-baseline gap-1.5 mt-0.5">
                    <span class="text-lg font-extrabold text-amber-600 dark:text-amber-400">{{ number_format($this->wmsStats['total_reserved']) }}</span>
                    <span class="text-[10px] text-amber-600 dark:text-amber-400">سفارش باز</span>
                </div>
            </div>
        </div>

        {{-- Card 5: Net available stock --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-500/15 flex items-center justify-center text-purple-600 dark:text-purple-300 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
            </div>
            <div>
                <span class="block text-[11px] text-slate-500 dark:text-slate-300 font-bold">موجودی خالص واقعی</span>
                <div class="flex items-baseline gap-1.5 mt-0.5">
                    <span class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400">{{ number_format(max(0, $this->wmsStats['total_online'] - $this->wmsStats['total_reserved'])) }}</span>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400">آزاد</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Scope Selector for Admin OR Multi-Warehouse Selector for Vendor --}}
    @if($isAdmin)
        <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl p-4 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 flex items-center justify-center text-indigo-600 dark:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">محدوده نظارت و انبارگردانی</h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-300 mt-0.5">انتخاب بین انبار مرکزی سیستم، انبارهای فروشندگان یا فیلتر روی یک انبار مشخص</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="flex bg-gray-100 dark:bg-gray-900 p-1 rounded-xl border border-gray-200/70 dark:border-gray-700">
                    <button type="button" wire:click="setWarehouseFilter('central')" class="px-3.5 py-2 rounded-lg text-xs font-bold transition-all {{ $warehouseFilter === 'central' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white' }}">
                        انبار مرکزی سیستم
                    </button>
                    <button type="button" wire:click="setWarehouseFilter('vendors')" class="px-3.5 py-2 rounded-lg text-xs font-bold transition-all {{ $warehouseFilter === 'vendors' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white' }}">
                        انبارهای فروشندگان
                    </button>
                    <button type="button" wire:click="setWarehouseFilter('all')" class="px-3.5 py-2 rounded-lg text-xs font-bold transition-all {{ $warehouseFilter === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white' }}">
                        کل انبارها (ادغام‌شده)
                    </button>
                </div>

                @if($warehouseFilter === 'vendors' || $warehouseFilter === 'all')
                    <select wire:model.live="selectedVendorId" class="rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs font-bold text-slate-700 dark:border-gray-700 dark:bg-gray-900 dark:text-slate-200 min-w-[160px] focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">همه فروشندگان</option>
                        @foreach($this->vendorsList as $vnd)
                            <option value="{{ $vnd->id }}">{{ $vnd->store_name }}</option>
                        @endforeach
                    </select>
                @endif

                <select wire:model.live="selectedWarehouseId" class="rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs font-bold text-slate-700 dark:border-gray-700 dark:bg-gray-900 dark:text-slate-200 min-w-[170px] focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">همه انبارهای این بخش</option>
                    @foreach($this->allWarehousesList as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    @elseif($this->userAccessibleWarehouses->count() > 1)
        {{-- Vendor Warehouse Switcher --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl p-3.5 shadow-sm flex flex-wrap items-center justify-between gap-3">
            <span class="text-xs font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                فیلتر بر اساس انبار شما:
            </span>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="clearWarehouseFilter" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ empty($selectedWarehouseId) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-900 text-slate-600 dark:text-slate-300' }}">
                    همه انبارهای من ({{ $this->userAccessibleWarehouses->count() }})
                </button>
                @foreach($this->userAccessibleWarehouses as $wh)
                    <button type="button" wire:click="$set('selectedWarehouseId', '{{ $wh->id }}')" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ (string)$selectedWarehouseId === (string)$wh->id ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-900 text-slate-600 dark:text-slate-300' }}">
                        {{ $wh->name }} ({{ $wh->code }})
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Active Single Warehouse Banner (Visible to both Admin & Vendor when selectedWarehouseId is set) --}}
    @if($selectedWarehouseId)
        @php
            $activeWh = \Modules\Market\Entities\Warehouse::with('vendor')->find($selectedWarehouseId);
        @endphp
        @if($activeWh)
            <div class="bg-indigo-50/80 dark:bg-indigo-950/30 border border-indigo-200/80 dark:border-indigo-800/60 p-3.5 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-bold text-indigo-950 dark:text-indigo-200">تمرکز انبارگردانی روی:</span>
                            <span class="text-sm font-extrabold text-indigo-700 dark:text-indigo-300">{{ $activeWh->name }}</span>
                            <span class="text-[11px] font-bold bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-300 px-2 py-0.5 rounded-lg border border-indigo-200 dark:border-indigo-700">کد: {{ $activeWh->code }}</span>
                            @if($activeWh->vendor)
                                <span class="text-[11px] font-bold bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded-lg">فروشگاه: {{ $activeWh->vendor->store_name }}</span>
                            @else
                                <span class="text-[11px] font-bold bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 px-2 py-0.5 rounded-lg">انبار مرکزی سیستم</span>
                            @endif
                        </div>
                    </div>
                </div>
                <button type="button" wire:click="clearWarehouseFilter" class="self-end sm:self-auto inline-flex items-center gap-1.5 text-xs font-bold text-rose-600 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/40 bg-white dark:bg-gray-800 px-3 py-1.5 rounded-xl shadow-sm border border-rose-200 dark:border-rose-800/60 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    نمایش همه انبارها
                </button>
            </div>
        @endif
    @endif

    {{-- Main Navigation Tabs --}}
    <div class="flex flex-wrap border-b border-gray-200 dark:border-gray-700 gap-1">
        @if($isAdmin && $warehouseFilter === 'vendors')
            <button type="button" wire:click="setTab('warehouses')" class="py-3 px-4 text-xs sm:text-sm font-bold border-b-2 transition-all flex items-center gap-2 {{ $currentTab === 'warehouses' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-300 bg-indigo-50/40 dark:bg-indigo-500/10 rounded-t-xl' : 'border-transparent text-slate-600 hover:text-gray-900 dark:text-slate-300 dark:hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                لیست انبارهای فروشندگان
            </button>
        @endif
        <button type="button" wire:click="setTab('stock')" class="py-3 px-4 text-xs sm:text-sm font-bold border-b-2 transition-all flex items-center gap-2 {{ $currentTab === 'stock' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-300 bg-indigo-50/40 dark:bg-indigo-500/10 rounded-t-xl' : 'border-transparent text-slate-600 hover:text-gray-900 dark:text-slate-300 dark:hover:text-white' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            موجودی و انبارگردانی محصولات
        </button>
        <button type="button" wire:click="setTab('transactions')" class="py-3 px-4 text-xs sm:text-sm font-bold border-b-2 transition-all flex items-center gap-2 {{ $currentTab === 'transactions' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-300 bg-indigo-50/40 dark:bg-indigo-500/10 rounded-t-xl' : 'border-transparent text-slate-600 hover:text-gray-900 dark:text-slate-300 dark:hover:text-white' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            اسناد حواله و گردش انبار (تراکنش‌ها)
        </button>
        <button type="button" wire:click="setTab('transfers')" class="py-3 px-4 text-xs sm:text-sm font-bold border-b-2 transition-all flex items-center gap-2 {{ $currentTab === 'transfers' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-300 bg-indigo-50/40 dark:bg-indigo-500/10 rounded-t-xl' : 'border-transparent text-slate-600 hover:text-gray-900 dark:text-slate-300 dark:hover:text-white' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            انتقال کالا بین انبارها
        </button>
    </div>

    {{-- TAB 1: Warehouses Overview --}}
    @if($currentTab === 'warehouses' && $isAdmin && $warehouseFilter === 'vendors')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($this->vendorWarehousesStats as $wh)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-3xl p-5 shadow-sm hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-500 transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3 mb-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 flex items-center justify-center text-indigo-600 dark:text-indigo-300 shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 dark:text-white text-sm">{{ $wh['name'] }}</h3>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400">کد: {{ $wh['code'] }}</span>
                                </div>
                            </div>
                            <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full {{ $wh['is_active'] ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300' }}">
                                {{ $wh['is_active'] ? 'فعال' : 'غیرفعال' }}
                            </span>
                        </div>

                        <div class="space-y-2 mb-5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500 dark:text-slate-400">مالک (فروشنده):</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $wh['vendor_name'] }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500 dark:text-slate-400">تنوع‌های دارای موجودی:</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ number_format($wh['total_products']) }} تنوع</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700/60 text-center mt-3">
                                <div>
                                    <span class="block text-[10px] text-slate-500 dark:text-slate-400 font-bold mb-0.5">فیزیکی</span>
                                    <span class="font-extrabold text-sm text-gray-900 dark:text-white">{{ number_format($wh['total_physical']) }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] text-indigo-500 dark:text-indigo-300 font-bold mb-0.5">آنلاین</span>
                                    <span class="font-extrabold text-sm text-indigo-600 dark:text-indigo-400">{{ number_format($wh['total_online']) }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] text-amber-600 dark:text-amber-400 font-bold mb-0.5">رزرو شده</span>
                                    <span class="font-extrabold text-sm text-amber-600 dark:text-amber-400">{{ number_format($wh['total_reserved']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2 border-t border-gray-100 dark:border-gray-700/60 pt-3 mt-auto">
                        <button type="button" wire:click="selectWarehouseAndTab({{ $wh['id'] }}, 'stock')" class="flex-1 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 text-xs font-bold transition-all text-center">
                            موجودی محصولات
                        </button>
                        <button type="button" wire:click="selectWarehouseAndTab({{ $wh['id'] }}, 'transactions')" class="flex-1 py-2 rounded-xl bg-gray-100 dark:bg-gray-700/60 text-slate-700 dark:text-slate-200 hover:bg-gray-200 dark:hover:bg-gray-700 text-xs font-bold transition-all text-center">
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

    {{-- TAB 2: Stock & Inventory Audit --}}
    @elseif($currentTab === 'stock')
        {{-- Comprehensive Filter & Search Panel --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
            {{-- Top Row: Search + Quick Status Pills --}}
            <div class="flex flex-col xl:flex-row gap-3 items-stretch xl:items-center justify-between">
                <div class="relative flex-1">
                    <input type="text"
                           class="w-full rounded-xl border-gray-200 bg-gray-50/80 px-4 py-2.5 text-xs sm:text-sm focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900/60 dark:text-white pl-10"
                           placeholder="جستجو در نام محصول، کد ویژن (بارکد)، کد سیستمی (CRM)، کد تنوع یا ویژگی‌ها..."
                           wire:model.live.debounce.300ms="search">
                    @if($search)
                        <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 left-3 flex items-center text-gray-400 hover:text-rose-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    @else
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-1.5 bg-gray-100/80 dark:bg-gray-900/70 p-1.5 rounded-xl border border-gray-200/60 dark:border-gray-700/60">
                    <button type="button" wire:click="setStockStatusFilter('all')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $stockStatusFilter === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white' }}">
                        همه کالاها
                    </button>
                    <button type="button" wire:click="setStockStatusFilter('in_stock')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $stockStatusFilter === 'in_stock' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400' }}">
                        دارای موجودی
                    </button>
                    <button type="button" wire:click="setStockStatusFilter('low_stock')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $stockStatusFilter === 'low_stock' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-amber-600 dark:hover:text-amber-400' }}">
                        کمبود موجودی
                    </button>
                    <button type="button" wire:click="setStockStatusFilter('out_of_stock')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $stockStatusFilter === 'out_of_stock' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-400' }}">
                        ناموجود (نیازمند شارژ)
                    </button>
                    <button type="button" wire:click="setStockStatusFilter('has_reserved')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $stockStatusFilter === 'has_reserved' ? 'bg-purple-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-purple-600 dark:hover:text-purple-400' }}">
                        دارای رزرو
                    </button>
                    <button type="button" wire:click="setStockStatusFilter('discrepancy')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $stockStatusFilter === 'discrepancy' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                        مغایرت فیزیکی و آنلاین
                        @if($this->wmsStats['discrepancy_count'] > 0)
                            <span class="mr-1 px-1.5 py-0.2 rounded-full text-[10px] bg-amber-500 text-white">{{ $this->wmsStats['discrepancy_count'] }}</span>
                        @endif
                    </button>
                </div>
            </div>

            {{-- Second Row: Brand, Category, Sort & Reset --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-3 border-t border-gray-100 dark:border-gray-700/60">
                <div>
                    <label class="{{ $labelClass }}">فیلتر برند (گروه کالایی)</label>
                    <select wire:model.live="brandFilter" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs font-bold text-slate-700 dark:border-gray-700 dark:bg-gray-900 dark:text-slate-200">
                        <option value="">همه برندها</option>
                        @foreach($this->brandsList as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }} {{ $brand->vision_code ? "(کد: {$brand->vision_code})" : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">فیلتر دسته‌بندی (زیرگروه)</label>
                    <select wire:model.live="categoryFilter" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs font-bold text-slate-700 dark:border-gray-700 dark:bg-gray-900 dark:text-slate-200">
                        <option value="">همه دسته‌بندی‌ها</option>
                        @foreach($this->categoriesList as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }} {{ $cat->vision_code ? "({$cat->vision_code})" : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">مرتب‌سازی بر اساس</label>
                    <select wire:model.live="sortBy" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs font-bold text-slate-700 dark:border-gray-700 dark:bg-gray-900 dark:text-slate-200">
                        <option value="latest">جدیدترین کالاها</option>
                        <option value="physical_desc">بیشترین موجودی فیزیکی</option>
                        <option value="physical_asc">کمترین موجودی فیزیکی</option>
                        <option value="online_desc">بیشترین موجودی آنلاین</option>
                        <option value="reserved_desc">بیشترین موجودی رزرو شده</option>
                    </select>
                </div>

                <div class="flex items-end">
                    @if($hasActiveProductFilters)
                        <button type="button" wire:click="resetProductFilters" class="w-full py-2 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/30 dark:hover:bg-rose-900/40 text-rose-600 dark:text-rose-300 border border-rose-200/70 dark:border-rose-800/50 text-xs font-bold transition-all flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            پاکسازی فیلترهای جستجو
                        </button>
                    @else
                        <div class="w-full py-2 px-3 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/50 text-[11px] text-slate-500 dark:text-slate-400 text-center">
                            نمایش {{ number_format($this->products->total()) }} تنوع کالایی
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Product List --}}
        <div class="space-y-3.5">
            @forelse($this->products as $variant)
                @php
                    $totalPhysicalStock = (int) ($variant->total_physical_stock ?? 0);
                    $totalOnlineStock = (int) ($variant->total_online_stock ?? 0);
                    $totalReservedStock = (int) ($variant->total_reserved_stock ?? 0);
                    $netAvailableStock = max(0, $totalOnlineStock - $totalReservedStock);
                    $overallStock = $totalPhysicalStock + $totalOnlineStock;
                    $reorderPoint = (int) ($variant->vendor_reorder_point ?? $variant->reorder_point ?? 5);
                    $isLowStock = $totalPhysicalStock > 0 && $totalPhysicalStock <= $reorderPoint;
                    $hasDiscrepancy = $totalPhysicalStock !== $totalOnlineStock;
                    $isNewForVendor = !$variant->vendor_product_id;

                    $basePrice = (float) ($variant->vendor_price ?? $variant->price ?? 0);
                    $discountPrice = (float) ($variant->vendor_discount_price ?? $variant->discount_price ?? 0);
                    $isExpanded = ($editingVariantId === $variant->id || $showTxVariantId === $variant->id);
                @endphp
                <div wire:key="wms-variant-{{ $variant->id }}" class="bg-white dark:bg-gray-800 rounded-2xl border {{ $isExpanded ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-gray-100 dark:border-gray-700 hover:border-indigo-200 dark:hover:border-gray-600' }} shadow-sm overflow-hidden transition-all duration-200">

                    {{-- Product Card Header --}}
                    <div wire:click="toggleEditStock({{ $variant->id }})" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 p-4 cursor-pointer hover:bg-gray-50/70 dark:hover:bg-gray-700/40 transition-colors">
                        <div class="flex items-start sm:items-center gap-3.5">
                            <div class="w-14 h-14 rounded-xl bg-gray-100 dark:bg-gray-900 flex items-center justify-center overflow-hidden border border-gray-200 dark:border-gray-700 shrink-0">
                                @if($variant->masterProduct?->main_image_url)
                                    <img src="{{ $variant->masterProduct->main_image_url }}" alt="{{ $variant->masterProduct->title }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                @endif
                            </div>

                            <div class="space-y-1.5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-bold text-gray-900 dark:text-white text-sm sm:text-base">{{ $variant->masterProduct->title ?? 'محصول بدون عنوان' }}</h3>
                                    @if($variant->masterProduct?->brand)
                                        <span class="text-[10px] font-bold bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 px-2 py-0.5 rounded-md border border-indigo-100 dark:border-indigo-500/30">
                                            {{ $variant->masterProduct->brand->name }}
                                        </span>
                                    @endif
                                    @if($variant->masterProduct?->category)
                                        <span class="text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 px-2 py-0.5 rounded-md">
                                            {{ $variant->masterProduct->category->name }}
                                        </span>
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-center gap-1.5">
                                    @if($variant->masterProduct?->crm_code)
                                        <span class="text-[10px] font-sans bg-gray-100 dark:bg-gray-900 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded border border-gray-200/70 dark:border-gray-700">
                                            CRM: {{ $variant->masterProduct->crm_code }}
                                        </span>
                                    @endif
                                    @if($variant->masterProduct?->barcode)
                                        <span class="text-[10px] font-sans font-bold bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded border border-purple-200/60 dark:border-purple-800/60">
                                            کد ویژن: {{ $variant->masterProduct->barcode }}
                                        </span>
                                    @endif
                                    <span class="text-[10px] font-sans bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded">
                                        تنوع: {{ $variant->variant_code }}
                                    </span>

                                    @if(!empty($variant->variant_attributes) && is_array($variant->variant_attributes))
                                        @foreach($variant->variant_attributes as $attrName => $attrValue)
                                            @if(($attrName === 'name' || $attrName === 'title') && in_array($attrValue, ['استاندارد', 'اصلی'])) @continue @endif
                                            <span class="text-[10px] font-bold text-slate-600 dark:text-slate-300 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">
                                                {{ $attrName === 'title' ? $attrValue : "{$attrName}: {$attrValue}" }}
                                            </span>
                                        @endforeach
                                    @endif

                                    @if($basePrice > 0)
                                        <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded">
                                            قیمت: {{ number_format($discountPrice > 0 ? $discountPrice : $basePrice) }} {{ $currencyLabel }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Right Metrics Summary --}}
                        <div class="flex items-center justify-between lg:justify-end gap-4 sm:gap-6 w-full lg:w-auto pt-3 lg:pt-0 border-t lg:border-0 border-gray-100 dark:border-gray-700">
                            <div class="grid grid-cols-4 sm:flex items-center gap-3 sm:gap-5 flex-1 sm:flex-initial text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">فیزیکی (قفسه)</span>
                                    <span class="font-extrabold text-sm text-gray-900 dark:text-white mt-0.5">{{ number_format($totalPhysicalStock) }}</span>
                                </div>
                                <div class="flex flex-col items-center">
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">آنلاین (سایت)</span>
                                    <span class="font-extrabold text-sm text-indigo-600 dark:text-indigo-400 mt-0.5">{{ number_format($totalOnlineStock) }}</span>
                                </div>
                                <div class="flex flex-col items-center">
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">رزرو / خالص</span>
                                    <span class="font-bold text-xs text-slate-700 dark:text-slate-200 mt-0.5">
                                        <span class="text-amber-600 dark:text-amber-400" title="رزرو شده">{{ number_format($totalReservedStock) }}</span>
                                        <span class="text-gray-300 dark:text-gray-600">/</span>
                                        <span class="text-emerald-600 dark:text-emerald-400 font-extrabold" title="خالص قابل فروش">{{ number_format($netAvailableStock) }}</span>
                                    </span>
                                </div>
                                <div class="flex flex-col items-center">
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">وضعیت</span>
                                    @if($isNewForVendor)
                                        <span class="text-[11px] font-bold text-sky-600 dark:text-sky-400 mt-0.5">تنوع جدید</span>
                                    @elseif($overallStock === 0)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-600 dark:text-rose-400 mt-0.5">
                                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                            ناموجود
                                        </span>
                                    @elseif($isLowStock)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 dark:text-amber-400 mt-0.5">
                                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                            کمبود موجودی
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            موجود
                                        </span>
                                    @endif
                                    @if($hasDiscrepancy)
                                        <span class="text-[9px] font-bold text-amber-600 dark:text-amber-400">مغایرت فیزیکی/آنلاین</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="hidden sm:inline-block text-[11px] font-bold {{ $isExpanded ? 'text-indigo-600 dark:text-indigo-300' : 'text-slate-500 dark:text-slate-400' }}">
                                    {{ $isExpanded ? 'بستن پنل' : 'انبارگردانی سریع' }}
                                </span>
                                <div class="p-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300 transform transition-transform duration-300 {{ $isExpanded ? 'rotate-180 bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-300' : '' }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Accordion Body (Fast Stock Edit / Manual Voucher) --}}
                    @if($isExpanded)
                        <div class="bg-slate-50/90 dark:bg-gray-900/60 border-t border-indigo-100 dark:border-gray-700 p-4 sm:p-5">

                            @if($showTxVariantId === $variant->id)
                                {{-- Manual Transaction Voucher Form --}}
                                <form wire:submit.prevent="submitTransaction({{ $variant->id }})">
                                    <div class="mb-4 flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3">
                                        <h4 class="text-xs sm:text-sm font-extrabold text-indigo-700 dark:text-indigo-300 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            ثبت سند حواله ورود / خروج انبار (با شماره سند و قیمت واحد)
                                        </h4>
                                        <button type="button" wire:click="toggleEditStock({{ $variant->id }})" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-300 flex items-center gap-1">
                                            ← بازگشت به انبارگردانی سریع
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                        {{-- Warehouse Selector --}}
                                        <div>
                                            <label class="{{ $labelClass }}">انبار هدف</label>
                                            <select wire:model.live="txWarehouseId" class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                @foreach($this->relevantWarehouses as $wh)
                                                    <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                                                @endforeach
                                            </select>
                                            @error('txWarehouseId') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- Voucher Type (Intake / Output) --}}
                                        <div>
                                            <label class="{{ $labelClass }}">نوع حواله</label>
                                            <div class="flex gap-2">
                                                <button type="button" wire:click="$set('txType', 'in')" class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all text-center border {{ $txType === 'in' ? 'bg-emerald-600 border-emerald-600 text-white shadow-sm' : 'bg-white border-gray-200 text-slate-600 dark:bg-gray-800 dark:border-gray-700 dark:text-slate-300' }}">ورود کالا (+)</button>
                                                <button type="button" wire:click="$set('txType', 'out')" class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition-all text-center border {{ $txType === 'out' ? 'bg-rose-600 border-rose-600 text-white shadow-sm' : 'bg-white border-gray-200 text-slate-600 dark:bg-gray-800 dark:border-gray-700 dark:text-slate-300' }}">خروج کالا (-)</button>
                                            </div>
                                            @error('txType') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- Stock Type Selector --}}
                                        <div>
                                            <label class="{{ $labelClass }}">بخش انبار هدف</label>
                                            <select wire:model="txStockType" class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                <option value="both">موجودی فیزیکی و آنلاین (هر دو)</option>
                                                <option value="physical">فقط موجودی فیزیکی (قفسه)</option>
                                                <option value="online">فقط موجودی آنلاین (سایت)</option>
                                            </select>
                                            @error('txStockType') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- Quantity --}}
                                        <div>
                                            <label class="{{ $labelClass }}">تعداد / مقدار</label>
                                            <input type="number" min="1" wire:model="txQuantity" class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-xs font-bold text-slate-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-center" placeholder="مثال: 5">
                                            @error('txQuantity') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                                        {{-- Unit Price --}}
                                        <div>
                                            <label class="{{ $labelClass }}">
                                                {{ $txType === 'in' ? "قیمت خرید واحد ($currencyLabel) - اختیاری" : "قیمت فروش واحد ($currencyLabel) - اختیاری" }}
                                            </label>
                                            <input type="text"
                                                   x-data="{ val: @entangle('txUnitPrice') }"
                                                   x-on:input="val = $event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                                                   x-bind:value="val ? val.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',') : ''"
                                                   class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-xs font-bold text-slate-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-center"
                                                   placeholder="مثال: ۱۵۰,۰۰۰">
                                            @error('txUnitPrice') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- Reason Dropdown --}}
                                        <div>
                                            <label class="{{ $labelClass }}">علت تراکنش</label>
                                            <select wire:model="txReason" class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
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
                                            @error('txReason') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- Document Ref --}}
                                        <div>
                                            <label class="{{ $labelClass }}">شماره سند مرجع / فاکتور</label>
                                            <input type="text" wire:model="txDocumentRef" class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-xs font-bold text-slate-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white" placeholder="مثال: F-1049">
                                            @error('txDocumentRef') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <label class="{{ $labelClass }}">توضیحات تکمیلی</label>
                                        <textarea wire:model="txCustomDescription" rows="2" class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-xs text-slate-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white" placeholder="جزئیات بیشتر در مورد این سند حواله دستی..."></textarea>
                                        @error('txCustomDescription') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <button type="button" wire:click="toggleEditStock({{ $variant->id }})" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-300 transition-colors">
                                            ← بازگشت به ویرایش سریع موجودی
                                        </button>
                                        <div class="flex items-center gap-2.5">
                                            <button type="button" wire:click="closeTransactionForm" class="px-4 py-2 rounded-xl bg-gray-200/70 dark:bg-gray-800 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors">انصراف</button>
                                            <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2">
                                                <span wire:loading.remove wire:target="submitTransaction({{ $variant->id }})">ثبت نهایی سند حواله</span>
                                                <span wire:loading wire:target="submitTransaction({{ $variant->id }})">در حال ثبت...</span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                {{-- Interactive Fast Stock Edit & Audit Form --}}
                                @if($isNewForVendor)
                                    <div class="mb-4 p-3.5 bg-sky-50 dark:bg-sky-950/30 border border-sky-200 dark:border-sky-800/60 rounded-xl flex items-start gap-3">
                                        <svg class="w-5 h-5 text-sky-600 dark:text-sky-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <div>
                                            <p class="text-xs sm:text-sm font-bold text-sky-900 dark:text-sky-300">این تنوع کالایی هنوز در لیست فروش شما مقداردهی نشده است.</p>
                                            <p class="text-xs text-sky-700 dark:text-sky-400 mt-0.5">با وارد کردن موجودی و ذخیره، این کالا به صورت خودکار به انبار و محصولات شما متصل خواهد شد.</p>
                                        </div>
                                    </div>
                                @endif

                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
                                        @foreach($this->relevantWarehouses as $warehouse)
                                            @php
                                                $savedWhStock = $variant->warehouseStocks->firstWhere('warehouse_id', $warehouse->id);
                                                $savedPhysical = (int) ($savedWhStock->physical_stock ?? 0);
                                                $savedOnline = (int) ($savedWhStock->online_stock ?? 0);
                                                $savedReserved = (int) ($savedWhStock->reserved_stock ?? 0);
                                            @endphp
                                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 flex flex-col gap-3.5 shadow-sm hover:border-indigo-300 dark:hover:border-indigo-500/60 transition-colors">
                                                {{-- Warehouse Card Header --}}
                                                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-2.5 gap-2">
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                                        <span class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white truncate">{{ $warehouse->name }}</span>
                                                        @if($warehouse->vendor)
                                                            <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/15 px-2 py-0.5 rounded-md shrink-0">{{ $warehouse->vendor->store_name }}</span>
                                                        @endif
                                                    </div>
                                                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 bg-gray-100 dark:bg-gray-900 px-2 py-0.5 rounded shrink-0">{{ $warehouse->code }}</span>
                                                </div>

                                                {{-- Editable Physical & Online Stock Inputs --}}
                                                <div class="grid grid-cols-2 gap-3">
                                                    {{-- Physical Stock Input --}}
                                                    <div class="bg-gray-50 dark:bg-gray-900/60 rounded-xl p-2.5 border border-gray-200/70 dark:border-gray-700">
                                                        <div class="flex items-center justify-between mb-1.5">
                                                            <span class="text-[10px] font-bold text-slate-600 dark:text-slate-300">فیزیکی (قفسه)</span>
                                                            <span class="text-[9px] text-slate-400">فعلی: {{ number_format($savedPhysical) }}</span>
                                                        </div>
                                                        <div class="flex items-center gap-1">
                                                            <button type="button" wire:click="adjustEditableStock({{ $warehouse->id }}, 'physical_stock', -1)" class="w-7 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-slate-600 dark:text-slate-200 hover:bg-rose-50 hover:text-rose-600 font-bold text-sm flex items-center justify-center shrink-0">-</button>
                                                            <input type="number" min="0"
                                                                   wire:model.live="editableStocks.{{ $warehouse->id }}.physical_stock"
                                                                   class="w-full h-8 rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-center font-extrabold text-sm text-gray-900 dark:text-white px-1 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                                            <button type="button" wire:click="adjustEditableStock({{ $warehouse->id }}, 'physical_stock', 1)" class="w-7 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-slate-600 dark:text-slate-200 hover:bg-emerald-50 hover:text-emerald-600 font-bold text-sm flex items-center justify-center shrink-0">+</button>
                                                        </div>
                                                    </div>

                                                    {{-- Online Stock Input --}}
                                                    <div class="bg-indigo-50/40 dark:bg-indigo-950/20 rounded-xl p-2.5 border border-indigo-100 dark:border-indigo-800/50">
                                                        <div class="flex items-center justify-between mb-1.5">
                                                            <span class="text-[10px] font-bold text-indigo-700 dark:text-indigo-300">آنلاین (سایت)</span>
                                                            <button type="button" wire:click="syncOnlineWithPhysical({{ $warehouse->id }})" class="text-[9px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline" title="قرار دادن موجودی آنلاین برابر با موجودی فیزیکی">
                                                                = برابر فیزیکی
                                                            </button>
                                                        </div>
                                                        <div class="flex items-center gap-1">
                                                            <button type="button" wire:click="adjustEditableStock({{ $warehouse->id }}, 'online_stock', -1)" class="w-7 h-8 rounded-lg bg-white dark:bg-gray-800 border border-indigo-200 dark:border-indigo-800 text-indigo-600 dark:text-indigo-300 hover:bg-rose-50 hover:text-rose-600 font-bold text-sm flex items-center justify-center shrink-0">-</button>
                                                            <input type="number" min="0"
                                                                   wire:model.live="editableStocks.{{ $warehouse->id }}.online_stock"
                                                                   class="w-full h-8 rounded-lg border-indigo-200 dark:border-indigo-800 bg-white dark:bg-gray-800 text-center font-extrabold text-sm text-indigo-600 dark:text-indigo-300 px-1 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                                            <button type="button" wire:click="adjustEditableStock({{ $warehouse->id }}, 'online_stock', 1)" class="w-7 h-8 rounded-lg bg-white dark:bg-gray-800 border border-indigo-200 dark:border-indigo-800 text-indigo-600 dark:text-indigo-300 hover:bg-emerald-50 hover:text-emerald-600 font-bold text-sm flex items-center justify-center shrink-0">+</button>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Warehouse Card Footer --}}
                                                <div class="flex flex-wrap justify-between items-center gap-2 text-[11px] text-slate-600 dark:text-slate-300 pt-2 border-t border-gray-100 dark:border-gray-700/60">
                                                    <div class="flex items-center gap-2">
                                                        <span>رزرو: <strong class="text-amber-600 dark:text-amber-400">{{ number_format($savedReserved) }}</strong></span>
                                                        <span>|</span>
                                                        <span>خالص: <strong class="text-emerald-600 dark:text-emerald-400">{{ number_format(max(0, $savedOnline - $savedReserved)) }}</strong></span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5">
                                                        <button type="button" wire:click="openTransferForm({{ $variant->id }}, {{ $warehouse->id }})" class="text-[10px] font-bold text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors flex items-center gap-1 bg-amber-50 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-800/50 px-2.5 py-1 rounded-lg">
                                                            <span>انتقال</span>
                                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                                        </button>
                                                        <button type="button" wire:click="openTransactionForm({{ $variant->id }}, {{ $warehouse->id }})" class="text-[10px] font-bold text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 transition-colors flex items-center gap-1 bg-indigo-50 dark:bg-indigo-500/15 border border-indigo-200/60 dark:border-indigo-500/30 px-2.5 py-1 rounded-lg">
                                                            <span>حواله دستی</span>
                                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Save Bar --}}
                                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <button type="button" wire:click="openTransactionForm({{ $variant->id }})" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-300 dark:hover:text-indigo-200 transition-colors flex items-center justify-center sm:justify-start gap-1.5 py-2">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            ثبت سند حواله با جزئیات (فاکتور خرید / ضایعات / مرجوعی)
                                        </button>
                                        <div class="flex items-center justify-end gap-2.5">
                                            <button type="button" wire:click="cancelEdit" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-gray-900 dark:text-slate-300 dark:hover:text-white transition-colors bg-gray-200/70 dark:bg-gray-800 rounded-xl">
                                                بستن
                                            </button>
                                            <button type="button" wire:click="saveStocksForVariant({{ $variant->id }})" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span wire:loading.remove wire:target="saveStocksForVariant({{ $variant->id }})">ذخیره موجودی (ثبت تعدیل انبارگردانی)</span>
                                                <span wire:loading wire:target="saveStocksForVariant({{ $variant->id }})">در حال ذخیره...</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 text-center border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col items-center">
                    <div class="w-16 h-16 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">هیچ کالا یا تنوعی یافت نشد!</h3>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1.5 max-w-md">
                        با توجه به فیلترهای انتخابی یا عبارت جستجو شده، کالایی در این انبار وجود ندارد.
                    </p>
                    @if($hasActiveProductFilters)
                        <button type="button" wire:click="resetProductFilters" class="mt-4 px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition-all">
                            نمایش همه کالاها
                        </button>
                    @endif
                </div>
            @endforelse
        </div>

        @if($this->products->hasPages())
            <div class="mt-6">
                {{ $this->products->links() }}
            </div>
        @endif

    {{-- TAB 3: Transactions & Audit Trail --}}
    @elseif($currentTab === 'transactions')
        <div class="space-y-4">
            {{-- Search & Filters for Transactions --}}
            <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="relative w-full">
                    <input type="text" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-xs font-bold focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white pl-10" placeholder="جستجو در نام کالا، کد ویژن، کد تنوع یا شرح سند..." wire:model.live.debounce.300ms="txSearch">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                </div>

                <div>
                    <select wire:model.live="txFilterWarehouseId" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-xs font-bold text-slate-700 dark:border-gray-700 dark:bg-gray-900 dark:text-slate-200">
                        <option value="">همه انبارهای مرتبط</option>
                        @foreach($this->relevantWarehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select wire:model.live="txFilterType" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-xs font-bold text-slate-700 dark:border-gray-700 dark:bg-gray-900 dark:text-slate-200">
                        <option value="all">همه انواع سند (ورود / خروج / تعدیل / رزرو)</option>
                        <option value="in">ورود کالا (+)</option>
                        <option value="out">خروج کالا (-)</option>
                        <option value="adjustment">تعدیل انبارگردانی / همگام‌سازی ویژن</option>
                        <option value="reserve_add">رزرو سفارش (+)</option>
                        <option value="reserve_release">آزادسازی رزرو (-)</option>
                    </select>
                </div>
            </div>

            {{-- Transactions Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-gray-50 dark:bg-gray-900/60 text-[11px] font-bold text-slate-700 dark:text-slate-200 border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-4 py-3.5">انبار</th>
                                <th class="px-4 py-3.5">محصول و شناسه‌ها</th>
                                <th class="px-4 py-3.5">نوع سند</th>
                                <th class="px-4 py-3.5">تعداد تغییر</th>
                                <th class="px-4 py-3.5">قیمت واحد</th>
                                <th class="px-4 py-3.5">شرح / علت تراکنش</th>
                                <th class="px-4 py-3.5">ثبت‌کننده</th>
                                <th class="px-4 py-3.5">تاریخ و ساعت</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @forelse($this->warehouseTransactions as $tx)
                                @php
                                    $txQty = (int) $tx->quantity;
                                    $isPositive = $txQty > 0;
                                @endphp
                                <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">
                                        <div>{{ $tx->warehouse->name ?? '-' }}</div>
                                        <div class="text-[10px] text-slate-400 font-normal">{{ $tx->warehouse->code ?? '' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-900 dark:text-white">
                                            {{ $tx->productVariant->masterProduct->title ?? 'محصول نامشخص' }}
                                        </div>
                                        <div class="flex items-center gap-2 text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            <span>تنوع: {{ $tx->productVariant->variant_code ?? '-' }}</span>
                                            @if($tx->productVariant?->masterProduct?->barcode)
                                                <span>| کد ویژن: {{ $tx->productVariant->masterProduct->barcode }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($tx->type === 'in')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                                ورود کالا
                                            </span>
                                        @elseif($tx->type === 'out')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">
                                                خروج کالا
                                            </span>
                                        @elseif($tx->type === 'adjustment')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                                                تعدیل موجودی
                                            </span>
                                        @elseif($tx->type === 'reserve_add')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                                                رزرو سفارش (+)
                                            </span>
                                        @elseif($tx->type === 'reserve_release')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300">
                                                آزادسازی رزرو
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-extrabold text-sm" dir="ltr">
                                        <span class="{{ $isPositive ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                            {{ $isPositive ? '+' : '' }}{{ number_format($txQty) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-bold text-slate-700 dark:text-slate-200">
                                        @if($tx->unit_price)
                                            {{ number_format($tx->unit_price) }} <span class="text-[10px] font-normal text-slate-400">{{ $currencyLabel }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-200 max-w-[260px] truncate" title="{{ $tx->description }}">
                                        {{ $tx->description ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                                        {{ $tx->user->name ?? 'سیستم / وب‌سرویس' }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-500 dark:text-slate-400 text-[11px]">
                                        {{ \Morilog\Jalali\Jalalian::fromCarbon($tx->created_at)->format('Y/m/d H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">
                                        هیچ تراکنش یا سند حواله‌ای با این مشخصات یافت نشد.
                                    </td>
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

    {{-- TAB 4: Inter-Warehouse Transfers --}}
    @elseif($currentTab === 'transfers')
        <div class="space-y-4">
            {{-- Transfer Filters --}}
            <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                <div class="relative flex-1">
                    <input type="text" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-xs font-bold focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white pl-10" placeholder="جستجو در کد درخواست، نام محصول یا کد تنوع..." wire:model.live.debounce.300ms="tfSearch">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-1.5 bg-gray-100 dark:bg-gray-900 p-1.5 rounded-xl">
                    <button type="button" wire:click="$set('tfFilterStatus', 'all')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $tfFilterStatus === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300' }}">همه درخواست‌ها</button>
                    <button type="button" wire:click="$set('tfFilterStatus', 'pending')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $tfFilterStatus === 'pending' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300' }}">در انتظار تایید</button>
                    <button type="button" wire:click="$set('tfFilterStatus', 'approved')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $tfFilterStatus === 'approved' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300' }}">تایید شده</button>
                    <button type="button" wire:click="$set('tfFilterStatus', 'rejected')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $tfFilterStatus === 'rejected' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300' }}">رد شده</button>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-gray-50 dark:bg-gray-900/60 text-[11px] font-bold text-slate-700 dark:text-slate-200 border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-4 py-3.5">کد درخواست</th>
                                <th class="px-4 py-3.5">انبار مبدا (فرستنده)</th>
                                <th class="px-4 py-3.5">انبار مقصد (گیرنده)</th>
                                <th class="px-4 py-3.5">محصول و تنوع</th>
                                <th class="px-4 py-3.5">تعداد انتقال</th>
                                <th class="px-4 py-3.5">وضعیت</th>
                                <th class="px-4 py-3.5">ثبت‌کننده / تاریخ</th>
                                <th class="px-4 py-3.5">بررسی‌کننده / جزئیات</th>
                                <th class="px-4 py-3.5">عملیات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @forelse($this->warehouseTransfers as $tf)
                                <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">#{{ $tf->id }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $tf->sourceWarehouse->name ?? '-' }}</div>
                                        <div class="text-[10px] text-indigo-600 dark:text-indigo-300 font-bold">
                                            {{ $tf->sourceWarehouse->vendor->store_name ?? 'انبار مرکزی سیستم' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">
                                        <div>{{ $tf->destinationWarehouse->name ?? '-' }}</div>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400">
                                            {{ $tf->destinationWarehouse->vendor->store_name ?? 'انبار مرکزی سیستم' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-900 dark:text-white">
                                            {{ $tf->productVariant->masterProduct->title ?? 'محصول نامشخص' }}
                                        </div>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            کد تنوع: {{ $tf->productVariant->variant_code ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-extrabold text-sm text-gray-900 dark:text-white">{{ number_format($tf->quantity) }}</td>
                                    <td class="px-4 py-3">
                                        @if($tf->status === 'pending')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                                                در انتظار تایید
                                            </span>
                                        @elseif($tf->status === 'approved')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                                تایید شده
                                            </span>
                                        @elseif($tf->status === 'rejected')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300" title="{{ $tf->rejection_reason }}">
                                                رد شده
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                                        <div>{{ $tf->user->name ?? 'نامشخص' }}</div>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            {{ \Morilog\Jalali\Jalalian::fromCarbon($tf->created_at)->format('Y/m/d H:i') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                                        @if($tf->processed_by)
                                            <div>توسط: {{ $tf->processor->name ?? 'نامشخص' }}</div>
                                            <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                {{ \Morilog\Jalali\Jalalian::fromCarbon($tf->processed_at)->format('Y/m/d H:i') }}
                                            </div>
                                            @if($tf->status === 'rejected')
                                                <div class="text-[10px] text-rose-500 font-bold max-w-[160px] truncate" title="{{ $tf->rejection_reason }}">
                                                    علت: {{ $tf->rejection_reason }}
                                                </div>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($tf->status === 'pending' && $isAdmin)
                                            <div class="flex items-center gap-2">
                                                <button type="button" wire:click="approveTransferRequest({{ $tf->id }})" wire:confirm="آیا از تایید این درخواست انتقال و جابه‌جایی موجودی انبارها مطمئن هستید؟" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold transition-colors shadow-sm">
                                                    تایید
                                                </button>
                                                <button type="button" wire:click="openRejectTransferForm({{ $tf->id }})" class="px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-bold transition-colors shadow-sm">
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
                                    <td colspan="9" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">
                                        هیچ درخواست انتقالی یافت نشد. برای ثبت درخواست انتقال جدید، از تب «موجودی محصولات» روی کالای مورد نظر کلیک کنید.
                                    </td>
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

    {{-- Transfer Request Modal --}}
    @if($showTransferVariantId)
        @php
            $transferVariant = \Modules\Market\Entities\ProductVariant::with('masterProduct')->find($showTransferVariantId);
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xl max-w-md w-full overflow-hidden p-6 animate-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3 mb-4">
                    <h3 class="font-extrabold text-gray-900 dark:text-white text-base">درخواست انتقال کالا بین انبارها</h3>
                    <button type="button" wire:click="closeTransferForm" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                @if($transferVariant)
                    <div class="mb-4 bg-gray-50 dark:bg-gray-900/50 p-3 rounded-xl border border-gray-100 dark:border-gray-700 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-900 flex items-center justify-center overflow-hidden border border-gray-200 dark:border-gray-700 shrink-0">
                            @if($transferVariant->masterProduct?->main_image_url)
                                <img src="{{ $transferVariant->masterProduct->main_image_url }}" alt="" class="w-full h-full object-cover">
                            @else
                                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            @endif
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-900 dark:text-white">{{ $transferVariant->masterProduct->title ?? '-' }}</span>
                            <span class="block text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">کد تنوع: {{ $transferVariant->variant_code }}</span>
                        </div>
                    </div>
                @endif

                <form wire:submit.prevent="submitTransferRequest({{ $showTransferVariantId }})">
                    <div class="space-y-4">
                        <div>
                            <label class="{{ $labelClass }}">انبار مبدا (فرستنده)</label>
                            <select wire:model.live="tfSourceWarehouseId" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs font-bold text-slate-700 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                @foreach($this->relevantWarehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                                @endforeach
                            </select>
                            @error('tfSourceWarehouseId') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">انبار مقصد (گیرنده)</label>
                            <select wire:model="tfDestinationWarehouseId" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-xs font-bold text-slate-700 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <option value="">انتخاب انبار مقصد...</option>
                                @foreach($this->allDestinationWarehouses as $destWh)
                                    @if((string)$destWh->id !== (string)$tfSourceWarehouseId)
                                        <option value="{{ $destWh->id }}">
                                            {{ $destWh->name }} ({{ $destWh->code }}) {{ $destWh->vendor ? "- {$destWh->vendor->store_name}" : '- انبار مرکزی سیستم' }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('tfDestinationWarehouseId') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">تعداد انتقال</label>
                            <input type="number" min="1" wire:model="tfQuantity" class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-xs font-bold text-slate-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-center" placeholder="مثال: ۱۰">
                            @error('tfQuantity') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" wire:click="closeTransferForm" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-gray-900 dark:text-slate-300 transition-colors">انصراف</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all">
                            ثبت درخواست انتقال
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Reject Transfer Modal --}}
    @if($showRejectTransferId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xl max-w-md w-full overflow-hidden p-6 animate-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3 mb-4">
                    <h3 class="font-extrabold text-rose-600 dark:text-rose-400 text-base">رد درخواست انتقال کالا</h3>
                    <button type="button" wire:click="closeRejectTransferForm" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="submitRejection">
                    <div class="space-y-4">
                        <div>
                            <label class="{{ $labelClass }}">علت رد درخواست انتقال</label>
                            <textarea wire:model="rejectReason" rows="3" class="w-full rounded-xl border-gray-200 bg-white px-3 py-2 text-xs text-slate-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="لطفاً علت رد درخواست انتقال را بنویسید (مثال: عدم هماهنگی، کسر موجودی فیزیکی و ...)"></textarea>
                            @error('rejectReason') <span class="text-[10px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" wire:click="closeRejectTransferForm" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-gray-900 dark:text-slate-300 transition-colors">انصراف</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 text-white text-xs font-bold shadow-md shadow-rose-500/20 hover:bg-rose-700 transition-all">
                            ثبت رد درخواست
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
