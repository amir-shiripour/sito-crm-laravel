@includeIf('partials.jalali-date-picker')

<div @if($pollInterval > 0) wire:poll.{{ $pollInterval }}s @endif class="space-y-6">
    {{-- هدر صفحه و ابزارها --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <rect x="2" y="3" width="20" height="14" rx="2" stroke-width="2" />
                        <line x1="8" y1="21" x2="16" y2="21" stroke-width="2" stroke-linecap="round" />
                        <line x1="12" y1="17" x2="12" y2="21" stroke-width="2" stroke-linecap="round" />
                        <path d="M7 10l3 3l2 -4l3 3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">مانیتورینگ زنده کلینیک</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پایش بلادرنگ صف و جریان نوبت‌های امروز مراجعین</p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- نشانگر وضعیت زنده (Live Pulse) --}}
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <span>پایش زنده ({{ $pollInterval }} ثانیه)</span>
            </span>

            {{-- دکمه به‌روزرسانی دستی --}}
            <button wire:click="refreshMonitoring" 
                    wire:loading.attr="disabled"
                    type="button" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-xl border border-gray-200 dark:border-gray-600 transition">
                <svg wire:loading.class="animate-spin" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>به‌روزرسانی</span>
            </button>

            {{-- پیوند به تقویم و نوبت‌ها --}}
            <a href="{{ route('user.booking.appointments.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 rounded-xl transition">
                <span>لیست نوبت‌ها</span>
            </a>
            <a href="{{ route('user.booking.schedule.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 rounded-xl transition">
                <span>برنامه زمانی</span>
            </a>
        </div>
    </div>

    {{-- نوار فیلترها و انتخابگرها --}}
    <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            {{-- فیلتر تاریخ --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">تاریخ نوبت‌ها</label>
                <div class="relative">
                    <input type="text" 
                           wire:model.live="selectedDateJalali" 
                           data-jdp
                           data-jdp-only-date
                           autocomplete="off"
                           placeholder="1405/06/14"
                           @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                           @focus="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                           @change="$wire.set('selectedDateJalali', $el.value)"
                           x-on:jdp:change="$wire.set('selectedDateJalali', $el.value)"
                           class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-3 py-2 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none dir-ltr text-right cursor-pointer">
                </div>
            </div>

            {{-- فیلتر پزشک / ارائه‌دهنده --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">پزشک / ارائه‌دهنده</label>
                <select wire:model.live="selectedProviderId" 
                        class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-3 py-2 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    @if(auth()->user()?->can('booking.appointments.view.all') || auth()->user()?->hasAnyRole(['super-admin', 'admin']))
                        <option value="">همه پزشکان / ارائه‌دهندگان</option>
                    @endif
                    @foreach($providers as $provider)
                        <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- فیلتر سرویس --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">سرویس / خدمت</label>
                <select wire:model.live="selectedServiceId" 
                        class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-3 py-2 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">همه سرویس‌ها</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- فیلتر وضعیت --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">وضعیت نوبت</label>
                <select wire:model.live="selectedStatus" 
                        class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-3 py-2 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">همه وضعیت‌ها</option>
                    @foreach($statusesList as $statusKey => $meta)
                        <option value="{{ $statusKey }}">{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- دکمه پاکسازی فیلترها --}}
            <div>
                <button wire:click="resetFilters" 
                        type="button" 
                        class="w-full text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-rose-600 dark:hover:text-rose-400 bg-gray-100 dark:bg-gray-700/60 hover:bg-rose-50 dark:hover:bg-rose-900/20 px-3 py-2 rounded-xl transition flex items-center justify-center gap-1.5 border border-transparent hover:border-rose-200 dark:hover:border-rose-800">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>تنظیم مجدد فیلترها</span>
                </button>
            </div>
        </div>
    </div>

    {{-- کارت‌های آمار و شمارنده‌های وضعیت (KPI Cards) --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3.5">
        {{-- کل نوبت‌ها --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">کل نوبت‌های روز</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-gray-900 dark:text-white">{{ $statusCounts['total'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-gray-400"></span>
            </div>
        </div>

        {{-- حاضر شده در کلینیک --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-teal-100 dark:border-teal-900/30 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-medium text-teal-700 dark:text-teal-300">حاضر شده</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-teal-600 dark:text-teal-400">{{ $statusCounts['attended'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
            </div>
        </div>

        {{-- در انتظار --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-amber-100 dark:border-amber-900/30 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-medium text-amber-700 dark:text-amber-300">در انتظار</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ $statusCounts['waiting'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
            </div>
        </div>

        {{-- تایید شده --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-emerald-100 dark:border-emerald-900/30 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-medium text-emerald-700 dark:text-emerald-300">تایید شده</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $statusCounts['confirmed'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            </div>
        </div>

        {{-- انجام شده --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-blue-100 dark:border-blue-900/30 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-medium text-blue-700 dark:text-blue-300">انجام شده</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ $statusCounts['done'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
            </div>
        </div>

        {{-- عدم حضور --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">عدم حضور (No-Show)</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-gray-700 dark:text-gray-300">{{ $statusCounts['noshow'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-gray-500"></span>
            </div>
        </div>

        {{-- لغو شده --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-rose-100 dark:border-rose-900/30 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-medium text-rose-700 dark:text-rose-300">لغو شده</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-rose-600 dark:text-rose-400">{{ $statusCounts['cancelled'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
            </div>
        </div>
    </div>

    {{-- بخش اصلی: کارت بیمار فعلی و نفر بعدی در صف --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- کارت بیمار فعلی (در حال ویزیت) --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-emerald-100 dark:border-emerald-900/40 p-6 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 right-0 w-2 h-full bg-emerald-500"></div>

            <div>
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">بیمار فعلی (در حال ویزیت)</h2>
                    </div>
                    @if($activePatient)
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                            در حال ارائه خدمت
                        </span>
                    @endif
                </div>

                @if($activePatient)
                    <div class="mt-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 flex items-center justify-center font-bold text-lg">
                                    {{ mb_substr($activePatient->client?->full_name ?? 'ب', 0, 1) }}
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                        {{ $activePatient->client?->full_name ?? 'مراجع بدون نام' }}
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 dir-ltr text-right">
                                        {{ $activePatient->client?->phone ?? '—' }}
                                    </p>
                                </div>
                            </div>

                            @if($activePatient->client?->case_number)
                                <div class="text-right">
                                    <span class="text-[11px] text-gray-400 block">شماره پرونده</span>
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300">#{{ $activePatient->client->case_number }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2 text-xs">
                            <div class="bg-gray-50 dark:bg-gray-700/40 p-3 rounded-xl">
                                <span class="text-gray-400 block mb-1">خدمت / سرویس</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $activePatient->service?->name ?? '—' }}</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/40 p-3 rounded-xl">
                                <span class="text-gray-400 block mb-1">پزشک معالج</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $activePatient->provider?->name ?? '—' }}</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/40 p-3 rounded-xl">
                                <span class="text-gray-400 block mb-1">ساعت ورود / شروع</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ $activePatient->entry_at_utc ? $activePatient->entry_at_utc->timezone(config('booking.timezones.schedule', 'Asia/Tehran'))->format('H:i') : ($activePatient->start_at_utc ? $activePatient->start_at_utc->timezone(config('booking.timezones.schedule', 'Asia/Tehran'))->format('H:i') : '—') }}
                                </span>
                            </div>
                        </div>

                        @if($activePatient->notes)
                            <div class="bg-amber-50/60 dark:bg-amber-900/20 p-3 rounded-xl border border-amber-100 dark:border-amber-800/40 text-xs text-amber-800 dark:text-amber-200">
                                <span class="font-bold block mb-0.5">یادداشت نوبت:</span>
                                <span>{{ $activePatient->notes }}</span>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="py-12 text-center text-gray-400 dark:text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-40 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <p class="text-sm font-medium">در حال حاضر بیماری در اتاق ویزیت حضور ندارد.</p>
                        <p class="text-xs mt-1">با ثبت ورود یا انتخاب وضعیت، ویزیت را شروع کنید.</p>
                    </div>
                @endif
            </div>

            {{-- دکمه‌های عملیات سریع برای بیمار فعلی --}}
            @if($activePatient && $quickStatusEnabled)
                <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700 flex flex-wrap gap-2">
                    <button wire:click="changeStatus({{ $activePatient->id }}, '{{ \Modules\Booking\Entities\Appointment::STATUS_DONE }}')" 
                            type="button" 
                            class="flex-1 min-w-[120px] px-3 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 transition flex items-center justify-center gap-1 shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>اتمام ویزیت (Done)</span>
                    </button>
                    <button wire:click="changeStatus({{ $activePatient->id }}, '{{ \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW }}')" 
                            type="button" 
                            class="px-3 py-2 rounded-xl text-xs font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 transition">
                        <span>عدم حضور</span>
                    </button>
                    <button wire:click="changeStatus({{ $activePatient->id }}, '{{ \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN }}')" 
                            type="button" 
                            class="px-3 py-2 rounded-xl text-xs font-medium text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 transition">
                        <span>لغو نوبت</span>
                    </button>
                </div>
            @endif
        </div>

        {{-- کارت نفر بعدی در صف --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-indigo-100 dark:border-indigo-900/40 p-6 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 right-0 w-2 h-full bg-indigo-500"></div>

            <div>
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">نفر بعدی در صف</h2>
                    </div>
                    @if($nextInQueue)
                        @if($nextInQueue->entry_at_utc)
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-teal-50 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300">
                                حاضر در لابی کلینیک
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                زمان‌بندی‌شده
                            </span>
                        @endif
                    @endif
                </div>

                @if($nextInQueue)
                    <div class="mt-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 flex items-center justify-center font-bold text-lg">
                                    {{ mb_substr($nextInQueue->client?->full_name ?? 'ب', 0, 1) }}
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                        {{ $nextInQueue->client?->full_name ?? 'مراجع بدون نام' }}
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 dir-ltr text-right">
                                        {{ $nextInQueue->client?->phone ?? '—' }}
                                    </p>
                                </div>
                            </div>

                            @if($nextInQueue->client?->case_number)
                                <div class="text-right">
                                    <span class="text-[11px] text-gray-400 block">شماره پرونده</span>
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300">#{{ $nextInQueue->client->case_number }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2 text-xs">
                            <div class="bg-gray-50 dark:bg-gray-700/40 p-3 rounded-xl">
                                <span class="text-gray-400 block mb-1">خدمت / سرویس</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $nextInQueue->service?->name ?? '—' }}</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/40 p-3 rounded-xl">
                                <span class="text-gray-400 block mb-1">پزشک معالج</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $nextInQueue->provider?->name ?? '—' }}</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/40 p-3 rounded-xl">
                                <span class="text-gray-400 block mb-1">ساعت نوبت</span>
                                <span class="font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ $nextInQueue->start_at_utc ? $nextInQueue->start_at_utc->timezone(config('booking.timezones.schedule', 'Asia/Tehran'))->format('H:i') : '—' }}
                                </span>
                            </div>
                        </div>

                        @if($nextInQueue->notes)
                            <div class="bg-gray-50 dark:bg-gray-700/40 p-3 rounded-xl text-xs text-gray-600 dark:text-gray-300">
                                <span class="font-bold block mb-0.5">یادداشت نوبت:</span>
                                <span>{{ $nextInQueue->notes }}</span>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="py-12 text-center text-gray-400 dark:text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-40 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-medium">نوبت بعدی در صف انتظار ثبت نشده است.</p>
                        <p class="text-xs mt-1">تمام نوبت‌های برنامه‌ریزی‌شده ویزیت شده‌اند یا نوبتی وجود ندارد.</p>
                    </div>
                @endif
            </div>

            {{-- دکمه‌های عملیات سریع برای نفر بعدی --}}
            @if($nextInQueue && $quickStatusEnabled)
                <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700 flex flex-wrap gap-2">
                    @if(empty($nextInQueue->entry_at_utc))
                        <button wire:click="checkIn({{ $nextInQueue->id }})" 
                                type="button" 
                                class="flex-1 min-w-[120px] px-3 py-2 rounded-xl text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 transition flex items-center justify-center gap-1 shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span>ثبت ورود بیمار</span>
                        </button>
                    @endif
                    <button wire:click="changeStatus({{ $nextInQueue->id }}, '{{ \Modules\Booking\Entities\Appointment::STATUS_DONE }}')" 
                            type="button" 
                            class="px-3 py-2 rounded-xl text-xs font-medium text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition">
                        <span>انجام شد</span>
                    </button>
                    <button wire:click="changeStatus({{ $nextInQueue->id }}, '{{ \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW }}')" 
                            type="button" 
                            class="px-3 py-2 rounded-xl text-xs font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 transition">
                        <span>عدم حضور</span>
                    </button>
                    <button wire:click="changeStatus({{ $nextInQueue->id }}, '{{ \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN }}')" 
                            type="button" 
                            class="px-3 py-2 rounded-xl text-xs font-medium text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 transition">
                        <span>لغو</span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- جدول جریان نوبت‌های امروز --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">جریان نوبت‌های امروز</h3>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                    {{ $appointments->count() }} نوبت
                </span>
            </div>
            @if(!$quickStatusEnabled)
                <span class="text-xs text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 px-2.5 py-1 rounded-lg border border-amber-200 dark:border-amber-800">
                    تغییر وضعیت سریع در تنظیمات غیرفعال است
                </span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-gray-50/75 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-semibold border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="py-3.5 px-4">#</th>
                        <th class="py-3.5 px-4">ساعت</th>
                        <th class="py-3.5 px-4">نام بیمار</th>
                        <th class="py-3.5 px-4">سرویس</th>
                        <th class="py-3.5 px-4">پزشک / ارائه‌دهنده</th>
                        <th class="py-3.5 px-4">وضعیت</th>
                        <th class="py-3.5 px-4">ورود / خروج</th>
                        <th class="py-3.5 px-4 text-center">عملیات سریع</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                    @forelse($appointments as $index => $apt)
                        @php
                            $statusMeta = $statusesList[$apt->status] ?? [
                                'label' => $apt->status_label,
                                'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
                            ];
                            $tz = config('booking.timezones.schedule', 'Asia/Tehran');
                        @endphp
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/30 transition">
                            <td class="py-3.5 px-4 font-mono text-gray-400">{{ $index + 1 }}</td>
                            <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">
                                {{ $apt->start_at_utc ? $apt->start_at_utc->timezone($tz)->format('H:i') : '—' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-gray-900 dark:text-white">{{ $apt->client?->full_name ?? '—' }}</div>
                                <div class="text-[11px] text-gray-400 dir-ltr text-right">{{ $apt->client?->phone ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $apt->service?->name ?? '—' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">
                                {{ $apt->provider?->name ?? '—' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusMeta['class'] }}">
                                    {{ $statusMeta['label'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-[11px] text-gray-500 dark:text-gray-400">
                                @if($apt->entry_at_utc)
                                    <div>ورود: {{ $apt->entry_at_utc->timezone($tz)->format('H:i') }}</div>
                                @endif
                                @if($apt->exit_at_utc)
                                    <div>خروج: {{ $apt->exit_at_utc->timezone($tz)->format('H:i') }}</div>
                                @endif
                                @if(!$apt->entry_at_utc && !$apt->exit_at_utc)
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($quickStatusEnabled)
                                    <div class="inline-flex items-center gap-1.5 justify-center">
                                        @if(empty($apt->entry_at_utc) && !in_array($apt->status, [\Modules\Booking\Entities\Appointment::STATUS_DONE, \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN, \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT, \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW]))
                                            <button wire:click="checkIn({{ $apt->id }})" 
                                                    title="ثبت ورود"
                                                    type="button" 
                                                    class="p-1 rounded-lg text-teal-600 hover:bg-teal-50 dark:hover:bg-teal-900/30 transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                                </svg>
                                            </button>
                                        @endif

                                        <button wire:click="changeStatus({{ $apt->id }}, '{{ \Modules\Booking\Entities\Appointment::STATUS_DONE }}')" 
                                                title="علامت‌گذاری انجام شده"
                                                type="button" 
                                                class="p-1 rounded-lg text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>

                                        <button wire:click="changeStatus({{ $apt->id }}, '{{ \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW }}')" 
                                                title="علامت‌گذاری عدم حضور"
                                                type="button" 
                                                class="p-1 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                        </button>

                                        <button wire:click="changeStatus({{ $apt->id }}, '{{ \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN }}')" 
                                                title="لغو نوبت"
                                                type="button" 
                                                class="p-1 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-[11px] text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-gray-400 dark:text-gray-500">
                                نوبتی با فیلترهای انتخابی برای این تاریخ یافت نشد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@script
<script>
    (function () {
        function registerLivewireExpirationHandler() {
            if (window.Livewire && window.Livewire.hook) {
                window.Livewire.hook('request', ({ uri, options, payload, respond, succeed, fail }) => {
                    fail(({ status, preventDefault }) => {
                        if (status === 419) {
                            if (typeof preventDefault === 'function') {
                                preventDefault();
                            }
                            // Silently refresh CSRF token in background
                            fetch('{{ route("user.booking.csrf-token") }}', {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data && data.token) {
                                    document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', data.token);
                                    if (window.axios) {
                                        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = data.token;
                                    }
                                    if (window.Livewire) {
                                        window.Livewire.dispatch('$refresh');
                                    }
                                }
                            })
                            .catch(() => {});
                        }
                    });
                });
            }
        }

        if (window.Livewire) {
            registerLivewireExpirationHandler();
        } else {
            document.addEventListener('livewire:init', registerLivewireExpirationHandler);
        }

        function initJalaliPicker() {
            if (window.jalaliDatepicker && typeof window.jalaliDatepicker.startWatch === 'function') {
                window.jalaliDatepicker.startWatch({
                    selector: '[data-jdp-only-date]',
                    minDate: 'attr',
                });
            }
        }
        initJalaliPicker();
        document.addEventListener('livewire:navigated', initJalaliPicker);

        // Keep session and CSRF token active continuously while dashboard is open
        const keepAliveTimer = setInterval(() => {
            fetch('{{ route("user.booking.csrf-token") }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.token) {
                    document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', data.token);
                    if (window.axios) {
                        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = data.token;
                    }
                }
            })
            .catch(() => {});
        }, 60000);

        document.addEventListener('livewire:navigating', () => {
            clearInterval(keepAliveTimer);
        }, { once: true });
    })();
</script>
@endscript

