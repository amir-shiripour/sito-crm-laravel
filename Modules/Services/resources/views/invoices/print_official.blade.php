@php
    use Carbon\Carbon;
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
            if ($date instanceof Carbon) {
                if ($date->year < 1900) return $faNum(sprintf('%04d/%02d/%02d', $date->year, $date->month, $date->day));
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
            foreach ($posDevices as $device) if (isset($device['id']) && (string)$device['id'] === $id) return 'کارتخوان ' . ($device['name'] ?? '');
            return 'کارتخوان';
        }
        if (preg_match('/^transfer[-_](\d+)$/', $methodStr, $m)) {
            $id = $m[1];
            foreach ($bankAccounts as $account) if (isset($account['id']) && (string)$account['id'] === $id) return 'انتقال به حساب ' . ($account['account_number'] ?? '');
            return 'انتقال بانکی';
        }

        $map = ['online' => 'آنلاین (درگاه)', 'zarinpal' => 'درگاه زرین‌پال', 'zibal' => 'درگاه زیبال', 'behpardakht' => 'درگاه به‌پرداخت', 'installment' => 'اقساطی', 'cash' => 'نقد', 'pos' => 'کارتخوان', 'transfer' => 'انتقال بانکی', 'cod' => 'پرداخت در محل', 'cheque' => 'چک', 'check' => 'چک', 'wallet' => 'کیف پول', 'credit' => 'اعتبار'];
        if (isset($map[$methodStr])) return $map[$methodStr];
        foreach ($map as $key => $value) if (str_contains($methodStr, $key)) return $value;
        return $methodStr;
    };

    $isCanceled = str_contains($invoice->status?->name ?? '', 'لغو');

    $total = (float) ($invoice->total ?? 0);
    $paid  = (float) ($invoice->paid_amount ?? 0);
    $due   = max(0, $total - $paid);
    $isFullyPaid = $due <= 0.01 && $total > 0 && !$isCanceled;

    $pickSetting = function (array $keys) use ($settings) {
        foreach ($keys as $key) if (!empty($settings[$key])) return $settings[$key];
        return null;
    };

    if (!isset($sellerInfo)) {
        $customFieldsRaw = $pickSetting(['identity_custom_fields', 'seller_custom_fields']);
        $sellerCustomFieldsRaw = [];
        if ($customFieldsRaw) {
            $decodedSellerFields = json_decode($customFieldsRaw, true);
            if (is_array($decodedSellerFields)) $sellerCustomFieldsRaw = array_values(array_filter($decodedSellerFields, fn ($field) => !empty($field['value'] ?? null)));
        }
        $sellerInfo = [
            'name' => $pickSetting(['identity_name', 'seller_name', 'company_name']) ?? '',
            'economic_number' => $pickSetting(['identity_economic_code', 'identity_economic_number', 'seller_economic_number', 'economic_number']) ?? '',
            'national_id' => $pickSetting(['identity_national_id', 'seller_national_id', 'national_id']) ?? '',
            'registration_number' => $pickSetting(['identity_registration_number', 'seller_registration_number', 'registration_number']) ?? '',
            'phone_fax' => $pickSetting(['identity_phone_fax', 'seller_phone_fax', 'phone_fax']) ?? '',
            'address' => $pickSetting(['identity_full_address', 'identity_address', 'seller_address', 'address']) ?? '',
            'stamp_signature_image' => $pickSetting(['identity_seal_signature', 'seller_stamp_signature', 'stamp_signature_image']),
            'custom_fields' => $sellerCustomFieldsRaw,
        ];
    }

    $sellerCustomFields = $sellerInfo['custom_fields'] ?? [];

    $sellerFields = array_values(array_filter([
        ['label' => 'نام شخص حقیقی/حقوقی', 'value' => $sellerInfo['name'], 'span' => 4, 'numeric' => false],
        ['label' => 'شماره اقتصادی', 'value' => $sellerInfo['economic_number'], 'span' => 2, 'numeric' => true],
        ['label' => 'شناسه ملی', 'value' => $sellerInfo['national_id'], 'span' => 2, 'numeric' => true],
        ['label' => 'شماره ثبت', 'value' => $sellerInfo['registration_number'], 'span' => 2, 'numeric' => true],
        ['label' => 'تلفن/نمابر', 'value' => $sellerInfo['phone_fax'], 'span' => 2, 'numeric' => true],
        ['label' => 'نشانی کامل', 'value' => $sellerInfo['address'], 'span' => 4, 'numeric' => false],
    ], fn ($f) => !empty(trim((string) $f['value']))));

    $hasSellerBlock = !empty($sellerFields) || !empty($sellerCustomFields);

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

    $stampSignatureDataUri = $toDataUri($sellerInfo['stamp_signature_image'] ?? null);

    $inlineAppCss = '';
    foreach ([public_path('build/manifest.json'), public_path('build/.vite/manifest.json')] as $manifestPath) {
        if (!is_file($manifestPath)) continue;
        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (!is_array($manifest)) continue;
        foreach ($manifest as $entryKey => $entry) {
            if (str_contains($entryKey, 'app.css') && isset($entry['file'])) {
                $cssFile = public_path('build/' . $entry['file']);
                if (is_file($cssFile)) $inlineAppCss = file_get_contents($cssFile);
                break 2;
            }
        }
    }
@endphp

    @php
        $orientation = $settings['services_official_invoice_orientation'] ?? 'portrait';
        $containerWidth = $orientation === 'landscape' ? '297mm' : '210mm';
        $containerHeight = $orientation === 'landscape' ? '210mm' : '297mm';
        $pageSize = $orientation === 'landscape' ? 'A4 landscape' : 'A4 portrait';
    @endphp

    <!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isProforma ? 'پیش فاکتور' : 'صورتحساب رسمی' }}
        شماره {{ $faNum($invoice->invoice_number ?: $invoice->proforma_invoice_number) }}</title>
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
            background-color: #f3f4f6;
            color: #000;
            font-size: 11px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .a4-container {
            width: {{ $containerWidth }};
            min-height: {{ $containerHeight }};
            padding: 10mm 15mm;
            margin: 10mm auto;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        @media print {
            @page {
                size: {{ $pageSize }};
                margin: 0;
            }

            body {
                background: white;
                margin: 0;
            }

            .a4-container {
                margin: 0;
                box-shadow: none;
                width: 100%;
                min-height: 100vh;
                page-break-after: always;
            }

            .no-print {
                display: none !important;
            }
        }

        .border-official {
            border-color: #000;
        }

        .table-cell-border {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        .header-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        thead {
            display: table-header-group;
        }

        .subfield-row td {
            background: #fafafa;
            border-top: 1px dashed #999 !important;
            border-bottom: 1px dashed #999 !important;
            font-size: 9.5px;
            color: #374151;
        }

        .avoid-break {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
    </style>
</head>
<body>
<div class="fixed bottom-8 right-8 no-print z-50">
    <button onclick="window.print()"
            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-full shadow-lg flex items-center gap-2 transition-transform transform hover:scale-105">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
        </svg>
        <span>چاپ (PDF)</span>
    </button>
</div>

<div class="a4-container relative">
    @if($isFullyPaid && !$isCanceled)
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0 opacity-10">
            <span class="text-8xl font-bold text-green-600 transform -rotate-45">تسویه شده</span>
        </div>
    @endif

    <div class="relative z-10">
        <div class="flex justify-between items-start mb-4">
            <div class="w-1/4 flex flex-col items-start pt-1">
                @if($isCanceled)
                    <span class="inline-block border-2 border-red-500 text-red-600 bg-red-50 font-bold px-3 py-1 rounded-lg text-sm transform -rotate-6 shadow-sm">لغو شده</span>
                @endif
            </div>
            <div class="w-2/4 header-title">
                @if($isProforma)
                    پیش فاکتور
                @else
                    صورتحساب فروش کالا و خدمات
                @endif
            </div>
            <div class="w-1/4 text-left space-y-1 text-[10px]">
                <p>شماره سریال: <span
                        class="text-xs font-bold">{{ $faNum($invoice->invoice_number ?: $invoice->proforma_invoice_number) }}</span>
                </p>
                <p>تاریخ صدور: <span class="font-bold">{{ $toJalali($invoice->issue_date) }}</span></p>
                @if($invoice->due_date)
                    <p>تاریخ سررسید: <span class="font-bold">{{ $toJalali($invoice->due_date) }}</span></p>
                @endif
            </div>
        </div>

        @if($hasSellerBlock)
            <div class="border border-official rounded mb-4">
                <div class="bg-gray-100 border-b border-official text-center font-bold py-1.5">مشخصات فروشنده</div>
                <div class="p-3 grid grid-cols-4 gap-y-3 gap-x-4 text-[10.5px]">
                    @foreach($sellerFields as $field)
                        <div class="col-span-{{ $field['span'] }}">
                            <span class="text-gray-600">{{ $field['label'] }}:</span>
                            <span
                                class="font-bold">{{ $field['numeric'] ? $faNum($field['value']) : $field['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Buyer Info --}}
        <div class="border border-official rounded mb-4">
            <div class="bg-gray-100 border-b border-official text-center font-bold py-1">مشخصات خریدار</div>
            <div class="p-2 grid grid-cols-4 gap-2 text-[10px]">
                @if($buyerNameField)
                    <div class="col-span-2"><span class="text-gray-600">نام شخص حقیقی/حقوقی:</span> <span
                            class="font-bold">{{ $buyerNameField['value'] }}</span></div>
                @endif

                @foreach($buyerOtherFields as $field)
                    <div class="col-span-2">
                        <span class="text-gray-600">{{ $field['label'] }}:</span>
                        <span class="font-bold"
                              @if(in_array($field['id'], $ltrFields)) dir="ltr" @endif>{{ $faNum($field['value']) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mb-4">
            <table class="w-full border-collapse border border-official text-[10px]">
                <thead class="bg-gray-100 font-bold">
                <tr>
                    <th class="table-cell-border w-8">ردیف</th>
                    <th class="table-cell-border">شرح کالا یا خدمات</th>
                    <th class="table-cell-border w-12">تعداد</th>
                    <th class="table-cell-border w-12">واحد</th>
                    <th class="table-cell-border w-24">مبلغ واحد<br><span class="font-normal text-[9px]">({{ $currencyLabel }})</span>
                    </th>
                    <th class="table-cell-border w-20">تخفیف<br><span class="font-normal text-[9px]">({{ $currencyLabel }})</span>
                    </th>
                    @if(($taxMode ?? 'invoice') === 'item')
                        <th class="table-cell-border w-20">مالیات<br><span class="font-normal text-[9px]">({{ $currencyLabel }})</span>
                        </th>
                    @endif
                    <th class="table-cell-border w-28">مبلغ کل<br><span class="font-normal text-[9px]">({{ $currencyLabel }})</span>
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($invoice->items as $index => $item)
                    @php
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
                        <td class="table-cell-border">{{ $faNum($index + 1) }}</td>
                        <td class="table-cell-border text-right">
                            <span
                                class="font-bold">{{ $item->custom_service_name ?: ($item->service->name ?? 'ردیف دستی') }}</span>
                            @if($item->description && $item->description !== ($item->custom_service_name ?: ($item->service->name ?? '')))
                                <div class="text-gray-500 text-[9px] mt-1">{{ $item->description }}</div>
                            @endif
                            @php $savedCustomFields = $item->meta['custom_fields'] ?? []; @endphp
                            @if(!empty($savedCustomFields))
                                <div class="text-gray-600 text-[9px] mt-1">
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
                                        فیلدهای سفارشی: {{ implode(' | ', $printedFields) }}
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="table-cell-border">{{ $faNum($displayQty) }}</td>
                        <td class="table-cell-border">{{ $item->unit ?? 'عدد' }}</td>
                        <td class="table-cell-border">{{ $faNum(number_format($rowBasePrice)) }}</td>
                        <td class="table-cell-border text-red-600">{{ $rowDiscount > 0 ? $faNum(number_format($rowDiscount)) : '۰' }}</td>
                        @if(($taxMode ?? 'invoice') === 'item')
                            <td class="table-cell-border text-green-600">{{ $item->tax_amount > 0 ? $faNum(number_format($item->tax_amount)) . ' (' . $faNum((float) $item->tax_percent) . '٪)' : '۰' }}</td>
                        @endif
                        <td class="table-cell-border font-bold">{{ $faNum(number_format($rowTotal)) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                @php $footColspan = ($taxMode ?? 'invoice') === 'item' ? 7 : 6; @endphp
                <tr class="bg-gray-100 font-bold">
                    <td colspan="{{ $footColspan }}" class="table-cell-border text-left">جمع مبالغ:</td>
                    <td class="table-cell-border">{{ $faNum(number_format($invoice->subtotal)) }}</td>
                </tr>
                @if($invoice->discount_amount > 0)
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="{{ $footColspan }}" class="table-cell-border text-left text-red-600">مجموع تخفیف‌ها:</td>
                        <td class="table-cell-border text-red-600">{{ $faNum(number_format($invoice->discount_amount)) }}</td>
                    </tr>
                @endif
                @if($invoice->tax_amount > 0)
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="{{ $footColspan }}" class="table-cell-border text-left text-green-600">مالیات
                            @if((float) $invoice->tax_percent > 0)
                                ({{ $faNum((float) $invoice->tax_percent) }}٪):
                            @else
                                :
                            @endif
                        </td>
                        <td class="table-cell-border text-green-600">{{ $faNum(number_format($invoice->tax_amount)) }}</td>
                    </tr>
                @endif
                <tr class="bg-gray-100 font-bold text-[12px]">
                    <td colspan="{{ $footColspan }}" class="table-cell-border text-left text-indigo-600">مبلغ نهایی ({{ $currencyLabel }}
                        ):
                    </td>
                    <td class="table-cell-border text-indigo-600">{{ $faNum(number_format($total)) }}</td>
                </tr>
                </tfoot>
            </table>
        </div>

        @if($invoice->payments->isNotEmpty())
            <div class="mb-4 avoid-break">
                <table class="w-full border-collapse border border-official text-[9.5px]">
                    <thead class="bg-gray-100 font-bold">
                    <tr>
                        <th class="table-cell-border">تاریخ</th>
                        <th class="table-cell-border">مبلغ</th>
                        <th class="table-cell-border">روش پرداخت</th>
                        <th class="table-cell-border">کد رهگیری</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($invoice->payments as $payment)
                        <tr>
                            <td class="table-cell-border">{{ $toJalali($payment->paid_at) }}</td>
                            <td class="table-cell-border font-bold text-emerald-600">{{ $faNum(number_format($payment->amount)) }}</td>
                            <td class="table-cell-border">{{ $getPaymentMethodName($payment->method) }}</td>
                            <td class="table-cell-border" dir="ltr">{{ $faNum($payment->transaction_id) ?: '---' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="text-[10px] mt-1 text-left font-bold">
                    پرداخت شده: <span
                        class="text-emerald-600">{{ $faNum(number_format($paid)) }} {{ $currencyLabel }}</span> &nbsp;|&nbsp;
                    مانده بدهی: <span
                        class="text-rose-600">{{ $faNum(number_format($due)) }} {{ $currencyLabel }}</span>
                </div>
            </div>
        @endif

        @if(!empty($settings['services_invoice_footer_note']))
            <div class="mt-4 border border-official rounded p-2 text-[10px] avoid-break bg-gray-50">
                <strong class="text-gray-800">یادداشت:</strong>
                <div class="mt-1 leading-relaxed">{!! nl2br(e($settings['services_invoice_footer_note'])) !!}</div>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4 mt-6 avoid-break">
            <div></div>
            <div class="flex justify-between items-start border border-official rounded p-4 h-32">
                <div class="w-1/2 text-center h-full flex flex-col justify-between">
                    <p class="font-bold text-[11px]">مهر و امضای فروشنده</p>
                    @if($stampSignatureDataUri)
                        <div class="flex-grow flex items-center justify-center">
                            <img src="{{ $stampSignatureDataUri }}" alt="مهر و امضا"
                                 class="max-h-20 max-w-full object-contain mix-blend-multiply">
                        </div>
                    @else
                        <div class="flex-grow"></div>
                    @endif
                </div>
                <div
                    class="w-1/2 text-center h-full flex flex-col justify-between border-r border-dashed border-gray-400">
                    <p class="font-bold text-[11px]">مهر و امضای خریدار</p>
                    <div class="flex-grow"></div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
