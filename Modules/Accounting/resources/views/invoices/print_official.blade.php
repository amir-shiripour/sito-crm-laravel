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
    <title>صورتحساب رسمی شماره {{ toPersianDigits($invoice->invoice_number) }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        body {
            font-family: 'IRANYekanX', sans-serif;
            background-color: #f3f4f6;
            color: #000;
            font-size: 11px; /* Smaller font for official dense tables */
        }

        .a4-container {
            width: 297mm; /* Landscape width */
            min-height: 210mm; /* Landscape height */
            padding: 10mm 15mm;
            margin: 10mm auto;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        @media print {
            @page {
                size: A4 landscape; /* Landscape orientation */
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

        /* Custom Borders for Official Look */
        .border-official { border-color: #000; }
        .table-cell-border { border: 1px solid #000; padding: 4px; text-align: center; }

        .header-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* Override Tailwind class for numbers */
        .dir-ltr {
            direction: rtl !important;
            unicode-bidi: embed;
        }
    </style>
</head>
<body>

    <div class="fixed bottom-8 right-8 no-print z-50">
        <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-full shadow-lg flex items-center gap-2 transition-transform transform hover:scale-105">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            <span>چاپ فاکتور رسمی (PDF)</span>
        </button>
    </div>

    <div class="a4-container relative">
        @if($invoice->status === 'paid')
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0 opacity-10">
                <span class="text-8xl font-black text-green-600 transform -rotate-45">تسویه شده</span>
            </div>
        @elseif($invoice->status === 'cancelled')
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0 opacity-10">
                <span class="text-8xl font-black text-red-600 transform -rotate-45">باطل شده</span>
            </div>
        @endif

        <div class="relative z-10">
            {{-- Header --}}
            <div class="flex justify-between items-start mb-4">
                <div class="w-1/4">
                    {{-- Space for logo if needed --}}
                </div>
                <div class="w-2/4 header-title">
                    صورتحساب فروش کالا و خدمات
                </div>
                <div class="w-1/4 text-left space-y-1 text-[10px]">
                    <p>شماره سریال: <span class="text-xs font-bold">{{ toPersianDigits($invoice->invoice_number) }}</span></p>
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
                    <div><span class="text-gray-600">استان/شهرستان:</span> <span>{{ $sellerInfo['province_city'] ?: '---' }}</span></div>
                    <div><span class="text-gray-600">کد پستی ۱۰ رقمی:</span> <span>{{ toPersianDigits($sellerInfo['postal_code'] ?: '---') }}</span></div>
                    <div><span class="text-gray-600">تلفن/نمابر:</span> <span class="inline-block">{{ toPersianDigits($sellerInfo['phone_fax'] ?: '---') }}</span></div>

                    <div class="col-span-4"><span class="text-gray-600">نشانی کامل:</span> <span>{{ $sellerInfo['address'] ?: '---' }}</span></div>

                    {{-- Render custom fields if they exist --}}
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
                    <div class="col-span-2"><span class="text-gray-600">نام شخص حقیقی/حقوقی:</span> <span class="font-bold">{{ $invoice->client->full_name ?? '---' }}</span></div>
                    <div><span class="text-gray-600">شماره اقتصادی:</span> <span>{{ toPersianDigits($invoice->client->economic_code ?? '---') }}</span></div>
                    <div><span class="text-gray-600">شناسه ملی:</span> <span>{{ toPersianDigits($invoice->client->national_code ?? '---') }}</span></div>

                    <div><span class="text-gray-600">شماره ثبت:</span> <span>{{ toPersianDigits($invoice->client->registration_number ?? '---') }}</span></div>
                    <div><span class="text-gray-600">استان/شهرستان:</span> <span>{{ $invoice->client->province ?? '---' }} - {{ $invoice->client->city ?? '---' }}</span></div>
                    <div><span class="text-gray-600">کد پستی ۱۰ رقمی:</span> <span>{{ toPersianDigits($invoice->client->postal_code ?? '---') }}</span></div>
                    <div><span class="text-gray-600">تلفن/نمابر:</span> <span class="inline-block">{{ toPersianDigits($invoice->client->phone ?? '---') }}</span></div>

                    <div class="col-span-4"><span class="text-gray-600">نشانی کامل:</span> <span>{{ $invoice->client->address ?? '---' }}</span></div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="mb-4">
                <table class="w-full border-collapse border border-official text-[10px]">
                    <thead class="bg-gray-100 font-bold">
                        <tr>
                            <th class="table-cell-border w-8">ردیف</th>
                            <th class="table-cell-border w-16">کد کالا</th>
                            <th class="table-cell-border">شرح کالا یا خدمات</th>
                            <th class="table-cell-border w-12">تعداد</th>
                            <th class="table-cell-border w-12">واحد</th>
                            <th class="table-cell-border w-24">مبلغ واحد<br><span class="font-normal text-[9px]">(ریال)</span></th>
                            <th class="table-cell-border w-28">مبلغ کل<br><span class="font-normal text-[9px]">(ریال)</span></th>
                            <th class="table-cell-border w-24">مبلغ تخفیف<br><span class="font-normal text-[9px]">(ریال)</span></th>
                            <th class="table-cell-border w-28">مبلغ کل پس از تخفیف<br><span class="font-normal text-[9px]">(ریال)</span></th>
                            <th class="table-cell-border w-24">مالیات و عوارض<br><span class="font-normal text-[9px]">(ریال)</span></th>
                            <th class="table-cell-border w-28">مبلغ کل با مالیات<br><span class="font-normal text-[9px]">(ریال)</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $sumTotalPrice = 0;
                            $sumDiscount = 0;
                            $sumTotalAfterDiscount = 0;
                            $sumTax = 0;
                            $sumFinalTotal = 0;
                        @endphp

                        @foreach($invoice->items as $index => $item)
                            @php
                                $totalPrice = $item->quantity * $item->unit_price;
                                $discount = $item->discount ?? 0;
                                $totalAfterDiscount = $totalPrice - $discount;
                                $taxAmount = ($totalAfterDiscount * $invoice->tax) / 100;
                                $finalTotal = $totalAfterDiscount + $taxAmount;

                                $sumTotalPrice += $totalPrice;
                                $sumDiscount += $discount;
                                $sumTotalAfterDiscount += $totalAfterDiscount;
                                $sumTax += $taxAmount;
                                $sumFinalTotal += $finalTotal;
                            @endphp
                            <tr>
                                <td class="table-cell-border">{{ toPersianDigits($index + 1) }}</td>
                                <td class="table-cell-border">{{ toPersianDigits($item->item_code ?? '---') }}</td>
                                <td class="table-cell-border text-right">{{ $item->description }}</td>
                                <td class="table-cell-border">{{ toPersianDigits($item->quantity + 0) }}</td>
                                <td class="table-cell-border">{{ $item->unit_type ?? '---' }}</td>
                                <td class="table-cell-border">{{ toPersianDigits(number_format($item->unit_price)) }}</td>
                                <td class="table-cell-border font-bold">{{ toPersianDigits(number_format($totalPrice)) }}</td>
                                <td class="table-cell-border text-red-600">{{ toPersianDigits(number_format($discount)) }}</td>
                                <td class="table-cell-border font-bold">{{ toPersianDigits(number_format($totalAfterDiscount)) }}</td>
                                <td class="table-cell-border text-green-600">{{ toPersianDigits(number_format($taxAmount)) }}</td>
                                <td class="table-cell-border font-bold text-indigo-600">{{ toPersianDigits(number_format($finalTotal)) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-100 font-bold">
                            <td colspan="6" class="table-cell-border text-left">جمع کل:</td>
                            <td class="table-cell-border">{{ toPersianDigits(number_format($sumTotalPrice)) }}</td>
                            <td class="table-cell-border text-red-600">{{ toPersianDigits(number_format($sumDiscount)) }}</td>
                            <td class="table-cell-border">{{ toPersianDigits(number_format($sumTotalAfterDiscount)) }}</td>
                            <td class="table-cell-border text-green-600">{{ toPersianDigits(number_format($sumTax)) }}</td>
                            <td class="table-cell-border text-indigo-600">{{ toPersianDigits(number_format($sumFinalTotal)) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Footer Conditions & Signatures --}}
            <div class="grid grid-cols-2 gap-4 mt-8">
                <div class="text-[10px] text-gray-700">
                    <p class="font-bold mb-1">شرایط و نحوه تسویه:</p>
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

    @if(request()->has('auto_print'))
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
    @endif
    @stack('scripts')
    @livewireScripts
</body>
</html>
