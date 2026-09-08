@extends('layouts.user')

@section('content')
    @php
        use Morilog\Jalali\Jalalian;

        $inputClass = "w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-xs text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:bg-gray-900 [&>option]:bg-white [&>option]:text-gray-900 [&>option]:dark:bg-gray-800 [&>option]:dark:text-gray-100";
        $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";

        $activeFiltersCount = count(array_filter(
            request()->only(['search', 'wallet_id', 'holder_type', 'type', 'status', 'flow', 'min_amount', 'max_amount', 'sort', 'per_page']),
            fn($v, $k) => !empty($v) && !($k === 'sort' && $v === 'latest') && !($k === 'per_page' && $v == '20'),
            ARRAY_FILTER_USE_BOTH
        ));
        $isFiltered = $activeFiltersCount > 0;
    @endphp

    <div class="w-full mx-auto px-4 py-8 space-y-6" x-data="{ filterOpen: {{ $isFiltered ? 'true' : 'false' }} }">

        {{-- Main Header --}}
        <div class="bg-white dark:bg-gray-800/90 rounded-2xl border border-gray-200/80 dark:border-gray-700/70 p-5 shadow-xs backdrop-blur-sm">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white flex items-center justify-center shadow-md shadow-indigo-500/20 shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">
                                دفتر کل تراکنش‌های مالی سیستم
                            </h1>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-900/50">
                                {{ $transactions->total() }} تراکنش
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>ره‌گیری و حسابرسی کلیه واریزها، برداشت‌ها، پرداخت فاکتورها، پورسانت‌ها و جابجایی‌ها</span>
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <a href="{{ route('user.wallet.index') }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-white hover:bg-gray-50 text-gray-700 hover:text-gray-900 border border-gray-200 hover:border-gray-300 shadow-xs transition-all active:scale-95 dark:bg-gray-700/80 dark:hover:bg-gray-700 dark:border-gray-600 dark:hover:border-gray-500 dark:text-gray-100 dark:hover:text-white">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        <span>مدیریت کیف‌ پول‌ها</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Specific Wallet Focus Banner (If Filtered by Wallet ID) --}}
        @if(isset($selectedWallet) && $selectedWallet)
            @php
                $swHolder = $selectedWallet->holder;
                $swIsUser = $selectedWallet->holder_type === (new \App\Models\User())->getMorphClass() || ($swHolder instanceof \App\Models\User);
                $swHolderName = $swIsUser 
                    ? ($swHolder->name ?? ('کاربر #' . $selectedWallet->holder_id))
                    : ($swHolder->full_name ?? ($swHolder->username ?? ('کلاینت #' . $selectedWallet->holder_id)));
                $swPhone = $swIsUser ? ($swHolder->mobile ?? '') : ($swHolder->phone ?? '');
                $swCurr = ($selectedWallet->currency === 'rial' || $selectedWallet->currency === 'IRR') ? 'ریال' : ($currencyLabel ?? 'تومان');
            @endphp
            <div class="p-4 rounded-2xl bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/80 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xs">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center font-black text-sm shrink-0 shadow-xs
                        {{ $swIsUser ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300' }}">
                        {{ mb_substr($swHolderName, 0, 1, 'utf-8') }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-indigo-700 dark:text-indigo-300">تراکنش‌های اختصاصی کیف پول:</span>
                            <strong class="text-sm font-black text-gray-900 dark:text-white">{{ $swHolderName }}</strong>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border
                                {{ $swIsUser ? 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/50 dark:text-purple-300 dark:border-purple-800' : 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800' }}">
                                {{ $swIsUser ? 'کاربر سیستم' : 'کلاینت' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <span>نام کیف پول: <strong class="text-gray-700 dark:text-gray-300">{{ $selectedWallet->name ?: $selectedWallet->slug }}</strong></span>
                            <span class="text-gray-300 dark:text-gray-600">•</span>
                            <span>موجودی فعلی: <strong class="text-emerald-600 dark:text-emerald-400 tabular-nums">{{ number_format((float)$selectedWallet->balance) }} {{ $swCurr }}</strong></span>
                            @if($swPhone)
                                <span class="text-gray-300 dark:text-gray-600">•</span>
                                <span class="dir-ltr text-gray-600 dark:text-gray-300 font-medium">{{ $swPhone }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('user.wallet.transactions.index') }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-white hover:bg-gray-50 text-gray-700 hover:text-gray-900 border border-gray-200 hover:border-gray-300 shadow-2xs dark:bg-gray-700/80 dark:hover:bg-gray-700 dark:border-gray-600 dark:hover:border-gray-500 dark:text-gray-100 dark:hover:text-white transition active:scale-95">
                        <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        <span>نمایش تمامی تراکنش‌های سیستم</span>
                    </a>
                </div>
            </div>
        @endif

        {{-- KPI Overview Cards --}}
        @if(isset($stats))
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Card 1: Total Inflow (Deposits) --}}
                <div class="p-5 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-md shadow-emerald-500/10 relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="flex items-center justify-between text-emerald-100">
                            <span class="text-xs font-bold">مجموع واریزی‌ها (ورودی)</span>
                            <span class="p-2 rounded-xl bg-white/10 backdrop-blur-xs">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                            </span>
                        </div>
                        <div class="mt-3 flex items-baseline gap-1.5">
                            <span class="text-2xl font-black tabular-nums">+{{ number_format($stats['total_inflow']) }}</span>
                            <span class="text-xs font-bold text-emerald-200">{{ $currencyLabel }}</span>
                        </div>
                        <span class="mt-1.5 block text-[11px] text-emerald-100/90">
                            شامل شارژها، عودت وجه، پاداش و پورسانت‌ها
                        </span>
                    </div>
                    <div class="absolute -left-4 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                </div>

                {{-- Card 2: Total Outflow (Withdrawals) --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 shadow-xs">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                        <span class="text-xs font-bold">مجموع برداشت‌ها (خروجی)</span>
                        <span class="p-2 rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-300">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black text-rose-600 dark:text-rose-400 tabular-nums">-{{ number_format($stats['total_outflow']) }}</span>
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $currencyLabel }}</span>
                    </div>
                    <span class="mt-1.5 block text-[11px] text-gray-500 dark:text-gray-400">
                        شامل تسویه‌ها، پرداخت فاکتورها و هزینه‌ها
                    </span>
                </div>

                {{-- Card 3: Net Cash Flow --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 shadow-xs">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                        <span class="text-xs font-bold">تراز گردش مالی (خالص)</span>
                        <span class="p-2 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black tabular-nums {{ $stats['net_flow'] >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ ($stats['net_flow'] >= 0 ? '+' : '') . number_format($stats['net_flow']) }}
                        </span>
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $currencyLabel }}</span>
                    </div>
                    <span class="mt-1.5 block text-[11px] text-gray-500 dark:text-gray-400">
                        تفاضل کل ورودی و خروجی در این فیلتر
                    </span>
                </div>

                {{-- Card 4: Total Transactions Count --}}
                <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 shadow-xs">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                        <span class="text-xs font-bold">تعداد تراکنش‌ها</span>
                        <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-300">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black text-gray-900 dark:text-white tabular-nums">{{ number_format($stats['total_count']) }}</span>
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">تراکنش</span>
                    </div>
                    <div class="mt-1.5 flex items-center gap-2 text-[11px]">
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ $stats['completed_count'] }} موفق</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Advanced Filter & Search Hub (Matching Wallets Page) --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-xs overflow-hidden">
            
            {{-- Quick Search & Filter Toggle Bar --}}
            <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                
                {{-- Fast Search Input with Immediate Submit --}}
                <form method="GET" action="{{ route('user.wallet.transactions.index') }}" class="flex-1 flex items-center gap-2 max-w-xl">
                    {{-- Preserve existing active filters if searching quickly --}}
                    @foreach(request()->except(['search', 'page']) as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach

                    <div class="relative flex-1">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="جستجوی هوشمند (کد پیگیری UUID، نام دارنده، شماره تماس، کد ملی، توضیحات، نام کیف پول)..."
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
                        <a href="{{ route('user.wallet.transactions.index') }}"
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
                
                <a href="{{ route('user.wallet.transactions.index') }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0
                   {{ !request()->hasAny(['flow', 'type', 'status', 'holder_type']) ? 'bg-indigo-600 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700/60 dark:text-gray-300 dark:hover:bg-gray-700 border border-gray-200/60 dark:border-gray-700' }}">
                    همه تراکنش‌ها
                </a>

                <a href="{{ route('user.wallet.transactions.index', array_merge(request()->except(['page', 'flow']), ['flow' => 'inflow'])) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0
                   {{ request('flow') === 'inflow' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 dark:hover:bg-emerald-900/50 border border-emerald-200/60 dark:border-emerald-900/40' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    فقط واریزها (ورودی)
                </a>

                <a href="{{ route('user.wallet.transactions.index', array_merge(request()->except(['page', 'flow']), ['flow' => 'outflow'])) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0
                   {{ request('flow') === 'outflow' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-300 dark:hover:bg-rose-900/50 border border-rose-200/60 dark:border-rose-900/40' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                    فقط برداشت‌ها (خروجی)
                </a>

                <a href="{{ route('user.wallet.transactions.index', array_merge(request()->except(['page', 'status']), ['status' => 'completed'])) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0
                   {{ request('status') === 'completed' ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-900/50 border border-blue-200/60 dark:border-blue-900/40' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                    تراکنش‌های موفق
                </a>

                <a href="{{ route('user.wallet.transactions.index', array_merge(request()->except(['page', 'holder_type']), ['holder_type' => 'client'])) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0
                   {{ request('holder_type') === 'client' ? 'bg-purple-600 text-white shadow-xs' : 'bg-purple-50 text-purple-700 hover:bg-purple-100 dark:bg-purple-950/40 dark:text-purple-300 dark:hover:bg-purple-900/50 border border-purple-200/60 dark:border-purple-900/40' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>
                    فقط کلاینت‌ها
                </a>

                <a href="{{ route('user.wallet.transactions.index', array_merge(request()->except(['page', 'holder_type']), ['holder_type' => 'user'])) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0
                   {{ request('holder_type') === 'user' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-950/40 dark:text-amber-300 dark:hover:bg-amber-900/50 border border-amber-200/60 dark:border-amber-900/40' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    فقط کاربران سیستم
                </a>
            </div>

            {{-- Collapsible Advanced Filters Drawer --}}
            <div x-show="filterOpen"
                 x-collapse
                 class="border-t border-gray-100 dark:border-gray-700/80 bg-gray-50/50 dark:bg-gray-900/40 p-5 sm:p-6">
                
                <form method="GET" action="{{ route('user.wallet.transactions.index') }}" class="space-y-4">
                    {{-- Preserve wallet_id if set --}}
                    @if(request('wallet_id'))
                        <input type="hidden" name="wallet_id" value="{{ request('wallet_id') }}">
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        
                        {{-- 1. Search Query --}}
                        <div>
                            <label class="{{ $labelClass }}">جستجوی هوشمند</label>
                            <div class="relative">
                                <input type="text" name="search" value="{{ request('search') }}"
                                       placeholder="کد پیگیری، نام، شماره، توضیحات..."
                                       class="{{ $inputClass }} pl-8">
                                <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 absolute left-2.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                        </div>

                        {{-- 2. Cash Flow Direction --}}
                        <div>
                            <label class="{{ $labelClass }}">جهت گردش مالی</label>
                            <select name="flow" class="{{ $inputClass }}">
                                <option value="">همه جریان‌ها</option>
                                <option value="inflow" @selected(request('flow') === 'inflow')>واریز / ورودی (+)</option>
                                <option value="outflow" @selected(request('flow') === 'outflow')>برداشت / خروجی (-)</option>
                            </select>
                        </div>

                        {{-- 3. Transaction Type --}}
                        <div>
                            <label class="{{ $labelClass }}">نوع دقیق تراکنش</label>
                            <select name="type" class="{{ $inputClass }}">
                                <option value="">همه انواع تراکنش</option>
                                @foreach($types as $t)
                                    <option value="{{ $t->value }}" @selected(request('type') === $t->value)>{{ $t->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 4. Status --}}
                        <div>
                            <label class="{{ $labelClass }}">وضعیت تراکنش</label>
                            <select name="status" class="{{ $inputClass }}">
                                <option value="">همه وضعیت‌ها</option>
                                @foreach($statuses as $s)
                                    <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 5. Holder Type --}}
                        <div>
                            <label class="{{ $labelClass }}">نوع دارنده حساب</label>
                            <select name="holder_type" class="{{ $inputClass }}">
                                <option value="">همه دارندگان</option>
                                <option value="client" @selected(request('holder_type') === 'client')>کلاینت‌ها (Client)</option>
                                <option value="user" @selected(request('holder_type') === 'user')>کاربران سیستم (User)</option>
                            </select>
                        </div>

                        {{-- 6. Min Amount --}}
                        <div>
                            <label class="{{ $labelClass }}">حداقل مبلغ ({{ $currencyLabel }})</label>
                            <input type="text" name="min_amount" value="{{ request('min_amount') }}"
                                   placeholder="مثلاً: ۵۰,۰۰۰"
                                   class="{{ $inputClass }}">
                        </div>

                        {{-- 7. Max Amount --}}
                        <div>
                            <label class="{{ $labelClass }}">حداکثر مبلغ ({{ $currencyLabel }})</label>
                            <input type="text" name="max_amount" value="{{ request('max_amount') }}"
                                   placeholder="مثلاً: ۵,۰۰۰,۰۰۰"
                                   class="{{ $inputClass }}">
                        </div>

                        {{-- 8. Sort By --}}
                        <div>
                            <label class="{{ $labelClass }}">مرتب‌سازی بر اساس</label>
                            <select name="sort" class="{{ $inputClass }}">
                                <option value="latest" @selected(request('sort', 'latest') === 'latest')>جدیدترین (پیش‌فرض)</option>
                                <option value="amount_desc" @selected(request('sort') === 'amount_desc')>بیشترین مبلغ</option>
                                <option value="amount_asc" @selected(request('sort') === 'amount_asc')>کمترین مبلغ</option>
                                <option value="oldest" @selected(request('sort') === 'oldest')>قدیمی‌ترین</option>
                            </select>
                        </div>

                        {{-- 9. Per Page --}}
                        <div>
                            <label class="{{ $labelClass }}">تعداد در صفحه</label>
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
                                <a href="{{ route('user.wallet.transactions.index') }}"
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
                            <a href="{{ route('user.wallet.transactions.index', request()->except(['page', 'search'])) }}" class="text-gray-400 hover:text-rose-500 dark:text-gray-400 dark:hover:text-rose-400 mr-1 font-bold">×</a>
                        </span>
                    @endif

                    @if(request('wallet_id'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 shadow-2xs">
                            کیف پول: <strong class="text-indigo-600 dark:text-indigo-400">#{{ request('wallet_id') }}</strong>
                            <a href="{{ route('user.wallet.transactions.index', request()->except(['page', 'wallet_id'])) }}" class="text-gray-400 hover:text-rose-500 dark:text-gray-400 dark:hover:text-rose-400 mr-1 font-bold">×</a>
                        </span>
                    @endif

                    @if(request('flow'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 shadow-2xs">
                            جریان: <strong class="text-indigo-600 dark:text-indigo-400">{{ request('flow') === 'inflow' ? 'واریزی / ورودی' : 'برداشتی / خروجی' }}</strong>
                            <a href="{{ route('user.wallet.transactions.index', request()->except(['page', 'flow'])) }}" class="text-gray-400 hover:text-rose-500 dark:text-gray-400 dark:hover:text-rose-400 mr-1 font-bold">×</a>
                        </span>
                    @endif

                    @if(request('type'))
                        @php
                            $matchedType = collect($types)->first(fn($t) => $t->value === request('type'));
                        @endphp
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 shadow-2xs">
                            نوع: <strong class="text-indigo-600 dark:text-indigo-400">{{ $matchedType ? $matchedType->label() : request('type') }}</strong>
                            <a href="{{ route('user.wallet.transactions.index', request()->except(['page', 'type'])) }}" class="text-gray-400 hover:text-rose-500 dark:text-gray-400 dark:hover:text-rose-400 mr-1 font-bold">×</a>
                        </span>
                    @endif

                    @if(request('status'))
                        @php
                            $matchedStatus = collect($statuses)->first(fn($s) => $s->value === request('status'));
                        @endphp
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 shadow-2xs">
                            وضعیت: <strong class="text-indigo-600 dark:text-indigo-400">{{ $matchedStatus ? $matchedStatus->label() : request('status') }}</strong>
                            <a href="{{ route('user.wallet.transactions.index', request()->except(['page', 'status'])) }}" class="text-gray-400 hover:text-rose-500 dark:text-gray-400 dark:hover:text-rose-400 mr-1 font-bold">×</a>
                        </span>
                    @endif

                    @if(request('holder_type'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 shadow-2xs">
                            دارنده: <strong class="text-indigo-600 dark:text-indigo-400">{{ request('holder_type') === 'user' ? 'کاربر سیستم' : 'کلاینت' }}</strong>
                            <a href="{{ route('user.wallet.transactions.index', request()->except(['page', 'holder_type'])) }}" class="text-gray-400 hover:text-rose-500 dark:text-gray-400 dark:hover:text-rose-400 mr-1 font-bold">×</a>
                        </span>
                    @endif

                    @if(request('min_amount'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 shadow-2xs">
                            حداقل مبلغ: <strong class="text-indigo-600 dark:text-indigo-400">{{ request('min_amount') }}</strong>
                            <a href="{{ route('user.wallet.transactions.index', request()->except(['page', 'min_amount'])) }}" class="text-gray-400 hover:text-rose-500 dark:text-gray-400 dark:hover:text-rose-400 mr-1 font-bold">×</a>
                        </span>
                    @endif

                    @if(request('max_amount'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 shadow-2xs">
                            حداکثر مبلغ: <strong class="text-indigo-600 dark:text-indigo-400">{{ request('max_amount') }}</strong>
                            <a href="{{ route('user.wallet.transactions.index', request()->except(['page', 'max_amount'])) }}" class="text-gray-400 hover:text-rose-500 dark:text-gray-400 dark:hover:text-rose-400 mr-1 font-bold">×</a>
                        </span>
                    @endif

                    @if(request('sort') && request('sort') !== 'latest')
                        @php
                            $sortLabels = [
                                'amount_desc' => 'بیشترین مبلغ',
                                'amount_asc'  => 'کمترین مبلغ',
                                'oldest'       => 'قدیمی‌ترین',
                            ];
                        @endphp
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 shadow-2xs">
                            مرتب‌سازی: <strong class="text-indigo-600 dark:text-indigo-400">{{ $sortLabels[request('sort')] ?? request('sort') }}</strong>
                            <a href="{{ route('user.wallet.transactions.index', request()->except(['page', 'sort'])) }}" class="text-gray-400 hover:text-rose-500 dark:text-gray-400 dark:hover:text-rose-400 mr-1 font-bold">×</a>
                        </span>
                    @endif

                    @if(request('per_page') && request('per_page') != '20')
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 shadow-2xs">
                            نمایش: <strong class="text-indigo-600 dark:text-indigo-400">{{ request('per_page') }} تایی</strong>
                            <a href="{{ route('user.wallet.transactions.index', request()->except(['page', 'per_page'])) }}" class="text-gray-400 hover:text-rose-500 dark:text-gray-400 dark:hover:text-rose-400 mr-1 font-bold">×</a>
                        </span>
                    @endif
                </div>
            @endif

        </div>

        {{-- Transactions Table Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-xs overflow-hidden">
            
            {{-- Table Toolbar --}}
            <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/40 dark:bg-gray-900/30">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-400 animate-pulse"></span>
                    <h2 class="text-sm font-black text-gray-900 dark:text-white">
                        ریز تراکنش‌های ثبت‌شده
                    </h2>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                        صفحه {{ $transactions->currentPage() }} از {{ $transactions->lastPage() ?: 1 }}
                    </span>
                </div>

                <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                    نمایش {{ $transactions->firstItem() ?? 0 }} تا {{ $transactions->lastItem() ?? 0 }} از مجموع {{ $transactions->total() }} تراکنش
                </div>
            </div>

            @if($transactions->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/80">
                        <thead class="bg-gray-50/80 dark:bg-gray-900/60">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    کد پیگیری
                                </th>
                                <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    دارنده حساب و کیف پول
                                </th>
                                <th scope="col" class="px-5 py-3.5 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    نوع تراکنش
                                </th>
                                <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    مبلغ تراکنش
                                </th>
                                <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    گردش موجودی
                                </th>
                                <th scope="col" class="px-5 py-3.5 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    وضعیت
                                </th>
                                <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    شرح و توضیحات
                                </th>
                                <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    تاریخ و ساعت
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 bg-white dark:bg-gray-800">
                            @foreach($transactions as $tx)
                                @php
                                    $isPositive = in_array($tx->type->value, ['deposit', 'refund', 'commission', 'bonus']);
                                    $wallet = $tx->wallet;
                                    $holder = $wallet?->holder;
                                    $isUser = $wallet && ($wallet->holder_type === (new \App\Models\User())->getMorphClass() || ($holder instanceof \App\Models\User));
                                    $holderName = $holder 
                                        ? ($isUser ? ($holder->name ?? ('کاربر #' . $wallet->holder_id)) : ($holder->full_name ?? ($holder->username ?? ('کلاینت #' . $wallet->holder_id))))
                                        : 'نامشخص';
                                    $holderPhone = $isUser ? ($holder->mobile ?? '') : ($holder->phone ?? '');
                                    $txCurr = ($wallet && ($wallet->currency === 'rial' || $wallet->currency === 'IRR')) ? 'ریال' : ($currencyLabel ?? 'تومان');
                                @endphp
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition-colors group">
                                    
                                    {{-- 1. UUID / Tracking Code --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700/70 text-gray-700 dark:text-gray-300 text-xs font-bold tracking-wider"
                                                  title="{{ $tx->uuid }}">
                                                {{ Str::limit($tx->uuid, 10, '..') }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- 2. Holder & Wallet Info --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-black text-xs shrink-0
                                                {{ $isUser ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' }}">
                                                {{ mb_substr($holderName, 0, 1, 'utf-8') }}
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xs font-bold text-gray-900 dark:text-white">
                                                        {{ $holderName }}
                                                    </span>
                                                    @if($holder)
                                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold border
                                                            {{ $isUser ? 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/50 dark:text-purple-300 dark:border-purple-800' : 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800' }}">
                                                            {{ $isUser ? 'کاربر' : 'کلاینت' }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-1">
                                                    @if($wallet)
                                                        <a href="{{ route('user.wallet.transactions.index', ['wallet_id' => $wallet->id]) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                                            {{ $wallet->name ?: $wallet->slug }}
                                                        </a>
                                                    @endif
                                                    @if($holderPhone)
                                                        <span class="text-gray-300 dark:text-gray-600">•</span>
                                                        <span class="dir-ltr text-right">{{ $holderPhone }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- 3. Type --}}
                                    <td class="px-5 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold border
                                            {{ $isPositive ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60' : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/60' }}">
                                            {{ $tx->type->label() }}
                                        </span>
                                    </td>

                                    {{-- 4. Amount --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="inline-flex items-baseline gap-1 font-black text-sm
                                            {{ $isPositive ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                            <span class="text-base tabular-nums">{{ $isPositive ? '+' : '-' }}{{ number_format((float)$tx->amount) }}</span>
                                            <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400">{{ $txCurr }}</span>
                                        </div>
                                    </td>

                                    {{-- 5. Balance Flow (Before -> After) --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5 text-xs">
                                            <span class="text-gray-500 dark:text-gray-400 tabular-nums">{{ number_format((float)$tx->balance_before) }}</span>
                                            <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" /></svg>
                                            <span class="font-bold text-gray-900 dark:text-white tabular-nums">{{ number_format((float)$tx->balance_after) }}</span>
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400">{{ $txCurr }}</span>
                                        </div>
                                    </td>

                                    {{-- 6. Status --}}
                                    <td class="px-5 py-4 whitespace-nowrap text-center">
                                        @if($tx->status->value === 'completed')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                موفق
                                            </span>
                                        @elseif($tx->status->value === 'pending')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/60">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                در انتظار
                                            </span>
                                        @elseif($tx->status->value === 'failed')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/60">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                ناموفق
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 text-gray-600 border border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                                                لغو شده
                                            </span>
                                        @endif
                                    </td>

                                    {{-- 7. Description & Reference --}}
                                    <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300 max-w-xs">
                                        <div class="font-medium truncate" title="{{ $tx->description }}">
                                            {{ $tx->description ?: '—' }}
                                        </div>
                                        @if($tx->payable)
                                            <div class="text-[10px] text-indigo-600 dark:text-indigo-400 mt-0.5 font-bold">
                                                مرجع: {{ class_basename($tx->payable_type) }} #{{ $tx->payable_id }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- 8. Date & Time --}}
                                    <td class="px-5 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                        <div class="font-medium">
                                            {{ $tx->created_at ? (class_exists(\Morilog\Jalali\Jalalian::class) ? \Morilog\Jalali\Jalalian::fromCarbon($tx->created_at)->format('Y/m/d H:i') : $tx->created_at->format('Y-m-d H:i')) : '—' }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Footer --}}
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/80 bg-gray-50/50 dark:bg-gray-900/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        نمایش {{ $transactions->firstItem() ?? 0 }} تا {{ $transactions->lastItem() ?? 0 }} از مجموع {{ $transactions->total() }} تراکنش
                    </div>
                    <div>
                        {{ $transactions->links() }}
                    </div>
                </div>
            @else
                {{-- Empty State --}}
                <div class="py-16 px-6 text-center space-y-4">
                    <div class="w-16 h-16 mx-auto rounded-3xl bg-gray-100 dark:bg-gray-700/60 text-gray-400 dark:text-gray-400 flex items-center justify-center shadow-inner">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="max-w-sm mx-auto space-y-1">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">هیچ تراکنشی با این شرایط یافت نشد!</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @if($isFiltered)
                                فیلترهای جستجو بیش از حد محدودکننده هستند. برای مشاهده تمامی تراکنش‌ها فیلترها را حذف کنید.
                            @else
                                تا این لحظه هیچ تراکنش مالی در سیستم ثبت نشده است.
                            @endif
                        </p>
                    </div>
                    @if($isFiltered)
                        <div>
                            <a href="{{ route('user.wallet.transactions.index') }}"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 dark:bg-indigo-950/50 dark:text-indigo-300 dark:border-indigo-800 transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                <span>حذف فیلترها و مشاهده همه تراکنش‌ها</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endif

        </div>

    </div>
@endsection
