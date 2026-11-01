@php
    $globalSettings = [];
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $globalSettings = \Modules\Settings\Entities\Setting::pluck('value', 'key')->toArray();
        }
    } catch (\Throwable $e) {}

    $appName = $globalSettings['app_name'] ?? $globalSettings['site_title'] ?? config('app.name', 'مستر ویلا');
    $appLogo = isset($globalSettings['app_logo']) ? asset($globalSettings['app_logo']) : null;
    $pageTitle = $appName . ' . اجاره ویلا، کلبه، بومگردی و انواع اقامتگاه در شمال';

    $rentalMultiHostEnabled = $rentalMultiHostEnabled ?? false;
    $rentalModeEnabled = $rentalModeEnabled ?? true;
    try {
        if (!isset($rentalMultiHostEnabled) && \Illuminate\Support\Facades\Schema::hasTable('property_settings')) {
            $rentalMultiHostEnabled = (bool) \Modules\Properties\Entities\PropertySetting::get('rental_multi_host_enabled', 0);
        }
        if (!isset($rentalModeEnabled) && \Illuminate\Support\Facades\Schema::hasTable('property_settings')) {
            $rentalModeEnabled = (bool) \Modules\Properties\Entities\PropertySetting::get('rental_mode_enabled', 1);
        }
    } catch (\Throwable $e) {}

    $isHostRegistered = false;
    if (auth()->check()) {
        try {
            $isHostRegistered = \Modules\Properties\Entities\PropertyHost::where('user_id', auth()->id())->exists();
        } catch (\Throwable $e) {}
    }

    $clientBookings = collect();
    if (auth()->check()) {
        try {
            if (class_exists(\Modules\Properties\Entities\PropertyRentalBooking::class)) {
                $clientBookings = \Modules\Properties\Entities\PropertyRentalBooking::with(['property'])
                    ->where('user_id', auth()->id())
                    ->latest()
                    ->take(5)
                    ->get();
            }
        } catch (\Throwable $e) {}
    }
@endphp

@extends('layouts.web')

@section('title', $pageTitle)

@section('content')
<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Responsive Carousel Cards:
       - Mobile (<640px): strictly 2 complete cards + peek of 3rd card
       - Tablet (640px-767px): 2 complete cards + peek of 3rd card
       - Desktop (>=768px): strictly 3 complete cards + peek of 4th card
    */
    .section-carousel > article,
    .carousel-card-item {
        width: calc((100% - 24px) / 2.25) !important;
        min-width: calc((100% - 24px) / 2.25) !important;
        max-width: calc((100% - 24px) / 2.25) !important;
        flex-shrink: 0 !important;
    }
    @media (min-width: 640px) {
        .section-carousel > article,
        .carousel-card-item {
            width: calc((100% - 32px) / 2.35) !important;
            min-width: calc((100% - 32px) / 2.35) !important;
            max-width: calc((100% - 32px) / 2.35) !important;
        }
    }
    @media (min-width: 768px) {
        .section-carousel > article,
        .carousel-card-item {
            width: calc((100% - 48px) / 3.25) !important;
            min-width: calc((100% - 48px) / 3.25) !important;
            max-width: calc((100% - 48px) / 3.25) !important;
        }
    }
    @media (min-width: 1024px) {
        .section-carousel > article,
        .carousel-card-item {
            width: calc((100% - 60px) / 3.3) !important;
            min-width: calc((100% - 60px) / 3.3) !important;
            max-width: calc((100% - 60px) / 3.3) !important;
        }
    }

    /* Grid cards inside favorites tab expand to column size */
    #favorites-tab-grid > article {
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
    }

    /* Drag to scroll cursor, momentum and grab enhancements */
    .section-carousel {
        cursor: grab;
        user-select: none;
        -webkit-user-select: none;
        -webkit-overflow-scrolling: touch;
        touch-action: pan-y pinch-zoom;
    }
    .section-carousel.is-dragging {
        cursor: grabbing !important;
        scroll-snap-type: none !important;
        scroll-behavior: auto !important;
    }
    .section-carousel img {
        pointer-events: none;
        -webkit-user-drag: none;
        user-drag: none;
    }

    /* Uniform 2-line title height (4em = 2 lines * 2em line-height) */
    .card-title-fixed-height {
        line-height: 2em !important;
        height: 4em !important;
        min-height: 4em !important;
        max-height: 4em !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
        word-break: break-word;
    }
</style>

<div class="min-h-screen bg-white dark:bg-zinc-950 text-[#18181B] dark:text-zinc-100 font-sans selection:bg-[#007A5E] selection:text-white">

    <!-- LIVEWIRE TOP LOADING BAR -->
    <div id="wire-loading-bar" class="fixed top-0 right-0 left-0 h-0.5 bg-[#007A5E] z-[100] transition-all duration-300 opacity-0 pointer-events-none" style="width: 0%;"></div>

    <!-- MINIMAL TOAST -->
    <div id="toast-container" class="fixed top-14 left-1/2 -translate-x-1/2 z-[95] pointer-events-none transition-all duration-200 opacity-0 -translate-y-2">
        <div class="bg-[#18181B] dark:bg-zinc-800 text-white px-4 py-2 rounded-full flex items-center gap-2 text-xs font-medium shadow-lg">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            <span id="toast-message">انجام شد</span>
        </div>
    </div>

    <!-- MAIN APP SHELL -->
    <div id="app-shell-wrapper">

        <!-- MOBILE BRAND HEADER (Matching uploaded image: Logo/App Title + Subtitle on right, Login/Profile on left; scrolls away naturally) -->
        <div class="md:hidden bg-white dark:bg-zinc-950 px-4 pt-3.5 pb-2 border-b border-transparent">
            <div class="flex items-center justify-between gap-3">
                <!-- Right: Logo & Subtitle -->
                <div class="flex flex-col items-start min-w-0">
                    <a href="{{ url('/') }}" class="flex items-center gap-2 group">
                        @if($appLogo)
                            <img src="{{ $appLogo }}" alt="{{ $appName }}" class="h-7 w-auto object-contain">
                        @else
                            <span class="text-base font-black tracking-tight text-[#18181B] dark:text-zinc-100 flex items-center gap-1.5">
                                {{ $appName }}
                                <span class="w-1.5 h-1.5 rounded-full bg-[#007A5E]"></span>
                            </span>
                        @endif
                    </a>
                    <span class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400 mt-0.5 truncate max-w-[210px]">
                        اجاره ویلا، کلبه، بومگردی و انواع اقامتگاه در شمال
                    </span>
                </div>

                <!-- Left: Login / Profile Link -->
                <div class="shrink-0">
                    @if(auth()->check())
                        <a href="{{ route('user.dashboard') }}" class="flex items-center gap-1.5 text-xs font-bold text-zinc-700 dark:text-zinc-200 bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl px-2.5 py-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="max-w-[90px] truncate">{{ auth()->user()->name ?: 'حساب کاربری' }}</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="flex items-center gap-1.5 text-xs font-bold text-[#18181B] dark:text-zinc-200 hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors py-1 px-1">
                            <span>ورود یا ثبت‌نام</span>
                            <svg class="w-4 h-4 text-zinc-600 dark:text-zinc-300 rotate-180 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- MOBILE STICKY MINIMAL SEARCH PILL (<768px, docks at top-0 when brand header scrolls away) -->
        <div id="mobile-sticky-search" class="md:hidden bg-white/95 dark:bg-zinc-950/95 backdrop-blur-md sticky top-0 z-40 px-4 pt-2 pb-3 border-b border-[#ECECEE] dark:border-zinc-800 transition-all">
            <button type="button" onclick="openMobileSearchSheet()" class="w-full bg-[#F7F8F9] dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 rounded-2xl px-4 py-3 flex items-center justify-between text-right active:scale-[0.99] transition-transform">
                <div class="flex items-center gap-3 min-w-0">
                    <svg class="w-4 h-4 text-[#007A5E] shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                    <div class="truncate">
                        <div id="mobile-pill-dest" class="text-xs font-bold text-[#18181B] dark:text-zinc-100 truncate">جستجو در استان و شهر مقصد...</div>
                        <div id="mobile-pill-sub" class="text-[11px] text-zinc-400 font-normal truncate mt-0.5">انتخاب تاریخ و نفرات</div>
                    </div>
                </div>
                <span class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 flex items-center justify-center text-zinc-600 dark:text-zinc-300 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/>
                    </svg>
                </span>
            </button>
        </div>

        <!-- ============================================================= -->
        <!-- TAB 1: EXPLORE (کاوش)                                         -->
        <!-- ============================================================= -->
        <div id="app-tab-explore" class="block">

            <!-- DESKTOP ULTRA-MINIMAL HERO & SEARCH BAR -->
            <section class="hidden md:block pt-12 pb-8">
                <div class="max-w-4xl mx-auto px-8 text-center">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#18181B] dark:text-zinc-100 tracking-tight mb-8">
                        {{ $appName }} . اجاره ویلا، کلبه، بومگردی و انواع اقامتگاه در شمال
                    </h1>

                    <!-- Clean 3-Part Flat Search Bar -->
                    <div class="bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 rounded-2xl p-2 grid grid-cols-12 gap-2 items-center text-right shadow-sm">
                <!-- 1. Zone -->
                @php
                    $availableDests = $availableDestinations ?? [];
                    if (empty($availableDests)) {
                        $availableDests = [
                            [
                                'province' => 'مازندران',
                                'cities' => ['لفور', 'شیرگاه', 'سلمان‌شهر', 'بابل']
                            ],
                            [
                                'province' => 'تهران',
                                'cities' => ['تهران']
                            ]
                        ];
                    }
                    $allDestCities = collect($availableDests)->flatMap(function($g) {
                        return collect($g['cities'])->map(function($c) use ($g) {
                            return ['city' => $c, 'province' => $g['province']];
                        });
                    })->values()->all();

                    $jalaliNow = class_exists(\Morilog\Jalali\Jalalian::class)
                        ? \Morilog\Jalali\Jalalian::now()
                        : null;
                    $currentJalaliYear = $jalaliNow ? $jalaliNow->getYear() : 1405;
                    $currentJalaliMonth = $jalaliNow ? $jalaliNow->getMonth() : 8;
                    $currentJalaliMonthName = $jalaliNow ? $jalaliNow->format('%B') : 'آبان';
                    $currentJalaliDay = $jalaliNow ? $jalaliNow->getDay() : 9;
                    $currentMonthDays = $jalaliNow ? $jalaliNow->getMonthDays() : 30;

                    $defaultStartDay = $currentJalaliDay;
                    $defaultEndDay = min($currentMonthDays, $defaultStartDay + 3);
                    if ($defaultEndDay <= $defaultStartDay && $defaultStartDay < $currentMonthDays) {
                        $defaultEndDay = $defaultStartDay + 1;
                    }
                    $defaultNights = max(1, $defaultEndDay - $defaultStartDay);
                @endphp
                <div class="col-span-4 relative" id="desktop-dest-wrapper">
                    <button type="button" onclick="toggleDesktopPopup('dest', event)" class="w-full px-4 py-2.5 rounded-xl hover:bg-[#F7F8F9] dark:hover:bg-zinc-800 transition-colors text-right flex items-center justify-between gap-2 group">
                        <div class="min-w-0">
                            <span class="block text-[11px] text-zinc-400 font-medium">استان و شهر مقصد</span>
                            <span id="desktop-dest-label" class="block text-xs font-bold text-[#18181B] dark:text-zinc-100 mt-0.5 truncate">همه مقاصد</span>
                        </div>
                        <svg class="w-4 h-4 text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-200 transition-colors shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </button>

                    <!-- Destination Picker Dropdown (Tabs + Live Search) -->
                    <div id="popup-dest" onclick="event.stopPropagation()" class="hidden absolute top-full right-0 mt-2.5 w-84 sm:w-96 bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 rounded-3xl p-4 z-50 shadow-2xl text-right">
                        
                        <!-- 1. Live Search Input -->
                        <div class="relative mb-3">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                                </svg>
                            </div>
                            <input type="text"
                                   id="dest-search-input"
                                   oninput="filterDestinationsLive(this.value, 'desktop')"
                                   placeholder="جستجوی نام شهر یا استان..."
                                   class="w-full bg-[#F7F8F9] dark:bg-zinc-800/80 border border-[#ECECEE] dark:border-zinc-700/80 rounded-xl pr-9 pl-8 py-2 text-xs font-bold text-[#18181B] dark:text-zinc-100 placeholder-zinc-400 outline-none focus:border-[#007A5E] dark:focus:border-emerald-500 transition-colors">
                            <button type="button"
                                    id="dest-search-clear"
                                    onclick="clearDestSearch('desktop')"
                                    class="hidden absolute inset-y-0 left-0 pl-2.5 flex items-center text-xs text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                                ✕
                            </button>
                        </div>

                        <!-- 2. Province Tabs Bar (Horizontal Scrollable) -->
                        <div id="desktop-dest-tabs" class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-2 mb-2.5 border-b border-[#ECECEE] dark:border-zinc-800">
                            <button type="button"
                                    onclick="switchDestTab('all', 'desktop')"
                                    data-context="desktop"
                                    data-dest-tab="all"
                                    class="dest-tab-btn shrink-0 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-[#007A5E] text-white shadow-xs is-active">
                                همه مقاصد
                            </button>
                            @foreach($availableDests as $destGroup)
                                <button type="button"
                                        onclick="switchDestTab('{{ $destGroup['province'] }}', 'desktop')"
                                        data-context="desktop"
                                        data-dest-tab="{{ $destGroup['province'] }}"
                                        class="dest-tab-btn shrink-0 px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-[#F7F8F9] dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:text-[#18181B] dark:hover:text-white border border-[#ECECEE] dark:border-zinc-700/70">
                                    {{ $destGroup['province'] }} ({{ count($destGroup['cities']) }})
                                </button>
                            @endforeach
                        </div>

                        <!-- 3. Fixed Height Content Area (No endless scroll!) -->
                        <div class="h-64 overflow-y-auto no-scrollbar" id="desktop-dest-content-area">

                            <!-- Live Search Results (shown on search) -->
                            <div id="desktop-search-results" class="hidden">
                                <div class="text-[11px] font-bold text-zinc-400 mb-2 px-1">نتایج جستجو:</div>
                                <div id="desktop-search-items" class="grid grid-cols-2 gap-2"></div>
                                <div id="desktop-search-empty" class="hidden py-12 text-center text-xs text-zinc-400">
                                    مقصدی با این نام یافت نشد.
                                </div>
                            </div>

                            <!-- Panel: All -->
                            <div id="desktop-panel-all" class="desktop-province-panel space-y-3">
                                <button type="button" onclick="selectDestination('all', 'همه مقاصد', event)" data-dest-key="all" class="w-full flex items-center justify-between p-3 rounded-2xl bg-[#F7F8F9] dark:bg-zinc-800/60 border border-[#ECECEE] dark:border-zinc-700/70 hover:bg-[#ECFDF5] dark:hover:bg-emerald-950/40 hover:border-[#007A5E] dark:hover:border-emerald-500 transition-all text-right shadow-xs group">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-[#007A5E] dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-xs group-hover:bg-[#007A5E] group-hover:text-white transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/>
                                            </svg>
                                        </span>
                                        <div>
                                            <div class="text-xs font-black text-[#18181B] dark:text-zinc-100 group-hover:text-[#007A5E] dark:group-hover:text-emerald-400">همه مقاصد و شهرها</div>
                                            <div class="text-[10px] text-zinc-400 font-medium">مشاهده اقامتگاه‌های تمام استان‌ها</div>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-[#007A5E] dark:text-emerald-400">انتخاب</span>
                                </button>

                                <div class="space-y-2">
                                    <div class="text-[11px] font-extrabold text-zinc-400 px-1">شهرهای پرطرفدار:</div>
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach($allDestCities as $item)
                                            <button type="button" onclick="selectDestination('{{ $item['city'] }}', '{{ $item['city'] }}', event)" data-dest-key="{{ $item['city'] }}"
                                                    class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-700/80 hover:border-[#007A5E] dark:hover:border-emerald-500 hover:bg-[#ECFDF5] dark:hover:bg-emerald-950/40 text-right transition-all shadow-xs group active:scale-98">
                                                <div>
                                                    <div class="text-xs font-bold text-zinc-700 dark:text-zinc-200 group-hover:text-[#007A5E] dark:group-hover:text-emerald-400">{{ $item['city'] }}</div>
                                                    <div class="text-[10px] text-zinc-400">استان {{ $item['province'] }}</div>
                                                </div>
                                                <span class="w-1.5 h-1.5 rounded-full bg-zinc-300 dark:bg-zinc-600 group-hover:bg-[#007A5E] dark:group-hover:bg-emerald-400 shrink-0 transition-colors"></span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Panel: Per Province -->
                            @foreach($availableDests as $destGroup)
                                <div id="desktop-panel-{{ $destGroup['province'] }}" class="desktop-province-panel hidden space-y-3">
                                    <button type="button" onclick="selectDestination('prov:{{ $destGroup['province'] }}', 'استان {{ $destGroup['province'] }}', event)" data-dest-key="prov:{{ $destGroup['province'] }}"
                                            class="w-full flex items-center justify-between p-3 rounded-2xl bg-white dark:bg-zinc-800 border border-[#007A5E]/40 dark:border-emerald-700/50 hover:bg-[#ECFDF5] dark:hover:bg-emerald-950/40 text-right transition-all shadow-xs group">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-[#007A5E] shrink-0"></span>
                                            <span class="text-xs font-extrabold text-[#007A5E] dark:text-emerald-400">همه اقامتگاه‌های استان {{ $destGroup['province'] }}</span>
                                        </div>
                                        <span class="text-[11px] font-bold text-[#007A5E] dark:text-emerald-400">انتخاب کل استان</span>
                                    </button>

                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach($destGroup['cities'] as $cityName)
                                            <button type="button" onclick="selectDestination('{{ $cityName }}', '{{ $cityName }}', event)" data-dest-key="{{ $cityName }}"
                                                    class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-700/80 hover:border-[#007A5E] dark:hover:border-emerald-500 hover:bg-[#ECFDF5] dark:hover:bg-emerald-950/40 text-right transition-all shadow-xs group active:scale-98">
                                                <span class="text-xs font-bold text-zinc-700 dark:text-zinc-200 group-hover:text-[#007A5E] dark:group-hover:text-emerald-400 truncate">{{ $cityName }}</span>
                                                <span class="w-1.5 h-1.5 rounded-full bg-zinc-300 dark:bg-zinc-600 group-hover:bg-[#007A5E] dark:group-hover:bg-emerald-400 shrink-0 transition-colors"></span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                        </div>

                        <!-- Dropdown Footer Action -->
                        <div class="mt-3 pt-2.5 border-t border-[#ECECEE] dark:border-zinc-800 flex items-center justify-between text-[11px]">
                            <span id="desktop-dest-selected-hint" class="text-zinc-500 dark:text-zinc-400 truncate max-w-[190px]">
                                مقصد: همه مقاصد
                            </span>
                            <button type="button" onclick="closeAllPopups()" class="px-3.5 py-1.5 rounded-xl bg-[#007A5E] hover:bg-[#005F48] text-white text-xs font-bold transition-colors shadow-xs">
                                تایید مقصد
                            </button>
                        </div>
                    </div>
                </div>

                        <!-- 2. Date -->
                        <div class="col-span-4 relative border-x border-[#ECECEE] dark:border-zinc-800 px-2" id="desktop-date-wrapper">
                            <button type="button" onclick="toggleDesktopPopup('date', event)" class="w-full px-3 py-2.5 rounded-xl hover:bg-[#F7F8F9] dark:hover:bg-zinc-800 transition-colors text-right">
                                <span class="block text-[11px] text-zinc-400 font-medium">تاریخ اقامت</span>
                                <span id="desktop-date-label" class="block text-xs font-bold text-[#18181B] dark:text-zinc-100 mt-0.5 truncate">{{ $defaultStartDay }} تا {{ $defaultEndDay }} {{ $currentJalaliMonthName }} ({{ $defaultNights }} شب)</span>
                            </button>

                            <!-- Minimal Calendar Dropdown -->
                            <div id="popup-date" onclick="event.stopPropagation()" class="hidden absolute top-full right-0 mt-2.5 w-84 bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 rounded-3xl p-4 z-50 shadow-2xl text-right">
                                <div class="flex items-center justify-between mb-3 text-xs font-bold text-[#18181B] dark:text-zinc-100 pb-2 border-b border-[#ECECEE] dark:border-zinc-800">
                                    <span>انتخاب تاریخ اقامت</span>
                                    <span class="text-xs text-[#007A5E] dark:text-emerald-400 font-black">{{ $currentJalaliMonthName }} {{ $currentJalaliYear }}</span>
                                </div>

                                <!-- Check-in & Check-out Tabs (Generous vertical spacing & refined theme harmony) -->
                                <div class="grid grid-cols-2 gap-2.5 mb-3.5">
                                    <button type="button" id="desktop-checkin-tab-btn" onclick="setDateSelectionStep('start', event)"
                                            class="p-3 rounded-2xl border-2 border-[#007A5E] dark:border-emerald-500 bg-white dark:bg-zinc-800/90 text-right transition-all flex flex-col justify-between min-h-[72px]">
                                        <div class="flex items-center justify-between w-full">
                                            <span class="text-[11px] font-bold text-[#007A5E] dark:text-emerald-400 flex items-center gap-1.5">
                                                <span id="desktop-checkin-dot" class="w-1.5 h-1.5 rounded-full bg-[#007A5E] dark:bg-emerald-400"></span>
                                                <span id="desktop-checkin-status">تاریخ ورود</span>
                                            </span>
                                            <span id="desktop-checkin-badge" class="text-[9px] font-bold text-[#007A5E] dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.5 rounded-md">انتخاب</span>
                                        </div>
                                        <span id="desktop-checkin-val" class="text-sm font-black text-[#18181B] dark:text-zinc-100 mt-2.5 block tracking-tight">
                                            {{ $defaultStartDay }} {{ $currentJalaliMonthName }}
                                        </span>
                                    </button>
                                    <button type="button" id="desktop-checkout-tab-btn" onclick="setDateSelectionStep('end', event)"
                                            class="p-3 rounded-2xl border border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9] dark:bg-zinc-900/60 text-right hover:border-zinc-300 dark:hover:border-zinc-700 transition-all flex flex-col justify-between min-h-[72px]">
                                        <div class="flex items-center justify-between w-full">
                                            <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400 flex items-center gap-1.5">
                                                <span id="desktop-checkout-dot" class="hidden w-1.5 h-1.5 rounded-full bg-[#007A5E] dark:bg-emerald-400"></span>
                                                <span id="desktop-checkout-status">تاریخ خروج</span>
                                            </span>
                                            <span id="desktop-checkout-badge" class="hidden text-[9px] font-bold text-[#007A5E] dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.5 rounded-md">انتخاب</span>
                                        </div>
                                        <span id="desktop-checkout-val" class="text-sm font-bold text-zinc-700 dark:text-zinc-300 mt-2.5 block tracking-tight">
                                            {{ $defaultEndDay }} {{ $currentJalaliMonthName }}
                                        </span>
                                    </button>
                                </div>

                                <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-bold text-zinc-400 mb-1.5">
                                    <span>ش</span><span>ی</span><span>د</span><span>س</span><span>چ</span><span>پ</span><span>ج</span>
                                </div>
                                <div id="desktop-jalali-grid" class="grid grid-cols-7 gap-1 text-center text-xs"></div>
                                <div class="mt-3 pt-2.5 border-t border-[#ECECEE] dark:border-zinc-800 flex items-center justify-between text-[11px]">
                                    <div class="flex items-center gap-1.5 text-zinc-500 dark:text-zinc-400">
                                        <span>مدت:</span>
                                        <span id="desktop-nights-count" class="font-bold text-[#007A5E] dark:text-emerald-400">{{ $defaultNights }}</span>
                                        <span>شب</span>
                                    </div>
                                    <button type="button" onclick="closeAllPopups()" class="px-3.5 py-1.5 rounded-xl bg-[#007A5E] hover:bg-[#005F48] text-white text-xs font-bold transition-colors shadow-xs">
                                        تایید تاریخ
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Guests & Search Button -->
                        <div class="col-span-4 flex items-center justify-between pl-1">
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="updateSearchGuests(-1)" class="w-7 h-7 rounded-lg bg-[#F7F8F9] dark:bg-zinc-800 hover:bg-[#ECECEE] dark:hover:bg-zinc-700 flex items-center justify-center text-xs font-bold transition-colors">−</button>
                                <div class="text-right">
                                    <span class="block text-[10px] text-zinc-400 font-medium leading-none">ظرفیت</span>
                                    <span id="desktop-guests-label" class="text-xs font-bold text-[#18181B] dark:text-zinc-100">۲ نفر</span>
                                </div>
                                <button type="button" onclick="updateSearchGuests(1)" class="w-7 h-7 rounded-lg bg-[#F7F8F9] dark:bg-zinc-800 hover:bg-[#ECECEE] dark:hover:bg-zinc-700 flex items-center justify-center text-xs font-bold transition-colors">+</button>
                            </div>

                            <button type="button" onclick="triggerLivewireSearch()" class="bg-[#007A5E] hover:bg-[#005F48] text-white p-3 rounded-xl transition-colors shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Quick Zone Filter Pills -->
                    <div class="flex items-center justify-center flex-wrap gap-2 mt-5">
                        <button type="button" onclick="applyQuickZoneFilter('all', 'همه مقاصد', event)" data-zone-pill="all" class="zone-quick-pill px-4 py-2 rounded-full text-xs font-bold bg-[#007A5E] text-white border border-[#007A5E] shadow-sm transition-all active:scale-95">
                            همه مقاصد
                        </button>
                        @php
                            $quickCities = collect($availableDests)->flatMap(function($g) { return $g['cities']; })->unique()->take(6);
                        @endphp
                        @foreach($quickCities as $quickCity)
                            <button type="button" onclick="applyQuickZoneFilter('{{ $quickCity }}', '{{ $quickCity }}', event)" data-zone-pill="{{ $quickCity }}" class="zone-quick-pill px-4 py-2 rounded-full text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-800 hover:border-[#007A5E] dark:hover:border-emerald-500 hover:text-[#007A5E] dark:hover:text-emerald-400 shadow-xs transition-all active:scale-95">
                                {{ $quickCity }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </section>

            <!-- 4-BOX FLAT CATEGORY GRID (Responsive Mobile & Desktop) -->
            <section class="max-w-[1360px] mx-auto px-4 sm:px-8 pt-4 sm:pt-2 pb-2">
                <div class="grid grid-cols-4 gap-2.5 sm:gap-4">
                    <!-- Box 1: All -->
                    <button type="button" onclick="filterByCategory('all')" data-cat="all" class="cat-box flex flex-col items-center justify-center gap-2 py-3.5 sm:py-5 px-2 rounded-2xl border border-[#007A5E] bg-[#ECFDF5]/70 dark:bg-emerald-950/30 text-[#007A5E] dark:text-emerald-400 transition-all active:scale-[0.98]">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                        </svg>
                        <span class="text-[11px] sm:text-xs font-bold truncate">همه</span>
                    </button>

                    <!-- Box 2: Wooden Cabin -->
                    <button type="button" onclick="filterByCategory('cabin')" data-cat="cabin" class="cat-box flex flex-col items-center justify-center gap-2 py-3.5 sm:py-5 px-2 rounded-2xl border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-zinc-300 dark:hover:border-zinc-700 text-zinc-600 dark:text-zinc-400 transition-all active:scale-[0.98]">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h12a1 1 0 001-1V10M9 21v-6a3 3 0 016 0v6"/>
                        </svg>
                        <span class="text-[11px] sm:text-xs font-medium truncate">کلبه چوبی</span>
                    </button>

                    <!-- Box 3: Pool Villa -->
                    <button type="button" onclick="filterByCategory('pool')" data-cat="pool" class="cat-box flex flex-col items-center justify-center gap-2 py-3.5 sm:py-5 px-2 rounded-2xl border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-zinc-300 dark:hover:border-zinc-700 text-zinc-600 dark:text-zinc-400 transition-all active:scale-[0.98]">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5c2.25 0 2.25-1.5 4.5-1.5s2.25 1.5 4.5 1.5 2.25-1.5 4.5-1.5M3 20.25c2.25 0 2.25-1.5 4.5-1.5s2.25 1.5 4.5 1.5M7.5 11.25V5.25a2.25 2.25 0 014.5 0v6m4.5 0V6.75a2.25 2.25 0 00-4.5 0"/>
                        </svg>
                        <span class="text-[11px] sm:text-xs font-medium truncate">استخردار</span>
                    </button>

                    <!-- Box 4: Beach & Forest -->
                    <button type="button" onclick="filterByCategory('beach')" data-cat="beach" class="cat-box flex flex-col items-center justify-center gap-2 py-3.5 sm:py-5 px-2 rounded-2xl border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-zinc-300 dark:hover:border-zinc-700 text-zinc-600 dark:text-zinc-400 transition-all active:scale-[0.98]">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                        </svg>
                        <span class="text-[11px] sm:text-xs font-medium truncate">ساحلی</span>
                    </button>
                </div>
            </section>

            <!-- LISTINGS SECTION CONTAINER (Dynamic Carousels) -->
            <main class="max-w-[1360px] mx-auto px-4 sm:px-8 py-6 sm:py-10">
                <!-- Dynamic Sections Carousel Container -->
                <div id="sections-wrapper" class="space-y-12 sm:space-y-16"></div>

                <!-- Minimal Empty State -->
                <div id="empty-state" class="hidden py-20 text-center">
                    <p class="text-sm font-bold text-[#18181B] dark:text-zinc-100 mb-2">اقامتگاهی با این مشخصات یافت نشد</p>
                    <button type="button" onclick="resetAllFilters()" class="mt-2 px-5 py-2.5 rounded-xl bg-[#F7F8F9] dark:bg-zinc-800 hover:bg-[#ECECEE] dark:hover:bg-zinc-700 text-xs font-bold text-[#18181B] dark:text-zinc-100 transition-colors">
                        نمایش همه اقامتگاه‌ها
                    </button>
                </div>
            </main>
        </div>

        <!-- ============================================================= -->
        <!-- TAB 2: SAVED / FAVORITES (ذخیره‌شده)                           -->
        <!-- ============================================================= -->
        <div id="app-tab-favorites" class="hidden max-w-[1360px] mx-auto px-4 sm:px-8 py-8 min-h-[70vh]">
            <h2 class="text-lg font-extrabold text-[#18181B] dark:text-zinc-100 mb-6">اقامتگاه‌های ذخیره‌شده</h2>
            <div id="favorites-tab-grid" class="grid grid-cols-2 sm:grid-cols-3 gap-3.5 sm:gap-6"></div>
        </div>

        <!-- ============================================================= -->
        <!-- TAB 3: TRIPS (سفرها)                                          -->
        <!-- ============================================================= -->
        <div id="app-tab-trips" class="hidden max-w-xl mx-auto px-5 sm:px-8 py-8 min-h-[70vh]">
            <h2 class="text-lg font-extrabold text-[#18181B] dark:text-zinc-100 mb-6">سفرهای من</h2>

            @if($clientBookings->isNotEmpty())
                <div class="space-y-4">
                    @foreach($clientBookings as $bk)
                        <div class="border border-[#ECECEE] dark:border-zinc-800 rounded-2xl p-5 bg-white dark:bg-zinc-900 shadow-sm">
                            <div class="flex items-center justify-between pb-4 mb-4 border-b border-[#ECECEE] dark:border-zinc-800">
                                <span class="text-xs font-bold text-[#007A5E] dark:text-emerald-400">
                                    @switch($bk->status)
                                        @case('confirmed') رزرو تایید شده @break
                                        @case('checked_in') اقامت فعال @break
                                        @case('completed') پایان یافته @break
                                        @case('cancelled') لغو شده @break
                                        @default در انتظار تایید میزبان
                                    @endswitch
                                </span>
                                <span class="text-xs text-zinc-400">کد رهگیری: {{ $bk->booking_number ?? $bk->id }}</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <img src="{{ $bk->property?->featured_image_url ?? 'https://images.unsplash.com/photo-1542718610-a1d656d1884c?auto=format&fit=crop&w=240&q=80' }}" alt="{{ $bk->property?->title }}" class="w-16 h-16 rounded-xl object-cover shrink-0">
                                <div>
                                    <h3 class="text-sm font-bold text-[#18181B] dark:text-zinc-100">{{ $bk->property?->title ?? 'اقامتگاه بوم‌گردی' }}</h3>
                                    <p class="text-xs text-zinc-400 mt-1">{{ $bk->checkin_date }} تا {{ $bk->checkout_date }} ({{ $bk->nights }} شب)</p>
                                    <span class="inline-block mt-2 text-xs font-bold text-[#007A5E] dark:text-emerald-400">{{ number_format($bk->total_amount) }} تومان</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="border border-[#ECECEE] dark:border-zinc-800 rounded-2xl p-5 bg-white dark:bg-zinc-900 shadow-sm">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-[#ECECEE] dark:border-zinc-800">
                        <span class="text-xs font-bold text-[#007A5E] dark:text-emerald-400">رزرو فعال</span>
                        <span class="text-xs text-zinc-400">کد پیگیری: ۹۸۴۱۲</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <img src="https://images.unsplash.com/photo-1542718610-a1d656d1884c?auto=format&fit=crop&w=240&q=80" alt="کلبه" class="w-16 h-16 rounded-xl object-cover shrink-0">
                        <div>
                            <h3 class="text-sm font-bold text-[#18181B] dark:text-zinc-100">کلبه سوئیسی اولسبلنگاه</h3>
                            <p class="text-xs text-zinc-400 mt-1">ماسال • ۱۵ تا ۱۸ آبان (۳ شب)</p>
                            <span class="inline-block mt-2 text-xs font-bold text-[#007A5E] dark:text-emerald-400">۷,۲۰۰,۰۰۰ تومان پرداخت شده</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- ============================================================= -->
        <!-- TAB 4: PROFILE (حساب من)                                      -->
        <!-- ============================================================= -->
        <div id="app-tab-profile" class="hidden max-w-md mx-auto px-5 py-8 min-h-[70vh]">
            <h2 class="text-lg font-extrabold text-[#18181B] dark:text-zinc-100 mb-6">حساب کاربری</h2>

            <div class="border border-[#ECECEE] dark:border-zinc-800 rounded-2xl p-5 mb-4 text-center bg-white dark:bg-zinc-900 shadow-sm">
                <div class="w-16 h-16 rounded-full bg-[#ECFDF5] dark:bg-emerald-950/40 text-[#007A5E] dark:text-emerald-400 flex items-center justify-center font-bold text-xl mx-auto mb-3">
                    {{ auth()->check() ? mb_substr(auth()->user()->name ?? 'م', 0, 1) : 'م' }}
                </div>
                <h3 class="text-sm font-bold text-[#18181B] dark:text-zinc-100">{{ auth()->check() ? auth()->user()->name : 'کاربر گرامی' }}</h3>
                <span class="text-xs text-zinc-400 mt-0.5 block">{{ auth()->check() ? (auth()->user()->phone ?? auth()->user()->email) : '۰۹۱۲۰۰۰۰۰۰۰' }}</span>
            </div>

            {{-- Multi-Host Marketplace Card (سیستم چندمیزبانی) --}}
            @if($rentalMultiHostEnabled)
                <div class="p-4 rounded-2xl bg-[#ECFDF5]/70 dark:bg-emerald-950/25 border border-[#D1FAE5] dark:border-emerald-900/40 mb-4 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-black text-[#005F48] dark:text-emerald-300">سیستم چندمیزبانی (Multi-Host)</span>
                        <span class="px-2 py-0.5 rounded-full bg-[#007A5E] text-white text-[10px] font-bold">میزبانان</span>
                    </div>
                    <p class="text-[11px] text-[#007A5E]/90 dark:text-emerald-400/80 mb-3 leading-relaxed">
                        اقامتگاه، ویلا یا کلبه جنگلی خود را ثبت کنید و به شبکه میزبانان حرفه‌ای مستر ویلا لفور بپیوندید.
                    </p>
                    <a href="{{ $isHostRegistered ? route('user.properties.hosts.dashboard') : route('user.properties.hosts.register') }}" 
                       class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-4 rounded-xl bg-[#007A5E] hover:bg-[#005F48] text-white text-xs font-bold transition-colors shadow-sm">
                        <span>{{ $isHostRegistered ? 'داشبورد اختصاصی میزبانی شما' : 'میزبان شوید — ثبت اقامتگاه' }}</span>
                        <span>←</span>
                    </a>
                </div>
            @endif

            <div class="border border-[#ECECEE] dark:border-zinc-800 rounded-2xl divide-y divide-[#ECECEE] dark:divide-zinc-800 text-xs font-medium bg-white dark:bg-zinc-900 shadow-sm">
                <button type="button" onclick="switchMobileTab('trips')" class="w-full text-right p-4 hover:bg-[#F7F8F9] dark:hover:bg-zinc-800/60 flex items-center justify-between text-zinc-700 dark:text-zinc-300">
                    <span>تاریخچه سفرها و رزروها</span>
                    <span class="text-zinc-400">←</span>
                </button>
                <button type="button" onclick="switchMobileTab('favorites')" class="w-full text-right p-4 hover:bg-[#F7F8F9] dark:hover:bg-zinc-800/60 flex items-center justify-between text-zinc-700 dark:text-zinc-300">
                    <span>اقامتگاه‌های ذخیره‌شده</span>
                    <span class="text-zinc-400">←</span>
                </button>
                <a href="{{ route('properties.index') }}" class="w-full text-right p-4 hover:bg-[#F7F8F9] dark:hover:bg-zinc-800/60 flex items-center justify-between text-[#007A5E] dark:text-emerald-400 font-bold">
                    <span>مشاهده تمام فایل‌های اقامتگاه‌ها و املاک</span>
                    <span class="text-zinc-400">←</span>
                </a>
                <button type="button" onclick="showToast('پشتیبانی ۲۴ ساعته فعال است')" class="w-full text-right p-4 hover:bg-[#F7F8F9] dark:hover:bg-zinc-800/60 flex items-center justify-between text-zinc-700 dark:text-zinc-300">
                    <span>پشتیبانی و ارتباط با کارشناس</span>
                    <span class="text-zinc-400">←</span>
                </button>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- MOBILE BOTTOM NAVIGATION (Tab Bar)                            -->
        <!-- ============================================================= -->
        <nav id="mobile-bottom-nav" class="md:hidden fixed bottom-0 right-0 left-0 h-16 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-sm border-t border-[#ECECEE] dark:border-zinc-800 z-40 grid grid-cols-4 items-center px-2 select-none">
            <button type="button" onclick="switchMobileTab('explore')" id="mob-nav-explore" class="flex flex-col items-center justify-center gap-1 py-1 text-[#007A5E] dark:text-emerald-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <span class="text-[10px] font-bold">کاوش</span>
            </button>

            <button type="button" onclick="switchMobileTab('favorites')" id="mob-nav-favorites" class="flex flex-col items-center justify-center gap-1 py-1 text-zinc-400 dark:text-zinc-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                <span class="text-[10px] font-medium">ذخیره‌شده</span>
            </button>

            <button type="button" onclick="switchMobileTab('trips')" id="mob-nav-trips" class="flex flex-col items-center justify-center gap-1 py-1 text-zinc-400 dark:text-zinc-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                <span class="text-[10px] font-medium">سفرها</span>
            </button>

            <button type="button" onclick="switchMobileTab('profile')" id="mob-nav-profile" class="flex flex-col items-center justify-center gap-1 py-1 text-zinc-400 dark:text-zinc-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                <span class="text-[10px] font-medium">حساب من</span>
            </button>
        </nav>

        <!-- MINIMAL DESKTOP FOOTER -->
        <footer class="hidden md:block border-t border-[#ECECEE] dark:border-zinc-800 mt-16 py-8 text-xs text-zinc-400">
            <div class="max-w-[1360px] mx-auto px-8 flex items-center justify-between">
                <span>بوم‌گرد — سامانه رزرو اقامتگاه و کلبه‌های جنگلی</span>
                <div class="flex items-center gap-6">
                    @php
                        $footerCities = collect($availableDests)->flatMap(function($g) { return $g['cities']; })->unique()->take(4);
                    @endphp
                    @foreach($footerCities as $fCity)
                        <button type="button" onclick="selectDestination('{{ $fCity }}', '{{ $fCity }}')" class="hover:text-[#18181B] dark:hover:text-white transition-colors">{{ $fCity }}</button>
                    @endforeach
                    @if($rentalMultiHostEnabled && Route::has('user.properties.hosts.register'))
                        <a href="{{ $isHostRegistered ? route('user.properties.hosts.dashboard') : route('user.properties.hosts.register') }}" class="text-[#007A5E] dark:text-emerald-400 font-bold hover:text-emerald-600 dark:hover:text-emerald-300 transition-colors">
                            {{ $isHostRegistered ? 'داشبورد میزبانی' : 'ثبت اقامتگاه (میزبان شوید)' }}
                        </a>
                    @endif
                    <a href="{{ route('properties.index') }}" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">همه اقامتگاه‌ها</a>
                </div>
            </div>
        </footer>

    </div>

    <!-- ===================================================================== -->
    <!-- MOBILE FULLSCREEN SEARCH MODAL                                        -->
    <!-- ===================================================================== -->
    <div id="mobile-search-sheet" class="hidden fixed inset-0 z-50 bg-white dark:bg-zinc-950 flex flex-col h-full w-full overflow-hidden">
        <!-- Top App Bar -->
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-[#ECECEE] dark:border-zinc-800 shrink-0 bg-white dark:bg-zinc-950">
            <div class="flex items-center gap-2.5">
                <button type="button" onclick="closeMobileSheets()" aria-label="بستن" class="w-8 h-8 rounded-full bg-[#F7F8F9] dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 text-xs text-zinc-600 dark:text-zinc-300 flex items-center justify-center active:scale-95 transition-transform shadow-2xs">
                    ✕
                </button>
                <h3 class="text-sm font-black text-[#18181B] dark:text-zinc-100">جستجو و فیلتر اقامتگاه</h3>
            </div>
            <button type="button" onclick="resetAllFilters()" class="text-xs font-bold text-zinc-500 hover:text-rose-500 transition-colors">
                پاکسازی همه
            </button>
        </div>

        <!-- Scrollable Modal Content (Takes Full Screen!) -->
        <div class="flex-1 overflow-y-auto px-5 py-5 space-y-6 no-scrollbar">
            <!-- Section 1: Destination -->
            <div>
                <div class="flex items-center justify-between mb-2.5">
                    <span class="text-xs font-black text-[#18181B] dark:text-zinc-100">استان و شهر مقصد</span>
                    <span class="text-[11px] text-zinc-400">انتخاب مقصد سفر</span>
                </div>

                <!-- 1. Mobile Live Search Input -->
                <div class="relative mb-2.5">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-zinc-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                    </div>
                    <input type="text"
                           id="mobile-dest-search-input"
                           oninput="filterDestinationsLive(this.value, 'mobile')"
                           placeholder="جستجوی نام شهر یا استان..."
                           class="w-full bg-[#F7F8F9] dark:bg-zinc-800/80 border border-[#ECECEE] dark:border-zinc-700/80 rounded-xl pr-9 pl-8 py-2.5 text-xs font-bold text-[#18181B] dark:text-zinc-100 placeholder-zinc-400 outline-none focus:border-[#007A5E] dark:focus:border-emerald-500 transition-colors">
                    <button type="button"
                            id="mobile-dest-search-clear"
                            onclick="clearDestSearch('mobile')"
                            class="hidden absolute inset-y-0 left-0 pl-2.5 flex items-center text-xs text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        ✕
                    </button>
                </div>

                <!-- 2. Mobile Province Tabs Bar -->
                <div id="mobile-dest-tabs" class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-2 mb-2.5 border-b border-[#ECECEE] dark:border-zinc-800">
                    <button type="button"
                            onclick="switchDestTab('all', 'mobile')"
                            data-context="mobile"
                            data-dest-tab="all"
                            class="dest-tab-btn shrink-0 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all bg-[#007A5E] text-white shadow-xs is-active">
                        همه مقاصد
                    </button>
                    @foreach($availableDests as $destGroup)
                        <button type="button"
                                onclick="switchDestTab('{{ $destGroup['province'] }}', 'mobile')"
                                data-context="mobile"
                                data-dest-tab="{{ $destGroup['province'] }}"
                                class="dest-tab-btn shrink-0 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all bg-[#F7F8F9] dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:text-[#18181B] dark:hover:text-white border border-[#ECECEE] dark:border-zinc-700/70">
                            {{ $destGroup['province'] }} ({{ count($destGroup['cities']) }})
                        </button>
                    @endforeach
                </div>

                <!-- 3. Mobile Content Area -->
                <div class="h-64 overflow-y-auto no-scrollbar" id="mobile-dest-content-area">

                    <!-- Live Search Results -->
                    <div id="mobile-search-results" class="hidden">
                        <div class="text-[11px] font-bold text-zinc-400 mb-2 px-1">نتایج جستجو:</div>
                        <div id="mobile-search-items" class="grid grid-cols-2 gap-1.5"></div>
                        <div id="mobile-search-empty" class="hidden py-10 text-center text-xs text-zinc-400">
                            مقصدی با این نام یافت نشد.
                        </div>
                    </div>

                    <!-- Panel: All -->
                    <div id="mobile-panel-all" class="mobile-province-panel space-y-2.5">
                        <button type="button" onclick="selectDestination('all', 'همه مقاصد', event)" data-dest-key="all" class="w-full flex items-center justify-between p-3 rounded-2xl bg-[#F7F8F9] dark:bg-zinc-800/80 border border-[#ECECEE] dark:border-zinc-700 text-right active:scale-98 transition-all shadow-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-[#007A5E] dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/>
                                    </svg>
                                </span>
                                <span class="text-xs font-bold text-[#18181B] dark:text-zinc-100">همه مقاصد (کل کشور)</span>
                            </div>
                            <span class="text-xs font-bold text-[#007A5E] dark:text-emerald-400">انتخاب</span>
                        </button>

                        <div class="grid grid-cols-2 gap-1.5">
                            @foreach($allDestCities as $item)
                                <button type="button" onclick="selectDestination('{{ $item['city'] }}', '{{ $item['city'] }}', event)" data-dest-key="{{ $item['city'] }}"
                                        class="p-2.5 rounded-xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-700/80 active:scale-95 shadow-xs transition-all text-right">
                                    <div class="text-xs font-bold text-zinc-700 dark:text-zinc-200 truncate">{{ $item['city'] }}</div>
                                    <div class="text-[10px] text-zinc-400 truncate">استان {{ $item['province'] }}</div>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Panel: Per Province -->
                    @foreach($availableDests as $destGroup)
                        <div id="mobile-panel-{{ $destGroup['province'] }}" class="mobile-province-panel hidden space-y-2.5">
                            <button type="button" onclick="selectDestination('prov:{{ $destGroup['province'] }}', 'استان {{ $destGroup['province'] }}', event)" data-dest-key="prov:{{ $destGroup['province'] }}"
                                    class="w-full flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-zinc-800 border border-[#007A5E]/40 dark:border-emerald-700/50 active:scale-95 transition-all text-right shadow-xs">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-[#007A5E]"></span>
                                    <span class="text-xs font-extrabold text-[#007A5E] dark:text-emerald-400">همه شهرهای {{ $destGroup['province'] }}</span>
                                </div>
                                <span class="text-[11px] font-bold text-[#007A5E] dark:text-emerald-400">انتخاب کل استان</span>
                            </button>

                            <div class="grid grid-cols-3 gap-1.5 text-xs">
                                @foreach($destGroup['cities'] as $cityName)
                                    <button type="button" onclick="selectDestination('{{ $cityName }}', '{{ $cityName }}', event)" data-dest-key="{{ $cityName }}"
                                            class="py-2.5 px-2 rounded-xl bg-white dark:bg-zinc-900 font-bold text-zinc-700 dark:text-zinc-200 text-center truncate border border-[#ECECEE] dark:border-zinc-700/80 active:scale-95 shadow-xs transition-all hover:border-[#007A5E] hover:text-[#007A5E]">
                                        {{ $cityName }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>

            <!-- Section 2: Date Picker -->
            <div class="pt-4 border-t border-[#ECECEE] dark:border-zinc-800">
                <div class="flex items-center justify-between text-xs mb-3">
                    <span class="font-black text-[#18181B] dark:text-zinc-100">تاریخ اقامت ({{ $currentJalaliMonthName }} {{ $currentJalaliYear }})</span>
                    <span id="mobile-calendar-summary" class="font-bold text-[#007A5E] dark:text-emerald-400">{{ $defaultStartDay }} تا {{ $defaultEndDay }} {{ $currentJalaliMonthName }}</span>
                </div>

                <!-- Check-in & Check-out Tabs (Generous vertical spacing & refined theme harmony) -->
                <div class="grid grid-cols-2 gap-2.5 mb-3.5">
                    <button type="button" id="mobile-checkin-tab-btn" onclick="setDateSelectionStep('start', event)"
                            class="p-3 rounded-2xl border-2 border-[#007A5E] dark:border-emerald-500 bg-white dark:bg-zinc-800/90 text-right transition-all flex flex-col justify-between min-h-[72px]">
                        <div class="flex items-center justify-between w-full">
                            <span class="text-[11px] font-bold text-[#007A5E] dark:text-emerald-400 flex items-center gap-1.5">
                                <span id="mobile-checkin-dot" class="w-1.5 h-1.5 rounded-full bg-[#007A5E] dark:bg-emerald-400"></span>
                                <span id="mobile-checkin-status">تاریخ ورود</span>
                            </span>
                            <span id="mobile-checkin-badge" class="text-[9px] font-bold text-[#007A5E] dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.5 rounded-md">انتخاب</span>
                        </div>
                        <span id="mobile-checkin-val" class="text-sm font-black text-[#18181B] dark:text-zinc-100 mt-2.5 block tracking-tight">
                            {{ $defaultStartDay }} {{ $currentJalaliMonthName }}
                        </span>
                    </button>
                    <button type="button" id="mobile-checkout-tab-btn" onclick="setDateSelectionStep('end', event)"
                            class="p-3 rounded-2xl border border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9] dark:bg-zinc-900/60 text-right hover:border-zinc-300 dark:hover:border-zinc-700 transition-all flex flex-col justify-between min-h-[72px]">
                        <div class="flex items-center justify-between w-full">
                            <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400 flex items-center gap-1.5">
                                <span id="mobile-checkout-dot" class="hidden w-1.5 h-1.5 rounded-full bg-[#007A5E] dark:bg-emerald-400"></span>
                                <span id="mobile-checkout-status">تاریخ خروج</span>
                            </span>
                            <span id="mobile-checkout-badge" class="hidden text-[9px] font-bold text-[#007A5E] dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.5 rounded-md">انتخاب</span>
                        </div>
                        <span id="mobile-checkout-val" class="text-sm font-bold text-zinc-700 dark:text-zinc-300 mt-2.5 block tracking-tight">
                            {{ $defaultEndDay }} {{ $currentJalaliMonthName }}
                        </span>
                    </button>
                </div>

                <div class="bg-[#F7F8F9] dark:bg-zinc-800/50 p-3.5 rounded-2xl border border-[#ECECEE] dark:border-zinc-800">
                    <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-bold text-zinc-400 mb-2">
                        <span>ش</span><span>ی</span><span>د</span><span>س</span><span>چ</span><span>پ</span><span>ج</span>
                    </div>
                    <div id="mobile-jalali-grid" class="grid grid-cols-7 gap-1 text-center text-xs"></div>
                    <div class="mt-2.5 pt-2 border-t border-[#ECECEE] dark:border-zinc-700/60 flex items-center justify-between text-[10px] text-zinc-400">
                        <span>امروز: {{ $currentJalaliDay }} {{ $currentJalaliMonthName }}</span>
                        <span>مدت: <strong id="mobile-nights-count" class="text-[#007A5E] dark:text-emerald-400 font-bold">{{ $defaultNights }}</strong> شب</span>
                    </div>
                </div>
            </div>

            <!-- Section 3: Guests -->
            <div class="pt-4 border-t border-[#ECECEE] dark:border-zinc-800">
                <div class="flex items-center justify-between bg-[#F7F8F9] dark:bg-zinc-800/70 border border-[#ECECEE] dark:border-zinc-800 rounded-2xl px-4 py-3 text-xs">
                    <div>
                        <span class="block font-bold text-zinc-700 dark:text-zinc-200">تعداد نفرات</span>
                        <span class="block text-[10px] text-zinc-400">شامل بزرگسال و کودک</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="updateSearchGuests(1)" class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-700 border border-[#ECECEE] dark:border-zinc-600 font-black text-sm text-[#18181B] dark:text-zinc-100 shadow-xs active:scale-90 transition-transform">+</button>
                        <span id="mobile-sheet-guests" class="font-black w-6 text-center text-sm text-[#18181B] dark:text-zinc-100">۲</span>
                        <button type="button" onclick="updateSearchGuests(-1)" class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-700 border border-[#ECECEE] dark:border-zinc-600 font-black text-sm text-[#18181B] dark:text-zinc-100 shadow-xs active:scale-90 transition-transform">−</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Bottom CTA -->
        <div class="p-4 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-sm border-t border-[#ECECEE] dark:border-zinc-800 shrink-0">
            <button type="button" onclick="closeMobileSheets(); triggerLivewireSearch();" class="w-full bg-[#007A5E] hover:bg-[#005F48] text-white font-black py-3.5 rounded-2xl text-xs transition-colors shadow-sm active:scale-98">
                اعمال فیلتر و جستجو
            </button>
        </div>
    </div>

    <!-- AUTH SIMPLE DIALOG MODAL -->
    <div id="simple-dialog-modal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 w-full max-w-sm rounded-3xl p-6 border border-[#ECECEE] dark:border-zinc-800 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-extrabold text-[#18181B] dark:text-zinc-100">ورود به بوم‌گرد</h3>
                <button type="button" onclick="closeSimpleDialog()" class="w-7 h-7 rounded-full bg-[#F7F8F9] dark:bg-zinc-800 text-xs text-zinc-500">✕</button>
            </div>
            <p class="text-xs text-zinc-400 mb-4 leading-relaxed">شماره موبایل خود را وارد کنید تا کد تایید پیامک شود.</p>
            <input type="tel" placeholder="۰۹۱۲..." class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 text-[#18181B] dark:text-zinc-100 rounded-xl px-4 py-3 text-sm font-bold text-center tracking-widest outline-none mb-4 focus:border-[#007A5E]">
            <button type="button" onclick="submitSimpleDialog()" class="w-full bg-[#007A5E] text-white font-bold py-3 rounded-xl text-xs">
                دریافت کد تایید
            </button>
        </div>
    </div>

</div>

<!-- SCRIPT ENGINE -->
<script>
    const rentalMultiHostEnabled = {{ $rentalMultiHostEnabled ? 'true' : 'false' }};
    const rentalModeEnabled = {{ $rentalModeEnabled ? 'true' : 'false' }};

    function toPersianNum(num) {
        if (num === null || num === undefined) return '';
        const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return num.toString().replace(/\d/g, x => persianDigits[x]);
    }

    function formatToman(amount) {
        if (!amount) return '';
        const withCommas = amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return toPersianNum(withCommas);
    }

    const SECTIONS_META = [
        {
            key: 'premium',
            title: 'اقامتگاه‌های ممتاز',
            subtitle: 'گلچینی از اقامتگاه‌های باکیفیت با میزبان‌های مهمان‌نواز'
        },
        {
            key: 'lastminute',
            title: 'تخفیف لحظه آخری',
            subtitle: 'برای امروز و فردا با تخفیف رزرو کن'
        },
        {
            key: 'unique',
            title: 'اقامتگاه‌های خاص',
            subtitle: 'اقامتگاه‌های متفاوت و جذاب برای افراد خاص'
        },
        {
            key: 'newest',
            title: 'جدیدترین بوم‌گردی‌ها و اقامتگاه‌ها',
            subtitle: 'بوم‌گردی و اقامتگاه، اختصاصی با ما'
        }
    ];

    // Fallback Mock data with harmonious villa photos and consistent aspects
    const DEFAULT_LISTINGS = [
        // 1. اقامتگاه‌های ممتاز (Premium)
        {
            id: 101,
            section: 'premium',
            title: 'کلبه سوئیسی اولسبلنگاه',
            city: 'ماسال',
            category: 'cabin',
            rooms: 2,
            maxGuests: 4,
            rating: '۴.۹',
            price: 2400000,
            instant: true,
            image: 'https://images.unsplash.com/photo-1542718610-a1d656d1884c?auto=format&fit=crop&w=800&q=80',
            url: '{{ url("/properties") }}',
            checkoutUrl: '{{ url("/properties") }}',
            hostName: 'سید رضا حسینی',
            isVerifiedHost: true
        },
        {
            id: 102,
            section: 'premium',
            title: 'ویلا ساحلی چابکسر',
            city: 'رامسر',
            category: 'beach',
            rooms: 2,
            maxGuests: 5,
            rating: '۴.۹',
            price: 3100000,
            instant: true,
            image: 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=800&q=80',
            url: '{{ url("/properties") }}',
            checkoutUrl: '{{ url("/properties") }}',
            hostName: 'امیرحسین رادمهر',
            isVerifiedHost: true
        },
        {
            id: 111,
            section: 'premium',
            title: 'کلبه چوبی جنگلی لفور',
            city: 'لفور',
            category: 'cabin',
            rooms: 2,
            maxGuests: 4,
            rating: '۵.۰',
            price: 2800000,
            instant: true,
            image: 'https://images.unsplash.com/photo-1587061949409-02df41d5e562?auto=format&fit=crop&w=800&q=80',
            url: '{{ url("/properties") }}',
            checkoutUrl: '{{ url("/properties") }}',
            hostName: 'محمد سوادکوهی',
            isVerifiedHost: true
        },
        {
            id: 112,
            section: 'premium',
            title: 'ویلا لوکس استخردار شیرگاه',
            city: 'شیرگاه',
            category: 'pool',
            rooms: 3,
            maxGuests: 6,
            rating: '۴.۹',
            price: 4200000,
            instant: true,
            image: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80',
            url: '{{ url("/properties") }}',
            checkoutUrl: '{{ url("/properties") }}',
            hostName: 'فرزاد کاظمی',
            isVerifiedHost: true
        },

        // 2. تخفیف لحظه آخری (Last Minute) - فقط آیتم‌های دارای تخفیف واقعی
        {
            id: 105,
            section: 'lastminute',
            title: 'کلبه جنگلی دالخانی',
            city: 'دالخانی',
            category: 'cabin',
            rooms: 1,
            maxGuests: 3,
            rating: '۴.۸',
            price: 1780000,
            oldPrice: 2100000,
            discountPercent: 15,
            instant: true,
            image: 'https://images.unsplash.com/photo-1449844908441-8829872d2607?auto=format&fit=crop&w=800&q=80',
            url: '{{ url("/properties") }}',
            checkoutUrl: '{{ url("/properties") }}',
            hostName: 'مهرداد قاسمی',
            isVerifiedHost: true
        },
        {
            id: 106,
            section: 'lastminute',
            title: 'کلبه چوبی رودخانه‌ای',
            city: 'شاندرمن',
            category: 'cabin',
            rooms: 1,
            maxGuests: 4,
            rating: '۴.۷',
            price: 1350000,
            oldPrice: 1690000,
            discountPercent: 20,
            instant: true,
            image: 'https://images.unsplash.com/photo-1510798831971-661eb04b3739?auto=format&fit=crop&w=800&q=80',
            url: '{{ url("/properties") }}',
            checkoutUrl: '{{ url("/properties") }}',
            hostName: 'صادق شریفی',
            isVerifiedHost: false
        },
        {
            id: 115,
            section: 'lastminute',
            title: 'سوئیت ساحلی تنکابن',
            city: 'رامسر',
            category: 'beach',
            rooms: 1,
            maxGuests: 3,
            rating: '۴.۶',
            price: 1200000,
            oldPrice: 1500000,
            discountPercent: 20,
            instant: true,
            image: 'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=800&q=80',
            url: '{{ url("/properties") }}',
            checkoutUrl: '{{ url("/properties") }}',
            hostName: 'مهدی ناصری',
            isVerifiedHost: true
        },

        // 3. اقامتگاه‌های خاص (Unique)
        {
            id: 103,
            section: 'unique',
            title: 'ویلا استخردار شاندرمن',
            city: 'شاندرمن',
            category: 'pool',
            rooms: 3,
            maxGuests: 6,
            rating: '۴.۸',
            price: 3900000,
            instant: true,
            image: 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=800&q=80',
            url: '{{ url("/properties") }}',
            checkoutUrl: '{{ url("/properties") }}',
            hostName: 'پژمان کاظمی',
            isVerifiedHost: true
        },
        {
            id: 104,
            section: 'unique',
            title: 'کلبه شیشه‌ای مه‌نشین',
            city: 'ماسال',
            category: 'cabin',
            rooms: 1,
            maxGuests: 3,
            rating: '۵.۰',
            price: 3450000,
            instant: true,
            image: 'https://images.unsplash.com/photo-1518780664697-55e3ad937233?auto=format&fit=crop&w=800&q=80',
            url: '{{ url("/properties") }}',
            checkoutUrl: '{{ url("/properties") }}',
            hostName: 'کامران یزدانی',
            isVerifiedHost: true
        },
        {
            id: 113,
            section: 'unique',
            title: 'ویلا لاکچری استخر آبگرم',
            city: 'رامسر',
            category: 'pool',
            rooms: 3,
            maxGuests: 8,
            rating: '۴.۹',
            price: 5200000,
            instant: true,
            image: 'https://images.unsplash.com/photo-1613977257363-707ba9348227?auto=format&fit=crop&w=800&q=80',
            url: '{{ url("/properties") }}',
            checkoutUrl: '{{ url("/properties") }}',
            hostName: 'بهنام فرهمند',
            isVerifiedHost: true
        },
        {
            id: 114,
            section: 'unique',
            title: 'کلبه دنج جنگلی آلاچیق‌دار',
            city: 'دالخانی',
            category: 'cabin',
            rooms: 1,
            maxGuests: 4,
            rating: '۴.۸',
            price: 2150000,
            instant: true,
            image: 'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=800&q=80',
            url: '{{ url("/properties") }}',
            checkoutUrl: '{{ url("/properties") }}',
            hostName: 'حامد رضوی',
            isVerifiedHost: true
        },

        // 4. جدیدترین بوم‌گردی‌ها و اقامتگاه‌ها (Newest)
        {
            id: 107,
            section: 'newest',
            title: 'بوم‌گردی تلار قلعه رودخان',
            city: 'قلعه رودخان',
            category: 'cabin',
            rooms: 2,
            maxGuests: 4,
            rating: '۵.۰',
            price: 1850000,
            instant: false,
            image: 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80',
            url: '{{ url("/properties") }}',
            checkoutUrl: '{{ url("/properties") }}',
            hostName: 'حسین میرزایی',
            isVerifiedHost: true
        },
        {
            id: 108,
            section: 'newest',
            title: 'خانه ساحلی سفید رامسر',
            city: 'رامسر',
            category: 'beach',
            rooms: 2,
            maxGuests: 4,
            rating: '۴.۹',
            price: 2650000,
            instant: true,
            image: 'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=800&q=80',
            url: '{{ url("/properties") }}',
            checkoutUrl: '{{ url("/properties") }}',
            hostName: 'مهدی ناصری',
            isVerifiedHost: true
        },
        {
            id: 109,
            section: 'newest',
            title: 'بوم‌گردی چوبی هیرکانی لفور',
            city: 'لفور',
            category: 'cabin',
            rooms: 2,
            maxGuests: 5,
            rating: '۴.۹',
            price: 1950000,
            instant: true,
            image: 'https://images.unsplash.com/photo-1542718610-a1d656d1884c?auto=format&fit=crop&w=800&q=80',
            url: '{{ url("/properties") }}',
            checkoutUrl: '{{ url("/properties") }}',
            hostName: 'رضا کمالی',
            isVerifiedHost: true
        },
        {
            id: 110,
            section: 'newest',
            title: 'کلبه دوبلکس ماسال با بالکن ابری',
            city: 'ماسال',
            category: 'cabin',
            rooms: 2,
            maxGuests: 4,
            rating: '۴.۸',
            price: 2750000,
            instant: true,
            image: 'https://images.unsplash.com/photo-1587061949409-02df41d5e562?auto=format&fit=crop&w=800&q=80',
            url: '{{ url("/properties") }}',
            checkoutUrl: '{{ url("/properties") }}',
            hostName: 'یاسر احمدی',
            isVerifiedHost: true
        }
    ];

    @php
        $dbListings = [];
        $propertyList = $properties ?? $latestProperties ?? [];
        if (count($propertyList) > 0) {
            $sectionsCycle = ['premium', 'unique', 'newest'];
            $idx = 0;
            foreach ($propertyList as $prop) {
                $categorySlug = 'cabin';
                $titleLower = mb_strtolower($prop->title);
                if (str_contains($titleLower, 'استخر')) $categorySlug = 'pool';
                elseif (str_contains($titleLower, 'ساحل') || str_contains($titleLower, 'دریا')) $categorySlug = 'beach';

                $price = $prop->rentalConfig->price_per_night ?? $prop->rentalConfig->daily_rate ?? $prop->rent_price ?? $prop->price ?? 2000000;
                $oldPrice = $prop->rentalConfig->old_daily_rate ?? ($prop->meta['old_daily_rate'] ?? null);
                if (!$oldPrice && !empty($prop->meta['discount_percent']) && (int)$prop->meta['discount_percent'] > 0) {
                    $oldPrice = round($price / (1 - ((int)$prop->meta['discount_percent'] / 100)));
                }
                $discount = ($oldPrice && $oldPrice > $price) ? round((($oldPrice - $price) / $oldPrice) * 100) : null;

                // تعیین دقیق سکشن: فقط اگر واقعاً تخفیف دارد به lastminute تخصیص می‌یابد
                $assignedSection = ($discount && $discount > 0) ? 'lastminute' : $sectionsCycle[$idx % 3];

                $hostName = $prop->host?->display_name ?: ($prop->host?->user?->name ?: null);
                $isVerified = (bool) ($prop->host?->kyc_status === 'approved' || $prop->host?->is_verified);

                $slugOrId = $prop->slug ?: $prop->id;
                $detailUrl = route('properties.show', $slugOrId);
                $checkoutUrl = route('properties.checkout', $slugOrId);

                $coverUrl = null;
                if (!empty($prop->cover_image)) {
                    $coverUrl = str_starts_with($prop->cover_image, 'http')
                        ? $prop->cover_image
                        : asset('storage/' . $prop->cover_image);
                } else {
                    $coverUrl = asset('images/property-placeholder.svg');
                }

                $dbListings[] = [
                    'id' => $prop->id,
                    'section' => $assignedSection,
                    'title' => $prop->title,
                    'province' => $prop->province ?? 'مازندران',
                    'city' => $prop->city ?? 'لفور',
                    'category' => $categorySlug,
                    'rooms' => $prop->rooms ?? 2,
                    'maxGuests' => $prop->rentalConfig->max_guests ?? 4,
                    'rating' => '۴.۹',
                    'price' => (int)$price,
                    'oldPrice' => $oldPrice ? (int)$oldPrice : null,
                    'discountPercent' => $discount,
                    'instant' => (bool) ($prop->rentalConfig->instant_booking ?? true),
                    'image' => $coverUrl,
                    'url' => $detailUrl,
                    'checkoutUrl' => $checkoutUrl,
                    'hostName' => $hostName,
                    'isVerifiedHost' => $isVerified,
                ];
                $idx++;
            }
        }
    @endphp

    const LISTINGS_DATA = @json(!empty($dbListings) ? $dbListings : null) || DEFAULT_LISTINGS;
    const AVAILABLE_DEST_CITIES = @json($allDestCities ?? []);

    const currentJalaliYear = {{ $currentJalaliYear }};
    const currentJalaliMonth = {{ $currentJalaliMonth }};
    const currentJalaliMonthName = '{{ $currentJalaliMonthName }}';
    const currentJalaliDay = {{ $currentJalaliDay }};
    const totalMonthDays = {{ $currentMonthDays }};

    const state = {
        mobileTab: 'explore',
        province: 'all',
        city: 'all',
        cityLabel: 'همه مقاصد',
        category: 'all',
        guests: 2,
        startDay: {{ $defaultStartDay }},
        endDay: {{ $defaultEndDay }},
        instantOnly: false,
        favorites: [101],
        activeModalItem: null,
        modalNights: {{ $defaultNights }}
    };

    function simulateWireLoading(callback) {
        const bar = document.getElementById('wire-loading-bar');
        if (!bar) {
            if (callback) callback();
            return;
        }
        bar.style.opacity = '1';
        bar.style.width = '80%';
        setTimeout(() => {
            bar.style.width = '100%';
            if (callback) callback();
            setTimeout(() => {
                bar.style.opacity = '0';
                setTimeout(() => { bar.style.width = '0%'; }, 150);
            }, 80);
        }, 100);
    }

    let toastTimer = null;
    function showToast(message) {
        const container = document.getElementById('toast-container');
        const msgEl = document.getElementById('toast-message');
        if (!container || !msgEl) return;
        msgEl.textContent = message;
        container.classList.remove('opacity-0', '-translate-y-2');
        container.classList.add('opacity-100', 'translate-y-0');
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            container.classList.add('opacity-0', '-translate-y-2');
            container.classList.remove('opacity-100', 'translate-y-0');
        }, 2000);
    }

    // Carousel Scroll Controller for Desktop and Touch Navigation
    function scrollCarousel(sectionKey, direction) {
        const el = document.getElementById('carousel-' + sectionKey);
        if (!el) return;
        const card = el.querySelector('article');
        const cardWidth = card ? (card.offsetWidth + 16) : 300;
        // In RTL layout, scrolling to next items is negative in modern browsers
        el.scrollBy({
            left: direction * cardWidth * -1,
            behavior: 'smooth'
        });
    }

    window.isCarouselDragging = false;

    // Ultra-smooth Mouse Drag-to-Scroll Controller with RTL-awareness & Momentum
    function initCarouselDrag() {
        const carousels = document.querySelectorAll('.section-carousel');
        carousels.forEach(carousel => {
            if (carousel.dataset.dragReady === 'true') return;
            carousel.dataset.dragReady = 'true';

            let isDown = false;
            let startX = 0;
            let currentX = 0;
            let scrollStart = 0;
            let lastMouseX = 0;
            let lastTime = 0;
            let velocityX = 0;
            let rafId = null;
            let hasMovedSignificantly = false;

            const updateScroll = () => {
                if (!isDown) return;
                const walk = currentX - startX;
                // RTL-Accurate: Dragging right (walk > 0) scrolls towards next items (decreasing scrollLeft in modern RTL)
                carousel.scrollLeft = scrollStart - walk;
                rafId = requestAnimationFrame(updateScroll);
            };

            const onMouseDown = (e) => {
                if (e.button !== 0) return; // Only primary left click
                isDown = true;
                hasMovedSignificantly = false;
                startX = e.pageX;
                currentX = e.pageX;
                lastMouseX = e.pageX;
                lastTime = performance.now();
                scrollStart = carousel.scrollLeft;
                velocityX = 0;

                carousel.classList.add('is-dragging');
                carousel.style.scrollSnapType = 'none';
                carousel.style.scrollBehavior = 'auto';

                cancelAnimationFrame(rafId);
                rafId = requestAnimationFrame(updateScroll);

                window.addEventListener('mousemove', onMouseMove, { passive: false });
                window.addEventListener('mouseup', onMouseUp);
            };

            const onMouseMove = (e) => {
                if (!isDown) return;
                currentX = e.pageX;
                const dist = Math.abs(currentX - startX);

                if (dist > 6) {
                    hasMovedSignificantly = true;
                    window.isCarouselDragging = true;
                    if (e.cancelable) e.preventDefault();
                }

                const now = performance.now();
                const dt = now - lastTime;
                if (dt > 12) {
                    velocityX = (currentX - lastMouseX) / dt;
                    lastMouseX = currentX;
                    lastTime = now;
                }
            };

            const onMouseUp = () => {
                if (!isDown) return;
                isDown = false;
                cancelAnimationFrame(rafId);

                carousel.classList.remove('is-dragging');
                window.removeEventListener('mousemove', onMouseMove);
                window.removeEventListener('mouseup', onMouseUp);

                // Buttery momentum glide based on release velocity
                let momentum = velocityX * 220;
                const maxGlide = carousel.clientWidth * 0.85;
                if (momentum > maxGlide) momentum = maxGlide;
                if (momentum < -maxGlide) momentum = -maxGlide;

                if (Math.abs(momentum) > 10) {
                    const targetLeft = carousel.scrollLeft - momentum;
                    carousel.scrollTo({
                        left: targetLeft,
                        behavior: 'smooth'
                    });
                }

                // Smoothly restore scroll-snap after momentum glide settles
                setTimeout(() => {
                    carousel.style.scrollSnapType = 'x mandatory';
                    carousel.style.scrollBehavior = 'smooth';
                }, 320);

                // Prevent card click when drag action was performed
                if (hasMovedSignificantly) {
                    setTimeout(() => {
                        window.isCarouselDragging = false;
                    }, 140);
                } else {
                    window.isCarouselDragging = false;
                }
            };

            carousel.addEventListener('mousedown', onMouseDown);
            carousel.addEventListener('dragstart', (e) => e.preventDefault());
        });
    }

    function navigateToProperty(url) {
        if (window.isCarouselDragging) return;
        if (url) {
            window.location.href = url;
        }
    }

    // Standard Carousel Card Builder (3 in Desktop, 2 Full + 1 Peek in Mobile)
    function buildVillaCardHTML(item) {
        const isFav = state.favorites.includes(item.id);
        const discountBadge = item.discountPercent
            ? `<span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-lg bg-[#007A5E] text-white text-[10px] sm:text-[11px] font-bold shadow-sm">${toPersianNum(item.discountPercent)}٪ تخفیف</span>`
            : '';

        const oldPriceMarkup = item.oldPrice
            ? `<span class="text-[10px] sm:text-xs text-zinc-400 line-through font-normal ml-1.5">${formatToman(item.oldPrice)}</span>`
            : '';

        const targetUrl = item.url || ('{{ url("/properties") }}/' + item.id);

        return `
        <article onclick="navigateToProperty('${targetUrl}')" class="carousel-card-item group cursor-pointer select-none shrink-0 snap-start bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800/80 hover:border-zinc-300 dark:hover:border-zinc-700 rounded-2xl p-2.5 sm:p-3 transition-all duration-200 shadow-[0_2px_8px_rgba(0,0,0,0.03)] dark:shadow-[0_2px_8px_rgba(0,0,0,0.2)] flex flex-col justify-between h-full">
            <div>
                <div class="relative aspect-[4/3] w-full rounded-xl overflow-hidden bg-[#F7F8F9] dark:bg-zinc-800 mb-2.5 sm:mb-3 border border-[#ECECEE]/60 dark:border-zinc-800/60">
                    <img src="${item.image}" alt="${item.title}" onerror="this.onerror=null; this.src='{{ asset('images/property-placeholder.svg') }}';" class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-300" loading="lazy">
                    ${discountBadge}
                    <button type="button" onclick="toggleFavorite(${item.id}, event)" aria-label="ذخیره" class="absolute top-2.5 left-2.5 w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/90 dark:bg-zinc-900/90 flex items-center justify-center active:scale-90 transition-transform shadow-sm">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 ${isFav ? 'text-rose-500 fill-rose-500' : 'text-[#18181B] dark:text-zinc-200'}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                        </svg>
                    </button>
                </div>

                <div class="flex items-start justify-between gap-1.5 px-0.5">
                    <h3 class="card-title-fixed-height text-xs sm:text-sm font-bold text-[#18181B] dark:text-zinc-100 flex-1">${item.title}</h3>
                    <span class="inline-flex items-center gap-1 text-[11px] sm:text-xs font-bold text-[#18181B] dark:text-zinc-200 shrink-0 mt-0.5">
                        <span class="text-amber-400 dark:text-amber-400 text-xs">★</span>
                        <span>${item.rating}</span>
                    </span>
                </div>

                <p class="text-[11px] sm:text-xs text-zinc-400 mt-0.5 sm:mt-1 truncate px-0.5">${item.city} • تا ${toPersianNum(item.maxGuests)} نفر</p>
            </div>

            <div class="mt-2.5 pt-2 border-t border-[#ECECEE]/70 dark:border-zinc-800/70 text-xs sm:text-sm font-extrabold text-[#18181B] dark:text-zinc-100 truncate px-0.5">
                ${oldPriceMarkup}${formatToman(item.price)} <span class="text-[10px] sm:text-xs font-normal text-zinc-400">تومان / شب</span>
            </div>
        </article>`;
    }

    // Distinct Carousel Card Builder for "Last Minute Deals"
    function buildLastMinuteCardHTML(item) {
        const isFav = state.favorites.includes(item.id);
        const savedAmount = item.oldPrice ? (item.oldPrice - item.price) : 0;
        const targetUrl = item.url || ('{{ url("/properties") }}/' + item.id);

        return `
        <article onclick="navigateToProperty('${targetUrl}')" class="carousel-card-item group cursor-pointer select-none shrink-0 snap-start bg-white dark:bg-zinc-900 border border-[#D1FAE5] dark:border-emerald-900/40 hover:border-[#007A5E] dark:hover:border-emerald-500 rounded-2xl p-2.5 sm:p-3 transition-all duration-200 shadow-[0_2px_8px_rgba(0,0,0,0.03)] dark:shadow-[0_2px_8px_rgba(0,0,0,0.2)] flex flex-col justify-between h-full">
            <div>
                <div class="relative aspect-[4/3] w-full rounded-xl overflow-hidden bg-[#F7F8F9] dark:bg-zinc-800 mb-2.5">
                    <img src="${item.image}" alt="${item.title}" onerror="this.onerror=null; this.src='{{ asset('images/property-placeholder.svg') }}';" class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-300" loading="lazy">
                    
                    <span class="absolute top-2 right-2 px-2 py-0.5 rounded-lg bg-[#007A5E] text-white text-[10px] sm:text-[11px] font-extrabold shadow-sm">
                        ${toPersianNum(item.discountPercent || 15)}٪ تخفیف
                    </span>

                    <button type="button" onclick="toggleFavorite(${item.id}, event)" aria-label="ذخیره" class="absolute top-2 left-2 w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/95 dark:bg-zinc-900/95 flex items-center justify-center active:scale-90 transition-transform shadow-sm">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 ${isFav ? 'text-rose-500 fill-rose-500' : 'text-[#18181B] dark:text-zinc-200'}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                        </svg>
                    </button>
                </div>

                <div class="flex items-start justify-between gap-1.5 px-0.5">
                    <h3 class="card-title-fixed-height text-xs sm:text-sm font-bold text-[#18181B] dark:text-zinc-100 flex-1">${item.title}</h3>
                    <span class="inline-flex items-center gap-1 text-[11px] sm:text-xs font-bold text-[#18181B] dark:text-zinc-200 shrink-0 mt-0.5">
                        <span class="text-amber-400 dark:text-amber-400 text-xs">★</span>
                        <span>${item.rating}</span>
                    </span>
                </div>

                <p class="text-[11px] sm:text-xs text-zinc-400 mt-0.5 truncate px-0.5">${item.city} • تا ${toPersianNum(item.maxGuests)} نفر</p>
            </div>

            <div class="mt-2 pt-2 border-t border-[#ECECEE] dark:border-zinc-800 flex items-end justify-between gap-1">
                <div>
                    <span class="block text-[10px] sm:text-[11px] text-zinc-400 line-through">${formatToman(item.oldPrice || Math.round(item.price * 1.2))}</span>
                    <div class="text-xs sm:text-sm font-extrabold text-[#007A5E] dark:text-emerald-400">
                        ${formatToman(item.price)} <span class="text-[10px] font-normal text-zinc-500">تومان</span>
                    </div>
                </div>
                ${savedAmount > 0 ? `<span class="hidden sm:inline-block px-1.5 py-0.5 rounded-lg bg-[#ECFDF5] dark:bg-emerald-950/40 text-[#007A5E] dark:text-emerald-400 text-[10px] font-bold">${formatToman(savedAmount)} سود</span>` : ''}
            </div>
        </article>`;
    }

    function renderListings() {
        const sectionsWrapper = document.getElementById('sections-wrapper');
        const emptyState = document.getElementById('empty-state');

        const filtered = LISTINGS_DATA.filter(item => {
            if (state.city !== 'all' && item.city !== state.city) return false;
            if (state.province !== 'all' && item.province !== state.province) return false;
            if (state.category !== 'all' && item.category !== state.category) return false;
            if (state.guests > item.maxGuests) return false;
            if (state.instantOnly && !item.instant) return false;
            return true;
        });

        // Sync zone pills
        document.querySelectorAll('.zone-quick-pill').forEach(btn => {
            const pillVal = btn.getAttribute('data-zone-pill');
            const isActive = (pillVal === 'all' && state.city === 'all' && state.province === 'all') || (pillVal === state.city);
            btn.className = isActive
                ? 'zone-quick-pill px-4 py-2 rounded-full text-xs font-bold bg-[#007A5E] text-white border border-[#007A5E] shadow-sm transition-all active:scale-95'
                : 'zone-quick-pill px-4 py-2 rounded-full text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-800 hover:border-[#007A5E] dark:hover:border-emerald-500 hover:text-[#007A5E] dark:hover:text-emerald-400 shadow-xs transition-all active:scale-95';
        });

        if (filtered.length === 0) {
            sectionsWrapper.innerHTML = '';
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
            const renderedSections = SECTIONS_META.map(sec => {
                let secItems = filtered.filter(item => item.section === sec.key);

                // قانون قطعی: وقتی تخفیفی نیست سکشن تخفیف لحظه آخری به هیچ وجه نباید نمایش داده شود
                if (sec.key === 'lastminute') {
                    secItems = secItems.filter(item => (item.discountPercent && item.discountPercent > 0) || (item.oldPrice && item.oldPrice > item.price));
                    if (secItems.length === 0) return '';

                    return `
                    <section class="bg-[#ECFDF5]/60 dark:bg-emerald-950/20 border border-[#D1FAE5] dark:border-emerald-900/40 rounded-3xl p-4 sm:p-6 mb-2">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-4 sm:mb-5">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-[#007A5E] animate-pulse"></span>
                                    <h2 class="text-sm sm:text-base lg:text-lg font-extrabold text-[#005F48] dark:text-emerald-400 tracking-tight">${sec.title}</h2>
                                </div>
                                <p class="text-[11px] sm:text-xs text-[#007A5E]/80 dark:text-emerald-400/80 mt-1">${sec.subtitle}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-1.5 bg-white dark:bg-zinc-900 border border-[#D1FAE5] dark:border-emerald-900/50 px-2.5 py-1.5 rounded-xl text-[#007A5E] dark:text-emerald-400 shadow-sm">
                                    <svg class="w-3.5 h-3.5 text-[#007A5E] dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div class="flex items-center gap-1 text-xs font-extrabold tracking-wider" dir="ltr">
                                        <span id="lm-timer-h" class="bg-[#ECFDF5] dark:bg-emerald-950/50 px-1.5 py-0.5 rounded-md min-w-[24px] text-center">۰۴</span>
                                        <span>:</span>
                                        <span id="lm-timer-m" class="bg-[#ECFDF5] dark:bg-emerald-950/50 px-1.5 py-0.5 rounded-md min-w-[24px] text-center">۳۸</span>
                                        <span>:</span>
                                        <span id="lm-timer-s" class="bg-[#ECFDF5] dark:bg-emerald-950/50 px-1.5 py-0.5 rounded-md min-w-[24px] text-center">۱۹</span>
                                    </div>
                                </div>
                                <div class="hidden md:flex items-center gap-1">
                                    <button type="button" onclick="scrollCarousel('${sec.key}', -1)" aria-label="قبلی" class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-900 border border-[#D1FAE5] dark:border-emerald-900/50 hover:bg-[#ECFDF5] dark:hover:bg-zinc-800 flex items-center justify-center text-[#007A5E] dark:text-emerald-400 transition-colors shadow-sm">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                    <button type="button" onclick="scrollCarousel('${sec.key}', 1)" aria-label="بعدی" class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-900 border border-[#D1FAE5] dark:border-emerald-900/50 hover:bg-[#ECFDF5] dark:hover:bg-zinc-800 flex items-center justify-center text-[#007A5E] dark:text-emerald-400 transition-colors shadow-sm">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="carousel-${sec.key}" class="section-carousel flex gap-3 sm:gap-4 lg:gap-5 overflow-x-auto scroll-smooth snap-x snap-mandatory no-scrollbar pb-1">
                            ${secItems.map(buildLastMinuteCardHTML).join('')}
                        </div>
                    </section>`;
                }

                // سایر سکشن‌های استاندارد (کاروسل ۳تایی در دسکتاپ و ۲تایی کامل + بعدی در موبایل)
                if (secItems.length === 0) return '';

                return `
                <section class="pt-2 first:pt-0">
                    <div class="flex items-end justify-between gap-2 mb-3 sm:mb-4">
                        <div>
                            <h2 class="text-sm sm:text-base lg:text-lg font-extrabold text-[#18181B] dark:text-zinc-100 tracking-tight">${sec.title}</h2>
                            <p class="text-[11px] sm:text-xs text-zinc-400 mt-0.5 sm:mt-1">${sec.subtitle}</p>
                        </div>
                        <div class="hidden md:flex items-center gap-1">
                            <button type="button" onclick="scrollCarousel('${sec.key}', -1)" aria-label="قبلی" class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 hover:bg-[#F7F8F9] dark:hover:bg-zinc-800 flex items-center justify-center text-zinc-600 dark:text-zinc-300 transition-colors shadow-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </button>
                            <button type="button" onclick="scrollCarousel('${sec.key}', 1)" aria-label="بعدی" class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 hover:bg-[#F7F8F9] dark:hover:bg-zinc-800 flex items-center justify-center text-zinc-600 dark:text-zinc-300 transition-colors shadow-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                        </div>
                    </div>
                    <div id="carousel-${sec.key}" class="section-carousel flex gap-3 sm:gap-4 lg:gap-5 overflow-x-auto scroll-smooth snap-x snap-mandatory no-scrollbar pb-2">
                        ${secItems.map(buildVillaCardHTML).join('')}
                    </div>
                </section>`;
            }).filter(Boolean).join('');

            sectionsWrapper.innerHTML = renderedSections;
            initCarouselDrag();
        }

        const favGrid = document.getElementById('favorites-tab-grid');
        if (favGrid) {
            const favItems = LISTINGS_DATA.filter(x => state.favorites.includes(x.id));
            favGrid.innerHTML = favItems.length
                ? favItems.map(buildVillaCardHTML).join('')
                : `<div class="col-span-full py-16 text-center text-xs text-zinc-400">هنوز اقامتگاهی ذخیره نکرده‌اید.</div>`;
        }
    }

    function toggleFavorite(listingId, event) {
        if (event) event.stopPropagation();
        if (window.isCarouselDragging) return;
        const idx = state.favorites.indexOf(listingId);
        if (idx > -1) {
            state.favorites.splice(idx, 1);
            showToast('از ذخیره‌ها حذف شد');
        } else {
            state.favorites.push(listingId);
            showToast('در ذخیره‌ها ذخیره شد');
        }
        renderListings();
    }

    function filterByCategory(categoryKey) {
        state.category = categoryKey;
        document.querySelectorAll('.cat-box').forEach(box => {
            const active = box.getAttribute('data-cat') === categoryKey;
            const labelSpan = box.querySelector('span');
            box.className = active
                ? 'cat-box flex flex-col items-center justify-center gap-2 py-3.5 sm:py-5 px-2 rounded-2xl border border-[#007A5E] bg-[#ECFDF5]/70 dark:bg-emerald-950/30 text-[#007A5E] dark:text-emerald-400 transition-all active:scale-[0.98]'
                : 'cat-box flex flex-col items-center justify-center gap-2 py-3.5 sm:py-5 px-2 rounded-2xl border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-zinc-300 dark:hover:border-zinc-700 text-zinc-600 dark:text-zinc-400 transition-all active:scale-[0.98]';
            if (labelSpan) {
                labelSpan.className = active
                    ? 'text-[11px] sm:text-xs font-bold truncate'
                    : 'text-[11px] sm:text-xs font-medium truncate';
            }
        });
        simulateWireLoading(() => renderListings());
    }

    // Tabbed Province Navigation & Live Search Controller
    function switchDestTab(provinceKey, context) {
        // Reset search input if active
        const input = document.getElementById(context === 'desktop' ? 'dest-search-input' : 'mobile-dest-search-input');
        if (input) input.value = '';
        const clearBtn = document.getElementById(context === 'desktop' ? 'dest-search-clear' : 'mobile-dest-search-clear');
        if (clearBtn) clearBtn.classList.add('hidden');
        const resultsContainer = document.getElementById(context === 'desktop' ? 'desktop-search-results' : 'mobile-search-results');
        if (resultsContainer) resultsContainer.classList.add('hidden');
        const tabsBar = document.getElementById(context === 'desktop' ? 'desktop-dest-tabs' : 'mobile-dest-tabs');
        if (tabsBar) tabsBar.classList.remove('opacity-40', 'pointer-events-none');

        // Update active tab buttons
        document.querySelectorAll(`.dest-tab-btn[data-context="${context}"]`).forEach(btn => {
            const isCurrent = btn.getAttribute('data-dest-tab') === provinceKey;
            btn.className = `dest-tab-btn shrink-0 px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
                isCurrent
                    ? 'bg-[#007A5E] text-white shadow-xs is-active'
                    : 'bg-[#F7F8F9] dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:text-[#18181B] dark:hover:text-white border border-[#ECECEE] dark:border-zinc-700/70'
            }`;
        });

        // Toggle panels
        document.querySelectorAll(`.${context}-province-panel`).forEach(p => p.classList.add('hidden'));
        const targetPanel = document.getElementById(`${context}-panel-${provinceKey}`);
        if (targetPanel) {
            targetPanel.classList.remove('hidden');
        }
    }

    function filterDestinationsLive(query, context) {
        const q = (query || '').trim().toLowerCase();
        const clearBtn = document.getElementById(context === 'desktop' ? 'dest-search-clear' : 'mobile-dest-search-clear');
        const tabsBar = document.getElementById(context === 'desktop' ? 'desktop-dest-tabs' : 'mobile-dest-tabs');
        const resultsContainer = document.getElementById(context === 'desktop' ? 'desktop-search-results' : 'mobile-search-results');
        const itemsEl = document.getElementById(context === 'desktop' ? 'desktop-search-items' : 'mobile-search-items');
        const emptyEl = document.getElementById(context === 'desktop' ? 'desktop-search-empty' : 'mobile-search-empty');
        const panels = document.querySelectorAll(`.${context}-province-panel`);

        if (clearBtn) clearBtn.classList.toggle('hidden', !q);

        if (!q) {
            if (resultsContainer) resultsContainer.classList.add('hidden');
            if (tabsBar) tabsBar.classList.remove('opacity-40', 'pointer-events-none');
            // Restore currently active tab panel
            const activeTab = document.querySelector(`.dest-tab-btn[data-context="${context}"].is-active`);
            const activeKey = activeTab ? activeTab.getAttribute('data-dest-tab') : 'all';
            panels.forEach(p => p.classList.add('hidden'));
            const activePanel = document.getElementById(`${context}-panel-${activeKey}`);
            if (activePanel) activePanel.classList.remove('hidden');
            return;
        }

        // Show live search results
        if (tabsBar) tabsBar.classList.add('opacity-40', 'pointer-events-none');
        panels.forEach(p => p.classList.add('hidden'));
        if (resultsContainer) resultsContainer.classList.remove('hidden');

        const matches = (typeof AVAILABLE_DEST_CITIES !== 'undefined' ? AVAILABLE_DEST_CITIES : []).filter(item => {
            return item.city.toLowerCase().includes(q) || item.province.toLowerCase().includes(q);
        });

        if (matches.length === 0) {
            if (itemsEl) itemsEl.innerHTML = '';
            if (emptyEl) emptyEl.classList.remove('hidden');
        } else {
            if (emptyEl) emptyEl.classList.add('hidden');
            if (itemsEl) {
                itemsEl.innerHTML = matches.map(m => `
                    <button type="button" onclick="selectDestination('${m.city}', '${m.city}', event)" data-dest-key="${m.city}"
                            class="flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-700/80 hover:border-[#007A5E] dark:hover:border-emerald-500 hover:bg-[#ECFDF5] dark:hover:bg-emerald-950/40 text-right transition-all shadow-xs group active:scale-98">
                        <div class="truncate">
                            <div class="text-xs font-bold text-zinc-700 dark:text-zinc-200 group-hover:text-[#007A5E] dark:group-hover:text-emerald-400 truncate">${m.city}</div>
                            <div class="text-[10px] text-zinc-400 truncate">استان ${m.province}</div>
                        </div>
                        <span class="w-1.5 h-1.5 rounded-full bg-zinc-300 dark:bg-zinc-600 group-hover:bg-[#007A5E] dark:group-hover:bg-emerald-400 shrink-0 transition-colors mr-1"></span>
                    </button>
                `).join('');
            }
        }
    }

    function clearDestSearch(context) {
        const input = document.getElementById(context === 'desktop' ? 'dest-search-input' : 'mobile-dest-search-input');
        if (input) input.value = '';
        filterDestinationsLive('', context);
        if (input) input.focus();
    }

    function selectDestination(destKey, label, event) {
        if (event && event.stopPropagation) event.stopPropagation();

        if (destKey === 'all') {
            state.province = 'all';
            state.city = 'all';
        } else if (destKey.startsWith('prov:')) {
            state.province = destKey.replace('prov:', '');
            state.city = 'all';
        } else {
            state.province = 'all';
            state.city = destKey;
        }
        state.cityLabel = label;

        const dLabel = document.getElementById('desktop-dest-label');
        const mPill = document.getElementById('mobile-pill-dest');
        const dHint = document.getElementById('desktop-dest-selected-hint');
        if (dLabel) dLabel.textContent = label;
        if (mPill) mPill.textContent = destKey === 'all' ? 'جستجو در استان و شهر مقصد...' : `مقصد: ${label}`;
        if (dHint) dHint.textContent = `مقصد: ${label}`;

        // Sync visual active styling on all destination buttons
        document.querySelectorAll('[data-dest-key]').forEach(btn => {
            const key = btn.getAttribute('data-dest-key');
            const isMatch = (key === destKey);
            if (isMatch) {
                btn.classList.add('border-[#007A5E]', 'dark:border-emerald-500', 'bg-[#ECFDF5]', 'dark:bg-emerald-950/40', 'text-[#007A5E]', 'dark:text-emerald-400');
            } else {
                btn.classList.remove('border-[#007A5E]', 'dark:border-emerald-500', 'bg-[#ECFDF5]', 'dark:bg-emerald-950/40', 'text-[#007A5E]', 'dark:text-emerald-400');
            }
        });

        // Note: Quick zone pills are NOT synced here because search form values
        // only apply when the user explicitly clicks the "جستجو" button!
    }

    function applyQuickZoneFilter(cityKey, label, event) {
        if (event && event.stopPropagation) event.stopPropagation();
        selectDestination(cityKey, label);
        triggerLivewireSearch();
    }

    function updateSearchGuests(delta) {
        state.guests = Math.max(1, Math.min(8, state.guests + delta));
        const pCount = toPersianNum(state.guests);
        const dGuests = document.getElementById('desktop-guests-label');
        const mGuests = document.getElementById('mobile-sheet-guests');
        const mSub = document.getElementById('mobile-pill-sub');
        if (dGuests) dGuests.textContent = `${pCount} نفر`;
        if (mGuests) mGuests.textContent = pCount;
        if (mSub) mSub.textContent = `${toPersianNum(state.startDay)} تا ${toPersianNum(state.endDay)} ${currentJalaliMonthName} • ${pCount} نفر`;
        // Search values do not live-filter without clicking the search button
    }

    let dateSelectionStep = 'start'; // 'start' | 'end'

    function setDateSelectionStep(step, event) {
        if (event && event.stopPropagation) event.stopPropagation();
        dateSelectionStep = step;
        updateDateLabelsAndSummary();
    }

    function updateDateLabelsAndSummary() {
        const nights = Math.max(1, state.endDay - state.startDay);
        const pStart = toPersianNum(state.startDay);
        const pEnd = toPersianNum(state.endDay);
        const pNights = toPersianNum(nights);
        const summary = `${pStart} تا ${pEnd} ${currentJalaliMonthName} (${pNights} شب)`;

        const dLabel = document.getElementById('desktop-date-label');
        const mSummary = document.getElementById('mobile-calendar-summary');
        const mSub = document.getElementById('mobile-pill-sub');
        const dNights = document.getElementById('desktop-nights-count');
        const mNights = document.getElementById('mobile-nights-count');

        if (dLabel) dLabel.textContent = summary;
        if (mSummary) mSummary.textContent = `${pStart} تا ${pEnd} ${currentJalaliMonthName} (${pNights} شب)`;
        if (mSub) mSub.textContent = `${pStart} تا ${pEnd} ${currentJalaliMonthName} • ${toPersianNum(state.guests)} نفر`;
        if (dNights) dNights.textContent = pNights;
        if (mNights) mNights.textContent = pNights;

        // Update Check-in & Check-out tabs UI in desktop & mobile
        ['desktop', 'mobile'].forEach(ctx => {
            const inBtn = document.getElementById(`${ctx}-checkin-tab-btn`);
            const outBtn = document.getElementById(`${ctx}-checkout-tab-btn`);
            const inVal = document.getElementById(`${ctx}-checkin-val`);
            const outVal = document.getElementById(`${ctx}-checkout-val`);
            const inDot = document.getElementById(`${ctx}-checkin-dot`);
            const outDot = document.getElementById(`${ctx}-checkout-dot`);
            const inBadge = document.getElementById(`${ctx}-checkin-badge`);
            const outBadge = document.getElementById(`${ctx}-checkout-badge`);
            const inStatus = document.getElementById(`${ctx}-checkin-status`);
            const outStatus = document.getElementById(`${ctx}-checkout-status`);

            if (inVal) inVal.textContent = `${pStart} ${currentJalaliMonthName}`;
            if (outVal) outVal.textContent = `${pEnd} ${currentJalaliMonthName}`;
            if (inStatus) inStatus.textContent = 'تاریخ ورود';
            if (outStatus) outStatus.textContent = 'تاریخ خروج';

            const activeCls = 'p-3 rounded-2xl border-2 border-[#007A5E] dark:border-emerald-500 bg-white dark:bg-zinc-800/90 text-right transition-all flex flex-col justify-between min-h-[72px]';
            const inactiveCls = 'p-3 rounded-2xl border border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9] dark:bg-zinc-900/60 text-right hover:border-zinc-300 dark:hover:border-zinc-700 transition-all flex flex-col justify-between min-h-[72px]';

            if (inBtn && outBtn) {
                if (dateSelectionStep === 'start') {
                    inBtn.className = activeCls;
                    outBtn.className = inactiveCls;
                    if (inDot) inDot.classList.remove('hidden');
                    if (outDot) outDot.classList.add('hidden');
                    if (inBadge) inBadge.classList.remove('hidden');
                    if (outBadge) outBadge.classList.add('hidden');
                    if (inStatus) inStatus.className = 'text-[11px] font-bold text-[#007A5E] dark:text-emerald-400';
                    if (outStatus) outStatus.className = 'text-[11px] font-medium text-zinc-500 dark:text-zinc-400';
                    if (inVal) inVal.className = 'text-sm font-black text-[#18181B] dark:text-zinc-100 mt-2.5 block tracking-tight';
                    if (outVal) outVal.className = 'text-sm font-bold text-zinc-700 dark:text-zinc-300 mt-2.5 block tracking-tight';
                } else {
                    inBtn.className = inactiveCls;
                    outBtn.className = activeCls;
                    if (inDot) inDot.classList.add('hidden');
                    if (outDot) outDot.classList.remove('hidden');
                    if (inBadge) inBadge.classList.add('hidden');
                    if (outBadge) outBadge.classList.remove('hidden');
                    if (inStatus) inStatus.className = 'text-[11px] font-medium text-zinc-500 dark:text-zinc-400';
                    if (outStatus) outStatus.className = 'text-[11px] font-bold text-[#007A5E] dark:text-emerald-400';
                    if (inVal) inVal.className = 'text-sm font-bold text-zinc-700 dark:text-zinc-300 mt-2.5 block tracking-tight';
                    if (outVal) outVal.className = 'text-sm font-black text-[#18181B] dark:text-zinc-100 mt-2.5 block tracking-tight';
                }
            }
        });
    }

    function resetAllFilters() {
        state.province = 'all';
        state.city = 'all';
        state.cityLabel = 'همه مقاصد';
        state.category = 'all';
        state.guests = 2;
        state.startDay = currentJalaliDay;
        state.endDay = Math.min(totalMonthDays, currentJalaliDay + 3);
        state.instantOnly = false;
        dateSelectionStep = 'start';

        const dLabel = document.getElementById('desktop-dest-label');
        const mPill = document.getElementById('mobile-pill-dest');
        const dHint = document.getElementById('desktop-dest-selected-hint');
        if (dLabel) dLabel.textContent = 'همه مقاصد';
        if (mPill) mPill.textContent = 'جستجو در استان و شهر مقصد...';
        if (dHint) dHint.textContent = 'مقصد: همه مقاصد';

        updateDateLabelsAndSummary();
        renderJalaliCalendars();
        filterByCategory('all');
    }

    function triggerLivewireSearch() {
        closeAllPopups();
        simulateWireLoading(() => renderListings());
    }

    function switchMobileTab(tabName) {
        state.mobileTab = tabName;
        ['explore', 'favorites', 'trips', 'profile'].forEach(t => {
            const viewEl = document.getElementById('app-tab-' + t);
            const navBtn = document.getElementById('mob-nav-' + t);
            if (viewEl) viewEl.classList.toggle('hidden', t !== tabName);
            if (navBtn) {
                navBtn.className = t === tabName
                    ? 'flex flex-col items-center justify-center gap-1 py-1 text-[#007A5E] dark:text-emerald-400'
                    : 'flex flex-col items-center justify-center gap-1 py-1 text-zinc-400 dark:text-zinc-500';
            }
        });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function renderJalaliCalendars() {
        ['desktop-jalali-grid', 'mobile-jalali-grid'].forEach(gridId => {
            const grid = document.getElementById(gridId);
            if (!grid) return;
            let html = '';
            for (let d = 1; d <= totalMonthDays; d++) {
                const isPast = d < currentJalaliDay;
                const isToday = d === currentJalaliDay;
                const isCheckIn = d === state.startDay;
                const isCheckOut = d === state.endDay;
                const inRange = d > state.startDay && d < state.endDay;

                if (isPast) {
                    html += `
                        <button type="button" disabled
                                title="امکان انتخاب تاریخ در گذشته وجود ندارد"
                                class="py-2 rounded-lg font-medium text-[11px] text-zinc-300 dark:text-zinc-600 line-through cursor-not-allowed select-none opacity-40">
                            ${toPersianNum(d)}
                        </button>
                    `;
                } else if (isCheckIn && isCheckOut) {
                    html += `
                        <button type="button" onclick="selectCalendarDay(${d})"
                                title="تاریخ ورود و خروج"
                                class="py-2 rounded-xl font-black text-xs bg-[#007A5E] text-white shadow-xs transition-transform active:scale-95">
                            ${toPersianNum(d)}
                        </button>
                    `;
                } else if (isCheckIn) {
                    html += `
                        <button type="button" onclick="selectCalendarDay(${d})"
                                title="تاریخ ورود"
                                class="py-2 rounded-r-xl rounded-l-none font-black text-xs bg-[#007A5E] text-white shadow-xs transition-transform active:scale-95">
                            ${toPersianNum(d)}
                        </button>
                    `;
                } else if (isCheckOut) {
                    html += `
                        <button type="button" onclick="selectCalendarDay(${d})"
                                title="تاریخ خروج"
                                class="py-2 rounded-l-xl rounded-r-none font-black text-xs bg-[#007A5E] text-white shadow-xs transition-transform active:scale-95">
                            ${toPersianNum(d)}
                        </button>
                    `;
                } else if (inRange) {
                    html += `
                        <button type="button" onclick="selectCalendarDay(${d})"
                                class="py-2 rounded-none font-bold text-xs bg-emerald-50 dark:bg-emerald-950/40 text-[#007A5E] dark:text-emerald-300 transition-colors">
                            ${toPersianNum(d)}
                        </button>
                    `;
                } else {
                    html += `
                        <button type="button" onclick="selectCalendarDay(${d})"
                                class="py-2 rounded-xl text-xs font-medium text-[#18181B] dark:text-zinc-200 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:text-[#007A5E] dark:hover:text-emerald-300 transition-colors">
                            ${toPersianNum(d)}
                        </button>
                    `;
                }
            }
            grid.innerHTML = html;
        });
    }

    function selectCalendarDay(day) {
        // Strict validation: Reject past dates
        if (day < currentJalaliDay) {
            showToast('امکان انتخاب تاریخ در گذشته وجود ندارد');
            return;
        }

        if (dateSelectionStep === 'start') {
            // User is setting Check-in date: updates startDay and moves to Check-out for next selection
            state.startDay = day;
            if (state.endDay <= day) {
                state.endDay = Math.min(totalMonthDays, day + 1);
            }
            dateSelectionStep = 'end';
        } else {
            // User is setting Check-out date: updates endDay and remains on Check-out
            if (day > state.startDay) {
                state.endDay = day;
            } else {
                state.startDay = day;
                state.endDay = Math.min(totalMonthDays, day + 1);
            }
            dateSelectionStep = 'end';
        }

        updateDateLabelsAndSummary();
        renderJalaliCalendars();
    }

    function toggleDesktopPopup(name, event) {
        if (event && event.stopPropagation) event.stopPropagation();
        ['dest', 'date'].forEach(p => {
            const el = document.getElementById('popup-' + p);
            if (el) el.classList.toggle('hidden', p !== name ? true : !el.classList.contains('hidden'));
        });
    }

    function closeAllPopups() {
        ['dest', 'date'].forEach(p => {
            const el = document.getElementById('popup-' + p);
            if (el) el.classList.add('hidden');
        });
    }

    // Close desktop search popups when clicking anywhere outside on the page
    document.addEventListener('click', function(e) {
        const destWrapper = document.getElementById('desktop-dest-wrapper');
        const dateWrapper = document.getElementById('desktop-date-wrapper');

        const path = (typeof e.composedPath === 'function') ? e.composedPath() : [];
        const isInsideDest = destWrapper && (destWrapper.contains(e.target) || path.includes(destWrapper));
        const isInsideDate = dateWrapper && (dateWrapper.contains(e.target) || path.includes(dateWrapper));

        if (!isInsideDest && !isInsideDate) {
            closeAllPopups();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllPopups();
            closeMobileSheets();
        }
    });

    function openMobileSearchSheet() {
        document.getElementById('mobile-search-sheet').classList.remove('hidden');
    }

    function closeMobileSheets() {
        document.getElementById('mobile-search-sheet').classList.add('hidden');
    }

    function openAuthModal() {
        document.getElementById('simple-dialog-modal').classList.remove('hidden');
    }

    function closeSimpleDialog() {
        document.getElementById('simple-dialog-modal').classList.add('hidden');
    }

    function submitSimpleDialog() {
        closeSimpleDialog();
        showToast('کد تایید ارسال شد');
    }

    // Live Countdown Timer for Last-Minute Section
    let lmRemainingSeconds = (4 * 3600) + (38 * 60) + 19;
    function updateLastMinuteTimerUI() {
        const hEl = document.getElementById('lm-timer-h');
        const mEl = document.getElementById('lm-timer-m');
        const sEl = document.getElementById('lm-timer-s');
        if (!hEl || !mEl || !sEl) return;

        const hrs = String(Math.floor(lmRemainingSeconds / 3600)).padStart(2, '0');
        const mins = String(Math.floor((lmRemainingSeconds % 3600) / 60)).padStart(2, '0');
        const secs = String(lmRemainingSeconds % 60).padStart(2, '0');

        hEl.textContent = toPersianNum(hrs);
        mEl.textContent = toPersianNum(mins);
        sEl.textContent = toPersianNum(secs);
    }

    setInterval(() => {
        if (lmRemainingSeconds > 0) {
            lmRemainingSeconds--;
        } else {
            lmRemainingSeconds = (4 * 3600) + (59 * 60) + 59;
        }
        updateLastMinuteTimerUI();
    }, 1000);

    window.addEventListener('DOMContentLoaded', function () {
        updateDateLabelsAndSummary();
        renderJalaliCalendars();
        renderListings();
        updateLastMinuteTimerUI();
    });
</script>
@endsection
