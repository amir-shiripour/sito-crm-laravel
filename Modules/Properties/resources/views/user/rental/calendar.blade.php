@extends('layouts.user')

@php
    $title = 'تقویم اقامتگاه: ' . $property->title;
    $cardClass = "bg-white dark:bg-gray-800/90 backdrop-blur-md rounded-2xl sm:rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-sm overflow-hidden transition-all duration-200";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5 font-sans";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-2.5 text-xs text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-100 dark:focus:bg-gray-900 font-sans";
    $currencyLabel = $currency === 'toman' ? 'تومان' : 'ریال';

    // محاسبه ماه قبل و بعد
    $prevMonth = $currentMonth == 1 ? 12 : $currentMonth - 1;
    $prevYear = $currentMonth == 1 ? $currentYear - 1 : $currentYear;

    $nextMonth = $currentMonth == 12 ? 1 : $currentMonth + 1;
    $nextYear = $currentMonth == 12 ? $currentYear + 1 : $currentYear;
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 py-4 sm:py-8 space-y-5 sm:space-y-7 font-sans" x-data="rentalCalendar()">

    {{-- هدر صفحه --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white flex items-center gap-2 sm:gap-2.5 font-sans">
                <span class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl sm:rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/50 flex items-center justify-center shadow-xs shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </span>
                <span class="truncate">تقویم رزرو و قیمت: {{ $property->title }}</span>
            </h1>
            <p class="text-[11px] sm:text-xs text-gray-500 dark:text-slate-400 mt-1 sm:mt-1.5 mr-10 sm:mr-11 font-sans">
                وضعیت تقویم، نرخ‌های فصلی و مسدودسازی روزها را در این بخش مدیریت کنید. با کلیک روی هر روز وضعیت مسدودی آن تغییر می‌کند.
            </p>
        </div>

        <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('user.properties.rental.config', $property) }}" 
               class="flex-1 sm:flex-initial px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-300 text-xs font-bold border border-indigo-200/80 dark:border-indigo-800/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition flex items-center justify-center gap-1.5 font-sans shadow-xs whitespace-nowrap">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
                تنظیمات اقامتگاه
            </a>
            <a href="{{ route('user.properties.index') }}" 
               class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-slate-300 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-700/60 transition flex items-center justify-center gap-1.5 font-sans shadow-xs whitespace-nowrap">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                بازگشت
            </a>
        </div>
    </div>

    {{-- نوار ناوبری ماه --}}
    <div class="{{ $cardClass }} p-3 sm:p-4">
        <div class="flex items-center justify-between">
            <a href="{{ route('user.properties.rental.calendar', ['property' => $property, 'year' => $prevYear, 'month' => $prevMonth]) }}" 
               class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-slate-300 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-700/60 transition flex items-center gap-1 font-sans shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span>ماه قبل</span>
            </a>

            <div class="text-center px-1">
                <h2 class="text-sm sm:text-base font-black text-gray-900 dark:text-white font-sans tracking-tight">
                    {{ $monthNames[$currentMonth] }} {{ $currentYear }}
                </h2>
                <span class="text-[10px] sm:text-[11px] text-gray-500 dark:text-slate-400 font-sans block sm:inline">تقویم نرخ و دسترسی</span>
            </div>

            <a href="{{ route('user.properties.rental.calendar', ['property' => $property, 'year' => $nextYear, 'month' => $nextMonth]) }}" 
               class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-slate-300 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-700/60 transition flex items-center gap-1 font-sans shadow-xs">
                <span>ماه بعد</span>
                <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </div>

    {{-- گرید تقویم --}}
    <div class="{{ $cardClass }} p-3.5 sm:p-5 space-y-4">
        {{-- راهنمای رنگ‌های نرخ و وضعیت + هینت ریسپانسیو --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs border-b border-gray-100 dark:border-gray-700/60 pb-3 font-sans">
            <div class="flex flex-wrap items-center gap-2.5 sm:gap-4 text-[10px] sm:text-[11px]">
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-md bg-white dark:bg-gray-800 border border-indigo-300 dark:border-indigo-600/60 shadow-xs"></span>
                    <span class="text-gray-700 dark:text-slate-300 font-medium">شب عادی</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-md bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700/60 shadow-xs"></span>
                    <span class="text-amber-800 dark:text-amber-300 font-medium">آخر هفته</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-md bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-700/60 shadow-xs"></span>
                    <span class="text-rose-800 dark:text-rose-300 font-medium">پیک / تعطیل</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-md bg-purple-50 dark:bg-purple-950/40 border border-purple-300 dark:border-purple-700/60 shadow-xs"></span>
                    <span class="text-purple-800 dark:text-purple-300 font-medium">تاریخ خاص</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-md bg-red-100 dark:bg-red-950/60 border border-red-300 dark:border-red-700/60 shadow-xs"></span>
                    <span class="text-red-800 dark:text-red-300 font-medium">مسدود</span>
                </div>
            </div>

            <div class="flex items-center justify-between sm:justify-end gap-2 text-[10px] sm:text-[11px] text-gray-500 dark:text-slate-400">
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                    </svg>
                    <span>کلیک روی هر روز: تغییر مسدودی</span>
                </span>
                <span class="inline-flex md:hidden items-center gap-1 text-[10px] text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/50 px-2 py-0.5 rounded-full font-medium">
                    <span>اسکرول افقی</span>
                    <svg class="w-3 h-3 rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                    </svg>
                </span>
            </div>
        </div>

        {{-- محفظه ریسپانسیو با اسکرول افقی نرم روی موبایل --}}
        <div class="overflow-x-auto -mx-3.5 px-3.5 sm:mx-0 sm:px-0 pb-1 scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-gray-700">
            <div class="min-w-[620px] md:min-w-full space-y-2">
                {{-- هدر روزهای هفته --}}
                <div class="grid grid-cols-7 gap-1.5 sm:gap-2 text-center">
                    @php
                        $weekDays = ['شنبه', '۱شنبه', '۲شنبه', '۳شنبه', '۴شنبه', '۵شنبه', 'جمعه'];
                        $weekendDaysList = $property->rentalConfig ? ($property->rentalConfig->weekend_days ?? ['4', '5']) : ['4', '5'];
                    @endphp
                    @foreach($weekDays as $wIndex => $wName)
                        <div class="py-1.5 sm:py-2 text-[11px] sm:text-xs font-black font-sans {{ in_array((string)$wIndex, $weekendDaysList, true) ? 'text-amber-600 dark:text-amber-400' : 'text-gray-600 dark:text-slate-400' }}">
                            {{ $wName }}
                        </div>
                    @endforeach
                </div>

                {{-- روزهای خالی قبل از شروع ماه و روزهای ماه --}}
                <div class="grid grid-cols-7 gap-1.5 sm:gap-2.5">
                    @php
                        $firstDayOfWeek = $startOfMonthJalali->getDayOfWeek(); // 0 تا 6
                    @endphp
                    @for($i = 0; $i < $firstDayOfWeek; $i++)
                        <div class="h-20 sm:h-24 rounded-xl sm:rounded-2xl bg-gray-50/40 dark:bg-gray-900/30 border border-transparent"></div>
                    @endfor

                    {{-- نمایش روزهای ماه --}}
                    @foreach($monthDays as $d)
                        @php
                            $isBlocked = $d['is_blocked'];
                            $isPast = $d['is_past'];
                            $priceType = $d['price_type'] ?? 'normal';
                            $cleanHoliday = !empty($d['holiday_title']) ? trim(preg_replace('/\[.*?\]/', '', $d['holiday_title'])) : '';
                        @endphp
                        <div @click="toggleDay('{{ $d['carbon_date'] }}')"
                             class="h-20 sm:h-24 p-2 sm:p-2.5 rounded-xl sm:rounded-2xl border transition-all cursor-pointer flex flex-col justify-between relative group select-none
                             {{ $isBlocked
                                ? 'bg-red-50/70 border-red-200/90 dark:bg-red-950/35 dark:border-red-900/60 shadow-xs'
                                : ($priceType === 'custom'
                                    ? 'bg-purple-50/60 border-purple-200/80 dark:bg-purple-950/25 dark:border-purple-800/60 hover:border-purple-400 dark:hover:border-purple-600 hover:shadow-xs'
                                    : ($priceType === 'holiday'
                                        ? 'bg-rose-50/60 border-rose-200/80 dark:bg-rose-950/25 dark:border-rose-800/60 hover:border-rose-400 dark:hover:border-rose-600 hover:shadow-xs'
                                        : ($priceType === 'weekend'
                                            ? 'bg-amber-50/60 border-amber-200/80 dark:bg-amber-950/20 dark:border-amber-800/50 hover:border-amber-400 dark:hover:border-amber-600 hover:shadow-xs'
                                            : 'bg-white dark:bg-gray-800/80 border-gray-200/90 dark:border-gray-700/80 hover:border-indigo-400 dark:hover:border-indigo-500/60 hover:shadow-xs'))) }}
                             {{ $isPast ? 'opacity-40 grayscale-[20%]' : '' }}"
                             id="day-box-{{ $d['carbon_date'] }}">

                            {{-- بالای سلول: شماره روز و وضعیت --}}
                            <div class="flex items-center justify-between">
                                <span class="text-xs sm:text-sm font-black {{ $isBlocked ? 'text-red-700 dark:text-red-400' : 'text-gray-900 dark:text-white' }} font-sans">
                                    {{ $d['day'] }}
                                </span>

                                @if($isBlocked)
                                    <span class="text-[9px] sm:text-[10px] font-bold text-red-600 dark:text-red-300 bg-red-100/90 dark:bg-red-900/50 px-1 sm:px-1.5 py-0.5 rounded-md font-sans">
                                        مسدود
                                    </span>
                                @else
                                    <span class="text-[9px] sm:text-[10px] font-medium text-emerald-600 dark:text-emerald-400 font-sans">
                                        آزاد
                                    </span>
                                @endif
                            </div>

                            {{-- پایین سلول: قیمت و مناسبت --}}
                            <div class="space-y-0.5">
                                @if($d['custom_title'])
                                    <span class="block text-[8px] sm:text-[9px] text-purple-700 dark:text-purple-300 font-bold truncate font-sans" title="{{ $d['custom_title'] }}">
                                        {{ $d['custom_title'] }}
                                    </span>
                                @elseif(!empty($cleanHoliday))
                                    <span class="block text-[8px] sm:text-[9px] text-rose-600 dark:text-rose-400 font-medium truncate font-sans" title="{{ $cleanHoliday }}">
                                        {{ $cleanHoliday }}
                                    </span>
                                @endif

                                @if($d['price'] > 0)
                                    <span class="block text-[11px] sm:text-xs font-black {{ $isBlocked ? 'text-gray-400 dark:text-gray-500 line-through' : ($priceType === 'custom' ? 'text-purple-700 dark:text-purple-300' : ($priceType === 'holiday' ? 'text-rose-700 dark:text-rose-400' : ($priceType === 'weekend' ? 'text-amber-700 dark:text-amber-300' : 'text-indigo-600 dark:text-indigo-400'))) }} font-sans">
                                        {{ number_format($d['price']) }}
                                    </span>
                                @else
                                    <span class="text-[9px] sm:text-[10px] text-gray-400 dark:text-gray-500 font-sans">—</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ابزار مسدودسازی دسته‌ای یک بازه زمانی --}}
    <div class="{{ $cardClass }} p-4 sm:p-7">
        <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-700/60 pb-3 sm:pb-4 mb-4 sm:mb-6">
            <span class="w-8 h-8 rounded-xl bg-red-50 text-red-600 dark:bg-red-950/50 dark:text-red-400 border border-red-100 dark:border-red-900/50 flex items-center justify-center shadow-xs shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            </span>
            <div>
                <h2 class="text-sm font-black text-gray-900 dark:text-white font-sans">
                    مسدودسازی دسته‌ای یک بازه زمانی
                </h2>
                <p class="text-[11px] sm:text-xs text-gray-500 dark:text-slate-400 font-sans mt-0.5">
                    در صورتی که اقامتگاه در بازه‌ای خاص در دسترس نیست، تاریخ شروع و پایان را انتخاب و تقویم را مسدود نمایید.
                </p>
            </div>
        </div>

        <form action="{{ route('user.properties.rental.calendar.batch-block', $property) }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4">
                {{-- تاریخ شروع --}}
                <div>
                    <label for="start_date_jalali" class="{{ $labelClass }}">از تاریخ (شمسی)</label>
                    <div class="relative">
                        <input type="text" 
                               id="start_date_jalali"
                               name="start_date_jalali" 
                               data-jdp 
                               data-jdp-only-date 
                               class="{{ $inputClass }} text-center pl-10 font-sans cursor-pointer" 
                               placeholder="1405/01/01" 
                               autocomplete="off"
                               @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                               @focus="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                               required>
                        <button type="button"
                                @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show(document.getElementById('start_date_jalali')); }"
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-indigo-600 transition focus:outline-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- تاریخ پایان --}}
                <div>
                    <label for="end_date_jalali" class="{{ $labelClass }}">تا تاریخ (شمسی)</label>
                    <div class="relative">
                        <input type="text" 
                               id="end_date_jalali"
                               name="end_date_jalali" 
                               data-jdp 
                               data-jdp-only-date 
                               class="{{ $inputClass }} text-center pl-10 font-sans cursor-pointer" 
                               placeholder="1405/01/05" 
                               autocomplete="off"
                               @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                               @focus="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                               required>
                        <button type="button"
                                @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show(document.getElementById('end_date_jalali')); }"
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-indigo-600 transition focus:outline-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- علت مسدودسازی --}}
                <div class="sm:col-span-2 lg:col-span-1">
                    <label for="batch_reason" class="{{ $labelClass }}">علت مسدودسازی (اختیاری)</label>
                    <input type="text" 
                           id="batch_reason"
                           name="reason" 
                           class="{{ $inputClass }}" 
                           placeholder="مثلا: استفاده شخصی یا رزرو تلفنی">
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" 
                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition shadow-sm flex items-center justify-center gap-2 font-sans">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    مسدود کردن بازه مشخص
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    function rentalCalendar() {
        return {
            init() {
                this.$nextTick(() => {
                    this.initDatePicker();
                });
            },

            initDatePicker() {
                if (window.jalaliDatepicker) {
                    try {
                        window.jalaliDatepicker.startWatch({
                            selector: '[data-jdp-only-date]',
                            minDate: 'attr',
                            date: true,
                            time: false,
                            autoHide: true,
                            hideAfterChange: true
                        });
                        window.jalaliDatepicker.startWatch({
                            selector: '[data-jdp]',
                            minDate: 'attr',
                            date: true,
                            time: false,
                            autoHide: true,
                            hideAfterChange: true
                        });
                    } catch (e) {
                        console.warn('JalaliDatepicker init warning:', e);
                    }
                }
            },

            toggleDay(dateStr) {
                fetch('{{ route('user.properties.rental.calendar.toggle-block', $property) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ date: dateStr })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    }
                })
                .catch(err => console.error(err));
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (window.jalaliDatepicker) {
            try {
                window.jalaliDatepicker.startWatch({
                    selector: '[data-jdp-only-date]',
                    minDate: 'attr',
                    date: true,
                    time: false,
                    autoHide: true,
                    hideAfterChange: true
                });
                window.jalaliDatepicker.startWatch({
                    selector: '[data-jdp]',
                    minDate: 'attr',
                    date: true,
                    time: false,
                    autoHide: true,
                    hideAfterChange: true
                });
            } catch (e) {
                console.warn('JalaliDatepicker DOM warning:', e);
            }
        }
    });
</script>
@endsection
