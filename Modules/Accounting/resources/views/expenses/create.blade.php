@extends('layouts.user')

@section('title', 'ثبت هزینه جدید')

@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
    $selectClass = $inputClass . " appearance-none cursor-pointer";

    // میز برش: آماده‌سازی و مرتب‌کردن داده‌ها قبل از فرستادن به جاوااسکریپت
    $mappedBanks = $banks->mapWithKeys(fn($bank) => [$bank->id => $bank->balance]);
    $mappedCategories = $categories->mapWithKeys(fn($category) => [$category->id => $category->title === 'دسته مشتریان']);

    $mappedCustomers = $customers->map(function($customer) {
        return [
            'id' => $customer->id,
            'text' => ($customer->full_name ?: $customer->username) . ' (' . ($customer->national_code ?? 'N/A') . ')',
            'national_code' => $customer->national_code,
        ];
    })->values();
@endphp

@section('content')
    <form action="{{ route('admin.accounting.expenses.store') }}" method="POST" enctype="multipart/form-data" x-data="formHandlers()">
        @csrf
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-24">

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
                        اطلاعات هزینه جدید را در این فرم وارد کنید.
                    </p>
                </div>
            </div>

            {{-- Form Card --}}
            <div class="{{ $cardClass }}">
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="description" class="{{ $labelClass }}">شرح هزینه <span class="text-red-500">*</span></label>
                        <input type="text" name="description" id="description" value="{{ old('description') }}" class="{{ $inputClass }}" required>
                        @error('description')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="amount" class="{{ $labelClass }}">مبلغ <span class="text-red-500">*</span></label>
                        <input type="text" name="amount" id="amount" value="{{ old('amount') }}" class="{{ $inputClass }} dir-ltr text-left" @input="formatNumber($el)" required>
                        @error('amount')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="document_date" class="{{ $labelClass }}">تاریخ <span class="text-red-500">*</span></label>
                        <input type="text" name="document_date" id="document_date" data-jdp value="{{ old('document_date', jdate()->format('Y/m/d')) }}" class="{{ $inputClass }} dir-ltr text-center" required>
                        @error('document_date')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="category_id" class="{{ $labelClass }}">دسته‌بندی <span class="text-red-500">*</span></label>
                        <select name="category_id" id="category_id" class="{{ $selectClass }}" x-model="selectedCategoryId" @change="updateCustomerVisibility()" required>
                            <option value="">انتخاب کنید...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->title }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="bank_id" class="{{ $labelClass }}">پرداخت از حساب <span class="text-red-500">*</span></label>
                        <select name="bank_id" id="bank_id" class="{{ $selectClass }}" x-model="selectedBankId" @change="updateSelectedBalance()" required>
                            <option value="">انتخاب کنید...</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->id }}" {{ old('bank_id') == $bank->id ? 'selected' : '' }}>{{ $bank->display_info }}</option>
                            @endforeach
                        </select>
                        @error('bank_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        <div x-show="selectedBankBalance !== null" class="mt-2 text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 p-2 rounded-lg inline-block">
                            موجودی فعلی: <span x-text="formatBalanceText(selectedBankBalance)" class="font-bold"></span> تومان
                        </div>
                    </div>
                    <div class="md:col-span-2" x-show="showCustomerSelect">
                        <label for="client_id" class="{{ $labelClass }}">مشتری <span class="text-red-500">*</span></label>
                        <select name="client_id" id="client_id" class="{{ $selectClass }}">
                            <option value="">انتخاب کنید...</option>
                        </select>
                        @error('client_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="reference_number" class="{{ $labelClass }}">شماره پیگیری / فیش (اختیاری)</label>
                        <input type="text" name="reference_number" id="reference_number" value="{{ old('reference_number') }}" class="{{ $inputClass }} dir-ltr text-left">
                        @error('reference_number')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="attachment" class="{{ $labelClass }}">فایل ضمیمه (اختیاری)</label>
                        <input type="file" name="attachment" id="attachment" class="block mt-2 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                        @error('attachment')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Sticky Footer --}}
            <div class="sticky bottom-4 z-40 max-w-4xl mx-auto">
                <div class="flex justify-between items-center bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
                    <a href="{{ route('admin.accounting.expenses.index') }}" class="px-6 py-3 rounded-xl text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                        انصراف
                    </a>
                    <button type="submit" class="px-8 py-3 rounded-xl bg-red-600 text-white font-bold shadow-lg shadow-red-500/30 hover:bg-red-700 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        ثبت هزینه
                    </button>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        @includeIf('partials.jalali-date-picker')
        {{-- TomSelect CSS --}}
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
        {{-- TomSelect JS --}}
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

        <script>
            function formHandlers() {
                return {
                    // حالا متغیرهای آماده شده رو اینجا به راحتی صدا می‌زنیم
                    banksData: @json($mappedBanks),
                    categoriesData: @json($mappedCategories),
                    customersData: @json($mappedCustomers),

                    selectedBankId: '{{ old('bank_id') }}',
                    selectedBankBalance: null,
                    selectedCategoryId: '{{ old('category_id') }}',
                    showCustomerSelect: false,
                    tomSelectCustomer: null, // To hold the TomSelect instance

                    init() {
                        this.updateSelectedBalance();
                        this.updateCustomerVisibility();

                        // Initialize TomSelect for client_id
                        this.$nextTick(() => {
                            this.tomSelectCustomer = new TomSelect('#client_id', {
                                sortField: { field: "text", direction: "asc" },
                                valueField: 'id',
                                labelField: 'text',
                                searchField: ['text', 'national_code'],
                                options: this.customersData,
                            });
                            // Set initial value if old('client_id') exists
                            if ('{{ old('client_id') }}') {
                                this.tomSelectCustomer.setValue('{{ old('client_id') }}');
                            }
                        });
                    },
                    updateSelectedBalance() {
                        if (this.selectedBankId && this.banksData[this.selectedBankId] !== undefined) {
                            this.selectedBankBalance = this.banksData[this.selectedBankId];
                        } else {
                            this.selectedBankBalance = null;
                        }
                    },
                    updateCustomerVisibility() {
                        this.showCustomerSelect = this.selectedCategoryId && this.categoriesData[this.selectedCategoryId];
                        // If customer select is hidden, clear its value
                        if (!this.showCustomerSelect && this.tomSelectCustomer) {
                            this.tomSelectCustomer.clear();
                        }
                    },
                    formatBalanceText(balance) {
                        return parseFloat(balance).toLocaleString('en-US');
                    },
                    formatNumber(el) {
                        let value = el.value.replace(/[^0-9]/g, '');
                        el.value = value ? parseInt(value, 10).toLocaleString('en-US') : '';
                    }
                }
            }
        </script>
    @endpush
@endsection
