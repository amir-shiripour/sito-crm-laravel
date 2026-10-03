@extends('layouts.user')

@php
    $title = 'میزبان شوید و درآمد کسب کنید';
    $cardClass = "bg-white dark:bg-gray-800/90 backdrop-blur-md rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-sm overflow-hidden transition-all duration-300";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-2xl border-gray-200 bg-gray-50/70 px-4 py-3 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-500 font-sans";
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 space-y-8" x-data="hostRegistrationWizard()">

    {{-- بنر پرمیوم Hero --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900 via-indigo-800 to-purple-900 text-white p-8 md:p-12 shadow-xl shadow-indigo-950/20">
        {{-- افکت‌های نوری پس‌زمینه --}}
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <div class="lg:col-span-8 space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-xs font-bold text-indigo-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    پلتفرم میزبانی و اجاره روزانه اقامتگاه
                </div>
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-black tracking-tight leading-tight">
                    ویلا یا اقامتگاه داری؟ <br class="hidden sm:inline">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-yellow-200 to-indigo-200">میزبان شو و درآمد پایدار بساز!</span>
                </h1>
                <p class="text-sm md:text-base text-indigo-100/80 max-w-xl leading-relaxed">
                    با پیوستن به جمع میزبانان ما، اقامتگاه خود را به هزاران مسافر معرفی کنید، تقویم و نرخ هر شب را خودتان مدیریت نمایید و تسویه منظم بانکی دریافت کنید.
                </p>

                {{-- ویژگی‌های کلیدی Bento --}}
                <div class="grid grid-cols-3 gap-3 pt-2 max-w-lg">
                    <div class="p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 text-center">
                        <span class="block text-lg md:text-xl font-bold font-sans text-amber-300">تسویه سریع</span>
                        <span class="text-[11px] text-indigo-200">واریز مستقیم به شبا</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 text-center">
                        <span class="block text-lg md:text-xl font-bold font-sans text-emerald-300">کنترل ۱۰۰٪</span>
                        <span class="text-[11px] text-indigo-200">تقویم و نرخ دلخواه</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 text-center">
                        <span class="block text-lg md:text-xl font-bold font-sans text-indigo-200">پشتیبانی</span>
                        <span class="text-[11px] text-indigo-200">همراهی ۲۴ ساعته</span>
                    </div>
                </div>
            </div>

            {{-- تصویر / کارت خلاصه میزبانی --}}
            <div class="lg:col-span-4 hidden lg:flex justify-center">
                <div class="relative w-64 p-5 rounded-3xl bg-white/10 backdrop-blur-xl border border-white/20 shadow-2xl rotate-2 hover:rotate-0 transition-transform duration-300">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white mb-4 shadow-lg shadow-orange-500/30">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    </div>
                    <h3 class="text-sm font-bold text-white">اقامتگاه شما، در دستان شما</h3>
                    <p class="text-xs text-indigo-200/80 mt-1">با چند گام ساده اطلاعات خود را وارد کنید و بلافاصله فعالیت خود را آغاز نمایید.</p>
                    <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between text-xs text-indigo-200">
                        <span>زمان تخمینی ثبت‌نام:</span>
                        <span class="font-bold font-sans text-amber-300">۲ دقیقه</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- نوار وضعیت چندمرحله‌ای (Interactive Stepper) --}}
    <div class="{{ $cardClass }} p-4">
        <div class="grid grid-cols-4 gap-2 text-center select-none">
            {{-- گام ۱ --}}
            <button type="button" @click="goToStep(1)" class="group flex flex-col items-center gap-2 p-2 rounded-2xl transition" :class="step === 1 ? 'bg-indigo-50/70 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400' : (step > 1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400')">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm transition-all"
                     :class="step === 1 ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : (step > 1 ? 'bg-emerald-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500')">
                    <template x-if="step > 1">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                    </template>
                    <template x-if="step <= 1">
                        <span class="font-sans">۱</span>
                    </template>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-bold font-sans">مشخصات میزبان</span>
                    <span class="text-[10px] hidden sm:inline text-gray-400">نام و معرفی مجموعه</span>
                </div>
            </button>

            {{-- گام ۲ --}}
            <button type="button" @click="goToStep(2)" class="group flex flex-col items-center gap-2 p-2 rounded-2xl transition" :class="step === 2 ? 'bg-indigo-50/70 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400' : (step > 2 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400')">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm transition-all"
                     :class="step === 2 ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : (step > 2 ? 'bg-emerald-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500')">
                    <template x-if="step > 2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                    </template>
                    <template x-if="step <= 2">
                        <span class="font-sans">۲</span>
                    </template>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-bold font-sans">اطلاعات مالی</span>
                    <span class="text-[10px] hidden sm:inline text-gray-400">شماره شبا و تسویه</span>
                </div>
            </button>

            {{-- گام ۳ --}}
            <button type="button" @click="goToStep(3)" class="group flex flex-col items-center gap-2 p-2 rounded-2xl transition" :class="step === 3 ? 'bg-indigo-50/70 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400' : (step > 3 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400')">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm transition-all"
                     :class="step === 3 ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : (step > 3 ? 'bg-emerald-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500')">
                    <template x-if="step > 3">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                    </template>
                    <template x-if="step <= 3">
                        <span class="font-sans">۳</span>
                    </template>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-bold font-sans">احراز هویت</span>
                    <span class="text-[10px] hidden sm:inline text-gray-400">کد ملی و مدارک</span>
                </div>
            </button>

            {{-- گام ۴ --}}
            <button type="button" @click="goToStep(4)" class="group flex flex-col items-center gap-2 p-2 rounded-2xl transition" :class="step === 4 ? 'bg-indigo-50/70 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400' : 'text-gray-400'">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm transition-all"
                     :class="step === 4 ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'bg-gray-100 dark:bg-gray-700 text-gray-500'">
                    <span class="font-sans">۴</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-bold font-sans">بازبینی و ثبت</span>
                    <span class="text-[10px] hidden sm:inline text-gray-400">تأیید نهایی درخواست</span>
                </div>
            </button>
        </div>
    </div>

    {{-- فرم اصلی با چند مرحله --}}
    <form id="hostRegisterForm" action="{{ route('user.properties.hosts.store') }}" method="POST" enctype="multipart/form-data" @submit="isSubmitting = true">
        @csrf

        {{-- مرحله ۱: مشخصات فردی و برند میزبان --}}
        <div x-show="step === 1" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="{{ $cardClass }} p-6 sm:p-10 space-y-8">
            <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">گام اول: هویت و معرفی میزبان</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">این اطلاعات برای مهمانان در صفحه آگهی‌های شما نمایش داده می‌شود.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
                {{-- بخش آپلود آواتار تعاملی --}}
                <div class="md:col-span-4 flex flex-col items-center text-center p-6 rounded-3xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800">
                    <div class="relative w-28 h-28 rounded-3xl overflow-hidden bg-gradient-to-tr from-indigo-100 to-purple-100 dark:from-indigo-950 dark:to-gray-800 border-2 border-dashed border-indigo-300 dark:border-indigo-700/60 flex items-center justify-center shadow-inner group">
                        <template x-if="avatarPreview">
                            <img :src="avatarPreview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!avatarPreview">
                            <div class="flex flex-col items-center text-indigo-500 dark:text-indigo-400">
                                <svg class="w-8 h-8 opacity-70 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <span class="text-[10px] font-bold">لوگو یا تصویر</span>
                            </div>
                        </template>
                        <label class="absolute inset-0 bg-black/40 text-white flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer text-xs font-bold">
                            <svg class="w-5 h-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /></svg>
                            تغییر عکس
                            <input type="file" name="avatar" accept="image/*" class="hidden" @change="previewImage($event, 'avatarPreview')">
                        </label>
                    </div>

                    <h4 class="text-xs font-bold text-gray-800 dark:text-gray-200 mt-4">تصویر پروفایل / برند</h4>
                    <p class="text-[11px] text-gray-400 mt-1 max-w-[200px]">تصویر باکیفیت از خودتان یا نشان تجاری مجموعه اقامتی (اختیاری)</p>
                </div>

                {{-- فیلدهای مشخصات --}}
                <div class="md:col-span-8 space-y-5">
                    <div>
                        <label class="{{ $labelClass }}">
                            نام نمایشی میزبان یا مجموعه اقامتی <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="display_name" x-model="formData.displayName" required class="{{ $inputClass }}" placeholder="مثلا: ویلاهای لوکس دریاکنار یا اقامتگاه بوم‌گردی نارتیش">
                        <span class="text-[11px] text-gray-400 mt-1 block">این نام در بالای اقامتگاه‌های شما برای مسافران نشان داده خواهد شد.</span>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">
                            شماره تماس مستقیم میزبان <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="phone" x-model="formData.phone" required class="{{ $inputClass }} text-left dir-ltr pl-10 font-sans" placeholder="09123456789">
                            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">درباره میزبان و استانداردهای اقامتی</label>
                        <textarea name="about" x-model="formData.about" rows="3" class="{{ $inputClass }} resize-none" placeholder="چند جمله درباره سابقه میزبانی، امکانات بهداشتی و حسن میزبانی خود بنویسید..."></textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-gray-100 dark:border-gray-700/60">
                <button type="button" @click="nextStep(2)" class="px-7 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-500/25 transition transform active:scale-95 flex items-center gap-2">
                    مرحله بعد: اطلاعات بانکی
                    <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
            </div>
        </div>

        {{-- مرحله ۲: اطلاعات مالی و کارت بانکی هوشمند --}}
        <div x-show="step === 2" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="{{ $cardClass }} p-6 sm:p-10 space-y-8">
            <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">گام دوم: اطلاعات بانکی و واریز درآمد</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">درآمدهای حاصل از رزرو اقامتگاه مستقیماً به این حساب واریز خواهد شد.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                {{-- شبیه‌ساز کارت بانکی زنده --}}
                <div class="lg:col-span-5 flex justify-center">
                    <div class="w-full max-w-sm h-52 rounded-3xl p-6 bg-gradient-to-tr from-slate-900 via-indigo-950 to-indigo-900 text-white shadow-2xl shadow-indigo-950/40 relative overflow-hidden flex flex-col justify-between border border-white/10">
                        {{-- ترنج و بافت شیشه‌ای --}}
                        <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-indigo-500/20 rounded-full blur-2xl"></div>

                        <div class="flex items-center justify-between relative z-10">
                            <span class="text-xs font-bold text-indigo-300 font-sans tracking-wide" x-text="formData.bankName || 'بانک تسویه'"></span>
                            <div class="flex items-center gap-1.5">
                                <span class="w-7 h-5 rounded bg-amber-400/80 inline-block"></span>
                                <span class="text-[10px] text-gray-300 font-sans">شتاب</span>
                            </div>
                        </div>

                        <div class="relative z-10 py-2">
                            <span class="block text-[10px] text-gray-400 mb-1">شماره شبا حساب:</span>
                            <div class="text-base sm:text-lg font-bold font-sans tracking-wider text-left dir-ltr text-amber-200 truncate">
                                IR <span x-text="formatShabaDisplay(formData.shabaNumber)"></span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs relative z-10 border-t border-white/10 pt-3">
                            <div>
                                <span class="block text-[9px] text-gray-400">صاحب حساب:</span>
                                <span class="font-bold font-sans text-gray-100 truncate block max-w-[150px]" x-text="formData.accountOwnerName || 'نام صاحب حساب'"></span>
                            </div>
                            <div class="text-left">
                                <span class="block text-[9px] text-gray-400">وضعیت شبا:</span>
                                <span class="text-[10px] text-emerald-400 font-bold" x-text="formData.shabaNumber.length === 24 ? 'معتبر' : 'در انتظار تکمیل'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- فیلدهای بانکی --}}
                <div class="lg:col-span-7 space-y-5">
                    <div>
                        <label class="{{ $labelClass }}">
                            شماره شبا بانکی (۲۴ رقم بدون IR) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="shaba_number" x-model="formData.shabaNumber" @input="detectBank()" maxlength="24" class="{{ $inputClass }} text-left dir-ltr pl-14 font-sans tracking-wider" placeholder="000000000000000000000000">
                            <span class="absolute inset-y-0 left-3 flex items-center text-xs font-bold text-indigo-500 dark:text-indigo-400 font-sans">IR</span>
                        </div>
                        <span class="text-[11px] text-gray-400 mt-1 block">شماره شبا باید به نام شخص میزبان یا صاحب معرفی‌شده باشد.</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700/60">
                <button type="button" @click="step = 1" class="px-5 py-2.5 rounded-2xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-50 transition">
                    گام قبلی
                </button>
                <button type="button" @click="nextStep(3)" class="px-7 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-500/25 transition transform active:scale-95 flex items-center gap-2">
                    مرحله بعد: احراز هویت
                    <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
            </div>
        </div>

        {{-- مرحله ۳: احراز هویت و مدارک (KYC) --}}
        <div x-show="step === 3" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="{{ $cardClass }} p-6 sm:p-10 space-y-8">
            <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-4">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">گام سوم: احراز هویت و امنیت</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">جهت حفظ امنیت و اطمینان مسافران، مشخصات هویتی خود را ثبت نمایید.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                <div class="space-y-5">
                    <div>
                        <label class="{{ $labelClass }}">کد ملی هوشمند</label>
                        <input type="text" name="national_code" x-model="formData.nationalCode" maxlength="10" class="{{ $inputClass }} text-left dir-ltr font-sans" placeholder="10 رقمی">
                        <span class="text-[11px] text-gray-400 mt-1 block">کد ملی جهت تطبیق حساب بانکی و اعتبار میزبانی است.</span>
                    </div>

                    {{-- باکس قوانین و تعهدنامه --}}
                    <div class="p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200/70 dark:border-amber-900/40 text-xs text-amber-900 dark:text-amber-200 space-y-2">
                        <span class="font-bold block flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            تعهدنامه میزبانی:
                        </span>
                        <p class="text-[11px] leading-relaxed text-amber-800/80 dark:text-amber-300/80">
                            میزبان متعهد می‌گردد اقامتگاه را دقیقاً با امکانات و شرایط ثبت‌شده به مهمان تحویل داده و از پذیرش مهمانان خارج از ضوابط عرفی و قانونی خودداری نماید.
                        </p>
                        <label class="flex items-center gap-2 pt-2 cursor-pointer">
                            <input type="checkbox" x-model="formData.acceptTerms" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600">
                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200">قوانین و شرایط میزبانی پلتفرم را می‌پذیرم</span>
                        </label>
                    </div>
                </div>

                {{-- آپلود کارت ملی هوشمند --}}
                <div>
                    <label class="{{ $labelClass }}">تصویر کارت ملی یا شناسنامه</label>
                    <div class="relative border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-3xl p-6 text-center hover:border-indigo-500 transition-colors bg-gray-50/50 dark:bg-gray-900/30">
                        <template x-if="nationalCardPreview">
                            <div class="space-y-3">
                                <img :src="nationalCardPreview" class="max-h-40 mx-auto rounded-2xl object-contain border border-gray-200 dark:border-gray-700 shadow-sm">
                                <button type="button" @click="nationalCardPreview = null" class="text-xs text-red-500 hover:underline font-bold">حذف و انتخاب مجدد</button>
                            </div>
                        </template>
                        <template x-if="!nationalCardPreview">
                            <div class="space-y-2 py-4">
                                <div class="w-12 h-12 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                                </div>
                                <span class="block text-xs font-bold text-gray-700 dark:text-gray-300">تصویر کارت ملی را بکشید یا انتخاب کنید</span>
                                <span class="block text-[11px] text-gray-400">فرمت‌های مجاز: JPG, PNG تا حداکثر ۵ مگابایت</span>
                                <label class="inline-block mt-2 px-4 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-bold text-indigo-600 dark:text-indigo-400 shadow-sm cursor-pointer hover:bg-indigo-50 transition">
                                    انتخاب فایل
                                    <input type="file" name="national_card_image" accept="image/*" class="hidden" @change="previewImage($event, 'nationalCardPreview')">
                                </label>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700/60">
                <button type="button" @click="step = 2" class="px-5 py-2.5 rounded-2xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-50 transition">
                    گام قبلی
                </button>
                <button type="button" @click="nextStep(4)" class="px-7 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-500/25 transition transform active:scale-95 flex items-center gap-2">
                    مرحله بعد: بازبینی نهایی
                    <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
            </div>
        </div>

        {{-- مرحله ۴: بازبینی نهایی و تأیید ثبت‌نام --}}
        <div x-show="step === 4" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="{{ $cardClass }} p-6 sm:p-10 space-y-8">
            <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-4">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">گام نهایی: خلاصه و تأیید اطلاعات میزبانی</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">لطفاً اطلاعات زیر را بازبینی کنید و برای شروع فعالیت دکمه تایید را بزنید.</p>
                </div>
            </div>

            {{-- کارت خلاصه اطلاعات Bento Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800">
                    <span class="text-xs text-gray-400 block mb-1">نام نمایشی میزبان:</span>
                    <span class="text-sm font-bold text-gray-900 dark:text-white font-sans" x-text="formData.displayName || '—'"></span>
                </div>

                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800">
                    <span class="text-xs text-gray-400 block mb-1">شماره تماس هماهنگی:</span>
                    <span class="text-sm font-bold text-gray-900 dark:text-white font-sans dir-ltr text-right" x-text="formData.phone || '—'"></span>
                </div>

                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800">
                    <span class="text-xs text-gray-400 block mb-1">بانک مقصد تسویه:</span>
                    <span class="text-sm font-bold text-gray-900 dark:text-white font-sans" x-text="formData.bankName || 'تعیین نشده'"></span>
                </div>

                <div class="sm:col-span-2 p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800">
                    <span class="text-xs text-gray-400 block mb-1">شماره شبا:</span>
                    <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400 font-sans dir-ltr text-right" x-text="'IR ' + formData.shabaNumber"></span>
                </div>

                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800">
                    <span class="text-xs text-gray-400 block mb-1">صاحب حساب:</span>
                    <span class="text-sm font-bold text-gray-900 dark:text-white font-sans" x-text="formData.accountOwnerName || '—'"></span>
                </div>
            </div>

            <div class="flex items-center justify-between pt-6 border-t border-gray-100 dark:border-gray-700/60">
                <button type="button" @click="step = 3" class="px-5 py-2.5 rounded-2xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-50 transition">
                    ویرایش اطلاعات
                </button>

                <button type="submit" :disabled="isSubmitting" class="px-10 py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-black text-sm shadow-xl shadow-indigo-500/30 transition transform active:scale-95 flex items-center gap-2">
                    <template x-if="!isSubmitting">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            تأیید نهایی و فعال‌سازی حساب میزبانی
                        </span>
                    </template>
                    <template x-if="isSubmitting">
                        <span class="flex items-center gap-2">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            در حال ثبت اطلاعات...
                        </span>
                    </template>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function hostRegistrationWizard() {
        return {
            step: 1,
            isSubmitting: false,
            avatarPreview: null,
            nationalCardPreview: null,
            formData: {
                displayName: '{{ old('display_name', $user->name) }}',
                phone: '{{ old('phone', $user->mobile ?? $user->phone ?? '') }}',
                about: '{{ old('about') }}',
                shabaNumber: '{{ old('shaba_number', '') }}',
                bankName: '{{ old('bank_name', '') }}',
                accountOwnerName: '{{ old('account_owner_name', $user->name) }}',
                nationalCode: '{{ old('national_code', '') }}',
                acceptTerms: true,
            },

            goToStep(target) {
                if (target <= this.step) {
                    this.step = target;
                } else {
                    this.nextStep(target);
                }
            },

            nextStep(target) {
                if (this.step === 1) {
                    if (!this.formData.displayName.trim()) {
                        alert('لطفاً نام نمایشی میزبان را وارد کنید.');
                        return;
                    }
                    if (!this.formData.phone.trim()) {
                        alert('لطفاً شماره تماس را وارد کنید.');
                        return;
                    }
                }

                if (this.step === 2 && target > 2) {
                    if (this.formData.shabaNumber && this.formData.shabaNumber.length < 24) {
                        alert('شماره شبا باید دقیقاً ۲۴ رقم باشد.');
                        return;
                    }
                }

                if (this.step === 3 && target > 3) {
                    if (!this.formData.acceptTerms) {
                        alert('پذیرش قوانین و شرایط میزبانی الزامی است.');
                        return;
                    }
                }

                this.step = target;
                window.scrollTo({ top: 100, behavior: 'smooth' });
            },

            formatShabaDisplay(shaba) {
                if (!shaba) return '—';
                return shaba.replace(/(\d{4})/g, '$1 ').trim();
            },

            detectBank() {
                // پاکسازی فاصله‌ها
                this.formData.shabaNumber = this.formData.shabaNumber.replace(/\D/g, '');

                // تشخیص خودکار بانک از ۳ رقم اول بعد از کد شعبه در صورت تمایل
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
