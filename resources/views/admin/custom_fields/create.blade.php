{{-- resources/views/admin/custom_fields/create.blade.php --}}
@extends('layouts.user')

@php
    $title = 'ایجاد فیلد سفارشی جدید';

    // استایل‌های مشترک
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-200";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $selectClass = $inputClass . " appearance-none cursor-pointer";
@endphp

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                </span>
                    ایجاد فیلد جدید
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">تعریف فیلد اختصاصی و اتصال آن به یک نقش کاربری خاص</p>
            </div>

            <a href="{{ route('admin.custom-fields.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                بازگشت به لیست
            </a>
        </div>

        {{-- نمایش خطاها در صورت وجود --}}
        @if($errors->any())
            <div class="rounded-xl bg-red-50 p-4 border border-red-100 dark:bg-red-900/20 dark:border-red-800/50 animate-in fade-in slide-in-from-top-2">
                <div class="flex items-center gap-2 text-red-800 dark:text-red-400 font-bold text-sm mb-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    لطفاً خطاهای زیر را بررسی کنید:
                </div>
                <ul class="list-disc list-inside text-xs text-red-600 dark:text-red-300 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.custom-fields.store') }}" class="space-y-6 pb-20" x-data="{ isSubmitting: false, showAdvanced: false }" @submit="isSubmitting = true">
            @csrf

            <div class="{{ $cardClass }}">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        اطلاعات پایه فیلد
                    </h2>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- نقش مرتبط --}}
                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}">نقش (Role) <span class="text-red-500">*</span></label>
                        <div class="relative max-w-md">
                            <select name="role_name" class="{{ $selectClass }} !pl-11 font-bold text-indigo-700 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/10" required>
                                <option value="">انتخاب نقش مرتبط...</option>
                                @foreach($roles as $k => $v)
                                    <option value="{{ $k }}" @selected(old('role_name') === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 left-0 pl-4 flex items-center text-indigo-500">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </div>
                        </div>
                    </div>

                    {{-- برچسب فیلد --}}
                    <div>
                        <label class="{{ $labelClass }}">برچسب فیلد (Label) <span class="text-red-500">*</span></label>
                        <input type="text" name="label" value="{{ old('label') }}" class="{{ $inputClass }}" placeholder="مثلاً: کدملی، آدرس شرکت، سال تأسیس..." required>
                    </div>

                    {{-- نوع فیلد --}}
                    <div>
                        <label for="field_type" class="{{ $labelClass }}">نوع فیلد (HTML Type) <span class="text-red-500">*</span></label>
                        <select id="field_type" name="field_type" class="{{ $selectClass }}" required>
                            @foreach([
                                'text' => 'متن (Text)',
                                'number' => 'عدد (Number)',
                                'email' => 'ایمیل (Email)',
                                'password' => 'رمز عبور (Password)',
                                'date' => 'تاریخ (Date)',
                                'textarea' => 'متن طولانی (Textarea)',
                                'file' => 'فایل (File)',
                                'select' => 'لیست انتخابی (Select)',
                                'checkbox' => 'چک‌باکس (Checkbox)',
                                'radio' => 'رادیو (Radio)',
                            ] as $type => $label)
                                <option value="{{ $type }}" @selected(old('field_type', 'text') === $type)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            نوع ورودی HTML که برای این فیلد نمایش داده خواهد شد.
                        </p>
                    </div>
                </div>

                {{-- تنظیمات پیشرفته (آکاردئون) --}}
                <div class="border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
                    <button type="button" @click="showAdvanced = !showAdvanced" class="w-full flex items-center justify-between p-6 outline-none group">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">تنظیمات پیشرفته (اختیاری)</span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-500 transition-transform duration-300" :class="showAdvanced ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>

                    <div x-show="showAdvanced" x-collapse x-cloak>
                        <div class="p-6 pt-0 border-t border-gray-100 dark:border-gray-700/50 mt-2 space-y-6">

                            <div>
                                <label class="{{ $labelClass }}">کلید سیستمی فیلد (field_name)</label>
                                <input type="text" name="field_name" value="{{ old('field_name') }}" class="{{ $inputClass }} dir-ltr text-left font-mono max-w-md" placeholder="مثلاً: company_address">
                                <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1.5 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    در صورتی که این بخش خالی بماند، کلید سیستمی به صورت خودکار از روی برچسب تولید می‌شود.
                                </p>
                            </div>

                            <div>
                                <label class="inline-flex items-center gap-3 cursor-pointer group p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-white dark:hover:bg-gray-800 transition-colors w-fit">
                                    <div class="relative flex items-center">
                                        <input type="checkbox" name="is_required" value="1" @checked(old('is_required')) class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 cursor-pointer">
                                    </div>
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">این فیلد برای کاربر اجباری (Required) باشد</span>
                                </label>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- دکمه ذخیره --}}
            <div class="flex items-center justify-between sticky bottom-6 z-40 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
                <a href="{{ route('admin.custom-fields.index') }}" class="px-5 py-2.5 text-sm font-bold text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                    انصراف
                </a>
                <button type="submit"
                        :disabled="isSubmitting"
                        class="inline-flex items-center gap-2 px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed">
                    <span x-show="isSubmitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-show="!isSubmitting">ایجاد فیلد</span>
                    <span x-show="isSubmitting">در حال پردازش...</span>
                    <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </button>
            </div>

        </form>
    </div>
@endsection
