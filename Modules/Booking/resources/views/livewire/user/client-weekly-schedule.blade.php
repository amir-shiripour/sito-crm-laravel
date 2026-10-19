<div class="space-y-6 font-sans"
     x-data="{
         initPickers() {
             this.$nextTick(() => {
                 if (window.jalaliDatepicker && typeof window.jalaliDatepicker.startWatch === 'function') {
                     window.jalaliDatepicker.startWatch({ selector: '[data-jdp-only-date]', date: true, time: false });
                     window.jalaliDatepicker.startWatch({ selector: '[data-jdp-only-time]', date: false, time: true, hasSecond: false });
                 }
             });
         }
     }"
     x-init="initPickers()"
     @init-pickers.window="initPickers()">
    {{-- Header Banner & Main Actions --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 p-5 rounded-2xl bg-gradient-to-r from-slate-50 via-indigo-50/30 to-white dark:from-slate-900/80 dark:via-indigo-950/20 dark:to-slate-900/60 border border-slate-200/80 dark:border-slate-800 shadow-xs">
        <div class="flex items-start gap-3.5">
            <span class="p-3 rounded-2xl bg-indigo-600/10 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-500/30 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
                        برنامه زمانی و ترجیحات هفتگی {{ $client->full_name ?? 'بیمار' }}
                    </h3>
                    @if($tempAbsenceActive)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-800/80">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            عدم حضور موقت فعال است
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                    ثبت ساعات آزاد، شیفت‌های کاری و محدودیت‌های مراجع جهت هماهنگی سریع و دقیق‌ترین زمان رزرو توسط منشی و پرسنل پذیرش
                </p>
                @if($lastUpdatedText)
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $lastUpdatedText }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Top Action Buttons --}}
        <div class="flex flex-wrap items-center gap-2.5 self-end lg:self-center shrink-0">
            <button type="button"
                    wire:click="resetSchedule"
                    wire:confirm="آیا از بازنشانی برنامه هفتگی به مقادیر پیش‌فرض اطمینان دارید؟"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/60 border border-slate-300 dark:border-slate-700 transition-all cursor-pointer active:scale-95">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>بازنشانی</span>
            </button>

            <button type="button"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-500 shadow-xs shadow-indigo-500/25 transition-all cursor-pointer active:scale-95 disabled:opacity-50">
                <span wire:loading.remove wire:target="save" class="flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>ذخیره تغییرات برنامه</span>
                </span>
                <span wire:loading wire:target="save" class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>در حال ذخیره‌سازی...</span>
                </span>
            </button>
        </div>
    </div>

    {{-- Presets Quick-Bar --}}
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 mb-3">
            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                <span class="text-indigo-600 dark:text-indigo-400">⚡</span>
                <span>الگوهای آماده برای تنظیم سریع با ۱ کلیک:</span>
            </span>
            <span class="text-[11px] text-slate-500 dark:text-slate-400">
                انتخاب الگو کل جدول روزهای هفته را متناسب با شرایط شغلی بیمار پر می‌کند
            </span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button"
                    wire:click="applyPreset('employee')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer border {{ $activePreset === 'employee' ? 'bg-indigo-600 text-white border-indigo-600 shadow-2xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700' }}">
                <span>👔</span>
                <span>شاغل اداری (عصرها)</span>
            </button>

            <button type="button"
                    wire:click="applyPreset('student')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer border {{ $activePreset === 'student' ? 'bg-indigo-600 text-white border-indigo-600 shadow-2xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700' }}">
                <span>🎓</span>
                <span>دانشجو / محصل</span>
            </button>

            <button type="button"
                    wire:click="applyPreset('morning_only')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer border {{ $activePreset === 'morning_only' ? 'bg-indigo-600 text-white border-indigo-600 shadow-2xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700' }}">
                <span>🌅</span>
                <span>فقط شیفت صبح</span>
            </button>

            <button type="button"
                    wire:click="applyPreset('evening_only')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer border {{ $activePreset === 'evening_only' ? 'bg-indigo-600 text-white border-indigo-600 shadow-2xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700' }}">
                <span>🌆</span>
                <span>فقط شیفت عصر</span>
            </button>

            <button type="button"
                    wire:click="applyPreset('all_free')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer border {{ $activePreset === 'all_free' ? 'bg-indigo-600 text-white border-indigo-600 shadow-2xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700' }}">
                <span>🟢</span>
                <span>تمام‌وقت آزاد</span>
            </button>

            <button type="button"
                    wire:click="applyPreset('even_days')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer border {{ $activePreset === 'even_days' ? 'bg-indigo-600 text-white border-indigo-600 shadow-2xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700' }}">
                <span>📅</span>
                <span>فقط روزهای زوج</span>
            </button>

            <button type="button"
                    wire:click="applyPreset('odd_days')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer border {{ $activePreset === 'odd_days' ? 'bg-indigo-600 text-white border-indigo-600 shadow-2xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700' }}">
                <span>📅</span>
                <span>فقط روزهای فرد</span>
            </button>
        </div>
    </div>

    {{-- Preferences & Temporary Absence Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Left: General Preferences --}}
        <div class="lg:col-span-1 p-5 rounded-2xl bg-white dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 space-y-4 shadow-2xs">
            <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                ترجیحات کلی نوبت‌دهی
            </h4>

            {{-- Preferred Shift --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                    شیفت ترجیحی مراجع
                </label>
                <select wire:model.live="preferredShift"
                        class="w-full text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3 py-2.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                    @foreach($shiftsList as $key => $title)
                        <option value="{{ $key }}">{{ $title }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Max appointments per week --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                    سقف مجاز نوبت در هفته
                </label>
                <div class="flex items-center gap-2">
                    <input type="number"
                           min="1"
                           max="7"
                           wire:model.live="maxAppointmentsPerWeek"
                           placeholder="مثال: ۱ یا ۲ نوبت (خالی یعنی نامحدود)"
                           class="w-full text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3 py-2.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                    <span class="text-xs text-slate-500 dark:text-slate-400 shrink-0">جلسه در هفته</span>
                </div>
            </div>

            {{-- Internal Staff Notes --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                    یادداشت‌های داخلی پرسنل برای این بیمار
                </label>
                <textarea wire:model.defer="generalNotes"
                          rows="3"
                          placeholder="مثلاً: بیمار به ترافیک عصرها حساس است، حتماً با همراه بیاید، یا سه‌شنبه‌ها کشیک بیمارستان است..."
                          class="w-full text-xs font-normal rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 p-3 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all leading-relaxed"></textarea>
            </div>
        </div>

        {{-- Right: Temporary Absence / Vacation Mode --}}
        <div class="lg:col-span-2 p-5 rounded-2xl bg-white dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 space-y-4 shadow-2xs">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </span>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                            عدم حضور موقت (مسافرت / مرخصی / بستری)
                        </h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            اگر بیمار در بازه زمانی خاصی شهر نیست یا امکان مراجعه ندارد، در این قسمت ثبت کنید
                        </p>
                    </div>
                </div>

                {{-- Toggle switch --}}
                <button type="button"
                        wire:click="$toggle('tempAbsenceActive')"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden {{ $tempAbsenceActive ? 'bg-amber-500' : 'bg-slate-200 dark:bg-slate-700' }}"
                        role="switch"
                        aria-checked="{{ $tempAbsenceActive ? 'true' : 'false' }}">
                    <span aria-hidden="true"
                          class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-md transform ring-0 transition duration-200 ease-in-out {{ $tempAbsenceActive ? 'rtl:-translate-x-5 ltr:translate-x-5' : 'translate-x-0' }}">
                    </span>
                </button>
            </div>

            @if($tempAbsenceActive)
                <div class="p-4 rounded-xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-800/60 space-y-3 transition-all">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                تاریخ شروع عدم حضور (شمسی)
                            </label>
                            <input type="text"
                                   data-jdp
                                   data-jdp-only-date
                                   placeholder="۱۴۰۵/۰۸/۰۱"
                                   wire:model.defer="tempAbsenceStartJalali"
                                   @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                   @focus="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                   @change="$wire.set('tempAbsenceStartJalali', $el.value)"
                                   autocomplete="off"
                                   class="w-full text-xs font-bold text-center rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3 py-2 cursor-pointer focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                تاریخ پایان عدم حضور (شمسی)
                            </label>
                            <input type="text"
                                   data-jdp
                                   data-jdp-only-date
                                   placeholder="۱۴۰۵/۰۸/۱۰"
                                   wire:model.defer="tempAbsenceEndJalali"
                                   @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                   @focus="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                   @change="$wire.set('tempAbsenceEndJalali', $el.value)"
                                   autocomplete="off"
                                   class="w-full text-xs font-bold text-center rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3 py-2 cursor-pointer focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            علت یا توضیحات غیبت
                        </label>
                        <input type="text"
                               wire:model.defer="tempAbsenceReason"
                               placeholder="مثلاً: سفر زیارتی / دوران نقاهت جراحی / ماموریت کاری..."
                               class="w-full text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3 py-2">
                    </div>
                </div>
            @else
                <div class="py-6 text-center text-xs text-slate-500 dark:text-slate-300">
                    در حال حاضر غیبت موقتی برای این مراجع ثبت نشده است. در صورت نیاز کلید بالا را فعال نمایید.
                </div>
            @endif
        </div>
    </div>

    {{-- Weekly Schedule Matrix (7 Days) --}}
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                <span>تقویم هفتگی و بازه‌های زمانی روزانه (شنبه تا جمعه)</span>
            </h4>
            <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                <span class="inline-flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> آزاد کامل
                </span>
                <span class="inline-flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span> ساعات مشخص
                </span>
                <span class="inline-flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> کلاً مشغول
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4">
            @foreach($daysMeta as $dayIdx => $dayMeta)
                @php
                    $dayData = $schedule[$dayIdx] ?? ['status' => 'custom', 'slots' => [], 'note' => ''];
                    $status = $dayData['status'] ?? 'custom';
                    $slots = $dayData['slots'] ?? [];
                @endphp

                <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 transition-all hover:border-indigo-300 dark:hover:border-indigo-800/60 shadow-2xs">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-3 border-b border-slate-100 dark:border-slate-800/80">
                        {{-- Day Name & Status Indicator --}}
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-sm font-bold {{ $dayMeta['is_weekend'] ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60' : 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200' }}">
                                {{ $dayMeta['short'] }}
                            </span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h5 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                                        {{ $dayMeta['name'] }}
                                    </h5>
                                    @if($dayMeta['is_weekend'])
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                            آخر هفته
                                        </span>
                                    @endif
                                </div>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                    @if($status === 'all_day_free')
                                        تمام ساعات این روز آزاد است
                                    @elseif($status === 'all_day_busy')
                                        کل روز امکان پذیرش نوبت ندارد
                                    @else
                                        {{ count($slots) }} بازه زمانی مشخص شده
                                    @endif
                                </span>
                            </div>
                        </div>

                        {{-- Day Status Button Group --}}
                        <div class="flex flex-wrap items-center gap-1.5 p-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/60">
                            <button type="button"
                                    wire:click="setDayStatus({{ $dayIdx }}, 'all_day_free')"
                                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $status === 'all_day_free' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-slate-700' }}">
                                آزاد کامل
                            </button>
                            <button type="button"
                                    wire:click="setDayStatus({{ $dayIdx }}, 'custom')"
                                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $status === 'custom' ? 'bg-indigo-600 text-white shadow-2xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-slate-700' }}">
                                ساعات مشخص
                            </button>
                            <button type="button"
                                    wire:click="setDayStatus({{ $dayIdx }}, 'all_day_busy')"
                                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $status === 'all_day_busy' ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-slate-700' }}">
                                کلاً مشغول
                            </button>
                        </div>

                        {{-- Quick Copy Dropdown (Alpine) --}}
                        <div x-data="{ open: false }" class="relative self-end md:self-center">
                            <button @click="open = !open"
                                    type="button"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition-all cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <span>کپی به سایر روزها</span>
                                <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="open"
                                 @click.outside="open = false"
                                 x-cloak
                                 class="absolute left-0 mt-1 w-52 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-lg py-1.5 z-20 space-y-0.5">
                                <button type="button"
                                        @click="open = false"
                                        wire:click="copyDayTo({{ $dayIdx }}, 'work_days')"
                                        class="w-full text-right px-3 py-1.5 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 font-bold transition-colors">
                                    کپی به روزهای کاری (شنبه تا چهارشنبه)
                                </button>
                                <button type="button"
                                        @click="open = false"
                                        wire:click="copyDayTo({{ $dayIdx }}, 'even_days')"
                                        class="w-full text-right px-3 py-1.5 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 font-bold transition-colors">
                                    کپی به روزهای زوج (ش - د - چ)
                                </button>
                                <button type="button"
                                        @click="open = false"
                                        wire:click="copyDayTo({{ $dayIdx }}, 'odd_days')"
                                        class="w-full text-right px-3 py-1.5 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 font-bold transition-colors">
                                    کپی به روزهای فرد (ی - س - پ)
                                </button>
                                <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>
                                <button type="button"
                                        @click="open = false"
                                        wire:click="copyDayTo({{ $dayIdx }}, 'all_week')"
                                        class="w-full text-right px-3 py-1.5 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 font-bold transition-colors">
                                    کپی به تمام روزهای هفته
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Day Content --}}
                    @if($status === 'custom')
                        <div class="mt-4 space-y-3">
                            @foreach($slots as $slotIdx => $slot)
                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 p-3 rounded-xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60">
                                    {{-- Time Range --}}
                                    <div class="flex items-center gap-2 shrink-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs text-slate-500 dark:text-slate-400">از</span>
                                            <input type="text"
                                                   data-jdp
                                                   data-jdp-only-time
                                                   placeholder="۰۹:۰۰"
                                                   wire:model.defer="schedule.{{ $dayIdx }}.slots.{{ $slotIdx }}.start"
                                                   @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: false, time: true, hasSecond: false}); jalaliDatepicker.show($el); }"
                                                   @focus="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: false, time: true, hasSecond: false}); jalaliDatepicker.show($el); }"
                                                   @change="$wire.set('schedule.{{ $dayIdx }}.slots.{{ $slotIdx }}.start', $el.value)"
                                                   autocomplete="off"
                                                   class="w-20 text-xs font-bold text-center rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-2 py-1.5 focus:ring-1 focus:ring-indigo-500 cursor-pointer">
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs text-slate-500 dark:text-slate-400">تا</span>
                                            <input type="text"
                                                   data-jdp
                                                   data-jdp-only-time
                                                   placeholder="۱۳:۰۰"
                                                   wire:model.defer="schedule.{{ $dayIdx }}.slots.{{ $slotIdx }}.end"
                                                   @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: false, time: true, hasSecond: false}); jalaliDatepicker.show($el); }"
                                                   @focus="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: false, time: true, hasSecond: false}); jalaliDatepicker.show($el); }"
                                                   @change="$wire.set('schedule.{{ $dayIdx }}.slots.{{ $slotIdx }}.end', $el.value)"
                                                   autocomplete="off"
                                                   class="w-20 text-xs font-bold text-center rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-2 py-1.5 focus:ring-1 focus:ring-indigo-500 cursor-pointer">
                                        </div>
                                    </div>

                                    {{-- Slot Type Selector --}}
                                    <div class="shrink-0 w-full sm:w-44">
                                        <select wire:model.defer="schedule.{{ $dayIdx }}.slots.{{ $slotIdx }}.type"
                                                class="w-full text-xs font-bold rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-2 py-1.5 focus:ring-1 focus:ring-indigo-500">
                                            <option value="preferred">🟢 ایده‌آل / ترجیحی</option>
                                            <option value="available">🔵 امکان‌پذیر / عادی</option>
                                            <option value="conditional">🟡 مشروط / با هماهنگی</option>
                                            <option value="busy">🔴 ناممکن / مشغول</option>
                                        </select>
                                    </div>

                                    {{-- Slot Note --}}
                                    <div class="grow">
                                        <input type="text"
                                               wire:model.defer="schedule.{{ $dayIdx }}.slots.{{ $slotIdx }}.note"
                                               placeholder="توضیح این بازه (مثلاً بعد از پایان کار)"
                                               class="w-full text-xs font-normal rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-2.5 py-1.5 focus:ring-1 focus:ring-indigo-500">
                                    </div>

                                    {{-- Remove Slot Button --}}
                                    <button type="button"
                                            wire:click="removeSlot({{ $dayIdx }}, {{ $slotIdx }})"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors shrink-0 self-end sm:self-center"
                                            title="حذف بازه">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach

                            <div class="flex items-center justify-between pt-1">
                                <button type="button"
                                        wire:click="addSlot({{ $dayIdx }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/60 transition-all cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>افزودن بازه زمانی جدید</span>
                                </button>

                                <input type="text"
                                       wire:model.defer="schedule.{{ $dayIdx }}.note"
                                       placeholder="یادداشت کلی برای روز {{ $dayMeta['name'] }} (اختیاری)"
                                       class="w-64 text-xs font-normal rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-2.5 py-1 focus:ring-1 focus:ring-indigo-500">
                            </div>
                        </div>
                    @elseif($status === 'all_day_free')
                        <div class="mt-3 p-3 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200/60 dark:border-emerald-800/40 flex items-center justify-between">
                            <span class="text-xs text-emerald-700 dark:text-emerald-300 font-bold flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                بیمار در تمام ساعات این روز آزاد است و محدودیتی ندارد
                            </span>
                            <input type="text"
                                   wire:model.defer="schedule.{{ $dayIdx }}.note"
                                   placeholder="توضیح اختیاری..."
                                   class="w-56 text-xs rounded-lg border border-emerald-300/60 dark:border-emerald-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-2 py-1">
                        </div>
                    @elseif($status === 'all_day_busy')
                        <div class="mt-3 p-3 rounded-xl bg-rose-50/50 dark:bg-rose-950/20 border border-rose-200/60 dark:border-rose-800/40 flex items-center justify-between">
                            <span class="text-xs text-rose-700 dark:text-rose-300 font-bold flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                بیمار در این روز کلاً امکان حضور ندارد (عدم ارائه نوبت)
                            </span>
                            <input type="text"
                                   wire:model.defer="schedule.{{ $dayIdx }}.note"
                                   placeholder="علت عدم امکان (مثلاً روز کاری ثابت)..."
                                   class="w-56 text-xs rounded-lg border border-rose-300/60 dark:border-rose-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-2 py-1">
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Bottom Save Action Bar --}}
    <div class="flex items-center justify-between p-4 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800 shadow-sm sticky bottom-4 z-10 backdrop-blur-md">
        <div class="text-xs text-slate-600 dark:text-slate-300 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>تغییرات شما به صورت ساختاریافته در پرونده بیمار ذخیره شده و منشی‌ها به آن دسترسی خواهند داشت.</span>
        </div>

        <button type="button"
                wire:click="save"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-500 shadow-xs shadow-indigo-500/25 transition-all cursor-pointer active:scale-95 disabled:opacity-50">
            <span wire:loading.remove wire:target="save" class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>ذخیره تغییرات برنامه</span>
            </span>
            <span wire:loading wire:target="save" class="flex items-center gap-1.5">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>در حال ذخیره‌سازی...</span>
            </span>
        </button>
    </div>

    @script
    <script>
        const rewatchPickers = () => {
            if (window.jalaliDatepicker && typeof window.jalaliDatepicker.startWatch === 'function') {
                window.jalaliDatepicker.startWatch({ selector: '[data-jdp-only-date]', date: true, time: false });
                window.jalaliDatepicker.startWatch({ selector: '[data-jdp-only-time]', date: false, time: true, hasSecond: false });
            }
        };
        rewatchPickers();
        $wire.on('notify', rewatchPickers);
    </script>
    @endscript
</div>
