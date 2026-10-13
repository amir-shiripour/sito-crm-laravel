@php use Morilog\Jalali\CalendarUtils; @endphp
@extends('clients::layouts.client')

@php
    $title = 'جزئیات و ووچر رزرو ' . $booking->booking_code;
    $badge = $booking->status_badge ?? ['label' => $booking->status_label ?? 'نامشخص', 'class' => 'bg-gray-100 text-gray-700'];
    $payBadge = $booking->payment_status_badge ?? ['label' => $booking->payment_status_label ?? 'نامشخص', 'class' => 'bg-gray-100 text-gray-700'];
    $isConfirmed = in_array($booking->status, [
        \Modules\Properties\Entities\PropertyRentalBooking::STATUS_CONFIRMED,
        \Modules\Properties\Entities\PropertyRentalBooking::STATUS_CHECKED_IN,
        \Modules\Properties\Entities\PropertyRentalBooking::STATUS_COMPLETED
    ]);
    $canPay = in_array($booking->status, [
        \Modules\Properties\Entities\PropertyRentalBooking::STATUS_AWAITING_PAYMENT,
        \Modules\Properties\Entities\PropertyRentalBooking::STATUS_PENDING_HOST
    ]) && $booking->payment_status !== 'paid';

    $canCancel = in_array($booking->status, [
        \Modules\Properties\Entities\PropertyRentalBooking::STATUS_PENDING_HOST,
        \Modules\Properties\Entities\PropertyRentalBooking::STATUS_AWAITING_PAYMENT,
        \Modules\Properties\Entities\PropertyRentalBooking::STATUS_CONFIRMED
    ]);

    $coverImage = $booking->property?->primary_image_url ?? $booking->property?->primaryImage?->image_url ?? asset('assets/images/placeholder.jpg');
    $propUrl = $booking->property ? route('properties.show', $booking->property->slug ?: $booking->property->code) : '#';
@endphp

@section('content')
    <div x-data="{
            showCancelModal: false,
            cancelReason: '',
            cancelLoading: false,
            showPayModal: false,
            payLoading: false,
            submitCancel() {
                if (!this.cancelReason.trim()) {
                    alert('لطفاً دلیل لغو را وارد فرمایید.');
                    return;
                }
                this.cancelLoading = true;
                fetch('{{ route('client.rental-bookings.cancel', $booking->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ cancellation_reason: this.cancelReason })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        window.location.reload();
                    } else {
                        alert(res.message || 'خطا در لغو رزرو');
                        this.cancelLoading = false;
                    }
                })
                .catch(err => {
                    alert('خطای ارتباط با سرور');
                    this.cancelLoading = false;
                });
            },
            submitPayment() {
                this.payLoading = true;
                fetch('{{ route('client.rental-bookings.pay', $booking->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ payment_method: 'online' })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        window.location.reload();
                    } else {
                        alert(res.message || 'خطا در ثبت پرداخت');
                        this.payLoading = false;
                    }
                })
                .catch(err => {
                    alert('خطای ارتباط با سرور');
                    this.payLoading = false;
                });
            }
         }" 
         class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700 font-sans">

        {{-- نوار ابزار بالا --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('client.rental-bookings.index') }}"
                   class="p-2 -m-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-gray-800 transition-colors"
                   title="بازگشت به لیست رزروها">
                    <svg class="w-6 h-6 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <span>سند و ووچر رزرو</span>
                        <span class="text-indigo-600 dark:text-indigo-400 font-black">{{ $booking->booking_code }}</span>
                    </h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        ثبت شده در تاریخ {{ CalendarUtils::convertNumbers(jdate($booking->created_at)->format('Y/m/d ساعت H:i')) }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('client.rental-bookings.print', $booking->id) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl transition text-xs font-bold shadow-xs">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>چاپ ووچر دیجیتال</span>
                </a>

                @if($canPay)
                    <button type="button" 
                            @click="showPayModal = true"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition text-xs font-black shadow-md shadow-emerald-600/20 active:scale-95">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>پرداخت آنلاین ({{ CalendarUtils::convertNumbers(number_format($booking->total_price)) }} {{ $booking->currency === 'IRR' ? 'ریال' : 'تومان' }})</span>
                    </button>
                @endif

                @if($canCancel)
                    <button type="button" 
                            @click="showCancelModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-50 hover:bg-red-100 dark:bg-red-950/40 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 rounded-xl transition text-xs font-bold">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>انصراف و لغو رزرو</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- بنر وضعیت رزرو --}}
        <div class="rounded-3xl p-6 {{ $badge['class'] ?? '' }} border flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/40 dark:bg-black/20 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-black">وضعیت رزرو: {{ $badge['label'] ?? '' }}</h3>
                    <p class="text-xs opacity-90 mt-0.5">
                        @if($booking->status === \Modules\Properties\Entities\PropertyRentalBooking::STATUS_PENDING_HOST)
                            درخواست شما به میزبان ارسال شده و در انتظار بررسی و تأیید نهایی می‌باشد.
                        @elseif($booking->status === \Modules\Properties\Entities\PropertyRentalBooking::STATUS_AWAITING_PAYMENT)
                            درخواست شما توسط میزبان تأیید شد. لطفاً جهت قطعی شدن رزرو، مبلغ را پرداخت نمایید.
                        @elseif($isConfirmed)
                            رزرو شما قطعی است. می‌توانید در تاریخ مقرر با هماهنگی میزبان در محل اقامتگاه حاضر شوید.
                        @elseif(str_starts_with($booking->status, 'cancelled'))
                            این رزرو لغو شده است. علت لغو: {{ $booking->cancellation_reason ?: 'عدم ارائه علت' }}
                        @else
                            وضعیت کنونی رزرو: {{ $badge['label'] ?? '' }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 self-end md:self-auto">
                <span class="px-3 py-1 rounded-xl bg-white/50 dark:bg-black/30 text-xs font-black">
                    وضعیت مالی: {{ $payBadge['label'] ?? '' }}
                </span>
            </div>
        </div>

        {{-- بدنه اصلی اطلاعات و فاکتور --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ستون راست: اطلاعات اقامتگاه و میزبان (2 ستون) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- کارت معرفی اقامتگاه --}}
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700/80 shadow-xs space-y-5">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-4">
                        <h2 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                            <span>اطلاعات اقامتگاه</span>
                        </h2>
                        <a href="{{ $propUrl }}" target="_blank" class="text-xs font-black text-indigo-600 dark:text-indigo-400 hover:underline">
                            مشاهده صفحه عمومی اقامتگاه &larr;
                        </a>
                    </div>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                        <img src="{{ $coverImage }}" alt="{{ $booking->property?->title ?? 'اقامتگاه' }}" class="w-full sm:w-36 h-32 rounded-2xl object-cover border border-gray-200 dark:border-gray-700 shadow-xs">
                        <div class="space-y-2 flex-1">
                            <span class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 text-[11px] font-bold">
                                کد ملک: {{ $booking->property?->code ?? '---' }}
                            </span>
                            <h3 class="text-base font-black text-gray-900 dark:text-white">
                                {{ $booking->property?->title ?? 'اقامتگاه' }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                {{ $booking->property?->city ?? 'شهر' }} - {{ $booking->property?->neighborhood ?? 'منطقه' }}
                            </p>
                        </div>
                    </div>

                    {{-- کارت آدرس دقیق و نقشه بر اساس وضعیت قطعی بودن --}}
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200/80 dark:border-gray-700/80 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>آدرس و موقعیت مکانی:</span>
                            </span>
                            @if($isConfirmed)
                                <span class="px-2 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold">
                                    تأیید شده و نمایان
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 text-[11px] font-bold">
                                    نمایش آدرس دقیق پس از قطعی شدن رزرو
                                </span>
                            @endif
                        </div>

                        <p class="text-xs font-bold text-gray-700 dark:text-gray-300 leading-relaxed">
                            @if($isConfirmed)
                                {{ $booking->property?->address ?: 'آدرس دقیق توسط میزبان ثبت نشده است؛ لطفاً با شماره میزبان تماس حاصل فرمایید.' }}
                            @else
                                {{ $booking->property?->city ?? 'منطقه اقامتگاه' }} (آدرس و پلاک دقیق پس از پرداخت و قطعی شدن رزرو برای شما نمایان می‌شود)
                            @endif
                        </p>
                    </div>
                </div>

                {{-- کارت مشخصات میزبان و مهمان --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- اطلاعات میزبان --}}
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 border border-gray-100 dark:border-gray-700/80 shadow-xs space-y-3">
                        <h3 class="text-xs font-black text-gray-400">اطلاعات میزبان</h3>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-gray-900 dark:text-white">{{ $booking->host?->display_name ?? 'میزبان اقامتگاه' }}</h4>
                                <p class="text-xs text-gray-500 font-bold mt-0.5">
                                    @if($isConfirmed || $booking->status === \Modules\Properties\Entities\PropertyRentalBooking::STATUS_AWAITING_PAYMENT)
                                        تماس: <span class="font-black text-indigo-600 dark:text-indigo-400">{{ $booking->host?->phone ?? '---' }}</span>
                                    @else
                                        شماره تماس پس از تأیید میزبان نمایان می‌شود
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- اطلاعات مهمان اصلی --}}
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 border border-gray-100 dark:border-gray-700/80 shadow-xs space-y-3">
                        <h3 class="text-xs font-black text-gray-400">اطلاعات مهمان اصلی</h3>
                        <div class="space-y-1.5 text-xs font-bold text-gray-700 dark:text-gray-300">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">نام و نام خانوادگی:</span>
                                <span class="text-gray-900 dark:text-white font-black">{{ $booking->guest_full_name }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">شماره موبایل:</span>
                                <span class="text-gray-900 dark:text-white font-black">{{ $booking->guest_phone }}</span>
                            </div>
                            @if($booking->guest_national_code)
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-400">کد ملی:</span>
                                    <span class="text-gray-900 dark:text-white font-black">{{ $booking->guest_national_code }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- یادداشت مهمان --}}
                @if($booking->guest_notes)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700/80 shadow-xs text-xs font-bold space-y-1">
                        <span class="text-gray-400 block">یادداشت و درخواست‌های شما:</span>
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ $booking->guest_notes }}</p>
                    </div>
                @endif

            </div>

            {{-- ستون چپ: فاکتور مالی و بازه اقامت --}}
            <div class="space-y-6">

                {{-- کارت بازه و تقویم اقامت --}}
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700/80 shadow-xs space-y-4">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        برنامه زمان‌بندی اقامت
                    </h3>

                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div class="p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200/60 dark:border-gray-700/60">
                            <span class="text-[11px] font-bold text-gray-400 block">تاریخ ورود</span>
                            <span class="text-sm font-black text-gray-900 dark:text-white mt-1 block">
                                {{ CalendarUtils::convertNumbers($booking->check_in) }}
                            </span>
                        </div>
                        <div class="p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200/60 dark:border-gray-700/60">
                            <span class="text-[11px] font-bold text-gray-400 block">تاریخ خروج</span>
                            <span class="text-sm font-black text-gray-900 dark:text-white mt-1 block">
                                {{ CalendarUtils::convertNumbers($booking->check_out) }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs font-bold text-gray-600 dark:text-gray-300 pt-2 border-t border-gray-100 dark:border-gray-700/60">
                        <span>مدت زمان اقامت:</span>
                        <span class="font-black text-gray-900 dark:text-white">{{ CalendarUtils::convertNumbers($booking->nights_count) }} شب</span>
                    </div>

                    <div class="flex items-center justify-between text-xs font-bold text-gray-600 dark:text-gray-300">
                        <span>تعداد مهمانان:</span>
                        <span class="font-black text-gray-900 dark:text-white">{{ CalendarUtils::convertNumbers($booking->guests_count) }} نفر</span>
                    </div>
                </div>

                {{-- ریز مبالغ صورت‌حساب --}}
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700/80 shadow-xs space-y-4">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700/60 pb-3">
                        جزئیات فاکتور و مبالغ
                    </h3>

                    <div class="space-y-3 text-xs font-bold">
                        <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
                            <span>مبلغ اقامت ({{ CalendarUtils::convertNumbers($booking->nights_count) }} شب):</span>
                            <span class="text-gray-900 dark:text-white">{{ CalendarUtils::convertNumbers(number_format($booking->nights_price)) }}</span>
                        </div>

                        @if($booking->extra_guests_fee > 0)
                            <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
                                <span>هزینه نفرات اضافه:</span>
                                <span class="text-gray-900 dark:text-white">{{ CalendarUtils::convertNumbers(number_format($booking->extra_guests_fee)) }}</span>
                            </div>
                        @endif

                        @if($booking->cleaning_fee > 0)
                            <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
                                <span>هزینه نظافت و خدمات:</span>
                                <span class="text-gray-900 dark:text-white">{{ CalendarUtils::convertNumbers(number_format($booking->cleaning_fee)) }}</span>
                            </div>
                        @endif

                        <div class="pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-sm font-black text-indigo-600 dark:text-indigo-400">
                            <span>مبلغ کل پرداختی:</span>
                            <span>{{ CalendarUtils::convertNumbers(number_format($booking->total_price)) }} {{ $booking->currency === 'IRR' ? 'ریال' : 'تومان' }}</span>
                        </div>

                        <div class="flex items-center justify-between text-xs font-bold pt-1">
                            <span class="text-gray-500">وضعیت پرداخت:</span>
                            <span class="{{ $payBadge['class'] }} px-2 py-0.5 rounded-lg text-[11px] font-black">
                                {{ $payBadge['label'] }}
                            </span>
                        </div>
                    </div>

                    @if($canPay)
                        <div class="pt-3">
                            <button type="button" 
                                    @click="showPayModal = true"
                                    class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs transition shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                <span>تسویه و پرداخت رزرو</span>
                            </button>
                        </div>
                    @endif
                </div>

            </div>

        </div>

        {{-- مدال تأیید پرداخت آنلاین --}}
        <div x-cloak x-show="showPayModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showPayModal = false"></div>
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 space-y-5">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                        <h3 class="text-sm font-black text-gray-900 dark:text-white">تأیید پرداخت و قطعی‌سازی رزرو</h3>
                        <button type="button" @click="showPayModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/40 text-xs font-bold text-indigo-900 dark:text-indigo-300 space-y-2">
                        <div class="flex justify-between">
                            <span>کد رزرو:</span>
                            <span class="font-black">{{ $booking->booking_code }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>مبلغ قابل پرداخت:</span>
                            <span class="font-black text-sm">{{ CalendarUtils::convertNumbers(number_format($booking->total_price)) }} {{ $booking->currency === 'IRR' ? 'ریال' : 'تومان' }}</span>
                        </div>
                    </div>

                    <p class="text-xs text-gray-500 leading-relaxed font-bold">
                        با تأیید پرداخت، رزرو شما به وضعیت قطعی تغییر یافته و تقویم اقامتگاه قفل خواهد شد.
                    </p>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" @click="showPayModal = false" class="w-1/3 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-bold text-xs">
                            انصراف
                        </button>
                        <button type="button" @click="submitPayment()" :disabled="payLoading" class="w-2/3 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md shadow-emerald-600/30 flex items-center justify-center gap-2">
                            <span x-show="!payLoading">پرداخت و ثبت نهایی</span>
                            <span x-show="payLoading">در حال انتقال...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- مدال لغو رزرو --}}
        <div x-cloak x-show="showCancelModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showCancelModal = false"></div>
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                        <h3 class="text-sm font-black text-red-600 dark:text-red-400">لغو و انصراف از رزرو</h3>
                        <button type="button" @click="showCancelModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <p class="text-xs text-gray-600 dark:text-gray-300 font-bold leading-relaxed">
                        آیا از لغو این رزرو اطمینان دارید؟ لطفاً علت انصراف را جهت ثبت در پرونده رزرو وارد نمایید:
                    </p>

                    <div>
                        <textarea x-model="cancelReason" rows="3" placeholder="علت لغو رزرو..." class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3 text-xs font-bold text-gray-900 dark:text-white resize-none font-sans"></textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" @click="showCancelModal = false" class="w-1/3 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-bold text-xs">
                            بازگشت
                        </button>
                        <button type="button" @click="submitCancel()" :disabled="cancelLoading" class="w-2/3 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-black text-xs shadow-md shadow-red-600/30 flex items-center justify-center gap-2">
                            <span x-show="!cancelLoading">تأیید و لغو رزرو</span>
                            <span x-show="cancelLoading">در حال لغو...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
