@extends('layouts.user')

@php
    $title = 'تنظیمات املاک';
@endphp

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">تنظیمات عمومی املاک</h1>

        <form action="{{ route('user.settings.properties.update') }}" method="POST">
            @csrf

            {{-- Currency --}}
            <div class="mb-8 pb-8 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">واحد پول</h2>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">واحد پول پیش‌فرض</label>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="currency" value="toman" {{ $currency == 'toman' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-gray-700 dark:text-gray-300">تومان</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="currency" value="rial" {{ $currency == 'rial' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-gray-700 dark:text-gray-300">ریال</span>
                    </label>
                </div>
                <p class="text-xs text-gray-500 mt-2">این واحد پول در تمام بخش‌های نمایش قیمت اعمال خواهد شد.</p>
            </div>

            {{-- Property Code Settings --}}
            <div class="mb-8 pb-8 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">تنظیمات کد ملک</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">پیش‌وند کد ملک</label>
                        <input type="text" name="property_code_prefix" value="{{ $property_code_prefix }}" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" placeholder="مثلا: P">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">جداکننده</label>
                        <input type="text" name="property_code_separator" value="{{ $property_code_separator }}" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" placeholder="مثلا: -">
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="property_code_include_year" value="1" {{ $property_code_include_year ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">شامل سال شمسی در ابتدای کد</span>
                        </label>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">مثال خروجی: 1403-P-1001 (اگر همه گزینه‌ها فعال باشند)</p>
            </div>

            {{-- Upload Settings --}}
            <div class="mb-8 pb-8 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">تنظیمات آپلود</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Images --}}
                    <div class="md:col-span-2">
                        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">تصاویر</h3>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">حداکثر حجم تصویر (KB)</label>
                        <input type="number" name="max_file_size" value="{{ $max_file_size }}" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">حداکثر تعداد تصاویر گالری</label>
                        <input type="number" name="max_gallery_images" value="{{ $max_gallery_images }}" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">فرمت‌های مجاز تصویر</label>
                        <input type="text" name="allowed_file_types" value="{{ $allowed_file_types }}" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" dir="ltr">
                    </div>

                    {{-- Video --}}
                    <div class="md:col-span-2 mt-4">
                        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">ویدیو</h3>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">حداکثر حجم ویدیو (KB)</label>
                        <input type="number" name="max_video_size" value="{{ $max_video_size }}" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">فرمت‌های مجاز ویدیو</label>
                        <input type="text" name="allowed_video_types" value="{{ $allowed_video_types }}" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-indigo-500 focus:border-indigo-500" dir="ltr">
                    </div>
                </div>
            </div>

            {{-- Storage Report --}}
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">وضعیت فضای ذخیره‌سازی</h2>
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">تعداد کل فایل‌ها:</span>
                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $fileCount }} فایل</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">حجم اشغال شده:</span>
                        <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ $formattedSize }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-3">مسیر ذخیره‌سازی: <span dir="ltr" class="font-mono">storage/app/public/properties</span></p>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/30">
                    ذخیره تنظیمات
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
