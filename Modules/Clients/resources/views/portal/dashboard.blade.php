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

        @if(isset($recentAppointments) && $recentAppointments->where('status', 'PENDING_PAYMENT')->count() > 0)
            <div class="rounded-xl bg-blue-50 dark:bg-blue-900/30 p-4 border border-blue-200 dark:border-blue-800 shadow-sm flex items-start gap-3">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-blue-800 dark:text-blue-300">شما نوبت(های) در انتظار پرداخت دارید</h3>
                    <p class="text-sm text-blue-700 dark:text-blue-400 mt-1">برای قطعی شدن نوبت‌های خود، لطفاً در اسرع وقت نسبت به پرداخت یا ثبت فیش اقدام نمایید.</p>
                </div>
                <a href="{{ route('client.appointments.index', ['status' => 'PENDING_PAYMENT']) }}" class="shrink-0 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    مشاهده نوبت‌ها
                </a>
            </div>
        @endif

        @if(($showRentalFeatures ?? false) && isset($recentRentalBookings) && $recentRentalBookings->whereIn('status', [\Modules\Properties\Entities\PropertyRentalBooking::STATUS_AWAITING_PAYMENT, \Modules\Properties\Entities\PropertyRentalBooking::STATUS_PENDING_HOST])->count() > 0)
            <div class="rounded-xl bg-amber-50 dark:bg-amber-950/30 p-4 border border-amber-200 dark:border-amber-800 shadow-sm flex items-start gap-3">
                <div class="p-1 rounded-lg bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-amber-900 dark:text-amber-300">شما رزرو اقامتگاه در انتظار تسویه و پرداخت دارید</h3>
                    <p class="text-sm text-amber-800/90 dark:text-amber-400 mt-1">جهت قطعی شدن رزرو اقامتگاه و صدور تأییدیه رسمی، لطفاً نسبت به پرداخت آنلاین اقدام فرمایید.</p>
                </div>
                <a href="{{ route('client.rental-bookings.index', ['status' => 'awaiting_payment']) }}" class="shrink-0 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-lg transition-colors shadow-sm">
                    مشاهده رزروها
                </a>
            </div>
        @endif

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
            if ($showInvoiceFeatures ?? false) $activeCards++;
            if ($showOrderFeatures ?? false) $activeCards++;
            if ($showRentalFeatures ?? false) $activeCards++;

            $gridColsClass = match(true) {
                $activeCards >= 4 => 'lg:grid-cols-4',
                $activeCards === 3 => 'lg:grid-cols-3',
                default => 'lg:grid-cols-2'
            };
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 {{ $gridColsClass }} gap-6">

            {{-- کارت رزروهای اقامتگاهی من --}}
            @if($showRentalFeatures ?? false)
                <a href="{{ route('client.rental-bookings.index') }}" class="group bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-all cursor-pointer block">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        </div>
                        <span class="text-xs text-gray-400 dark:text-gray-500">جاری و فعال</span>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1 font-sans">{{ \Morilog\Jalali\CalendarUtils::convertNumbers($activeRentalBookingsCount ?? 0) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 group-hover:text-indigo-600 transition-colors">رزروهای اقامتگاه من &larr;</div>
                </a>
            @endif

            {{-- کارت نوبت ها --}}
            @if($showBookingFeatures)
                <div class="group bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <span class="text-xs text-gray-400 dark:text-gray-500">فعال و در انتظار</span>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ \Morilog\Jalali\CalendarUtils::convertNumbers($activeAppointmentsCount ?? 0) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">نوبت های من</div>
                </div>
            @endif

            {{-- کارت سفارشات خدمات --}}
            @if($showOrderFeatures ?? false)
                <a href="{{ route('client.orders.index') }}" class="group bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-all cursor-pointer block">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                        </div>
                        <span class="text-xs text-gray-400 dark:text-gray-500">فعال</span>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ \Morilog\Jalali\CalendarUtils::convertNumbers($activeOrdersCount ?? 0) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 group-hover:text-blue-600 transition-colors">سفارش‌های من &larr;</div>
                </a>
            @endif

            {{-- کارت فاکتورهای خدمات --}}
            @if($showInvoiceFeatures ?? false)
                <a href="{{ route('client.invoices.index') }}" class="group bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-all cursor-pointer block">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <span class="text-xs text-gray-400 dark:text-gray-500">پرداخت نشده</span>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ \Morilog\Jalali\CalendarUtils::convertNumbers($unpaidInvoicesCount ?? 0) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 group-hover:text-blue-600 transition-colors">فاکتورهای من &larr;</div>
                </a>
            @endif

            {{-- کارت تیکت‌ها --}}
            <div class="group bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500">در انتظار</span>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ \Morilog\Jalali\CalendarUtils::convertNumbers(0) }}</div>
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
                <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ \Morilog\Jalali\CalendarUtils::convertNumbers(number_format($unpaidInvoicesSum ?? 0)) }} <span class="text-xs font-normal text-gray-400">ریال</span></div>
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

                    <a href="{{ route('client.market.interactions') }}" class="flex flex-col items-center justify-center gap-3 p-6 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 hover:shadow-md transition-all group">
                        <div class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600">دیدگاه‌ها و پرسش‌ها</span>
                    </a>
                @endif

                {{-- دکمه رزرو اقامتگاه --}}
                @if($showRentalFeatures ?? false)
                    <a href="{{ route('client.rental-bookings.index') }}" class="flex flex-col items-center justify-center gap-3 p-6 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 hover:shadow-md transition-all group">
                        <div class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600">رزروهای اقامتگاه</span>
                    </a>
                @endif

                @if(!$showBookingFeatures && !$showMarketFeatures && !($showInvoiceFeatures ?? false) && !($showRentalFeatures ?? false))
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
                                    @php
                                        $tz = config('booking.timezones.display_default', 'Asia/Tehran');
                                        $startLocal = $appointment->start_at_utc?->copy()->timezone($tz);
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer" onclick="window.location.href='{{ route('client.appointments.show', $appointment->id) }}'">
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $appointment->service->name ?? '---' }}</span>
                                                <span class="text-xs text-gray-500">{{ $appointment->provider->full_name ?? $appointment->provider->name ?? '---' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                            <div class="flex flex-col">
                                                <span>{{ $startLocal ? \Morilog\Jalali\CalendarUtils::convertNumbers(jdate($startLocal)->format('Y/m/d')) : '---' }}</span>
                                                <span class="text-xs text-gray-500">{{ $startLocal ? \Morilog\Jalali\CalendarUtils::convertNumbers($startLocal->format('H:i')) : '---' }}</span>
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
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            لیست صورتحساب‌ها
                        </h2>
                        <a href="{{ route('client.payments.index') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">مشاهده همه</a>
                    </div>
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
                                    @php
                                        $statusKey = strtoupper(trim((string)$payment->status));
                                    @endphp
                                    @continue(in_array($statusKey, ['CANCELED', 'CANCELLED', 'REJECTED', 'EXPIRED'], true))
                                    @php
                                        $paymentTargetUrl = route('client.payments.show', ['type' => $payment->type, 'id' => $payment->id]);
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer" onclick="window.location.href='{{ $paymentTargetUrl }}'">
                                        <td class="px-6 py-4 text-gray-900 dark:text-white font-medium">
                                            <div class="flex flex-col">
                                                <span>{{ \Morilog\Jalali\CalendarUtils::convertNumbers(number_format($payment->amount_display)) }} <span class="text-xs font-normal text-gray-500">{{ $payment->currency_label }}</span></span>
                                                <span class="text-xs text-gray-400 font-normal mt-1">{{ $payment->type_label }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                            <div class="flex flex-col">
                                                <span>{{ $payment->date ? \Morilog\Jalali\CalendarUtils::convertNumbers(jdate($payment->date)->format('Y/m/d')) : '---' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $payStatusColor = match($statusKey) {
                                                    'PAID', 'SUCCESS', 'COMPLETED' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                                    'PENDING', 'UNPAID', 'AWAITING_PAYMENT', 'WAITING' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                                    'FAILED', 'FAIL' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                                    'REFUNDED' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
                                                    'CANCELED', 'CANCELLED', 'REJECTED', 'EXPIRED' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                                    default => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                                                };

                                                $payStatusLabel = match($statusKey) {
                                                    'PAID', 'SUCCESS', 'COMPLETED' => 'پرداخت شده',
                                                    'PENDING', 'UNPAID', 'AWAITING_PAYMENT', 'WAITING' => 'در انتظار پرداخت',
                                                    'FAILED', 'FAIL' => 'ناموفق',
                                                    'REFUNDED' => 'استرداد شده',
                                                    'CANCELED', 'CANCELLED' => 'لغو شده',
                                                    'REJECTED' => 'رد شده',
                                                    'EXPIRED' => 'منقضی شده',
                                                    default => ($statusKey === 'UNPAID' ? 'در انتظار پرداخت' : $payment->status)
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

            {{-- بخش سفارش‌های خدمات اخیر --}}
            @if(($showOrderFeatures ?? false) && isset($recentOrders) && $recentOrders->isNotEmpty())
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                            آخرین سفارش‌های خدمات شما
                        </h2>
                        <a href="{{ route('client.orders.index') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">مشاهده همه</a>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-right">
                                <thead class="text-xs text-gray-500 bg-gray-50/50 dark:bg-gray-900/50 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                                <tr>
                                    <th class="px-6 py-4 font-medium">شماره و سرویس</th>
                                    <th class="px-6 py-4 font-medium">تاریخ ثبت</th>
                                    <th class="px-6 py-4 font-medium">وضعیت</th>
                                    <th class="px-6 py-4 font-medium"></th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($recentOrders as $order)
                                    @php
                                        $ordStatusName = $order->status?->name ?? 'نامشخص';
                                        $isActive = str_contains($ordStatusName, 'فعال') && !str_contains($ordStatusName, 'غیر');
                                        $isPending = str_contains($ordStatusName, 'انتظار') || str_contains($ordStatusName, 'بررسی');
                                        $isCanceled = str_contains($ordStatusName, 'لغو') || str_contains($ordStatusName, 'غیر فعال');

                                        $ordStatusColor = match(true) {
                                            $isActive => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                            $isPending => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                            $isCanceled => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                                        };
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer" onclick="window.location.href='{{ route('client.orders.show', $order->id) }}'">
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-900 dark:text-white">{{ \Modules\Clients\App\Support\OrderDisplayHelper::getTitle($order) }}</span>
                                                <span class="text-xs text-gray-400">#{{ \Morilog\Jalali\CalendarUtils::convertNumbers($order->order_number) }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                            <div class="flex flex-col">
                                                <span>{{ $order->created_at ? \Morilog\Jalali\CalendarUtils::convertNumbers(jdate($order->created_at)->format('Y/m/d')) : '---' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium {{ $ordStatusColor }}">
                                                {{ $ordStatusName }}
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
                                            #{{ \Morilog\Jalali\CalendarUtils::convertNumbers($order->tracking_code ?? $order->id) }}
                                        </td>
                                         <td class="px-6 py-4 text-gray-700 dark:text-gray-300 text-xs font-bold">
                                             {{ \Morilog\Jalali\CalendarUtils::convertNumbers(number_format($order->grand_total ?? 0)) }} {{ $order->currency_label ?? 'ریال' }}
                                         </td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                            {{ $order->created_at ? \Morilog\Jalali\CalendarUtils::convertNumbers(jdate($order->created_at)->format('Y/m/d')) : '---' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $delStatusMap = [
                                                    'pending' => ['label' => 'در انتظار تایید', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'],
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

            {{-- بخش آخرین رزروهای اقامتگاه من --}}
            @if(($showRentalFeatures ?? false) && isset($recentRentalBookings) && $recentRentalBookings->isNotEmpty())
                <div class="lg:col-span-2">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            آخرین رزروهای اقامتگاهی شما
                        </h2>
                        <a href="{{ route('client.rental-bookings.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-bold">مشاهده همه</a>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-right">
                                <thead class="text-xs text-gray-500 bg-gray-50/50 dark:bg-gray-900/50 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                                <tr>
                                    <th class="px-6 py-4 font-medium">اقامتگاه</th>
                                    <th class="px-6 py-4 font-medium">کد رزرو</th>
                                    <th class="px-6 py-4 font-medium">بازه اقامت</th>
                                    <th class="px-6 py-4 font-medium">مبلغ کل</th>
                                    <th class="px-6 py-4 font-medium">وضعیت</th>
                                    <th class="px-6 py-4 font-medium"></th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($recentRentalBookings as $booking)
                                    @php
                                        $badge = $booking->status_badge ?: ['label' => $booking->status_label ?? 'نامشخص', 'class' => 'bg-gray-100 text-gray-700'];
                                        $propCover = $booking->property?->primary_image_url ?? $booking->property?->primaryImage?->image_url ?? asset('assets/images/placeholder.jpg');
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer" onclick="window.location.href='{{ route('client.rental-bookings.show', $booking->id) }}'">
                                        <td class="px-6 py-4 text-gray-900 dark:text-white font-medium">
                                            <div class="flex items-center gap-3">
                                                <img src="{{ $propCover }}" alt="" class="w-10 h-10 rounded-xl object-cover shrink-0 border border-gray-100 dark:border-gray-700">
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-gray-900 dark:text-white line-clamp-1 max-w-[220px]">{{ $booking->property?->title ?? 'اقامتگاه' }}</span>
                                                    <span class="text-xs text-gray-400 font-normal mt-0.5">{{ $booking->nights_count }} شب • {{ $booking->guests_count }} نفر</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-indigo-600 dark:text-indigo-400 font-black text-xs">
                                            {{ $booking->booking_code }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300 text-xs font-bold">
                                            <span>{{ \Morilog\Jalali\CalendarUtils::convertNumbers($booking->check_in) }}</span>
                                            <span class="text-gray-400 mx-1">تا</span>
                                            <span>{{ \Morilog\Jalali\CalendarUtils::convertNumbers($booking->check_out) }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-900 dark:text-white text-xs font-black">
                                            {{ \Morilog\Jalali\CalendarUtils::convertNumbers(number_format($booking->total_price)) }}
                                            <span class="text-[11px] font-normal text-gray-400">{{ $booking->currency === 'IRR' ? 'ریال' : 'تومان' }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium {{ $badge['class'] ?? 'bg-gray-100 text-gray-700' }}">
                                                {{ $badge['label'] ?? 'نامشخص' }}
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
