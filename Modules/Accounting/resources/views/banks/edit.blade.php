@extends('layouts.user')

@section('title', 'ویرایش حساب بانکی')

@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
    $colors = ['#333333', '#e1a6ad', '#ffd100', '#6558b1', '#00ab84', '#fe0c3b', '#4e91e6'];
@endphp

@section('content')
    <form action="{{ route('admin.accounting.banks.update', $bank) }}" method="POST" x-data="formHandlers()">
        @csrf
        @method('PUT')
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-24">

            {{-- Header --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z" /></svg>
                        </span>
                        ویرایش حساب بانکی
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-14 max-w-2xl leading-relaxed">
                        اطلاعات حساب بانکی <span class="font-bold">{{ $bank->bank_name }}</span> را ویرایش کنید.
                    </p>
                </div>
            </div>

            {{-- Form Card --}}
            <div class="{{ $cardClass }}">
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="bank_name" class="{{ $labelClass }}">نام بانک <span class="text-red-500">*</span></label>
                        <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $bank->bank_name) }}" class="{{ $inputClass }}" required>
                        @error('bank_name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="account_holder_name" class="{{ $labelClass }}">نام صاحب حساب <span class="text-red-500">*</span></label>
                        <input type="text" name="account_holder_name" id="account_holder_name" value="{{ old('account_holder_name', $bank->account_holder_name) }}" class="{{ $inputClass }}" required>
                        @error('account_holder_name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="balance" class="{{ $labelClass }}">موجودی (غیرقابل ویرایش)</label>
                        <input type="text" value="{{ number_format($bank->balance) }}" class="{{ $inputClass }} dir-ltr text-left bg-gray-100 dark:bg-gray-800" readonly>
                        <p class="mt-1 text-xs text-gray-500">موجودی از طریق تراکنش‌ها مدیریت می‌شود.</p>
                    </div>
                    <div>
                        <label for="account_number" class="{{ $labelClass }}">شماره حساب</label>
                        <input type="text" name="account_number" id="account_number" value="{{ old('account_number', $bank->account_number) }}" class="{{ $inputClass }} dir-ltr text-left" @input="sanitizeNumber($el)">
                        @error('account_number')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="card_number" class="{{ $labelClass }}">شماره کارت</label>
                        <input type="text" name="card_number" id="card_number" value="{{ old('card_number', $bank->card_number) }}" maxlength="16" class="{{ $inputClass }} dir-ltr text-left" @input="sanitizeNumber($el)">
                        @error('card_number')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="iban" class="{{ $labelClass }}">شماره شبا (IBAN)</label>
                        <input type="text" name="iban" id="iban" value="{{ old('iban', $bank->iban) }}" maxlength="26" class="{{ $inputClass }} dir-ltr text-left" placeholder="IR123456789012345678901234">
                        @error('iban')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}">رنگ کارت</label>
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="color" x-model="selectedColor">
                            @foreach($colors as $color)
                                <button type="button" @click="selectedColor = '{{ $color }}'"
                                        class="w-8 h-8 rounded-full transition-all transform"
                                        :class="{ 'ring-2 ring-offset-2 dark:ring-offset-gray-800 ring-indigo-500 scale-110': selectedColor === '{{ $color }}' }"
                                        :style="{ backgroundColor: '{{ $color }}' }"></button>
                            @endforeach
                        </div>
                        @error('color')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">وضعیت</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="status" value="0">
                            <input type="checkbox" name="status" value="1" class="sr-only peer" {{ old('status', $bank->status) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">فعال</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Sticky Footer --}}
            <div class="sticky bottom-4 z-40 max-w-4xl mx-auto">
                <div class="flex justify-between items-center bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
                    <a href="{{ route('admin.accounting.banks.index') }}" class="px-6 py-3 rounded-xl text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                        انصراف
                    </a>
                    <button type="submit" class="px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        به‌روزرسانی حساب
                    </button>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        function formHandlers() {
            return {
                selectedColor: '{{ old('color', $bank->color ?? $colors[0]) }}',
                sanitizeNumber(el) {
                    el.value = el.value.replace(/[^0-9]/g, '');
                }
            }
        }
    </script>
    @endpush
@endsection
