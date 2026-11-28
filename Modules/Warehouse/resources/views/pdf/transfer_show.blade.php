@extends('warehouse::pdf.layout')

@php
    $docTitle = 'حواله رسمی درخواست و انتقال کالا بین انبارها';
    $docSubtitle = 'وضعیت سند: ' . $transfer->status_label;
    $docCode = $transfer->transfer_number;
    $jalaliCreated = $transfer->created_at && (!$transfer->request_date || $transfer->request_date->toDateString() === $transfer->created_at->toDateString())
        ? (function_exists('jdate') ? jdate($transfer->created_at)->format('Y/m/d H:i') : $transfer->created_at->format('Y-m-d H:i'))
        : ($transfer->request_date ? (function_exists('jdate') ? jdate($transfer->request_date)->format('Y/m/d') : $transfer->request_date->format('Y-m-d')) : '—');
    $jalaliNeeded = $transfer->needed_date
        ? (function_exists('jdate') ? jdate($transfer->needed_date)->format('Y/m/d') : $transfer->needed_date->format('Y-m-d'))
        : '—';
    $jalaliDelivered = $transfer->delivered_at
        ? (function_exists('jdate') ? jdate($transfer->delivered_at)->format('Y/m/d H:i') : $transfer->delivered_at->format('Y-m-d H:i'))
        : '—';
@endphp

@section('pdf_content')
    <div class="info-grid">
        <div class="info-cell">
            <div class="info-label">شماره درخواست سیستمی</div>
            <div class="info-value"><span class="ltr">{{ $transfer->request_number ?: '—' }}</span></div>
        </div>
        <div class="info-cell">
            <div class="info-label">تاریخ ثبت / تاریخ نیاز</div>
            <div class="info-value">
                ثبت: <span class="ltr">{{ $jalaliCreated }}</span>
                @if($jalaliNeeded !== '—')
                    <br>نیاز: <span class="ltr text-indigo">{{ $jalaliNeeded }}</span>
                @endif
            </div>
        </div>
        <div class="info-cell">
            <div class="info-label">مسیر انتقال</div>
            <div class="info-value">
                {{ $transfer->sourceWarehouse?->name ?? 'مبدأ' }} ← {{ $transfer->destinationWarehouse?->name ?? 'مقصد' }}
            </div>
        </div>
        <div class="info-cell">
            <div class="info-label">درخواست‌کننده</div>
            <div class="info-value">{{ $transfer->effective_requester_name }}</div>
        </div>
        <div class="info-cell">
            <div class="info-label">ثبت‌کننده / تحویل‌دهنده</div>
            <div class="info-value">
                ثبت: {{ $transfer->creator_name }}
                @if($transfer->approver)
                    <br>تحویل: {{ $transfer->effective_approver_name }} (<span class="ltr">{{ $jalaliDelivered }}</span>)
                @endif
            </div>
        </div>
    </div>

    @if($transfer->notes)
        <div class="notes-box">
            <strong>توضیحات حواله:</strong> {{ $transfer->notes }}
        </div>
    @endif

    <div class="section-heading">اقلام درخواست و انتقال ({{ $transfer->items->count() }} قلم)</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 18%;">کد کالا</th>
                <th style="width: 31%;">نام کالا</th>
                <th style="width: 11%;">تعداد درخواستی</th>
                <th style="width: 11%;">تعداد تحویلی</th>
                <th style="width: 12%;">بهای تمام‌شده (ریال)</th>
                <th style="width: 12%;">توضیحات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transfer->items as $idx => $line)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="bold text-indigo"><span class="ltr">{{ $line->item?->code ?? '—' }}</span></td>
                    <td class="text-right bold">{{ $line->item?->name ?? 'کالای حذف‌شده' }}</td>
                    <td>{{ number_format($line->requested_quantity) }} {{ $line->item?->unit }}</td>
                    <td class="bold text-emerald">{{ number_format($line->delivered_quantity) }} {{ $line->item?->unit }}</td>
                    <td class="bold">{{ number_format($line->total_cost_value) }}</td>
                    <td class="text-right">{{ $line->notes ?: '—' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">جمع کل حواله</td>
                <td>{{ number_format($transfer->total_requested_quantity) }}</td>
                <td class="text-emerald">{{ number_format($transfer->total_delivered_quantity) }}</td>
                <td class="text-indigo">{{ number_format($transfer->total_cost_value) }} ریال</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    @if($transfer->transferredBatches->isNotEmpty())
        <div class="section-heading">پارت‌های تخصیص‌یافته به این انتقال</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>کد پارت / فاکتور</th>
                    <th>نام کالا</th>
                    <th>تعداد منتقل‌شده</th>
                    <th>فی خرید واحد (ریال)</th>
                    <th>ارزش پارت (ریال)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transfer->transferredBatches as $tBatch)
                    <tr>
                        <td class="bold text-indigo"><span class="ltr">{{ $tBatch->batch_code }}</span></td>
                        <td class="text-right">{{ $tBatch->item?->name ?? '—' }}</td>
                        <td>{{ number_format($tBatch->initial_quantity) }}</td>
                        <td>{{ number_format($tBatch->unit_purchase_price) }}</td>
                        <td class="bold">{{ number_format($tBatch->initial_quantity * $tBatch->unit_purchase_price) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="signatures-grid">
        <div class="sig-box">
            <div class="sig-title">درخواست‌کننده</div>
            <div>{{ $transfer->effective_requester_name !== '—' ? $transfer->effective_requester_name : $transfer->creator_name }}</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">تحویل‌دهنده ({{ $transfer->sourceWarehouse?->name ?? 'انبار مبدأ' }})</div>
            <div>{{ $transfer->approver ? $transfer->effective_approver_name : $transfer->creator_name }}</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">تحویل‌گیرنده ({{ $transfer->destinationWarehouse?->name ?? 'انبارک مقصد' }})</div>
            <div>امضا و تاریخ تحویل</div>
        </div>
    </div>
@endsection
