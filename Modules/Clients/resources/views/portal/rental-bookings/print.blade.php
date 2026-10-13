@php use Morilog\Jalali\CalendarUtils; @endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>ووچر رسمی اقامتگاه - {{ $booking->booking_code }}</title>
    <style>
        body {
            font-family: 'IRANYekanX', 'Tahoma', sans-serif;
            margin: 0;
            padding: 24px;
            background: #fff;
            color: #1e293b;
            font-size: 12px;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .brand-title {
            font-size: 18px;
            font-weight: 900;
            color: #4f46e5;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 11px;
            background: #e0e7ff;
            color: #3730a3;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        .card {
            border: 1px solid #f1f5f9;
            background: #f8fafc;
            border-radius: 12px;
            padding: 14px;
        }
        .card-title {
            font-weight: 800;
            font-size: 12px;
            color: #0f172a;
            margin-bottom: 8px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 11px;
        }
        .table th, .table td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: right;
        }
        .table th {
            background: #f1f5f9;
            font-weight: 800;
        }
        .footer {
            margin-top: 24px;
            text-align: center;
            font-size: 10px;
            color: #64748b;
            border-top: 1px dashed #cbd5e1;
            padding-top: 12px;
        }
        @media print {
            body { padding: 0; }
            .container { border: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <div>
                <div class="brand-title">{{ config('app.name', 'سامانه رزرواسیون اقامتگاه') }}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">برگه رسمی تأییدیه و ووچر دیجیتال رزرو اقامتگاه</div>
            </div>
            <div style="text-align: left;">
                <div style="font-weight: 900; font-size: 14px;">کد رزرو: {{ $booking->booking_code }}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">تاریخ صدور: {{ CalendarUtils::convertNumbers(jdate()->format('Y/m/d')) }}</div>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div class="badge">وضعیت رزرو: {{ $booking->status_badge['label'] ?? ($booking->status_label ?? 'نامشخص') }}</div>
            <div class="badge" style="background: #dcfce7; color: #166534;">وضعیت مالی: {{ $booking->payment_status_badge['label'] ?? ($booking->payment_status_label ?? 'نامشخص') }}</div>
        </div>

        <div class="grid-2">
            <div class="card">
                <div class="card-title">مشخصات اقامتگاه</div>
                <div class="row"><span>نام اقامتگاه:</span><strong>{{ $booking->property?->title ?? '---' }}</strong></div>
                <div class="row"><span>کد ملک:</span><strong>{{ $booking->property?->code ?? '---' }}</strong></div>
                <div class="row"><span>میزبان:</span><strong>{{ $booking->host?->display_name ?? 'مدیریت' }}</strong></div>
                <div class="row"><span>شماره تماس میزبان:</span><strong>{{ $booking->host?->phone ?? '---' }}</strong></div>
                <div class="row"><span>آدرس دقیق:</span><strong>{{ $booking->property?->address ?: ($booking->property?->city . ' - ' . $booking->property?->neighborhood) }}</strong></div>
            </div>

            <div class="card">
                <div class="card-title">مشخصات مهمان و برنامه سفر</div>
                <div class="row"><span>نام مهمان اصلی:</span><strong>{{ $booking->guest_full_name }}</strong></div>
                <div class="row"><span>شماره تماس:</span><strong>{{ $booking->guest_phone }}</strong></div>
                <div class="row"><span>تعداد مهمانان:</span><strong>{{ CalendarUtils::convertNumbers($booking->guests_count) }} نفر</strong></div>
                <div class="row"><span>تاریخ ورود:</span><strong>{{ CalendarUtils::convertNumbers($booking->check_in) }}</strong></div>
                <div class="row"><span>تاریخ خروج:</span><strong>{{ CalendarUtils::convertNumbers($booking->check_out) }} ({{ CalendarUtils::convertNumbers($booking->nights_count) }} شب)</strong></div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 16px;">
            <div class="card-title">صورت‌حساب مالی اقامت</div>
            <table class="table">
                <thead>
                    <tr>
                        <th>شرح هزینه</th>
                        <th>تعداد / مدت</th>
                        <th>مبلغ ({{ $currencyLabel }})</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>اجاره بهای اقامتگاه</td>
                        <td>{{ CalendarUtils::convertNumbers($booking->nights_count) }} شب</td>
                        <td>{{ CalendarUtils::convertNumbers(number_format($booking->nights_price)) }}</td>
                    </tr>
                    @if($booking->extra_guests_fee > 0)
                        <tr>
                            <td>هزینه نفرات اضافه</td>
                            <td>---</td>
                            <td>{{ CalendarUtils::convertNumbers(number_format($booking->extra_guests_fee)) }}</td>
                        </tr>
                    @endif
                    @if($booking->cleaning_fee > 0)
                        <tr>
                            <td>هزینه خدمات و نظافت</td>
                            <td>---</td>
                            <td>{{ CalendarUtils::convertNumbers(number_format($booking->cleaning_fee)) }}</td>
                        </tr>
                    @endif
                    <tr style="font-weight: 800; background: #e0e7ff;">
                        <td colspan="2">مبلغ کل قابل پرداخت / تسویه شده:</td>
                        <td>{{ CalendarUtils::convertNumbers(number_format($booking->total_price)) }} {{ $currencyLabel }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if($booking->guest_notes)
            <div class="card" style="margin-bottom: 16px;">
                <div class="card-title">توضیحات و درخواست‌های مهمان</div>
                <div>{{ $booking->guest_notes }}</div>
            </div>
        @endif

        <div class="footer">
            ارائه کارت شناسایی معتبر در زمان تحویل اقامتگاه الزامی است. جهت هماهنگی ساعت دقیق ورود با میزبان تماس حاصل فرمایید.
        </div>

        <div class="no-print" style="margin-top: 20px; text-align: center;">
            <button onclick="window.print()" style="padding: 10px 24px; background: #4f46e5; color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">
                چاپ ووچر
            </button>
        </div>
    </div>

</body>
</html>
