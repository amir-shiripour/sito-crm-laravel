@extends('layouts.user')

@section('content')
    @php
        $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900";
        $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";
    @endphp

    <div class="w-full mx-auto px-4 py-8 space-y-6">

        {{-- Alerts --}}
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-sm font-bold flex items-center justify-between dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800">
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 rounded-xl bg-red-50 text-red-700 border border-red-200 text-sm font-bold flex items-center justify-between dark:bg-red-900/30 dark:text-red-300 dark:border-red-800">
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </span>
                    مدیریت کیف پول‌ها
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">
                    لیست کیف پول‌های فعال سیستمی، کلاینت‌ها و موجودی حساب‌ها
                </p>
            </div>

            <div class="flex items-center gap-3">
                @can('wallet.transactions.view')
                    <a href="{{ route('user.wallet.transactions.index') }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all dark:bg-gray-700 dark:text-gray-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        <span>تراکنش‌های مالی</span>
                    </a>
                @endcan

                @can('wallet.deposit')
                    <button type="button" onclick="document.getElementById('depositModal').classList.remove('hidden')"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-lg shadow-emerald-500/20 transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                        <span>شارژ / واریز</span>
                    </button>
                @endcan
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="{{ $labelClass }}">جستجو</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="نام کیف پول، نوع..."
                           class="{{ $inputClass }}">
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
                            class="flex-1 px-4 py-2 rounded-xl text-sm font-bold bg-indigo-50 text-indigo-600 border border-indigo-100 hover:bg-indigo-100 transition-colors dark:bg-indigo-900/30 dark:border-indigo-800 dark:text-indigo-300">
                        فیلتر
                    </button>
                    @if(request()->anyFilled(['search', 'holder_type']))
                        <a href="{{ route('user.wallet.index') }}"
                           class="px-3 py-2 rounded-xl text-sm font-medium border border-gray-200 text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">
                            حذف فیلتر
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
            @if($wallets->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">شناسه</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">دارنده حساب</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">نام کیف پول / شناسه</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">موجودی فعلی</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">وضعیت</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">عملیات</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        @foreach($wallets as $wallet)
                            @php
                                $holderName = '—';
                                $holderBadge = 'سیستم';
                                if ($wallet->holder) {
                                    $holderName = $wallet->holder->name ?? $wallet->holder->full_name ?? $wallet->holder->username ?? 'نامشخص';
                                    $holderBadge = str_contains($wallet->holder_type, 'User') ? 'کاربر سیستم' : 'کلاینت';
                                }
                            @endphp
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    #{{ $wallet->id }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ str_contains($wallet->holder_type, 'User') ? 'bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-300' : 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300' }}">
                                            {{ $holderBadge }}
                                        </span>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">
                                            {{ $holderName }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $wallet->name ?? $wallet->slug }}
                                    <span class="text-xs text-gray-400">({{ $wallet->slug }})</span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-base text-emerald-600 dark:text-emerald-400">
                                        {{ number_format((float)$wallet->balance) }}
                                    </span>
                                    <span class="text-xs text-gray-500 mr-1">{{ $wallet->currency }}</span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($wallet->is_active)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800">
                                            فعال
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800">
                                            مسدود / غیرفعال
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @can('wallet.manage')
                                        <form action="{{ route('user.wallet.toggle-status', $wallet->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $wallet->is_active ? 'bg-amber-50 text-amber-600 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-300' }}">
                                                {{ $wallet->is_active ? 'غیرفعال‌سازی' : 'فعال‌سازی' }}
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    {{ $wallets->links() }}
                </div>
            @else
                <div class="py-16 text-center text-gray-500 dark:text-gray-400">
                    هیچ کیف پولی ثبت نشده است.
                </div>
            @endif
        </div>
    </div>

    {{-- Deposit Modal --}}
    <div id="depositModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-200 dark:border-gray-700 space-y-4">
            <div class="flex items-center justify-between border-b pb-3 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">شارژ دستی کیف پول</h3>
                <button type="button" onclick="document.getElementById('depositModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form action="{{ route('user.wallet.deposit') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="{{ $labelClass }}">نوع دارنده حساب</label>
                    <select name="holder_type" class="{{ $inputClass }}" required>
                        <option value="client">کلاینت (Client)</option>
                        <option value="user">کاربر سیستم (User)</option>
                    </select>
                </div>
                <div>
                    <label class="{{ $labelClass }}">شناسه عددی (ID)</label>
                    <input type="number" name="holder_id" class="{{ $inputClass }}" placeholder="مثلا 1" required>
                </div>
                <div>
                    <label class="{{ $labelClass }}">مبلغ (ریال / تومان)</label>
                    <input type="number" name="amount" class="{{ $inputClass }}" min="1000" step="1000" placeholder="مثلا 500000" required>
                </div>
                <div>
                    <label class="{{ $labelClass }}">توضیحات</label>
                    <input type="text" name="description" class="{{ $inputClass }}" placeholder="دلیل شارژ دستی">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('depositModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-sm font-bold border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300">انصراف</button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-sm font-bold bg-emerald-600 text-white hover:bg-emerald-700">ثبت شارژ</button>
                </div>
            </form>
        </div>
    </div>
@endsection
