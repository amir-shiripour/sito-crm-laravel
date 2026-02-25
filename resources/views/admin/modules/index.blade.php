@extends('layouts.admin')

@php($title = 'مدیریت هوشمند ماژول‌ها')

@section('content')
    <div class="animate-fadeIn font-iranYekan" x-data="{ openUpdateModal: false, activeModule: '', activeModuleName: '' }">
        {{-- Page Header --}}
        <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                    کنترل <span class="text-indigo-600 dark:text-indigo-400">ماژول‌ها</span>
                </h1>
                <p class="mt-2 text-slate-500 dark:text-slate-400 font-medium text-sm">مدیریت نسخه‌ها و به‌روزرسانی امن اجزای Sito CRM</p>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 bg-white/50 dark:bg-slate-900/30 backdrop-blur-sm rounded-2xl border border-slate-200/50 dark:border-slate-800/50">
                <span class="flex h-2 w-2 rounded-full bg-indigo-500 animate-pulse"></span>
                <span class="text-xs font-bold text-slate-600 dark:text-slate-400">تعداد کل: {{ count($dbModules) }} ماژول</span>
            </div>
        </div>

        {{-- Main Container --}}
        <div class="bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden transition-all duration-300">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                    <tr>
                        <th class="px-8 py-5 text-right text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">اطلاعات ماژول</th>
                        <th class="px-8 py-5 text-center text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">نسخه فعلی</th>
                        <th class="px-8 py-5 text-right text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">وضعیت</th>
                        <th class="px-8 py-5 text-center text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">عملیات مدیریت</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($dbModules as $m)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all group">
                            {{-- Module Info --}}
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-lg border border-indigo-100 dark:border-indigo-500/20 shadow-sm">
                                        {{ substr($m->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-slate-900 dark:text-white group-hover:text-indigo-600 transition-colors">{{ $m->name }}</div>
                                        <div class="text-[10px] font-mono font-bold text-slate-400 dark:text-slate-500 mt-1 uppercase tracking-tighter">{{ $m->slug }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Version Display --}}
                            <td class="px-8 py-6 text-center">
                                <div class="inline-flex px-3 py-1 bg-indigo-50 dark:bg-indigo-500/10 rounded-xl text-indigo-600 dark:text-indigo-400 font-mono font-bold text-xs border border-indigo-100 dark:border-indigo-500/20">
                                    v{{ $m->version ?? '1.0.0' }}
                                </div>
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-8 py-6 text-right">
                                @if(!$m->installed)
                                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-[10px] font-black bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">آماده نصب</span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-[10px] font-black {{ $m->active ? 'bg-emerald-100 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400' : 'bg-rose-100 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400' }} border transition-all">
                                        {{ $m->active ? 'فعال' : 'غیرفعال' }}
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-8 py-6 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if($m->installed)
                                        {{-- Update Package Button --}}
                                        <button @click="openUpdateModal = true; activeModule = '{{ $m->slug }}'; activeModuleName = '{{ $m->name }}'"
                                                class="p-2.5 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-xl hover:bg-indigo-600 hover:text-white transition-all active:scale-90" title="به‌روزرسانی پکیج">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                                        </button>

                                        {{-- Enable/Disable --}}
                                        <form method="POST" action="{{ $m->active ? route('admin.modules.disable') : route('admin.modules.enable') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="slug" value="{{ $m->slug }}">
                                            <button title="{{ $m->active ? 'غیرفعال‌سازی' : 'فعال‌سازی' }}" class="p-2.5 {{ $m->active ? 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' }} rounded-xl hover:scale-110 transition-all">
                                                @if($m->active)
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                @else
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                @endif
                                            </button>
                                        </form>

                                        {{-- Reset --}}
                                        <form method="POST" action="{{ route('admin.modules.reset') }}" class="inline" onsubmit="return confirm('عملیات ریست باعث حذف داده‌ها و اجرای Seeder می‌شود. ادامه می‌دهید؟');">
                                            @csrf
                                            <input type="hidden" name="slug" value="{{ $m->slug }}">
                                            <button title="بازنشانی" class="p-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-xl hover:bg-slate-600 hover:text-white transition-all">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            </button>
                                        </form>

                                        {{-- Delete --}}
                                        <form method="POST" action="{{ route('admin.modules.uninstall') }}" class="inline" onsubmit="return confirm('حذف کامل ماژول و فایل‌ها؟');">
                                            @csrf
                                            <input type="hidden" name="slug" value="{{ $m->slug }}">
                                            <button title="حذف ماژول" class="p-2.5 bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-xl hover:bg-rose-600 hover:text-white transition-all">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.modules.install') }}">
                                            @csrf
                                            <input type="hidden" name="slug" value="{{ $m->slug }}">
                                            <button class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black rounded-xl shadow-lg transition-all active:scale-95">نصب اولیه</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Update Modal (Glassmorphism) --}}
        <div x-show="openUpdateModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6">
            <div x-show="openUpdateModal" x-transition.opacity @click="openUpdateModal = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            <div x-show="openUpdateModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative w-full max-w-lg bg-white/90 dark:bg-slate-900/90 backdrop-blur-2xl border border-white/20 dark:border-slate-800 rounded-[2.5rem] shadow-2xl overflow-hidden p-10 text-right">

                <div class="mb-8">
                    <h3 class="text-xl font-black text-slate-900 dark:text-white flex items-center gap-3">
                        <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        بروزرسانی ماژول: <span x-text="activeModuleName" class="text-indigo-600 dark:text-indigo-400 font-black"></span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-bold">فایل ZIP جدید را انتخاب کنید. سیستم به صورت خودکار فایل‌ها را جایگزین و دیتابیس را به‌روزرسانی می‌کند.</p>
                </div>

                <form action="{{ route('admin.modules.update-package') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    <input type="hidden" name="slug" :value="activeModule">

                    <div class="group relative w-full h-44 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-[2rem] flex flex-col items-center justify-center transition-all hover:border-indigo-500 bg-slate-50/50 dark:bg-slate-800/30">
                        <input type="file" name="module_zip" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="p-4 bg-white dark:bg-slate-800 rounded-2xl shadow-sm mb-3 group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                        </div>
                        <span class="text-xs font-black text-slate-500 dark:text-slate-400 tracking-tight">کلیک کنید یا فایل ZIP را اینجا رها کنید</span>
                    </div>

                    <div class="flex items-center justify-start gap-3">
                        <button type="submit" class="px-10 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-2xl shadow-xl shadow-indigo-600/20 active:scale-95 transition-all">تایید و شروع آپدیت امن</button>
                        <button type="button" @click="openUpdateModal = false" class="px-6 py-3.5 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors">انصراف</button>
                    </div>
                </form>
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
        [x-cloak] { display: none !important; }
    </style>
@endsection
