@extends('warehouse::pdf.layout')

@php
    $docTitle = 'سند رسمی خروج و مصرف کالا';
    $docSubtitle = $consumption->issue_type_label
        . ($consumption->effective_patient_name ? (' — بیمار: ' . $consumption->effective_patient_name) : '')
        . ($consumption->effective_case_number ? (' (پرونده: ' . $consumption->effective_case_number . ')') : '');
    $docCode = $consumption->issue_number;
    $issueJalali = $consumption->issue_date ? (function_exists('jdate') ? jdate($consumption->issue_date)->format('Y/m/d') : $consumption->issue_date->format('Y-m-d')) : '—';
    $createdJalali = $consumption->created_at ? (function_exists('jdate') ? jdate($consumption->created_at)->format('Y/m/d H:i') : $consumption->created_at->format('Y-m-d H:i')) : '—';
@endphp

@section('pdf_content')
    <div class="info-grid">
        <div class="info-cell">
            <div class="info-label">تاریخ مصرف / خروج</div>
            <div class="info-value"><span class="ltr">{{ $issueJalali }}</span></div>
        </div>
        <div class="info-cell">
            <div class="info-label">انبار / انبارک مبدأ</div>
            <div class="info-value">{{ $consumption->sourceWarehouse?->name ?? '—' }}</div>
        </div>
        <div class="info-cell">
            <div class="info-label">بیمار / شماره پرونده</div>
            <div class="info-value">
                {{ $consumption->effective_patient_name ?: ($consumption->effective_consumer_name ?: 'مصرف عمومی بخش') }}
                @if($consumption->effective_case_number)
                    <br><span style="font-size: 9px; font-weight: normal;">پرونده: {{ $consumption->effective_case_number }}</span>
                @endif
            </div>
        </div>
        <div class="info-cell">
            <div class="info-label">مجموع تعداد و بهای تمام‌شده</div>
            <div class="info-value text-emerald">
                {{ rtrim(rtrim(number_format((float)$consumption->total_quantity, 2), '0'), '.') }} واحد — {{ number_format((float)$consumption->total_cost_value) }} ریال
            </div>
        </div>
        <div class="info-cell">
            <div class="info-label">ثبت‌کننده / وضعیت</div>
            <div class="info-value">
                {{ $consumption->creator_name }}<br>
                <span style="font-size: 8.5px; font-weight: normal;">{{ $consumption->status_label }} · <span class="ltr">{{ $createdJalali }}</span></span>
            </div>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 14%;">کد کالا</th>
                <th style="width: 24%;">نام کالا</th>
                <th style="width: 15%;">انبار / انبارک مبدأ</th>
                <th style="width: 10%;">تعداد</th>
                <th style="width: 19%;">پارت‌های کسرشده</th>
                <th style="width: 13%;">بهای تمام‌شده (ریال)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($consumption->items as $idx => $line)
                @php
                    $batchText = $line->batchDeductions->map(function ($bd) {
                        $code = $bd->batch?->batch_code ?? ('#' . $bd->batch_id);
                        return $code . ' (' . rtrim(rtrim(number_format((float)$bd->quantity_deducted, 2), '0'), '.') . ')';
                    })->join('، ');
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="bold text-indigo"><span class="ltr">{{ $line->item?->code }}</span></td>
                    <td class="text-right bold">{{ $line->item?->name }}</td>
                    <td>{{ $line->warehouse?->name ?? '—' }}</td>
                    <td class="bold">{{ rtrim(rtrim(number_format((float)$line->quantity, 2), '0'), '.') }} {{ $line->item?->unit }}</td>
                    <td style="font-size: 8.5px;">{{ $batchText ?: '—' }}</td>
                    <td class="bold text-emerald">{{ number_format((float)$line->total_cost) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($consumption->notes)
        <div class="notes-box">
            <strong>توضیحات:</strong> {{ $consumption->notes }}
        </div>
    @endif

    <div class="signatures-grid">
        <div class="sig-box">
            <div class="sig-title">تحویل‌دهنده (انباردار)</div>
            <div>{{ $consumption->creator_name }}</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">تحویل‌گیرنده / پزشک بخش</div>
            <div>{{ $consumption->effective_consumer_name ?: '........................' }}</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">تایید مدیریت کلینیک</div>
            <div>مهر و امضا</div>
        </div>
    </div>
@endsection
