@extends('warehouse::pdf.layout')

@php
    $docTitle = 'گزارش رسمی رسیدهای خرید و ورود کالا';
    $docSubtitle = 'فهرست اسناد ورود خرید انبار بر اساس فیلترهای انتخابی';
    $docCode = $metrics['receipts_count'] . ' سند';
@endphp

@section('pdf_content')
    <div class="info-grid">
        <div class="info-cell">
            <div class="info-label">تعداد رسیدهای ثبت‌شده</div>
            <div class="info-value">{{ number_format($metrics['receipts_count']) }} فاکتور</div>
        </div>
        <div class="info-cell">
            <div class="info-label">جمع تعداد اقلام وارده</div>
            <div class="info-value">{{ number_format($metrics['total_quantity']) }} واحد</div>
        </div>
        <div class="info-cell">
            <div class="info-label">مجموع ریالی خریدها</div>
            <div class="info-value text-emerald">{{ number_format($metrics['total_amount']) }} ریال</div>
        </div>
        <div class="info-cell">
            <div class="info-label">دامنه جستجو / فیلتر</div>
            <div class="info-value">
                @if(!empty($dateFrom) || !empty($dateTo))
                    از {{ $dateFrom ?: 'ابتدا' }} تا {{ $dateTo ?: 'امروز' }}
                @else
                    همه دوره‌ها
                @endif
            </div>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 14%;">شماره رسید</th>
                <th style="width: 12%;">شماره فاکتور</th>
                <th style="width: 11%;">تاریخ خرید</th>
                <th style="width: 13%;">تاریخ ثبت</th>
                <th style="width: 15%;">تأمین‌کننده</th>
                <th style="width: 10%;">ثبت‌کننده</th>
                <th style="width: 8%;">تعداد</th>
                <th style="width: 12%;">مبلغ کل (ریال)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($receipts as $idx => $r)
                @php
                    $pDate = $r->purchase_date ? (function_exists('jdate') ? jdate($r->purchase_date)->format('Y/m/d') : $r->purchase_date->format('Y-m-d')) : '—';
                    $cDate = $r->created_at ? (function_exists('jdate') ? jdate($r->created_at)->format('Y/m/d H:i') : $r->created_at->format('Y-m-d H:i')) : '—';
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="bold text-indigo"><span class="ltr">{{ $r->receipt_number }}</span></td>
                    <td><span class="ltr">{{ $r->invoice_number ?: '—' }}</span></td>
                    <td><span class="ltr">{{ $pDate }}</span></td>
                    <td><span class="ltr">{{ $cDate }}</span></td>
                    <td class="text-right">{{ $r->supplier_name ?: '—' }}</td>
                    <td>{{ $r->creator_name }}</td>
                    <td class="bold">{{ number_format($r->total_quantity) }}</td>
                    <td class="bold text-emerald">{{ number_format($r->total_amount) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">هیچ رسید خریدی یافت نشد.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
