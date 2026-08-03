@php
    use Modules\Clients\Entities\ClientForm;use Modules\Clients\Entities\ClientSetting;use Modules\Services\App\Http\Models\Status;
    use Morilog\Jalali\Jalalian;
    use Carbon\Carbon;

    if(!isset($settings)) {
        $settings = [];
    }
@endphp
@extends('layouts.user')
@section('title', 'جزئیات سفارش: ' . $order->order_number)

@include('partials.jalali-date-picker')

@php
    $currencyLabel = $currency === 'rial' ? 'ریال' : 'تومان';

    // پشتیبانی از چند فاکتور برای یک سفارش:
    // اگر مدل Order رابطه‌ی چندتایی invoices() داشته باشد از آن استفاده می‌شود،
    // در غیر این صورت از رابطه‌ی تکی invoice (سازگاری با نسخه‌های قبلی) استفاده می‌شود.
    $relatedInvoices = collect();
    if (method_exists($order, 'invoices')) {
        try {
            $relatedInvoices = $order->invoices()->with('status')->get();
        } catch (\Throwable $e) {
            $relatedInvoices = collect();
        }
    }
    if ($relatedInvoices->isEmpty() && $order->invoice) {
        $order->invoice->loadMissing('status');
        $relatedInvoices = collect([$order->invoice]);
    }

    // فاکتور اصلی برای محاسبات مربوط به تمدید/فیلدهای سفارشی/یادداشت/تاریخچه (سازگاری با نسخه قبلی)
    $invoice = $order->invoice ?? $relatedInvoices->first();

    $toJalali = function ($date) {
        if (!$date) return null;
        $carbon = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
        return Jalalian::fromCarbon($carbon);
    };

    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $allStatusesForColor = Status::whereIn('type', ['payment', 'invoice'])->get()->keyBy('name');
    $getInvoiceStatusColor = function ($inv) use ($allStatusesForColor) {
        $name = $inv->status?->name ?? '—';
        return $allStatusesForColor[$name]->color ?? ($inv->status?->color ?? '#6b7280');
    };

    $displayCustomFields = [];
    $invoiceItem = null;
    if ($invoice) {
        if ($order->service_id) {
            $invoiceItem = $invoice->items->where('service_id', $order->service_id)->first();
        } else {
            $invoiceItem = $invoice->items->where('custom_service_name', $order->notes)->first()
                ?? $invoice->items->where('description', $order->notes)->first();
        }
    }

    if ($invoiceItem && !empty($invoiceItem->meta['custom_fields']) && $order->service) {
        $customFieldsData = $invoiceItem->meta['custom_fields'];
        $customFieldsPrices = $invoiceItem->meta['custom_fields_prices'] ?? [];
        $basePrice = $invoiceItem->unit_price ?? 0;
        $serviceCustomFields = $order->service->customFields->keyBy('id');

        foreach ($customFieldsData as $fieldId => $value) {
            $field = $serviceCustomFields->get($fieldId);
            if ($field) {
                $displayValue = $value;
                if ($field->type === 'checkbox') {
                    $displayValue = in_array($value, [true, '1', 1], true) ? 'بله' : 'خیر';
                } elseif ($field->type === 'multiselect' && is_array($value)) {
                    $displayValue = implode('، ', $value);
                } elseif (is_array($value)) {
                    $displayValue = implode('، ', $value);
                }

                if ($displayValue !== '' && $displayValue !== null && $displayValue !== 'خیر') {
                    $price = 0;
                    $hasPricing = $field->has_pricing ?? false;

                    if ($hasPricing) {
                        if (isset($customFieldsPrices[$fieldId])) {
                            $price = (float)$customFieldsPrices[$fieldId];
                        } else {
                            $price = $field->pricing_type === 'percentage'
                                ? $basePrice * ((float)($field->pricing_amount ?? 0) / 100)
                                : (float)($field->pricing_amount ?? 0);
                        }
                    }

                    $displayCustomFields[] = [
                        'label' => $field->label,
                        'value' => $displayValue,
                        'has_pricing' => $hasPricing,
                        'price' => $price,
                    ];
                }
            }
        }
    }

    $clientFieldsSetting = \Modules\Settings\Entities\Setting::where('key', 'services_invoice_client_fields')->value('value');
    $selectedClientFields = $clientFieldsSetting ? json_decode($clientFieldsSetting, true) : [];
    $customerCustomFields = [];

    if ($order->customer && !empty($selectedClientFields)) {
        $clientForm = null;
        if (class_exists(ClientForm::class) && class_exists(ClientSetting::class)) {
            $clientForm = ClientForm::active(
                ClientSetting::getValue('default_form_key')
            );
        }
        $clientSchemaFields = $clientForm ? collect($clientForm->schema['fields'] ?? [])->keyBy('id') : collect();

        foreach ($selectedClientFields as $fieldId) {
            $fieldDef = $clientSchemaFields->get($fieldId);
            $label = $fieldDef['label'] ?? $fieldId;

            $val = $order->customer->{$fieldId} ?? null;
            if ($val === null && isset($order->customer->form_data) && is_array($order->customer->form_data)) {
                $val = $order->customer->form_data[$fieldId] ?? null;
            }
            if ($val === null && isset($order->customer->meta) && is_array($order->customer->meta)) {
                $val = $order->customer->meta[$fieldId] ?? null;
            }

            if (is_array($val)) {
                $val = implode('، ', $val);
            }

            if ($val !== null && $val !== '') {
                $customerCustomFields[] = [
                    'label' => $label,
                    'value' => $val
                ];
            }
        }
    }

    $statusColor = $order->status?->color ?? '#6b7280';
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-base text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/15 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-white dark:focus:border-indigo-500 dark:focus:bg-gray-900";

    $remainingAmount = $invoice ? $invoice->remainingAmount() : 0;
    $isCanceled = $invoice ? str_contains($invoice->status?->name ?? '', 'لغو') : false;

    // فاکتورهای ادغام شده نباید در هیچ کدام جمع‌ها حساب شوند چون پرداختی‌هایشان به فاکتور جدید منتقل شده
    $nonMergedInvoices = $relatedInvoices->filter(fn($inv) => !$inv->isMerged());
    $totalInvoicesAmount = $nonMergedInvoices->sum('total');
    $totalInvoicesPaid = $nonMergedInvoices->sum('paid_amount');
    $totalInvoicesRemaining = $nonMergedInvoices->sum(fn($inv) => $inv->remainingAmount());

    $renewalDate = $order->renewal_date;
    if (!$renewalDate && $order->billing_cycle && $invoice?->issue_date) {
        try {
            $issueJalali = clone $toJalali($invoice->issue_date);
            switch ($order->billing_cycle) {
                case 'monthly':     $renewalDate = $issueJalali->addMonths(1)->format('Y/m/d'); break;
                case 'quarterly':   $renewalDate = $issueJalali->addMonths(3)->format('Y/m/d'); break;
                case 'semi_annual': $renewalDate = $issueJalali->addMonths(6)->format('Y/m/d'); break;
                case 'annual':      $renewalDate = $issueJalali->addYears(1)->format('Y/m/d'); break;
            }
        } catch (\Exception $e) {
            $renewalDate = clone Carbon::parse($invoice->issue_date);
            switch ($order->billing_cycle) {
                case 'monthly':     $renewalDate->addMonth(); break;
                case 'quarterly':   $renewalDate->addMonths(3); break;
                case 'semi_annual': $renewalDate->addMonths(6); break;
                case 'annual':      $renewalDate->addYear(); break;
            }
        }
    }

    $daysDiff = $renewalDate ? Carbon::now()->startOfDay()->diffInDays(Carbon::parse($renewalDate)->startOfDay(), false) : null;
    $isExpired = $daysDiff !== null && $daysDiff < 0;
    $isNear = $daysDiff !== null && $daysDiff >= 0 && $daysDiff <= 7;

    $billingCycleLabels = [
        'monthly' => 'ماهانه', 'quarterly' => 'فصلی',
        'semi_annual' => 'شش ماهه', 'annual' => 'سالانه',
    ];

    $basePrice = $order->service?->base_price ?? 0;

    if ($invoice) {
        if ($invoice->items->count() === 1) {
            $finalServicePrice = $invoice->total;
        } elseif ($invoiceItem) {
            $rowBaseWithTax = $invoiceItem->total + ($invoiceItem->tax_amount ?? 0);
            if (($invoice->tax_percent ?? 0) > 0 && ($invoiceItem->tax_amount ?? 0) == 0) {
                $rowBaseWithTax += $rowBaseWithTax * ($invoice->tax_percent / 100);
            }
            if (($invoice->extra_discount_value ?? 0) > 0 && $invoice->subtotal > 0) {
                $extraDiscShare = $invoice->extra_discount_value * ($rowBaseWithTax / $invoice->subtotal);
                $rowBaseWithTax = max(0, $rowBaseWithTax - $extraDiscShare);
            }
            $finalServicePrice = (int) round($rowBaseWithTax);
        } else {
            $finalServicePrice = $invoice->total;
        }
    } else {
        $finalServicePrice = $order->first_payment_amount ?? $order->total_amount;
    }

    $isRenewalManual = ($order->renewal_price_type ?? 'auto') === 'manual';
    $calculatedRenewalPrice = $isRenewalManual
        ? ($order->renewal_price ?? 0)
        : (($order->service && $order->billing_cycle && isset($order->service->renewal_prices[$order->billing_cycle]))
            ? $order->service->renewal_prices[$order->billing_cycle]
            : ($order->renewal_price ?? 0));
@endphp

@section('content')
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="orderManager()">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm font-medium" aria-label="Breadcrumb">
            <a href="{{ route('services.orders.index') }}"
               class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">لیست
                سفارشات</a>
            <svg class="w-4 h-4 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                 stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            <span
                class="text-gray-900 dark:text-white font-bold truncate tabular-nums">{{ $faNum($order->order_number) }}</span>
        </nav>

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path
                            stroke-linecap="round" stroke-linejoin="round"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                جزئیات سفارش <span
                    class="text-indigo-600 dark:text-indigo-400 dir-ltr">{{ $order->order_number }}</span>
            </h1>
            <a href="{{ request('from') === 'client' ? route('user.clients.show', [$order->customer_id ?? ($order->invoice ? $order->invoice->customer_id : 1)]) . '#orders' : route('services.orders.index') }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت
            </a>
        </div>

        @if(session('success'))
            <div
                class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400 text-sm font-bold flex items-center gap-3">
                <span class="bg-emerald-100 text-emerald-600 p-1.5 rounded-full dark:bg-emerald-500/20"><svg
                        class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path
                            stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                {{ session('success') }}
            </div>
        @endif

        {{-- خلاصه سریع سفارش --}}
        <div class="grid grid-cols-1 md:grid-cols-2 {{ $order->billing_cycle ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }} gap-6">
            {{-- مشتری --}}
            <div
                class="bg-white dark:bg-gray-800/60 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-4">
                    <div class="p-2 bg-sky-50 dark:bg-sky-500/10 text-sky-500 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-black text-gray-400 uppercase">مشتری</span>
                </div>
                <div
                    class="font-black text-gray-900 dark:text-white text-lg truncate">{{ $order->customer?->full_name ?? $order->client_name ?? '—' }}</div>
                @if($order->customer?->phone || $invoice?->client_phone)
                    <div
                        class="text-sm font-bold text-gray-500 mt-2 dir-ltr tabular-nums truncate text-right">{{ $faNum($order->customer?->phone ?? $invoice?->client_phone) }}</div>
                @endif
                @if($order->customer?->email || $invoice?->client_email)
                    <div
                        class="text-xs text-gray-400 mt-1 truncate">{{ $order->customer?->email ?? $invoice?->client_email }}</div>
                @endif
            </div>

            {{-- وضعیت --}}
            <div class="bg-white dark:bg-gray-800/60 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-6 relative z-20">
                <div class="flex items-center gap-2 mb-4">
                    <div class="p-2 bg-violet-50 dark:bg-violet-500/10 text-violet-500 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-black text-gray-400 uppercase">وضعیت سفارش</span>
                </div>
                <div class="mt-2 relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open" @click.away="open = false"
                            class="w-full inline-flex items-center justify-between gap-2 px-4 py-2.5 rounded-xl text-sm font-black border transition-all hover:opacity-80 focus:ring-4 focus:outline-none"
                            style="background: {{ $statusColor }}15; color: {{ $statusColor }}; border-color: {{ $statusColor }}40; --tw-ring-color: {{ $statusColor }}30">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full animate-pulse" style="background: {{ $statusColor }}"></span>
                            {{ $order->status?->name ?? 'نامشخص' }}
                        </span>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open" x-transition.opacity.duration.200ms
                         style="display: none;"
                         class="absolute z-50 top-full left-0 right-0 mt-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-xl overflow-hidden max-h-60 overflow-y-auto ring-1 ring-black ring-opacity-5">
                        @foreach($statuses as $st)
                            <form action="{{ route('services.orders.update', $order) }}" method="POST" class="m-0">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status_id" value="{{ $st->id }}">
                                <input type="hidden" name="renewal_price_type" value="{{ $order->renewal_price_type ?? 'auto' }}">
                                <button type="submit"
                                        class="w-full text-start px-4 py-3 text-sm font-bold transition-colors hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-between border-b border-gray-50 dark:border-gray-700/50 last:border-0"
                                        style="color: {{ $st->color }}">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full" style="background: {{ $st->color }}"></span>
                                        {{ $st->name }}
                                    </span>
                                    @if($order->status_id == $st->id)
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @endif
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- مبلغ نهایی سرویس --}}
            <div
                class="bg-white dark:bg-gray-800/60 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-4">
                    <div class="p-2 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-500 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-black text-gray-400 uppercase">مبلغ نهایی سرویس</span>
                </div>
                <div
                    class="font-black text-gray-900 dark:text-white text-xl tabular-nums">{{ $faNum(number_format($finalServicePrice)) }}
                    <span class="text-sm text-gray-400 font-normal">{{ $currencyLabel }}</span></div>
                <p class="text-[10px] text-gray-400 mt-2">ثابت و غیرقابل تغییر</p>
            </div>

            {{-- مبلغ تمدید --}}
            @if($order->billing_cycle)
                <div
                    class="bg-white dark:bg-gray-800/60 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-amber-50 dark:bg-amber-500/10 text-amber-500 rounded-lg">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0-0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-black text-gray-400 uppercase">مبلغ تمدید</span>
                        </div>
                        <span
                            class="text-[10px] font-bold px-2 py-0.5 rounded-md {{ $isRenewalManual ? 'bg-amber-100 text-amber-700' : 'bg-indigo-100 text-indigo-700' }}">{{ $isRenewalManual ? 'دستی' : 'خودکار' }}</span>
                    </div>
                    <div
                        class="font-black text-gray-900 dark:text-white text-xl tabular-nums">{{ $faNum(number_format($calculatedRenewalPrice)) }}
                        <span class="text-sm text-gray-400 font-normal">{{ $currencyLabel }}</span></div>
                    @if($order->billing_cycle)
                        <span
                            class="inline-block mt-2 text-xs font-black text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-500/20 px-2.5 py-1 rounded-md">{{ $billingCycleLabels[$order->billing_cycle] ?? $order->billing_cycle }}</span>
                    @endif
                </div>
            @endif
        </div>

        {{-- ================= فاکتورهای مرتبط (Full Width) ================= --}}
        @if($relatedInvoices->isNotEmpty())
            <div class="{{ $cardClass }}">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-l from-amber-50 to-transparent dark:from-amber-500/10 flex items-center justify-between flex-wrap gap-3">
                    <h3 class="text-lg font-black text-amber-700 dark:text-amber-400 flex items-center gap-3">
                        <div class="p-2 bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        فاکتورهای مرتبط
                    </h3>
                    <span class="text-sm font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-3 py-1.5 rounded-lg border border-amber-100 dark:border-amber-500/20">{{ $faNum($relatedInvoices->count()) }} فاکتور</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-start border-collapse min-w-[900px]">
                        <thead class="bg-gray-50/80 dark:bg-gray-900/30 text-gray-500 dark:text-gray-400 font-bold border-b border-gray-100 dark:border-gray-700/50 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 font-bold text-start">شماره فاکتور</th>
                            <th class="px-4 py-3 font-bold text-center">وضعیت</th>
                            <th class="px-4 py-3 font-bold text-center">تاریخ صدور</th>
                            <th class="px-4 py-3 font-bold text-center">سررسید</th>
                            <th class="px-4 py-3 font-bold text-center">مبلغ کل</th>
                            <th class="px-4 py-3 font-bold text-center">پرداخت‌شده</th>
                            <th class="px-4 py-3 font-bold text-center">مانده</th>
                            <th class="px-4 py-3 font-bold text-center"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @foreach($relatedInvoices as $inv)
                            @php
                                $invStatusColor = $getInvoiceStatusColor($inv);
                                $invRemaining = $inv->remainingAmount();
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 py-4 font-black text-gray-900 dark:text-white tabular-nums text-start">
                                    {{ $faNum($inv->invoice_number ?: $inv->proforma_invoice_number) }}
                                    @if(!empty($inv->meta['created_by_workflow']))
                                        <span class="block mt-1 text-[10px] font-bold text-indigo-500 bg-indigo-50 dark:bg-indigo-500/10 px-2 py-0.5 rounded border border-indigo-100 dark:border-indigo-500/20 w-max">سیستمی</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border"
                                          style="background: {{ $invStatusColor }}15; color: {{ $invStatusColor }}; border-color: {{ $invStatusColor }}40">
                                        <span class="w-2 h-2 rounded-full" style="background: {{ $invStatusColor }}"></span>{{ $inv->status?->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center tabular-nums text-gray-600 dark:text-gray-400 dir-ltr">
                                    {{ $faNum($toJalali($inv->issue_date)?->format('Y/m/d')) }}
                                </td>
                                <td class="px-4 py-4 text-center tabular-nums text-gray-600 dark:text-gray-400 dir-ltr">
                                    {{ $inv->due_date ? $faNum($toJalali($inv->due_date)->format('Y/m/d')) : '—' }}
                                </td>
                                <td class="px-4 py-4 text-center font-bold tabular-nums text-gray-800 dark:text-gray-200">
                                    {{ $faNum(number_format($inv->total)) }}
                                    <span class="text-[10px] text-gray-400 ms-1">{{ $currencyLabel }}</span>
                                </td>
                                <td class="px-4 py-4 text-center font-bold tabular-nums {{ $inv->isMerged() ? 'text-gray-400 dark:text-gray-600' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    @if($inv->isMerged())
                                        —
                                    @else
                                        {{ $faNum(number_format($inv->paid_amount)) }}
                                        <span class="text-[10px] text-emerald-400/80 ms-1">{{ $currencyLabel }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center font-black tabular-nums {{ $invRemaining > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-400' }}">
                                    @if($invRemaining > 0)
                                        {{ $faNum(number_format($invRemaining)) }}
                                        <span class="text-[10px] font-normal ms-1">{{ $currencyLabel }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <a href="{{ route('services.invoices.show', $inv) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 font-bold text-xs hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-colors">
                                        مشاهده فاکتور
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- جمع کل ترکیبی، وقتی بیش از یک فاکتور وجود دارد --}}
                @if($relatedInvoices->count() > 1)
                    <div class="bg-gray-50/80 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700/50 p-6 grid grid-cols-1 sm:grid-cols-3 gap-6 divide-y sm:divide-y-0 sm:divide-x sm:divide-x-reverse divide-gray-200 dark:divide-gray-700">
                        <div class="flex flex-col items-center  px-4">
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide block mb-1">جمع کل فاکتورها</span>
                            <div class="flex items-baseline gap-1 mt-1">
                                <span class="text-lg font-black text-gray-900 dark:text-white tabular-nums">{{ $faNum(number_format($totalInvoicesAmount)) }}</span>
                                <span class="text-xs font-medium text-gray-900">{{ $currencyLabel }}</span>
                            </div>
                        </div>
                        <div class="flex flex-col items-center  px-4 pt-4 sm:pt-0">
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide block mb-1">مجموع پرداختی</span>
                            <div class="flex items-baseline gap-1 mt-1">
                                <span class="text-lg font-black text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $faNum(number_format($totalInvoicesPaid)) }}</span>
                                <span class="text-xs font-medium text-emerald-700">{{ $currencyLabel }}</span>
                            </div>
                        </div>
                        <div class="flex flex-col items-center  px-4 pt-4 sm:pt-0">
                            <span class="text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wide block mb-1">مجموع مانده بدهی</span>
                            <div class="flex items-baseline gap-1 mt-1">
                                <span class="text-lg font-black text-rose-700 dark:text-rose-400 tabular-nums">{{ $faNum(number_format($totalInvoicesRemaining)) }}</span>
                                <span class="text-xs font-medium text-rose-700">{{ $currencyLabel }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            {{-- ستون سمت راست --}}
            <div class="space-y-8">

                {{-- اطلاعات سرویس --}}
                <div class="{{ $cardClass }}">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                        <h3 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                            <div
                                class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            اطلاعات سرویس
                        </h3>
                    </div>
                    <div class="p-6 space-y-6">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div
                                class="bg-gray-50 dark:bg-gray-900/40 p-5 rounded-2xl border border-gray-100 dark:border-gray-800">
                                <span class="block text-xs font-bold text-gray-400 mb-2">نام سرویس</span>
                                <span
                                    class="font-black text-gray-900 dark:text-white text-lg">{{ $order->service?->name ?? $order->notes ?? 'ردیف دستی' }}</span>
                            </div>

                            <div
                                class="bg-amber-50/50 dark:bg-amber-900/10 p-5 rounded-2xl border border-amber-100 dark:border-amber-800/30">
                                <span
                                    class="block text-xs font-bold text-amber-500 dark:text-amber-400 mb-2">مبلغ پایه</span>
                                <div class="font-black text-gray-900 dark:text-white text-xl tabular-nums mb-2">
                                    {{ $faNum(number_format($basePrice)) }} <span
                                        class="text-xs text-gray-500 font-normal">{{ $currencyLabel }}</span>
                                </div>
                                <p class="text-[10.5px] text-gray-500 dark:text-gray-400 leading-relaxed">
                                    این مبلغ به صورت خودکار از سرویس دریافت می‌شود.
                                </p>
                            </div>
                        </div>

                        @if(!empty($displayCustomFields))
                            <div class="pt-6 border-t border-gray-100 dark:border-gray-700/50">
                                <h4 class="text-sm font-black text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                    فیلدهای سفارشی سرویس
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($displayCustomFields as $cf)
                                        <div
                                            class="bg-gray-50/80 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800">
                                            <span
                                                class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">{{ $cf['label'] }}</span>
                                            <div
                                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                                <span
                                                    class="font-bold text-gray-800 dark:text-gray-200 text-sm break-words">{{ $cf['value'] }}</span>
                                                @if($cf['has_pricing'] && $cf['price'] > 0)
                                                    <span
                                                        class="inline-flex items-center justify-center gap-1 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20 px-2.5 py-1 rounded-lg text-xs font-bold tabular-nums shrink-0 shadow-sm">
                                                        + {{ $faNum(number_format($cf['price'])) }} <span
                                                            class="text-[10px]">{{ $currencyLabel }}</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ستون سمت چپ --}}
            <div class="space-y-8">
                @if($order->billing_cycle)
                    <form action="{{ route('services.orders.update', $order) }}" method="POST" class="{{ $cardClass }}">
                        @csrf
                        @method('PUT')
                        <div class="p-6 sm:p-8 space-y-8">
                            <div
                                class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-6">
                                <h3 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                                    <div
                                        class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    مدیریت مالی و تمدید
                                </h3>
                                <div
                                    class="flex items-center gap-3 bg-gray-50 dark:bg-gray-800 px-4 py-2 rounded-full border border-gray-200 dark:border-gray-700 shadow-sm cursor-pointer select-none"
                                    @click="editMode = !editMode">
                                <span class="text-sm font-black text-gray-600 dark:text-gray-300"
                                      :class="editMode ? 'text-indigo-600 dark:text-indigo-400' : ''">حالت ویرایش</span>
                                    <button type="button"
                                            class="relative inline-flex h-6 w-11 shrink-0 rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 pointer-events-none"
                                            :class="editMode ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'">
                                    <span
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                        :class="editMode ? '-translate-x-5' : 'translate-x-0'"></span>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">تاریخ تمدید
                                        (سررسید)</label>
                                    <input type="text" name="renewal_date"
                                           value="{{ $order->renewal_date ? $toJalali($order->renewal_date)->format('Y/m/d') : '' }}"
                                           :data-jdp="editMode ? '' : null" :data-jdp-only-date="editMode ? '' : null"
                                           :readonly="!editMode" class="{{ $inputClass }} dir-ltr text-left"
                                           :class="!editMode ? 'bg-gray-100 cursor-not-allowed text-gray-500' : 'cursor-pointer'"
                                           placeholder="انتخاب تاریخ">
                                </div>
                            </div>

                            <div class="pt-6 border-t border-gray-100 dark:border-gray-700">
                                {{-- مبلغ تمدید --}}
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">مبلغ تمدید
                                            دوره‌ای</label>
                                        <input type="hidden" name="renewal_price_type" :value="renewalPriceType">
                                        <button type="button" x-show="editMode"
                                                @click="renewalPriceType = (renewalPriceType === 'auto' ? 'manual' : 'auto')"
                                                class="px-2.5 py-1 text-xs font-bold rounded-lg transition-colors border"
                                                :class="renewalPriceType === 'manual' ? 'bg-amber-100 text-amber-700 border-amber-300' : 'bg-indigo-100 text-indigo-700 border-indigo-300'"
                                                x-text="renewalPriceType === 'manual' ? 'حالت: دستی' : 'حالت: خودکار'"></button>
                                        <span x-show="!editMode" class="text-xs font-bold px-2 py-0.5 rounded-md"
                                              :class="renewalPriceType === 'manual' ? 'bg-amber-100 text-amber-700' : 'bg-indigo-100 text-indigo-700'"
                                              x-text="renewalPriceType === 'manual' ? 'دستی' : 'خودکار'"></span>
                                    </div>
                                    <div class="relative">
                                        <input type="text" x-model="renewalPrice" @input="formatRenewalPrice($event)"
                                               name="renewal_price"
                                               class="{{ $inputClass }} font-bold tabular-nums dir-ltr pl-14 text-left"
                                               :readonly="!editMode || renewalPriceType === 'auto'"
                                               :class="(!editMode || renewalPriceType === 'auto') ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed text-gray-500' : ''"
                                               dir="ltr" placeholder="مبلغ...">
                                        <span
                                            class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500 text-sm font-bold">{{ $currencyLabel }}</span>
                                    </div>
                                </div>
                            </div>

                            <div x-show="editMode" x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-4"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="pt-6 border-t border-gray-100 dark:border-gray-700">
                                <button type="submit"
                                        class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-indigo-600 text-white font-black text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all active:scale-95 flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    ذخیره تغییرات
                                </button>
                            </div>
                        </div>
                    </form>
                @endif

                @if($invoice?->notes)
                    <div class="{{ $cardClass }} p-6 border-l-4 border-l-amber-500 bg-amber-50/50 dark:bg-amber-900/10">
                        <div class="flex items-start gap-4 text-amber-800 dark:text-amber-300">
                            <svg class="w-8 h-8 shrink-0 mt-0.5 opacity-80" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <div>
                                <strong class="block mb-2 font-black text-lg tracking-tight">یادداشت فاکتور</strong>
                                <p class="font-medium text-base leading-loose">{{ $invoice->notes }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($invoice?->activities?->isNotEmpty())
                    <div class="{{ $cardClass }}">
                        <div
                            class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                            <h3 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                                <div
                                    class="p-2 bg-gray-100 text-gray-600 dark:bg-gray-700/40 dark:text-gray-300 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                تاریخچه فعالیت
                            </h3>
                        </div>
                        <div class="p-6">
                            <ol class="relative border-e-2 border-gray-100 dark:border-gray-700/50 pe-6 space-y-6">
                                @foreach($invoice->activities->take(8) as $log)
                                    <li class="relative">
                                        <span
                                            class="absolute top-1.5 -end-[27px] w-3 h-3 rounded-full bg-indigo-500 ring-4 ring-white dark:ring-gray-800"></span>
                                        <span
                                            class="text-base text-gray-800 dark:text-gray-200 font-bold block">{{ $log->description ?? $log->action }}</span>
                                        <div class="flex items-center gap-3 mt-2">
                                            @if($log->user)
                                                <span
                                                    class="text-xs font-bold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-700 px-2.5 py-1 rounded-md flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor" stroke-width="2"><path
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                    {{ $log->user->name }}
                                                </span>
                                            @endif
                                            <span
                                                class="text-xs font-medium text-gray-400 dir-ltr tabular-nums">{{ $faNum($toJalali($log->created_at)->format('Y-m-d H:i')) }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('orderManager', () => ({
                    editMode: false,
                    renewalPriceType: '{{ $order->renewal_price_type ?? "auto" }}',
                    renewalPrice: '{{ number_format($isRenewalManual ? ($order->renewal_price ?? 0) : (($order->service && $order->billing_cycle && isset($order->service->renewal_prices[$order->billing_cycle])) ? $order->service->renewal_prices[$order->billing_cycle] : ($order->renewal_price ?? 0))) }}',

                    init() {
                        this.$watch('editMode', value => {
                            if (value) {
                                this.$nextTick(() => {
                                    if (typeof jalaliDatepicker !== 'undefined') {
                                        jalaliDatepicker.startWatch();
                                    }
                                });
                            }
                        });

                        this.$watch('renewalPriceType', value => {
                            if (value === 'auto') {
                                this.renewalPrice = '{{ number_format((($order->service && $order->billing_cycle && isset($order->service->renewal_prices[$order->billing_cycle])) ? $order->service->renewal_prices[$order->billing_cycle] : ($order->renewal_price ?? 0))) }}';
                            } else {
                                this.renewalPrice = '{{ number_format($order->renewal_price ?? 0) }}';
                            }
                        });
                    },
                    formatRenewalPrice(e) {
                        if (this.renewalPriceType === 'auto') return;
                        let num = e.target.value.replace(/[^\d]/g, '');
                        if (num) {
                            this.renewalPrice = Number(num).toLocaleString('en-US');
                        } else {
                            this.renewalPrice = '';
                        }
                    }
                }));
            });
        </script>
    @endpush
@endsection
