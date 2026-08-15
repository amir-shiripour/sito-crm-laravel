@php
use Modules\Accounting\App\Services\CurrencyService;
function toPersianDigits($number) {
    $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($englishDigits, $persianDigits, $number);
}
$currencySuffix = CurrencyService::getBaseCurrency();
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoice->title }} شماره {{ toPersianDigits($invoice->display_number) }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'IRANYekanX', sans-serif;
            background-color: #f3f4f6;
            color: #000;
        }
        .a4-container {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 10mm auto;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        @media print {
            @page { size: A4; margin: 0; }
            body { background: white; margin: 0; }
            .a4-container { margin: 0; box-shadow: none; width: 100%; min-height: 100vh; page-break-after: always; }
            .no-print { display: none !important; }
        }
        .border-theme { border-color: #374151; }
        .bg-theme { background-color: #f3f4f6; }
    </style>
</head>
<body>
    <div class="a4-container relative">
        @if($invoice->status === 'paid')
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0 opacity-10">
                <span class="text-8xl font-black text-green-600 transform -rotate-45">پرداخت شده</span>
            </div>
        @endif
        <div class="relative z-10">
            <header class="flex justify-between items-start border-b-2 border-theme pb-6 mb-6">
                <div>
                    <h1 class="text-3xl font-black text-gray-900 mb-2">{{ $invoice->title }}</h1>
                    <p class="text-sm text-gray-600 font-medium">{{ $sellerInfo['name'] ?: 'نام فروشنده تعیین نشده' }}</p>
                </div>
                <div class="text-right border border-gray-300 p-3 rounded-lg bg-gray-50">
                    <p class="text-sm mb-1"><span class="text-gray-500 inline-block w-20">شماره:</span> <strong class="text-lg">{{ toPersianDigits($invoice->display_number) }}</strong></p>
                    <p class="text-sm mb-1"><span class="text-gray-500 inline-block w-20">تاریخ صدور:</span> <strong>{{ $invoice->issue_date ? toPersianDigits(jdate($invoice->issue_date)->format('Y/m/d')) : '---' }}</strong></p>
                </div>
            </header>

            <div class="grid grid-cols-2 gap-6 mb-8">
                <div class="border border-gray-300 rounded-lg p-4">
                    <h2 class="text-sm font-bold text-gray-800 border-b border-gray-200 pb-2 mb-3 bg-gray-50 px-2 py-1 -mx-4 -mt-4 rounded-t-lg">مشخصات فروشنده</h2>
                    <div class="text-xs space-y-2 text-gray-700">
                        <p><span class="text-gray-500 w-16 inline-block">نام:</span> <span class="font-bold text-sm">{{ $sellerInfo['name'] ?: '---' }}</span></p>
                        <div class="grid grid-cols-2 gap-2">
                            <p><span class="text-gray-500 w-16 inline-block">شناسه ملی:</span> <span>{{ toPersianDigits($sellerInfo['national_id'] ?: '---') }}</span></p>
                            <p><span class="text-gray-500 w-16 inline-block">کد اقتصادی:</span> <span>{{ toPersianDigits($sellerInfo['economic_number'] ?: '---') }}</span></p>
                        </div>
                        <p><span class="text-gray-500 w-16 inline-block">تلفن/فکس:</span> <span>{{ toPersianDigits($sellerInfo['phone_fax'] ?: '---') }}</span></p>
                        <p><span class="text-gray-500 w-16 inline-block">نشانی:</span> <span>{{ $sellerInfo['address'] ?: '---' }}</span></p>
                        @foreach($sellerInfo['custom_fields'] as $field)
                            <p><span class="text-gray-500 font-bold inline-block mr-1">{{ $field['key'] }}:</span> <span>{{ toPersianDigits($field['value']) }}</span></p>
                        @endforeach
                    </div>
                </div>

                <div class="border border-gray-300 rounded-lg p-4">
                    <h2 class="text-sm font-bold text-gray-800 border-b border-gray-200 pb-2 mb-3 bg-gray-50 px-2 py-1 -mx-4 -mt-4 rounded-t-lg">مشخصات خریدار</h2>
                    <div class="text-xs space-y-2 text-gray-700">
                        <p><span class="text-gray-500 w-16 inline-block">نام:</span> <span class="font-bold text-sm">{{ $invoice->customer->full_name ?? $invoice->customer->name ?? '---' }}</span></p>
                        <p><span class="text-gray-500 w-16 inline-block">تلفن همراه:</span> <span>{{ toPersianDigits($invoice->customer->phone ?? '---') }}</span></p>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <table class="w-full text-sm border-collapse border border-gray-300">
                    <thead class="bg-gray-100 text-gray-800 font-bold border-b-2 border-gray-300">
                        <tr>
                            <th class="border border-gray-300 p-2 w-8 text-center">ردیف</th>
                            <th class="border border-gray-300 p-2 text-right">شرح کالا یا خدمات</th>
                            <th class="border border-gray-300 p-2 w-20 text-center">تعداد</th>
                            <th class="border border-gray-300 p-2 w-20 text-center">واحد</th>
                            <th class="border border-gray-300 p-2 w-24 text-center">مبلغ واحد ({{ $currencySuffix }})</th>
                            <th class="border border-gray-300 p-2 w-28 text-center">مبلغ کل ({{ $currencySuffix }})</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-900">
                        @foreach($invoice->items as $index => $item)
                            <tr>
                                <td class="border border-gray-300 p-2 text-center">{{ toPersianDigits($index + 1) }}</td>
                                <td class="border border-gray-300 p-2">{{ $item->description }}</td>
                                <td class="border border-gray-300 p-2 text-center">{{ toPersianDigits(number_format($item->quantity)) }}</td>
                                <td class="border border-gray-300 p-2 text-center">{{ $item->unit_type ?? '---' }}</td>
                                <td class="border border-gray-300 p-2 text-center">{{ toPersianDigits(number_format($item->unit_price)) }}</td>
                                <td class="border border-gray-300 p-2 text-center font-bold">{{ toPersianDigits(number_format($item->total)) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-start gap-8 border-t-2 border-theme pt-6">
                <div class="w-1/2 flex flex-col justify-between">
                    <div>
                        @if($invoice->notes)
                            <h4 class="text-xs font-bold text-gray-600 mb-1">توضیحات:</h4>
                            <p class="text-xs text-gray-700 whitespace-pre-line leading-relaxed">{{ $invoice->notes }}</p>
                        @endif
                    </div>
                    <div class="flex justify-between mt-12 px-8">
                        <div class="text-center">
                            <p class="text-sm font-bold text-gray-800 mb-2">مهر و امضای فروشنده</p>
                            @if(!empty($sellerInfo['stamp_signature_image']))
                                <div class="h-24 flex items-center justify-center">
                                    <img src="{{ Storage::url($sellerInfo['stamp_signature_image']) }}" alt="مهر و امضا" class="max-h-full object-contain mix-blend-multiply">
                                </div>
                            @else
                                <div class="h-24 flex items-end justify-center">
                                    <p class="text-xs text-gray-400 border-t border-dashed border-gray-400 pt-2 w-32 mx-auto"></p>
                                </div>
                            @endif
                        </div>
                        <div class="text-center">
                            <p class="text-sm font-bold text-gray-800 mb-2">مهر و امضای خریدار</p>
                            <div class="h-24 flex items-end justify-center">
                                <p class="text-xs text-gray-400 border-t border-dashed border-gray-400 pt-2 w-32 mx-auto"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-1/2 max-w-sm ml-auto">
                    <table class="w-full text-sm border-collapse border border-gray-300">
                        <tbody>
                            <tr>
                                <td class="border border-gray-300 p-2 font-bold text-gray-700 bg-gray-50 text-left">جمع مبالغ:</td>
                                <td class="border border-gray-300 p-2 text-center font-bold">{{ toPersianDigits(number_format($invoice->subtotal)) }}</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 p-2 font-bold text-gray-700 bg-gray-50 text-left">تخفیف:</td>
                                <td class="border border-gray-300 p-2 text-center text-red-700">- {{ toPersianDigits(number_format($invoice->discount)) }}</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 p-2 font-bold text-gray-700 bg-gray-50 text-left">مالیات:</td>
                                <td class="border border-gray-300 p-2 text-center text-green-700">+ {{ toPersianDigits(number_format($invoice->tax)) }}</td>
                            </tr>
                            <tr class="font-black text-lg">
                                <td class="border border-gray-300 p-3 bg-gray-200 text-left">مبلغ نهایی ({{ $currencySuffix }}):</td>
                                <td class="border border-gray-300 p-3 text-center">{{ toPersianDigits(number_format($invoice->total)) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
