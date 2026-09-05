@section('title', 'مانیتورینگ زنده')

@php
    $lp = $labelProvider ?? config('booking.labels.provider', 'ارائه‌دهنده');
    $lps = $labelProviders ?? config('booking.labels.providers', 'ارائه‌دهندگان');
    $ls = $labelService ?? config('booking.labels.service', 'سرویس');
    $lss = $labelServices ?? config('booking.labels.services', 'سرویس‌ها');
    $tz = config('booking.timezones.schedule', 'Asia/Tehran');
@endphp

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
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">مانیتورینگ زنده</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پایش بلادرنگ صف، اتاق‌ها و جریان نوبت‌های امروز مراجعین</p>
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

            {{-- پیوند به ثبت نوبت جدید --}}
            <a href="{{ route('user.booking.appointments.create') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>ثبت نوبت جدید</span>
            </a>

            {{-- پیوند به لیست نوبت‌ها و تقویم --}}
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
    <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 space-y-4">
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
                           x-on:jdp:change="$el.dispatchEvent(new Event('input', { bubbles: true }))"
                           class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-3 py-2 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none dir-ltr text-right cursor-pointer">
                </div>
            </div>

            {{-- فیلتر ارائه‌دهنده (پویا بر اساس برچسب‌ها) --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">{{ $lp }}</label>
                <select wire:model.live="selectedProviderId" 
                        class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-3 py-2 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    @if(auth()->user()?->can('booking.appointments.view.all') || auth()->user()?->hasAnyRole(['super-admin', 'admin']))
                        <option value="">همه {{ $lps }}</option>
                    @endif
                    @foreach($providers as $provider)
                        <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- فیلتر سرویس (پویا بر اساس برچسب‌ها) --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">{{ $ls }}</label>
                <select wire:model.live="selectedServiceId" 
                        class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-3 py-2 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">همه {{ $lss }}</option>
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

        {{-- جستجوی سریع مراجع (نام، شماره تماس، کد ملی، شماره پرونده) --}}
        <div class="pt-2 border-t border-gray-100 dark:border-gray-700/60 flex items-center gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="searchQuery"
                       placeholder="جستجوی سریع مراجع در نوبت‌های امروز (نام، تلفن، کدملی، شماره پرونده یا یادداشت)..."
                       class="w-full text-xs rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50/60 dark:bg-gray-700/60 pr-10 pl-3 py-2 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            @if(!empty($searchQuery))
                <button wire:click="$set('searchQuery', '')" 
                        type="button" 
                        class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 px-3 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 transition">
                    پاکسازی جستجو
                </button>
            @endif
        </div>
    </div>

    {{-- کارت‌های آمار و شمارنده‌های وضعیت (KPI Cards) هماهنگ با فیلترها --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3.5">
        {{-- کل نوبت‌ها --}}
        <button type="button" 
                wire:click="$set('selectedStatus', '')"
                title="نمایش تمام نوبت‌ها"
                class="bg-white dark:bg-gray-800 p-4 rounded-2xl border text-right transition cursor-pointer hover:shadow-md {{ empty($selectedStatus) ? 'border-indigo-500 ring-2 ring-indigo-500/20 dark:border-indigo-400' : 'border-gray-100 dark:border-gray-700 hover:border-indigo-200' }} shadow-sm flex flex-col justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">کل نوبت‌های انتخابی</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-gray-900 dark:text-white">{{ $statusCounts['total'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full {{ empty($selectedStatus) ? 'bg-indigo-500' : 'bg-gray-400' }}"></span>
            </div>
        </button>

        {{-- حاضر شده در کلینیک --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-teal-100 dark:border-teal-900/30 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-medium text-teal-700 dark:text-teal-300">حاضر در کلینیک</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-teal-600 dark:text-teal-400">{{ $statusCounts['attended'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
            </div>
        </div>

        {{-- در انتظار --}}
        <button type="button" 
                wire:click="$set('selectedStatus', '{{ $selectedStatus === 'PENDING' ? '' : 'PENDING' }}')"
                title="فیلتر نوبت‌های در انتظار"
                class="bg-white dark:bg-gray-800 p-4 rounded-2xl border text-right transition cursor-pointer hover:shadow-md {{ $selectedStatus === 'PENDING' ? 'border-amber-500 ring-2 ring-amber-500/20 dark:border-amber-400' : 'border-amber-100 dark:border-amber-900/30 hover:border-amber-300' }} shadow-sm flex flex-col justify-between">
            <span class="text-xs font-medium text-amber-700 dark:text-amber-300">در انتظار</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ $statusCounts['waiting'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
            </div>
        </button>

        {{-- تایید شده --}}
        <button type="button" 
                wire:click="$set('selectedStatus', '{{ $selectedStatus === 'CONFIRMED' ? '' : 'CONFIRMED' }}')"
                title="فیلتر نوبت‌های تایید شده"
                class="bg-white dark:bg-gray-800 p-4 rounded-2xl border text-right transition cursor-pointer hover:shadow-md {{ $selectedStatus === 'CONFIRMED' ? 'border-emerald-500 ring-2 ring-emerald-500/20 dark:border-emerald-400' : 'border-emerald-100 dark:border-emerald-900/30 hover:border-emerald-300' }} shadow-sm flex flex-col justify-between">
            <span class="text-xs font-medium text-emerald-700 dark:text-emerald-300">تایید شده</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $statusCounts['confirmed'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            </div>
        </button>

        {{-- انجام شده --}}
        <button type="button" 
                wire:click="$set('selectedStatus', '{{ $selectedStatus === 'DONE' ? '' : 'DONE' }}')"
                title="فیلتر نوبت‌های انجام شده"
                class="bg-white dark:bg-gray-800 p-4 rounded-2xl border text-right transition cursor-pointer hover:shadow-md {{ $selectedStatus === 'DONE' ? 'border-blue-500 ring-2 ring-blue-500/20 dark:border-blue-400' : 'border-blue-100 dark:border-blue-900/30 hover:border-blue-300' }} shadow-sm flex flex-col justify-between">
            <span class="text-xs font-medium text-blue-700 dark:text-blue-300">انجام شده</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ $statusCounts['done'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
            </div>
        </button>

        {{-- عدم حضور --}}
        <button type="button" 
                wire:click="$set('selectedStatus', '{{ $selectedStatus === 'NO_SHOW' ? '' : 'NO_SHOW' }}')"
                title="فیلتر نوبت‌های عدم حضور"
                class="bg-white dark:bg-gray-800 p-4 rounded-2xl border text-right transition cursor-pointer hover:shadow-md {{ $selectedStatus === 'NO_SHOW' ? 'border-gray-500 ring-2 ring-gray-500/20 dark:border-gray-400' : 'border-gray-200 dark:border-gray-700 hover:border-gray-400' }} shadow-sm flex flex-col justify-between">
            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">عدم حضور (No-Show)</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-gray-700 dark:text-gray-300">{{ $statusCounts['noshow'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-gray-500"></span>
            </div>
        </button>

        {{-- لغو شده --}}
        <button type="button" 
                wire:click="$set('selectedStatus', '{{ in_array($selectedStatus, ['CANCELED_BY_ADMIN', 'CANCELED_BY_CLIENT']) ? '' : 'CANCELED_BY_ADMIN' }}')"
                title="فیلتر نوبت‌های لغو شده"
                class="bg-white dark:bg-gray-800 p-4 rounded-2xl border text-right transition cursor-pointer hover:shadow-md {{ in_array($selectedStatus, ['CANCELED_BY_ADMIN', 'CANCELED_BY_CLIENT']) ? 'border-rose-500 ring-2 ring-rose-500/20 dark:border-rose-400' : 'border-rose-100 dark:border-rose-900/30 hover:border-rose-300' }} shadow-sm flex flex-col justify-between">
            <span class="text-xs font-medium text-rose-700 dark:text-rose-300">لغو شده</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-rose-600 dark:text-rose-400">{{ $statusCounts['cancelled'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
            </div>
        </button>
    </div>

    {{-- مراجعین حاضر در سالن انتظار (Waiting Lobby Queue) --}}
    @if($lobbyPatients && $lobbyPatients->isNotEmpty())
        <div class="bg-gradient-to-r from-teal-500/10 via-emerald-500/5 to-transparent dark:from-teal-900/30 dark:via-emerald-950/20 border border-teal-200 dark:border-teal-800/60 p-5 rounded-2xl">
            <div class="flex items-center justify-between mb-3.5">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-teal-500"></span>
                    </span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                        مراجعین حاضر در سالن انتظار کلینیک ({{ $lobbyPatients->count() }} نفر)
                    </h3>
                </div>
                <span class="text-xs text-teal-700 dark:text-teal-300 font-medium">ورود این افراد در پذیرش ثبت شده و منتظر فراخوانی هستند</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($lobbyPatients as $lobbyApt)
                    <div class="bg-white dark:bg-gray-800 p-3.5 rounded-xl border border-teal-100 dark:border-teal-900/50 shadow-sm flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-teal-100 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 flex items-center justify-center font-bold text-xs">
                                {{ mb_substr($lobbyApt->client?->full_name ?? 'م', 0, 1) }}
                            </div>
                            <div>
                                <div class="font-bold text-xs text-gray-900 dark:text-white">
                                    {{ $lobbyApt->client?->full_name ?? 'مراجع بدون نام' }}
                                    @if($lobbyApt->client?->case_number)
                                        <span class="text-[10px] text-gray-400">#{{ $lobbyApt->client->case_number }}</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                    <span>{{ $lp }}: <strong>{{ $lobbyApt->provider?->name ?? '—' }}</strong></span>
                                    <span class="mx-1">•</span>
                                    <span>{{ $lobbyApt->service?->name ?? '—' }}</span>
                                </div>
                                <div class="text-[10px] text-teal-600 dark:text-teal-400 mt-0.5 font-medium flex items-center gap-2">
                                    <span>ساعت نوبت: {{ $lobbyApt->start_at_utc ? $lobbyApt->start_at_utc->timezone($tz)->format('H:i') : '—' }}</span>
                                    <span>•</span>
                                    <span>ساعت ورود: {{ $lobbyApt->entry_at_utc ? $lobbyApt->entry_at_utc->timezone($tz)->format('H:i') : '—' }}</span>
                                </div>
                            </div>
                        </div>

                        @if($quickStatusEnabled)
                            <div class="flex flex-col gap-1.5 shrink-0">
                                <button wire:click="startVisit({{ $lobbyApt->id }})" 
                                        type="button" 
                                        title="فراخوانی و شروع خدمت"
                                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 transition flex items-center gap-1 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                    <span>شروع خدمت</span>
                                </button>
                                <button wire:click="finishVisit({{ $lobbyApt->id }})" 
                                        type="button" 
                                        class="px-2 py-1 rounded-lg text-[10px] font-medium text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition text-center">
                                    ثبت اتمام (Done)
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- نمای ویژه مدیر و پذیرش: تابلوی مانیتورینگ زنده اتاق‌ها و ارائه‌دهندگان (هنگام عدم انتخاب ارائه‌دهنده خاص) --}}
    @if(empty($selectedProviderId) && $providersSummary)
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-indigo-500"></div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">
                        تابلوی پایش اتاق‌ها و {{ $lps }} (Live Rooms & {{ $lps }} Board)
                    </h2>
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    پشتیبانی از نوبت‌های هم‌زمان و تفکیک جریان کار هر اتاق
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($providersSummary as $pSummary)
                    @php
                        $p = $pSummary['provider'];
                        $pActives = $pSummary['active_patients'] ?? collect();
                        $pActive = $pSummary['active_patient'];
                        $pNexts = $pSummary['next_patients'] ?? collect();
                        $pNext = $pSummary['next_patient'];
                        $pTotal = $pSummary['total'];
                        $pDone = $pSummary['done'];
                        $pAttended = $pSummary['attended'];
                    @endphp
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border {{ $pActives->isNotEmpty() ? 'border-emerald-200 dark:border-emerald-800/60 shadow-emerald-500/5' : 'border-gray-100 dark:border-gray-700' }} p-5 flex flex-col justify-between transition hover:shadow-md">
                        <div>
                            {{-- هدر کارت ارائه‌دهنده --}}
                            <div class="flex items-center justify-between pb-3.5 border-b border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl {{ $pActives->isNotEmpty() ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }} flex items-center justify-center font-bold text-sm">
                                        {{ mb_substr($p->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ $p->name }}</h3>
                                        <div class="flex items-center gap-1.5 text-[11px] text-gray-400 mt-0.5">
                                            <span>کل نوبت‌ها: <strong>{{ $pTotal }}</strong></span>
                                            <span>•</span>
                                            <span>انجام شده: <strong>{{ $pDone }}</strong></span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    @if($pActives->count() > 1)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                            <span>{{ $pActives->count() }} مراجع هم‌زمان</span>
                                        </span>
                                    @elseif($pActives->count() === 1)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <span>در حال ارائه خدمت</span>
                                        </span>
                                    @elseif($pTotal > 0)
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                            اتاق آماده
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                            بدون نوبت امروز
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- اطلاعات بیمار / بیماران در حال ویزیت --}}
                            <div class="mt-4 space-y-3">
                                <div class="bg-gray-50/70 dark:bg-gray-700/30 p-3 rounded-xl">
                                    <div class="flex items-center justify-between text-[11px] text-gray-400 mb-1.5">
                                        <span class="font-bold text-gray-600 dark:text-gray-300">
                                            @if($pActives->count() > 1)
                                                مراجعین هم‌زمان در اتاق ({{ $pActives->count() }} نفر):
                                            @else
                                                مراجع فعلی در اتاق:
                                            @endif
                                        </span>
                                    </div>
                                    @if($pActives->isNotEmpty())
                                        <div class="space-y-2">
                                            @foreach($pActives as $pActItem)
                                                <div class="p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 flex items-center justify-between gap-2">
                                                    <div>
                                                        <div class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                                            <span>{{ $pActItem->client?->full_name ?? 'مراجع بدون نام' }}</span>
                                                            @if($pActItem->client?->case_number)
                                                                <span class="text-[10px] text-gray-400">#{{ $pActItem->client->case_number }}</span>
                                                            @endif
                                                        </div>
                                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 flex flex-wrap items-center gap-2">
                                                            <span>{{ $ls }}: {{ $pActItem->service?->name ?? '—' }}</span>
                                                            <span>•</span>
                                                            <span class="text-indigo-600 dark:text-indigo-400">ساعت نوبت: {{ $pActItem->start_at_utc ? $pActItem->start_at_utc->timezone($tz)->format('H:i') : '—' }}</span>
                                                            <span>•</span>
                                                            @if($pActItem->entry_at_utc)
                                                                <span class="text-emerald-600 dark:text-emerald-400 font-medium">ورود: {{ $pActItem->entry_at_utc->timezone($tz)->format('H:i') }}</span>
                                                            @else
                                                                <span class="text-amber-500">ورود: ثبت نشده</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if($quickStatusEnabled)
                                                        <button wire:click="finishVisit({{ $pActItem->id }})"
                                                                type="button" 
                                                                title="اتمام نوبت این مراجع"
                                                                class="px-2 py-1 text-[11px] font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition shrink-0">
                                                            اتمام
                                                        </button>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-400 py-1 italic">
                                            در حال حاضر بیماری در این اتاق حضور ندارد.
                                        </div>
                                    @endif
                                </div>

                                {{-- اطلاعات بیمار بعدی در صف این ارائه‌دهنده --}}
                                <div class="bg-gray-50/70 dark:bg-gray-700/30 p-3 rounded-xl">
                                    <div class="flex items-center justify-between text-[11px] text-gray-400 mb-1">
                                        <span class="font-bold text-gray-600 dark:text-gray-300">نوبت بعدی در صف:</span>
                                        @if($pNext)
                                            <span class="text-indigo-600 dark:text-indigo-400 font-bold">
                                                ساعت نوبت: {{ $pNext->start_at_utc ? $pNext->start_at_utc->timezone($tz)->format('H:i') : '—' }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($pNext)
                                        <div class="flex items-center justify-between gap-2">
                                            <div>
                                                <div class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                                    <span>{{ $pNext->client?->full_name ?? 'مراجع بدون نام' }}</span>
                                                    @if($pNext->entry_at_utc)
                                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-100 text-teal-800 dark:bg-teal-900/50 dark:text-teal-200">
                                                            حاضر در لابی
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                                    <span>{{ $ls }}: {{ $pNext->service?->name ?? '—' }}</span>
                                                    @if($pNexts->count() > 1)
                                                        <span class="text-indigo-500 font-medium mr-1">(+{{ $pNexts->count() - 1 }} مراجع دیگر در این ساعت)</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($quickStatusEnabled)
                                                <button wire:click="startVisit({{ $pNext->id }})"
                                                        type="button" 
                                                        class="px-2 py-1 text-[11px] font-medium text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-900/30 hover:bg-teal-100 rounded-lg transition shrink-0">
                                                    فراخوانی
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-400 py-1 italic">
                                            نوبت بعدی در صف ثبت نشده است.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- دکمه فیلتر و تمرکز روی این ارائه‌دهنده --}}
                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <button wire:click="selectProvider({{ $p->id }})" 
                                    type="button" 
                                    class="w-full py-1.5 px-3 rounded-xl bg-gray-100 hover:bg-indigo-50 hover:text-indigo-600 dark:bg-gray-700/60 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-300 text-xs font-bold text-gray-700 dark:text-gray-300 transition flex items-center justify-center gap-1.5">
                                <span>مدیریت و مشاهده صف {{ $p->name }}</span>
                                <svg class="w-3.5 h-3.5 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        {{-- نمای متمرکز برای یک ارائه‌دهنده خاص (پزشک، متخصص یا مدیر فیلتر کرده) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- کارت مراجعین فعلی (در حال دریافت خدمت) --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-emerald-100 dark:border-emerald-900/40 p-6 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute top-0 right-0 w-2 h-full bg-emerald-500"></div>

                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">
                                مراجع فعلی (در حال دریافت خدمت)
                            </h2>
                        </div>
                        @if($activePatients && $activePatients->count() > 1)
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700">
                                {{ $activePatients->count() }} مراجع هم‌زمان در اتاق
                            </span>
                        @elseif($activePatient)
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                در حال ارائه خدمت
                            </span>
                        @endif
                    </div>

                    @if($activePatients && $activePatients->isNotEmpty())
                        <div class="mt-5 space-y-4">
                            @foreach($activePatients as $actIndex => $activeItem)
                                <div class="{{ $activePatients->count() > 1 ? 'p-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/20' : '' }} space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 flex items-center justify-center font-bold text-base">
                                                {{ mb_substr($activeItem->client?->full_name ?? 'م', 0, 1) }}
                                            </div>
                                            <div>
                                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                                                    {{ $activeItem->client?->full_name ?? 'مراجع بدون نام' }}
                                                </h3>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 dir-ltr text-right">
                                                    {{ $activeItem->client?->phone ?? '—' }}
                                                </p>
                                            </div>
                                        </div>

                                        @if($activeItem->client?->case_number)
                                            <div class="text-right">
                                                <span class="text-[10px] text-gray-400 block">شماره پرونده</span>
                                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">#{{ $activeItem->client->case_number }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- مشخصات تفکیک شده زمان و خدمت --}}
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                                        {{-- ساعت نوبت --}}
                                        <div class="bg-white dark:bg-gray-700/60 p-2.5 rounded-xl border border-gray-100 dark:border-gray-600/60">
                                            <span class="text-gray-400 block mb-0.5 text-[10px]">ساعت نوبت</span>
                                            <span class="font-bold text-indigo-600 dark:text-indigo-400">
                                                {{ $activeItem->start_at_utc ? $activeItem->start_at_utc->timezone($tz)->format('H:i') : '—' }}
                                            </span>
                                        </div>

                                        {{-- ساعت ثبت ورود --}}
                                        <div class="bg-white dark:bg-gray-700/60 p-2.5 rounded-xl border border-gray-100 dark:border-gray-600/60">
                                            <span class="text-gray-400 block mb-0.5 text-[10px]">ساعت ثبت ورود</span>
                                            @if($activeItem->entry_at_utc)
                                                <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                                    {{ $activeItem->entry_at_utc->timezone($tz)->format('H:i') }}
                                                </span>
                                            @else
                                                <span class="font-medium text-amber-500 text-[11px]">ثبت نشده</span>
                                            @endif
                                        </div>

                                        {{-- خدمت --}}
                                        <div class="bg-white dark:bg-gray-700/60 p-2.5 rounded-xl border border-gray-100 dark:border-gray-600/60">
                                            <span class="text-gray-400 block mb-0.5 text-[10px]">{{ $ls }}</span>
                                            <span class="font-bold text-gray-800 dark:text-gray-200 truncate block">{{ $activeItem->service?->name ?? '—' }}</span>
                                        </div>

                                        {{-- ارائه‌دهنده --}}
                                        <div class="bg-white dark:bg-gray-700/60 p-2.5 rounded-xl border border-gray-100 dark:border-gray-600/60">
                                            <span class="text-gray-400 block mb-0.5 text-[10px]">{{ $lp }}</span>
                                            <span class="font-bold text-gray-800 dark:text-gray-200 truncate block">{{ $activeItem->provider?->name ?? '—' }}</span>
                                        </div>
                                    </div>

                                    @if($activeItem->notes)
                                        <div class="bg-amber-50/60 dark:bg-amber-900/20 p-2.5 rounded-xl border border-amber-100 dark:border-amber-800/40 text-[11px] text-amber-800 dark:text-amber-200">
                                            <span class="font-bold">یادداشت نوبت:</span>
                                            <span>{{ $activeItem->notes }}</span>
                                        </div>
                                    @endif

                                    {{-- دکمه‌های عملیات سریع برای هر مراجع هم‌زمان --}}
                                    @if($quickStatusEnabled && $activePatients->count() > 1)
                                        <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-gray-100 dark:border-gray-700">
                                            <button wire:click="finishVisit({{ $activeItem->id }})" 
                                                    type="button" 
                                                    class="flex-1 min-w-[100px] px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 transition flex items-center justify-center gap-1 shadow-sm">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                <span>اتمام خدمت این مراجع</span>
                                            </button>
                                            <button wire:click="changeStatus({{ $activeItem->id }}, '{{ \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW }}')" 
                                                    type="button" 
                                                    class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 transition">
                                                <span>عدم حضور</span>
                                            </button>
                                            <button wire:click="changeStatus({{ $activeItem->id }}, '{{ \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN }}')" 
                                                    type="button" 
                                                    class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 transition">
                                                <span>لغو</span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-12 text-center text-gray-400 dark:text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-40 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <p class="text-sm font-medium">در حال حاضر مراجعی در اتاق حضور ندارد.</p>
                            <p class="text-xs mt-1">با ثبت ورود یا فراخوانی نوبت بعدی، خدمت را آغاز کنید.</p>
                        </div>
                    @endif
                </div>

                {{-- دکمه‌های عملیات سریع برای حالت تکی بیمار فعلی --}}
                @if($activePatients && $activePatients->count() === 1 && $quickStatusEnabled)
                    @php $singleActive = $activePatients->first(); @endphp
                    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700 flex flex-wrap gap-2">
                        <button wire:click="finishVisit({{ $singleActive->id }})" 
                                type="button" 
                                class="flex-1 min-w-[120px] px-3 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 transition flex items-center justify-center gap-1 shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>اتمام خدمت (Done)</span>
                        </button>
                        @if(empty($singleActive->entry_at_utc))
                            <button wire:click="checkIn({{ $singleActive->id }})" 
                                    type="button" 
                                    class="px-3 py-2 rounded-xl text-xs font-bold text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-900/30 hover:bg-teal-100 transition">
                                <span>ثبت ورود</span>
                            </button>
                        @endif
                        <button wire:click="changeStatus({{ $singleActive->id }}, '{{ \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW }}')" 
                                type="button" 
                                class="px-3 py-2 rounded-xl text-xs font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 transition">
                            <span>عدم حضور</span>
                        </button>
                        <button wire:click="changeStatus({{ $singleActive->id }}, '{{ \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN }}')" 
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
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">نوبت بعدی در صف</h2>
                        </div>
                        @if($nextInQueue)
                            @if($nextInQueueGroup && $nextInQueueGroup->count() > 1)
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-200 border border-indigo-300 dark:border-indigo-700">
                                    {{ $nextInQueueGroup->count() }} مراجع در این نوبت
                                </span>
                            @elseif($nextInQueue->entry_at_utc)
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-teal-50 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300">
                                    حاضر در لابی کلینیک
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
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
                                        {{ mb_substr($nextInQueue->client?->full_name ?? 'م', 0, 1) }}
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

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                                {{-- ساعت نوبت --}}
                                <div class="bg-gray-50 dark:bg-gray-700/40 p-2.5 rounded-xl">
                                    <span class="text-gray-400 block mb-0.5 text-[10px]">ساعت نوبت</span>
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ $nextInQueue->start_at_utc ? $nextInQueue->start_at_utc->timezone($tz)->format('H:i') : '—' }}
                                    </span>
                                </div>

                                {{-- وضعیت حضور --}}
                                <div class="bg-gray-50 dark:bg-gray-700/40 p-2.5 rounded-xl">
                                    <span class="text-gray-400 block mb-0.5 text-[10px]">وضعیت حضور</span>
                                    @if($nextInQueue->entry_at_utc)
                                        <span class="font-bold text-teal-600 dark:text-teal-400 text-[11px]">حاضر در لابی</span>
                                    @else
                                        <span class="font-medium text-gray-500 text-[11px]">در انتظار مراجعه</span>
                                    @endif
                                </div>

                                {{-- خدمت --}}
                                <div class="bg-gray-50 dark:bg-gray-700/40 p-2.5 rounded-xl">
                                    <span class="text-gray-400 block mb-0.5 text-[10px]">{{ $ls }}</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200 truncate block">{{ $nextInQueue->service?->name ?? '—' }}</span>
                                </div>

                                {{-- ارائه‌دهنده --}}
                                <div class="bg-gray-50 dark:bg-gray-700/40 p-2.5 rounded-xl">
                                    <span class="text-gray-400 block mb-0.5 text-[10px]">{{ $lp }}</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200 truncate block">{{ $nextInQueue->provider?->name ?? '—' }}</span>
                                </div>
                            </div>

                            @if($nextInQueueGroup && $nextInQueueGroup->count() > 1)
                                <div class="bg-indigo-50/70 dark:bg-indigo-900/20 p-3 rounded-xl border border-indigo-100 dark:border-indigo-800/40">
                                    <span class="text-xs font-bold text-indigo-900 dark:text-indigo-200 block mb-2">
                                        سایر نوبت‌های هم‌زمان در همین ساعت ({{ $nextInQueueGroup->count() - 1 }} مراجع دیگر):
                                    </span>
                                    <div class="space-y-1.5">
                                        @foreach($nextInQueueGroup->skip(1) as $concurrentNext)
                                            <div class="flex items-center justify-between text-xs bg-white dark:bg-gray-800 p-2 rounded-lg border border-indigo-100 dark:border-indigo-800">
                                                <div>
                                                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ $concurrentNext->client?->full_name ?? 'مراجع' }}</span>
                                                    <span class="text-gray-400 text-[11px]">({{ $concurrentNext->service?->name }})</span>
                                                    @if($concurrentNext->entry_at_utc)
                                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-100 text-teal-800 dark:bg-teal-900/50">حاضر</span>
                                                    @endif
                                                </div>
                                                @if($quickStatusEnabled)
                                                    <button wire:click="startVisit({{ $concurrentNext->id }})" 
                                                            type="button" 
                                                            class="px-2 py-0.5 text-[10px] font-bold text-teal-700 bg-teal-50 dark:bg-teal-900/40 hover:bg-teal-100 rounded">
                                                        فراخوانی هم‌زمان
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

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
                            <p class="text-xs mt-1">تمام نوبت‌های برنامه‌ریزی‌شده ارائه خدمت شده‌اند یا نوبتی وجود ندارد.</p>
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
                                <span>ثبت ورود به لابی</span>
                            </button>
                        @endif
                        <button wire:click="startVisit({{ $nextInQueue->id }})" 
                                type="button" 
                                class="flex-1 min-w-[120px] px-3 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition flex items-center justify-center gap-1 shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                            <span>فراخوانی و شروع</span>
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
    @endif

    {{-- جدول جریان نوبت‌های امروز --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">جریان نوبت‌های امروز</h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                    {{ $appointments->count() }} نوبت
                </span>
                @if(!empty($searchQuery))
                    <span class="text-xs text-gray-400">
                        (فیلتر شده با جستجوی: «{{ $searchQuery }}»)
                    </span>
                @endif
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
                        <th class="py-3.5 px-4">ساعت نوبت</th>
                        <th class="py-3.5 px-4">نام مراجع</th>
                        <th class="py-3.5 px-4">{{ $ls }}</th>
                        <th class="py-3.5 px-4">{{ $lp }}</th>
                        <th class="py-3.5 px-4">وضعیت</th>
                        <th class="py-3.5 px-4">ساعت ورود / خروج</th>
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
                        @endphp
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/30 transition">
                            <td class="py-3.5 px-4 font-mono text-gray-400">{{ $index + 1 }}</td>
                            <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">
                                {{ $apt->start_at_utc ? $apt->start_at_utc->timezone($tz)->format('H:i') : '—' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5 font-bold text-gray-900 dark:text-white">
                                    <span>{{ $apt->client?->full_name ?? '—' }}</span>
                                    @if($apt->client?->case_number)
                                        <span class="text-[10px] font-normal text-gray-400 bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">#{{ $apt->client->case_number }}</span>
                                    @endif
                                </div>
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
                                    <div class="text-teal-600 dark:text-teal-400 font-medium">ورود: {{ $apt->entry_at_utc->timezone($tz)->format('H:i') }}</div>
                                @endif
                                @if($apt->exit_at_utc)
                                    <div>خروج: {{ $apt->exit_at_utc->timezone($tz)->format('H:i') }}</div>
                                @endif
                                @if(!$apt->entry_at_utc && !$apt->exit_at_utc)
                                    <span class="text-gray-400">ثبت نشده</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($quickStatusEnabled)
                                    <div class="inline-flex items-center gap-1.5 justify-center">
                                        {{-- ثبت ورود به لابی --}}
                                        @if(empty($apt->entry_at_utc) && !in_array($apt->status, [\Modules\Booking\Entities\Appointment::STATUS_DONE, \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN, \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT, \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW]))
                                            <button wire:click="checkIn({{ $apt->id }})" 
                                                    title="ثبت ورود مراجع"
                                                    type="button" 
                                                    class="p-1 rounded-lg text-teal-600 hover:bg-teal-50 dark:hover:bg-teal-900/30 transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                                </svg>
                                            </button>
                                        @endif

                                        {{-- شروع ویزیت / فراخوانی --}}
                                        @if(!in_array($apt->status, [\Modules\Booking\Entities\Appointment::STATUS_DONE, \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN, \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT, \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW]))
                                            <button wire:click="startVisit({{ $apt->id }})" 
                                                    title="شروع ویزیت / فراخوانی"
                                                    type="button" 
                                                    class="p-1 rounded-lg text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                </svg>
                                            </button>
                                        @endif

                                        {{-- اتمام ویزیت --}}
                                        <button wire:click="finishVisit({{ $apt->id }})" 
                                                title="علامت‌گذاری انجام شده"
                                                type="button" 
                                                class="p-1 rounded-lg text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>

                                        {{-- عدم حضور --}}
                                        <button wire:click="changeStatus({{ $apt->id }}, '{{ \Modules\Booking\Entities\Appointment::STATUS_NO_SHOW }}')" 
                                                title="علامت‌گذاری عدم حضور"
                                                type="button" 
                                                class="p-1 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                        </button>

                                        {{-- لغو نوبت --}}
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
