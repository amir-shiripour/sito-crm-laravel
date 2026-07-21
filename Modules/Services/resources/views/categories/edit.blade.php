@extends('layouts.user')
@section('title', 'ویرایش دسته‌بندی: ' . $category->name)

@php
    $inputClass = "w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15 transition-all shadow-sm dark:border-gray-700 dark:bg-gray-900/50 dark:text-white dark:placeholder-gray-500 dark:focus:border-amber-500 dark:focus:ring-amber-500/20";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 ms-1";
    $cardClass  = "bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl";
@endphp

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8"
         x-data="{
            color: '{{ old('color', $category->color) }}',
            name: '{{ addslashes(old('name', $category->name)) }}',
            preview: @js($category->icon_url),
            removeIcon: false
         }">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-4 tracking-tight">
                <span class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 text-white shadow-lg shadow-amber-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </span>
                ویرایش دسته‌بندی
            </h1>
            <a href="{{ route('services.categories.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors group">
                <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت به لیست
            </a>
        </div>

        {{-- Errors --}}
        @if($errors->any())
            <div class="p-5 text-sm text-red-800 rounded-2xl bg-red-50 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20 flex items-start gap-4 shadow-sm">
                <span class="bg-red-100 text-red-600 dark:bg-red-500/20 p-2 rounded-full shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
                <div>
                    <p class="font-black text-base mb-2">خطا در ذخیره اطلاعات!</p>
                    <ul class="list-disc ps-5 space-y-1.5 marker:text-red-400">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('services.categories.update', $category) }}" class="space-y-8" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Basic Info --}}
            <div class="{{ $cardClass }}">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                    <h2 class="text-lg font-black text-gray-800 dark:text-gray-100 flex items-center gap-3">
                        <div class="p-2 bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        اطلاعات پایه
                    </h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}">نام دسته‌بندی <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="name" value="{{ old('name', $category->name) }}" required
                               class="{{ $inputClass }}" placeholder="مثال: طراحی وب">
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">رنگ نمایشی</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="color" x-model="color" value="{{ old('color', $category->color) }}"
                                   class="h-11 w-16 rounded-xl border border-gray-200 dark:border-gray-700 cursor-pointer shrink-0">
                            <span class="flex-1 px-3 py-2.5 rounded-xl text-xs font-bold border transition-all"
                                  :style="`background: ${color}15; color: ${color}; border-color: ${color}30`"
                                  x-text="name || 'پیش‌نمایش'"></span>
                        </div>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">ترتیب نمایش</label>
                        <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $category->sort_order) }}"
                               class="{{ $inputClass }} dir-ltr text-left">
                    </div>

                    {{-- Icon upload --}}
                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}">آیکون دسته‌بندی (اختیاری)</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 flex items-center justify-center overflow-hidden bg-gray-50 dark:bg-gray-900/40 shrink-0">
                                <img x-show="preview && !removeIcon" :src="preview" class="w-full h-full object-cover" alt="">
                                <svg x-show="!preview || removeIcon" class="w-6 h-6 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 space-y-2.5">
                                <input type="file" name="icon" accept="image/png,image/jpeg,image/gif,image/svg+xml,image/webp"
                                       @change="if ($event.target.files[0]) { preview = URL.createObjectURL($event.target.files[0]); removeIcon = false; }"
                                       class="block w-full text-sm text-gray-600 dark:text-gray-300 file:me-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-amber-50 file:text-amber-700 dark:file:bg-amber-500/10 dark:file:text-amber-400 hover:file:bg-amber-100 dark:hover:file:bg-amber-500/20 cursor-pointer transition-colors">
                                <p class="text-xs text-gray-400">PNG، JPG، GIF، SVG یا WEBP — حداکثر ۲ مگابایت.</p>

                                @if($category->icon)
                                    <label class="flex items-center gap-2 text-xs font-bold text-red-500 dark:text-red-400 cursor-pointer w-fit">
                                        <input type="checkbox" name="remove_icon" value="1" x-model="removeIcon"
                                               class="rounded border-gray-300 dark:border-gray-600 text-red-500 focus:ring-red-500">
                                        حذف آیکون فعلی
                                    </label>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}">وضعیت</label>
                        <select name="status" class="{{ $inputClass }} cursor-pointer md:max-w-xs">
                            <option value="active" @selected(old('status', $category->status) === 'active')>فعال</option>
                            <option value="inactive" @selected(old('status', $category->status) === 'inactive')>غیرفعال</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}">توضیحات</label>
                        <textarea name="description" rows="3" class="{{ $inputClass }} resize-none"
                                  placeholder="توضیح مختصری درباره این دسته‌بندی...">{{ old('description', $category->description) }}</textarea>
                    </div>
                </div>
            </div>

            @if($category->services()->count() > 0)
                <div class="rounded-2xl bg-blue-50 dark:bg-blue-500/10 p-5 border border-blue-100 dark:border-blue-500/20 text-sm text-blue-800 dark:text-blue-400 flex items-center gap-4">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    این دسته‌بندی به {{ $category->services()->count() }} سرویس متصل است. تغییر وضعیت به «غیرفعال» سرویس‌های مرتبط را حذف نمی‌کند.
                </div>
            @endif

            {{-- Sticky Submit Bar --}}
            <div class="sticky bottom-4 z-40">
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl p-4 rounded-2xl border border-gray-200 dark:border-gray-700/50 shadow-[0_10px_40px_rgba(0,0,0,0.05)] dark:shadow-[0_10px_40px_rgba(0,0,0,0.3)] flex flex-row-reverse items-center justify-between gap-4">
                    <button type="submit"
                            class="flex-1 md:flex-none px-8 py-3.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 text-white font-black shadow-lg shadow-amber-500/30 hover:shadow-amber-500/50 hover:from-amber-400 hover:to-amber-500 transition-all duration-300 active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        ذخیره تغییرات
                    </button>
                    <a href="{{ route('services.categories.index') }}"
                       class="px-6 py-3.5 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-xl dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        انصراف
                    </a>
                </div>
            </div>
        </form>
    </div>
@endsection
