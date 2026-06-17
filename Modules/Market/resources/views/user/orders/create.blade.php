@extends('layouts.user')

@php
    $title = 'ثبت سفارش دستی جدید';
@endphp

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm" dir="rtl">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('user.market.orders.index') }}" class="p-1 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700" title="بازگشت">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">ثبت سفارش فروشگاه (دستی)</h1>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-8">ثبت مستقیم سفارش جدید برای خریدار در سیستم.</p>
            </div>
        </div>

        {{-- Livewire Form Component --}}
        <livewire:market::user.order-form />
    </div>
@endsection
