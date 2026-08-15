@extends('layouts.user')

@section('title', 'ایجاد سرفصل حسابداری جدید')

@php
    $cardClass = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1";
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 transition-all shadow-sm dark:border-gray-700 dark:bg-gray-900/50 dark:text-white dark:placeholder-gray-500 dark:focus:border-indigo-500 dark:focus:ring-indigo-500/20";
@endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-24">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                <span class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                </span>
                افزودن سرفصل حسابداری جدید
            </h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">سرفصل کل یا معین جدید را برای طبقه‌بندی تراکنش‌ها تعریف کنید.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.accounting.categories.index') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-bold transition-all active:scale-95">
                بازگشت به لیست
            </a>
        </div>
    </div>

    {{-- Errors --}}
    @if ($errors->any())
        <div class="p-5 text-sm text-red-800 rounded-2xl bg-red-50 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20 flex items-start gap-4 shadow-sm">
            <span class="bg-red-100 text-red-600 dark:bg-red-500/20 p-2 rounded-full shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </span>
            <div>
                <p class="font-black text-base mb-2">خطا در ثبت سرفصل!</p>
                <ul class="list-disc ps-5 space-y-1.5 marker:text-red-400">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Form --}}
    <form action="{{ route('admin.accounting.categories.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="{{ $cardClass }} p-6 sm:p-8 space-y-6">
            <div>
                <label for="title" class="{{ $labelClass }}">عنوان سرفصل <span class="text-rose-500 font-black">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" placeholder="مثال: هزینه‌های تبلیغات و بازاریابی" class="{{ $inputClass }}" required>
            </div>

            <div>
                <label for="type" class="{{ $labelClass }}">ماهیت حساب / نوع <span class="text-rose-500 font-black">*</span></label>
                <select name="type" id="type" class="{{ $inputClass }} cursor-pointer" required>
                    <option value="">انتخاب کنید...</option>
                    @foreach($types as $value => $label)
                        <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status & Treasury Relation -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-center pt-5">
                <div>
                    <label class="{{ $labelClass }}">وضعیت</label>
                    <label class="relative inline-flex items-center cursor-pointer gap-3">
                        <input type="hidden" name="status" value="0">
                        <input type="checkbox" name="status" value="1" class="sr-only peer" {{ old('status', true) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-300">فعال</span>
                    </label>
                </div>
                <div>
                    <label class="{{ $labelClass }}">اتصال به خزانه‌داری</label>
                    <label class="relative inline-flex items-center cursor-pointer gap-3">
                        <input type="hidden" name="is_treasury_related" value="0">
                        <input type="checkbox" name="is_treasury_related" value="1" class="sr-only peer" {{ old('is_treasury_related', false) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-300">مرتبط با خزانه‌داری</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Sticky Footer --}}
        <div class="sticky bottom-4 z-40 max-w-4xl mx-auto mt-8">
            <div class="flex justify-between items-center bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xl">
                <a href="{{ route('admin.accounting.categories.index') }}" class="px-6 py-3 rounded-xl text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                    انصراف
                </a>
                <button type="submit" class="px-8 py-3.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all flex items-center gap-2 active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    ذخیره سرفصل
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
