@extends('layouts.user')
@section('title', 'مشاهده الگوی «' . $template->title . '»')

@php
    $faNum = function($str) {
        if (is_null($str)) return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(range(0, 9), $persian, (string)$str);
    };
    $structure = $structure ?? ($template->structure ?? ['phases' => [], 'unphased_tasks' => []]);
    $phases = $structure['phases'] ?? [];
    $unphased = $structure['unphased_tasks'] ?? [];
    $usersMap = $users ?? collect();
@endphp

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6"
         x-data="{
             collapsedPhases: {},
             collapsedTasks: {},
             allPhasesCollapsed: false,
             allTasksCollapsed: false,
             togglePhase(key) { this.collapsedPhases[key] = !this.collapsedPhases[key]; },
             toggleTask(key) { this.collapsedTasks[key] = !this.collapsedTasks[key]; },
             toggleAllPhases() {
                 this.allPhasesCollapsed = !this.allPhasesCollapsed;
                 @foreach($phases as $pIdx => $p)
                     this.collapsedPhases['{{ $pIdx }}'] = this.allPhasesCollapsed;
                 @endforeach
                 this.collapsedPhases['unphased'] = this.allPhasesCollapsed;
             },
             toggleAllTasks() {
                 this.allTasksCollapsed = !this.allTasksCollapsed;
                 @foreach($phases as $pIdx => $phase)
                     @foreach($phase['tasks'] ?? [] as $tIdx => $task)
                         this.collapsedTasks['p{{ $pIdx }}_t{{ $tIdx }}'] = this.allTasksCollapsed;
                     @endforeach
                 @endforeach
                 @foreach($unphased as $tIdx => $task)
                     this.collapsedTasks['unphased_t{{ $tIdx }}'] = this.allTasksCollapsed;
                 @endforeach
             }
         }">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm font-medium" aria-label="Breadcrumb">
            <a href="{{ route('projects.projects.index') }}"
               class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">پروژه‌ها</a>
            <svg class="w-4 h-4 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <a href="{{ route('projects.templates.index') }}"
               class="text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">الگوها</a>
            <svg class="w-4 h-4 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="text-gray-900 dark:text-white font-bold truncate max-w-xs">{{ $template->title }}</span>
        </nav>

        {{-- Top Header Card --}}
        <div
            class="bg-white dark:bg-gray-800/80 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700/50 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6 backdrop-blur-xl">
            <div class="flex items-center gap-4">
                <div
                    class="w-14 h-14 rounded-2xl bg-linear-to-br from-indigo-500 to-indigo-700 text-white flex items-center justify-center shadow-lg shadow-indigo-500/25 shrink-0">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white">{{ $template->title }}</h1>
                        @if($template->category)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border"
                                  style="background: {{ $template->category->color }}15; color: {{ $template->category->color }}; border-color: {{ $template->category->color }}33;">
                                {{ $template->category->name }}
                            </span>
                        @endif
                    </div>
                    @if($template->description)
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">{{ $template->description }}</p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('projects.templates.edit', $template) }}"
                   class="px-4 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white text-xs sm:text-sm font-bold shadow-md shadow-amber-500/20 transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    ویرایش الگو
                </a>
                <a href="{{ route('projects.templates.index') }}"
                   class="px-4 py-2.5 rounded-2xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold transition-all">
                    بازگشت
                </a>
            </div>
        </div>

        {{-- Action Bar --}}
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800/80 p-5 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-sm">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    پیش‌نمایش ساختار فازها، گروه‌ها و کارهای الگو
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">مشاهده ساختار لایه‌ای فازها و کارها همراه با مدیران، مسئولان و
                    تاریخ‌های سررسید.</p>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" @click="toggleAllPhases()"
                        class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700/60 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs transition-all flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                    <span x-text="allPhasesCollapsed ? 'باز کردن همه فازها' : 'جمع کردن همه فازها'"></span>
                </button>

                <button type="button" @click="toggleAllTasks()"
                        class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700/60 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs transition-all flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span x-text="allTasksCollapsed ? 'باز کردن همه گروه‌ها' : 'جمع کردن همه گروه‌ها'"></span>
                </button>
            </div>
        </div>

        {{-- Phases & Groups Container --}}
        <div class="space-y-6">

            {{-- 1. Phases --}}
            @foreach($phases as $pIdx => $phase)
                @php
                    $pColor = $phase['color'] ?? '#6366f1';
                    $pTasks = $phase['tasks'] ?? [];
                @endphp
                <div
                    class="bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-200/80 dark:border-gray-700/60 shadow-xs overflow-hidden transition-all">

                    {{-- Phase Header --}}
                    <div
                        class="p-4 sm:p-5 bg-gray-50/90 dark:bg-gray-900/50 border-b border-gray-200/70 dark:border-gray-700/60 flex items-center justify-between gap-4 cursor-pointer select-none"
                        @click="togglePhase('{{ $pIdx }}')">

                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <button type="button"
                                    class="p-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 transition-transform duration-200 shadow-xs shrink-0"
                                    :class="collapsedPhases['{{ $pIdx }}'] ? 'rotate-180' : ''">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                          d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow-xs"
                                  style="background-color: {{ $pColor }}"></span>

                            <div class="min-w-0 flex-1">
                                <h4 class="font-bold text-gray-900 dark:text-white text-sm sm:text-base truncate">
                                    {{ $phase['name'] ?? 'فاز بدون عنوان' }}
                                </h4>
                                @if(!empty($phase['description']))
                                    <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $phase['description'] }}</p>
                                @endif
                            </div>

                            <span
                                class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/40 shrink-0 mr-1">
                                {{ $faNum(count($pTasks)) }} گروه
                            </span>
                        </div>
                    </div>

                    {{-- Phase Body --}}
                    <div x-show="!collapsedPhases['{{ $pIdx }}']"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="p-4 sm:p-5 space-y-4">

                        @forelse($pTasks as $tIdx => $task)
                            @php
                                $mgrId = $task['manager_id'] ?? $task['assigned_to'] ?? null;
                                $mgrName = $mgrId && isset($usersMap[$mgrId]) ? $usersMap[$mgrId]->name : null;
                                $dueDate = $task['due_date'] ?? null;
                            @endphp
                            <div
                                class="bg-gray-50/60 dark:bg-gray-900/30 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 p-5 space-y-4 shadow-xs">

                                {{-- Group Header --}}
                                <div class="flex flex-col gap-3 select-none cursor-pointer"
                                     @click="toggleTask('p{{ $pIdx }}_t{{ $tIdx }}')">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="flex items-start sm:items-center gap-3 min-w-0 flex-1">
                                            <button type="button"
                                                    class="p-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 transition-transform duration-200 shadow-xs shrink-0"
                                                    :class="collapsedTasks['p{{ $pIdx }}_t{{ $tIdx }}'] ? 'rotate-180' : ''">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>

                                            <span
                                                class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-xs"
                                                style="background: {{ $pColor }}18; border: 1.5px solid {{ $pColor }}33">
                                                <span class="w-3 h-3 rounded-full"
                                                      style="background: {{ $pColor }}"></span>
                                            </span>

                                            <div class="min-w-0 flex-1">
                                                <h5 class="font-bold text-gray-900 dark:text-white text-sm sm:text-base leading-snug truncate">
                                                    {{ $task['title'] ?? 'گروه بدون عنوان' }}
                                                </h5>
                                                @if(!empty($task['description']))
                                                    <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">{{ $task['description'] }}</p>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800/40 shadow-xs">
                                                {{ $faNum(count($task['items'] ?? [])) }} کار
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Group Meta Badges: Manager & Due Date --}}
                                    @if($mgrName || $dueDate)
                                        <div
                                            class="flex items-center gap-3 pt-2 border-t border-gray-200/60 dark:border-gray-700/40 flex-wrap text-xs text-gray-500 dark:text-gray-400">
                                            @if($mgrName)
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-100 dark:border-indigo-800/40">
                                                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor"><path
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                    مدیر گروه: {{ $mgrName }}
                                                </span>
                                            @endif
                                            @if($dueDate)
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-bold border border-amber-100 dark:border-amber-800/40">
                                                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor"><path
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    سررسید: {{ $faNum($dueDate) }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Checklist Items Section --}}
                                @if(!empty($task['items']))
                                    <div x-show="!collapsedTasks['p{{ $pIdx }}_t{{ $tIdx }}']"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 -translate-y-2"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         class="pt-3 border-t border-gray-200/70 dark:border-gray-700/50 space-y-2">

                                        <div class="flex items-center justify-between text-xs pb-1">
                                            <span
                                                class="font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                کارها:
                                            </span>
                                            <span class="text-[11px] text-gray-400 font-bold">
                                                {{ $faNum(count($task['items'])) }} کار تعریف شده
                                            </span>
                                        </div>

                                        <div class="space-y-2">
                                            @foreach($task['items'] as $item)
                                                @php
                                                    $itemAssId = ($item['assigned_to'] ?? '') ? (string)$item['assigned_to'] : $mgrId;
                                                    $itemAssName = $itemAssId && isset($usersMap[$itemAssId]) ? $usersMap[$itemAssId]->name : $mgrName;
                                                    $itemDueDate = !empty($item['due_date']) ? $item['due_date'] : $dueDate;
                                                @endphp
                                                <div
                                                    class="group flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/70 dark:border-gray-700/60 shadow-xs">
                                                    <div class="flex items-center gap-2 min-w-0 flex-1">
                                                        <span
                                                            class="w-4 h-4 rounded border border-gray-300 dark:border-gray-600 flex items-center justify-center shrink-0 bg-gray-50 dark:bg-gray-700/50"></span>
                                                        <span
                                                            class="font-medium text-xs text-gray-800 dark:text-gray-200 truncate">
                                                            {{ $item['title'] ?? '' }}
                                                        </span>
                                                        @if(!empty($item['description']))
                                                            <span class="text-[11px] text-gray-400 truncate max-w-xs">
                                                                ({{ $item['description'] }})
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if($itemAssName || $itemDueDate)
                                                        <div class="flex items-center gap-2 shrink-0 flex-wrap">
                                                            @if($itemAssName)
                                                                <span
                                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-[11px] font-medium">
                                                                    {{ $itemAssName }}
                                                                </span>
                                                            @endif
                                                            @if($itemDueDate)
                                                                <span
                                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 text-[11px] font-bold">
                                                                    {{ $faNum($itemDueDate) }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="py-6 text-center text-gray-400 text-xs">
                                گروهی در این فاز ثبت نشده است.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach

            {{-- 2. Unphased Tasks --}}
            @if(!empty($unphased))
                <div
                    class="bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-200/80 dark:border-gray-700/60 shadow-xs overflow-hidden transition-all">

                    <div
                        class="p-4 sm:p-5 bg-gray-50/90 dark:bg-gray-900/50 border-b border-gray-200/70 dark:border-gray-700/60 flex items-center justify-between gap-4 cursor-pointer select-none"
                        @click="togglePhase('unphased')">

                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <button type="button"
                                    class="p-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 transition-transform duration-200 shadow-xs shrink-0"
                                    :class="collapsedPhases['unphased'] ? 'rotate-180' : ''">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                          d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div class="flex items-center gap-2.5 min-w-0">
                                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                <h4 class="font-bold text-gray-900 dark:text-white text-sm sm:text-base truncate">
                                    گروه‌های عمومی (بدون فاز)
                                </h4>
                            </div>

                            <span
                                class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 shrink-0 mr-1">
                                {{ $faNum(count($unphased)) }} گروه
                            </span>
                        </div>
                    </div>

                    <div x-show="!collapsedPhases['unphased']"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="p-4 sm:p-5 space-y-4">

                        @foreach($unphased as $tIdx => $task)
                            @php
                                $mgrId = $task['manager_id'] ?? $task['assigned_to'] ?? null;
                                $mgrName = $mgrId && isset($usersMap[$mgrId]) ? $usersMap[$mgrId]->name : null;
                                $dueDate = $task['due_date'] ?? null;
                            @endphp
                            <div
                                class="bg-gray-50/60 dark:bg-gray-900/30 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 p-5 space-y-4 shadow-xs">

                                <div class="flex flex-col gap-3 select-none cursor-pointer"
                                     @click="toggleTask('unphased_t{{ $tIdx }}')">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="flex items-start sm:items-center gap-3 min-w-0 flex-1">
                                            <button type="button"
                                                    class="p-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 transition-transform duration-200 shadow-xs shrink-0"
                                                    :class="collapsedTasks['unphased_t{{ $tIdx }}'] ? 'rotate-180' : ''">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>

                                            <span
                                                class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-xs bg-gray-100 dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600">
                                                <span class="w-3 h-3 rounded-full bg-gray-400"></span>
                                            </span>

                                            <div class="min-w-0 flex-1">
                                                <h5 class="font-bold text-gray-900 dark:text-white text-sm sm:text-base leading-snug truncate">
                                                    {{ $task['title'] ?? 'گروه بدون عنوان' }}
                                                </h5>
                                                @if(!empty($task['description']))
                                                    <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">{{ $task['description'] }}</p>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800/40 shadow-xs">
                                                {{ $faNum(count($task['items'] ?? [])) }} کار
                                            </span>
                                        </div>
                                    </div>

                                    @if($mgrName || $dueDate)
                                        <div
                                            class="flex items-center gap-3 pt-2 border-t border-gray-200/60 dark:border-gray-700/40 flex-wrap text-xs text-gray-500 dark:text-gray-400">
                                            @if($mgrName)
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-100 dark:border-indigo-800/40">
                                                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor"><path
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                    مدیر گروه: {{ $mgrName }}
                                                </span>
                                            @endif
                                            @if($dueDate)
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-bold border border-amber-100 dark:border-amber-800/40">
                                                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor"><path
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    سررسید: {{ $faNum($dueDate) }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                @if(!empty($task['items']))
                                    <div x-show="!collapsedTasks['unphased_t{{ $tIdx }}']"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 -translate-y-2"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         class="pt-3 border-t border-gray-200/70 dark:border-gray-700/50 space-y-2">

                                        <div class="flex items-center justify-between text-xs pb-1">
                                            <span
                                                class="font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                کارها:
                                            </span>
                                            <span class="text-[11px] text-gray-400 font-bold">
                                                {{ $faNum(count($task['items'])) }} کار تعریف شده
                                            </span>
                                        </div>

                                        <div class="space-y-2">
                                            @foreach($task['items'] as $item)
                                                @php
                                                    $itemAssId = ($item['assigned_to'] ?? '') ? (string)$item['assigned_to'] : $mgrId;
                                                    $itemAssName = $itemAssId && isset($usersMap[$itemAssId]) ? $usersMap[$itemAssId]->name : $mgrName;
                                                    $itemDueDate = !empty($item['due_date']) ? $item['due_date'] : $dueDate;
                                                @endphp
                                                <div
                                                    class="group flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200/70 dark:border-gray-700/60 shadow-xs">
                                                    <div class="flex items-center gap-2 min-w-0 flex-1">
                                                        <span
                                                            class="w-4 h-4 rounded border border-gray-300 dark:border-gray-600 flex items-center justify-center shrink-0 bg-gray-50 dark:bg-gray-700/50"></span>
                                                        <span
                                                            class="font-medium text-xs text-gray-800 dark:text-gray-200 truncate">
                                                            {{ $item['title'] ?? '' }}
                                                        </span>
                                                        @if(!empty($item['description']))
                                                            <span class="text-[11px] text-gray-400 truncate max-w-xs">
                                                                ({{ $item['description'] }})
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if($itemAssName || $itemDueDate)
                                                        <div class="flex items-center gap-2 shrink-0 flex-wrap">
                                                            @if($itemAssName)
                                                                <span
                                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-[11px] font-medium">
                                                                    {{ $itemAssName }}
                                                                </span>
                                                            @endif
                                                            @if($itemDueDate)
                                                                <span
                                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 text-[11px] font-bold">
                                                                    {{ $faNum($itemDueDate) }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(empty($phases) && empty($unphased))
                <div
                    class="bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-100 dark:border-gray-700/50 p-12 text-center space-y-4 shadow-xs">
                    <p class="text-gray-400 text-xs">این الگو هیچ فاز یا گروهی ندارد.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
