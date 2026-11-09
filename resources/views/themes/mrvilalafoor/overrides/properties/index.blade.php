@extends('layouts.web')

@section('title', 'اجاره ویلا، کلبه و اقامتگاه در شمال و لفور - مستر ویلا لفور')

@section('content')
<style>
    /* Uniform 2-line title height (Locked so cards never vary in height) */
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

    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Modal Animation */
    .filter-drawer-open {
        transform: translateX(0) !important;
    }

    /* Horizontal Drag & Scroll for Sorting Options */
    #sorting-track {
        cursor: grab;
        user-select: none;
        -webkit-user-select: none;
        -webkit-overflow-scrolling: touch;
        touch-action: pan-x pan-y;
        overscroll-behavior-x: contain;
    }
    #sorting-track.is-dragging {
        cursor: grabbing !important;
        scroll-behavior: auto !important;
    }
    #sorting-track a {
        -webkit-user-drag: none;
        user-drag: none;
    }
</style>

@php
    $rentalModeEnabled = $rentalModeEnabled ?? (bool) \Modules\Properties\Entities\PropertySetting::get('rental_mode_enabled', 1);
    $multiHostEnabled = $multiHostEnabled ?? (bool) \Modules\Properties\Entities\PropertySetting::get('rental_multi_host_enabled', 0);
    $rentalPropertyTypes = $rentalPropertyTypes ?? \Modules\Properties\Entities\Property::getRentalPropertyTypes(true);
    $activeHosts = $activeHosts ?? ($multiHostEnabled ? \Modules\Properties\Entities\PropertyHost::where('status', 'active')->get() : collect());
    $categoriesList = $categories ?? \Modules\Properties\Entities\PropertyCategory::all();
    $destinationsList = $availableDestinations ?? [];
    $currLabel = $currencyLabel ?? (\Modules\Properties\Entities\PropertySetting::getCurrencyLabel() ?? 'تومان');
    $displayProperties = $properties;

    // Active filters detector for quick pills
    $activeFilterPills = [];
    if (request()->filled('search')) {
        $activeFilterPills[] = ['label' => 'جستجو: ' . request('search'), 'param' => 'search'];
    }
    if (request()->filled('province')) {
        $activeFilterPills[] = ['label' => 'استان: ' . request('province'), 'param' => 'province'];
    }
    if (request()->filled('city')) {
        $activeFilterPills[] = ['label' => 'شهر: ' . request('city'), 'param' => 'city'];
    }
    if (request()->filled('check_in') && request()->filled('check_out')) {
        $activeFilterPills[] = ['label' => 'تاریخ: ' . request('check_in') . ' تا ' . request('check_out'), 'param' => 'dates'];
    }
    if (request()->filled('guests') && (int)request('guests') > 0) {
        $activeFilterPills[] = ['label' => 'ظرفیت: تا ' . request('guests') . ' نفر', 'param' => 'guests'];
    }
    if (request()->filled('property_type')) {
        $typeLabel = $rentalPropertyTypes[request('property_type')]['label'] ?? request('property_type');
        $activeFilterPills[] = ['label' => 'نوع: ' . $typeLabel, 'param' => 'property_type'];
    }
    if (request()->filled('category_id')) {
        $foundCat = $categoriesList->firstWhere('id', request('category_id'));
        $catLabel = $foundCat ? $foundCat->name : ('دسته #' . request('category_id'));
        $activeFilterPills[] = ['label' => 'دسته: ' . $catLabel, 'param' => 'category_id'];
    }
    if (request()->filled('instant_booking') && (request('instant_booking') == '1' || request('instant_booking') === 'true')) {
        $activeFilterPills[] = ['label' => '⚡ رزرو آنی', 'param' => 'instant_booking'];
    }
    $cleanPriceNum = function($v) {
        if ($v === null || $v === '') return 0;
        $p = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $e = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $s = str_replace($p, $e, (string)$v);
        $s = preg_replace('/[^0-9]/', '', $s);
        return (float)($s ?: 0);
    };

    if (request()->filled('has_discount') && (request('has_discount') == '1' || request('has_discount') === 'true')) {
        $activeFilterPills[] = ['label' => '🏷️ دارای تخفیف', 'param' => 'has_discount'];
    }
    if (request()->filled('min_price') || request()->filled('max_price')) {
        $priceText = 'قیمت: ';
        if (request()->filled('min_price')) $priceText .= 'از ' . number_format($cleanPriceNum(request('min_price'))) . ' ';
        if (request()->filled('max_price')) $priceText .= 'تا ' . number_format($cleanPriceNum(request('max_price'))) . ' ';
        $priceText .= $currLabel;
        $activeFilterPills[] = ['label' => $priceText, 'param' => 'price_range'];
    }
    if ($multiHostEnabled && request()->filled('host_id')) {
        $foundHost = $activeHosts->firstWhere('id', request('host_id'));
        $hostLabel = $foundHost ? $foundHost->display_name : ('میزبان #' . request('host_id'));
        $activeFilterPills[] = ['label' => 'میزبان: ' . $hostLabel, 'param' => 'host_id'];
    }

    $activeSort = request('sort', 'popular');

    // Jalali Month generator for Quick Date Picker
    $jalaliNow = class_exists(\Morilog\Jalali\Jalalian::class) ? \Morilog\Jalali\Jalalian::now() : null;
    $currentJalaliYear = $jalaliNow ? $jalaliNow->getYear() : 1405;
    $currentJalaliMonth = $jalaliNow ? $jalaliNow->getMonth() : 8;
    $currentJalaliMonthName = $jalaliNow ? $jalaliNow->format('%B') : 'آبان';
    $currentJalaliDay = $jalaliNow ? $jalaliNow->getDay() : 18;

    $calendarMonths = [];
    if ($jalaliNow) {
        $baseMonth = new \Morilog\Jalali\Jalalian($currentJalaliYear, $currentJalaliMonth, 1);
        for ($m = 0; $m < 6; $m++) {
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
    }
@endphp

<div class="w-full bg-[#FDFDFD] dark:bg-zinc-950 font-sans selection:bg-[#007A5E] selection:text-white pb-6 sm:pb-8">

    <!-- ============================================================= -->
    <!-- 1. STICKY QUICK FILTER BAR (شب و جاباما استایل)              -->
    <!-- ============================================================= -->
    <section class="sticky top-0 md:top-[76px] z-30 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border-b border-[#ECECEE] dark:border-zinc-800 shadow-xs transition-colors">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-3">
            
            <!-- Mobile Sticky Quick Search Bar (< md) -->
            <div class="md:hidden flex items-center gap-2">
                <button type="button" onclick="openAllFiltersDrawer('search')" class="flex-1 bg-[#F7F8F9] dark:bg-zinc-800/80 border border-[#ECECEE] dark:border-zinc-700/80 rounded-2xl px-3.5 py-2.5 flex items-center justify-between text-right">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <svg class="w-4 h-4 text-[#007A5E] dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                        <div class="truncate flex flex-col gap-1">
                            <span class="block text-xs font-bold text-[#18181B] dark:text-zinc-100 truncate leading-snug">
                                {{ request('city') ? ('اقامتگاه‌های ' . request('city')) : (request('search') ? request('search') : 'جستجوی شهر، عنوان یا کد اقامتگاه...') }}
                            </span>
                            <span class="block text-[11px] text-zinc-400 dark:text-zinc-400 font-normal truncate leading-relaxed">
                                {{ request('check_in') ? (request('check_in') . ' تا ' . request('check_out')) : 'انتخاب تاریخ و تعداد نفرات' }}
                            </span>
                        </div>
                    </div>
                    <span class="w-7 h-7 rounded-xl bg-white dark:bg-zinc-700 border border-[#ECECEE] dark:border-zinc-600 flex items-center justify-center text-zinc-600 dark:text-zinc-200 shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/>
                        </svg>
                    </span>
                </button>

                <!-- Mobile Map Pill Toggle -->
                <a href="{{ route('properties.map', request()->query()) }}" title="نمایش روی نقشه" class="h-11 px-3 rounded-2xl border border-[#ECECEE] dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 flex items-center justify-center gap-1.5 shrink-0 text-xs font-bold shadow-2xs">
                    <svg class="w-4 h-4 text-[#007A5E] dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    <span class="hidden xs:inline">نقشه</span>
                </a>
            </div>

            <!-- Desktop Horizontal Filter Bar (>= md) -->
            <div class="hidden md:flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 flex-wrap">

                    {{-- 1. Destination / Keyword Pill --}}
                    <div class="relative" id="dest-dropdown-wrapper">
                        <button type="button" onclick="toggleFilterDropdown('dest', event)" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border {{ (request('city') || request('province') || request('search')) ? 'border-[#007A5E] bg-[#ECFDF5] dark:bg-emerald-950/40 text-[#007A5E] dark:text-emerald-300 font-black' : 'border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9] dark:bg-zinc-800/80 text-zinc-700 dark:text-zinc-200 hover:border-zinc-300 dark:hover:border-zinc-700 font-bold' }} text-xs transition-colors">
                            <svg class="w-4 h-4 {{ (request('city') || request('province') || request('search')) ? 'text-[#007A5E] dark:text-emerald-400' : 'text-zinc-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>{{ request('city') ? ('شهر: ' . request('city')) : (request('province') ? ('استان: ' . request('province')) : (request('search') ? ('جستجو: ' . request('search')) : 'مقصد و شهر')) }}</span>
                            <svg class="w-3 h-3 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </button>

                        {{-- Dest Dropdown Menu --}}
                        <div id="dest-dropdown-menu" class="hidden absolute right-0 top-full mt-2 w-80 bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 rounded-2xl shadow-xl p-4 z-50 font-sans space-y-3">
                            <form action="{{ route('properties.index') }}" method="GET" class="space-y-3">
                                @foreach(request()->except(['city', 'province', 'search', 'page']) as $k => $v)
                                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                @endforeach
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-400 mb-1">جستجوی متنی عنوان یا کد ملک</label>
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="نام اقامتگاه، کد ملک یا منطقه..." class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 rounded-xl px-3 py-2 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none focus:border-[#007A5E]">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-400 mb-1">انتخاب شهر و مقصد</label>
                                    <select name="city" class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 rounded-xl px-3 py-2 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none focus:border-[#007A5E]">
                                        <option value="">همه مقاصد</option>
                                        @foreach($destinationsList as $destGroup)
                                            <optgroup label="استان {{ $destGroup['province'] }}">
                                                @foreach($destGroup['cities'] as $c)
                                                    <option value="{{ $c }}" {{ request('city') == $c ? 'selected' : '' }}>{{ $c }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-[#ECECEE] dark:border-zinc-800">
                                    <a href="{{ request()->fullUrlWithQuery(['city' => null, 'province' => null, 'search' => null]) }}" class="text-[11px] font-bold text-rose-500 hover:text-rose-600">پاکسازی</a>
                                    <button type="submit" class="bg-[#007A5E] hover:bg-[#005F48] text-white px-4 py-1.5 rounded-xl font-bold text-xs transition-colors shadow-xs">ثبت مقصد</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- 2. Date Picker Pill --}}
                    <div class="relative" id="date-dropdown-wrapper">
                        <button type="button" onclick="toggleFilterDropdown('date', event)" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border {{ (request('check_in') && request('check_out')) ? 'border-[#007A5E] bg-[#ECFDF5] dark:bg-emerald-950/40 text-[#007A5E] dark:text-emerald-300 font-black' : 'border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9] dark:bg-zinc-800/80 text-zinc-700 dark:text-zinc-200 hover:border-zinc-300 dark:hover:border-zinc-700 font-bold' }} text-xs transition-colors">
                            <svg class="w-4 h-4 {{ (request('check_in') && request('check_out')) ? 'text-[#007A5E] dark:text-emerald-400' : 'text-zinc-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>{{ (request('check_in') && request('check_out')) ? (request('check_in') . ' تا ' . request('check_out')) : 'تاریخ سفر' }}</span>
                            <svg class="w-3 h-3 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </button>

                        {{-- Date Dropdown Menu with Jalali Month Calendar --}}
                        <div id="date-dropdown-menu" onclick="event.stopPropagation();" class="hidden absolute right-0 top-full mt-2 w-[340px] bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 rounded-2xl shadow-xl p-4 z-50 font-sans space-y-3">
                            <div class="flex items-center justify-between border-b border-[#ECECEE] dark:border-zinc-800 pb-2">
                                <span class="text-xs font-black text-[#18181B] dark:text-zinc-100 flex items-center gap-1.5">
                                    <span>🗓️</span>
                                    <span>انتخاب تاریخ ورود و خروج</span>
                                </span>
                                <span id="calendar-month-name-display" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-[#007A5E] dark:text-emerald-300 text-xs font-black border border-emerald-200/60 dark:border-emerald-800/60">
                                    {{ $currentJalaliMonthName }} {{ $currentJalaliYear }}
                                </span>
                            </div>

                            {{-- Month Days Grid --}}
                            <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-bold text-zinc-400 mb-1">
                                <span>ش</span><span>ی</span><span>د</span><span>س</span><span>چ</span><span>پ</span><span class="text-rose-500">ج</span>
                            </div>
                            <div id="calendar-days-grid" class="grid grid-cols-7 gap-1">
                                {{-- Dynamically rendered via JavaScript with soft intermediate color --}}
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-[#ECECEE] dark:border-zinc-800">
                                <button type="button" onclick="clearSelectedDates()" class="text-[11px] font-bold text-rose-500 hover:text-rose-600">پاکسازی</button>
                                <button type="button" onclick="applySelectedDates()" class="bg-[#007A5E] hover:bg-[#005F48] text-white px-4 py-1.5 rounded-xl font-bold text-xs transition-colors shadow-xs">تأیید تاریخ</button>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Guests Pill --}}
                    <div class="relative" id="guests-dropdown-wrapper">
                        <button type="button" onclick="toggleFilterDropdown('guests', event)" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border {{ (request('guests') && (int)request('guests') > 0) ? 'border-[#007A5E] bg-[#ECFDF5] dark:bg-emerald-950/40 text-[#007A5E] dark:text-emerald-300 font-black' : 'border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9] dark:bg-zinc-800/80 text-zinc-700 dark:text-zinc-200 hover:border-zinc-300 dark:hover:border-zinc-700 font-bold' }} text-xs transition-colors">
                            <svg class="w-4 h-4 {{ (request('guests') && (int)request('guests') > 0) ? 'text-[#007A5E] dark:text-emerald-400' : 'text-zinc-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>{{ (request('guests') && (int)request('guests') > 0) ? ('تا ' . request('guests') . ' نفر') : 'تعداد نفرات' }}</span>
                            <svg class="w-3 h-3 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </button>

                        {{-- Guests Dropdown Menu --}}
                        <div id="guests-dropdown-menu" class="hidden absolute right-0 top-full mt-2 w-72 bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 rounded-2xl shadow-xl p-4 z-50 font-sans space-y-3">
                            <form action="{{ route('properties.index') }}" method="GET" class="space-y-3">
                                @foreach(request()->except(['guests', 'page']) as $k => $v)
                                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                @endforeach
                                <div class="flex items-center justify-between">
                                    <div class="flex flex-col gap-1">
                                        <div class="text-xs font-bold text-[#18181B] dark:text-zinc-100 leading-snug">تعداد مسافران</div>
                                        <div class="text-[11px] text-zinc-400 dark:text-zinc-400 leading-relaxed">حداقل ظرفیت مسافر</div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="adjustGuestCount(-1)" class="w-8 h-8 rounded-xl border border-[#ECECEE] dark:border-zinc-700 bg-[#F7F8F9] dark:bg-zinc-800 flex items-center justify-center text-sm font-black hover:bg-zinc-200 transition-colors">-</button>
                                        <span id="guest-count-display" class="w-7 text-center text-xs font-black text-[#18181B] dark:text-zinc-100 font-sans">{{ request('guests', 1) }}</span>
                                        <input type="hidden" id="guest-count-input" name="guests" value="{{ request('guests', 1) }}">
                                        <button type="button" onclick="adjustGuestCount(1)" class="w-8 h-8 rounded-xl border border-[#ECECEE] dark:border-zinc-700 bg-[#F7F8F9] dark:bg-zinc-800 flex items-center justify-center text-sm font-black hover:bg-zinc-200 transition-colors">+</button>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-[#ECECEE] dark:border-zinc-800">
                                    <a href="{{ request()->fullUrlWithQuery(['guests' => null]) }}" class="text-[11px] font-bold text-rose-500 hover:text-rose-600">حذف</a>
                                    <button type="submit" class="bg-[#007A5E] hover:bg-[#005F48] text-white px-4 py-1.5 rounded-xl font-bold text-xs transition-colors shadow-xs">اعمال ظرفیت</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- 4. Property Type Pill --}}
                    @if($rentalModeEnabled)
                        <div class="relative" id="type-dropdown-wrapper">
                            <button type="button" onclick="toggleFilterDropdown('type', event)" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border {{ request('property_type') ? 'border-[#007A5E] bg-[#ECFDF5] dark:bg-emerald-950/40 text-[#007A5E] dark:text-emerald-300 font-black' : 'border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9] dark:bg-zinc-800/80 text-zinc-700 dark:text-zinc-200 hover:border-zinc-300 dark:hover:border-zinc-700 font-bold' }} text-xs transition-colors">
                                <svg class="w-4 h-4 {{ request('property_type') ? 'text-[#007A5E] dark:text-emerald-400' : 'text-zinc-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <span>{{ request('property_type') ? ($rentalPropertyTypes[request('property_type')]['label'] ?? request('property_type')) : 'نوع اقامتگاه' }}</span>
                                <svg class="w-3 h-3 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </button>

                            {{-- Type Dropdown Menu --}}
                            <div id="type-dropdown-menu" class="hidden absolute right-0 top-full mt-2 w-56 bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 rounded-2xl shadow-xl p-3 z-50 font-sans space-y-1.5">
                                <a href="{{ request()->fullUrlWithQuery(['property_type' => null, 'page' => null]) }}" class="block px-3 py-2 rounded-xl text-xs {{ !request('property_type') ? 'bg-emerald-50 dark:bg-emerald-950/40 text-[#007A5E] font-black' : 'text-zinc-700 dark:text-zinc-300 hover:bg-[#F7F8F9] dark:hover:bg-zinc-800' }} transition-colors">
                                    همه انواع اقامتگاه
                                </a>
                                @foreach($rentalPropertyTypes as $tKey => $tConfig)
                                    <a href="{{ request()->fullUrlWithQuery(['property_type' => $tKey, 'page' => null]) }}" class="block px-3 py-2 rounded-xl text-xs {{ request('property_type') == $tKey ? 'bg-emerald-50 dark:bg-emerald-950/40 text-[#007A5E] font-black' : 'text-zinc-700 dark:text-zinc-300 hover:bg-[#F7F8F9] dark:hover:bg-zinc-800' }} transition-colors">
                                        {{ $tConfig['label'] ?? $tKey }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- 5. Price Range Pill --}}
                    <div class="relative" id="price-dropdown-wrapper">
                        <button type="button" onclick="toggleFilterDropdown('price', event)" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border {{ (request('min_price') || request('max_price')) ? 'border-[#007A5E] bg-[#ECFDF5] dark:bg-emerald-950/40 text-[#007A5E] dark:text-emerald-300 font-black' : 'border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9] dark:bg-zinc-800/80 text-zinc-700 dark:text-zinc-200 hover:border-zinc-300 dark:hover:border-zinc-700 font-bold' }} text-xs transition-colors">
                            <svg class="w-4 h-4 {{ (request('min_price') || request('max_price')) ? 'text-[#007A5E] dark:text-emerald-400' : 'text-zinc-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ (request('min_price') || request('max_price')) ? 'قیمت تعیین‌شده' : 'قیمت هر شب' }}</span>
                            <svg class="w-3 h-3 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </button>

                        {{-- Price Dropdown Menu --}}
                        <div id="price-dropdown-menu" onclick="event.stopPropagation();" class="hidden absolute right-0 top-full mt-2 w-72 bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 rounded-2xl shadow-xl p-4 z-50 font-sans space-y-3">
                            <form action="{{ route('properties.index') }}" method="GET" class="space-y-3">
                                @foreach(request()->except(['min_price', 'max_price', 'page']) as $k => $v)
                                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                @endforeach
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-400 mb-1">حداقل قیمت ({{ $currLabel }})</label>
                                    <input type="text" inputmode="numeric" name="min_price" value="{{ request('min_price') ? number_format($cleanPriceNum(request('min_price'))) : '' }}" oninput="formatPriceInput(this)" placeholder="مثلاً ۱,۰۰۰,۰۰۰" class="price-format-input w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 rounded-xl px-3 py-2 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none focus:border-[#007A5E] font-sans">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-400 mb-1">حداکثر قیمت ({{ $currLabel }})</label>
                                    <input type="text" inputmode="numeric" name="max_price" value="{{ request('max_price') ? number_format($cleanPriceNum(request('max_price'))) : '' }}" oninput="formatPriceInput(this)" placeholder="مثلاً ۵,۰۰۰,۰۰۰" class="price-format-input w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 rounded-xl px-3 py-2 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none focus:border-[#007A5E] font-sans">
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-[#ECECEE] dark:border-zinc-800">
                                    <a href="{{ request()->fullUrlWithQuery(['min_price' => null, 'max_price' => null]) }}" class="text-[11px] font-bold text-rose-500 hover:text-rose-600">حذف</a>
                                    <button type="submit" class="bg-[#007A5E] hover:bg-[#005F48] text-white px-4 py-1.5 rounded-xl font-bold text-xs transition-colors shadow-xs">اعمال قیمت</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- 6. Instant Booking Toggle Pill --}}
                    @php
                        $isInstantActive = request()->filled('instant_booking') && (request('instant_booking') == '1' || request('instant_booking') === 'true');
                        $instantToggleUrl = request()->fullUrlWithQuery([
                            'instant_booking' => $isInstantActive ? null : '1',
                            'page' => null
                        ]);
                    @endphp
                    <a href="{{ $instantToggleUrl }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border {{ $isInstantActive ? 'border-amber-400/80 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-black shadow-xs' : 'border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9] dark:bg-zinc-800/80 text-zinc-700 dark:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-700 font-bold' }} text-xs transition-all">
                        <span class="text-xs">⚡</span>
                        <span>رزرو آنی</span>
                    </a>

                    {{-- 7. All Filters (فیلترهای بیشتر) Drawer Button --}}
                    <button type="button" onclick="openAllFiltersDrawer()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9] dark:bg-zinc-800/80 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 text-xs font-bold transition-colors">
                        <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        <span>فیلترها</span>
                        @if(count($activeFilterPills) > 0)
                            <span class="w-4 h-4 rounded-full bg-[#007A5E] text-white text-[10px] font-black flex items-center justify-center font-sans">
                                {{ count($activeFilterPills) }}
                            </span>
                        @endif
                    </button>

                </div>

                {{-- Left Side: Map Toggle Pill --}}
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('properties.map', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-800/90 hover:border-zinc-300 dark:hover:border-zinc-700 text-zinc-700 dark:text-zinc-200 text-xs font-bold transition-all shadow-xs group">
                        <svg class="w-4 h-4 text-[#007A5E] dark:text-emerald-400 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        <span>نمایش نقشه</span>
                    </a>
                </div>

            </div>

        </div>
    </section>

    <!-- ============================================================= -->
    <!-- 2. MAIN CONTAINER: HEADER, SORTING & PROPERTY GRID            -->
    <!-- ============================================================= -->
    <main class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 pt-6 sm:pt-8 w-full font-sans space-y-6">

        {{-- Breadcrumb & Category Title & Results Count --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-zinc-400 mb-1.5">
                    <a href="{{ url('/') }}" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">مستر ویلا لفور</a>
                    <span>/</span>
                    <span class="text-[#18181B] dark:text-zinc-100 font-bold">
                        {{ request('city') ? ('اقامتگاه‌های ' . request('city')) : 'اجاره ویلا و اقامتگاه' }}
                    </span>
                </div>
                <div class="flex items-baseline gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-[#18181B] dark:text-zinc-100 tracking-tight">
                        {{ request('city') ? ('اجاره ویلا و سوئیت در ' . request('city')) : 'اجاره ویلا، سوئیت و کلبه‌های جنگلی' }}
                    </h1>
                    <span class="text-xs sm:text-sm font-bold text-zinc-500 dark:text-zinc-400 font-sans">
                        ({{ number_format($displayProperties->total()) }} اقامتگاه)
                    </span>
                </div>
            </div>

            {{-- Sorting Bar (مطابق نمونه‌های شب و جاباما همراه با درگ و لمس راست‌چین) --}}
            <div id="sorting-track" class="flex items-center gap-1.5 text-xs font-medium text-zinc-500 dark:text-zinc-400 overflow-x-auto no-scrollbar py-1 max-w-full select-none cursor-grab active:cursor-grabbing touch-pan-x">
                <span class="shrink-0 font-bold text-zinc-400 ml-1 sticky right-0 bg-[#FDFDFD] dark:bg-zinc-950 pl-1 z-10">مرتب‌سازی:</span>

                @php
                    $sortItems = [
                        'popular' => 'محبوب‌ترین',
                        'price_asc' => 'ارزان‌ترین',
                        'price_desc' => 'گران‌ترین',
                        'newest' => 'جدیدترین',
                        'rating' => 'بالاترین امتیاز'
                    ];
                @endphp

                @foreach($sortItems as $sKey => $sLabel)
                    @php
                        $isActiveSort = ($activeSort === $sKey);
                        $sortUrl = request()->fullUrlWithQuery(['sort' => $sKey, 'page' => null]);
                    @endphp
                    <a href="{{ $sortUrl }}" class="sort-pill px-3 py-1.5 rounded-xl shrink-0 transition-colors select-none {{ $isActiveSort ? 'bg-[#007A5E] text-white font-bold shadow-xs' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-600 dark:text-zinc-300 font-medium' }}">
                        {{ $sLabel }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Active Filter Pills (اگر فیلتری انتخاب شده باشد) --}}
        @if(count($activeFilterPills) > 0)
            <div class="flex items-center gap-2 flex-wrap py-2 border-y border-[#ECECEE] dark:border-zinc-800">
                <span class="text-xs font-bold text-zinc-400">فیلترهای فعال:</span>
                @foreach($activeFilterPills as $pill)
                    @php
                        $clearQuery = [];
                        if ($pill['param'] === 'dates') {
                            $clearQuery = ['check_in' => null, 'check_out' => null];
                        } elseif ($pill['param'] === 'price_range') {
                            $clearQuery = ['min_price' => null, 'max_price' => null];
                        } else {
                            $clearQuery = [$pill['param'] => null];
                        }
                    @endphp
                    <a href="{{ request()->fullUrlWithQuery($clearQuery) }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 text-[#18181B] dark:text-zinc-200 text-xs font-bold hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                        <span>{{ $pill['label'] }}</span>
                        <span class="text-zinc-400 hover:text-rose-500 font-black">×</span>
                    </a>
                @endforeach
                <a href="{{ route('properties.index') }}" class="text-xs font-bold text-rose-500 hover:text-rose-600 mr-2">
                    پاکسازی همه
                </a>
            </div>
        @endif

        <!-- ============================================================= -->
        <!-- 3. PROPERTY CARDS GRID (4 COLUMNS DESKTOP, RESPONSIVE)       -->
        <!-- ============================================================= -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5.5">
            @forelse($displayProperties as $property)
                @php
                    $rentalConfig = $property->rentalConfig;
                    $coverUrl = null;
                    if (!empty($property->cover_image)) {
                        $coverUrl = str_starts_with($property->cover_image, 'http')
                            ? $property->cover_image
                            : asset('storage/' . $property->cover_image);
                    } else {
                        $coverUrl = asset('images/property-placeholder.svg');
                    }

                    $rate = $rentalConfig?->price_per_night ?: ($property->rent_price ?: $property->price);
                    $oldRate = $rentalConfig?->old_daily_rate ?: ($rentalConfig?->normal_price_per_night ?: ($property->price > $rate ? $property->price : null));
                    $hasDiscount = $oldRate && $oldRate > $rate;
                    $discountPercent = $hasDiscount ? round((($oldRate - $rate) / $oldRate) * 100) : 0;

                    // Specs Line
                    $rooms = $property->bedrooms ?? $property->rooms ?? ($property->meta['rooms'] ?? ($property->meta['bedrooms'] ?? null));
                    $maxGuests = $rentalConfig?->max_guests ?? ($property->max_guests ?? ($property->meta['max_guests'] ?? null));
                    $area = $property->area ?? ($property->meta['area'] ?? null);

                    $specs = [];
                    if (!empty($rooms) && (int)$rooms > 0) {
                        $specs[] = (int)$rooms . ' خوابه';
                    }
                    if (!empty($maxGuests) && (int)$maxGuests > 0) {
                        $specs[] = 'تا ' . (int)$maxGuests . ' نفر';
                    }
                    if (!empty($area) && (int)$area > 0) {
                        $specs[] = (int)$area . ' متر';
                    }
                    $specsText = count($specs) > 0 ? implode(' • ', $specs) : 'اقامتگاه مبله';

                    $propertySlug = $property->slug ?: $property->code;
                    $propertyShowUrl = route('properties.show', $propertySlug);
                @endphp

                <article class="group select-none bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800/80 hover:border-zinc-300 dark:hover:border-zinc-700 rounded-2xl p-2.5 sm:p-3 transition-all duration-200 shadow-[0_2px_8px_rgba(0,0,0,0.03)] dark:shadow-[0_2px_8px_rgba(0,0,0,0.2)] hover:shadow-lg flex flex-col justify-between h-full">
                    <a href="{{ $propertyShowUrl }}" class="flex flex-col justify-between h-full">
                        <div>
                            {{-- Image Container --}}
                            <div class="relative aspect-[4/3] w-full rounded-xl overflow-hidden bg-[#F7F8F9] dark:bg-zinc-800 mb-2.5 sm:mb-3 border border-[#ECECEE]/60 dark:border-zinc-800/60">
                                <img src="{{ $coverUrl }}" alt="{{ $property->title }}" onerror="this.onerror=null; this.src='{{ asset('images/property-placeholder.svg') }}';" class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-300" loading="lazy">
                                
                                {{-- Favorite Heart Button (Synchronized with localStorage) --}}
                                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleCardFavorite('{{ $property->id }}')" class="fav-btn-{{ $property->id }} absolute top-2.5 left-2.5 w-8 h-8 rounded-full bg-white/90 dark:bg-zinc-900/90 text-zinc-600 dark:text-zinc-300 flex items-center justify-center hover:scale-110 active:scale-95 transition-all shadow-sm z-10" title="نشان کردن اقامتگاه">
                                    <svg class="w-4 h-4 stroke-current fill-transparent" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                                    </svg>
                                </button>

                                {{-- Special Badge --}}
                                @if(!empty($property->is_special) || (!empty($property->meta) && !empty($property->meta['is_special'])))
                                    <span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-lg bg-amber-500 text-white text-[10px] sm:text-[11px] font-bold shadow-sm font-sans">
                                        ویژه
                                    </span>
                                @endif

                                {{-- Instant Booking Badge (لایتنینگ زرد/سبز مطابق جاباما) --}}
                                @if($rentalConfig && $rentalConfig->instant_booking)
                                    <span class="absolute bottom-2.5 right-2.5 inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white/95 dark:bg-zinc-900/95 text-amber-600 dark:text-amber-400 text-[10px] sm:text-[11px] font-black shadow-xs backdrop-blur-xs font-sans">
                                        <span class="text-xs">⚡</span>
                                        <span>رزرو آنی</span>
                                    </span>
                                @endif
                            </div>

                            {{-- Rating Row & Host --}}
                            <div class="flex items-center justify-between gap-1.5 px-0.5 mb-1.5">
                                <div class="flex items-center gap-1">
                                    @if(!empty($property->rating))
                                        <span class="inline-flex items-center gap-1 text-[11px] sm:text-xs font-black text-[#18181B] dark:text-zinc-100 font-sans">
                                            <span class="text-amber-400">★</span>
                                            <span>{{ number_format($property->rating, 1) }}</span>
                                        </span>
                                    @else
                                        <span class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 font-sans">
                                            جدید
                                        </span>
                                    @endif
                                </div>
                                @if(!empty($property->city))
                                    <span class="text-[11px] text-zinc-400 dark:text-zinc-500 font-medium truncate flex items-center gap-1">
                                        <svg class="w-3 h-3 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>{{ $property->city }}</span>
                                    </span>
                                @endif
                            </div>

                            {{-- Title (Fixed 2 lines) --}}
                            <h3 class="card-title-fixed-height text-xs sm:text-sm font-black text-[#18181B] dark:text-zinc-100 group-hover:text-[#007A5E] dark:group-hover:text-emerald-400 transition-colors px-0.5">
                                {{ $property->title }}
                            </h3>

                            {{-- Multi-Host Info (When Multi-Host Enabled) --}}
                            @if($multiHostEnabled && $property->host)
                                <div class="flex items-center gap-1 mt-1 px-0.5 text-[11px] text-zinc-500 dark:text-zinc-400 font-medium">
                                    <svg class="w-3 h-3 text-[#007A5E] dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span class="truncate">میزبان: {{ $property->host->display_name }}</span>
                                </div>
                            @endif

                            {{-- Specs Line --}}
                            <p class="text-[11px] sm:text-xs text-zinc-500 dark:text-zinc-400 truncate font-sans px-0.5 mt-1.5">
                                {{ $specsText }}
                            </p>
                        </div>

                        {{-- Pricing Section (Clean Shab & Jabama style) --}}
                        <div class="mt-3 pt-2.5 border-t border-[#ECECEE]/70 dark:border-zinc-800/70 flex items-end justify-between gap-1.5 px-0.5 font-sans">
                            <div>
                                @if($hasDiscount)
                                    <span class="block text-[11px] text-zinc-400 dark:text-zinc-500 line-through font-normal leading-tight font-sans">
                                        {{ number_format($oldRate) }}
                                    </span>
                                @endif
                                <div class="text-sm sm:text-base font-black {{ $hasDiscount ? 'text-[#007A5E] dark:text-emerald-400' : 'text-[#18181B] dark:text-zinc-100' }} leading-tight font-sans mt-0.5">
                                    @if($rate > 0)
                                        {{ number_format($rate) }} <span class="text-xs font-bold text-zinc-500 dark:text-zinc-400 font-sans">{{ $currLabel }}</span> <span class="text-[10px] font-normal text-zinc-400 dark:text-zinc-500 font-sans">/ شب</span>
                                    @else
                                        <span class="text-xs font-medium text-zinc-500">استعلام قیمت</span>
                                    @endif
                                </div>
                            </div>

                            @if($hasDiscount && $discountPercent > 0)
                                <span class="inline-flex items-center px-2 py-1 rounded-lg bg-[#ECFDF5] dark:bg-emerald-950/60 text-[#007A5E] dark:text-emerald-400 text-xs font-black font-sans leading-none shrink-0 mb-0.5">
                                    {{ $discountPercent }}٪ تخفیف
                                </span>
                            @endif
                        </div>
                    </a>
                </article>
            @empty
                <div class="col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-4 py-20 text-center bg-white dark:bg-zinc-900 rounded-3xl border border-[#ECECEE] dark:border-zinc-800 p-8">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-3xl text-zinc-400">
                        🏡
                    </div>
                    <h3 class="text-base sm:text-lg font-black text-[#18181B] dark:text-zinc-100 mb-2">
                        اقامتگاهی با فیلترهای مشخص‌شده یافت نشد.
                    </h3>
                    <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-5 leading-relaxed">
                        می‌توانید فیلترهای انتخابی (شهر، تاریخ، محدوده قیمت یا نوع اقامتگاه) را تغییر داده یا تمام اقامتگاه‌های موجود را مشاهده نمایید.
                    </p>
                    <a href="{{ route('properties.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#007A5E] hover:bg-[#005F48] text-white text-xs font-bold transition-colors shadow-sm">
                        <span>مشاهده تمام اقامتگاه‌ها</span>
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Custom Pagination --}}
        @if($displayProperties->hasPages())
            <div class="pt-8 border-t border-[#ECECEE] dark:border-zinc-800 flex justify-center">
                {{ $displayProperties->links() }}
            </div>
        @endif

        <!-- ============================================================= -->
        <!-- 4. BOTTOM SEO DISCOVERY PILLS (شب و جاباما استایل)            -->
        <!-- ============================================================= -->
        <section class="mt-6 pt-5 border-t border-[#ECECEE] dark:border-zinc-800 space-y-3">
            <h4 class="text-xs sm:text-sm font-black text-[#18181B] dark:text-zinc-200">
                اقامتگاه‌های محبوب و مقاصد پرطرفدار در شمال و لفور:
            </h4>
            <div class="flex items-center gap-2 flex-wrap text-xs font-medium">
                <a href="{{ route('properties.index', ['city' => 'لفور']) }}" class="px-3 py-1.5 rounded-xl bg-[#F7F8F9] dark:bg-zinc-800/80 hover:bg-[#ECFDF5] hover:text-[#007A5E] dark:hover:bg-emerald-950/40 text-zinc-600 dark:text-zinc-300 transition-colors">
                    اجاره ویلا در لفور
                </a>
                <a href="{{ route('properties.index', ['property_type' => 'swiss_cottage']) }}" class="px-3 py-1.5 rounded-xl bg-[#F7F8F9] dark:bg-zinc-800/80 hover:bg-[#ECFDF5] hover:text-[#007A5E] dark:hover:bg-emerald-950/40 text-zinc-600 dark:text-zinc-300 transition-colors">
                    کلبه‌های چوبی و سوئیسی
                </a>
                <a href="{{ route('properties.index', ['city' => 'سوادکوه']) }}" class="px-3 py-1.5 rounded-xl bg-[#F7F8F9] dark:bg-zinc-800/80 hover:bg-[#ECFDF5] hover:text-[#007A5E] dark:hover:bg-emerald-950/40 text-zinc-600 dark:text-zinc-300 transition-colors">
                    اقامتگاه‌های سوادکوه
                </a>
                <a href="{{ route('properties.index', ['instant_booking' => '1']) }}" class="px-3 py-1.5 rounded-xl bg-[#F7F8F9] dark:bg-zinc-800/80 hover:bg-[#ECFDF5] hover:text-[#007A5E] dark:hover:bg-emerald-950/40 text-zinc-600 dark:text-zinc-300 transition-colors">
                    اقامتگاه‌های رزرو قطعی آنی
                </a>
                <a href="{{ route('properties.index', ['property_type' => 'eco_lodge']) }}" class="px-3 py-1.5 rounded-xl bg-[#F7F8F9] dark:bg-zinc-800/80 hover:bg-[#ECFDF5] hover:text-[#007A5E] dark:hover:bg-emerald-950/40 text-zinc-600 dark:text-zinc-300 transition-colors">
                    اقامتگاه‌های بوم‌گردی شمال
                </a>
                <a href="{{ route('properties.index', ['city' => 'شیرگاه']) }}" class="px-3 py-1.5 rounded-xl bg-[#F7F8F9] dark:bg-zinc-800/80 hover:bg-[#ECFDF5] hover:text-[#007A5E] dark:hover:bg-emerald-950/40 text-zinc-600 dark:text-zinc-300 transition-colors">
                    اجاره سوئیت در شیرگاه
                </a>
                <a href="{{ route('properties.index', ['has_discount' => '1']) }}" class="px-3 py-1.5 rounded-xl bg-[#F7F8F9] dark:bg-zinc-800/80 hover:bg-[#ECFDF5] hover:text-[#007A5E] dark:hover:bg-emerald-950/40 text-zinc-600 dark:text-zinc-300 transition-colors">
                    اقامتگاه‌های تخفیف‌دار
                </a>
            </div>
        </section>

    </main>

    <!-- ============================================================= -->
    <!-- 5. ALL FILTERS SLIDE-OVER DRAWER / MODAL                      -->
    <!-- ============================================================= -->
    <div id="all-filters-drawer-overlay" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs transition-opacity opacity-0 pointer-events-none duration-300" onclick="closeAllFiltersDrawer()"></div>

    <aside id="all-filters-drawer" class="fixed top-0 bottom-0 left-0 w-full sm:w-[480px] bg-white dark:bg-zinc-900 border-r border-[#ECECEE] dark:border-zinc-800 z-50 shadow-2xl transition-transform duration-300 -translate-x-full flex flex-col justify-between font-sans">
        
        {{-- Drawer Header --}}
        <div class="p-4 sm:p-5 border-b border-[#ECECEE] dark:border-zinc-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-[#007A5E] dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
                <h3 class="text-sm font-black text-[#18181B] dark:text-zinc-100">فیلترهای پیشرفته اقامتگاه</h3>
            </div>
            <button type="button" onclick="closeAllFiltersDrawer()" class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200">
                ✕
            </button>
        </div>

        {{-- Drawer Body / Scrollable Form --}}
        <form id="drawer-filter-form" action="{{ route('properties.index') }}" method="GET" class="p-4 sm:p-6 overflow-y-auto space-y-6 flex-1 text-xs">
            
            {{-- Search & Destination --}}
            <div class="space-y-3">
                <h4 class="font-bold text-zinc-400 text-[11px]">مقصد و مشخصات اولیه</h4>
                <div class="space-y-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="جستجوی نام اقامتگاه، کد ملک یا منطقه..." class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 rounded-xl px-3.5 py-2.5 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none focus:border-[#007A5E]">
                    <select name="city" class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 rounded-xl px-3 py-2.5 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none focus:border-[#007A5E]">
                        <option value="">همه مقاصد و شهرها</option>
                        @foreach($destinationsList as $destGroup)
                            <optgroup label="استان {{ $destGroup['province'] }}">
                                @foreach($destGroup['cities'] as $c)
                                    <option value="{{ $c }}" {{ request('city') == $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Property Types --}}
            @if($rentalModeEnabled)
                <div class="space-y-3 pt-3 border-t border-[#ECECEE] dark:border-zinc-800">
                    <h4 class="font-bold text-zinc-400 text-[11px]">نوع اقامتگاه</h4>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($rentalPropertyTypes as $typeKey => $tConfig)
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9] dark:bg-zinc-800/60 cursor-pointer hover:border-zinc-300">
                                <input type="radio" name="property_type" value="{{ $typeKey }}" {{ request('property_type') == $typeKey ? 'checked' : '' }} class="text-[#007A5E] focus:ring-[#007A5E]">
                                <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300">{{ $tConfig['label'] ?? $typeKey }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Instant Booking & Discount Switches --}}
            <div class="space-y-2 pt-3 border-t border-[#ECECEE] dark:border-zinc-800">
                <label class="flex items-center justify-between p-3.5 rounded-2xl border border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9] dark:bg-zinc-800/60 cursor-pointer transition-colors hover:border-zinc-300 dark:hover:border-zinc-700">
                    <div class="flex items-center gap-2.5">
                        <span class="text-lg">⚡</span>
                        <div class="flex flex-col gap-1">
                            <span class="block text-xs font-black text-[#18181B] dark:text-zinc-100 leading-snug">فقط رزروهای قطعی و آنی</span>
                            <span class="block text-[11px] text-zinc-400 dark:text-zinc-400 font-medium leading-relaxed">بدون نیاز به تأیید تلفنی میزبان</span>
                        </div>
                    </div>
                    <input type="checkbox" name="instant_booking" value="1" {{ (request('instant_booking') == '1' || request('instant_booking') === 'true') ? 'checked' : '' }} class="w-4 h-4 rounded text-[#007A5E] focus:ring-[#007A5E]">
                </label>

                <label class="flex items-center justify-between p-3.5 rounded-2xl border border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9] dark:bg-zinc-800/60 cursor-pointer transition-colors hover:border-zinc-300 dark:hover:border-zinc-700">
                    <div class="flex items-center gap-2.5">
                        <span class="text-lg">🏷️</span>
                        <div class="flex flex-col gap-1">
                            <span class="block text-xs font-black text-[#18181B] dark:text-zinc-100 leading-snug">فقط اقامتگاه‌های تخفیف‌دار</span>
                            <span class="block text-[11px] text-zinc-400 dark:text-zinc-400 font-medium leading-relaxed">دارای تخفیف لحظه آخری یا فصلی</span>
                        </div>
                    </div>
                    <input type="checkbox" name="has_discount" value="1" {{ (request('has_discount') == '1' || request('has_discount') === 'true') ? 'checked' : '' }} class="w-4 h-4 rounded text-[#007A5E] focus:ring-[#007A5E]">
                </label>
            </div>

            {{-- Bedrooms & Capacity --}}
            <div class="space-y-3 pt-3 border-t border-[#ECECEE] dark:border-zinc-800">
                <h4 class="font-bold text-zinc-400 text-[11px]">ظرفیت و خواب</h4>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-zinc-400 mb-1">حداقل مسافران</label>
                        <input type="number" name="guests" value="{{ request('guests') }}" placeholder="مثلاً ۴" class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 rounded-xl px-3 py-2 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none focus:border-[#007A5E] font-sans">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-zinc-400 mb-1">تعداد اتاق خواب</label>
                        <input type="number" name="rooms" value="{{ request('rooms') }}" placeholder="مثلاً ۲" class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 rounded-xl px-3 py-2 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none focus:border-[#007A5E] font-sans">
                    </div>
                </div>
            </div>

            {{-- Price Range Inputs --}}
            <div class="space-y-3 pt-3 border-t border-[#ECECEE] dark:border-zinc-800">
                <h4 class="font-bold text-zinc-400 text-[11px]">محدوده قیمت هر شب ({{ $currLabel }})</h4>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-zinc-400 mb-1">از قیمت ({{ $currLabel }})</label>
                        <input type="text" inputmode="numeric" name="min_price" value="{{ request('min_price') ? number_format($cleanPriceNum(request('min_price'))) : '' }}" oninput="formatPriceInput(this)" placeholder="مثلاً ۱,۰۰۰,۰۰۰" class="price-format-input w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 rounded-xl px-3 py-2 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none focus:border-[#007A5E] font-sans">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-zinc-400 mb-1">تا قیمت ({{ $currLabel }})</label>
                        <input type="text" inputmode="numeric" name="max_price" value="{{ request('max_price') ? number_format($cleanPriceNum(request('max_price'))) : '' }}" oninput="formatPriceInput(this)" placeholder="مثلاً ۵,۰۰۰,۰۰۰" class="price-format-input w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 rounded-xl px-3 py-2 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none focus:border-[#007A5E] font-sans">
                    </div>
                </div>
            </div>

            {{-- Multi-Host Filter (اگر فعال باشد) --}}
            @if($multiHostEnabled && $activeHosts->isNotEmpty())
                <div class="space-y-3 pt-3 border-t border-[#ECECEE] dark:border-zinc-800">
                    <h4 class="font-bold text-zinc-400 text-[11px]">انتخاب میزبان اقامتگاه</h4>
                    <select name="host_id" class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 rounded-xl px-3 py-2.5 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none focus:border-[#007A5E]">
                        <option value="">همه میزبان‌ها</option>
                        @foreach($activeHosts as $h)
                            <option value="{{ $h->id }}" {{ request('host_id') == $h->id ? 'selected' : '' }}>{{ $h->display_name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Dynamic Amenities / Features Filter --}}
            @if(isset($features) && $features->isNotEmpty())
                <div class="space-y-3 pt-3 border-t border-[#ECECEE] dark:border-zinc-800">
                    <h4 class="font-bold text-zinc-400 text-[11px]">امکانات و ویژگی‌های اقامتگاه</h4>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($features as $feature)
                            @php
                                $isChecked = request()->has('features') && is_array(request('features')) && in_array($feature->id, request('features'));
                            @endphp
                            <label class="flex items-center gap-2 p-2 rounded-xl border border-[#ECECEE] dark:border-zinc-800 bg-[#F7F8F9] dark:bg-zinc-800/60 cursor-pointer">
                                <input type="checkbox" name="features[]" value="{{ $feature->id }}" {{ $isChecked ? 'checked' : '' }} class="w-3.5 h-3.5 rounded text-[#007A5E] focus:ring-[#007A5E]">
                                <span class="text-[11px] font-bold text-zinc-700 dark:text-zinc-300 truncate">{{ $feature->label ?? $feature->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Preserve current sort --}}
            <input type="hidden" name="sort" value="{{ request('sort', 'popular') }}">
        </form>

        {{-- Drawer Footer Actions --}}
        <div class="p-4 sm:p-5 border-t border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 flex items-center justify-between gap-3">
            <a href="{{ route('properties.index') }}" class="px-4 py-2.5 rounded-xl border border-rose-200 dark:border-rose-900/60 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 font-bold text-xs hover:bg-rose-100 transition-colors">
                پاکسازی همه
            </a>
            <button type="button" onclick="document.getElementById('drawer-filter-form').submit()" class="flex-1 bg-[#007A5E] hover:bg-[#005F48] text-white py-2.5 rounded-xl font-black text-xs transition-colors shadow-sm">
                اعمال فیلترها ({{ $displayProperties->total() }} اقامتگاه)
            </button>
        </div>

    </aside>

</div>

{{-- JALALI CALENDAR & FAVORITES INTERACTIVE LOGIC --}}
<script>
    // State for Date Selection
    let selectedCheckIn = '{{ request("check_in") }}' || null;
    let selectedCheckOut = '{{ request("check_out") }}' || null;

    const calendarMonthsData = @json($calendarMonths ?? []);

    function toPersianNum(n) {
        if (n === null || n === undefined) return '';
        const p = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return String(n).replace(/[0-9]/g, w => p[+w]);
    }

    // Toggle Dropdowns
    function toggleFilterDropdown(name, event) {
        if (event) {
            event.stopPropagation();
        }
        const menus = ['dest', 'date', 'guests', 'type', 'price'];
        menus.forEach(m => {
            const el = document.getElementById(m + '-dropdown-menu');
            if (el) {
                if (m === name) {
                    el.classList.toggle('hidden');
                } else {
                    el.classList.add('hidden');
                }
            }
        });
    }

    // Close dropdowns on outside click
    document.addEventListener('click', function(e) {
        if (e.target.closest('#dest-dropdown-wrapper') || 
            e.target.closest('#dest-dropdown-menu') ||
            e.target.closest('#date-dropdown-wrapper') || 
            e.target.closest('#date-dropdown-menu') ||
            e.target.closest('#guests-dropdown-wrapper') || 
            e.target.closest('#guests-dropdown-menu') ||
            e.target.closest('#type-dropdown-wrapper') || 
            e.target.closest('#type-dropdown-menu') ||
            e.target.closest('#price-dropdown-wrapper') ||
            e.target.closest('#price-dropdown-menu')) {
            return;
        }
        ['dest', 'date', 'guests', 'type', 'price'].forEach(m => {
            const el = document.getElementById(m + '-dropdown-menu');
            if (el) el.classList.add('hidden');
        });
    });

    // Drawer Open/Close
    function openAllFiltersDrawer(target) {
        const drawer = document.getElementById('all-filters-drawer');
        const overlay = document.getElementById('all-filters-drawer-overlay');
        if (drawer && overlay) {
            drawer.classList.remove('-translate-x-full');
            drawer.classList.add('translate-x-0');
            overlay.classList.remove('opacity-0', 'pointer-events-none');
            overlay.classList.add('opacity-100');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeAllFiltersDrawer() {
        const drawer = document.getElementById('all-filters-drawer');
        const overlay = document.getElementById('all-filters-drawer-overlay');
        if (drawer && overlay) {
            drawer.classList.add('-translate-x-full');
            drawer.classList.remove('translate-x-0');
            overlay.classList.add('opacity-0', 'pointer-events-none');
            overlay.classList.remove('opacity-100');
            document.body.style.overflow = '';
        }
    }

    // Guest Counter
    function adjustGuestCount(delta) {
        const input = document.getElementById('guest-count-input');
        const display = document.getElementById('guest-count-display');
        if (!input || !display) return;
        let val = parseInt(input.value) || 1;
        val += delta;
        if (val < 1) val = 1;
        if (val > 30) val = 30;
        input.value = val;
        display.textContent = toPersianNum(val);
    }

    // Render Jalali Calendar in Popup
    function renderJalaliCalendarGrid() {
        const container = document.getElementById('calendar-days-grid');
        if (!container || calendarMonthsData.length === 0) return;
        container.innerHTML = '';

        const m = calendarMonthsData[0];
        const daysCount = m.daysCount;
        const firstDayOfWeek = m.firstDayOfWeek; // 0=Sat, 6=Fri
        const currentDay = m.currentDay || 1;

        // Empty slots before 1st day
        for (let i = 0; i < firstDayOfWeek; i++) {
            const blank = document.createElement('div');
            blank.className = 'h-8';
            container.appendChild(blank);
        }

        // Days 1..daysCount
        for (let d = 1; d <= daysCount; d++) {
            const dayBtn = document.createElement('button');
            dayBtn.type = 'button';
            const dateStr = `${m.year}/${String(m.month).padStart(2, '0')}/${String(d).padStart(2, '0')}`;

            let isStart = (selectedCheckIn === dateStr);
            let isEnd = (selectedCheckOut === dateStr);
            let isInRange = (selectedCheckIn && selectedCheckOut && dateStr > selectedCheckIn && dateStr < selectedCheckOut);
            let isPast = (m.isCurrentMonth && d < currentDay);

            let baseClass = 'h-8 text-xs font-bold rounded-xl transition-all flex items-center justify-center font-sans ';

            if (isPast) {
                baseClass += 'text-zinc-300 dark:text-zinc-600 cursor-not-allowed';
                dayBtn.disabled = true;
            } else if (isStart || isEnd) {
                // Solid green for check-in & check-out
                baseClass += 'bg-[#007A5E] text-white font-black shadow-sm scale-105';
            } else if (isInRange) {
                // Soft light emerald tint for intermediate days
                baseClass += 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 border border-emerald-200/40';
            } else {
                baseClass += 'text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800';
            }

            dayBtn.className = baseClass;
            dayBtn.textContent = toPersianNum(d);

            if (!isPast) {
                dayBtn.onclick = function(e) {
                    handleDateClick(dateStr, e);
                };
            }

            container.appendChild(dayBtn);
        }
    }

    function handleDateClick(dateStr, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        if (!selectedCheckIn || (selectedCheckIn && selectedCheckOut)) {
            // First click: select Check-in (stays open)
            selectedCheckIn = dateStr;
            selectedCheckOut = null;
            renderJalaliCalendarGrid();
        } else if (selectedCheckIn && !selectedCheckOut) {
            // Second click: select Check-out
            if (dateStr > selectedCheckIn) {
                selectedCheckOut = dateStr;
                renderJalaliCalendarGrid();
                // Automatically apply and close/submit on check-out selection!
                setTimeout(() => {
                    applySelectedDates();
                }, 180);
            } else {
                // Clicked an earlier date -> reset check-in to this date
                selectedCheckIn = dateStr;
                selectedCheckOut = null;
                renderJalaliCalendarGrid();
            }
        }
    }

    // Format price input with 3-digit comma separators and Persian numerals
    function formatPriceInput(el) {
        if (!el) return;
        const p = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        const e = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        let raw = el.value || '';
        let cleaned = String(raw).replace(/[۰-۹]/g, w => e[p.indexOf(w)]);
        cleaned = cleaned.replace(/[^0-9]/g, '');
        if (!cleaned) {
            el.value = '';
            return;
        }
        const num = parseInt(cleaned, 10);
        if (isNaN(num)) {
            el.value = '';
            return;
        }
        el.value = toPersianNum(num.toLocaleString('en-US'));
    }

    function clearSelectedDates() {
        selectedCheckIn = null;
        selectedCheckOut = null;
        renderJalaliCalendarGrid();
    }

    function applySelectedDates() {
        if (!selectedCheckIn) return;
        const url = new URL(window.location.href);
        url.searchParams.set('check_in', selectedCheckIn);
        if (selectedCheckOut) {
            url.searchParams.set('check_out', selectedCheckOut);
        } else {
            url.searchParams.delete('check_out');
        }
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    // LocalStorage Favorites
    function initFavoritesUI() {
        let favs = [];
        try {
            favs = JSON.parse(localStorage.getItem('mrvila_favorites') || '[]');
        } catch(e) {}

        favs.forEach(id => {
            const btns = document.querySelectorAll('.fav-btn-' + id);
            btns.forEach(btn => {
                const svg = btn.querySelector('svg');
                if (svg) {
                    svg.classList.remove('text-zinc-600', 'dark:text-zinc-300', 'fill-transparent');
                    svg.classList.add('text-rose-500', 'fill-rose-500');
                }
            });
        });
    }

    function toggleCardFavorite(propId) {
        let favs = [];
        try {
            favs = JSON.parse(localStorage.getItem('mrvila_favorites') || '[]');
        } catch(e) {}

        const idx = favs.indexOf(propId);
        const btns = document.querySelectorAll('.fav-btn-' + propId);

        if (idx > -1) {
            favs.splice(idx, 1);
            btns.forEach(btn => {
                const svg = btn.querySelector('svg');
                if (svg) {
                    svg.classList.remove('text-rose-500', 'fill-rose-500');
                    svg.classList.add('text-zinc-600', 'dark:text-zinc-300', 'fill-transparent');
                }
            });
        } else {
            favs.push(propId);
            btns.forEach(btn => {
                const svg = btn.querySelector('svg');
                if (svg) {
                    svg.classList.remove('text-zinc-600', 'dark:text-zinc-300', 'fill-transparent');
                    svg.classList.add('text-rose-500', 'fill-rose-500');
                }
            });
        }

        try {
            localStorage.setItem('mrvila_favorites', JSON.stringify(favs));
        } catch(e) {}
    }

    // Mouse Drag-to-Scroll & Touch Navigation for Sorting Bar (RTL Friendly)
    function initSortingDrag() {
        const track = document.getElementById('sorting-track');
        if (!track) return;

        let isDown = false;
        let startX = 0;
        let scrollStart = 0;
        let hasMovedSignificantly = false;
        let velocityX = 0;
        let lastMouseX = 0;
        let lastTime = 0;
        let isTouchMoving = false;

        track.addEventListener('mousedown', (e) => {
            if (e.button !== 0) return;
            isDown = true;
            hasMovedSignificantly = false;
            startX = e.pageX;
            lastMouseX = e.pageX;
            lastTime = performance.now();
            scrollStart = track.scrollLeft;
            velocityX = 0;
            track.classList.add('is-dragging');
            track.style.scrollBehavior = 'auto';
        });

        window.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            const currentX = e.pageX;
            const dist = Math.abs(currentX - startX);
            if (dist > 5) {
                hasMovedSignificantly = true;
                if (e.cancelable) e.preventDefault();
            }
            const walk = currentX - startX;
            track.scrollLeft = scrollStart - walk;

            const now = performance.now();
            const dt = now - lastTime;
            if (dt > 10) {
                velocityX = (currentX - lastMouseX) / dt;
                lastMouseX = currentX;
                lastTime = now;
            }
        });

        window.addEventListener('mouseup', () => {
            if (!isDown) return;
            isDown = false;
            track.classList.remove('is-dragging');

            // Apply momentum glide
            let momentum = velocityX * 180;
            if (Math.abs(momentum) > 10) {
                track.scrollTo({
                    left: track.scrollLeft - momentum,
                    behavior: 'smooth'
                });
            }

            setTimeout(() => {
                track.style.scrollBehavior = 'smooth';
            }, 50);
        });

        // Touch event handling to prevent accidental clicks while swiping
        track.addEventListener('touchstart', () => {
            isTouchMoving = false;
        }, { passive: true });

        track.addEventListener('touchmove', () => {
            isTouchMoving = true;
        }, { passive: true });

        // Prevent pill link click if user was dragging or swiping
        track.querySelectorAll('a.sort-pill').forEach(link => {
            link.addEventListener('click', (e) => {
                if (hasMovedSignificantly || isTouchMoving) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderJalaliCalendarGrid();
        initFavoritesUI();
        initSortingDrag();
        document.querySelectorAll('.price-format-input').forEach(formatPriceInput);
    });
</script>
@endsection
