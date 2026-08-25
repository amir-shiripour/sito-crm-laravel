@extends('layouts.user')

@section('content')
    @php
        $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900";
        $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";
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

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300 shadow-xs">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </span>
                    مدیریت کیف پول‌ها
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mr-11">
                    لیست کیف پول‌های فعال سیستمی، کلاینت‌ها و مدیریت موجودی حساب‌ها (واحد پول: {{ $currencyLabel ?? 'تومان' }})
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                @can('wallet.transactions.view')
                    <a href="{{ route('user.wallet.transactions.index') }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 hover:border-gray-300 shadow-xs transition-all dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        <span>تراکنش‌های مالی</span>
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

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 shadow-xs">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="{{ $labelClass }}">جستجو در لیست</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="نام کیف پول، اسلاگ، دارنده..."
                               class="{{ $inputClass }} pl-8">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>

                <div>
                    <label class="{{ $labelClass }}">نوع دارنده</label>
                    <select name="holder_type" class="{{ $inputClass }}">
                        <option value="">همه دارندگان</option>
                        <option value="user" @selected(request('holder_type') === 'user')>کاربران سیستم (User)</option>
                        <option value="client" @selected(request('holder_type') === 'client')>کلاینت‌ها (Client)</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-600 border border-indigo-100 hover:bg-indigo-100 transition-colors dark:bg-indigo-900/30 dark:border-indigo-800 dark:text-indigo-300">
                        اعمال فیلتر
                    </button>
                    @if(request()->anyFilled(['search', 'holder_type']))
                        <a href="{{ route('user.wallet.index') }}"
                           class="px-3.5 py-2.5 rounded-xl text-xs font-medium border border-gray-200 text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-750">
                            حذف فیلتر
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-xs">
            @if($wallets->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                        <thead class="bg-gray-50/80 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">شناسه</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">دارنده حساب</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">نام کیف پول / شناسه</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">موجودی فعلی</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">وضعیت</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">عملیات</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        @foreach($wallets as $wallet)
                            @php
                                $holderName = '—';
                                $holderPhone = '';
                                $holderBadge = 'سیستم';
                                $isUser = str_contains($wallet->holder_type, 'User');
                                if ($wallet->holder) {
                                    $holderName = $wallet->holder->name ?? $wallet->holder->full_name ?? $wallet->holder->username ?? 'نامشخص';
                                    $holderPhone = $wallet->holder->phone ?? $wallet->holder->mobile ?? '';
                                    $holderBadge = $isUser ? 'کاربر سیستم' : 'کلاینت';
                                }
                                $walletCurr = ($wallet->currency === 'rial' || $wallet->currency === 'IRR') ? 'ریال' : 'تومان';
                            @endphp
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold text-gray-400">
                                    #{{ $wallet->id }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black {{ $isUser ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' }}">
                                            {{ mb_substr($holderName, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $isUser ? 'bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-300' : 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300' }}">
                                                    {{ $holderBadge }}
                                                </span>
                                                <span class="text-sm font-bold text-gray-900 dark:text-white">
                                                    {{ $holderName }}
                                                </span>
                                            </div>
                                            @if($holderPhone)
                                                <span class="text-[11px] text-gray-400 dark:text-gray-500 font-mono block mt-0.5">{{ $holderPhone }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-xs font-medium text-gray-700 dark:text-gray-300">
                                    <span class="font-bold">{{ $wallet->name ?? $wallet->slug }}</span>
                                    <span class="text-gray-400 font-mono block text-[11px]">({{ $wallet->slug }})</span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-base font-black {{ (float)$wallet->balance > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-400' }}">
                                            {{ number_format((float)$wallet->balance) }}
                                        </span>
                                        <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400">{{ $walletCurr }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($wallet->is_active)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            فعال
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            مسدود / غیرفعال
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @can('wallet.deposit')
                                            <button type="button"
                                                    @click="openModal('deposit', {
                                                        id: {{ $wallet->holder_id }},
                                                        holder_type: '{{ $isUser ? 'user' : 'client' }}',
                                                        holder_name: '{{ addslashes($holderName) }}',
                                                        phone: '{{ $holderPhone }}',
                                                        balance: {{ (float)$wallet->balance }},
                                                        currency: '{{ $wallet->currency }}',
                                                        currency_label: '{{ $walletCurr }}',
                                                        is_active: {{ $wallet->is_active ? 'true' : 'false' }}
                                                    })"
                                                    class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/40 transition-colors"
                                                    title="شارژ مستقیم این کیف پول">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                            </button>
                                        @endcan

                                        @can('wallet.withdraw')
                                            <button type="button"
                                                    @click="openModal('withdraw', {
                                                        id: {{ $wallet->holder_id }},
                                                        holder_type: '{{ $isUser ? 'user' : 'client' }}',
                                                        holder_name: '{{ addslashes($holderName) }}',
                                                        phone: '{{ $holderPhone }}',
                                                        balance: {{ (float)$wallet->balance }},
                                                        currency: '{{ $wallet->currency }}',
                                                        currency_label: '{{ $walletCurr }}',
                                                        is_active: {{ $wallet->is_active ? 'true' : 'false' }}
                                                    })"
                                                    class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40 transition-colors"
                                                    title="کسر موجودی از این کیف پول">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                            </button>
                                        @endcan

                                        @can('wallet.manage')
                                            <form action="{{ route('user.wallet.toggle-status', $wallet->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold border transition-all active:scale-95 {{ $wallet->is_active ? 'bg-amber-50 text-amber-700 border-amber-200/80 hover:bg-amber-100 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/60 dark:hover:bg-amber-900/50' : 'bg-emerald-50 text-emerald-700 border-emerald-200/80 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60 dark:hover:bg-emerald-900/50' }}"
                                                        title="{{ $wallet->is_active ? 'مسدودسازی و غیرفعال‌سازی کیف پول' : 'فعال‌سازی مجدد کیف پول' }}">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $wallet->is_active ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                                                    <span>{{ $wallet->is_active ? 'غیرفعال‌سازی' : 'فعال‌سازی' }}</span>
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

                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                    {{ $wallets->links() }}
                </div>
            @else
                <div class="py-16 text-center text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    هیچ کیف پولی ثبت نشده است.
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
                            <p class="text-xs text-gray-400 dark:text-gray-400 mt-0.5"
                               x-text="mode === 'deposit' ? 'واریز دستی وجه به کیف پول کلاینت یا کاربر' : 'برداشت دستی وجه از موجودی کیف پول کلاینت یا کاربر'"></p>
                        </div>
                    </div>
                    <button type="button" @click="closeModal()" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {{-- Form --}}
                <form :action="mode === 'deposit' ? '{{ route('user.wallet.deposit') }}' : '{{ route('user.wallet.withdraw') }}'"
                      method="POST"
                      class="space-y-4"
                      @submit="return validateSubmit($event)">
                    @csrf

                    {{-- Section 1: Searchable Holder Selection --}}
                    <div>
                        <label class="{{ $labelClass }}">انتخاب دارنده حساب (کلاینت / کاربر) *</label>

                        {{-- If holder is selected --}}
                        <template x-if="selectedHolder">
                            <div class="p-3.5 rounded-2xl border transition-all flex items-center justify-between"
                                 :class="mode === 'deposit' ? 'bg-emerald-50/60 border-emerald-200 text-emerald-950 dark:bg-emerald-950/30 dark:border-emerald-800/60 dark:text-emerald-100' : 'bg-rose-50/60 border-rose-200 text-rose-950 dark:bg-rose-950/30 dark:border-rose-800/60 dark:text-rose-100'">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm"
                                         :class="selectedHolder.holder_type === 'user' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300'">
                                        <span x-text="selectedHolder.holder_name.charAt(0)"></span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold"
                                                  :class="selectedHolder.holder_type === 'user' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300'"
                                                  x-text="selectedHolder.holder_type === 'user' ? 'کاربر سیستم' : 'کلاینت'"></span>
                                            <span class="font-bold text-sm text-gray-900 dark:text-white" x-text="selectedHolder.holder_name"></span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            <span x-show="selectedHolder.phone" x-text="selectedHolder.phone" class="font-mono"></span>
                                            <span class="text-gray-300 dark:text-gray-600">•</span>
                                            <span>موجودی فعلی:</span>
                                            <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="formatNumber(selectedHolder.balance) + ' ' + (selectedHolder.currency_label || activeCurrencyLabel)"></span>
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
                            <div class="flex items-center gap-1 mb-2 bg-gray-100 dark:bg-gray-900 p-1 rounded-xl">
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
                                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                                <span class="text-[10px] text-gray-400 dark:text-gray-500 block font-mono mt-0.5" x-text="item.phone"></span>
                                            </div>
                                        </div>
                                        <div class="text-left">
                                            <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 block" x-text="formatNumber(item.balance) + ' ' + (item.currency_label || activeCurrencyLabel)"></span>
                                            <span class="text-[9px] text-gray-400 dark:text-gray-500">موجودی فعلی</span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Loading state --}}
                            <div x-show="isLoading" class="absolute left-9 top-12 text-xs text-gray-400 flex items-center gap-1">
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
                                <span class="text-gray-400 dark:text-gray-500 text-[11px] font-normal" x-text="'(به ' + activeCurrencyLabel + ')'"></span>
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
                                   required
                                   class="{{ $inputClass }} pl-14 font-bold text-base">
                            <span class="absolute left-3.5 top-2.5 text-xs font-bold text-gray-400 dark:text-gray-500" x-text="activeCurrencyLabel"></span>
                        </div>

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
                                        class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700/60 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                                    <span x-text="'+ ' + chip.label"></span>
                                </button>
                            </template>
                            <button type="button" @click="amount = ''" class="px-2 py-1 rounded-lg text-[10px] font-bold text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
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
                            <span class="font-bold" x-text="formatNumber(selectedHolder ? selectedHolder.balance : 0) + ' ' + (selectedHolder.currency_label || activeCurrencyLabel)"></span>
                        </div>
                        <div class="flex items-center justify-between font-bold pt-1 border-t border-gray-200/70 dark:border-gray-700/70">
                            <span x-text="mode === 'deposit' ? 'موجودی پس از شارژ:' : 'موجودی پس از کسر:'"></span>
                            <span class="text-sm"
                                  :class="mode === 'deposit' ? 'text-emerald-600 dark:text-emerald-400' : (isWithdrawExceeded() ? 'text-rose-600 dark:text-rose-400' : 'text-blue-600 dark:text-blue-400')"
                                  x-text="formatNumber(calculateProjectedBalance()) + ' ' + (selectedHolder.currency_label || activeCurrencyLabel)"></span>
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
                                        class="px-2 py-0.5 rounded-md text-[10px] font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700/60 dark:text-gray-300 dark:hover:bg-gray-700 transition"
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
                    const clean = String(val).replace(/,/g, '').trim();
                    if (clean === '') return '';
                    const num = Math.round(parseFloat(clean));
                    if (isNaN(num)) return '';
                    return String(num).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                },

                unformatNumber(val) {
                    if (val === null || val === undefined || val === '') return 0;
                    const clean = String(val).replace(/,/g, '').trim();
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
