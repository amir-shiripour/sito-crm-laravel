@extends('layouts.app') {{-- Assuming you have a main app layout --}}

@section('title', 'ثبت موفق سفارش')

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl overflow-hidden text-center">
            <div class="bg-emerald-500 p-8 sm:p-12 text-white">
                <div class="mx-auto w-24 h-24 bg-white rounded-full flex items-center justify-center mb-6">
                    <svg class="w-12 h-12 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-3xl font-extrabold mb-2">سفارش شما با موفقیت ثبت شد!</h1>
                <p class="text-emerald-100 text-lg">از خرید شما سپاسگزاریم.</p>
            </div>

            <div class="p-8 sm:p-12">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-right bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-6 mb-8">
                    <div>
                        <span class="block text-sm text-gray-500 dark:text-gray-400 mb-1">شماره سفارش:</span>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $order->id }}</span>
                    </div>
                    <div>
                        <span class="block text-sm text-gray-500 dark:text-gray-400 mb-1">مبلغ پرداختی:</span>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($order->grand_total) }} تومان</span>
                    </div>
                    <div>
                         <span class="block text-sm text-gray-500 dark:text-gray-400 mb-1">وضعیت پرداخت:</span>
                         @if($order->payment_status === 'paid')
                             <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">پرداخت شده</span>
                         @elseif($order->payment_method === 'pos' || $order->payment_method === 'transfer')
                             <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">در انتظار تایید (آفلاین)</span>
                         @else
                              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">نامشخص</span>
                         @endif
                    </div>
                    @if($order->payment_ref_id)
                        <div>
                            <span class="block text-sm text-gray-500 dark:text-gray-400 mb-1">کد پیگیری تراکنش:</span>
                            <span class="text-lg font-bold text-gray-900 dark:text-white dir-ltr">{{ $order->payment_ref_id }}</span>
                        </div>
                    @endif
                </div>

                @if($order->payment_method === 'pos')
                     <div class="mb-8 p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl text-sm leading-relaxed text-right">
                         <strong>توجه:</strong> شما روش پرداخت در محل را انتخاب کرده‌اید. لطفا مبلغ سفارش را در زمان تحویل کالا پرداخت نمایید.
                     </div>
                @elseif($order->payment_method === 'transfer')
                     <div class="mb-8 p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl text-sm leading-relaxed text-right">
                         <strong>توجه:</strong> شما روش پرداخت انتقال بانکی را انتخاب کرده‌اید. لطفا نسبت به واریز وجه و اطلاع دادن به پشتیبانی اقدام فرمایید تا سفارش شما ارسال شود.
                     </div>
                @endif

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('market.public.index') }}" class="inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-colors">
                        بازگشت به فروشگاه
                    </a>
                    <a href="{{ route('user.market.orders.index') }}" class="inline-flex justify-center items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-xl text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition-colors">
                        پیگیری سفارشات
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
