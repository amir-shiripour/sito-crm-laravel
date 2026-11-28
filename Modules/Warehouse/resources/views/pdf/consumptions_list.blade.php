@extends('warehouse::pdf.layout')

@php
    $docTitle = 'گزارش رسمی اسناد خروج و مصرف کالا';
    $docSubtitle = 'خلاصه عملیات خروج از انبار مرکزی و انبارک‌ها به‌تفکیک بیمار، پرونده و بهای تمام‌شده';
    $docCode = $documents->count() . ' سند';
    $totalUnits = $documents->where('status', 'registered')->sum('total_quantity');
    $totalCost = $documents->where('status', 'registered')->sum('total_cost_value');
@endphp

@section('pdf_content')
    <div class="info-grid">
        <div class="info-cell">
            <div class="info-label">تعداد کل اسناد گزارش</div>
            <div class="info-value">{{ number_format($documents->count()) }} سند</div>
        </div>
        <div class="info-cell">
            <div class="info-label">اسناد فعال / ابطال‌شده</div>
            <div class="info-value">{{ number_format($documents->where('status', 'registered')->count()) }} فعال · {{ number_format($documents->where('status', 'cancelled')->count()) }} ابطال</div>
        </div>
        <div class="info-cell">
            <div class="info-label">مجموع تعداد مصرفی</div>
            <div class="info-value text-indigo">{{ rtrim(rtrim(number_format((float)$totalUnits, 2), '0'), '.') }} واحد</div>
        </div>
        <div class="info-cell">
            <div class="info-label">بهای تمام‌شده کل مصرف</div>
            <div class="info-value text-emerald">{{ number_format((float)$totalCost) }} ریال</div>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 11%;">شماره سند</th>
                <th style="width: 9%;">تاریخ مصرف</th>
                <th style="width: 12%;">نوع خروج</th>
                <th style="width: 16%;">بیمار / پرونده / مصرف‌کننده</th>
                <th style="width: 13%;">انبار / انبارک مبدأ</th>
                <th style="width: 8%;">تعداد</th>
                <th style="width: 11%;">بهای کل (ریال)</th>
                <th style="width: 9%;">ثبت‌کننده</th>
                <th style="width: 7%;">وضعیت</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $idx => $doc)
                @php
                    $issueJ = $doc->issue_date ? (function_exists('jdate') ? jdate($doc->issue_date)->format('Y/m/d') : $doc->issue_date->format('Y-m-d')) : '—';
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="bold text-indigo"><span class="ltr">{{ $doc->issue_number }}</span></td>
                    <td><span class="ltr">{{ $issueJ }}</span></td>
                    <td>{{ $doc->issue_type_label }}</td>
                    <td class="text-right">
                        {{ $doc->effective_patient_name ?: ($doc->effective_consumer_name ?: 'مصرف بخش') }}
                        @if($doc->effective_case_number)
                            ({{ $doc->effective_case_number }})
                        @endif
                    </td>
                    <td>{{ $doc->sourceWarehouse?->name ?? '—' }}</td>
                    <td class="bold">{{ rtrim(rtrim(number_format((float)$doc->total_quantity, 2), '0'), '.') }}</td>
                    <td class="bold text-emerald">{{ number_format((float)$doc->total_cost_value) }}</td>
                    <td>{{ $doc->creator_name }}</td>
                    <td class="{{ $doc->status === 'cancelled' ? 'text-rose' : 'text-emerald' }}">{{ $doc->status === 'cancelled' ? 'ابطال' : 'ثبت‌شده' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">هیچ سند خروج و مصرفی یافت نشد.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signatures-grid">
        <div class="sig-box">
            <div class="sig-title">مسئول انبار و توزیع</div>
            <div>{{ $generatedBy }}</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">حسابداری صنعتی / بهای تمام‌شده</div>
            <div>مهر و امضا</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">تایید مدیریت کلینیک</div>
            <div>مهر و امضا</div>
        </div>
    </div>
@endsection
