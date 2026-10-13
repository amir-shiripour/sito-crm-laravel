@php use Morilog\Jalali\CalendarUtils; @endphp
@extends('layouts.user')

@php
    $title = 'مدیریت رزرواسیون اقامتگاه‌ها';
@endphp

@section('content')
<div class="space-y-6 pb-12 font-sans" x-data="{
        showCancelModal: false,
        cancelActionUrl: '',
        cancelBookingCode: '',
        cancelReason: '',
        openCancel(url, code) {
            this.cancelActionUrl = url;
            this.cancelBookingCode = code;
            this.cancelReason = '';
            this.showCancelModal = true;
        }
    }">

    {{-- هدر صفحه --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">
                <a href="{{ route('user.properties.index') }}" class="hover:text-indigo-600 transition">املاک و اقامتگاه‌ها</a>
                <span>&bull;</span>
                <span class="text-indigo-600 dark:text-indigo-400 font-bold">رزرواسیون اقامتگاه</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-indigo-600"></span>
                <span>مدیریت جامع رزرواسیون اقامتگاه‌ها</span>
            </h1>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('user.properties.index') }}" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 text-xs font-bold transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>بازگشت به لیست املاک</span>
            </a>
        </div>
    </div>

    {{-- تب‌های فیلتر و جستجو --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0">
            <a href="{{ route('user.properties.bookings.index', array_filter(['search' => request('search')])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ !$status || $status === 'all' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-400 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                همه ({{ CalendarUtils::convertNumbers($counts['all'] ?? 0) }})
            </a>
            <a href="{{ route('user.properties.bookings.index', array_filter(['status' => 'pending_host', 'search' => request('search')])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $status === 'pending_host' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                در انتظار تأیید میزبان ({{ CalendarUtils::convertNumbers($counts['pending_host'] ?? 0) }})
            </a>
            <a href="{{ route('user.properties.bookings.index', array_filter(['status' => 'awaiting_payment', 'search' => request('search')])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $status === 'awaiting_payment' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                در انتظار پرداخت ({{ CalendarUtils::convertNumbers($counts['awaiting_payment'] ?? 0) }})
            </a>
            <a href="{{ route('user.properties.bookings.index', array_filter(['status' => 'confirmed', 'search' => request('search')])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $status === 'confirmed' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                قطعی ({{ CalendarUtils::convertNumbers($counts['confirmed'] ?? 0) }})
            </a>
            <a href="{{ route('user.properties.bookings.index', array_filter(['status' => 'completed', 'search' => request('search')])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $status === 'completed' ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                تکمیل شده ({{ CalendarUtils::convertNumbers($counts['completed'] ?? 0) }})
            </a>
            <a href="{{ route('user.properties.bookings.index', array_filter(['status' => 'cancelled', 'search' => request('search')])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $status === 'cancelled' ? 'bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-400 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                لغو شده ({{ CalendarUtils::convertNumbers($counts['cancelled'] ?? 0) }})
            </a>
        </div>

        <form method="GET" action="{{ route('user.properties.bookings.index') }}" class="w-full md:w-72">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="جستجوی کد، مهمان، میزبان، ملک..."
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 px-4 py-2 text-xs font-bold text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 transition-all font-sans">
                <button type="submit" class="absolute left-3 top-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>

    {{-- جدول رزروها --}}
    @if($bookings->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 text-center border border-gray-100 dark:border-gray-700 space-y-4">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-base font-black text-gray-900 dark:text-white">هیچ رزروی یافت نشد</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto">هیچ رکوردی منطبق با جستجو یا فیلترهای کنونی در پایگاه داده ثبت نشده است.</p>
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs font-bold">
                    <thead class="bg-gray-50/70 dark:bg-gray-900/50 text-gray-400 border-b border-gray-100 dark:border-gray-700/80">
                        <tr>
                            <th class="py-4 px-5">کد رزرو</th>
                            <th class="py-4 px-5">اقامتگاه</th>
                            <th class="py-4 px-5">مهمان (کلاینت)</th>
                            <th class="py-4 px-5">میزبان</th>
                            <th class="py-4 px-5">بازه اقامت</th>
                            <th class="py-4 px-5">مبلغ کل</th>
                            <th class="py-4 px-5">وضعیت</th>
                            <th class="py-4 px-5 text-center">اقدام</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @foreach($bookings as $booking)
                            @php
                                $badge = $booking->status_badge;
                                $payBadge = $booking->payment_status_badge;
                                $propCover = $booking->property?->primary_image_url ?? $booking->property?->primaryImage?->image_url ?? asset('assets/images/placeholder.jpg');
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition">
                                <td class="py-4 px-5">
                                    <a href="{{ route('user.properties.bookings.show', $booking->id) }}" class="font-black text-indigo-600 dark:text-indigo-400 hover:underline">
                                        {{ $booking->booking_code }}
                                    </a>
                                </td>

                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-2.5">
                                        <img src="{{ $propCover }}" alt="" class="w-10 h-10 rounded-xl object-cover shrink-0 border border-gray-200 dark:border-gray-700">
                                        <div>
                                            <div class="text-gray-900 dark:text-white font-black line-clamp-1 max-w-[180px]">
                                                {{ $booking->property?->title ?? 'اقامتگاه' }}
                                            </div>
                                            <div class="text-[10px] text-gray-400">کد: {{ $booking->property?->code ?? '---' }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 px-5">
                                    <div class="space-y-0.5">
                                        <div class="text-gray-900 dark:text-white font-black">{{ $booking->guest_full_name }}</div>
                                        <div class="text-gray-500 font-bold dir-ltr inline-block text-right text-[11px]">{{ $booking->guest_phone }}</div>
                                        @if($booking->client)
                                            <span class="inline-block text-[10px] text-emerald-600 font-bold">عضو پرتال کلاینت</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="py-4 px-5">
                                    <div class="space-y-0.5">
                                        <div class="text-gray-900 dark:text-white font-black">{{ $booking->host?->display_name ?? '---' }}</div>
                                        <div class="text-gray-400 text-[11px] dir-ltr inline-block text-right">{{ $booking->host?->phone ?? '---' }}</div>
                                    </div>
                                </td>

                                <td class="py-4 px-5">
                                    <div class="space-y-0.5 text-[11px]">
                                        <div class="text-gray-900 dark:text-white">
                                            {{ CalendarUtils::convertNumbers($booking->check_in) }} تا {{ CalendarUtils::convertNumbers($booking->check_out) }}
                                        </div>
                                        <div class="text-indigo-600 dark:text-indigo-400 font-black text-[10px]">
                                            {{ CalendarUtils::convertNumbers($booking->nights_count) }} شب &bull; {{ CalendarUtils::convertNumbers($booking->guests_count) }} نفر
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 px-5">
                                    <div class="space-y-0.5">
                                        <div class="text-xs font-black text-gray-900 dark:text-white">
                                            {{ CalendarUtils::convertNumbers(number_format($booking->total_price)) }}
                                            <span class="text-[10px] font-normal text-gray-500">{{ $booking->currency === 'IRR' ? 'ریال' : 'تومان' }}</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black {{ $payBadge['class'] }}">
                                            {{ $payBadge['label'] }}
                                        </span>
                                    </div>
                                </td>

                                <td class="py-4 px-5">
                                    <span class="px-2.5 py-1 rounded-xl text-xs font-black {{ $badge['class'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </td>

                                <td class="py-4 px-5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('user.properties.bookings.show', $booking->id) }}" class="p-1.5 rounded-lg text-gray-500 hover:text-indigo-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="مشاهده جزئیات">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-12 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>

                                        @if(!in_array($booking->status, [\Modules\Properties\Entities\PropertyRentalBooking::STATUS_COMPLETED, \Modules\Properties\Entities\PropertyRentalBooking::STATUS_CANCELLED_BY_ADMIN, \Modules\Properties\Entities\PropertyRentalBooking::STATUS_CANCELLED_BY_HOST, \Modules\Properties\Entities\PropertyRentalBooking::STATUS_CANCELLED_BY_GUEST]))
                                            <button type="button" 
                                                    @click="openCancel('{{ route('user.properties.bookings.cancel', $booking->id) }}', '{{ $booking->booking_code }}')"
                                                    class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 transition" title="لغو رزرو">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700/80">
                {{ $bookings->links() }}
            </div>
        </div>
    @endif

    {{-- مدال لغو رزرو توسط ادمین --}}
    <div x-cloak x-show="showCancelModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showCancelModal = false"></div>
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 space-y-4">
                <form :action="cancelActionUrl" method="POST" class="space-y-4">
                    @csrf
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                        <h3 class="text-sm font-black text-red-600 dark:text-red-400 flex items-center gap-2">
                            <span>لغو رزرو توسط مدیریت:</span>
                            <span x-text="cancelBookingCode"></span>
                        </h3>
                        <button type="button" @click="showCancelModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <p class="text-xs text-gray-600 dark:text-gray-300 font-bold leading-relaxed">
                        با لغو این رزرو، بلاک تاریخ‌های مربوطه در تقویم اقامتگاه حذف و تقویم آزاد خواهد شد. لطفاً علت لغو را درج فرمایید:
                    </p>

                    <div>
                        <textarea name="cancellation_reason" x-model="cancelReason" required rows="3" placeholder="علت لغو توسط مدیر..." class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3 text-xs font-bold text-gray-900 dark:text-white resize-none font-sans"></textarea>
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
