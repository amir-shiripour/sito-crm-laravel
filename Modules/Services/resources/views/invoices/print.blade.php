@php
    use Illuminate\Support\Facades\DB as DBAlias;use Modules\Clients\Entities\ClientForm;use Morilog\Jalali\Jalalian;

    if (!isset($settings)) {
        $settings = DBAlias::table('settings')->pluck('value', 'key')->all();
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
        } catch (Exception $e) {
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
        $siteName = $pickSetting(['identity_site_name', 'site_name', 'app_name', 'identity_name']) ?: (($sellerInfo['name'] ?? '') ?: 'فاکتور');
    }
    if (!isset($appLogo)) {
        $appLogo = $pickSetting(['identity_logo', 'site_logo', 'app_logo', 'company_logo']);
    }

    $stampStandardWidth = !empty($settings['services_stamp_standard_width']) ? (int) $settings['services_stamp_standard_width'] : null;
    $stampStandardHeight = !empty($settings['services_stamp_standard_height']) ? (int) $settings['services_stamp_standard_height'] : null;

    $stampStandardImgStyle = 'max-width: 100%; object-fit: contain; mix-blend-mode: multiply;';
    if ($stampStandardWidth && $stampStandardHeight) {
        $stampStandardImgStyle .= " width: {$stampStandardWidth}px; height: {$stampStandardHeight}px;";
    } elseif ($stampStandardWidth) {
        $stampStandardImgStyle .= " width: {$stampStandardWidth}px; height: auto;";
    } elseif ($stampStandardHeight) {
        $stampStandardImgStyle .= " height: {$stampStandardHeight}px; width: auto;";
    } else {
        $stampStandardImgStyle .= " max-height: 3rem;";
    }

    $defaultBuyerFieldIds = ['full_name', 'phone', 'email', 'national_code', 'case_number'];
    $savedBuyerFieldIds = array_key_exists('services_invoice_client_fields', $settings)
        ? (json_decode($settings['services_invoice_client_fields'] ?? '[]', true) ?: [])
        : $defaultBuyerFieldIds;
    $clientSelectedFields = is_array($invoice->meta) ? ($invoice->meta['client_selected_fields'] ?? []) : [];

    $initialBuyerFields = $invoice->customer
        ? $invoice->customer->getFormFieldValues($savedBuyerFieldIds)
        : [];

    $fieldsById = [];
    foreach ($initialBuyerFields as $f) {
        $fieldsById[$f['id']] = $f;
    }

    $systemLabels = [
        'full_name' => 'نام و نام خانوادگی',
        'phone' => 'شماره تماس',
        'email' => 'پست الکترونیک',
        'national_code' => 'کد / شناسه ملی',
        'case_number' => 'شماره پرونده',
        'address' => 'نشانی',
    ];
    $activeClientForm = class_exists(ClientForm::class)
        ? ClientForm::active()
        : null;

    foreach ($savedBuyerFieldIds as $fid) {
        $selectedVal = $clientSelectedFields[$fid] ?? null;
        if (!empty($selectedVal)) {
            $formattedVal = is_array($selectedVal)
                ? implode(' ، ', array_filter(array_map('trim', $selectedVal)))
                : trim((string)$selectedVal);

            if ($formattedVal !== '') {
                if (isset($fieldsById[$fid])) {
                    $fieldsById[$fid]['value'] = $formattedVal;
                } else {
                    $label = $systemLabels[$fid] ?? null;
                    if (!$label && $activeClientForm) {
                        $fieldDef = $activeClientForm->field($fid);
                        $label = $fieldDef['label'] ?? null;
                    }
                    $fieldsById[$fid] = [
                        'id' => $fid,
                        'label' => $label ?: $fid,
                        'value' => $formattedVal,
                    ];
                }
            }
        }
    }

    $buyerDisplayFields = [];
    foreach ($savedBuyerFieldIds as $fid) {
        if (isset($fieldsById[$fid]) && !empty($fieldsById[$fid]['value'])) {
            $buyerDisplayFields[] = $fieldsById[$fid];
        }
    }

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
            max-width: 100%;
            padding: 10mm 12mm;
            font-size: 10.5px;
            box-sizing: border-box;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 10px;
            margin-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .invoice-header .brand-logo img {
            max-height: 52px;
            max-width: 190px;
            object-contain: contain;
            object-fit: contain;
        }

        .invoice-header .invoice-meta-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
            text-align: right;
            min-width: 220px;
        }

        .invoice-meta-header {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            margin-bottom: 6px;
        }

        .invoice-meta-header h1.invoice-title {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
        }

        .status-tag {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10.5px;
            font-weight: 700;
            border: 1px solid;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .merged-tag {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10.5px;
            font-weight: 700;
            background-color: #f3e8ff;
            color: #7e22ce;
            border: 1px solid #d8b4fe;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .invoice-meta-dates {
            font-size: 10px;
            color: #64748b;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            border-top: 1px dashed #cbd5e1;
            padding-top: 5px;
            margin-top: 4px;
        }

        .parties-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 14px;
        }

        .party-block h2 {
            font-size: 11.5px;
            font-weight: 700;
            color: #0f172a;
            padding-bottom: 4px;
            margin-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
        }

        .party-block p {
            font-size: 10.5px;
            color: #475569;
            line-height: 1.6;
            margin: 0;
        }

        .party-block p.party-name {
            font-size: 11.5px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .section-title {
            font-size: 11.5px;
            font-weight: 700;
            color: #0f172a;
            padding-bottom: 4px;
            margin-bottom: 8px;
            border-bottom: 2px solid #059669;
            display: inline-block;
        }

        .items-section-title {
            font-size: 11px;
            font-weight: 700;
            color: #374151;
            margin-top: 0;
            margin-bottom: 8px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 24px;
        }

        .items-table thead tr {
            background-color: #f8fafc;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .items-table th {
            padding: 8px 12px;
            font-size: 10.5px;
            font-weight: 700;
            color: #374151;
            border: none;
        }

        .items-table th.col-row {
            text-align: center;
            width: 40px;
        }

        .items-table th.col-desc {
            text-align: right;
        }

        .items-table th.col-qty {
            text-align: center;
            width: 60px;
        }

        .items-table th.col-discount {
            text-align: center;
            width: 85px;
        }

        .items-table th.col-amount {
            text-align: left;
            width: 140px;
        }

        .items-table td {
            padding: 8px 12px;
            font-size: 10.5px;
            vertical-align: middle;
            border: none;
        }

        .items-table td.col-row {
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }

        .items-table td.col-desc {
            text-align: right;
            color: #1f2937;
        }

        .items-table td.col-qty {
            text-align: center;
            color: #1f2937;
        }

        .items-table td.col-discount {
            text-align: center;
            color: #1f2937;
        }

        .items-table td.col-amount {
            text-align: left;
            font-weight: 700;
            color: #1f2937;
            white-space: nowrap;
        }

        .items-table td.col-summary-label {
            text-align: left;
            padding-left: 24px;
            font-weight: 700;
            color: #374151;
        }

        .items-table tr.row-white {
            background-color: #ffffff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .items-table tr.row-alt {
            background-color: #f8fafc !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .items-table .item-title {
            font-weight: 400;
            color: #111827;
        }

        .items-table .item-desc {
            color: #4b5563;
        }

        .avoid-break {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .no-page-break-before {
            page-break-before: avoid !important;
            break-before: avoid !important;
        }

        .payments-table {
            width: 100%;
            border-collapse: collapse;
        }

        .payments-table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 9px;
            font-weight: 700;
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
        }

        .payments-table tbody td {
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
            font-size: 9px;
        }

        .invoice-footer {
            display: flex;
            justify-content: flex-end;
            align-items: flex-start;
            gap: 20px;
            page-break-before: avoid !important;
            break-before: avoid !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .signature-block {
            text-align: center;
            width: 170px;
            flex-shrink: 0;
            page-break-before: avoid !important;
            break-before: avoid !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .signature-block p.label {
            font-size: 10px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 2px;
        }

        .signature-box {
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signature-block .seller-name {
            border-top: 1px dashed #cbd5e1;
            font-size: 9.5px;
            padding-top: 2px;
            margin-top: 2px;
            color: #0f172a;
            font-weight: 700;
        }

        @media print {
            body {
                background: white;
                margin: 0;
            }

            .print-container {
                margin: 0 !important;
                box-shadow: none !important;
                width: 100% !important;
                min-height: auto !important;
                padding: 4mm 6mm !important;
            }

            .print-fab {
                display: none !important;
            }

            @page {
                size: A4 portrait;
                margin: 3mm;
            }

            tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .invoice-footer, .signature-block {
                page-break-before: avoid !important;
                break-before: avoid !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .items-table td, .items-table th {
                padding: 4px 6px !important;
            }

            .payments-table td, .payments-table th {
                padding: 3px 5px !important;
                font-size: 8.5px !important;
            }

            .section-title {
                margin-top: 6px !important;
                margin-bottom: 4px !important;
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

    <div class="relative z-10 flex flex-col flex-1">

        <header class="invoice-header">
            <div class="brand-logo">
                @if($appLogoDataUri)
                    <img src="{{ $appLogoDataUri }}" alt="{{ $siteName }}">
                @endif
            </div>
            <div class="invoice-meta-card">
                <div class="invoice-meta-header">
                    <h1 class="invoice-title">{{ $isProforma ? 'پیش فاکتور' : 'صورتحساب' }}
                        #{{ $faNum($invoice->invoice_number ?: $invoice->proforma_invoice_number) }}</h1>
                    <span class="status-tag"
                          style="background-color: {{ $statusColor }}15; color: {{ $statusColor }}; border-color: {{ $statusColor }}44;">
                        {{ $statusName }}
                    </span>
                </div>
                <div class="invoice-meta-dates">
                    @if($invoice->issue_date)
                        <span>تاریخ صدور: {{ $toJalali($invoice->issue_date) }}</span>
                    @endif
                    @if($invoice->due_date)
                        <span>تاریخ سررسید: {{ $toJalali($invoice->due_date) }}</span>
                    @endif
                    @php
                        $invoicePackages = collect($invoice->items)->map(function($it) {
                            return $it->meta['_packageTitle'] ?? null;
                        })->filter()->unique()->values();
                        if ($invoicePackages->isEmpty() && !empty($invoice->meta['packages']) && is_array($invoice->meta['packages'])) {
                            $invoicePackages = collect($invoice->meta['packages'])->filter()->unique()->values();
                        }
                    @endphp
                </div>
            </div>
        </header>

        <div class="parties-grid">
            <div class="party-block">
                <h2>اطلاعات فروشنده</h2>
                <p class="party-name">{{ ($sellerInfo['name'] ?? null) ?: '---' }}</p>
                @if(!empty($sellerInfo['address']))
                    <p>آدرس: {{ $sellerInfo['address'] }}</p>
                @endif
                @if(!empty($sellerInfo['phone_fax']))
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

        <h3 class="items-section-title">اقلام صورت حساب</h3>
        <table class="items-table mb-8">
            <thead>
            <tr>
                <th class="col-row">ردیف</th>
                <th class="col-desc">توضیحات</th>
                <th class="col-qty">تعداد</th>
                <th class="col-discount">تخفیف</th>
                <th class="col-amount">مجموع</th>
            </tr>
            </thead>
            <tbody>
            @php $zebraIdx = 0; @endphp
            @foreach($invoice->items as $index => $item)
                @php
                    $itemQty = (float) $item->quantity;
                    $displayQty = fmod($itemQty, 1.0) === 0.0 ? (int) $itemQty : $itemQty;
                    $rowBasePrice = $item->unit_price;
                    $rowGross = $rowBasePrice * $item->quantity;
                    $rowDiscount = $item->discount;
                    $rowTotal = $item->total;
                    if (($taxMode ?? 'invoice') === 'item') {
                        $rowTotal += $item->tax_amount;
                    }
                    $itemMeta = is_array($item->meta) ? $item->meta : (json_decode($item->meta, true) ?: []);
                    $currentPkgId = $itemMeta['_packageGroupId'] ?? (!empty($itemMeta['_packageTitle']) ? $itemMeta['_packageTitle'] : null);
                    $prevItem = $loop->first ? null : $invoice->items[$loop->index - 1];
                    $prevMeta = $prevItem ? (is_array($prevItem->meta) ? $prevItem->meta : (json_decode($prevItem->meta, true) ?: [])) : [];
                    $prevPkgId = $prevMeta['_packageGroupId'] ?? (!empty($prevMeta['_packageTitle']) ? $prevMeta['_packageTitle'] : null);
                    $isFirstInPackage = !empty($currentPkgId) && ($currentPkgId !== $prevPkgId);
                    $isInPackage = !empty($currentPkgId);

                    $savedCustomFields = $itemMeta['custom_fields'] ?? [];
                    $customFieldsCollection = $item->service ? $item->service->customFields : collect([]);
                    $customFieldsQuantities = $itemMeta['custom_fields_quantities'] ?? [];
                    $customFieldsPrices = $itemMeta['custom_fields_prices'] ?? [];
                    $customFieldsDiscounts = $itemMeta['custom_fields_discounts'] ?? [];
                    $customFieldsTaxes = $itemMeta['custom_fields_taxes'] ?? [];
                    $taxApplyCustomFields = !empty($settings['services_tax_apply_custom_fields']);

                    $itemSubRows = [];
                    if (!empty($savedCustomFields) && is_array($savedCustomFields)) {
                        foreach ($savedCustomFields as $field_id => $value) {
                            $fieldDef = $customFieldsCollection->firstWhere('id', $field_id);
                            if (!$fieldDef) continue;

                            if ($fieldDef->type === 'multiselect' && is_array($value)) {
                                foreach ($value as $opt) {
                                    if ($opt === null || trim((string)$opt) === '') continue;

                                    $optQty = is_array($customFieldsQuantities[$field_id] ?? null)
                                        ? ($customFieldsQuantities[$field_id][$opt] ?? ($customFieldsQuantities[$field_id] ?? 1))
                                        : ($customFieldsQuantities[$field_id] ?? 1);
                                    $optQty = (float)$optQty;
                                    if ($optQty <= 0) $optQty = 1;

                                    $optPrice = null;
                                    if (isset($customFieldsPrices[$field_id]) && is_array($customFieldsPrices[$field_id]) && isset($customFieldsPrices[$field_id][$opt]) && $customFieldsPrices[$field_id][$opt] !== '') {
                                        $optPrice = (float)$customFieldsPrices[$field_id][$opt];
                                    } elseif (isset($customFieldsPrices[$field_id]) && !is_array($customFieldsPrices[$field_id]) && $customFieldsPrices[$field_id] !== '') {
                                        $optPrice = (float)$customFieldsPrices[$field_id];
                                    } elseif ($fieldDef->has_pricing) {
                                        $optPrice = (float)$fieldDef->getOptionPrice($opt, $item->unit_price);
                                    }
                                    $hasPricing = ($fieldDef->has_pricing || ($optPrice !== null && (float)$optPrice > 0));
                                    $optPrice = (float)($optPrice ?? 0);

                                    $optDiscount = 0;
                                    if (isset($customFieldsDiscounts[$field_id]) && is_array($customFieldsDiscounts[$field_id])) {
                                        $optDiscount = (float)($customFieldsDiscounts[$field_id][$opt] ?? 0);
                                    } elseif (isset($customFieldsDiscounts[$field_id])) {
                                        $optDiscount = (float)$customFieldsDiscounts[$field_id];
                                    }

                                    $cfTaxAmount = 0;
                                    $cfTaxPercent = 0;
                                    if (($taxMode ?? 'invoice') === 'item' && $taxApplyCustomFields) {
                                        if (isset($customFieldsTaxes[$field_id]) && is_array($customFieldsTaxes[$field_id])) {
                                            $cfTaxPercent = (float)($customFieldsTaxes[$field_id][$opt] ?? 0);
                                        } elseif (isset($customFieldsTaxes[$field_id])) {
                                            $cfTaxPercent = (float)$customFieldsTaxes[$field_id];
                                        }
                                        $cfTaxable = max(0, ($optPrice * $optQty) - $optDiscount);
                                        $cfTaxAmount = $cfTaxable * ($cfTaxPercent / 100);
                                    }

                                    $cfBase = max(0, ($optPrice * $optQty) - $optDiscount);
                                    $cfRowTotal = (($taxMode ?? 'invoice') === 'item') ? ($cfBase + $cfTaxAmount) : $cfBase;

                                    $subLabel = $fieldDef->label;
                                    if (!empty($opt) && $opt !== $fieldDef->label) {
                                        $subLabel .= ' (' . $opt . ')';
                                    }

                                    $itemSubRows[] = [
                                        'label' => $subLabel,
                                        'value' => (string)$opt,
                                        'quantity' => $optQty,
                                        'unit' => 'عدد',
                                        'unit_price' => $optPrice,
                                        'discount' => $optDiscount,
                                        'tax_percent' => $cfTaxPercent,
                                        'tax_amount' => $cfTaxAmount,
                                        'total' => $cfRowTotal,
                                        'has_pricing' => $hasPricing,
                                    ];
                                }
                            } else {
                                if (is_array($value)) {
                                    $filteredVal = array_filter($value, fn($v) => $v !== null && trim((string)$v) !== '');
                                    $displayValue = !empty($filteredVal) ? implode('، ', $filteredVal) : null;
                                } elseif ($fieldDef->type === 'checkbox') {
                                    $displayValue = in_array($value, [true, '1', 1], true) ? 'انتخاب شده' : null;
                                } elseif ($fieldDef->type === 'file') {
                                    $displayValue = $value ? 'فایل پیوست شده' : null;
                                } else {
                                    $displayValue = ($value !== null && trim((string)$value) !== '') ? (string)$value : null;
                                }

                                if (!$displayValue) continue;

                                $fieldQty = 1;
                                if (isset($customFieldsQuantities[$field_id]) && !is_array($customFieldsQuantities[$field_id])) {
                                    $fieldQty = (float)$customFieldsQuantities[$field_id];
                                } elseif ($fieldDef->type === 'number' && is_numeric($displayValue)) {
                                    $fieldQty = (float)$displayValue;
                                }
                                if ($fieldQty <= 0) $fieldQty = 1;

                                $fieldPrice = null;
                                if (isset($customFieldsPrices[$field_id]) && !is_array($customFieldsPrices[$field_id]) && $customFieldsPrices[$field_id] !== '') {
                                    $fieldPrice = (float)$customFieldsPrices[$field_id];
                                } elseif ($fieldDef->has_pricing) {
                                    if (in_array($fieldDef->type, ['select', 'radio'])) {
                                        $fieldPrice = (float)$fieldDef->getOptionPrice($displayValue, $item->unit_price);
                                    } else {
                                        $fieldPrice = $fieldDef->pricing_type === 'percentage'
                                            ? ((float)$item->unit_price * ((float)$fieldDef->pricing_amount / 100))
                                            : (float)$fieldDef->pricing_amount;
                                    }
                                }
                                $hasPricing = ($fieldDef->has_pricing || ($fieldPrice !== null && (float)$fieldPrice > 0));
                                $fieldPrice = (float)($fieldPrice ?? 0);

                                $fieldDiscount = 0;
                                if (isset($customFieldsDiscounts[$field_id]) && !is_array($customFieldsDiscounts[$field_id])) {
                                    $fieldDiscount = (float)$customFieldsDiscounts[$field_id];
                                }

                                $cfTaxAmount = 0;
                                $cfTaxPercent = 0;
                                if (($taxMode ?? 'invoice') === 'item' && $taxApplyCustomFields) {
                                    if (isset($customFieldsTaxes[$field_id]) && !is_array($customFieldsTaxes[$field_id])) {
                                        $cfTaxPercent = (float)$customFieldsTaxes[$field_id];
                                    }
                                    $cfTaxable = max(0, ($fieldPrice * $fieldQty) - $fieldDiscount);
                                    $cfTaxAmount = $cfTaxable * ($cfTaxPercent / 100);
                                }

                                $cfBase = max(0, ($fieldPrice * $fieldQty) - $fieldDiscount);
                                $cfRowTotal = (($taxMode ?? 'invoice') === 'item') ? ($cfBase + $cfTaxAmount) : $cfBase;

                                $subLabel = $fieldDef->label;
                                if (in_array($fieldDef->type, ['select', 'radio']) && !empty($displayValue) && $displayValue !== $fieldDef->label) {
                                    $subLabel .= ' (' . $displayValue . ')';
                                }

                                $itemSubRows[] = [
                                    'label' => $subLabel,
                                    'value' => $displayValue,
                                    'quantity' => $fieldQty,
                                    'unit' => 'عدد',
                                    'unit_price' => $fieldPrice,
                                    'discount' => $fieldDiscount,
                                    'tax_percent' => $cfTaxPercent,
                                    'tax_amount' => $cfTaxAmount,
                                    'total' => $cfRowTotal,
                                    'has_pricing' => $hasPricing,
                                ];
                            }
                        }
                    }

                    $pricedSubRowsSum = 0;
                    $subRowsTaxSum = 0;
                    foreach ($itemSubRows as $r) {
                        if (!empty($r['has_pricing']) && $r['total'] > 0) {
                            $pricedSubRowsSum += $r['total'];
                        }
                        if (!empty($r['tax_amount']) && $r['tax_amount'] > 0) {
                            $subRowsTaxSum += $r['tax_amount'];
                        }
                    }

                    if ($pricedSubRowsSum > 0) {
                        $mainRowDisplayTotal = max(0, $rowTotal - $pricedSubRowsSum);
                    } else {
                        $mainRowDisplayTotal = $rowTotal;
                    }
                    $mainRowTax = max(0, $item->tax_amount - $subRowsTaxSum);
                @endphp
                @if($isFirstInPackage)
                    <tr class="avoid-break"
                        style="background-color: #fef3c7; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                        <td colspan="5"
                            style="padding: 6px 12px; font-weight: bold; font-size: 10.5px; color: #92400e;">
                            <span>{{ $itemMeta['_packageTitle'] ?? 'اقلام پکیج' }}</span>
                        </td>
                    </tr>
                @endif
                <tr class="avoid-break {{ ($zebraIdx++ % 2 === 1) ? 'row-alt' : 'row-white' }}">
                    <td class="col-row">{{ $faNum($index + 1) }}</td>
                    <td class="col-desc">
                        <span class="item-title">{{ $item->custom_service_name ?: ($item->service->name ?? 'ردیف دستی') }}</span>
                        @if($item->description && $item->description !== ($item->custom_service_name ?: ($item->service->name ?? '')))
                            <span class="item-desc"> - {{ $item->description }}</span>
                        @endif
                    </td>
                    <td class="col-qty">
                        {{ $faNum($displayQty) }}@if($item->unit && $item->unit !== 'عدد') <span style="font-size: 9px; color: #6b7280;">{{ $item->unit }}</span>@endif
                    </td>
                    <td class="col-discount">
                        @if($rowDiscount > 0)
                            <span style="color: #dc2626;">{{ $faNum(number_format($rowDiscount)) }}</span>
                        @else
                            ۰
                        @endif
                    </td>
                    <td class="col-amount">{{ $faNum(number_format($mainRowDisplayTotal)) }} {{ $currencyLabel }}</td>
                </tr>
                @foreach($itemSubRows as $subIdx => $subRow)
                    @php
                        $subQty = (float) $subRow['quantity'];
                        $subDisplayQty = fmod($subQty, 1.0) === 0.0 ? (int) $subQty : $subQty;
                    @endphp
                    <tr class="avoid-break {{ ($zebraIdx++ % 2 === 1) ? 'row-alt' : 'row-white' }}">
                        <td class="col-row" style="font-size: 9px; color: #9ca3af;">{{ $faNum($index + 1) }}-{{ $faNum($subIdx + 1) }}</td>
                        <td class="col-desc">
                            <span class="item-title">{{ $subRow['label'] }}</span>
                        </td>
                        <td class="col-qty">
                            @if($subRow['has_pricing'])
                                {{ $faNum($subDisplayQty) }}@if(($subRow['unit'] ?? '') && $subRow['unit'] !== 'عدد') <span style="font-size: 9px; color: #6b7280;">{{ $subRow['unit'] }}</span>@endif
                            @else
                                —
                            @endif
                        </td>
                        <td class="col-discount">
                            @if($subRow['discount'] > 0)
                                <span style="color: #dc2626;">{{ $faNum(number_format($subRow['discount'])) }}</span>
                            @elseif($subRow['has_pricing'])
                                ۰
                            @else
                                —
                            @endif
                        </td>
                        <td class="col-amount">
                            @if($subRow['has_pricing'] && $subRow['total'] > 0)
                                {{ $faNum(number_format($subRow['total'])) }} {{ $currencyLabel }}
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endforeach

            @php
                $hasTaxOrDiscount = ($invoice->tax_amount > 0) || ($invoice->discount_amount > 0);
            @endphp
            @if($hasTaxOrDiscount)
                <tr class="avoid-break {{ ($zebraIdx++ % 2 === 1) ? 'row-alt' : 'row-white' }}">
                    <td colspan="4" class="col-summary-label">جمع مبالغ پایه</td>
                    <td class="col-amount">{{ $faNum(number_format($invoice->subtotal)) }} {{ $currencyLabel }}</td>
                </tr>
                @if($invoice->tax_amount > 0)
                    <tr class="avoid-break {{ ($zebraIdx++ % 2 === 1) ? 'row-alt' : 'row-white' }}">
                        <td colspan="4" class="col-summary-label">مالیات @if((float) $invoice->tax_percent > 0)({{ $faNum((float) $invoice->tax_percent) }}٪)@endif</td>
                        <td class="col-amount">+ {{ $faNum(number_format($invoice->tax_amount)) }} {{ $currencyLabel }}</td>
                    </tr>
                @endif
                @if($invoice->discount_amount > 0)
                    <tr class="avoid-break {{ ($zebraIdx++ % 2 === 1) ? 'row-alt' : 'row-white' }}">
                        <td colspan="4" class="col-summary-label" style="color: #dc2626;">مجموع تخفیف‌ها</td>
                        <td class="col-amount" style="color: #dc2626;">− {{ $faNum(number_format($invoice->discount_amount)) }} {{ $currencyLabel }}</td>
                    </tr>
                @endif
            @endif

            @if($paid > 0)
                <tr class="avoid-break {{ ($zebraIdx++ % 2 === 1) ? 'row-alt' : 'row-white' }}">
                    <td colspan="4" class="col-summary-label" style="color: #059669;">پرداخت شده</td>
                    <td class="col-amount" style="color: #059669;">{{ $faNum(number_format($paid)) }} {{ $currencyLabel }}</td>
                </tr>
            @endif

            <tr class="avoid-break {{ ($zebraIdx++ % 2 === 1) ? 'row-alt' : 'row-white' }}">
                <td colspan="4" class="col-summary-label">جمع فاکتور</td>
                <td class="col-amount">{{ $faNum(number_format($total)) }} {{ $currencyLabel }}</td>
            </tr>

            <tr class="avoid-break {{ ($zebraIdx++ % 2 === 1) ? 'row-alt' : 'row-white' }}">
                <td colspan="4" class="col-summary-label">
                    @if($due > 0)
                        مانده
                    @else
                        تسویه شده
                    @endif
                </td>
                <td class="col-amount">{{ $faNum(number_format($due)) }} {{ $currencyLabel }}</td>
            </tr>
            </tbody>
        </table>

        @php
            $validPayments = $invoice->payments->reject(fn($p) => ($p->status ?? '') === 'canceled');
        @endphp

        @if($validPayments->isNotEmpty())
            <h3 class="section-title mt-3 mb-1">تاریخچه پرداخت</h3>
            <table class="payments-table mb-3">
                <thead>
                <tr>
                    <th class="text-right">تاریخ</th>
                    <th class="text-center">مبلغ</th>
                    <th class="text-center">روش پرداخت</th>
                    <th class="text-center">کد رهگیری</th>
                </tr>
                </thead>
                <tbody>
                @foreach($validPayments as $payment)
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

        <div class="invoice-footer mt-3 pt-2 avoid-break">
            <div class="signature-block"
                 style="{{ $stampStandardWidth ? 'min-width: ' . max(170, $stampStandardWidth + 10) . 'px; width: auto;' : '' }}">
                <p class="label">مهر و امضا:</p>
                <div class="signature-box"
                     style="{{ $stampStandardHeight ? 'min-height: ' . $stampStandardHeight . 'px; height: auto;' : '' }}">
                    @if($stampSignatureDataUri)
                        <img src="{{ $stampSignatureDataUri }}" alt="مهر و امضا"
                             style="{{ $stampStandardImgStyle }}">
                    @endif
                </div>
                <p class="seller-name">{{ $sellerInfo['name'] ?? '' }}</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
