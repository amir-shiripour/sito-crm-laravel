@extends('warehouse::pdf.layout')

@php
    $docTitle = $selectedWarehouse
        ? ('گزارش رسمی موجودی لحظه‌ای ' . $selectedWarehouse->name)
        : 'گزارش رسمی موجودی لحظه‌ای کل انبارها و انبارک‌ها';
    $visibleManagersText = '';
    if ($selectedWarehouse) {
        $visMgrs = \Modules\Warehouse\Support\WarehouseUserVisibility::filterVisibleManagers($selectedWarehouse->managers ?? collect([]));
        if ($visMgrs->isNotEmpty()) {
            $visibleManagersText = $visMgrs->pluck('name')->join('، ');
        } elseif ($selectedWarehouse->managers->isEmpty() && $selectedWarehouse->manager) {
            $visibleManagersText = $selectedWarehouse->manager;
        }
    }
    $docSubtitle = $selectedWarehouse
        ? ($selectedWarehouse->is_main ? 'انبار مرکزی موجودی' : 'انبارک فرعی') . ($visibleManagersText !== '' ? (' — مسئول: ' . $visibleManagersText) : '')
        : 'نمای تجمیعی و تفکیک موجودی کالا در انبار مرکزی و تمامی انبارک‌ها';
    $docCode = $selectedWarehouse ? ($selectedWarehouse->code ?: ('WH-' . $selectedWarehouse->id)) : ($warehouses->count() . ' انبار فعال');
@endphp

@section('pdf_content')
    <div class="info-grid">
        <div class="info-cell">
            <div class="info-label">تنوع اقلام دارای موجودی</div>
            <div class="info-value">{{ number_format($metrics['in_stock_items_count']) }} قلم</div>
        </div>
        <div class="info-cell">
            <div class="info-label">مجموع تعداد موجودی فیزیکی</div>
            <div class="info-value text-indigo">{{ number_format($metrics['total_units_in_stock']) }} واحد</div>
        </div>
        <div class="info-cell">
            <div class="info-label">بهای تمام‌شده پارت‌ها</div>
            <div class="info-value">{{ number_format($metrics['total_cost_value']) }} ریال</div>
        </div>
        <div class="info-cell">
            <div class="info-label">ارزش به نرخ روز</div>
            <div class="info-value text-emerald">{{ number_format($metrics['total_market_value']) }} ریال</div>
        </div>
        <div class="info-cell">
            <div class="info-label">محدوده گزارش</div>
            <div class="info-value">{{ $selectedWarehouse ? $selectedWarehouse->name : 'کل انبارهای موجودی' }}</div>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 14%;">کد کالا</th>
                <th style="width: {{ $selectedWarehouse ? '25%' : '21%' }};">نام کالا</th>
                <th style="width: 12%;">دسته / زیردسته</th>
                @if(!$selectedWarehouse)
                    <th style="width: 18%;">تفکیک موجودی در انبارها</th>
                @endif
                <th style="width: 10%;">تعداد موجودی</th>
                <th style="width: 12%;">ارزش بهای خرید</th>
                <th style="width: 11%;">ارزش روز</th>
                <th style="width: 10%;">نزدیک‌ترین انقضا</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $idx => $item)
                @php
                    $scopedBatches = $item->activeBatches;
                    $scopedQty = (float)$scopedBatches->sum('remaining_quantity');
                    $scopedCost = (float)$scopedBatches->sum(fn($b) => (float)$b->remaining_quantity * (float)$b->unit_purchase_price);
                    $scopedMarket = (float)($item->current_price ?? 0) > 0 ? ($scopedQty * (float)$item->current_price) : $scopedCost;
                    $nearestExpiry = $scopedBatches->whereNotNull('expiry_date')->sortBy('expiry_date')->first()?->expiry_date;
                    $expJalali = $nearestExpiry
                        ? (function_exists('jdate') ? jdate($nearestExpiry)->format('Y/m/d') : $nearestExpiry->format('Y-m-d'))
                        : '—';
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="bold text-indigo"><span class="ltr">{{ $item->code }}</span></td>
                    <td class="text-right bold">{{ $item->name }}</td>
                    <td>{{ $item->category?->name ?? '—' }} @if($item->subcategory) / {{ $item->subcategory->name }} @endif</td>
                    @if(!$selectedWarehouse)
                        <td class="text-right" style="font-size: 8.5px;">
                            @php
                                $parts = [];
                                foreach ($scopedBatches->groupBy('warehouse_id') as $group) {
                                    $whName = $group->first()?->warehouse?->name;
                                    $sum = (float)$group->sum('remaining_quantity');
                                    if ($whName && $sum > 0) {
                                        $parts[] = e($whName) . ': ' . number_format($sum);
                                    }
                                }
                            @endphp
                            {!! !empty($parts) ? implode(' <br> ', $parts) : '—' !!}
                        </td>
                    @endif
                    <td class="bold text-indigo">{{ number_format($scopedQty) }} {{ $item->unit }}</td>
                    <td>{{ number_format($scopedCost) }}</td>
                    <td class="bold text-emerald">{{ number_format($scopedMarket) }}</td>
                    <td><span class="ltr">{{ $expJalali }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $selectedWarehouse ? 8 : 9 }}">هیچ کالایی با شرایط انتخاب‌شده یافت نشد.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signatures-grid">
        <div class="sig-box">
            <div class="sig-title">{{ $selectedWarehouse ? ('مسئول ' . $selectedWarehouse->name) : 'مسئول انبار مرکزی' }}</div>
            <div>{{ $visibleManagersText !== '' ? $visibleManagersText : '........................' }}</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">تهیه‌کننده گزارش موجودی</div>
            <div>{{ $generatedBy }}</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">تایید انبارگردانی / مدیریت</div>
            <div>مهر و امضا</div>
        </div>
    </div>
@endsection
