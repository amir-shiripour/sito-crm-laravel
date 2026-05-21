@php
function toPersianDigits($number) {
    $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($englishDigits, $persianDigits, $number);
}
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاکتور فروش شماره {{ toPersianDigits($invoice->invoice_number) }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        body {
            font-family: 'IRANYekanX', sans-serif;
            background-color: #f3f4f6; /* Gray-100 for screen, white for print */
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
            @page {
                size: A4;
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

        .border-theme { border-color: #374151; }
        .bg-theme { background-color: #f3f4f6; }
    </style>
</head>
<body>

    {{-- Floating Print Button for Screen View --}}
    <div class="fixed bottom-8 right-8 no-print z-50">
        <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-full shadow-lg flex items-center gap-2 transition-transform transform hover:scale-105">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            <span>چاپ فاکتور (PDF)</span>
        </button>
    </div>

    <div class="a4-container relative">
        {{-- Status Stamp (Watermark) --}}
        @if($invoice->status === 'paid')
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0 opacity-10">
                <span class="text-8xl font-black text-green-600 transform -rotate-45">پرداخت شده</span>
            </div>
        @elseif($invoice->status === 'cancelled')
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0 opacity-10">
                <span class="text-8xl font-black text-red-600 transform -rotate-45">باطل شده</span>
            </div>
        @endif

        <div class="relative z-10">
            {{-- Header --}}
            <header class="flex justify-between items-start border-b-2 border-theme pb-6 mb-6">
                <div>
                    <h1 class="text-3xl font-black text-gray-900 mb-2">صورتحساب</h1>
                    <p class="text-sm text-gray-600 font-medium">{{ $sellerInfo['name'] ?: 'نام فروشنده تعیین نشده' }}</p>
                </div>
                <div class="text-right border border-gray-300 p-3 rounded-lg bg-gray-50">
                    <p class="text-sm mb-1"><span class="text-gray-500 inline-block w-20">شماره فاکتور:</span> <strong class="text-lg">{{ toPersianDigits($invoice->invoice_number) }}</strong></p>
                    <p class="text-sm mb-1"><span class="text-gray-500 inline-block w-20">تاریخ صدور:</span> <strong>{{ $invoice->issue_date ? toPersianDigits(jdate($invoice->issue_date)->format('Y/m/d')) : '---' }}</strong></p>
                </div>
            </header>

            {{-- Parties Info --}}
            <div class="grid grid-cols-2 gap-6 mb-8">
                {{-- Seller --}}
                <div class="border border-gray-300 rounded-lg p-4">
                    <h2 class="text-sm font-bold text-gray-800 border-b border-gray-200 pb-2 mb-3 bg-gray-50 px-2 py-1 -mx-4 -mt-4 rounded-t-lg">مشخصات فروشنده</h2>
                    <div class="text-xs space-y-2 text-gray-700">
                        <p><span class="text-gray-500 w-16 inline-block">نام:</span> <span class="font-bold text-sm">{{ $sellerInfo['name'] ?: '---' }}</span></p>
                        <div class="grid grid-cols-2 gap-2">
                            <p><span class="text-gray-500 w-16 inline-block">شناسه ملی:</span> <span>{{ toPersianDigits($sellerInfo['national_id'] ?: '---') }}</span></p>
                            <p><span class="text-gray-500 w-16 inline-block">کد اقتصادی:</span> <span>{{ toPersianDigits($sellerInfo['economic_number'] ?: '---') }}</span></p>
                            <p><span class="text-gray-500 w-16 inline-block">شماره ثبت:</span> <span>{{ toPersianDigits($sellerInfo['registration_number'] ?: '---') }}</span></p>
                            <p><span class="text-gray-500 w-16 inline-block">کد پستی:</span> <span>{{ toPersianDigits($sellerInfo['postal_code'] ?: '---') }}</span></p>
                        </div>
                        <p><span class="text-gray-500 w-16 inline-block">تلفن/فکس:</span> <span>{{ toPersianDigits($sellerInfo['phone_fax'] ?: '---') }}</span></p>
                        <p><span class="text-gray-500 w-16 inline-block">نشانی:</span> <span>{{ $sellerInfo['province_city'] ? $sellerInfo['province_city'] . ' - ' : '' }}{{ $sellerInfo['address'] ?: '---' }}</span></p>
                        <div class="grid grid-cols-2 gap-4 text-xs text-gray-700">
                            @foreach($sellerInfo['custom_fields'] as $field)
                                <p><span class="text-gray-500 font-bold inline-block mr-1">{{ $field['key'] }}:</span> <span>{{ toPersianDigits($field['value']) }}</span></p>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Buyer --}}
                <div class="border border-gray-300 rounded-lg p-4">
                    <h2 class="text-sm font-bold text-gray-800 border-b border-gray-200 pb-2 mb-3 bg-gray-50 px-2 py-1 -mx-4 -mt-4 rounded-t-lg">مشخصات خریدار</h2>
                    <div class="text-xs space-y-2 text-gray-700">
                        <p><span class="text-gray-500 w-16 inline-block">نام:</span> <span class="font-bold text-sm">{{ $invoice->client->full_name ?? '---' }}</span></p>
                        <div class="grid grid-cols-2 gap-2">
                            <p><span class="text-gray-500 w-16 inline-block">شناسه/ملی:</span> <span>{{ toPersianDigits($invoice->client->national_code ?? '---') }}</span></p>
                            <p><span class="text-gray-500 w-16 inline-block">تلفن همراه:</span> <span>{{ toPersianDigits($invoice->client->phone ?? '---') }}</span></p>
                        </div>
                        {{-- Add more client details if available in the DB (like address, postal code) --}}
                        <p><span class="text-gray-500 w-16 inline-block">نشانی:</span> <span>{{ $invoice->client->address ?? '---' }}</span></p>
                    </div>
                </div>
            </div>



            {{-- Items Table --}}
            <div class="mb-8">
                <table class="w-full text-sm border-collapse border border-gray-300">
                    <thead class="bg-gray-100 text-gray-800 font-bold border-b-2 border-gray-300">
                        <tr>
                            <th class="border border-gray-300 p-2 w-8 text-center">ردیف</th>
                            <th class="border border-gray-300 p-2 text-right">شرح کالا یا خدمات</th>
                            <th class="border border-gray-300 p-2 w-20 text-center">تعداد</th>
                            <th class="border border-gray-300 p-2 w-20 text-center">واحد</th>
                            <th class="border border-gray-300 p-2 w-20 text-center">مبلغ واحد</th>
                            <th class="border border-gray-300 p-2 w-20 text-center">تخفیف</th>
                            <th class="border border-gray-300 p-2 w-20 text-center">مبلغ کل</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-900">
                        @foreach($invoice->items as $index => $item)
                            <tr>
                                <td class="border border-gray-300 p-2 text-center">{{ toPersianDigits($index + 1) }}</td>
                                <td class="border border-gray-300 p-2">{{ $item->description }}</td>
                                <td class="border border-gray-300 p-2 text-center">{{ toPersianDigits($item->quantity + 0) }}</td>
                                <td class="border border-gray-300 p-2 text-center">{{ $item->unit_type ?? '---' }}</td>
                                <td class="border border-gray-300 p-2 text-center">{{ toPersianDigits(number_format($item->unit_price)) }}</td>
                                <td class="border border-gray-300 p-2 text-center text-red-600">{{ toPersianDigits(number_format($item->discount)) }}</td>
                                <td class="border border-gray-300 p-2 text-center font-bold">{{ toPersianDigits(number_format($item->total_price - $item->discount)) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totals & Signatures --}}
            <div class="flex justify-between items-start gap-8 border-t-2 border-theme pt-6">

                {{-- Signatures & Notes --}}
                <div class="w-1/2 flex flex-col justify-between">
                    <div>
                        @if($invoice->notes)
                            <h4 class="text-xs font-bold text-gray-600 mb-1">توضیحات و شرایط:</h4>
                            <p class="text-xs text-gray-700 whitespace-pre-line leading-relaxed">{{ $invoice->notes }}</p>
                        @endif
                    </div>

                    <div class="flex justify-between mt-12 px-8">
                        <div class="text-center">
                            <p class="text-sm font-bold text-gray-800 mb-2">مهر و امضای فروشنده</p>
                            @if(!empty($sellerInfo['stamp_signature_image']))
                                <div class="h-24 flex items-center justify-center">
                                    <img src="{{ Storage::url($sellerInfo['stamp_signature_image']) }}" alt="مهر و امضا" class="max-h-full object-contain mix-blend-multiply" style="width: {{ $sellerInfo['stamp_signature_width'] ?: 'auto' }}px;">
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

                {{-- Totals --}}
                <div class="w-1/2 max-w-sm ml-auto">
                    <table class="w-full text-sm border-collapse border border-gray-300">
                        <tbody>
                            <tr>
                                <td class="border border-gray-300 p-2 font-bold text-gray-700 bg-gray-50 text-left">جمع مبالغ:</td>
                                <td class="border border-gray-300 p-2 text-center font-bold">{{ toPersianDigits(number_format($invoice->subtotal)) }}</td>
                            </tr>
                            @if($invoice->discount > 0)
                                <tr>
                                    <td class="border border-gray-300 p-2 font-bold text-gray-700 bg-gray-50 text-left">جمع تخفیف‌ها:</td>
                                    <td class="border border-gray-300 p-2 text-center text-red-700">- {{ toPersianDigits(number_format($invoice->discount)) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="border border-gray-300 p-2 font-bold text-gray-700 bg-gray-50 text-left">مبلغ مشمول مالیات:</td>
                                <td class="border border-gray-300 p-2 text-center font-bold">{{ toPersianDigits(number_format($invoice->subtotal - $invoice->discount)) }}</td>
                            </tr>
                            @if($invoice->tax > 0)
                                <tr>
                                    <td class="border border-gray-300 p-2 font-bold text-gray-700 bg-gray-50 text-left">مالیات و عوارض ({{ toPersianDigits($invoice->tax + 0) }}%):</td>
                                    <td class="border border-gray-300 p-2 text-center text-green-700">+ {{ toPersianDigits(number_format((($invoice->subtotal - $invoice->discount) * $invoice->tax) / 100)) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="border border-gray-300 p-3 font-black text-gray-900 bg-gray-200 text-left text-base">مبلغ نهایی:</td>
                                <td class="border border-gray-300 p-3 text-center font-black text-lg">{{ toPersianDigits(number_format($invoice->total_amount)) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    {{-- Auto trigger print dialog if a query parameter is set (optional UX enhancement) --}}
    @if(request()->has('auto_print'))
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
    @endif
</body>
</html>
