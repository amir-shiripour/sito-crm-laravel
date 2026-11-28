@extends('layouts.user')

@section('title', 'درون‌ریزی اکسل درخواست و انتقال کالا')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 font-sans">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">
                <a href="{{ route('admin.warehouse.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">داشبورد انبار</a>
                <span>/</span>
                <a href="{{ route('admin.warehouse.transfers.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">درخواست و انتقال کالا</a>
                <span>/</span>
                <span class="text-gray-900 dark:text-white">درون‌ریزی اکسل</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                درون‌ریزی اکسل درخواست و انتقال بین انبارها
            </h1>
        </div>

        <a href="{{ route('admin.warehouse.transfers.index') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold text-xs transition-all">
            بازگشت به فهرست حواله‌ها
        </a>
    </div>

    @include('warehouse::partials.flash_messages')

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-6 sm:p-8 space-y-6 shadow-xs">
        <form action="{{ route('admin.warehouse.transfers.import.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl p-8 text-center hover:border-indigo-500 transition-colors">
                <svg class="w-11 h-11 mx-auto text-indigo-500 dark:text-indigo-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">
                    فایل اکسل درخواست کالا را انتخاب کنید
                </p>
                <p class="text-xs text-slate-600 dark:text-slate-300 mb-4">
                    پشتیبانی از فایل .xlsx یا .xls و شیت «درخواست کالا»
                </p>

                <input type="file"
                       name="excel_file"
                       id="excel_file"
                       accept=".xlsx,.xls"
                       required
                       class="block w-full max-w-sm mx-auto text-xs text-slate-700 dark:text-slate-200 file:ml-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-500/15 dark:file:text-indigo-300 cursor-pointer">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 cursor-pointer">
                    <input type="radio" name="submission_mode" value="deliver" checked class="text-indigo-600 focus:ring-indigo-500">
                    <span class="font-bold text-slate-700 dark:text-slate-200">اجرای فوری انتقال موجودی</span>
                </label>

                <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 cursor-pointer">
                    <input type="radio" name="submission_mode" value="pending" class="text-indigo-600 focus:ring-indigo-500">
                    <span class="font-bold text-slate-700 dark:text-slate-200">ثبت به‌عنوان درخواست در انتظار تحویل</span>
                </label>
            </div>

            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 text-xs space-y-2 border border-gray-200/70 dark:border-gray-700/60">
                <p class="font-bold text-gray-900 dark:text-white">
                    ستون‌های مورد انتظار مطابق نمونه اکسل:
                </p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1 text-slate-700 dark:text-slate-200 font-bold">
                    <span class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-center">تاریخ (شمسی)</span>
                    <span class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-center">شماره درخواست</span>
                    <span class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-center">انبارک درخواست‌کننده</span>
                    <span class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-center">کد کالا</span>
                    <span class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-center">نام کالا</span>
                    <span class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-center">تعداد</span>
                    <span class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-center">درخواست‌کننده</span>
                    <span class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-center">توضیحات</span>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm transition-all shadow-sm">
                    شروع درون‌ریزی درخواست‌ها
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
