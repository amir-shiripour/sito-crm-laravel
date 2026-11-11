@extends('clients::layouts.client')

@php
    $title = 'داشبورد کاربری';

    // وضعیت حساب کاربری
    $status = optional($client->status);
    $statusLabel = $status?->label;
    $statusKey   = $status?->key ?? 'unknown';

    if (empty($statusLabel) || $statusLabel === 'نامشخص') {
        $statusLabel = 'حساب فعال';
        $statusBg = 'bg-emerald-500';
    } else {
        $statusBg = match ($statusKey) {
            'active'   => 'bg-emerald-500',
            'pending'  => 'bg-amber-500',
            'blocked', 'cancelled', 'CANCELLED' => 'bg-rose-500',
            default    => 'bg-emerald-500',
        };
    }
@endphp

@section('content')
    <div class="space-y-6 sm:space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 font-sans">

        {{-- ۱. پیام هشدار رزرو در انتظار تسویه و پرداخت (کاملاً ریسپانسیو) --}}
        @if(($showRentalFeatures ?? false) && isset($recentRentalBookings) && $recentRentalBookings->whereIn('status', [\Modules\Properties\Entities\PropertyRentalBooking::STATUS_AWAITING_PAYMENT, \Modules\Properties\Entities\PropertyRentalBooking::STATUS_PENDING_HOST])->count() > 0)
            <div class="relative overflow-hidden rounded-2xl bg-amber-50 dark:bg-amber-950/40 p-4 sm:p-5 border border-amber-200/90 dark:border-amber-800/80 shadow-sm transition-all">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start sm:items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20">
                            <svg class="w-5 h-5 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-black text-amber-950 dark:text-amber-200">
                                شما رزرو اقامتگاه در انتظار تسویه و پرداخت دارید
                            </h3>
                            <p class="text-xs sm:text-sm text-amber-900/80 dark:text-amber-300/80 mt-0.5 leading-relaxed">
                                جهت قطعی شدن رزرو اقامتگاه و صدور تأییدیه رسمی سفر، لطفاً نسبت به پرداخت صورتحساب اقدام فرمایید.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('client.rental-bookings.index', ['status' => 'awaiting_payment']) }}" 
                       class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs sm:text-sm font-bold shadow-md shadow-amber-500/25 transition-all shrink-0">
                        <span>مشاهده و پرداخت صورتحساب</span>
                        <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        @endif

        {{-- ۲. هیرو سکشن خوش‌آمدگویی (طراحی لوکس، متوازن و ریسپانسیو) --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-800 p-6 sm:p-8 md:p-10 shadow-xl shadow-indigo-600/15 text-white">
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none opacity-10">
                <svg class="absolute -top-24 -left-24 w-96 h-96 text-white" fill="currentColor" viewBox="0 0 200 200">
                    <path d="M42.7,-62.9C50.9,-52.8,50.1,-34.4,51.7,-19.2C53.4,-4,57.4,8,54.5,18.7C51.6,29.3,41.8,38.7,31.2,46.9C20.6,55.1,9.2,62.1,-2.9,66.1C-15,70.1,-27.8,71.1,-38.6,63.9C-49.4,56.7,-58.2,41.3,-62.9,25.2C-67.6,9.1,-68.2,-7.7,-61.4,-21.8C-54.6,-35.9,-40.4,-47.3,-27,-54.2C-13.6,-61.1,-1,-63.5,13.8,-63.5C28.6,-63.5,42.7,-62.9,42.7,-62.9Z" transform="translate(100 100)" />
                </svg>
            </div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-xs text-indigo-100 font-bold">
                        <span>خوش آمدید به پرتال مهمانان مسترویلا لفور</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-black tracking-tight text-white drop-shadow-sm">
                        {{ $client->full_name ?: $client->username }}
                    </h1>
                    <p class="text-xs sm:text-sm text-indigo-100/90 max-w-xl leading-relaxed">
                        از طریق این پنل می‌توانید رزروهای اقامتگاهی خود را مشاهده، صورت‌حساب‌ها را پرداخت و تأییدیه رسمی اقامت خود را دریافت فرمایید.
                    </p>
                </div>

                <div class="flex items-center md:flex-col md:items-end justify-between gap-2 pt-2 md:pt-0 border-t md:border-t-0 border-white/10">
                    <span class="text-xs font-medium text-indigo-200">وضعیت حساب شما</span>
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 shadow-inner">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $statusBg }} opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 {{ $statusBg }}"></span>
                        </span>
                        <span class="font-bold text-xs tracking-wide text-white">{{ $statusLabel }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ۳. کارت‌های آمار و شاخص‌های اقامتگاه (۴ کارت متوازن، منظم و بدون فضای خالی) --}}
        <div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">

                {{-- کارت ۱: رزروهای جاری و قطعی --}}
                <a href="{{ route('client.rental-bookings.index', ['status' => 'confirmed']) }}" 
                   class="group relative overflow-hidden bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-100 dark:border-gray-700/80 shadow-xs hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-600 transition-all block text-right">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400">جاری و قطعی</span>
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white mb-1">
                        {{ \Morilog\Jalali\CalendarUtils::convertNumbers($activeRentalBookingsCount ?? 0) }}
                    </div>
                    <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-1 group-hover:translate-x-[-2px] transition-transform">
                        <span>اقامت‌های پیش‌رو</span>
                        <span class="text-sm">&larr;</span>
                    </div>
                </a>

                {{-- کارت ۲: در انتظار تسویه و پرداخت --}}
                <a href="{{ route('client.rental-bookings.index', ['status' => 'awaiting_payment']) }}" 
                   class="group relative overflow-hidden bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border {{ ($pendingRentalBookingsCount ?? 0) > 0 ? 'border-amber-300 dark:border-amber-700/80 bg-amber-50/20' : 'border-gray-100 dark:border-gray-700/80' }} shadow-xs hover:shadow-md hover:border-amber-400 transition-all block text-right">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400">نیازمند اقدام</span>
                        <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white mb-1">
                        {{ \Morilog\Jalali\CalendarUtils::convertNumbers($pendingRentalBookingsCount ?? 0) }}
                    </div>
                    <div class="text-xs font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1 group-hover:translate-x-[-2px] transition-transform">
                        <span>در انتظار پرداخت</span>
                        <span class="text-sm">&larr;</span>
                    </div>
                </a>

                {{-- کارت ۳: اقامت‌های انجام‌شده --}}
                <a href="{{ route('client.rental-bookings.index', ['status' => 'completed']) }}" 
                   class="group relative overflow-hidden bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-100 dark:border-gray-700/80 shadow-xs hover:shadow-md hover:border-emerald-300 dark:hover:border-emerald-600 transition-all block text-right">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400">سفرهای قبلی</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white mb-1">
                        {{ \Morilog\Jalali\CalendarUtils::convertNumbers($completedRentalBookingsCount ?? 0) }}
                    </div>
                    <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 group-hover:translate-x-[-2px] transition-transform">
                        <span>اقامت‌های تکمیل‌شده</span>
                        <span class="text-sm">&larr;</span>
                    </div>
                </a>

                {{-- کارت ۴: مجموع شب‌های اقامت (دیدنی و بدون لینک) --}}
                <div class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-100 dark:border-gray-700/80 shadow-xs block text-right select-none">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400">تجربه اقامت</span>
                        <div class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white mb-1">
                        {{ \Morilog\Jalali\CalendarUtils::convertNumbers($totalRentalNightsCount ?? 0) }} <span class="text-xs font-bold text-gray-500">شب</span>
                    </div>
                    <div class="text-xs font-bold text-purple-600 dark:text-purple-400">
                        <span>مجموع شب‌های اقامت</span>
                    </div>
                </div>

            </div>
        </div>

        {{-- ۴. بخش دسترسی سریع (۴ آیتم کاربردی، پرکننده کامل سطر و ریسپانسیو) --}}
        <div>
            <h2 class="text-base sm:text-lg font-black text-gray-900 dark:text-white mb-3 sm:mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                <span>دسترسی سریع</span>
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                {{-- ۱. پروفایل من --}}
                <a href="{{ route('client.profile.show') }}" 
                   class="flex flex-col items-center justify-center text-center p-4 sm:p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/80 hover:border-indigo-500 dark:hover:border-indigo-500 hover:shadow-md transition-all group">
                    <div class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 group-hover:scale-110 transition-transform mb-2.5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <span class="text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 group-hover:text-indigo-600">پروفایل کاربری</span>
                    <span class="text-[10px] text-gray-400 mt-0.5">مشخصات و اطلاعات تماس</span>
                </a>

                {{-- ۲. رزروهای من --}}
                <a href="{{ route('client.rental-bookings.index') }}" 
                   class="flex flex-col items-center justify-center text-center p-4 sm:p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/80 hover:border-indigo-500 dark:hover:border-indigo-500 hover:shadow-md transition-all group">
                    <div class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 group-hover:scale-110 transition-transform mb-2.5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <span class="text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 group-hover:text-indigo-600">رزروهای اقامتگاه</span>
                    <span class="text-[10px] text-gray-400 mt-0.5">پیگیری و صدور تأییدیه</span>
                </a>

                {{-- ۳. رزرو اقامتگاه جدید --}}
                <a href="{{ url('/properties') }}" 
                   class="flex flex-col items-center justify-center text-center p-4 sm:p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/80 hover:border-emerald-500 dark:hover:border-emerald-500 hover:shadow-md transition-all group">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform mb-2.5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 group-hover:text-emerald-600">رزرو اقامتگاه جدید</span>
                    <span class="text-[10px] text-gray-400 mt-0.5">جستجو در ویلاهای لفور</span>
                </a>

                {{-- ۴. پشتیبانی و تماس --}}
                @php
                    $phoneVal = $contactPhone ?? (function_exists('get_setting') ? get_setting('contact_phone') : null);
                    if (empty($phoneVal) && class_exists(\Modules\Settings\Entities\Setting::class)) {
                        $phoneVal = \Modules\Settings\Entities\Setting::where('key', 'contact_phone')->value('value');
                    }
                    $phoneVal = !empty(trim((string)$phoneVal)) ? trim((string)$phoneVal) : null;
                @endphp

                @if($phoneVal)
                    <a href="tel:{{ $phoneVal }}" 
                       class="flex flex-col items-center justify-center text-center p-4 sm:p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/80 hover:border-violet-500 dark:hover:border-violet-500 hover:shadow-md transition-all group"
                       title="تماس مستقیم با پشتیبانی: {{ $phoneVal }}">
                        <div class="w-11 h-11 rounded-2xl bg-violet-50 dark:bg-violet-950/50 flex items-center justify-center text-violet-600 dark:text-violet-400 group-hover:scale-110 transition-transform mb-2.5">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <span class="text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 group-hover:text-violet-600">پشتیبانی و تماس</span>
                        <span class="text-xs font-black text-violet-600 dark:text-violet-400 mt-1 dir-ltr tracking-wider">
                            {{ \Morilog\Jalali\CalendarUtils::convertNumbers($phoneVal) }}
                        </span>
                    </a>
                @else
                    <div class="flex flex-col items-center justify-center text-center p-4 sm:p-5 rounded-2xl bg-gray-50/60 dark:bg-gray-800/40 border border-dashed border-gray-200 dark:border-gray-700/60 opacity-60 cursor-not-allowed select-none"
                         title="شماره تماسی در تنظیمات سیستم ثبت نشده است">
                        <div class="w-11 h-11 rounded-2xl bg-gray-100 dark:bg-gray-700/50 flex items-center justify-center text-gray-400 dark:text-gray-500 mb-2.5">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <span class="text-xs sm:text-sm font-bold text-gray-400 dark:text-gray-500">پشتیبانی و تماس</span>
                        <span class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">شماره ثبت نشده</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- ۵. بخش آخرین رزروهای اقامتگاهی شما (جدول دسکتاپ خوانا + کارت‌های شیک موبایل) --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base sm:text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                    <span>آخرین رزروهای اقامتگاهی شما</span>
                </h2>
                <a href="{{ route('client.rental-bookings.index') }}" 
                   class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 flex items-center gap-1 transition-colors">
                    <span>مشاهده همه رزروها</span>
                    <span class="text-sm">&larr;</span>
                </a>
            </div>

            @if(isset($recentRentalBookings) && $recentRentalBookings->isNotEmpty())
                {{-- حالت دسکتاپ (جدول با فاصله، تایپوگرافی خوانا و اکشن مستقیم) --}}
                <div class="hidden md:block bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700/80 shadow-xs overflow-hidden">
                    <table class="w-full text-right text-sm">
                        <thead class="text-xs text-gray-500 dark:text-gray-400 bg-gray-50/70 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4 font-bold">اقامتگاه</th>
                                <th class="px-5 py-4 font-bold">کد رزرو</th>
                                <th class="px-5 py-4 font-bold">بازه اقامت</th>
                                <th class="px-5 py-4 font-bold">مبلغ کل</th>
                                <th class="px-5 py-4 font-bold">وضعیت</th>
                                <th class="px-6 py-4 font-bold text-left">عملیات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            @foreach($recentRentalBookings as $booking)
                                @php
                                    $badge = $booking->status_badge ?: ['label' => $booking->status_label ?? 'نامشخص', 'class' => 'bg-gray-100 text-gray-700'];
                                    $propCover = $booking->property?->primary_image_url ?? $booking->property?->primaryImage?->image_url ?? asset('assets/images/placeholder.jpg');
                                    $isAwaiting = ($booking->status === \Modules\Properties\Entities\PropertyRentalBooking::STATUS_AWAITING_PAYMENT);
                                @endphp
                                <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/30 transition-colors">
                                    {{-- اقامتگاه --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3.5">
                                            <img src="{{ $propCover }}" alt="" class="w-13 h-13 rounded-2xl object-cover shrink-0 border border-gray-100 dark:border-gray-700 shadow-xs">
                                            <div class="flex flex-col min-w-0">
                                                <a href="{{ route('client.rental-bookings.show', $booking->id) }}" 
                                                   class="font-black text-gray-900 dark:text-white hover:text-indigo-600 transition-colors line-clamp-1 max-w-[260px]">
                                                    {{ $booking->property?->title ?? 'اقامتگاه' }}
                                                </a>
                                                <span class="text-xs text-gray-400 font-bold mt-1">
                                                    {{ \Morilog\Jalali\CalendarUtils::convertNumbers($booking->nights_count) }} شب • {{ \Morilog\Jalali\CalendarUtils::convertNumbers($booking->guests_count) }} نفر
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- کد رزرو --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <span class="inline-block px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-900/80 text-indigo-600 dark:text-indigo-400 font-bold text-xs dir-ltr">
                                            {{ $booking->booking_code }}
                                        </span>
                                    </td>

                                    {{-- بازه اقامت --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="flex flex-col text-xs font-bold text-gray-700 dark:text-gray-300">
                                            <span>ورود: {{ \Morilog\Jalali\CalendarUtils::convertNumbers($booking->check_in) }}</span>
                                            <span class="text-gray-400 mt-0.5">خروج: {{ \Morilog\Jalali\CalendarUtils::convertNumbers($booking->check_out) }}</span>
                                        </div>
                                    </td>

                                    {{-- مبلغ کل --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="text-sm font-black text-gray-900 dark:text-white">
                                            {{ \Morilog\Jalali\CalendarUtils::convertNumbers(number_format($booking->total_price)) }}
                                            <span class="text-[11px] font-normal text-gray-400">{{ $booking->currency === 'IRR' ? 'ریال' : 'تومان' }}</span>
                                        </div>
                                    </td>

                                    {{-- وضعیت --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold {{ $badge['class'] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ $badge['label'] ?? 'نامشخص' }}
                                        </span>
                                    </td>

                                    {{-- دکمه عملیات و پرداخت --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-left">
                                        @if($isAwaiting)
                                            <a href="{{ route('client.rental-bookings.show', $booking->id) }}" 
                                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-sm shadow-amber-500/20 transition-all">
                                                <span>پرداخت صورتحساب</span>
                                                <svg class="w-3.5 h-3.5 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                                </svg>
                                            </a>
                                        @else
                                            <a href="{{ route('client.rental-bookings.show', $booking->id) }}" 
                                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-xs font-bold transition-all">
                                                <span>مشاهده جزئیات</span>
                                                <svg class="w-3.5 h-3.5 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- حالت موبایل (کارت‌های مدرن رزرو بدون نیاز به اسکرول افقی کلافه‌کننده) --}}
                <div class="block md:hidden space-y-3.5">
                    @foreach($recentRentalBookings as $booking)
                        @php
                            $badge = $booking->status_badge ?: ['label' => $booking->status_label ?? 'نامشخص', 'class' => 'bg-gray-100 text-gray-700'];
                            $propCover = $booking->property?->primary_image_url ?? $booking->property?->primaryImage?->image_url ?? asset('assets/images/placeholder.jpg');
                            $isAwaiting = ($booking->status === \Modules\Properties\Entities\PropertyRentalBooking::STATUS_AWAITING_PAYMENT);
                        @endphp
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700/80 shadow-xs space-y-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $propCover }}" alt="" class="w-14 h-14 rounded-2xl object-cover shrink-0 border border-gray-100 dark:border-gray-700 shadow-xs">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-black text-sm text-gray-900 dark:text-white truncate">
                                        {{ $booking->property?->title ?? 'اقامتگاه' }}
                                    </h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs text-indigo-600 dark:text-indigo-400 font-bold dir-ltr">
                                            {{ $booking->booking_code }}
                                        </span>
                                        <span class="text-gray-300 dark:text-gray-600">•</span>
                                        <span class="text-xs text-gray-500 font-bold">
                                            {{ \Morilog\Jalali\CalendarUtils::convertNumbers($booking->nights_count) }} شب
                                        </span>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[11px] font-bold shrink-0 {{ $badge['class'] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $badge['label'] ?? 'نامشخص' }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between text-xs font-bold text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/60 p-2.5 rounded-xl">
                                <span>{{ \Morilog\Jalali\CalendarUtils::convertNumbers($booking->check_in) }} تا {{ \Morilog\Jalali\CalendarUtils::convertNumbers($booking->check_out) }}</span>
                                <span class="text-gray-900 dark:text-white font-black">
                                    {{ \Morilog\Jalali\CalendarUtils::convertNumbers(number_format($booking->total_price)) }} {{ $booking->currency === 'IRR' ? 'ریال' : 'تومان' }}
                                </span>
                            </div>

                            <a href="{{ route('client.rental-bookings.show', $booking->id) }}" 
                               class="w-full flex items-center justify-center gap-1.5 py-2.5 rounded-xl {{ $isAwaiting ? 'bg-amber-500 hover:bg-amber-600 text-white' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200' }} text-xs font-bold transition-all">
                                <span>{{ $isAwaiting ? 'مشاهده و پرداخت صورتحساب' : 'مشاهده جزئیات رزرو' }}</span>
                                <svg class="w-3.5 h-3.5 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center bg-white dark:bg-gray-800 rounded-3xl border border-dashed border-gray-200 dark:border-gray-700/80 space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 mx-auto flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200">هنوز هیچ رزروی برای شما ثبت نشده است</p>
                    <p class="text-xs text-gray-400 max-w-sm mx-auto">می‌توانید همین حالا انواع ویلا، کلبه و اقامتگاه‌های لفور را بررسی و رزرو فرمایید.</p>
                    <a href="{{ url('/properties') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all">
                        <span>کاوش اقامتگاه‌ها</span>
                        <span class="text-sm">&larr;</span>
                    </a>
                </div>
            @endif
        </div>

    </div>
@endsection
