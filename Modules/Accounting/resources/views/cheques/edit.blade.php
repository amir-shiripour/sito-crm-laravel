@extends('layouts.user')

@section('title', 'ویرایش چک')

@php
    // Define standard classes for reuse
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
    $selectClass = $inputClass . " appearance-none cursor-pointer";
@endphp

@section('content')
    {{-- Include Jalali Date Picker styles and scripts --}}
    @includeIf('partials.jalali-date-picker')

    <form action="{{ route('admin.accounting.cheques.update', $cheque) }}" method="POST" x-data="chequeForm()">
        @csrf
        @method('PUT')
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-24">

            {{-- Page Header --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z" /></svg>
                        </span>
                        ویرایش چک
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-14 max-w-2xl leading-relaxed">
                        اطلاعات چک را در این فرم ویرایش کنید.
                    </p>
                </div>
            </div>

            {{-- Form Card --}}
            <div class="{{ $cardClass }}">
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Type --}}
                    <div>
                        <label for="type" class="{{ $labelClass }}">نوع چک <span class="text-red-500">*</span></label>
                        <select name="type" id="type" class="{{ $selectClass }}" required>
                            <option value="received" {{ old('type', $cheque->type) == 'received' ? 'selected' : '' }}>چک دریافتی</option>
                            <option value="issued" {{ old('type', $cheque->type) == 'issued' ? 'selected' : '' }}>چک پرداختی</option>
                        </select>
                        @error('type')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Amount --}}
                    <div>
                        <label for="amount" class="{{ $labelClass }}">مبلغ چک <span class="text-red-500">*</span></label>
                        <input type="text" name="amount" id="amount" value="{{ old('amount', number_format($cheque->amount, 0, '.', '')) }}" class="{{ $inputClass }} dir-ltr text-left" @input="formatAmount($el)" required>
                        @error('amount')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Bank Name --}}
                    <div>
                        <label for="bank_name" class="{{ $labelClass }}">نام بانک <span class="text-red-500">*</span></label>
                        <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $cheque->bank_name) }}" class="{{ $inputClass }}" required>
                        @error('bank_name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Branch Name --}}
                    <div>
                        <label for="branch_name" class="{{ $labelClass }}">نام شعبه (اختیاری)</label>
                        <input type="text" name="branch_name" id="branch_name" value="{{ old('branch_name') }}" class="{{ $inputClass }}">
                        @error('branch_name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Payee Name --}}
                    <div class="md:col-span-2">
                        <label for="payee_name" class="{{ $labelClass }}">نام دریافت کننده (در وجه) <span class="text-red-500">*</span></label>
                        <input type="text" name="payee_name" id="payee_name" value="{{ old('payee_name', $cheque->payee_name) }}" class="{{ $inputClass }}" required>
                        @error('payee_name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Cheque Number --}}
                    <div>
                        <label for="cheque_number" class="{{ $labelClass }}">شماره چک <span class="text-red-500">*</span></label>
                        <input type="text" name="cheque_number" id="cheque_number" value="{{ old('cheque_number', $cheque->cheque_number) }}" class="{{ $inputClass }} dir-ltr text-left">
                        @error('cheque_number')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Sayyad ID --}}
                    <div>
                        <label for="sayyad_id" class="{{ $labelClass }}">شناسه صیادی (۱۶ رقم)</label>
                        <input type="text" name="sayyad_id" id="sayyad_id" value="{{ old('sayyad_id', $cheque->sayyad_id) }}" class="{{ $inputClass }} dir-ltr text-left" maxlength="16">
                        @error('sayyad_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Issue Date --}}
                    <div>
                        <label for="issue_date" class="{{ $labelClass }}">تاریخ صدور <span class="text-red-500">*</span></label>
                        <input type="text" name="issue_date" id="issue_date" data-jdp value="{{ old('issue_date', jdate($cheque->issue_date)->format('Y/m/d')) }}" class="{{ $inputClass }} dir-ltr text-center" required>
                        @error('issue_date')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Due Date --}}
                    <div>
                        <label for="due_date" class="{{ $labelClass }}">تاریخ سررسید <span class="text-red-500">*</span></label>
                        <input type="text" name="due_date" id="due_date" data-jdp value="{{ old('due_date', jdate($cheque->due_date)->format('Y/m/d')) }}" class="{{ $inputClass }} dir-ltr text-center" required>
                        @error('due_date')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Client --}}
                    <div class="md:col-span-1">
                        <label for="client_id" class="{{ $labelClass }}">مربوط به مشتری (اختیاری)</label>
                        <select name="client_id" id="client_id" class="{{ $selectClass }}">
                            <option value="">هیچکدام</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id', $cheque->client_id) == $client->id ? 'selected' : '' }}>
                                    {{ $client->full_name ?: $client->username }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Bank Account --}}
                    <div class="md:col-span-1">
                        <label for="bank_id" class="{{ $labelClass }}">حساب بانکی مرتبط (اختیاری)</label>
                        <select name="bank_id" id="bank_id" class="{{ $selectClass }}">
                            <option value="">هیچکدام</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->id }}" {{ old('bank_id', $cheque->bank_id) == $bank->id ? 'selected' : '' }}>
                                    {{ $bank->bank_name }} - {{ $bank->account_number }}
                                </option>
                            @endforeach
                        </select>
                        @error('bank_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Status --}}
                    <div class="md:col-span-2">
                        <label for="status" class="{{ $labelClass }}">وضعیت چک <span class="text-red-500">*</span></label>
                        <select name="status" id="status" class="{{ $selectClass }}" required>
                            <option value="registered" {{ old('status', $cheque->status) == 'registered' ? 'selected' : '' }}>ثبت شده</option>
                            <option value="issued" {{ old('status', $cheque->status) == 'issued' ? 'selected' : '' }}>صادر شده</option>
                            <option value="passed" {{ old('status', $cheque->status) == 'passed' ? 'selected' : '' }}>وصول شده</option>
                            <option value="returned" {{ old('status', $cheque->status) == 'returned' ? 'selected' : '' }}>برگشت خورده</option>
                        </select>
                        @error('status')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Description --}}
                    <div class="md:col-span-2">
                        <label for="description" class="{{ $labelClass }}">توضیحات (اختیاری)</label>
                        <textarea name="description" id="description" rows="3" class="{{ $inputClass }}">{{ old('description', $cheque->description) }}</textarea>
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
                    <button type="submit" class="px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        ذخیره تغییرات
                    </button>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        {{-- TomSelect for better dropdowns --}}
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Initialize TomSelect for the client dropdown
                if (document.getElementById('client_id')) {
                    new TomSelect('#client_id', {
                        sortField: { field: "text", direction: "asc" }
                    });
                }
                if (document.getElementById('bank_id')) {
                    new TomSelect('#bank_id', {
                        sortField: { field: "text", direction: "asc" }
                    });
                }
            });

            function chequeForm() {
                return {
                    init() {
                        // Format amount on initial load
                        this.formatAmount(document.getElementById('amount'));
                    },
                    /**
                     * Converts Persian/Arabic digits to English.
                     */
                    toEnglishDigits(str) {
                        if (!str) return '';
                        const persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                        const arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
                        const english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

                        return str.toString()
                                  .replace(new RegExp(persian.join('|'), 'g'), c => english[persian.indexOf(c)])
                                  .replace(new RegExp(arabic.join('|'), 'g'), c => english[arabic.indexOf(c)]);
                    },

                    /**
                     * Formats the amount input with commas and ensures English digits.
                     */
                    formatAmount(el) {
                        // 1. Convert all digits to English and remove non-numeric characters
                        let value = this.toEnglishDigits(el.value);
                        let numericValue = value.replace(/[^0-9]/g, '');

                        // 2. Format with commas
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
