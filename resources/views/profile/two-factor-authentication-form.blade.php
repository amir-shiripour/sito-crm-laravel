@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm mb-6";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4";
    $titleClass = "text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2";
    $descClass = "text-xs text-gray-500 dark:text-gray-400 mt-1";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2";
    $btnPrimaryClass = "inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed";
    $btnDangerClass = "inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl bg-red-600 text-white font-bold text-sm shadow-lg shadow-red-500/30 hover:bg-red-700 hover:shadow-red-500/50 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed";
    $btnSecondaryClass = "inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white border border-gray-200 text-sm font-bold text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white";
    $footerClass = "px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-start gap-4";
@endphp

<div class="{{ $cardClass }}">
    {{-- هدر --}}
    <div class="{{ $headerClass }}">
        <div>
            <h2 class="{{ $titleClass }}">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                {{ __('احراز هویت دو مرحله‌ای') }}
            </h2>
            <p class="{{ $descClass }}">
                {{ __('با استفاده از احراز هویت دو مرحله‌ای، امنیت حساب خود را افزایش دهید.') }}
            </p>
        </div>
    </div>

    {{-- محتوا --}}
    <div class="p-6">
        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 mb-2">
            @if ($this->enabled)
                @if ($showingConfirmation)
                    {{ __('تکمیل فعال‌سازی احراز هویت دو مرحله‌ای.') }}
                @else
                    {{ __('شما احراز هویت دو مرحله‌ای را فعال کرده‌اید.') }}
                @endif
            @else
                {{ __('شما احراز هویت دو مرحله‌ای را فعال نکرده‌اید.') }}
            @endif
        </h3>

        <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
            <p>
                {{ __('هنگامی که احراز هویت دو مرحله‌ای فعال باشد، در حین ورود، یک کد تصادفی و امن از شما خواسته می‌شود. شما می‌توانید این کد را از اپلیکیشن Google Authenticator در گوشی خود دریافت کنید.') }}
            </p>
        </div>

        @if ($this->enabled)
            @if ($showingQrCode)
                <div class="max-w-xl p-4 bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 rounded-xl border border-blue-100 dark:border-blue-800/50 text-sm font-bold leading-relaxed mb-6">
                    @if ($showingConfirmation)
                        {{ __('برای تکمیل فعال‌سازی، بارکد زیر را با استفاده از اپلیکیشن Authenticator در گوشی خود اسکن کنید یا کلید راه‌اندازی را وارد نمایید و کد تولید شده را ارائه دهید.') }}
                    @else
                        {{ __('احراز هویت دو مرحله‌ای اکنون فعال است. بارکد زیر را با اپلیکیشن خود اسکن کنید یا کلید راه‌اندازی را وارد نمایید.') }}
                    @endif
                </div>

                <div class="flex flex-col sm:flex-row gap-6 items-start mb-6">
                    <div class="p-4 bg-white border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm inline-block">
                        {!! $this->user->twoFactorQrCodeSvg() !!}
                    </div>

                    <div class="flex-1 w-full p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/50">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('کلید راه‌اندازی (Setup Key):') }}</p>
                        <div class="font-mono text-sm dir-ltr tracking-wider bg-white dark:bg-gray-800 px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 select-all">
                            {{ decrypt($this->user->two_factor_secret) }}
                        </div>
                    </div>
                </div>

                @if ($showingConfirmation)
                    <div class="w-full sm:w-1/2 p-4 rounded-xl bg-gray-50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-700/50 mb-6">
                        <label for="code" class="{{ $labelClass }}">{{ __('کد تأیید') }}</label>
                        <input id="code" type="text" name="code" class="{{ $inputClass }} text-center tracking-widest text-lg dir-ltr font-mono" inputmode="numeric" autofocus autocomplete="one-time-code"
                               wire:model="code"
                               wire:keydown.enter="confirmTwoFactorAuthentication" placeholder="------" />
                        <x-input-error for="code" class="mt-2 text-xs text-red-500 font-bold" />
                    </div>
                @endif
            @endif

            @if ($showingRecoveryCodes)
                <div class="max-w-xl p-4 bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-300 rounded-xl border border-amber-100 dark:border-amber-800/50 text-sm font-bold leading-relaxed mb-4">
                    {{ __('این کدهای بازیابی را در یک مکان امن ذخیره کنید. در صورتی که دستگاه احراز هویت خود را گم کنید، می‌توانید از این کدها برای بازیابی دسترسی به حساب خود استفاده کنید.') }}
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-xl p-5 font-mono text-sm bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl text-center dir-ltr">
                    @foreach (json_decode(decrypt($this->user->two_factor_recovery_codes), true) as $code)
                        <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-600 py-2 rounded-lg text-gray-800 dark:text-gray-200 tracking-widest select-all shadow-sm">
                            {{ $code }}
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>

    {{-- فوتر - دکمه‌ها --}}
    <div class="{{ $footerClass }} flex-wrap justify-end">
        @if (! $this->enabled)
            <x-confirms-password wire:then="enableTwoFactorAuthentication">
                <button type="button" wire:loading.attr="disabled" class="{{ $btnPrimaryClass }}">
                    {{ __('فعال‌سازی') }}
                </button>
            </x-confirms-password>
        @else
            @if ($showingRecoveryCodes)
                <x-confirms-password wire:then="regenerateRecoveryCodes">
                    <button type="button" class="{{ $btnSecondaryClass }}">
                        {{ __('تولید مجدد کدهای بازیابی') }}
                    </button>
                </x-confirms-password>
            @elseif ($showingConfirmation)
                <x-confirms-password wire:then="confirmTwoFactorAuthentication">
                    <button type="button" wire:loading.attr="disabled" class="{{ $btnPrimaryClass }}">
                        {{ __('تأیید و ذخیره') }}
                    </button>
                </x-confirms-password>
            @else
                <x-confirms-password wire:then="showRecoveryCodes">
                    <button type="button" class="{{ $btnSecondaryClass }}">
                        {{ __('نمایش کدهای بازیابی') }}
                    </button>
                </x-confirms-password>
            @endif

            @if ($showingConfirmation)
                <x-confirms-password wire:then="disableTwoFactorAuthentication">
                    <button type="button" wire:loading.attr="disabled" class="{{ $btnSecondaryClass }}">
                        {{ __('انصراف') }}
                    </button>
                </x-confirms-password>
            @else
                <x-confirms-password wire:then="disableTwoFactorAuthentication">
                    <button type="button" wire:loading.attr="disabled" class="{{ $btnDangerClass }}">
                        {{ __('غیرفعال‌سازی') }}
                    </button>
                </x-confirms-password>
            @endif
        @endif
    </div>
</div>
