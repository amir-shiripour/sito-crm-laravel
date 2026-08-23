@php use Modules\Projects\App\Http\Models\Project; @endphp
@extends('layouts.user')
@section('title', 'پروژه «' . $project->title . '»')

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
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6"
         x-data="{
             currentTab: (new URLSearchParams(window.location.search).get('tab')) || '{{ session('tab', $tab ?? 'dashboard') }}' || 'dashboard',
             setTab(tab) {
                 this.currentTab = tab;
                 const url = new URL(window.location);
                 url.searchParams.set('tab', tab);
                 window.history.replaceState({}, '', url);
                 window.dispatchEvent(new CustomEvent('tab-changed', { detail: tab }));
             }
         }"
         x-init="
             const urlParams = new URLSearchParams(window.location.search);
             const initialTab = urlParams.get('tab') || '{{ session('tab', $tab ?? 'dashboard') }}' || 'dashboard';
             currentTab = initialTab;
             const url = new URL(window.location);
             url.searchParams.set('tab', initialTab);
             window.history.replaceState({}, '', url);
             $watch('currentTab', val => {
                 const u = new URL(window.location);
                 u.searchParams.set('tab', val);
                 window.history.replaceState({}, '', u);
                 window.dispatchEvent(new CustomEvent('tab-changed', { detail: val }));
             });
             window.addEventListener('popstate', () => {
                 const tab = new URLSearchParams(window.location.search).get('tab');
                 if (tab) { currentTab = tab; }
             });
         ">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm font-medium" aria-label="Breadcrumb">
            <a href="{{ route('projects.projects.index') }}"
               class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">لیست
                پروژه‌ها</a>
            <svg class="w-4 h-4 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                 stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="text-gray-900 dark:text-white font-bold truncate max-w-xs">{{ $project->title }}</span>
        </nav>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div
                class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 dark:bg-emerald-500/10 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-400 text-sm font-bold flex items-center gap-3">
                <span class="bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 p-1.5 rounded-full shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
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
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </span>
                {{ session('error') }}
            </div>
        @endif

        {{-- Project Master Header Card --}}
        <div
            class="bg-white dark:bg-gray-800/60 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden backdrop-blur-xl">
            <div class="p-6 sm:p-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                {{-- Title & Info --}}
                <div class="flex items-start gap-5">
                    <div
                        class="shrink-0 flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-500/30">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold font-mono border bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20">
                                {{ $project->code }}
                            </span>
                            @if($project->category)
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border"
                                    style="background: {{ $project->category->color }}15; color: {{ $project->category->color }}; border-color: {{ $project->category->color }}33;">
                                    <span class="w-2 h-2 rounded-full"
                                          style="background: {{ $project->category->color }}"></span>
                                    {{ $project->category->name }}
                                </span>
                            @endif
                            @if($project->client)
                                <a href="{{ route('user.clients.show', $project->client_id) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-700/60 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 transition-colors">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    {{ $project->client->full_name }}
                                </a>
                            @endif
                        </div>

                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl sm:text-4xl font-black text-gray-900 dark:text-white tracking-tight">
                                {{ $project->title }}
                            </h1>
                        </div>

                        <div
                            class="flex flex-wrap items-center gap-x-6 gap-y-2 mt-3 text-sm text-gray-500 dark:text-gray-400">
                            @if($project->start_date)
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    تاریخ شروع: {{ $formatJalali($project->start_date) }}
                                </span>
                            @endif
                            @if($project->end_date)
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    تاریخ سررسید: {{ $formatJalali($project->end_date) }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Status & Actions --}}
                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    {{-- Status Badge (Automated) --}}
                    @if($project->status)
                        <span id="project-status-badge"
                              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold border shadow-xs transition-all"
                              style="background-color: {{ $project->status->color }}1a; color: {{ $project->status->color }}; border-color: {{ $project->status->color }}33;">
                            <span class="w-2.5 h-2.5 rounded-full"
                                  style="background-color: {{ $project->status->color }}"></span>
                            <span id="project-status-name">{{ $project->status->name }}</span>
                        </span>
                    @endif

                    {{-- Edit Button --}}
                    @can('update', $project)
                        <a href="{{ route('projects.projects.edit', $project) }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:hover:bg-amber-500/20 text-xs font-bold transition-all shadow-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            ویرایش
                        </a>
                    @endcan

                    {{-- Cancel Button --}}
                    @can('cancel', $project)
                        @if(!$project->isCanceled())
                            <form method="POST" action="{{ route('projects.projects.cancel', $project) }}"
                                  onsubmit="return confirm('آیا از لغو این پروژه اطمینان دارید؟ وضعیت پروژه به «لغو شده» تغییر خواهد کرد.')">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 text-xs font-bold transition-all shadow-xs cursor-pointer"
                                        title="لغو پروژه">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    لغو
                                </button>
                            </form>
                        @endif
                    @endcan

                    {{-- Back Button --}}
                    <a href="{{ route('projects.projects.index') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs font-bold transition-all shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        بازگشت
                    </a>
                </div>
            </div>
        </div>

        {{-- Tab Navigation Bar --}}
        <div
            class="flex gap-2 bg-white dark:bg-gray-800/80 p-2 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-x-auto">
            {{-- Tab 1: Dashboard --}}
            <button @click="setTab('dashboard')"
                    :class="currentTab === 'dashboard' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'"
                    class="flex items-center gap-2 px-5 py-3 rounded-2xl text-xs sm:text-sm font-bold transition-all whitespace-nowrap cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                داشبورد و نمودارها
            </button>

            {{-- Tab 2: Tasks --}}
            <button @click="setTab('tasks')"
                    :class="currentTab === 'tasks' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'"
                    class="flex items-center gap-2 px-5 py-3 rounded-2xl text-xs sm:text-sm font-bold transition-all whitespace-nowrap cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                گروه‌ها و کارها
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold"
                      :class="currentTab === 'tasks' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500'">
                    {{ $faNum($project->tasks->count()) }}
                </span>
            </button>

            {{-- Tab 3: Kanban Board --}}
            <button @click="setTab('kanban')"
                    :class="currentTab === 'kanban' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'"
                    class="flex items-center gap-2 px-5 py-3 rounded-2xl text-xs sm:text-sm font-bold transition-all whitespace-nowrap cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                برد کانبان
            </button>

            {{-- Tab 4: Messages --}}
            @can('viewMessages', $project)
                <button @click="setTab('messages')"
                        :class="currentTab === 'messages' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'"
                        class="flex items-center gap-2 px-5 py-3 rounded-2xl text-xs sm:text-sm font-bold transition-all whitespace-nowrap cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    پیام‌ها و گفتگو
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold"
                          :class="currentTab === 'messages' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500'">
                        {{ $faNum($project->messages->count()) }}
                    </span>
                </button>
            @endcan

            {{-- Tab 5: Documents --}}
            @can('viewDocuments', $project)
                <button @click="setTab('documents')"
                        :class="currentTab === 'documents' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'"
                        class="flex items-center gap-2 px-5 py-3 rounded-2xl text-xs sm:text-sm font-bold transition-all whitespace-nowrap cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    اسناد و پیوست‌ها
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold"
                          :class="currentTab === 'documents' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500'">
                        {{ $faNum($project->documents->count()) }}
                    </span>
                </button>
            @endcan

            {{-- Tab 6: Activity Log --}}
            @can('viewActivities', $project)
                <button @click="setTab('activity')"
                        :class="currentTab === 'activity' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'"
                        class="flex items-center gap-2 px-5 py-3 rounded-2xl text-xs sm:text-sm font-bold transition-all whitespace-nowrap cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    تاریخچه
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold"
                          :class="currentTab === 'activity' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500'">
                        {{ $faNum($project->activities->count()) }}
                    </span>
                </button>
            @endcan
        </div>

        {{-- Tab Panels --}}
        <div>
            <div x-show="currentTab === 'dashboard'" x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                @include('projects::projects.partials.tab-dashboard')
            </div>

            <div x-show="currentTab === 'tasks'" x-cloak x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                @include('projects::projects.partials.tab-tasks')
            </div>

            <div x-show="currentTab === 'kanban'" x-cloak x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                @include('projects::projects.partials.tab-kanban')
            </div>

            @can('viewMessages', $project)
                <div x-show="currentTab === 'messages'" x-cloak x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    @include('projects::projects.partials.tab-messages')
                </div>
            @endcan

            @can('viewDocuments', $project)
                <div x-show="currentTab === 'documents'" x-cloak x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    @include('projects::projects.partials.tab-documents')
                </div>
            @endcan

            @can('viewActivities', $project)
                <div x-show="currentTab === 'activity'" x-cloak x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    @include('projects::projects.partials.tab-activity')
                </div>
            @endcan
        </div>

        {{-- Subtask (کار) Modal --}}
        @include('projects::projects.partials.modal-subtask')

        {{-- Apply Template Modal --}}
        @include('projects::projects.partials.modal-apply-template')
    </div>

    @includeIf('partials.jalali-date-picker')
@endsection
