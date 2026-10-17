@extends('clients::layouts.client')

@includeIf('partials.jalali-date-picker')

@php
    use Morilog\Jalali\CalendarUtils;

    $title = 'جزئیات صورت‌حساب رزرو اقامتگاه #' . $booking->booking_code;

    $isPaid = $booking->payment_status === \Modules\Properties\Entities\PropertyRentalBooking::PAYMENT_PAID;
    $isPendingHost = $booking->status === \Modules\Properties\Entities\PropertyRentalBooking::STATUS_PENDING_HOST;
    $isAwaitingPayment = $booking->status === \Modules\Properties\Entities\PropertyRentalBooking::STATUS_AWAITING_PAYMENT && !$isPaid;
    $isCancelled = str_contains($booking->status, 'cancel') || in_array($booking->status, ['rejected', 'expired']);

    $payStatusColor = match(true) {
        $isPaid => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        $isAwaitingPayment => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        $isPendingHost => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        $isCancelled => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
    };

    $payStatusLabel = match(true) {
        $isPaid => 'تسویه کامل و پرداخت شده',
        $isAwaitingPayment => 'در انتظار پرداخت',
        $isPendingHost => 'در انتظار بررسی و تأیید میزبان',
        $isCancelled => 'لغو شده',
        default => ($booking->payment_status_label ?? 'نامشخص')
    };

    $meta = $booking->meta ?? [];
    $bankReceipt = $meta['bank_receipt'] ?? null;
    $onlinePaymentInfo = $meta['online_payment'] ?? ($meta['pending_online_payment'] ?? null);

    $paidDate = $booking->confirmed_at ?? ($isPaid ? $booking->updated_at : null);

    // روش و درگاه پرداخت
    $gatewayName = match(strtolower((string)$booking->payment_method)) {
        'zibal' => 'درگاه پرداخت زیبال',
        'zarinpal' => 'درگاه پرداخت زرین‌پال',
        'mellat', 'behpardakht' => 'به‌پرداخت (بانک ملت)',
        'saman', 'sep' => 'سامان کیش (سپ)',
        'transfer' => 'انتقال بانکی (کارت به کارت / شبا)',
        'online' => 'پرداخت اینترنتی شتاب (' . ($onlinePaymentInfo['gateway'] ?? 'شاپرک') . ')',
        default => ($booking->payment_method ? ucfirst($booking->payment_method) : ($isPaid ? 'تسویه شده' : 'در انتظار پرداخت'))
    };

    $refCode = $onlinePaymentInfo['ref_id'] 
        ?? ($onlinePaymentInfo['tracking_code'] 
        ?? ($bankReceipt['tracking_code'] ?? null));

    $displayAmount = (float) $booking->total_price;
    $coverImage = $booking->property?->primary_image_url ?? $booking->property?->primaryImage?->image_url ?? asset('assets/images/placeholder.jpg');
    $canPay = $isAwaitingPayment && !$isCancelled;
@endphp

@section('content')
<div x-data="clientPaymentModal()" class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-4xl mx-auto font-sans">

    {{-- هدر صفحه --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <a href="{{ route('client.payments.index') }}" class="p-2 -m-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-gray-800 transition-colors" title="بازگشت به لیست پرداخت‌ها">
                    <svg class="w-6 h-6 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </a>
                <span>{{ $isPendingHost ? 'پیش‌فاکتور رزرو اقامتگاه' : 'صورت‌حساب مالی اقامتگاه' }}</span>
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                کد رزرو مربوطه: <span class="font-black text-indigo-600 dark:text-indigo-400">{{ $booking->booking_code }}</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('client.rental-bookings.show', $booking->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl transition text-xs font-bold shadow-xs">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>مشاهده پرونده و سند اقامتگاه</span>
            </a>

            <a href="{{ route('client.rental-bookings.print', $booking->id) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl transition text-xs font-bold shadow-xs">
                <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>چاپ نسخه رسمی</span>
            </a>

            @if($canPay)
                <button type="button" 
                        @click="openModal({{ $booking->id }}, '{{ number_format($displayAmount) }}')"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition text-xs font-black shadow-md shadow-emerald-600/20 active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                    <span>تکمیل و تسویه صورت‌حساب</span>
                </button>
            @elseif($isPendingHost)
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-800/40 text-blue-700 dark:text-blue-300 rounded-xl text-xs font-bold">
                    <svg class="w-4 h-4 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>در انتظار تأیید میزبان</span>
                </div>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-bold flex items-center gap-3">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($isPendingHost)
        <div class="p-5 rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/40 dark:to-orange-950/30 border border-amber-200/80 dark:border-amber-800/60 text-amber-900 dark:text-amber-200 text-xs font-bold flex items-start gap-3 shadow-xs">
            <svg class="w-6 h-6 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="space-y-1">
                <div class="text-sm font-black text-amber-950 dark:text-amber-100">درخواست رزرو شما در انتظار بررسی و تأیید میزبان است</div>
                <p class="leading-relaxed text-amber-800 dark:text-amber-300">
                    صورت‌حساب نهایی و درگاه پرداخت آنلاین پس از تأیید اقامتگاه توسط میزبان صادر و فعال خواهد شد. به محض تأیید میزبان، پیامک اطلاع‌رسانی برای شما ارسال شده و مهلت پرداخت آغاز می‌گردد.
                </p>
            </div>
        </div>
    @endif

    {{-- کارت اصلی صورت‌حساب رسید مالی --}}
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-10 border border-gray-100 dark:border-gray-700 shadow-xl shadow-gray-200/40 dark:shadow-none relative overflow-hidden">
        {{-- نوار رنگی دکوراتیو بالا --}}
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-indigo-500 to-violet-600"></div>

        <div class="flex flex-col md:flex-row justify-between gap-8 mb-8 pb-8 border-b border-gray-100 dark:border-gray-700 border-dashed">
            <div>
                <div class="text-xs font-bold text-gray-400 mb-1">کد یکتای رزرو اقامتگاه</div>
                <div class="text-2xl font-black text-gray-900 dark:text-white tracking-widest font-sans">{{ $booking->booking_code }}</div>
                <div class="mt-3 inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-black {{ $payStatusColor }}">
                    {{ $payStatusLabel }}
                </div>
            </div>

            <div class="text-right">
                <div class="text-xs font-bold text-gray-400 mb-1">تاریخ ثبت درخواست</div>
                <div class="font-black text-sm text-gray-900 dark:text-white">{{ CalendarUtils::convertNumbers(jdate($booking->created_at)->format('Y/m/d H:i')) }}</div>

                @if($paidDate)
                    <div class="text-xs font-bold text-gray-400 mt-3 mb-1">تاریخ پرداخت و تسویه</div>
                    <div class="font-black text-sm text-emerald-600 dark:text-emerald-400">{{ CalendarUtils::convertNumbers(jdate($paidDate)->format('Y/m/d H:i')) }}</div>
                @endif
            </div>
        </div>

        {{-- اطلاعات تراکنش و درگاه --}}
        <div class="mb-8">
            <h3 class="text-sm font-black text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                <span>اطلاعات پرداخت و رهگیری بانکی</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-4 border border-gray-100 dark:border-gray-700/80 flex items-center justify-between text-xs font-bold">
                    <span class="text-gray-400">روش / کانال پرداخت:</span>
                    <span class="text-gray-900 dark:text-white font-black">{{ $gatewayName }}</span>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-4 border border-gray-100 dark:border-gray-700/80 flex items-center justify-between text-xs font-bold">
                    <span class="text-gray-400">کد رهگیری / شماره پیگیری:</span>
                    <span class="text-gray-900 dark:text-white font-black dir-ltr">{{ $refCode ?: '---' }}</span>
                </div>
            </div>
        </div>

        {{-- ریز آیتم‌های صورت‌حساب اقامتگاه --}}
        <div class="mb-8">
            <h3 class="text-sm font-black text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                <span>ریز اقلام و خدمات صورت‌حساب</span>
            </h3>

            {{-- کارت معرفی اقامتگاه و بازه سفر --}}
            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-4 sm:p-5 border border-gray-100 dark:border-gray-700/80 mb-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <img src="{{ $coverImage }}" alt="" class="w-16 h-16 rounded-xl object-cover border border-gray-200 dark:border-gray-700 shrink-0">
                    <div>
                        <h4 class="font-black text-sm text-gray-900 dark:text-white">{{ $booking->property?->title ?? 'اقامتگاه' }}</h4>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex flex-wrap gap-x-3 gap-y-1">
                            <span>میزبان: <strong>{{ $booking->host?->display_name ?? 'مدیریت' }}</strong></span>
                            <span>مدت اقامت: <strong>{{ CalendarUtils::convertNumbers($booking->nights_count) }} شب</strong></span>
                            <span>ورود: <strong>{{ CalendarUtils::convertNumbers($booking->check_in) }}</strong></span>
                            <span>خروج: <strong>{{ CalendarUtils::convertNumbers($booking->check_out) }}</strong></span>
                        </div>
                    </div>
                </div>
                <div class="text-left font-black text-indigo-600 dark:text-indigo-400 self-end sm:self-center">
                    <span class="text-xs text-gray-400 block font-normal">تعداد مهمانان:</span>
                    <span>{{ CalendarUtils::convertNumbers($booking->guests_count) }} نفر</span>
                </div>
            </div>

            {{-- جدول فاکتور --}}
            <div class="border border-gray-100 dark:border-gray-700/80 rounded-2xl overflow-hidden">
                <table class="w-full text-xs text-right">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 font-bold border-b border-gray-100 dark:border-gray-700/80">
                        <tr>
                            <th class="p-3.5">شرح قلم صورت‌حساب</th>
                            <th class="p-3.5 text-center">مدت / تعداد</th>
                            <th class="p-3.5 text-left">مبلغ ({{ $currencyLabel }})</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-bold text-gray-700 dark:text-gray-300">
                        <tr>
                            <td class="p-3.5 font-black text-gray-900 dark:text-white">اجاره بهای اقامتگاه</td>
                            <td class="p-3.5 text-center text-gray-500">{{ CalendarUtils::convertNumbers($booking->nights_count) }} شب</td>
                            <td class="p-3.5 text-left font-black">{{ CalendarUtils::convertNumbers(number_format($booking->nights_total)) }}</td>
                        </tr>
                        @if($booking->cleaning_fee > 0)
                            <tr>
                                <td class="p-3.5">هزینه خدمات و نظافت نهایی</td>
                                <td class="p-3.5 text-center text-gray-500">۱ بار</td>
                                <td class="p-3.5 text-left font-black">{{ CalendarUtils::convertNumbers(number_format($booking->cleaning_fee)) }}</td>
                            </tr>
                        @endif
                        @if($booking->extra_guests_fee > 0)
                            <tr>
                                <td class="p-3.5">هزینه نفرات اضافه بر ظرفیت پایه</td>
                                <td class="p-3.5 text-center text-gray-500">{{ CalendarUtils::convertNumbers($booking->extra_guests_count) }} نفر</td>
                                <td class="p-3.5 text-left font-black">{{ CalendarUtils::convertNumbers(number_format($booking->extra_guests_fee)) }}</td>
                            </tr>
                        @endif
                        @if($booking->discount_amount > 0)
                            <tr class="text-emerald-600 dark:text-emerald-400">
                                <td class="p-3.5">تخفیف ویژه رزرواسیون</td>
                                <td class="p-3.5 text-center">---</td>
                                <td class="p-3.5 text-left font-black">-{{ CalendarUtils::convertNumbers(number_format($booking->discount_amount)) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- جمع کل و مانده --}}
        <div class="border-t border-gray-100 dark:border-gray-700 pt-6 flex flex-col items-end gap-3">
            <div class="flex justify-between w-full sm:w-1/2 text-xs font-bold text-gray-500 dark:text-gray-400">
                <span>جمع کل صورت‌حساب:</span>
                <span class="text-gray-900 dark:text-white font-black text-sm">{{ CalendarUtils::convertNumbers(number_format($booking->total_price)) }} {{ $currencyLabel }}</span>
            </div>

            <div class="flex justify-between w-full sm:w-1/2 text-sm font-black text-indigo-700 dark:text-indigo-400 bg-indigo-50/70 dark:bg-indigo-950/40 p-4 rounded-2xl border border-indigo-100 dark:border-indigo-900/50">
                <span>{{ $isPaid ? 'مبلغ تسویه شده:' : ($isPendingHost ? 'مبلغ برآوردی پس از تأیید میزبان:' : 'مبلغ قابل پرداخت:') }}</span>
                <span>{{ CalendarUtils::convertNumbers(number_format($displayAmount)) }} {{ $currencyLabel }}</span>
            </div>
        </div>
    </div>

    {{-- مودال پرداخت اختصاصی هماهنگ با ساختار ماژول نوبت‌دهی و سرویس‌ها --}}
    <div x-show="showPaymentModal" 
         class="relative z-[150]" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true" 
         style="display: none;">
        
        <div x-show="showPaymentModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>
             
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-3 text-center sm:items-center sm:p-4">
                <div x-show="showPaymentModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-900 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl md:max-w-2xl border border-gray-100 dark:border-gray-800 flex flex-col max-h-[90vh] min-h-0">
                     
                    {{-- Modal Header --}}
                    <div class="px-6 py-5 bg-gray-50/80 dark:bg-gray-800/80 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center shrink-0">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-xs border border-indigo-100 dark:border-indigo-900/50">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white" id="modal-title">تکمیل و تسویه صورت‌حساب اقامتگاه</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">روش پرداخت دلخواه را انتخاب کرده و اطلاعات را تکمیل کنید</p>
                            </div>
                        </div>
                        <button @click="closeModal()" type="button" class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                            <span class="sr-only">بستن</span>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form :action="formAction" method="POST" enctype="multipart/form-data" id="clientRentalPaymentForm" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                        @csrf
                        <input type="hidden" name="payment_method" :value="method">
                        <input type="hidden" name="sub_item" :value="subItem">

                        {{-- Modal Body --}}
                        <div class="p-6 space-y-6 flex-1 min-h-0 overflow-y-auto custom-scrollbar">
                            
                            {{-- کارت خلاصه مبلغ با دکمه کپی عدد خالص --}}
                            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-800 p-4 sm:p-5 text-white shadow-lg shadow-indigo-600/20">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 relative z-10">
                                    <div>
                                        <span class="text-xs font-medium text-indigo-100/90 block mb-1">مبلغ قابل پرداخت صورت‌حساب</span>
                                        <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
                                            <div class="flex items-baseline gap-1.5">
                                                <span class="text-xl sm:text-2xl font-bold tracking-tight" x-text="paymentAmount"></span>
                                                <span class="text-xs font-bold text-indigo-200">{{ $currencyLabel }}</span>
                                            </div>
                                            
                                            <button type="button" 
                                                    @click="copyAmount()" 
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold transition-all shadow-xs"
                                                    :class="copiedKey === 'amount' ? 'bg-emerald-500 text-white' : 'bg-white/20 hover:bg-white/30 text-white'">
                                                <template x-if="copiedKey === 'amount'">
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                        کپی شد!
                                                    </span>
                                                </template>
                                                <template x-if="copiedKey !== 'amount'">
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 012-2v-8a2 2 0 01-2-2h-8a2 2 0 01-2 2v8a2 2 0 012 2z" /></svg>
                                                        کپی مبلغ
                                                    </span>
                                                </template>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="self-start sm:self-auto shrink-0 px-3 py-1.5 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 text-xs font-bold text-white flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                        <span>رزرو #{{ $booking->booking_code }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- انتخاب روش پرداخت --}}
                            <div class="space-y-3">
                                <label class="block text-sm font-bold text-gray-800 dark:text-gray-200">انتخاب روش پرداخت <span class="text-red-500">*</span></label>
                                
                                <div class="grid grid-cols-1 gap-3">
                                    @foreach($availablePaymentMethods as $mId => $mLabel)
                                        <div @click="selectMethod('{{ $mId }}')"
                                             class="relative flex items-center p-4 border rounded-2xl cursor-pointer transition-all duration-200 select-none"
                                             :class="method === '{{ $mId }}' 
                                                ? 'bg-indigo-50/70 dark:bg-indigo-950/40 border-indigo-500 ring-2 ring-indigo-500/20 shadow-xs' 
                                                : 'bg-gray-50/60 dark:bg-gray-800/60 border-gray-200 dark:border-gray-700 hover:border-indigo-300'">
                                            
                                            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                                                 :class="method === '{{ $mId }}' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-white dark:bg-gray-700 text-gray-500 border border-gray-200 dark:border-gray-600'">
                                                @if($mId === 'online')
                                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                                @else
                                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" /></svg>
                                                @endif
                                            </div>

                                            <div class="mr-3 text-right flex-1">
                                                <span class="block text-sm font-bold text-gray-900 dark:text-white">{{ $mLabel }}</span>
                                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    @if($mId === 'online') تأیید آنی و اتصال به درگاه شاپرک با کارت‌های عضو شتاب
                                                    @else واریز به شماره کارت / شبا و ثبت اطلاعات فیش پرداختی
                                                    @endif
                                                </span>
                                            </div>

                                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all shrink-0"
                                                 :class="method === '{{ $mId }}' ? 'border-indigo-600 bg-indigo-600' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800'">
                                                <div class="w-2 h-2 rounded-full bg-white transition-transform" :class="method === '{{ $mId }}' ? 'scale-100' : 'scale-0'"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- بخش درگاه‌های آنلاین --}}
                            <div x-show="method === 'online' && currentSubItems.length > 0" class="space-y-3 pt-2">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">درگاه پرداخت اینترنتی را انتخاب کنید:</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <template x-for="item in currentSubItems" :key="item.id">
                                        <div @click="subItem = item.id"
                                             class="p-3.5 rounded-xl border text-center cursor-pointer transition-all text-xs font-bold flex items-center justify-center gap-2 select-none"
                                             :class="subItem === item.id ? 'bg-indigo-600 text-white border-indigo-600 shadow-md ring-2 ring-indigo-600/20' : 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 hover:border-indigo-300'">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                            <span x-text="item.label"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- 🏦 اسلایدر مدرن کارت‌های بانکی با دکمه‌های کپی مجزا --}}
                            <div x-show="method === 'transfer'" class="space-y-4 pt-2">
                                <div class="border-t border-gray-100 dark:border-gray-700 pt-4 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
                                            <label class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                                انتخاب کارت / حساب مقصد <span class="text-rose-500">*</span>
                                            </label>
                                        </div>

                                        @if(!empty($bankAccounts) && count($bankAccounts) > 1)
                                            <div class="flex items-center gap-2">
                                                <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 rounded-full border border-gray-200 dark:border-gray-700">
                                                    <span x-text="activeBankIndex + 1"></span> / {{ count($bankAccounts) }}
                                                </span>

                                                <button type="button" @click="prevBank()" class="p-1.5 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:bg-indigo-50 dark:hover:bg-indigo-950 transition-all active:scale-95 shadow-xs">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                                </button>

                                                <button type="button" @click="nextBank()" class="p-1.5 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:bg-indigo-50 dark:hover:bg-indigo-950 transition-all active:scale-95 shadow-xs">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                                                </button>
                                            </div>
                                        @endif
                                    </div>

                                    @if(!empty($bankAccounts))
                                        <div class="relative w-full overflow-x-auto snap-x snap-mandatory py-2 scroll-smooth custom-scrollbar" x-ref="bankTrack">
                                            <div class="flex items-center gap-3.5 sm:gap-4 px-[2%] min-w-full">
                                                @foreach($bankAccounts as $index => $acc)
                                                    @php
                                                        $cardGradients = [
                                                            0 => 'from-slate-900 via-indigo-950 to-blue-950',
                                                            1 => 'from-zinc-900 via-purple-950 to-slate-950',
                                                            2 => 'from-slate-900 via-emerald-950 to-teal-950',
                                                            3 => 'from-stone-900 via-rose-950 to-zinc-950',
                                                        ];
                                                        $bgGradient = $cardGradients[$index % 4];
                                                        $ownerName = $acc['owner_name'] ?? ($acc['owner'] ?? ($acc['name'] ?? ''));
                                                        $bankName = $acc['bank_name'] ?? 'بانک';
                                                        $cardNumber = !empty($acc['card_number']) ? preg_replace('/[^0-9]/', '', $acc['card_number']) : '';
                                                        $accountNumber = !empty($acc['account_number']) ? trim($acc['account_number']) : '';
                                                        $ibanNumber = !empty($acc['iban']) ? trim($acc['iban']) : '';
                                                        $accId = $acc['id'] ?? ($acc['bank_name'] ?? ('acc_' . $index));
                                                    @endphp
                                                    <div @click="selectBank({{ $index }}, '{{ $accId }}')"
                                                         class="snap-center shrink-0 w-[88%] sm:w-[350px] transition-all duration-300 select-none cursor-pointer"
                                                         :class="activeBankIndex === {{ $index }} ? 'scale-100 opacity-100 z-20' : 'scale-95 opacity-50 hover:opacity-80 z-10'">
                                                        
                                                        <div class="relative overflow-hidden rounded-2xl p-4 sm:p-5 flex flex-col justify-between text-white border-2 transition-all duration-300 min-h-[220px] shadow-xl"
                                                             :class="activeBankIndex === {{ $index }} ? 'border-indigo-400 ring-4 ring-indigo-500/30 shadow-indigo-500/25' : 'border-transparent'">

                                                            <div class="absolute inset-0 bg-gradient-to-br {{ $bgGradient }}"></div>
                                                            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/5 rounded-full blur-2xl"></div>

                                                            <div class="relative z-10 flex items-center justify-between border-b border-white/20 pb-2.5 mb-2">
                                                                <div>
                                                                    <span class="text-[11px] font-bold text-amber-200/90 block mb-0.5">نام صاحب حساب:</span>
                                                                    <h4 class="text-sm sm:text-base font-black text-white tracking-wide drop-shadow-md">
                                                                        {{ !empty($ownerName) ? $ownerName : $bankName }}
                                                                    </h4>
                                                                </div>

                                                                <div class="flex items-center gap-1.5">
                                                                    <span class="text-xs font-bold px-2 py-0.5 rounded-md backdrop-blur-md transition-all shadow-xs"
                                                                          :class="activeBankIndex === {{ $index }} ? 'bg-indigo-600 text-white shadow-md border border-indigo-400/50' : 'bg-white/20 text-white/80'">
                                                                        <span x-text="activeBankIndex === {{ $index }} ? 'انتخاب‌شده' : 'کارت ' + ({{ $index }} + 1)"></span>
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            <div class="relative z-10 space-y-2 my-1">
                                                                @if(!empty($cardNumber))
                                                                    <div class="flex items-center justify-between gap-2.5 bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/20">
                                                                        <span class="text-[11px] font-bold text-amber-200/90 shrink-0">شماره کارت:</span>
                                                                        <div class="flex items-center gap-2">
                                                                            <span class="text-xs sm:text-sm font-extrabold tracking-wider text-amber-300 block font-sans" dir="ltr">
                                                                                {{ implode(' - ', str_split($cardNumber, 4)) }}
                                                                            </span>
                                                                            <button type="button" @click.stop="copyToClipboard('{{ $cardNumber }}', 'card_{{ $index }}')" class="p-1 rounded-lg bg-white/15 hover:bg-white/30 text-white transition-all shrink-0 active:scale-90" title="کپی شماره کارت">
                                                                                <template x-if="copiedKey === 'card_{{ $index }}'"><svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></template>
                                                                                <template x-if="copiedKey !== 'card_{{ $index }}'"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 012-2v-8a2 2 0 01-2-2h-8a2 2 0 01-2 2v8a2 2 0 012 2z"/></svg></template>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                @if(!empty($accountNumber))
                                                                    <div class="flex items-center justify-between gap-2.5 bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/20">
                                                                        <span class="text-[11px] font-bold text-amber-200/90 shrink-0">شماره حساب:</span>
                                                                        <div class="flex items-center gap-2">
                                                                            <span class="text-xs sm:text-sm font-bold text-white block font-sans" dir="ltr">{{ $accountNumber }}</span>
                                                                            <button type="button" @click.stop="copyToClipboard('{{ $accountNumber }}', 'acc_{{ $index }}')" class="p-1 rounded-lg bg-white/15 hover:bg-white/30 text-white transition-all shrink-0 active:scale-90" title="کپی شماره حساب">
                                                                                <template x-if="copiedKey === 'acc_{{ $index }}'"><svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></template>
                                                                                <template x-if="copiedKey !== 'acc_{{ $index }}'"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 012-2v-8a2 2 0 01-2-2h-8a2 2 0 01-2 2v8a2 2 0 012 2z"/></svg></template>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                @if(!empty($ibanNumber))
                                                                    <div class="flex items-center justify-between gap-2.5 bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/20">
                                                                        <span class="text-[11px] font-bold text-amber-200/90 shrink-0">شماره شبا:</span>
                                                                        <div class="flex items-center gap-2 overflow-hidden">
                                                                            <span class="text-xs font-bold text-indigo-100 block truncate font-sans" dir="ltr">IR{{ $ibanNumber }}</span>
                                                                            <button type="button" @click.stop="copyToClipboard('IR{{ $ibanNumber }}', 'iban_{{ $index }}')" class="p-1 rounded-lg bg-white/15 hover:bg-white/30 text-white transition-all shrink-0 active:scale-90" title="کپی شماره شبا">
                                                                                <template x-if="copiedKey === 'iban_{{ $index }}'"><svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></template>
                                                                                <template x-if="copiedKey !== 'iban_{{ $index }}'"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 012-2v-8a2 2 0 01-2-2h-8a2 2 0 01-2 2v8a2 2 0 012 2z"/></svg></template>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- ورودی‌های واریزکننده و فیش رسید کارت‌به‌کارت --}}
                            <div x-show="method === 'transfer'" class="space-y-4 pt-2">
                                <div class="bg-gray-50 dark:bg-gray-800/60 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-4">
                                    <h4 class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5 border-b border-gray-200 dark:border-gray-700 pb-3">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        ثبت اطلاعات واریز و تصویر رسید
                                    </h4>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="payer_name" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نام صاحب حساب واریزکننده</label>
                                            <input type="text" name="payer_name" id="payer_name" value="{{ auth('client')->user()->full_name ?? '' }}" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-xs font-bold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans" placeholder="نام واریز کننده">
                                        </div>

                                        <div>
                                            <label for="payer_mobile" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">شماره موبایل واریزکننده</label>
                                            <input type="text" name="payer_mobile" id="payer_mobile" value="{{ auth('client')->user()->phone ?? '' }}" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-xs font-bold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans dir-ltr text-left" placeholder="0912...">
                                        </div>

                                        <div>
                                            <label for="tracking_code" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">شماره پیگیری / ارجاع بانکی <span class="text-red-500">*</span></label>
                                            <input type="text" name="tracking_code" id="tracking_code" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-xs font-bold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans" placeholder="مثال: 7654321" :required="method === 'transfer'">
                                        </div>

                                        <div>
                                            <label for="payment_date" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">تاریخ فیش / واریز</label>
                                            <input type="text" name="payment_date" id="payment_date" value="{{ CalendarUtils::convertNumbers(jdate()->format('Y/m/d')) }}" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3.5 py-2.5 text-xs font-bold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-sans dir-ltr text-left">
                                        </div>

                                        <div class="sm:col-span-2 pt-1">
                                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">تصویر یا فایل فیش رسید</label>
                                            <div class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-indigo-400 rounded-2xl p-4 text-center bg-white dark:bg-gray-800 transition-all">
                                                <input type="file" name="receipt_file" id="receipt_file" accept="image/*,.pdf" @change="handleFileSelect($event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                                
                                                <div class="flex flex-col items-center justify-center gap-1.5">
                                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                                    </div>
                                                    
                                                    <template x-if="!receiptFileName">
                                                        <div>
                                                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block">انتخاب تصویر فیش یا فایل رسید</span>
                                                            <span class="text-[10px] text-gray-400 mt-0.5 block">فرمت‌های مجاز: JPG, PNG, WEBP, PDF (حداکثر ۱۰ مگابایت)</span>
                                                        </div>
                                                    </template>

                                                    <template x-if="receiptFileName">
                                                        <div class="flex items-center gap-2 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 px-3 py-1.5 rounded-xl border border-emerald-200 dark:border-emerald-800">
                                                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                            <span class="text-xs font-bold truncate max-w-[250px]" x-text="receiptFileName"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        
                        {{-- Modal Footer --}}
                        <div class="p-5 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-100 dark:border-gray-700 shrink-0 flex flex-col sm:flex-row items-center justify-between gap-3 rounded-b-3xl">
                            <button @click="closeModal()" type="button" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-xs font-bold transition-all order-2 sm:order-1">
                                انصراف
                            </button>
                            
                            <button type="submit" 
                                    :disabled="!method"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl border border-transparent bg-indigo-600 text-white text-xs font-black shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all active:scale-95 order-1 sm:order-2">
                                <template x-if="method === 'online'">
                                    <span class="flex items-center gap-2">
                                        <span>انتقال به درگاه پرداخت شاپرک</span>
                                        <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                    </span>
                                </template>
                                <template x-if="method === 'transfer'">
                                    <span class="flex items-center gap-2">
                                        <span>ثبت فیش و ارسال برای تأیید</span>
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </span>
                                </template>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('clientPaymentModal', () => ({
            showPaymentModal: false,
            paymentId: {{ $booking->id }},
            paymentAmount: '{{ number_format($displayAmount) }}',
            rawAmountNumber: '{{ (string)(int)$displayAmount }}',
            availableMethods: @json(array_keys($availablePaymentMethods ?? [])),
            method: (@json(array_keys($availablePaymentMethods ?? [])))[0] || 'online',
            subItem: '{{ $onlineGateways[0]['id'] ?? '' }}',
            subItemsMap: @json($paymentSubItems ?? []),
            baseUrl: '{{ route('client.rental-bookings.pay', $booking->id) }}',
            activeBankIndex: 0,
            totalBankAccounts: {{ count($bankAccounts ?? []) }},
            copiedKey: null,
            receiptFileName: '',
            
            get formAction() {
                return this.baseUrl;
            },

            scrollToActiveBank() {
                this.$nextTick(() => {
                    if (this.$refs.bankTrack && this.$refs.bankTrack.children && this.$refs.bankTrack.children[0] && this.$refs.bankTrack.children[0].children[this.activeBankIndex]) {
                        this.$refs.bankTrack.children[0].children[this.activeBankIndex].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                    }
                });
            },

            nextBank() {
                if (this.totalBankAccounts > 0) {
                    this.activeBankIndex = (this.activeBankIndex + 1) % this.totalBankAccounts;
                    const items = this.subItemsMap['transfer'] || [];
                    if (items[this.activeBankIndex]) {
                        this.subItem = items[this.activeBankIndex].id;
                    }
                    this.scrollToActiveBank();
                }
            },

            prevBank() {
                if (this.totalBankAccounts > 0) {
                    this.activeBankIndex = (this.activeBankIndex - 1 + this.totalBankAccounts) % this.totalBankAccounts;
                    const items = this.subItemsMap['transfer'] || [];
                    if (items[this.activeBankIndex]) {
                        this.subItem = items[this.activeBankIndex].id;
                    }
                    this.scrollToActiveBank();
                }
            },

            selectBank(index, id) {
                this.activeBankIndex = index;
                this.subItem = id;
                this.scrollToActiveBank();
            },

            selectMethod(m) {
                this.method = m;
                const items = this.subItemsMap[m] || [];
                this.subItem = items.length > 0 ? items[0].id : '';
                this.activeBankIndex = 0;
                if (m === 'transfer') {
                    this.$nextTick(() => this.scrollToActiveBank());
                }
            },

            openModal(id, amount) {
                this.paymentId = id;
                this.paymentAmount = amount;
                this.rawAmountNumber = amount.toString().replace(/,/g, '').replace(/[^0-9]/g, '');
                
                const defaultMethod = (this.availableMethods && this.availableMethods.length > 0) ? this.availableMethods[0] : 'online';
                this.method = defaultMethod;
                this.activeBankIndex = 0;
                this.copiedKey = null;
                this.receiptFileName = '';
                
                const items = this.subItemsMap[this.method] || [];
                this.subItem = items.length > 0 ? items[0].id : '';
                
                const form = document.getElementById('clientRentalPaymentForm');
                if (form) form.reset();
                this.showPaymentModal = true;
                document.body.classList.add('overflow-hidden');
                setTimeout(() => this.scrollToActiveBank(), 150);
            },
            
            closeModal() {
                this.showPaymentModal = false;
                document.body.classList.remove('overflow-hidden');
            },

            get currentSubItems() {
                return this.subItemsMap[this.method] || [];
            },

            handleFileSelect(e) {
                const file = e.target.files[0];
                if (file) {
                    this.receiptFileName = file.name;
                }
            },

            copyAmount() {
                if (this.rawAmountNumber) {
                    this.copyToClipboard(this.rawAmountNumber, 'amount');
                }
            },

            copyToClipboard(text, key) {
                if (!text) return;
                const cleanText = text.toString().replace(/\s+/g, '');
                
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(cleanText).then(() => {
                        this.setCopiedState(key);
                    }).catch(() => {
                        this.fallbackCopy(cleanText, key);
                    });
                } else {
                    this.fallbackCopy(cleanText, key);
                }
            },

            fallbackCopy(text, key) {
                const textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.position = "fixed";
                textArea.style.opacity = "0";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    this.setCopiedState(key);
                } catch (err) {}
                document.body.removeChild(textArea);
            },

            setCopiedState(key) {
                this.copiedKey = key;
                setTimeout(() => {
                    if (this.copiedKey === key) {
                        this.copiedKey = null;
                    }
                }, 2500);
            }
        }));
    });
</script>
@endsection
