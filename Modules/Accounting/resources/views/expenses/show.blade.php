@extends('layouts.user')

@section('title', 'جزئیات سند هزینه ' . ($expense->reference_number ?: $expense->id))

@php
    use Carbon\Carbon;
    use Modules\Accounting\App\Models\AccountingSetting;
    use Morilog\Jalali\Jalalian;
    use Modules\Accounting\App\Services\CurrencyService;

    $currencyLabel = CurrencyService::getBaseCurrency();

    $faNum = function($str) {
        if (is_null($str)) return '—';
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $toJalali = function ($date) {
        if (!$date) return null;
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        return Jalalian::fromCarbon($carbon);
    };

    $debitTxs = $expense->transactions->where('debit', '>', 0);
    $creditTxs = $expense->transactions->where('credit', '>', 0);
    $totalAmount = $debitTxs->sum('debit') ?: $expense->amount;

    $bankFeeCatId = (int)AccountingSetting::get('defaults.bank_fee_category_id', 26);
    $feeTx = $debitTxs->first(fn($t) => $t->category_id == $bankFeeCatId || str_contains($t->description, 'کارمزد'));
    $mainExpenseTx = $debitTxs->first(fn($t) => !$feeTx || $t->id !== $feeTx->id) ?? $debitTxs->first();

    $mainExpenseAmount = $mainExpenseTx?->debit ?? $totalAmount;
    $bankFeeAmount = $feeTx?->debit ?? 0;
    $categoryName = $mainExpenseTx?->category?->title ?? 'سرفصل عمومی';

    $cheques = $expense->cheques->count() > 0 ? $expense->cheques : ($expense->cheque ? collect([$expense->cheque]) : collect());
    $chequeTxs = $creditTxs->whereNull('fund_account_id');
    $bankTxs = $creditTxs->whereNotNull('fund_account_id');

    $chequePaidAmount = $chequeTxs->sum('credit');
    if ($chequePaidAmount == 0 && $cheques->isNotEmpty()) {
        $chequePaidAmount = min($cheques->sum('amount'), $totalAmount);
    }
    $bankPaidAmount = $bankTxs->sum('credit');

    if ($chequePaidAmount > 0 && $bankPaidAmount > 0) {
        $paymentMethodLabel = 'ترکیبی (چک + خزانه‌داری)';
        $paymentBadgeClass = 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400 border-purple-200 dark:border-purple-500/20';
    } elseif ($chequePaidAmount > 0 || $cheques->isNotEmpty()) {
        $paymentMethodLabel = 'پرداخت با چک صیادی (' . $faNum($cheques->count()) . ' چک)';
        $paymentBadgeClass = 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border-amber-200 dark:border-amber-500/20';
    } else {
        $paymentMethodLabel = 'پرداخت از خزانه‌داری (بانک / صندوق)';
        $paymentBadgeClass = 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 border-indigo-200 dark:border-indigo-500/20';
    }

    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-6 backdrop-blur-xl";
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-24">

        {{-- Header Section --}}
        <div
            class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-800/80 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-xs backdrop-blur-xl">
            <div class="flex items-start gap-4">
                <div
                    class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-rose-500 to-rose-700 text-white shadow-lg shadow-rose-500/30 shrink-0">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                            {{ $expense->description }}
                        </h1>
                        @if($expense->status === 'cancelled')
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                لغو شده
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                فعال / ثبت‌شده
                            </span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
                        <span>شماره سند: <strong
                                class="text-gray-700 dark:text-gray-200 tabular-nums font-bold">{{ $faNum($expense->document_number ?: ('EXP-' . $expense->id)) }}</strong></span>
                        <span class="text-gray-300 dark:text-gray-600">•</span>
                        <span>تاریخ پرداخت: <strong
                                class="text-gray-700 dark:text-gray-200 tabular-nums">{{ $faNum($toJalali($expense->document_date)?->format('Y/m/d')) }}</strong></span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-wrap">

                @if($expense->status !== 'cancelled')
                    <a href="{{ route('admin.accounting.expenses.edit', $expense->id) }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-500/20 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 text-sm font-bold transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        ویرایش هزینه
                    </a>

                    <form action="{{ route('admin.accounting.expenses.cancel', $expense->id) }}" method="POST"
                          onsubmit="return confirm('آیا از لغو این هزینه اطمینان دارید؟ مبلغ به خزانه‌داری بازگردانده می‌شود.');"
                          class="inline-block">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-200/60 dark:border-rose-500/20 hover:bg-rose-100 dark:hover:bg-rose-500/20 text-sm font-bold transition-all active:scale-95">
                            <svg class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                            لغو هزینه و بازگشت وجه
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.accounting.expenses.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-bold transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                    بازگشت به لیست
                </a>
            </div>
        </div>

        {{-- Top KPI Cards Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Card 1: Total Amount --}}
            <div
                class="{{ $cardClass }} bg-gradient-to-br from-rose-500/5 to-rose-600/10 border-rose-100 dark:border-rose-500/20">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide">مبلغ کل پرداختی</span>
                    <span class="p-2.5 rounded-xl bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span
                        class="text-2xl xl:text-3xl font-black text-gray-900 dark:text-white tabular-nums tracking-tight">
                        {{ $faNum(number_format($totalAmount)) }}
                    </span>
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $currencyLabel }}</span>
                </div>
                @if($bankFeeAmount > 0)
                    <div
                        class="mt-2.5 pt-2.5 border-t border-rose-100/80 dark:border-rose-500/20 text-[11px] font-bold text-gray-500 dark:text-gray-400 flex items-center justify-between flex-wrap gap-1">
                        <span>اصل: {{ $faNum(number_format($mainExpenseAmount)) }}</span>
                        <span
                            class="text-indigo-600 dark:text-indigo-400">+ کارمزد: {{ $faNum(number_format($bankFeeAmount)) }}</span>
                    </div>
                @endif
            </div>

            {{-- Card 2: Payment Method --}}
            <div class="{{ $cardClass }}">
                <div class="flex items-center justify-between mb-3">
                    <span
                        class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide">روش پرداخت</span>
                    <span
                        class="p-2.5 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </span>
                </div>
                <div>
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border {{ $paymentBadgeClass }}">
                        {{ $paymentMethodLabel }}
                    </span>
                </div>
            </div>

            {{-- Card 3: Expense Category --}}
            <div class="{{ $cardClass }}">
                <div class="flex items-center justify-between mb-3">
                    <span
                        class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide">سرفصل هزینه</span>
                    <span class="p-2.5 rounded-xl bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10M7 12h10m-8 5h8"/>
                        </svg>
                    </span>
                </div>
                <span class="text-lg font-black text-gray-900 dark:text-white truncate block">
                    {{ $categoryName }}
                </span>
            </div>

            {{-- Card 4: Reference Number --}}
            <div class="{{ $cardClass }}">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide">شماره پیگیری / فیش</span>
                    <span
                        class="p-2.5 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </span>
                </div>
                <span class="text-base font-black text-gray-900 dark:text-white block truncate tabular-nums">
                    {{ $faNum($expense->reference_number) ?: '—' }}
                </span>
            </div>
        </div>

        {{-- Payment Sources Breakdown Card --}}
        <div class="{{ $cardClass }} space-y-6">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-4">
                <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    جزئیات منابع مالی پرداخت‌شده
                </h3>
                <span class="text-xs font-bold text-gray-400">مجموع پرداختی: <strong
                        class="text-gray-800 dark:text-gray-200 tabular-nums">{{ $faNum(number_format($totalAmount)) }} {{ $currencyLabel }}</strong></span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Cheque Details Card if any --}}
                @if($cheques->isNotEmpty() || $chequePaidAmount > 0)
                    @php
                        $chequePercent = $totalAmount > 0 ? round(($chequePaidAmount / $totalAmount) * 100) : 0;
                    @endphp
                    <div
                        class="p-6 rounded-3xl bg-amber-50/70 dark:bg-amber-900/10 border border-amber-200/80 dark:border-amber-700/40 space-y-4 relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="font-black text-gray-900 dark:text-white text-base">پرداخت با چک صیادی
                                        ({{ $faNum($cheques->count()) }} چک)</h4>
                                    <p class="text-xs text-amber-700 dark:text-amber-400 font-bold mt-0.5">سهم
                                        پرداخت: {{ $faNum($chequePercent) }}٪ از کل هزینه</p>
                                </div>
                            </div>
                            <span
                                class="px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                {{ $faNum(number_format($chequePaidAmount)) }} {{ $currencyLabel }}
                            </span>
                        </div>

                        <div class="space-y-3 pt-3 border-t border-amber-200/60 dark:border-amber-800/40 text-xs">
                            @foreach($cheques as $cheque)
                                <div
                                    class="p-3.5 bg-white/80 dark:bg-gray-800/60 rounded-2xl border border-amber-100 dark:border-amber-800/30 grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400 block mb-0.5">شماره صیادی:</span>
                                        <span
                                            class="font-bold text-gray-900 dark:text-white tabular-nums tracking-wide">{{ $faNum($cheque->cheque_number) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400 block mb-0.5">مبلغ چک:</span>
                                        <span
                                            class="font-black text-amber-700 dark:text-amber-300 tabular-nums">{{ $faNum(number_format($cheque->amount)) }} {{ $currencyLabel }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400 block mb-0.5">تاریخ سررسید:</span>
                                        <span
                                            class="font-bold text-gray-800 dark:text-gray-200 tabular-nums">{{ $cheque->due_date ? $faNum($toJalali($cheque->due_date)->format('Y/m/d')) : '—' }}</span>
                                    </div>
                                    <div>
                                        <span
                                            class="text-gray-500 dark:text-gray-400 block mb-0.5">بانک صادرکننده:</span>
                                        <span
                                            class="font-bold text-gray-800 dark:text-gray-200">{{ $cheque->bank_name ?: '—' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if($bankTxs->isNotEmpty() || $bankPaidAmount > 0)
                    @php
                        $bankPercent = $totalAmount > 0 ? round(($bankPaidAmount / $totalAmount) * 100) : 0;
                    @endphp
                    <div
                        class="p-6 rounded-3xl bg-indigo-50/70 dark:bg-indigo-900/10 border border-indigo-200/80 dark:border-indigo-700/40 space-y-4 relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <div>
                                    <h4 class="font-black text-gray-900 dark:text-white text-base">پرداخت از حساب‌های
                                        خزانه‌داری ({{ $faNum($bankTxs->count() ?: 1) }} حساب)</h4>
                                    <p class="text-xs text-indigo-700 dark:text-indigo-400 font-bold mt-0.5">سهم
                                        پرداخت: {{ $faNum($bankPercent) }}٪ از کل هزینه</p>
                                </div>
                            </div>
                            <span
                                class="px-3 py-1 rounded-full text-xs font-black bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300">
                                {{ $faNum(number_format($bankPaidAmount)) }} {{ $currencyLabel }}
                            </span>
                        </div>

                        <div class="space-y-3 pt-3 border-t border-indigo-200/60 dark:border-indigo-800/40 text-xs">
                            @foreach($bankTxs as $bTx)
                                @php
                                    $fa = $bTx->fundAccount;
                                    $txBalAfter = $bTx->account_balance_after;
                                @endphp
                                <div
                                    class="p-3.5 bg-white/80 dark:bg-gray-800/60 rounded-2xl border border-indigo-100 dark:border-indigo-800/30 grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400 block mb-0.5">نام حساب:</span>
                                        <span
                                            class="font-bold text-gray-900 dark:text-white">{{ $fa?->name ?: '—' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400 block mb-0.5">مبلغ کسر شده:</span>
                                        <span
                                            class="font-black text-indigo-700 dark:text-indigo-300 tabular-nums">{{ $faNum(number_format($bTx->credit)) }} {{ $currencyLabel }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400 block mb-0.5">بانک / نوع:</span>
                                        <span
                                            class="font-bold text-gray-800 dark:text-gray-200">{{ $fa?->bank_name ?: ($fa?->isWalletAccount() ? 'کیف پول' : 'خزانه') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400 block mb-0.5">شماره حساب:</span>
                                        <span
                                            class="font-bold text-gray-800 dark:text-gray-200 tabular-nums">{{ $fa?->account_number ? $faNum($fa->account_number) : '—' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Accounting Journal Entry Table --}}
        <div class="{{ $cardClass }} space-y-6">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-4">
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        سند حسابداری به تفکیک بدهکار و بستانکار
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">تراکنش‌های دوطرفه ثبت‌شده در سیستم برای این
                        سند هزینه</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40">
                    <tr>
                        <th scope="col"
                            class="px-6 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            نوع ردیف
                        </th>
                        <th scope="col"
                            class="px-6 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            سرفصل حسابداری / منبع
                        </th>
                        <th scope="col"
                            class="px-6 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            مبلغ بدهکار ({{ $currencyLabel }})
                        </th>
                        <th scope="col"
                            class="px-6 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            مبلغ بستانکار ({{ $currencyLabel }})
                        </th>
                        <th scope="col"
                            class="px-6 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            مانده حساب (پس از تراکنش)
                        </th>
                        <th scope="col"
                            class="px-6 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            شرح تراکنش
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                    @foreach($expense->transactions as $tx)
                        @php
                            $txBalAfter = $tx->account_balance_after;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($tx->debit > 0)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-100 dark:border-rose-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        بدهکار (هزینه)
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        بستانکار (منبع)
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900 dark:text-white">
                                {{ $tx->category?->title ?? $tx->fundAccount?->name ?? '—' }}
                                @if($tx->fundAccount)
                                    <span
                                        class="text-xs font-normal text-gray-400 block mt-0.5">حساب خزانه‌داری: {{ $tx->fundAccount->name }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-black text-rose-600 dark:text-rose-400 tabular-nums">
                                {{ $tx->debit > 0 ? $faNum(number_format($tx->debit)) : '0' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-black text-emerald-600 dark:text-emerald-400 tabular-nums">
                                {{ $tx->credit > 0 ? $faNum(number_format($tx->credit)) : '0' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(!is_null($txBalAfter))
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-sky-50 dark:bg-sky-500/10 text-sky-800 dark:text-sky-300 border border-sky-100 dark:border-sky-500/20 font-bold text-xs">
                                        <span
                                            class="tabular-nums font-black">{{ $faNum(number_format(CurrencyService::convertForDisplay($txBalAfter))) }}</span>
                                        <span
                                            class="text-[10px] text-sky-600/80 dark:text-sky-400/80">{{ $currencyLabel }}</span>
                                    </span>
                                @else
                                    <span class="text-gray-400 font-mono">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300 font-medium">
                                {{ $tx->description ?: '—' }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50/60 dark:bg-gray-900/60 font-black text-xs">
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-gray-600 dark:text-gray-300">مجموع (تراز سند):</td>
                        <td class="px-6 py-4 text-rose-700 dark:text-rose-400 tabular-nums text-sm">{{ $faNum(number_format($expense->transactions->sum('debit'))) }} {{ $currencyLabel }}</td>
                        <td class="px-6 py-4 text-emerald-700 dark:text-emerald-400 tabular-nums text-sm">{{ $faNum(number_format($expense->transactions->sum('credit'))) }} {{ $currencyLabel }}</td>
                        <td class="px-6 py-4 text-gray-400 font-mono">—</td>
                        <td class="px-6 py-4 text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            سند تراز می‌باشد
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Client & Attachment Additional Section --}}
        @if($expense->client || $expense->attachment)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($expense->client)
                    <div class="{{ $cardClass }} space-y-3">
                        <h4 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            مشتری مرتبط
                        </h4>
                        <div class="flex items-center gap-3 pt-2">
                            <div
                                class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300 font-black flex items-center justify-center">
                                {{ mb_substr($expense->client->full_name ?: $expense->client->username, 0, 1) }}
                            </div>
                            <div>
                                <span
                                    class="font-bold text-gray-900 dark:text-white block">{{ $expense->client->full_name ?: $expense->client->username }}</span>
                                @if($expense->client->national_code)
                                    <span class="text-xs text-gray-400">کد ملی: <strong
                                            class="tabular-nums font-mono">{{ $faNum($expense->client->national_code) }}</strong></span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @if($expense->attachment)
                    <div class="{{ $cardClass }} space-y-3">
                        <h4 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            فایل ضمیمه سند
                        </h4>
                        <div class="pt-2">
                            <a href="{{ Storage::url($expense->attachment) }}" target="_blank"
                               class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:hover:bg-indigo-500/20 dark:text-indigo-400 font-bold text-xs transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                مشاهده و دانلود فایل ضمیمه
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
