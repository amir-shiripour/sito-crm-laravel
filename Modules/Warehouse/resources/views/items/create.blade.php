@extends('layouts.user')

@section('title', 'تعریف و ثبت کالای جدید')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 font-sans">

    {{-- Panel Standard Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-gray-200 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300 mb-1.5">
                <a href="{{ route('admin.warehouse.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">داشبورد انبار</a>
                <span>/</span>
                <a href="{{ route('admin.warehouse.items.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">شناسنامه کالاها</a>
                <span>/</span>
                <span class="text-gray-900 dark:text-white">کالای جدید</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                تعریف و ثبت کالای جدید
            </h1>
            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                مشخصات کالا، موقعیت انبار و آستانه‌های کنترل موجودی
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.warehouse.items.import.form') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:hover:bg-indigo-500/25 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-500/30 text-xs font-bold transition-all">
                درون‌ریزی از اکسل
            </a>

            <a href="{{ route('admin.warehouse.items.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all">
                بازگشت به لیست
            </a>
        </div>
    </div>

    @include('warehouse::partials.flash_messages')

    <form action="{{ route('admin.warehouse.items.store') }}" method="POST">
        @include('warehouse::items._form')
    </form>

</div>
@endsection
