@php use Morilog\Jalali\CalendarUtils; @endphp
@extends('layouts.user')

@php
    $title = 'جزئیات رزرو ' . $booking->booking_code;
    $badge = $booking->status_badge;
    $payBadge = $booking->payment_status_badge;
    $propCover = $booking->property?->primary_image_url ?? $booking->property?->primaryImage?->image_url ?? asset('assets/images/placeholder.jpg');
    $canConfirm = in_array($booking->status, [
        \Modules\Properties\Entities\PropertyRentalBooking::STATUS_PENDING_HOST,
        \Modules\Properties\Entities\PropertyRentalBooking::STATUS_AWAITING_PAYMENT
    ]);
@endphp

@section('content')
<div class="space-y-6 pb-12 font-sans" x-data="{
        showConfirmModal: false,
        showCancelModal: false,
        cancelReason: '',
        paymentMethod: 'bank_transfer',
        transactionId: '',
        adminNote: ''
    }">

    {{-- هدر صفحه --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('user.properties.bookings.index') }}"
               class="p-2 -m-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-gray-800 transition-colors">
                <svg class="w-6 h-6 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <span>پرونده رزرواسیون:</span>
                    <span class="text-indigo-600 dark:text-indigo-400">{{ $booking->booking_code }}</span>
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    ثبت شده در {{ CalendarUtils::convertNumbers(jdate($booking->created_at)->format('Y/m/d H:i')) }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if($canConfirm)
                <button type="button" @click="showConfirmModal = true" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs transition shadow-md shadow-emerald-600/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>تأیید دستی و قطعی‌سازی رزرو</span>
                </button>
            @endif

            @if(!in_array($booking->status, [\Modules\Properties\Entities\PropertyRentalBooking::STATUS_COMPLETED, \Modules\Properties\Entities\PropertyRentalBooking::STATUS_CANCELLED_BY_ADMIN]))
                <button type="button" @click="showCancelModal = true" class="px-4 py-2.5 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-950/40 dark:hover:bg-red-900/50 dark:text-red-400 text-xs font-bold transition flex items-center gap-2 border border-red-200 dark:border-red-800">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>لغو رزرو توسط مدیریت</span>
                </button>
            @endif
        </div>
    </div>

    {{-- وضعیت و بنر --}}
    <div class="rounded-3xl p-6 {{ $badge['class'] }} border flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-black">وضعیت رزرو: {{ $badge['label'] }}</h3>
            <p class="text-xs opacity-90 mt-1">
                @if($booking->cancellation_reason)
                    علت لغو: {{ $booking->cancellation_reason }}
                @else
                    شناسه تراکنش: {{ $booking->transaction_id ?: 'ثبت نشده' }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-xl bg-white/50 dark:bg-black/30 text-xs font-black">
                پرداخت: {{ $payBadge['label'] }}
            </span>
        </div>
    </div>

    {{-- کارت‌های اطلاعات --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- مشخصات اقامتگاه و مهمان --}}
        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700/80 shadow-xs space-y-4">
                <h3 class="text-sm font-black text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700/60 pb-3">اقامتگاه مرتبط</h3>
                <div class="flex items-center gap-4">
                    <img src="{{ $propCover }}" alt="" class="w-24 h-24 rounded-2xl object-cover border border-gray-200 dark:border-gray-700">
                    <div class="space-y-1.5 flex-1">
                        <div class="font-black text-base text-gray-900 dark:text-white">{{ $booking->property?->title ?? 'اقامتگاه' }}</div>
                        <div class="text-xs text-gray-400">کد ملک: {{ $booking->property?->code ?? '---' }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-300">
                            آدرس: {{ $booking->property?->address ?: ($booking->property?->city . ' - ' . $booking->property?->neighborhood) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- مهمان / مشتری --}}
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 border border-gray-100 dark:border-gray-700/80 shadow-xs space-y-3">
                    <h4 class="text-xs font-black text-gray-400">مشخصات مهمان (کلاینت)</h4>
                    <div class="space-y-2 text-xs font-bold text-gray-700 dark:text-gray-300">
                        <div class="flex justify-between">
                            <span class="text-gray-400">نام کامل:</span>
                            <span class="font-black text-gray-900 dark:text-white">{{ $booking->guest_full_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">شماره موبایل:</span>
                            <span class="font-black dir-ltr">{{ $booking->guest_phone }}</span>
                        </div>
                        @if($booking->guest_national_code)
                            <div class="flex justify-between">
                                <span class="text-gray-400">کد ملی:</span>
                                <span>{{ $booking->guest_national_code }}</span>
                            </div>
                        @endif
                        @if($booking->client)
                            <div class="pt-2 border-t border-gray-100 dark:border-gray-700/60 flex justify-between items-center text-indigo-600 dark:text-indigo-400">
                                <span>پروفایل مشتری در پرتال:</span>
                                <a href="{{ route('user.clients.show', $booking->client->id) }}" class="underline text-[11px] font-black">مشاهده پرونده مشتری &larr;</a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- میزبان --}}
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 border border-gray-100 dark:border-gray-700/80 shadow-xs space-y-3">
                    <h4 class="text-xs font-black text-gray-400">مشخصات میزبان اقامتگاه</h4>
                    <div class="space-y-2 text-xs font-bold text-gray-700 dark:text-gray-300">
                        <div class="flex justify-between">
                            <span class="text-gray-400">نام نمایشی:</span>
                            <span class="font-black text-gray-900 dark:text-white">{{ $booking->host?->display_name ?? 'مدیریت مجموعه' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">تلفن تماس:</span>
                            <span class="font-black dir-ltr">{{ $booking->host?->phone ?? '---' }}</span>
                        </div>
                        @if($booking->host?->shaba_number)
                            <div class="flex justify-between">
                                <span class="text-gray-400">شماره شبا:</span>
                                <span class="dir-ltr text-[11px]">{{ $booking->host->shaba_number }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($booking->guest_notes)
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 text-xs font-bold space-y-1">
                    <span class="text-gray-400 block">یادداشت ثبت شده توسط مهمان:</span>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ $booking->guest_notes }}</p>
                </div>
            @endif

        </div>

        {{-- ستون مالی و بازه اقامت --}}
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700/80 shadow-xs space-y-4">
                <h3 class="text-sm font-black text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700/60 pb-3">بازه اقامت</h3>
                <div class="space-y-3 text-xs font-bold">
                    <div class="flex justify-between">
                        <span class="text-gray-400">تاریخ ورود:</span>
                        <span class="font-black text-gray-900 dark:text-white">{{ CalendarUtils::convertNumbers($booking->check_in) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">تاریخ خروج:</span>
                        <span class="font-black text-gray-900 dark:text-white">{{ CalendarUtils::convertNumbers($booking->check_out) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">تعداد شب:</span>
                        <span class="font-black text-indigo-600 dark:text-indigo-400">{{ CalendarUtils::convertNumbers($booking->nights_count) }} شب</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">تعداد مهمانان:</span>
                        <span class="font-black">{{ CalendarUtils::convertNumbers($booking->guests_count) }} نفر</span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700/80 shadow-xs space-y-4">
                <h3 class="text-sm font-black text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700/60 pb-3">صورت‌حساب مالی</h3>
                <div class="space-y-3 text-xs font-bold">
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>هزینه شب‌ها:</span>
                        <span>{{ CalendarUtils::convertNumbers(number_format($booking->nights_price)) }}</span>
                    </div>
                    @if($booking->extra_guests_fee > 0)
                        <div class="flex justify-between text-gray-600 dark:text-gray-300">
                            <span>هزینه نفر اضافه:</span>
                            <span>{{ CalendarUtils::convertNumbers(number_format($booking->extra_guests_fee)) }}</span>
                        </div>
                    @endif
                    @if($booking->cleaning_fee > 0)
                        <div class="flex justify-between text-gray-600 dark:text-gray-300">
                            <span>هزینه خدمات:</span>
                            <span>{{ CalendarUtils::convertNumbers(number_format($booking->cleaning_fee)) }}</span>
                        </div>
                    @endif
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700/60 flex justify-between text-sm font-black text-indigo-600 dark:text-indigo-400">
                        <span>مبلغ کل:</span>
                        <span>{{ CalendarUtils::convertNumbers(number_format($booking->total_price)) }} {{ $booking->currency === 'IRR' ? 'ریال' : 'تومان' }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- مدال تأیید و تسویه دستی --}}
    <div x-cloak x-show="showConfirmModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showConfirmModal = false"></div>
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 space-y-4">
                <form action="{{ route('user.properties.bookings.confirm', $booking->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                        <h3 class="text-sm font-black text-gray-900 dark:text-white">تأیید دستی و قطعی‌سازی رزرو</h3>
                        <button type="button" @click="showConfirmModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-3 text-xs font-bold">
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-1">روش پرداخت:</label>
                            <select name="payment_method" x-model="paymentMethod" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-2.5 text-xs font-bold text-gray-900 dark:text-white font-sans">
                                <option value="bank_transfer">کارت به کارت / حواله بانکی</option>
                                <option value="pos">کارتخوان حضوری (POS)</option>
                                <option value="cash">نقدی / تحویل حضوری</option>
                                <option value="online">درگاه آنلاین</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-1">شماره پیگیری / ارجاع:</label>
                            <input type="text" name="transaction_id" x-model="transactionId" placeholder="مثال: ۱۰۵۹۲۸" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-2.5 text-xs font-bold text-gray-900 dark:text-white font-sans">
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-1">یادداشت اداری:</label>
                            <textarea name="admin_note" x-model="adminNote" rows="2" placeholder="توضیحات اختیاری..." class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-2 text-xs font-bold text-gray-900 dark:text-white font-sans resize-none"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" @click="showConfirmModal = false" class="w-1/3 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-bold text-xs">
                            انصراف
                        </button>
                        <button type="submit" class="w-2/3 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md shadow-emerald-600/30">
                            تأیید و قفل تقویم
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- مدال لغو رزرو توسط مدیریت --}}
    <div x-cloak x-show="showCancelModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showCancelModal = false"></div>
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 space-y-4">
                <form action="{{ route('user.properties.bookings.cancel', $booking->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                        <h3 class="text-sm font-black text-red-600 dark:text-red-400">لغو رزرو توسط مدیریت</h3>
                        <button type="button" @click="showCancelModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 mb-1 text-xs font-bold">دلیل لغو:</label>
                        <textarea name="cancellation_reason" x-model="cancelReason" required rows="3" placeholder="علت لغو توسط مدیر..." class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-2.5 text-xs font-bold text-gray-900 dark:text-white font-sans resize-none"></textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" @click="showCancelModal = false" class="w-1/3 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-bold text-xs">
                            انصراف
                        </button>
                        <button type="submit" class="w-2/3 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-black text-xs shadow-md shadow-red-600/30">
                            تأیید لغو رزرو
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
