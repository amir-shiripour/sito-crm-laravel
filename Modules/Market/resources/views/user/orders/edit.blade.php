@extends('layouts.user')

@php
    $title = 'ویرایش سفارش #' . $order->id;
@endphp

@section('content')
    <div class="screen-dashboard-container space-y-6 text-right" dir="rtl">
        {{-- Header & Top Action bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-3">
                <a href="{{ route('user.market.orders.index') }}" class="p-2 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all hover:scale-105 active:scale-95" title="بازگشت به لیست">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-black text-gray-900 dark:text-white">ویرایش سفارش #ORD-{{ $order->id }}</h1>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">ویرایش اقلام، آدرس و مشخصات پرداخت سفارش.</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('user.market.orders.show', $order) }}" class="inline-flex items-center gap-2 px-5 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold rounded-2xl hover:scale-[1.01] text-xs transition-all duration-200 shadow-sm border border-gray-200 dark:border-gray-700">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    مشاهده جزئیات
                </a>
            </div>
        </div>


        {{-- Livewire Form Component --}}
        <livewire:market::user.order-form :order="$order" />
    </div>
@endsection
