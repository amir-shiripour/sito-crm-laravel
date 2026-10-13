@extends('layouts.user')

@php
    $title = 'ویرایش اطلاعات و مدارک میزبانی';
    $cardClass = "bg-white dark:bg-gray-800/90 backdrop-blur-md rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-sm overflow-hidden";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";
    $inputClass = "w-full rounded-2xl border-gray-200 bg-gray-50/70 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-colors dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-500 font-sans";
@endphp

@section('content')
<div class="space-y-6 pb-12 font-sans"
     x-data="hostProfileManager({
         isKycApproved: {{ $host->kyc_status === 'approved' ? 'true' : 'false' }},
         hasKycErrors: {{ $errors->hasAny(['custom', 'custom.*']) ? 'true' : 'false' }}
     })">

    <form action="{{ route('user.properties.hosts.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6" @submit="isSubmitting = true">
        @csrf
        @method('PUT')

        {{-- ۱. بنر پرمیوم سربرگ و اطلاعات میزبان (هماهنگ با صفحه پروفایل میزبانی) --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900 via-indigo-800 to-purple-900 text-white p-6 sm:p-8 shadow-xl shadow-indigo-950/20">
            {{-- افکت‌های نوری ملایم --}}
            <div class="absolute -top-24 -left-24 w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="flex items-start sm:items-center gap-4 sm:gap-5">
                    {{-- آواتار با قابلیت کلیک جهت تغییر سریع تصویر --}}
                    <div class="relative group cursor-pointer flex-shrink-0" @click="$refs.avatarInput.click()" title="کلیک جهت تغییر تصویر پروفایل">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-3xl bg-white/10 backdrop-blur-md border border-white/20 text-white flex items-center justify-center font-black text-2xl overflow-hidden shadow-2xl">
                            <template x-if="avatarPreview">
                                <img :src="avatarPreview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!avatarPreview">
                                @if($host->avatar)
                                    <img src="{{ asset('storage/' . $host->avatar) }}" alt="{{ $host->display_name }}" class="w-full h-full object-cover">
                                @else
                                    <span class="font-sans font-black" x-text="(formData.displayName || '{{ $host->display_name }}').charAt(0)">{{ mb_substr($host->display_name, 0, 1) }}</span>
                                @endif
                            </template>
                        </div>

                        <div class="absolute inset-0 rounded-3xl bg-black/45 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>

                        @if($host->status === 'active')
                            <span class="absolute -bottom-1 -left-1 w-6 h-6 rounded-full bg-emerald-500 border-2 border-indigo-900 flex items-center justify-center text-white" title="میزبان تأیید شده">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                            </span>
                        @endif
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h1 class="text-xl sm:text-2xl font-black text-white font-sans" x-text="formData.displayName || '{{ $host->display_name }}'">{{ $host->display_name }}</h1>

                            @if($host->status === 'active')
                                <span class="px-3 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-xs font-bold font-sans">
                                    میزبان فعال
                                </span>
                            @elseif($host->status === 'pending')
                                <span class="px-3 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-400/30 text-xs font-bold font-sans">
                                    در انتظار تأیید مدیریت
                                </span>
                            @else
                                <span class="px-3 py-0.5 rounded-full bg-red-500/20 text-red-300 border border-red-400/30 text-xs font-bold font-sans">
                                    حساب غیرفعال
                                </span>
                            @endif

                            @if($host->kyc_status === 'approved')
                                <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/30 text-indigo-200 border border-indigo-400/20 text-xs font-bold font-sans flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    هویت احراز شده
                                </span>
                            @elseif($host->kyc_status === 'pending')
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-500/30 text-amber-200 border border-amber-400/20 text-xs font-bold font-sans">
                                    مدارک در حال بررسی
                                </span>
                            @elseif($host->kyc_status === 'rejected')
                                <span class="px-2.5 py-0.5 rounded-full bg-red-500/30 text-red-200 border border-red-400/20 text-xs font-bold font-sans">
                                    مدارک نیازمند اصلاح
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-x-4 gap-y-1.5 text-xs text-indigo-200/90 font-sans flex-wrap">
                            <span>شماره تماس: <strong class="dir-ltr inline-block text-white" x-text="formData.phone || '{{ $host->phone }}'">{{ $host->phone }}</strong></span>
                            <span class="text-indigo-400/60">•</span>
                            <span>تاریخ عضویت: <strong class="text-white">{{ \Morilog\Jalali\Jalalian::fromCarbon($host->created_at)->format('Y/m/d') }}</strong></span>
                            <span class="text-indigo-400/60">•</span>
                            <span class="text-amber-300">نرخ کارمزد: <strong>{{ $host->effective_commission_rate }}٪</strong></span>
                        </div>
                    </div>
                </div>

                {{-- دکمه‌های عملیاتی هدر --}}
                <div class="flex items-center gap-3 flex-wrap">
                    <a href="{{ route('user.properties.hosts.dashboard') }}"
                       class="px-4 py-2.5 rounded-2xl bg-white/10 hover:bg-white/15 backdrop-blur-md border border-white/15 text-white text-xs font-bold transition-colors flex items-center gap-2 font-sans">
                        <svg class="w-4 h-4 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                        بازگشت به پروفایل میزبانی
                    </a>

                    <button type="submit"
                            :disabled="isSubmitting"
                            class="px-5 py-2.5 rounded-2xl bg-amber-400 hover:bg-amber-500 text-slate-950 font-black text-xs transition-colors flex items-center gap-2 shadow-lg shadow-amber-400/20 font-sans">
                        <svg x-show="!isSubmitting" class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        <svg x-show="isSubmitting" x-cloak class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="isSubmitting ? 'در حال ذخیره...' : 'ذخیره تغییرات'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- نمایش خطاهای اعتبارسنجی در صورت وجود --}}
        @if($errors->any())
            <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800/60 text-red-800 dark:text-red-300 text-sm font-sans space-y-1.5">
                <div class="font-bold flex items-center gap-2 text-xs sm:text-sm">
                    <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    لطفاً خطاهای زیر را بررسی و اصلاح فرمایید:
                </div>
                <ul class="list-disc list-inside mr-6 text-xs space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ۲. چیدمان دو ستونه متوازن (۷ ستون اطلاعات و مدارک / ۵ ستون شبا و قوانین تسویه) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- ستون راست: مشخصات عمومی و مدارک هویتی (۷ ستون) --}}
            <div class="lg:col-span-7 space-y-6">

                {{-- کارت ۱: مشخصات عمومی و تصویر پروفایل --}}
                <div class="{{ $cardClass }} p-5 sm:p-6 space-y-5">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </div>
                            <div>
                                <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">مشخصات عمومی و تماس</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">اطلاعاتی که مهمانان در صفحه اقامتگاه‌های شما مشاهده می‌کنند</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        {{-- نوار فشرده تصویر پروفایل --}}
                        <div class="flex items-center justify-between gap-4 p-3.5 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60">
                            <div class="flex items-center gap-3.5">
                                <div class="w-14 h-14 rounded-2xl overflow-hidden bg-indigo-50 dark:bg-gray-700 flex-shrink-0 border border-gray-200 dark:border-gray-600 flex items-center justify-center">
                                    <template x-if="avatarPreview">
                                        <img :src="avatarPreview" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!avatarPreview">
                                        @if($host->avatar)
                                            <img src="{{ asset('storage/' . $host->avatar) }}" alt="{{ $host->display_name }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-indigo-600 dark:text-indigo-300 font-bold text-lg font-sans">
                                                {{ mb_substr($host->display_name, 0, 1) }}
                                            </span>
                                        @endif
                                    </template>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-gray-900 dark:text-white">تصویر پروفایل میزبان</span>
                                    <span class="block text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">فرمت JPG یا PNG (حداکثر ۳ مگابایت)</span>
                                </div>
                            </div>

                            <label class="px-3.5 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-bold text-indigo-600 dark:text-indigo-400 cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-500/50 transition-colors flex-shrink-0">
                                <span x-text="avatarPreview ? 'تغییر تصویر' : 'انتخاب تصویر'"></span>
                                <input type="file" x-ref="avatarInput" name="avatar" accept="image/*" class="hidden" @change="previewImage($event, 'avatarPreview')">
                            </label>
                        </div>

                        {{-- نام نمایشی و شماره تماس --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $labelClass }}">
                                    نام نمایشی میزبان / مجموعه <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="display_name" x-model="formData.displayName" required class="{{ $inputClass }}" placeholder="مثلاً: اقامتگاه بوم‌گردی کاشانه">
                                @error('display_name')
                                    <span class="text-xs text-red-500 font-bold block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">
                                    شماره تماس هماهنگی <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" name="phone" x-model="formData.phone" required class="{{ $inputClass }} text-left dir-ltr font-sans" placeholder="09123456789">
                                @error('phone')
                                    <span class="text-xs text-red-500 font-bold block mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- درباره میزبان --}}
                        <div>
                            <label class="{{ $labelClass }}">درباره میزبان و اقامتگاه‌ها</label>
                            <textarea name="about" rows="2" class="{{ $inputClass }} resize-none" placeholder="توضیح مختصری درباره تجربه میزبانی، سوابق و خدمات ویژه اقامتگاه‌های خود بنویسید...">{{ old('about', $host->about) }}</textarea>
                            <span class="text-[11px] text-gray-400 mt-1 block">این معرفی کوتاه، اعتماد مهمانان را به رزرو اقامتگاه‌های شما افزایش می‌دهد.</span>
                        </div>
                    </div>
                </div>

                {{-- کارت ۲: احراز هویت و مدارک امنیتی (KYC) --}}
                <div class="{{ $cardClass }} p-5 sm:p-6 space-y-5">
                    <div class="flex items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-4 flex-wrap">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            </div>
                            <div>
                                <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">احراز هویت و مدارک امنیتی</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">ثبت مدارک هویتی جهت دریافت نشان اصالت و فعال‌سازی تسویه منظم</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($host->kyc_status === 'approved')
                                <span class="px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50 text-xs font-bold font-sans flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                    تأیید شده
                                </span>
                                @if(isset($customFields) && $customFields->count() > 0)
                                    <button type="button"
                                            @click="editingKyc = !editingKyc"
                                            class="px-3 py-1 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-bold transition-colors">
                                        <span x-text="editingKyc ? 'لغو ویرایش مدارک' : 'ویرایش مدارک'"></span>
                                    </button>
                                @endif
                            @elseif($host->kyc_status === 'pending')
                                <span class="px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50 text-xs font-bold font-sans">
                                    در صف بررسی
                                </span>
                            @elseif($host->kyc_status === 'rejected')
                                <span class="px-3 py-1 rounded-full bg-red-50 dark:bg-red-950/50 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800/50 text-xs font-bold font-sans">
                                    نیازمند اصلاح
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-bold font-sans">
                                    ارسال نشده
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- پیام‌های وضعیت احراز هویت --}}
                    @if($host->kyc_status === 'approved')
                        <div x-show="!editingKyc" class="p-4 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/20 border border-emerald-200/80 dark:border-emerald-900/40 text-xs space-y-3 font-sans">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 font-bold text-emerald-800 dark:text-emerald-300">
                                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span>هویت و مدارک شما توسط کارشناسان تأیید شده است</span>
                                </div>
                            </div>

                            @if(isset($customFields) && $customFields->count() > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2 border-t border-emerald-200/60 dark:border-emerald-800/40">
                                    @foreach($customFields as $cf)
                                        @php
                                            $cVal = $customValues->get($cf->field_name)->value ?? null;
                                        @endphp
                                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-white/80 dark:bg-gray-800/80 border border-emerald-100 dark:border-emerald-900/30">
                                            <span class="text-gray-600 dark:text-gray-300 font-bold">{{ $cf->label }}:</span>
                                            @if(!empty($cVal))
                                                @if($cf->field_type === 'file')
                                                    <a href="{{ asset('storage/' . $cVal) }}" target="_blank" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                                        مشاهده مدرک
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                                    </a>
                                                @else
                                                    <span class="font-bold text-gray-900 dark:text-white dir-ltr font-sans">{{ $cVal }}</span>
                                                @endif
                                            @else
                                                <span class="text-gray-400">ثبت نشده</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div x-show="editingKyc" x-cloak class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2.5 font-sans">
                            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            <p class="leading-relaxed">
                                <strong>توجه:</strong> در صورت تغییر هر یک از اطلاعات یا بارگذاری مدرک جدید در این بخش، وضعیت احراز هویت شما جهت بررسی مجدد کارشناس به حالت <strong>«در صف بررسی»</strong> تغییر خواهد کرد.
                            </p>
                        </div>
                    @elseif($host->kyc_status === 'rejected')
                        <div class="p-4 rounded-2xl bg-red-50/80 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 text-xs text-red-900 dark:text-red-200 space-y-1 font-sans">
                            <div class="font-bold flex items-center gap-2 text-red-800 dark:text-red-300">
                                <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                علت عدم تأیید مدارک قبلی:
                            </div>
                            <p class="text-red-700 dark:text-red-300 mr-6 leading-relaxed">
                                {{ $host->kyc_rejection_reason ?: 'مدارک ارسالی ناخوانا بوده یا با مشخصات هویتی مطابقت ندارد. لطفاً مدارک اصلاح‌شده را بارگذاری فرمایید.' }}
                            </p>
                        </div>
                    @elseif($host->kyc_status === 'pending')
                        <div class="p-3.5 rounded-2xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 text-xs text-amber-900 dark:text-amber-200 flex items-center gap-2.5 font-sans">
                            <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>مدارک شما ثبت شده و در صف بررسی کارشناسان قرار دارد. در صورت نیاز می‌توانید آن‌ها را ویرایش نمایید.</span>
                        </div>
                    @endif

                    {{-- فیلدهای داینامیک احراز هویت (CustomUserField) --}}
                    <div x-show="editingKyc" @if($host->kyc_status === 'approved') x-cloak @endif>
                        @if(isset($customFields) && $customFields->count() > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                                @foreach($customFields as $field)
                                    @php
                                        $type = strtolower($field->field_type ?? 'text');
                                        $existingVal = $customValues->get($field->field_name)->value ?? '';
                                        $meta = is_array($field->meta) ? $field->meta : (is_string($field->meta) ? json_decode($field->meta, true) : []);
                                        $opts = $meta['options'] ?? null;
                                        $isFullWidth = in_array($type, ['textarea', 'file']);
                                        $isLtr = in_array($type, ['number', 'email', 'url', 'tel']);
                                    @endphp

                                    @if($type === 'file')
                                        {{-- کارت فشرده و مدرن بارگذاری مدرک --}}
                                        <div class="sm:col-span-2">
                                            <label class="{{ $labelClass }}">
                                                {{ $field->label }}
                                                @if($field->is_required) <span class="text-red-500">*</span> @endif
                                            </label>
                                            <div class="p-3.5 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                                <div class="flex items-center gap-3.5 min-w-0">
                                                    {{-- پیش‌نمایش فایل جدید انتخاب شده --}}
                                                    <template x-if="previews['{{ $field->field_name }}']">
                                                        <div class="flex items-center gap-3 min-w-0">
                                                            <template x-if="previews['{{ $field->field_name }}'].isImage">
                                                                <img :src="previews['{{ $field->field_name }}'].data" class="w-14 h-14 rounded-xl object-cover border border-indigo-200 dark:border-indigo-700 flex-shrink-0">
                                                            </template>
                                                            <template x-if="!previews['{{ $field->field_name }}'].isImage">
                                                                <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0">
                                                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                                </div>
                                                            </template>
                                                            <div class="min-w-0">
                                                                <span class="block text-xs font-bold text-indigo-600 dark:text-indigo-400 truncate" x-text="previews['{{ $field->field_name }}'].name"></span>
                                                                <span class="inline-block mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">فایل جدید آماده ذخیره است</span>
                                                            </div>
                                                        </div>
                                                    </template>

                                                    {{-- وضعیت مدرک قبلی --}}
                                                    <template x-if="!previews['{{ $field->field_name }}']">
                                                        <div class="flex items-center gap-3 min-w-0">
                                                            @if(!empty($existingVal))
                                                                @php
                                                                    $ext = strtolower(pathinfo($existingVal, PATHINFO_EXTENSION));
                                                                    $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
                                                                @endphp
                                                                @if($isImg)
                                                                    <img src="{{ asset('storage/' . $existingVal) }}" class="w-14 h-14 rounded-xl object-cover border border-gray-200 dark:border-gray-700 flex-shrink-0">
                                                                @else
                                                                    <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                                                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                                    </div>
                                                                @endif
                                                                <div class="space-y-1">
                                                                    <div class="flex items-center gap-2">
                                                                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold">مدرک ثبت شده</span>
                                                                        <a href="{{ asset('storage/' . $existingVal) }}" target="_blank" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-bold">مشاهده فایل فعلی</a>
                                                                    </div>
                                                                    <span class="block text-[11px] text-gray-400">در صورت تمایل می‌توانید فایل جدیدی جایگزین کنید</span>
                                                                </div>
                                                            @else
                                                                <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-400 flex items-center justify-center flex-shrink-0">
                                                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                                </div>
                                                                <div>
                                                                    <span class="block text-xs font-bold text-gray-700 dark:text-gray-200">{{ $field->label }}</span>
                                                                    <span class="block text-[11px] text-gray-400 mt-0.5">تصویر یا سند معتبر (حداکثر ۱۰ مگابایت)</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </template>
                                                </div>

                                                <label class="px-3.5 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-bold text-indigo-600 dark:text-indigo-400 cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-500/50 transition-colors flex-shrink-0 text-center font-sans">
                                                    <span x-text="previews['{{ $field->field_name }}'] ? 'تغییر فایل' : '{{ !empty($existingVal) ? 'جایگزینی فایل' : 'انتخاب فایل' }}'"></span>
                                                    <input type="file" name="custom[{{ $field->field_name }}]" class="hidden" @change="previewFieldFile($event, '{{ $field->field_name }}')">
                                                </label>
                                            </div>
                                            @error('custom.'.$field->field_name)
                                                <span class="text-xs text-red-500 font-bold block mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>

                                    @elseif($type === 'textarea')
                                        <div class="sm:col-span-2">
                                            <label class="{{ $labelClass }}">
                                                {{ $field->label }}
                                                @if($field->is_required) <span class="text-red-500">*</span> @endif
                                            </label>
                                            <textarea name="custom[{{ $field->field_name }}]" rows="2" class="{{ $inputClass }} font-sans" placeholder="{{ $field->label }}">{{ old('custom.'.$field->field_name, $existingVal) }}</textarea>
                                            @error('custom.'.$field->field_name)
                                                <span class="text-xs text-red-500 font-bold block mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>

                                    @elseif($type === 'select')
                                        <div>
                                            <label class="{{ $labelClass }}">
                                                {{ $field->label }}
                                                @if($field->is_required) <span class="text-red-500">*</span> @endif
                                            </label>
                                            <select name="custom[{{ $field->field_name }}]" class="{{ $inputClass }} font-sans">
                                                <option value="">انتخاب کنید...</option>
                                                @if(is_array($opts))
                                                    @foreach($opts as $optVal => $optLabel)
                                                        <option value="{{ $optVal }}" {{ old('custom.'.$field->field_name, $existingVal) == $optVal ? 'selected' : '' }}>{{ $optLabel }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @error('custom.'.$field->field_name)
                                                <span class="text-xs text-red-500 font-bold block mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>

                                    @else
                                        <div>
                                            <label class="{{ $labelClass }}">
                                                {{ $field->label }}
                                                @if($field->is_required) <span class="text-red-500">*</span> @endif
                                            </label>
                                            <input type="{{ $type === 'number' ? 'number' : ($type === 'date' ? 'date' : ($type === 'email' ? 'email' : 'text')) }}"
                                                   name="custom[{{ $field->field_name }}]"
                                                   value="{{ old('custom.'.$field->field_name, $existingVal) }}"
                                                   class="{{ $inputClass }} {{ $isLtr ? 'text-left dir-ltr' : '' }} font-sans"
                                                   placeholder="{{ $field->label }}">
                                            @error('custom.'.$field->field_name)
                                                <span class="text-xs text-red-500 font-bold block mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="p-5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-200/80 dark:border-gray-800 text-center font-sans space-y-1.5">
                                <p class="text-xs font-bold text-gray-600 dark:text-gray-300">در حال حاضر مدرک یا فیلد سفارشی خاصی برای احراز هویت میزبان تعریف نشده است.</p>
                                <p class="text-[11px] text-gray-400">مدیران سیستم می‌توانند از منوی «فیلدهای سفارشی کاربر»، مدارک مورد نیاز را اضافه کنند.</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- ستون چپ: اطلاعات بانکی تسویه و قوانین مالی (۵ ستون) --}}
            <div class="lg:col-span-5 space-y-6">

                {{-- کارت ۳: اطلاعات بانکی و حساب تسویه (شبا) --}}
                <div class="{{ $cardClass }} p-5 sm:p-6 space-y-5">
                    <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-4">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                        </div>
                        <div>
                            <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white">اطلاعات بانکی و حساب تسویه</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">حساب متصل جهت واریز درآمد رزروها در سیکل پایا</p>
                        </div>
                    </div>

                    {{-- کارت شبیه‌ساز بانکی زنده --}}
                    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-tr from-slate-950 via-indigo-950 to-slate-900 text-white p-5 border border-indigo-500/20">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-7 rounded-lg bg-gradient-to-tr from-amber-400 via-yellow-200 to-amber-500 border border-amber-300 flex items-center justify-center">
                                <div class="w-7 h-4 border border-amber-700/40 rounded-sm"></div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-indigo-200 font-sans" x-text="formData.bankName || 'شبکه بانکی شتاب'"></span>
                                <span class="w-6 h-6 rounded-full bg-white/10 flex items-center justify-center text-[10px] font-black text-amber-300 font-sans">IR</span>
                            </div>
                        </div>

                        <div class="my-3.5">
                            <span class="block text-[10px] text-indigo-300/80 mb-1">شماره شبا جهت واریز:</span>
                            <div class="text-sm sm:text-base font-bold font-sans tracking-wider text-left dir-ltr text-amber-200 truncate">
                                IR <span x-text="formatShabaDisplay(formData.shabaNumber)"></span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs border-t border-white/10 pt-3">
                            <div>
                                <span class="block text-[10px] text-gray-400">صاحب حساب:</span>
                                <span class="font-bold font-sans text-gray-100 truncate block max-w-[180px]" x-text="formData.accountOwnerName || formData.displayName || 'نام صاحب حساب'"></span>
                            </div>
                            <div class="text-left">
                                <span class="block text-[10px] text-gray-400">وضعیت شبا:</span>
                                <span class="text-[11px] font-bold font-sans"
                                      :class="formData.shabaNumber && formData.shabaNumber.length === 24 ? 'text-emerald-400' : 'text-amber-400'"
                                      x-text="formData.shabaNumber && formData.shabaNumber.length === 24 ? '۲۴ رقم کامل' : ((formData.shabaNumber ? formData.shabaNumber.length : 0) + ' از ۲۴ رقم')"></span>
                            </div>
                        </div>
                    </div>

                    {{-- فیلدهای ورودی اطلاعات بانکی --}}
                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-bold text-gray-700 dark:text-gray-300">
                                    شماره شبا (۲۴ رقم بدون IR)
                                </label>
                                <span class="text-[11px] font-bold font-sans"
                                      :class="formData.shabaNumber && formData.shabaNumber.length === 24 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400'"
                                      x-text="(formData.shabaNumber ? formData.shabaNumber.length : 0) + ' / ۲۴'"></span>
                            </div>
                            <div class="relative">
                                <input type="text"
                                       name="shaba_number"
                                       x-model="formData.shabaNumber"
                                       @input="detectBank()"
                                       maxlength="24"
                                       class="{{ $inputClass }} text-left dir-ltr pl-12 font-sans tracking-wider"
                                       placeholder="000000000000000000000000">
                                <span class="absolute inset-y-0 left-3.5 flex items-center text-xs font-black text-indigo-600 dark:text-indigo-400 font-sans pointer-events-none">IR</span>
                            </div>
                            @error('shaba_number')
                                <span class="text-xs text-red-500 font-bold block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $labelClass }}">نام بانک</label>
                                <input type="text" name="bank_name" x-model="formData.bankName" class="{{ $inputClass }}" placeholder="مثلاً: بانک ملت">
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">نام صاحب حساب</label>
                                <input type="text" name="account_owner_name" x-model="formData.accountOwnerName" class="{{ $inputClass }}" placeholder="مطابق شناسنامه">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- کارت ۴: قوانین و رویه تسویه حساب درآمد (پویا از تنظیمات املاک) --}}
                <div class="{{ $cardClass }} p-5 sm:p-6 space-y-4 bg-gradient-to-br from-indigo-50/40 to-white dark:from-gray-800 dark:to-gray-800/80 border-indigo-100 dark:border-gray-700">
                    <div class="flex items-center gap-2.5 text-indigo-700 dark:text-indigo-300 font-black text-xs sm:text-sm">
                        <span class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </span>
                        <span>{{ $settlementRulesTitle ?? 'قوانین و رویه تسویه حساب درآمد:' }}</span>
                    </div>

                    @php
                        $activeRules = $settlementRules ?? [
                            [
                                'title' => 'زمان تسویه',
                                'text' => 'مبالغ رزروها پس از تحویل اقامتگاه به مسافر و ورود بدون مغایرت در اولین سیکل پایا واریز می‌گردد.',
                            ],
                            [
                                'title' => 'کارمزد پلتفرم',
                                'text' => 'سهم پلتفرم از هر رزرو {commission}٪ بوده و مابقی مستقیماً به شبا واریز می‌شود.',
                            ],
                            [
                                'title' => 'تطابق حساب',
                                'text' => 'نام صاحب حساب باید با اطلاعات هویتی و کد ملی همخوانی کامل داشته باشد.',
                            ],
                        ];
                    @endphp

                    <ul class="text-xs text-gray-600 dark:text-gray-300 space-y-2.5 leading-relaxed font-sans">
                        @foreach($activeRules as $rule)
                            @php
                                $rTitle = trim($rule['title'] ?? '');
                                $rText = trim($rule['text'] ?? '');
                                $formattedText = str_replace(
                                    ['{commission}', ':commission'],
                                    '<strong class="text-indigo-600 dark:text-indigo-400 font-sans">' . $host->effective_commission_rate . '٪</strong>',
                                    e($rText)
                                );
                            @endphp
                            @if(!empty($rText) || !empty($rTitle))
                                <li class="flex items-start gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mt-1.5 flex-shrink-0"></span>
                                    <span>
                                        @if(!empty($rTitle))
                                            <strong class="text-gray-900 dark:text-white">{{ $rTitle }}:</strong>
                                        @endif
                                        {!! $formattedText !!}
                                    </span>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>

            </div>

        </div>

        {{-- نوار دکمه‌های پایانی --}}
        <div class="flex items-center justify-between p-4 sm:px-6 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 shadow-sm">
            <a href="{{ route('user.properties.hosts.dashboard') }}"
               class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                انصراف و بازگشت
            </a>

            <button type="submit"
                    :disabled="isSubmitting"
                    class="px-7 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-colors flex items-center gap-2">
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
    function hostProfileManager(config) {
        return {
            isSubmitting: false,
            editingKyc: config.hasKycErrors ? true : !config.isKycApproved,
            avatarPreview: null,
            previews: {},
            formData: {
                displayName: @js(old('display_name', $host->display_name)),
                phone: @js(old('phone', $host->phone)),
                shabaNumber: @js(preg_replace('/^IR/i', '', (string) old('shaba_number', $host->shaba_number))),
                bankName: @js(old('bank_name', $host->bank_name)),
                accountOwnerName: @js(old('account_owner_name', $host->account_owner_name)),
            },

            formatShabaDisplay(shaba) {
                if (!shaba) return '—';
                return shaba.replace(/(\d{4})/g, '$1 ').trim();
            },

            detectBank() {
                this.formData.shabaNumber = (this.formData.shabaNumber || '').replace(/\D/g, '');

                if (this.formData.shabaNumber.length >= 5) {
                    const code = this.formData.shabaNumber.substring(2, 5);
                    const bankMap = {
                        '010': 'بانک مرکزی',
                        '011': 'بانک صنعت و معدن',
                        '012': 'بانک ملت',
                        '013': 'بانک رفاه کارگران',
                        '014': 'بانک مسکن',
                        '015': 'بانک سپه',
                        '016': 'بانک کشاورزی',
                        '017': 'بانک ملی ایران',
                        '018': 'بانک تجارت',
                        '019': 'بانک صادرات ایران',
                        '020': 'بانک توسعه صادرات',
                        '021': 'پست بانک ایران',
                        '022': 'بانک توسعه تعاون',
                        '051': 'موسسه اعتباری توسعه',
                        '053': 'بانک کارآفرین',
                        '054': 'بانک پارسیان',
                        '055': 'بانک اقتصاد نوین',
                        '056': 'بانک سامان',
                        '057': 'بانک پاسارگاد',
                        '058': 'بانک سرمایه',
                        '059': 'بانک سینا',
                        '060': 'بانک قرض‌الحسنه مهر ایران',
                        '061': 'بانک شهر',
                        '062': 'بانک آینده',
                        '064': 'بانک گردشگری',
                        '066': 'بانک دی',
                        '069': 'بانک ایران زمین',
                        '070': 'بانک قرض‌الحسنه رسالت',
                        '078': 'بانک خاورمیانه',
                    };

                    if (bankMap[code]) {
                        this.formData.bankName = bankMap[code];
                    }
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
            },

            previewFieldFile(event, fieldName) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.previews[fieldName] = {
                            data: e.target.result,
                            name: file.name,
                            isImage: file.type.startsWith('image/')
                        };
                    };
                    reader.readAsDataURL(file);
                }
            }
        }
    }
</script>
@endsection
