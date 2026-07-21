@extends('layouts.user')
@section('title', 'ثبت پرداختی فاکتور #' . $invoice->invoice_number)

@include('partials.jalali-date-picker')

@php
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-sm dark:border-gray-700 dark:bg-gray-900/50 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1";
    $cardClass  = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl";

    $currency      = $currency ?? 'toman';
    $currencyLabel = $currency === 'rial' ? 'ریال' : 'تومان';

    $useGlobalPayment = ($settings['services_use_global_payment_settings'] ?? '0') === '1';

    $activePaymentMethods = [];
    $onlineGateways = [];
    $bankTransferAccounts = [];
    $posDevices = [];
    $codEnabled = false;

    if ($useGlobalPayment) {
        $activePaymentMethods = json_decode($settings['active_payment_methods'] ?? '[]', true);

        if (($settings['zarinpal_status'] ?? 'inactive') === 'active') $onlineGateways['zarinpal'] = 'زرین‌پال';
        if (($settings['zibal_status'] ?? 'inactive') === 'active') $onlineGateways['zibal'] = 'زیبال';
        if (($settings['behpardakht_status'] ?? 'inactive') === 'active') $onlineGateways['behpardakht'] = 'به‌پرداخت ملت';

        $bankTransferAccounts = in_array('transfer', $activePaymentMethods) ? json_decode($settings['bank_transfer_accounts'] ?? '[]', true) : [];
        $posDevices = in_array('pos', $activePaymentMethods) ? json_decode($settings['pos_devices'] ?? '[]', true) : [];
        $codEnabled = in_array('cod', $activePaymentMethods);
    }

    $hasOnline = $useGlobalPayment && in_array('online', $activePaymentMethods) && count($onlineGateways) > 0;
    $hasManual = !$useGlobalPayment || !empty($bankTransferAccounts) || !empty($posDevices) || $codEnabled;

    $invoiceTotal = $invoice->total;
    $paidAmount = $invoice->paid_amount;
    $dueAmount = $invoiceTotal - $paidAmount;

    $faNum = function($str) { if (is_null($str)) return ''; $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹']; return str_replace(range(0,9), $persian, (string)$str); };
@endphp

@section('content')
    <style>[x-cloak] {
            display: none !important;
        }</style>

    {{-- توسعه عرض کانتینر به سایز 2XL --}}
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="paymentWizard()">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-lg shadow-emerald-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path
                            stroke-linecap="round" stroke-linejoin="round"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </span>
                ثبت پرداختی <span
                    class="text-emerald-600 dark:text-emerald-400 font-black tabular-nums">#{{ $faNum($invoice->invoice_number) }}</span>
            </h1>
            <a href="{{ route('services.invoices.show', $invoice) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                <svg class="w-5 h-5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت به فاکتور
            </a>
        </div>

        @if($errors->any())
            <div
                class="p-5 text-sm text-red-800 rounded-2xl bg-red-50 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20 flex items-start gap-4 shadow-sm">
                <span class="bg-red-100 text-red-600 dark:bg-red-500/20 p-2 rounded-full shrink-0 mt-0.5"><svg
                        class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path
                            stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
                <div>
                    <p class="font-black text-base mb-2">خطا در ثبت اطلاعات!</p>
                    <ul class="list-disc ps-5 space-y-1.5 marker:text-red-400">@foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach</ul>
                </div>
            </div>
        @endif

        <form id="paymentForm" action="{{ route('services.invoices.payment.store', $invoice) }}" method="POST"
              @submit.prevent="submitForm"
              class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            @csrf
            <input type="hidden" name="payment_method" :value="selectedMethod">
            <input type="hidden" name="gateway" :value="paymentType === 'online' ? selectedMethod : ''">

            {{-- Left Column: Method Selection (وسیع‌تر در دسکتاپ بزرگ) --}}
            <div class="lg:col-span-8 xl:col-span-9 space-y-8">
                {{-- Step 1: Payment Type --}}
                <div class="{{ $cardClass }}">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex items-center gap-3">
                        <span
                            class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-500 text-white font-bold text-sm">۱</span>
                        <h2 class="text-lg font-black text-gray-800 dark:text-gray-100">انتخاب نوع پرداخت</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        @if($hasOnline)
                            <div @click="setPaymentType('online')"
                                 class="flex flex-col items-center justify-center text-center gap-4 p-8 rounded-2xl border-2 transition-all cursor-pointer"
                                 :class="paymentType === 'online' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 shadow-md' : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-800'">
                                <div
                                    class="w-16 h-16 rounded-xl bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-center transition-colors text-indigo-600 dark:text-indigo-400">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15A2.25 2.25 0 002.25 6.75v10.5A2.25 2.25 0 004.5 21z"/>
                                    </svg>
                                </div>
                                <span class="text-lg font-bold transition-colors"
                                      :class="paymentType === 'online' ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-300'">پرداخت آنلاین</span>
                            </div>
                        @endif
                        @if($hasManual)
                            <div @click="setPaymentType('manual')"
                                 class="flex flex-col items-center justify-center text-center gap-4 p-8 rounded-2xl border-2 transition-all cursor-pointer"
                                 :class="paymentType === 'manual' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 shadow-md' : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-800'">
                                <div
                                    class="w-16 h-16 rounded-xl bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-center transition-colors text-indigo-600 dark:text-indigo-400">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6A.75.75 0 012.25 5.25v-.75m0 0A.75.75 0 013 4.5A.75.75 0 013.75 3.75m0 0A.75.75 0 014.5 4.5A.75.75 0 013.75 4.5m5.25 0v.75A.75.75 0 019 6A.75.75 0 018.25 5.25v-.75m0 0A.75.75 0 019 4.5A.75.75 0 018.25 4.5m5.25 0v.75A.75.75 0 0115 6a.75.75 0 01-.75-.75v-.75m0 0a.75.75 0 01.75.75a.75.75 0 01-.75.75M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5"/>
                                    </svg>
                                </div>
                                <span class="text-lg font-bold transition-colors"
                                      :class="paymentType === 'manual' ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-300'">پرداخت نقدی / انتقال بانکی</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Step 2: Specific Method --}}
                <div x-show="paymentType" x-transition class="{{ $cardClass }}">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex items-center gap-3">
                        <span
                            class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-500 text-white font-bold text-sm">۲</span>
                        <h2 class="text-lg font-black text-gray-800 dark:text-gray-100">انتخاب روش دقیق پرداخت</h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div x-show="paymentType === 'online'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                            @foreach($onlineGateways as $key => $name)
                                <label
                                    class="flex items-center gap-4 p-5 rounded-2xl border-2 transition-all cursor-pointer"
                                    :class="selectedMethod === '{{ $key }}' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-indigo-200 dark:hover:border-indigo-800'">
                                    <input type="radio" name="method_radio" value="{{ $key }}" x-model="selectedMethod"
                                           class="w-5 h-5 text-indigo-600 focus:ring-indigo-500/50 dark:bg-gray-800 dark:border-gray-600">
                                    <span class="text-base font-bold text-gray-800 dark:text-gray-200">{{ $name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div x-show="paymentType === 'manual'" x-cloak class="space-y-6">
                            @if(!$useGlobalPayment)
                                <div>
                                    <h3 class="text-base font-bold text-gray-500 dark:text-gray-400 mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
                                        ثبت دستی پرداخت</h3>
                                    <label
                                        class="flex items-start text-start gap-4 p-5 rounded-2xl border-2 transition-all cursor-pointer"
                                        :class="selectedMethod === 'cash' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-indigo-200 dark:hover:border-indigo-800'">
                                        <input type="radio" name="method_radio" value="cash" x-model="selectedMethod"
                                               class="mt-1 w-5 h-5 text-indigo-600 focus:ring-indigo-500/50 dark:bg-gray-800 dark:border-gray-600">
                                        <div class="flex-1">
                                            <span class="text-base font-bold text-gray-800 dark:text-gray-100">نقدی / انتقال بانکی مستقیم</span>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">پس از انجام پرداخت،
                                                می‌توانید شماره ارجاع یا پیگیری را در مرحله بعد وارد کنید.</p>
                                        </div>
                                    </label>
                                </div>
                            @else
                                @if(!empty($bankTransferAccounts))
                                    <div>
                                        <h3 class="text-base font-bold text-gray-500 dark:text-gray-400 mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
                                            کارت به کارت / حواله بانکی</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach($bankTransferAccounts as $account)
                                                <label
                                                    class="flex items-start text-start gap-4 p-5 rounded-2xl border-2 transition-all cursor-pointer"
                                                    :class="selectedMethod === 'transfer-{{ $account['id'] ?? '' }}' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-indigo-200 dark:hover:border-indigo-800'">
                                                    <input type="radio" name="method_radio"
                                                           value="transfer-{{ $account['id'] ?? '' }}"
                                                           x-model="selectedMethod"
                                                           class="mt-1 w-5 h-5 text-indigo-600 focus:ring-indigo-500/50 dark:bg-gray-800 dark:border-gray-600">
                                                    <div class="flex-1">
                                                        <span
                                                            class="text-base font-bold text-gray-800 dark:text-gray-100">{{ $account['name'] ?? 'حساب بانکی' }}</span>
                                                        <div
                                                            class="text-sm text-gray-500 dark:text-gray-400 mt-3 space-y-2">
                                                            @if(!empty($account['account_number']))
                                                                <p>شماره حساب: <strong
                                                                        class="font-mono text-gray-700 dark:text-gray-300">{{ $account['account_number'] }}</strong>
                                                                </p>
                                                            @endif
                                                            @if(!empty($account['card_number']))
                                                                <p>شماره کارت: <strong
                                                                        class="font-mono text-gray-700 dark:text-gray-300">{{ $account['card_number'] }}</strong>
                                                                </p>
                                                            @endif
                                                            @if(!empty($account['iban']))
                                                                <p>شماره شبا: <strong
                                                                        class="font-mono text-gray-700 dark:text-gray-300">{{ $account['iban'] }}</strong>
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($posDevices))
                                    <div>
                                        <h3 class="text-base font-bold text-gray-500 dark:text-gray-400 mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
                                            کارتخوان (POS)</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach($posDevices as $device)
                                                <label
                                                    class="flex items-start text-start gap-4 p-5 rounded-2xl border-2 transition-all cursor-pointer"
                                                    :class="selectedMethod === 'pos-{{ $device['id'] ?? '' }}' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-indigo-200 dark:hover:border-indigo-800'">
                                                    <input type="radio" name="method_radio"
                                                           value="pos-{{ $device['id'] ?? '' }}"
                                                           x-model="selectedMethod"
                                                           class="mt-1 w-5 h-5 text-indigo-600 focus:ring-indigo-500/50 dark:bg-gray-800 dark:border-gray-600">
                                                    <div>
                                                        <span
                                                            class="text-base font-bold text-gray-800 dark:text-gray-100">{{ $device['name'] ?? 'کارتخوان' }}</span>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ $device['description'] ?? 'پرداخت با دستگاه پوز' }}</p>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                @if($codEnabled)
                                    <div>
                                        <h3 class="text-base font-bold text-gray-500 dark:text-gray-400 mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
                                            پرداخت در محل</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <label
                                                class="flex items-start text-start gap-4 p-5 rounded-2xl border-2 transition-all cursor-pointer"
                                                :class="selectedMethod === 'cod' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-indigo-200 dark:hover:border-indigo-800'">
                                                <input type="radio" name="method_radio" value="cod"
                                                       x-model="selectedMethod"
                                                       class="mt-1 w-5 h-5 text-indigo-600 focus:ring-indigo-500/50 dark:bg-gray-800 dark:border-gray-600">
                                                <div>
                                                    <span class="text-base font-bold text-gray-800 dark:text-gray-100">پرداخت در محل</span>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ $settings['cod_guidance'] ?? 'پرداخت به پیک هنگام تحویل' }}</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Summary & Details (سایدبار برای رزولوشن عریض) --}}
            <div class="lg:col-span-4 xl:col-span-3 space-y-8 lg:sticky lg:top-8">
                {{-- Invoice Financial Summary --}}
                <div class="{{ $cardClass }}">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700/50">
                        <h3 class="text-lg font-black text-gray-800 dark:text-gray-100">خلاصه وضعیت فاکتور</h3>
                    </div>
                    <div class="p-6 space-y-5 text-base">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-500 dark:text-gray-400">مشتری:</span>
                            <span
                                class="font-bold text-gray-800 dark:text-gray-200">{{ $invoice->client_name ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-500 dark:text-gray-400">مبلغ کل:</span>
                            <span
                                class="font-black text-gray-800 dark:text-gray-200 tabular-nums">{{ $faNum(number_format($invoiceTotal)) }} <span class="text-sm font-medium">{{ $currencyLabel }}</span></span>
                        </div>
                        <div class="flex justify-between items-center border-b border-dashed border-gray-200 dark:border-gray-700 pb-5">
                            <span class="font-bold text-gray-500 dark:text-gray-400">پرداخت شده:</span>
                            <span
                                class="font-black text-emerald-600 dark:text-emerald-400 tabular-nums">{{ $faNum(number_format($paidAmount)) }} <span class="text-sm font-medium">{{ $currencyLabel }}</span></span>
                        </div>
                        <div
                            class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800/30 flex justify-between items-center">
                            <span class="font-bold text-amber-800 dark:text-amber-300">مانده بدهی:</span>
                            <span
                                class="font-black text-2xl text-amber-700 dark:text-amber-200 tabular-nums">{{ $faNum(number_format($dueAmount)) }} <span class="text-sm font-medium">{{ $currencyLabel }}</span></span>
                        </div>
                    </div>
                </div>

                {{-- Step 3: Deposit Details --}}
                <div x-show="selectedMethod" x-transition class="{{ $cardClass }}">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex items-center gap-3">
                        <span
                            class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-500 text-white font-bold text-sm">۳</span>
                        <h2 class="text-lg font-black text-gray-800 dark:text-gray-100">ثبت اطلاعات واریز</h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label for="payment_amount" class="{{ $labelClass }}">مبلغ پرداختی</label>
                            <div class="relative">
                                <input type="text" id="payment_amount" name="amount" x-model="amount"
                                       @input="amount = formatPriceInput($event.target.value)"
                                       class="{{ $inputClass }} text-center font-black text-xl tracking-widest py-4"
                                       placeholder="مبلغ را وارد کنید...">
                                <span
                                    class="absolute left-5 top-1/2 -translate-y-1/2 font-medium text-gray-400">{{ $currencyLabel }}</span>
                            </div>
                        </div>
                        <div>
                            <label for="payment_date" class="{{ $labelClass }}">تاریخ واریز</label>
                            <input type="text" id="payment_date" name="paid_at" x-model="paidAtDate" readonly data-jdp-only-date
                                   class="{{ $inputClass }} text-center cursor-pointer font-bold"
                                   placeholder="انتخاب تاریخ...">
                        </div>
                        <div>
                            <label for="tracking_number" class="{{ $labelClass }}">کد پیگیری / ارجاع (اختیاری)</label>
                            <input type="text" id="tracking_number" name="transaction_id"
                                   class="{{ $inputClass }} text-center font-mono" placeholder="شماره پیگیری بانک...">
                        </div>
                        <button type="submit"
                                class="w-full px-8 py-4.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-black text-lg shadow-xl shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:from-emerald-400 hover:to-emerald-500 transition-all duration-300 active:scale-95 flex items-center justify-center gap-3 mt-4">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            ثبت نهایی پرداختی
                        </button>
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
                    paymentType: null,
                    selectedMethod: @json(old('payment_method', '')),
                    amount: '',
                    paidAtDate: '',

                    init() {
                        this.amount = this.formatPriceInput(@json(old('amount', '')));
                        this.setDefaultDates();

                        const oldMethod = @json(old('payment_method', ''));
                        if (oldMethod) {
                            const onlineMethods = @json(array_keys($onlineGateways));
                            if (onlineMethods.includes(oldMethod)) {
                                this.paymentType = 'online';
                            } else if (oldMethod.startsWith('transfer-') || oldMethod.startsWith('pos-') || oldMethod === 'cod' || oldMethod === 'cash') {
                                this.paymentType = 'manual';
                            }
                        }

                        this.$watch('selectedMethod', (value) => {
                            if (value) {
                                this.amount = this.formatPriceInput(this.dueAmount);
                            }
                        });
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

                    setPaymentType(type) {
                        if (this.paymentType !== type) {
                            this.paymentType = type;
                            this.selectedMethod = null;
                        }
                    },

                    submitForm(event) {
                        const amountInput = this.$el.querySelector('#payment_amount');
                        let numericAmount = this.getNumericAmount(this.amount);

                        if (numericAmount > this.dueAmount) {
                            alert('مبلغ پرداختی نمی‌تواند بیشتر از مانده بدهی باشد.');
                            return;
                        }

                        amountInput.value = numericAmount;
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
                        if (!val) return '';
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
