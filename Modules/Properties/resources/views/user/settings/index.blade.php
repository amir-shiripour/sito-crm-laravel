@extends('layouts.user')

@php
    $title = 'تنظیمات جامع املاک و اقامتگاه‌ها';

    // استایل‌های مشترک ارگونومیک سازگار با تم دارک و استاندارد پلتفرم
    $cardClass = "bg-white dark:bg-gray-800/90 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 shadow-sm overflow-hidden transition-all duration-200";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5 font-sans";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50/70 px-4 py-2.5 text-xs text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-100 dark:focus:bg-gray-800/90 placeholder-gray-400 dark:placeholder-gray-500 font-sans";
    $checkboxClass = "w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 cursor-pointer";
@endphp

@section('content')
<div x-data="propertySettingsManager({
    activeTab: window.location.hash ? window.location.hash.substring(1) : 'general',
    rentalEnabled: {{ ($rental_mode_enabled ?? 0) ? 'true' : 'false' }},
    multiHostEnabled: {{ ($rental_multi_host_enabled ?? 0) ? 'true' : 'false' }},
    mapService: '{{ $map_service ?? 'leaflet' }}',
    officeLat: {{ $office_location_lat ?? 35.6892 }},
    officeLng: {{ $office_location_lng ?? 51.3890 }},
    rulesTitle: @js($rental_settlement_rules_title ?? 'قوانین و رویه تسویه حساب درآمد:'),
    rules: @js($rental_settlement_rules ?? [])
})" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 font-sans">

    {{-- هدر صفحه با بردکرامب و دکمه ذخیره سریع --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-gray-100 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('user.properties.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">املاک</a>
                <span>/</span>
                <span class="text-slate-700 dark:text-slate-300 font-bold">تنظیمات سیستم</span>
            </div>
            <h1 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white flex items-center gap-2.5">
                <span class="flex items-center justify-center w-9 h-9 rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300 dark:border dark:border-indigo-500/30 shadow-inner">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </span>
                تنظیمات جامع املاک و اقامتگاه‌ها
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mr-11">
                پیکربندی هوشمند واحد پول، کدینگ، اقامتگاه‌های روزانه، قوانین تسویه حساب درآمد، دسترسی‌ها و نقشه
            </p>
        </div>

        {{-- دکمه ذخیره در هدر --}}
        <div class="flex items-center gap-2">
            <button type="button" 
                    @click="submitForm()"
                    class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span>ذخیره کلیه تنظیمات</span>
            </button>
        </div>
    </div>

    {{-- نوار تب‌های موضوعی (Categorized Segmented Tabs) جهت حذف اسکرول طولانی --}}
    <div class="bg-gray-100/80 dark:bg-gray-800/60 p-1.5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 flex items-center gap-1.5 overflow-x-auto no-scrollbar">
        {{-- تب ۱: عمومی و کدینگ --}}
        <button type="button" 
                @click="setTab('general')"
                :class="activeTab === 'general' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-sm border border-gray-200/50 dark:border-gray-600 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/50 dark:hover:bg-gray-700/40'"
                class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs whitespace-nowrap transition-all duration-150">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
            <span>عمومی و کدینگ</span>
        </button>

        {{-- تب ۲: اقامتگاه‌های روزانه و قوانین تسویه --}}
        <button type="button" 
                @click="setTab('rentals')"
                :class="activeTab === 'rentals' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-sm border border-gray-200/50 dark:border-gray-600 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/50 dark:hover:bg-gray-700/40'"
                class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs whitespace-nowrap transition-all duration-150">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
            <span>اقامتگاه روزانه و تسویه حساب</span>
            <span class="w-2 h-2 rounded-full bg-teal-500"></span>
        </button>

        {{-- تب ۳: سطوح دسترسی و محرمانگی --}}
        <button type="button" 
                @click="setTab('visibility')"
                :class="activeTab === 'visibility' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-sm border border-gray-200/50 dark:border-gray-600 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/50 dark:hover:bg-gray-700/40'"
                class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs whitespace-nowrap transition-all duration-150">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
            <span>سطوح دسترسی و نمایش</span>
        </button>

        {{-- تب ۴: نقشه و موقعیت دفتر --}}
        <button type="button" 
                @click="setTab('map')"
                :class="activeTab === 'map' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-sm border border-gray-200/50 dark:border-gray-600 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/50 dark:hover:bg-gray-700/40'"
                class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs whitespace-nowrap transition-all duration-150">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
            <span>سرویس نقشه و موقعیت</span>
        </button>

        {{-- تب ۵: رسانه، AI و فضای ذخیره‌سازی --}}
        <button type="button" 
                @click="setTab('media_ai')"
                :class="activeTab === 'media_ai' ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-300 shadow-sm border border-gray-200/50 dark:border-gray-600 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/50 dark:hover:bg-gray-700/40'"
                class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs whitespace-nowrap transition-all duration-150">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
            <span>رسانه، AI و حافظه</span>
        </button>
    </div>

    {{-- فرم اصلی ارسال تنظیمات --}}
    <form id="propertySettingsForm" action="{{ route('user.settings.properties.update') }}" method="POST" class="space-y-6">
        @csrf

        {{-- ======================================================== --}}
        {{-- تب ۱: عمومی، واحد پول و کدینگ --}}
        {{-- ======================================================== --}}
        <div x-show="activeTab === 'general'" x-cloak class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- کارت ۱: واحد پول سیستم --}}
                <div class="{{ $cardClass }} p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            واحد پول اصلی سامانه
                        </h2>
                        <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold">نمایش قیمت‌ها</span>
                    </div>

                    <div class="bg-gray-50/70 dark:bg-gray-900/40 rounded-2xl p-4 border border-gray-100 dark:border-gray-700/50 space-y-3">
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="radio" name="currency" value="toman" {{ $currency == 'toman' ? 'checked' : '' }} class="peer sr-only">
                                    <div class="w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-full peer-checked:border-indigo-600 peer-checked:bg-indigo-600 transition-all"></div>
                                    <div class="absolute inset-0 m-auto w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                </div>
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">تومان (پیش‌فرض)</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="radio" name="currency" value="rial" {{ $currency == 'rial' ? 'checked' : '' }} class="peer sr-only">
                                    <div class="w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-full peer-checked:border-indigo-600 peer-checked:bg-indigo-600 transition-all"></div>
                                    <div class="absolute inset-0 m-auto w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                </div>
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">ریال</span>
                            </label>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5 leading-relaxed">
                            <svg class="w-3.5 h-3.5 text-indigo-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            این واحد پولی در کلیه محاسبات، رزرواسیون، اجاره و فروش اقامتگاه‌ها اعمال خواهد شد.
                        </p>
                    </div>
                </div>

                {{-- کارت ۲: قالب‌بندی کد ملک --}}
                <div class="{{ $cardClass }} p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            قالب‌بندی و شماره‌گذاری کد ملک
                        </h2>
                        <span class="text-[11px] text-indigo-600 dark:text-indigo-400 font-bold">Coding Format</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="{{ $labelClass }}">پیش‌وند حروف (Prefix)</label>
                            <input type="text" name="property_code_prefix" value="{{ $property_code_prefix }}" class="{{ $inputClass }} dir-ltr text-center" placeholder="P">
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">جداکننده (Separator)</label>
                            <input type="text" name="property_code_separator" value="{{ $property_code_separator }}" class="{{ $inputClass }} dir-ltr text-center" placeholder="-">
                        </div>
                    </div>

                    <div class="space-y-2 pt-1">
                        <label class="flex items-center gap-3 p-2.5 rounded-xl border border-gray-200/80 dark:border-gray-700/60 bg-gray-50/60 dark:bg-gray-900/30 cursor-pointer hover:bg-white dark:hover:bg-gray-800 transition">
                            <input type="checkbox" name="property_code_include_year" value="1" {{ $property_code_include_year ? 'checked' : '' }} class="{{ $checkboxClass }}">
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">درج سال شمسی جاری در ابتدای کد ملک</span>
                        </label>

                        <label class="flex items-center gap-3 p-2.5 rounded-xl border border-gray-200/80 dark:border-gray-700/60 bg-gray-50/60 dark:bg-gray-900/30 cursor-pointer hover:bg-white dark:hover:bg-gray-800 transition">
                            <input type="checkbox" name="property_code_use_category_slug" value="1" {{ $property_code_use_category_slug ? 'checked' : '' }} class="{{ $checkboxClass }}">
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">استفاده از نامک دسته‌بندی ملک به عنوان پیش‌وند</span>
                        </label>
                    </div>

                    {{-- پیش‌نمایش زنده با فونت IRANYekanX --}}
                    <div class="text-center py-2 px-3 bg-indigo-50/70 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 rounded-xl text-xs font-bold font-sans border border-indigo-100 dark:border-indigo-900/40 dir-ltr">
                        نمونه کد تولیدی: 1403{{ $property_code_separator ?: '-' }}{{ $property_code_prefix ?: 'P' }}{{ $property_code_separator ?: '-' }}1001
                    </div>
                </div>

            </div>

            {{-- کارت ۳: تنظیمات نمایش و محدودیت مهمان --}}
            <div class="{{ $cardClass }} p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                    <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                        تنظیمات نمایش عمومی و کارت‌ها
                    </h2>
                    <span class="text-[11px] text-purple-600 dark:text-purple-400 font-bold">Display & Cards</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200/80 dark:border-gray-700/60 bg-gray-50/60 dark:bg-gray-900/30 cursor-pointer hover:bg-white dark:hover:bg-gray-800 transition">
                        <input type="checkbox" name="show_features_in_card" value="1" {{ $show_features_in_card ? 'checked' : '' }} class="{{ $checkboxClass }}">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200">نمایش ویژگی‌های کلیدی در کارت ملک</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">نمایش متراژ، کد ملک، خواب و امکانات خلاصه</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200/80 dark:border-gray-700/60 bg-gray-50/60 dark:bg-gray-900/30 cursor-pointer hover:bg-white dark:hover:bg-gray-800 transition">
                        <input type="checkbox" name="show_bookmark_button" value="1" {{ ($show_bookmark_button ?? true) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200">دکمه نشان‌کردن (علاقه‌مندی)</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">امکان ذخیره اقامتگاه در لیست علاقه‌مندی‌ها</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200/80 dark:border-gray-700/60 bg-gray-50/60 dark:bg-gray-900/30 cursor-pointer hover:bg-white dark:hover:bg-gray-800 transition">
                        <input type="checkbox" name="restrict_public_index_guests" value="1" {{ ($restrict_public_index_guests ?? false) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200">محدودیت لیست عمومی برای مهمانان</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">الزام ورود به سامانه برای مشاهده لیست املاک</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200/80 dark:border-gray-700/60 bg-gray-50/60 dark:bg-gray-900/30 cursor-pointer hover:bg-white dark:hover:bg-gray-800 transition">
                        <input type="checkbox" name="restrict_public_map_guests" value="1" {{ ($restrict_public_map_guests ?? false) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200">محدودیت نقشه عمومی برای مهمانان</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">الزام ورود به سامانه برای مشاهده نقشه تعاملی</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- تب ۲: اقامتگاه‌های روزانه و مدیریت قوانین تسویه حساب --}}
        {{-- ======================================================== --}}
        <div x-show="activeTab === 'rentals'" x-cloak class="space-y-6">

            {{-- کارت فعال‌سازی و تنظیمات بازار چندمیزبانی --}}
            <div class="{{ $cardClass }} p-5 sm:p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        </div>
                        <div>
                            <h2 class="text-xs font-black text-gray-900 dark:text-white">اقامتگاه‌های روزانه و سیستم چندمیزبانی (ویلا، بوم‌گردی و سوئیت)</h2>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">مدیریت رزرواسیون تقویمی، بازار چندمیزبانی و سیاست‌های تأیید آگهی</p>
                        </div>
                    </div>
                    <span class="text-xs bg-teal-50 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300 px-3 py-1 rounded-full font-bold border border-teal-100 dark:border-teal-800/40">Marketplace</span>
                </div>

                <div class="space-y-4">
                    {{-- فعال‌سازی حالت اجاره روزانه --}}
                    <label class="flex items-center justify-between p-4 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 bg-gray-50/70 dark:bg-gray-900/40 cursor-pointer hover:bg-white dark:hover:bg-gray-800 transition">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-gray-900 dark:text-white">فعال‌سازی ماژول اجاره روزانه و رزرواسیون</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">پشتیبانی از تقویم قیمت شبانه، آخر هفته، ظرفیت مهمان و مسدودسازی روزها</span>
                        </div>
                        <input type="checkbox" name="rental_mode_enabled" value="1" x-model="rentalEnabled" {{ ($rental_mode_enabled ?? 0) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                    </label>

                    {{-- زیرمجموعه اجاره روزانه --}}
                    <div x-show="rentalEnabled" x-transition class="space-y-4 pt-2">
                        <label class="flex items-center justify-between p-4 rounded-2xl border border-indigo-100 dark:border-indigo-900/50 bg-indigo-50/40 dark:bg-indigo-950/20 cursor-pointer hover:bg-indigo-50/70 dark:hover:bg-indigo-950/40 transition">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-indigo-950 dark:text-indigo-200 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    فعال‌سازی سیستم چندمیزبانی (Multi-Host Marketplace)
                                </span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">میزبانان می‌توانند مستقل ثبت‌نام کرده، مدارک احراز هویت را ارسال و اقامتگاه‌های خود را مدیریت کنند.</span>
                            </div>
                            <input type="checkbox" name="rental_multi_host_enabled" value="1" x-model="multiHostEnabled" {{ ($rental_multi_host_enabled ?? 0) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                        </label>

                        <div x-show="multiHostEnabled" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="flex items-center gap-3 p-3.5 rounded-xl border border-gray-200/80 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer">
                                <input type="checkbox" name="rental_host_auto_approve" value="1" {{ ($rental_host_auto_approve ?? 0) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">تأیید خودکار میزبانان جدید</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">بدون نیاز به بررسی دستی مدارک توسط ادمین</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-3.5 rounded-xl border border-gray-200/80 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer">
                                <input type="checkbox" name="rental_property_auto_approve" value="1" {{ ($rental_property_auto_approve ?? 0) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">تأیید خودکار اقامتگاه‌های ثبت‌شده</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">انتشار فوری و بدون بازبینی دستی مدیریت</span>
                                </div>
                            </label>

                            <label class="md:col-span-2 flex items-center gap-3 p-3.5 rounded-xl border border-gray-200/80 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer">
                                <input type="checkbox" name="rental_pending_host_can_create" value="1" {{ ($rental_pending_host_can_create ?? 0) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">امکان ثبت اقامتگاه در وضعیت «در انتظار تأیید مدیریت»</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">در صورت غیرفعال بودن (پیش‌فرض امنیتی)، میزبان تا زمان تایید هویت امکان ثبت اقامتگاه را نخواهد داشت.</span>
                                </div>
                            </label>

                            <div class="md:col-span-2 p-4 rounded-xl border border-gray-200/80 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <span class="text-xs font-bold text-gray-900 dark:text-white">درصد کارمزد پیش‌فرض پلتفرم</span>
                                    <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">سهم پلتفرم از مبلغ کل رزروها در صورت عدم تعیین نرخ اختصاصی برای میزبان</span>
                                </div>
                                <div class="w-36">
                                    <div class="relative">
                                        <input type="number" name="rental_default_commission" value="{{ $rental_default_commission ?? 10 }}" min="0" max="100" step="0.5" class="{{ $inputClass }} text-center font-sans font-bold" placeholder="10">
                                        <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-xs text-slate-400 font-sans">٪</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- کارت اختصاصی جدید: مدیریت قوانین و رویه تسویه حساب درآمد --}}
            {{-- ======================================================== --}}
            <div class="{{ $cardClass }} p-5 sm:p-6 space-y-6 border-indigo-100 dark:border-indigo-900/50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <div>
                            <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                                <span>مدیریت متن و بندهای «قوانین و رویه تسویه حساب درآمد»</span>
                                <span class="px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 text-[10px] font-bold">پروفایل میزبان</span>
                            </h2>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                متون نمایش‌داده‌شده در کارت تسویه حساب پروفایل میزبانان به صورت پویا از این بخش کنترل و ویرایش می‌شود.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="resetDefaultRules()" 
                                class="px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 text-slate-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-gray-800 text-[11px] font-bold transition">
                            بازنشانی به پیش‌فرض
                        </button>
                        <button type="button" 
                                @click="addRule()" 
                                class="px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-500/15 dark:text-indigo-300 dark:border dark:border-indigo-500/30 text-[11px] font-bold transition flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            <span>افزودن بند قانونی جدید</span>
                        </button>
                    </div>
                </div>

                {{-- فیلد عنوان بخش قوانین --}}
                <div class="space-y-1.5">
                    <label class="{{ $labelClass }}">
                        عنوان اصلی بخش قوانین تسویه در پروفایل میزبان:
                    </label>
                    <input type="text" 
                           name="rental_settlement_rules_title" 
                           x-model="rulesTitle" 
                           class="{{ $inputClass }} font-bold text-xs" 
                           placeholder="قوانین و رویه تسویه حساب درآمد:">
                </div>

                {{-- راهنمای کد جانشین هوشمند --}}
                <div class="p-3.5 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/40 text-xs text-indigo-900 dark:text-indigo-200 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <div class="space-y-0.5 text-[11px]">
                        <span class="font-bold">راهنمای هوشمند کارمزد:</span>
                        <p class="text-slate-600 dark:text-slate-300 leading-relaxed font-sans">
                            در هر یک از بندها، چنانچه از عبارت <code class="px-1.5 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 font-bold font-sans">{commission}</code> استفاده نمایید، در پروفایل هر میزبان درصد اختصاصی کارمزد همان میزبان (مثلاً ۱۰٪ یا ۱۲.۵٪) به صورت خودکار و برجسته جایگزین خواهد شد.
                        </p>
                    </div>
                </div>

                {{-- لیست تکرارکننده بندهای قوانین (Rules Repeater) --}}
                <div class="space-y-3">
                    <template x-for="(rule, index) in rules" :key="index">
                        <div class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200/70 dark:border-gray-700/70 space-y-3 relative transition hover:border-indigo-300 dark:hover:border-indigo-800">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-[10px] font-bold font-sans" x-text="index + 1"></span>
                                    <span>بند شماره <span x-text="index + 1"></span></span>
                                </span>

                                <button type="button" 
                                        @click="removeRule(index)" 
                                        class="p-1 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 transition" 
                                        title="حذف این بند">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">
                                        عنوان بند (مثلا: زمان تسویه):
                                    </label>
                                    <input type="text" 
                                           :name="'rental_settlement_rules[' + index + '][title]'" 
                                           x-model="rule.title" 
                                           class="{{ $inputClass }}" 
                                           placeholder="عنوان تیتر بند...">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">
                                        متن کامل بند قانونی:
                                    </label>
                                    <textarea :name="'rental_settlement_rules[' + index + '][text]'" 
                                              x-model="rule.text" 
                                              rows="2" 
                                              class="{{ $inputClass }} resize-none" 
                                              placeholder="شرح کامل بند قانونی تسویه..."></textarea>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- پیش‌نمایش زنده کارت تسویه در پروفایل میزبان --}}
                <div class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-2">
                    <span class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        پیش‌نمایش زنده در پروفایل میزبان:
                    </span>
                    <div class="p-5 rounded-2xl bg-gradient-to-br from-indigo-50/50 to-white dark:from-gray-800 dark:to-gray-800/80 border border-indigo-100 dark:border-gray-700 space-y-3">
                        <div class="flex items-center gap-2 text-indigo-700 dark:text-indigo-300 font-bold text-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span x-text="rulesTitle || 'قوانین و رویه تسویه حساب درآمد:'"></span>
                        </div>
                        <ul class="text-xs text-gray-600 dark:text-gray-300 space-y-2 leading-relaxed font-sans">
                            <template x-for="(r, idx) in rules" :key="idx">
                                <li class="flex items-start gap-2" x-show="r.title || r.text">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mt-1.5 flex-shrink-0"></span>
                                    <span>
                                        <strong x-show="r.title" x-text="r.title + ': '"></strong>
                                        <span x-text="r.text ? r.text.replace('{commission}', '۱۰٪') : ''"></span>
                                    </span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

            </div>

        </div>

        {{-- ======================================================== --}}
        {{-- تب ۳: سطوح دسترسی و محرمانگی (Visibility Matrix) --}}
        {{-- ======================================================== --}}
        <div x-show="activeTab === 'visibility'" x-cloak class="space-y-6">
            <div class="{{ $cardClass }} p-5 sm:p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-pink-50 dark:bg-pink-950/40 text-pink-600 dark:text-pink-400 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </div>
                        <div>
                            <h2 class="text-xs font-black text-gray-900 dark:text-white">ماتریس دسترسی و نمایش اطلاعات حساس املاک</h2>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">مشخص کنید هر نقش کاربری مجاز به مشاهده کدام بخش از اطلاعات است</p>
                        </div>
                    </div>
                    <span class="text-xs bg-pink-50 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300 px-3 py-1 rounded-full font-bold border border-pink-100 dark:border-pink-800/40">Permissions</span>
                </div>

                {{-- شبکه جامع کارت‌های دسترسی (۱۰ بخش کامل طبق قالب و کنترلر) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- ۱. اطلاعات مالک --}}
                    <div class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200/70 dark:border-gray-700/60 space-y-3">
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            مشاهده اطلاعات مالک (نام و شماره تماس)
                        </h3>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($roles as $role)
                                <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-indigo-400 transition">
                                    <input type="checkbox" name="visibility_owner_info[]" value="{{ $role->name }}" {{ in_array($role->name, $visibility_owner_info) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                    <span class="text-[11px] text-gray-700 dark:text-gray-300">{{ $role->display_name ?? $role->name }}</span>
                                </label>
                            @endforeach
                            <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-indigo-400 transition">
                                <input type="checkbox" name="visibility_owner_info[]" value="guest" {{ in_array('guest', $visibility_owner_info) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                <span class="text-[11px] text-gray-700 dark:text-gray-300">مهمان</span>
                            </label>
                        </div>
                    </div>

                    {{-- ۲. یادداشت‌های محرمانه --}}
                    <div class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200/70 dark:border-gray-700/60 space-y-3">
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                            مشاهده یادداشت‌های محرمانه ملک
                        </h3>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($roles as $role)
                                <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-indigo-400 transition">
                                    <input type="checkbox" name="visibility_confidential_notes[]" value="{{ $role->name }}" {{ in_array($role->name, $visibility_confidential_notes) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                    <span class="text-[11px] text-gray-700 dark:text-gray-300">{{ $role->display_name ?? $role->name }}</span>
                                </label>
                            @endforeach
                            <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-indigo-400 transition">
                                <input type="checkbox" name="visibility_confidential_notes[]" value="guest" {{ in_array('guest', $visibility_confidential_notes) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                <span class="text-[11px] text-gray-700 dark:text-gray-300">مهمان</span>
                            </label>
                        </div>
                    </div>

                    {{-- ۳. اطلاعات قیمت --}}
                    <div class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200/70 dark:border-gray-700/60 space-y-3">
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            مشاهده اطلاعات قیمت
                        </h3>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($roles as $role)
                                <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-indigo-400 transition">
                                    <input type="checkbox" name="visibility_price_info[]" value="{{ $role->name }}" {{ in_array($role->name, $visibility_price_info) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                    <span class="text-[11px] text-gray-700 dark:text-gray-300">{{ $role->display_name ?? $role->name }}</span>
                                </label>
                            @endforeach
                            <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-indigo-400 transition">
                                <input type="checkbox" name="visibility_price_info[]" value="guest" {{ in_array('guest', $visibility_price_info) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                <span class="text-[11px] text-gray-700 dark:text-gray-300">مهمان</span>
                            </label>
                        </div>
                    </div>

                    {{-- ۴. قیمت کف --}}
                    <div class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200/70 dark:border-gray-700/60 space-y-3">
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                            مشاهده قیمت کف (حداقل قیمت)
                        </h3>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($roles as $role)
                                <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-indigo-400 transition">
                                    <input type="checkbox" name="visibility_min_price[]" value="{{ $role->name }}" {{ in_array($role->name, $visibility_min_price ?? []) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                    <span class="text-[11px] text-gray-700 dark:text-gray-300">{{ $role->display_name ?? $role->name }}</span>
                                </label>
                            @endforeach
                            <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-indigo-400 transition">
                                <input type="checkbox" name="visibility_min_price[]" value="guest" {{ in_array('guest', $visibility_min_price ?? []) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                <span class="text-[11px] text-gray-700 dark:text-gray-300">مهمان</span>
                            </label>
                        </div>
                    </div>

                    {{-- ۵. قابلیت معاوضه --}}
                    <div class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200/70 dark:border-gray-700/60 space-y-3">
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>
                            مشاهده قابلیت معاوضه
                        </h3>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($roles as $role)
                                <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-indigo-400 transition">
                                    <input type="checkbox" name="visibility_convertible[]" value="{{ $role->name }}" {{ in_array($role->name, $visibility_convertible ?? []) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                    <span class="text-[11px] text-gray-700 dark:text-gray-300">{{ $role->display_name ?? $role->name }}</span>
                                </label>
                            @endforeach
                            <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-indigo-400 transition">
                                <input type="checkbox" name="visibility_convertible[]" value="guest" {{ in_array('guest', $visibility_convertible ?? []) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                <span class="text-[11px] text-gray-700 dark:text-gray-300">مهمان</span>
                            </label>
                        </div>
                    </div>

                    {{-- ۶. جزئیات معاوضه --}}
                    <div class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200/70 dark:border-gray-700/60 space-y-3">
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                            مشاهده جزئیات معاوضه (معاوضه شدن با چی)
                        </h3>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($roles as $role)
                                <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-indigo-400 transition">
                                    <input type="checkbox" name="visibility_convertible_with[]" value="{{ $role->name }}" {{ in_array($role->name, $visibility_convertible_with ?? []) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                    <span class="text-[11px] text-gray-700 dark:text-gray-300">{{ $role->display_name ?? $role->name }}</span>
                                </label>
                            @endforeach
                            <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-indigo-400 transition">
                                <input type="checkbox" name="visibility_convertible_with[]" value="guest" {{ in_array('guest', $visibility_convertible_with ?? []) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                <span class="text-[11px] text-gray-700 dark:text-gray-300">مهمان</span>
                            </label>
                        </div>
                    </div>

                    {{-- ۷. نقشه و آدرس دقیق --}}
                    <div class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200/70 dark:border-gray-700/60 space-y-3">
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                            مشاهده نقشه و آدرس دقیق ملک
                        </h3>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($roles as $role)
                                <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-indigo-400 transition">
                                    <input type="checkbox" name="visibility_map_info[]" value="{{ $role->name }}" {{ in_array($role->name, $visibility_map_info) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                    <span class="text-[11px] text-gray-700 dark:text-gray-300">{{ $role->display_name ?? $role->name }}</span>
                                </label>
                            @endforeach
                            <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-indigo-400 transition">
                                <input type="checkbox" name="visibility_map_info[]" value="guest" {{ in_array('guest', $visibility_map_info) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                <span class="text-[11px] text-gray-700 dark:text-gray-300">مهمان</span>
                            </label>
                        </div>
                    </div>

                    {{-- ۸. تصویر کاور --}}
                    <div class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200/70 dark:border-gray-700/60 space-y-3">
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            مشاهده تصویر کاور ملک
                        </h3>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($roles as $role)
                                <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-indigo-400 transition">
                                    <input type="checkbox" name="visibility_cover_image[]" value="{{ $role->name }}" {{ in_array($role->name, $visibility_cover_image ?? []) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                    <span class="text-[11px] text-gray-700 dark:text-gray-300">{{ $role->display_name ?? $role->name }}</span>
                                </label>
                            @endforeach
                            <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-indigo-400 transition">
                                <input type="checkbox" name="visibility_cover_image[]" value="guest" {{ in_array('guest', $visibility_cover_image ?? []) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                <span class="text-[11px] text-gray-700 dark:text-gray-300">مهمان</span>
                            </label>
                        </div>
                    </div>

                    {{-- ۹. گالری تصاویر --}}
                    <div class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200/70 dark:border-gray-700/60 space-y-3">
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                            مشاهده گالری تصاویر ملک
                        </h3>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($roles as $role)
                                <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-indigo-400 transition">
                                    <input type="checkbox" name="visibility_gallery_images[]" value="{{ $role->name }}" {{ in_array($role->name, $visibility_gallery_images ?? []) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                    <span class="text-[11px] text-gray-700 dark:text-gray-300">{{ $role->display_name ?? $role->name }}</span>
                                </label>
                            @endforeach
                            <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-indigo-400 transition">
                                <input type="checkbox" name="visibility_gallery_images[]" value="guest" {{ in_array('guest', $visibility_gallery_images ?? []) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                <span class="text-[11px] text-gray-700 dark:text-gray-300">مهمان</span>
                            </label>
                        </div>
                    </div>

                    {{-- ۱۰. نقش‌های مجاز برای مشاور --}}
                    <div class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200/70 dark:border-gray-700/60 space-y-3">
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                            نقش‌های مجاز برای انتخاب به عنوان مشاور
                        </h3>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($roles as $role)
                                <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-indigo-400 transition">
                                    <input type="checkbox" name="agent_roles[]" value="{{ $role->name }}" {{ in_array($role->name, $agent_roles) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                    <span class="text-[11px] text-gray-700 dark:text-gray-300">{{ $role->display_name ?? $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- تب ۴: سرویس نقشه و موقعیت دفتر مرکزی --}}
        {{-- ======================================================== --}}
        <div x-show="activeTab === 'map'" x-cloak class="space-y-6">
            <div class="{{ $cardClass }} p-5 sm:p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-orange-50 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                        </div>
                        <div>
                            <h2 class="text-xs font-black text-gray-900 dark:text-white">سرویس نقشه و موقعیت دفتر مرکزی</h2>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">پیکربندی ارائه‌دهنده نقشه و تعیین لوکیشن دفتر مرکزی پلتفرم</p>
                        </div>
                    </div>
                    <span class="text-xs bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300 px-3 py-1 rounded-full font-bold border border-orange-100 dark:border-orange-800/40">GIS & Map</span>
                </div>

                {{-- انتخاب سرویس‌دهنده --}}
                <div class="space-y-4">
                    <div class="bg-gray-50/70 dark:bg-gray-900/40 rounded-2xl p-4 border border-gray-100 dark:border-gray-700/50">
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="radio" name="map_service" value="leaflet" x-model="mapService" {{ ($map_service ?? 'leaflet') == 'leaflet' ? 'checked' : '' }} class="peer sr-only">
                                    <div class="w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-full peer-checked:border-indigo-600 peer-checked:bg-indigo-600 transition-all"></div>
                                    <div class="absolute inset-0 m-auto w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                </div>
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Leaflet (OpenStreetMap - استاندارد جهانی)</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="radio" name="map_service" value="map_ir" x-model="mapService" {{ ($map_service ?? '') == 'map_ir' ? 'checked' : '' }} class="peer sr-only">
                                    <div class="w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-full peer-checked:border-indigo-600 peer-checked:bg-indigo-600 transition-all"></div>
                                    <div class="absolute inset-0 m-auto w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                </div>
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Map.ir (نقشه بومی ایران)</span>
                            </label>
                        </div>
                    </div>

                    <div x-show="mapService === 'map_ir'" x-transition class="space-y-1.5 pt-1">
                        <label for="map_ir_api_key" class="{{ $labelClass }}">کلید اختصاصی API سرویس Map.ir</label>
                        <input type="text" id="map_ir_api_key" name="map_ir_api_key" value="{{ $map_ir_api_key ?? '' }}" class="{{ $inputClass }} dir-ltr text-left" placeholder="API Key Map.ir...">
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            جهت دریافت کلید رایگان به سامانه <a href="https://corp.map.ir/" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold">map.ir</a> مراجعه فرمایید.
                        </p>
                    </div>

                    {{-- فرم دفتر مرکزی و نقشه تعاملی --}}
                    <div class="space-y-3 pt-2">
                        <div>
                            <label class="{{ $labelClass }}">عنوان دفتر مرکزی</label>
                            <input type="text" name="office_location_title" value="{{ $office_location_title }}" class="{{ $inputClass }}" placeholder="مثلا: دفتر مرکزی پلتفرم">
                        </div>

                        <div class="h-64 sm:h-72 w-full rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 z-0 relative shadow-inner" id="office-map"></div>

                        <input type="hidden" name="office_location_lat" x-model="officeLat">
                        <input type="hidden" name="office_location_lng" x-model="officeLng">

                        <div class="flex items-center gap-4 text-xs text-slate-500 dark:text-slate-400 font-sans dir-ltr pt-1">
                            <span>عرض جغرافیایی (Lat): <span class="font-bold text-gray-900 dark:text-gray-100" x-text="officeLat"></span></span>
                            <span>•</span>
                            <span>طول جغرافیایی (Lng): <span class="font-bold text-gray-900 dark:text-gray-100" x-text="officeLng"></span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- تب ۵: رسانه، آپلود، هوش مصنوعی و حافظه --}}
        {{-- ======================================================== --}}
        <div x-show="activeTab === 'media_ai'" x-cloak class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- کارت ۱: محدودیت‌های آپلود --}}
                <div class="{{ $cardClass }} p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            محدودیت‌های فایل، تصویر و ویدیو
                        </h2>
                        <span class="text-[11px] text-amber-600 dark:text-amber-400 font-bold">Media Upload</span>
                    </div>

                    <div class="space-y-4">
                        {{-- تصویر --}}
                        <div class="space-y-2">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">تصاویر گالری اقامتگاه</span>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="{{ $labelClass }}">حداکثر حجم تصویر (KB)</label>
                                    <input type="number" name="max_file_size" value="{{ $max_file_size }}" class="{{ $inputClass }} dir-ltr text-center font-sans">
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">حداکثر تعداد تصویر</label>
                                    <input type="number" name="max_gallery_images" value="{{ $max_gallery_images }}" class="{{ $inputClass }} dir-ltr text-center font-sans">
                                </div>
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">فرمت‌های مجاز تصویر</label>
                                <input type="text" name="allowed_file_types" value="{{ $allowed_file_types }}" class="{{ $inputClass }} dir-ltr text-left font-sans" placeholder="jpg,jpeg,png,webp">
                            </div>
                        </div>

                        {{-- ویدیو --}}
                        <div class="space-y-2 pt-2 border-t border-gray-100 dark:border-gray-700/60">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">ویدیو معرفی اقامتگاه</span>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="{{ $labelClass }}">حداکثر حجم ویدیو (KB)</label>
                                    <input type="number" name="max_video_size" value="{{ $max_video_size }}" class="{{ $inputClass }} dir-ltr text-center font-sans">
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">فرمت‌های مجاز ویدیو</label>
                                    <input type="text" name="allowed_video_types" value="{{ $allowed_video_types }}" class="{{ $inputClass }} dir-ltr text-left font-sans" placeholder="mp4,mov,avi">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- کارت ۲: هوش مصنوعی و گزارش ذخیره‌سازی --}}
                <div class="space-y-6">
                    {{-- تنظیمات AI --}}
                    <div class="{{ $cardClass }} p-5 sm:p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                            <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                                قابلیت‌های هوش مصنوعی (AI)
                            </h2>
                            <span class="text-[11px] text-cyan-600 dark:text-cyan-400 font-bold">Smart Assist</span>
                        </div>

                        <div class="space-y-3">
                            <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200/80 dark:border-gray-700/60 bg-gray-50/60 dark:bg-gray-900/30 cursor-pointer hover:bg-white dark:hover:bg-gray-800 transition">
                                <input type="checkbox" name="ai_property_completion" value="1" {{ ($ai_property_completion ?? false) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">تکمیل هوشمند اطلاعات و عنوان ملک</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">تولید خودکار توضیحات جذاب برای آگهی با AI</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200/80 dark:border-gray-700/60 bg-gray-50/60 dark:bg-gray-900/30 cursor-pointer hover:bg-white dark:hover:bg-gray-800 transition">
                                <input type="checkbox" name="ai_property_search" value="1" {{ ($ai_property_search ?? false) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">جستجوی هوشمند محاوره‌ای</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">جستجو بر اساس جملات طبیعی و نیازهای مسافر</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- کارت وضعیت حافظه دیسک --}}
                    <div class="{{ $cardClass }} p-5 sm:p-6 space-y-4 bg-gradient-to-br from-gray-50/60 to-white dark:from-gray-800 dark:to-gray-800/60">
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                            <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                وضعیت فضای ذخیره‌سازی فایل‌ها
                            </h2>
                            <span class="text-[11px] text-blue-600 dark:text-blue-400 font-bold">Disk Storage</span>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3.5 rounded-xl bg-white dark:bg-gray-900/80 border border-gray-200/80 dark:border-gray-700/80 text-center shadow-inner">
                                <span class="block text-xl font-black text-indigo-600 dark:text-indigo-400 font-sans">{{ $fileCount }}</span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">فایل ذخیره شده</span>
                            </div>
                            <div class="p-3.5 rounded-xl bg-white dark:bg-gray-900/80 border border-gray-200/80 dark:border-gray-700/80 text-center shadow-inner">
                                <span class="block text-xl font-black text-indigo-600 dark:text-indigo-400 dir-ltr font-sans">{{ $formattedSize }}</span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">فضای اشغال شده</span>
                            </div>
                        </div>

                        <div class="bg-gray-100/80 dark:bg-gray-900/60 rounded-xl p-2.5 text-center border border-gray-200/50 dark:border-gray-700/40">
                            <span class="text-[11px] text-slate-600 dark:text-slate-400 dir-ltr font-sans">storage/app/public/properties</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- نوار شناور ذخیره در انتهای صفحه جهت دسترسی سریع --}}
        <div class="fixed bottom-6 left-0 right-0 z-40 flex justify-center pointer-events-none px-4">
            <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-md p-2.5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xl pointer-events-auto max-w-md w-full flex justify-between items-center gap-4">
                <span class="text-xs text-slate-600 dark:text-slate-300 mr-2 hidden sm:inline font-bold">
                    تغییرات در حال حاضر پیش‌نویس هستند
                </span>
                <button type="submit"
                        class="flex-1 px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-lg shadow-indigo-600/25 hover:bg-indigo-700 transition transform active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    ذخیره کلیه تغییرات
                </button>
            </div>
        </div>

    </form>
</div>

<script>
    function propertySettingsManager(config) {
        return {
            activeTab: config.activeTab || 'general',
            rentalEnabled: config.rentalEnabled,
            multiHostEnabled: config.multiHostEnabled,
            mapService: config.mapService,
            officeLat: config.officeLat,
            officeLng: config.officeLng,
            rulesTitle: config.rulesTitle,
            rules: config.rules && config.rules.length > 0 ? config.rules : [
                {
                    title: 'زمان تسویه',
                    text: 'مبالغ رزروها پس از تحویل اقامتگاه به مسافر و ورود بدون مغایرت در اولین سیکل پایا واریز می‌گردد.'
                },
                {
                    title: 'کارمزد پلتفرم',
                    text: 'سهم پلتفرم از هر رزرو {commission}٪ بوده و مابقی مستقیماً به شبا واریز می‌شود.'
                },
                {
                    title: 'تطابق حساب',
                    text: 'نام صاحب حساب باید با اطلاعات هویتی و کد ملی همخوانی کامل داشته باشد.'
                }
            ],
            mapInstance: null,
            markerInstance: null,

            init() {
                // هماهنگی تغییر تب با هش مرورگر
                window.addEventListener('hashchange', () => {
                    if (window.location.hash) {
                        this.activeTab = window.location.hash.substring(1);
                    }
                });

                // رصد تغییر تب جهت رفرش ابعاد نقشه در صورت لزوم
                this.$watch('activeTab', (val) => {
                    window.location.hash = val;
                    if (val === 'map') {
                        setTimeout(() => {
                            this.refreshMap();
                        }, 100);
                    }
                });

                this.$watch('mapService', () => {
                    if (this.activeTab === 'map') {
                        this.destroyMap();
                        this.initializeMap();
                    }
                });

                // راه‌اندازی اولیه نقشه در پس‌زمینه یا به محض لود
                setTimeout(() => {
                    this.initializeMap();
                }, 200);
            },

            setTab(tabName) {
                this.activeTab = tabName;
            },

            submitForm() {
                document.getElementById('propertySettingsForm').submit();
            },

            addRule() {
                this.rules.push({
                    title: '',
                    text: ''
                });
            },

            removeRule(idx) {
                if (this.rules.length > 1) {
                    this.rules.splice(idx, 1);
                } else {
                    this.rules = [{ title: '', text: '' }];
                }
            },

            resetDefaultRules() {
                this.rulesTitle = 'قوانین و رویه تسویه حساب درآمد:';
                this.rules = [
                    {
                        title: 'زمان تسویه',
                        text: 'مبالغ رزروها پس از تحویل اقامتگاه به مسافر و ورود بدون مغایرت در اولین سیکل پایا واریز می‌گردد.'
                    },
                    {
                        title: 'کارمزد پلتفرم',
                        text: 'سهم پلتفرم از هر رزرو {commission}٪ بوده و مابقی مستقیماً به شبا واریز می‌شود.'
                    },
                    {
                        title: 'تطابق حساب',
                        text: 'نام صاحب حساب باید با اطلاعات هویتی و کد ملی همخوانی کامل داشته باشد.'
                    }
                ];
            },

            refreshMap() {
                if (this.mapInstance) {
                    if (typeof this.mapInstance.invalidateSize === 'function') {
                        this.mapInstance.invalidateSize();
                    } else if (this.mapInstance.map && typeof this.mapInstance.map.invalidateSize === 'function') {
                        this.mapInstance.map.invalidateSize();
                    }
                } else {
                    this.initializeMap();
                }
            },

            initializeMap() {
                const mapEl = document.getElementById('office-map');
                if (!mapEl) return;

                if (this.mapService === 'leaflet') {
                    this.initLeaflet();
                } else if (this.mapService === 'map_ir') {
                    this.initMapIr();
                }
            },

            destroyMap() {
                if (this.mapInstance) {
                    if (typeof this.mapInstance.remove === 'function') {
                        this.mapInstance.remove();
                    } else if (this.mapInstance.map && typeof this.mapInstance.map.remove === 'function') {
                        this.mapInstance.map.remove();
                    }
                    this.mapInstance = null;
                    this.markerInstance = null;
                    const el = document.getElementById('office-map');
                    if (el) el.innerHTML = '';
                }
            },

            initLeaflet() {
                const loadLeafletMap = () => {
                    const el = document.getElementById('office-map');
                    if (!el) return;

                    this.mapInstance = L.map('office-map').setView([parseFloat(this.officeLat), parseFloat(this.officeLng)], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(this.mapInstance);

                    this.markerInstance = L.marker([parseFloat(this.officeLat), parseFloat(this.officeLng)], {draggable: true}).addTo(this.mapInstance);

                    this.markerInstance.on('dragend', (e) => {
                        const pos = e.target.getLatLng();
                        this.officeLat = pos.lat.toFixed(6);
                        this.officeLng = pos.lng.toFixed(6);
                    });

                    this.mapInstance.on('click', (e) => {
                        this.officeLat = e.latlng.lat.toFixed(6);
                        this.officeLng = e.latlng.lng.toFixed(6);
                        this.markerInstance.setLatLng([parseFloat(this.officeLat), parseFloat(this.officeLng)]);
                    });
                };

                if (typeof L === 'undefined') {
                    this.loadScript('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', 'css');
                    this.loadScript('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', 'js', loadLeafletMap);
                } else {
                    loadLeafletMap();
                }
            },

            initMapIr() {
                const baseUrl = '{{ asset("modules/properties/dist") }}';

                const loadAndSetup = () => {
                    this.loadScript(baseUrl + '/css/mapp.min.css', 'css');
                    this.loadScript(baseUrl + '/css/fa/style.css', 'css');

                    const loadMapp = () => this.loadScript(baseUrl + '/js/mapp.min.js', 'js', () => setTimeout(() => this.setupMapIr(), 0));
                    const loadEnv = () => this.loadScript(baseUrl + '/js/mapp.env.js', 'js', loadMapp);

                    if (typeof jQuery === 'undefined') {
                        this.loadScript(baseUrl + '/js/jquery-3.2.1.min.js', 'js', loadEnv);
                    } else {
                        loadEnv();
                    }
                };

                if (typeof Mapp === 'undefined') {
                    loadAndSetup();
                } else {
                    this.setupMapIr();
                }
            },

            setupMapIr() {
                if (typeof Mapp === 'undefined') return;

                const apiKeyInput = document.querySelector('input[name="map_ir_api_key"]');
                const currentApiKey = apiKeyInput ? apiKeyInput.value : '';
                if (!currentApiKey) {
                    const el = document.getElementById('office-map');
                    if (el) el.innerHTML = `<div class="flex items-center justify-center h-full bg-gray-100 dark:bg-gray-800 text-gray-500 text-xs p-4 text-center">برای نمایش نقشه، لطفاً کلید API سرویس Map.ir را وارد کنید.</div>`;
                    return;
                }

                this.mapInstance = new Mapp({
                    element: '#office-map',
                    presets: {
                        latlng: {
                            lat: parseFloat(this.officeLat),
                            lng: parseFloat(this.officeLng)
                        },
                        zoom: 13
                    },
                    apiKey: currentApiKey
                });

                this.mapInstance.addLayers();
                const leafletMapInstance = this.mapInstance.map;

                this.markerInstance = L.marker([parseFloat(this.officeLat), parseFloat(this.officeLng)], {draggable: true}).addTo(leafletMapInstance);

                this.markerInstance.on('dragend', (e) => {
                    const pos = e.target.getLatLng();
                    this.officeLat = pos.lat.toFixed(6);
                    this.officeLng = pos.lng.toFixed(6);
                });

                leafletMapInstance.on('click', (e) => {
                    this.officeLat = e.latlng.lat.toFixed(6);
                    this.officeLng = e.latlng.lng.toFixed(6);
                    this.markerInstance.setLatLng([parseFloat(this.officeLat), parseFloat(this.officeLng)]);
                });
            },

            loadScript(src, type, callback) {
                const existing = (type === 'js') ? document.querySelector(`script[src="${src}"]`) : document.querySelector(`link[href="${src}"]`);
                if (existing) {
                    if (callback) callback();
                    return;
                }

                let tag;
                if (type === 'js') {
                    tag = document.createElement('script');
                    tag.src = src;
                    tag.onload = callback;
                } else {
                    tag = document.createElement('link');
                    tag.href = src;
                    tag.rel = 'stylesheet';
                }
                document.head.appendChild(tag);
            }
        };
    }
</script>
@endsection
