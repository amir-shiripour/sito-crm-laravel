@extends('layouts.user')

@php
    $title = 'مدیریت ویژگی‌های ملک';
@endphp

@section('content')
<div class="max-w-4xl mx-auto" x-data="{ activeTab: 'details' }">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">مدیریت ویژگی‌ها و امکانات</h1>
        <a href="{{ route('user.settings.properties.index') }}" class="text-sm text-gray-500 hover:text-gray-700">بازگشت به تنظیمات</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="flex border-b border-gray-200 dark:border-gray-700">
            <button @click="activeTab = 'details'" :class="activeTab === 'details' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'" class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">
                اطلاعات تکمیلی
            </button>
            <button @click="activeTab = 'features'" :class="activeTab === 'features' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'" class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">
                امکانات
            </button>
        </div>

        <div class="p-6">
            {{-- Details Tab --}}
            <div x-show="activeTab === 'details'">
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">افزودن ویژگی جدید</h3>
                    <form action="{{ route('user.settings.properties.attributes.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        @csrf
                        <input type="hidden" name="section" value="details">

                        <div class="md:col-span-1">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">عنوان</label>
                            <input type="text" name="name" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm" required placeholder="مثلا: سال ساخت">
                        </div>

                        <div class="md:col-span-1">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">نوع ورودی</label>
                            <select name="type" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm" x-data="{ type: 'text' }" x-model="type" @change="$dispatch('type-changed', type)">
                                <option value="text">متن (Text)</option>
                                <option value="number">عدد (Number)</option>
                                <option value="select">لیست کشویی (Select)</option>
                            </select>
                        </div>

                        <div class="md:col-span-1" x-data="{ show: false }" @type-changed.window="show = $event.detail === 'select'">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1" :class="{ 'opacity-50': !show }">گزینه‌ها (با کاما جدا کنید)</label>
                            <input type="text" name="options" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm" :disabled="!show" placeholder="گزینه ۱, گزینه ۲">
                        </div>

                        <div class="md:col-span-1">
                            <button type="submit" class="w-full px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 text-sm">افزودن</button>
                        </div>
                    </form>
                </div>

                <div class="space-y-2">
                    @foreach($detailsAttributes as $attr)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $attr->name }}</span>
                                <span class="text-xs px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-gray-600 dark:text-gray-300">{{ $attr->type }}</span>
                                @if($attr->type === 'select')
                                    <span class="text-xs text-gray-500">({{ implode(', ', $attr->options ?? []) }})</span>
                                @endif
                            </div>
                            <form action="{{ route('user.settings.properties.attributes.destroy', $attr) }}" method="POST" onsubmit="return confirm('آیا مطمئن هستید؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                    @if($detailsAttributes->isEmpty())
                        <p class="text-center text-gray-500 text-sm py-4">هیچ ویژگی تعریف نشده است.</p>
                    @endif
                </div>
            </div>

            {{-- Features Tab --}}
            <div x-show="activeTab === 'features'" style="display: none;">
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">افزودن امکانات جدید</h3>
                    <form action="{{ route('user.settings.properties.attributes.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        @csrf
                        <input type="hidden" name="section" value="features">
                        <input type="hidden" name="type" value="checkbox"> {{-- Features are always checkboxes --}}

                        <div class="md:col-span-3">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">عنوان امکانات</label>
                            <input type="text" name="name" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm" required placeholder="مثلا: استخر، سونا، جکوزی">
                        </div>

                        <div class="md:col-span-1">
                            <button type="submit" class="w-full px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 text-sm">افزودن</button>
                        </div>
                    </form>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach($featuresAttributes as $attr)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-700">
                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ $attr->name }}</span>
                            <form action="{{ route('user.settings.properties.attributes.destroy', $attr) }}" method="POST" onsubmit="return confirm('آیا مطمئن هستید؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
                @if($featuresAttributes->isEmpty())
                    <p class="text-center text-gray-500 text-sm py-4">هیچ امکاناتی تعریف نشده است.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
