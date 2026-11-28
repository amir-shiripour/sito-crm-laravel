@php
    $fontFile = resource_path('fonts/iranYekanX/IRANYekanMediumFaNum.ttf');
    $fontBase64 = file_exists($fontFile) ? base64_encode(file_get_contents($fontFile)) : null;
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>{{ $docTitle ?? 'گزارش رسمی انبار' }}</title>
    <style>
        @if($fontBase64)
        @font-face {
            font-family: 'IRANYekanX';
            src: url('data:font/ttf;base64,{{ $fontBase64 }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'IRANYekanX';
            src: url('data:font/ttf;base64,{{ $fontBase64 }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        @endif

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'IRANYekanX', Tahoma, Arial, sans-serif;
            font-size: 10.5px;
            direction: rtl;
            color: #1a1a1a;
            background: #ffffff;
            margin: 0;
            padding: 8px;
            line-height: 1.55;
        }

        /* Flat Administrative Header */
        .pdf-header {
            width: 100%;
            border-bottom: 1.5px solid #222222;
            padding-bottom: 10px;
            margin-bottom: 14px;
            display: table;
        }

        .pdf-header-right {
            display: table-cell;
            width: 66%;
            vertical-align: middle;
            text-align: right;
        }

        .pdf-header-left {
            display: table-cell;
            width: 34%;
            vertical-align: middle;
            text-align: left;
        }

        .brand-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-logo {
            max-height: 38px;
            max-width: 100px;
            object-fit: contain;
        }

        .org-name {
            font-size: 9.5px;
            color: #444444;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .doc-title {
            font-size: 14px;
            font-weight: bold;
            color: #111111;
            margin: 0;
        }

        .doc-subtitle {
            font-size: 9.5px;
            color: #555555;
            margin-top: 2px;
        }

        .meta-badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 0;
            background: #f4f4f4;
            border: 1px solid #c8c8c8;
            color: #111111;
            font-size: 9.5px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .meta-line {
            font-size: 8.8px;
            color: #444444;
            margin-top: 2px;
        }

        /* Flat Summary / Info Bar */
        .info-grid {
            width: 100%;
            border: 1px solid #cfcfcf;
            border-radius: 0;
            background: #fafafa;
            padding: 8px 10px;
            margin-bottom: 14px;
            display: table;
            table-layout: fixed;
        }

        .info-cell {
            display: table-cell;
            vertical-align: top;
            padding: 0 7px;
            border-left: 1px solid #dddddd;
        }

        .info-cell:last-child {
            border-left: none;
        }

        .info-label {
            font-size: 8.5px;
            color: #555555;
            margin-bottom: 2px;
        }

        .info-value {
            font-size: 10px;
            font-weight: bold;
            color: #111111;
        }

        /* Flat Section Title */
        .section-heading {
            font-size: 10.5px;
            font-weight: bold;
            color: #111111;
            margin: 14px 0 6px 0;
            padding-bottom: 4px;
            border-bottom: 1px solid #222222;
        }

        /* Flat Standard Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #cfcfcf;
            padding: 6px 6px;
            font-size: 9px;
            text-align: center;
            vertical-align: middle;
            color: #1a1a1a;
        }

        table.data-table th {
            background: #f0f0f0;
            color: #111111;
            font-weight: bold;
        }

        table.data-table tbody tr:nth-child(even) td {
            background: #fcfcfc;
        }

        table.data-table tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        table.data-table tfoot td {
            background: #f0f0f0;
            font-weight: bold;
            color: #111111;
            border-top: 1.5px solid #222222;
        }

        .text-right { text-align: right !important; }
        .text-left { text-align: left !important; }
        .ltr { direction: ltr; display: inline-block; }
        .bold { font-weight: bold; }
        /* Flat monochrome-ink emphasis classes (kept for semantic compatibility without bright color distractions) */
        .text-indigo { color: #111111; }
        .text-emerald { color: #111111; }
        .text-rose { color: #222222; font-weight: bold; }
        .text-amber { color: #333333; font-weight: bold; }

        .notes-box {
            border: 1px solid #cfcfcf;
            background: #fafafa;
            border-radius: 0;
            padding: 7px 10px;
            font-size: 9px;
            color: #222222;
            margin-bottom: 14px;
        }

        /* Flat Signatures Grid */
        .signatures-grid {
            width: 100%;
            display: table;
            table-layout: fixed;
            border-collapse: collapse;
            margin-top: 20px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .sig-box {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 9px 8px 44px 8px;
            border: 1px solid #cfcfcf;
            background: #ffffff;
            font-size: 9px;
            color: #333333;
        }

        .sig-title {
            font-weight: bold;
            color: #111111;
            margin-bottom: 4px;
        }

        .pdf-footer-note {
            margin-top: 14px;
            padding-top: 6px;
            border-top: 1px solid #cfcfcf;
            font-size: 8.2px;
            color: #555555;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="pdf-header">
        <div class="pdf-header-right">
            <div class="brand-row">
                @if(!empty($logoDataUri))
                    <img src="{{ $logoDataUri }}" alt="{{ $siteName ?? '' }}" class="brand-logo">
                @endif
                <div>
                    <div class="org-name">{{ !empty($siteName) ? ($siteName . ' — واحد انبارداری') : 'واحد انبارداری' }}</div>
                    <h1 class="doc-title">{{ $docTitle ?? 'گزارش رسمی' }}</h1>
                    @if(!empty($docSubtitle))
                        <div class="doc-subtitle">{{ $docSubtitle }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="pdf-header-left">
            @if(!empty($docCode))
                <div class="meta-badge"><span class="ltr">{{ $docCode }}</span></div>
            @endif
            <div class="meta-line">تاریخ صدور گزارش: <span class="ltr">{{ $generatedAtJalali }}</span></div>
            <div class="meta-line">کاربر صادرکننده: {{ $generatedBy }}</div>
        </div>
    </div>

    @yield('pdf_content')

    <div class="pdf-footer-note">
        <span>{{ !empty($siteName) ? ($siteName . ' — مدیریت انبار و اموال') : 'مدیریت انبار و اموال' }}</span>
        <span>تاریخ و زمان چاپ: <span class="ltr">{{ $generatedAtJalali }}</span></span>
    </div>
</body>
</html>
