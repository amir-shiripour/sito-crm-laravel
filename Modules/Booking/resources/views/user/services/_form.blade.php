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

    $effectiveCategoryIds = old('category_ids');
    if ($effectiveCategoryIds === null) {
        if ($editingPublicAsProvider && $serviceProvider && $serviceProvider->override_category_id !== null) {
            $effectiveCategoryIds = [$serviceProvider->override_category_id];
        } else {
            $effectiveCategoryIds = (isset($service) && $service->exists) ? $service->categories->pluck('id')->toArray() : [];
            // Fallback for old single category
            if (empty($effectiveCategoryIds) && !empty($service->category_id)) {
                $effectiveCategoryIds = [$service->category_id];
            }
        }
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

    $effectiveAutoConfirm = old('auto_confirm_online_booking');
    if ($effectiveAutoConfirm === null) {
        $effectiveAutoConfirm = ($editingPublicAsProvider && $serviceProvider && $serviceProvider->override_auto_confirm !== null)
            ? $serviceProvider->override_auto_confirm
            : ($service->auto_confirm_online_booking ?? false);
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
    $inputClass = 'w-full h-11 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-slate-900/40 px-4 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm transition-all focus:bg-white dark:focus:bg-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 hover:border-gray-300 dark:hover:border-gray-600';
    $selectClass = $inputClass . ' appearance-none cursor-pointer pr-10';
    $labelClass = 'block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center gap-2';
    $helpClass = 'text-xs text-slate-500 dark:text-slate-400 mt-1.5 flex items-start gap-1.5';
    $errorClass = 'text-xs text-rose-600 dark:text-rose-400 mt-1.5 flex items-center gap-1';

    $currencyMap = ['IRR' => 'ریال', 'IRT' => 'تومان'];
    $currencyLabel = $currencyMap[$settings->currency_unit] ?? $settings->currency_unit;
@endphp

<div class="space-y-6">
    {{-- بخش اطلاعات اصلی --}}
    <div class="p-6 bg-white dark:bg-slate-800/80 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm hover:shadow-md transition-shadow duration-300">
        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-5 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            اطلاعات اصلی
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- نام سرویس (برای Provider روی سرویس عمومی مخفی) --}}
            @if(! $editingPublicAsProvider)
                <div>
                    <label class="{{ $labelClass }}">نام سرویس</label>
                    <input type="text" name="name" class="{{ $inputClass }}" value="{{ old('name', $service->name ?? '') }}" required placeholder="مثال: مشاوره تخصصی">
                    @error('name')<div class="{{ $errorClass }}"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> {{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}">وضعیت سرویس</label>
                    @php $v = old('status', $service->status ?? \Modules\Booking\Entities\BookingService::STATUS_ACTIVE); @endphp
                    <div class="relative">
                        <select name="status" class="{{ $selectClass }}" required>
                            <option value="ACTIVE" @selected($v==='ACTIVE')>فعال</option>
                            <option value="INACTIVE" @selected($v==='INACTIVE')>غیرفعال</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    @error('status')<div class="{{ $errorClass }}"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> {{ $message }}</div>@enderror
                </div>
            @endif

            {{-- دسته‌ها --}}
            <div class="col-span-1 md:col-span-2">
                <label class="{{ $labelClass }}">دسته‌های سرویس (انتخاب چندگانه)</label>
                
                <div class="relative mt-1" id="multi-select-container">
                    <!-- Trigger -->
                    <div id="multi-select-trigger" class="min-h-[44px] w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-slate-900/40 px-3 py-2 text-sm shadow-sm cursor-pointer transition-all hover:border-gray-300 dark:hover:border-gray-600 flex flex-wrap items-center gap-2">
                        <span id="multi-select-placeholder" class="text-slate-400 dark:text-slate-500 py-1 px-1">انتخاب دسته‌بندی‌ها...</span>
                        <div id="multi-select-badges" class="flex flex-wrap gap-2 empty:hidden"></div>
                        <div class="mr-auto pointer-events-none text-slate-400">
                            <svg id="multi-select-icon" class="h-4 w-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <!-- Dropdown -->
                    <div id="multi-select-dropdown" class="absolute z-10 mt-2 w-full rounded-xl bg-white dark:bg-slate-800 shadow-lg border border-slate-200 dark:border-slate-700 max-h-60 overflow-auto py-1 hidden">
                        @foreach($categories as $cat)
                            <label class="flex items-center gap-3 px-4 py-2.5 cursor-pointer transition-colors hover:bg-slate-50 dark:hover:bg-slate-700/50 group">
                                <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}" data-name="{{ $cat->name }}" 
                                    @checked(in_array($cat->id, $effectiveCategoryIds))
                                    class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 multi-select-checkbox">
                                <span class="text-sm text-slate-700 dark:text-slate-300 group-hover:text-indigo-700 dark:group-hover:text-indigo-400">{{ $cat->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                @error('category_ids')<div class="{{ $errorClass }}"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> {{ $message }}</div>@enderror
            </div>

            <div>
                <label class="{{ $labelClass }}">فرم اختصاصی نوبت</label>
                <div class="relative">
                    <select name="appointment_form_id" class="{{ $selectClass }}">
                        <option value="">بدون فرم اختصاصی</option>
                        @foreach($forms as $f)
                            <option value="{{ $f->id }}" @selected((string)$effectiveFormId===(string)$f->id)>{{ $f->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                @error('appointment_form_id')<div class="{{ $errorClass }}"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> {{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- بخش قیمت‌گذاری --}}
    <div class="p-6 bg-white dark:bg-slate-800/80 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm hover:shadow-md transition-shadow duration-300">
        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-5 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            قیمت‌گذاری
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- قیمت‌ها --}}
            <div>
                <label class="{{ $labelClass }}">قیمت پایه ({{ $currencyLabel }})</label>
                <div class="relative">
                    <input type="text" name="base_price" class="{{ $inputClass }} price-format pl-16" value="{{ $effectiveBasePrice }}" required>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 font-medium text-sm">
                        {{ $currencyLabel }}
                    </div>
                </div>
                @error('base_price')<div class="{{ $errorClass }}"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> {{ $message }}</div>@enderror
            </div>

            <div>
                <label class="{{ $labelClass }}">قیمت تخفیفی ({{ $currencyLabel }})</label>
                <div class="relative">
                    <input type="text" name="discount_price" class="{{ $inputClass }} price-format pl-16" value="{{ $effectiveDiscountPrice }}">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 font-medium text-sm">
                        {{ $currencyLabel }}
                    </div>
                </div>
                @error('discount_price')<div class="{{ $errorClass }}"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> {{ $message }}</div>@enderror
            </div>

            <div>
                <label class="{{ $labelClass }}">شروع تخفیف</label>
                <div class="relative">
                    <input type="text" name="discount_from" class="{{ $inputClass }} jalali-datetime text-left dir-ltr" data-jdp data-jdp-time="true" value="{{ $discountFromValue }}">
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                <div class="{{ $helpClass }}">
                    <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>تاریخ و ساعت به صورت شمسی</span>
                </div>
                @error('discount_from')<div class="{{ $errorClass }}"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> {{ $message }}</div>@enderror
            </div>

            <div>
                <label class="{{ $labelClass }}">پایان تخفیف</label>
                <div class="relative">
                    <input type="text" name="discount_to" class="{{ $inputClass }} jalali-datetime text-left dir-ltr" data-jdp data-jdp-time="true" value="{{ $discountToValue }}">
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                @error('discount_to')<div class="{{ $errorClass }}"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> {{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- بخش رزرو آنلاین و پرداخت --}}
    <div class="p-6 bg-white dark:bg-slate-800/80 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm hover:shadow-md transition-shadow duration-300">
        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-5 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
            </svg>
            رزرو آنلاین و پرداخت
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- رزرو آنلاین --}}
            <div>
                <label class="{{ $labelClass }}">وضعیت رزرو آنلاین</label>
                <div class="relative">
                    <select name="online_booking_mode" class="{{ $selectClass }}" required>
                        <option value="INHERIT" @selected($effectiveOnlineMode==='INHERIT')>مطابق تنظیمات کلی (INHERIT)</option>
                        <option value="FORCE_ON" @selected($effectiveOnlineMode==='FORCE_ON')>اجباری فعال (فقط آنلاین)</option>
                        <option value="FORCE_OFF" @selected($effectiveOnlineMode==='FORCE_OFF')>غیرفعال برای رزرو آنلاین</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                @error('online_booking_mode')<div class="{{ $errorClass }}"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> {{ $message }}</div>@enderror
            </div>

            <div class="flex items-center mt-8">
                <label class="relative inline-flex items-center cursor-pointer gap-3">
                    <input type="checkbox" name="auto_confirm_online_booking" value="1" class="sr-only peer" @checked($effectiveAutoConfirm)>
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-500/20 dark:peer-focus:ring-indigo-800/30 rounded-full peer dark:bg-slate-700 peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-200">تایید خودکار رزروهای آنلاین</span>
                </label>
                @error('auto_confirm_online_booking')<div class="{{ $errorClass }}"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> {{ $message }}</div>@enderror
            </div>

            {{-- پرداخت --}}
            <div>
                <label class="{{ $labelClass }}">سیاست پرداخت آنلاین</label>
                <div class="relative">
                    <select name="payment_mode" id="payment_mode" class="{{ $selectClass }}" required>
                        <option value="NONE" @selected($effectivePaymentMode==='NONE')>بدون پرداخت آنلاین</option>
                        <option value="OPTIONAL" @selected($effectivePaymentMode==='OPTIONAL')>اختیاری (مشتری می‌تواند پرداخت کند)</option>
                        <option value="REQUIRED" @selected($effectivePaymentMode==='REQUIRED')>الزامی قبل از تایید نوبت</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                @error('payment_mode')<div class="{{ $errorClass }}"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> {{ $message }}</div>@enderror
            </div>

            <div>
                <label class="{{ $labelClass }}">نوع مبلغ پرداخت</label>
                <div class="relative">
                    <select name="payment_amount_type" id="payment_amount_type" class="{{ $selectClass }}">
                        <option value="">-</option>
                        <option value="FULL" @selected($effectivePaymentAmountType==='FULL')>کل مبلغ سرویس</option>
                        <option value="DEPOSIT" @selected($effectivePaymentAmountType==='DEPOSIT')>بیعانه (درصدی)</option>
                        <option value="FIXED_AMOUNT" @selected($effectivePaymentAmountType==='FIXED_AMOUNT')>مبلغ ثابت ({{ $currencyLabel }})</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                @error('payment_amount_type')<div class="{{ $errorClass }}"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> {{ $message }}</div>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="{{ $labelClass }}">مقدار پرداخت</label>
                <input type="text" name="payment_amount_value" id="payment_amount_value" class="{{ $inputClass }} price-format max-w-sm" value="{{ $effectivePaymentAmountValue }}">
                <div class="{{ $helpClass }}">
                    <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>برای بیعانه درصد (مثلا 20) و برای مبلغ ثابت، قیمت را وارد کنید.</span>
                </div>
                @error('payment_amount_value')<div class="{{ $errorClass }}"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> {{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- بخش تنظیمات پیشرفته --}}
    <div class="p-6 bg-white dark:bg-slate-800/80 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-sm hover:shadow-md transition-shadow duration-300">
        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-5 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            تنظیمات پیشرفته
        </h3>
        <div class="space-y-6">
            {{-- زمان‌بندی سفارشی --}}
            @if(!($editingPublicAsProvider ?? false))
                <div>
                    <label class="relative inline-flex items-center cursor-pointer gap-3">
                        <input type="checkbox" name="custom_schedule_enabled" value="1" class="sr-only peer" @checked(old('custom_schedule_enabled', $service->custom_schedule_enabled ?? false))>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-500/20 dark:peer-focus:ring-indigo-800/30 rounded-full peer dark:bg-slate-700 peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-200">فعال‌سازی زمان‌بندی سفارشی برای ثبت دستی ساعت شروع/پایان</span>
                    </label>
                    <div class="{{ $helpClass }} mt-2">
                        <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>در صورت فعال بودن، در مرحله انتخاب اسلات می‌توانید ساعت شروع و پایان را دستی وارد کنید.</span>
                    </div>
                    @error('custom_schedule_enabled')<div class="{{ $errorClass }}"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> {{ $message }}</div>@enderror
                </div>
            @endif

            {{-- provider_can_customize فقط برای ادمین --}}
            @if($isAdminUser)
                <div class="pt-4 border-t border-slate-200 dark:border-slate-700/50">
                    <label class="relative inline-flex items-center cursor-pointer gap-3">
                        <input type="checkbox" name="provider_can_customize" value="1" class="sr-only peer" @checked(old('provider_can_customize', $service->provider_can_customize ?? false))>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-500/20 dark:peer-focus:ring-indigo-800/30 rounded-full peer dark:bg-slate-700 peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-200">اجازهٔ شخصی‌سازی قیمت/وضعیت توسط ارائه‌دهنده (Provider)</span>
                    </label>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Custom Multi-Select Logic
        const msContainer = document.getElementById('multi-select-container');
        const msTrigger = document.getElementById('multi-select-trigger');
        const msDropdown = document.getElementById('multi-select-dropdown');
        const msBadgesContainer = document.getElementById('multi-select-badges');
        const msPlaceholder = document.getElementById('multi-select-placeholder');
        const msIcon = document.getElementById('multi-select-icon');
        const msCheckboxes = document.querySelectorAll('.multi-select-checkbox');

        if (msContainer) {
            function updateMultiSelect() {
                msBadgesContainer.innerHTML = '';
                let selectedCount = 0;

                msCheckboxes.forEach(cb => {
                    if (cb.checked) {
                        selectedCount++;
                        const badge = document.createElement('span');
                        badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-500/20 text-xs font-medium';
                        badge.innerHTML = `
                            ${cb.dataset.name}
                            <button type="button" class="text-indigo-500 hover:text-indigo-700 dark:hover:text-indigo-300 focus:outline-none" data-id="${cb.value}">
                                <svg class="w-3.5 h-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        `;
                        msBadgesContainer.appendChild(badge);
                    }
                });

                if (selectedCount > 0) {
                    msPlaceholder.classList.add('hidden');
                    msBadgesContainer.classList.remove('hidden');
                } else {
                    msPlaceholder.classList.remove('hidden');
                    msBadgesContainer.classList.add('hidden');
                }
            }

            msTrigger.addEventListener('click', () => {
                msDropdown.classList.toggle('hidden');
                msIcon.classList.toggle('rotate-180');
            });

            document.addEventListener('click', (e) => {
                if (!msContainer.contains(e.target)) {
                    msDropdown.classList.add('hidden');
                    msIcon.classList.remove('rotate-180');
                }
            });

            msCheckboxes.forEach(cb => {
                cb.addEventListener('change', updateMultiSelect);
            });

            msBadgesContainer.addEventListener('click', (e) => {
                if (e.target.closest('button')) {
                    e.stopPropagation(); // prevent opening dropdown
                    const btn = e.target.closest('button');
                    const id = btn.dataset.id;
                    const cb = document.querySelector(`.multi-select-checkbox[value="${id}"]`);
                    if (cb) {
                        cb.checked = false;
                        updateMultiSelect();
                    }
                }
            });

            // initial state
            updateMultiSelect();
        }

        // Payment UI logic
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

        // Jalali Datepicker
        if (window.jalaliDatepicker && typeof window.jalaliDatepicker.startWatch === 'function') {
            window.jalaliDatepicker.startWatch();
        }

        // --- Price Formatting ---
        function formatPrice(input) {
            if (!input) return;
            // 1. Unformat the value and remove non-numeric characters
            let value = input.value.replace(/,/g, '').replace(/[^0-9]/g, '');

            // 2. If value is not empty, format it with commas
            if (value) {
                input.value = parseInt(value, 10).toLocaleString('en-US');
            } else {
                input.value = '';
            }
        }

        function unformatPrice(input) {
            if (input) {
                input.value = input.value.replace(/,/g, '');
            }
        }

        const priceInputs = document.querySelectorAll('.price-format');

        priceInputs.forEach(input => {
            // Format on initial load
            formatPrice(input);
            // Format in real-time as user types
            input.addEventListener('input', () => formatPrice(input));
        });

        // Find the form and add a submit listener to unformat prices before submission
        const form = document.querySelector('form[action*="services"]');
        if (form) {
            form.addEventListener('submit', function(e) {
                priceInputs.forEach(input => {
                    unformatPrice(input);
                });
            });
        }
    });
</script>
@endpush
