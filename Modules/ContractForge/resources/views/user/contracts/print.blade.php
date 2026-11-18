<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $contract->contract_number }} - {{ $contract->title }}</title>
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
            font-size: 13px;
            line-height: 1.8;
            color: #1f2937;
            background-color: #fff;
            margin: 30px;
            direction: rtl;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            text-align: right;
            font-family: 'IRANYekanX', Tahoma, Arial, sans-serif;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .mb-6 {
            margin-bottom: 24px;
        }
        .mt-8 {
            margin-top: 32px;
        }
        .border-t {
            border-top: 1px solid #e5e7eb;
        }
        .pt-4 {
            padding-top: 16px;
        }
        .text-gray-500 {
            color: #6b7280;
        }
        
        @media print {
            body {
                margin: 10px;
            }
            .no-print {
                display: none;
            }
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="contract-container">
        {!! $contract->rendered_body !!}
    </div>

    @if($contract->status === 'signed' && $contract->signed_at)
        <div class="mt-8 border-t pt-4" style="font-size: 12px; color: #065f46; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 12px 16px; border-radius: 8px;">
            <strong>تایید و امضای الکترونیکی:</strong>
            این قرارداد توسط {{ $contract->party_name }} در تاریخ
            {{ \Morilog\Jalali\Jalalian::fromCarbon($contract->signed_at)->format('Y/m/d H:i') }}
            مطالعه و تایید الکترونیکی شده است.
        </div>
    @endif
    
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
