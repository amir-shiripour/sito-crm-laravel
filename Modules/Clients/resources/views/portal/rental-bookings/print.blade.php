@php
    use Morilog\Jalali\CalendarUtils;

    $isConfirmed = ($booking->payment_status === \Modules\Properties\Entities\PropertyRentalBooking::PAYMENT_PAID)
        || in_array($booking->status, [
            \Modules\Properties\Entities\PropertyRentalBooking::STATUS_CONFIRMED,
            \Modules\Properties\Entities\PropertyRentalBooking::STATUS_CHECKED_IN,
            \Modules\Properties\Entities\PropertyRentalBooking::STATUS_COMPLETED
        ]);

    $addressRule = $rentalDisplaySettings['show_address'] ?? 'confirmed_booking';
    $canSeeAddress = ($addressRule === 'all') || ($addressRule === 'confirmed_booking' && $isConfirmed);

    $phoneRule = $rentalDisplaySettings['host_show_phone'] ?? 'confirmed_booking';
    $canSeePhone = ($phoneRule === 'all') || ($phoneRule === 'confirmed_booking' && $isConfirmed);

    $hostBoxRule = $rentalDisplaySettings['show_host_box'] ?? 'all';
    $canSeeHostBox = ($hostBoxRule === 'all') || ($hostBoxRule === 'confirmed_booking' && $isConfirmed);

    $badge = $booking->status_badge ?? ['label' => $booking->status_label ?? 'نامشخص', 'class' => 'bg-gray-100 text-gray-700'];
    $payBadge = $booking->payment_status_badge ?? ['label' => $booking->payment_status_label ?? 'نامشخص', 'class' => 'bg-gray-100 text-gray-700'];

    $propCover = $booking->property?->primary_image_url ?? $booking->property?->primaryImage?->image_url ?? null;
    $displayTitle = $siteTitle ?? (function_exists('get_setting') ? get_setting('app_name', config('app.name')) : config('app.name'));
    if (empty($displayTitle)) {
        $displayTitle = config('app.name', 'سامانه رزرواسیون اقامتگاه');
    }
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>برگه رسمی تأییدیه رزرو اقامتگاه - {{ $booking->booking_code }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'IRANYekanX', 'Vazirmatn', sans-serif !important;
            -webkit-font-smoothing: antialiased;
        }

        @page {
            size: A4;
            margin: 10mm 12mm;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                font-size: 11px !important;
            }
            .no-print {
                display: none !important;
            }
            .print-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
            .print-break-inside-avoid {
                break-inside: avoid;
                page-break-inside: avoid;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 p-3 sm:p-6 lg:p-8 font-sans selection:bg-indigo-500 selection:text-white">

    {{-- نوار ابزار دسکتاپ (فقط نمایش در مرورگر، مخفی در چاپ) --}}
    <div class="no-print max-w-4xl mx-auto mb-4 flex flex-col sm:flex-row items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="{{ route('client.rental-bookings.show', $booking->id) }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
                <span>بازگشت به جزئیات رزرو</span>
            </a>
            <span class="text-xs text-slate-500 font-bold hidden sm:inline">
                نسخه قابل چاپ و ذخیره PDF برگه رسمی رزرو
            </span>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" 
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 active:scale-95 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>چاپ یا ذخیره PDF</span>
            </button>
        </div>
    </div>

    {{-- کانتینر اصلی برگه تأییدیه رسمی (طراحی استاندارد A4 و هماهنگ با طراحی پرتال) --}}
    <main class="print-container max-w-4xl mx-auto bg-white rounded-3xl border border-slate-200/90 shadow-xl shadow-slate-200/50 p-6 sm:p-9 space-y-6">

        {{-- ۱. هدر سربرگ رسمی --}}
        <header class="border-b-2 border-indigo-600 pb-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                {{-- عنوان سربرگ رسمی بر اساس تنظیمات کلی سیستم (بدون لوگو و بدون Accommodation Voucher) --}}
                <div class="space-y-1">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        {{ $displayTitle }}
                    </h1>
                    <div class="text-xs text-indigo-700 font-bold">
                        برگه رسمی تأییدیه رزرو و پذیرش اقامتگاه
                    </div>
                </div>

                {{-- شماره و شناسه رزرو --}}
                <div class="flex flex-col sm:items-end justify-center text-xs font-bold space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-slate-500">کد رهگیری رزرو:</span>
                        <span class="px-3 py-1 rounded-xl bg-slate-100 text-indigo-700 text-sm font-black dir-ltr tracking-wider border border-slate-200">
                            {{ $booking->booking_code }}
                        </span>
                    </div>
                    <div class="text-slate-400 text-[11px]">
                        تاریخ صدور برگه: {{ CalendarUtils::convertNumbers(jdate()->format('Y/m/d ساعت H:i')) }}
                    </div>
                </div>
            </div>
        </header>

        {{-- ۲. نوار وضعیت و هویت رسمی رزرو --}}
        <section class="rounded-2xl bg-slate-50 border border-slate-200/80 p-3.5 sm:p-4 print-break-inside-avoid">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center sm:text-right">
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block">وضعیت رزرو</span>
                    <span class="inline-flex items-center mt-1 px-2.5 py-0.5 rounded-lg text-xs font-black {{ $badge['class'] ?? 'bg-slate-200 text-slate-800' }}">
                        {{ $badge['label'] ?? ($booking->status_label ?? 'نامشخص') }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block">وضعیت مالی و تسویه</span>
                    <span class="inline-flex items-center mt-1 px-2.5 py-0.5 rounded-lg text-xs font-black {{ $payBadge['class'] ?? 'bg-slate-200 text-slate-800' }}">
                        {{ $payBadge['label'] ?? ($booking->payment_status_label ?? 'نامشخص') }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block">مبلغ کل قرارداد اقامت</span>
                    <span class="text-xs sm:text-sm font-black text-slate-900 block mt-1">
                        {{ CalendarUtils::convertNumbers(number_format($booking->total_price)) }} {{ $currencyLabel }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block">تاریخ ثبت اولیه</span>
                    <span class="text-xs font-bold text-slate-700 block mt-1">
                        {{ CalendarUtils::convertNumbers(jdate($booking->created_at)->format('Y/m/d')) }}
                    </span>
                </div>
            </div>
        </section>

        {{-- ۳. مشخصات اقامتگاه و برنامه سفر --}}
        <section class="grid grid-cols-1 md:grid-cols-2 gap-4 print-break-inside-avoid">
            {{-- کارت مشخصات اقامتگاه --}}
            <div class="rounded-2xl border border-slate-200 p-4 space-y-3 bg-white">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <h3 class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                        <span>مشخصات اقامتگاه</span>
                    </h3>
                    @if($booking->property?->code)
                        <span class="text-[11px] font-bold text-slate-400">کد ملک: {{ $booking->property->code }}</span>
                    @endif
                </div>

                <div class="flex gap-3">
                    @if($propCover)
                        <img src="{{ $propCover }}" alt="" class="w-16 h-16 rounded-xl object-cover shrink-0 border border-slate-200">
                    @endif
                    <div class="min-w-0 flex-1 space-y-1">
                        <h4 class="font-black text-xs sm:text-sm text-slate-900 line-clamp-2">
                            {{ $booking->property?->title ?? 'اقامتگاه' }}
                        </h4>
                        <p class="text-[11px] text-slate-500 font-bold">
                            {{ $booking->property?->province ? $booking->property->province . '، ' : '' }}{{ $booking->property?->city ?? 'منطقه اقامتگاه' }}{{ $booking->property?->neighborhood ? ' - ' . $booking->property->neighborhood : '' }}
                        </p>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 text-xs space-y-1.5">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-slate-400 font-bold shrink-0">آدرس دقیق:</span>
                        <span class="font-bold text-slate-800 text-left sm:text-right">
                            @if($addressRule === 'none')
                                {{ $booking->property?->city ?? 'منطقه اقامتگاه' }} (عدم نمایش آدرس متنی دقیق طبق تنظیمات سامانه)
                            @elseif($canSeeAddress)
                                {{ $booking->property?->address ?: ($booking->property?->city . ' - ' . $booking->property?->neighborhood) }}
                            @else
                                {{ $booking->property?->city ?? 'منطقه اقامتگاه' }} (آدرس و پلاک دقیق پس از پرداخت و قطعی شدن رزرو)
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            {{-- کارت زمان‌بندی و برنامه سفر --}}
            <div class="rounded-2xl border border-slate-200 p-4 space-y-3 bg-white">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <h3 class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                        <span>زمان‌بندی و برنامه سفر</span>
                    </h3>
                    <span class="text-xs font-black text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-lg border border-indigo-100">
                        {{ CalendarUtils::convertNumbers($booking->nights_count) }} شب اقامت
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-2.5 text-center">
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] text-slate-400 font-bold block">تاریخ و ساعت ورود</span>
                        <span class="text-xs sm:text-sm font-black text-slate-900 block mt-0.5">
                            {{ CalendarUtils::convertNumbers($booking->check_in) }}
                        </span>
                        <span class="text-[10px] text-slate-500 font-bold mt-0.5 block">
                            از ساعت {{ CalendarUtils::convertNumbers($checkInTime) }}
                        </span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] text-slate-400 font-bold block">تاریخ و ساعت خروج</span>
                        <span class="text-xs sm:text-sm font-black text-slate-900 block mt-0.5">
                            {{ CalendarUtils::convertNumbers($booking->check_out) }}
                        </span>
                        <span class="text-[10px] text-slate-500 font-bold mt-0.5 block">
                            تا ساعت {{ CalendarUtils::convertNumbers($checkOutTime) }}
                        </span>
                    </div>
                </div>

                <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-slate-700">
                    <span class="text-slate-400">تعداد مهمانان ثبت‌شده:</span>
                    <span class="font-black text-slate-900">{{ CalendarUtils::convertNumbers($booking->guests_count) }} نفر</span>
                </div>
            </div>
        </section>

        {{-- ۴. مشخصات مهمان اصلی و میزبان اقامتگاه --}}
        <section class="grid grid-cols-1 md:grid-cols-2 gap-4 print-break-inside-avoid">
            {{-- مشخصات مهمان --}}
            <div class="rounded-2xl border border-slate-200 p-4 space-y-2.5 bg-white">
                <h3 class="text-xs font-black text-slate-900 border-b border-slate-100 pb-2">
                    مشخصات مهمان اصلی (تحویل‌گیرنده)
                </h3>
                <div class="space-y-1.5 text-xs font-bold">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">نام و نام خانوادگی:</span>
                        <span class="text-slate-900 font-black">{{ $booking->guest_full_name }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">شماره تماس همراه:</span>
                        <span class="text-slate-900 font-black dir-ltr">{{ CalendarUtils::convertNumbers($booking->guest_phone) }}</span>
                    </div>
                    @if($booking->guest_national_code)
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">کد ملی:</span>
                            <span class="text-slate-900 font-black dir-ltr">{{ CalendarUtils::convertNumbers($booking->guest_national_code) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- مشخصات میزبان --}}
            <div class="rounded-2xl border border-slate-200 p-4 space-y-2.5 bg-white">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="text-xs font-black text-slate-900">مشخصات میزبان و هماهنگی</h3>
                    @if(!empty($rentalDisplaySettings['host_show_badge']) && ($booking->host?->is_verified || $booking->host?->kyc_status === 'approved'))
                        <span class="text-[10px] font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded-md border border-teal-100">
                            تأیید هویت شده
                        </span>
                    @endif
                </div>

                @if($canSeeHostBox)
                    <div class="space-y-1.5 text-xs font-bold">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">نام میزبان:</span>
                            <span class="text-slate-900 font-black">{{ $booking->host?->display_name ?? 'مدیریت اقامتگاه' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">شماره تماس میزبان:</span>
                            @if($canSeePhone)
                                <span class="text-indigo-700 font-black dir-ltr">{{ CalendarUtils::convertNumbers($booking->host?->phone ?? '---') }}</span>
                            @else
                                <span class="text-amber-700 text-[11px]">نمایش پس از پرداخت و قطعی شدن</span>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="text-xs text-amber-700 font-bold py-2">
                        اطلاعات تماس میزبان پس از پرداخت و قطعی شدن رزرو در دسترس خواهد بود.
                    </p>
                @endif
            </div>
        </section>

        {{-- ۵. صورت‌حساب مالی تفکیک‌شده --}}
        <section class="rounded-2xl border border-slate-200 overflow-hidden print-break-inside-avoid">
            <div class="bg-slate-50 px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                    <span>صورت‌حساب مالی اقامت</span>
                </h3>
                <span class="text-[11px] font-bold text-slate-400">مبالغ به {{ $currencyLabel }}</span>
            </div>

            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50/70 border-b border-slate-200 text-slate-500 font-bold text-[11px]">
                    <tr>
                        <th class="px-4 py-2.5">شرح فاکتور</th>
                        <th class="px-4 py-2.5 text-center">تعداد / مدت</th>
                        <th class="px-4 py-2.5 text-left">مبلغ ({{ $currencyLabel }})</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-bold text-slate-700">
                    <tr>
                        <td class="px-4 py-2.5">اجاره بهای اقامتگاه</td>
                        <td class="px-4 py-2.5 text-center">{{ CalendarUtils::convertNumbers($booking->nights_count) }} شب</td>
                        <td class="px-4 py-2.5 text-left font-black text-slate-900">
                            {{ CalendarUtils::convertNumbers(number_format($booking->nights_total ?? $booking->nights_price)) }}
                        </td>
                    </tr>
                    @if($booking->extra_guests_fee > 0)
                        <tr>
                            <td class="px-4 py-2.5">هزینه نفرات اضافه</td>
                            <td class="px-4 py-2.5 text-center">---</td>
                            <td class="px-4 py-2.5 text-left font-black text-slate-900">
                                {{ CalendarUtils::convertNumbers(number_format($booking->extra_guests_fee)) }}
                            </td>
                        </tr>
                    @endif
                    @if($booking->cleaning_fee > 0)
                        <tr>
                            <td class="px-4 py-2.5">هزینه خدمات و نظافت</td>
                            <td class="px-4 py-2.5 text-center">---</td>
                            <td class="px-4 py-2.5 text-left font-black text-slate-900">
                                {{ CalendarUtils::convertNumbers(number_format($booking->cleaning_fee)) }}
                            </td>
                        </tr>
                    @endif
                    @if($booking->discount_amount > 0)
                        <tr class="text-emerald-700 bg-emerald-50/40">
                            <td class="px-4 py-2.5">تخفیف ویژه</td>
                            <td class="px-4 py-2.5 text-center">---</td>
                            <td class="px-4 py-2.5 text-left font-black">
                                -{{ CalendarUtils::convertNumbers(number_format($booking->discount_amount)) }}
                            </td>
                        </tr>
                    @endif
                    <tr class="bg-indigo-50/80 text-indigo-950 font-black text-xs sm:text-sm">
                        <td class="px-4 py-3" colspan="2">مبلغ کل قابل پرداخت / تسویه شده:</td>
                        <td class="px-4 py-3 text-left font-black text-indigo-700">
                            {{ CalendarUtils::convertNumbers(number_format($booking->total_price)) }} {{ $currencyLabel }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>

        {{-- ۶. مقررات اقامتگاه و شرایط کنسلی --}}
        <section class="rounded-2xl border border-slate-200 p-4 space-y-3 print-break-inside-avoid bg-white">
            <h3 class="text-xs font-black text-slate-900 flex items-center gap-1.5 border-b border-slate-100 pb-2">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span>قوانین، شرایط کنسلی و ضوابط اقامت</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-bold text-slate-700">
                <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-400 block text-[10px]">ساعت ورود:</span>
                    <span class="text-slate-900 font-black">از ساعت {{ CalendarUtils::convertNumbers($checkInTime) }} به بعد</span>
                </div>
                <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-400 block text-[10px]">ساعت خروج و تخلیه:</span>
                    <span class="text-slate-900 font-black">حداکثر تا ساعت {{ CalendarUtils::convertNumbers($checkOutTime) }} ظهر</span>
                </div>
            </div>

            @if(!empty($cancellationRulesText))
                <div class="p-3 rounded-xl bg-amber-50/70 border border-amber-200/60 text-amber-950 text-xs font-bold leading-relaxed space-y-0.5">
                    <span class="font-black text-amber-900 block">شرایط و قوانین کنسلی:</span>
                    <p class="text-[11px] leading-relaxed">{{ $cancellationRulesText }}</p>
                </div>
            @endif

            @if($booking->guest_notes)
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs font-bold text-slate-700 space-y-0.5">
                    <span class="text-slate-400 block text-[10px]">یادداشت و درخواست‌های مهمان:</span>
                    <p class="text-[11px] text-slate-800">{{ $booking->guest_notes }}</p>
                </div>
            @endif
        </section>

        {{-- ۷. فوتر سند، تذکرات مهم و اصالت‌سنجی --}}
        <footer class="pt-4 border-t border-slate-200 text-xs font-bold text-slate-500 space-y-3 print-break-inside-avoid">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-slate-50 p-3 rounded-xl border border-slate-100">
                <div class="space-y-0.5">
                    <div class="text-slate-800 font-black text-xs">تذکرات مهم پذیرش:</div>
                    <div class="text-[11px] text-slate-500 leading-relaxed">
                        • همراه داشتن کارت ملی هوشمند یا شناسنامه عکس‌دار معتبر برای تمامی مهمانان در زمان پذیرش الزامی است.
                        <br>
                        • تحویل کلید اقامتگاه منوط به تسویه کامل فاکتور و احراز هویت مسافر گرامی خواهد بود.
                    </div>
                </div>

                @if($contactPhone)
                    <div class="shrink-0 text-left sm:border-r sm:pr-4 sm:border-slate-200">
                        <span class="text-[10px] text-slate-400 block">پشتیبانی سامانه:</span>
                        <span class="text-xs font-black text-indigo-700 dir-ltr block mt-0.5">
                            {{ CalendarUtils::convertNumbers($contactPhone) }}
                        </span>
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1">
                <span>این سند به‌صورت سیستمی توسط سامانه رزرواسیون صادر گردیده و دارای اعتبار رسمی جهت ورود به اقامتگاه می‌باشد.</span>
                <span class="dir-ltr font-bold text-slate-400">ID: {{ $booking->id }} • {{ $booking->booking_code }}</span>
            </div>
        </footer>

    </main>

</body>
</html>
