@extends('layouts.admin')

@section('content')
    <div class="py-10 bg-[#f8fafc] dark:bg-[#070a13] min-h-screen transition-colors duration-500 font-iranYekan">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- هدر و دکمه‌ها --}}
            <div class="mb-10 flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        مدیریت <span class="text-indigo-600 dark:text-indigo-400">نسخه‌ها و استقرار</span>
                    </h1>
                    <p class="mt-2 text-slate-500 dark:text-slate-400 font-medium">وضعیت پایداری هسته CRM و همگام‌سازی با مخزن گیت‌هاب</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.version-control.check-remote') }}" class="px-5 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-sm font-bold rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        بررسی مخزن آنلاین
                    </a>
                    <a href="{{ route('admin.version-control.create') }}" class="px-6 py-3 bg-indigo-600 text-white text-sm font-bold rounded-2xl hover:bg-indigo-700 shadow-xl shadow-indigo-600/20 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        ثبت ورژن دستی
                    </a>
                </div>
            </div>

            {{-- بخش وضعیت آنلاین (Advanced Update Panel) --}}
            @php $remote = session('remote_version_info'); @endphp
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                {{-- کارت نسخه محلی --}}
                <div class="bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 p-6 rounded-[2rem] shadow-xl shadow-slate-200/50 dark:shadow-none">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="p-3 bg-indigo-100 dark:bg-indigo-500/10 rounded-2xl text-indigo-600 dark:text-indigo-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12l4-4m-4 4l4 4"/></svg>
                        </div>
                        <h3 class="font-bold text-slate-800 dark:text-slate-200">نسخه نصب شده</h3>
                    </div>
                    <div class="text-3xl font-black text-slate-900 dark:text-white font-mono">
                        v{{ $currentLocal ? $currentLocal->version_number : '1.0.0' }}
                    </div>
                    <p class="mt-2 text-xs text-slate-500">آخرین ثبت داخلی: {{ $currentLocal ? verta($currentLocal->release_date)->format('Y/m/d') : 'نامشخص' }}</p>
                </div>

                {{-- کارت وضعیت گیت‌هاب --}}
                <div class="md:col-span-2 bg-gradient-to-br from-slate-900 to-slate-800 dark:from-indigo-950/40 dark:to-slate-900 p-8 rounded-[2rem] text-white relative overflow-hidden group shadow-2xl">
                    <div class="absolute -right-10 -top-10 opacity-10 group-hover:scale-110 transition-transform duration-1000">
                        <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
                    </div>

                    <div class="relative z-10 flex flex-col md:flex-row justify-between gap-6">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                <span class="text-xs font-bold uppercase tracking-widest text-indigo-300">GitHub Repository Status</span>
                            </div>
                            @if($remote)
                                <h2 class="text-2xl font-black">نسخه موجود در Branch: <span class="text-emerald-400">main</span></h2>
                                <p class="mt-2 text-sm text-slate-300 leading-relaxed max-w-md">
                                    آخرین تغییر: <span class="font-mono text-white">{{ Str::limit($remote['commit']['commit']['message'], 60) }}</span>
                                </p>
                            @else
                                <h2 class="text-2xl font-black">عدم اتصال به مخزن آنلاین</h2>
                                <p class="mt-2 text-sm text-slate-300">برای بررسی وجود آپدیت‌های جدید، روی دکمه بررسی کلیک کنید.</p>
                            @endif
                        </div>

                        @if($remote)
                            <div class="flex items-center">
                                <form action="{{ route('admin.version-control.deploy') }}" method="POST">
                                    @csrf
                                    <button type="submit" onclick="return confirm('هشدار: سیستم برای دقایقی در حالت تعمیرات قرار می‌گیرد. ادامه می‌دهید؟')"
                                            class="px-8 py-4 bg-emerald-500 hover:bg-emerald-600 text-white font-black rounded-2xl shadow-2xl shadow-emerald-500/40 transition-all active:scale-95 flex items-center gap-2">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16.5V21m0 0l-3-3m3 3l3-3M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        نصب و بروزرسانی هوشمند
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- لیست نسخه‌ها --}}
            <div class="bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 rounded-[2.5rem] overflow-hidden shadow-2xl shadow-slate-200/50 dark:shadow-none">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                    <tr>
                        <th class="px-8 py-5 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">اطلاعات انتشار</th>
                        <th class="px-8 py-5 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">تاریخ</th>
                        <th class="px-8 py-5 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">وضعیت</th>
                        <th class="px-8 py-5 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">مدیریت</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($versions as $version)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-mono font-black text-sm border border-indigo-100 dark:border-indigo-500/20">
                                        v{{ $version->version_number }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-slate-900 dark:text-white">{{ $version->title ?? 'نسخه سیستمی' }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-1 max-w-md">{{ $version->summary }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-sm text-slate-600 dark:text-slate-300 font-medium">
                                {{ verta($version->release_date)->format('Y/m/d') }}
                            </td>
                            <td class="px-8 py-6">
                                @if($version->is_current)
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-xl text-[10px] font-black bg-emerald-100 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 ml-2 animate-pulse"></span>
                                        LIVE ON PRODUCTION
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-xl text-[10px] font-black bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                        ARCHIVED
                                    </span>
                                @endif
                            </td>
                            <td class="px-8 py-6 text-center">
                                <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('admin.version-control.edit', $version) }}" class="p-2 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </a>
                                    <form action="{{ route('admin.version-control.destroy', $version) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-400 hover:text-red-500 transition-colors" onclick="return confirm('حذف شود؟')">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        {{-- ردیف خالی --}}
                    @endforelse
                    </tbody>
                </table>
                <div class="px-8 py-5 bg-slate-50/30 dark:bg-slate-950/30">
                    {{ $versions->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
