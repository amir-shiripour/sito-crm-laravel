@extends('layouts.user')

@php
    $title = 'پروفایل میزبانی و اطلاعات بانکی';
    $cardClass = "bg-white dark:bg-gray-800/90 backdrop-blur-md rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-sm overflow-hidden transition-all duration-300";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-2xl border-gray-200 bg-gray-50/70 px-4 py-3 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-500 font-sans";
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 space-y-8" x-data="hostProfileManager()">

    {{-- بنر پرمیوم سربرگ و اطلاعات میزبان --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900 via-indigo-800 to-purple-900 text-white p-6 sm:p-8 shadow-xl shadow-indigo-950/20">
        {{-- افکت‌های نوری ملایم --}}
        <div class="absolute -top-24 -left-24 w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4 sm:gap-5">
                <div class="relative">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-3xl bg-white/10 backdrop-blur-md border border-white/20 text-white flex items-center justify-center font-black text-2xl overflow-hidden shadow-2xl flex-shrink-0">
                        <template x-if="avatarPreview">
                            <img :src="avatarPreview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!avatarPreview">
                            @if($host->avatar)
                                <img src="{{ asset('storage/' . $host->avatar) }}" class="w-full h-full object-cover">
                            @else
                                <span class="font-sans font-black">{{ mb_substr($host->display_name, 0, 1) }}</span>
                            @endif
                        </template>
                    </div>
                    @if($host->status === 'active')
                        <span class="absolute -bottom-1 -left-1 w-6 h-6 rounded-full bg-emerald-500 border-2 border-indigo-900 flex items-center justify-center text-white" title="میزبان تایید شده">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                        </span>
                    @endif
                </div>

                <div class="space-y-1.5">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-black text-white font-sans">{{ $host->display_name }}</h1>

                        @if($host->status === 'active')
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-xs font-bold font-sans">
                                میزبان فعال
                            </span>
                        @elseif($host->status === 'pending')
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-400/30 text-xs font-bold font-sans">
                                در انتظار تأیید مدیریت
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full bg-red-500/20 text-red-300 border border-red-400/30 text-xs font-bold font-sans">
                                حساب غیرفعال
                            </span>
                        @endif

                        @if($host->kyc_status === 'approved')
                            <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/30 text-indigo-200 border border-indigo-400/20 text-xs font-bold font-sans flex items-center gap-1">
                                <svg class="w-3 h-3 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                هویت احراز شده
                            </span>
                        @elseif($host->kyc_status === 'pending')
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-500/30 text-amber-200 border border-amber-400/20 text-xs font-bold font-sans">
                                مدارک در حال بررسی
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-4 text-xs text-indigo-200/80 font-sans flex-wrap">
                        <span>شماره تماس: <span class="dir-ltr inline-block font-bold">{{ $host->phone }}</span></span>
                        <span>•</span>
                        <span>تاریخ عضویت: <span class="font-bold">{{ \Morilog\Jalali\Jalalian::fromCarbon($host->created_at)->format('Y/m/d') }}</span></span>
                        <span>•</span>
                        <span class="text-amber-300">نرخ کارمزد: <span class="font-bold">{{ $host->effective_commission_rate }}٪</span></span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('user.properties.hosts.dashboard') }}" class="px-4 py-2.5 rounded-2xl bg-white/10 hover:bg-white/15 backdrop-blur-md border border-white/15 text-white text-xs font-bold transition flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                    میزکار میزبانی
                </a>

                <a href="{{ route('user.properties.create') }}?type=daily_rental" class="px-4 py-2.5 rounded-2xl bg-amber-400 hover:bg-amber-500 text-slate-950 font-black text-xs transition flex items-center gap-2 shadow-lg shadow-amber-400/20">
                    <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    ثبت اقامتگاه
                </a>
            </div>
        </div>
    </div>

    {{-- پیام‌های سیستمی موفقیت و خطا --}}
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center gap-3 font-sans">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 text-sm font-sans space-y-1">
            <div class="font-bold flex items-center gap-2">
                <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                لطفاً خطاهای زیر را اصلاح فرمایید:
            </div>
            <ul class="list-disc list-inside mr-6 text-xs space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- فرم ویرایش پروفایل و اطلاعات مالی --}}
    <form action="{{ route('user.properties.hosts.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8" @submit="isSubmitting = true">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- ستون راست: اطلاعات عمومی و مدارک هویتی (۷ ستون) --}}
            <div class="lg:col-span-7 space-y-6">

                {{-- کارت ۱: مشخصات عمومی و هویت بصری --}}
                <div class="{{ $cardClass }} p-6 sm:p-7 space-y-6">
                    <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-4">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">مشخصات عمومی و تماس</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">نام و مشخصاتی که مهمانان در صفحه اقامتگاه‌های شما مشاهده می‌کنند.</p>
                        </div>
                    </div>

                    <div class="space-y-5">
                        {{-- نام نمایشی و تلفن --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $labelClass }}">
                                    نام نمایشی میزبان / مجموعه <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="display_name" x-model="formData.displayName" required class="{{ $inputClass }}" placeholder="مثلاً: اقامتگاه بوم‌گردی کاشانه">
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">
                                    شماره تماس هماهنگی <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="phone" x-model="formData.phone" required class="{{ $inputClass }} text-left dir-ltr font-sans" placeholder="09123456789">
                            </div>
                        </div>

                        {{-- متن معرفی میزبان --}}
                        <div>
                            <label class="{{ $labelClass }}">درباره میزبان و اقامتگاه‌ها</label>
                            <textarea name="about" rows="3" class="{{ $inputClass }} resize-none" placeholder="توضیح مختصری درباره تجربه میزبانی، سوابق و خدمات ویژه اقامتگاه‌های خود بنویسید...">{{ old('about', $host->about) }}</textarea>
                            <span class="text-[11px] text-gray-400 mt-1 block">این معرفی کوتاه، اعتماد مهمانان را به رزرو اقامتگاه‌های شما افزایش می‌دهد.</span>
                        </div>

                        {{-- آپلودر آواتار با پیش‌نمایش --}}
                        <div>
                            <label class="{{ $labelClass }}">تصویر پروفایل میزبان</label>
                            <div class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800">
                                <div class="w-16 h-16 rounded-2xl overflow-hidden bg-gray-200 dark:bg-gray-700 flex-shrink-0 border border-gray-200 dark:border-gray-600 shadow-inner">
                                    <template x-if="avatarPreview">
                                        <img :src="avatarPreview" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!avatarPreview">
                                        @if($host->avatar)
                                            <img src="{{ asset('storage/' . $host->avatar) }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-400 font-sans font-bold text-xl">
                                                {{ mb_substr($host->display_name, 0, 1) }}
                                            </div>
                                        @endif
                                    </template>
                                </div>

                                <div class="space-y-1">
                                    <label class="inline-block px-4 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-bold text-indigo-600 dark:text-indigo-400 shadow-sm cursor-pointer hover:bg-indigo-50 dark:hover:bg-gray-700/60 transition">
                                        انتخاب تصویر جدید
                                        <input type="file" name="avatar" accept="image/*" class="hidden" @change="previewImage($event, 'avatarPreview')">
                                    </label>
                                    <span class="block text-[11px] text-gray-400">فرمت‌های مجاز JPG یا PNG تا حداکثر ۳ مگابایت</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- کارت ۲: امنیت و احراز هویت (KYC) --}}
                <div class="{{ $cardClass }} p-6 sm:p-7 space-y-6">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">احراز هویت و مدارک امنیتی</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">ثبت مدارک هویتی جهت دریافت نشان اصالت و فعال‌سازی تسویه منظم</p>
                            </div>
                        </div>

                        {{-- نشان وضعیت احراز هویت --}}
                        @if($host->kyc_status === 'approved')
                            <span class="px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-xs font-bold font-sans">
                                تایید شده
                            </span>
                        @elseif($host->kyc_status === 'pending')
                            <span class="px-3 py-1 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 text-xs font-bold font-sans">
                                در صف بررسی
                            </span>
                        @elseif($host->kyc_status === 'rejected')
                            <span class="px-3 py-1 rounded-full bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20 text-xs font-bold font-sans">
                                مدارک رد شده
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs font-bold font-sans">
                                ارسال نشده
                            </span>
                        @endif
                    </div>

                    @if($host->kyc_status === 'approved')
                        {{-- حالت تایید شده کامل --}}
                        <div class="p-5 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/20 border border-emerald-200/80 dark:border-emerald-900/40 text-xs text-emerald-900 dark:text-emerald-200 space-y-3 font-sans">
                            <div class="flex items-center gap-2 font-bold text-sm text-emerald-800 dark:text-emerald-300">
                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                هویت شما با موفقیت تأیید شده است
                            </div>
                            <p class="leading-relaxed text-emerald-700 dark:text-emerald-300/90 text-xs">
                                مدارک هویتی شما توسط کارشناسان تأیید شده و حساب شما بدون محدودیت واریز در حال فعالیت است. اقامتگاه‌های شما دارای نشان «میزبان تأیید شده» هستند.
                            </p>
                            <div class="pt-2 border-t border-emerald-200/50 dark:border-emerald-800/40 flex items-center justify-between">
                                <span class="text-emerald-800 dark:text-emerald-300 font-bold">کد ملی ثبت‌شده:</span>
                                <span class="font-bold text-sm tracking-wider dir-ltr text-emerald-900 dark:text-emerald-200">{{ $host->national_code ?? 'ثبت شده' }}</span>
                            </div>
                        </div>

                    @else
                        {{-- حالت در انتظار یا رد شده یا ارسال نشده --}}
                        @if($host->kyc_status === 'rejected')
                            <div class="p-4 rounded-2xl bg-red-50/80 dark:bg-red-950/30 border border-red-200 dark:border-red-900/40 text-xs text-red-900 dark:text-red-200 space-y-1.5 font-sans">
                                <div class="font-bold flex items-center gap-2 text-red-800 dark:text-red-300">
                                    <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    علت عدم تأیید مدارک قبلی:
                                </div>
                                <p class="text-red-700 dark:text-red-300 mr-6">
                                    {{ $host->kyc_rejection_reason ?? 'کیفیت تصویر کارت ملی ناخوانا بوده یا اطلاعات هویتی با اطلاعات بانکی مطابقت ندارد. لطفاً مجدداً بارگذاری فرمایید.' }}
                                </p>
                            </div>
                        @elseif($host->kyc_status === 'pending')
                            <div class="p-4 rounded-2xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/40 text-xs text-amber-900 dark:text-amber-200 space-y-1.5 font-sans">
                                <div class="font-bold flex items-center gap-2 text-amber-800 dark:text-amber-300">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    مدارک شما در حال بررسی است
                                </div>
                                <p class="text-amber-700 dark:text-amber-300 mr-6">
                                    مدارک هویتی شما دریافت شده و طی ۲۴ ساعت کاری توسط پشتیبانی بررسی و تایید می‌گردد. در صورت تمایل می‌توانید مدارک جدید بارگذاری نمایید.
                                </p>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 items-start">
                            <div>
                                <label class="{{ $labelClass }}">کد ملی هوشمند (۱۰ رقمی)</label>
                                <input type="text" name="national_code" value="{{ old('national_code', $host->national_code) }}" maxlength="10" class="{{ $inputClass }} text-left dir-ltr font-sans" placeholder="10 رقمی">
                                <span class="text-[11px] text-gray-400 mt-1 block">کد ملی جهت راستی‌آزمایی با شبا بانکی است.</span>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">تصویر کارت ملی هوشمند</label>
                                <div class="relative border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-2xl p-4 text-center hover:border-indigo-500 transition-colors bg-gray-50/50 dark:bg-gray-900/30">
                                    <template x-if="nationalCardPreview">
                                        <div class="space-y-2">
                                            <img :src="nationalCardPreview" class="max-h-28 mx-auto rounded-xl object-contain border border-gray-200 dark:border-gray-700 shadow-sm">
                                            <button type="button" @click="nationalCardPreview = null" class="text-xs text-red-500 hover:underline font-bold">تغییر تصویر</button>
                                        </div>
                                    </template>
                                    <template x-if="!nationalCardPreview">
                                        <div class="space-y-2">
                                            @if($host->national_card_image)
                                                <div class="space-y-1 mb-2">
                                                    <span class="inline-block px-2 py-0.5 rounded-lg bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 text-[10px] font-bold">مدرک قبلاً بارگذاری شده</span>
                                                </div>
                                            @endif
                                            <span class="block text-xs text-gray-600 dark:text-gray-300">انتخاب تصویر کارت ملی یا شناسنامه</span>
                                            <label class="inline-block px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-bold text-indigo-600 dark:text-indigo-400 shadow-sm cursor-pointer hover:bg-indigo-50 transition">
                                                انتخاب فایل
                                                <input type="file" name="national_card_image" accept="image/*" class="hidden" @change="previewImage($event, 'nationalCardPreview')">
                                            </label>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

            </div>

            {{-- ستون چپ: اطلاعات مالی، کارت شبیه‌ساز و تسویه (۵ ستون) --}}
            <div class="lg:col-span-5 space-y-6">

                {{-- کارت شبیه‌ساز بانکی زنده --}}
                <div class="{{ $cardClass }} p-6 space-y-5">
                    <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-gray-900 dark:text-white">کارت شبیه‌ساز تسویه حساب</h2>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">پیش‌نمایش کارت و حساب متصل جهت واریز درآمد</p>
                        </div>
                    </div>

                    {{-- کارت فیزیکی متالیک --}}
                    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-tr from-slate-950 via-indigo-950 to-slate-900 text-white p-5 shadow-xl border border-indigo-500/20">
                        {{-- چیپ طلایی متالیک و لوگو --}}
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-10 h-7 rounded-lg bg-gradient-to-tr from-amber-400 via-yellow-200 to-amber-500 shadow-md border border-amber-300 flex items-center justify-center">
                                <div class="w-7 h-4 border border-amber-700/40 rounded-sm"></div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-indigo-300 tracking-wide" x-text="formData.bankName || 'شبکه بانکی شتاب'"></span>
                                <div class="w-6 h-6 rounded-full bg-white/10 flex items-center justify-center text-[10px] font-black text-amber-300">IR</div>
                            </div>
                        </div>

                        {{-- نمایش زنده شماره شبا با فونت IRANYekanX --}}
                        <div class="my-4">
                            <span class="block text-[10px] text-indigo-300/80 mb-1">شماره شبا جهت واریز:</span>
                            <div class="text-sm sm:text-base font-bold font-sans tracking-widest text-left dir-ltr text-amber-200 truncate">
                                IR <span x-text="formatShabaDisplay(formData.shabaNumber)"></span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs border-t border-white/10 pt-3">
                            <div>
                                <span class="block text-[9px] text-gray-400">صاحب حساب:</span>
                                <span class="font-bold font-sans text-gray-100 truncate block max-w-[150px]" x-text="formData.accountOwnerName || 'نام صاحب حساب'"></span>
                            </div>
                            <div class="text-left">
                                <span class="block text-[9px] text-gray-400">وضعیت شبا:</span>
                                <span class="text-[10px] font-bold" :class="formData.shabaNumber && formData.shabaNumber.length === 24 ? 'text-emerald-400' : 'text-amber-400'" x-text="formData.shabaNumber && formData.shabaNumber.length === 24 ? '۲۴ رقم کامل' : 'در حال تکمیل'"></span>
                            </div>
                        </div>
                    </div>

                    {{-- فیلدهای ورودی اطلاعات بانکی --}}
                    <div class="space-y-4 pt-2">
                        <div>
                            <label class="{{ $labelClass }}">
                                شماره شبا (۲۴ رقم بدون IR) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" name="shaba_number" x-model="formData.shabaNumber" @input="detectBank()" maxlength="24" class="{{ $inputClass }} text-left dir-ltr pl-14 font-sans tracking-wider" placeholder="000000000000000000000000">
                                <span class="absolute inset-y-0 left-3 flex items-center text-xs font-bold text-indigo-500 dark:text-indigo-400 font-sans">IR</span>
                            </div>
                            <span class="text-[11px] text-gray-400 mt-1 block">شماره شبا باید به نام شخص میزبان باشد.</span>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">نام بانک</label>
                            <input type="text" name="bank_name" x-model="formData.bankName" class="{{ $inputClass }}" placeholder="مثلا: بانک ملت، ملی، سامان...">
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">نام و نام خانوادگی صاحب حساب</label>
                            <input type="text" name="account_owner_name" x-model="formData.accountOwnerName" class="{{ $inputClass }}" placeholder="دقیقاً مطابق شناسنامه">
                        </div>
                    </div>
                </div>

                {{-- کارت راهنمای تسویه مالی و نرخ کارمزد --}}
                <div class="{{ $cardClass }} p-6 space-y-4 bg-gradient-to-br from-indigo-50/50 to-white dark:from-gray-800 dark:to-gray-800/80 border-indigo-100 dark:border-gray-700">
                    <div class="flex items-center gap-2 text-indigo-700 dark:text-indigo-300 font-bold text-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        قوانین و رویه تسویه حساب درآمد:
                    </div>

                    <ul class="text-xs text-gray-600 dark:text-gray-300 space-y-2 leading-relaxed font-sans">
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mt-1.5 flex-shrink-0"></span>
                            <span><strong>زمان تسویه:</strong> مبالغ رزروها پس از تحویل اقامتگاه به مسافر و ورود بدون مغایرت در اولین سیکل پایا واریز می‌گردد.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mt-1.5 flex-shrink-0"></span>
                            <span><strong>کارمزد پلتفرم:</strong> سهم پلتفرم از هر رزرو <strong class="text-indigo-600 dark:text-indigo-400">{{ $host->effective_commission_rate }}٪</strong> بوده و مابقی مستقیماً به شبا واریز می‌شود.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mt-1.5 flex-shrink-0"></span>
                            <span><strong>تطابق حساب:</strong> نام صاحب حساب باید با اطلاعات هویتی و کد ملی همخوانی کامل داشته باشد.</span>
                        </li>
                    </ul>
                </div>

            </div>

        </div>

        {{-- نوار دکمه‌های پایانی --}}
        <div class="flex items-center justify-between p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 shadow-sm">
            <a href="{{ route('user.properties.hosts.dashboard') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                انصراف و بازگشت
            </a>

            <button type="submit" :disabled="isSubmitting" class="px-8 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-500/25 transition transform active:scale-95 flex items-center gap-2">
                <template x-if="!isSubmitting">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        ذخیره تغییرات پروفایل و اطلاعات بانکی
                    </span>
                </template>
                <template x-if="isSubmitting">
                    <span class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        در حال ذخیره‌سازی...
                    </span>
                </template>
            </button>
        </div>

    </form>
</div>

<script>
    function hostProfileManager() {
        return {
            isSubmitting: false,
            avatarPreview: null,
            nationalCardPreview: null,
            formData: {
                displayName: '{{ old('display_name', $host->display_name) }}',
                phone: '{{ old('phone', $host->phone) }}',
                shabaNumber: '{{ old('shaba_number', $host->shaba_number) }}',
                bankName: '{{ old('bank_name', $host->bank_name) }}',
                accountOwnerName: '{{ old('account_owner_name', $host->account_owner_name) }}',
            },

            formatShabaDisplay(shaba) {
                if (!shaba) return '—';
                return shaba.replace(/(\d{4})/g, '$1 ').trim();
            },

            detectBank() {
                // پاکسازی کاراکترهای غیر عددی
                this.formData.shabaNumber = this.formData.shabaNumber.replace(/\D/g, '');

                // تشخیص خودکار بانک از پیش‌شماره شبا
                const code = this.formData.shabaNumber.substring(2, 5);
                const bankMap = {
                    '012': 'بانک ملت',
                    '017': 'بانک ملی ایران',
                    '018': 'بانک تجارت',
                    '019': 'بانک صادرات ایران',
                    '013': 'بانک رفاه کارگران',
                    '056': 'بانک سامان',
                    '054': 'بانک پارسیان',
                    '055': 'بانک اقتصاد نوین',
                    '057': 'بانک پاسارگاد',
                    '061': 'بانک شهر',
                    '058': 'بانک سرمایه',
                    '059': 'بانک سینا',
                    '062': 'بانک آینده',
                    '051': 'بانک موسسه اعتباری توسعه',
                    '053': 'بانک کارآفرین',
                    '020': 'بانک توسعه صادرات',
                    '021': 'پست بانک ایران',
                    '022': 'بانک توسعه تعاون',
                    '014': 'بانک مسکن',
                    '016': 'بانک کشاورزی',
                    '011': 'بانک صنعت و معدن',
                    '015': 'بانک سپه',
                };

                if (bankMap[code] && !this.formData.bankName) {
                    this.formData.bankName = bankMap[code];
                }
            },

            previewImage(event, targetProp) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this[targetProp] = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            }
        }
    }
</script>
@endsection
