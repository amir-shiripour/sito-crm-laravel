@extends('layouts.user')
@section('title', 'مدیریت پروژه‌ها')

@php
    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };

    $formatJalali = function($date) use ($faNum) {
        if (!$date) return '';
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
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3 tracking-tight">
                <span class="flex items-center justify-center w-12 h-12 rounded-2xl bg-linear-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6"/>
                    </svg>
                </span>
                <span>مدیریت پروژه‌ها</span>
            </h1>

            <div class="flex items-center gap-3 flex-wrap">
                @can('projects.create')
                    <a href="{{ route('projects.projects.create') }}"
                       class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-md shadow-indigo-500/30 hover:bg-indigo-700 transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>پروژه جدید</span>
                    </a>
                @endcan
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-500/10 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-400 text-sm font-bold flex items-center gap-3">
                <span class="bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 p-1.5 rounded-full shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
                {{ session('success') }}
            </div>
        @endif

        {{-- Quick Stats Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2.5 sm:gap-3">
            <div class="bg-white dark:bg-gray-800/60 p-3.5 sm:p-4 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <span class="text-[11px] sm:text-xs font-bold text-gray-400 dark:text-gray-400 truncate block">کل پروژه‌ها</span>
                    <p class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white mt-0.5">{{ $faNum($stats['total']) }}</p>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800/60 p-3.5 sm:p-4 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <span class="text-[11px] sm:text-xs font-bold text-gray-400 dark:text-gray-400 truncate block">در صف</span>
                    <p class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 mt-0.5">{{ $faNum($stats['queued']) }}</p>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800/60 p-3.5 sm:p-4 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <span class="text-[11px] sm:text-xs font-bold text-gray-400 dark:text-gray-400 truncate block">در حال انجام</span>
                    <p class="text-xl sm:text-2xl font-black text-blue-600 dark:text-blue-400 mt-0.5">{{ $faNum($stats['in_progress']) }}</p>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800/60 p-3.5 sm:p-4 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <span class="text-[11px] sm:text-xs font-bold text-gray-400 dark:text-gray-400 truncate block">تکمیل شده</span>
                    <p class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $faNum($stats['completed']) }}</p>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800/60 p-3.5 sm:p-4 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <span class="text-[11px] sm:text-xs font-bold text-gray-400 dark:text-gray-400 truncate block">دارای تعویق</span>
                    <p class="text-xl sm:text-2xl font-black text-orange-600 dark:text-orange-400 mt-0.5">{{ $faNum($stats['delayed']) }}</p>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800/60 p-3.5 sm:p-4 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-xs flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <span class="text-[11px] sm:text-xs font-bold text-gray-400 dark:text-gray-400 truncate block">لغو شده</span>
                    <p class="text-xl sm:text-2xl font-black text-rose-600 dark:text-rose-400 mt-0.5">{{ $faNum($stats['canceled']) }}</p>
                </div>
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Filters Form --}}
        <form method="GET" class="bg-white dark:bg-gray-800/60 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">
                <div class="relative lg:col-span-4">
                    <div class="absolute inset-y-0 start-0 ps-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="جستجو بر اساس نام یا کد..."
                           class="w-full rounded-xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 ps-11 pe-4 py-2.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white">
                </div>

                <div class="lg:col-span-3">
                    <select name="category_id" class="w-full rounded-xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="">همه دسته‌بندی‌ها</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <select name="status_id" class="w-full rounded-xl border-gray-200 bg-gray-50 dark:bg-gray-900/50 dark:border-gray-700 px-4 py-2.5 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all dark:text-white cursor-pointer">
                        <option value="">همه وضعیت‌ها</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st->id }}" @selected(request('status_id') == $st->id)>{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all">
                        اعمال فیلتر
                    </button>
                    @if(request()->hasAny(['search', 'category_id', 'status_id', 'client_id']))
                        <a href="{{ route('projects.projects.index') }}"
                           class="p-2.5 rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-all"
                           title="پاکسازی فیلترها">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Projects Table --}}
        <div class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-start divide-y divide-gray-100 dark:divide-gray-700/50">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-start">
                            عنوان و کد پروژه
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            دسته‌بندی
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            مشتری / کارفرما
                        </th>
                        <th class="px-6 py-5 font-bold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">
                            پیشرفت
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
                    @forelse($projects as $project)
                        <tr class="group hover:bg-gray-50/70 dark:hover:bg-gray-700/20 transition-colors duration-200">
                            {{-- Title & Code --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <a href="{{ route('projects.projects.show', $project) }}"
                                           class="font-bold text-gray-900 dark:text-white text-base hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors line-clamp-1">
                                            {{ $project->title }}
                                        </a>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-mono">{{ $project->code }}</span>
                                            @if($project->start_date)
                                                <span class="text-[11px] text-gray-400">
                                                    شروع: {{ $formatJalali($project->start_date) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Category --}}
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($project->category)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold shadow-xs"
                                          style="background-color: {{ $project->category->color }}15; color: {{ $project->category->color }}; border: 1px solid {{ $project->category->color }}30">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $project->category->color }}"></span>
                                        {{ $project->category->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>

                            {{-- Client --}}
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($project->client)
                                    <a href="{{ route('user.clients.show', $project->client_id) }}"
                                       class="font-bold text-xs text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                        {{ $project->client->full_name }}
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">عمومی / داخلی</span>
                                @endif
                            </td>

                            {{-- Progress --}}
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="w-28 mx-auto space-y-1">
                                    <div class="flex items-center justify-between text-xs font-bold text-gray-700 dark:text-gray-300">
                                        <span>پیشرفت</span>
                                        <span>{{ $faNum($project->progress) }}٪</span>
                                    </div>
                                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-indigo-600 h-1.5 rounded-full transition-all duration-300"
                                             style="width: {{ $project->progress }}%"></div>
                                    </div>
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($project->status)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold shadow-xs"
                                          style="background-color: {{ $project->status->color }}1a; color: {{ $project->status->color }}; border: 1px solid {{ $project->status->color }}33;">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $project->status->color }}"></span>
                                        {{ $project->status->name }}
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-end">
                                <div class="flex items-center justify-end gap-1 opacity-100 sm:opacity-60 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('projects.projects.show', $project) }}"
                                       class="p-2 rounded-xl text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-all hover:scale-110 cursor-pointer"
                                       title="مشاهده پروژه">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    @can('update', $project)
                                        <a href="{{ route('projects.projects.edit', $project) }}"
                                           class="p-2 rounded-xl text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-all hover:scale-110 cursor-pointer"
                                           title="ویرایش">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    @endcan

                                    @can('cancel', $project)
                                        @if(!$project->isCanceled())
                                            <form method="POST" action="{{ route('projects.projects.cancel', $project) }}"
                                                  onsubmit="return confirm('آیا از لغو این پروژه اطمینان دارید؟')"
                                                  class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="p-2 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-all hover:scale-110 cursor-pointer"
                                                        title="لغو پروژه">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
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
                                    <div class="w-20 h-20 rounded-full bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center mb-4">
                                        <svg class="w-10 h-10 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">
                                        هیچ پروژه‌ای یافت نشد
                                    </h3>
                                    <p class="text-xs text-gray-400 mb-5">
                                        شما هنوز پروژه‌ای ایجاد نکرده‌اید یا فیلترهای جستجو نتیجه‌ای نداشتند.
                                    </p>
                                    @can('projects.create')
                                        <a href="{{ route('projects.projects.create') }}"
                                           class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-md hover:bg-indigo-700 transition-all cursor-pointer">
                                            ایجاد اولین پروژه
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($projects->total() > 0)
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                        <span>
                            نمایش
                            <strong class="text-gray-800 dark:text-gray-200">{{ $faNum($projects->firstItem() ?? 0) }}</strong>
                            تا
                            <strong class="text-gray-800 dark:text-gray-200">{{ $faNum($projects->lastItem() ?? 0) }}</strong>
                            از
                            <strong class="text-gray-800 dark:text-gray-200">{{ $faNum($projects->total()) }}</strong>
                            پروژه
                        </span>

                        <span class="text-gray-300 dark:text-gray-600">•</span>

                        <form method="GET" class="inline-flex items-center gap-1.5 m-0">
                            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                            @if(request('category_id')) <input type="hidden" name="category_id" value="{{ request('category_id') }}"> @endif
                            @if(request('status_id')) <input type="hidden" name="status_id" value="{{ request('status_id') }}"> @endif
                            @if(request('client_id')) <input type="hidden" name="client_id" value="{{ request('client_id') }}"> @endif
                            <span>تعداد در صفحه:</span>
                            <select name="per_page" onchange="this.form.submit()"
                                    class="rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-1 px-2 text-xs font-bold text-gray-700 dark:text-gray-300 focus:ring-1 focus:ring-indigo-500 cursor-pointer">
                                @foreach([10, 15, 25, 50, 100] as $size)
                                    <option value="{{ $size }}" @selected(request('per_page', 15) == $size)>{{ $faNum($size) }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    @if($projects->hasPages())
                        <div>
                            {{ $projects->links() }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
