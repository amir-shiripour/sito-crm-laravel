@extends('layouts.user')

@section('title', 'ثبت هزینه جدید')

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-6 backdrop-blur-xl";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-sm dark:border-gray-700 dark:bg-gray-900/50 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";
    $selectClass = $inputClass . " cursor-pointer";

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $mappedCategories = $categories->mapWithKeys(fn($category) => [
        $category->id => (str_contains($category->title, 'مشتری') || ($category->is_system && $category->title === 'مشتریان'))
    ]);

    $walletBankIds = $banks->filter(fn($b) => $b->isWalletAccount())->pluck('id')->map(fn($id) => (string)$id)->values()->toArray();
@endphp

@section('content')
@includeIf('partials.jalali-date-picker')

<form action="{{ route('admin.accounting.expenses.store') }}" method="POST" enctype="multipart/form-data" x-data="formHandlers()">
    @csrf
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-24">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                    <span
                        class="flex items-center justify-center w-12 h-12 rounded-2xl bg-rose-600 text-white shadow-lg shadow-rose-500/30">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </span>
                    ثبت هزینه جدید
                </h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">اطلاعات هزینه، سرفصل و روش پرداخت را وارد
                    نمایید.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.accounting.expenses.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-bold transition-all active:scale-95">
                    بازگشت به لیست
                </a>
            </div>
        </div>

        @if($errors->any())
            <div
                class="p-5 rounded-3xl bg-rose-50 dark:bg-rose-900/30 text-rose-800 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/50 space-y-2 shadow-sm">
                <div class="flex items-center gap-2 font-black text-sm">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>لطفاً خطاهای زیر را بررسی و اصلاح فرمایید:</span>
                </div>
                <ul class="list-disc list-inside text-xs font-bold space-y-1 ps-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="{{ $cardClass }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="description" class="{{ $labelClass }}">شرح و عنوان هزینه <span
                            class="text-rose-500">*</span></label>
                    <input type="text" name="description" id="description" value="{{ old('description') }}"
                           class="{{ $inputClass }}" placeholder="مثال: خرید ملزومات اداری / پرداخت قبض برق...">
                    @error('description')<p class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="amount" class="{{ $labelClass }}">مبلغ هزینه ({{ $currencySuffix }}) <span
                            class="text-rose-500">*</span></label>
                    <input type="text" name="amount" id="amount" x-model="amount"
                           class="{{ $inputClass }} dir-ltr text-left font-bold tabular-nums"
                           @input="formatNumber($el)">
                    @error('amount')<p class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="document_date" class="{{ $labelClass }}">تاریخ پرداخت <span
                            class="text-rose-500">*</span></label>
                    <input type="text" name="document_date" id="document_date" data-jdp data-jdp-only-date
                           x-model="documentDate" class="{{ $inputClass }} dir-ltr text-center font-bold">
                    @error('document_date')<p class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                </div>
                @php
                    $catOptions = [];
                    foreach($categories as $category) {
                        $catOptions[] = ['id' => (string)$category->id, 'title' => $category->title];
                    }
                @endphp
                <div>
                    <label class="{{ $labelClass }}">سرفصل هزینه <span class="text-rose-500">*</span></label>
                    <div x-data="{
                        open: false,
                        search: '',
                        options: @js($catOptions),
                        get filteredOptions() {
                            if (!this.search.trim()) return this.options;
                            return this.options.filter(o => o.title.toLowerCase().includes(this.search.toLowerCase()));
                        },
                        select(opt) {
                            if (opt) {
                                selectedCategoryId = opt.id;
                            } else {
                                selectedCategoryId = '';
                            }
                            updateCustomerVisibility();
                            this.open = false;
                            this.search = '';
                        },
                        getSelectedTitle() {
                            let found = this.options.find(o => String(o.id) === String(selectedCategoryId));
                            return found ? found.title : 'انتخاب سرفصل...';
                        }
                    }" class="relative">
                        <input type="hidden" name="category_id" :value="selectedCategoryId">
                        
                        <button type="button" @click="open = !open" 
                                class="{{ $inputClass }} flex items-center justify-between cursor-pointer w-full text-start">
                            <span x-text="getSelectedTitle()" class="truncate text-sm"></span>
                            <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div x-show="open" @click.outside="open = false" x-cloak 
                             class="absolute z-50 mt-1.5 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl p-2 max-h-64 overflow-y-auto">
                            <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                <input type="text" x-model="search" placeholder="جستجو در سرفصل‌های هزینه..." 
                                       class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                            </div>

                            <template x-for="opt in filteredOptions" :key="opt.id">
                                <div @click="select(opt)" 
                                     class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors"
                                     :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(selectedCategoryId) === String(opt.id) }"
                                     x-text="opt.title">
                                </div>
                            </template>

                            <div x-show="filteredOptions.length === 0" class="p-3 text-xs text-gray-400 text-center">
                                هیچ سرفصلی پیدا نشد
                            </div>
                        </div>
                    </div>
                    @error('category_id')<p class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                </div>

                <div x-show="isCategoryCustomer" x-cloak>
                    <label for="client_id_cat" class="{{ $labelClass }}">انتخاب مشتری <span class="text-rose-500">*</span></label>
                    <select :name="isCategoryCustomer ? 'client_id' : ''" id="client_id_cat" class="{{ $selectClass }}">
                        <option value="">انتخاب مشتری...</option>
                        @foreach($customers as $customer)
                            @php
                                $wb = $customer->wallet_balance ?? 0;
                                $wbFormatted = number_format($wb);
                            @endphp
                            <option value="{{ $customer->value_key ?? $customer->id }}"
                                    {{ old('client_id') == ($customer->value_key ?? $customer->id) ? 'selected' : '' }}
                                    :disabled="isWalletAccountSelected && ((paymentType === 'bank' ? cleanAmount : remainingAmount) > {{ $wb }})"
                                    class="disabled:text-gray-400 disabled:bg-gray-100 dark:disabled:bg-gray-800 dark:disabled:text-gray-600">
                                {{ $customer->display_name ?? ($customer->full_name ?: $customer->username) }}
                                — (موجودی کیف پول: {{ $faNum($wbFormatted) }} {{ $currencySuffix }})
                                @if($wb <= 0) [عدم موجودی] @endif
                            </option>
                        @endforeach
                    </select>
                    @error('client_id')<p class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                </div>

                <div
                    class="md:col-span-2 space-y-4 rounded-3xl bg-gray-50/80 dark:bg-gray-900/40 p-6 border border-gray-100 dark:border-gray-700/60">
                    <label class="{{ $labelClass }}">روش و منبع پرداخت <span class="text-rose-500">*</span></label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label
                            class="flex items-center gap-3.5 p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200"
                            :class="paymentType === 'bank' ? 'border-rose-500 bg-rose-50/70 dark:bg-rose-900/30 text-rose-950 dark:text-rose-100 font-bold shadow-md shadow-rose-500/10' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800'">
                            <input type="radio" name="payment_type" value="bank" x-model="paymentType"
                                   class="w-4 h-4 text-rose-600 focus:ring-rose-500">
                            <div class="flex items-center gap-3">
                                <span
                                    class="flex items-center justify-center w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <span class="text-sm">پرداخت از حساب خزانه‌داری (بانک / صندوق)</span>
                            </div>
                        </label>
                        <label
                            class="flex items-center gap-3.5 p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200"
                            :class="paymentType === 'cheque' ? 'border-rose-500 bg-rose-50/70 dark:bg-rose-900/30 text-rose-950 dark:text-rose-100 font-bold shadow-md shadow-rose-500/10' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800'">
                            <input type="radio" name="payment_type" value="cheque" x-model="paymentType"
                                   class="w-4 h-4 text-rose-600 focus:ring-rose-500">
                            <div class="flex items-center gap-3">
                                <span
                                    class="flex items-center justify-center w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </span>
                                <span class="text-sm">پرداخت / خرج چک (دریافتی یا پرداختی)</span>
                            </div>
                        </label>
                    </div>
                    <div x-show="paymentType === 'bank'" x-cloak class="pt-2">
                        <label for="bank_id" class="{{ $labelClass }}">انتخاب حساب خزانه‌داری <span
                                class="text-rose-500">*</span></label>
                        <select name="bank_id" id="bank_id" class="{{ $selectClass }}" x-model="selectedBankId" @change="updateCustomerVisibility()">
                            <option value="">انتخاب حساب خزانه‌داری...</option>
                            @foreach($banks as $bank)
                                @php
                                    $balVal = $bank->balance_val ?? 0;
                                    $balFormatted = number_format($balVal);
                                @endphp
                                <option value="{{ $bank->id }}"
                                        {{ old('bank_id') == $bank->id ? 'selected' : '' }}
                                        :disabled="{{ $bank->isWalletAccount() ? 'false' : ('cleanAmount > ' . $balVal) }}"
                                        class="disabled:text-gray-400 disabled:bg-gray-100 dark:disabled:bg-gray-800 dark:disabled:text-gray-600">
                                    {{ $bank->name }}{{ $bank->bank_name ? ' — ' . $bank->bank_name : '' }}
                                    @if(!$bank->isWalletAccount())
                                        — (موجودی: {{ $faNum($balFormatted) }} {{ $currencySuffix }}) @if($balVal <= 0) [عدم موجودی] @endif
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('bank_id')<p class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror

                        <div x-show="!isCategoryCustomer && isWalletAccountSelected" x-cloak class="mt-4 p-5 rounded-2xl bg-indigo-50/90 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700/60 space-y-2 shadow-sm">
                            <label for="client_id_bank" class="{{ $labelClass }}">انتخاب مشتری (جهت کسر از کیف پول) <span class="text-rose-500">*</span></label>
                            <select :name="(!isCategoryCustomer && isWalletAccountSelected && paymentType === 'bank') ? 'client_id' : ''" id="client_id_bank" class="{{ $selectClass }}">
                                <option value="">انتخاب مشتری صاحبان کیف پول...</option>
                                @foreach($customers as $customer)
                                    @php
                                        $wb = $customer->wallet_balance ?? 0;
                                        $wbFormatted = number_format($wb);
                                    @endphp
                                    <option value="{{ $customer->value_key ?? $customer->id }}"
                                            {{ old('client_id') == ($customer->value_key ?? $customer->id) ? 'selected' : '' }}
                                            :disabled="isWalletAccountSelected && ((paymentType === 'bank' ? cleanAmount : remainingAmount) > {{ $wb }})"
                                            class="disabled:text-gray-400 disabled:bg-gray-100 dark:disabled:bg-gray-800 dark:disabled:text-gray-600">
                                        {{ $customer->display_name ?? ($customer->full_name ?: $customer->username) }}
                                        — (موجودی کیف پول: {{ $faNum($wbFormatted) }} {{ $currencySuffix }})
                                        @if($wb <= 0) [عدم موجودی] @endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-indigo-600 dark:text-indigo-400 font-bold flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                مبلغ این هزینه پس از ثبت، از موجودی کیف پول این مشتری کسر خواهد شد.
                            </p>
                            @error('client_id')<p class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div x-show="paymentType === 'cheque'" x-cloak class="pt-2 space-y-4">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <label class="{{ $labelClass }}">انتخاب یک یا چند چک (دریافتی / پرداختی) <span
                                    class="text-rose-500">*</span></label>
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400">
                                تعداد چک‌های آماده: <strong class="text-rose-600 dark:text-rose-400"
                                                            x-text="receivableCheques.length"></strong>
                                <span x-show="selectedChequeIds.length > 0"
                                      class="ms-2 text-emerald-600 dark:text-emerald-400 font-bold">
                                    (انتخاب شده: <span x-text="selectedChequeIds.length"></span> عدد — مجموع: <span
                                        x-text="Number(totalChequesAmount).toLocaleString('en-US')"></span> {{ $currencySuffix }})
                                </span>
                            </span>
                        </div>

                        <template x-for="id in selectedChequeIds" :key="id">
                            <input type="hidden" name="cheque_ids[]" :value="id">
                        </template>

                        <template x-if="receivableCheques.length === 0">
                            <div
                                class="p-6 text-center bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700">
                                <p class="text-sm font-bold text-gray-500 dark:text-gray-400">هیچ چک دریافتی یا پرداختی
                                    آماده‌ای در سیستم وجود ندارد.</p>
                            </div>
                        </template>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto p-1">
                            <template x-for="cheque in receivableCheques" :key="cheque.id">
                                <div @click="toggleCheque(cheque)"
                                     :class="{
                                        'border-rose-500 p-4 bg-rose-50/80 dark:bg-rose-900/30 shadow-md ring-2 ring-rose-500/20 scale-[1.01] cursor-pointer': isChequeSelected(cheque.id),
                                        'border-gray-200 dark:border-gray-700 hover:border-rose-300 dark:hover:border-rose-800 bg-white dark:bg-gray-800 cursor-pointer': !isChequeSelected(cheque.id) && canSelectCheque(cheque),
                                        'opacity-50 bg-gray-100 dark:bg-gray-800/40 border-gray-200 dark:border-gray-700/60 cursor-not-allowed': !isChequeSelected(cheque.id) && !canSelectCheque(cheque)
                                     }"
                                     class="relative flex flex-col justify-between p-4.5 rounded-2xl border-2 transition-all duration-200">

                                    <div class="flex items-center justify-between gap-2 p-4 mb-3">
                                        <div class="flex items-center gap-3">
                                            <span class="flex items-center justify-center w-10 h-10 rounded-xl shrink-0"
                                                  :class="cheque.type === 'payable' ? 'bg-indigo-500/10 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400' : 'bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400'">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </span>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="block text-[11px] font-bold text-gray-400 dark:text-gray-500">شماره صیادی</span>
                                                    <span class="px-2 py-0.5 text-[10px] font-black rounded-md"
                                                          :class="cheque.type === 'payable' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300'"
                                                          x-text="cheque.type === 'payable' ? 'چک پرداختی' : 'چک دریافتی'"></span>
                                                </div>
                                                <span
                                                    class="text-sm font-black text-gray-900 dark:text-white tabular-nums tracking-wide"
                                                    x-text="cheque.cheque_number"></span>
                                            </div>
                                        </div>
                                        <div
                                            class="w-6 h-6  rounded-lg border-2 flex items-center justify-center transition-all duration-200"
                                            :class="isChequeSelected(cheque.id) ? 'border-rose-600 bg-rose-600 text-white shadow-sm shadow-rose-500/30' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800'">
                                            <svg x-show="isChequeSelected(cheque.id)" class="w-4 h-4" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="space-y-2 p-4 border-t border-gray-100 dark:border-gray-700/60 text-xs">
                                        <div class="flex items-center justify-between">
                                            <span
                                                class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 font-medium">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                مبلغ چک:
                                            </span>
                                            <span
                                                class="font-black text-sm text-rose-600 dark:text-rose-400 tabular-nums"
                                                x-text="Number(cheque.amount).toLocaleString('en-US') + ' ' + currencySuffix"></span>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <span
                                                class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 font-medium">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                تاریخ سررسید:
                                            </span>
                                            <span class="font-bold text-gray-800 dark:text-gray-200 tabular-nums"
                                                  x-text="cheque.due_date_jalali || '—'"></span>
                                        </div>

                                        <template x-if="cheque.bank_name">
                                            <div class="flex items-center justify-between">
                                                <span
                                                    class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 font-medium">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V5"/>
                                                    </svg>
                                                    بانک صادرکننده:
                                                </span>
                                                <span class="font-bold text-gray-700 dark:text-gray-300"
                                                      x-text="cheque.bank_name"></span>
                                            </div>
                                        </template>
                                    </div>
                                    <template x-if="!isChequeSelected(cheque.id) && !canSelectCheque(cheque)">
                                        <div class="mt-3 p-3 border-t border-amber-200/60 dark:border-amber-900/40">
                                            <div
                                                class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-500/10 text-amber-800 dark:text-amber-300 border border-amber-500/20 text-[11px] font-bold">
                                                <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                                <span>غیرقابل انتخاب (فراتر از مبلغ کل هزینه)</span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <template x-if="selectedChequeIds.length > 0 && remainingAmount > 0">
                            <div
                                class="mt-4 p-5 rounded-3xl bg-amber-50/90 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 space-y-4 shadow-sm">
                                <div
                                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs font-bold">
                                    <div class="flex items-center gap-2 text-amber-900 dark:text-amber-300">
                                        <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>مجموع مبالغ چک‌های انتخاب شده کمتر از کل هزینه است (پرداخت ترکیبی):</span>
                                    </div>
                                    <div class="flex items-center gap-4 flex-wrap">
                                        <span class="text-gray-600 dark:text-gray-400">مجموع چک‌ها: <strong
                                                class="text-amber-700 dark:text-amber-400 tabular-nums"
                                                x-text="Number(totalChequesAmount).toLocaleString('en-US') + ' ' + currencySuffix"></strong></span>
                                        <span class="text-rose-600 dark:text-rose-400">مانده: <strong
                                                class="text-rose-700 dark:text-rose-300 tabular-nums font-black text-sm"
                                                x-text="Number(remainingAmount).toLocaleString('en-US') + ' ' + currencySuffix"></strong></span>
                                    </div>
                                </div>
                                <div class="pt-2 border-t border-amber-200/60 dark:border-amber-800/40">
                                    <label for="bank_id_split" class="{{ $labelClass }}">انتخاب حساب خزانه‌داری جهت
                                        پرداخت مانده (بانک / صندوق) <span class="text-rose-500">*</span></label>
                                    <select name="bank_id" id="bank_id_split" class="{{ $selectClass }}"
                                            x-model="selectedBankId" @change="updateCustomerVisibility()">
                                        <option value="">انتخاب حساب خزانه‌داری جهت پرداخت مانده...</option>
                                        @foreach($banks as $bank)
                                            @php
                                                $balVal = $bank->balance_val ?? 0;
                                                $balFormatted = number_format($balVal);
                                            @endphp
                                            <option value="{{ $bank->id }}"
                                                    {{ old('bank_id') == $bank->id ? 'selected' : '' }}
                                                    :disabled="{{ $bank->isWalletAccount() ? 'false' : ('remainingAmount > ' . $balVal) }}"
                                                    class="disabled:text-gray-400 disabled:bg-gray-100 dark:disabled:bg-gray-800 dark:disabled:text-gray-600">
                                                {{ $bank->name }}{{ $bank->bank_name ? ' — ' . $bank->bank_name : '' }}
                                                @if(!$bank->isWalletAccount())
                                                    — (موجودی: {{ $faNum($balFormatted) }} {{ $currencySuffix }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('bank_id')<p
                                        class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror

                                    <div x-show="!isCategoryCustomer && isWalletAccountSelected" x-cloak class="mt-4 p-5 rounded-2xl bg-indigo-50/90 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700/60 space-y-2 shadow-sm">
                                        <label for="client_id_split" class="{{ $labelClass }}">انتخاب مشتری (جهت کسر مانده از کیف پول) <span class="text-rose-500">*</span></label>
                                        <select :name="(!isCategoryCustomer && isWalletAccountSelected && paymentType === 'cheque') ? 'client_id' : ''" id="client_id_split" class="{{ $selectClass }}">
                                            <option value="">انتخاب مشتری صاحبان کیف پول...</option>
                                            @foreach($customers as $customer)
                                                @php
                                                    $wb = $customer->wallet_balance ?? 0;
                                                    $wbFormatted = number_format($wb);
                                                @endphp
                                                <option value="{{ $customer->value_key ?? $customer->id }}"
                                                        {{ old('client_id') == ($customer->value_key ?? $customer->id) ? 'selected' : '' }}
                                                        :disabled="isWalletAccountSelected && (remainingAmount > {{ $wb }})"
                                                        class="disabled:text-gray-400 disabled:bg-gray-100 dark:disabled:bg-gray-800 dark:disabled:text-gray-600">
                                                    {{ $customer->display_name ?? ($customer->full_name ?: $customer->username) }}
                                                    — (موجودی کیف پول: {{ $faNum($wbFormatted) }} {{ $currencySuffix }})
                                                    @if($wb <= 0) [عدم موجودی] @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="mt-1 text-xs text-indigo-600 dark:text-indigo-400 font-bold flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            مبلغ مانده هزینه پس از ثبت، از موجودی کیف پول این مشتری کسر خواهد شد.
                                        </p>
                                        @error('client_id')<p class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedChequeIds.length > 0 && remainingAmount === 0">
                            <div
                                class="mt-4 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700/50 flex items-center gap-3 text-xs font-bold text-emerald-800 dark:text-emerald-300 shadow-sm">
                                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>مبلغ هزینه به‌طور کامل با چک‌های انتخاب شده پوشش داده شد و نیازی به کسر از حساب خزانه‌داری نمی‌باشد.</span>
                            </div>
                        </template>

                        @error('cheque_ids')<p class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label for="reference_number" class="{{ $labelClass }}">شماره پیگیری / فیش (اختیاری)</label>
                    <input type="text" name="reference_number" id="reference_number" x-model="referenceNumber"
                           class="{{ $inputClass }} dir-ltr text-left">
                    @error('reference_number')<p
                        class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">فایل ضمیمه (اختیاری)</label>
                    <div
                        x-data="fileUploader()"
                        @dragover.prevent="dragging = true"
                        @dragleave.prevent="dragging = false"
                        @drop.prevent="handleDrop($event)"
                        :class="dragging
                            ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-900/20 scale-[1.01]'
                            : 'border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/30'"
                        class="relative flex flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed p-8 text-center transition-all duration-200 cursor-pointer group"
                        @click="$refs.fileInput.click()"
                    >
                        <div
                            class="w-14 h-14 rounded-2xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                            <svg class="w-7 h-7 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>

                        <div>
                            <p class="text-sm font-bold text-gray-700 dark:text-gray-200" x-show="!fileName">
                                فایل را اینجا رها کنید یا
                                <span class="text-indigo-500">انتخاب کنید</span>
                            </p>
                            <p class="text-sm font-bold text-indigo-600 dark:text-indigo-400"
                               x-show="fileName" x-text="fileName" style="display:none"></p>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                PDF، تصویر — حداکثر ۲ مگابایت
                            </p>
                        </div>

                        <input type="file" name="attachment" id="attachment"
                               class="hidden" x-ref="fileInput"
                               @change="handleFileChange($event)"
                               accept=".pdf,.jpg,.jpeg,.png,.gif,.webp">
                    </div>
                    @error('attachment')<p class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                </div>

            </div>
        </div>
        <div class="sticky bottom-4 z-40 max-w-4xl mx-auto">
            <div
                class="flex justify-between items-center bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xl">
                <a href="{{ route('admin.accounting.expenses.index') }}"
                   class="px-6 py-3 rounded-xl text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                    انصراف
                </a>
                <button type="submit"
                        class="px-8 py-3.5 rounded-xl bg-rose-600 text-white font-bold text-sm shadow-lg shadow-rose-500/30 hover:bg-rose-700 transition-all flex items-center gap-2 active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    ثبت و ذخیره هزینه
                </button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('formHandlers', () => ({
                amount: '{{ old('amount', '') }}',
                documentDate: '{{ old('document_date', jdate()->format('Y/m/d')) }}',
                referenceNumber: '{{ old('reference_number', '') }}',
                defaultDocumentDate: '{{ old('document_date', jdate()->format('Y/m/d')) }}',
                defaultReferenceNumber: '{{ old('reference_number', '') }}',
                paymentType: '{{ old('payment_type', 'bank') }}',
                selectedCategoryId: '{{ old('category_id', '') }}',
                selectedBankId: '{{ old('bank_id', '') }}',
                selectedChequeIds: @json(old('cheque_ids', [])),
                showCustomerSelect: false,
                currencySuffix: '{{ $currencySuffix }}',
                categories: @json($mappedCategories),
                walletBankIds: @json($walletBankIds),
                receivableCheques: @json($receivableCheques),

                get cleanAmount() {
                    let val = String(this.amount || '').replace(/[^0-9]/g, '');
                    return val ? parseInt(val, 10) : 0;
                },

                get totalChequesAmount() {
                    return this.receivableCheques
                        .filter(c => this.selectedChequeIds.map(String).includes(String(c.id)))
                        .reduce((sum, c) => sum + (parseFloat(c.amount) || 0), 0);
                },

                get remainingAmount() {
                    return Math.max(0, this.cleanAmount - this.totalChequesAmount);
                },

                isChequeSelected(chequeId) {
                    return this.selectedChequeIds.map(String).includes(String(chequeId));
                },

                canSelectCheque(cheque) {
                    if (this.isChequeSelected(cheque.id)) return true;
                    return (this.totalChequesAmount + parseFloat(cheque.amount)) <= this.cleanAmount;
                },

                get isCategoryCustomer() {
                    return !!this.categories[this.selectedCategoryId];
                },

                get isWalletAccountSelected() {
                    return this.walletBankIds.map(String).includes(String(this.selectedBankId));
                },

                get isCustomerRequired() {
                    return this.isCategoryCustomer || this.isWalletAccountSelected;
                },

            init() {
                this.$watch('paymentType', (newType) => {
                    if (newType === 'bank') {
                        this.selectedChequeIds = [];
                        this.documentDate = this.defaultDocumentDate;
                        this.referenceNumber = this.defaultReferenceNumber;
                        let dateInput = document.getElementById('document_date');
                        if (dateInput) {
                            dateInput.value = this.defaultDocumentDate;
                        }
                        let refInput = document.getElementById('reference_number');
                        if (refInput) {
                            refInput.value = this.defaultReferenceNumber;
                        }
                    }
                });
            },
            toggleCheque(cheque) {
                let strId = String(cheque.id);
                let index = this.selectedChequeIds.map(String).indexOf(strId);
                if (index > -1) {
                    this.selectedChequeIds.splice(index, 1);
                } else {
                    if (!this.canSelectCheque(cheque)) return;
                    this.selectedChequeIds.push(cheque.id);
                    if (this.selectedChequeIds.length === 1) {
                        if (cheque.due_date_jalali) {
                            this.documentDate = cheque.due_date_jalali;
                            let dateInput = document.getElementById('document_date');
                            if (dateInput) {
                                dateInput.value = cheque.due_date_jalali;
                            }
                        }
                        if (!this.referenceNumber) {
                            this.referenceNumber = 'چک ' + cheque.cheque_number;
                        }
                    }
                }
            },
            formatNumber(el) {
                let val = el.value.replace(/[^0-9]/g, '');
                this.amount = val ? parseInt(val, 10).toLocaleString('en-US') : '';
                el.value = this.amount;
            }
        }));
    });

    function fileUploader() {
        return {
            dragging: false,
            fileName: '',
            handleFileChange(e) {
                const file = e.target.files[0];
                this.fileName = file ? file.name : '';
            },
            handleDrop(e) {
                this.dragging = false;
                const file = e.dataTransfer.files[0];
                if (!file) return;
                this.fileName = file.name;
                const dt = new DataTransfer();
                dt.items.add(file);
                this.$refs.fileInput.files = dt.files;
            }
        }
    }
</script>
@endpush
@endsection
