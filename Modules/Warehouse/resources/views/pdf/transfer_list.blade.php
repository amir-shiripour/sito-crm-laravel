@extends('warehouse::pdf.layout')

@php
    $docTitle = 'گزارش رسمی درخواست‌ها و حواله‌های انتقال بین انبارها';
    $docSubtitle = 'گزارش تجمیعی جابه‌جایی کالا بین انبار مرکزی و انبارک‌ها';
    $docCode = $metrics['total_count'] . ' سند';
@endphp

@section('pdf_content')
    <div class="info-grid">
        <div class="info-cell">
            <div class="info-label">تعداد کل اسناد</div>
            <div class="info-value">{{ number_format($metrics['total_count']) }}</div>
        </div>
        <div class="info-cell">
            <div class="info-label">در انتظار تایید/تحویل</div>
            <div class="info-value text-amber">{{ number_format($metrics['pending_count']) }}</div>
        </div>
        <div class="info-cell">
            <div class="info-label">جمع تعداد منتقل‌شده</div>
            <div class="info-value text-indigo">{{ number_format($metrics['delivered_units']) }} واحد</div>
        </div>
        <div class="info-cell">
            <div class="info-label">ارزش بهای تمام‌شده</div>
            <div class="info-value text-emerald">{{ number_format($metrics['delivered_cost_value']) }} ریال</div>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 12%;">کد حواله / درخواست</th>
                <th style="width: 12%;">تاریخ ثبت / نیاز</th>
                <th style="width: 20%;">مسیر انتقال</th>
                <th style="width: 12%;">درخواست‌کننده</th>
                <th style="width: 10%;">ثبت‌کننده</th>
                <th style="width: 9%;">درخواست / تحویل</th>
                <th style="width: 11%;">وضعیت</th>
                <th style="width: 10%;">بهای تمام‌شده</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transfers as $idx => $t)
                @php
                    $cDate = $t->created_at ? (function_exists('jdate') ? jdate($t->created_at)->format('Y/m/d') : $t->created_at->format('Y-m-d')) : '—';
                    $nDate = $t->needed_date ? (function_exists('jdate') ? jdate($t->needed_date)->format('Y/m/d') : $t->needed_date->format('Y-m-d')) : null;
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>
                        <div class="bold text-indigo"><span class="ltr">{{ $t->transfer_number }}</span></div>
                        <div style="font-size: 8px; color: #4b5563;"><span class="ltr">{{ $t->request_number }}</span></div>
                    </td>
                    <td>
                        <div>ثبت: <span class="ltr">{{ $cDate }}</span></div>
                        @if($nDate)
                            <div class="text-indigo">نیاز: <span class="ltr">{{ $nDate }}</span></div>
                        @endif
                    </td>
                    <td class="text-right">
                        {{ $t->sourceWarehouse?->name ?? '—' }} ← <strong>{{ $t->destinationWarehouse?->name ?? '—' }}</strong>
                    </td>
                    <td>{{ $t->effective_requester_name }}</td>
                    <td>{{ $t->creator_name }}</td>
                    <td>{{ number_format($t->total_requested_quantity) }} / <strong class="text-emerald">{{ number_format($t->total_delivered_quantity) }}</strong></td>
                    <td>{{ $t->status_label }}</td>
                    <td class="bold">{{ number_format($t->total_cost_value) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">هیچ حواله یا درخواستی یافت نشد.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
