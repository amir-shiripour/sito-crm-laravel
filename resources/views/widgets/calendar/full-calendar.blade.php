@extends('layouts.user')

@section('title', 'تقویم جامع شمسی و میلادی')

@section('content')
<style>
    /* مخفی کردن اسکرول‌بارهای اضافی در خانه‌های تقویم */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<div 
    :class="{
        'fixed inset-0 z-[100] bg-gray-100/95 dark:bg-gray-950/95 backdrop-blur-md p-3 sm:p-5 overflow-y-auto flex flex-col space-y-3': isFocusMode,
        'space-y-4': !isFocusMode
    }"
    x-data="fullCalendarManager()" 
    x-init="initCalendar()"
    @keydown.window.escape="if(isFocusMode) toggleFocusMode()"
>

    {{-- هدر صفحه، انتخاب حالت نمایش و کنترل‌های ناوبری --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 sm:p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm flex-shrink-0">
        <div>
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center w-11 h-11 rounded-2xl bg-rose-500 text-white shadow-lg shadow-rose-500/30 flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </span>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2 flex-wrap">
                        <span>تقویم جامع شمسی و میلادی</span>
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 flex items-center gap-1.5 shadow-sm">
                            <span x-text="activeDisplayTitle"></span>
                            <svg x-show="isLoading" class="w-3.5 h-3.5 animate-spin text-rose-600 dark:text-rose-400 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <template x-if="isFocusMode">
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 flex items-center gap-1 shadow-sm">
                                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                                <span>حالت تمرکز</span>
                            </span>
                        </template>
                    </h1>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-2">
                        <span>معادل میلادی:</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400" x-text="gregorianTitle"></span>
                    </p>
                </div>
            </div>
        </div>

        {{-- سوییچر نماها و کنترل‌های ناوبری --}}
        <div class="flex items-center gap-2 flex-wrap self-end lg:self-auto">
            
            {{-- دکمه ثبت رویداد جدید (به سبک Google Calendar) --}}
            @if($allowEventCreation ?? true)
            <button 
                type="button" 
                @click="openCreateEventModal()" 
                class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-rose-500 to-indigo-600 hover:from-rose-600 hover:to-indigo-700 text-white text-xs font-bold shadow-sm hover:shadow-md transition-all flex items-center gap-1.5 active:scale-95"
                title="ثبت رویداد جدید در تقویم"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>رویداد جدید</span>
            </button>
            @endif

            {{-- دکمه حالت تمرکز (تمام‌صفحه بدون تغییر صفحه) --}}
            <button 
                type="button" 
                @click="toggleFocusMode()" 
                :class="isFocusMode ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'" 
                class="px-3 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 border border-gray-200 dark:border-gray-600/60"
                :title="isFocusMode ? 'خروج از حالت تمرکز (Esc)' : 'حالت تمرکز و تمام‌صفحه'"
            >
                <template x-if="!isFocusMode">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                        <span class="hidden sm:inline">حالت تمرکز</span>
                    </div>
                </template>
                <template x-if="isFocusMode">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>خروج (Esc)</span>
                    </div>
                </template>
            </button>

            {{-- انتخاب حالت نمایش (ماهانه / هفتگی) --}}
            <div class="flex items-center bg-gray-100 dark:bg-gray-700/60 p-1 rounded-xl border border-gray-200 dark:border-gray-600/60">
                <button 
                    type="button" 
                    @click="changeViewMode('month')" 
                    :class="viewMode === 'month' ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm font-bold' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium'" 
                    class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <span>نمای ماهانه</span>
                </button>
                <button 
                    type="button" 
                    @click="changeViewMode('week')" 
                    :class="viewMode === 'week' ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm font-bold' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium'" 
                    class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                    </svg>
                    <span>نمای هفتگی</span>
                </button>
            </div>

            {{-- سوییچر سطح‌بندی نمایش تقویم (سطح ۱: کارت ساده / سطح ۲: ساعتی Google Calendar) --}}
            <div x-show="viewMode === 'week' || viewMode === 'day'" x-transition class="flex items-center bg-gray-100 dark:bg-gray-700/60 p-1 rounded-xl border border-gray-200 dark:border-gray-600/60">
                <button 
                    type="button" 
                    @click="changeCalendarLevel('level1')" 
                    :class="calendarLevel === 'level1' ? 'bg-white dark:bg-gray-800 text-rose-600 dark:text-rose-400 shadow-sm font-black' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium'" 
                    class="px-2.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5"
                    title="سطح ۱: نمایش کارتی روزها (حالت کلاسیک)"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                    <span>سطح ۱ (کارت)</span>
                </button>
                <button 
                    type="button" 
                    @click="changeCalendarLevel('level2')" 
                    :class="calendarLevel === 'level2' ? 'bg-white dark:bg-gray-800 text-rose-600 dark:text-rose-400 shadow-sm font-black' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium'" 
                    class="px-2.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5"
                    title="سطح ۲: جدول زمانی ۲۴ ساعته پیشرفته Google Calendar"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>سطح ۲ (ساعتی گوگل)</span>
                </button>
            </div>

            {{-- دکمه امروز / این هفته --}}
            <button @click="goToToday()" class="px-3 py-2 rounded-xl bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-rose-700 dark:text-rose-300 border border-rose-200/70 dark:border-rose-800/60 font-bold text-xs transition-colors flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span x-text="viewMode === 'week' ? 'این هفته' : 'امروز'"></span>
            </button>

            {{-- دکمه‌های ناوبری دوره (قبل / بعد) --}}
            <div class="flex items-center bg-gray-100 dark:bg-gray-700/60 p-1 rounded-xl border border-gray-200 dark:border-gray-600/60">
                <button @click="prevPeriod()" class="p-1.5 rounded-lg hover:bg-white dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition-colors" :title="viewMode === 'week' ? 'هفته قبل' : 'ماه قبل'">
                    <svg class="w-5 h-5 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <span class="px-3 text-xs font-bold text-gray-900 dark:text-gray-100" x-text="activeDisplayTitle"></span>
                <button @click="nextPeriod()" class="p-1.5 rounded-lg hover:bg-white dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition-colors" :title="viewMode === 'week' ? 'هفته بعد' : 'ماه بعد'">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- فیلتر منابع رویداد --}}
    <div class="flex items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm flex-wrap">
        <div class="flex items-center gap-2 flex-wrap">
            <div class="flex items-center gap-2">
                <span class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300">فیلتر منابع:</span>
                <div class="flex items-center gap-1 text-[11px] font-bold bg-gray-100 dark:bg-gray-700/60 px-2 py-0.5 rounded-lg border border-gray-200/60 dark:border-gray-600/50">
                    <button type="button" @click="selectAllSources()" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors" title="انتخاب همه منابع">همه</button>
                    <span class="text-gray-300 dark:text-gray-500">|</span>
                    <button type="button" @click="deselectAllSources()" class="text-gray-500 dark:text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors" title="عدم انتخاب همه">هیچکدام</button>
                </div>
            </div>

            @foreach($sources as $src)
                <label 
                    class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl border text-xs sm:text-sm font-bold cursor-pointer transition-all select-none shadow-sm"
                    :class="{
                        @if($src['key'] === 'booking')
                            'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-700/60 ring-1 ring-blue-500/20': selectedSources.includes('{{ $src['key'] }}'),
                        @elseif($src['key'] === 'tasks')
                            'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-700/60 ring-1 ring-amber-500/20': selectedSources.includes('{{ $src['key'] }}'),
                        @elseif($src['key'] === 'reminders')
                            'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-700/60 ring-1 ring-purple-500/20': selectedSources.includes('{{ $src['key'] }}'),
                        @elseif($src['key'] === 'jalali_holidays')
                            'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-700/60 ring-1 ring-rose-500/20': selectedSources.includes('{{ $src['key'] }}'),
                        @elseif($src['key'] === 'google_calendar')
                            'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/60 dark:text-teal-300 dark:border-teal-700/60 ring-1 ring-teal-500/20': selectedSources.includes('{{ $src['key'] }}'),
                        @else
                            'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-700/60 ring-1 ring-indigo-500/20': selectedSources.includes('{{ $src['key'] }}'),
                        @endif
                        'bg-gray-100/70 text-gray-500 border-gray-200 dark:bg-gray-800/60 dark:text-gray-400 dark:border-gray-700/80 opacity-60 hover:opacity-90': !selectedSources.includes('{{ $src['key'] }}')
                    }"
                >
                    <input type="checkbox" value="{{ $src['key'] }}" x-model="selectedSources" class="rounded text-rose-600 dark:text-rose-500 focus:ring-rose-500 bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 w-4 h-4 cursor-pointer">
                    <span>{{ $src['label'] }}</span>
                </label>
            @endforeach
        </div>

        <div class="flex items-center gap-3.5 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-300 flex-wrap">
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-500"></span> نوبت‌ها</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500"></span> وظایف</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-purple-500"></span> یادآوری‌ها</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-500"></span> تعطیلات رسمی</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> مناسبت‌ها</span>
            @if(collect($sources)->contains('key', 'google_calendar'))
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-teal-500"></span> گوگل کلندر</span>
            @endif
        </div>
    </div>

    {{-- بخش اصلی تقویم (ماهانه، هفتگی و روزانه) همراه با لودینگ اختصاصی --}}
    <div class="relative min-h-[440px]">
        {{-- لودینگ شیشه‌ای مدرن و ملایم --}}
        <div 
            x-show="isLoading" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute inset-0 z-30 flex flex-col items-center justify-center bg-white/75 dark:bg-gray-900/75 backdrop-blur-[2px] rounded-2xl"
            x-cloak
        >
            <div class="flex flex-col items-center gap-3 p-5 rounded-2xl bg-white/95 dark:bg-gray-800/95 shadow-2xl border border-gray-200/80 dark:border-gray-700/80">
                <div class="relative flex items-center justify-center">
                    <svg class="w-9 h-9 animate-spin text-rose-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="absolute w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
                </div>
                <span class="text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 animate-pulse">
                    در حال به‌روزرسانی تقویم...
                </span>
            </div>
        </div>

        {{-- ۱. شبکه تقویم ماهانه --}}
        <div x-show="viewMode === 'month'" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            {{-- سرستون روزهای هفته --}}
            <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 text-center py-3.5 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300">
                <span>شنبه</span>
                <span>یکشنبه</span>
                <span>دوشنبه</span>
                <span>سه‌شنبه</span>
                <span>چهارشنبه</span>
                <span>پنج‌شنبه</span>
                <span class="text-rose-600 dark:text-rose-400 font-extrabold">جمعه</span>
            </div>

            {{-- خانه‌های تقویم ماهانه --}}
            <div class="grid grid-cols-7 divide-x divide-x-reverse divide-y divide-gray-200 dark:divide-gray-700 overflow-x-hidden">
                <template x-for="cell in calendarCells" :key="cell.key">
                    <div 
                        @click="selectDay(cell)"
                        :class="{
                            'bg-gray-50/40 dark:bg-gray-900/60 opacity-40 select-none': !cell.day,
                            'bg-rose-50/50 dark:bg-rose-950/30 ring-2 ring-rose-500/50 z-10': cell.isToday,
                            'hover:bg-gray-50/80 dark:hover:bg-gray-700/40 cursor-pointer': cell.day
                        }"
                        class="p-2 sm:p-3 min-h-[100px] sm:min-h-[150px] transition-colors flex flex-col justify-between relative group overflow-hidden"
                    >
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                {{-- روز شمسی --}}
                                <span 
                                    x-text="cell.day || ''" 
                                    :class="{
                                        'bg-rose-500 text-white font-black px-2.5 py-0.5 sm:py-1 rounded-full shadow-md shadow-rose-500/30': cell.isToday,
                                        'text-rose-600 dark:text-rose-400 font-black': !cell.isToday && cell.isHoliday,
                                        'text-gray-900 dark:text-gray-100 font-bold': !cell.isToday && !cell.isHoliday
                                    }"
                                    class="text-xs sm:text-sm"
                                ></span>
                                {{-- تاریخ معادل میلادی (روز + ماه) --}}
                                <span 
                                    x-show="cell.shortEn" 
                                    x-text="cell.shortEn" 
                                    class="hidden md:inline-block text-[10px] sm:text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/70 border border-gray-200/60 dark:border-gray-600/40 px-1.5 py-0.5 rounded-md" 
                                    :title="'تاریخ معادل میلادی: ' + cell.formattedEn"
                                ></span>
                            </div>
                            <span x-show="cell.events && cell.events.length" x-text="cell.events.length + ' مورد'" class="text-[10px] sm:text-xs font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/50 border border-indigo-100 dark:border-indigo-800/50 px-1.5 py-0.5 rounded-md"></span>
                        </div>

                        {{-- رویدادهای روز ماهانه --}}
                        <div class="space-y-1 my-1 overflow-y-auto no-scrollbar max-h-[80px] sm:max-h-[105px] w-full">
                            <template x-for="ev in (cell.events || []).slice(0, 3)" :key="ev.id">
                                <div 
                                    @click.stop="showEventDetail(ev)"
                                    :style="getMonthEventStyle(ev)"
                                    :class="{
                                        'bg-blue-50 dark:bg-blue-950/70 text-blue-700 dark:text-blue-200 border-blue-200 dark:border-blue-800/80': ev.source === 'booking' && !ev.service_color,
                                        'bg-amber-50 dark:bg-amber-950/70 text-amber-800 dark:text-amber-200 border-amber-200 dark:border-amber-800/80': ev.source === 'tasks',
                                        'bg-purple-50 dark:bg-purple-950/70 text-purple-700 dark:text-purple-200 border-purple-200 dark:border-purple-800/80': ev.source === 'reminders',
                                        'bg-rose-50 dark:bg-rose-950/70 text-rose-700 dark:text-rose-200 border-rose-200 dark:border-rose-800/80 font-extrabold': ev.source === 'jalali_holidays' && ev.is_holiday,
                                        'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-200 border-emerald-200 dark:border-emerald-800/80': ev.source === 'jalali_holidays' && !ev.is_holiday,
                                        'bg-teal-50 dark:bg-teal-950/70 text-teal-700 dark:text-teal-200 border-teal-200 dark:border-teal-800/80': ev.source === 'google_calendar'
                                    }"
                                    class="px-2 py-1 rounded-xl text-[10px] sm:text-xs font-bold border truncate hover:brightness-95 dark:hover:brightness-125 transition-all cursor-pointer shadow-sm flex items-center justify-between gap-1 w-full min-w-0"
                                >
                                    <span x-text="ev.title" class="truncate"></span>
                                    <span x-text="ev.time" class="hidden sm:inline-block text-[10px] sm:text-xs opacity-90 mr-1 flex-shrink-0"></span>
                                </div>
                            </template>

                            <template x-if="cell.events && cell.events.length > 3">
                                <div class="text-[10px] sm:text-xs font-bold text-indigo-600 dark:text-indigo-400 text-center pt-0.5 hover:underline cursor-pointer">
                                    + <span x-text="cell.events.length - 3"></span> مورد دیگر
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ۲. شبکه تقویم هفتگی (دارای دو سطح نمایش) --}}
        <div x-show="viewMode === 'week'">
            
            {{-- سطح ۱: نمایش کارتی فعلی (حفظ ۱۰۰٪ ساختار قبلی بدون هیچ تغییری) --}}
            <div x-show="calendarLevel === 'level1'" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-7 divide-y md:divide-y-0 md:divide-x md:divide-x-reverse divide-gray-200 dark:divide-gray-700">
                    <template x-for="wday in weekDays" :key="wday.key">
                        <div 
                            :class="{
                                'bg-rose-50/40 dark:bg-rose-950/20': wday.is_today,
                                'bg-white dark:bg-gray-800': !wday.is_today
                            }"
                            class="flex flex-col min-h-[350px] sm:min-h-[460px] transition-colors"
                        >
                            {{-- سرستون هر روز هفته --}}
                            <div 
                                @click="selectDay({ day: wday.day, formattedEn: wday.formatted_en, events: wday.events })"
                                :class="{
                                    'bg-rose-500 text-white shadow-md': wday.is_today,
                                    'bg-gray-50/90 dark:bg-gray-900/60 border-b border-gray-200 dark:border-gray-700': !wday.is_today
                                }"
                                class="p-3 text-center cursor-pointer hover:opacity-90 transition-all flex flex-col items-center justify-between gap-1 select-none"
                            >
                                <span class="text-xs font-bold" :class="wday.is_today ? 'text-white' : (wday.day_name === 'جمعه' ? 'text-rose-600 dark:text-rose-400 font-extrabold' : 'text-gray-700 dark:text-gray-300')" x-text="wday.day_name"></span>
                                
                                <div class="flex items-center gap-2 my-0.5">
                                    <span class="text-base sm:text-lg font-black" :class="wday.is_today ? 'text-white' : (wday.is_holiday ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-gray-100')" x-text="wday.day + ' ' + wday.month_name"></span>
                                </div>

                                <div class="flex items-center gap-1.5 text-[10px]">
                                    <span :class="wday.is_today ? 'text-white/80' : 'text-gray-500 dark:text-gray-400'" x-text="wday.short_en"></span>
                                    <template x-if="wday.is_today">
                                        <span class="bg-white text-rose-600 text-[9px] font-black px-1.5 py-0.2 rounded-full">امروز</span>
                                    </template>
                                    <template x-if="wday.is_holiday && !wday.is_today">
                                        <span class="bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300 text-[9px] font-extrabold px-1.5 py-0.2 rounded-full">تعطیل</span>
                                    </template>
                                </div>
                            </div>

                            {{-- لیست رویدادهای روز هفته --}}
                            <div class="p-2 sm:p-2.5 flex-1 space-y-2 overflow-y-auto no-scrollbar max-h-[380px]">
                                <template x-if="!wday.events || !wday.events.length">
                                    <div class="h-full flex items-center justify-center py-8 text-center text-[11px] text-gray-400 dark:text-gray-500 border border-dashed border-gray-200/70 dark:border-gray-700/60 rounded-xl p-3">
                                        <span>بدون رویداد</span>
                                    </div>
                                </template>

                                <template x-for="ev in (wday.events || [])" :key="ev.id">
                                    <div 
                                        @click="showEventDetail(ev)"
                                        :style="getWeekLevel1CardStyle(ev)"
                                        :class="{
                                            'bg-blue-50 dark:bg-blue-950/70 text-blue-800 dark:text-blue-200 border-blue-200 dark:border-blue-800/80 hover:border-blue-400': ev.source === 'booking' && !ev.service_color,
                                            'bg-amber-50 dark:bg-amber-950/70 text-amber-800 dark:text-amber-200 border-amber-200 dark:border-amber-800/80 hover:border-amber-400': ev.source === 'tasks',
                                            'bg-purple-50 dark:bg-purple-950/70 text-purple-800 dark:text-purple-200 border-purple-200 dark:border-purple-800/80 hover:border-purple-400': ev.source === 'reminders',
                                            'bg-rose-50 dark:bg-rose-950/70 text-rose-800 dark:text-rose-200 border-rose-200 dark:border-rose-800/80 hover:border-rose-400 font-extrabold': ev.source === 'jalali_holidays' && ev.is_holiday,
                                            'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-200 border-emerald-200 dark:border-emerald-800/80 hover:border-emerald-400': ev.source === 'jalali_holidays' && !ev.is_holiday,
                                            'bg-teal-50 dark:bg-teal-950/70 text-teal-800 dark:text-teal-200 border-teal-200 dark:border-teal-800/80 hover:border-teal-400': ev.source === 'google_calendar'
                                        }"
                                        class="p-2.5 rounded-xl border text-xs font-bold transition-all cursor-pointer shadow-sm hover:shadow hover:scale-[1.02] flex flex-col justify-between gap-1.5"
                                    >
                                        <div class="flex items-center justify-between gap-1">
                                            <span class="text-[10px] opacity-80" x-text="ev.time || 'تمام روز'"></span>
                                            <span 
                                                :style="(ev.source === 'booking' && ev.service_color) || (ev.source === 'custom_events' && ev.color) ? { backgroundColor: ev.service_color || ev.color } : {}"
                                                :class="{
                                                    'bg-blue-500': ev.source === 'booking' && !ev.service_color,
                                                    'bg-indigo-500': ev.source === 'custom_events' && !ev.color,
                                                    'bg-amber-500': ev.source === 'tasks',
                                                    'bg-purple-500': ev.source === 'reminders',
                                                    'bg-rose-500': ev.source === 'jalali_holidays' && ev.is_holiday,
                                                    'bg-emerald-500': ev.source === 'jalali_holidays' && !ev.is_holiday,
                                                    'bg-teal-500': ev.source === 'google_calendar'
                                                }"
                                                class="w-2 h-2 rounded-full flex-shrink-0"
                                            ></span>
                                        </div>
                                        <p class="text-xs font-bold leading-snug line-clamp-2" x-text="ev.title"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- سطح ۲: نمای پیشرفته Google Calendar (جدول زمانی ۲۴ ساعته با بازه‌های یک‌ساعته و رنگ اختصاصی سرویس‌ها) --}}
            <div x-show="calendarLevel === 'level2'" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col transition-colors">
                
                {{-- ۱. هدر ثابت روزهای هفته (Sticky Header) --}}
                <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/95 dark:bg-gray-900/90 z-20 flex select-none">
                    {{-- ستون عنوان ساعت --}}
                    <div class="w-14 sm:w-16 flex-shrink-0 flex items-center justify-center p-2 text-[10px] sm:text-xs font-bold text-gray-500 dark:text-gray-400 border-l border-gray-200 dark:border-gray-700">
                        <span>ساعت</span>
                    </div>

                    {{-- ۷ سرستون روزهای هفته --}}
                    <div class="grid grid-cols-7 flex-1">
                        <template x-for="wday in weekDays" :key="'ghead_' + wday.key">
                            <div 
                                @click="selectDay({ day: wday.day, formattedEn: wday.formatted_en, events: wday.events, is_today: wday.is_today })"
                                :class="{
                                    'bg-rose-50/80 dark:bg-rose-950/60': wday.is_today,
                                    'hover:bg-gray-100/70 dark:hover:bg-gray-700/50': !wday.is_today
                                }"
                                class="p-2 sm:p-3 text-center cursor-pointer transition-colors flex flex-col items-center justify-center gap-1 min-w-0 border-l border-gray-200 dark:border-gray-700 last:border-l-0"
                            >
                                <span class="text-[11px] sm:text-xs font-bold truncate" :class="wday.is_today ? 'text-rose-600 dark:text-rose-400 font-black' : (wday.day_name === 'جمعه' ? 'text-rose-600 dark:text-rose-400 font-extrabold' : 'text-gray-700 dark:text-gray-300')" x-text="wday.day_name"></span>
                                
                                <div class="flex items-center gap-1.5 my-0.5">
                                    <span 
                                        :class="wday.is_today ? 'bg-rose-500 text-white shadow-md shadow-rose-500/30' : (wday.is_holiday ? 'text-rose-600 dark:text-rose-400 font-black' : 'text-gray-900 dark:text-gray-100 font-black')"
                                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center text-xs sm:text-sm"
                                        x-text="wday.day"
                                    ></span>
                                    <span class="text-[11px] sm:text-xs font-medium text-gray-500 dark:text-gray-400 hidden lg:inline-block" x-text="wday.month_name"></span>
                                </div>

                                <div class="flex items-center gap-1 text-[9px] sm:text-[10px] text-gray-500 dark:text-gray-400 font-medium">
                                    <span x-text="wday.short_en"></span>
                                    <template x-if="wday.is_holiday">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 inline-block" title="تعطیل"></span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- ۲. ردیف رویدادهای تمام‌روز و مناسبت‌ها (All-Day Section) --}}
                <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 flex text-xs">
                    <div class="w-14 sm:w-16 flex-shrink-0 flex items-center justify-center p-1.5 text-[9px] sm:text-[10px] font-bold text-gray-500 dark:text-gray-400 select-none border-l border-gray-200 dark:border-gray-700">
                        <span>تمام‌روز</span>
                    </div>

                    <div class="grid grid-cols-7 flex-1 p-1.5 min-h-[38px] items-center">
                        <template x-for="wday in weekDays" :key="'allday_' + wday.key">
                            <div class="px-1 space-y-1 min-w-0 border-l border-gray-200 dark:border-gray-700 last:border-l-0 h-full flex flex-col justify-center">
                                <template x-for="ev in getDayAllDayEvents(wday)" :key="'ad_' + ev.id">
                                    <div 
                                        @click="showEventDetail(ev)"
                                        :style="getAllDayEventStyle(ev)"
                                        :class="{
                                            'bg-rose-100 dark:bg-rose-900/80 text-rose-800 dark:text-rose-200 border-rose-300 dark:border-rose-700 font-extrabold': ev.source === 'jalali_holidays' && ev.is_holiday,
                                            'bg-emerald-100 dark:bg-emerald-900/80 text-emerald-800 dark:text-emerald-200 border-emerald-300 dark:border-emerald-700': ev.source === 'jalali_holidays' && !ev.is_holiday,
                                            'bg-teal-100 dark:bg-teal-900/80 text-teal-800 dark:text-teal-200 border-teal-300 dark:border-teal-700': ev.source === 'google_calendar',
                                            'bg-amber-100 dark:bg-amber-900/80 text-amber-800 dark:text-amber-200 border-amber-300 dark:border-amber-700': ev.source === 'tasks',
                                            'bg-purple-100 dark:bg-purple-900/80 text-purple-800 dark:text-purple-200 border-purple-300 dark:border-purple-700': ev.source === 'reminders'
                                        }"
                                        class="px-1.5 py-0.5 rounded-lg border text-[10px] font-bold truncate cursor-pointer hover:opacity-90 shadow-2xs transition-all"
                                        :title="ev.title"
                                    >
                                        <span x-text="ev.title"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- ۳. جدول زمانی ۲۴ ساعته اسکرول‌پذیر (Google Calendar 24h Hourly Time Grid) --}}
                <div 
                    id="week-time-grid" 
                    :class="isFocusMode ? 'h-[68vh] sm:h-[73vh] max-h-[76vh]' : 'max-h-[640px] sm:max-h-[740px]'"
                    class="overflow-y-auto relative scroll-smooth bg-white dark:bg-gray-800"
                >
                    <div class="flex relative min-h-[1728px] h-[1728px] w-full">
                        
                        {{-- محور زمان (Time Gutter - ارتفاع هر ساعت ۷۲ پیکسل) --}}
                        <div class="w-14 sm:w-16 flex-shrink-0 relative border-l border-gray-200 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/60 select-none">
                            <template x-for="hour in 24" :key="'hmark_' + hour">
                                <div 
                                    class="absolute left-0 right-0 flex items-center justify-center -translate-y-1/2"
                                    :style="{ top: ((hour - 1) * 72) + 'px' }"
                                >
                                    <span 
                                        x-show="hour > 1"
                                        class="text-[11px] sm:text-xs font-semibold text-gray-500 dark:text-gray-400" 
                                        x-text="formatHour(hour - 1)"
                                    ></span>
                                </div>
                            </template>
                        </div>

                        {{-- شبکه ۷ ستون روزهای هفته (Day Columns & Grid) --}}
                        <div class="grid grid-cols-7 flex-1 relative">
                            
                            {{-- خطوط افقی سرتاسری پس‌زمینه (بازه‌های یک‌ساعته ۷۲ پیکسل و نیم‌ساعته ۳۶ پیکسل) --}}
                            <div class="absolute inset-0 pointer-events-none flex flex-col">
                                <template x-for="hour in 24" :key="'gridline_' + hour">
                                    <div class="h-[72px] min-h-[72px] max-h-[72px] flex-shrink-0 border-b border-gray-200/80 dark:border-gray-700/70 relative">
                                        {{-- خط‌چین نیم‌ساعت --}}
                                        <div class="absolute top-[36px] left-0 right-0 border-b border-dashed border-gray-100 dark:border-gray-700/40"></div>
                                    </div>
                                </template>
                            </div>

                            {{-- محتوای هر روز هفته --}}
                            <template x-for="wday in weekDays" :key="'tcol_' + wday.key">
                                <div 
                                    @click="handleWeekGridClick($event, wday)"
                                    :class="{
                                        'bg-rose-50/25 dark:bg-rose-950/20': wday.is_today,
                                        'cursor-pointer': allowEventCreation
                                    }"
                                    class="relative h-[1728px] min-h-[1728px] flex-1 min-w-0 border-l border-gray-200 dark:border-gray-700 last:border-l-0"
                                >
                                    {{-- خط نشانگر ساعت زنده جاری --}}
                                    <template x-if="wday.is_today">
                                        <div 
                                            :style="{ top: getCurrentTimeIndicatorTop() }"
                                            class="absolute left-0 right-0 z-20 pointer-events-none flex items-center -translate-y-1/2"
                                        >
                                            <span class="w-3 h-3 rounded-full bg-rose-500 -mr-1.5 shadow-sm ring-2 ring-white dark:ring-gray-800"></span>
                                            <div class="flex-1 border-t-2 border-rose-500 shadow-sm"></div>
                                        </div>
                                    </template>

                                    {{-- رویدادهای زمان‌دار این روز (موقعیت‌دهی دقیق و نمایش رویدادهای هم‌پوشان) --}}
                                    <template x-for="ev in getDayTimedEvents(wday)" :key="'gcal_ev_' + ev.id">
                                        <div 
                                            @click.stop="showEventDetail(ev)"
                                            :style="getEventCardStyle(ev)"
                                            class="absolute z-10 rounded-lg p-2 transition-all cursor-pointer hover:z-30 hover:shadow-lg flex flex-col justify-between overflow-hidden group select-none border text-right"
                                        >
                                            {{-- بخش عنوان و ساعت کارت --}}
                                            <div class="min-w-0 space-y-0.5 pointer-events-auto">
                                                <div class="flex items-center justify-between gap-1">
                                                    <span class="text-[10px] sm:text-[11px] font-bold truncate opacity-95" x-text="ev.time"></span>
                                                </div>

                                                <h4 class="text-xs sm:text-[13px] font-bold leading-snug line-clamp-2" x-text="ev.title"></h4>
                                            </div>

                                            {{-- جزئیات تکمیلی نوبت / سرویس (اگر ارتفاع کارت اجازه دهد) --}}
                                            <template x-if="ev.duration_minutes >= 35 && (ev.client_name || ev.provider_name)">
                                                <div class="text-[10px] opacity-85 pt-1 truncate flex items-center gap-1 border-t border-current/15 mt-1 font-medium">
                                                    <svg class="w-3 h-3 flex-shrink-0 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                    <span x-text="ev.client_name ? ('مشتری: ' + ev.client_name) : ev.description" class="truncate"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                </div>
                            </template>
                        </div>

                    </div>
                </div>

            </div>

        </div>

        {{-- ۳. نمای روزانه / جزئیات (دارای دو سطح نمایش ۱ و ۲) --}}
        <div x-show="viewMode === 'day'" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col transition-colors">
            {{-- هدر نمای روزانه (چیدمان استاندارد ۲ طرفه) --}}
            <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/60 select-none">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-11 h-11 rounded-2xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/60 flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2 flex-wrap">
                            <span>رویدادهای</span>
                            <span class="text-rose-600 dark:text-rose-400 font-black" x-text="selectedDayTitle"></span>
                            <template x-if="selectedDayIsToday">
                                <span class="bg-rose-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-sm">امروز</span>
                            </template>
                        </h2>
                        <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mt-0.5" x-text="selectedDayGregorian"></p>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <button @click="backFromDayView()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700/80 hover:bg-gray-200 dark:hover:bg-gray-600 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 transition-all duration-200 shadow-sm">
                        <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                        <span x-text="previousViewMode === 'week' ? 'بازگشت به تقویم هفتگی' : 'بازگشت به تقویم ماهانه'"></span>
                    </button>
                </div>
            </div>

            {{-- سطح ۱: نمایش کارتی کلاسیک لیست رویدادها --}}
            <div x-show="calendarLevel === 'level1'" class="p-4 sm:p-6 space-y-4">
                <template x-if="!selectedDayEvents || !selectedDayEvents.length">
                    <div class="py-12 text-center bg-gray-50 dark:bg-gray-900/40 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm font-bold text-gray-600 dark:text-gray-300">هیچ رویدادی برای این روز ثبت نشده است.</p>
                    </div>
                </template>

                <div class="space-y-3">
                    <template x-for="ev in selectedDayEvents" :key="ev.id">
                        <div 
                            @click="showEventDetail(ev)"
                            :style="getDayLevel1CardStyle(ev)"
                            class="p-4 rounded-2xl border bg-gray-50/70 dark:bg-gray-900/40 border-gray-200 dark:border-gray-700/80 hover:bg-white dark:hover:bg-gray-700/40 hover:shadow-md transition-all flex items-center justify-between cursor-pointer"
                        >
                            <div class="flex items-center gap-3">
                                <span 
                                    :style="(ev.source === 'booking' && ev.service_color) || (ev.source === 'custom_events' && ev.color) ? { backgroundColor: ev.service_color || ev.color } : {}"
                                    :class="{
                                        'bg-blue-500': ev.source === 'booking' && !ev.service_color,
                                        'bg-indigo-500': ev.source === 'custom_events' && !ev.color,
                                        'bg-amber-500': ev.source === 'tasks',
                                        'bg-purple-500': ev.source === 'reminders',
                                        'bg-rose-500': ev.source === 'jalali_holidays' && ev.is_holiday,
                                        'bg-emerald-500': ev.source === 'jalali_holidays' && !ev.is_holiday,
                                        'bg-teal-500': ev.source === 'google_calendar'
                                    }"
                                    class="w-3 h-3 rounded-full flex-shrink-0"
                                ></span>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100" x-text="ev.title"></h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="ev.description"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-gray-600 dark:text-gray-300" x-text="ev.time"></span>
                                <span 
                                    :style="(ev.source === 'booking' && ev.service_color) || (ev.source === 'custom_events' && ev.color) ? { backgroundColor: (ev.service_color || ev.color) + '20', color: ev.service_color || ev.color, borderColor: (ev.service_color || ev.color) + '50' } : {}"
                                    :class="{
                                        'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800/80': ev.source === 'booking' && !ev.service_color,
                                        'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800/80': ev.source === 'custom_events' && !ev.color,
                                        'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800/80': ev.source === 'tasks',
                                        'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800/80': ev.source === 'reminders',
                                        'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800/80': ev.source === 'jalali_holidays' && ev.is_holiday,
                                        'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800/80': ev.source === 'jalali_holidays' && !ev.is_holiday,
                                        'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/60 dark:text-teal-300 dark:border-teal-800/80': ev.source === 'google_calendar'
                                    }"
                                    class="text-xs font-bold px-2.5 py-1 rounded-xl border shadow-sm"
                                    x-text="ev.source_label"
                                ></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- سطح ۲: جدول زمانی ۲۴ ساعته روزانه پیشرفته Google Calendar (نمای روزانه گوگل کلندر) --}}
            <div x-show="calendarLevel === 'level2'" class="flex flex-col">
                
                {{-- ردیف رویدادهای تمام‌روز و مناسبت‌ها (All-Day Section) --}}
                <template x-if="getDayAllDayEventsForSelectedDay().length > 0">
                    <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 flex text-xs">
                        <div class="w-14 sm:w-16 flex-shrink-0 flex items-center justify-center p-2 text-[10px] sm:text-xs font-bold text-gray-500 dark:text-gray-400 select-none border-l border-gray-200 dark:border-gray-700">
                            <span>تمام‌روز</span>
                        </div>
                        <div class="flex-1 p-2 flex flex-wrap gap-2 items-center">
                            <template x-for="ev in getDayAllDayEventsForSelectedDay()" :key="'dad_' + ev.id">
                                <div 
                                    @click="showEventDetail(ev)"
                                    :style="getAllDayEventStyle(ev)"
                                    :class="{
                                        'bg-rose-100 dark:bg-rose-900/80 text-rose-800 dark:text-rose-200 border-rose-300 dark:border-rose-700 font-extrabold': ev.source === 'jalali_holidays' && ev.is_holiday,
                                        'bg-emerald-100 dark:bg-emerald-900/80 text-emerald-800 dark:text-emerald-200 border-emerald-300 dark:border-emerald-700': ev.source === 'jalali_holidays' && !ev.is_holiday,
                                        'bg-teal-100 dark:bg-teal-900/80 text-teal-800 dark:text-teal-200 border-teal-300 dark:border-teal-700': ev.source === 'google_calendar',
                                        'bg-amber-100 dark:bg-amber-900/80 text-amber-800 dark:text-amber-200 border-amber-300 dark:border-amber-700': ev.source === 'tasks',
                                        'bg-purple-100 dark:bg-purple-900/80 text-purple-800 dark:text-purple-200 border-purple-300 dark:border-purple-700': ev.source === 'reminders'
                                    }"
                                    class="px-2.5 py-1 rounded-lg border text-xs font-bold truncate cursor-pointer hover:opacity-90 shadow-2xs transition-all flex items-center gap-1.5"
                                    :title="ev.title"
                                >
                                    <span x-text="ev.title"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- جدول زمانی ۲۴ ساعته اسکرول‌پذیر روزانه (Daily 24h Time Grid) --}}
                <div 
                    id="day-time-grid" 
                    :class="isFocusMode ? 'h-[68vh] sm:h-[73vh] max-h-[76vh]' : 'max-h-[640px] sm:max-h-[740px]'"
                    class="overflow-y-auto relative scroll-smooth bg-white dark:bg-gray-800"
                >
                    <div class="flex relative min-h-[1728px] h-[1728px] w-full">
                        
                        {{-- محور زمان (Time Gutter) --}}
                        <div class="w-14 sm:w-16 flex-shrink-0 relative border-l border-gray-200 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/60 select-none">
                            <template x-for="hour in 24" :key="'dhmark_' + hour">
                                <div 
                                    class="absolute left-0 right-0 flex items-center justify-center -translate-y-1/2"
                                    :style="{ top: ((hour - 1) * 72) + 'px' }"
                                >
                                    <span 
                                        x-show="hour > 1"
                                        class="text-[11px] sm:text-xs font-semibold text-gray-500 dark:text-gray-400" 
                                        x-text="formatHour(hour - 1)"
                                    ></span>
                                </div>
                            </template>
                        </div>

                        {{-- ستون تمام‌عرض روز انتخابی --}}
                        <div class="flex-1 relative">
                            
                            {{-- خطوط افقی سرتاسری پس‌زمینه --}}
                            <div class="absolute inset-0 pointer-events-none flex flex-col">
                                <template x-for="hour in 24" :key="'dgridline_' + hour">
                                    <div class="h-[72px] min-h-[72px] max-h-[72px] flex-shrink-0 border-b border-gray-200/80 dark:border-gray-700/70 relative">
                                        {{-- خط‌چین نیم‌ساعت --}}
                                        <div class="absolute top-[36px] left-0 right-0 border-b border-dashed border-gray-100 dark:border-gray-700/40"></div>
                                    </div>
                                </template>
                            </div>

                            {{-- محتوای روز --}}
                            <div 
                                @click="handleDayGridClick($event)"
                                :class="{
                                    'cursor-pointer': allowEventCreation
                                }"
                                class="relative h-[1728px] min-h-[1728px] w-full"
                            >
                                
                                {{-- خط نشانگر ساعت زنده جاری (در صورتی که روز جاری باشد) --}}
                                <template x-if="selectedDayIsToday">
                                    <div 
                                        :style="{ top: getCurrentTimeIndicatorTop() }"
                                        class="absolute left-0 right-0 z-20 pointer-events-none flex items-center -translate-y-1/2"
                                    >
                                        <span class="w-3 h-3 rounded-full bg-rose-500 -mr-1.5 shadow-sm ring-2 ring-white dark:ring-gray-800"></span>
                                        <div class="flex-1 border-t-2 border-rose-500 shadow-sm"></div>
                                    </div>
                                </template>

                                {{-- رویدادهای زمان‌دار این روز با چیدمان موازی گوگل کلندر --}}
                                <template x-for="ev in getDayTimedEventsForSelectedDay()" :key="'gcal_dev_' + ev.id">
                                    <div 
                                        @click.stop="showEventDetail(ev)"
                                        :style="getEventCardStyle(ev)"
                                        class="absolute z-10 rounded-xl p-2.5 sm:p-3 transition-all cursor-pointer hover:z-30 hover:shadow-lg flex flex-col justify-between overflow-hidden group select-none border text-right"
                                    >
                                        {{-- بخش عنوان و ساعت --}}
                                        <div class="min-w-0 space-y-1 pointer-events-auto">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-xs sm:text-sm font-bold truncate opacity-95" x-text="ev.time"></span>
                                            </div>

                                            <h4 class="text-xs sm:text-sm font-black leading-snug line-clamp-2" x-text="ev.title"></h4>
                                        </div>

                                        {{-- جزئیات تکمیلی نوبت / سرویس / مشتری --}}
                                        <template x-if="ev.duration_minutes >= 30 && (ev.client_name || ev.description)">
                                            <div class="text-[11px] opacity-85 pt-1.5 truncate flex items-center gap-1.5 border-t border-current/15 mt-1 font-medium">
                                                <svg class="w-3.5 h-3.5 flex-shrink-0 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                <span x-text="ev.client_name ? ('مشتری: ' + ev.client_name) : ev.description" class="truncate"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                            </div>

                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- مدال جزئیات رویداد --}}
    <div x-show="activeEvent" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.away="activeEvent = null" class="bg-white dark:bg-gray-800 rounded-2xl max-w-lg w-full p-6 border border-gray-200 dark:border-gray-700 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <span 
                        :style="(activeEvent?.source === 'booking' && activeEvent?.service_color) || (activeEvent?.source === 'custom_events' && activeEvent?.color) ? { backgroundColor: activeEvent.service_color || activeEvent.color } : {}"
                        :class="{
                            'bg-blue-500': activeEvent?.source === 'booking' && !activeEvent?.service_color,
                            'bg-indigo-500': activeEvent?.source === 'custom_events' && !activeEvent?.color,
                            'bg-amber-500': activeEvent?.source === 'tasks',
                            'bg-purple-500': activeEvent?.source === 'reminders',
                            'bg-rose-500': activeEvent?.source === 'jalali_holidays' && activeEvent?.is_holiday,
                            'bg-emerald-500': activeEvent?.source === 'jalali_holidays' && !activeEvent?.is_holiday,
                            'bg-teal-500': activeEvent?.source === 'google_calendar'
                        }"
                        class="w-3 h-3 rounded-full"
                    ></span>
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400" x-text="activeEvent?.source_label"></span>
                    <template x-if="activeEvent?.service_name">
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300" x-text="activeEvent?.service_name"></span>
                    </template>
                </div>
                <button @click="activeEvent = null" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100" x-text="activeEvent?.title"></h3>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 mt-2 leading-relaxed whitespace-pre-line" x-text="activeEvent?.description"></p>
                
                {{-- شماره تماس مشتری (برای رویدادهای نوبت‌دهی) --}}
                <template x-if="activeEvent?.client_phone">
                    <div class="mt-2.5 text-xs font-medium text-gray-700 dark:text-gray-200 flex items-center justify-between bg-blue-50/70 dark:bg-blue-950/40 p-2.5 rounded-xl border border-blue-100 dark:border-blue-800/50">
                        <div class="flex items-center gap-1.5 text-blue-700 dark:text-blue-300 font-bold">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span>شماره تماس مشتری:</span>
                        </div>
                        <a :href="'tel:' + activeEvent.client_phone" class="font-bold text-blue-600 dark:text-blue-400 hover:underline dir-ltr text-right" x-text="activeEvent.client_phone"></a>
                    </div>
                </template>

                <template x-if="activeEvent?.location">
                    <div class="mt-2 text-xs font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1.5 bg-gray-50 dark:bg-gray-900/50 p-2 rounded-xl border border-gray-100 dark:border-gray-700">
                        <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span x-text="'مکان: ' + activeEvent.location"></span>
                    </div>
                </template>
            </div>

            <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between text-xs font-bold text-gray-600 dark:text-gray-400 flex-wrap gap-2">
                <span x-text="'تاریخ: ' + (activeEvent?.date_fa || '')"></span>
                <span x-text="'زمان: ' + (activeEvent?.time || '')"></span>
            </div>

            <div class="pt-2 flex items-center justify-between flex-wrap gap-2 border-t border-gray-100 dark:border-gray-700/60">
                <div>
                    <template x-if="activeEvent?.can_delete">
                        <button 
                            type="button" 
                            @click="deleteCurrentEvent()" 
                            :disabled="isSavingEvent" 
                            class="px-3.5 py-2 rounded-xl bg-red-50 hover:bg-red-100 dark:bg-red-950/50 dark:hover:bg-red-900/60 text-red-600 dark:text-red-400 text-xs font-bold transition-colors flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span>حذف رویداد</span>
                        </button>
                    </template>
                </div>

                <div class="flex items-center gap-2">
                    <template x-if="activeEvent?.can_edit">
                        <button 
                            type="button" 
                            @click="openEditEventModal(activeEvent)" 
                            class="px-4 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-300 text-xs font-bold transition-colors flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>ویرایش</span>
                        </button>
                    </template>

                    <button @click="activeEvent = null" class="px-5 py-2 rounded-xl bg-gray-100 dark:bg-gray-700/80 hover:bg-gray-200 dark:hover:bg-gray-600 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors">
                        بستن
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- مدال ایجاد / ویرایش سریع رویداد به سبک Google Calendar --}}
    <div x-show="isEventModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.away="closeEventModal()" class="bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full p-6 border border-gray-200 dark:border-gray-700 shadow-2xl space-y-5 transition-all animate-in fade-in zoom-in-95">
            
            {{-- هدر مدال --}}
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-black text-gray-900 dark:text-white" x-text="isEditingEvent ? 'ویرایش رویداد' : 'افزودن رویداد جدید'"></h3>
                </div>
                <button @click="closeEventModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- پیام خطا در صورت وجود --}}
            <template x-if="eventFormError">
                <div class="p-3 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/60 text-xs font-bold text-red-600 dark:text-red-400 flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-text="eventFormError"></span>
                </div>
            </template>

            {{-- فرم رویداد --}}
            <form @submit.prevent="saveEvent()" class="space-y-4">
                
                {{-- ۱. عنوان رویداد (ورودی برجسته) --}}
                <div>
                    <input 
                        type="text" 
                        x-ref="eventTitleInput"
                        x-model="eventForm.title" 
                        placeholder="افزودن عنوان رویداد..." 
                        class="w-full px-4 py-3 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/60 text-sm sm:text-base font-bold text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:bg-white dark:focus:bg-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all"
                        required
                    >
                </div>

                {{-- ۲. تاریخ و ساعت --}}
                <div class="p-4 rounded-2xl bg-gray-50/60 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">تاریخ (شمسی)</label>
                            <input 
                                type="text" 
                                x-model="eventForm.date_fa" 
                                placeholder="مثلاً: 1405/06/10" 
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-bold text-gray-900 dark:text-white dir-ltr text-center focus:ring-2 focus:ring-indigo-500/20"
                                required
                            >
                        </div>

                        <div class="flex items-center justify-between pt-6">
                            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" x-model="eventForm.is_all_day" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">تمام‌روز (All day)</span>
                            </label>
                        </div>
                    </div>

                    {{-- زمان شروع و پایان (در صورت تمام‌روز نبودن) --}}
                    <div x-show="!eventForm.is_all_day" class="grid grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">ساعت شروع</label>
                            <input 
                                type="time" 
                                x-model="eventForm.start_time" 
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-bold text-gray-900 dark:text-white dir-ltr text-center"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">ساعت پایان</label>
                            <input 
                                type="time" 
                                x-model="eventForm.end_time" 
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-bold text-gray-900 dark:text-white dir-ltr text-center"
                            >
                        </div>
                    </div>
                </div>

                {{-- ۳. انتخاب رنگ رویداد (پالت رنگی Google Calendar) --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">رنگ رویداد</label>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <template x-for="c in colorPalette" :key="c.hex">
                            <button 
                                type="button" 
                                @click="eventForm.color = c.hex" 
                                :style="{ backgroundColor: c.hex }"
                                :class="eventForm.color === c.hex ? 'ring-2 ring-offset-2 ring-indigo-500 scale-110 shadow-md' : 'hover:scale-105 opacity-85 hover:opacity-100'"
                                class="w-6 h-6 sm:w-7 sm:h-7 rounded-full transition-all flex items-center justify-center text-white"
                                :title="c.label"
                            >
                                <svg x-show="eventForm.color === c.hex" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- ۴. مکان / لینک جلسه --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">مکان یا لینک جلسه آنلاین (اختیاری)</label>
                    <input 
                        type="text" 
                        x-model="eventForm.location" 
                        placeholder="اتاق جلسه، آدرس یا لینک Google Meet / Zoom..." 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 text-xs font-medium text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900"
                    >
                </div>

                {{-- ۵. توضیحات و یادداشت --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">توضیحات و جزئیات رویداد (اختیاری)</label>
                    <textarea 
                        x-model="eventForm.description" 
                        rows="2" 
                        placeholder="یادداشت‌ها یا دستور جلسه..." 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 text-xs font-medium text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900"
                    ></textarea>
                </div>

                {{-- دکمه‌های اقدام --}}
                <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-2">
                    <button 
                        type="button" 
                        @click="closeEventModal()" 
                        class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700/80 hover:bg-gray-200 dark:hover:bg-gray-600 text-xs font-bold text-gray-700 dark:text-gray-300 transition-colors"
                    >
                        انصراف
                    </button>
                    <button 
                        type="submit" 
                        :disabled="isSavingEvent" 
                        class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/30 hover:shadow-lg transition-all flex items-center gap-1.5 disabled:opacity-50"
                    >
                        <template x-if="isSavingEvent">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-text="isSavingEvent ? 'در حال ذخیره...' : (isEditingEvent ? 'ویرایش رویداد' : 'ذخیره رویداد')"></span>
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

<script>
function fullCalendarManager() {
    const allAvailableSources = {!! json_encode(array_column($sources, 'key')) !!};

    const getInitialSources = () => {
        try {
            const stored = localStorage.getItem('calendar_selected_sources');
            if (stored !== null) {
                const parsed = JSON.parse(stored);
                if (Array.isArray(parsed)) {
                    return parsed.filter(key => allAvailableSources.includes(key));
                }
            }
        } catch (e) {
            console.error('Error reading saved calendar sources from localStorage:', e);
        }
        return [...allAvailableSources];
    };

    const getInitialViewMode = () => {
        try {
            const stored = localStorage.getItem('calendar_view_mode');
            if (stored === 'week' || stored === 'month' || stored === 'day') {
                return stored;
            }
        } catch (e) {}
        return 'month';
    };

    const getInitialCalendarLevel = () => {
        try {
            const stored = localStorage.getItem('calendar_display_level');
            if (stored === 'level1' || stored === 'level2') {
                return stored;
            }
        } catch (e) {}
        return 'level2';
    };

    return {
        isLoading: false,
        isFocusMode: false,
        allowEventCreation: {{ ($allowEventCreation ?? true) ? 'true' : 'false' }},
        viewMode: getInitialViewMode(),
        calendarLevel: getInitialCalendarLevel(),
        previousViewMode: 'month',
        jYear: {{ $todayJalali->getYear() }},
        jMonth: {{ $todayJalali->getMonth() }},
        currentDateEn: '{{ now()->format("Y-m-d") }}',
        currentMonthYearTitle: '{{ $todayJalali->format("%B %Y") }}',
        currentWeekTitle: '',
        gregorianTitle: '',
        availableSources: allAvailableSources,
        selectedSources: getInitialSources(),
        calendarCells: [],
        weekDays: [],
        eventsList: [],
        selectedDayNumber: null,
        selectedDayEvents: [],
        selectedDayTitle: '',
        selectedDayGregorian: '',
        selectedDayIsToday: false,
        activeEvent: null,

        // متغیرهای حالت مدال ایجاد / ویرایش رویداد
        isEventModalOpen: false,
        isSavingEvent: false,
        isEditingEvent: false,
        editEventId: null,
        eventFormError: null,
        colorPalette: [
            { hex: '#4f46e5', label: 'نیلی' },
            { hex: '#0284c7', label: 'آبی' },
            { hex: '#10b981', label: 'زمردی' },
            { hex: '#ea580c', label: 'نارنجی' },
            { hex: '#e11d48', label: 'یاقوتی' },
            { hex: '#9333ea', label: 'بنفش' },
            { hex: '#0d9488', label: 'فیروزه‌ای' },
            { hex: '#d97706', label: 'کهربایی' },
            { hex: '#475569', label: 'دودی' }
        ],
        eventForm: {
            title: '',
            date_fa: '',
            date_en: '',
            start_time: '09:00',
            end_time: '10:00',
            is_all_day: false,
            color: '#4f46e5',
            location: '',
            description: ''
        },

        get activeDisplayTitle() {
            if (this.viewMode === 'week') {
                return this.currentWeekTitle || this.currentMonthYearTitle;
            }
            return this.currentMonthYearTitle;
        },

        toggleFocusMode() {
            this.isFocusMode = !this.isFocusMode;
            this.$nextTick(() => {
                if (this.viewMode === 'week') {
                    this.scrollToCurrentTime('week-time-grid');
                } else if (this.viewMode === 'day') {
                    this.scrollToCurrentTime('day-time-grid');
                }
            });
        },

        initCalendar() {
            this.$watch('selectedSources', (val) => {
                try {
                    localStorage.setItem('calendar_selected_sources', JSON.stringify(val));
                } catch (e) {}
                this.fetchEvents();
            });
            this.fetchEvents();
        },

        changeViewMode(mode) {
            if (this.viewMode === mode) return;
            this.previousViewMode = this.viewMode;
            this.viewMode = mode;
            try {
                localStorage.setItem('calendar_view_mode', mode);
            } catch (e) {}
            this.fetchEvents();
            if (mode === 'week') {
                this.scrollToCurrentTime('week-time-grid');
            } else if (mode === 'day') {
                this.scrollToCurrentTime('day-time-grid');
            }
        },

        changeCalendarLevel(level) {
            this.calendarLevel = level;
            try {
                localStorage.setItem('calendar_display_level', level);
            } catch (e) {}
            if (level === 'level2') {
                if (this.viewMode === 'week') {
                    this.scrollToCurrentTime('week-time-grid');
                } else if (this.viewMode === 'day') {
                    this.scrollToCurrentTime('day-time-grid');
                }
            }
        },

        backFromDayView() {
            this.viewMode = (this.previousViewMode === 'week') ? 'week' : 'month';
            if (this.viewMode === 'week') {
                this.scrollToCurrentTime('week-time-grid');
            }
        },

        selectAllSources() {
            this.selectedSources = [...this.availableSources];
        },

        deselectAllSources() {
            this.selectedSources = [];
        },

        goToToday() {
            this.jYear = {{ $todayJalali->getYear() }};
            this.jMonth = {{ $todayJalali->getMonth() }};
            this.currentDateEn = '{{ now()->format("Y-m-d") }}';
            this.fetchEvents();
            if (this.viewMode === 'week') {
                this.scrollToCurrentTime('week-time-grid');
            } else if (this.viewMode === 'day') {
                this.scrollToCurrentTime('day-time-grid');
            }
        },

        prevPeriod() {
            if (this.viewMode === 'week') {
                if (this.currentDateEn) {
                    const d = new Date(this.currentDateEn);
                    d.setDate(d.getDate() - 7);
                    this.currentDateEn = d.toISOString().split('T')[0];
                }
            } else {
                if (this.jMonth === 1) {
                    this.jMonth = 12;
                    this.jYear--;
                } else {
                    this.jMonth--;
                }
            }
            this.fetchEvents();
        },

        nextPeriod() {
            if (this.viewMode === 'week') {
                if (this.currentDateEn) {
                    const d = new Date(this.currentDateEn);
                    d.setDate(d.getDate() + 7);
                    this.currentDateEn = d.toISOString().split('T')[0];
                }
            } else {
                if (this.jMonth === 12) {
                    this.jMonth = 1;
                    this.jYear++;
                } else {
                    this.jMonth++;
                }
            }
            this.fetchEvents();
        },

        async fetchEvents() {
            this.isLoading = true;
            const sourcesParam = (this.selectedSources && this.selectedSources.length) 
                ? this.selectedSources.join(',') 
                : '__none__';

            const queryView = (this.viewMode === 'week') ? 'week' : 'month';
            let url = `{{ route('user.calendar.events') }}?view=${queryView}&sources=${sourcesParam}`;

            if (queryView === 'week') {
                url += `&date=${this.currentDateEn}`;
            } else {
                url += `&jyear=${this.jYear}&jmonth=${this.jMonth}`;
            }

            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) return;

                const data = await response.json();
                
                if (queryView === 'week') {
                    this.currentWeekTitle = data.title || '';
                    this.gregorianTitle = data.gregorian_range || '';
                    this.weekDays = data.days || [];
                    this.eventsList = data.events || [];
                    this.scrollToCurrentTime('week-time-grid');
                } else {
                    this.currentMonthYearTitle = data.month_title || '';
                    this.gregorianTitle = data.gregorian_range || '';
                    this.calendarCells = data.cells || [];
                    this.eventsList = data.events || [];
                }

                if (this.selectedDayNumber) {
                    if (queryView === 'week') {
                        const currentWDay = this.weekDays.find(w => w.day === this.selectedDayNumber);
                        this.selectedDayEvents = currentWDay ? (currentWDay.events || []) : [];
                    } else {
                        const currentCell = this.calendarCells.find(c => c.day === this.selectedDayNumber);
                        this.selectedDayEvents = currentCell ? (currentCell.events || []) : [];
                    }
                }
            } catch (err) {
                console.error('Error fetching calendar events:', err);
            } finally {
                this.isLoading = false;
            }
        },

        selectDay(cell) {
            if (!cell.day) return;
            this.previousViewMode = (this.viewMode === 'week') ? 'week' : 'month';
            this.selectedDayNumber = cell.day;
            this.selectedDayTitle = `${cell.day} ${this.currentMonthYearTitle}`;
            this.selectedDayGregorian = cell.formattedEn ? `معادل میلادی: ${cell.formattedEn}` : '';
            this.selectedDayEvents = cell.events || [];
            this.selectedDayIsToday = !!cell.isToday || !!cell.is_today;
            this.viewMode = 'day';
            this.$nextTick(() => {
                this.scrollToCurrentTime('day-time-grid');
            });
        },

        showEventDetail(ev) {
            this.activeEvent = ev;
        },

        // متدهای ایجاد و مدیریت رویداد تقویم
        openCreateEventModal(defaults = {}) {
            if (!this.allowEventCreation) return;
            this.isEditingEvent = false;
            this.editEventId = null;
            this.eventFormError = null;

            const now = new Date();
            const currentHour = now.getHours();
            const startH = (currentHour + 1) % 24;
            const endH = (startH + 1) % 24;

            const defaultDateFa = `${this.jYear}/${this.jMonth.toString().padStart(2, '0')}/${(this.selectedDayNumber || {{ $todayJalali->getDay() }}).toString().padStart(2, '0')}`;

            this.eventForm = {
                title: defaults.title || '',
                date_fa: defaults.date_fa || defaultDateFa,
                date_en: defaults.date_en || '',
                start_time: defaults.start_time || `${startH.toString().padStart(2, '0')}:00`,
                end_time: defaults.end_time || `${endH.toString().padStart(2, '0')}:00`,
                is_all_day: !!defaults.is_all_day,
                color: defaults.color || '#4f46e5',
                location: defaults.location || '',
                description: defaults.description || ''
            };

            this.isEventModalOpen = true;
            this.$nextTick(() => {
                this.$refs.eventTitleInput?.focus();
            });
        },

        openEditEventModal(ev) {
            if (!ev || !ev.can_edit) return;
            this.isEditingEvent = true;
            this.editEventId = ev.raw_id;
            this.eventFormError = null;

            this.eventForm = {
                title: ev.title || '',
                date_fa: ev.date_fa || '',
                date_en: ev.date_en || '',
                start_time: ev.start_time || '09:00',
                end_time: ev.end_time || '10:00',
                is_all_day: !!ev.is_all_day,
                color: ev.color || '#4f46e5',
                location: ev.location || '',
                description: ev.description || ''
            };

            this.isEventModalOpen = true;
            this.$nextTick(() => {
                this.$refs.eventTitleInput?.focus();
            });
        },

        closeEventModal() {
            this.isEventModalOpen = false;
            this.isEditingEvent = false;
            this.editEventId = null;
            this.eventFormError = null;
        },

        async saveEvent() {
            if (!this.eventForm.title.trim()) {
                this.eventFormError = 'لطفاً عنوان رویداد را وارد نمایید.';
                return;
            }

            this.isSavingEvent = true;
            this.eventFormError = null;

            const url = this.isEditingEvent 
                ? `{{ url('/user/calendar/events') }}/${this.editEventId}` 
                : `{{ route('user.calendar.events.store') }}`;

            const method = this.isEditingEvent ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.eventForm)
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.closeEventModal();
                    if (this.activeEvent && this.isEditingEvent) {
                        this.activeEvent = null;
                    }
                    await this.fetchEvents();
                } else {
                    this.eventFormError = data.message || (data.errors ? Object.values(data.errors).flat().join(' - ') : 'خطایی در ثبت اطلاعات رخ داد.');
                }
            } catch (err) {
                console.error('Error saving calendar event:', err);
                this.eventFormError = 'خطای ارتباط با سرور. لطفاً دوباره تلاش کنید.';
            } finally {
                this.isSavingEvent = false;
            }
        },

        async deleteCurrentEvent() {
            if (!this.activeEvent || !this.activeEvent.raw_id) return;

            if (!confirm('آیا از حذف این رویداد از تقویم اطمینان دارید؟')) {
                return;
            }

            this.isSavingEvent = true;
            const url = `{{ url('/user/calendar/events') }}/${this.activeEvent.raw_id}`;

            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.activeEvent = null;
                    await this.fetchEvents();
                } else {
                    alert(data.message || 'خطا در حذف رویداد.');
                }
            } catch (err) {
                console.error('Error deleting calendar event:', err);
                alert('خطای ارتباط با سرور.');
            } finally {
                this.isSavingEvent = false;
            }
        },

        handleWeekGridClick(event, wday) {
            if (!this.allowEventCreation) return;
            const rect = event.currentTarget.getBoundingClientRect();
            const offsetY = event.clientY - rect.top;
            const totalMinutes = Math.floor((offsetY / 72) * 60);
            const roundedMinutes = Math.floor(totalMinutes / 15) * 15;
            const startH = Math.max(0, Math.min(23, Math.floor(roundedMinutes / 60)));
            const startM = roundedMinutes % 60;
            const endH = Math.min(23, startH + 1);
            const endM = startM;

            const startTime = `${startH.toString().padStart(2, '0')}:${startM.toString().padStart(2, '0')}`;
            const endTime = `${endH.toString().padStart(2, '0')}:${endM.toString().padStart(2, '0')}`;

            const dateFa = `${wday.year || this.jYear}/${(wday.month || this.jMonth).toString().padStart(2, '0')}/${(wday.day).toString().padStart(2, '0')}`;

            this.openCreateEventModal({
                date_fa: dateFa,
                date_en: wday.date_en || '',
                start_time: startTime,
                end_time: endTime
            });
        },

        handleDayGridClick(event) {
            if (!this.allowEventCreation) return;
            const rect = event.currentTarget.getBoundingClientRect();
            const offsetY = event.clientY - rect.top;
            const totalMinutes = Math.floor((offsetY / 72) * 60);
            const roundedMinutes = Math.floor(totalMinutes / 15) * 15;
            const startH = Math.max(0, Math.min(23, Math.floor(roundedMinutes / 60)));
            const startM = roundedMinutes % 60;
            const endH = Math.min(23, startH + 1);
            const endM = startM;

            const startTime = `${startH.toString().padStart(2, '0')}:${startM.toString().padStart(2, '0')}`;
            const endTime = `${endH.toString().padStart(2, '0')}:${endM.toString().padStart(2, '0')}`;

            const dateFa = `${this.jYear}/${this.jMonth.toString().padStart(2, '0')}/${(this.selectedDayNumber || {{ $todayJalali->getDay() }}).toString().padStart(2, '0')}`;

            this.openCreateEventModal({
                date_fa: dateFa,
                start_time: startTime,
                end_time: endTime
            });
        },

        formatHour(hour) {
            const h = hour.toString().padStart(2, '0');
            return `${h}:00`;
        },

        getDayAllDayEvents(wday) {
            return (wday.events || []).filter(e => e.is_all_day);
        },

        getDayAllDayEventsForSelectedDay() {
            return (this.selectedDayEvents || []).filter(e => e.is_all_day);
        },

        getDayTimedEventsForSelectedDay() {
            const timed = (this.selectedDayEvents || []).filter(e => !e.is_all_day && typeof e.start_minute === 'number');
            if (!timed.length) return [];

            const sorted = [...timed].sort((a, b) => a.start_minute - b.start_minute || b.duration_minutes - a.duration_minutes);

            const clusters = [];
            let currentCluster = [];
            let clusterEnd = -1;

            for (const ev of sorted) {
                const evEnd = ev.start_minute + Math.max(15, ev.duration_minutes || 60);
                if (currentCluster.length === 0 || ev.start_minute < clusterEnd) {
                    currentCluster.push(ev);
                    clusterEnd = Math.max(clusterEnd, evEnd);
                } else {
                    clusters.push(currentCluster);
                    currentCluster = [ev];
                    clusterEnd = evEnd;
                }
            }
            if (currentCluster.length) clusters.push(currentCluster);

            const HOUR_HEIGHT = 72;
            const result = [];
            for (const cluster of clusters) {
                const columns = [];
                for (const ev of cluster) {
                    let placed = false;
                    for (let colIdx = 0; colIdx < columns.length; colIdx++) {
                        const lastInCol = columns[colIdx][columns[colIdx].length - 1];
                        const lastEnd = lastInCol.start_minute + Math.max(15, lastInCol.duration_minutes || 60);
                        if (ev.start_minute >= lastEnd) {
                            columns[colIdx].push(ev);
                            ev._col = colIdx;
                            placed = true;
                            break;
                        }
                    }
                    if (!placed) {
                        ev._col = columns.length;
                        columns.push([ev]);
                    }
                }

                const totalCols = columns.length;
                for (const ev of cluster) {
                    const col = ev._col || 0;
                    const widthPct = 100 / totalCols;
                    const rightPct = col * widthPct;
                    const topPx = (ev.start_minute / 60) * HOUR_HEIGHT;
                    const heightPx = Math.max(32, ((ev.duration_minutes || 60) / 60) * HOUR_HEIGHT);

                    result.push({
                        ...ev,
                        _layout: {
                            top: `${topPx}px`,
                            height: `${heightPx}px`,
                            right: `calc(${rightPct}% + 3px)`,
                            width: `calc(${widthPct}% - 6px)`,
                        }
                    });
                }
            }

            return result;
        },

        getDayTimedEvents(wday) {
            const timed = (wday.events || []).filter(e => !e.is_all_day && typeof e.start_minute === 'number');
            if (!timed.length) return [];

            const sorted = [...timed].sort((a, b) => a.start_minute - b.start_minute || b.duration_minutes - a.duration_minutes);

            const clusters = [];
            let currentCluster = [];
            let clusterEnd = -1;

            for (const ev of sorted) {
                const evEnd = ev.start_minute + Math.max(15, ev.duration_minutes || 60);
                if (currentCluster.length === 0 || ev.start_minute < clusterEnd) {
                    currentCluster.push(ev);
                    clusterEnd = Math.max(clusterEnd, evEnd);
                } else {
                    clusters.push(currentCluster);
                    currentCluster = [ev];
                    clusterEnd = evEnd;
                }
            }
            if (currentCluster.length) clusters.push(currentCluster);

            const HOUR_HEIGHT = 72;

            const result = [];
            for (const cluster of clusters) {
                const columns = [];
                for (const ev of cluster) {
                    let placed = false;
                    for (let colIdx = 0; colIdx < columns.length; colIdx++) {
                        const lastInCol = columns[colIdx][columns[colIdx].length - 1];
                        const lastEnd = lastInCol.start_minute + Math.max(15, lastInCol.duration_minutes || 60);
                        if (ev.start_minute >= lastEnd) {
                            columns[colIdx].push(ev);
                            ev._col = colIdx;
                            placed = true;
                            break;
                        }
                    }
                    if (!placed) {
                        ev._col = columns.length;
                        columns.push([ev]);
                    }
                }

                const totalCols = columns.length;
                for (const ev of cluster) {
                    const col = ev._col || 0;
                    const widthPct = 100 / totalCols;
                    const rightPct = col * widthPct;
                    const topPx = (ev.start_minute / 60) * HOUR_HEIGHT;
                    const heightPx = Math.max(30, ((ev.duration_minutes || 60) / 60) * HOUR_HEIGHT);

                    result.push({
                        ...ev,
                        _layout: {
                            top: `${topPx}px`,
                            height: `${heightPx}px`,
                            right: `calc(${rightPct}% + 2px)`,
                            width: `calc(${widthPct}% - 4px)`,
                        }
                    });
                }
            }

            return result;
        },

        getEventCardStyle(ev) {
            const base = {
                top: ev._layout?.top || '0px',
                height: ev._layout?.height || '30px',
                right: ev._layout?.right || '0%',
                width: ev._layout?.width || '100%',
            };

            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#f8fafc' : '#0f172a';

            // ۱. نوبت‌ها (با اولویت رنگ اختصاصی سرویس)
            if (ev.source === 'booking') {
                const color = ev.service_color || '#0284c7';
                const hex = color.startsWith('#') ? color : '#' + color;
                return {
                    ...base,
                    backgroundColor: hex + (isDark ? '35' : '1e'),
                    borderColor: hex + (isDark ? '70' : '50'),
                    borderRightWidth: '4px',
                    borderRightColor: hex,
                    color: textColor,
                };
            }

            // ۲. رویدادهای مستقل تقویم (Custom Events)
            if (ev.source === 'custom_events') {
                const color = ev.color || '#4f46e5';
                const hex = color.startsWith('#') ? color : '#' + color;
                return {
                    ...base,
                    backgroundColor: hex + (isDark ? '35' : '1e'),
                    borderColor: hex + (isDark ? '70' : '50'),
                    borderRightWidth: '4px',
                    borderRightColor: hex,
                    color: textColor,
                };
            }

            // ۳. رویدادهای گوگل کلندر
            if (ev.source === 'google_calendar') {
                return {
                    ...base,
                    backgroundColor: isDark ? '#0d948835' : '#0d94881e',
                    borderColor: isDark ? '#0d948870' : '#0d948850',
                    borderRightWidth: '4px',
                    borderRightColor: '#0d9488',
                    color: textColor,
                };
            }

            // ۴. وظایف
            if (ev.source === 'tasks') {
                return {
                    ...base,
                    backgroundColor: isDark ? '#d9770635' : '#d977061e',
                    borderColor: isDark ? '#d9770670' : '#d9770650',
                    borderRightWidth: '4px',
                    borderRightColor: '#d97706',
                    color: textColor,
                };
            }

            // ۵. یادآوری‌ها
            if (ev.source === 'reminders') {
                return {
                    ...base,
                    backgroundColor: isDark ? '#7c3aed35' : '#7c3aed1e',
                    borderColor: isDark ? '#7c3aed70' : '#7c3aed50',
                    borderRightWidth: '4px',
                    borderRightColor: '#7c3aed',
                    color: textColor,
                };
            }

            return base;
        },

        getAllDayEventStyle(ev) {
            if (ev.source === 'custom_events') {
                const color = ev.color || '#4f46e5';
                const hex = color.startsWith('#') ? color : '#' + color;
                return {
                    backgroundColor: hex,
                    color: '#ffffff',
                    borderColor: hex,
                };
            }
            if (ev.source === 'booking' && ev.service_color) {
                return {
                    backgroundColor: ev.service_color,
                    color: '#ffffff',
                    borderColor: ev.service_color,
                };
            }
            return {};
        },

        getMonthEventStyle(ev) {
            const isDark = document.documentElement.classList.contains('dark');
            if (ev.source === 'custom_events') {
                const color = ev.color || '#4f46e5';
                const hex = color.startsWith('#') ? color : '#' + color;
                if (ev.is_all_day) {
                    return {
                        backgroundColor: hex,
                        color: '#ffffff',
                        borderColor: hex,
                    };
                }
                return {
                    backgroundColor: hex + (isDark ? '30' : '18'),
                    borderColor: hex + (isDark ? '60' : '40'),
                    borderRightColor: hex,
                    borderRightWidth: '3px',
                    color: isDark ? '#f8fafc' : '#0f172a',
                };
            }
            if (ev.source === 'booking' && ev.service_color) {
                if (ev.is_all_day) {
                    return {
                        backgroundColor: ev.service_color,
                        color: '#ffffff',
                        borderColor: ev.service_color,
                    };
                }
                return {
                    backgroundColor: ev.service_color + (isDark ? '30' : '18'),
                    borderColor: ev.service_color + (isDark ? '60' : '40'),
                    borderRightColor: ev.service_color,
                    borderRightWidth: '3px',
                    color: isDark ? '#f8fafc' : '#0f172a',
                };
            }
            return {};
        },

        getWeekLevel1CardStyle(ev) {
            const isDark = document.documentElement.classList.contains('dark');
            if (ev.source === 'custom_events') {
                const color = ev.color || '#4f46e5';
                const hex = color.startsWith('#') ? color : '#' + color;
                return {
                    backgroundColor: hex + (isDark ? '25' : '15'),
                    borderColor: hex + (isDark ? '60' : '40'),
                    borderRightColor: hex,
                    borderRightWidth: '4px',
                    color: isDark ? '#f8fafc' : '#0f172a',
                };
            }
            if (ev.source === 'booking' && ev.service_color) {
                return {
                    backgroundColor: ev.service_color + (isDark ? '25' : '15'),
                    borderColor: ev.service_color + (isDark ? '60' : '40'),
                    borderRightColor: ev.service_color,
                    borderRightWidth: '4px',
                    color: isDark ? '#f8fafc' : '#0f172a',
                };
            }
            return {};
        },

        getDayLevel1CardStyle(ev) {
            if (ev.source === 'custom_events') {
                const color = ev.color || '#4f46e5';
                const hex = color.startsWith('#') ? color : '#' + color;
                return {
                    borderRightColor: hex,
                    borderRightWidth: '4px',
                };
            }
            if (ev.source === 'booking' && ev.service_color) {
                return {
                    borderRightColor: ev.service_color,
                    borderRightWidth: '4px',
                };
            }
            return {};
        },

        getCurrentTimeIndicatorTop() {
            const now = new Date();
            const minutes = now.getHours() * 60 + now.getMinutes();
            const topPx = (minutes / 60) * 72;
            return `${topPx}px`;
        },

        scrollToCurrentTime(targetId = null) {
            this.$nextTick(() => {
                const id = targetId || (this.viewMode === 'day' ? 'day-time-grid' : 'week-time-grid');
                const grid = document.getElementById(id);
                if (grid) {
                    const now = new Date();
                    const targetHour = Math.max(0, now.getHours() - 1);
                    grid.scrollTop = targetHour * 72;
                }
            });
        }
    };
}
</script>
@endsection
