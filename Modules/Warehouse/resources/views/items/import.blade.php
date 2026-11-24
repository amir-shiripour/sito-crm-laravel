@extends('layouts.user')

@section('title', 'درون‌ریزی کالاهای انبار از اکسل')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    {{-- Panel Standard Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-200 dark:border-gray-800">
        <div class="flex items-center gap-3">
            <span class="flex items-center justify-center w-11 h-11 rounded-xl bg-teal-600 text-white shadow-sm">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
            </span>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                    درون‌ریزی هوشمند اقلام انبار از اکسل
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    خواندن دسته‌بندی‌ها، زیردسته‌ها، برندها، مشخصات فنی و موجودی ابتدای دوره از فایل اکسل
                </p>
            </div>
        </div>

        <div>
            <a href="{{ route('admin.warehouse.items.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-bold transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت به لیست کالاها
            </a>
        </div>
    </div>

    @include('warehouse::partials.flash_messages')

    {{-- Preset Quick-Import Card (Dental Clinic Dr. Zabihi File) --}}
    @if($presetExists)
        <div class="bg-indigo-50/70 dark:bg-indigo-950/40 rounded-2xl border border-indigo-200 dark:border-indigo-800/60 p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <span class="p-2.5 rounded-xl bg-indigo-600 text-white shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                                فایل پیش‌فرض آماده: «سیستم انبار کلینیک دکتر ذبیحی»
                            </h3>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300">
                                ۹۳۶ ردیف کالا
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-1 leading-relaxed">
                            این فایل شامل ۱۱ دسته اصلی، ۷۶ زیردسته، ۲۸۴ نوع/متریال و ۹۳۶ قلم دارویی و ایمپلنت با فرمول‌های نام‌گذاری خودکار است.
                        </p>
                    </div>
                </div>

                <form action="{{ route('admin.warehouse.items.import.submit') }}" method="POST" onsubmit="return confirm('آیا از درون‌ریزی فایل ۹۳۶ ردیفی کلینیک دکتر ذبیحی اطمینان دارید؟');">
                    @csrf
                    <input type="hidden" name="use_preset" value="1">
                    <button type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold transition-all shadow-sm shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        ایمپورت یکپارچه فایل کلینیک (۱ کلیک)
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- Custom File Upload Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700/80 p-6 sm:p-8 space-y-6 shadow-xs">
        <div class="flex items-center gap-2.5 pb-4 border-b border-gray-200 dark:border-gray-700/80">
            <span class="w-3.5 h-3.5 rounded-full bg-teal-600"></span>
            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                بارگذاری فایل اکسل دلخواه
            </h3>
        </div>

        <form action="{{ route('admin.warehouse.items.import.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl p-8 text-center hover:border-indigo-500 transition-colors">
                <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>

                <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">
                    فایل اکسل خود را انتخاب کنید یا به اینجا بکشید
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    فرمت‌های مجاز: .xlsx یا .xls (حداکثر ۲۰ مگابایت)
                </p>

                <input type="file"
                       name="excel_file"
                       id="excel_file"
                       accept=".xlsx,.xls"
                       required
                       class="block w-full max-w-sm mx-auto text-xs text-gray-700 dark:text-gray-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-950/60 dark:file:text-indigo-300 cursor-pointer">
            </div>

            {{-- Excel Format Guide --}}
            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 text-xs space-y-2 border border-gray-100 dark:border-gray-700/60">
                <p class="font-bold text-gray-800 dark:text-gray-200">
                    ساختار شیت‌های اکسل مورد پشتیبانی:
                </p>
                <ul class="list-disc list-inside space-y-1 text-gray-600 dark:text-gray-300 leading-relaxed font-sans">
                    <li><strong>شیت «تعریف کالا»:</strong> ستون‌های ردیف، کد کالا، نام کالا، دسته، زیردسته، جنس/نوع/تیپ، برند، سایز/رنگ، واحد، تاریخ انقضا، نقطه سفارش، موجودی ابتدای دوره، قیمت روز، موقعیت انبار، مشخصات.</li>
                    <li><strong>شیت «تنظیمات»:</strong> ستون‌های لیست دسته‌ها، زیردسته‌ها، تیپ‌ها، برندها و سایزها جهت ثبت در پایگاه داده.</li>
                    <li>در صورتی که کد یا نام کالا خالی باشد، سیستم به صورت هوشمند بر اساس ترکیب [زیردسته] [برند] [تیپ] [سایز] آن را تولید خواهد کرد.</li>
                </ul>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700/60">
                <a href="{{ route('admin.warehouse.items.index') }}"
                   class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-bold transition-all">
                    انصراف
                </a>
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-8 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    شروع پردازش و درون‌ریزی فایل
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
