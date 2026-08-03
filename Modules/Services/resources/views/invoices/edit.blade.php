@php
    $isProforma = !$invoice->invoice_number;
@endphp
@extends('layouts.user')
@section('title', 'ویرایش ' . ($isProforma ? 'پیش فاکتور #' : 'فاکتور #') . ($invoice->invoice_number ?: $invoice->proforma_invoice_number))

@include('partials.jalali-date-picker')

@php
    use Modules\Services\App\Http\Models\Status;
    use Morilog\Jalali\Jalalian;

    $customersListForJs = $customers->map(function ($c) {
        return [
            'id'       => $c->id,
            'name'     => $c->full_name,
            'email'    => $c->email,
            'phone'    => $c->phone ?? '',
            'username' => $c->username ?? '',
            'label'    => $c->full_name . ' - ' . ($c->email ?? $c->phone ?? ''),
        ];
    })->values();

    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-sm dark:border-gray-700 dark:bg-gray-900/50 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1";
    $cardClass  = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl";

    $currency      = $currency ?? 'toman';
    $currencyLabel = $currency === 'rial' ? 'ریال' : 'تومان';

    $marketAttributesForJs = collect($marketAttributes ?? [])->map(function ($attr) {
        return [
            'id' => $attr->id,
            'name' => $attr->name,
            'values' => $attr->values->map(function ($v) {
                return ['id' => $v->id, 'value' => $v->value];
            })->values(),
        ];
    })->values();

    $invoiceAuto = !empty($settings['services_invoice_auto_numbering']) || !empty($settings['services_invoice_auto']);
    $proformaAuto = !empty($settings['services_proforma_auto_numbering']) || !empty($settings['services_proforma_auto']);

    $existingItems = $invoice->items->map(function ($item) use ($services, $products, $defaultTaxRate) {
        $service = $services->firstWhere('id', $item->service_id);
        $meta = is_array($item->meta) ? $item->meta : json_decode($item->meta, true);
        if (!is_array($meta)) $meta = [];

        $isProduct = !empty($meta['product_id']) || (isset($meta['type']) && $meta['type'] === 'product');
        $mode = $item->service_id ? 'service' : ($isProduct ? 'product' : 'manual');

        $stock = null;
        if ($isProduct && !empty($products)) {
            $prodMatch = collect($products)->first(function($p) use ($meta) {
                if (!empty($meta['product_variant_id'])) {
                    return (string)($p['variant_id'] ?? '') === (string)$meta['product_variant_id'];
                }
                if (!empty($meta['product_id'])) {
                    return (string)($p['master_id'] ?? $p['id'] ?? '') === (string)$meta['product_id'];
                }
                return false;
            });
            if ($prodMatch) {
                $stock = isset($prodMatch['stock']) ? (int)$prodMatch['stock'] : null;
            }
        }

        $customFieldsArray = [];
        $serviceRawArray = null;
        if ($service) {
            $serviceRawArray = $service->toArray();
            $customFieldsArray = $service->customFields
                ->filter(function($f) { return $f->show_in_invoice === true || $f->show_in_invoice === 1 || $f->show_in_invoice === '1' || $f->show_in_invoice === null; })
                ->values()
                ->map(function($f) {
                    if (is_string($f->options)) {
                        try { $f->options = json_decode($f->options, true); } catch (\Exception $e) { $f->options = []; }
                    }
                    if (!is_array($f->options)) $f->options = [];
                    return $f;
                })
                ->toArray();
        }

        return [
            'mode' => $mode,
            'service_id'  => $item->service_id ? (string) $item->service_id : '',
            'product_id'  => isset($meta['product_id']) ? (string) $meta['product_id'] : '',
            'product_variant_id' => isset($meta['product_variant_id']) ? (string) $meta['product_variant_id'] : '',
            'stock'       => $stock,
            'service_raw' => $serviceRawArray,
            'custom_service_name' => $service ? $service->name : ($item->custom_service_name ?: $item->description ?? ''),
            '_showServiceDropdown' => false,
            '_showProductDropdown' => false,
            '_selectedGroup' => '',
            'description' => $item->description,
            'unit'        => $item->unit ?? 'عدد',
            'quantity'    => (float) $item->quantity,
            'unit_price'  => (float) $item->unit_price,
            'discount'    => (float) $item->discount,
            'billing_period' => $meta['billing_period'] ?? null,
            '_priceUnlocked' => false,
            'service_custom_fields' => $customFieldsArray,
            'custom_field_values' => $meta['custom_fields'] ?? [],
            '_showCustomFields' => false,
            'custom_field_custom_prices' => $meta['custom_fields_prices'] ?? [],
            'custom_field_custom_discounts' => $meta['custom_fields_discounts'] ?? [],
            'custom_field_tax_percents' => $meta['custom_fields_taxes'] ?? [],
            'tax_percent' => $item->tax_percent > 0 ? (float) $item->tax_percent : ($defaultTaxRate ?? 9),
            '_taxUnlocked' => false,
        ];
    })->values();

    $toJalaliDateStr = function ($date) {
        if (!$date) return '';
        $dateStr = substr((string)$date, 0, 10);
        $dateNorm = str_replace('/', '-', $dateStr);
        if (str_starts_with($dateNorm, '13') || str_starts_with($dateNorm, '14')) {
            return str_replace('-', '/', $dateNorm);
        }
        try {
            $carbon = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
            return Jalalian::fromCarbon($carbon)->format('Y/m/d');
        } catch (\Exception $e) {
            return '';
        }
    };

    $todayJalaliStr = Jalalian::now()->format('Y/m/d');

    $issueDateValue = old('issue_date', $invoice->issue_date ? $toJalaliDateStr($invoice->issue_date) : $todayJalaliStr);
    $dueDateValue   = old('due_date', $invoice->due_date ? $toJalaliDateStr($invoice->due_date) : $issueDateValue);

@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="invoiceCreator()">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 text-white shadow-lg shadow-amber-500/30">
                     <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path
                             stroke-linecap="round" stroke-linejoin="round"
                             d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </span>
                ویرایش {{ $isProforma ? 'پیش فاکتور' : 'فاکتور' }}
                <span
                    class="text-amber-600 dark:text-amber-400 font-black tabular-nums">#{{ $invoice->invoice_number ?: $invoice->proforma_invoice_number }}</span>
            </h1>
            <a href="{{ route('services.invoices.show', $invoice) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت
            </a>
        </div>

        @if($errors->any())
            <div
                class="p-5 text-sm text-red-800 rounded-2xl bg-red-50 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20 flex items-start gap-4 shadow-sm">
                <span class="bg-red-100 text-red-600 dark:bg-red-500/20 p-2 rounded-full shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path
                            stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
                <div>
                    <p class="font-black text-base mb-2">خطا در ثبت اطلاعات!</p>
                    <ul class="list-disc ps-5 space-y-1.5 marker:text-red-400">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form id="invoiceForm" @submit="onSubmitCheck($event)"
              action="{{ route('services.invoices.update', $invoice) }}" method="POST" enctype="multipart/form-data"
              class="space-y-8">
            @csrf
            @method('PUT')
            <input type="hidden" name="invoice_type" value="{{ $isProforma ? 'proforma' : 'invoice' }}">

            {{-- اطلاعات فاکتور و مشتری --}}
            <div class="{{ $cardClass }} overflow-visible relative z-30">
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl">
                    <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                        <div
                            class="p-2 bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        اطلاعات {{ $isProforma ? 'پیش فاکتور' : 'فاکتور' }} و مشتری
                    </h2>
                </div>

                <div class="p-6 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            @if($isProforma)
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-200">شماره پیش
                                        فاکتور <span class="text-rose-500 font-black">*</span></label>
                                    <template x-if="proformaInvoiceAuto"><span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-[10px] font-black text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/30 dark:text-emerald-400 shadow-sm"><svg
                                                class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round"
                                                                                               stroke-linejoin="round"
                                                                                               d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>تولید خودکار</span>
                                    </template>
                                </div>
                                <input type="text" name="proforma_invoice_number" required
                                       class="{{ $inputClass }} text-start font-medium tabular-nums transition-all duration-300"
                                       :class="proformaInvoiceAuto ? 'bg-gray-100/80 border-gray-300/50 text-gray-500 cursor-not-allowed shadow-inner dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-400 focus:ring-0 focus:border-gray-300 dark:focus:border-gray-700' : ''"
                                       placeholder="مثال: PI-1403001" dir="ltr" x-model="proformaInvoiceNumber"
                                       :readonly="proformaInvoiceAuto">
                            @else
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-200">شماره فاکتور
                                        <span class="text-rose-500 font-black">*</span></label>
                                    <template x-if="invoiceAuto"><span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-[10px] font-black text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/30 dark:text-emerald-400 shadow-sm"><svg
                                                class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round"
                                                                                               stroke-linejoin="round"
                                                                                               d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>تولید خودکار</span>
                                    </template>
                                </div>
                                <input type="text" name="invoice_number" required
                                       class="{{ $inputClass }} text-start font-medium tabular-nums transition-all duration-300"
                                       :class="invoiceAuto ? 'bg-gray-100/80 border-gray-300/50 text-gray-500 cursor-not-allowed shadow-inner dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-400 focus:ring-0 focus:border-gray-300 dark:focus:border-gray-700' : ''"
                                       placeholder="مثال: INV-1403001" dir="ltr" x-model="invoiceNumber"
                                       :readonly="invoiceAuto">
                            @endif
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">تاریخ صدور <span class="text-rose-500 font-black">*</span></label>
                            <div class="relative">
                                <input type="text" name="issue_date" x-model="issueDate"
                                       @change="issueDate = $event.target.value; checkDateValidity()" data-jdp
                                       data-jdp-only-date
                                       class="{{ $inputClass }} cursor-pointer focus:ring-amber-500/20 focus:border-amber-500"
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
                            <label class="{{ $labelClass }}">تاریخ سررسید</label>
                            <div class="relative">
                                <input type="text" name="due_date" x-model="dueDate"
                                       @change="dueDate = $event.target.value; checkDateValidity()"
                                       :data-jdp-min-date="issueDate || 'today'" data-jdp data-jdp-only-date
                                       class="{{ $inputClass }} cursor-pointer focus:ring-amber-500/20 focus:border-amber-500"
                                       placeholder="انتخاب تاریخ سررسید" autocomplete="off" readonly>
                                <svg
                                    class="w-5 h-5 absolute end-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex items-center gap-2 mt-3" x-show="issueDate" x-transition>
                                <button type="button" @click="setDueDate('week')"
                                        class="flex-1 py-1.5 text-[11px] font-bold rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white border border-indigo-100 transition-all shadow-sm dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20 dark:hover:bg-indigo-500 dark:hover:text-white active:scale-95">
                                    ۱ هفته بعد
                                </button>
                                <button type="button" @click="setDueDate('month')"
                                        class="flex-1 py-1.5 text-[11px] font-bold rounded-xl bg-violet-50 text-violet-600 hover:bg-violet-600 hover:text-white border border-violet-100 transition-all shadow-sm dark:bg-violet-500/10 dark:text-violet-400 dark:border-violet-500/20 dark:hover:bg-violet-500 dark:hover:text-white active:scale-95">
                                    ۱ ماه بعد
                                </button>
                                <button type="button" @click="setDueDate('year')"
                                        class="flex-1 py-1.5 text-[11px] font-bold rounded-xl bg-fuchsia-50 text-fuchsia-600 hover:bg-fuchsia-600 hover:text-white border border-fuchsia-100 transition-all shadow-sm dark:bg-fuchsia-500/10 dark:text-fuchsia-400 dark:border-fuchsia-500/20 dark:hover:bg-fuchsia-500 dark:hover:text-white active:scale-95">
                                    ۱ سال بعد
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- انتخاب مشتری --}}
                    <div class="pt-8 border-t border-dashed border-gray-200 dark:border-gray-700">
                        <label class="{{ $labelClass }}">انتخاب مشتری <span
                                class="text-rose-500 font-black">*</span></label>
                        <input type="hidden" name="customer_id" :value="selectedCustomer">
                        <input type="hidden" name="client_name" :value="selectedCustomerData?.name || ''">
                        <input type="hidden" name="client_phone" :value="selectedCustomerData?.phone || ''">
                        <input type="hidden" name="client_email" :value="selectedCustomerData?.email || ''">

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
                                       autocomplete="off" placeholder="جستجو با نام، ایمیل، موبایل یا کد ملی...">
                            </div>
                            <div x-show="customerDropdownOpen && filteredCustomers.length > 0" x-transition
                                 class="absolute z-100 w-full max-h-64 overflow-y-auto bg-white dark:bg-gray-900 border border-t-0 border-gray-200 dark:border-gray-700 rounded-xl rounded-t-none shadow-xl">
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
                                                  x-text="[c.phone, c.email].filter(Boolean).join(' • ')"></span>
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
                </div>
            </div>

            {{-- اقلام فاکتور --}}
            <div class="{{ $cardClass }} relative"
                 :class="items.some(i => i._showServiceDropdown || i._showProductDropdown) ? 'z-20 overflow-visible' : 'z-10 overflow-hidden'">
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex flex-wrap gap-4 justify-between items-center">
                    <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                        <div
                            class="p-2 bg-violet-100 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                        </div>
                        اقلام فاکتور
                    </h2>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="addItem('service')"
                                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-indigo-700 bg-indigo-100 hover:bg-indigo-200 rounded-xl transition-all dark:bg-indigo-500/20 dark:text-indigo-400 dark:hover:bg-indigo-500/30 active:scale-95 shadow-sm">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            انتخاب از سرویس‌ها
                        </button>
                        @if(!empty($marketModuleEnabled))
                            <button type="button" @click="addItem('product')"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 rounded-xl transition-all dark:bg-emerald-500/20 dark:text-emerald-400 dark:hover:bg-emerald-500/30 active:scale-95 shadow-sm">
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                انتخاب از فروشگاه
                            </button>
                        @endif
                        <button type="button" @click="addItem('manual')"
                                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-violet-700 bg-violet-100 hover:bg-violet-200 rounded-xl transition-all dark:bg-violet-500/20 dark:text-violet-400 dark:hover:bg-violet-500/30 active:scale-95 shadow-sm">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h6"/>
                            </svg>
                            ردیف دستی
                        </button>
                    </div>
                </div>

                <div class="w-full transition-all duration-300"
                     :class="items.some(i => i._showServiceDropdown || i._showProductDropdown) ? 'overflow-visible' : 'overflow-x-auto'">
                    <table class="w-full text-sm text-start border-collapse min-w-[1100px]">
                        <thead
                            class="bg-gray-50/80 dark:bg-gray-900/30 text-gray-500 dark:text-gray-400 font-bold border-b border-gray-100 dark:border-gray-700/50 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 w-[20%] min-w-[200px] font-bold">سرویس / محصول</th>
                            <th class="px-4 py-3 w-[18%] min-w-[180px] font-bold">شرح</th>
                            <th class="px-4 py-3 w-[11%] min-w-[120px] font-bold text-center">تعداد / واحد</th>
                            <th class="px-4 py-3 w-[18%] min-w-[170px] font-bold text-center">مبلغ واحد</th>
                            <th class="px-4 py-3 w-[13%] min-w-[150px] font-bold text-center">تخفیف</th>
                            <th class="px-4 py-3 w-[10%] min-w-[110px] font-bold text-center"
                                x-show="taxMode === 'item'">مالیات ردیف
                            </th>
                            <th class="px-4 py-3 w-[10%] min-w-[130px] font-bold text-center">جمع کل</th>
                            <th class="px-4 py-3 w-12 text-center"></th>
                        </tr>
                        </thead>
                        <template x-for="(item, index) in items" :key="index">
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 transition-all"
                                   :class="(item._showServiceDropdown || item._showProductDropdown) ? 'relative z-50' : 'relative z-10'">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors group"
                                :class="(item._showServiceDropdown || item._showProductDropdown) ? 'relative z-50' : 'relative z-10'">
                                <td class="px-4 py-3 align-top"
                                    :class="(item._showServiceDropdown || item._showProductDropdown) ? 'overflow-visible' : ''">
                                    <input type="hidden" :name="'items[' + index + '][service_id]'"
                                           :value="item.service_id">
                                    <input type="hidden" :name="'items[' + index + '][product_id]'"
                                           :value="item.product_id || ''">
                                    <input type="hidden" :name="'items[' + index + '][product_variant_id]'"
                                           :value="item.product_variant_id || ''">

                                    <template x-if="item.mode === 'manual'">
                                        <input type="text" :name="'items[' + index + '][custom_service_name]'"
                                               x-model="item.custom_service_name"
                                               class="{{ $inputClass }} py-2.5 text-xs w-full"
                                               placeholder="نام سرویس / کالا را تایپ کنید...">
                                    </template>

                                    <template x-if="item.mode === 'product'">
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-2 w-full">
                                                <div class="relative flex-1 min-w-0"
                                                     @click.outside="item._showProductDropdown = false">
                                                    <input type="text"
                                                           :name="'items[' + index + '][custom_service_name]'"
                                                           :value="item.custom_service_name"
                                                           @focus="item._showProductDropdown = true"
                                                           @input.debounce.300ms="item.custom_service_name = $event.target.value; onProductInput(index)"
                                                           class="{{ $inputClass }} py-2.5 text-xs w-full"
                                                           placeholder="جستجوی محصول فروشگاه...">
                                                    <div
                                                        x-show="item._showProductDropdown && filteredProducts(index).length > 0"
                                                        x-transition
                                                        class="absolute z-[100] mt-1 w-full max-h-56 overflow-y-auto overscroll-contain bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl">
                                                        <template x-for="p in filteredProducts(index)" :key="p.id">
                                                            <button type="button" @click="selectProductInline(index, p)"
                                                                    class="w-full text-start px-4 py-3 text-xs hover:bg-emerald-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700/50 last:border-0 transition-colors">
                                                                <span class="font-bold text-gray-900 dark:text-white block"
                                                                      x-text="p.name"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                    <p x-show="item._showProductDropdown && item.custom_service_name && filteredProducts(index).length === 0"
                                                       class="mt-1 text-[10px] text-gray-400 px-1">کالایی یافت نشد.</p>
                                                </div>
                                                <button type="button" @click="openProductModal(index)"
                                                        x-show="item.product_id"
                                                        class="shrink-0 relative w-10 h-10 flex items-center justify-center rounded-xl transition-all border shadow-sm outline-none focus:ring-2 focus:ring-emerald-500/20 active:scale-95 bg-emerald-50 border-emerald-300 text-emerald-600 dark:bg-emerald-500/20 dark:border-emerald-500/40 dark:text-emerald-400"
                                                        title="تغییر ویژگی‌های کالا">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div x-show="item.product_id"
                                                 class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 px-2 py-1 rounded-lg">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                                  stroke-linejoin="round"
                                                                                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg> فروشگاه
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="item.mode === 'service'">
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-2 w-full">
                                                <div class="relative flex-1 min-w-0"
                                                     @click.outside="item._showServiceDropdown = false">
                                                    <input type="text"
                                                           :name="'items[' + index + '][custom_service_name]'"
                                                           :value="item.custom_service_name"
                                                           @focus="item._showServiceDropdown = true"
                                                           @input.debounce.300ms="item.custom_service_name = $event.target.value; onServiceInput(index)"
                                                           class="{{ $inputClass }} py-2.5 text-xs w-full"
                                                           placeholder="جستجوی سرویس...">
                                                    <div
                                                        x-show="item._showServiceDropdown && filteredServices(index).length > 0"
                                                        x-transition
                                                        class="absolute z-[100] mt-1 w-full max-h-48 overflow-y-auto overscroll-contain bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl">
                                                        <template x-for="s in filteredServices(index)" :key="s.id">
                                                            <button type="button" @click="selectService(index, s)"
                                                                    class="w-full text-start px-4 py-3 text-xs hover:bg-indigo-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700/50 last:border-0 transition-colors">
                                                                <span class="font-bold text-gray-900 dark:text-white"
                                                                      x-text="s.name"></span>
                                                                <span
                                                                    class="text-[10px] text-gray-400 dark:text-gray-500 block mt-1"
                                                                    x-text="(s.has_unit_pricing ? (s.unit_price ? formatMoney(s.unit_price) : 0) : (s.base_price ? formatMoney(s.base_price) : 0)) + ' {{ $currencyLabel }}' + (s.has_unit_pricing && s.unit_name ? ' / ' + s.unit_name : '')"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                                <button type="button"
                                                        x-show="item.service_custom_fields && item.service_custom_fields.length > 0"
                                                        @click="item._showCustomFields = !item._showCustomFields"
                                                        class="shrink-0 relative w-10 h-10 flex items-center justify-center rounded-xl transition-all border shadow-sm outline-none focus:ring-2 focus:ring-indigo-500/20 active:scale-95"
                                                        :class="item._showCustomFields ? 'bg-indigo-50 border-indigo-300 text-indigo-600 dark:bg-indigo-500/20 dark:border-indigo-500/40 dark:text-indigo-400' : 'bg-white border-gray-200 text-gray-500 hover:text-indigo-600 hover:border-indigo-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400'"
                                                        title="مشاهده فیلدهای سفارشی">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                                    </svg>
                                                    <span
                                                        class="absolute -top-1.5 -end-1.5 flex items-center justify-center min-w-4 h-4 px-1 rounded-full text-[9px] tabular-nums font-black shadow-sm"
                                                        :class="item._showCustomFields ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300'"
                                                        x-text="item.service_custom_fields.length"></span>
                                                </button>
                                            </div>
                                            <div
                                                x-show="item.mode === 'service' && item.service_raw && item.service_raw.billing_type === 'recurring'"
                                                class="mt-2">
                                                <input type="hidden" :name="'items[' + index + '][billing_period]'"
                                                       :value="item.billing_period">
                                                <select x-model="item.billing_period"
                                                        @change="updatePriceForPeriod(index)"
                                                        class="{{ $inputClass }} py-2 text-xs">
                                                    <option value="">انتخاب دوره</option>
                                                    <template x-for="(label, period) in periodLabels" :key="period">
                                                        <option :value="period" x-text="label"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>
                                    </template>
                                </td>
                                <td class="px-4 py-3 align-top"><input type="text" x-model="item.description"
                                                                       :name="'items[' + index + '][description]'"
                                                                       class="{{ $inputClass }} py-2.5 text-xs w-full"
                                                                       placeholder="توضیحات ردیف"></td>
                                <td class="px-4 py-3 align-top">
                                    <div
                                        class="flex items-stretch w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/50 overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500 transition-all shadow-sm">
                                        <input type="text" :value="toPersianNum(item.quantity)"
                                               @input="let v = toEnglishNum($event.target.value).replace(/[^\d.]/g, ''); if(item.single_sell && v > 1) { v = 1; $el.value = '۱'; alert('این کالا دارای محدودیت فروش تکی (۱ عدد) است.'); } item.quantity = v;"
                                               :name="'items[' + index + '][quantity]'" required
                                               class="flex-1 min-w-0 w-full border-none bg-transparent py-2.5 px-2 font-black text-gray-900 dark:text-white text-center tabular-nums focus:ring-0 transition-all duration-300"
                                               :class="item.mode === 'manual' ? 'text-sm' : 'text-base'" dir="ltr"
                                               placeholder="۰">
                                        <div class="w-px bg-gray-200 dark:bg-gray-700 shrink-0"></div>
                                        <div class="flex items-stretch shrink-0 transition-all duration-300"
                                             :class="[item.mode === 'manual' && item._unitUnlocked ? 'bg-indigo-50 dark:bg-indigo-500/10' : 'bg-slate-50 dark:bg-slate-800/80', item.mode === 'manual' ? 'w-20' : 'w-16']">
                                            <input type="text" x-model="item.unit" :name="'items[' + index + '][unit]'"
                                                   :readonly="item.mode === 'service' || !item._unitUnlocked"
                                                   class="w-full min-w-0 border-none bg-transparent py-2 px-1 text-xs font-black text-center focus:ring-0 transition-colors duration-300"
                                                   :class="item.mode === 'manual' && item._unitUnlocked ? 'text-indigo-700 dark:text-indigo-400 cursor-text' : 'text-slate-500 dark:text-slate-400 pointer-events-none'">
                                            <button type="button" x-show="item.mode === 'manual'"
                                                    @click="item._unitUnlocked = !item._unitUnlocked; if(item._unitUnlocked) $nextTick(() => { $el.previousElementSibling.focus() })"
                                                    class="shrink-0 px-2 flex items-center justify-center border-s border-gray-200 dark:border-gray-700 transition-all"
                                                    :class="item._unitUnlocked ? 'text-indigo-600 bg-indigo-100/50 dark:bg-indigo-500/20 dark:text-indigo-400 border-indigo-200 dark:border-indigo-500/30' : 'text-gray-400 hover:text-indigo-500 hover:bg-gray-200 dark:hover:bg-gray-700'"
                                                    title="ویرایش واحد">
                                                <svg class="w-3.5 h-3.5 transition-transform"
                                                     :class="item._unitUnlocked ? 'scale-110' : ''" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <div class="flex items-center gap-1.5 w-full">
                                        <div class="relative w-full">
                                            <input type="text" :value="formatPriceInput(item.unit_price)"
                                                   @input="item.unit_price = parsePriceInput($event.target.value)"
                                                   :name="'items[' + index + '][unit_price]'" required
                                                   :readonly="item.mode === 'service' && !!item.service_id && !item._priceUnlocked"
                                                   :class="(item.mode === 'service' && item.service_id && !item._priceUnlocked) ? 'bg-gray-100 dark:bg-gray-900 cursor-not-allowed text-gray-500 dark:text-gray-400' : ''"
                                                   class="{{ $inputClass }} py-2.5 text-sm font-black text-center tabular-nums w-full pe-12"
                                                   dir="ltr" placeholder="۰">
                                            <span
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-gray-400 pointer-events-none">{{ $currencyLabel }}</span>
                                        </div>
                                        <button type="button" x-show="item.mode === 'service' && item.service_id"
                                                @click="item._priceUnlocked = !item._priceUnlocked"
                                                class="shrink-0 p-2.5 rounded-lg border transition-colors"
                                                :class="item._priceUnlocked ? 'border-indigo-400 bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400' : 'border-gray-200 text-gray-400 hover:text-indigo-500 hover:border-indigo-300 dark:border-gray-700'"
                                                title="ویرایش مبلغ واحد">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div
                                        x-show="item.mode === 'service' && item.service_raw && !item.service_raw.has_unit_pricing && getPeriodPrice(item) > 0"
                                        class="text-[10px] text-gray-500 dark:text-gray-400 mt-1.5 text-center bg-gray-100 dark:bg-gray-800/50 p-1 rounded-md">
                                        (پایه: <span x-text="formatMoney(item.service_raw?.base_price || 0)"></span> +
                                        اشتراک: <span x-text="formatMoney(getPeriodPrice(item) || 0)"></span>)
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <div class="relative w-full">
                                        <input type="text" :value="formatPriceInput(item.discount)"
                                               @input="item.discount = parsePriceInput($event.target.value)"
                                               :name="'items[' + index + '][discount]'"
                                               class="{{ $inputClass }} py-2.5 text-xs text-center tabular-nums font-medium w-full pe-10"
                                               dir="ltr" placeholder="۰">
                                        <span
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-gray-400 pointer-events-none">{{ $currencyLabel }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-top" x-show="taxMode === 'item'">
                                    <div class="flex items-center justify-center gap-1.5 w-full">
                                        <span class="relative inline-flex items-center">
                                            <input type="text" :value="toPersianNum(item.tax_percent)"
                                                   @input="item.tax_percent = Math.min(100, Math.max(0, Number(toEnglishNum($event.target.value).replace(/[^\d.]/g, '')) || 0))"
                                                   :name="'items[' + index + '][tax_percent]'"
                                                   class="w-14 rounded-lg border-2 bg-amber-50 dark:bg-amber-900/20 px-2 py-1.5 text-xs text-center tabular-nums font-bold focus:ring-2 focus:ring-amber-500/20 outline-none transition-colors"
                                                   :class="item._taxUnlocked ? 'border-amber-400 text-amber-800 dark:text-amber-200 bg-white dark:bg-gray-900' : 'border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400 cursor-not-allowed opacity-80'"
                                                   dir="ltr" maxlength="3" :readonly="!item._taxUnlocked"><span
                                                class="ms-1 text-xs">%</span>
                                        </span>
                                        <button type="button" @click="item._taxUnlocked = !item._taxUnlocked"
                                                class="p-1.5 rounded-lg border-2 transition-all active:scale-95 shrink-0"
                                                :class="item._taxUnlocked ? 'border-amber-500 bg-amber-50 text-amber-600 dark:bg-amber-500/20 dark:border-amber-500/50 dark:text-amber-400 shadow-sm' : 'border-amber-200 text-amber-500 hover:text-amber-700 hover:border-amber-400 hover:bg-amber-50/50 dark:border-amber-700/50 dark:text-amber-500 dark:hover:bg-amber-900/30'"
                                                :title="item._taxUnlocked ? 'قفل کردن مالیات ردیف' : 'ویرایش دستی مالیات ردیف'">
                                            <svg x-show="!item._taxUnlocked" class="w-3.5 h-3.5" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                            <svg x-show="item._taxUnlocked" class="w-3.5 h-3.5" x-cloak fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-3 tabular-nums font-bold text-gray-800 dark:text-gray-100 text-center whitespace-nowrap align-top">
                                    <div class="py-2"><span x-text="formatMoney(calculateRowTotal(item))"></span><span
                                            class="text-[10px] font-normal text-gray-400 ms-1">{{ $currencyLabel }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center align-top">
                                    <button type="button" @click="removeItem(index)"
                                            class="mt-1 text-gray-300 hover:text-red-500 dark:hover:bg-red-500/10 hover:bg-red-50 rounded-lg p-2 transition-colors opacity-0 group-hover:opacity-100 focus:opacity-100">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>

                            {{-- ردیف‌های قیمتی فیلدهای سفارشی --}}
                            <template x-if="item.service_custom_fields && item.service_custom_fields.length > 0">
                                <template x-for="field in item.service_custom_fields" :key="field.id + '_subrow'">
                                    <tr x-show="field.has_pricing && isFieldSelected(field, item.custom_field_values[field.id])"
                                        class="bg-indigo-50/20 dark:bg-indigo-500/5 border-y border-dashed border-indigo-100/70 dark:border-indigo-500/10 transition-all group relative">
                                        <td class="px-4 py-2.5 relative align-middle">
                                            <div
                                                class="absolute top-0 bottom-0 right-5 w-px bg-indigo-200 dark:bg-indigo-800/50"></div>
                                            <div
                                                class="absolute top-1/2 right-5 w-3 h-px bg-indigo-200 dark:bg-indigo-800/50"></div>
                                            <div class="pe-4 ps-6 flex items-center gap-2">
                                                <span
                                                    class="flex items-center justify-center w-5 h-5 rounded-md bg-indigo-100/80 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 shrink-0 shadow-sm">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor" stroke-width="2.5"><path
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                                </span>
                                                <span
                                                    class="text-xs font-bold text-indigo-900 dark:text-indigo-300 truncate"
                                                    x-text="field.label"></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2.5 align-middle">
                                            <span
                                                class="inline-block text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100/70 dark:bg-gray-800/60 px-2.5 py-1 rounded-lg border border-gray-200/40 dark:border-gray-700/40"
                                                x-text="getFieldValueLabel(field, item.custom_field_values[field.id])"></span>
                                        </td>
                                        <td class="px-4 py-2.5 align-middle"><input type="text" readonly
                                                                                    :value="toPersianNum(item.quantity)"
                                                                                    class="{{ $inputClass }} py-1.5 text-xs text-center bg-gray-50/50 dark:bg-gray-900/20 opacity-70 cursor-not-allowed border-gray-200 dark:border-gray-800 text-gray-400 shadow-none">
                                        </td>
                                        <td class="px-4 py-2.5 align-middle">
                                            <div class="flex items-center gap-1.5 w-full">
                                                <div class="relative w-full">
                                                    <input type="text"
                                                           :value="formatPriceInput(getCustomFieldPrice(item, field))"
                                                           @input="item.custom_field_custom_prices[field.id] = parsePriceInput($event.target.value)"
                                                           :name="'items[' + index + '][custom_fields_prices][' + field.id + ']'"
                                                           :readonly="!item._customPricesUnlocked?.[field.id]"
                                                           :class="!item._customPricesUnlocked?.[field.id] ? 'bg-gray-100 dark:bg-gray-900/50 cursor-not-allowed text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-800' : 'bg-white dark:bg-gray-900 border-indigo-300'"
                                                           class="{{ $inputClass }} py-2 text-sm text-center tabular-nums font-black w-full pe-14 shadow-none"
                                                           dir="ltr" placeholder="۰">
                                                    <span
                                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] font-bold text-gray-400 pointer-events-none">{{ $currencyLabel }}</span>
                                                </div>
                                                <button type="button"
                                                        @click="item._customPricesUnlocked = item._customPricesUnlocked || {}; item._customPricesUnlocked[field.id] = !item._customPricesUnlocked[field.id]"
                                                        class="shrink-0 p-1.5 rounded-lg border transition-colors"
                                                        :class="item._customPricesUnlocked?.[field.id] ? 'border-indigo-400 bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400' : 'border-gray-200 text-gray-400 hover:text-indigo-500 hover:border-indigo-300 dark:border-gray-700'"
                                                        title="ویرایش مبلغ فیلد">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2.5 align-middle">
                                            <div class="relative w-full">
                                                <input type="text"
                                                       :value="formatPriceInput(getCustomFieldDiscount(item, field))"
                                                       @input="item.custom_field_custom_discounts[field.id] = parsePriceInput($event.target.value)"
                                                       :name="'items[' + index + '][custom_fields_discounts][' + field.id + ']'"
                                                       class="{{ $inputClass }} py-2 text-sm text-center tabular-nums font-black w-full pe-14 shadow-none border-gray-200 dark:border-gray-800"
                                                       dir="ltr" placeholder="۰">
                                                <span
                                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] font-bold text-gray-400 pointer-events-none">{{ $currencyLabel }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2.5 align-middle"
                                            x-show="taxMode === 'item' && taxApplyCustomFields">
                                            <div class="flex items-center justify-center gap-1.5 w-full">
                                                <span class="relative inline-flex items-center">
                                                    <input type="text"
                                                           :value="toPersianNum(item.custom_field_tax_percents[field.id] || defaultTaxRate)"
                                                           @input="item.custom_field_tax_percents[field.id] = Math.min(100, Math.max(0, Number(toEnglishNum($event.target.value).replace(/[^\d.]/g, '')) || 0))"
                                                           :name="'items[' + index + '][custom_fields_taxes][' + field.id + ']'"
                                                           class="w-14 rounded-lg border-2 bg-amber-50 dark:bg-amber-900/20 px-2 py-1.5 text-xs text-center tabular-nums font-bold focus:ring-2 focus:ring-amber-500/20 outline-none transition-colors"
                                                           :class="item._customFieldTaxUnlocked?.[field.id] ? 'border-amber-400 text-amber-800 dark:text-amber-200 bg-white dark:bg-gray-900' : 'border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400 cursor-not-allowed opacity-80'"
                                                           dir="ltr" maxlength="3"
                                                           :readonly="!item._customFieldTaxUnlocked?.[field.id]"><span
                                                        class="ms-1 text-xs">%</span>
                                                </span>
                                                <button type="button"
                                                        @click="item._customFieldTaxUnlocked = item._customFieldTaxUnlocked || {}; item._customFieldTaxUnlocked[field.id] = !item._customFieldTaxUnlocked[field.id]"
                                                        class="p-1.5 rounded-lg border-2 transition-all active:scale-95 shrink-0"
                                                        :class="item._customFieldTaxUnlocked?.[field.id] ? 'border-amber-500 bg-amber-50 text-amber-600 dark:bg-amber-500/20 dark:border-amber-500/50 dark:text-amber-400 shadow-sm' : 'border-amber-200 text-amber-500 hover:text-amber-700 hover:border-amber-400 hover:bg-amber-50/50 dark:border-amber-700/50 dark:text-amber-500 dark:hover:bg-amber-900/30'"
                                                        :title="item._customFieldTaxUnlocked?.[field.id] ? 'قفل کردن مالیات' : 'ویرایش دستی مالیات'">
                                                    <svg x-show="!item._customFieldTaxUnlocked?.[field.id]"
                                                         class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                    </svg>
                                                    <svg x-show="item._customFieldTaxUnlocked?.[field.id]"
                                                         class="w-3.5 h-3.5" x-cloak fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2.5 align-middle tabular-nums font-black text-indigo-600 dark:text-indigo-400 text-center whitespace-nowrap text-sm">
                                            <span x-text="formatMoney(getCustomFieldRowTotal(item, field))"></span>
                                            <span
                                                class="text-[10px] font-normal text-gray-400 ms-1">{{ $currencyLabel }}</span>
                                        </td>
                                        <td class="px-4 py-2.5 align-middle text-center">
                                            <button type="button"
                                                    @click="if (field.type === 'checkbox') item.custom_field_values[field.id] = false; else if (field.type === 'multiselect') item.custom_field_values[field.id] = []; else item.custom_field_values[field.id] = '';"
                                                    class="text-gray-300 hover:text-red-500 dark:hover:bg-red-500/10 hover:bg-red-50 rounded-lg p-1.5 transition-colors opacity-0 group-hover:opacity-100 focus:opacity-100"
                                                    title="حذف مقدار فیلد">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </template>

                            {{-- بخش تنظیمات فیلدهای سفارشی --}}
                            <tr x-show="item.service_custom_fields && item.service_custom_fields.length > 0">
                                <td colspan="8" class="p-0 border-0">
                                    <div x-show="item._showCustomFields"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 -translate-y-2"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-150"
                                         x-transition:leave-start="opacity-100 translate-y-0"
                                         x-transition:leave-end="opacity-0 -translate-y-2"
                                         class="bg-slate-50/70 dark:bg-slate-800/40 border-y border-slate-200/80 dark:border-slate-700/80 shadow-[inset_0_4px_6px_-4px_rgba(0,0,0,0.05)] relative z-0">
                                        <div class="flex">
                                            <div
                                                class="w-1.5 bg-indigo-400/80 dark:bg-indigo-600/80 shadow-[2px_0_8px_rgba(99,102,241,0.2)]"></div>
                                            <div class="p-6 w-full">
                                                <div
                                                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                                    <template x-for="field in item.service_custom_fields"
                                                              :key="field.id">
                                                        <div
                                                            class="relative p-3 rounded-xl bg-white dark:bg-gray-900/50 border border-gray-200/80 dark:border-gray-700/60 shadow-sm hover:border-indigo-300 dark:hover:border-indigo-500/50 transition-colors group">
                                                            <div class="flex justify-between items-center mb-2">
                                                                <label
                                                                    class="text-xs font-black text-gray-700 dark:text-gray-200 truncate"
                                                                    :title="field.label" x-text="field.label"></label>
                                                                <div class="flex items-center gap-2 shrink-0">
                                                                    <template
                                                                        x-if="field.has_pricing && getCustomFieldPrice(item, field) > 0">
                                                                        <span
                                                                            class="flex items-center gap-1 text-sm font-black text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-2.5 py-1 rounded-md border border-indigo-200 dark:border-indigo-500/20 shadow-sm">+<span
                                                                                x-text="formatMoney(getCustomFieldPrice(item, field))"></span><span
                                                                                class="text-[10px] font-bold text-indigo-500 dark:text-indigo-400/80">{{ $currencyLabel }}</span></span>
                                                                    </template>
                                                                    <span x-show="field.is_required"
                                                                          class="text-[9px] font-black text-rose-500 bg-rose-50 dark:bg-rose-500/10 px-2 py-0.5 rounded-md border border-rose-100 dark:border-rose-500/20">الزامی</span>
                                                                </div>
                                                            </div>
                                                            <div class="w-full flex items-center">
                                                                <template
                                                                    x-if="['text', 'email', 'phone', 'url'].includes(field.type)">
                                                                    <input
                                                                        :type="field.type === 'url' ? 'url' : field.type"
                                                                        :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                        x-model="item.custom_field_values[field.id]"
                                                                        class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-xs text-gray-800 dark:text-gray-200 dark:bg-gray-900/50 dark:border-gray-700 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                                                                        :required="field.is_required"></template>
                                                                <template x-if="field.type === 'date'"><input
                                                                        type="text" readonly data-jdp data-jdp-only-date
                                                                        :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                        x-model="item.custom_field_values[field.id]"
                                                                        class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-xs text-gray-800 dark:text-gray-200 dark:bg-gray-900/50 dark:border-gray-700 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none cursor-pointer transition-all"
                                                                        placeholder="انتخاب تاریخ" autocomplete="off"
                                                                        :required="field.is_required"></template>
                                                                <template x-if="field.type === 'datetime'"><input
                                                                        type="text" readonly data-jdp
                                                                        :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                        x-model="item.custom_field_values[field.id]"
                                                                        class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-xs text-gray-800 dark:text-gray-200 dark:bg-gray-900/50 dark:border-gray-700 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none cursor-pointer transition-all"
                                                                        placeholder="انتخاب تاریخ و ساعت"
                                                                        autocomplete="off"
                                                                        :required="field.is_required"></template>
                                                                <template x-if="field.type === 'number'"><input
                                                                        type="text"
                                                                        :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                        :value="formatPriceInput(item.custom_field_values[field.id])"
                                                                        @input="item.custom_field_values[field.id] = parsePriceInput($event.target.value)"
                                                                        class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-sm font-bold text-gray-800 dark:text-gray-200 dark:bg-gray-900/50 dark:border-gray-700 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none text-center tabular-nums dir-ltr transition-all"
                                                                        :required="field.is_required"></template>
                                                                <template x-if="field.type === 'textarea'"><textarea
                                                                        :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                        x-model="item.custom_field_values[field.id]"
                                                                        rows="2"
                                                                        class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-xs text-gray-800 dark:text-gray-200 dark:bg-gray-900/50 dark:border-gray-700 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none resize-none transition-all"
                                                                        :required="field.is_required"></textarea>
                                                                </template>
                                                                <template x-if="field.type === 'select'"><select
                                                                        :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                        x-model="item.custom_field_values[field.id]"
                                                                        class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-xs text-gray-800 dark:text-gray-200 dark:bg-gray-900/50 dark:border-gray-700 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none cursor-pointer transition-all"
                                                                        :required="field.is_required">
                                                                        <option value="">انتخاب کنید...</option>
                                                                        <template
                                                                            x-for="opt in (Array.isArray(field.options) ? field.options : [])"
                                                                            :key="opt">
                                                                            <option :value="opt" x-text="opt"></option>
                                                                        </template>
                                                                    </select></template>
                                                                <template x-if="field.type === 'multiselect'">
                                                                    <div
                                                                        class="flex flex-col gap-1.5 w-full max-h-40 overflow-y-auto sc-thin">
                                                                        <template
                                                                            x-for="opt in (Array.isArray(field.options) ? field.options : [])"
                                                                            :key="opt"><label
                                                                                class="flex items-center gap-2.5 cursor-pointer text-[11px] px-3 py-2.5 rounded-xl bg-gray-50/50 dark:bg-gray-800/50 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 border border-transparent hover:border-indigo-200 dark:hover:border-indigo-800 transition-colors w-full"><input
                                                                                    type="checkbox"
                                                                                    :name="'items[' + index + '][custom_fields][' + field.id + '][]'"
                                                                                    :value="opt"
                                                                                    :checked="Array.isArray(item.custom_field_values[field.id]) && item.custom_field_values[field.id].includes(opt)"
                                                                                    @change="toggleMultiselect(item, field.id, opt, $event.target.checked)"
                                                                                    class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 dark:bg-gray-900"><span
                                                                                    x-text="opt"
                                                                                    class="text-gray-700 dark:text-gray-300 font-medium"></span></label>
                                                                        </template>
                                                                    </div>
                                                                </template>
                                                                <template x-if="field.type === 'radio'">
                                                                    <div class="flex flex-col gap-1.5 w-full">
                                                                        <template
                                                                            x-for="opt in (Array.isArray(field.options) ? field.options : [])"
                                                                            :key="opt"><label
                                                                                class="flex items-center gap-2.5 cursor-pointer text-[11px] px-3 py-2.5 rounded-xl bg-gray-50/50 dark:bg-gray-800/50 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 border border-transparent hover:border-indigo-200 dark:hover:border-indigo-800 transition-colors w-full"><input
                                                                                    type="radio"
                                                                                    :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                                    x-model="item.custom_field_values[field.id]"
                                                                                    :value="opt"
                                                                                    class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 dark:bg-gray-900"><span
                                                                                    x-text="opt"
                                                                                    class="text-gray-700 dark:text-gray-300 font-medium"></span></label>
                                                                        </template>
                                                                    </div>
                                                                </template>
                                                                <template x-if="field.type === 'checkbox'"><label
                                                                        class="flex items-center gap-2.5 cursor-pointer w-full px-3 py-3 rounded-xl border border-gray-200 bg-gray-50/50 hover:bg-indigo-50 hover:border-indigo-300 dark:bg-gray-900/50 dark:border-gray-700 dark:hover:bg-indigo-900/30 dark:hover:border-indigo-700 transition-colors"><input
                                                                            type="checkbox"
                                                                            :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                            x-model="item.custom_field_values[field.id]"
                                                                            value="1"
                                                                            class="w-4.5 h-4.5 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 dark:bg-gray-900"><span
                                                                            class="text-xs font-bold text-gray-700 dark:text-gray-300">انتخاب می‌کنم</span></label>
                                                                </template>
                                                                <template x-if="field.type === 'file'"><input
                                                                        type="file"
                                                                        :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                        @change="item.custom_field_values[field.id] = $event.target.files[0]?.name || ''"
                                                                        class="w-full text-xs rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-gray-800 dark:text-gray-200 dark:bg-gray-900/50 dark:border-gray-700 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-400"
                                                                        :required="field.is_required"></template>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </template>
                    </table>
                </div>

                {{-- Totals --}}
                <div class="bg-gray-50/50 dark:bg-gray-900/20 p-6 border-t border-gray-100 dark:border-gray-700/50">
                    <input type="hidden" name="extra_discount_type" :value="extraDiscountType">
                    <input type="hidden" name="extra_discount_value" :value="extraDiscountValue">
                    <div class="w-full md:w-md ms-auto">
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between text-gray-600 dark:text-gray-400 font-medium"><span>جمع کل مبالغ</span><span
                                    class="tabular-nums font-medium"><span x-text="formatMoney(totals.subtotal)"></span><span
                                        class="text-[10px] text-gray-400 ms-1">{{ $currencyLabel }}</span></span></div>
                            <div
                                class="flex justify-between items-center text-red-500 dark:text-red-400 font-medium gap-3">
                                <span class="flex items-center gap-2 shrink-0 flex-wrap">جمع تخفیف‌ها<span
                                        class="relative inline-flex items-center gap-1"><input type="text"
                                                                                               :value="extraDiscountType === 'percent' ? toPersianNum(extraDiscountValue) : formatPriceInput(extraDiscountValue)"
                                                                                               @input="onExtraDiscountInput($event)"
                                                                                               :class="extraDiscountType === 'percent' ? 'w-14' : 'w-28'"
                                                                                               class="rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-2 py-1 text-xs text-center tabular-nums font-bold text-red-700 dark:text-red-400 focus:ring-2 focus:ring-red-500/20 outline-none transition-all"
                                                                                               dir="ltr"
                                                                                               placeholder="۰"><button
                                            type="button" @click="toggleExtraDiscountType()"
                                            class="px-2 py-1 rounded-lg text-[10px] font-black border transition-colors"
                                            :class="extraDiscountType === 'percent' ? 'bg-red-600 text-white border-red-600' : 'bg-white dark:bg-gray-800 text-red-500 border-red-200 dark:border-red-800'"
                                            title="تغییر نوع تخفیف (مبلغ / درصد)"><span
                                                x-text="extraDiscountType === 'percent' ? '٪' : '{{ $currencyLabel }}'"></span></button></span></span>
                                <span class="tabular-nums font-medium">− <span
                                        x-text="formatMoney(totals.discount)"></span><span
                                        class="text-[10px] text-gray-400 ms-1">{{ $currencyLabel }}</span></span>
                            </div>
                            <div x-show="taxMode !== 'item'"
                                 class="flex justify-between items-center text-amber-600 dark:text-amber-400 font-medium gap-3">
                                <span class="flex items-center gap-2 shrink-0">مالیات فاکتور<span
                                        class="relative inline-flex items-center"><input type="text" name="tax_percent"
                                                                                         :value="toPersianNum(taxPercent)"
                                                                                         @input="taxPercent = Math.min(100, Math.max(0, Number(toEnglishNum($event.target.value).replace(/[^\d.]/g, '')) || 0))"
                                                                                         class="w-14 rounded-lg border-2 bg-amber-50 dark:bg-amber-900/20 px-2 py-1 text-xs text-center tabular-nums font-bold focus:ring-2 focus:ring-amber-500/20 outline-none transition-colors"
                                                                                         :class="taxUnlocked ? 'border-amber-400 text-amber-800 dark:text-amber-200 bg-white dark:bg-gray-900' : 'border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400 cursor-not-allowed opacity-80'"
                                                                                         dir="ltr" maxlength="3"
                                                                                         :readonly="!taxUnlocked"><span
                                            class="ms-1 text-xs">%</span></span><button type="button"
                                                                                        @click="taxUnlocked = !taxUnlocked"
                                                                                        class="p-1.5 rounded-lg border-2 transition-all active:scale-95"
                                                                                        :class="taxUnlocked ? 'border-amber-500 bg-amber-50 text-amber-600 dark:bg-amber-500/20 dark:border-amber-500/50 dark:text-amber-400 shadow-sm' : 'border-amber-200 text-amber-500 hover:text-amber-700 hover:border-amber-400 hover:bg-amber-50/50 dark:border-amber-700/50 dark:text-amber-500 dark:hover:bg-amber-900/30'"
                                                                                        :title="taxUnlocked ? 'قفل کردن مالیات' : 'تایپ و ویرایش دستی مالیات'"><svg
                                            x-show="!taxUnlocked" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2"><path stroke-linecap="round"
                                                                                         stroke-linejoin="round"
                                                                                         d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg><svg
                                            x-show="taxUnlocked" class="w-3.5 h-3.5" x-cloak fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path
                                                stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></button></span>
                                <span class="tabular-nums font-medium">+ <span
                                        x-text="formatMoney(totals.tax)"></span><span
                                        class="text-[10px] text-gray-400 ms-1">{{ $currencyLabel }}</span></span>
                            </div>
                            <div x-show="taxMode === 'item'"
                                 class="flex justify-between items-center text-amber-600 dark:text-amber-400 font-medium gap-3">
                                <span class="flex items-center gap-2 shrink-0">مجموع مالیات ردیف‌ها<span
                                        class="text-[10px] font-normal text-gray-400">(از تنظیمات هر ردیف)</span></span>
                                <span class="tabular-nums font-medium">+ <span
                                        x-text="formatMoney(totals.tax)"></span><span
                                        class="text-[10px] text-gray-400 ms-1">{{ $currencyLabel }}</span></span>
                            </div>
                        </div>
                        <div class="border-t-2 border-dashed border-gray-200 dark:border-gray-700 my-4"></div>
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-base font-black text-gray-900 dark:text-white block">مبلغ نهایی</span>
                                <template x-if="totals.isRounded">
                                    <div class="mt-1 flex items-center gap-1.5 text-[11px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/40 px-2 py-0.5 rounded-lg border border-indigo-200/80 dark:border-indigo-800/60 w-fit">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>حاصل رندسازی مبالغ (<span x-text="(totals.roundingDiff > 0 ? '+' : '') + formatMoney(totals.roundingDiff)"></span> {{ $currencyLabel }})</span>
                                    </div>
                                </template>
                            </div>
                            <div class="text-end"><span
                                    class="tabular-nums text-xl font-black text-indigo-600 dark:text-indigo-400"
                                    x-text="formatMoney(totals.grand)"></span><span
                                    class="text-xs text-gray-400 block">{{ $currencyLabel }}</span></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- یادداشت --}}
            <div class="{{ $cardClass }} relative overflow-hidden">
                <div
                    class="absolute inset-0 bg-linear-to-br from-emerald-50/50 via-transparent to-transparent dark:from-emerald-500/5 pointer-events-none"></div>
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 rounded-t-3xl relative">
                    <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                        <div
                            class="p-2 bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        یادداشت
                    </h2>
                </div>
                <div class="p-6 relative">
                    <div class="relative">
                        <textarea name="notes" rows="4" x-model="notesText"
                                  class="w-full rounded-2xl border-2 border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 px-5 py-4 text-sm leading-7 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all resize-none shadow-inner"
                                  placeholder="یادداشتی برای مشتری بنویسید... (مثلاً شرایط پرداخت، توضیحات گارانتی و ...)"></textarea>
                        <span
                            class="absolute bottom-3 left-4 text-[10px] font-bold text-gray-300 dark:text-gray-600 pointer-events-none select-none"
                            x-text="(notesText || '').length + ' نویسه'"></span>
                    </div>
                </div>
            </div>

            {{-- Sticky Bottom Submit Bar --}}
            <div class="sticky bottom-4 z-40">
                <div
                    class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl p-4 rounded-2xl border border-gray-200 dark:border-gray-700/50 shadow-lg flex flex-row-reverse items-center justify-between gap-4">
                    <button type="submit"
                            class="flex-1 md:flex-none px-8 py-3.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 text-white font-black shadow-lg shadow-amber-500/30 hover:shadow-amber-500/50 transition-all duration-300 active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        ذخیره تغییرات
                    </button>
                </div>
            </div>
        </form>

        @if($isProforma)
            <form action="{{ route('services.invoices.convertToInvoice', $invoice) }}" method="POST"
                  class="flex-1 md:flex-none" @submit="onConvertSubmit($event)">
                @csrf
                <input type="hidden" name="invoice_number" :value="convertInvoiceNumber">
                <button type="submit"
                        class="w-full px-8 py-3.5 rounded-xl bg-linear-to-r from-green-500 to-green-600 text-white font-black shadow-lg shadow-green-500/30 hover:shadow-green-500/50 hover:from-green-400 hover:to-green-500 transition-all duration-300 active:scale-95 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    تبدیل به فاکتور
                </button>
            </form>
        @endif

        <a href="{{ route('services.invoices.show', $invoice) }}"
           class="px-6 py-3.5 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-xl dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">انصراف</a>

        {{-- مدال انتخاب کالا از فروشگاه --}}
        <div x-show="activeProductModalIndex !== null" x-transition.opacity
             class="fixed inset-0 z-[100] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
             style="display: none;" @click.self="closeProductModal()">
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden"
                x-transition.scale.origin.center @click.outside="closeProductModal()">

                {{-- هدر مدال --}}
                <div
                    class="p-5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900/30">
                    <h3 class="text-lg font-black text-gray-800 dark:text-white flex items-center gap-2">
                        <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        انتخاب کالا از فروشگاه
                    </h3>
                    <button @click="closeProductModal()"
                            class="p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-500 hover:text-rose-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- فیلترها --}}
                <div class="p-5 border-b border-gray-200 dark:border-gray-700 space-y-4 bg-gray-50/50 dark:bg-gray-800/50">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        {{-- Stock Status Filter --}}
                        <div>
                            <select x-model="modalStockStatus"
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-emerald-500 outline-none transition-all cursor-pointer">
                                <option value="all">وضعیت موجودی (همه)</option>
                                <option value="in_stock">فقط کالاهای موجود</option>
                                <option value="out_of_stock">فقط ناموجود</option>
                            </select>
                        </div>

                        {{-- Attributes Filters --}}
                        <template x-for="attr in modalProductAttributes" :key="attr.name">
                            <div class="relative" x-data="{ open: false }" x-init="if(!modalSelectedAttributes[attr.name]) modalSelectedAttributes = { ...modalSelectedAttributes, [attr.name]: [] }" @click.outside="open = false">
                                <button type="button" @click="open = !open"
                                        class="w-full flex items-center justify-between rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-emerald-500 outline-none transition-all cursor-pointer">
                                    <div class="flex items-center gap-2 truncate">
                                        <span class="truncate" x-text="attr.name"></span>
                                        <template x-if="modalSelectedAttributes[attr.name] && modalSelectedAttributes[attr.name].length > 0">
                                            <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400 text-[10px] font-bold" x-text="toPersianNum(modalSelectedAttributes[attr.name].length)"></span>
                                        </template>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                                <div x-show="open" style="display: none;" class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                                    <div class="max-h-48 overflow-y-auto p-2 space-y-1 custom-scrollbar">
                                        <template x-for="v in attr.values" :key="v">
                                            <label class="flex items-center gap-2 px-2 py-1.5 text-xs font-medium rounded-lg cursor-pointer hover:bg-emerald-50 dark:hover:bg-gray-700 transition-colors">
                                                <input type="checkbox" :value="v" x-model="modalSelectedAttributes[attr.name]" @change="modalSelectedAttributes = { ...modalSelectedAttributes }" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 bg-gray-50 dark:bg-gray-900 dark:border-gray-600 h-4 w-4">
                                                <span x-text="v" class="text-gray-700 dark:text-gray-300"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- محتوای مدال (دسته‌بندی‌ها و کالاها) --}}
                <div class="p-5 flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900/20">
                    <template x-if="modalCategories.length === 0">
                        <div class="flex flex-col items-center justify-center py-20 text-gray-400 dark:text-gray-500">
                            <svg class="w-12 h-12 mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                            </svg>
                            <span class="text-sm font-bold">کالایی با این مشخصات یافت نشد</span>
                        </div>
                    </template>

                    <template x-for="cat in modalCategories" :key="cat.id + '-' + cat.name">
                        <div
                            class="mb-4 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden bg-white dark:bg-gray-800"
                            x-data="{ open: true }">
                            <button @click="open = !open"
                                    class="w-full flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-1.5 h-6 bg-emerald-500 rounded-full"></span>
                                    <span class="font-black text-sm text-gray-800 dark:text-white"
                                          x-text="cat.name"></span>
                                    <span
                                        class="text-[10px] font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-md"
                                        x-text="cat.products.length + ' کالا'"></span>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 transition-transform duration-300"
                                     :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" x-transition.duration.200ms
                                 class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 border-t border-gray-100 dark:border-gray-700">
                                <template x-for="prod in cat.products" :key="prod.id">
                                    <button @click="selectProductFromModal(prod)"
                                            class="flex items-start gap-3 p-3 rounded-xl border transition-all text-start shadow-sm group"
                                            :class="(items[activeProductModalIndex] && items[activeProductModalIndex].product_id == prod.master_id && items[activeProductModalIndex].product_variant_id == (prod.variant_id || '')) ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-400 ring-1 ring-emerald-500' : 'border-gray-100 dark:border-gray-700 hover:border-emerald-400 hover:bg-emerald-50/40 dark:hover:bg-emerald-500/10'">
                                        <div
                                            class="w-14 h-14 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-900 shrink-0 border border-gray-200 dark:border-gray-700 flex items-center justify-center">
                                            <img x-show="prod.image" :src="prod.image"
                                                 class="w-full h-full object-cover">
                                            <svg x-show="!prod.image" class="w-6 h-6 text-gray-300 dark:text-gray-600"
                                                 fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M18 18.75H6A2.25 2.25 0 013.75 16.5v-7.5A2.25 2.25 0 016 6.75h12a2.25 2.25 0 012.25 2.25v7.5A2.25 2.25 0 0118 18.75z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-bold text-xs text-gray-800 dark:text-gray-200 truncate"
                                                 x-text="prod.name"></div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span
                                                    class="text-[11px] font-black text-emerald-600 dark:text-emerald-400 tabular-nums"
                                                    x-text="formatMoney(prod.price) + ' {{ $currencyLabel }}'"></span>
                                            </div>
                                            <div class="mt-2">
                                                <span class="text-[9px] font-bold px-2 py-1 rounded-md"
                                                      :class="prod.stock > 0 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400'"
                                                      x-text="prod.stock > 0 ? prod.stock + ' موجود' : 'ناموجود'"></span>
                                            </div>
                                        </div>
                                        <div
                                            class="shrink-0 w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('invoiceCreator', () => ({
                    issueDate: @json($issueDateValue),
                    dueDate: @json($dueDateValue),
                    notesText: @json(old('notes', $invoice->notes)),

                    invoiceNumber: @json(old('invoice_number', $invoice->invoice_number)),
                    invoiceAuto: @json($invoiceAuto ?? false),
                    invoiceNumberUnlocked: false,

                    proformaInvoiceNumber: @json(old('proforma_invoice_number', $invoice->proforma_invoice_number)),
                    proformaInvoiceAuto: @json($proformaAuto ?? false),

                    servicesList: @json($services),
                    productsList: @json($products ?? []),
                    marketAttributesList: @json($marketAttributesForJs ?? []),
                    modalSelectedAttributes: {},
                    items: @json($existingItems),

                    taxPercent: @json(old('tax_percent', $invoice->tax_percent)),
                    taxUnlocked: false,
                    defaultTaxRate: @json($defaultTaxRate ?? 9),
                    taxMode: @json($taxMode ?? 'invoice'),
                    taxApplyCustomFields: @json($taxApplyCustomFields ?? false),
                    servicesRoundingMode: @json($servicesRoundingMode ?? 'none'),
                    servicesRoundingFactor: parseInt(@json($servicesRoundingFactor ?? 1000)) || 1000,

                    extraDiscountType: @json(old('extra_discount_type', $invoice->extra_discount_type ?? 'amount')),
                    extraDiscountValue: @json(old('extra_discount_value', $invoice->extra_discount_value ?? 0)),

                    selectedCustomer: @json(old('customer_id', $invoice->customer_id)),
                    selectedCustomerData: null,
                    customersList: @json($customersListForJs),
                    customerQuery: '',
                    customerDropdownOpen: false,

                    periodLabels: {monthly: 'ماهانه', quarterly: 'فصلی', semi_annual: 'شش ماهه', annual: 'سالانه'},

                    convertInvoiceNumber: '',

                    activeProductModalIndex: null,
                    modalSelectedMasterProduct: '',
                    modalSelectedBrand: '',
                    modalStockStatus: 'all',
                    modalSelectedAttributes: {},

                    onConvertSubmit(e) {
                        if (!this.invoiceAuto) {
                            let n = prompt('سیستم روی شماره‌گذاری دستی تنظیم شده است. لطفاً شماره فاکتور جدید را وارد کنید:');
                            if (!n || n.trim() === '') {
                                e.preventDefault();
                                alert('وارد کردن شماره فاکتور الزامی است.');
                                return;
                            }
                            this.convertInvoiceNumber = n;
                        }
                    },

                    gregorianToJalali(date) {
                        try {
                            const f = new Intl.DateTimeFormat('en-US', {
                                calendar: 'persian',
                                year: 'numeric',
                                month: '2-digit',
                                day: '2-digit'
                            });
                            let p = f.formatToParts(date);
                            let y = p.find(p => p.type === 'year').value;
                            let m = p.find(p => p.type === 'month').value;
                            let d = p.find(p => p.type === 'day').value;
                            return `${y}/${m.toString().padStart(2, '0')}/${d.toString().padStart(2, '0')}`;
                        } catch (e) {
                            return '';
                        }
                    },
                    setDefaultDates() {
                        const t = this.gregorianToJalali(new Date());
                        if (!this.issueDate) this.issueDate = t;
                        if (!this.dueDate) this.dueDate = this.issueDate || t;
                        this.forceSyncDateInputs();
                    },
                    forceSyncDateInputs() {
                        const s = () => {
                            this.syncDateInput('issue_date', this.issueDate);
                            this.syncDateInput('due_date', this.dueDate);
                        };
                        this.$nextTick(s);
                        setTimeout(s, 150);
                        setTimeout(s, 500);
                        setTimeout(s, 1000);
                    },
                    syncDateInput(n, v) {
                        const e = document.querySelector(`input[name="${n}"]`);
                        if (e && v) {
                            e.value = v;
                            e.dispatchEvent(new Event('input', {bubbles: true}));
                            e.dispatchEvent(new Event('change', {bubbles: true}));
                        }
                    },
                    checkDateValidity() {
                        if (this.issueDate && this.dueDate) {
                            let i = this.toEnglishNum(this.issueDate).replace(/\//g, '');
                            let d = this.toEnglishNum(this.dueDate).replace(/\//g, '');
                            if (d < i) {
                                alert('تاریخ سررسید نمی‌تواند قبل از تاریخ صدور باشد!');
                                this.dueDate = this.issueDate;
                            }
                        }
                    },
                    setDueDate(t) {
                        if (!this.issueDate) {
                            alert('ابتدا تاریخ صدور را انتخاب کنید.');
                            return;
                        }
                        this.dueDate = this.addJalali(this.issueDate, 1, t);
                        this.checkDateValidity();
                        this.$nextTick(() => {
                            let e = document.querySelector('input[name="due_date"]');
                            if (e) {
                                e.value = this.dueDate;
                                e.dispatchEvent(new Event('input', {bubbles: true}));
                                e.dispatchEvent(new Event('change', {bubbles: true}));
                            }
                        });
                    },
                    addJalali(j, a, t) {
                        if (!j) return '';
                        let e = this.toEnglishNum(j);
                        let p = e.split('/').map(Number);
                        if (p.length !== 3) return '';
                        let [y, m, d] = p;
                        if (t === 'year') y += a; else if (t === 'month') {
                            m += a;
                            while (m > 12) {
                                m -= 12;
                                y++;
                            }
                        } else if (t === 'week') {
                            d += 7 * a;
                            while (true) {
                                let mD = (m <= 6) ? 31 : (m <= 11 ? 30 : 29);
                                if (d <= mD) break;
                                d -= mD;
                                m++;
                                if (m > 12) {
                                    m = 1;
                                    y++;
                                }
                            }
                        }
                        let mD = (m <= 6) ? 31 : (m <= 11 ? 30 : 29);
                        if (d > mD) d = mD;
                        return `${y}/${m.toString().padStart(2, '0')}/${d.toString().padStart(2, '0')}`;
                    },

                    get filteredCustomers() {
                        if (!this.customerQuery) return [];
                        const q = this.customerQuery.toLowerCase();
                        return (this.customersList || []).filter(c => (c.name || '').toLowerCase().includes(q) || (c.email || '').toLowerCase().includes(q) || (c.phone || '').toLowerCase().includes(q) || String(c.id).includes(q)).slice(0, 8);
                    },
                    selectCustomer(c) {
                        this.selectedCustomer = c.id;
                        this.selectedCustomerData = c;
                        this.customerQuery = '';
                        this.customerDropdownOpen = false;
                    },
                    clearCustomer() {
                        this.selectedCustomer = '';
                        this.selectedCustomerData = null;
                        this.customerQuery = '';
                    },
                    onExtraDiscountInput(e) {
                        let v = e.target.value;
                        let n = this.parsePriceInput(v);
                        if (this.extraDiscountType === 'percent') {
                            if (n > 100) n = 100;
                            if (n < 0) n = 0;
                            e.target.value = this.toPersianNum(n);
                        } else {
                            e.target.value = this.formatPriceInput(n);
                        }
                        this.extraDiscountValue = n;
                    },
                    toggleExtraDiscountType() {
                        this.extraDiscountValue = 0;
                        this.extraDiscountType = this.extraDiscountType === 'amount' ? 'percent' : 'amount';
                    },
                    init() {
                        this.setDefaultDates();
                        if (this.selectedCustomer) {
                            const c = this.customersList.find(c => c.id == this.selectedCustomer);
                            if (c) this.selectedCustomerData = c;
                        }
                        this.forceSyncDateInputs();
                        this.$watch('issueDate', () => {
                            this.items.forEach((i, idx) => {
                                if (i.mode === 'service' && i.service_raw && i.service_raw.billing_type === 'recurring' && i.billing_period) this.updatePriceForPeriod(idx);
                            });
                        });
                        this.$nextTick(() => {
                            if (typeof jalaliDatepicker !== 'undefined') jalaliDatepicker.startWatch();
                        });
                        document.addEventListener('jdp:change', (e) => {
                            e.target.dispatchEvent(new Event('input', {bubbles: true}));
                        });
                    },

                    addItem(m = 'service') {
                        this.items.push({
                            mode: m,
                            service_id: '',
                            product_id: '',
                            product_variant_id: '',
                            service_raw: null,
                            custom_service_name: '',
                            _showServiceDropdown: false,
                            _showProductDropdown: false,
                            _selectedGroup: '',
                            description: '',
                            unit: 'عدد',
                            quantity: 1,
                            unit_price: 0,
                            discount: 0,
                            billing_period: '',
                            _priceUnlocked: false,
                            service_custom_fields: [],
                            custom_field_values: {},
                            _showCustomFields: false,
                            custom_field_custom_prices: {},
                            custom_field_custom_discounts: {},
                            custom_field_tax_percents: {},
                            tax_percent: this.defaultTaxRate,
                            _taxUnlocked: false
                        });
                    },
                    removeItem(i) {
                        this.items.splice(i, 1);
                    },

                    openProductModal(index) {
                        const currentMaster = this.items[index].product_id;
                        this.activeProductModalIndex = index;
                        if (currentMaster) {
                            if (currentMaster !== this.modalSelectedMasterProduct) {
                                this.modalSelectedMasterProduct = currentMaster;
                                this.modalSelectedBrand = '';
                                this.modalStockStatus = 'all';
                                this.modalSelectedAttributes = {};
                            }
                        } else {
                            this.modalSelectedMasterProduct = '';
                            this.modalSelectedBrand = '';
                            this.modalStockStatus = 'all';
                            this.modalSelectedAttributes = {};
                        }
                    },
                    closeProductModal() {
                        this.activeProductModalIndex = null;
                    },
                    get modalFilterProducts() {
                        const p = this.productsList || [];
                        const m = {};
                        p.forEach(i => {
                            if (i.master_id && i.master_title) m[i.master_id] = i.master_title;
                        });
                        return Object.keys(m).map(id => ({id: id, name: m[id]}));
                    },
                    get modalProductAttributes() {
                        const sP = String(this.modalSelectedMasterProduct || '');
                        if (!sP) return [];
                        const attrs = {};
                        (this.productsList || []).forEach(p => {
                            if (String(p.master_id) === sP && p.attributes) {
                                for (const [key, val] of Object.entries(p.attributes)) {
                                    if (key === 'name' && val === 'استاندارد') continue;
                                    if (!attrs[key]) attrs[key] = new Set();
                                    if (val) attrs[key].add(val);
                                }
                            }
                        });
                        return Object.keys(attrs).map(key => ({
                            name: key,
                            values: Array.from(attrs[key])
                        }));
                    },
                    get modalCategories() {
                        const sP = String(this.modalSelectedMasterProduct || '');
                        const sS = this.modalStockStatus || 'all';
                        const selAttrs = this.modalSelectedAttributes || {};

                        let fP = (this.productsList || []).filter(p => {
                            if (sP && String(p.master_id) !== sP) return false;
                            if (sS === 'in_stock' && (!p.stock || p.stock <= 0)) return false;
                            if (sS === 'out_of_stock' && p.stock > 0) return false;

                            for (const key in selAttrs) {
                                const val = selAttrs[key];
                                if (!val || (Array.isArray(val) && val.length === 0)) continue;
                                const pAttrs = p.attributes || {};
                                const pVal = String(pAttrs[key] ?? '');
                                if (Array.isArray(val)) {
                                    if (!val.includes(pVal)) return false;
                                } else {
                                    if (pVal !== String(val)) return false;
                                }
                            }
                            return true;
                        });

                        const cM = {};
                        fP.forEach(p => {
                            const k = p.category_id + '_' + p.category_name;
                            if (!cM[k]) cM[k] = {id: p.category_id, name: p.category_name, products: []};
                            cM[k].products.push(p);
                        });
                        Object.values(cM).forEach(c => c.products.sort((a, b) => (b.stock > 0) - (a.stock > 0)));
                        return Object.values(cM);
                    },
                    selectProductFromModal(prod) {
                        const i = this.activeProductModalIndex;
                        if (i === null) return;
                        this.items[i].product_id = String(prod.master_id || prod.id);
                        this.items[i].product_variant_id = prod.variant_id ? String(prod.variant_id) : '';
                        this.items[i].stock = prod.stock !== undefined ? Number(prod.stock) : null;
                        this.items[i].single_sell = !!prod.single_sell;
                        if (this.items[i].single_sell && this.items[i].quantity > 1) this.items[i].quantity = 1;
                        this.items[i].service_id = '';
                        this.items[i].service_raw = null;
                        this.items[i].custom_service_name = prod.name;
                        this.items[i].unit = prod.unit || 'عدد';
                        this.items[i].unit_price = Number(prod.price) || 0;
                        this.items[i]._priceUnlocked = true;
                        this.items[i].description = '';
                        this.items[i].service_custom_fields = [];
                        this.items[i].custom_field_values = {};
                        this.items[i]._showCustomFields = false;
                        this.items[i].custom_field_custom_prices = {};
                        this.items[i].custom_field_custom_discounts = {};
                        this.items[i].custom_field_tax_percents = {};
                        this.closeProductModal();
                    },

                    onProductInput(i) {
                        if (this.items[i].product_id) {
                            this.items[i].product_id = '';
                            this.items[i].product_variant_id = '';
                            this.items[i].unit_price = 0;
                            this.items[i].stock = null;
                            this.items[i]._priceUnlocked = false;
                        }
                        this.items[i]._showProductDropdown = true;
                    },
                    filteredProducts(i) {
                        const q = (this.items[i].custom_service_name || '').trim().toLowerCase();
                        if (q.length < 2) return [];
                        const terms = q.split(/\s+/).filter(t => t.length > 0);
                        const uniqueMasters = new Set();
                        return (this.productsList || []).filter(p => {
                            if (!p.master_id) return false;
                            const target = (p.master_title || p.name || '') + ' ' + (p.search_text || '');
                            const targetLower = ' ' + target.toLowerCase().replace(/[-_]/g, ' ');
                            const match = terms.every(t => targetLower.includes(' ' + t));
                            if (match && !uniqueMasters.has(p.master_id)) {
                                uniqueMasters.add(p.master_id);
                                return true;
                            }
                            return false;
                        }).map(p => {
                            return {...p, name: p.master_title || p.name};
                        }).slice(0, 8);
                    },
                    selectProductInline(i, prod) {
                        this.items[i].custom_service_name = prod.master_title || prod.name;
                        this.items[i]._showProductDropdown = false;
                        
                        const variants = (this.productsList || []).filter(p => String(p.master_id) === String(prod.master_id));
                        if (variants.length === 1) {
                            this.activeProductModalIndex = i;
                            this.selectProductFromModal(variants[0]);
                        } else {
                            this.activeProductModalIndex = i;
                            this.modalSelectedMasterProduct = String(prod.master_id);
                            this.modalStockStatus = 'all';
                            this.modalSelectedAttributes = {};
                        }
                    },

                    onServiceInput(i) {
                        if (this.items[i].service_id) {
                            this.items[i].service_id = '';
                            this.items[i].service_raw = null;
                            this.items[i].unit_price = 0;
                            this.items[i].description = '';
                            this.items[i]._priceUnlocked = false;
                            this.items[i].service_custom_fields = [];
                            this.items[i].custom_field_values = {};
                            this.items[i]._showCustomFields = false;
                            this.items[i].custom_field_custom_prices = {};
                            this.items[i].custom_field_custom_discounts = {};
                            this.items[i].custom_field_tax_percents = {};
                        }
                        this.items[i]._showServiceDropdown = true;
                    },
                    filteredServices(i) {
                        const q = (this.items[i].custom_service_name || '').trim().toLowerCase();
                        if (!q) return [];
                        return this.servicesList.filter(s => (s.name || '').toLowerCase().includes(q)).slice(0, 8);
                    },
                    selectService(i, s) {
                        let rF = s.customFields || s.custom_fields || [];
                        let f = rF.filter(f => f.show_in_invoice === true || f.show_in_invoice === 1 || String(f.show_in_invoice) === '1' || f.show_in_invoice === undefined).map(f => {
                            let f2 = {...f};
                            if (typeof f2.options === 'string') {
                                try {
                                    f2.options = JSON.parse(f2.options);
                                } catch (e) {
                                    f2.options = [];
                                }
                            }
                            if (!Array.isArray(f2.options)) f2.options = [];
                            return f2;
                        });
                        let cV = {};
                        f.forEach(f => {
                            if (f.type === 'checkbox') cV[f.id] = false; else if (f.type === 'multiselect') cV[f.id] = []; else cV[f.id] = '';
                        });
                        let sU = s.has_unit_pricing ? (s.unit_name || 'عدد') : 'عدد';
                        this.items[i].service_id = String(s.id);
                        this.items[i].service_raw = s;
                        this.items[i].custom_service_name = s.name;
                        this.items[i]._showServiceDropdown = false;
                        this.items[i].unit = sU;
                        this.items[i].billing_period = (s.billing_type === 'recurring') ? '' : null;
                        this.items[i]._priceUnlocked = false;
                        this.items[i].description = '';
                        this.items[i].service_custom_fields = f;
                        this.items[i].custom_field_values = cV;
                        this.items[i]._showCustomFields = f.length > 0;
                        this.items[i].custom_field_custom_prices = {};
                        this.items[i].custom_field_custom_discounts = {};
                        this.items[i].custom_field_tax_percents = {};
                        this.updatePriceForPeriod(i);
                    },
                    getPeriodPrice(i) {
                        if (!i.service_raw || i.service_raw.billing_type !== 'recurring' || !i.billing_period) return 0;
                        const r = i.service_raw.renewal_prices || {};
                        return Number(r[i.billing_period] || 0);
                    },
                    updatePriceForPeriod(i) {
                        const it = this.items[i];
                        const s = it.service_raw;
                        if (!s) return;
                        let p = 0;
                        if (s.has_unit_pricing) p = Number(s.unit_price) || 0; else p = Number(s.base_price) || 0;
                        if (s.billing_type === 'recurring' && it.billing_period) p += this.getPeriodPrice(it);
                        it.unit_price = p;
                        if (s.billing_type === 'recurring' && it.billing_period) {
                            let aA = 0;
                            let aT = '';
                            if (it.billing_period === 'monthly') {
                                aA = 1;
                                aT = 'month';
                            } else if (it.billing_period === 'quarterly') {
                                aA = 3;
                                aT = 'month';
                            } else if (it.billing_period === 'semi_annual') {
                                aA = 6;
                                aT = 'month';
                            } else if (it.billing_period === 'annual') {
                                aA = 1;
                                aT = 'year';
                            }
                            if (aT && this.issueDate) {
                                let rD = this.addJalali(this.issueDate, aA, aT);
                                it.description = this.issueDate + ' تا ' + rD;
                            }
                        }
                    },
                    toggleMultiselect(it, fId, o, c) {
                        if (!Array.isArray(it.custom_field_values[fId])) it.custom_field_values[fId] = [];
                        const a = it.custom_field_values[fId];
                        const i = a.indexOf(o);
                        if (c && i === -1) a.push(o);
                        if (!c && i !== -1) a.splice(i, 1);
                    },
                    isFieldSelected(f, v) {
                        if (f.type === 'checkbox') return (v === true || v === '1' || v === 1);
                        if (f.type === 'multiselect') return Array.isArray(v) && v.length > 0;
                        return (v !== '' && v !== null && v !== undefined);
                    },
                    getFieldValueLabel(f, v) {
                        if (!this.isFieldSelected(f, v)) return '';
                        if (f.type === 'checkbox') return 'انتخاب شده';
                        if (f.type === 'multiselect' && Array.isArray(v)) return v.join('، ');
                        return v;
                    },
                    getCustomFieldPrice(it, f) {
                        if (it.custom_field_custom_prices && it.custom_field_custom_prices[f.id] !== undefined) return Number(it.custom_field_custom_prices[f.id]);
                        let p = parseFloat(it.unit_price) || 0;
                        let a = Number(f.pricing_amount) || 0;
                        return f.pricing_type === 'percentage' ? p * (a / 100) : a;
                    },
                    getCustomFieldDiscount(it, f) {
                        if (it.custom_field_custom_discounts && it.custom_field_custom_discounts[f.id] !== undefined) return Number(it.custom_field_custom_discounts[f.id]);
                        return 0;
                    },
                    getCustomFieldRowTotal(it, f) {
                        let q = parseFloat(it.quantity) || 0;
                        let cP = this.getCustomFieldPrice(it, f) * q;
                        let cD = this.getCustomFieldDiscount(it, f);
                        let cT = Math.max(0, cP - cD);
                        if (this.taxMode === 'item' && this.taxApplyCustomFields) {
                            let cTP = it.custom_field_tax_percents?.[f.id] ?? this.defaultTaxRate;
                            let t = cT * ((Number(cTP) || 0) / 100);
                            return cT + t;
                        }
                        return cT;
                    },
                    calculateRowTotal(it) {
                        let q = parseFloat(it.quantity) || 0;
                        let p = parseFloat(it.unit_price) || 0;
                        let d = parseFloat(it.discount) || 0;
                        let rB = p * q;
                        let mT = Math.max(0, rB - d);
                        let rT = 0;
                        if (this.taxMode === 'item') rT += mT * ((Number(it.tax_percent) || 0) / 100);
                        let cG = 0;
                        let cD = 0;
                        if (it.service_custom_fields && it.custom_field_values) {
                            it.service_custom_fields.forEach(f => {
                                if (f.has_pricing && this.isFieldSelected(f, it.custom_field_values[f.id])) {
                                    let cP = this.getCustomFieldPrice(it, f) * q;
                                    let cD2 = this.getCustomFieldDiscount(it, f);
                                    cG += cP;
                                    cD += cD2;
                                    if (this.taxMode === 'item' && this.taxApplyCustomFields) {
                                        let cT = Math.max(0, cP - cD2);
                                        let cTP = it.custom_field_tax_percents?.[f.id] ?? this.defaultTaxRate;
                                        rT += cT * ((Number(cTP) || 0) / 100);
                                    }
                                }
                            });
                        }
                        let tT = Math.max(0, rB - d) + Math.max(0, cG - cD);
                        if (this.taxMode === 'item') return tT + rT;
                        return tT;
                    },
                    get totals() {
                        let bS = 0, iD = 0, tC = 0, iTT = 0;
                        this.items.forEach(it => {
                            let q = parseFloat(it.quantity) || 0;
                            let p = parseFloat(it.unit_price) || 0;
                            let d = parseFloat(it.discount) || 0;
                            let cP = 0;
                            let cD = 0;
                            let rB = p * q;
                            bS += rB;
                            iD += d;
                            if (this.taxMode === 'item') {
                                let mT = Math.max(0, rB - d);
                                iTT += mT * ((Number(it.tax_percent) || 0) / 100);
                            }
                            if (it.service_custom_fields && it.custom_field_values) {
                                it.service_custom_fields.forEach(f => {
                                    if (f.has_pricing && this.isFieldSelected(f, it.custom_field_values[f.id])) {
                                        let cP2 = this.getCustomFieldPrice(it, f) * q;
                                        let cD2 = this.getCustomFieldDiscount(it, f);
                                        cP += cP2;
                                        cD += cD2;
                                        if (this.taxMode === 'item' && this.taxApplyCustomFields) {
                                            let cT = Math.max(0, cP2 - cD2);
                                            let cTP = it.custom_field_tax_percents?.[f.id] ?? this.defaultTaxRate;
                                            iTT += cT * ((Number(cTP) || 0) / 100);
                                        }
                                    }
                                });
                            }
                            tC += cP;
                            iD += cD;
                        });
                        let s = bS + tC;
                        let aI = Math.max(0, s - iD);
                        let eD = 0;
                        if (this.extraDiscountType === 'percent') eD = aI * ((Number(this.extraDiscountValue) || 0) / 100); else eD = Number(this.extraDiscountValue) || 0;
                        eD = Math.max(0, Math.min(eD, aI));
                        let tA = Math.max(0, aI - eD);
                        let tT = this.taxMode === 'item' ? Math.max(0, iTT) : tA * ((Number(this.taxPercent) || 0) / 100);
                        let gT = tA + tT;
                        let uG = Math.round(gT);
                        let rG = uG;
                        let rD = 0;
                        let iR = false;
                        if (this.servicesRoundingMode === 'up' && this.servicesRoundingFactor > 0) {
                            rG = Math.ceil(gT / this.servicesRoundingFactor) * this.servicesRoundingFactor;
                            rD = rG - uG;
                            iR = rD !== 0;
                        } else if (this.servicesRoundingMode === 'down' && this.servicesRoundingFactor > 0) {
                            rG = Math.floor(gT / this.servicesRoundingFactor) * this.servicesRoundingFactor;
                            rD = rG - uG;
                            iR = rD !== 0;
                        }
                        return {
                            baseSubtotal: Math.max(0, bS),
                            customFieldsTotal: Math.max(0, tC),
                            subtotal: Math.max(0, s),
                            itemsDiscount: Math.max(0, iD),
                            extraDiscount: Math.max(0, eD),
                            discount: Math.max(0, iD + eD),
                            tax: Math.max(0, tT),
                            unroundedGrand: Math.max(0, uG),
                            grand: Math.max(0, rG),
                            roundingDiff: rD,
                            isRounded: iR,
                            roundingMode: this.servicesRoundingMode,
                            roundingFactor: this.servicesRoundingFactor
                        };
                    },
                    get appliedCustomFieldsSummary() {
                        let s = {};
                        this.items.forEach(it => {
                            let q = parseFloat(it.quantity) || 0;
                            if (it.service_custom_fields && it.custom_field_values) {
                                it.service_custom_fields.forEach(f => {
                                    if (f.has_pricing && this.isFieldSelected(f, it.custom_field_values[f.id])) {
                                        let fP = this.getCustomFieldPrice(it, f);
                                        let fD = this.getCustomFieldDiscount(it, f);
                                        let v = it.custom_field_values[f.id];
                                        let l = f.label;
                                        if (['select', 'radio'].includes(f.type)) l += ` (${v})`;
                                        if (f.type === 'multiselect' && Array.isArray(v)) l += ` (${v.join('، ')})`;
                                        if (!s[l]) s[l] = 0;
                                        s[l] += Math.max(0, (fP * q) - fD);
                                    }
                                });
                            }
                        });
                        return Object.keys(s).map(k => ({label: k, amount: s[k]}));
                    },
                    formatMoney(v) {
                        return new Intl.NumberFormat('fa-IR').format(Math.round(v));
                    },
                    formatPriceInput(v) {
                        if (v === '' || v === null || v === undefined) return '';
                        let n = this.toEnglishNum(v.toString()).replace(/[^\d]/g, '');
                        if (!n) return '';
                        return this.toPersianNum(Number(n).toLocaleString('en-US'));
                    },
                    parsePriceInput(v) {
                        let n = this.toEnglishNum(v.toString()).replace(/[^\d]/g, '');
                        return n ? Number(n) : 0;
                    },
                    toPersianNum(v) {
                        if (v === '' || v === null || v === undefined) return '';
                        const d = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                        return v.toString().replace(/\d/g, n => d[n]);
                    },
                    toEnglishNum(v) {
                        if (v === '' || v === null || v === undefined) return '';
                        const p = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                        const a = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
                        return v.toString().replace(/[۰-۹]/g, d => p.indexOf(d)).replace(/[٠-٩]/g, d => a.indexOf(d));
                    },
                    onSubmitCheck(e) {
                        const f = e.target;
                        if (!this.selectedCustomer) {
                            e.preventDefault();
                            alert('انتخاب مشتری الزامی است.');
                            return;
                        }
                        const nI = @json($isProforma) ? f.querySelector('input[name="proforma_invoice_number"]') : f.querySelector('input[name="invoice_number"]');
                        if (nI && !nI.value.trim()) {
                            e.preventDefault();
                            alert(`وارد کردن شماره {{ $isProforma ? 'پیش فاکتور' : 'فاکتور' }} الزامی است.`);
                            nI.focus();
                            return;
                        }
                        for (let i = 0; i < this.items.length; i++) {
                            const it = this.items[i];
                            if ((it.product_id || it.product_variant_id) && it.stock !== null && it.stock !== undefined) {
                                const q = parseFloat(it.quantity) || 0;
                                if (q > Number(it.stock)) {
                                    e.preventDefault();
                                    alert(`تعداد درخواستی برای ردیف ${i + 1} (${it.custom_service_name || 'محصول فروشگاه'}) بیش از موجودی انبار است (موجودی فعلی: ${it.stock} عدد).`);
                                    return;
                                }
                            }
                        }
                        const nF = f.querySelectorAll('input[name*="[quantity]"], input[name*="[unit_price]"], input[name*="[discount]"], input[name*="[custom_fields_prices]"], input[name*="[custom_fields_discounts]"], input[name*="[tax_percent]"], input[name*="[custom_fields_taxes]"], input[name="tax_percent"]');
                        nF.forEach(i => {
                            i.value = this.toEnglishNum(i.value).replace(/[^\d.]/g, '');
                        });
                    },
                }));
            });
        </script>
    @endpush
@endsection
