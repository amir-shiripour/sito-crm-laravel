@extends('clients::layouts.client')

@php
    $title = 'لیست سفارشات فروشگاه';
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
                سفارشات فروشگاه من
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">مشاهده، پیگیری وضعیت ارسال و جزئیات خریدهای شما از فروشگاه.</p>
        </div>
    </div>

    {{-- جدول لیست سفارشات --}}
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-right">
                <thead class="text-xs text-gray-500 bg-gray-50/50 dark:bg-gray-900/50 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 font-bold">شماره سفارش</th>
                        <th class="px-6 py-4 font-bold">مبلغ نهایی</th>
                        <th class="px-6 py-4 font-bold">تاریخ ثبت</th>
                        <th class="px-6 py-4 font-bold">وضعیت پرداخت</th>
                        <th class="px-6 py-4 font-bold">وضعیت ارسال</th>
                        <th class="px-6 py-4 font-bold"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer" onclick="window.location.href='{{ route('client.market.orders.show', $order->id) }}'">
                            <td class="px-6 py-4 text-gray-900 dark:text-white font-medium">
                                #{{ $order->tracking_code ?: $order->id }}
                            </td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white font-mono text-xs font-bold">
                                {{ number_format($order->grand_total ?? 0) }} تومان
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                {{ $order->created_at ? jdate($order->created_at)->format('Y/m/d H:i') : '---' }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $payStatusColor = match($order->payment_status) {
                                        'paid' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                        'pending', 'unpaid' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                        'failed' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        'refunded' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
                                        'canceled' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                                    };

                                    $payStatusLabel = match($order->payment_status) {
                                        'paid' => 'پرداخت شده',
                                        'pending', 'unpaid' => 'در انتظار پرداخت',
                                        'failed' => 'پرداخت ناموفق',
                                        'refunded' => 'استرداد شده',
                                        'canceled' => 'لغو شده',
                                        default => $order->payment_status
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium {{ $payStatusColor }}">
                                    {{ $payStatusLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $delStatusMap = [
                                        'pending' => ['label' => 'در انتظار تایید', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'],
                                        'processing' => ['label' => 'در حال پردازش', 'class' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400'],
                                        'shipped' => ['label' => 'ارسال شده', 'class' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-400'],
                                        'delivered' => ['label' => 'تحویل داده شده', 'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400'],
                                        'canceled' => ['label' => 'لغو شده', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400'],
                                    ];
                                    $ds = $delStatusMap[strtolower($order->delivery_status)] ?? ['label' => $order->delivery_status ?: 'نامشخص', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium {{ $ds['class'] }}">
                                    {{ $ds['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-left">
                                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                                    <svg class="w-16 h-16 text-gray-200 dark:text-gray-700 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-lg font-bold">هیچ سفارشی ثبت نشده است</p>
                                    <p class="text-xs mt-1 text-gray-400">سفارش‌های شما پس از ثبت خرید در اینجا نمایش داده خواهند شد.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
