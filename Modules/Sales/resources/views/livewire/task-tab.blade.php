@php
    $inputClass = "w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 px-3.5 py-2 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors shadow-xs font-sans";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5 font-sans";
@endphp

<div class="space-y-5" dir="rtl">
    {{-- اسکریپت و استایل JalaliDatePicker --}}
    @includeIf('partials.jalali-date-picker')

    <!-- ==========================================
         1. TOP KPI SUMMARY CARDS
         ========================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- کل پیگیری‌های امروز -->
        <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block font-sans">پیگیری‌های امروز</span>
                <span class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white block font-sans">
                    {{ $kpis['today_total'] ?? 0 }}
                </span>
                <span class="text-[10px] text-gray-400 dark:text-gray-500 block font-sans">برنامه‌ریزی شده</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100/60 dark:border-indigo-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>

        <!-- انجام شده / موفق -->
        <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block font-sans">انجام شده امروز</span>
                <span class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 block font-sans">
                    {{ $kpis['today_done'] ?? 0 }}
                </span>
                <span class="text-[10px] text-emerald-600/80 dark:text-emerald-400/80 block font-sans">پیگیری‌های محقق‌شده</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100/60 dark:border-emerald-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- در انتظار / پیش‌رو -->
        <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block font-sans">در انتظار اقدام</span>
                <span class="text-xl sm:text-2xl font-black text-sky-600 dark:text-sky-400 block font-sans">
                    {{ $kpis['today_pending'] ?? 0 }}
                </span>
                <span class="text-[10px] text-sky-600/80 dark:text-sky-400/80 block font-sans">نوبت‌های پیش‌رو</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-100/60 dark:border-sky-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- معوق / دارای تاخیر -->
        <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block font-sans">معوق و گذشته</span>
                <span class="text-xl sm:text-2xl font-black text-rose-600 dark:text-rose-400 block font-sans">
                    {{ $kpis['overdue'] ?? 0 }}
                </span>
                <span class="text-[10px] text-rose-600/80 dark:text-rose-400/80 block font-sans">نیازمند اقدام فوری</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-100/60 dark:border-rose-900/50 flex items-center justify-center shrink-0">
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
                           placeholder="جستجو در موضوع پیگیری، توضیحات، نام یا شماره مشتری...">
                </div>
            @endif

            <!-- Advanced Status Filter Dropdown (Custom & Interactive) -->
            <div x-data="{ open: false }" class="relative {{ $selectedClientId ? 'w-full sm:w-72' : 'w-full sm:w-64' }} shrink-0" @click.outside="open = false">
                <!-- Dropdown Trigger Button -->
                <button type="button"
                        @click="open = !open"
                        class="w-full flex items-center justify-between gap-2 px-3.5 py-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-700 dark:text-gray-200 shadow-xs hover:border-gray-300 dark:hover:border-gray-600 transition-colors font-sans">
                    <div class="flex items-center gap-2 truncate">
                        @if($filterStatus === 'todo')
                            <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                            <span class="font-bold text-amber-700 dark:text-amber-300">در صف انجام</span>
                        @elseif($filterStatus === 'in_progress')
                            <span class="w-2 h-2 rounded-full bg-sky-500 shrink-0"></span>
                            <span class="font-bold text-sky-700 dark:text-sky-300">در حال انجام</span>
                        @elseif($filterStatus === 'done')
                            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                            <span class="font-bold text-emerald-700 dark:text-emerald-300">انجام شده</span>
                        @elseif($filterStatus === 'overdue')
                            <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0 animate-pulse"></span>
                            <span class="font-bold text-rose-700 dark:text-rose-300">معوق و با تاخیر</span>
                        @elseif($filterStatus === 'active')
                            <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
                            <span class="font-bold text-indigo-700 dark:text-indigo-300">پیگیری‌های فعال</span>
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

                <!-- Popover Panel -->
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute z-50 mt-1.5 w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl overflow-hidden py-1 text-xs"
                     style="display: none;">
                    
                    <!-- Option: All -->
                    <button type="button"
                            wire:click="$set('filterStatus', ''); open = false"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors {{ $filterStatus === '' ? 'bg-indigo-50/50 dark:bg-indigo-950/20 font-bold text-indigo-600 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300' }}">
                        <span class="flex items-center gap-2 font-sans">
                            <span class="w-2 h-2 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                            همه وضعیت‌ها
                        </span>
                        @if($filterStatus === '')
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </button>

                    <!-- Option: Active -->
                    <button type="button"
                            wire:click="$set('filterStatus', 'active'); open = false"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors {{ $filterStatus === 'active' ? 'bg-indigo-50/50 dark:bg-indigo-950/20 font-bold text-indigo-600 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300' }}">
                        <span class="flex items-center gap-2 font-sans">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            پیگیری‌های فعال (در صف / در حال انجام)
                        </span>
                        @if($filterStatus === 'active')
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </button>

                    <!-- Option: Todo -->
                    <button type="button"
                            wire:click="$set('filterStatus', 'todo'); open = false"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors {{ $filterStatus === 'todo' ? 'bg-amber-50/50 dark:bg-amber-950/20 font-bold text-amber-600 dark:text-amber-400' : 'text-gray-700 dark:text-gray-300' }}">
                        <span class="flex items-center gap-2 font-sans">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            در صف انجام
                        </span>
                        @if($filterStatus === 'todo')
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </button>

                    <!-- Option: In Progress -->
                    <button type="button"
                            wire:click="$set('filterStatus', 'in_progress'); open = false"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors {{ $filterStatus === 'in_progress' ? 'bg-sky-50/50 dark:bg-sky-950/20 font-bold text-sky-600 dark:text-sky-400' : 'text-gray-700 dark:text-gray-300' }}">
                        <span class="flex items-center gap-2 font-sans">
                            <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                            در حال انجام
                        </span>
                        @if($filterStatus === 'in_progress')
                            <svg class="w-4 h-4 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </button>

                    <!-- Option: Done -->
                    <button type="button"
                            wire:click="$set('filterStatus', 'done'); open = false"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors {{ $filterStatus === 'done' ? 'bg-emerald-50/50 dark:bg-emerald-950/20 font-bold text-emerald-600 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300' }}">
                        <span class="flex items-center gap-2 font-sans">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            انجام شده
                        </span>
                        @if($filterStatus === 'done')
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </button>

                    <!-- Option: Overdue -->
                    <button type="button"
                            wire:click="$set('filterStatus', 'overdue'); open = false"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors {{ $filterStatus === 'overdue' ? 'bg-rose-50/50 dark:bg-rose-950/20 font-bold text-rose-600 dark:text-rose-400' : 'text-gray-700 dark:text-gray-300' }}">
                        <span class="flex items-center gap-2 font-sans">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                            معوق و با تاخیر
                        </span>
                        @if($filterStatus === 'overdue')
                            <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </button>

                    <!-- Option: Cancelled -->
                    <button type="button"
                            wire:click="$set('filterStatus', 'cancelled'); open = false"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors {{ $filterStatus === 'cancelled' ? 'bg-gray-100 dark:bg-gray-800 font-bold text-gray-700 dark:text-gray-300' : 'text-gray-700 dark:text-gray-300' }}">
                        <span class="flex items-center gap-2 font-sans">
                            <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                            لغو شده
                        </span>
                        @if($filterStatus === 'cancelled')
                            <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </button>
                </div>
            </div>

            <!-- View Toggle & New Follow-up Button -->
            <div class="flex items-center gap-2 shrink-0">
                <!-- View Mode Toggle (Segmented Control) -->
                <div class="inline-flex items-center bg-gray-100 dark:bg-gray-900 p-1 rounded-xl border border-gray-200/80 dark:border-gray-700/80">
                    <button type="button"
                            wire:click="$set('viewMode', 'list')"
                            class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 font-sans {{ $viewMode === 'list' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-gray-500 hover:text-gray-800 dark:hover:text-gray-200' }}">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        <span>لیست</span>
                    </button>
                    <button type="button"
                            wire:click="$set('viewMode', 'kanban')"
                            class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 font-sans {{ $viewMode === 'kanban' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-gray-500 hover:text-gray-800 dark:hover:text-gray-200' }}">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                        <span>کانبان</span>
                    </button>
                </div>

                <!-- Primary Action Button: New Follow-up -->
                <button wire:click="openCreateModal"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs hover:shadow transition-all active:scale-95 font-sans">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>ثبت پیگیری جدید</span>
                </button>
            </div>
        </div>

        <!-- Sub-bar: Quick Date & Priority Pills -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-gray-100 dark:border-gray-700/60 text-xs">
            <!-- Date Range Pills -->
            <div class="flex items-center gap-1 overflow-x-auto scrollbar-hide py-0.5">
                <span class="text-[11px] text-gray-400 ml-1.5 font-sans">موعد:</span>
                @foreach(['all' => 'همه', 'today' => 'امروز', 'week' => 'این هفته', 'month' => 'این ماه'] as $key => $label)
                    <button type="button"
                            wire:click="$set('filterDate', '{{ $key }}')"
                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-colors font-sans {{ $filterDate === $key ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/60' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800/60' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <!-- Priority Filter Chips -->
            <div class="flex items-center gap-1 overflow-x-auto scrollbar-hide py-0.5">
                <span class="text-[11px] text-gray-400 ml-1.5 font-sans">اولویت:</span>
                @foreach(['' => 'همه', 'CRITICAL' => 'بحرانی', 'HIGH' => 'زیاد', 'MEDIUM' => 'معمولی', 'LOW' => 'کم'] as $pKey => $pLabel)
                    <button type="button"
                            wire:click="$set('filterPriority', '{{ $pKey }}')"
                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-colors font-sans {{ $filterPriority === $pKey ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800/60' }}">
                        {{ $pLabel }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ==========================================
         3. ACTIVE CLIENT FOCUS BANNER
         ========================================== -->
    @if($selectedClient)
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-4 py-3 bg-indigo-50/80 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 rounded-2xl text-xs">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-400 animate-pulse shrink-0"></span>
                <div class="flex items-center flex-wrap gap-2">
                    <span class="font-bold text-indigo-950 dark:text-indigo-200 font-sans">
                        پیگیری‌های مشتری فعال: {{ $selectedClient->full_name }}
                    </span>
                    @if($selectedClient->phone)
                        <span class="text-[11px] text-indigo-600/90 dark:text-indigo-400 font-sans">({{ $selectedClient->phone }})</span>
                    @endif
                    @if($selectedClient->case_number)
                        <span class="text-[10px] bg-indigo-100/70 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 px-2 py-0.5 rounded-md font-sans">
                            پرونده: {{ $selectedClient->case_number }}
                        </span>
                    @endif
                </div>
            </div>
            
            <div class="flex items-center gap-2 shrink-0">
                <button wire:click="$set('filterClientMode', '{{ $filterClientMode === 'active' ? 'all' : 'active' }}')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all font-sans {{ $filterClientMode === 'active' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:bg-gray-50' }}">
                    {{ $filterClientMode === 'active' ? 'نمایش همه پیگیری‌ها' : 'فقط پیگیری‌های این مشتری' }}
                </button>
            </div>
        </div>
    @endif

    <!-- ==========================================
         4. MAIN CONTENT AREA (List or Kanban)
         ========================================== -->
    @if($isKanban)
        <!-- ================= KANBAN VIEW ================= -->
        <div wire:loading.class.delay.100ms="opacity-50 pointer-events-none" class="grid grid-cols-1 md:grid-cols-3 gap-4 transition-opacity duration-200">
            <!-- TO DO column -->
            <div class="bg-gray-50/70 dark:bg-gray-900/40 rounded-2xl p-4 border border-gray-200/70 dark:border-gray-800 flex flex-col min-h-[500px]">
                <div class="flex items-center justify-between border-b border-gray-200/80 dark:border-gray-700/80 pb-3 mb-3">
                    <h3 class="text-xs font-black text-gray-800 dark:text-gray-200 inline-flex items-center gap-2 font-sans">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <span>در صف انجام</span>
                    </h3>
                    <span class="text-[11px] font-bold bg-amber-100 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 px-2 py-0.5 rounded-full font-sans">
                        {{ $kanbanTasks['todo']->count() }}
                    </span>
                </div>
                <div class="space-y-3 flex-1 overflow-y-auto">
                    @forelse($kanbanTasks['todo'] as $task)
                        @include('sales::livewire.partials.task-card', ['task' => $task])
                    @empty
                        <div class="py-12 flex flex-col items-center justify-center text-center text-gray-400 dark:text-gray-500">
                            <svg class="w-8 h-8 stroke-1 mb-2 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span class="text-xs font-sans">موردی در صف انجام نیست</span>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- IN PROGRESS column -->
            <div class="bg-gray-50/70 dark:bg-gray-900/40 rounded-2xl p-4 border border-gray-200/70 dark:border-gray-800 flex flex-col min-h-[500px]">
                <div class="flex items-center justify-between border-b border-gray-200/80 dark:border-gray-700/80 pb-3 mb-3">
                    <h3 class="text-xs font-black text-gray-800 dark:text-gray-200 inline-flex items-center gap-2 font-sans">
                        <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                        <span>در حال انجام</span>
                    </h3>
                    <span class="text-[11px] font-bold bg-sky-100 dark:bg-sky-950/40 text-sky-700 dark:text-sky-400 px-2 py-0.5 rounded-full font-sans">
                        {{ $kanbanTasks['in_progress']->count() }}
                    </span>
                </div>
                <div class="space-y-3 flex-1 overflow-y-auto">
                    @forelse($kanbanTasks['in_progress'] as $task)
                        @include('sales::livewire.partials.task-card', ['task' => $task])
                    @empty
                        <div class="py-12 flex flex-col items-center justify-center text-center text-gray-400 dark:text-gray-500">
                            <svg class="w-8 h-8 stroke-1 mb-2 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-xs font-sans">موردی در حال پیگیری نیست</span>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- DONE column -->
            <div class="bg-gray-50/70 dark:bg-gray-900/40 rounded-2xl p-4 border border-gray-200/70 dark:border-gray-800 flex flex-col min-h-[500px]">
                <div class="flex items-center justify-between border-b border-gray-200/80 dark:border-gray-700/80 pb-3 mb-3">
                    <h3 class="text-xs font-black text-gray-800 dark:text-gray-200 inline-flex items-center gap-2 font-sans">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span>انجام شده</span>
                    </h3>
                    <span class="text-[11px] font-bold bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 px-2 py-0.5 rounded-full font-sans">
                        {{ $kanbanTasks['done']->count() }}
                    </span>
                </div>
                <div class="space-y-3 flex-1 overflow-y-auto">
                    @forelse($kanbanTasks['done'] as $task)
                        @include('sales::livewire.partials.task-card', ['task' => $task])
                    @empty
                        <div class="py-12 flex flex-col items-center justify-center text-center text-gray-400 dark:text-gray-500">
                            <svg class="w-8 h-8 stroke-1 mb-2 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-xs font-sans">پیگیری انجام‌شده‌ای وجود ندارد</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @else
        <!-- ================= LIST VIEW ================= -->
        <div wire:loading.class.delay.100ms="opacity-50 pointer-events-none" class="space-y-3 transition-opacity duration-200">
            @forelse($tasks as $task)
                @php
                    $isDone = ($task->status === 'DONE');
                    $isOverdue = ($task->due_at && $task->due_at->isPast() && !$isDone && $task->status !== 'CANCELED');
                    $meta = $task->meta ?? [];
                    $dealId = $meta['deal_id'] ?? ($task->related_type === 'DEAL' ? $task->related_id : null);
                    $deal = ($dealId && class_exists(\Modules\Sales\App\Models\SalesDeal::class)) ? \Modules\Sales\App\Models\SalesDeal::find($dealId) : null;
                    
                    $client = null;
                    if ($task->related_type === 'CLIENT' && $task->relatedClient) {
                        $client = $task->relatedClient;
                    } elseif ($deal && $deal->client) {
                        $client = $deal->client;
                    } elseif ($task->relatedClient) {
                        $client = $task->relatedClient;
                    }
                @endphp
                <div class="bg-white dark:bg-gray-800/90 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 p-4 shadow-xs hover:shadow-md transition-all flex flex-col md:flex-row md:items-center justify-between gap-4 border-r-4 {{ $task->priority === 'CRITICAL' ? 'border-r-rose-600' : ($task->priority === 'HIGH' ? 'border-r-amber-500' : ($task->priority === 'MEDIUM' ? 'border-r-indigo-500' : 'border-r-slate-400')) }} {{ $isDone ? 'opacity-80' : '' }}">
                    
                    <!-- Left block: Priority, Title, Meta, Client, Deal, Due Date -->
                    <div class="flex-1 space-y-2">
                        <div class="flex items-center flex-wrap gap-2">
                            <!-- Priority badge -->
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg font-sans {{ $task->priority === 'CRITICAL' ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' : ($task->priority === 'HIGH' ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400' : ($task->priority === 'MEDIUM' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300')) }}">
                                {{ $task->priority === 'CRITICAL' ? 'بحرانی' : ($task->priority === 'HIGH' ? 'زیاد' : ($task->priority === 'MEDIUM' ? 'معمولی' : 'کم')) }}
                            </span>

                            <!-- Type badge -->
                            <span class="text-[10px] bg-purple-50 text-purple-700 dark:bg-purple-950/30 dark:text-purple-300 px-2 py-0.5 rounded-lg font-bold font-sans">
                                {{ $task->task_type === 'FOLLOW_UP' ? 'پیگیری' : 'وظیفه عمومی' }}
                            </span>

                            <!-- Overdue badge -->
                            @if($isOverdue)
                                <span class="text-[10px] bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 px-2 py-0.5 rounded-lg font-extrabold animate-pulse font-sans">
                                    معوق
                                </span>
                            @endif

                            <!-- Title -->
                            <h4 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white font-sans {{ $isDone ? 'line-through text-gray-400 dark:text-gray-500' : '' }}">
                                {{ $task->title }}
                            </h4>
                        </div>

                        <!-- Description -->
                        @if($task->description)
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed font-sans">
                                {{ $task->description }}
                            </p>
                        @endif

                        <!-- Badges: Client, Deal, Date -->
                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            @if($client)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-300 text-[11px] font-sans">
                                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span class="font-bold">{{ $client->full_name }}</span>
                                    @if($client->phone)
                                        <span class="text-[10px] text-indigo-500/80 font-sans">({{ $client->phone }})</span>
                                    @endif
                                </span>
                            @endif

                            @if($deal)
                                @php
                                    $cleanDealTitle = preg_replace('/^(پرونده\s*:\s*)+/u', '', trim($deal->title));
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300 text-[11px] font-sans">
                                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <span>پرونده: <span class="font-bold">{{ $cleanDealTitle }}</span></span>
                                </span>
                            @endif

                            @if($task->due_at)
                                <span class="inline-flex items-center gap-1 text-[11px] text-gray-500 dark:text-gray-400 font-sans" dir="ltr">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    موعد: {{ \Morilog\Jalali\Jalalian::fromDateTime($task->due_at)->format('Y/m/d H:i') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Right block: Assignee + Action Operations -->
                    <div class="flex items-center gap-3 justify-between md:justify-end border-t md:border-t-0 border-gray-100 dark:border-gray-700/60 pt-3 md:pt-0 shrink-0">
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 font-sans">
                            <span>مسئول:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 mr-1">{{ $task->assignee ? $task->assignee->name : 'ناشناس' }}</span>
                        </div>

                        <div class="flex items-center gap-1.5">
                            @if($task->status !== 'DONE' && $task->status !== 'CANCELED')
                                <button wire:click="completeTask({{ $task->id }})"
                                        class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold shadow-xs transition-all inline-flex items-center gap-1.5 active:scale-95 font-sans">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>انجام شد</span>
                                </button>
                                
                                <button wire:click="cancelTask({{ $task->id }})"
                                        class="px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-xs font-bold transition-colors font-sans">
                                    لغو
                                </button>
                            @endif

                            @if($task->assignee_id !== auth()->id() && $task->status !== 'DONE' && $task->status !== 'CANCELED')
                                <button wire:click="assignToMe({{ $task->id }})"
                                        class="px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-xl text-xs font-bold transition-colors font-sans">
                                    من انجام می‌دهم
                                </button>
                            @endif

                            <button wire:click="editTask({{ $task->id }})"
                                    class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700/80 text-gray-400 hover:text-indigo-600 rounded-xl transition-colors"
                                    title="ویرایش">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-16 flex flex-col items-center justify-center text-center text-gray-500 dark:text-gray-400 bg-gray-50/50 dark:bg-gray-800/30 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/30 text-indigo-500 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300 font-sans">پیگیری یا وظیفه‌ای یافت نشد.</span>
                    <p class="text-xs text-gray-400 mt-1 font-sans">می‌توانید با کلیک روی «ثبت پیگیری جدید»، اولین پیگیری را ثبت کنید.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($tasks->hasPages())
            <div class="pt-4 font-sans">
                {{ $tasks->links() }}
            </div>
        @endif
    @endif

    <!-- ==========================================
         5. CREATE / EDIT FOLLOW-UP MODAL
         ========================================== -->
    <template x-teleport="body">
        <div x-data="{ show: @entangle('showCreateModal') }"
             x-init="$watch('show', value => {
                 if (value) {
                     document.body.style.overflow = 'hidden';
                 } else {
                     document.body.style.overflow = '';
                 }
             })"
             class="overscroll-contain">
            
            <div x-show="show"
                 class="fixed inset-0 z-[100] overflow-y-auto"
                 aria-labelledby="modal-title"
                 role="dialog"
                 aria-modal="true"
                 style="display: none;">
                
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Backdrop Overlay -->
                    <div class="fixed inset-0 bg-gray-950/70 dark:bg-gray-950/80 backdrop-blur-sm transition-opacity"
                         x-on:click="show = false"></div>
                    
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    
                    <!-- Modal Card -->
                    <div class="inline-block align-bottom relative bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-200/80 dark:border-gray-700">
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/80 pb-4 mb-5">
                                <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white inline-flex items-center gap-2 font-sans" id="modal-title">
                                    <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </span>
                                    <span>{{ $editingTaskId ? 'ویرایش پیگیری' : 'ثبت پیگیری جدید' }}</span>
                                </h3>
                                <button x-on:click="show = false"
                                        type="button"
                                        class="text-gray-400 hover:text-rose-500 bg-gray-50 hover:bg-rose-50 dark:bg-gray-900/50 dark:hover:bg-rose-950/30 p-2 rounded-xl transition-colors">
                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Form -->
                            <form wire:submit.prevent="saveTask" class="space-y-4" dir="rtl">
                                <!-- Client Selector Section -->
                                <div>
                                    <label class="{{ $labelClass }}">مشتری مرتبط</label>
                                    @if($modalClientId && $modalClientName)
                                        <div class="flex items-center justify-between p-2.5 bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 rounded-xl">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                                <span class="text-xs font-bold text-indigo-900 dark:text-indigo-200 font-sans">{{ $modalClientName }}</span>
                                            </div>
                                            <button type="button"
                                                    wire:click="clearModalClient"
                                                    class="text-xs text-rose-500 hover:text-rose-700 font-sans">
                                                تغییر مشتری
                                            </button>
                                        </div>
                                    @else
                                        <div class="relative">
                                            <input type="text"
                                                   wire:model.live.debounce.300ms="clientSearch"
                                                   class="{{ $inputClass }}"
                                                   placeholder="جستجوی نام یا شماره مشتری...">
                                            @if(!empty($clientSearchResults))
                                                <div class="absolute z-20 top-full right-0 left-0 mt-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-48 overflow-y-auto py-1">
                                                    @foreach($clientSearchResults as $res)
                                                        <button type="button"
                                                                wire:click="selectClientForModal({{ $res['id'] }})"
                                                                class="w-full text-right px-3.5 py-2 hover:bg-gray-50 dark:hover:bg-gray-800 flex items-center justify-between text-xs transition-colors font-sans">
                                                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ $res['full_name'] }}</span>
                                                            <span class="text-[11px] text-gray-400 font-sans">{{ $res['phone'] }}</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <!-- Deal Connection (if client selected) -->
                                @if($modalClientId && $clientDeals->isNotEmpty())
                                    <div>
                                        <label class="{{ $labelClass }}">اتصال به پرونده فروش (Deal)</label>
                                        <select wire:model="deal_id" class="{{ $inputClass }}">
                                            <option value="">-- بدون اتصال به پرونده خاص --</option>
                                            @foreach($clientDeals as $deal)
                                                <option value="{{ $deal->id }}">
                                                    {{ $deal->title }} ({{ $deal->status === 'open' ? 'باز' : ($deal->status === 'won' ? 'موفق' : 'ناموفق') }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <!-- Title -->
                                <div>
                                    <label class="{{ $labelClass }}">عنوان پیگیری <span class="text-rose-500">*</span></label>
                                    <input type="text" wire:model="title" class="{{ $inputClass }}" placeholder="مثال: پیگیری استعلام قیمت یا تمدید قرارداد">
                                    @error('title') <span class="text-rose-500 text-[11px] mt-1 block font-sans">{{ $message }}</span> @enderror
                                </div>

                                <!-- Type and Priority -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="{{ $labelClass }}">نوع پیگیری <span class="text-rose-500">*</span></label>
                                        <select wire:model="taskType" class="{{ $inputClass }}">
                                            <option value="FOLLOW_UP">پیگیری (Follow-up)</option>
                                            <option value="GENERAL">وظیفه عمومی</option>
                                        </select>
                                        @error('taskType') <span class="text-rose-500 text-[11px] mt-1 block font-sans">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">اولویت <span class="text-rose-500">*</span></label>
                                        <select wire:model="taskPriority" class="{{ $inputClass }}">
                                            <option value="LOW">کم</option>
                                            <option value="MEDIUM">معمولی</option>
                                            <option value="HIGH">زیاد</option>
                                            <option value="CRITICAL">بحرانی (فوری)</option>
                                        </select>
                                        @error('taskPriority') <span class="text-rose-500 text-[11px] mt-1 block font-sans">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- Date & Time Picker -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="{{ $labelClass }}">تاریخ سررسید (شمسی) <span class="text-rose-500">*</span></label>
                                        <input type="text"
                                               wire:model="due_date_jalali"
                                               data-jdp
                                               autocomplete="off"
                                               class="{{ $inputClass }}"
                                               placeholder="1403/06/30">
                                        @error('due_date_jalali') <span class="text-rose-500 text-[11px] mt-1 block font-sans">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">ساعت انجام</label>
                                        <input type="time"
                                               wire:model="due_time"
                                               class="{{ $inputClass }}">
                                        @error('due_time') <span class="text-rose-500 text-[11px] mt-1 block font-sans">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- Assignee Selection -->
                                <div>
                                    <label class="{{ $labelClass }}">کارشناس مسئول</label>
                                    <select wire:model="assignee_id" class="{{ $inputClass }}">
                                        <option value="">-- بدون تخصیص (پیش‌فرض: خودم) --</option>
                                        @if(isset($users))
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('assignee_id') <span class="text-rose-500 text-[11px] mt-1 block font-sans">{{ $message }}</span> @enderror
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="{{ $labelClass }}">توضیحات و نکات تکمیلی</label>
                                    <textarea wire:model="description" rows="3" class="{{ $inputClass }} resize-none" placeholder="نکاتی درباره اهداف پیگیری و توافقات قبلی..."></textarea>
                                    @error('description') <span class="text-rose-500 text-[11px] mt-1 block font-sans">{{ $message }}</span> @enderror
                                </div>

                                <!-- Footer Buttons -->
                                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-gray-100 dark:border-gray-700/80 mt-5">
                                    <button type="button"
                                            x-on:click="show = false"
                                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-colors font-sans">
                                        انصراف
                                    </button>
                                    <button type="submit"
                                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs hover:shadow transition-all active:scale-95 font-sans">
                                        {{ $editingTaskId ? 'بروزرسانی پیگیری' : 'ثبت و ذخیره پیگیری' }}
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
