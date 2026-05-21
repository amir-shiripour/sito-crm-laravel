@extends('layouts.web')

@section('title', 'نتیجه رزرو نوبت')

@section('content')
    @php
        $currencyMap = ['IRR' => 'ریال', 'IRT' => 'تومان'];
        // واحد پولی از تنظیمات کلی خوانده می‌شود، چون ممکن است $appointment->payments خالی باشد
        $settings = \Modules\Booking\Entities\BookingSetting::current();
        $currencyLabel = $currencyMap[$settings->currency_unit ?? 'IRR'] ?? ($settings->currency_unit ?? 'ریال');
    @endphp
    <div class="max-w-3xl mx-auto w-full px-6 md:px-0 pt-8 pb-16">
        <div
            class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl rounded-[2.5rem] border border-gray-100 dark:border-gray-800 shadow-2xl shadow-gray-200/20 dark:shadow-none overflow-hidden animate-in fade-in slide-in-from-bottom-8 duration-700">

            {{-- هدر بر اساس وضعیت --}}
            @php
                $isSuccess = session('success') || in_array($appointment->status, ['CONFIRMED', 'PENDING']);
                $headerColor = $isSuccess ? 'bg-gradient-to-br from-emerald-500 to-emerald-600' : 'bg-gradient-to-br from-rose-500 to-rose-600';
                $icon = $isSuccess
                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />'
                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />';

                $statusLabels = [
                    'DRAFT' => ['label' => 'پیش‌نویس', 'color' => 'bg-gray-100 text-gray-800'],
                    'PENDING_PAYMENT' => ['label' => 'در انتظار پرداخت', 'color' => 'bg-amber-100 text-amber-800'],
                    'PENDING' => ['label' => 'در انتظار تایید', 'color' => 'bg-blue-100 text-blue-800'],
                    'CONFIRMED' => ['label' => 'تایید شده', 'color' => 'bg-emerald-100 text-emerald-800'],
                    'DONE' => ['label' => 'انجام شده', 'color' => 'bg-indigo-100 text-indigo-800'],
                    'NO_SHOW' => ['label' => 'عدم حضور', 'color' => 'bg-rose-100 text-rose-800'],
                    'CANCELED_BY_ADMIN' => ['label' => 'لغو توسط سیستم', 'color' => 'bg-red-100 text-red-800'],
                    'CANCELED_BY_CLIENT' => ['label' => 'لغو توسط مشتری', 'color' => 'bg-red-100 text-red-800'],
                    'RESCHEDULED' => ['label' => 'جابجا شده', 'color' => 'bg-purple-100 text-purple-800'],
                ];

                $statusData = $statusLabels[$appointment->status] ?? ['label' => $appointment->status, 'color' => 'bg-gray-100 text-gray-800'];
            @endphp

            <div class="{{ $headerColor }} px-6 py-12 text-center text-white relative overflow-hidden">
                {{-- Decorative pattern overlay --}}
                <div
                    class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9IiNmZmZmZmYiLz48L3N2Zz4=')] mask-image-gradient-to-b"></div>

                <div class="relative z-10">
                    <div
                        class="w-24 h-24 mx-auto bg-white/20 rounded-[1.5rem] flex items-center justify-center mb-6 backdrop-blur-md shadow-xl rotate-3 hover:rotate-0 transition-transform duration-300">
                        <div
                            class="w-full h-full bg-white/20 rounded-[1.5rem] flex items-center justify-center -rotate-3 transition-transform hover:rotate-0 duration-300">
                            <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                {!! $icon !!}
                            </svg>
                        </div>
                    </div>

                    @if(session('success'))
                        <h1 class="text-3xl md:text-4xl font-black mb-4 tracking-tight">{{ session('success') }}</h1>
                    @elseif(session('error'))
                        <h1 class="text-3xl md:text-4xl font-black mb-4 tracking-tight">{{ session('error') }}</h1>
                    @else
                        <h1 class="text-3xl md:text-4xl font-black mb-4 tracking-tight">وضعیت نوبت</h1>
                    @endif

                    <div
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-white/20 backdrop-blur-md text-sm font-medium mt-2 shadow-sm border border-white/20">
                        <span>وضعیت نوبت شما:</span>
                        <span class="font-black">{{ $statusData['label'] }}</span>
                    </div>
                </div>
            </div>

            {{-- جزئیات نوبت --}}
            <div class="p-6 md:p-10 space-y-10 relative">

                <div>
                    <div class="flex items-center gap-3 mb-6 border-b border-gray-100 dark:border-gray-800/60 pb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                            جزئیات نوبت <span
                                class="text-gray-400 dark:text-gray-500 text-lg">#{{ $appointment->id }}</span>
                        </h3>
                    </div>
                    <p class="mb-6" style="font-size: 15px; line-height: 2em;">
                        <span style="display:block; margin-bottom: 10px;">
                            زمان تعیین‌شده برای ویزیت قطعی نیست و معمولاً ممکن است با کمی تأخیر همراه باشد.
                        </span>

                        <span style="display:block; margin-bottom: 10px;">
                            گاهی برای دقت بیشتر در روند ویزیت، مدت زمان معاینه طولانی‌تر می‌شود.
                        </span>

                        <span style="display:block; margin-bottom: 10px;">
                            مطمئن باشید برای شما نیز همین میزان توجه و دقت در نظر گرفته خواهد شد.
                        </span>

                        <span style="display:block;">
                            از صبوری و همراهی شما سپاسگزاریم.
                        </span>
                    </p>

                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-gray-50/50 dark:bg-gray-900/50 p-6 md:p-8 rounded-[1.5rem] border border-gray-200/60 dark:border-gray-700/50 shadow-inner">
                        <div class="space-y-1.5">
                            <div class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                سرویس
                            </div>
                            <div
                                class="font-black text-gray-900 dark:text-white text-lg">{{ $appointment->service->name ?? '---' }}</div>
                        </div>

                        <div class="space-y-1.5">
                            <div
                                class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ config('booking.labels.provider', 'ارائه‌دهنده') }}</div>
                            <div
                                class="font-black text-gray-900 dark:text-white text-lg">{{ $appointment->provider->name ?? $appointment->provider->full_name ?? '---' }}</div>
                        </div>

                        <div
                            class="space-y-2 sm:col-span-2 pt-6 mt-2 border-t border-gray-200/60 dark:border-gray-700/50">
                            <div class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                تاریخ و زمان نوبت
                            </div>
                            <div
                                class="inline-flex items-center gap-4 bg-white dark:bg-gray-800 px-5 py-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm w-full">
                                <div
                                    class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="font-black text-gray-900 dark:text-white inline-block text-xl">
                                    @php
                                        $scheduleTz = config('booking.timezones.display_default', 'Asia/Tehran');
                                        $startLocal = \Carbon\Carbon::parse($appointment->start_at_utc)->timezone($scheduleTz);
                                        $endLocal = \Carbon\Carbon::parse($appointment->end_at_utc)->timezone($scheduleTz);

                                        try {
                                            $jDate = \Morilog\Jalali\Jalalian::fromCarbon($startLocal);
                                            $dateStr = $jDate->format('Y/m/d');
                                        } catch (\Exception $e) {
                                            $dateStr = $startLocal->format('Y/m/d');
                                        }
                                    @endphp
                                    {{ $dateStr }} <span
                                        class="text-gray-300 dark:text-gray-600 mx-3 font-normal">|</span> {{ $startLocal->format('H:i') }}
                                    <span
                                        class="font-normal text-sm text-gray-400 mx-2">تا</span> {{ $endLocal->format('H:i') }}
                                </div>
                            </div>
                        </div>

                        <div
                            class="space-y-1.5 sm:col-span-2 pt-6 mt-2 border-t border-gray-200/60 dark:border-gray-700/50">
                            <div class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                ثبت‌کننده
                            </div>
                            <div
                                class="font-bold text-gray-900 dark:text-white text-lg">{{ $appointment->client->full_name ?? '---' }}</div>
                        </div>
                    </div>
                </div>

                {{-- اطلاعات پرداخت --}}
                @if($appointment->payments->isNotEmpty())
                    <div>
                        <div class="flex items-center gap-3 mb-6 border-b border-gray-100 dark:border-gray-800/60 pb-4">
                            <div
                                class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                                وضعیت مالی و پرداخت
                            </h3>
                        </div>

                        <div class="space-y-4">
                            @foreach($appointment->payments as $payment)
                                <div
                                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-5 p-6 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50 shadow-sm relative overflow-hidden group">
                                    <div
                                        class="absolute top-0 right-0 w-1.5 h-full {{ $payment->status === 'PAID' ? 'bg-emerald-500' : ($payment->status === 'FAILED' ? 'bg-rose-500' : 'bg-amber-500') }}"></div>

                                    <div class="pr-2">
                                        <div class="flex items-baseline gap-1.5 mb-2">
                                            <span
                                                class="font-black text-3xl text-gray-900 dark:text-white">{{ number_format((float)$payment->amount) }}</span>
                                            <span
                                                class="text-sm font-bold text-gray-500 dark:text-gray-400">{{ $currencyMap[$payment->currency_unit] ?? $payment->currency_unit }}</span>
                                        </div>
                                        <div
                                            class="text-sm text-gray-500 dark:text-gray-400 flex flex-wrap gap-x-5 gap-y-2">
                                            <span>نوع پرداخت: <span
                                                    class="font-bold text-gray-700 dark:text-gray-300">{{ ($payment->mode ?? '') === 'REQUIRED' ? 'درگاه آنلاین' : 'پرداخت اختیاری/پیش‌پرداخت' }}</span></span>
                                            @if($payment->transaction_ref)
                                                <span class="flex items-center gap-1.5">کد پیگیری: <span
                                                        class="font-mono bg-white dark:bg-gray-800 px-2.5 py-1 rounded-md text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700 shadow-sm">{{ $payment->transaction_ref }}</span></span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="shrink-0 flex flex-col items-start sm:items-end gap-3">
                                        @if($payment->status === 'PAID')
                                            <span
                                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-black bg-emerald-100 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:border-emerald-800/50 dark:text-emerald-400 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M5 13l4 4L19 7"/></svg>
                                            پرداخت شده
                                        </span>
                                        @elseif($payment->status === 'FAILED')
                                            <span
                                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-black bg-rose-100 text-rose-700 border border-rose-200 dark:bg-rose-900/30 dark:border-rose-800/50 dark:text-rose-400 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M6 18L18 6M6 6l12 12"/></svg>
                                            ناموفق
                                        </span>
                                        @elseif($payment->status === 'CANCELLED')
                                            <span
                                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-black bg-gray-200 text-gray-700 border border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            لغو شده
                                        </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-black bg-amber-100 text-amber-700 border border-amber-200 dark:bg-amber-900/30 dark:border-amber-800/50 dark:text-amber-400 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            در انتظار پرداخت
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- دکمه پرداخت مجدد در صورت ناموفق بودن --}}
                                @if($payment->status === 'FAILED' || $payment->status === 'CANCELLED')
                                    <div
                                        class="p-6 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl border border-indigo-100 dark:border-indigo-800/50 flex flex-col sm:flex-row items-center justify-between gap-5 animate-in fade-in slide-in-from-top-4 shadow-sm">
                                        <div class="flex items-center gap-4 text-indigo-900 dark:text-indigo-300">
                                            <div
                                                class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-800/50 flex items-center justify-center shrink-0">
                                                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <span class="text-base font-bold">آیا مایل به تلاش مجدد برای رزرو و پرداخت هستید؟</span>
                                        </div>
                                        <a href="{{ route('booking.public.service', $appointment->service_id) }}"
                                           class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl border border-transparent bg-indigo-600 text-white text-sm font-black hover:bg-indigo-700 transition-all hover:-translate-y-1 shadow-xl shadow-indigo-600/30 shrink-0 w-full sm:w-auto group">
                                            <svg
                                                class="w-5 h-5 group-hover:rotate-180 transition-transform duration-500"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            بازگشت و رزرو مجدد
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <div
                    class="pt-8 mt-6 border-t border-gray-100 dark:border-gray-800/60 flex flex-col sm:flex-row items-center justify-between gap-5">
                    <a href="{{ route('booking.public.index') }}"
                       class="text-base font-bold text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors flex items-center gap-2 group px-5 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-900/30 w-full sm:w-auto justify-center">
                        {{-- فلش به سمت راست (Right Arrow) برای بازگشت در صفحات RTL --}}
                        <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                        بازگشت به لیست سرویس‌ها
                    </a>

                    @if(Auth::guard('client')->check())
                        <a href="{{ url('/clients/dashboard') }}"
                           class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-white font-black hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-md w-full sm:w-auto">
                            ورود به پنل کاربری
                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
