@extends('clients::layouts.client')

@php
    $title = 'جزئیات صورت‌حساب نوبت #' . $payment->id;

    $payStatusColor = match($payment->status) {
        'PAID' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'PENDING' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'FAILED' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        'REFUNDED' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
        'CANCELED' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
    };

    $payStatusLabel = match($payment->status) {
        'PAID' => 'پرداخت شده',
        'PENDING' => 'در انتظار پرداخت',
        'FAILED' => 'ناموفق',
        'REFUNDED' => 'استرداد شده',
        'CANCELED' => 'لغو شده',
        default => $payment->status
    };

    // fallback for paid_at if it's null but status is PAID
    $paidDate = $payment->paid_at ?? ($payment->status === 'PAID' ? $payment->updated_at : null);

    // Map payment mode to readable gateway name
    $gatewayName = match(strtolower((string)$payment->mode)) {
        'zibal' => 'زیبال',
        'zarinpal' => 'زرین‌پال',
        'payping' => 'پی‌پینگ',
        'idpay' => 'آیدی‌پی',
        'sadad' => 'سداد (بانک ملی)',
        'mellat', 'behpardakht' => 'به‌پرداخت (بانک ملت)',
        'saman' => 'سامان کیش',
        'parsian' => 'پارسارگاد / پارسیان',
        'wallet' => 'کیف پول',
        'cash' => 'پرداخت نقدی / حضوری',
        'online', 'required' => 'پرداخت آنلاین (زرین‌پال)',
        '' => null,
        default => ucfirst($payment->mode)
    };
@endphp

@section('content')
<div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-4xl mx-auto">

    {{-- هدر صفحه --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <a href="{{ route('client.payments.index') }}" class="p-2 -m-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </a>
                صورت‌حساب مالی نوبت
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if($payment->appointment_id)
            <a href="{{ route('client.appointments.show', $payment->appointment_id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl transition-colors text-sm font-medium shadow-sm">
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                مشاهده نوبت
            </a>
            @endif

            @if($payment->status === 'PENDING')
            <button class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl transition-colors text-sm font-bold shadow-md shadow-emerald-500/20">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                پرداخت آنلاین
            </button>
            @endif
        </div>
    </div>

    {{-- کارت اصلی رسید --}}
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-10 border border-gray-100 dark:border-gray-700 shadow-xl shadow-gray-200/40 dark:shadow-none relative overflow-hidden">

        {{-- دکوراتیو بالا --}}
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-500 to-indigo-600"></div>

        <div class="flex flex-col md:flex-row justify-between gap-8 mb-10 pb-8 border-b border-gray-100 dark:border-gray-700 border-dashed">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">شناسه سیستم</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white tracking-widest">#{{ $payment->id }}</div>
                <div class="mt-4 inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-bold {{ $payStatusColor }}">
                    {{ $payStatusLabel }}
                </div>
            </div>

            <div class="text-right">
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">تاریخ ایجاد</div>
                <div class="font-medium text-gray-900 dark:text-white">{{ jdate($payment->created_at)->format('Y/m/d H:i') }}</div>

                @if($payment->status === 'PAID')
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-4 mb-1">تاریخ پرداخت</div>
                    <div class="font-medium text-gray-900 dark:text-white">{{ $paidDate ? jdate($paidDate)->format('Y/m/d H:i') : '---' }}</div>
                @endif
            </div>
        </div>

        <div class="mb-10">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">اطلاعات تراکنش</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">روش / درگاه پرداخت:</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $gatewayName ?: 'ثبت نشده' }}</span>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 flex flex-col justify-center overflow-hidden">
                    <span class="text-sm text-gray-500 dark:text-gray-400 mb-1">کد ارجاع بانکی:</span>
                    <span class="font-medium text-gray-900 dark:text-white font-mono text-left text-xs sm:text-sm break-all" dir="ltr">
                        {{ $payment->gateway_ref ?? $payment->transaction_ref ?? '---' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="mb-10">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">جزئیات آیتم‌ها</h3>

            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-4 sm:p-6 border border-gray-100 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 dark:text-white">هزینه رزرو سرویس: {{ optional($payment->appointment->service)->name ?? '---' }}</div>
                            <div class="text-sm text-gray-500 mt-1">ارائه‌دهنده: {{ optional($payment->appointment->provider)->full_name ?? optional($payment->appointment->provider)->name ?? '---' }}</div>
                        </div>
                    </div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($payment->amount) }} <span class="text-sm font-normal text-gray-500">تومان</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col items-end gap-2 border-t border-gray-100 dark:border-gray-700 pt-6">
            <div class="flex justify-between w-full sm:w-1/2">
                <span class="text-gray-500 dark:text-gray-400">جمع کل مبلغ:</span>
                <span class="font-bold text-xl text-gray-900 dark:text-white">{{ number_format($payment->amount) }} <span class="text-sm font-normal text-gray-500">تومان</span></span>
            </div>
        </div>

    </div>

</div>
@endsection
