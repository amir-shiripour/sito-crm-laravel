@extends('layouts.user')
@section('title', 'الگوهای ساختار فاز و کار (Templates)')

@php
    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $formatJalali = function($date) use ($faNum) {
        if (!$date) return '-';
        try {
            if (function_exists('jdate')) {
                return $faNum(jdate($date)->format('Y/m/d'));
            }
            if (class_exists(\Morilog\Jalali\Jalalian::class)) {
                return $faNum(\Morilog\Jalali\Jalalian::fromCarbon($date)->format('Y/m/d'));
            }
            return $faNum($date instanceof \DateTimeInterface ? $date->format('Y/m/d') : (string)$date);
        } catch (\Throwable) {
            return $faNum($date instanceof \DateTimeInterface ? $date->format('Y/m/d') : (string)$date);
        }
    };
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm font-medium" aria-label="Breadcrumb">
            <a href="{{ route('projects.projects.index') }}"
               class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">پروژه‌ها</a>
            <svg class="w-4 h-4 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="text-gray-900 dark:text-white font-bold">الگوهای ساختار فاز و کار</span>
        </nav>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div
                class="rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 p-4 border border-emerald-200/60 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-300 text-sm font-bold flex items-center gap-3 shadow-xs">
                <span
                    class="bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 p-1.5 rounded-full shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
                {{ session('success') }}
            </div>
        @endif

        {{-- Header Card --}}
        <div
            class="bg-white dark:bg-gray-800/80 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700/50 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6 backdrop-blur-xl">
            <div class="flex items-center gap-4">
                <div
                    class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white flex items-center justify-center shadow-lg shadow-indigo-500/25 shrink-0">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white">الگوهای ساختار فاز، گروه و
                        کارها</h1>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                        تعریف ساختارهای آماده و استاندارد برای فازبندی،گروه ها و کارها جهت استفاده در پروژه‌ها
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('projects.templates.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-indigo-500/20 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>ایجاد الگوی جدید</span>
                </a>
            </div>
        </div>

        {{-- Statistics Row --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div
                class="bg-white dark:bg-gray-800/80 rounded-2xl p-4 border border-gray-100 dark:border-gray-700/50 shadow-xs">
                <span class="text-xs text-gray-400 block font-medium">تعداد کل الگوها</span>
                <strong
                    class="text-xl font-black text-gray-900 dark:text-white mt-1 block">{{ $faNum($stats['total_templates']) }}</strong>
            </div>
            <div
                class="bg-white dark:bg-gray-800/80 rounded-2xl p-4 border border-gray-100 dark:border-gray-700/50 shadow-xs">
                <span class="text-xs text-gray-400 block font-medium">مجموع فازهای الگو</span>
                <strong
                    class="text-xl font-black text-purple-600 dark:text-purple-400 mt-1 block">{{ $faNum($stats['total_phases']) }}</strong>
            </div>
            <div
                class="bg-white dark:bg-gray-800/80 rounded-2xl p-4 border border-gray-100 dark:border-gray-700/50 shadow-xs">
                <span class="text-xs text-gray-400 block font-medium">مجموع گروه‌ها</span>
                <strong
                    class="text-xl font-black text-indigo-600 dark:text-indigo-400 mt-1 block">{{ $faNum($stats['total_tasks']) }}</strong>
            </div>
            <div
                class="bg-white dark:bg-gray-800/80 rounded-2xl p-4 border border-gray-100 dark:border-gray-700/50 shadow-xs">
                <span class="text-xs text-gray-400 block font-medium">مجموع کارها</span>
                <strong
                    class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1 block">{{ $faNum($stats['total_items']) }}</strong>
            </div>
        </div>

        {{-- Filters Form --}}
        <form method="GET" action="{{ route('projects.templates.index') }}"
              class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">
                <div class="relative lg:col-span-6">
                    <div class="absolute inset-y-0 start-0 ps-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="جستجو بر اساس عنوان یا توضیحات الگو..."
                           class="w-full rounded-xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 ps-11 pe-4 py-2.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
                </div>

                <div class="lg:col-span-4">
                    <select name="category_id"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="">همه دسته‌بندی‌ها</option>
                        @foreach($categories as $cat)
                            <option
                                value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all cursor-pointer">
                        اعمال فیلتر
                    </button>
                    @if(request()->hasAny(['search', 'category_id']))
                        <a href="{{ route('projects.templates.index') }}"
                           class="p-2.5 rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-all shrink-0"
                           title="پاکسازی فیلترها">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Templates Grid --}}
        @if($templates->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($templates as $tpl)
                    <div
                        class="bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-xs hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-800/50 transition-all p-6 flex flex-col justify-between group">
                        <div class="space-y-4">
                            {{-- Card Header --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <h3 class="font-bold text-gray-900 dark:text-white text-base group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors line-clamp-1">
                                            {{ $tpl->title }}
                                        </h3>
                                        @if($tpl->category)
                                            <span
                                                class="inline-flex items-center gap-1 mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold border"
                                                style="background: {{ $tpl->category->color }}15; color: {{ $tpl->category->color }}; border-color: {{ $tpl->category->color }}33;">
                                                {{ $tpl->category->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Description --}}
                            @if($tpl->description)
                                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed line-clamp-2">
                                    {{ $tpl->description }}
                                </p>
                            @else
                                <p class="text-xs text-gray-400 italic">بدون توضیحات</p>
                            @endif

                            {{-- Structure Summary Badges --}}
                            <div
                                class="flex items-center gap-2 flex-wrap pt-2 border-t border-gray-100 dark:border-gray-700/60 text-xs">
                                <span
                                    class="px-2.5 py-1 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 font-bold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    {{ $faNum($tpl->phases_count) }} فاز
                                </span>
                                <span
                                    class="px-2.5 py-1 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-bold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/></svg>
                                    {{ $faNum($tpl->tasks_count) }} گروه
                                </span>
                                <span
                                    class="px-2.5 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-bold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"/></svg>
                                    {{ $faNum($tpl->items_count) }} کار
                                </span>
                            </div>
                        </div>

                        {{-- Card Actions --}}
                        <div
                            class="flex items-center justify-between gap-2 pt-4 mt-4 border-t border-gray-100 dark:border-gray-700/60">
                            <span class="text-[11px] text-gray-400">
                                ثبت: {{ $formatJalali($tpl->created_at) }}
                            </span>

                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('projects.templates.edit', $tpl) }}"
                                   class="p-2 rounded-xl text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/40 transition-colors"
                                   title="ویرایش الگو">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>

                                <form method="POST" action="{{ route('projects.templates.destroy', $tpl) }}"
                                      onsubmit="return confirm('آیا از حذف الگوی «{{ $tpl->title }}» اطمینان دارید؟')"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="p-2 rounded-xl text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/40 transition-colors cursor-pointer"
                                            title="حذف الگو">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $templates->links() }}
            </div>
        @else
            <div
                class="bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-100 dark:border-gray-700/50 p-12 text-center space-y-4 shadow-xs">
                <div
                    class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto shadow-xs">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">هیچ الگویی یافت نشد</h3>
                <p class="text-xs text-gray-400 max-w-sm mx-auto">
                    هنوز الگویی برای فازها و کارها تعریف نشده است. می‌توانید اولین الگوی ساختاریافته خود را ایجاد کنید
                    یا از داخل پروژه‌ها استخراج کنید.
                </p>
                <div class="pt-2">
                    <a href="{{ route('projects.templates.create') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all">
                        + ایجاد اولین الگو
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection
