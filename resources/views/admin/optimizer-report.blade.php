@extends('layouts.admin')

@php($title = 'گزارش بهینه‌ساز تصاویر')

@section('content')
    <div class="animate-fadeIn font-iranYekan" x-data="{ showSpinner: false }">
        {{-- Page Header & Manual Run Button --}}
        <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                    گزارش <span class="text-indigo-600 dark:text-indigo-400">بهینه‌ساز تصاویر</span>
                </h1>
                <p class="mt-2 text-slate-500 dark:text-slate-400 font-medium text-sm">آمار و وضعیت فایل‌های بهینه‌ شده در سیستم</p>
            </div>
            <div>
                <form action="{{ route('admin.optimizer.run') }}" method="POST" onsubmit="showSpinner = true">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-3 px-6 py-3 bg-indigo-600 text-white font-black rounded-2xl shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 active:scale-95 transition-all duration-300">
                        <svg x-show="showSpinner" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg x-show="!showSpinner" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        <span x-text="showSpinner ? 'در حال پردازش...' : 'بهینه‌سازی دستی تصاویر قدیمی'"></span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Overall Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="group bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 p-6 rounded-[2rem] shadow-xl shadow-slate-200/50 dark:shadow-none">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-indigo-50 dark:bg-indigo-500/10 rounded-2xl text-indigo-600 dark:text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l-1-1m6-5l-1.5-1.5a2.25 2.25 0 00-3.182 0L12 12M9 3l-1.5 1.5a2.25 2.25 0 000 3.182L12 12m3-9l-1.5 1.5a2.25 2.25 0 000 3.182L12 12" /></svg>
                    </div>
                </div>
                <div class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">کل فایل‌های بررسی شده</div>
                <div class="text-3xl font-black text-slate-900 dark:text-white font-mono leading-none">{{ number_format($total_files) }}</div>
            </div>
            <div class="group bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 p-6 rounded-[2rem] shadow-xl shadow-slate-200/50 dark:shadow-none">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-500/10 rounded-2xl text-emerald-600 dark:text-emerald-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>
                <div class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">حجم کل بهینه شده</div>
                <div class="text-3xl font-black text-slate-900 dark:text-white font-mono leading-none">{{ \App\Helpers\FileSizeHelper::humanFilesize($total_optimized_size) }}</div>
            </div>
             <div class="group bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 p-6 rounded-[2rem] shadow-xl shadow-slate-200/50 dark:shadow-none">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-amber-50 dark:bg-amber-500/10 rounded-2xl text-amber-600 dark:text-amber-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>
                </div>
                <div class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">فضای ذخیره شده (تخمینی)</div>
                <div class="text-3xl font-black text-slate-900 dark:text-white font-mono leading-none">{{ \App\Helpers\FileSizeHelper::humanFilesize($total_saved) }}</div>
            </div>
        </div>

        {{-- Main Table Container --}}
        <div class="bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden transition-all duration-300">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                        <tr>
                            <th class="px-8 py-5 text-right text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">پوشه</th>
                            <th class="px-8 py-5 text-center text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">تعداد فایل</th>
                            <th class="px-8 py-5 text-center text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">فایل‌های WebP</th>
                            <th class="px-8 py-5 text-center text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">حجم بهینه شده</th>
                            <th class="px-8 py-5 text-center text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">فضای ذخیره شده</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($stats as $dir => $data)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all group">
                                <td class="px-8 py-6">
                                    <div class="text-sm font-bold text-slate-700 dark:text-slate-300 font-mono">{{ $dir }}</div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <div class="text-sm font-mono font-bold text-slate-600 dark:text-slate-400">{{ number_format($data['file_count']) }}</div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <div class="inline-flex items-center gap-2">
                                        <span class="text-sm font-mono font-bold text-slate-600 dark:text-slate-400">{{ number_format($data['optimized_count']) }}</span>
                                        <span class="text-[10px] font-black {{ $data['file_count'] > 0 && ($data['optimized_count'] / $data['file_count']) > 0.5 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                            ({{ $data['file_count'] > 0 ? round(($data['optimized_count'] / $data['file_count']) * 100) : 0 }}%)
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <div class="text-sm font-mono font-bold text-slate-600 dark:text-slate-400">{{ \App\Helpers\FileSizeHelper::humanFilesize($data['optimized_size']) }}</div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <div class="inline-flex px-3 py-1 bg-emerald-50 dark:bg-emerald-500/10 rounded-xl text-emerald-600 dark:text-emerald-400 font-mono font-bold text-xs border border-emerald-100 dark:border-emerald-500/20">
                                        {{ \App\Helpers\FileSizeHelper::humanFilesize($data['saved_space']) }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-10 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-slate-300 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="mt-4 text-sm font-bold text-slate-500 dark:text-slate-400">هیچ فایلی برای گزارش‌گیری یافت نشد.</p>
                                        <p class="text-xs text-slate-400 dark:text-slate-500">پوشه‌های آپلود خالی هستند.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn {
            animation: fadeIn 0.4s ease-out forwards;
        }
    </style>
@endsection
