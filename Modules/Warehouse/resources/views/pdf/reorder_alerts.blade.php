@extends('warehouse::pdf.layout')

@php
    $scopeLabels = [
        'alerts_only' => 'همه هشدارهای نیازمند اقدام',
        'critical' => 'اقلام بحرانی و ناموجود',
        'reorder' => 'اقلام نیازمند سفارش کلینیک',
        'central_low' => 'اقلام دارای کسری انبار مرکزی',
        'expiring' => 'اقلام دارای هشدار انقضا',
        'adequate' => 'اقلام با موجودی کافی',
        'unconfigured' => 'اقلام بدون نقطه سفارش',
        'all' => 'تمامی کالاهای فعال انبار',
    ];
    $docTitle = 'گزارش رسمی هشدارهای موجودی و نقطه سفارش';
    $docSubtitle = 'دامنه گزارش: ' . ($scopeLabels[$scope] ?? 'هشدارهای سفارش')
        . ($mainWarehouse ? (' — مبنای انبار مرکزی: ' . $mainWarehouse->name) : '')
        . ($search ? (' — جستجو: ' . $search) : '');
    $docCode = $rows->count() . ' ردیف';
@endphp

@section('pdf_content')
    <div class="info-grid">
        <div class="info-cell">
            <div class="info-label">اقلام بحرانی / ناموجود</div>
            <div class="info-value text-rose">{{ number_format($summary['critical_count']) }} قلم</div>
        </div>
        <div class="info-cell">
            <div class="info-label">نیازمند سفارش کلینیک</div>
            <div class="info-value text-amber">{{ number_format($summary['reorder_count']) }} قلم</div>
        </div>
        <div class="info-cell">
            <div class="info-label">کسری انبار مرکزی</div>
            <div class="info-value text-indigo">{{ number_format($summary['central_low_count']) }} قلم</div>
        </div>
        <div class="info-cell">
            <div class="info-label">مجموع کسری در این گزارش</div>
            <div class="info-value">{{ rtrim(rtrim(number_format((float)$summary['filtered_deficit_units'], 2), '0'), '.') }} واحد</div>
        </div>
        <div class="info-cell">
            <div class="info-label">برآورد بودجه رفع کسری</div>
            <div class="info-value text-emerald">{{ number_format($summary['filtered_estimated_budget']) }} ریال</div>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 11%;">موجودی کل کلینیک</th>
                <th style="width: 13%;">کد کالا</th>
                <th style="width: 24%;">نام کالا</th>
                <th style="width: 11%;">نقطه سفارش (حداقل)</th>
                <th style="width: 10%;">موجودی مرکزی</th>
                <th style="width: 10%;">کسری تا نقطه سفارش</th>
                <th style="width: 10%;">برآورد مبلغ (ریال)</th>
                <th style="width: 12%;">وضعیت</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $idx => $row)
                @php
                    $item = $row['item'];
                    $fmt = fn($val) => rtrim(rtrim(number_format((float)$val, 2), '0'), '.') ?: '0';
                    $statusText = match ($row['alert_status']) {
                        'critical' => $row['clinic_stock'] <= 0 ? 'ناموجود / بحرانی' : 'بحرانی (زیر حداقل)',
                        'reorder' => 'نیازمند سفارش',
                        'central_low' => 'کسری انبار مرکزی',
                        'adequate' => 'موجودی کافی',
                        default => 'تنظیم‌نشده',
                    };
                    $statusClass = match ($row['alert_status']) {
                        'critical' => 'text-rose',
                        'reorder' => 'text-amber',
                        'central_low' => 'text-indigo',
                        'adequate' => 'text-emerald',
                        default => '',
                    };
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="bold">{{ $fmt($row['clinic_stock']) }} {{ $item->unit }}</td>
                    <td class="bold text-indigo"><span class="ltr">{{ $item->code }}</span></td>
                    <td class="text-right bold">{{ $item->name }}</td>
                    <td>{{ $fmt($row['reorder_point']) }} ({{ $fmt($row['min_stock']) }})</td>
                    <td class="bold">{{ $fmt($row['central_stock']) }}</td>
                    <td class="bold {{ $row['suggested_order_qty'] > 0 ? 'text-rose' : '' }}">
                        {{ $row['suggested_order_qty'] > 0 ? $fmt($row['suggested_order_qty']) : '۰' }}
                    </td>
                    <td>{{ $row['estimated_order_cost'] > 0 ? number_format($row['estimated_order_cost']) : '—' }}</td>
                    <td class="bold {{ $statusClass }}">{{ $statusText }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">هیچ کالایی در این دامنه گزارش یافت نشد.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signatures-grid">
        <div class="sig-box">
            <div class="sig-title">تهیه و کنترل انبار مرکزی</div>
            <div>{{ $generatedBy }}</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">بررسی واحد خرید و تدارکات</div>
            <div>مهر و امضا</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">تایید مدیریت کلینیک</div>
            <div>مهر و امضا</div>
        </div>
    </div>
@endsection
