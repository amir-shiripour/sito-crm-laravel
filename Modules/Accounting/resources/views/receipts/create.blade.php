@extends('layouts.user')

@section('title', $isForInvoice ? 'ثبت پرداختی برای فاکتور' : 'ثبت رسید دریافت / پرداخت')

@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";

    $catTypePriority = ['asset' => 1, 'liability' => 2, 'equity' => 3, 'income' => 4, 'expense' => 5];
    $categoriesList = $categories->sort(function($a, $b) use ($catTypePriority) {
        $pA = $catTypePriority[$a->type] ?? 99;
        $pB = $catTypePriority[$b->type] ?? 99;
        if ($pA !== $pB) return $pA <=> $pB;
        $codeA = (string) ($a->account_code ?? '');
        $codeB = (string) ($b->account_code ?? '');
        if ($codeA !== $codeB) return strcmp($codeA, $codeB);
        return strcmp($a->title ?? '', $b->title ?? '');
    })->map(function($cat) {
        $typeLabels = [
            'asset' => 'دارایی',
            'liability' => 'بدهی',
            'equity' => 'سرمایه',
            'income' => 'درآمد',
            'expense' => 'هزینه',
        ];
        $typeBadges = [
            'asset' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800/50',
            'liability' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200 dark:border-amber-800/50',
            'equity' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border-purple-200 dark:border-purple-800/50',
            'income' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50',
            'expense' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 border-rose-200 dark:border-rose-800/50',
        ];
        return [
            'id' => (string) $cat->id,
            'title' => $cat->title,
            'account_code' => $cat->account_code ?? '',
            'type' => $cat->type,
            'type_label' => $typeLabels[$cat->type] ?? $cat->type,
            'type_badge' => $typeBadges[$cat->type] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        ];
    })->values()->all();

    $fundTypePriority = ['bank' => 1, 'cash' => 2, 'gateway' => 3, 'petty_cash' => 4];
    $fundAccountsList = $fundAccounts->sort(function($a, $b) use ($fundTypePriority) {
        $pA = $fundTypePriority[$a->type] ?? 99;
        $pB = $fundTypePriority[$b->type] ?? 99;
        if ($pA !== $pB) return $pA <=> $pB;
        return strcmp($a->name ?? '', $b->name ?? '');
    })->map(function($fa) {
        $types = [
            'bank' => 'بانک',
            'cash' => 'صندوق',
            'gateway' => 'درگاه پرداخت',
            'petty_cash' => 'تنخواه',
        ];
        $typeBadges = [
            'bank' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20',
            'cash' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20',
            'gateway' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-500/10 dark:text-purple-400 dark:border-purple-500/20',
            'petty_cash' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
        ];
        return [
            'id' => (string) $fa->id,
            'name' => $fa->name,
            'bank_name' => $fa->bank_name ?? '',
            'account_number' => $fa->account_number ?? '',
            'type' => $fa->type,
            'type_label' => $types[$fa->type] ?? ($fa->type ?: 'خزانه'),
            'type_badge' => $typeBadges[$fa->type] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
            'current_balance' => (float) ($fa->balance_val ?? $fa->current_balance ?? 0),
            'balance_val' => (float) ($fa->balance_val ?? $fa->current_balance ?? 0),
        ];
    })->values()->all();
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

        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-24">

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
                        <span
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <span x-show="!isForInvoice">ثبت رسید دستی</span>
                        <span x-show="isForInvoice">ثبت پرداختی برای فاکتور #{{ $invoice->display_number ?? '' }}</span>
                    </h1>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ $isForInvoice ? route('admin.accounting.invoices.show', $invoice->id) : route('admin.accounting.documents.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-bold transition-all active:scale-95">
                        بازگشت به لیست
                    </a>
                </div>
            </div>

            {{-- Invoice Summary --}}
            @if($isForInvoice)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                    <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl">
                        <p class="text-sm text-gray-500 dark:text-gray-400">مبلغ کل فاکتور</p>
                        <p class="text-xl font-bold text-gray-800 dark:text-gray-200 font-mono">{{ number_format($invoice->total) }}
                            <span class="text-xs">{{ $currencySuffix }}</span></p>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-xl">
                        <p class="text-sm text-green-600 dark:text-green-400">پرداختی قبلی</p>
                        <p class="text-xl font-bold text-green-800 dark:text-green-300 font-mono">{{ number_format($totalPaid) }}
                            <span class="text-xs">{{ $currencySuffix }}</span></p>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-xl">
                        <p class="text-sm text-red-600 dark:text-red-400">مانده بدهی</p>
                        <p class="text-xl font-bold text-red-800 dark:text-red-300 font-mono">{{ number_format($balanceDue) }}
                            <span class="text-xs">{{ $currencySuffix }}</span></p>
                    </div>
                </div>
            @endif

            {{-- Form Card --}}
            <div class="{{ $cardClass }}">
                <div class="p-6 space-y-8">

                    {{-- Payment Method --}}
                    <div>
                        <label for="payment_method" class="{{ $labelClass }}">روش پرداخت <span
                                class="text-red-500">*</span></label>
                        <select name="payment_method" id="payment_method" x-model="paymentMethod"
                                class="{{ $inputClass }}">
                            <option value="cash">نقد / واریز به حساب</option>
                            <option value="cheque">دریافت چک</option>
                        </select>
                    </div>

                    {{-- Cash/Bank Transfer Fields --}}
                    <div x-show="paymentMethod === 'cash'" class="space-y-6 animate-in fade-in">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="amount" class="{{ $labelClass }}">مبلغ پرداختی ({{ $currencySuffix }}) <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="amount" id="amount" x-model="amount"
                                       @input="formatAmountInput($el)"
                                       class="{{ $inputClass }} text-xl font-black text-left dir-ltr text-indigo-700 dark:text-indigo-400 placeholder:text-gray-300"
                                       :required="paymentMethod === 'cash'" :disabled="paymentMethod !== 'cash'">
                            </div>
                            <div>
                                <label for="document_date" class="{{ $labelClass }}">تاریخ پرداخت <span
                                        class="text-red-500">*</span></label>
                                <input type="text" id="document_date" name="document_date"
                                       value="{{ old('document_date', jdate()->format('Y/m/d')) }}"
                                       x-model="documentDate" data-jdp class="{{ $inputClass }} dir-ltr text-center"
                                       :required="paymentMethod === 'cash'" :disabled="paymentMethod !== 'cash'">
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">واریز به حساب خزانه‌داری <span
                                        class="text-red-500">*</span></label>
                                <div x-data="{
                                    open: false,
                                    search: '',
                                    options: @js($fundAccountsList),
                                    get filteredOptions() {
                                        if (!this.search.trim()) return this.options;
                                        const q = this.search.toLowerCase();
                                        return this.options.filter(o =>
                                            (o.name && o.name.toLowerCase().includes(q)) ||
                                            (o.bank_name && o.bank_name.toLowerCase().includes(q)) ||
                                            (o.account_number && String(o.account_number).toLowerCase().includes(q)) ||
                                            (o.type_label && o.type_label.toLowerCase().includes(q))
                                        );
                                    },
                                    select(opt) {
                                        fundAccountId = opt ? String(opt.id) : '';
                                        this.open = false;
                                        this.search = '';
                                    },
                                    getSelectedTitle() {
                                        let found = this.options.find(o => String(o.id) === String(fundAccountId));
                                        if (!found) return 'انتخاب حساب خزانه‌داری...';
                                        return found.name + (found.bank_name ? ' (' + found.bank_name + ')' : '');
                                    },
                                    formatFa(str) {
                                        if (!str && str !== 0) return '';
                                        const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                        return String(str).replace(/[0-9]/g, w => farsi[+w]);
                                    },
                                    formatNumber(num) {
                                        if (!num && num !== 0) return '۰';
                                        return this.formatFa(Number(num).toLocaleString('en-US'));
                                    }
                                }" class="relative" :class="{ 'z-50': open }">
                                    <input type="hidden" name="fund_account_id" :value="fundAccountId"
                                           :required="paymentMethod === 'cash'" :disabled="paymentMethod !== 'cash'">

                                    <button type="button" @click="open = !open"
                                            class="{{ $inputClass }} flex items-center justify-between cursor-pointer w-full text-start py-2.5 px-3.5 text-xs sm:text-sm">
                                        <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                        <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] sm:min-w-[340px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="search"
                                                   placeholder="جستجو نام حساب، بانک، شماره حساب..."
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                        </div>

                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                            <div @click="select(opt)"
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                 :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(fundAccountId) === String(opt.id) }">
                                                <div class="flex flex-col gap-0.5 truncate">
                                                    <div class="flex items-center gap-1.5 truncate font-medium">
                                                        <span x-text="opt.name"></span>
                                                        <span x-show="opt.bank_name" class="text-[10px] text-gray-400"
                                                              x-text="'(' + opt.bank_name + ')'"></span>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-[10px] text-gray-400">
                                                        <span x-show="opt.account_number"
                                                              x-text="'ش‌ح: ' + formatFa(opt.account_number)"></span>
                                                        <span class="text-emerald-600 dark:text-emerald-400 font-bold"
                                                              x-text="'موجودی: ' + formatNumber(opt.current_balance) + ' {{ $currencySuffix }}'"></span>
                                                    </div>
                                                </div>
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                    :class="opt.type_badge"
                                                    x-text="opt.type_label"></span>
                                            </div>
                                        </template>

                                        <div x-show="filteredOptions.length === 0"
                                             class="p-3 text-xs text-gray-400 text-center">
                                            هیچ حساب خزانه‌داری پیدا نشد
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div x-show="!isForInvoice">
                                <label class="{{ $labelClass }}">طرف حساب / بابت <span
                                        class="text-red-500">*</span></label>
                                <div x-data="{
                                    open: false,
                                    search: '',
                                    options: @js($categoriesList),
                                    get filteredOptions() {
                                        if (!this.search.trim()) return this.options;
                                        const q = this.search.toLowerCase();
                                        return this.options.filter(o =>
                                            o.title.toLowerCase().includes(q) ||
                                            (o.account_code && String(o.account_code).toLowerCase().includes(q)) ||
                                            (o.type_label && o.type_label.toLowerCase().includes(q))
                                        );
                                    },
                                    select(opt) {
                                        categoryId = opt ? String(opt.id) : '';
                                        this.open = false;
                                        this.search = '';
                                    },
                                    getSelectedTitle() {
                                        let found = this.options.find(o => String(o.id) === String(categoryId));
                                        if (!found) return 'انتخاب سرفصل...';
                                        return found.title + (found.type_label ? ' (' + found.type_label + ')' : '');
                                    },
                                    formatFa(str) {
                                        if (!str) return '';
                                        const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                        return String(str).replace(/[0-9]/g, w => farsi[+w]);
                                    }
                                }" class="relative" :class="{ 'z-50': open }">
                                    <input type="hidden" name="category_id" :value="categoryId">

                                    <button type="button" @click="open = !open"
                                            class="{{ $inputClass }} flex items-center justify-between cursor-pointer w-full text-start py-2.5 px-3.5 text-xs sm:text-sm">
                                        <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                        <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] sm:min-w-[340px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="search"
                                                   placeholder="جستجو سرفصل، کد یا ماهیت..."
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                        </div>

                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                            <div @click="select(opt)"
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                 :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(categoryId) === String(opt.id) }">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <span x-show="opt.account_code" class="text-[10px] text-gray-400"
                                                          x-text="formatFa(opt.account_code)"></span>
                                                    <span x-text="opt.title" class="truncate"></span>
                                                </div>
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                    :class="opt.type_badge"
                                                    x-text="opt.type_label"></span>
                                            </div>
                                        </template>

                                        <div x-show="filteredOptions.length === 0"
                                             class="p-3 text-xs text-gray-400 text-center">
                                            هیچ سرفصلی پیدا نشد
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Cheque Fields --}}
                    <div x-show="paymentMethod === 'cheque'"
                         class="space-y-6 border-t border-dashed border-gray-200 dark:border-gray-700 pt-6 animate-in fade-in">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="document_date_cheque" class="{{ $labelClass }}">تاریخ ثبت سند <span
                                        class="text-red-500">*</span></label>
                                <input type="text" id="document_date_cheque" name="document_date"
                                       value="{{ old('document_date', jdate()->format('Y/m/d')) }}"
                                       x-model="documentDate" data-jdp class="{{ $inputClass }} dir-ltr text-center"
                                       :required="paymentMethod === 'cheque'" :disabled="paymentMethod !== 'cheque'">
                            </div>
                            <div x-show="!isForInvoice">
                                <label class="{{ $labelClass }}">طرف حساب / بابت <span
                                        class="text-red-500">*</span></label>
                                <div x-data="{
                                    open: false,
                                    search: '',
                                    options: @js($categoriesList),
                                    get filteredOptions() {
                                        if (!this.search.trim()) return this.options;
                                        const q = this.search.toLowerCase();
                                        return this.options.filter(o =>
                                            o.title.toLowerCase().includes(q) ||
                                            (o.account_code && String(o.account_code).toLowerCase().includes(q)) ||
                                            (o.type_label && o.type_label.toLowerCase().includes(q))
                                        );
                                    },
                                    select(opt) {
                                        categoryId = opt ? String(opt.id) : '';
                                        this.open = false;
                                        this.search = '';
                                    },
                                    getSelectedTitle() {
                                        let found = this.options.find(o => String(o.id) === String(categoryId));
                                        if (!found) return 'انتخاب سرفصل...';
                                        return found.title + (found.type_label ? ' (' + found.type_label + ')' : '');
                                    },
                                    formatFa(str) {
                                        if (!str) return '';
                                        const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                        return String(str).replace(/[0-9]/g, w => farsi[+w]);
                                    }
                                }" class="relative" :class="{ 'z-50': open }">
                                    <input type="hidden" name="category_id" :value="categoryId">

                                    <button type="button" @click="open = !open"
                                            class="{{ $inputClass }} flex items-center justify-between cursor-pointer w-full text-start py-2.5 px-3.5 text-xs sm:text-sm">
                                        <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                        <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] sm:min-w-[340px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="search"
                                                   placeholder="جستجو سرفصل، کد یا ماهیت..."
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                        </div>

                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                            <div @click="select(opt)"
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                 :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(categoryId) === String(opt.id) }">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <span x-show="opt.account_code" class="text-[10px] text-gray-400"
                                                          x-text="formatFa(opt.account_code)"></span>
                                                    <span x-text="opt.title" class="truncate"></span>
                                                </div>
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                    :class="opt.type_badge"
                                                    x-text="opt.type_label"></span>
                                            </div>
                                        </template>

                                        <div x-show="filteredOptions.length === 0"
                                             class="p-3 text-xs text-gray-400 text-center">
                                            هیچ سرفصلی پیدا نشد
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="{{ $labelClass }} mb-0">انتخاب چک دریافتی (در جریان) <span
                                        class="text-red-500">*</span></label>
                                <a href="{{ route('admin.accounting.cheques.create', ['type' => 'receivable', 'redirect_to' => route('admin.accounting.receipts.create')]) }}"
                                   class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-3 py-1.5 rounded-xl border border-indigo-200/50 dark:border-indigo-500/20 transition-all hover:scale-105">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>ثبت چک جدید</span>
                                </a>
                            </div>

                            <input type="hidden" name="cheque_id" :value="selectedChequeId"
                                   :disabled="paymentMethod !== 'cheque'">

                            @if($receivableCheques->isNotEmpty())
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto p-1">
                                    @foreach($receivableCheques as $ch)
                                        <div
                                            @click="selectedChequeId = '{{ $ch->id }}'; amount = formatRawNumber('{{ $ch->amount }}')"
                                            :class="selectedChequeId == '{{ $ch->id }}'
                                                ? 'border-indigo-600 bg-indigo-50/80 dark:bg-indigo-900/30 shadow-md ring-2 ring-indigo-500/20 scale-[1.01] cursor-pointer'
                                                : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-800 bg-white dark:bg-gray-800 cursor-pointer'"
                                            class="relative flex flex-col justify-between rounded-2xl border-2 transition-all duration-200">

                                            <div class="flex items-center justify-between gap-2 p-4 mb-1">
                                                <div class="flex items-center gap-3">
                                                    <span
                                                        class="flex items-center justify-center w-10 h-10 rounded-xl shrink-0 bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="1.8">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                    </span>
                                                    <div>
                                                        <div class="flex items-center gap-2 mb-0.5">
                                                            <span
                                                                class="block text-[11px] font-bold text-gray-400 dark:text-gray-500">شماره صیادی</span>
                                                            <span
                                                                class="px-2 py-0.5 text-[10px] font-black rounded-md bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">چک دریافتی</span>
                                                        </div>
                                                        <span
                                                            class="text-sm font-black text-gray-900 dark:text-white tabular-nums tracking-wide">{{ $ch->cheque_number }}</span>
                                                    </div>
                                                </div>
                                                <div
                                                    class="w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all duration-200"
                                                    :class="selectedChequeId == '{{ $ch->id }}' ? 'border-indigo-600 bg-indigo-600 text-white shadow-sm shadow-indigo-500/30' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800'">
                                                    <svg x-show="selectedChequeId == '{{ $ch->id }}'" class="w-4 h-4"
                                                         fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                         stroke-width="3">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </div>
                                            </div>

                                            <div
                                                class="space-y-2 p-4 border-t border-gray-100 dark:border-gray-700/60 text-xs">
                                                <div class="flex items-center justify-between">
                                                    <span
                                                        class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 font-medium">
                                                        <svg class="w-4 h-4 text-gray-400" fill="none"
                                                             viewBox="0 0 24 24" stroke="currentColor"
                                                             stroke-width="1.8">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        مبلغ چک:
                                                    </span>
                                                    <span
                                                        class="font-black text-sm text-indigo-600 dark:text-indigo-400 tabular-nums">
                                                        {{ number_format($ch->amount) }} {{ $currencySuffix }}
                                                    </span>
                                                </div>

                                                <div class="flex items-center justify-between">
                                                    <span
                                                        class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 font-medium">
                                                        <svg class="w-4 h-4 text-gray-400" fill="none"
                                                             viewBox="0 0 24 24" stroke="currentColor"
                                                             stroke-width="1.8">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                        تاریخ سررسید:
                                                    </span>
                                                    <span
                                                        class="font-bold text-gray-800 dark:text-gray-200 tabular-nums">{{ $ch->due_date_jalali ?? '—' }}</span>
                                                </div>

                                                @if($ch->bank_name)
                                                    <div class="flex items-center justify-between">
                                                        <span
                                                            class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 font-medium">
                                                            <svg class="w-4 h-4 text-gray-400" fill="none"
                                                                 viewBox="0 0 24 24" stroke="currentColor"
                                                                 stroke-width="1.8">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V5"/>
                                                            </svg>
                                                            بانک صادرکننده:
                                                        </span>
                                                        <span
                                                            class="font-bold text-gray-700 dark:text-gray-300">{{ $ch->bank_name }}</span>
                                                    </div>
                                                @endif

                                                @if($ch->payee_name || $ch->drawer_name)
                                                    <div class="flex items-center justify-between">
                                                        <span
                                                            class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 font-medium">
                                                            <svg class="w-4 h-4 text-gray-400" fill="none"
                                                                 viewBox="0 0 24 24" stroke="currentColor"
                                                                 stroke-width="1.8">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                            </svg>
                                                            صاحب حساب / صادرکننده:
                                                        </span>
                                                        <span
                                                            class="font-bold text-gray-700 dark:text-gray-300">{{ $ch->payee_name ?? $ch->drawer_name }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <p class="text-xs text-purple-600 dark:text-purple-400 font-bold flex items-center gap-1.5 mt-2">
                                    <svg class="w-4 h-4 text-purple-500 shrink-0" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    پس از ثبت رسید، وضعیت چک انتخاب‌شده به «خرج شده» با برچسب «به علت ثبت دستی» تغییر
                                    می‌یابد.
                                </p>
                            @else
                                <div
                                    class="p-6 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 text-center space-y-3">
                                    <p class="text-sm font-bold text-amber-800 dark:text-amber-300">هیچ چک دریافتی آزاد
                                        با وضعیت در جریان جهت انتخاب وجود ندارد.</p>
                                    <a href="{{ route('admin.accounting.cheques.create', ['type' => 'receivable', 'redirect_to' => route('admin.accounting.receipts.create')]) }}"
                                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 shadow-md shadow-indigo-500/20 transition-all">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span>جهت ایجاد چک کلیک کنید (ثبت چک جدید)</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Live Treasury & Payment Sources Balance Card --}}
                    <div x-show="selectedFundAccountsSummary.length > 0"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform -translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl relative z-20">
                        <div
                            class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50 dark:bg-gray-900/30">
                            <div class="flex items-center gap-3">
                                <span
                                    class="flex items-center justify-center w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                </span>
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        <span>موجودی لحظه‌ای حساب‌های خزانه‌داری / پرداخت انتخابی</span>
                                        <span
                                            class="px-2 py-0.5 rounded-md text-xs font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/20"
                                            x-text="`${formatNumber(selectedFundAccountsSummary.length)} منبع ${paymentMethod === 'cheque' ? 'چک' : 'خزانه'}`"></span>
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">نمایش زنده موجودی فعلی،
                                        تفکیک مبالغ واریز در این سند و موجودی نهایی</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <template x-for="item in selectedFundAccountsSummary" :key="item.id">
                                    <div
                                        class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/60 space-y-3 shadow-sm">
                                        <div
                                            class="flex items-center justify-between pb-2.5 border-b border-gray-100 dark:border-gray-700/50">
                                            <div class="space-y-0.5 truncate">
                                                <div class="flex items-center gap-2 truncate">
                                                    <span class="w-2 h-2 rounded-full"
                                                          :class="item.is_cheque ? 'bg-amber-500' : 'bg-indigo-500'"></span>
                                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate"
                                                        x-text="item.name"></h4>
                                                </div>
                                                <template x-if="item.sub_title">
                                                    <p class="text-[11px] text-gray-400 truncate"
                                                       x-text="item.sub_title"></p>
                                                </template>
                                            </div>
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg border shrink-0"
                                                  :class="item.type_badge" x-text="item.type_label"></span>
                                        </div>

                                        <div class="space-y-2 text-xs">
                                            <div
                                                class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/50">
                                                <span class="text-gray-500 dark:text-gray-400">
                                                    <template
                                                        x-if="item.is_cheque"><span>مبلغ صیادی چک:</span></template>
                                                    <template
                                                        x-if="!item.is_cheque"><span>موجودی فعلی حساب:</span></template>
                                                </span>
                                                <div
                                                    class="flex items-center gap-1 font-bold text-gray-800 dark:text-gray-200 tabular-nums">
                                                    <span x-text="formatNumber(item.initialBalance)"></span>
                                                    <span
                                                        class="text-[10px] text-gray-400 font-normal">{{ $currencySuffix }}</span>
                                                </div>
                                            </div>

                                            <template x-if="!item.is_cheque">
                                                <div
                                                    class="flex items-center justify-between p-2.5 rounded-xl bg-emerald-50/60 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20">
                                                    <span class="text-emerald-700 dark:text-emerald-400 font-medium">مبلغ واریز در این سند:</span>
                                                    <span
                                                        class="font-bold text-emerald-700 dark:text-emerald-400 tabular-nums"
                                                        x-text="item.docDebit > 0 ? '+' + formatNumber(item.docDebit) + ' {{ $currencySuffix }}' : '۰'"></span>
                                                </div>
                                            </template>

                                            <template x-if="item.is_cheque">
                                                <div
                                                    class="flex items-center justify-between p-2.5 rounded-xl bg-indigo-50/60 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20">
                                                    <span class="text-indigo-700 dark:text-indigo-400 font-medium">مبلغ رسید چک صیادی:</span>
                                                    <span
                                                        class="font-bold text-indigo-700 dark:text-indigo-400 tabular-nums"
                                                        x-text="formatNumber(item.initialBalance) + ' {{ $currencySuffix }}'"></span>
                                                </div>
                                            </template>

                                            <template x-if="!item.is_cheque">
                                                <div
                                                    class="flex items-center justify-between p-2.5 rounded-xl border font-bold"
                                                    :class="item.newBalance < 0
                                                            ? 'bg-rose-50/60 dark:bg-rose-500/10 border-rose-200/80 dark:border-rose-500/20 text-rose-700 dark:text-rose-400'
                                                            : 'bg-emerald-50/60 dark:bg-emerald-500/10 border-emerald-200/80 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400'">
                                                    <span>موجودی پس از واریز:</span>
                                                    <div class="flex items-center gap-1 tabular-nums">
                                                        <span class="text-sm font-black"
                                                              x-text="formatSignedNumber(item.newBalance)"></span>
                                                        <span
                                                            class="text-[10px] font-normal opacity-75">{{ $currencySuffix }}</span>
                                                    </div>
                                                </div>
                                            </template>

                                            <template x-if="item.is_cheque">
                                                <div
                                                    class="flex items-center justify-between p-2.5 rounded-xl border font-bold bg-amber-50/60 dark:bg-amber-500/10 border-amber-200/80 dark:border-amber-500/20 text-amber-700 dark:text-amber-400">
                                                    <span>وضعیت چک صیادی:</span>
                                                    <span
                                                        class="px-2 py-0.5 text-[10px] font-bold rounded-lg border shadow-sm"
                                                        :class="item.cheque_status_badge"
                                                        x-text="item.cheque_status_label">
                                                    </span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="{{ $labelClass }}">شرح سند <span
                                class="text-red-500">*</span></label>
                        <textarea name="description" id="description" rows="3" class="{{ $inputClass }}"
                                  required>{{ old('description', $isForInvoice ? 'پرداخت بابت فاکتور شماره ' . $invoice->display_number : '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Sticky Footer --}}
            <div class="sticky bottom-4 z-40 max-w-screen-2xl mx-auto">
                <div
                    class="flex justify-between items-center bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
                    <a href="{{ $isForInvoice ? route('admin.accounting.invoices.show', $invoice->id) : route('admin.accounting.documents.index') }}"
                       class="px-6 py-3 rounded-xl text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                        بازگشت
                    </a>
                    <button type="submit"
                            class="px-8 py-3.5 rounded-xl text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 active:scale-95 transition-all flex items-center gap-2 bg-indigo-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
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
                fundAccountId: '{{ old('fund_account_id', '') }}',
                categoryId: '{{ old('category_id', '') }}',
                documentDate: '{{ old('document_date', jdate()->format('Y/m/d')) }}',
                selectedChequeId: '{{ old('cheque_id', session('cheque_id', request('cheque_id', ''))) }}',
                currencySuffix: '{{ $currencySuffix }}',
                fundAccounts: @json($fundAccountsList),
                receivableCheques: @json($receivableCheques),

                cleanNumber(val) {
                    if (val === null || val === undefined) return 0;
                    let clean = String(val).replace(/[^0-9]/g, '');
                    return clean ? parseInt(clean, 10) : 0;
                },

                get cleanAmount() {
                    return this.cleanNumber(this.amount);
                },

                formatFa(str) {
                    if (!str && str !== 0) return '';
                    const farsi = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                    return String(str).replace(/[0-9]/g, w => farsi[+w]);
                },

                formatNumber(num) {
                    if (num === null || num === undefined || num === '') return '۰';
                    const n = Math.abs(parseInt(num, 10) || 0);
                    const formatted = n.toLocaleString('en-US');
                    return this.formatFa(formatted);
                },

                formatSignedNumber(num) {
                    if (num === null || num === undefined || num === '') return '۰';
                    const val = parseInt(num, 10) || 0;
                    const absFormatted = Math.abs(val).toLocaleString('en-US');
                    const faNum = this.formatFa(absFormatted);
                    return val < 0 ? '-' + faNum : faNum;
                },

                formatRawNumber(num) {
                    if (num === null || num === undefined || num === '') return '';
                    let clean = this.cleanNumber(num);
                    return clean > 0 ? clean.toLocaleString('en-US') : '';
                },

                formatAmountInput(el) {
                    let val = el.value.replace(/[^0-9]/g, '');
                    this.amount = val ? parseInt(val, 10).toLocaleString('en-US') : '';
                    el.value = this.amount;
                },

                get selectedFundAccountsSummary() {
                    const list = [];

                    if (this.paymentMethod === 'cash' && this.fundAccountId) {
                        const fundInfo = this.fundAccounts.find(f => String(f.id) === String(this.fundAccountId));
                        if (fundInfo) {
                            const initialBal = parseFloat(fundInfo.balance_val ?? fundInfo.current_balance ?? 0);
                            const debit = this.cleanAmount;
                            const newBal = initialBal + debit;

                            list.push({
                                id: 'fund-' + fundInfo.id,
                                name: fundInfo.name + (fundInfo.bank_name ? ' (' + fundInfo.bank_name + ')' : ''),
                                sub_title: fundInfo.account_number ? 'شماره حساب: ' + this.formatFa(fundInfo.account_number) : '',
                                type_label: fundInfo.type_label,
                                type_badge: fundInfo.type_badge,
                                initialBalance: initialBal,
                                docDebit: debit,
                                newBalance: newBal,
                                isNegative: newBal < 0,
                                is_cheque: false,
                            });
                        }
                    } else if (this.paymentMethod === 'cheque' && this.selectedChequeId) {
                        const cheque = this.receivableCheques.find(c => String(c.id) === String(this.selectedChequeId));
                        if (cheque) {
                            const chAmount = parseFloat(cheque.amount) || 0;
                            list.push({
                                id: 'cheque-' + cheque.id,
                                name: `چک صیادی شماره ${cheque.cheque_number ? this.formatFa(cheque.cheque_number) : '—'}`,
                                sub_title: (cheque.bank_name ? cheque.bank_name + ' | ' : '') + `سررسید: ${cheque.due_date_jalali || '—'}` + (cheque.payee_name || cheque.drawer_name ? ` | ${cheque.drawer_name ? 'صادرکننده: ' + cheque.drawer_name : 'گیرنده: ' + cheque.payee_name}` : ''),
                                type_label: cheque.type_label || 'چک دریافتی',
                                type_badge: cheque.type_badge || 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200',
                                initialBalance: chAmount,
                                docDebit: chAmount,
                                newBalance: chAmount,
                                isNegative: false,
                                is_cheque: true,
                                cheque_status_label: cheque.status_label || 'در جریان',
                                cheque_status_badge: cheque.status_badge || 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300 border border-amber-200 dark:border-amber-700/60',
                            });
                        }
                    }

                    return list;
                },

                init() {
                    this.amount = this.formatRawNumber(config.amount);
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
            }));
        });
    </script>
@endpush
@endsection
