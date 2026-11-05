@extends('layouts.user')

@php
    $title = 'مدیریت دسته‌بندی‌های املاک';
    $totalCategories = $totalCategories ?? $categories->total();
    $categoriesWithProperties = $categoriesWithProperties ?? 0;
    $totalPropertiesCategorized = $totalPropertiesCategorized ?? 0;
    $emptyCategories = max(0, $totalCategories - $categoriesWithProperties);

    // پالت رنگ‌های پیشنهادی استاندارد
    $presetColors = [
        ['hex' => '#4f46e5', 'name' => 'نیلی سازمانی'],
        ['hex' => '#059669', 'name' => 'سبز زمردی'],
        ['hex' => '#2563eb', 'name' => 'آبی درباری'],
        ['hex' => '#0d9488', 'name' => 'فیروزه‌ای'],
        ['hex' => '#d97706', 'name' => 'کهربایی'],
        ['hex' => '#dc2626', 'name' => 'یاقوتی'],
        ['hex' => '#7c3aed', 'name' => 'ارغوانی'],
        ['hex' => '#db2777', 'name' => 'سرخابی'],
        ['hex' => '#0891b2', 'name' => 'اقیانوسی'],
        ['hex' => '#475569', 'name' => 'دودی سربی'],
    ];

    // الگوهای آیکون اقامتگاه و ملک
    $iconPresets = [
        ['key' => 'villa', 'name' => 'ویلا'],
        ['key' => 'cottage', 'name' => 'کلبه چوبی'],
        ['key' => 'apartment', 'name' => 'آپارتمان و سوئیت'],
        ['key' => 'ecolodge', 'name' => 'بوم‌گردی'],
        ['key' => 'resort', 'name' => 'مجتمع اقامتی'],
        ['key' => 'pool', 'name' => 'استخردار'],
        ['key' => 'beach', 'name' => 'ساحلی و لب آب'],
        ['key' => 'forest', 'name' => 'جنگلی'],
        ['key' => 'mountain', 'name' => 'کوهستانی'],
        ['key' => 'jacuzzi', 'name' => 'جکوزی‌دار'],
        ['key' => 'luxury', 'name' => 'لوکس و VIP'],
        ['key' => 'breakfast', 'name' => 'با صبحانه'],
        ['key' => 'pet', 'name' => 'پت‌نواز'],
        ['key' => 'commercial', 'name' => 'تجاری و مغازه'],
        ['key' => 'land', 'name' => 'زمین و باغ'],
        ['key' => 'office', 'name' => 'دفتر و اداری'],
    ];
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6 font-sans" x-data="categoriesManager()">

    {{-- پیام‌های سیستم --}}
    @if(session('success'))
        <div class="flex items-center justify-between gap-3 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-200 text-sm shadow-xs transition-all">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-300 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </span>
                <span class="font-bold">{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.closest('div').remove()" class="text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-100 p-1 rounded-lg">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center justify-between gap-3 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-rose-800 dark:text-rose-200 text-sm shadow-xs transition-all">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-300 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
                <span class="font-bold">{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.closest('div').remove()" class="text-rose-500 hover:text-rose-700 dark:hover:text-rose-100 p-1 rounded-lg">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-rose-800 dark:text-rose-200 text-xs space-y-1 shadow-xs">
            <div class="font-bold flex items-center gap-2 text-sm mb-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                لطفاً خطاهای زیر را برطرف نمایید:
            </div>
            <ul class="list-disc list-inside space-y-0.5 pr-2">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- هدر صفحه استاندارد با استایل اختصاصی پنل --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex items-center gap-3.5">
            <span class="flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300 flex-shrink-0 shadow-inner">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
            </span>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                        مدیریت دسته‌بندی‌های املاک
                    </h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 font-sans border border-indigo-200/80 dark:border-indigo-800/50">
                        {{ number_format($totalCategories) }} دسته‌بندی
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                    سازماندهی هوشمند اقامتگاه‌ها و املاک، تعریف رنگ سازمانی و آیکون اختصاصی
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 self-stretch sm:self-auto flex-wrap">
            <a href="{{ route('user.properties.index') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/60 transition-all shadow-2xs">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>لیست املاک</span>
            </a>

            <button type="button" 
                    @click="openCreateModal()" 
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-xs sm:text-sm font-bold hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30 transition-all active:scale-95 cursor-pointer shadow-md shadow-indigo-600/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>افزودن دسته‌بندی جدید</span>
            </button>
        </div>
    </div>

    {{-- کارت‌های آمار و شاخص‌های کلیدی (KPI Cards) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xs space-y-1 transition-all hover:border-indigo-300 dark:hover:border-indigo-600">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">کل دسته‌بندی‌ها</span>
                <span class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white font-sans">
                {{ number_format($totalCategories) }}
            </div>
            <div class="text-[11px] text-gray-400">دسته‌بندی‌های تعریف شده در سامانه</div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xs space-y-1 transition-all hover:border-emerald-300 dark:hover:border-emerald-600">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">دسته‌بندی‌های فعال</span>
                <span class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 font-sans">
                {{ number_format($categoriesWithProperties) }}
            </div>
            <div class="text-[11px] text-gray-400">دارای حداقل یک ملک یا اقامتگاه</div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xs space-y-1 transition-all hover:border-blue-300 dark:hover:border-blue-600">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">املاک دسته‌بندی‌شده</span>
                <span class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-blue-600 dark:text-blue-400 font-sans">
                {{ number_format($totalPropertiesCategorized) }}
            </div>
            <div class="text-[11px] text-gray-400">تعداد کل فایل‌های متصل به دسته‌ها</div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xs space-y-1 transition-all hover:border-amber-300 dark:hover:border-amber-600">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">دسته‌های بدون ملک</span>
                <span class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 font-sans">
                {{ number_format($emptyCategories) }}
            </div>
            <div class="text-[11px] text-gray-400">آماده برای تخصیص و دسته‌بندی فایل‌ها</div>
        </div>
    </div>

    {{-- نوار جستجو و فیلتر --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <form action="{{ route('user.properties.categories.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="relative flex-1">
                <input type="text" 
                       name="search" 
                       x-model="searchQuery" 
                       value="{{ request('search') }}" 
                       placeholder="جستجوی عنوان دسته‌بندی یا نامک (Slug)..." 
                       class="w-full rounded-xl border-gray-200 bg-gray-50/70 pr-10 pl-9 py-2.5 text-xs text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-indigo-400 dark:focus:bg-gray-900 font-sans shadow-2xs">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400 dark:text-gray-500">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <button type="button" 
                        x-show="searchQuery" 
                        @click="searchQuery = ''; $el.closest('form').submit()" 
                        class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition"
                        title="پاک کردن جستجو">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700/60 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 text-xs font-bold transition-all shadow-2xs flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>اعمال فیلتر</span>
                </button>
                @if(request('search'))
                    <a href="{{ route('user.properties.categories.index') }}" class="px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:text-rose-600 text-xs font-bold transition-all" title="حذف تمام فیلترها">
                        بازنشانی
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- جدول لیست دسته‌بندی‌ها --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        @if($categories->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right font-sans">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40 text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700 text-xs font-bold">
                        <tr>
                            <th class="px-5 py-3.5 w-16 text-center">آیکون</th>
                            <th class="px-6 py-3.5">نام دسته‌بندی</th>
                            <th class="px-6 py-3.5">نامک (Slug)</th>
                            <th class="px-6 py-3.5">رنگ سازمانی</th>
                            <th class="px-6 py-3.5 text-center">تعداد املاک</th>
                            <th class="px-6 py-3.5 text-center">تاریخ ایجاد</th>
                            <th class="px-6 py-3.5 text-left">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($categories as $category)
                            @php
                                $catColor = $category->color ?: '#4f46e5';
                                $propCount = $category->properties_count ?? $category->properties()->count();
                                $jalaliDate = \Morilog\Jalali\CalendarUtils::convertNumbers(\Morilog\Jalali\Jalalian::fromCarbon($category->created_at)->format('Y/m/d'));
                            @endphp
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-750/50 transition-colors group">
                                {{-- نماد و آیکون --}}
                                <td class="px-5 py-4 text-center">
                                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center mx-auto shadow-xs border transition-transform group-hover:scale-105"
                                         style="background-color: {{ $catColor }}18; border-color: {{ $catColor }}40; color: {{ $catColor }};">
                                        @if(!empty($category->image))
                                            <img src="{{ asset($category->image) }}" alt="{{ $category->name }}" class="w-6 h-6 object-contain rounded-lg">
                                        @elseif(!empty($category->icon))
                                            <span class="inline-flex items-center justify-center" x-html="renderIconSvg('{{ $category->icon }}')"></span>
                                        @else
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                                        @endif
                                    </div>
                                </td>

                                {{-- نام دسته‌بندی --}}
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <button type="button" 
                                                @click="openDetailsModal({{ json_encode([
                                                    'id' => $category->id,
                                                    'name' => $category->name,
                                                    'slug' => $category->slug,
                                                    'color' => $catColor,
                                                    'icon' => $category->icon ?? '',
                                                    'image' => $category->image ?? '',
                                                    'properties_count' => $propCount,
                                                    'created_at_jalali' => $jalaliDate,
                                                    'user_name' => $category->user?->name ?? 'سیستم'
                                                ]) }})" 
                                                class="font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors text-right flex items-center gap-1.5 cursor-pointer">
                                            <span>{{ $category->name }}</span>
                                            <svg class="w-3.5 h-3.5 text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        <span class="text-[11px] text-gray-400 mt-0.5">شناسه دسته: #{{ $category->id }}</span>
                                    </div>
                                </td>

                                {{-- نامک (Slug) --}}
                                <td class="px-6 py-4">
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 text-xs font-sans dir-ltr text-right border border-gray-200/80 dark:border-gray-600/50">
                                        {{ $category->slug }}
                                    </span>
                                </td>

                                {{-- رنگ سازمانی --}}
                                <td class="px-6 py-4">
                                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700">
                                        <span class="w-4 h-4 rounded-full shadow-xs border border-white dark:border-gray-800 shrink-0" style="background-color: {{ $catColor }}"></span>
                                        <span class="text-xs text-gray-600 dark:text-gray-300 font-sans font-bold dir-ltr">{{ $catColor }}</span>
                                    </div>
                                </td>

                                {{-- تعداد املاک با لینک فیلتر مستقیم --}}
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('user.properties.index', ['category_id' => $category->id]) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold font-sans transition-all {{ $propCount > 0 ? 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 border border-indigo-200 dark:border-indigo-800/40' : 'bg-gray-100 text-gray-400 dark:bg-gray-700/40 dark:text-gray-500' }}"
                                       title="مشاهده املاک این دسته‌بندی">
                                        <span class="font-black">{{ number_format($propCount) }}</span>
                                        <span>ملک</span>
                                        @if($propCount > 0)
                                            <svg class="w-3 h-3 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        @endif
                                    </a>
                                </td>

                                {{-- تاریخ ایجاد --}}
                                <td class="px-6 py-4 text-center text-xs text-gray-500 dark:text-gray-400 font-sans">
                                    {{ $jalaliDate }}
                                </td>

                                {{-- دکمه‌های عملیات --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1.5">
                                        {{-- دکمه مشاهده جزئیات --}}
                                        <button type="button" 
                                                @click="openDetailsModal({{ json_encode([
                                                    'id' => $category->id,
                                                    'name' => $category->name,
                                                    'slug' => $category->slug,
                                                    'color' => $catColor,
                                                    'icon' => $category->icon ?? '',
                                                    'image' => $category->image ?? '',
                                                    'properties_count' => $propCount,
                                                    'created_at_jalali' => $jalaliDate,
                                                    'user_name' => $category->user?->name ?? 'سیستم'
                                                ]) }})" 
                                                class="p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl dark:text-gray-400 dark:hover:text-indigo-300 dark:hover:bg-indigo-950/40 transition-colors" 
                                                title="مشاهده جزئیات کامل">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>

                                        {{-- دکمه ویرایش --}}
                                        <button type="button" 
                                                @click="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->slug) }}', '{{ $catColor }}', '{{ $category->icon }}', '{{ addslashes($category->image ?? '') }}')" 
                                                class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-xl dark:text-indigo-400 dark:hover:bg-indigo-950/40 transition-colors" 
                                                title="ویرایش دسته‌بندی">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>

                                        {{-- دکمه حذف با مودال امن --}}
                                        <button type="button" 
                                                @click="openDeleteModal({{ $category->id }}, '{{ addslashes($category->name) }}', {{ $propCount }})" 
                                                class="p-2 text-rose-600 hover:bg-rose-50 rounded-xl dark:text-rose-400 dark:hover:bg-rose-950/40 transition-colors" 
                                                title="حذف دسته‌بندی">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- صفحه‌بندی --}}
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                {{ $categories->links() }}
            </div>
        @else
            {{-- حالت بدون داده (Empty State) --}}
            <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                <div class="w-16 h-16 rounded-3xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-4 shadow-inner">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1.5">
                    {{ request('search') ? 'هیچ دسته‌بندی مطابق با جستجوی شما یافت نشد' : 'هنوز دسته‌بندی تعریف نشده است' }}
                </h3>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 max-w-md mb-6">
                    {{ request('search') ? 'عبارت جستجو شده را بررسی کرده یا فیلترها را بازنشانی کنید.' : 'با ایجاد اولین دسته‌بندی (مانند ویلا، کلبه، استخردار)، املاک و اقامتگاه‌ها را دسته‌بندی و فیلتر کنید.' }}
                </p>
                <div class="flex items-center gap-3">
                    @if(request('search'))
                        <a href="{{ route('user.properties.categories.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/60 transition-all">
                            پاک کردن جستجو
                        </a>
                    @endif
                    <button type="button" 
                            @click="openCreateModal()" 
                            class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-xs sm:text-sm font-bold hover:bg-indigo-700 transition-all shadow-md shadow-indigo-600/20 active:scale-95">
                        ایجاد اولین دسته‌بندی
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- مودال ۱: افزودن و ویرایش دسته‌بندی (Create / Edit Form Modal) --}}
    <div x-cloak x-show="showFormModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        {{-- پس‌زمینه مات --}}
        <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-sm transition-opacity" 
             @click="closeFormModal()"
             x-show="showFormModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-200 dark:border-gray-700"
                 x-show="showFormModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <form :action="formAction" method="POST" id="categoryForm">
                    @csrf
                    <input type="hidden" name="_method" :value="formMethod">

                    {{-- هدر مودال --}}
                    <div class="flex items-center justify-between px-6 py-4.5 border-b border-gray-100 dark:border-gray-700/80 bg-gray-50/70 dark:bg-gray-900/40">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-2xl flex items-center justify-center shadow-inner"
                                  :style="'background-color: ' + formData.color + '20; color: ' + formData.color + '; border: 1px solid ' + formData.color + '40;'">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white" x-text="isEditMode ? 'ویرایش دسته‌بندی' : 'افزودن دسته‌بندی جدید'"></h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">مشخصات، نماد، نامک و رنگ اختصاصی دسته‌بندی را تعیین کنید</p>
                            </div>
                        </div>

                        <button type="button" @click="closeFormModal()" class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-500 dark:text-gray-300 flex items-center justify-center transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- بدنه فرم --}}
                    <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">

                        {{-- نام و نامک --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5 font-sans">
                                    نام دسته‌بندی <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       name="name" 
                                       x-model="formData.name" 
                                       @input="onNameInput()"
                                       placeholder="مثلاً: ویلاهای لوکس، کلبه جنگلی" 
                                       class="w-full rounded-xl border-gray-200 bg-gray-50/70 px-4 py-2.5 text-xs sm:text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-indigo-400 font-sans shadow-2xs" 
                                       required>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5 font-sans">
                                    نامک یکتا (Slug)
                                    <span class="text-[10px] text-gray-400 font-normal mr-1">(اختیاری)</span>
                                </label>
                                <input type="text" 
                                       name="slug" 
                                       x-model="formData.slug" 
                                       placeholder="luxury-villas" 
                                       class="w-full rounded-xl border-gray-200 bg-gray-50/70 px-4 py-2.5 text-xs sm:text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-indigo-400 font-sans dir-ltr text-right shadow-2xs">
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">در صورت خالی بودن، به طور خودکار از نام دسته‌بندی تولید می‌شود.</p>
                            </div>
                        </div>

                        {{-- انتخاب رنگ سازمانی با پیش‌نمایش پالت --}}
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 font-sans">
                                رنگ نماد و شناسه دسته‌بندی
                            </label>
                            
                            <div class="flex flex-wrap items-center gap-2 p-3 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200/80 dark:border-gray-700">
                                @foreach($presetColors as $colorItem)
                                    <button type="button" 
                                            @click="formData.color = '{{ $colorItem['hex'] }}'" 
                                            class="w-7 h-7 rounded-xl transition-all relative flex items-center justify-center cursor-pointer border-2"
                                            :class="formData.color.toLowerCase() === '{{ strtolower($colorItem['hex']) }}' ? 'border-gray-900 dark:border-white scale-110 shadow-sm' : 'border-transparent hover:scale-105'"
                                            style="background-color: {{ $colorItem['hex'] }};"
                                            title="{{ $colorItem['name'] }}">
                                        <svg x-show="formData.color.toLowerCase() === '{{ strtolower($colorItem['hex']) }}'" class="w-3.5 h-3.5 text-white drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                @endforeach

                                <div class="h-6 w-px bg-gray-300 dark:bg-gray-700 mx-1"></div>

                                {{-- انتخاب رنگ سفارشی --}}
                                <div class="flex items-center gap-2">
                                    <input type="color" 
                                           name="color" 
                                           x-model="formData.color" 
                                           class="w-8 h-8 rounded-xl border border-gray-300 dark:border-gray-600 p-0.5 cursor-pointer bg-white dark:bg-gray-800">
                                    <span class="text-xs font-bold font-sans dir-ltr text-gray-600 dark:text-gray-300" x-text="formData.color"></span>
                                </div>
                            </div>
                        </div>

                        {{-- انتخاب آیکون نمایشی با پیش‌نمایش گرافیکی --}}
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 font-sans">
                                    انتخاب آیکون نمایشی اقامتگاه
                                </label>
                                <span class="text-[11px] text-gray-400">یک نماد متناسب با نوع اقامتگاه انتخاب کنید</span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 max-h-48 overflow-y-auto p-1 border border-gray-100 dark:border-gray-700/60 rounded-2xl bg-gray-50/40 dark:bg-gray-900/20">
                                @foreach($iconPresets as $preset)
                                    <button type="button" 
                                            @click="formData.icon = '{{ $preset['key'] }}'" 
                                            class="flex items-center gap-2 p-2 rounded-xl border text-right transition-all cursor-pointer"
                                            :class="formData.icon === '{{ $preset['key'] }}' ? 'border-indigo-500 bg-indigo-50/70 text-indigo-700 dark:bg-indigo-950/50 dark:border-indigo-500 dark:text-indigo-300 font-bold shadow-2xs' : 'border-gray-200/70 dark:border-gray-700/70 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/40'">
                                        <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                                              :style="formData.icon === '{{ $preset['key'] }}' ? 'background-color: ' + formData.color + '20; color: ' + formData.color : 'background-color: #f3f4f6; color: #6b7280;'">
                                            <span x-html="renderIconSvg('{{ $preset['key'] }}')"></span>
                                        </span>
                                        <span class="text-xs truncate font-sans">{{ $preset['name'] }}</span>
                                    </button>
                                @endforeach
                            </div>

                            {{-- فیلد سفارشی برای آیکون یا ایموجی --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1 font-sans">
                                        کلید یا ایموجی سفارشی
                                    </label>
                                    <input type="text" 
                                           name="icon" 
                                           x-model="formData.icon" 
                                           placeholder="مثلاً: 🏡 یا villa یا campfire" 
                                           class="w-full rounded-xl border-gray-200 bg-gray-50/70 px-3.5 py-2 text-xs text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 font-sans shadow-2xs">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 mb-1 font-sans">
                                        آدرس تصویر یا لوگو اختصاصی (اختیاری)
                                    </label>
                                    <input type="text" 
                                           name="image" 
                                           x-model="formData.image" 
                                           placeholder="images/categories/cabin.png" 
                                           class="w-full rounded-xl border-gray-200 bg-gray-50/70 px-3.5 py-2 text-xs text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 font-sans dir-ltr text-right shadow-2xs">
                                </div>
                            </div>
                        </div>

                        {{-- پیش‌نمایش زنده کارت دسته‌بندی --}}
                        <div class="p-4 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 bg-gray-50/50 dark:bg-gray-900/30 space-y-2">
                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                <span class="font-bold flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    پیش‌نمایش زنده در فیلترها و کارت‌ها
                                </span>
                                <span class="text-[11px] text-gray-400 font-sans" x-text="formData.color"></span>
                            </div>

                            <div class="flex items-center gap-3 pt-1">
                                {{-- پیش‌نمایش به صورت دکمه فیلتر --}}
                                <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl border shadow-xs"
                                     :style="'background-color: ' + formData.color + '12; border-color: ' + formData.color + '40; color: ' + formData.color + ';'">
                                    <span class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0 text-sm"
                                          :style="'background-color: ' + formData.color + '22; color: ' + formData.color + ';'">
                                        <template x-if="formData.image">
                                            <img :src="formData.image" alt="" class="w-5 h-5 object-contain rounded">
                                        </template>
                                        <template x-if="!formData.image">
                                            <span x-html="renderIconSvg(formData.icon)"></span>
                                        </template>
                                    </span>
                                    <span class="text-xs font-bold font-sans" x-text="formData.name || 'عنوان دسته‌بندی'"></span>
                                </div>

                                {{-- پیش‌نمایش به صورت بج کارت اقامتگاه --}}
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-bold font-sans"
                                      :style="'background-color: ' + formData.color + '15; color: ' + formData.color + '; border: 1px solid ' + formData.color + '30;'">
                                    <span class="w-1.5 h-1.5 rounded-full" :style="'background-color: ' + formData.color"></span>
                                    <span x-text="formData.name || 'نام دسته'"></span>
                                </span>
                            </div>
                        </div>

                    </div>

                    {{-- فوتر دکمه‌های مودال --}}
                    <div class="flex items-center justify-end gap-2.5 px-6 py-4 border-t border-gray-100 dark:border-gray-700/80 bg-gray-50/70 dark:bg-gray-900/40">
                        <button type="button" 
                                @click="closeFormModal()" 
                                class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            انصراف
                        </button>
                        <button type="submit" 
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-xs sm:text-sm font-bold hover:bg-indigo-700 transition-all shadow-md shadow-indigo-600/20 active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span x-text="isEditMode ? 'ذخیره تغییرات' : 'ایجاد دسته‌بندی'"></span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- مودال ۲: جزئیات دسته‌بندی (Category Details Modal) --}}
    <div x-cloak x-show="showDetailsModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-sm transition-opacity" 
             @click="closeDetailsModal()"
             x-show="showDetailsModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200 dark:border-gray-700"
                 x-show="showDetailsModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <template x-if="selectedCategory">
                    <div>
                        {{-- بنر و هویت دسته‌بندی --}}
                        <div class="p-6 relative overflow-hidden border-b border-gray-100 dark:border-gray-700/80"
                             :style="'background: linear-gradient(135deg, ' + selectedCategory.color + '15, ' + selectedCategory.color + '05);'">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-3xl flex items-center justify-center shadow-md border-2 border-white dark:border-gray-700"
                                         :style="'background-color: ' + selectedCategory.color + '25; color: ' + selectedCategory.color + ';'">
                                        <template x-if="selectedCategory.image">
                                            <img :src="selectedCategory.image" alt="" class="w-10 h-10 object-contain rounded-xl">
                                        </template>
                                        <template x-if="!selectedCategory.image">
                                            <span class="w-8 h-8 flex items-center justify-center" x-html="renderIconSvg(selectedCategory.icon, 'w-8 h-8')"></span>
                                        </template>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-lg font-black text-gray-900 dark:text-white" x-text="selectedCategory.name"></h3>
                                            <span class="w-3.5 h-3.5 rounded-full border border-white dark:border-gray-800 shadow-xs" :style="'background-color: ' + selectedCategory.color"></span>
                                        </div>
                                        <span class="inline-block mt-1 px-2.5 py-0.5 rounded-md bg-white/80 dark:bg-gray-800/80 text-gray-600 dark:text-gray-300 text-xs font-sans dir-ltr text-right border border-gray-200/60 dark:border-gray-700">
                                            slug: <span x-text="selectedCategory.slug"></span>
                                        </span>
                                    </div>
                                </div>

                                <button type="button" @click="closeDetailsModal()" class="w-8 h-8 rounded-xl bg-white/80 hover:bg-white dark:bg-gray-800/80 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-300 flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- جزئیات و مشخصات --}}
                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div class="p-3 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200/80 dark:border-gray-700/60 space-y-1">
                                    <span class="text-gray-400 block">تعداد املاک متصل</span>
                                    <div class="text-base font-bold text-gray-900 dark:text-white font-sans flex items-center gap-1.5">
                                        <span x-text="selectedCategory.properties_count"></span>
                                        <span class="text-xs text-gray-400 font-normal">ملک</span>
                                    </div>
                                </div>

                                <div class="p-3 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200/80 dark:border-gray-700/60 space-y-1">
                                    <span class="text-gray-400 block">شناسه دسته‌بندی</span>
                                    <div class="text-base font-bold text-gray-900 dark:text-white font-sans" x-text="'#' + selectedCategory.id"></div>
                                </div>

                                <div class="p-3 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200/80 dark:border-gray-700/60 space-y-1">
                                    <span class="text-gray-400 block">کد رنگ سازمانی</span>
                                    <div class="text-xs font-bold font-sans dir-ltr text-right" :style="'color: ' + selectedCategory.color" x-text="selectedCategory.color"></div>
                                </div>

                                <div class="p-3 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200/80 dark:border-gray-700/60 space-y-1">
                                    <span class="text-gray-400 block">تاریخ ایجاد</span>
                                    <div class="text-xs font-bold text-gray-800 dark:text-gray-200 font-sans" x-text="selectedCategory.created_at_jalali"></div>
                                </div>
                            </div>

                            {{-- دکمه پیوند به املاک این دسته‌بندی --}}
                            <div class="pt-2">
                                <a :href="'{{ route('user.properties.index') }}?category_id=' + selectedCategory.id" 
                                   class="w-full flex items-center justify-between p-3.5 rounded-2xl bg-indigo-50/70 hover:bg-indigo-100/80 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/50 border border-indigo-200/80 dark:border-indigo-800/50 text-indigo-700 dark:text-indigo-300 text-xs font-bold transition-all group">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                        <span>مشاهده و مدیریت فایل‌های این دسته‌بندی</span>
                                    </div>
                                    <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                </a>
                            </div>
                        </div>

                        {{-- فوتر مودال جزئیات --}}
                        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 dark:border-gray-700/80 bg-gray-50/70 dark:bg-gray-900/40">
                            <button type="button" 
                                    @click="closeDetailsModal(); openEditModal(selectedCategory.id, selectedCategory.name, selectedCategory.slug, selectedCategory.color, selectedCategory.icon, selectedCategory.image)" 
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition-colors shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                <span>ویرایش این دسته</span>
                            </button>

                            <button type="button" 
                                    @click="closeDetailsModal()" 
                                    class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                بستن
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- مودال ۳: تأیید حذف امن (Delete Confirmation Modal) --}}
    <div x-cloak x-show="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-sm transition-opacity" 
             @click="closeDeleteModal()"
             x-show="showDeleteModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-200 dark:border-gray-700"
                 x-show="showDeleteModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <form :action="deleteAction" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="p-6 text-center space-y-4">
                        <div class="w-14 h-14 rounded-3xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto border border-rose-200 dark:border-rose-800/50 shadow-inner">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </div>

                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">حذف دسته‌بندی</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 leading-relaxed">
                                آیا از حذف دسته‌بندی <strong class="text-gray-900 dark:text-white" x-text="'«' + deleteCategoryName + '»'"></strong> اطمینان دارید؟
                            </p>
                        </div>

                        <template x-if="deleteCategoryCount > 0">
                            <div class="p-3 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/50 text-right text-xs text-amber-800 dark:text-amber-200 flex items-start gap-2">
                                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>
                                    تعداد <strong class="font-bold" x-text="deleteCategoryCount"></strong> ملک به این دسته‌بندی متصل هستند. پس از حذف، املاک بدون دسته‌بندی خواهند شد و فایل آن‌ها حفظ می‌گردد.
                                </span>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center justify-center gap-2.5 px-6 py-4 border-t border-gray-100 dark:border-gray-700/80 bg-gray-50/70 dark:bg-gray-900/40">
                        <button type="button" 
                                @click="closeDeleteModal()" 
                                class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            انصراف
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-md shadow-rose-600/20 active:scale-95 cursor-pointer">
                            تأیید و حذف دسته‌بندی
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

{{-- اسکریپت کنترلر Alpine.js --}}
<script>
    function categoriesManager() {
        return {
            searchQuery: '{{ request('search') }}',
            showFormModal: false,
            isEditMode: false,
            formAction: "{{ route('user.properties.categories.store') }}",
            formMethod: 'POST',
            formData: {
                id: null,
                name: '',
                slug: '',
                color: '#4f46e5',
                icon: 'villa',
                image: ''
            },
            showDetailsModal: false,
            selectedCategory: null,
            showDeleteModal: false,
            deleteAction: '',
            deleteCategoryName: '',
            deleteCategoryCount: 0,

            // باز کردن مودال ایجاد
            openCreateModal() {
                this.isEditMode = false;
                this.formAction = "{{ route('user.properties.categories.store') }}";
                this.formMethod = 'POST';
                this.formData = {
                    id: null,
                    name: '',
                    slug: '',
                    color: '#4f46e5',
                    icon: 'villa',
                    image: ''
                };
                this.showFormModal = true;
            },

            // باز کردن مودال ویرایش
            openEditModal(id, name, slug, color, icon = '', image = '') {
                this.isEditMode = true;
                let url = "{{ route('user.properties.categories.update', ':id') }}";
                this.formAction = url.replace(':id', id);
                this.formMethod = 'PUT';
                this.formData = {
                    id: id,
                    name: name,
                    slug: slug,
                    color: color || '#4f46e5',
                    icon: icon || 'villa',
                    image: image || ''
                };
                this.showFormModal = true;
            },

            closeFormModal() {
                this.showFormModal = false;
            },

            // تولید خودکار Slug در حالت ایجاد
            onNameInput() {
                if (!this.isEditMode && (!this.formData.slug || this.formData.slug === this.slugify(this.formData.name.slice(0, -1)))) {
                    this.formData.slug = this.slugify(this.formData.name);
                }
            },

            slugify(text) {
                return text.toString().toLowerCase()
                    .trim()
                    .replace(/\s+/g, '-')
                    .replace(/[^\u0600-\u06FFa-z0-9\-]/g, '')
                    .replace(/\-\-+/g, '-');
            },

            // مودال جزئیات
            openDetailsModal(category) {
                this.selectedCategory = category;
                this.showDetailsModal = true;
            },

            closeDetailsModal() {
                this.showDetailsModal = false;
                this.selectedCategory = null;
            },

            // مودال حذف
            openDeleteModal(id, name, count = 0) {
                let url = "{{ route('user.properties.categories.destroy', ':id') }}";
                this.deleteAction = url.replace(':id', id);
                this.deleteCategoryName = name;
                this.deleteCategoryCount = count;
                this.showDeleteModal = true;
            },

            closeDeleteModal() {
                this.showDeleteModal = false;
            },

            // رندر آیکون‌های SVG بر اساس کلید
            renderIconSvg(iconKey, sizeClass = 'w-5 h-5') {
                const icons = {
                    'villa': `<svg class="${sizeClass}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>`,
                    'cottage': `<svg class="${sizeClass}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3L2 12h3v8h14v-8h3L12 3zm0 5a2 2 0 100 4 2 2 0 000-4z"/></svg>`,
                    'apartment': `<svg class="${sizeClass}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>`,
                    'ecolodge': `<svg class="${sizeClass}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21V3m0 0a9 9 0 018.716 6.747M12 3a9 9 0 00-8.716 6.747"/></svg>`,
                    'resort': `<svg class="${sizeClass}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>`,
                    'pool': `<svg class="${sizeClass}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2 1m0 0L10 4m2 1v2.5M4 17c1.333-1 2.667-1 4 0 1.333 1 2.667 1 4 0 1.333-1 2.667-1 4 0 1.333 1 2.667 1 4 0M4 21c1.333-1 2.667-1 4 0 1.333 1 2.667 1 4 0 1.333-1 2.667-1 4 0 1.333 1 2.667 1 4 0"/></svg>`,
                    'beach': `<svg class="${sizeClass}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>`,
                    'forest': `<svg class="${sizeClass}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 2L8 8h3l-4 6h4l-3 5h8l-3-5h4l-4-6h3L12 2zm0 17v3"/></svg>`,
                    'mountain': `<svg class="${sizeClass}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 20h18L15 6l-4 7-2-3-6 10z"/></svg>`,
                    'jacuzzi': `<svg class="${sizeClass}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m4-3v2m4-2v2"/></svg>`,
                    'luxury': `<svg class="${sizeClass}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>`,
                    'breakfast': `<svg class="${sizeClass}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8zM6 1v3M10 1v3M14 1v3"/></svg>`,
                    'pet': `<svg class="${sizeClass}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 14c-2.21 0-4 1.79-4 4 0 1.5 1.5 2 4 2s4-.5 4-2c0-2.21-1.79-4-4-4zm-4-3c-.83 0-1.5-.67-1.5-1.5S7.17 8 8 8s1.5.67 1.5 1.5S8.83 11 8 11zm8 0c-.83 0-1.5-.67-1.5-1.5S15.17 8 16 8s1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm-6.5-5c-.83 0-1.5-.67-1.5-1.5S6.67 3 7.5 3s1.5.67 1.5 1.5S8.33 6 7.5 6zm5 0c-.83 0-1.5-.67-1.5-1.5S11.67 3 12.5 3s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>`,
                    'commercial': `<svg class="${sizeClass}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>`,
                    'land': `<svg class="${sizeClass}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>`,
                    'office': `<svg class="${sizeClass}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>`
                };

                if (icons[iconKey]) {
                    return icons[iconKey];
                }

                // اگر ایموجی یا کاراکتر باشد
                if (iconKey && iconKey.length <= 4) {
                    return `<span class="text-base select-none">${iconKey}</span>`;
                }

                // پیش‌فرض
                return `<svg class="${sizeClass}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>`;
            }
        };
    }
</script>
@endsection
