@extends('layouts.user')

@php
    $title = 'احراز هویت فروشندگان';
@endphp

@section('content')
    <div class="max-w-4xl mx-auto py-6">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-3">ثبت‌نام و احراز هویت فروشندگان</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm">برای شروع فروش محصولات خود در مارکت‌پلیس، لطفا این ۴ مرحله را با دقت تکمیل کنید.</p>
        </div>

        {{-- فراخوانی کامپوننت ویزارد --}}
        @livewire('market::vendor.kyc-wizard')
    </div>
@endsection
