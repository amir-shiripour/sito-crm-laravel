@extends('layouts.admin')

@php
    $title = 'تنظیم برچسب‌های ماژول نوبت‌دهی';
@endphp

@section('content')
    <div class="max-w-4xl mx-auto space-y-8 font-iranYekan animate-fadeIn"
         x-data="{
             provider: '{{ old('label_provider', $labelProvider) }}',
             providers: '{{ old('label_providers', $labelProviders) }}',
             service: '{{ old('label_service', $labelService) }}',
             services: '{{ old('label_services', $labelServices) }}',
             setPreset(p, ps, s, ss) {
                 this.provider = p;
                 this.providers = ps;
                 this.service = s;
                 this.services = ss;
             }
         }">

        {{-- هدر و ناوبری صفحه --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-700 text-white flex items-center justify-center shadow-xl shadow-violet-600/30">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-400 dark:text-slate-500 mb-1">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-indigo-600 transition-colors">داشبورد ادمین</a>
                        <span>/</span>
                        <span>پیکربندی ماژول‌ها</span>
                        <span>/</span>
                        <span class="text-violet-600 dark:text-violet-400">برچسب‌های نوبت‌دهی</span>
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        تنظیم برچسب‌های ماژول نوبت‌دهی
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white/80 dark:bg-slate-900/60 backdrop-blur-md border border-slate-200/80 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all text-xs font-bold shadow-sm">
                    <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>بازگشت به داشبورد</span>
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
                    <svg class="w-4 h-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    الگوهای پیشنهادی سریع بر اساس صنف و کسب‌وکار:
                </span>
                <span class="text-[11px] text-slate-400">با کلیک روی هر الگو عناوین ارائه‌دهنده و سرویس همزمان پر می‌شوند</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="setPreset('متخصص', 'متخصصین', 'لاین', 'لاین‌ها')"
                        class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-violet-600 hover:text-white dark:hover:bg-violet-600 text-xs font-bold text-slate-700 dark:text-slate-200 transition-all border border-slate-200/60 dark:border-slate-700/60">
                    ✂️ زیبایی و سالن (متخصص / متخصصین — لاین / لاین‌ها)
                </button>
                <button type="button" @click="setPreset('پزشک', 'پزشکان', 'خدمت', 'خدمات')"
                        class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-violet-600 hover:text-white dark:hover:bg-violet-600 text-xs font-bold text-slate-700 dark:text-slate-200 transition-all border border-slate-200/60 dark:border-slate-700/60">
                    🩺 پزشکی و کلینیک (پزشک / پزشکان — خدمت / خدمات)
                </button>
                <button type="button" @click="setPreset('کارشناس', 'کارشناسان', 'آزمایش', 'آزمایش‌ها')"
                        class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-violet-600 hover:text-white dark:hover:bg-violet-600 text-xs font-bold text-slate-700 dark:text-slate-200 transition-all border border-slate-200/60 dark:border-slate-700/60">
                    🔬 آزمایشگاه و تصویربرداری (کارشناس — آزمایش / آزمایش‌ها)
                </button>
                <button type="button" @click="setPreset('مشاور', 'مشاوران', 'جلسه', 'جلسات')"
                        class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-violet-600 hover:text-white dark:hover:bg-violet-600 text-xs font-bold text-slate-700 dark:text-slate-200 transition-all border border-slate-200/60 dark:border-slate-700/60">
                    💼 مشاوره و روانشناسی (مشاور — جلسه / جلسات)
                </button>
                <button type="button" @click="setPreset('وکیل', 'وکلا', 'پرونده', 'پرونده‌ها')"
                        class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-violet-600 hover:text-white dark:hover:bg-violet-600 text-xs font-bold text-slate-700 dark:text-slate-200 transition-all border border-slate-200/60 dark:border-slate-700/60">
                    ⚖️ حقوقی و وکالت (وکیل / وکلا — پرونده / پرونده‌ها)
                </button>
                <button type="button" @click="setPreset('استاد', 'اساتید', 'کلاس', 'کلاس‌ها')"
                        class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-violet-600 hover:text-white dark:hover:bg-violet-600 text-xs font-bold text-slate-700 dark:text-slate-200 transition-all border border-slate-200/60 dark:border-slate-700/60">
                    🎓 آموزش و تدریس (استاد / اساتید — کلاس / کلاس‌ها)
                </button>
                <button type="button" @click="setPreset('ارائه‌دهنده', 'ارائه‌دهندگان', 'سرویس', 'سرویس‌ها')"
                        class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-violet-600 hover:text-white dark:hover:bg-violet-600 text-xs font-bold text-slate-700 dark:text-slate-200 transition-all border border-slate-200/60 dark:border-slate-700/60">
                    🔄 عمومی سیستم (ارائه‌دهنده — سرویس / سرویس‌ها)
                </button>
            </div>
        </div>

        {{-- فرم تنظیمات --}}
        <form method="POST" action="{{ route('admin.booking.settings.label.update') }}" class="space-y-8 pb-20">
            @csrf
            @method('PUT')

            {{-- کارت فیلدهای ارائه‌دهندگان --}}
            <div class="bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 dark:shadow-none">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-violet-50 dark:bg-violet-500/10 text-violet-600 dark:text-violet-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">عناوین ارائه‌دهندگان خدمات</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">این عبارات جایگزین کلمه "ارائه‌دهنده" در تقویم، فرم‌های رزرو و لیست پرسنل خواهند شد.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                            برچسب مفرد ارائه‌دهنده (Singular Provider) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="label_provider" x-model="provider"
                                   class="w-full rounded-2xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800/80 px-4 py-3 text-sm text-slate-900 dark:text-white focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 transition-all font-medium"
                                   placeholder="مثلاً: پزشک، متخصص، مشاور، وکیل">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-2 font-medium">
                            استفاده در عباراتی مانند: <span class="text-violet-600 dark:text-violet-400 font-bold" x-text="'انتخاب ' + (provider || 'ارائه‌دهنده')"></span>
                        </p>
                        @error('label_provider')
                            <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                            برچسب جمع ارائه‌دهندگان (Plural Providers) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="label_providers" x-model="providers"
                                   class="w-full rounded-2xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800/80 px-4 py-3 text-sm text-slate-900 dark:text-white focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 transition-all font-medium"
                                   placeholder="مثلاً: پزشکان، متخصصین، مشاوران، وکلا">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-2 font-medium">
                            استفاده در عباراتی مانند: <span class="text-violet-600 dark:text-violet-400 font-bold" x-text="'لیست ' + (providers || 'ارائه‌دهندگان')"></span>
                        </p>
                        @error('label_providers')
                            <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- کارت فیلدهای سرویس‌ها و خدمات --}}
            <div class="bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 dark:shadow-none">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">عناوین سرویس‌ها و خدمات (لاین، خدمت، آزمایش و...)</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">این عبارات جایگزین کلمه "سرویس" در لیست خدمات، دسته‌بندی‌ها، تعرفه‌ها و مراحل رزرو خواهند شد.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                            برچسب مفرد سرویس (Singular Service) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="label_service" x-model="service"
                                   class="w-full rounded-2xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800/80 px-4 py-3 text-sm text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all font-medium"
                                   placeholder="مثلاً: لاین، خدمت، آزمایش، جلسه، کلاس">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-2 font-medium">
                            استفاده در عباراتی مانند: <span class="text-indigo-600 dark:text-indigo-400 font-bold" x-text="'ایجاد ' + (service || 'سرویس') + ' جدید'"></span>
                        </p>
                        @error('label_service')
                            <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                            برچسب جمع سرویس‌ها (Plural Services) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="label_services" x-model="services"
                                   class="w-full rounded-2xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800/80 px-4 py-3 text-sm text-slate-900 dark:text-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all font-medium"
                                   placeholder="مثلاً: لاین‌ها، خدمات، آزمایش‌ها، جلسات، کلاس‌ها">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-2 font-medium">
                            استفاده در عباراتی مانند: <span class="text-indigo-600 dark:text-indigo-400 font-bold" x-text="'لیست ' + (services || 'سرویس‌ها')"></span>
                        </p>
                        @error('label_services')
                            <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- پیش‌نمایش زنده در رابط کاربری --}}
            <div class="bg-gradient-to-br from-violet-50/50 to-indigo-50/50 dark:from-slate-800/40 dark:to-slate-900/40 p-8 rounded-[2.5rem] border border-violet-100 dark:border-slate-800">
                <h3 class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    پیش‌نمایش نحوه نمایش همزمان در رابط کاربری
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm">
                        <div class="text-[10px] text-slate-400 font-bold mb-1">منوی ناوبری ارائه‌دهندگان</div>
                        <div class="text-xs font-black text-slate-800 dark:text-slate-200" x-text="'مدیریت ' + (providers || 'ارائه‌دهندگان')"></div>
                    </div>
                    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm">
                        <div class="text-[10px] text-slate-400 font-bold mb-1">منوی ناوبری سرویس‌ها</div>
                        <div class="text-xs font-black text-slate-800 dark:text-slate-200" x-text="'مدیریت ' + (services || 'سرویس‌ها')"></div>
                    </div>
                    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm">
                        <div class="text-[10px] text-slate-400 font-bold mb-1">فرایند رزرو نوبت</div>
                        <div class="text-xs font-black text-violet-600 dark:text-violet-400" x-text="'انتخاب ' + (service || 'سرویس') + ' و تعیین ' + (provider || 'ارائه‌دهنده')"></div>
                    </div>
                </div>
            </div>

            {{-- دکمه شناور ذخیره --}}
            <div class="sticky bottom-6 z-40 flex justify-end">
                <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl p-2 rounded-2xl border border-white dark:border-slate-800 shadow-2xl">
                    <button type="submit"
                            class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 text-white font-black text-sm shadow-xl shadow-violet-600/30 hover:shadow-violet-600/50 hover:scale-[1.02] active:scale-95 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        <span>ذخیره تغییرات برچسب‌ها</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
