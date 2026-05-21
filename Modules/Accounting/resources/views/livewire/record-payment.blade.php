@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/30 rounded-t-2xl";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
    $selectClass = $inputClass . " appearance-none cursor-pointer";
@endphp

<form wire:submit.prevent="savePayment">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-24">

        {{-- Global Error Display --}}
        @if ($errors->any())
            <div class="rounded-2xl bg-red-50 p-4 border border-red-100 dark:bg-red-900/10 dark:border-red-800/30 text-red-700 dark:text-red-400 text-sm font-medium flex items-start gap-3 animate-in fade-in slide-in-from-top-2 shadow-sm">
                <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-800/30 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <ul class="list-disc list-inside space-y-1 mt-1.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Invoice Summary Card --}}
        <div class="{{ $cardClass }}">
            <div class="{{ $headerClass }}">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">خلاصه صورت‌حساب #{{ $invoice->invoice_number }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">مشتری: {{ $invoice->client->full_name ?? $invoice->client->username }}</p>
                </div>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 dark:text-gray-400">مبلغ کل فاکتور:</span>
                    <span class="font-bold text-gray-800 dark:text-gray-200 dir-ltr">{{ number_format($invoice->total_amount) }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 dark:text-gray-400">مبلغ پرداخت شده:</span>
                    <span class="font-bold text-green-600 dark:text-green-400 dir-ltr">{{ number_format($invoice->transactions->sum('amount')) }}</span>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 pt-3 flex justify-between items-center text-lg">
                    <span class="font-bold text-gray-900 dark:text-white">مبلغ قابل پرداخت:</span>
                    <span class="font-bold text-indigo-600 dark:text-indigo-400 dir-ltr">{{ number_format($invoice->total_amount - $invoice->transactions->sum('amount')) }}</span>
                </div>
            </div>
        </div>

        {{-- Payment Form Card --}}
        <div class="{{ $cardClass }}">
            <div class="{{ $headerClass }}">
                <div class="w-8 h-8 rounded-lg bg-green-50 dark:bg-green-900/20 flex items-center justify-center text-green-600 dark:text-green-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01" /></svg>
                </div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">ثبت پرداختی جدید</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="amount" class="{{ $labelClass }}">مبلغ پرداخت</label>
                    <input type="text" id="amount" class="{{ $inputClass }} dir-ltr text-left"
                           x-data="{ value: @entangle('amount') }"
                           x-init="$el.value = formatNumber(value)"
                           @input="value = unformatNumber(toEnglishDigits($event.target.value))"
                           @blur="$el.value = formatNumber(value)">
                    @error('amount') <span class="text-red-500 text-xs mt-2">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="bank_id" class="{{ $labelClass }}">واریز به بانک</label>
                    <select id="bank_id" wire:model.lazy="bank_id" class="{{ $selectClass }}">
                        <option value="">انتخاب کنید...</option>
                        @foreach($banks as $bank)
                            <option value="{{ $bank->id }}">{{ $bank->display_info }}</option>
                        @endforeach
                    </select>
                    @error('bank_id') <span class="text-red-500 text-xs mt-2">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="payment_method" class="{{ $labelClass }}">روش پرداخت</label>
                    <select id="payment_method" wire:model.lazy="payment_method" class="{{ $selectClass }}">
                        @foreach($paymentMethods as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('payment_method') <span class="text-red-500 text-xs mt-2">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="transaction_date" class="{{ $labelClass }}">تاریخ تراکنش</label>
                    <input type="text" id="transaction_date" data-jdp wire:model.lazy="transaction_date" class="{{ $inputClass }} dir-ltr text-center">
                    @error('transaction_date') <span class="text-red-500 text-xs mt-2">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2">
                    <label for="reference_code" class="{{ $labelClass }}">کد رهگیری / شماره چک</label>
                    <input type="text" id="reference_code" wire:model.lazy="reference_code" class="{{ $inputClass }} dir-ltr text-left">
                    @error('reference_code') <span class="text-red-500 text-xs mt-2">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2">
                    <label for="description" class="{{ $labelClass }}">توضیحات (اختیاری)</label>
                    <textarea id="description" wire:model.lazy="description" rows="3" class="{{ $inputClass }}"></textarea>
                    @error('description') <span class="text-red-500 text-xs mt-2">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Sticky Footer --}}
    <div class="sticky bottom-4 z-40 max-w-3xl mx-auto">
        <div class="flex justify-end items-center bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
            <button type="submit"
                    class="px-8 py-3 rounded-xl bg-green-600 text-white font-bold shadow-lg shadow-green-500/30 hover:bg-green-700 hover:shadow-green-500/50 transition-all transform active:scale-95 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                ثبت پرداخت
                <div wire:loading wire:target="savePayment" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
            </button>
        </div>
    </div>
</form>

@push('scripts')
    @includeIf('partials.jalali-date-picker')
    <script>
        function toEnglishDigits(str) {
            if (!str) return '';
            return String(str)
                .replace(/[\u0660-\u0669]/g, c => c.charCodeAt(0) - 0x0660)
                .replace(/[\u06F0-\u06F9]/g, c => c.charCodeAt(0) - 0x06F0);
        }

        function formatNumber(value) {
            if (!value) return '';
            let num = String(value).replace(/[^0-9.]/g, '');
            let parts = num.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return parts.join('.');
        }

        function unformatNumber(value) {
            if (!value) return '';
            return String(value).replace(/,/g, '');
        }
    </script>
@endpush
