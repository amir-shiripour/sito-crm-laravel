@extends('layouts.user')

@php
    $title = 'ثبت‌نام و درخواست میزبانی اقامتگاه';
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-200";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600 font-sans";
@endphp

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">

    {{-- هدر صفحه --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300 mb-3 shadow-inner">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">میزبان شوید و درآمد کسب کنید</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 max-w-md mx-auto leading-relaxed">
            با تکمیل اطلاعات زیر، اقامتگاه‌ها و ویلاهای خود را در سامانه به اشتراک بگذارید و به صورت مستقیم تقویم و رزروهای خود را مدیریت کنید.
        </p>
    </div>

    <form action="{{ route('user.properties.hosts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- مشخصات فردی و معرفی --}}
        <div class="{{ $cardClass }} p-6 space-y-5">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                مشخصات میزبان
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="{{ $labelClass }}">نام نمایشی میزبان یا اقامتگاه <span class="text-red-500">*</span></label>
                    <input type="text" name="display_name" value="{{ old('display_name', $user->name) }}" required class="{{ $inputClass }}" placeholder="مثلا: اقامتگاه بوم‌گردی کاج یا ویلاهای مهندس رضایی">
                </div>

                <div>
                    <label class="{{ $labelClass }}">شماره تماس همراه <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $user->mobile ?? $user->phone ?? '') }}" required class="{{ $inputClass }} text-left dir-ltr" placeholder="09123456789">
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">درباره میزبان / معرفی مجموعه</label>
                    <textarea name="about" rows="3" class="{{ $inputClass }} resize-none" placeholder="توضیح مختصری درباره تجربه میزبانی، سوابق و استانداردهای اقامتگاه شما...">{{ old('about') }}</textarea>
                </div>

                <div>
                    <label class="{{ $labelClass }}">تصویر پروفایل میزبان (اختیاری)</label>
                    <input type="file" name="avatar" accept="image/*" class="{{ $inputClass }}">
                </div>
            </div>
        </div>

        {{-- اطلاعات مالی و بانکی جهت تسویه --}}
        <div class="{{ $cardClass }} p-6 space-y-5">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                اطلاعات بانکی جهت واریز و تسویه
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">شماره شبا (بدون IR)</label>
                    <div class="relative">
                        <input type="text" name="shaba_number" value="{{ old('shaba_number') }}" class="{{ $inputClass }} text-left dir-ltr pl-12" placeholder="000000000000000000000000">
                        <span class="absolute inset-y-0 left-3 flex items-center text-xs font-bold text-gray-400">IR</span>
                    </div>
                </div>

                <div>
                    <label class="{{ $labelClass }}">نام بانک</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name') }}" class="{{ $inputClass }}" placeholder="مثلا: بانک ملت">
                </div>

                <div>
                    <label class="{{ $labelClass }}">نام و نام خانوادگی صاحب حساب</label>
                    <input type="text" name="account_owner_name" value="{{ old('account_owner_name', $user->name) }}" class="{{ $inputClass }}" placeholder="دقیقاً مطابق شناسنامه">
                </div>
            </div>
        </div>

        {{-- احراز هویت (اختیاری در فاز اول) --}}
        <div class="{{ $cardClass }} p-6 space-y-5">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                احراز هویت میزبان
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="{{ $labelClass }}">کد ملی</label>
                    <input type="text" name="national_code" value="{{ old('national_code') }}" class="{{ $inputClass }} text-left dir-ltr" placeholder="10 رقمی">
                </div>

                <div>
                    <label class="{{ $labelClass }}">تصویر کارت ملی</label>
                    <input type="file" name="national_card_image" accept="image/*" class="{{ $inputClass }}">
                </div>
            </div>
        </div>

        {{-- دکمه ارسال --}}
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('user.properties.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-600 dark:border-gray-600 dark:text-gray-300 text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                انصراف
            </a>
            <button type="submit" class="px-8 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-lg shadow-indigo-500/25 transition transform active:scale-95 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                ثبت درخواست میزبانی
            </button>
        </div>
    </form>
</div>
@endsection
