@extends('layouts.user')
@section('title', 'دسته‌بندی: ' . $category->name)

@php
    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };
@endphp

@section('content')
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- Top Header & Actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-lg shrink-0 overflow-hidden"
                      style="background: {{ $category->color }}20; border: 2px solid {{ $category->color }}55; shadow-color: {{ $category->color }}40">
                    @if($category->icon_url)
                        <img src="{{ $category->icon_url }}" alt="{{ $category->name }}"
                             class="w-full h-full object-cover">
                    @else
                        <span class="w-4 h-4 rounded-full" style="background: {{ $category->color }}"></span>
                    @endif
                </span>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                        {{ $category->name }}
                        @if($category->status === 'active')
                            <span
                                class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400">فعال</span>
                        @else
                            <span
                                class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-500 border border-gray-200 dark:bg-gray-700/50 dark:text-gray-400">غیرفعال</span>
                        @endif
                    </h1>
                    <p class="text-xs font-mono text-gray-400 mt-0.5">{{ $category->slug }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                @can('projects.categories.manage')
                    <a href="{{ route('projects.categories.edit', $category) }}"
                       class="px-4 py-2.5 rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 font-bold text-xs hover:bg-amber-100 transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        ویرایش
                    </a>

                    @if($category->projects->count() > 0)
                        <span
                            class="px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600 font-bold text-xs cursor-not-allowed flex items-center gap-1.5 opacity-60"
                            title="امکان حذف وجود ندارد: این دسته‌بندی دارای {{ $faNum($category->projects->count()) }} پروژه متصل است">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            حذف دسته‌بندی
                        </span>
                    @else
                        <form method="POST" action="{{ route('projects.categories.destroy', $category) }}"
                              onsubmit="return confirm('آیا از حذف دسته‌بندی «{{ $category->name }}» مطمئن هستید؟')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="px-4 py-2.5 rounded-xl bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 font-bold text-xs hover:bg-rose-100 transition-all flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                حذف دسته‌بندی
                            </button>
                        </form>
                    @endif
                @endcan
                @can('projects.create')
                    <a href="{{ route('projects.projects.create', ['category_id' => $category->id]) }}"
                       class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-md shadow-indigo-500/30 hover:bg-indigo-700 transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        پروژه جدید در این دسته
                    </a>
                @endcan
                <a href="{{ route('projects.categories.index') }}"
                   class="px-4 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-bold hover:bg-gray-200 transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    لیست دسته‌ها
                </a>
            </div>
        </div>

        {{-- Category Details & Stats Card --}}
        <div
            class="bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm p-6 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div class="p-4 bg-gray-50 dark:bg-gray-900/40 rounded-2xl border border-gray-100 dark:border-gray-800">
                    <span class="text-gray-400 block mb-1">کل پروژه‌های متصل</span>
                    <span
                        class="text-xl font-black text-gray-900 dark:text-white">{{ $faNum($category->projects->count()) }}</span>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-900/40 rounded-2xl border border-gray-100 dark:border-gray-800">
                    <span class="text-gray-400 block mb-1">ترتیب نمایش</span>
                    <span
                        class="text-xl font-black text-gray-900 dark:text-white">{{ $faNum($category->sort_order) }}</span>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-900/40 rounded-2xl border border-gray-100 dark:border-gray-800">
                    <span class="text-gray-400 block mb-1">کد رنگی</span>
                    <span class="text-sm font-black font-mono flex items-center gap-2"
                          style="color: {{ $category->color }}">
                        <span class="w-3.5 h-3.5 rounded-md inline-block shadow-sm"
                              style="background: {{ $category->color }}"></span>
                        {{ $category->color }}
                    </span>
                </div>
            </div>

            @if($category->description)
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700/50">
                    <span class="text-xs font-bold text-gray-400 block mb-1">توضیحات:</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $category->description }}</p>
                </div>
            @endif
        </div>

        {{-- Projects in this category --}}
        <div
            class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700/50 flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    پروژه‌های این دسته‌بندی
                </h3>
                <span class="text-xs font-bold text-gray-400">{{ $faNum($category->projects->count()) }} پروژه</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-6 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            پروژه و کد
                        </th>
                        <th class="px-6 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            مشتری
                        </th>
                        <th class="px-6 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            پیشرفت
                        </th>
                        <th class="px-6 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            وضعیت
                        </th>
                        <th class="px-6 py-4 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-end">
                            عملیات
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                    @forelse($category->projects as $project)
                        <tr class="group hover:bg-gray-50/70 dark:hover:bg-gray-700/20 transition-colors">
                            <td class="px-6 py-4">
                                <a href="{{ route('projects.projects.show', $project) }}"
                                   class="font-bold text-gray-900 dark:text-white text-sm hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    {{ $project->title }}
                                </a>
                                @if($project->code)
                                    <div class="text-xs text-gray-400 font-mono mt-0.5">{{ $project->code }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">
                                    {{ $project->client?->full_name ?? '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="w-28 mx-auto space-y-1">
                                    <div
                                        class="flex items-center justify-between text-xs font-bold text-gray-700 dark:text-gray-300">
                                        <span>پیشرفت</span>
                                        <span class="text-indigo-600 dark:text-indigo-400">{{ $faNum($project->progress) }}٪</span>
                                    </div>
                                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-indigo-600 h-1.5 rounded-full transition-all duration-300"
                                             style="width: {{ $project->progress }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($project->status)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border shadow-sm"
                                        style="background-color: {{ $project->status->color }}1a; color: {{ $project->status->color }}; border-color: {{ $project->status->color }}33;">
                                        <span class="w-1.5 h-1.5 rounded-full"
                                              style="background-color: {{ $project->status->color }}"></span>
                                        {{ $project->status->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-end">
                                <a href="{{ route('projects.projects.show', $project) }}"
                                   class="p-2 rounded-xl text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-all inline-block"
                                   title="مشاهده پروژه">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 text-xs">
                                هنوز پروژه‌ای در این دسته‌بندی تعریف نشده است.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
