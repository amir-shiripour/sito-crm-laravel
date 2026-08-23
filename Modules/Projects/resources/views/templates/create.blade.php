@extends('layouts.user')

@section('title', 'ایجاد الگوی جدید ساختار پروژه')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-7xl mx-auto"
         x-data="templateBuilder({{ json_encode($initialData) }})"
         x-init="init()">

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-gray-800/80 rounded-3xl p-6 border border-gray-100 dark:border-gray-700/50 shadow-xs">
            <div class="flex items-center gap-3.5">
                <a href="{{ route('projects.templates.index') }}"
                   class="p-2.5 rounded-2xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-500 hover:text-gray-700 dark:text-gray-300 transition-colors shadow-xs">
                    <svg class="w-5 h-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                        ایجاد الگوی ساختار پروژه
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                        تعریف ساختار استاندارد شامل فازها، گروه‌ها، کارها، مدیر گروه، مسئول کار و سررسیدها
                    </p>
                </div>
            </div>

            @if($fromProject)
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50 text-xs font-bold shadow-xs">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>استخراج شده از پروژه: <strong>{{ $fromProject->title }}</strong></span>
                </div>
            @endif
        </div>

        <form action="{{ route('projects.templates.store') }}" method="POST" @submit="prepareSubmit($event)">
            @csrf
            <input type="hidden" name="structure" :value="JSON.stringify(structure)">
            @if($fromProject)
                <input type="hidden" name="source_project_id" value="{{ $fromProject->id }}">
            @endif

            <div class="space-y-6">
                {{-- Template Info Card --}}
                <div
                    class="bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-100 dark:border-gray-700/50 shadow-xs p-6 space-y-5">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        مشخصات کلی الگو
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        {{-- Title --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                عنوان الگو <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" x-model="form.title" required
                                   placeholder="مثال: الگوی استاندارد پروژه‌های سئو و بهینه‌سازی"
                                   class="w-full rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white transition-all">
                            @error('title')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Category --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                دسته‌بندی مرتبط
                            </label>
                            <select name="category_id" x-model="form.category_id"
                                    class="w-full rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white transition-all cursor-pointer">
                                <option value="">بدون دسته‌بندی اختصاصی</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Description --}}
                        <div class="sm:col-span-2 space-y-1.5">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                توضیحات و راهنمای الگو
                            </label>
                            <textarea name="description" x-model="form.description" rows="2"
                                      placeholder="توضیحاتی در مورد کاربرد این الگو، اهداف و مراحل آن بنویسید..."
                                      class="w-full rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white transition-all"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Structure Builder Action Bar --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-gray-800/80 rounded-3xl p-5 border border-gray-100 dark:border-gray-700/50 shadow-xs">
                    <div>
                        <h3 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            ساختار فازها، گروه‌ها و کارها
                        </h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            فازها و گروه‌های کاری را بسازید، مدیر گروه، مسئول کار و سررسیدها را مشخص کنید.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        {{-- Collapse/Expand Buttons --}}
                        <button type="button" @click="toggleAllPhases()"
                                class="px-3 py-2 rounded-xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-bold border border-gray-200 dark:border-gray-600 transition-all flex items-center gap-1.5 shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                            </svg>
                            <span x-text="allPhasesCollapsed ? 'باز کردن همه فازها' : 'جمع کردن فازها'"></span>
                        </button>

                        <button type="button" @click="toggleAllTasks()"
                                class="px-3 py-2 rounded-xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-bold border border-gray-200 dark:border-gray-600 transition-all flex items-center gap-1.5 shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                            </svg>
                            <span x-text="allTasksCollapsed ? 'باز کردن همه گروه‌ها' : 'جمع کردن گروه‌ها'"></span>
                        </button>

                        {{-- Add Phase Button --}}
                        <button type="button" @click="addPhase()"
                                class="px-4 py-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-bold text-xs hover:bg-indigo-100 dark:hover:bg-indigo-900/50 border border-indigo-200 dark:border-indigo-800/40 transition-all flex items-center gap-1.5 shadow-xs">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                            </svg>
                            + ایجاد فاز جدید
                        </button>

                        {{-- Add Unphased Group Button --}}
                        <button type="button" @click="addUnphasedTask()"
                                class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                      d="M12 4v16m8-8H4"/>
                            </svg>
                            افزودن گروه جدید (عمومی)
                        </button>
                    </div>
                </div>

                {{-- PHASES AND GROUPS LIST --}}
                <div class="space-y-6">

                    {{-- 1. SPECIFIC PHASES --}}
                    <template x-for="(phase, pIdx) in structure.phases" :key="'phase-' + pIdx">
                        <div
                            class="bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-200/80 dark:border-gray-700/60 shadow-xs overflow-hidden transition-all"
                            :class="(selectedPhases.includes(pIdx) || selectedPhases.includes(String(pIdx))) ? 'ring-2 ring-indigo-500/50 border-indigo-400 dark:border-indigo-600' : ''">

                            {{-- Phase Header Bar --}}
                            <div
                                class="p-4 sm:p-5 bg-gray-50/90 dark:bg-gray-900/50 border-b border-gray-200/70 dark:border-gray-700/60 flex items-center justify-between gap-4 select-none">

                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div class="flex items-center shrink-0">
                                        <input type="checkbox"
                                               :value="pIdx"
                                               x-model="selectedPhases"
                                               class="w-4 h-4 rounded-md text-indigo-600 focus:ring-2 focus:ring-indigo-500/20 border-gray-300 dark:border-gray-600 dark:bg-gray-700/80 cursor-pointer shadow-2xs transition-all"
                                               title="انتخاب فاز برای عملیات گروهی">
                                    </div>

                                    {{-- Collapse / Expand Chevron Icon --}}
                                    <button type="button" @click="togglePhase(pIdx)"
                                            class="p-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 transition-transform duration-200 shadow-xs shrink-0"
                                            :class="collapsedPhases[pIdx] ? 'rotate-180' : ''">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                  d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    {{-- Phase Color Indicator & Picker --}}
                                    <div class="relative flex items-center justify-center shrink-0" title="تغییر رنگ فاز">
                                        <span class="w-4 h-4 rounded-full shadow-xs cursor-pointer block border border-black/10"
                                              :style="'background-color: ' + (phase.color || '#6366f1')"></span>
                                        <input type="color" x-model="phase.color"
                                               class="absolute inset-0 opacity-0 w-full h-full cursor-pointer">
                                    </div>

                                    {{-- Phase Title & Description --}}
                                    <div class="min-w-0 flex-1 flex flex-col sm:flex-row sm:items-center gap-2">
                                        <input type="text" x-model="phase.name" required
                                               placeholder="عنوان فاز (مثلاً: فاز ۱ - تحلیل و مستندسازی)..."
                                               class="font-bold text-gray-900 dark:text-white text-sm sm:text-base bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-1.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 flex-1">

                                        <input type="text" x-model="phase.description"
                                               placeholder="توضیحات فاز (اختیاری)..."
                                               class="text-xs text-gray-500 dark:text-gray-400 bg-white/70 dark:bg-gray-800/70 rounded-xl border border-gray-100 dark:border-gray-700/50 px-3 py-1.5 focus:ring-1 focus:ring-indigo-500 sm:w-1/3">
                                    </div>

                                    {{-- Groups Count Badge --}}
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/40 shrink-0 mr-1"
                                          x-text="(phase.tasks ? phase.tasks.length : 0) + ' گروه'">
                                    </span>
                                </div>

                                {{-- Phase Header Actions --}}
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" @click="addTaskToPhase(pIdx)"
                                            class="px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-300 text-gray-700 dark:text-gray-300 text-xs font-bold border border-gray-200 dark:border-gray-700 transition-all flex items-center gap-1.5 shadow-xs">
                                        <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                  d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span class="hidden sm:inline">افزودن گروه در این فاز</span>
                                    </button>

                                    <button type="button" @click="removePhase(pIdx)"
                                            class="p-1.5 rounded-xl bg-white dark:bg-gray-800 text-gray-400 hover:text-red-600 border border-gray-200 dark:border-gray-700 transition-all shadow-xs"
                                            title="حذف فاز">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Phase Body: Tasks / Groups List --}}
                            <div x-show="!collapsedPhases[pIdx]"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="p-4 sm:p-5 space-y-4">

                                <template x-if="!phase.tasks || phase.tasks.length === 0">
                                    <div
                                        class="py-8 text-center text-gray-400 text-xs bg-gray-50/50 dark:bg-gray-900/30 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                                        هنوز گروهی در این فاز ثبت نشده است.
                                        <button type="button" @click="addTaskToPhase(pIdx)"
                                                class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline mr-1">
                                            افزودن اولین گروه
                                        </button>
                                    </div>
                                </template>

                                {{-- Group Cards --}}
                                <template x-for="(task, tIdx) in phase.tasks" :key="'p-' + pIdx + '-t-' + tIdx">
                                    <div
                                        class="bg-gray-50/60 dark:bg-gray-900/30 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 p-5 transition-all hover:border-indigo-200 dark:hover:border-indigo-800/60 space-y-4 shadow-xs"
                                        :class="selectedTasks.includes('p' + pIdx + '_t' + tIdx) ? 'ring-2 ring-indigo-500/50 border-indigo-400 dark:border-indigo-600 bg-indigo-50/20 dark:bg-indigo-900/10' : ''">

                                        {{-- Task / Group Top Header --}}
                                        <div class="flex flex-col gap-3">
                                            <div
                                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 select-none">
                                                <div class="flex items-start sm:items-center gap-3 min-w-0 flex-1">
                                                    <div class="flex items-center shrink-0" @click.stop>
                                                        <input type="checkbox"
                                                               :value="'p' + pIdx + '_t' + tIdx"
                                                               x-model="selectedTasks"
                                                               class="w-4 h-4 rounded-md text-indigo-600 focus:ring-2 focus:ring-indigo-500/20 border-gray-300 dark:border-gray-600 dark:bg-gray-700/80 cursor-pointer shadow-2xs transition-all"
                                                               title="انتخاب گروه برای عملیات گروهی">
                                                    </div>

                                                    {{-- Collapse / Expand Chevron Icon --}}
                                                    <button type="button" @click="toggleTask('p' + pIdx + '_t' + tIdx)"
                                                            class="p-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 transition-transform duration-200 shadow-xs shrink-0"
                                                            :class="collapsedTasks['p' + pIdx + '_t' + tIdx] ? 'rotate-180' : ''">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                    </button>

                                                    {{-- Icon Box --}}
                                                    <span
                                                        class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-xs transition-colors duration-200"
                                                        :style="'background: ' + (phase.color || '#6366f1') + '18; border: 1.5px solid ' + (phase.color || '#6366f1') + '33'">
                                                        <span
                                                            class="w-3 h-3 rounded-full transition-colors duration-200"
                                                            :style="'background: ' + (phase.color || '#6366f1')"></span>
                                                    </span>

                                                    {{-- Title & Description Inputs --}}
                                                    <div class="min-w-0 flex-1 space-y-1.5">
                                                        <input type="text" x-model="task.title" required
                                                               placeholder="عنوان گروه (مثلاً: طراحی پروتوتایپ صفحات)..."
                                                               class="w-full font-bold text-gray-900 dark:text-white text-sm sm:text-base rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                                        <input type="text" x-model="task.description"
                                                               placeholder="توضیحات تکمیلی گروه (اختیاری)..."
                                                               class="w-full text-xs text-gray-500 dark:text-gray-400 rounded-lg border border-gray-100 dark:border-gray-700/50 bg-white/70 dark:bg-gray-800/70 px-2.5 py-1 focus:ring-1 focus:ring-indigo-500 transition-all">
                                                    </div>
                                                </div>

                                                {{-- Badges & Actions --}}
                                                <div class="flex items-center gap-2 flex-wrap shrink-0">
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800/40 shadow-xs"
                                                        x-text="(task.items ? task.items.length : 0) + ' کار'">
                                                    </span>

                                                    <button type="button" @click="addItemToPhaseTask(pIdx, tIdx)"
                                                            class="px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 hover:bg-emerald-50 hover:text-emerald-600 text-gray-700 dark:text-gray-300 text-xs font-bold border border-gray-200 dark:border-gray-700 transition-all flex items-center gap-1 shadow-xs">
                                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none"
                                                             viewBox="0 0 24 24" stroke="currentColor"
                                                             stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                        + کار جدید
                                                    </button>

                                                    <button type="button" @click="removeTaskFromPhase(pIdx, tIdx)"
                                                            class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                                            title="حذف گروه">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2"
                                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Group Meta Row: Manager & Due Date --}}
                                            <div
                                                class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2.5 border-t border-gray-200/60 dark:border-gray-700/40">
                                                {{-- Manager Select --}}
                                                <div class="flex items-center gap-2">
                                                    <label
                                                        class="text-xs font-bold text-gray-600 dark:text-gray-400 shrink-0 flex items-center gap-1.5">
                                                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none"
                                                             viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2"
                                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                        </svg>
                                                        مدیر گروه:
                                                    </label>
                                                    <select x-model="task.manager_id"
                                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-medium text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                                                        <option value="">(بدون مدیر گروه)</option>
                                                        @foreach($users as $u)
                                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                {{-- Due Date --}}
                                                <div class="flex items-center gap-2">
                                                    <label
                                                        class="text-xs font-bold text-gray-600 dark:text-gray-400 shrink-0 flex items-center gap-1.5">
                                                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none"
                                                             viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2"
                                                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                        سررسید گروه:
                                                    </label>
                                                    <div class="relative flex items-center w-full">
                                                        <input type="text" data-jdp data-jdp-only-date
                                                               x-model="task.due_date"
                                                               placeholder="۱۴۰۴/۰۱/۱۵"
                                                               autocomplete="off"
                                                               @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                                               @focus="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                                               @change="task.due_date = $event.target.value"
                                                               @input="task.due_date = $event.target.value"
                                                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-medium text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                                                        <button type="button" x-show="task.due_date"
                                                                @click.stop="task.due_date = ''"
                                                                class="absolute left-2.5 p-1 text-gray-400 hover:text-red-500 text-xs"
                                                                title="پاک کردن تاریخ">
                                                            ✕
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Checklist Items Section inside Group --}}
                                        <div x-show="!collapsedTasks['p' + pIdx + '_t' + tIdx]"
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 -translate-y-2"
                                             x-transition:enter-end="opacity-100 translate-y-0"
                                             class="pt-3 border-t border-gray-200/70 dark:border-gray-700/50 space-y-2.5">

                                            <div class="flex items-center justify-between text-xs pb-1">
                                                <span
                                                    class="font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    کارهای این گروه:
                                                </span>
                                                <span class="text-[11px] text-gray-400 font-bold"
                                                      x-text="(task.items ? task.items.length : 0) + ' کار تعریف شده'"></span>
                                            </div>

                                            <div class="space-y-2.5">
                                                <template x-if="!task.items || task.items.length === 0">
                                                    <div
                                                        class="py-3 text-center text-xs text-gray-400 bg-white/40 dark:bg-gray-800/40 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                                                        هنوز کاری برای این گروه اضافه نشده است. روی «+ کار جدید» کلیک
                                                        کنید.
                                                    </div>
                                                </template>

                                                <template x-for="(item, iIdx) in task.items"
                                                          :key="'p-' + pIdx + '-t-' + tIdx + '-i-' + iIdx">
                                                    <div
                                                        class="group flex flex-col md:flex-row md:items-center justify-between gap-2.5 p-2.5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200/70 dark:border-gray-700/60 hover:border-indigo-300 dark:hover:border-indigo-700 transition-all shadow-xs">
                                                        {{-- Work Item Title & Description --}}
                                                        <div class="flex items-center gap-2 min-w-0 flex-1">
                                                            <span
                                                                class="w-4 h-4 rounded-md border border-gray-300 dark:border-gray-600 flex items-center justify-center shrink-0 bg-gray-50 dark:bg-gray-700/50"></span>

                                                            <input type="text" x-model="item.title" required
                                                                   placeholder="عنوان کار..."
                                                                   class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-3 py-1.5 text-xs font-bold text-gray-800 dark:text-gray-200 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">

                                                            <input type="text" x-model="item.description"
                                                                   placeholder="توضیحات کار..."
                                                                   class="w-1/3 sm:w-2/5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-3 py-1.5 text-xs text-gray-500 dark:text-gray-400 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                                        </div>

                                                        {{-- Work Item Assignee, Due Date & Delete Action --}}
                                                        <div
                                                            class="flex items-center gap-2 shrink-0 flex-wrap sm:flex-nowrap">
                                                            {{-- Assignee (مسئول کار) --}}
                                                            <select x-model="item.assigned_to"
                                                                    title="مسئول کار"
                                                                    class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/60 px-2.5 py-1.5 text-[11px] font-medium text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                                                                <option value="">(بدون مسئول کار)</option>
                                                                @foreach($users as $u)
                                                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                                @endforeach
                                                            </select>

                                                            {{-- Due Date (سررسید کار) --}}
                                                            <div class="relative flex items-center">
                                                                <input type="text" data-jdp data-jdp-only-date
                                                                       x-model="item.due_date"
                                                                       placeholder="سررسید کار"
                                                                       title="تاریخ سررسید کار"
                                                                       autocomplete="off"
                                                                       @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                                                       @focus="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                                                       @change="item.due_date = $event.target.value"
                                                                       @input="item.due_date = $event.target.value"
                                                                       class="w-28 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/60 px-2.5 py-1.5 text-[11px] font-medium text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                                                                <button type="button" x-show="item.due_date"
                                                                        @click.stop="item.due_date = ''"
                                                                        class="absolute left-2 p-0.5 text-gray-400 hover:text-red-500 text-[10px]"
                                                                        title="پاک کردن تاریخ">
                                                                    ✕
                                                                </button>
                                                            </div>

                                                            {{-- Delete Item Button --}}
                                                            <button type="button"
                                                                    @click="removeItemFromPhaseTask(pIdx, tIdx, iIdx)"
                                                                    class="p-1.5 rounded-xl text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                                                    title="حذف کار">
                                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                                     stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                          stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- 2. UNPHASED / GENERAL GROUPS SECTION --}}
                    <div
                        class="bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-200/80 dark:border-gray-700/60 shadow-xs overflow-hidden transition-all"
                        x-show="structure.unphased_tasks && structure.unphased_tasks.length > 0">

                        {{-- General Header Bar --}}
                        <div
                            class="p-4 sm:p-5 bg-gray-50/90 dark:bg-gray-900/50 border-b border-gray-200/70 dark:border-gray-700/60 flex items-center justify-between gap-4 select-none">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <button type="button" @click="togglePhase('unphased')"
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
                                    class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 shrink-0 mr-1"
                                    x-text="(structure.unphased_tasks ? structure.unphased_tasks.length : 0) + ' گروه'">
                                </span>
                            </div>

                            <button type="button" @click="addUnphasedTask()"
                                    class="px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 hover:bg-indigo-50 hover:text-indigo-600 text-gray-700 dark:text-gray-300 text-xs font-bold border border-gray-200 dark:border-gray-700 transition-all flex items-center gap-1.5 shadow-xs">
                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                          d="M12 4v16m8-8H4"/>
                                </svg>
                                + افزودن گروه عمومی
                            </button>
                        </div>

                        {{-- General Tasks Body --}}
                        <div x-show="!collapsedPhases['unphased']"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="p-4 sm:p-5 space-y-4">

                            <template x-for="(task, tIdx) in structure.unphased_tasks" :key="'unphased-t-' + tIdx">
                                <div
                                    class="bg-gray-50/60 dark:bg-gray-900/30 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 p-5 transition-all hover:border-indigo-200 dark:hover:border-indigo-800/60 space-y-4 shadow-xs"
                                    :class="selectedTasks.includes('unphased_' + tIdx) ? 'ring-2 ring-indigo-500/50 border-indigo-400 dark:border-indigo-600 bg-indigo-50/20 dark:bg-indigo-900/10' : ''">

                                    {{-- Group Top Header --}}
                                    <div class="flex flex-col gap-3">
                                        <div
                                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 select-none">
                                            <div class="flex items-start sm:items-center gap-3 min-w-0 flex-1">
                                                <div class="flex items-center shrink-0" @click.stop>
                                                    <input type="checkbox"
                                                           :value="'unphased_' + tIdx"
                                                           x-model="selectedTasks"
                                                           class="w-4 h-4 rounded-md text-indigo-600 focus:ring-2 focus:ring-indigo-500/20 border-gray-300 dark:border-gray-600 dark:bg-gray-700/80 cursor-pointer shadow-2xs transition-all"
                                                           title="انتخاب گروه برای عملیات گروهی">
                                                </div>

                                                <button type="button" @click="toggleTask('unphased_t' + tIdx)"
                                                        class="p-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 transition-transform duration-200 shadow-xs shrink-0"
                                                        :class="collapsedTasks['unphased_t' + tIdx] ? 'rotate-180' : ''">
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

                                                <div class="min-w-0 flex-1 space-y-1.5">
                                                    <input type="text" x-model="task.title" required
                                                           placeholder="عنوان گروه وظیفه عمومی..."
                                                           class="w-full font-bold text-gray-900 dark:text-white text-sm sm:text-base rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                                    <input type="text" x-model="task.description"
                                                           placeholder="توضیحات تکمیلی گروه (اختیاری)..."
                                                           class="w-full text-xs text-gray-500 dark:text-gray-400 rounded-lg border border-gray-100 dark:border-gray-700/50 bg-white/70 dark:bg-gray-800/70 px-2.5 py-1 focus:ring-1 focus:ring-indigo-500 transition-all">
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2 flex-wrap shrink-0">
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800/40 shadow-xs"
                                                    x-text="(task.items ? task.items.length : 0) + ' کار'">
                                                </span>

                                                <button type="button" @click="addItemToUnphasedTask(tIdx)"
                                                        class="px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 hover:bg-emerald-50 hover:text-emerald-600 text-gray-700 dark:text-gray-300 text-xs font-bold border border-gray-200 dark:border-gray-700 transition-all flex items-center gap-1 shadow-xs">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                    + کار جدید
                                                </button>

                                                <button type="button" @click="removeUnphasedTask(tIdx)"
                                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                                        title="حذف گروه">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Group Meta Row: Manager & Due Date --}}
                                        <div
                                            class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2.5 border-t border-gray-200/60 dark:border-gray-700/40">
                                            {{-- Manager Select --}}
                                            <div class="flex items-center gap-2">
                                                <label
                                                    class="text-xs font-bold text-gray-600 dark:text-gray-400 shrink-0 flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                    مدیر گروه:
                                                </label>
                                                <select x-model="task.manager_id"
                                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-medium text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                                                    <option value="">(بدون مدیر گروه)</option>
                                                    @foreach($users as $u)
                                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Due Date --}}
                                            <div class="flex items-center gap-2">
                                                <label
                                                    class="text-xs font-bold text-gray-600 dark:text-gray-400 shrink-0 flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    سررسید گروه:
                                                </label>
                                                <div class="relative flex items-center w-full">
                                                    <input type="text" data-jdp data-jdp-only-date
                                                           x-model="task.due_date"
                                                           placeholder="۱۴۰۴/۰۱/۱۵"
                                                           autocomplete="off"
                                                           @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                                           @focus="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                                           @change="task.due_date = $event.target.value"
                                                           @input="task.due_date = $event.target.value"
                                                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-medium text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                                                    <button type="button" x-show="task.due_date"
                                                            @click.stop="task.due_date = ''"
                                                            class="absolute left-2.5 p-1 text-gray-400 hover:text-red-500 text-xs"
                                                            title="پاک کردن تاریخ">
                                                        ✕
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Checklist Items Section --}}
                                    <div x-show="!collapsedTasks['unphased_t' + tIdx]"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 -translate-y-2"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         class="pt-3 border-t border-gray-200/70 dark:border-gray-700/50 space-y-2.5">

                                        <div class="flex items-center justify-between text-xs pb-1">
                                            <span
                                                class="font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                کارهای این گروه:
                                            </span>
                                            <span class="text-[11px] text-gray-400 font-bold"
                                                  x-text="(task.items ? task.items.length : 0) + ' کار تعریف شده'"></span>
                                        </div>

                                        <div class="space-y-2.5">
                                            <template x-if="!task.items || task.items.length === 0">
                                                <div class="py-3 text-center text-xs text-gray-400 bg-white/40 dark:bg-gray-800/40 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                                                    هنوز کاری برای این گروه اضافه نشده است. روی «+ کار جدید» کلیک کنید.
                                                </div>
                                            </template>

                                            <template x-for="(item, iIdx) in task.items" :key="'unphased-t-' + tIdx + '-i-' + iIdx">
                                                <div class="group flex flex-col md:flex-row md:items-center justify-between gap-2.5 p-2.5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200/70 dark:border-gray-700/60 hover:border-indigo-300 dark:hover:border-indigo-700 transition-all shadow-xs">
                                                    <div class="flex items-center gap-2 min-w-0 flex-1">
                                                        <span class="w-4 h-4 rounded-md border border-gray-300 dark:border-gray-600 flex items-center justify-center shrink-0 bg-gray-50 dark:bg-gray-700/50"></span>

                                                        <input type="text" x-model="item.title" required
                                                               placeholder="عنوان کار..."
                                                               class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-3 py-1.5 text-xs font-bold text-gray-800 dark:text-gray-200 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">

                                                        <input type="text" x-model="item.description"
                                                               placeholder="توضیحات کار..."
                                                               class="w-1/3 sm:w-2/5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-3 py-1.5 text-xs text-gray-500 dark:text-gray-400 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                                                    </div>

                                                    <div
                                                        class="flex items-center gap-2 shrink-0 flex-wrap sm:flex-nowrap">
                                                        <select x-model="item.assigned_to"
                                                                title="مسئول کار"
                                                                class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/60 px-2.5 py-1.5 text-[11px] font-medium text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                                                            <option value="">(بدون مسئول کار)</option>
                                                            @foreach($users as $u)
                                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="relative flex items-center">
                                                            <input type="text" data-jdp data-jdp-only-date
                                                                   x-model="item.due_date"
                                                                   placeholder="سررسید کار"
                                                                   title="تاریخ سررسید کار"
                                                                   autocomplete="off"
                                                                   @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                                                   @focus="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                                                   @change="item.due_date = $event.target.value"
                                                                   @input="item.due_date = $event.target.value"
                                                                   class="w-28 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/60 px-2.5 py-1.5 text-[11px] font-medium text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                                                            <button type="button" x-show="item.due_date"
                                                                    @click.stop="item.due_date = ''"
                                                                    class="absolute left-2 p-0.5 text-gray-400 hover:text-red-500 text-[10px]"
                                                                    title="پاک کردن تاریخ">
                                                                ✕
                                                            </button>
                                                        </div>
                                                        <button type="button"
                                                                @click="removeItemFromUnphasedTask(tIdx, iIdx)"
                                                                class="p-1.5 rounded-xl text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                                                title="حذف کار">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                                 stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Empty State --}}
                    <div x-show="structure.phases.length === 0 && structure.unphased_tasks.length === 0"
                         class="bg-white dark:bg-gray-800/80 rounded-3xl border border-gray-100 dark:border-gray-700/50 p-12 text-center space-y-4 shadow-xs">
                        <div
                            class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto shadow-xs">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <h4 class="font-bold text-gray-900 dark:text-white text-base">این الگو هنوز فاز یا گروهی
                            ندارد</h4>
                        <p class="text-xs text-gray-400 max-w-md mx-auto leading-relaxed">
                            می‌توانید با کلیک بر روی «+ ایجاد فاز جدید» یا «افزودن گروه جدید (عمومی)» ساختار الگو را
                            طراحی کنید.
                        </p>
                        <div class="flex items-center justify-center gap-3 pt-2">
                            <button type="button" @click="addPhase()"
                                    class="px-4 py-2 rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 font-bold text-xs hover:bg-indigo-100 transition-all">
                                + ایجاد اولین فاز
                            </button>
                            <button type="button" @click="addUnphasedTask()"
                                    class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-700 transition-all">
                                + تعریف اولین گروه
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Form Actions Footer --}}
                <div
                    class="flex items-center justify-between gap-4 bg-white dark:bg-gray-800/80 rounded-3xl p-5 border border-gray-100 dark:border-gray-700/50 shadow-sm backdrop-blur-xl">
                    <a href="{{ route('projects.templates.index') }}"
                       class="px-5 py-2.5 rounded-2xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-bold transition-all">
                        انصراف و بازگشت
                    </a>

                    <button type="submit"
                            class="px-8 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-indigo-500/25 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>ذخیره الگوی جدید</span>
                    </button>
                </div>
            </div>
        </form>

        {{-- Floating Bulk Actions Bar --}}
        <div x-show="totalSelectedCount > 0"
             x-cloak
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 scale-95"
             class="fixed bottom-6 inset-x-0 mx-auto w-full max-w-lg z-50 px-4 pointer-events-auto">
            <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl border border-gray-200/90 dark:border-gray-700/80 p-3.5 sm:p-4 rounded-2xl sm:rounded-3xl shadow-2xl shadow-gray-900/10 dark:shadow-black/50 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl sm:rounded-2xl bg-indigo-50 dark:bg-indigo-900/50 border border-indigo-100 dark:border-indigo-800/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-xs sm:text-sm shrink-0 shadow-2xs">
                        <span x-text="totalSelectedCount"></span>
                    </span>
                    <div class="min-w-0">
                        <p class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white truncate">
                            <span x-show="selectedPhases.length > 0" x-text="selectedPhases.length + ' فاز '"></span>
                            <span x-show="selectedPhases.length > 0 && selectedTasks.length > 0">و </span>
                            <span x-show="selectedTasks.length > 0" x-text="selectedTasks.length + ' گروه '"></span>
                            <span>انتخاب شده</span>
                        </p>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500">عملیات حذف گروهی روی ساختار الگو</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" @click="deselectAll()"
                            class="px-3 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700/70 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold transition-all shadow-2xs">
                        لغو
                    </button>
                    <button type="button" @click="deleteSelected()"
                            class="px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/50 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/60 text-xs font-bold transition-all shadow-2xs flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>حذف موارد</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function templateBuilder(initial) {
                return {
                    form: {
                        title: initial?.title || '',
                        description: initial?.description || '',
                        category_id: initial?.category_id || '',
                    },
                    structure: {
                        phases: initial?.structure?.phases || [],
                        unphased_tasks: initial?.structure?.unphased_tasks || [],
                    },
                    collapsedPhases: {},
                    collapsedTasks: {},
                    allPhasesCollapsed: false,
                    allTasksCollapsed: false,
                    selectedPhases: [],
                    selectedTasks: [],
                    get totalSelectedCount() {
                        return this.selectedPhases.length + this.selectedTasks.length;
                    },
                    deselectAll() {
                        this.selectedPhases = [];
                        this.selectedTasks = [];
                    },
                    deleteSelected() {
                        const phaseCount = this.selectedPhases.length;
                        const taskCount = this.selectedTasks.length;
                        if (phaseCount === 0 && taskCount === 0) return;

                        let msg = `آیا از حذف ${phaseCount > 0 ? phaseCount + ' فاز ' : ''}${phaseCount > 0 && taskCount > 0 ? 'و ' : ''}${taskCount > 0 ? taskCount + ' گروه ' : ''}انتخاب شده اطمینان دارید؟`;
                        if (!confirm(msg)) return;

                        const selectedPhasesSet = new Set(this.selectedPhases.map(Number));

                        // 1. Delete unphased tasks (descending index)
                        const unphasedIndices = this.selectedTasks
                            .filter(t => typeof t === 'string' && t.startsWith('unphased_'))
                            .map(t => parseInt(t.replace('unphased_', ''), 10))
                            .sort((a, b) => b - a);

                        unphasedIndices.forEach(idx => {
                            if (this.structure.unphased_tasks && this.structure.unphased_tasks[idx]) {
                                this.structure.unphased_tasks.splice(idx, 1);
                            }
                        });

                        // 2. Delete phased tasks for phases NOT being deleted
                        const phasedTasksMap = {};
                        this.selectedTasks
                            .filter(t => typeof t === 'string' && t.startsWith('p'))
                            .forEach(str => {
                                const match = str.match(/^p(\d+)_t(\d+)$/);
                                if (match) {
                                    const pIdx = parseInt(match[1], 10);
                                    const tIdx = parseInt(match[2], 10);
                                    if (!selectedPhasesSet.has(pIdx)) {
                                        if (!phasedTasksMap[pIdx]) phasedTasksMap[pIdx] = [];
                                        phasedTasksMap[pIdx].push(tIdx);
                                    }
                                }
                            });

                        Object.keys(phasedTasksMap).forEach(pIdx => {
                            const tIndices = phasedTasksMap[pIdx].sort((a, b) => b - a);
                            tIndices.forEach(tIdx => {
                                if (this.structure.phases[pIdx]?.tasks && this.structure.phases[pIdx].tasks[tIdx]) {
                                    this.structure.phases[pIdx].tasks.splice(tIdx, 1);
                                }
                            });
                        });

                        // 3. Delete selected phases (descending index)
                        const phaseIndices = [...this.selectedPhases].map(Number).sort((a, b) => b - a);
                        phaseIndices.forEach(pIdx => {
                            if (this.structure.phases && this.structure.phases[pIdx]) {
                                this.structure.phases.splice(pIdx, 1);
                            }
                        });

                        this.selectedPhases = [];
                        this.selectedTasks = [];
                    },

                    init() {
                        this.$nextTick(() => {
                            if (window.jalaliDatepicker) {
                                window.jalaliDatepicker.updateOptions({date: true, time: false});
                                window.jalaliDatepicker.startWatch({
                                    selector: '[data-jdp], [data-jdp-only-date]',
                                    date: true,
                                    time: false
                                });
                            }
                        });
                    },

                    togglePhase(key) {
                        this.collapsedPhases[key] = !this.collapsedPhases[key];
                    },

                    toggleTask(key) {
                        this.collapsedTasks[key] = !this.collapsedTasks[key];
                    },

                    toggleAllPhases() {
                        this.allPhasesCollapsed = !this.allPhasesCollapsed;
                        this.structure.phases.forEach((_, idx) => {
                            this.collapsedPhases[idx] = this.allPhasesCollapsed;
                        });
                        this.collapsedPhases['unphased'] = this.allPhasesCollapsed;
                    },

                    toggleAllTasks() {
                        this.allTasksCollapsed = !this.allTasksCollapsed;
                        this.structure.phases.forEach((phase, pIdx) => {
                            (phase.tasks || []).forEach((_, tIdx) => {
                                this.collapsedTasks['p' + pIdx + '_t' + tIdx] = this.allTasksCollapsed;
                            });
                        });
                        (this.structure.unphased_tasks || []).forEach((_, tIdx) => {
                            this.collapsedTasks['unphased_t' + tIdx] = this.allTasksCollapsed;
                        });
                    },

                    addPhase() {
                        this.structure.phases.push({
                            name: '',
                            color: '#6366f1',
                            description: '',
                            tasks: [{
                                title: '',
                                description: '',
                                manager_id: '',
                                due_date: '',
                                items: []
                            }]
                        });
                        this.$nextTick(() => {
                            if (window.jalaliDatepicker) {
                                window.jalaliDatepicker.updateOptions({date: true, time: false});
                                window.jalaliDatepicker.startWatch({
                                    selector: '[data-jdp], [data-jdp-only-date]',
                                    date: true,
                                    time: false
                                });
                            }
                        });
                    },

                    removePhase(pIdx) {
                        if (confirm('آیا از حذف این فاز و تمام گروه‌های داخل آن اطمینان دارید؟')) {
                            this.structure.phases.splice(pIdx, 1);
                        }
                    },

                    addTaskToPhase(pIdx) {
                        if (!this.structure.phases[pIdx].tasks) {
                            this.structure.phases[pIdx].tasks = [];
                        }
                this.structure.phases[pIdx].tasks.push({
                    title: '',
                    description: '',
                    manager_id: '',
                    due_date: '',
                    items: []
                });
                this.collapsedPhases[pIdx] = false;
                this.$nextTick(() => {
                    if (window.jalaliDatepicker) {
                        window.jalaliDatepicker.updateOptions({date: true, time: false});
                        window.jalaliDatepicker.startWatch({
                            selector: '[data-jdp], [data-jdp-only-date]',
                            date: true,
                            time: false
                        });
                    }
                });
            },

            removeTaskFromPhase(pIdx, tIdx) {
                this.structure.phases[pIdx].tasks.splice(tIdx, 1);
            },

            addItemToPhaseTask(pIdx, tIdx) {
                if (!this.structure.phases[pIdx].tasks[tIdx].items) {
                    this.structure.phases[pIdx].tasks[tIdx].items = [];
                }
                const currentTask = this.structure.phases[pIdx].tasks[tIdx];
                this.structure.phases[pIdx].tasks[tIdx].items.push({
                    title: '',
                    description: '',
                    assigned_to: currentTask.manager_id || '',
                    due_date: currentTask.due_date || ''
                });
                this.collapsedTasks['p' + pIdx + '_t' + tIdx] = false;
                this.$nextTick(() => {
                    if (window.jalaliDatepicker) {
                        window.jalaliDatepicker.updateOptions({date: true, time: false});
                        window.jalaliDatepicker.startWatch({
                            selector: '[data-jdp], [data-jdp-only-date]',
                            date: true,
                            time: false
                        });
                    }
                });
            },

            removeItemFromPhaseTask(pIdx, tIdx, iIdx) {
                this.structure.phases[pIdx].tasks[tIdx].items.splice(iIdx, 1);
            },

            addUnphasedTask() {
                if (!this.structure.unphased_tasks) {
                    this.structure.unphased_tasks = [];
                }
                this.structure.unphased_tasks.push({
                    title: '',
                    description: '',
                    manager_id: '',
                    due_date: '',
                    items: []
                });
                this.collapsedPhases['unphased'] = false;
                this.$nextTick(() => {
                    if (window.jalaliDatepicker) {
                        window.jalaliDatepicker.updateOptions({date: true, time: false});
                        window.jalaliDatepicker.startWatch({
                            selector: '[data-jdp], [data-jdp-only-date]',
                            date: true,
                            time: false
                        });
                    }
                });
            },

            removeUnphasedTask(tIdx) {
                this.structure.unphased_tasks.splice(tIdx, 1);
            },

            addItemToUnphasedTask(tIdx) {
                if (!this.structure.unphased_tasks[tIdx].items) {
                    this.structure.unphased_tasks[tIdx].items = [];
                }
                const currentTask = this.structure.unphased_tasks[tIdx];
                this.structure.unphased_tasks[tIdx].items.push({
                    title: '',
                    description: '',
                    assigned_to: currentTask.manager_id || '',
                    due_date: currentTask.due_date || ''
                });
                this.collapsedTasks['unphased_t' + tIdx] = false;
                this.$nextTick(() => {
                    if (window.jalaliDatepicker) {
                        window.jalaliDatepicker.updateOptions({date: true, time: false});
                        window.jalaliDatepicker.startWatch({
                            selector: '[data-jdp], [data-jdp-only-date]',
                            date: true,
                            time: false
                        });
                    }
                });
            },

            removeItemFromUnphasedTask(tIdx, iIdx) {
                this.structure.unphased_tasks[tIdx].items.splice(iIdx, 1);
            },

            prepareSubmit(e) {
                if (!this.form.title || !this.form.title.trim()) {
                    e.preventDefault();
                    alert('لطفاً عنوان الگو را وارد کنید.');
                    return;
                }
                const hiddenStructureInput = e.target.querySelector('input[name="structure"]');
                if (hiddenStructureInput) {
                    hiddenStructureInput.value = JSON.stringify(this.structure);
                }
            }
        };
    }
    </script>
    @endpush

    @includeIf('partials.jalali-date-picker')
@endsection
