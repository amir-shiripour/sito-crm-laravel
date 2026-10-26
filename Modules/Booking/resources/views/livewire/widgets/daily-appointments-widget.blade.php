<div class="space-y-5 font-sans relative" wire:poll.30s>
    {{-- نوار لودینگ باریک و مدرن در لبه بالای ویجت بدون بر هم زدن ظاهر عناصر --}}
    <div wire:loading.delay.shortest class="absolute -top-2.5 inset-x-0 h-1 bg-gradient-to-r from-indigo-500 via-sky-400 to-indigo-600 animate-pulse rounded-full z-20"></div>

    {{-- هدر اصلی ویجت: آیکون، عنوان داینامیک، و اکشن‌های سریع تقویم و مانیتورینگ --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800/80">
        <div class="flex items-center gap-3">
            {{-- آیکون تقویم با انیمیشن چرخش ظریف هنگام بارگذاری اطلاعات --}}
            <div class="w-11 h-11 rounded-2xl bg-indigo-600/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-500/20 shadow-xs relative">
                <svg wire:loading.remove class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <svg wire:loading class="w-5 h-5 animate-spin text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h2 class="text-base sm:text-lg font-bold text-slate-800 dark:text-slate-100 font-sans">
                        نوبت‌های روزانه
                    </h2>
                    @if($isToday)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 font-sans">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            امروز: {{ $currentJalaliLong }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30 font-sans">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            تاریخ انتخابی: {{ $currentJalaliLong }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-sans">
                    نمای زنده نوبت‌ها و پذیرش مراجعین {{ $labelProviders }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 self-end sm:self-auto flex-wrap">
            @if(Route::has('user.booking.monitoring'))
                <a href="{{ route('user.booking.monitoring') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl text-indigo-600 dark:text-indigo-300 bg-indigo-50/90 dark:bg-indigo-950/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 border border-indigo-200/80 dark:border-indigo-800/60 transition-colors font-sans">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>مانیتورینگ زنده</span>
                </a>
            @endif

            @if(Route::has('user.booking.appointments.schedule'))
                <a href="{{ route('user.booking.appointments.schedule') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700 transition-colors font-sans">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                    <span>تقویم زمان‌بندی</span>
                </a>
            @endif

            @if($canCreate && Route::has('user.booking.appointments.create'))
                <a href="{{ route('user.booking.appointments.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-500 shadow-sm transition-colors font-sans">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>نوبت جدید</span>
                </a>
            @endif
        </div>
    </div>

    {{-- نوار ابزار اصلی: سلکتور تقویم جلالی هماهنگ با پیش‌فرض تاریخ روز و فیلتر ارائه‌دهنده --}}
    <div class="p-3 rounded-2xl bg-slate-50/90 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700/60 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 shadow-2xs">
        
        {{-- ناوبری تاریخ و انتخابگر تقویم جلالی --}}
        <div
            x-data="{
                openPicker() {
                    if (window.jalaliDatepicker) {
                        if (typeof window.jalaliDatepicker.startWatch === 'function') {
                            window.jalaliDatepicker.startWatch({
                                selector: '[data-jdp-only-date]',
                                minDate: 'attr'
                            });
                        }
                        window.jalaliDatepicker.show(this.$refs.dateInput);
                    }
                }
            }"
            class="flex items-center gap-2 flex-wrap"
        >
            {{-- باکس ناوبری تقویم: روز قبل، کپسول تقویم جلالی، و روز بعد --}}
            <div class="flex items-center bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-1 shadow-2xs">
                {{-- روز قبل (در تقویم RTL دکمه راست به روز قبل می‌رود) --}}
                <button
                    type="button"
                    wire:click="previousDay"
                    wire:loading.attr="disabled"
                    class="p-2 rounded-lg text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors disabled:opacity-50"
                    title="روز قبل"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                {{-- کپسول تعاملی تاریخ جلالی: همواره دارای پیش‌فرض تاریخ روز و بازشونده با کلیک --}}
                <div
                    @click="openPicker()"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-750 transition-colors cursor-pointer group"
                    title="کلیک برای باز شدن تقویم جلالی و انتخاب تاریخ"
                >
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>

                    {{-- فیلد متنی تقویم جلالی با مقدار پیش‌فرض --}}
                    <input
                        x-ref="dateInput"
                        type="text"
                        id="widget_daily_date_selector"
                        value="{{ $selectedDateJalali }}"
                        data-jdp
                        data-jdp-only-date
                        autocomplete="off"
                        readonly
                        @click.stop="openPicker()"
                        @focus.stop="openPicker()"
                        @change="$wire.selectDate($el.value)"
                        class="w-24 text-center text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 bg-transparent border-0 p-0 focus:ring-0 focus:outline-none font-sans cursor-pointer select-none"
                    />

                    {{-- روز هفته هماهنگ با تاریخ انتخابی --}}
                    <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 font-sans border-r border-slate-200 dark:border-slate-700 pr-2">
                        {{ $dayOfWeekName }}
                    </span>

                    {{-- نشانگر فلش بازشونده --}}
                    <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>

                {{-- روز بعد (در تقویم RTL دکمه چپ به روز بعد می‌رود) --}}
                <button
                    type="button"
                    wire:click="nextDay"
                    wire:loading.attr="disabled"
                    class="p-2 rounded-lg text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors disabled:opacity-50"
                    title="روز بعد"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            {{-- دکمه سریع بازگشت به تاریخ امروز اگر در تاریخی غیر از امروز باشیم --}}
            @if(!$isToday)
                <button
                    type="button"
                    wire:click="goToToday"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-all transform active:scale-95 font-sans"
                    title="بازگشت به تاریخ امروز"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>امروز ({{ $todayJalaliShort }})</span>
                </button>
            @endif
        </div>

        {{-- فیلتر داینامیک پزشک / ارائه‌دهنده (داینامیک بر اساس تنظیمات /admin/booking/settings/label) --}}
        @if($canViewAll && $providers->count() > 0)
            <div class="flex items-center gap-2">
                <label class="text-xs text-slate-500 dark:text-slate-400 shrink-0 font-medium font-sans">{{ $labelProvider }}:</label>
                <select
                    wire:model.live="selectedProviderId"
                    class="w-full sm:w-48 text-xs font-medium px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-sans shadow-2xs"
                >
                    <option value="">همه {{ $labelProviders }}</option>
                    @foreach($providers as $prov)
                        <option value="{{ $prov->id }}">{{ $prov->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    {{-- کارت‌های آمار نوبت‌ها برای تاریخ انتخابی --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5" wire:loading.class="opacity-60">
        <button type="button" wire:click="setTab('all')" class="flex items-center justify-between p-2.5 rounded-xl border text-right transition-all font-sans {{ $activeTab === 'all' ? 'bg-indigo-50/80 dark:bg-indigo-950/40 border-indigo-400 dark:border-indigo-600 text-indigo-700 dark:text-indigo-300 shadow-xs' : 'bg-white dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700/60 text-slate-700 dark:text-slate-300 hover:border-slate-300 dark:hover:border-slate-600' }}">
            <span class="text-xs">کل نوبت‌ها</span>
            <span class="text-sm font-bold font-sans">{{ $stats['total'] }}</span>
        </button>

        <button type="button" wire:click="setTab('waiting')" class="flex items-center justify-between p-2.5 rounded-xl border text-right transition-all font-sans {{ $activeTab === 'waiting' ? 'bg-amber-50/80 dark:bg-amber-950/40 border-amber-400 dark:border-amber-600 text-amber-800 dark:text-amber-200 shadow-xs' : 'bg-white dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700/60 text-slate-700 dark:text-slate-300 hover:border-slate-300 dark:hover:border-slate-600' }}">
            <span class="text-xs flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                در انتظار
            </span>
            <span class="text-sm font-bold font-sans text-amber-600 dark:text-amber-400">{{ $stats['waiting'] }}</span>
        </button>

        <button type="button" wire:click="setTab('in_progress')" class="flex items-center justify-between p-2.5 rounded-xl border text-right transition-all font-sans {{ $activeTab === 'in_progress' ? 'bg-cyan-50/80 dark:bg-cyan-950/40 border-cyan-400 dark:border-cyan-600 text-cyan-800 dark:text-cyan-200 shadow-xs' : 'bg-white dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700/60 text-slate-700 dark:text-slate-300 hover:border-slate-300 dark:hover:border-slate-600' }}">
            <span class="text-xs flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                در حال ویزیت
            </span>
            <span class="text-sm font-bold font-sans text-cyan-600 dark:text-cyan-400">{{ $stats['in_progress'] }}</span>
        </button>

        <button type="button" wire:click="setTab('done')" class="flex items-center justify-between p-2.5 rounded-xl border text-right transition-all font-sans {{ $activeTab === 'done' ? 'bg-emerald-50/80 dark:bg-emerald-950/40 border-emerald-400 dark:border-emerald-600 text-emerald-800 dark:text-emerald-200 shadow-xs' : 'bg-white dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700/60 text-slate-700 dark:text-slate-300 hover:border-slate-300 dark:hover:border-slate-600' }}">
            <span class="text-xs flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                تکمیل‌شده
            </span>
            <span class="text-sm font-bold font-sans text-emerald-600 dark:text-emerald-400">{{ $stats['done'] }}</span>
        </button>

        <div class="col-span-2 sm:col-span-1 flex items-center justify-between p-2.5 rounded-xl border bg-white dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700/60 text-slate-700 dark:text-slate-300 font-sans">
            <span class="text-xs text-rose-600 dark:text-rose-400">لغو / غیبت</span>
            <span class="text-sm font-bold font-sans text-rose-600 dark:text-rose-400">{{ $stats['canceled'] }}</span>
        </div>
    </div>

    {{-- نوار ابزار جستجو و چیدمان بر اساس ساعت --}}
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        {{-- نوار جستجو --}}
        <div class="relative flex-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="جستجوی مراجع، شماره تلفن، کد ملی یا نوع {{ $labelService }}..."
                class="w-full text-xs pr-9 pl-8 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-sans shadow-2xs"
            />
            <svg class="w-4 h-4 absolute right-3 top-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>

            @if(!empty($search))
                <button
                    type="button"
                    wire:click="$set('search', '')"
                    class="absolute left-2.5 top-2.5 p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-md"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            @endif
        </div>

        {{-- کنترل چیدمان بر اساس کمترین یا بیشترین ساعت نوبت --}}
        <div class="flex items-center gap-1 bg-slate-100/90 dark:bg-slate-800/90 p-1 rounded-xl border border-slate-200 dark:border-slate-700 shrink-0">
            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium px-2 flex items-center gap-1.5 font-sans">
                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>چیدمان:</span>
            </span>

            {{-- کمترین ساعت (زودترین نوبت اول) --}}
            <button
                type="button"
                wire:click="setTimeSort('asc')"
                class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all font-sans flex items-center gap-1 {{ $timeSort === 'asc' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-300 shadow-2xs border border-slate-200/60 dark:border-slate-600' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}"
                title="نمایش نوبت‌ها از کمترین ساعت (صبح به بعد)"
            >
                <svg class="w-3 h-3 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                </svg>
                <span>کمترین ساعت</span>
            </button>

            {{-- بیشترین ساعت (دیرترین نوبت اول) --}}
            <button
                type="button"
                wire:click="setTimeSort('desc')"
                class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all font-sans flex items-center gap-1 {{ $timeSort === 'desc' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-300 shadow-2xs border border-slate-200/60 dark:border-slate-600' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}"
                title="نمایش نوبت‌ها از بیشترین ساعت (عصر/شب به قبل)"
            >
                <svg class="w-3 h-3 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
                <span>بیشترین ساعت</span>
            </button>
        </div>
    </div>

    {{-- لیست نوبت‌ها با استایل درشت و خوانا --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700/70 divide-y divide-slate-100 dark:divide-slate-800/80 bg-white dark:bg-slate-800/50 shadow-2xs" wire:loading.class="opacity-60 pointer-events-none">
        @forelse($appointments as $appointment)
            @php
                $statusList = \Modules\Booking\Entities\Appointment::statusesList();
                $statusInfo = $statusList[$appointment->status] ?? ['label' => $appointment->status_label, 'class' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'];

                $startTime = $appointment->start_at_utc ? $appointment->start_at_utc->copy()->timezone($scheduleTz)->format('H:i') : '--:--';
                $endTime = $appointment->end_at_utc ? $appointment->end_at_utc->copy()->timezone($scheduleTz)->format('H:i') : '--:--';

                $hasArrived = !empty($appointment->entry_at_utc);
                $isInProgress = $appointment->status === \Modules\Booking\Entities\Appointment::STATUS_IN_PROGRESS;
                $isDone = $appointment->status === \Modules\Booking\Entities\Appointment::STATUS_DONE;
                $isCanceled = in_array($appointment->status, [
                    \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN,
                    \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT,
                    \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW
                ], true);
            @endphp

            <div class="p-4 hover:bg-slate-50/80 dark:hover:bg-slate-800/90 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4 font-sans">
                {{-- ستون اطلاعات نوبت و مراجع --}}
                <div class="flex items-center gap-3.5 min-w-0">
                    {{-- جعبه ساعت نوبت: درشت، واضح و کاملاً خوانا --}}
                    <div class="flex flex-col items-center justify-center w-24 sm:w-28 shrink-0 py-2.5 px-2 rounded-2xl border text-center shadow-xs transition-colors {{ $isInProgress ? 'bg-cyan-50/90 dark:bg-cyan-950/50 border-cyan-300 dark:border-cyan-700/80 text-cyan-900 dark:text-cyan-200' : ($hasArrived ? 'bg-emerald-50/90 dark:bg-emerald-950/50 border-emerald-300 dark:border-emerald-700/80 text-emerald-900 dark:text-emerald-200' : 'bg-slate-50/90 dark:bg-slate-900/70 border-slate-200/90 dark:border-slate-700/80 text-slate-800 dark:text-slate-200') }}">
                        {{-- ساعت شروع با فونت درشت و برجسته --}}
                        <span class="text-base sm:text-lg font-black tracking-tight font-sans text-slate-900 dark:text-slate-100">
                            {{ $startTime }}
                        </span>

                        {{-- ساعت پایان نوبت --}}
                        <span class="text-xs font-bold font-sans text-slate-500 dark:text-slate-400">
                            تا {{ $endTime }}
                        </span>

                        {{-- نشانگر وضعیت حضور یا ویزیت جاری --}}
                        @if($isInProgress)
                            <span class="mt-1 inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-cyan-500/20 text-cyan-800 dark:text-cyan-200 border border-cyan-500/30 font-sans animate-pulse">
                                <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>
                                ویزیت
                            </span>
                        @elseif($hasArrived)
                            <span class="mt-1 inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/20 text-emerald-800 dark:text-emerald-200 border border-emerald-500/30 font-sans">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                حاضر
                            </span>
                        @endif
                    </div>

                    {{-- مشخصات مراجع و اطلاعات خدمت --}}
                    <div class="min-w-0 flex-1 space-y-1.5">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            {{-- نام بیمار --}}
                            <span class="text-sm sm:text-base font-bold text-slate-800 dark:text-slate-100 font-sans">
                                {{ $appointment->client?->full_name ?? 'بدون نام' }}
                            </span>

                            @if($appointment->client?->phone)
                                <a href="tel:{{ $appointment->client->phone }}" class="text-xs text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 font-sans dir-ltr">
                                    {{ $appointment->client->phone }}
                                </a>
                            @endif

                            {{-- بج وضعیت نوبت --}}
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium {{ $statusInfo['class'] }} font-sans">
                                {{ $statusInfo['label'] }}
                            </span>
                        </div>

                        <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 flex-wrap font-sans">
                            {{-- نوع خدمت / سرویس --}}
                            <span class="inline-flex items-center gap-1.5 font-sans" title="{{ $labelService }}">
                                <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $appointment->service?->color ?? '#6366f1' }}"></span>
                                <span class="font-medium text-slate-700 dark:text-slate-300">{{ $appointment->service?->name ?? 'سرویس عمومی' }}</span>
                            </span>

                            {{-- نام ارائه‌دهنده (داینامیک) --}}
                            @if($appointment->provider)
                                <span class="inline-flex items-center gap-1 text-slate-600 dark:text-slate-300 font-sans" title="{{ $labelProvider }}">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ $appointment->provider->name }}
                                </span>
                            @endif

                            {{-- شماره پرونده بیمار --}}
                            @if(!empty($appointment->client?->case_number))
                                <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-750 px-2 py-0.5 rounded-md border border-slate-200/80 dark:border-slate-700/80 font-sans">
                                    پرونده: {{ $appointment->client->case_number }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ستون اکشن‌های سریع، دکمه انتقال به پرونده و تغییر وضعیت --}}
                <div class="flex items-center gap-2 shrink-0 self-end md:self-center flex-wrap">
                    {{-- دکمه اختصاصی پرونده بیمار --}}
                    @if($appointment->client_id && Route::has('user.clients.show'))
                        <a
                            href="{{ route('user.clients.show', $appointment->client_id) }}"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-750 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 hover:text-indigo-600 dark:hover:text-indigo-300 border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-700 transition-all font-sans shadow-2xs"
                            title="مشاهده و ویرایش پرونده کامل بیمار"
                        >
                            <svg class="w-3.5 h-3.5 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>پرونده بیمار</span>
                        </a>
                    @endif

                    @if($canEdit && !$isDone && !$isCanceled)
                        {{-- ۱. ثبت ورود مراجع --}}
                        @if(!$hasArrived)
                            <button
                                type="button"
                                wire:click="checkIn({{ $appointment->id }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 transition-all font-sans shadow-2xs"
                                title="ثبت ورود مراجع به کلینیک"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>ثبت ورود</span>
                            </button>
                        @endif

                        {{-- ۲. شروع ویزیت --}}
                        @if(!$isInProgress)
                            <button
                                type="button"
                                wire:click="startVisit({{ $appointment->id }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-500 shadow-2xs transition-all font-sans"
                                title="شروع معاینه و ورود مراجع به اتاق ویزیت"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>شروع ویزیت</span>
                            </button>
                        @endif

                        {{-- ۳. پایان ویزیت --}}
                        @if($isInProgress)
                            <button
                                type="button"
                                wire:click="finishVisit({{ $appointment->id }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 shadow-2xs transition-all font-sans"
                                title="ثبت خاتمه ویزیت و خروج مراجع"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>پایان ویزیت</span>
                            </button>
                        @endif

                        {{-- منوی عملیات بیشتر --}}
                        <div x-data="{ open: false }" class="relative">
                            <button
                                @click="open = !open"
                                @click.outside="open = false"
                                type="button"
                                class="p-2 rounded-xl text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200/60 dark:border-slate-700/60 transition-colors"
                                title="عملیات بیشتر"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                </svg>
                            </button>

                            <div
                                x-show="open"
                                x-transition
                                class="absolute left-0 mt-1 w-44 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xl py-1.5 z-30 font-sans"
                                style="display: none;"
                            >
                                @if($appointment->client_id && Route::has('user.clients.show'))
                                    <a
                                        href="{{ route('user.clients.show', $appointment->client_id) }}"
                                        target="_blank"
                                        class="w-full text-right px-3.5 py-2 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50 flex items-center gap-2 font-sans"
                                    >
                                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span>مشاهده پرونده بیمار</span>
                                    </a>
                                @endif

                                <button
                                    type="button"
                                    wire:click="quickChangeStatus({{ $appointment->id }}, 'CONFIRMED')"
                                    @click="open = false"
                                    class="w-full text-right px-3.5 py-2 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50 flex items-center gap-2 font-sans"
                                >
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span>تایید نوبت</span>
                                </button>

                                <button
                                    type="button"
                                    wire:click="markNoShow({{ $appointment->id }})"
                                    @click="open = false"
                                    class="w-full text-right px-3.5 py-2 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50 flex items-center gap-2 font-sans"
                                >
                                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                    <span>عدم حضور</span>
                                </button>

                                <button
                                    type="button"
                                    wire:click="quickChangeStatus({{ $appointment->id }}, 'CANCELED_BY_ADMIN')"
                                    @click="open = false"
                                    class="w-full text-right px-3.5 py-2 text-xs text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 flex items-center gap-2 font-sans"
                                >
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                    <span>لغو نوبت</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- لینک مشاهده کامل نوبت --}}
                    @if(Route::has('user.booking.appointments.show'))
                        <a
                            href="{{ route('user.booking.appointments.show', $appointment->id) }}"
                            class="p-2 rounded-xl text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200/60 dark:border-slate-700/60 transition-colors"
                            title="مشاهده جزئیات کامل نوبت"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="py-12 px-4 text-center font-sans space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300 font-sans">
                        هیچ نوبتی برای {{ $currentJalaliLong }} ثبت نشده است
                    </p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 font-sans">
                        می‌توانید با استفاده از فلش‌های تاریخ یا انتخابگر تقویم، نوبت‌های سایر روزها را بررسی فرمایید.
                    </p>
                </div>

                @if(!empty($search) || !empty($selectedProviderId) || $activeTab !== 'all')
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 border border-indigo-200/80 dark:border-indigo-800/60 transition-all font-sans"
                    >
                        <span>حذف فیلترها و نمایش همه</span>
                    </button>
                @endif
            </div>
        @endforelse

        {{-- صفحه‌بندی هر ۱۰ تا با استایل هماهنگ لایووایر و فونت یکپارچه IRANYekanX --}}
        @if($appointments->hasPages())
            <div class="p-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/40 flex flex-col sm:flex-row items-center justify-between gap-3 font-sans">
                <div class="text-xs text-slate-500 dark:text-slate-400 font-sans">
                    نمایش نوبت‌های
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $appointments->firstItem() }}</span>
                    تا
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $appointments->lastItem() }}</span>
                    از مجموع
                    <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $appointments->total() }}</span>
                    نوبت (صفحه {{ $appointments->currentPage() }} از {{ $appointments->lastPage() }})
                </div>

                <div class="flex items-center gap-1 font-sans">
                    {{-- دکمه صفحه قبل --}}
                    <button
                        type="button"
                        wire:click="previousPage"
                        wire:loading.attr="disabled"
                        @if($appointments->onFirstPage()) disabled @endif
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold transition-all font-sans {{ $appointments->onFirstPage() ? 'opacity-40 cursor-not-allowed text-slate-400 bg-slate-100 dark:bg-slate-800 border border-transparent' : 'text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 shadow-2xs' }}"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span>قبلی</span>
                    </button>

                    {{-- شماره صفحات --}}
                    @foreach($appointments->getUrlRange(max(1, $appointments->currentPage() - 2), min($appointments->lastPage(), $appointments->currentPage() + 2)) as $page => $url)
                        <button
                            type="button"
                            wire:click="gotoPage({{ $page }})"
                            wire:loading.attr="disabled"
                            class="w-8 h-8 rounded-xl text-xs font-bold transition-all font-sans {{ $page == $appointments->currentPage() ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700' }}"
                        >
                            {{ $page }}
                        </button>
                    @endforeach

                    {{-- دکمه صفحه بعد --}}
                    <button
                        type="button"
                        wire:click="nextPage"
                        wire:loading.attr="disabled"
                        @if(!$appointments->hasMorePages()) disabled @endif
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold transition-all font-sans {{ !$appointments->hasMorePages() ? 'opacity-40 cursor-not-allowed text-slate-400 bg-slate-100 dark:bg-slate-800 border border-transparent' : 'text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 shadow-2xs' }}"
                    >
                        <span>بعدی</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
