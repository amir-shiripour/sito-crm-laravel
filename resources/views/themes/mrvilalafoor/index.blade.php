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

    $rentalSeoContentEnabled = $rentalSeoContentEnabled ?? true;
    $rentalSeoContentTitle = $rentalSeoContentTitle ?? null;
    $rentalSeoContentBody = $rentalSeoContentBody ?? null;
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('property_settings')) {
            if (!isset($rentalSeoContentEnabled)) {
                $rentalSeoContentEnabled = (bool) \Modules\Properties\Entities\PropertySetting::get('rental_seo_content_enabled', 1);
            }
            if (!$rentalSeoContentTitle) {
                $rentalSeoContentTitle = \Modules\Properties\Entities\PropertySetting::get('rental_seo_content_title', 'مستر ویلا؛ سامانه رزرواسیون و اجاره آنلاین ویلا و اقامتگاه در شمال');
            }
            if (!$rentalSeoContentBody) {
                $rentalSeoContentBody = \Modules\Properties\Entities\PropertySetting::get('rental_seo_content_body', "مستر ویلا سال‌ها است که به عنوان مرجع تخصصی رزرو و اجاره آنلاین انواع اقامتگاه، کلبه‌های جنگلی، سوئیت و ویلاهای استخردار در زیباترین مناطق شمال کشور به‌ویژه منطقه سرسبز لفور و سوادکوه شناخته می‌شود. هدف ما ایجاد تجربه‌ای بی‌دغدغه و مطمئن از سفر برای تمام میهمانان گرامی است. تضمین تطابق کامل تصاویر با اقامتگاه، پشتیبانی ۲۴ ساعته در تمام روزهای هفته، تحویل تضمین‌شده و تسویه با میزبان پس از رضایت و ورود میهمان، از استانداردهای بنیادین مستر ویلا می‌باشد. با خیالی آسوده اقامتگاه رویایی خود را بررسی کرده و تنها با چند کلیک، خاطره‌ای ماندگار در دل طبیعت بسازید.");
            }
        }
    } catch (\Throwable $e) {}

    $currencyLabel = $currencyLabel ?? (\Modules\Properties\Entities\PropertySetting::getCurrencyLabel() ?? 'تومان');

    $rentalTypes = [];
    try {
        if (class_exists(\Modules\Properties\Entities\Property::class)) {
            $rentalTypes = \Modules\Properties\Entities\Property::getRentalPropertyTypes(true);
        }
    } catch (\Throwable $e) {}

    $propertyCategories = collect();
    try {
        if (class_exists(\Modules\Properties\Entities\PropertyCategory::class)) {
            $propertyCategories = \Modules\Properties\Entities\PropertyCategory::withCount(['properties' => function($q) {
                $q->where('publication_status', 'published');
            }])->get();
        }
    } catch (\Throwable $e) {}

    $rentalTypeCounts = [];
    try {
        if (class_exists(\Modules\Properties\Entities\Property::class)) {
            $rentalTypeCounts = \Modules\Properties\Entities\Property::where('publication_status', 'published')
                ->whereNotNull('property_type')
                ->groupBy('property_type')
                ->selectRaw('property_type, count(*) as total')
                ->pluck('total', 'property_type')
                ->toArray();
        }
    } catch (\Throwable $e) {}

    $aggregatedExploreItems = [];
    foreach ($rentalTypes as $tKey => $tItem) {
        $aggregatedExploreItems[] = [
            'key' => $tKey,
            'title' => $tItem['label'] ?? $tKey,
            'url' => route('properties.index', ['property_type' => $tKey]),
            'icon' => $tItem['icon'] ?? $tKey,
            'image' => $tItem['image'] ?? null,
            'count' => $rentalTypeCounts[$tKey] ?? 0,
            'source' => 'property_type',
        ];
    }
    foreach ($propertyCategories as $cat) {
        $aggregatedExploreItems[] = [
            'key' => 'cat_' . $cat->id,
            'title' => $cat->name,
            'url' => route('properties.index', ['category_id' => $cat->id]),
            'icon' => $cat->icon ?: 'category',
            'image' => $cat->image,
            'count' => $cat->properties_count ?? 0,
            'source' => 'category',
            'color' => $cat->color,
        ];
    }

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
       - Mobile (<640px): strictly 1 complete card + peek of 2nd card
       - Tablet (640px-767px): 2 complete cards + peek of 3rd card
       - Desktop (>=768px): strictly 3 complete cards + peek of 4th card
    */
    .section-carousel > article,
    .carousel-card-item {
        width: calc((100% - 16px) / 1.18) !important;
        min-width: calc((100% - 16px) / 1.18) !important;
        max-width: calc((100% - 16px) / 1.18) !important;
        flex-shrink: 0 !important;
        scroll-snap-align: start;
    }
    @media (min-width: 640px) {
        .section-carousel > article,
        .carousel-card-item {
            width: calc((100% - 32px) / 2.25) !important;
            min-width: calc((100% - 32px) / 2.25) !important;
            max-width: calc((100% - 32px) / 2.25) !important;
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
            width: calc((100% - 60px) / 3.25) !important;
            min-width: calc((100% - 60px) / 3.25) !important;
            max-width: calc((100% - 60px) / 3.25) !important;
        }
    }

    /* Grid cards inside favorites tab expand to column size */
    #favorites-tab-grid > article {
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
    }

    /* Touch & Drag to scroll cursor, momentum and grab enhancements */
    .section-carousel {
        cursor: grab;
        user-select: none;
        -webkit-user-select: none;
        -webkit-overflow-scrolling: touch;
        touch-action: pan-x pan-y pinch-zoom;
        overscroll-behavior-x: contain;
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

    /* Uniform 2-line title height (Locked at 2.75rem so cards never vary in height) */
    .card-title-fixed-height {
        line-height: 1.375rem !important;
        height: 2.75rem !important;
        min-height: 2.75rem !important;
        max-height: 2.75rem !important;
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
            <!-- HERO PROMOTIONAL BANNER CAROUSEL (اسلایدر بنرهای اختصاصی)     -->
            <!-- ============================================================= -->
            @php
                $rawHeroSlides = class_exists(\Modules\Properties\Entities\PropertySetting::class) 
                    ? \Modules\Properties\Entities\PropertySetting::get('rental_hero_slides') 
                    : null;
                $allHeroSlides = $rawHeroSlides ? json_decode($rawHeroSlides, true) : [];
                $activeHeroSlides = is_array($allHeroSlides) 
                    ? array_values(array_filter($allHeroSlides, fn($s) => !empty($s['is_active']) && !empty($s['image']))) 
                    : [];

                $rawHeroBg = class_exists(\Modules\Properties\Entities\PropertySetting::class) 
                    ? \Modules\Properties\Entities\PropertySetting::get('rental_hero_bg_image') 
                    : null;
                $rentalHeroBg = $rawHeroBg ? asset($rawHeroBg) : null;

                $rawHeroMobileBg = class_exists(\Modules\Properties\Entities\PropertySetting::class) 
                    ? \Modules\Properties\Entities\PropertySetting::get('rental_hero_mobile_bg_image') 
                    : null;
                $rentalHeroMobileBg = $rawHeroMobileBg ? asset($rawHeroMobileBg) : null;

                // تصویر موبایل در صورت وجود، یا فالبک به تصویر دسکتاپ
                $effectiveMobileBg = $rentalHeroMobileBg ?: $rentalHeroBg;

                // وضعیت وجود تصاویر پس‌زمینه
                $hasDesktopHeroBg = !empty($rentalHeroBg);
                $hasMobileHeroBg = !empty($effectiveMobileBg);
                $hasAnyHeroBg = $hasDesktopHeroBg || $hasMobileHeroBg;

                // موقعیت عمودی محتوای هیرو (وسط یا بالا)
                $rentalHeroValign = class_exists(\Modules\Properties\Entities\PropertySetting::class) 
                    ? \Modules\Properties\Entities\PropertySetting::get('rental_hero_valign', 'center') 
                    : 'center';

                // موقعیت فوکوس پس‌زمینه (background-position)
                $rentalHeroBgPos = class_exists(\Modules\Properties\Entities\PropertySetting::class) 
                    ? \Modules\Properties\Entities\PropertySetting::get('rental_hero_bg_position', 'center') 
                    : 'center';

                $rentalHeroMobileBgPos = class_exists(\Modules\Properties\Entities\PropertySetting::class) 
                    ? \Modules\Properties\Entities\PropertySetting::get('rental_hero_mobile_bg_position', 'center') 
                    : 'center';

                $valignDesktopClass = $rentalHeroValign === 'top' 
                    ? 'md:justify-start md:pt-16 lg:pt-20 xl:pt-24 md:pb-12' 
                    : 'md:justify-center md:py-32 lg:py-40';
            @endphp

            @if($hasAnyHeroBg)
                <style>
                    @if($effectiveMobileBg)
                        .hero-panoramic-bg {
                            background-image: url('{{ $effectiveMobileBg }}');
                            background-position: {{ $rentalHeroMobileBgPos }};
                        }
                    @endif
                    @if($rentalHeroBg)
                        @media (min-width: 768px) {
                            .hero-panoramic-bg {
                                background-image: url('{{ $rentalHeroBg }}') !important;
                                background-position: {{ $rentalHeroBgPos }} !important;
                            }
                        }
                    @else
                        @media (min-width: 768px) {
                            .hero-panoramic-bg {
                                background-image: none !important;
                            }
                        }
                    @endif
                </style>
            @endif

            <!-- FULL-WIDTH PANORAMIC HERO (Mobile & Desktop) OR ULTRA-MINIMAL HERO (Desktop Only) -->
            @if($hasAnyHeroBg)
                <section class="hero-panoramic-bg w-full relative bg-cover overflow-hidden {{ $hasMobileHeroBg ? 'block h-40 sm:h-48' : 'hidden md:block' }} {{ $hasDesktopHeroBg ? 'md:h-auto md:min-h-[640px] lg:min-h-[720px] xl:min-h-[780px] md:flex md:flex-col ' . $valignDesktopClass . ' md:items-center shadow-xs' : 'md:h-auto md:min-h-0 md:pt-6 md:pb-8 md:block' }}">
            @else
                <section class="hidden md:block pt-6 pb-8">
            @endif

                @if($hasDesktopHeroBg)
                    {{-- Desktop Dark Gradient Backdrop Overlay (Edge-to-Edge) --}}
                    <div class="hidden md:block absolute inset-0 bg-gradient-to-b from-black/60 via-black/35 to-black/65 backdrop-blur-[0.5px]"></div>
                @endif

                <div class="relative z-10 w-full max-w-5xl lg:max-w-6xl xl:max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center font-sans hidden md:block">
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl xl:text-[38px] font-black {{ $hasDesktopHeroBg ? 'text-white drop-shadow-md' : 'text-[#18181B] dark:text-zinc-100' }} tracking-tight mb-8 leading-snug whitespace-normal md:whitespace-nowrap">
                        {{ $appName }} . اجاره ویلا، کلبه، بومگردی و انواع اقامتگاه در شمال
                    </h1>

                    <div class="max-w-4xl lg:max-w-5xl mx-auto">
                        <!-- 1. DESKTOP 3-PART FLAT SEARCH BAR (Hidden on Mobile) -->
                        <div class="hidden md:grid {{ $hasDesktopHeroBg ? 'bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border border-white/40 dark:border-zinc-700/60 shadow-2xl' : 'bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 shadow-sm' }} rounded-2xl p-2 grid-cols-12 gap-2 items-center text-right font-sans">
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

                    <!-- Quick Zone & Instant Filter Pills (Desktop Only) -->
                    <div class="hidden md:flex items-center justify-center flex-wrap gap-2 mt-5 sm:mt-6">
                        <button type="button" 
                                id="instant-pill-btn"
                                onclick="toggleInstantFilter(event)"
                                class="px-4 py-2 rounded-full text-xs font-bold transition-all {{ $rentalHeroBg ? 'bg-white/90 dark:bg-zinc-900/90 backdrop-blur-md text-zinc-800 dark:text-zinc-100 border border-white/30 shadow-md' : 'border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300' }} hover:border-[#007A5E] hover:text-[#007A5E] dark:hover:text-emerald-400 shadow-xs flex items-center gap-1.5 active:scale-95">
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
                            <button type="button" onclick="applyQuickZoneFilter('{{ $quickCity }}', '{{ $quickCity }}', event)" data-zone-pill="{{ $quickCity }}" class="zone-quick-pill px-4 py-2 rounded-full text-xs font-bold {{ $hasDesktopHeroBg ? 'bg-white/90 dark:bg-zinc-900/90 backdrop-blur-md text-zinc-800 dark:text-zinc-100 border border-white/30 shadow-md' : 'bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-800' }} hover:border-[#007A5E] dark:hover:border-emerald-500 hover:text-[#007A5E] dark:hover:text-emerald-400 shadow-xs transition-all active:scale-95">
                                {{ $quickCity }}
                            </button>
                        @endforeach
                    </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================= -->
            <!-- LISTINGS SECTION CONTAINER (Dynamic Carousels with Unified Consistent Spacing) -->
            <!-- ============================================================= -->
            <main class="w-full {{ $hasAnyHeroBg ? 'pt-12 sm:pt-16 lg:pt-20' : 'pt-6 sm:pt-8 lg:pt-10' }} pb-0 flex flex-col gap-12 sm:gap-16 lg:gap-20 font-sans">
                <!-- Active Filters Notification Bar -->
                <div id="active-filters-bar" class="max-w-[1360px] mx-auto px-4 sm:px-8 w-full hidden font-sans"></div>

                <!-- ============================================================= -->
                <!-- DYNAMIC AGGREGATED CATEGORIES & PROPERTY TYPES (Flat Cards & Drag Navigation with Peek Effect) -->
                <!-- ============================================================= -->
                @if(!empty($aggregatedExploreItems) && count($aggregatedExploreItems) > 0)
                    <section id="category-explore-section" class="w-full max-w-[1360px] mx-auto px-4 sm:px-8 font-sans">
                        <style>
                            #category-explore-track {
                                cursor: grab;
                                user-select: none;
                                -webkit-user-select: none;
                                -webkit-overflow-scrolling: touch;
                                touch-action: pan-x pan-y pinch-zoom;
                                overscroll-behavior-x: contain;
                                scroll-behavior: smooth;
                                -webkit-tap-highlight-color: transparent;
                            }
                            #category-explore-track.is-dragging {
                                cursor: grabbing !important;
                                scroll-snap-type: none !important;
                                scroll-behavior: auto !important;
                                user-select: none !important;
                                -webkit-user-select: none !important;
                            }
                            #category-explore-track a, #category-explore-track img, #category-explore-track svg {
                                -webkit-user-drag: none;
                                user-drag: none;
                            }
                            #category-explore-track.is-dragging a {
                                pointer-events: none !important;
                            }
                            /* Guaranteed Peek Affordance across all screen resolutions */
                            .category-peek-item {
                                flex: 0 0 calc((100% - 3.5 * 14px) / 4.45) !important;
                                width: calc((100% - 3.5 * 14px) / 4.45) !important;
                                min-width: 76px !important;
                                max-width: 96px !important;
                            }
                            @media (min-width: 640px) {
                                .category-peek-item {
                                    flex: 0 0 calc((100% - 5.5 * 18px) / 6.45) !important;
                                    width: calc((100% - 5.5 * 18px) / 6.45) !important;
                                    min-width: 86px !important;
                                    max-width: 104px !important;
                                }
                            }
                            @media (min-width: 768px) {
                                .category-peek-item {
                                    flex: 0 0 calc((100% - 6.5 * 20px) / 7.45) !important;
                                    width: calc((100% - 6.5 * 20px) / 7.45) !important;
                                    min-width: 90px !important;
                                    max-width: 108px !important;
                                }
                            }
                            @media (min-width: 1024px) {
                                .category-peek-item {
                                    flex: 0 0 calc((100% - 7.5 * 22px) / 8.45) !important;
                                    width: calc((100% - 7.5 * 22px) / 8.45) !important;
                                    min-width: 94px !important;
                                    max-width: 112px !important;
                                }
                            }
                            @media (min-width: 1280px) {
                                .category-peek-item {
                                    flex: 0 0 calc((100% - 8.5 * 24px) / 9.45) !important;
                                    width: calc((100% - 8.5 * 24px) / 9.45) !important;
                                    min-width: 98px !important;
                                    max-width: 116px !important;
                                }
                            }
                        </style>

                        <!-- Section Header Row: Title, Subtitle with generous vertical gap, Carousel Arrows (No "All Properties" button) -->
                        <div class="flex items-center justify-between mb-6 sm:mb-8 px-1">
                            <div>
                                <h2 class="text-sm sm:text-base font-extrabold text-[#18181B] dark:text-zinc-100 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-[#007A5E] dark:bg-emerald-400"></span>
                                    دسته‌بندی و انواع اقامتگاه
                                </h2>
                                <p class="text-[11px] sm:text-xs text-zinc-400 dark:text-zinc-400 mt-2 sm:mt-2.5">کاوش بر اساس سبک و نوع ملک در اقامتگاه‌های شمال</p>
                            </div>
                            <div class="flex items-center gap-2 sm:gap-3">
                                <div class="hidden md:flex items-center gap-1">
                                    <button type="button"
                                            id="cat-scroll-right-btn"
                                            onclick="scrollCategoryExploreTrack('prev')"
                                            class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 hover:bg-[#F7F8F9] dark:hover:bg-zinc-800 flex items-center justify-center text-zinc-600 dark:text-zinc-300 transition-colors shadow-xs active:scale-95 disabled:opacity-30 disabled:pointer-events-none"
                                            title="قبلی">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                    <button type="button"
                                            id="cat-scroll-left-btn"
                                            onclick="scrollCategoryExploreTrack('next')"
                                            class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 hover:bg-[#F7F8F9] dark:hover:bg-zinc-800 flex items-center justify-center text-zinc-600 dark:text-zinc-300 transition-colors shadow-xs active:scale-95 disabled:opacity-30 disabled:pointer-events-none"
                                            title="بعدی">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Items Carousel Track Container with Peek Effect & Desktop Navigation -->
                        <div class="relative">
                            <div id="category-explore-track"
                                 class="flex items-start gap-3.5 sm:gap-5 lg:gap-6 overflow-x-auto no-scrollbar py-1 px-1">
                                @foreach($aggregatedExploreItems as $item)
                                    <a href="{{ $item['url'] }}"
                                       onclick="if(window.isCategoryDragging){event.preventDefault();event.stopPropagation();return false;}"
                                       class="category-peek-item group shrink-0 flex flex-col items-center justify-start text-center bg-transparent border-0 shadow-none hover:shadow-none transition-transform duration-200 active:scale-95 cursor-pointer font-sans select-none no-underline"
                                       title="{{ $item['title'] }}">
                                        
                                        {{-- Flat Icon Squircle Badge (Mint/Pastel Green background, Forest green stroke, Flat without shadow or border) --}}
                                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-[20px] sm:rounded-[22px] bg-[#E8F8F3] dark:bg-emerald-950/40 text-[#007A5E] dark:text-emerald-400 group-hover:bg-[#D4F4E7] dark:group-hover:bg-emerald-900/50 transition-all duration-200 mb-3 sm:mb-3.5 flex items-center justify-center border-0 shadow-none">
                                            @if(!empty($item['image']))
                                                <img src="{{ asset($item['image']) }}" alt="{{ $item['title'] }}" class="w-7 h-7 sm:w-8 sm:h-8 object-contain rounded-lg pointer-events-none">
                                            @else
                                                @php $iKey = strtolower($item['icon'] ?? 'category'); @endphp
                                                <svg class="w-6 h-6 sm:w-7 sm:h-7 pointer-events-none transition-transform duration-200 group-hover:scale-105" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                                                    @if($iKey === 'cottage')
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3L2.5 19.5h19L12 3zm0 6.5v10m-3.5 0v-4h7v4"/>
                                                    @elseif($iKey === 'villa')
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                                                    @elseif($iKey === 'apartment')
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                                                    @elseif($iKey === 'ecolodge')
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c-4.5 0-8 3.5-8 8v9a1 1 0 001 1h14a1 1 0 001-1v-9c0-4.5-3.5-8-8-8zm-2 17v-4a2 2 0 114 0v4m-2-12a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                                                    @elseif($iKey === 'resort')
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.75H6v17.25h2.25z"/>
                                                    @elseif($iKey === 'pool')
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 18.5c1.5 0 2-1 3.5-1s2 1 3.5 1 2-1 3.5-1 2 1 3.5 1 2-1 3.5-1M3 21.5c1.5 0 2-1 3.5-1s2 1 3.5 1 2-1 3.5-1 2 1 3.5 1 2-1 3.5-1M7 14V3.5a1.5 1.5 0 013 0V14m-3-7h3m-3 4h3m4-7.5V3.5a1.5 1.5 0 013 0V14m-3-7h3m-3 4h3"/>
                                                    @elseif($iKey === 'beach')
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM3 19.5c2 0 3-1 4.5-1s2.5 1 4.5 1 3-1 4.5-1 2.5 1 4.5 1"/>
                                                    @elseif($iKey === 'forest')
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L6 11h3l-4 7h6v3h2v-3h6l-4-7h3L12 2z"/>
                                                    @elseif($iKey === 'mountain')
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 19.5L9.75 7.5l4.5 7.5 3-4.5 4.5 9H2.25zM8.25 10l2 3.5m4.25-1.5l1.5 2.5"/>
                                                    @elseif($iKey === 'jacuzzi')
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13h18c0 4.5-3.5 8-8 8h-2c-4.5 0-8-3.5-8-8zm2-4c0-2 1.5-2 1.5-4s-1.5-2-1.5-4m6 8c0-2 1.5-2 1.5-4s-1.5-2-1.5-4m6 8c0-2 1.5-2 1.5-4s-1.5-2-1.5-4"/>
                                                    @elseif($iKey === 'luxury')
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                                                    @elseif($iKey === 'breakfast')
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18zm0-5a4 4 0 100-8 4 4 0 000 8z"/>
                                                    @elseif($iKey === 'pet')
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 13a4 4 0 100-8 4 4 0 000 8zm-7-2a2.5 2.5 0 115 0 2.5 2.5 0 01-5 0zm14 0a2.5 2.5 0 115 0 2.5 2.5 0 01-5 0zM7 18.5a4.5 4.5 0 019 0v.5H7v-.5z"/>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.386a10.978 10.978 0 003.955-3.955c.486-.827.313-1.908-.386-2.607L9.835 4.909A2.25 2.25 0 008.243 4.25H9.568zM6 6h.008v.008H6V6z"/>
                                                    @endif
                                                </svg>
                                            @endif
                                        </div>

                                        {{-- Title (Uniform width with clean truncate) --}}
                                        <span class="w-full text-xs sm:text-[13px] font-bold text-zinc-800 dark:text-zinc-200 group-hover:text-[#007A5E] dark:group-hover:text-emerald-400 transition-colors text-center truncate block px-0.5"
                                              title="{{ $item['title'] }}">
                                            {{ $item['title'] }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif

                <!-- Dynamic Sections Carousel Container (Consistent Spacing Between All Sections) -->
                <div id="sections-wrapper" class="w-full flex flex-col gap-12 sm:gap-16 lg:gap-20">
                    <div id="sections-before-slider" class="w-full flex flex-col gap-12 sm:gap-16 lg:gap-20 empty:hidden"></div>

                    @if(!empty($activeHeroSlides) && count($activeHeroSlides) > 0)
                        <!-- HERO PROMOTIONAL BANNER CAROUSEL (Positioned between 'اقامتگاه‌های پیشنهادی' and 'جدیدترین بوم‌گردی‌ها و اقامتگاه‌ها') -->
                        <section id="hero-banner-section" class="w-full max-w-[1360px] mx-auto px-4 sm:px-8 font-sans">
                            <style>
                                #hero-slider-track {
                                    scroll-snap-type: x mandatory;
                                    overscroll-behavior-x: contain;
                                    -webkit-overflow-scrolling: touch;
                                    touch-action: pan-x pan-y pinch-zoom;
                                    user-select: none;
                                    -webkit-user-select: none;
                                }
                                .hero-slide {
                                    scroll-snap-align: center;
                                    scroll-snap-stop: normal;
                                }
                                .hero-slide a {
                                    -webkit-user-drag: none;
                                    user-drag: none;
                                }
                                .hero-slide img {
                                    pointer-events: none;
                                    -webkit-user-drag: none;
                                    user-drag: none;
                                }
                            </style>
                            <div class="relative group">
                                <!-- Carousel Scroll Track with Peek Effect -->
                                <div id="hero-slider-track"
                                     class="flex gap-3 sm:gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory no-scrollbar py-1 px-1 select-none font-sans cursor-grab"
                                     style="scrollbar-width: none; -ms-overflow-style: none;">

                                    @foreach($activeHeroSlides as $slideIdx => $slide)
                                        @php
                                            $slideUrl = !empty($slide['link']) ? $slide['link'] : 'javascript:void(0)';
                                            $slideTarget = ($slide['target'] ?? '_self') === '_blank' ? '_blank' : '_self';
                                            $slideImg = $slide['image'] ?? '';
                                            if (!str_starts_with($slideImg, 'http') && !str_starts_with($slideImg, '/')) {
                                                $slideImg = '/' . $slideImg;
                                            }
                                        @endphp
                                        <div class="hero-slide shrink-0 w-[88%] sm:w-[92%] md:w-[94%] snap-center rounded-2xl sm:rounded-3xl overflow-hidden shadow-lg relative block font-sans transition-transform duration-300 aspect-[2.6/1] bg-zinc-100 dark:bg-zinc-800"
                                             data-slide-index="{{ $slideIdx }}">
                                            <a href="{{ $slideUrl }}"
                                               @if($slideTarget === '_blank') target="_blank" rel="noopener noreferrer" @endif
                                               class="block w-full h-full relative"
                                               draggable="false"
                                               aria-label="{{ $slide['title'] ?? ('اسلاید ' . ($slideIdx + 1)) }}">
                                                <img src="{{ asset($slideImg) }}"
                                                     alt="{{ $slide['title'] ?? ('بنر ' . ($slideIdx + 1)) }}"
                                                     class="w-full h-full object-cover rounded-2xl sm:rounded-3xl select-none pointer-events-none"
                                                     draggable="false"
                                                     loading="{{ $slideIdx === 0 ? 'eager' : 'lazy' }}" />
                                            </a>
                                        </div>
                                    @endforeach
                                </div>

                                @if(count($activeHeroSlides) > 1)
                                    <!-- Desktop Navigation Arrows (Floating on edges - correctly pointing outwards) -->
                                    <button type="button" onclick="prevHeroSlide()" aria-label="اسلاید قبلی"
                                            class="hidden md:flex absolute top-1/2 -translate-y-1/2 -right-3 lg:-right-5 w-10 h-10 rounded-full bg-white/95 dark:bg-zinc-900/95 text-zinc-700 dark:text-zinc-200 border border-zinc-200/80 dark:border-zinc-700 shadow-xl items-center justify-center hover:bg-white dark:hover:bg-zinc-800 transition-all z-20 opacity-0 group-hover:opacity-100 active:scale-90 font-sans cursor-pointer">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                    <button type="button" onclick="nextHeroSlide()" aria-label="اسلاید بعدی"
                                            class="hidden md:flex absolute top-1/2 -translate-y-1/2 -left-3 lg:-left-5 w-10 h-10 rounded-full bg-white/95 dark:bg-zinc-900/95 text-zinc-700 dark:text-zinc-200 border border-zinc-200/80 dark:border-zinc-700 shadow-xl items-center justify-center hover:bg-white dark:hover:bg-zinc-800 transition-all z-20 opacity-0 group-hover:opacity-100 active:scale-90 font-sans cursor-pointer">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                    </button>

                                    <!-- Modern Pagination Indicators (Hidden on Mobile per user request) -->
                                    <div id="hero-slider-dots" class="hidden sm:flex items-center justify-center gap-1.5 mt-2.5 sm:mt-3.5 font-sans">
                                        @foreach($activeHeroSlides as $dotIdx => $dotSlide)
                                            <button type="button" onclick="goToHeroSlide({{ $dotIdx }})"
                                                    class="hero-dot {{ $dotIdx === 0 ? 'w-6 h-2 bg-[#EA580C] dark:bg-amber-400' : 'w-2 h-2 bg-zinc-300 dark:bg-zinc-700 hover:bg-zinc-400 dark:hover:bg-zinc-600' }} rounded-full transition-all duration-300 font-sans cursor-pointer"
                                                    aria-label="اسلاید {{ $dotIdx + 1 }}"></button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </section>
                    @endif

                    <div id="sections-after-slider" class="w-full flex flex-col gap-12 sm:gap-16 lg:gap-20 empty:hidden"></div>

                    <!-- ============================================================= -->
                    <!-- VALUE PROPOSITIONS & TRUST GUARANTEES (بخش تضمین‌ها و ویژگی‌های اقامتگاه‌ها) -->
                    <!-- ============================================================= -->
                    <section id="trust-guarantees-section" class="w-full max-w-[1360px] mx-auto px-4 sm:px-8 font-sans">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4 lg:gap-5">
                            
                            {{-- ۱. ضمانت تحویل اقامتگاه --}}
                            <div class="bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 rounded-2xl sm:rounded-3xl p-4 sm:p-5 flex items-center gap-3.5 sm:gap-4 shadow-xs hover:shadow-sm hover:border-amber-300 dark:hover:border-amber-700/60 transition-all duration-200 group">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-800/40 text-amber-500 dark:text-amber-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform duration-200 shadow-2xs">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                                    </svg>
                                </div>
                                <div class="space-y-1 text-right min-w-0">
                                    <h3 class="text-xs sm:text-sm font-black text-[#18181B] dark:text-zinc-100 tracking-tight group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors truncate">
                                        ضمانت تحویل اقامتگاه
                                    </h3>
                                    <p class="text-[11px] sm:text-xs text-zinc-400 dark:text-zinc-400 font-medium leading-relaxed font-sans">
                                        تسویه با میزبان پس از تحویل اقامتگاه
                                    </p>
                                </div>
                            </div>

                            {{-- ۲. کم‌هزینه و باکیفیت --}}
                            <div class="bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 rounded-2xl sm:rounded-3xl p-4 sm:p-5 flex items-center gap-3.5 sm:gap-4 shadow-xs hover:shadow-sm hover:border-emerald-300 dark:hover:border-emerald-700/60 transition-all duration-200 group">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-800/40 text-[#007A5E] dark:text-emerald-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform duration-200 shadow-2xs">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V14a2 2 0 01-2 2h-4.5l-4 4-4-4H5a2 2 0 01-2-2V4.757a2 2 0 012-2h14a2 2 0 012 2zM9 9h.01M15 15h.01"/>
                                    </svg>
                                </div>
                                <div class="space-y-1 text-right min-w-0">
                                    <h3 class="text-xs sm:text-sm font-black text-[#18181B] dark:text-zinc-100 tracking-tight group-hover:text-[#007A5E] dark:group-hover:text-emerald-400 transition-colors truncate">
                                        کم‌هزینه و باکیفیت
                                    </h3>
                                    <p class="text-[11px] sm:text-xs text-zinc-400 dark:text-zinc-400 font-medium leading-relaxed font-sans">
                                        اقامت باکیفیت‌تر با هزینه کمتر
                                    </p>
                                </div>
                            </div>

                            {{-- ۳. ضمانت تطابق امکانات --}}
                            <div class="bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 rounded-2xl sm:rounded-3xl p-4 sm:p-5 flex items-center gap-3.5 sm:gap-4 shadow-xs hover:shadow-sm hover:border-indigo-300 dark:hover:border-indigo-700/60 transition-all duration-200 group">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200/60 dark:border-indigo-800/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform duration-200 shadow-2xs">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 13.5l1.5 1.5 3-3"/>
                                    </svg>
                                </div>
                                <div class="space-y-1 text-right min-w-0">
                                    <h3 class="text-xs sm:text-sm font-black text-[#18181B] dark:text-zinc-100 tracking-tight group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate">
                                        ضمانت تطابق امکانات
                                    </h3>
                                    <p class="text-[11px] sm:text-xs text-zinc-400 dark:text-zinc-400 font-medium leading-relaxed font-sans">
                                        یکسان بودن اقامتگاه‌ها با تصاویر
                                    </p>
                                </div>
                            </div>

                            {{-- ۴. رزرواسیون ۲۴ ساعته --}}
                            <div class="bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 rounded-2xl sm:rounded-3xl p-4 sm:p-5 flex items-center gap-3.5 sm:gap-4 shadow-xs hover:shadow-sm hover:border-sky-300 dark:hover:border-sky-700/60 transition-all duration-200 group">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-sky-50 dark:bg-sky-950/40 border border-sky-200/60 dark:border-sky-800/40 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform duration-200 shadow-2xs">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 19.5a1.5 1.5 0 01-1.5-1.5v-3a1.5 1.5 0 011.5-1.5h1.5a1.5 1.5 0 011.5 1.5v3a1.5 1.5 0 01-1.5 1.5h-1.5zm7.5 0a1.5 1.5 0 01-1.5-1.5v-3a1.5 1.5 0 011.5-1.5h1.5a1.5 1.5 0 011.5 1.5v3a1.5 1.5 0 01-1.5 1.5h-1.5z"/>
                                    </svg>
                                </div>
                                <div class="space-y-1 text-right min-w-0">
                                    <h3 class="text-xs sm:text-sm font-black text-[#18181B] dark:text-zinc-100 tracking-tight group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors truncate">
                                        رزرواسیون ۲۴ ساعته
                                    </h3>
                                    <p class="text-[11px] sm:text-xs text-zinc-400 dark:text-zinc-400 font-medium leading-relaxed font-sans">
                                        پشتیبانی آنلاین در تمام روزهای هفته
                                    </p>
                                </div>
                            </div>

                        </div>
                    </section>

                    {{-- ============================================================= --}}
                    {{-- HOMEPAGE SEO & INTRODUCTORY CONTENT (Digikala/Jabama style)   --}}
                    {{-- کاملاً فلت و بدون کادر/باکس، با دکمه بازشونده «مشاهده بیشتر»    --}}
                    {{-- ============================================================= --}}
                    @if(!empty($rentalSeoContentEnabled) && !empty($rentalSeoContentBody))
                        <section id="homepage-seo-content-section" 
                                 x-data="{ expanded: false }"
                                 class="w-full max-w-[1360px] mx-auto px-4 sm:px-8 font-sans">
                            
                            @if(!empty($rentalSeoContentTitle))
                                <h2 class="text-base sm:text-lg lg:text-xl font-bold text-slate-800 dark:text-zinc-100 tracking-tight leading-snug mb-3 text-right">
                                    {{ $rentalSeoContentTitle }}
                                </h2>
                            @endif

                            <div class="relative transition-all duration-300 overflow-hidden"
                                 :class="expanded ? 'max-h-none' : 'max-h-20 sm:max-h-24'">
                                
                                <div class="text-xs sm:text-[13px] text-slate-600 dark:text-slate-300 leading-relaxed sm:leading-7 text-justify font-sans space-y-2 [&_h1]:text-base [&_h1]:font-bold [&_h1]:text-slate-900 dark:[&_h1]:text-white [&_h1]:my-2 [&_h2]:text-sm [&_h2]:font-bold [&_h2]:text-slate-800 dark:[&_h2]:text-zinc-100 [&_h2]:my-2 [&_h3]:text-xs [&_h3]:font-bold [&_h3]:text-slate-800 dark:[&_h3]:text-zinc-200 [&_p]:mb-2 [&_ul]:list-disc [&_ul]:mr-5 [&_ol]:list-decimal [&_ol]:mr-5 [&_a]:text-indigo-600 dark:[&_a]:text-indigo-400 [&_a]:underline">
                                    {!! $rentalSeoContentBody !!}
                                </div>

                                {{-- افکت گرادیان محوشدگی انتهای متن در حالت بسته --}}
                                <div x-show="!expanded"
                                     class="absolute bottom-0 inset-x-0 h-14 bg-gradient-to-t from-white via-white/85 to-transparent dark:from-gray-950 dark:via-gray-950/85 pointer-events-none transition-opacity duration-300"></div>
                            </div>

                            {{-- دکمه مشاهده بیشتر - فقط در حالت بسته نمایش داده می‌شود و پس از باز شدن حذف می‌گردد --}}
                            <div x-show="!expanded" class="flex items-center justify-start mt-2">
                                <button type="button" 
                                        @click="expanded = true"
                                        class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-600 hover:text-sky-700 dark:text-sky-400 dark:hover:text-sky-300 transition-colors cursor-pointer py-1 pe-2 rounded-lg no-underline hover:no-underline group">
                                    <span>مشاهده بیشتر</span>
                                    <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                    </svg>
                                </button>
                            </div>
                        </section>
                    @endif
                </div>

                <!-- Minimal Empty State (Hidden by default, consumes 0 space when hidden) -->
                <div id="empty-state" class="hidden max-w-[1360px] mx-auto px-4 sm:px-8 py-20 text-center space-y-3 font-sans">
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
        <!-- MOBILE BOTTOM NAVIGATION (Tab Bar: خانه / اقامتگاه‌ها / سفرهای من / مورد علاقه‌ها / حساب من) -->
        <!-- ============================================================= -->
        <nav id="mobile-bottom-nav" class="md:hidden fixed bottom-0 right-0 left-0 h-16 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-sm border-t border-[#ECECEE] dark:border-zinc-800 z-40 grid grid-cols-5 items-center px-1 select-none">
            {{-- 1. خانه --}}
            <button type="button" onclick="switchMobileTab('explore')" id="mob-nav-explore" class="flex flex-col items-center justify-center gap-1 py-1 text-[#007A5E] dark:text-emerald-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                <span class="text-[10px] font-bold">خانه</span>
            </button>

            {{-- 2. اقامتگاه‌ها --}}
            <a href="{{ route('properties.index') }}" id="mob-nav-properties" class="flex flex-col items-center justify-center gap-1 py-1 text-zinc-400 dark:text-zinc-500 hover:text-[#007A5E] no-underline">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                <span class="text-[10px] font-medium">اقامتگاه‌ها</span>
            </a>

            {{-- 3. سفرهای من --}}
            <button type="button" onclick="switchMobileTab('trips')" id="mob-nav-trips" class="flex flex-col items-center justify-center gap-1 py-1 text-zinc-400 dark:text-zinc-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                <span class="text-[10px] font-medium">سفرهای من</span>
            </button>

            {{-- 4. مورد علاقه‌ها --}}
            <button type="button" onclick="switchMobileTab('favorites')" id="mob-nav-favorites" class="flex flex-col items-center justify-center gap-1 py-1 text-zinc-400 dark:text-zinc-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                <span class="text-[10px] font-medium">مورد علاقه‌ها</span>
            </button>

            {{-- 5. حساب من --}}
            <button type="button" onclick="switchMobileTab('profile')" id="mob-nav-profile" class="flex flex-col items-center justify-center gap-1 py-1 text-zinc-400 dark:text-zinc-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                <span class="text-[10px] font-medium">حساب من</span>
            </button>
        </nav>

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
                    </div>

                    <!-- Dynamic Step Guide / Status Banner (Replaces old Check-in/Check-out cards - matches show.blade.php UX) -->
                    <div class="flex items-center justify-between p-3 rounded-2xl bg-[#F7F8F9] dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/80 text-xs font-sans">
                        <div class="flex items-center gap-2">
                            <span id="mobile-date-step-icon" class="text-sm">📍</span>
                            <span id="mobile-date-step-text" class="font-bold text-[#18181B] dark:text-zinc-100 font-sans">
                                تاریخ ورود و خروج را با لمس روزها انتخاب کنید
                            </span>
                        </div>
                        <button type="button" onclick="clearDateSelection()" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline font-sans">
                            پاک کردن
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
                        <button type="button" onclick="setMobileSearchSection('none')" class="text-[11px] font-bold text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 font-sans">
                            تایید و بستن ↑
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
            title: 'اقامتگاه‌های محبوب',
            subtitle: 'گلچینی از محبوب‌ترین و پرطرفدارترین اقامتگاه‌ها با میزبان‌های مهمان‌نواز'
        },
        {
            key: 'lastminute',
            title: 'تخفیف لحظه آخری',
            subtitle: 'برای امروز و فردا با تخفیف رزرو کن'
        },
        {
            key: 'unique',
            title: 'اقامتگاه‌های پیشنهادی',
            subtitle: 'اقامتگاه‌های منتخب و پیشنهادی برای اقامتی دلنشین'
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
                    'area' => $prop->area ?? null,
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
        'bg-[#EA580C] dark:bg-amber-400',       // Amber / Orange
        'bg-[#007A5E] dark:bg-emerald-400',    // Emerald
        'bg-[#4F46E5] dark:bg-indigo-400',     // Indigo / Purple
        'bg-[#2563EB] dark:bg-blue-400',       // Blue
        'bg-[#0D9488] dark:bg-teal-400',       // Teal
        'bg-[#E11D48] dark:bg-rose-400'        // Rose
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
        let isMouseDown = false;
        let mouseStartX = 0;
        let mouseCurrentX = 0;
        let lastMouseX = 0;
        let hasMovedSignificantly = false;
        let originIndex = 0;

        // Prevent native browser image/link dragghost
        track.addEventListener('dragstart', (e) => e.preventDefault());

        // Mouse Drag (Desktop)
        track.addEventListener('mousedown', (e) => {
            if (e.button !== 0) return; // Only primary left-click
            isMouseDown = true;
            hasMovedSignificantly = false;
            mouseStartX = e.pageX;
            mouseCurrentX = e.pageX;
            originIndex = currentHeroSlideIndex;
            pauseHeroAutoplay();

            track.classList.add('cursor-grabbing');
            track.classList.remove('cursor-grab');
        });

        window.addEventListener('mousemove', (e) => {
            if (!isMouseDown) return;
            mouseCurrentX = e.pageX;
            const diffX = mouseCurrentX - mouseStartX;
            if (Math.abs(diffX) > 8) {
                hasMovedSignificantly = true;
            }
        });

        window.addEventListener('mouseup', (e) => {
            if (!isMouseDown) return;
            isMouseDown = false;
            track.classList.remove('cursor-grabbing');
            track.classList.add('cursor-grab');

            if (hasMovedSignificantly) {
                const diffX = mouseCurrentX - mouseStartX;
                if (diffX > 30) {
                    // Dragged Right -> Next slide (+1, bounded)
                    goToHeroSlide(Math.min(slides.length - 1, originIndex + 1), false);
                } else if (diffX < -30) {
                    // Dragged Left -> Prev slide (-1, bounded)
                    goToHeroSlide(Math.max(0, originIndex - 1), false);
                } else {
                    goToHeroSlide(originIndex, false);
                }
            }
            startHeroAutoplay();
        });

        // Prevent accidental link open if user dragged with mouse or touch
        track.addEventListener('click', (e) => {
            if (hasMovedSignificantly) {
                e.preventDefault();
                e.stopPropagation();
                hasMovedSignificantly = false;
            }
        }, true);

        // Touch Swipe (Mobile)
        let isTouchDown = false;
        let touchStartX = 0;
        let touchStartY = 0;
        let touchCurrentX = 0;
        let isTouchHorizontal = false;

        track.addEventListener('touchstart', (e) => {
            if (e.touches.length > 1) return;
            isTouchDown = true;
            isTouchHorizontal = false;
            touchStartX = e.touches[0].pageX;
            touchStartY = e.touches[0].pageY;
            touchCurrentX = e.touches[0].pageX;
            originIndex = currentHeroSlideIndex;
            pauseHeroAutoplay();
        }, { passive: true });

        track.addEventListener('touchmove', (e) => {
            if (!isTouchDown || e.touches.length > 1) return;
            touchCurrentX = e.touches[0].pageX;
            const diffX = touchCurrentX - touchStartX;
            const diffY = e.touches[0].pageY - touchStartY;

            if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 10) {
                isTouchHorizontal = true;
                hasMovedSignificantly = true;
            }
        }, { passive: true });

        track.addEventListener('touchend', () => {
            if (!isTouchDown) return;
            isTouchDown = false;

            if (isTouchHorizontal) {
                const diffX = touchCurrentX - touchStartX;
                if (diffX > 30) {
                    // Swiped Right -> Next slide (+1, bounded)
                    goToHeroSlide(Math.min(slides.length - 1, originIndex + 1), false);
                } else if (diffX < -30) {
                    // Swiped Left -> Prev slide (-1, bounded)
                    goToHeroSlide(Math.max(0, originIndex - 1), false);
                } else {
                    goToHeroSlide(originIndex, false);
                }
            }
            startHeroAutoplay();
        }, { passive: true });

        track.addEventListener('touchcancel', () => {
            isTouchDown = false;
            startHeroAutoplay();
        }, { passive: true });

        // Pause autoplay on mouse hover
        track.addEventListener('mouseenter', pauseHeroAutoplay);
        track.addEventListener('mouseleave', () => {
            if (!isMouseDown && !isTouchDown) startHeroAutoplay();
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
            if (index >= slides.length) index = 0;
            if (index >= slides.length) index = slides.length - 1;
        }

        currentHeroSlideIndex = index;
        updateHeroSliderDots(index);

        const targetSlide = slides[index];
        if (targetSlide) {
            isProgrammaticScrolling = true;
            clearTimeout(programmaticScrollTimer);

            // Temporarily disable scroll-snap during programmatic slide transition so browser snap engine doesn't clamp to intermediate slides
            track.style.scrollSnapType = 'none';

            const trackRect = track.getBoundingClientRect();
            const slideRect = targetSlide.getBoundingClientRect();
            const deltaX = (slideRect.left + slideRect.width / 2) - (trackRect.left + trackRect.width / 2);

            if (Math.abs(deltaX) > 2) {
                track.scrollBy({ left: deltaX, behavior: 'smooth' });
            }

            const restoreScrollSnap = () => {
                isProgrammaticScrolling = false;
                track.style.scrollSnapType = 'x mandatory';
            };

            let endHandled = false;
            const onScrollEnd = () => {
                if (endHandled) return;
                endHandled = true;
                track.removeEventListener('scrollend', onScrollEnd);
                clearTimeout(programmaticScrollTimer);
                restoreScrollSnap();
            };

            track.addEventListener('scrollend', onScrollEnd, { once: true });
            programmaticScrollTimer = setTimeout(onScrollEnd, 1200);
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
        const activeColor = HERO_SLIDE_DOT_COLORS[activeIndex % HERO_SLIDE_DOT_COLORS.length] || 'bg-[#EA580C] dark:bg-amber-400';
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

            const isRTL = document.documentElement.dir === 'rtl' || getComputedStyle(carousel).direction === 'rtl';

            const updateScroll = () => {
                if (!isDown) return;
                const walk = currentX - startX;
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
                        hasMovedSignificantly = false;
                    }, 180);
                } else {
                    window.isCarouselDragging = false;
                    hasMovedSignificantly = false;
                }
            };

            carousel.addEventListener('mousedown', onMouseDown);
            carousel.addEventListener('dragstart', (e) => e.preventDefault());

            // Capture-phase click interceptor on carousel so dragged cards never trigger navigation
            carousel.addEventListener('click', (e) => {
                if (window.isCarouselDragging || hasMovedSignificantly) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
            }, true);

            // Touch Swipe Tracking for Mobile (Ensures natural scroll without opening cards)
            let touchStartX = 0;
            let touchStartY = 0;
            let isTouchSwiping = false;

            carousel.addEventListener('touchstart', (e) => {
                if (!e.touches || !e.touches[0]) return;
                touchStartX = e.touches[0].clientX;
                touchStartY = e.touches[0].clientY;
                isTouchSwiping = false;
            }, { passive: true });

            carousel.addEventListener('touchmove', (e) => {
                if (!e.touches || !e.touches[0]) return;
                const dx = Math.abs(e.touches[0].clientX - touchStartX);
                const dy = Math.abs(e.touches[0].clientY - touchStartY);
                if (dx > 7 && dx > dy) {
                    isTouchSwiping = true;
                    window.isCarouselDragging = true;
                    hasMovedSignificantly = true;
                }
            }, { passive: true });

            carousel.addEventListener('touchend', () => {
                if (isTouchSwiping) {
                    setTimeout(() => {
                        window.isCarouselDragging = false;
                        isTouchSwiping = false;
                        hasMovedSignificantly = false;
                    }, 180);
                } else {
                    window.isCarouselDragging = false;
                    hasMovedSignificantly = false;
                }
            }, { passive: true });
        });
    }

    window.isCategoryDragging = false;

    window.scrollCategoryExploreTrack = function(direction) {
        const track = document.getElementById('category-explore-track');
        if (!track) return;
        const scrollDelta = direction === 'next' ? -220 : 220;
        track.scrollBy({ left: scrollDelta, behavior: 'smooth' });
    };

    function updateCategoryTrackAffordance() {
        const track = document.getElementById('category-explore-track');
        const leftBtn = document.getElementById('cat-scroll-left-btn');
        const rightBtn = document.getElementById('cat-scroll-right-btn');
        if (!track) return;

        const hasOverflow = track.scrollWidth > track.clientWidth + 8;
        if (!hasOverflow) {
            if (leftBtn) leftBtn.disabled = true;
            if (rightBtn) rightBtn.disabled = true;
            return;
        }

        const scrollAmount = Math.abs(track.scrollLeft);
        const maxScroll = track.scrollWidth - track.clientWidth;
        const isAtStart = scrollAmount <= 6; // Right edge in RTL
        const isAtEnd = scrollAmount >= maxScroll - 6; // Left edge in RTL

        if (rightBtn) rightBtn.disabled = isAtStart;
        if (leftBtn) leftBtn.disabled = isAtEnd;
    }

    // Ultra-smooth Mouse Drag & Mobile Touch Controller for Category Explore Track (RTL-accurate, Momentum, No Auto-play)
    function initCategoryExploreDrag() {
        const track = document.getElementById('category-explore-track');
        if (!track || track.dataset.dragReady === 'true') return;
        track.dataset.dragReady = 'true';

        let isDown = false;
        let startX = 0;
        let currentX = 0;
        let scrollStart = 0;
        let lastMouseX = 0;
        let lastTime = 0;
        let velocityX = 0;
        let rafId = null;
        let hasMovedSignificantly = false;

        const isRTL = document.documentElement.dir === 'rtl' || getComputedStyle(track).direction === 'rtl';

        const updateScroll = () => {
            if (!isDown) return;
            const walk = currentX - startX;
            track.scrollLeft = scrollStart - walk;
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
            scrollStart = track.scrollLeft;
            velocityX = 0;

            track.classList.add('is-dragging');
            track.style.scrollBehavior = 'auto';

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
                window.isCategoryDragging = true;
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

            window.removeEventListener('mousemove', onMouseMove);
            window.removeEventListener('mouseup', onMouseUp);

            // Natural momentum glide based on release velocity
            let momentum = velocityX * 220;
            const maxGlide = track.clientWidth * 0.85;
            if (momentum > maxGlide) momentum = maxGlide;
            if (momentum < -maxGlide) momentum = -maxGlide;

            if (Math.abs(momentum) > 10) {
                const targetLeft = track.scrollLeft - momentum;
                track.scrollTo({
                    left: targetLeft,
                    behavior: 'smooth'
                });
            }

            // Restore smooth scrolling behavior
            setTimeout(() => {
                track.classList.remove('is-dragging');
                track.style.scrollBehavior = 'smooth';
                updateCategoryTrackAffordance();
            }, 50);

            // Prevent card navigation if user dragged
            if (hasMovedSignificantly) {
                setTimeout(() => {
                    window.isCategoryDragging = false;
                    hasMovedSignificantly = false;
                }, 180);
            } else {
                window.isCategoryDragging = false;
                hasMovedSignificantly = false;
            }
        };

        track.addEventListener('mousedown', onMouseDown);
        track.addEventListener('dragstart', (e) => e.preventDefault());

        // Capture-phase click interceptor on track to guarantee dragged/swiped items never trigger URL navigation
        track.addEventListener('click', (e) => {
            if (window.isCategoryDragging || hasMovedSignificantly) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                return false;
            }
        }, true);

        // Native touch support tracking for mobile devices
        let touchStartX = 0;
        let touchStartY = 0;
        let isTouchSwiping = false;

        track.addEventListener('touchstart', (e) => {
            if (!e.touches || !e.touches[0]) return;
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
            isTouchSwiping = false;
        }, { passive: true });

        track.addEventListener('touchmove', (e) => {
            if (!e.touches || !e.touches[0]) return;
            const dx = Math.abs(e.touches[0].clientX - touchStartX);
            const dy = Math.abs(e.touches[0].clientY - touchStartY);
            if (dx > 6 && dx > dy) {
                isTouchSwiping = true;
                window.isCategoryDragging = true;
                hasMovedSignificantly = true;
            }
        }, { passive: true });

        track.addEventListener('touchend', () => {
            if (isTouchSwiping) {
                setTimeout(() => {
                    window.isCategoryDragging = false;
                    hasMovedSignificantly = false;
                    isTouchSwiping = false;
                }, 180);
            } else {
                window.isCategoryDragging = false;
                hasMovedSignificantly = false;
            }
            setTimeout(updateCategoryTrackAffordance, 100);
        }, { passive: true });

        track.addEventListener('touchcancel', () => {
            window.isCategoryDragging = false;
            hasMovedSignificantly = false;
            isTouchSwiping = false;
        }, { passive: true });

        track.addEventListener('scroll', updateCategoryTrackAffordance, { passive: true });
        window.addEventListener('resize', updateCategoryTrackAffordance, { passive: true });

        // Initial check for overflow affordance
        setTimeout(updateCategoryTrackAffordance, 100);
    }

    function navigateToProperty(url) {
        if (window.isCarouselDragging) return;
        if (url) {
            window.location.href = url;
        }
    }

    // Helper to format property specs strictly: "x خوابه • تا X نفر • x متر"
    function formatPropertySpecs(item) {
        const parts = [];
        const rooms = parseInt(item.rooms);
        if (!isNaN(rooms) && rooms > 0) parts.push(`${toPersianNum(rooms)} خوابه`);
        const guests = parseInt(item.maxGuests);
        if (!isNaN(guests) && guests > 0) parts.push(`تا ${toPersianNum(guests)} نفر`);
        const area = parseInt(item.area);
        if (!isNaN(area) && area > 0) parts.push(`${toPersianNum(area)} متر`);
        return parts.length > 0 ? parts.join(' • ') : 'اقامتگاه';
    }

    // Standard Carousel Card Builder (3 in Desktop, 2 in Tablet, 1 Full + 1 Peek in Mobile)
    function buildVillaCardHTML(item) {
        const isFav = state.favorites.includes(item.id);
        const targetUrl = item.url || ('{{ url("/properties") }}/' + item.id);

        return `
        <article onclick="navigateToProperty('${targetUrl}')" class="carousel-card-item group cursor-pointer select-none shrink-0 snap-start bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800/80 hover:border-zinc-300 dark:hover:border-zinc-700 rounded-2xl p-2.5 sm:p-3 transition-all duration-200 shadow-[0_2px_8px_rgba(0,0,0,0.03)] dark:shadow-[0_2px_8px_rgba(0,0,0,0.2)] flex flex-col justify-between h-full">
            <div>
                <div class="relative aspect-[4/3] w-full rounded-xl overflow-hidden bg-[#F7F8F9] dark:bg-zinc-800 mb-2.5 sm:mb-3 border border-[#ECECEE]/60 dark:border-zinc-800/60">
                    <img src="${item.image}" alt="${item.title}" onerror="this.onerror=null; this.src='{{ asset('images/property-placeholder.svg') }}';" class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-300" loading="lazy">
                    
                    ${item.isSpecial ? `<span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-lg bg-amber-500 text-white text-[10px] sm:text-[11px] font-bold shadow-sm font-sans">ویژه</span>` : ''}
                    
                    <button type="button" onclick="toggleFavorite(${item.id}, event)" aria-label="ذخیره" class="absolute top-2.5 left-2.5 w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/90 dark:bg-zinc-900/90 flex items-center justify-center active:scale-90 transition-transform shadow-sm">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 ${isFav ? 'text-rose-500 fill-rose-500' : 'text-[#18181B] dark:text-zinc-200'}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                        </svg>
                    </button>

                    ${item.instant ? `
                    <span class="absolute bottom-2 right-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/95 dark:bg-zinc-900/95 text-amber-600 dark:text-amber-400 text-[10px] font-bold shadow-xs backdrop-blur-xs font-sans">
                        <span class="text-xs">⚡</span>
                        <span>رزرو آنی</span>
                    </span>` : ''}
                </div>

                <div class="flex items-start justify-between gap-1.5 px-0.5">
                    <h3 class="card-title-fixed-height text-xs sm:text-sm font-bold text-[#18181B] dark:text-zinc-100 flex-1 leading-[1.375rem] overflow-hidden">${item.title}</h3>
                    ${item.rating ? `
                    <span class="inline-flex items-center gap-1 text-[11px] sm:text-xs font-bold text-[#18181B] dark:text-zinc-200 shrink-0 mt-0.5">
                        <span class="text-amber-400 dark:text-amber-400 text-xs">★</span>
                        <span>${item.rating}</span>
                    </span>` : ''}
                </div>

                <div class="flex items-center justify-between gap-1.5 mt-1.5 px-0.5">
                    <p class="text-[11px] sm:text-xs text-zinc-500 dark:text-zinc-400 truncate font-sans">${formatPropertySpecs(item)}</p>
                    ${item.city ? `<span class="text-[11px] sm:text-xs text-zinc-500 dark:text-zinc-400 shrink-0 font-sans">${item.city}</span>` : ''}
                </div>
            </div>

            <div class="mt-2.5 pt-1 flex items-end justify-between gap-1.5 px-0.5 font-sans">
                <div>
                    ${(item.oldPrice && item.oldPrice > item.price) ? `
                    <span class="block text-[11px] sm:text-xs text-zinc-400 dark:text-zinc-500 line-through font-normal leading-tight font-sans">
                        ${formatToman(item.oldPrice)}
                    </span>` : ''}
                    <div class="text-sm sm:text-base font-black ${item.discountPercent || (item.oldPrice && item.oldPrice > item.price) ? 'text-[#007A5E] dark:text-emerald-400' : 'text-[#18181B] dark:text-zinc-100'} leading-tight font-sans mt-0.5">
                        ${(item.price && item.price > 0) 
                            ? `${formatToman(item.price)} <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400 font-sans">${currencyLabel}</span> <span class="text-[10px] sm:text-[11px] font-normal text-zinc-400 dark:text-zinc-500 font-sans">/ شب</span>` 
                            : '<span class="text-xs font-medium text-zinc-500">استعلام قیمت</span>'}
                    </div>
                </div>
                ${(item.oldPrice && item.oldPrice > item.price) ? `
                <span class="inline-flex items-center px-2 py-1 rounded-lg bg-[#ECFDF5] dark:bg-emerald-950/60 text-[#007A5E] dark:text-emerald-400 text-xs font-black font-sans leading-none shrink-0 mb-0.5">
                    ${toPersianNum(Math.round(((item.oldPrice - item.price) / item.oldPrice) * 100))}٪ تخفیف
                </span>` : (item.discountPercent ? `
                <span class="inline-flex items-center px-2 py-1 rounded-lg bg-[#ECFDF5] dark:bg-emerald-950/60 text-[#007A5E] dark:text-emerald-400 text-xs font-black font-sans leading-none shrink-0 mb-0.5">
                    ${toPersianNum(item.discountPercent)}٪ تخفیف
                </span>` : '')}
            </div>
        </article>`;
    }

    // Clean Card Builder for Full-Width "Last Minute Deals"
    function buildLastMinuteCardHTML(item) {
        const isFav = state.favorites.includes(item.id);
        const targetUrl = item.url || ('{{ url("/properties") }}/' + item.id);
        const discountVal = (item.oldPrice && item.oldPrice > item.price)
            ? Math.round(((item.oldPrice - item.price) / item.oldPrice) * 100)
            : (item.discountPercent || 0);

        return `
        <article onclick="navigateToProperty('${targetUrl}')" class="carousel-card-item group cursor-pointer select-none shrink-0 snap-start bg-white dark:bg-zinc-900 border border-emerald-100/80 dark:border-zinc-800 hover:border-[#007A5E] dark:hover:border-emerald-500 rounded-2xl p-2.5 sm:p-3 transition-colors duration-200 flex flex-col justify-between h-full shadow-lg">
            <div>
                <div class="relative aspect-[4/3] w-full rounded-xl overflow-hidden bg-[#F7F8F9] dark:bg-zinc-800 mb-2.5 sm:mb-3 border border-[#ECECEE]/60 dark:border-zinc-800/60">
                    <img src="${item.image}" alt="${item.title}" onerror="this.onerror=null; this.src='{{ asset('images/property-placeholder.svg') }}';" class="w-full h-full object-cover" loading="lazy">
                    
                    ${discountVal > 0 ? `
                    <span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-lg bg-rose-500 text-white text-[10px] sm:text-[11px] font-black shadow-xs font-sans">
                        ${toPersianNum(discountVal)}٪ تخفیف
                    </span>` : ''}

                    <button type="button" onclick="toggleFavorite(${item.id}, event)" aria-label="ذخیره" class="absolute top-2.5 left-2.5 w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/90 dark:bg-zinc-900/90 flex items-center justify-center active:scale-90 transition-transform shadow-sm">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 ${isFav ? 'text-rose-500 fill-rose-500' : 'text-[#18181B] dark:text-zinc-200'}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                        </svg>
                    </button>

                    ${item.instant ? `
                    <span class="absolute bottom-2 right-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/95 dark:bg-zinc-900/95 text-amber-600 dark:text-amber-400 text-[10px] font-bold shadow-xs backdrop-blur-xs font-sans">
                        <span class="text-xs">⚡</span>
                        <span>رزرو آنی</span>
                    </span>` : ''}
                </div>

                <div class="flex items-start justify-between gap-1.5 px-0.5">
                    <h3 class="card-title-fixed-height text-xs sm:text-sm font-bold text-[#18181B] dark:text-zinc-100 flex-1 leading-[1.375rem] overflow-hidden">${item.title}</h3>
                    ${item.rating ? `
                    <span class="inline-flex items-center gap-1 text-[11px] sm:text-xs font-bold text-[#18181B] dark:text-zinc-200 shrink-0 mt-0.5">
                        <span class="text-amber-400 dark:text-amber-400 text-xs">★</span>
                        <span>${item.rating}</span>
                    </span>` : ''}
                </div>

                <div class="flex items-center justify-between gap-1.5 mt-1.5 px-0.5">
                    <p class="text-[11px] sm:text-xs text-zinc-500 dark:text-zinc-400 truncate font-sans">${formatPropertySpecs(item)}</p>
                    ${item.city ? `<span class="text-[11px] sm:text-xs text-zinc-500 dark:text-zinc-400 shrink-0 font-sans">${item.city}</span>` : ''}
                </div>
            </div>

            <div class="mt-2.5 pt-1.5 border-t border-[#ECECEE]/70 dark:border-zinc-800/70 flex items-end justify-between gap-1.5 px-0.5 font-sans">
                <div>
                    ${(item.oldPrice && item.oldPrice > item.price) ? `
                    <span class="block text-[11px] sm:text-xs text-zinc-400 dark:text-zinc-500 line-through font-normal leading-tight font-sans">
                        ${formatToman(item.oldPrice)}
                    </span>` : ''}
                    <div class="text-sm sm:text-base font-black text-[#18181B] dark:text-zinc-100 leading-tight font-sans mt-0.5">
                        ${(item.price && item.price > 0) 
                            ? `${formatToman(item.price)} <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400 font-sans">${currencyLabel}</span> <span class="text-[10px] sm:text-[11px] font-normal text-zinc-400 dark:text-zinc-500 font-sans">/ شب</span>` 
                            : '<span class="text-xs font-medium text-zinc-500">استعلام قیمت</span>'}
                    </div>
                </div>
                ${discountVal > 0 ? `
                <span class="inline-flex items-center px-2 py-1 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 text-xs font-black font-sans leading-none shrink-0 mb-0.5">
                    ${toPersianNum(discountVal)}٪ تخفیف
                </span>` : ''}
            </div>
        </article>`;
    }

    function renderListings() {
        const beforeWrapper = document.getElementById('sections-before-slider');
        const afterWrapper = document.getElementById('sections-after-slider');
        const heroBanner = document.getElementById('hero-banner-section');
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

        const trustSection = document.getElementById('trust-guarantees-section');

        if (filtered.length === 0) {
            if (beforeWrapper) beforeWrapper.innerHTML = '';
            if (afterWrapper) afterWrapper.innerHTML = '';
            if (heroBanner) heroBanner.classList.add('hidden');
            if (trustSection) trustSection.classList.add('hidden');
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
            if (trustSection) trustSection.classList.remove('hidden');

            // When keyword search is active, render a dedicated search results grid so all matches are immediately visible!
            if (state.keyword) {
                const searchResultsHtml = `
                    <section class="w-full max-w-[1360px] mx-auto px-4 sm:px-8 pt-2 font-sans">
                        <div class="flex items-center justify-between mb-5 sm:mb-7">
                            <div>
                                <h2 class="text-base sm:text-lg font-extrabold text-[#18181B] dark:text-zinc-100 font-sans">
                                    نتایج جستجو برای «${toPersianNum(state.keyword)}»
                                </h2>
                                <p class="text-xs text-zinc-400 mt-2 sm:mt-2.5 font-sans">${toPersianNum(filtered.length)} اقامتگاه یافت شد</p>
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
                if (beforeWrapper) beforeWrapper.innerHTML = searchResultsHtml;
                if (afterWrapper) afterWrapper.innerHTML = '';
                if (heroBanner) heroBanner.classList.add('hidden');
                if (trustSection) trustSection.classList.add('hidden');
                initCarouselDrag();
                return;
            }

            const beforeSectionsHtml = [];
            const afterSectionsHtml = [];

            SECTIONS_META.forEach(sec => {
                let secItems = filtered.filter(item => {
                    if (Array.isArray(item.sections)) {
                        return item.sections.includes(sec.key);
                    }
                    return item.section === sec.key;
                });

                // قانون قطعی: وقتی تخفیفی نیست سکشن تخفیف لحظه آخری به هیچ وجه نباید نمایش داده شود
                if (sec.key === 'lastminute') {
                    secItems = secItems.filter(item => (item.discountPercent && item.discountPercent > 0) || (item.oldPrice && item.oldPrice > item.price));
                    secItems = secItems.slice(0, 24);
                    if (secItems.length === 0) return;

                    const lmHtml = `
                    <section class="w-full bg-[#007A5E] dark:bg-[#064e3b] py-8 sm:py-12 border-y border-[#005F48]/60 dark:border-emerald-800/40 text-white relative overflow-hidden">
                        <!-- Subtle Travel Doodles Watermark (Matching Brand Palette) -->
                        <div class="absolute inset-0 opacity-[0.05] pointer-events-none text-white" style="background-image: radial-gradient(circle, currentColor 1.5px, transparent 1.5px), radial-gradient(circle, currentColor 1px, transparent 1px); background-size: 28px 28px; background-position: 0 0, 14px 14px;"></div>

                        <div class="relative z-10 max-w-[1360px] mx-auto px-4 sm:px-8">
                            <div class="mb-5 sm:mb-7 space-y-1 sm:space-y-1.5">
                                <div class="flex items-center justify-between gap-2 sm:gap-4">
                                    <!-- Title (Right side, white & bold) -->
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="text-xl shrink-0">🔥</span>
                                        <h2 class="text-lg sm:text-xl font-black text-white tracking-tight truncate">${sec.title}</h2>
                                    </div>

                                    <!-- Digital Flip-Clock Timer & Controls (Left side) -->
                                    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                                        <!-- Flip clock style dark glass timer -->
                                        <div class="flex items-center gap-1.5 bg-black/35 backdrop-blur-xs border border-white/20 text-white px-2.5 sm:px-3 py-1.5 rounded-2xl shadow-inner font-sans shrink-0">
                                            <span class="text-amber-300 text-xs hidden sm:inline">⏳</span>
                                            <div class="flex items-center gap-1 text-xs sm:text-sm font-black tracking-wider" dir="ltr">
                                                <span id="lm-timer-h" class="tabular-nums inline-block bg-white/15 text-white px-1.5 py-0.5 rounded-lg w-[26px] sm:w-[28px] text-center font-sans">۰۴</span>
                                                <span class="text-emerald-200 font-bold">:</span>
                                                <span id="lm-timer-m" class="tabular-nums inline-block bg-white/15 text-white px-1.5 py-0.5 rounded-lg w-[26px] sm:w-[28px] text-center font-sans">۳۸</span>
                                                <span class="text-emerald-200 font-bold">:</span>
                                                <span id="lm-timer-s" class="tabular-nums inline-block bg-white/15 text-amber-300 px-1.5 py-0.5 rounded-lg w-[26px] sm:w-[28px] text-center font-sans">۱۹</span>
                                            </div>
                                        </div>

                                        <!-- Carousel nav buttons -->
                                        <div class="hidden md:flex items-center gap-1.5 shrink-0">
                                            <button type="button" onclick="scrollCarousel('${sec.key}', -1)" aria-label="قبلی" class="w-9 h-9 rounded-xl bg-white/15 hover:bg-white/25 active:scale-95 text-white backdrop-blur-xs border border-white/20 flex items-center justify-center transition-all shadow-xs">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                            </button>
                                            <button type="button" onclick="scrollCarousel('${sec.key}', 1)" aria-label="بعدی" class="w-9 h-9 rounded-xl bg-white/15 hover:bg-white/25 active:scale-95 text-white backdrop-blur-xs border border-white/20 flex items-center justify-center transition-all shadow-xs">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Subtitle on its own clean line -->
                                <p class="text-xs sm:text-sm text-emerald-100/90 font-medium font-sans">${sec.subtitle || 'برای امروز و فردا با تخفیف رزرو کن'}</p>
                            </div>

                            <div id="carousel-${sec.key}" class="section-carousel flex items-stretch gap-3 sm:gap-4 lg:gap-5 overflow-x-auto scroll-smooth snap-x snap-mandatory no-scrollbar pb-2">
                                ${secItems.map(buildLastMinuteCardHTML).join('')}
                            </div>
                        </div>
                    </section>`;
                    beforeSectionsHtml.push(lmHtml);
                    return;
                }

                // اقامتگاه‌های خاص: بررسی مستقیم بر اساس تیک «آگهی ویژه» (is_special)
                if (sec.key === 'unique') {
                    secItems = secItems.filter(item => item.isSpecial);
                }

                // قانون سقف ۲۴ کارت در هر سکشن
                secItems = secItems.slice(0, 24);

                // سایر سکشن‌های استاندارد (کاروسل ۳تایی در دسکتاپ و ۲تایی کامل + بعدی در موبایل)
                if (secItems.length === 0) return;

                const secHtml = `
                <section class="w-full max-w-[1360px] mx-auto px-4 sm:px-8">
                    <div class="flex items-end justify-between gap-2 mb-5 sm:mb-7">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#007A5E]"></span>
                                <h2 class="text-sm sm:text-base lg:text-lg font-extrabold text-[#18181B] dark:text-zinc-100 tracking-tight">${sec.title}</h2>
                            </div>
                            <p class="text-[11px] sm:text-xs text-zinc-400 mt-2 sm:mt-2.5">${sec.subtitle}</p>
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
                    <div id="carousel-${sec.key}" class="section-carousel flex items-stretch gap-3 sm:gap-4 lg:gap-5 overflow-x-auto scroll-smooth snap-x snap-mandatory no-scrollbar pb-2">
                        ${secItems.map(buildVillaCardHTML).join('')}
                    </div>
                </section>`;

                if (sec.key === 'newest') {
                    afterSectionsHtml.push(secHtml);
                } else {
                    beforeSectionsHtml.push(secHtml);
                }
            });

            if (beforeWrapper) beforeWrapper.innerHTML = beforeSectionsHtml.join('');
            if (afterWrapper) afterWrapper.innerHTML = afterSectionsHtml.join('');
            if (heroBanner) heroBanner.classList.remove('hidden');

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
        const hPill = document.getElementById('hero-mobile-pill-dest');
        const mSummaryDest = document.getElementById('m-summary-dest-val');
        if (state.keyword) {
            const kwText = `جستجو: ${toPersianNum(state.keyword)}`;
            if (dLabel) dLabel.textContent = kwText;
            if (mPill) mPill.textContent = kwText;
            if (hPill) hPill.textContent = kwText;
            if (mSummaryDest) mSummaryDest.textContent = kwText;
        } else {
            if (dLabel) dLabel.textContent = 'همه مقاصد';
            if (mPill) mPill.textContent = 'جستجو بر اساس مقصد، عنوان یا کد اقامتگاه...';
            if (hPill) hPill.textContent = 'جستجو بر اساس مقصد، عنوان یا کد اقامتگاه...';
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
        const hPill = document.getElementById('hero-mobile-pill-dest');
        if (dLabel) dLabel.textContent = state.cityLabel || 'همه مقاصد';
        const clearDestText = state.city === 'all' ? 'جستجو بر اساس مقصد، عنوان یا کد اقامتگاه...' : `مقصد: ${state.cityLabel}`;
        if (mPill) mPill.textContent = clearDestText;
        if (hPill) hPill.textContent = clearDestText;
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
        const hPill = document.getElementById('hero-mobile-pill-dest');
        const dHint = document.getElementById('desktop-dest-selected-hint');
        const mSummaryDest = document.getElementById('m-summary-dest-val');
        const destTitle = destKey === 'all' ? 'جستجو در استان و شهر مقصد...' : `مقصد: ${label}`;
        if (dLabel) dLabel.textContent = label;
        if (mPill) mPill.textContent = destTitle;
        if (hPill) hPill.textContent = destTitle;
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
        const hSub = document.getElementById('hero-mobile-pill-sub');
        const mSummaryGuests = document.getElementById('m-summary-guests-val');
        const guestSubText = `${toPersianNum(state.startDay)} تا ${toPersianNum(state.endDay)} ${currentJalaliMonthName} • ${pCount} نفر`;
        if (dGuests) dGuests.textContent = `${pCount} نفر`;
        if (mGuests) mGuests.textContent = pCount;
        if (mSub) mSub.textContent = guestSubText;
        if (hSub) hSub.textContent = guestSubText;
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
        const hasStart = (state.startDay !== null && state.startMonthIndex !== null);
        const hasEnd = (state.endDay !== null && state.endMonthIndex !== null);

        let absStart = -1;
        let absEnd = -1;
        let nights = 0;
        let pNights = '۰';
        let startStr = '';
        let endStr = '';
        let summary = '';
        let summaryShort = '';

        const startMonth = (hasStart && CALENDAR_MONTHS[state.startMonthIndex]) ? CALENDAR_MONTHS[state.startMonthIndex] : (CALENDAR_MONTHS[0] || {});
        const endMonth = (hasEnd && CALENDAR_MONTHS[state.endMonthIndex]) ? CALENDAR_MONTHS[state.endMonthIndex] : (CALENDAR_MONTHS[0] || {});

        if (hasStart && hasEnd) {
            absStart = getAbsoluteDay(state.startMonthIndex, state.startDay);
            absEnd = getAbsoluteDay(state.endMonthIndex, state.endDay);
            nights = Math.max(1, absEnd - absStart);
            pNights = toPersianNum(nights);
            startStr = `${toPersianNum(state.startDay)} ${startMonth.name}`;
            endStr = `${toPersianNum(state.endDay)} ${endMonth.name}`;
            summary = (state.startMonthIndex === state.endMonthIndex)
                ? `${toPersianNum(state.startDay)} تا ${toPersianNum(state.endDay)} ${startMonth.name} (${pNights} شب)`
                : `${startStr} تا ${endStr} (${pNights} شب)`;
            summaryShort = (state.startMonthIndex === state.endMonthIndex)
                ? `${toPersianNum(state.startDay)} تا ${toPersianNum(state.endDay)} ${startMonth.name}`
                : `${startStr} تا ${endStr}`;
        } else if (hasStart) {
            absStart = getAbsoluteDay(state.startMonthIndex, state.startDay);
            startStr = `${toPersianNum(state.startDay)} ${startMonth.name}`;
            summary = `ورود: ${startStr} (انتخاب خروج)`;
            summaryShort = `ورود: ${startStr}`;
            pNights = '-';
        } else {
            summary = 'انتخاب تاریخ ورود و خروج';
            summaryShort = 'انتخاب تاریخ';
            pNights = '-';
        }

        const dLabel = document.getElementById('desktop-date-label');
        const mSummary = document.getElementById('mobile-calendar-summary');
        const mSummaryDate = document.getElementById('m-summary-date-val');
        const mSub = document.getElementById('mobile-pill-sub');
        const hSub = document.getElementById('hero-mobile-pill-sub');
        const dNights = document.getElementById('desktop-nights-count');
        const mNights = document.getElementById('mobile-nights-count');

        const dateSubText = `${summaryShort} • ${toPersianNum(state.guests)} نفر`;
        if (dLabel) dLabel.textContent = summary;
        if (mSummary) mSummary.textContent = summary;
        if (mSummaryDate) mSummaryDate.textContent = summaryShort;
        if (mSub) mSub.textContent = dateSubText;
        if (hSub) hSub.textContent = dateSubText;
        if (dNights) dNights.textContent = pNights;
        if (mNights) mNights.textContent = pNights;

        // Mobile dynamic step banner (matching show.blade.php UX)
        const stepIcon = document.getElementById('mobile-date-step-icon');
        const stepText = document.getElementById('mobile-date-step-text');
        if (stepIcon && stepText) {
            if (hasStart && hasEnd) {
                stepIcon.textContent = '✨';
                stepText.textContent = `${summaryShort} (${pNights} شب اقامت)`;
            } else if (hasStart) {
                stepIcon.textContent = '🟢';
                stepText.textContent = `ورود: ${startStr} — لطفاً تاریخ خروج را لمس کنید`;
            } else {
                stepIcon.textContent = '📍';
                stepText.textContent = 'تاریخ ورود و خروج را با لمس روزها انتخاب کنید';
            }
        }

        // Desktop checkin & checkout tabs UI (remains supported for desktop view)
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

        if (inVal) inVal.textContent = startStr || 'انتخاب ورود';
        if (outVal) outVal.textContent = endStr || 'انتخاب خروج';
        if (inStatus) inStatus.textContent = 'تاریخ ورود';
        if (outStatus) outStatus.textContent = 'تاریخ خروج';

        const activeCls = 'p-3 rounded-2xl border-2 border-[#007A5E] dark:border-emerald-500 bg-white dark:bg-zinc-800/90 text-right transition-all flex flex-col justify-between min-h-[72px]';
        const inactiveCls = 'p-3 rounded-2xl border border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9] dark:bg-zinc-900/60 text-right hover:border-zinc-300 dark:hover:border-zinc-700 transition-all flex flex-col justify-between min-h-[72px]';

        if (inBtn && outBtn) {
            if (!hasEnd || dateSelectionStep === 'start') {
                inBtn.className = activeCls;
                outBtn.className = inactiveCls;
                if (inDot) inDot.classList.remove('hidden');
                if (outDot) outDot.classList.add('hidden');
                if (inBadge) inBadge.classList.remove('hidden');
                if (outBadge) outBadge.classList.add('hidden');
                if (inStatus) inStatus.className = 'text-[11px] font-bold text-[#007A5E] dark:text-emerald-400';
                if (outStatus) outStatus.className = 'text-[11px] font-medium text-zinc-500 dark:text-zinc-400';
            } else {
                inBtn.className = inactiveCls;
                outBtn.className = activeCls;
                if (inDot) inDot.classList.add('hidden');
                if (outDot) outDot.classList.remove('hidden');
                if (inBadge) inBadge.classList.add('hidden');
                if (outBadge) outBadge.classList.remove('hidden');
                if (inStatus) inStatus.className = 'text-[11px] font-medium text-zinc-500 dark:text-zinc-400';
                if (outStatus) outStatus.className = 'text-[11px] font-bold text-[#007A5E] dark:text-emerald-400';
            }
        }

        // Sync quick stay duration chips active state
        document.querySelectorAll('.quick-stay-chip').forEach(chip => {
            const chipNights = parseInt(chip.getAttribute('data-nights'), 10);
            if (hasStart && hasEnd && chipNights === nights) {
                chip.className = 'quick-stay-chip px-3 py-1.5 rounded-xl border border-[#007A5E] bg-[#ECFDF5] dark:bg-emerald-950/40 text-[#007A5E] dark:text-emerald-400 text-[11px] font-black shrink-0 active:scale-95 transition-all font-sans';
            } else {
                chip.className = 'quick-stay-chip px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700/80 bg-[#F7F8F9] dark:bg-zinc-800/80 text-zinc-700 dark:text-zinc-300 text-[11px] font-bold shrink-0 hover:border-[#007A5E] hover:text-[#007A5E] active:scale-95 transition-all font-sans';
            }
        });
    }

    function clearDateSelection() {
        state.startMonthIndex = 0;
        state.startDay = currentJalaliDay;
        state.endMonthIndex = null;
        state.endDay = null;
        dateSelectionStep = 'end';
        updateDateLabelsAndSummary();
        renderJalaliCalendars();
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
        dateSelectionStep = 'start';
        activeCalendarMonthIndex = 0;
        updateMonthNavigationUI();
        updateDateLabelsAndSummary();
        renderJalaliCalendars();

        setTimeout(() => {
            setMobileSearchSection('none');
        }, 280);
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
        const hPill = document.getElementById('hero-mobile-pill-dest');
        const dHint = document.getElementById('desktop-dest-selected-hint');
        const mSummaryDest = document.getElementById('m-summary-dest-val');
        if (dLabel) dLabel.textContent = 'همه مقاصد';
        if (mPill) mPill.textContent = 'جستجو بر اساس مقصد، عنوان یا کد اقامتگاه...';
        if (hPill) hPill.textContent = 'جستجو بر اساس مقصد، عنوان یا کد اقامتگاه...';
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

        // Fallback: If user selected check-in but didn't pick check-out, set +1 night
        if (state.startDay !== null && state.endDay === null) {
            const nextDay = getDateFromAbsoluteDay(getAbsoluteDay(state.startMonthIndex, state.startDay) + 1);
            state.endMonthIndex = nextDay.monthIndex;
            state.endDay = nextDay.day;
            updateDateLabelsAndSummary();
        }

        const dInput = document.getElementById('dest-search-input');
        const mInput = document.getElementById('mobile-dest-search-input');
        const keywordVal = (dInput && dInput.value.trim()) || (mInput && mInput.value.trim()) || '';
        if (keywordVal) {
            state.keyword = keywordVal;
            state.city = 'all';
            state.province = 'all';
            const dLabel = document.getElementById('desktop-dest-label');
            const mPill = document.getElementById('mobile-pill-dest');
            const hPill = document.getElementById('hero-mobile-pill-dest');
            const kwSearchText = `جستجو: ${toPersianNum(state.keyword)}`;
            if (dLabel) dLabel.textContent = kwSearchText;
            if (mPill) mPill.textContent = kwSearchText;
            if (hPill) hPill.textContent = kwSearchText;
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

        const hasStart = (state.startDay !== null && state.startMonthIndex !== null);
        const hasEnd = (state.endDay !== null && state.endMonthIndex !== null);
        const absStart = hasStart ? getAbsoluteDay(state.startMonthIndex, state.startDay) : -1;
        const absEnd = hasEnd ? getAbsoluteDay(state.endMonthIndex, state.endDay) : -1;

        ['desktop-jalali-grid', 'mobile-jalali-grid'].forEach(gridId => {
            const grid = document.getElementById(gridId);
            if (!grid) return;
            let html = '';

            // Add blank cells for correct Jalali weekday offset alignment
            for (let b = 0; b < monthFirstDay; b++) {
                html += '<div class="h-10 sm:h-11 w-full rounded-lg opacity-0 pointer-events-none"></div>';
            }

            for (let d = 1; d <= monthDaysCount; d++) {
                const currentDayAbs = getAbsoluteDay(activeCalendarMonthIndex, d);
                const isPast = isFirstMonth && (d < currentJalaliDay);
                const isCheckIn = (hasStart && currentDayAbs === absStart);
                const isCheckOut = (hasEnd && currentDayAbs === absEnd);
                const inRange = (hasStart && hasEnd && currentDayAbs > absStart && currentDayAbs < absEnd);

                if (isPast) {
                    html += `
                        <button type="button" disabled
                                title="امکان انتخاب تاریخ در گذشته وجود ندارد"
                                class="h-10 sm:h-11 w-full rounded-lg sm:rounded-xl text-xs sm:text-sm font-medium bg-[#F8F9FA] dark:bg-zinc-800/40 border border-[#F1F3F5] dark:border-zinc-800/60 text-[#ADB5BD] dark:text-zinc-600 select-none cursor-not-allowed flex items-center justify-center font-sans">
                            ${toPersianNum(d)}
                        </button>
                    `;
                } else if (isCheckIn && isCheckOut) {
                    html += `
                        <button type="button" onclick="selectCalendarDay(${d})"
                                title="تاریخ ورود و خروج"
                                class="h-10 sm:h-11 w-full rounded-lg sm:rounded-xl font-black text-xs sm:text-sm bg-[#007A5E] dark:bg-emerald-600 text-white shadow-md flex items-center justify-center transition-transform active:scale-95 font-sans z-10">
                            ${toPersianNum(d)}
                        </button>
                    `;
                } else if (isCheckIn) {
                    html += `
                        <button type="button" onclick="selectCalendarDay(${d})"
                                title="تاریخ ورود"
                                class="h-10 sm:h-11 w-full rounded-lg sm:rounded-xl font-black text-xs sm:text-sm bg-[#007A5E] dark:bg-emerald-600 text-white shadow-md flex items-center justify-center transition-transform active:scale-95 font-sans z-10">
                            ${toPersianNum(d)}
                        </button>
                    `;
                } else if (isCheckOut) {
                    html += `
                        <button type="button" onclick="selectCalendarDay(${d})"
                                title="تاریخ خروج"
                                class="h-10 sm:h-11 w-full rounded-lg sm:rounded-xl font-black text-xs sm:text-sm bg-[#007A5E] dark:bg-emerald-600 text-white shadow-md flex items-center justify-center transition-transform active:scale-95 font-sans z-10">
                            ${toPersianNum(d)}
                        </button>
                    `;
                } else if (inRange) {
                    // In-Range days: Soft luminous emerald tint with dark green text for clear contrast with check-in/out
                    html += `
                        <button type="button" onclick="selectCalendarDay(${d})"
                                title="بازه انتخابی"
                                class="h-10 sm:h-11 w-full rounded-lg sm:rounded-xl font-bold text-xs sm:text-sm bg-[#E6F4F0] dark:bg-emerald-950/60 text-[#007A5E] dark:text-emerald-300 border border-emerald-100/80 dark:border-emerald-800/50 flex items-center justify-center transition-colors font-sans">
                            ${toPersianNum(d)}
                        </button>
                    `;
                } else {
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
        if (activeCalendarMonthIndex === 0 && day < currentJalaliDay) {
            showToast('امکان انتخاب تاریخ در گذشته وجود ندارد');
            return;
        }

        const clickedAbs = getAbsoluteDay(activeCalendarMonthIndex, day);
        const hasStart = (state.startDay !== null && state.startMonthIndex !== null);
        const hasEnd = (state.endDay !== null && state.endMonthIndex !== null);

        if (!hasStart || (hasStart && hasEnd)) {
            // First tap: Select Check-in date, reset Check-out (matching show.blade.php UX)
            state.startMonthIndex = activeCalendarMonthIndex;
            state.startDay = day;
            state.endMonthIndex = null;
            state.endDay = null;
            dateSelectionStep = 'end';
            updateDateLabelsAndSummary();
            renderJalaliCalendars();
        } else {
            // Second tap: Check-in is selected, now selecting Check-out
            const startAbs = getAbsoluteDay(state.startMonthIndex, state.startDay);
            if (clickedAbs > startAbs) {
                state.endMonthIndex = activeCalendarMonthIndex;
                state.endDay = day;
                dateSelectionStep = 'start';
                updateDateLabelsAndSummary();
                renderJalaliCalendars();

                // Close date step on mobile search sheet after picking check-out
                setTimeout(() => {
                    setMobileSearchSection('none');
                }, 280);
            } else if (clickedAbs < startAbs) {
                // Clicked an earlier date: make it the new check-in date
                state.startMonthIndex = activeCalendarMonthIndex;
                state.startDay = day;
                state.endMonthIndex = null;
                state.endDay = null;
                dateSelectionStep = 'end';
                updateDateLabelsAndSummary();
                renderJalaliCalendars();
            } else {
                // Clicked the exact same day: keep as check-in
                state.startMonthIndex = activeCalendarMonthIndex;
                state.startDay = day;
                state.endMonthIndex = null;
                state.endDay = null;
                dateSelectionStep = 'end';
                updateDateLabelsAndSummary();
                renderJalaliCalendars();
            }
        }
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
        initCategoryExploreDrag();
    });
</script>
@endsection
