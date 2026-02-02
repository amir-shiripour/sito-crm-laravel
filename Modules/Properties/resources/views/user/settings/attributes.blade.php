@extends('layouts.user')

@php
    $title = 'مدیریت ویژگی‌های ملک';

    // استایل‌های مشترک
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all duration-200";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $selectClass = $inputClass . " appearance-none cursor-pointer";
    $labelClass = "block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5";
    $checkboxClass = "w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 cursor-pointer";
@endphp

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-8 space-y-6" x-data="attributeSettings()">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                </span>
                    مدیریت ویژگی‌ها و امکانات
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mr-10">تعریف فیلدها و امکانات رفاهی قابل انتخاب برای املاک</p>
            </div>

            <a href="{{ route('user.settings.properties.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                بازگشت به تنظیمات
            </a>
        </div>

        <div class="{{ $cardClass }}">

            {{-- نوار تب‌ها --}}
            <div class="flex border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                <button @click="activeTab = 'details'"
                        class="flex-1 py-4 px-6 text-center border-b-2 font-bold text-sm transition-all whitespace-nowrap outline-none focus:outline-none flex items-center justify-center gap-2"
                        :class="activeTab === 'details' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-gray-800' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    اطلاعات تکمیلی
                </button>
                <button @click="activeTab = 'features'"
                        class="flex-1 py-4 px-6 text-center border-b-2 font-bold text-sm transition-all whitespace-nowrap outline-none focus:outline-none flex items-center justify-center gap-2"
                        :class="activeTab === 'features' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-gray-800' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                    امکانات رفاهی
                </button>
            </div>

            <div class="p-6 sm:p-8">

                {{-- Tab 1: Details Attributes --}}
                <div x-show="activeTab === 'details'" x-transition:enter.duration.300ms x-transition:enter.opacity>

                    {{-- فرم افزودن --}}
                    <div class="bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800 rounded-xl p-5 mb-8">
                        <h3 class="text-sm font-bold text-indigo-900 dark:text-indigo-100 mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            افزودن ویژگی جدید
                        </h3>
                        <form action="{{ route('user.settings.properties.attributes.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="section" value="details">

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
                                <div class="md:col-span-3">
                                    <label class="{{ $labelClass }}">عنوان ویژگی</label>
                                    <input type="text" name="name" class="{{ $inputClass }}" required placeholder="مثلاً: سال ساخت، طبقه">
                                </div>

                                <div class="md:col-span-3">
                                    <label class="{{ $labelClass }}">نوع ورودی</label>
                                    <div class="relative">
                                        <select name="type" class="{{ $selectClass }}" x-model="newType">
                                            <option value="text">متن (Text)</option>
                                            <option value="number">عدد (Number)</option>
                                            <option value="select">لیست کشویی (Select)</option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                    </div>
                                </div>

                                <div class="md:col-span-3">
                                    <label class="{{ $labelClass }}" :class="{'opacity-50': newType !== 'select'}">گزینه‌ها (جداکننده کاما)</label>
                                    <input type="text" name="options" class="{{ $inputClass }}" :disabled="newType !== 'select'" placeholder="گزینه ۱، گزینه ۲" x-transition>
                                </div>

                                <div class="md:col-span-3 flex flex-col gap-2 pt-6">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="is_filterable" value="1" class="{{ $checkboxClass }}" x-model="isFilterable">
                                        <span class="text-xs text-gray-700 dark:text-gray-300">قابل فیلتر در جستجو</span>
                                    </label>

                                    <label class="flex items-center gap-2 cursor-pointer" x-show="isFilterable && newType === 'number'" x-transition>
                                        <input type="checkbox" name="is_range_filter" value="1" class="{{ $checkboxClass }}">
                                        <span class="text-xs text-gray-700 dark:text-gray-300">فیلتر بازه‌ای (حداقل/حداکثر)</span>
                                    </label>
                                </div>

                                <div class="md:col-span-12 mt-2">
                                    <button type="submit" class="w-full md:w-auto px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all active:scale-95 flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        افزودن ویژگی
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- لیست ویژگی‌ها --}}
                    <div class="space-y-3">
                        @forelse($detailsAttributes as $attr)
                            <div class="group flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 hover:shadow-sm transition-all" x-data="{ editing: false, name: '{{ $attr->name }}', isFilterable: {{ $attr->is_filterable ? 'true' : 'false' }}, isRangeFilter: {{ $attr->is_range_filter ? 'true' : 'false' }} }">
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400">
                                        <span class="text-xs font-mono font-bold">{{ $loop->iteration }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <div x-show="!editing">
                                            <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                                {{ $attr->name }}
                                                @if($attr->is_filterable)
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300" title="قابل فیلتر">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                                                    </span>
                                                @endif
                                                @if($attr->is_range_filter)
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-300" title="فیلتر بازه‌ای">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                                    </span>
                                                @endif
                                            </h4>
                                            <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] px-2 py-0.5 rounded bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                {{ match($attr->type) { 'text' => 'متن', 'number' => 'عدد', 'select' => 'لیست', default => $attr->type } }}
                                            </span>
                                                @if($attr->type === 'select')
                                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 truncate max-w-[200px]" title="{{ implode(', ', $attr->options ?? []) }}">
                                                    {{ implode(', ', $attr->options ?? []) }}
                                                </span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- فرم ویرایش --}}
                                        <form x-show="editing" action="{{ route('user.settings.properties.attributes.update', $attr) }}" method="POST" class="flex flex-col gap-3 mt-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="type" value="{{ $attr->type }}">
                                            <div class="flex gap-2">
                                                <input type="text" name="name" x-model="name" class="{{ $inputClass }} py-1.5 text-xs" required>
                                                @if($attr->type === 'select')
                                                    <input type="text" name="options" value="{{ implode(',', $attr->options ?? []) }}" class="{{ $inputClass }} py-1.5 text-xs" placeholder="گزینه‌ها">
                                                @endif
                                            </div>
                                            <div class="flex gap-4">
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox" name="is_filterable" value="1" class="{{ $checkboxClass }}" x-model="isFilterable">
                                                    <span class="text-xs text-gray-700 dark:text-gray-300">قابل فیلتر</span>
                                                </label>
                                                @if($attr->type === 'number')
                                                    <label class="flex items-center gap-2 cursor-pointer" x-show="isFilterable">
                                                        <input type="checkbox" name="is_range_filter" value="1" class="{{ $checkboxClass }}" x-model="isRangeFilter">
                                                        <span class="text-xs text-gray-700 dark:text-gray-300">فیلتر بازه‌ای</span>
                                                    </label>
                                                @endif
                                            </div>
                                            <div class="flex gap-2">
                                                <button type="submit" class="px-3 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700">ذخیره</button>
                                                <button type="button" @click="editing = false" class="px-3 py-1 bg-gray-200 text-gray-700 text-xs rounded hover:bg-gray-300">انصراف</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2" x-show="!editing">
                                    <button @click="editing = true" class="p-2 text-gray-400 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors" title="ویرایش">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <form action="{{ route('user.settings.properties.attributes.destroy', $attr) }}" method="POST" onsubmit="return confirm('آیا از حذف این ویژگی اطمینان دارید؟ تمام مقادیر مرتبط در املاک نیز حذف خواهند شد.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="حذف">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10 flex flex-col items-center justify-center text-gray-400 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl">
                                <svg class="w-12 h-12 mb-3 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                <p class="text-sm">هنوز هیچ ویژگی‌ای تعریف نشده است.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Tab 2: Features Attributes --}}
                <div x-show="activeTab === 'features'" x-transition:enter.duration.300ms x-transition:enter.opacity style="display: none;">

                    {{-- فرم افزودن --}}
                    <div class="bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-800 rounded-xl p-5 mb-8" x-data="{ isFilterable: true }">
                        <h3 class="text-sm font-bold text-emerald-900 dark:text-emerald-100 mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            افزودن امکانات رفاهی جدید
                        </h3>
                        <form action="{{ route('user.settings.properties.attributes.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="section" value="features">
                            {{-- Features are always checkbox/boolean in logic, but stored as attribute --}}
                            <input type="hidden" name="type" value="checkbox">

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                                <div class="md:col-span-6">
                                    <label class="{{ $labelClass }}">عنوان امکانات</label>
                                    <input type="text" name="name" class="{{ $inputClass }}" required placeholder="مثلاً: استخر، سونا، روف گاردن، لابی من">
                                </div>

                                <div class="md:col-span-3 pb-3">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="is_filterable" value="1" class="{{ $checkboxClass }}" x-model="isFilterable">
                                        <span class="text-xs text-gray-700 dark:text-gray-300">قابل فیلتر در جستجو</span>
                                    </label>
                                </div>

                                <div class="md:col-span-3">
                                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-emerald-600 text-white font-bold text-sm shadow-lg shadow-emerald-500/30 hover:bg-emerald-700 transition-all active:scale-95 flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        افزودن
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- لیست امکانات --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @forelse($featuresAttributes as $attr)
                            <div class="group flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-emerald-300 dark:hover:border-emerald-700 hover:shadow-sm transition-all" x-data="{ editing: false, name: '{{ $attr->name }}', isFilterable: {{ $attr->is_filterable ? 'true' : 'false' }} }">
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="w-2 h-2 rounded-full bg-emerald-400" x-show="!editing"></div>
                                    <div class="flex-1">
                                        <div x-show="!editing" class="flex items-center gap-2">
                                            <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $attr->name }}</h4>
                                            @if($attr->is_filterable)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300" title="قابل فیلتر">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                                                </span>
                                            @endif
                                        </div>

                                        {{-- فرم ویرایش --}}
                                        <form x-show="editing" action="{{ route('user.settings.properties.attributes.update', $attr) }}" method="POST" class="flex flex-col gap-3 mt-2 w-full">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="type" value="checkbox">
                                            <div class="flex gap-2 w-full">
                                                <input type="text" name="name" x-model="name" class="{{ $inputClass }} py-1.5 text-xs w-full" required>
                                            </div>
                                            <div class="flex gap-4">
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox" name="is_filterable" value="1" class="{{ $checkboxClass }}" x-model="isFilterable">
                                                    <span class="text-xs text-gray-700 dark:text-gray-300">قابل فیلتر</span>
                                                </label>
                                            </div>
                                            <div class="flex gap-2">
                                                <button type="submit" class="px-3 py-1 bg-emerald-600 text-white text-xs rounded hover:bg-emerald-700">ذخیره</button>
                                                <button type="button" @click="editing = false" class="px-3 py-1 bg-gray-200 text-gray-700 text-xs rounded hover:bg-gray-300">انصراف</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2" x-show="!editing">
                                    <button @click="editing = true" class="p-1.5 text-gray-400 hover:text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-colors" title="ویرایش">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <form action="{{ route('user.settings.properties.attributes.destroy', $attr) }}" method="POST" onsubmit="return confirm('آیا از حذف این مورد اطمینان دارید؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="حذف">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2 text-center py-10 flex flex-col items-center justify-center text-gray-400 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl">
                                <svg class="w-12 h-12 mb-3 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                                <p class="text-sm">هنوز هیچ امکاناتی تعریف نشده است.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function attributeSettings() {
            return {
                activeTab: 'details',
                newType: 'text',
                isFilterable: false
            }
        }
    </script>
@endsection
