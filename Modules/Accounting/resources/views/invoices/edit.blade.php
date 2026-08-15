@extends('layouts.user')

@section('title', 'ویرایش فاکتور ' . $invoice->invoice_number)

@php
    $cardClass = "bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-3xl border border-gray-100 dark:border-gray-800 shadow-xl";
    $labelClass = "block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
@endphp

@section('content')
@includeIf('partials.jalali-date-picker')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="invoiceForm({
    items: {!! json_encode(old('items', $invoice->items->map(function($item) {
        return ['name' => $item->description, 'quantity' => (float)$item->quantity, 'price' => (float)$item->unit_price, 'unit_type' => $item->unit_type];
    })->toArray())) !!},
    discount: {{ old('discount', (float)$invoice->discount ?? 0) }},
    tax: {{ old('tax', (float)$invoice->tax ?? 0) }},
    tax_percentage: {{ $settings->get('tax.enabled') ? $settings->get('tax.percentage', 0) : 0 }},
    rounding_mode: '{{ $settings->get('tax.rounding_mode', 'none') }}',
    rounding_amount: {{ $settings->get('tax.rounding_amount', 1000) }},
    units: {{ json_encode($units) }},
    currency: '{{ $currency }}'
})">
    <form action="{{ route('admin.accounting.invoices.update', $invoice) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="space-y-8">
            {{-- Header --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">ویرایش فاکتور <span class="font-mono text-xl">#{{ $invoice->invoice_number }}</span></h1>
            </div>

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 rounded-xl shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="mr-3">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-300">خطا در ثبت اطلاعات</h3>
                            <div class="mt-2 text-sm text-red-700 dark:text-red-400">
                                <ul role="list" class="list-disc pr-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Main Info Card --}}
            <div class="{{ $cardClass }}">
                <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label for="invoice_number" class="{{ $labelClass }}">شماره فاکتور</label>
                        <input type="text" name="invoice_number" id="invoice_number" value="{{ old('invoice_number', $invoice->invoice_number) }}"
                               class="{{ $inputClass }} dir-ltr text-center"
                               @if($settings->get('numbering.mode') === 'auto') readonly @endif>
                    </div>
                    <div>
                        <label for="client_id" class="{{ $labelClass }}">مشتری <span class="text-red-500">*</span></label>
                        <select name="client_id" id="client_id" class="{{ $inputClass }}" required>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('client_id', $invoice->client_id) == $customer->id)>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="issue_date" class="{{ $labelClass }}">تاریخ صدور <span class="text-red-500">*</span></label>
                        <input type="text" id="issue_date" name="issue_date" value="{{ old('issue_date', $invoice->issue_date->format('Y/m/d')) }}" data-jdp class="{{ $inputClass }} dir-ltr text-center" required>
                    </div>
                    <div>
                        <label for="due_date" class="{{ $labelClass }}">تاریخ سررسید <span class="text-red-500">*</span></label>
                        <input type="text" id="due_date" name="due_date" value="{{ old('due_date', $invoice->due_date->format('Y/m/d')) }}" data-jdp class="{{ $inputClass }} dir-ltr text-center" required>
                    </div>
                </div>
            </div>

            {{-- Items Card --}}
            <div class="{{ $cardClass }}">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">اقلام فاکتور</h3>
                    <div class="overflow-x-auto -mx-6">
                        <table class="min-w-full">
                            <thead class="border-b border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase min-w-[20rem]">شرح</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase min-w-[8rem]">تعداد</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase min-w-[8rem]">واحد</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase min-w-[12rem]">قیمت واحد</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase min-w-[12rem]">مبلغ کل</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                        <td class="px-2 py-3"><input type="text" x-model="item.name" class="{{ $inputClass }}" required></td>
                                        <td class="px-2 py-3"><input type="number" x-model.number="item.quantity" @input="calculateTotals" class="{{ $inputClass }} text-center" min="1" required></td>
                                        <td class="px-2 py-3">
                                            <select x-model="item.unit_type" class="{{ $inputClass }}">
                                                <template x-for="unit in units" :key="unit">
                                                    <option :value="unit" x-text="unit"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="px-2 py-3"><input type="number" x-model.number="item.price" @input="calculateTotals" class="{{ $inputClass }} text-left dir-ltr" min="0" required></td>
                                        <td class="px-2 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300" x-text="formatCurrency(item.quantity * item.price)"></td>
                                        <td class="px-2 py-3 text-center">
                                            <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-gray-400 hover:text-red-500 p-1 rounded-full transition-colors">&times;</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" @click="addItem()" class="mt-4 inline-flex items-center px-3 py-2 border border-dashed border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-xl text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">+ افزودن ردیف</button>
                    <input type="hidden" name="items" :value="JSON.stringify(items.map(i => ({...i, total: i.quantity * i.price})))">
                </div>
            </div>

            {{-- Notes and Totals --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 {{ $cardClass }}">
                    <div class="p-6">
                        <label for="notes" class="{{ $labelClass }}">یادداشت‌ها</label>
                        <textarea name="notes" id="notes" rows="8" class="{{ $inputClass }}">{{ old('notes', $invoice->notes) }}</textarea>
                    </div>
                </div>
                <div class="{{ $cardClass }}">
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">جمع جزء:</span>
                            <span class="text-base font-bold text-gray-800 dark:text-gray-100" x-text="formatCurrency(subtotal) + ' ' + currency"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <label for="discount" class="text-sm font-medium text-gray-600 dark:text-gray-400">تخفیف:</label>
                            <input type="number" name="discount" id="discount" x-model.number="discount" @input="calculateTotals" class="w-32 {{ $inputClass }} text-left dir-ltr" min="0">
                        </div>
                        <div class="flex justify-between items-center">
                            <label for="tax" class="text-sm font-medium text-gray-600 dark:text-gray-400">مالیات:</label>
                            <input type="number" name="tax" id="tax" x-model.number="tax" @input="calculateTotals" class="w-32 {{ $inputClass }} text-left dir-ltr" min="0">
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 flex justify-between items-center">
                            <span class="text-lg font-black text-gray-900 dark:text-white">مبلغ نهایی:</span>
                            <span x-text="formatCurrency(total) + ' ' + currency" class="text-2xl font-black text-indigo-600 dark:text-indigo-400"></span>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="subtotal" :value="subtotal">
            <input type="hidden" name="total" :value="total">

            {{-- Submit Button --}}
            <div class="flex justify-end">
                <button type="submit" class="px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    ذخیره تغییرات
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function invoiceForm(data) {
        return {
            items: data.items,
            subtotal: 0,
            discount: data.discount,
            tax_percentage: data.tax_percentage,
            tax_amount: data.tax,
            total: 0,
            units: data.units,
            currency: data.currency,
            rounding_mode: data.rounding_mode,
            rounding_amount: data.rounding_amount,

            init() { this.calculateTotals(); },
            addItem() { this.items.push({ name: '', quantity: 1, price: 0, unit_type: this.units[0] || '' }); },
            removeItem(index) { if (this.items.length > 1) this.items.splice(index, 1); this.calculateTotals(); },
            calculateTotals() {
                let sub = 0;
                this.items.forEach(item => { sub += (parseFloat(item.quantity) || 0) * (parseFloat(item.price) || 0); });
                this.subtotal = sub;

                let taxableAmount = this.subtotal - (this.discount || 0);
                this.tax_amount = (taxableAmount * this.tax_percentage) / 100;

                let rawTotal = taxableAmount + this.tax_amount;
                this.total = this.roundValue(rawTotal);
            },
            roundValue(value) {
                if (this.rounding_mode === 'none' || this.rounding_amount <= 0) return value;
                const factor = parseFloat(this.rounding_amount);
                if (this.rounding_mode === 'up') {
                    return Math.ceil(value / factor) * factor;
                } else if (this.rounding_mode === 'down') {
                    return Math.floor(value / factor) * factor;
                }
                return value;
            },
            formatCurrency(amount) { return new Intl.NumberFormat('fa-IR').format(amount); }
        }
    }
</script>
@endsection
