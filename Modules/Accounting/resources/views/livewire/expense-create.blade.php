@php
    use Modules\Accounting\App\Services\CurrencyService;
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
    $selectClass = $inputClass . " appearance-none cursor-pointer";

    $tomSelectCustomers = $customers->map(function($customer) {
        return [
            'value' => $customer->id,
            'text' => ($customer->full_name ?: $customer->username) . ' (' . ($customer->national_code ?? 'N/A') . ')',
        ];
    })->values();
@endphp

@includeIf('partials.jalali-date-picker')

<form wire:submit.prevent="save">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-24"
         x-data="expenseForm({
            payments: @entangle('payments'),
            totalAmount: @entangle('amount'),
            categoryId: @entangle('category_id')
         })">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-red-600 text-white shadow-lg shadow-red-500/30">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    </span>
                    ثبت هزینه جدید
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-14 max-w-2xl leading-relaxed">
                    اطلاعات هزینه و نحوه پرداخت آن را وارد کنید.
                </p>
            </div>
        </div>

        {{-- General Error Message --}}
        @if (session()->has('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg" role="alert">
                <p class="font-bold">خطا</p>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg" role="alert">
                <p class="font-bold">لطفاً خطاهای زیر را برطرف کنید:</p>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        {{-- Expense Details Card --}}
        <div class="{{ $cardClass }}">
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="description" class="{{ $labelClass }}">شرح هزینه <span class="text-red-500">*</span></label>
                    <input type="text" id="description" wire:model.lazy="description" class="{{ $inputClass }}" required>
                    @error('description')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="amount" class="{{ $labelClass }}">مبلغ کل هزینه ({{ $currencySuffix }}) <span class="text-red-500">*</span></label>
                    <input type="text" id="amount" wire:model.live="amount" @input="formatNumber($el)" class="{{ $inputClass }} dir-ltr text-left">
                    @error('amount')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="document_date" class="{{ $labelClass }}">تاریخ <span class="text-red-500">*</span></label>
                    <input type="text" id="document_date" data-jdp wire:model.lazy="document_date" class="{{ $inputClass }} dir-ltr text-center" required>
                    @error('document_date')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="category_id" class="{{ $labelClass }}">دسته‌بندی <span class="text-red-500">*</span></label>
                    <select id="category_id" x-model="categoryId" class="{{ $selectClass }}" required>
                        <option value="">انتخاب کنید...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->title }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div wire:ignore>
                    <label for="client_id" class="{{ $labelClass }}">
                        مشتری
                        <template x-if="isCustomerCategorySelected()">
                            <span class="text-red-500">*</span>
                        </template>
                    </label>
                    <select id="client_id">
                        <option value="">هیچکدام</option>
                    </select>
                    @error('client_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Payments Card --}}
        <div class="{{ $cardClass }}">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">جزئیات پرداخت</h3>
                <div class="space-y-4">
                    <template x-for="(payment, index) in payments" :key="index">
                        <div class="grid grid-cols-12 gap-4 items-start p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-700">
                            <div class="col-span-12 md:col-span-3">
                                <label class="{{ $labelClass }}">روش پرداخت</label>
                                <select x-model="payment.method" class="{{ $selectClass }}">
                                    <option value="bank">حساب بانکی</option>
                                    <option value="cheque">واگذاری چک</option>
                                </select>
                            </div>

                            <div class="col-span-12 md:col-span-4" x-show="payment.method === 'bank'">
                                <label class="{{ $labelClass }}">از حساب</label>
                                <select x-model="payment.bank_id" class="{{ $selectClass }}">
                                    @foreach($banks as $bank)
                                        @php $bal = $bank->transactions->sum('credit') - $bank->transactions->sum('debit'); @endphp
                                        <option value="{{ $bank->id }}">{{ $bank->name }} ({{ CurrencyService::formatWithSuffix($bal) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-12 md:col-span-4" x-show="payment.method === 'cheque'">
                                <label class="{{ $labelClass }}">انتخاب چک</label>
                                <select x-model="payment.cheque_id" class="{{ $selectClass }}">
                                    <option value="">یک چک انتخاب کنید...</option>
                                    @foreach($availableCheques as $cheque)
                                        <option value="{{ $cheque->id }}">چک #{{ $cheque->cheque_number }} - مبلغ: {{ CurrencyService::formatWithSuffix($cheque->amount) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-span-10 md:col-span-4">
                                <label class="{{ $labelClass }}">مبلغ پرداختی ({{ $currencySuffix }})</label>
                                <input type="text" x-model="payment.amount" @input="updateAmounts(index); formatNumber($el)" class="{{ $inputClass }} dir-ltr text-left">
                            </div>

                            <div class="col-span-2 md:col-span-1 flex items-end justify-end h-full">
                                <template x-if="payments.length > 1">
                                <button type="button" @click="removePaymentRow(index)" class="text-gray-400 hover:text-red-500 transition-colors p-2 rounded-full">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="mt-4">
                    <button type="button" @click="addPaymentRow()" class="inline-flex items-center px-3 py-2 border border-dashed border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-xl text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        + افزودن ردیف پرداخت
                    </button>
                </div>
            </div>
        </div>

        {{-- Summary and Sticky Footer --}}
        <div class="sticky bottom-4 z-40 max-w-4xl mx-auto">
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
                <div class="flex justify-end">
                    <button type="submit" class="px-8 py-3 rounded-xl bg-red-600 text-white font-bold shadow-lg shadow-red-500/30 hover:bg-red-700 transition-all flex items-center gap-2">
                        <div wire:loading wire:target="save" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                        <span wire:loading.remove>ثبت هزینه</span>
                        <span wire:loading>در حال پردازش...</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Excess Payment Modal --}}
        <div x-show="$wire.showExcessPaymentModal"
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="$wire.set('showExcessPaymentModal', false)"></div>
            <div class="flex min-h-dvh items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-yellow-50/50 dark:bg-yellow-900/10">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            مدیریت مازاد پرداخت
                        </h3>
                        <button type="button" @click="$wire.set('showExcessPaymentModal', false)" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="px-5 py-6 space-y-5">
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            مبلغ پرداختی شما
                            <strong class="font-bold text-red-600">{{ CurrencyService::formatWithSuffix(abs($this->remainingAmountToPay)) }}</strong>
                            بیشتر از مبلغ کل هزینه است. لطفاً نحوه دریافت این مبلغ مازاد را مشخص کنید.
                        </p>
                        <div>
                            <label for="excess_payment_bank_id" class="{{ $labelClass }}">واریز به حساب <span class="text-red-500">*</span></label>
                            <select id="excess_payment_bank_id" wire:model="excess_payment_bank_id" class="{{ $selectClass }}">
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                            @error('excess_payment_bank_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="excess_payment_description" class="{{ $labelClass }}">توضیحات (اختیاری)</label>
                            <input type="text" id="excess_payment_description" wire:model.lazy="excess_payment_description" class="{{ $inputClass }}" placeholder="مثال: دریافت نقدی از فروشنده">
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-5 py-4 flex flex-row-reverse gap-2">
                        <button type="button" wire:click="saveWithExcess" class="inline-flex w-full justify-center rounded-xl bg-yellow-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-500 sm:w-auto transition-colors items-center gap-2">
                            تایید نهایی و ثبت هزینه
                        </button>
                        <button type="button" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors dark:bg-gray-700 dark:text-gray-200 dark:ring-gray-600 dark:hover:bg-gray-600" @click="$wire.set('showExcessPaymentModal', false)">
                            بازنگری
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('expenseForm', (config) => ({
            payments: config.payments,
            totalAmount: config.totalAmount,
            categoryId: config.categoryId,
            categories: @json($categories->keyBy('id')),
            tomSelectClient: null,

            init() {
                this.tomSelectClient = new TomSelect('#client_id', {
                    options: @json($tomSelectCustomers),
                    valueField: 'value',
                    labelField: 'text',
                    searchField: ['text'],
                    create: false,
                    onChange: (value) => {
                        this.$wire.set('client_id', value);
                    }
                });

                if (this.$wire.get('client_id')) {
                    this.tomSelectClient.setValue(this.$wire.get('client_id'));
                }

                this.$watch('totalAmount', (newValue) => {
                    if (this.payments.length === 1) {
                        this.payments[0].amount = this.cleanNumber(newValue);
                    }
                });

                this.$watch('categoryId', (newCategoryId) => {
                    this.$wire.set('category_id', newCategoryId);
                });
            },
            isCustomerCategorySelected() {
                if (!this.categoryId) return false;
                const category = this.categories[this.categoryId];
                return category && category.title === 'دسته مشتریان';
            },
            cleanNumber(value) {
                if (typeof value !== 'string') {
                    value = String(value);
                }
                const persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                const arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
                const english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                return value.replace(new RegExp(persian.join('|'), 'g'), c => english[persian.indexOf(c)])
                            .replace(new RegExp(arabic.join('|'), 'g'), c => english[arabic.indexOf(c)])
                            .replace(/,/g, '') || '0';
            },
            formatNumber(el) {
                let value = this.cleanNumber(el.value);
                el.value = value !== '0' ? parseInt(value, 10).toLocaleString('en-US') : '';
            },
            updateAmounts(currentIndex) {
                let total = parseFloat(this.cleanNumber(this.totalAmount));
                let paid = 0;
                this.payments.forEach((p, index) => {
                    if (index <= currentIndex) {
                        paid += parseFloat(this.cleanNumber(p.amount));
                    }
                });

                let remaining = total - paid;

                if (this.payments[currentIndex + 1]) {
                    this.payments[currentIndex + 1].amount = remaining > 0 ? remaining : 0;
                }
            },
            addPaymentRow() {
                let total = parseFloat(this.cleanNumber(this.totalAmount));
                let paid = 0;
                if(Array.isArray(this.payments)) {
                    this.payments.forEach(p => {
                        paid += parseFloat(this.cleanNumber(p.amount));
                    });
                }
                let remaining = total - paid;
                this.$wire.addPayment(remaining > 0 ? remaining : 0);
            },
            removePaymentRow(index) {
                this.$wire.removePayment(index);
            }
        }));
    });
</script>
@endpush
