@php
    /** @var \Modules\Booking\Entities\BookingService $service */
    $isAdminUser = (bool)($isAdminUser ?? false);
    $isProvider = (bool)($isProvider ?? false);

    $editingPublicAsProvider = (bool)($editingPublicAsProvider ?? false);

    /** @var \Modules\Booking\Entities\BookingServiceProvider|null $serviceProvider */
    $serviceProvider = $serviceProvider ?? null;

    $priceOverrideActive = $editingPublicAsProvider
    && $serviceProvider
    && $serviceProvider->override_price_mode === \Modules\Booking\Entities\BookingServiceProvider::OVERRIDE_MODE_OVERRIDE;

    $effectiveBasePrice = old('base_price');
    if ($effectiveBasePrice === null) {
    $effectiveBasePrice = ($priceOverrideActive && $serviceProvider->override_base_price !== null)
    ? $serviceProvider->override_base_price
    : ($service->base_price ?? 0);
    }

    $effectiveDiscountPrice = old('discount_price');
    if ($effectiveDiscountPrice === null) {
    $effectiveDiscountPrice = ($priceOverrideActive)
    ? ($serviceProvider->override_discount_price ?? null)
    : ($service->discount_price ?? null);
    }

    // discount_from/to (همان الگوی قبلی شما، با اولویت override)
    $discountFromCarbon = null;
    $discountToCarbon = null;

    if ($editingPublicAsProvider && $serviceProvider) {
    $discountFromCarbon = $serviceProvider->override_discount_from ?? null;
    $discountToCarbon = $serviceProvider->override_discount_to ?? null;
    } else {
    $discountFromCarbon = $service->discount_from ?? null;
    $discountToCarbon = $service->discount_to ?? null;
    }

    $discountFromValue = old('discount_from');
    $discountToValue = old('discount_to');

    if (!$discountFromValue && $discountFromCarbon) {
    if (class_exists(\Morilog\Jalali\Jalalian::class)) {
    $discountFromValue = \Morilog\Jalali\Jalalian::fromCarbon($discountFromCarbon)->format('Y/m/d H:i');
    } else {
    $discountFromValue = $discountFromCarbon->format('Y-m-d H:i');
    }
    }

    if (!$discountToValue && $discountToCarbon) {
    if (class_exists(\Morilog\Jalali\Jalalian::class)) {
    $discountToValue = \Morilog\Jalali\Jalalian::fromCarbon($discountToCarbon)->format('Y/m/d H:i');
    } else {
    $discountToValue = $discountToCarbon->format('Y-m-d H:i');
    }
    }

    $effectiveCategoryId = old('category_id');
    if ($effectiveCategoryId === null) {
    $effectiveCategoryId = ($editingPublicAsProvider && $serviceProvider && $serviceProvider->override_category_id !== null)
    ? $serviceProvider->override_category_id
    : ($service->category_id ?? null);
    }

    $effectiveFormId = old('appointment_form_id');
    if ($effectiveFormId === null) {
    $effectiveFormId = ($editingPublicAsProvider && $serviceProvider && $serviceProvider->override_appointment_form_id !==
    null)
    ? $serviceProvider->override_appointment_form_id
    : ($service->appointment_form_id ?? null);
    }

    $effectiveOnlineMode = old('online_booking_mode');
    if ($effectiveOnlineMode === null) {
    $effectiveOnlineMode = ($editingPublicAsProvider && $serviceProvider && $serviceProvider->override_online_booking_mode)
    ? $serviceProvider->override_online_booking_mode
    : ($service->online_booking_mode ?? \Modules\Booking\Entities\BookingService::ONLINE_MODE_INHERIT);
    }

    $effectivePaymentMode = old('payment_mode');
    if ($effectivePaymentMode === null) {
    $effectivePaymentMode = ($editingPublicAsProvider && $serviceProvider && $serviceProvider->override_payment_mode)
    ? $serviceProvider->override_payment_mode
    : ($service->payment_mode ?? \Modules\Booking\Entities\BookingService::PAYMENT_MODE_NONE);
    }

    $effectivePaymentAmountType = old('payment_amount_type');
    if ($effectivePaymentAmountType === null) {
    $effectivePaymentAmountType = ($editingPublicAsProvider && $serviceProvider)
    ? ($serviceProvider->override_payment_amount_type ?? null)
    : ($service->payment_amount_type ?? 'FULL');
    }

    $effectivePaymentAmountValue = old('payment_amount_value');
    if ($effectivePaymentAmountValue === null) {
    $effectivePaymentAmountValue = ($editingPublicAsProvider && $serviceProvider)
    ? ($serviceProvider->override_payment_amount_value ?? null)
    : ($service->payment_amount_value ?? null);
    }
    $inputClass = 'w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/60 px-3 py-2
    text-sm text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition';
    $selectClass = $inputClass . ' appearance-none cursor-pointer';
    $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1';
    $helpClass = 'text-xs text-gray-500 dark:text-gray-400 mt-1';
    $errorClass = 'text-xs text-rose-600 dark:text-rose-400 mt-1';
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    {{-- نام سرویس (برای Provider روی سرویس عمومی مخفی) --}}
    @if(! $editingPublicAsProvider)
        <div>
            <label class="{{ $labelClass }}">نام سرویس</label>
            <input type="text" name="name" class="{{ $inputClass }}" value="{{ old('name', $service->name ?? '') }}"
                   required>
            @error('name')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
        </div>

        <div>
            <label class="{{ $labelClass }}">وضعیت سرویس</label>
            @php $v = old('status', $service->status ?? \Modules\Booking\Entities\BookingService::STATUS_ACTIVE); @endphp
            <select name="status" class="{{ $selectClass }}" required>
                <option value="ACTIVE" @selected($v==='ACTIVE' )>فعال</option>
                <option value="INACTIVE" @selected($v==='INACTIVE' )>غیرفعال</option>
            </select>
            @error('status')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
        </div>
    @endif

    {{-- قیمت‌ها --}}
    <div>
        <label class="{{ $labelClass }}">قیمت پایه ({{ config('booking.defaults.currency_unit', 'IRR') }})</label>
        <input type="number" step="0.01" name="base_price" class="{{ $inputClass }}" value="{{ $effectiveBasePrice }}"
               required>
        @error('base_price')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="{{ $labelClass }}">قیمت تخفیفی</label>
        <input type="number" step="0.01" name="discount_price" class="{{ $inputClass }}"
               value="{{ $effectiveDiscountPrice }}">
        @error('discount_price')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="{{ $labelClass }}">شروع تخفیف</label>
        <input type="text" name="discount_from" class="{{ $inputClass }} jalali-datetime" data-jdp data-jdp-time="true"
               value="{{ $discountFromValue }}">
        <div class="{{ $helpClass }}">تاریخ و ساعت به صورت شمسی (jalalidatepicker)</div>
        @error('discount_from')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="{{ $labelClass }}">پایان تخفیف</label>
        <input type="text" name="discount_to" class="{{ $inputClass }} jalali-datetime" data-jdp data-jdp-time="true"
               value="{{ $discountToValue }}">
        @error('discount_to')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
    </div>

    {{-- دسته و فرم --}}
    <div>
        <label class="{{ $labelClass }}">دسته سرویس</label>
        <select name="category_id" class="{{ $selectClass }}">
            <option value="">بدون دسته</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected((string)$effectiveCategoryId===(string)$cat->id)>{{ $cat->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="{{ $labelClass }}">فرم اختصاصی نوبت</label>
        <select name="appointment_form_id" class="{{ $selectClass }}">
            <option value="">بدون فرم اختصاصی</option>
            @foreach($forms as $f)
                <option value="{{ $f->id }}" @selected((string)$effectiveFormId===(string)$f->id)>{{ $f->name }}</option>
            @endforeach
        </select>
        @error('appointment_form_id')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
    </div>

    {{-- رزرو آنلاین --}}
    <div>
        <label class="{{ $labelClass }}">وضعیت رزرو آنلاین</label>
        <select name="online_booking_mode" class="{{ $selectClass }}" required>
            <option value="INHERIT" @selected($effectiveOnlineMode==='INHERIT' )>مطابق تنظیمات کلی (INHERIT)</option>
            <option value="FORCE_ON" @selected($effectiveOnlineMode==='FORCE_ON' )>اجباری فعال (فقط آنلاین)</option>
            <option value="FORCE_OFF" @selected($effectiveOnlineMode==='FORCE_OFF' )>غیرفعال برای رزرو آنلاین</option>
        </select>
        @error('online_booking_mode')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
    </div>

    {{-- زمان‌بندی سفارشی --}}
    @if(!($editingPublicAsProvider ?? false))
        <div class="md:col-span-2">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                <input type="checkbox" name="custom_schedule_enabled" value="1" @checked(old('custom_schedule_enabled',
                $service->custom_schedule_enabled ?? false))>
                <span>فعال‌سازی زمان‌بندی سفارشی برای ثبت دستی ساعت شروع/پایان</span>
            </label>
            <div class="{{ $helpClass }}">
                در صورت فعال بودن، در مرحله انتخاب اسلات می‌توانید ساعت شروع و پایان را دستی وارد کنید.
            </div>
            @error('custom_schedule_enabled')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
        </div>
    @endif

    {{-- پرداخت --}}
    <div>
        <label class="{{ $labelClass }}">سیاست پرداخت آنلاین</label>
        <select name="payment_mode" id="payment_mode" class="{{ $selectClass }}" required>
            <option value="NONE" @selected($effectivePaymentMode==='NONE' )>بدون پرداخت آنلاین</option>
            <option value="OPTIONAL" @selected($effectivePaymentMode==='OPTIONAL' )>اختیاری (مشتری می‌تواند پرداخت کند)
            </option>
            <option value="REQUIRED" @selected($effectivePaymentMode==='REQUIRED' )>الزامی قبل از تایید نوبت</option>
        </select>
        @error('payment_mode')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="{{ $labelClass }}">نوع مبلغ پرداخت</label>
        <select name="payment_amount_type" id="payment_amount_type" class="{{ $selectClass }}">
            <option value="">-</option>
            <option value="FULL" @selected($effectivePaymentAmountType==='FULL' )>کل مبلغ سرویس</option>
            <option value="DEPOSIT" @selected($effectivePaymentAmountType==='DEPOSIT' )>بیعانه (درصدی)</option>
            <option value="FIXED_AMOUNT" @selected($effectivePaymentAmountType==='FIXED_AMOUNT' )>مبلغ ثابت</option>
        </select>
        @error('payment_amount_type')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="{{ $labelClass }}">
            مقدار پرداخت
            <span class="text-xs text-gray-500 dark:text-gray-400">(برای بیعانه: درصد، برای مبلغ ثابت: عدد کامل)</span>
        </label>
        <input type="number" step="0.01" name="payment_amount_value" id="payment_amount_value" class="{{ $inputClass }}"
               value="{{ $effectivePaymentAmountValue }}">
        @error('payment_amount_value')<div class="{{ $errorClass }}">{{ $message }}</div>@enderror
    </div>

    {{-- provider_can_customize فقط برای ادمین --}}
    @if($isAdminUser)
        <div class="md:col-span-2">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                <input type="checkbox" name="provider_can_customize" value="1" @checked(old('provider_can_customize',
                $service->provider_can_customize ?? false))>
                <span>اجازهٔ شخصی‌سازی قیمت/وضعیت توسط ارائه‌دهنده (Provider)</span>
            </label>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modeEl = document.getElementById('payment_mode');
            const typeEl = document.getElementById('payment_amount_type');
            const valueEl = document.getElementById('payment_amount_value');

            function refreshPaymentUi() {
                const mode = modeEl.value;
                const disabled = (mode === 'NONE');

                typeEl.disabled = disabled;
                valueEl.disabled = disabled;

                if (disabled) {
                    typeEl.value = '';
                    valueEl.value = '';
                }
            }

            if (modeEl) {
                modeEl.addEventListener('change', refreshPaymentUi);
                refreshPaymentUi();
            }

            if (window.jalaliDatepicker && typeof window.jalaliDatepicker.startWatch === 'function') {
                window.jalaliDatepicker.startWatch();
            }
        });
    </script>
@endpush
