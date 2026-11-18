<div class="space-y-5 font-sans" x-data="fleetMapHandler(@js($mapConfig), @js($serializedTechs), @js($serializedOrders))" x-init="initMap()">

    {{-- ========================================================================= --}}
    {{-- 1. HEADER BANNER & COMMAND CONTROLS                                       --}}
    {{-- ========================================================================= --}}
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 p-5 sm:p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl font-bold shrink-0">
                        📡
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-black text-slate-900 dark:text-white">مرکز عملیات و مانیتورینگ زنده ناوگان (GIS)</h1>
                            <span class="flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold border border-emerald-100 dark:border-emerald-500/20">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                رادار زنده
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                            پایش لحظه‌ای موقعیت تکنسین‌ها، کنترل امواج رادار و ناوبری اعزام سفارشات در مناطق و شهرهای تحت پوشش
                        </p>
                    </div>
                </div>
            </div>

            {{-- Quick action buttons --}}
            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button" wire:click="toggleSidebar"
                        class="px-4 py-2.5 rounded-2xl border transition flex items-center gap-2 text-xs font-bold {{ $showSidebar ? 'bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-500/30' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-600' }}">
                    <span>📑</span>
                    <span>{{ $showSidebar ? 'بستن پنل ناوگان' : 'نمایش پنل ناوگان' }}</span>
                </button>

                <a href="{{ route('user.fieldservice.orders.index') }}"
                   class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center gap-2">
                    <span>📦</span>
                    <span>سفارش‌ها و ماموریت‌ها</span>
                </a>

                <a href="{{ route('user.fieldservice.settings.index') }}"
                   class="px-3.5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 transition"
                   title="تنظیمات نقشه و مناطق تحت پوشش">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
            </div>
        </div>

        {{-- Top KPI Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100 dark:border-slate-700/50">
            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">تکنسین آزاد و آماده</div>
                    <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1 font-sans">{{ number_format($stats['online_free']) }}</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-base">
                    🟢
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">در حال انجام ماموریت</div>
                    <div class="text-xl font-black text-amber-600 dark:text-amber-400 mt-1 font-sans">{{ number_format($stats['busy_count']) }}</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center text-base">
                    🟡
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">سفارش‌های فعال رادار</div>
                    <div class="text-xl font-black text-indigo-600 dark:text-indigo-400 mt-1 font-sans">{{ number_format($stats['orders_count']) }}</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-base">
                    📦
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/50 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-rose-600 dark:text-rose-400">اعزام فوری اضطراری</div>
                    <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-1 font-sans flex items-center gap-1.5">
                        <span>{{ number_format($stats['emergency_count']) }}</span>
                        @if($stats['emergency_count'] > 0)
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                        @endif
                    </div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center text-base">
                    🚨
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. ADVANCED MULTI-DIMENSIONAL FILTER TOOLBAR                              --}}
    {{-- ========================================================================= --}}
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/60 p-4 sm:p-5 shadow-sm space-y-3.5">
        
        {{-- Row 1: Geographic & Category Dropdowns --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            {{-- Province selector --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">استان:</label>
                <select wire:model.live="selectedProvince"
                        class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="all">📍 تمامی استان‌ها</option>
                    @foreach($availableProvinces as $prov)
                        <option value="{{ $prov }}">{{ $prov }}</option>
                    @endforeach
                </select>
            </div>

            {{-- City selector --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">شهر:</label>
                <select wire:model.live="selectedCity"
                        class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="all">🏙️ همه شهرهای این محدوده</option>
                    @foreach($availableCities as $city)
                        <option value="{{ $city }}">{{ $city }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Technical Category / Skill selector --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">رسته و خدمت فنی:</label>
                <select wire:model.live="selectedCategory"
                        class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="all">🛠️ همه حوزه‌ها و تخصص‌ها</option>
                    @foreach($allCategories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->icon ?: '🛠️' }} {{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Technician Status selector --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">وضعیت تکنسین:</label>
                <select wire:model.live="techStatusFilter"
                        class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="all">همه تکنسین‌ها</option>
                    <option value="online_free">🟢 آزاد و آماده اعزام</option>
                    <option value="busy">🟡 در حال ماموریت</option>
                    <option value="offline">⚪ آفلاین و در استراحت</option>
                </select>
            </div>

            {{-- Order Priority selector --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">فوریت و وضعیت سفارش:</label>
                <select wire:model.live="orderPriorityFilter"
                        class="w-full h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="all">همه سفارش‌ها</option>
                    <option value="emergency">🚨 فقط اعزام فوری اضطراری</option>
                    <option value="searching">📡 در حال جستجوی رادار</option>
                    <option value="in_progress">🔧 در مسیر یا در حال تعمیر</option>
                </select>
            </div>
        </div>

        {{-- Row 2: Search Input & Quick Map Navigation Buttons --}}
        <div class="flex flex-col md:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-100 dark:border-slate-700/50">
            {{-- Search input --}}
            <div class="relative w-full md:w-96">
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="جستجوی نام تکنسین، مشتری، تلفن یا کد سفارش..."
                       class="w-full h-10 pr-10 pl-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs font-sans text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 text-slate-400 absolute right-3.5 top-3 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            {{-- Quick Map View Buttons --}}
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto justify-end">
                <button type="button" @click="fitCoverage()"
                        class="h-10 px-3.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-500/15 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30 text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                    <span>🎯</span>
                    <span>محدوده مناطق فعال</span>
                </button>

                <button type="button" @click="fitIran()"
                        class="h-10 px-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center gap-1.5">
                    <span>🗺️</span>
                    <span>نمای کلی کشور (ایران)</span>
                </button>

                @if($selectedProvince !== 'all' || $selectedCity !== 'all' || $selectedCategory !== 'all' || $techStatusFilter !== 'all' || $orderPriorityFilter !== 'all' || !empty($search))
                <button type="button" wire:click="resetFilters"
                        class="h-10 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-500/15 dark:hover:bg-rose-500/25 text-rose-600 dark:text-rose-400 text-xs font-bold transition flex items-center gap-1 border border-rose-200 dark:border-rose-500/30">
                    <span>✕</span>
                    <span>پاکسازی فیلترها</span>
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. COMMAND CENTER VIEWPORT: GIS MAP + INTERACTIVE FLEET DRAWER            --}}
    {{-- ========================================================================= --}}
    <div class="relative w-full rounded-3xl overflow-hidden border border-slate-200/80 dark:border-slate-700/80 shadow-xl bg-slate-100 dark:bg-slate-900 flex flex-col lg:flex-row" style="min-height: 640px; height: 72vh;">
        
        {{-- Right/Main: Leaflet Map Container --}}
        <div class="relative flex-1 h-full min-h-[400px]">
            <div id="fleetMap" class="w-full h-full z-0" wire:ignore></div>

            {{-- Floating Overlay Counter --}}
            <div class="absolute top-4 right-4 z-[400] bg-white/95 dark:bg-slate-900/95 backdrop-blur-md p-3 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xl flex items-center gap-3.5 text-xs font-sans">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-slate-600 dark:text-slate-300 font-bold">آزاد:</span>
                    <span class="font-black text-slate-900 dark:text-white">{{ $stats['online_free'] }}</span>
                </div>
                <span class="w-px h-3.5 bg-slate-200 dark:bg-slate-700"></span>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span class="text-slate-600 dark:text-slate-300 font-bold">ماموریت:</span>
                    <span class="font-black text-slate-900 dark:text-white">{{ $stats['busy_count'] }}</span>
                </div>
                <span class="w-px h-3.5 bg-slate-200 dark:bg-slate-700"></span>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                    <span class="text-slate-600 dark:text-slate-300 font-bold">سفارش‌ها:</span>
                    <span class="font-black text-slate-900 dark:text-white">{{ $stats['orders_count'] }}</span>
                </div>
                @if($stats['emergency_count'] > 0)
                <span class="w-px h-3.5 bg-slate-200 dark:bg-slate-700"></span>
                <div class="flex items-center gap-1.5 text-rose-600 dark:text-rose-400 font-bold">
                    <span>🚨</span>
                    <span>{{ $stats['emergency_count'] }} فوری</span>
                </div>
                @endif
            </div>

            {{-- Map Provider Badge --}}
            <div class="absolute bottom-3 left-3 z-[400] bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm px-3 py-1.5 rounded-xl border border-slate-200/70 dark:border-slate-800 text-[11px] text-slate-600 dark:text-slate-300 shadow-md flex items-center gap-2">
                <span>سرویس نقشه:</span>
                <strong class="text-slate-900 dark:text-white font-bold">{{ $mapConfig['name'] }}</strong>
            </div>
        </div>

        {{-- Left/Side: Interactive Fleet Drawer / Sidebar --}}
        @if($showSidebar)
        <div class="w-full lg:w-96 bg-white dark:bg-slate-900 border-t lg:border-t-0 lg:border-r border-slate-200/80 dark:border-slate-800 flex flex-col z-10 shadow-2xl overflow-hidden shrink-0">
            
            {{-- Drawer Tabs --}}
            <div class="p-3 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2 bg-slate-50/70 dark:bg-slate-950/40">
                <button type="button" wire:click="$set('activeSideTab', 'techs')"
                        class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 {{ $activeSideTab === 'techs' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-100' }}">
                    <span>🔧 تکنسین‌ها</span>
                    <span class="px-1.5 py-0.2 rounded-md text-[10px] {{ $activeSideTab === 'techs' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                        {{ count($technicians) }}
                    </span>
                </button>

                <button type="button" wire:click="$set('activeSideTab', 'orders')"
                        class="flex-1 py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 {{ $activeSideTab === 'orders' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-100' }}">
                    <span>📦 ماموریت‌ها</span>
                    <span class="px-1.5 py-0.2 rounded-md text-[10px] {{ $activeSideTab === 'orders' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                        {{ count($activeOrders) }}
                    </span>
                </button>
            </div>

            {{-- Drawer Content List --}}
            <div class="flex-1 overflow-y-auto p-3 space-y-2.5 divide-y divide-slate-100 dark:divide-slate-800/60" style="max-height: 570px;">
                
                {{-- TAB 1: TECHNICIANS LIST --}}
                @if($activeSideTab === 'techs')
                    @forelse($technicians as $tech)
                    <div class="pt-2.5 first:pt-0">
                        <div class="p-3 rounded-2xl border transition-all cursor-pointer {{ $focusedEntityId === $tech->id && $focusedEntityType === 'tech' ? 'bg-indigo-50/80 dark:bg-indigo-500/15 border-indigo-300 dark:border-indigo-500/40 shadow-md' : 'bg-white dark:bg-slate-800/80 hover:bg-slate-50 dark:hover:bg-slate-800 border-slate-200/70 dark:border-slate-700/60' }}"
                             wire:click="focusEntity('tech', {{ $tech->id }}, {{ $tech->lat }}, {{ $tech->lng }}, '{{ addslashes($tech->full_name) }}')">
                            
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm shrink-0 {{ $tech->is_busy ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30' : ($tech->is_online ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 border border-slate-200 dark:border-slate-600') }}">
                                        🔧
                                    </div>
                                    <div>
                                        <div class="font-black text-xs text-slate-900 dark:text-white flex items-center gap-1.5">
                                            <span>{{ $tech->full_name }}</span>
                                            <span class="text-[9px] px-1.5 py-0.2 rounded font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                                {{ $tech->tier?->label() ?: 'نقره‌ای' }}
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-sans mt-0.5" dir="ltr">
                                            {{ $tech->mobile }}
                                        </div>
                                    </div>
                                </div>

                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 {{ $tech->is_busy ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/25' : ($tech->is_online ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/25' : 'bg-slate-100 dark:bg-slate-700 text-slate-500') }}">
                                    {{ $tech->is_busy ? 'در ماموریت' : ($tech->is_online ? 'آماده به‌کار' : 'آفلاین') }}
                                </span>
                            </div>

                            <div class="mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-700/50 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                                <div class="flex items-center gap-1">
                                    <span>📍</span>
                                    <span>{{ $tech->city ?: 'محدوده ثبت نشده' }}</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="text-amber-500 font-bold font-sans">★ {{ $tech->rating_avg }}</span>
                                    <button type="button" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                                        فوکوس نقشه ↗
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-400 space-y-2">
                        <div class="text-2xl">🔍</div>
                        <p class="text-xs font-bold">تکنسینی با این فیلترها یافت نشد</p>
                    </div>
                    @endforelse
                @endif

                {{-- TAB 2: ACTIVE ORDERS LIST --}}
                @if($activeSideTab === 'orders')
                    @forelse($activeOrders as $ord)
                    <div class="pt-2.5 first:pt-0">
                        <div class="p-3 rounded-2xl border transition-all cursor-pointer {{ $focusedEntityId === $ord->id && $focusedEntityType === 'order' ? 'bg-indigo-50/80 dark:bg-indigo-500/15 border-indigo-300 dark:border-indigo-500/40 shadow-md' : 'bg-white dark:bg-slate-800/80 hover:bg-slate-50 dark:hover:bg-slate-800 border-slate-200/70 dark:border-slate-700/60' }}"
                             wire:click="focusEntity('order', {{ $ord->id }}, {{ $ord->lat }}, {{ $ord->lng }}, '{{ addslashes($ord->order_number) }}')">
                            
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-black text-xs font-sans text-indigo-600 dark:text-indigo-400">{{ $ord->order_number }}</span>
                                        @if($ord->is_emergency)
                                        <span class="px-1.5 py-0.2 rounded bg-rose-500/15 text-rose-600 dark:text-rose-400 text-[10px] font-black border border-rose-500/25 animate-pulse">
                                            🚨 فوری
                                        </span>
                                        @endif
                                    </div>
                                    <div class="font-bold text-xs text-slate-900 dark:text-white mt-1">
                                        {{ $ord->customer_name }}
                                    </div>
                                </div>

                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-500/20">
                                    {{ $ord->status?->label() ?: 'در جریان' }}
                                </span>
                            </div>

                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 truncate">
                                <span>{{ $ord->category?->icon ?: '🛠️' }}</span>
                                <span>{{ $ord->category?->name ?: 'خدمت فنی' }}</span>
                                <span>• {{ $ord->city }}</span>
                            </div>

                            <div class="mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-700/50 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                                <span class="truncate max-w-[170px]" title="{{ $ord->address }}">{{ $ord->address ?: 'آدرس ثبت نشده' }}</span>
                                <button type="button" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline shrink-0">
                                    فوکوس نقشه ↗
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-400 space-y-2">
                        <div class="text-2xl">📦</div>
                        <p class="text-xs font-bold">سفارش فعالی با این فیلترها یافت نشد</p>
                    </div>
                    @endforelse
                @endif

            </div>
        </div>
        @endif

    </div>

</div>
