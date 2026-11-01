@extends('layouts.web')

@section('title', 'پیش‌فاکتور و تأیید نهایی رزرو - ' . $property->title)

@section('content')
    @php
        use Morilog\Jalali\CalendarUtils;
        use Morilog\Jalali\Jalalian;

        $jCheckIn = Jalalian::fromCarbon(\Carbon\Carbon::parse($quote['check_in_date']));
        $jCheckOut = Jalalian::fromCarbon(\Carbon\Carbon::parse($quote['check_out_date']));
        $checkInDayName = $jCheckIn->format('%A');
        $checkOutDayName = $jCheckOut->format('%A');
        $checkInFormatted = CalendarUtils::convertNumbers($jCheckIn->format('d %B Y'));
        $checkOutFormatted = CalendarUtils::convertNumbers($jCheckOut->format('d %B Y'));

        $coverUrl = $property->cover_image ? (str_starts_with($property->cover_image, 'http') ? $property->cover_image : asset('storage/' . $property->cover_image)) : asset('images/property-placeholder.svg');
        $host = $property->host;
        $hostUser = $host?->user;
        $hostDisplayName = $host?->display_name ?: ($hostUser?->name ?: 'میزبان لفور');

        $checkInTime = $config?->check_in_time ? substr($config->check_in_time, 0, 5) : '۱۴:۰۰';
        $checkOutTime = $config?->check_out_time ? substr($config->check_out_time, 0, 5) : '۱۲:۰۰';

        $isInstant = !empty($quote['instant_booking']);
        $grandTotal = (float) ($quote['grand_total'] ?? 0);
        $prepaymentAmount = (float) ($quote['prepayment_amount'] ?? $grandTotal);

        $clientName = old('guest_full_name', $client->full_name ?: ($client->first_name ? $client->first_name . ' ' . $client->last_name : ''));
        $clientPhone = old('guest_phone', $client->phone ?: '');
        $clientNationalCode = old('guest_national_code', $client->national_code ?: '');
    @endphp

    <div class="max-w-[1360px] mx-auto px-4 sm:px-8 py-8 w-full font-sans">
        
        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-xs font-medium text-zinc-400 mb-6">
            <a href="{{ url('/') }}" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors">مستر ویلا لفور</a>
            <span>/</span>
            <a href="{{ route('properties.show', $property->slug ?: $property->code) }}" class="hover:text-[#007A5E] dark:hover:text-emerald-400 transition-colors line-clamp-1 max-w-[200px]">{{ $property->title }}</a>
            <span>/</span>
            <span class="text-[#18181B] dark:text-zinc-100 font-bold">پیش‌فاکتور و نهایی‌سازی رزرو</span>
        </div>

        <div class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-black text-[#18181B] dark:text-zinc-100 tracking-tight">
                پیش‌فاکتور شفاف و نهایی‌سازی رزرو
            </h1>
            <p class="text-xs text-zinc-400 mt-1">مشخصات مسافر و جزئیات روزهای اقامت را بررسی نموده و رزرو خود را ثبت فرمایید.</p>
        </div>

        {{-- Error & Session Alerts --}}
        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-bold">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-bold space-y-1">
                @foreach($errors->all() as $err)
                    <div>• {{ $err }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('properties.book', $property->slug ?: $property->code) }}" method="POST">
            @csrf
            <input type="hidden" name="check_in_date" value="{{ $checkIn }}">
            <input type="hidden" name="check_out_date" value="{{ $checkOut }}">
            <input type="hidden" name="guests_count" value="{{ $guests }}">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                {{-- Right Column: Guest Information & House Rules --}}
                <div class="lg:col-span-7 space-y-6">

                    {{-- Guest Data Card --}}
                    <div class="border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-3xl p-6 shadow-sm space-y-5">
                        <div class="flex items-center gap-2 text-sm font-black text-[#18181B] dark:text-zinc-100">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#007A5E] dark:bg-emerald-500"></span>
                            <span>اطلاعات مسافر اصلی (تحویل‌گیرنده اقامتگاه)</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="space-y-1.5">
                                <label class="block font-bold text-zinc-700 dark:text-zinc-300">نام و نام خانوادگی <span class="text-rose-500">*</span></label>
                                <input type="text" name="guest_full_name" value="{{ $clientName }}" required class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 focus:border-[#007A5E] rounded-xl px-4 py-3 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none">
                            </div>

                            <div class="space-y-1.5">
                                <label class="block font-bold text-zinc-700 dark:text-zinc-300">شماره موبایل <span class="text-rose-500">*</span></label>
                                <input type="tel" dir="ltr" name="guest_phone" value="{{ $clientPhone }}" required class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 focus:border-[#007A5E] rounded-xl px-4 py-3 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none">
                            </div>

                            <div class="space-y-1.5 sm:col-span-2">
                                <label class="block font-bold text-zinc-700 dark:text-zinc-300">کد ملی مسافر اصلی <span class="text-rose-500">*</span></label>
                                <input type="text" dir="ltr" name="guest_national_code" value="{{ $clientNationalCode }}" required class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 focus:border-[#007A5E] rounded-xl px-4 py-3 text-xs font-bold text-[#18181B] dark:text-zinc-100 outline-none" placeholder="برای ثبت بیمه سفر و احراز هویت تحویل">
                            </div>

                            <div class="space-y-1.5 sm:col-span-2">
                                <label class="block font-bold text-zinc-700 dark:text-zinc-300">توضیحات یا درخواست ویژه به میزبان (اختیاری)</label>
                                <textarea name="notes" rows="3" class="w-full bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 focus:border-[#007A5E] rounded-xl p-4 text-xs font-medium text-[#18181B] dark:text-zinc-100 outline-none" placeholder="ساعت تقریبی ورود، تعداد خودرو و ..."></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Dates and Times Summary Card --}}
                    <div class="border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-3xl p-6 shadow-sm space-y-4">
                        <h3 class="text-sm font-black text-[#18181B] dark:text-zinc-100">زمان‌بندی تحویل و تخلیه اقامتگاه</h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="p-4 rounded-2xl bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 space-y-1">
                                <span class="text-[11px] text-zinc-400 block font-medium">ورود (تحویل)</span>
                                <div class="text-sm font-black text-[#18181B] dark:text-zinc-100">{{ $checkInDayName }}، {{ $checkInFormatted }}</div>
                                <div class="text-[11px] text-[#007A5E] dark:text-emerald-400 font-bold">از ساعت {{ $checkInTime }} به بعد</div>
                            </div>

                            <div class="p-4 rounded-2xl bg-[#F7F8F9] dark:bg-zinc-800 border border-[#ECECEE] dark:border-zinc-700 space-y-1">
                                <span class="text-[11px] text-zinc-400 block font-medium">خروج (تخلیه)</span>
                                <div class="text-sm font-black text-[#18181B] dark:text-zinc-100">{{ $checkOutDayName }}، {{ $checkOutFormatted }}</div>
                                <div class="text-[11px] text-rose-600 dark:text-rose-400 font-bold">تا ساعت {{ $checkOutTime }}</div>
                            </div>
                        </div>

                        <div class="pt-2 text-[11px] text-zinc-400 leading-relaxed">
                            در صورت نیاز به هماهنگی برای تحویل خارج از ساعات مقرر، می‌توانید با میزبان اقامتگاه هماهنگ فرمایید.
                        </div>
                    </div>

                    {{-- Terms Agreement --}}
                    <div class="p-4 rounded-2xl bg-[#ECFDF5] dark:bg-emerald-950/30 border border-[#007A5E]/20 text-xs">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="accept_rules" value="1" required checked class="mt-1 rounded text-[#007A5E] focus:ring-[#007A5E] dark:bg-zinc-800 dark:border-zinc-700">
                            <span class="text-zinc-700 dark:text-zinc-300 leading-relaxed">
                                تمامی <strong class="text-[#007A5E] dark:text-emerald-400">قوانین اقامتگاه</strong>، شرایط استرداد وجه و صحت مدارک هویتی خود را مطالعه کرده و می‌پذیرم.
                            </span>
                        </label>
                    </div>

                </div>

                {{-- Left Column: Villa Card & Invoice Summary --}}
                <div class="lg:col-span-5 sticky top-24 space-y-6">
                    
                    {{-- Villa Quick Card --}}
                    <div class="border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-3xl p-5 shadow-sm space-y-4">
                        <div class="flex items-center gap-4">
                            <img src="{{ $coverUrl }}" alt="{{ $property->title }}" class="w-20 h-20 rounded-2xl object-cover shrink-0 border border-[#ECECEE] dark:border-zinc-700">
                            <div class="truncate">
                                <span class="text-[11px] font-bold text-[#007A5E] dark:text-emerald-400 block">{{ $property->city ?? 'لفور' }}</span>
                                <h3 class="text-xs sm:text-sm font-black text-[#18181B] dark:text-zinc-100 truncate mt-0.5">{{ $property->title }}</h3>
                                <p class="text-[11px] text-zinc-400 mt-1">میزبان: {{ $hostDisplayName }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Transparent Invoice Breakdown --}}
                    <div class="border border-[#ECECEE] dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-3xl p-6 shadow-xl space-y-4 text-xs">
                        <h3 class="text-sm font-black text-[#18181B] dark:text-zinc-100 pb-3 border-b border-[#ECECEE] dark:border-zinc-800">
                            ریزمحاسبات پیش‌فاکتور
                        </h3>

                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                            <span>مدت زمان اقامت:</span>
                            <span class="font-bold text-[#18181B] dark:text-zinc-200">{{ number_format($quote['nights_count']) }} شب</span>
                        </div>

                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                            <span>تعداد مهمانان:</span>
                            <span class="font-bold text-[#18181B] dark:text-zinc-200">{{ number_format($guests) }} نفر</span>
                        </div>

                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                            <span>هزینه اقامت شب‌ها:</span>
                            <span class="font-bold text-[#18181B] dark:text-zinc-200">{{ number_format($quote['nights_total']) }} {{ $currencyLabel }}</span>
                        </div>

                        @if(!empty($quote['extra_guests_fee_total']) && $quote['extra_guests_fee_total'] > 0)
                            <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                                <span>هزینه نفرات اضافه:</span>
                                <span class="font-bold text-[#18181B] dark:text-zinc-200">{{ number_format($quote['extra_guests_fee_total']) }} {{ $currencyLabel }}</span>
                            </div>
                        @endif

                        @if(!empty($quote['cleaning_fee']) && $quote['cleaning_fee'] > 0)
                            <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                                <span>هزینه نظافت:</span>
                                <span class="font-bold text-[#18181B] dark:text-zinc-200">{{ number_format($quote['cleaning_fee']) }} {{ $currencyLabel }}</span>
                            </div>
                        @endif

                        <div class="pt-4 border-t border-[#ECECEE] dark:border-zinc-800 flex items-center justify-between text-sm font-black text-[#18181B] dark:text-zinc-100">
                            <span>مبلغ کل فاکتور:</span>
                            <span class="text-base text-[#007A5E] dark:text-emerald-400">{{ number_format($grandTotal) }} {{ $currencyLabel }}</span>
                        </div>

                        @if($prepaymentAmount < $grandTotal)
                            <div class="p-3 rounded-xl bg-[#F7F8F9] dark:bg-zinc-800 flex items-center justify-between text-xs font-bold text-zinc-800 dark:text-zinc-200">
                                <span>پیش‌پرداخت آنلاین جهت ثبت رزرو:</span>
                                <span class="text-[#007A5E] dark:text-emerald-400">{{ number_format($prepaymentAmount) }} {{ $currencyLabel }}</span>
                            </div>
                        @endif

                        {{-- Final Submit Button --}}
                        <div class="pt-4">
                            <button type="submit" class="w-full py-4 rounded-2xl bg-[#007A5E] hover:bg-[#005F48] text-white font-bold text-xs transition-colors shadow-lg shadow-emerald-900/10 flex items-center justify-center gap-2">
                                <span>{{ $isInstant ? 'تأیید و پرداخت نهایی رزرو' : 'ثبت قطعی درخواست رزرو' }}</span>
                                <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>

                        <div class="text-[10px] text-zinc-400 text-center leading-relaxed">
                            پشتیبانی ۲۴ ساعته مستر ویلا لفور در تمام مراحل همراه شماست.
                        </div>

                    </div>

                </div>

            </div>
        </form>

    </div>
@endsection
