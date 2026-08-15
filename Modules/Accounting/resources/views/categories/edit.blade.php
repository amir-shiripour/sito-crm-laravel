@extends('layouts.user')

@section('title', 'ویرایش سرفصل حسابداری')

@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800";
@endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <form action="{{ route('admin.accounting.categories.update', $category) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-8">
            {{-- Header --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z" /></svg>
                        </span>
                        ویرایش سرفصل: {{ $category->title }}
                    </h1>
                </div>
            </div>

            {{-- Form Card --}}
            <div class="{{ $cardClass }}">
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Title -->
                    <div class="md:col-span-2">
                        <label for="title" class="{{ $labelClass }}">عنوان سرفصل <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title', $category->title) }}" class="{{ $inputClass }}" required>
                        @error('title')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Type -->
                    <div>
                        <label for="type" class="{{ $labelClass }}">نوع ماهیت <span class="text-red-500">*</span></label>
                        <select name="type" id="type" class="{{ $inputClass }}" required>
                            <option value="">انتخاب کنید...</option>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" {{ old('type', $category->type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Status & Treasury Relation -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-center pt-5">
                        <div>
                            <label class="{{ $labelClass }}">وضعیت</label>
                            <label class="relative inline-flex items-center cursor-pointer gap-3">
                                <input type="hidden" name="status" value="0">
                                <input type="checkbox" name="status" value="1" class="sr-only peer" {{ old('status', $category->status) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-300">فعال</span>
                            </label>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">اتصال به خزانه‌داری</label>
                            <label class="relative inline-flex items-center cursor-pointer gap-3">
                                <input type="hidden" name="is_treasury_related" value="0">
                                <input type="checkbox" name="is_treasury_related" value="1" class="sr-only peer" {{ old('is_treasury_related', $category->is_treasury_related) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-300">مرتبط با خزانه‌داری</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sticky Footer --}}
        <div class="sticky bottom-4 z-40 max-w-4xl mx-auto mt-8">
            <div class="flex justify-between items-center bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl">
                <a href="{{ route('admin.accounting.categories.index') }}" class="px-6 py-3 rounded-xl text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">
                    انصراف
                </a>
                <button type="submit" class="px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    به‌روزرسانی سرفصل
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
