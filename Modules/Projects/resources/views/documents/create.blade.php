@extends('layouts.user')
@section('title', 'افزودن سند به پروژه «' . $project->title . '»')

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6" x-data="{ docType: 'file' }">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <span
                    class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </span>
                ثبت سند جدید در «{{ $project->title }}»
            </h1>
            <a href="{{ route('projects.projects.show', ['project' => $project->id, 'tab' => 'documents']) }}"
               class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-bold hover:bg-gray-200 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت به اسناد
            </a>
        </div>

        @if($errors->any())
            <div
                class="rounded-2xl bg-red-50 p-4 border border-red-100 dark:bg-red-900/10 dark:border-red-800/30 text-red-700 dark:text-red-400 text-sm font-medium">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('projects.projects.documents.store', $project) }}" method="POST"
              enctype="multipart/form-data"
              class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-6 sm:p-8 space-y-6">
            @csrf

            {{-- Type Selector --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">نوع سند</label>
                <div class="grid grid-cols-2 gap-4">
                    <label
                        class="flex items-center justify-center gap-2 p-4 rounded-2xl border cursor-pointer text-xs font-bold transition-all"
                        :class="docType === 'file' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 dark:border-indigo-500' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400'">
                        <input type="radio" name="type" value="file" x-model="docType" class="sr-only">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        آپلود فایل (PDF, تصویر, آرشیو و ...)
                    </label>
                    <label
                        class="flex items-center justify-center gap-2 p-4 rounded-2xl border cursor-pointer text-xs font-bold transition-all"
                        :class="docType === 'link' ? 'border-emerald-600 bg-emerald-50/50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-500' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400'">
                        <input type="radio" name="type" value="link" x-model="docType" class="sr-only">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        پیوند اینترنتی یا لینک خارجی
                    </label>
                </div>
            </div>

            {{-- Category Select --}}
            @php
                $configuredCategories = \Modules\Projects\App\Http\Models\ProjectDocument::getCategories();
            @endphp
            @if(count($configuredCategories) > 0)
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">دسته‌بندی موضوعی
                        سند</label>
                    <select name="category"
                            class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white transition-all">
                        <option value="">بدون دسته‌بندی (عمومی)</option>
                        @foreach($configuredCategories as $cat)
                            <option value="{{ $cat }}" @selected(old('category') === $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Title --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">عنوان سند <span
                        class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       placeholder="مثال: قرارداد رسمی نسخه نهایی"
                       class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white">
            </div>

            @php
                $allowedExtsArray = \Modules\Projects\App\Http\Models\ProjectDocument::getAllowedExtensions();
                $acceptAttr = !empty($allowedExtsArray) ? ('.' . implode(',.', $allowedExtsArray)) : '*';
                $maxSizeMb = \Modules\Projects\App\Http\Models\ProjectDocument::getMaxFileSizeMb();
                $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                $maxSizeFa = str_replace(range(0, 9), $persianDigits, (string)$maxSizeMb);
            @endphp

            {{-- File Input --}}
            <div x-show="docType === 'file'" class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                        انتخاب فایل <span class="text-red-500">*</span>
                    </label>
                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                        حداکثر حجم: {{ $maxSizeFa }} مگابایت
                    </span>
                </div>
                <input type="file" name="file" :required="docType === 'file'"
                       accept="{{ $acceptAttr }}"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-3 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all cursor-pointer">

                @if(!empty($allowedExtsArray))
                    <div
                        class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-700/60 text-xs text-gray-500 dark:text-gray-400 flex items-start gap-2">
                        <svg class="w-4 h-4 text-indigo-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <span class="font-bold text-gray-700 dark:text-gray-300">فرمت‌های مجاز:</span>
                            <span
                                class="font-mono text-xs text-gray-600 dark:text-gray-400 dir-ltr inline-block mr-1 leading-normal">{{ implode(', ', $allowedExtsArray) }}</span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Link URL Input --}}
            <div x-show="docType === 'link'">
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">آدرس اینترنتی (URL) <span
                        class="text-red-500">*</span></label>
                <input type="url" name="link_url" value="{{ old('link_url') }}" :required="docType === 'link'"
                       placeholder="https://example.com/document" dir="ltr"
                       class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white font-mono text-left">
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">توضیحات تکمیلی</label>
                <textarea name="description" rows="3" placeholder="توضیحات در مورد محتوا، نسخه یا نکات این سند..."
                          class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white">{{ old('description') }}</textarea>
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('projects.projects.show', ['project' => $project->id, 'tab' => 'documents']) }}"
                   class="px-6 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-sm hover:bg-gray-200 transition-all">
                    انصراف
                </a>
                <button type="submit"
                        class="px-8 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    ثبت و آپلود سند
                </button>
            </div>
        </form>
    </div>
@endsection
