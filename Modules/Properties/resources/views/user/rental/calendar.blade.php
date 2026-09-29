@extends('layouts.user')

@php
    $title = 'تقویم اقامتگاه: ' . $property->title;
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-200";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 font-sans";
    $currencyLabel = $currency === 'toman' ? 'تومان' : 'ریال';

    // محاسبه ماه قبل و بعد
    $prevMonth = $currentMonth == 1 ? 12 : $currentMonth - 1;
    $prevYear = $currentMonth == 1 ? $currentYear - 1 : $currentYear;

    $nextMonth = $currentMonth == 12 ? 1 : $currentMonth + 1;
    $nextYear = $currentMonth == 12 ? $currentYear + 1 : $currentYear;
@endphp

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8 space-y-6" x-data="rentalCalendar()">

    {{-- هدر صفحه --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 dark:bg-teal-500/20 dark:text-teal-300 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </span>
                تقویم رزرو و قیمت: {{ $property->title }}
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mr-10">برای مسدود یا آزاد کردن هر روز کافیست روی آن کلیک کنید.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('user.properties.rental.config', $property) }}" class="px-4 py-2 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300 text-xs font-bold border border-indigo-200 dark:border-indigo-800/40 hover:bg-indigo-100 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                تنظیمات ظرفیت و امکانات
            </a>
            <a href="{{ route('user.properties.index') }}" class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 text-xs font-bold hover:bg-gray-50 transition">
                بازگشت به املاک
            </a>
        </div>
    </div>

    {{-- نوار ناوبری ماه --}}
    <div class="{{ $cardClass }} p-4">
        <div class="flex items-center justify-between">
            <a href="{{ route('user.properties.rental.calendar', ['property' => $property, 'year' => $prevYear, 'month' => $prevMonth]) }}" class="px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                ماه قبل
            </a>

            <div class="text-center">
                <h2 class="text-base font-bold text-gray-900 dark:text-white font-sans">
                    {{ $monthNames[$currentMonth] }} {{ $currentYear }}
                </h2>
                <span class="text-[11px] text-gray-400">تقویم نرخ و دسترسی</span>
            </div>

            <a href="{{ route('user.properties.rental.calendar', ['property' => $property, 'year' => $nextYear, 'month' => $nextMonth]) }}" class="px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center gap-1">
                ماه بعد
                <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </a>
        </div>
    </div>

    {{-- گرید تقویم --}}
    <div class="{{ $cardClass }} p-5">
        {{-- هدر روزهای هفته --}}
        <div class="grid grid-cols-7 gap-2 mb-3 text-center">
            @php
                $weekDays = ['شنبه', '۱شنبه', '۲شنبه', '۳شنبه', '۴شنبه', '۵شنبه', 'جمعه'];
            @endphp
            @foreach($weekDays as $wIndex => $wName)
                <div class="py-2 text-xs font-bold {{ $wIndex >= 4 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-500 dark:text-gray-400' }}">
                    {{ $wName }}
                </div>
            @endforeach
        </div>

        {{-- روزهای خالی قبل از شروع ماه --}}
        <div class="grid grid-cols-7 gap-2.5">
            @php
                $firstDayOfWeek = $startOfMonthJalali->getDayOfWeek(); // 0 تا 6
            @endphp
            @for($i = 0; $i < $firstDayOfWeek; $i++)
                <div class="h-24 rounded-xl bg-gray-50/50 dark:bg-gray-800/30 border border-transparent"></div>
            @endfor

            {{-- نمایش روزهای ماه --}}
            @foreach($monthDays as $d)
                @php
                    $isBlocked = $d['is_blocked'];
                    $isPast = $d['is_past'];
                @endphp
                <div @click="toggleDay('{{ $d['carbon_date'] }}')"
                     class="h-24 p-2 rounded-xl border transition-all cursor-pointer flex flex-col justify-between relative group select-none
                     {{ $isBlocked
                        ? 'bg-red-50/70 border-red-200 dark:bg-red-950/20 dark:border-red-900/50'
                        : ($d['is_weekend']
                            ? 'bg-amber-50/50 border-amber-200/80 dark:bg-amber-950/15 dark:border-amber-900/40 hover:border-amber-400'
                            : 'bg-white dark:bg-gray-800/80 border-gray-200 dark:border-gray-700 hover:border-indigo-400 hover:shadow-sm') }}
                     {{ $isPast ? 'opacity-50' : '' }}"
                     id="day-box-{{ $d['carbon_date'] }}">

                    {{-- بالای سلول: شماره روز و وضعیت --}}
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold {{ $isBlocked ? 'text-red-700 dark:text-red-400' : 'text-gray-900 dark:text-white' }} font-sans">
                            {{ $d['day'] }}
                        </span>

                        @if($isBlocked)
                            <span class="text-[10px] font-bold text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/40 px-1.5 py-0.5 rounded">
                                مسدود
                            </span>
                        @else
                            <span class="text-[10px] font-medium text-emerald-600 dark:text-emerald-400">
                                آزاد
                            </span>
                        @endif
                    </div>

                    {{-- پایین سلول: قیمت و مناسبت --}}
                    <div>
                        @if($d['custom_title'])
                            <span class="block text-[9px] text-purple-600 dark:text-purple-300 font-bold truncate" title="{{ $d['custom_title'] }}">
                                {{ $d['custom_title'] }}
                            </span>
                        @endif

                        @if($d['price'] > 0)
                            <span class="block text-xs font-bold {{ $isBlocked ? 'text-gray-400 line-through' : 'text-indigo-600 dark:text-indigo-300' }} font-sans">
                                {{ number_format($d['price']) }}
                            </span>
                        @else
                            <span class="text-[10px] text-gray-400 font-sans">—</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ابزارهای کمکی: قیمت فصلی و مسدودسازی دسته‌ای --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- مسدودسازی دسته‌ای یک بازه --}}
        <div class="{{ $cardClass }} p-6 space-y-4">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                مسدودسازی یک بازه زمانی
            </h2>

            <form action="{{ route('user.properties.rental.calendar.batch-block', $property) }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $labelClass }}">از تاریخ (شمسی)</label>
                        <input type="text" name="start_date_jalali" class="{{ $inputClass }} text-center" placeholder="1405/01/01" required>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">تا تاریخ (شمسی)</label>
                        <input type="text" name="end_date_jalali" class="{{ $inputClass }} text-center" placeholder="1405/01/05" required>
                    </div>
                </div>

                <div>
                    <label class="{{ $labelClass }}">علت مسدودسازی</label>
                    <input type="text" name="reason" class="{{ $inputClass }}" placeholder="مثلا: استفاده شخصی مالک یا رزرو تلفنی">
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition shadow-sm">
                    مسدود کردن بازه مشخص
                </button>
            </form>
        </div>

        {{-- قیمت‌گذاری ویژه فصلی / مناسبتی --}}
        <div class="{{ $cardClass }} p-6 space-y-4">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                تعریف قیمت ویژه برای بازه مشخص
            </h2>

            <form action="{{ route('user.properties.rental.calendar.seasonal-prices.store', $property) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="{{ $labelClass }}">عنوان مناسبت / فصل</label>
                    <input type="text" name="title" class="{{ $inputClass }}" placeholder="مثلا: پیک نوروز یا جشنواره تابستانه" required>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $labelClass }}">از تاریخ (شمسی)</label>
                        <input type="text" name="start_date_jalali" class="{{ $inputClass }} text-center" placeholder="1405/01/01" required>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">تا تاریخ (شمسی)</label>
                        <input type="text" name="end_date_jalali" class="{{ $inputClass }} text-center" placeholder="1405/01/13" required>
                    </div>
                </div>

                <div>
                    <label class="{{ $labelClass }}">قیمت هر شب ({{ $currencyLabel }})</label>
                    <input type="text" name="price_per_night" class="{{ $inputClass }} text-left dir-ltr" placeholder="0" required>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition shadow-sm">
                    ثبت قیمت فصلی
                </button>
            </form>
        </div>
    </div>

    {{-- لیست بازه‌های ویژه فصلی ثبت شده --}}
    @if($seasonalPrices->isNotEmpty())
        <div class="{{ $cardClass }} p-5">
            <h3 class="text-xs font-bold text-gray-800 dark:text-gray-200 mb-3">قیمت‌های فصلی تعریف شده:</h3>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($seasonalPrices as $sp)
                    <div class="py-2.5 flex items-center justify-between text-xs font-sans">
                        <div>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $sp->title }}</span>
                            <span class="text-gray-500 dark:text-gray-400 mr-2">
                                (از {{ \Morilog\Jalali\Jalalian::fromCarbon($sp->start_date)->format('Y/m/d') }} تا {{ \Morilog\Jalali\Jalalian::fromCarbon($sp->end_date)->format('Y/m/d') }})
                            </span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-bold text-purple-600 dark:text-purple-400">{{ number_format($sp->price_per_night) }} {{ $currencyLabel }}</span>
                            <form action="{{ route('user.properties.rental.calendar.seasonal-prices.destroy', $sp) }}" method="POST" onsubmit="return confirm('حذف این قیمت ویژه؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<script>
    function rentalCalendar() {
        return {
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
</script>
@endsection
