<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-widest text-slate-400">{{ __('Welcome back') }}</p>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ __('داشبورد شخصی') }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">مرکز کنترل فعالیت‌های روزانه و پیگیری تعاملات با مشتریان.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('profile.show') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/70 px-4 py-2 text-sm font-medium text-slate-600 transition hover:border-indigo-500 hover:text-indigo-600 dark:border-slate-700/60 dark:text-slate-200 dark:hover:border-indigo-400 dark:hover:text-indigo-200">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 19.5a7.5 7.5 0 0115 0v.75H4.5v-.75z" /></svg>
                    <span>تنظیمات پروفایل</span>
                </a>
                <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : route('dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-indigo-500 to-sky-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:from-indigo-600 hover:to-sky-600">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75l3.75 3.75L7.5 20.25H3.75V16.5L16.5 3.75z" /></svg>
                    <span>میانبر مدیریت</span>
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $metrics = [
            ['title' => 'مشتریان فعال', 'value' => '1,248', 'change' => '+12%', 'trend' => 'up'],
            ['title' => 'تیکت‌های باز', 'value' => '37', 'change' => '-8%', 'trend' => 'down'],
            ['title' => 'میانگین رضایت', 'value' => '4.7/5', 'change' => '+0.4', 'trend' => 'up'],
            ['title' => 'جلسات امروز', 'value' => '5', 'change' => '+2', 'trend' => 'up'],
        ];

        $pipeline = [
            ['stage' => 'سرنخ جدید', 'count' => 56, 'value' => '125M', 'color' => 'from-emerald-400 to-emerald-600'],
            ['stage' => 'در حال پیگیری', 'count' => 34, 'value' => '98M', 'color' => 'from-sky-400 to-sky-600'],
            ['stage' => 'در انتظار قرارداد', 'count' => 12, 'value' => '43M', 'color' => 'from-amber-400 to-amber-600'],
            ['stage' => 'بسته شده', 'count' => 22, 'value' => '210M', 'color' => 'from-indigo-400 to-indigo-600'],
        ];

        $activities = [
            ['title' => 'ثبت یادداشت جدید برای «کافه مینا»', 'time' => '10 دقیقه قبل', 'type' => 'note'],
            ['title' => 'ارسال قرارداد برای «شرکت پارس خودرو»', 'time' => '1 ساعت قبل', 'type' => 'contract'],
            ['title' => 'دریافت تیکت پشتیبانی از «مهناز آذر»', 'time' => '2 ساعت قبل', 'type' => 'support'],
            ['title' => 'به‌روزرسانی وضعیت فرصت «همکاری بانک مهر»', 'time' => 'دیروز', 'type' => 'deal'],
        ];
    @endphp

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <div class="lg:col-span-8 space-y-6">
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($metrics as $metric)
                    <div class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white/80 p-5 shadow-sm transition hover:-translate-y-1 hover:border-indigo-400 hover:shadow-lg dark:border-slate-700/60 dark:bg-slate-900/70">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $metric['title'] }}</p>
                                <p class="mt-3 text-2xl font-bold text-slate-900 dark:text-white">{{ $metric['value'] }}</p>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                @if ($metric['trend'] === 'up')
                                    <svg class="h-4 w-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l6-6 4.5 4.5 4.5-4.5" /></svg>
                                @else
                                    <svg class="h-4 w-4 text-rose-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-6 6-4.5-4.5-4.5 4.5" /></svg>
                                @endif
                                <span>{{ $metric['change'] }}</span>
                            </span>
                        </div>
                        <div class="mt-6 h-16 w-full">
                            <svg viewBox="0 0 120 40" preserveAspectRatio="none" class="h-full w-full text-indigo-400">
                                <path d="M0 30 L20 26 L40 28 L60 18 L80 24 L100 12 L120 16" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="{{ $metric['trend'] === 'up' ? 'text-emerald-400' : 'text-rose-400' }}"></path>
                            </svg>
                        </div>
                    </div>
                @endforeach
            </section>

            <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm dark:border-slate-700/60 dark:bg-slate-900/70">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">عملکرد ماهانه</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">نمایی کلی از رشد درآمد و نرخ تبدیل در ماه جاری</p>
                        </div>
                        <button class="rounded-xl border border-slate-200/70 px-3 py-1 text-xs text-slate-500 hover:border-indigo-500 hover:text-indigo-600 dark:border-slate-700/60 dark:text-slate-300 dark:hover:border-indigo-400 dark:hover:text-indigo-200">گزارش کامل</button>
                    </div>
                    <div class="mt-6 grid grid-cols-4 gap-4 text-center">
                        <div class="rounded-2xl bg-indigo-500/10 p-4 text-sm text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-200">
                            <p class="text-xs text-indigo-400">درآمد</p>
                            <p class="mt-2 text-xl font-bold">+32%</p>
                        </div>
                        <div class="rounded-2xl bg-emerald-500/10 p-4 text-sm text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-200">
                            <p class="text-xs text-emerald-400">تبدیل</p>
                            <p class="mt-2 text-xl font-bold">+18%</p>
                        </div>
                        <div class="rounded-2xl bg-amber-500/10 p-4 text-sm text-amber-600 dark:bg-amber-500/20 dark:text-amber-200">
                            <p class="text-xs text-amber-400">فروش</p>
                            <p class="mt-2 text-xl font-bold">+24%</p>
                        </div>
                        <div class="rounded-2xl bg-rose-500/10 p-4 text-sm text-rose-600 dark:bg-rose-500/20 dark:text-rose-200">
                            <p class="text-xs text-rose-400">ریزش</p>
                            <p class="mt-2 text-xl font-bold">-6%</p>
                        </div>
                    </div>
                    <div class="mt-6 h-52 rounded-2xl bg-gradient-to-br from-slate-100 via-white to-white p-4 dark:from-slate-900 dark:via-slate-950 dark:to-slate-900">
                        <svg viewBox="0 0 120 80" preserveAspectRatio="none" class="h-full w-full">
                            <defs>
                                <linearGradient id="gradient-chart" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" style="stop-color: rgba(79, 70, 229, 0.45);" />
                                    <stop offset="100%" style="stop-color: rgba(79, 70, 229, 0);" />
                                </linearGradient>
                            </defs>
                            <path d="M0 70 L15 60 L30 62 L45 48 L60 36 L75 38 L90 24 L105 28 L120 18 V80 H0 Z" fill="url(#gradient-chart)" />
                            <path d="M0 70 L15 60 L30 62 L45 48 L60 36 L75 38 L90 24 L105 28 L120 18" fill="none" stroke="#4f46e5" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
                <div class="rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm dark:border-slate-700/60 dark:bg-slate-900/70">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">فرصت‌های فروش</h2>
                        <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : '#' }}" class="text-xs text-indigo-500 hover:text-indigo-600">نمایش جزئیات</a>
                    </div>
                    <ul class="mt-6 space-y-4">
                        @foreach ($pipeline as $stage)
                            <li class="flex items-center justify-between rounded-2xl border border-slate-200/70 bg-slate-50/60 p-4 dark:border-slate-700/60 dark:bg-slate-900/60">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $stage['stage'] }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $stage['count'] }} فرصت فعال</p>
                                </div>
                                <div class="text-end">
                                    <span class="text-xs text-slate-400">ارزش</span>
                                    <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $stage['value'] }}</p>
                                    <div class="mt-2 h-2 w-32 rounded-full bg-slate-200/80 dark:bg-slate-800">
                                        <div class="h-2 rounded-full bg-gradient-to-l {{ $stage['color'] }}" style="width: {{ min(100, $stage['count'] * 3) }}%"></div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        </div>

        <aside class="space-y-6 lg:col-span-4">
            <div class="rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm dark:border-slate-700/60 dark:bg-slate-900/70">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">برنامه امروز</h2>
                    <span class="rounded-full bg-indigo-500/10 px-3 py-1 text-xs text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-200">{{ now()->translatedFormat('l d F') }}</span>
                </div>
                <ul class="mt-5 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                    <li class="flex items-start gap-3 rounded-2xl border border-slate-200/70 bg-slate-50/60 p-4 dark:border-slate-700/60 dark:bg-slate-900/60">
                        <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        <div>
                            <p class="font-semibold">جلسه پیگیری با «پارس خودرو»</p>
                            <p class="text-xs text-slate-400">ساعت 14:00 - دفتر مرکزی</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3 rounded-2xl border border-slate-200/70 bg-slate-50/60 p-4 dark:border-slate-700/60 dark:bg-slate-900/60">
                        <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                        <div>
                            <p class="font-semibold">مرور گزارش عملکرد تیم فروش</p>
                            <p class="text-xs text-slate-400">ساعت 16:30 - ویدئوکنفرانس</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3 rounded-2xl border border-slate-200/70 bg-slate-50/60 p-4 dark:border-slate-700/60 dark:bg-slate-900/60">
                        <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                        <div>
                            <p class="font-semibold">پیگیری فاکتورهای معوق</p>
                            <p class="text-xs text-slate-400">ساعت 18:00 - سامانه مالی</p>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm dark:border-slate-700/60 dark:bg-slate-900/70">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">فعالیت‌های اخیر</h2>
                    <a href="{{ route('dashboard') }}" class="text-xs text-indigo-500 hover:text-indigo-600">همه موارد</a>
                </div>
                <ul class="mt-5 space-y-3">
                    @foreach ($activities as $activity)
                        <li class="flex items-start gap-3 rounded-2xl border border-slate-200/70 bg-slate-50/60 p-4 text-sm text-slate-600 dark:border-slate-700/60 dark:bg-slate-900/60 dark:text-slate-300">
                            <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full {{ match ($activity['type']) {
                                'note' => 'bg-emerald-500',
                                'contract' => 'bg-indigo-500',
                                'support' => 'bg-amber-500',
                                default => 'bg-slate-400',
                            } }}"></span>
                            <div>
                                <p class="font-semibold">{{ $activity['title'] }}</p>
                                <p class="text-xs text-slate-400">{{ $activity['time'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>
    </div>
</x-app-layout>
