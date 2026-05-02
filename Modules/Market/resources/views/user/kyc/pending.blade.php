@extends('layouts.user')

@php($title = 'وضعیت احراز هویت')

@section('content')
    <div class="max-w-3xl mx-auto py-10">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl shadow-gray-200/40 dark:shadow-none border border-gray-100 dark:border-gray-700 p-10 text-center relative overflow-hidden">

            {{-- بک‌گراند تزئینی --}}
            <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-amber-500/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl"></div>

            <div class="relative z-10 flex flex-col items-center">
                <div class="w-24 h-24 bg-amber-100 dark:bg-amber-900/30 text-amber-500 rounded-full flex items-center justify-center mb-6 animate-pulse">
                    <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white mb-3">درخواست شما در حال بررسی است</h1>
                <p class="text-gray-500 dark:text-gray-400 max-w-lg mx-auto leading-relaxed">
                    اطلاعات و مدارک شما با موفقیت دریافت شد. همکاران ما در بخش پشتیبانی در حال بررسی اطلاعات شما هستند. نتیجه بررسی به زودی به شما اطلاع داده خواهد شد.
                </p>

                <div class="mt-8 pt-8 border-t border-gray-100 dark:border-gray-700 w-full max-w-md">
                    <div class="flex justify-between items-center text-sm mb-3">
                        <span class="text-gray-500">نام فروشگاه:</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">{{ $vendor->store_name }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">وضعیت فعلی:</span>
                        <span class="px-3 py-1 bg-amber-50 text-amber-600 rounded-full font-bold text-xs">در انتظار تایید ادمین</span>
                    </div>
                </div>

                <div class="mt-8">
                    <a href="{{ route('user.dashboard') }}" class="text-indigo-600 hover:text-indigo-700 font-medium text-sm">بازگشت به داشبورد اصلی سیستم</a>
                </div>
            </div>
        </div>
    </div>
@endsection
