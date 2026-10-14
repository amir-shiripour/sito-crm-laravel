@extends('layouts.user')

@php($title = 'تنظیمات وب‌سرویس ویژن ERP')

@section('content')
    <div class="max-w-5xl mx-auto py-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-600 text-white shadow-md shadow-indigo-500/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    اتصال به نرم‌افزار حسابداری و انبارداری ویژن (Vision ERP)
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    پیکربندی اتصال خودکار محصولات، موجودی و صدور فاکتور در نرم‌افزار مالی ویژن نسخه ۳.۶.۱
                </p>
            </div>
        </div>

        @livewire('market::vendor.vision-erp-settings')
    </div>
@endsection
