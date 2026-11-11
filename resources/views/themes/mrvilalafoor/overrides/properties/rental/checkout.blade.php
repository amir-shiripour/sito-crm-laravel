@extends('layouts.web')

@section('hide_header', true)

@section('title', 'پیش‌فاکتور و نهایی‌سازی رزرو - ' . $property->title)

@php
    use Morilog\Jalali\Jalalian;
    use Morilog\Jalali\CalendarUtils;

    // استخراج رنگ سازمانی مستقیماً از بخش «شخصی‌سازی پالت رنگی» در «تنظیمات کلی سیستم» (/settings#general)
    $appThemeSetting = \Modules\Settings\Entities\Setting::where('key', 'app_theme')->value('value') ?: 'properties';
    $themeColorsJson = \Modules\Settings\Entities\Setting::where('key', 'theme_colors')->value('value');
    $savedThemeColors = $themeColorsJson ? (is_string($themeColorsJson) ? json_decode($themeColorsJson, true) : $themeColorsJson) : [];
    $activeColor = $savedThemeColors[$appThemeSetting] ?? ($savedThemeColors['properties'] ?? 'lime');

    $tailwindColorPalette = [
        'indigo'      => ['primary' => '#4f46e5', 'hover' => '#4338ca', 'light' => '#eef2ff', 'light_dark' => 'rgba(79, 70, 229, 0.18)', 'border' => '#c7d2fe'],
        'blue'        => ['primary' => '#2563eb', 'hover' => '#1d4ed8', 'light' => '#eff6ff', 'light_dark' => 'rgba(37, 99, 235, 0.18)', 'border' => '#bfdbfe'],
        'sky'         => ['primary' => '#0ea5e9', 'hover' => '#0284c7', 'light' => '#f0f9ff', 'light_dark' => 'rgba(14, 165, 233, 0.18)', 'border' => '#bae6fd'],
        'cyan'        => ['primary' => '#06b6d4', 'hover' => '#0891b2', 'light' => '#ecfeff', 'light_dark' => 'rgba(6, 182, 212, 0.18)', 'border' => '#a5f3fc'],
        'teal'        => ['primary' => '#0d9488', 'hover' => '#0f766e', 'light' => '#f0fdfa', 'light_dark' => 'rgba(13, 148, 136, 0.18)', 'border' => '#99f6e4'],
        'emerald'     => ['primary' => '#10b981', 'hover' => '#059669', 'light' => '#ecfdf5', 'light_dark' => 'rgba(16, 185, 129, 0.18)', 'border' => '#a7f3d0'],
        'green'       => ['primary' => '#16a34a', 'hover' => '#15803d', 'light' => '#f0fdf4', 'light_dark' => 'rgba(22, 163, 74, 0.18)', 'border' => '#bbf7d0'],
        'lime'        => ['primary' => '#65a30d', 'hover' => '#4d7c0f', 'light' => '#f7fee7', 'light_dark' => 'rgba(101, 163, 13, 0.18)', 'border' => '#d9f99d'],
        'orange'      => ['primary' => '#ea580c', 'hover' => '#c2410c', 'light' => '#fff7ed', 'light_dark' => 'rgba(234, 88, 12, 0.18)', 'border' => '#fed7aa'],
        'amber'       => ['primary' => '#d97706', 'hover' => '#b45309', 'light' => '#fffbeb', 'light_dark' => 'rgba(217, 119, 6, 0.18)', 'border' => '#fde68a'],
        'red'         => ['primary' => '#dc2626', 'hover' => '#b91c1c', 'light' => '#fef2f2', 'light_dark' => 'rgba(220, 38, 38, 0.18)', 'border' => '#fecaca'],
        'rose'        => ['primary' => '#e11d48', 'hover' => '#be123c', 'light' => '#fff1f2', 'light_dark' => 'rgba(225, 29, 72, 0.18)', 'border' => '#fecdd3'],
        'purple'      => ['primary' => '#9333ea', 'hover' => '#7e22ce', 'light' => '#faf5ff', 'light_dark' => 'rgba(147, 51, 234, 0.18)', 'border' => '#e9d5ff'],
        'fuchsia'     => ['primary' => '#c026d3', 'hover' => '#a21caf', 'light' => '#fdf4ff', 'light_dark' => 'rgba(192, 38, 211, 0.18)', 'border' => '#f5d0fe'],
        'pink'        => ['primary' => '#db2777', 'hover' => '#be185d', 'light' => '#fdf2f8', 'light_dark' => 'rgba(219, 39, 119, 0.18)', 'border' => '#fbcfe8'],
        'slate'       => ['primary' => '#475569', 'hover' => '#334155', 'light' => '#f8fafc', 'light_dark' => 'rgba(71, 85, 105, 0.18)', 'border' => '#cbd5e1'],
    ];

    $brandTheme = $tailwindColorPalette[$activeColor] ?? $tailwindColorPalette['lime'];

    $coverUrl = $property->primary_image_url
        ?: ($property->cover_image
            ? (str_starts_with($property->cover_image, 'http')
                ? $property->cover_image
                : asset('storage/' . $property->cover_image))
            : ($property->images && $property->images->first()
                ? (str_starts_with($property->images->first()->path ?? $property->images->first()->image_path, 'http')
                    ? ($property->images->first()->path ?? $property->images->first()->image_path)
                    : asset('storage/' . ($property->images->first()->path ?? $property->images->first()->image_path)))
                : asset('assets/images/placeholder.svg')));
    $checkInTime = $config?->check_in_time ? \Morilog\Jalali\CalendarUtils::convertNumbers(substr($config->check_in_time, 0, 5)) : '۱۴:۰۰';
    $checkOutTime = $config?->check_out_time ? \Morilog\Jalali\CalendarUtils::convertNumbers(substr($config->check_out_time, 0, 5)) : '۱۲:۰۰';

    $nightsCount = (int) ($quote['nights_count'] ?? 1);
    $guestsCount = (int) ($quote['guests_count'] ?? 1);
    $grandTotal = (float) ($quote['grand_total'] ?? 0);
    $prepaymentAmount = (float) ($quote['prepayment_amount'] ?? $grandTotal);
    $isInstant = !empty($quote['instant_booking']);

    $clientName = old('full_name', old('guest_full_name', $client->full_name ?: ($client->first_name ? $client->first_name . ' ' . $client->last_name : '')));
    $clientPhone = $client->phone ?: '';
    $clientNationalCode = old('national_code', old('guest_national_code', $client->national_code ?: ''));

    // بررسی اینکه آیا فیلد الزامی خالی وجود دارد تا فرم مشخصات خودکار باز شود
    $hasMissingRequiredField = false;
    foreach($clientFormFields as $cf) {
        if (!empty($cf['client_auth']) && empty($cf['value'])) {
            $hasMissingRequiredField = true;
            break;
        }
    }
@endphp

@push('styles')
    <style>
        :root {
            --theme-primary: {{ $brandTheme['primary'] }};
            --theme-primary-hover: {{ $brandTheme['hover'] }};
            --theme-primary-light: {{ $brandTheme['light'] }};
            --theme-primary-light-dark: {{ $brandTheme['light_dark'] }};
            --theme-primary-border: {{ $brandTheme['border'] }};
        }
        .btn-theme-primary {
            background-color: var(--theme-primary) !important;
            color: #ffffff !important;
            border: 1px solid var(--theme-primary) !important;
            box-shadow: 0 4px 14px 0 rgba(0, 0, 0, 0.12) !important;
        }
        .btn-theme-primary:hover {
            background-color: var(--theme-primary-hover) !important;
            border-color: var(--theme-primary-hover) !important;
        }
        .text-theme-primary {
            color: var(--theme-primary) !important;
        }
        .border-theme-primary {
            border-color: var(--theme-primary) !important;
        }
        .bg-theme-light {
            background-color: var(--theme-primary-light) !important;
        }
        .dark .bg-theme-light {
            background-color: var(--theme-primary-light-dark) !important;
        }
        .hover-theme-primary:hover {
            border-color: var(--theme-primary) !important;
            color: var(--theme-primary) !important;
        }

                /* مدال‌های استاندارد با بک‌گراند تیره، بلور و قفل اسکرول صفحه */
        [x-cloak] {
            display: none !important;
        }

        .checkout-modal-overlay {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background-color: rgba(0, 0, 0, 0.75) !important;
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            z-index: 99999 !important;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            box-sizing: border-box;
            overflow: hidden;
        }

        .checkout-modal-overlay[style*="display: none"] {
            display: none !important;
        }

        .checkout-modal-card {
            position: relative !important;
            max-height: 90vh !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
            z-index: 100000 !important;
        }

        .checkout-modal-body {
            flex: 1 1 auto !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }

        /* استایل‌های انتخاب بازه تقویم (جهت RTL: ورود در راست و خروج در چپ) */
        .cal-day-checkin {
            background-color: var(--theme-primary) !important;
            border-color: var(--theme-primary) !important;
            color: #ffffff !important;
            font-weight: 900 !important;
            border-top-right-radius: 1rem !important;
            border-bottom-right-radius: 1rem !important;
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            z-index: 10;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .cal-day-checkout {
            background-color: var(--theme-primary) !important;
            border-color: var(--theme-primary) !important;
            color: #ffffff !important;
            font-weight: 900 !important;
            border-top-left-radius: 1rem !important;
            border-bottom-left-radius: 1rem !important;
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            z-index: 10;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .cal-day-single {
            background-color: var(--theme-primary) !important;
            border-color: var(--theme-primary) !important;
            color: #ffffff !important;
            font-weight: 900 !important;
            border-radius: 1rem !important;
            z-index: 10;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .cal-day-range {
            background-color: var(--theme-primary-light) !important;
            border-top: 1px solid var(--theme-primary-border) !important;
            border-bottom: 1px solid var(--theme-primary-border) !important;
            border-left: 0 !important;
            border-right: 0 !important;
            color: var(--theme-primary) !important;
            font-weight: 700 !important;
            border-radius: 0 !important;
        }
        .dark .cal-day-range {
            background-color: var(--theme-primary-light-dark) !important;
            border-top-color: var(--theme-primary-border) !important;
            border-bottom-color: var(--theme-primary-border) !important;
            color: #ffffff !important;
        }
    </style>
@endpush

@section('content')
    {{-- هدر اختصاصی و مینیمال صفحه پیش‌فاکتور (مشابه نمونه جاباما) --}}
    <header class="sticky top-0 z-40 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-md border-b border-[#ECECEE] dark:border-zinc-800 transition-colors duration-200">
        <div class="max-w-[1360px] mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between font-sans">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-theme-light flex items-center justify-center text-theme-primary shadow-2xs">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
                <div>
                    <span class="text-sm sm:text-base font-black text-gray-900 dark:text-white block">{{ $appName }}</span>
                    <span class="text-[10px] text-gray-400 block font-medium">پیش‌فاکتور و نهایی‌سازی رزرو اقامتگاه</span>
                </div>
            </div>

            <a href="{{ route('properties.show', $property->slug ?: $property->code) }}" 
               class="inline-flex items-center gap-2 text-xs sm:text-sm font-bold text-zinc-600 dark:text-zinc-300 hover:text-black dark:hover:text-white transition-colors">
                <span>بازگشت به اقامتگاه</span>
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>
    </header>

    <div class="max-w-[1360px] mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-8 w-full font-sans pb-28 lg:pb-12"
         x-data="rentalCheckoutApp(
             @js($config),
             @js($quote),
             @js($calendarData),
             @js($clientFormFields),
             '{{ $property->slug ?: $property->code }}',
             '{{ route('properties.quote', $property->slug ?: $property->code) }}',
             '{{ route('properties.book', $property->slug ?: $property->code) }}',
             '{{ $currencyLabel }}',
             '{{ addslashes($clientName) }}',
             '{{ addslashes($clientPhone) }}',
             '{{ addslashes($clientNationalCode) }}',
             {{ $hasMissingRequiredField ? 'true' : 'false' }}
         )">

        {{-- Validation Error Alerts --}}
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-200 text-xs sm:text-sm space-y-1">
                <div class="font-bold flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span>لطفاً خطاهای زیر را برطرف نمایید:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 pt-1 text-xs pr-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- MAIN CHECKOUT FORM --}}
        <form id="checkout-booking-form" method="POST" action="{{ route('properties.book', $property->slug ?: $property->code) }}" @submit.prevent="submitBookingForm($event)">
            @csrf
            <input type="hidden" name="check_in" :value="checkIn">
            <input type="hidden" name="check_out" :value="checkOut">
            <input type="hidden" name="guests" :value="guests">
            <input type="hidden" name="check_in_date" :value="checkIn">
            <input type="hidden" name="check_out_date" :value="checkOut">
            <input type="hidden" name="guests_count" :value="guests">

            {{-- TWO-COLUMN DESKTOP LAYOUT (Right: Form Details / Left: Invoice Sidebar) --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                {{-- RIGHT COLUMN (8 cols): BOOKING & GUEST DETAILS --}}
                <div class="lg:col-span-8 space-y-6">

                    {{-- 1. Property Summary Box --}}
                    <div class="p-5 sm:p-6 rounded-3xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 shadow-2xs space-y-5">
                        
                        {{-- Property Title & Location --}}
                        <div class="flex items-center gap-4">
                            @if(!empty($coverUrl))
                                <img src="{{ $coverUrl }}" 
                                     alt="{{ $property->title }}" 
                                     class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover shrink-0 shadow-2xs">
                            @endif
                            <div class="min-w-0 flex-1">
                                <h1 class="text-sm sm:text-base font-black text-[#18181B] dark:text-zinc-100 truncate">
                                    {{ $property->title }}
                                </h1>
                                <p class="text-xs text-zinc-400 truncate font-sans mt-0.5">
                                    {{ $property->city ?? 'مازندران، لفور' }}
                                    @if($property->category)
                                        • {{ $property->category->name }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Dates & Guests Editable Rows --}}
                        <div class="border-t border-gray-100 dark:border-zinc-800 pt-4 space-y-3 font-sans text-xs">
                            
                            {{-- Row: Travel Dates --}}
                            <div class="flex items-center justify-between gap-2 p-3 rounded-2xl bg-[#F7F8F9] dark:bg-zinc-800/60 border border-transparent hover:border-gray-200 dark:hover:border-zinc-700 transition">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-800 flex items-center justify-center text-zinc-600 dark:text-zinc-300 shadow-2xs shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="truncate">
                                        <span class="text-[10px] text-zinc-400 block font-medium">تاریخ سفر</span>
                                        <span class="font-bold text-zinc-800 dark:text-zinc-100 text-xs sm:text-sm" x-text="formatDateRangeDisplay()">
                                            {{ $quote['check_in_jalali'] }} تا {{ $quote['check_out_jalali'] }} ({{ $nightsCount }} شب)
                                        </span>
                                    </div>
                                </div>
                                <button type="button" 
                                        @click="openCalendarModal()" 
                                        class="px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover-theme-primary font-bold text-xs transition-colors shrink-0 shadow-2xs cursor-pointer">
                                    ویرایش
                                </button>
                            </div>

                            {{-- Row: Guests Count --}}
                            <div class="flex items-center justify-between gap-2 p-3 rounded-2xl bg-[#F7F8F9] dark:bg-zinc-800/60 border border-transparent hover:border-gray-200 dark:hover:border-zinc-700 transition">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-800 flex items-center justify-center text-zinc-600 dark:text-zinc-300 shadow-2xs shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                    <div class="truncate">
                                        <span class="text-[10px] text-zinc-400 block font-medium">تعداد مسافران</span>
                                        <span class="font-bold text-zinc-800 dark:text-zinc-100 text-xs sm:text-sm">
                                            <span x-text="formatNumber(guests)">{{ $guestsCount }}</span> نفر
                                        </span>
                                    </div>
                                </div>
                                <button type="button" 
                                        @click="guestsModalOpen = true; tempGuests = guests" 
                                        class="px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover-theme-primary font-bold text-xs transition-colors shrink-0 shadow-2xs cursor-pointer">
                                    ویرایش
                                </button>
                            </div>

                            {{-- Quick Actions: Property Details & Chat With Host --}}
                            <div class="grid grid-cols-2 gap-3 pt-1">
                                <button type="button" 
                                        @click="propertyDetailsModalOpen = true" 
                                        class="py-2.5 px-3 rounded-2xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 font-bold text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>جزئیات اقامتگاه</span>
                                </button>

                                <button type="button" 
                                        @click="chatModalOpen = true" 
                                        class="py-2.5 px-3 rounded-2xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 font-bold text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    <span>چت با میزبان</span>
                                </button>
                            </div>

                        </div>

                    </div>

                    {{-- 2. Client Profile / Reservation Holder Details Box (Direct Form, No Separate Button) --}}
                    <div class="p-5 sm:p-6 rounded-3xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 shadow-2xs space-y-4 font-sans" id="client-info-section">
                        
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-zinc-800">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-theme-light text-theme-primary flex items-center justify-center font-bold text-sm">
                                    👤
                                </div>
                                <div>
                                    <h3 class="font-black text-sm sm:text-base text-[#18181B] dark:text-zinc-100">
                                        اطلاعات تحویل‌گیرنده اقامتگاه
                                    </h3>
                                    <p class="text-[11px] text-zinc-400 mt-0.5">
                                        اطلاعات شخص مسافر یا سرپرست جهت صدور واچر و تحویل اقامتگاه
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Form Builder Inputs directly in the card --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            @foreach($clientFormFields as $field)
                                @php
                                    $fieldId = $field['id'];
                                    $fieldLabel = $field['label'];
                                    $fieldType = $field['type'] ?? 'text';
                                    $isRequired = !empty($field['required']) || !empty($field['client_auth']);
                                    $isLocked = !empty($field['locked']);
                                    $val = old($fieldId, old('guest_' . $fieldId, $field['value'] ?? ''));
                                @endphp

                                <div class="{{ in_array($fieldType, ['textarea', 'address']) ? 'sm:col-span-2' : '' }}">
                                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1.5 font-sans">
                                        {{ $fieldLabel }}
                                        @if($isRequired)
                                            <span class="text-rose-500 font-black">*</span>
                                        @endif
                                    </label>

                                    @if($isLocked)
                                        <div class="relative">
                                            <input type="text" 
                                                   value="{{ $val }}" 
                                                   readonly 
                                                   disabled
                                                   class="w-full bg-gray-100 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl px-3.5 py-2.5 text-xs text-zinc-600 dark:text-zinc-300 cursor-not-allowed font-sans select-none pl-20">
                                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 px-2 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold border border-emerald-200/60 dark:border-emerald-800/40">
                                                احراز شده
                                            </span>
                                            <input type="hidden" name="{{ $fieldId }}" value="{{ $val }}">
                                        </div>
                                    @elseif($fieldType === 'textarea' || $fieldType === 'address')
                                        <textarea name="{{ $fieldId }}" 
                                                  id="field-{{ $fieldId }}"
                                                  rows="2"
                                                  placeholder="{{ $field['placeholder'] ?? '' }}"
                                                  :class="validationErrors['{{ $fieldId }}'] ? '!border-rose-500 ring-1 ring-rose-500' : ''"
                                                  class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 focus:border-[var(--theme-primary)] rounded-xl p-3 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none font-sans transition-colors">{{ $val }}</textarea>
                                    @elseif($fieldType === 'select')
                                        <select name="{{ $fieldId }}" 
                                                id="field-{{ $fieldId }}"
                                                :class="validationErrors['{{ $fieldId }}'] ? '!border-rose-500 ring-1 ring-rose-500' : ''"
                                                class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 focus:border-[var(--theme-primary)] rounded-xl px-3.5 py-2.5 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none font-sans transition-colors">
                                            <option value="">انتخاب نمایید...</option>
                                            @foreach($field['options'] ?? [] as $opt)
                                                <option value="{{ $opt }}" {{ $val == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="{{ in_array($fieldType, ['number', 'tel']) ? 'text' : $fieldType }}" 
                                               name="{{ $fieldId }}" 
                                               id="field-{{ $fieldId }}"
                                               value="{{ $val }}"
                                               placeholder="{{ $field['placeholder'] ?? '' }}"
                                               @if($fieldId === 'full_name' || $fieldId === 'name') x-model="supervisorName" @endif
                                               @if($fieldId === 'national_code') x-model="supervisorNationalCode" @endif
                                               :class="validationErrors['{{ $fieldId }}'] ? '!border-rose-500 ring-1 ring-rose-500' : ''"
                                               class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 focus:border-[var(--theme-primary)] rounded-xl px-3.5 py-2.5 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none font-sans transition-colors">
                                    @endif

                                    <template x-if="validationErrors['{{ $fieldId }}']">
                                        <span class="text-[11px] text-rose-500 font-bold mt-1 block" x-text="validationErrors['{{ $fieldId }}']"></span>
                                    </template>
                                </div>
                            @endforeach
                        </div>

                    </div>

                    {{-- 3. Important Property Rules (Matching rental/show.blade.php) --}}
                    @if(!empty($houseRulesStructured))
                        <div class="p-5 sm:p-6 rounded-3xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 shadow-2xs space-y-4 font-sans">
                            <h2 class="text-sm sm:text-base font-black text-[#18181B] dark:text-zinc-100">
                                مقررات مهم اقامتگاه
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                @foreach($houseRulesStructured as $rule)
                                    <div class="flex items-center justify-between p-3 rounded-2xl bg-[#F7F8F9] dark:bg-zinc-800/60 border border-transparent text-xs">
                                        <span class="text-zinc-700 dark:text-zinc-300 font-medium">{{ $rule['title'] }}</span>
                                        <span class="font-bold {{ $rule['status_type'] === 'emerald' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                            {{ $rule['status_text'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- 4. Cancellation Policy Box (Only shown if defined on property) --}}
                    @if(!empty($cancellationPolicy))
                        <div class="p-5 sm:p-6 rounded-3xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 shadow-2xs space-y-3 font-sans">
                            <h2 class="text-sm sm:text-base font-black text-[#18181B] dark:text-zinc-100 flex items-center gap-2">
                                <span>قوانین لغو رزرو</span>
                            </h2>
                            <div class="text-xs text-zinc-600 dark:text-zinc-300 leading-relaxed font-sans whitespace-pre-line p-3.5 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/40 text-amber-900 dark:text-amber-200">
                                {{ $cancellationPolicy }}
                            </div>
                        </div>
                    @endif

                    {{-- 5. Message To Host Box --}}
                    <div class="p-5 sm:p-6 rounded-3xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 shadow-2xs space-y-3 font-sans">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm sm:text-base font-black text-[#18181B] dark:text-zinc-100">
                                پیام به میزبان
                            </h2>
                            <span class="text-[11px] text-zinc-400 font-medium">(اختیاری)</span>
                        </div>
                        <p class="text-[11px] text-zinc-400 leading-relaxed">
                            اگر سوالی داری یا نکته‌ای هست که میزبان باید بدونه، اینجا بنویس. پیامت همراه درخواست رزرو برای میزبان ارسال میشه.
                        </p>
                        <div class="relative">
                            <textarea name="guest_notes" 
                                      x-model="notes"
                                      rows="3" 
                                      maxlength="1000"
                                      placeholder="متن پیام برای میزبان..."
                                      class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 focus:border-[var(--theme-primary)] rounded-2xl p-4 text-xs font-medium text-[#18181B] dark:text-zinc-100 outline-none resize-none font-sans"></textarea>
                            <span class="absolute bottom-3 left-4 text-[10px] text-zinc-400 font-sans" x-text="formatNumber(notes.length) + ' / ۱۰۰۰'">
                                ۰ / ۱۰۰۰
                            </span>
                        </div>
                    </div>

                </div>

                {{-- LEFT COLUMN (4 cols): INVOICE BREAKDOWN & CTAS (STICKY ON DESKTOP) --}}
                <div class="lg:col-span-4 space-y-4 lg:sticky lg:top-24">
                    
                    {{-- Invoice Box --}}
                    <div class="p-5 sm:p-6 rounded-3xl bg-white dark:bg-zinc-900 border border-[#ECECEE] dark:border-zinc-800 shadow-sm space-y-5 font-sans">
                        
                        <h2 class="text-sm font-black text-[#18181B] dark:text-zinc-100 pb-3 border-b border-[#ECECEE] dark:border-zinc-800">
                            جزئیات پرداخت
                        </h2>

                        {{-- Line Items --}}
                        <div class="space-y-3 text-xs font-sans">
                            
                            {{-- Base Nights Fee --}}
                            <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                                <span>
                                    <span x-text="formatNumber(nightsCount)">{{ $nightsCount }}</span> شب × 
                                    <span x-text="formatNumber(pricePerNightDisplay()) + ' ' + currencyLabel"></span>
                                </span>
                                <span class="font-bold text-[#18181B] dark:text-zinc-200" x-text="formatNumber(nightsTotalDisplay()) + ' ' + currencyLabel">
                                    {{ number_format($quote['nights_total'] ?? 0) }} {{ $currencyLabel }}
                                </span>
                            </div>

                            {{-- Discounts if applicable --}}
                            <template x-if="totalDiscountDisplay() > 0">
                                <div class="flex items-center justify-between text-emerald-600 dark:text-emerald-400 font-bold">
                                    <span>تخفیف اقامتگاه:</span>
                                    <span>- <span x-text="formatNumber(totalDiscountDisplay()) + ' ' + currencyLabel"></span></span>
                                </div>
                            </template>

                            {{-- Extra Guests Fee if applicable --}}
                            <template x-if="extraGuestsFeeDisplay() > 0">
                                <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                                    <span>هزینه <span x-text="formatNumber(extraGuestsCountDisplay())"></span> نفر اضافه:</span>
                                    <span class="font-bold text-[#18181B] dark:text-zinc-200" x-text="formatNumber(extraGuestsFeeDisplay()) + ' ' + currencyLabel"></span>
                                </div>
                            </template>

                            <template x-if="cleaningFeeDisplay() > 0">
                                <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                                    <span>هزینه نظافت:</span>
                                    <span class="font-bold text-[#18181B] dark:text-zinc-200" x-text="formatNumber(cleaningFeeDisplay()) + ' ' + currencyLabel"></span>
                                </div>
                            </template>
                        </div>

                        {{-- Total Pay Amount --}}
                        <div class="pt-4 border-t border-[#ECECEE] dark:border-zinc-800 flex items-center justify-between text-sm font-black text-[#18181B] dark:text-zinc-100">
                            <span>قابل پرداخت نهایی:</span>
                            <span class="text-base sm:text-lg text-theme-primary font-black font-sans" x-text="formatNumber(grandTotalDisplay()) + ' ' + currencyLabel">
                                {{ number_format($grandTotal) }} {{ $currencyLabel }}
                            </span>
                        </div>

                        {{-- Prepayment Note if applicable --}}
                        <template x-if="prepaymentAmountDisplay() < grandTotalDisplay()">
                            <div class="flex items-center justify-between text-xs text-amber-700 dark:text-amber-400 pt-1 font-bold">
                                <span>پیش‌پرداخت آنلاین:</span>
                                <span x-text="formatNumber(prepaymentAmountDisplay()) + ' ' + currencyLabel"></span>
                            </div>
                        </template>

                        {{-- Need More Help Button --}}
                        @if(!empty($supportPhone))
                            <button type="button" 
                                    @click="supportModalOpen = true" 
                                    class="w-full py-2.5 px-3 rounded-2xl bg-zinc-50 hover:bg-zinc-100 dark:bg-zinc-800/60 dark:hover:bg-zinc-800 border border-zinc-200/60 dark:border-zinc-700/60 text-zinc-700 dark:text-zinc-300 font-bold text-xs flex items-center justify-center gap-2 transition-colors cursor-pointer">
                                <span>🎧</span>
                                <span>راهنمایی بیشتری نیاز دارید؟</span>
                            </button>
                        @endif

                        {{-- Validation Global Error Alert --}}
                        <template x-if="formErrorMessage">
                            <div class="p-3 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-300 text-xs font-bold flex items-start gap-2">
                                <span class="shrink-0 text-sm">⚠️</span>
                                <span x-text="formErrorMessage"></span>
                            </div>
                        </template>

                        {{-- Final Submit Action Button (Desktop) --}}
                        <div class="pt-2">
                            <button type="submit" 
                                    :disabled="isSubmitting"
                                    class="btn-theme-primary w-full py-4 rounded-2xl font-black text-xs sm:text-sm transition-all flex items-center justify-center gap-2 active:scale-[0.99] cursor-pointer">
                                <span x-show="!isSubmitting">{{ $isInstant ? 'تأیید و پرداخت نهایی رزرو' : 'ثبت درخواست رزرو' }}</span>
                                <span x-show="isSubmitting" style="display: none;" class="flex items-center gap-2">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    در حال ثبت...
                                </span>
                            </button>
                        </div>

                    </div>

                </div>

            </div>

            {{-- MOBILE STICKY BOTTOM ACTION BAR (< lg) --}}
            <div class="lg:hidden fixed bottom-0 inset-x-0 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border-t border-[#ECECEE] dark:border-zinc-800 px-4 py-3 z-40 shadow-2xl font-sans">
                <div class="flex items-center justify-between gap-4 max-w-md mx-auto">
                    <div>
                        <span class="text-[10px] text-zinc-400 block font-medium">قابل پرداخت:</span>
                        <div class="text-sm font-black text-[#18181B] dark:text-zinc-100 font-sans" x-text="formatNumber(grandTotalDisplay()) + ' ' + currencyLabel">
                            {{ number_format($grandTotal) }} {{ $currencyLabel }}
                        </div>
                    </div>

                    <button type="submit" 
                            :disabled="isSubmitting"
                            class="btn-theme-primary px-6 py-3.5 rounded-2xl font-bold text-xs transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                        <span x-show="!isSubmitting">{{ $isInstant ? 'پرداخت آنلاین' : 'ثبت درخواست رزرو' }}</span>
                        <span x-show="isSubmitting" style="display: none;">در حال ثبت...</span>
                    </button>
                </div>
            </div>

        </form>

        {{-- ============================================================= --}}
        {{-- MODAL 1: INTERACTIVE JALALI CALENDAR MODAL (EXACT rental/show) --}}
        {{-- ============================================================= --}}
        <div x-cloak x-show="calendarModalOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="checkout-modal-overlay"
                 style="display: none;">
                
                <div class="checkout-modal-card bg-white dark:bg-zinc-900 rounded-3xl max-w-4xl w-full p-4 sm:p-6 border border-gray-200 dark:border-zinc-800 font-sans space-y-4"
                     @click.outside="calendarModalOpen = false">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-zinc-800 shrink-0">
                    <div>
                        <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">انتخاب تاریخ سفر</h3>
                        <p class="text-[11px] text-zinc-400 mt-0.5">تاریخ ورود و خروج مورد نظر خود را با لمس روزها انتخاب نمایید.</p>
                    </div>
                    <button type="button" 
                            @click="calendarModalOpen = false" 
                            class="w-8 h-8 rounded-full bg-gray-100 dark:bg-zinc-800 flex items-center justify-center text-gray-500 hover:text-black dark:hover:text-white font-bold transition cursor-pointer">
                        ✕
                    </button>
                </div>

                {{-- Top Selection Status Pills (Persian Jalali dates) --}}
                <div class="grid grid-cols-3 gap-2 bg-[#F7F8F9] dark:bg-zinc-800/60 p-2.5 rounded-2xl shrink-0 text-center text-xs">
                    <div class="p-2 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200/60 dark:border-zinc-700/60 shadow-2xs">
                        <span class="text-[10px] text-gray-400 block mb-0.5">تاریخ ورود</span>
                        <span class="font-bold text-gray-900 dark:text-white font-sans" x-text="formatDatePill(tempCheckIn)">--</span>
                    </div>
                    <div class="p-2 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200/60 dark:border-zinc-700/60 shadow-2xs">
                        <span class="text-[10px] text-gray-400 block mb-0.5">تاریخ خروج</span>
                        <span class="font-bold text-gray-900 dark:text-white font-sans" x-text="formatDatePill(tempCheckOut)">--</span>
                    </div>
                    <div class="p-2 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200/60 dark:border-zinc-700/60 shadow-2xs">
                        <span class="text-[10px] text-gray-400 block mb-0.5">مدت اقامت</span>
                        <span class="font-bold text-gray-900 dark:text-white font-sans" x-text="tempNightsCount > 0 ? (formatNumber(tempNightsCount) + ' شب') : '--'">--</span>
                    </div>
                </div>

                {{-- Calendar Body (Scrollable) --}}
                <div class="checkout-modal-body flex-1 overflow-y-auto px-1 py-2 space-y-6">
                    
                    {{-- آ) نسخه موبایل: نمایش تک‌ماه با نوار ناوبری (دقیقاً مشابه show.blade.php) --}}
                    <div class="md:hidden">
                        {{-- نوار ناوبری ماه در موبایل --}}
                        <div class="flex items-center justify-between mb-4">
                            <button type="button" 
                                    @click="prevMonthMobile()" 
                                    :disabled="mobileMonthIndex <= 0"
                                    :class="mobileMonthIndex <= 0 ? 'opacity-25 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-800 dark:text-gray-200'"
                                    class="w-10 h-10 rounded-full flex items-center justify-center border border-gray-200 dark:border-zinc-700 transition font-bold cursor-pointer"
                                    title="ماه قبل">
                                <svg class="w-5 h-5 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                            </button>

                            @foreach($calendarData['months'] ?? [] as $mIdx => $m)
                                <div x-show="mobileMonthIndex === {{ $mIdx }}" 
                                     class="px-6 py-2 rounded-full bg-gray-100 dark:bg-zinc-800 text-sm font-black text-gray-900 dark:text-white font-sans shadow-2xs">
                                    {{ $m['month_title'] ?? (($m['month_name'] ?? '') . ' ' . ($m['year'] ?? '')) }}
                                </div>
                            @endforeach

                            <button type="button" 
                                    @click="nextMonthMobile()" 
                                    :disabled="mobileMonthIndex >= {{ count($calendarData['months'] ?? []) - 1 }}"
                                    :class="mobileMonthIndex >= {{ count($calendarData['months'] ?? []) - 1 }} ? 'opacity-25 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-800 dark:text-gray-200'"
                                    class="w-10 h-10 rounded-full flex items-center justify-center border border-gray-200 dark:border-zinc-700 transition font-bold cursor-pointer"
                                    title="ماه بعد">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                            </button>
                        </div>

                        {{-- شبکه‌های ماه‌ها در موبایل --}}
                        @foreach($calendarData['months'] ?? [] as $mIdx => $m)
                            <div x-show="mobileMonthIndex === {{ $mIdx }}" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                                <div class="grid grid-cols-7 gap-1 text-center text-xs font-bold text-gray-400 dark:text-gray-500 py-2 border-b border-gray-200 dark:border-zinc-800 mb-3">
                                    <span>ش</span><span>ی</span><span>د</span><span>س</span><span>چ</span><span>پ</span><span class="text-rose-500">ج</span>
                                </div>

                                <div class="grid grid-cols-7 gap-1 text-center text-xs">
                                    @for($e = 0; $e < ($m['empty_cells_start'] ?? ($m['first_day_of_week'] ?? 0)); $e++)
                                        <div class="h-14"></div>
                                    @endfor

                                    @foreach($m['days'] ?? [] as $day)
                                        @php
                                            $isPast = !empty($day['is_past']);
                                            $isBlocked = !empty($day['is_blocked']);
                                            $iso = $day['carbon_date'];
                                            $dayNum = $day['day_num'] ?? ($day['day'] ?? '');
                                            $priceVal = !empty($day['price']) ? (float) $day['price'] : 0;
                                            $priceThousands = $priceVal > 0 ? number_format(round($priceVal / 1000)) : '';
                                            $isHoliday = !empty($day['is_holiday']);
                                            $isFriday = ($day['day_of_week'] ?? 0) == 6;
                                            $isHolidayOrFriday = $isHoliday || $isFriday;
                                            $isPeakOrWeekend = in_array($day['price_type'] ?? '', ['weekend', 'holiday', 'custom']) || !empty($day['is_weekend']) || !empty($day['is_holiday']);
                                        @endphp

                                        @if($isPast)
                                            <div class="h-14 flex items-center justify-center text-sm font-sans {{ $isHolidayOrFriday ? 'text-rose-300 dark:text-rose-900/60' : 'text-gray-300 dark:text-zinc-600' }}">
                                                {{ $dayNum }}
                                            </div>
                                        @else
                                            <button type="button" 
                                                    @if(!$isBlocked) @click="selectCalendarDate('{{ $iso }}')" @endif
                                                    :class="getDayClasses('{{ $iso }}', false, {{ $isBlocked ? 'true' : 'false' }})"
                                                    class="relative rounded-2xl border border-dashed border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 hover:border-[var(--theme-primary)] transition-all flex flex-col items-center justify-center py-1 px-0.5 min-h-[58px] font-sans shadow-2xs {{ $isBlocked ? 'opacity-35 cursor-not-allowed bg-gray-50 dark:bg-zinc-800/40' : 'cursor-pointer' }}">
                                                
                                                @if(!$isBlocked && $isPeakOrWeekend)
                                                    <span :class="(tempCheckIn === '{{ $iso }}' || tempCheckOut === '{{ $iso }}') ? 'hidden' : ''" 
                                                          class="absolute top-0 right-0 w-2.5 h-2.5 bg-amber-400 rounded-bl-md"></span>
                                                @endif

                                                <span class="text-xs sm:text-sm font-black font-sans {{ $isHolidayOrFriday ? 'text-rose-600 dark:text-rose-400' : 'text-gray-800 dark:text-gray-100' }}"
                                                      :class="(tempCheckIn === '{{ $iso }}' || tempCheckOut === '{{ $iso }}') ? '!text-white' : ''">
                                                    {{ $dayNum }}
                                                </span>

                                                @if($isBlocked)
                                                    <span class="text-[9px] text-gray-400 line-through mt-0.5">مسدود</span>
                                                @elseif($priceVal > 0)
                                                    <span class="text-[10px] sm:text-xs font-black font-sans mt-0.5 tracking-tight text-gray-900 dark:text-white"
                                                          :class="(tempCheckIn === '{{ $iso }}' || tempCheckOut === '{{ $iso }}') ? '!text-white' : ''">
                                                        {{ $priceThousands }}
                                                    </span>
                                                @endif
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- ب) نسخه دسکتاپ: نمایش دو ماه در کنار هم با کلیدهای ورق‌زدن (دقیقاً مشابه show.blade.php) --}}
                    <div class="hidden md:block">
                        <div class="grid grid-cols-2 gap-6 lg:gap-8">
                            {{-- ستون راست: ماه اول --}}
                            <div class="space-y-3">
                                @foreach($calendarData['months'] ?? [] as $mIdx => $m)
                                    @if($mIdx < count($calendarData['months'] ?? []) - 1)
                                        <div x-show="desktopMonthIndex === {{ $mIdx }}">
                                            <div class="flex items-center justify-between mb-3 px-1">
                                                <button type="button" 
                                                        @click="prevMonthDesktop()" 
                                                        :disabled="desktopMonthIndex <= 0"
                                                        :class="desktopMonthIndex <= 0 ? 'opacity-20 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-800 dark:text-gray-200'"
                                                        class="w-9 h-9 rounded-full flex items-center justify-center border border-gray-200 dark:border-zinc-700 transition font-bold cursor-pointer"
                                                        title="ماه قبل">
                                                    <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                                                </button>
                                                <span class="text-sm sm:text-base font-black text-gray-900 dark:text-white font-sans">
                                                    {{ $m['month_title'] ?? (($m['month_name'] ?? '') . ' ' . ($m['year'] ?? '')) }}
                                                </span>
                                                <div class="w-9"></div>
                                            </div>

                                            <div class="grid grid-cols-7 gap-1 text-center text-xs font-bold text-gray-400 dark:text-gray-500 py-2 border-b border-gray-200 dark:border-zinc-800 mb-3">
                                                <span>ش</span><span>ی</span><span>د</span><span>س</span><span>چ</span><span>پ</span><span class="text-rose-500">ج</span>
                                            </div>

                                            <div class="grid grid-cols-7 gap-1.5 text-center text-xs">
                                                @for($e = 0; $e < ($m['empty_cells_start'] ?? ($m['first_day_of_week'] ?? 0)); $e++)
                                                    <div class="h-16"></div>
                                                @endfor

                                                @foreach($m['days'] ?? [] as $day)
                                                    @php
                                                        $isPast = !empty($day['is_past']);
                                                        $isBlocked = !empty($day['is_blocked']);
                                                        $iso = $day['carbon_date'];
                                                        $dayNum = $day['day_num'] ?? ($day['day'] ?? '');
                                                        $priceVal = !empty($day['price']) ? (float) $day['price'] : 0;
                                                        $priceThousands = $priceVal > 0 ? number_format(round($priceVal / 1000)) : '';
                                                        $isHoliday = !empty($day['is_holiday']);
                                                        $isFriday = ($day['day_of_week'] ?? 0) == 6;
                                                        $isHolidayOrFriday = $isHoliday || $isFriday;
                                                        $isPeakOrWeekend = in_array($day['price_type'] ?? '', ['weekend', 'holiday', 'custom']) || !empty($day['is_weekend']) || !empty($day['is_holiday']);
                                                    @endphp

                                                    @if($isPast)
                                                        <div class="h-16 flex items-center justify-center text-sm font-sans {{ $isHolidayOrFriday ? 'text-rose-300 dark:text-rose-900/60' : 'text-gray-300 dark:text-zinc-600' }}">
                                                            {{ $dayNum }}
                                                        </div>
                                                    @else
                                                        <button type="button" 
                                                                @if(!$isBlocked) @click="selectCalendarDate('{{ $iso }}')" @endif
                                                                :class="getDayClasses('{{ $iso }}', false, {{ $isBlocked ? 'true' : 'false' }})"
                                                                class="relative rounded-2xl border border-dashed border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 hover:border-[var(--theme-primary)] transition-all flex flex-col items-center justify-center py-1.5 px-0.5 min-h-[62px] font-sans shadow-2xs {{ $isBlocked ? 'opacity-35 cursor-not-allowed bg-gray-50 dark:bg-zinc-800/40' : 'cursor-pointer' }}">
                                                            
                                                            @if(!$isBlocked && $isPeakOrWeekend)
                                                                <span :class="(tempCheckIn === '{{ $iso }}' || tempCheckOut === '{{ $iso }}') ? 'hidden' : ''" 
                                                                      class="absolute top-0 right-0 w-2.5 h-2.5 bg-amber-400 rounded-bl-md"></span>
                                                            @endif

                                                            <span class="text-xs sm:text-sm font-black font-sans {{ $isHolidayOrFriday ? 'text-rose-600 dark:text-rose-400' : 'text-gray-800 dark:text-gray-100' }}"
                                                                  :class="(tempCheckIn === '{{ $iso }}' || tempCheckOut === '{{ $iso }}') ? '!text-white' : ''">
                                                                {{ $dayNum }}
                                                            </span>

                                                            @if($isBlocked)
                                                                <span class="text-[9px] text-gray-400 line-through mt-0.5">مسدود</span>
                                                            @elseif($priceVal > 0)
                                                                <span class="text-[10px] sm:text-xs font-black font-sans mt-0.5 tracking-tight text-gray-900 dark:text-white"
                                                                      :class="(tempCheckIn === '{{ $iso }}' || tempCheckOut === '{{ $iso }}') ? '!text-white' : ''">
                                                                    {{ $priceThousands }}
                                                                </span>
                                                            @endif
                                                        </button>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            {{-- ستون چپ: ماه دوم --}}
                            <div class="space-y-3">
                                @foreach($calendarData['months'] ?? [] as $mIdx => $m)
                                    @if($mIdx > 0)
                                        <div x-show="desktopMonthIndex + 1 === {{ $mIdx }}">
                                            <div class="flex items-center justify-between mb-3 px-1">
                                                <div class="w-9"></div>
                                                <span class="text-sm sm:text-base font-black text-gray-900 dark:text-white font-sans">
                                                    {{ $m['month_title'] ?? (($m['month_name'] ?? '') . ' ' . ($m['year'] ?? '')) }}
                                                </span>
                                                <button type="button" 
                                                        @click="nextMonthDesktop()" 
                                                        :disabled="desktopMonthIndex >= {{ count($calendarData['months'] ?? []) - 2 }}"
                                                        :class="desktopMonthIndex >= {{ count($calendarData['months'] ?? []) - 2 }} ? 'opacity-20 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-800 dark:text-gray-200'"
                                                        class="w-9 h-9 rounded-full flex items-center justify-center border border-gray-200 dark:border-zinc-700 transition font-bold cursor-pointer"
                                                        title="ماه بعد">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                                                </button>
                                            </div>

                                            <div class="grid grid-cols-7 gap-1 text-center text-xs font-bold text-gray-400 dark:text-gray-500 py-2 border-b border-gray-200 dark:border-zinc-800 mb-3">
                                                <span>ش</span><span>ی</span><span>د</span><span>س</span><span>چ</span><span>پ</span><span class="text-rose-500">ج</span>
                                            </div>

                                            <div class="grid grid-cols-7 gap-1.5 text-center text-xs">
                                                @for($e = 0; $e < ($m['empty_cells_start'] ?? ($m['first_day_of_week'] ?? 0)); $e++)
                                                    <div class="h-16"></div>
                                                @endfor

                                                @foreach($m['days'] ?? [] as $day)
                                                    @php
                                                        $isPast = !empty($day['is_past']);
                                                        $isBlocked = !empty($day['is_blocked']);
                                                        $iso = $day['carbon_date'];
                                                        $dayNum = $day['day_num'] ?? ($day['day'] ?? '');
                                                        $priceVal = !empty($day['price']) ? (float) $day['price'] : 0;
                                                        $priceThousands = $priceVal > 0 ? number_format(round($priceVal / 1000)) : '';
                                                        $isHoliday = !empty($day['is_holiday']);
                                                        $isFriday = ($day['day_of_week'] ?? 0) == 6;
                                                        $isHolidayOrFriday = $isHoliday || $isFriday;
                                                        $isPeakOrWeekend = in_array($day['price_type'] ?? '', ['weekend', 'holiday', 'custom']) || !empty($day['is_weekend']) || !empty($day['is_holiday']);
                                                    @endphp

                                                    @if($isPast)
                                                        <div class="h-16 flex items-center justify-center text-sm font-sans {{ $isHolidayOrFriday ? 'text-rose-300 dark:text-rose-900/60' : 'text-gray-300 dark:text-zinc-600' }}">
                                                            {{ $dayNum }}
                                                        </div>
                                                    @else
                                                        <button type="button" 
                                                                @if(!$isBlocked) @click="selectCalendarDate('{{ $iso }}')" @endif
                                                                :class="getDayClasses('{{ $iso }}', false, {{ $isBlocked ? 'true' : 'false' }})"
                                                                class="relative rounded-2xl border border-dashed border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 hover:border-[var(--theme-primary)] transition-all flex flex-col items-center justify-center py-1.5 px-0.5 min-h-[62px] font-sans shadow-2xs {{ $isBlocked ? 'opacity-35 cursor-not-allowed bg-gray-50 dark:bg-zinc-800/40' : 'cursor-pointer' }}">
                                                            
                                                            @if(!$isBlocked && $isPeakOrWeekend)
                                                                <span :class="(tempCheckIn === '{{ $iso }}' || tempCheckOut === '{{ $iso }}') ? 'hidden' : ''" 
                                                                      class="absolute top-0 right-0 w-2.5 h-2.5 bg-amber-400 rounded-bl-md"></span>
                                                            @endif

                                                            <span class="text-xs sm:text-sm font-black font-sans {{ $isHolidayOrFriday ? 'text-rose-600 dark:text-rose-400' : 'text-gray-800 dark:text-gray-100' }}"
                                                                  :class="(tempCheckIn === '{{ $iso }}' || tempCheckOut === '{{ $iso }}') ? '!text-white' : ''">
                                                                {{ $dayNum }}
                                                            </span>

                                                            @if($isBlocked)
                                                                <span class="text-[9px] text-gray-400 line-through mt-0.5">مسدود</span>
                                                            @elseif($priceVal > 0)
                                                                <span class="text-[10px] sm:text-xs font-black font-sans mt-0.5 tracking-tight text-gray-900 dark:text-white"
                                                                      :class="(tempCheckIn === '{{ $iso }}' || tempCheckOut === '{{ $iso }}') ? '!text-white' : ''">
                                                                    {{ $priceThousands }}
                                                                </span>
                                                            @endif
                                                        </button>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- راهنمای رنگ‌های تقویم و واحد پولی (دقیقاً مشابه show.blade.php) --}}
                    <div class="flex items-center justify-between flex-wrap gap-3 pt-4 border-t border-gray-100 dark:border-zinc-800 text-xs text-gray-500 dark:text-gray-400 font-sans">
                        <div class="hidden sm:flex items-center gap-5 flex-wrap">
                            <span class="flex items-center gap-1.5">
                                <span class="w-3.5 h-3.5 rounded-lg bg-[var(--theme-primary)] shadow-2xs"></span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">تاریخ انتخابی شما</span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-bl-md bg-amber-400"></span>
                                <span>نرخ آخر هفته و پیک</span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-3.5 h-3.5 rounded-lg bg-rose-500 shadow-2xs"></span>
                                <span>جمعه و تعطیلات رسمی</span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-3.5 h-3.5 rounded-lg border border-dashed border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800"></span>
                                <span>غیرقابل رزرو</span>
                            </span>
                        </div>

                        <span class="text-[11px] text-gray-400 font-medium">
                            * مبالغ داخل تقویم بر حسب هزار {{ $currencyLabel }} است.
                        </span>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="pt-3 border-t border-gray-100 dark:border-zinc-800 flex items-center justify-between gap-3 shrink-0">
                    <button type="button" 
                            @click="clearDatesSelection()" 
                            class="text-xs text-rose-600 dark:text-rose-400 font-bold hover:underline cursor-pointer">
                        پاک کردن تاریخ‌ها
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="calendarModalOpen = false" 
                                class="px-5 py-2.5 rounded-2xl border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 font-bold text-xs hover:bg-gray-50 dark:hover:bg-zinc-800 transition cursor-pointer">
                            انصراف
                        </button>
                        <button type="button" 
                                @click="applyDatesSelection()" 
                                :disabled="!tempCheckIn || !tempCheckOut" 
                                class="btn-theme-primary px-6 py-2.5 rounded-2xl font-bold text-xs transition disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                            ثبت تغییرات
                        </button>
                    </div>
                </div>

                </div>
            </div>

        {{-- ============================================================= --}}
        {{-- MODAL 2: GUESTS COUNTER MODAL (Matching User Design)         --}}
        {{-- ============================================================= --}}
        <div x-cloak x-show="guestsModalOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="checkout-modal-overlay"
                 style="display: none;">
                
                <div class="checkout-modal-card bg-white dark:bg-zinc-900 rounded-3xl max-w-lg w-full p-6 border border-gray-200 dark:border-zinc-800 font-sans space-y-5"
                     @click.outside="guestsModalOpen = false">
                
                {{-- Header matching user design --}}
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">ویرایش تعداد مسافران</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">تعداد مسافران و واحد مورد نظر را انتخاب کنید.</p>
                    </div>
                    <button type="button" 
                            @click="guestsModalOpen = false" 
                            class="w-8 h-8 rounded-full bg-gray-100 dark:bg-zinc-800 flex items-center justify-center text-gray-500 hover:text-black dark:hover:text-white font-bold transition cursor-pointer">
                        ✕
                    </button>
                </div>

                {{-- Content Row with Breakdown (Matching user design) --}}
                <div class="flex items-center justify-between py-6 my-2 border-y border-gray-100 dark:border-zinc-800">
                    
                    {{-- Label + Breakdown (Right side in RTL) --}}
                    <div class="flex items-center gap-3 text-right">
                        <div class="w-10 h-10 rounded-2xl bg-gray-100 dark:bg-zinc-800 flex items-center justify-center text-gray-700 dark:text-gray-300 shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-bold mb-1">تعداد مسافران</div>
                            <div class="text-sm sm:text-base font-black text-gray-900 dark:text-white font-sans" x-text="guestsBreakdownText()"></div>
                        </div>
                    </div>

                    {{-- Stepper (Left side in RTL: [-] [count] [+]) --}}
                    <div class="flex items-center gap-2.5" dir="ltr">
                        <button type="button" 
                                @click="decreaseGuests()" 
                                :disabled="tempGuests <= 1"
                                class="w-10 h-10 rounded-xl border border-gray-300 dark:border-zinc-700 flex items-center justify-center text-lg font-bold text-gray-700 dark:text-gray-200 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-zinc-800 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                        </button>
                        <span class="w-8 text-center text-lg font-black text-gray-900 dark:text-white font-sans" x-text="formatNumber(tempGuests)"></span>
                        <button type="button" 
                                @click="increaseGuests()" 
                                :disabled="tempGuests >= maxGuests"
                                class="w-10 h-10 rounded-xl border border-gray-300 dark:border-zinc-700 flex items-center justify-center text-lg font-bold text-gray-700 dark:text-gray-200 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-zinc-800 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>

                </div>

                {{-- Footer matching user design --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="button" 
                            @click="applyGuestsSelection()" 
                            class="btn-theme-primary flex-1 py-3.5 rounded-2xl font-black text-sm transition-all active:scale-[0.99] cursor-pointer">
                        ثبت تغییرات
                    </button>
                    <button type="button" 
                            @click="guestsModalOpen = false" 
                            class="flex-1 py-3.5 rounded-2xl border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 font-bold text-sm hover:bg-gray-50 dark:hover:bg-zinc-800 transition cursor-pointer">
                        بستن
                    </button>
                </div>
                </div>
            </div>

        {{-- ============================================================= --}}
        {{-- MODAL 3: PROPERTY DETAILS QUICK VIEW                          --}}
        {{-- ============================================================= --}}
        <div x-cloak x-show="propertyDetailsModalOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="checkout-modal-overlay"
                 style="display: none;">
                
                <div class="checkout-modal-card bg-white dark:bg-zinc-900 rounded-3xl max-w-lg w-full p-6 border border-gray-200 dark:border-zinc-800 font-sans space-y-4"
                     @click.outside="propertyDetailsModalOpen = false">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-zinc-800">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white">مشخصات اقامتگاه</h3>
                    <button type="button" @click="propertyDetailsModalOpen = false" class="w-7 h-7 rounded-full bg-gray-100 dark:bg-zinc-800 flex items-center justify-center text-gray-500 font-bold cursor-pointer">✕</button>
                </div>

                <div class="space-y-3 text-xs leading-relaxed text-zinc-600 dark:text-zinc-300">
                    <div class="flex items-center gap-3">
                        <img src="{{ $coverUrl }}" alt="{{ $property->title }}" class="w-16 h-16 rounded-xl object-cover shrink-0">
                        <div>
                            <h4 class="font-bold text-[#18181B] dark:text-zinc-100">{{ $property->title }}</h4>
                            <p class="text-[11px] text-zinc-400 mt-0.5">{{ $property->city ?? 'مازندران، لفور' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2">
                        <div class="p-2.5 rounded-xl bg-[#F7F8F9] dark:bg-zinc-800/60">
                            <span class="text-[10px] text-zinc-400 block">ظرفیت پایه:</span>
                            <span class="font-bold text-xs mt-0.5 block">{{ $config?->base_guests ?? 1 }} نفر</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-[#F7F8F9] dark:bg-zinc-800/60">
                            <span class="text-[10px] text-zinc-400 block">حداکثر ظرفیت:</span>
                            <span class="font-bold text-xs mt-0.5 block">{{ $config?->max_guests ?? ($config?->base_guests ?? 1) }} نفر</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-[#F7F8F9] dark:bg-zinc-800/60">
                            <span class="text-[10px] text-zinc-400 block">ساعت تحویل:</span>
                            <span class="font-bold text-xs mt-0.5 block">{{ $checkInTime }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-[#F7F8F9] dark:bg-zinc-800/60">
                            <span class="text-[10px] text-zinc-400 block">ساعت تخلیه:</span>
                            <span class="font-bold text-xs mt-0.5 block">{{ $checkOutTime }}</span>
                        </div>
                    </div>

                    <a href="{{ route('properties.show', $property->slug ?: $property->code) }}" target="_blank" class="block text-center text-theme-primary font-bold text-xs pt-2 hover:underline">
                        مشاهده صفحه کامل اقامتگاه در تب جدید ←
                    </a>
                </div>
            </div>
       
 </div>

        {{-- ============================================================= --}}
        {{-- MODAL 4: CHAT / CONTACT WITH HOST MODAL                       --}}
        {{-- ============================================================= --}}
        <div x-cloak x-show="chatModalOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="checkout-modal-overlay"
                 style="display: none;">
                
                <div class="checkout-modal-card bg-white dark:bg-zinc-900 rounded-3xl max-w-sm w-full p-6 border border-gray-200 dark:border-zinc-800 font-sans space-y-4"
                     @click.outside="chatModalOpen = false">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-zinc-800">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white">ارتباط با میزبان</h3>
                    <button type="button" @click="chatModalOpen = false" class="w-7 h-7 rounded-full bg-gray-100 dark:bg-zinc-800 flex items-center justify-center text-gray-500 font-bold cursor-pointer">✕</button>
                </div>

                <div class="text-center space-y-3 py-2">
                    <div class="w-14 h-14 rounded-full bg-theme-light text-theme-primary flex items-center justify-center mx-auto text-xl font-bold">
                        💬
                    </div>
                    <h4 class="font-bold text-xs text-zinc-800 dark:text-zinc-200">میزبان: {{ $hostDisplayName }}</h4>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 leading-relaxed font-sans">
                        می‌توانید پیام یا سوال خود را در کادر «پیام به میزبان» بنویسید. پس از ثبت و تأیید درخواست رزرو، اطلاعات تماس مستقیم در اختیار شما قرار می‌گیرد.
                    </p>
                    @if($canShowDirectCall && !empty($hostPhone))
                        <div class="pt-2">
                            <a href="tel:{{ $hostPhone }}" class="w-full py-2.5 rounded-xl bg-zinc-800 hover:bg-zinc-900 text-white font-bold text-xs flex items-center justify-center gap-2">
                                <span>تماس مستقیم با میزبان: {{ $hostPhone }}</span>
                            </a>
                        </div>
                    @endif
                </div>

                <button type="button" @click="chatModalOpen = false" class="w-full py-2.5 rounded-xl bg-gray-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 font-bold text-xs cursor-pointer">
                    متوجه شدم
                </button>
                </div>
            </div>

        {{-- ============================================================= --}}
        {{-- MODAL 5: SUPPORT MODAL                                        --}}
        {{-- ============================================================= --}}
        @if(!empty($supportPhone))
        <div x-cloak x-show="supportModalOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="checkout-modal-overlay"
                 style="display: none;">
                
                <div class="checkout-modal-card bg-white dark:bg-zinc-900 rounded-3xl max-w-sm w-full p-6 border border-gray-200 dark:border-zinc-800 font-sans space-y-4"
                     @click.outside="supportModalOpen = false">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-zinc-800">
                        <h3 class="text-sm font-black text-gray-900 dark:text-white">پشتیبانی {{ $appName }}</h3>
                        <button type="button" @click="supportModalOpen = false" class="w-7 h-7 rounded-full bg-gray-100 dark:bg-zinc-800 flex items-center justify-center text-gray-500 font-bold cursor-pointer">✕</button>
                    </div>

                    <div class="text-center space-y-3 py-2">
                        <div class="w-14 h-14 rounded-full bg-theme-light text-theme-primary flex items-center justify-center mx-auto text-xl font-bold">
                            🎧
                        </div>
                        <p class="text-xs text-zinc-600 dark:text-zinc-300 leading-relaxed font-sans">
                            در صورت نیاز به راهنمایی در فرایند رزرو اقامتگاه، کارشناسان پشتیبانی آماده پاسخگویی هستند.
                        </p>
                    </div>

                    <div class="pt-2">
                        <a href="tel:{{ $supportPhone }}" class="btn-theme-primary w-full py-3 rounded-xl font-bold text-xs flex items-center justify-center gap-2">
                            <span>تماس تلفنی با پشتیبانی ({{ $supportPhone }})</span>
                        </a>
                    </div>
                </div>
            </div>
        @endif

    </div>

    {{-- Interactive Alpine Engine for Checkout & Calendar --}}
    <script>
        function rentalCheckoutApp(config, initialQuote, calendarData, clientFields, propertySlug, quoteUrl, bookUrl, currencyLabel, clientName, clientPhone, clientNationalCode, initialEditOpen) {
            return {
                init() {
                    this.$watch('calendarModalOpen', val => this.handleBodyScroll());
                    this.$watch('guestsModalOpen', val => this.handleBodyScroll());
                    this.$watch('propertyDetailsModalOpen', val => this.handleBodyScroll());
                    this.$watch('chatModalOpen', val => this.handleBodyScroll());
                    this.$watch('supportModalOpen', val => this.handleBodyScroll());
                },
                handleBodyScroll() {
                    if (this.calendarModalOpen || this.guestsModalOpen || this.propertyDetailsModalOpen || this.chatModalOpen || this.supportModalOpen) {
                        document.body.style.overflow = 'hidden';
                    } else {
                        document.body.style.overflow = '';
                    }
                },
                config: config || {},
                quote: initialQuote || {},
                calendarData: calendarData || {},
                flatDates: (calendarData?.flatDates || calendarData?.flat || {}),
                clientFields: clientFields || [],
                propertySlug: propertySlug,
                quoteUrl: quoteUrl,
                bookUrl: bookUrl,
                currencyLabel: currencyLabel,

                checkIn: initialQuote?.check_in_date || '',
                checkOut: initialQuote?.check_out_date || '',
                checkInJalali: initialQuote?.check_in_jalali || '',
                checkOutJalali: initialQuote?.check_out_jalali || '',
                nightsCount: Number(initialQuote?.nights_count || 1),
                guests: Number(initialQuote?.guests_count || 1),
                baseGuests: Number(config?.base_guests || 1),
                maxGuests: Number(config?.max_guests || config?.base_guests || 1),

                // Temp states for modals
                tempCheckIn: initialQuote?.check_in_date || '',
                tempCheckOut: initialQuote?.check_out_date || '',
                tempNightsCount: Number(initialQuote?.nights_count || 1),
                tempGuests: Number(initialQuote?.guests_count || 1),

                // Modals
                calendarModalOpen: false,
                guestsModalOpen: false,
                editSupervisorOpen: initialEditOpen || false,
                propertyDetailsModalOpen: false,
                chatModalOpen: false,
                supportModalOpen: false,

                // Calendar months navigation (Mobile & Desktop)
                mobileMonthIndex: 0,
                desktopMonthIndex: 0,
                totalMonthsCount: (calendarData?.months || []).length,

                // Notes
                notes: '',
                validationErrors: {},
                formErrorMessage: '',
                isSubmitting: false,
                isRecalculating: false,

                // Client dynamic displays
                supervisorName: clientName || '',
                supervisorPhone: clientPhone || '',
                supervisorNationalCode: clientNationalCode || '',

                supervisorSummary() {
                    let parts = [];
                    if (this.supervisorName) parts.push(this.supervisorName);
                    if (this.supervisorPhone) parts.push(this.supervisorPhone);
                    return parts.length > 0 ? parts.join(' - ') : (this.supervisorPhone || 'اطلاعات مسافر');
                },

                // Pricing accessors
                pricePerNightDisplay() {
                    let total = Number(this.quote?.nights_total || 0);
                    let count = Math.max(1, Number(this.nightsCount || 1));
                    return Math.round(total / count);
                },
                nightsTotalDisplay() {
                    return Number(this.quote?.nights_total || 0);
                },
                totalDiscountDisplay() {
                    return Number(this.quote?.discount_amount || 0);
                },
                extraGuestsCountDisplay() {
                    return Number(this.quote?.extra_guests_count || 0);
                },
                extraGuestsFeeDisplay() {
                    return Number(this.quote?.extra_guests_fee || 0);
                },
                cleaningFeeDisplay() {
                    return Number(this.quote?.cleaning_fee || 0);
                },
                grandTotalDisplay() {
                    return Number(this.quote?.grand_total || 0);
                },
                prepaymentAmountDisplay() {
                    return Number(this.quote?.prepayment_amount || this.quote?.grand_total || 0);
                },

                // Formatters
                formatNumber(val) {
                    if (val === null || val === undefined || isNaN(val)) return '۰';
                    let str = String(Math.round(Number(val))).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    const faDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                    return str.replace(/\d/g, d => faDigits[d]);
                },

                formatDateRangeDisplay() {
                    let inTxt = this.checkInJalali || this.checkIn;
                    let outTxt = this.checkOutJalali || this.checkOut;
                    return inTxt + ' تا ' + outTxt + ' (' + this.formatNumber(this.nightsCount) + ' شب)';
                },

                // Breakdown text for guests modal matching screenshot
                guestsBreakdownText() {
                    let t = Number(this.tempGuests || 1);
                    let b = Number(this.baseGuests || 1);
                    if (t <= b) {
                        return this.formatNumber(t) + ' نفر پایه';
                    }
                    let extra = t - b;
                    return this.formatNumber(b) + ' نفر پایه + ' + this.formatNumber(extra) + ' نفر اضافه';
                },

                // Format date for calendar top status pills using Jalali data
                formatDatePill(isoDate) {
                    if (!isoDate) return '--';
                    let norm = (isoDate || '').trim();
                    let d = this.flatDates[norm];
                    if (d) {
                        if (d.human_jalali) return d.human_jalali;
                        if (d.human_jalali_full) return d.human_jalali_full;
                        if (d.jalali_date) return d.jalali_date;
                    }
                    try {
                        let parts = norm.split('-');
                        if (parts.length === 3) {
                            let dt = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
                            if (!isNaN(dt.getTime())) {
                                return new Intl.DateTimeFormat('fa-IR-u-nu-arabext', { day: 'numeric', month: 'long' }).format(dt);
                            }
                        }
                    } catch (e) {}
                    return norm;
                },

                // Calendar Navigation
                prevMonthMobile() {
                    if (this.mobileMonthIndex > 0) this.mobileMonthIndex--;
                },
                nextMonthMobile() {
                    if (this.mobileMonthIndex < this.totalMonthsCount - 1) this.mobileMonthIndex++;
                },
                prevMonthDesktop() {
                    if (this.desktopMonthIndex > 0) this.desktopMonthIndex--;
                },
                nextMonthDesktop() {
                    if (this.desktopMonthIndex < this.totalMonthsCount - 2) this.desktopMonthIndex++;
                },

                openCalendarModal() {
                    this.tempCheckIn = this.checkIn;
                    this.tempCheckOut = this.checkOut;
                    this.updateTempNightsCount();
                    this.calendarModalOpen = true;
                },

                selectCalendarDate(isoStr) {
                    let norm = (isoStr || '').trim();
                    if (!this.tempCheckIn || (this.tempCheckIn && this.tempCheckOut)) {
                        this.tempCheckIn = norm;
                        this.tempCheckOut = '';
                    } else if (this.tempCheckIn && !this.tempCheckOut) {
                        if (norm > this.tempCheckIn) {
                            this.tempCheckOut = norm;
                        } else if (norm < this.tempCheckIn) {
                            this.tempCheckIn = norm;
                            this.tempCheckOut = '';
                        } else {
                            this.tempCheckIn = '';
                        }
                    }
                    this.updateTempNightsCount();
                },

                updateTempNightsCount() {
                    if (!this.tempCheckIn || !this.tempCheckOut || this.tempCheckOut <= this.tempCheckIn) {
                        this.tempNightsCount = 0;
                        return;
                    }
                    let d1 = new Date(this.tempCheckIn);
                    let d2 = new Date(this.tempCheckOut);
                    let diff = Math.round((d2 - d1) / (1000 * 3600 * 24));
                    this.tempNightsCount = diff > 0 ? diff : 0;
                },

                clearDatesSelection() {
                    this.tempCheckIn = '';
                    this.tempCheckOut = '';
                    this.tempNightsCount = 0;
                },

                getDayClasses(isoStr, isDesktop, isBlocked) {
                    if (isBlocked) return '!opacity-35 !cursor-not-allowed !bg-gray-50 dark:!bg-zinc-800/40';
                    let d = (isoStr || '').trim();
                    let cIn = (this.tempCheckIn || '').trim();
                    let cOut = (this.tempCheckOut || '').trim();

                    if (d === cIn) {
                        return cOut ? 'cal-day-checkin' : 'cal-day-single';
                    }
                    if (d === cOut) {
                        return 'cal-day-checkout';
                    }
                    if (cIn && cOut && d > cIn && d < cOut) {
                        return 'cal-day-range';
                    }
                    return '';
                },

                // Apply Dates and Recalculate
                applyDatesSelection() {
                    if (!this.tempCheckIn || !this.tempCheckOut) return;
                    this.checkIn = this.tempCheckIn;
                    this.checkOut = this.tempCheckOut;
                    this.calendarModalOpen = false;
                    this.recalculateQuote();
                },

                // Guests selection
                increaseGuests() {
                    if (this.tempGuests < this.maxGuests) this.tempGuests++;
                },
                decreaseGuests() {
                    if (this.tempGuests > 1) this.tempGuests--;
                },
                                // Central form submission and validation
                submitBookingForm(e) {
                    this.validationErrors = {};
                    this.formErrorMessage = '';

                    // 1. Validate dates
                    if (!this.checkIn || !this.checkOut) {
                        this.openCalendarModal();
                        this.formErrorMessage = 'لطفاً ابتدا تاریخ ورود و خروج را در تقویم انتخاب نمایید.';
                        return false;
                    }

                    // 2. Validate client required fields
                    let hasError = false;
                    let firstErrorInput = null;

                    for (let f of this.clientFields) {
                        if (f.required || f.client_auth) {
                            let inputEl = document.querySelector('[name="' + f.id + '"]');
                            let val = inputEl ? inputEl.value.trim() : '';

                            if (!val) {
                                hasError = true;
                                this.validationErrors[f.id] = f.label + ' الزامی است.';
                                if (!firstErrorInput) firstErrorInput = inputEl;
                            } else if (f.id === 'national_code' && !/^\d{10}$/.test(val)) {
                                hasError = true;
                                this.validationErrors[f.id] = 'کد ملی باید ۱۰ رقم باشد.';
                                if (!firstErrorInput) firstErrorInput = inputEl;
                            }
                        }
                    }

                    if (hasError) {
                        this.formErrorMessage = 'لطفاً فیلدهای ستاره‌دار مشخصات مسافر را تکمیل نمایید.';
                        if (firstErrorInput) {
                            firstErrorInput.focus();
                            firstErrorInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        return false;
                    }

                    this.isSubmitting = true;
                    // Directly submit HTML form
                    const formEl = document.getElementById('checkout-booking-form') || (e ? e.target : null);
                    if (formEl) {
                        formEl.submit();
                    }
                },

                applyGuestsSelection() {
                    this.guests = this.tempGuests;
                    this.guestsModalOpen = false;
                    this.recalculateQuote();
                },

                // Recalculate Quote via AJAX
                async recalculateQuote() {
                    this.isRecalculating = true;
                    try {
                        let response = await fetch(this.quoteUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                check_in_date: this.checkIn,
                                check_out_date: this.checkOut,
                                guests_count: this.guests
                            })
                        });

                        let data = await response.json();
                        if (data && data.success && data.quote) {
                            this.quote = data.quote;
                            this.nightsCount = data.quote.nights_count;
                            this.checkInJalali = data.quote.check_in_jalali;
                            this.checkOutJalali = data.quote.check_out_jalali;
                        } else if (data && data.message) {
                            alert(data.message);
                        }
                    } catch (e) {
                        console.error('Error recalculating quote:', e);
                    } finally {
                        this.isRecalculating = false;
                    }
                }
            };
        }
    </script>
@endsection
