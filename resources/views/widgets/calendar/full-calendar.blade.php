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

<div class="space-y-4" x-data="fullCalendarManager()" x-init="initCalendar()">

    {{-- هدر صفحه و دکمه‌های ناوبری --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
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
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300" x-text="currentMonthYearTitle"></span>
                    </h1>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-2">
                        <span>معادل میلادی:</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400" x-text="gregorianTitle"></span>
                    </p>
                </div>
            </div>
        </div>

        {{-- کنترل‌های تغییر ماه و امروز --}}
        <div class="flex items-center gap-2.5 self-end md:self-auto">
            <button @click="goToToday()" class="px-4 py-2.5 rounded-xl bg-rose-50 dark:bg-rose-900/30 hover:bg-rose-100 text-rose-700 dark:text-rose-300 font-bold text-xs sm:text-sm transition-colors flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>امروز</span>
            </button>

            <div class="flex items-center bg-gray-100 dark:bg-gray-700/60 p-1 rounded-xl border border-gray-200 dark:border-gray-600/60">
                <button @click="prevMonth()" class="p-2 rounded-lg hover:bg-white dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition-colors" title="ماه قبل">
                    <svg class="w-5 h-5 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <span class="px-4 text-xs sm:text-sm font-bold text-gray-900 dark:text-gray-100" x-text="currentMonthYearTitle"></span>
                <button @click="nextMonth()" class="p-2 rounded-lg hover:bg-white dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition-colors" title="ماه بعد">
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
            <span class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300">فیلتر منابع:</span>
            @foreach($sources as $src)
                <label class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl border text-xs sm:text-sm font-bold cursor-pointer transition-all select-none
                    {{ $src['key'] === 'booking' ? 'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-900/30 dark:border-blue-800 dark:text-blue-300' : '' }}
                    {{ $src['key'] === 'tasks' ? 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-900/30 dark:border-amber-800 dark:text-amber-300' : '' }}
                    {{ $src['key'] === 'reminders' ? 'bg-purple-50 border-purple-200 text-purple-800 dark:bg-purple-900/30 dark:border-purple-800 dark:text-purple-300' : '' }}
                    {{ $src['key'] === 'jalali_holidays' ? 'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-900/30 dark:border-rose-800 dark:text-rose-300' : '' }}
                ">
                    <input type="checkbox" value="{{ $src['key'] }}" x-model="selectedSources" @change="fetchEvents()" class="rounded text-indigo-600 focus:ring-indigo-500 w-4 h-4">
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
        </div>
    </div>

    {{-- شبکه تقویم ماهانه --}}
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

        {{-- خانه‌های تقویم --}}
        <div class="grid grid-cols-7 divide-x divide-x-reverse divide-y divide-gray-200 dark:divide-gray-700 overflow-x-hidden">
            <template x-for="cell in calendarCells" :key="cell.key">
                <div 
                    @click="selectDay(cell)"
                    :class="{
                        'bg-gray-50/40 dark:bg-gray-900/40 opacity-40 select-none': !cell.day,
                        'bg-rose-50/50 dark:bg-rose-950/30 ring-2 ring-rose-500/50 z-10': cell.isToday,
                        'hover:bg-gray-50/80 dark:hover:bg-gray-700/50 cursor-pointer': cell.day
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
                                class="hidden md:inline-block text-[10px] sm:text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded-md" 
                                :title="'تاریخ معادل میلادی: ' + cell.formattedEn"
                            ></span>
                        </div>
                        <span x-show="cell.events && cell.events.length" x-text="cell.events.length + ' مورد'" class="text-[10px] sm:text-xs font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/40 px-1.5 py-0.5 rounded-md"></span>
                    </div>

                    {{-- رویدادهای روز (طراحی تمیز بدون اسکرول‌بار تداخل‌کننده) --}}
                    <div class="space-y-1 my-1 overflow-y-auto no-scrollbar max-h-[80px] sm:max-h-[105px] w-full">
                        <template x-for="ev in (cell.events || []).slice(0, 3)" :key="ev.id">
                            <div 
                                @click.stop="showEventDetail(ev)"
                                :class="{
                                    'bg-blue-50 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200 border-blue-200 dark:border-blue-800': ev.source === 'booking',
                                    'bg-amber-50 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200 border-amber-200 dark:border-amber-800': ev.source === 'tasks',
                                    'bg-purple-50 dark:bg-purple-900/40 text-purple-800 dark:text-purple-200 border-purple-200 dark:border-purple-800': ev.source === 'reminders',
                                    'bg-rose-50 dark:bg-rose-900/40 text-rose-800 dark:text-rose-200 border-rose-200 dark:border-rose-800 font-extrabold': ev.source === 'jalali_holidays' && ev.is_holiday,
                                    'bg-emerald-50 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-200 border-emerald-200 dark:border-emerald-800': ev.source === 'jalali_holidays' && !ev.is_holiday
                                }"
                                class="px-2 py-1 rounded-xl text-[10px] sm:text-xs font-bold border truncate hover:brightness-95 dark:hover:brightness-110 transition-all cursor-pointer shadow-sm flex items-center justify-between gap-1 w-full min-w-0"
                            >
                                <span x-text="ev.title" class="truncate"></span>
                                <span x-text="ev.time" class="hidden sm:inline-block text-[10px] sm:text-xs opacity-90 mr-1 flex-shrink-0"></span>
                            </div>
                        </template>

                        <template x-if="cell.events && cell.events.length > 3">
                            <div class="text-[10px] sm:text-xs font-bold text-indigo-600 dark:text-indigo-400 text-center pt-0.5">
                                + <span x-text="cell.events.length - 3"></span> مورد دیگر
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- نمای روزانه / جزئیات --}}
    <div x-show="viewMode === 'day'" class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
        {{-- هدر نمای روزانه (چیدمان استاندارد ۲ طرفه) --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center w-10 h-10 rounded-2xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2 flex-wrap">
                        <span>رویدادهای</span>
                        <span class="text-rose-600 dark:text-rose-400 font-black" x-text="selectedDayTitle"></span>
                    </h2>
                    <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mt-0.5" x-text="selectedDayGregorian"></p>
                </div>
            </div>

            <button @click="viewMode = 'month'" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700/80 hover:bg-gray-200 dark:hover:bg-gray-700 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 transition-all duration-200 shadow-sm self-start sm:self-auto">
                <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
                <span>بازگشت به تقویم ماهانه</span>
            </button>
        </div>

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
                    class="p-4 rounded-2xl border bg-gray-50 dark:bg-gray-900/40 border-gray-200 dark:border-gray-700 hover:shadow-md transition-all flex items-center justify-between cursor-pointer"
                >
                    <div class="flex items-center gap-3">
                        <span 
                            :class="{
                                'bg-blue-500': ev.source === 'booking',
                                'bg-amber-500': ev.source === 'tasks',
                                'bg-purple-500': ev.source === 'reminders',
                                'bg-rose-500': ev.source === 'jalali_holidays' && ev.is_holiday,
                                'bg-emerald-500': ev.source === 'jalali_holidays' && !ev.is_holiday
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
                            :class="{
                                'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300': ev.source === 'booking',
                                'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300': ev.source === 'tasks',
                                'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300': ev.source === 'reminders',
                                'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300': ev.source === 'jalali_holidays' && ev.is_holiday,
                                'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300': ev.source === 'jalali_holidays' && !ev.is_holiday
                            }"
                            class="text-xs font-bold px-2.5 py-1 rounded-xl"
                            x-text="ev.source_label"
                        ></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- مدال جزئیات رویداد --}}
    <div x-show="activeEvent" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.away="activeEvent = null" class="bg-white dark:bg-gray-800 rounded-2xl max-w-lg w-full p-6 border border-gray-200 dark:border-gray-700 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <span 
                        :class="{
                            'bg-blue-500': activeEvent?.source === 'booking',
                            'bg-amber-500': activeEvent?.source === 'tasks',
                            'bg-purple-500': activeEvent?.source === 'reminders',
                            'bg-rose-500': activeEvent?.source === 'jalali_holidays' && activeEvent?.is_holiday,
                            'bg-emerald-500': activeEvent?.source === 'jalali_holidays' && !activeEvent?.is_holiday
                        }"
                        class="w-3 h-3 rounded-full"
                    ></span>
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400" x-text="activeEvent?.source_label"></span>
                </div>
                <button @click="activeEvent = null" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100" x-text="activeEvent?.title"></h3>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 mt-2 leading-relaxed whitespace-pre-line" x-text="activeEvent?.description"></p>
            </div>

            <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between text-xs font-bold text-gray-600 dark:text-gray-400">
                <span x-text="'تاریخ: ' + (activeEvent?.date_fa || '')"></span>
                <span x-text="'زمان: ' + (activeEvent?.time || '')"></span>
            </div>

            <div class="pt-2 flex justify-end">
                <button @click="activeEvent = null" class="px-5 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200">
                    بستن
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function fullCalendarManager() {
    return {
        viewMode: 'month',
        jYear: {{ $todayJalali->getYear() }},
        jMonth: {{ $todayJalali->getMonth() }},
        currentMonthYearTitle: '{{ $todayJalali->format("%B %Y") }}',
        gregorianTitle: '',
        selectedSources: ['booking', 'tasks', 'reminders', 'jalali_holidays'],
        calendarCells: [],
        eventsList: [],
        selectedDayEvents: [],
        selectedDayTitle: '',
        selectedDayGregorian: '',
        activeEvent: null,

        initCalendar() {
            this.fetchEvents();
        },

        goToToday() {
            this.jYear = {{ $todayJalali->getYear() }};
            this.jMonth = {{ $todayJalali->getMonth() }};
            this.fetchEvents();
        },

        prevMonth() {
            if (this.jMonth === 1) {
                this.jMonth = 12;
                this.jYear--;
            } else {
                this.jMonth--;
            }
            this.fetchEvents();
        },

        nextMonth() {
            if (this.jMonth === 12) {
                this.jMonth = 1;
                this.jYear++;
            } else {
                this.jMonth++;
            }
            this.fetchEvents();
        },

        async fetchEvents() {
            const sourcesParam = this.selectedSources.join(',');
            const url = `{{ route('user.calendar.events') }}?jyear=${this.jYear}&jmonth=${this.jMonth}&sources=${sourcesParam}`;

            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) return;

                const data = await response.json();
                this.currentMonthYearTitle = data.month_title || '';
                this.gregorianTitle = data.gregorian_range || '';
                this.calendarCells = data.cells || [];
                this.eventsList = data.events || [];
            } catch (err) {
                console.error('Error fetching calendar events:', err);
            }
        },

        selectDay(cell) {
            if (!cell.day) return;
            this.selectedDayTitle = `${cell.day} ${this.currentMonthYearTitle}`;
            this.selectedDayGregorian = cell.formattedEn ? `معادل میلادی: ${cell.formattedEn}` : '';
            this.selectedDayEvents = cell.events || [];
            this.viewMode = 'day';
        },

        showEventDetail(ev) {
            this.activeEvent = ev;
        }
    };
}
</script>
@endsection
