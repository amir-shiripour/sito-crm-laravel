@extends('warehouse::pdf.layout')

@php
    $isAsset = ($type ?? 'inventory') === 'asset';
    $docTitle = $isAsset ? 'گزارش رسمی مدیریت اموال و مکان‌های استقرار' : 'گزارش رسمی انبار موجودی و انبارک‌های فعال';
    $docSubtitle = $isAsset ? 'فهرست واحدهای اموال و مسئولین مربوطه' : 'فهرست انبار مرکزی، انبارک‌های فرعی و خلاصه موجودی';
    $docCode = $warehouses->count() . ' واحد';
@endphp

@section('pdf_content')
    <div class="info-grid">
        <div class="info-cell">
            <div class="info-label">تعداد کل انبارها / انبارک‌ها</div>
            <div class="info-value">{{ number_format($warehouses->count()) }}</div>
        </div>
        <div class="info-cell">
            <div class="info-label">انبار مرکزی</div>
            <div class="info-value text-indigo">{{ $mainWarehouse?->name ?? 'تعریف‌نشده' }}</div>
        </div>
        <div class="info-cell">
            <div class="info-label">کاربر ثبت‌کننده اصلی</div>
            <div class="info-value">{{ $mainWarehouse?->creator_name ?? '—' }}</div>
        </div>
        <div class="info-cell">
            <div class="info-label">نوع انبار</div>
            <div class="info-value">{{ $isAsset ? 'اموال سازمانی' : 'موجودی کالا' }}</div>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 13%;">کد انبار</th>
                <th style="width: 18%;">نام انبار / انبارک</th>
                <th style="width: 11%;">سطح</th>
                <th style="width: 17%;">مسئولین</th>
                <th style="width: 14%;">موقعیت</th>
                <th style="width: 11%;">ثبت‌کننده</th>
                <th style="width: 11%;">جمع موجودی / وضعیت</th>
            </tr>
        </thead>
        <tbody>
            @forelse($warehouses as $idx => $w)
                @php
                    $visibleMgrs = \Modules\Warehouse\Support\WarehouseUserVisibility::filterVisibleManagers($w->managers ?? collect([]));
                    $mgrNames = $visibleMgrs->isNotEmpty()
                        ? $visibleMgrs->pluck('name')->join('، ')
                        : ($w->managers->isEmpty() && $w->manager ? $w->manager : '—');
                    $stockUnits = $stockTotals[$w->id] ?? 0;
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="bold text-indigo"><span class="ltr">{{ $w->code }}</span></td>
                    <td class="text-right bold">{{ $w->name }}</td>
                    <td>{{ $w->is_main ? 'انبار مرکزی' : 'انبارک فرعی' }}</td>
                    <td class="text-right">{{ $mgrNames }}</td>
                    <td class="text-right">{{ $w->location ?: '—' }}</td>
                    <td>{{ $w->creator_name }}</td>
                    <td class="bold">
                        @if($isAsset)
                            {{ $w->is_active ? 'فعال' : 'غیرفعال' }}
                        @else
                            {{ number_format($stockUnits) }} واحد
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">هیچ انباری ثبت نشده است.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
