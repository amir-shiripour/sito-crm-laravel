@extends('layouts.user')

@section('content')
    @php
        $inputClass = "w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:bg-gray-900 [&>option]:bg-white [&>option]:text-gray-900 [&>option]:dark:bg-gray-800 [&>option]:dark:text-gray-100";
        $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";

        $activeFiltersCount = count(array_filter(
            request()->only(['search', 'holder_type', 'status', 'balance_status', 'min_balance', 'max_balance', 'sort', 'per_page']),
            fn($v, $k) => !empty($v) && !($k === 'sort' && $v === 'latest') && !($k === 'per_page' && $v == '20'),
            ARRAY_FILTER_USE_BOTH
        ));
        $isFiltered = $activeFiltersCount > 0;
    @endphp

    <div class="w-full mx-auto px-4 py-8 space-y-6" x-data="walletManager()">

        {{-- Alerts --}}
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-sm font-bold flex items-center justify-between shadow-xs dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/80">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 text-rose-800 border border-rose-200 text-sm font-bold flex items-center justify-between shadow-xs dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/80">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        {{-- Main Header --}}
        <div class="bg-white dark:bg-gray-800/90 rounded-2xl border border-gray-200/80 dark:border-gray-700/70 p-5 shadow-xs backdrop-blur-sm">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white flex items-center justify-center shadow-md shadow-indigo-500/20 shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">
                                مدیریت کیف پول‌ها
                            </h1>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900/50">
                                {{ $wallets->total() }} رکورد یافت‌شده
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>مدیریت حساب‌ها، شارژ و برداشت اعتبارات (واحد پول: {{ $currencyLabel ?? 'تومان' }})</span>
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    @can('wallet.transactions.view')
                        <a href="{{ route('user.wallet.transactions.index') }}"
                           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-white hover:bg-gray-50 text-gray-700 hover:text-gray-900 border border-gray-200 hover:border-gray-300 shadow-xs transition-all active:scale-95 dark:bg-gray-700/80 dark:hover:bg-gray-700 dark:border-gray-600 dark:hover:border-gray-500 dark:text-gray-100 dark:hover:text-white">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            <span>دفتر کل تراکنش‌ها</span>
                        </a>
                    @endcan

                    @can('wallet.withdraw')
                        <button type="button" @click="openModal('withdraw')"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-rose-600 text-white hover:bg-rose-700 shadow-md shadow-rose-500/20 transition-all active:scale-95">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                            <span>کسر / برداشت</span>
                        </button>
                    @endcan

                    @can('wallet.deposit')
                        <button type="button" @click="openModal('deposit')"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-md shadow-emerald-500/20 transition-all active:scale-95">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                            <span>شارژ / واریز</span>
                        </button>
                    @endcan
                </div>
            </div>
        </div>

        {{-- KPI Overview Cards --}}
        @if(isset($stats))
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Card 1: Total System Balance --}}
                <div class="p-5 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-md shadow-indigo-500/10 relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="flex items-center justify-between text-indigo-100">
                            <span class="text-xs font-bold">کل موجودی در گردش سیستم</span>
                            <span class="p-2 rounded-xl bg-white/10 backdrop-blur-xs">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </span>
                        </div>
                        <div class="mt-3 flex items-baseline gap-1.5">
                            <span class="text-2xl font-black tabular-nums">{{ number_format($stats['total_balance']) }}</span>
                            <span class="text-xs font-bold text-indigo-200">{{ $currencyLabel }}</span>
                        </div>
                        <span class="mt-1.5 block text-[11px] text-indigo-100/90">
                            در {{ number_format($stats['positive_count']) }} کیف پول دارای اعتبار
                        </span>
                    </div>
                    <div class="absolute -left-4 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                </div>

                {{-- Card 2: Clients Balance --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 shadow-xs">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                        <span class="text-xs font-bold">موجودی کلاینت‌ها (مشتریان)</span>
                        <span class="p-2 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black text-blue-600 dark:text-blue-400 tabular-nums">{{ number_format($stats['clients_balance']) }}</span>
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $currencyLabel }}</span>
                    </div>
                    <span class="mt-1.5 block text-[11px] text-gray-500 dark:text-gray-400">
                        مجموع سپرده‌ها و اعتبارات مشتریان
                    </span>
                </div>

                {{-- Card 3: Users Balance --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 shadow-xs">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                        <span class="text-xs font-bold">موجودی کاربران سیستم</span>
                        <span class="p-2 rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-950/40 dark:text-purple-300">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black text-purple-600 dark:text-purple-400 tabular-nums">{{ number_format($stats['users_balance']) }}</span>
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $currencyLabel }}</span>
                    </div>
                    <span class="mt-1.5 block text-[11px] text-gray-500 dark:text-gray-400">
                        اعتبارات کارمندان و کاربران داخلی
                    </span>
                </div>

                {{-- Card 4: Total Wallets Counts --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 shadow-xs">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                        <span class="text-xs font-bold">تعداد کل کیف پول‌ها</span>
                        <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" /></svg>
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black text-gray-900 dark:text-white tabular-nums">{{ number_format($stats['total_wallets']) }}</span>
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">حساب</span>
                    </div>
                    <div class="mt-1.5 flex items-center gap-2 text-[11px]">
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ $stats['active_wallets'] }} فعال</span>
                        <span class="text-gray-300 dark:text-gray-600">•</span>
                        <span class="text-rose-600 dark:text-rose-400 font-bold">{{ $stats['inactive_wallets'] }} مسدود</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Advanced Filter & Search Hub --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-xs overflow-hidden">
            
            {{-- Quick Search & Filter Toggle Bar --}}
            <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                
                {{-- Fast Search Input with Immediate Submit --}}
                <form method="GET" action="{{ route('user.wallet.index') }}" class="flex-1 flex items-center gap-2 max-w-xl">
                    {{-- Preserve existing active filters if searching quickly --}}
                    @foreach(request()->except(['search', 'page']) as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach

                    <div class="relative flex-1">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="جستجوی هوشمند (نام کیف پول، اسلاگ، نام دارنده، شماره تماس، کد ملی، شناسه)..."
                               class="{{ $inputClass }} pl-9 pr-4 py-2.5 font-medium">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                    </div>
                    <button type="submit"
                            class="px-4 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm shadow-indigo-600/20 transition active:scale-95 shrink-0">
                        جستجو
                    </button>
                </form>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2 shrink-0">
                    {{-- Advanced Filter Toggle Button --}}
                    <button type="button" @click="filterOpen = !filterOpen"
                            class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold border transition-all shrink-0
                            {{ $isFiltered ? 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/50 dark:text-indigo-300 dark:border-indigo-800' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span>فیلترهای پیشرفته</span>
                        @if($activeFiltersCount > 0)
                            <span class="w-4 h-4 rounded-full bg-indigo-600 text-white text-[10px] flex items-center justify-center font-black">
                                {{ $activeFiltersCount }}
                            </span>
                        @endif
                    </button>

                    @if($isFiltered)
                        <a href="{{ route('user.wallet.index') }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 border border-rose-200 dark:text-rose-400 dark:border-rose-900/60 dark:hover:bg-rose-950/40 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            <span>حذف تمام فیلترها</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Quick Filter Presets (Pills) --}}
            <div class="px-5 pb-4 pt-1 border-t border-gray-100 dark:border-gray-700/60 flex items-center gap-2 overflow-x-auto custom-scrollbar">
                <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 shrink-0 ml-1">دسترسی سریع:</span>
                
                <a href="{{ route('user.wallet.index') }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0
                   {{ !request()->hasAny(['holder_type', 'status', 'balance_status']) ? 'bg-indigo-600 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700/60 dark:text-gray-300 dark:hover:bg-gray-700 border border-gray-200/60 dark:border-gray-700' }}">
                    همه کیف پول‌ها
                </a>

                <a href="{{ route('user.wallet.index', array_merge(request()->except(['page', 'holder_type']), ['holder_type' => 'client'])) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0
                   {{ request('holder_type') === 'client' ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-900/50 border border-blue-200/60 dark:border-blue-900/40' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                    فقط کلاینت‌ها
                </a>

                <a href="{{ route('user.wallet.index', array_merge(request()->except(['page', 'holder_type']), ['holder_type' => 'user'])) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0
                   {{ request('holder_type') === 'user' ? 'bg-purple-600 text-white shadow-xs' : 'bg-purple-50 text-purple-700 hover:bg-purple-100 dark:bg-purple-950/40 dark:text-purple-300 dark:hover:bg-purple-900/50 border border-purple-200/60 dark:border-purple-900/40' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>
                    فقط کاربران سیستم
                </a>

                <a href="{{ route('user.wallet.index', array_merge(request()->except(['page', 'balance_status']), ['balance_status' => 'positive'])) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0
                   {{ request('balance_status') === 'positive' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 dark:hover:bg-emerald-900/50 border border-emerald-200/60 dark:border-emerald-900/40' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    دارای موجودی (> ۰)
                </a>

                <a href="{{ route('user.wallet.index', array_merge(request()->except(['page', 'balance_status']), ['balance_status' => 'zero'])) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0
                   {{ request('balance_status') === 'zero' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-950/40 dark:text-amber-300 dark:hover:bg-amber-900/50 border border-amber-200/60 dark:border-amber-900/40' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    موجودی صفر
                </a>

                <a href="{{ route('user.wallet.index', array_merge(request()->except(['page', 'status']), ['status' => 'inactive'])) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0
                   {{ request('status') === 'inactive' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-300 dark:hover:bg-rose-900/50 border border-rose-200/60 dark:border-rose-900/40' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                    مسدود / غیرفعال
                </a>
            </div>

            {{-- Collapsible Advanced Filters Drawer --}}
            <div x-show="filterOpen"
                 x-collapse
                 class="border-t border-gray-100 dark:border-gray-700/80 bg-gray-50/50 dark:bg-gray-900/40 p-5 sm:p-6">
                
                <form method="GET" action="{{ route('user.wallet.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        
                        {{-- 1. Search Query --}}
                        <div>
                            <label class="{{ $labelClass }}">جستجوی هوشمند</label>
                            <div class="relative">
                                <input type="text" name="search" value="{{ request('search') }}"
                                       placeholder="نام، اسلاگ، شماره، کد ملی..."
                                       class="{{ $inputClass }} pl-8">
                                <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 absolute left-2.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                        </div>

                        {{-- 2. Holder Type --}}
                        <div>
                            <label class="{{ $labelClass }}">نوع دارنده حساب</label>
                            <select name="holder_type" class="{{ $inputClass }}">
                                <option value="">همه دارندگان</option>
                                <option value="client" @selected(request('holder_type') === 'client')>کلاینت‌ها (Client)</option>
                                <option value="user" @selected(request('holder_type') === 'user')>کاربران سیستم (User)</option>
                            </select>
                        </div>

                        {{-- 3. Wallet Status --}}
                        <div>
                            <label class="{{ $labelClass }}">وضعیت کیف پول</label>
                            <select name="status" class="{{ $inputClass }}">
                                <option value="">همه وضعیت‌ها</option>
                                <option value="active" @selected(request('status') === 'active')>فقط فعال</option>
                                <option value="inactive" @selected(request('status') === 'inactive')>فقط مسدود / غیرفعال</option>
                            </select>
                        </div>

                        {{-- 4. Balance Status --}}
                        <div>
                            <label class="{{ $labelClass }}">وضعیت موجودی</label>
                            <select name="balance_status" class="{{ $inputClass }}">
                                <option value="">همه مقادیر</option>
                                <option value="positive" @selected(request('balance_status') === 'positive')>دارای موجودی (> ۰)</option>
                                <option value="zero" @selected(request('balance_status') === 'zero')>موجودی صفر (۰)</option>
                            </select>
                        </div>

                        {{-- 5. Min Balance --}}
                        <div>
                            <label class="{{ $labelClass }}">حداقل موجودی ({{ $currencyLabel }})</label>
                            <input type="text" name="min_balance" value="{{ request('min_balance') }}"
                                   placeholder="مثلاً: ۵۰,۰۰۰"
                                   class="{{ $inputClass }}">
                        </div>

                        {{-- 6. Max Balance --}}
                        <div>
                            <label class="{{ $labelClass }}">حداکثر موجودی ({{ $currencyLabel }})</label>
                            <input type="text" name="max_balance" value="{{ request('max_balance') }}"
                                   placeholder="مثلاً: ۵,۰۰۰,۰۰۰"
                                   class="{{ $inputClass }}">
                        </div>

                        {{-- 7. Sort By --}}
                        <div>
                            <label class="{{ $labelClass }}">مرتب‌سازی بر اساس</label>
                            <select name="sort" class="{{ $inputClass }}">
                                <option value="latest" @selected(request('sort', 'latest') === 'latest')>جدیدترین (پیش‌فرض)</option>
                                <option value="balance_desc" @selected(request('sort') === 'balance_desc')>بیشترین موجودی</option>
                                <option value="balance_asc" @selected(request('sort') === 'balance_asc')>کمترین موجودی</option>
                                <option value="oldest" @selected(request('sort') === 'oldest')>قدیمی‌ترین</option>
                                <option value="name_asc" @selected(request('sort') === 'name_asc')>نام کیف پول (الف تا ی)</option>
                                <option value="name_desc" @selected(request('sort') === 'name_desc')>نام کیف پول (ی تا الف)</option>
                            </select>
                        </div>

                        {{-- 8. Items Per Page --}}
                        <div>
                            <label class="{{ $labelClass }}">تعداد نمایش در صفحه</label>
                            <select name="per_page" class="{{ $inputClass }}">
                                <option value="15" @selected(request('per_page') == '15')>۱۵ مورد</option>
                                <option value="20" @selected(request('per_page', '20') == '20')>۲۰ مورد</option>
                                <option value="30" @selected(request('per_page') == '30')>۳۰ مورد</option>
                                <option value="50" @selected(request('per_page') == '50')>۵۰ مورد</option>
                                <option value="100" @selected(request('per_page') == '100')>۱۰۰ مورد</option>
                            </select>
                        </div>
                    </div>

                    {{-- Filter Actions --}}
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200/70 dark:border-gray-700/60">
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            فیلترها به صورت همزمان با تمام شرایط اعمال می‌شوند.
                        </div>
                        <div class="flex items-center gap-2">
                            @if($isFiltered)
                                <a href="{{ route('user.wallet.index') }}"
                                   class="px-4 py-2.5 rounded-xl text-xs font-bold border border-gray-200 text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                                    پاک کردن فیلترها
                                </a>
                            @endif
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/20 active:scale-95 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>اعمال فیلترهای پیشرفته</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Active Filter Tags Bar --}}
            @if($isFiltered)
                <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700/60 bg-indigo-50/30 dark:bg-indigo-950/20 flex flex-wrap items-center gap-2">
                    <span class="text-[11px] font-bold text-indigo-700 dark:text-indigo-300 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        فیلترهای فعال:
                    </span>

                    @if(request('search'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 shadow-2xs">
                            جستجو: <strong class="text-indigo-600 dark:text-indigo-400">{{ request('search') }}</strong>
                            <a href="{{ route('user.wallet.index', request()->except(['page', 'search'])) }}" class="text-gray-400 hover:text-rose-500 dark:text-gray-400 dark:hover:text-rose-400 mr-1 font-bold">×</a>
                        </span>
                    @endif

                    @if(request('holder_type'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 shadow-2xs">
                            دارنده: <strong class="text-indigo-600 dark:text-indigo-400">{{ request('holder_type') === 'user' ? 'کاربر سیستم' : 'کلاینت' }}</strong>
                            <a href="{{ route('user.wallet.index', request()->except(['page', 'holder_type'])) }}" class="text-gray-400 hover:text-rose-500 dark:text-gray-400 dark:hover:text-rose-400 mr-1 font-bold">×</a>
                        </span>
                    @endif

                    @if(request('status'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 shadow-2xs">
                            وضعیت: <strong class="text-indigo-600 dark:text-indigo-400">{{ request('status') === 'active' ? 'فعال' : 'مسدود/غیرفعال' }}</strong>
                            <a href="{{ route('user.wallet.index', request()->except(['page', 'status'])) }}" class="text-gray-400 hover:text-rose-500 dark:text-gray-400 dark:hover:text-rose-400 mr-1 font-bold">×</a>
                        </span>
                    @endif

                    @if(request('balance_status'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 shadow-2xs">
                            موجودی: <strong class="text-indigo-600 dark:text-indigo-400">{{ request('balance_status') === 'positive' ? 'دارای موجودی' : 'موجودی صفر' }}</strong>
                            <a href="{{ route('user.wallet.index', request()->except(['page', 'balance_status'])) }}" class="text-gray-400 hover:text-rose-500 dark:text-gray-400 dark:hover:text-rose-400 mr-1 font-bold">×</a>
                        </span>
                    @endif

                    @if(request('min_balance'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 shadow-2xs">
                            حداقل: <strong class="text-indigo-600 dark:text-indigo-400">{{ request('min_balance') }}</strong>
                            <a href="{{ route('user.wallet.index', request()->except(['page', 'min_balance'])) }}" class="text-gray-400 hover:text-rose-500 dark:text-gray-400 dark:hover:text-rose-400 mr-1 font-bold">×</a>
                        </span>
                    @endif

                    @if(request('max_balance'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 shadow-2xs">
                            حداکثر: <strong class="text-indigo-600 dark:text-indigo-400">{{ request('max_balance') }}</strong>
                            <a href="{{ route('user.wallet.index', request()->except(['page', 'max_balance'])) }}" class="text-gray-400 hover:text-rose-500 dark:text-gray-400 dark:hover:text-rose-400 mr-1 font-bold">×</a>
                        </span>
                    @endif

                    @if(request('sort') && request('sort') !== 'latest')
                        @php
                            $sortLabels = [
                                'balance_desc' => 'بیشترین موجودی',
                                'balance_asc'  => 'کمترین موجودی',
                                'oldest'        => 'قدیمی‌ترین',
                                'name_asc'      => 'نام (الف تا ی)',
                                'name_desc'     => 'نام (ی تا الف)',
                            ];
                        @endphp
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 shadow-2xs">
                            مرتب‌سازی: <strong class="text-indigo-600 dark:text-indigo-400">{{ $sortLabels[request('sort')] ?? request('sort') }}</strong>
                            <a href="{{ route('user.wallet.index', request()->except(['page', 'sort'])) }}" class="text-gray-400 hover:text-rose-500 dark:text-gray-400 dark:hover:text-rose-400 mr-1 font-bold">×</a>
                        </span>
                    @endif

                    @if(request('per_page') && request('per_page') != '20')
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 shadow-2xs">
                            نمایش: <strong class="text-indigo-600 dark:text-indigo-400">{{ request('per_page') }} تایی</strong>
                            <a href="{{ route('user.wallet.index', request()->except(['page', 'per_page'])) }}" class="text-gray-400 hover:text-rose-500 dark:text-gray-400 dark:hover:text-rose-400 mr-1 font-bold">×</a>
                        </span>
                    @endif
                </div>
            @endif

        </div>

        {{-- Wallets List & Table Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-xs overflow-hidden">
            
            {{-- Table Top Toolbar --}}
            <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/40 dark:bg-gray-900/30">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-400 animate-pulse"></span>
                    <h2 class="text-sm font-black text-gray-900 dark:text-white">
                        فهرست حساب‌های کیف پول
                    </h2>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                        صفحه {{ $wallets->currentPage() }} از {{ $wallets->lastPage() ?: 1 }}
                    </span>
                </div>

                <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                    نمایش {{ $wallets->firstItem() ?? 0 }} تا {{ $wallets->lastItem() ?? 0 }} از مجموع {{ $wallets->total() }} حساب
                </div>
            </div>

            @if($wallets->count())
                {{-- Desktop & Tablet Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/80">
                        <thead class="bg-gray-50/80 dark:bg-gray-900/60">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16">
                                    #
                                </th>
                                <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    دارنده حساب
                                </th>
                                <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    مشخصات کیف پول
                                </th>
                                <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    موجودی
                                </th>
                                <th scope="col" class="px-5 py-3.5 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    وضعیت
                                </th>
                                <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    تاریخ ایجاد
                                </th>
                                <th scope="col" class="px-5 py-3.5 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-40">
                                    عملیات
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 bg-white dark:bg-gray-800">
                            @foreach($wallets as $wallet)
                                @php
                                    $holder = $wallet->holder;
                                    $isUser = $wallet->holder_type === (new \App\Models\User())->getMorphClass() || ($holder instanceof \App\Models\User);
                                    $holderName = $isUser 
                                        ? ($holder->name ?? ('کاربر #' . $wallet->holder_id))
                                        : ($holder->full_name ?? ($holder->username ?? ('کلاینت #' . $wallet->holder_id)));
                                    $holderPhone = $isUser ? ($holder->mobile ?? '') : ($holder->phone ?? '');
                                    $holderEmail = $holder->email ?? '';
                                    $walletCurr = ($wallet->currency === 'rial' || $wallet->currency === 'IRR') ? 'ریال' : ($currencyLabel ?? 'تومان');
                                    $holderTypeParam = $isUser ? 'user' : 'client';
                                    $hasBalance = (float)$wallet->balance > 0;
                                @endphp
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition-colors group">
                                    
                                    {{-- 1. ID --}}
                                    <td class="px-5 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400 font-bold">
                                        #{{ $wallet->id }}
                                    </td>

                                    {{-- 2. Holder Info --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-sm shrink-0 shadow-xs
                                                {{ $isUser ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' }}">
                                                {{ mb_substr($holderName, 0, 1, 'utf-8') }}
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    @if(!$isUser && $holder && Route::has('user.clients.show'))
                                                        <a href="{{ route('user.clients.show', $holder->id) }}" class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                                            {{ $holderName }}
                                                        </a>
                                                    @else
                                                        <span class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white">
                                                            {{ $holderName }}
                                                        </span>
                                                    @endif

                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border
                                                        {{ $isUser ? 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/50 dark:text-purple-300 dark:border-purple-800' : 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800' }}">
                                                        {{ $isUser ? 'کاربر سیستم' : 'کلاینت' }}
                                                    </span>
                                                </div>

                                                <div class="flex items-center gap-2 mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                                    @if($holderPhone)
                                                        <span class="dir-ltr text-right font-medium text-gray-600 dark:text-gray-300">{{ $holderPhone }}</span>
                                                    @endif
                                                    @if($holderPhone && $holderEmail)
                                                        <span class="text-gray-300 dark:text-gray-600">•</span>
                                                    @endif
                                                    @if($holderEmail)
                                                        <span class="truncate max-w-[150px] text-gray-500 dark:text-gray-400">{{ $holderEmail }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- 3. Wallet Details --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="text-xs font-bold text-gray-900 dark:text-white">
                                            {{ $wallet->name ?: 'کیف پول اصلی' }}
                                        </div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 font-normal mt-0.5 flex items-center gap-1.5">
                                            <span>اسلاگ: <code class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-mono text-[10px]">{{ $wallet->slug }}</code></span>
                                            <span class="text-gray-300 dark:text-gray-600">•</span>
                                            <span>{{ $walletCurr }}</span>
                                        </div>
                                    </td>

                                    {{-- 4. Balance --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="inline-flex items-baseline gap-1.5 px-3 py-1.5 rounded-xl font-black text-sm
                                            {{ $hasBalance ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800/80 shadow-2xs' : 'bg-gray-50 text-gray-600 border border-gray-200/60 dark:bg-gray-900/60 dark:text-gray-400 dark:border-gray-700' }}">
                                            <span class="text-base tabular-nums">{{ number_format((float)$wallet->balance) }}</span>
                                            <span class="text-[11px] font-bold {{ $hasBalance ? 'text-emerald-600/70 dark:text-emerald-400/70' : 'text-gray-500 dark:text-gray-400' }}">{{ $walletCurr }}</span>
                                        </div>
                                    </td>

                                    {{-- 5. Status --}}
                                    <td class="px-5 py-4 whitespace-nowrap text-center">
                                        @if($wallet->is_active)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800/60">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                فعال
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-800/60">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                مسدود
                                            </span>
                                        @endif
                                    </td>

                                    {{-- 6. Created Date --}}
                                    <td class="px-5 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                        <div class="font-medium">
                                            {{ $wallet->created_at ? (class_exists(\Morilog\Jalali\Jalalian::class) ? \Morilog\Jalali\Jalalian::fromCarbon($wallet->created_at)->format('Y/m/d H:i') : $wallet->created_at->format('Y-m-d')) : '—' }}
                                        </div>
                                    </td>

                                    {{-- 7. Action Buttons --}}
                                    <td class="px-5 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            {{-- Deposit Button --}}
                                            @can('wallet.deposit')
                                                 <button type="button"
                                                        @click="openModal('deposit', {
                                                            id: {{ $wallet->holder_id }},
                                                            holder_type: '{{ $holderTypeParam }}',
                                                            holder_name: '{{ addslashes($holderName) }}',
                                                            phone: '{{ addslashes($holderPhone) }}',
                                                            email: '{{ addslashes($holderEmail) }}',
                                                            balance: {{ (float)$wallet->balance }},
                                                            currency: '{{ $wallet->currency }}',
                                                            currency_label: '{{ $walletCurr }}',
                                                            is_active: {{ $wallet->is_active ? 'true' : 'false' }},
                                                            wallet_id: {{ $wallet->id }}
                                                        })"
                                                        title="شارژ و واریز وجه به این کیف پول"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-emerald-600 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-300 dark:hover:bg-emerald-900/60 border border-emerald-200/80 dark:border-emerald-800/60 transition shadow-2xs active:scale-95">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                                </button>
                                            @endcan

                                            {{-- Withdraw Button --}}
                                            @can('wallet.withdraw')
                                                <button type="button"
                                                        @click="openModal('withdraw', {
                                                            id: {{ $wallet->holder_id }},
                                                            holder_type: '{{ $holderTypeParam }}',
                                                            holder_name: '{{ addslashes($holderName) }}',
                                                            phone: '{{ addslashes($holderPhone) }}',
                                                            email: '{{ addslashes($holderEmail) }}',
                                                            balance: {{ (float)$wallet->balance }},
                                                            currency: '{{ $wallet->currency }}',
                                                            currency_label: '{{ $walletCurr }}',
                                                            is_active: {{ $wallet->is_active ? 'true' : 'false' }},
                                                            wallet_id: {{ $wallet->id }}
                                                        })"
                                                        title="کسر و برداشت از موجودی این کیف پول"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-rose-600 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/50 dark:text-rose-300 dark:hover:bg-rose-900/60 border border-rose-200/80 dark:border-rose-800/60 transition shadow-2xs active:scale-95">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                                </button>
                                            @endcan

                                            {{-- Transactions Link --}}
                                            @can('wallet.transactions.view')
                                                <a href="{{ route('user.wallet.transactions.index', ['wallet_id' => $wallet->id]) }}"
                                                   title="مشاهده دفتر تراکنش‌های این کیف پول"
                                                   class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-gray-600 bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/80 dark:text-gray-200 dark:hover:bg-gray-600 dark:hover:text-white border border-gray-200 dark:border-gray-600 transition shadow-2xs">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                </a>
                                            @endcan

                                            {{-- Toggle Status --}}
                                            @can('wallet.manage')
                                                <form action="{{ route('user.wallet.toggle-status', $wallet) }}" method="POST" class="inline" onsubmit="return confirm('آیا از تغییر وضعیت این کیف پول مطمئن هستید؟')">
                                                    @csrf
                                                    <button type="submit"
                                                            title="{{ $wallet->is_active ? 'مسدود کردن این حساب' : 'فعال کردن این حساب' }}"
                                                            class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs {{ $wallet->is_active ? 'text-amber-600 bg-amber-50 hover:bg-amber-100 border-amber-200/80 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/60' : 'text-emerald-600 bg-emerald-50 hover:bg-emerald-100 border-emerald-200/80 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60' }} border transition shadow-2xs">
                                                        @if($wallet->is_active)
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                        @else
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        @endif
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination & Summary Footer --}}
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/80 bg-gray-50/50 dark:bg-gray-900/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        نمایش {{ $wallets->firstItem() ?? 0 }} تا {{ $wallets->lastItem() ?? 0 }} از مجموع {{ $wallets->total() }} حساب
                    </div>
                    <div>
                        {{ $wallets->links() }}
                    </div>
                </div>
            @else
                {{-- Empty State --}}
                <div class="py-16 px-6 text-center space-y-4">
                    <div class="w-16 h-16 mx-auto rounded-3xl bg-gray-100 dark:bg-gray-700/60 text-gray-400 dark:text-gray-400 flex items-center justify-center shadow-inner">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="max-w-sm mx-auto space-y-1">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">هیچ کیف پولی با این شرایط یافت نشد!</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @if($isFiltered)
                                فیلترهای جستجو بیش از حد محدودکننده هستند. برای مشاهده تمامی حساب‌ها فیلترها را حذف کنید.
                            @else
                                تا این لحظه هیچ حسابی در سیستم ایجاد نشده است.
                            @endif
                        </p>
                    </div>
                    @if($isFiltered)
                        <div>
                            <a href="{{ route('user.wallet.index') }}"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 dark:bg-indigo-950/50 dark:text-indigo-300 dark:border-indigo-800 transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                <span>حذف فیلترها و مشاهده همه</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endif

        </div>

        {{-- Advanced Professional Transaction Modal (Deposit & Withdraw) --}}
        <div x-show="showModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 dark:bg-black/75 backdrop-blur-sm flex items-center justify-center p-4"
             @keydown.escape.window="closeModal()">
            <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-gray-100 dark:border-gray-700 relative space-y-5 text-gray-900 dark:text-gray-100"
                 @click.outside="closeModal()">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700/80">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center shadow-xs"
                             :class="mode === 'deposit' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400' : 'bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400'">
                            <template x-if="mode === 'deposit'">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </template>
                            <template x-if="mode === 'withdraw'">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                            </template>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white"
                                x-text="mode === 'deposit' ? 'شارژ و افزایش موجودی کیف پول' : 'کسر و برداشت از موجودی کیف پول'"></h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"
                                x-text="mode === 'deposit' ? 'واریز دستی وجه به کیف پول کلاینت یا کاربر' : 'برداشت دستی وجه از موجودی کیف پول کلاینت یا کاربر'"></p>
                        </div>
                    </div>
                    <button type="button" @click="closeModal()" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-gray-200 dark:text-gray-400 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {{-- Form --}}
                <form :action="mode === 'deposit' ? '{{ route('user.wallet.deposit') }}' : '{{ route('user.wallet.withdraw') }}'"
                      method="POST"
                      class="space-y-4"
                      @submit="validateSubmit($event)">
                    @csrf

                    {{-- Section 1: Searchable Holder Selection --}}
                    <div>
                        <label class="{{ $labelClass }}">انتخاب دارنده حساب (کلاینت / کاربر) *</label>

                        {{-- If holder is selected --}}
                        <template x-if="selectedHolder">
                            <div class="p-3.5 rounded-2xl border transition-all flex items-center justify-between"
                                 :class="mode === 'deposit' ? 'bg-emerald-50/60 border-emerald-200 text-emerald-950 dark:bg-emerald-950/40 dark:border-emerald-800/80 dark:text-emerald-200' : 'bg-rose-50/60 border-rose-200 text-rose-950 dark:bg-rose-950/40 dark:border-rose-800/80 dark:text-rose-200'">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm"
                                         :class="selectedHolder.holder_type === 'user' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300'">
                                        <span x-text="selectedHolder.holder_name ? selectedHolder.holder_name.charAt(0) : ''"></span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold"
                                                  :class="selectedHolder.holder_type === 'user' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300'"
                                                  x-text="selectedHolder.holder_type === 'user' ? 'کاربر سیستم' : 'کلاینت'"></span>
                                            <span class="font-bold text-sm text-gray-900 dark:text-white" x-text="selectedHolder.holder_name"></span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            <span x-show="selectedHolder.phone" x-text="selectedHolder.phone"></span>
                                            <span class="text-gray-300 dark:text-gray-600">•</span>
                                            <span>موجودی فعلی:</span>
                                            <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="formatNumber(selectedHolder.balance) + ' ' + holderCurrencyLabel()"></span>
                                        </div>
                                    </div>
                                </div>

                                <button type="button"
                                        @click="clearSelectedHolder()"
                                        class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700 transition">
                                    تغییر
                                </button>
                            </div>
                        </template>

                        {{-- If NO holder is selected: Search box with autocomplete --}}
                        <div x-show="!selectedHolder" class="relative">
                            {{-- Type Filter Tabs --}}
                            <div class="flex items-center gap-1 mb-2 bg-gray-100 dark:bg-gray-900 border border-transparent dark:border-gray-700/60 p-1 rounded-xl">
                                <button type="button" @click="searchType = 'all'; if (searchQuery.trim().length > 0) searchHolders()"
                                        :class="searchType === 'all' ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-xs font-bold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                                        class="flex-1 py-1 text-xs rounded-lg transition-all">همه</button>
                                <button type="button" @click="searchType = 'client'; if (searchQuery.trim().length > 0) searchHolders()"
                                        :class="searchType === 'client' ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 shadow-xs font-bold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                                        class="flex-1 py-1 text-xs rounded-lg transition-all">کلاینت‌ها</button>
                                <button type="button" @click="searchType = 'user'; if (searchQuery.trim().length > 0) searchHolders()"
                                        :class="searchType === 'user' ? 'bg-white dark:bg-gray-800 text-purple-600 dark:text-purple-400 shadow-xs font-bold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                                        class="flex-1 py-1 text-xs rounded-lg transition-all">کاربران سیستم</button>
                            </div>

                            <div class="relative">
                                <input type="text"
                                       x-model="searchQuery"
                                       @input.debounce.300ms="searchHolders()"
                                       @focus="if (searchQuery.trim().length > 0 && searchResults.length > 0) isOpenSearch = true"
                                       placeholder="برای جستجو نام، شماره تماس، کدملی یا شناسه را تایپ کنید..."
                                       class="{{ $inputClass }} pl-8">
                                <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 absolute left-3 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>

                            {{-- Dropdown Search Results --}}
                            <div x-show="isOpenSearch && searchResults.length > 0"
                                 @click.outside="isOpenSearch = false"
                                 class="absolute z-20 top-full right-0 left-0 mt-1 max-h-56 overflow-y-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="item in searchResults" :key="item.holder_type + '_' + item.id">
                                    <div @click="selectHolder(item)"
                                         class="p-3 hover:bg-gray-50 dark:hover:bg-gray-700/60 cursor-pointer flex items-center justify-between transition-colors">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs"
                                                 :class="item.holder_type === 'user' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300'">
                                                <span x-text="item.holder_name.charAt(0)"></span>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold"
                                                          :class="item.holder_type === 'user' ? 'bg-purple-50 text-purple-600 dark:bg-purple-900/40 dark:text-purple-300' : 'bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300'"
                                                          x-text="item.badge"></span>
                                                    <span class="text-xs font-bold text-gray-900 dark:text-gray-100" x-text="item.holder_name"></span>
                                                </div>
                                                <span class="text-[10px] text-gray-500 dark:text-gray-400 block mt-0.5" x-text="item.phone"></span>
                                            </div>
                                        </div>
                                        <div class="text-left">
                                            <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 block" x-text="formatNumber(item.balance) + ' ' + (item.currency_label || activeCurrencyLabel)"></span>
                                            <span class="text-[9px] text-gray-500 dark:text-gray-400">موجودی فعلی</span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Loading state --}}
                            <div x-show="isLoading" class="absolute left-9 top-12 text-xs text-gray-400 dark:text-gray-400 flex items-center gap-1">
                                <svg class="animate-spin h-3.5 w-3.5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                        </div>

                        {{-- Hidden inputs for submission --}}
                        <input type="hidden" name="holder_type" :value="selectedHolder ? selectedHolder.holder_type : ''" required>
                        <input type="hidden" name="holder_id" :value="selectedHolder ? selectedHolder.id : ''" required>
                    </div>

                    {{-- Section 2: Amount & Quick Chips --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-bold text-gray-700 dark:text-gray-300">
                                <span>مبلغ</span>
                                <span class="text-gray-500 dark:text-gray-400 text-[11px] font-normal" x-text="'(به ' + activeCurrencyLabel + ')'"></span>
                                <span class="text-rose-500">*</span>
                            </label>
                            <template x-if="mode === 'withdraw' && selectedHolder && selectedHolder.balance > 0">
                                <button type="button"
                                        @click="amount = formatNumber(selectedHolder.balance)"
                                        class="text-[11px] font-bold text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 underline">
                                    برداشت کل موجودی
                                </button>
                            </template>
                        </div>

                        <div class="relative">
                            <input type="text"
                                   x-model="amount"
                                   @input="amount = formatNumber($event.target.value)"
                                   placeholder="مثلاً: ۱,۰۰۰,۰۰۰"
                                   class="{{ $inputClass }} pl-14 font-bold text-base">
                            <span class="absolute left-3.5 top-2.5 text-xs font-bold text-gray-400 dark:text-gray-400" x-text="activeCurrencyLabel"></span>
                        </div>

                        {{-- Hidden input for actual amount submission --}}
                        <input type="hidden" name="amount" :value="unformatNumber(amount)">

                        {{-- Persian Verbal Amount Preview --}}
                        <div x-show="amount && unformatNumber(amount) > 0" class="mt-1.5 text-[11px] font-bold"
                             :class="mode === 'deposit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                            <span x-text="numberToWords(unformatNumber(amount)) + ' ' + activeCurrencyLabel"></span>
                        </div>

                        {{-- Quick Amount Chips --}}
                        <div class="flex flex-wrap gap-1.5 mt-2.5">
                            <template x-for="chip in quickChips" :key="chip.value">
                                <button type="button"
                                        @click="addAmount(chip.value)"
                                        class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700/60 dark:text-gray-300 dark:hover:bg-gray-700 border border-gray-200/60 dark:border-gray-600/60 transition">
                                    <span x-text="'+ ' + chip.label"></span>
                                </button>
                            </template>
                            <button type="button" @click="amount = ''" class="px-2 py-1 rounded-lg text-[10px] font-bold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                پاک کردن
                            </button>
                        </div>
                    </div>

                    {{-- Section 3: Live Balance Impact Simulation --}}
                    <div x-show="selectedHolder && unformatNumber(amount) > 0"
                         class="p-3.5 rounded-2xl border text-xs space-y-1.5 transition-all"
                         :class="isWithdrawExceeded() ? 'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:border-rose-900/80 dark:text-rose-200' : 'bg-gray-50 border-gray-200 text-gray-700 dark:bg-gray-900/60 dark:border-gray-700/80 dark:text-gray-300'">
                        <div class="flex items-center justify-between">
                            <span class="font-medium">موجودی فعلی:</span>
                            <span class="font-bold" x-text="formatNumber(selectedHolder ? selectedHolder.balance : 0) + ' ' + holderCurrencyLabel()"></span>
                        </div>
                        <div class="flex items-center justify-between font-bold pt-1 border-t border-gray-200/70 dark:border-gray-700/70">
                            <span x-text="mode === 'deposit' ? 'موجودی پس از شارژ:' : 'موجودی پس از کسر:'"></span>
                            <span class="text-sm"
                                  :class="mode === 'deposit' ? 'text-emerald-600 dark:text-emerald-400' : (isWithdrawExceeded() ? 'text-rose-600 dark:text-rose-400' : 'text-blue-600 dark:text-blue-400')"
                                  x-text="formatNumber(calculateProjectedBalance()) + ' ' + holderCurrencyLabel()"></span>
                        </div>
                        <template x-if="isWithdrawExceeded()">
                            <p class="text-[11px] font-bold text-rose-600 dark:text-rose-400 pt-1 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                مبلغ درخواستی برای کسر بیشتر از موجودی کیف پول دارنده است!
                            </p>
                        </template>
                    </div>

                    {{-- Section 4: Description & Presets --}}
                    <div>
                        <label class="{{ $labelClass }}">توضیحات و دلیل تراکنش (اختیاری)</label>
                        <input type="text"
                               name="description"
                               x-model="description"
                               placeholder="دلیل شارژ یا کسر موجودی..."
                               class="{{ $inputClass }}">

                        {{-- Presets --}}
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            <template x-for="preset in (mode === 'deposit' ? depositPresets : withdrawPresets)" :key="preset">
                                <button type="button"
                                        @click="description = preset"
                                        class="px-2 py-0.5 rounded-md text-[10px] font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700/60 dark:text-gray-300 dark:hover:bg-gray-700 border border-gray-200/60 dark:border-gray-700 transition"
                                        x-text="preset"></button>
                            </template>
                        </div>
                    </div>

                    {{-- Modal Footer Actions --}}
                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-gray-100 dark:border-gray-700/80">
                        <button type="button"
                                @click="closeModal()"
                                class="px-4 py-2.5 rounded-xl text-xs font-bold border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            انصراف
                        </button>
                        <button type="submit"
                                :disabled="!selectedHolder || !unformatNumber(amount) || isWithdrawExceeded()"
                                class="px-5 py-2.5 rounded-xl text-xs font-bold text-white shadow-md transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="mode === 'deposit' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/20' : 'bg-rose-600 hover:bg-rose-700 shadow-rose-500/20'"
                                x-text="mode === 'deposit' ? 'ثبت و واریز شارژ' : 'ثبت و کسر موجودی'">
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- Alpine.js Manager Script --}}
    <script>
        function walletManager() {
            const systemCurrency = '{{ $systemCurrency ?? "toman" }}';
            const isRial = (systemCurrency === 'rial' || systemCurrency === 'IRR');
            const currencyLabel = '{{ $currencyLabel ?? "تومان" }}';

            return {
                filterOpen: {{ $isFiltered ? 'true' : 'false' }},
                showModal: false,
                mode: 'deposit', // 'deposit' or 'withdraw'
                selectedHolder: null,
                searchQuery: '',
                searchType: 'all',
                searchResults: [],
                isLoading: false,
                isOpenSearch: false,
                amount: '',
                description: '',
                activeCurrencyLabel: currencyLabel,
                isRialSystem: isRial,

                holderCurrencyLabel() {
                    return (this.selectedHolder && this.selectedHolder.currency_label)
                        ? this.selectedHolder.currency_label
                        : this.activeCurrencyLabel;
                },

                quickChips: isRial ? [
                    { label: '۱۰۰ هزار', value: 100000 },
                    { label: '۵۰۰ هزار', value: 500000 },
                    { label: '۱ میلیون', value: 1000000 },
                    { label: '۵ میلیون', value: 5000000 },
                    { label: '۱۰ میلیون', value: 10000000 },
                    { label: '۵۰ میلیون', value: 50000000 },
                ] : [
                    { label: '۱۰ هزار', value: 10000 },
                    { label: '۵۰ هزار', value: 50000 },
                    { label: '۱۰۰ هزار', value: 100000 },
                    { label: '۵۰۰ هزار', value: 500000 },
                    { label: '۱ میلیون', value: 1000000 },
                    { label: '۵ میلیون', value: 5000000 },
                ],

                depositPresets: ['شارژ تشویقی', 'اصلاح موجودی', 'عودت وجه', 'پاداش وفاداری', 'واریز نقدی / کارت'],
                withdrawPresets: ['کسر بابت خدمات', 'اصلاح موجودی', 'تسویه حساب', 'برداشت کاربر', 'کارمزد / جریمه'],

                openModal(mode = 'deposit', preloadedHolder = null) {
                    this.mode = mode;
                    this.amount = '';
                    this.description = '';
                    this.searchQuery = '';
                    this.searchResults = [];
                    this.isOpenSearch = false;
                    this.isLoading = false;

                    if (preloadedHolder) {
                        this.selectedHolder = { ...preloadedHolder };
                    } else {
                        this.selectedHolder = null;
                    }

                    this.showModal = true;
                    document.body.classList.add('overflow-hidden');
                },

                closeModal() {
                    this.showModal = false;
                    document.body.classList.remove('overflow-hidden');
                },

                async searchHolders() {
                    const query = (this.searchQuery || '').trim();
                    if (query.length === 0) {
                        this.searchResults = [];
                        this.isOpenSearch = false;
                        this.isLoading = false;
                        return;
                    }

                    this.isLoading = true;
                    try {
                        const url = `{{ route('user.wallet.search-holders') }}?q=${encodeURIComponent(query)}&type=${this.searchType}&limit=20`;
                        const res = await fetch(url);
                        if (res.ok) {
                            this.searchResults = await res.json();
                            this.isOpenSearch = this.searchResults.length > 0;
                        }
                    } catch (e) {
                        console.error('Error fetching holders:', e);
                    } finally {
                        this.isLoading = false;
                    }
                },

                selectHolder(holder) {
                    this.selectedHolder = holder;
                    this.isOpenSearch = false;
                    this.searchResults = [];
                    this.searchQuery = '';
                },

                clearSelectedHolder() {
                    this.selectedHolder = null;
                    this.searchQuery = '';
                    this.searchResults = [];
                    this.isOpenSearch = false;
                },

                addAmount(val) {
                    const current = this.unformatNumber(this.amount) || 0;
                    this.amount = this.formatNumber(current + val);
                },

                isWithdrawExceeded() {
                    if (this.mode !== 'withdraw' || !this.selectedHolder) return false;
                    const entered = this.unformatNumber(this.amount) || 0;
                    return entered > (this.selectedHolder.balance || 0);
                },

                calculateProjectedBalance() {
                    if (!this.selectedHolder) return 0;
                    const current = parseFloat(this.selectedHolder.balance) || 0;
                    const entered = this.unformatNumber(this.amount) || 0;
                    if (this.mode === 'deposit') {
                        return current + entered;
                    } else {
                        return Math.max(0, current - entered);
                    }
                },

                validateSubmit(e) {
                    if (!this.selectedHolder) {
                        alert('لطفاً دارنده حساب را انتخاب کنید.');
                        e.preventDefault();
                        return false;
                    }
                    const num = this.unformatNumber(this.amount);
                    if (!num || num <= 0) {
                        alert('مبلغ باید بزرگتر از صفر باشد.');
                        e.preventDefault();
                        return false;
                    }
                    if (this.isWithdrawExceeded()) {
                        alert('مبلغ کسر بیشتر از موجودی کیف پول دارنده است.');
                        e.preventDefault();
                        return false;
                    }
                    return true;
                },

                formatNumber(val) {
                    if (val === null || val === undefined || val === '') return '';
                    let clean = String(val).replace(/,/g, '').trim();
                    clean = clean.replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
                                 .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));
                    if (clean === '') return '';
                    const num = Math.round(parseFloat(clean));
                    if (isNaN(num)) return '';
                    return String(num).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                },

                unformatNumber(val) {
                    if (val === null || val === undefined || val === '') return 0;
                    let clean = String(val).replace(/,/g, '').trim();
                    clean = clean.replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
                                 .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));
                    if (clean === '') return 0;
                    const num = Math.round(parseFloat(clean));
                    return isNaN(num) ? 0 : num;
                },

                numberToWords(value) {
                    if (!value) return '';
                    let num = String(value).replace(/,/g, '');
                    if (num === '' || isNaN(num) || parseInt(num) === 0) return '';

                    const ones = ['', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه'];
                    const tens = ['', 'ده', 'بیست', 'سی', 'چهل', 'پنجاه', 'شصت', 'هفتاد', 'هشتاد', 'نود'];
                    const hundreds = ['', 'صد', 'دویست', 'سیصد', 'چهارصد', 'پانصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'];
                    const teens = ['ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده', 'شانزده', 'هفده', 'هجده', 'نوزده'];
                    const classes = ['', 'هزار', 'میلیون', 'میلیارد', 'هزار میلیارد', 'میلیون میلیارد'];

                    let str = num.split('').reverse().join('');
                    let result = [];

                    for (let i = 0; i < str.length; i += 3) {
                        let group = str.substr(i, 3).split('').reverse().join('');
                        if (parseInt(group) === 0) continue;

                        let groupWords = [];
                        let h = parseInt(group.length === 3 ? group[0] : 0);
                        let t = parseInt(group.length >= 2 ? group[group.length - 2] : 0);
                        let o = parseInt(group[group.length - 1]);

                        if (h > 0) groupWords.push(hundreds[h]);

                        if (t === 1 && o >= 0) {
                            groupWords.push(teens[o]);
                        } else {
                            if (t > 1) groupWords.push(tens[t]);
                            if (o > 0) groupWords.push(ones[o]);
                        }

                        let groupText = groupWords.filter(Boolean).join(' و ');
                        if (classes[i / 3]) groupText += ' ' + classes[i / 3];
                        result.push(groupText);
                    }

                    return result.reverse().join(' و ');
                }
            }
        }
    </script>
@endsection
