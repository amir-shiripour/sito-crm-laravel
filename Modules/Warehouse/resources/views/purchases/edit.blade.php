@extends('layouts.user')

@section('title', 'ویرایش رسید خرید ' . $receipt->receipt_number)

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 font-sans">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">
                <a href="{{ route('admin.warehouse.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">داشبورد انبار</a>
                <span>/</span>
                <a href="{{ route('admin.warehouse.purchases.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">رسید خرید کالا</a>
                <span>/</span>
                <span class="text-gray-900 dark:text-white dir-ltr">{{ $receipt->receipt_number }}</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                ویرایش رسید خرید «{{ $receipt->receipt_number }}»
            </h1>
        </div>

        <div>
            <a href="{{ route('admin.warehouse.purchases.show', $receipt) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold text-xs transition-all">
                بازگشت به رسید
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

    <form action="{{ route('admin.warehouse.purchases.update', $receipt) }}" method="POST">
        @method('PUT')
        @include('warehouse::purchases._form')
    </form>

</div>
@endsection
