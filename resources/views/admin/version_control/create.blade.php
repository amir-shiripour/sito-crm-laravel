@extends('layouts.admin')

@section('content')
    <div class="py-10 bg-[#f8fafc] dark:bg-[#070a13] min-h-screen transition-colors duration-500">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Breadcrumb & Navigation --}}
            <nav class="flex mb-8" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-3 space-x-reverse">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400">پنل مدیریت</a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg>
                            <a href="{{ route('admin.version-control.index') }}" class="mr-1 text-sm font-medium text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400">کنترل نسخه</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg>
                            <span class="mr-1 text-sm font-bold text-indigo-600 dark:text-indigo-400">ثبت نسخه جدید</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <form action="{{ route('admin.version-control.store') }}" method="POST" class="relative">
                @csrf

                {{-- Header Card --}}
                <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                            انتشار <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600 dark:from-indigo-400 dark:to-violet-400">ورژن جدید</span>
                        </h1>
                        <p class="mt-2 text-slate-500 dark:text-slate-400 font-medium">اطلاعات فنی و لیست تغییرات هسته مرکزی را مدیریت کنید.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="history.back()" class="px-5 py-2.5 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-2xl transition-all">
                            انصراف
                        </button>
                        <button type="submit" class="group relative inline-flex items-center justify-center px-8 py-3 font-bold text-white transition-all duration-200 bg-indigo-600 font-pj rounded-2xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 shadow-xl shadow-indigo-600/20 hover:bg-indigo-700">
                            تایید و ثبت نهایی
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    {{-- Left Column: Main Info --}}
                    <div class="lg:col-span-2 space-y-8">
                        <div class="bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 shadow-2xl shadow-slate-200/50 dark:shadow-none rounded-[2.5rem] p-8 transition-all">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                {{-- Version Number --}}
                                <div class="space-y-2">
                                    <x-label for="version_number" value="شماره نسخه" class="text-sm font-bold text-slate-700 dark:text-slate-300 mr-1" />
                                    <div class="relative group">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                        </div>
                                        <input type="text" name="version_number" id="version_number" value="{{ old('version_number') }}" required
                                               class="block w-full pr-11 pl-4 py-3 bg-slate-100/50 dark:bg-slate-800/50 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-indigo-500/10 rounded-2xl text-slate-900 dark:text-white font-mono placeholder-slate-400 transition-all"
                                               placeholder="1.0.0">
                                    </div>
                                    <x-input-error for="version_number" />
                                </div>

                                {{-- Release Date --}}
                                <div class="space-y-2">
                                    <x-label for="release_date" value="تاریخ انتشار" class="text-sm font-bold text-slate-700 dark:text-slate-300 mr-1" />
                                    <div class="relative group">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                        <input type="date" name="release_date" id="release_date" value="{{ old('release_date', date('Y-m-d')) }}" required
                                               class="block w-full pr-11 pl-4 py-3 bg-slate-100/50 dark:bg-slate-800/50 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-indigo-500/10 rounded-2xl text-slate-900 dark:text-white transition-all">
                                    </div>
                                    <x-input-error for="release_date" />
                                </div>

                                {{-- Title --}}
                                <div class="md:col-span-2 space-y-2">
                                    <x-label for="title" value="عنوان انتشار" class="text-sm font-bold text-slate-700 dark:text-slate-300 mr-1" />
                                    <input type="text" name="title" id="title" value="{{ old('title') }}"
                                           class="block w-full px-5 py-3 bg-slate-100/50 dark:bg-slate-800/50 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-indigo-500/10 rounded-2xl text-slate-900 dark:text-white transition-all"
                                           placeholder="مثلاً: نسخه پایداری فوریه 2026">
                                    <x-input-error for="title" />
                                </div>

                                {{-- Summary --}}
                                <div class="md:col-span-2 space-y-2">
                                    <x-label for="summary" value="توضیحات و خلاصه" class="text-sm font-bold text-slate-700 dark:text-slate-300 mr-1" />
                                    <textarea name="summary" id="summary" rows="4"
                                              class="block w-full px-5 py-3 bg-slate-100/50 dark:bg-slate-800/50 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-indigo-500/10 rounded-[2rem] text-slate-900 dark:text-white transition-all resize-none"
                                              placeholder="توضیحات مختصری درباره اهداف این نسخه بنویسید...">{{ old('summary') }}</textarea>
                                    <x-input-error for="summary" />
                                </div>
                            </div>
                        </div>

                        {{-- Changelog Section --}}
                        <div x-data="{ items: {{ json_encode(old('changelog', [''])) }} }" class="bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 shadow-2xl shadow-slate-200/50 dark:shadow-none rounded-[2.5rem] p-8 transition-all">
                            <div class="flex items-center justify-between mb-8">
                                <div>
                                    <h3 class="text-lg font-black text-slate-900 dark:text-white">لیست تغییرات (Changelog)</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">جزئیات فنی اضافه شده یا باگ‌های رفع شده</p>
                                </div>
                                <button type="button" @click="items.push('')" class="flex items-center gap-2 px-4 py-2 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-xl text-xs font-bold hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    افزودن ردیف
                                </button>
                            </div>

                            <div class="space-y-4">
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="flex items-center gap-4 group animate-in slide-in-from-right duration-300">
                                        <span class="flex-shrink-0 w-10 h-10 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center text-xs font-mono font-bold" x-text="index + 1"></span>
                                        <div class="flex-1">
                                            <input type="text" x-model="items[index]" name="changelog[]"
                                                   class="block w-full px-5 py-3 bg-slate-50 dark:bg-slate-800/30 border border-slate-200 dark:border-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 rounded-2xl text-sm text-slate-900 dark:text-white transition-all"
                                                   placeholder="تغییرات را اینجا بنویسید...">
                                        </div>
                                        <button type="button" @click="items.splice(index, 1)" class="p-3 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-2xl transition-all opacity-0 group-hover:opacity-100">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <div x-show="items.length === 0" class="py-12 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-[2rem] text-center">
                                <p class="text-sm text-slate-400 dark:text-slate-500 italic font-medium">هیچ تغییری برای این نسخه ثبت نشده است.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Side Settings --}}
                    <div class="lg:col-span-1 space-y-8">
                        <div class="bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 shadow-2xl shadow-slate-200/50 dark:shadow-none rounded-[2.5rem] p-8">
                            <h3 class="text-lg font-black text-slate-900 dark:text-white mb-6">تنظیمات وضعیت</h3>

                            <div class="p-6 bg-indigo-50 dark:bg-indigo-500/5 rounded-[2rem] border border-indigo-100 dark:border-indigo-500/10">
                                <div class="flex items-start gap-4">
                                    <div class="mt-1">
                                        <input type="checkbox" name="is_current" id="is_current" value="1" {{ old('is_current') ? 'checked' : '' }}
                                        class="w-6 h-6 text-indigo-600 bg-white border-slate-300 rounded-lg focus:ring-indigo-500 dark:focus:ring-offset-slate-900 transition-all cursor-pointer">
                                    </div>
                                    <div>
                                        <label for="is_current" class="text-sm font-black text-slate-900 dark:text-white cursor-pointer select-none">نسخه فعال سیستم</label>
                                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">با فعال کردن این گزینه، این ورژن به عنوان نسخه فعلی در تمام بخش‌های CRM نمایش داده خواهد شد.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 pt-8 border-t border-slate-100 dark:border-slate-800">
                                <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-4">نکات مهم</h4>
                                <ul class="space-y-3">
                                    <li class="flex items-start gap-2 text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        شماره نسخه‌ها باید منحصر‌به‌فرد باشند.
                                    </li>
                                    <li class="flex items-start gap-2 text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        تغییر نسخه فعلی بر تمام کاربران اثر می‌گذارد.
                                    </li>
                                </ul>
                            </div>
                        </div>

                        {{-- Dynamic Preview Helper (Optional) --}}
                        <div class="bg-gradient-to-br from-indigo-600 to-violet-700 rounded-[2.5rem] p-8 text-white shadow-2xl shadow-indigo-500/30 overflow-hidden relative group">
                            <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform duration-700">
                                <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24"><path d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <h4 class="text-lg font-bold mb-2">اطمینان از پایداری</h4>
                            <p class="text-xs text-indigo-100 leading-relaxed opacity-80">قبل از انتشار عمومی، مطمئن شوید تمام تست‌های مربوط به ماژول‌های فعال را با موفقیت پشت سر گذاشته‌اید.</p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(10px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .animate-fadeIn {
            animation: fadeIn 0.4s ease-out forwards;
        }
    </style>
@endsection
