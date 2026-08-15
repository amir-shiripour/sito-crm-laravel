@php use Modules\Accounting\App\Services\CurrencyService; @endphp
@extends('layouts.user')

@section('title', 'ثبت چک جدید')

@include('partials.jalali-date-picker')

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-visible backdrop-blur-xl relative z-30";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-sm dark:border-gray-700 dark:bg-gray-900/50 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";

    $customersListForJs = $clients->map(function ($c) {
        return [
            'id'       => $c->id,
            'name'     => $c->full_name,
            'email'    => $c->email ?? '',
            'phone'    => $c->phone ?? '',
            'username' => $c->username ?? '',
            'label'    => $c->full_name . ' - ' . ($c->email ?? $c->phone ?? ''),
        ];
    })->values();
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-24"
         x-data="chequeForm({
        amount: '{{ old('amount', '') }}',
        type: '{{ old('type', 'receivable') }}',
        initialCustomer: '{{ old('client_id', '') }}',
        customersList: {{ json_encode($customersListForJs) }}
     })">

        {{-- Errors --}}
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
                    <p class="font-black text-base mb-2">خطا در ثبت اطلاعات!</p>
                    <ul class="list-disc ps-5 space-y-1.5 marker:text-red-400">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

    <form action="{{ route('admin.accounting.cheques.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                    <span
                        class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H4a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </span>
                    ثبت چک جدید در خزانه‌داری
                </h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">اطلاعات چک دریافتی یا پرداختی را با دقت ثبت
                    کنید.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.accounting.cheques.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-bold transition-all active:scale-95">
                    بازگشت به لیست
                </a>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Basic Info Card --}}
            <div class="{{ $cardClass }} z-50">
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                        اطلاعات پایه چک
                    </h3>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        @if($redirectTo ?? request('redirect_to'))
                            <input type="hidden" name="redirect_to" value="{{ $redirectTo ?? request('redirect_to') }}">
                        @endif
                        <label class="{{ $labelClass }}">نوع چک <span class="text-rose-500 font-black">*</span></label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="flex items-center p-4 rounded-2xl border cursor-pointer transition-all"
                                   :class="{ 'bg-indigo-50/50 border-indigo-200 dark:bg-indigo-500/10 dark:border-indigo-500/30': type === 'receivable' }">
                                <input type="radio" name="type" value="receivable" x-model="type"
                                       class="text-indigo-600 focus:ring-indigo-500">
                                <span class="mr-3 font-bold text-sm text-gray-900 dark:text-white">چک دریافتی</span>
                            </label>
                            <label class="flex items-center p-4 rounded-2xl border cursor-pointer transition-all"
                                   x-show="!isLockedReceivable"
                                   :class="{ 'bg-indigo-50/50 border-indigo-200 dark:bg-indigo-500/10 dark:border-indigo-500/30': type === 'payable' }">
                                <input type="radio" name="type" value="payable" x-model="type" :disabled="isLockedReceivable"
                                       class="text-indigo-600 focus:ring-indigo-500">
                                <span class="mr-3 font-bold text-sm text-gray-900 dark:text-white">چک پرداختی</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="amount" class="{{ $labelClass }}">مبلغ چک
                            ({{ $currency ?? CurrencyService::getBaseCurrency() }}) <span
                                class="text-rose-500 font-black">*</span></label>
                        <input type="text" name="amount" id="amount" x-model="amount"
                               class="{{ $inputClass }} text-xl font-black text-left dir-ltr tabular-nums text-indigo-700 dark:text-indigo-400"
                               required>
                    </div>

                    {{-- انتخاب مشتری --}}
                    <div class="md:col-span-2 pt-4 border-t border-dashed border-gray-200 dark:border-gray-700">
                        <label class="{{ $labelClass }}">طرف حساب / مشتری مرتبط <span
                                class="text-rose-500 font-black">*</span></label>

                        <input type="hidden" name="client_id" :value="selectedCustomer">

                        <div x-show="!selectedCustomer" class="max-w-xl relative"
                             @click.outside="customerDropdownOpen = false">
                            <div class="relative">
                                <svg
                                    class="absolute start-4 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-gray-400 pointer-events-none"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                                </svg>
                                <input type="text" x-model="customerQuery" @focus="customerDropdownOpen = true"
                                       @input="customerDropdownOpen = true"
                                       class="{{ $inputClass }} ps-11 cursor-text focus:outline-none outline-none"
                                       :class="customerDropdownOpen && filteredCustomers.length > 0 ? 'rounded-b-none border-b-0' : ''"
                                       autocomplete="off" placeholder="جستجو با نام، ایمیل، موبایل یا یوزرنیم...">
                            </div>

                            <div x-show="customerDropdownOpen && filteredCustomers.length > 0" x-transition
                                 class="absolute z-[100] w-full max-h-64 overflow-y-auto bg-white dark:bg-gray-900 border border-t-0 border-gray-200 dark:border-gray-700 rounded-xl rounded-t-none shadow-xl">
                                <template x-for="c in filteredCustomers" :key="c.id">
                                    <button type="button" @click="selectCustomer(c)"
                                            class="w-full text-start px-4 py-3 text-sm hover:bg-indigo-50 dark:hover:bg-gray-800 border-b border-gray-100 dark:border-gray-800 last:border-0 flex items-center gap-3">
                                        <span
                                            class="flex items-center justify-center w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 font-black text-xs shrink-0"
                                            x-text="(c.name || '؟').trim().charAt(0)"></span>
                                        <span class="min-w-0">
                                            <span class="font-bold text-gray-900 dark:text-white block truncate"
                                                  x-text="c.name"></span>
                                            <span class="text-xs text-gray-400 block truncate"
                                                  x-text="[c.phone, c.email, c.username].filter(Boolean).join(' • ')"></span>
                                        </span>
                                    </button>
                                </template>
                            </div>
                            <p x-show="customerDropdownOpen && customerQuery && filteredCustomers.length === 0"
                               class="mt-2 text-xs text-gray-400 px-1">مشتری‌ای یافت نشد.</p>
                        </div>

                        <div x-show="selectedCustomer" x-transition class="max-w-xl">
                            <div
                                class="flex items-center gap-4 p-4 rounded-2xl border-2 border-indigo-200 dark:border-indigo-500/30 bg-indigo-50/60 dark:bg-indigo-500/10">
                                <span
                                    class="flex items-center justify-center w-12 h-12 rounded-full bg-indigo-600 text-white font-black text-base shrink-0"
                                    x-text="(selectedCustomerData?.name || '؟').trim().charAt(0)"></span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-black text-gray-900 dark:text-white truncate"
                                       x-text="selectedCustomerData?.name"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate"
                                       x-text="[selectedCustomerData?.phone, selectedCustomerData?.email].filter(Boolean).join(' • ')"></p>
                                </div>
                                <button type="button" @click="clearCustomer()"
                                        class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white dark:bg-gray-800 text-xs font-bold text-gray-500 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4l16 16M20 4L4 20"/>
                                    </svg>
                                    تغییر
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="payee_name" class="{{ $labelClass }}">
                            <span x-text="type === 'receivable' ? 'صادرکننده چک (صاحب حساب)' : 'در وجه'"></span>
                            <span class="text-rose-500 font-black">*</span>
                        </label>
                        <input type="text" name="payee_name" id="payee_name" value="{{ old('payee_name') }}"
                               class="{{ $inputClass }}" placeholder="نام صادرکننده یا دریافت کننده چک را وارد کنید"
                               required>
                    </div>
                </div>
            </div>

            {{-- Bank Info Card --}}
            <div class="{{ $cardClass }}">
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                        مشخصات بانکی و سررسید
                    </h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label for="cheque_number" class="{{ $labelClass }}">شماره صیاد / شماره چک <span
                                class="text-rose-500 font-black">*</span></label>
                        <input type="text" name="cheque_number" id="cheque_number" value="{{ old('cheque_number') }}"
                               class="{{ $inputClass }} font-mono dir-ltr text-left" required>
                    </div>
                    <div>
                        <label for="bank_name" class="{{ $labelClass }}">نام بانک صادرکننده <span
                                class="text-rose-500 font-black">*</span></label>
                        <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name') }}"
                               class="{{ $inputClass }}" required>
                    </div>
                    <div>
                        <label for="issue_date" class="{{ $labelClass }}">تاریخ صدور چک <span
                                class="text-rose-500 font-black">*</span></label>
                        <div class="relative">
                            <input type="text" name="issue_date" id="issue_date" data-jdp-only-date
                                   value="{{ old('issue_date', jdate()->format('Y/m/d')) }}"
                                   class="{{ $inputClass }} cursor-pointer focus:ring-indigo-500/20 focus:border-indigo-500"
                                   placeholder="انتخاب تاریخ صدور" autocomplete="off" readonly required>
                            <svg
                                class="w-5 h-5 absolute end-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <label for="due_date" class="{{ $labelClass }}">تاریخ سررسید چک <span
                                class="text-rose-500 font-black">*</span></label>
                        <div class="relative">
                            <input type="text" name="due_date" id="due_date" data-jdp-only-date
                                   value="{{ old('due_date') }}"
                                   class="{{ $inputClass }} cursor-pointer focus:ring-indigo-500/20 focus:border-indigo-500"
                                   placeholder="انتخاب تاریخ سررسید" autocomplete="off" readonly required>
                            <svg
                                class="w-5 h-5 absolute end-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sticky Footer --}}
        <div class="sticky bottom-4 z-40 max-w-screen-2xl mx-auto mt-8">
            <div
                class="flex justify-between items-center bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xl">
                <a href="{{ route('admin.accounting.cheques.index') }}"
                   class="px-6 py-3 rounded-xl text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                    انصراف
                </a>
                <button type="submit"
                        class="px-8 py-3.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all flex items-center gap-2 active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    ثبت و ذخیره چک
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('chequeForm', (config) => ({
                isLockedReceivable: {{ ($redirectTo ?? request('redirect_to') || request('type') === 'receivable') ? 'true' : 'false' }},
                amount: '',
                type: config.type,
                customers: config.customersList || [],
                selectedCustomer: config.initialCustomer || '',
                customerQuery: '',
                customerDropdownOpen: false,

                init() {
                    this.amount = this.formatNumber(config.amount);
                    this.$watch('amount', (value) => {
                        this.amount = this.formatNumber(value);
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
                },

                get selectedCustomerData() {
                    if (!this.selectedCustomer) return null;
                    return this.customers.find(c => c.id == this.selectedCustomer) || null;
                },
                get filteredCustomers() {
                    if (!this.customerQuery.trim()) return [];
                    const q = this.customerQuery.trim().toLowerCase();
                    return this.customers.filter(c =>
                        (c.name && c.name.toLowerCase().includes(q)) ||
                        (c.phone && c.phone.includes(q)) ||
                        (c.email && c.email.toLowerCase().includes(q)) ||
                        (c.username && c.username.toLowerCase().includes(q))
                    ).slice(0, 25);
                },
                selectCustomer(c) {
                    this.selectedCustomer = c.id;
                    this.customerDropdownOpen = false;
                    this.customerQuery = '';
                },
                clearCustomer() {
                    this.selectedCustomer = '';
                    this.customerQuery = '';
                    this.customerDropdownOpen = false;
                }
            }));
        });
    </script>
@endpush
@endsection
