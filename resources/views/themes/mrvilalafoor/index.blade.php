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

    $currencyLabel = $currencyLabel ?? (\Modules\Properties\Entities\PropertySetting::getCurrencyLabel() ?? 'تومان');

    $rentalTypes = [];
    try {
        if (class_exists(\Modules\Properties\Entities\Property::class)) {
            $rentalTypes = \Modules\Properties\Entities\Property::getRentalPropertyTypes(true);
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
                        <div id="mobile-pill-dest" class="text-xs font-bold text-[#18181B] dark:text-zinc-100 truncate">جستجو بر اساس مقصد، عنوان یا کد اقامتگاه...</div>
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

            <!-- ============================================================= -->
            <!-- HERO PROMOTIONAL BANNER CAROUSEL (بالای عنوان)                 -->
            <!-- ============================================================= -->
            <section id="hero-banner-section" class="pt-3 sm:pt-6 pb-2 max-w-[1360px] mx-auto px-3 sm:px-6 md:px-8 font-sans">
                <style>
                    #hero-slider-track {
                        scroll-snap-type: x mandatory;
                        overscroll-behavior-x: contain;
                        -webkit-overflow-scrolling: touch;
                    }
                    .hero-slide {
                        scroll-snap-align: center;
                    }
                </style>
                <div class="relative group">
                    <!-- Carousel Scroll Track with Peek Effect -->
                    <div id="hero-slider-track"
                         class="flex gap-3 sm:gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory no-scrollbar py-1 px-1 select-none font-sans cursor-grab"
                         style="scrollbar-width: none; -ms-overflow-style: none;">

                        <!-- SLIDE 1: میزبان شوید — ثبت رایگان اقامتگاه (لینک مستقیم به /register) -->
                        <div class="hero-slide shrink-0 w-[88%] sm:w-[92%] md:w-[94%] snap-center rounded-2xl sm:rounded-3xl overflow-hidden shadow-lg relative bg-gradient-to-l from-[#9A3412] via-[#EA580C] to-[#F59E0B] text-white p-4 sm:p-6 md:p-8 flex items-center justify-between min-h-[170px] sm:min-h-[200px] md:min-h-[225px] font-sans transition-transform duration-300"
                             data-slide-index="0">
                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_25%_50%,rgba(255,255,255,0.22),transparent_65%)] pointer-events-none"></div>

                            <!-- Right Side Copy -->
                            <div class="relative z-10 flex-1 min-w-0 pr-1 sm:pr-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-white/20 backdrop-blur-md text-white text-[10px] sm:text-xs font-bold border border-white/25 shadow-xs mb-1.5 sm:mb-2.5 font-sans">
                                    <span>🤝</span>
                                    <span>همکاری با صاحبان اقامتگاه</span>
                                </span>
                                <h3 class="text-lg sm:text-2xl md:text-3xl lg:text-4xl font-black text-white tracking-tight leading-tight font-sans drop-shadow-sm">
                                    میزبان شوید — ثبت رایگان اقامتگاه
                                </h3>
                                <p class="text-xs sm:text-sm md:text-base text-white/95 font-bold leading-relaxed mt-1 sm:mt-2 font-sans">
                                    ویلا و کلبه خود را ثبت کنید و به شبکه سراسری گردشگران بپیوندید
                                </p>
                                <p class="text-[11px] sm:text-xs text-white/80 font-medium mt-0.5 sm:mt-1 hidden sm:block font-sans">
                                    کمیسیون استثنایی، تسویه منظم و سیستم تقویم هوشمند رزرواسیون
                                </p>
                                <a href="{{ url('/register') }}"
                                   class="mt-3 sm:mt-4 px-3.5 sm:px-5 py-1.5 sm:py-2.5 rounded-xl bg-white text-[#EA580C] hover:bg-zinc-50 text-xs sm:text-sm font-black transition-transform active:scale-95 shadow-md inline-flex items-center gap-1.5 font-sans">
                                    <span>ثبت اقامتگاه و میزبانی</span>
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                </a>
                            </div>

                            <!-- Left Side Graphic: 3D Host shield & gold coin tokens -->
                            <div class="relative w-36 sm:w-56 md:w-68 lg:w-76 shrink-0 flex items-center justify-center select-none pointer-events-none mr-2 sm:mr-4">
                                <svg viewBox="0 0 340 220" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto drop-shadow-2xl">
                                    <defs>
                                        <linearGradient id="hostGold" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#FDE047"/>
                                            <stop offset="100%" stop-color="#D97706"/>
                                        </linearGradient>
                                        <linearGradient id="houseGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#FFFBEB"/>
                                            <stop offset="100%" stop-color="#FDE68A"/>
                                        </linearGradient>
                                    </defs>

                                    <!-- House Model -->
                                    <g transform="translate(100, 35)">
                                        <path d="M 60 10 L 10 50 L 25 50 L 25 110 L 95 110 L 95 50 L 110 50 Z" fill="url(#houseGrad)" stroke="#FFFFFF" stroke-width="2.5" />
                                        <rect x="48" y="70" width="24" height="40" rx="3" fill="#B45309" />
                                        <circle cx="66" cy="90" r="2" fill="#FDE68A" />
                                        <polygon points="60,10 10,50 18,53 60,18 102,53 110,50" fill="#EA580C" />
                                        <!-- Superhost Heart Badge -->
                                        <circle cx="60" cy="40" r="12" fill="#EF4444" />
                                        <path d="M 60 45 C 60 45, 53 40, 53 37 C 53 34.5, 55 33, 57 33 C 58.5 33, 60 34.5, 60 34.5 C 60 34.5, 61.5 33, 63 33 C 65 33, 67 34.5, 67 37 C 67 40, 60 45, 60 45 Z" fill="#FFFFFF" />
                                    </g>

                                    <!-- Stacks of Golden Coins -->
                                    <g transform="translate(60, 125)">
                                        <ellipse cx="30" cy="45" rx="22" ry="9" fill="url(#hostGold)" stroke="#FFFFFF" stroke-width="1.2" />
                                        <ellipse cx="30" cy="40" rx="22" ry="9" fill="url(#hostGold)" stroke="#FFFFFF" stroke-width="1.2" />
                                        <ellipse cx="30" cy="35" rx="22" ry="9" fill="url(#hostGold)" stroke="#FFFFFF" stroke-width="1.2" />
                                        <ellipse cx="65" cy="50" rx="20" ry="8" fill="url(#hostGold)" stroke="#FFFFFF" stroke-width="1.2" />
                                        <ellipse cx="65" cy="45" rx="20" ry="8" fill="url(#hostGold)" stroke="#FFFFFF" stroke-width="1.2" />
                                        <ellipse cx="65" cy="40" rx="20" ry="8" fill="url(#hostGold)" stroke="#FFFFFF" stroke-width="1.2" />
                                        <ellipse cx="65" cy="35" rx="20" ry="8" fill="url(#hostGold)" stroke="#FFFFFF" stroke-width="1.2" />
                                        <ellipse cx="65" cy="30" rx="20" ry="8" fill="url(#hostGold)" stroke="#FFFFFF" stroke-width="1.2" />
                                    </g>

                                    <!-- Growth Trend Arrow -->
                                    <path d="M 40 120 Q 90 95 150 70 L 150 82 L 158 65 L 140 68 L 147 75" fill="none" stroke="#22C55E" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                        </div>

                        <!-- SLIDE 2: تا ۳۵٪ تخفیف کلبه‌های جنگلی لفور -->
                        <div class="hero-slide shrink-0 w-[88%] sm:w-[92%] md:w-[94%] snap-center rounded-2xl sm:rounded-3xl overflow-hidden shadow-lg relative bg-gradient-to-l from-[#064E3B] via-[#007A5E] to-[#0D9488] text-white p-4 sm:p-6 md:p-8 flex items-center justify-between min-h-[170px] sm:min-h-[200px] md:min-h-[225px] font-sans transition-transform duration-300"
                             data-slide-index="1">
                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_25%_50%,rgba(255,255,255,0.2),transparent_65%)] pointer-events-none"></div>

                            <!-- Right Side Copy -->
                            <div class="relative z-10 flex-1 min-w-0 pr-1 sm:pr-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-white/20 backdrop-blur-md text-white text-[10px] sm:text-xs font-bold border border-white/25 shadow-xs mb-1.5 sm:mb-2.5 font-sans">
                                    <span>🌲</span>
                                    <span>تخفیف ویژه اقامت پاییزی</span>
                                </span>
                                <h3 class="text-lg sm:text-2xl md:text-3xl lg:text-4xl font-black text-white tracking-tight leading-tight font-sans drop-shadow-sm">
                                    تا ۳۵٪ تخفیف کلبه‌های جنگلی
                                </h3>
                                <p class="text-xs sm:text-sm md:text-base text-white/95 font-bold leading-relaxed mt-1 sm:mt-2 font-sans">
                                    اقامت رویایی در دل طبیعت مه‌آلود لفور و سد البرز
                                </p>
                                <p class="text-[11px] sm:text-xs text-white/80 font-medium mt-0.5 sm:mt-1 hidden sm:block font-sans">
                                    کلبه‌های سوئیسی، استخردار و دوبلکس چوبی با ضمانت تمیزی
                                </p>
                                <button type="button"
                                        onclick="filterByCategory('cottage'); document.getElementById('sections-wrapper')?.scrollIntoView({behavior: 'smooth'})"
                                        class="mt-3 sm:mt-4 px-3.5 sm:px-5 py-1.5 sm:py-2.5 rounded-xl bg-white text-[#007A5E] hover:bg-zinc-50 text-xs sm:text-sm font-black transition-transform active:scale-95 shadow-md inline-flex items-center gap-1.5 font-sans">
                                    <span>مشاهده کلبه‌های تخفیف‌دار</span>
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                </button>
                            </div>

                            <!-- Left Side Graphic: 3D Swiss Cottage in Forest -->
                            <div class="relative w-36 sm:w-56 md:w-68 lg:w-76 shrink-0 flex items-center justify-center select-none pointer-events-none mr-2 sm:mr-4">
                                <svg viewBox="0 0 340 220" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto drop-shadow-2xl">
                                    <defs>
                                        <linearGradient id="cabinWood" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#F59E0B"/>
                                            <stop offset="100%" stop-color="#78350F"/>
                                        </linearGradient>
                                        <linearGradient id="pineGrad1" x1="0%" y1="0%" x2="0%" y2="100%">
                                            <stop offset="0%" stop-color="#10B981"/>
                                            <stop offset="100%" stop-color="#064E3B"/>
                                        </linearGradient>
                                        <linearGradient id="pineGrad2" x1="0%" y1="0%" x2="0%" y2="100%">
                                            <stop offset="0%" stop-color="#34D399"/>
                                            <stop offset="100%" stop-color="#047857"/>
                                        </linearGradient>
                                        <linearGradient id="windowGlow" x1="0%" y1="0%" x2="0%" y2="100%">
                                            <stop offset="0%" stop-color="#FEF08A"/>
                                            <stop offset="100%" stop-color="#F59E0B"/>
                                        </linearGradient>
                                    </defs>

                                    <!-- Pine Trees in Background -->
                                    <path d="M 40 180 L 70 90 L 100 180 Z" fill="url(#pineGrad1)" opacity="0.6" />
                                    <path d="M 230 180 L 265 80 L 300 180 Z" fill="url(#pineGrad1)" opacity="0.5" />
                                    <path d="M 25 185 L 55 110 L 85 185 Z" fill="url(#pineGrad2)" opacity="0.8" />
                                    <path d="M 250 185 L 285 105 L 320 185 Z" fill="url(#pineGrad2)" opacity="0.7" />

                                    <!-- Ground Curve -->
                                    <ellipse cx="170" cy="195" rx="150" ry="25" fill="#064E3B" opacity="0.4" />

                                    <!-- A-Frame Cottage Structure -->
                                    <g transform="translate(95, 28)">
                                        <polygon points="75,8 5,160 145,160" fill="url(#cabinWood)" stroke="#FDE68A" stroke-width="2" />
                                        <line x1="20" y1="130" x2="130" y2="130" stroke="#78350F" stroke-width="1.5" />
                                        <line x1="32" y1="100" x2="118" y2="100" stroke="#78350F" stroke-width="1.5" />
                                        <line x1="48" y1="70" x2="102" y2="70" stroke="#78350F" stroke-width="1.5" />

                                        <!-- Glowing Triangle Window Front -->
                                        <polygon points="75,32 25,155 125,155" fill="url(#windowGlow)" opacity="0.9" />
                                        <line x1="75" y1="32" x2="75" y2="155" stroke="#78350F" stroke-width="2.5" />
                                        <line x1="38" y1="125" x2="112" y2="125" stroke="#78350F" stroke-width="2" />
                                        <line x1="52" y1="95" x2="98" y2="95" stroke="#78350F" stroke-width="2" />

                                        <rect x="0" y="157" width="150" height="10" rx="3" fill="#92400E" stroke="#FDE68A" stroke-width="1" />
                                        <!-- Chimney & Smoke -->
                                        <rect x="98" y="18" width="14" height="35" rx="2" fill="#78350F" />
                                        <circle cx="105" cy="10" r="5" fill="#FFFFFF" opacity="0.4" />
                                        <circle cx="112" cy="3" r="7" fill="#FFFFFF" opacity="0.3" />
                                    </g>

                                    <!-- Campfire Flames -->
                                    <g transform="translate(58, 160)">
                                        <ellipse cx="25" cy="22" rx="15" ry="6" fill="#451A03" />
                                        <path d="M 18 20 Q 25 2 32 20 Z" fill="#F97316" />
                                        <path d="M 21 20 Q 25 8 29 20 Z" fill="#FDE047" />
                                    </g>
                                </svg>
                            </div>
                        </div>

                        <!-- SLIDE 3: رزرو آنی و تحویل فوری کلید -->
                        <div class="hero-slide shrink-0 w-[88%] sm:w-[92%] md:w-[94%] snap-center rounded-2xl sm:rounded-3xl overflow-hidden shadow-lg relative bg-gradient-to-l from-[#3730A3] via-[#4F46E5] to-[#7C3AED] text-white p-4 sm:p-6 md:p-8 flex items-center justify-between min-h-[170px] sm:min-h-[200px] md:min-h-[225px] font-sans transition-transform duration-300"
                             data-slide-index="2">
                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_25%_50%,rgba(255,255,255,0.22),transparent_65%)] pointer-events-none"></div>

                            <!-- Right Side Copy -->
                            <div class="relative z-10 flex-1 min-w-0 pr-1 sm:pr-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-white/20 backdrop-blur-md text-white text-[10px] sm:text-xs font-bold border border-white/25 shadow-xs mb-1.5 sm:mb-2.5 font-sans">
                                    <span>🔑</span>
                                    <span>تحویل قطعی بدون معطلی</span>
                                </span>
                                <h3 class="text-lg sm:text-2xl md:text-3xl lg:text-4xl font-black text-white tracking-tight leading-tight font-sans drop-shadow-sm">
                                    رزرو آنی بدون نیاز به تایید
                                </h3>
                                <p class="text-xs sm:text-sm md:text-base text-white/95 font-bold leading-relaxed mt-1 sm:mt-2 font-sans">
                                    با یک کلیک رزرو کنید و با خیال آسوده راهی سفر شوید
                                </p>
                                <p class="text-[11px] sm:text-xs text-white/80 font-medium mt-0.5 sm:mt-1 hidden sm:block font-sans">
                                    ضمانت ۱۰۰٪ تحویل و تمیزی به همراه پشتیبانی ۲۴ ساعته سفر
                                </p>
                                <button type="button"
                                        onclick="state.instantOnly = true; syncInstantUI(); simulateWireLoading(() => renderListings()); document.getElementById('sections-wrapper')?.scrollIntoView({behavior: 'smooth'})"
                                        class="mt-3 sm:mt-4 px-3.5 sm:px-5 py-1.5 sm:py-2.5 rounded-xl bg-white text-[#4F46E5] hover:bg-zinc-50 text-xs sm:text-sm font-black transition-transform active:scale-95 shadow-md inline-flex items-center gap-1.5 font-sans">
                                    <span>ویلاهای رزرو فوری</span>
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                </button>
                            </div>

                            <!-- Left Side Graphic: 3D Golden Key & Shield -->
                            <div class="relative w-36 sm:w-56 md:w-68 lg:w-76 shrink-0 flex items-center justify-center select-none pointer-events-none mr-2 sm:mr-4">
                                <svg viewBox="0 0 340 220" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto drop-shadow-2xl">
                                    <defs>
                                        <linearGradient id="goldKeyGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#FEF08A"/>
                                            <stop offset="40%" stop-color="#FACC15"/>
                                            <stop offset="80%" stop-color="#EAB308"/>
                                            <stop offset="100%" stop-color="#CA8A04"/>
                                        </linearGradient>
                                        <linearGradient id="shieldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#818CF8"/>
                                            <stop offset="100%" stop-color="#4338CA"/>
                                        </linearGradient>
                                        <linearGradient id="boltGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#FDE047"/>
                                            <stop offset="100%" stop-color="#F59E0B"/>
                                        </linearGradient>
                                    </defs>

                                    <!-- Protection Shield -->
                                    <g transform="translate(70, 30)">
                                        <path d="M 80 15 Q 140 10 140 70 C 140 120, 80 150, 80 150 C 80 150, 20 120, 20 70 Q 20 10 80 15 Z" fill="url(#shieldGrad)" stroke="#C7D2FE" stroke-width="3" opacity="0.85" />
                                        <circle cx="80" cy="70" r="28" fill="#FFFFFF" fill-opacity="0.15" />
                                        <path d="M 68 70 L 76 78 L 94 60" fill="none" stroke="#FFFFFF" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </g>

                                    <!-- 3D Golden Key Crossing Shield -->
                                    <g transform="translate(140, 50) rotate(-28)">
                                        <circle cx="45" cy="45" r="32" fill="url(#goldKeyGrad)" stroke="#FFFFFF" stroke-width="2" />
                                        <circle cx="45" cy="45" r="16" fill="#312E81" />
                                        <rect x="70" y="38" width="105" height="14" rx="4" fill="url(#goldKeyGrad)" stroke="#FFFFFF" stroke-width="1.5" />
                                        <rect x="145" y="50" width="12" height="18" rx="2" fill="url(#goldKeyGrad)" />
                                        <rect x="163" y="50" width="10" height="24" rx="2" fill="url(#goldKeyGrad)" />
                                    </g>

                                    <!-- Lightning Bolt Badge -->
                                    <g transform="translate(45, 120)">
                                        <circle cx="28" cy="28" r="26" fill="#1E1B4B" stroke="#FDE047" stroke-width="2" />
                                        <path d="M 30 12 L 20 28 L 27 28 L 23 44 L 37 25 L 29 25 Z" fill="url(#boltGrad)" />
                                    </g>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Navigation Arrows (Floating on edges - correctly pointing outwards) -->
                    <button type="button" onclick="prevHeroSlide()" aria-label="اسلاید قبلی"
                            class="hidden md:flex absolute top-1/2 -translate-y-1/2 -right-3 lg:-right-5 w-10 h-10 rounded-full bg-white/95 dark:bg-zinc-900/95 text-zinc-700 dark:text-zinc-200 border border-zinc-200/80 dark:border-zinc-700 shadow-xl items-center justify-center hover:bg-white dark:hover:bg-zinc-800 transition-all z-20 opacity-0 group-hover:opacity-100 active:scale-90 font-sans cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <button type="button" onclick="nextHeroSlide()" aria-label="اسلاید بعدی"
                            class="hidden md:flex absolute top-1/2 -translate-y-1/2 -left-3 lg:-left-5 w-10 h-10 rounded-full bg-white/95 dark:bg-zinc-900/95 text-zinc-700 dark:text-zinc-200 border border-zinc-200/80 dark:border-zinc-700 shadow-xl items-center justify-center hover:bg-white dark:hover:bg-zinc-800 transition-all z-20 opacity-0 group-hover:opacity-100 active:scale-90 font-sans cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>

                    <!-- Modern Pagination Indicators (Elongated Active Pill Dot matching reference image) -->
                    <div id="hero-slider-dots" class="flex items-center justify-center gap-1.5 mt-2.5 sm:mt-3.5 font-sans">
                        <button type="button" onclick="goToHeroSlide(0)" class="hero-dot w-6 h-2 rounded-full bg-[#EA580C] dark:bg-amber-400 transition-all duration-300 font-sans cursor-pointer" aria-label="اسلاید ۱"></button>
                        <button type="button" onclick="goToHeroSlide(1)" class="hero-dot w-2 h-2 rounded-full bg-zinc-300 dark:bg-zinc-700 hover:bg-zinc-400 dark:hover:bg-zinc-600 transition-all duration-300 font-sans cursor-pointer" aria-label="اسلاید ۲"></button>
                        <button type="button" onclick="goToHeroSlide(2)" class="hero-dot w-2 h-2 rounded-full bg-zinc-300 dark:bg-zinc-700 hover:bg-zinc-400 dark:hover:bg-zinc-600 transition-all duration-300 font-sans cursor-pointer" aria-label="اسلاید ۳"></button>
                    </div>
                </div>
            </section>

            <!-- DESKTOP ULTRA-MINIMAL HERO & SEARCH BAR -->
            <section class="hidden md:block pt-6 pb-8">
                <div class="max-w-4xl mx-auto px-8 text-center">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#18181B] dark:text-zinc-100 tracking-tight mb-8">
                        {{ $appName }} . اجاره ویلا، کلبه، بومگردی و انواع اقامتگاه در شمال
                    </h1>

                    <!-- Clean 3-Part Flat Search Bar -->
                    <div class="bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 rounded-2xl p-2 grid grid-cols-12 gap-2 items-center text-right shadow-sm">
                <!-- 1. Zone -->
                @php
                    $availableDests = $availableDestinations ?? [];
                    $allDestCities = collect($availableDests)->flatMap(function($g) {
                        return collect($g['cities'] ?? [])->map(function($c) use ($g) {
                            return ['city' => $c, 'province' => $g['province'] ?? ''];
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
                    $firstDayOfWeek = $jalaliNow ? (new \Morilog\Jalali\Jalalian($currentJalaliYear, $currentJalaliMonth, 1))->getDayOfWeek() : 0;

                    $calendarMonths = [];
                    if ($jalaliNow) {
                        $baseMonth = new \Morilog\Jalali\Jalalian($currentJalaliYear, $currentJalaliMonth, 1);
                        for ($m = 0; $m < 12; $m++) {
                            $mDate = ($m === 0) ? $baseMonth : $baseMonth->addMonths($m);
                            $y = $mDate->getYear();
                            $mNum = $mDate->getMonth();
                            $calendarMonths[] = [
                                'index' => $m,
                                'year' => $y,
                                'month' => $mNum,
                                'name' => $mDate->format('%B'),
                                'daysCount' => $mDate->getMonthDays(),
                                'firstDayOfWeek' => (new \Morilog\Jalali\Jalalian($y, $mNum, 1))->getDayOfWeek(),
                                'isCurrentMonth' => ($m === 0),
                                'currentDay' => ($m === 0 ? $currentJalaliDay : 0),
                            ];
                        }
                    } else {
                        $calendarMonths[] = [
                            'index' => 0,
                            'year' => 1405,
                            'month' => 8,
                            'name' => 'آبان',
                            'daysCount' => 30,
                            'firstDayOfWeek' => 6,
                            'isCurrentMonth' => true,
                            'currentDay' => 9,
                        ];
                    }

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
                            <span class="block text-[11px] text-zinc-400 font-medium">مقصد، عنوان یا کد اقامتگاه</span>
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
                                   onkeydown="handleSearchInputKey(event, 'desktop')"
                                   placeholder="جستجوی شهر، استان، نام یا کد اقامتگاه (مثلاً ۱۰۲)..."
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
                        <div class="max-h-64 overflow-y-auto no-scrollbar" id="desktop-dest-content-area">

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

                                @if(!empty($allDestCities))
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
                                @endif
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

                                <!-- Month Navigation Bar (Desktop) -->
                                <div class="flex items-center justify-between p-1 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl mb-2.5 font-sans border border-zinc-200/60 dark:border-zinc-700/60">
                                    <button type="button" onclick="changeCalendarMonth(-1)"
                                            class="cal-prev-btn w-7 h-7 rounded-lg flex items-center justify-center text-zinc-600 dark:text-zinc-300 hover:bg-white dark:hover:bg-zinc-700 disabled:opacity-20 disabled:cursor-not-allowed transition-all font-sans"
                                            title="ماه قبل">
                                        <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                    <div class="flex items-center gap-1.5 font-sans">
                                        <span class="cal-month-title text-xs font-black text-[#18181B] dark:text-zinc-100 font-sans">
                                            {{ $currentJalaliMonthName }} {{ $currentJalaliYear }}
                                        </span>
                                    </div>
                                    <button type="button" onclick="changeCalendarMonth(1)"
                                            class="cal-next-btn w-7 h-7 rounded-lg flex items-center justify-center text-zinc-600 dark:text-zinc-300 hover:bg-white dark:hover:bg-zinc-700 disabled:opacity-20 disabled:cursor-not-allowed transition-all font-sans"
                                            title="ماه بعد">
                                        <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </div>

                                <div class="grid grid-cols-7 gap-1.5 sm:gap-2 text-center text-[11px] font-bold text-zinc-400 dark:text-zinc-500 mb-2 font-sans">
                                    <span>ش</span><span>ی</span><span>د</span><span>س</span><span>چ</span><span>پ</span><span class="text-rose-500 font-black">ج</span>
                                </div>
                                <div id="desktop-jalali-grid" class="grid grid-cols-7 gap-1.5 sm:gap-2 text-center text-xs font-sans"></div>
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

                        <!-- 3. Guests & Instant Booking & Search Button -->
                        <div class="col-span-4 flex items-center justify-between pl-1">
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="updateSearchGuests(-1)" class="w-7 h-7 rounded-lg bg-[#F7F8F9] dark:bg-zinc-800 hover:bg-[#ECECEE] dark:hover:bg-zinc-700 flex items-center justify-center text-xs font-bold transition-colors">−</button>
                                <div class="text-right">
                                    <span class="block text-[10px] text-zinc-400 font-medium leading-none">ظرفیت</span>
                                    <span id="desktop-guests-label" class="text-xs font-bold text-[#18181B] dark:text-zinc-100">۲ نفر</span>
                                </div>
                                <button type="button" onclick="updateSearchGuests(1)" class="w-7 h-7 rounded-lg bg-[#F7F8F9] dark:bg-zinc-800 hover:bg-[#ECECEE] dark:hover:bg-zinc-700 flex items-center justify-center text-xs font-bold transition-colors">+</button>
                            </div>

                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <button type="button" 
                                        id="desktop-instant-btn" 
                                        onclick="toggleInstantFilter(event)"
                                        title="فیلتر فقط اقامتگاه‌های رزرو قطعی آنی"
                                        class="px-2.5 py-2.5 rounded-xl text-xs font-bold transition-all border border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9] dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:border-[#007A5E] hover:text-[#007A5E] dark:hover:text-emerald-400 flex items-center gap-1 shadow-2xs">
                                    <span class="text-xs text-amber-500">⚡</span>
                                    <span class="text-[11px] hidden lg:inline">رزرو آنی</span>
                                </button>
                                <button type="button" onclick="triggerLivewireSearch()" class="bg-[#007A5E] hover:bg-[#005F48] text-white p-3 rounded-xl transition-colors shadow-sm" title="جستجو">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Zone & Instant Filter Pills -->
                    <div class="flex items-center justify-center flex-wrap gap-2 mt-5">
                        <button type="button" 
                                id="instant-pill-btn"
                                onclick="toggleInstantFilter(event)"
                                class="px-4 py-2 rounded-full text-xs font-bold transition-all border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 hover:border-[#007A5E] hover:text-[#007A5E] dark:hover:text-emerald-400 shadow-xs flex items-center gap-1.5 active:scale-95">
                            <span class="text-amber-500 text-xs">⚡</span>
                            <span>رزرو آنی</span>
                        </button>

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

            <!-- DYNAMIC FLAT CATEGORY GRID (Responsive Mobile & Desktop) -->
            <section class="max-w-[1360px] mx-auto px-4 sm:px-8 pt-4 sm:pt-2 pb-2">
                <div class="flex items-center gap-2.5 sm:gap-4 overflow-x-auto no-scrollbar pb-1">
                    <!-- Box 1: All -->
                    <button type="button" onclick="filterByCategory('all')" data-cat="all" class="cat-box shrink-0 min-w-[76px] sm:min-w-[110px] flex-1 flex flex-col items-center justify-center gap-1.5 sm:gap-2 py-3 sm:py-4 px-2 rounded-2xl border border-[#007A5E] bg-[#ECFDF5]/70 dark:bg-emerald-950/30 text-[#007A5E] dark:text-emerald-400 transition-all active:scale-[0.98]">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                        </svg>
                        <span class="text-[11px] sm:text-xs font-bold truncate">همه</span>
                    </button>

                    @foreach($rentalTypes as $typeKey => $typeItem)
                    <button type="button" onclick="filterByCategory('{{ $typeKey }}')" data-cat="{{ $typeKey }}" class="cat-box shrink-0 min-w-[76px] sm:min-w-[110px] flex-1 flex flex-col items-center justify-center gap-1.5 sm:gap-2 py-3 sm:py-4 px-2 rounded-2xl border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-zinc-300 dark:hover:border-zinc-700 text-zinc-600 dark:text-zinc-400 transition-all active:scale-[0.98]">
                        @if($typeKey === 'cottage')
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h12a1 1 0 001-1V10M9 21v-6a3 3 0 016 0v6"/>
                        </svg>
                        @elseif($typeKey === 'villa')
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                        </svg>
                        @elseif($typeKey === 'apartment')
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                        </svg>
                        @elseif($typeKey === 'ecolodge')
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582"/>
                        </svg>
                        @else
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.75H6v17.25h2.25z"/>
                        </svg>
                        @endif
                        <span class="text-[11px] sm:text-xs font-medium truncate">{{ $typeItem['label'] ?? $typeKey }}</span>
                    </button>
                    @endforeach
                </div>
            </section>

            <!-- LISTINGS SECTION CONTAINER (Dynamic Carousels) -->
            <main class="max-w-[1360px] mx-auto px-4 sm:px-8 py-6 sm:py-10">
                <!-- Active Filters Notification Bar -->
                <div id="active-filters-bar" class="hidden mb-6 font-sans"></div>

                <!-- Dynamic Sections Carousel Container -->
                <div id="sections-wrapper" class="space-y-12 sm:space-y-16"></div>

                <!-- Minimal Empty State -->
                <div id="empty-state" class="hidden py-20 text-center space-y-3 font-sans">
                    <div class="w-14 h-14 rounded-2xl bg-[#ECFDF5] dark:bg-emerald-950/40 text-[#007A5E] dark:text-emerald-400 flex items-center justify-center mx-auto shadow-xs">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <p id="empty-state-title" class="text-sm font-bold text-[#18181B] dark:text-zinc-100">اقامتگاهی با این مشخصات یافت نشد</p>
                    <p id="empty-state-subtitle" class="text-xs text-zinc-400 max-w-sm mx-auto leading-relaxed">می‌توانید فیلترهای جستجو را پاکسازی کرده یا مقاصد دیگر را انتخاب نمایید.</p>
                    <button id="empty-state-btn" type="button" onclick="resetAllFilters()" class="mt-2 px-5 py-2.5 rounded-xl bg-[#007A5E] hover:bg-[#005F48] text-xs font-bold text-white transition-colors shadow-sm">
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
                                <img src="{{ $bk->property?->featured_image_url ?? asset('images/property-placeholder.svg') }}" alt="{{ $bk->property?->title }}" class="w-16 h-16 rounded-xl object-cover shrink-0">
                                <div>
                                    <h3 class="text-sm font-bold text-[#18181B] dark:text-zinc-100">{{ $bk->property?->title ?? 'اقامتگاه بوم‌گردی' }}</h3>
                                    <p class="text-xs text-zinc-400 mt-1">{{ $bk->checkin_date }} تا {{ $bk->checkout_date }} ({{ $bk->nights }} شب)</p>
                                    <span class="inline-block mt-2 text-xs font-bold text-[#007A5E] dark:text-emerald-400">{{ number_format($bk->total_amount) }} {{ $currencyLabel }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="border border-[#ECECEE] dark:border-zinc-800 rounded-2xl p-8 bg-white dark:bg-zinc-900 shadow-sm text-center space-y-3 font-sans">
                    <div class="w-12 h-12 rounded-2xl bg-zinc-100 dark:bg-zinc-800 text-zinc-400 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="text-xs font-bold text-[#18181B] dark:text-zinc-200">هنوز سفری ثبت نشده است</h3>
                    <p class="text-[11px] text-zinc-400 leading-relaxed max-w-xs mx-auto">رزروهای اقامتی شما پس از ثبت در این بخش قابل مشاهده و پیگیری خواهند بود.</p>
                    <a href="{{ route('properties.index') }}" class="inline-block px-4 py-2 rounded-xl bg-[#007A5E] hover:bg-[#005F48] text-white text-xs font-bold transition shadow-sm">
                        مشاهده اقامتگاه‌ها
                    </a>
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
                <h3 class="text-sm font-bold text-[#18181B] dark:text-zinc-100">{{ auth()->check() ? auth()->user()->name : 'کاربر مهمان' }}</h3>
                <span class="text-xs text-zinc-400 mt-0.5 block">{{ auth()->check() ? (auth()->user()->mobile ?? auth()->user()->phone ?? auth()->user()->email) : 'جهت دسترسی به امکانات کامل وارد شوید' }}</span>
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
    <!-- MOBILE FULLSCREEN SEARCH MODAL (Airbnb-Style Minimal Progressive Disclosure) -->
    <!-- ===================================================================== -->
    <div id="mobile-search-sheet" class="hidden fixed inset-0 z-50 bg-[#F8F9FA] dark:bg-zinc-950 flex flex-col h-full w-full overflow-hidden font-sans">
        
        <!-- Top App Bar -->
        <div class="flex items-center justify-between px-4 py-3 bg-white dark:bg-zinc-900 border-b border-[#ECECEE] dark:border-zinc-800 shrink-0 font-sans">
            <button type="button" onclick="closeMobileSheets()" aria-label="بستن" class="w-8 h-8 rounded-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 text-xs text-zinc-600 dark:text-zinc-300 flex items-center justify-center active:scale-95 transition-transform shadow-2xs font-sans">
                ✕
            </button>
            <h3 class="text-xs font-black text-[#18181B] dark:text-zinc-100 font-sans">فیلتر و جستجوی اقامتگاه</h3>
            <div class="w-8"></div>
        </div>

        <!-- Scrollable Modal Content (3 Progressive Accordion Cards) -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3 no-scrollbar font-sans">

            <!-- ================= CARD 1: DESTINATION ================= -->
            <div id="m-card-dest" class="transition-all duration-200 font-sans">
                <!-- Collapsed State -->
                <div id="m-section-dest-collapsed" onclick="setMobileSearchSection('dest')" class="hidden cursor-pointer bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 rounded-2xl px-4 py-3.5 flex items-center justify-between shadow-2xs active:scale-[0.99] transition-all font-sans">
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-zinc-400">📍</span>
                        <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400">کجا؟</span>
                    </div>
                    <span id="m-summary-dest-val" class="text-xs font-black text-[#18181B] dark:text-zinc-100 font-sans">همه مقاصد</span>
                </div>

                <!-- Expanded State -->
                <div id="m-section-dest-expanded" class="bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 rounded-3xl p-4.5 sm:p-5 shadow-xs space-y-3.5 font-sans">
                    <div class="flex items-center justify-between">
                        <h4 class="text-base font-black text-[#18181B] dark:text-zinc-100 font-sans">کجا می‌روید؟</h4>
                        <span class="text-[11px] text-zinc-400 font-medium font-sans">مقصد، نام یا کد</span>
                    </div>

                    <!-- Search Input -->
                    <div class="relative">
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-zinc-400">
                            <svg class="w-4 h-4 text-[#007A5E]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                            </svg>
                        </div>
                        <input type="text"
                               id="mobile-dest-search-input"
                               oninput="filterDestinationsLive(this.value, 'mobile')"
                               onkeydown="handleSearchInputKey(event, 'mobile')"
                               placeholder="جستجوی شهر، استان، نام یا کد ویلا..."
                               class="w-full bg-[#F7F8F9] dark:bg-zinc-800/80 border border-[#ECECEE] dark:border-zinc-700/80 rounded-2xl pr-10 pl-8 py-3 text-xs font-bold text-[#18181B] dark:text-zinc-100 placeholder-zinc-400 outline-none focus:border-[#007A5E] focus:bg-white dark:focus:bg-zinc-900 transition-all font-sans">
                        <button type="button"
                                id="mobile-dest-search-clear"
                                onclick="clearDestSearch('mobile')"
                                class="hidden absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                            ✕
                        </button>
                    </div>

                    <!-- Live Search Results (Shows only when typing) -->
                    <div id="mobile-search-results" class="hidden pt-1 max-h-56 overflow-y-auto no-scrollbar font-sans">
                        <div class="text-[11px] font-bold text-zinc-400 mb-2 px-1 font-sans">نتایج جستجو:</div>
                        <div id="mobile-search-items" class="space-y-1.5 font-sans"></div>
                        <div id="mobile-search-empty" class="hidden py-8 text-center text-xs text-zinc-400 font-sans">
                            موردی با این مشخصات یافت نشد.
                        </div>
                    </div>

                    <!-- Popular Destination Chips (Default when not searching) -->
                    <div id="mobile-popular-dest-container" class="space-y-2 pt-1 font-sans">
                        <div class="text-[11px] font-bold text-zinc-400 px-0.5 font-sans">مقاصد پرطرفدار:</div>
                        <div class="flex items-center flex-wrap gap-2">
                            <button type="button"
                                    onclick="selectDestination('all', 'همه مقاصد', event); setMobileSearchSection('date');"
                                    data-dest-key="all"
                                    class="px-3.5 py-2 rounded-xl text-xs font-bold border border-zinc-200 dark:border-zinc-700 bg-[#F7F8F9] dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover:border-[#007A5E] hover:text-[#007A5E] active:scale-95 transition-all flex items-center gap-1.5 font-sans">
                                <span>🗺️</span>
                                <span>همه مقاصد</span>
                            </button>
                            @foreach($allDestCities as $item)
                                <button type="button"
                                        onclick="selectDestination('{{ $item['city'] }}', '{{ $item['city'] }}', event); setMobileSearchSection('date');"
                                        data-dest-key="{{ $item['city'] }}"
                                        class="px-3.5 py-2 rounded-xl text-xs font-bold border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 text-zinc-700 dark:text-zinc-200 hover:border-[#007A5E] hover:text-[#007A5E] active:scale-95 transition-all flex items-center gap-1.5 font-sans">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>{{ $item['city'] }}</span>
                                    <span class="text-[10px] text-zinc-400 font-normal font-sans">({{ $item['province'] }})</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= CARD 2: DATE ================= -->
            <div id="m-card-date" class="transition-all duration-200 font-sans">
                <!-- Collapsed State -->
                <div id="m-section-date-collapsed" onclick="setMobileSearchSection('date')" class="cursor-pointer bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 rounded-2xl px-4 py-3.5 flex items-center justify-between shadow-2xs active:scale-[0.99] transition-all font-sans">
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-emerald-600 dark:text-emerald-400">📅</span>
                        <span class="text-xs font-bold text-zinc-600 dark:text-zinc-300">تاریخ سفر</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="m-summary-date-val" class="text-xs font-black text-[#18181B] dark:text-zinc-100 font-sans">{{ $defaultStartDay }} تا {{ $defaultEndDay }} {{ $currentJalaliMonthName }}</span>
                        <span class="text-xs text-zinc-400 font-sans">✎</span>
                    </div>
                </div>

                <!-- Expanded State -->
                <div id="m-section-date-expanded" class="hidden bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 rounded-3xl p-4 sm:p-5 shadow-xs space-y-4 font-sans">
                    <!-- Header -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-black text-[#18181B] dark:text-zinc-100 font-sans">چه زمانی سفر می‌کنید؟</h4>
                            <div class="flex items-center gap-1.5 mt-0.5 text-xs text-zinc-500 dark:text-zinc-400 font-sans">
                                <span>مدت اقامت:</span>
                                <span class="inline-flex items-center gap-1 font-black text-[#007A5E] dark:text-emerald-400 font-sans">
                                    <span>🌙</span>
                                    <span id="mobile-nights-count">{{ $defaultNights }}</span>
                                    <span>شب</span>
                                </span>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-[#007A5E] dark:text-emerald-300 text-xs font-black border border-emerald-200/60 dark:border-emerald-800/60 font-sans">
                            <span>🗓️</span>
                            <span>{{ $currentJalaliMonthName }} {{ $currentJalaliYear }}</span>
                        </span>
                    </div>

                    <!-- Check-in & Check-out Dual Buttons (Side-by-Side, Zero Overlap) -->
                    <div class="grid grid-cols-2 gap-2.5 font-sans">
                        <!-- Check-in -->
                        <button type="button" id="mobile-checkin-tab-btn" onclick="setDateSelectionStep('start', event)"
                                class="p-3 rounded-2xl text-right transition-all bg-white dark:bg-zinc-900 border-2 border-[#007A5E] dark:border-emerald-500 shadow-xs font-sans">
                            <div class="flex items-center justify-between">
                                <span id="mobile-checkin-status" class="text-[10px] text-[#007A5E] dark:text-emerald-400 font-bold font-sans">تاریخ ورود</span>
                                <span id="mobile-checkin-dot" class="w-2 h-2 rounded-full bg-[#007A5E] dark:text-emerald-400"></span>
                            </div>
                            <span id="mobile-checkin-val" class="block text-xs font-black text-[#18181B] dark:text-zinc-100 mt-1 tracking-tight font-sans">{{ $defaultStartDay }} {{ $currentJalaliMonthName }}</span>
                        </button>

                        <!-- Check-out -->
                        <button type="button" id="mobile-checkout-tab-btn" onclick="setDateSelectionStep('end', event)"
                                class="p-3 rounded-2xl text-right transition-all bg-[#F7F8F9] dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/80 hover:border-zinc-300 font-sans">
                            <div class="flex items-center justify-between">
                                <span id="mobile-checkout-status" class="text-[10px] text-zinc-500 dark:text-zinc-400 font-medium font-sans">تاریخ خروج</span>
                                <span id="mobile-checkout-dot" class="hidden w-2 h-2 rounded-full bg-[#007A5E] dark:text-emerald-400"></span>
                            </div>
                            <span id="mobile-checkout-val" class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mt-1 tracking-tight font-sans">{{ $defaultEndDay }} {{ $currentJalaliMonthName }}</span>
                        </button>
                    </div>

                    <!-- Quick Stay Duration Chips -->
                    <div class="space-y-1.5 font-sans">
                        <span class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 block px-0.5">مدت اقامت سریع:</span>
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar text-xs font-sans">
                            <button type="button" onclick="setQuickDaysRange(1)" class="quick-stay-chip px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700/80 bg-[#F7F8F9] dark:bg-zinc-800/80 text-zinc-700 dark:text-zinc-300 text-[11px] font-bold shrink-0 hover:border-[#007A5E] hover:text-[#007A5E] active:scale-95 transition-all font-sans" data-nights="1">
                                ⚡ ۱ شب
                            </button>
                            <button type="button" onclick="setQuickDaysRange(2)" class="quick-stay-chip px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700/80 bg-[#F7F8F9] dark:bg-zinc-800/80 text-zinc-700 dark:text-zinc-300 text-[11px] font-bold shrink-0 hover:border-[#007A5E] hover:text-[#007A5E] active:scale-95 transition-all font-sans" data-nights="2">
                                🌿 ۲ شب (آخر هفته)
                            </button>
                            <button type="button" onclick="setQuickDaysRange(3)" class="quick-stay-chip px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700/80 bg-[#F7F8F9] dark:bg-zinc-800/80 text-zinc-700 dark:text-zinc-300 text-[11px] font-bold shrink-0 hover:border-[#007A5E] hover:text-[#007A5E] active:scale-95 transition-all font-sans" data-nights="3">
                                ✨ ۳ شب
                            </button>
                            <button type="button" onclick="setQuickDaysRange(7)" class="quick-stay-chip px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700/80 bg-[#F7F8F9] dark:bg-zinc-800/80 text-zinc-700 dark:text-zinc-300 text-[11px] font-bold shrink-0 hover:border-[#007A5E] hover:text-[#007A5E] active:scale-95 transition-all font-sans" data-nights="7">
                                🏡 ۱ هفته کامل
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Grid Frame -->
                    <div class="p-3.5 bg-[#F7F8F9] dark:bg-zinc-800/40 rounded-2xl border border-zinc-200/70 dark:border-zinc-800/70 font-sans">
                        <!-- Month Navigation Bar (Mobile) -->
                        <div class="flex items-center justify-between p-1 bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl mb-3 font-sans shadow-2xs">
                            <button type="button" onclick="changeCalendarMonth(-1)"
                                    class="cal-prev-btn w-9 h-9 rounded-xl flex items-center justify-center text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 disabled:opacity-20 disabled:cursor-not-allowed transition-all active:scale-90 font-sans"
                                    title="ماه قبل">
                                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <div class="flex items-center gap-2 font-sans">
                                <span class="cal-month-title text-sm font-black text-[#18181B] dark:text-zinc-100 font-sans">
                                    {{ $currentJalaliMonthName }} {{ $currentJalaliYear }}
                                </span>
                                <span class="cal-month-badge px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-[#007A5E] dark:text-emerald-400 text-[10px] font-bold font-sans">
                                    ماه جاری
                                </span>
                            </div>
                            <button type="button" onclick="changeCalendarMonth(1)"
                                    class="cal-next-btn w-9 h-9 rounded-xl flex items-center justify-center text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 disabled:opacity-20 disabled:cursor-not-allowed transition-all active:scale-90 font-sans"
                                    title="ماه بعد">
                                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-7 gap-1.5 sm:gap-2 text-center text-zinc-400 dark:text-zinc-500 text-[11px] font-bold mb-2 font-sans">
                            <span>ش</span>
                            <span>ی</span>
                            <span>د</span>
                            <span>س</span>
                            <span>چ</span>
                            <span>پ</span>
                            <span class="text-rose-500 dark:text-rose-400 font-black">ج</span>
                        </div>
                        <div id="mobile-jalali-grid" class="grid grid-cols-7 gap-1.5 sm:gap-2 text-center text-xs font-sans"></div>
                    </div>

                    <!-- Footer of Date Card: Status Indicator -->
                    <div class="flex items-center justify-between pt-1 text-xs font-sans">
                        <div class="flex items-center gap-1.5 text-[11px] text-zinc-600 dark:text-zinc-300 font-sans">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                            <span id="mobile-calendar-summary" class="font-bold">{{ $defaultStartDay }} تا {{ $defaultEndDay }} {{ $currentJalaliMonthName }} ({{ $defaultNights }} شب)</span>
                        </div>
                        <button type="button" onclick="setMobileSearchSection('dest')" class="text-[11px] font-bold text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 font-sans">
                            بستن تقویم ↑
                        </button>
                    </div>
                </div>
            </div>

            <!-- ================= STANDALONE OPTIONS: GUESTS & INSTANT ================= -->
            <div class="bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 rounded-3xl p-4 sm:p-5 shadow-xs space-y-4 font-sans">
                <!-- Guests Counter Row -->
                <div class="flex items-center justify-between font-sans">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-[#007A5E] dark:text-emerald-400 flex items-center justify-center text-lg shrink-0 font-sans shadow-2xs border border-emerald-100 dark:border-emerald-900/40">
                            👥
                        </div>
                        <div>
                            <span class="block text-xs font-black text-[#18181B] dark:text-zinc-100 font-sans">تعداد نفرات</span>
                            <span class="block text-[11px] text-zinc-400 mt-0.5 font-sans">همسفران این اقامت</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 bg-[#F7F8F9] dark:bg-zinc-800/80 p-1 rounded-2xl border border-zinc-200/80 dark:border-zinc-700 font-sans">
                        <button type="button" onclick="updateSearchGuests(-1)" aria-label="کاهش"
                                class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-700 border border-zinc-200 dark:border-zinc-600 font-black text-sm text-[#18181B] dark:text-zinc-100 flex items-center justify-center active:scale-90 hover:bg-zinc-50 transition-all shadow-2xs font-sans">−</button>
                        <div class="w-9 text-center font-sans">
                            <span id="mobile-sheet-guests" class="font-black text-sm text-[#18181B] dark:text-zinc-100 font-sans">۲</span>
                            <span class="text-[9px] text-zinc-400 block -mt-1 font-sans">نفر</span>
                        </div>
                        <button type="button" onclick="updateSearchGuests(1)" aria-label="افزایش"
                                class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-700 border border-zinc-200 dark:border-zinc-600 font-black text-sm text-[#18181B] dark:text-zinc-100 flex items-center justify-center active:scale-90 hover:bg-zinc-50 transition-all shadow-2xs font-sans">+</button>
                    </div>
                </div>

                <div class="h-px bg-zinc-100 dark:bg-zinc-800"></div>

                <!-- Instant Booking Row with iOS Toggle Switch -->
                <label class="flex items-center justify-between cursor-pointer select-none font-sans group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-500 flex items-center justify-center text-lg shrink-0 font-sans shadow-2xs border border-amber-100 dark:border-amber-900/40">
                            ⚡
                        </div>
                        <div>
                            <span class="block text-xs font-black text-[#18181B] dark:text-zinc-100 font-sans">فقط رزرو قطعی آنی</span>
                            <span class="block text-[11px] text-zinc-400 mt-0.5 font-sans">رزرو فوری بدون نیاز به تایید میزبان</span>
                        </div>
                    </div>
                    <!-- iOS Toggle Switch -->
                    <div class="relative">
                        <input type="checkbox" id="mobile-instant-checkbox" onchange="toggleInstantFilter(event, this.checked)" class="sr-only peer">
                        <div class="w-11 h-6 bg-zinc-200 dark:bg-zinc-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#007A5E] dark:peer-checked:bg-emerald-600"></div>
                    </div>
                </label>
            </div>

        </div>

        <!-- Sticky Bottom Bar -->
        <div class="px-4 py-3.5 bg-white dark:bg-zinc-900 border-t border-[#ECECEE] dark:border-zinc-800 flex items-center justify-between shrink-0 font-sans shadow-lg gap-3">
            <button type="button" onclick="resetAllFilters()"
                    class="px-4 py-3 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-xs font-bold text-zinc-600 dark:text-zinc-300 hover:bg-rose-50 hover:border-rose-200 hover:text-rose-600 dark:hover:bg-rose-950/40 dark:hover:border-rose-800 dark:hover:text-rose-400 active:scale-95 transition-all font-sans flex items-center gap-1.5 shadow-2xs no-underline shrink-0">
                <svg class="w-3.5 h-3.5 text-zinc-400 group-hover:text-rose-500 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                </svg>
                <span>پاکسازی همه</span>
            </button>
            <button type="button" onclick="closeMobileSheets(); triggerLivewireSearch();"
                    class="flex-1 bg-[#007A5E] hover:bg-[#005F48] text-white font-black py-3 px-5 rounded-2xl text-xs transition-all shadow-sm flex items-center justify-center gap-2 active:scale-95 font-sans no-underline">
                <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
                <span>مشاهده اقامتگاه‌ها</span>
            </button>
        </div>

    </div>

</div>

<!-- SCRIPT ENGINE -->
<script>
    const rentalMultiHostEnabled = {{ $rentalMultiHostEnabled ? 'true' : 'false' }};
    const rentalModeEnabled = {{ $rentalModeEnabled ? 'true' : 'false' }};
    const currencyLabel = '{{ $currencyLabel }}';

    function toPersianNum(num) {
        if (num === null || num === undefined) return '';
        const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return num.toString().replace(/\d/g, x => persianDigits[x]);
    }

    function normalizePersianDigits(str) {
        if (str === null || str === undefined) return '';
        const persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        const arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        let res = String(str);
        for (let i = 0; i < 10; i++) {
            res = res.replaceAll(persian[i], String(i)).replaceAll(arabic[i], String(i));
        }
        return res;
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

    // دیتای اقامتگاه‌ها منحصراً از پایگاه‌داده بارگذاری می‌شود و هیچ داده فیکی رندر نمی‌شود
    const DEFAULT_LISTINGS = [];

    @php
        $dbListings = [];
        $propertyList = $properties ?? $latestProperties ?? [];
        if (count($propertyList) > 0) {
            foreach ($propertyList as $prop) {
                // اعتبارسنجی قطعی وضعیت انتشار و تایید اقامتگاه
                if (($prop->publication_status ?? 'published') !== 'published') {
                    continue;
                }
                if (($prop->approval_status ?? 'approved') !== 'approved') {
                    continue;
                }
                if (($prop->listing_type ?? 'daily_rental') !== 'daily_rental') {
                    continue;
                }

                $rentalConfig = $prop->rentalConfig;
                $baseDailyPrice = (float) ($rentalConfig?->price_per_night ?? $prop->rent_price ?? $prop->price ?? 0);

                // محاسبه تخفیف واقعی بر اساس تنظیمات اجاره اقامتگاه (فقط در صورت فعال‌سازی صریح میزبان)
                $hasDiscount = !empty($rentalConfig?->has_discount) && (float)($rentalConfig?->discount_value ?? $rentalConfig?->discount_percent ?? 0) > 0;

                $price = $baseDailyPrice;
                $oldPrice = null;
                $discount = null;

                if ($hasDiscount && $rentalConfig) {
                    $discountAmount = $rentalConfig->calculateDiscountAmount($baseDailyPrice);
                    if ($discountAmount > 0) {
                        $price = max(0, $baseDailyPrice - $discountAmount);
                        $oldPrice = $baseDailyPrice;
                        if ($rentalConfig->discount_type === 'percent' && $rentalConfig->discount_value > 0) {
                            $discount = (int) $rentalConfig->discount_value;
                        } else {
                            $discount = round(($discountAmount / $baseDailyPrice) * 100);
                        }
                    } else {
                        $hasDiscount = false;
                    }
                }

                // بررسی مستقیم تیک «آگهی ویژه» برای سکشن «اقامتگاه‌های خاص»
                $isSpecial = !empty($prop->meta['is_special']);

                $hostName = $prop->host?->display_name ?: ($prop->host?->user?->name ?: null);
                $isVerified = (bool) ($prop->host?->kyc_status === 'approved' || $prop->host?->is_verified);

                // تخصیص منطقی و هماهنگ با ماژول املاک به سکشن‌ها
                $assignedSections = [];

                // ۱. تخفیف لحظه آخری: فقط اگر تخفیف واقعی در تنظیمات اجاره فعال باشد
                if ($hasDiscount && $price < $baseDailyPrice) {
                    $assignedSections[] = 'lastminute';
                }

                // ۲. اقامتگاه‌های خاص: بررسی مستقیم بر اساس تیک «آگهی ویژه» (is_special)
                if ($isSpecial) {
                    $assignedSections[] = 'unique';
                }

                // ۳. اقامتگاه‌های ممتاز: اقامتگاه‌های دارای رزرو آنی، میزبان تایید شده یا ویژه
                if ($isVerified || !empty($rentalConfig?->instant_booking) || $isSpecial) {
                    $assignedSections[] = 'premium';
                }

                // ۴. جدیدترین‌ها: شامل کلیه اقامتگاه‌های منتشر شده
                $assignedSections[] = 'newest';

                if (empty($assignedSections)) {
                    $assignedSections[] = 'premium';
                }

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

                // استخراج دسته‌بندی اقامتگاه بر اساس نوع واقعی در ماژول املاک
                $categorySlug = $prop->property_type ?? 'villa';

                $dbListings[] = [
                    'id' => $prop->id,
                    'code' => $prop->code,
                    'section' => $assignedSections[0],
                    'sections' => $assignedSections,
                    'isSpecial' => $isSpecial,
                    'title' => $prop->title,
                    'province' => !empty($prop->province) ? $prop->province : 'مازندران',
                    'city' => !empty($prop->city) ? $prop->city : 'لفور',
                    'category' => $categorySlug,
                    'rooms' => $prop->bedrooms ?? $prop->rooms ?? null,
                    'maxGuests' => $rentalConfig?->max_guests ?? $prop->max_guests ?? null,
                    'rating' => !empty($prop->rating) ? number_format($prop->rating, 1) : null,
                    'price' => $price > 0 ? (int)$price : null,
                    'oldPrice' => ($oldPrice && $oldPrice > 0) ? (int)$oldPrice : null,
                    'discountPercent' => $discount ? (int)$discount : null,
                    'instant' => (bool) ($rentalConfig?->instant_booking ?? false),
                    'image' => $coverUrl,
                    'url' => $detailUrl,
                    'checkoutUrl' => $checkoutUrl,
                    'hostName' => $hostName,
                    'isVerifiedHost' => $isVerified,
                ];
            }
        }
    @endphp

    const LISTINGS_DATA = @json($dbListings ?? []);
    const AVAILABLE_DEST_CITIES = @json($allDestCities ?? []);
    const CALENDAR_MONTHS = @json($calendarMonths ?? []);
    let activeCalendarMonthIndex = 0;

    const currentJalaliYear = {{ $currentJalaliYear }};
    const currentJalaliMonth = {{ $currentJalaliMonth }};
    const currentJalaliMonthName = '{{ $currentJalaliMonthName }}';
    const currentJalaliDay = {{ $currentJalaliDay }};
    const totalMonthDays = {{ $currentMonthDays }};
    const firstDayOfWeek = {{ $firstDayOfWeek }};

    const state = {
        mobileTab: 'explore',
        province: 'all',
        city: 'all',
        cityLabel: 'همه مقاصد',
        category: 'all',
        keyword: '',
        guests: 2,
        startMonthIndex: 0,
        startDay: {{ $defaultStartDay }},
        endMonthIndex: 0,
        endDay: {{ $defaultEndDay }},
        instantOnly: false,
        favorites: [],
        activeModalItem: null,
        modalNights: {{ $defaultNights }}
    };

    function getAbsoluteDay(monthIdx, day) {
        let abs = 0;
        for (let i = 0; i < monthIdx; i++) {
            abs += (CALENDAR_MONTHS[i] ? CALENDAR_MONTHS[i].daysCount : 30);
        }
        return abs + day;
    }

    function getDateFromAbsoluteDay(absDay) {
        let accumulated = 0;
        for (let i = 0; i < CALENDAR_MONTHS.length; i++) {
            const days = CALENDAR_MONTHS[i] ? CALENDAR_MONTHS[i].daysCount : 30;
            if (absDay <= accumulated + days) {
                return {
                    monthIndex: i,
                    day: Math.max(1, absDay - accumulated)
                };
            }
            accumulated += days;
        }
        const lastIdx = Math.max(0, CALENDAR_MONTHS.length - 1);
        return {
            monthIndex: lastIdx,
            day: CALENDAR_MONTHS[lastIdx] ? CALENDAR_MONTHS[lastIdx].daysCount : 30
        };
    }

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

    // =============================================================
    // HERO BANNER SLIDER CONTROLLER (Peek Effect, Swipe & Autoplay)
    // =============================================================
    let currentHeroSlideIndex = 0;
    let heroAutoplayTimer = null;
    let isProgrammaticScrolling = false;
    let programmaticScrollTimer = null;
    const HERO_SLIDE_DOT_COLORS = [
        'bg-[#EA580C] dark:bg-amber-400',       // Slide 0 (Amber Host)
        'bg-[#007A5E] dark:bg-emerald-400',    // Slide 1 (Emerald Cottages)
        'bg-[#4F46E5] dark:bg-indigo-400'      // Slide 2 (Purple Instant)
    ];

    function initHeroSlider() {
        const track = document.getElementById('hero-slider-track');
        if (!track) return;

        const slides = track.querySelectorAll('.hero-slide');
        if (!slides.length) return;

        // Dynamic IntersectionObserver to update active dot when user manually swipes
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                if (isProgrammaticScrolling) return;
                entries.forEach(entry => {
                    if (entry.isIntersecting && entry.intersectionRatio >= 0.5) {
                        const idx = parseInt(entry.target.getAttribute('data-slide-index'), 10);
                        if (!isNaN(idx)) {
                            currentHeroSlideIndex = idx;
                            updateHeroSliderDots(idx);
                        }
                    }
                });
            }, {
                root: track,
                threshold: 0.5
            });

            slides.forEach(slide => observer.observe(slide));
        }

        // =========================================================
        // Touch (Mobile) and Mouse (Desktop) Bounded Slide-by-Slide
        // =========================================================
        let isDragging = false;
        let startX = 0;
        let startY = 0;
        let currentX = 0;
        let isHorizontalSwipe = false;
        let originIndex = 0;

        // Mouse Drag (Desktop)
        track.addEventListener('mousedown', (e) => {
            isDragging = true;
            isProgrammaticScrolling = false;
            clearTimeout(programmaticScrollTimer);
            track.style.scrollSnapType = 'x mandatory';
            isHorizontalSwipe = false;
            startX = e.pageX;
            startY = e.pageY;
            currentX = e.pageX;
            originIndex = currentHeroSlideIndex;
            pauseHeroAutoplay();
            track.classList.add('cursor-grabbing');
            track.classList.remove('cursor-grab');
        });

        window.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            currentX = e.pageX;
            if (Math.abs(currentX - startX) > 10) {
                isHorizontalSwipe = true;
            }
        });

        window.addEventListener('mouseup', (e) => {
            if (!isDragging) return;
            isDragging = false;
            track.classList.remove('cursor-grabbing');
            track.classList.add('cursor-grab');

            if (isHorizontalSwipe) {
                const diffX = currentX - startX;
                if (diffX > 35) {
                    // Dragged Right -> Next slide (+1, bounded)
                    goToHeroSlide(Math.min(slides.length - 1, originIndex + 1), false);
                } else if (diffX < -35) {
                    // Dragged Left -> Prev slide (-1, bounded)
                    goToHeroSlide(Math.max(0, originIndex - 1), false);
                } else {
                    // Snap back to origin
                    goToHeroSlide(originIndex, false);
                }
            }
            startHeroAutoplay();
        });

        // Prevent accidental link / button click if user was dragging
        track.addEventListener('click', (e) => {
            if (isHorizontalSwipe) {
                e.preventDefault();
                e.stopPropagation();
                isHorizontalSwipe = false;
            }
        }, true);

        // Touch Swipe (Mobile)
        track.addEventListener('touchstart', (e) => {
            if (e.touches.length > 1) return;
            isDragging = true;
            isProgrammaticScrolling = false;
            clearTimeout(programmaticScrollTimer);
            track.style.scrollSnapType = 'x mandatory';
            isHorizontalSwipe = false;
            startX = e.touches[0].pageX;
            startY = e.touches[0].pageY;
            currentX = e.touches[0].pageX;
            originIndex = currentHeroSlideIndex;
            pauseHeroAutoplay();
        }, { passive: true });

        track.addEventListener('touchmove', (e) => {
            if (!isDragging || e.touches.length > 1) return;
            currentX = e.touches[0].pageX;
            const diffX = currentX - startX;
            const diffY = e.touches[0].pageY - startY;

            if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 12) {
                isHorizontalSwipe = true;
            }
        }, { passive: true });

        track.addEventListener('touchend', () => {
            if (!isDragging) return;
            isDragging = false;

            if (isHorizontalSwipe) {
                const diffX = currentX - startX;
                if (diffX > 35) {
                    // Swiped Right -> Next slide (+1, bounded)
                    goToHeroSlide(Math.min(slides.length - 1, originIndex + 1), false);
                } else if (diffX < -35) {
                    // Swiped Left -> Prev slide (-1, bounded)
                    goToHeroSlide(Math.max(0, originIndex - 1), false);
                } else {
                    goToHeroSlide(originIndex, false);
                }
            }
            startHeroAutoplay();
        }, { passive: true });

        track.addEventListener('touchcancel', () => {
            isDragging = false;
            startHeroAutoplay();
        }, { passive: true });

        // Pause autoplay on mouse hover
        track.addEventListener('mouseenter', pauseHeroAutoplay);
        track.addEventListener('mouseleave', () => {
            if (!isDragging) startHeroAutoplay();
        });

        // Pause autoplay when hero slider is scrolled out of viewport (saves CPU & avoids background churn)
        if ('IntersectionObserver' in window) {
            const heroVisibilityObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        startHeroAutoplay();
                    } else {
                        pauseHeroAutoplay();
                    }
                });
            }, { threshold: 0.1 });
            heroVisibilityObserver.observe(track);
        } else {
            startHeroAutoplay();
        }
    }

    function goToHeroSlide(index, isLoop = false) {
        const track = document.getElementById('hero-slider-track');
        if (!track) return;
        const slides = track.querySelectorAll('.hero-slide');
        if (!slides.length) return;

        if (isLoop) {
            if (index < 0) index = slides.length - 1;
            if (index >= slides.length) index = 0;
        } else {
            // Strictly clamp to prevent jumping to end / start on manual gestures
            if (index < 0) index = 0;
            if (index >= slides.length) index = slides.length - 1;
        }

        currentHeroSlideIndex = index;
        updateHeroSliderDots(index);

        const targetSlide = slides[index];
        if (targetSlide) {
            isProgrammaticScrolling = true;
            clearTimeout(programmaticScrollTimer);

            // Temporarily disable scroll-snap during programmatic slide-to-slide travel so it glides smoothly past any intermediate points
            track.style.scrollSnapType = 'none';

            if (index === 0) {
                // In RTL layout, scrolling to left: 0 always returns cleanly to the very first slide at the start
                track.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                const trackRect = track.getBoundingClientRect();
                const slideRect = targetSlide.getBoundingClientRect();
                const deltaX = (slideRect.left + slideRect.width / 2) - (trackRect.left + trackRect.width / 2);
                track.scrollBy({ left: deltaX, behavior: 'smooth' });
            }

            // Restore scroll-snap once smooth scroll settles, and re-enable swipe observer
            programmaticScrollTimer = setTimeout(() => {
                track.style.scrollSnapType = 'x mandatory';
                isProgrammaticScrolling = false;
            }, 600);
        }
    }

    function nextHeroSlide() {
        const track = document.getElementById('hero-slider-track');
        if (!track) return;
        const slides = track.querySelectorAll('.hero-slide');
        if (!slides.length) return;
        const nextIdx = (currentHeroSlideIndex + 1) % slides.length;
        goToHeroSlide(nextIdx, true);
    }

    function prevHeroSlide() {
        const track = document.getElementById('hero-slider-track');
        if (!track) return;
        const slides = track.querySelectorAll('.hero-slide');
        if (!slides.length) return;
        const prevIdx = (currentHeroSlideIndex - 1 + slides.length) % slides.length;
        goToHeroSlide(prevIdx, true);
    }

    function updateHeroSliderDots(activeIndex) {
        const dots = document.querySelectorAll('.hero-dot');
        const activeColor = HERO_SLIDE_DOT_COLORS[activeIndex] || 'bg-[#007A5E] dark:bg-emerald-400';
        dots.forEach((dot, idx) => {
            if (idx === activeIndex) {
                dot.className = `hero-dot w-6 h-2 rounded-full ${activeColor} transition-all duration-300 font-sans cursor-pointer`;
            } else {
                dot.className = 'hero-dot w-2 h-2 rounded-full bg-zinc-300 dark:bg-zinc-700 hover:bg-zinc-400 dark:hover:bg-zinc-600 transition-all duration-300 font-sans cursor-pointer';
            }
        });
    }

    function startHeroAutoplay() {
        pauseHeroAutoplay();
        heroAutoplayTimer = setInterval(() => {
            nextHeroSlide();
        }, 5500);
    }

    function pauseHeroAutoplay() {
        if (heroAutoplayTimer) {
            clearInterval(heroAutoplayTimer);
            heroAutoplayTimer = null;
        }
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
            ? `<span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-lg bg-[#007A5E] text-white text-[10px] sm:text-[11px] font-bold shadow-sm font-sans">${toPersianNum(item.discountPercent)}٪ تخفیف</span>`
            : (item.isSpecial ? `<span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-lg bg-amber-500 text-white text-[10px] sm:text-[11px] font-bold shadow-sm font-sans">ویژه</span>` : '');

        const oldPriceMarkup = item.oldPrice
            ? `<span class="text-[10px] sm:text-xs text-zinc-400 line-through font-normal ml-1.5 font-sans">${formatToman(item.oldPrice)}</span>`
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
                    ${item.rating ? `
                    <span class="inline-flex items-center gap-1 text-[11px] sm:text-xs font-bold text-[#18181B] dark:text-zinc-200 shrink-0 mt-0.5">
                        <span class="text-amber-400 dark:text-amber-400 text-xs">★</span>
                        <span>${item.rating}</span>
                    </span>` : ''}
                </div>

                <div class="flex items-center justify-between gap-1 mt-1 px-0.5">
                    <p class="text-[11px] sm:text-xs text-zinc-400 truncate font-sans">${item.city}${item.maxGuests ? ` • تا ${toPersianNum(item.maxGuests)} نفر` : ''}</p>
                    <div class="flex items-center gap-1 shrink-0">
                        ${item.instant ? `<span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 text-[10px] font-bold border border-amber-200/60 dark:border-amber-800/60 font-sans leading-none">⚡ آنی</span>` : ''}
                        ${item.code ? `<span class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded-md font-sans leading-none">کد ${toPersianNum(item.code)}</span>` : ''}
                    </div>
                </div>
            </div>

            ${(item.price && item.price > 0) ? `
            <div class="mt-2.5 pt-2 border-t border-[#ECECEE]/70 dark:border-zinc-800/70 text-xs sm:text-sm font-extrabold text-[#18181B] dark:text-zinc-100 truncate px-0.5 font-sans">
                ${oldPriceMarkup}${formatToman(item.price)} <span class="text-[10px] sm:text-xs font-normal text-zinc-400 font-sans">${currencyLabel} / شب</span>
            </div>` : `
            <div class="mt-2.5 pt-2 border-t border-[#ECECEE]/70 dark:border-zinc-800/70 text-xs sm:text-sm font-bold text-zinc-500 truncate px-0.5 font-sans">
                استعلام قیمت
            </div>`}
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
                    
                    ${item.discountPercent ? `
                    <span class="absolute top-2 right-2 px-2 py-0.5 rounded-lg bg-[#007A5E] text-white text-[10px] sm:text-[11px] font-extrabold shadow-sm">
                        ${toPersianNum(item.discountPercent)}٪ تخفیف
                    </span>` : ''}

                    <button type="button" onclick="toggleFavorite(${item.id}, event)" aria-label="ذخیره" class="absolute top-2 left-2 w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/95 dark:bg-zinc-900/95 flex items-center justify-center active:scale-90 transition-transform shadow-sm">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 ${isFav ? 'text-rose-500 fill-rose-500' : 'text-[#18181B] dark:text-zinc-200'}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                        </svg>
                    </button>
                </div>

                <div class="flex items-start justify-between gap-1.5 px-0.5">
                    <h3 class="card-title-fixed-height text-xs sm:text-sm font-bold text-[#18181B] dark:text-zinc-100 flex-1">${item.title}</h3>
                    ${item.rating ? `
                    <span class="inline-flex items-center gap-1 text-[11px] sm:text-xs font-bold text-[#18181B] dark:text-zinc-200 shrink-0 mt-0.5">
                        <span class="text-amber-400 dark:text-amber-400 text-xs">★</span>
                        <span>${item.rating}</span>
                    </span>` : ''}
                </div>

                <div class="flex items-center justify-between gap-1 mt-1 px-0.5">
                    <p class="text-[11px] sm:text-xs text-zinc-400 truncate font-sans">${item.city}${item.maxGuests ? ` • تا ${toPersianNum(item.maxGuests)} نفر` : ''}</p>
                    <div class="flex items-center gap-1 shrink-0">
                        ${item.instant ? `<span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 text-[10px] font-bold border border-amber-200/60 dark:border-amber-800/60 font-sans leading-none">⚡ آنی</span>` : ''}
                        ${item.code ? `<span class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded-md font-sans leading-none">کد ${toPersianNum(item.code)}</span>` : ''}
                    </div>
                </div>
            </div>

            <div class="mt-2 pt-2 border-t border-[#ECECEE] dark:border-zinc-800 flex items-end justify-between gap-1">
                <div>
                    ${item.oldPrice ? `<span class="block text-[10px] sm:text-[11px] text-zinc-400 line-through">${formatToman(item.oldPrice)}</span>` : ''}
                    <div class="text-xs sm:text-sm font-extrabold text-[#007A5E] dark:text-emerald-400">
                        ${(item.price && item.price > 0) ? `${formatToman(item.price)} <span class="text-[10px] font-normal text-zinc-500">${currencyLabel}</span>` : 'استعلام قیمت'}
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
            if (state.keyword) {
                const normKeyword = normalizePersianDigits(state.keyword).toLowerCase().trim();
                const normItemCode = normalizePersianDigits(item.code || '').toLowerCase().trim();
                const normItemId = normalizePersianDigits(item.id || '').toLowerCase().trim();
                const titleLower = (item.title || '').toLowerCase();
                const cityLower = (item.city || '').toLowerCase();
                const provLower = (item.province || '').toLowerCase();

                const matchCode = (normItemCode && normItemCode.includes(normKeyword)) || (normItemId && normItemId === normKeyword);
                const matchTitle = titleLower.includes(normKeyword) || titleLower.includes(state.keyword.toLowerCase().trim());
                const matchLocation = cityLower.includes(state.keyword.toLowerCase().trim()) || provLower.includes(state.keyword.toLowerCase().trim());

                if (!matchCode && !matchTitle && !matchLocation) return false;
            } else {
                if (state.city !== 'all' && item.city !== state.city) return false;
                if (state.province !== 'all' && item.province !== state.province) return false;
            }
            if (state.category !== 'all' && item.category !== state.category) return false;
            if (item.maxGuests && state.guests > item.maxGuests) return false;
            if (state.instantOnly && !item.instant) return false;
            return true;
        });

        // Sync active filters notification bar
        const activeFiltersBar = document.getElementById('active-filters-bar');
        if (activeFiltersBar) {
            let pillsHtml = '';
            if (state.keyword) {
                pillsHtml += `
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-[#007A5E] dark:text-emerald-400 text-xs font-bold border border-emerald-200/60 dark:border-emerald-800/60 shadow-2xs font-sans">
                        <span>جستجو: ${toPersianNum(state.keyword)}</span>
                        <button type="button" onclick="clearKeywordFilter()" class="hover:text-rose-500 font-black cursor-pointer pr-1">✕</button>
                    </span>
                `;
            }
            if (state.instantOnly) {
                pillsHtml += `
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 text-xs font-bold border border-amber-200/60 dark:border-amber-800/60 shadow-2xs font-sans">
                        <span>⚡ فقط رزرو آنی</span>
                        <button type="button" onclick="toggleInstantFilter(event, false)" class="hover:text-rose-500 font-black cursor-pointer pr-1">✕</button>
                    </span>
                `;
            }
            if (state.city !== 'all') {
                pillsHtml += `
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 text-xs font-bold border border-zinc-200 dark:border-zinc-700 shadow-2xs font-sans">
                        <span>مقصد: ${state.cityLabel || state.city}</span>
                        <button type="button" onclick="selectDestination('all', 'همه مقاصد'); triggerLivewireSearch();" class="hover:text-rose-500 font-black cursor-pointer pr-1">✕</button>
                    </span>
                `;
            }
            if (state.category !== 'all') {
                pillsHtml += `
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 text-xs font-bold border border-zinc-200 dark:border-zinc-700 shadow-2xs font-sans">
                        <span>دسته‌بندی خاص</span>
                        <button type="button" onclick="filterByCategory('all')" class="hover:text-rose-500 font-black cursor-pointer pr-1">✕</button>
                    </span>
                `;
            }

            if (pillsHtml) {
                activeFiltersBar.innerHTML = `
                    <div class="flex items-center gap-2 flex-wrap p-2.5 rounded-2xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 shadow-xs font-sans">
                        <span class="text-xs text-zinc-400 font-medium font-sans">فیلترهای فعال:</span>
                        <div class="flex items-center gap-2 flex-wrap">${pillsHtml}</div>
                        <button type="button" onclick="resetAllFilters()" class="text-xs px-2.5 py-1 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 border border-rose-200/60 dark:border-rose-900 font-bold mr-auto transition-all active:scale-95 font-sans no-underline">پاکسازی همه</button>
                    </div>
                `;
                activeFiltersBar.classList.remove('hidden');
            } else {
                activeFiltersBar.innerHTML = '';
                activeFiltersBar.classList.add('hidden');
            }
        }

        // Sync zone pills
        document.querySelectorAll('.zone-quick-pill').forEach(btn => {
            const pillVal = btn.getAttribute('data-zone-pill');
            const isActive = (pillVal === 'all' && state.city === 'all' && state.province === 'all') || (pillVal === state.city);
            btn.className = isActive
                ? 'zone-quick-pill px-4 py-2 rounded-full text-xs font-bold bg-[#007A5E] text-white border border-[#007A5E] shadow-sm transition-all active:scale-95'
                : 'zone-quick-pill px-4 py-2 rounded-full text-xs font-bold bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-800 hover:border-[#007A5E] dark:hover:border-emerald-500 hover:text-[#007A5E] dark:hover:text-emerald-400 shadow-xs transition-all active:scale-95';
        });

        syncInstantUI();

        if (filtered.length === 0) {
            sectionsWrapper.innerHTML = '';
            emptyState.classList.remove('hidden');
            const emptyTitle = document.getElementById('empty-state-title');
            const emptySubtitle = document.getElementById('empty-state-subtitle');
            const emptyBtn = document.getElementById('empty-state-btn');
            if (emptyTitle) {
                if (LISTINGS_DATA.length === 0) {
                    emptyTitle.textContent = 'در حال حاضر هیچ اقامتگاهی در سامانه ثبت نشده است.';
                    if (emptySubtitle) emptySubtitle.textContent = 'به‌زودی اقامتگاه‌های جدید بوم‌گردی و ویلاها به سیستم افزوده خواهند شد.';
                    if (emptyBtn) emptyBtn.style.display = 'none';
                } else {
                    emptyTitle.textContent = state.keyword 
                        ? `اقامتگاهی با عنوان یا کد «${toPersianNum(state.keyword)}» یافت نشد`
                        : 'اقامتگاهی با این مشخصات یافت نشد';
                    if (emptySubtitle) emptySubtitle.textContent = 'می‌توانید فیلترهای جستجو را پاکسازی کرده یا عبارت جستجو را تغییر دهید.';
                    if (emptyBtn) emptyBtn.style.display = 'inline-block';
                }
            }
        } else {
            emptyState.classList.add('hidden');

            // When keyword search is active, render a dedicated search results grid so all matches are immediately visible!
            if (state.keyword) {
                const searchResultsHtml = `
                    <section class="pt-2 font-sans">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-base sm:text-lg font-extrabold text-[#18181B] dark:text-zinc-100 font-sans">
                                    نتایج جستجو برای «${toPersianNum(state.keyword)}»
                                </h2>
                                <p class="text-xs text-zinc-400 mt-0.5 font-sans">${toPersianNum(filtered.length)} اقامتگاه یافت شد</p>
                            </div>
                            <button type="button" onclick="clearKeywordFilter()"
                                    class="px-3.5 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-bold text-zinc-600 dark:text-zinc-300 hover:text-rose-500 transition-colors shadow-2xs font-sans">
                                نمایش همه اقامتگاه‌ها ✕
                            </button>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
                            ${filtered.map(buildVillaCardHTML).join('')}
                        </div>
                    </section>
                `;
                sectionsWrapper.innerHTML = searchResultsHtml;
                initCarouselDrag();
                return;
            }

            const renderedSections = SECTIONS_META.map(sec => {
                let secItems = filtered.filter(item => {
                    if (Array.isArray(item.sections)) {
                        return item.sections.includes(sec.key);
                    }
                    return item.section === sec.key;
                });

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
                                <div class="flex items-center gap-1.5 bg-white dark:bg-zinc-900 border border-[#D1FAE5] dark:border-emerald-900/50 px-2.5 py-1.5 rounded-xl text-[#007A5E] dark:text-emerald-400 shadow-sm font-sans">
                                    <svg class="w-3.5 h-3.5 text-[#007A5E] dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div class="flex items-center gap-1 text-xs font-extrabold tracking-wider" dir="ltr">
                                        <span id="lm-timer-h" class="bg-[#ECFDF5] dark:bg-emerald-950/50 px-1.5 py-0.5 rounded-md min-w-[24px] text-center font-sans">۰۴</span>
                                        <span>:</span>
                                        <span id="lm-timer-m" class="bg-[#ECFDF5] dark:bg-emerald-950/50 px-1.5 py-0.5 rounded-md min-w-[24px] text-center font-sans">۳۸</span>
                                        <span>:</span>
                                        <span id="lm-timer-s" class="bg-[#ECFDF5] dark:bg-emerald-950/50 px-1.5 py-0.5 rounded-md min-w-[24px] text-center font-sans">۱۹</span>
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

                // اقامتگاه‌های خاص: بررسی مستقیم بر اساس تیک «آگهی ویژه» (is_special)
                if (sec.key === 'unique') {
                    secItems = secItems.filter(item => item.isSpecial);
                    if (secItems.length === 0) return '';
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
        const rawQ = (query || '').trim();
        const q = rawQ.toLowerCase();
        const normQ = normalizePersianDigits(rawQ).toLowerCase();

        const clearBtn = document.getElementById(context === 'desktop' ? 'dest-search-clear' : 'mobile-dest-search-clear');
        const tabsBar = document.getElementById(context === 'desktop' ? 'desktop-dest-tabs' : 'mobile-dest-tabs');
        const resultsContainer = document.getElementById(context === 'desktop' ? 'desktop-search-results' : 'mobile-search-results');
        const itemsEl = document.getElementById(context === 'desktop' ? 'desktop-search-items' : 'mobile-search-items');
        const emptyEl = document.getElementById(context === 'desktop' ? 'desktop-search-empty' : 'mobile-search-empty');
        const panels = document.querySelectorAll(`.${context}-province-panel`);

        if (clearBtn) clearBtn.classList.toggle('hidden', !rawQ);

        if (!rawQ) {
            if (resultsContainer) resultsContainer.classList.add('hidden');
            if (tabsBar) tabsBar.classList.remove('opacity-40', 'pointer-events-none');
            const mobPop = document.getElementById('mobile-popular-dest-container');
            if (mobPop) mobPop.classList.remove('hidden');

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
        const mobPop = document.getElementById('mobile-popular-dest-container');
        if (mobPop) mobPop.classList.add('hidden');

        // 1. Destination Matches (City / Province)
        const cityMatches = (typeof AVAILABLE_DEST_CITIES !== 'undefined' ? AVAILABLE_DEST_CITIES : []).filter(item => {
            return item.city.toLowerCase().includes(q) || item.province.toLowerCase().includes(q);
        });

        // 2. Property Matches (by title or code)
        const propertyMatches = (typeof LISTINGS_DATA !== 'undefined' ? LISTINGS_DATA : []).filter(item => {
            const normCode = normalizePersianDigits(item.code || '').toLowerCase();
            const normId = normalizePersianDigits(item.id || '').toLowerCase();
            const titleLower = (item.title || '').toLowerCase();

            const matchCode = (normCode && normCode.includes(normQ)) || (normId && normId === normQ);
            const matchTitle = titleLower.includes(q) || titleLower.includes(normQ);

            return matchCode || matchTitle;
        }).slice(0, 8);

        if (cityMatches.length === 0 && propertyMatches.length === 0) {
            if (itemsEl) itemsEl.innerHTML = '';
            if (emptyEl) {
                emptyEl.classList.remove('hidden');
                emptyEl.innerHTML = `موردی با عنوان، کد یا مقصد «${toPersianNum(rawQ)}» یافت نشد.`;
            }
        } else {
            if (emptyEl) emptyEl.classList.add('hidden');
            let html = '';

            // Search Trigger Button for direct keyword
            const escapedQ = rawQ.replace(/'/g, "\\'");
            html += `
                <div class="col-span-full mb-2">
                    <button type="button" onclick="applyKeywordSearch('${escapedQ}')"
                            class="w-full flex items-center justify-between p-3 rounded-2xl bg-[#007A5E] hover:bg-[#005F48] text-white text-right transition-all shadow-sm active:scale-[0.99] font-sans no-underline group">
                        <div class="flex items-center gap-2.5 truncate">
                            <div class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold truncate">جستجوی عبارت «${toPersianNum(rawQ)}» در همه اقامتگاه‌ها</span>
                        </div>
                        <span class="text-xs font-black bg-white/20 group-hover:bg-white/30 text-white px-3 py-1.5 rounded-xl shrink-0 transition-colors shadow-2xs font-sans no-underline">
                            مشاهده نتایج ↵
                        </span>
                    </button>
                </div>
            `;

            // Property Matches
            if (propertyMatches.length > 0) {
                html += `
                    <div class="col-span-full text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mt-2 mb-1 px-1 font-sans">
                        اقامتگاه‌های یافت‌شده بر اساس نام یا کد (${toPersianNum(propertyMatches.length)} مورد):
                    </div>
                `;
                html += propertyMatches.map(p => `
                    <div onclick="navigateToProperty('${p.url}')"
                         class="col-span-full flex items-center justify-between p-2 rounded-xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-700/80 hover:border-[#007A5E] dark:hover:border-emerald-500 hover:bg-[#ECFDF5] dark:hover:bg-emerald-950/40 text-right cursor-pointer transition-all shadow-xs group font-sans">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <img src="${p.image}" alt="${p.title}" onerror="this.onerror=null; this.src='{{ asset('images/property-placeholder.svg') }}';" class="w-11 h-11 rounded-lg object-cover shrink-0 border border-zinc-200 dark:border-zinc-700">
                            <div class="min-w-0 truncate">
                                <div class="text-xs font-bold text-zinc-800 dark:text-zinc-100 group-hover:text-[#007A5E] dark:group-hover:text-emerald-400 truncate font-sans">${p.title}</div>
                                <div class="flex items-center gap-1.5 text-[10px] text-zinc-400 mt-0.5 font-sans">
                                    <span>${p.city}</span>
                                    ${p.code ? `<span class="bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 px-1 rounded font-sans">کد: ${toPersianNum(p.code)}</span>` : ''}
                                    ${p.instant ? `<span class="text-amber-500 font-bold font-sans">⚡ آنی</span>` : ''}
                                </div>
                            </div>
                        </div>
                        <div class="text-left shrink-0 mr-2 font-sans">
                            ${p.price ? `<span class="text-[11px] font-extrabold text-[#007A5E] dark:text-emerald-400 block">${formatToman(p.price)}</span><span class="text-[9px] text-zinc-400 block font-sans">${currencyLabel}</span>` : `<span class="text-[10px] text-zinc-400 font-sans">استعلام</span>`}
                        </div>
                    </div>
                `).join('');
            }

            // Destination / City Matches
            if (cityMatches.length > 0) {
                html += `
                    <div class="col-span-full text-[11px] font-bold text-zinc-500 dark:text-zinc-400 mt-2.5 mb-1 px-1 font-sans">
                        شهرها و مقاصد (${toPersianNum(cityMatches.length)} مورد):
                    </div>
                `;
                html += cityMatches.map(m => `
                    <button type="button" onclick="selectDestination('${m.city}', '${m.city}', event)" data-dest-key="${m.city}"
                            class="flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-700/80 hover:border-[#007A5E] dark:hover:border-emerald-500 hover:bg-[#ECFDF5] dark:hover:bg-emerald-950/40 text-right transition-all shadow-xs group active:scale-98 font-sans">
                        <div class="truncate">
                            <div class="text-xs font-bold text-zinc-700 dark:text-zinc-200 group-hover:text-[#007A5E] dark:group-hover:text-emerald-400 truncate font-sans">${m.city}</div>
                            <div class="text-[10px] text-zinc-400 truncate font-sans">استان ${m.province}</div>
                        </div>
                        <span class="w-1.5 h-1.5 rounded-full bg-zinc-300 dark:bg-zinc-600 group-hover:bg-[#007A5E] dark:group-hover:bg-emerald-400 shrink-0 transition-colors mr-1"></span>
                    </button>
                `).join('');
            }

            if (itemsEl) itemsEl.innerHTML = html;
        }
    }

    function applyKeywordSearch(keyword) {
        state.keyword = keyword || '';
        state.city = 'all';
        state.province = 'all';
        state.cityLabel = 'همه مقاصد';
        closeAllPopups();
        closeMobileSheets();

        const dLabel = document.getElementById('desktop-dest-label');
        const mPill = document.getElementById('mobile-pill-dest');
        const mSummaryDest = document.getElementById('m-summary-dest-val');
        if (state.keyword) {
            if (dLabel) dLabel.textContent = `جستجو: ${toPersianNum(state.keyword)}`;
            if (mPill) mPill.textContent = `جستجو: ${toPersianNum(state.keyword)}`;
            if (mSummaryDest) mSummaryDest.textContent = `جستجو: ${toPersianNum(state.keyword)}`;
        } else {
            if (dLabel) dLabel.textContent = 'همه مقاصد';
            if (mPill) mPill.textContent = 'جستجو بر اساس مقصد، عنوان یا کد اقامتگاه...';
            if (mSummaryDest) mSummaryDest.textContent = 'همه مقاصد';
        }

        // Reset visual active styling on all destination buttons
        document.querySelectorAll('[data-dest-key]').forEach(btn => {
            const key = btn.getAttribute('data-dest-key');
            if (key === 'all') {
                btn.classList.add('border-[#007A5E]', 'dark:border-emerald-500', 'bg-[#ECFDF5]', 'dark:bg-emerald-950/40', 'text-[#007A5E]', 'dark:text-emerald-400');
            } else {
                btn.classList.remove('border-[#007A5E]', 'dark:border-emerald-500', 'bg-[#ECFDF5]', 'dark:bg-emerald-950/40', 'text-[#007A5E]', 'dark:text-emerald-400');
            }
        });

        simulateWireLoading(() => renderListings());
    }

    function handleSearchInputKey(event, context) {
        if (event.key === 'Enter') {
            event.preventDefault();
            const input = document.getElementById(context === 'desktop' ? 'dest-search-input' : 'mobile-dest-search-input');
            const val = input ? input.value.trim() : '';
            applyKeywordSearch(val);
        }
    }

    function clearKeywordFilter() {
        state.keyword = '';
        const dInput = document.getElementById('dest-search-input');
        const mInput = document.getElementById('mobile-dest-search-input');
        if (dInput) dInput.value = '';
        if (mInput) mInput.value = '';
        const dLabel = document.getElementById('desktop-dest-label');
        const mPill = document.getElementById('mobile-pill-dest');
        if (dLabel) dLabel.textContent = state.cityLabel || 'همه مقاصد';
        if (mPill) mPill.textContent = state.city === 'all' ? 'جستجو بر اساس مقصد، عنوان یا کد اقامتگاه...' : `مقصد: ${state.cityLabel}`;
        simulateWireLoading(() => renderListings());
    }

    function toggleInstantFilter(event, explicitVal) {
        if (event && event.stopPropagation) event.stopPropagation();
        if (typeof explicitVal === 'boolean') {
            state.instantOnly = explicitVal;
        } else {
            state.instantOnly = !state.instantOnly;
        }
        syncInstantUI();
        simulateWireLoading(() => renderListings());
    }

    function syncInstantUI() {
        const desktopBtn = document.getElementById('desktop-instant-btn');
        const instantPill = document.getElementById('instant-pill-btn');
        const mobileCheckbox = document.getElementById('mobile-instant-checkbox');

        if (mobileCheckbox) {
            mobileCheckbox.checked = state.instantOnly;
        }

        if (desktopBtn) {
            if (state.instantOnly) {
                desktopBtn.className = 'px-2.5 py-2.5 rounded-xl text-xs font-bold transition-all border border-[#007A5E] bg-[#ECFDF5] dark:bg-emerald-950/40 text-[#007A5E] dark:text-emerald-400 flex items-center gap-1 shadow-sm font-sans';
            } else {
                desktopBtn.className = 'px-2.5 py-2.5 rounded-xl text-xs font-bold transition-all border border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9] dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:border-[#007A5E] hover:text-[#007A5E] dark:hover:text-emerald-400 flex items-center gap-1 shadow-2xs font-sans';
            }
        }

        if (instantPill) {
            if (state.instantOnly) {
                instantPill.className = 'px-4 py-2 rounded-full text-xs font-bold transition-all border border-[#007A5E] bg-[#007A5E] text-white shadow-sm flex items-center gap-1.5 active:scale-95 font-sans';
            } else {
                instantPill.className = 'px-4 py-2 rounded-full text-xs font-bold transition-all border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 hover:border-[#007A5E] hover:text-[#007A5E] dark:hover:text-emerald-400 shadow-xs flex items-center gap-1.5 active:scale-95 font-sans';
            }
        }

        const mSummaryGuests = document.getElementById('m-summary-guests-val');
        if (mSummaryGuests) {
            const instantPart = state.instantOnly ? ' • ⚡ آنی' : '';
            mSummaryGuests.textContent = `${toPersianNum(state.guests)} نفر${instantPart}`;
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
        const mSummaryDest = document.getElementById('m-summary-dest-val');
        if (dLabel) dLabel.textContent = label;
        if (mPill) mPill.textContent = destKey === 'all' ? 'جستجو در استان و شهر مقصد...' : `مقصد: ${label}`;
        if (dHint) dHint.textContent = `مقصد: ${label}`;
        if (mSummaryDest) mSummaryDest.textContent = label;

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
        const mSummaryGuests = document.getElementById('m-summary-guests-val');
        if (dGuests) dGuests.textContent = `${pCount} نفر`;
        if (mGuests) mGuests.textContent = pCount;
        if (mSub) mSub.textContent = `${toPersianNum(state.startDay)} تا ${toPersianNum(state.endDay)} ${currentJalaliMonthName} • ${pCount} نفر`;
        if (mSummaryGuests) {
            const instantPart = state.instantOnly ? ' • ⚡ آنی' : '';
            mSummaryGuests.textContent = `${pCount} نفر${instantPart}`;
        }
        // Search values do not live-filter without clicking the search button
    }

    let dateSelectionStep = 'start'; // 'start' | 'end'

    function setDateSelectionStep(step, event) {
        if (event && event.stopPropagation) event.stopPropagation();
        dateSelectionStep = step;
        updateDateLabelsAndSummary();
    }

    function updateDateLabelsAndSummary() {
        const absStart = getAbsoluteDay(state.startMonthIndex, state.startDay);
        const absEnd = getAbsoluteDay(state.endMonthIndex, state.endDay);
        const nights = Math.max(1, absEnd - absStart);
        const pNights = toPersianNum(nights);

        const startMonth = CALENDAR_MONTHS[state.startMonthIndex] || CALENDAR_MONTHS[0];
        const endMonth = CALENDAR_MONTHS[state.endMonthIndex] || CALENDAR_MONTHS[0];

        const startStr = `${toPersianNum(state.startDay)} ${startMonth.name}`;
        const endStr = `${toPersianNum(state.endDay)} ${endMonth.name}`;
        const summary = (state.startMonthIndex === state.endMonthIndex)
            ? `${toPersianNum(state.startDay)} تا ${toPersianNum(state.endDay)} ${startMonth.name} (${pNights} شب)`
            : `${startStr} تا ${endStr} (${pNights} شب)`;
        const summaryShort = (state.startMonthIndex === state.endMonthIndex)
            ? `${toPersianNum(state.startDay)} تا ${toPersianNum(state.endDay)} ${startMonth.name}`
            : `${startStr} تا ${endStr}`;

        const dLabel = document.getElementById('desktop-date-label');
        const mSummary = document.getElementById('mobile-calendar-summary');
        const mSummaryDate = document.getElementById('m-summary-date-val');
        const mSub = document.getElementById('mobile-pill-sub');
        const dNights = document.getElementById('desktop-nights-count');
        const mNights = document.getElementById('mobile-nights-count');

        if (dLabel) dLabel.textContent = summary;
        if (mSummary) mSummary.textContent = summary;
        if (mSummaryDate) mSummaryDate.textContent = summaryShort;
        if (mSub) mSub.textContent = `${summaryShort} • ${toPersianNum(state.guests)} نفر`;
        if (dNights) dNights.textContent = pNights;
        if (mNights) mNights.textContent = pNights;

        // Desktop checkin & checkout tabs UI
        const inBtn = document.getElementById('desktop-checkin-tab-btn');
        const outBtn = document.getElementById('desktop-checkout-tab-btn');
        const inVal = document.getElementById('desktop-checkin-val');
        const outVal = document.getElementById('desktop-checkout-val');
        const inDot = document.getElementById('desktop-checkin-dot');
        const outDot = document.getElementById('desktop-checkout-dot');
        const inBadge = document.getElementById('desktop-checkin-badge');
        const outBadge = document.getElementById('desktop-checkout-badge');
        const inStatus = document.getElementById('desktop-checkin-status');
        const outStatus = document.getElementById('desktop-checkout-status');

        if (inVal) inVal.textContent = startStr;
        if (outVal) outVal.textContent = endStr;
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
                if (inVal) inVal.className = 'text-sm font-black text-[#18181B] dark:text-zinc-100 mt-2.5 block tracking-tight font-sans';
                if (outVal) outVal.className = 'text-sm font-bold text-zinc-700 dark:text-zinc-300 mt-2.5 block tracking-tight font-sans';
            } else {
                inBtn.className = inactiveCls;
                outBtn.className = activeCls;
                if (inDot) inDot.classList.add('hidden');
                if (outDot) outDot.classList.remove('hidden');
                if (inBadge) inBadge.classList.add('hidden');
                if (outBadge) outBadge.classList.remove('hidden');
                if (inStatus) inStatus.className = 'text-[11px] font-medium text-zinc-500 dark:text-zinc-400';
                if (outStatus) outStatus.className = 'text-[11px] font-bold text-[#007A5E] dark:text-emerald-400';
                if (inVal) inVal.className = 'text-sm font-bold text-zinc-700 dark:text-zinc-300 mt-2.5 block tracking-tight font-sans';
                if (outVal) outVal.className = 'text-sm font-black text-[#18181B] dark:text-zinc-100 mt-2.5 block tracking-tight font-sans';
            }
        }

        // Mobile dual switch tabs
        const mInBtn = document.getElementById('mobile-checkin-tab-btn');
        const mOutBtn = document.getElementById('mobile-checkout-tab-btn');
        const mInVal = document.getElementById('mobile-checkin-val');
        const mOutVal = document.getElementById('mobile-checkout-val');
        const mInStatus = document.getElementById('mobile-checkin-status');
        const mOutStatus = document.getElementById('mobile-checkout-status');
        const mInDot = document.getElementById('mobile-checkin-dot');
        const mOutDot = document.getElementById('mobile-checkout-dot');

        if (mInVal) mInVal.textContent = startStr;
        if (mOutVal) mOutVal.textContent = endStr;

        if (mInBtn && mOutBtn) {
            if (dateSelectionStep === 'start') {
                mInBtn.className = 'p-3 rounded-2xl text-right transition-all bg-white dark:bg-zinc-900 border-2 border-[#007A5E] dark:border-emerald-500 shadow-xs font-sans';
                mOutBtn.className = 'p-3 rounded-2xl text-right transition-all bg-[#F7F8F9] dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/80 hover:border-zinc-300 font-sans';
                if (mInStatus) mInStatus.className = 'text-[10px] text-[#007A5E] dark:text-emerald-400 font-bold font-sans';
                if (mOutStatus) mOutStatus.className = 'text-[10px] text-zinc-500 dark:text-zinc-400 font-medium font-sans';
                if (mInDot) mInDot.classList.remove('hidden');
                if (mOutDot) mOutDot.classList.add('hidden');
            } else {
                mInBtn.className = 'p-3 rounded-2xl text-right transition-all bg-[#F7F8F9] dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/80 hover:border-zinc-300 font-sans';
                mOutBtn.className = 'p-3 rounded-2xl text-right transition-all bg-white dark:bg-zinc-900 border-2 border-[#007A5E] dark:border-emerald-500 shadow-xs font-sans';
                if (mInStatus) mInStatus.className = 'text-[10px] text-zinc-500 dark:text-zinc-400 font-medium font-sans';
                if (mOutStatus) mOutStatus.className = 'text-[10px] text-[#007A5E] dark:text-emerald-400 font-bold font-sans';
                if (mInDot) mInDot.classList.add('hidden');
                if (mOutDot) mOutDot.classList.remove('hidden');
            }
        }

        // Sync quick stay duration chips active state
        document.querySelectorAll('.quick-stay-chip').forEach(chip => {
            const chipNights = parseInt(chip.getAttribute('data-nights'), 10);
            if (chipNights === nights) {
                chip.className = 'quick-stay-chip px-3 py-1.5 rounded-xl border border-[#007A5E] bg-[#ECFDF5] dark:bg-emerald-950/40 text-[#007A5E] dark:text-emerald-400 text-[11px] font-black shrink-0 active:scale-95 transition-all font-sans';
            } else {
                chip.className = 'quick-stay-chip px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700/80 bg-[#F7F8F9] dark:bg-zinc-800/80 text-zinc-700 dark:text-zinc-300 text-[11px] font-bold shrink-0 hover:border-[#007A5E] hover:text-[#007A5E] active:scale-95 transition-all font-sans';
            }
        });
    }

    function changeCalendarMonth(direction) {
        const newIdx = activeCalendarMonthIndex + direction;
        if (newIdx < 0 || newIdx >= CALENDAR_MONTHS.length) return;
        activeCalendarMonthIndex = newIdx;
        updateMonthNavigationUI();
        renderJalaliCalendars();
    }

    function updateMonthNavigationUI() {
        const curMonth = CALENDAR_MONTHS[activeCalendarMonthIndex] || CALENDAR_MONTHS[0];
        const monthTitle = `${curMonth.name} ${toPersianNum(curMonth.year)}`;

        document.querySelectorAll('.cal-month-title').forEach(el => {
            el.textContent = monthTitle;
        });

        document.querySelectorAll('.cal-prev-btn').forEach(btn => {
            btn.disabled = activeCalendarMonthIndex <= 0;
        });

        document.querySelectorAll('.cal-next-btn').forEach(btn => {
            btn.disabled = activeCalendarMonthIndex >= CALENDAR_MONTHS.length - 1;
        });

        document.querySelectorAll('.cal-month-badge').forEach(badge => {
            if (activeCalendarMonthIndex === 0) {
                badge.classList.remove('hidden');
                badge.textContent = 'ماه جاری';
            } else {
                badge.classList.add('hidden');
            }
        });
    }

    function setQuickDaysRange(nights) {
        const todayAbs = getAbsoluteDay(0, currentJalaliDay);
        state.startMonthIndex = 0;
        state.startDay = currentJalaliDay;
        const targetEnd = getDateFromAbsoluteDay(todayAbs + nights);
        state.endMonthIndex = targetEnd.monthIndex;
        state.endDay = targetEnd.day;
        dateSelectionStep = 'end';
        activeCalendarMonthIndex = 0;
        updateMonthNavigationUI();
        updateDateLabelsAndSummary();
        renderJalaliCalendars();
    }

    function resetAllFilters() {
        state.province = 'all';
        state.city = 'all';
        state.cityLabel = 'همه مقاصد';
        state.category = 'all';
        state.keyword = '';
        state.guests = 2;
        state.startMonthIndex = 0;
        state.startDay = currentJalaliDay;
        state.endMonthIndex = 0;
        state.endDay = Math.min(totalMonthDays, currentJalaliDay + 3);
        state.instantOnly = false;
        dateSelectionStep = 'start';
        activeCalendarMonthIndex = 0;
        updateMonthNavigationUI();

        const dInput = document.getElementById('dest-search-input');
        const mInput = document.getElementById('mobile-dest-search-input');
        if (dInput) dInput.value = '';
        if (mInput) mInput.value = '';

        const dLabel = document.getElementById('desktop-dest-label');
        const mPill = document.getElementById('mobile-pill-dest');
        const dHint = document.getElementById('desktop-dest-selected-hint');
        const mSummaryDest = document.getElementById('m-summary-dest-val');
        if (dLabel) dLabel.textContent = 'همه مقاصد';
        if (mPill) mPill.textContent = 'جستجو بر اساس مقصد، عنوان یا کد اقامتگاه...';
        if (dHint) dHint.textContent = 'مقصد: همه مقاصد';
        if (mSummaryDest) mSummaryDest.textContent = 'همه مقاصد';
        setMobileSearchSection('dest');

        // Clear search live results container if open
        ['desktop', 'mobile'].forEach(ctx => {
            const clearBtn = document.getElementById(ctx === 'desktop' ? 'dest-search-clear' : 'mobile-dest-search-clear');
            if (clearBtn) clearBtn.classList.add('hidden');
            const res = document.getElementById(ctx === 'desktop' ? 'desktop-search-results' : 'mobile-search-results');
            if (res) res.classList.add('hidden');
        });

        // Reset visual active styling on all destination buttons
        document.querySelectorAll('[data-dest-key]').forEach(btn => {
            const key = btn.getAttribute('data-dest-key');
            if (key === 'all') {
                btn.classList.add('border-[#007A5E]', 'dark:border-emerald-500', 'bg-[#ECFDF5]', 'dark:bg-emerald-950/40', 'text-[#007A5E]', 'dark:text-emerald-400');
            } else {
                btn.classList.remove('border-[#007A5E]', 'dark:border-emerald-500', 'bg-[#ECFDF5]', 'dark:bg-emerald-950/40', 'text-[#007A5E]', 'dark:text-emerald-400');
            }
        });

        // Reset guest count UI in mobile sheet and desktop
        const mGuests = document.getElementById('mobile-sheet-guests');
        if (mGuests) mGuests.textContent = toPersianNum(2);
        const dGuests = document.getElementById('desktop-guests-label');
        if (dGuests) dGuests.textContent = '۲ نفر';

        syncInstantUI();
        updateDateLabelsAndSummary();
        renderJalaliCalendars();
        filterByCategory('all');
        simulateWireLoading(() => renderListings());
        showToast('تمامی فیلترها پاکسازی شدند');
    }

    function triggerLivewireSearch() {
        closeAllPopups();
        closeMobileSheets();

        const dInput = document.getElementById('dest-search-input');
        const mInput = document.getElementById('mobile-dest-search-input');
        const keywordVal = (dInput && dInput.value.trim()) || (mInput && mInput.value.trim()) || '';
        if (keywordVal) {
            state.keyword = keywordVal;
            state.city = 'all';
            state.province = 'all';
            const dLabel = document.getElementById('desktop-dest-label');
            const mPill = document.getElementById('mobile-pill-dest');
            if (dLabel) dLabel.textContent = `جستجو: ${toPersianNum(state.keyword)}`;
            if (mPill) mPill.textContent = `جستجو: ${toPersianNum(state.keyword)}`;
        }

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
        updateMonthNavigationUI();
        const curMonth = CALENDAR_MONTHS[activeCalendarMonthIndex] || CALENDAR_MONTHS[0];
        const monthDaysCount = curMonth.daysCount;
        const monthFirstDay = curMonth.firstDayOfWeek;
        const isFirstMonth = activeCalendarMonthIndex === 0;

        const absStart = getAbsoluteDay(state.startMonthIndex, state.startDay);
        const absEnd = getAbsoluteDay(state.endMonthIndex, state.endDay);

        ['desktop-jalali-grid', 'mobile-jalali-grid'].forEach(gridId => {
            const grid = document.getElementById(gridId);
            if (!grid) return;
            let html = '';

            // Add blank cells for correct Jalali weekday offset alignment (matches image ghost slots)
            for (let b = 0; b < monthFirstDay; b++) {
                html += '<div class="h-10 sm:h-11 w-full rounded-lg opacity-0 pointer-events-none"></div>';
            }

            for (let d = 1; d <= monthDaysCount; d++) {
                const currentDayAbs = getAbsoluteDay(activeCalendarMonthIndex, d);
                const isPast = isFirstMonth && (d < currentJalaliDay);
                const isToday = isFirstMonth && (d === currentJalaliDay);
                const isCheckIn = (currentDayAbs === absStart);
                const isCheckOut = (currentDayAbs === absEnd);
                const inRange = (currentDayAbs > absStart && currentDayAbs < absEnd);

                if (isPast) {
                    // Past Days: Soft muted tile matching sample (days 1 to 13 in image 2)
                    html += `
                        <button type="button" disabled
                                title="امکان انتخاب تاریخ در گذشته وجود ندارد"
                                class="h-10 sm:h-11 w-full rounded-lg sm:rounded-xl text-xs sm:text-sm font-medium bg-[#F8F9FA] dark:bg-zinc-800/40 border border-[#F1F3F5] dark:border-zinc-800/60 text-[#ADB5BD] dark:text-zinc-600 select-none cursor-not-allowed flex items-center justify-center font-sans">
                            ${toPersianNum(d)}
                        </button>
                    `;
                } else if (isCheckIn && isCheckOut) {
                    // Single day check-in & check-out: Solid deep panel brand color
                    html += `
                        <button type="button" onclick="selectCalendarDay(${d})"
                                title="تاریخ ورود و خروج"
                                class="h-10 sm:h-11 w-full rounded-lg sm:rounded-xl font-black text-xs sm:text-sm bg-[#007A5E] dark:bg-emerald-600 text-white shadow-md flex items-center justify-center transition-transform active:scale-95 font-sans z-10">
                            ${toPersianNum(d)}
                        </button>
                    `;
                } else if (isCheckIn) {
                    // Check-in (day 19 in sample): Solid deep panel brand color with white text
                    html += `
                        <button type="button" onclick="selectCalendarDay(${d})"
                                title="تاریخ ورود"
                                class="h-10 sm:h-11 w-full rounded-lg sm:rounded-xl font-black text-xs sm:text-sm bg-[#007A5E] dark:bg-emerald-600 text-white shadow-md flex items-center justify-center transition-transform active:scale-95 font-sans z-10">
                            ${toPersianNum(d)}
                        </button>
                    `;
                } else if (isCheckOut) {
                    // Check-out (day 22 in sample): Solid deep panel brand color with white text
                    html += `
                        <button type="button" onclick="selectCalendarDay(${d})"
                                title="تاریخ خروج"
                                class="h-10 sm:h-11 w-full rounded-lg sm:rounded-xl font-black text-xs sm:text-sm bg-[#007A5E] dark:bg-emerald-600 text-white shadow-md flex items-center justify-center transition-transform active:scale-95 font-sans z-10">
                            ${toPersianNum(d)}
                        </button>
                    `;
                } else if (inRange) {
                    // In-Range days (days 20 and 21 in sample): Solid medium panel brand tint with white text
                    html += `
                        <button type="button" onclick="selectCalendarDay(${d})"
                                title="بازه انتخابی"
                                class="h-10 sm:h-11 w-full rounded-lg sm:rounded-xl font-bold text-xs sm:text-sm bg-[#007A5E]/75 dark:bg-emerald-600/75 text-white shadow-2xs flex items-center justify-center transition-colors font-sans">
                            ${toPersianNum(d)}
                        </button>
                    `;
                } else {
                    // Future Unselected days (days 14-18, 23-30 in sample): Clean white card tile with crisp border (uniform styling, today is not styled differently)
                    html += `
                        <button type="button" onclick="selectCalendarDay(${d})"
                                class="h-10 sm:h-11 w-full rounded-lg sm:rounded-xl text-xs sm:text-sm font-bold bg-white dark:bg-zinc-800 border border-[#E9ECEF] dark:border-zinc-700/80 text-[#212529] dark:text-zinc-100 shadow-2xs hover:border-[#007A5E] hover:text-[#007A5E] dark:hover:border-emerald-400 dark:hover:text-emerald-400 hover:bg-emerald-50/30 dark:hover:bg-zinc-700/60 flex items-center justify-center transition-all active:scale-95 font-sans">
                            ${toPersianNum(d)}
                        </button>
                    `;
                }
            }
            grid.innerHTML = html;
        });
    }

    function selectCalendarDay(day) {
        // Strict validation: Reject past dates only if in first month (index 0) and day < currentJalaliDay
        if (activeCalendarMonthIndex === 0 && day < currentJalaliDay) {
            showToast('امکان انتخاب تاریخ در گذشته وجود ندارد');
            return;
        }

        const clickedAbs = getAbsoluteDay(activeCalendarMonthIndex, day);
        const startAbs = getAbsoluteDay(state.startMonthIndex, state.startDay);

        if (dateSelectionStep === 'start') {
            // User is setting Check-in date:
            state.startMonthIndex = activeCalendarMonthIndex;
            state.startDay = day;

            const currentEndAbs = getAbsoluteDay(state.endMonthIndex, state.endDay);
            if (currentEndAbs <= clickedAbs) {
                // Automatically set Check-out to next day
                const nextDay = getDateFromAbsoluteDay(clickedAbs + 1);
                state.endMonthIndex = nextDay.monthIndex;
                state.endDay = nextDay.day;
            }
            dateSelectionStep = 'end';
        } else {
            // User is setting Check-out date:
            if (clickedAbs > startAbs) {
                state.endMonthIndex = activeCalendarMonthIndex;
                state.endDay = day;
            } else {
                // Clicked before or on check-in: reset check-in to this day and set check-out to +1 day
                state.startMonthIndex = activeCalendarMonthIndex;
                state.startDay = day;
                const nextDay = getDateFromAbsoluteDay(clickedAbs + 1);
                state.endMonthIndex = nextDay.monthIndex;
                state.endDay = nextDay.day;
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

    let currentMobileSearchSection = 'dest';

    function setMobileSearchSection(sec) {
        currentMobileSearchSection = sec;
        ['dest', 'date'].forEach(s => {
            const exp = document.getElementById('m-section-' + s + '-expanded');
            const col = document.getElementById('m-section-' + s + '-collapsed');
            if (exp) exp.classList.toggle('hidden', s !== sec);
            if (col) col.classList.toggle('hidden', s === sec);
        });
    }

    function openMobileSearchSheet() {
        setMobileSearchSection('dest');
        document.getElementById('mobile-search-sheet').classList.remove('hidden');
    }

    function closeMobileSheets() {
        document.getElementById('mobile-search-sheet').classList.add('hidden');
    }

    function openAuthModal() {
        window.location.href = '{{ route("login") }}';
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
        syncInstantUI();
        updateDateLabelsAndSummary();
        renderJalaliCalendars();
        renderListings();
        updateLastMinuteTimerUI();
        initHeroSlider();
    });
</script>
@endsection
