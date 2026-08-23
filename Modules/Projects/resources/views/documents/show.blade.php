@extends('layouts.user')
@section('title', 'مشاهده سند «' . $document->title . '»')

@php
    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $jalaliCreated = $document->created_at
        ? (function_exists('jdate') ? $faNum(jdate($document->created_at)->format('Y/m/d ساعت H:i')) : $document->created_at->format('Y/m/d H:i'))
        : '-';

    $isImage = $document->type === 'file' && $document->file_path && in_array(
        strtolower(pathinfo($document->file_original_name ?? $document->file_path, PATHINFO_EXTENSION)),
        ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg']
    );
@endphp

@section('content')
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- Header & Breadcrumbs --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 shrink-0">
                    @if($document->type === 'file')
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    @else
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    @endif
                </span>
                <div>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <a href="{{ route('projects.projects.show', $project) }}"
                           class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                            {{ $project->title }}
                        </a>
                        <span>/</span>
                        <a href="{{ route('projects.projects.show', ['project' => $project->id, 'tab' => 'documents']) }}"
                           class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                            اسناد و پیوست‌ها
                        </a>
                        <span>/</span>
                        <span class="text-gray-700 dark:text-gray-200 font-bold">جزئیات سند</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $document->title }}</h1>
                </div>
            </div>

            <a href="{{ route('projects.projects.show', ['project' => $project->id, 'tab' => 'documents']) }}"
               class="px-4 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-xl text-xs sm:text-sm font-bold hover:bg-gray-200 dark:hover:bg-gray-700 transition-all flex items-center gap-2 self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت به اسناد پروژه
            </a>
        </div>

        {{-- Main Document Card --}}
        <div
            class="bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-sm overflow-hidden divide-y divide-gray-100 dark:divide-gray-700/60">

            {{-- Top Info Row --}}
            <div
                class="p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-gray-50/50 to-transparent dark:from-gray-800/40">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($document->category)
                            <span
                                class="px-3 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40">
                                {{ $document->category }}
                            </span>
                        @endif
                        <span
                            class="px-3 py-1 rounded-xl text-xs font-bold {{ $document->type === 'file' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/40' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40' }}">
                            {{ $document->type === 'file' ? 'فایل پیوست' : 'پیوند اینترنتی (لینک)' }}
                        </span>
                    </div>
                </div>

                {{-- Action Buttons in Header --}}
                <div class="flex items-center gap-3 self-end sm:self-center">
                    @if($document->type === 'file' && $document->file_path)
                        <a href="{{ route('projects.projects.documents.download', ['project' => $project->id, 'document' => $document->id]) }}"
                           class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            دانلود فایل
                        </a>
                    @elseif($document->type === 'link' && $document->link_url)
                        <a href="{{ $document->link_url }}" target="_blank" rel="noopener noreferrer"
                           class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white font-bold text-xs shadow-md shadow-emerald-500/20 hover:bg-emerald-700 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            باز کردن در پنجره جدید
                        </a>
                    @endif

                    @can('deleteDocument', $project)
                        <form method="POST"
                              action="{{ route('projects.projects.documents.destroy', ['project' => $project->id, 'document' => $document->id]) }}"
                              onsubmit="return confirm('آیا از حذف این سند اطمینان دارید؟')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="p-2.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-900/20 dark:text-rose-400 dark:hover:bg-rose-900/40 transition-all cursor-pointer"
                                    title="حذف سند">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    @endcan
                </div>
            </div>

            {{-- Description (if present) --}}
            @if($document->description)
                <div class="p-6 sm:p-8 space-y-2">
                    <h3 class="text-xs font-bold text-gray-500 dark:text-gray-400">توضیحات و یادداشت سند:</h3>
                    <p class="text-sm text-gray-700 dark:text-gray-200 leading-relaxed whitespace-pre-line bg-gray-50 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800">
                        {{ $document->description }}
                    </p>
                </div>
            @endif

            {{-- Image Preview (if image file) --}}
            @if($isImage && $document->file_url)
                <div class="p-6 sm:p-8 space-y-3">
                    <h3 class="text-xs font-bold text-gray-500 dark:text-gray-400">پیش‌نمایش تصویر:</h3>
                    <div
                        class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/60 p-3 max-w-2xl overflow-hidden">
                        <img src="{{ $document->file_url }}" alt="{{ $document->title }}"
                             class="rounded-xl w-full h-auto max-h-96 object-contain">
                    </div>
                </div>
            @endif

            {{-- Metadata Grid --}}
            <div class="p-6 sm:p-8">
                <h3 class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-4">اطلاعات و مشخصات فنی سند:</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                    {{-- Uploaded By --}}
                    <div
                        class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800 space-y-1">
                        <span class="block text-[11px] text-gray-400">بارگذاری شده توسط</span>
                        <div class="flex items-center gap-2 pt-0.5">
                            <span
                                class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 font-bold text-xs flex items-center justify-center">
                                {{ mb_substr($document->uploader?->name ?? 'ک', 0, 1) }}
                            </span>
                            <span
                                class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $document->uploader?->name ?? 'کاربر' }}</span>
                        </div>
                    </div>

                    {{-- Created At --}}
                    <div
                        class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800 space-y-1">
                        <span class="block text-[11px] text-gray-400">تاریخ و زمان ثبت</span>
                        <span
                            class="text-sm font-bold text-gray-800 dark:text-gray-200 block pt-0.5">{{ $jalaliCreated }}</span>
                    </div>

                    {{-- Category --}}
                    <div
                        class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800 space-y-1">
                        <span class="block text-[11px] text-gray-400">دسته‌بندی موضوعی</span>
                        <span
                            class="text-sm font-bold text-gray-800 dark:text-gray-200 block pt-0.5">{{ $document->category ?: 'عمومی (بدون دسته)' }}</span>
                    </div>

                    @if($document->type === 'file')
                        {{-- Original File Name --}}
                        <div
                            class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800 space-y-1 sm:col-span-2">
                            <span class="block text-[11px] text-gray-400">نام فایل اصلی</span>
                            <span
                                class="text-xs font-bold text-gray-800 dark:text-gray-200 block pt-0.5 truncate dir-ltr text-right"
                                title="{{ $document->file_original_name }}">
                                {{ $document->file_original_name ?: '-' }}
                            </span>
                        </div>

                        {{-- File Size --}}
                        <div
                            class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800 space-y-1">
                            <span class="block text-[11px] text-gray-400">حجم فایل</span>
                            <span
                                class="text-sm font-bold text-indigo-600 dark:text-indigo-400 block pt-0.5">{{ $faNum($document->human_file_size) ?: '-' }}</span>
                        </div>
                    @else
                        {{-- External Link URL --}}
                        <div
                            class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800 space-y-1 sm:col-span-3">
                            <span class="block text-[11px] text-gray-400">آدرس پیوند (URL)</span>
                            <a href="{{ $document->link_url }}" target="_blank" rel="noopener noreferrer"
                               class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline block pt-0.5 truncate dir-ltr text-right">
                                {{ $document->link_url }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
