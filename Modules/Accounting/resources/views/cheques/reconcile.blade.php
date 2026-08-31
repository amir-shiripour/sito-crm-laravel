@extends('layouts.user')

@section('title', 'تعیین وضعیت چک')

@php
    use Modules\Accounting\App\Services\CurrencyService;
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
    $selectClass = $inputClass . " appearance-none cursor-pointer";

    $mappedBanks = $banks->mapWithKeys(fn($bank) => [$bank->id => $bank->balance]);

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
            'balance' => (float) ($b->balance ?? $b->current_balance ?? 0),
        ];
    })->values()->all();
@endphp

@section('content')
    @includeIf('partials.jalali-date-picker')

    <form action="{{ route('admin.accounting.cheques.reconcile.process', $cheque) }}" method="POST" x-data="reconcileForm()">
        @csrf
        @method('PUT')
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-24">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-500/30">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        تعیین وضعیت چک
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-14 max-w-2xl leading-relaxed">
                        وضعیت نهایی چک به شماره <span
                            class="font-bold text-gray-800 dark:text-gray-200">{{ $cheque->cheque_number }}</span> را
                        مشخص کنید.
                    </p>
                </div>
            </div>

            <div class="{{ $cardClass }}">
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label for="status" class="{{ $labelClass }}">وضعیت جدید <span
                                class="text-red-500">*</span></label>
                        <select name="status" id="status" class="{{ $selectClass }}" required>
                            <option value="passed">وصول شده</option>
                            <option value="returned">برگشت خورده</option>
                        </select>
                        @error('status')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">
                            @if($cheque->type === 'received')
                                واریز به حساب خزانه‌داری / بانک <span class="text-red-500">*</span>
                            @else
                                برداشت از حساب خزانه‌داری / بانک <span class="text-red-500">*</span>
                            @endif
                        </label>
                        <div x-data="{
                            open: false,
                            search: '',
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
                                selectedBankId = opt ? String(opt.id) : '';
                                updateSelectedBalance();
                                this.open = false;
                                this.search = '';
                            },
                            getSelectedTitle() {
                                let found = this.options.find(o => String(o.id) === String(selectedBankId));
                                if (!found) return 'انتخاب حساب خزانه‌داری...';
                                return found.name + (found.bank_name ? ' — ' + found.bank_name : '');
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
                            <input type="hidden" name="bank_id" :value="selectedBankId" required>

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
                                           class="w-full text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                                </div>

                                <template x-for="opt in filteredOptions" :key="opt.id">
                                    <div @click="select(opt)"
                                         class="px-3 py-2 text-xs rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-300 cursor-pointer text-gray-700 dark:text-gray-200 transition-colors flex items-center justify-between gap-2"
                                         :class="{ 'bg-blue-50/70 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 font-bold': String(selectedBankId) === String(opt.id) }">
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
                                                      x-text="'موجودی: ' + formatNumber(opt.balance) + ' ' + '{{ CurrencyService::getBaseCurrency() }}'"></span>
                                            </div>
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
                        <label for="reconciliation_date" class="{{ $labelClass }}">تاریخ وصول / برگشت <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="reconciliation_date" id="reconciliation_date" data-jdp
                               value="{{ old('reconciliation_date', jdate()->format('Y/m/d')) }}"
                               class="{{ $inputClass }} dir-ltr text-center" required>
                        @error('reconciliation_date')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="{{ $labelClass }}">توضیحات (اختیاری)</label>
                        <textarea name="description" id="description" rows="3"
                                  class="{{ $inputClass }}">{{ old('description') }}</textarea>
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
                            class="px-8 py-3 rounded-xl bg-blue-600 text-white font-bold shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        ثبت تغییرات
                    </button>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            function reconcileForm() {
                return {
                    banksData: @json($mappedBanks),
                    selectedBankId: '{{ old('bank_id') }}',
                    selectedBankBalance: null,

                    init() {
                        this.updateSelectedBalance();
                    },
                    updateSelectedBalance() {
                        if (this.selectedBankId && this.banksData[this.selectedBankId] !== undefined) {
                            this.selectedBankBalance = this.banksData[this.selectedBankId];
                        } else {
                            this.selectedBankBalance = null;
                        }
                    },
                    formatBalanceText(balance) {
                        return '{{ CurrencyService::formatWithSuffix('') }}'.replace('0', parseFloat(balance).toLocaleString('en-US'));
                    }
                }
            }
        </script>
    @endpush
@endsection
