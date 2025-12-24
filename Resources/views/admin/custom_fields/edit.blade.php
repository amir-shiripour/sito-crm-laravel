@extends('layouts.user')
@php($title = 'ویرایش فیلد')

@section('content')
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
            <h1 class="font-semibold text-gray-900 dark:text-gray-100">ویرایش فیلد</h1>
            <a href="{{ route('admin.custom-fields.index') }}"
               class="px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                بازگشت
            </a>
        </div>

        <form method="POST" action="{{ route('admin.custom-fields.update', $field) }}" class="p-6 space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-200">نقش</label>
                <select name="role_name" class="w-full border rounded-lg p-2.5 bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100" required>
                    @foreach($roles as $k => $v)
                        <option value="{{ $k }}" @selected(old('role_name',$field->role_name)===$k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-200">نوع فیلد (HTML)</label>
                <input type="text" name="field_type" value="{{ old('field_type', $field->field_type) }}"
                       class="w-full border rounded-lg p-2.5 bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                       required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-200">برچسب فیلد (label)</label>
                <input type="text" name="label" value="{{ old('label', $field->label) }}"
                       class="w-full border rounded-lg p-2.5 bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                       required>
            </div>

            <details class="mt-2">
                <summary class="cursor-pointer text-sm text-gray-600 dark:text-gray-300">تنظیمات پیشرفته (اختیاری)</summary>
                <div class="mt-3 space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-800 dark:text-gray-200">کلید فیلد (field_name)</label>
                        <input type="text" name="field_name" value="{{ old('field_name', $field->field_name) }}"
                               class="w-full border rounded-lg p-2.5 bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                               placeholder="خالی = تولید خودکار از label">
                    </div>
                    <div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200">
                            <input type="checkbox" name="is_required" value="1" @checked(old('is_required',$field->is_required))>
                            ضروری باشد
                        </label>
                    </div>
                </div>
            </details>

            <div class="pt-2">
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">ذخیره</button>
            </div>
        </form>
    </div>
@endsection
