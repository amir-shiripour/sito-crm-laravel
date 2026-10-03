@extends('layouts.user')

@php
    $title = 'میزکار میزبانی اقامتگاه';
    $cardClass = "bg-white dark:bg-gray-800/90 backdrop-blur-md rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-sm overflow-hidden transition-all duration-300";
    $badgeClass = "inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold font-sans";
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 space-y-8" x-data="{ viewMode: 'cards' }">

    {{-- بنر پرمیوم و پروفایل میزبان --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900 via-indigo-800 to-purple-900 text-white p-6 sm:p-8 md:p-10 shadow-xl shadow-indigo-950/20">
        {{-- افکت‌های نوری ملایم --}}
        <div class="absolute -top-24 -left-24 w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="relative">
                    <div class="w-20 h-20 rounded-3xl bg-white/10 backdrop-blur-md border border-white/20 text-white flex items-center justify-center font-black text-2xl overflow-hidden shadow-2xl flex-shrink-0">
                        @if($host->avatar)
                            <img src="{{ asset('storage/' . $host->avatar) }}" class="w-full h-full object-cover">
                        @else
                            {{ mb_substr($host->display_name, 0, 1) }}
                        @endif
                    </div>
                    @if($host->status === 'active')
                        <span class="absolute -bottom-1 -left-1 w-6 h-6 rounded-full bg-emerald-500 border-2 border-indigo-900 flex items-center justify-center text-white" title="میزبان فعال و تایید شده">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                        </span>
                    @endif
                </div>

                <div class="space-y-1.5">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-black text-white">{{ $host->display_name }}</h1>
                        @if($host->status === 'active')
                            <span class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-xs font-bold font-sans">
                                میزبان فعال
                            </span>
                        @elseif($host->status === 'pending')
                            <span class="px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-400/30 text-xs font-bold font-sans">
                                در انتظار تایید مدیریت
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full bg-red-500/20 text-red-300 border border-red-400/30 text-xs font-bold font-sans">
                                حساب معلق
                            </span>
                        @endif

                        @if($host->kyc_status === 'approved')
                            <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/30 text-indigo-200 border border-indigo-400/20 text-[11px] font-bold font-sans flex items-center gap-1">
                                <svg class="w-3 h-3 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                هویت احراز شده
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-indigo-200/80 font-sans">
                        شماره هماهنگی: <span class="dir-ltr inline-block font-bold">{{ $host->phone }}</span> • تاریخ عضویت: <span class="font-bold">{{ \Morilog\Jalali\Jalalian::fromCarbon($host->created_at)->format('Y/m/d') }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('user.properties.hosts.profile') }}" class="px-4 py-3 rounded-2xl bg-white/10 hover:bg-white/15 backdrop-blur-md border border-white/15 text-white text-xs font-bold transition flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    تنظیمات پروفایل و شبا
                </a>

                <a href="{{ route('user.properties.create') }}?type=daily_rental" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-amber-400 to-orange-500 hover:from-amber-500 hover:to-orange-600 text-slate-950 font-black text-xs shadow-lg shadow-orange-500/25 transition transform active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    ثبت اقامتگاه جدید
                </a>
            </div>
        </div>
    </div>

    {{-- کارت‌های آمار Bento Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        {{-- کل اقامتگاه‌ها --}}
        <div class="{{ $cardClass }} p-5 sm:p-6 bg-gradient-to-br from-white to-gray-50/50 dark:from-gray-800 dark:to-gray-800/60 relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">کل اقامتگاه‌ها</span>
                <div class="w-9 h-9 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-gray-900 dark:text-white font-sans">{{ $stats['total'] }}</span>
                <span class="text-xs text-gray-400 font-sans">واحد</span>
            </div>
        </div>

        {{-- اقامتگاه‌های منتشر شده --}}
        <div class="{{ $cardClass }} p-5 sm:p-6 bg-gradient-to-br from-white to-emerald-50/20 dark:from-gray-800 dark:to-gray-800/60 border-emerald-100 dark:border-emerald-900/30 relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400">منتشر و آماده رزرو</span>
                <div class="w-9 h-9 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400 font-sans">{{ $stats['approved'] }}</span>
                <span class="text-xs text-emerald-600/70 font-sans">فعال در سایت</span>
            </div>
        </div>

        {{-- در انتظار بررسی --}}
        <div class="{{ $cardClass }} p-5 sm:p-6 bg-gradient-to-br from-white to-amber-50/20 dark:from-gray-800 dark:to-gray-800/60 border-amber-100 dark:border-amber-900/30 relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-amber-700 dark:text-amber-400">در انتظار تایید</span>
                <div class="w-9 h-9 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-amber-600 dark:text-amber-400 font-sans">{{ $stats['pending'] }}</span>
                <span class="text-xs text-amber-600/70 font-sans">در صف بازبینی</span>
            </div>
        </div>

        {{-- رد شده / نیاز به اصلاح --}}
        <div class="{{ $cardClass }} p-5 sm:p-6 bg-gradient-to-br from-white to-red-50/20 dark:from-gray-800 dark:to-gray-800/60 border-red-100 dark:border-red-900/30 relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-red-700 dark:text-red-400">نیاز به ویرایش</span>
                <div class="w-9 h-9 rounded-2xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-red-600 dark:text-red-400 font-sans">{{ $stats['rejected'] }}</span>
                <span class="text-xs text-red-600/70 font-sans">رد شده</span>
            </div>
        </div>
    </div>

    {{-- بنر وضعیت مالی و تسویه --}}
    <div class="{{ $cardClass }} p-6 bg-gradient-to-r from-gray-50/70 via-white to-indigo-50/30 dark:from-gray-800 dark:via-gray-800 dark:to-indigo-950/20">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-gray-900 dark:text-white">وضعیت تسویه و شماره شبا</h3>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 font-sans">
                        @if($host->shaba_number)
                            شماره شبا: <span class="font-bold dir-ltr inline-block text-gray-800 dark:text-gray-200">IR {{ $host->shaba_number }}</span> ({{ $host->bank_name ?? 'بانک ثبت شده' }})
                        @else
                            <span class="text-amber-600 dark:text-amber-400 font-bold">شماره شبا هنوز ثبت نشده است. لطفاً جهت دریافت واریزی‌ها شبا را ثبت کنید.</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="text-right sm:text-left">
                    <span class="block text-[10px] text-gray-400 font-sans">نرخ کارمزد پلتفرم:</span>
                    <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 font-sans">{{ $host->effective_commission_rate }}٪</span>
                </div>
                <a href="{{ route('user.properties.hosts.profile') }}" class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    ویرایش شبا
                </a>
            </div>
        </div>
    </div>

    {{-- بخش اصلی: مدیریت اقامتگاه‌ها --}}
    <div class="{{ $cardClass }}">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    اقامتگاه‌ها و ویلاهای شما
                </h2>
                <p class="text-xs text-gray-400 mt-0.5 font-sans">مجموعاً {{ $properties->count() }} اقامتگاه ثبت شده در پنل میزبانی</p>
            </div>

            {{-- دکمه‌های سوئیچ نمایش (کارت / جدول) --}}
            <div class="flex items-center gap-2">
                <div class="bg-gray-100 dark:bg-gray-900/60 p-1 rounded-2xl flex items-center gap-1 border border-gray-200/50 dark:border-gray-700/50">
                    <button type="button" @click="viewMode = 'cards'" :class="viewMode === 'cards' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-400 hover:text-gray-600'" class="p-2 rounded-xl transition text-xs font-bold flex items-center gap-1.5" title="نمایش کارتی">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                        <span class="hidden md:inline font-sans">کارت‌ها</span>
                    </button>
                    <button type="button" @click="viewMode = 'table'" :class="viewMode === 'table' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-400 hover:text-gray-600'" class="p-2 rounded-xl transition text-xs font-bold flex items-center gap-1.5" title="نمایش جدولی">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        <span class="hidden md:inline font-sans">جدول</span>
                    </button>
                </div>

                <a href="{{ route('user.properties.create') }}?type=daily_rental" class="px-4 py-2 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    ثبت جدید
                </a>
            </div>
        </div>

        @if($properties->isEmpty())
            <div class="py-20 text-center space-y-4">
                <div class="w-20 h-20 mx-auto rounded-3xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-500 dark:text-indigo-400 flex items-center justify-center shadow-inner">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">هنوز اقامتگاهی ثبت نکرده‌اید!</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto leading-relaxed">
                        اولین ویلا، سوئیت یا اقامتگاه بوم‌گردی خود را اضافه کنید تا در سایت منتشر شده و رزرو دریافت نمایید.
                    </p>
                </div>
                <a href="{{ route('user.properties.create') }}?type=daily_rental" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-lg shadow-indigo-500/25 transition transform active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    ثبت اولین اقامتگاه
                </a>
            </div>
        @else

            {{-- ۱. حالت نمایش کارتی مدرن (Visual Cards) --}}
            <div x-show="viewMode === 'cards'" class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($properties as $property)
                    <div class="rounded-3xl border border-gray-200/80 dark:border-gray-700/80 bg-white dark:bg-gray-800/80 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
                        <div>
                            {{-- تصویر و بج‌های روی کاور --}}
                            <div class="relative h-48 bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                @if($property->cover_image)
                                    <img src="{{ asset('storage/' . $property->cover_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                                        <svg class="w-10 h-10 stroke-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        <span class="text-[11px] mt-1">بدون تصویر</span>
                                    </div>
                                @endif

                                {{-- وضعیت تایید بالای تصویر --}}
                                <div class="absolute top-3 right-3">
                                    @if($property->approval_status === 'approved')
                                        <span class="px-3 py-1 rounded-xl bg-emerald-500/90 backdrop-blur-md text-white text-[11px] font-bold font-sans shadow-md">
                                            تأیید شده
                                        </span>
                                    @elseif($property->approval_status === 'pending_review')
                                        <span class="px-3 py-1 rounded-xl bg-amber-500/90 backdrop-blur-md text-white text-[11px] font-bold font-sans shadow-md">
                                            در انتظار بررسی
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-xl bg-red-500/90 backdrop-blur-md text-white text-[11px] font-bold font-sans shadow-md" title="{{ $property->rejection_reason }}">
                                            رد شده
                                        </span>
                                    @endif
                                </div>

                                {{-- کد ملک --}}
                                <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-xl bg-black/60 backdrop-blur-md text-white text-[10px] font-bold font-sans">
                                    کد: {{ $property->code }}
                                </div>
                            </div>

                            {{-- محتوای کارت --}}
                            <div class="p-5 space-y-3">
                                <div>
                                    <h3 class="font-bold text-gray-900 dark:text-white line-clamp-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition" title="{{ $property->title }}">
                                        {{ $property->title }}
                                    </h3>
                                    <p class="text-xs text-gray-400 mt-1 line-clamp-1 font-sans">
                                        {{ $property->address ?? 'آدرس مشخص نشده' }}
                                    </p>
                                </div>

                                {{-- ظرفیت و اتاق‌ها --}}
                                <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 font-sans py-2 border-y border-gray-100 dark:border-gray-700/60">
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        <span>ظرفیت: <strong>{{ $property->rentalConfig->base_guests ?? 2 }} تا {{ $property->rentalConfig->max_guests ?? 4 }}</strong> نفر</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                        <span><strong>{{ $property->rentalConfig->bedrooms ?? 1 }}</strong> خواب</span>
                                    </div>
                                </div>

                                {{-- قیمت شبانه --}}
                                <div class="flex items-baseline justify-between pt-1">
                                    <span class="text-xs text-gray-400 font-sans">نرخ هر شب:</span>
                                    @if($property->rentalConfig && $property->rentalConfig->price_per_night > 0)
                                        <div class="text-left font-sans">
                                            <span class="text-base font-black text-indigo-600 dark:text-indigo-400">
                                                {{ number_format($property->rentalConfig->price_per_night) }}
                                            </span>
                                            <span class="text-[11px] text-gray-400">تومان</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-amber-600 dark:text-amber-400 font-sans font-bold">تنظیم نشده</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- عملیات کارت --}}
                        <div class="p-4 bg-gray-50/70 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700/60 grid grid-cols-3 gap-2">
                            <a href="{{ route('user.properties.rental.calendar', $property) }}" class="py-2.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 dark:bg-teal-950/40 dark:hover:bg-teal-900/50 dark:text-teal-300 text-xs font-bold text-center transition flex items-center justify-center gap-1 font-sans" title="تقویم روزانه و نرخ‌ها">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                تقویم
                            </a>

                            <a href="{{ route('user.properties.rental.config', $property) }}" class="py-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/50 dark:text-indigo-300 text-xs font-bold text-center transition flex items-center justify-center gap-1 font-sans" title="امکانات و قوانین">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                                مشخصات
                            </a>

                            <a href="{{ route('user.properties.edit', $property) }}" class="py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 text-xs font-bold text-center transition flex items-center justify-center gap-1 font-sans" title="ویرایش کامل">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                ویرایش
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ۲. حالت نمایش جدولی فشرده (Table View) --}}
            <div x-show="viewMode === 'table'" class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40 text-xs font-bold text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4">عنوان اقامتگاه</th>
                            <th class="px-6 py-4">ظرفیت و خواب</th>
                            <th class="px-6 py-4">قیمت هر شب</th>
                            <th class="px-6 py-4">وضعیت انتشار</th>
                            <th class="px-6 py-4 text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @foreach($properties as $property)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/20 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-700 overflow-hidden flex-shrink-0 border border-gray-200 dark:border-gray-600 shadow-sm">
                                            @if($property->cover_image)
                                                <img src="{{ asset('storage/' . $property->cover_image) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                    <svg class="w-6 h-6 stroke-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900 dark:text-white">{{ $property->title }}</span>
                                            <span class="text-xs text-gray-400 font-sans mt-0.5">کد: {{ $property->code }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-xs font-sans text-gray-600 dark:text-gray-300">
                                    <div>ظرفیت: <strong>{{ $property->rentalConfig->base_guests ?? 2 }} تا {{ $property->rentalConfig->max_guests ?? 4 }}</strong> نفر</div>
                                    <div class="text-gray-400 mt-0.5"><strong>{{ $property->rentalConfig->bedrooms ?? 1 }}</strong> خواب • <strong>{{ $property->rentalConfig->bathrooms ?? 1 }}</strong> سرویس</div>
                                </td>

                                <td class="px-6 py-4">
                                    @if($property->rentalConfig && $property->rentalConfig->price_per_night > 0)
                                        <span class="font-bold text-indigo-600 dark:text-indigo-400 font-sans">
                                            {{ number_format($property->rentalConfig->price_per_night) }} تومان / شب
                                        </span>
                                    @else
                                        <span class="text-xs text-amber-600 dark:text-amber-400 font-sans">تنظیم نشده</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    @if($property->approval_status === 'approved')
                                        <span class="{{ $badgeClass }} bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40">تأیید شده</span>
                                    @elseif($property->approval_status === 'pending_review')
                                        <span class="{{ $badgeClass }} bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800/40">در انتظار بررسی</span>
                                    @else
                                        <span class="{{ $badgeClass }} bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300 border border-red-200 dark:border-red-800/40" title="{{ $property->rejection_reason }}">رد شده</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('user.properties.rental.calendar', $property) }}" class="p-2.5 rounded-xl bg-teal-50 text-teal-600 hover:bg-teal-100 dark:bg-teal-950/40 dark:text-teal-300 dark:hover:bg-teal-900/50 transition" title="مدیریت تقویم و قیمت‌ها">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        </a>

                                        <a href="{{ route('user.properties.rental.config', $property) }}" class="p-2.5 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-300 dark:hover:bg-indigo-900/50 transition" title="مشخصات و قوانین اقامتگاه">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                                        </a>

                                        <a href="{{ route('user.properties.edit', $property) }}" class="p-2.5 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition" title="ویرایش اطلاعات کلی">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
