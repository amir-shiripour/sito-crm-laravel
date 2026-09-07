@php use Morilog\Jalali\Jalalian; @endphp
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
            if (class_exists(Jalalian::class)) {
                return $faNum(Jalalian::fromCarbon($date)->format('Y/m/d'));
            }
            return $faNum($date instanceof \DateTimeInterface ? $date->format('Y/m/d') : (string)$date);
        } catch (\Throwable) {
            return $faNum($date instanceof \DateTimeInterface ? $date->format('Y/m/d') : (string)$date);
        }
    };
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6"
         x-data="{ uploadModalOpen: false, fileName: '', isDragging: false }">

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

            <div class="flex items-center gap-3 shrink-0 flex-wrap">
                @if(auth()->user()?->can('projects.templates.manage'))
                    <button type="button" @click="uploadModalOpen = true"
                            class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-xs sm:text-sm font-bold border border-indigo-200 dark:border-indigo-800/40 transition-all cursor-pointer shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        <span>آپلود فایل JSON</span>
                    </button>
                @endif

                @if(auth()->user()?->can('projects.templates.create'))
                    <a href="{{ route('projects.templates.create') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-indigo-500/20 transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>ایجاد الگوی جدید</span>
                    </a>
                @endif
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
                                @if(auth()->user()?->can('projects.templates.manage'))
                                    <a href="{{ route('projects.templates.export', $tpl) }}"
                                       class="p-2 rounded-xl text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 transition-colors"
                                       title="دانلود فایل خروجی JSON الگو">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                    </a>
                                @endif

                                @if(auth()->user()?->can('projects.templates.edit'))
                                    <a href="{{ route('projects.templates.edit', $tpl) }}"
                                       class="p-2 rounded-xl text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/40 transition-colors"
                                       title="ویرایش الگو">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                @endif

                                @if(auth()->user()?->can('projects.templates.delete'))
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
                                @endif
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

        {{-- Upload JSON Modal --}}
        <div x-show="uploadModalOpen"
             x-cloak
             @keydown.escape.window="uploadModalOpen = false"
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">

            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
                     @click="uploadModalOpen = false"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal Dialog --}}
                <div
                    class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-3xl text-right overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 dark:border-gray-700/60"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <form action="{{ route('projects.templates.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Modal Header --}}
                        <div
                            class="p-6 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-xs">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                </span>
                                <div>
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white" id="modal-title">
                                        آپلود و شناسایی فایل JSON الگو
                                    </h3>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        فایل JSON ساختار الگو را انتخاب کنید تا به صورت خودکار شناسایی و ذخیره شود.
                                    </p>
                                </div>
                            </div>

                            <button type="button" @click="uploadModalOpen = false"
                                    class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Modal Body --}}
                        <div class="p-6 space-y-4">
                            {{-- Dropzone / File Picker --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                                    انتخاب فایل الگو (.json) <span class="text-red-500">*</span>
                                </label>
                                <div
                                    class="relative border-2 border-dashed rounded-2xl p-6 text-center transition-all cursor-pointer"
                                    :class="isDragging ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/20' : 'border-gray-200 dark:border-gray-700 hover:border-indigo-400'"
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="isDragging = false; if($event.dataTransfer.files.length) { $refs.fileInput.files = $event.dataTransfer.files; fileName = $event.dataTransfer.files[0].name; }">
                                    <input type="file" name="template_file" accept=".json,application/json" required
                                           x-ref="fileInput"
                                           @change="fileName = $event.target.files.length ? $event.target.files[0].name : ''"
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">

                                    <div class="space-y-2 pointer-events-none">
                                        <div
                                            class="w-12 h-12 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                                <span x-show="!fileName">فایل JSON را به اینجا بکشید یا برای انتخاب کلیک کنید</span>
                                                <span x-show="fileName"
                                                      class="text-indigo-600 dark:text-indigo-400 font-black"
                                                      x-text="fileName"></span>
                                            </p>
                                            <p class="text-[11px] text-gray-400 mt-1">
                                                فرمت‌های مجاز: فقط JSON (حداکثر ۱۰ مگابایت)
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Optional Custom Title --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                    عنوان اختصاصی الگو <span class="text-gray-400 font-normal">(اختیاری - در صورت خالی بودن از فایل خوانده می‌شود)</span>
                                </label>
                                <input type="text" name="custom_title"
                                       placeholder="مثال: الگوی توسعه نرم‌افزار..."
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-3.5 py-2.5 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                            </div>

                            {{-- Optional Category --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                    دسته‌بندی مربوطه <span class="text-gray-400 font-normal">(اختیاری)</span>
                                </label>
                                <select name="category_id"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-3.5 py-2.5 text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                                    <option value="">انتخاب دسته‌بندی...</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Note / Tip Box --}}
                            <div
                                class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-800/40 text-[11px] text-amber-800 dark:text-amber-300 flex items-start gap-2">
                                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>سیستم به صورت خودکار فازها، گروه‌های وظیفه و کارهای ثبت‌شده در فایل JSON را شناسایی کرده و الگو را برای ویرایش و استفاده در پروژه‌ها آماده می‌سازد.</span>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div
                            class="p-6 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-end gap-3 bg-gray-50/50 dark:bg-gray-900/30">
                            <button type="button" @click="uploadModalOpen = false"
                                    class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-100 transition-colors">
                                انصراف
                            </button>
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>آپلود و ذخیره الگو</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
