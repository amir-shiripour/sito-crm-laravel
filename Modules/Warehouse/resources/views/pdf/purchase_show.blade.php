@extends('warehouse::pdf.layout')

@php
    $docTitle = 'رسید رسمی خرید و ورود کالا به انبار';
    $docSubtitle = $purchase->status === 'cancelled' ? 'وضعیت سند: ابطال‌شده' : 'وضعیت سند: ثبت‌شده در انبار';
    $docCode = $purchase->receipt_number;
    $jalaliPurchaseDate = $purchase->purchase_date
        ? (function_exists('jdate') ? jdate($purchase->purchase_date)->format('Y/m/d') : $purchase->purchase_date->format('Y-m-d'))
        : '—';
    $jalaliCreatedAt = $purchase->created_at
        ? (function_exists('jdate') ? jdate($purchase->created_at)->format('Y/m/d H:i') : $purchase->created_at->format('Y-m-d H:i'))
        : '—';
@endphp

@section('pdf_content')
    <div class="info-grid">
        <div class="info-cell">
            <div class="info-label">شماره فاکتور فروشنده</div>
            <div class="info-value"><span class="ltr">{{ $purchase->invoice_number ?: '—' }}</span></div>
        </div>
        <div class="info-cell">
            <div class="info-label">تاریخ خرید (فاکتور)</div>
            <div class="info-value"><span class="ltr">{{ $jalaliPurchaseDate }}</span></div>
        </div>
        <div class="info-cell">
            <div class="info-label">تاریخ ثبت در سیستم</div>
            <div class="info-value"><span class="ltr">{{ $jalaliCreatedAt }}</span></div>
        </div>
        <div class="info-cell">
            <div class="info-label">تأمین‌کننده / فروشنده</div>
            <div class="info-value">{{ $purchase->supplier_name ?: '—' }}</div>
        </div>
        <div class="info-cell">
            <div class="info-label">کاربر ثبت‌کننده</div>
            <div class="info-value">{{ $purchase->creator_name }}</div>
        </div>
    </div>

    @if($purchase->notes)
        <div class="notes-box">
            <strong>توضیحات رسید:</strong> {{ $purchase->notes }}
        </div>
    @endif

    <div class="section-heading">شرح اقلام وارده به انبار ({{ $purchase->items->count() }} قلم)</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 15%;">کد کالا</th>
                <th style="width: 24%;">نام کالا</th>
                <th style="width: 13%;">انبار مقصد</th>
                <th style="width: 9%;">تعداد</th>
                <th style="width: 11%;">فی خرید (ریال)</th>
                <th style="width: 12%;">جمع کل (ریال)</th>
                <th style="width: 12%;">انقضا / توضیحات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->items as $idx => $line)
                @php
                    $expJalali = $line->expiry_date
                        ? (function_exists('jdate') ? jdate($line->expiry_date)->format('Y/m/d') : $line->expiry_date->format('Y-m-d'))
                        : null;
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="bold text-indigo"><span class="ltr">{{ $line->item?->code ?? '—' }}</span></td>
                    <td class="text-right bold">{{ $line->item?->name ?? 'کالای حذف‌شده' }}</td>
                    <td>{{ $line->warehouse?->name ?? '—' }}</td>
                    <td class="bold">{{ number_format($line->quantity) }} {{ $line->item?->unit }}</td>
                    <td>{{ number_format($line->unit_price) }}</td>
                    <td class="bold text-indigo">{{ number_format($line->total_price) }}</td>
                    <td class="text-right">
                        @if($expJalali)
                            انقضا: <span class="ltr">{{ $expJalali }}</span>
                            @if($line->notes) · @endif
                        @endif
                        {{ $line->notes ?: ($expJalali ? '' : '—') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right">جمع کل رسید خرید</td>
                <td>{{ number_format($purchase->total_quantity) }}</td>
                <td>—</td>
                <td class="text-emerald">{{ number_format($purchase->total_amount) }} ریال</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="signatures-grid">
        <div class="sig-box">
            <div class="sig-title">تحویل‌دهنده / تأمین‌کننده</div>
            <div>{{ $purchase->supplier_name ?: '........................' }}</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">ثبت‌کننده و تحویل‌گیرنده انبار</div>
            <div>{{ $purchase->creator_name }}</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">تایید حسابداری / مدیریت</div>
            <div>مهر و امضا</div>
        </div>
    </div>
@endsection
