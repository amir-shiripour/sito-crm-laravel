@extends('layouts.user')

@section('title', 'ویرایش چک')

@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
    $selectClass = $inputClass . " appearance-none cursor-pointer";

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
            'type_label' => $types[$b->type] ?? ($b->type ?: 'خزانه'),
            'type_badge' => $typeBadges[$b->type] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        ];
    })->values()->all();
@endphp

@section('content')
    @includeIf('partials.jalali-date-picker')

    <form action="{{ route('admin.accounting.cheques.update', $cheque) }}" method="POST" x-data="chequeForm()">
        @csrf
        @method('PUT')
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-24">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z"/></svg>
                        </span>
                        ویرایش چک
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-14 max-w-2xl leading-relaxed">
                        اطلاعات چک را در این فرم ویرایش کنید.
                    </p>
                </div>
            </div>

            <div class="{{ $cardClass }}">
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label for="type" class="{{ $labelClass }}">نوع چک <span class="text-red-500">*</span></label>
                        <select name="type" id="type" class="{{ $selectClass }}" required>
                            <option value="received" {{ old('type', $cheque->type) == 'received' ? 'selected' : '' }}>چک
                                دریافتی
                            </option>
                            <option value="issued" {{ old('type', $cheque->type) == 'issued' ? 'selected' : '' }}>چک
                                پرداختی
                            </option>
                        </select>
                        @error('type')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="amount" class="{{ $labelClass }}">مبلغ چک ({{ $currencySuffix }}) <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="amount" id="amount"
                               value="{{ old('amount', number_format($cheque->amount, 0, '.', '')) }}"
                               class="{{ $inputClass }} dir-ltr text-left" @input="formatAmount($el)" required>
                        @error('amount')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="bank_name" class="{{ $labelClass }}">نام بانک <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="bank_name" id="bank_name"
                               value="{{ old('bank_name', $cheque->bank_name) }}" class="{{ $inputClass }}" required>
                        @error('bank_name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="branch_name" class="{{ $labelClass }}">نام شعبه (اختیاری)</label>
                        <input type="text" name="branch_name" id="branch_name"
                               value="{{ old('branch_name', $cheque->branch_name) }}" class="{{ $inputClass }}">
                        @error('branch_name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="payee_name" class="{{ $labelClass }}">نام دریافت کننده (در وجه) <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="payee_name" id="payee_name"
                               value="{{ old('payee_name', $cheque->payee_name) }}" class="{{ $inputClass }}" required>
                        @error('payee_name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="cheque_number" class="{{ $labelClass }}">شماره چک <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="cheque_number" id="cheque_number"
                               value="{{ old('cheque_number', $cheque->cheque_number) }}"
                               class="{{ $inputClass }} dir-ltr text-left">
                        @error('cheque_number')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="sayyad_id" class="{{ $labelClass }}">شناسه صیادی (۱۶ رقم)</label>
                        <input type="text" name="sayyad_id" id="sayyad_id"
                               value="{{ old('sayyad_id', $cheque->sayyad_id) }}"
                               class="{{ $inputClass }} dir-ltr text-left" maxlength="16">
                        @error('sayyad_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="issue_date" class="{{ $labelClass }}">تاریخ صدور <span class="text-red-500">*</span></label>
                        <input type="text" name="issue_date" id="issue_date" data-jdp
                               value="{{ old('issue_date', jdate($cheque->issue_date)->format('Y/m/d')) }}"
                               class="{{ $inputClass }} dir-ltr text-center" required>
                        @error('issue_date')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="due_date" class="{{ $labelClass }}">تاریخ سررسید <span class="text-red-500">*</span></label>
                        <input type="text" name="due_date" id="due_date" data-jdp
                               value="{{ old('due_date', jdate($cheque->due_date)->format('Y/m/d')) }}"
                               class="{{ $inputClass }} dir-ltr text-center" required>
                        @error('due_date')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-1">
                        <label for="client_id" class="{{ $labelClass }}">مربوط به مشتری (اختیاری)</label>
                        <select name="client_id" id="client_id" class="{{ $selectClass }}">
                            <option value="">هیچکدام</option>
                            @foreach($clients as $client)
                                <option
                                    value="{{ $client->id }}" {{ old('client_id', $cheque->client_id) == $client->id ? 'selected' : '' }}>
                                    {{ $client->full_name ?: $client->username }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-1">
                        <label class="{{ $labelClass }}">حساب بانکی مرتبط (اختیاری)</label>
                        <div x-data="{
                            open: false,
                            search: '',
                            selectedBankId: '{{ old('bank_id', $cheque->bank_id) }}',
                            options: @js($banksList),
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
                                this.selectedBankId = opt ? String(opt.id) : '';
                                this.open = false;
                                this.search = '';
                            },
                            getSelectedTitle() {
                                let found = this.options.find(o => String(o.id) === String(this.selectedBankId));
                                if (!found) return 'هیچکدام (انتخاب حساب...)';
                                return found.name + (found.bank_name ? ' — ' + found.bank_name : '');
                            },
                            formatFa(str) {
                                if (!str && str !== 0) return '';
                                const farsi = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
                                return String(str).replace(/[0-9]/g, w => farsi[+w]);
                            }
                        }" class="relative" :class="{ 'z-50': open }">
                            <input type="hidden" name="bank_id" :value="selectedBankId">

                            <button type="button" @click="open = !open"
                                    class="{{ $inputClass }} flex items-center justify-between cursor-pointer w-full text-start py-2.5 px-3.5 text-xs sm:text-sm">
                                <span x-text="getSelectedTitle()" class="truncate font-medium"></span>
                                <div class="flex items-center gap-1 shrink-0 ms-1">
                                    <span x-show="selectedBankId" @click.stop="select(null)"
                                          class="text-gray-400 hover:text-rose-500 p-0.5 rounded transition-colors"
                                          title="حذف انتخاب">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
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
                                 class="absolute z-[100] top-full mt-1.5 start-0 w-full min-w-[280px] sm:min-w-[340px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl p-2 max-h-64 overflow-y-auto ring-1 ring-black/5 dark:ring-white/10">
                                <div class="p-1 border-b border-gray-100 dark:border-gray-700 mb-1">
                                    <input type="text" x-model="search"
                                           placeholder="جستجو نام حساب، بانک، شماره حساب..."
                                           class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                </div>

                                <div @click="select(null)"
                                     class="px-3 py-2 text-xs rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 cursor-pointer text-gray-500 dark:text-gray-400 transition-colors"
                                     :class="{ 'font-bold text-indigo-600 dark:text-indigo-400': !selectedBankId }">
                                    هیچکدام
                                </div>

                                <template x-for="opt in filteredOptions" :key="opt.id">
                                    <div @click="select(opt)"
                                         class="px-3 py-2 text-xs rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                         :class="{ 'bg-indigo-50/70 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold': String(selectedBankId) === String(opt.id) }">
                                        <div class="flex flex-col gap-0.5 truncate">
                                            <div class="flex items-center gap-1.5 truncate font-medium">
                                                <span x-text="opt.name"></span>
                                                <span x-show="opt.bank_name" class="text-[10px] text-gray-400"
                                                      x-text="'(' + opt.bank_name + ')'"></span>
                                            </div>
                                            <span x-show="opt.account_number" class="text-[10px] text-gray-400"
                                                  x-text="'ش‌ح: ' + formatFa(opt.account_number)"></span>
                                        </div>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded border shrink-0 font-medium"
                                              :class="opt.type_badge"
                                              x-text="opt.type_label"></span>
                                    </div>
                                </template>

                                <div x-show="filteredOptions.length === 0"
                                     class="p-3 text-xs text-gray-400 text-center">
                                    هیچ حسابی پیدا نشد
                                </div>
                            </div>
                        </div>
                        @error('bank_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="status" class="{{ $labelClass }}">وضعیت چک <span
                                class="text-red-500">*</span></label>
                        <select name="status" id="status" class="{{ $selectClass }}" required>
                            <option
                                value="registered" {{ old('status', $cheque->status) == 'registered' ? 'selected' : '' }}>
                                ثبت شده
                            </option>
                            <option value="issued" {{ old('status', $cheque->status) == 'issued' ? 'selected' : '' }}>
                                صادر شده
                            </option>
                            <option value="passed" {{ old('status', $cheque->status) == 'passed' ? 'selected' : '' }}>
                                وصول شده
                            </option>
                            <option
                                value="returned" {{ old('status', $cheque->status) == 'returned' ? 'selected' : '' }}>
                                برگشت خورده
                            </option>
                        </select>
                        @error('status')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="{{ $labelClass }}">توضیحات (اختیاری)</label>
                        <textarea name="description" id="description" rows="3"
                                  class="{{ $inputClass }}">{{ old('description', $cheque->description) }}</textarea>
                        @error('description')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="sticky bottom-4 z-40 max-w-screen-2xl mx-auto">
                <div
                    class="flex justify-between items-center bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
                    <a href="{{ route('admin.accounting.cheques.index') }}"
                       class="px-6 py-3 rounded-xl text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                        انصراف
                    </a>
                    <button type="submit"
                            class="px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        ذخیره تغییرات
                    </button>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (document.getElementById('client_id')) {
                    new TomSelect('#client_id', {
                        sortField: {field: "text", direction: "asc"}
                    });
                }
                if (document.getElementById('bank_id')) {
                    new TomSelect('#bank_id', {
                        sortField: {field: "text", direction: "asc"}
                    });
                }
            });

            function chequeForm() {
                return {
                    init() {
                        this.formatAmount(document.getElementById('amount'));
                    },
                    toEnglishDigits(str) {
                        if (!str) return '';
                        const persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                        const arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
                        const english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

                        return str.toString()
                            .replace(new RegExp(persian.join('|'), 'g'), c => english[persian.indexOf(c)])
                            .replace(new RegExp(arabic.join('|'), 'g'), c => english[arabic.indexOf(c)]);
                    },
                    formatAmount(el) {
                        let value = this.toEnglishDigits(el.value);
                        let numericValue = value.replace(/[^0-9]/g, '');

                        if (numericValue) {
                            el.value = parseInt(numericValue, 10).toLocaleString('en-US');
                        } else {
                            el.value = '';
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection
