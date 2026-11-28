@extends('layouts.user')

@section('title', 'ثبت درخواست و انتقال کالا بین انبارها')

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 font-sans">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">
                <a href="{{ route('admin.warehouse.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">داشبورد انبار</a>
                <span>/</span>
                <a href="{{ route('admin.warehouse.transfers.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">درخواست و انتقال کالا</a>
                <span>/</span>
                <span class="text-gray-900 dark:text-white">ثبت حواله جدید</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                ثبت درخواست و انتقال کالا بین انبارها
            </h1>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.warehouse.transfers.import.form') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-emerald-200 dark:border-emerald-700/60 bg-emerald-50/80 dark:bg-emerald-500/15 hover:bg-emerald-100 dark:hover:bg-emerald-500/25 text-emerald-700 dark:text-emerald-300 font-bold text-xs transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                درون‌ریزی از اکسل
            </a>

            <a href="{{ route('admin.warehouse.transfers.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold text-xs transition-all">
                بازگشت به فهرست
            </a>
        </div>
    </div>

    @include('warehouse::partials.flash_messages')

    @if (isset($errors) && $errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-xs text-rose-700 dark:text-rose-300 space-y-1">
            <p class="font-bold">لطفاً خطاهای زیر را بررسی کنید:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.warehouse.transfers.store') }}" method="POST">
        @include('warehouse::transfers._form')
    </form>

</div>
@endsection
