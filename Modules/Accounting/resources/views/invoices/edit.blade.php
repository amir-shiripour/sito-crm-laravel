@extends('layouts.user')

@section('title', 'ویرایش فاکتور شماره ' . $invoice->invoice_number)

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

    <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6 py-8" x-data="invoiceForm({
        units: {{ json_encode($units) }},
        roundingMode: '{{ \Modules\Accounting\App\Models\AccountingSetting::getValue('tax.rounding_mode', 'none') }}',
        roundingAmount: {{ \Modules\Accounting\App\Models\AccountingSetting::getValue('tax.rounding_amount', 1000) }}
    })">
        <form action="{{ route('admin.accounting.invoices.update', $invoice) }}" method="POST" @submit.prevent="submitForm($event.target)">
            @csrf
            @method('PUT')
            <div class="space-y-8 pb-24">

                {{-- Page Header --}}
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                            <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </span>
                            ویرایش فاکتور <span class="font-mono text-xl">{{ $invoice->invoice_number }}</span>
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mr-14 max-w-2xl leading-relaxed">
                            اطلاعات این فاکتور را ویرایش کنید.
                        </p>
                    </div>
                </div>

                {{-- Main Info Card --}}
                <div class="{{ $cardClass }}">
                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Client --}}
                        <div class="md:col-span-1">
                            <label for="client_id" class="{{ $labelClass }}">مشتری <span class="text-red-500">*</span></label>
                            <select name="client_id" id="client_id" class="{{ $selectClass }}" required>
                                <option value="">انتخاب کنید...</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" @if(old('client_id', $invoice->client_id) == $client->id) selected @endif>{{ $client->full_name . ' (' . $client->username . ')' }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Invoice Number --}}
                        <div>
                            <label for="invoice_number" class="{{ $labelClass }}">شماره فاکتور</label>
                            <input type="text" name="invoice_number" id="invoice_number" value="{{ old('invoice_number', $invoice->invoice_number) }}" class="{{ $inputClass }}" required>
                            <p class="text-xs text-gray-500 mt-1">ویرایش شماره فاکتور مجاز است.</p>
                        </div>

                        {{-- Issue Date --}}
                        <div>
                            <label for="issue_date" class="{{ $labelClass }}">تاریخ صدور <span class="text-red-500">*</span></label>
                            <input type="text" id="issue_date" name="issue_date" data-jdp value="{{ old('issue_date', $invoice->issue_date ? jdate($invoice->issue_date)->format('Y/m/d') : '') }}" class="{{ $inputClass }} dir-ltr text-center" required>
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
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase min-w-[10rem]">کد کالا</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase min-w-[20rem]">شرح کالا / خدمات</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase min-w-[8rem]">تعداد</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase min-w-[10rem]">واحد</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase min-w-[12rem]">قیمت واحد</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase min-w-[12rem]">تخفیف</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase min-w-[12rem]">مبلغ کل</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <template x-for="(item, index) in formData.items" :key="index">
                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                        <td class="px-2 py-3"><input type="text" :name="`items[${index}][item_code]`" x-model="item.item_code" class="{{ $inputClass }}" placeholder="اختیاری"></td>
                                        <td class="px-2 py-3"><input type="text" :name="`items[${index}][description]`" x-model="item.description" class="{{ $inputClass }}" required></td>
                                        <td class="px-2 py-3"><input type="text" :name="`items[${index}][quantity]`" @input="item.quantity = toEnglishDigits($event.target.value)" :value="item.quantity" class="{{ $inputClass }} text-center" required></td>
                                        <td class="px-2 py-3">
                                            <select :name="`items[${index}][unit_type]`" x-model="item.unit_type" class="{{ $selectClass }}">
                                                <template x-for="unit in units" :key="unit">
                                                    <option :value="unit" x-text="unit"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="px-2 py-3"><input type="text" :name="`items[${index}][unit_price]`" @input="item.unit_price = toEnglishDigits($event.target.value, true)" :value="formatNumber(item.unit_price)" class="{{ $inputClass }} text-left dir-ltr" required></td>
                                        <td class="px-2 py-3"><input type="text" :name="`items[${index}][discount]`" @input="item.discount = toEnglishDigits($event.target.value, true)" :value="formatNumber(item.discount)" class="{{ $inputClass }} text-left dir-ltr"></td>
                                        <td class="px-2 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300" x-text="formatNumber(getRowTotal(index))"></td>
                                        <td class="px-2 py-3 text-center"><button type="button" @click="removeItem(index)" x-show="formData.items.length > 1" class="text-gray-400 hover:text-red-500 p-1 rounded-full transition-colors">&times;</button></td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" @click="addItem()" class="mt-4 inline-flex items-center px-3 py-2 border border-dashed border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-xl text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">+ افزودن ردیف</button>
                    </div>
                </div>

                {{-- Totals and Notes Card --}}
                <div class="{{ $cardClass }}">
                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-x-12 gap-y-6">
                        <div class="md:col-span-2">
                            <label for="notes" class="{{ $labelClass }}">یادداشت‌ها و شرایط</label>
                            <textarea name="notes" id="notes" rows="6" class="{{ $inputClass }}" x-model="formData.notes"></textarea>
                        </div>
                        <div class="space-y-4 bg-gray-50 dark:bg-gray-900/50 p-5 rounded-2xl border border-gray-100 dark:border-gray-700">
                            {{-- جمع کل --}}
                            <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-3">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">جمع کل ردیف‌ها:</span>
                                <span class="text-base font-bold text-gray-800 dark:text-gray-100" x-text="formatNumber(grandTotal.subtotal)"></span>
                            </div>

                            {{-- تخفیف --}}
                            <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-3">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">جمع تخفیف‌ها:</span>
                                <span class="text-base font-bold text-red-600 dark:text-red-400" x-text="formatNumber(grandTotal.discount)"></span>
                            </div>

                            {{-- مبلغ مشمول مالیات --}}
                            <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-3">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">مبلغ مشمول مالیات:</span>
                                <span class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="formatNumber(grandTotal.taxableAmount)"></span>
                            </div>

                            {{-- مالیات --}}
                            <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-3">
                                <div class="flex flex-col">
                                    <label for="tax" class="text-sm font-medium text-gray-600 dark:text-gray-400">مالیات (اضافه می‌شود):</label>
                                    <div class="flex items-center gap-2 mt-2">
                                        <input type="text" name="tax" id="tax" @input="formData.tax = toEnglishDigits($event.target.value)" :value="formData.tax" class="w-16 {{ $inputClass }} text-center" placeholder="%">
                                        <span class="text-xs text-gray-500">%</span>
                                    </div>
                                </div>
                                <span class="text-base font-bold text-gray-800 dark:text-gray-100" x-text="formatNumber(grandTotal.taxAmount)"></span>
                            </div>

                            {{-- مبلغ نهایی --}}
                            <div class="flex justify-between items-center pt-2">
                                <span class="text-lg font-black text-gray-900 dark:text-white">مبلغ نهایی فاکتور:</span>
                                <span x-text="formatNumber(grandTotal.total)" class="text-2xl font-black text-indigo-600 dark:text-indigo-400"></span>
                            </div>

                            {{-- Rounding Info --}}
                            <template x-if="grandTotal.roundedAmount !== 0">
                                <div class="flex justify-between items-center text-xs text-gray-500 border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
                                    <span x-text="grandTotal.roundedAmount > 0 ? 'مبلغ گرد شده (اضافه شد):' : 'مبلغ گرد شده (کسر شد):'"></span>
                                    <span x-text="formatNumber(Math.abs(grandTotal.roundedAmount)) + ' ریال'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Sticky Footer --}}
            <div class="sticky bottom-4 z-40 max-w-7xl mx-auto">
                <div class="flex justify-end items-center bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
                    <button type="submit" class="px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        ثبت تغییرات فاکتور
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function invoiceForm(config) {
                let initialItems = {!! json_encode(old('items', $invoice->items->map(function($item) {
                    return [
                        'item_code' => $item->item_code ?? '',
                        'description' => $item->description,
                        'quantity' => $item->quantity + 0,
                        'unit_type' => $item->unit_type ?? 'عدد',
                        'unit_price' => $item->unit_price + 0,
                        'discount' => $item->discount + 0,
                    ];
                })->toArray())) !!};

                if (initialItems.length === 0) {
                    initialItems = [{ item_code: '', description: '', quantity: 1, unit_type: config.units[0] || 'عدد', unit_price: '', discount: 0 }];
                }

                return {
                    formData: {
                        items: initialItems,
                        notes: {!! json_encode(old('notes', $invoice->notes)) !!},
                        tax: {!! json_encode(old('tax', $invoice->tax + 0)) !!},
                    },
                    units: config.units || ['عدد'],
                    roundingMode: config.roundingMode || 'none',
                    roundingAmount: config.roundingAmount || 1000,

                    toEnglishDigits(value, isPrice = false) {
                        if (value === null || typeof value === 'undefined') return '';
                        let strValue = String(value)
                            .replace(/[\u0660-\u0669]/g, c => c.charCodeAt(0) - 0x0660)
                            .replace(/[\u06F0-\u06F9]/g, c => c.charCodeAt(0) - 0x06F0);
                        if (isPrice) strValue = strValue.replace(/,/g, '');
                        return strValue.replace(/[^0-9.]/g, '');
                    },
                    formatNumber(value) {
                        const cleanValue = this.toEnglishDigits(String(value), true);
                        const num = parseFloat(cleanValue);
                        if (isNaN(num)) return '';
                        return num.toLocaleString('en-US', { maximumFractionDigits: 2 });
                    },
                    addItem() { this.formData.items.push({ item_code: '', description: '', quantity: 1, unit_type: this.units[0] || 'عدد', unit_price: '', discount: 0 }); },
                    removeItem(index) { if (this.formData.items.length > 1) this.formData.items.splice(index, 1); },
                    getRowTotal(index) {
                        const item = this.formData.items[index];
                        const quantity = parseFloat(this.toEnglishDigits(item.quantity)) || 0;
                        const unitPrice = parseFloat(this.toEnglishDigits(item.unit_price, true)) || 0;
                        const discount = parseFloat(this.toEnglishDigits(item.discount, true)) || 0;
                        return (quantity * unitPrice) - discount;
                    },
                    roundValue(value) {
                        if (this.roundingMode === 'none' || this.roundingAmount <= 0) return value;
                        const factor = parseFloat(this.roundingAmount);
                        if (this.roundingMode === 'up') {
                            return Math.ceil(value / factor) * factor;
                        } else if (this.roundingMode === 'down') {
                            return Math.floor(value / factor) * factor;
                        }
                        return value;
                    },
                    get grandTotal() {
                        const subtotal = this.formData.items.reduce((acc, item) => {
                            const quantity = parseFloat(this.toEnglishDigits(item.quantity)) || 0;
                            const unitPrice = parseFloat(this.toEnglishDigits(item.unit_price, true)) || 0;
                            return acc + (quantity * unitPrice);
                        }, 0);

                        const discount = this.formData.items.reduce((acc, item) => {
                            return acc + (parseFloat(this.toEnglishDigits(item.discount, true)) || 0);
                        }, 0);

                        const taxPercent = parseFloat(this.toEnglishDigits(this.formData.tax)) || 0;
                        const taxableAmount = subtotal - discount;
                        const taxAmount = (taxableAmount * taxPercent) / 100;
                        const rawTotal = taxableAmount + taxAmount;
                        const total = this.roundValue(rawTotal);
                        const roundedAmount = total - rawTotal;

                        return { subtotal, discount, taxableAmount, taxAmount, total, roundedAmount };
                    },
                    submitForm(form) {
                        form.submit();
                    }
                }
            }
        </script>
    @endpush
@endsection
