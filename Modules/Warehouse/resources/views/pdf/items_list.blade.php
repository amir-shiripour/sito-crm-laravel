@extends('warehouse::pdf.layout')

@php
    $docTitle = 'گزارش جامع موجودی و ارزش‌گذاری کالاهای انبار';
    $docSubtitle = 'فهرست کالاها، موجودی کل، بهای تمام‌شده، نرخ روز و کاربر ثبت‌کننده';
    $docCode = $metrics['total_items_count'] . ' قلم کالا';
@endphp

@section('pdf_content')
    <div class="info-grid">
        <div class="info-cell">
            <div class="info-label">تنوع اقلام</div>
            <div class="info-value">{{ number_format($metrics['total_items_count']) }} قلم</div>
        </div>
        <div class="info-cell">
            <div class="info-label">جمع کل واحدهای موجودی</div>
            <div class="info-value text-indigo">{{ number_format($metrics['total_units_in_stock']) }} واحد</div>
        </div>
        <div class="info-cell">
            <div class="info-label">ارزش بهای تمام‌شده خرید</div>
            <div class="info-value">{{ number_format($metrics['total_cost_value']) }} ریال</div>
        </div>
        <div class="info-cell">
            <div class="info-label">ارزش به نرخ روز</div>
            <div class="info-value text-emerald">{{ number_format($metrics['total_market_value']) }} ریال</div>
        </div>
        <div class="info-cell">
            <div class="info-label">سود تورمی برآوردی</div>
            <div class="info-value {{ $metrics['unrealized_profit'] >= 0 ? 'text-emerald' : 'text-rose' }}">
                {{ ($metrics['unrealized_profit'] >= 0 ? '+' : '') . number_format($metrics['unrealized_profit']) }} ریال
            </div>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 14%;">کد کالا (SKU)</th>
                <th style="width: 22%;">نام کالا</th>
                <th style="width: 12%;">دسته / زیردسته</th>
                <th style="width: 9%;">ثبت‌کننده</th>
                <th style="width: 9%;">موجودی</th>
                <th style="width: 10%;">نرخ روز (ریال)</th>
                <th style="width: 11%;">ارزش خرید (ریال)</th>
                <th style="width: 9%;">وضعیت</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="bold text-indigo"><span class="ltr">{{ $item->code }}</span></td>
                    <td class="text-right bold">{{ $item->name }}</td>
                    <td>{{ $item->category?->name ?? '—' }} @if($item->subcategory) / {{ $item->subcategory->name }} @endif</td>
                    <td>{{ $item->creator_name }}</td>
                    <td class="bold">{{ number_format($item->total_stock) }} {{ $item->unit }}</td>
                    <td>{{ $item->current_price ? number_format($item->current_price) : '—' }}</td>
                    <td class="bold">{{ number_format($item->total_cost_value) }}</td>
                    <td>
                        @if($item->is_below_min_stock)
                            <span class="text-rose bold">کسری بحرانی</span>
                        @elseif($item->is_below_reorder_point)
                            <span class="text-amber bold">نیاز به سفارش</span>
                        @else
                            <span class="text-emerald bold">مطلوب</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">هیچ کالایی مطابق فیلترها یافت نشد.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
