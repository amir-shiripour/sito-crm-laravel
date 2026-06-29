<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $contract->contract_number }} - {{ $contract->title }}</title>
    <style>
        body {
            font-family: Tahoma, Arial, sans-serif;
            font-size: 13px;
            line-height: 1.8;
            color: #1f2937;
            background-color: #fff;
            margin: 10px;
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
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="contract-container">
        {!! $contract->rendered_body !!}
    </div>
</body>
</html>
