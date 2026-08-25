@extends('layouts.user')

@section('content')
    @php
        $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900";
        $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";
    @endphp

    <div class="w-full mx-auto px-4 py-8 space-y-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l-4 -4l4 -4" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10h11a4 4 0 1 1 0 8h-1" />
                        </svg>
                    </span>
                    تراکنش‌های مالی سیستم
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">
                    دفتر کل تراکنش‌های واریز، برداشت، پرداخت فاکتورها و پورسانت‌ها
                </p>
            </div>

            <a href="{{ route('user.wallet.index') }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all dark:bg-gray-700 dark:text-gray-200">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                <span>بازگشت به لیست کیف‌ پول‌ها</span>
            </a>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="{{ $labelClass }}">جستجو</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="کد پیگیری UUID، توضیحات..."
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">نوع تراکنش</label>
                    <select name="type" class="{{ $inputClass }}">
                        <option value="">همه انواع</option>
                        @foreach($types as $t)
                            <option value="{{ $t->value }}" @selected(request('type') === $t->value)>{{ $t->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">وضعیت</label>
                    <select name="status" class="{{ $inputClass }}">
                        <option value="">همه وضعیت‌ها</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="flex-1 px-4 py-2 rounded-xl text-sm font-bold bg-indigo-50 text-indigo-600 border border-indigo-100 hover:bg-indigo-100 transition-colors dark:bg-indigo-900/30 dark:border-indigo-800 dark:text-indigo-300">
                        فیلتر
                    </button>
                    @if(request()->anyFilled(['search', 'type', 'status']))
                        <a href="{{ route('user.wallet.transactions.index') }}"
                           class="px-3 py-2 rounded-xl text-sm font-medium border border-gray-200 text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">
                            حذف فیلتر
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Transactions Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
            @if($transactions->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                        <thead class="bg-gray-50/80 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">کد پیگیری</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">کیف پول / دارنده</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">نوع</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">مبلغ ({{ $currencyLabel ?? 'تومان' }})</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">موجودی جدید</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">توضیحات</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">تاریخ</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        @foreach($transactions as $tx)
                            @php
                                $isPositive = in_array($tx->type->value, ['deposit', 'refund', 'commission', 'bonus']);
                                $holderName = '—';
                                if ($tx->wallet && $tx->wallet->holder) {
                                    $holderName = $tx->wallet->holder->name ?? $tx->wallet->holder->full_name ?? $tx->wallet->holder->username ?? 'نامشخص';
                                }
                                $txCurr = ($tx->wallet && ($tx->wallet->currency === 'rial' || $tx->wallet->currency === 'IRR')) ? 'ریال' : ($currencyLabel ?? 'تومان');
                            @endphp
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-mono">
                                    {{ Str::limit($tx->uuid, 8) }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $holderName }}
                                    <span class="block text-xs text-gray-400 font-normal">({{ $tx->wallet->name ?? $tx->wallet->slug }})</span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold {{ $isPositive ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border dark:border-emerald-800/60' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 dark:border dark:border-rose-800/60' }}">
                                        {{ $tx->type->label() }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $isPositive ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ $isPositive ? '+' : '-' }}{{ number_format((float)$tx->amount) }}
                                    <span class="text-[11px] font-normal text-gray-400 mr-0.5">{{ $txCurr }}</span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ number_format((float)$tx->balance_after) }}
                                    <span class="text-[11px] font-normal text-gray-400 mr-0.5">{{ $txCurr }}</span>
                                </td>

                                <td class="px-6 py-4 text-xs text-gray-600 dark:text-gray-300 max-w-xs truncate">
                                    {{ $tx->description ?? '—' }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-mono">
                                    {{ $tx->created_at ? jdate($tx->created_at)->format('Y/m/d H:i') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    {{ $transactions->links() }}
                </div>
            @else
                <div class="py-16 text-center text-gray-500 dark:text-gray-400">
                    هیچ تراکنشی یافت نشد.
                </div>
            @endif
        </div>
    </div>
@endsection
