@extends('layouts.user')

@php
    $title = 'سفارشات فروشگاه';
@endphp

@section('content')
    <div class="space-y-4">
        {{-- هدر --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">سفارشات ثبت شده</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">پیگیری و مدیریت فاکتورهای خریداران.</p>
            </div>
        </div>

        {{-- جدول سفارشات --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-sm text-right">
                    <thead class="bg-gray-50/50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">شماره سفارش</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">مشتری</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">مبلغ کل (تومان)</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">وضعیت پرداخت</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">وضعیت ارسال</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">تاریخ</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-left pl-6">جزئیات</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($orders ?? [] as $order)
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors duration-150">
                            <td class="px-4 py-3 font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                #ORD-{{ $order->id }}
                            </td>
                            <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                {{ optional($order->customer)->name ?? 'کاربر ناشناس' }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                {{ number_format($order->grand_total) }}
                            </td>
                            <td class="px-4 py-3">
                                @if($order->payment_status === 'paid')
                                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 text-xs">پرداخت شده</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-rose-50 text-rose-600 dark:bg-rose-900/20 dark:text-rose-400 text-xs">در انتظار پرداخت</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-gray-600 dark:text-gray-300 text-xs">{{ $order->delivery_status }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs dir-ltr text-right">
                                {{ \Morilog\Jalali\Jalalian::fromDateTime($order->created_at)->format('Y/m/d H:i') }}
                            </td>
                            <td class="px-4 py-3 text-left">
                                <a href="#" class="p-1.5 inline-block rounded-lg text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20" title="مشاهده فاکتور">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-base font-medium">سفارشی ثبت نشده است</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
