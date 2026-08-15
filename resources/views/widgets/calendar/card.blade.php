@inject('calendarService', 'App\Services\CalendarEventService')

@php
    $user = auth()->user();
    $todayCarbon = \Carbon\Carbon::now();
    $todayJalali = \Morilog\Jalali\Jalalian::now();

    // Jalali Date Parts
    $jYear  = $todayJalali->getYear();
    $jMonth = $todayJalali->getMonth();
    $jDay   = $todayJalali->getDay();
    $jMonthName = $todayJalali->format('%B');
    $jDayName   = $todayJalali->format('%A');

    // Gregorian Date Parts
    $gMonthNameUpper = strtoupper($todayCarbon->format('F'));
    $gDayNameShort   = $todayCarbon->format('D');
    $gMonthNameShort = $todayCarbon->format('M');
    $gDay            = (int)$todayCarbon->format('d');
    $gYear           = $todayCarbon->format('Y');

    // Month Progress Calculations
    $daysInMonth = $todayJalali->getMonthDays();

    // Calendar Grid for Jalali Current Month
    $firstDayJalali = new \Morilog\Jalali\Jalalian($jYear, $jMonth, 1);
    $lastDayJalali  = new \Morilog\Jalali\Jalalian($jYear, $jMonth, $daysInMonth);
    $firstDayCarbon = $firstDayJalali->toCarbon();
    $lastDayCarbon  = $lastDayJalali->toCarbon();
    $startDow       = ($firstDayCarbon->dayOfWeek + 1) % 7; // 0=شنبه, 6=جمعه

    // دریافت رویدادها و مناسبت‌های امروز (فقط منبع مناسبت‌ها و تعطیلات)
    $todayEvents = $user ? $calendarService->getEventsForDate($todayCarbon, $user, ['jalali_holidays']) : collect();

    // دریافت تمام مناسبت‌های ماه جاری جهت هایلایت روزهای تعطیل در تقویم کوچک
    $monthEvents = $user ? $calendarService->getEventsForRange($firstDayCarbon->copy()->startOfDay(), $lastDayCarbon->copy()->endOfDay(), $user, ['jalali_holidays']) : collect();
    $monthHolidaysByDay = $monthEvents->groupBy('day');

    // تنظیمات نحوه نمایش کارت ویجت تقویم (both, counter_only, calendar_only)
    $widgetDisplayMode = 'both';
    if (class_exists(\Modules\Settings\Entities\Setting::class) && \Illuminate\Support\Facades\Schema::hasTable('settings')) {
        $widgetDisplayMode = \Modules\Settings\Entities\Setting::where('key', 'widget_calendar_display_mode')->value('value') ?? 'both';
    }
@endphp

<div class="h-full flex flex-col justify-between select-none">
    {{-- هدر کارت با دکمه ورود به تقویم کامل --}}
    <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-200/80 dark:border-slate-800">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
            <h3 class="text-sm font-black text-slate-800 dark:text-slate-100">تقویم و مناسبت‌های روز</h3>
        </div>
        <a href="{{ route('user.calendar') }}" 
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-rose-500 hover:text-white text-slate-700 dark:bg-slate-800/80 dark:hover:bg-rose-600 dark:text-slate-300 text-xs font-bold transition-all duration-300 group shadow-sm">
            <span>تقویم کامل</span>
            <svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-1 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
        </a>
    </div>

    {{-- بدنه اصلی ویجت: کارت‌های دوگانه کاملاً هماهنگ با تم دارک و لایت --}}
    <div class="grid grid-cols-1 {{ $widgetDisplayMode === 'both' ? 'md:grid-cols-2' : '' }} gap-3 my-1">
        
        @if(in_array($widgetDisplayMode, ['both', 'counter_only']))
            {{-- بخش ۱: شمارنده روز به شمسی --}}
            <div class="bg-gradient-to-br from-slate-950 via-purple-950 to-indigo-950 text-white dark:from-[#1e1b2e] dark:to-[#120f1d] p-4 rounded-3xl shadow-lg border border-slate-800 dark:border-purple-900/40 flex flex-col justify-between min-h-[200px] transition-colors duration-200">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-black text-rose-400 tracking-wide">{{ $jDayName }}</span>
                        <span class="text-xs font-extrabold text-slate-300 dark:text-slate-400">{{ $jMonthName }} {{ $jYear }}</span>
                    </div>
                    <div class="text-xs text-slate-400 dark:text-slate-500 mt-0.5" dir="ltr">
                        {{ $gDayNameShort }} {{ $gDay }} {{ $gMonthNameShort }} {{ $gYear }}
                    </div>
                </div>

                {{-- عدد بزرگ روز شمسی --}}
                <div class="text-center my-2">
                    <span class="text-6xl font-black tracking-tight text-white drop-shadow-md">
                        {{ $jDay }}
                    </span>
                </div>

                {{-- بخش پایینی روزشمار --}}
                <div class="pt-2.5 border-t border-white/10 flex items-center justify-between text-xs font-bold text-slate-300 dark:text-slate-400">
                    <span>تاریخ امروز</span>
                    <span class="text-rose-400 font-extrabold">{{ $jDay }} {{ $jMonthName }} {{ $jYear }}</span>
                </div>
            </div>
        @endif

        @if(in_array($widgetDisplayMode, ['both', 'calendar_only']))
            {{-- بخش ۲: تقویم کوچک ماه جاری --}}
            <div class="bg-slate-50 border-slate-200/80 text-slate-800 shadow-sm dark:bg-gradient-to-br dark:from-[#18181b] dark:to-[#09090b] dark:text-white dark:border-zinc-800 dark:shadow-xl p-4 rounded-3xl border flex flex-col justify-between min-h-[200px] transition-colors duration-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-black text-rose-600 dark:text-rose-500 tracking-wider uppercase">{{ $gMonthNameUpper }} {{ $gYear }}</span>
                    <span class="text-[11px] font-bold text-slate-500 dark:text-zinc-400">{{ $jYear }}</span>
                </div>

                {{-- سرستون روزهای هفته (شنبه تا جمعه) --}}
                <div class="grid grid-cols-7 text-center text-[10px] font-bold text-slate-500 dark:text-zinc-500 mb-1">
                    <span>ش</span>
                    <span>ی</span>
                    <span>د</span>
                    <span>س</span>
                    <span>چ</span>
                    <span>پ</span>
                    <span class="text-rose-600 dark:text-rose-500">ج</span>
                </div>

                {{-- خانه روزها همراه با روز و ماه میلادی --}}
                <div class="grid grid-cols-7 gap-1 text-center text-xs font-bold">
                    {{-- خنثی کردن خانه‌های خالی اول ماه --}}
                    @for ($i = 0; $i < $startDow; $i++)
                        <span class="py-1 opacity-0">.</span>
                    @endfor

                    {{-- روزهای ماه --}}
                    @for ($d = 1; $d <= $daysInMonth; $d++)
                        @php 
                            $isToday = ($d == $jDay);
                            $dayEvents = $monthHolidaysByDay->get($d, collect());
                            $hasOfficialHoliday = $dayEvents->contains(fn($e) => !empty($e['is_holiday']));
                            $cDate = (new \Morilog\Jalali\Jalalian($jYear, $jMonth, $d))->toCarbon();
                            $gDayNum = $cDate->format('j');
                            $gFullText = $cDate->format('d M Y');
                        @endphp
                        <div class="flex flex-col items-center justify-center h-8 w-8 mx-auto rounded-xl transition-all text-[11px] relative
                            {{ $isToday ? 'bg-rose-500 text-white font-black shadow-md shadow-rose-500/30 ring-2 ring-rose-400 dark:shadow-rose-500/40' : ($hasOfficialHoliday ? 'text-rose-600 font-extrabold hover:bg-rose-100/70 dark:text-rose-400 dark:hover:bg-rose-950/40' : 'text-slate-700 hover:bg-slate-200/70 dark:text-zinc-300 dark:hover:bg-zinc-800') }}"
                            title="شمسی: {{ $d }} {{ $jMonthName }} {{ $jYear }} | میلادی: {{ $gFullText }}{{ $hasOfficialHoliday ? ' (تعطیل رسمی)' : '' }}">
                            <span class="leading-none font-bold">{{ $d }}</span>
                            <span class="text-[8px] opacity-70 leading-none mt-0.5 font-normal">{{ $gDayNum }}</span>
                            @if($hasOfficialHoliday && !$isToday)
                                <span class="w-1 h-1 rounded-full bg-rose-500 absolute bottom-0.5"></span>
                            @endif
                        </div>
                    @endfor
                </div>
            </div>
        @endif

    </div>

    {{-- بخش مناسبت‌ها و تعطیلات امروز --}}
    <div class="mt-3 pt-3 border-t border-slate-200/80 dark:border-slate-800">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                مناسبت‌ها & تعطیلات امروز
            </span>
            <span class="text-[10px] font-black px-2.5 py-0.5 rounded-full bg-rose-100/80 text-rose-700 border border-rose-200/60 dark:bg-rose-900/30 dark:text-rose-300 dark:border-transparent">
                {{ $todayEvents->count() }} مورد
            </span>
        </div>

        @if($todayEvents->isEmpty())
            <div class="py-3.5 text-center bg-slate-50 border-slate-200/80 dark:bg-slate-800/40 dark:border-slate-700/60 rounded-2xl border border-dashed">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">هیچ مناسبت یا تعطیلی برای امروز ثبت نشده است.</p>
            </div>
        @else
            <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                @foreach($todayEvents->take(5) as $ev)
                    <a href="{{ route('user.calendar') }}" class="group flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-rose-50/80 border-slate-200/70 dark:bg-slate-800/60 dark:hover:bg-rose-900/20 dark:border-slate-700/60 border transition-all">
                        <div class="flex items-center gap-2 min-w-0">
                            @if(!empty($ev['is_holiday']))
                                <span class="w-2 h-2 rounded-full bg-rose-500 flex-shrink-0"></span>
                            @else
                                <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                            @endif
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate group-hover:text-rose-600 dark:group-hover:text-rose-400">
                                {{ $ev['title'] }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-lg {{ !empty($ev['is_holiday']) ? 'bg-rose-100 text-rose-800 border border-rose-200/60 dark:bg-rose-900/40 dark:text-rose-300 dark:border-transparent' : 'bg-emerald-100 text-emerald-800 border border-emerald-200/60 dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-transparent' }}">
                                {{ !empty($ev['is_holiday']) ? 'تعطیل رسمی' : 'مناسبت' }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
