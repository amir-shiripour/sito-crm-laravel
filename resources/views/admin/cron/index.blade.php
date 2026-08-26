@extends('layouts.admin')

@section('content')
<div x-data="cronManager()" class="py-4 bg-[#f8fafc] dark:bg-[#070a13] min-h-screen transition-colors duration-500 font-iranYekan text-right">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- ۱. بخش هدر اصلی و اکشن‌ها --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-slate-200/60 dark:border-slate-800/60">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                        مرکز زمان‌بندی و پایش پس‌زمینه
                    </span>
                    <span class="text-xs text-slate-400 dark:text-slate-500">|</span>
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">نسخه هسته CRM</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                    مرکز کنترل <span class="text-indigo-600 dark:text-indigo-400">کرون‌جاب‌ها</span>
                </h1>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                {{-- دکمه تست دسترسی‌های لینوکس --}}
                <button @click="openPermissionsModal = true" 
                        class="px-3.5 py-2 bg-white dark:bg-slate-800 border {{ $permissions['all_healthy'] ? 'border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300' : 'border-rose-300 dark:border-rose-500/30 text-rose-600 dark:text-rose-400 bg-rose-50/50' }} text-xs font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/50 shadow-sm transition-all flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full {{ $permissions['all_healthy'] ? 'bg-emerald-500' : 'bg-rose-500 animate-ping' }}"></span>
                    <span>وضعیت لینوکس & www-data</span>
                </button>

                {{-- دکمه پاکسازی لاگ‌ها --}}
                <button @click="openClearLogsModal = true" 
                        class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-400 text-xs font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/50 shadow-sm transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    <span>پاکسازی لاگ‌ها</span>
                </button>

                {{-- دکمه افزودن تسک جدید --}}
                <button @click="openCreateModal = true"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/20 active:scale-95 transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>افزودن تسک</span>
                </button>
            </div>
        </div>

        {{-- ۲. نوار وضعیت پالس سرور و دستور Crontab (Compact Hub) --}}
        <div class="bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-4 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                {{-- وضعیت پالس سرور --}}
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $heartbeat['is_active'] ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20' : 'bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20' }}">
                        @if($heartbeat['is_active'])
                            <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black text-slate-800 dark:text-slate-200">وضعیت کرون سرور:</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-black {{ $heartbeat['is_active'] ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $heartbeat['is_active'] ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                {{ $heartbeat['is_active'] ? 'فعال و برخط (Healthy)' : 'فاقد پالس (Inactive)' }}
                            </span>
                            <span class="text-[11px] text-slate-400 font-bold">({{ $heartbeat['relative_time'] }})</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            آخرین ثبت موفق: <span dir="ltr" class="font-bold text-slate-700 dark:text-slate-300">{{ $heartbeat['jalali_time'] }}</span>
                        </p>
                    </div>
                </div>

                {{-- کادر کپی سریع Crontab --}}
                <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-950 p-1.5 pr-3 rounded-xl border border-slate-200/80 dark:border-slate-800 max-w-xl w-full">
                    <div class="overflow-x-auto text-left w-full custom-scrollbar">
                        <code class="text-[11px] font-mono text-indigo-600 dark:text-indigo-400 select-all whitespace-nowrap block" dir="ltr">{{ $heartbeat['recommended_cron'] }}</code>
                    </div>
                    <button @click="copyToClipboard('{{ $heartbeat['recommended_cron'] }}', 'دستور Crontab کپی شد')" 
                            title="کپی دستور Crontab"
                            class="px-3 py-1.5 bg-white dark:bg-slate-800 hover:bg-indigo-600 hover:text-white text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm transition-all flex items-center gap-1 flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        <span>کپی</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ۳. کارت‌های شاخص عملکرد (KPI Cards) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
            {{-- کل تسک‌ها --}}
            <div class="bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800/80 p-4 rounded-2xl shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 block">کل تسک‌ها</span>
                    <span class="text-xl font-black text-slate-900 dark:text-white mt-0.5 block font-mono">{{ $totalTasks }}</span>
                </div>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 grid place-content-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
            </div>

            {{-- تسک‌های فعال --}}
            <div class="bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800/80 p-4 rounded-2xl shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 block">تسک‌های فعال</span>
                    <span class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5 block font-mono">{{ $activeTasks }}</span>
                </div>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 grid place-content-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>

            {{-- خطاهای ۲۴ ساعت اخیر --}}
            <div class="bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800/80 p-4 rounded-2xl shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 block">خطای ۲۴ ساعت گذشته</span>
                    <span class="text-xl font-black {{ $failed24h > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-700 dark:text-slate-300' }} mt-0.5 block font-mono">{{ $failed24h }}</span>
                </div>
                <div class="w-9 h-9 rounded-xl {{ $failed24h > 0 ? 'bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} grid place-content-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            {{-- میانگین مدت زمان پاسخ --}}
            <div class="bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800/80 p-4 rounded-2xl shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 block">میانگین زمان پاسخ</span>
                    <span class="text-xl font-black text-indigo-600 dark:text-indigo-400 mt-0.5 block font-mono">{{ $avgDuration }} <span class="text-xs font-normal text-slate-400">ms</span></span>
                </div>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 grid place-content-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        {{-- ۴. نوار فیلتر ماژول‌ها و فیلد جستجو --}}
        <div class="bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800/80 p-3 rounded-2xl shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
            {{-- تب‌های فیلتر ماژول --}}
            <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-1 md:pb-0 custom-scrollbar">
                <a href="{{ route('admin.cron.index') }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-black whitespace-nowrap transition-all {{ !request('module') || request('module') == 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    همه ماژول‌ها
                </a>
                @foreach($modules as $mod)
                    <a href="{{ route('admin.cron.index', ['module' => $mod, 'status' => request('status'), 'search' => request('search')]) }}" 
                       class="px-3 py-1.5 rounded-xl text-xs font-black whitespace-nowrap transition-all {{ request('module') == $mod ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                        {{ $mod }}
                    </a>
                @endforeach
            </div>

            {{-- فیلتر وضعیت و کادر جستجو --}}
            <form method="GET" action="{{ route('admin.cron.index') }}" class="flex items-center gap-2 w-full md:w-auto">
                <input type="hidden" name="module" value="{{ request('module') }}">
                
                <select name="status" onchange="this.form.submit()" 
                        class="px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold rounded-xl text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>فقط فعال‌ها</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>فقط غیرفعال‌ها</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>دارای خطا</option>
                </select>

                <div class="relative w-full md:w-56">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="جستجو..." 
                           class="w-full pl-8 pr-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <button type="submit" class="absolute left-2.5 top-2 text-slate-400 hover:text-indigo-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </div>
            </form>
        </div>

        {{-- ۵. لیست تسک‌ها با طراحی مدرن و خلوت (Clean Structured List) --}}
        <div class="space-y-3">
            @forelse($tasks as $task)
                <div class="bg-white/85 dark:bg-slate-900/70 backdrop-blur-xl border {{ $task->is_active ? 'border-slate-200/80 dark:border-slate-800/80 shadow-sm' : 'border-slate-200/40 dark:border-slate-800/40 opacity-75' }} rounded-2xl p-4 transition-all hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-500/30 group"
                     id="task-row-{{ $task->id }}">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        
                        {{-- ستون عنوان، ماژول و دستور --}}
                        <div class="flex items-start gap-3 min-w-[280px]">
                            <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center font-black text-xs 
                                {{ $task->module === 'Booking' ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20' : 
                                   ($task->module === 'Workflows' ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20' : 
                                   ($task->module === 'Sms' ? 'bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-500/20' : 
                                   ($task->module === 'Reminders' ? 'bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-500/20' : 
                                   'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700'))) }}">
                                {{ substr($task->module ?: 'CR', 0, 2) }}
                            </div>

                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-black text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                        {{ $task->name }}
                                    </h3>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                        {{ $task->module }}
                                    </span>
                                </div>
                                
                                <div class="flex items-center gap-1.5">
                                    <code dir="ltr" class="text-[11px] font-mono text-slate-500 dark:text-slate-400 bg-slate-100/80 dark:bg-slate-800/80 px-2 py-0.5 rounded select-all">
                                        {{ $task->command }}
                                    </code>
                                    <button @click="copyToClipboard('{{ $task->command }}', 'دستور کپی شد')" class="text-slate-400 hover:text-indigo-500 transition-colors" title="کپی">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </button>
                                </div>

                                @if($task->description)
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500 line-clamp-1 max-w-lg">
                                        {{ $task->description }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- ستون زمان‌بندی و اطلاعات اجرا --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 lg:gap-8 items-center text-xs">
                            {{-- فرکانس زمان‌بندی --}}
                            <div>
                                <span class="text-[10px] text-slate-400 block mb-0.5">فرکانس اجرا</span>
                                <span class="inline-flex items-center gap-1 font-bold text-slate-700 dark:text-slate-300">
                                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $task->human_expression }}
                                </span>
                            </div>

                            {{-- آخرین اجرا --}}
                            <div>
                                <span class="text-[10px] text-slate-400 block mb-0.5">آخرین اجرا</span>
                                @if($task->last_run_at)
                                    <span class="font-bold text-slate-700 dark:text-slate-300 block">
                                        {{ $task->relative_last_run }}
                                    </span>
                                    <span class="text-[10px] font-mono text-slate-400">
                                        {{ $task->last_duration_ms !== null ? $task->last_duration_ms . ' ms' : '-' }}
                                    </span>
                                @else
                                    <span class="text-slate-400 font-medium">-</span>
                                @endif
                            </div>

                            {{-- وضعیت آخرین اجرا --}}
                            <div id="task-status-{{ $task->id }}">
                                <span class="text-[10px] text-slate-400 block mb-0.5">وضعیت</span>
                                @if(!$task->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-slate-100 dark:bg-slate-800 text-slate-500">
                                        غیرفعال
                                    </span>
                                @elseif($task->last_status === 'success')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        موفق
                                    </span>
                                @elseif($task->last_status === 'failed')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-500/20" title="{{ $task->last_error_message }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        خطا
                                    </span>
                                @elseif($task->last_status === 'running')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-ping"></span>
                                        در حال اجرا
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-slate-100 dark:bg-slate-800 text-slate-500">
                                        آماده
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- ستون سوییچ و دکمه‌های عملیات --}}
                        <div class="flex items-center justify-end gap-3 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100 dark:border-slate-800">
                            {{-- سوییچ فعال/غیرفعال --}}
                            <label class="relative inline-flex items-center cursor-pointer" title="تغییر وضعیت فعال‌سازی">
                                <input type="checkbox" 
                                       {{ $task->is_active ? 'checked' : '' }} 
                                       @change="toggleTask({{ $task->id }}, $event.target)"
                                       class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
                            </label>

                            {{-- دکمه اجرای دستی (Run Now) --}}
                            <button @click="runTaskNow({{ $task->id }}, '{{ addslashes($task->name) }}')"
                                    title="اجرای آنی و نمایش خروجی کنسول"
                                    class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-600 text-indigo-600 dark:text-indigo-400 hover:text-white text-xs font-bold rounded-xl border border-indigo-200/60 dark:border-indigo-500/20 shadow-sm active:scale-95 transition-all flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>اجرای آنی</span>
                            </button>

                            {{-- دکمه مشاهده لاگ‌ها --}}
                            <button @click="openLogsModal({{ $task->id }})"
                                    title="مشاهده تاریخچه لاگ‌ها"
                                    class="p-2 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white rounded-xl transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </button>

                            {{-- دکمه ویرایش --}}
                            <button @click="openEditModal({{ json_encode($task) }})"
                                    title="ویرایش تنظیمات"
                                    class="p-2 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-indigo-600 rounded-xl transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>

                            {{-- دکمه حذف (فقط برای تسک‌های سفارشی) --}}
                            @if(!$task->is_system)
                                <form action="{{ route('admin.cron.destroy', $task) }}" method="POST" class="inline" onsubmit="return confirm('آیا از حذف این تسک اطمینان دارید؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 bg-rose-50 dark:bg-rose-500/10 text-rose-500 hover:bg-rose-500 hover:text-white rounded-xl transition-all" title="حذف تسک">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-12 text-center">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 mx-auto grid place-content-center mb-2.5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">هیچ تسک زمان‌بندی با این فیلتر یافت نشد.</p>
                </div>
            @endforelse
        </div>

    </div>

    {{-- ================================================================= --}}
    {{-- مودال ۱: پنجره خروجی لحظه‌ای اجرای زنده تسک (Live Terminal Modal) --}}
    {{-- ================================================================= --}}
    <div x-show="liveRunModal.open" 
         x-transition.opacity
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-md flex items-center justify-center p-4"
         style="display: none;">
        <div class="bg-slate-900 border border-slate-800 w-full max-w-3xl rounded-2xl overflow-hidden shadow-2xl text-white">
            <div class="p-4 bg-slate-950 border-b border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-rose-500"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                    <span class="text-xs font-mono text-slate-400 mr-2">Terminal Output - <span x-text="liveRunModal.taskName"></span></span>
                </div>
                <button @click="liveRunModal.open = false" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-5">
                {{-- لودینگ در حین اجرا --}}
                <div x-show="liveRunModal.loading" class="py-10 text-center">
                    <div class="inline-block w-8 h-8 border-3 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                    <p class="mt-3 text-xs font-bold text-slate-300">در حال اجرای دستور در پس‌زمینه...</p>
                </div>

                {{-- خروجی تکمیل شده --}}
                <div x-show="!liveRunModal.loading" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span :class="liveRunModal.success ? 'text-emerald-400 bg-emerald-950/60 border-emerald-800' : 'text-rose-400 bg-rose-950/60 border-rose-800'" 
                              class="px-2.5 py-0.5 rounded-lg text-xs font-bold border inline-flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full" :class="liveRunModal.success ? 'bg-emerald-400' : 'bg-rose-400'"></span>
                            <span x-text="liveRunModal.success ? 'اجرا با موفقیت پایان یافت' : 'خطا در اجرا'"></span>
                        </span>
                        <span class="text-xs font-mono text-slate-400">
                            مدت زمان: <span class="text-white font-bold" x-text="liveRunModal.duration_ms + ' ms'"></span>
                        </span>
                    </div>

                    <div class="bg-slate-950 p-4 rounded-xl border border-slate-800/80 font-mono text-xs text-emerald-400 overflow-x-auto max-h-80 custom-scrollbar text-left" dir="ltr">
                        <pre x-text="liveRunModal.output || 'خروجی متنی ثبت نشد.'"></pre>
                    </div>
                </div>
            </div>

            <div class="p-3.5 bg-slate-950/50 border-t border-slate-800 flex justify-end gap-2">
                <button @click="copyToClipboard(liveRunModal.output, 'خروجی کپی شد')" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-xs font-bold rounded-xl transition-all">
                    کپی خروجی
                </button>
                <button @click="liveRunModal.open = false" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all">
                    بستن پنجره
                </button>
            </div>
        </div>
    </div>

    {{-- ================================================================= --}}
    {{-- مودال ۲: تاریخچه و لاگ‌های تسک (Task Logs Archive Modal) --}}
    {{-- ================================================================= --}}
    <div x-show="logsModal.open" 
         x-transition.opacity
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
         style="display: none;">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 w-full max-w-3xl rounded-2xl overflow-hidden shadow-2xl">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white">
                        تاریخچه لاگ‌های تسک: <span class="text-indigo-600 dark:text-indigo-400" x-text="logsModal.task.name"></span>
                    </h3>
                    <p class="text-[11px] text-slate-400 font-mono mt-0.5" dir="ltr" x-text="logsModal.task.command"></p>
                </div>
                <button @click="logsModal.open = false" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-5 max-h-[55vh] overflow-y-auto custom-scrollbar space-y-3">
                <div x-show="logsModal.loading" class="py-10 text-center">
                    <div class="inline-block w-7 h-7 border-3 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                    <p class="mt-2 text-xs font-bold text-slate-400">در حال دریافت لاگ‌ها...</p>
                </div>

                <template x-if="!logsModal.loading && logsModal.logs.length === 0">
                    <div class="py-10 text-center text-slate-400 text-xs font-bold">
                        هیچ لاگی برای این تسک ثبت نشده است.
                    </div>
                </template>

                <template x-for="log in logsModal.logs" :key="log.id">
                    <div class="p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span :class="log.status === 'success' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400'"
                                      class="px-2 py-0.5 rounded-md font-black text-[10px]" x-text="log.status === 'success' ? 'موفق' : 'ناموفق'"></span>
                                <span class="font-bold text-slate-600 dark:text-slate-300" x-text="log.started_at"></span>
                                <span class="text-slate-400 text-[11px]" x-text="'(' + log.relative_time + ')'"></span>
                            </div>
                            <div class="flex items-center gap-2.5 text-[11px] font-mono text-slate-400">
                                <span x-text="'توسط: ' + log.triggered_by"></span>
                                <span x-text="log.duration_ms + ' ms'" class="font-bold text-indigo-500"></span>
                            </div>
                        </div>

                        <div class="bg-slate-900 p-3 rounded-lg font-mono text-[11px] text-slate-300 overflow-x-auto text-left" dir="ltr">
                            <pre x-text="log.output"></pre>
                        </div>
                    </div>
                </template>
            </div>

            <div class="p-3.5 bg-slate-50 dark:bg-slate-950/50 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button @click="logsModal.open = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl transition-all">
                    بستن
                </button>
            </div>
        </div>
    </div>

    {{-- ================================================================= --}}
    {{-- مودال ۳: ایجاد تسک جدید (Create Task Modal) --}}
    {{-- ================================================================= --}}
    <div x-show="openCreateModal" 
         x-transition.opacity
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
         style="display: none;">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 w-full max-w-lg rounded-2xl overflow-hidden shadow-2xl p-6">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-slate-800 mb-5">
                <h3 class="text-base font-black text-slate-900 dark:text-white">افزودن تسک زمان‌بندی جدید</h3>
                <button @click="openCreateModal = false" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-900 dark:hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.cron.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">نام تسک <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="مثلاً: پاکسازی دوره‌ای فایل‌های موقت" 
                           class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">دستور Artisan <span class="text-rose-500">*</span></label>
                        <input type="text" name="command" required placeholder="cache:clear" dir="ltr" 
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">ماژول مربوطه</label>
                        <input type="text" name="module" placeholder="Custom" 
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">فرکانس و دوره اجرا <span class="text-rose-500">*</span></label>
                    <select name="expression" required class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="everyMinute">هر یک دقیقه (everyMinute)</option>
                        <option value="everyFiveMinutes" selected>هر ۵ دقیقه (everyFiveMinutes)</option>
                        <option value="everyTenMinutes">هر ۱۰ دقیقه (everyTenMinutes)</option>
                        <option value="everyFifteenMinutes">هر ۱۵ دقیقه (everyFifteenMinutes)</option>
                        <option value="everyThirtyMinutes">هر ۳۰ دقیقه (everyThirtyMinutes)</option>
                        <option value="hourly">هر یک ساعت (hourly)</option>
                        <option value="everyTwoHours">هر ۲ ساعت (everyTwoHours)</option>
                        <option value="daily">روزانه رأس ساعت ۰۰:۰۰ (daily)</option>
                        <option value="weekly">هفتگی (weekly)</option>
                        <option value="monthly">ماهانه (monthly)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">توضیحات تکمیلی</label>
                    <textarea name="description" rows="2" placeholder="توضیح عملکرد این تسک..." 
                              class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700 dark:text-slate-300">
                        <input type="checkbox" name="prevent_overlap" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        جلوگیری از تداخل اجراهای همزمان (withoutOverlapping)
                    </label>
                </div>

                <div class="pt-3.5 flex justify-end gap-2.5 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openCreateModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl hover:bg-slate-200">
                        انصراف
                    </button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/30">
                        ثبت تسک
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================================================================= --}}
    {{-- مودال ۴: ویرایش تسک (Edit Task Modal) --}}
    {{-- ================================================================= --}}
    <div x-show="editModal.open" 
         x-transition.opacity
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
         style="display: none;">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 w-full max-w-lg rounded-2xl overflow-hidden shadow-2xl p-6">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-slate-800 mb-5">
                <h3 class="text-base font-black text-slate-900 dark:text-white">ویرایش تسک زمان‌بندی</h3>
                <button @click="editModal.open = false" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-900 dark:hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'{{ url('admin/cron') }}/' + editModal.task.id" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">نام تسک</label>
                    <input type="text" name="name" x-model="editModal.task.name" required
                           class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">فرکانس و دوره اجرا</label>
                    <select name="expression" x-model="editModal.task.expression" required 
                            class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="everyMinute">هر یک دقیقه (everyMinute)</option>
                        <option value="everyTwoMinutes">هر ۲ دقیقه (everyTwoMinutes)</option>
                        <option value="everyFiveMinutes">هر ۵ دقیقه (everyFiveMinutes)</option>
                        <option value="everyTenMinutes">هر ۱۰ دقیقه (everyTenMinutes)</option>
                        <option value="everyFifteenMinutes">هر ۱۵ دقیقه (everyFifteenMinutes)</option>
                        <option value="everyThirtyMinutes">هر ۳۰ دقیقه (everyThirtyMinutes)</option>
                        <option value="hourly">هر یک ساعت (hourly)</option>
                        <option value="everyTwoHours">هر ۲ ساعت (everyTwoHours)</option>
                        <option value="daily">روزانه رأس ساعت ۰۰:۰۰ (daily)</option>
                        <option value="weekly">هفتگی (weekly)</option>
                        <option value="monthly">ماهانه (monthly)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">توضیحات</label>
                    <textarea name="description" x-model="editModal.task.description" rows="2" 
                              class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700 dark:text-slate-300">
                        <input type="checkbox" name="prevent_overlap" value="1" :checked="editModal.task.prevent_overlap" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        جلوگیری از تداخل اجراهای همزمان
                    </label>
                </div>

                <div class="pt-3.5 flex justify-end gap-2.5 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="editModal.open = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl hover:bg-slate-200">
                        انصراف
                    </button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/30">
                        ذخیره تغییرات
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================================================================= --}}
    {{-- مودال ۵: پاکسازی لاگ‌ها (Clear Logs Modal) --}}
    {{-- ================================================================= --}}
    <div x-show="openClearLogsModal" 
         x-transition.opacity
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
         style="display: none;">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 w-full max-w-sm rounded-2xl overflow-hidden shadow-2xl p-6">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-slate-800 mb-4">
                <h3 class="text-base font-black text-slate-900 dark:text-white">پاکسازی تاریخچه لاگ‌ها</h3>
                <button @click="openClearLogsModal = false" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-900 dark:hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.cron.clear-logs') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">محدوده زمانی پاکسازی</label>
                    <select name="days" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="0">تمام لاگ‌ها (پاکسازی کامل)</option>
                        <option value="7" selected>لاگ‌های قدیمی‌تر از ۷ روز</option>
                        <option value="30">لاگ‌های قدیمی‌تر از ۳۰ روز</option>
                        <option value="90">لاگ‌های قدیمی‌تر از ۹۰ روز</option>
                    </select>
                </div>

                <p class="text-[11px] text-slate-400 dark:text-slate-500 leading-relaxed">
                    با اجرای این عملیات، تاریخچه خروجی‌های گذشته حذف و حجم دیتابیس بهینه‌سازی می‌شود.
                </p>

                <div class="pt-3.5 flex justify-end gap-2.5 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openClearLogsModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl hover:bg-slate-200">
                        انصراف
                    </button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-md shadow-rose-600/30">
                        پاکسازی لاگ‌ها
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================================================================= --}}
    {{-- مودال ۶: بررسی و عیب‌یابی مجوزهای لینوکس (Linux Permissions Modal) --}}
    {{-- ================================================================= --}}
    <div x-show="openPermissionsModal" 
         x-transition.opacity
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
         style="display: none;">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 w-full max-w-2xl rounded-2xl overflow-hidden shadow-2xl p-6">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white">بررسی سلامت مجوزهای لینوکس</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">کاربر فعلی وب‌سرور: <code class="text-indigo-600 dark:text-indigo-400 font-mono font-bold">{{ $permissions['current_user'] }}</code></p>
                </div>
                <button @click="openPermissionsModal = false" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-900 dark:hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-3.5">
                <div class="grid grid-cols-1 gap-2 max-h-56 overflow-y-auto custom-scrollbar">
                    @foreach($permissions['paths'] as $p)
                        <div class="p-2.5 rounded-xl border border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-950/50 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-mono font-bold text-slate-800 dark:text-slate-200">{{ $p['name'] }}</span>
                                <span class="text-[10px] text-slate-400 block font-mono">{{ $p['path'] }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-mono font-bold text-slate-500 bg-slate-200/60 dark:bg-slate-800 px-2 py-0.5 rounded">{{ $p['permissions'] }}</span>
                                @if($p['is_writable'])
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-black bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        قابل نوشتن
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-black bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        فاقد دسترسی
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- راهنمای اصلاح دسترسی با یک کلیک --}}
                <div class="bg-slate-950 p-3.5 rounded-xl border border-slate-800 text-left" dir="ltr">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Linux Fix Command (SSH)</span>
                        <button @click="copyToClipboard('{{ addslashes($permissions['fix_commands']) }}', 'دستور اصلاح دسترسی کپی شد')" class="text-xs text-indigo-400 hover:text-indigo-300 font-bold">
                            کپی دستورات
                        </button>
                    </div>
                    <code class="text-[11px] font-mono text-emerald-400 block whitespace-pre select-all">{{ $permissions['fix_commands'] }}</code>
                </div>
            </div>

            <div class="pt-3.5 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800 mt-4">
                <button type="button" @click="openPermissionsModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl hover:bg-slate-200">
                    بستن
                </button>
            </div>
        </div>
    </div>

</div>

{{-- اسکریپت آلپاین برای مدیریت تعاملی کامپوننت --}}
<script>
function cronManager() {
    return {
        openCreateModal: false,
        openClearLogsModal: false,
        openPermissionsModal: false,
        
        liveRunModal: {
            open: false,
            loading: false,
            taskName: '',
            success: true,
            duration_ms: 0,
            output: '',
        },

        logsModal: {
            open: false,
            loading: false,
            task: {},
            logs: [],
        },

        editModal: {
            open: false,
            task: {},
        },

        // فعال/غیرفعال‌سازی تسک
        async toggleTask(taskId, checkbox) {
            try {
                const response = await fetch(`{{ url('admin/cron') }}/${taskId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    }
                });
                const data = await response.json();
                if (data.success) {
                    const badgeContainer = document.getElementById(`task-status-${taskId}`);
                    if (badgeContainer) {
                        if (data.is_active) {
                            badgeContainer.innerHTML = `<span class="text-[10px] text-slate-400 block mb-0.5">وضعیت</span><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-500/20"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> فعال </span>`;
                        } else {
                            badgeContainer.innerHTML = `<span class="text-[10px] text-slate-400 block mb-0.5">وضعیت</span><span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-slate-100 dark:bg-slate-800 text-slate-500"> غیرفعال </span>`;
                        }
                    }
                } else {
                    checkbox.checked = !checkbox.checked;
                    alert('خطا در تغییر وضعیت تسک.');
                }
            } catch (e) {
                checkbox.checked = !checkbox.checked;
                alert('خطای ارتباط با سرور.');
            }
        },

        // اجرای آنی تسک (Run Now)
        async runTaskNow(taskId, taskName) {
            this.liveRunModal.open = true;
            this.liveRunModal.loading = true;
            this.liveRunModal.taskName = taskName;
            this.liveRunModal.output = '';

            try {
                const response = await fetch(`{{ url('admin/cron') }}/${taskId}/run`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    }
                });
                const data = await response.json();
                this.liveRunModal.loading = false;
                this.liveRunModal.success = data.success;
                this.liveRunModal.duration_ms = data.duration_ms || 0;
                this.liveRunModal.output = data.output || (data.success ? 'دستور با موفقیت پایان یافت.' : data.error);
            } catch (e) {
                this.liveRunModal.loading = false;
                this.liveRunModal.success = false;
                this.liveRunModal.output = 'خطای ارتباط با سرور در حین اجرای دستور.';
            }
        },

        // مشاهده لاگ‌های تسک
        async openLogsModal(taskId) {
            this.logsModal.open = true;
            this.logsModal.loading = true;
            this.logsModal.logs = [];

            try {
                const response = await fetch(`{{ url('admin/cron') }}/${taskId}/logs`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                this.logsModal.loading = false;
                this.logsModal.task = data.task;
                this.logsModal.logs = data.logs;
            } catch (e) {
                this.logsModal.loading = false;
                alert('خطا در دریافت لاگ‌ها.');
            }
        },

        // باز کردن مودال ویرایش
        openEditModal(task) {
            this.editModal.task = { ...task };
            this.editModal.open = true;
        },

        // کپی به کلیپ‌بورد
        copyToClipboard(text, message) {
            if (!text) return;
            navigator.clipboard.writeText(text).then(() => {
                alert(message || 'در کلیپ‌بورد کپی شد.');
            });
        }
    };
}
</script>
@endsection
