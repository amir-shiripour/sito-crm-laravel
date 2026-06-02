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
        'blocked', 'cancelled', 'CANCELLED' => ['bg' => 'bg-red-500', 'text' => 'text-red-50', 'icon' => 'path/to/x', 'ring' => 'ring-red-400'],
        default    => ['bg' => 'bg-gray-500',    'text' => 'text-gray-50',    'icon' => 'path/to/q', 'ring' => 'ring-gray-400'],
    };

    // کدهای چک کردن ماژول از اینجا حذف شد و جای آن متغیر $showMarketFeatures از کنترلر دریافت می‌شود (طبق معماری استاندارد)
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
                        به پنل مدیریت {{ config('clients.labels.plural', 'مشتریان') }} خوش آمدید. از اینجا می‌توانید وضعیت حساب، سرویس‌ها و نوبت های خود را مشاهده کنید.
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

        {{-- کارت‌های آمار و اطلاعات --}}
        @php
            $activeCards = 2; // تیکت و صورت حساب همیشه هستند
            if ($showBookingFeatures) $activeCards++;
            if ($showMarketFeatures) $activeCards++;

            $gridColsClass = match($activeCards) {
                4 => 'lg:grid-cols-4',
                3 => 'lg:grid-cols-3',
                2 => 'lg:grid-cols-2',
                default => 'lg:grid-cols-4'
            };
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 {{ $gridColsClass }} gap-6">

            {{-- کارت نوبت ها --}}
            @if($showBookingFeatures)
                <div class="group bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <span class="text-xs text-gray-400 dark:text-gray-500">فعال و در انتظار</span>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ $activeAppointmentsCount ?? 0 }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">نوبت های من</div>
                </div>
            @endif

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
            <a href="{{ route('client.payments.index') }}" class="group bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:border-emerald-500 dark:hover:border-emerald-500 hover:shadow-md transition-all cursor-pointer block">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500">پرداخت نشده</span>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ number_format($unpaidInvoicesSum ?? 0) }} <span class="text-xs font-normal text-gray-400">تومان</span></div>
                <div class="text-sm text-gray-500 dark:text-gray-400 group-hover:text-emerald-600 transition-colors">لیست صورت‌حساب‌ها &larr;</div>
            </a>

            {{-- کارت سفارشات فروشگاه (نمایش مشروط) --}}
            @if($showMarketFeatures)
                <a href="{{ Route::has('client.market.orders.index') ? route('client.market.orders.index') : '#' }}" class="group bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:border-purple-500 dark:hover:border-purple-500 hover:shadow-md transition-all cursor-pointer block">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        </div>
                        <span class="text-xs text-gray-400 dark:text-gray-500">در حال پردازش</span>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ $activeMarketOrdersCount ?? 0 }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 group-hover:text-purple-600 transition-colors">سفارشات فروشگاه &larr;</div>
                </a>
            @endif

        </div>

        {{-- بخش دسترسی سریع --}}
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                دسترسی سریع
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('client.profile.show') }}" class="flex flex-col items-center justify-center gap-3 p-6 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 hover:shadow-md transition-all group">
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

                @if($showBookingFeatures)
                    <a href="{{ Route::has('booking.public.index') ? route('booking.public.index') : '#' }}" class="flex flex-col items-center justify-center gap-3 p-6 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-500 hover:shadow-md transition-all group">
                        <div class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 group-hover:bg-blue-50 group-hover:text-blue-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-blue-600">ثبت نوبت جدید</span>
                    </a>
                @endif

                {{-- دکمه فروشگاه (نمایش مشروط) --}}
                @if($showMarketFeatures)
                    <a href="{{ Route::has('client.market.index') ? route('client.market.index') : '#' }}" class="flex flex-col items-center justify-center gap-3 p-6 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-purple-500 dark:hover:border-purple-500 hover:shadow-md transition-all group">
                        <div class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 group-hover:bg-purple-50 group-hover:text-purple-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-purple-600">محصولات فروشگاه</span>
                    </a>
                @endif

                @if(!$showBookingFeatures || !$showMarketFeatures)
                    {{-- آیتم‌های غیرفعال (Placeholder) --}}
                    <div class="flex items-center justify-center p-6 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 text-gray-400 dark:text-gray-500 text-sm">
                        سایر بخش‌ها...
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            {{-- بخش نوبت های اخیر --}}
            @if(isset($recentAppointments) && $recentAppointments->isNotEmpty())
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                        آخرین نوبت های شما
                    </h2>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-right">
                                <thead class="text-xs text-gray-500 bg-gray-50/50 dark:bg-gray-900/50 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                                <tr>
                                    <th class="px-6 py-4 font-medium">سرویس</th>
                                    <th class="px-6 py-4 font-medium">تاریخ و زمان</th>
                                    <th class="px-6 py-4 font-medium">وضعیت</th>
                                    <th class="px-6 py-4 font-medium"></th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($recentAppointments as $appointment)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer" onclick="window.location.href='{{ route('client.appointments.show', $appointment->id) }}'">
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $appointment->service->name ?? '---' }}</span>
                                                <span class="text-xs text-gray-500">{{ $appointment->provider->full_name ?? $appointment->provider->name ?? '---' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                            <div class="flex flex-col">
                                                <span>{{ $appointment->start_at_utc ? jdate($appointment->start_at_utc)->format('Y/m/d') : '---' }}</span>
                                                <span class="text-xs text-gray-500">{{ $appointment->start_at_utc ? jdate($appointment->start_at_utc)->format('H:i') : '---' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $appStatusColor = match($appointment->status) {
                                                    'CONFIRMED' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                                    'PENDING' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                                    'PENDING_PAYMENT' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                                    'CANCELED_BY_CLIENT', 'CANCELED_BY_ADMIN' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                                    'DONE' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                                                };

                                                $appStatusLabel = match($appointment->status) {
                                                    'CONFIRMED' => 'تایید شده',
                                                    'PENDING' => 'در انتظار بررسی',
                                                    'PENDING_PAYMENT' => 'در انتظار پرداخت',
                                                    'CANCELED_BY_CLIENT' => 'لغو توسط شما',
                                                    'CANCELED_BY_ADMIN' => 'لغو توسط سیستم',
                                                    'DONE' => 'انجام شده',
                                                    'RESCHEDULED' => 'تغییر زمان',
                                                    'NO_SHOW' => 'عدم حضور',
                                                    'DRAFT' => 'پیش‌نویس',
                                                    default => $appointment->status
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium {{ $appStatusColor }}">
                                            {{ $appStatusLabel }}
                                        </span>
                                        </td>
                                        <td class="px-6 py-4 text-left">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- بخش صورتحساب های اخیر --}}
            @if(isset($recentPayments) && $recentPayments->isNotEmpty())
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        آخرین پرداخت‌ها
                    </h2>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-right">
                                <thead class="text-xs text-gray-500 bg-gray-50/50 dark:bg-gray-900/50 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                                <tr>
                                    <th class="px-6 py-4 font-medium">مبلغ و نوع</th>
                                    <th class="px-6 py-4 font-medium">تاریخ</th>
                                    <th class="px-6 py-4 font-medium">وضعیت</th>
                                    <th class="px-6 py-4 font-medium"></th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($recentPayments as $payment)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer" onclick="window.location.href='{{ route('client.payments.show', ['type' => $payment->type, 'id' => $payment->id]) }}'">
                                        <td class="px-6 py-4 text-gray-900 dark:text-white font-medium">
                                            <div class="flex flex-col">
                                                <span>{{ number_format($payment->amount) }} <span class="text-xs font-normal text-gray-500">تومان</span></span>
                                                <span class="text-xs text-gray-400 font-normal mt-1">{{ $payment->type_label }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                            <div class="flex flex-col">
                                                <span>{{ $payment->date ? jdate($payment->date)->format('Y/m/d') : '---' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $payStatusColor = match($payment->status) {
                                                    'PAID' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                                    'PENDING' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                                    'FAILED' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                                    'REFUNDED' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
                                                    'CANCELED' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                                    'CANCELLED' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                                                };

                                                $payStatusLabel = match($payment->status) {
                                                    'PAID' => 'پرداخت شده',
                                                    'PENDING' => 'در انتظار پرداخت',
                                                    'FAILED' => 'ناموفق',
                                                    'REFUNDED' => 'استرداد شده',
                                                    'CANCELED' => 'لغو شده',
                                                    'CANCELLED' => 'لغو شده',
                                                    default => $payment->status
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium {{ $payStatusColor }}">
                                            {{ $payStatusLabel }}
                                        </span>
                                        </td>
                                        <td class="px-6 py-4 text-left">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- بخش سفارشات اخیر فروشگاه (نمایش مشروط) --}}
            @if($showMarketFeatures && isset($recentMarketOrders) && $recentMarketOrders->isNotEmpty())
                <div class="lg:col-span-2">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                        آخرین سفارشات فروشگاه
                    </h2>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-right">
                                <thead class="text-xs text-gray-500 bg-gray-50/50 dark:bg-gray-900/50 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                                <tr>
                                    <th class="px-6 py-4 font-medium">شماره سفارش</th>
                                    <th class="px-6 py-4 font-medium">مبلغ کل</th>
                                    <th class="px-6 py-4 font-medium">تاریخ</th>
                                    <th class="px-6 py-4 font-medium">وضعیت</th>
                                    <th class="px-6 py-4 font-medium"></th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($recentMarketOrders as $order)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer" onclick="window.location.href='{{ Route::has('client.market.orders.show') ? route('client.market.orders.show', $order->id) : '#' }}'">
                                        <td class="px-6 py-4 text-gray-900 dark:text-white font-medium">
                                            #{{ $order->tracking_code ?? $order->id }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300 font-mono text-xs">
                                            {{ number_format($order->grand_total ?? 0) }} تومان
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                            {{ $order->created_at ? jdate($order->created_at)->format('Y/m/d') : '---' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $delStatusMap = [
                                                    'processing' => ['label' => 'در حال پردازش', 'class' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400'],
                                                    'shipped' => ['label' => 'ارسال شده', 'class' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-400'],
                                                    'delivered' => ['label' => 'تحویل داده شده', 'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400'],
                                                    'canceled' => ['label' => 'لغو شده', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400'],
                                                ];
                                                $ds = $delStatusMap[strtolower($order->delivery_status)] ?? ['label' => $order->delivery_status ?: 'نامشخص', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'];
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium {{ $ds['class'] }}">
                                                {{ $ds['label'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-left">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </div>
@endsection
