@extends('layouts.app')

@section('title', 'پرداخت ناموفق')

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl overflow-hidden text-center">
            <div class="bg-red-500 p-8 sm:p-12 text-white">
                <div class="mx-auto w-24 h-24 bg-white rounded-full flex items-center justify-center mb-6">
                    <svg class="w-12 h-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-extrabold mb-2">پرداخت ناموفق بود!</h1>
                <p class="text-red-100 text-lg">متاسفانه در فرآیند پرداخت خطایی رخ داد.</p>
            </div>

            <div class="p-8 sm:p-12">
                @if(session('error'))
                    <div class="mb-8 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 text-red-800 dark:text-red-300 rounded-xl text-sm leading-relaxed text-right">
                        <strong>دلیل خطا:</strong> {{ session('error') }}
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-right bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-6 mb-8">
                    <div>
                        <span class="block text-sm text-gray-500 dark:text-gray-400 mb-1">شماره سفارش:</span>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $order->id }}</span>
                    </div>
                    <div>
                        <span class="block text-sm text-gray-500 dark:text-gray-400 mb-1">مبلغ سفارش:</span>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($order->grand_total) }} تومان</span>
                    </div>
                </div>

                <p class="text-gray-600 dark:text-gray-400 mb-8">
                    نگران نباشید، هیچ مبلغی از حساب شما کسر نشده است. موجودی سبد خرید شما محفوظ است. می‌توانید دوباره برای پرداخت تلاش کنید.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('market.cart.index') }}" class="inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-colors">
                        بازگشت به سبد خرید و تلاش مجدد
                    </a>
                     <a href="{{ route('market.public.index') }}" class="inline-flex justify-center items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-xl text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition-colors">
                        بازگشت به فروشگاه
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
