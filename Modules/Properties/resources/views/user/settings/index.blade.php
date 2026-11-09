@extends('layouts.user')

@push('styles')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .custom-quill-wrapper .ql-toolbar {
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            border-color: #e5e7eb;
            background-color: #f9fafb;
            direction: rtl;
            text-align: right;
        }
        .dark .custom-quill-wrapper .ql-toolbar {
            border-color: #374151;
            background-color: #111827;
        }
        .custom-quill-wrapper .ql-container {
            border-bottom-left-radius: 0.75rem;
            border-bottom-right-radius: 0.75rem;
            border-color: #e5e7eb;
            min-height: 220px;
            font-family: inherit;
            font-size: 0.875rem;
        }
        .dark .custom-quill-wrapper .ql-container {
            border-color: #374151;
            color: #f3f4f6;
            background-color: #1f2937;
        }
        .custom-quill-wrapper .ql-editor {
            direction: rtl;
            text-align: right;
            font-family: inherit;
            min-height: 220px;
            line-height: 1.8;
        }
        .dark .ql-snow .ql-stroke { stroke: #9ca3af; }
        .dark .ql-snow .ql-fill { fill: #9ca3af; }
        .dark .ql-snow .ql-picker { color: #9ca3af; }
        .dark .ql-snow .ql-picker-options { background-color: #1f2937; border-color: #374151; color: #f3f4f6; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
@endpush

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
    rentalVideoEnabled: {{ ($rental_video_enabled ?? 1) ? 'true' : 'false' }},
    rentalBuildingEnabled: {{ ($rental_building_enabled ?? 1) ? 'true' : 'false' }},
    rentalPropertyTypes: @js(array_values($rental_property_types ?? [])),
    multiHostEnabled: {{ ($rental_multi_host_enabled ?? 0) ? 'true' : 'false' }},
    mapService: '{{ $map_service ?? 'leaflet' }}',
    officeLat: {{ $office_location_lat ?? 35.6892 }},
    officeLng: {{ $office_location_lng ?? 51.3890 }},
    mapBoundaryScope: '{{ $map_boundary_scope ?? 'iran' }}',
    mapSelectedProvinces: @js($map_selected_provinces ?? []),
    mapAllProvinces: @js($map_all_provinces ?? []),
    mapBoundsConfig: @js($map_bounds_config ?? []),
    rulesTitle: @js($rental_settlement_rules_title ?? 'قوانین و رویه تسویه حساب درآمد:'),
    rules: @js($rental_settlement_rules ?? []),
    hostTipsEnabled: {{ ($rental_host_tips_enabled ?? 1) ? 'true' : 'false' }},
    hostTipsTitle: @js($rental_host_tips_title ?? 'نکات کلیدی میزبانی موفق'),
    hostTips: @js($rental_host_tips ?? []),
    rentalSubTab: 'slider',
    heroSlides: @js($rental_hero_slides ?? []),
    rentalHeroBgImage: @js($rental_hero_bg_image ?? null),
    rentalHeroBgPreview: @js(!empty($rental_hero_bg_image) ? asset($rental_hero_bg_image) : null),
    deleteRentalHeroBg: false,
    rentalHeroMobileBgImage: @js($rental_hero_mobile_bg_image ?? null),
    rentalHeroMobileBgPreview: @js(!empty($rental_hero_mobile_bg_image) ? asset($rental_hero_mobile_bg_image) : null),
    deleteRentalHeroMobileBg: false,
    rentalHeroValign: @js($rental_hero_valign ?? 'center'),
    rentalHeroBgPosition: @js($rental_hero_bg_position ?? 'center'),
    rentalHeroMobileBgPosition: @js($rental_hero_mobile_bg_position ?? 'center'),
    rentalSeoContentEnabled: {{ ($rental_seo_content_enabled ?? 1) ? 'true' : 'false' }},
    rentalSeoContentTitle: @js($rental_seo_content_title ?? 'مستر ویلا؛ سامانه رزرواسیون و اجاره آنلاین ویلا و اقامتگاه در شمال'),
    rentalSeoContentBody: @js($rental_seo_content_body ?? 'مستر ویلا سال‌ها است که به عنوان مرجع تخصصی رزرو و اجاره آنلاین انواع اقامتگاه، کلبه‌های جنگلی، سوئیت و ویلاهای استخردار در زیباترین مناطق شمال کشور به‌ویژه منطقه سرسبز لفور و سوادکوه شناخته می‌شود. هدف ما ایجاد تجربه‌ای بی‌دغدغه و مطمئن از سفر برای تمام میهمانان گرامی است. تضمین تطابق کامل تصاویر با اقامتگاه، پشتیبانی ۲۴ ساعته در تمام روزهای هفته، تحویل تضمین‌شده و تسویه با میزبان پس از رضایت و ورود میهمان، از استانداردهای بنیادین مستر ویلا می‌باشد. با خیالی آسوده اقامتگاه رویایی خود را بررسی کرده و تنها با چند کلیک، خاطره‌ای ماندگار در دل طبیعت بسازید.')
})" class="w-full max-w-none px-4 sm:px-6 lg:px-8 py-6 space-y-6 font-sans">

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
    <form id="propertySettingsForm" action="{{ route('user.settings.properties.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
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

            {{-- ======================================================== --}}
            {{-- نوار زیر-تب‌های بخش اقامتگاه‌ها (Sub-Tabs)                 --}}
            {{-- ======================================================== --}}
            <div class="bg-gray-100/90 dark:bg-gray-800/80 p-1.5 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs flex items-center gap-1.5 overflow-x-auto no-scrollbar font-sans">
                {{-- زیر-تب ۱: اسلایدر و بنرها --}}
                <button type="button" 
                        @click="rentalSubTab = 'slider'"
                        :class="rentalSubTab === 'slider' ? 'bg-indigo-600 text-white shadow-sm font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/60 dark:hover:bg-gray-700/50'"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs whitespace-nowrap transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>اسلایدر و بنرهای تبلیغاتی</span>
                    <span x-show="heroSlides && heroSlides.length" class="px-1.5 py-0.5 rounded-full text-[10px] bg-white/20 text-white font-bold" x-text="heroSlides.length"></span>
                </button>

                {{-- زیر-تب ۲: پیکربندی و چندمیزبانی --}}
                <button type="button" 
                        @click="rentalSubTab = 'core'"
                        :class="rentalSubTab === 'core' ? 'bg-indigo-600 text-white shadow-sm font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/60 dark:hover:bg-gray-700/50'"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs whitespace-nowrap transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>پیکربندی و چندمیزبانی</span>
                </button>

                {{-- زیر-تب ۳: قوانین تسویه و نکات میزبانی --}}
                <button type="button" 
                        @click="rentalSubTab = 'rules_tips'"
                        :class="rentalSubTab === 'rules_tips' ? 'bg-indigo-600 text-white shadow-sm font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/60 dark:hover:bg-gray-700/50'"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs whitespace-nowrap transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>قوانین تسویه و نکات میزبانی</span>
                </button>

                {{-- زیر-تب ۴: حریم خصوصی و نمایش جزئیات --}}
                <button type="button" 
                        @click="rentalSubTab = 'display'"
                        :class="rentalSubTab === 'display' ? 'bg-indigo-600 text-white shadow-sm font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/60 dark:hover:bg-gray-700/50'"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs whitespace-nowrap transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>حریم خصوصی و نمایش جزئیات</span>
                </button>

                {{-- زیر-تب ۵: محتوای متنی و سئو صفحه اصلی --}}
                <button type="button" 
                        @click="rentalSubTab = 'seo_content'"
                        :class="rentalSubTab === 'seo_content' ? 'bg-indigo-600 text-white shadow-sm font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white/60 dark:hover:bg-gray-700/50'"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs whitespace-nowrap transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    <span>معرفی و سئو صفحه اصلی</span>
                    <span x-show="rentalSeoContentEnabled" class="w-2 h-2 rounded-full bg-emerald-400"></span>
                </button>
            </div>

            {{-- ======================================================== --}}
            {{-- ۱. بخش اسلایدر، بنرهای تبلیغاتی و پس‌زمینه هیرو       --}}
            {{-- ======================================================== --}}
            <div x-show="rentalSubTab === 'slider'" class="space-y-6">
                <input type="hidden" name="rental_hero_slides_submitted" value="1">

                {{-- کارت اختصاصی مدیریت تصویر پس‌زمینه هیرو سکشن (باکس جستجو و عنوان اصلی) --}}
                <div class="{{ $cardClass }} p-5 sm:p-6 space-y-6 border-indigo-100 dark:border-indigo-900/50">
                    {{-- هدر کارت پس‌زمینه هیرو --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-300 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                                    <span>تصویر پس‌زمینه هیرو سکشن صفحه اصلی</span>
                                    <span class="px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 text-[10px] font-bold">بخش جستجو و سربرگ</span>
                                </h2>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    آپلود تصویر پس‌زمینه عریض برای پشت عنوان و باکس جستجوی اصلی در تم MrVila
                                </p>
                            </div>
                        </div>

                        {{-- دکمه‌های اقدام سریع هیرو --}}
                        <div class="flex items-center gap-2 shrink-0">
                            <template x-if="rentalHeroBgPreview && !deleteRentalHeroBg">
                                <div class="flex items-center gap-2">
                                    <button type="button" 
                                            @click="$refs.heroBgFileInput.click()"
                                            class="px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                        <span>تغییر تصویر</span>
                                    </button>
                                    <button type="button" 
                                            @click="removeHeroBg()"
                                            class="px-3.5 py-2 rounded-xl bg-red-50 hover:bg-red-100 dark:bg-red-950/50 dark:hover:bg-red-900/60 text-red-600 dark:text-red-300 border border-red-200 dark:border-red-800 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>حذف تصویر</span>
                                    </button>
                                </div>
                            </template>
                            <template x-if="deleteRentalHeroBg">
                                <button type="button" 
                                        @click="undoDeleteHeroBg()"
                                        class="px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/50 dark:hover:bg-amber-900/60 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-700 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    <span>لغو حذف تصویر</span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- فیلدهای مخفی ارسال فرم --}}
                    <input type="file" 
                           name="rental_hero_bg_image_file" 
                           x-ref="heroBgFileInput" 
                           @change="handleHeroBgSelect($event)" 
                           accept="image/jpeg,image/png,image/webp,image/avif,image/gif" 
                           class="hidden">
                    <input type="hidden" name="delete_rental_hero_bg_image" :value="deleteRentalHeroBg ? '1' : '0'">

                    {{-- راهنما و مشخصات ابعاد --}}
                    <div class="p-4 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/20 border border-indigo-200/70 dark:border-indigo-800/40 flex flex-col md:flex-row md:items-center justify-between gap-3 font-sans">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="text-xs text-slate-700 dark:text-slate-300 space-y-1">
                                <p class="font-bold text-gray-900 dark:text-white">
                                    ابعاد استاندارد پیشنهادی برای پس‌زمینه هیرو سکشن:
                                </p>
                                <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed">
                                    ابعاد بهینه: <span class="font-bold text-indigo-600 dark:text-indigo-400">۱۹۲۰ × ۶۵۰ پیکسل</span> یا <span class="font-bold">۱۹۲۰ × ۷۲۰ پیکسل</span> (کیفیت بالا، عریض و پانوراما).
                                    تصویر به صورت <span class="font-bold text-indigo-600 dark:text-indigo-400">تمام‌عرض (Full-Width / سرتاسری)</span> بدون محدودیت کادر در دسکتاپ و موبایل نمایش می‌یابد و به صورت خودکار توسط موتور بهینه‌سازی هسته به فرمت سبک <span class="font-bold">WebP</span> تبدیل می‌شود.
                                </p>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">
                                    * در صورت حذف تصویر یا عدم آپلود، هیرو سکشن به شکل مینیمال پیش‌فرض (رنگ پس‌زمینه تم) نمایش داده خواهد شد.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- پیش‌نمایش زنده یا دراپ‌زون آپلود --}}
                    <div>
                        {{-- حالت ۱: تصویری وجود دارد و فعال است (نمایش پیش‌نمایش شبیه‌سازی‌شده هیرو تمام‌عرض) --}}
                        <template x-if="rentalHeroBgPreview && !deleteRentalHeroBg">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        پیش‌نمایش زنده در هیرو سکشن صفحه اصلی (تمام‌عرض):
                                    </span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                        (نمایش تمام‌عرض همراه با شبیه‌سازی عنوان و کادر جستجو روی دسکتاپ و موبایل)
                                    </span>
                                </div>

                                <div class="relative w-full rounded-2xl overflow-hidden shadow-xl border border-gray-200 dark:border-gray-700 aspect-[3.2/1] min-h-[220px] bg-cover flex items-center justify-center p-6 text-center transition-all"
                                     :style="'background-image: url(' + rentalHeroBgPreview + '); background-position: ' + rentalHeroBgPosition">
                                    {{-- گرادیان شبیه‌سازی شده تاریک تمام‌عرض --}}
                                    <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/40 to-black/65 backdrop-blur-[0.5px]"></div>
                                    
                                    {{-- المان‌های شبیه‌سازی شده --}}
                                    <div class="relative z-10 max-w-xl mx-auto space-y-4">
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-white text-[10px] font-bold border border-white/30">
                                            <span>نمایش تمام‌عرض (Full-Width) در دسکتاپ و موبایل</span>
                                        </div>
                                        <h3 class="text-sm sm:text-base md:text-lg font-black text-white drop-shadow-md">
                                            اجاره ویلا، کلبه، بومگردی و انواع اقامتگاه در شمال
                                        </h3>
                                        <div class="bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md rounded-xl p-2.5 shadow-xl border border-white/20 flex items-center justify-between text-right text-xs text-slate-600 dark:text-slate-300">
                                            <span class="font-bold">مقصد، تاریخ و نفرات...</span>
                                            <span class="px-3 py-1 rounded-lg bg-[#007A5E] text-white font-bold text-[11px]">جستجو</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- حالت ۲: کاربر تصویر را برای حذف علامت زده است --}}
                        <template x-if="deleteRentalHeroBg">
                            <div class="p-6 rounded-2xl border-2 border-dashed border-red-300 dark:border-red-800/60 bg-red-50/50 dark:bg-red-950/20 text-center space-y-3 font-sans">
                                <div class="w-12 h-12 rounded-2xl bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 mx-auto flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-xs font-bold text-red-700 dark:text-red-300">
                                        تصویر پس‌زمینه برای حذف نشانه‌گذاری شد
                                    </p>
                                    <p class="text-[11px] text-red-600/80 dark:text-red-400/80">
                                        پس از کلیک بر روی دکمه «ذخیره تنظیمات»، تصویر به طور کامل حذف شده و هیرو سکشن به حالت مینیمال بازمی‌گردد.
                                    </p>
                                </div>
                                <button type="button" 
                                        @click="undoDeleteHeroBg()"
                                        class="px-4 py-2 rounded-xl bg-white dark:bg-zinc-800 border border-red-200 dark:border-red-800 text-xs font-bold text-red-600 dark:text-red-300 hover:bg-red-50 dark:hover:bg-zinc-700 transition cursor-pointer">
                                    انصراف و بازگردانی تصویر
                                </button>
                            </div>
                        </template>

                        {{-- حالت ۳: هیچ تصویری تنظیم نشده است (دراپ‌زون آپلود) --}}
                        <template x-if="!rentalHeroBgPreview && !deleteRentalHeroBg">
                            <div @click="$refs.heroBgFileInput.click()"
                                 class="p-8 rounded-2xl border-2 border-dashed border-indigo-200 dark:border-indigo-900/60 hover:border-indigo-500 dark:hover:border-indigo-500 bg-indigo-50/30 dark:bg-indigo-950/10 hover:bg-indigo-50/60 dark:hover:bg-indigo-950/20 transition-all cursor-pointer text-center space-y-3 font-sans group">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 mx-auto flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                        برای آپلود تصویر پس‌زمینه هیرو سکشن کلیک کنید
                                    </p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                        یا فایل تصویر خود را به این کادر بکشید (WebP, JPG, PNG تا سقف ۱۵ مگابایت)
                                    </p>
                                </div>
                                <button type="button" 
                                        class="px-4 py-2 rounded-xl bg-indigo-600 group-hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition inline-flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <span>انتخاب تصویر از کامپیوتر</span>
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- کنترل موقعیت پس‌زمینه دسکتاپ (background-position) و چیدمان عمودی محتوا --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- موقعیت فوکوس پس‌زمینه (background-position) --}}
                        <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700/70 space-y-2">
                            <div>
                                <label class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>موقعیت کادر عکس دسکتاپ (Background Position)</span>
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    تنظیم نقطه تمرکز و تراز تصویر پس‌زمینه دسکتاپ (بالا، مرکز یا پایین)
                                </p>
                            </div>

                            <input type="hidden" name="rental_hero_bg_position" :value="rentalHeroBgPosition">

                            <div class="inline-flex p-1 rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 w-full justify-between">
                                <button type="button" 
                                        @click="rentalHeroBgPosition = 'top'"
                                        :class="rentalHeroBgPosition === 'top' ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-gray-200'"
                                        class="flex-1 py-1.5 rounded-lg text-xs transition cursor-pointer text-center">
                                    بالا
                                </button>
                                <button type="button" 
                                        @click="rentalHeroBgPosition = 'center'"
                                        :class="rentalHeroBgPosition === 'center' ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-gray-200'"
                                        class="flex-1 py-1.5 rounded-lg text-xs transition cursor-pointer text-center">
                                    مرکز (پیش‌فرض)
                                </button>
                                <button type="button" 
                                        @click="rentalHeroBgPosition = 'bottom'"
                                        :class="rentalHeroBgPosition === 'bottom' ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-gray-200'"
                                        class="flex-1 py-1.5 rounded-lg text-xs transition cursor-pointer text-center">
                                    پایین
                                </button>
                            </div>
                        </div>

                        {{-- موقعیت عمودی محتوا (وسط یا بالا) --}}
                        <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700/70 space-y-2">
                            <div>
                                <label class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                    </svg>
                                    <span>موقعیت عمودی محتوا (عنوان و جستجو)</span>
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    محل قرارگیری باکس جستجو و تیتر بر روی تصویر
                                </p>
                            </div>

                            <input type="hidden" name="rental_hero_valign" :value="rentalHeroValign">

                            <div class="inline-flex p-1 rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 w-full justify-between">
                                <button type="button" 
                                        @click="rentalHeroValign = 'center'"
                                        :class="rentalHeroValign === 'center' ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-gray-200'"
                                        class="flex-1 py-1.5 rounded-lg text-xs transition cursor-pointer text-center">
                                    وسط (پیش‌فرض)
                                </button>
                                <button type="button" 
                                        @click="rentalHeroValign = 'top'"
                                        :class="rentalHeroValign === 'top' ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-gray-200'"
                                        class="flex-1 py-1.5 rounded-lg text-xs transition cursor-pointer text-center">
                                    بالا
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- بخش تصویر پس‌زمینه اختصاصی موبایل --}}
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-300 flex items-center justify-center font-bold">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                                        <span>تصویر پس‌زمینه اختصاصی موبایل</span>
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-bold">نمایش در گوشی‌ها</span>
                                    </h3>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                        آپلود تصویر مناسب برای نمایشگرهای عمودی گوشی‌های موبایل (ابعاد پیشنهادی: ۱۰۸۰ × ۷۲۰ پیکسل)
                                    </p>
                                </div>
                            </div>

                            {{-- دکمه‌های اقدام تصویر موبایل --}}
                            <div class="flex items-center gap-2 shrink-0">
                                <template x-if="rentalHeroMobileBgPreview && !deleteRentalHeroMobileBg">
                                    <div class="flex items-center gap-2">
                                        <button type="button" 
                                                @click="$refs.heroMobileBgFileInput.click()"
                                                class="px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                            <span>تغییر تصویر موبایل</span>
                                        </button>
                                        <button type="button" 
                                                @click="removeHeroMobileBg()"
                                                class="px-3 py-1.5 rounded-xl bg-red-50 hover:bg-red-100 dark:bg-red-950/50 dark:hover:bg-red-900/60 text-red-600 dark:text-red-300 border border-red-200 dark:border-red-800 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>حذف</span>
                                        </button>
                                    </div>
                                </template>
                                <template x-if="deleteRentalHeroMobileBg">
                                    <button type="button" 
                                            @click="undoDeleteHeroMobileBg()"
                                            class="px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/50 dark:hover:bg-amber-900/60 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-700 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                        <span>لغو حذف</span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- فیلدهای مخفی ارسال تصویر موبایل --}}
                        <input type="file" 
                               name="rental_hero_mobile_bg_image_file" 
                               x-ref="heroMobileBgFileInput" 
                               @change="handleHeroMobileBgSelect($event)" 
                               accept="image/jpeg,image/png,image/webp,image/avif,image/gif" 
                               class="hidden">
                        <input type="hidden" name="delete_rental_hero_mobile_bg_image" :value="deleteRentalHeroMobileBg ? '1' : '0'">

                        {{-- وضعیت یا آپلود تصویر موبایل --}}
                        <div>
                            <template x-if="rentalHeroMobileBgPreview && !deleteRentalHeroMobileBg">
                                <div class="space-y-2">
                                    <div class="relative w-full max-w-sm mx-auto rounded-2xl overflow-hidden shadow-md border border-gray-200 dark:border-gray-700 aspect-[16/9] sm:aspect-[2/1] bg-cover bg-center flex items-center justify-center p-4 text-center"
                                         :style="'background-image: url(' + rentalHeroMobileBgPreview + ')'">
                                        <div class="absolute inset-0 bg-black/20"></div>
                                        <span class="relative z-10 px-3 py-1 rounded-full bg-black/50 backdrop-blur-md text-white text-[11px] font-bold border border-white/20">
                                            پیش‌نمایش تصویر موبایل
                                        </span>
                                    </div>
                                </div>
                            </template>

                            <template x-if="deleteRentalHeroMobileBg">
                                <div class="p-4 rounded-xl border border-dashed border-red-300 dark:border-red-800 bg-red-50/50 dark:bg-red-950/20 text-center space-y-1">
                                    <p class="text-xs font-bold text-red-600 dark:text-red-400">تصویر موبایل برای حذف نشانه‌گذاری شد.</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">در صورت عدم وجود تصویر موبایل، از تصویر دسکتاپ (در صورت وجود) یا حالت پیش‌فرض استفاده خواهد شد.</p>
                                </div>
                            </template>

                            <template x-if="!rentalHeroMobileBgPreview && !deleteRentalHeroMobileBg">
                                <div @click="$refs.heroMobileBgFileInput.click()"
                                     class="p-5 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 bg-gray-50/50 dark:bg-gray-800/20 hover:bg-indigo-50/30 transition-all cursor-pointer text-center space-y-2 font-sans group">
                                    <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 group-hover:text-indigo-600 group-hover:bg-indigo-50 dark:group-hover:bg-indigo-950/40 mx-auto flex items-center justify-center transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-700 dark:text-gray-300">
                                            آپلود تصویر اختصاصی پس‌زمینه برای موبایل (اختیاری)
                                        </p>
                                        <p class="text-[11px] text-slate-400 dark:text-slate-500">
                                            در صورت عدم انتخاب، از تصویر پس‌زمینه دسکتاپ به عنوان تصویر موبایل استفاده خواهد شد
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- کنترل موقعیت کادر عکس موبایل (background-position) --}}
                        <div class="p-3.5 rounded-xl bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700/70 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <label class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    <span>موقعیت کادر عکس در موبایل (Mobile Background Position)</span>
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    تراز و نقطه تمرکز تصویر روی نمایشگر گوشی‌ها (بالا، مرکز، یا پایین)
                                </p>
                            </div>

                            <input type="hidden" name="rental_hero_mobile_bg_position" :value="rentalHeroMobileBgPosition">

                            <div class="inline-flex p-1 rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shrink-0">
                                <button type="button" 
                                        @click="rentalHeroMobileBgPosition = 'top'"
                                        :class="rentalHeroMobileBgPosition === 'top' ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-gray-200'"
                                        class="px-3.5 py-1.5 rounded-lg text-xs transition cursor-pointer text-center">
                                    بالا
                                </button>
                                <button type="button" 
                                        @click="rentalHeroMobileBgPosition = 'center'"
                                        :class="rentalHeroMobileBgPosition === 'center' ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-gray-200'"
                                        class="px-3.5 py-1.5 rounded-lg text-xs transition cursor-pointer text-center">
                                    مرکز (پیش‌فرض)
                                </button>
                                <button type="button" 
                                        @click="rentalHeroMobileBgPosition = 'bottom'"
                                        :class="rentalHeroMobileBgPosition === 'bottom' ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-gray-200'"
                                        class="px-3.5 py-1.5 rounded-lg text-xs transition cursor-pointer text-center">
                                    پایین
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ۲. کارت مدیریت اسلایدر و بنرهای تبلیغاتی --}}
                <div class="{{ $cardClass }} p-5 sm:p-6 space-y-6 border-indigo-100 dark:border-indigo-900/50">
                    {{-- هدر کارت اسلایدر --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-300 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                                    <span>مدیریت اسلایدر و بنرهای تبلیغاتی صفحه اصلی</span>
                                    <span class="px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 text-[10px] font-bold">اسلایدر بنرها</span>
                                </h2>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    آپلود تصاویر بنر، تنظیم لینک و ترتیب نمایش در اسلایدر اختصاصی بین بخش‌های صفحه اصلی
                                </p>
                            </div>
                        </div>

                        <button type="button" 
                                @click="addSlide()"
                                class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5 cursor-pointer shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>افزودن اسلاید جدید</span>
                        </button>
                    </div>

                    {{-- راهنمای بصری ابعاد و یکپارچگی بهینه‌ساز هسته --}}
                    <div class="p-4 rounded-2xl bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200/70 dark:border-amber-800/40 flex flex-col md:flex-row md:items-center justify-between gap-3 font-sans">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="text-xs text-slate-700 dark:text-slate-300 space-y-1">
                                <p class="font-bold text-gray-900 dark:text-white">
                                    ابعاد استاندارد پیشنهادی برای طراحی بنرها (تناسب ۲.۶ به ۱):
                                </p>
                                <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed">
                                    ابعاد بهینه: <span class="font-bold text-indigo-600 dark:text-indigo-400">۱۲۰۰ × ۴۶۵ پیکسل</span> (کیفیت بسیار بالا / رتینا)، <span class="font-bold">۱۰۲۴ × ۳۹۶ پیکسل</span>، یا <span class="font-bold">۶۴۰ × ۲۴۸ پیکسل</span>.
                                    تصاویر به صورت خودکار توسط سرویس بهینه‌ساز هسته به فرمت سبک <span class="font-bold">WebP</span> تبدیل و در گزارش بهینه‌ساز سیستم ثبت می‌شوند.
                                </p>
                                <p class="text-[10px] text-amber-700 dark:text-amber-400 font-medium">
                                    * توجه: در صورت عدم وجود اسلاید، کادر اسلایدر در صفحه اصلی نمایش داده نخواهد شد.
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('admin.optimizer-report') }}" target="_blank" class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 font-bold shrink-0">
                            <span>مشاهده گزارش بهینه‌ساز هسته</span>
                            <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    {{-- حالت خالی بودن اسلایدها --}}
                    <div x-show="!heroSlides || heroSlides.length === 0" class="text-center py-12 px-4 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700/80 bg-gray-50/50 dark:bg-gray-900/30">
                        <div class="w-16 h-16 mx-auto rounded-3xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-3">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white">هیچ اسلایدی ثبت نشده است</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                            برای نمایش اسلایدر بنر در بالای صفحه اصلی، روی دکمه «افزودن اسلاید جدید» کلیک کنید.
                        </p>
                        <button type="button" @click="addSlide()" class="mt-4 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition">
                            افزودن اولین اسلاید
                        </button>
                    </div>

                    {{-- لیست اسلایدها (Repeater) --}}
                    <div class="space-y-4" x-show="heroSlides && heroSlides.length > 0">
                        <template x-for="(slide, sIdx) in heroSlides" :key="slide.id">
                            <div class="p-4 sm:p-5 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 bg-gray-50/60 dark:bg-gray-900/50 space-y-4 transition hover:border-indigo-300 dark:hover:border-indigo-600/50">
                                {{-- هدر اسلاید: شماره، ترتیب، فعال بودن و دکمه حذف --}}
                                <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-gray-200/60 dark:border-gray-700/60">
                                    <div class="flex items-center gap-2">
                                        <span class="flex items-center justify-center w-6 h-6 rounded-lg bg-indigo-600 text-white text-[11px] font-bold" x-text="sIdx + 1"></span>
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">
                                            اسلاید شماره <span x-text="sIdx + 1"></span>
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        {{-- سوییچ فعال / غیرفعال --}}
                                        <label class="flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs cursor-pointer select-none">
                                            <input type="checkbox" 
                                                   :name="'rental_hero_slides[' + sIdx + '][is_active]'" 
                                                   value="1" 
                                                   x-model="slide.is_active" 
                                                   class="{{ $checkboxClass }}">
                                            <span class="text-[11px] font-bold" :class="slide.is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'">
                                                <span x-show="slide.is_active">فعال</span>
                                                <span x-show="!slide.is_active">غیرفعال</span>
                                            </span>
                                        </label>

                                        {{-- دکمه جابجایی به بالا --}}
                                        <button type="button" 
                                                @click="moveSlide(sIdx, -1)" 
                                                :disabled="sIdx === 0"
                                                :class="sIdx === 0 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer'"
                                                class="w-7 h-7 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-slate-600 dark:text-slate-300 transition"
                                                title="انتقال به بالا">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        </button>

                                        {{-- دکمه جابجایی به پایین --}}
                                        <button type="button" 
                                                @click="moveSlide(sIdx, 1)" 
                                                :disabled="sIdx === heroSlides.length - 1"
                                                :class="sIdx === heroSlides.length - 1 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer'"
                                                class="w-7 h-7 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-slate-600 dark:text-slate-300 transition"
                                                title="انتقال به پایین">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>

                                        {{-- دکمه حذف اسلاید --}}
                                        <button type="button" 
                                                @click="removeSlide(sIdx)"
                                                class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900/50 flex items-center justify-center hover:bg-rose-100 dark:hover:bg-rose-900/60 transition cursor-pointer"
                                                title="حذف اسلاید">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- فیلدهای پنهان --}}
                                <input type="hidden" :name="'rental_hero_slides[' + sIdx + '][id]'" :value="slide.id">
                                <input type="hidden" :name="'rental_hero_slides[' + sIdx + '][order]'" :value="sIdx">
                                <input type="hidden" :name="'rental_hero_slides[' + sIdx + '][image]'" :value="slide.image">

                                {{-- گرید محتوای اسلاید: سمت راست آپلود و پیش‌نمایش تصویر، سمت چپ تنظیمات لینک و متن --}}
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
                                    {{-- کادر آپلود و پیش‌نمایش تصویر --}}
                                    <div class="md:col-span-5 space-y-2">
                                        <label class="{{ $labelClass }}">تصویر بنر (نسبت ۲.۶ به ۱):</label>
                                        <div class="relative w-full aspect-[2.6/1] rounded-2xl overflow-hidden border-2 border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 flex items-center justify-center group hover:border-indigo-500 transition cursor-pointer shadow-xs">
                                            {{-- عکس پیش‌نمایش یا عکس قبلی --}}
                                            <template x-if="slide.preview || slide.image">
                                                <img :src="slide.preview || (slide.image ? (slide.image.startsWith('http') || slide.image.startsWith('/') ? slide.image : '/' + slide.image) : '')"
                                                     alt="پیش‌نمایش بنر"
                                                     class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!slide.preview && !slide.image">
                                                <div class="text-center p-3 text-slate-400 dark:text-slate-500 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">
                                                    <svg class="w-8 h-8 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                                    <span class="text-[11px] font-bold block">انتخاب و بارگذاری تصویر بنر</span>
                                                    <span class="text-[10px] text-slate-400">۱۲۰۰×۴۶۵ یا ۱۰۲۴×۳۹۶</span>
                                                </div>
                                            </template>

                                            {{-- لایه تغییر تصویر هنگام وجود عکس قبلی --}}
                                            <div x-show="slide.preview || slide.image" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold gap-1.5 backdrop-blur-xs">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <span>تغییر تصویر بنر</span>
                                            </div>

                                            {{-- ورودی فایل --}}
                                            <input type="file" 
                                                   :name="'rental_hero_slides[' + sIdx + '][image_file]'" 
                                                   accept="image/*" 
                                                   @change="handleImageSelect($event, sIdx)" 
                                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                        </div>
                                    </div>

                                    {{-- تنظیمات لینک و اطلاعات بنر --}}
                                    <div class="md:col-span-7 space-y-3 pt-1">
                                        {{-- لینک مقصد --}}
                                        <div>
                                            <label class="{{ $labelClass }}">لینک مقصد بنر (URL / Route):</label>
                                            <input type="text" 
                                                   :name="'rental_hero_slides[' + sIdx + '][link]'" 
                                                   x-model="slide.link" 
                                                   class="{{ $inputClass }} font-sans" 
                                                   placeholder="مثال: /register یا /properties?category=cottage یا لینک اینترنتی">
                                            {{-- پیشنهادهای سریع لینک --}}
                                            <div class="flex flex-wrap items-center gap-1.5 mt-1.5 text-[11px] text-slate-500">
                                                <span class="text-[10px]">پیشنهادها:</span>
                                                <button type="button" @click="slide.link = '/register'" class="px-2 py-0.5 rounded-lg bg-gray-200/80 dark:bg-gray-800 hover:bg-indigo-100 dark:hover:bg-indigo-950 text-slate-700 dark:text-slate-300 hover:text-indigo-600 transition text-[10px] font-sans">
                                                    ثبت نام میزبان (/register)
                                                </button>
                                                <button type="button" @click="slide.link = '/properties?category=cottage'" class="px-2 py-0.5 rounded-lg bg-gray-200/80 dark:bg-gray-800 hover:bg-indigo-100 dark:hover:bg-indigo-950 text-slate-700 dark:text-slate-300 hover:text-indigo-600 transition text-[10px] font-sans">
                                                    کلبه‌ها
                                                </button>
                                                <button type="button" @click="slide.link = '/properties?instant=1'" class="px-2 py-0.5 rounded-lg bg-gray-200/80 dark:bg-gray-800 hover:bg-indigo-100 dark:hover:bg-indigo-950 text-slate-700 dark:text-slate-300 hover:text-indigo-600 transition text-[10px] font-sans">
                                                    رزرو فوری
                                                </button>
                                            </div>
                                        </div>

                                        {{-- عنوان بنر و تارگت در یک سطر --}}
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <label class="{{ $labelClass }}">عنوان / متن جایگزین (Alt & Title):</label>
                                                <input type="text" 
                                                       :name="'rental_hero_slides[' + sIdx + '][title]'" 
                                                       x-model="slide.title" 
                                                       class="{{ $inputClass }} font-sans" 
                                                       placeholder="عنوان اسلاید (جهت سئو و دسترس‌پذیری)">
                                            </div>

                                            <div>
                                                <label class="{{ $labelClass }}">نحوه باز شدن لینک:</label>
                                                <select :name="'rental_hero_slides[' + sIdx + '][target]'" 
                                                        x-model="slide.target" 
                                                        class="{{ $inputClass }} font-sans">
                                                    <option value="_self">در همان برگه (_self)</option>
                                                    <option value="_blank">در برگه جدید (_blank)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- ۲. بخش پیکربندی و چندمیزبانی                           --}}
            {{-- ======================================================== --}}
            <div x-show="rentalSubTab === 'core'" class="space-y-6">

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
                        {{-- امکانات و فیلدهای فرم اقامتگاه (اجاره روزانه) --}}
                        <div class="p-4 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 bg-white/70 dark:bg-gray-800/70 space-y-3">
                            <div class="flex items-center gap-2 pb-2 border-b border-gray-100 dark:border-gray-700/60">
                                <span class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                <span class="text-xs font-bold text-gray-900 dark:text-white">امکانات و فیلدهای فرم اقامتگاه (اجاره روزانه)</span>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400">کنترل فیلدهای اختیاری در فرم ایجاد و ویرایش ملک</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                                {{-- سوییچ ویدیو معرفی --}}
                                <label class="flex items-start justify-between p-3.5 rounded-xl border border-gray-200/80 dark:border-gray-700/60 bg-gray-50/60 dark:bg-gray-900/40 cursor-pointer hover:bg-white dark:hover:bg-gray-800 transition">
                                    <div class="flex flex-col pr-1">
                                        <span class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            ویدیو معرفی (اختیاری)
                                        </span>
                                        <span class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                            در صورت غیرفعال بودن، کادر بارگذاری ویدیو در فرم‌های ثبت و ویرایش اقامتگاه مخفی می‌شود.
                                        </span>
                                    </div>
                                    <input type="checkbox" name="rental_video_enabled" value="1" x-model="rentalVideoEnabled" {{ ($rental_video_enabled ?? 1) ? 'checked' : '' }} class="{{ $checkboxClass }} shrink-0 mt-0.5">
                                </label>

                                {{-- سوییچ ساختمان / برج --}}
                                <label class="flex items-start justify-between p-3.5 rounded-xl border border-gray-200/80 dark:border-gray-700/60 bg-gray-50/60 dark:bg-gray-900/40 cursor-pointer hover:bg-white dark:hover:bg-gray-800 transition">
                                    <div class="flex flex-col pr-1">
                                        <span class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            ساختمان / برج
                                        </span>
                                        <span class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                            در صورت غیرفعال بودن، فیلد ساختمان در فرم‌ها مخفی شده و منوی مدیریت ساختمان‌ها نیز از سایدبار حذف می‌گردد.
                                        </span>
                                    </div>
                                    <input type="checkbox" name="rental_building_enabled" value="1" x-model="rentalBuildingEnabled" {{ ($rental_building_enabled ?? 1) ? 'checked' : '' }} class="{{ $checkboxClass }} shrink-0 mt-0.5">
                                </label>
                            </div>
                        </div>

                        {{-- کارت مدیریت انواع اقامتگاه (نوع ملک در اجاره روزانه) --}}
                        <div class="p-4 sm:p-5 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 bg-white/70 dark:bg-gray-800/70 space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100 dark:border-gray-700/60">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-teal-500 shadow-[0_0_8px_rgba(20,184,166,0.5)]"></span>
                                    <div>
                                        <h3 class="text-xs font-bold text-gray-900 dark:text-white">انواع اقامتگاه (دسته‌بندی نوع ملک در اجاره روزانه)</h3>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            مدیریت گزینه‌های انتخابی سلکتور «نوع ملک» در اقامتگاه‌ها. می‌توانید گزینه‌های غیرضروری را غیرفعال کرده یا عنوان نمایشی آن‌ها را ویرایش نمایید.
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="resetDefaultRentalTypes()" class="px-2.5 py-1.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 text-[11px] font-bold dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 transition">
                                        بازنشانی پیش‌فرض
                                    </button>
                                    <button type="button" @click="addRentalType()" class="px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50 text-[11px] font-bold transition flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        افزودن نوع جدید
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-2.5">
                                <template x-for="(typeItem, idx) in rentalPropertyTypes" :key="typeItem.key || idx">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-xl border border-gray-200/70 dark:border-gray-700/60 bg-gray-50/70 dark:bg-gray-900/40 hover:bg-white dark:hover:bg-gray-800 transition">
                                        {{-- فیلدهای فرم مخفی و عنوان --}}
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            {{-- سوییچ فعال/غیرفعال --}}
                                            <label class="flex items-center gap-2 cursor-pointer shrink-0">
                                                <input type="checkbox"
                                                       :name="'rental_property_types[' + typeItem.key + '][is_active]'"
                                                       value="1"
                                                       x-model="typeItem.is_active"
                                                       class="{{ $checkboxClass }}">
                                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full"
                                                      :class="typeItem.is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-400'"
                                                      x-text="typeItem.is_active ? 'فعال' : 'غیرفعال'"></span>
                                            </label>

                                            {{-- کلید سیستمی --}}
                                            <input type="hidden" :name="'rental_property_types[' + typeItem.key + '][key]'" :value="typeItem.key">
                                            
                                            <span class="px-2 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-[10px] font-sans border border-gray-200 dark:border-gray-700 shrink-0"
                                                  title="شناسه فنی ذخیره‌سازی در دیتابیس">
                                                کلید: <span class="font-bold" x-text="typeItem.key"></span>
                                            </span>

                                            {{-- فیلد ویرایش عنوان فارسی --}}
                                            <div class="flex-1 min-w-0">
                                                <input type="text"
                                                       :name="'rental_property_types[' + typeItem.key + '][label]'"
                                                       x-model="typeItem.label"
                                                       class="{{ $inputClass }} text-xs py-2 w-full"
                                                       placeholder="عنوان نمایشی (فارسی)..."
                                                       required>
                                            </div>

                                            {{-- انتخاب آیکون نمایشی --}}
                                            <div class="w-36 shrink-0">
                                                <select :name="'rental_property_types[' + typeItem.key + '][icon]'"
                                                        x-model="typeItem.icon"
                                                        class="{{ $inputClass }} text-[11px] py-1.5 px-2 w-full">
                                                    <option value="">آیکون پیش‌فرض</option>
                                                    <option value="villa">ویلا</option>
                                                    <option value="cottage">کلبه چوبی</option>
                                                    <option value="apartment">آپارتمان و سوئیت</option>
                                                    <option value="ecolodge">بوم‌گردی و سنتی</option>
                                                    <option value="resort">مجتمع اقامتی</option>
                                                    <option value="pool">استخردار</option>
                                                    <option value="beach">ساحلی و لب آب</option>
                                                    <option value="forest">جنگلی</option>
                                                    <option value="mountain">ییلاقی و کوهستانی</option>
                                                    <option value="jacuzzi">جکوزی‌دار</option>
                                                    <option value="luxury">لوکس و ممتاز</option>
                                                    <option value="breakfast">با صبحانه</option>
                                                    <option value="pet">پت‌نواز</option>
                                                </select>
                                            </div>

                                            {{-- تصویر اختیاری --}}
                                            <div class="w-40 shrink-0">
                                                <input type="text"
                                                       :name="'rental_property_types[' + typeItem.key + '][image]'"
                                                       x-model="typeItem.image"
                                                       class="{{ $inputClass }} text-[11px] py-1.5 px-2 w-full"
                                                       placeholder="آدرس آیکون/تصویر...">
                                            </div>
                                        </div>

                                        {{-- دکمه حذف برای موارد اضافه شده توسط کاربر --}}
                                        <div class="flex items-center justify-end gap-2 shrink-0">
                                            <template x-if="!['villa', 'apartment', 'cottage', 'ecolodge', 'resort'].includes(typeItem.key)">
                                                <button type="button" @click="removeRentalType(idx)" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition" title="حذف این مورد">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </template>
                                            <template x-if="['villa', 'apartment', 'cottage', 'ecolodge', 'resort'].includes(typeItem.key)">
                                                <span class="text-[10px] text-slate-400 dark:text-slate-500 px-2 py-1 select-none">پیش‌فرض سیستمی</span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

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

                            <label class="flex items-center gap-3 p-3.5 rounded-xl border border-gray-200/80 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer">
                                <input type="checkbox" name="rental_pending_host_can_create" value="1" {{ ($rental_pending_host_can_create ?? 0) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">امکان ثبت اقامتگاه در وضعیت «در انتظار تأیید مدیریت»</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">در صورت غیرفعال بودن (پیش‌فرض امنیتی)، میزبان تا زمان تایید هویت امکان ثبت اقامتگاه را نخواهد داشت.</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-3.5 rounded-xl border border-gray-200/80 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer">
                                <input type="checkbox" name="rental_redirect_host_to_dashboard" value="1" {{ ($rental_redirect_host_to_dashboard ?? 1) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">هدایت خودکار پیشخوان به «پروفایل میزبانی»</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">هدایت کاربرانی که فقط نقش میزبان دارند به پروفایل میزبانی (کاربران چندنقشی محدود نمی‌شوند)</span>
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
            </div>

            {{-- ======================================================== --}}
            {{-- ۳. بخش قوانین تسویه و نکات میزبانی                     --}}
            {{-- ======================================================== --}}
            <div x-show="rentalSubTab === 'rules_tips'" class="space-y-6">

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

            {{-- ======================================================== --}}
            {{-- کارت اختصاصی: مدیریت متن و آیتم‌های «نکات کلیدی میزبانی موفق» --}}
            {{-- ======================================================== --}}
            <div class="{{ $cardClass }} p-5 sm:p-6 space-y-6 border-indigo-100 dark:border-indigo-900/50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                        </div>
                        <div>
                            <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                                <span>مدیریت متن و آیتم‌های «نکات کلیدی میزبانی موفق»</span>
                                <span class="px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 text-[10px] font-bold">صفحه پروفایل میزبانی</span>
                            </h2>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                آیتم‌های راهنمای نمایش‌داده‌شده در صفحه اصلی پروفایل میزبانی (`/user/properties/hosts/dashboard`) از این بخش مدیریت می‌شود.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button"
                                @click="resetDefaultHostTips()"
                                class="px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 text-slate-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-gray-800 text-[11px] font-bold transition">
                            بازنشانی به پیش‌فرض
                        </button>
                        <button type="button"
                                @click="addHostTip()"
                                class="px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-500/15 dark:text-indigo-300 dark:border dark:border-indigo-500/30 text-[11px] font-bold transition flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            <span>افزودن نکته جدید</span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                    {{-- تیک فعال‌سازی نمایش بخش --}}
                    <label class="flex items-center gap-3 p-3.5 rounded-xl border border-gray-200/80 dark:border-gray-700/60 bg-gray-50/60 dark:bg-gray-900/40 cursor-pointer">
                        <input type="checkbox" name="rental_host_tips_enabled" value="1" x-model="hostTipsEnabled" class="{{ $checkboxClass }}">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200">نمایش بخش «نکات کلیدی میزبانی» در پروفایل میزبانی</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">در صورت برداشتن تیک، این بخش در صفحه پروفایل میزبانی نمایش داده نمی‌شود</span>
                        </div>
                    </label>

                    {{-- فیلد عنوان بخش --}}
                    <div class="space-y-1.5">
                        <label class="{{ $labelClass }}">
                            عنوان اصلی بخش در پروفایل میزبانی:
                        </label>
                        <input type="text"
                               name="rental_host_tips_title"
                               x-model="hostTipsTitle"
                               class="{{ $inputClass }} font-bold text-xs"
                               placeholder="نکات کلیدی میزبانی موفق">
                    </div>
                </div>

                {{-- لیست تکرارکننده آیتم‌های نکات میزبانی --}}
                <div class="space-y-3" :class="!hostTipsEnabled && 'opacity-60'">
                    <template x-for="(tip, idx) in hostTips" :key="idx">
                        <div class="p-4 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200/70 dark:border-gray-700/70 space-y-3 relative transition hover:border-indigo-300 dark:hover:border-indigo-800">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-[10px] font-bold font-sans" x-text="idx + 1"></span>
                                    <span>نکته شماره <span x-text="idx + 1"></span></span>
                                </span>

                                <button type="button"
                                        @click="removeHostTip(idx)"
                                        class="p-1 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 transition"
                                        title="حذف این نکته">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">
                                        عنوان نکته (مثلاً: بروزرسانی تقویم):
                                    </label>
                                    <input type="text"
                                           :name="'rental_host_tips[' + idx + '][title]'"
                                           x-model="tip.title"
                                           class="{{ $inputClass }}"
                                           placeholder="عنوان کوتاه نکته...">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">
                                        متن کامل راهنما:
                                    </label>
                                    <textarea :name="'rental_host_tips[' + idx + '][text]'"
                                              x-model="tip.text"
                                              rows="2"
                                              class="{{ $inputClass }} resize-none"
                                              placeholder="شرح کامل نکته کلیدی برای میزبانان..."></textarea>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            </div>

            {{-- ======================================================== --}}
            {{-- ۴. بخش حریم خصوصی و نمایش جزئیات اقامتگاه                --}}
            {{-- ======================================================== --}}
            <div x-show="rentalSubTab === 'display'" class="space-y-6">

            {{-- ======================================================== --}}
            {{-- کارت اختصاصی: تنظیمات نمایش اقامتگاه، نقشه و تماس مسافر --}}
            {{-- ======================================================== --}}
            <div class="{{ $cardClass }} p-5 sm:p-6 space-y-6 border-teal-100 dark:border-teal-900/50">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        </div>
                        <div>
                            <h2 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                                <span>تنظیمات نمایش صفحه اقامتگاه، نقشه و تماس</span>
                                <span class="px-2 py-0.5 rounded-full bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 text-[10px] font-bold">حریم خصوصی و مسافران</span>
                            </h2>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                تعیین نحوه نمایش باکس معرفی میزبان، موقعیت جغرافیایی و نقشه، و دکمه تماس مستقیم قبل یا بعد از رزرو تأیید شده
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ۱. بخش باکس معرفی میزبان --}}
                <div class="p-4.5 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200/70 dark:border-gray-700/60 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-gray-200/50 dark:border-gray-700/50">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                            <h3 class="text-xs font-black text-gray-900 dark:text-white">۱. باکس معرفی و هویت میزبان</h3>
                        </div>
                        <div class="w-full sm:w-64">
                            <select name="rental_show_host_box" class="{{ $inputClass }} font-bold text-xs">
                                <option value="all" {{ ($rental_show_host_box ?? 'all') === 'all' ? 'selected' : '' }}>نمایش عمومی (برای همه بازدیدکنندگان)</option>
                                <option value="confirmed_booking" {{ ($rental_show_host_box ?? '') === 'confirmed_booking' ? 'selected' : '' }}>فقط پس از رزرو تأیید شده مسافر</option>
                                <option value="none" {{ ($rental_show_host_box ?? '') === 'none' ? 'selected' : '' }}>عدم نمایش (مخفی کامل)</option>
                            </select>
                        </div>
                    </div>

                    {{-- تفکیک اجزای داخل باکس میزبان --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 pt-1">
                        <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-teal-400 transition">
                            <input type="checkbox" name="rental_host_show_avatar" value="1" {{ ($rental_host_show_avatar ?? 1) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                            <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300">عکس آواتار میزبان</span>
                        </label>

                        <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-teal-400 transition">
                            <input type="checkbox" name="rental_host_show_badge" value="1" {{ ($rental_host_show_badge ?? 1) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                            <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300">نشان تأیید هویت (KYC)</span>
                        </label>

                        <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 cursor-pointer hover:border-teal-400 transition">
                            <input type="checkbox" name="rental_host_show_about" value="1" {{ ($rental_host_show_about ?? 1) ? 'checked' : '' }} class="{{ $checkboxClass }}">
                            <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300">متن درباره میزبان (بیو)</span>
                        </label>

                        <div class="flex flex-col">
                            <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 mb-1">شماره تماس در کارت میزبان:</label>
                            <select name="rental_host_show_phone" class="{{ $inputClass }} py-1.5 text-[11px] font-bold">
                                <option value="all" {{ ($rental_host_show_phone ?? '') === 'all' ? 'selected' : '' }}>نمایش عمومی</option>
                                <option value="confirmed_booking" {{ ($rental_host_show_phone ?? 'confirmed_booking') === 'confirmed_booking' ? 'selected' : '' }}>فقط پس از تایید رزرو</option>
                                <option value="none" {{ ($rental_host_show_phone ?? '') === 'none' ? 'selected' : '' }}>عدم نمایش</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ۲. بخش موقعیت جغرافیایی و دسترسی (نقشه و آدرس) --}}
                <div class="p-4.5 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200/70 dark:border-gray-700/60 space-y-4">
                    <div class="flex items-center gap-2 pb-2 border-b border-gray-200/50 dark:border-gray-700/50">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        <h3 class="text-xs font-black text-gray-900 dark:text-white">۲. موقعیت جغرافیایی، آدرس متنی و نقشه</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- نمایش متن آدرس --}}
                        <div class="p-3.5 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 space-y-2">
                            <label class="block text-xs font-bold text-gray-800 dark:text-gray-200">نمایش متن آدرس اقامتگاه</label>
                            <select name="rental_show_address" class="{{ $inputClass }} font-bold text-xs">
                                <option value="all" {{ ($rental_show_address ?? 'all') === 'all' ? 'selected' : '' }}>نمایش عمومی آدرس برای همه</option>
                                <option value="confirmed_booking" {{ ($rental_show_address ?? '') === 'confirmed_booking' ? 'selected' : '' }}>نمایش آدرس دقیق فقط پس از رزرو تأیید شده</option>
                                <option value="none" {{ ($rental_show_address ?? '') === 'none' ? 'selected' : '' }}>عدم نمایش آدرس متنی</option>
                            </select>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block">در صورت انتخاب «پس از رزرو تأیید شده»، برای سایرین پیام راهنما نمایش می‌یابد.</span>
                        </div>

                        {{-- نمایش کلی نقشه --}}
                        <div class="p-3.5 rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/90 space-y-2">
                            <label class="block text-xs font-bold text-gray-800 dark:text-gray-200">نمایش بخش نقشه تعاملی</label>
                            <select name="rental_show_map" class="{{ $inputClass }} font-bold text-xs">
                                <option value="all" {{ ($rental_show_map ?? 'all') === 'all' ? 'selected' : '' }}>نمایش نقشه برای همه بازدیدکنندگان</option>
                                <option value="confirmed_booking" {{ ($rental_show_map ?? '') === 'confirmed_booking' ? 'selected' : '' }}>نمایش نقشه فقط پس از رزرو تأیید شده</option>
                                <option value="none" {{ ($rental_show_map ?? '') === 'none' ? 'selected' : '' }}>عدم نمایش بخش نقشه</option>
                            </select>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block">کنترل فعال یا غیرفعال بودن نمایش کامپوننت نقشه در صفحه اقامتگاه.</span>
                        </div>
                    </div>

                    {{-- نوع نمایش در نقشه و تعیین شعاع دایره امن --}}
                    <div class="p-4 rounded-xl border border-indigo-100 dark:border-indigo-900/40 bg-indigo-50/40 dark:bg-indigo-950/20 space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-indigo-950 dark:text-indigo-200 mb-1.5">
                                    نوع نمایش موقعیت روی نقشه:
                                </label>
                                <select name="rental_map_display_type" class="{{ $inputClass }} font-bold text-xs">
                                    <option value="circle_then_exact" {{ ($rental_map_display_type ?? 'circle_then_exact') === 'circle_then_exact' ? 'selected' : '' }}>محدوده دایره قبل از رزرو ➔ نقطه دقیق بعد از تایید رزرو (پیش‌نهادی)</option>
                                    <option value="circle" {{ ($rental_map_display_type ?? '') === 'circle' ? 'selected' : '' }}>همیشه محدوده دایره تقریبی (حفظ کامل حریم خصوصی)</option>
                                    <option value="exact" {{ ($rental_map_display_type ?? '') === 'exact' ? 'selected' : '' }}>همیشه نقطه دقیق با پین / مارکر</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-indigo-950 dark:text-indigo-200 mb-1.5">
                                    شعاع دایره محدوده اقامتگاه (بر حسب متر):
                                </label>
                                <div class="relative">
                                    <input type="number" 
                                           name="rental_map_circle_radius" 
                                           value="{{ $rental_map_circle_radius ?? 300 }}" 
                                           min="50" 
                                           max="5000" 
                                           step="50" 
                                           class="{{ $inputClass }} text-center font-sans font-bold" 
                                           placeholder="300">
                                    <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-xs text-slate-400 font-sans">متر</span>
                                </div>
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-600 dark:text-slate-300 font-sans leading-relaxed">
                            💡 در حالت <strong>«محدوده دایره قبل از رزرو ➔ نقطه دقیق بعد از تایید رزرو»</strong>، برای عموم کاربران یک دایره بنفش ملایم با شعاع تعیین‌شده رسم می‌شود و پس از آنکه رزرو مسافر تأیید شد (یا برای مدیران پلتفرم)، مارکر و پین دقیق آدرس اقامتگاه به نمایش درمی‌آید.
                        </p>
                    </div>
                </div>

                {{-- ۳. دکمه تماس مستقیم در کارت رزرو --}}
                <div class="p-4.5 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200/70 dark:border-gray-700/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <h3 class="text-xs font-black text-gray-900 dark:text-white">۳. دکمه «تماس مستقیم با میزبان»</h3>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            کنترل نمایش دکمه تماس تلفنی موجود در سایدبار رزرو (زیر دکمه «ثبت درخواست رزرو اقامتگاه»)
                        </p>
                    </div>

                    <div class="w-full sm:w-64 shrink-0">
                        <select name="rental_show_direct_call_btn" class="{{ $inputClass }} font-bold text-xs">
                            <option value="all" {{ ($rental_show_direct_call_btn ?? 'all') === 'all' ? 'selected' : '' }}>نمایش عمومی برای همه</option>
                            <option value="confirmed_booking" {{ ($rental_show_direct_call_btn ?? '') === 'confirmed_booking' ? 'selected' : '' }}>فقط پس از رزرو تأیید شده</option>
                            <option value="none" {{ ($rental_show_direct_call_btn ?? '') === 'none' ? 'selected' : '' }}>عدم نمایش (غیرفعال)</option>
                        </select>
                    </div>
                </div>

            </div>
            </div>

            {{-- ======================================================== --}}
            {{-- ۵. بخش محتوای متنی، معرفی و سئو صفحه اصلی (SEO Content)   --}}
            {{-- ======================================================== --}}
            <div x-show="rentalSubTab === 'seo_content'" class="space-y-6">

                <div class="{{ $cardClass }} p-5 sm:p-6 space-y-6 border-indigo-100 dark:border-indigo-900/50">
                    {{-- هدر کارت سئو --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-700/60">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 shadow-2xs">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-gray-900 dark:text-white">محتوای متنی و بهینه‌سازی سئو (SEO) صفحه اصلی</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    بخش متنی معرفی پلتفرم در انتهای صفحه اصلی (مشابه دیجی‌کالا و جاباما) با قابلیت باز شدن («مشاهده بیشتر») جهت ارتقای رتبه سئو در گوگل.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-start sm:self-auto">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold"
                                  :class="rentalSeoContentEnabled ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700'">
                                <span class="w-2 h-2 rounded-full" :class="rentalSeoContentEnabled ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400'"></span>
                                <span x-text="rentalSeoContentEnabled ? 'نمایش در صفحه اصلی فعال است' : 'نمایش غیرفعال است'"></span>
                            </span>
                        </div>
                    </div>

                    {{-- سوئیچ فعال / غیرفعال --}}
                    <div class="p-4 rounded-xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200/70 dark:border-gray-700/60 flex items-center justify-between gap-4">
                        <div class="space-y-0.5">
                            <label for="toggle-rental-seo-enabled" class="text-xs font-bold text-gray-900 dark:text-white cursor-pointer">
                                فعال‌سازی بلوک سئو و معرفی در انتهای صفحه اصلی
                            </label>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                در صورت فعال بودن، عنوان و متن زیر در پایین‌ترین بخش محتوایی صفحه اصلی (بدون باکس) نمایش داده می‌شود.
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" 
                                   id="toggle-rental-seo-enabled"
                                   name="rental_seo_content_enabled" 
                                   value="1" 
                                   x-model="rentalSeoContentEnabled" 
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    {{-- فیلدهای عنوان و ویرایشگر پیشرفته Quill --}}
                    <div class="space-y-4">
                        {{-- عنوان هدینگ --}}
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-800 dark:text-gray-200">
                                    عنوان معرفی و تیتر سئو (تگ H2):
                                </label>
                                <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold">بسیار مهم برای موتورهای جستجو</span>
                            </div>
                            <input type="text" 
                                   name="rental_seo_content_title" 
                                   x-model="rentalSeoContentTitle" 
                                   class="{{ $inputClass }} font-bold text-xs sm:text-sm" 
                                   placeholder="مثال: مستر ویلا؛ سامانه رزرواسیون و اجاره آنلاین ویلا و اقامتگاه در شمال">
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-sans">
                                💡 توصیه می‌شود در عنوان از نام برند، کلمات کلیدی نظیر «اجاره ویلا»، «کلبه»، «رزرو اقامتگاه» و منطقه جغرافیایی هدف استفاده نمایید.
                            </p>
                        </div>

                        {{-- ویرایشگر حرفه‌ای Quill (مشابه ماژول‌های Market و ContentForge) --}}
                        <div class="space-y-1.5"
                             x-data="{
                                 _quillInstance: null,
                                 initQuillEditor() {
                                     const setup = () => {
                                         if (typeof Quill === 'undefined') {
                                             setTimeout(setup, 150);
                                             return;
                                         }
                                         if (this._quillInstance) return;

                                         const quill = new Quill(this.$refs.quillEditor, {
                                             theme: 'snow',
                                             placeholder: 'متن معرفی، خدمات، توضیحات سئو، هدینگ‌ها و کلمات کلیدی را اینجا بنویسید...',
                                             modules: {
                                                 toolbar: [
                                                     [{ 'header': [1, 2, 3, 4, false] }],
                                                     ['bold', 'italic', 'underline', 'strike'],
                                                     [{ 'color': [] }, { 'background': [] }],
                                                     [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                                     [{ 'align': [] }, { 'direction': 'rtl' }],
                                                     ['link'],
                                                     ['clean']
                                                 ]
                                             }
                                         });

                                         this._quillInstance = quill;

                                         quill.on('text-change', () => {
                                             this.rentalSeoContentBody = quill.root.innerHTML;
                                             if (this.$refs.hiddenSeoBody) {
                                                 this.$refs.hiddenSeoBody.value = this.rentalSeoContentBody;
                                             }
                                         });

                                         if (this.rentalSeoContentBody) {
                                             quill.root.innerHTML = this.rentalSeoContentBody;
                                         }

                                         this.$watch('rentalSubTab', (tab) => {
                                             if (tab === 'seo_content' && this._quillInstance) {
                                                 setTimeout(() => this._quillInstance.update(), 50);
                                             }
                                         });
                                     };

                                     setup();
                                 }
                             }"
                             x-init="initQuillEditor()">
                            
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-800 dark:text-gray-200">
                                    متن کامل محتوا و سئو (با امکان تنظیم تگ‌ها، هدینگ‌ها و لیست‌ها):
                                </label>
                                <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold">ویرایشگر پیشرفته Quill</span>
                            </div>

                            <input type="hidden" name="rental_seo_content_body" x-ref="hiddenSeoBody" :value="rentalSeoContentBody">

                            <div class="custom-quill-wrapper border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-white dark:bg-gray-800/90 transition-all focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500 shadow-2xs font-sans">
                                <div x-ref="quillEditor" class="focus:outline-none"></div>
                            </div>
                            
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-sans">
                                💡 با استفاده از نوار ابزار بالا می‌توانید تگ‌های ساختاریافته (H1, H2, H3)، لینک‌ها، لیست‌های نشانه‌دار و استایل متنی دلخواه خود را برای سئوی حداکثری تنظیم فرمایید.
                            </p>
                        </div>
                    </div>

                    {{-- کارت پیش‌نمایش زنده در همین تب (کاملاً تخت و بدون باکس، مطابق دیجی‌کالا) --}}
                    <div class="p-4 sm:p-5 rounded-2xl bg-indigo-50/30 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/40 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-indigo-100 dark:border-indigo-900/30">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                <h4 class="text-xs font-black text-indigo-950 dark:text-indigo-200">پیش‌نمایش در صفحه اصلی (بدون باکس و تخت - استایل دیجی‌کالا):</h4>
                            </div>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-sans">فقط مشاهده بیشتر (بدون مشاهده کمتر)</span>
                        </div>

                        <div class="py-2 space-y-2 font-sans">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-zinc-100" 
                                x-text="rentalSeoContentTitle ? rentalSeoContentTitle : 'مستر ویلا؛ سامانه رزرواسیون و اجاره آنلاین ویلا و اقامتگاه در شمال'"></h4>

                            <div class="relative overflow-hidden transition-all duration-300"
                                 :class="seoPreviewExpanded ? 'max-h-none' : 'max-h-16 sm:max-h-20'">
                                <div class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed sm:leading-7 text-justify font-sans [&_p]:mb-1 [&_h1]:text-sm [&_h2]:text-xs [&_h3]:text-xs [&_ul]:list-disc [&_ul]:mr-4 [&_ol]:list-decimal [&_ol]:mr-4"
                                     x-html="rentalSeoContentBody ? rentalSeoContentBody : 'متن معرفی خالی است...'"></div>

                                {{-- افکت گرادیان محوشدگی در پیش‌نمایش --}}
                                <div x-show="!seoPreviewExpanded"
                                     class="absolute bottom-0 inset-x-0 h-10 bg-gradient-to-t from-indigo-50/40 via-indigo-50/20 to-transparent dark:from-gray-900 dark:via-gray-900/60 pointer-events-none"></div>
                            </div>

                            <div x-show="!seoPreviewExpanded" class="flex items-center justify-start pt-1">
                                <button type="button" 
                                        @click="seoPreviewExpanded = true"
                                        class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-600 hover:text-sky-700 dark:text-sky-400 transition cursor-pointer no-underline hover:no-underline">
                                    <span>مشاهده بیشتر</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                    </svg>
                                </button>
                            </div>
                        </div>
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

                    {{-- بخش مرزبندی جغرافیایی نقشه و محدودسازی استانی --}}
                    <div class="bg-gray-50/70 dark:bg-gray-900/40 rounded-2xl p-4 sm:p-5 border border-gray-100 dark:border-gray-700/50 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-200/60 dark:border-gray-700/60 pb-3">
                            <div>
                                <h3 class="text-xs sm:text-sm font-black text-gray-900 dark:text-white flex items-center gap-2 font-sans">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    محدوده مجاز جغرافیایی نقشه‌ها (مرزهای ملی و استانی)
                                </h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 font-sans">
                                    محدود کردن کادر پَن و زوم نقشه به مرزهای ایران یا استان‌های فعال جهت بهینه‌سازی بارگذاری تایل‌ها و کاهش مصرف ترافیک
                                </p>
                            </div>
                            <span class="text-[11px] bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 px-2.5 py-0.5 rounded-full font-bold border border-emerald-200/60 dark:border-emerald-800/40 font-sans shrink-0">
                                بهینه‌سازی منابع
                            </span>
                        </div>

                        {{-- انتخاب دامنه: کل کشور یا استان‌های منتخب --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 cursor-pointer transition hover:border-indigo-500 select-none">
                                <div class="relative flex items-center">
                                    <input type="radio" name="map_boundary_scope" value="iran" x-model="mapBoundaryScope" class="peer sr-only">
                                    <div class="w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-full peer-checked:border-indigo-600 peer-checked:bg-indigo-600 transition-all"></div>
                                    <div class="absolute inset-0 m-auto w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block font-sans">سراسر کشور (ایران)</span>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 block mt-0.5 font-sans">محدود کردن نقشه به مرزهای کشور ایران (پیش‌فرض سیستم)</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 cursor-pointer transition hover:border-indigo-500 select-none">
                                <div class="relative flex items-center">
                                    <input type="radio" name="map_boundary_scope" value="custom_provinces" x-model="mapBoundaryScope" class="peer sr-only">
                                    <div class="w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-full peer-checked:border-indigo-600 peer-checked:bg-indigo-600 transition-all"></div>
                                    <div class="absolute inset-0 m-auto w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block font-sans">محدود به استان‌های منتخب (چند استانه)</span>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 block mt-0.5 font-sans">قفل کردن کادر جابجایی نقشه روی یک یا چند استان مشخص</span>
                                </div>
                            </label>
                        </div>

                        {{-- بخش انتخاب استان‌ها (تنها در حالت custom_provinces) --}}
                        <div x-show="mapBoundaryScope === 'custom_provinces'" x-transition class="space-y-3 pt-2">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                                <label class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5 font-sans">
                                    <span>انتخاب استان‌های مجاز پلتفرم:</span>
                                    <span class="text-[11px] text-indigo-600 dark:text-indigo-400 font-bold font-sans bg-indigo-50 dark:bg-indigo-950/40 px-2 py-0.5 rounded-md border border-indigo-200/60 dark:border-indigo-800/40" x-text="`${mapSelectedProvinces.length} استان انتخاب‌شده`"></span>
                                </label>
                                <div class="relative w-full sm:w-64">
                                    <input type="text" x-model="provinceSearch" placeholder="جستجوی سریع استان..." class="{{ $inputClass }} text-xs py-1.5 font-sans">
                                </div>
                            </div>

                            {{-- چیپ‌های استان‌ها --}}
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 max-h-56 overflow-y-auto p-2.5 bg-white dark:bg-gray-800/80 rounded-xl border border-gray-200 dark:border-gray-700">
                                <template x-for="prov in filteredProvinces" :key="prov">
                                    <label class="flex items-center gap-2 p-2 rounded-lg border text-xs cursor-pointer select-none transition"
                                           :class="mapSelectedProvinces.includes(prov) 
                                               ? 'border-indigo-500 bg-indigo-50/70 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-200 font-bold' 
                                               : 'border-gray-100 dark:border-gray-700/80 hover:bg-gray-50 dark:hover:bg-gray-700/50 text-gray-700 dark:text-gray-300'">
                                        <input type="checkbox" name="map_selected_provinces[]" :value="prov"
                                               :checked="mapSelectedProvinces.includes(prov)"
                                               @change="toggleProvince(prov)"
                                               class="w-3.5 h-3.5 rounded text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600">
                                        <span x-text="prov" class="truncate font-sans"></span>
                                    </label>
                                </template>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-sans">
                                * در صورت عدم انتخاب هیچ استانی، نقشه به صورت خودکار به مرزهای سراسر کشور ایران محدود می‌گردد.
                            </p>
                        </div>
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
            rentalSubTab: config.rentalSubTab || 'slider',
            rentalHeroBgImage: config.rentalHeroBgImage || null,
            rentalHeroBgPreview: config.rentalHeroBgPreview || null,
            deleteRentalHeroBg: Boolean(config.deleteRentalHeroBg),
            rentalHeroMobileBgImage: config.rentalHeroMobileBgImage || null,
            rentalHeroMobileBgPreview: config.rentalHeroMobileBgPreview || null,
            deleteRentalHeroMobileBg: Boolean(config.deleteRentalHeroMobileBg),
            rentalHeroValign: config.rentalHeroValign || 'center',
            rentalHeroBgPosition: config.rentalHeroBgPosition || 'center',
            rentalHeroMobileBgPosition: config.rentalHeroMobileBgPosition || 'center',
            rentalSeoContentEnabled: Boolean(config.rentalSeoContentEnabled ?? true),
            rentalSeoContentTitle: config.rentalSeoContentTitle || '',
            rentalSeoContentBody: config.rentalSeoContentBody || '',
            seoPreviewExpanded: false,
            heroSlides: (config.heroSlides && Array.isArray(config.heroSlides)) ? config.heroSlides.map((s, idx) => ({
                id: s.id || ('slide_' + Date.now() + '_' + idx),
                image: s.image || '',
                preview: null,
                title: s.title || '',
                link: s.link || '',
                target: s.target || '_self',
                is_active: typeof s.is_active !== 'undefined' ? Boolean(s.is_active) : true,
                order: typeof s.order !== 'undefined' ? s.order : idx
            })) : [],
            rentalEnabled: config.rentalEnabled,
            rentalVideoEnabled: config.rentalVideoEnabled ?? true,
            rentalBuildingEnabled: config.rentalBuildingEnabled ?? true,
            rentalPropertyTypes: config.rentalPropertyTypes && config.rentalPropertyTypes.length > 0 ? config.rentalPropertyTypes : [
                { key: 'villa', label: 'ویلا', is_active: true },
                { key: 'apartment', label: 'آپارتمان و سوئیت', is_active: true },
                { key: 'cottage', label: 'کلبه و اقامتگاه چوبی', is_active: true },
                { key: 'ecolodge', label: 'بوم‌گردی و سنتی', is_active: true },
                { key: 'resort', label: 'مجتمع اقامتی و هتل‌آپارتمان', is_active: true }
            ],
            multiHostEnabled: config.multiHostEnabled,
            mapService: config.mapService,
            officeLat: config.officeLat,
            officeLng: config.officeLng,
            mapBoundaryScope: config.mapBoundaryScope || 'iran',
            mapSelectedProvinces: Array.isArray(config.mapSelectedProvinces) ? config.mapSelectedProvinces : [],
            mapAllProvinces: config.mapAllProvinces || [],
            mapBoundsConfig: config.mapBoundsConfig || {},
            provinceSearch: '',
            toggleProvince(prov) {
                if (this.mapSelectedProvinces.includes(prov)) {
                    this.mapSelectedProvinces = this.mapSelectedProvinces.filter(p => p !== prov);
                } else {
                    this.mapSelectedProvinces.push(prov);
                }
            },
            get filteredProvinces() {
                if (!this.provinceSearch || !this.provinceSearch.trim()) {
                    return this.mapAllProvinces || [];
                }
                const q = this.provinceSearch.trim();
                return (this.mapAllProvinces || []).filter(p => p.includes(q));
            },
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
            hostTipsEnabled: config.hostTipsEnabled ?? true,
            hostTipsTitle: config.hostTipsTitle || 'نکات کلیدی میزبانی موفق',
            hostTips: config.hostTips && config.hostTips.length > 0 ? config.hostTips : [
                {
                    title: 'بروزرسانی تقویم',
                    text: 'روزهایی که اقامتگاه خارج از پلتفرم پر شده است را از بخش «مدیریت تقویم» مسدود نمایید تا از تداخل رزرو جلوگیری شود.'
                },
                {
                    title: 'نرخ‌گذاری فصلی و پیک',
                    text: 'تعیین نرخ دقیق آخر هفته و ایام پیک باعث افزایش رتبه نمایش اقامتگاه شما در نتایج جستجو می‌شود.'
                },
                {
                    title: 'ویرایش مدارک هویتی',
                    text: 'در صورت تغییر اطلاعات احراز هویت پس از تأیید، وضعیت مدارک مجدداً جهت بررسی امنیتی به حالت «در انتظار بررسی» تغییر می‌یابد.'
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

            addSlide() {
                this.heroSlides.push({
                    id: 'slide_' + Date.now(),
                    image: '',
                    preview: null,
                    title: '',
                    link: '',
                    target: '_self',
                    is_active: true,
                    order: this.heroSlides.length
                });
            },

            removeSlide(idx) {
                if (confirm('آیا از حذف این اسلاید اطمینان دارید؟')) {
                    this.heroSlides.splice(idx, 1);
                    this.heroSlides.forEach((s, i) => s.order = i);
                }
            },

            moveSlide(idx, direction) {
                const targetIdx = idx + direction;
                if (targetIdx < 0 || targetIdx >= this.heroSlides.length) return;
                const temp = this.heroSlides[idx];
                this.heroSlides[idx] = this.heroSlides[targetIdx];
                this.heroSlides[targetIdx] = temp;
                this.heroSlides.forEach((s, i) => s.order = i);
            },

            handleImageSelect(event, idx) {
                const file = event.target.files[0];
                if (file) {
                    this.heroSlides[idx].preview = URL.createObjectURL(file);
                }
            },

            handleHeroBgSelect(event) {
                const file = event.target.files[0];
                if (file) {
                    this.rentalHeroBgPreview = URL.createObjectURL(file);
                    this.deleteRentalHeroBg = false;
                }
            },

            removeHeroBg() {
                this.deleteRentalHeroBg = true;
                this.rentalHeroBgPreview = null;
                if (this.$refs.heroBgFileInput) {
                    this.$refs.heroBgFileInput.value = '';
                }
            },

            undoDeleteHeroBg() {
                this.deleteRentalHeroBg = false;
                this.rentalHeroBgPreview = this.rentalHeroBgImage ? ('/' + this.rentalHeroBgImage.replace(/^\/+/, '')) : null;
            },

            handleHeroMobileBgSelect(event) {
                const file = event.target.files[0];
                if (file) {
                    this.rentalHeroMobileBgPreview = URL.createObjectURL(file);
                    this.deleteRentalHeroMobileBg = false;
                }
            },

            removeHeroMobileBg() {
                this.deleteRentalHeroMobileBg = true;
                this.rentalHeroMobileBgPreview = null;
                if (this.$refs.heroMobileBgFileInput) {
                    this.$refs.heroMobileBgFileInput.value = '';
                }
            },

            undoDeleteHeroMobileBg() {
                this.deleteRentalHeroMobileBg = false;
                this.rentalHeroMobileBgPreview = this.rentalHeroMobileBgImage ? ('/' + this.rentalHeroMobileBgImage.replace(/^\/+/, '')) : null;
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

            addHostTip() {
                this.hostTips.push({
                    title: '',
                    text: ''
                });
            },

            removeHostTip(idx) {
                if (this.hostTips.length > 1) {
                    this.hostTips.splice(idx, 1);
                } else {
                    this.hostTips = [{ title: '', text: '' }];
                }
            },

            resetDefaultHostTips() {
                this.hostTipsEnabled = true;
                this.hostTipsTitle = 'نکات کلیدی میزبانی موفق';
                this.hostTips = [
                    {
                        title: 'بروزرسانی تقویم',
                        text: 'روزهایی که اقامتگاه خارج از پلتفرم پر شده است را از بخش «مدیریت تقویم» مسدود نمایید تا از تداخل رزرو جلوگیری شود.'
                    },
                    {
                        title: 'نرخ‌گذاری فصلی و پیک',
                        text: 'تعیین نرخ دقیق آخر هفته و ایام پیک باعث افزایش رتبه نمایش اقامتگاه شما در نتایج جستجو می‌شود.'
                    },
                    {
                        title: 'ویرایش مدارک هویتی',
                        text: 'در صورت تغییر اطلاعات احراز هویت پس از تأیید، وضعیت مدارک مجدداً جهت بررسی امنیتی به حالت «در انتظار بررسی» تغییر می‌یابد.'
                    }
                ];
            },

            addRentalType() {
                const uniqueKey = 'type_' + Date.now().toString().slice(-4);
                this.rentalPropertyTypes.push({
                    key: uniqueKey,
                    label: '',
                    icon: 'villa',
                    image: '',
                    is_active: true
                });
            },

            removeRentalType(idx) {
                if (this.rentalPropertyTypes.length > 1) {
                    this.rentalPropertyTypes.splice(idx, 1);
                }
            },

            resetDefaultRentalTypes() {
                this.rentalPropertyTypes = [
                    { key: 'villa', label: 'ویلا', icon: 'villa', image: '', is_active: true },
                    { key: 'apartment', label: 'آپارتمان و سوئیت', icon: 'apartment', image: '', is_active: true },
                    { key: 'cottage', label: 'کلبه و اقامتگاه چوبی', icon: 'cottage', image: '', is_active: true },
                    { key: 'ecolodge', label: 'بوم‌گردی و سنتی', icon: 'ecolodge', image: '', is_active: true },
                    { key: 'resort', label: 'مجتمع اقامتی و هتل‌آپارتمان', icon: 'resort', image: '', is_active: true }
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

                    const mapOptions = {
                        maxBounds: this.mapBoundsConfig?.bounds || [[25.0, 44.0], [39.8, 63.5]],
                        maxBoundsViscosity: 1.0,
                        minZoom: this.mapBoundsConfig?.minZoom || 5,
                    };

                    this.mapInstance = L.map('office-map', mapOptions).setView([parseFloat(this.officeLat), parseFloat(this.officeLng)], 13);
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
