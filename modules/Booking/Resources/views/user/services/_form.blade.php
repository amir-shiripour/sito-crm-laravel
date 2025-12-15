<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    {{-- نام سرویس --}}
    <div>
        <label class="block text-sm mb-1">نام سرویس</label>
        <input type="text" name="name" class="w-full border rounded p-2"
               value="{{ old('name', $service->name ?? '') }}" required>
        @error('name')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
    </div>

    {{-- وضعیت سرویس --}}
    <div>
        <label class="block text-sm mb-1">وضعیت سرویس</label>
        @php $v = old('status', $service->status ?? \Modules\Booking\Entities\BookingService::STATUS_ACTIVE); @endphp
        <select name="status" class="w-full border rounded p-2" required>
            <option value="ACTIVE" @selected($v==='ACTIVE')>فعال</option>
            <option value="INACTIVE" @selected($v==='INACTIVE')>غیرفعال</option>
        </select>
        @error('status')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
    </div>

    {{-- قیمت‌ها --}}
    <div>
        <label class="block text-sm mb-1">قیمت پایه ({{ config('booking.defaults.currency_unit', 'IRR') }})</label>
        <input type="number" step="0.01" name="base_price" class="w-full border rounded p-2"
               value="{{ old('base_price', $service->base_price ?? 0) }}" required>
        @error('base_price')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="block text-sm mb-1">قیمت تخفیفی</label>
        <input type="number" step="0.01" name="discount_price" class="w-full border rounded p-2"
               value="{{ old('discount_price', $service->discount_price ?? '') }}">
        @error('discount_price')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
    </div>

    {{-- شروع و پایان تخفیف - جلالی --}}
    @php
        $discountFromValue = old('discount_from');
        $discountToValue   = old('discount_to');

        if (!$discountFromValue && ($service->discount_from ?? null)) {
            if (class_exists(\Morilog\Jalali\Jalalian::class)) {
                $discountFromValue = \Morilog\Jalali\Jalalian::fromCarbon($service->discount_from)->format('Y/m/d H:i');
            } else {
                $discountFromValue = $service->discount_from->format('Y-m-d H:i');
            }
        }

        if (!$discountToValue && ($service->discount_to ?? null)) {
            if (class_exists(\Morilog\Jalali\Jalalian::class)) {
                $discountToValue = \Morilog\Jalali\Jalalian::fromCarbon($service->discount_to)->format('Y/m/d H:i');
            } else {
                $discountToValue = $service->discount_to->format('Y-m-d H:i');
            }
        }
    @endphp

    <div>
        <label class="block text-sm mb-1">شروع تخفیف</label>
        <input type="text"
               name="discount_from"
               class="w-full border rounded p-2 jalali-datetime"
               data-jdp
               data-jdp-time="true"
               value="{{ $discountFromValue }}">
        <div class="text-xs text-gray-500 mt-1">تاریخ و ساعت به صورت شمسی (jalalidatepicker)</div>
        @error('discount_from')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="block text-sm mb-1">پایان تخفیف</label>
        <input type="text"
               name="discount_to"
               class="w-full border rounded p-2 jalali-datetime"
               data-jdp
               data-jdp-time="true"
               value="{{ $discountToValue }}">
        @error('discount_to')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
    </div>

    {{-- دسته و فرم --}}
    <div>
        <label class="block text-sm mb-1">دسته سرویس</label>
        @php $v = old('category_id', $service->category_id ?? null); @endphp
        <select name="category_id" class="w-full border rounded p-2">
            <option value="">بدون دسته</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected((string)$v === (string)$cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        @error('category_id')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="block text-sm mb-1">فرم اختصاصی نوبت</label>
        @php $v = old('appointment_form_id', $service->appointment_form_id ?? null); @endphp
        <select name="appointment_form_id" class="w-full border rounded p-2">
            <option value="">بدون فرم اختصاصی</option>
            @foreach($forms as $f)
                <option value="{{ $f->id }}" @selected((string)$v === (string)$f->id)>{{ $f->name }}</option>
            @endforeach
        </select>
        @error('appointment_form_id')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
    </div>

    {{-- رزرو آنلاین --}}
    <div>
        <label class="block text-sm mb-1">وضعیت رزرو آنلاین</label>
        @php $v = old('online_booking_mode', $service->online_booking_mode ?? \Modules\Booking\Entities\BookingService::ONLINE_MODE_INHERIT); @endphp
        <select name="online_booking_mode" class="w-full border rounded p-2" required>
            <option value="INHERIT" @selected($v==='INHERIT')>مطابق تنظیمات کلی (INHERIT)</option>
            <option value="FORCE_ON" @selected($v==='FORCE_ON')>اجباری فعال (فقط آنلاین)</option>
            <option value="FORCE_OFF" @selected($v==='FORCE_OFF')>غیرفعال برای رزرو آنلاین</option>
        </select>
        @error('online_booking_mode')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
    </div>

    {{-- پرداخت --}}
    <div>
        <label class="block text-sm mb-1">سیاست پرداخت آنلاین</label>
        @php $v = old('payment_mode', $service->payment_mode ?? \Modules\Booking\Entities\BookingService::PAYMENT_MODE_NONE); @endphp
        <select name="payment_mode" id="payment_mode" class="w-full border rounded p-2" required>
            <option value="NONE" @selected($v==='NONE')>بدون پرداخت آنلاین</option>
            <option value="OPTIONAL" @selected($v==='OPTIONAL')>اختیاری (مشتری می‌تواند پرداخت کند)</option>
            <option value="REQUIRED" @selected($v==='REQUIRED')>الزامی قبل از تایید نوبت</option>
        </select>
        @error('payment_mode')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="block text-sm mb-1">نوع مبلغ پرداخت</label>
        @php $v = old('payment_amount_type', $service->payment_amount_type ?? 'FULL'); @endphp
        <select name="payment_amount_type" id="payment_amount_type" class="w-full border rounded p-2">
            <option value="">-</option>
            <option value="FULL" @selected($v==='FULL')>کل مبلغ سرویس</option>
            <option value="DEPOSIT" @selected($v==='DEPOSIT')>بیعانه (درصدی)</option>
            <option value="FIXED_AMOUNT" @selected($v==='FIXED_AMOUNT')>مبلغ ثابت</option>
        </select>
        @error('payment_amount_type')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="block text-sm mb-1">
            مقدار پرداخت
            <span class="text-xs text-gray-500">(برای بیعانه: درصد، برای مبلغ ثابت: عدد کامل)</span>
        </label>
        <input type="number" step="0.01" name="payment_amount_value" id="payment_amount_value"
               class="w-full border rounded p-2"
               value="{{ old('payment_amount_value', $service->payment_amount_value ?? '') }}">
        @error('payment_amount_value')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
    </div>

    {{-- شخصی‌سازی توسط Provider --}}
    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="provider_can_customize" value="1"
                    @checked(old('provider_can_customize', $service->provider_can_customize ?? false))>
            <span class="text-sm">اجازهٔ شخصی‌سازی قیمت/وضعیت توسط ارائه‌دهنده (Provider)</span>
        </label>
    </div>
</div>

{{-- اسکریپت کوچک برای UX پرداخت --}}
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modeEl   = document.getElementById('payment_mode');
            const typeEl   = document.getElementById('payment_amount_type');
            const valueEl  = document.getElementById('payment_amount_value');

            function refreshPaymentUi() {
                const mode = modeEl.value;
                const disabled = (mode === 'NONE');

                typeEl.disabled  = disabled;
                valueEl.disabled = disabled;

                if (disabled) {
                    typeEl.value  = '';
                    valueEl.value = '';
                }
            }

            if (modeEl) {
                modeEl.addEventListener('change', refreshPaymentUi);
                refreshPaymentUi();
            }

            // اگر jalalidatepicker در پروژه لود شده:
            if (window.jalaliDatepicker && typeof window.jalaliDatepicker.startWatch === 'function') {
                window.jalaliDatepicker.startWatch();
            }
        });
    </script>
@endpush
