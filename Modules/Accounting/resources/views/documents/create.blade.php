@php use Modules\Accounting\App\Services\CurrencyService; @endphp
@extends('layouts.user')

@section('title', 'صدور سند حسابداری دستی')

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-6 backdrop-blur-xl";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-sm dark:border-gray-700 dark:bg-gray-900/50 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";
    $selectClass = $inputClass . " cursor-pointer";
    $currencySuffix = $currencySuffix ?? CurrencyService::getBaseCurrency();
@endphp

@section('content')
@includeIf('partials.jalali-date-picker')

<form action="{{ route('admin.accounting.documents.store') }}" method="POST" x-data="multiLineDocumentForm()" @submit.prevent="submitForm">
    @csrf
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-24">

        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                    <span class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </span>
                    صدور سند حسابداری دستی
                </h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">ثبت اسناد مرکب چند ردیفه با کنترل آنلاین و هوشمند تراز بدهکار و بستانکار.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.accounting.documents.index') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-bold transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    بازگشت به دفتر اسناد
                </a>
            </div>
        </div>

        {{-- Errors Banner --}}
        @if ($errors->any())
            <div class="p-5 text-sm text-red-800 rounded-2xl bg-red-50 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20 flex items-start gap-4 shadow-sm">
                <span class="bg-red-100 text-red-600 dark:bg-red-500/20 p-2 rounded-full shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
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
                               value="{{ old('document_date', jdate()->format('Y/m/d')) }}"
                               class="{{ $inputClass }} dir-ltr text-center font-bold"
                               data-jdp
                               data-jdp-only-date
                               required
                               placeholder="1403/01/01">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </span>
                    </div>
                    @error('document_date')<p class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="document_description" class="{{ $labelClass }}">شرح کلی سند</label>
                    <input type="text"
                           name="document_description"
                           id="document_description"
                           value="{{ old('document_description') }}"
                           class="{{ $inputClass }}"
                           placeholder="مثال: سند ثبت هزینه‌ها و بستانکاری طرف حساب‌ها...">
                    @error('document_description')<p class="mt-2 text-sm text-rose-600 font-bold">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Transaction Rows Card --}}
        <div class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-indigo-500 shadow-sm shadow-indigo-500/50"></span>
                        ردیف‌های بدهکار و بستانکار سند
                    </h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">حداقل ۲ ردیف جهت ثبت دوطرفه سند حسابداری لازم است.</p>
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

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-4 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center w-12">#</th>
                            <th class="px-4 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start min-w-[220px]">سرفصل حساب <span class="text-rose-500">*</span></th>
                            <th class="px-4 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start min-w-[180px]">حساب خزانه (اختیاری)</th>
                            <th class="px-4 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start min-w-[200px]">شرح ردیف</th>
                            <th class="px-4 py-4 font-bold text-rose-600 dark:text-rose-400 text-xs uppercase tracking-wider text-start min-w-[160px]">بدهکار ({{ $currencySuffix }})</th>
                            <th class="px-4 py-4 font-bold text-emerald-600 dark:text-emerald-400 text-xs uppercase tracking-wider text-start min-w-[160px]">بستانکار ({{ $currencySuffix }})</th>
                            <th class="px-4 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-end w-24">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
                        <template x-for="(row, index) in rows" :key="row.id">
                            <tr class="group hover:bg-gray-50/80 dark:hover:bg-gray-700/20 transition-colors duration-200">
                                <td class="px-4 py-4 text-center text-gray-400 font-mono text-xs font-bold" x-text="index + 1"></td>
                                <td class="px-4 py-4">
                                    <select :name="`rows[${index}][category_id]`" x-model="row.category_id" class="{{ $selectClass }}" required>
                                        <option value="">انتخاب سرفصل...</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">
                                                {{ $category->title }} ({{ $category->type === 'income' ? 'درآمد' : ($category->type === 'expense' ? 'هزینه' : ($category->type === 'asset' ? 'دارایی' : ($category->type === 'liability' ? 'بدهی' : 'سرمایه'))) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-4">
                                    <select :name="`rows[${index}][fund_account_id]`" x-model="row.fund_account_id" class="{{ $selectClass }}">
                                        <option value="">انتخاب صندوق / بانک...</option>
                                        @foreach($fundAccounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-4">
                                    <input type="text" :name="`rows[${index}][description]`" x-model="row.description" class="{{ $inputClass }}" placeholder="شرح این ردیف...">
                                </td>
                                <td class="px-4 py-4">
                                    <input type="text"
                                           :name="`rows[${index}][debit]`"
                                           x-model="row.debit_formatted"
                                           @input="formatDebit(index, $event.target.value)"
                                           class="{{ $inputClass }} dir-ltr text-left font-bold tabular-nums text-rose-600 dark:text-rose-400 placeholder:text-gray-300"
                                           placeholder="0">
                                </td>
                                <td class="px-4 py-4">
                                    <input type="text"
                                           :name="`rows[${index}][credit]`"
                                           x-model="row.credit_formatted"
                                           @input="formatCredit(index, $event.target.value)"
                                           class="{{ $inputClass }} dir-ltr text-left font-bold tabular-nums text-emerald-600 dark:text-emerald-400 placeholder:text-gray-300"
                                           placeholder="0">
                                </td>
                                <td class="px-4 py-4 text-end whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button"
                                                @click="autoBalance(index)"
                                                title="تراز خودکار این ردیف"
                                                x-show="!isBalanced && getUnbalancedDiff() > 0"
                                                class="px-2 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 dark:hover:bg-indigo-500/20 text-[11px] font-bold transition-all">
                                            تراز خودکار
                                        </button>
                                        <button type="button"
                                                @click="removeRow(index)"
                                                class="p-2 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-all"
                                                x-show="rows.length > 2"
                                                title="حذف ردیف">
                                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Balance Footer Bar --}}
            <div class="p-6 bg-gray-50/70 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-6 text-sm font-bold">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                        <span class="text-gray-600 dark:text-gray-400">جمع بدهکار:</span>
                        <span class="text-rose-600 dark:text-rose-400 tabular-nums font-black text-base" x-text="formatNumber(totalDebit)"></span>
                        <span class="text-xs text-gray-500 font-normal">{{ $currencySuffix }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span class="text-gray-600 dark:text-gray-400">جمع بستانکار:</span>
                        <span class="text-emerald-600 dark:text-emerald-400 tabular-nums font-black text-base" x-text="formatNumber(totalCredit)"></span>
                        <span class="text-xs text-gray-500 font-normal">{{ $currencySuffix }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400">وضعیت تراز سند:</span>
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-xs font-bold transition-all shadow-sm"
                          :class="isBalanced ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20' : 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20'">
                        <span class="w-2 h-2 rounded-full animate-pulse" :class="isBalanced ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                        <span x-text="isBalanced ? 'تراز است (متوازن)' : 'نامتوازن (اختلاف ' + formatNumber(getUnbalancedDiff()) + ' {{ $currencySuffix }})'"></span>
                    </span>
                </div>
            </div>
        </div>

        {{-- Sticky Submit Bar --}}
        <div class="sticky bottom-4 z-40 max-w-screen-2xl mx-auto">
            <div class="flex justify-between items-center bg-white/90 dark:bg-gray-800/90 backdrop-blur-md p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
                <a href="{{ route('admin.accounting.documents.index') }}" class="px-6 py-3 rounded-xl text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                    انصراف
                </a>
                <button type="submit"
                        :disabled="!isBalanced"
                        class="px-8 py-3.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>ثبت و تایید سند مالی</span>
                </button>
            </div>
        </div>

    </div>
</form>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('multiLineDocumentForm', () => {
            const rawOldRows = @json(old('rows', []));
            let initialRows = [];

            if (Array.isArray(rawOldRows) && rawOldRows.length >= 2) {
                initialRows = rawOldRows.map((r, i) => {
                    let debitVal = parseInt(String(r.debit || 0).replace(/[^0-9]/g, ''), 10) || 0;
                    let creditVal = parseInt(String(r.credit || 0).replace(/[^0-9]/g, ''), 10) || 0;
                    return {
                        id: i + 1,
                        category_id: r.category_id || '',
                        fund_account_id: r.fund_account_id || '',
                        description: r.description || '',
                        debit: debitVal,
                        credit: creditVal,
                        debit_formatted: debitVal ? debitVal.toLocaleString('en-US') : '',
                        credit_formatted: creditVal ? creditVal.toLocaleString('en-US') : ''
                    };
                });
            } else {
                initialRows = [
                    { id: 1, category_id: '', fund_account_id: '', description: '', debit: 0, credit: 0, debit_formatted: '', credit_formatted: '' },
                    { id: 2, category_id: '', fund_account_id: '', description: '', debit: 0, credit: 0, debit_formatted: '', credit_formatted: '' }
                ];
            }

            return {
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
                autoBalance(index) {
                    let diff = this.getUnbalancedDiff();
                    if (diff <= 0) return;

                    if (this.totalDebit < this.totalCredit) {
                        // Needs more debit
                        this.formatDebit(index, String(this.rows[index].debit + diff));
                    } else if (this.totalCredit < this.totalDebit) {
                        // Needs more credit
                        this.formatCredit(index, String(this.rows[index].credit + diff));
                    }
                },
                formatNumber(num) {
                    return (num || 0).toLocaleString('en-US');
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
