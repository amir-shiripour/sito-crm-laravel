@php use Morilog\Jalali\CalendarUtils; @endphp
@extends('layouts.user')

@php
    $title = 'مدیریت رزرواسیون میزبان';
    $cardClass = "bg-white dark:bg-gray-800/90 backdrop-blur-md rounded-3xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs overflow-hidden transition-all duration-300";
@endphp

@section('content')
<div class="space-y-6 pb-12 font-sans" x-data="{
        showRejectModal: false,
        rejectActionUrl: '',
        rejectBookingCode: '',
        rejectionReason: '',
        openReject(url, code) {
            this.rejectActionUrl = url;
            this.rejectBookingCode = code;
            this.rejectionReason = '';
            this.showRejectModal = true;
        }
    }">

    {{-- هدر صفحه --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">
                <a href="{{ route('user.properties.hosts.dashboard') }}" class="hover:text-indigo-600 transition">داشبورد میزبانی</a>
                <span>&bull;</span>
                <span class="text-indigo-600 dark:text-indigo-400 font-bold">رزرواسیون</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-indigo-600"></span>
                <span>مدیریت رزروهای اقامتگاه‌های من</span>
            </h1>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('user.properties.hosts.dashboard') }}" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 text-xs font-bold transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>بازگشت به داشبورد</span>
            </a>
        </div>
    </div>

    {{-- تب‌های فیلتر وضعیت و جستجو --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0">
            <a href="{{ route('user.properties.hosts.bookings.index', array_filter(['search' => request('search')])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ !$status || $status === 'all' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-400 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                همه ({{ CalendarUtils::convertNumbers($counts['all'] ?? 0) }})
            </a>
            <a href="{{ route('user.properties.hosts.bookings.index', array_filter(['status' => 'pending_host', 'search' => request('search')])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $status === 'pending_host' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                نیازمند تأیید ({{ CalendarUtils::convertNumbers($counts['pending_host'] ?? 0) }})
            </a>
            <a href="{{ route('user.properties.hosts.bookings.index', array_filter(['status' => 'awaiting_payment', 'search' => request('search')])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $status === 'awaiting_payment' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                در انتظار پرداخت ({{ CalendarUtils::convertNumbers($counts['awaiting_payment'] ?? 0) }})
            </a>
            <a href="{{ route('user.properties.hosts.bookings.index', array_filter(['status' => 'confirmed', 'search' => request('search')])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $status === 'confirmed' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                قطعی و آماده پذیرش ({{ CalendarUtils::convertNumbers($counts['confirmed'] ?? 0) }})
            </a>
            <a href="{{ route('user.properties.hosts.bookings.index', array_filter(['status' => 'checked_in', 'search' => request('search')])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $status === 'checked_in' ? 'bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                مقیم در اقامتگاه ({{ CalendarUtils::convertNumbers($counts['checked_in'] ?? 0) }})
            </a>
            <a href="{{ route('user.properties.hosts.bookings.index', array_filter(['status' => 'completed', 'search' => request('search')])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $status === 'completed' ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                تکمیل شده ({{ CalendarUtils::convertNumbers($counts['completed'] ?? 0) }})
            </a>
        </div>

        <form method="GET" action="{{ route('user.properties.hosts.bookings.index') }}" class="w-full md:w-72">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="جستجوی مهمان، کد یا اقامتگاه..."
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
            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto">در حال حاضر هیچ درخواستی در این بخش ثبت نگردیده است.</p>
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs font-bold">
                    <thead class="bg-gray-50/70 dark:bg-gray-900/50 text-gray-400 border-b border-gray-100 dark:border-gray-700/80">
                        <tr>
                            <th class="py-4 px-5">کد رزرو و اقامتگاه</th>
                            <th class="py-4 px-5">مشخصات مهمان</th>
                            <th class="py-4 px-5">بازه اقامت</th>
                            <th class="py-4 px-5">مبلغ کل و مالی</th>
                            <th class="py-4 px-5">وضعیت</th>
                            <th class="py-4 px-5 text-center">عملیات اقدام</th>
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
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $propCover }}" alt="" class="w-12 h-12 rounded-xl object-cover shrink-0 border border-gray-200 dark:border-gray-700">
                                        <div>
                                            <div class="font-black text-indigo-600 dark:text-indigo-400 text-xs">{{ $booking->booking_code }}</div>
                                            <div class="text-gray-900 dark:text-white font-black line-clamp-1 max-w-[200px] mt-0.5">
                                                {{ $booking->property?->title ?? 'اقامتگاه' }}
                                            </div>
                                            <div class="text-[10px] text-gray-400 mt-0.5">کد ملک: {{ $booking->property?->code ?? '---' }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 px-5">
                                    <div class="space-y-1">
                                        <div class="text-gray-900 dark:text-white font-black">{{ $booking->guest_full_name }}</div>
                                        <div class="text-gray-500 font-bold dir-ltr inline-block text-right">{{ $booking->guest_phone }}</div>
                                        <div class="text-[10px] text-gray-400">{{ CalendarUtils::convertNumbers($booking->guests_count) }} نفر مهمان</div>
                                    </div>
                                </td>

                                <td class="py-4 px-5">
                                    <div class="space-y-1">
                                        <div class="text-gray-900 dark:text-white">
                                            <span class="text-gray-400 text-[10px]">ورود:</span> {{ CalendarUtils::convertNumbers($booking->check_in) }}
                                        </div>
                                        <div class="text-gray-900 dark:text-white">
                                            <span class="text-gray-400 text-[10px]">خروج:</span> {{ CalendarUtils::convertNumbers($booking->check_out) }}
                                        </div>
                                        <div class="text-indigo-600 dark:text-indigo-400 text-[11px] font-black">
                                            {{ CalendarUtils::convertNumbers($booking->nights_count) }} شب
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 px-5">
                                    <div class="space-y-1">
                                        <div class="text-sm font-black text-gray-900 dark:text-white">
                                            {{ CalendarUtils::convertNumbers(number_format($booking->total_price)) }}
                                            <span class="text-[10px] font-normal text-gray-500">{{ $booking->currency === 'IRR' ? 'ریال' : 'تومان' }}</span>
                                        </div>
                                        <div>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black {{ $payBadge['class'] }}">
                                                {{ $payBadge['label'] }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 px-5">
                                    <span class="px-3 py-1 rounded-xl text-xs font-black {{ $badge['class'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </td>

                                <td class="py-4 px-5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- دکمه تأیید میزبان در وضعیت در انتظار --}}
                                        @if($booking->status === \Modules\Properties\Entities\PropertyRentalBooking::STATUS_PENDING_HOST)
                                            <form method="POST" action="{{ route('user.properties.hosts.bookings.approve', $booking->id) }}">
                                                @csrf
                                                <button type="submit" 
                                                        onclick="return confirm('آیا درخواست رزرو {{ $booking->booking_code }} را تأیید می‌کنید؟')"
                                                        class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-[11px] transition shadow-sm">
                                                    تأیید درخواست
                                                </button>
                                            </form>
                                            <button type="button" 
                                                    @click="openReject('{{ route('user.properties.hosts.bookings.reject', $booking->id) }}', '{{ $booking->booking_code }}')"
                                                    class="px-3 py-1.5 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 font-bold text-[11px] transition">
                                                رد درخواست
                                            </button>
                                        @endif

                                        {{-- دکمه ثبت ورود مهمان --}}
                                        @if($booking->status === \Modules\Properties\Entities\PropertyRentalBooking::STATUS_CONFIRMED)
                                            <form method="POST" action="{{ route('user.properties.hosts.bookings.check-in', $booking->id) }}">
                                                @csrf
                                                <button type="submit" 
                                                        onclick="return confirm('آیا ورود و تحویل کلید به مهمان برای رزرو {{ $booking->booking_code }} انجام شد؟')"
                                                        class="px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-[11px] transition shadow-sm">
                                                    ثبت ورود مهمان
                                                </button>
                                            </form>
                                        @endif

                                        {{-- دکمه تکمیل و تحویل نهایی اقامتگاه --}}
                                        @if($booking->status === \Modules\Properties\Entities\PropertyRentalBooking::STATUS_CHECKED_IN)
                                            <form method="POST" action="{{ route('user.properties.hosts.bookings.complete', $booking->id) }}">
                                                @csrf
                                                <button type="submit" 
                                                        onclick="return confirm('آیا اقامت مهمان در اقامتگاه به اتمام رسیده و تحویل انجام گرفت؟')"
                                                        class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-[11px] transition shadow-sm">
                                                    تکمیل اقامت و تحویل
                                                </button>
                                            </form>
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

    {{-- مدال رد درخواست رزرو توسط میزبان --}}
    <div x-cloak x-show="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showRejectModal = false"></div>
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 space-y-4">
                <form :action="rejectActionUrl" method="POST" class="space-y-4">
                    @csrf
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                        <h3 class="text-sm font-black text-red-600 dark:text-red-400 flex items-center gap-2">
                            <span>رد درخواست رزرو</span>
                            <span x-text="rejectBookingCode"></span>
                        </h3>
                        <button type="button" @click="showRejectModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <p class="text-xs text-gray-600 dark:text-gray-300 font-bold leading-relaxed">
                        لطفاً علت عدم امکان پذیرش این رزرو را وارد نمایید (این پیام به مهمان نمایش داده خواهد شد):
                    </p>

                    <div>
                        <textarea name="rejection_reason" x-model="rejectionReason" required rows="3" placeholder="علت رد درخواست (مثال: پر بودن اقامتگاه در این بازه، تعمیرات و ...)" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3 text-xs font-bold text-gray-900 dark:text-white resize-none font-sans"></textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" @click="showRejectModal = false" class="w-1/3 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-bold text-xs">
                            انصراف
                        </button>
                        <button type="submit" class="w-2/3 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-black text-xs shadow-md shadow-red-600/30">
                            تأیید رد رزرو
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
