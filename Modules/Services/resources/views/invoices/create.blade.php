@php
    $isInvoice = ($type ?? 'proforma') === 'invoice';
    $pageTitle = $isInvoice ? 'ثبت فاکتور جدید' : 'ثبت پیش فاکتور جدید';
@endphp

@extends('layouts.user')
@section('title', $pageTitle)

@include('partials.jalali-date-picker')

@php
    use Modules\Clients\Entities\ClientForm;
    use Modules\Clients\Entities\ClientSetting;
    use Modules\Services\App\Http\Models\Status;

    if (!function_exists('extractClientFieldSubItems')) {
        function extractClientFieldSubItems($client, string $fieldId, ?array $activeFormSchema = null): array {
            $items = [];

            if ($fieldId === 'address' || $fieldId === 'addresses') {
                if ($client->addresses && $client->addresses->count() > 0) {
                    foreach ($client->addresses as $addr) {
                        $parts = array_filter([
                            $addr->title ? "{$addr->title}:" : null,
                            $addr->province,
                            $addr->city,
                            $addr->address,
                            $addr->postal_code ? "(کدپستی: {$addr->postal_code})" : null,
                        ]);
                        $items[] = implode(' ', $parts);
                    }
                }
            }

            $systemFields = ClientForm::getSystemFields();
            if (isset($systemFields[$fieldId])) {
                $col = $systemFields[$fieldId]['column'];
                $val = $col === 'status_id' ? ($client->status->name ?? null) : ($client->{$col} ?? null);
                if ($val) {
                    if (is_array($val)) {
                        $items = array_merge($items, $val);
                    } else {
                        $split = preg_split('/[\n\r\|::]+/u', (string)$val);
                        if (count($split) > 1) {
                            $items = array_merge($items, array_map('trim', $split));
                        } else {
                            $commaSplit = preg_split('/[،,]+/u', (string)$val);
                            if (count($commaSplit) > 1) {
                                $items = array_merge($items, array_map('trim', $commaSplit));
                            } else {
                                $items[] = trim((string)$val);
                            }
                        }
                    }
                }
            }

            $metaVal = ($client->meta ?? [])[$fieldId] ?? null;
            if ($metaVal !== null && $metaVal !== '') {
                if (is_array($metaVal)) {
                    foreach ($metaVal as $m) {
                        if (is_scalar($m)) $items[] = (string)$m;
                        elseif (is_array($m)) $items[] = implode(' - ', array_filter($m));
                    }
                } else {
                    $str = (string)$metaVal;
                    if (str_starts_with(trim($str), '[') && str_ends_with(trim($str), ']')) {
                        $decoded = json_decode($str, true);
                        if (is_array($decoded)) {
                            foreach ($decoded as $d) {
                                if (is_scalar($d)) $items[] = (string)$d;
                            }
                        }
                    } else {
                        $split = preg_split('/[\n\r\|::]+/u', $str);
                        if (count($split) > 1) {
                            $items = array_merge($items, array_map('trim', $split));
                        } else {
                            $commaSplit = preg_split('/[،,]+/u', $str);
                            if (count($commaSplit) > 1) {
                                $items = array_merge($items, array_map('trim', $commaSplit));
                            } else {
                                $items[] = trim($str);
                            }
                        }
                    }
                }
            }

            if ($activeFormSchema && !empty($activeFormSchema['fields'])) {
                foreach ($activeFormSchema['fields'] as $f) {
                    if (($f['id'] ?? '') === $fieldId) {
                        $rawOpts = $f['options'] ?? $f['options_json'] ?? null;
                        if ($rawOpts) {
                            $opts = is_array($rawOpts) ? $rawOpts : (json_decode($rawOpts, true) ?: []);
                            if (is_string($rawOpts) && empty($opts)) {
                                $opts = array_map('trim', preg_split('/[\n\r,،]+/u', $rawOpts));
                            }
                            if (is_array($opts) && count($opts) > 1 && empty($items)) {
                                foreach ($opts as $opt) {
                                    $items[] = is_array($opt) ? ($opt['label'] ?? $opt['value'] ?? '') : (string)$opt;
                                }
                            }
                        }
                    }
                }
            }

            return array_values(array_unique(array_filter(array_map('trim', $items))));
        }
    }

    $checkedClientFields = json_decode($settings['services_invoice_client_fields'] ?? '[]', true);
    if (!is_array($checkedClientFields) || empty($checkedClientFields)) {
        $checkedClientFields = ['full_name', 'phone', 'email', 'national_code', 'case_number'];
    }

    $activeForm = ClientForm::active(ClientSetting::getValue('default_form_key'));
    $formSchema = $activeForm ? $activeForm->schema : [];
    $allFieldsMap = [];
    foreach (ClientForm::systemFieldDefaults() as $fid => $f) {
        $allFieldsMap[$fid] = $f['label'] ?? $fid;
    }
    if ($activeForm && !empty($formSchema['fields'])) {
        foreach ($formSchema['fields'] as $f) {
            if (!empty($f['id'])) {
                $allFieldsMap[$f['id']] = $f['label'] ?? $f['id'];
            }
        }
    }
    $allFieldsMap['address'] = 'آدرس';
    $allFieldsMap['addresses'] = 'آدرس‌ها';

    $customersListForJs = $customers->map(function ($c) use ($checkedClientFields, $formSchema, $allFieldsMap) {
        $multiSubFields = [];
        foreach ($checkedClientFields as $fieldId) {
            $subItems = extractClientFieldSubItems($c, $fieldId, $formSchema);
            if (count($subItems) > 1) {
                $multiSubFields[] = [
                    'id' => $fieldId,
                    'label' => $allFieldsMap[$fieldId] ?? $fieldId,
                    'options' => $subItems,
                ];
            }
        }

        return [
            'id'       => $c->id,
            'name'     => $c->full_name,
            'email'    => $c->email,
            'phone'    => $c->phone ?? '',
            'username' => $c->username ?? '',
            'label'    => $c->full_name . ' - ' . ($c->email ?? $c->phone ?? ''),
            'multi_sub_fields' => $multiSubFields,
        ];
    })->values();

    $marketAttributesForJs = collect($marketAttributes ?? [])->map(function ($attr) {
        return [
            'id' => $attr->id,
            'name' => $attr->name,
            'values' => $attr->values->map(function ($v) {
                return ['id' => $v->id, 'value' => $v->value];
            })->values(),
        ];
    })->values();

    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-sm dark:border-gray-700 dark:bg-gray-900/50 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1";
    $cardClass  = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl";

    $currency      = $currency ?? 'toman';
    $currencyLabel = $currency === 'rial' ? 'ریال' : 'تومان';
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="invoiceCreator()">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                {{ $pageTitle }}
            </h1>
            <a href="{{ route('services.invoices.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت به لیست
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
              @keydown.enter="if ($event.target.tagName !== 'TEXTAREA' && $event.target.type !== 'submit') $event.preventDefault()"
              action="{{ route('services.invoices.store') }}"
              method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            <input type="hidden" name="invoice_type" value="{{ $type }}">
            @if(!empty($mergedFromIds))
                <input type="hidden" name="merged_from_invoice_ids" value="{{ $mergedFromIds }}">
            @endif
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
                        اطلاعات {{ $isInvoice ? 'فاکتور' : 'پیش فاکتور' }} و مشتری
                    </h2>
                </div>

                <div class="p-6 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            @if($isInvoice)
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
                            @else
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
                                                  x-text="[c.phone, c.email].filter(Boolean).join(' • ')"></span>
                                        </span>
                                    </button>
                                </template>
                            </div>
                            <p x-show="customerDropdownOpen && customerQuery && filteredCustomers.length === 0"
                               class="mt-2 text-xs text-gray-400 px-1">مشتری‌ای یافت نشد.</p>
                        </div>

                        <div x-show="selectedCustomer" x-transition class="max-w-xl space-y-3">
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
                                <button type="button" @click="clearCustomer()" x-show="!isMergeMode"
                                        class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white dark:bg-gray-800 text-xs font-bold text-gray-500 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4l16 16M20 4L4 20"/>
                                    </svg>
                                    تغییر
                                </button>
                            </div>

                            <template
                                x-if="selectedCustomerData?.multi_sub_fields && selectedCustomerData.multi_sub_fields.length > 0">
                                <div
                                    class="p-5 rounded-2xl border border-indigo-200 dark:border-indigo-800/60 bg-white dark:bg-gray-800 shadow-sm space-y-4">
                                    <div
                                        class="flex flex-wrap items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-2.5 gap-2">
                                        <div
                                            class="flex items-center gap-2 text-xs font-bold text-indigo-700 dark:text-indigo-300">
                                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                            </svg>
                                            <span>انتخاب زیرمجموعه‌های مشتری در فاکتور (چند انتخابی / Multi-Select)</span>
                                        </div>
                                        <span class="text-[10px] text-gray-400 font-medium">امکان انتخاب چند زیرمجموعه به صورت همزمان</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <template x-for="field in selectedCustomerData.multi_sub_fields"
                                                  :key="field.id">
                                            <div
                                                class="space-y-2 p-3 rounded-xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60">
                                                <div class="flex items-center justify-between">
                                                    <label
                                                        class="block text-xs font-bold text-gray-800 dark:text-gray-200"
                                                        x-text="field.label"></label>
                                                    <div class="flex items-center gap-2">
                                                        <button type="button"
                                                                @click="selectAllSubItems(field.id, field.options)"
                                                                class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                                                            انتخاب همه
                                                        </button>
                                                        <span class="text-[10px] text-gray-300">|</span>
                                                        <button type="button" @click="deselectAllSubItems(field.id)"
                                                                class="text-[10px] text-gray-400 hover:text-gray-600">
                                                            پاک کردن
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="max-h-40 overflow-y-auto space-y-1.5 pe-1">
                                                    <template x-for="(opt, idx) in field.options" :key="idx">
                                                        <label
                                                            class="flex items-center gap-2.5 p-1.5 rounded-lg hover:bg-white dark:hover:bg-gray-800 cursor-pointer transition-colors text-xs text-gray-700 dark:text-gray-300">
                                                            <input type="checkbox"
                                                                   :name="'client_selected_fields[' + field.id + '][]'"
                                                                   :value="opt"
                                                                   :checked="isSubItemChecked(field.id, opt)"
                                                                   @change="toggleSubItem(field.id, opt)"
                                                                   class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                                                            <span x-text="opt" class="min-w-0 truncate"></span>
                                                        </label>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ $cardClass }} relative"
                 :class="items.some(i => i._showServiceDropdown || i._showProductDropdown || i._hasOpenSelectDropdown) ? 'z-20 overflow-visible' : 'z-10 overflow-hidden'">
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
                        {{-- Inline Package Search (type-to-search, like service search) --}}
                        <div class="relative w-64" @click.outside="showPackageDropdown = false"
                             x-show="packagesList && packagesList.length > 0">
                            <div class="relative">
                                <svg
                                    class="w-4.5 h-4.5 absolute right-3 top-1/2 -translate-y-1/2 text-amber-500 pointer-events-none"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <input type="text" x-model="packageSearch"
                                       @focus="showPackageDropdown = true"
                                       @input.debounce.150ms="showPackageDropdown = true"
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/50 pr-9 pl-3 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 focus:border-amber-300 focus:ring-2 focus:ring-amber-300/20 outline-none transition-all shadow-sm"
                                       placeholder="جست و جوی پکیج..." autocomplete="off"
                                       @keydown.escape="showPackageDropdown = false">
                            </div>

                            <div
                                x-show="showPackageDropdown && packageSearch.trim().length > 0 && filteredPackagesList().length > 0"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                                style="display:none;"
                                class="absolute right-0 top-full mt-1 z-[200] w-80 max-h-80 overflow-y-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl">
                                <template x-for="pkg in filteredPackagesList()" :key="pkg.id">
                                    <button type="button"
                                            @click="selectPackage(pkg); showPackageDropdown = false; packageSearch = ''"
                                            class="w-full text-start px-4 py-3 hover:bg-amber-50 dark:hover:bg-amber-500/10 border-b border-gray-100 dark:border-gray-700/50 last:border-0 transition-colors group">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2 min-w-0">
                        <span
                            class="shrink-0 w-7 h-7 flex items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </span>
                                                <div class="min-w-0">
                                                    <span
                                                        class="font-bold text-sm text-gray-800 dark:text-white block truncate"
                                                        x-text="pkg.name"></span>
                                                    <span class="text-[10px] text-gray-400"
                                                          x-text="pkg.items.length + ' ردیف'"></span>
                                                </div>
                                            </div>
                                            <span
                                                class="shrink-0 text-xs font-black text-amber-600 dark:text-amber-400 tabular-nums"
                                                x-text="formatMoney(pkg.final_price) + ' {{ $currencyLabel }}'"></span>
                                        </div>
                                    </button>
                                </template>
                            </div>

                            <p x-show="showPackageDropdown && packageSearch.trim().length > 0 && filteredPackagesList().length === 0"
                               class="absolute right-0 top-full mt-1 z-[200] w-80 px-4 py-3 text-xs font-bold text-gray-400 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl">
                                هیچ پکیجی یافت نشد.
                            </p>
                        </div>
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
                     :class="items.some(i => i._showServiceDropdown || i._showProductDropdown || i._hasOpenSelectDropdown) ? 'overflow-visible' : 'overflow-x-auto'">
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
                                   :class="(item._showServiceDropdown || item._showProductDropdown || item._hasOpenSelectDropdown) ? 'relative z-50' : 'relative z-10'">
                            {{-- Package Group Header Row --}}
                            <template
                                x-if="item._packageGroupId && (index === 0 || items[index-1]._packageGroupId !== item._packageGroupId)">
                                <tr class="bg-amber-50/80 dark:bg-amber-900/10 border-t-2 border-amber-200 dark:border-amber-700/50">
                                    <td :colspan="taxMode === 'item' ? 8 : 7" class="px-4 py-2">
                                        <div class="flex items-center gap-3">
                                            <button type="button"
                                                    @click="collapsedPackages[item._packageGroupId] = !collapsedPackages[item._packageGroupId]"
                                                    class="shrink-0 flex items-center gap-2 text-amber-700 dark:text-amber-400 hover:text-amber-900 dark:hover:text-amber-300 transition-colors">
                                                <span
                                                    class="w-6 h-6 flex items-center justify-center rounded-md bg-amber-200/70 dark:bg-amber-500/20">
                                                    <svg class="w-3.5 h-3.5 transition-transform duration-200"
                                                         :class="collapsedPackages[item._packageGroupId] ? '' : 'rotate-90'"
                                                         fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                         stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </span>
                                                <span class="text-xs font-black" x-text="item._packageTitle"></span>
                                            </button>
                                            <span
                                                class="text-[10px] font-bold text-amber-500 dark:text-amber-500/70 bg-amber-100 dark:bg-amber-500/10 px-2 py-0.5 rounded-md"
                                                x-text="items.filter(i => i._packageGroupId === item._packageGroupId).length + ' ردیف'"></span>
                                            <span x-show="collapsedPackages[item._packageGroupId]"
                                                  class="text-[10px] font-bold text-gray-400">(مخفی شده)</span>
                                            <div class="ms-auto">
                                                <button type="button" @click="removePackage(item._packageGroupId)"
                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-500/10 border border-red-200 dark:border-red-500/30 transition-colors active:scale-95">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    حذف پکیج
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors group"
                                :class="(item._showServiceDropdown || item._showProductDropdown || item._hasOpenSelectDropdown) ? 'relative z-50' : 'relative z-10'"
                                x-show="!item._packageGroupId || !collapsedPackages[item._packageGroupId]">
                                <td class="px-4 py-3 align-top"
                                    :class="(item._showServiceDropdown || item._showProductDropdown || item._hasOpenSelectDropdown) ? 'overflow-visible' : ''">
                                    <input type="hidden" :name="'items[' + index + '][service_id]'"
                                           :value="item.service_id">
                                    <input type="hidden" :name="'items[' + index + '][product_id]'"
                                           :value="item.product_id || ''">
                                    <input type="hidden" :name="'items[' + index + '][product_variant_id]'"
                                           :value="item.product_variant_id || ''">
                                    <input type="hidden" :name="'items[' + index + '][_packageGroupId]'"
                                           :value="item._packageGroupId || ''">
                                    <input type="hidden" :name="'items[' + index + '][_packageTitle]'"
                                           :value="item._packageTitle || ''">
                                    <input type="hidden" :name="'items[' + index + '][_baseQuantity]'"
                                           :value="item._baseQuantity || ''">

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
                                                           @input.debounce.300ms="item.custom_service_name = $event.target.value; onProductInput(index);"
                                                           class="{{ $inputClass }} py-2.5 text-xs w-full"
                                                           placeholder="جستجوی محصول فروشگاه...">
                                                    <div
                                                        x-show="item._showProductDropdown && filteredProducts(index).length > 0"
                                                        x-transition
                                                        class="absolute z-[100] mt-1 w-full max-h-56 overflow-y-auto overscroll-contain bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl">
                                                        <template x-for="p in filteredProducts(index)" :key="p.id">
                                                            <button type="button" @click="selectProductInline(index, p)"
                                                                    class="w-full text-start px-4 py-3 text-xs hover:bg-emerald-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700/50 last:border-0 transition-colors">
                                                                <span
                                                                    class="font-bold text-gray-900 dark:text-white block"
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
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                                </svg>
                                                فروشگاه
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
                                                           @input.debounce.300ms="item.custom_service_name = $event.target.value; onServiceInput(index);"
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
                                                {{-- Locked badge only for merged items --}}
                                                <template x-if="item._isMerged">
                                                    <div
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 text-xs font-bold text-amber-700 dark:text-amber-400">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                        </svg>
                                                        <span
                                                            x-text="periodLabels[item.billing_period] || item.billing_period || 'دوره تعریف نشده'"></span>
                                                    </div>
                                                </template>
                                                {{-- Editable select for non-merged items --}}
                                                <template x-if="!item._isMerged">
                                                    <select x-model="item.billing_period"
                                                            @change="updatePriceForPeriod(index)"
                                                            class="{{ $inputClass }} py-2 text-xs">
                                                        <option value="">انتخاب دوره</option>
                                                        <template x-for="(label, period) in periodLabels" :key="period">
                                                            <option :value="period" x-text="label"></option>
                                                        </template>
                                                    </select>
                                                </template>
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
                                               @blur="if(typeof calculateTotals === 'function') calculateTotals();"
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
                                                   dir="ltr" maxlength="3" :readonly="!item._taxUnlocked">
                                            <span class="ms-1 text-xs">%</span>
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
                                    <div class="py-2">
                                        <span x-text="formatMoney(calculateRowTotal(item))"></span>
                                        <span
                                            class="text-[10px] font-normal text-gray-400 ms-1">{{ $currencyLabel }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center align-top">
                                    <button type="button" @click="removeItem(index)"
                                            class="mt-1 text-gray-300 hover:text-red-500 dark:hover:bg-red-500/10 hover:bg-red-50 rounded-lg p-2 transition-colors opacity-0 group-hover:opacity-100 focus:opacity-100"
                                            title="حذف ردیف">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <template x-if="item.service_custom_fields && item.service_custom_fields.length > 0">
                                <template x-for="field in item.service_custom_fields"
                                          :key="field.id + '_subrows_group'">
                                    <tbody class="contents">
                                    <template x-if="field.type === 'multiselect' && field.has_pricing">
                                        <template
                                            x-for="opt in (Array.isArray(item.custom_field_values[field.id]) ? item.custom_field_values[field.id] : [])"
                                            :key="field.id + '_' + opt + '_subrow'">
                                            <tr x-show="!item._packageGroupId || !collapsedPackages[item._packageGroupId]"
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
                                                            x-text="field.label + ': ' + opt"></span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-2.5 align-middle">
                                                        <span
                                                            class="inline-block text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100/70 dark:bg-gray-800/60 px-2.5 py-1 rounded-lg border border-gray-200/40 dark:border-gray-700/40"
                                                            x-text="opt"></span>
                                                </td>
                                                <td class="px-4 py-2.5 align-middle">
                                                    <input type="text"
                                                           :value="toPersianNum(getCustomFieldQuantity(item, field, opt))"
                                                           @input="setCustomFieldQuantity(item, field, opt, $event.target.value)"
                                                           :name="'items[' + index + '][custom_fields_quantities][' + field.id + '][' + opt + ']'"
                                                           class="{{ $inputClass }} py-1.5 text-xs text-center tabular-nums font-bold border-indigo-200 dark:border-indigo-800/60 shadow-none"
                                                           dir="ltr" placeholder="۱">
                                                </td>
                                                <td class="px-4 py-2.5 align-middle">
                                                    <div class="flex items-center gap-1.5 w-full">
                                                        <div class="relative w-full">
                                                            <input type="text"
                                                                   :value="formatPriceInput(getCustomFieldPrice(item, field, opt))"
                                                                   @input="setCustomFieldPrice(item, field, opt, $event.target.value)"
                                                                   :name="'items[' + index + '][custom_fields_prices][' + field.id + '][' + opt + ']'"
                                                                   :readonly="!isCustomPriceUnlocked(item, field, opt)"
                                                                   :class="!isCustomPriceUnlocked(item, field, opt) ? 'bg-gray-100 dark:bg-gray-900/50 cursor-not-allowed text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-800' : 'bg-white dark:bg-gray-900 border-indigo-300'"
                                                                   class="{{ $inputClass }} py-2 text-sm text-center tabular-nums font-black w-full pe-14 shadow-none"
                                                                   dir="ltr" placeholder="۰">
                                                            <span
                                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] font-bold text-gray-400 pointer-events-none">{{ $currencyLabel }}</span>
                                                        </div>
                                                        <button type="button"
                                                                @click="toggleCustomPriceUnlock(item, field, opt)"
                                                                class="shrink-0 p-1.5 rounded-lg border transition-colors"
                                                                :class="isCustomPriceUnlocked(item, field, opt) ? 'border-indigo-400 bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400' : 'border-gray-200 text-gray-400 hover:text-indigo-500 hover:border-indigo-300 dark:border-gray-700'"
                                                                title="ویرایش مبلغ گزینه">
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
                                                               :value="formatPriceInput(getCustomFieldDiscount(item, field, opt))"
                                                               @input="setCustomFieldDiscount(item, field, opt, $event.target.value)"
                                                               :name="'items[' + index + '][custom_fields_discounts][' + field.id + '][' + opt + ']'"
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
                                                                       :value="toPersianNum(getCustomFieldTax(item, field, opt))"
                                                                       @input="setCustomFieldTax(item, field, opt, $event.target.value)"
                                                                       :name="'items[' + index + '][custom_fields_taxes][' + field.id + '][' + opt + ']'"
                                                                       class="w-14 rounded-lg border-2 bg-amber-50 dark:bg-amber-900/20 px-2 py-1.5 text-xs text-center tabular-nums font-bold focus:ring-2 focus:ring-amber-500/20 outline-none transition-colors"
                                                                       :class="isCustomTaxUnlocked(item, field, opt) ? 'border-amber-400 text-amber-800 dark:text-amber-200 bg-white dark:bg-gray-900' : 'border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400 cursor-not-allowed opacity-80'"
                                                                       dir="ltr" maxlength="3"
                                                                       :readonly="!isCustomTaxUnlocked(item, field, opt)">
                                                                <span class="ms-1 text-xs">%</span>
                                                            </span>
                                                        <button type="button"
                                                                @click="toggleCustomTaxUnlock(item, field, opt)"
                                                                class="p-1.5 rounded-lg border-2 transition-all active:scale-95 shrink-0"
                                                                :class="isCustomTaxUnlocked(item, field, opt) ? 'border-amber-500 bg-amber-50 text-amber-600 dark:bg-amber-500/20 dark:border-amber-500/50 dark:text-amber-400 shadow-sm' : 'border-amber-200 text-amber-500 hover:text-amber-700 hover:border-amber-400 hover:bg-amber-50/50 dark:border-amber-700/50 dark:text-amber-500 dark:hover:bg-amber-900/30'"
                                                                :title="isCustomTaxUnlocked(item, field, opt) ? 'قفل کردن مالیات' : 'ویرایش دستی مالیات'">
                                                            <svg x-show="!isCustomTaxUnlocked(item, field, opt)"
                                                                 class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                                 stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                            </svg>
                                                            <svg x-show="isCustomTaxUnlocked(item, field, opt)"
                                                                 class="w-3.5 h-3.5" x-cloak fill="none"
                                                                 viewBox="0 0 24 24" stroke="currentColor"
                                                                 stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-2.5 align-middle tabular-nums font-black text-indigo-600 dark:text-indigo-400 text-center whitespace-nowrap text-sm">
                                                        <span
                                                            x-text="formatMoney(getCustomFieldRowTotal(item, field, opt))"></span>
                                                    <span
                                                        class="text-[10px] font-normal text-gray-400 ms-1">{{ $currencyLabel }}</span>
                                                </td>
                                                <td class="px-4 py-2.5 align-middle text-center">
                                                    <button type="button"
                                                            @click="removeMultiselectOption(item, field.id, opt)"
                                                            class="text-gray-300 hover:text-red-500 dark:hover:bg-red-500/10 hover:bg-red-50 rounded-lg p-1.5 transition-colors opacity-0 group-hover:opacity-100 focus:opacity-100"
                                                            title="حذف این گزینه">
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
                                    <template x-if="field.type !== 'multiselect' && field.has_pricing">
                                        <tr x-show="isFieldSelected(field, item.custom_field_values[field.id]) && (!item._packageGroupId || !collapsedPackages[item._packageGroupId])"
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
                                            <td class="px-4 py-2.5 align-middle max-w-[220px]">
                                                <div class="max-w-full overflow-hidden">
                                                    <span
                                                        class="block truncate text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100/70 dark:bg-gray-800/60 px-2.5 py-1 rounded-lg border border-gray-200/40 dark:border-gray-700/40 max-w-full"
                                                        :title="getFieldValueLabel(field, item.custom_field_values[field.id])"
                                                        x-text="getFieldValueLabel(field, item.custom_field_values[field.id])"></span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-2.5 align-middle">
                                                <input type="text"
                                                       :value="toPersianNum(getCustomFieldQuantity(item, field))"
                                                       @input="
                                                           setCustomFieldQuantity(item, field, null, $event.target.value);
                                                           if (field.type === 'number') {
                                                               item.custom_field_values[field.id] = parsePriceInput($event.target.value);
                                                           }
                                                       "
                                                       :name="'items[' + index + '][custom_fields_quantities][' + field.id + ']'"
                                                       class="{{ $inputClass }} py-1.5 text-xs text-center tabular-nums font-bold border-indigo-200 dark:border-indigo-800/60 shadow-none"
                                                       dir="ltr" placeholder="۱">
                                            </td>
                                            <td class="px-4 py-2.5 align-middle">
                                                <div class="flex items-center gap-1.5 w-full">
                                                    <div class="relative w-full">
                                                        <input type="text"
                                                               :value="formatPriceInput(getCustomFieldPrice(item, field))"
                                                               @input="setCustomFieldPrice(item, field, null, $event.target.value)"
                                                               :name="'items[' + index + '][custom_fields_prices][' + field.id + ']'"
                                                               :readonly="!isCustomPriceUnlocked(item, field)"
                                                               :class="!isCustomPriceUnlocked(item, field) ? 'bg-gray-100 dark:bg-gray-900/50 cursor-not-allowed text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-800' : 'bg-white dark:bg-gray-900 border-indigo-300'"
                                                               class="{{ $inputClass }} py-2 text-sm text-center tabular-nums font-black w-full pe-14 shadow-none"
                                                               dir="ltr" placeholder="۰">
                                                        <span
                                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] font-bold text-gray-400 pointer-events-none">{{ $currencyLabel }}</span>
                                                    </div>
                                                    <button type="button"
                                                            @click="toggleCustomPriceUnlock(item, field)"
                                                            class="shrink-0 p-1.5 rounded-lg border transition-colors"
                                                            :class="isCustomPriceUnlocked(item, field) ? 'border-indigo-400 bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400' : 'border-gray-200 text-gray-400 hover:text-indigo-500 hover:border-indigo-300 dark:border-gray-700'"
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
                                                           @input="setCustomFieldDiscount(item, field, null, $event.target.value)"
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
                                                                   :value="toPersianNum(getCustomFieldTax(item, field))"
                                                                   @input="setCustomFieldTax(item, field, null, $event.target.value)"
                                                                   :name="'items[' + index + '][custom_fields_taxes][' + field.id + ']'"
                                                                   class="w-14 rounded-lg border-2 bg-amber-50 dark:bg-amber-900/20 px-2 py-1.5 text-xs text-center tabular-nums font-bold focus:ring-2 focus:ring-amber-500/20 outline-none transition-colors"
                                                                   :class="isCustomTaxUnlocked(item, field) ? 'border-amber-400 text-amber-800 dark:text-amber-200 bg-white dark:bg-gray-900' : 'border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400 cursor-not-allowed opacity-80'"
                                                                   dir="ltr" maxlength="3"
                                                                   :readonly="!isCustomTaxUnlocked(item, field)">
                                                            <span class="ms-1 text-xs">%</span>
                                                        </span>
                                                    <button type="button"
                                                            @click="toggleCustomTaxUnlock(item, field)"
                                                            class="p-1.5 rounded-lg border-2 transition-all active:scale-95 shrink-0"
                                                            :class="isCustomTaxUnlocked(item, field) ? 'border-amber-500 bg-amber-50 text-amber-600 dark:bg-amber-500/20 dark:border-amber-500/50 dark:text-amber-400 shadow-sm' : 'border-amber-200 text-amber-500 hover:text-amber-700 hover:border-amber-400 hover:bg-amber-50/50 dark:border-amber-700/50 dark:text-amber-500 dark:hover:bg-amber-900/30'"
                                                            :title="isCustomTaxUnlocked(item, field) ? 'قفل کردن مالیات' : 'ویرایش دستی مالیات'">
                                                        <svg x-show="!isCustomTaxUnlocked(item, field)"
                                                             class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                        </svg>
                                                        <svg x-show="isCustomTaxUnlocked(item, field)"
                                                             class="w-3.5 h-3.5" x-cloak fill="none"
                                                             viewBox="0 0 24 24" stroke="currentColor"
                                                             stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-4 py-2.5 align-middle tabular-nums font-black text-indigo-600 dark:text-indigo-400 text-center whitespace-nowrap text-sm">
                                                    <span
                                                        x-text="formatMoney(getCustomFieldRowTotal(item, field))"></span>
                                                <span
                                                    class="text-[10px] font-normal text-gray-400 ms-1">{{ $currencyLabel }}</span>
                                            </td>
                                            <td class="px-4 py-2.5 align-middle text-center">
                                                <button type="button"
                                                        @click="clearFieldValue(item, field)"
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
                                    </tbody>
                                </template>
                            </template>
                            <tr x-show="(item.service_custom_fields && item.service_custom_fields.length > 0) && (!item._packageGroupId || !collapsedPackages[item._packageGroupId])"
                                :class="item._hasOpenSelectDropdown ? 'relative z-50' : 'relative z-10'">
                                <td colspan="8" class="p-0 border-0">
                                    <div x-show="item._showCustomFields"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 -translate-y-2"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-150"
                                         x-transition:leave-start="opacity-100 translate-y-0"
                                         x-transition:leave-end="opacity-0 -translate-y-2"
                                         class="bg-slate-50/70 dark:bg-slate-800/40 border-y border-slate-200/80 dark:border-slate-700/80 shadow-[inset_0_4px_6px_-4px_rgba(0,0,0,0.05)] relative"
                                         :class="item._hasOpenSelectDropdown ? 'z-50 overflow-visible' : 'z-0'">
                                        <div class="flex">
                                            <div
                                                class="w-1.5 bg-indigo-400/80 dark:bg-indigo-600/80 shadow-[2px_0_8px_rgba(99,102,241,0.2)]"></div>
                                            <div class="p-6 w-full">
                                                <div
                                                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                                    <template x-for="field in item.service_custom_fields"
                                                              :key="field.id">
                                                        <div x-data="{ open: false }"
                                                             :class="open ? 'relative z-[100]' : 'relative z-10'"
                                                             class="p-3 rounded-xl bg-white dark:bg-gray-900/50 border border-gray-200/80 dark:border-gray-700/60 shadow-sm hover:border-indigo-300 dark:hover:border-indigo-500/50 transition-colors group">
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
                                                                <template x-if="item._isMerged">
                                                                    <div class="w-full">
                                                                        <div
                                                                            class="w-full flex items-center justify-between p-2.5 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 text-xs font-bold text-amber-800 dark:text-amber-300">
                                                                            <div
                                                                                class="flex items-center gap-1.5 min-w-0">
                                                                                <svg
                                                                                    class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 shrink-0"
                                                                                    fill="none" viewBox="0 0 24 24"
                                                                                    stroke="currentColor"
                                                                                    stroke-width="2">
                                                                                    <path stroke-linecap="round"
                                                                                          stroke-linejoin="round"
                                                                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                                                </svg>
                                                                                <span class="truncate"
                                                                                      x-text="getFieldValueLabel(field, item.custom_field_values[field.id]) || 'تنظیم نشده'"></span>
                                                                            </div>
                                                                            <span
                                                                                class="text-[10px] text-amber-600 dark:text-amber-400 font-normal shrink-0 me-1">(قفل شده)</span>
                                                                        </div>
                                                                        <input type="hidden"
                                                                               :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                               :value="item.custom_field_values[field.id]">
                                                                    </div>
                                                                </template>
                                                                <template x-if="!item._isMerged">
                                                                    <div class="w-full">
                                                                        <template
                                                                            x-if="field.type === 'text' || field.type === 'email' || field.type === 'url'"><input
                                                                                type="text"
                                                                                :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                                x-model="item.custom_field_values[field.id]"
                                                                                class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-xs text-gray-800 dark:text-gray-200 dark:bg-gray-900/50 dark:border-gray-700 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                                                                                :placeholder="field.placeholder || field.label"
                                                                                :required="field.is_required">
                                                                        </template>
                                                                        <template x-if="field.type === 'datetime'">
                                                                            <div class="relative w-full">
                                                                                <input
                                                                                    type="text" readonly data-jdp-with-time
                                                                                    :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                                    x-model="item.custom_field_values[field.id]"
                                                                                    @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: true, hasSecond: false}); jalaliDatepicker.show($el); }"
                                                                                    @focus="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: true, hasSecond: false}); jalaliDatepicker.show($el); }"
                                                                                    @change="item.custom_field_values[field.id] = $el.value; calculateTotals();"
                                                                                    class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 pl-8 text-xs text-gray-800 dark:text-gray-200 dark:bg-gray-900/50 dark:border-gray-700 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none cursor-pointer transition-all"
                                                                                    placeholder="انتخاب تاریخ و ساعت"
                                                                                    autocomplete="off"
                                                                                    :required="field.is_required">
                                                                                <button type="button"
                                                                                        x-show="item.custom_field_values[field.id]"
                                                                                        @click="item.custom_field_values[field.id] = ''; calculateTotals();"
                                                                                        class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 p-0.5 transition-colors"
                                                                                        title="پاک کردن تاریخ">
                                                                                    <svg class="w-3.5 h-3.5" fill="none"
                                                                                         viewBox="0 0 24 24" stroke="currentColor">
                                                                                        <path stroke-linecap="round"
                                                                                              stroke-linejoin="round"
                                                                                              stroke-width="2"
                                                                                              d="M6 18L18 6M6 6l12 12"/>
                                                                                    </svg>
                                                                                </button>
                                                                            </div>
                                                                        </template>
                                                                        <template x-if="field.type === 'date'">
                                                                            <div class="relative w-full">
                                                                                <input
                                                                                    type="text" readonly data-jdp-only-date
                                                                                    :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                                    x-model="item.custom_field_values[field.id]"
                                                                                    @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                                                                    @focus="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                                                                    @change="item.custom_field_values[field.id] = $el.value; calculateTotals();"
                                                                                    class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 pl-8 text-xs text-gray-800 dark:text-gray-200 dark:bg-gray-900/50 dark:border-gray-700 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none cursor-pointer transition-all"
                                                                                    placeholder="انتخاب تاریخ"
                                                                                    autocomplete="off"
                                                                                    :required="field.is_required">
                                                                                <button type="button"
                                                                                        x-show="item.custom_field_values[field.id]"
                                                                                        @click="item.custom_field_values[field.id] = ''; calculateTotals();"
                                                                                        class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 p-0.5 transition-colors"
                                                                                        title="پاک کردن تاریخ">
                                                                                    <svg class="w-3.5 h-3.5" fill="none"
                                                                                         viewBox="0 0 24 24" stroke="currentColor">
                                                                                        <path stroke-linecap="round"
                                                                                              stroke-linejoin="round"
                                                                                              stroke-width="2"
                                                                                              d="M6 18L18 6M6 6l12 12"/>
                                                                                    </svg>
                                                                                </button>
                                                                            </div>
                                                                        </template>
                                                                        <template x-if="field.type === 'number'"><input
                                                                                type="text"
                                                                                :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                                :value="formatPriceInput(item.custom_field_values[field.id])"
                                                                                @input="
                                                                                    let val = parsePriceInput($event.target.value);
                                                                                    item.custom_field_values[field.id] = val;
                                                                                    if (field.has_pricing) {
                                                                                        setCustomFieldQuantity(item, field, null, val);
                                                                                    }
                                                                                "
                                                                                class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-sm font-bold text-gray-800 dark:text-gray-200 dark:bg-gray-900/50 dark:border-gray-700 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none text-center tabular-nums dir-ltr transition-all"
                                                                                :required="field.is_required">
                                                                        </template>
                                                                        <template
                                                                            x-if="field.type === 'textarea'"><textarea
                                                                                :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                                x-model="item.custom_field_values[field.id]"
                                                                                rows="2"
                                                                                class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-xs text-gray-800 dark:text-gray-200 dark:bg-gray-900/50 dark:border-gray-700 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none resize-none transition-all"
                                                                                :required="field.is_required"></textarea>
                                                                        </template>
                                                                        <template x-if="field.type === 'select'">
                                                                            <div class="relative w-full"
                                                                                 @click.outside="open = false; item._hasOpenSelectDropdown = false">
                                                                                <button type="button"
                                                                                        @click="open = !open; item._hasOpenSelectDropdown = open"
                                                                                        class="w-full flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-xs text-gray-800 dark:text-gray-200 dark:bg-gray-900/50 dark:border-gray-700 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none cursor-pointer transition-all text-start">
                                                                            <span
                                                                                x-text="item.custom_field_values[field.id] || 'انتخاب کنید...'"
                                                                                :class="item.custom_field_values[field.id] ? 'font-bold text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'"></span>
                                                                                    <svg
                                                                                        class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0"
                                                                                        :class="open ? 'rotate-180 text-indigo-600' : ''"
                                                                                        fill="none" viewBox="0 0 24 24"
                                                                                        stroke="currentColor">
                                                                                        <path stroke-linecap="round"
                                                                                              stroke-linejoin="round"
                                                                                              stroke-width="2"
                                                                                              d="M19 9l-7 7-7-7"/>
                                                                                    </svg>
                                                                                </button>
                                                                                <input type="hidden"
                                                                                       :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                                       :value="item.custom_field_values[field.id]"
                                                                                       :required="field.is_required && !item.custom_field_values[field.id]">

                                                                                <div x-show="open"
                                                                                     x-transition.opacity.duration.150ms
                                                                                     style="display: none;"
                                                                                     class="absolute z-[100] mt-1 w-full max-h-48 overflow-y-auto overscroll-contain bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl py-1">
                                                                                    <button type="button"
                                                                                            @click="item.custom_field_values[field.id] = ''; open = false; item._hasOpenSelectDropdown = false; calculateTotals();"
                                                                                            class="w-full text-start px-3 py-2 text-xs text-gray-400 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                                                                        انتخاب کنید...
                                                                                    </button>
                                                                                    <template
                                                                                        x-for="opt in getFieldOptionsList(field)"
                                                                                        :key="opt.label">
                                                                                        <button type="button"
                                                                                                @click="item.custom_field_values[field.id] = opt.label; open = false; item._hasOpenSelectDropdown = false; calculateTotals();"
                                                                                                class="w-full flex items-center justify-between text-start px-3 py-2 text-xs hover:bg-indigo-50 dark:hover:bg-indigo-500/10 border-t border-gray-100 dark:border-gray-700/40 transition-colors"
                                                                                                :class="item.custom_field_values[field.id] === opt.label ? 'bg-indigo-50/70 dark:bg-indigo-500/20 font-bold text-indigo-600 dark:text-indigo-400' : 'text-gray-800 dark:text-gray-200'">
                                                                                            <span x-text="opt.label"
                                                                                                  class="font-medium"></span>
                                                                                            <span
                                                                                                x-show="field.has_pricing && opt.price > 0"
                                                                                                class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-200/50 dark:border-emerald-500/20"
                                                                                                x-text="'+ ' + (opt.pricing_type === 'percentage' ? (opt.price + '%') : (formatMoney(opt.price) + ' {{ $currencyLabel }}'))"></span>
                                                                                        </button>
                                                                                    </template>
                                                                                </div>
                                                                            </div>
                                                                        </template>
                                                                        <template x-if="field.type === 'multiselect'">
                                                                            <div
                                                                                class="flex flex-col gap-1.5 w-full max-h-40 overflow-y-auto sc-thin">
                                                                                <template
                                                                                    x-for="opt in getFieldOptionsList(field)"
                                                                                    :key="opt.label"><label
                                                                                        class="flex items-center justify-between gap-2.5 cursor-pointer text-[11px] px-3 py-2.5 rounded-xl bg-gray-50/50 dark:bg-gray-800/50 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 border border-transparent hover:border-indigo-200 dark:hover:border-indigo-800 transition-colors w-full">
                                                                                        <div
                                                                                            class="flex items-center gap-2.5">
                                                                                            <input
                                                                                                type="checkbox"
                                                                                                :name="'items[' + index + '][custom_fields][' + field.id + '][]'"
                                                                                                :value="opt.label"
                                                                                                :checked="Array.isArray(item.custom_field_values[field.id]) && item.custom_field_values[field.id].includes(opt.label)"
                                                                                                @change="toggleMultiselect(item, field.id, opt.label, $event.target.checked); calculateTotals();"
                                                                                                class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                                                                                            <span
                                                                                                x-text="opt.label"
                                                                                                class="text-gray-700 dark:text-gray-300 font-medium"></span>
                                                                                        </div>
                                                                                        <template
                                                                                            x-if="field.has_pricing && opt.price > 0">
                                                                                    <span
                                                                                        class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-2 py-0.5 rounded-md border border-indigo-200 dark:border-indigo-500/20"
                                                                                        x-text="'+ ' + (opt.pricing_type === 'percentage' ? (opt.price + '%') : (formatMoney(opt.price) + ' {{ $currencyLabel }}'))"></span>
                                                                                        </template>
                                                                                    </label>
                                                                                </template>
                                                                            </div>
                                                                        </template>
                                                                        <template x-if="field.type === 'radio'">
                                                                            <div class="flex flex-col gap-1.5 w-full">
                                                                                <template
                                                                                    x-for="opt in getFieldOptionsList(field)"
                                                                                    :key="opt.label"><label
                                                                                        class="flex items-center justify-between gap-2.5 cursor-pointer text-[11px] px-3 py-2.5 rounded-xl bg-gray-50/50 dark:bg-gray-800/50 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 border border-transparent hover:border-indigo-200 dark:hover:border-indigo-800 transition-colors w-full">
                                                                                        <div
                                                                                            class="flex items-center gap-2.5">
                                                                                            <input
                                                                                                type="radio"
                                                                                                :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                                                x-model="item.custom_field_values[field.id]"
                                                                                                :value="opt.label"
                                                                                                @change="calculateTotals()"
                                                                                                class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                                                                                            <span
                                                                                                x-text="opt.label"
                                                                                                class="text-gray-700 dark:text-gray-300 font-medium"></span>
                                                                                        </div>
                                                                                        <template
                                                                                            x-if="field.has_pricing && opt.price > 0">
                                                                                    <span
                                                                                        class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-2 py-0.5 rounded-md border border-indigo-200 dark:border-indigo-500/20"
                                                                                        x-text="'+ ' + (opt.pricing_type === 'percentage' ? (opt.price + '%') : (formatMoney(opt.price) + ' {{ $currencyLabel }}'))"></span>
                                                                                        </template>
                                                                                    </label>
                                                                                </template>
                                                                            </div>
                                                                        </template>

                                                                        <template x-if="field.has_pricing && ['select', 'multiselect', 'radio'].includes(field.type)">
                                                                            <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
                                                                                <label class="inline-flex items-center gap-1.5 cursor-pointer text-[11px] text-gray-600 dark:text-gray-400 select-none">
                                                                                    <input type="checkbox"
                                                                                           :name="'items[' + index + '][custom_fields_use_default_price][' + field.id + ']'"
                                                                                           value="1"
                                                                                           x-model="item.custom_field_use_default_price[field.id]"
                                                                                           @change="calculateTotals()"
                                                                                           class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                                    <span>استفاده از قیمت پیش‌فرض</span>
                                                                                </label>
                                                                                <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500"
                                                                                      x-text="'(پیش‌فرض: ' + formatMoney(field.pricing_amount || 0) + ' ' + (field.pricing_type === 'percentage' ? '%' : '{{ $currencyLabel }}') + ')'"></span>
                                                                            </div>
                                                                        </template>

                                                                        <template x-if="field.type === 'checkbox'">
                                                                            <label
                                                                                class="flex items-center gap-2.5 cursor-pointer w-full px-3 py-3 rounded-xl border border-gray-200 bg-gray-50/50 hover:bg-indigo-50 hover:border-indigo-300 dark:bg-gray-900/50 dark:border-gray-700 dark:hover:bg-indigo-900/30 dark:hover:border-indigo-700 transition-colors"><input
                                                                                    type="checkbox"
                                                                                    :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                                    x-model="item.custom_field_values[field.id]"
                                                                                    value="1"
                                                                                    @change="calculateTotals()"
                                                                                    class="w-4.5 h-4.5 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 dark:bg-gray-900"><span
                                                                                    class="text-xs font-bold text-gray-700 dark:text-gray-300">انتخاب می‌کنم</span></label>
                                                                        </template>
                                                                        <template x-if="field.type === 'file'"><input
                                                                                type="file"
                                                                                :name="'items[' + index + '][custom_fields][' + field.id + ']'"
                                                                                @change="item.custom_field_values[field.id] = $event.target.files[0]?.name || ''"
                                                                                class="w-full text-xs rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-gray-800 dark:text-gray-200 dark:bg-gray-900/50 dark:border-gray-700 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-400"
                                                                                :required="field.is_required">
                                                                        </template>
                                                                    </div>
                                                                </template>
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
                    <div class="w-full md:w-[28rem] ms-auto">
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between text-gray-600 dark:text-gray-400 font-medium"><span>جمع کل مبالغ</span><span
                                    class="tabular-nums font-medium"><span x-text="formatMoney(totals.subtotal)"></span><span
                                        class="text-[10px] text-gray-400 ms-1">{{ $currencyLabel }}</span></span></div>
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
                            <div x-show="totals.tax > 0"
                                 class="flex justify-between items-center text-gray-700 dark:text-gray-300 font-medium bg-amber-50/50 dark:bg-amber-900/10 px-2.5 py-1.5 rounded-lg border border-amber-100 dark:border-amber-900/30">
                                <span class="text-xs">مبلغ با احتساب مالیات</span>
                                <span class="tabular-nums font-bold">
                                    <span x-text="formatMoney(totals.subtotalWithTax)"></span>
                                    <span class="text-[10px] text-gray-400 ms-1">{{ $currencyLabel }}</span>
                                </span>
                            </div>
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
                        </div>
                        <div class="border-t-2 border-dashed border-gray-200 dark:border-gray-700 my-4"></div>
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-base font-black text-gray-900 dark:text-white block">مبلغ نهایی</span>
                                <template x-if="totals.isRounded">
                                    <div
                                        class="mt-1 flex items-center gap-1.5 text-[11px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/40 px-2 py-0.5 rounded-lg border border-indigo-200/80 dark:border-indigo-800/60 w-fit">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>حاصل رندسازی مبالغ (<span
                                                x-text="(totals.roundingDiff > 0 ? '+' : '') + formatMoney(totals.roundingDiff)"></span> {{ $currencyLabel }})</span>
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
            <div class="{{ $cardClass }} relative overflow-hidden">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-emerald-50/50 via-transparent to-transparent dark:from-emerald-500/5 pointer-events-none"></div>
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
                                  class="w-full rounded-2xl border-2 border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 px-5 py-4 text-sm leading-7 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:border-emerald-500 focus:ring-4 focus:emerald-500/10 outline-none transition-all resize-none shadow-inner"
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
                            class="flex-1 md:flex-none px-8 py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-black shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all duration-300 active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>{{ $isInvoice ? 'ثبت فاکتور' : 'ثبت پیش فاکتور' }}</button>
                    <a href="{{ route('services.invoices.index') }}"
                       class="px-6 py-3.5 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-xl dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">انصراف</a>
                </div>
            </div>
        </form>
        <div x-show="activeProductModalIndex !== null" x-transition.opacity
             class="fixed inset-0 z-[100] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
             style="display: none;" @click.self="closeProductModal()">
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden"
                x-transition.scale.origin.center @click.outside="closeProductModal()">
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
                <div
                    class="p-5 border-b border-gray-200 dark:border-gray-700 space-y-4 bg-gray-50/50 dark:bg-gray-800/50">
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
                            <div class="relative" x-data="{ open: false }"
                                 x-init="if(!modalSelectedAttributes[attr.name]) modalSelectedAttributes = { ...modalSelectedAttributes, [attr.name]: [] }"
                                 @click.outside="open = false">
                                <button type="button" @click="open = !open"
                                        class="w-full flex items-center justify-between rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-emerald-500 outline-none transition-all cursor-pointer">
                                    <div class="flex items-center gap-2 truncate">
                                        <span class="truncate" x-text="attr.name"></span>
                                        <template
                                            x-if="modalSelectedAttributes[attr.name] && modalSelectedAttributes[attr.name].length > 0">
                                            <span
                                                class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400 text-[10px] font-bold"
                                                x-text="toPersianNum(modalSelectedAttributes[attr.name].length)"></span>
                                        </template>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="open" style="display: none;"
                                     class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                                    <div class="max-h-48 overflow-y-auto p-2 space-y-1 custom-scrollbar">
                                        <template x-for="v in attr.values" :key="v">
                                            <label
                                                class="flex items-center gap-2 px-2 py-1.5 text-xs font-medium rounded-lg cursor-pointer hover:bg-emerald-50 dark:hover:bg-gray-700 transition-colors">
                                                <input type="checkbox" :value="v"
                                                       x-model="modalSelectedAttributes[attr.name]"
                                                       @change="modalSelectedAttributes = { ...modalSelectedAttributes }"
                                                       class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 bg-gray-50 dark:bg-gray-900 dark:border-gray-600 h-4 w-4">
                                                <span x-text="v" class="text-gray-700 dark:text-gray-300"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
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
                    issueDate: '',
                    dueDate: '',
                    notesText: '',
                    invoiceNumber: @json($invoiceNumber ?? ''),
                    invoiceAuto: @json($invoiceAuto ?? false),
                    invoiceNumberUnlocked: false,
                    proformaInvoiceNumber: @json($proformaInvoiceNumber ?? ''),
                    proformaInvoiceAuto: @json($proformaAuto ?? false),
                    servicesList: @json($services),
                    packagesList: @json($packages ?? []),
                    showPackageDropdown: false,
                    packageSearch: '',
                    collapsedPackages: {},
                    productsList: @json($products ?? []),
                    marketAttributesList: @json($marketAttributesForJs ?? []),
                    modalSelectedAttributes: {},
                    items: @json(!empty($mergedItems) ? $mergedItems : []),
                    taxPercent: @json($defaultTaxRate ?? 9),
                    taxUnlocked: false,
                    defaultTaxRate: @json($defaultTaxRate ?? 9),
                    taxMode: @json($taxMode ?? 'invoice'),
                    taxApplyCustomFields: @json($taxApplyCustomFields ?? false),
                    servicesRoundingMode: @json($servicesRoundingMode ?? 'none'),
                    servicesRoundingFactor: parseInt(@json($servicesRoundingFactor ?? 1000)) || 1000,
                    extraDiscountType: 'amount',
                    extraDiscountValue: 0,
                    selectedCustomer: @json(old('customer_id', request('customer_id')) ?? ''),
                    selectedCustomerData: null,
                    clientSelectedFields: @json(old('client_selected_fields', (object)[])),
                    isMergeMode: @json(!empty($mergedFromIds)),
                    customersList: @json($customersListForJs),
                    customerQuery: '',
                    customerDropdownOpen: false,
                    periodLabels: {monthly: 'ماهانه', quarterly: 'فصلی', semi_annual: 'شش ماهه', annual: 'سالانه'},

                    activeProductModalIndex: null,
                    modalSelectedMasterProduct: '',
                    modalSelectedBrand: '',
                    modalStockStatus: 'all',

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
                        if (c && c.multi_sub_fields) {
                            c.multi_sub_fields.forEach(f => {
                                if (!this.clientSelectedFields[f.id]) {
                                    this.clientSelectedFields[f.id] = [...(f.options || [])];
                                }
                            });
                        }
                    },
                    clearCustomer() {
                        this.selectedCustomer = '';
                        this.selectedCustomerData = null;
                        this.customerQuery = '';
                        this.clientSelectedFields = {};
                    },
                    isSubItemChecked(fieldId, opt) {
                        if (!this.clientSelectedFields[fieldId]) return false;
                        const arr = Array.isArray(this.clientSelectedFields[fieldId])
                            ? this.clientSelectedFields[fieldId]
                            : [this.clientSelectedFields[fieldId]];
                        return arr.includes(opt);
                    },
                    toggleSubItem(fieldId, opt) {
                        if (!this.clientSelectedFields[fieldId] || !Array.isArray(this.clientSelectedFields[fieldId])) {
                            this.clientSelectedFields[fieldId] = [];
                        }
                        const idx = this.clientSelectedFields[fieldId].indexOf(opt);
                        if (idx > -1) {
                            this.clientSelectedFields[fieldId].splice(idx, 1);
                        } else {
                            this.clientSelectedFields[fieldId].push(opt);
                        }
                    },
                    selectAllSubItems(fieldId, options) {
                        this.clientSelectedFields[fieldId] = [...(options || [])];
                    },
                    deselectAllSubItems(fieldId) {
                        this.clientSelectedFields[fieldId] = [];
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
                        if (this.selectedCustomer) {
                            this.selectedCustomerData = this.customersList.find(c => String(c.id) === String(this.selectedCustomer)) || null;
                        }
                        this.notesText = @json(old('notes', ''));
                        this.setDefaultDates();
                        if (this.invoiceAuto && !this.invoiceNumber) this.invoiceNumber = 'در حال تولید...';
                        if (this.proformaInvoiceAuto && !this.proformaInvoiceNumber) this.proformaInvoiceNumber = 'در حال تولید...';
                        this.$watch('issueDate', () => {
                            this.items.forEach((i, idx) => {
                                if (i.mode === 'service' && i.service_raw && i.service_raw.billing_type === 'recurring' && i.billing_period) this.updatePriceForPeriod(idx);
                            });
                        });
                        this.$nextTick(() => {
                            if (typeof jalaliDatepicker !== 'undefined') {
                                jalaliDatepicker.startWatch({
                                    date: true,
                                    time: true,
                                    hasSecond: false
                                });
                            }
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
                            _hasOpenSelectDropdown: false,
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
                            custom_field_quantities: {},
                            custom_field_custom_prices: {},
                            custom_field_custom_discounts: {},
                            custom_field_tax_percents: {},
                            custom_field_use_default_price: {},
                            tax_percent: this.defaultTaxRate,
                            _taxUnlocked: false
                        });
                    },
                    removeItem(i) {
                        this.items.splice(i, 1);
                    },
                    removePackage(groupId) {
                        this.items = this.items.filter(i => i._packageGroupId !== groupId);
                        delete this.collapsedPackages[groupId];
                    },
                    filteredPackagesList() {
                        const q = this.packageSearch.trim().toLowerCase();
                        if (!q) return [];
                        return this.packagesList.filter(p => p.name.toLowerCase().includes(q) || (p.description && p.description.toLowerCase().includes(q))).slice(0, 8);
                    },

                    togglePackageDropdown() {
                        this.showPackageDropdown = !this.showPackageDropdown;
                    },
                    selectPackage(pkg) {
                        if (!pkg || !pkg.items || pkg.items.length === 0) return;

                        const groupId = pkg.id + '_' + Date.now();

                        pkg.items.forEach(item => {
                            let serviceRaw = null;
                            if (item.service_id && this.servicesList) {
                                const sMatch = this.servicesList.find(s => String(s.id) === String(item.service_id));
                                if (sMatch) serviceRaw = sMatch;
                            }
                            if (!serviceRaw && item.service) {
                                serviceRaw = item.service;
                            }

                            let rF = (serviceRaw ? (serviceRaw.custom_fields || serviceRaw.customFields) : (item.service ? (item.service.custom_fields || item.service.customFields) : [])) || [];
                            let customFieldsArray = rF.filter(f => f.show_in_invoice === true || f.show_in_invoice === 1 || String(f.show_in_invoice) === '1' || f.show_in_invoice === undefined || f.show_in_invoice === null).map(f => {
                                let f2 = {...f};
                                let rawOptions = f2.options || [];
                                if (typeof rawOptions === 'string') {
                                    try {
                                        rawOptions = JSON.parse(rawOptions);
                                    } catch (e) {
                                        rawOptions = [];
                                    }
                                }
                                let normalizedOptions = [];
                                if (Array.isArray(rawOptions)) {
                                    normalizedOptions = rawOptions.map(opt => {
                                        if (typeof opt === 'object' && opt !== null) {
                                            return {
                                                label: opt.label || opt.title || opt.name || '',
                                                price: Number(opt.price || opt.pricing_amount || 0),
                                                pricing_type: opt.pricing_type || 'fixed'
                                            };
                                        }
                                        return {
                                            label: String(opt || ''),
                                            price: 0,
                                            pricing_type: 'fixed'
                                        };
                                    }).filter(o => o.label.trim() !== '');
                                }
                                f2.options = normalizedOptions;
                                return f2;
                            });

                            let rawQuantities = item.custom_fields_quantities || item.custom_field_quantities || {};
                            let customFieldQuantities = {};
                            if (typeof rawQuantities === 'object' && rawQuantities !== null) {
                                Object.keys(rawQuantities).forEach(k => {
                                    let v = rawQuantities[k];
                                    if (typeof v === 'object' && v !== null) {
                                        customFieldQuantities[k] = {};
                                        Object.keys(v).forEach(subK => {
                                            let n = Number(this.toEnglishNum((v[subK] || '').toString()).replace(/[^\d.]/g, ''));
                                            customFieldQuantities[k][subK] = isNaN(n) || n <= 0 ? 1 : n;
                                        });
                                    } else {
                                        let n = Number(this.toEnglishNum((v || '').toString()).replace(/[^\d.]/g, ''));
                                        customFieldQuantities[k] = isNaN(n) || n <= 0 ? 1 : n;
                                    }
                                });
                            }

                            let customFieldValues = {};
                            if (item.custom_fields && typeof item.custom_fields === 'object') {
                                customFieldValues = JSON.parse(JSON.stringify(item.custom_fields));
                            }

                            let customFieldCustomPrices = {};
                            if (item.custom_fields_prices && typeof item.custom_fields_prices === 'object') {
                                customFieldCustomPrices = JSON.parse(JSON.stringify(item.custom_fields_prices));
                            }

                            customFieldsArray.forEach(f => {
                                if (f.type === 'number') {
                                    let rawV = customFieldValues[f.id] !== undefined ? customFieldValues[f.id] : null;
                                    let rawQ = customFieldQuantities[f.id] !== undefined ? customFieldQuantities[f.id] : null;
                                    let numFromV = rawV !== null ? Number(this.toEnglishNum(rawV.toString()).replace(/[^\d.]/g, '')) : NaN;
                                    let numFromQ = rawQ !== null ? Number(this.toEnglishNum(rawQ.toString()).replace(/[^\d.]/g, '')) : NaN;

                                    if (!isNaN(numFromV) && numFromV > 0) {
                                        customFieldValues[f.id] = numFromV;
                                        if (f.has_pricing) customFieldQuantities[f.id] = numFromV;
                                    } else if (!isNaN(numFromQ) && numFromQ > 0) {
                                        customFieldValues[f.id] = numFromQ;
                                        if (f.has_pricing) customFieldQuantities[f.id] = numFromQ;
                                    }
                                } else if (f.has_pricing) {
                                    if (f.type === 'multiselect') {
                                        if (Array.isArray(customFieldValues[f.id])) {
                                            if (!customFieldQuantities[f.id] || typeof customFieldQuantities[f.id] !== 'object') {
                                                customFieldQuantities[f.id] = {};
                                            }
                                            customFieldValues[f.id].forEach(opt => {
                                                let optQ = customFieldQuantities[f.id][opt];
                                                let n = Number(this.toEnglishNum((optQ || '').toString()).replace(/[^\d.]/g, ''));
                                                customFieldQuantities[f.id][opt] = (!isNaN(n) && n > 0) ? n : 1;
                                            });
                                        }
                                    } else if (this.isFieldSelected(f, customFieldValues[f.id])) {
                                        let curQ = customFieldQuantities[f.id];
                                        let n = Number(this.toEnglishNum((curQ || '').toString()).replace(/[^\d.]/g, ''));
                                        customFieldQuantities[f.id] = (!isNaN(n) && n > 0) ? n : 1;
                                    }
                                }
                            });

                            this.items.push({
                                mode: item.service_id ? 'service' : 'manual',
                                service_id: item.service_id ? String(item.service_id) : '',
                                product_id: '',
                                product_variant_id: '',
                                stock: null,
                                service_raw: serviceRaw,
                                custom_service_name: item.custom_service_name || (serviceRaw ? serviceRaw.name : ''),
                                _showServiceDropdown: false,
                                _showProductDropdown: false,
                                _hasOpenSelectDropdown: false,
                                _selectedGroup: '',
                                description: item.description || '',
                                unit: item.unit || 'عدد',
                                quantity: item.quantity || 1,
                                unit_price: item.unit_price || 0,
                                discount: item.discount_amount ? parseFloat(item.discount_amount) : (item.discount_value ? parseFloat(item.discount_value) : 0),
                                billing_period: item.billing_period || '',
                                service_custom_fields: customFieldsArray,
                                custom_field_values: customFieldValues,
                                custom_field_custom_prices: customFieldCustomPrices,
                                custom_field_quantities: customFieldQuantities,
                                custom_field_custom_discounts: {},
                                custom_field_tax_percents: {},
                                custom_field_use_default_price: item.custom_fields_use_default_price || item.custom_field_use_default_price || {},
                                tax_percent: this.defaultTaxRate,
                                _priceUnlocked: false,
                                _unitUnlocked: false,
                                _taxUnlocked: false,
                                _customPricesUnlocked: {},
                                _customFieldTaxUnlocked: {},
                                _showCustomFields: customFieldsArray.length > 0,
                                _packageGroupId: groupId,
                                _packageTitle: pkg.name,
                                _baseQuantity: parseFloat(item.quantity) || 1,
                                _baseCustomFieldQuantities: JSON.parse(JSON.stringify(customFieldQuantities)),
                                _baseCustomFieldValues: JSON.parse(JSON.stringify(customFieldValues))
                            });
                        });

                        let pkgDiscAmount = 0;
                        if (pkg.total_amount && pkg.final_price && pkg.total_amount > pkg.final_price) {
                            pkgDiscAmount = parseFloat(pkg.total_amount) - parseFloat(pkg.final_price);
                        } else if (pkg.discount_value > 0) {
                            if (pkg.discount_type === 'percent' || pkg.discount_type === 'percentage') {
                                let tot = parseFloat(pkg.total_amount) || 0;
                                pkgDiscAmount = (tot * parseFloat(pkg.discount_value)) / 100;
                            } else {
                                pkgDiscAmount = parseFloat(pkg.discount_value);
                            }
                        }

                        if (pkgDiscAmount > 0) {
                            this.extraDiscountType = 'amount';
                            this.extraDiscountValue = (parseFloat(this.extraDiscountValue) || 0) + pkgDiscAmount;
                        }

                        this.showPackageDropdown = false;
                        if (typeof this.calculateTotals === 'function') {
                            this.calculateTotals();
                        }
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
                    get modalBrands() {
                        const p = this.productsList || [];
                        const b = {};
                        p.forEach(i => {
                            if (i.brand_id) b[i.brand_id] = i.brand_name;
                        });
                        return Object.keys(b).map(id => ({id: id, name: b[id]}));
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
                            let rawOptions = f2.options || [];
                            if (typeof rawOptions === 'string') {
                                try {
                                    rawOptions = JSON.parse(rawOptions);
                                } catch (e) {
                                    rawOptions = [];
                                }
                            }
                            let normalizedOptions = [];
                            if (Array.isArray(rawOptions)) {
                                normalizedOptions = rawOptions.map(opt => {
                                    if (typeof opt === 'object' && opt !== null) {
                                        return {
                                            label: opt.label || opt.title || opt.name || '',
                                            price: Number(opt.price || opt.pricing_amount || 0),
                                            pricing_type: opt.pricing_type || 'fixed'
                                        };
                                    }
                                    return {
                                        label: String(opt || ''),
                                        price: 0,
                                        pricing_type: 'fixed'
                                    };
                                }).filter(o => o.label.trim() !== '');
                            }
                            f2.options = normalizedOptions;
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
                        this.items[i].custom_field_quantities = {};
                        this.items[i].custom_field_custom_prices = {};
                        this.items[i].custom_field_custom_discounts = {};
                        this.items[i].custom_field_tax_percents = {};
                        this.items[i].custom_field_use_default_price = {};
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
                    getFieldOptionsList(f) {
                        if (!f || !f.options) return [];
                        let opts = f.options;
                        if (typeof opts === 'string') {
                            try {
                                opts = JSON.parse(opts);
                            } catch (e) {
                                opts = [];
                            }
                        }
                        if (!Array.isArray(opts)) return [];
                        return opts.map(opt => {
                            if (typeof opt === 'object' && opt !== null) {
                                return {
                                    label: String(opt.label || opt.title || opt.name || ''),
                                    price: Number(opt.price || opt.pricing_amount || 0),
                                    pricing_type: opt.pricing_type || 'fixed'
                                };
                            }
                            return {
                                label: String(opt || ''),
                                price: 0,
                                pricing_type: 'fixed'
                            };
                        }).filter(o => o.label.trim() !== '');
                    },
                    toggleMultiselect(it, fId, o, c) {
                        if (!Array.isArray(it.custom_field_values[fId])) it.custom_field_values[fId] = [];
                        const a = it.custom_field_values[fId];
                        const i = a.indexOf(o);
                        if (c && i === -1) a.push(o);
                        if (!c && i !== -1) a.splice(i, 1);
                    },
                    isFieldSelected(f, v) {
                        if (v === undefined || v === null || v === '') return false;
                        if (f.type === 'checkbox') return (v === true || v === '1' || v === 1 || String(v) === 'true');
                        if (f.type === 'multiselect') return Array.isArray(v) && v.length > 0;
                        if (f.type === 'number') {
                            if (f.has_pricing) {
                                let num = Number(this.toEnglishNum(v.toString()).replace(/[^\d.]/g, ''));
                                return !isNaN(num) && num > 0;
                            }
                            return (v !== '' && v !== null && v !== undefined);
                        }
                        return (v !== '' && v !== null && v !== undefined);
                    },
                    getFieldValueLabel(f, v) {
                        if (!this.isFieldSelected(f, v)) return '';
                        if (f.type === 'checkbox') return 'انتخاب شده';
                        if (f.type === 'multiselect' && Array.isArray(v)) return v.join('، ');
                        if (f.type === 'number' && f.has_pricing) return '-';
                        return v;
                    },
                    getPackageBaseFieldQuantity(it, f, opt = null) {
                        if (!it || !it._packageGroupId) return 0;
                        const fId = (f && f.id !== undefined) ? f.id : f;
                        let fieldObj = (typeof f === 'object' && f !== null) ? f : (it.service_custom_fields || []).find(cf => String(cf.id) === String(fId));

                        if (it._baseCustomFieldQuantities) {
                            const raw = it._baseCustomFieldQuantities[fId] !== undefined
                                ? it._baseCustomFieldQuantities[fId]
                                : it._baseCustomFieldQuantities[String(fId)];
                            if (raw !== undefined && raw !== null) {
                                if (opt !== null && opt !== undefined) {
                                    if (typeof raw === 'object' && raw[opt] !== undefined) {
                                        let n = Number(this.toEnglishNum((raw[opt] || '').toString()).replace(/[^\d.]/g, ''));
                                        if (!isNaN(n) && n > 0) return n;
                                    } else if (typeof raw !== 'object' && raw !== '') {
                                        let n = Number(this.toEnglishNum((raw || '').toString()).replace(/[^\d.]/g, ''));
                                        if (!isNaN(n) && n > 0) return n;
                                    }
                                } else if (typeof raw !== 'object' && raw !== '') {
                                    let n = Number(this.toEnglishNum((raw || '').toString()).replace(/[^\d.]/g, ''));
                                    if (!isNaN(n) && n > 0) return n;
                                }
                            }
                        }

                        if (it._baseCustomFieldValues) {
                            const baseVal = it._baseCustomFieldValues[fId] !== undefined
                                ? it._baseCustomFieldValues[fId]
                                : it._baseCustomFieldValues[String(fId)];
                            if (opt !== null && opt !== undefined) {
                                if (Array.isArray(baseVal) && baseVal.includes(opt)) return 1;
                            } else if (fieldObj && this.isFieldSelected(fieldObj, baseVal)) {
                                return 1;
                            }
                        }
                        return 0;
                    },
                    getPackageBaseItemQuantity(it) {
                        if (!it || !it._packageGroupId) return 0;
                        let n = parseFloat(it._baseQuantity);
                        return isNaN(n) || n <= 0 ? 1 : n;
                    },
                    getCustomFieldQuantity(it, f, opt = null) {
                        if (!it) return 1;
                        const fId = (f && f.id !== undefined) ? f.id : f;
                        let fieldObj = (typeof f === 'object' && f !== null) ? f : (it.service_custom_fields || []).find(cf => String(cf.id) === String(fId));
                        let rawFieldQ = it.custom_field_quantities ? (it.custom_field_quantities[fId] !== undefined ? it.custom_field_quantities[fId] : it.custom_field_quantities[String(fId)]) : undefined;

                        if (opt !== null && opt !== undefined) {
                            if (rawFieldQ && typeof rawFieldQ === 'object' && rawFieldQ[opt] !== undefined && rawFieldQ[opt] !== '') {
                                let q = Number(this.toEnglishNum((rawFieldQ[opt] || '').toString()).replace(/[^\d.]/g, ''));
                                if (!isNaN(q) && q > 0) return q;
                            }
                            if (rawFieldQ !== undefined && typeof rawFieldQ !== 'object' && rawFieldQ !== '') {
                                let q = Number(this.toEnglishNum((rawFieldQ || '').toString()).replace(/[^\d.]/g, ''));
                                if (!isNaN(q) && q > 0) return q;
                            }
                            let itemQ = parseFloat(it.quantity) || 1;
                            return itemQ <= 0 ? 1 : itemQ;
                        }

                        if (fieldObj && fieldObj.type === 'number' && fieldObj.has_pricing) {
                            if (rawFieldQ !== undefined && typeof rawFieldQ !== 'object' && rawFieldQ !== '') {
                                let q = Number(this.toEnglishNum((rawFieldQ || '').toString()).replace(/[^\d.]/g, ''));
                                if (!isNaN(q) && q > 0) return q;
                            }
                            if (it.custom_field_values && it.custom_field_values[fId] !== undefined && it.custom_field_values[fId] !== '') {
                                let q = Number(this.toEnglishNum((it.custom_field_values[fId] || '').toString()).replace(/[^\d.]/g, ''));
                                if (!isNaN(q) && q > 0) return q;
                            }
                            return 0;
                        }

                        if (rawFieldQ !== undefined && typeof rawFieldQ !== 'object' && rawFieldQ !== '') {
                            let q = Number(this.toEnglishNum((rawFieldQ || '').toString()).replace(/[^\d.]/g, ''));
                            if (!isNaN(q) && q > 0) return q;
                        }

                        let itemQ = parseFloat(it.quantity) || 1;
                        return itemQ <= 0 ? 1 : itemQ;
                    },
                    setCustomFieldQuantity(it, f, opt = null, val) {
                        let num = Number(this.toEnglishNum((val !== null && val !== undefined ? val : '').toString()).replace(/[^\d.]/g, ''));
                        if (isNaN(num)) num = 0;
                        const fId = (f && f.id !== undefined) ? f.id : f;
                        let fieldObj = (typeof f === 'object' && f !== null) ? f : (it.service_custom_fields || []).find(cf => String(cf.id) === String(fId));

                        if (!it.custom_field_quantities) it.custom_field_quantities = {};
                        if (opt !== null && opt !== undefined) {
                            if (typeof it.custom_field_quantities[fId] !== 'object' || it.custom_field_quantities[fId] === null) {
                                it.custom_field_quantities[fId] = {};
                            }
                            it.custom_field_quantities[fId][opt] = num;
                            if (it.custom_field_quantities[String(fId)] && String(fId) !== fId) {
                                it.custom_field_quantities[String(fId)][opt] = num;
                            }
                        } else {
                            it.custom_field_quantities[fId] = num;
                            it.custom_field_quantities[String(fId)] = num;
                            if (fieldObj && fieldObj.type === 'number' && fieldObj.has_pricing) {
                                if (!it.custom_field_values) it.custom_field_values = {};
                                it.custom_field_values[fId] = num > 0 ? num : (val === '' ? '' : num);
                            }
                        }
                        if (typeof this.calculateTotals === 'function') {
                            this.calculateTotals();
                        }
                    },
                    getCustomFieldPrice(it, f, opt = null) {
                        if (!it || !f) return 0;
                        const fId = (f && f.id !== undefined) ? f.id : f;
                        let fieldObj = (typeof f === 'object' && f !== null) ? f : (it.service_custom_fields || []).find(cf => String(cf.id) === String(fId));
                        if (!fieldObj) return 0;

                        let p = parseFloat(it.unit_price) || 0;
                        let a = Number(fieldObj.pricing_amount) || 0;
                        if (isNaN(a)) a = 0;
                        let defaultFieldPrice = fieldObj.pricing_type === 'percentage' ? p * (a / 100) : a;

                        let useDefault = !!(it.custom_field_use_default_price && (it.custom_field_use_default_price[fId] === true || it.custom_field_use_default_price[fId] === '1' || it.custom_field_use_default_price[fId] === 1));

                        if (opt !== null && opt !== undefined) {
                            if (this.isCustomPriceUnlocked(it, fieldObj, opt)) {
                                if (it.custom_field_custom_prices && it.custom_field_custom_prices[fId] && typeof it.custom_field_custom_prices[fId] === 'object' && it.custom_field_custom_prices[fId][opt] !== undefined && it.custom_field_custom_prices[fId][opt] !== '') {
                                    let customVal = Number(it.custom_field_custom_prices[fId][opt]);
                                    if (!isNaN(customVal)) return customVal;
                                }
                            }

                            if (useDefault) {
                                return defaultFieldPrice;
                            }

                            const optList = this.getFieldOptionsList(fieldObj);
                            const match = optList.find(o => String(o.label) === String(opt));
                            if (match && match.price !== undefined && match.price !== null && match.price !== '') {
                                let optP = Number(match.price) || 0;
                                return match.pricing_type === 'percentage' ? p * (optP / 100) : optP;
                            }

                            return defaultFieldPrice;
                        }

                        if (this.isCustomPriceUnlocked(it, fieldObj)) {
                            if (it.custom_field_custom_prices && it.custom_field_custom_prices[fId] !== undefined && typeof it.custom_field_custom_prices[fId] !== 'object' && it.custom_field_custom_prices[fId] !== null && it.custom_field_custom_prices[fId] !== '') {
                                let customVal = Number(it.custom_field_custom_prices[fId]);
                                if (!isNaN(customVal)) return customVal;
                            }
                        }

                        if (useDefault) {
                            return defaultFieldPrice;
                        }

                        if (['select', 'radio'].includes(fieldObj.type) && it.custom_field_values?.[fId]) {
                            let val = it.custom_field_values[fId];
                            const optList = this.getFieldOptionsList(fieldObj);
                            const match = optList.find(o => String(o.label) === String(val));
                            if (match && match.price !== undefined && match.price !== null && match.price !== '') {
                                let optP = Number(match.price) || 0;
                                return match.pricing_type === 'percentage' ? p * (optP / 100) : optP;
                            }
                        }

                        return defaultFieldPrice;
                    },
                    setCustomFieldPrice(it, f, opt = null, val) {
                        let num = this.parsePriceInput(val);
                        const fId = (f && f.id !== undefined) ? f.id : f;
                        if (!it.custom_field_custom_prices) it.custom_field_custom_prices = {};
                        if (opt !== null && opt !== undefined) {
                            if (typeof it.custom_field_custom_prices[fId] !== 'object' || it.custom_field_custom_prices[fId] === null) {
                                it.custom_field_custom_prices[fId] = {};
                            }
                            it.custom_field_custom_prices[fId][opt] = num;
                        } else {
                            it.custom_field_custom_prices[fId] = num;
                        }
                        if (typeof this.calculateTotals === 'function') {
                            this.calculateTotals();
                        }
                    },
                    getCustomFieldDiscount(it, f, opt = null) {
                        if (opt !== null && opt !== undefined) {
                            if (it.custom_field_custom_discounts && it.custom_field_custom_discounts[f.id] && typeof it.custom_field_custom_discounts[f.id] === 'object' && it.custom_field_custom_discounts[f.id][opt] !== undefined) {
                                let d = Number(it.custom_field_custom_discounts[f.id][opt]);
                                return isNaN(d) ? 0 : d;
                            }
                            return 0;
                        }
                        if (it.custom_field_custom_discounts && it.custom_field_custom_discounts[f.id] !== undefined && typeof it.custom_field_custom_discounts[f.id] !== 'object') {
                            let d = Number(it.custom_field_custom_discounts[f.id]);
                            return isNaN(d) ? 0 : d;
                        }
                        return 0;
                    },
                    setCustomFieldDiscount(it, f, opt = null, val) {
                        let num = this.parsePriceInput(val);
                        if (!it.custom_field_custom_discounts) it.custom_field_custom_discounts = {};
                        if (opt !== null && opt !== undefined) {
                            if (typeof it.custom_field_custom_discounts[f.id] !== 'object' || it.custom_field_custom_discounts[f.id] === null) {
                                it.custom_field_custom_discounts[f.id] = {};
                            }
                            it.custom_field_custom_discounts[f.id][opt] = num;
                        } else {
                            it.custom_field_custom_discounts[f.id] = num;
                        }
                        if (typeof this.calculateTotals === 'function') {
                            this.calculateTotals();
                        }
                    },
                    getCustomFieldTax(it, f, opt = null) {
                        if (opt !== null && opt !== undefined) {
                            if (it.custom_field_tax_percents && it.custom_field_tax_percents[f.id] && typeof it.custom_field_tax_percents[f.id] === 'object' && it.custom_field_tax_percents[f.id][opt] !== undefined) {
                                return it.custom_field_tax_percents[f.id][opt];
                            }
                            return this.defaultTaxRate;
                        }
                        if (it.custom_field_tax_percents && it.custom_field_tax_percents[f.id] !== undefined && typeof it.custom_field_tax_percents[f.id] !== 'object') {
                            return it.custom_field_tax_percents[f.id];
                        }
                        return this.defaultTaxRate;
                    },
                    setCustomFieldTax(it, f, opt = null, val) {
                        let num = Math.min(100, Math.max(0, Number(this.toEnglishNum(val.toString()).replace(/[^\d.]/g, '')) || 0));
                        if (!it.custom_field_tax_percents) it.custom_field_tax_percents = {};
                        if (opt !== null && opt !== undefined) {
                            if (typeof it.custom_field_tax_percents[f.id] !== 'object' || it.custom_field_tax_percents[f.id] === null) {
                                it.custom_field_tax_percents[f.id] = {};
                            }
                            it.custom_field_tax_percents[f.id][opt] = num;
                        } else {
                            it.custom_field_tax_percents[f.id] = num;
                        }
                        if (typeof this.calculateTotals === 'function') {
                            this.calculateTotals();
                        }
                    },
                    isCustomPriceUnlocked(it, f, opt = null) {
                        if (!it._customPricesUnlocked) it._customPricesUnlocked = {};
                        const fId = (f && f.id !== undefined) ? f.id : f;
                        if (opt !== null && opt !== undefined) {
                            return !!(typeof it._customPricesUnlocked[fId] === 'object' ? it._customPricesUnlocked[fId]?.[opt] : it._customPricesUnlocked[fId]);
                        }
                        return !!it._customPricesUnlocked[fId];
                    },
                    toggleCustomPriceUnlock(it, f, opt = null) {
                        const fId = (f && f.id !== undefined) ? f.id : f;
                        if (!it._customPricesUnlocked) it._customPricesUnlocked = {};
                        if (opt !== null && opt !== undefined) {
                            if (typeof it._customPricesUnlocked[fId] !== 'object' || it._customPricesUnlocked[fId] === null) {
                                it._customPricesUnlocked[fId] = {};
                            }
                            it._customPricesUnlocked[fId][opt] = !it._customPricesUnlocked[fId][opt];
                            if (it._customPricesUnlocked[fId][opt]) {
                                if (!it.custom_field_custom_prices) it.custom_field_custom_prices = {};
                                if (typeof it.custom_field_custom_prices[fId] !== 'object' || it.custom_field_custom_prices[fId] === null) {
                                    it.custom_field_custom_prices[fId] = {};
                                }
                                it.custom_field_custom_prices[fId][opt] = this.getCustomFieldPrice(it, f, opt);
                            } else {
                                if (it.custom_field_custom_prices && it.custom_field_custom_prices[fId]) {
                                    delete it.custom_field_custom_prices[fId][opt];
                                }
                            }
                        } else {
                            it._customPricesUnlocked[fId] = !it._customPricesUnlocked[fId];
                            if (it._customPricesUnlocked[fId]) {
                                if (!it.custom_field_custom_prices) it.custom_field_custom_prices = {};
                                it.custom_field_custom_prices[fId] = this.getCustomFieldPrice(it, f);
                            } else {
                                if (it.custom_field_custom_prices) {
                                    delete it.custom_field_custom_prices[fId];
                                }
                            }
                        }
                        if (typeof this.calculateTotals === 'function') {
                            this.calculateTotals();
                        }
                    },
                    isCustomTaxUnlocked(it, f, opt = null) {
                        if (!it._customFieldTaxUnlocked) it._customFieldTaxUnlocked = {};
                        const fId = (f && f.id !== undefined) ? f.id : f;
                        if (opt !== null && opt !== undefined) {
                            return !!(typeof it._customFieldTaxUnlocked[fId] === 'object' ? it._customFieldTaxUnlocked[fId]?.[opt] : it._customFieldTaxUnlocked[fId]);
                        }
                        return !!it._customFieldTaxUnlocked[fId];
                    },
                    toggleCustomTaxUnlock(it, f, opt = null) {
                        const fId = (f && f.id !== undefined) ? f.id : f;
                        if (!it._customFieldTaxUnlocked) it._customFieldTaxUnlocked = {};
                        if (opt !== null && opt !== undefined) {
                            if (typeof it._customFieldTaxUnlocked[fId] !== 'object' || it._customFieldTaxUnlocked[fId] === null) {
                                it._customFieldTaxUnlocked[fId] = {};
                            }
                            it._customFieldTaxUnlocked[fId][opt] = !it._customFieldTaxUnlocked[fId][opt];
                        } else {
                            it._customFieldTaxUnlocked[fId] = !it._customFieldTaxUnlocked[fId];
                        }
                        if (typeof this.calculateTotals === 'function') {
                            this.calculateTotals();
                        }
                    },
                    removeMultiselectOption(it, fId, opt) {
                        if (Array.isArray(it.custom_field_values[fId])) {
                            const idx = it.custom_field_values[fId].indexOf(opt);
                            if (idx !== -1) {
                                it.custom_field_values[fId].splice(idx, 1);
                            }
                        }
                        if (typeof this.calculateTotals === 'function') {
                            this.calculateTotals();
                        }
                    },
                    clearFieldValue(it, f) {
                        if (f.type === 'checkbox') it.custom_field_values[f.id] = false;
                        else if (f.type === 'multiselect') it.custom_field_values[f.id] = [];
                        else it.custom_field_values[f.id] = '';
                    },
                    getCustomFieldRowTotal(it, f, opt = null) {
                        let q = this.getCustomFieldQuantity(it, f, opt);
                        let p = this.getCustomFieldPrice(it, f, opt);
                        let d = this.getCustomFieldDiscount(it, f, opt);
                        let gross = p * q;
                        let base = Math.max(0, gross - d);
                        if (this.taxMode === 'item' && this.taxApplyCustomFields) {
                            let taxPercent = this.getCustomFieldTax(it, f, opt);
                            let tax = gross * ((Number(taxPercent) || 0) / 100);
                            return base + tax;
                        }
                        return base;
                    },
                    calculateRowTotal(it) {
                        let q = parseFloat(it.quantity) || 0;
                        let p = parseFloat(it.unit_price) || 0;
                        let d = parseFloat(it.discount) || 0;
                        if (isNaN(q)) q = 0;
                        if (isNaN(p)) p = 0;
                        if (isNaN(d)) d = 0;
                        let rB = p * q;
                        let rT = 0;
                        if (this.taxMode === 'item') rT += rB * ((Number(it.tax_percent) || 0) / 100);
                        let cG = 0;
                        let cD = 0;
                        let cT = 0;
                        if (it.service_custom_fields && it.custom_field_values) {
                            it.service_custom_fields.forEach(f => {
                                if (!f.has_pricing) return;

                                if (f.type === 'multiselect') {
                                    const selectedOpts = Array.isArray(it.custom_field_values[f.id]) ? it.custom_field_values[f.id] : [];
                                    selectedOpts.forEach(opt => {
                                        let optQ = this.getCustomFieldQuantity(it, f, opt);
                                        let optP = this.getCustomFieldPrice(it, f, opt);
                                        let optD = this.getCustomFieldDiscount(it, f, opt);
                                        let optGross = optP * optQ;
                                        cG += optGross;
                                        cD += optD;
                                        if (this.taxMode === 'item' && this.taxApplyCustomFields) {
                                            let optTax = this.getCustomFieldTax(it, f, opt);
                                            cT += optGross * ((Number(optTax) || 0) / 100);
                                        }
                                    });
                                } else if (this.isFieldSelected(f, it.custom_field_values[f.id])) {
                                    let fQ = this.getCustomFieldQuantity(it, f);
                                    let fP = this.getCustomFieldPrice(it, f);
                                    let fD = this.getCustomFieldDiscount(it, f);
                                    let fGross = fP * fQ;
                                    cG += fGross;
                                    cD += fD;
                                    if (this.taxMode === 'item' && this.taxApplyCustomFields) {
                                        let fTax = this.getCustomFieldTax(it, f);
                                        cT += fGross * ((Number(fTax) || 0) / 100);
                                    }
                                }
                            });
                        }
                        let tT = Math.max(0, rB - d) + Math.max(0, cG - cD);
                        let res = (this.taxMode === 'item') ? (tT + rT + cT) : tT;
                        return isNaN(res) ? 0 : res;
                    },
                    get totals() {
                        let bS = 0, iD = 0, tC = 0, iTT = 0;
                        this.items.forEach(it => {
                            let q = parseFloat(it.quantity) || 0;
                            let p = parseFloat(it.unit_price) || 0;
                            let d = parseFloat(it.discount) || 0;
                            let rB = p * q;
                            bS += rB;
                            iD += d;
                            if (this.taxMode === 'item') {
                                iTT += rB * ((Number(it.tax_percent) || 0) / 100);
                            }
                            if (it.service_custom_fields && it.custom_field_values) {
                                it.service_custom_fields.forEach(f => {
                                    if (!f.has_pricing) return;

                                    if (f.type === 'multiselect') {
                                        const selectedOpts = Array.isArray(it.custom_field_values[f.id]) ? it.custom_field_values[f.id] : [];
                                        selectedOpts.forEach(opt => {
                                            let optQ = this.getCustomFieldQuantity(it, f, opt);
                                            let optP = this.getCustomFieldPrice(it, f, opt);
                                            let optD = this.getCustomFieldDiscount(it, f, opt);
                                            let optGross = optP * optQ;
                                            tC += optGross;
                                            iD += optD;
                                            if (this.taxMode === 'item' && this.taxApplyCustomFields) {
                                                let optTax = this.getCustomFieldTax(it, f, opt);
                                                iTT += optGross * ((Number(optTax) || 0) / 100);
                                            }
                                        });
                                    } else if (this.isFieldSelected(f, it.custom_field_values[f.id])) {
                                        let fQ = this.getCustomFieldQuantity(it, f);
                                        let fP = this.getCustomFieldPrice(it, f);
                                        let fD = this.getCustomFieldDiscount(it, f);
                                        let fGross = fP * fQ;
                                        tC += fGross;
                                        iD += fD;
                                        if (this.taxMode === 'item' && this.taxApplyCustomFields) {
                                            let fTax = this.getCustomFieldTax(it, f);
                                            iTT += fGross * ((Number(fTax) || 0) / 100);
                                        }
                                    }
                                });
                            }
                        });
                        let s = bS + tC;
                        let tT = this.taxMode === 'item' ? Math.max(0, iTT) : s * ((Number(this.taxPercent) || 0) / 100);
                        let sWithTax = Math.max(0, s + tT);
                        let baseForExtraDisc = Math.max(0, sWithTax - iD);
                        let eD = 0;
                        if (this.extraDiscountType === 'percent') eD = baseForExtraDisc * ((Number(this.extraDiscountValue) || 0) / 100); else eD = Number(this.extraDiscountValue) || 0;
                        eD = Math.max(0, Math.min(eD, baseForExtraDisc));
                        let totalDiscount = Math.max(0, iD + eD);
                        let gT = Math.max(0, sWithTax - totalDiscount);
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
                            subtotalWithTax: sWithTax,
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
                            if (it.service_custom_fields && it.custom_field_values) {
                                it.service_custom_fields.forEach(f => {
                                    if (!f.has_pricing) return;

                                    if (f.type === 'multiselect') {
                                        const selectedOpts = Array.isArray(it.custom_field_values[f.id]) ? it.custom_field_values[f.id] : [];
                                        selectedOpts.forEach(opt => {
                                            let optQ = this.getCustomFieldQuantity(it, f, opt);
                                            let optP = this.getCustomFieldPrice(it, f, opt);
                                            let optD = this.getCustomFieldDiscount(it, f, opt);
                                            let l = f.label + ' (' + opt + ')';
                                            if (!s[l]) s[l] = 0;
                                            s[l] += Math.max(0, (optP * optQ) - optD);
                                        });
                                    } else if (this.isFieldSelected(f, it.custom_field_values[f.id])) {
                                        let fQ = this.getCustomFieldQuantity(it, f);
                                        let fP = this.getCustomFieldPrice(it, f);
                                        let fD = this.getCustomFieldDiscount(it, f);
                                        let v = it.custom_field_values[f.id];
                                        let l = f.label;
                                        if (['select', 'radio'].includes(f.type)) l += ` (${v})`;
                                        if (!s[l]) s[l] = 0;
                                        s[l] += Math.max(0, (fP * fQ) - fD);
                                    }
                                });
                            }
                        });
                        return Object.keys(s).map(k => ({label: k, amount: s[k]}));
                    },
                    formatMoney(v) {
                        let num = Number(v);
                        if (isNaN(num) || !isFinite(num)) num = 0;
                        return new Intl.NumberFormat('fa-IR').format(Math.round(num));
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
                        if (!this.items || this.items.length === 0) {
                            e.preventDefault();
                            alert('ثبت فاکتور بدون آیتم امکان‌پذیر نیست. لطفاً حداقل یک سرویس یا کالا اضافه کنید.');
                            return;
                        }
                        const iI = f.querySelector('input[name="invoice_type"]').value === 'invoice';
                        if (!this.selectedCustomer) {
                            e.preventDefault();
                            alert('انتخاب مشتری الزامی است.');
                            return;
                        }
                        const nI = iI ? f.querySelector('input[name="invoice_number"]') : f.querySelector('input[name="proforma_invoice_number"]');
                        if (nI && !nI.value.trim()) {
                            e.preventDefault();
                            alert(`وارد کردن شماره ${iI ? 'فاکتور' : 'پیش فاکتور'} الزامی است.`);
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
                        const nF = f.querySelectorAll('input[name*="[quantity]"], input[name*="[custom_fields_quantities]"], input[name*="[unit_price]"], input[name*="[discount]"], input[name*="[custom_fields_prices]"], input[name*="[custom_fields_discounts]"], input[name*="[tax_percent]"], input[name*="[custom_fields_taxes]"], input[name="tax_percent"]');
                        nF.forEach(i => {
                            i.value = this.toEnglishNum(i.value).replace(/[^\d.]/g, '');
                        });
                    },
                    hasRecurringItems() {
                        return Array.isArray(this.items) && this.items.some(item => {
                            if (!item) return false;
                            if (item.billing_period) return true;
                            if (item.service_id) {
                                const s = (this.servicesList || []).find(srv => srv.id == item.service_id);
                                if (s && s.billing_type === 'recurring') return true;
                            }
                            return false;
                        });
                    },
                }));
            });

            document.addEventListener('click', function (e) {
                if (window.jalaliDatepicker && typeof window.jalaliDatepicker.hide === 'function') {
                    const dp = document.querySelector('jdp-container');
                    if (!dp) return;
                    const isInsideDp = dp === e.target || dp.contains(e.target);
                    const isInput = e.target && e.target.closest && e.target.closest('[data-jdp], [data-jdp-only-date], [data-jdp-with-time], [data-jdp-only-time], input[data-jdp]');
                    if (!isInsideDp && !isInput) {
                        window.jalaliDatepicker.hide();
                    }
                }
            }, true);
        </script>
    @endpush
@endsection
