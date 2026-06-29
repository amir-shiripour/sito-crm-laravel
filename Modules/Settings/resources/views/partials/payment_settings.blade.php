<style>
    .installment-card.collapsed .installment-body {
        display: none !important;
    }
    .installment-card .toggle-icon {
        transition: transform 0.2s ease;
    }
    .installment-card.collapsed .toggle-icon {
        transform: rotate(180deg);
    }
    .installment-card .installment-header {
        transition: background-color 0.2s ease;
    }
    .installment-card.collapsed .installment-header {
        border-bottom-width: 0px !important;
        border-bottom-left-radius: 1rem;
        border-bottom-right-radius: 1rem;
    }
    .installment-card .installment-header:hover {
        background-color: rgba(243, 244, 246, 0.5);
    }
    .dark .installment-card .installment-header:hover {
        background-color: rgba(31, 41, 55, 0.5);
    }


    /* هماهنگی استایل متن‌ها در حالت تاریک */
    .dark #main-settings-form .text-gray-500,
    .dark #main-settings-form .text-gray-400,
    .dark #main-settings-form .text-gray-600 {
        color: #9ca3af !important; /* text-gray-400 */
    }
    .dark #main-settings-form .text-blue-700\/80 {
        color: #60a5fa !important; /* text-blue-400 */
    }
    .dark #main-settings-form .text-emerald-700\/80 {
        color: #34d399 !important; /* text-emerald-400 */
    }
    .dark #main-settings-form .text-amber-700\/80 {
        color: #fbbf24 !important; /* text-amber-400 */
    }

    /* ===== یکپارچه‌سازی استایل سلکتورها در تمام تب‌ها ===== */
    /* حذف فلش نیتیو مرورگر و اعمال پس‌زمینه یکپارچه در تم لایت */
    #main-settings-form select:not([multiple]) {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-color: #f9fafb !important; /* gray-50 */
        background-image: none !important;    /* حذف فلش داخلی مرورگر */
        border-color: #e5e7eb;               /* gray-200 */
        border-radius: 0.75rem;              /* rounded-xl */
        color: #111827;                       /* gray-900 */
    }

    /* تم دارک — override پس‌زمینه */
    .dark #main-settings-form select:not([multiple]) {
        background-color: #111827 !important; /* gray-900 */
        border-color: #374151;               /* gray-700 */
        color: #f3f4f6;                      /* gray-100 */
    }
</style>

<div class="{{ $cardClass }}">
                <div class="{{ $headerClass }}">
                    <div
                        class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">مدیریت روش‌های پرداخت</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">پیکربندی روش‌ها و درگاه‌های پرداخت
                            آنلاین و آفلاین سیستم</p>
                    </div>
                </div>

                <div class="p-6 space-y-10">

                    @if(!$isAccountingActive)
                        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl p-4 text-sm font-medium flex items-center gap-3">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <p class="font-bold">ماژول حسابداری غیرفعال است!</p>
                                <p class="text-xs mt-1">برای اتصال درگاه‌های پرداخت، دستگاه‌های POS و حساب‌های بانکی به صندوق‌های مالی، ابتدا باید ماژول حسابداری را از بخش <a href="{{ route('admin.modules.index') }}" class="underline text-yellow-900">مدیریت ماژول‌ها</a> فعال کنید.</p>
                            </div>
                        </div>
                    @endif

                    <div
                        class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-gray-100 dark:border-gray-700">
                        <div>
                            <label for="payment_currency" class="{{ $labelClass }}">واحد پول سیستم</label>
                            <select class="{{ $inputClass }}" id="payment_currency" name="payment_currency">
                                <option
                                    value="toman" {{ ($settings['payment_currency'] ?? 'toman') == 'toman' ? 'selected' : '' }}>
                                    تومان
                                </option>
                                <option
                                    value="rial" {{ ($settings['payment_currency'] ?? '') == 'rial' ? 'selected' : '' }}>
                                    ریال
                                </option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">واحد پولی که مبالغ در سیستم شما با آن ثبت می‌شوند.</p>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">روش‌های پرداخت فعال سیستم</label>
                            @php
                                $activePaymentMethods = isset($settings['active_payment_methods']) ? (is_string($settings['active_payment_methods']) ? json_decode($settings['active_payment_methods'], true) : $settings['active_payment_methods']) : ['online'];
                                if (!is_array($activePaymentMethods)) $activePaymentMethods = ['online'];
                            @endphp
                            <div class="flex flex-wrap gap-4 mt-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="active_payment_methods[]" value="online"
                                           class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 cursor-pointer" {{ in_array('online', $activePaymentMethods) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">درگاه اینترنتی</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="active_payment_methods[]" value="pos"
                                           class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 cursor-pointer" {{ in_array('pos', $activePaymentMethods) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">دستگاه POS</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="active_payment_methods[]" value="transfer"
                                           class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 cursor-pointer" {{ in_array('transfer', $activePaymentMethods) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">انتقال بانکی</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="active_payment_methods[]" value="cod"
                                           class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 cursor-pointer" {{ in_array('cod', $activePaymentMethods) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">پرداخت در محل</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="active_payment_methods[]" value="installment"
                                           class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-900 cursor-pointer" {{ in_array('installment', $activePaymentMethods) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">پرداخت قسطی</span>
                                </label>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-2">این روش‌ها در فرم‌های نوبت‌دهی و بخش‌های مختلف
                                سیستم قابل استفاده خواهند بود.</p>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 pb-2 border-b border-gray-100 dark:border-gray-700">
                            <div
                                class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-md font-bold text-gray-900 dark:text-white">۱. درگاه‌های اینترنتی</h3>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div
                                class="md:col-span-2 bg-indigo-50/50 dark:bg-indigo-900/10 p-4 rounded-xl border border-indigo-100 dark:border-indigo-800/30">
                                <label for="default_payment_gateway" class="{{ $labelClass }}">درگاه اینترنتی
                                    پیش‌فرض</label>
                                <select class="{{ $inputClass }} md:w-1/2" id="default_payment_gateway"
                                        name="default_payment_gateway">
                                    <option value="">انتخاب کنید...</option>
                                    <option
                                        value="zarinpal" {{ ($settings['default_payment_gateway'] ?? '') == 'zarinpal' ? 'selected' : '' }}>
                                        زرین‌پال
                                    </option>
                                    <option
                                        value="zibal" {{ ($settings['default_payment_gateway'] ?? '') == 'zibal' ? 'selected' : '' }}>
                                        زیبال
                                    </option>
                                    <option
                                        value="behpardakht" {{ ($settings['default_payment_gateway'] ?? '') == 'behpardakht' ? 'selected' : '' }}>
                                        به‌پرداخت ملت
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div
                            class="bg-gray-50/50 dark:bg-gray-800/50 p-5 rounded-xl border border-gray-100 dark:border-gray-700">
                            <div
                                class="flex items-center gap-3 mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
                                <div
                                    class="w-2 h-2 rounded-full {{ ($settings['zarinpal_status'] ?? '') == 'active' ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600' }}"></div>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">درگاه زرین‌پال</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="zarinpal_status" class="{{ $labelClass }}">وضعیت درگاه</label>
                                    <select class="{{ $inputClass }}" id="zarinpal_status" name="zarinpal_status">
                                        <option
                                            value="inactive" {{ ($settings['zarinpal_status'] ?? 'inactive') == 'inactive' ? 'selected' : '' }}>
                                            غیرفعال
                                        </option>
                                        <option
                                            value="active" {{ ($settings['zarinpal_status'] ?? '') == 'active' ? 'selected' : '' }}>
                                            فعال
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label for="zarinpal_sandbox" class="{{ $labelClass }}">حالت آزمایشی
                                        (Sandbox)</label>
                                    <select class="{{ $inputClass }}" id="zarinpal_sandbox" name="zarinpal_sandbox">
                                        <option
                                            value="0" {{ ($settings['zarinpal_sandbox'] ?? '0') == '0' ? 'selected' : '' }}>
                                            خیر (محیط عملیاتی)
                                        </option>
                                        <option
                                            value="1" {{ ($settings['zarinpal_sandbox'] ?? '') == '1' ? 'selected' : '' }}>
                                            بله (محیط تست)
                                        </option>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label for="zarinpal_merchant_id" class="{{ $labelClass }}">کد مرچنت (Merchant
                                        ID)</label>
                                    <input type="text" class="{{ $inputClass }} dir-ltr text-left"
                                           id="zarinpal_merchant_id" name="zarinpal_merchant_id"
                                           value="{{ $settings['zarinpal_merchant_id'] ?? '' }}"
                                           placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                                </div>

                                <div>
                                    <label for="zarinpal_bank_id" class="{{ $labelClass }}">بانک متصل (حسابداری)</label>
                                    <select name="zarinpal_bank_id" id="zarinpal_bank_id" class="{{ $inputClass }}" @if(!$isAccountingActive) disabled @endif>
                                        <option value="">انتخاب کنید...</option>
                                        @foreach($banks as $bank)
                                            <option
                                                value="{{ $bank->id }}" {{ ($settings['zarinpal_bank_id'] ?? '') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                                        @endforeach
                                    </select>
                                    @if(!$isAccountingActive)
                                        <p class="text-xs text-red-500 mt-1">ماژول حسابداری غیرفعال است.</p>
                                    @endif
                                </div>

                                <div class="md:col-span-2 flex items-center justify-end pt-2">
                                    <button type="button" id="test-zarinpal-btn"
                                            class="px-4 py-2 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 font-medium hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors flex items-center gap-2 text-sm shadow-sm">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        تست پرداخت زرین‌پال
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div
                            class="bg-gray-50/50 dark:bg-gray-800/50 p-5 rounded-xl border border-gray-100 dark:border-gray-700">
                            <div
                                class="flex items-center gap-3 mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
                                <div
                                    class="w-2 h-2 rounded-full {{ ($settings['zibal_status'] ?? '') == 'active' ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600' }}"></div>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">درگاه زیبال</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="zibal_status" class="{{ $labelClass }}">وضعیت درگاه</label>
                                    <select class="{{ $inputClass }}" id="zibal_status" name="zibal_status">
                                        <option
                                            value="inactive" {{ ($settings['zibal_status'] ?? 'inactive') == 'inactive' ? 'selected' : '' }}>
                                            غیرفعال
                                        </option>
                                        <option
                                            value="active" {{ ($settings['zibal_status'] ?? '') == 'active' ? 'selected' : '' }}>
                                            فعال
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label for="zibal_merchant_id" class="{{ $labelClass }}">کد مرچنت (Merchant)</label>
                                    <input type="text" class="{{ $inputClass }} dir-ltr text-left"
                                           id="zibal_merchant_id" name="zibal_merchant_id"
                                           value="{{ $settings['zibal_merchant_id'] ?? '' }}"
                                           placeholder="zibal (برای تست)">
                                </div>

                                <div>
                                    <label for="zibal_bank_id" class="{{ $labelClass }}">بانک متصل (حسابداری)</label>
                                    <select name="zibal_bank_id" id="zibal_bank_id" class="{{ $inputClass }}" @if(!$isAccountingActive) disabled @endif>
                                        <option value="">انتخاب کنید...</option>
                                        @foreach($banks as $bank)
                                            <option
                                                value="{{ $bank->id }}" {{ ($settings['zibal_bank_id'] ?? '') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                                        @endforeach
                                    </select>
                                    @if(!$isAccountingActive)
                                        <p class="text-xs text-red-500 mt-1">ماژول حسابداری غیرفعال است.</p>
                                    @endif
                                </div>

                                <div class="md:col-span-2 flex items-center justify-end pt-2">
                                    <button type="button" id="test-zibal-btn"
                                            class="px-4 py-2 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 font-medium hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors flex items-center gap-2 text-sm shadow-sm">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        تست پرداخت زیبال
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-gray-50/50 dark:bg-gray-800/50 p-5 rounded-xl border border-gray-100 dark:border-gray-700">
                            <div
                                class="flex items-center gap-3 mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
                                <div
                                    class="w-2 h-2 rounded-full {{ ($settings['behpardakht_status'] ?? '') == 'active' ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600' }}"></div>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">درگاه به‌پرداخت ملت</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-3">
                                    <label for="behpardakht_status" class="{{ $labelClass }}">وضعیت درگاه</label>
                                    <select class="{{ $inputClass }} md:w-1/3" id="behpardakht_status"
                                            name="behpardakht_status">
                                        <option
                                            value="inactive" {{ ($settings['behpardakht_status'] ?? 'inactive') == 'inactive' ? 'selected' : '' }}>
                                            غیرفعال
                                        </option>
                                        <option
                                            value="active" {{ ($settings['behpardakht_status'] ?? '') == 'active' ? 'selected' : '' }}>
                                            فعال
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label for="behpardakht_terminal_id" class="{{ $labelClass }}">شماره ترمینال
                                        (Terminal ID)</label>
                                    <input type="text" class="{{ $inputClass }} dir-ltr text-left"
                                           id="behpardakht_terminal_id" name="behpardakht_terminal_id"
                                           value="{{ $settings['behpardakht_terminal_id'] ?? '' }}">
                                </div>
                                <div>
                                    <label for="behpardakht_username" class="{{ $labelClass }}">نام کاربری
                                        (Username)</label>
                                    <input type="text" class="{{ $inputClass }} dir-ltr text-left"
                                           id="behpardakht_username" name="behpardakht_username"
                                           value="{{ $settings['behpardakht_username'] ?? '' }}">
                                </div>
                                <div>
                                    <label for="behpardakht_password" class="{{ $labelClass }}">رمز عبور
                                        (Password)</label>
                                    <input type="password" class="{{ $inputClass }} dir-ltr text-left"
                                           id="behpardakht_password" name="behpardakht_password"
                                           value="{{ $settings['behpardakht_password'] ?? '' }}">
                                </div>

                                <div class="md:col-span-3">
                                    <label for="behpardakht_bank_id" class="{{ $labelClass }}">بانک متصل
                                        (حسابداری)</label>
                                    <select name="behpardakht_bank_id" id="behpardakht_bank_id"
                                            class="{{ $inputClass }} md:w-1/3" @if(!$isAccountingActive) disabled @endif>
                                        <option value="">انتخاب کنید...</option>
                                        @foreach($banks as $bank)
                                            <option
                                                value="{{ $bank->id }}" {{ ($settings['behpardakht_bank_id'] ?? '') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                                        @endforeach
                                    </select>
                                    @if(!$isAccountingActive)
                                        <p class="text-xs text-red-500 mt-1">ماژول حسابداری غیرفعال است.</p>
                                    @endif
                                </div>

                                <div class="md:col-span-3 flex items-center justify-end pt-2">
                                    <button type="button" id="test-behpardakht-btn"
                                            class="px-4 py-2 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 font-medium hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors flex items-center gap-2 text-sm shadow-sm">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        تست پرداخت به‌پرداخت ملت
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3 pb-2 border-b border-gray-100 dark:border-gray-700">
                            <div
                                class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-900/20 flex items-center justify-center text-teal-600 dark:text-teal-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-md font-bold text-gray-900 dark:text-white">۲. دستگاه POS
                                    (کارتخوان)</h3>
                            </div>
                        </div>

                        <div
                            class="bg-gray-50/50 dark:bg-gray-800/50 p-5 rounded-xl border border-gray-100 dark:border-gray-700 space-y-4">
                            <div>
                                <label for="pos_status" class="{{ $labelClass }}">وضعیت پرداخت با POS</label>
                                <select class="{{ $inputClass }} md:w-1/3" id="pos_status" name="pos_status">
                                    <option
                                        value="inactive" {{ ($settings['pos_status'] ?? 'inactive') == 'inactive' ? 'selected' : '' }}>
                                        غیرفعال
                                    </option>
                                    <option
                                        value="active" {{ ($settings['pos_status'] ?? '') == 'active' ? 'selected' : '' }}>
                                        فعال
                                    </option>
                                </select>
                            </div>

                            <div id="pos-devices-container" class="space-y-4">
                            </div>

                            <div class="flex justify-start pt-2">
                                <button type="button" id="add-pos-device-btn"
                                        class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors flex items-center gap-2 text-sm shadow-sm">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    افزودن دستگاه POS
                                </button>
                            </div>

                            <div>
                                <label for="pos_guidance" class="{{ $labelClass }}">متن راهنمای کاربر</label>
                                <textarea class="{{ $inputClass }}" id="pos_guidance" name="pos_guidance" rows="2"
                                          placeholder="مثال: لطفاً پس از پرداخت در محل، رسید خود را به صندوقدار تحویل دهید.">{{ $settings['pos_guidance'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- بخش ۳: انتقال بانکی --}}
                    <div class="space-y-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3 pb-2 border-b border-gray-100 dark:border-gray-700">
                            <div
                                class="w-8 h-8 rounded-lg bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center text-orange-600 dark:text-orange-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-md font-bold text-gray-900 dark:text-white">۳. انتقال بانکی (کارت به
                                    کارت / شبا)</h3>
                            </div>
                        </div>

                        <div
                            class="bg-gray-50/50 dark:bg-gray-800/50 p-5 rounded-xl border border-gray-100 dark:border-gray-700 space-y-4">
                            <div>
                                <label for="bank_transfer_status" class="{{ $labelClass }}">وضعیت انتقال بانکی</label>
                                <select class="{{ $inputClass }} md:w-1/3" id="bank_transfer_status"
                                        name="bank_transfer_status">
                                    <option
                                        value="inactive" {{ ($settings['bank_transfer_status'] ?? 'inactive') == 'inactive' ? 'selected' : '' }}>
                                        غیرفعال
                                    </option>
                                    <option
                                        value="active" {{ ($settings['bank_transfer_status'] ?? '') == 'active' ? 'selected' : '' }}>
                                        فعال
                                    </option>
                                </select>
                            </div>

                            <div id="bank-accounts-container" class="space-y-4">
                            </div>

                            <div class="flex justify-start pt-2">
                                <button type="button" id="add-bank-account-btn"
                                        class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors flex items-center gap-2 text-sm shadow-sm">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    افزودن حساب بانکی
                                </button>
                            </div>

                            <div>
                                <label for="bank_transfer_guidance" class="{{ $labelClass }}">متن راهنمای آپلود
                                    فیش</label>
                                <textarea class="{{ $inputClass }}" id="bank_transfer_guidance"
                                          name="bank_transfer_guidance" rows="2"
                                          placeholder="مثال: لطفاً پس از واریز مبلغ، تصویر فیش یا کد پیگیری را در این قسمت وارد کنید.">{{ $settings['bank_transfer_guidance'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3 pb-2 border-b border-gray-100 dark:border-gray-700">
                            <div
                                class="w-8 h-8 rounded-lg bg-green-50 dark:bg-green-900/20 flex items-center justify-center text-green-600 dark:text-green-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-md font-bold text-gray-900 dark:text-white">۴. پرداخت در محل (Cash on Delivery)</h3>
                            </div>
                        </div>
                        <div
                            class="bg-gray-50/50 dark:bg-gray-800/50 p-5 rounded-xl border border-gray-100 dark:border-gray-700 space-y-4">
                            <div>
                                <label for="cod_status" class="{{ $labelClass }}">وضعیت پرداخت در محل</label>
                                <select class="{{ $inputClass }} md:w-1/3" id="cod_status" name="cod_status">
                                    <option
                                        value="inactive" {{ ($settings['cod_status'] ?? 'inactive') == 'inactive' ? 'selected' : '' }}>
                                        غیرفعال
                                    </option>
                                    <option
                                        value="active" {{ ($settings['cod_status'] ?? '') == 'active' ? 'selected' : '' }}>
                                        فعال
                                    </option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">با فعال کردن این گزینه، امکان پرداخت نقدی در محل تحویل برای مشتریان فعال می‌شود. این روش نیازی به اتصال به بانک یا درگاه پرداخت ندارد.</p>
                            </div>

                            <div>
                                <label for="cod_guidance" class="{{ $labelClass }}">متن راهنمای کاربر</label>
                                <textarea class="{{ $inputClass }}" id="cod_guidance" name="cod_guidance" rows="2"
                                          placeholder="مثال: پس از تأیید سفارش، مبلغ را هنگام تحویل به پیک یا مسئول فروش پرداخت نمایید.">{{ $settings['cod_guidance'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3 pb-2 border-b border-gray-100 dark:border-gray-700">
                            <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center text-purple-600 dark:text-purple-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-md font-bold text-gray-900 dark:text-white">۵. پرداخت قسطی (Installment)</h3>
                            </div>
                        </div>

                        <div class="bg-gray-50/50 dark:bg-gray-800/50 p-5 rounded-xl border border-gray-100 dark:border-gray-700 space-y-4">
                            <div>
                                <label for="installment_status" class="{{ $labelClass }}">وضعیت کلی پرداخت قسطی</label>
                                <select class="{{ $inputClass }} md:w-1/3" id="installment_status" name="installment_status">
                                    <option value="inactive" {{ ($settings['installment_status'] ?? 'inactive') == 'inactive' ? 'selected' : '' }}>غیرفعال</option>
                                    <option value="active" {{ ($settings['installment_status'] ?? '') == 'active' ? 'selected' : '' }}>فعال</option>
                                </select>
                            </div>
                            <div id="installment-types-container" class="space-y-4 mt-4">
                            </div>
                            <div class="flex justify-start pt-2">
                                <button type="button" id="add-installment-btn" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors flex items-center gap-2 text-sm shadow-sm">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    افزودن نوع اقساط
                                </button>
                            </div>
                            {{-- Days for installment due dates --}}
                            <div class="mt-4 p-4 bg-indigo-50/50 dark:bg-indigo-900/10 rounded-xl border border-indigo-100 dark:border-indigo-800/30">
                                <label class="{{ $labelClass }}">روزهای مجاز برای سررسید چک در ماه</label>
                                <p class="text-[11px] text-gray-500 mb-3">روزهایی که بیمار مجاز است سررسید چک‌های خود را در آن‌ها قرار دهد را انتخاب کنید (می‌توانید چند روز را انتخاب کنید).</p>

                                @php
                                    $rawDueDays = $settings['installment_due_days'] ?? [];

                                    // Handle string (JSON or comma-separated)
                                    if (is_string($rawDueDays)) {
                                        if (str_starts_with($rawDueDays, '[') || str_starts_with($rawDueDays, '{')) {
                                            $dueDays = json_decode($rawDueDays, true) ?? [];
                                        } else {
                                            $dueDays = array_filter(array_map('intval', explode(',', $rawDueDays)));
                                        }
                                    } else {
                                        $dueDays = is_array($rawDueDays) ? $rawDueDays : [];
                                    }

                                    $dueDays = array_map('intval', $dueDays);
                                @endphp

                                <div class="grid grid-cols-7 sm:grid-cols-10 gap-2 mt-2">
                                    @for ($i = 1; $i <= 31; $i++)
                                        <label class="relative cursor-pointer">
                                            <input
                                                type="checkbox"
                                                name="installment_due_days[]"
                                                value="{{ $i }}"
                                                class="peer sr-only"
                                                {{ in_array($i, $dueDays) ? 'checked' : '' }}>
                                            <div class="w-full h-9 flex items-center justify-center rounded-xl border-2 text-sm font-bold transition-all duration-200
                    border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400
                    hover:border-indigo-300 dark:hover:border-indigo-700 hover:text-indigo-500 hover:-translate-y-0.5
                    peer-checked:border-transparent peer-checked:bg-linear-to-br peer-checked:from-indigo-500 peer-checked:to-indigo-600 peer-checked:text-white peer-checked:shadow-md peer-checked:shadow-indigo-500/30">
                                                {{ $i }}
                                            </div>
                                        </label>
                                    @endfor
                                </div>
                            </div>
                            <div class="mt-4 rounded-2xl border border-purple-100 dark:border-purple-800/30 overflow-hidden shadow-sm">
                                <div class="px-5 py-4 bg-gradient-to-l from-purple-50 to-fuchsia-50/40 dark:from-purple-900/20 dark:to-fuchsia-900/10 border-b border-purple-100 dark:border-purple-800/30 flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-purple-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-purple-500/30">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18M17 8l4 4m0 0l-4 4m4-4H3"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">تنظیمات رند کردن مبالغ اقساط</h3>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">نحوه رند شدن مبلغ هر قسط و چک را مشخص کنید</p>
                                    </div>
                                </div>

                                <div class="p-5 bg-white dark:bg-gray-800/40 space-y-5">
                                    {{-- Rounding mode: visual radio cards instead of a plain select --}}
                                    <div>
                                        <label class="{{ $labelClass }} mb-3">نوع رندسازی</label>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" id="rounding-mode-cards">
                                            @php
                                                $roundingModeOptions = [
                                                    'none' => ['title' => 'بدون رندسازی', 'desc' => 'دیفالت سیستم', 'icon' => 'M5 12h14'],
                                                    'up'   => ['title' => 'رو به بالا',   'desc' => 'گرد به سمت بالا', 'icon' => 'M5 15l7-7 7 7'],
                                                    'down' => ['title' => 'رو به پایین',  'desc' => 'گرد به سمت پایین', 'icon' => 'M19 9l-7 7-7-7'],
                                                ];
                                                $currentRoundingMode = $settings['installment_rounding_mode'] ?? 'none';
                                            @endphp
                                            @foreach($roundingModeOptions as $modeValue => $modeMeta)
                                                <label class="relative cursor-pointer group">
                                                    <input type="radio" name="installment_rounding_mode" value="{{ $modeValue }}"
                                                           class="peer sr-only rounding-mode-radio"
                                                        {{ $currentRoundingMode == $modeValue ? 'checked' : '' }}>
                                                    <div class="flex items-center gap-3 p-3.5 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 transition-all duration-200
                                                                peer-checked:border-purple-500 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20 peer-checked:shadow-md peer-checked:shadow-purple-500/10
                                                                group-hover:border-purple-300 dark:group-hover:border-purple-700">
                                                        <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 transition-colors
                                                                    bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400
                                                                    peer-checked:bg-purple-600 peer-checked:text-white">
                                                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $modeMeta['icon'] }}"/>
                                                            </svg>
                                                        </div>
                                                        <div class="min-w-0">
                                                            <p class="text-xs font-bold text-gray-800 dark:text-gray-100 peer-checked:text-purple-700">{{ $modeMeta['title'] }}</p>
                                                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">{{ $modeMeta['desc'] }}</p>
                                                        </div>
                                                        <svg class="w-4 h-4 text-purple-600 mr-auto shrink-0 opacity-0 peer-checked:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Rounding factor --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-end">
                                        <div>
                                            <label for="installment_rounding_factor" class="{{ $labelClass }}">ضریب گرد کردن</label>
                                            <div class="relative">
                                                <input type="number" min="0" step="1" id="installment_rounding_factor" name="installment_rounding_factor"
                                                       value="{{ $settings['installment_rounding_factor'] ?? 1000 }}" placeholder="مثال: 10، 100، 1000"
                                                       class="{{ $inputClass }} dir-ltr text-left font-bold pl-16">
                                                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                                    <span class="text-[10px] font-bold text-purple-500 bg-purple-50 dark:bg-purple-900/30 px-2 py-1 rounded-md">واحد</span>
                                                </div>
                                            </div>
                                            <p class="text-[10px] text-gray-400 mt-1.5">مبنای رند کردن؛ مثلاً 10 برای دهگان، 1000 برای هزارگان.</p>
                                        </div>

                                        {{-- Live preview card --}}
                                        <div class="rounded-xl border border-gray-100 dark:border-gray-700 bg-gradient-to-br from-gray-50 to-purple-50/30 dark:from-gray-900/40 dark:to-purple-900/10 p-3.5">
                                            <div class="flex items-center gap-1.5 mb-2">
                                                <svg class="w-3.5 h-3.5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400">پیش‌نمایش زنده</span>
                                            </div>
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="text-center">
                                                    <p class="text-[9px] text-gray-400 mb-0.5">مبلغ نمونه</p>
                                                    <p class="text-xs font-bold text-gray-700 dark:text-gray-200 dir-ltr" id="rounding-preview-input">345,200</p>
                                                </div>
                                                <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                                </svg>
                                                <div class="text-center">
                                                    <p class="text-[9px] text-gray-400 mb-0.5">نتیجه رند شده</p>
                                                    <p class="text-sm font-black dir-ltr" id="rounding-preview-output">345,200</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-2 bg-purple-50/60 dark:bg-purple-900/10 p-3 rounded-lg border border-purple-100/70 dark:border-purple-800/30">
                                        <svg class="w-4 h-4 text-purple-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                                            <strong class="text-gray-700 dark:text-gray-300">راهنما:</strong> ضریب وارد شده تعیین می‌کند مبالغ بر چه مبنایی رند شوند؛ مقدار «پیش‌نمایش زنده» در بالا با تغییر نوع رندسازی و ضریب، به‌صورت آنی به‌روز می‌شود.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label for="installment_guidance" class="{{ $labelClass }}">متن راهنمای کلی قسطی</label>
                                <textarea class="{{ $inputClass }}" id="installment_guidance" name="installment_guidance" rows="2" placeholder="مثال: پرداخت اقساط از زمان ثبت سفارش فعال شده و هر ماه به صورت خودکار کسر می‌گردد.">{{ $settings['installment_guidance'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            </div>