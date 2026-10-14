@php use Morilog\Jalali\CalendarUtils; @endphp
@extends('clients::layouts.client')

@php
    $title = 'سند و جزئیات رزرو ' . $booking->booking_code;
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

    // محاسبه استپ‌های تایم‌لاین بصری
    $currentStep = match($booking->status) {
        \Modules\Properties\Entities\PropertyRentalBooking::STATUS_PENDING_HOST     => 1,
        \Modules\Properties\Entities\PropertyRentalBooking::STATUS_AWAITING_PAYMENT => 2,
        \Modules\Properties\Entities\PropertyRentalBooking::STATUS_CONFIRMED        => 3,
        \Modules\Properties\Entities\PropertyRentalBooking::STATUS_CHECKED_IN       => 4,
        \Modules\Properties\Entities\PropertyRentalBooking::STATUS_COMPLETED        => 5,
        default                                                                     => 0, // لغو شده یا نامشخص
    };
    $isCancelled = str_contains($booking->status, 'cancel') || in_array($booking->status, ['rejected', 'expired']);
@endphp

@section('content')
    <div x-data="{
            showCancelModal: false,
            cancelReason: '',
            cancelLoading: false,
            showPayModal: false,
            paymentMethod: '{{ !empty($availablePaymentMethods['online']) ? 'online' : 'transfer' }}',
            selectedGateway: '{{ $onlineGateways[0]['id'] ?? 'zarinpal' }}',
            trackingCode: '',
            paymentDate: '{{ jdate()->format('Y/m/d') }}',
            selectedBankId: '{{ $bankAccounts[0]['id'] ?? '' }}',
            receiptFile: null,
            payLoading: false,
            copySuccess: false,
            copyText(text) {
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text).then(() => {
                        this.copySuccess = true;
                        setTimeout(() => this.copySuccess = false, 2000);
                    });
                }
            },
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
                if (this.paymentMethod === 'online') {
                    // ارسال درخواست پرداخت آنلاین
                    let form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('client.rental-bookings.pay', $booking->id) }}';
                    let csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);

                    let mInput = document.createElement('input');
                    mInput.type = 'hidden';
                    mInput.name = 'payment_method';
                    mInput.value = 'online';
                    form.appendChild(mInput);

                    let gInput = document.createElement('input');
                    gInput.type = 'hidden';
                    gInput.name = 'gateway';
                    gInput.value = this.selectedGateway;
                    form.appendChild(gInput);

                    document.body.appendChild(form);
                    form.submit();
                    return;
                }

                // ارسال فیش واریزی کارت به کارت
                let formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('payment_method', 'transfer');
                formData.append('tracking_code', this.trackingCode);
                formData.append('payment_date', this.paymentDate);
                formData.append('bank_id', this.selectedBankId);
                let fileInput = document.getElementById('receipt_file_input');
                if (fileInput && fileInput.files[0]) {
                    formData.append('receipt_file', fileInput.files[0]);
                }

                fetch('{{ route('client.rental-bookings.pay', $booking->id) }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        alert(res.message || 'فیش واریزی با موفقیت ثبت شد.');
                        window.location.reload();
                    } else {
                        alert(res.message || 'خطا در ثبت فیش واریزی');
                        this.payLoading = false;
                    }
                })
                .catch(err => {
                    alert('خطای ارتباط با سرور در ارسال فیش');
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
                        <span>سند و جزئیات رزرو</span>
                        <span class="text-indigo-600 dark:text-indigo-400 font-black">{{ $booking->booking_code }}</span>
                    </h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        ثبت شده در تاریخ {{ CalendarUtils::convertNumbers(jdate($booking->created_at)->format('Y/m/d ساعت H:i')) }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('client.payments.show', ['type' => 'rental', 'id' => $booking->id]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl transition text-xs font-bold shadow-xs">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>مشاهده صورت‌حساب مالی</span>
                </a>

                <a href="{{ route('client.rental-bookings.print', $booking->id) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl transition text-xs font-bold shadow-xs">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>چاپ تأییدیه رسمی رزرو</span>
                </a>

                @if($canPay)
                    <button type="button" 
                            @click="showPayModal = true"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition text-xs font-black shadow-md shadow-emerald-600/20 active:scale-95">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>پرداخت صورت‌حساب ({{ CalendarUtils::convertNumbers(number_format($booking->total_price)) }} {{ $currencyLabel }})</span>
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

        {{-- تایم‌لاین بصری مراحل رزرو --}}
        @if(!$isCancelled)
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700/80 shadow-xs">
                <h3 class="text-xs font-black text-gray-500 dark:text-gray-400 mb-6 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>مراحل پیشرفت رزرو اقامتگاه</span>
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 relative">
                    {{-- مرحله ۱: ثبت درخواست --}}
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-500 text-white flex items-center justify-center font-bold shadow-md shadow-emerald-500/20">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-xs font-black text-gray-900 dark:text-white">۱. ثبت درخواست</span>
                        <span class="text-[10px] text-gray-400">تکمیل شده</span>
                    </div>

                    {{-- مرحله ۲: تأیید میزبان --}}
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-bold {{ $currentStep >= 2 ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20' : ($currentStep == 1 ? 'bg-amber-500 text-white animate-pulse' : 'bg-gray-100 dark:bg-gray-700 text-gray-400') }}">
                            @if($currentStep >= 2)
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <span class="text-xs">۲</span>
                            @endif
                        </div>
                        <span class="text-xs font-black {{ $currentStep >= 2 ? 'text-gray-900 dark:text-white' : ($currentStep == 1 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400') }}">۲. تأیید میزبان</span>
                        <span class="text-[10px] text-gray-400">{{ $currentStep >= 2 ? 'تأیید شد' : ($currentStep == 1 ? 'در حال بررسی' : 'در انتظار') }}</span>
                    </div>

                    {{-- مرحله ۳: پرداخت و قطعی شدن --}}
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-bold {{ $currentStep >= 3 ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20' : ($currentStep == 2 ? 'bg-indigo-600 text-white animate-pulse' : 'bg-gray-100 dark:bg-gray-700 text-gray-400') }}">
                            @if($currentStep >= 3)
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <span class="text-xs">۳</span>
                            @endif
                        </div>
                        <span class="text-xs font-black {{ $currentStep >= 3 ? 'text-gray-900 dark:text-white' : ($currentStep == 2 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400') }}">۳. پرداخت و صدور سند</span>
                        <span class="text-[10px] text-gray-400">{{ $currentStep >= 3 ? 'قطعی شد' : ($currentStep == 2 ? 'در انتظار پرداخت' : 'در انتظار') }}</span>
                    </div>

                    {{-- مرحله ۴: تحویل و اقامت --}}
                    <div class="flex flex-col items-center text-center space-y-2">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-bold {{ $currentStep >= 4 ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20' : ($currentStep == 3 ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-400') }}">
                            @if($currentStep >= 4)
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <span class="text-xs">۴</span>
                            @endif
                        </div>
                        <span class="text-xs font-black {{ $currentStep >= 4 ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}">۴. تحویل کلید و اقامت</span>
                        <span class="text-[10px] text-gray-400">{{ $currentStep >= 4 ? 'انجام شد' : 'موعد ورود' }}</span>
                    </div>

                    {{-- مرحله ۵: پایان اقامت --}}
                    <div class="flex flex-col items-center text-center space-y-2 col-span-2 md:col-span-1">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-bold {{ $currentStep >= 5 ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20' : 'bg-gray-100 dark:bg-gray-700 text-gray-400' }}">
                            <span class="text-xs">۵</span>
                        </div>
                        <span class="text-xs font-black {{ $currentStep >= 5 ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}">۵. اتمام اقامت و تخلیه</span>
                        <span class="text-[10px] text-gray-400">{{ $currentStep >= 5 ? 'تکمیل شد' : 'پایان سفر' }}</span>
                    </div>
                </div>
            </div>
        @endif

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
                            درخواست شما تأیید گردید؛ جهت قطعی شدن رزرو و صدور تأییدیه رسمی، لطفاً نسبت به پرداخت آنلاین اقدام فرمایید.
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

                    {{-- کارت آدرس دقیق و موقعیت مکانی --}}
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
                                {{ $booking->property?->address ?: 'آدرس توسط میزبان ثبت نشده است؛ لطفاً با شماره تماس میزبان هماهنگ فرمایید.' }}
                            @else
                                {{ $booking->property?->city ?? 'منطقه اقامتگاه' }} (آدرس و پلاک دقیق پس از پرداخت و قطعی شدن رزرو در این سند برای شما نمایان می‌شود)
                            @endif
                        </p>
                    </div>
                </div>

                {{-- کارت مشخصات میزبان و مهمان اصلی --}}
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
                                        تماس: <span class="font-black text-indigo-600 dark:text-indigo-400 dir-ltr">{{ $booking->host?->phone ?? '---' }}</span>
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
                                <span class="text-gray-900 dark:text-white font-black dir-ltr">{{ $booking->guest_phone }}</span>
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

                {{-- کارت مقررات تحویل، ساعت ورود/خروج و قوانین کنسلی --}}
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700/80 shadow-xs space-y-4">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>مقررات تحویل و شرایط کنسلی اقامتگاه</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs font-bold">
                        <div class="p-3 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700 space-y-1">
                            <span class="text-gray-400 block text-[11px]">ساعت ورود و تحویل کلید:</span>
                            <span class="text-gray-900 dark:text-white font-black">از ساعت {{ $booking->property?->rentalConfig?->check_in_time ?? '۱۴:۰۰' }}</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700 space-y-1">
                            <span class="text-gray-400 block text-[11px]">ساعت تخلیه و خروج:</span>
                            <span class="text-gray-900 dark:text-white font-black">تا ساعت {{ $booking->property?->rentalConfig?->check_out_time ?? '۱۲:۰۰' }}</span>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-800/40 text-amber-900 dark:text-amber-200 text-xs font-bold leading-relaxed space-y-1">
                        <span class="font-black block">شرایط و قوانین کنسلی:</span>
                        <p>{{ $cancellationRulesText }}</p>
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
                            <span class="text-[11px] font-bold text-gray-400 block mb-1">تاریخ ورود</span>
                            <span class="text-sm font-black text-gray-900 dark:text-white block">{{ $booking->check_in }}</span>
                            <span class="text-[10px] text-gray-500 mt-1 block">از ساعت {{ $booking->property?->rentalConfig?->check_in_time ?? '۱۴:۰۰' }}</span>
                        </div>
                        <div class="p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200/60 dark:border-gray-700/60">
                            <span class="text-[11px] font-bold text-gray-400 block mb-1">تاریخ خروج</span>
                            <span class="text-sm font-black text-gray-900 dark:text-white block">{{ $booking->check_out }}</span>
                            <span class="text-[10px] text-gray-500 mt-1 block">تا ساعت {{ $booking->property?->rentalConfig?->check_out_time ?? '۱۲:۰۰' }}</span>
                        </div>
                    </div>

                    <div class="p-3 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 text-indigo-800 dark:text-indigo-300 flex items-center justify-between text-xs font-bold">
                        <span>مدت اقامت:</span>
                        <span class="font-black">{{ CalendarUtils::convertNumbers($booking->nights_count) }} شب</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-gray-50 dark:bg-gray-900/50 text-gray-700 dark:text-gray-300 flex items-center justify-between text-xs font-bold">
                        <span>تعداد مهمانان:</span>
                        <span class="font-black">{{ CalendarUtils::convertNumbers($booking->guests_count) }} نفر</span>
                    </div>
                </div>

                {{-- کارت صورت‌حساب مالی --}}
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700/80 shadow-xs space-y-4">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700/60 pb-3 flex items-center justify-between">
                        <span>صورت‌حساب مالی</span>
                        <span class="text-xs text-gray-400">{{ $currencyLabel }}</span>
                    </h3>

                    <div class="space-y-3 text-xs font-bold text-gray-600 dark:text-gray-300">
                        <div class="flex items-center justify-between">
                            <span>هزینه اقامت ({{ CalendarUtils::convertNumbers($booking->nights_count) }} شب):</span>
                            <span class="font-black text-gray-900 dark:text-white">{{ CalendarUtils::convertNumbers(number_format($booking->nights_total)) }}</span>
                        </div>

                        @if($booking->cleaning_fee > 0)
                            <div class="flex items-center justify-between">
                                <span>هزینه نظافت:</span>
                                <span class="font-black text-gray-900 dark:text-white">{{ CalendarUtils::convertNumbers(number_format($booking->cleaning_fee)) }}</span>
                            </div>
                        @endif

                        @if($booking->extra_guests_fee > 0)
                            <div class="flex items-center justify-between">
                                <span>هزینه نفرات اضافه:</span>
                                <span class="font-black text-gray-900 dark:text-white">{{ CalendarUtils::convertNumbers(number_format($booking->extra_guests_fee)) }}</span>
                            </div>
                        @endif

                        @if($booking->discount_amount > 0)
                            <div class="flex items-center justify-between text-emerald-600 dark:text-emerald-400">
                                <span>تخفیف:</span>
                                <span class="font-black">-{{ CalendarUtils::convertNumbers(number_format($booking->discount_amount)) }}</span>
                            </div>
                        @endif

                        <div class="pt-3 border-t border-gray-100 dark:border-gray-700/80 flex items-center justify-between text-sm font-black text-indigo-600 dark:text-indigo-400">
                            <span>مبلغ کل قابل پرداخت:</span>
                            <span>{{ CalendarUtils::convertNumbers(number_format($booking->total_price)) }} {{ $currencyLabel }}</span>
                        </div>
                    </div>

                    <div class="pt-2 space-y-2">
                        @if($canPay)
                            <button type="button" 
                                    @click="showPayModal = true"
                                    class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-black transition shadow-lg shadow-emerald-600/20 flex items-center justify-center gap-2 active:scale-95">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                <span>پرداخت آنلاین و تسویه</span>
                            </button>
                        @endif

                        <a href="{{ route('client.payments.show', ['type' => 'rental', 'id' => $booking->id]) }}"
                           class="w-full py-3 px-4 bg-gray-50 dark:bg-gray-700/60 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600 rounded-2xl text-xs font-bold transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>مشاهده صورت‌حساب مالی در لیست پرداخت‌ها</span>
                        </a>
                    </div>
                </div>

            </div>

        </div>

        {{-- مودال پرداخت صورت‌حساب رزرو (شامل درگاه آنلاین و کارت‌به‌کارت) --}}
        <div x-cloak x-show="showPayModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="showPayModal = false"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl p-6 sm:p-7 space-y-6">

                    {{-- هدر مودال --}}
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-gray-900 dark:text-white">پرداخت صورت‌حساب رزرو اقامتگاه</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">مبلغ قابل پرداخت: <span class="text-emerald-600 dark:text-emerald-400 font-black">{{ CalendarUtils::convertNumbers(number_format($booking->total_price)) }} {{ $currencyLabel }}</span></p>
                            </div>
                        </div>
                        <button type="button" @click="showPayModal = false" class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 flex items-center justify-center transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- انتخاب روش پرداخت (تب‌ها) --}}
                    <div class="grid grid-cols-2 gap-3">
                        @if(!empty($availablePaymentMethods['online']))
                            <button type="button" 
                                    @click="paymentMethod = 'online'"
                                    :class="paymentMethod === 'online' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700'"
                                    class="py-3 px-4 rounded-2xl text-xs font-black transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                <span>درگاه آنلاین (شتاب)</span>
                            </button>
                        @endif

                        @if(!empty($availablePaymentMethods['transfer']))
                            <button type="button" 
                                    @click="paymentMethod = 'transfer'"
                                    :class="paymentMethod === 'transfer' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700'"
                                    class="py-3 px-4 rounded-2xl text-xs font-black transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                <span>کارت به کارت / شبا</span>
                            </button>
                        @endif
                    </div>

                    {{-- فرم درگاه آنلاین --}}
                    <div x-show="paymentMethod === 'online'" class="space-y-4 text-xs font-bold">
                        <label class="block text-gray-700 dark:text-gray-300">انتخاب درگاه پرداخت اینترنتی:</label>
                        <div class="space-y-2">
                            @foreach($onlineGateways as $gw)
                                <label class="flex items-center justify-between p-3.5 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 cursor-pointer hover:border-indigo-500 transition">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="selected_gateway" value="{{ $gw['id'] }}" x-model="selectedGateway" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-gray-900 dark:text-white font-black">{{ $gw['label'] }}</span>
                                    </div>
                                    <span class="text-[10px] text-gray-400">شاپرک</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- فرم کارت به کارت / انتقال بانکی --}}
                    <div x-show="paymentMethod === 'transfer'" class="space-y-4 text-xs font-bold">
                        {{-- نمایش مشخصات حساب‌های فعال --}}
                        <div class="space-y-2.5 max-h-48 overflow-y-auto">
                            @foreach($bankAccounts as $acc)
                                <div class="p-3.5 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span class="font-black text-indigo-900 dark:text-indigo-200">{{ $acc['bank_name'] }} ({{ $acc['owner_name'] }})</span>
                                        @if(!empty($acc['card_number']))
                                            <button type="button" @click="copyText('{{ $acc['card_number'] }}')" class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline">کپی کارت</button>
                                        @endif
                                    </div>
                                    @if(!empty($acc['card_number']))
                                        <div class="font-black text-sm text-gray-900 dark:text-white tracking-widest dir-ltr text-right">{{ $acc['card_number'] }}</div>
                                    @endif
                                    @if(!empty($acc['iban']))
                                        <div class="text-[11px] text-gray-500 dir-ltr text-right font-sans">IR{{ $acc['iban'] }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 mb-1">شماره پیگیری / کد ارجاع <span class="text-red-500">*</span></label>
                                <input type="text" x-model="trackingCode" placeholder="شماره پیگیری فیش واریزی" class="w-full rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 px-3 py-2 text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 font-sans">
                            </div>
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 mb-1">تاریخ پرداخت</label>
                                <input type="text" x-model="paymentDate" placeholder="۱۴۰۳/۰۷/۲۲" class="w-full rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 px-3 py-2 text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 font-sans">
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 mb-1">تصویر فیش واریزی (اختیاری):</label>
                            <input type="file" id="receipt_file_input" accept="image/*,.pdf" class="w-full rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 px-3 py-2 text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 font-sans">
                        </div>
                    </div>

                    {{-- دکمه‌های اقدام --}}
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center gap-3">
                        <button type="button" @click="showPayModal = false" class="w-1/3 py-3 px-4 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 font-bold text-xs transition">
                            انصراف
                        </button>
                        <button type="button" 
                                @click="submitPayment()"
                                :disabled="payLoading || (paymentMethod === 'transfer' && !trackingCode.trim())"
                                class="w-2/3 py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs transition shadow-lg shadow-emerald-600/20 flex items-center justify-center gap-2 disabled:opacity-50">
                            <span x-show="!payLoading">
                                <span x-show="paymentMethod === 'online'">هدایت به درگاه پرداخت شاپرک &larr;</span>
                                <span x-show="paymentMethod === 'transfer'">ثبت و ارسال فیش واریزی</span>
                            </span>
                            <span x-show="payLoading" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span>در حال پردازش...</span>
                            </span>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        {{-- مودال انصراف و لغو رزرو --}}
        <div x-cloak x-show="showCancelModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="showCancelModal = false"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-right shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 space-y-4">
                    <h3 class="text-base font-black text-red-600 dark:text-red-400">انصراف و لغو رزرو اقامتگاه</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">آیا از لغو این رزرو اطمینان دارید؟ لطفاً علت انصراف خود را ثبت فرمایید.</p>
                    <textarea x-model="cancelReason" rows="3" placeholder="علت لغو رزرو..." class="w-full rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 p-3 text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-red-500/20 resize-none font-sans"></textarea>
                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" @click="showCancelModal = false" class="w-1/2 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 font-bold text-xs">بازگشت</button>
                        <button type="button" @click="submitCancel()" :disabled="cancelLoading" class="w-1/2 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs shadow-md">
                            <span x-show="!cancelLoading">تأیید لغو رزرو</span>
                            <span x-show="cancelLoading">در حال لغو...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
