<div>
    <style>
        .tooth-path {
            cursor: pointer;
            transition: fill .14s ease, stroke .14s ease, filter .14s ease;
            stroke-width: 1.5px;
            vector-effect: non-scaling-stroke;
        }
        .tooth-selected {
            fill: #4f46e5 !important;
            stroke: #4338ca !important;
            stroke-width: 2.5px !important;
            filter: drop-shadow(0 2px 6px rgba(79, 70, 229, 0.45));
        }
        .dark .tooth-selected {
            fill: #6366f1 !important;
            stroke: #818cf8 !important;
        }
        .tooth-unselected {
            fill: #ffffff !important;
            stroke: #cbd5e1 !important;
        }
        .dark .tooth-unselected {
            fill: #1e293b !important;
            stroke: #475569 !important;
        }
        .tooth-unselected:hover {
            fill: #eef2ff !important;
            stroke: #6366f1 !important;
        }
        .dark .tooth-unselected:hover {
            fill: #334155 !important;
            stroke: #818cf8 !important;
        }
    </style>

    @php
        $todayJalali = \Morilog\Jalali\Jalalian::now()->format('Y/m/d');
        $yesterdayJalali = \Morilog\Jalali\Jalalian::now()->subDays(1)->format('Y/m/d');
        $tomorrowJalali = \Morilog\Jalali\Jalalian::now()->addDays(1)->format('Y/m/d');
        $isToday = ($selectedDateJalali === $todayJalali);
    @endphp

    <div class="space-y-6">
        {{-- هدر صفحه کارتابل پیگیری روزانه --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-xs p-5 sm:p-6">
            <div class="flex items-center gap-3.5">
                <span class="flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/25">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </span>
                <div>
                    <h1 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        برگه پیگیری روزانه لابراتوار (Daily Board)
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-300 mt-1">
                        کارتابل متمرکز پیگیری کارهای سررسید امروز ({{ $selectedDateJalali }})، کنترل تاخیرها و ثبت نتایج تماس
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('user.booking.laboratory.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-xs font-bold transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    مدیریت سفارشات لابراتوار
                </a>
            </div>
        </div>

        {{-- اعلان موفقیت پیام‌ها --}}
        @if($toastSuccess)
            <div class="rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 p-4 border border-emerald-200 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    {{ $toastSuccess }}
                </div>
                <button type="button" wire:click="$set('toastSuccess', null)" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200 text-base font-bold">&times;</button>
            </div>
        @endif

        {{-- ۴ کارت آماری هوشمند KPI پیگیری روزانه (همگی کاملاً متصل به تاریخ انتخابی) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- کارت تاخیرها --}}
            <button type="button" 
                    wire:click="setKpiFilter('overdue')" 
                    class="p-4 rounded-3xl border text-right transition-all duration-150 cursor-pointer {{ $kpiFilter === 'overdue' ? 'bg-rose-500 text-white border-rose-600 shadow-md shadow-rose-500/25 ring-2 ring-rose-500/30' : 'bg-white dark:bg-gray-800 border-rose-200 dark:border-rose-900/50 hover:bg-rose-50/60 dark:hover:bg-rose-950/30' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold {{ $kpiFilter === 'overdue' ? 'text-white' : 'text-rose-600 dark:text-rose-400' }}">کارهای دارای تاخیر</span>
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center {{ $kpiFilter === 'overdue' ? 'bg-white/20 text-white' : 'bg-rose-100 dark:bg-rose-950/80 text-rose-600 dark:text-rose-400' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black {{ $kpiFilter === 'overdue' ? 'text-white' : 'text-gray-900 dark:text-white' }}">{{ $overdueCount }}</span>
                    <span class="text-[11px] {{ $kpiFilter === 'overdue' ? 'text-rose-100' : 'text-slate-500 dark:text-slate-400' }}">نیازمند تماس فوری</span>
                </div>
            </button>

            {{-- کارت موعد پیگیری امروز / تاریخ انتخابی --}}
            <button type="button" 
                    wire:click="setKpiFilter('due_today')" 
                    class="p-4 rounded-3xl border text-right transition-all duration-150 cursor-pointer {{ $kpiFilter === 'due_today' ? 'bg-amber-500 text-white border-amber-600 shadow-md shadow-amber-500/25 ring-2 ring-amber-500/30' : 'bg-white dark:bg-gray-800 border-amber-200 dark:border-amber-900/50 hover:bg-amber-50/60 dark:hover:bg-amber-950/30' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold {{ $kpiFilter === 'due_today' ? 'text-white' : 'text-amber-600 dark:text-amber-400' }}">
                        {{ $isToday ? 'موعد پیگیری امروز' : 'موعد سررسید این تاریخ' }}
                    </span>
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center {{ $kpiFilter === 'due_today' ? 'bg-white/20 text-white' : 'bg-amber-100 dark:bg-amber-950/80 text-amber-600 dark:text-amber-400' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black {{ $kpiFilter === 'due_today' ? 'text-white' : 'text-gray-900 dark:text-white' }}">{{ $dueTodayCount }}</span>
                    <span class="text-[11px] {{ $kpiFilter === 'due_today' ? 'text-amber-100' : 'text-slate-500 dark:text-slate-400' }}">سررسید در {{ $selectedDateJalali }}</span>
                </div>
            </button>

            {{-- کارت پیگیری‌شده‌های این تاریخ --}}
            <button type="button" 
                    wire:click="setKpiFilter('completed_today')" 
                    class="p-4 rounded-3xl border text-right transition-all duration-150 cursor-pointer {{ $kpiFilter === 'completed_today' ? 'bg-emerald-600 text-white border-emerald-700 shadow-md shadow-emerald-600/25 ring-2 ring-emerald-500/30' : 'bg-white dark:bg-gray-800 border-emerald-200 dark:border-emerald-900/50 hover:bg-emerald-50/60 dark:hover:bg-emerald-950/30' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold {{ $kpiFilter === 'completed_today' ? 'text-white' : 'text-emerald-600 dark:text-emerald-400' }}">پیگیری‌شده‌های این تاریخ</span>
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center {{ $kpiFilter === 'completed_today' ? 'bg-white/20 text-white' : 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black {{ $kpiFilter === 'completed_today' ? 'text-white' : 'text-gray-900 dark:text-white' }}">{{ $completedTodayCount }}</span>
                    <span class="text-[11px] {{ $kpiFilter === 'completed_today' ? 'text-emerald-100' : 'text-slate-500 dark:text-slate-400' }}">ثبت لاگ انجام شده</span>
                </div>
            </button>

            {{-- کارت کل کارتابل این روز (مجموع تاخیرها + موعد امروز + پیگیری‌شده‌ها) --}}
            <button type="button" 
                    wire:click="setKpiFilter('daily_all')" 
                    class="p-4 rounded-3xl border text-right transition-all duration-150 cursor-pointer {{ $kpiFilter === 'daily_all' ? 'bg-indigo-600 text-white border-indigo-700 shadow-md shadow-indigo-600/25 ring-2 ring-indigo-500/30' : 'bg-white dark:bg-gray-800 border-indigo-200 dark:border-indigo-800/80 hover:bg-indigo-50/60 dark:hover:bg-indigo-950/30' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold {{ $kpiFilter === 'daily_all' ? 'text-white' : 'text-indigo-600 dark:text-indigo-400' }}">
                        {{ $isToday ? 'کل کارتابل امروز' : 'کل کارتابل این تاریخ' }}
                    </span>
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center {{ $kpiFilter === 'daily_all' ? 'bg-white/20 text-white' : 'bg-indigo-100 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-black {{ $kpiFilter === 'daily_all' ? 'text-white' : 'text-gray-900 dark:text-white' }}">{{ $dailyTotalCount }}</span>
                    <span class="text-[11px] {{ $kpiFilter === 'daily_all' ? 'text-indigo-100' : 'text-slate-500 dark:text-slate-400' }}">نیازمند توجه / اقدام</span>
                </div>
            </button>
        </div>

        {{-- نوار ابزار: ناوبری تاریخ و فیلترهای تکمیلی --}}
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-xs p-4 sm:p-5 space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                
                {{-- بخش ناوبری سریع تاریخ --}}
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 ml-1">تاریخ کارتابل:</span>
                    
                    <div class="inline-flex items-center p-1 rounded-2xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                        <button type="button" 
                                wire:click="setDateYesterday" 
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $selectedDateJalali === $yesterdayJalali ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                            دیروز
                        </button>
                        <button type="button" 
                                wire:click="setDateToday" 
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $selectedDateJalali === $todayJalali ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                            امروز
                        </button>
                        <button type="button" 
                                wire:click="setDateTomorrow" 
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $selectedDateJalali === $tomorrowJalali ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                            فردا
                        </button>
                    </div>

                    <div class="w-36">
                        <input type="text" 
                               wire:model.live.debounce.400ms="selectedDateJalali" 
                               placeholder="1403/07/03" 
                               class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3.5 py-2 text-xs text-center font-bold text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-indigo-500/20 transition-all">
                    </div>
                </div>

                {{-- بخش جستجو و فیلترهای متمرکز --}}
                <div class="flex items-center gap-2.5 flex-wrap flex-1 lg:justify-end">
                    {{-- فیلتر لابراتوار همکار --}}
                    <div class="w-full sm:w-48">
                        <select wire:model.live="selectedLabPartner" 
                                class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3.5 py-2 text-xs font-medium text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-indigo-500/20 transition-all">
                            <option value="">همه لابراتوارها</option>
                            @foreach($labPartners as $lp)
                                <option value="{{ $lp }}">{{ $lp }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- فیلتر پزشک معالج --}}
                    <div class="w-full sm:w-44">
                        <select wire:model.live="selectedDoctorId" 
                                class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3.5 py-2 text-xs font-medium text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-indigo-500/20 transition-all">
                            <option value="">همه پزشکان</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- اینپوت جستجو --}}
                    <div class="w-full sm:w-64 relative">
                        <input type="text" 
                               wire:model.live.debounce.300ms="search" 
                               placeholder="جستجوی نام، پرونده، کد..." 
                               class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3.5 py-2 text-xs text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-indigo-500/20 transition-all pl-9">
                        <svg class="w-4 h-4 absolute left-3 top-2.5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    @if(!empty($search) || !empty($selectedLabPartner) || !empty($selectedDoctorId) || $kpiFilter !== 'daily_all')
                        <button type="button" 
                                wire:click="$set('search', ''); $set('selectedLabPartner', ''); $set('selectedDoctorId', ''); $set('kpiFilter', 'daily_all');" 
                                class="p-2 rounded-2xl bg-gray-100 dark:bg-gray-700 text-slate-500 hover:text-slate-800 dark:text-slate-300 dark:hover:text-white text-xs font-bold transition-colors cursor-pointer"
                                title="ریست به حالت پیش‌فرض کارتابل روز">
                            ✕
                        </button>
                    @endif
                </div>
            </div>

            {{-- نوار تب‌های کارتابل: پیش‌فرض روی «کل کارتابل این روز» تا کاربر همه چیز را یکجا ببیند --}}
            <div class="flex items-center gap-2 pt-3 border-t border-gray-100 dark:border-gray-700/80 flex-wrap">
                <span class="text-xs font-bold text-gray-600 dark:text-gray-400">نمایش:</span>

                {{-- تب پیش‌فرض: کل کارتابل این روز --}}
                <button type="button" 
                        wire:click="setKpiFilter('daily_all')"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 {{ $kpiFilter === 'daily_all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    <span>کل کارتابل این تاریخ</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $kpiFilter === 'daily_all' ? 'bg-indigo-700 text-white' : 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300' }}">{{ $dailyTotalCount }}</span>
                </button>

                {{-- فقط دارای تاخیر --}}
                <button type="button" 
                        wire:click="setKpiFilter('overdue')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 {{ $kpiFilter === 'overdue' ? 'bg-rose-500 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    <span>دارای تاخیر</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $kpiFilter === 'overdue' ? 'bg-rose-600 text-white' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }}">{{ $overdueCount }}</span>
                </button>

                {{-- فقط موعد امروز --}}
                <button type="button" 
                        wire:click="setKpiFilter('due_today')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 {{ $kpiFilter === 'due_today' ? 'bg-amber-500 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    <span>موعد سررسید</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $kpiFilter === 'due_today' ? 'bg-amber-600 text-white' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' }}">{{ $dueTodayCount }}</span>
                </button>

                {{-- فقط پیگیری‌شده‌ها --}}
                <button type="button" 
                        wire:click="setKpiFilter('completed_today')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 {{ $kpiFilter === 'completed_today' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    <span>پیگیری‌شده‌ها</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $kpiFilter === 'completed_today' ? 'bg-emerald-700 text-white' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' }}">{{ $completedTodayCount }}</span>
                </button>

                {{-- موعدهای روزهای آینده --}}
                <button type="button" 
                        wire:click="setKpiFilter('upcoming')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 {{ $kpiFilter === 'upcoming' ? 'bg-sky-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    <span>روزهای آینده</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $kpiFilter === 'upcoming' ? 'bg-sky-700 text-white' : 'bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300' }}">{{ $upcomingCount }}</span>
                </button>

                {{-- همه سفارشات فعال در جریان --}}
                <button type="button" 
                        wire:click="setKpiFilter('all')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 {{ $kpiFilter === 'all' ? 'bg-slate-700 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    <span>همه سفارشات فعال</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $kpiFilter === 'all' ? 'bg-slate-800 text-white' : 'bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-200' }}">{{ $allActiveCount }}</span>
                </button>
            </div>
        </div>

        {{-- جدول پیشرفته کارتابل پیگیری روزانه --}}
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="bg-gray-50/90 dark:bg-gray-900/70 border-b border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-bold">
                            <th class="py-3.5 px-4 w-14 text-center">#</th>
                            <th class="py-3.5 px-4">بیمار و پروتز (دندان‌ها +)</th>
                            <th class="py-3.5 px-4">لابراتوار و پزشک معالج</th>
                            <th class="py-3.5 px-4">مرحله جاری و موعد</th>
                            <th class="py-3.5 px-4">آخرین وضعیت پیگیری و تماس</th>
                            <th class="py-3.5 px-4 text-center w-64 min-w-[240px]">اقدامات عملیاتی سریع</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200/80 dark:divide-gray-700/80">
                        @forelse($rows as $index => $item)
                            @php
                                $order = $item->order;
                                $stage = $item->currentStage;
                                $statusInfo = $item->stageDaysStatus;
                                $isOverdue = $item->isOverdue;
                                $isDueOnDate = $item->isDueOnDate;
                                $targetLog = $item->targetDateLog;
                                $latestLog = $item->latestLog;

                                $trClass = 'transition-all duration-150 ';
                                if ($isOverdue) {
                                    $trClass .= 'bg-rose-50/60 dark:bg-rose-950/30 border-r-4 border-rose-500 hover:bg-rose-50/90 dark:hover:bg-rose-950/40';
                                } elseif ($isDueOnDate) {
                                    $trClass .= 'bg-amber-50/60 dark:bg-amber-950/25 border-r-4 border-amber-500 hover:bg-amber-50/90 dark:hover:bg-amber-950/35';
                                } elseif ($item->isLoggedToday) {
                                    $trClass .= 'bg-emerald-50/40 dark:bg-emerald-950/20 border-r-4 border-emerald-500 hover:bg-emerald-50/70 dark:hover:bg-emerald-950/30';
                                } else {
                                    $trClass .= 'hover:bg-gray-50/80 dark:hover:bg-gray-700/40';
                                }
                            @endphp

                            <tr class="{{ $trClass }}">
                                {{-- ۱. ردیف --}}
                                <td class="py-4 px-4 text-center font-bold text-gray-700 dark:text-gray-300">
                                    {{ $index + 1 }}
                                </td>

                                {{-- ۲. بیمار و پروتز همراه کادر پالمر دندان‌ها (+) --}}
                                <td class="py-4 px-4">
                                    <div class="space-y-1.5">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-bold text-gray-900 dark:text-white text-sm cursor-pointer hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                                                  wire:click="openDetailModal({{ $order->id }}, 'overview')">
                                                {{ $order->patient_name }}
                                            </span>
                                            @if($order->patient_file_number)
                                                <span class="text-[11px] text-slate-500 dark:text-slate-400">({{ $order->patient_file_number }})</span>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-[11px] text-slate-600 dark:text-slate-300">{{ $order->category_label }}</span>
                                            @if($order->units_count > 0)
                                                <span class="text-[11px] text-indigo-600 dark:text-indigo-400 font-bold">({{ $order->units_count }} واحد)</span>
                                            @endif
                                        </div>

                                        {{-- کادر کراس پالمر دندان‌ها (+) با کلیک برای باز شدن تب نقشه دندانی --}}
                                        @if($order->has_teeth)
                                            @php
                                                $dtGrouped = $order->grouped_teeth;
                                            @endphp
                                            <div wire:click="openDetailModal({{ $order->id }}, 'dental_chart')"
                                                 class="inline-flex items-center gap-2 border border-indigo-200 dark:border-indigo-800/80 bg-indigo-50/40 dark:bg-slate-800/80 px-2.5 py-1 rounded-xl shadow-2xs select-none mt-0.5 cursor-pointer hover:border-indigo-400 dark:hover:border-indigo-600 transition-colors"
                                                 title="مشاهده نقشه دندانی کامل">
                                                <span class="text-[10px] font-bold text-indigo-700 dark:text-indigo-300 pl-1.5 border-l border-indigo-200 dark:border-indigo-700">دندان:</span>
                                                <div class="inline-grid grid-cols-2 text-center align-middle">
                                                    {{-- UR --}}
                                                    <div class="border-l-2 border-b-2 border-slate-300 dark:border-slate-500 py-0.5 px-1.5 flex items-center justify-end gap-1 min-w-[20px] min-h-[16px]">
                                                        @foreach($dtGrouped['UR'] as $t)
                                                            <span class="inline-flex items-center justify-center min-w-[10px] text-[11px] font-black text-indigo-600 dark:text-indigo-400">{{ $t['num'] }}</span>
                                                        @endforeach
                                                    </div>
                                                    {{-- UL --}}
                                                    <div class="border-b-2 border-slate-300 dark:border-slate-500 py-0.5 px-1.5 flex items-center justify-start gap-1 min-w-[20px] min-h-[16px]">
                                                        @foreach($dtGrouped['UL'] as $t)
                                                            <span class="inline-flex items-center justify-center min-w-[10px] text-[11px] font-black text-indigo-600 dark:text-indigo-400">{{ $t['num'] }}</span>
                                                        @endforeach
                                                    </div>
                                                    {{-- LR --}}
                                                    <div class="border-l-2 border-slate-300 dark:border-slate-500 py-0.5 px-1.5 flex items-center justify-end gap-1 min-w-[20px] min-h-[16px]">
                                                        @foreach($dtGrouped['LR'] as $t)
                                                            <span class="inline-flex items-center justify-center min-w-[10px] text-[11px] font-black text-indigo-600 dark:text-indigo-400">{{ $t['num'] }}</span>
                                                        @endforeach
                                                    </div>
                                                    {{-- LL --}}
                                                    <div class="py-0.5 px-1.5 flex items-center justify-start gap-1 min-w-[20px] min-h-[16px]">
                                                        @foreach($dtGrouped['LL'] as $t)
                                                            <span class="inline-flex items-center justify-center min-w-[10px] text-[11px] font-black text-indigo-600 dark:text-indigo-400">{{ $t['num'] }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- ۳. لابراتوار و پزشک معالج --}}
                                <td class="py-4 px-4">
                                    <div class="space-y-1">
                                        <div class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
                                            {{ $order->lab_partner_name }}
                                        </div>
                                        <div class="text-[11px] text-slate-600 dark:text-slate-300">
                                            پزشک: {{ $order->doctor?->name ?: 'تعیین نشده' }}
                                        </div>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400">
                                            ارسال: {{ $order->sent_at_jalali ?: '-' }}
                                        </div>
                                    </div>
                                </td>

                                {{-- ۴. مرحله جاری و شمارنده هوشمند روزهای تاخیر/مانده --}}
                                <td class="py-4 px-4">
                                    <div class="space-y-1.5">
                                        @if($stage)
                                            <div class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                                <span>{{ $stage->stage_title }}</span>
                                            </div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                                موعد: {{ $stage->due_at_jalali ?: '-' }}
                                            </div>

                                            {{-- بج شمارنده وضعیت بر اساس اولویت و فوریت --}}
                                            @if($isOverdue)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border border-rose-300 dark:border-rose-800 animate-pulse">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    {{ $statusInfo['text'] }}
                                                </span>
                                            @elseif($isDueOnDate)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                                    ● {{ $statusInfo['text'] }}
                                                </span>
                                            @elseif($item->isLoggedToday)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                                    ✓ پیگیری انجام شد
                                                </span>
                                            @elseif(($statusInfo['type'] ?? '') === 'upcoming')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                                    {{ $statusInfo['text'] }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-xs flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                آماده دریافت در مطب
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- ۵. آخرین وضعیت پیگیری و تماس --}}
                                <td class="py-4 px-4">
                                    <div class="max-w-xs space-y-1">
                                        @if($targetLog && $targetLog->followup_result)
                                            <div class="p-2.5 rounded-2xl bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/40 text-[11px] text-emerald-900 dark:text-emerald-200 leading-relaxed">
                                                <div class="font-bold flex items-center justify-between gap-1 mb-0.5">
                                                    <span>تماس این تاریخ:</span>
                                                    <span class="text-[10px] text-emerald-700 dark:text-emerald-400">{{ $targetLog->operator?->name ?: 'اپراتور' }}</span>
                                                </div>
                                                {{ $targetLog->followup_result }}
                                            </div>
                                        @elseif($latestLog && $latestLog->followup_result)
                                            <div class="p-2.5 rounded-2xl bg-gray-50 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-700 text-[11px] text-gray-800 dark:text-gray-200 leading-relaxed">
                                                <div class="flex items-center justify-between gap-1 mb-0.5 text-[10px] text-slate-500 dark:text-slate-400">
                                                    <span>آخرین تماس ({{ $latestLog->log_date_jalali }}):</span>
                                                    <span>{{ $latestLog->operator?->name ?: 'اپراتور' }}</span>
                                                </div>
                                                {{ $latestLog->followup_result }}
                                            </div>
                                        @else
                                            <span class="text-slate-400 dark:text-slate-500 italic text-[11px]">هنوز یادداشت تماسی ثبت نشده است.</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- ۶. اقدامات عملیاتی سریع --}}
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap">
                                        {{-- دکمه ثبت نتیجه تماس --}}
                                        <button type="button" 
                                                wire:click="openLogModal({{ $order->id }}, {{ $stage?->id }})" 
                                                class="inline-flex items-center gap-1.5 h-8 px-3 rounded-xl bg-amber-500 hover:bg-amber-600 dark:bg-amber-500/20 dark:hover:bg-amber-500/30 text-white dark:text-amber-300 dark:border dark:border-amber-500/40 font-bold text-[11px] shadow-xs hover:shadow-sm transition-all cursor-pointer"
                                                title="ثبت نتیجه تماس و تغییر وضعیت">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                            <span>تماس و ثبت</span>
                                        </button>

                                        {{-- دکمه تایید مرحله --}}
                                        @if($stage)
                                            <button type="button" 
                                                    wire:click="openStageModal({{ $stage->id }})" 
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-500/15 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-500/30 shadow-2xs transition-all cursor-pointer"
                                                    title="تایید و تکمیل مرحله">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                            </button>
                                        @endif

                                        {{-- دکمه دریافت کار از لابراتوار (سبز) --}}
                                        <button type="button" 
                                                wire:click="openReceiveModal({{ $order->id }})" 
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-500/15 dark:hover:bg-emerald-500/25 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-500/30 shadow-2xs transition-all cursor-pointer"
                                                title="دریافت نهایی کار (سبز شدن ردیف)">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                        </button>

                                        {{-- دکمه مشاهده کامل جزئیات و نقشه دندانی --}}
                                        <button type="button" 
                                                wire:click="openDetailModal({{ $order->id }}, 'overview')" 
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800/80 dark:hover:bg-slate-700/80 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 shadow-2xs transition-all cursor-pointer"
                                                title="مشاهده جزئیات کامل و نقشه دندانی">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-14 text-center text-slate-400 dark:text-slate-500">
                                    <div class="max-w-sm mx-auto space-y-3">
                                        <div class="w-14 h-14 mx-auto rounded-3xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">هیچ سفارشی در این وضعیت وجود ندارد</h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                            در این تاریخ پیگیری، موردی در وضعیت انتخابی یافت نشد. می‌توانید از تب‌های بالا «روزهای آینده» یا «همه سفارشات فعال» را بررسی نمایید.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- مودال هوشمند ثبت پیگیری و نتیجه تماس --}}
    @if($showLogModal && $modalOrder)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-2xl w-full max-w-xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                
                {{-- هدر مودال لاگ --}}
                <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/80 dark:bg-gray-900/60">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center shadow-md shadow-amber-500/20">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                                ثبت نتیجه تماس و پیگیری لابراتوار
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                بیمار: <strong>{{ $modalOrder->patient_name }}</strong> (لابراتوار: {{ $modalOrder->lab_partner_name }})
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="$set('showLogModal', false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                {{-- بدنه مودال --}}
                <div class="p-5 sm:p-6 space-y-4 text-xs max-h-[75vh] overflow-y-auto">
                    
                    {{-- کادر خلاصه مرحله جاری --}}
                    <div class="p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 flex items-center justify-between gap-3 flex-wrap">
                        <div>
                            <span class="text-slate-500 dark:text-slate-400 block text-[11px]">مرحله در حال پیگیری:</span>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400 text-xs">{{ $currentStageTitle }}</span>
                        </div>

                        @if($modalOrder->has_teeth)
                            <div class="inline-flex items-center gap-1.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-2 py-0.5 rounded-lg">
                                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">دندان‌ها:</span>
                                <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400">{{ $modalOrder->teeth_numbers }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- انتخاب نوع اقدام روی مرحله --}}
                    <div class="space-y-2">
                        <label class="block font-bold text-gray-700 dark:text-gray-300">اقدام متناظر با این پیگیری:</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <label class="p-3 rounded-2xl border cursor-pointer transition-all flex flex-col justify-between gap-1 {{ $modalActionType === 'log_only' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/30 text-amber-900 dark:text-amber-200 ring-1 ring-amber-500' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                                <div class="flex items-center gap-2">
                                    <input type="radio" wire:model.live="modalActionType" value="log_only" class="text-amber-500 focus:ring-amber-500">
                                    <span class="font-bold">فقط ثبت تماس</span>
                                </div>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">بدون تغییر در مرحله</span>
                            </label>

                            <label class="p-3 rounded-2xl border cursor-pointer transition-all flex flex-col justify-between gap-1 {{ $modalActionType === 'complete_stage' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30 text-emerald-900 dark:text-emerald-200 ring-1 ring-emerald-500' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                                <div class="flex items-center gap-2">
                                    <input type="radio" wire:model.live="modalActionType" value="complete_stage" class="text-emerald-500 focus:ring-emerald-500">
                                    <span class="font-bold">تکمیل مرحله جاری</span>
                                </div>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">انتقال به مرحله بعد</span>
                            </label>

                            <label class="p-3 rounded-2xl border cursor-pointer transition-all flex flex-col justify-between gap-1 {{ $modalActionType === 'postpone_stage' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/30 text-indigo-900 dark:text-indigo-200 ring-1 ring-indigo-500' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                                <div class="flex items-center gap-2">
                                    <input type="radio" wire:model.live="modalActionType" value="postpone_stage" class="text-indigo-500 focus:ring-indigo-500">
                                    <span class="font-bold">تمدید موعد پیگیری</span>
                                </div>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">جابجایی سررسید مرحله</span>
                            </label>
                        </div>
                    </div>

                    {{-- در صورت انتخاب تمدید موعد --}}
                    @if($modalActionType === 'postpone_stage')
                        <div class="p-3.5 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/60 space-y-2">
                            <label class="block font-bold text-indigo-900 dark:text-indigo-200">میزان تمدید موعد سررسید:</label>
                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" wire:click="$set('modalPostponeDays', 1)" class="px-3 py-1 rounded-xl font-bold cursor-pointer {{ $modalPostponeDays === 1 ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-white dark:bg-gray-800 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800' }}">۱ روز دیگر</button>
                                <button type="button" wire:click="$set('modalPostponeDays', 2)" class="px-3 py-1 rounded-xl font-bold cursor-pointer {{ $modalPostponeDays === 2 ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-white dark:bg-gray-800 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800' }}">۲ روز دیگر</button>
                                <button type="button" wire:click="$set('modalPostponeDays', 3)" class="px-3 py-1 rounded-xl font-bold cursor-pointer {{ $modalPostponeDays === 3 ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-white dark:bg-gray-800 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800' }}">۳ روز دیگر</button>
                                <button type="button" wire:click="$set('modalPostponeDays', 5)" class="px-3 py-1 rounded-xl font-bold cursor-pointer {{ $modalPostponeDays === 5 ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-white dark:bg-gray-800 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800' }}">۵ روز دیگر</button>
                                <button type="button" wire:click="$set('modalPostponeDays', 7)" class="px-3 py-1 rounded-xl font-bold cursor-pointer {{ $modalPostponeDays === 7 ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-white dark:bg-gray-800 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800' }}">۱ هفته</button>
                            </div>
                        </div>
                    @endif

                    {{-- متن نتیجه تماس --}}
                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1.5">شرح نتیجه تماس و توضیحات لابراتوار *</label>
                        <textarea wire:model="modalResult" rows="3" placeholder="مثلاً: با لابراتوار تماس گرفته شد؛ اعلام کردند مرحله فریم انجام شده و فردا ظهر برای امتحان ارسال می‌شود..."
                                  class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-3 text-xs text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-amber-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-amber-500/20 transition-all"></textarea>
                    </div>

                    {{-- تاریخچه تماس‌های قبلی همین سفارش --}}
                    @if($modalOrder->dailyLogs && $modalOrder->dailyLogs->count() > 0)
                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700 space-y-2">
                            <span class="font-bold text-gray-700 dark:text-gray-300 block text-[11px]">سابقه تماس‌های قبلی با لابراتوار:</span>
                            <div class="max-h-36 overflow-y-auto space-y-1.5 pr-1">
                                @foreach($modalOrder->dailyLogs->sortByDesc('created_at')->take(4) as $pastLog)
                                    <div class="p-2 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-150 dark:border-gray-700/60 text-[11px]">
                                        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-[10px] mb-0.5">
                                            <span>{{ $pastLog->log_date_jalali }}</span>
                                            <span>{{ $pastLog->operator?->name ?: 'اپراتور' }}</span>
                                        </div>
                                        <div class="text-gray-800 dark:text-gray-200">{{ $pastLog->followup_result }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- فوتر مودال لاگ --}}
                <div class="p-5 border-t border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/60 flex items-center justify-end gap-2.5">
                    <button type="button" wire:click="$set('showLogModal', false)" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 transition-colors cursor-pointer">انصراف</button>
                    <button type="button" wire:click="saveDailyLog" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-md shadow-amber-500/20 transition-all cursor-pointer">ثبت و ذخیره گزارش</button>
                </div>
            </div>
        </div>
    @endif

    {{-- مودال تایید / تکمیل سریع مرحله --}}
    @if($showStageModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-2xl w-full max-w-md p-6 space-y-4">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    تایید و تکمیل مرحله پیگیری
                </h3>

                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    با تایید این مرحله، وضعیت آن به حالت «تکمیل شده» تغییر کرده و کار به مرحله بعدی منتقل می‌شود. در صورت تمایل توضیحی وارد کنید:
                </p>

                <textarea wire:model="stageNote" rows="2" placeholder="توضیحات اختیاری (مثلاً: کار فریم تحویل گرفته و تایید شد)..."
                          class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-3 text-xs text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-indigo-500/20 transition-all"></textarea>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" wire:click="$set('showStageModal', false)" class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 transition-colors cursor-pointer">انصراف</button>
                    <button type="button" wire:click="confirmStageCompletion" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all cursor-pointer">تایید و تکمیل مرحله</button>
                </div>
            </div>
        </div>
    @endif

    {{-- مودال دریافت نهایی کار (سبز شدن ردیف) --}}
    @if($showReceiveModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-emerald-300 dark:border-emerald-700/60 shadow-2xl w-full max-w-md p-6 space-y-4">
                <div class="flex items-center gap-3 text-emerald-600 dark:text-emerald-400">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-100 dark:bg-emerald-950/60 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">دریافت نهایی کار از لابراتوار</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">تغییر وضعیت به آماده تحویل در مطب</p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    با تایید دریافت کار، کلیه مراحل ساخت سفارش تکمیل شده و ردیف سفارش به رنگ <strong class="text-emerald-600 dark:text-emerald-400 font-bold">سبز</strong> در می‌آید.
                </p>

                <textarea wire:model="receiveNote" rows="2" placeholder="توضیحات دریافت در مطب (اختیاری)..."
                          class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-3 text-xs text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-emerald-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-emerald-500/20 transition-all"></textarea>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" wire:click="$set('showReceiveModal', false)" class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 transition-colors cursor-pointer">انصراف</button>
                    <button type="button" wire:click="confirmReceiveOrder" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-500/20 transition-all cursor-pointer">تایید دریافت کار (سبز)</button>
                </div>
            </div>
        </div>
    @endif

    {{-- مودال جامع جزئیات کامل سفارش و نقشه دندانی --}}
    @if($showDetailModal && $detailOrder)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-2xl w-full max-w-4xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                
                {{-- هدر مودال جزئیات --}}
                <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/60 flex items-center justify-between gap-4 flex-wrap">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/25 shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                    جزئیات سفارش لابراتوار: {{ $detailOrder->order_number }}
                                </h3>
                                @if($detailOrder->isReceived())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        دریافت شده در مطب
                                    </span>
                                @elseif($detailOrder->isOverdue())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300 dark:border-rose-700 animate-pulse">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        دارای تاخیر
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-300 dark:border-indigo-700">
                                        در جریان ساخت
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                ثبت شده در تاریخ {{ $detailOrder->created_at_jalali ?: '-' }} 
                                @if($detailOrder->createdBy)
                                    توسط {{ $detailOrder->createdBy->name }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" 
                                wire:click="closeDetailModal" 
                                class="p-2 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/60 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                {{-- تب‌های ناوبری بالای مودال جزئیات --}}
                <div class="px-5 sm:px-6 pt-3 pb-0 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex items-center gap-2 overflow-x-auto">
                    <button type="button" 
                            wire:click="setDetailTab('overview')" 
                            class="pb-3 px-3.5 text-xs font-bold transition-all border-b-2 flex items-center gap-1.5 whitespace-nowrap cursor-pointer {{ $detailModalTab === 'overview' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        <span>خلاصه و مراحل ساخت</span>
                    </button>

                    <button type="button" 
                            wire:click="setDetailTab('dental_chart')" 
                            class="pb-3 px-3.5 text-xs font-bold transition-all border-b-2 flex items-center gap-1.5 whitespace-nowrap cursor-pointer {{ $detailModalTab === 'dental_chart' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        <span>نقشه دندانی و واحدها</span>
                        @if($detailOrder->units_count > 0)
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">{{ $detailOrder->units_count }}</span>
                        @endif
                    </button>

                    <button type="button" 
                            wire:click="setDetailTab('logs')" 
                            class="pb-3 px-3.5 text-xs font-bold transition-all border-b-2 flex items-center gap-1.5 whitespace-nowrap cursor-pointer {{ $detailModalTab === 'logs' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>لاگ‌های پیگیری روزانه</span>
                        @if($detailOrder->dailyLogs && $detailOrder->dailyLogs->count() > 0)
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200">{{ $detailOrder->dailyLogs->count() }}</span>
                        @endif
                    </button>
                </div>

                {{-- محتوای اسکرول‌پذیر مودال جزئیات --}}
                <div class="p-5 sm:p-6 space-y-6 max-h-[75vh] overflow-y-auto text-xs">
                    
                    {{-- تب ۱: خلاصه و مراحل ساخت --}}
                    @if($detailModalTab === 'overview')
                        {{-- اطلاعات بیمار، پزشک و طرفین --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                            {{-- بیمار --}}
                            <div class="p-3.5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-200/80 dark:border-gray-700/80 space-y-1">
                                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 block">بیمار</span>
                                <div class="font-bold text-sm text-gray-900 dark:text-white">{{ $detailOrder->patient_name }}</div>
                                @if($detailOrder->patient_file_number)
                                    <div class="text-[11px] text-slate-600 dark:text-slate-300">شماره پرونده: {{ $detailOrder->patient_file_number }}</div>
                                @endif
                                @if($detailOrder->client?->phone)
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">تماس: {{ $detailOrder->client->phone }}</div>
                                @endif
                            </div>

                            {{-- پزشک معالج --}}
                            <div class="p-3.5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-200/80 dark:border-gray-700/80 space-y-1">
                                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 block">پزشک معالج</span>
                                <div class="font-bold text-sm text-gray-900 dark:text-white">{{ $detailOrder->doctor?->name ?: 'تعیین نشده' }}</div>
                                @if($detailOrder->technician)
                                    <div class="text-[11px] text-slate-600 dark:text-slate-300">تکنسین: {{ $detailOrder->technician->name }}</div>
                                @endif
                            </div>

                            {{-- لابراتوار و تاریخ ارسال --}}
                            <div class="p-3.5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-200/80 dark:border-gray-700/80 space-y-1">
                                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 block">لابراتوار مجری</span>
                                <div class="font-bold text-sm text-indigo-600 dark:text-indigo-400">{{ $detailOrder->lab_partner_name }}</div>
                                <div class="text-[11px] text-slate-600 dark:text-slate-300">تاریخ ارسال: {{ $detailOrder->sent_at_jalali ?: '-' }}</div>
                            </div>

                            {{-- موعد و زمان تحویل --}}
                            <div class="p-3.5 rounded-2xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-200/80 dark:border-gray-700/80 space-y-1">
                                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 block">موعد تحویل نهایی</span>
                                <div class="font-bold text-sm text-gray-900 dark:text-white">{{ $detailOrder->expected_delivery_at_jalali ?: 'تعیین نشده' }}</div>
                                @if($detailOrder->received_at_jalali)
                                    <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold">تحویل مطب: {{ $detailOrder->received_at_jalali }}</div>
                                @endif
                            </div>
                        </div>

                        {{-- مشخصات پروتز و کادر کراس پالمر دندان‌ها (+) --}}
                        <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 shadow-xs space-y-3">
                            <div class="flex items-center justify-between gap-4 flex-wrap">
                                <h4 class="font-bold text-xs text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                    مشخصات پروتز و دندان‌ها (پالمر +)
                                </h4>

                                <button type="button" 
                                        wire:click="setDetailTab('dental_chart')" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 font-bold text-xs border border-indigo-200 dark:border-indigo-800/60 transition-all cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    مشاهده نقشه کامل آناتومیک ↵
                                </button>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-bold">
                                    <span>نوع کار:</span>
                                    <span class="text-indigo-600 dark:text-indigo-400">{{ $detailOrder->category_label }}</span>
                                </div>

                                @if($detailOrder->units_count > 0)
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-200 dark:border-indigo-800">
                                        <span>تعداد واحد:</span>
                                        <span>{{ $detailOrder->units_count }} واحد</span>
                                    </div>
                                @endif

                                @if($detailOrder->has_pmma)
                                    <div class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 font-bold border border-amber-200 dark:border-amber-800">
                                        دارای مرحله PMMA
                                    </div>
                                @endif

                                {{-- نمایش کراس پالمر دندان‌ها (+) با چارک‌های متقاطع --}}
                                @if($detailOrder->has_teeth)
                                    @php
                                        $dtGrouped = $detailOrder->grouped_teeth;
                                    @endphp
                                    <div class="inline-flex items-center gap-3 border-2 border-indigo-200 dark:border-indigo-800/80 bg-indigo-50/30 dark:bg-slate-800/90 px-3.5 py-1.5 rounded-2xl shadow-2xs">
                                        <span class="text-xs font-bold text-indigo-700 dark:text-indigo-300 pl-2 border-l border-indigo-200 dark:border-indigo-700">شماره دندان‌ها (پالمر +):</span>
                                        <div class="inline-grid grid-cols-2 text-center align-middle">
                                            {{-- UR --}}
                                            <div class="border-l-2 border-b-2 border-slate-300 dark:border-slate-500 py-1 px-2.5 flex items-center justify-end gap-1.5 min-w-[30px] min-h-[22px]">
                                                @foreach($dtGrouped['UR'] as $t)
                                                    <span class="inline-flex items-center justify-center min-w-[12px] text-sm font-black text-indigo-600 dark:text-indigo-400">{{ $t['num'] }}</span>
                                                @endforeach
                                            </div>
                                            {{-- UL --}}
                                            <div class="border-b-2 border-slate-300 dark:border-slate-500 py-1 px-2.5 flex items-center justify-start gap-1.5 min-w-[30px] min-h-[22px]">
                                                @foreach($dtGrouped['UL'] as $t)
                                                    <span class="inline-flex items-center justify-center min-w-[12px] text-sm font-black text-indigo-600 dark:text-indigo-400">{{ $t['num'] }}</span>
                                                @endforeach
                                            </div>
                                            {{-- LR --}}
                                            <div class="border-l-2 border-slate-300 dark:border-slate-500 py-1 px-2.5 flex items-center justify-end gap-1.5 min-w-[30px] min-h-[22px]">
                                                @foreach($dtGrouped['LR'] as $t)
                                                    <span class="inline-flex items-center justify-center min-w-[12px] text-sm font-black text-indigo-600 dark:text-indigo-400">{{ $t['num'] }}</span>
                                                @endforeach
                                            </div>
                                            {{-- LL --}}
                                            <div class="py-1 px-2.5 flex items-center justify-start gap-1.5 min-w-[30px] min-h-[22px]">
                                                @foreach($dtGrouped['LL'] as $t)
                                                    <span class="inline-flex items-center justify-center min-w-[12px] text-sm font-black text-indigo-600 dark:text-indigo-400">{{ $t['num'] }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @elseif($detailOrder->teeth_numbers)
                                    <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                        دندان‌ها: {{ $detailOrder->teeth_numbers }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- تایم‌لاین مراحل ساخت و زمان‌بندی پیگیری‌ها --}}
                        <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 shadow-xs space-y-4">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <h4 class="font-bold text-xs text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    زمان‌بندی و مراحل پیگیری ساخت
                                </h4>

                                <div class="flex items-center gap-2 text-xs">
                                    <span class="text-slate-500 dark:text-slate-400">پیشرفت کل:</span>
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $detailOrder->completed_stages_count }} از {{ $detailOrder->total_stages_count }} مرحله ({{ $detailOrder->progress_percentage }}٪)</span>
                                </div>
                            </div>

                            {{-- نوار پیشرفت سراسری --}}
                            <div class="w-full bg-slate-100 dark:bg-slate-700/60 rounded-full h-2 overflow-hidden">
                                <div class="bg-indigo-600 dark:bg-indigo-500 h-2 rounded-full transition-all duration-300" style="width: {{ $detailOrder->progress_percentage }}%"></div>
                            </div>

                            {{-- لیست تایم‌لاین عمودی مراحل --}}
                            <div class="relative pl-2 pr-4 space-y-4 before:absolute before:right-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200 dark:before:bg-slate-700">
                                @forelse($detailOrder->stages as $stIndex => $st)
                                    @php
                                        $isDone = $st->is_completed;
                                        $isOver = $st->isOverdue();
                                        $isDueToday = $st->isDueToday();
                                    @endphp
                                    <div class="relative flex items-start gap-3.5 group">
                                        {{-- نقطه وضعیت در خط زمان --}}
                                        <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 z-10 
                                            {{ $isDone ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-500/30' : ($isOver ? 'bg-rose-600 text-white shadow-sm shadow-rose-500/30 animate-pulse' : ($isDueToday ? 'bg-amber-500 text-white' : 'bg-slate-300 dark:bg-slate-600 text-slate-700 dark:text-slate-200')) }}">
                                            @if($isDone)
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                            @else
                                                <span class="text-[10px] font-bold">{{ $stIndex + 1 }}</span>
                                            @endif
                                        </div>

                                        {{-- کارت جزئیات مرحله --}}
                                        <div class="flex-1 p-3 rounded-xl border {{ $isDone ? 'bg-emerald-50/40 border-emerald-200/80 dark:bg-emerald-950/20 dark:border-emerald-800/40' : ($isOver ? 'bg-rose-50/40 border-rose-200/80 dark:bg-rose-950/20 dark:border-rose-800/40' : ($isDueToday ? 'bg-amber-50/40 border-amber-200/80 dark:bg-amber-950/20 dark:border-amber-800/40' : 'bg-gray-50/70 border-gray-200/80 dark:bg-gray-900/30 dark:border-gray-700/80')) }} flex items-center justify-between gap-3 flex-wrap">
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2">
                                                    <h5 class="font-bold text-xs text-gray-900 dark:text-white">{{ $st->stage_title }}</h5>
                                                    @if($isDone)
                                                        <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400">✓ تکمیل شده</span>
                                                    @elseif($isOver)
                                                        <span class="text-[10px] font-bold text-rose-700 dark:text-rose-400">! دارای تاخیر</span>
                                                    @elseif($isDueToday)
                                                        <span class="text-[10px] font-bold text-amber-700 dark:text-amber-400">● موعد امروز</span>
                                                    @else
                                                        <span class="text-[10px] text-slate-500 dark:text-slate-400">در انتظار</span>
                                                    @endif
                                                </div>
                                                <div class="flex flex-wrap items-center gap-x-4 text-[11px] text-slate-600 dark:text-slate-300">
                                                    <span>موعد برنامه‌ریزی: <strong>{{ $st->due_at_jalali }}</strong></span>
                                                    @if($st->completed_at_jalali)
                                                        <span class="text-emerald-700 dark:text-emerald-400">تاریخ تایید: {{ $st->completed_at_jalali }}</span>
                                                    @endif
                                                    @if($st->completedBy)
                                                        <span>توسط: {{ $st->completedBy->name }}</span>
                                                    @endif
                                                </div>
                                                @if($st->notes)
                                                    <div class="text-[11px] text-slate-600 dark:text-slate-400 italic bg-white/70 dark:bg-slate-800/60 p-1.5 rounded-lg mt-1 border border-slate-200/60 dark:border-slate-700/60">
                                                        توضیحات: {{ $st->notes }}
                                                    </div>
                                                @endif
                                            </div>

                                            @if(!$isDone)
                                                <button type="button" 
                                                        wire:click="openStageModal({{ $st->id }})" 
                                                        class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm shadow-indigo-500/20 transition-all shrink-0 cursor-pointer">
                                                    تایید و ثبت انجام
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-slate-400 dark:text-slate-500 text-xs py-2">هیچ مرحله‌ای برای این سفارش تعریف نشده است.</div>
                                @endforelse
                            </div>
                        </div>

                        {{-- دستور ساخت بالینی و یادداشت‌های پزشک --}}
                        @if($detailOrder->notes)
                            <div class="p-4 rounded-2xl bg-amber-50/40 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-800/40 space-y-1.5">
                                <h4 class="font-bold text-xs text-amber-900 dark:text-amber-200 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>
                                    دستور ساخت و یادداشت‌های بالینی
                                </h4>
                                <p class="text-xs text-amber-900/80 dark:text-amber-200/80 leading-relaxed whitespace-pre-line">
                                    {{ $detailOrder->notes }}
                                </p>
                            </div>
                        @endif

                    {{-- تب ۲: نقشه دندانی و واحدها --}}
                    @elseif($detailModalTab === 'dental_chart')
                        <div x-data="labDetailToothViewer('{{ $detailOrder->teeth_numbers }}')" class="space-y-6">
                            
                            {{-- کارت نقشه دندانی گرافیکی با هایلایت دندان‌ها --}}
                            <div class="p-5 rounded-3xl bg-slate-50/80 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/80 shadow-xs space-y-4">
                                <div class="flex items-center justify-between gap-4 flex-wrap border-b border-slate-200/80 dark:border-slate-700/80 pb-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                            🦷
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-sm text-gray-900 dark:text-white">نقشه دندانی گرافیکی سفارش (Dental Anatomy Chart)</h4>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400">دندان‌های مشخص شده برای این سفارش با رنگ شاخص نیلی هایلایت شده‌اند</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                            مجموع: <span x-text="teeth.length"></span> واحد دندانی
                                        </span>
                                    </div>
                                </div>

                                {{-- کامپوننت چارت دندانی در کادر متناسب و تمیز --}}
                                <div class="max-w-xl mx-auto py-2">
                                    <x-booking::dental-chart />
                                </div>
                            </div>

                            {{-- کارت‌های تفکیک چهار چارک پالمر و FDI --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- فک بالا راست (UR) --}}
                                <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 space-y-2.5">
                                    <div class="flex items-center justify-between border-b border-gray-150 dark:border-gray-700 pb-2">
                                        <h5 class="font-bold text-xs text-gray-900 dark:text-white flex items-center gap-1.5">
                                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                            فک بالا - سمت راست
                                        </h5>
                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400" x-text="getQuadrantTeeth('UR').length + ' دندان'"></span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 min-h-[36px] items-center">
                                        <template x-for="t in getQuadrantTeeth('UR')" :key="t.id">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs font-bold bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-500/15 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-500/30 shadow-2xs transition-colors cursor-default"
                                                  :title="t.name + ' (FDI: ' + t.fdi + ')'"
                                                  x-text="t.num">
                                            </span>
                                        </template>
                                        <template x-if="getQuadrantTeeth('UR').length === 0">
                                            <span class="text-[11px] text-slate-400 dark:text-slate-500 italic">دندانی در این بخش انتخاب نشده است.</span>
                                        </template>
                                    </div>
                                </div>

                                {{-- فک بالا چپ (UL) --}}
                                <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 space-y-2.5">
                                    <div class="flex items-center justify-between border-b border-gray-150 dark:border-gray-700 pb-2">
                                        <h5 class="font-bold text-xs text-gray-900 dark:text-white flex items-center gap-1.5">
                                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                            فک بالا - سمت چپ
                                        </h5>
                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400" x-text="getQuadrantTeeth('UL').length + ' دندان'"></span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 min-h-[36px] items-center">
                                        <template x-for="t in getQuadrantTeeth('UL')" :key="t.id">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs font-bold bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-500/15 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-500/30 shadow-2xs transition-colors cursor-default"
                                                  :title="t.name + ' (FDI: ' + t.fdi + ')'"
                                                  x-text="t.num">
                                            </span>
                                        </template>
                                        <template x-if="getQuadrantTeeth('UL').length === 0">
                                            <span class="text-[11px] text-slate-400 dark:text-slate-500 italic">دندانی در این بخش انتخاب نشده است.</span>
                                        </template>
                                    </div>
                                </div>

                                {{-- فک پایین راست (LR) --}}
                                <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 space-y-2.5">
                                    <div class="flex items-center justify-between border-b border-gray-150 dark:border-gray-700 pb-2">
                                        <h5 class="font-bold text-xs text-gray-900 dark:text-white flex items-center gap-1.5">
                                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                            فک پایین - سمت راست
                                        </h5>
                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400" x-text="getQuadrantTeeth('LR').length + ' دندان'"></span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 min-h-[36px] items-center">
                                        <template x-for="t in getQuadrantTeeth('LR')" :key="t.id">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs font-bold bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-500/15 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-500/30 shadow-2xs transition-colors cursor-default"
                                                  :title="t.name + ' (FDI: ' + t.fdi + ')'"
                                                  x-text="t.num">
                                            </span>
                                        </template>
                                        <template x-if="getQuadrantTeeth('LR').length === 0">
                                            <span class="text-[11px] text-slate-400 dark:text-slate-500 italic">دندانی در این بخش انتخاب نشده است.</span>
                                        </template>
                                    </div>
                                </div>

                                {{-- فک پایین چپ (LL) --}}
                                <div class="p-4 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 space-y-2.5">
                                    <div class="flex items-center justify-between border-b border-gray-150 dark:border-gray-700 pb-2">
                                        <h5 class="font-bold text-xs text-gray-900 dark:text-white flex items-center gap-1.5">
                                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                            فک پایین - سمت چپ
                                        </h5>
                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400" x-text="getQuadrantTeeth('LL').length + ' دندان'"></span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 min-h-[36px] items-center">
                                        <template x-for="t in getQuadrantTeeth('LL')" :key="t.id">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs font-bold bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-500/15 dark:hover:bg-indigo-500/25 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-500/30 shadow-2xs transition-colors cursor-default"
                                                  :title="t.name + ' (FDI: ' + t.fdi + ')'"
                                                  x-text="t.num">
                                            </span>
                                        </template>
                                        <template x-if="getQuadrantTeeth('LL').length === 0">
                                            <span class="text-[11px] text-slate-400 dark:text-slate-500 italic">دندانی در این بخش انتخاب نشده است.</span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                    {{-- تب ۳: لاگ‌های پیگیری روزانه --}}
                    @elseif($detailModalTab === 'logs')
                        <div class="p-5 rounded-2xl bg-white dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 shadow-xs space-y-4">
                            <div class="flex items-center justify-between border-b border-gray-150 dark:border-gray-700 pb-3">
                                <h4 class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                    تاریخچه کامل پیگیری‌ها و تماس‌های ثبت‌شده با لابراتوار
                                </h4>
                            </div>

                            @if($detailOrder->dailyLogs && $detailOrder->dailyLogs->count() > 0)
                                <div class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                    @foreach($detailOrder->dailyLogs->sortByDesc('created_at') as $log)
                                        <div class="py-3 flex items-start justify-between gap-4 text-xs">
                                            <div class="space-y-1">
                                                <div class="font-bold text-gray-900 dark:text-white text-sm">{{ $log->followup_result }}</div>
                                                <div class="flex items-center gap-3 text-[11px] text-slate-500 dark:text-slate-400">
                                                    <span>ثبت شده توسط: <strong>{{ $log->operator?->name ?: 'سیستم' }}</strong></span>
                                                    @if($log->stage)
                                                        <span>مرحله: {{ $log->stage->stage_title }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 shrink-0">
                                                {{ $log->log_date_jalali ?: $log->created_at_jalali }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="py-8 text-center text-slate-400 dark:text-slate-500 text-xs">
                                    هنوز هیچ لاگ پیگیری برای این سفارش ثبت نشده است.
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- فوتر مودال جزئیات --}}
                <div class="p-5 border-t border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/60 flex items-center justify-end">
                    <button type="button" wire:click="closeDetailModal" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all cursor-pointer">بستن پنجره</button>
                </div>
            </div>
        </div>
    @endif

    {{-- کدهای Alpine.js جهت نقشه دندانی --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('labDetailToothViewer', (rawTeethStr) => ({
                teeth: [],

                init() {
                    if (!rawTeethStr) {
                        this.teeth = [];
                        return;
                    }
                    const parts = String(rawTeethStr).split(/[^0-9]+/).map(s => parseInt(s.trim())).filter(n => !isNaN(n) && n >= 1 && n <= 28);
                    this.teeth = [...new Set(parts)].sort((a, b) => a - b);
                },

                getToothLabel(id) {
                    const mapping = {
                        1:  { num: 7, fdi: 17, pos: 'UR', name: 'مولر دوم بالا راست' },
                        2:  { num: 6, fdi: 16, pos: 'UR', name: 'مولر اول بالا راست' },
                        3:  { num: 5, fdi: 15, pos: 'UR', name: 'پری‌مولر دوم بالا راست' },
                        4:  { num: 4, fdi: 14, pos: 'UR', name: 'پری‌مولر اول بالا راست' },
                        5:  { num: 3, fdi: 13, pos: 'UR', name: 'کانین (نیش) بالا راست' },
                        6:  { num: 2, fdi: 12, pos: 'UR', name: 'لترال بالا راست' },
                        7:  { num: 1, fdi: 11, pos: 'UR', name: 'سنترال بالا راست' },
                        8:  { num: 1, fdi: 21, pos: 'UL', name: 'سنترال بالا چپ' },
                        9:  { num: 2, fdi: 22, pos: 'UL', name: 'لترال بالا چپ' },
                        10: { num: 3, fdi: 23, pos: 'UL', name: 'کانین (نیش) بالا چپ' },
                        11: { num: 4, fdi: 24, pos: 'UL', name: 'پری‌مولر اول بالا چپ' },
                        12: { num: 5, fdi: 25, pos: 'UL', name: 'پری‌مولر دوم بالا چپ' },
                        13: { num: 6, fdi: 26, pos: 'UL', name: 'مولر اول بالا چپ' },
                        14: { num: 7, fdi: 27, pos: 'UL', name: 'مولر دوم بالا چپ' },
                        15: { num: 7, fdi: 47, pos: 'LR', name: 'مولر دوم پایین راست' },
                        16: { num: 6, fdi: 46, pos: 'LR', name: 'مولر اول پایین راست' },
                        17: { num: 5, fdi: 45, pos: 'LR', name: 'پری‌مولر دوم پایین راست' },
                        18: { num: 4, fdi: 44, pos: 'LR', name: 'پری‌مولر اول پایین راست' },
                        19: { num: 3, fdi: 43, pos: 'LR', name: 'کانین (نیش) پایین راست' },
                        20: { num: 2, fdi: 42, pos: 'LR', name: 'لترال پایین راست' },
                        21: { num: 1, fdi: 41, pos: 'LR', name: 'سنترال پایین راست' },
                        22: { num: 1, fdi: 31, pos: 'LL', name: 'سنترال پایین چپ' },
                        23: { num: 2, fdi: 32, pos: 'LL', name: 'لترال پایین چپ' },
                        24: { num: 3, fdi: 33, pos: 'LL', name: 'کانین (نیش) پایین چپ' },
                        25: { num: 4, fdi: 34, pos: 'LL', name: 'پری‌مولر اول پایین چپ' },
                        26: { num: 5, fdi: 35, pos: 'LL', name: 'پری‌مولر دوم پایین چپ' },
                        27: { num: 6, fdi: 36, pos: 'LL', name: 'مولر اول پایین چپ' },
                        28: { num: 7, fdi: 37, pos: 'LL', name: 'مولر دوم پایین چپ' }
                    };
                    return mapping[id] ?? { num: id, fdi: id, pos: 'UR', name: 'دندان شماره ' + id };
                },

                getQuadrantTeeth(pos) {
                    return (this.teeth || []).map(Number).filter(t => this.getToothLabel(t).pos === pos).map(t => ({
                        id: t,
                        ...this.getToothLabel(t)
                    })).sort((a,b) => {
                        if (pos === 'UR' || pos === 'LR') return b.num - a.num;
                        return a.num - b.num;
                    });
                },

                toggle(id) {},

                is(id) {
                    id = Number(id);
                    return (Array.isArray(this.teeth) && this.teeth.includes(id)) ? 'tooth-path tooth-selected' : 'tooth-path tooth-unselected';
                }
            }));
        });
    </script>
</div>
