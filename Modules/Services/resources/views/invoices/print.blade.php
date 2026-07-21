@php
    use Morilog\Jalali\Jalalian;

    if (!isset($settings)) {
        $settings = \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->all();
    }

    $isProforma = !$invoice->invoice_number;
    $currencyLabel = $currency ?? $settings['currency'] ?? 'toman';
    $currencyLabel = $currencyLabel === 'rial' ? 'ریال' : ($currencyLabel === 'toman' ? 'تومان' : $currencyLabel);

    $faNum = function ($str) {
        if (is_null($str) || $str === '') return '';
        return str_replace(range(0, 9), ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], (string) $str);
    };

    $toJalali = function ($date) use ($faNum) {
        if (!$date) return '---';
        try {
            if ($date instanceof \Carbon\Carbon) {
                if ($date->year < 1900) {
                    return $faNum(sprintf('%04d/%02d/%02d', $date->year, $date->month, $date->day));
                }
                return $faNum(Jalalian::fromCarbon($date)->format('Y/m/d'));
            }
            $str = explode(' ', (string) $date)[0];
            return $faNum(str_replace('-', '/', $str));
        } catch (\Exception $e) {
            $str = explode(' ', (string) $date)[0];
            return $faNum(str_replace('-', '/', $str));
        }
    };

    $getPaymentMethodName = function ($method) use ($settings) {
        if (!$method) return 'نامشخص';
        $methodStr = strtolower(trim((string) $method));
        $posDevices = json_decode($settings['pos_devices'] ?? '[]', true);
        $bankAccounts = json_decode($settings['bank_transfer_accounts'] ?? '[]', true);

        if (preg_match('/^pos[-_](\d+)$/', $methodStr, $m)) {
            $id = $m[1];
            foreach ($posDevices as $device) {
                if (isset($device['id']) && (string)$device['id'] === $id) return 'کارتخوان ' . ($device['name'] ?? '');
            }
            return 'کارتخوان';
        }

        if (preg_match('/^transfer[-_](\d+)$/', $methodStr, $m)) {
            $id = $m[1];
            foreach ($bankAccounts as $account) {
                if (isset($account['id']) && (string)$account['id'] === $id) return 'انتقال به حساب ' . ($account['account_number'] ?? '');
            }
            return 'انتقال بانکی';
        }

        $map = [
            'online' => 'آنلاین (درگاه)', 'zarinpal' => 'درگاه زرین‌پال', 'zibal' => 'درگاه زیبال',
            'behpardakht' => 'درگاه به‌پرداخت', 'installment' => 'اقساطی', 'cash' => 'نقد',
            'pos' => 'کارتخوان', 'transfer' => 'انتقال بانکی', 'cod' => 'پرداخت در محل',
            'cheque' => 'چک', 'check' => 'چک', 'wallet' => 'کیف پول', 'credit' => 'اعتبار',
        ];

        if (isset($map[$methodStr])) return $map[$methodStr];
        foreach ($map as $key => $value) {
            if (str_contains($methodStr, $key)) return $value;
        }
        return $methodStr;
    };

    $statusColor = $invoice->status?->color ?? '#6b7280';
    $statusName  = $invoice->status?->name  ?? '—';

    $isCanceled = str_contains($invoice->status?->name ?? '', 'لغو');

    $total = (float) ($invoice->total ?? 0);
    $paid  = (float) ($invoice->paid_amount ?? 0);
    $due   = max(0, $total - $paid);
    $isFullyPaid = $due <= 0.01 && $total > 0 && !$isCanceled;

    $pickSetting = function (array $keys) use ($settings) {
        foreach ($keys as $key) {
            if (!empty($settings[$key])) return $settings[$key];
        }
        return null;
    };

    if (!isset($sellerInfo)) {
        $customFieldsRaw = $pickSetting(['identity_custom_fields', 'seller_custom_fields']);
        $sellerCustomFields = [];
        if ($customFieldsRaw) {
            $decodedSellerFields = json_decode($customFieldsRaw, true);
            if (is_array($decodedSellerFields)) {
                $sellerCustomFields = array_values(array_filter($decodedSellerFields, fn ($field) => !empty($field['value'] ?? null)));
            }
        }
        $sellerInfo = [
            'name' => $pickSetting(['identity_name', 'seller_name', 'company_name']) ?? '',
            'economic_number' => $pickSetting(['identity_economic_number', 'seller_economic_number', 'economic_number']) ?? '',
            'national_id' => $pickSetting(['identity_national_id', 'seller_national_id', 'national_id']) ?? '',
            'registration_number' => $pickSetting(['identity_registration_number', 'seller_registration_number', 'registration_number']) ?? '',
            'phone_fax' => $pickSetting(['identity_phone_fax', 'seller_phone_fax', 'phone_fax']) ?? '',
            'address' => $pickSetting(['identity_address', 'seller_address', 'address']) ?? '',
            'stamp_signature_image' => $pickSetting(['identity_seal_signature', 'seller_stamp_signature', 'stamp_signature_image']),
            'custom_fields' => $sellerCustomFields,
        ];
    }

    if (!isset($siteName)) {
        $siteName = $pickSetting(['identity_site_name', 'site_name', 'app_name', 'identity_name']) ?: ($sellerInfo['name'] ?: 'فاکتور');
    }
    if (!isset($appLogo)) {
        $appLogo = $pickSetting(['identity_logo', 'site_logo', 'app_logo', 'company_logo']);
    }

    // --- پردازش فیلدهای انتخابی خریدار ---
    $defaultBuyerFieldIds = ['full_name', 'phone', 'email', 'national_code', 'case_number'];
    $savedBuyerFieldIds = array_key_exists('services_invoice_client_fields', $settings)
        ? (json_decode($settings['services_invoice_client_fields'] ?? '[]', true) ?: [])
        : $defaultBuyerFieldIds;

    $buyerDisplayFields = $invoice->customer
        ? $invoice->customer->getFormFieldValues($savedBuyerFieldIds)
        : [];

    $buyerNameField = collect($buyerDisplayFields)->firstWhere('id', 'full_name');
    $buyerOtherFields = collect($buyerDisplayFields)->reject(fn($f) => $f['id'] === 'full_name');

    $ltrFields = ['phone', 'email', 'national_code', 'case_number'];

    $toDataUri = function (?string $relativePath) {
        if (!$relativePath) return null;
        $absolutePath = public_path(ltrim($relativePath, '/'));
        if (!is_file($absolutePath)) return null;
        $data = @file_get_contents($absolutePath);
        if ($data === false) return null;
        $mime = @mime_content_type($absolutePath) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode($data);
    };

    $appLogoDataUri = $toDataUri($appLogo);
    $stampSignatureDataUri = $toDataUri($sellerInfo['stamp_signature_image'] ?? null);

    $inlineAppCss = '';
    foreach ([public_path('build/manifest.json'), public_path('build/.vite/manifest.json')] as $manifestPath) {
        if (!is_file($manifestPath)) continue;
        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (!is_array($manifest)) continue;
        foreach ($manifest as $entryKey => $entry) {
            if (str_contains($entryKey, 'app.css') && isset($entry['file'])) {
                $cssFile = public_path('build/' . $entry['file']);
                if (is_file($cssFile)) {
                    $inlineAppCss = file_get_contents($cssFile);
                }
                break 2;
            }
        }
    }
@endphp

    <!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isProforma ? 'پیش فاکتور' : 'صورتحساب' }} {{ $faNum($invoice->invoice_number ?: $invoice->proforma_invoice_number) }}</title>
    <style>{!! $inlineAppCss !!}</style>
    <style>
        @font-face {
            font-family: 'IRANYekanX';
            src: url('data:font/ttf;base64,{{ base64_encode(file_get_contents(resource_path('fonts/iranYekanX/IRANYekanMediumFaNum.ttf'))) }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'IRANYekanX';
            src: url('data:font/ttf;base64,{{ base64_encode(file_get_contents(resource_path('fonts/iranYekanX/IRANYekanMediumFaNum.ttf'))) }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        body {
            font-family: 'IRANYekanX', Tahoma, Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .print-container {
            margin: 20px auto;
            background: white;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .print-fab {
            display: none;
        }

        @media screen {
            .print-fab {
                display: flex;
                position: fixed;
                bottom: 1.5rem;
                right: 1.5rem;
                background-color: #059669;
                color: white;
                padding: 0.75rem 1.25rem;
                border-radius: 9999px;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, .1);
                align-items: center;
                gap: 0.5rem;
                font-weight: 700;
                cursor: pointer;
                transition: transform 0.2s;
                z-index: 50;
            }

            .print-fab:hover {
                transform: scale(1.05);
            }
        }

        .page-standard {
            width: 210mm;
            min-height: 297mm;
            padding: 16mm 15mm;
            font-size: 11px;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 18px;
            margin-bottom: 22px;
            border-bottom: 1px solid #e2e8f0;
        }

        .invoice-header .brand-logo img {
            max-height: 52px;
            max-width: 190px;
            object-contain: contain;
            object-fit: contain;
        }

        .invoice-header .invoice-meta {
            text-align: right;
        }

        .invoice-header .invoice-meta h1 {
            font-size: 17px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 6px;
        }

        .invoice-header .status-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
        }

        .parties-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            margin-bottom: 26px;
        }

        .party-block h2 {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            padding-bottom: 8px;
            margin-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .party-block p {
            font-size: 11px;
            color: #475569;
            line-height: 1.9;
            margin: 0;
        }

        .party-block p.party-name {
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .section-title {
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
            border-bottom: 2px solid #059669;
            display: inline-block;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .items-table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 10.5px;
            font-weight: 700;
            padding: 10px 14px;
            border-bottom: 1px solid #e2e8f0;
            text-align: right;
        }

        .items-table thead th.col-total {
            text-align: left;
            width: 160px;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .items-table tbody td, .items-table tfoot td {
            padding: 11px 14px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
            vertical-align: top;
        }

        .items-table tbody tr:last-child td {
            border-bottom: none;
        }

        .items-table td.col-total {
            text-align: left;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
        }

        .items-table .item-title {
            font-weight: 700;
            color: #0f172a;
        }

        .items-table .item-note {
            font-size: 9.5px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .items-table .item-discount-note {
            font-size: 9.5px;
            color: #dc2626;
            margin-top: 2px;
        }

        .items-table tr.subfield-row td {
            background: #fafafa;
            color: #475569;
            font-size: 10px;
        }

        .items-table tr.subfield-row td.col-total {
            color: #334155;
            font-weight: 700;
        }

        .items-table tr.summary-row td {
            font-size: 11.5px;
        }

        .pill-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 11px;
        }

        .pill-badge.pill-total {
            background: #d1fae5;
            color: #065f46;
        }

        .pill-badge.pill-due {
            background: #fee2e2;
            color: #991b1b;
        }

        .pill-badge.pill-settled {
            background: #d1fae5;
            color: #065f46;
        }

        .avoid-break {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .payments-table {
            width: 100%;
            border-collapse: collapse;
        }

        .payments-table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 10px;
            font-weight: 700;
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
        }

        .payments-table tbody td {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
        }

        .invoice-footer {
            display: flex;
            justify-content: flex-end;
            align-items: flex-start;
            gap: 32px;
        }

        .signature-block {
            text-align: center;
            width: 190px;
            flex-shrink: 0;
        }

        .signature-block p.label {
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 8px;
        }

        .signature-box {
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signature-block .seller-name {
            border-top: 1px dashed #cbd5e1;
            font-size: 10.5px;
            padding-top: 8px;
            margin-top: 4px;
            color: #0f172a;
            font-weight: 700;
        }

        @media print {
            body {
                background: white;
                margin: 0;
            }

            .print-container {
                margin: 0;
                box-shadow: none;
                width: 100%;
                min-height: 100vh;
                page-break-after: always;
            }

            .print-fab {
                display: none;
            }

            @page {
                size: A4;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<button onclick="window.print()" class="print-fab">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
    </svg>
    <span>چاپ / ذخیره PDF</span>
</button>

<div class="print-container page-standard flex flex-col relative">
    @if($isFullyPaid && !$isCanceled)
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0 opacity-10">
            <span class="text-8xl font-bold text-green-600 transform -rotate-45">پرداخت شده</span>
        </div>
    @endif

    <div class="relative z-10 flex flex-col flex-1">

        <header class="invoice-header">
            <div class="brand-logo">
                @if($appLogoDataUri)
                    <img src="{{ $appLogoDataUri }}" alt="{{ $siteName }}">
                @endif
            </div>
            <div class="invoice-meta">
                <h1>{{ $isProforma ? 'پیش فاکتور' : 'صورتحساب' }}
                    #{{ $faNum($invoice->invoice_number ?: $invoice->proforma_invoice_number) }}</h1>
                <div class="flex items-center justify-end gap-2 mt-1">
                    @if($isCanceled)
                        <span class="inline-block border border-red-500 text-red-600 bg-red-50 font-bold px-2 py-0.5 rounded text-xs shadow-sm">لغو شده</span>
                    @else
                        <span class="status-badge" style="color: {{ $statusColor }}">{{ $statusName }}</span>
                    @endif
                </div>
            </div>
        </header>

        <div class="parties-grid">
            <div class="party-block">
                <h2>اطلاعات فروشنده</h2>
                <p class="party-name">{{ $sellerInfo['name'] ?: '---' }}</p>
                @if($sellerInfo['address'])
                    <p>آدرس: {{ $sellerInfo['address'] }}</p>
                @endif
                @if($sellerInfo['phone_fax'])
                    <p>شماره تماس: <span dir="ltr">{{ $faNum($sellerInfo['phone_fax']) }}</span></p>
                @endif
            </div>
            <div class="party-block">
                <h2>اطلاعات خریدار</h2>
                @if($buyerNameField)
                    <p class="party-name">{{ $buyerNameField['value'] }}</p>
                @endif

                @foreach($buyerOtherFields as $field)
                    <p>
                        {{ $field['label'] }}:
                        <span @if(in_array($field['id'], $ltrFields)) dir="ltr" @endif>
                            {{ $faNum($field['value']) }}
                        </span>
                    </p>
                @endforeach

                @if($invoice->client_address)
                    <p>آدرس: {{ $invoice->client_address }}</p>
                @endif
            </div>
        </div>

        <h3 class="section-title">اقلام صورت حساب</h3>
        <table class="items-table mb-8">
            <thead>
            <tr>
                <th>توضیحات</th>
                <th class="col-total">مجموع</th>
            </tr>
            </thead>
            <tbody>
            @foreach($invoice->items as $item)
                @php
                    $savedCustomFields = $item->meta['custom_fields'] ?? [];
                    $displayQty = fmod($item->quantity, 1.0) === 0.0 ? (int) $item->quantity : $item->quantity;
                    $rowBasePrice = $item->unit_price;
                    $rowGross = $rowBasePrice * $item->quantity;
                    $rowDiscount = $item->discount;
                    $rowTotal = $item->total;
                    if (($taxMode ?? 'invoice') === 'item') {
                        $rowTotal += $item->tax_amount;
                    }
                @endphp
                <tr class="avoid-break">
                    <td>
                        <p class="item-title">{{ $item->custom_service_name ?: ($item->service->name ?? 'ردیف دستی') }}</p>
                        @if($item->description && $item->description !== ($item->custom_service_name ?: ($item->service->name ?? '')))
                            <p class="item-note">{{ $item->description }}</p>
                        @endif
                        @if(!empty($savedCustomFields))
                            @php
                                $customFieldsCollection = $item->service ? $item->service->customFields : collect([]);
                                $printedFields = [];
                                foreach($savedCustomFields as $field_id => $value) {
                                    $fieldDef = $customFieldsCollection->firstWhere('id', $field_id);
                                    if (!$fieldDef) continue;
                                    if (is_array($value)) { $displayValue = implode('، ', $value); }
                                    elseif ($fieldDef->type === 'checkbox') { $displayValue = $value ? 'انتخاب شده' : null; }
                                    else { $displayValue = $value ?: null; }
                                    if ($displayValue) {
                                        $printedFields[] = $fieldDef->label . ': ' . $displayValue;
                                    }
                                }
                            @endphp
                            @if(count($printedFields) > 0)
                                <p class="item-note">فیلدهای سفارشی: {{ implode(' | ', $printedFields) }}</p>
                            @endif
                        @endif
                        @if($displayQty != 1)
                            <p class="item-note">{{ $faNum($displayQty) }} {{ $item->unit ?? 'عدد' }}
                                × {{ $faNum(number_format($rowBasePrice)) }} {{ $currencyLabel }}</p>
                        @endif
                        @if($rowDiscount > 0)
                            <p class="item-discount-note">
                                شامل {{ $faNum(number_format($rowDiscount)) }} {{ $currencyLabel }} تخفیف</p>
                        @endif
                        @if($item->tax_amount > 0)
                            <p class="item-note">مالیات ردیف ({{ $faNum((float) $item->tax_percent) }}٪):
                                {{ $faNum(number_format($item->tax_amount)) }} {{ $currencyLabel }}</p>
                        @endif
                    </td>
                    <td class="col-total">{{ $faNum(number_format($rowTotal)) }} {{ $currencyLabel }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <td class="text-right">جمع مبالغ پایه</td>
                <td class="col-total">{{ $faNum(number_format($invoice->subtotal)) }} {{ $currencyLabel }}</td>
            </tr>
            @if($invoice->discount_amount > 0)
                <tr>
                    <td class="text-right" style="color:#dc2626;">مجموع تخفیف‌ها</td>
                    <td class="col-total" style="color:#dc2626;">
                        − {{ $faNum(number_format($invoice->discount_amount)) }} {{ $currencyLabel }}</td>
                </tr>
            @endif
            @if($invoice->tax_amount > 0)
                <tr>
                    <td class="text-right">مالیات
                        @if((float) $invoice->tax_percent > 0)
                            ({{ $faNum((float) $invoice->tax_percent) }}٪)
                        @endif
                    </td>
                    <td class="col-total">+ {{ $faNum(number_format($invoice->tax_amount)) }} {{ $currencyLabel }}</td>
                </tr>
            @endif
            @if($paid > 0)
                <tr>
                    <td class="text-right" style="color:#059669;">پرداخت شده</td>
                    <td class="col-total"
                        style="color:#059669;">{{ $faNum(number_format($paid)) }} {{ $currencyLabel }}</td>
                </tr>
            @endif
            <tr class="summary-row">
                <td><span class="pill-badge pill-total">جمع فاکتور</span></td>
                <td class="col-total">{{ $faNum(number_format($total)) }} {{ $currencyLabel }}</td>
            </tr>
            <tr class="summary-row">
                <td>
                    @if($due > 0)
                        <span class="pill-badge pill-due">مانده</span>
                    @else
                        <span class="pill-badge pill-settled">تسویه شده</span>
                    @endif
                </td>
                <td class="col-total">{{ $faNum(number_format($due)) }} {{ $currencyLabel }}</td>
            </tr>
            </tfoot>
        </table>

        @if($invoice->payments->isNotEmpty())
            <h3 class="section-title mt-8">تاریخچه پرداخت</h3>
            <table class="payments-table mb-8 avoid-break">
                <thead>
                <tr>
                    <th class="text-right">تاریخ</th>
                    <th class="text-center">مبلغ</th>
                    <th class="text-center">روش پرداخت</th>
                    <th class="text-center">کد رهگیری</th>
                </tr>
                </thead>
                <tbody>
                @foreach($invoice->payments as $payment)
                    <tr>
                        <td class="text-right">{{ $toJalali($payment->paid_at) }}</td>
                        <td class="text-center font-bold"
                            style="color:#059669;">{{ $faNum(number_format($payment->amount)) }} {{ $currencyLabel }}</td>
                        <td class="text-center">{{ $getPaymentMethodName($payment->method) }}</td>
                        <td class="text-center" dir="ltr">{{ $faNum($payment->transaction_id) ?: '---' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        @if(!empty($settings['services_invoice_footer_note']))
            <div class="mt-4 text-xs text-gray-600 avoid-break border-t border-gray-200 pt-4">
                <strong class="text-gray-800">یادداشت:</strong><br>
                <div class="mt-1 leading-relaxed">{!! nl2br(e($settings['services_invoice_footer_note'])) !!}</div>
            </div>
        @endif

        <div class="invoice-footer mt-auto pt-10 avoid-break">
            <div class="signature-block">
                <p class="label">مهر و امضا:</p>
                <div class="signature-box">
                    @if($stampSignatureDataUri)
                        <img src="{{ $stampSignatureDataUri }}" alt="مهر و امضا"
                             style="max-height: 5rem; max-width: 100%; object-fit: contain; mix-blend-mode: multiply;">
                    @endif
                </div>
                <p class="seller-name">{{ $sellerInfo['name'] }}</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
