@extends('warehouse::pdf.layout')

@php
    $docTitle = 'کاردکس رسمی کالا و تفکیک پارت‌های موجودی';
    $docSubtitle = $item->name;
    $docCode = $item->system_code;
    $createdJalali = $item->created_at
        ? (function_exists('jdate') ? jdate($item->created_at)->format('Y/m/d H:i') : $item->created_at->format('Y-m-d H:i'))
        : '—';
@endphp

@section('pdf_content')
    <div class="info-grid">
        <div class="info-cell">
            <div class="info-label">کد تجاری (SKU)</div>
            <div class="info-value"><span class="ltr">{{ $item->code }}</span></div>
        </div>
        <div class="info-cell">
            <div class="info-label">دسته‌بندی</div>
            <div class="info-value">{{ $item->category?->name ?? '—' }} @if($item->subcategory) / {{ $item->subcategory->name }} @endif</div>
        </div>
        <div class="info-cell">
            <div class="info-label">موجودی کل انبارها</div>
            <div class="info-value text-indigo">{{ number_format($item->total_stock) }} {{ $item->unit }}</div>
        </div>
        <div class="info-cell">
            <div class="info-label">ارزش بهای خرید / نرخ روز</div>
            <div class="info-value">
                خرید: {{ number_format($item->total_cost_value) }} ریال
                <br>روز: <span class="text-emerald">{{ number_format($item->total_market_value) }} ریال</span>
            </div>
        </div>
        <div class="info-cell">
            <div class="info-label">کاربر ثبت‌کننده کالا</div>
            <div class="info-value">{{ $item->creator_name }}<br><span style="font-size: 8.5px; font-weight: normal;"><span class="ltr">{{ $createdJalali }}</span></span></div>
        </div>
    </div>

    <div class="section-heading">پراکندگی موجودی فعلی در انبارها و انبارک‌ها</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>نام انبار / انبارک</th>
                <th>نوع انبار</th>
                <th>موجودی فعلی</th>
                <th>موقعیت فیزیکی</th>
            </tr>
        </thead>
        <tbody>
            @forelse($item->stocks->where('quantity', '>', 0) as $st)
                <tr>
                    <td class="bold">{{ $st->warehouse?->name ?? 'نامشخص' }}</td>
                    <td>{{ $st->warehouse?->is_main ? 'انبار مرکزی' : 'انبارک فرعی' }}</td>
                    <td class="bold text-indigo">{{ number_format($st->quantity) }} {{ $item->unit }}</td>
                    <td>{{ $st->location_in_warehouse ?: ($item->default_location ?: '—') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">موجودی فعلی در انبارها صفر است.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-heading">کاردکس کامل پارت‌های ورود و انتقال</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>کد پارت / فاکتور</th>
                <th>انبار مستقر</th>
                <th>تاریخ ورود</th>
                <th>اولیه</th>
                <th>باقیمانده</th>
                <th>فی خرید (ریال)</th>
                <th>ارزش باقیمانده (ریال)</th>
                <th>ثبت‌کننده پارت</th>
                <th>وضعیت</th>
            </tr>
        </thead>
        <tbody>
            @forelse($item->batches as $idx => $b)
                @php
                    $bDate = $b->purchase_date ? (function_exists('jdate') ? jdate($b->purchase_date)->format('Y/m/d') : $b->purchase_date->format('Y-m-d')) : '—';
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="bold text-indigo"><span class="ltr">{{ $b->batch_code }}</span></td>
                    <td>{{ $b->warehouse?->name ?? '—' }}</td>
                    <td><span class="ltr">{{ $bDate }}</span></td>
                    <td>{{ number_format($b->initial_quantity) }}</td>
                    <td class="bold">{{ number_format($b->remaining_quantity) }}</td>
                    <td>{{ number_format($b->unit_purchase_price) }}</td>
                    <td class="bold">{{ number_format($b->remaining_quantity * $b->unit_purchase_price) }}</td>
                    <td>{{ $b->creator_name }}</td>
                    <td>{{ $b->remaining_quantity > 0 ? 'فعال' : 'تخلیه‌شده' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">هیچ پارتی برای این کالا ثبت نشده است.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(isset($transferItems) && $transferItems->count() > 0)
        <div class="section-heading">سابقه درخواست‌ها و انتقال‌های بین انبارها</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>شماره حواله / درخواست</th>
                    <th>تاریخ ثبت</th>
                    <th>مبدأ ← مقصد</th>
                    <th>درخواست‌کننده</th>
                    <th>درخواستی / تحویلی</th>
                    <th>بهای تمام‌شده (ریال)</th>
                    <th>وضعیت</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transferItems as $tIdx => $tLine)
                    @php
                        $tr = $tLine->transferRequest;
                        $trDate = $tr?->request_date ? (function_exists('jdate') ? jdate($tr->request_date)->format('Y/m/d') : $tr->request_date->format('Y-m-d')) : '—';
                    @endphp
                    <tr>
                        <td>{{ $tIdx + 1 }}</td>
                        <td class="bold text-indigo"><span class="ltr">{{ $tr?->transfer_number ?? '—' }}</span></td>
                        <td><span class="ltr">{{ $trDate }}</span></td>
                        <td>{{ $tr?->sourceWarehouse?->name ?? '—' }} ← {{ $tr?->destinationWarehouse?->name ?? '—' }}</td>
                        <td>{{ $tr?->effective_requester_name ?? '—' }}</td>
                        <td class="bold">{{ number_format($tLine->requested_quantity) }} / {{ number_format($tLine->delivered_quantity) }} {{ $item->unit }}</td>
                        <td>{{ number_format((float)$tLine->total_cost_value) }}</td>
                        <td>{{ $tr?->status_label ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(isset($issueItems) && $issueItems->count() > 0)
        <div class="section-heading">سابقه خروج و مصرف کالا (بیماران و بخش‌ها)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>شماره سند</th>
                    <th>تاریخ مصرف</th>
                    <th>انبار مبدأ</th>
                    <th>بیمار / پرونده / مصرف‌کننده</th>
                    <th>تعداد</th>
                    <th>بهای تمام‌شده (ریال)</th>
                    <th>ثبت‌کننده</th>
                </tr>
            </thead>
            <tbody>
                @foreach($issueItems as $iIdx => $issLine)
                    @php
                        $issDoc = $issLine->document;
                        $issDate = $issDoc?->issue_date ? (function_exists('jdate') ? jdate($issDoc->issue_date)->format('Y/m/d') : $issDoc->issue_date->format('Y-m-d')) : '—';
                    @endphp
                    <tr>
                        <td>{{ $iIdx + 1 }}</td>
                        <td class="bold text-indigo"><span class="ltr">{{ $issDoc?->issue_number ?? '—' }}</span></td>
                        <td><span class="ltr">{{ $issDate }}</span></td>
                        <td>{{ $issLine->warehouse?->name ?? '—' }}</td>
                        <td class="text-right">
                            {{ $issDoc?->effective_patient_name ?: ($issDoc?->effective_consumer_name ?: 'مصرف بخش') }}
                            @if($issDoc?->effective_case_number)
                                ({{ $issDoc->effective_case_number }})
                            @endif
                        </td>
                        <td class="bold text-rose">{{ rtrim(rtrim(number_format((float)$issLine->quantity, 2), '0'), '.') }} {{ $item->unit }}</td>
                        <td>{{ number_format((float)$issLine->total_cost) }}</td>
                        <td>{{ $issDoc?->creator_name ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
