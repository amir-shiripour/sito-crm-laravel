@extends('layouts.admin')
@php($title = 'داشبورد مدیریتی')

@section('content')
    @php
        $insights = $insights ?? [
            ['label' => 'کاربران کل', 'value' => '3,482', 'change' => '+14%', 'trend' => 'up'],
            ['label' => 'نقش‌های تعریف‌شده', 'value' => '12', 'change' => '+1', 'trend' => 'up'],
            ['label' => 'ماژول‌های فعال', 'value' => '9', 'change' => '+3%', 'trend' => 'up'],
            ['label' => 'اعلان‌های مهم', 'value' => '7', 'change' => '-2', 'trend' => 'down'],
        ];

        $modulesHealth = $modulesHealth ?? [
            ['name' => 'مدیریت مشتریان', 'status' => 'active', 'uptime' => '99.9%', 'issues' => 0],
            ['name' => 'اتوماسیون فروش', 'status' => 'warning', 'uptime' => '97.8%', 'issues' => 2],
            ['name' => 'گزارش‌ساز', 'status' => 'active', 'uptime' => '99.2%', 'issues' => 1],
            ['name' => 'اتوماسیون بازاریابی', 'status' => 'maintenance', 'uptime' => '96.4%', 'issues' => 4],
        ];

        $teamPerformance = $teamPerformance ?? [
            ['team' => 'تیم فروش', 'rate' => 86, 'leader' => 'نرگس صمدی'],
            ['team' => 'تیم پشتیبانی', 'rate' => 92, 'leader' => 'امیر احمدی'],
            ['team' => 'تیم توسعه محصول', 'rate' => 78, 'leader' => 'الهام سادات'],
        ];

        $auditLog = $auditLog ?? [
            ['event' => 'ایجاد کاربر جدید «زهرا کیانی»', 'time' => '5 دقیقه قبل', 'user' => 'سیستم'],
            ['event' => 'تغییر نقش کاربر «سینا محمدی» به مدیر شعبه', 'time' => '45 دقیقه قبل', 'user' => 'مینا رفیعی'],
            ['event' => 'نصب ماژول «تحلیل کمپین»', 'time' => 'دیروز', 'user' => 'حسین مرادی'],
            ['event' => 'بازنشانی تنظیمات اعلان‌ها', 'time' => '2 روز قبل', 'user' => 'سیستم'],
        ];
        $logRoute = \Illuminate\Support\Facades\Route::has('admin.logs.index') ? route('admin.logs.index') : '#';
        $moduleRoute = \Illuminate\Support\Facades\Route::has('admin.modules.index') ? route('admin.modules.index') : '#';
        $settingsRoute = \Illuminate\Support\Facades\Route::has('admin.settings.index') ? route('admin.settings.index') : '#';
        $usersRoute = \Illuminate\Support\Facades\Route::has('admin.users.index') ? route('admin.users.index') : '#';
        $userCreateRoute = \Illuminate\Support\Facades\Route::has('admin.users.create') ? route('admin.users.create') : '#';
        $rolesRoute = \Illuminate\Support\Facades\Route::has('admin.roles.index') ? route('admin.roles.index') : '#';
    @endphp

    <div class="flex flex-col gap-6">
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($insights as $item)
                <article class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white/80 p-5 shadow-sm transition hover:-translate-y-1 hover:border-indigo-400 hover:shadow-lg dark:border-slate-700/60 dark:bg-slate-900/70">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $item['label'] }}</p>
                            <p class="mt-3 text-3xl font-bold text-slate-900 dark:text-slate-100">{{ $item['value'] }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            @if ($item['trend'] === 'up')
                                <svg class="h-4 w-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l6-6 4.5 4.5 4.5-4.5" /></svg>
                            @else
                                <svg class="h-4 w-4 text-rose-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-6 6-4.5-4.5-4.5 4.5" /></svg>
                            @endif
                            <span>{{ $item['change'] }}</span>
                        </span>
                    </div>
                    <div class="mt-6 h-16 w-full">
                        <svg viewBox="0 0 120 40" preserveAspectRatio="none" class="h-full w-full">
                            <path d="M0 32 L20 26 L40 28 L60 18 L80 24 L100 12 L120 18" fill="none" stroke="{{ $item['trend'] === 'up' ? '#10b981' : '#f43f5e' }}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2 rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm dark:border-slate-700/60 dark:bg-slate-900/70">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">تحلیل فعالیت سامانه</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">آمار ورود کاربران، نرخ موفقیت API و ظرفیت پردازش</p>
                    </div>
                    <a href="{{ $logRoute }}" class="text-xs text-indigo-500 hover:text-indigo-600">گزارش رویداد</a>
                </div>
                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl bg-indigo-500/10 p-4 text-sm text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-200">
                        <p class="text-xs text-indigo-400">میانگین لاگین روزانه</p>
                        <p class="mt-2 text-2xl font-semibold">+4.3K</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-500/10 p-4 text-sm text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-200">
                        <p class="text-xs text-emerald-400">موفقیت API</p>
                        <p class="mt-2 text-2xl font-semibold">99.2%</p>
                    </div>
                    <div class="rounded-2xl bg-amber-500/10 p-4 text-sm text-amber-600 dark:bg-amber-500/20 dark:text-amber-200">
                        <p class="text-xs text-amber-400">مصرف پردازشی</p>
                        <p class="mt-2 text-2xl font-semibold">72%</p>
                    </div>
                </div>
                <div class="mt-6 h-60 rounded-2xl bg-gradient-to-br from-slate-100 via-white to-white p-4 dark:from-slate-900 dark:via-slate-950 dark:to-slate-900">
                    <svg viewBox="0 0 120 80" preserveAspectRatio="none" class="h-full w-full">
                        <defs>
                            <linearGradient id="admin-usage" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" style="stop-color: rgba(79, 70, 229, 0.45);" />
                                <stop offset="100%" style="stop-color: rgba(79, 70, 229, 0);" />
                            </linearGradient>
                        </defs>
                        <path d="M0 70 L15 56 L30 62 L45 40 L60 32 L75 42 L90 26 L105 30 L120 18 V80 H0 Z" fill="url(#admin-usage)" />
                        <path d="M0 70 L15 56 L30 62 L45 40 L60 32 L75 42 L90 26 L105 30 L120 18" fill="none" stroke="#4f46e5" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>
            <div class="rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm dark:border-slate-700/60 dark:bg-slate-900/70">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">سلامت ماژول‌ها</h2>
                    <a href="{{ $moduleRoute }}" class="text-xs text-indigo-500 hover:text-indigo-600">مدیریت</a>
                </div>
                <ul class="mt-6 space-y-4 text-sm text-slate-600 dark:text-slate-300">
                    @foreach ($modulesHealth as $module)
                        <li class="rounded-2xl border border-slate-200/70 bg-slate-50/60 p-4 dark:border-slate-700/60 dark:bg-slate-900/60">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $module['name'] }}</p>
                                    <p class="text-xs text-slate-400">آپتایم {{ $module['uptime'] }} · {{ $module['issues'] }} مورد بحرانی</p>
                                </div>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ match ($module['status']) {
                                    'active' => 'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-200',
                                    'warning' => 'bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-200',
                                    'maintenance' => 'bg-sky-500/10 text-sky-600 dark:bg-sky-500/20 dark:text-sky-200',
                                    default => 'bg-slate-500/10 text-slate-600 dark:bg-slate-500/20 dark:text-slate-200',
                                } }}">
                                    {{ match ($module['status']) {
                                        'active' => 'فعال',
                                        'warning' => 'نیازمند توجه',
                                        'maintenance' => 'در دست بهینه‌سازی',
                                        default => 'نامشخص',
                                    } }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm dark:border-slate-700/60 dark:bg-slate-900/70">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">عملکرد تیم‌ها</h2>
                    <a href="{{ $usersRoute }}" class="text-xs text-indigo-500 hover:text-indigo-600">جزئیات اعضا</a>
                </div>
                <ul class="mt-5 space-y-4">
                    @foreach ($teamPerformance as $team)
                        <li class="rounded-2xl border border-slate-200/70 bg-slate-50/60 p-4 text-sm text-slate-600 dark:border-slate-700/60 dark:bg-slate-900/60 dark:text-slate-300">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $team['team'] }}</p>
                                    <p class="text-xs text-slate-400">سرپرست: {{ $team['leader'] }}</p>
                                </div>
                                <span class="text-sm font-bold text-indigo-500 dark:text-indigo-300">{{ $team['rate'] }}%</span>
                            </div>
                            <div class="mt-3 h-2 rounded-full bg-slate-200/80 dark:bg-slate-800">
                                <div class="h-2 rounded-full bg-gradient-to-l from-indigo-500 to-sky-500" style="width: {{ $team['rate'] }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm dark:border-slate-700/60 dark:bg-slate-900/70">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">میانبرهای مدیریتی</h2>
                    <a href="{{ $settingsRoute }}" class="text-xs text-indigo-500 hover:text-indigo-600">تنظیمات</a>
                </div>
                <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                    <a href="{{ $userCreateRoute }}" class="flex items-center gap-2 rounded-2xl border border-slate-200/70 bg-white/60 px-3 py-3 text-slate-600 transition hover:border-indigo-500 hover:text-indigo-600 dark:border-slate-700/60 dark:bg-slate-900/60 dark:text-slate-300 dark:hover:border-indigo-400 dark:hover:text-indigo-200">
                        <x-icon name="user-plus" class="h-4 w-4" />
                        <span>کاربر جدید</span>
                    </a>
                    <a href="{{ $rolesRoute }}" class="flex items-center gap-2 rounded-2xl border border-slate-200/70 bg-white/60 px-3 py-3 text-slate-600 transition hover:border-indigo-500 hover:text-indigo-600 dark:border-slate-700/60 dark:bg-slate-900/60 dark:text-slate-300 dark:hover:border-indigo-400 dark:hover:text-indigo-200">
                        <x-icon name="shield" class="h-4 w-4" />
                        <span>مدیریت نقش‌ها</span>
                    </a>
                    <a href="{{ $moduleRoute }}" class="flex items-center gap-2 rounded-2xl border border-slate-200/70 bg-white/60 px-3 py-3 text-slate-600 transition hover:border-indigo-500 hover:text-indigo-600 dark:border-slate-700/60 dark:bg-slate-900/60 dark:text-slate-300 dark:hover:border-indigo-400 dark:hover:text-indigo-200">
                        <x-icon name="cube" class="h-4 w-4" />
                        <span>ماژول‌ها</span>
                    </a>
                    <a href="{{ $logRoute }}" class="flex items-center gap-2 rounded-2xl border border-slate-200/70 bg-white/60 px-3 py-3 text-slate-600 transition hover:border-indigo-500 hover:text-indigo-600 dark:border-slate-700/60 dark:bg-slate-900/60 dark:text-slate-300 dark:hover:border-indigo-400 dark:hover:text-indigo-200">
                        <x-icon name="activity" class="h-4 w-4" />
                        <span>لاگ‌ها و مانیتورینگ</span>
                    </a>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm dark:border-slate-700/60 dark:bg-slate-900/70">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">گزارش فعالیت امنیتی</h2>
                    <a href="{{ $logRoute }}" class="text-xs text-indigo-500 hover:text-indigo-600">تاریخچه کامل</a>
                </div>
                <ul class="mt-5 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                    @foreach ($auditLog as $log)
                        <li class="rounded-2xl border border-slate-200/70 bg-slate-50/60 p-4 dark:border-slate-700/60 dark:bg-slate-900/60">
                            <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $log['event'] }}</p>
                            <div class="mt-1 flex items-center justify-between text-xs text-slate-400">
                                <span>{{ $log['time'] }}</span>
                                <span>توسط {{ $log['user'] }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    </div>
@endsection
