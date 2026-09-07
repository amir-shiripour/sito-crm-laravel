@extends('layouts.user')

@section('title', 'ایجاد الگوی جدید ساختار پروژه')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8 space-y-8 max-w-7xl mx-auto"
         x-data="templateBuilder({{ json_encode($initialData) }})"
         x-init="init()">

        {{-- Page Header & Breadcrumbs --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-gray-800/90 rounded-3xl p-6 border border-gray-100 dark:border-gray-700/60 shadow-xs backdrop-blur-xl">
            <div class="flex items-center gap-4">
                <a href="{{ route('projects.templates.index') }}"
                   class="p-2.5 rounded-2xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/60 dark:hover:bg-gray-700 text-gray-500 hover:text-gray-700 dark:text-gray-300 transition-colors shadow-xs"
                   title="بازگشت به لیست الگوها">
                    <svg class="w-5 h-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold text-gray-400 mb-1">
                        <a href="{{ route('projects.projects.index') }}" class="hover:text-indigo-600 transition-colors">پروژه‌ها</a>
                        <span>/</span>
                        <a href="{{ route('projects.templates.index') }}" class="hover:text-indigo-600 transition-colors">الگوها</a>
                        <span>/</span>
                        <span class="text-indigo-600 dark:text-indigo-400">ایجاد الگوی جدید</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-purple-600 text-white shadow-md shadow-indigo-500/25 shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="12" y1="18" x2="12" y2="12"></line>
                                <line x1="9" y1="15" x2="15" y2="15"></line>
                            </svg>
                        </span>
                        <span>تعریف الگوی ساختار پروژه</span>
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                @if($fromProject)
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50 text-xs font-bold shadow-xs">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>استخراج‌شده از: <strong>{{ $fromProject->title }}</strong></span>
                    </div>
                @endif

                <a href="{{ route('projects.templates.index') }}"
                   class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-2xl text-xs font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all flex items-center gap-1.5">
                    انصراف
                </a>

                <button type="button" @click="submitForm()"
                        class="px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all flex items-center gap-2 active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>ذخیره الگو</span>
                </button>
            </div>
        </div>

        {{-- Main Form --}}
        <form id="template-create-form" action="{{ route('projects.templates.store') }}" method="POST" @submit="prepareSubmit($event)" class="space-y-8">
            @csrf
            <input type="hidden" name="structure" :value="JSON.stringify(structure)">
            @if($fromProject)
                <input type="hidden" name="source_project_id" value="{{ $fromProject->id }}">
            @endif

            <div class="bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-100 dark:border-gray-700/60 shadow-xs p-6 sm:p-7 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100 dark:border-gray-700/60">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-sm">
                            ۱
                        </span>
                        <div>
                            <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                                مشخصات پایه و شناسنامه الگو
                            </h2>
                            <p class="text-xs text-gray-400 mt-0.5">نام، دسته‌بندی و اهداف کلی این الگو را مشخص کنید.</p>
                        </div>
                    </div>

                    {{-- Live Summary Chips --}}
                    <div class="flex items-center gap-2 flex-wrap text-xs">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 font-bold border border-purple-100 dark:border-purple-800/40 shadow-xs">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            <span x-text="structure.phases ? structure.phases.length : 0"></span> فاز
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 font-bold border border-indigo-100 dark:border-indigo-800/40 shadow-xs">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            <span x-text="totalTasksCount"></span> گروه
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 font-bold border border-emerald-100 dark:border-emerald-800/40 shadow-xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span x-text="totalItemsCount"></span> کار
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Title --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                            عنوان الگو <span class="text-rose-500 font-black">*</span>
                        </label>
                        <input type="text" name="title" x-model="form.title" required
                               placeholder="مثال: الگوی استاندارد طراحی وب‌سایت شرکتی..."
                               class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/50 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white transition-all">
                        @error('title')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Category --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                            دسته‌بندی مرتبط
                        </label>
                        <select name="category_id" x-model="form.category_id"
                                class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/50 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white transition-all cursor-pointer">
                            <option value="" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">بدون دسته‌بندی اختصاصی (عمومی)</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Description --}}
                    <div class="sm:col-span-2 space-y-1.5">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                            توضیحات و راهنمای استفاده از الگو
                        </label>
                        <textarea name="description" x-model="form.description" rows="2"
                                  placeholder="اهداف این الگو، مراحل کلیدی و نکات مهم اجرایی را در اینجا یادداشت کنید..."
                                  class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/50 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:text-white transition-all"></textarea>
                    </div>
                </div>
            </div>
            <div
                class="bg-indigo-50/40 dark:bg-gray-800/60 rounded-3xl border-2 border-dashed border-indigo-200/80 dark:border-gray-700/80 shadow-xs p-6 sm:p-7 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span
                            class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 flex items-center justify-center font-black text-sm">
                            ۲
                        </span>
                        <div>
                            <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                                بارگذاری هوشمند و شناسایی الگو از فایل JSON
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                می‌توانید فایل ساختار الگو را با فرمت JSON بارگذاری کنید تا خودکار خوانده و جایگذاری
                                شود.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="downloadSampleJson()"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 text-xs font-bold transition-all shadow-2xs cursor-pointer">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span>دانلود نمونه فایل JSON الگو</span>
                        </button>
                    </div>
                </div>

                {{-- JSON Feedback Banner --}}
                <template x-if="jsonFeedback">
                    <div class="p-4 rounded-2xl flex items-start gap-3 transition-all"
                         :class="jsonFeedback.success ? 'bg-emerald-50 text-emerald-800 border border-emerald-200/80 dark:bg-emerald-950/30 dark:text-emerald-300 dark:border-emerald-800/40' : 'bg-rose-50 text-rose-800 border border-rose-200/80 dark:bg-rose-950/30 dark:text-rose-300 dark:border-rose-800/40'">
                        <span class="p-1 rounded-lg shrink-0 mt-0.5"
                              :class="jsonFeedback.success ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/50'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      :d="jsonFeedback.success ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'"/>
                            </svg>
                        </span>
                        <div class="flex-1 text-xs font-bold leading-relaxed" x-text="jsonFeedback.message"></div>
                        <button type="button" @click="jsonFeedback = null"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-xs">✕
                        </button>
                    </div>
                </template>

                {{-- Upload Box & Options --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
                    {{-- Dropzone Area --}}
                    <div class="lg:col-span-8">
                        <label
                            class="relative flex flex-col items-center justify-center p-5 rounded-2xl border-2 border-dashed transition-all cursor-pointer group bg-white dark:bg-gray-900/50"
                            :class="isDraggingJson ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-950/40 ring-2 ring-indigo-500/20' : 'border-indigo-200/80 dark:border-gray-700 hover:border-indigo-400 dark:hover:border-indigo-500'"
                            @dragover.prevent="isDraggingJson = true"
                            @dragleave.prevent="isDraggingJson = false"
                            @drop.prevent="isDraggingJson = false; handleJsonDrop($event)">
                            <input type="file" accept=".json,application/json" @change="handleJsonFileInput($event)"
                                   class="sr-only">
                            <div class="flex items-center gap-3 text-center sm:text-right flex-col sm:flex-row">
                                <div
                                    class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                </div>
                                <div class="space-y-0.5">
                                    <p class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                        کلیک کنید یا فایل JSON را به این محدوده بکشید و رها کنید
                                    </p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-400">
                                        سیستم ساختارهای تو در تو (فاز، گروه، کار) را بی‌درنگ شناسایی و درون فرم بارگذاری
                                        می‌کند.
                                    </p>
                                </div>
                            </div>
                        </label>
                    </div>

                    {{-- Mode Switcher (Replace vs Append) --}}
                    <div
                        class="lg:col-span-4 bg-white dark:bg-gray-900/50 p-3.5 rounded-2xl border border-gray-200/70 dark:border-gray-700/60 space-y-2">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                            نحوه اعمال داده‌های فایل:
                        </label>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <button type="button" @click="jsonImportMode = 'replace'"
                                    class="py-2 px-2.5 rounded-xl font-bold transition-all text-center border cursor-pointer"
                                    :class="jsonImportMode === 'replace' ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700'">
                                جایگزینی کامل
                            </button>
                            <button type="button" @click="jsonImportMode = 'append'"
                                    class="py-2 px-2.5 rounded-xl font-bold transition-all text-center border cursor-pointer"
                                    :class="jsonImportMode === 'append' ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700'">
                                افزودن به ساختار
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="space-y-6">
                {{-- Section Toolbar --}}
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800/90 rounded-3xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-xs">
                    <div class="flex items-center gap-3">
                        <span
                            class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-sm">
                            ۳
                        </span>
                        <div>
                            <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                                ساختار فازها، گروه‌ها و کارها
                            </h2>
                            <p class="text-xs text-gray-400 mt-0.5">
                                سلسله‌مراتب فازهای پروژه، گروه‌های کاری و چک‌لیست‌های مربوطه را بسازید.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        {{-- Collapse/Expand Buttons --}}
                        <button type="button" @click="toggleAllPhases()"
                                class="px-3 py-2 rounded-xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/60 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-bold border border-gray-200 dark:border-gray-600 transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                            </svg>
                            <span x-text="allPhasesCollapsed ? 'باز کردن همه فازها' : 'جمع کردن فازها'"></span>
                        </button>

                        <button type="button" @click="toggleAllTasks()"
                                class="px-3 py-2 rounded-xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/60 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-bold border border-gray-200 dark:border-gray-600 transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                            </svg>
                            <span x-text="allTasksCollapsed ? 'باز کردن گروه‌ها' : 'جمع کردن گروه‌ها'"></span>
                        </button>

                        <button type="button" @click="toggleSelectAll()"
                                class="px-3 py-2 rounded-xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/60 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-bold border border-gray-200 dark:border-gray-600 transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer"
                                title="انتخاب همه فازها و گروه‌ها برای عملیات گروهی">
                            <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span
                                x-text="totalSelectedCount > 0 ? 'لغو انتخاب همه' : 'انتخاب همه فازها و گروه‌ها'"></span>
                        </button>

                        {{-- Add Phase Button --}}
                        <button type="button" @click="addPhase()"
                                class="px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-500/25 transition-all flex items-center gap-1.5 active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>+ ایجاد فاز جدید</span>
                        </button>

                        {{-- Add Unphased Group Button --}}
                        <button type="button" @click="addUnphasedTask()"
                                class="px-4 py-2.5 rounded-2xl bg-white dark:bg-gray-800 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-bold text-xs border border-indigo-200 dark:border-indigo-800/60 transition-all flex items-center gap-1.5 shadow-xs active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>+ گروه عمومی (بدون فاز)</span>
                        </button>
                    </div>
                </div>

                {{-- Empty State --}}
                <template
                    x-if="(!structure.phases || structure.phases.length === 0) && (!structure.unphased_tasks || structure.unphased_tasks.length === 0)">
                    <div
                        class="bg-white dark:bg-gray-800/80 rounded-3xl p-12 text-center border-2 border-dashed border-gray-200 dark:border-gray-700 space-y-4">
                        <div
                            class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto shadow-xs">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">هنوز فاز یا گروهی به این الگو
                            اضافه نشده است</h3>
                        <p class="text-xs text-gray-400 max-w-md mx-auto">
                            می‌توانید یک فایل JSON آپلود کنید تا خودکار پر شود، یا با کلیک بر روی دکمه‌های زیر فازها و
                            گروه‌های جدید را دستی بسازید.
                        </p>
                        <div class="flex items-center justify-center gap-3 pt-2">
                            <button type="button" @click="addPhase()"
                                    class="px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition-all cursor-pointer">
                                + افزودن فاز اول
                            </button>
                            <button type="button" @click="addUnphasedTask()"
                                    class="px-5 py-2.5 rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all cursor-pointer">
                                + افزودن گروه عمومی
                            </button>
                        </div>
                    </div>
                </template>

                {{-- PHASES LIST --}}
                <div class="space-y-6">
                    <template x-for="(phase, pIdx) in structure.phases" :key="'phase-' + pIdx">
                        <div
                            class="bg-white dark:bg-gray-800/90 rounded-3xl border shadow-xs overflow-hidden transition-all duration-200"
                            :class="isPhaseSelected(pIdx) ? 'ring-2 ring-indigo-500 border-indigo-500' : 'border-gray-200/90 dark:border-gray-700/70'"
                            :style="'border-top: 5px solid ' + (phase.color || '#6366f1')">

                            {{-- Phase Header Bar --}}
                            <div
                                class="p-4 sm:p-5 bg-gray-50/90 dark:bg-gray-900/50 border-b border-gray-200/80 dark:border-gray-700/60 flex items-center justify-between gap-4 select-none flex-wrap">

                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    {{-- Checkbox with Clickable Label for Name/Row --}}
                                    <label
                                        class="flex items-center gap-2 cursor-pointer p-1.5 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors shrink-0"
                                        title="کلیک برای انتخاب یا عدم انتخاب فاز">
                                        <input type="checkbox"
                                               :value="String(pIdx)"
                                               x-model="selectedPhases"
                                               class="w-4 h-4 rounded-md text-indigo-600 focus:ring-2 focus:ring-indigo-500/20 border-gray-300 dark:border-gray-600 dark:bg-gray-700/80 transition-all cursor-pointer">
                                        <span
                                            class="text-xs font-bold text-gray-600 dark:text-gray-300 select-none hidden md:inline">انتخاب</span>
                                    </label>

                                    {{-- Collapse / Expand Chevron Icon --}}
                                    <button type="button" @click="togglePhase(pIdx)"
                                            class="p-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-2xs shrink-0 cursor-pointer"
                                            :class="collapsedPhases[pIdx] ? 'rotate-180' : ''">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    {{-- Phase Number Badge --}}
                                    <span
                                        class="w-7 h-7 rounded-xl flex items-center justify-center font-black text-xs text-white shadow-xs shrink-0 cursor-pointer hover:scale-105 active:scale-95 transition-transform"
                                        :style="'background-color: ' + (phase.color || '#6366f1')"
                                        @click="togglePhaseSelection(pIdx)"
                                        title="کلیک برای انتخاب یا عدم انتخاب این فاز"
                                        x-text="pIdx + 1"></span>

                                    {{-- Phase Title & Description Inputs --}}
                                    <div class="min-w-0 flex-1 flex flex-col sm:flex-row sm:items-center gap-2">
                                        <input type="text" x-model="phase.name" required
                                               placeholder="عنوان فاز (مثلاً: فاز ۱ - تحلیل و مستندسازی)..."
                                               class="font-black text-gray-900 dark:text-white text-sm sm:text-base bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 px-3.5 py-2 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 dark:placeholder-gray-500 flex-1">

                                        <input type="text" x-model="phase.description"
                                               placeholder="توضیحات فاز (اختیاری)..."
                                               class="text-xs text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 focus:ring-1 focus:ring-indigo-500 dark:placeholder-gray-500 sm:w-1/3">
                                    </div>

                                    {{-- Color Picker & Presets --}}
                                    <div class="flex items-center gap-1.5 shrink-0" title="انتخاب رنگ فاز">
                                        <div
                                            class="relative flex items-center justify-center w-7 h-7 rounded-xl cursor-pointer shadow-2xs border border-black/10 overflow-hidden"
                                            :style="'background-color: ' + (phase.color || '#6366f1')">
                                            <input type="color" x-model="phase.color"
                                                   class="absolute inset-0 opacity-0 w-full h-full cursor-pointer">
                                        </div>
                                    </div>
                                </div>

                                {{-- Phase Header Actions --}}
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold border shadow-2xs"
                                          :style="'background-color: ' + (phase.color || '#6366f1') + '15; color: ' + (phase.color || '#6366f1') + '; border-color: ' + (phase.color || '#6366f1') + '33;'"
                                          x-text="(phase.tasks ? phase.tasks.length : 0) + ' گروه کاری'">
                                    </span>

                                    <button type="button" @click="addTaskToPhase(pIdx)"
                                            class="px-3.5 py-2 rounded-xl bg-white dark:bg-gray-800 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-900/40 dark:hover:text-indigo-300 text-gray-700 dark:text-gray-300 text-xs font-bold border border-gray-200 dark:border-gray-700 transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span>+ گروه جدید</span>
                                    </button>

                                    <button type="button" @click="removePhase(pIdx)"
                                            class="p-2 rounded-xl bg-white dark:bg-gray-800 text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 dark:hover:text-rose-400 border border-gray-200 dark:border-gray-700 transition-all shadow-2xs cursor-pointer"
                                            title="حذف این فاز">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                             stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Phase Body: Tasks / Groups List --}}
                            <div x-show="!collapsedPhases[pIdx]"
                                 x-transition
                                 class="p-4 sm:p-6 space-y-4 bg-gray-50/40 dark:bg-gray-900/30">

                                <template x-if="!phase.tasks || phase.tasks.length === 0">
                                    <div
                                        class="py-8 text-center text-gray-400 text-xs bg-white/60 dark:bg-gray-800/50 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                                        هنوز گروهی در این فاز ثبت نشده است.
                                        <button type="button" @click="addTaskToPhase(pIdx)"
                                                class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline mr-1 cursor-pointer">
                                            افزودن اولین گروه
                                        </button>
                                    </div>
                                </template>

                                {{-- Group Cards inside Phase --}}
                                <template x-for="(task, tIdx) in phase.tasks" :key="'p-' + pIdx + '-t-' + tIdx">
                                    <div
                                        class="bg-white dark:bg-gray-800 rounded-2xl border transition-all p-4 sm:p-5 space-y-4 shadow-2xs"
                                        :class="isTaskSelected('p' + pIdx + '_t' + tIdx) ? 'ring-2 ring-indigo-500 border-indigo-400 bg-indigo-50/10 dark:bg-indigo-950/25' : 'border-gray-200/80 dark:border-gray-700/60'"
                                        :style="'border-right: 4px solid ' + (phase.color || '#6366f1')">

                                        {{-- Task / Group Top Header --}}
                                        <div class="flex flex-col gap-3">
                                            <div
                                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 select-none">
                                                <div class="flex items-start sm:items-center gap-3 min-w-0 flex-1">
                                                    {{-- Clickable Label Checkbox for Group --}}
                                                    <label
                                                        class="flex items-center gap-2 cursor-pointer p-1.5 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors shrink-0"
                                                        title="کلیک برای انتخاب یا عدم انتخاب این گروه">
                                                        <input type="checkbox"
                                                               :value="'p' + pIdx + '_t' + tIdx"
                                                               x-model="selectedTasks"
                                                               class="w-4 h-4 rounded-md text-indigo-600 focus:ring-2 focus:ring-indigo-500/20 border-gray-300 dark:border-gray-600 dark:bg-gray-700/80 transition-all cursor-pointer">
                                                        <span
                                                            class="text-xs font-bold text-gray-500 dark:text-gray-400 select-none hidden lg:inline">انتخاب</span>
                                                    </label>

                                                    {{-- Collapse / Expand Chevron Icon --}}
                                                    <button type="button" @click="toggleTask('p' + pIdx + '_t' + tIdx)"
                                                            class="p-1.5 rounded-xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/60 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 transition-all shadow-2xs shrink-0 cursor-pointer"
                                                            :class="collapsedTasks['p' + pIdx + '_t' + tIdx] ? 'rotate-180' : ''">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                    </button>

                                                    {{-- Group Icon Indicator --}}
                                                    <span
                                                        class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-2xs cursor-pointer hover:scale-105 active:scale-95 transition-transform"
                                                        :style="'background: ' + (phase.color || '#6366f1') + '18; border: 1.5px solid ' + (phase.color || '#6366f1') + '33; color: ' + (phase.color || '#6366f1')"
                                                        @click="toggleTaskSelection('p' + pIdx + '_t' + tIdx)"
                                                        title="کلیک برای انتخاب یا عدم انتخاب این گروه">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                        </svg>
                                                    </span>

                                                    {{-- Title & Description Inputs --}}
                                                    <div class="min-w-0 flex-1 space-y-1.5">
                                                        <input type="text" x-model="task.title" required
                                                               placeholder="عنوان گروه وظیفه (مثلاً: طراحی ظاهر و صفحات)..."
                                                               class="w-full font-bold text-gray-900 dark:text-white text-sm sm:text-base rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-3.5 py-1.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:placeholder-gray-500">
                                                        <input type="text" x-model="task.description"
                                                               placeholder="توضیحات تکمیلی این گروه (اختیاری)..."
                                                               class="w-full text-xs text-gray-500 dark:text-gray-400 rounded-lg border border-gray-100 dark:border-gray-700/60 bg-gray-50/30 dark:bg-gray-900/30 px-3 py-1 focus:ring-1 focus:ring-indigo-500 transition-all dark:placeholder-gray-500">
                                                    </div>
                                                </div>

                                                {{-- Group Actions --}}
                                                <div class="flex items-center gap-2 flex-wrap shrink-0">
                                                    <span
                                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800/40 shadow-2xs"
                                                        x-text="(task.items ? task.items.length : 0) + ' کار'">
                                                    </span>

                                                    <button type="button" @click="addItemToPhaseTask(pIdx, tIdx)"
                                                            class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/30 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-xs font-bold border border-emerald-200 dark:border-emerald-800/50 transition-all flex items-center gap-1 shadow-2xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none"
                                                             viewBox="0 0 24 24" stroke="currentColor"
                                                             stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                        <span>+ کار جدید</span>
                                                    </button>

                                                    <button type="button" @click="removeTaskFromPhase(pIdx, tIdx)"
                                                            class="p-1.5 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 dark:hover:text-rose-400 transition-colors cursor-pointer"
                                                            title="حذف گروه">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Group Meta Row: Manager & Due Date --}}
                                            <div
                                                class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3 border-t border-gray-100 dark:border-gray-700/50">
                                                {{-- Manager Select --}}
                                                <div class="flex items-center gap-2">
                                                    <label
                                                        class="text-xs font-bold text-gray-600 dark:text-gray-400 shrink-0 flex items-center gap-1.5">
                                                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none"
                                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                        </svg>
                                                        مدیر گروه:
                                                    </label>
                                                    <select x-model="task.manager_id"
                                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 px-3 py-1.5 text-xs font-medium text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                                                        <option value=""
                                                                class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                                            (بدون مدیر گروه)
                                                        </option>
                                                        @foreach($users as $u)
                                                            <option value="{{ $u->id }}"
                                                                    class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">{{ $u->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                {{-- Due Date --}}
                                                <div class="flex items-center gap-2">
                                                    <label
                                                        class="text-xs font-bold text-gray-600 dark:text-gray-400 shrink-0 flex items-center gap-1.5">
                                                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none"
                                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
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
                                                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 px-3 py-1.5 text-xs font-medium text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer dark:placeholder-gray-500">
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
                                             x-transition
                                             class="pt-3 border-t border-gray-100 dark:border-gray-700/60 space-y-2.5">

                                            <div class="flex items-center justify-between text-xs pb-1">
                                                <span
                                                    class="font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    کارهای این گروه (چک‌لیست اجرایی):
                                                </span>
                                                <span class="text-[11px] text-gray-400 font-bold"
                                                      x-text="(task.items ? task.items.length : 0) + ' کار تعریف شده'"></span>
                                            </div>

                                            <div class="space-y-2.5">
                                                <template x-if="!task.items || task.items.length === 0">
                                                    <div
                                                        class="py-3 text-center text-xs text-gray-400 bg-gray-50/50 dark:bg-gray-900/30 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                                                        هنوز کاری برای این گروه اضافه نشده است. روی «+ کار جدید» کلیک
                                                        کنید.
                                                    </div>
                                                </template>

                                                <template x-for="(item, iIdx) in task.items"
                                                          :key="'p-' + pIdx + '-t-' + tIdx + '-i-' + iIdx">
                                                    <div
                                                        class="group flex flex-col md:flex-row md:items-center justify-between gap-2.5 p-3 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200/70 dark:border-gray-700/60 hover:border-indigo-300 dark:hover:border-indigo-700 transition-all shadow-2xs">
                                                        {{-- Work Item Title & Description --}}
                                                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                                            <span
                                                                class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 text-[10px] font-black flex items-center justify-center shrink-0"
                                                                x-text="iIdx + 1"></span>

                                                            <input type="text" x-model="item.title" required
                                                                   placeholder="عنوان کار یا فعالیت اجرایی..."
                                                                   class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-bold text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:placeholder-gray-500">

                                                            <input type="text" x-model="item.description"
                                                                   placeholder="توضیحات تکمیلی کار..."
                                                                   class="w-1/3 sm:w-2/5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs text-gray-500 dark:text-gray-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:placeholder-gray-500">
                                                        </div>

                                                        {{-- Work Item Assignee, Due Date & Delete Action --}}
                                                        <div
                                                            class="flex items-center gap-2 shrink-0 flex-wrap sm:flex-nowrap">
                                                            {{-- Assignee (مسئول کار) --}}
                                                            <div class="flex items-center gap-1.5">
                                                                <select x-model="item.assigned_to"
                                                                        title="مسئول کار"
                                                                        class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-1.5 text-xs text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                                                                    <option value=""
                                                                            class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                                                        (پیش‌فرض مدیر گروه)
                                                                    </option>
                                                                    @foreach($users as $u)
                                                                        <option
                                                                            value="{{ $u->id }}"
                                                                            class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">{{ $u->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            {{-- Due Date --}}
                                                            <div class="relative flex items-center w-28 sm:w-32">
                                                                <input type="text" data-jdp data-jdp-only-date
                                                                       x-model="item.due_date"
                                                                       placeholder="۱۴۰۴/۰۱/۱۵"
                                                                       autocomplete="off"
                                                                       @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                                                       @focus="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                                                       @change="item.due_date = $event.target.value"
                                                                       @input="item.due_date = $event.target.value"
                                                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-1.5 text-xs text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer dark:placeholder-gray-500">
                                                                <button type="button" x-show="item.due_date"
                                                                        @click.stop="item.due_date = ''"
                                                                        class="absolute left-1.5 p-1 text-gray-400 hover:text-red-500 text-xs"
                                                                        title="پاک کردن">
                                                                    ✕
                                                                </button>
                                                            </div>

                                                            {{-- Delete Item Action --}}
                                                            <button type="button"
                                                                    @click="removeItemFromPhaseTask(pIdx, tIdx, iIdx)"
                                                                    class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 dark:hover:text-rose-400 transition-colors cursor-pointer"
                                                                    title="حذف کار">
                                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                                     stroke="currentColor" stroke-width="2">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
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
                </div>
                <div
                    class="bg-white dark:bg-gray-800/90 rounded-3xl border border-gray-200/90 dark:border-gray-700/70 shadow-xs overflow-hidden"
                    style="border-top: 5px solid #0284c7">

                    {{-- Unphased Header Bar --}}
                    <div
                        class="p-4 sm:p-5 bg-sky-50/50 dark:bg-sky-950/30 border-b border-gray-200/80 dark:border-gray-700/60 flex items-center justify-between gap-4 select-none flex-wrap">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <button type="button" @click="togglePhase('unphased')"
                                    class="p-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-2xs shrink-0 cursor-pointer"
                                    :class="collapsedPhases['unphased'] ? 'rotate-180' : ''">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <span
                                class="w-8 h-8 rounded-xl bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300 flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </span>

                            <div>
                                <h3 class="font-black text-gray-900 dark:text-white text-sm sm:text-base">
                                    گروه‌های کاری عمومی (بدون فاز اختصاصی)
                                </h3>
                                <p class="text-[11px] text-gray-400 mt-0.5">
                                    کارهایی که وابستگی زمانی یا مرحله‌ای به فاز مشخصی ندارند و در طول کل پروژه جاری
                                    هستند.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <span
                                class="px-2.5 py-1 rounded-xl text-xs font-bold bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300 border border-sky-200/60 dark:border-sky-800/40 shadow-2xs"
                                x-text="(structure.unphased_tasks ? structure.unphased_tasks.length : 0) + ' گروه عمومی'">
                            </span>

                            <button type="button" @click="addUnphasedTask()"
                                    class="px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-2xs transition-all flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>+ افزودن گروه عمومی</span>
                            </button>
                        </div>
                    </div>

                    {{-- Unphased Groups List --}}
                    <div x-show="!collapsedPhases['unphased']"
                         x-transition
                         class="p-4 sm:p-6 space-y-4 bg-gray-50/40 dark:bg-gray-900/30">

                        <template x-if="!structure.unphased_tasks || structure.unphased_tasks.length === 0">
                            <div
                                class="py-6 text-center text-gray-400 text-xs bg-white/60 dark:bg-gray-800/50 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                                گروه عمومی ثبت نشده است. در صورت تمایل می‌توانید با کلیک بر روی «+ افزودن گروه عمومی»
                                اضافه کنید.
                            </div>
                        </template>

                        <template x-for="(task, tIdx) in structure.unphased_tasks" :key="'unphased-t-' + tIdx">
                            <div
                                class="bg-white dark:bg-gray-800 rounded-2xl border transition-all p-4 sm:p-5 space-y-4 shadow-2xs"
                                :class="isTaskSelected('unphased_' + tIdx) ? 'ring-2 ring-indigo-500 border-indigo-400 bg-indigo-50/10 dark:bg-indigo-950/25' : 'border-gray-200/80 dark:border-gray-700/60'"
                                style="border-right: 4px solid #0284c7">

                                {{-- Group Header --}}
                                <div class="flex flex-col gap-3">
                                    <div
                                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 select-none">
                                        <div class="flex items-start sm:items-center gap-3 min-w-0 flex-1">
                                            {{-- Clickable Label Checkbox for Unphased Group --}}
                                            <label
                                                class="flex items-center gap-2 cursor-pointer p-1.5 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors shrink-0"
                                                title="کلیک برای انتخاب یا عدم انتخاب این گروه">
                                                <input type="checkbox"
                                                       :value="'unphased_' + tIdx"
                                                       x-model="selectedTasks"
                                                       class="w-4 h-4 rounded-md text-indigo-600 focus:ring-2 focus:ring-indigo-500/20 border-gray-300 dark:border-gray-600 dark:bg-gray-700/80 transition-all cursor-pointer">
                                                <span
                                                    class="text-xs font-bold text-gray-500 dark:text-gray-400 select-none hidden lg:inline">انتخاب</span>
                                            </label>

                                            <button type="button" @click="toggleTask('unphased_t' + tIdx)"
                                                    class="p-1.5 rounded-xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/60 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 transition-all shadow-2xs shrink-0 cursor-pointer"
                                                    :class="collapsedTasks['unphased_t' + tIdx] ? 'rotate-180' : ''">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>

                                            <span
                                                class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0 border border-sky-200 dark:border-sky-800/40 cursor-pointer hover:scale-105 active:scale-95 transition-transform"
                                                @click="toggleTaskSelection('unphased_' + tIdx)"
                                                title="کلیک برای انتخاب یا عدم انتخاب این گروه">
                                                <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                                            </span>

                                            <div class="min-w-0 flex-1 space-y-1.5">
                                                <input type="text" x-model="task.title" required
                                                       placeholder="عنوان گروه وظیفه عمومی..."
                                                       class="w-full font-bold text-gray-900 dark:text-white text-sm sm:text-base rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 px-3.5 py-1.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:placeholder-gray-500">
                                                <input type="text" x-model="task.description"
                                                       placeholder="توضیحات تکمیلی گروه (اختیاری)..."
                                                       class="w-full text-xs text-gray-500 dark:text-gray-400 rounded-lg border border-gray-100 dark:border-gray-700/60 bg-gray-50/30 dark:bg-gray-900/30 px-3 py-1 focus:ring-1 focus:ring-indigo-500 transition-all dark:placeholder-gray-500">
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 flex-wrap shrink-0">
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800/40 shadow-2xs"
                                                x-text="(task.items ? task.items.length : 0) + ' کار'">
                                            </span>

                                            <button type="button" @click="addItemToUnphasedTask(tIdx)"
                                                    class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/30 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-xs font-bold border border-emerald-200 dark:border-emerald-800/50 transition-all flex items-center gap-1 shadow-2xs cursor-pointer">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none"
                                                     viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M12 4v16m8-8H4"/>
                                                </svg>
                                                <span>+ کار جدید</span>
                                            </button>

                                            <button type="button" @click="removeUnphasedTask(tIdx)"
                                                    class="p-1.5 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 dark:hover:text-rose-400 transition-colors cursor-pointer"
                                                    title="حذف گروه">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Meta Row --}}
                                    <div
                                        class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3 border-t border-gray-100 dark:border-gray-700/50">
                                        <div class="flex items-center gap-2">
                                            <label
                                                class="text-xs font-bold text-gray-600 dark:text-gray-400 shrink-0 flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                مدیر گروه:
                                            </label>
                                            <select x-model="task.manager_id"
                                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 px-3 py-1.5 text-xs font-medium text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                                                <option value=""
                                                        class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                                    (بدون مدیر گروه)
                                                </option>
                                                @foreach($users as $u)
                                                    <option value="{{ $u->id }}"
                                                            class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">{{ $u->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <label
                                                class="text-xs font-bold text-gray-600 dark:text-gray-400 shrink-0 flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24"
                                                     stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
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
                                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 px-3 py-1.5 text-xs font-medium text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer dark:placeholder-gray-500">
                                                <button type="button" x-show="task.due_date"
                                                        @click.stop="task.due_date = ''"
                                                        class="absolute left-2.5 p-1 text-gray-400 hover:text-red-500 text-xs"
                                                        title="پاک کردن">
                                                    ✕
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Checklist Items inside Unphased Group --}}
                                <div x-show="!collapsedTasks['unphased_t' + tIdx]"
                                     x-transition
                                     class="pt-3 border-t border-gray-100 dark:border-gray-700/60 space-y-2.5">

                                    <div class="flex items-center justify-between text-xs pb-1">
                                        <span
                                            class="font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            کارهای این گروه (چک‌لیست اجرایی):
                                        </span>
                                        <span class="text-[11px] text-gray-400 font-bold"
                                              x-text="(task.items ? task.items.length : 0) + ' کار تعریف شده'"></span>
                                    </div>

                                    <div class="space-y-2.5">
                                        <template x-if="!task.items || task.items.length === 0">
                                            <div
                                                class="py-3 text-center text-xs text-gray-400 bg-gray-50/50 dark:bg-gray-900/30 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                                                هنوز کاری برای این گروه اضافه نشده است. روی «+ کار جدید» کلیک کنید.
                                            </div>
                                        </template>

                                        <template x-for="(item, iIdx) in task.items"
                                                  :key="'unphased-t-' + tIdx + '-i-' + iIdx">
                                            <div
                                                class="group flex flex-col md:flex-row md:items-center justify-between gap-2.5 p-3 rounded-2xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200/70 dark:border-gray-700/60 hover:border-indigo-300 dark:hover:border-indigo-700 transition-all shadow-2xs">
                                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                                    <span
                                                        class="w-6 h-6 rounded-lg bg-sky-50 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400 text-[10px] font-black flex items-center justify-center shrink-0"
                                                        x-text="iIdx + 1"></span>

                                                    <input type="text" x-model="item.title" required
                                                           placeholder="عنوان کار یا فعالیت اجرایی..."
                                                           class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-bold text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:placeholder-gray-500">

                                                    <input type="text" x-model="item.description"
                                                           placeholder="توضیحات تکمیلی کار..."
                                                           class="w-1/3 sm:w-2/5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs text-gray-500 dark:text-gray-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:placeholder-gray-500">
                                                </div>

                                                <div class="flex items-center gap-2 shrink-0 flex-wrap sm:flex-nowrap">
                                                    <select x-model="item.assigned_to"
                                                            title="مسئول کار"
                                                            class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-1.5 text-xs text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer">
                                                        <option value=""
                                                                class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                                            (پیش‌فرض مدیر گروه)
                                                        </option>
                                                        @foreach($users as $u)
                                                            <option value="{{ $u->id }}"
                                                                    class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">{{ $u->name }}</option>
                                                        @endforeach
                                                    </select>

                                                    <div class="relative flex items-center w-28 sm:w-32">
                                                        <input type="text" data-jdp data-jdp-only-date
                                                               x-model="item.due_date"
                                                               placeholder="۱۴۰۴/۰۱/۱۵"
                                                               autocomplete="off"
                                                               @click="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                                               @focus="if(window.jalaliDatepicker) { jalaliDatepicker.updateOptions({date: true, time: false}); jalaliDatepicker.show($el); }"
                                                               @change="item.due_date = $event.target.value"
                                                               @input="item.due_date = $event.target.value"
                                                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-1.5 text-xs text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer dark:placeholder-gray-500">
                                                        <button type="button" x-show="item.due_date"
                                                                @click.stop="item.due_date = ''"
                                                                class="absolute left-1.5 p-1 text-gray-400 hover:text-red-500 text-xs"
                                                                title="پاک کردن">
                                                            ✕
                                                        </button>
                                                    </div>

                                                    <button type="button"
                                                            @click="removeItemFromUnphasedTask(tIdx, iIdx)"
                                                            class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 dark:hover:text-rose-400 transition-colors cursor-pointer"
                                                            title="حذف کار">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                             stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
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
            </div>
            <div
                class="sticky bottom-4 z-30 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl rounded-3xl p-4 sm:p-5 border border-gray-200/80 dark:border-gray-700/80 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                {{-- Left: Live Statistics & Batch Actions --}}
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="flex items-center gap-2 text-xs font-bold text-gray-600 dark:text-gray-300">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>مجموع ساختار الگو:</span>
                        <span class="text-indigo-600 dark:text-indigo-400 font-black"
                              x-text="(structure.phases ? structure.phases.length : 0) + ' فاز'"></span>
                        <span>•</span>
                        <span class="text-indigo-600 dark:text-indigo-400 font-black"
                              x-text="totalTasksCount + ' گروه'"></span>
                        <span>•</span>
                        <span class="text-indigo-600 dark:text-indigo-400 font-black"
                              x-text="totalItemsCount + ' کار'"></span>
                    </div>

                    {{-- Batch Selected Tools --}}
                    <div x-show="totalSelectedCount > 0" x-cloak x-transition
                         class="flex items-center gap-2 mr-2 pr-3 border-r border-gray-200 dark:border-gray-700">
                        <span
                            class="text-xs font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/40 px-2.5 py-1 rounded-xl"
                            x-text="totalSelectedCount + ' مورد انتخاب‌شده'"></span>

                        <button type="button" @click="deleteSelected()"
                                class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>حذف انتخاب‌شده‌ها</span>
                        </button>

                        <button type="button" @click="deselectAll()"
                                class="px-2.5 py-1 rounded-xl text-gray-500 hover:text-gray-700 dark:text-gray-400 text-xs font-bold transition-colors cursor-pointer">
                            لغو
                        </button>
                    </div>
                </div>

                {{-- Right: Main Actions --}}
                <div class="flex items-center gap-2.5 justify-end">
                    <a href="{{ route('projects.templates.index') }}"
                       class="px-5 py-2.5 rounded-2xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-xs font-bold transition-colors">
                        انصراف
                    </a>

                    <button type="submit"
                            class="px-6 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/40 transition-all flex items-center gap-2 active:scale-95 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>ذخیره نهایی الگوی ساختار</span>
                    </button>
                </div>
            </div>
        </form>
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
                    isDraggingJson: false,
                    jsonImportMode: 'replace',
                    jsonFeedback: null,

                    get totalTasksCount() {
                        let count = 0;
                        (this.structure.phases || []).forEach(p => {
                            count += (p.tasks || []).length;
                        });
                        count += (this.structure.unphased_tasks || []).length;
                        return count;
                    },

                    get totalItemsCount() {
                        let count = 0;
                        (this.structure.phases || []).forEach(p => {
                            (p.tasks || []).forEach(t => {
                                count += (t.items || []).length;
                            });
                        });
                        (this.structure.unphased_tasks || []).forEach(t => {
                            count += (t.items || []).length;
                        });
                        return count;
                    },

                    get totalSelectedCount() {
                        return this.selectedPhases.length + this.selectedTasks.length;
                    },

                    isPhaseSelected(pIdx) {
                        const sIdx = String(pIdx);
                        const nIdx = Number(pIdx);
                        return this.selectedPhases.some(x => x === sIdx || x === nIdx);
                    },

                    isTaskSelected(key) {
                        return this.selectedTasks.includes(String(key));
                    },

                    togglePhaseSelection(pIdx) {
                        const sIdx = String(pIdx);
                        const nIdx = Number(pIdx);
                        const pos = this.selectedPhases.findIndex(x => x === sIdx || x === nIdx);
                        if (pos > -1) {
                            this.selectedPhases.splice(pos, 1);
                        } else {
                            this.selectedPhases.push(sIdx);
                        }
                    },

                    toggleTaskSelection(key) {
                        const sKey = String(key);
                        const pos = this.selectedTasks.indexOf(sKey);
                        if (pos > -1) {
                            this.selectedTasks.splice(pos, 1);
                        } else {
                            this.selectedTasks.push(sKey);
                        }
                    },

                    toggleSelectAll() {
                        if (this.totalSelectedCount > 0) {
                            this.deselectAll();
                        } else {
                            this.selectAll();
                        }
                    },

                    selectAll() {
                        this.selectedPhases = (this.structure.phases || []).map((_, idx) => String(idx));
                        const tasks = [];
                        (this.structure.phases || []).forEach((p, pIdx) => {
                            (p.tasks || []).forEach((_, tIdx) => {
                                tasks.push('p' + pIdx + '_t' + tIdx);
                            });
                        });
                        (this.structure.unphased_tasks || []).forEach((_, tIdx) => {
                            tasks.push('unphased_' + tIdx);
                        });
                        this.selectedTasks = tasks;
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

                        // 1. Delete unphased tasks
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

                        // 3. Delete selected phases
                        const phaseIndices = [...this.selectedPhases].map(Number).sort((a, b) => b - a);
                        phaseIndices.forEach(pIdx => {
                            if (this.structure.phases && this.structure.phases[pIdx]) {
                                this.structure.phases.splice(pIdx, 1);
                            }
                        });

                        this.selectedPhases = [];
                        this.selectedTasks = [];
                    },

                    normalizeDueDate(val) {
                        if (!val && val !== 0) return '';
                        let str = String(val).trim();
                        if (!str) return '';
                        const persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
                        const latin = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                        for (let i = 0; i < persian.length; i++) {
                            str = str.replaceAll(persian[i], latin[i]);
                        }
                        if (/^\d{4}[-\/]\d{1,2}[-\/]\d{1,2}/.test(str)) {
                            return str.replace(/-/g, '/');
                        }
                        if (/^\d{1,4}$/.test(str)) {
                            const days = parseInt(str, 10);
                            const d = new Date(Date.now() + (days * 86400000));
                            try {
                                return new Intl.DateTimeFormat('fa-IR-u-nu-latn', {
                                    year: 'numeric',
                                    month: '2-digit',
                                    day: '2-digit'
                                }).format(d);
                            } catch (e) {
                                return '';
                            }
                        }
                        return str;
                    },

                    init() {
                        if (this.structure?.phases) {
                            this.structure.phases.forEach(p => {
                                (p.tasks || []).forEach(t => {
                                    t.due_date = this.normalizeDueDate(t.due_date);
                                    (t.items || []).forEach(i => {
                                        i.due_date = this.normalizeDueDate(i.due_date);
                                    });
                                });
                            });
                        }
                        if (this.structure?.unphased_tasks) {
                            this.structure.unphased_tasks.forEach(t => {
                                t.due_date = this.normalizeDueDate(t.due_date);
                                (t.items || []).forEach(i => {
                                    i.due_date = this.normalizeDueDate(i.due_date);
                                });
                            });
                        }
                        this.refreshDatepicker();
                    },

                    refreshDatepicker() {
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
                        (this.structure.phases || []).forEach((_, idx) => {
                            this.collapsedPhases[idx] = this.allPhasesCollapsed;
                        });
                        this.collapsedPhases['unphased'] = this.allPhasesCollapsed;
                    },

                    toggleAllTasks() {
                        this.allTasksCollapsed = !this.allTasksCollapsed;
                        (this.structure.phases || []).forEach((phase, pIdx) => {
                            (phase.tasks || []).forEach((_, tIdx) => {
                                this.collapsedTasks['p' + pIdx + '_t' + tIdx] = this.allTasksCollapsed;
                            });
                        });
                        (this.structure.unphased_tasks || []).forEach((_, tIdx) => {
                            this.collapsedTasks['unphased_t' + tIdx] = this.allTasksCollapsed;
                        });
                    },

                    addPhase() {
                        if (!this.structure.phases) this.structure.phases = [];
                        const colors = ['#6366f1', '#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4'];
                        const nextColor = colors[this.structure.phases.length % colors.length];

                        this.structure.phases.push({
                            name: '',
                            color: nextColor,
                            description: '',
                            tasks: [{
                                title: '',
                                description: '',
                                manager_id: '',
                                due_date: '',
                                items: []
                            }]
                        });
                        this.refreshDatepicker();
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
                        this.refreshDatepicker();
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
                        this.refreshDatepicker();
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
                        this.refreshDatepicker();
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
                        this.refreshDatepicker();
                    },

                    removeItemFromUnphasedTask(tIdx, iIdx) {
                        this.structure.unphased_tasks[tIdx].items.splice(iIdx, 1);
                    },

                    submitForm() {
                        const form = document.getElementById('template-create-form');
                        if (form) {
                            if (typeof form.requestSubmit === 'function') {
                                form.requestSubmit();
                            } else {
                                form.submit();
                            }
                        }
                    },

                    prepareSubmit(e) {
                        if (!this.form.title || !this.form.title.trim()) {
                            e.preventDefault();
                            alert('لطفاً عنوان الگو را وارد نمایید.');
                            return;
                        }
                        const hiddenStructureInput = e.target.querySelector('input[name="structure"]');
                        if (hiddenStructureInput) {
                            hiddenStructureInput.value = JSON.stringify(this.structure);
                        }
                    },

                    // --- JSON Import & Sample Tools ---
                    handleJsonFileInput(event) {
                        const file = event.target.files ? event.target.files[0] : null;
                        if (file) this.readAndApplyJson(file);
                    },

                    handleJsonDrop(event) {
                        const file = event.dataTransfer.files ? event.dataTransfer.files[0] : null;
                        if (file) this.readAndApplyJson(file);
                    },

                    readAndApplyJson(file) {
                        if (!file.name.endsWith('.json') && file.type !== 'application/json') {
                            this.jsonFeedback = {
                                success: false,
                                message: 'فرمت فایل انتخاب‌شده مجاز نیست. لطفاً یک فایل با پسوند .json انتخاب کنید.'
                            };
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = (e) => {
                            try {
                                let text = e.target.result;
                                // Remove potential BOM
                                if (text.charCodeAt(0) === 0xFEFF) {
                                    text = text.substring(1);
                                }
                                const parsed = JSON.parse(text);
                                this.applyParsedJson(parsed, file.name.replace(/\.json$/i, ''));
                            } catch (err) {
                                this.jsonFeedback = {
                                    success: false,
                                    message: 'خطا در پردازش فایل JSON: ساختار فایل نامعتبر یا آسیب‌دیده است (' + err.message + ').'
                                };
                            }
                        };
                        reader.onerror = () => {
                            this.jsonFeedback = {
                                success: false,
                                message: 'خطا در خواندن فایل از روی سیستم شما.'
                            };
                        };
                        reader.readAsText(file, 'UTF-8');
                    },

                    applyParsedJson(data, defaultTitle) {
                        if (!data || typeof data !== 'object') {
                            this.jsonFeedback = {
                                success: false,
                                message: 'محتوای فایل JSON معتبر نمی‌باشد.'
                            };
                            return;
                        }

                        // Title
                        const importedTitle = data.title || data.name || data['عنوان'] || data['نام'] || defaultTitle;
                        if (this.jsonImportMode === 'replace' || !this.form.title) {
                            this.form.title = importedTitle;
                        }

                        // Description
                        const importedDesc = data.description || data.desc || data['توضیحات'] || '';
                        if ((this.jsonImportMode === 'replace' || !this.form.description) && importedDesc) {
                            this.form.description = importedDesc;
                        }

                        // Category ID if provided
                        if (data.category_id && (this.jsonImportMode === 'replace' || !this.form.category_id)) {
                            this.form.category_id = String(data.category_id);
                        }

                        // Extract structure
                        let phases = [];
                        let unphased = [];

                        if (data.structure && typeof data.structure === 'object') {
                            phases = data.structure.phases || data.structure['فازها'] || [];
                            unphased = data.structure.unphased_tasks || data.structure['گروه‌ها'] || data.structure['tasks'] || [];
                        } else if (data.phases || data['فازها']) {
                            phases = data.phases || data['فازها'] || [];
                            unphased = data.unphased_tasks || data.tasks || data['گروه‌ها'] || [];
                        } else if (Array.isArray(data)) {
                            // Check if array of phases or tasks
                            const isPhaseList = data.some(item => item && (item.tasks || item.phases || item['فاز']));
                            if (isPhaseList) {
                                phases = data;
                            } else {
                                unphased = data;
                            }
                        } else if (data.tasks || data['گروه‌ها']) {
                            unphased = data.tasks || data['گروه‌ها'] || [];
                        }

                        // Format & Normalize
                        const colors = ['#6366f1', '#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#ec4899'];
                        const cleanPhases = phases.map((p, idx) => ({
                            name: p.name || p.title || p['عنوان'] || `فاز ${idx + 1}`,
                            color: p.color || colors[idx % colors.length],
                            description: p.description || p['توضیحات'] || '',
                            tasks: (p.tasks || p['گروه‌ها'] || p.items || []).map(t => ({
                                title: t.title || t.name || t['عنوان'] || '',
                                description: t.description || t['توضیحات'] || '',
                                manager_id: String(t.manager_id || t.assigned_to || ''),
                                due_date: this.normalizeDueDate(t.due_date || t['مهلت']),
                                items: (t.items || t.checklist || t['کارها'] || []).map(i => ({
                                    title: i.title || i.name || i['عنوان'] || '',
                                    description: i.description || i['توضیحات'] || '',
                                    assigned_to: String(i.assigned_to || ''),
                                    due_date: this.normalizeDueDate(i.due_date || i['مهلت']),
                                }))
                            }))
                        }));

                        const cleanUnphased = unphased.map(t => ({
                            title: t.title || t.name || t['عنوان'] || '',
                            description: t.description || t['توضیحات'] || '',
                            manager_id: String(t.manager_id || t.assigned_to || ''),
                            due_date: this.normalizeDueDate(t.due_date || t['مهلت']),
                            items: (t.items || t.checklist || t['کارها'] || []).map(i => ({
                                title: i.title || i.name || i['عنوان'] || '',
                                description: i.description || i['توضیحات'] || '',
                                assigned_to: String(i.assigned_to || ''),
                                due_date: this.normalizeDueDate(i.due_date || i['مهلت']),
                            }))
                        }));

                        if (this.jsonImportMode === 'replace') {
                            this.structure.phases = cleanPhases;
                            this.structure.unphased_tasks = cleanUnphased;
                        } else {
                            this.structure.phases = [...(this.structure.phases || []), ...cleanPhases];
                            this.structure.unphased_tasks = [...(this.structure.unphased_tasks || []), ...cleanUnphased];
                        }

                        this.jsonFeedback = {
                            success: true,
                            message: `ساختار الگو با موفقیت شناسایی شد: ${cleanPhases.length} فاز و ${(cleanPhases.reduce((acc, p) => acc + p.tasks.length, 0) + cleanUnphased.length)} گروه کاری در فرم جایگذاری گردید.`
                        };

                        this.refreshDatepicker();
                    },

                    downloadSampleJson() {
                        const sample = {
                            version: "1.0",
                            title: "الگوی استاندارد مدیریت پروژه و بازاریابی",
                            description: "الگوی آماده حاوی فازهای تحلیل، طراحی و توسعه با چک‌لیست‌های مربوطه",
                            structure: {
                                phases: [
                                    {
                                        name: "فاز ۱: نیازسنجی و برنامه‌ریزی",
                                        color: "#6366f1",
                                        description: "بررسی الزامات اولیه پروژه و تهیه مستندات",
                                        tasks: [
                                            {
                                                title: "جلسه بریف و مستندسازی نیازمندی‌ها",
                                                description: "برگزاری جلسات با کارفرما و استخراج RFP",
                                                manager_id: "",
                                                due_date: "1404/01/20",
                                                items: [
                                                    {
                                                        title: "تنظیم پرسشنامه نیازمندی‌ها",
                                                        description: "",
                                                        assigned_to: "",
                                                        due_date: "1404/01/15"
                                                    },
                                                    {
                                                        title: "تکمیل و تأیید سند بریف",
                                                        description: "",
                                                        assigned_to: "",
                                                        due_date: "1404/01/20"
                                                    }
                                                ]
                                            }
                                        ]
                                    },
                                    {
                                        name: "فاز ۲: طراحی رابط و تجربه کاربری (UI/UX)",
                                        color: "#8b5cf6",
                                        description: "طراحی وایرفریم‌ها و پروتوتایپ صفحات",
                                        tasks: [
                                            {
                                                title: "طراحی پروتوتایپ در فیگما",
                                                description: "طراحی نسخه اولیه صفحات اصلی و پنل کاربری",
                                                manager_id: "",
                                                due_date: "1404/02/10",
                                                items: [
                                                    {
                                                        title: "وایرفریمینگ ساختار کلی",
                                                        description: "",
                                                        assigned_to: "",
                                                        due_date: "1404/01/28"
                                                    },
                                                    {
                                                        title: "طراحی دیزاین سیستم و کامپوننت‌ها",
                                                        description: "",
                                                        assigned_to: "",
                                                        due_date: "1404/02/05"
                                                    },
                                                    {
                                                        title: "تأیید نهایی طرح از سمت کارفرما",
                                                        description: "",
                                                        assigned_to: "",
                                                        due_date: "1404/02/10"
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                ],
                                unphased_tasks: [
                                    {
                                        title: "مدیریت ارتباط با مشتری و هماهنگی‌ها",
                                        description: "جلسات هفتگی گزارش پیشرفت و صورت‌جلسات",
                                        manager_id: "",
                                        due_date: "",
                                        items: [
                                            {
                                                title: "ارسال گزارش پیشرفت هفتگی",
                                                description: "",
                                                assigned_to: "",
                                                due_date: ""
                                            }
                                        ]
                                    }
                                ]
                            }
                        };

                        const blob = new Blob([JSON.stringify(sample, null, 4)], {type: 'application/json;charset=utf-8;'});
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'sample-project-template.json';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);
                    }
                };
            }
        </script>
    @endpush

    @includeIf('partials.jalali-date-picker')
@endsection
