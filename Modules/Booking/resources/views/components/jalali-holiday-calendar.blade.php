@props([
    'scopeType' => 'GLOBAL', // GLOBAL, SERVICE, SERVICE_PROVIDER
    'scopeId' => null,
    'storeUrl' => route('user.booking.settings.holidays.store'),
    'batchUrl' => route('user.booking.settings.holidays.batch'),
    'exceptions' => [], // collection or array of exceptions
    'globalExceptions' => [], // global exceptions if viewing service or provider scope
])

@php
    $exceptionsArray = [];
    foreach ($exceptions as $ex) {
        $dateKey = is_string($ex['local_date'] ?? null)
            ? substr($ex['local_date'], 0, 10)
            : ($ex->local_date ? $ex->local_date->format('Y-m-d') : null);
        if ($dateKey) {
            $exceptionsArray[$dateKey] = [
                'id' => $ex['id'] ?? $ex->id,
                'is_closed' => (bool)($ex['is_closed'] ?? $ex->is_closed),
                'override_capacity_per_day' => $ex['override_capacity_per_day'] ?? $ex->override_capacity_per_day ?? null,
            ];
        }
    }

    $globalArray = [];
    if (!empty($globalExceptions)) {
        foreach ($globalExceptions as $gex) {
            $gDateKey = is_string($gex['local_date'] ?? null)
                ? substr($gex['local_date'], 0, 10)
                : ($gex->local_date ? $gex->local_date->format('Y-m-d') : null);
            if ($gDateKey && ($gex['is_closed'] ?? $gex->is_closed)) {
                $globalArray[$gDateKey] = true;
            }
        }
    }

    $currentJalaliYear = (int) \Morilog\Jalali\Jalalian::now()->getYear();
    $currentJalaliMonth = (int) \Morilog\Jalali\Jalalian::now()->getMonth();
@endphp

<div
    x-data="jalaliHolidayCalendar({
        scopeType: '{{ $scopeType }}',
        storeUrl: '{{ $storeUrl }}',
        batchUrl: '{{ $batchUrl }}',
        initialYear: {{ $currentJalaliYear }},
        initialMonth: {{ $currentJalaliMonth }},
        exceptions: {{ json_encode($exceptionsArray) }},
        globalClosedDates: {{ json_encode($globalArray) }},
        csrfToken: '{{ csrf_token() }}'
    })"
    class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-xl shadow-gray-200/40 dark:shadow-none p-6 space-y-6"
>
    {{-- هدر تقویم: عنوان، انتخاب ماه و سال، و دکمه‌های عملیاتی --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-700 pb-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    تقویم شمسی مدیریت تعطیلات
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-medium bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">
                        @if($scopeType === 'GLOBAL')سیستم کلی@elseif($scopeType === 'SERVICE_PROVIDER')ارائه‌دهنده@elseسرویس@endif
                    </span>
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">روی هر روز کلیک کنید تا تعطیل/فعال شود، یا از انتخاب گروهی استفاده نمایید.</p>
            </div>
        </div>

        {{-- کنترل‌های تقویم (سال، ماه، و دکمه‌ها) --}}
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="prevMonth()" class="p-2 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition">
                <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>

            <select x-model="selectedMonth" @change="renderCalendar()" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/60 px-3 py-1.5 text-xs font-bold text-gray-800 dark:text-gray-200 outline-none">
                <template x-for="(monthName, index) in jalaliMonths" :key="index">
                    <option :value="index + 1" x-text="monthName" :selected="index + 1 === selectedMonth"></option>
                </template>
            </select>

            <select x-model="selectedYear" @change="renderCalendar()" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/60 px-3 py-1.5 text-xs font-bold text-gray-800 dark:text-gray-200 outline-none font-mono">
                <template x-for="yr in yearRange" :key="yr">
                    <option :value="yr" x-text="yr" :selected="yr === selectedYear"></option>
                </template>
            </select>

            <button type="button" @click="nextMonth()" class="p-2 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>

            <button type="button" @click="openBatchModal = true" class="mr-2 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                ثبت تعطیلی گروهی (بازه)
            </button>
        </div>
    </div>

    {{-- راهنمای علائم و رنگ‌ها (Legend) --}}
    <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-50/70 dark:bg-gray-900/30 p-3 rounded-2xl border border-gray-100 dark:border-gray-700/60">
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
            <span>روز کاری فعال</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span>
            <span>تعطیل (در این لایه)</span>
        </div>
        @if($scopeType !== 'GLOBAL')
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-indigo-500 inline-block"></span>
                <span>تعطیل سراسری سیستم (موروثی)</span>
            </div>
        @endif
        <div class="flex items-center gap-1.5 mr-auto text-gray-400">
            <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>با کلیک روی هر روز وضعیت تعطیلی تغییر می‌کند.</span>
        </div>
    </div>

    {{-- شبکه روزهای تقویم (Calendar Grid) --}}
    <div class="space-y-2">
        {{-- روزهای هفته --}}
        <div class="grid grid-cols-7 text-center text-xs font-bold text-gray-500 dark:text-gray-400 py-2 border-b border-gray-100 dark:border-gray-700">
            <div>شنبه</div>
            <div>یکشنبه</div>
            <div>دوشنبه</div>
            <div>سه‌شنبه</div>
            <div>چهارشنبه</div>
            <div>پنج‌شنبه</div>
            <div class="text-rose-500">جمعه</div>
        </div>

        {{-- روزهای ماه --}}
        <div class="grid grid-cols-7 gap-1.5 text-center">
            {{-- سلول‌های خالی ابتدای ماه --}}
            <template x-for="emptyCell in emptyDaysCount" :key="'empty-' + emptyCell">
                <div class="h-16 rounded-2xl bg-gray-50/30 dark:bg-gray-900/10 border border-transparent opacity-30"></div>
            </template>

            {{-- سلول‌های روزهای ماه --}}
            <template x-for="dayObj in monthDays" :key="dayObj.gregorianDate">
                <div
                    @click="toggleDayHoliday(dayObj)"
                    :class="{
                        'border-rose-500/80 bg-rose-50/90 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 font-black shadow-xs': dayObj.isClosed,
                        'border-indigo-400/80 bg-indigo-50/90 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300': !dayObj.isClosed && dayObj.isGlobalClosed,
                        'border-gray-200 dark:border-gray-700/70 bg-white dark:bg-gray-800/80 text-gray-800 dark:text-gray-200 hover:border-emerald-400 hover:bg-emerald-50/40 dark:hover:bg-emerald-950/20': !dayObj.isClosed && !dayObj.isGlobalClosed
                    }"
                    class="h-16 rounded-2xl border p-2 flex flex-col justify-between items-center cursor-pointer transition-all hover:scale-[1.03] select-none relative overflow-hidden group"
                >
                    {{-- شماره روز شمسی --}}
                    <div class="flex items-center justify-between w-full">
                        <span class="text-sm font-black font-mono" x-text="dayObj.dayNumber"></span>
                        <template x-if="dayObj.isClosed">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        </template>
                        <template x-if="!dayObj.isClosed && dayObj.isGlobalClosed">
                            <span class="w-2 h-2 rounded-full bg-indigo-500" title="تعطیل سراسری سیستم"></span>
                        </template>
                    </div>

                    {{-- برچسب وضعیت --}}
                    <div class="text-[10px] font-bold">
                        <template x-if="dayObj.isClosed">
                            <span class="text-rose-600 dark:text-rose-400">تعطیل</span>
                        </template>
                        <template x-if="!dayObj.isClosed && dayObj.isGlobalClosed">
                            <span class="text-indigo-600 dark:text-indigo-400">سیستم تعطیل</span>
                        </template>
                        <template x-if="!dayObj.isClosed && !dayObj.isGlobalClosed">
                            <span class="text-emerald-600 dark:text-emerald-400 group-hover:block hidden">کار فعال</span>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Modal ثبت تعطیلی گروهی --}}
    <div x-show="openBatchModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    ثبت تعطیلی گروهی (بازه زمانی)
                </h3>
                <button type="button" @click="openBatchModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">تاریخ شروع (شمسی)</label>
                    <input type="text" x-ref="batchStartDate" data-jdp placeholder="مثلاً 1404/01/01"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-900/90 dark:border-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 outline-none focus:ring-2 focus:ring-rose-500/20">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">تاریخ پایان (شمسی)</label>
                    <input type="text" x-ref="batchEndDate" data-jdp placeholder="مثلاً 1404/01/13"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-900/90 dark:border-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 outline-none focus:ring-2 focus:ring-rose-500/20">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">وضعیت بازه انتخاب شده</label>
                    <select x-model="batchIsClosed" class="w-full rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-900/90 dark:border-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 outline-none">
                        <option value="1">تعطیل (رزرو بسته شود)</option>
                        <option value="0">بازگشت به روز کاری عادی (حذف تعطیلی)</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" @click="openBatchModal = false" class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 text-xs font-bold hover:bg-gray-200 transition">
                        انصراف
                    </button>
                    <button type="button" @click="submitBatchHoliday()" class="px-5 py-2.5 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 shadow-sm transition">
                        ثبت تعطیلات بازه
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('jalaliHolidayCalendar', (config) => ({
            scopeType: config.scopeType,
            storeUrl: config.storeUrl,
            batchUrl: config.batchUrl,
            selectedYear: config.initialYear,
            selectedMonth: config.initialMonth,
            exceptions: config.exceptions || {},
            globalClosedDates: config.globalClosedDates || {},
            csrfToken: config.csrfToken,

            openBatchModal: false,
            batchIsClosed: "1",

            jalaliMonths: [
                'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
                'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
            ],
            yearRange: [1402, 1403, 1404, 1405, 1406, 1407, 1408, 1409, 1410],

            emptyDaysCount: 0,
            monthDays: [],

            init() {
                this.renderCalendar();
            },

            prevMonth() {
                if (this.selectedMonth === 1) {
                    this.selectedMonth = 12;
                    this.selectedYear--;
                } else {
                    this.selectedMonth--;
                }
                this.renderCalendar();
            },

            nextMonth() {
                if (this.selectedMonth === 12) {
                    this.selectedMonth = 1;
                    this.selectedYear++;
                } else {
                    this.selectedMonth++;
                }
                this.renderCalendar();
            },

            // الگوریتم استاندارد تبدیل تاریخ شمسی به میلادی جهت محاسبه دقیق تقویم
            jalaliToGregorian(j_y, j_m, j_d) {
                j_y = parseInt(j_y);
                j_m = parseInt(j_m);
                j_d = parseInt(j_d);

                let jy = j_y - 979;
                let jm = j_m - 1;
                let jd = j_d - 1;

                let j_day_no = 365 * jy + parseInt(jy / 33) * 8 + parseInt((jy % 33 + 3) / 4);
                for (let i = 0; i < jm; ++i) {
                    j_day_no += (i < 6) ? 31 : 30;
                }
                j_day_no += jd;

                let g_day_no = j_day_no + 79;

                let gy = 1600 + 400 * parseInt(g_day_no / 146097);
                g_day_no = g_day_no % 146097;

                let leap = true;
                if (g_day_no >= 36525) {
                    g_day_no--;
                    gy += 100 * parseInt(g_day_no / 36524);
                    g_day_no = g_day_no % 36524;

                    if (g_day_no >= 365) {
                        g_day_no++;
                    } else {
                        leap = false;
                    }
                }

                gy += 4 * parseInt(g_day_no / 1461);
                g_day_no = g_day_no % 1461;

                if (g_day_no >= 366) {
                    leap = false;
                    g_day_no--;
                    gy += parseInt(g_day_no / 365);
                    g_day_no = g_day_no % 365;
                }

                let g_m, g_d;
                let sal_a = [0, 31, (leap ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
                for (g_m = 0; g_m < 13; g_m++) {
                    let v = sal_a[g_m];
                    if (g_day_no < v) break;
                    g_day_no -= v;
                }
                g_d = g_day_no + 1;

                let mm = g_m < 10 ? '0' + g_m : g_m;
                let dd = g_d < 10 ? '0' + g_d : g_d;
                return `${gy}-${mm}-${dd}`;
            },

            isJalaliLeap(year) {
                return ((((year - (year > 0 ? 474 : 473)) % 2820) + 474 + 38) * 682) % 2816 < 682;
            },

            getJalaliMonthDays(year, month) {
                if (month <= 6) return 31;
                if (month <= 11) return 30;
                return this.isJalaliLeap(year) ? 30 : 29;
            },

            renderCalendar() {
                const totalDays = this.getJalaliMonthDays(this.selectedYear, this.selectedMonth);

                // محاسبه روز اول ماه شمسی در هفته (0 = شنبه ... 6 = جمعه)
                const firstDayGregorianStr = this.jalaliToGregorian(this.selectedYear, this.selectedMonth, 1);
                const firstDayObj = new Date(firstDayGregorianStr + 'T00:00:00');
                const dayOfWeekGregorian = firstDayObj.getDay(); // 0: Sun, 1: Mon, ... 6: Sat

                // تبدیل روز هفته میلادی به شمسی (شنبه = 0 ... جمعه = 6)
                // Sun (0) -> 1, Mon (1) -> 2, Tue (2) -> 3, Wed (3) -> 4, Thu (4) -> 5, Fri (5) -> 6, Sat (6) -> 0
                const dayOfWeekJalali = (dayOfWeekGregorian + 1) % 7;
                this.emptyDaysCount = dayOfWeekJalali;

                const days = [];
                for (let d = 1; d <= totalDays; d++) {
                    const gDate = this.jalaliToGregorian(this.selectedYear, this.selectedMonth, d);
                    const ex = this.exceptions[gDate];
                    const isClosed = ex ? ex.is_closed : false;
                    const isGlobalClosed = !!this.globalClosedDates[gDate];

                    days.push({
                        dayNumber: d,
                        gregorianDate: gDate,
                        isClosed: isClosed,
                        isGlobalClosed: isGlobalClosed,
                    });
                }
                this.monthDays = days;
            },

            async toggleDayHoliday(dayObj) {
                const newStatus = !dayObj.isClosed;
                dayObj.isClosed = newStatus;

                if (newStatus) {
                    this.exceptions[dayObj.gregorianDate] = { is_closed: true };
                } else {
                    delete this.exceptions[dayObj.gregorianDate];
                }

                try {
                    const response = await fetch(this.storeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken
                        },
                        body: JSON.stringify({
                            local_date: dayObj.gregorianDate,
                            is_closed: newStatus ? 1 : 0
                        })
                    });
                    const resData = await response.json();
                    if (!resData.success) {
                        // Revert on error
                        dayObj.isClosed = !newStatus;
                    }
                } catch (e) {
                    console.error('Error updating holiday status:', e);
                    dayObj.isClosed = !newStatus;
                }
            },

            async submitBatchHoliday() {
                const startVal = this.$refs.batchStartDate.value;
                const endVal = this.$refs.batchEndDate.value;

                if (!startVal || !endVal) {
                    alert('لطفاً هر دو تاریخ شروع و پایان را وارد کنید.');
                    return;
                }

                try {
                    const response = await fetch(this.batchUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken
                        },
                        body: JSON.stringify({
                            start_date: startVal,
                            end_date: endVal,
                            is_closed: this.batchIsClosed === "1" ? 1 : 0
                        })
                    });
                    const resData = await response.json();
                    if (resData.success) {
                        this.openBatchModal = false;
                        window.location.reload();
                    }
                } catch (e) {
                    console.error('Error submitting batch holiday:', e);
                    alert('بروز خطا در ثبت تعطیلات گروهی.');
                }
            }
        }));
    });
</script>
