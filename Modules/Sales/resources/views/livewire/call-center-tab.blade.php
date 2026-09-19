@php
    $inputClass = "w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 px-3.5 py-2 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors shadow-xs";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";
@endphp

<div class="space-y-5" dir="rtl">
    {{-- اسکریپت و استایل JalaliDatePicker --}}
    @includeIf('partials.jalali-date-picker')

    <!-- ==========================================
         1. TOP KPI SUMMARY CARDS
         ========================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- کل تماس‌های امروز -->
        <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">تماس‌های امروز</span>
                <span class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white block">
                    {{ $callKpis['today_total'] ?? 0 }}
                </span>
                <span class="text-[10px] text-gray-400 dark:text-gray-500 block">مجموع ثبت‌شده‌ها</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100/60 dark:border-indigo-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </div>
        </div>

        <!-- پاسخ داده شده / موفق -->
        <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">پاسخ داده شده</span>
                <span class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 block">
                    {{ $callKpis['today_done'] ?? 0 }}
                </span>
                <span class="text-[10px] text-emerald-600/80 dark:text-emerald-400/80 block">مذاکره انجام‌شده</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100/60 dark:border-emerald-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- برنامه‌ریزی شده / پیگیری امروز -->
        <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">برنامه‌ریزی شده</span>
                <span class="text-xl sm:text-2xl font-black text-sky-600 dark:text-sky-400 block">
                    {{ $callKpis['today_planned'] ?? 0 }}
                </span>
                <span class="text-[10px] text-sky-600/80 dark:text-sky-400/80 block">نوبت‌های پیش‌رو</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-100/60 dark:border-sky-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>

        <!-- بدون پاسخ / اشغال -->
        <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">بی‌پاسخ / اشغال</span>
                <span class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 block">
                    {{ $callKpis['today_missed'] ?? 0 }}
                </span>
                <span class="text-[10px] text-amber-600/80 dark:text-amber-400/80 block">نیاز به تماس مجدد</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-100/60 dark:border-amber-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- ==========================================
         2. FILTER & ACTION TOOLBAR
         ========================================== -->
    <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 p-4 rounded-2xl shadow-xs space-y-3.5">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            @if(!$selectedClientId)
                <!-- Search Input (Only displayed when NO active client is selected) -->
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           class="{{ $inputClass }} pr-10"
                           placeholder="جستجو بر اساس نام مشتری، موضوع، نتیجه، شماره...">
                </div>
            @endif

            <!-- Advanced Status Filter Dropdown (Custom & Interactive) -->
            <div x-data="{ open: false }" class="relative {{ $selectedClientId ? 'w-full sm:w-72' : 'w-full sm:w-64' }} shrink-0" @click.outside="open = false">
                <!-- Dropdown Trigger Button -->
                <button type="button"
                        @click="open = !open"
                        class="w-full flex items-center justify-between gap-2 px-3.5 py-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-700 dark:text-gray-200 shadow-xs hover:border-gray-300 dark:hover:border-gray-600 transition-colors">
                    <div class="flex items-center gap-2 truncate">
                        @if($filterStatus === 'done')
                            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                            <span class="font-bold text-emerald-700 dark:text-emerald-300">انجام شده / پاسخ داده شده</span>
                        @elseif($filterStatus === 'planned')
                            <span class="w-2 h-2 rounded-full bg-sky-500 shrink-0"></span>
                            <span class="font-bold text-sky-700 dark:text-sky-300">برنامه‌ریزی شده</span>
                        @elseif($filterStatus === 'no_answer')
                            <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                            <span class="font-bold text-rose-700 dark:text-rose-300">بدون پاسخ</span>
                        @elseif($filterStatus === 'busy')
                            <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                            <span class="font-bold text-amber-700 dark:text-amber-300">اشغال</span>
                        @elseif($filterStatus === 'failed')
                            <span class="w-2 h-2 rounded-full bg-rose-600 shrink-0"></span>
                            <span class="font-bold text-rose-700 dark:text-rose-300">ناموفق</span>
                        @elseif($filterStatus === 'cancelled')
                            <span class="w-2 h-2 rounded-full bg-gray-400 shrink-0"></span>
                            <span class="font-bold text-gray-600 dark:text-gray-400">لغو شده</span>
                        @else
                            <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            <span class="font-semibold text-gray-600 dark:text-gray-400">همه وضعیت‌ها</span>
                        @endif
                    </div>
                    
                    <div class="flex items-center gap-1.5 shrink-0">
                        @if($filterStatus)
                            <span wire:click.stop="$set('filterStatus', '')"
                                  title="پاک کردن فیلتر وضعیت"
                                  class="p-0.5 text-gray-400 hover:text-rose-500 rounded transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </span>
                        @endif
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </button>

                <!-- Dropdown Popover List -->
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute z-50 right-0 left-0 mt-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl py-1 text-xs overflow-hidden divide-y divide-gray-100 dark:divide-gray-700/60"
                     style="display: none;">
                    
                    <!-- All -->
                    <button type="button"
                            wire:click="$set('filterStatus', '')"
                            @click="open = false"
                            class="w-full text-right px-3 py-2 flex items-center justify-between transition-colors {{ empty($filterStatus) ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-bold' : 'hover:bg-gray-50 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-200' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            <span>همه وضعیت‌ها</span>
                        </div>
                        @if(empty($filterStatus))
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif
                    </button>

                    <!-- Done / Answered -->
                    <button type="button"
                            wire:click="$set('filterStatus', 'done')"
                            @click="open = false"
                            class="w-full text-right px-3 py-2 flex items-center justify-between transition-colors {{ $filterStatus === 'done' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold' : 'hover:bg-gray-50 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-200' }}">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                            <span>انجام شده / پاسخ داده شده</span>
                        </div>
                        @if($filterStatus === 'done')
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif
                    </button>

                    <!-- Planned -->
                    <button type="button"
                            wire:click="$set('filterStatus', 'planned')"
                            @click="open = false"
                            class="w-full text-right px-3 py-2 flex items-center justify-between transition-colors {{ $filterStatus === 'planned' ? 'bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 font-bold' : 'hover:bg-gray-50 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-200' }}">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-sky-500 shrink-0"></span>
                            <span>برنامه‌ریزی شده</span>
                        </div>
                        @if($filterStatus === 'planned')
                            <svg class="w-4 h-4 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif
                    </button>

                    <!-- No Answer -->
                    <button type="button"
                            wire:click="$set('filterStatus', 'no_answer')"
                            @click="open = false"
                            class="w-full text-right px-3 py-2 flex items-center justify-between transition-colors {{ $filterStatus === 'no_answer' ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 font-bold' : 'hover:bg-gray-50 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-200' }}">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                            <span>بدون پاسخ</span>
                        </div>
                        @if($filterStatus === 'no_answer')
                            <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif
                    </button>

                    <!-- Busy -->
                    <button type="button"
                            wire:click="$set('filterStatus', 'busy')"
                            @click="open = false"
                            class="w-full text-right px-3 py-2 flex items-center justify-between transition-colors {{ $filterStatus === 'busy' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-bold' : 'hover:bg-gray-50 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-200' }}">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                            <span>اشغال</span>
                        </div>
                        @if($filterStatus === 'busy')
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif
                    </button>

                    <!-- Failed -->
                    <button type="button"
                            wire:click="$set('filterStatus', 'failed')"
                            @click="open = false"
                            class="w-full text-right px-3 py-2 flex items-center justify-between transition-colors {{ $filterStatus === 'failed' ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 font-bold' : 'hover:bg-gray-50 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-200' }}">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-600 shrink-0"></span>
                            <span>ناموفق</span>
                        </div>
                        @if($filterStatus === 'failed')
                            <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif
                    </button>

                    <!-- Cancelled -->
                    <button type="button"
                            wire:click="$set('filterStatus', 'cancelled')"
                            @click="open = false"
                            class="w-full text-right px-3 py-2 flex items-center justify-between transition-colors {{ $filterStatus === 'cancelled' ? 'bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-200 font-bold' : 'hover:bg-gray-50 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-200' }}">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-gray-400 shrink-0"></span>
                            <span>لغو شده</span>
                        </div>
                        @if($filterStatus === 'cancelled')
                            <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif
                    </button>
                </div>
            </div>

            <!-- New Call Button -->
            <div class="shrink-0">
                <button wire:click="openCreateModal"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-bold shadow-xs transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>ثبت تماس جدید</span>
                </button>
            </div>
        </div>

        <!-- Sub-Bar: Segmented Date Navigation & Quick Status Chips -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between border-t border-gray-100 dark:border-gray-700/60 pt-3 gap-3">
            <!-- Date Filter (Segmented Pills) -->
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 shrink-0">بازه زمانی:</span>
                <div class="inline-flex p-1 bg-gray-100/80 dark:bg-gray-900/60 rounded-xl border border-gray-200/60 dark:border-gray-700/60 gap-1 text-xs">
                    <button wire:click="$set('filterDate', 'today')"
                            class="px-3 py-1 rounded-lg font-bold transition-all {{ $filterDate === 'today' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        امروز
                    </button>
                    <button wire:click="$set('filterDate', 'week')"
                            class="px-3 py-1 rounded-lg font-bold transition-all {{ $filterDate === 'week' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        این هفته
                    </button>
                    <button wire:click="$set('filterDate', 'month')"
                            class="px-3 py-1 rounded-lg font-bold transition-all {{ $filterDate === 'month' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        این ماه
                    </button>
                    <button wire:click="$set('filterDate', 'all')"
                            class="px-3 py-1 rounded-lg font-bold transition-all {{ $filterDate === 'all' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        همه
                    </button>
                </div>
            </div>

            <!-- Quick Status Chips (Advanced & Rapid Filtering) -->
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 shrink-0 ml-1">فیلتر سریع:</span>
                <button wire:click="$set('filterStatus', '')"
                        class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ empty($filterStatus) ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200' }}">
                    همه
                </button>
                <button wire:click="$set('filterStatus', 'done')"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $filterStatus === 'done' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    انجام شده
                </button>
                <button wire:click="$set('filterStatus', 'planned')"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $filterStatus === 'planned' ? 'bg-sky-50 text-sky-700 dark:bg-sky-950/50 dark:text-sky-300 border border-sky-200 dark:border-sky-800 shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                    برنامه‌ریزی شده
                </button>
                <button wire:click="$set('filterStatus', 'no_answer')"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $filterStatus === 'no_answer' ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200 dark:border-rose-800 shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    بدون پاسخ
                </button>
                <button wire:click="$set('filterStatus', 'busy')"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $filterStatus === 'busy' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    اشغال
                </button>
            </div>
        </div>
    </div>

    <!-- ==========================================
         3. CALLS LIST CARDS (Clean & Integrated)
         ========================================== -->
    <div wire:loading.class.delay.100ms="opacity-50 pointer-events-none" class="space-y-3.5 transition-opacity duration-200">
        @forelse($calls as $call)
            @php
                $isOverdue = $call->status === 'planned' && $call->call_date && $call->call_date->isPast() && !$call->call_date->isToday();
                $dealTitle = ($call->deal_id && isset($dealsMap[$call->deal_id])) ? $dealsMap[$call->deal_id] : ($call->deal ? $call->deal->title : null);
                $isInbound = $call->direction === 'inbound';
            @endphp
            <!-- Call Card with Elegant Integrated Accent Border -->
            <div class="bg-white dark:bg-gray-800/95 border border-gray-200/80 dark:border-gray-700/80 border-r-4 {{ $isInbound ? 'border-r-teal-500' : 'border-r-indigo-600' }} p-4 sm:p-5 rounded-2xl shadow-xs hover:shadow-md hover:border-gray-300 dark:hover:border-gray-600 transition-all">
                <!-- Top Header Row -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <!-- Direction Badge -->
                        @if($isInbound)
                            <span class="inline-flex items-center gap-1 text-[11px] bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300 border border-teal-200/60 dark:border-teal-800/60 px-2 py-0.5 rounded-lg font-bold">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                </svg>
                                تماس ورودی
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[11px] bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-900/60 px-2 py-0.5 rounded-lg font-bold">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                </svg>
                                تماس خروجی
                            </span>
                        @endif

                        <!-- Client Identity -->
                        @if($call->client)
                            <div class="inline-flex items-center gap-1.5">
                                <button wire:click="selectClient({{ $call->client->id }})"
                                        title="انتخاب و تمرکز روی این مشتری در کل میز کار"
                                        class="text-xs font-black text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    {{ $call->client->full_name ?: $call->client->username }}
                                </button>
                                @can('client-calls.view')
                                    <a href="{{ route('user.clients.calls.index', $call->client->id) }}"
                                       target="_blank"
                                       title="مشاهده تاریخچه کامل در ماژول تماس"
                                       class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                @endcan
                            </div>
                        @endif

                        <!-- VoIP Click-to-Call Button on Phone -->
                        @if($call->contact_phone)
                            <button type="button"
                                    wire:click="initiateVoipCall('{{ $call->contact_phone }}', {{ $call->client_id ?? 'null' }})"
                                    title="کلیک برای شماره‌گیری خودکار VoIP با شماره {{ $call->contact_phone }}"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-gray-100/80 hover:bg-indigo-50 text-gray-700 hover:text-indigo-700 dark:bg-gray-900/60 dark:hover:bg-indigo-950/40 dark:text-gray-300 dark:hover:text-indigo-300 border border-gray-200/70 dark:border-gray-700/70 transition-colors">
                                <svg class="w-3 h-3 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span dir="ltr">{{ $call->contact_phone }}</span>
                            </button>
                        @endif

                        <!-- Deal Badge -->
                        @if($dealTitle)
                            <span class="inline-flex items-center gap-1 text-[11px] bg-purple-50 text-purple-700 dark:bg-purple-950/30 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800/60 px-2 py-0.5 rounded-lg font-medium">
                                <svg class="w-3 h-3 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ $dealTitle }}
                            </span>
                        @endif

                        <!-- Duration Badge -->
                        @if($call->duration_seconds)
                            @php
                                $mins = floor($call->duration_seconds / 60);
                                $secs = $call->duration_seconds % 60;
                                $durationStr = ($mins > 0 ? "{$mins} دقیقه " : "") . ($secs > 0 ? "{$secs} ثانیه" : "");
                            @endphp
                            <span class="inline-flex items-center gap-1 text-[11px] bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded-lg font-bold border border-gray-200/60 dark:border-gray-600/50">
                                <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $durationStr }}
                            </span>
                        @endif

                        <!-- Overdue Alert -->
                        @if($isOverdue)
                            <span class="inline-flex items-center gap-1 text-[10px] bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200/60 dark:border-rose-900/60 px-2 py-0.5 rounded-lg font-extrabold animate-pulse">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                معوق سررسید گذشته
                            </span>
                        @endif
                    </div>

                    <!-- Date & Time Badge -->
                    <div class="shrink-0">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/40 px-2.5 py-1 rounded-lg border border-gray-100 dark:border-gray-800" dir="ltr">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $call->call_date ? \Morilog\Jalali\Jalalian::fromDateTime($call->call_date)->format('Y/m/d') : '-' }} {{ $call->call_time ? \Carbon\Carbon::parse($call->call_time)->format('H:i') : '' }}
                        </span>
                    </div>
                </div>

                <!-- Reason / Subject -->
                <div class="mb-2.5">
                    <span class="text-xs font-bold text-gray-900 dark:text-gray-100 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                        <span>موضوع: {{ $call->reason ?: 'بدون موضوع مشخص' }}</span>
                    </span>
                </div>

                <!-- Result & Notes Reading Bubble -->
                <div class="bg-gray-50/70 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800/80 rounded-xl p-3 text-xs leading-relaxed text-gray-600 dark:text-gray-300 mb-3">
                    <div class="space-y-1.5">
                        @if($call->result)
                            <div>
                                <span class="font-bold text-gray-700 dark:text-gray-200">نتیجه مکالمه:</span>
                                <span>{{ $call->result }}</span>
                            </div>
                        @endif
                        @if($call->notes && $call->notes !== $call->result)
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 border-t border-gray-200/50 dark:border-gray-700/50 pt-1 mt-1">
                                <span class="font-semibold text-gray-600 dark:text-gray-300">یادداشت داخلی:</span>
                                <span>{{ $call->notes }}</span>
                            </div>
                        @endif
                        @if(!$call->result && !$call->notes)
                            <span class="text-gray-400 italic">بدون ثبت نتیجه یا یادداشت برای این تماس</span>
                        @endif
                    </div>
                </div>

                <!-- Next Action Alert Box (if present) -->
                @if($call->next_action)
                    <div class="mb-3 flex items-center justify-between bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-900/50 px-3 py-2 rounded-xl text-xs text-amber-900 dark:text-amber-200">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                            </svg>
                            <div>
                                <span class="font-bold">اقدام بعدی:</span>
                                <span>{{ $call->next_action }}</span>
                            </div>
                        </div>
                        @if($call->next_action_date)
                            <span class="text-[11px] font-bold text-amber-700 dark:text-amber-300 dir-ltr">
                                {{ \Morilog\Jalali\Jalalian::fromDateTime($call->next_action_date)->format('Y/m/d') }}
                            </span>
                        @endif
                    </div>
                @endif

                <!-- Card Footer: Status Pill, Rep Info, Action Buttons -->
                <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700/60 flex-wrap gap-2">
                    <div class="flex items-center gap-3 flex-wrap">
                        <!-- Status Badges -->
                        @if($call->status === 'done' || $call->status === 'answered')
                            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300 border border-emerald-200/70 dark:border-emerald-800/70 text-[11px] font-bold px-2.5 py-0.5 rounded-lg">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                انجام شده / پاسخ داده شده
                            </span>
                        @elseif($call->status === 'planned')
                            <span class="inline-flex items-center gap-1.5 bg-sky-50 text-sky-700 dark:bg-sky-950/30 dark:text-sky-300 border border-sky-200/70 dark:border-sky-800/70 text-[11px] font-bold px-2.5 py-0.5 rounded-lg">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                برنامه‌ریزی شده
                            </span>
                        @elseif($call->status === 'no_answer')
                            <span class="inline-flex items-center gap-1.5 bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-300 border border-rose-200/70 dark:border-rose-800/70 text-[11px] font-bold px-2.5 py-0.5 rounded-lg">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                بدون پاسخ
                            </span>
                        @elseif($call->status === 'busy')
                            <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300 border border-amber-200/70 dark:border-amber-800/70 text-[11px] font-bold px-2.5 py-0.5 rounded-lg">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                اشغال
                            </span>
                        @elseif($call->status === 'cancelled' || $call->status === 'canceled')
                            <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-700 dark:bg-gray-700/60 dark:text-gray-300 border border-gray-200 dark:border-gray-600 text-[11px] font-bold px-2.5 py-0.5 rounded-lg">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                لغو شده
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-300 border border-rose-200/70 dark:border-rose-800/70 text-[11px] font-bold px-2.5 py-0.5 rounded-lg">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                ناموفق
                            </span>
                        @endif

                        <!-- Sales Rep / Creator -->
                        <span class="text-[11px] text-gray-400 dark:text-gray-500 inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>کارشناس:</span>
                            <strong class="font-bold text-gray-600 dark:text-gray-300">{{ $call->user ? $call->user->name : '-' }}</strong>
                        </span>
                    </div>

                    <!-- Actions: Edit / Delete -->
                    <div class="flex items-center gap-3">
                        <button wire:click="editCall({{ $call->id }})"
                                class="text-xs font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 inline-flex items-center gap-1 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span>ویرایش</span>
                        </button>
                        <button onclick="confirm('آیا از حذف این تماس مطمئن هستید؟') || event.stopImmediatePropagation()"
                                wire:click="deleteCall({{ $call->id }})"
                                class="text-xs font-bold text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 inline-flex items-center gap-1 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>حذف</span>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-12 flex flex-col items-center justify-center text-center text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800/60 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700/80">
                <div class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-700/50 flex items-center justify-center text-gray-400 dark:text-gray-500 mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-1">تماسی با این مشخصات یافت نشد</h4>
                <p class="text-xs text-gray-400 max-w-sm mb-4">می‌توانید فیلتر بازه زمانی را تغییر دهید یا تماس تلفنی جدیدی را ثبت فرمایید.</p>
                <button wire:click="openCreateModal"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>ثبت تماس جدید</span>
                </button>
            </div>
        @endforelse
    </div>

    <!-- ==========================================
         4. PAGINATION
         ========================================== -->
    @if($calls->count())
        <div class="pt-4" dir="rtl">
            {{ $calls->links() }}
        </div>
    @endif

    <!-- ==========================================
         5. CREATE / EDIT CALL MODAL
         ========================================== -->
    <template x-teleport="body">
        <div x-data="{ show: @entangle('showCreateModal') }"
             x-init="$watch('show', value => {
                 document.body.style.overflow = value ? 'hidden' : '';
                 document.documentElement.style.overflow = value ? 'hidden' : '';
             })"
             x-effect="if (!show) { document.body.style.overflow = ''; document.documentElement.style.overflow = ''; }">
            <div x-show="show"
                 class="fixed inset-0 z-[100] overflow-y-auto overscroll-contain"
                 aria-labelledby="modal-title"
                 role="dialog"
                 aria-modal="true"
                 style="display: none;">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Overlay Backdrop -->
                    <div class="fixed inset-0 bg-gray-950/60 dark:bg-gray-950/80 backdrop-blur-xs transition-opacity"
                         x-on:click="$wire.cancelEditing()"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <!-- Modal Dialog Card -->
                    <div class="inline-block align-bottom relative bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <!-- Modal Header -->
                            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4 mb-5">
                                <h3 class="text-base font-black text-gray-900 dark:text-white inline-flex items-center gap-2" id="modal-title">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                    </div>
                                    <span>{{ $editingCallId ? 'ویرایش جزئیات تماس' : 'ثبت تماس جدید' }}</span>
                                </h3>
                                <button x-on:click="$wire.cancelEditing()"
                                        type="button"
                                        class="text-gray-400 hover:text-rose-500 bg-gray-50 hover:bg-rose-50 dark:bg-gray-900/50 dark:hover:bg-rose-950/40 p-2 rounded-xl transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Form -->
                            <form wire:submit.prevent="saveCall" class="space-y-4">
                                <!-- انتخاب / جستجوی مشتری (Customer Selector) -->
                                <div class="bg-gray-50/70 dark:bg-gray-900/40 p-3.5 rounded-xl border border-gray-200/80 dark:border-gray-700/80">
                                    <label class="{{ $labelClass }}">مشتری طرف تماس <span class="text-rose-500">*</span></label>
                                    @if($selectedClientId && $selectedClientName)
                                        <div class="flex items-center justify-between bg-white dark:bg-gray-800 px-3 py-2 rounded-xl border border-indigo-200/80 dark:border-indigo-900/80 shadow-xs">
                                            <div class="flex items-center gap-2 truncate">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                                <span class="text-xs font-bold text-gray-800 dark:text-gray-100 truncate">{{ $selectedClientName }}</span>
                                                @if($contact_phone)
                                                    <span class="text-[11px] text-gray-500 dir-ltr shrink-0">({{ $contact_phone }})</span>
                                                @endif
                                            </div>
                                            <button type="button" wire:click="clearSelectedClientInModal" class="text-xs text-rose-500 hover:text-rose-700 dark:hover:text-rose-400 font-bold transition-colors shrink-0">
                                                تغییر مشتری
                                            </button>
                                        </div>
                                    @else
                                        <div class="relative">
                                            <input type="text"
                                                   wire:model.live.debounce.250ms="clientSearch"
                                                   class="{{ $inputClass }}"
                                                   placeholder="برای جستجو نام، شماره موبایل یا کد ملی مشتری را بنویسید...">
                                            @if(!empty($clientSearchResults))
                                                <div class="absolute z-50 right-0 left-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-48 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700/60">
                                                    @foreach($clientSearchResults as $c)
                                                        <button type="button"
                                                                wire:click="selectClientForModal({{ $c['id'] }})"
                                                                class="w-full text-right px-3 py-2 text-xs hover:bg-indigo-50 dark:hover:bg-indigo-950/40 flex items-center justify-between transition-colors">
                                                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ $c['full_name'] }}</span>
                                                            <span class="text-gray-400 dir-ltr">{{ $c['phone'] }}</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                    @error('selectedClientId') <span class="text-rose-500 text-2xs mt-1 block font-semibold">{{ $message }}</span> @enderror
                                </div>

                                <!-- تاریخ و زمان (شمسی) -->
                                <div class="grid grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="{{ $labelClass }}">تاریخ تماس (شمسی) <span class="text-rose-500">*</span></label>
                                        <input type="text"
                                               wire:model="call_date_jalali"
                                               x-on:change="$wire.set('call_date_jalali', $el.value)"
                                               data-jdp-only-date
                                               placeholder="1403/10/12"
                                               class="{{ $inputClass }} dir-ltr text-center">
                                        @error('call_date_jalali') <span class="text-rose-500 text-2xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">ساعت تماس <span class="text-rose-500">*</span></label>
                                        <input type="text"
                                               wire:model="call_time"
                                               x-on:change="$wire.set('call_time', $el.value)"
                                               data-jdp-only-time
                                               placeholder="14:30"
                                               class="{{ $inputClass }} dir-ltr text-center">
                                        @error('call_time') <span class="text-rose-500 text-2xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- جهت و وضعیت تماس -->
                                <div class="grid grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="{{ $labelClass }}">جهت تماس <span class="text-rose-500">*</span></label>
                                        <select wire:model="direction" class="{{ $inputClass }}">
                                            <option value="outbound">خروجی</option>
                                            <option value="inbound">ورودی</option>
                                        </select>
                                        @error('direction') <span class="text-rose-500 text-2xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">وضعیت تماس <span class="text-rose-500">*</span></label>
                                        <select wire:model="status" class="{{ $inputClass }}">
                                            <option value="done">انجام شده / پاسخ داده شده</option>
                                            <option value="planned">برنامه‌ریزی شده</option>
                                            <option value="no_answer">بدون پاسخ</option>
                                            <option value="busy">اشغال</option>
                                            <option value="failed">ناموفق</option>
                                            <option value="cancelled">لغو شده</option>
                                        </select>
                                        @error('status') <span class="text-rose-500 text-2xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- شماره و کمپین -->
                                <div class="grid grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="{{ $labelClass }}">شماره تماس</label>
                                        <input type="text" wire:model="contact_phone" class="{{ $inputClass }} dir-ltr text-left" placeholder="0912...">
                                        @error('contact_phone') <span class="text-rose-500 text-2xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">کمپین مرتبط</label>
                                        <select wire:model="campaign_id" class="{{ $inputClass }}">
                                            <option value="">-- بدون کمپین --</option>
                                            @if(isset($campaigns))
                                                @foreach($campaigns as $camp)
                                                    <option value="{{ $camp->id }}">{{ $camp->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('campaign_id') <span class="text-rose-500 text-2xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- اتصال به پرونده فروش -->
                                @if(!empty($availableDeals) && $availableDeals->count() > 0)
                                    <div>
                                        <label class="{{ $labelClass }}">اتصال به پرونده فروش</label>
                                        <select wire:model="deal_id" class="{{ $inputClass }}">
                                            <option value="">-- بدون اتصال به پرونده خاص --</option>
                                            @foreach($availableDeals as $dl)
                                                <option value="{{ $dl->id }}">{{ $dl->title }} ({{ number_format($dl->expected_revenue) }} ریال)</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <!-- مدت و علت -->
                                <div class="grid grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="{{ $labelClass }}">مدت مکالمه (ثانیه)</label>
                                        <input type="number" wire:model="duration_seconds" class="{{ $inputClass }} dir-ltr text-center" placeholder="120">
                                        @error('duration_seconds') <span class="text-rose-500 text-2xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">علت / موضوع تماس</label>
                                        <input type="text" wire:model="reason" class="{{ $inputClass }}" placeholder="مثال: پیگیری قرارداد، ارائه پیش‌فاکتور">
                                        @error('reason') <span class="text-rose-500 text-2xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- اقدام بعدی و تاریخ پیگیری -->
                                <div class="grid grid-cols-2 gap-3.5 bg-amber-50/50 dark:bg-amber-950/20 p-3.5 rounded-xl border border-amber-200/60 dark:border-amber-900/40">
                                    <div>
                                        <label class="{{ $labelClass }}">اقدام بعدی (ایجاد خودکار تسک)</label>
                                        <input type="text" wire:model="next_action" class="{{ $inputClass }}" placeholder="مثال: تماس مجدد، ارسال قرارداد">
                                        @error('next_action') <span class="text-rose-500 text-2xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">تاریخ اقدام بعدی (شمسی)</label>
                                        <input type="text"
                                               wire:model="next_action_date_jalali"
                                               x-on:change="$wire.set('next_action_date_jalali', $el.value)"
                                               data-jdp-only-date
                                               placeholder="1403/10/15"
                                               class="{{ $inputClass }} dir-ltr text-center">
                                        @error('next_action_date_jalali') <span class="text-rose-500 text-2xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- نتیجه و یادداشت -->
                                <div>
                                    <label class="{{ $labelClass }}">نتیجه مکالمه</label>
                                    <textarea wire:model="result" rows="2" class="{{ $inputClass }} resize-none" placeholder="خلاصه نتیجه تماس و صحبت‌های انجام شده..."></textarea>
                                    @error('result') <span class="text-rose-500 text-2xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">یادداشت‌های داخلی</label>
                                    <textarea wire:model="notes" rows="2" class="{{ $inputClass }} resize-none" placeholder="نکات تکمیلی یا یادداشت داخلی کارشناس فروش..."></textarea>
                                    @error('notes') <span class="text-rose-500 text-2xs mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Modal Footer Buttons -->
                                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <button type="button"
                                            x-on:click="$wire.cancelEditing()"
                                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-colors">
                                        انصراف
                                    </button>
                                    <button type="submit"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-bold shadow-xs transition-all active:scale-95 disabled:opacity-50">
                                        <span wire:loading.remove wire:target="saveCall">{{ $editingCallId ? 'ذخیره تغییرات' : 'ثبت تماس' }}</span>
                                        <span wire:loading wire:target="saveCall" class="inline-flex items-center gap-2">
                                            <svg class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                            </svg>
                                            <span>درحال ثبت...</span>
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
