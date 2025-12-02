@extends('clients::layouts.client')

@php
    $title = 'داشبورد ناحیه کاربری';

    // دریافت وضعیت
    $status = optional($client->status);
    $statusLabel = $status?->label ?? 'نامشخص';
    $statusKey   = $status?->key ?? 'unknown';

    // تنظیم رنگ‌ها و آیکون‌ها بر اساس وضعیت
    $statusConfig = match ($statusKey) {
        'active'   => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-50', 'icon' => 'path/to/check', 'ring' => 'ring-emerald-400'],
        'pending'  => ['bg' => 'bg-amber-500',   'text' => 'text-amber-50',   'icon' => 'path/to/clock', 'ring' => 'ring-amber-400'],
        'blocked', 'cancelled' => ['bg' => 'bg-red-500', 'text' => 'text-red-50', 'icon' => 'path/to/x', 'ring' => 'ring-red-400'],
        default    => ['bg' => 'bg-gray-500',    'text' => 'text-gray-50',    'icon' => 'path/to/q', 'ring' => 'ring-gray-400'],
    };
@endphp

@section('content')
    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">

        {{-- بخش هیرو و خوش‌آمدگویی --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 to-violet-700 p-8 sm:p-10 shadow-xl shadow-indigo-600/20 text-white">

            {{-- پترن پس‌زمینه --}}
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none opacity-10">
                <svg class="absolute -top-24 -left-24 w-96 h-96 text-white" fill="currentColor" viewBox="0 0 200 200"><path d="M42.7,-62.9C50.9,-52.8,50.1,-34.4,51.7,-19.2C53.4,-4,57.4,8,54.5,18.7C51.6,29.3,41.8,38.7,31.2,46.9C20.6,55.1,9.2,62.1,-2.9,66.1C-15,70.1,-27.8,71.1,-38.6,63.9C-49.4,56.7,-58.2,41.3,-62.9,25.2C-67.6,9.1,-68.2,-7.7,-61.4,-21.8C-54.6,-35.9,-40.4,-47.3,-27,-54.2C-13.6,-61.1,-1,-63.5,13.8,-63.5C28.6,-63.5,42.7,-62.9,42.7,-62.9Z" transform="translate(100 100)" /></svg>
            </div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-2 opacity-90">
                        <span class="text-sm font-medium">خوش آمدید،</span>
                    </div>
                    <h1 class="text-3xl font-bold tracking-tight mb-2">
                        {{ $client->full_name ?: $client->username }}
                    </h1>
                    <p class="text-indigo-100 text-sm max-w-lg leading-relaxed opacity-90">
                        به پنل مدیریت {{ config('clients.labels.plural', 'مشتریان') }} خوش آمدید. از اینجا می‌توانید وضعیت حساب و سرویس‌های خود را مشاهده کنید.
                    </p>
                </div>

                {{-- بج وضعیت --}}
                <div class="flex flex-col items-start md:items-end gap-2">
                    <span class="text-xs font-medium opacity-80">وضعیت حساب شما</span>
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/20 backdrop-blur-md border border-white/10 shadow-inner">
                        <span class="relative flex h-3 w-3">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $statusConfig['bg'] }} opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 {{ $statusConfig['bg'] }}"></span>
                        </span>
                        <span class="font-bold text-sm tracking-wide">{{ $statusLabel }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- کارت‌های آمار و اطلاعات (فعلا Placeholder) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- کارت سرویس‌ها --}}
            <div class="group bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500">فعال</span>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">0</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">سرویس‌های من</div>
            </div>

            {{-- کارت تیکت‌ها --}}
            <div class="group bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500">در انتظار</span>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">0</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">تیکت‌های پشتیبانی</div>
            </div>

            {{-- کارت صورت‌حساب --}}
            <div class="group bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500">پرداخت نشده</span>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">0 <span class="text-xs font-normal text-gray-400">تومان</span></div>
                <div class="text-sm text-gray-500 dark:text-gray-400">صورت‌حساب مالی</div>
            </div>
        </div>

        {{-- بخش دسترسی سریع --}}
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                دسترسی سریع
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="#" class="flex flex-col items-center justify-center gap-3 p-6 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 hover:shadow-md transition-all group">
                    <div class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600">پروفایل من</span>
                </a>

                <a href="#" class="flex flex-col items-center justify-center gap-3 p-6 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-emerald-500 dark:hover:border-emerald-500 hover:shadow-md transition-all group">
                    <div class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-emerald-600">تیکت جدید</span>
                </a>

                {{-- آیتم‌های غیرفعال (Placeholder) --}}
                <div class="col-span-2 md:col-span-2 flex items-center justify-center p-6 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 text-gray-400 dark:text-gray-500 text-sm">
                    سایر بخش‌ها به زودی فعال می‌شوند...
                </div>
            </div>
        </div>

    </div>
@endsection
