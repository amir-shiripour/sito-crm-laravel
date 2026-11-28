@extends('layouts.user')

@section('title', 'ثبت سند خروج و مصرف کالا')

@section('content')
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 font-sans">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">
                <a href="{{ route('admin.warehouse.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">داشبورد انبار</a>
                <span>/</span>
                <a href="{{ route('admin.warehouse.consumptions.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">خروج و مصرف کالا</a>
                <span>/</span>
                <span class="text-gray-900 dark:text-white">ثبت سند جدید</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                ثبت سند جدید خروج و مصرف کالا
            </h1>
        </div>

        <a href="{{ route('admin.warehouse.consumptions.index') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-colors">
            بازگشت به فهرست
        </a>
    </div>

    @include('warehouse::partials.flash_messages')

    <form action="{{ route('admin.warehouse.consumptions.store') }}" method="POST">
        @include('warehouse::consumptions._form')
    </form>
</div>
@endsection
