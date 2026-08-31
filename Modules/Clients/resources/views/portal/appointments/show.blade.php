@extends('clients::layouts.client')

@php
    $title = 'جزئیات نوبت #' . $appointment->id;

    $appStatusColor = match($appointment->status) {
        'CONFIRMED' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'PENDING' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'PENDING_PAYMENT' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'CANCELED_BY_CLIENT', 'CANCELED_BY_ADMIN' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        'DONE' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
    };

    $appStatusLabel = match($appointment->status) {
        'CONFIRMED' => 'تایید شده',
        'PENDING' => 'در انتظار تایید',
        'PENDING_PAYMENT' => 'در انتظار پرداخت',
        'CANCELED_BY_CLIENT' => 'لغو توسط شما',
        'CANCELED_BY_ADMIN' => 'لغو توسط سیستم',
        'DONE' => 'انجام شده',
        'RESCHEDULED' => 'تغییر زمان',
        'NO_SHOW' => 'عدم حضور',
        'DRAFT' => 'پیش‌نویس',
        default => $appointment->status
    };

    $canCancel = in_array($appointment->status, [
        \Modules\Booking\Entities\Appointment::STATUS_CONFIRMED,
        \Modules\Booking\Entities\Appointment::STATUS_PENDING,
        \Modules\Booking\Entities\Appointment::STATUS_PENDING_PAYMENT,
        \Modules\Booking\Entities\Appointment::STATUS_RESCHEDULED
    ]);

    // Calculate Financials
    $service = $appointment->service;

    $basePrice = $service?->base_price ?: 0;
    $discountPrice = $service?->discount_price ?: 0;
    // قیمت خدمت به واحد پول تنظیمات (مثلاً تومان یا ریال)
    $totalPrice = $discountPrice > 0 ? $discountPrice : $basePrice;

    // booking_payments.amount همیشه به ریال (IRR) در دیتابیس ذخیره شده
    // اگر واحد تنظیمات تومان (IRT) باشد باید مبلغ پرداخت را تقسیم بر ۱۰ کنیم
    $paidSumIRR = $appointment->payments->where('status', 'PAID')->sum('amount');
    $totalPaid = ($bookingCurrencyUnit === 'IRT') ? ($paidSumIRR / 10) : $paidSumIRR;

    $remainingBalance = max(0, $totalPrice - $totalPaid);

    if ($service?->payment_mode === 'NONE' || $totalPrice == 0) {
        $financialStatusLabel = 'بدون نیاز به پرداخت آنلاین';
        $financialStatusColor = 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
    } elseif ($remainingBalance == 0) {
        $financialStatusLabel = 'تسویه شده';
        $financialStatusColor = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400';
    } elseif ($totalPaid > 0) {
        $financialStatusLabel = 'پرداخت بخشی از مبلغ';
        $financialStatusColor = 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
    } else {
        $financialStatusLabel = 'نیازمند پرداخت';
        $financialStatusColor = 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';
    }
@endphp

@section('content')
@includeIf('partials.jalali-date-picker')

<div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700" x-data="clientPaymentModal()">

    {{-- هدر صفحه --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <a href="{{ route('client.dashboard') }}" class="p-2 -m-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </a>
                جزئیات نوبت #{{ $appointment->id }}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">شناسه یکتای سیستم برای پیگیری رزرو شما</p>
        </div>

        @if($canCancel)
        <form action="{{ route('client.appointments.cancel', $appointment->id) }}" method="POST" onsubmit="return confirm('آیا از لغو این نوبت اطمینان دارید؟ این عمل غیرقابل بازگشت است.');">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-red-200 dark:border-red-900/50 text-red-600 dark:text-red-400 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-sm font-medium shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                لغو نوبت
            </button>
        </form>
        @endif
    </div>

    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/30 text-emerald-700 dark:text-emerald-400 flex items-center gap-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30 text-red-700 dark:text-red-400 flex items-center gap-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- وضعیت و زمان --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-3 bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-full {{ $appStatusColor }}">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        @if(in_array($appointment->status, ['CONFIRMED', 'DONE']))
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        @elseif(in_array($appointment->status, ['CANCELED_BY_CLIENT', 'CANCELED_BY_ADMIN']))
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        @endif
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm text-gray-500 dark:text-gray-400">وضعیت فعلی نوبت</h3>
                    <div class="font-bold text-lg mt-1 text-gray-900 dark:text-white">{{ $appStatusLabel }}</div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-6 text-left">
                <div>
                    <span class="text-xs text-gray-400 block mb-1">تاریخ مراجعه</span>
                    <span class="font-medium text-gray-900 dark:text-white flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        {{ $appointment->start_at_utc ? jdate($appointment->start_at_utc)->format('Y/m/d (l)') : '---' }}
                    </span>
                </div>
                <div>
                    <span class="text-xs text-gray-400 block mb-1">ساعت مراجعه</span>
                    <span class="font-medium text-gray-900 dark:text-white flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ $appointment->start_at_utc ? jdate($appointment->start_at_utc)->format('H:i') : '---' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ستون اطلاعات --}}
        <div class="md:col-span-2 space-y-6">

            {{-- اطلاعات سرویس و مالی --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                        اطلاعات سرویس و تسویه حساب
                    </h2>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium {{ $financialStatusColor }}">
                        {{ $financialStatusLabel }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pb-6 border-b border-gray-100 dark:border-gray-700">
                    <div>
                        <span class="block text-sm text-gray-500 dark:text-gray-400 mb-1">سرویس انتخابی</span>
                        <span class="font-medium text-gray-900 dark:text-white text-lg">{{ $service->name ?? '---' }}</span>
                    </div>

                    @if($showProviderInfo ?? true)
                    <div>
                        <span class="block text-sm text-gray-500 dark:text-gray-400 mb-1">{{ config('booking.labels.provider') }}</span>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </div>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $appointment->provider->full_name ?? $appointment->provider->name ?? '---' }}</span>
                        </div>
                    </div>
                    @endif

                    @if(($showServiceDescription ?? true) && !empty($service?->description))
                    <div class="col-span-1 sm:col-span-2 pt-3 border-t border-gray-100 dark:border-gray-700/60">
                        <span class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5">توضیحات سرویس</span>
                        <div class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed prose dark:prose-invert max-w-none">
                            {!! $service->description !!}
                        </div>
                    </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="flex flex-col">
                        <span class="text-sm text-gray-500 dark:text-gray-400 mb-1">مبلغ کل نوبت</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ number_format($totalPrice) }} <span class="text-xs text-gray-500">{{ $bookingCurrencyLabel }}</span></span>
                    </div>

                    <div class="flex flex-col border-t sm:border-t-0 sm:border-r border-gray-200 dark:border-gray-700 pt-3 sm:pt-0 sm:pr-4">
                        <span class="text-sm text-gray-500 dark:text-gray-400 mb-1">پرداخت شده</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($totalPaid) }} <span class="text-xs text-emerald-500/70">{{ $bookingCurrencyLabel }}</span></span>
                    </div>

                    <div class="flex flex-col border-t sm:border-t-0 sm:border-r border-gray-200 dark:border-gray-700 pt-3 sm:pt-0 sm:pr-4">
                        <span class="text-sm text-gray-500 dark:text-gray-400 mb-1">مبلغ باقیمانده</span>
                        <span class="font-bold {{ $remainingBalance > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white' }}">{{ number_format($remainingBalance) }} <span class="text-xs {{ $remainingBalance > 0 ? 'text-rose-500/70' : 'text-gray-500' }}">{{ $bookingCurrencyLabel }}</span></span>
                    </div>
                </div>
            </div>

            {{-- اطلاعات فرم (پاسخ‌ها) --}}
            @if(($showSupplementaryInfo ?? true) && !empty($formResponses))
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm space-y-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                    اطلاعات تکمیلی
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-5 gap-x-4">
                    @foreach($formResponses as $response)
                        <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mb-1.5">{{ $response['label'] }}</span>
                            @php $value = $response['value']; @endphp
                            <span class="font-medium text-gray-900 dark:text-white">
                                @if(is_array($value))
                                    {{ implode('، ', array_filter(array_map('strval', $value))) ?: '---' }}
                                @else
                                    {{ $value !== null && $value !== '' ? $value : '---' }}
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        {{-- ستون پرداختی‌ها --}}
        <div class="md:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col h-full sticky top-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    تاریخچه پرداخت‌های نوبت
                </h2>

                @if($appointment->payments->isEmpty())
                    <div class="flex-1 flex flex-col items-center justify-center text-center p-6 bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                        <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">بدون صورت‌حساب</span>
                        <span class="text-xs text-gray-500 dark:text-gray-500">هنوز پرداختی برای این نوبت ثبت نشده است.</span>
                    </div>
                @else
                    <div class="space-y-3 flex-1 overflow-y-auto pr-1">
                        @foreach($appointment->payments as $payment)
                            @php
                                $payStatusColor = match($payment->status) {
                                    'PAID' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                    'PENDING' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                    'FAILED' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                    'REFUNDED' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
                                    'CANCELED' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                    'CANCELLED' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                                };

                                $payStatusLabel = match($payment->status) {
                                    'PAID' => 'پرداخت شده',
                                    'PENDING' => 'در انتظار پرداخت',
                                    'FAILED' => 'ناموفق',
                                    'REFUNDED' => 'استرداد شده',
                                    'CANCELED' => 'لغو شده',
                                    'CANCELLED' => 'لغو شده',
                                    default => $payment->status
                                };
                            @endphp
                            <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col gap-3 transition-colors hover:border-emerald-200 dark:hover:border-emerald-800">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex flex-col">
                                        @php
                                            // booking_payments.amount به IRR ذخیره شده - نمایش با واحد تنظیمات
                                            $displayAmount = ($bookingCurrencyUnit === 'IRT') ? ($payment->amount / 10) : $payment->amount;
                                        @endphp
                                        <span class="font-bold text-gray-900 dark:text-white text-lg">{{ number_format($displayAmount) }} <span class="text-xs font-normal text-gray-500">{{ $bookingCurrencyLabel }}</span></span>
                                        <span class="text-xs text-gray-400 mt-0.5">{{ jdate($payment->created_at)->format('Y/m/d H:i') }}</span>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-medium {{ $payStatusColor }}">{{ $payStatusLabel }}</span>
                                </div>

                                <div class="flex items-center gap-2 mt-1">
                                    <a href="{{ route('client.payments.show', ['type' => 'booking', 'id' => $payment->id]) }}" class="flex-1 text-center px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-medium rounded-lg transition-colors shadow-sm">
                                        مشاهده جزئیات رسید
                                    </a>
                                    @if($payment->status === 'PENDING')
                                        <button @click.prevent="openModal({{ $payment->id }}, '{{ number_format($displayAmount) }}')" type="button" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-lg transition-colors shadow-sm">
                                            پرداخت
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- مودال اختصاصی تسویه/پرداخت نوبت (کارت بانکی هوشمند + آپلود رسید + کپی مبلغ و اطلاعات) --}}
    <div x-show="showPaymentModal"
         class="relative z-[150]"
         aria-labelledby="modal-title"
         role="dialog"
         aria-modal="true"
         style="display: none;">

        {{-- Backdrop --}}
        <div x-show="showPaymentModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @touchmove.prevent></div>

        <div class="fixed inset-0 z-10 overflow-y-auto overscroll-contain">
            <div class="flex min-h-full items-end justify-center p-3 text-center sm:items-center sm:p-4">
                <div x-show="showPaymentModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-3xl bg-white text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl md:max-w-2xl border border-gray-100 flex flex-col max-h-[90vh] min-h-0">

                    {{-- Modal Header --}}
                    <div class="px-6 py-5 bg-gray-50/80 border-b border-gray-100 flex justify-between items-center shrink-0">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-sm border border-indigo-100">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900" id="modal-title">تکمیل و تسویه صورت‌حساب نوبت</h3>
                                <p class="text-xs text-gray-500 mt-0.5">روش پرداخت دلخواه را انتخاب کرده و اطلاعات را تکمیل کنید</p>
                            </div>
                        </div>
                        <button @click="closeModal()" type="button" class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                            <span class="sr-only">بستن</span>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form :action="formAction" method="POST" enctype="multipart/form-data" id="clientPaymentForm" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                        @csrf
                        <input type="hidden" name="payment_method" :value="method">
                        <input type="hidden" name="sub_item" :value="subItem">

                        {{-- Modal Body --}}
                        <div class="p-6 space-y-6 flex-1 min-h-0 overflow-y-auto custom-scrollbar overscroll-contain">

                            {{-- کارت خلاصه مبلغ با دکمه کپی عدد خالص --}}
                            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-800 p-4 sm:p-5 text-white shadow-lg shadow-indigo-600/20">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 relative z-10">
                                    <div>
                                        <span class="text-xs font-medium text-indigo-100/90 block mb-1">مبلغ قابل پرداخت صورت‌حساب</span>
                                        <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
                                            <div class="flex items-baseline gap-1.5">
                                                <span class="text-xl sm:text-2xl font-bold tracking-tight" x-text="paymentAmount"></span>
                                                <span class="text-xs font-bold text-indigo-200">{{ $bookingCurrencyLabel }}</span>
                                            </div>

                                            {{-- دکمه کپی مبلغ بدون ویرگول --}}
                                            <button type="button"
                                                    @click="copyAmount()"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold transition-all shadow-sm"
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
                                        <span>صورت‌حساب #{{ $appointment->id }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- انتخاب روش پرداخت (کارت‌های تعاملی) --}}
                            <div class="space-y-3">
                                <label class="block text-sm font-bold text-gray-800">انتخاب روش پرداخت <span class="text-red-500">*</span></label>

                                <div class="grid grid-cols-1 gap-3">
                                    @foreach($availablePaymentMethods as $mId => $mLabel)
                                        <div @click="selectMethod('{{ $mId }}')"
                                             class="relative flex items-center p-4 border rounded-2xl cursor-pointer transition-all duration-200 select-none"
                                             :class="method === '{{ $mId }}'
                                                ? 'bg-indigo-50/70 border-indigo-500 ring-2 ring-indigo-500/20 shadow-sm'
                                                : 'bg-gray-50/60 border-gray-200 hover:border-indigo-300'">

                                            {{-- آیکون متناسب با روش --}}
                                            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                                                 :class="method === '{{ $mId }}' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-white text-gray-500 border border-gray-200'">
                                                @if($mId === 'online')
                                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                                @elseif($mId === 'transfer')
                                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" /></svg>
                                                @elseif($mId === 'pos')
                                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                                @elseif($mId === 'installment')
                                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                @else
                                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                                @endif
                                            </div>

                                            <div class="mr-3 text-right flex-1">
                                                <span class="block text-sm font-bold text-gray-900">{{ $mLabel }}</span>
                                                <span class="block text-xs text-gray-500 mt-0.5">
                                                    @if($mId === 'online') تایید آنی و اتصال به درگاه شتاب
                                                    @elseif($mId === 'transfer') کارت به کارت یا واریز شبا و ثبت فیش
                                                    @elseif($mId === 'pos') پرداختی حضوری از طریق کارتخوان
                                                    @elseif($mId === 'installment') تسویه به صورت اقساطی یا با چک
                                                    @else پرداخت به صورت نقدی @endif
                                                </span>
                                            </div>

                                            {{-- دایره رادیو باتن --}}
                                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all shrink-0"
                                                 :class="method === '{{ $mId }}' ? 'border-indigo-600 bg-indigo-600' : 'border-gray-300 bg-white'">
                                                <div class="w-2 h-2 rounded-full bg-white transition-transform" :class="method === '{{ $mId }}' ? 'scale-100' : 'scale-0'"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- بخش درگاه‌های آنلاین (پیش‌فرض انتخاب‌شده) --}}
                            <div x-show="method === 'online' && currentSubItems.length > 0" x-collapse class="space-y-3 pt-2">
                                <label class="block text-xs font-bold text-gray-700">درگاه پرداخت مورد نظر را انتخاب کنید:</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <template x-for="item in currentSubItems" :key="item.id">
                                        <div @click="subItem = item.id"
                                             class="p-3.5 rounded-xl border text-center cursor-pointer transition-all text-xs font-bold flex items-center justify-center gap-2 select-none"
                                             :class="subItem === item.id ? 'bg-indigo-600 text-white border-indigo-600 shadow-md ring-2 ring-indigo-600/20' : 'bg-gray-50 border-gray-200 text-gray-800 hover:border-indigo-300'">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                            <span x-text="item.label"></span>
                                        </div>
                                    </template>
                                </div>
                                <input type="hidden" name="sub_item" :value="subItem">
                            </div>

                            {{-- 🏦 اسلایدر/کاروسل کارت‌های بانکی جهت واریز (طراحی یکسان با ماژول مارکت) --}}
                            <div x-show="method === 'transfer'" x-collapse class="space-y-4 pt-2">
                                <div class="border-t border-gray-100 pt-4 space-y-3">

                                    {{-- Carousel Header & Nav Controls --}}
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
                                            <label class="text-xs font-bold text-gray-800">
                                                انتخاب کارت / حساب مقصد <span class="text-rose-500">*</span>
                                            </label>
                                        </div>

                                        @if(!empty($bankAccounts) && count($bankAccounts) > 1)
                                            <div class="flex items-center gap-2">
                                                <span class="text-[11px] font-bold text-gray-600 bg-gray-100 px-2.5 py-0.5 rounded-full border border-gray-200">
                                                    <span x-text="activeBankIndex + 1"></span> / {{ count($bankAccounts) }}
                                                </span>

                                                <button type="button" @click="prevBank()"
                                                        class="p-1.5 rounded-xl bg-white text-gray-700 border border-gray-200 hover:bg-indigo-50 transition-all active:scale-95 shadow-xs">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </button>

                                                <button type="button" @click="nextBank()"
                                                        class="p-1.5 rounded-xl bg-white text-gray-700 border border-gray-200 hover:bg-indigo-50 transition-all active:scale-95 shadow-xs">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                                    </svg>
                                                </button>
                                            </div>
                                        @endif
                                    </div>

                                    @if(!empty($bankAccounts))
                                        {{-- Peeking Carousel Track with Native Scroll-Snap --}}
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

                                                            <!-- Background Gradient & Glow -->
                                                            <div class="absolute inset-0 bg-gradient-to-br {{ $bgGradient }}"></div>
                                                            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/5 rounded-full blur-2xl"></div>

                                                            {{-- Header: Account Owner Name --}}
                                                            <div class="relative z-10 flex items-center justify-between border-b border-white/20 pb-2.5 mb-2">
                                                                <div>
                                                                    <span class="text-[11px] font-bold text-amber-200/90 block mb-0.5">نام صاحب حساب:</span>
                                                                    <h4 class="text-sm sm:text-base font-black text-white tracking-wide drop-shadow-md">
                                                                        {{ !empty($ownerName) ? $ownerName : $bankName }}
                                                                    </h4>
                                                                </div>

                                                                <!-- Selected Badge & Bank Name -->
                                                                <div class="flex items-center gap-1.5">
                                                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white/15 text-amber-300 border border-white/20">
                                                                        {{ $bankName }}
                                                                    </span>
                                                                    <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full transition-all"
                                                                          :class="activeBankIndex === {{ $index }} ? 'bg-indigo-600 text-white shadow-md border border-indigo-400/50' : 'bg-white/20 text-white/80'">
                                                                        <span x-text="activeBankIndex === {{ $index }} ? 'انتخاب‌شده' : 'کارت ' + ({{ $index }} + 1)"></span>
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            {{-- Middle Section: Numbers Left-Aligned with Orderly High-Legibility Boxes --}}
                                                            <div class="relative z-10 space-y-2 my-1">
                                                                <!-- Card Number -->
                                                                @if(!empty($cardNumber))
                                                                    <div class="flex items-center justify-between gap-2.5 bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/20">
                                                                        <span class="text-[11px] font-bold text-amber-200/90 shrink-0">شماره کارت:</span>
                                                                        <div class="flex items-center gap-2">
                                                                            <span class="text-xs sm:text-sm font-extrabold tracking-wider text-amber-300 block" dir="ltr">
                                                                                {{ implode(' - ', str_split($cardNumber, 4)) }}
                                                                            </span>
                                                                            <button type="button"
                                                                                    @click.stop="copyToClipboard('{{ $cardNumber }}', 'card_{{ $index }}')"
                                                                                    title="کپی شماره کارت"
                                                                                    class="p-1 rounded-lg bg-white/15 hover:bg-white/30 text-white transition-all shrink-0 active:scale-90">
                                                                                <template x-if="copiedKey === 'card_{{ $index }}'">
                                                                                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                                                </template>
                                                                                <template x-if="copiedKey !== 'card_{{ $index }}'">
                                                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 012-2v-8a2 2 0 01-2-2h-8a2 2 0 01-2 2v8a2 2 0 012 2z"/></svg>
                                                                                </template>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                <!-- Account Number -->
                                                                @if(!empty($accountNumber))
                                                                    <div class="flex items-center justify-between gap-2.5 bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/20">
                                                                        <span class="text-[11px] font-bold text-amber-200/90 shrink-0">شماره حساب:</span>
                                                                        <div class="flex items-center gap-2">
                                                                            <span class="text-xs sm:text-sm font-bold text-white block" dir="ltr">
                                                                                {{ $accountNumber }}
                                                                            </span>
                                                                            <button type="button"
                                                                                    @click.stop="copyToClipboard('{{ $accountNumber }}', 'acc_{{ $index }}')"
                                                                                    title="کپی شماره حساب"
                                                                                    class="p-1 rounded-lg bg-white/15 hover:bg-white/30 text-white transition-all shrink-0 active:scale-90">
                                                                                <template x-if="copiedKey === 'acc_{{ $index }}'">
                                                                                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                                                </template>
                                                                                <template x-if="copiedKey !== 'acc_{{ $index }}'">
                                                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 012-2v-8a2 2 0 01-2-2h-8a2 2 0 01-2 2v8a2 2 0 012 2z"/></svg>
                                                                                </template>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                <!-- IBAN / Sheba -->
                                                                @if(!empty($ibanNumber))
                                                                    <div class="flex items-center justify-between gap-2.5 bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/20">
                                                                        <span class="text-[11px] font-bold text-amber-200/90 shrink-0">شماره شبا:</span>
                                                                        <div class="flex items-center gap-2 overflow-hidden">
                                                                            <span class="text-xs font-bold text-indigo-100 block truncate" dir="ltr">
                                                                                IR{{ $ibanNumber }}
                                                                            </span>
                                                                            <button type="button"
                                                                                    @click.stop="copyToClipboard('IR{{ $ibanNumber }}', 'iban_{{ $index }}')"
                                                                                    title="کپی شماره شبا"
                                                                                    class="p-1 rounded-lg bg-white/15 hover:bg-white/30 text-white transition-all shrink-0 active:scale-90">
                                                                                <template x-if="copiedKey === 'iban_{{ $index }}'">
                                                                                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                                                </template>
                                                                                <template x-if="copiedKey !== 'iban_{{ $index }}'">
                                                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 012-2v-8a2 2 0 01-2-2h-8a2 2 0 01-2 2v8a2 2 0 012 2z"/></svg>
                                                                                </template>
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
                                    @else
                                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-800 font-bold">
                                            لطفاً مبلغ صورت‌حساب را به شماره حساب مجموعه واریز کرده و فیش آن را ثبت کنید.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- ورودی‌های اطلاعات واریزکننده و تصویر رسید --}}
                            <div x-show="method === 'transfer' || method === 'pos' || method === 'installment'" x-collapse class="space-y-4 pt-2">
                                <div class="bg-gray-50 p-5 rounded-2xl border border-gray-200 space-y-4">
                                    <h4 class="text-xs font-bold text-gray-800 flex items-center gap-1.5 border-b border-gray-200 pb-3">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        ثبت اطلاعات واریز و تصویر رسید
                                    </h4>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        {{-- نام واریزکننده (خودکار پر شده) --}}
                                        <div>
                                            <label for="payer_name" class="block text-xs font-bold text-gray-700 mb-1.5">نام و نام خانوادگی واریزکننده <span class="text-red-500">*</span></label>
                                            <input type="text" name="payer_name" id="payer_name" value="{{ $appointment->client->full_name ?? '' }}" class="w-full rounded-xl border-gray-200 bg-white text-gray-900 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-bold" placeholder="نام صاحب حساب واریز کننده">
                                        </div>

                                        {{-- شماره تماس واریزکننده (خودکار پر شده) --}}
                                        <div>
                                            <label for="payer_mobile" class="block text-xs font-bold text-gray-700 mb-1.5">شماره تماس واریزکننده <span class="text-red-500">*</span></label>
                                            <input type="text" name="payer_mobile" id="payer_mobile" value="{{ $appointment->client->mobile ?? $appointment->client->phone ?? '' }}" class="w-full rounded-xl border-gray-200 bg-white text-gray-900 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-bold text-left" dir="ltr" placeholder="09123456789">
                                        </div>

                                        {{-- کد پیگیری --}}
                                        <div>
                                            <label for="tracking_code" class="block text-xs font-bold text-gray-700 mb-1.5">شماره پیگیری / کد ارجاع <span class="text-red-500">*</span></label>
                                            <input type="text" name="tracking_code" id="tracking_code" class="w-full rounded-xl border-gray-200 bg-white text-gray-900 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-bold" placeholder="مثال: 7654321">
                                        </div>

                                        {{-- تاریخ فیش (پیش‌فرض امروز و غیرقابل انتخاب تاریخ گذشته) --}}
                                        <div>
                                            <label for="payment_date" class="block text-xs font-bold text-gray-700 mb-1.5">تاریخ فیش / واریز <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <input data-jdp-only-date
                                                       data-jdp-min-date="today"
                                                       type="text"
                                                       name="payment_date"
                                                       id="payment_date"
                                                       value="{{ jdate()->format('Y/m/d') }}"
                                                       class="w-full rounded-xl border-gray-200 bg-white text-gray-900 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all text-left font-bold"
                                                       dir="ltr"
                                                       placeholder="1402/05/20"
                                                       required>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- فیلد آپلود رسید بانکی (الزامی) --}}
                                        <div class="sm:col-span-2 pt-1">
                                            <label class="block text-xs font-bold text-gray-700 mb-1.5">تصویر یا فایل رسید پرداخت <span class="text-red-500">*</span></label>
                                            <div class="relative border-2 border-dashed border-gray-300 hover:border-indigo-400 rounded-2xl p-4 text-center bg-white transition-all">
                                                <input type="file"
                                                       name="receipt_file"
                                                       id="receipt_file"
                                                       accept="image/*,.pdf"
                                                       :required="method !== 'online'"
                                                       @change="handleFileSelect($event)"
                                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                                                <div class="flex flex-col items-center justify-center gap-1.5">
                                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                                    </div>

                                                    <template x-if="!receiptFileName">
                                                        <div>
                                                            <span class="text-xs font-bold text-gray-700 block">انتخاب تصویر فیش یا فایل رسید <span class="text-red-500">(الزامی)</span></span>
                                                            <span class="text-[10px] text-gray-400 mt-0.5 block">فرمت‌های مجاز: JPG, PNG, WEBP, PDF (حداکثر ۱۰ مگابایت)</span>
                                                        </div>
                                                    </template>

                                                    <template x-if="receiptFileName">
                                                        <div class="flex items-center gap-2 bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-xl border border-emerald-200">
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
                        <div class="p-5 bg-gray-50 border-t border-gray-100 shrink-0 flex flex-col sm:flex-row items-center justify-between gap-3 rounded-b-3xl">
                            <button @click="closeModal()" type="button" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 hover:bg-gray-100 text-sm font-bold transition-all order-2 sm:order-1">
                                انصراف
                            </button>

                            <button type="submit"
                                    :disabled="!method"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl border border-transparent bg-indigo-600 text-white text-sm font-bold shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all active:scale-95 order-1 sm:order-2">
                                <template x-if="method === 'online'">
                                    <span class="flex items-center gap-2">
                                        <span>انتقال به درگاه پرداخت آنلاین</span>
                                        <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                    </span>
                                </template>
                                <template x-if="method === 'transfer'">
                                    <span class="flex items-center gap-2">
                                        <span>ثبت فیش و ارسال برای تایید</span>
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </span>
                                </template>
                                <template x-if="method !== 'online' && method !== 'transfer'">
                                    <span>تایید و ثبت نهایی پرداخت</span>
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
            paymentId: null,
            paymentAmount: '0',
            rawAmountNumber: '0',
            availableMethods: @json(array_keys($availablePaymentMethods ?? [])),
            method: (@json(array_keys($availablePaymentMethods ?? [])))[0] || 'online',
            subItem: '',
            subItemsMap: @json($paymentSubItems ?? []),
            baseUrl: '{{ url("clients/payments") }}',
            activeBankIndex: 0,
            totalBankAccounts: {{ count($bankAccounts ?? []) }},
            copiedKey: null,
            receiptFileName: '',

            lockScroll() {
                document.body.classList.add('overflow-hidden');
            },

            unlockScroll() {
                document.body.classList.remove('overflow-hidden');
            },

            init() {
                this.$watch('showPaymentModal', (val) => {
                    if (val) {
                        this.lockScroll();
                    } else {
                        this.unlockScroll();
                    }
                });
                
                // اطمینان از آزاد شدن اسکرول در صورت خروج ناگهانی
                window.addEventListener('beforeunload', () => {
                    this.unlockScroll();
                });
            },

            get formAction() {
                if(!this.paymentId) return '#';
                return this.baseUrl + '/' + this.paymentId + '/process';
            },

            scrollToActiveBank() {
                this.$nextTick(() => {
                    const track = this.$refs.bankTrack;
                    if (track && track.children && track.children[0] && track.children[0].children[this.activeBankIndex]) {
                        const card = track.children[0].children[this.activeBankIndex];
                        const targetLeft = card.offsetLeft - (track.clientWidth / 2) + (card.clientWidth / 2);
                        track.scrollTo({ left: targetLeft, behavior: 'smooth' });
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
                this.lockScroll();
                if (m === 'transfer') {
                    this.$nextTick(() => this.scrollToActiveBank());
                }
            },

            openModal(id, amount) {
                this.paymentId = id;
                this.paymentAmount = amount;
                // Save raw amount without commas for copying
                this.rawAmountNumber = amount.toString().replace(/,/g, '').replace(/[^0-9]/g, '');

                const defaultMethod = (this.availableMethods && this.availableMethods.length > 0) ? this.availableMethods[0] : 'online';
                this.method = defaultMethod;
                this.activeBankIndex = 0;
                this.copiedKey = null;
                this.receiptFileName = '';

                const items = this.subItemsMap[this.method] || [];
                this.subItem = items.length > 0 ? items[0].id : '';

                const form = document.getElementById('clientPaymentForm');
                if (form) form.reset();
                this.showPaymentModal = true;
                this.lockScroll();
                setTimeout(() => this.scrollToActiveBank(), 150);
            },

            closeModal() {
                this.showPaymentModal = false;
                this.paymentId = null;
                this.unlockScroll();
            },

            hasSubItems() {
                return this.currentSubItems.length > 0;
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
