@extends('clients::layouts.client')

@php
    $title = 'جزئیات صورت‌حساب فروشگاه #' . $order->id;

    $statusMap = [
        'pending' => 'PENDING',
        'paid' => 'PAID',
        'failed' => 'FAILED',
        'refunded' => 'REFUNDED',
        'canceled' => 'CANCELED'
    ];
    $normalizedStatus = $statusMap[strtolower($order->payment_status)] ?? strtoupper($order->payment_status);

    $payStatusColor = match($normalizedStatus) {
        'PAID' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'PENDING' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'FAILED' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        'REFUNDED' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
        'CANCELED' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        'CANCELLED' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
    };

    $payStatusLabel = match($normalizedStatus) {
        'PAID' => 'پرداخت شده',
        'PENDING' => 'در انتظار پرداخت',
        'FAILED' => 'ناموفق',
        'REFUNDED' => 'استرداد شده',
        'CANCELED' => 'لغو شده',
        'CANCELLED' => 'لغو شده',
        default => $normalizedStatus
    };

    // fallback for paid date for market (use updated_at when it's PAID)
    $paidDate = $normalizedStatus === 'PAID' ? $order->updated_at : null;

    // Map payment method to readable gateway name
    $gatewayName = match(strtolower((string)$order->payment_method)) {
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
        'online' => 'پرداخت آنلاین',
        '' => null,
        default => ucfirst($order->payment_method)
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
                صورت‌حساب سفارش فروشگاه
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- دکمه موقت برای مشاهده سفارش (در صورتی که روت آن وجود داشته باشد) --}}
            <a href="{{ route('client.market.orders.show', $order->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl transition-colors text-sm font-medium shadow-sm">
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                مشاهده سفارش
            </a>

            @if($normalizedStatus === 'PENDING')
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
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-purple-500 to-pink-600"></div>

        <div class="flex flex-col md:flex-row justify-between gap-8 mb-10 pb-8 border-b border-gray-100 dark:border-gray-700 border-dashed">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">شناسه سفارش</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white tracking-widest">#{{ $order->id }}</div>
                <div class="mt-4 inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-bold {{ $payStatusColor }}">
                    {{ $payStatusLabel }}
                </div>
            </div>

            <div class="text-right">
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">تاریخ ایجاد</div>
                <div class="font-medium text-gray-900 dark:text-white">{{ jdate($order->created_at)->format('Y/m/d H:i') }}</div>

                @if($normalizedStatus === 'PAID')
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
                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">کد پیگیری پرداخت (بانکی):</span>
                    <span class="font-medium text-gray-900 dark:text-white font-mono">{{ $order->payment_ref_id ?: ($order->transaction_id ?: '---') }}</span>
                </div>
            </div>
        </div>

        <div class="mb-10">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">جزئیات سفارش فروشگاه</h3>

            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-4 sm:p-6 border border-gray-100 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 dark:text-white">خرید کالا از فروشگاه</div>
                            <div class="text-sm text-gray-500 mt-1">تعداد اقلام: {{ $order->items ? $order->items->count() : '---' }} مورد</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col items-end gap-3 border-t border-gray-100 dark:border-gray-700 pt-6">
            <div class="flex justify-between w-full sm:w-1/2 text-sm">
                <span class="text-gray-500 dark:text-gray-400">مجموع اقلام:</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ number_format($order->total_items_price) }} تومان</span>
            </div>
            @if($order->total_shipping_cost > 0)
            <div class="flex justify-between w-full sm:w-1/2 text-sm">
                <span class="text-gray-500 dark:text-gray-400">هزینه ارسال:</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ number_format($order->total_shipping_cost) }} تومان</span>
            </div>
            @endif
            @if($order->total_discount > 0)
            <div class="flex justify-between w-full sm:w-1/2 text-sm text-emerald-600 dark:text-emerald-400">
                <span>تخفیف:</span>
                <span class="font-medium">{{ number_format($order->total_discount) }} تومان</span>
            </div>
            @endif
            <div class="w-full sm:w-1/2 border-t border-dashed border-gray-200 dark:border-gray-700 my-1"></div>
            <div class="flex justify-between w-full sm:w-1/2">
                <span class="text-gray-700 dark:text-gray-300 font-bold">مبلغ نهایی:</span>
                <span class="font-bold text-xl text-gray-900 dark:text-white">{{ number_format($order->grand_total) }} <span class="text-sm font-normal text-gray-500">تومان</span></span>
            </div>
        </div>

    </div>

</div>
@endsection
