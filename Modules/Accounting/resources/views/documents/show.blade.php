@php
    use Carbon\Carbon;use Modules\Accounting\App\Services\CurrencyService;
    use Morilog\Jalali\Jalalian;

    $currencySuffix = CurrencyService::getBaseCurrency();

    $toJalali = function ($date) {
        if (!$date) return null;
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        return Jalalian::fromCarbon($carbon);
    };

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $getPaymentMethodName = function($method) {
        if (!$method) return '—';
        $settings = \Illuminate\Support\Facades\DB::table('settings')->whereIn('key', ['pos_devices', 'bank_transfer_accounts'])->pluck('value', 'key')->toArray();
        $posDevices = json_decode($settings['pos_devices'] ?? '[]', true) ?: [];
        $bankAccounts = json_decode($settings['bank_transfer_accounts'] ?? '[]', true) ?: [];

        if (str_starts_with($method, 'pos-')) {
            $id = substr($method, 4);
            foreach ($posDevices as $device) {
                if ($device['id'] === $id) {
                    return 'کارتخوان ' . $device['name'];
                }
            }
            return 'کارتخوان';
        }
        if (str_starts_with($method, 'cash-')) return 'نقد';
        if ($method === 'cod') return 'پرداخت در محل';
        if (str_starts_with($method, 'transfer-')) {
             $id = substr($method, 9);
             foreach ($bankAccounts as $account) {
                if ($account['id'] === $id) {
                    return 'انتقال به ' . $account['account_number'];
                }
            }
            return 'انتقال بانکی';
        }
        if (str_starts_with($method, 'online-')) return 'درگاه آنلاین';
        if (str_starts_with($method, 'cheque-') || str_starts_with($method, 'check-') || $method === 'cheque' || $method === 'check') return 'چک';
        return $method;
    };

    $cardClass  = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl";

    $backUrl = route('admin.accounting.documents.index');
@endphp
@extends('layouts.user')
@section('title', 'جزئیات سند: ' . $document->document_number)

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm font-medium" aria-label="Breadcrumb">
            <a href="{{ route('admin.accounting.documents.index') }}"
               class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">لیست
                اسناد</a>
            <svg class="w-4 h-4 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                 stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            <span
                class="text-gray-900 dark:text-white font-bold truncate max-w-xs tabular-nums">{{ $faNum($document->document_number) }}</span>
        </nav>

        {{-- Hero Header --}}
        <div class="{{ $cardClass }}">
            <div class="p-6 sm:p-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="flex items-start gap-5">
                    <div
                        class="shrink-0 flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                         <span
                             class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20">
                            سند حسابداری روزنامه
                        </span>
                        </div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl sm:text-4xl font-black text-gray-900 dark:text-white tracking-tight tabular-nums">{{ $faNum($document->document_number) }}</h1>
                            @if($document->sourceDocument)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-500 border border-indigo-100 dark:border-indigo-500/20">ثبت اتوماتیک</span>
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-gray-50 dark:bg-gray-500/10 text-gray-500 border border-gray-200 dark:border-gray-500/20">ثبت دستی</span>
                            @endif
                        </div>
                        <div
                            class="flex flex-wrap items-center gap-x-6 gap-y-2 mt-3 text-sm text-gray-500 dark:text-gray-400">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            تاریخ ثبت: {{ $faNum($toJalali($document->document_date)->format('Y/m/d')) }}
                        </span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    @php
                        $isDocCancelled = ($document->status === 'cancelled') ||
                                          in_array($document->sourceDocument?->event_type ?? '', ['invoice_cancelled', 'payment_cancelled', 'expense_cancelled']) ||
                                          \Illuminate\Support\Str::contains($document->description, ['لغو', 'برگشت']);
                    @endphp

                    @if(!$document->sourceDocument && !$isDocCancelled)
                        <a href="{{ route('admin.accounting.documents.edit', $document->id) }}"
                           class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm shadow-md shadow-amber-500/30 transition-all active:scale-95">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            ویرایش سند
                        </a>

                        <form action="{{ route('admin.accounting.documents.cancel', $document->id) }}" method="POST"
                              onsubmit="return confirm('آیا از لغو این سند دستی و برگشت/اصلاح مبالغ آن به خزانه‌داری اطمینان دارید؟');"
                              class="inline">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-bold text-sm shadow-md shadow-rose-500/30 transition-all active:scale-95">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                لغو سند دستی و برگشت مبلغ
                            </button>
                        </form>
                    @elseif($isDocCancelled)
                        <span
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20 font-bold text-sm">
                        <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        این سند لغو شده است
                    </span>
                    @endif

                    <a href="{{ $backUrl }}"
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
            <span class="bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 p-1.5 rounded-full shrink-0"><svg
                    class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path
                        stroke-linecap="round" stroke-linejoin="round"
                        d="M5 13l4 4L19 7"/></svg></span>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div
                class="rounded-2xl bg-red-50 p-4 border border-red-100 dark:bg-red-500/10 dark:border-red-500/20 text-red-800 dark:text-red-400 text-sm font-bold flex items-center gap-3">
            <span class="bg-red-100 text-red-600 dark:bg-red-500/20 p-1.5 rounded-full shrink-0"><svg class="w-5 h-5"
                                                                                                      fill="none"
                                                                                                      viewBox="0 0 24 24"
                                                                                                      stroke="currentColor"
                                                                                                      stroke-width="3"><path
                        stroke-linecap="round" stroke-linejoin="round"
                        d="M6 18L18 6M6 6l12 12"/></svg></span>{{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            {{-- SIDEBAR: Source Info / Summary --}}
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
                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            مشخصات سیستم مبدأ
                        </h3>
                    </div>
                    <div class="p-6 space-y-4 text-base">
                        @if($document->sourceDocument)
                            @php
                                $source = $document->sourceDocument;
                                $snapshot = $source->snapshot_data;

                                $moduleName = 'نامشخص';
                                $moduleColor = 'text-gray-500';

                                if ($source->module === 'services') {
                                    $moduleName = 'اتوماسیون خدمات';
                                    $moduleColor = 'text-emerald-600 dark:text-emerald-400';
                                } elseif ($source->module === 'booking') {
                                    $moduleName = 'نوبت‌دهی';
                                    $moduleColor = 'text-purple-600 dark:text-purple-400';
                                } elseif ($source->module === 'market') {
                                    $moduleName = 'فروشگاه';
                                    $moduleColor = 'text-amber-600 dark:text-amber-400';
                                } elseif ($source->module === 'wallet') {
                                    $moduleName = 'کیف پول کاربران';
                                    $moduleColor = 'text-blue-600 dark:text-blue-400';
                                }
                            @endphp
                            <div
                                class="flex justify-between items-center text-gray-600 dark:text-gray-400 mb-4 pb-4 border-b border-gray-100 dark:border-gray-700/50">
                                <span class="font-medium text-sm">ماژول:</span>
                                <span class="font-bold {{ $moduleColor }}">{{ $moduleName }}</span>
                            </div>

                            @if(isset($snapshot['holder']))
                                <div class="flex justify-between items-center text-gray-600 dark:text-gray-400 pb-2">
                                    <span class="font-medium text-sm">کاربر / دارنده:</span>
                                    <span
                                        class="font-bold text-gray-800 dark:text-gray-200">{{ $snapshot['holder'] }}</span>
                                </div>
                            @endif

                            @if(isset($snapshot['invoice_number']))
                                <div class="flex justify-between items-center text-gray-600 dark:text-gray-400 pb-2">
                                    <span class="font-medium text-sm">شماره فاکتور:</span>
                                    <span
                                        class="tabular-nums font-bold text-gray-800 dark:text-gray-200">{{ $faNum($snapshot['invoice_number']) }}</span>
                                </div>
                            @endif

                            @if(isset($snapshot['order_id']))
                                <div class="flex justify-between items-center text-gray-600 dark:text-gray-400 pb-2">
                                    <span class="font-medium text-sm">شماره سفارش:</span>
                                    <span
                                        class="tabular-nums font-bold text-gray-800 dark:text-gray-200">{{ $faNum($snapshot['order_id']) }}</span>
                                </div>
                            @endif

                            @if(isset($snapshot['amount']) || isset($snapshot['grand_total']))
                                <div class="flex justify-between items-center text-gray-600 dark:text-gray-400 pb-2">
                                    <span class="font-medium text-sm">مبلغ پرداختی:</span>
                                    <span class="tabular-nums font-bold text-gray-800 dark:text-gray-200">{{ $faNum(number_format($snapshot['amount'] ?? $snapshot['grand_total'])) }} <span
                                            class="text-xs">{{ $currencySuffix }}</span></span>
                                </div>
                            @endif

                            @if(isset($snapshot['method']))
                                <div class="flex justify-between items-center text-gray-600 dark:text-gray-400">
                                    <span class="font-medium text-sm">روش پرداختی مبدأ:</span>
                                    <span
                                        class="font-bold text-gray-800 dark:text-gray-200">{{ $getPaymentMethodName($snapshot['method']) }}</span>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-4">
                                <div
                                    class="w-16 h-16 mx-auto rounded-full bg-gray-50 dark:bg-gray-700/50 flex items-center justify-center mb-3 text-gray-400">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">سند دستی</h4>
                                <p class="text-xs text-gray-500 mt-2">توسط کاربر حسابدار ثبت شده است.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="{{ $cardClass }}">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 {{ $totalDebit == $totalCredit ? 'bg-gradient-to-l from-emerald-50 to-transparent dark:from-emerald-500/10' : 'bg-gradient-to-l from-rose-50 to-transparent dark:from-rose-500/10' }}">
                        <h3 class="text-lg font-black {{ $totalDebit == $totalCredit ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }} flex items-center gap-3">
                            <div
                                class="p-2 {{ $totalDebit == $totalCredit ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400' : 'bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400' }} rounded-lg shadow-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                </svg>
                            </div>
                            ترازنامه سند
                        </h3>
                    </div>
                    <div class="p-6 text-center">
                        <p class="text-sm font-bold text-gray-500 mb-1">وضعیت فعلی</p>
                        <h4 class="text-xl font-black {{ $totalDebit == $totalCredit ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} mb-4">
                            {{ $totalDebit == $totalCredit ? 'تراز می‌باشد' : 'نامتوازن' }}
                        </h4>

                        @if($totalDebit != $totalCredit)
                            <div
                                class="mt-4 p-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 text-xs font-bold border border-rose-100 dark:border-rose-500/20 flex flex-col gap-1">
                                <span>اختلاف:</span>
                                <span
                                    class="tabular-nums text-lg">{{ $faNum(number_format(abs($totalDebit - $totalCredit))) }} {{ $currencySuffix }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="lg:col-span-8 xl:col-span-8 space-y-8 order-1 lg:order-2">
                <div class="{{ $cardClass }}">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                        <h3 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                            <div class="p-2 bg-sky-100 text-sky-600 dark:bg-sky-500/20 dark:text-sky-400 rounded-lg">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                            </div>
                            شرح کلی سند
                        </h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-800 dark:text-gray-200 text-base leading-relaxed font-bold">{{ $document->description }}</p>
                    </div>
                </div>

                <div class="{{ $cardClass }}">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex items-center justify-between">
                        <h3 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                            <div
                                class="p-2 bg-violet-100 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400 rounded-lg">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            ردیف‌های تراکنش
                        </h3>
                        <span
                            class="text-sm font-bold text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-500/10 px-3 py-1.5 rounded-lg border border-violet-100 dark:border-violet-500/20">{{ $faNum($document->transactions->count()) }} ردیف</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-start border-collapse min-w-[700px]">
                            <thead
                                class="bg-gray-50/80 dark:bg-gray-900/30 text-gray-500 dark:text-gray-400 font-bold border-b border-gray-100 dark:border-gray-700/50 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 font-bold text-start w-16">#</th>
                                <th class="px-4 py-3 font-bold text-start">سرفصل حساب</th>
                                <th class="px-4 py-3 font-bold text-center">بانک/صندوق</th>
                                <th class="px-4 py-3 font-bold text-center w-32">بدهکار</th>
                                <th class="px-4 py-3 font-bold text-center w-32">بستانکار</th>
                                <th class="px-4 py-3 font-bold text-center">مانده حساب (پس از تراکنش)</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 transition-all">
                            @foreach($document->transactions as $transaction)
                                @php
                                    $rowBalAfter = $transaction->account_balance_after;
                                @endphp
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors group">
                                    <td class="px-4 py-4 align-top text-gray-400 font-mono text-xs">
                                        {{ $faNum($loop->iteration) }}
                                    </td>
                                    <td class="px-4 py-4 align-top text-start">
                                        <div
                                            class="font-bold text-gray-800 dark:text-gray-100">{{ $transaction->category->title ?? 'نامشخص' }}</div>
                                        @if($transaction->description && $transaction->description !== $document->description)
                                            <div
                                                class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-sm">{{ $transaction->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 align-top text-center text-gray-800 dark:text-gray-200 font-bold">
                                        {{ $transaction->fundAccount?->bank_display_name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-4 align-top text-center tabular-nums font-medium text-gray-700 dark:text-gray-300">
                                        @if($transaction->debit > 0)
                                            {{ $faNum(number_format(CurrencyService::convertForDisplay($transaction->debit))) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 align-top text-center tabular-nums font-medium text-gray-700 dark:text-gray-300">
                                        @if($transaction->credit > 0)
                                            {{ $faNum(number_format(CurrencyService::convertForDisplay($transaction->credit))) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 align-top text-center whitespace-nowrap">
                                        @if(!is_null($rowBalAfter))
                                            <span
                                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-sky-50 dark:bg-sky-500/10 text-sky-800 dark:text-sky-300 border border-sky-100 dark:border-sky-500/20 font-bold text-xs">
                                            <span
                                                class="tabular-nums font-black">{{ $faNum(number_format(CurrencyService::convertForDisplay($rowBalAfter))) }}</span>
                                            <span
                                                class="text-[10px] text-sky-600/80 dark:text-sky-400/80">{{ $currencySuffix }}</span>
                                        </span>
                                        @else
                                            <span class="text-gray-400 font-mono">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="bg-gray-50/80 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700/50 p-6 flex justify-end">
                        <div class="w-full sm:w-96 space-y-3 text-base">
                            <div class="flex justify-between items-center text-emerald-600 dark:text-emerald-400">
                                <span class="font-bold">جمع کل بدهکار:</span>
                                <span
                                    class="tabular-nums font-black text-lg">{{ $faNum(number_format($totalDebit)) }} <span
                                        class="text-sm text-emerald-500/80">{{ $currencySuffix }}</span></span>
                            </div>
                            <div
                                class="flex justify-between items-center text-rose-600 dark:text-rose-400 border-t border-dashed border-gray-200 dark:border-gray-700 pt-3 mt-1">
                                <span class="font-bold">جمع کل بستانکار:</span>
                                <span
                                    class="tabular-nums font-black text-lg">{{ $faNum(number_format($totalCredit)) }} <span
                                        class="text-sm text-rose-500/80">{{ $currencySuffix }}</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
