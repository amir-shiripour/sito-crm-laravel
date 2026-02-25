@extends('layouts.admin')

@php($title = 'پنل مدیریت هسته')

@section('content')
    <div class="animate-fadeIn font-iranYekan">
        {{-- Welcome Header --}}
        <div class="mb-10">
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                خوش آمدید، <span class="text-indigo-600 dark:text-indigo-400">{{ Auth::user()->name }}</span>
            </h1>
            <p class="mt-2 text-slate-500 dark:text-slate-400 font-medium text-sm">نمای کلی از وضعیت فعلی زیرساخت و هسته مرکزی Sito CRM</p>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Total Users Card --}}
            <div class="group bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 p-6 rounded-[2rem] shadow-xl shadow-slate-200/50 dark:shadow-none hover:scale-[1.02] transition-all duration-300">
                <div class="flex items-center justify-between mb-4 text-right">
                    <div class="p-3 bg-indigo-50 dark:bg-indigo-500/10 rounded-2xl text-indigo-600 dark:text-indigo-400 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                    <span class="text-[10px] font-black text-emerald-500 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 rounded-lg" dir="ltr">+12%</span>
                </div>
                <div class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">کاربران کل هسته</div>
                <div class="text-3xl font-black text-slate-900 dark:text-white font-mono leading-none">1,248</div>
            </div>

            {{-- Roles Card --}}
            <div class="group bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 p-6 rounded-[2rem] shadow-xl shadow-slate-200/50 dark:shadow-none hover:scale-[1.02] transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-amber-50 dark:bg-amber-500/10 rounded-2xl text-amber-600 dark:text-amber-400 group-hover:bg-amber-500 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    </div>
                </div>
                <div class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">نقش‌های تعریف شده</div>
                <div class="text-3xl font-black text-slate-900 dark:text-white font-mono leading-none">4</div>
            </div>

            {{-- Active Modules Card --}}
            <div class="group bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 p-6 rounded-[2rem] shadow-xl shadow-slate-200/50 dark:shadow-none hover:scale-[1.02] transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-500/10 rounded-2xl text-emerald-600 dark:text-emerald-400 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <div class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                </div>
                <div class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">ماژول‌های فعال</div>
                <div class="text-3xl font-black text-slate-900 dark:text-white font-mono leading-none">3</div>
            </div>

            {{-- Alerts Card --}}
            <div class="group bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 p-6 rounded-[2rem] shadow-xl shadow-slate-200/50 dark:shadow-none hover:scale-[1.02] transition-all duration-300">
                <div class="flex items-center justify-between mb-4 text-right">
                    <div class="p-3 bg-rose-50 dark:bg-rose-500/10 rounded-2xl text-rose-600 dark:text-rose-400 group-hover:bg-rose-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    </div>
                    <span class="text-[10px] font-black text-rose-500 bg-rose-50 dark:bg-rose-500/10 px-2 py-1 rounded-lg">فوری</span>
                </div>
                <div class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">اعلان‌های سیستمی</div>
                <div class="text-3xl font-black text-slate-900 dark:text-white font-mono leading-none">7</div>
            </div>
        </div>

        <div class="mt-10 grid grid-cols-1 xl:grid-cols-3 gap-8 text-right">
            {{-- Activity Section --}}
            <div class="xl:col-span-2 bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 dark:shadow-none transition-all">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-3">
                        <div class="w-1.5 h-6 bg-indigo-600 dark:bg-indigo-400 rounded-full"></div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">آخرین فعالیت‌های هسته</h3>
                    </div>
                    <a href="#" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">مشاهده تاریخچه کامل</a>
                </div>

                <div class="space-y-6">
                    {{-- Example Item 1 --}}
                    <div class="flex gap-4 relative">
                        <div class="absolute right-[11px] top-8 bottom-[-24px] w-0.5 bg-slate-100 dark:bg-slate-800"></div>
                        <div class="flex-shrink-0 w-6 h-6 rounded-full border-4 border-white dark:border-slate-900 bg-indigo-500 z-10"></div>
                        <div>
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-200">به‌روزرسانی ماژول پیامک به نسخه 2.1.0</p>
                            <p class="text-xs text-slate-500 mt-1">توسط سیستم هوشمند • ۵ دقیقه پیش</p>
                        </div>
                    </div>
                    {{-- Example Item 2 --}}
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full border-4 border-white dark:border-slate-900 bg-emerald-500 z-10"></div>
                        <div>
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-200">ورود موفق مدیر سیستم (Super Admin)</p>
                            <p class="text-xs text-slate-500 mt-1">آی‌پی: 192.168.1.1 • ۱ ساعت پیش</p>
                        </div>
                    </div>
                    <div class="py-10 text-center">
                        <p class="text-xs text-slate-400 dark:text-slate-500 italic">محتوای فعالیتی برای نمایش بیشتر یافت نشد.</p>
                    </div>
                </div>
            </div>

            {{-- Shortcuts & Quick Actions --}}
            <div class="space-y-8">
                <div class="bg-white/70 dark:bg-slate-900/50 backdrop-blur-xl border border-white dark:border-slate-800 p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 dark:shadow-none">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white mb-6">دسترسی سریع</h3>
                    <div class="grid grid-cols-1 gap-3">
                        {{-- User Dashboard Shortcut (NEW) --}}
                        <a href="{{ route('user.dashboard') }}" class="group flex items-center justify-between p-4 bg-emerald-50 dark:bg-emerald-500/5 rounded-2xl border border-emerald-100 dark:border-emerald-500/10 hover:bg-emerald-600 transition-all duration-300">
                            <span class="text-sm font-bold text-emerald-700 dark:text-emerald-400 group-hover:text-white transition-colors">داشبورد کاربری (User)</span>
                            <svg class="w-5 h-5 text-emerald-500 group-hover:text-white transition-colors rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4-4m4-4H3"/></svg>
                        </a>

                        <a href="{{ route('admin.users.index') }}" class="group flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-700 hover:bg-indigo-600 transition-all duration-300">
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200 group-hover:text-white transition-colors">مدیریت کاربران</span>
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-white transition-colors rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4-4m4-4H3"/></svg>
                        </a>
                        <a href="{{ route('admin.roles.index') }}" class="group flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-700 hover:bg-indigo-600 transition-all duration-300">
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200 group-hover:text-white transition-colors">نقش‌ها و دسترسی</span>
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-white transition-colors rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                        <a href="{{ route('admin.modules.index') }}" class="group flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-700 hover:bg-indigo-600 transition-all duration-300">
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200 group-hover:text-white transition-colors">ماژول‌های نصب شده</span>
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-white transition-colors rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                        <a href="{{ route('admin.version-control.index') }}" class="group flex items-center justify-between p-4 bg-indigo-50 dark:bg-indigo-500/5 rounded-2xl border border-indigo-100 dark:border-indigo-500/10 hover:bg-indigo-600 transition-all duration-300">
                            <span class="text-sm font-bold text-indigo-700 dark:text-indigo-400 group-hover:text-white transition-colors">کنترل نسخه (Git)</span>
                            <svg class="w-5 h-5 text-indigo-500 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </a>
                    </div>
                </div>

                {{-- System Health --}}
                <div class="bg-gradient-to-br from-indigo-900 to-slate-900 rounded-[2.5rem] p-8 text-white relative overflow-hidden group shadow-2xl">
                    <div class="absolute -right-10 -bottom-10 opacity-10 group-hover:scale-110 transition-transform duration-700 text-right">
                        <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div class="relative z-10 text-right">
                        <h4 class="text-lg font-black mb-2">وضعیت سرور</h4>
                        <div class="flex items-center justify-end gap-2 mb-4 text-xs font-bold text-emerald-400">
                            تمامی سرویس‌ها فعال هستند
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        </div>
                        <p class="text-[10px] text-slate-400 leading-relaxed font-bold">
                            آخرین بررسی خودکار پایداری سیستم در تاریخ
                            <span class="font-mono" dir="ltr">
                            {{ function_exists('verta') ? verta()->format('Y/m/d H:i') : now()->format('Y/m/d H:i') }}
                        </span>
                            انجام شد.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
@endsection
