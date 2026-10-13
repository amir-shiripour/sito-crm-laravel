@php use Morilog\Jalali\CalendarUtils; @endphp
@extends('clients::layouts.client')

@php
    $title = 'رزروهای اقامتگاهی من';
@endphp

@section('content')
    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700 font-sans">

        {{-- هدر صفحه --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <a href="{{ route('client.dashboard') }}"
                       class="p-2 -m-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                    رزروهای اقامتگاهی من
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">مشاهده و پیگیری درخواست‌ها، ووچر سفر، وضعیت تأیید میزبان و پرداخت</p>
            </div>
            <div>
                <a href="{{ route('properties.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-md shadow-indigo-600/20">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>جستجو و رزرو اقامتگاه جدید</span>
                </a>
            </div>
        </div>

        {{-- تب‌های فیلتر و جستجو --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0">
                <a href="{{ route('client.rental-bookings.index', array_filter(['search' => request('search')])) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ !$status || $status === 'all' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    همه ({{ CalendarUtils::convertNumbers($counts['all'] ?? 0) }})
                </a>
                <a href="{{ route('client.rental-bookings.index', array_filter(['status' => 'pending_host', 'search' => request('search')])) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $status === 'pending_host' ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    در انتظار تأیید میزبان ({{ CalendarUtils::convertNumbers($counts['pending_host'] ?? 0) }})
                </a>
                <a href="{{ route('client.rental-bookings.index', array_filter(['status' => 'awaiting_payment', 'search' => request('search')])) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $status === 'awaiting_payment' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    در انتظار پرداخت ({{ CalendarUtils::convertNumbers($counts['awaiting_payment'] ?? 0) }})
                </a>
                <a href="{{ route('client.rental-bookings.index', array_filter(['status' => 'confirmed', 'search' => request('search')])) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $status === 'confirmed' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    قطعی و تأیید شده ({{ CalendarUtils::convertNumbers($counts['confirmed'] ?? 0) }})
                </a>
                <a href="{{ route('client.rental-bookings.index', array_filter(['status' => 'history', 'search' => request('search')])) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ $status === 'history' ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 font-black shadow-xs' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    تاریخچه و لغو شده ({{ CalendarUtils::convertNumbers($counts['history'] ?? 0) }})
                </a>
            </div>

            <form method="GET" action="{{ route('client.rental-bookings.index') }}" class="w-full md:w-72">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="جستجوی کد رزرو یا اقامتگاه..."
                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 px-4 py-2 text-xs font-bold text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 transition-all font-sans">
                    <button type="submit" class="absolute left-3 top-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        {{-- لیست رزروها --}}
        @if($bookings->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 text-center border border-gray-100 dark:border-gray-700 space-y-4">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
                <h3 class="text-base font-black text-gray-900 dark:text-white">هیچ رزروی یافت نشد</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto">شما در حال حاضر هیچ درخواستی در این بخش ثبت نکرده‌اید یا با فیلترهای انتخابی موردی تطابق ندارد.</p>
                <div class="pt-2">
                    <a href="{{ route('properties.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition">
                        مشاهده لیست اقامتگاه‌ها
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4">
                @foreach($bookings as $booking)
                    @php
                        $coverImage = $booking->property?->primary_image_url ?? $booking->property?->primaryImage?->image_url ?? asset('assets/images/placeholder.jpg');
                        $propUrl = $booking->property ? route('properties.show', $booking->property->slug ?: $booking->property->code) : '#';
                        $showUrl = route('client.rental-bookings.show', $booking->id);
                        $badge = $booking->status_badge ?: ['label' => $booking->status_label ?? 'نامشخص', 'class' => 'bg-gray-100 text-gray-700'];
                    @endphp
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/80 shadow-xs hover:shadow-md transition-all flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
                        
                        {{-- تصویر و اطلاعات اصلی اقامتگاه --}}
                        <div class="flex items-center gap-4 w-full md:w-auto">
                            <img src="{{ $coverImage }}" alt="{{ $booking->property?->title ?? 'اقامتگاه' }}" class="w-20 h-20 rounded-2xl object-cover shrink-0 border border-gray-200 dark:border-gray-700">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-gray-400">کد رزرو:</span>
                                    <span class="text-xs font-black text-indigo-600 dark:text-indigo-400">{{ $booking->booking_code }}</span>
                                </div>
                                <h3 class="text-sm font-black text-gray-900 dark:text-white line-clamp-1">
                                    <a href="{{ $showUrl }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">{{ $booking->property?->title ?? 'اقامتگاه حذف شده' }}</a>
                                </h3>
                                <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400 font-bold">
                                    <span>میزبان: {{ $booking->host?->display_name ?? 'مدیریت مجموعه' }}</span>
                                    <span>&bull;</span>
                                    <span>{{ CalendarUtils::convertNumbers($booking->guests_count) }} مهمان</span>
                                </div>
                            </div>
                        </div>

                        {{-- تاریخ‌ها و اقامت --}}
                        <div class="flex items-center gap-6 text-xs font-bold text-gray-700 dark:text-gray-300 w-full md:w-auto justify-between md:justify-start border-t md:border-t-0 pt-3 md:pt-0 border-gray-100 dark:border-gray-700">
                            <div>
                                <span class="text-gray-400 block text-[11px]">ورود:</span>
                                <span class="font-black text-gray-900 dark:text-white">{{ CalendarUtils::convertNumbers($booking->check_in) }}</span>
                            </div>
                            <div class="text-center">
                                <span class="px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-[10px] font-bold text-gray-500">
                                    {{ CalendarUtils::convertNumbers($booking->nights_count) }} شب
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[11px]">خروج:</span>
                                <span class="font-black text-gray-900 dark:text-white">{{ CalendarUtils::convertNumbers($booking->check_out) }}</span>
                            </div>
                        </div>

                        {{-- مبلغ و وضعیت --}}
                        <div class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-end border-t md:border-t-0 pt-3 md:pt-0 border-gray-100 dark:border-gray-700">
                            <div class="text-left md:text-right">
                                <span class="text-gray-400 block text-[11px]">مبلغ کل:</span>
                                <span class="text-sm font-black text-gray-900 dark:text-white">
                                    {{ CalendarUtils::convertNumbers(number_format($booking->total_price)) }}
                                    <span class="text-[11px] font-normal text-gray-500">{{ $booking->currency === 'IRR' ? 'ریال' : 'تومان' }}</span>
                                </span>
                            </div>

                            <div class="flex flex-col items-end gap-2">
                                <span class="px-3 py-1 rounded-xl text-xs font-bold {{ $badge['class'] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $badge['label'] ?? 'نامشخص' }}
                                </span>
                                <a href="{{ $showUrl }}" class="text-xs font-black text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                    <span>مشاهده و ووچر</span>
                                    <svg class="w-3.5 h-3.5 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>

            {{-- صفحه‌بندی --}}
            <div class="pt-4">
                {{ $bookings->links() }}
            </div>
        @endif

    </div>
@endsection
