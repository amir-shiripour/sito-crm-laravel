@extends('layouts.admin')
@include('partials.jalali-date-picker')

@section('title', 'تنظیمات سیستم')

@php
    // استایل‌های مشترک
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-200 hover:shadow-md";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/30 rounded-t-2xl";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $selectClass = $inputClass . " appearance-none cursor-pointer";

    // دیکود کردن تنظیمات رنگی برای قالب‌های مختلف
    $savedColors = isset($settings['theme_colors']) ? (is_string($settings['theme_colors']) ? json_decode($settings['theme_colors'], true) : $settings['theme_colors']) : [];

    // رنگ‌های پیش‌فرض هر قالب
    $themeDefaults = [
        'default' => 'indigo', 'market' => 'orange', 'booking' => 'teal', 'properties' => 'blue'
    ];

    // پالت رنگ‌های قابل انتخاب (Tailwind Colors)
    $tailwindColors = [
        'indigo' => '#4f46e5', 'blue' => '#2563eb', 'sky' => '#0ea5e9', 'cyan' => '#06b6d4',
        'teal' => '#0d9488', 'emerald' => '#10b981', 'green' => '#16a34a', 'lime' => '#65a30d',
        'orange' => '#ea580c', 'amber' => '#d97706', 'red' => '#dc2626', 'rose' => '#e11d48',
        'purple' => '#9333ea', 'fuchsia' => '#c026d3', 'pink' => '#db2777', 'slate' => '#475569'
    ];

 $pos_devices = isset($settings['pos_devices'])
    ? (is_string($settings['pos_devices'])
        ? json_decode($settings['pos_devices'], true)
        : $settings['pos_devices'])
    : [];

 $bank_transfer_accounts = isset($settings['bank_transfer_accounts'])
    ? (is_string($settings['bank_transfer_accounts'])
        ? json_decode($settings['bank_transfer_accounts'], true)
        : $settings['bank_transfer_accounts'])
    : [];
@endphp

@section('content')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- هدر صفحه --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span
                        class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </span>
                    تنظیمات کلی سیستم
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-14 max-w-2xl leading-relaxed">
                    پیکربندی اطلاعات پایه سایت، لوگو، اطلاعات تماس و سایر تنظیمات عمومی.
                </p>
            </div>
        </div>

        @if(session('success'))
            <div
                class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-900/10 dark:border-emerald-800/30 text-emerald-700 dark:text-emerald-400 text-sm font-medium flex items-center gap-3 animate-in fade-in slide-in-from-top-2 shadow-sm">
                <div
                    class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-800/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div
                class="rounded-2xl bg-red-50 p-4 border border-red-100 dark:bg-red-900/10 dark:border-red-800/30 text-red-700 dark:text-red-400 text-sm font-medium flex items-center gap-3 animate-in fade-in slide-in-from-top-2 shadow-sm">
                <div
                    class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-800/30 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data"
              class="pb-24" id="main-settings-form">
            @csrf
            
            <div x-data="{ activeTab: window.location.hash ? window.location.hash.substring(1) : 'general' }" 
                 x-init="$watch('activeTab', value => window.location.hash = value)"
                 class="space-y-6">
                 
                 <!-- Horizontal Tabs -->
                 <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl border border-gray-200 dark:border-gray-700 rounded-2xl p-2 shadow-sm flex flex-wrap gap-2">
                     <button type="button" @click="activeTab = 'general'" :class="activeTab === 'general' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400 font-bold shadow-sm' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800/50'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-200 text-sm">
                         <div :class="activeTab === 'general' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-800/50 dark:text-indigo-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'" class="p-1 rounded-lg transition-colors">
                             <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                         </div>
                         اطلاعات پایه و ظاهر
                     </button>
                     <button type="button" @click="activeTab = 'contact'" :class="activeTab === 'contact' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 font-bold shadow-sm' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800/50'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-200 text-sm">
                         <div :class="activeTab === 'contact' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-800/50 dark:text-emerald-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'" class="p-1 rounded-lg transition-colors">
                             <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                         </div>
                         ارتباطات
                     </button>
                     <button type="button" @click="activeTab = 'registration'" :class="activeTab === 'registration' ? 'bg-sky-50 text-sky-700 dark:bg-sky-900/40 dark:text-sky-400 font-bold shadow-sm' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800/50'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-200 text-sm">
                         <div :class="activeTab === 'registration' ? 'bg-sky-100 text-sky-600 dark:bg-sky-800/50 dark:text-sky-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'" class="p-1 rounded-lg transition-colors">
                             <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                         </div>
                         کاربران و ثبت‌نام
                     </button>
                     <button type="button" @click="activeTab = 'ai'" :class="activeTab === 'ai' ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-400 font-bold shadow-sm' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800/50'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-200 text-sm">
                         <div :class="activeTab === 'ai' ? 'bg-purple-100 text-purple-600 dark:bg-purple-800/50 dark:text-purple-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'" class="p-1 rounded-lg transition-colors">
                             <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                         </div>
                         هوش مصنوعی
                     </button>
                     <button type="button" @click="activeTab = 'payment'" :class="activeTab === 'payment' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400 font-bold shadow-sm' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800/50'" class="flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-200 text-sm">
                         <div :class="activeTab === 'payment' ? 'bg-blue-100 text-blue-600 dark:bg-blue-800/50 dark:text-blue-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'" class="p-1 rounded-lg transition-colors">
                             <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                         </div>
                         مالی و پرداخت
                     </button>
                 </div>

                 <!-- Main Content Panels -->
                 <div class="w-full relative">
                 
                 {{-- تب عمومی: اطلاعات پایه و پالت رنگی --}}
                 <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
            {{-- کارت ۱: اطلاعات پایه --}}
            <div class="{{ $cardClass }}">
                <div class="{{ $headerClass }}">
                    <div
                        class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">اطلاعات پایه</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">نام و نشان تجاری سایت</p>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="app_name" class="{{ $labelClass }}">عنوان سایت</label>
                        <input type="text" class="{{ $inputClass }}" id="app_name" name="app_name"
                               value="{{ $settings['app_name'] ?? config('app.name') }}">
                    </div>

                    <div>
                        <label for="app_theme" class="{{ $labelClass }}">قالب اصلی سایت (Theme)</label>
                        <div class="select-wrapper relative">
                            <select class="{{ $selectClass }} !pl-10" id="app_theme" name="app_theme">
                                <option
                                    value="default" {{ ($settings['app_theme'] ?? 'default') == 'default' ? 'selected' : '' }}>
                                    پیش‌فرض (شرکتی/خدماتی)
                                </option>
                                <option value="booking" {{ ($settings['app_theme'] ?? '') == 'booking' ? 'selected' : '' }}>
                                    کلینیک درمانی و پزشکی (Booking)
                                </option>
                                <option value="market" {{ ($settings['app_theme'] ?? '') == 'market' ? 'selected' : '' }}>
                                    فروشگاهی (Market)
                                </option>
                                <option
                                    value="properties" {{ ($settings['app_theme'] ?? '') == 'properties' ? 'selected' : '' }}>
                                    املاک (Properties)
                                </option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500 dark:text-gray-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" /></svg>
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">با تغییر این گزینه، صفحه اول و استایل‌های سایت تغییر
                            خواهد کرد.</p>
                    </div>

                    <div>
                        <label for="site_display_type" class="{{ $labelClass }}">قالب نمایش سایت</label>
                        <div class="select-wrapper relative">
                            <select class="{{ $selectClass }} !pl-10" id="site_display_type" name="site_display_type">
                                <option
                                    value="landing" {{ ($settings['site_display_type'] ?? 'landing') == 'landing' ? 'selected' : '' }}>
                                    لندینگ پیج
                                </option>
                                <option
                                    value="theme" {{ ($settings['site_display_type'] ?? '') == 'theme' ? 'selected' : '' }}>
                                    صفحه اصلی تم
                                </option>
                                <option
                                    value="admin" {{ ($settings['site_display_type'] ?? '') == 'admin' ? 'selected' : '' }}>
                                    پنل مدیران
                                </option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500 dark:text-gray-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" /></svg>
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">نحوه نمایش صفحه اول سایت را انتخاب کنید.</p>
                    </div>

                    <div>
                        <label for="app_logo" class="{{ $labelClass }}">لوگو</label>
                        <div class="flex items-center gap-4">
                            <div class="relative flex-1">
                                <input type="file" class="hidden" id="app_logo" name="app_logo"
                                       onchange="document.getElementById('logo-preview').src = window.URL.createObjectURL(this.files[0])">
                                <label for="app_logo"
                                       class="flex items-center justify-center w-full px-4 py-2.5 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-500 transition-colors bg-gray-50 dark:bg-gray-900/50 text-sm text-gray-500 dark:text-gray-400">
                                    <svg class="w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    انتخاب فایل...
                                </label>
                            </div>
                            @if(isset($settings['app_logo']))
                                <div
                                    class="w-12 h-12 rounded-lg border border-gray-200 dark:border-gray-700 p-1 bg-white flex items-center justify-center">
                                    <img id="logo-preview" src="{{ asset($settings['app_logo']) }}" alt="Logo"
                                         class="max-w-full max-h-full">
                                </div>
                            @else
                                <div
                                    class="w-12 h-12 rounded-lg border border-gray-200 dark:border-gray-700 p-1 bg-white flex items-center justify-center">
                                    <img id="logo-preview" src="" alt="" class="max-w-full max-h-full hidden">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="footer_text" class="{{ $labelClass }}">متن فوتر</label>
                        <input type="text" class="{{ $inputClass }}" id="footer_text" name="footer_text"
                               value="{{ $settings['footer_text'] ?? '' }}">
                    </div>
                </div>
            </div>
            <div class="{{ $cardClass }}">
                <div class="{{ $headerClass }}">
                    <div
                        class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-900/20 flex items-center justify-center text-rose-600 dark:text-rose-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">شخصی‌سازی پالت رنگی</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">رنگ اصلی قالبی که در بالا انتخاب
                            کرده‌اید را تعیین کنید.</p>
                    </div>
                </div>

                <div class="p-6 bg-gray-50/30 dark:bg-gray-800/20">
                    @foreach($themeDefaults as $themeKey => $defaultColorName)
                        <div
                            class="theme-color-panel {{ ($settings['app_theme'] ?? 'default') === $themeKey ? 'block animate-in fade-in duration-300' : 'hidden' }}"
                            id="panel-color-{{ $themeKey }}">

                            <div
                                class="mb-4 flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 font-bold border-b border-gray-200 dark:border-gray-700 pb-3">
                                انتخاب رنگ برای قالب: <span
                                    class="px-2 py-1 bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 text-indigo-600 dark:text-indigo-400 uppercase tracking-wider text-xs">{{ $themeKey }}</span>
                            </div>

                            <div class="flex flex-wrap gap-4">
                                @foreach($tailwindColors as $colorName => $hex)
                                    @php
                                        $activeColor = $savedColors[$themeKey] ?? $defaultColorName;
                                        $isChecked = $activeColor === $colorName;
                                    @endphp
                                    <label class="cursor-pointer relative group" title="{{ ucfirst($colorName) }}">
                                        <input type="radio" name="theme_colors[{{ $themeKey }}]"
                                               value="{{ $colorName }}"
                                               class="peer sr-only" {{ $isChecked ? 'checked' : '' }}>

                                        <div
                                            class="w-10 h-10 rounded-full border-2 peer-checked:scale-110 peer-checked:ring-2 peer-checked:ring-offset-2 dark:peer-checked:ring-offset-gray-900 transition-all shadow-sm flex items-center justify-center"
                                            style="background-color: {{ $hex }}; border-color: transparent; --tw-ring-color: {{ $hex }};">
                                            <svg
                                                class="w-5 h-5 text-white opacity-0 peer-checked:opacity-100 transition-opacity drop-shadow-md"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                      d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div
                        class="mt-6 bg-blue-50/50 border border-blue-100 dark:bg-blue-900/10 dark:border-blue-800/30 p-4 rounded-xl">
                        <p class="text-sm text-blue-700 dark:text-blue-400 leading-relaxed font-medium">
                            <span class="font-bold">راهنما:</span> با انتخاب هر رنگ، تمام سیستم (شامل دکمه‌ها،
                            پس‌زمینه‌ها، سایه‌ها، هاورها و متون) به صورت خودکار با طیف‌های استاندارد آن رنگ هماهنگ
                            می‌شود. رنگ‌ها برای هر قالب به‌صورت مجزا ذخیره می‌شوند.
                        </p>
                    </div>
                </div>
            </div>
            
            </div> {{-- پایان تب عمومی --}}

            {{-- تب ارتباطات --}}
            <div x-show="activeTab === 'contact'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
            <div class="{{ $cardClass }}">
                <div class="{{ $headerClass }}">
                    <div
                        class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">اطلاعات تماس</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">راه‌های ارتباطی نمایش داده شده در
                            سایت</p>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="contact_email" class="{{ $labelClass }}">ایمیل تماس</label>
                        <input type="email" class="{{ $inputClass }} dir-ltr text-left" id="contact_email"
                               name="contact_email" value="{{ $settings['contact_email'] ?? '' }}">
                    </div>

                    <div>
                        <label for="contact_phone" class="{{ $labelClass }}">شماره تماس</label>
                        <input type="text" class="{{ $inputClass }} dir-ltr text-left" id="contact_phone"
                               name="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}">
                    </div>

                    <div class="md:col-span-2">
                        <label for="address" class="{{ $labelClass }}">آدرس</label>
                        <textarea class="{{ $inputClass }}" id="address" name="address"
                                  rows="3">{{ $settings['address'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
            
            </div> {{-- پایان تب ارتباطات --}}

            {{-- تب کاربران و ثبت نام --}}
            <div x-show="activeTab === 'registration'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
            <div class="{{ $cardClass }}">
                <div class="{{ $headerClass }}">
                    <div
                        class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-900/20 flex items-center justify-center text-sky-600 dark:text-sky-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">تنظیمات ثبت نام</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پیکربندی فرم‌های ثبت‌نام برای نقش‌های
                            مختلف</p>
                    </div>
                </div>
                <div class="p-6 space-y-6">
                    @php
                        $roles = \Spatie\Permission\Models\Role::all();
                        $registrationSettings = $settings['registration'] ?? [];
                    @endphp
                    @foreach($roles as $role)
                        <div
                            class="p-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                            <h3 class="font-bold text-gray-800 dark:text-gray-200 mb-4">{{ $role->name }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="registration_{{ $role->id }}_enabled" class="{{ $labelClass }}">وضعیت
                                        ثبت نام</label>
                                    <div class="select-wrapper relative">
                                        <select name="registration[{{ $role->id }}][enabled]"
                                                id="registration_{{ $role->id }}_enabled" class="{{ $selectClass }} !pl-10">
                                            <option value="0"
                                                    @if(!($registrationSettings[$role->id]['enabled'] ?? false)) selected @endif>
                                                غیرفعال
                                            </option>
                                            <option value="1"
                                                    @if($registrationSettings[$role->id]['enabled'] ?? false) selected @endif>
                                                فعال
                                            </option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500 dark:text-gray-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" /></svg>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="registration_{{ $role->id }}_approval" class="{{ $labelClass }}">نوع
                                        تایید</label>
                                    <div class="select-wrapper relative">
                                        <select name="registration[{{ $role->id }}][approval]"
                                                id="registration_{{ $role->id }}_approval" class="{{ $selectClass }} !pl-10">
                                            <option value="manual"
                                                    @if(($registrationSettings[$role->id]['approval'] ?? 'manual') == 'manual') selected @endif>
                                                تایید دستی
                                            </option>
                                            <option value="automatic"
                                                    @if(($registrationSettings[$role->id]['approval'] ?? 'manual') == 'automatic') selected @endif>
                                                تایید خودکار
                                            </option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500 dark:text-gray-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" /></svg>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="registration_{{ $role->id }}_approvers" class="{{ $labelClass }}">نقش‌های
                                        تایید کننده</label>
                                    <select multiple name="registration[{{ $role->id }}][approvers][]"
                                            id="registration_{{ $role->id }}_approvers" class="{{ $inputClass }}">
                                        @foreach($roles as $approverRole)
                                            <option value="{{ $approverRole->id }}"
                                                    @if(in_array($approverRole->id, $registrationSettings[$role->id]['approvers'] ?? [])) selected @endif>
                                                {{ $approverRole->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            </div> {{-- پایان تب ثبت نام --}}

            {{-- تب هوش مصنوعی --}}
            <div x-show="activeTab === 'ai'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
            <div class="{{ $cardClass }}">
                <div class="{{ $headerClass }}">
                    <div
                        class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center justify-between w-full">
                            <div>
                                <h2 class="text-base font-bold text-gray-900 dark:text-white">تنظیمات هوش مصنوعی</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پیکربندی اتصال به سرویس‌های
                                    هوش مصنوعی (GapGPT)</p>
                            </div>
                            <a href="{{ route('settings.gapgpt-logs.index') }}"
                               class="text-xs font-medium text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                مشاهده تاریخچه درخواست‌ها
                            </a>
                        </div>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="gapgpt_api_key" class="{{ $labelClass }}">کلید دسترسی (API Key)</label>
                        <input type="password" class="{{ $inputClass }} dir-ltr text-left" id="gapgpt_api_key"
                               name="gapgpt_api_key" value="{{ $settings['gapgpt_api_key'] ?? '' }}"
                               placeholder="sk-...">
                        <p class="text-xs text-gray-500 mt-1">کلید API دریافتی از پنل GapGPT را اینجا وارد کنید.</p>
                    </div>

                    <div class="md:col-span-2">
                        <label for="gapgpt_base_url" class="{{ $labelClass }}">آدرس پایه (Base URL)</label>
                        <input type="text" class="{{ $inputClass }} dir-ltr text-left" id="gapgpt_base_url"
                               name="gapgpt_base_url"
                               value="{{ $settings['gapgpt_base_url'] ?? 'https://api.gapgpt.app' }}"
                               placeholder="https://api.gapgpt.app">
                        <p class="text-xs text-gray-500 mt-1">در صورت نیاز به تغییر آدرس پیش‌فرض API، آن را اینجا وارد
                            کنید.</p>
                    </div>

                    <div>
                        <label for="gapgpt_default_model" class="{{ $labelClass }}">مدل پیش‌فرض</label>
                        <input type="text" class="{{ $inputClass }} dir-ltr text-left" id="gapgpt_default_model"
                               name="gapgpt_default_model"
                               value="{{ $settings['gapgpt_default_model'] ?? 'gpt-4o-mini' }}"
                               placeholder="gpt-4o-mini">
                        <p class="text-xs text-gray-500 mt-1">مدل زبانی پیش‌فرض برای درخواست‌ها (مثلاً gpt-4o-mini یا
                            gpt-4).</p>
                    </div>

                    <div>
                        <label for="gapgpt_timeout" class="{{ $labelClass }}">تایم‌اوت (ثانیه)</label>
                        <input type="number" class="{{ $inputClass }} dir-ltr text-left" id="gapgpt_timeout"
                               name="gapgpt_timeout" value="{{ $settings['gapgpt_timeout'] ?? '30' }}" placeholder="30">
                        <p class="text-xs text-gray-500 mt-1">حداکثر زمان انتظار برای پاسخ (به ثانیه).</p>
                    </div>

                    <div class="md:col-span-2 flex items-center justify-end pt-2">
                        <button type="button" id="test-ai-connection-btn"
                                class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors flex items-center gap-2 text-sm shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            تست اتصال
                        </button>
                    </div>

                    <div id="test-result" class="md:col-span-2 hidden"></div>
                </div>
            </div>
            
            </div> {{-- پایان تب هوش مصنوعی --}}

            {{-- تب مالی و پرداخت --}}
            <div x-show="activeTab === 'payment'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
                @include('settings::partials.payment_settings')
            {{-- پایان تب مالی --}}
            
            </div> {{-- پایان پنل‌ها --}}
            </div> {{-- پایان کانتینر رپ اصلی (Sidebar + Panels) --}}

            <div class="sticky bottom-4 z-40 flex justify-end mt-8">
                <div
                    class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-2 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
                    <button type="submit"
                            class="px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all transform active:scale-95 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        ذخیره تنظیمات
                    </button>
                </div>
            </div>
        </form>

        {{-- مدیریت کلیدهای API --}}
        @if($isPropertiesActive ?? false)
            @include('settings::api-keys.index')
        @endif

        <form id="test-payment-form" action="{{ route('settings.payment.request') }}" method="POST" class="hidden">
            @csrf
            <input type="hidden" name="gateway" id="test-gateway-input" value="">
            <input type="hidden" name="amount" value="1000">
            <input type="hidden" name="description" value="تست پرداخت">
        </form>
    </div>

    @include('settings::partials.payment_scripts')
@endsection
