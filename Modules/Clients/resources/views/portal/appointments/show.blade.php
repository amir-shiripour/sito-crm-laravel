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
        'PENDING' => 'در انتظار بررسی',
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
    $totalPrice = $discountPrice > 0 ? $discountPrice : $basePrice;

    $totalPaid = $appointment->payments->where('status', 'PAID')->sum('amount');
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
<div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">

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

                    <div>
                        <span class="block text-sm text-gray-500 dark:text-gray-400 mb-1">ارائه‌دهنده (پزشک/متخصص)</span>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </div>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $appointment->provider->full_name ?? $appointment->provider->name ?? '---' }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="flex flex-col">
                        <span class="text-sm text-gray-500 dark:text-gray-400 mb-1">مبلغ کل نوبت</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ number_format($totalPrice) }} <span class="text-xs text-gray-500">تومان</span></span>
                    </div>

                    <div class="flex flex-col border-t sm:border-t-0 sm:border-r border-gray-200 dark:border-gray-700 pt-3 sm:pt-0 sm:pr-4">
                        <span class="text-sm text-gray-500 dark:text-gray-400 mb-1">پرداخت شده</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($totalPaid) }} <span class="text-xs text-emerald-500/70">تومان</span></span>
                    </div>

                    <div class="flex flex-col border-t sm:border-t-0 sm:border-r border-gray-200 dark:border-gray-700 pt-3 sm:pt-0 sm:pr-4">
                        <span class="text-sm text-gray-500 dark:text-gray-400 mb-1">مبلغ باقیمانده</span>
                        <span class="font-bold {{ $remainingBalance > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white' }}">{{ number_format($remainingBalance) }} <span class="text-xs {{ $remainingBalance > 0 ? 'text-rose-500/70' : 'text-gray-500' }}">تومان</span></span>
                    </div>
                </div>
            </div>

            {{-- اطلاعات فرم (پاسخ‌ها) --}}
            @if(!empty($formResponses))
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
                                        <span class="font-bold text-gray-900 dark:text-white text-lg">{{ number_format($payment->amount) }} <span class="text-xs font-normal text-gray-500">تومان</span></span>
                                        <span class="text-xs text-gray-400 mt-0.5">{{ jdate($payment->created_at)->format('Y/m/d H:i') }}</span>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-medium {{ $payStatusColor }}">{{ $payStatusLabel }}</span>
                                </div>

                                <div class="flex items-center gap-2 mt-1">
                                    <a href="{{ route('client.payments.show', ['type' => 'booking', 'id' => $payment->id]) }}" class="flex-1 text-center px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-medium rounded-lg transition-colors shadow-sm">
                                        مشاهده جزئیات رسید
                                    </a>
                                    @if($payment->status === 'PENDING')
                                        <a href="#" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-lg transition-colors shadow-sm">
                                            پرداخت
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>
@endsection
