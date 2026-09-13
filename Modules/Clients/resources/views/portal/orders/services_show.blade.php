@extends('clients::layouts.client')

@php
    $title = 'جزئیات سفارش #' . \Morilog\Jalali\CalendarUtils::convertNumbers($order->order_number);

    $statusName = $order->status?->name ?? 'نامشخص';
    $isActive = str_contains($statusName, 'فعال') && !str_contains($statusName, 'غیر');
    $isPending = str_contains($statusName, 'انتظار') || str_contains($statusName, 'بررسی');
    $isCanceled = str_contains($statusName, 'لغو') || str_contains($statusName, 'غیر فعال');

    $orderStatusColor = match(true) {
        $isActive => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/30 dark:border-emerald-800/40 dark:text-emerald-400',
        $isPending => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/30 dark:border-amber-800/40 dark:text-amber-400',
        $isCanceled => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/30 dark:border-rose-800/40 dark:text-rose-400',
        default => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/30 dark:border-blue-800/40 dark:text-blue-400',
    };

    $orderStatusDot = match(true) {
        $isActive => 'bg-emerald-500',
        $isPending => 'bg-amber-500',
        $isCanceled => 'bg-rose-500',
        default => 'bg-blue-500',
    };

    $cycleLabel = match($order->billing_cycle) {
        'monthly' => 'ماهانه',
        'quarterly' => '۳ ماهه',
        'semi_annual', 'semi-annual' => '۶ ماهه',
        'annual' => 'سالانه',
        'biennial' => '۲ ساله',
        'triennial' => '۳ ساله',
        'one_time', 'onetime' => 'یکباره',
        default => $order->billing_cycle ?: 'یکباره'
    };

    $orderTotal = $order->total_amount ?: $order->first_payment_amount ?: 0;

    $relatedInvoices = $order->related_invoices;
    $primaryInvoice = $order->invoice ?? $relatedInvoices->first();

    // Extract custom fields from the order's primary invoice item
    $displayCustomFields = [];
    $packageTitle = null;
    $invoiceItem = null;

    if ($primaryInvoice) {
        if ($order->service_id) {
            $invoiceItem = $primaryInvoice->items->where('service_id', $order->service_id)->first();
        } else {
            $invoiceItem = $primaryInvoice->items->where('custom_service_name', $order->notes)->first()
                ?? $primaryInvoice->items->where('description', $order->notes)->first();
        }
    }

    if ($invoiceItem) {
        $packageTitle = $invoiceItem->meta['_packageTitle'] ?? null;
        if (!empty($invoiceItem->meta['custom_fields']) && $order->service) {
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
                            if ($field->type === 'multiselect' && is_array($value)) {
                                $totalPrice = 0;
                                foreach ($value as $opt) {
                                    $optPrice = is_array($customFieldsPrices[$fieldId] ?? null)
                                        ? ($customFieldsPrices[$fieldId][$opt] ?? null)
                                        : null;
                                    if ($optPrice === null) {
                                        $optPrice = $field->getOptionPrice($opt, $basePrice);
                                    }
                                    $totalPrice += (float)$optPrice;
                                }
                                $price = $totalPrice;
                            } else {
                                if (isset($customFieldsPrices[$fieldId]) && !is_array($customFieldsPrices[$fieldId])) {
                                    $price = (float)$customFieldsPrices[$fieldId];
                                } else {
                                    if (in_array($field->type, ['select', 'radio'])) {
                                        $price = $field->getOptionPrice($displayValue, $basePrice);
                                    } else {
                                        $price = $field->pricing_type === 'percentage'
                                            ? $basePrice * ((float)($field->pricing_amount ?? 0) / 100)
                                            : (float)($field->pricing_amount ?? 0);
                                    }
                                }
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
    }
@endphp


@section('content')
<div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-5xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <a href="{{ route('client.orders.index') }}"
                   class="p-2 -m-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-gray-800 transition-colors"
                   title="بازگشت به لیست سفارش‌ها">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
                <span>جزئیات سفارش</span>
                <span class="text-lg font-extrabold text-blue-600 dark:text-blue-400"
                      dir="ltr">#{{ \Morilog\Jalali\CalendarUtils::convertNumbers($order->order_number) }}</span>
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">شناسه یکتای سفارش در سامانه جهت مدیریت، پیگیری و
                دریافت خدمات</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @php
                $headerInvoice = $primaryInvoice ?: $relatedInvoices->first();
            @endphp
            @if($headerInvoice)
                <a href="{{ route('client.invoices.show', $headerInvoice->id) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-all text-xs font-bold shadow-md shadow-blue-600/20 active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>مشاهده صورت‌حساب</span>
                    @if($headerInvoice->invoice_number)
                        <span class="px-1.5 py-0.5 rounded-md bg-white/20 text-[10px]"
                              dir="ltr">#{{ \Morilog\Jalali\CalendarUtils::convertNumbers($headerInvoice->invoice_number) }}</span>
                    @endif
                </a>
            @endif


            <a href="{{ route('client.orders.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl transition-colors text-xs font-medium shadow-sm">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <span>لیست سفارش‌ها</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div
            class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/30 text-emerald-700 dark:text-emerald-400 flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div
            class="p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30 text-red-700 dark:text-red-400 flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <div
        class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-xl shadow-gray-200/40 dark:shadow-none relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600"></div>
        <div
            class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-gray-100 dark:border-gray-700 border-dashed">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-gray-400 mb-1.5">
                    <span>شماره سفارش:</span>
                    <span class="text-gray-600 dark:text-gray-300"
                          dir="ltr">#{{ \Morilog\Jalali\CalendarUtils::convertNumbers($order->order_number) }}</span>
                </div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                    {{ optional($order->service)->name ?: 'سرویس خدمات' }}
                </h2>
                <div class="mt-3 flex flex-wrap items-center gap-2.5">
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold border {{ $orderStatusColor }}">
                        <span class="w-2 h-2 rounded-full {{ $orderStatusDot }} animate-pulse"></span>
                        {{ $statusName }}
                    </span>
                    @if(optional($order->service)->category)
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round"
                                                             stroke-width="2"
                                                             d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            {{ $order->service->category->name }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-left">
                <div
                    class="bg-gray-50/80 dark:bg-gray-900/40 p-3.5 rounded-2xl border border-gray-100 dark:border-gray-700/60">
                    <span class="text-[11px] font-bold text-gray-400 block mb-1">تاریخ صدور</span>
                    <span
                        class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white">{{ $order->issue_date ? \Morilog\Jalali\CalendarUtils::convertNumbers(jdate($order->issue_date)->format('Y/m/d')) : '---' }}</span>
                </div>
                <div
                    class="bg-gray-50/80 dark:bg-gray-900/40 p-3.5 rounded-2xl border border-gray-100 dark:border-gray-700/60">
                    <span class="text-[11px] font-bold text-gray-400 block mb-1">دوره صورت‌حساب</span>
                    <span class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white">{{ $cycleLabel }}</span>
                </div>
                <div
                    class="bg-gray-50/80 dark:bg-gray-900/40 p-3.5 rounded-2xl border border-gray-100 dark:border-gray-700/60">
                    <span class="text-[11px] font-bold text-gray-400 block mb-1">سررسید بعدی</span>
                    <span
                        class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white">{{ $order->effective_renewal_date ? \Morilog\Jalali\CalendarUtils::convertNumbers(jdate($order->effective_renewal_date)->format('Y/m/d')) : ($order->billing_cycle === 'one_time' ? 'یکباره' : '---') }}</span>
                </div>

                <div
                    class="bg-gray-50/80 dark:bg-gray-900/40 p-3.5 rounded-2xl border border-gray-100 dark:border-gray-700/60">
                    <span class="text-[11px] font-bold text-gray-400 block mb-1">مبلغ سفارش</span>
                    <span class="font-bold text-xs sm:text-sm text-blue-600 dark:text-blue-400">{{ \Morilog\Jalali\CalendarUtils::convertNumbers(number_format($orderTotal)) }} <span
                            class="text-[10px] font-normal text-gray-500">{{ $order->currency_label ?? 'ریال' }}</span></span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
            <div class="lg:col-span-2 space-y-6">
                <div
                    class="bg-gray-50/60 dark:bg-gray-900/30 rounded-2xl p-5 sm:p-6 border border-gray-100 dark:border-gray-700/60 space-y-5">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        مشخصات و شرایط سرویس سفارش داده‌شده
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div
                            class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                            <span class="block text-xs text-gray-400 mb-1">عنوان کامل سرویس</span>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-sm text-gray-900 dark:text-white">{{ optional($order->service)->name ?: 'سرویس خدمات' }}</span>
                                @if($packageTitle)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-500/10 border border-amber-300 dark:border-amber-500/20 px-2 py-0.5 rounded-md">
                                        پکیج: {{ $packageTitle }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div
                            class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                            <span class="block text-xs text-gray-400 mb-1">دسته‌بندی خدمت</span>
                            <span
                                class="font-bold text-sm text-gray-900 dark:text-white">{{ optional(optional($order->service)->category)->name ?: 'عمومی' }}</span>
                        </div>


                        <div
                            class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                            <span class="block text-xs text-gray-400 mb-1">هزینه سفارش</span>
                            <span class="font-bold text-sm text-gray-900 dark:text-white">{{ \Morilog\Jalali\CalendarUtils::convertNumbers(number_format($orderTotal)) }} {{ $order->currency_label ?? 'ریال' }}</span>
                        </div>

                        <div
                            class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                            <span class="block text-xs text-gray-400 mb-1">هزینه تمدید در دوره‌های بعد</span>
                            <span class="font-bold text-sm text-gray-900 dark:text-white">
                                @if($order->billing_cycle === 'one_time')
                                    فاقد تمدید (سفارش یکباره)
                                @else
                                    {{ \Morilog\Jalali\CalendarUtils::convertNumbers(number_format($order->calculated_renewal_price)) }}
                                    {{ $order->currency_label ?? 'ریال' }}
                                @endif
                            </span>
                        </div>
                    </div>

                    @if(!empty($displayCustomFields))
                        <div class="pt-4 border-t border-gray-200/60 dark:border-gray-700">
                            <span class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-3 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                                مشخصات و گزینه‌های انتخابی سفارش
                            </span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($displayCustomFields as $cf)
                                    <div class="bg-white dark:bg-gray-800 p-3.5 rounded-xl border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ $cf['label'] }}:</span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $cf['value'] }}</span>
                                            @if($cf['has_pricing'] && $cf['price'] > 0)
                                                <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 px-1.5 py-0.5 rounded">
                                                    +{{ \Morilog\Jalali\CalendarUtils::convertNumbers(number_format($cf['price'])) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif


                    @if(optional($order->service)->description)
                        <div class="pt-3 border-t border-gray-200/60 dark:border-gray-700">
                            <span class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">توضیحات و ویژگی‌های سرویس</span>
                            <div
                                class="p-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-xs text-gray-700 dark:text-gray-300 leading-relaxed prose dark:prose-invert max-w-none">
                                {!! optional($order->service)->description !!}
                            </div>
                        </div>
                    @endif

                    @if($order->notes)
                        <div class="pt-3 border-t border-gray-200/60 dark:border-gray-700">
                            <span class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">یادداشت‌های اختصاصی سفارش</span>
                            <div
                                class="p-4 rounded-xl bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200/70 dark:border-amber-900/40 text-xs text-amber-900 dark:text-amber-200 leading-relaxed flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 mt-0.5 shrink-0" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>{!! nl2br(e($order->notes)) !!}</div>
                            </div>
                        </div>
                    @endif
                </div>

            </div>

            <div class="lg:col-span-1 space-y-6">
                @if($relatedInvoices->isNotEmpty())
                    <div
                        class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xs font-bold text-gray-900 dark:text-white">صورت‌حساب‌های این سفارش</h3>
                            </div>
                            <span class="text-xs text-gray-400 font-medium">
                                {{ \Morilog\Jalali\CalendarUtils::convertNumbers($relatedInvoices->count()) }} فاکتور
                            </span>
                        </div>

                        <div class="space-y-2.5 divide-y divide-gray-100 dark:divide-gray-700/60">
                            @foreach($relatedInvoices as $relInv)
                                @php
                                    $rStatusName = $relInv->status?->name ?? 'نامشخص';
                                    $rIsPaid = str_contains($rStatusName, 'پرداخت شده');
                                    $rIsPending = str_contains($rStatusName, 'انتظار') || str_contains($rStatusName, 'معوقه');
                                    $rIsCanceled = str_contains($rStatusName, 'لغو') || str_contains($rStatusName, 'ادغام');

                                    $rStatusClass = match(true) {
                                        $rIsPaid => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400',
                                        $rIsPending => 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400',
                                        $rIsCanceled => 'bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-400',
                                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                    };

                                    $isPrimary = $relInv->id == $order->invoice_id;
                                    $isRenewal = !empty($relInv->meta['source_order_id']) && $relInv->id != $order->invoice_id;
                                @endphp
                                <div class="pt-2.5 first:pt-0 flex items-center justify-between gap-3 text-xs">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <a href="{{ route('client.invoices.show', $relInv->id) }}"
                                               class="font-bold text-gray-900 dark:text-white hover:text-blue-600 transition-colors"
                                               dir="ltr">#{{ \Morilog\Jalali\CalendarUtils::convertNumbers($relInv->invoice_number ?: $relInv->proforma_invoice_number) }}</a>
                                            @if($isRenewal)
                                                <span class="text-[9.5px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 px-1.5 py-0.5 rounded">
                                                    تمدید دوره‌ای
                                                </span>
                                            @elseif($isPrimary)
                                                <span class="text-[9.5px] font-bold text-gray-500 bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">
                                                    فاکتور اولیه
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-gray-400 mt-0.5">
                                            <span>{{ \Morilog\Jalali\CalendarUtils::convertNumbers(number_format($relInv->total)) }} {{ $relInv->currency_label ?? 'ریال' }}</span>
                                            @if($relInv->issue_date)
                                                <span> • {{ \Morilog\Jalali\CalendarUtils::convertNumbers(jdate($relInv->issue_date)->format('Y/m/d')) }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold {{ $rStatusClass }}">
                                            {{ $rStatusName }}
                                        </span>
                                        <a href="{{ route('client.invoices.show', $relInv->id) }}"
                                           class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                           title="مشاهده فاکتور">
                                            <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @php
                            $pendingInvoice = $relatedInvoices->first(fn($i) => str_contains($i->status?->name ?? '', 'انتظار') || str_contains($i->status?->name ?? '', 'معوقه'));
                        @endphp
                        @if($pendingInvoice)
                            <a href="{{ route('client.invoices.show', $pendingInvoice->id) }}"
                               class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-600/20 transition-all active:scale-95">
                                <span>پرداخت فاکتور در انتظار (#{{ \Morilog\Jalali\CalendarUtils::convertNumbers($pendingInvoice->invoice_number) }})</span>
                                <svg class="w-3.5 h-3.5 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                @endif


                <div
                    class="bg-gray-50/60 dark:bg-gray-900/30 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 space-y-4">
                    <h3 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        اطلاعات سفارش‌دهنده
                    </h3>

                    <div class="space-y-3 text-xs">
                        <div
                            class="flex items-center justify-between pb-2.5 border-b border-gray-200/50 dark:border-gray-700/50">
                            <span class="text-gray-400">نام و نام خانوادگی:</span>
                            <span
                                class="font-bold text-gray-900 dark:text-white">{{ $order->client_name ?: optional($order->customer)->full_name ?: '---' }}</span>
                        </div>

                        <div
                            class="flex items-center justify-between pb-2.5 border-b border-gray-200/50 dark:border-gray-700/50">
                            <span class="text-gray-400">شماره تماس:</span>
                            <span class="font-bold text-gray-900 dark:text-white"
                                  dir="ltr">{{ \Morilog\Jalali\CalendarUtils::convertNumbers($order->client_phone ?: optional($order->customer)->phone ?: optional($order->customer)->mobile ?: '---') }}</span>
                        </div>

                        @if($order->client_email || optional($order->customer)->email)
                            <div
                                class="flex items-center justify-between pb-2.5 border-b border-gray-200/50 dark:border-gray-700/50">
                                <span class="text-gray-400">ایمیل:</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300"
                                      dir="ltr">{{ $order->client_email ?: optional($order->customer)->email }}</span>
                            </div>
                        @endif

                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">تاریخ ثبت:</span>
                            <span
                                class="font-medium text-gray-700 dark:text-gray-300">{{ $order->issue_date ? \Morilog\Jalali\CalendarUtils::convertNumbers(jdate($order->issue_date)->format('Y/m/d (l)')) : '---' }}</span>
                        </div>
                    </div>
                </div>

                <div
                    class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-dashed border-gray-200 dark:border-gray-700 text-center space-y-1.5">
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block">نیاز به تغییر یا راهنمایی دارید؟</span>
                    <p class="text-[11px] text-gray-400 leading-relaxed">
                        جهت هرگونه درخواست تغییر مشخصات سرویس یا پیگیری با تیم پشتیبانی در ارتباط باشید.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
