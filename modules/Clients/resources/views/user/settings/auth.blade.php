{{-- clients::user.settings.auth --}}
@php
    $title = 'تنظیمات ورود به پرتال '.config('clients.labels.plural');

    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900";
    $labelClass = "block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5";
@endphp

<div class="flex justify-center">
    <div class="w-full max-w-2xl">

        {{-- هدر --}}
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
            <p class="text-sm text-gray-500 mt-2">
                نحوه احراز هویت مشتریان هنگام ورود به پرتال را تنظیم کنید (رمز عبور، کد پیامکی یا هر دو).
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl shadow-gray-200/50 dark:shadow-none overflow-hidden">

            {{-- نوار اعلان موفقیت --}}
            @if(session('success'))
                <div class="bg-emerald-50 border-b border-emerald-100 px-4 py-3 flex items-center gap-3 text-emerald-700 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-300">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <div class="p-6 sm:p-8 space-y-6">

                {{-- هشدار در صورت نبودن ماژول SMS --}}
                @if(! $smsModuleAvailable)
                    <div class="mb-4 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-xs text-amber-800 dark:bg-amber-900/20 dark:border-amber-700 dark:text-amber-200 flex gap-2">
                        <svg class="w-4 h-4 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/>
                        </svg>
                        <div>
                            <div class="font-semibold mb-1">ماژول پیامک فعال نیست</div>
                            <p>
                                تا زمانی که ماژول پیامک و درگاه SMS تنظیم نشده باشد، فقط ورود با
                                <span class="font-semibold">رمز عبور</span> فعال خواهد بود.
                            </p>
                        </div>
                    </div>
                @endif

                {{-- حالت ورود --}}
                <div>
                    <label class="{{ $labelClass }}">روش‌های مجاز برای ورود مشتری</label>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <label class="flex items-center gap-2 px-3 py-2 rounded-xl border cursor-pointer
                                      @if($mode === 'password') border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 @else border-gray-200 dark:border-gray-700 @endif">
                            <input type="radio" wire:model.live="mode" value="password" class="sr-only">
                            <span class="font-medium text-gray-800 dark:text-gray-100">فقط رمز عبور</span>
                        </label>

                        <label class="flex items-center gap-2 px-3 py-2 rounded-xl border cursor-pointer
                                      @if($mode === 'otp') border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 @else border-gray-200 dark:border-gray-700 @endif
                                      @if(! $smsModuleAvailable) opacity-50 cursor-not-allowed @endif">
                            <input type="radio" wire:model.live="mode" value="otp" class="sr-only" @if(! $smsModuleAvailable) disabled @endif>
                            <span class="font-medium text-gray-800 dark:text-gray-100">فقط کد پیامکی (OTP)</span>
                        </label>

                        <label class="flex items-center gap-2 px-3 py-2 rounded-xl border cursor-pointer
                                      @if($mode === 'both') border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 @else border-gray-200 dark:border-gray-700 @endif
                                      @if(! $smsModuleAvailable) opacity-50 cursor-not-allowed @endif">
                            <input type="radio" wire:model.live="mode" value="both" class="sr-only" @if(! $smsModuleAvailable) disabled @endif>
                            <span class="font-medium text-gray-800 dark:text-gray-100">هر دو (رمز + OTP)</span>
                        </label>
                    </div>

                    @if($smsModuleAvailable && $smsClientOtpPattern)
                        <p class="mt-2 text-[11px] text-emerald-600 dark:text-emerald-300">
                            پترن OTP مشتریان در تنظیمات پیامک تعریف شده است (OtpId = {{ $smsClientOtpPattern }}).
                        </p>
                    @elseif($smsModuleAvailable)
                        <p class="mt-2 text-[11px] text-amber-600 dark:text-amber-300">
                            برای فعال‌سازی کامل ورود با OTP، در صفحه تنظیمات پیامک، کد پترن OTP مشتریان را نیز تنظیم کنید.
                        </p>
                    @endif
                </div>

                {{-- حالت پیش‌فرض وقتی هر دو فعال است --}}
                @if($mode === 'both')
                    <div class="border border-dashed border-gray-200 dark:border-gray-700 rounded-xl p-4 bg-gray-50/60 dark:bg-gray-900/40">
                        <label class="{{ $labelClass }}">روش پیش‌فرض در فرم ورود</label>
                        <div class="flex flex-col sm:flex-row gap-3 text-xs">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model.live="defaultLogin" value="password" class="text-indigo-600 border-gray-300">
                                <span class="text-gray-700 dark:text-gray-200">پیش‌فرض: رمز عبور</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model.live="defaultLogin" value="otp" class="text-indigo-600 border-gray-300">
                                <span class="text-gray-700 dark:text-gray-200">پیش‌فرض: OTP پیامکی</span>
                            </label>
                        </div>
                        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                            برای مثال اگر پیش‌فرض را <strong>OTP</strong> انتخاب کنید، فرم ورود مشتری روی حالت ارسال کد پیامکی باز می‌شود.
                        </p>
                    </div>
                @endif

                {{-- تنظیمات OTP (فقط اگر mode = otp یا both) --}}
                @if($mode === 'otp' || $mode === 'both')
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 space-y-4">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            تنظیمات کد یک‌بارمصرف (OTP)
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="{{ $labelClass }}">تعداد ارقام کد</label>
                                <input type="number" min="3" max="10" wire:model.defer="otpLength" class="{{ $inputClass }} dir-ltr">
                                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                    مثلاً 4 یا 5 رقم (کد فقط شامل اعداد خواهد بود).
                                </p>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">مدت اعتبار کد (دقیقه)</label>
                                <input type="number" min="1" max="60" wire:model.defer="otpTtl" class="{{ $inputClass }} dir-ltr">
                                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                    بعد از این مدت، کد ارسال‌شده منقضی می‌شود و قابل استفاده نخواهد بود.
                                </p>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">فاصله بین دو ارسال مجدد (ثانیه)</label>
                                <input type="number" min="10" max="600" wire:model.defer="otpResendInterval" class="{{ $inputClass }} dir-ltr">
                                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                    حداقل فاصله زمانی بین دو درخواست پشت‌سرهم ارسال کد برای یک شماره.
                                </p>
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">حداکثر تعداد درخواست پشت‌سرهم</label>
                                <input type="number" min="1" max="10" wire:model.defer="otpMaxRequests" class="{{ $inputClass }} dir-ltr">
                                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                    برای جلوگیری از سوءاستفاده، بعد از این تعداد تلاش، ارسال کد موقتاً محدود می‌شود.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- دکمه ذخیره --}}
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button wire:click="save"
                            wire:loading.attr="disabled"
                            type="button"
                            class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 focus:ring-4 focus:ring-indigo-500/30 transition-all transform active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed">
                        ذخیره تنظیمات
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
