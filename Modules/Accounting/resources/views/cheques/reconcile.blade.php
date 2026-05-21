@extends('layouts.user')

@section('title', 'تعیین وضعیت چک')

@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
    $selectClass = $inputClass . " appearance-none cursor-pointer";

    // آماده‌سازی داده‌های بانک‌ها برای استفاده در Alpine.js
    $mappedBanks = $banks->mapWithKeys(fn($bank) => [$bank->id => $bank->balance]);
@endphp

@section('content')
    @includeIf('partials.jalali-date-picker')

    <form action="{{ route('admin.accounting.cheques.reconcile.process', $cheque) }}" method="POST" x-data="reconcileForm()">
        @csrf
        @method('PUT')
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-24">

            {{-- Page Header --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-500/30">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </span>
                        تعیین وضعیت چک
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-14 max-w-2xl leading-relaxed">
                        وضعیت نهایی چک به شماره <span class="font-bold text-gray-800 dark:text-gray-200">{{ $cheque->cheque_number }}</span> را مشخص کنید.
                    </p>
                </div>
            </div>

            {{-- Form Card --}}
            <div class="{{ $cardClass }}">
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- New Status --}}
                    <div>
                        <label for="status" class="{{ $labelClass }}">وضعیت جدید <span class="text-red-500">*</span></label>
                        <select name="status" id="status" class="{{ $selectClass }}" required>
                            <option value="passed">وصول شده</option>
                            <option value="returned">برگشت خورده</option>
                        </select>
                        @error('status')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Bank Account --}}
                    <div>
                        <label for="bank_id" class="{{ $labelClass }}">
                            @if($cheque->type === 'received')
                                واریز به بانک <span class="text-red-500">*</span>
                            @else
                                برداشت از بانک <span class="text-red-500">*</span>
                            @endif
                        </label>
                        <select name="bank_id" id="bank_id" class="{{ $selectClass }}" x-model="selectedBankId" @change="updateSelectedBalance()" required>
                            <option value="">انتخاب کنید...</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->id }}" {{ old('bank_id') == $bank->id ? 'selected' : '' }}>{{ $bank->bank_name }} - ({{ $bank->account_number }})</option>
                            @endforeach
                        </select>
                        @error('bank_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

                        <div x-show="selectedBankBalance !== null" class="mt-2 text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 p-2 rounded-lg inline-block">
                            موجودی فعلی: <span x-text="formatBalanceText(selectedBankBalance)" class="font-bold"></span> تومان
                        </div>
                    </div>

                    {{-- Reconciliation Date --}}
                    <div class="md:col-span-2">
                        <label for="reconciliation_date" class="{{ $labelClass }}">تاریخ وصول / برگشت <span class="text-red-500">*</span></label>
                        <input type="text" name="reconciliation_date" id="reconciliation_date" data-jdp value="{{ old('reconciliation_date', jdate()->format('Y/m/d')) }}" class="{{ $inputClass }} dir-ltr text-center" required>
                        @error('reconciliation_date')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Description --}}
                    <div class="md:col-span-2">
                        <label for="description" class="{{ $labelClass }}">توضیحات (اختیاری)</label>
                        <textarea name="description" id="description" rows="3" class="{{ $inputClass }}">{{ old('description') }}</textarea>
                        @error('description')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Sticky Footer with Action Buttons --}}
            <div class="sticky bottom-4 z-40 max-w-4xl mx-auto">
                <div class="flex justify-between items-center bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
                    <a href="{{ route('admin.accounting.cheques.index') }}" class="px-6 py-3 rounded-xl text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                        انصراف
                    </a>
                    <button type="submit" class="px-8 py-3 rounded-xl bg-blue-600 text-white font-bold shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
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
                        return parseFloat(balance).toLocaleString('en-US');
                    }
                }
            }
        </script>
    @endpush
@endsection
