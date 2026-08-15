@php
    use Carbon\Carbon;use Modules\Accounting\App\Services\CurrencyService;use Morilog\Jalali\Jalalian;
@endphp
@extends('layouts.user')
@section('title', 'چک شماره ' . $cheque->cheque_number)

@php
    $toJalali = function ($date) {
        if (!$date) return null;
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        return Jalalian::fromCarbon($carbon);
    };

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $isReceivable = $cheque->type === 'receivable';
    $currencyLabel = CurrencyService::getBaseCurrency();
    $statusConfig = [
        'pending'     => ['label' => 'در جریان',       'color' => '#f59e0b'],
        'deposited'   => ['label' => 'واگذار به بانک',  'color' => '#3b82f6'],
        'cleared'     => ['label' => 'وصول شده',        'color' => '#10b981'],
        'bounced'     => ['label' => 'برگشت خورده',     'color' => '#ef4444'],
        'transferred' => ['label' => 'منتقل شده',       'color' => '#8b5cf6'],
        'returned'    => ['label' => 'عودت شده',        'color' => '#6b7280'],
        'endorsed'    => ['label' => 'خرج شده',         'color' => '#f97316'],
    ];
    $statusColor = $statusConfig[$cheque->status]['color'] ?? '#6b7280';
    $statusName  = $statusConfig[$cheque->status]['label'] ?? $cheque->status;

    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl";

    $chequeData = [
        'id'     => $cheque->id,
        'type'   => $cheque->type,
        'status' => $cheque->status,
    ];
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8"
         x-data="chequeShowPage({{ json_encode($chequeData) }}, {{ $fundAccounts->toJson() }}, {{ $categories->toJson() }})">
        <nav class="flex items-center gap-2 text-sm font-medium" aria-label="Breadcrumb">
            <a href="{{ route('admin.accounting.cheques.index') }}"
               class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">
                لیست چک‌ها
            </a>
            <svg class="w-4 h-4 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                 stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            <span
                class="text-gray-900 dark:text-white font-bold truncate max-w-xs tabular-nums">{{ $cheque->cheque_number }}</span>
        </nav>

        <div class="{{ $cardClass }}">
            <div class="p-6 sm:p-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="flex items-start gap-5">
                    <div class="shrink-0 flex items-center justify-center w-16 h-16 rounded-2xl text-white shadow-lg
                    {{ $isReceivable
                        ? 'bg-gradient-to-br from-emerald-500 to-teal-600 shadow-emerald-500/30'
                        : 'bg-gradient-to-br from-rose-500 to-red-600 shadow-rose-500/30' }}">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border
                            {{ $isReceivable
                                ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20'
                                : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20' }}">
                            چک {{ $isReceivable ? 'دریافتی' : 'پرداختی' }}
                        </span>
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border"
                                style="background: {{ $statusColor }}15; color: {{ $statusColor }}; border-color: {{ $statusColor }}33;">
                            <span class="w-2 h-2 rounded-full" style="background: {{ $statusColor }}"></span>
                            {{ $statusName }}
                        </span>
                        </div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl sm:text-4xl font-black text-gray-900 dark:text-white tracking-tight tabular-nums">
                                {{ $faNum($cheque->cheque_number) }}
                            </h1>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    @if($cheque->type === 'receivable')
                        @if($cheque->status === 'pending')
                            <button @click="openModal('deposit', cheque)"
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-white text-sm font-bold shadow-lg transition-all active:scale-95 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-400 hover:to-blue-500 shadow-blue-500/30 hover:shadow-blue-500/50">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                </svg>
                                واگذاری به بانک
                            </button>
                            <button @click="openModal('clear', cheque)"
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-white text-sm font-bold shadow-lg transition-all active:scale-95 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 shadow-emerald-500/30 hover:shadow-emerald-500/50">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                اعلام وصول
                            </button>
                            <button @click="openModal('endorse', cheque)"
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:hover:bg-amber-500/20 text-sm font-bold transition-all active:scale-95">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                                خرج کردن
                            </button>
                            <button @click="openModal('bounce', cheque)"
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 text-sm font-bold transition-all active:scale-95">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                اعلام برگشتی
                            </button>
                        @elseif($cheque->status === 'deposited')
                            <button @click="openModal('clear', cheque)"
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-white text-sm font-bold shadow-lg transition-all active:scale-95 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 shadow-emerald-500/30 hover:shadow-emerald-500/50">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                اعلام وصول
                            </button>
                            <button @click="openModal('return-with-cash', cheque)"
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-50 text-violet-700 hover:bg-violet-100 dark:bg-violet-500/10 dark:text-violet-400 dark:hover:bg-violet-500/20 text-sm font-bold transition-all active:scale-95">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                عودت و دریافت نقد
                            </button>
                        @elseif($cheque->status === 'transferred')
                            <button @click="openModal('return-with-cash', cheque)"
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-50 text-violet-700 hover:bg-violet-100 dark:bg-violet-500/10 dark:text-violet-400 dark:hover:bg-violet-500/20 text-sm font-bold transition-all active:scale-95">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                عودت و دریافت نقد
                            </button>
                        @elseif($cheque->status === 'bounced')
                            <button @click="openModal('clear', cheque)"
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-white text-sm font-bold shadow-lg transition-all active:scale-95 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 shadow-emerald-500/30 hover:shadow-emerald-500/50">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                وصول مجدد
                            </button>
                            <button @click="openModal('return-with-cash', cheque)"
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-50 text-violet-700 hover:bg-violet-100 dark:bg-violet-500/10 dark:text-violet-400 dark:hover:bg-violet-500/20 text-sm font-bold transition-all active:scale-95">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                عودت و دریافت نقد
                            </button>
                        @elseif($cheque->status === 'cleared')
                            <button @click="openModal('revert-clearance', cheque)"
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:hover:bg-amber-500/20 text-sm font-bold transition-all active:scale-95">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                </svg>
                                لغو وصول
                            </button>
                        @endif
                    @else
                        {{-- payable --}}
                        @if($cheque->status === 'pending')
                            <button @click="openModal('clear', cheque)"
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-white text-sm font-bold shadow-lg transition-all active:scale-95 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 shadow-emerald-500/30 hover:shadow-emerald-500/50">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                اعلام پاس شدن
                            </button>
                            <button @click="openModal('return-with-cash', cheque)"
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-50 text-violet-700 hover:bg-violet-100 dark:bg-violet-500/10 dark:text-violet-400 dark:hover:bg-violet-500/20 text-sm font-bold transition-all active:scale-95">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                عودت و پرداخت نقد
                            </button>
                            <button @click="openModal('bounce', cheque)"
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 text-sm font-bold transition-all active:scale-95">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                اعلام برگشتی
                            </button>
                        @elseif($cheque->status === 'bounced')
                            <button @click="openModal('clear', cheque)"
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-white text-sm font-bold shadow-lg transition-all active:scale-95 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 shadow-emerald-500/30 hover:shadow-emerald-500/50">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                اعلام پاس شدن مجدد
                            </button>
                        @elseif($cheque->status === 'cleared')
                            <button @click="openModal('revert-clearance', cheque)"
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:hover:bg-amber-500/20 text-sm font-bold transition-all active:scale-95">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                </svg>
                                لغو پاس شدن
                            </button>
                        @endif
                    @endif
                    <a href="{{ route('admin.accounting.cheques.print', $cheque) }}" target="_blank"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 dark:bg-sky-500/10 dark:text-sky-400 dark:hover:bg-sky-500/20 text-sm font-bold transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        چاپ چک
                    </a>
                    <a href="{{ route('admin.accounting.cheques.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-bold transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        بازگشت
                    </a>
                </div>
            </div>
        </div>
        @if(session('success'))
            <div
                class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-500/10 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-400 text-sm font-bold flex items-center gap-3">
            <span class="bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 p-1.5 rounded-full shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path
                        stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </span>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div
                class="rounded-2xl bg-red-50 p-4 border border-red-100 dark:bg-red-500/10 dark:border-red-500/20 text-red-800 dark:text-red-400 text-sm font-bold flex items-center gap-3">
            <span class="bg-red-100 text-red-600 dark:bg-red-500/20 p-1.5 rounded-full shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path
                        stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </span>
                {{ session('error') }}
            </div>
        @endif
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-4 xl:col-span-4 space-y-8 order-2 lg:order-1">
                <div class="{{ $cardClass }}">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-l from-indigo-50 to-transparent dark:from-indigo-500/10">
                        <h3 class="text-lg font-black text-indigo-700 dark:text-indigo-400 flex items-center gap-3">
                            <div
                                class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg shadow-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                                </svg>
                            </div>
                            خلاصه وضعیت چک
                        </h3>
                    </div>
                    <div class="p-6 space-y-4 text-base">

                        <div
                            class="flex justify-between items-center text-gray-600 dark:text-gray-400 mb-4 pb-4 border-b border-gray-100 dark:border-gray-700/50">
                            <span class="font-medium text-sm">وضعیت فعلی:</span>
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border"
                                style="background: {{ $statusColor }}15; color: {{ $statusColor }}; border-color: {{ $statusColor }}33;">
                            <span class="w-2 h-2 rounded-full" style="background: {{ $statusColor }}"></span>
                            {{ $statusName }}
                        </span>
                        </div>

                        <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                            <span class="font-medium">نوع چک:</span>
                            <span
                                class="font-bold {{ $isReceivable ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $isReceivable ? 'دریافتی' : 'پرداختی' }}
                        </span>
                        </div>

                        <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                            <span class="font-medium">بانک:</span>
                            <span
                                class="font-bold text-gray-800 dark:text-gray-200 text-left">{{ $cheque->bank_name }}</span>
                        </div>

                        @if($cheque->bank_branch)
                            <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                                <span class="font-medium">شعبه:</span>
                                <span
                                    class="font-bold text-gray-800 dark:text-gray-200">{{ $cheque->bank_branch }}</span>
                            </div>
                        @endif

                        @if($cheque->issue_date)
                            <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                                <span class="font-medium">تاریخ صدور:</span>
                                <span
                                    class="font-bold text-gray-800 dark:text-gray-200 tabular-nums">{{ $faNum($toJalali($cheque->issue_date)->format('Y/m/d')) }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                            <span class="font-medium">تاریخ سررسید:</span>
                            <span
                                class="font-bold text-gray-800 dark:text-gray-200 tabular-nums">{{ $faNum($toJalali($cheque->due_date)->format('Y/m/d')) }}</span>
                        </div>

                        @if($cheque->payee_name)
                            <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                                <span class="font-medium">{{ $isReceivable ? 'صادرکننده' : 'در وجه' }}:</span>
                                <span
                                    class="font-bold text-gray-800 dark:text-gray-200">{{ $cheque->payee_name }}</span>
                            </div>
                        @endif

                        @if($cheque->client)
                            <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                                <span class="font-medium">{{ $isReceivable ? 'مشتری' : 'طرف حساب' }}:</span>
                                <span
                                    class="font-bold text-indigo-600 dark:text-indigo-400">{{ $cheque->client->full_name }}</span>
                            </div>
                        @endif

                        @if($cheque->depositedFundAccount)
                            <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                                <span class="font-medium">واگذار به:</span>
                                <span
                                    class="font-bold text-blue-600 dark:text-blue-400">{{ $cheque->depositedFundAccount->name }}</span>
                            </div>
                        @endif

                        @if($cheque->clearedFundAccount)
                            <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                                <span class="font-medium">وصول در:</span>
                                <span
                                    class="font-bold text-emerald-600 dark:text-emerald-400">{{ $cheque->clearedFundAccount->name }}</span>
                            </div>
                        @endif

                        <div
                            class="pt-4 border-t-2 border-dashed border-gray-200 dark:border-gray-700 flex justify-between items-center">
                            <span class="font-black text-gray-900 dark:text-white">مبلغ چک:</span>
                            <span
                                class="font-black text-2xl tabular-nums {{ $isReceivable ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $faNum(number_format((int)$cheque->amount)) }}
                            <span class="text-sm font-bold">{{ $currencyLabel }}</span>
                        </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-8 xl:col-span-8 space-y-8 order-1 lg:order-2">
                <div class="{{ $cardClass }}">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex items-center justify-between">
                        <h3 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                            <div class="p-2 bg-sky-100 text-sky-600 dark:bg-sky-500/20 dark:text-sky-400 rounded-lg">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            اطلاعات {{ $isReceivable ? 'صادرکننده' : 'دریافت‌کننده' }} و طرف حساب
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                        @if($cheque->payee_name)
                            <div
                                class="bg-gray-50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                        <span
                            class="block text-xs font-bold text-gray-400 dark:text-gray-500 mb-1.5">{{ $isReceivable ? 'صادرکننده چک (صاحب حساب)' : 'دریافت‌کننده چک (در وجه)' }}</span>
                                <span
                                    class="text-gray-800 dark:text-gray-200 text-base font-bold break-all">{{ $cheque->payee_name }}</span>
                            </div>
                        @endif

                        @if($cheque->client)
                            <div
                                class="bg-gray-50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                                <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 mb-1.5">مشتری / طرف حساب مرتبط</span>
                                <a href="{{ route('user.clients.show', $cheque->client) }}"
                                   class="text-indigo-600 dark:text-indigo-400 hover:underline text-base font-bold break-all flex items-center gap-1">
                                    {{ $cheque->client->full_name }}
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </div>

                                @if($cheque->client->phone || $cheque->client->username)
                                    <div
                                        class="bg-gray-50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                                        <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 mb-1.5">شماره تماس مشتری</span>
                                        <span
                                            class="text-gray-800 dark:text-gray-200 text-base font-bold dir-ltr break-all tabular-nums text-right">{{ $faNum($cheque->client->phone ?? $cheque->client->username) }}</span>
                                    </div>
                                @endif

                                @if($cheque->client->email)
                                    <div
                                        class="bg-gray-50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                                        <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 mb-1.5">پست الکترونیک</span>
                                        <span
                                            class="text-gray-800 dark:text-gray-200 text-base font-medium dir-ltr break-all">{{ $cheque->client->email }}</span>
                                    </div>
                                @endif

                                @if($cheque->client->national_code)
                                    <div
                                        class="bg-gray-50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                                        <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 mb-1.5">کد ملی / شناسه ملی</span>
                                        <span
                                            class="text-gray-800 dark:text-gray-200 text-base font-bold dir-ltr break-all tabular-nums text-right">{{ $faNum($cheque->client->national_code) }}</span>
                                    </div>
                                @endif
                            @endif
                    </div>
                </div>
                <div class="{{ $cardClass }}">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-l from-violet-50 to-transparent dark:from-violet-500/10">
                        <h3 class="text-lg font-black text-violet-700 dark:text-violet-400 flex items-center gap-3">
                            <div
                                class="p-2 bg-violet-100 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400 rounded-lg shadow-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                            </div>
                            اسناد حسابداری
                        </h3>
                    </div>

                    @forelse($cheque->documents as $document)
                        <div class="{{ !$loop->first ? 'border-t border-gray-100 dark:border-gray-700/50' : '' }}">
                            {{-- Document header --}}
                            <div class="px-6 py-4 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/20">
                                <div class="flex items-center gap-3">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-violet-100 dark:bg-violet-500/20 text-violet-600 dark:text-violet-400 text-xs font-black">
                                {{ $loop->iteration }}
                            </span>
                                    <div>
                                        <a href="{{ route('admin.accounting.documents.show', $document) }}"
                                           class="text-sm font-black text-indigo-600 dark:text-indigo-400 hover:underline">
                                            سند شماره: {{ $document->document_number }}
                                        </a>
                                    </div>
                                </div>
                                <span
                                    class="text-xs font-bold text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-500/10 px-2.5 py-1 rounded-lg border border-violet-100 dark:border-violet-500/20">
                            {{ $faNum($document->transactions->count()) }} ردیف
                        </span>
                            </div>

                            {{-- Transactions table --}}
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-start border-collapse">
                                    <thead
                                        class="bg-gray-50/80 dark:bg-gray-900/30 text-gray-500 dark:text-gray-400 font-bold border-b border-gray-100 dark:border-gray-700/50 text-xs uppercase tracking-wider">
                                    <tr>
                                        <th class="px-6 py-3 font-bold text-start">سرفصل حساب</th>
                                        <th class="px-6 py-3 font-bold text-center text-emerald-600 dark:text-emerald-400">
                                            بدهکار
                                        </th>
                                        <th class="px-6 py-3 font-bold text-center text-rose-600 dark:text-rose-400">
                                            بستانکار
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                    @foreach($document->transactions as $transaction)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                            <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-200 text-start">
                                                {{ $transaction->category->title }}
                                            </td>
                                            <td class="px-6 py-4 text-center tabular-nums font-bold {{ $transaction->debit > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-300 dark:text-gray-600' }}">
                                                @if($transaction->debit > 0)
                                                    {{ $faNum(number_format($transaction->debit)) }}
                                                    <span
                                                        class="text-xs font-normal text-emerald-400/80 ms-0.5">{{ $currencyLabel }}</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-center tabular-nums font-bold {{ $transaction->credit > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-300 dark:text-gray-600' }}">
                                                @if($transaction->credit > 0)
                                                    {{ $faNum(number_format($transaction->credit)) }}
                                                    <span
                                                        class="text-xs font-normal text-rose-400/80 ms-0.5">{{ $currencyLabel }}</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    {{-- Totals row --}}
                                    <tfoot
                                        class="bg-gray-50/80 dark:bg-gray-900/40 border-t-2 border-dashed border-gray-200 dark:border-gray-700">
                                    <tr>
                                        <td class="px-6 py-3 font-black text-gray-700 dark:text-gray-300 text-sm">جمع
                                        </td>
                                        <td class="px-6 py-3 text-center tabular-nums font-black text-emerald-600 dark:text-emerald-400 text-sm">
                                            {{ $faNum(number_format($document->transactions->sum('debit'))) }}
                                            <span class="text-xs font-normal ms-0.5">{{ $currencyLabel }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-center tabular-nums font-black text-rose-600 dark:text-rose-400 text-sm">
                                            {{ $faNum(number_format($document->transactions->sum('credit'))) }}
                                            <span class="text-xs font-normal ms-0.5">{{ $currencyLabel }}</span>
                                        </td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 flex flex-col items-center justify-center text-center">
                            <div
                                class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700/40 mb-4">
                                <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-gray-400 dark:text-gray-500">هیچ سند حسابداری برای این چک
                                ثبت
                                نشده است</p>
                            <p class="text-xs text-gray-400 dark:text-gray-600 mt-1">اسناد پس از انجام عملیات (وصول،
                                واگذاری
                                و...) ایجاد می‌شوند</p>
                        </div>
                    @endforelse
                </div>

                @if($cheque->description)
                    <div
                        class="{{ $cardClass }} p-6 sm:p-8 border-l-4 border-l-amber-500 bg-amber-50/50 dark:bg-amber-900/10">
                        <div class="flex items-start gap-4 text-amber-800 dark:text-amber-300">
                            <svg class="w-8 h-8 shrink-0 mt-0.5 opacity-80" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <div>
                                <strong class="block mb-2 font-black text-lg tracking-tight">توضیحات چک</strong>
                                <p class="font-medium text-base leading-loose">{{ $cheque->description }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @include('accounting::cheques.partials.actions-modal')
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('chequeShowPage', (chequeData, fundAccounts, categories) => ({
                cheque: chequeData,
                fundAccounts: fundAccounts,
                categories: categories,
                modalOpen: false,
                modalAction: null,
                modalTitle: '',
                currentCheque: null,

                openModal(action, cheque) {
                    this.modalAction = action;
                    this.currentCheque = cheque;
                    const titles = {
                        'deposit': 'واگذاری چک به بانک',
                        'clear': cheque.type === 'receivable' ? 'اعلام وصول چک' : 'اعلام پاس شدن چک',
                        'bounce': 'اعلام برگشتی چک',
                        'endorse': 'خرج کردن چک',
                        'return-with-cash': cheque.type === 'receivable' ? 'عودت و دریافت نقد' : 'عودت و پرداخت نقد',
                        'revert-clearance': 'لغو عملیات وصول',
                    };
                    this.modalTitle = titles[action] || action;
                    this.modalOpen = true;
                },
                closeModal() {
                    this.modalOpen = false;
                    this.modalAction = null;
                }
            }));
        });
    </script>
@endsection
