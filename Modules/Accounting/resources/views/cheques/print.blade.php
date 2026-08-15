@php
    // Helper function to convert numbers to Persian words.
    // Ensure you have the 'number-to-persian-words.js' equivalent in PHP or pass it from the controller.
    use Modules\Accounting\App\Services\CurrencyService;function numberToPersianWords($number) {
        // This is a placeholder. You should use a proper library for this.
        return 'مبلغ به حروف';
    }
@endphp
    <!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>چاپ چک شماره {{ $cheque->sayad_number }}</title>
    <style>
        /* Basic Reset & Font */
        body {
            margin: 0;
            padding: 0;
            font-family: 'IRANYekanX', 'B Nazanin', 'Nazanin', sans-serif; /* Common fonts for cheque printing */
            background-color: #f0f0f0;
        }

        /* The main canvas for the cheque */
        .cheque-canvas {
            width: 175mm;
            height: 85mm;
            position: relative;
            margin: 20px auto;
            overflow: hidden;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            border: 1px dashed #ccc;
        }

        /* Print-specific styles */
        @media print {
            @page {
                size: 175mm 85mm;
                margin: 0;
            }

            body {
                background-color: white;
            }

            .cheque-canvas {
                margin: 0;
                box-shadow: none;
                border: none;
            }

            .no-print {
                display: none !important;
            }
        }

        /* Absolute positioned elements for printing data */
        .print-item {
            position: absolute;
            font-size: 14px; /* Adjust as needed */
            letter-spacing: 2px; /* Spacing for date and amount numbers */
            text-align: center;
        }

        /* --- Placeholder Positions (in mm) --- */
        /* These values MUST be calibrated later */
        .payee-name {
            top: 25mm;
            left: 80mm;
            width: 80mm;
            text-align: right;
            letter-spacing: normal;
        }

        .amount-words {
            top: 35mm;
            left: 40mm;
            width: 120mm;
            text-align: right;
            letter-spacing: normal;
        }

        .amount-numbers {
            top: 48mm;
            left: 20mm;
            width: 40mm;
            font-weight: bold;
        }

        .due-date-year {
            top: 15mm;
            left: 20mm;
        }

        .due-date-month {
            top: 15mm;
            left: 35mm;
        }

        .due-date-day {
            top: 15mm;
            left: 45mm;
        }

        .no-endorse-line {
            top: 65mm;
            left: 10mm;
            width: 80mm;
            text-align: center;
            letter-spacing: normal;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align: center; padding: 10px; background: #333; color: white;">
    <p>این یک پیش‌نمایش برای چاپ چک است. لطفاً برگه چک را در پرینتر قرار داده و دکمه چاپ را بزنید.</p>
    <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">چاپ</button>
</div>

<div class="cheque-canvas">
    {{-- Payee Name --}}
    <div class="print-item payee-name">
        {{ $cheque->drawer_name }}
    </div>

    {{-- Amount in Words --}}
    <div class="print-item amount-words">
        {{ numberToPersianWords($cheque->amount) }} {{ CurrencyService::getBaseCurrency() }}
    </div>

    {{-- Amount in Numbers --}}
    <div class="print-item amount-numbers">
        {{ number_format($cheque->amount) }}
    </div>

    {{-- Due Date (Separated) --}}
    @php
        $dateParts = explode('/', jdate($cheque->due_date)->format('y/m/d'));
    @endphp
    <div class="print-item due-date-year">{{ $dateParts[0] ?? '' }}</div>
    <div class="print-item due-date-month">{{ $dateParts[1] ?? '' }}</div>
    <div class="print-item due-date-day">{{ $dateParts[2] ?? '' }}</div>

    {{-- No Endorsement Line --}}
    <div class="print-item no-endorse-line">
        // جهت جلوگیری از پشت‌نویسی //
    </div>
</div>

</body>
</html>
