@php use Modules\Accounting\App\Services\CurrencyService; @endphp
@extends('layouts.user')

@section('title', 'ویرایش سند حسابداری شماره ' . $document->document_number)

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-6 backdrop-blur-xl";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-sm dark:border-gray-700 dark:bg-gray-900/50 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";
    $selectClass = $inputClass . " cursor-pointer";
    $currencySuffix = $currencySuffix ?? CurrencyService::getBaseCurrency();

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
            'total_debit' => (float) ($cat->total_debit ?? 0),
            'total_credit' => (float) ($cat->total_credit ?? 0),
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
            'current_balance' => (float) $fa->current_balance,
        ];
    })->values()->all();

    $existingRows = $document->transactions->map(function($t, $i) {
        $debitVal = (float) CurrencyService::convertForDisplay($t->debit ?? 0);
        $creditVal = (float) CurrencyService::convertForDisplay($t->credit ?? 0);
        return [
            'id' => $i + 1,
            'category_id' => (string) $t->category_id,
            'fund_account_id' => (string) ($t->fund_account_id ?? ''),
            'description' => $t->description ?? '',
            'debit' => $debitVal,
            'credit' => $creditVal,
            'debit_formatted' => $debitVal ? number_format($debitVal) : '',
            'credit_formatted' => $creditVal ? number_format($creditVal) : '',
        ];
    })->values()->all();
@endphp

@section('content')
@includeIf('partials.jalali-date-picker')

<form action="{{ route('admin.accounting.documents.update', $document) }}" method="POST" x-data="multiLineDocumentForm()" @submit.prevent="submitForm">
    @csrf
    @method('PUT')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-24">

        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                    <span
                        class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 text-white shadow-lg shadow-amber-500/30 shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </span>
                    ویرایش سند حسابداری شماره {{ $document->document_number }}
                </h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">ویرایش ردیف‌های سند و کنترل لحظه‌ای تراز بدهکار
                    و بستانکار.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.accounting.documents.show', $document) }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-bold transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    مشاهده سند
                </a>
                <a href="{{ route('admin.accounting.documents.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-bold transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    دفتر اسناد
                </a>
            </div>
        </div>

        {{-- Errors Banner --}}
        @if ($errors->any())
            <div
                class="p-5 text-sm text-red-800 rounded-2xl bg-red-50 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20 flex items-start gap-4 shadow-sm">
                <span class="bg-red-100 text-red-600 dark:bg-red-500/20 p-2 rounded-full shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
                <div>
                    <p class="font-black text-base mb-1.5">خطاهایی در ثبت اطلاعات رخ داده است:</p>
                    <ul class="list-disc ps-5 space-y-1.5 marker:text-red-400">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Document Header Info Card --}}
        <div class="{{ $cardClass }}">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="document_date" class="{{ $labelClass }}">تاریخ سند <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input type="text"
                               name="document_date"
                               id="document_date"
                               value="{{ old('document_date', jdate($document->document_date)->format('Y/m/d')) }}"
                               class="{{ $inputClass }} dir-ltr text-center font-bold"
                               data-jdp
                               data-jdp-only-date
                               required
                               placeholder="1403/01/01">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </span>
                    </div>
                    @error('document_date')<p class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="document_description" class="{{ $labelClass }}">شرح کلی سند <span class="text-rose-500">*</span></label>
                    <input type="text"
                           name="document_description"
                           id="document_description"
                           value="{{ old('document_description', $document->description) }}"
                           class="{{ $inputClass }}"
                           required
                           placeholder="مثال: سند اصلاحی یا ویرایش هزینه‌ها...">
                    @error('document_description')<p
                        class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Transaction Rows Card --}}
        <div
            class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl relative z-30 overflow-visible">
            <div
                class="p-6 border-b border-gray-100 dark:border-gray-700/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-amber-500 shadow-sm shadow-amber-500/50"></span>
                        ردیف‌های بدهکار و بستانکار سند
                    </h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">حداقل ۲ ردیف جهت ثبت دوطرفه سند حسابداری
                        لازم است.</p>
                </div>
                <button type="button"
                        @click="addRow()"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 dark:hover:bg-indigo-500/20 text-xs font-bold transition-all active:scale-95 shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    افزودن ردیف جدید
                </button>
            </div>

            <div class="overflow-x-visible md:overflow-visible">
                <table
                    class="min-w-full text-sm text-start divide-y divide-gray-100 dark:divide-gray-700/50 overflow-visible">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-4 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center w-12">
                            #
                        </th>
                        <th class="px-4 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start min-w-[260px]">
                            سرفصل حساب <span class="text-rose-500">*</span></th>
                        <th class="px-4 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start min-w-[220px]">
                            حساب خزانه (بانک / صندوق)
                        </th>
                        <th class="px-4 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start min-w-[200px]">
                            شرح ردیف
                        </th>
                        <th class="px-4 py-4 font-bold text-rose-600 dark:text-rose-400 text-xs uppercase tracking-wider text-start min-w-[160px]">
                            بدهکار ({{ $currencySuffix }})
                        </th>
                        <th class="px-4 py-4 font-bold text-emerald-600 dark:text-emerald-400 text-xs uppercase tracking-wider text-start min-w-[160px]">
                            بستانکار ({{ $currencySuffix }})
                        </th>
                        <th class="px-4 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-end w-24">
                            عملیات
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40 overflow-visible">
                    <template x-for="(row, index) in rows" :key="row.id">
                        <tr class="group hover:bg-gray-50/80 dark:hover:bg-gray-700/20 transition-colors duration-200 relative"
                            :style="'position: relative; z-index: ' + (40 - index)">
                            <td class="px-4 py-4 text-center text-gray-400 text-xs font-bold"
                                x-text="formatNumber(index + 1)"></td>

                            {{-- Searchable Category Dropdown --}}
                            <td class="px-4 py-4 relative" :style="'z-index: ' + (40 - index)">
                                <div x-data="{
                                        open: false,
                                        search: '',
                                        get filteredOptions() {
                                            if (!this.search.trim()) return categories;
                                            const q = this.search.toLowerCase();
                                            return categories.filter(o =>
                                                o.title.toLowerCase().includes(q) ||
                                                (o.account_code && String(o.account_code).toLowerCase().includes(q)) ||
                                                (o.type_label && o.type_label.toLowerCase().includes(q))
                                            );
                                        },
                                        select(opt) {
                                            row.category_id = opt ? String(opt.id) : '';
                                            this.open = false;
                                            this.search = '';
                                        },
                                        getSelectedTitle() {
                                            let found = categories.find(o => String(o.id) === String(row.category_id));
                                            if (!found) return 'انتخاب سرفصل...';
                                            return found.title + (found.type_label ? ' (' + found.type_label + ')' : '');
                                        }
                                    }" class="relative" :class="{ 'z-50': open }">
                                    <input type="hidden" :name="`rows[${index}][category_id]`"
                                           :value="row.category_id" required>

                                    <button type="button" @click="open = !open"
                                            class="{{ $inputClass }} flex items-center justify-between cursor-pointer w-full text-start py-2.5 px-3.5 text-xs sm:text-sm">
                                        <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                        <svg class="w-4 h-4 text-gray-400 shrink-0 ms-2" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         class="absolute z-100 top-full mt-1.5 start-0 w-full min-w-[280px] sm:min-w-[340px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="search"
                                                   placeholder="جستجو سرفصل، کد یا ماهیت..."
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                        </div>

                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                            <div @click="select(opt)"
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                 :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(row.category_id) === String(opt.id) }">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <span x-show="opt.account_code"
                                                          class="text-[10px] text-gray-400"
                                                          x-text="formatNumber(opt.account_code)"></span>
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
                            </td>

                            {{-- Searchable Fund Account Dropdown --}}
                            <td class="px-4 py-4 relative" :style="'z-index: ' + (40 - index)">
                                <div x-data="{
                                        open: false,
                                        search: '',
                                        get filteredOptions() {
                                            if (!this.search.trim()) return fundAccounts;
                                            const q = this.search.toLowerCase();
                                            return fundAccounts.filter(o =>
                                                o.name.toLowerCase().includes(q) ||
                                                (o.bank_name && o.bank_name.toLowerCase().includes(q)) ||
                                                (o.account_number && String(o.account_number).toLowerCase().includes(q)) ||
                                                (o.type_label && o.type_label.toLowerCase().includes(q))
                                            );
                                        },
                                        select(opt) {
                                            row.fund_account_id = opt ? String(opt.id) : '';
                                            this.open = false;
                                            this.search = '';
                                        },
                                        getSelectedTitle() {
                                            if (!row.fund_account_id) return 'بدون ارتباط خزانه (اختیاری)';
                                            let found = fundAccounts.find(o => String(o.id) === String(row.fund_account_id));
                                            if (!found) return 'انتخاب حساب خزانه...';
                                            return found.name + (found.bank_name ? ' (' + found.bank_name + ')' : '');
                                        }
                                    }" class="relative" :class="{ 'z-50': open }">
                                    <input type="hidden" :name="`rows[${index}][fund_account_id]`"
                                           :value="row.fund_account_id">

                                    <button type="button" @click="open = !open"
                                            class="{{ $inputClass }} flex items-center justify-between cursor-pointer w-full text-start py-2.5 px-3.5 text-xs sm:text-sm"
                                            :class="{ 'text-gray-400 dark:text-gray-500': !row.fund_account_id }">
                                        <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                        <div class="flex items-center gap-1 shrink-0 ms-2">
                                            <span x-show="row.fund_account_id"
                                                  @click.stop="row.fund_account_id = ''"
                                                  class="text-gray-400 hover:text-rose-500 p-0.5 rounded cursor-pointer transition-colors"
                                                  title="حذف ارتباط خزانه">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor"><path stroke-linecap="round"
                                                                                 stroke-linejoin="round"
                                                                                 stroke-width="2"
                                                                                 d="M6 18L18 6M6 6l12 12"/></svg>
                                            </span>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </button>

                                    <div x-show="open" @click.outside="open = false" x-cloak
                                         class="absolute z-100 top-full mt-1.5 start-0 w-full min-w-[280px] sm:min-w-[340px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                        <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                            <input type="text" x-model="search"
                                                   placeholder="جستجو نام حساب، بانک، شماره حساب..."
                                                   class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                        </div>

                                        <div @click="select(null)"
                                             class="px-3 py-2 text-xs rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 cursor-pointer transition-colors mb-1 border-b border-gray-100 dark:border-gray-700/50 flex items-center justify-between">
                                            <span>بدون ارتباط خزانه</span>
                                            <span class="text-[10px] text-gray-400">(اختیاری)</span>
                                        </div>

                                        <template x-for="opt in filteredOptions" :key="opt.id">
                                            <div @click="select(opt)"
                                                 class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                                 :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(row.fund_account_id) === String(opt.id) }">
                                                <div class="flex flex-col gap-0.5 truncate">
                                                    <div class="flex items-center gap-1.5 truncate font-medium">
                                                        <span x-text="opt.name"></span>
                                                        <span x-show="opt.bank_name" class="text-[10px] text-gray-400"
                                                              x-text="'(' + opt.bank_name + ')'"></span>
                                                    </div>
                                                    <span x-show="opt.account_number" class="text-[10px] text-gray-400"
                                                          x-text="'ش‌ح: ' + formatNumber(opt.account_number)"></span>
                                                </div>
                                                <div class="flex items-center gap-1.5 shrink-0">
                                                    <span
                                                        class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                                        :class="opt.type_badge"
                                                        x-text="opt.type_label"></span>
                                                </div>
                                            </div>
                                        </template>

                                        <div x-show="filteredOptions.length === 0"
                                             class="p-3 text-xs text-gray-400 text-center">
                                            هیچ حسابی پیدا نشد
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Description --}}
                            <td class="px-4 py-4">
                                <input type="text"
                                       :name="`rows[${index}][description]`"
                                       x-model="row.description"
                                       class="{{ $inputClass }} py-2.5 px-3.5 text-xs sm:text-sm"
                                       placeholder="شرح ردیف (اختیاری)">
                            </td>

                            {{-- Debit Amount --}}
                            <td class="px-4 py-4">
                                <div class="relative">
                                    <input type="text"
                                           :name="`rows[${index}][debit]`"
                                           x-model="row.debit_formatted"
                                           @input="formatDebit(index, $event.target.value)"
                                           class="{{ $inputClass }} font-bold text-rose-600 dark:text-rose-400 dir-ltr text-end py-2.5 px-3.5 text-xs sm:text-sm focus:border-rose-500 focus:ring-rose-500/20"
                                           placeholder="۰">
                                    <button type="button"
                                            x-show="!row.debit && totalCredit > totalDebit"
                                            @click="autoBalance(index)"
                                            class="absolute start-2 top-1/2 -translate-y-1/2 text-[10px] px-1.5 py-0.5 rounded bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 font-bold transition-all shadow-xs"
                                            title="تراز خودکار با مانده بستانکار">
                                        تراز
                                    </button>
                                </div>
                            </td>

                            {{-- Credit Amount --}}
                            <td class="px-4 py-4">
                                <div class="relative">
                                    <input type="text"
                                           :name="`rows[${index}][credit]`"
                                           x-model="row.credit_formatted"
                                           @input="formatCredit(index, $event.target.value)"
                                           class="{{ $inputClass }} font-bold text-emerald-600 dark:text-emerald-400 dir-ltr text-end py-2.5 px-3.5 text-xs sm:text-sm focus:border-emerald-500 focus:ring-emerald-500/20"
                                           placeholder="۰">
                                    <button type="button"
                                            x-show="!row.credit && totalDebit > totalCredit"
                                            @click="autoBalance(index)"
                                            class="absolute start-2 top-1/2 -translate-y-1/2 text-[10px] px-1.5 py-0.5 rounded bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 font-bold transition-all shadow-xs"
                                            title="تراز خودکار با مانده بدهکار">
                                        تراز
                                    </button>
                                </div>
                            </td>

                            {{-- Action --}}
                            <td class="px-4 py-4 text-end">
                                <button type="button"
                                        @click="removeRow(index)"
                                        :disabled="rows.length <= 2"
                                        class="p-2 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-all disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-gray-400">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                    </tbody>
                </table>
            </div>

            {{-- Table Footer Summary --}}
            <div
                class="p-6 bg-gray-50/80 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700/50 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400">وضعیت سند:</span>
                    <template x-if="isBalanced">
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M5 13l4 4L19 7"/></svg>
                            سند تراز است
                        </span>
                    </template>
                    <template x-if="!isBalanced">
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M6 18L18 6M6 6l12 12"/></svg>
                            نامتوازن (اختلاف: <span x-text="formatNumber(getUnbalancedDiff())"></span> {{ $currencySuffix }})
                        </span>
                    </template>
                </div>

                <div class="flex flex-wrap items-center gap-6 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">جمع بدهکار:</span>
                        <span class="font-black text-rose-600 dark:text-rose-400 text-base"
                              x-text="formatNumber(totalDebit)"></span>
                        <span class="text-xs text-gray-400">{{ $currencySuffix }}</span>
                    </div>

                    <div class="w-px h-6 bg-gray-200 dark:bg-gray-700 hidden md:block"></div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">جمع بستانکار:</span>
                        <span class="font-black text-emerald-600 dark:text-emerald-400 text-base"
                              x-text="formatNumber(totalCredit)"></span>
                        <span class="text-xs text-gray-400">{{ $currencySuffix }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Live Treasury Impact Preview --}}
        <div x-show="selectedFundAccountsSummary.length > 0"
             class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl relative z-10"
             x-transition>
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/50 flex items-center justify-between gap-4 bg-gray-50/50 dark:bg-gray-900/30">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span>پیش‌نمایش تغییرات موجودی حساب‌های خزانه‌داری</span>
                            <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-500/20"
                                  x-text="`${formatNumber(selectedFundAccountsSummary.length)} حساب`"></span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">شبیه‌سازی زنده بر اساس مقادیر وارد شده</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="fund in selectedFundAccountsSummary" :key="fund.id">
                        <div class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/60 space-y-3 shadow-sm">
                            <div class="flex items-center justify-between pb-2.5 border-b border-gray-100 dark:border-gray-700/50">
                                <span class="font-bold text-xs text-gray-900 dark:text-white truncate"
                                      x-text="fund.name"></span>
                                <span class="text-[10px] px-2 py-0.5 rounded-lg font-bold border"
                                      :class="fund.type_badge"
                                      x-text="fund.type_label"></span>
                            </div>

                            <div class="space-y-2 text-xs">
                                <div class="flex justify-between items-center p-2.5 rounded-xl bg-gray-50/80 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/50">
                                    <span class="text-gray-500 dark:text-gray-400">موجودی اولیه:</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200 tabular-nums"
                                          x-text="formatNumber(fund.initialBalance) + ' ' + '{{ $currencySuffix }}'"></span>
                                </div>
                                <div class="flex justify-between items-center p-2.5 rounded-xl border text-xs"
                                     :class="fund.netChange >= 0 ? 'bg-emerald-50/60 dark:bg-emerald-500/10 border-emerald-100 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 font-bold' : 'bg-rose-50/60 dark:bg-rose-500/10 border-rose-100 dark:border-rose-500/20 text-rose-700 dark:text-rose-400 font-bold'">
                                    <span>تغییر با این سند:</span>
                                    <span class="font-bold tabular-nums" dir="ltr"
                                          x-text="formatSignedNumber(fund.netChange) + ' ' + '{{ $currencySuffix }}'"></span>
                                </div>
                                <div
                                    class="flex justify-between items-center p-2.5 rounded-xl border font-bold"
                                    :class="fund.isNegative ? 'bg-rose-50/60 dark:bg-rose-500/10 border-rose-200/80 dark:border-rose-500/20 text-rose-700 dark:text-rose-400' : 'bg-emerald-50/60 dark:bg-emerald-500/10 border-emerald-200/80 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400'">
                                    <span>موجودی پس از ثبت:</span>
                                    <span class="tabular-nums"
                                          x-text="formatNumber(fund.newBalance) + ' ' + '{{ $currencySuffix }}'"></span>
                                </div>
                            </div>

                            <div x-show="fund.isNegative"
                                 class="mt-2 text-[11px] text-rose-600 dark:text-rose-400 font-bold flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>هشدار: موجودی حساب منفی خواهد شد!</span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Sticky Submit Bar --}}
        <div class="sticky bottom-4 z-10 max-w-screen-2xl mx-auto">
            <div
                class="flex justify-between items-center bg-white/90 dark:bg-gray-800/90 backdrop-blur-md p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
                <a href="{{ route('admin.accounting.documents.index') }}"
                   class="px-6 py-3 rounded-xl text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                    انصراف
                </a>
                <button type="submit"
                        :disabled="!isBalanced"
                        class="px-8 py-3.5 rounded-xl bg-amber-600 text-white font-bold text-sm shadow-lg shadow-amber-500/30 hover:bg-amber-700 transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>ذخیره تغییرات سند مالی</span>
                </button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('multiLineDocumentForm', () => {
                const rawOldRows = @json(old('rows', $existingRows));
                const categories = @json($categoriesList);
                const fundAccounts = @json($fundAccountsList);
                let initialRows = [];

                if (Array.isArray(rawOldRows) && rawOldRows.length >= 2) {
                    initialRows = rawOldRows.map((r, i) => {
                        let debitVal = parseInt(String(r.debit || 0).replace(/[^0-9]/g, ''), 10) || 0;
                        let creditVal = parseInt(String(r.credit || 0).replace(/[^0-9]/g, ''), 10) || 0;
                        return {
                            id: i + 1,
                            category_id: String(r.category_id || ''),
                            fund_account_id: String(r.fund_account_id || ''),
                            description: r.description || '',
                            debit: debitVal,
                            credit: creditVal,
                            debit_formatted: debitVal ? debitVal.toLocaleString('en-US') : '',
                            credit_formatted: creditVal ? creditVal.toLocaleString('en-US') : ''
                        };
                    });
                } else {
                    initialRows = [
                        {
                            id: 1,
                            category_id: '',
                            fund_account_id: '',
                            description: '',
                            debit: 0,
                            credit: 0,
                            debit_formatted: '',
                            credit_formatted: ''
                        },
                        {
                            id: 2,
                            category_id: '',
                            fund_account_id: '',
                            description: '',
                            debit: 0,
                            credit: 0,
                            debit_formatted: '',
                            credit_formatted: ''
                        }
                    ];
                }

                return {
                    categories: categories,
                    fundAccounts: fundAccounts,
                    rows: initialRows,
                    nextId: initialRows.length + 1,

                    addRow() {
                        this.rows.push({
                            id: this.nextId++,
                            category_id: '',
                            fund_account_id: '',
                            description: '',
                            debit: 0,
                            credit: 0,
                            debit_formatted: '',
                            credit_formatted: ''
                        });
                    },
                    removeRow(index) {
                        if (this.rows.length > 2) {
                            this.rows.splice(index, 1);
                        }
                    },
                    formatDebit(index, val) {
                        let num = parseInt(String(val || '').replace(/[^0-9]/g, ''), 10) || 0;
                        this.rows[index].debit = num;
                        this.rows[index].debit_formatted = num ? num.toLocaleString('en-US') : '';
                        if (num > 0) {
                            this.rows[index].credit = 0;
                            this.rows[index].credit_formatted = '';
                        }
                    },
                    formatCredit(index, val) {
                        let num = parseInt(String(val || '').replace(/[^0-9]/g, ''), 10) || 0;
                        this.rows[index].credit = num;
                        this.rows[index].credit_formatted = num ? num.toLocaleString('en-US') : '';
                        if (num > 0) {
                            this.rows[index].debit = 0;
                            this.rows[index].debit_formatted = '';
                        }
                    },
                    get totalDebit() {
                        return this.rows.reduce((sum, r) => sum + (parseInt(r.debit, 10) || 0), 0);
                    },
                    get totalCredit() {
                        return this.rows.reduce((sum, r) => sum + (parseInt(r.credit, 10) || 0), 0);
                    },
                    get isBalanced() {
                        return this.totalDebit > 0 && this.totalDebit === this.totalCredit;
                    },
                    getUnbalancedDiff() {
                        return Math.abs(this.totalDebit - this.totalCredit);
                    },
                    get selectedFundAccountsSummary() {
                        const fundMap = {};

                        this.rows.forEach(row => {
                            const fundId = String(row.fund_account_id || '').trim();
                            if (!fundId) return;

                            if (!fundMap[fundId]) {
                                const fundInfo = this.fundAccounts.find(f => String(f.id) === fundId);
                                if (!fundInfo) return;
                                fundMap[fundId] = {
                                    id: fundInfo.id,
                                    name: fundInfo.name,
                                    type: fundInfo.type,
                                    type_label: fundInfo.type_label,
                                    type_badge: fundInfo.type_badge,
                                    initialBalance: parseFloat(fundInfo.current_balance) || 0,
                                    docDebit: 0,
                                    docCredit: 0,
                                };
                            }

                            fundMap[fundId].docDebit += (parseInt(row.debit, 10) || 0);
                            fundMap[fundId].docCredit += (parseInt(row.credit, 10) || 0);
                        });

                        const list = Object.values(fundMap);
                        if (list.length === 0) return [];

                        return list.map(item => {
                            const newBalance = item.initialBalance + item.docDebit - item.docCredit;
                            const netChange = item.docDebit - item.docCredit;

                            return {
                                ...item,
                                newBalance,
                                netChange,
                                isNegative: newBalance < 0,
                            };
                        });
                    },
                    autoBalance(index) {
                        let diff = this.getUnbalancedDiff();
                        if (diff <= 0) return;

                        if (this.totalDebit < this.totalCredit) {
                            this.formatDebit(index, String(this.rows[index].debit + diff));
                        } else if (this.totalCredit < this.totalDebit) {
                            this.formatCredit(index, String(this.rows[index].credit + diff));
                        }
                    },
                    formatNumber(num) {
                        if (num === null || num === undefined || num === '') return '۰';
                        const n = Math.abs(parseInt(num, 10) || 0);
                        const formatted = n.toLocaleString('en-US');
                        const farsiDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                        return formatted.replace(/[0-9]/g, w => farsiDigits[+w]);
                    },
                    formatSignedNumber(num) {
                        if (num === null || num === undefined || num === '') return '۰';
                        const val = parseInt(num, 10) || 0;
                        const absFormatted = Math.abs(val).toLocaleString('en-US');
                        const farsiDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                        const faNum = absFormatted.replace(/[0-9]/g, w => farsiDigits[+w]);
                        return val < 0 ? '-' + faNum : faNum;
                    },
                    submitForm(e) {
                        if (!this.isBalanced) {
                            alert('سند مالی نامتوازن است! مجموع بدهکار باید دقیقاً با مجموع بستانکار برابر باشد.');
                            return;
                        }
                        e.target.submit();
                    }
                };
            });
        });
    </script>
@endpush
@endsection
