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

    $walletBankIds = $banks->filter(fn($b) => $b->isWalletAccount())->pluck('id')->map(fn($id) => (string)$id)->values()->toArray();

    $bankTypePriority = ['bank' => 1, 'cash' => 2, 'gateway' => 3, 'petty_cash' => 4];
    $banksList = $banks->sort(function($a, $b) use ($bankTypePriority) {
        $pA = $bankTypePriority[$a->type] ?? 99;
        $pB = $bankTypePriority[$b->type] ?? 99;
        if ($pA !== $pB) return $pA <=> $pB;
        return strcmp($a->name ?? '', $b->name ?? '');
    })->map(function($b) {
        $types = [
            'bank' => 'بانک',
            'cash' => 'صندوق',
            'gateway' => 'درگاه پرداخت',
            'petty_cash' => 'تنخواه',
        ];
        $typeBadges = [
            'bank' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800/50',
            'cash' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50',
            'gateway' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border-purple-200 dark:border-purple-800/50',
            'petty_cash' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200 dark:border-amber-800/50',
        ];
        return [
            'id' => (string) $b->id,
            'name' => $b->name,
            'bank_name' => $b->bank_name ?? '',
            'account_number' => $b->account_number ?? '',
            'type' => $b->type,
            'type_label' => $b->isWalletAccount() ? 'کیف پول' : ($types[$b->type] ?? ($b->type ?: 'خزانه')),
            'type_badge' => $b->isWalletAccount() ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/50' : ($typeBadges[$b->type] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'),
            'balance_val' => (float) ($b->balance_val ?? 0),
            'is_wallet' => $b->isWalletAccount(),
        ];
    })->values()->all();

    $customersList = $customers->map(function($c) {
        $valKey = (string) ($c->value_key ?? $c->id);
        $isUser = str_contains($valKey, 'User') || (isset($c->display_name) && str_contains($c->display_name, 'کاربر سیستم')) || (isset($c->email) && !isset($c->national_code));
        $name = (string) ($c->full_name ?: ($c->name ?: ($c->username ?: $c->email)));
        $nationalCode = (string) ($c->national_code ?? '');
        $wb = (float) ($c->wallet_balance ?? 0);
        return [
            'id' => $valKey,
            'name' => $name,
            'national_code' => $nationalCode,
            'username' => (string) ($c->username ?? ''),
            'email' => (string) ($c->email ?? ''),
            'wallet_balance' => $wb,
            'is_user' => $isUser,
            'type_priority' => $isUser ? 2 : 1,
            'type_label' => $isUser ? 'کاربر سیستم' : 'مشتری',
            'type_badge' => $isUser
                ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border-purple-200 dark:border-purple-800/50'
                : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/50',
        ];
    })->sort(function($a, $b) {
        if ($a['type_priority'] !== $b['type_priority']) {
            return $a['type_priority'] <=> $b['type_priority'];
        }
        return strcmp($a['name'], $b['name']);
    })->values()->all();
@endphp

@section('content')
@includeIf('partials.jalali-date-picker')

<form action="{{ route('admin.accounting.expenses.store') }}" method="POST" enctype="multipart/form-data" x-data="formHandlers()" @submit="if (!isTotalAllocatedBalanced) { $event.preventDefault(); return false; }">
    @csrf
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-24">
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
                    <div class="relative">
                        <input type="text" name="amount" id="amount" x-model="amount"
                               class="{{ $inputClass }} dir-ltr text-left font-bold tabular-nums pl-3 pr-14"
                               @input="formatAmountInput($el)" placeholder="0">
                        <div
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-xs text-gray-400 font-medium select-none">
                            {{ $currencySuffix }}
                        </div>
                    </div>
                    @error('amount')<p class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="document_date" class="{{ $labelClass }}">تاریخ پرداخت <span
                            class="text-rose-500">*</span></label>
                    <input type="text" name="document_date" id="document_date" data-jdp data-jdp-only-date
                           x-model="documentDate" class="{{ $inputClass }} dir-ltr text-center font-bold">
                    @error('document_date')<p class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $labelClass }}">سرفصل هزینه <span class="text-rose-500">*</span></label>
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
                            selectedCategoryId = opt ? String(opt.id) : '';
                            this.open = false;
                            this.search = '';
                        },
                        getSelectedTitle() {
                            let found = this.options.find(o => String(o.id) === String(selectedCategoryId));
                            if (!found) return 'انتخاب سرفصل...';
                            return found.title + (found.type_label ? ' (' + found.type_label + ')' : '');
                        },
                        formatFa(str) {
                            if (!str) return '';
                            const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                            return String(str).replace(/[0-9]/g, w => farsi[+w]);
                        }
                    }" class="relative" :class="{ 'z-50': open }">
                        <input type="hidden" name="category_id" :value="selectedCategoryId" required>

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
                                <input type="text" x-model="search" placeholder="جستجو سرفصل، کد یا ماهیت..."
                                       class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                            </div>

                            <template x-for="opt in filteredOptions" :key="opt.id">
                                <div @click="select(opt)"
                                     class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                     :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(selectedCategoryId) === String(opt.id) }">
                                    <div class="flex items-center gap-1.5 truncate">
                                        <span x-show="opt.account_code" class="text-[10px] text-gray-400"
                                              x-text="formatFa(opt.account_code)"></span>
                                        <span x-text="opt.title" class="truncate"></span>
                                    </div>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                          :class="opt.type_badge"
                                          x-text="opt.type_label"></span>
                                </div>
                            </template>

                            <div x-show="filteredOptions.length === 0" class="p-3 text-xs text-gray-400 text-center">
                                هیچ سرفصلی پیدا نشد
                            </div>
                        </div>
                    </div>
                    @error('category_id')<p class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                </div>

                <div x-show="isCategoryCustomer" x-cloak class="md:col-span-1">
                    <label class="{{ $labelClass }}">انتخاب مشتری <span class="text-rose-500">*</span></label>
                    <div x-data="{
                        open: false,
                        search: '',
                        options: @js($customersList),
                        get filteredOptions() {
                            if (!this.search.trim()) return this.options;
                            const q = this.search.toLowerCase();
                            return this.options.filter(o =>
                                (o.name && o.name.toLowerCase().includes(q)) ||
                                (o.national_code && String(o.national_code).toLowerCase().includes(q)) ||
                                (o.username && o.username.toLowerCase().includes(q)) ||
                                (o.email && o.email.toLowerCase().includes(q)) ||
                                (o.type_label && o.type_label.toLowerCase().includes(q))
                            );
                        },
                        select(opt) {
                            selectedCustomerId = opt ? String(opt.id) : '';
                            this.open = false;
                            this.search = '';
                        },
                        getSelectedTitle() {
                            let found = this.options.find(o => String(o.id) === String(selectedCustomerId));
                            if (!found) return 'انتخاب مشتری...';
                            return found.name + (found.national_code ? ' (' + this.formatFa(found.national_code) + ')' : '');
                        },
                        getSelectedCustomer() {
                            return this.options.find(o => String(o.id) === String(selectedCustomerId)) || null;
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
                        <input type="hidden" :name="isCategoryCustomer ? 'client_id' : ''" :value="selectedCustomerId">

                        <button type="button" @click="open = !open"
                                class="{{ $selectClass }} flex items-center justify-between cursor-pointer w-full text-start py-2.5 px-3.5 text-xs sm:text-sm">
                            <div class="flex items-center gap-2 truncate">
                                <span
                                    class="flex items-center justify-center w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 font-bold text-xs shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </span>
                                <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                <template x-if="getSelectedCustomer()">
                                    <span
                                        class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium hidden sm:inline"
                                        x-text="'(کیف پول: ' + formatNumber(getSelectedCustomer().wallet_balance) + ' {{ $currencySuffix }})'"></span>
                                </template>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" @click.outside="open = false" x-cloak
                             class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] sm:min-w-[360px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-72 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                            <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                <input type="text" x-model="search"
                                       placeholder="جستجو نام، کدملی، نام کاربری یا ایمیل..."
                                       class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                            </div>

                            <template x-for="opt in filteredOptions" :key="opt.id">
                                <div>
                                    <template
                                        x-if="!opt.is_user && (filteredOptions.indexOf(opt) === 0 || filteredOptions[filteredOptions.indexOf(opt) - 1].is_user)">
                                        <div
                                            class="px-2.5 py-1 text-[11px] font-bold text-indigo-700 dark:text-indigo-400 bg-indigo-50/60 dark:bg-indigo-950/40 rounded-lg my-1 flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                            <span>مشتریان</span>
                                        </div>
                                    </template>
                                    <template
                                        x-if="opt.is_user && (filteredOptions.indexOf(opt) === 0 || !filteredOptions[filteredOptions.indexOf(opt) - 1].is_user)">
                                        <div
                                            class="px-2.5 py-1 text-[11px] font-bold text-purple-700 dark:text-purple-400 bg-purple-50/60 dark:bg-purple-950/40 rounded-lg my-1 flex items-center gap-1.5"
                                            :class="{ 'mt-2': filteredOptions.indexOf(opt) > 0 }">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                            <span>کاربران سیستم</span>
                                        </div>
                                    </template>

                                    <div @click="select(opt)"
                                         class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                         :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(selectedCustomerId) === String(opt.id) }">
                                        <div class="flex flex-col gap-0.5 truncate">
                                            <div class="flex items-center gap-1.5 truncate font-medium">
                                                <span x-text="opt.name"></span>
                                                <span x-show="opt.national_code" class="text-[10px] text-gray-400"
                                                      x-text="'(' + formatFa(opt.national_code) + ')'"></span>
                                            </div>
                                            <div class="flex items-center gap-2 text-[10px]">
                                                <span x-show="opt.username" class="text-gray-400"
                                                      x-text="'کاربر: ' + opt.username"></span>
                                                <span class="text-emerald-600 dark:text-emerald-400 font-medium"
                                                      x-text="'موجودی کیف پول: ' + formatNumber(opt.wallet_balance) + ' {{ $currencySuffix }}'"></span>
                                            </div>
                                        </div>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                              :class="opt.type_badge"
                                              x-text="opt.type_label"></span>
                                    </div>
                                </div>
                            </template>

                            <div x-show="filteredOptions.length === 0"
                                 class="p-3 text-xs text-gray-400 text-center">
                                هیچ مشتری یا کاربری پیدا نشد
                            </div>
                        </div>
                    </div>
                    @error('client_id')<p class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                </div>

                <div
                    class="md:col-span-2 space-y-6 rounded-3xl bg-gray-50/80 dark:bg-gray-900/40 p-6 border border-gray-100 dark:border-gray-700/60">
                    <input type="hidden" name="payment_type" :value="selectedChequeIds.length > 0 ? 'cheque' : 'bank'">

                    {{-- Section Header --}}
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <h3 class="font-black text-gray-900 dark:text-white text-base flex items-center gap-2">
                                <span
                                    class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </span>
                                <span>روش‌ها و منابع پرداخت هزینه</span>
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">می‌توانید پرداخت را از طریق یک یا
                                چند حساب بانکی، چک‌های صیادی و یا ترکیب هر دو ثبت فرمایید.</p>
                        </div>
                    </div>

                    {{-- PART 1: Cheques Selection --}}
                    <div class="space-y-4 pt-2 border-t border-gray-200/80 dark:border-gray-700/60">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <h4 class="text-sm font-black text-gray-800 dark:text-gray-200">۱. پرداخت / خرج چک‌های
                                    صیادی (اختیاری)</h4>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">
                                    چک‌های آماده در کارتابل: <strong class="text-amber-600 dark:text-amber-400"
                                                                     x-text="receivableCheques.length"></strong>
                                </span>
                                <template x-if="selectedChequeIds.length > 0">
                                    <span
                                        class="text-xs text-emerald-600 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-1 rounded-xl border border-emerald-200 dark:border-emerald-800/40">
                                        <span x-text="selectedChequeIds.length"></span> چک انتخاب شده — مجموع: <strong
                                            class="tabular-nums"
                                            x-text="formatNumber(totalChequesAmount) + ' ' + currencySuffix"></strong>
                                    </span>
                                </template>
                            </div>
                        </div>

                        {{-- Hidden inputs for cheques --}}
                        <template x-for="id in selectedChequeIds" :key="id">
                            <div>
                                <input type="hidden" name="cheque_ids[]" :value="id">
                                <input type="hidden" :name="`cheques[${id}][id]`" :value="id">
                                <input type="hidden" :name="`cheques[${id}][fee]`"
                                       :value="cleanNumber(getChequeFee(id))">
                                <input type="hidden" :name="`cheques[${id}][fee_bank_id]`"
                                       :value="getChequeFeeBankId(id)">
                            </div>
                        </template>

                        <template x-if="receivableCheques.length === 0">
                            <div
                                class="p-4 text-center bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700">
                                <p class="text-xs font-bold text-gray-400">هیچ چک دریافتی یا پرداختی آماده‌ای در سیستم
                                    وجود ندارد.</p>
                            </div>
                        </template>
                        <div x-show="receivableCheques.length > 0"
                             class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 max-h-80 overflow-y-auto p-1">
                            <template x-for="cheque in receivableCheques" :key="cheque.id">
                                <div @click="toggleCheque(cheque)"
                                     :class="{
                                         'border-2 border-amber-500 dark:border-amber-500 bg-amber-50/60 dark:bg-amber-900/20 shadow-sm': isChequeSelected(cheque.id),
                                         'border border-amber-200 dark:border-amber-700/60 bg-white dark:bg-gray-800 hover:border-amber-400 dark:hover:border-amber-500': !isChequeSelected(cheque.id) && canSelectCheque(cheque),
                                         'opacity-40 bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-800 cursor-not-allowed': !isChequeSelected(cheque.id) && !canSelectCheque(cheque)
                                     }"
                                     class="p-4 rounded-2xl transition-all duration-200 cursor-pointer relative shadow-sm space-y-3 select-none outline-none focus:outline-none">

                                    <div
                                        class="flex items-center justify-between gap-2 pb-2 border-b border-gray-100 dark:border-gray-700/60">
                                        <div class="flex items-center gap-2 truncate">
                                            <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                                                  :class="cheque.type === 'payable' ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300'">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </span>
                                            <div class="truncate">
                                                <span
                                                    class="text-xs font-black text-gray-900 dark:text-white tabular-nums tracking-wide block truncate"
                                                    x-text="cheque.cheque_number"></span>
                                                <div class="flex items-center gap-1 text-[10px]">
                                                    <span class="font-bold"
                                                          :class="cheque.type === 'payable' ? 'text-indigo-600 dark:text-indigo-400' : 'text-amber-600 dark:text-amber-400'"
                                                          x-text="cheque.type === 'payable' ? 'چک پرداختی' : 'چک دریافتی'"></span>
                                                    <span x-show="cheque.bank_name" class="text-gray-400 truncate"
                                                          x-text="'(' + cheque.bank_name + ')'"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            class="w-5 h-5 rounded-md border-2 flex items-center justify-center shrink-0 transition-all"
                                            :class="isChequeSelected(cheque.id) ? 'border-amber-500 bg-amber-500 text-white shadow-sm' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800'">
                                            <svg x-show="isChequeSelected(cheque.id)" class="w-3.5 h-3.5" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between text-xs pt-0.5">
                                        <span class="text-[11px] text-gray-400">سررسید: <strong
                                                class="text-gray-700 dark:text-gray-300 tabular-nums"
                                                x-text="cheque.due_date_jalali || '—'"></strong></span>
                                        <span class="font-black text-amber-700 dark:text-amber-400 tabular-nums text-xs"
                                              x-text="formatNumber(cheque.amount) + ' ' + currencySuffix"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- 2. Selected Cheques Repeater Rows --}}
                        <div x-show="selectedChequeIds.length > 0" class="space-y-4 pt-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">ردیف‌های چک‌های انتخاب‌شده جهت پرداخت:</span>
                            </div>

                            <div class="space-y-4">
                                <template x-for="cheque in selectedChequesList" :key="cheque.id">
                                    <div
                                        class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-amber-200 dark:border-amber-700/60 space-y-4 shadow-sm relative">
                                        <div
                                            class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-700/60">
                                            <span
                                                class="text-xs font-black text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                <span
                                                    class="w-6 h-6 rounded-lg bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300 flex items-center justify-center text-xs font-bold"
                                                    x-text="formatFa(selectedChequesList.indexOf(cheque) + 1)"></span>
                                                <span>چک صیادی شماره <strong class="tabular-nums font-black"
                                                                             x-text="cheque.cheque_number"></strong></span>
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-md"
                                                      :class="cheque.type === 'payable' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300'"
                                                      x-text="cheque.type === 'payable' ? 'چک پرداختی' : 'چک دریافتی'"></span>
                                                <template x-if="cheque.bank_name">
                                                    <span class="text-[11px] text-gray-500 dark:text-gray-400"
                                                          x-text="'(' + cheque.bank_name + ')'"></span>
                                                </template>
                                                <span class="text-[11px] text-gray-400">سررسید: <strong
                                                        class="text-gray-700 dark:text-gray-300 tabular-nums"
                                                        x-text="cheque.due_date_jalali || '—'"></strong></span>
                                            </span>
                                            <button type="button" @click="toggleCheque(cheque)"
                                                    class="text-xs text-rose-500 hover:text-rose-700 dark:hover:text-rose-300 font-bold flex items-center gap-1 transition-colors cursor-pointer outline-none focus:outline-none">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                <span>حذف</span>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5">
                                            {{-- 1. Cheque Amount (md:col-span-4) --}}
                                            <div class="md:col-span-4">
                                                <div class="flex items-center justify-between h-7 mb-1.5">
                                                    <label class="{{ $labelClass }} text-xs mb-0">مبلغ چک (غیرقابل
                                                        ویرایش)</label>
                                                </div>
                                                <div
                                                    class="h-11 px-3.5 rounded-xl bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/50 flex items-center justify-between text-xs font-bold">
                                                    <span class="text-gray-500 dark:text-gray-400 font-medium">مبلغ صیادی:</span>
                                                    <span
                                                        class="font-black text-amber-700 dark:text-amber-300 tabular-nums text-sm"
                                                        x-text="formatNumber(cheque.amount) + ' ' + currencySuffix"></span>
                                                </div>
                                            </div>

                                            {{-- 2. Cheque Fee Input (md:col-span-4) --}}
                                            <div class="md:col-span-4">
                                                <div class="flex items-center justify-between h-7 mb-1.5">
                                                    <label class="{{ $labelClass }} text-xs mb-0">کارمزد ثبت / انتقال چک
                                                        (اختیاری)</label>
                                                </div>
                                                <div class="relative">
                                                    <input type="text"
                                                           :value="getChequeFee(cheque.id)"
                                                           @input="setChequeFee(cheque.id, $el.value); $el.value = getChequeFee(cheque.id)"
                                                           class="w-full h-11 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/50 text-gray-900 dark:text-white dir-ltr text-left font-bold tabular-nums pl-3 pr-14 text-xs sm:text-sm focus:outline-none focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 dark:focus:border-amber-500 dark:focus:ring-amber-500/20 transition-all shadow-sm"
                                                           placeholder="0">
                                                    <div
                                                        class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[11px] text-gray-400 font-medium select-none">
                                                        {{ $currencySuffix }}
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- 3. Cheque Fee Bank Selection (md:col-span-4) --}}
                                            <div class="md:col-span-4">
                                                <div class="flex items-center justify-between h-7 mb-1.5">
                                                    <label class="{{ $labelClass }} text-xs mb-0">حساب کسر کارمزد
                                                        چک</label>
                                                </div>
                                                <div x-data="{
                                                    open: false,
                                                    search: '',
                                                    options: banks,
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
                                                        setChequeFeeBankId(cheque.id, opt ? String(opt.id) : '');
                                                        this.open = false;
                                                        this.search = '';
                                                    },
                                                    getSelectedTitle() {
                                                        let fBankId = getChequeFeeBankId(cheque.id);
                                                        let found = this.options.find(o => String(o.id) === String(fBankId));
                                                        if (!found) return 'کسر از حساب پیش‌فرض...';
                                                        return found.name + (found.bank_name ? ' — ' + found.bank_name : '');
                                                    }
                                                }" class="relative" :class="{ 'z-50': open }">
                                                    <button type="button" @click="open = !open"
                                                            class="w-full h-11 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/50 text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start px-3.5 text-xs sm:text-sm focus:outline-none focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 dark:focus:border-amber-500 dark:focus:ring-amber-500/20 transition-all shadow-sm">
                                                        <span x-text="getSelectedTitle()"
                                                              class="truncate font-medium"></span>
                                                        <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2" fill="none"
                                                             viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                    </button>

                                                    <div x-show="open" @click.outside="open = false" x-cloak
                                                         class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[260px] sm:min-w-[320px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                                        <div
                                                            class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                                            <input type="text" x-model="search"
                                                                   placeholder="جستجو نام حساب، بانک..."
                                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none">
                                                        </div>

                                                        <div @click="select(null)"
                                                             class="px-3 py-2 text-xs rounded-lg transition-colors flex items-center justify-between gap-2 hover:bg-gray-100 dark:hover:bg-gray-700/50 cursor-pointer text-gray-500 font-medium">
                                                            <span>کسر از اولین حساب پرداخت</span>
                                                        </div>

                                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                                            <div @click="select(opt)"
                                                                 class="px-3 py-2 text-xs rounded-lg transition-colors flex items-center justify-between gap-2"
                                                                 :class="{
                                                                     'bg-amber-50/70 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300 font-bold': String(getChequeFeeBankId(cheque.id)) === String(opt.id),
                                                                     'hover:bg-amber-50 dark:hover:bg-amber-900/30 hover:text-amber-600 dark:hover:text-amber-300 cursor-pointer text-gray-700 dark:text-gray-200': true
                                                                 }">
                                                                <div class="flex flex-col gap-0.5 truncate">
                                                                    <div
                                                                        class="flex items-center gap-1.5 truncate font-medium">
                                                                        <span x-text="opt.name"></span>
                                                                        <span x-show="opt.bank_name"
                                                                              class="text-[10px] text-gray-400"
                                                                              x-text="'(' + opt.bank_name + ')'"></span>
                                                                    </div>
                                                                </div>
                                                                <span
                                                                    class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                                    :class="opt.type_badge"
                                                                    x-text="opt.type_label"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        @error('cheque_ids')<p class="mt-1 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                    </div>

                    {{-- PART 2: Bank Accounts Repeater --}}
                    <div class="space-y-4 pt-4 border-t border-gray-200/80 dark:border-gray-700/60">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                <h4 class="text-sm font-black text-gray-800 dark:text-gray-200">۲. پرداخت از حساب‌های
                                    خزانه‌داری (بانک / صندوق / کیف پول)</h4>
                            </div>
                            <button type="button" @click="addBankAccount()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 text-xs font-bold hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-colors shadow-sm cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>افزودن حساب پرداخت دیگر</span>
                            </button>
                        </div>

                        {{-- Bank Accounts Rows --}}
                        <div class="space-y-4">
                            <template x-for="acc in bankAccounts" :key="acc._uid">
                                <div
                                    class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 space-y-4 shadow-sm relative">
                                    <div
                                        class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-700/60">
                                        <span
                                            class="text-xs font-black text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                            <span
                                                class="w-6 h-6 rounded-lg bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xs font-bold"
                                                x-text="formatFa(bankAccounts.indexOf(acc) + 1)"></span>
                                            <span>حساب پرداخت شماره <span
                                                    x-text="formatFa(bankAccounts.indexOf(acc) + 1)"></span></span>
                                        </span>
                                        <template x-if="bankAccounts.length > 1">
                                            <button type="button" @click="removeBankAccount(acc)"
                                                    class="text-xs text-rose-500 hover:text-rose-700 dark:hover:text-rose-300 font-bold flex items-center gap-1 transition-colors cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                <span>حذف</span>
                                            </button>
                                        </template>
                                    </div>

                                    {{-- Hidden Inputs --}}
                                    <input type="hidden" :name="`bank_accounts[${bankAccounts.indexOf(acc)}][bank_id]`"
                                           :value="acc.bank_id">
                                    <input type="hidden" :name="`bank_accounts[${bankAccounts.indexOf(acc)}][amount]`"
                                           :value="cleanNumber(acc.amount)">
                                    <input type="hidden" :name="`bank_accounts[${bankAccounts.indexOf(acc)}][fee]`"
                                           :value="cleanNumber(acc.fee)">
                                    <input type="hidden"
                                           :name="`bank_accounts[${bankAccounts.indexOf(acc)}][client_id]`"
                                           :value="acc.client_id || selectedCustomerId">

                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5">
                                        {{-- 1. Select Treasury Account (md:col-span-5) --}}
                                        <div class="md:col-span-5">
                                            <div class="flex items-center justify-between h-7 mb-1.5">
                                                <label class="{{ $labelClass }} text-xs mb-0">انتخاب حساب خزانه‌داری
                                                    <span class="text-rose-500">*</span></label>
                                            </div>
                                            <div x-data="{
                                                open: false,
                                                search: '',
                                                options: banks,
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
                                                    acc.bank_id = opt ? String(opt.id) : '';
                                                    this.open = false;
                                                    this.search = '';
                                                },
                                                getSelectedTitle() {
                                                    let found = this.options.find(o => String(o.id) === String(acc.bank_id));
                                                    if (!found) return 'انتخاب حساب خزانه‌داری...';
                                                    return found.name + (found.bank_name ? ' — ' + found.bank_name : '');
                                                },
                                                isInsufficient(opt) {
                                                    if (opt.is_wallet) return false;
                                                    const req = cleanNumber(acc.amount) + cleanNumber(acc.fee);
                                                    return req > opt.balance_val;
                                                }
                                            }" class="relative" :class="{ 'z-50': open }">
                                                <button type="button" @click="open = !open"
                                                        class="w-full h-11 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/50 text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start px-3.5 text-xs sm:text-sm focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20 transition-all shadow-sm">
                                                    <span x-text="getSelectedTitle()"
                                                          class="truncate font-medium"></span>
                                                    <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2" fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>

                                                <div x-show="open" @click.outside="open = false" x-cloak
                                                     class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] sm:min-w-[340px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                                    <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                                        <input type="text" x-model="search"
                                                               placeholder="جستجو نام حساب، بانک..."
                                                               class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 outline-none">
                                                    </div>

                                                    <template x-for="opt in filteredOptions" :key="opt.id">
                                                        <div @click="select(opt)"
                                                             class="px-3 py-2 text-xs rounded-lg transition-colors flex items-center justify-between gap-2"
                                                             :class="{
                                                                 'bg-rose-50/70 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300 font-bold': String(acc.bank_id) === String(opt.id),
                                                                 'hover:bg-rose-50 dark:hover:bg-rose-900/30 hover:text-rose-600 dark:hover:text-rose-300 cursor-pointer text-gray-700 dark:text-gray-200': true
                                                             }">
                                                            <div class="flex flex-col gap-0.5 truncate">
                                                                <div
                                                                    class="flex items-center gap-1.5 truncate font-medium">
                                                                    <span x-text="opt.name"></span>
                                                                    <span x-show="opt.bank_name"
                                                                          class="text-[10px] text-gray-400"
                                                                          x-text="'(' + opt.bank_name + ')'"></span>
                                                                </div>
                                                                <div class="flex items-center gap-2 text-[10px]">
                                                                    <span x-show="opt.account_number"
                                                                          class="text-gray-400"
                                                                          x-text="'ش‌ح: ' + formatFa(opt.account_number)"></span>
                                                                    <template x-if="!opt.is_wallet">
                                                                        <span
                                                                            :class="opt.balance_val <= 0 ? 'text-rose-500 font-bold' : (isInsufficient(opt) ? 'text-amber-600 font-bold' : 'text-emerald-600 dark:text-emerald-400 font-medium')"
                                                                            x-text="'موجودی: ' + formatNumber(opt.balance_val) + ' ' + currencySuffix + (opt.balance_val <= 0 ? ' [عدم موجودی]' : '')"></span>
                                                                    </template>
                                                                </div>
                                                            </div>
                                                            <span
                                                                class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                                :class="opt.type_badge" x-text="opt.type_label"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- 2. Amount Input (md:col-span-4) with Auto-Balance Button --}}
                                        <div class="md:col-span-4">
                                            <div class="flex items-center justify-between h-7 mb-1.5">
                                                <label class="{{ $labelClass }} text-xs mb-0">مبلغ پرداختی <span
                                                        class="text-rose-500">*</span></label>
                                                <button type="button"
                                                        @click="autoBalance(acc)"
                                                        title="تراز خودکار این ردیف"
                                                        x-show="!isTotalAllocatedBalanced && allocationDiff > 0"
                                                        class="h-6 px-2.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 dark:hover:bg-indigo-500/20 text-[11px] font-bold inline-flex items-center transition-all">
                                                    تراز خودکار
                                                </button>
                                            </div>
                                            <div class="relative">
                                                <input type="text" x-model="acc.amount"
                                                       @input="formatRowAmount(acc, $el)"
                                                       class="w-full h-11 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/50 text-gray-900 dark:text-white dir-ltr text-left font-bold tabular-nums pl-3 pr-14 text-xs sm:text-sm focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20 transition-all shadow-sm"
                                                       placeholder="0">
                                                <div
                                                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[11px] text-gray-400 font-medium select-none">
                                                    {{ $currencySuffix }}
                                                </div>
                                            </div>
                                        </div>

                                        {{-- 3. Fee Input (md:col-span-3) --}}
                                        <div class="md:col-span-3">
                                            <div class="flex items-center justify-between h-7 mb-1.5">
                                                <label class="{{ $labelClass }} text-xs mb-0">کارمزد بانکی
                                                    (اختیاری)</label>
                                            </div>
                                            <div class="relative">
                                                <input type="text" x-model="acc.fee" @input="formatRowFee(acc, $el)"
                                                       class="w-full h-11 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/50 text-gray-900 dark:text-white dir-ltr text-left font-bold tabular-nums pl-3 pr-14 text-xs sm:text-sm focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20 transition-all shadow-sm"
                                                       placeholder="0">
                                                <div
                                                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[11px] text-gray-400 font-medium select-none">
                                                    {{ $currencySuffix }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Customer Selection if Wallet Account --}}
                                    <div x-show="!isCategoryCustomer && isWalletAccount(acc.bank_id)" x-cloak
                                         class="p-3.5 rounded-xl bg-indigo-50/90 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700/60 space-y-2">
                                        <label class="{{ $labelClass }} text-xs">انتخاب مشتری جهت کسر از کیف پول <span
                                                class="text-rose-500">*</span></label>
                                        <div x-data="{
                                            open: false,
                                            search: '',
                                            options: customers,
                                            get filteredOptions() {
                                                if (!this.search.trim()) return this.options;
                                                const q = this.search.toLowerCase();
                                                return this.options.filter(o =>
                                                    (o.name && o.name.toLowerCase().includes(q)) ||
                                                    (o.company && o.company.toLowerCase().includes(q)) ||
                                                    (o.mobile && o.mobile.includes(q))
                                                );
                                            },
                                            select(c) {
                                                acc.client_id = c ? String(c.id) : '';
                                                this.open = false;
                                                this.search = '';
                                            },
                                            getSelectedCustomerTitle() {
                                                let targetId = acc.client_id || selectedCustomerId;
                                                let found = this.options.find(c => String(c.id) === String(targetId));
                                                if (!found) return 'انتخاب مشتری...';
                                                return found.name + (found.company ? ' (' + found.company + ')' : '');
                                            }
                                        }" class="relative">
                                            <button type="button" @click="open = !open"
                                                    class="w-full h-11 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/50 text-gray-900 dark:text-white flex items-center justify-between cursor-pointer text-start px-3.5 text-xs focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20 transition-all shadow-sm">
                                                <span x-text="getSelectedCustomerTitle()"
                                                      class="truncate font-medium"></span>
                                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>

                                            <div x-show="open" @click.outside="open = false" x-cloak
                                                 class="absolute z-50 top-full mt-1.5 start-0 w-full min-w-[260px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-56 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                                <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                                    <input type="text" x-model="search"
                                                           placeholder="جستجو نام مشتری، شرکت، موبایل..."
                                                           class="w-full text-xs p-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                                </div>
                                                <template x-for="c in filteredOptions" :key="c.id">
                                                    <div @click="select(c)"
                                                         class="px-2.5 py-1.5 text-xs rounded-lg transition-colors flex items-center justify-between"
                                                         :class="{
                                                             'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(acc.client_id || selectedCustomerId) === String(c.id),
                                                             'hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:text-indigo-600 cursor-pointer text-gray-700 dark:text-gray-200': true
                                                         }">
                                                        <div>
                                                            <span x-text="c.name" class="font-medium"></span>
                                                            <span x-show="c.company"
                                                                  class="text-[10px] text-gray-400 ms-1"
                                                                  x-text="'(' + c.company + ')'"></span>
                                                        </div>
                                                        <span x-show="c.mobile"
                                                              class="text-[10px] text-gray-400 font-mono"
                                                              x-text="c.mobile"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        @error('bank_accounts')<p
                            class="mt-1 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                    </div>

                    {{-- Overall Allocation Status Banner --}}
                    <div
                        class="p-4 rounded-2xl border text-xs flex flex-col md:flex-row md:items-center justify-between gap-3 font-bold transition-all duration-200"
                        :class="isTotalAllocatedBalanced ? 'bg-emerald-50/80 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-300' : 'bg-amber-50/80 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800/50 text-amber-800 dark:text-amber-300'">
                        <div class="flex items-center gap-2.5">
                            <template x-if="isTotalAllocatedBalanced">
                                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </template>
                            <template x-if="!isTotalAllocatedBalanced">
                                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </template>
                            <span class="text-xs sm:text-sm" x-text="allocationStatusText"></span>
                        </div>
                        <div class="flex items-center gap-3.5 flex-wrap text-xs">
                            <span>مبلغ سند: <strong class="tabular-nums"
                                                    x-text="formatNumber(cleanAmount) + ' ' + currencySuffix"></strong></span>
                            <template x-if="totalChequesAmount > 0">
                                <span class="text-amber-700 dark:text-amber-300">سهم چک‌ها: <strong class="tabular-nums"
                                                                                                    x-text="formatNumber(totalChequesAmount) + ' ' + currencySuffix"></strong></span>
                            </template>
                            <span class="text-rose-700 dark:text-rose-300">سهم حساب‌ها: <strong class="tabular-nums"
                                                                                                x-text="formatNumber(totalBankAccountsAmount) + ' ' + currencySuffix"></strong></span>
                            <template x-if="totalCombinedFees > 0">
                                <span class="text-indigo-600 dark:text-indigo-400">جمع کارمزدها: <strong
                                        class="tabular-nums"
                                        x-text="formatNumber(totalCombinedFees) + ' ' + currencySuffix"></strong></span>
                            </template>
                        </div>
                    </div>

                    {{-- Live Treasury & Payment Sources Balance Card (دقیقاً زیر مبالغ پرداختی و وضعیت تراز) --}}
                    <div x-show="selectedFundAccountsSummary.length > 0"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform -translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         class="bg-white dark:bg-gray-800/60 rounded-3xl border border-indigo-100 dark:border-indigo-900/40 shadow-sm overflow-hidden backdrop-blur-xl relative z-20">
                        <div
                            class="p-5 border-b border-indigo-50 dark:border-indigo-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-blue-50/60 via-indigo-50/40 to-transparent dark:from-blue-950/30 dark:via-indigo-950/20 dark:to-transparent">
                            <div class="flex items-center gap-3">
                                <span
                                    class="flex items-center justify-center w-10 h-10 rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-500/20 shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                </span>
                                <div>
                                    <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                                        <span>موجودی لحظه‌ای حساب‌های خزانه‌داری / پرداخت انتخابی</span>
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300"
                                            x-text="`${formatNumber(selectedFundAccountsSummary.length)} منبع پرداخت`"></span>
                                    </h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">نمایش زنده موجودی فعلی،
                                        تفکیک مبالغ کسر در این سند و موجودی نهایی پس از ثبت هزینه</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <template x-for="item in selectedFundAccountsSummary" :key="item.id">
                                    <div
                                        class="rounded-2xl border border-gray-200/80 dark:border-gray-700/70 bg-gradient-to-b from-gray-50/60 to-white dark:from-gray-800/40 dark:to-gray-800/80 p-4 space-y-3.5 shadow-sm hover:shadow-md transition-all">
                                        <div
                                            class="flex items-start justify-between gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-3">
                                            <div class="space-y-1 truncate">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <span class="w-2 h-2 rounded-full"
                                                          :class="item.is_cheque ? 'bg-amber-500' : (item.is_customer_wallet ? 'bg-purple-500' : 'bg-indigo-500')"></span>
                                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white truncate"
                                                        x-text="item.name"></h3>
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
                                                class="flex items-center justify-between bg-white dark:bg-gray-900/40 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700/40">
                                                <span class="text-gray-500 dark:text-gray-400 font-medium">
                                                    <template
                                                        x-if="item.is_cheque"><span>مبلغ صیادی چک:</span></template>
                                                    <template
                                                        x-if="!item.is_cheque"><span>موجودی فعلی حساب:</span></template>
                                                </span>
                                                <div class="flex items-center gap-1.5">
                                                    <span
                                                        class="font-bold text-gray-800 dark:text-gray-200 tabular-nums"
                                                        x-text="formatNumber(item.initialBalance)"></span>
                                                    <span class="text-[10px] text-gray-400">{{ $currencySuffix }}</span>
                                                </div>
                                            </div>

                                            <div
                                                class="bg-rose-50/60 dark:bg-rose-900/20 p-2.5 rounded-xl border border-rose-100 dark:border-rose-900/30 space-y-1.5">
                                                <div class="flex items-center justify-between text-[11px]">
                                                    <span class="text-rose-700 dark:text-rose-300 font-bold">
                                                        <template
                                                            x-if="item.is_cheque"><span>پوشش هزینه با این چک:</span></template>
                                                        <template
                                                            x-if="!item.is_cheque"><span>مبلغ کسر در این سند:</span></template>
                                                    </span>
                                                    <span
                                                        class="font-black text-rose-700 dark:text-rose-300 tabular-nums"
                                                        x-text="item.docCredit > 0 ? '-' + formatNumber(item.docCredit) + ' {{ $currencySuffix }}' : '۰'"></span>
                                                </div>
                                                <template x-if="item.fee_portion > 0">
                                                    <div
                                                        class="pt-1.5 border-t border-rose-200/60 dark:border-rose-800/40 flex items-center justify-between text-[10px] text-gray-600 dark:text-gray-300">
                                                        <span>
                                                            <span>کارمزد بانکی / انتقال:</span>
                                                            <template x-if="item.fee_bank_name">
                                                                <span class="text-gray-400 ms-1"
                                                                      x-text="'(از ' + item.fee_bank_name + ')'"></span>
                                                            </template>
                                                        </span>
                                                        <span
                                                            class="font-bold text-rose-600 dark:text-rose-400 tabular-nums"
                                                            x-text="formatNumber(item.fee_portion) + ' {{ $currencySuffix }}'"></span>
                                                    </div>
                                                </template>
                                            </div>

                                            <template x-if="!item.is_cheque">
                                                <div
                                                    class="flex items-center justify-between p-2.5 rounded-xl border font-bold"
                                                    :class="item.newBalance < 0
                                                            ? 'bg-rose-50/80 dark:bg-rose-900/30 border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300'
                                                            : 'bg-emerald-50/60 dark:bg-emerald-950/20 border-emerald-100 dark:border-emerald-900/30 text-emerald-800 dark:text-emerald-300'">
                                                    <span>موجودی پس از کسر:</span>
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="text-sm font-black tabular-nums"
                                                              x-text="formatSignedNumber(item.newBalance)"></span>
                                                        <span
                                                            class="text-[10px] opacity-75">{{ $currencySuffix }}</span>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="item.is_cheque">
                                                <div
                                                    class="flex items-center justify-between p-2.5 rounded-xl border font-bold bg-amber-50/60 dark:bg-amber-950/20 border-amber-100 dark:border-amber-900/30 text-amber-800 dark:text-amber-300">
                                                    <span>وضعیت چک صیادی:</span>
                                                    <span
                                                        class="px-2.5 py-0.5 text-[11px] font-bold rounded-lg border shadow-sm"
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
                </div>

                <div class="md:col-span-2 p-4">
                    <label for="reference_number" class="{{ $labelClass }}">شماره پیگیری / فیش (اختیاری)</label>
                    <input type="text" name="reference_number" id="reference_number" x-model="referenceNumber"
                           class="{{ $inputClass }} dir-ltr text-left">
                    @error('reference_number')<p
                        class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2 p-4">
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

        {{-- Sticky Submit Bar --}}
        <div class="sticky bottom-4 z-10 max-w-screen-2xl mx-auto">
            <div
                class="flex justify-between items-center bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xl">
                <a href="{{ route('admin.accounting.expenses.index') }}"
                   class="px-6 py-3 rounded-xl text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                    انصراف
                </a>

                <div class="flex items-center gap-3">
                    <template x-if="!isTotalAllocatedBalanced">
                        <span
                            class="text-xs font-bold text-amber-700 dark:text-amber-300 hidden sm:inline-flex items-center gap-1.5 bg-amber-50 dark:bg-amber-900/30 px-3 py-1.5 rounded-xl border border-amber-200/80 dark:border-amber-800/40">
                            <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span
                                x-text="cleanAmount === 0 ? 'مبلغ کل هزینه را وارد کنید' : (allocationDiff > 0 ? `مانده تخصیص‌نیافته: ${formatNumber(allocationDiff)} ${currencySuffix}` : `مبلغ مازاد: ${formatNumber(Math.abs(allocationDiff))} ${currencySuffix}`)"></span>
                        </span>
                    </template>
                    <button type="submit"
                            :disabled="!isTotalAllocatedBalanced"
                            :class="!isTotalAllocatedBalanced ? 'opacity-50 cursor-not-allowed shadow-none' : 'shadow-lg shadow-rose-500/30 hover:bg-rose-700 active:scale-95 cursor-pointer'"
                            :title="!isTotalAllocatedBalanced ? 'جهت ثبت هزینه، مجموع مبالغ پرداختی باید دقیقاً با مبلغ کل هزینه برابر و تراز باشد' : 'ثبت و ذخیره هزینه'"
                            class="px-8 py-3.5 rounded-xl bg-rose-600 text-white font-bold text-sm transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>ثبت و ذخیره هزینه</span>
                    </button>
                </div>
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
                selectedCategoryId: '{{ old('category_id', '') }}',
                selectedCustomerId: '{{ old('client_id', '') }}',
                selectedChequeIds: @json(old('cheque_ids', [])),
                chequesFeesMap: @json(old('cheques', [])),
                bankAccounts: [],
                currencySuffix: '{{ $currencySuffix }}',
                categories: @json($mappedCategories),
                banks: @json($banksList),
                customers: @json($customersList),
                walletBankIds: @json($walletBankIds),
                receivableCheques: @json($receivableCheques),

                cleanNumber(val) {
                    if (val === null || val === undefined) return 0;
                    let clean = String(val).replace(/[^0-9]/g, '');
                    return clean ? parseInt(clean, 10) : 0;
                },

                get cleanAmount() {
                    return this.cleanNumber(this.amount);
                },

                get totalChequesAmount() {
                    return this.receivableCheques
                        .filter(c => this.selectedChequeIds.map(String).includes(String(c.id)))
                        .reduce((sum, c) => sum + (parseFloat(c.amount) || 0), 0);
                },

                get selectedChequesList() {
                    return this.receivableCheques.filter(c => this.selectedChequeIds.map(String).includes(String(c.id)));
                },

                get totalBankAccountsAmount() {
                    return this.bankAccounts.reduce((sum, acc) => sum + this.cleanNumber(acc.amount), 0);
                },

                get totalPaid() {
                    return this.totalChequesAmount + this.totalBankAccountsAmount;
                },

                get remainingAmount() {
                    return Math.max(0, this.cleanAmount - this.totalChequesAmount);
                },

                get totalBankAccountsFee() {
                    return this.bankAccounts.reduce((sum, acc) => sum + this.cleanNumber(acc.fee), 0);
                },

                get totalChequeFees() {
                    let sum = 0;
                    for (let id of this.selectedChequeIds) {
                        sum += this.cleanNumber(this.getChequeFee(id));
                    }
                    return sum;
                },

                get totalCombinedFees() {
                    return this.totalBankAccountsFee + this.totalChequeFees;
                },

                get totalPayable() {
                    return this.cleanAmount + this.totalCombinedFees;
                },

                get isTotalAllocatedBalanced() {
                    return this.cleanAmount > 0 && this.totalPaid === this.cleanAmount;
                },

                get allocationDiff() {
                    return this.cleanAmount - this.totalPaid;
                },

                get allocationStatusText() {
                    if (this.cleanAmount === 0) {
                        return 'لطفاً ابتدا مبلغ کل هزینه را وارد فرمایید.';
                    }
                    if (this.isTotalAllocatedBalanced) {
                        return 'مبالغ پرداختی (چک‌ها + حساب‌ها) دقیقاً با مبلغ هزینه تراز است.';
                    }
                    if (this.allocationDiff > 0) {
                        return `مانده تخصیص‌نیافته: ${this.formatNumber(this.allocationDiff)} ${this.currencySuffix} (لطفاً مبالغ حساب‌ها یا چک‌ها را تکمیل فرمایید)`;
                    }
                    return `مجموع مبالغ پرداختی ${this.formatNumber(Math.abs(this.allocationDiff))} ${this.currencySuffix} بیشتر از مبلغ هزینه است!`;
                },

                isWalletAccount(bankId) {
                    return this.walletBankIds.map(String).includes(String(bankId));
                },

                getChequeFee(chequeId) {
                    let entry = this.chequesFeesMap[chequeId] || this.chequesFeesMap[String(chequeId)];
                    if (typeof entry === 'object' && entry !== null) {
                        return entry.fee !== undefined && entry.fee !== null ? this.formatRawNumber(entry.fee) : '';
                    }
                    return entry ? this.formatRawNumber(entry) : '';
                },

                getChequeFeeBankId(chequeId) {
                    let entry = this.chequesFeesMap[chequeId] || this.chequesFeesMap[String(chequeId)];
                    if (typeof entry === 'object' && entry !== null) {
                        return entry.fee_bank_id !== undefined && entry.fee_bank_id !== null ? String(entry.fee_bank_id) : '';
                    }
                    return '';
                },

                setChequeFee(chequeId, val) {
                    let clean = this.cleanNumber(val);
                    let strVal = clean > 0 ? clean.toLocaleString('en-US') : '';
                    let current = this.chequesFeesMap[chequeId] || this.chequesFeesMap[String(chequeId)] || {};
                    let feeBankId = typeof current === 'object' && current !== null ? current.fee_bank_id : null;

                    let nextMap = Object.assign({}, this.chequesFeesMap);
                    nextMap[chequeId] = { fee: strVal, fee_bank_id: feeBankId };
                    nextMap[String(chequeId)] = { fee: strVal, fee_bank_id: feeBankId };
                    this.chequesFeesMap = nextMap;
                },

                setChequeFeeBankId(chequeId, bankId) {
                    let current = this.chequesFeesMap[chequeId] || this.chequesFeesMap[String(chequeId)] || {};
                    let feeVal = typeof current === 'object' && current !== null ? current.fee : '';

                    let nextMap = Object.assign({}, this.chequesFeesMap);
                    nextMap[chequeId] = { fee: feeVal, fee_bank_id: bankId ? String(bankId) : null };
                    nextMap[String(chequeId)] = { fee: feeVal, fee_bank_id: bankId ? String(bankId) : null };
                    this.chequesFeesMap = nextMap;
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

                addBankAccount() {
                    this.bankAccounts.push({
                        _uid: 'b_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
                        bank_id: '',
                        amount: '',
                        fee: '',
                        client_id: '',
                    });
                },

                removeBankAccount(target) {
                    let idx = typeof target === 'number' ? target : this.bankAccounts.indexOf(target);
                    if (idx !== -1 && this.bankAccounts.length > 1) {
                        this.bankAccounts.splice(idx, 1);
                    }
                },

                autoBalance(target) {
                    let idx = typeof target === 'number' ? target : this.bankAccounts.indexOf(target);
                    if (idx === -1 || this.allocationDiff <= 0) return;
                    let current = this.cleanNumber(this.bankAccounts[idx].amount);
                    let newAmt = current + this.allocationDiff;
                    this.bankAccounts[idx].amount = newAmt > 0 ? newAmt.toLocaleString('en-US') : '';
                },

                formatRowAmount(acc, el) {
                    let clean = this.cleanNumber(el.value);
                    acc.amount = clean > 0 ? clean.toLocaleString('en-US') : '';
                    el.value = acc.amount;
                },

                formatRowFee(acc, el) {
                    let clean = this.cleanNumber(el.value);
                    acc.fee = clean > 0 ? clean.toLocaleString('en-US') : '';
                    el.value = acc.fee;
                },

                formatRawNumber(num) {
                    if (num === null || num === undefined || num === '') return '';
                    let clean = this.cleanNumber(num);
                    return clean > 0 ? clean.toLocaleString('en-US') : '';
                },

                get selectedFundAccountsSummary() {
                    const list = [];

                    if (this.selectedChequeIds.length > 0) {
                        const selCheques = this.receivableCheques.filter(c => this.selectedChequeIds.map(String).includes(String(c.id)));
                        selCheques.forEach(cheque => {
                            let feeBankId = this.getChequeFeeBankId(cheque.id);
                            let feeBank = this.banks.find(b => String(b.id) === String(feeBankId));
                            let feeAmount = this.cleanNumber(this.getChequeFee(cheque.id));

                            list.push({
                                id: 'cheque-' + cheque.id,
                                name: `چک صیادی شماره ${cheque.cheque_number ? this.formatFa(cheque.cheque_number) : '—'}`,
                                sub_title: (cheque.bank_name ? cheque.bank_name + ' | ' : '') + `سررسید: ${cheque.due_date_jalali || '—'}` + (cheque.payee_name ? ` | گیرنده: ${cheque.payee_name}` : ''),
                                type_label: cheque.type_label || (cheque.type === 'payable' ? 'چک پرداختی' : 'چک دریافتی'),
                                type_badge: cheque.type === 'payable' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border-indigo-200' : 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200',
                                initialBalance: parseFloat(cheque.amount) || 0,
                                docCredit: parseFloat(cheque.amount) || 0,
                                newBalance: 0,
                                isNegative: false,
                                is_cheque: true,
                                cheque_status_label: cheque.status_label || 'در جریان',
                                cheque_status_badge: cheque.status_badge || 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300 border border-amber-200 dark:border-amber-700/60',
                                fee_portion: feeAmount,
                                fee_bank_name: feeBank ? feeBank.name : null,
                                principal_portion: parseFloat(cheque.amount) || 0,
                            });
                        });
                    }

                    // 2. Bank Accounts
                    this.bankAccounts.forEach((acc, idx) => {
                        if (!acc.bank_id) return;
                        const bankInfo = this.banks.find(b => String(b.id) === String(acc.bank_id));
                        if (!bankInfo) return;

                        const principal = this.cleanNumber(acc.amount);
                        const fee = this.cleanNumber(acc.fee);

                        let extraChequeFees = 0;
                        this.selectedChequeIds.forEach(cId => {
                            let assignedBankId = this.getChequeFeeBankId(cId);
                            // If specifically assigned to this bank, or if not assigned and this is the first bank
                            if (String(assignedBankId) === String(bankInfo.id) || (!assignedBankId && idx === 0)) {
                                extraChequeFees += this.cleanNumber(this.getChequeFee(cId));
                            }
                        });

                        const totalFee = fee + extraChequeFees;
                        const totalCredit = principal + totalFee;

                        if (totalCredit <= 0 && !bankInfo.is_wallet) return;

                        if (!bankInfo.is_wallet) {
                            const initialBal = parseFloat(bankInfo.balance_val) || 0;
                            const newBal = initialBal - totalCredit;
                            list.push({
                                id: 'bank-' + bankInfo.id + '-' + idx,
                                name: bankInfo.name + (bankInfo.bank_name ? ' (' + bankInfo.bank_name + ')' : ''),
                                sub_title: bankInfo.account_number ? 'شماره حساب: ' + this.formatFa(bankInfo.account_number) : '',
                                type_label: bankInfo.type_label,
                                type_badge: bankInfo.type_badge,
                                initialBalance: initialBal,
                                docCredit: totalCredit,
                                newBalance: newBal,
                                isNegative: newBal < 0 && totalCredit > 0,
                                is_bank: true,
                                fee_portion: totalFee,
                                principal_portion: principal,
                            });
                        } else {
                            const targetCustId = acc.client_id || this.selectedCustomerId;
                            if (targetCustId) {
                                const custInfo = this.customers.find(c => String(c.id) === String(targetCustId));
                                if (custInfo) {
                                    const custBal = parseFloat(custInfo.wallet_balance) || 0;
                                    const newCustBal = custBal - totalCredit;
                                    list.push({
                                        id: 'cust-' + custInfo.id + '-' + idx,
                                        name: custInfo.name + (custInfo.national_code ? ' (' + this.formatFa(custInfo.national_code) + ')' : ''),
                                        sub_title: (custInfo.is_user ? 'کاربر سیستم' : 'کیف پول مشتری') + ` [حساب: ${bankInfo.name}]`,
                                        type_label: custInfo.type_label,
                                        type_badge: custInfo.type_badge,
                                        initialBalance: custBal,
                                        docCredit: totalCredit,
                                        newBalance: newCustBal,
                                        isNegative: newCustBal < 0 && totalCredit > 0,
                                        is_customer_wallet: true,
                                        fee_portion: totalFee,
                                        principal_portion: principal,
                                    });
                                }
                            }
                        }
                    });

                    return list;
                },

                init() {
                    const oldBankAccs = @json(old('bank_accounts', []));
                    if (Array.isArray(oldBankAccs) && oldBankAccs.length > 0) {
                        this.bankAccounts = oldBankAccs.map((item, idx) => ({
                            _uid: 'b_' + Date.now() + '_' + idx,
                            bank_id: String(item.bank_id || ''),
                            amount: item.amount ? this.formatRawNumber(item.amount) : '',
                            fee: item.fee ? this.formatRawNumber(item.fee) : '',
                            client_id: item.client_id ? String(item.client_id) : '',
                        }));
                    } else {
                        const oldBankId = '{{ old('bank_id', '') }}';
                        this.bankAccounts = [
                            {_uid: 'b_' + Date.now(), bank_id: oldBankId, amount: '', fee: '', client_id: ''}
                        ];
                    }
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

                formatAmountInput(el) {
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
