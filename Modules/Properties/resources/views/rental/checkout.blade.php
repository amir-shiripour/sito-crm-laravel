@extends('layouts.web')

@section('title', 'پیش‌فاکتور و تأیید نهایی رزرو - ' . $property->title)

@push('styles')
    <style>
        .checkout-pattern {
            background-image: radial-gradient(circle at 50% 0%, rgba(99, 102, 241, 0.08) 0%, transparent 60%);
        }
    </style>
@endpush

@section('content')
    @php
        use Morilog\Jalali\CalendarUtils;
        use Morilog\Jalali\Jalalian;

        $jCheckIn = Jalalian::fromCarbon(\Carbon\Carbon::parse($quote['check_in_date']));
        $jCheckOut = Jalalian::fromCarbon(\Carbon\Carbon::parse($quote['check_out_date']));
        $checkInDayName = $jCheckIn->format('%A');
        $checkOutDayName = $jCheckOut->format('%A');
        $checkInFormatted = CalendarUtils::convertNumbers($jCheckIn->format('d %B Y'));
        $checkOutFormatted = CalendarUtils::convertNumbers($jCheckOut->format('d %B Y'));

        $coverUrl = $property->cover_image ? asset('storage/' . $property->cover_image) : null;
        $host = $property->host;
        $hostUser = $host?->user;
        $hostDisplayName = $host?->display_name ?: ($hostUser?->name ?: 'میزبان اقامتگاه');
        $hostAvatar = $host?->avatar ? asset('storage/' . $host->avatar) : ($hostUser?->avatar_url ?: null);

        $checkInTime = $config?->check_in_time ? substr($config->check_in_time, 0, 5) : '۱۴:۰۰';
        $checkOutTime = $config?->check_out_time ? substr($config->check_out_time, 0, 5) : '۱۲:۰۰';
        $checkInTimeFormatted = CalendarUtils::convertNumbers($checkInTime);
        $checkOutTimeFormatted = CalendarUtils::convertNumbers($checkOutTime);

        $houseRules = $config ? ($config->house_rules ?? []) : [];
        $customRules = $houseRules['custom_rules'] ?? [];

        $isInstant = !empty($quote['instant_booking']);
        $grandTotal = (float) ($quote['grand_total'] ?? 0);
        $prepaymentAmount = (float) ($quote['prepayment_amount'] ?? $grandTotal);
        $remainingAmount = max(0, $grandTotal - $prepaymentAmount);

        $clientName = old('guest_full_name', $client->full_name ?: ($client->first_name ? $client->first_name . ' ' . $client->last_name : ''));
        $clientPhone = old('guest_phone', $client->phone ?: '');
        $clientNationalCode = old('guest_national_code', $client->national_code ?: '');

        $provinceName = is_object($property->province) ? ($property->province->name ?? '') : (string) ($property->province ?? '');
        $cityName = is_object($property->city) ? ($property->city->name ?? '') : (string) ($property->city ?? '');
        $locationLabel = implode('، ', array_filter([$provinceName, $cityName]));
        $categoryName = is_object($property->category) ? ($property->category->name ?? '') : (string) ($property->category ?? '');
    @endphp

    <div class="checkout-pattern min-h-screen py-8 sm:py-12 bg-gray-50/50 dark:bg-gray-950 font-sans" 
         x-data="checkoutApp()">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- نوار بازگشت و مسیر راهنما (Breadcrumb & Header) --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200/80 dark:border-gray-800 pb-6">
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2 text-xs font-bold text-gray-500 dark:text-gray-400">
                        <a href="{{ route('properties.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">املاک و اقامتگاه‌ها</a>
                        <span>/</span>
                        <a href="{{ route('properties.show', $property->slug ?: $property->code) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition line-clamp-1 max-w-[200px]">{{ $property->title }}</a>
                        <span>/</span>
                        <span class="text-gray-900 dark:text-white">سبد خرید و پیش‌فاکتور رزرو</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-indigo-600 dark:bg-indigo-500"></span>
                        <span>پیش‌فاکتور و بررسی نهایی رزرو اقامتگاه</span>
                    </h1>
                </div>

                <a href="{{ route('properties.show', $property->slug ?: $property->code) }}" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition shadow-sm">
                    <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                    <span>بازگشت به تقویم اقامتگاه</span>
                </a>
            </div>

            {{-- پیام‌های سیستمی خطا یا هشدار --}}
            @if(session('error'))
                <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-bold flex items-center gap-3">
                    <svg class="w-5 h-5 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-bold space-y-1">
                    @foreach($errors->all() as $err)
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                            <span>{{ $err }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- خطای ایجکس ثبت رزرو --}}
            <div x-show="errorMessage" 
                 x-text="errorMessage" 
                 class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-bold flex items-center gap-3"
                 style="display: none;">
            </div>

            {{-- فرم اصلی ارسال رزرو --}}
            <form id="checkoutForm" 
                  action="{{ route('properties.book', $property->slug ?: $property->code) }}" 
                  method="POST" 
                  @submit.prevent="submitBooking()">
                @csrf

                <input type="hidden" name="check_in_date" value="{{ $quote['check_in_date'] }}">
                <input type="hidden" name="check_out_date" value="{{ $quote['check_out_date'] }}">
                <input type="hidden" name="guests_count" value="{{ $quote['guests_count'] }}">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    {{-- ستون اصلی: مشخصات اقامتگاه، زمان‌بندی سفر، فرم مسافر، قوانین (۸ ستون) --}}
                    <div class="lg:col-span-7 xl:col-span-8 space-y-6">

                        {{-- ۱. کارت معرفی اقامتگاه و میزبان --}}
                        <div class="bg-white dark:bg-gray-900 rounded-3xl p-5 sm:p-6 border border-gray-200/80 dark:border-gray-800 shadow-sm flex flex-col sm:flex-row gap-5 items-start">
                            <div class="w-full sm:w-44 h-36 rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-800 shrink-0 relative">
                                @if($coverUrl)
                                    <img src="{{ $coverUrl }}" alt="{{ $property->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-8 h-8 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                                <span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-lg bg-black/60 backdrop-blur text-white text-[10px] font-bold">
                                    کد: {{ CalendarUtils::convertNumbers($property->code) }}
                                </span>
                            </div>

                            <div class="flex-1 space-y-3 w-full">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 text-xs font-bold">
                                        {{ $categoryName ?: 'اقامتگاه بوم‌گردی و ویلا' }}
                                    </span>
                                    @if($isInstant)
                                        <span class="px-2.5 py-1 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 text-xs font-black flex items-center gap-1">
                                            <span>⚡</span>
                                            <span>رزرو قطعی آنی</span>
                                        </span>
                                    @endif
                                </div>

                                <h2 class="text-base sm:text-lg font-black text-gray-900 dark:text-white leading-snug">
                                    {{ $property->title }}
                                </h2>

                                <div class="flex items-center gap-4 text-xs font-bold text-gray-500 dark:text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>{{ $locationLabel ?: 'ایران' }}</span>
                                    </span>
                                    <span>•</span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span>میزبان: {{ $hostDisplayName }}</span>
                                    </span>
                                </div>

                                {{-- ساعت رسمی تحویل و تخلیه --}}
                                <div class="pt-2 border-t border-gray-100 dark:border-gray-800 grid grid-cols-2 gap-3 text-xs font-bold">
                                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                        <span>ساعت تحویل کلید:</span>
                                        <span class="text-gray-900 dark:text-white font-black">از {{ $checkInTimeFormatted }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        <span>ساعت تخلیه اقامتگاه:</span>
                                        <span class="text-gray-900 dark:text-white font-black">تا {{ $checkOutTimeFormatted }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ۲. کارت زمان‌بندی و مشخصات اقامت --}}
                        <div class="bg-white dark:bg-gray-900 rounded-3xl p-5 sm:p-6 border border-gray-200/80 dark:border-gray-800 shadow-sm space-y-4">
                            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                                <h3 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>مشخصات زمان‌بندی و تعداد مسافران</span>
                                </h3>
                                <a href="{{ route('properties.show', $property->slug ?: $property->code) }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                    ویرایش تاریخ یا نفرات
                                </a>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                {{-- تاریخ ورود --}}
                                <div class="p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800 space-y-1">
                                    <span class="text-[11px] font-bold text-gray-400 block">تاریخ ورود</span>
                                    <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 block">{{ $checkInDayName }}</span>
                                    <span class="text-sm font-black text-gray-900 dark:text-white block">{{ $checkInFormatted }}</span>
                                </div>

                                {{-- مدت اقامت --}}
                                <div class="p-3.5 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-100/60 dark:border-indigo-900/40 flex flex-col items-center justify-center text-center space-y-1">
                                    <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400">مدت کل اقامت</span>
                                    <span class="text-base font-black text-indigo-700 dark:text-indigo-300">
                                        {{ CalendarUtils::convertNumbers($quote['nights_count']) }} شب
                                    </span>
                                </div>

                                {{-- تاریخ خروج --}}
                                <div class="p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800 space-y-1">
                                    <span class="text-[11px] font-bold text-gray-400 block">تاریخ خروج</span>
                                    <span class="text-xs font-black text-rose-600 dark:text-rose-400 block">{{ $checkOutDayName }}</span>
                                    <span class="text-sm font-black text-gray-900 dark:text-white block">{{ $checkOutFormatted }}</span>
                                </div>
                            </div>

                            <div class="p-3 rounded-2xl bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs font-bold text-gray-700 dark:text-gray-300">
                                <span>تعداد نفرات مسافر:</span>
                                <span class="text-gray-900 dark:text-white font-black">
                                    {{ CalendarUtils::convertNumbers($quote['guests_count']) }} نفر
                                    @if(!empty($quote['extra_guests_count']))
                                        <span class="text-[11px] text-amber-600 dark:text-amber-400 font-bold">
                                            (شامل {{ CalendarUtils::convertNumbers($quote['extra_guests_count']) }} نفر اضافه بر ظرفیت پایه)
                                        </span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        {{-- ۳. فرم اطلاعات سرپرست اقامت و یادداشت برای میزبان --}}
                        <div class="bg-white dark:bg-gray-900 rounded-3xl p-5 sm:p-6 border border-gray-200/80 dark:border-gray-800 shadow-sm space-y-5">
                            <div class="border-b border-gray-100 dark:border-gray-800 pb-3">
                                <h3 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span>مشخصات سرپرست اقامت و مسافر اصلی</span>
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    پیامک‌های اطلاع‌رسانی، پیگیری و سند رسمی رزرو به این اطلاعات ارسال خواهد شد.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-bold">
                                <div>
                                    <label class="block text-gray-700 dark:text-gray-300 mb-1.5">
                                        نام و نام خانوادگی سرپرست <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="guest_full_name" 
                                           x-model="formData.guestFullName" 
                                           required
                                           placeholder="مثال: علی احمدی" 
                                           class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/80 px-4 py-3 text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-sans">
                                </div>

                                <div>
                                    <label class="block text-gray-700 dark:text-gray-300 mb-1.5">
                                        شماره موبایل جهت هماهنگی و پیامک <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="guest_phone" 
                                           x-model="formData.guestPhone" 
                                           required
                                           dir="ltr"
                                           placeholder="۰۹۱۲۳۴۵۶۷۸۹" 
                                           class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/80 px-4 py-3 text-xs font-bold text-right text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-sans">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-gray-700 dark:text-gray-300 mb-1.5">
                                        کد ملی مسافر اصلی (اختیاری - جهت تطبیق هنگام تحویل اقامتگاه)
                                    </label>
                                    <input type="text" 
                                           name="guest_national_code" 
                                           x-model="formData.guestNationalCode" 
                                           dir="ltr"
                                           placeholder="۱۰ رقم کد ملی" 
                                           class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/80 px-4 py-3 text-xs font-bold text-right text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-sans">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-gray-700 dark:text-gray-300 mb-1.5">
                                        درخواست یا یادداشت ویژه برای میزبان (اختیاری)
                                    </label>
                                    <textarea name="notes" 
                                              x-model="formData.notes" 
                                              rows="3" 
                                              placeholder="اگر درخواست ویژه‌ای در مورد ساعت ورود، همراهان یا امکانات اقامتگاه دارید یادداشت کنید..." 
                                              class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/80 px-4 py-3 text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 resize-none transition font-sans"></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- ۴. کارت قوانین و شرایط کنسلی --}}
                        <div class="bg-white dark:bg-gray-900 rounded-3xl p-5 sm:p-6 border border-gray-200/80 dark:border-gray-800 shadow-sm space-y-5">
                            <div class="border-b border-gray-100 dark:border-gray-800 pb-3">
                                <h3 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>مقررات اقامتگاه و ضوابط لغو رزرو</span>
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs font-bold">
                                <div class="flex items-center gap-2 p-3 rounded-2xl bg-gray-50 dark:bg-gray-800/60 text-gray-700 dark:text-gray-300">
                                    <span class="w-2 h-2 rounded-full {{ !empty($houseRules['pets_allowed']) ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    <span>ورود حیوانات خانگی:</span>
                                    <span class="{{ !empty($houseRules['pets_allowed']) ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ !empty($houseRules['pets_allowed']) ? 'مجاز است' : 'ممنوع است' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 p-3 rounded-2xl bg-gray-50 dark:bg-gray-800/60 text-gray-700 dark:text-gray-300">
                                    <span class="w-2 h-2 rounded-full {{ !empty($houseRules['smoking_allowed']) ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    <span>استعمال دخانیات:</span>
                                    <span class="{{ !empty($houseRules['smoking_allowed']) ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ !empty($houseRules['smoking_allowed']) ? 'مجاز است' : 'در فضای سرپوشیده ممنوع' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 p-3 rounded-2xl bg-gray-50 dark:bg-gray-800/60 text-gray-700 dark:text-gray-300">
                                    <span class="w-2 h-2 rounded-full {{ !empty($houseRules['parties_allowed']) ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    <span>برگزاری مراسم و جشن:</span>
                                    <span class="{{ !empty($houseRules['parties_allowed']) ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ !empty($houseRules['parties_allowed']) ? 'مجاز است' : 'ممنوع است' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 p-3 rounded-2xl bg-gray-50 dark:bg-gray-800/60 text-gray-700 dark:text-gray-300">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    <span>مدارک شناسایی:</span>
                                    <span class="text-gray-800 dark:text-gray-200">ارائه اصل کارت ملی الزامی است</span>
                                </div>
                            </div>

                            @if(!empty($customRules) && is_array($customRules))
                                <div class="pt-3 border-t border-gray-100 dark:border-gray-800 space-y-1.5">
                                    <span class="block text-xs font-black text-gray-800 dark:text-gray-200">سایر قوانین اختصاصی میزبان:</span>
                                    <ul class="space-y-1 text-xs text-gray-600 dark:text-gray-400 list-disc list-inside">
                                        @foreach($customRules as $cRule)
                                            <li>{{ $cRule }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- چک‌باکس پذیرش قوانین --}}
                            <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                                <label class="flex items-start gap-3 cursor-pointer select-none">
                                    <input type="checkbox" 
                                           x-model="rulesAccepted" 
                                           class="mt-0.5 rounded-lg border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 leading-relaxed">
                                        کلیه قوانین اقامتگاه، ساعت ورود و خروج و شرایط لغو رزرو پلتفرم را مطالعه نموده و متعهد به رعایت آن می‌باشم. <span class="text-rose-500">*</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                    </div>

                    {{-- ستون سایدبار چسبان (Sticky): پیش‌فاکتور مالی، جدول ریز مبالغ، دکمه قطعی‌سازی (۴ ستون) --}}
                    <div class="lg:col-span-5 xl:col-span-4 sticky top-6 space-y-5">

                        <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 border border-gray-200/80 dark:border-gray-800 shadow-xl space-y-6">

                            {{-- هدر پیش‌فاکتور --}}
                            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                                <div>
                                    <h3 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                        <span>پیش‌فاکتور نهایی اقامت</span>
                                    </h3>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 block font-bold">محاسبه شفاف بر مبنای تقویم میزبان</span>
                                </div>
                                <span class="px-2.5 py-1 rounded-xl bg-gray-100 dark:bg-gray-800 text-[11px] font-bold text-gray-600 dark:text-gray-300">
                                    {{ CalendarUtils::convertNumbers($quote['nights_count']) }} شب
                                </span>
                            </div>

                            {{-- ریز فاکتور شفاف --}}
                            <div class="space-y-3 text-xs font-bold">
                                {{-- شب‌ها --}}
                                <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
                                    <span>هزینه شب‌های اقامت ({{ CalendarUtils::convertNumbers($quote['nights_count']) }} شب):</span>
                                    <span class="text-gray-900 dark:text-white font-black">
                                        {{ CalendarUtils::convertNumbers(number_format($quote['nights_total'])) }} {{ $currencyLabel }}
                                    </span>
                                </div>

                                {{-- آکاردئون نمایش تفکیک روزها --}}
                                @if(!empty($quote['nights_breakdown']) && count($quote['nights_breakdown']) > 1)
                                    <div class="pt-1">
                                        <button type="button" 
                                                @click="showBreakdown = !showBreakdown" 
                                                class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                            <span x-text="showBreakdown ? 'بستن ریز شب‌ها' : 'مشاهده نرخ هر شب'"></span>
                                            <svg class="w-3 h-3 transition-transform" :class="showBreakdown ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        
                                        <div x-show="showBreakdown" class="mt-2 p-3 rounded-2xl bg-gray-50 dark:bg-gray-800/50 space-y-1.5 text-[11px] font-medium border border-gray-100 dark:border-gray-800" style="display: none;">
                                            @foreach($quote['nights_breakdown'] as $night)
                                                <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                                                    <span>{{ CalendarUtils::convertNumbers($night['jalali_date']) }} ({{ $night['title'] ?? 'نرخ عادی' }}):</span>
                                                    <span class="text-gray-800 dark:text-gray-200 font-bold">{{ CalendarUtils::convertNumbers(number_format($night['price'])) }} {{ $currencyLabel }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- نفرات اضافه --}}
                                @if(!empty($quote['extra_guests_fee']) && $quote['extra_guests_fee'] > 0)
                                    <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
                                        <span>هزینه نفرات اضافه:</span>
                                        <span class="text-gray-900 dark:text-white font-black">
                                            {{ CalendarUtils::convertNumbers(number_format($quote['extra_guests_fee'])) }} {{ $currencyLabel }}
                                        </span>
                                    </div>
                                @endif

                                {{-- هزینه نظافت --}}
                                @if(!empty($quote['cleaning_fee']) && $quote['cleaning_fee'] > 0)
                                    <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
                                        <span>هزینه نظافت و خدمات:</span>
                                        <span class="text-gray-900 dark:text-white font-black">
                                            {{ CalendarUtils::convertNumbers(number_format($quote['cleaning_fee'])) }} {{ $currencyLabel }}
                                        </span>
                                    </div>
                                @endif

                                {{-- خط جداکننده جمع کل --}}
                                <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-sm font-black">
                                    <span class="text-gray-900 dark:text-white">مبلغ کل اقامت:</span>
                                    <span class="text-base text-indigo-600 dark:text-indigo-400">
                                        {{ CalendarUtils::convertNumbers(number_format($grandTotal)) }} {{ $currencyLabel }}
                                    </span>
                                </div>

                                {{-- تفکیک پیش‌پرداخت و تسویه در محل (در صورت کمتر بودن از ۱۰۰٪) --}}
                                @if($remainingAmount > 0)
                                    <div class="p-3 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/70 dark:border-amber-800/50 space-y-2 text-xs font-bold">
                                        <div class="flex items-center justify-between text-amber-900 dark:text-amber-200">
                                            <span>مبلغ پیش‌پرداخت آنلاین:</span>
                                            <span class="font-black">{{ CalendarUtils::convertNumbers(number_format($prepaymentAmount)) }} {{ $currencyLabel }}</span>
                                        </div>
                                        <div class="flex items-center justify-between text-gray-600 dark:text-gray-300 pt-1.5 border-t border-amber-200/60 dark:border-amber-800/40">
                                            <span>مابقی تسویه در محل تحویل کلید:</span>
                                            <span class="font-black">{{ CalendarUtils::convertNumbers(number_format($remainingAmount)) }} {{ $currencyLabel }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- بنر راهنمای نوع رزرو --}}
                            @if($isInstant)
                                <div class="p-3 rounded-2xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/40 text-amber-900 dark:text-amber-200 text-xs font-bold flex items-center gap-2">
                                    <span class="text-base">⚡</span>
                                    <span>این اقامتگاه نیاز به تأیید میزبان ندارد و با فشردن دکمه زیر به درگاه پرداخت متصل خواهید شد.</span>
                                </div>
                            @else
                                <div class="p-3 rounded-2xl bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200/80 dark:border-indigo-800/40 text-indigo-900 dark:text-indigo-200 text-xs font-bold flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                    <span>در این مرحله مبلغی پرداخت نمی‌شود؛ پس از ارسال درخواست و تأیید میزبان، پیامک جهت واریز ارسال خواهد شد.</span>
                                </div>
                            @endif

                            {{-- دکمه قطعی‌سازی اقدام --}}
                            <div class="space-y-3 pt-2">
                                <button type="submit" 
                                        :disabled="submitting || !rulesAccepted"
                                        class="w-full py-4 px-5 rounded-2xl {{ $isInstant ? 'bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 shadow-amber-500/30' : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-600/30' }} text-white font-black text-sm transition-all shadow-lg flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span x-show="!submitting">
                                        {{ $isInstant ? 'تأیید نهایی و پرداخت آنلاین ⚡' : 'تأیید نهایی و ارسال درخواست رزرو' }}
                                    </span>
                                    <span x-show="submitting" class="flex items-center gap-2" style="display: none;">
                                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        <span>در حال پردازش و ثبت...</span>
                                    </span>
                                </button>

                                <p x-show="!rulesAccepted" class="text-center text-[11px] text-amber-600 dark:text-amber-400 font-bold">
                                    لطفاً جهت فعال شدن دکمه، تیک پذیرش قوانین اقامتگاه را بزنید.
                                </p>
                            </div>

                            {{-- ضمانت‌های پلتفرم --}}
                            <div class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-2 text-[11px] font-bold text-gray-500 dark:text-gray-400">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span>ضمانت تطابق مشخصات و تمیزی اقامتگاه</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span>پشتیبانی شبانه‌روزی مسافر در طول اقامت</span>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>
            </form>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function checkoutApp() {
            return {
                showBreakdown: false,
                rulesAccepted: true,
                submitting: false,
                errorMessage: '',
                formData: {
                    guestFullName: '{{ addslashes($clientName) }}',
                    guestPhone: '{{ addslashes($clientPhone) }}',
                    guestNationalCode: '{{ addslashes($clientNationalCode) }}',
                    notes: ''
                },

                async submitBooking() {
                    this.errorMessage = '';

                    if (!this.rulesAccepted) {
                        this.errorMessage = 'لطفاً ابتدا قوانین اقامتگاه و ضوابط لغو رزرو را تأیید فرمایید.';
                        return;
                    }

                    if (!this.formData.guestFullName || !this.formData.guestFullName.trim()) {
                        this.errorMessage = 'لطفاً نام و نام خانوادگی سرپرست مسافران را وارد نمایید.';
                        return;
                    }

                    if (!this.formData.guestPhone || !this.formData.guestPhone.trim()) {
                        this.errorMessage = 'لطفاً شماره تماس سرپرست را وارد فرمایید.';
                        return;
                    }

                    this.submitting = true;

                    try {
                        let form = document.getElementById('checkoutForm');
                        let tokenEl = document.querySelector('meta[name="csrf-token"]');
                        let csrfToken = tokenEl ? tokenEl.getAttribute('content') : '{{ csrf_token() }}';

                        let payload = {
                            check_in_date: '{{ $quote["check_in_date"] }}',
                            check_out_date: '{{ $quote["check_out_date"] }}',
                            guests_count: {{ (int) $quote['guests_count'] }},
                            guest_full_name: this.formData.guestFullName,
                            guest_phone: this.formData.guestPhone,
                            guest_national_code: this.formData.guestNationalCode,
                            notes: this.formData.notes
                        };

                        let response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify(payload)
                        });

                        let data = await response.json();

                        if (response.status === 401 || data.require_login) {
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                                return;
                            }
                        }

                        if (!response.ok || !data.success) {
                            this.errorMessage = data.message || 'خطا در ثبت درخواست رزرو. لطفاً مجدداً تلاش فرمایید.';
                            this.submitting = false;
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            return;
                        }

                        // موفقیت‌آمیز: هدایت به سند رزرو یا پرداخت
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            window.location.reload();
                        }

                    } catch (err) {
                        console.error('Checkout submit error:', err);
                        this.errorMessage = 'خطای اتصال به سرور. لطفاً اتصال اینترنت خود را بررسی نمایید.';
                        this.submitting = false;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                }
            };
        }
    </script>
@endpush
