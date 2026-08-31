@extends('layouts.admin')

@php
    $title = 'تنظیم برچسب‌های ماژول مشتریان';
@endphp

@section('content')
    <div class="max-w-4xl mx-auto space-y-8 font-iranYekan animate-fadeIn"
         x-data="{
             singular: '{{ old('label_singular', $labelSingular) }}',
             plural: '{{ old('label_plural', $labelPlural) }}',
             setPreset(s, p) {
                 this.singular = s;
                 this.plural = p;
             }
         }">

        {{-- هدر و ناوبری صفحه --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-600 to-blue-700 text-white flex items-center justify-center shadow-xl shadow-indigo-600/30">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-400 dark:text-slate-500 mb-1">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-indigo-600 transition-colors">داشبورد ادمین</a>
                        <span>/</span>
                        <span>پیکربندی ماژول‌ها</span>
                        <span>/</span>
                        <span class="text-indigo-600 dark:text-indigo-400">برچسب‌های مشتریان</span>
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        تنظیم برچسب‌های ماژول مشتریان
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @if(Route::has('admin.clients.index'))
                    <a href="{{ route('admin.clients.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/80 dark:bg-slate-900/60 backdrop-blur-md border border-slate-200/80 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all text-xs font-bold shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                        <span>لیست مشتریان</span>
                    </a>
                @endif
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/80 dark:bg-slate-900/60 backdrop-blur-md border border-slate-200/80 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all text-xs font-bold shadow-sm">
                    <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>داشبورد</span>
                </a>
            </div>
        </div>

        {{-- پیام موفقیت --}}
        @if(session('success'))
            <div class="rounded-2xl bg-emerald-500/10 border border-emerald-500/20 p-4 text-emerald-700 dark:text-emerald-400 text-sm font-bold flex items-center gap-3 shadow-lg shadow-emerald-500/5">
                <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- الگوهای پیشنهادی سریع بر اساس صنف --}}
        <div class="bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 p-6 rounded-[2rem] shadow-xl shadow-slate-200/50 dark:shadow-none">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-black text-slate-800 dark:text-slate-200 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    الگوهای پیشنهادی سریع بر اساس کسب‌وکار:
                </span>
                <span class="text-[11px] text-slate-400">با کلیک روی هر الگو فیلدها خودکار پر می‌شوند</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="setPreset('مشتری', 'مشتریان')"
                        class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 text-xs font-bold text-slate-600 dark:text-slate-300 transition-all border border-slate-200/60 dark:border-slate-700/60">
                    👥 عمومی (مشتری / مشتریان)
                </button>
                <button type="button" @click="setPreset('بیمار', 'بیماران')"
                        class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 text-xs font-bold text-slate-600 dark:text-slate-300 transition-all border border-slate-200/60 dark:border-slate-700/60">
                    🩺 پزشکی و کلینیک (بیمار / بیماران)
                </button>
                <button type="button" @click="setPreset('دانش‌آموز', 'دانش‌آموزان')"
                        class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 text-xs font-bold text-slate-600 dark:text-slate-300 transition-all border border-slate-200/60 dark:border-slate-700/60">
                    🎓 آموزشی (دانش‌آموز / دانش‌آموزان)
                </button>
                <button type="button" @click="setPreset('موکل', 'موکلین')"
                        class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 text-xs font-bold text-slate-600 dark:text-slate-300 transition-all border border-slate-200/60 dark:border-slate-700/60">
                    ⚖️ وکالت و مشاوره (موکل / موکلین)
                </button>
                <button type="button" @click="setPreset('خریدار', 'خریداران')"
                        class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 text-xs font-bold text-slate-600 dark:text-slate-300 transition-all border border-slate-200/60 dark:border-slate-700/60">
                    🛍️ فروشگاهی (خریدار / خریداران)
                </button>
                <button type="button" @click="setPreset('متقاضی', 'متقاضیان')"
                        class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 text-xs font-bold text-slate-600 dark:text-slate-300 transition-all border border-slate-200/60 dark:border-slate-700/60">
                    📋 سازمانی و املاک (متقاضی / متقاضیان)
                </button>
            </div>
        </div>

        {{-- فرم تنظیمات --}}
        <form method="POST" action="{{ route('admin.clients.settings.label.update') }}" class="space-y-8 pb-20">
            @csrf
            @method('PUT')

            {{-- کارت فیلدهای ورودی --}}
            <div class="bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 dark:shadow-none">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">عناوین نمایشی ماژول</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">این عبارات در تمام بخش‌ها، منوها، فرم‌های ثبت و گزارش‌های مشتریان جایگزین می‌شوند.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                            برچسب مفرد (Singular) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="label_singular" x-model="singular"
                                   class="w-full rounded-2xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800/80 px-4 py-3 text-sm text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all font-medium"
                                   placeholder="مثلاً: مشتری، بیمار، دانش‌آموز">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-2 font-medium">
                            استفاده در عباراتی مانند: <span class="text-indigo-600 dark:text-indigo-400 font-bold" x-text="'افزودن ' + (singular || 'مشتری') + ' جدید'"></span>
                        </p>
                        @error('label_singular')
                            <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                            برچسب جمع (Plural) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="label_plural" x-model="plural"
                                   class="w-full rounded-2xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800/80 px-4 py-3 text-sm text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all font-medium"
                                   placeholder="مثلاً: مشتریان، بیماران، دانش‌آموزان">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-2 font-medium">
                            استفاده در عباراتی مانند: <span class="text-indigo-600 dark:text-indigo-400 font-bold" x-text="'لیست ' + (plural || 'مشتریان')"></span>
                        </p>
                        @error('label_plural')
                            <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- پیش‌نمایش زنده در رابط کاربری --}}
            <div class="bg-gradient-to-br from-indigo-50/50 to-blue-50/50 dark:from-slate-800/40 dark:to-slate-900/40 p-8 rounded-[2.5rem] border border-indigo-100 dark:border-slate-800">
                <h3 class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    پیش‌نمایش نحوه نمایش در سیستم
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm">
                        <div class="text-[10px] text-slate-400 font-bold mb-1">منوی اصلی</div>
                        <div class="text-xs font-black text-slate-800 dark:text-slate-200" x-text="'مدیریت ' + (plural || 'مشتریان')"></div>
                    </div>
                    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm">
                        <div class="text-[10px] text-slate-400 font-bold mb-1">دکمه ایجاد جدید</div>
                        <div class="text-xs font-black text-indigo-600 dark:text-indigo-400" x-text="'+ ایجاد ' + (singular || 'مشتری') + ' جدید'"></div>
                    </div>
                    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm">
                        <div class="text-[10px] text-slate-400 font-bold mb-1">عنوان کارت جزئیات</div>
                        <div class="text-xs font-black text-slate-800 dark:text-slate-200" x-text="'اطلاعات پرونده ' + (singular || 'مشتری')"></div>
                    </div>
                </div>
            </div>

            {{-- دکمه شناور ذخیره --}}
            <div class="sticky bottom-6 z-40 flex justify-end">
                <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl p-2 rounded-2xl border border-white dark:border-slate-800 shadow-2xl">
                    <button type="submit"
                            class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-black text-sm shadow-xl shadow-indigo-600/30 hover:shadow-indigo-600/50 hover:scale-[1.02] active:scale-95 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        <span>ذخیره تغییرات برچسب‌ها</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
