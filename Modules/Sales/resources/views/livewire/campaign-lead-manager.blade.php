@php
    $inputClass = "w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 px-3.5 py-2.5 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors shadow-xs font-sans";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";
    $stats = $leadStats ?? [];
@endphp

<div class="space-y-5 text-right font-sans" dir="rtl">
    <!-- ==========================================
         1. MODULE HEADER & HUB SUB-NAV (مشابه /user/clients)
         ========================================== -->
    <div class="bg-white dark:bg-gray-800/90 rounded-2xl border border-gray-200/80 dark:border-gray-700/70 p-5 shadow-xs backdrop-blur-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            {{-- عنوان و اطلاعات ماژول --}}
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white flex items-center justify-center shadow-md shadow-indigo-500/20 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">
                            مدیریت لیدهای فروش
                        </h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900/50">
                            {{ $leads->total() }} لید فعال
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        پایش، غربالگری و تخصیص هوشمند لیدهای ورودی و مشتریان بالقوه
                    </p>
                </div>
            </div>

            {{-- دکمه‌های عملیات سریع بالا --}}
            <div class="flex items-center gap-2.5 flex-wrap">
                <button wire:click="openCreateModal"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 active:scale-95 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>ثبت لید جدید</span>
                </button>
            </div>
        </div>

        {{-- نوار دسترسی سریع و هاب زیرمنوها (Module Hub Sub-Nav) --}}
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between gap-3 overflow-x-auto custom-scrollbar pb-1">
            <div class="flex items-center gap-1.5 text-xs shrink-0 flex-wrap">
                <a href="{{ route('user.sales.leads.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold bg-indigo-600 text-white shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>همه لیدها</span>
                </a>

                <a href="{{ route('user.sales.cockpit') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all">
                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                    <span>میز کار فروش</span>
                </a>

                <a href="{{ route('user.sales.campaigns.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all">
                    <svg class="w-3.5 h-3.5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    <span>کمپین‌های بازاریابی</span>
                </a>

                <a href="{{ route('user.sales.pipeline') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                    <span>کانبان خط لوله</span>
                </a>

                <a href="{{ route('user.sales.reports.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all">
                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>گزارش‌های فروش</span>
                </a>
            </div>

            {{-- دکمه تاگل فیلتر پیشرفته --}}
            <button wire:click="$toggle('filterOpen')"
                    type="button"
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold border transition-all shrink-0 {{ $activeFiltersCount > 0 ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800' : 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span>فیلتر پیشرفته</span>
                @if($activeFiltersCount > 0)
                    <span class="w-4 h-4 rounded-full bg-indigo-600 text-white text-[10px] flex items-center justify-center font-black">
                        {{ $activeFiltersCount }}
                    </span>
                @endif
            </button>
        </div>

        {{-- نوار وضعیت‌های سازنده کلاینت (سریع، تعاملی و چندانتخابی) --}}
        @if(isset($clientStatuses) && $clientStatuses->isNotEmpty())
            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between gap-3 overflow-x-auto custom-scrollbar">
                <div class="flex items-center gap-2 text-xs shrink-0">
                    <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 shrink-0 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>وضعیت‌های کلاینت:</span>
                    </span>

                    @foreach($clientStatuses as $cStatus)
                        @php
                            $isSelected = in_array($cStatus->id, $selectedClientStatuses, true);
                        @endphp
                        <button type="button"
                                wire:click="toggleClientStatus({{ $cStatus->id }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border transition-all active:scale-95 shadow-2xs {{ $isSelected ? 'bg-indigo-50/90 dark:bg-indigo-950/60 border-indigo-300 dark:border-indigo-700 text-indigo-900 dark:text-indigo-200 ring-1 ring-indigo-500/20' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/60' }}">
                            <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $cStatus->color ?: '#6366f1' }}"></span>
                            <span>{{ $cStatus->label }}</span>
                            @if($isSelected)
                                <svg class="w-3 h-3 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            @endif
                        </button>
                    @endforeach

                    <button type="button"
                            wire:click="selectAllClientStatuses"
                            class="text-[11px] font-bold text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-300 px-2 py-1 transition-colors shrink-0">
                        {{ count($selectedClientStatuses) === count($clientStatuses) ? 'حالت اول' : 'همه وضعیت‌ها' }}
                    </button>
                </div>

                <a href="{{ route('user.settings.clients.statuses') }}" target="_blank" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline shrink-0 hidden md:inline-flex items-center gap-1">
                    <span>تنظیم وضعیت‌ها</span>
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        @endif
    </div>

    <!-- ==========================================
         2. TOP KPI SUMMARY CARDS
         ========================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- کل لیدها -->
        <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">کل لیدهای سیستم</span>
                <span class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white block font-sans">
                    {{ $stats['total'] ?? 0 }}
                </span>
                <span class="text-[10px] text-gray-400 dark:text-gray-500 block">مجموع ورودی‌های کمپین و مستقیم</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100/60 dark:border-indigo-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>

        <!-- لیدهای بدون کارشناس -->
        <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">نیازمند تخصیص</span>
                <span class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 block font-sans">
                    {{ $stats['unassigned'] ?? 0 }}
                </span>
                <span class="text-[10px] text-amber-600/80 dark:text-amber-400/80 block">لیدهای آزاد و بدون کارشناس</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-100/60 dark:border-amber-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
        </div>

        <!-- در حال ارزیابی و راغب -->
        <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">در حال ارزیابی و پیگیری</span>
                <span class="text-xl sm:text-2xl font-black text-sky-600 dark:text-sky-400 block font-sans">
                    {{ $stats['in_qualification'] ?? 0 }}
                </span>
                <span class="text-[10px] text-sky-600/80 dark:text-sky-400/80 block">مذاکره اولیه و سنجش پتانسیل</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-100/60 dark:border-sky-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
        </div>

        <!-- تبدیل به پرونده فروش -->
        <div class="bg-white dark:bg-gray-800/90 border border-gray-200/80 dark:border-gray-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">تبدیل به پرونده فروش</span>
                <span class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 block font-sans">
                    {{ $stats['converted'] ?? 0 }}
                </span>
                <span class="text-[10px] text-emerald-600/80 dark:text-emerald-400/80 block">فرصت‌های فروش ایجادشده</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100/60 dark:border-emerald-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- ==========================================
         3. ADVANCED FILTER PANEL (Accordion تاشو مشابه /user/clients)
         ========================================== -->
    @if($filterOpen)
        <div class="bg-white dark:bg-gray-800/95 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-xs overflow-hidden transition-all animate-fade-in">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    <h2 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                        فیلتر و جستجوی چندگانه لیدها
                    </h2>
                </div>
                @if($activeFiltersCount > 0)
                    <button wire:click="clearFilters" class="text-xs font-bold text-rose-500 hover:text-rose-700 flex items-center gap-1 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        <span>پاک کردن همه فیلترها</span>
                    </button>
                @endif
            </div>

            <div class="p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    {{-- جستجوی متنی --}}
                    <div class="lg:col-span-2">
                        <label class="{{ $labelClass }}">جستجوی متن</label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                   placeholder="نام، شماره، کدملی، کد پرونده یا نام‌کاربری..."
                                   class="{{ $inputClass }} pl-9 pr-3.5">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </div>
                        </div>
                    </div>

                    {{-- وضعیت لید --}}
                    <div>
                        <label class="{{ $labelClass }}">وضعیت لید</label>
                        <select wire:model.live="filterStatus" class="{{ $inputClass }}">
                            <option value="all">همه وضعیت‌ها</option>
                            <option value="pending">در انتظار تماس</option>
                            <option value="contacted">تماس گرفته شده</option>
                            <option value="responded">پاسخ‌داده‌شده / راغب</option>
                            <option value="converted">تبدیل شده به پرونده</option>
                            <option value="lost">رد شده / ناموفق</option>
                        </select>
                    </div>

                    {{-- منبع لید --}}
                    <div>
                        <label class="{{ $labelClass }}">منبع ورودی</label>
                        <select wire:model.live="filterSource" class="{{ $inputClass }}">
                            <option value="all">همه منابع</option>
                            <option value="campaign">کمپین‌های بازاریابی</option>
                            <option value="direct">مشتریان مستقیم</option>
                        </select>
                    </div>

                    {{-- کارشناس مسئول --}}
                    <div>
                        <label class="{{ $labelClass }}">کارشناس مسئول</label>
                        <select wire:model.live="filterAgent" class="{{ $inputClass }}">
                            <option value="all">همه کارشناسان</option>
                            <option value="unassigned">بدون کارشناس (آزاد)</option>
                            @foreach($salesAgents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- کمپین تبلیغاتی --}}
                    <div>
                        <label class="{{ $labelClass }}">کمپین بازاریابی</label>
                        <select wire:model.live="filterCampaign" class="{{ $inputClass }}">
                            <option value="all">همه کمپین‌ها</option>
                            @foreach($campaigns as $camp)
                                <option value="{{ $camp->id }}">{{ $camp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- انتخاب چندگانه وضعیت‌های کلاینت (متصل به /user/settings/clients/statuses) --}}
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700/60">
                    <div class="flex flex-wrap items-center justify-between gap-2.5 mb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                وضعیت‌های فعال کلاینت (فیلتر چندگانه وضعیت‌ساز):
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="selectAllClientStatuses" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ count($selectedClientStatuses) === count($clientStatuses) ? 'بازنشانی به حالت پیش‌فرض' : 'انتخاب همه وضعیت‌ها' }}
                            </button>
                            <a href="{{ route('user.settings.clients.statuses') }}" target="_blank" class="text-[11px] text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 inline-flex items-center gap-1">
                                <span>مدیریت وضعیت‌ها</span>
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        @foreach($clientStatuses as $cStatus)
                            @php
                                $isActive = in_array($cStatus->id, $selectedClientStatuses, true);
                            @endphp
                            <button type="button"
                                    wire:click="toggleClientStatus({{ $cStatus->id }})"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold border transition-all active:scale-95 shadow-2xs {{ $isActive ? 'bg-white dark:bg-gray-900 border-indigo-500 text-indigo-700 dark:text-indigo-300 ring-2 ring-indigo-500/20' : 'bg-gray-50/70 dark:bg-gray-800/60 border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:bg-white dark:hover:bg-gray-800' }}">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $cStatus->color ?: '#6366f1' }}"></span>
                                <span>{{ $cStatus->label }}</span>
                                @if($isActive)
                                    <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ==========================================
         4. FLOATING BULK ACTIONS BAR (پنل عملیات گروهی)
         ========================================== -->
    @if(count($selectedKeys) > 0)
        <div class="bg-indigo-50/90 dark:bg-gray-800/95 border border-indigo-200/90 dark:border-indigo-500/30 rounded-2xl p-4 shadow-sm flex flex-col lg:flex-row items-center justify-between gap-4 transition-all animate-fade-in" dir="rtl">
            <div class="flex items-center gap-3">
                <span class="w-7 h-7 rounded-xl bg-indigo-600 dark:bg-indigo-500 text-white flex items-center justify-center font-black text-xs font-sans shrink-0 shadow-xs">
                    {{ count($selectedKeys) }}
                </span>
                <span class="text-xs font-bold text-indigo-950 dark:text-indigo-200">
                    لید برای عملیات گروهی انتخاب شده است.
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto justify-end">
                <!-- تخصیص به کارشناس -->
                <div class="flex items-center gap-1.5 bg-white dark:bg-gray-900/90 p-1 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xs">
                    <select wire:model="assignToUserId" class="text-xs bg-transparent border-0 rounded-lg px-2.5 py-1 text-gray-800 dark:text-gray-200 focus:ring-0 focus:outline-hidden">
                        <option value="" class="bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">انتخاب کارشناس...</option>
                        @foreach($salesAgents as $agent)
                            <option value="{{ $agent->id }}" class="bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">{{ $agent->name }}</option>
                        @endforeach
                    </select>
                    <button wire:click="bulkAssign" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white rounded-lg text-xs font-bold transition-all shadow-xs active:scale-95">
                        تخصیص
                    </button>
                </div>

                <!-- افزودن به کمپین -->
                <div class="flex items-center gap-1.5 bg-white dark:bg-gray-900/90 p-1 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xs">
                    <select wire:model="assignToCampaignId" class="text-xs bg-transparent border-0 rounded-lg px-2.5 py-1 text-gray-800 dark:text-gray-200 focus:ring-0 focus:outline-hidden">
                        <option value="" class="bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">انتخاب کمپین...</option>
                        @foreach($campaigns as $camp)
                            <option value="{{ $camp->id }}" class="bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">{{ $camp->name }}</option>
                        @endforeach
                    </select>
                    <button wire:click="bulkAddToCampaign" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white rounded-lg text-xs font-bold transition-all shadow-xs active:scale-95">
                        افزودن
                    </button>
                </div>

                <!-- تغییر وضعیت -->
                <div class="flex items-center gap-1.5 bg-white dark:bg-gray-900/90 p-1 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xs">
                    <select wire:model="bulkStatus" class="text-xs bg-transparent border-0 rounded-lg px-2.5 py-1 text-gray-800 dark:text-gray-200 focus:ring-0 focus:outline-hidden">
                        <option value="" class="bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">تغییر وضعیت به...</option>
                        <option value="pending" class="bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">در انتظار تماس</option>
                        <option value="contacted" class="bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">تماس گرفته شده</option>
                        <option value="responded" class="bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">پاسخ‌داده‌شده / راغب</option>
                        <option value="converted" class="bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">تبدیل به پرونده</option>
                        <option value="lost" class="bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">رد شده / ناموفق</option>
                    </select>
                    <button wire:click="bulkUpdateStatus" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 dark:bg-sky-600 dark:hover:bg-sky-500 text-white rounded-lg text-xs font-bold transition-all shadow-xs active:scale-95">
                        ثبت وضعیت
                    </button>
                </div>

                <!-- تبدیل به پرونده فروش -->
                <button wire:click="bulkConvertToDeals" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition-all shadow-xs active:scale-95">
                    تبدیل به پرونده
                </button>

                <!-- حذف گروهی -->
                <button onclick="confirm('آیا از حذف گروهی لیدهای انتخاب شده اطمینان دارید؟') || event.stopImmediatePropagation()"
                        wire:click="bulkDelete" 
                        class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 text-white rounded-xl text-xs font-bold transition-all shadow-xs active:scale-95">
                    حذف گروهی
                </button>
            </div>
        </div>
    @endif

    <!-- ==========================================
         5. RICH LEADS DATA TABLE
         ========================================== -->
    <div class="bg-white dark:bg-gray-800/90 rounded-2xl shadow-xs border border-gray-200/80 dark:border-gray-700/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs whitespace-nowrap">
                <thead class="bg-gray-50/80 dark:bg-gray-900/60 text-gray-500 dark:text-gray-400 font-bold border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">
                            <input type="checkbox" 
                                   wire:model.live="selectAll"
                                   class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 cursor-pointer">
                        </th>
                        <th class="py-3.5 px-4">مشخصات لید</th>
                        <th class="py-3.5 px-4">اطلاعات ارتباطی</th>
                        <th class="py-3.5 px-4">منبع لید</th>
                        <th class="py-3.5 px-4">کارشناس مسئول</th>
                        <th class="py-3.5 px-4">وضعیت مرحله لید</th>
                        <th class="py-3.5 px-4">تاریخ ثبت</th>
                        <th class="py-3.5 px-4 text-center">عملیات</th>
                    </tr>
                </thead>
                <tbody wire:loading.class="opacity-50 pointer-events-none" class="divide-y divide-gray-100 dark:divide-gray-700/60 text-gray-900 dark:text-gray-100 transition-opacity duration-200 font-sans">
                    @forelse($leads as $lead)
                        @php
                            $isSelected = in_array($lead->key, $selectedKeys, true);
                        @endphp
                        <tr class="transition-colors {{ $isSelected ? 'bg-indigo-50/70 dark:bg-indigo-950/40 ring-1 ring-inset ring-indigo-500/30' : ($lead->is_converted ? 'bg-emerald-50/25 dark:bg-emerald-950/15 hover:bg-gray-50/80 dark:hover:bg-gray-900/30' : 'hover:bg-gray-50/80 dark:hover:bg-gray-900/30') }}">
                            <!-- Checkbox -->
                            <td class="py-3.5 px-4 text-center">
                                <input type="checkbox" value="{{ $lead->key }}" wire:model.live="selectedKeys"
                                       class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 cursor-pointer">
                            </td>

                            <!-- مشخصات لید (آواتار + نام) -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100/80 dark:border-indigo-800/50 flex items-center justify-center font-black text-sm text-indigo-600 dark:text-indigo-400 shadow-xs shrink-0">
                                        {{ mb_substr($lead->name ?: 'ل', 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-gray-900 dark:text-white block">
                                            {{ $lead->name }}
                                        </span>
                                        <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                                            @if(!empty($lead->case_number))
                                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-900/40 font-sans" title="کد پرونده">
                                                    پرونده: {{ $lead->case_number }}
                                                </span>
                                            @endif
                                            @if(!empty($lead->national_code))
                                                <span class="text-[9px] font-medium px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 font-sans" title="کد ملی">
                                                    کدملی: {{ $lead->national_code }}
                                                </span>
                                            @endif
                                            @if(!empty($lead->username))
                                                <span class="text-[10px] text-gray-400 dark:text-gray-500 font-sans" dir="ltr">@<span>{{ $lead->username }}</span></span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- اطلاعات تماس -->
                            <td class="py-3.5 px-4">
                                <div class="space-y-1">
                                    @if($lead->phone)
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-gray-700 dark:text-gray-300 font-sans" dir="ltr">
                                                {{ $lead->phone }}
                                            </span>
                                            <button wire:click="initiateVoipCall('{{ $lead->phone }}', {{ $lead->client_id ?? 'null' }}, {{ $lead->type === 'camp' ? $lead->id : 'null' }})"
                                                    title="تماس صوتی VoIP"
                                                    class="p-1 rounded-lg text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/30 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                </svg>
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-[11px]">—</span>
                                    @endif

                                    @if($lead->email)
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500 block" dir="ltr">{{ $lead->email }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- منبع لید -->
                            <td class="py-3.5 px-4">
                                @if($lead->source_type === 'campaign')
                                    <span class="px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 text-[10px] font-bold inline-flex items-center gap-1.5 border border-purple-100/60 dark:border-purple-900/40">
                                        <svg class="w-3 h-3 text-purple-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                        </svg>
                                        <span>{{ $lead->source_label }}</span>
                                    </span>
                                @else
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 text-[10px] font-bold inline-flex items-center gap-1.5 border border-indigo-100/60 dark:border-indigo-900/40">
                                            <svg class="w-3 h-3 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <span>{{ $lead->source_label }}</span>
                                        </span>
                                        @if(!empty($lead->client_status_label))
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-bold border border-gray-200/80 dark:border-gray-700/80 bg-gray-50/90 dark:bg-gray-800 text-gray-700 dark:text-gray-300 inline-flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full shrink-0" style="background-color: {{ $lead->client_status_color ?? '#6366f1' }}"></span>
                                                <span>{{ $lead->client_status_label }}</span>
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <!-- کارشناس مسئول -->
                            <td class="py-3.5 px-4">
                                <select wire:change="assignSingleAgent('{{ $lead->type }}', {{ $lead->id }}, $event.target.value)"
                                        class="text-[11px] bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg py-1 px-2 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-indigo-500 font-sans cursor-pointer">
                                    <option value="">بدون کارشناس</option>
                                    @foreach($salesAgents as $agent)
                                        <option value="{{ $agent->id }}" {{ $lead->assigned_to == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <!-- وضعیت مرحله لید -->
                            <td class="py-3.5 px-4">
                                <select 
                                    wire:change="updateLeadStatus('{{ $lead->type }}', {{ $lead->id }}, $event.target.value)"
                                    class="text-[11px] font-bold rounded-lg border py-1 px-2.5 transition-colors focus:ring-1 focus:ring-indigo-500 font-sans cursor-pointer {{ 
                                        match($lead->status) {
                                            'pending' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60',
                                            'contacted' => 'bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800/60',
                                            'responded' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60',
                                            'converted' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60',
                                            'lost' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60',
                                            default => 'bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700',
                                        }
                                    }}"
                                    {{ $lead->is_converted ? 'disabled' : '' }}
                                >
                                    <option value="pending" {{ $lead->status === 'pending' ? 'selected' : '' }}>در انتظار تماس</option>
                                    <option value="contacted" {{ $lead->status === 'contacted' ? 'selected' : '' }}>تماس گرفته شده</option>
                                    <option value="responded" {{ $lead->status === 'responded' ? 'selected' : '' }}>پاسخ‌داده‌شده / راغب</option>
                                    <option value="converted" {{ $lead->status === 'converted' ? 'selected' : '' }} disabled>تبدیل به پرونده</option>
                                    <option value="lost" {{ $lead->status === 'lost' ? 'selected' : '' }}>رد شده / ناموفق</option>
                                </select>
                            </td>

                            <!-- تاریخ ثبت -->
                            <td class="py-3.5 px-4 text-gray-500 dark:text-gray-400 font-sans" dir="ltr">
                                {{ $lead->date ? \Morilog\Jalali\Jalalian::fromDateTime($lead->date)->format('Y/m/d') : '—' }}
                            </td>

                            <!-- عملیات -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <!-- دکمه تبدیل به پرونده فروش -->
                                    @if(!$lead->is_converted)
                                        <button wire:click="convertToDeal('{{ $lead->type }}', {{ $lead->id }})" 
                                                title="تبدیل به پرونده فروش"
                                                class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 transition-colors px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/30 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/40 flex items-center gap-1 text-[11px] font-bold border border-emerald-100 dark:border-emerald-900/40">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            <span>تبدیل به پرونده</span>
                                        </button>
                                    @else
                                        <span class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1 text-[10px] bg-emerald-50 dark:bg-emerald-950/30 px-2 py-0.5 rounded-lg border border-emerald-100 dark:border-emerald-900/40">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            پرونده فعال
                                        </span>
                                    @endif

                                    <!-- حذف لید -->
                                    <button onclick="confirm('آیا از حذف این لید اطمینان دارید؟') || event.stopImmediatePropagation()" 
                                            wire:click="deleteLead('{{ $lead->type }}', {{ $lead->id }})" 
                                            title="حذف لید"
                                            class="text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 p-1.5 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-800 text-gray-400 flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400">هیچ لیدی با فیلترهای انتخابی یافت نشد.</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">می‌توانید فیلترها را پاک کرده یا لید جدیدی ثبت کنید.</p>
                                    <button wire:click="openCreateModal" class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs transition-colors">
                                        ＋ ثبت لید جدید
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($leads->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700/80 bg-gray-50/40 dark:bg-gray-900/30">
                {{ $leads->links() }}
            </div>
        @endif
    </div>

    <!-- ==========================================
         6. CREATE LEAD MODAL (مودال ثبت لید جدید)
         ========================================== -->
    <template x-teleport="body">
        <div x-data="{ show: @entangle('showCreateModal') }"
             x-init="$watch('show', value => { document.body.style.overflow = value ? 'hidden' : '' })">
            <div x-show="show" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-950/60 dark:bg-gray-950/80 backdrop-blur-sm transition-opacity" x-on:click="show = false"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    
                    <div class="inline-block align-bottom relative bg-white dark:bg-gray-800 rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <!-- Modal Header -->
                            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                                <h3 class="text-base font-bold text-gray-900 dark:text-white inline-flex items-center gap-2" id="modal-title">
                                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                    </svg>
                                    <span>ثبت لید جدید در سیستم</span>
                                </h3>
                                <button x-on:click="show = false" type="button" class="text-gray-400 hover:text-rose-500 bg-gray-50 hover:bg-rose-50 dark:bg-gray-900/50 dark:hover:bg-rose-500/20 p-2 rounded-xl transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <form wire:submit.prevent="saveNewLead" class="space-y-4">
                                <!-- نام لید -->
                                <div>
                                    <label class="{{ $labelClass }}">نام و نام خانوادگی <span class="text-rose-500">*</span></label>
                                    <input type="text" wire:model="new_name" class="{{ $inputClass }}" placeholder="مثال: علی محمدی">
                                    @error('new_name') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- تماس و ایمیل -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="{{ $labelClass }}">شماره تماس / موبایل</label>
                                        <input type="text" wire:model="new_phone" class="{{ $inputClass }}" placeholder="۰۹۱۲..." dir="ltr">
                                        @error('new_phone') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="{{ $labelClass }}">پست الکترونیک (ایمیل)</label>
                                        <input type="email" wire:model="new_email" class="{{ $inputClass }}" placeholder="email@example.com" dir="ltr">
                                        @error('new_email') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- منبع و کمپین -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="{{ $labelClass }}">نوع منبع لید <span class="text-rose-500">*</span></label>
                                        <select wire:model.live="new_source_type" class="{{ $inputClass }}">
                                            <option value="direct">مشتری مستقیم</option>
                                            <option value="campaign">کمپین بازاریابی</option>
                                        </select>
                                    </div>
                                    @if($new_source_type === 'campaign')
                                        <div>
                                            <label class="{{ $labelClass }}">انتخاب کمپین <span class="text-rose-500">*</span></label>
                                            <select wire:model="new_campaign_id" class="{{ $inputClass }}">
                                                <option value="">انتخاب کمپین...</option>
                                                @foreach($campaigns as $camp)
                                                    <option value="{{ $camp->id }}">{{ $camp->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('new_campaign_id') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                    @else
                                        <div>
                                            <label class="{{ $labelClass }}">کارشناس مسئول</label>
                                            <select wire:model="new_assigned_to" class="{{ $inputClass }}">
                                                <option value="">تخصیص نیافته (آزاد)</option>
                                                @foreach($salesAgents as $agent)
                                                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                </div>

                                <!-- وضعیت و کارشناس در صورت کمپین -->
                                @if($new_source_type === 'campaign')
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="{{ $labelClass }}">کارشناس مسئول</label>
                                            <select wire:model="new_assigned_to" class="{{ $inputClass }}">
                                                <option value="">تخصیص نیافته (آزاد)</option>
                                                @foreach($salesAgents as $agent)
                                                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="{{ $labelClass }}">وضعیت اولیه <span class="text-rose-500">*</span></label>
                                            <select wire:model="new_status" class="{{ $inputClass }}">
                                                <option value="pending">در انتظار تماس</option>
                                                <option value="contacted">تماس گرفته شده</option>
                                                <option value="responded">پاسخ‌داده‌شده / راغب</option>
                                            </select>
                                        </div>
                                    </div>
                                @else
                                    <div>
                                        <label class="{{ $labelClass }}">وضعیت اولیه <span class="text-rose-500">*</span></label>
                                        <select wire:model="new_status" class="{{ $inputClass }}">
                                            <option value="pending">در انتظار تماس</option>
                                            <option value="contacted">تماس گرفته شده</option>
                                            <option value="responded">پاسخ‌داده‌شده / راغب</option>
                                        </select>
                                    </div>
                                @endif

                                <!-- یادداشت -->
                                <div>
                                    <label class="{{ $labelClass }}">توضیح یا یادداشت لید</label>
                                    <textarea wire:model="new_note" rows="2" class="{{ $inputClass }} resize-none" placeholder="نکاتی درباره این لید یا نحوه آشنایی..."></textarea>
                                    @error('new_note') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- دکمه‌ها -->
                                <div class="flex justify-end gap-2.5 pt-4 border-t border-gray-200 dark:border-gray-700 mt-4">
                                    <button type="button" x-on:click="show = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-colors">
                                        انصراف
                                    </button>
                                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition-colors active:scale-95">
                                        ثبت لید
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
