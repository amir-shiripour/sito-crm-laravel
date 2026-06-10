@extends('layouts.admin')

@section('title', 'تنظیمات سیستم')

@php
    // استایل‌های مشترک
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 hover:shadow-md";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/30 rounded-t-2xl";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";

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

    $pos_devices = isset($settings['pos_devices']) ? json_decode($settings['pos_devices'], true) : [];
    $bank_transfer_accounts = isset($settings['bank_transfer_accounts']) ? json_decode($settings['bank_transfer_accounts'], true) : [];
@endphp

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

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
                    class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-800/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data"
              class="space-y-8 pb-24" id="main-settings-form">
            @csrf

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
                        <select class="{{ $inputClass }}" id="app_theme" name="app_theme">
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
                        <p class="text-[11px] text-gray-400 mt-1">با تغییر این گزینه، صفحه اول و استایل‌های سایت تغییر
                            خواهد کرد.</p>
                    </div>

                    <div>
                        <label for="site_display_type" class="{{ $labelClass }}">قالب نمایش سایت</label>
                        <select class="{{ $inputClass }}" id="site_display_type" name="site_display_type">
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
                        <p class="text-[11px] text-gray-400 mt-1">نحوه نمایش صفحه اول سایت را انتخاب کنید.</p>
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

            {{-- کارت جدید و داینامیک: پالت رنگی بصری --}}
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
                    {{-- ایجاد تب‌های نامرئی برای سوئیچ قالب‌ها با JS --}}
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
                                        // بررسی اینکه آیا قبلا برای این قالب رنگی ذخیره شده است؟ اگر نه، رنگ پیش‌فرض قالب انتخاب شود
                                        $activeColor = $savedColors[$themeKey] ?? $defaultColorName;
                                        $isChecked = $activeColor === $colorName;
                                    @endphp
                                    <label class="cursor-pointer relative group" title="{{ ucfirst($colorName) }}">
                                        {{-- اینپوت رادیو نامرئی --}}
                                        <input type="radio" name="theme_colors[{{ $themeKey }}]"
                                               value="{{ $colorName }}"
                                               class="peer sr-only" {{ $isChecked ? 'checked' : '' }}>

                                        {{-- دایره رنگی ویژوال --}}
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

            {{-- کارت ۲: اطلاعات تماس --}}
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

            {{-- کارت تنظیمات ثبت نام --}}
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
                                    <select name="registration[{{ $role->id }}][enabled]"
                                            id="registration_{{ $role->id }}_enabled" class="{{ $inputClass }}">
                                        <option value="0"
                                                @if(!($registrationSettings[$role->id]['enabled'] ?? false)) selected @endif>
                                            غیرفعال
                                        </option>
                                        <option value="1"
                                                @if($registrationSettings[$role->id]['enabled'] ?? false) selected @endif>
                                            فعال
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label for="registration_{{ $role->id }}_approval" class="{{ $labelClass }}">نوع
                                        تایید</label>
                                    <select name="registration[{{ $role->id }}][approval]"
                                            id="registration_{{ $role->id }}_approval" class="{{ $inputClass }}">
                                        <option value="manual"
                                                @if(($registrationSettings[$role->id]['approval'] ?? 'manual') == 'manual') selected @endif>
                                            تایید دستی
                                        </option>
                                        <option value="automatic"
                                                @if(($registrationSettings[$role->id]['approval'] ?? 'manual') == 'automatic') selected @endif>
                                            تایید خودکار
                                        </option>
                                    </select>
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


            {{-- کارت ۳: تنظیمات هوش مصنوعی (GapGPT) --}}
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
                                class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors flex items-center gap-2 text-sm">
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

            {{-- کارت ۴: تنظیمات روش‌های پرداخت --}}
            <div class="{{ $cardClass }}">
                <div class="{{ $headerClass }}">
                    <div
                        class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">مدیریت روش‌های پرداخت</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پیکربندی روش‌ها و درگاه‌های پرداخت
                            آنلاین و آفلاین سیستم</p>
                    </div>
                </div>

                <div class="p-6 space-y-10">

                    @if(!$isAccountingActive)
                        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl p-4 text-sm font-medium flex items-center gap-3">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <p class="font-bold">ماژول حسابداری غیرفعال است!</p>
                                <p class="text-xs mt-1">برای اتصال درگاه‌های پرداخت، دستگاه‌های POS و حساب‌های بانکی به صندوق‌های مالی، ابتدا باید ماژول حسابداری را از بخش <a href="{{ route('admin.modules.index') }}" class="underline text-yellow-900">مدیریت ماژول‌ها</a> فعال کنید.</p>
                            </div>
                        </div>
                    @endif

                    {{-- تنظیمات کلی پرداخت --}}
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-gray-100 dark:border-gray-700">
                        <div>
                            <label for="payment_currency" class="{{ $labelClass }}">واحد پول سیستم</label>
                            <select class="{{ $inputClass }}" id="payment_currency" name="payment_currency">
                                <option
                                    value="toman" {{ ($settings['payment_currency'] ?? 'toman') == 'toman' ? 'selected' : '' }}>
                                    تومان
                                </option>
                                <option
                                    value="rial" {{ ($settings['payment_currency'] ?? '') == 'rial' ? 'selected' : '' }}>
                                    ریال
                                </option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">واحد پولی که مبالغ در سیستم شما با آن ثبت می‌شوند.</p>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">روش‌های پرداخت فعال سیستم</label>
                            @php
                                $activePaymentMethods = isset($settings['active_payment_methods']) ? (is_string($settings['active_payment_methods']) ? json_decode($settings['active_payment_methods'], true) : $settings['active_payment_methods']) : ['online'];
                                if (!is_array($activePaymentMethods)) $activePaymentMethods = ['online'];
                            @endphp
                            <div class="flex flex-wrap gap-4 mt-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="active_payment_methods[]" value="online"
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ in_array('online', $activePaymentMethods) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">درگاه اینترنتی</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="active_payment_methods[]" value="pos"
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ in_array('pos', $activePaymentMethods) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">دستگاه POS</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="active_payment_methods[]" value="transfer"
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ in_array('transfer', $activePaymentMethods) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">انتقال بانکی</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="active_payment_methods[]" value="cod"
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ in_array('cod', $activePaymentMethods) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">پرداخت در محل</span>
                                </label>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-2">این روش‌ها در فرم‌های نوبت‌دهی و بخش‌های مختلف
                                سیستم قابل استفاده خواهند بود.</p>
                        </div>
                    </div>

                    {{-- بخش ۱: درگاه‌های پرداخت اینترنتی --}}
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 pb-2 border-b border-gray-100 dark:border-gray-700">
                            <div
                                class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-md font-bold text-gray-900 dark:text-white">۱. درگاه‌های اینترنتی</h3>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div
                                class="md:col-span-2 bg-indigo-50/50 dark:bg-indigo-900/10 p-4 rounded-xl border border-indigo-100 dark:border-indigo-800/30">
                                <label for="default_payment_gateway" class="{{ $labelClass }}">درگاه اینترنتی
                                    پیش‌فرض</label>
                                <select class="{{ $inputClass }} md:w-1/2" id="default_payment_gateway"
                                        name="default_payment_gateway">
                                    <option value="">انتخاب کنید...</option>
                                    <option
                                        value="zarinpal" {{ ($settings['default_payment_gateway'] ?? '') == 'zarinpal' ? 'selected' : '' }}>
                                        زرین‌پال
                                    </option>
                                    <option
                                        value="zibal" {{ ($settings['default_payment_gateway'] ?? '') == 'zibal' ? 'selected' : '' }}>
                                        زیبال
                                    </option>
                                    <option
                                        value="behpardakht" {{ ($settings['default_payment_gateway'] ?? '') == 'behpardakht' ? 'selected' : '' }}>
                                        به‌پرداخت ملت
                                    </option>
                                </select>
                            </div>
                        </div>

                        {{-- زرین‌پال --}}
                        <div
                            class="bg-gray-50/50 dark:bg-gray-800/50 p-5 rounded-xl border border-gray-100 dark:border-gray-700">
                            <div
                                class="flex items-center gap-3 mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
                                <div
                                    class="w-2 h-2 rounded-full {{ ($settings['zarinpal_status'] ?? '') == 'active' ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600' }}"></div>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">درگاه زرین‌پال</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="zarinpal_status" class="{{ $labelClass }}">وضعیت درگاه</label>
                                    <select class="{{ $inputClass }}" id="zarinpal_status" name="zarinpal_status">
                                        <option
                                            value="inactive" {{ ($settings['zarinpal_status'] ?? 'inactive') == 'inactive' ? 'selected' : '' }}>
                                            غیرفعال
                                        </option>
                                        <option
                                            value="active" {{ ($settings['zarinpal_status'] ?? '') == 'active' ? 'selected' : '' }}>
                                            فعال
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label for="zarinpal_sandbox" class="{{ $labelClass }}">حالت آزمایشی
                                        (Sandbox)</label>
                                    <select class="{{ $inputClass }}" id="zarinpal_sandbox" name="zarinpal_sandbox">
                                        <option
                                            value="0" {{ ($settings['zarinpal_sandbox'] ?? '0') == '0' ? 'selected' : '' }}>
                                            خیر (محیط عملیاتی)
                                        </option>
                                        <option
                                            value="1" {{ ($settings['zarinpal_sandbox'] ?? '') == '1' ? 'selected' : '' }}>
                                            بله (محیط تست)
                                        </option>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label for="zarinpal_merchant_id" class="{{ $labelClass }}">کد مرچنت (Merchant
                                        ID)</label>
                                    <input type="text" class="{{ $inputClass }} dir-ltr text-left"
                                           id="zarinpal_merchant_id" name="zarinpal_merchant_id"
                                           value="{{ $settings['zarinpal_merchant_id'] ?? '' }}"
                                           placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                                </div>

                                <div>
                                    <label for="zarinpal_bank_id" class="{{ $labelClass }}">بانک متصل (حسابداری)</label>
                                    <select name="zarinpal_bank_id" id="zarinpal_bank_id" class="{{ $inputClass }}" @if(!$isAccountingActive) disabled @endif>
                                        <option value="">انتخاب کنید...</option>
                                        @foreach($banks as $bank)
                                            <option
                                                value="{{ $bank->id }}" {{ ($settings['zarinpal_bank_id'] ?? '') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                                        @endforeach
                                    </select>
                                    @if(!$isAccountingActive)
                                        <p class="text-xs text-red-500 mt-1">ماژول حسابداری غیرفعال است.</p>
                                    @endif
                                </div>

                                <div class="md:col-span-2 flex items-center justify-end pt-2">
                                    <button type="button" id="test-zarinpal-btn"
                                            class="px-4 py-2 rounded-xl bg-blue-100 text-blue-700 font-medium hover:bg-blue-200 transition-colors flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        تست پرداخت زرین‌پال
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- زیبال --}}
                        <div
                            class="bg-gray-50/50 dark:bg-gray-800/50 p-5 rounded-xl border border-gray-100 dark:border-gray-700">
                            <div
                                class="flex items-center gap-3 mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
                                <div
                                    class="w-2 h-2 rounded-full {{ ($settings['zibal_status'] ?? '') == 'active' ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600' }}"></div>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">درگاه زیبال</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="zibal_status" class="{{ $labelClass }}">وضعیت درگاه</label>
                                    <select class="{{ $inputClass }}" id="zibal_status" name="zibal_status">
                                        <option
                                            value="inactive" {{ ($settings['zibal_status'] ?? 'inactive') == 'inactive' ? 'selected' : '' }}>
                                            غیرفعال
                                        </option>
                                        <option
                                            value="active" {{ ($settings['zibal_status'] ?? '') == 'active' ? 'selected' : '' }}>
                                            فعال
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label for="zibal_merchant_id" class="{{ $labelClass }}">کد مرچنت (Merchant)</label>
                                    <input type="text" class="{{ $inputClass }} dir-ltr text-left"
                                           id="zibal_merchant_id" name="zibal_merchant_id"
                                           value="{{ $settings['zibal_merchant_id'] ?? '' }}"
                                           placeholder="zibal (برای تست)">
                                </div>

                                <div>
                                    <label for="zibal_bank_id" class="{{ $labelClass }}">بانک متصل (حسابداری)</label>
                                    <select name="zibal_bank_id" id="zibal_bank_id" class="{{ $inputClass }}" @if(!$isAccountingActive) disabled @endif>
                                        <option value="">انتخاب کنید...</option>
                                        @foreach($banks as $bank)
                                            <option
                                                value="{{ $bank->id }}" {{ ($settings['zibal_bank_id'] ?? '') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                                        @endforeach
                                    </select>
                                    @if(!$isAccountingActive)
                                        <p class="text-xs text-red-500 mt-1">ماژول حسابداری غیرفعال است.</p>
                                    @endif
                                </div>

                                <div class="md:col-span-2 flex items-center justify-end pt-2">
                                    <button type="button" id="test-zibal-btn"
                                            class="px-4 py-2 rounded-xl bg-blue-100 text-blue-700 font-medium hover:bg-blue-200 transition-colors flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        تست پرداخت زیبال
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- به‌پرداخت ملت --}}
                        <div
                            class="bg-gray-50/50 dark:bg-gray-800/50 p-5 rounded-xl border border-gray-100 dark:border-gray-700">
                            <div
                                class="flex items-center gap-3 mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
                                <div
                                    class="w-2 h-2 rounded-full {{ ($settings['behpardakht_status'] ?? '') == 'active' ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600' }}"></div>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">درگاه به‌پرداخت ملت</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-3">
                                    <label for="behpardakht_status" class="{{ $labelClass }}">وضعیت درگاه</label>
                                    <select class="{{ $inputClass }} md:w-1/3" id="behpardakht_status"
                                            name="behpardakht_status">
                                        <option
                                            value="inactive" {{ ($settings['behpardakht_status'] ?? 'inactive') == 'inactive' ? 'selected' : '' }}>
                                            غیرفعال
                                        </option>
                                        <option
                                            value="active" {{ ($settings['behpardakht_status'] ?? '') == 'active' ? 'selected' : '' }}>
                                            فعال
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label for="behpardakht_terminal_id" class="{{ $labelClass }}">شماره ترمینال
                                        (Terminal ID)</label>
                                    <input type="text" class="{{ $inputClass }} dir-ltr text-left"
                                           id="behpardakht_terminal_id" name="behpardakht_terminal_id"
                                           value="{{ $settings['behpardakht_terminal_id'] ?? '' }}">
                                </div>
                                <div>
                                    <label for="behpardakht_username" class="{{ $labelClass }}">نام کاربری
                                        (Username)</label>
                                    <input type="text" class="{{ $inputClass }} dir-ltr text-left"
                                           id="behpardakht_username" name="behpardakht_username"
                                           value="{{ $settings['behpardakht_username'] ?? '' }}">
                                </div>
                                <div>
                                    <label for="behpardakht_password" class="{{ $labelClass }}">رمز عبور
                                        (Password)</label>
                                    <input type="password" class="{{ $inputClass }} dir-ltr text-left"
                                           id="behpardakht_password" name="behpardakht_password"
                                           value="{{ $settings['behpardakht_password'] ?? '' }}">
                                </div>

                                <div class="md:col-span-3">
                                    <label for="behpardakht_bank_id" class="{{ $labelClass }}">بانک متصل
                                        (حسابداری)</label>
                                    <select name="behpardakht_bank_id" id="behpardakht_bank_id"
                                            class="{{ $inputClass }} md:w-1/3" @if(!$isAccountingActive) disabled @endif>
                                        <option value="">انتخاب کنید...</option>
                                        @foreach($banks as $bank)
                                            <option
                                                value="{{ $bank->id }}" {{ ($settings['behpardakht_bank_id'] ?? '') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                                        @endforeach
                                    </select>
                                    @if(!$isAccountingActive)
                                        <p class="text-xs text-red-500 mt-1">ماژول حسابداری غیرفعال است.</p>
                                    @endif
                                </div>

                                <div class="md:col-span-3 flex items-center justify-end pt-2">
                                    <button type="button" id="test-behpardakht-btn"
                                            class="px-4 py-2 rounded-xl bg-blue-100 text-blue-700 font-medium hover:bg-blue-200 transition-colors flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        تست پرداخت به‌پرداخت ملت
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- بخش ۲: دستگاه POS --}}
                    <div class="space-y-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3 pb-2 border-b border-gray-100 dark:border-gray-700">
                            <div
                                class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-900/20 flex items-center justify-center text-teal-600 dark:text-teal-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-md font-bold text-gray-900 dark:text-white">۲. دستگاه POS
                                    (کارتخوان)</h3>
                            </div>
                        </div>

                        <div
                            class="bg-gray-50/50 dark:bg-gray-800/50 p-5 rounded-xl border border-gray-100 dark:border-gray-700 space-y-4">
                            <div>
                                <label for="pos_status" class="{{ $labelClass }}">وضعیت پرداخت با POS</label>
                                <select class="{{ $inputClass }} md:w-1/3" id="pos_status" name="pos_status">
                                    <option
                                        value="inactive" {{ ($settings['pos_status'] ?? 'inactive') == 'inactive' ? 'selected' : '' }}>
                                        غیرفعال
                                    </option>
                                    <option
                                        value="active" {{ ($settings['pos_status'] ?? '') == 'active' ? 'selected' : '' }}>
                                        فعال
                                    </option>
                                </select>
                            </div>

                            <div id="pos-devices-container" class="space-y-4">
                                <!-- POS device items will be injected here by JS -->
                            </div>

                            <div class="flex justify-start pt-2">
                                <button type="button" id="add-pos-device-btn"
                                        class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors flex items-center gap-2 text-sm">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    افزودن دستگاه POS
                                </button>
                            </div>

                            <div>
                                <label for="pos_guidance" class="{{ $labelClass }}">متن راهنمای کاربر</label>
                                <textarea class="{{ $inputClass }}" id="pos_guidance" name="pos_guidance" rows="2"
                                          placeholder="مثال: لطفاً پس از پرداخت در محل، رسید خود را به صندوقدار تحویل دهید.">{{ $settings['pos_guidance'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- بخش ۳: انتقال بانکی --}}
                    <div class="space-y-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3 pb-2 border-b border-gray-100 dark:border-gray-700">
                            <div
                                class="w-8 h-8 rounded-lg bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center text-orange-600 dark:text-orange-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-md font-bold text-gray-900 dark:text-white">۳. انتقال بانکی (کارت به
                                    کارت / شبا)</h3>
                            </div>
                        </div>

                        <div
                            class="bg-gray-50/50 dark:bg-gray-800/50 p-5 rounded-xl border border-gray-100 dark:border-gray-700 space-y-4">
                            <div>
                                <label for="bank_transfer_status" class="{{ $labelClass }}">وضعیت انتقال بانکی</label>
                                <select class="{{ $inputClass }} md:w-1/3" id="bank_transfer_status"
                                        name="bank_transfer_status">
                                    <option
                                        value="inactive" {{ ($settings['bank_transfer_status'] ?? 'inactive') == 'inactive' ? 'selected' : '' }}>
                                        غیرفعال
                                    </option>
                                    <option
                                        value="active" {{ ($settings['bank_transfer_status'] ?? '') == 'active' ? 'selected' : '' }}>
                                        فعال
                                    </option>
                                </select>
                            </div>

                            <div id="bank-accounts-container" class="space-y-4">
                                <!-- Bank account items will be injected here by JS -->
                            </div>

                            <div class="flex justify-start pt-2">
                                <button type="button" id="add-bank-account-btn"
                                        class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors flex items-center gap-2 text-sm">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    افزودن حساب بانکی
                                </button>
                            </div>

                            <div>
                                <label for="bank_transfer_guidance" class="{{ $labelClass }}">متن راهنمای آپلود
                                    فیش</label>
                                <textarea class="{{ $inputClass }}" id="bank_transfer_guidance"
                                          name="bank_transfer_guidance" rows="2"
                                          placeholder="مثال: لطفاً پس از واریز مبلغ، تصویر فیش یا کد پیگیری را در این قسمت وارد کنید.">{{ $settings['bank_transfer_guidance'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- بخش ۴: پرداخت در محل --}}
                    <div class="space-y-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3 pb-2 border-b border-gray-100 dark:border-gray-700">
                            <div
                                class="w-8 h-8 rounded-lg bg-green-50 dark:bg-green-900/20 flex items-center justify-center text-green-600 dark:text-green-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-md font-bold text-gray-900 dark:text-white">۴. پرداخت در محل (Cash on Delivery)</h3>
                            </div>
                        </div>

                        <div
                            class="bg-gray-50/50 dark:bg-gray-800/50 p-5 rounded-xl border border-gray-100 dark:border-gray-700 space-y-4">
                            <div>
                                <label for="cod_status" class="{{ $labelClass }}">وضعیت پرداخت در محل</label>
                                <select class="{{ $inputClass }} md:w-1/3" id="cod_status" name="cod_status">
                                    <option
                                        value="inactive" {{ ($settings['cod_status'] ?? 'inactive') == 'inactive' ? 'selected' : '' }}>
                                        غیرفعال
                                    </option>
                                    <option
                                        value="active" {{ ($settings['cod_status'] ?? '') == 'active' ? 'selected' : '' }}>
                                        فعال
                                    </option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">با فعال کردن این گزینه، امکان پرداخت نقدی در محل تحویل برای مشتریان فعال می‌شود. این روش نیازی به اتصال به بانک یا درگاه پرداخت ندارد.</p>
                            </div>

                            <div>
                                <label for="cod_guidance" class="{{ $labelClass }}">متن راهنمای کاربر</label>
                                <textarea class="{{ $inputClass }}" id="cod_guidance" name="cod_guidance" rows="2"
                                          placeholder="مثال: پس از تأیید سفارش، مبلغ را هنگام تحویل به پیک یا مسئول فروش پرداخت نمایید.">{{ $settings['cod_guidance'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- دکمه ذخیره --}}
            <div class="sticky bottom-4 z-40 flex justify-end">
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

        {{-- فرم مجزا برای تست پرداخت --}}
        <form id="test-payment-form" action="{{ route('settings.payment.request') }}" method="POST" class="hidden">
            @csrf
            <input type="hidden" name="gateway" id="test-gateway-input" value="">
            <input type="hidden" name="amount" value="1000"> {{-- Example amount in Toman --}}
            <input type="hidden" name="description" value="تست پرداخت">
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const banks = @json($banks);
            const isAccountingActive = @json($isAccountingActive);

            // تابع تولید ID یکتا برای آیتم‌های داینامیک
            function generateUniqueId(prefix) {
                return prefix + '_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
            }

            // =================================================================
            // DYNAMIC POS DEVICES
            // =================================================================
            const posDevicesContainer = document.getElementById('pos-devices-container');
            const addPosDeviceBtn = document.getElementById('add-pos-device-btn');
            let posDevices = @json($pos_devices);
            if (!Array.isArray(posDevices)) posDevices = [];

            // اختصاص آیدی به دستگاه‌های قبلی که آیدی نداشتند
            posDevices.forEach(d => {
                if (!d.id) d.id = generateUniqueId('pos');
            });

            function createPosDeviceItem(device = {}, index) {
                const deviceId = `pos_device_${index}`;
                const item = document.createElement('div');
                item.className = 'p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800/50 relative';
                item.setAttribute('data-index', index);

                let bankOptions = '<option value="">انتخاب بانک</option>';
                banks.forEach(bank => {
                    bankOptions += `<option value="${bank.id}" ${device.bank_id == bank.id ? 'selected' : ''}>${bank.name}</option>`;
                });

                let disabledAttr = !isAccountingActive ? 'disabled' : '';
                let warningText = !isAccountingActive ? '<p class="text-xs text-red-500 mt-1">ماژول حسابداری غیرفعال است.</p>' : '';

                item.innerHTML = `
                    <input type="hidden" name="pos_devices[${index}][id]" value="${device.id}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="${deviceId}_name" class="{{ $labelClass }}">نام دستگاه</label>
                            <input type="text" data-field="name" id="${deviceId}_name" name="pos_devices[${index}][name]" value="${device.name || ''}" class="{{ $inputClass }}" placeholder="مثال: کارتخوان سامان">
                        </div>
                        <div>
                            <label for="${deviceId}_bank_id" class="{{ $labelClass }}">بانک متصل</label>
                            <select data-field="bank_id" id="${deviceId}_bank_id" name="pos_devices[${index}][bank_id]" class="{{ $inputClass }}" ${disabledAttr}>${bankOptions}</select>
                            ${warningText}
                        </div>
                        <div>
                            <label for="${deviceId}_account_number" class="{{ $labelClass }}">شماره حساب</label>
                            <input type="text" data-field="account_number" id="${deviceId}_account_number" name="pos_devices[${index}][account_number]" value="${device.account_number || ''}" class="{{ $inputClass }} dir-ltr text-left" placeholder="123-456-789">
                        </div>
                    </div>
                    <button type="button" class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center remove-pos-device-btn">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" /></svg>
                    </button>
                `;
                posDevicesContainer.appendChild(item);
            }

            function renderPosDevices() {
                posDevicesContainer.innerHTML = '';
                posDevices.forEach((device, index) => createPosDeviceItem(device, index));
            }

            // همگام‌سازی تایپ کاربر با آرایه قبل از حذف (جلوگیری از پرش دیتا)
            posDevicesContainer.addEventListener('input', (e) => {
                if (e.target.hasAttribute('data-field')) {
                    const index = e.target.closest('[data-index]').getAttribute('data-index');
                    posDevices[index][e.target.getAttribute('data-field')] = e.target.value;
                }
            });
            posDevicesContainer.addEventListener('change', (e) => {
                if (e.target.hasAttribute('data-field')) {
                    const index = e.target.closest('[data-index]').getAttribute('data-index');
                    posDevices[index][e.target.getAttribute('data-field')] = e.target.value;
                }
            });

            addPosDeviceBtn.addEventListener('click', () => {
                posDevices.push({id: generateUniqueId('pos')});
                renderPosDevices();
            });

            posDevicesContainer.addEventListener('click', (e) => {
                if (e.target.closest('.remove-pos-device-btn')) {
                    const item = e.target.closest('[data-index]');
                    const index = parseInt(item.getAttribute('data-index'));
                    posDevices.splice(index, 1);
                    renderPosDevices();
                }
            });

            // =================================================================
            // DYNAMIC BANK ACCOUNTS
            // =================================================================
            const bankAccountsContainer = document.getElementById('bank-accounts-container');
            const addBankAccountBtn = document.getElementById('add-bank-account-btn');
            let bankAccounts = @json($bank_transfer_accounts);
            if (!Array.isArray(bankAccounts)) bankAccounts = [];

            // اختصاص آیدی به دستگاه‌های قبلی
            bankAccounts.forEach(a => {
                if (!a.id) a.id = generateUniqueId('bank');
            });

            function createBankAccountItem(account = {}, index) {
                const accountId = `bank_account_${index}`;
                const item = document.createElement('div');
                item.className = 'p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800/50 relative';
                item.setAttribute('data-index', index);

                let bankOptions = '<option value="">انتخاب بانک</option>';
                banks.forEach(bank => {
                    bankOptions += `<option value="${bank.id}" ${account.bank_id == bank.id ? 'selected' : ''}>${bank.name}</option>`;
                });

                let disabledAttr = !isAccountingActive ? 'disabled' : '';
                let warningText = !isAccountingActive ? '<p class="text-xs text-red-500 mt-1">ماژول حسابداری غیرفعال است.</p>' : '';

                item.innerHTML = `
                    <input type="hidden" name="bank_transfer_accounts[${index}][id]" value="${account.id}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="${accountId}_bank_id" class="{{ $labelClass }}">نام بانک</label>
                            <select data-field="bank_id" id="${accountId}_bank_id" name="bank_transfer_accounts[${index}][bank_id]" class="{{ $inputClass }}" ${disabledAttr}>${bankOptions}</select>
                            ${warningText}
                        </div>
                        <div>
                            <label for="${accountId}_account_number" class="{{ $labelClass }}">شماره حساب</label>
                            <input type="text" data-field="account_number" id="${accountId}_account_number" name="bank_transfer_accounts[${index}][account_number]" value="${account.account_number || ''}" class="{{ $inputClass }} dir-ltr text-left" placeholder="123-456-789">
                        </div>
                        <div>
                            <label for="${accountId}_card_number" class="{{ $labelClass }}">شماره کارت</label>
                            <input type="text" data-field="card_number" id="${accountId}_card_number" name="bank_transfer_accounts[${index}][card_number]" value="${account.card_number || ''}" class="{{ $inputClass }} dir-ltr text-left" placeholder="6037-xxxx-xxxx-xxxx">
                        </div>
                        <div>
                            <label for="${accountId}_iban" class="{{ $labelClass }}">شماره شبا</label>
                            <input type="text" data-field="iban" id="${accountId}_iban" name="bank_transfer_accounts[${index}][iban]" value="${account.iban || ''}" class="{{ $inputClass }} dir-ltr text-left" placeholder="IR...">
                        </div>
                    </div>
                    <button type="button" class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center remove-bank-account-btn">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" /></svg>
                    </button>
                `;
                bankAccountsContainer.appendChild(item);
            }

            function renderBankAccounts() {
                bankAccountsContainer.innerHTML = '';
                bankAccounts.forEach((account, index) => createBankAccountItem(account, index));
            }

            // همگام‌سازی تایپ کاربر با آرایه
            bankAccountsContainer.addEventListener('input', (e) => {
                if (e.target.hasAttribute('data-field')) {
                    const index = e.target.closest('[data-index]').getAttribute('data-index');
                    bankAccounts[index][e.target.getAttribute('data-field')] = e.target.value;
                }
            });
            bankAccountsContainer.addEventListener('change', (e) => {
                if (e.target.hasAttribute('data-field')) {
                    const index = e.target.closest('[data-index]').getAttribute('data-index');
                    bankAccounts[index][e.target.getAttribute('data-field')] = e.target.value;
                }
            });

            addBankAccountBtn.addEventListener('click', () => {
                bankAccounts.push({id: generateUniqueId('bank')});
                renderBankAccounts();
            });

            bankAccountsContainer.addEventListener('click', (e) => {
                if (e.target.closest('.remove-bank-account-btn')) {
                    const item = e.target.closest('[data-index]');
                    const index = parseInt(item.getAttribute('data-index'));
                    bankAccounts.splice(index, 1);
                    renderBankAccounts();
                }
            });


            // =================================================================
            // EVENT LISTENERS & INITIAL RENDERING
            // =================================================================

            // اسکریپت جابجایی تب‌های رنگ بر اساس قالب انتخابی
            document.getElementById('app_theme').addEventListener('change', function () {
                const selectedTheme = this.value;
                document.querySelectorAll('.theme-color-panel').forEach(panel => {
                    panel.classList.toggle('hidden', panel.id !== `panel-color-${selectedTheme}`);
                    panel.classList.toggle('block', panel.id === `panel-color-${selectedTheme}`);
                });
            });

            // تابع تست پرداخت
            window.submitTestPayment = function (gateway = 'zarinpal') {
                document.getElementById('test-gateway-input').value = gateway;

                if (gateway === 'zarinpal') {
                    const merchantId = document.getElementById('zarinpal_merchant_id').value;
                    if (!merchantId) {
                        alert('لطفاً ابتدا کد مرچنت زرین‌پال را وارد کرده و تنظیمات را ذخیره کنید.');
                        return;
                    }
                } else if (gateway === 'zibal') {
                    const merchantId = document.getElementById('zibal_merchant_id').value;
                    if (!merchantId) {
                        alert('لطفاً ابتدا کد مرچنت زیبال را وارد کرده و تنظیمات را ذخیره کنید.');
                        return;
                    }
                } else if (gateway === 'behpardakht') {
                    const terminalId = document.getElementById('behpardakht_terminal_id').value;
                    if (!terminalId) {
                        alert('لطفاً ابتدا اطلاعات به‌پرداخت ملت را وارد کرده و تنظیمات را ذخیره کنید.');
                        return;
                    }
                }

                document.getElementById('test-payment-form').submit();
            };

            // اتصال Event Listeners به دکمه‌های تست
            document.getElementById('test-zarinpal-btn').addEventListener('click', () => submitTestPayment('zarinpal'));
            document.getElementById('test-zibal-btn').addEventListener('click', () => submitTestPayment('zibal'));
            document.getElementById('test-behpardakht-btn').addEventListener('click', () => submitTestPayment('behpardakht'));
            document.getElementById('test-ai-connection-btn').addEventListener('click', () => testConnection());


            // تابع تست اتصال هوش مصنوعی
            window.testConnection = function () {
                const btn = document.getElementById('test-ai-connection-btn');
                const resultDiv = document.getElementById('test-result');
                const apiKey = document.getElementById('gapgpt_api_key').value;
                const baseUrl = document.getElementById('gapgpt_base_url').value;

                if (!apiKey) {
                    alert('لطفاً ابتدا کلید API را وارد کنید.');
                    return;
                }

                btn.disabled = true;
                btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> در حال بررسی...';
                resultDiv.classList.add('hidden');
                resultDiv.className = 'md:col-span-2 hidden';

                fetch('{{ route('settings.test-gapgpt') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({gapgpt_api_key: apiKey, gapgpt_base_url: baseUrl})
                })
                    .then(response => response.json())
                    .then(data => {
                        resultDiv.classList.remove('hidden');
                        if (data.success) {
                            resultDiv.className = 'md:col-span-2 p-4 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 text-sm';
                            resultDiv.innerHTML = `<div class="font-bold">${data.message}</div>`;
                        } else {
                            resultDiv.className = 'md:col-span-2 p-4 rounded-xl bg-red-50 text-red-700 border border-red-100 text-sm';
                            resultDiv.innerHTML = `<div class="font-bold">${data.message}</div>`;
                        }
                    })
                    .catch(error => {
                        resultDiv.classList.remove('hidden');
                        resultDiv.className = 'md:col-span-2 p-4 rounded-xl bg-red-50 text-red-700 border border-red-100 text-sm';
                        resultDiv.innerHTML = 'خطای غیرمنتظره رخ داد.';
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg> تست اتصال';
                    });
            };

            // Initial render of dynamic fields
            renderPosDevices();
            renderBankAccounts();
        });
    </script>
@endsection
