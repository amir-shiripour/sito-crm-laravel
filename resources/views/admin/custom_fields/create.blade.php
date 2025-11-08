{{-- resources/views/admin/custom_fields/create.blade.php --}}
@extends('layouts.admin')
@php($title = 'ایجاد فیلد جدید')

@section('content')
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
            <h1 class="font-semibold text-gray-900 dark:text-gray-100">ایجاد فیلد جدید</h1>
            <a href="{{ route('admin.custom-fields.index') }}"
               class="px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                بازگشت
            </a>
        </div>

        <form method="POST" action="{{ route('admin.custom-fields.store') }}" class="p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-200">نقش</label>
                <select name="role_name" class="w-full border rounded-lg p-2.5 bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100" required>
                    <option value="">انتخاب نقش...</option>
                    @foreach($roles as $k => $v)
                        <option value="{{ $k }}" @selected(old('role_name')===$k)>{{ $v }}</option>
                    @endforeach

                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-200">نوع فیلد (HTML)</label>
                <input type="text" name="field_type" value="{{ old('field_type','text') }}"
                       class="w-full border rounded-lg p-2.5 bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                       placeholder="مثال: text, number, email, date, file, url, tel, textarea, select ..." required>
                {{-- چرا: بنا به درخواست شما، همهٔ انواع HTML را می‌پذیریم --}}
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-200">برچسب فیلد (label)</label>
                <input type="text" name="label" value="{{ old('label') }}"
                       class="w-full border rounded-lg p-2.5 bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                       required>
            </div>

            {{-- اختیاری: اگر خواستید کلید را خودتان تعیین کنید؛ در غیر این صورت خالی بگذارید تا اتومات تولید شود --}}
            <details class="mt-2">
                <summary class="cursor-pointer text-sm text-gray-600 dark:text-gray-300">تنظیمات پیشرفته (اختیاری)</summary>
                <div class="mt-3 space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-200">کلید فیلد (field_name)</label>
                        <input type="text" name="field_name" value="{{ old('field_name') }}"
                               class="w-full border rounded-lg p-2.5 bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                               placeholder="خالی = تولید خودکار">
                    </div>
                    <div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200">
                            <input type="checkbox" name="is_required" value="1" @checked(old('is_required'))>
                            ضروری باشد
                        </label>
                    </div>
                </div>
            </details>

            <div class="pt-2">
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">ایجاد فیلد</button>
            </div>
        </form>
    </div>
@endsection
