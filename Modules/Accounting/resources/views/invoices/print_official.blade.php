@php
use Modules\Accounting\App\Services\CurrencyService;
function toPersianDigits($number) {
    $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($englishDigits, $persianDigits, $number);
}
$currencySuffix = CurrencyService::getBaseCurrency();
$isDraft = $invoice->status === 'draft';
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isDraft ? 'پیش فاکتور' : 'صورتحساب رسمی' }} شماره {{ toPersianDigits($invoice->display_number) }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'IRANYekanX', sans-serif;
            background-color: #f3f4f6;
            color: #000;
            font-size: 11px;
        }
        .a4-container {
            width: 297mm;
            min-height: 210mm;
            padding: 10mm 15mm;
            margin: 10mm auto;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        @media print {
            @page { size: A4 landscape; margin: 0; }
            body { background: white; margin: 0; }
            .a4-container { margin: 0; box-shadow: none; width: 100%; min-height: 100vh; page-break-after: always; }
            .no-print { display: none !important; }
        }
        .border-official { border-color: #000; }
        .table-cell-border { border: 1px solid #000; padding: 4px; text-align: center; }
        .header-title { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="fixed bottom-8 right-8 no-print z-50">
        <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-full shadow-lg flex items-center gap-2 transition-transform transform hover:scale-105">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            <span>چاپ (PDF)</span>
        </button>
    </div>

    <div class="a4-container relative">
        @if($invoice->status === 'paid')
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0 opacity-10">
                <span class="text-8xl font-black text-green-600 transform -rotate-45">تسویه شده</span>
            </div>
        @endif

        <div class="relative z-10">
            {{-- Header --}}
            <div class="flex justify-between items-start mb-4">
                <div class="w-1/4"></div>
                <div class="w-2/4 header-title">
                    @if($isDraft)
                        پیش فاکتور
                    @else
                        صورتحساب فروش کالا و خدمات
                    @endif
                </div>
                <div class="w-1/4 text-left space-y-1 text-[10px]">
                    <p>شماره سریال: <span class="text-xs font-bold">{{ toPersianDigits($invoice->display_number) }}</span></p>
                    <p>تاریخ صدور: <span class="font-bold">{{ $invoice->issue_date ? toPersianDigits(jdate($invoice->issue_date)->format('Y/m/d')) : '---' }}</span></p>
                </div>
            </div>

            {{-- Seller Info --}}
            <div class="border border-official rounded mb-2">
                <div class="bg-gray-100 border-b border-official text-center font-bold py-1">مشخصات فروشنده</div>
                <div class="p-2 grid grid-cols-4 gap-2 text-[10px]">
                    <div class="col-span-2"><span class="text-gray-600">نام شخص حقیقی/حقوقی:</span> <span class="font-bold">{{ $sellerInfo['name'] ?: '---' }}</span></div>
                    <div><span class="text-gray-600">شماره اقتصادی:</span> <span>{{ toPersianDigits($sellerInfo['economic_number'] ?: '---') }}</span></div>
                    <div><span class="text-gray-600">شناسه ملی:</span> <span>{{ toPersianDigits($sellerInfo['national_id'] ?: '---') }}</span></div>
                    <div><span class="text-gray-600">شماره ثبت:</span> <span>{{ toPersianDigits($sellerInfo['registration_number'] ?: '---') }}</span></div>
                    <div><span class="text-gray-600">تلفن/نمابر:</span> <span class="inline-block">{{ toPersianDigits($sellerInfo['phone_fax'] ?: '---') }}</span></div>
                    <div class="col-span-4"><span class="text-gray-600">نشانی کامل:</span> <span>{{ $sellerInfo['address'] ?: '---' }}</span></div>
                    @if(!empty($sellerInfo['custom_fields']))
                        @foreach($sellerInfo['custom_fields'] as $field)
                            <div class="col-span-2"><span class="text-gray-600">{{ $field['key'] }}:</span> <span>{{ toPersianDigits($field['value']) }}</span></div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- Buyer Info --}}
            <div class="border border-official rounded mb-4">
                <div class="bg-gray-100 border-b border-official text-center font-bold py-1">مشخصات خریدار</div>
                <div class="p-2 grid grid-cols-4 gap-2 text-[10px]">
                    <div class="col-span-2"><span class="text-gray-600">نام شخص حقیقی/حقوقی:</span> <span class="font-bold">{{ $invoice->customer->full_name ?? $invoice->customer->name ?? '---' }}</span></div>
                    <div><span class="text-gray-600">تلفن همراه:</span> <span>{{ toPersianDigits($invoice->customer->phone ?? '---') }}</span></div>
                    <div class="col-span-4"><span class="text-gray-600">نشانی کامل:</span> <span>{{ $invoice->customer->address ?? '---' }}</span></div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="mb-4">
                <table class="w-full border-collapse border border-official text-[10px]">
                    <thead class="bg-gray-100 font-bold">
                        <tr>
                            <th class="table-cell-border w-8">ردیف</th>
                            <th class="table-cell-border">شرح کالا یا خدمات</th>
                            <th class="table-cell-border w-12">تعداد</th>
                            <th class="table-cell-border w-12">واحد</th>
                            <th class="table-cell-border w-24">مبلغ واحد<br><span class="font-normal text-[9px]">({{ $currency ?? CurrencyService::getBaseCurrency() }})</span></th>
                            <th class="table-cell-border w-28">مبلغ کل<br><span class="font-normal text-[9px]">({{ $currency ?? CurrencyService::getBaseCurrency() }})</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $index => $item)
                            <tr>
                                <td class="table-cell-border">{{ toPersianDigits($index + 1) }}</td>
                                <td class="table-cell-border text-right">{{ $item->description }}</td>
                                <td class="table-cell-border">{{ toPersianDigits((float)$item->quantity) }}</td>
                                <td class="table-cell-border">{{ $item->unit_type ?? 'عدد' }}</td>
                                <td class="table-cell-border">{{ toPersianDigits(number_format($item->unit_price)) }}</td>
                                <td class="table-cell-border font-bold">{{ toPersianDigits(number_format($item->total)) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-100 font-bold">
                            <td colspan="5" class="table-cell-border text-left">جمع کل:</td>
                            <td class="table-cell-border">{{ toPersianDigits(number_format($invoice->subtotal)) }}</td>
                        </tr>
                        <tr class="bg-gray-100 font-bold">
                            <td colspan="5" class="table-cell-border text-left text-red-600">تخفیف:</td>
                            <td class="table-cell-border text-red-600">{{ toPersianDigits(number_format($invoice->discount)) }}</td>
                        </tr>
                        <tr class="bg-gray-100 font-bold">
                            <td colspan="5" class="table-cell-border text-left text-green-600">مالیات:</td>
                            <td class="table-cell-border text-green-600">{{ toPersianDigits(number_format($invoice->tax)) }}</td>
                        </tr>
                        <tr class="bg-gray-100 font-bold text-lg">
                            <td colspan="5" class="table-cell-border text-left text-indigo-600">مبلغ نهایی ({{ $currencySuffix }}):</td>
                            <td class="table-cell-border text-indigo-600">{{ toPersianDigits(number_format($invoice->total)) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Footer Conditions & Signatures --}}
            <div class="grid grid-cols-2 gap-4 mt-8">
                <div class="text-[10px] text-gray-700">
                    <p class="font-bold mb-1">توضیحات:</p>
                    <p class="whitespace-pre-line leading-relaxed">{{ $invoice->notes ?: '---' }}</p>
                </div>
                <div class="flex justify-between items-start border border-official rounded p-4 h-32">
                    <div class="w-1/2 text-center h-full flex flex-col justify-between">
                        <p class="font-bold text-[11px]">مهر و امضای فروشنده</p>
                        @if(!empty($sellerInfo['stamp_signature_image']))
                            <div class="flex-grow flex items-center justify-center">
                                <img src="{{ Storage::url($sellerInfo['stamp_signature_image']) }}" alt="مهر و امضا" class="max-h-20 max-w-full object-contain mix-blend-multiply">
                            </div>
                        @else
                            <div class="flex-grow"></div>
                        @endif
                    </div>
                    <div class="w-1/2 text-center h-full flex flex-col justify-between border-r border-dashed border-gray-400">
                        <p class="font-bold text-[11px]">مهر و امضای خریدار</p>
                        <div class="flex-grow"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
