@extends('layouts.user')

@section('title', $isForInvoice ? 'ثبت پرداختی برای فاکتور' : 'ثبت رسید دریافت / پرداخت')

@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
@endphp

@section('content')
@includeIf('partials.jalali-date-picker')

    <form action="{{ route('admin.accounting.receipts.store') }}" method="POST" x-data="receiptFormHandlers({
        isForInvoice: {{ $isForInvoice ? 'true' : 'false' }},
        amount: '{{ old('amount', $isForInvoice ? $balanceDue : '') }}',
        paymentMethod: '{{ old('payment_method', 'cash') }}'
    })">
        @csrf
        @if($isForInvoice)
            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
        @endif

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-24">

            {{-- Header --}}
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </span>
                        <span x-show="!isForInvoice">ثبت رسید دستی</span>
                        <span x-show="isForInvoice">ثبت پرداختی برای فاکتور #{{ $invoice->display_number ?? '' }}</span>
                    </h1>
                </div>
            </div>

            {{-- Invoice Summary --}}
            @if($isForInvoice)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl">
                    <p class="text-sm text-gray-500 dark:text-gray-400">مبلغ کل فاکتور</p>
                    <p class="text-xl font-bold text-gray-800 dark:text-gray-200 font-mono">{{ number_format($invoice->total) }} <span class="text-xs">{{ $currencySuffix }}</span></p>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-xl">
                    <p class="text-sm text-green-600 dark:text-green-400">پرداختی قبلی</p>
                    <p class="text-xl font-bold text-green-800 dark:text-green-300 font-mono">{{ number_format($totalPaid) }} <span class="text-xs">{{ $currencySuffix }}</span></p>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-xl">
                    <p class="text-sm text-red-600 dark:text-red-400">مانده بدهی</p>
                    <p class="text-xl font-bold text-red-800 dark:text-red-300 font-mono">{{ number_format($balanceDue) }} <span class="text-xs">{{ $currencySuffix }}</span></p>
                </div>
            </div>
            @endif

            {{-- Form Card --}}
            <div class="{{ $cardClass }}">
                <div class="p-6 space-y-8">

                    {{-- Payment Method --}}
                    <div>
                        <label for="payment_method" class="{{ $labelClass }}">روش پرداخت <span class="text-red-500">*</span></label>
                        <select name="payment_method" id="payment_method" x-model="paymentMethod" class="{{ $inputClass }}">
                            <option value="cash">نقد / واریز به حساب</option>
                            <option value="cheque">دریافت چک</option>
                        </select>
                    </div>

                    {{-- Cash/Bank Transfer Fields --}}
                    <div x-show="paymentMethod === 'cash'" class="space-y-6 animate-in fade-in">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="amount" class="{{ $labelClass }}">مبلغ پرداختی ({{ $currencySuffix }}) <span class="text-red-500">*</span></label>
                                <input type="text" name="amount" id="amount" x-model="amount" class="{{ $inputClass }} text-xl font-black text-left dir-ltr text-indigo-700 dark:text-indigo-400 placeholder:text-gray-300" :required="paymentMethod === 'cash'" :disabled="paymentMethod !== 'cash'">
                            </div>
                            <div>
                                <label for="document_date" class="{{ $labelClass }}">تاریخ پرداخت <span class="text-red-500">*</span></label>
                                <input type="text" id="document_date" name="document_date" value="{{ old('document_date', jdate()->format('Y/m/d')) }}" x-model="documentDate" data-jdp class="{{ $inputClass }} dir-ltr text-center" :required="paymentMethod === 'cash'" :disabled="paymentMethod !== 'cash'">
                            </div>
                            <div>
                                <label for="fund_account_id" class="{{ $labelClass }}">واریز به حساب <span class="text-red-500">*</span></label>
                                <select name="fund_account_id" id="fund_account_id" class="{{ $inputClass }}" :required="paymentMethod === 'cash'" :disabled="paymentMethod !== 'cash'">
                                    <option value="">انتخاب کنید...</option>
                                    @foreach($fundAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('fund_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} ({{ $account->type === 'bank' ? 'بانک' : ($account->type === 'cash' ? 'صندوق' : 'درگاه') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @php
                                $receiptCatOptions = [];
                                foreach($categories as $category) {
                                    $receiptCatOptions[] = ['id' => (string)$category->id, 'title' => $category->title];
                                }
                            @endphp
                            <div x-show="!isForInvoice">
                                <label class="{{ $labelClass }}">طرف حساب / بابت <span class="text-red-500">*</span></label>
                                <div x-data="{
                                    open: false,
                                    search: '',
                                    options: @js($receiptCatOptions),
                                    get filteredOptions() {
                                        if (!this.search.trim()) return this.options;
                                        return this.options.filter(o => o.title.toLowerCase().includes(this.search.toLowerCase()));
                                    },
                                    select(opt) {
                                        categoryId = opt ? opt.id : '';
                                        this.open = false;
                                        this.search = '';
                                    },
                                    getSelectedTitle() {
                                        let found = this.options.find(o => String(o.id) === String(categoryId));
                                        return found ? found.title : 'انتخاب سرفصل...';
                                    }
                                }" class="relative">
                                    <input type="hidden" name="category_id" :value="categoryId">
                                    
                                    <button type="button" @click="open = !open" 
                                            class="{{ $inputClass }} flex items-center justify-between cursor-pointer w-full text-start">
                                        <span x-text="getSelectedTitle()" class="truncate text-sm"></span>
                                        <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>

                                    <div x-show="open" @click.outside="open = false" x-cloak 
                                         class="absolute z-50 mt-1.5 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl p-2 max-h-64 overflow-y-auto">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="search" placeholder="جستجو سرفصل / طرف حساب..." 
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                        </div>

                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                            <div @click="select(opt)" 
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors"
                                                 :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(categoryId) === String(opt.id) }"
                                                 x-text="opt.title">
                                            </div>
                                        </template>

                                        <div x-show="filteredOptions.length === 0" class="p-3 text-xs text-gray-400 text-center">
                                            هیچ سرفصلی پیدا نشد
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Cheque Fields --}}
                    <div x-show="paymentMethod === 'cheque'" class="space-y-6 border-t border-dashed border-gray-200 dark:border-gray-700 pt-6 animate-in fade-in">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="document_date_cheque" class="{{ $labelClass }}">تاریخ ثبت سند <span class="text-red-500">*</span></label>
                                <input type="text" id="document_date_cheque" name="document_date" value="{{ old('document_date', jdate()->format('Y/m/d')) }}" x-model="documentDate" data-jdp class="{{ $inputClass }} dir-ltr text-center" :required="paymentMethod === 'cheque'" :disabled="paymentMethod !== 'cheque'">
                            </div>
                            <div x-show="!isForInvoice">
                                <label class="{{ $labelClass }}">طرف حساب / بابت <span class="text-red-500">*</span></label>
                                <div x-data="{
                                    open: false,
                                    search: '',
                                    options: @js($receiptCatOptions),
                                    get filteredOptions() {
                                        if (!this.search.trim()) return this.options;
                                        return this.options.filter(o => o.title.toLowerCase().includes(this.search.toLowerCase()));
                                    },
                                    select(opt) {
                                        categoryId = opt ? opt.id : '';
                                        this.open = false;
                                        this.search = '';
                                    },
                                    getSelectedTitle() {
                                        let found = this.options.find(o => String(o.id) === String(categoryId));
                                        return found ? found.title : 'انتخاب سرفصل...';
                                    }
                                }" class="relative">
                                    <input type="hidden" name="category_id" :value="categoryId">
                                    
                                    <button type="button" @click="open = !open" 
                                            class="{{ $inputClass }} flex items-center justify-between cursor-pointer w-full text-start">
                                        <span x-text="getSelectedTitle()" class="truncate text-sm"></span>
                                        <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>

                                    <div x-show="open" @click.outside="open = false" x-cloak 
                                         class="absolute z-50 mt-1.5 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl p-2 max-h-64 overflow-y-auto">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="search" placeholder="جستجو سرفصل / طرف حساب..." 
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                        </div>

                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                            <div @click="select(opt)" 
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors"
                                                 :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(categoryId) === String(opt.id) }"
                                                 x-text="opt.title">
                                            </div>
                                        </template>

                                        <div x-show="filteredOptions.length === 0" class="p-3 text-xs text-gray-400 text-center">
                                            هیچ سرفصلی پیدا نشد
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="{{ $labelClass }} mb-0">انتخاب چک دریافتی (در جریان) <span class="text-red-500">*</span></label>
                                <a href="{{ route('admin.accounting.cheques.create', ['type' => 'receivable', 'redirect_to' => route('admin.accounting.receipts.create')]) }}"
                                   class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-3 py-1.5 rounded-xl border border-indigo-200/50 dark:border-indigo-500/20 transition-all hover:scale-105">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    <span>ثبت چک جدید</span>
                                </a>
                            </div>

                            <input type="hidden" name="cheque_id" :value="selectedChequeId" :disabled="paymentMethod !== 'cheque'">

                            @if($receivableCheques->isNotEmpty())
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto p-1">
                                    @foreach($receivableCheques as $ch)
                                        <div @click="selectedChequeId = '{{ $ch->id }}'; amount = '{{ number_format($ch->amount) }}'"
                                             :class="selectedChequeId == '{{ $ch->id }}'
                                                ? 'border-indigo-600 bg-indigo-50/80 dark:bg-indigo-900/30 shadow-md ring-2 ring-indigo-500/20 scale-[1.01] cursor-pointer'
                                                : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-800 bg-white dark:bg-gray-800 cursor-pointer'"
                                             class="relative flex flex-col justify-between rounded-2xl border-2 transition-all duration-200">

                                            <div class="flex items-center justify-between gap-2 p-4 mb-1">
                                                <div class="flex items-center gap-3">
                                                    <span class="flex items-center justify-center w-10 h-10 rounded-xl shrink-0 bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                    </span>
                                                    <div>
                                                        <div class="flex items-center gap-2 mb-0.5">
                                                            <span class="block text-[11px] font-bold text-gray-400 dark:text-gray-500">شماره صیادی</span>
                                                            <span class="px-2 py-0.5 text-[10px] font-black rounded-md bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">چک دریافتی</span>
                                                        </div>
                                                        <span class="text-sm font-black text-gray-900 dark:text-white tabular-nums tracking-wide">{{ $ch->cheque_number }}</span>
                                                    </div>
                                                </div>
                                                <div class="w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all duration-200"
                                                     :class="selectedChequeId == '{{ $ch->id }}' ? 'border-indigo-600 bg-indigo-600 text-white shadow-sm shadow-indigo-500/30' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800'">
                                                    <svg x-show="selectedChequeId == '{{ $ch->id }}'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </div>
                                            </div>

                                            <div class="space-y-2 p-4 border-t border-gray-100 dark:border-gray-700/60 text-xs">
                                                <div class="flex items-center justify-between">
                                                    <span class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 font-medium">
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        مبلغ چک:
                                                    </span>
                                                    <span class="font-black text-sm text-indigo-600 dark:text-indigo-400 tabular-nums">
                                                        {{ number_format($ch->amount) }} {{ $currencySuffix }}
                                                    </span>
                                                </div>

                                                <div class="flex items-center justify-between">
                                                    <span class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 font-medium">
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                        تاریخ سررسید:
                                                    </span>
                                                    <span class="font-bold text-gray-800 dark:text-gray-200 tabular-nums">{{ $ch->due_date_jalali ?? '—' }}</span>
                                                </div>

                                                @if($ch->bank_name)
                                                    <div class="flex items-center justify-between">
                                                        <span class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 font-medium">
                                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V5"/>
                                                            </svg>
                                                            بانک صادرکننده:
                                                        </span>
                                                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ $ch->bank_name }}</span>
                                                    </div>
                                                @endif

                                                @if($ch->payee_name || $ch->drawer_name)
                                                    <div class="flex items-center justify-between">
                                                        <span class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 font-medium">
                                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                            </svg>
                                                            صاحب حساب / صادرکننده:
                                                        </span>
                                                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ $ch->payee_name ?? $ch->drawer_name }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <p class="text-xs text-purple-600 dark:text-purple-400 font-bold flex items-center gap-1.5 mt-2">
                                    <svg class="w-4 h-4 text-purple-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    پس از ثبت رسید، وضعیت چک انتخاب‌شده به «خرج شده» با برچسب «به علت ثبت دستی» تغییر می‌یابد.
                                </p>
                            @else
                                <div class="p-6 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 text-center space-y-3">
                                    <p class="text-sm font-bold text-amber-800 dark:text-amber-300">هیچ چک دریافتی آزاد با وضعیت در جریان جهت انتخاب وجود ندارد.</p>
                                    <a href="{{ route('admin.accounting.cheques.create', ['type' => 'receivable', 'redirect_to' => route('admin.accounting.receipts.create')]) }}"
                                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 shadow-md shadow-indigo-500/20 transition-all">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                        <span>جهت ایجاد چک کلیک کنید (ثبت چک جدید)</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="{{ $labelClass }}">شرح سند <span class="text-red-500">*</span></label>
                        <textarea name="description" id="description" rows="3" class="{{ $inputClass }}" required>{{ old('description', $isForInvoice ? 'پرداخت بابت فاکتور شماره ' . $invoice->display_number : '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Sticky Footer --}}
            <div class="sticky bottom-4 z-40 max-w-4xl mx-auto">
                <div class="flex justify-between items-center bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
                    <a href="{{ url()->previous() }}" class="px-6 py-3 rounded-xl text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                        بازگشت
                    </a>
                    <button type="submit" class="px-8 py-3 rounded-xl text-white font-bold shadow-lg transition-all flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/30">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        <span>ثبت سند دریافت</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('receiptFormHandlers', (config) => ({
                isForInvoice: config.isForInvoice,
                paymentMethod: config.paymentMethod,
                amount: '',
                categoryId: '{{ old('category_id', '') }}',
                documentDate: '{{ old('document_date', jdate()->format('Y/m/d')) }}',
                selectedChequeId: '{{ old('cheque_id', session('cheque_id', request('cheque_id', ''))) }}',
                init() {
                    this.amount = this.formatNumber(config.amount);
                    this.$watch('amount', (value) => {
                        this.amount = this.formatNumber(value);
                    });
                    // Re-initialize jalali datepicker after Alpine has mounted the DOM
                    this.$nextTick(() => {
                        if (window.jalaliDatepicker) {
                            jalaliDatepicker.startWatch({
                                selector: '[data-jdp]',
                                minDate: 'attr',
                                date: true,
                                time: false
                            });
                        }
                    });
                },
                toEnglishDigits(value) {
                    if (value === null || typeof value === 'undefined') return '';
                    return String(value).replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[^0-9]/g, '');
                },
                formatNumber(value) {
                    const cleanValue = this.toEnglishDigits(value);
                    if (!cleanValue) return '';
                    return parseInt(cleanValue, 10).toLocaleString('en-US');
                }
            }));
        });
    </script>
    @endpush
@endsection
