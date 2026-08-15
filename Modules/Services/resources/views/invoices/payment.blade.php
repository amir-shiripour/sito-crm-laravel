@extends('layouts.user')
@section('title', 'ثبت پرداختی فاکتور #' . $invoice->invoice_number)

@include('partials.jalali-date-picker')

@php
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-sm dark:border-gray-700 dark:bg-gray-900/50 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-200 mb-1.5 ms-1";
    $cardClass  = "bg-white dark:bg-gray-800/60 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl";

    $conversionFactor = $conversionFactor ?? 1.0;
    $currency         = $currency ?? 'toman';
    $currencyLabel    = ($currency === 'rial' || $currency === 'irr' || $currency === 'ریال') ? 'ریال' : 'تومان';

    $useGlobalPayment = ($settings['services_use_global_payment_settings'] ?? '0') === '1';

    $banksMap = [];
    if (\Illuminate\Support\Facades\Schema::hasTable('accounting_fund_accounts')) {
        $banksMap = \Illuminate\Support\Facades\DB::table('accounting_fund_accounts')
            ->where('type', 'bank')
            ->pluck('name', 'id')
            ->all();
    }

    $onlineGateways = [];
    $bankTransferAccounts = [];
    $posDevices = [];
    $activePaymentMethods = [];

    if ($useGlobalPayment) {
        $activePaymentMethods = json_decode($settings['active_payment_methods'] ?? '[]', true);
        if (!is_array($activePaymentMethods)) {
            $activePaymentMethods = [];
        }

        if (($settings['zarinpal_status'] ?? 'inactive') === 'active') $onlineGateways['zarinpal'] = 'زرین‌پال';
        if (($settings['zibal_status'] ?? 'inactive') === 'active') $onlineGateways['zibal'] = 'زیبال';
        if (($settings['behpardakht_status'] ?? 'inactive') === 'active') $onlineGateways['behpardakht'] = 'به‌پرداخت ملت';

        $rawAccounts = in_array('transfer', $activePaymentMethods) ? json_decode($settings['bank_transfer_accounts'] ?? '[]', true) : [];
        if (is_array($rawAccounts)) {
            foreach ($rawAccounts as $acc) {
                if (is_array($acc)) {
                    if (empty($acc['bank_name']) && !empty($acc['bank_id']) && isset($banksMap[$acc['bank_id']])) {
                        $acc['bank_name'] = $banksMap[$acc['bank_id']];
                    }
                    $bankTransferAccounts[] = $acc;
                }
            }
        }
        $posDevices = in_array('pos', $activePaymentMethods) ? json_decode($settings['pos_devices'] ?? '[]', true) : [];
    }

    $hasOnlineMethod = $useGlobalPayment && in_array('online', $activePaymentMethods) && count($onlineGateways) > 0;
    $hasTransferMethod = !$useGlobalPayment || (in_array('transfer', $activePaymentMethods) && count($bankTransferAccounts) > 0);
    $hasPosMethod = !$useGlobalPayment || (in_array('pos', $activePaymentMethods) && count($posDevices) > 0);
    $hasCodMethod = $useGlobalPayment && in_array('cod', $activePaymentMethods);

    $hasManual = $hasTransferMethod || $hasPosMethod || $hasCodMethod;
    $hasChequesAvailable = isset($customerCheques) && count($customerCheques) > 0;

    $invoiceTotal = $invoice->total * $conversionFactor;
    $paidAmount   = $invoice->calculatePaidAmount() * $conversionFactor;
    $dueAmount    = $invoiceTotal - $paidAmount;

    $faNum = function($str) { if (is_null($str)) return ''; $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹']; return str_replace(range(0,9), $persian, (string)$str); };
@endphp

@section('content')
    <style>[x-cloak] { display: none !important; }</style>

    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6" x-data="paymentWizard()">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h1 class="text-xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                <span
                    class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-md shadow-emerald-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path
                            stroke-linecap="round" stroke-linejoin="round"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </span>
                ثبت پرداختی فاکتور <span
                    class="text-emerald-600 dark:text-emerald-400 font-black tabular-nums">#{{ $faNum($invoice->invoice_number) }}</span>
            </h1>
            <a href="{{ route('services.invoices.show', $invoice) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت به فاکتور
            </a>
        </div>

        @if($errors->any())
            <div
                class="p-4 text-xs text-red-800 rounded-xl bg-red-50 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20 flex items-start gap-3 shadow-sm">
                <span class="bg-red-100 text-red-600 dark:bg-red-500/20 p-1.5 rounded-full shrink-0 mt-0.5"><svg
                        class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path
                            stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
                <div>
                    <p class="font-black text-sm mb-1">خطا در ثبت اطلاعات!</p>
                    <ul class="list-disc ps-4 space-y-1 marker:text-red-400">@foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach</ul>
                </div>
            </div>
        @endif

        <form id="paymentForm" action="{{ route('services.invoices.payment.store', $invoice) }}" method="POST"
              @submit.prevent="submitForm"
              class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            @csrf
            <template x-for="id in selectedChequeIds" :key="id">
                <input type="hidden" name="cheque_ids[]" :value="id">
            </template>

            {{-- Main Form Column --}}
            <div class="lg:col-span-8 xl:col-span-9 space-y-6">

                {{-- Step 1: Compact Payment Type Cards --}}
                <div class="{{ $cardClass }}">
                    <div
                        class="p-4 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex items-center gap-2.5">
                        <span
                            class="flex items-center justify-center w-7 h-7 rounded-full bg-indigo-500 text-white font-bold text-xs">۱</span>
                        <h2 class="text-base font-black text-gray-800 dark:text-gray-100">انتخاب نوع پرداخت</h2>
                    </div>
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                        @if($hasOnlineMethod)
                            <div @click="setPaymentType('online')"
                                 class="flex flex-col items-center justify-center text-center gap-2.5 p-4 rounded-xl border-2 transition-all cursor-pointer focus:outline-none outline-none"
                                 :class="paymentType === 'online' ? 'border-indigo-500 bg-indigo-50/80 dark:bg-indigo-900/30 shadow-sm ring-2 ring-indigo-500/20' : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-800 bg-white dark:bg-gray-800'">
                                <div
                                    class="w-11 h-11 rounded-xl bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <rect x="2.5" y="5.5" width="19" height="13" rx="2.5"/>
                                        <path stroke-linecap="round" d="M2.5 9.5h19"/>
                                        <path stroke-linecap="round" d="M6 14.5h4"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-bold transition-colors"
                                      :class="paymentType === 'online' ? 'text-indigo-700 dark:text-indigo-300 font-black' : 'text-gray-600 dark:text-gray-300'">پرداخت آنلاین</span>
                            </div>
                        @endif

                        @if($hasManual)
                            <div @click="setPaymentType('manual')"
                                 class="flex flex-col items-center justify-center text-center gap-2.5 p-4 rounded-xl border-2 transition-all cursor-pointer focus:outline-none outline-none"
                                 :class="paymentType === 'manual' ? 'border-indigo-500 bg-indigo-50/80 dark:bg-indigo-900/30 shadow-sm ring-2 ring-indigo-500/20' : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-800 bg-white dark:bg-gray-800'">
                                <div
                                    class="w-11 h-11 rounded-xl bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <rect x="2.5" y="6" width="19" height="12" rx="2"/>
                                        <circle cx="12" cy="12" r="2.75"/>
                                        <path stroke-linecap="round" d="M5.5 9v0M18.5 15v0"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-bold transition-colors"
                                      :class="paymentType === 'manual' ? 'text-indigo-700 dark:text-indigo-300 font-black' : 'text-gray-600 dark:text-gray-300'">پرداخت بانکی / پوز / در محل</span>
                            </div>
                        @endif

                        {{-- Cheque Card in Step 1 (Always Visible, Disabled if no free cheques exist) --}}
                        <div @click="{{ $hasChequesAvailable ? "setPaymentType('cheque')" : "null" }}"
                             class="flex flex-col items-center justify-center text-center gap-2.5 p-4 rounded-xl border-2 transition-all focus:outline-none outline-none"
                             :class="{
                                 'border-indigo-500 bg-indigo-50/80 dark:bg-indigo-900/30 shadow-sm ring-2 ring-indigo-500/20 cursor-pointer': paymentType === 'cheque',
                                 'border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-800 bg-white dark:bg-gray-800 cursor-pointer': paymentType !== 'cheque' && {{ $hasChequesAvailable ? 'true' : 'false' }},
                                 'opacity-60 bg-gray-100 dark:bg-gray-800/40 border-gray-200 dark:border-gray-700/60 cursor-not-allowed': {{ $hasChequesAvailable ? 'false' : 'true' }}
                             }">
                            <div
                                class="w-11 h-11 rounded-xl bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <rect x="2.5" y="5" width="19" height="14" rx="2"/>
                                    <path stroke-linecap="round" d="M6 10h6"/>
                                    <path stroke-linecap="round" d="M6 13h4"/>
                                    <path stroke-linecap="round" d="M14 16.5c1.2 0 1.2-1.5 2.4-1.5s1.2 1.5 2.4 1.5"/>
                                </svg>
                            </div>
                            <div class="space-y-0.5">
                                <span class="text-xs font-bold transition-colors"
                                      :class="paymentType === 'cheque' ? 'text-indigo-700 dark:text-indigo-300 font-black' : 'text-gray-600 dark:text-gray-300'">پرداخت با چک صیادی</span>
                                @if(!$hasChequesAvailable)
                                    <span class="block text-[10px] text-gray-400 dark:text-gray-500 font-bold">فاقد چک صیادی آزاد</span>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Step 2: Method Cards with Toggle Button & Smooth Animation --}}
                <div x-show="paymentType" x-transition class="{{ $cardClass }}">
                    <div
                        class="p-4 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span
                                class="flex items-center justify-center w-7 h-7 rounded-full bg-indigo-500 text-white font-bold text-xs">۲</span>
                            <h2 class="text-base font-black text-gray-800 dark:text-gray-100">انتخاب روش دقیق پرداخت</h2>
                        </div>
                        <button type="button" @click="showMethodDetails = !showMethodDetails"
                                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition-all font-bold text-xs shadow-sm active:scale-95">
                            <span x-text="showMethodDetails ? 'مخفی‌سازی روش‌ها' : 'نمایش روش‌ها'"></span>
                            <svg class="w-4 h-4 transition-transform duration-300" :class="showMethodDetails ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>

                    <div x-show="showMethodDetails"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 -translate-y-2 scale-98"
                         class="p-4 space-y-5">

                        {{-- Online Payment Cards --}}
                        @if($hasOnlineMethod)
                            <div x-show="paymentType === 'online'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                                @foreach($onlineGateways as $key => $name)
                                    <div @click="toggleMethod('{{ $key }}', 'درگاه {{ $name }}', '{{ $key }}')"
                                         :class="isMethodSelected('{{ $key }}')
                                            ? 'border-indigo-500 bg-indigo-50/80 dark:bg-indigo-900/30 shadow-sm ring-2 ring-indigo-500/20'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-800 bg-white dark:bg-gray-800'"
                                         class="p-4 rounded-xl border-2 transition-all cursor-pointer flex items-center justify-between gap-3 focus:outline-none outline-none">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0">
                                                درگاه
                                            </div>
                                            <span class="text-xs font-black text-gray-800 dark:text-gray-100">{{ $name }}</span>
                                        </div>
                                        <div class="w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all shrink-0"
                                             :class="isMethodSelected('{{ $key }}') ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800'">
                                            <svg x-show="isMethodSelected('{{ $key }}')" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Manual Payment Cards (Bank Transfer / POS / COD) --}}
                        <div x-show="paymentType === 'manual'" x-cloak class="space-y-4">
                            @if($hasTransferMethod)
                                <div class="space-y-2.5">
                                    <h3 class="text-xs font-black text-gray-500 dark:text-gray-400">کارت به کارت / حواله بانکی</h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                        @foreach($bankTransferAccounts as $account)
                                            @php
                                                $accId = 'transfer-' . ($account['id'] ?? '');
                                                $bankName = $account['bank_name'] ?? '';
                                                $ownerName = $account['owner_name'] ?? $account['name'] ?? '';
                                                if (empty($bankName) && !empty($ownerName) && (str_contains($ownerName, 'بانک') || str_contains($ownerName, 'Bank'))) {
                                                    $bankName = $ownerName;
                                                    $ownerName = '';
                                                }
                                                $title = !empty($bankName) ? $bankName : (!empty($ownerName) ? $ownerName : 'حساب بانکی');
                                            @endphp
                                            <div @click="toggleMethod('{{ $accId }}', 'حواله/کارت {{ $faNum($title) }}')"
                                                 :class="isMethodSelected('{{ $accId }}')
                                                    ? 'border-indigo-500 bg-indigo-50/80 dark:bg-indigo-900/30 shadow-sm ring-2 ring-indigo-500/20'
                                                    : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-800 bg-white dark:bg-gray-800'"
                                                 class="p-4 rounded-xl border-2 transition-all cursor-pointer flex flex-col justify-between space-y-3 focus:outline-none outline-none">
                                                
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-9 h-9 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-xs shrink-0">
                                                            بانک
                                                        </div>
                                                        <div>
                                                            <span class="text-sm font-black text-gray-800 dark:text-gray-100">{{ $faNum($title) }}</span>
                                                            @if(!empty($ownerName))
                                                                <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mt-0.5">به نام: {{ $faNum($ownerName) }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all shrink-0 mt-0.5"
                                                         :class="isMethodSelected('{{ $accId }}') ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800'">
                                                        <svg x-show="isMethodSelected('{{ $accId }}')" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </div>
                                                </div>

                                                {{-- Full Bank Info --}}
                                                <div class="space-y-1.5 pt-2.5 border-t border-gray-100 dark:border-gray-700/60 text-xs text-gray-600 dark:text-gray-300">
                                                    @if(!empty($account['card_number']))
                                                        <p class="flex justify-between">
                                                            <span class="text-gray-500 dark:text-gray-400 font-medium">شماره کارت:</span>
                                                            <strong class="font-bold tabular-nums text-gray-800 dark:text-gray-200">{{ $faNum($account['card_number']) }}</strong>
                                                        </p>
                                                    @endif
                                                    @if(!empty($account['account_number']))
                                                        <p class="flex justify-between">
                                                            <span class="text-gray-500 dark:text-gray-400 font-medium">شماره حساب:</span>
                                                            <strong class="font-bold tabular-nums text-gray-800 dark:text-gray-200">{{ $faNum($account['account_number']) }}</strong>
                                                        </p>
                                                    @endif
                                                    @if(!empty($account['iban']))
                                                        <p class="flex justify-between">
                                                            <span class="text-gray-500 dark:text-gray-400 font-medium">شماره شبا:</span>
                                                            <strong class="font-bold tabular-nums text-gray-800 dark:text-gray-200">{{ $faNum($account['iban']) }}</strong>
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($hasPosMethod)
                                <div class="space-y-2.5">
                                    <h3 class="text-xs font-black text-gray-500 dark:text-gray-400">دستگاه‌های کارتخوان (POS)</h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                        @foreach($posDevices as $device)
                                            @php $posId = 'pos-' . ($device['id'] ?? ''); @endphp
                                            <div @click="toggleMethod('{{ $posId }}', 'دستگاه پوز {{ $device['name'] ?? '' }}')"
                                                 :class="isMethodSelected('{{ $posId }}')
                                                    ? 'border-indigo-500 bg-indigo-50/80 dark:bg-indigo-900/30 shadow-sm ring-2 ring-indigo-500/20'
                                                    : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-800 bg-white dark:bg-gray-800'"
                                                 class="p-4 rounded-xl border-2 transition-all cursor-pointer flex items-center justify-between gap-3 focus:outline-none outline-none">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-9 h-9 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-xs shrink-0">
                                                        POS
                                                    </div>
                                                    <div>
                                                        <span class="text-xs font-black text-gray-800 dark:text-gray-100">{{ $device['name'] ?? 'کارتخوان' }}</span>
                                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $device['description'] ?? 'پرداخت با کارتخوان' }}</p>
                                                    </div>
                                                </div>
                                                <div class="w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all shrink-0"
                                                     :class="isMethodSelected('{{ $posId }}') ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800'">
                                                    <svg x-show="isMethodSelected('{{ $posId }}')" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($hasCodMethod)
                                <div class="space-y-2.5">
                                    <h3 class="text-xs font-black text-gray-500 dark:text-gray-400">پرداخت در محل (COD)</h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                        <div @click="toggleMethod('cod', 'پرداخت در محل (COD)')"
                                             :class="isMethodSelected('cod')
                                                ? 'border-indigo-500 bg-indigo-50/80 dark:bg-indigo-900/30 shadow-sm ring-2 ring-indigo-500/20'
                                                : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-800 bg-white dark:bg-gray-800'"
                                             class="p-4 rounded-xl border-2 transition-all cursor-pointer flex items-center justify-between gap-3 focus:outline-none outline-none">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-xs shrink-0">
                                                    COD
                                                </div>
                                                <span class="text-xs font-black text-gray-800 dark:text-gray-100">پرداخت در محل (COD)</span>
                                            </div>
                                            <div class="w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all shrink-0"
                                                 :class="isMethodSelected('cod') ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800'">
                                                <svg x-show="isMethodSelected('cod')" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Cheque Cards Grid --}}
                        @if($hasChequesAvailable)
                            <div x-show="paymentType === 'cheque'" x-cloak class="space-y-4">
                                <h3 class="text-xs font-black text-gray-500 dark:text-gray-400">انتخاب چک‌های صیادی مشتری (چک‌های فراتر از مانده غیرفعال هستند)</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    @foreach($customerCheques as $cheque)
                                        @php
                                            $chequeAmt = (float)($cheque->display_amount ?? $cheque->amount);
                                        @endphp
                                        <div @click="canSelectCheque({{ json_encode($cheque) }}) || isChequeSelected({{ $cheque->id }}) ? toggleCheque({{ json_encode($cheque) }}) : null"
                                             :class="{
                                                 'border-amber-500 bg-amber-50/80 dark:bg-amber-900/30 shadow-sm ring-2 ring-amber-500/20 cursor-pointer': isChequeSelected({{ $cheque->id }}),
                                                 'border-gray-200 dark:border-gray-700 hover:border-amber-300 dark:hover:border-amber-700 bg-white dark:bg-gray-800 cursor-pointer': !isChequeSelected({{ $cheque->id }}) && canSelectCheque({{ json_encode($cheque) }}),
                                                 'opacity-50 bg-gray-100 dark:bg-gray-800/40 border-gray-200 dark:border-gray-700/60 cursor-not-allowed': !isChequeSelected({{ $cheque->id }}) && !canSelectCheque({{ json_encode($cheque) }})
                                             }"
                                             class="p-4 rounded-xl border-2 transition-all flex flex-col justify-between space-y-3 focus:outline-none outline-none">
                                            
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center font-black text-xs shrink-0">
                                                        چک
                                                    </div>
                                                    <div>
                                                        <span class="block text-[11px] font-bold text-gray-400 dark:text-gray-500">شماره صیادی</span>
                                                        <span class="text-sm font-black text-gray-900 dark:text-white tabular-nums tracking-wide">{{ $faNum($cheque->cheque_number) }}</span>
                                                    </div>
                                                </div>
                                                <div class="w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all shrink-0 mt-0.5"
                                                     :class="isChequeSelected({{ $cheque->id }}) ? 'border-amber-600 bg-amber-600 text-white' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800'">
                                                    <svg x-show="isChequeSelected({{ $cheque->id }})" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </div>
                                            </div>

                                            {{-- Full Cheque Info --}}
                                            <div class="space-y-1.5 pt-2.5 border-t border-gray-100 dark:border-gray-700/60 text-xs">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-gray-500 dark:text-gray-400 font-medium">مبلغ چک:</span>
                                                    <span class="font-black text-amber-600 dark:text-amber-400 tabular-nums">{{ $faNum(number_format($chequeAmt)) }} {{ $currencyLabel }}</span>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-gray-500 dark:text-gray-400 font-medium">تاریخ سررسید:</span>
                                                    <span class="font-bold text-gray-800 dark:text-gray-200 tabular-nums">{{ $faNum($cheque->due_date_jalali) }}</span>
                                                </div>
                                                @if(!empty($cheque->bank_name))
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-gray-500 dark:text-gray-400 font-medium">بانک صادرکننده:</span>
                                                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ $faNum($cheque->bank_name) }}</span>
                                                    </div>
                                                @endif
                                            </div>

                                            <template x-if="!isChequeSelected({{ $cheque->id }}) && !canSelectCheque({{ json_encode($cheque) }})">
                                                <div class="mt-2 p-1.5 rounded-lg bg-gray-100 dark:bg-gray-900/60 text-gray-500 dark:text-gray-400 text-[11px] font-bold text-center">
                                                    مبلغ چک بیشتر از مانده بدهی است
                                                </div>
                                            </template>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- Step 3: Selected Payments Rows Table & Deposit Date (Visible if paymentType is selected OR if selectedRows has items) --}}
                <div x-show="paymentType || selectedRows.length > 0" x-transition class="{{ $cardClass }}">
                    <div
                        class="p-4 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span
                                class="flex items-center justify-center w-7 h-7 rounded-full bg-emerald-500 text-white font-bold text-xs">۳</span>
                            <h2 class="text-base font-black text-gray-800 dark:text-gray-100">جدول ردیف‌های پرداختی و نهایی‌سازی</h2>
                        </div>
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">
                            تعداد ردیف‌ها: <strong class="text-emerald-600 dark:text-emerald-400 font-black tabular-nums" x-text="selectedRows.length"></strong>
                        </span>
                    </div>

                    <div class="p-4 space-y-6">
                        {{-- Empty State --}}
                        <template x-if="selectedRows.length === 0">
                            <div class="text-center py-8 px-4 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                                <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <p class="text-xs font-bold text-gray-500 dark:text-gray-400">هنوز هیچ روش پرداختی انتخاب نشده است.</p>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">لطفاً از کارت‌های بالا یا کیف پول سایدبار، روش پرداخت دلخواه خود را انتخاب کنید.</p>
                            </div>
                        </template>

                        {{-- Table Rows --}}
                        <template x-if="selectedRows.length > 0">
                            <div class="space-y-3">
                                <template x-for="(row, index) in selectedRows" :key="row.id">
                                    <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3 transition-all">

                                        {{-- Hidden inputs --}}
                                        <template x-if="row.type === 'wallet'">
                                            <div>
                                                <input type="hidden" name="use_wallet" value="1">
                                                <input type="hidden" name="wallet_amount" :value="getNumericAmount(row.amount)">
                                            </div>
                                        </template>

                                        <template x-if="row.type === 'cheque'">
                                            <input type="hidden" name="cheque_ids[]" :value="row.cheque_id">
                                        </template>

                                        <template x-if="row.type === 'direct'">
                                            <div>
                                                <input type="hidden" :name="'payment_items[' + row.method + '][method]'" :value="row.method">
                                                <input type="hidden" :name="'payment_items[' + row.method + '][amount]'" :value="getNumericAmount(row.amount)">
                                                <input type="hidden" :name="'payment_items[' + row.method + '][transaction_id]'" :value="row.transaction_id">
                                                <input type="hidden" :name="'payment_items[' + row.method + '][gateway]'" :value="row.gateway">
                                            </div>
                                        </template>

                                        {{-- Title Badge --}}
                                        <div class="flex items-center gap-2.5 min-w-[200px]">
                                            <span class="w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold text-xs shrink-0 shadow-sm"
                                                  :class="{
                                                      'bg-emerald-500': row.type === 'wallet',
                                                      'bg-amber-500': row.type === 'cheque',
                                                      'bg-indigo-500': row.type === 'direct'
                                                  }">
                                                <span x-text="row.type === 'wallet' ? 'کیف' : (row.type === 'cheque' ? 'چک' : 'بانک')"></span>
                                            </span>
                                            <div>
                                                <span class="block text-xs font-black text-gray-900 dark:text-white" x-text="row.title"></span>
                                                <span class="text-[10px] text-gray-400 dark:text-gray-500" x-text="'ردیف ' + (index + 1)"></span>
                                            </div>
                                        </div>

                                        {{-- Amount Input --}}
                                        <div class="flex-1 max-w-xs">
                                            <label class="{{ $labelClass }}">مبلغ پرداختی این ردیف ({{ $currencyLabel }})</label>
                                            <input type="text"
                                                   :value="row.amount"
                                                   @input="row.amount = formatPriceInput($event.target.value)"
                                                   :readonly="row.is_readonly_amount === true"
                                                   class="{{ $inputClass }} text-center font-bold"
                                                   :class="row.is_readonly_amount === true ? 'bg-gray-50 dark:bg-gray-900/80 text-gray-500 cursor-not-allowed' : 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white'"
                                                   placeholder="مبلغ را وارد کنید...">
                                        </div>

                                        {{-- Tracking Code Input --}}
                                        <div class="flex-1 max-w-xs">
                                            <label class="{{ $labelClass }}">کد پیگیری / ارجاع (اختیاری)</label>
                                            <input type="text" x-model="row.transaction_id"
                                                   :readonly="row.is_readonly_id === true"
                                                   class="{{ $inputClass }} text-center font-mono"
                                                   :class="row.is_readonly_id === true ? 'bg-gray-50 dark:bg-gray-900/80 text-gray-500 cursor-not-allowed' : 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white'"
                                                   placeholder="شماره پیگیری...">
                                        </div>

                                        {{-- Delete Button --}}
                                        <div class="flex items-center justify-end">
                                            <button type="button" @click="removeRow(index)"
                                                    class="p-2 rounded-lg text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                                                    title="حذف ردیف">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Date & Submit Button --}}
                        <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60 space-y-4">
                            <div>
                                <label for="payment_date" class="{{ $labelClass }}">تاریخ واریز پرداختی‌ها</label>
                                <input type="text" id="payment_date" name="paid_at" x-model="paidAtDate" readonly data-jdp-only-date
                                       class="{{ $inputClass }} text-center cursor-pointer font-bold"
                                       placeholder="انتخاب تاریخ...">
                            </div>
                            <button type="submit"
                                    class="w-full px-6 py-3.5 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-black text-base shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:from-emerald-400 hover:to-emerald-500 transition-all duration-300 active:scale-95 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                ثبت نهایی تمامی پرداختی‌ها
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column: Summary & Wallet Sidebar --}}
            <div class="lg:col-span-4 xl:col-span-3 space-y-6 lg:sticky lg:top-6">
                {{-- Financial Summary Card --}}
                <div class="{{ $cardClass }}">
                    <div class="p-4 border-b border-gray-100 dark:border-gray-700/50">
                        <h3 class="text-base font-black text-gray-800 dark:text-gray-100">خلاصه وضعیت فاکتور</h3>
                    </div>
                    <div class="p-4 space-y-4 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-500 dark:text-gray-400">مشتری:</span>
                            <span
                                class="font-bold text-gray-800 dark:text-gray-200">{{ $invoice->client_name ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-500 dark:text-gray-400">مبلغ کل:</span>
                            <span
                                class="font-black text-gray-800 dark:text-gray-200 tabular-nums">{{ $faNum(number_format($invoiceTotal)) }} <span class="text-xs font-medium">{{ $currencyLabel }}</span></span>
                        </div>
                        <div class="flex justify-between items-center border-b border-dashed border-gray-200 dark:border-gray-700 pb-4">
                            <span class="font-bold text-gray-500 dark:text-gray-400">پرداخت شده قبلی:</span>
                            <span
                                class="font-black text-emerald-600 dark:text-emerald-400 tabular-nums">{{ $faNum(number_format($paidAmount)) }} <span class="text-xs font-medium">{{ $currencyLabel }}</span></span>
                        </div>
                        <div
                            class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800/30 flex justify-between items-center">
                            <span class="font-bold text-amber-800 dark:text-amber-300 text-xs">مانده بدهی فاکتور:</span>
                            <span
                                class="font-black text-xl text-amber-700 dark:text-amber-200 tabular-nums">{{ $faNum(number_format($dueAmount)) }} <span class="text-xs font-medium">{{ $currencyLabel }}</span></span>
                        </div>
                    </div>
                </div>

                {{-- Wallet Card in Sidebar --}}
                @if(isset($customerWallet) && $customerWallet->balance > 0)
                    <div class="{{ $cardClass }} border-emerald-200 dark:border-emerald-800/50 bg-emerald-50/30 dark:bg-emerald-950/20">
                        <div class="p-4 border-b border-emerald-100 dark:border-emerald-800/30 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-emerald-500 text-white shadow-sm">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </span>
                                <div>
                                    <h3 class="text-xs font-black text-gray-900 dark:text-white">پرداخت از کیف پول مشتری</h3>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                         موجودی: <strong class="text-emerald-600 dark:text-emerald-400 font-black tabular-nums">{{ $faNum(number_format($customerWallet->balance * $conversionFactor)) }}</strong> {{ $currencyLabel }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <button type="button" @click="toggleWallet()"
                                    class="w-full py-2.5 px-4 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-2"
                                    :class="isWalletSelected() ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'bg-white dark:bg-gray-800 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 hover:bg-emerald-50'">
                                <span x-text="isWalletSelected() ? '✓ کسر از کیف پول فعال است (حذف)' : '+ افزودن کیف پول به پرداختی‌ها'"></span>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Payment Breakdown Sidebar Card --}}
                <div x-show="selectedRows.length > 0" x-transition class="{{ $cardClass }} border-indigo-200 dark:border-indigo-800/50">
                    <div class="p-4 border-b border-gray-100 dark:border-gray-700/50 bg-indigo-50/30 dark:bg-indigo-900/10">
                        <h3 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            ترکیب پرداختی‌های این فرم
                        </h3>
                    </div>
                    <div class="p-4 space-y-2.5 text-xs font-bold">
                        <div class="flex justify-between items-center text-gray-600 dark:text-gray-300" x-show="walletDeduction > 0">
                            <span>کسر از کیف پول:</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-black tabular-nums" x-text="formatPriceInput(walletDeduction) + ' {{ $currencyLabel }}'"></span>
                        </div>
                        <div class="flex justify-between items-center text-gray-600 dark:text-gray-300" x-show="totalChequesAmount > 0">
                            <span>مجموع چـک‌ها:</span>
                            <span class="text-amber-600 dark:text-amber-400 font-black tabular-nums" x-text="formatPriceInput(totalChequesAmount) + ' {{ $currencyLabel }}'"></span>
                        </div>
                        <div class="flex justify-between items-center text-gray-600 dark:text-gray-300" x-show="totalDirectPaymentsAmount > 0">
                            <span>مجموع مستقیم:</span>
                            <span class="text-indigo-600 dark:text-indigo-400 font-black tabular-nums" x-text="formatPriceInput(totalDirectPaymentsAmount) + ' {{ $currencyLabel }}'"></span>
                        </div>

                        <div class="pt-2.5 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center text-xs">
                            <span class="text-gray-800 dark:text-gray-100 font-black">مجموع پرداختی فرم:</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-black text-base tabular-nums" x-text="formatPriceInput(totalPaidInForm) + ' {{ $currencyLabel }}'"></span>
                        </div>

                        <div class="flex justify-between items-center text-[11px] pt-0.5 text-gray-500 dark:text-gray-400">
                            <span>مانده بدهی نهایی:</span>
                            <span class="font-black tabular-nums" :class="remainingUnpaidInForm === 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'" x-text="formatPriceInput(remainingUnpaidInForm) + ' {{ $currencyLabel }}'"></span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('paymentWizard', () => ({
                    invoiceTotal: {{ $invoiceTotal }},
                    dueAmount: {{ $dueAmount }},
                    walletBalance: {{ isset($customerWallet) ? ((float)$customerWallet->balance * $conversionFactor) : 0 }},
                    customerCheques: @json($customerCheques ?? []),
                    paymentType: null,
                    showMethodDetails: true,
                    paidAtDate: '',

                    selectedRows: [],

                    isWalletSelected() {
                        return this.selectedRows.some(r => r.type === 'wallet');
                    },

                    isChequeSelected(chequeId) {
                        return this.selectedRows.some(r => r.type === 'cheque' && r.cheque_id == chequeId);
                    },

                    isMethodSelected(methodId) {
                        return this.selectedRows.some(r => r.method === methodId);
                    },

                    canSelectCheque(cheque) {
                        if (this.isChequeSelected(cheque.id)) return true;
                        let chkAmt = parseFloat(cheque.display_amount || cheque.amount) || 0;
                        return chkAmt <= (this.remainingUnpaidInForm + 1);
                    },

                    setPaymentType(type) {
                        if (this.paymentType !== type) {
                            this.paymentType = type;
                            this.showMethodDetails = true;
                        }
                    },

                    toggleWallet() {
                        let index = this.selectedRows.findIndex(r => r.type === 'wallet');
                        if (index > -1) {
                            this.selectedRows.splice(index, 1);
                        } else {
                            if (this.walletBalance <= 0) {
                                alert('موجودی کیف پول مشتری صفر است.');
                                return;
                            }
                            let rem = this.remainingUnpaidInForm;
                            let initialWalletAmt = Math.min(this.walletBalance, rem > 0 ? rem : this.dueAmount);
                            this.selectedRows.push({
                                id: 'wallet',
                                type: 'wallet',
                                method: 'wallet',
                                title: 'پرداخت از کیف پول مشتری',
                                amount: this.formatPriceInput(initialWalletAmt),
                                transaction_id: 'کیف پول',
                                is_readonly_amount: false,
                                is_readonly_id: true
                            });
                        }
                    },

                    toggleCheque(cheque) {
                        let index = this.selectedRows.findIndex(r => r.type === 'cheque' && r.cheque_id == cheque.id);
                        if (index > -1) {
                            this.selectedRows.splice(index, 1);
                        } else {
                            let chkAmt = parseFloat(cheque.display_amount || cheque.amount) || 0;
                            let rem = this.remainingUnpaidInForm;
                            if (!this.canSelectCheque(cheque)) {
                                alert('مبلغ این چک (' + this.formatPriceInput(chkAmt) + ' ' + @json($currencyLabel) + ') بیشتر از مانده بدهی فاکتور است.');
                                return;
                            }
                            this.selectedRows.push({
                                id: 'cheque-' + cheque.id,
                                type: 'cheque',
                                cheque_id: cheque.id,
                                method: 'cheque-' + cheque.id,
                                title: 'چک صیادی #' + cheque.cheque_number + (cheque.bank_name ? ' (' + cheque.bank_name + ')' : ''),
                                amount: this.formatPriceInput(chkAmt),
                                transaction_id: cheque.cheque_number,
                                is_readonly_amount: true,
                                is_readonly_id: true
                            });
                        }
                    },

                    toggleMethod(methodId, title, gateway = '') {
                        let index = this.selectedRows.findIndex(r => r.method === methodId);
                        if (index > -1) {
                            this.selectedRows.splice(index, 1);
                        } else {
                            let rem = this.remainingUnpaidInForm;
                            let initialAmt = rem > 0 ? rem : this.dueAmount;
                            this.selectedRows.push({
                                id: methodId,
                                type: 'direct',
                                method: methodId,
                                title: title,
                                amount: this.formatPriceInput(initialAmt),
                                transaction_id: '',
                                gateway: gateway,
                                is_readonly_amount: false,
                                is_readonly_id: false
                            });
                        }
                    },

                    removeRow(index) {
                        this.selectedRows.splice(index, 1);
                    },

                    get walletDeduction() {
                        let r = this.selectedRows.find(item => item.type === 'wallet');
                        if (!r) return 0;
                        let amt = this.getNumericAmount(r.amount);
                        return Math.min(amt, Math.min(this.walletBalance, this.dueAmount));
                    },

                    get totalChequesAmount() {
                        return this.selectedRows
                            .filter(r => r.type === 'cheque')
                            .reduce((sum, r) => sum + this.getNumericAmount(r.amount), 0);
                    },

                    get totalDirectPaymentsAmount() {
                        return this.selectedRows
                            .filter(r => r.type === 'direct')
                            .reduce((sum, r) => sum + this.getNumericAmount(r.amount), 0);
                    },

                    get totalPaidInForm() {
                        return this.walletDeduction + this.totalChequesAmount + this.totalDirectPaymentsAmount;
                    },

                    get remainingUnpaidInForm() {
                        return Math.max(0, this.dueAmount - this.totalPaidInForm);
                    },

                    init() {
                        const oldWallet = @json(old('use_wallet'));
                        if (oldWallet) {
                            let oldWAmt = @json(old('wallet_amount', ''));
                            this.selectedRows.push({
                                id: 'wallet',
                                type: 'wallet',
                                method: 'wallet',
                                title: 'پرداخت از کیف پول مشتری',
                                amount: oldWAmt ? this.formatPriceInput(oldWAmt) : this.formatPriceInput(Math.min(this.walletBalance, this.dueAmount)),
                                transaction_id: 'کیف پول',
                                is_readonly_amount: false,
                                is_readonly_id: true
                            });
                        }

                        const oldChequeIds = @json(old('cheque_ids', []));
                        if (Array.isArray(oldChequeIds) && oldChequeIds.length > 0) {
                            this.paymentType = 'cheque';
                            oldChequeIds.forEach(cId => {
                                let cheque = this.customerCheques.find(c => String(c.id) === String(cId));
                                if (cheque) {
                                    this.selectedRows.push({
                                        id: 'cheque-' + cheque.id,
                                        type: 'cheque',
                                        cheque_id: cheque.id,
                                        method: 'cheque-' + cheque.id,
                                        title: 'چک صیادی #' + cheque.cheque_number + (cheque.bank_name ? ' (' + cheque.bank_name + ')' : ''),
                                        amount: this.formatPriceInput(cheque.amount),
                                        transaction_id: cheque.cheque_number,
                                        is_readonly_amount: true,
                                        is_readonly_id: true
                                    });
                                }
                            });
                        }

                        const oldPaymentItems = @json(old('payment_items', []));
                        if (typeof oldPaymentItems === 'object' && oldPaymentItems !== null) {
                            Object.keys(oldPaymentItems).forEach(key => {
                                let item = oldPaymentItems[key];
                                if (item && item.method) {
                                    this.selectedRows.push({
                                        id: item.method,
                                        type: 'direct',
                                        method: item.method,
                                        title: 'روش ' + item.method,
                                        amount: this.formatPriceInput(item.amount || ''),
                                        transaction_id: item.transaction_id || '',
                                        gateway: item.gateway || '',
                                        is_readonly_amount: false,
                                        is_readonly_id: false
                                    });
                                }
                            });
                        }

                        this.setDefaultDates();
                    },

                    gregorianToJalali(date) {
                        try {
                            const formatter = new Intl.DateTimeFormat('en-US', {
                                calendar: 'persian',
                                year: 'numeric',
                                month: '2-digit',
                                day: '2-digit'
                            });
                            let parts = formatter.formatToParts(date);
                            let y = parts.find(p => p.type === 'year').value;
                            let m = parts.find(p => p.type === 'month').value;
                            let d = parts.find(p => p.type === 'day').value;
                            return `${y}/${m.toString().padStart(2, '0')}/${d.toString().padStart(2, '0')}`;
                        } catch (e) {
                            return '';
                        }
                    },

                    setDefaultDates() {
                        const todayJalali = this.gregorianToJalali(new Date());
                        this.paidAtDate = todayJalali;
                        this.$nextTick(() => {
                            const el = document.querySelector('input[name="paid_at"]');
                            if (el) {
                                el.value = this.paidAtDate;
                                el.dispatchEvent(new Event('input', { bubbles: true }));
                                el.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        });
                    },

                    submitForm(event) {
                        let totalPaid = this.totalPaidInForm;

                        if (totalPaid <= 0) {
                            alert('لطفاً حداقل یک روش پرداخت را انتخاب یا مبلغی وارد کنید.');
                            return;
                        }

                        if (totalPaid > (this.dueAmount + 10)) {
                            alert('مجموع مبالغ پرداختی (' + this.formatPriceInput(totalPaid) + ' تومان) نمی‌تواند بیشتر از مانده بدهی فاکتور (' + this.formatPriceInput(this.dueAmount) + ' تومان) باشد.');
                            return;
                        }

                        this.$el.submit();
                    },

                    getNumericAmount(val) {
                        if (!val) return 0;
                        return Number(String(val)
                            .replace(/[\u06F0-\u06F9]/g, d => d.charCodeAt(0) - 0x06F0)
                            .replace(/[\u0660-\u0669]/g, d => d.charCodeAt(0) - 0x0660)
                            .replace(/[^\d]/g, ''));
                    },

                    formatPriceInput(val) {
                        if (val === null || val === undefined || val === '') return '';
                        let num_str = String(val)
                            .replace(/[\u06F0-\u06F9]/g, d => d.charCodeAt(0) - 0x06F0)
                            .replace(/[\u0660-\u0669]/g, d => d.charCodeAt(0) - 0x0660)
                            .replace(/[^\d]/g, '');
                        if (num_str === '') return '';
                        return Number(num_str).toLocaleString('en-US');
                    },
                }));
            });
        </script>
    @endpush
@endsection
