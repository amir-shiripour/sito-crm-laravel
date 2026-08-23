@extends('layouts.user')
@section('title', 'دسته‌بندی‌های پروژه')

@php
    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                <span
                    class="flex items-center justify-center w-12 h-12 rounded-2xl bg-linear-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </span>
                دسته‌بندی‌های پروژه
            </h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('projects.projects.index') }}"
                   class="px-4 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-bold hover:bg-gray-200 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    پروژه‌ها
                </a>
                @can('projects.categories.manage')
                    <a href="{{ route('projects.categories.create') }}"
                       class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-md shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/40 transition-all duration-200 active:scale-95">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        دسته‌بندی جدید
                    </a>
                @endcan
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div
                class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-500/10 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-400 text-sm font-bold flex items-center gap-3">
                <span class="bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 p-1.5 rounded-full shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div
                class="rounded-2xl bg-red-50 p-4 border border-red-100 dark:bg-red-500/10 dark:border-red-500/20 text-red-800 dark:text-red-400 text-sm font-bold flex items-center gap-3">
                <span class="bg-red-100 text-red-600 dark:bg-red-500/20 p-1.5 rounded-full shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </span>
                {{ session('error') }}
            </div>
        @endif

        {{-- Table Container --}}
        <div
            class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            دسته‌بندی
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            توضیحات
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            تعداد پروژه
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            ترتیب
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            وضعیت
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-end">
                            عملیات
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                    @forelse($categories as $category)
                        <tr class="group hover:bg-gray-50/70 dark:hover:bg-gray-700/20 transition-colors duration-200">
                            {{-- Category Name & Color --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="w-10 h-10 rounded-2xl flex items-center justify-center shadow-sm shrink-0 overflow-hidden"
                                        style="background: {{ $category->color }}20; border: 1.5px solid {{ $category->color }}44">
                                        @if($category->icon_url)
                                            <img src="{{ $category->icon_url }}" alt="{{ $category->name }}"
                                                 class="w-full h-full object-cover">
                                        @else
                                            <span class="w-3 h-3 rounded-full"
                                                  style="background: {{ $category->color }}"></span>
                                        @endif
                                    </span>
                                    <div>
                                        <a href="{{ route('projects.categories.show', $category) }}"
                                           class="font-bold text-gray-900 dark:text-white text-sm hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                            {{ $category->name }}
                                        </a>
                                        <div class="text-xs font-mono text-gray-400 mt-0.5">{{ $category->slug }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Description --}}
                            <td class="px-6 py-4">
                                @if($category->description)
                                    <span
                                        class="text-sm text-gray-600 dark:text-gray-300 line-clamp-1 max-w-xs block">{{ $category->description }}</span>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>

                            {{-- Projects Count --}}
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/40">
                                    {{ $faNum($category->projects_count) }} پروژه
                                </span>
                            </td>

                            {{-- Sort Order --}}
                            <td class="px-6 py-4 text-center text-xs font-bold text-gray-400">
                                {{ $faNum($category->sort_order) }}
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 text-center">
                                @if($category->status === 'active')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        فعال
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-500 border border-gray-200 dark:bg-gray-700/50 dark:text-gray-400 dark:border-gray-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                        غیرفعال
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-end">
                                <div
                                    class="flex items-center justify-end gap-1 opacity-100 sm:opacity-40 group-hover:opacity-100 transition-opacity duration-200">
                                    <a href="{{ route('projects.categories.show', $category) }}"
                                       class="p-2 rounded-xl text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-all hover:scale-110"
                                       title="مشاهده جزئیات">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    @can('projects.categories.manage')
                                        <a href="{{ route('projects.categories.edit', $category) }}"
                                           class="p-2 rounded-xl text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-all hover:scale-110"
                                           title="ویرایش">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        @if($category->projects_count > 0)
                                            <span class="p-2 rounded-xl text-gray-300 dark:text-gray-600 cursor-not-allowed inline-flex items-center"
                                                  title="امکان حذف وجود ندارد: این دسته‌بندی دارای {{ $faNum($category->projects_count) }} پروژه متصل است">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </span>
                                        @else
                                            <form method="POST"
                                                  action="{{ route('projects.categories.destroy', $category) }}"
                                                  onsubmit="return confirm('دسته‌بندی «{{ $category->name }}» حذف شود؟')"
                                                  class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="p-2 rounded-xl text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all hover:scale-110"
                                                        title="حذف">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="max-w-sm mx-auto flex flex-col items-center">
                                    <div
                                        class="w-20 h-20 rounded-full bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center mb-4">
                                        <svg class="w-10 h-10 text-indigo-400" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">هیچ دسته‌بندی‌ای
                                        یافت نشد</h3>
                                    <p class="text-xs text-gray-400 mb-5">برای سازمان‌دهی پروژه‌ها، اولین دسته‌بندی خود
                                        را تعریف کنید.</p>
                                    @can('projects.categories.manage')
                                        <a href="{{ route('projects.categories.create') }}"
                                           class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-md hover:bg-indigo-700 transition-all">
                                            ایجاد اولین دسته‌بندی
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
