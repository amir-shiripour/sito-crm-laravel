@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm mb-6";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4";
    $titleClass = "text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2";
    $descClass = "text-xs text-gray-500 dark:text-gray-400 mt-1";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $labelClass = "block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2";
    $btnPrimaryClass = "inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed";
    $footerClass = "px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-end gap-4";
@endphp

<form wire:submit="updatePassword" class="{{ $cardClass }}" x-data="{ show: false }">
    {{-- هدر فرم --}}
    <div class="{{ $headerClass }}">
        <div>
            <h2 class="{{ $titleClass }}">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                {{ __('تغییر رمز عبور') }}
            </h2>
            <p class="{{ $descClass }}">
                {{ __('برای حفظ امنیت حساب کاربری خود، از یک رمز عبور طولانی و تصادفی استفاده کنید.') }}
            </p>
        </div>
    </div>

    {{-- محتوای فرم --}}
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2 md:w-1/2">
            <label for="current_password" class="{{ $labelClass }}">{{ __('رمز عبور فعلی') }}</label>
            <div class="relative">
                <input :type="show ? 'text' : 'password'" id="current_password" class="{{ $inputClass }} dir-ltr text-left font-mono !pr-11" wire:model="state.current_password" autocomplete="current-password" placeholder="••••••••" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 focus:outline-none transition-colors" tabindex="-1">
                    <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    <svg x-show="show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0l1.414 1.414m12.022-1.254A9.97 9.97 0 0021.543 12c-1.274 4.057-5.064 7-9.542 7m-1.724-1.724l-3.29-3.29" /></svg>
                </button>
            </div>
            <x-input-error for="current_password" class="mt-2 text-xs text-red-500 font-bold" />
        </div>

        <div class="md:col-span-1">
            <label for="password" class="{{ $labelClass }}">{{ __('رمز عبور جدید') }}</label>
            <div class="relative">
                <input :type="show ? 'text' : 'password'" id="password" class="{{ $inputClass }} dir-ltr text-left font-mono !pr-11" wire:model="state.password" autocomplete="new-password" placeholder="••••••••" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 focus:outline-none transition-colors" tabindex="-1">
                    <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    <svg x-show="show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0l1.414 1.414m12.022-1.254A9.97 9.97 0 0021.543 12c-1.274 4.057-5.064 7-9.542 7m-1.724-1.724l-3.29-3.29" /></svg>
                </button>
            </div>
            <x-input-error for="password" class="mt-2 text-xs text-red-500 font-bold" />
        </div>

        <div class="md:col-span-1">
            <label for="password_confirmation" class="{{ $labelClass }}">{{ __('تأیید رمز عبور جدید') }}</label>
            <div class="relative">
                <input :type="show ? 'text' : 'password'" id="password_confirmation" class="{{ $inputClass }} dir-ltr text-left font-mono !pr-11" wire:model="state.password_confirmation" autocomplete="new-password" placeholder="••••••••" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 focus:outline-none transition-colors" tabindex="-1">
                    <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    <svg x-show="show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0l1.414 1.414m12.022-1.254A9.97 9.97 0 0021.543 12c-1.274 4.057-5.064 7-9.542 7m-1.724-1.724l-3.29-3.29" /></svg>
                </button>
            </div>
            <x-input-error for="password_confirmation" class="mt-2 text-xs text-red-500 font-bold" />
        </div>
    </div>

    {{-- فوتر و ذخیره --}}
    <div class="{{ $footerClass }}">
        <x-action-message class="me-3 text-sm font-bold text-emerald-600 dark:text-emerald-400" on="saved">
            <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ __('رمز عبور با موفقیت تغییر یافت.') }}
            </span>
        </x-action-message>

        <button type="submit" wire:loading.attr="disabled" class="{{ $btnPrimaryClass }}">
            <span wire:loading.remove wire:target="updatePassword" class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                {{ __('ذخیره تغییرات') }}
            </span>
            <span wire:loading wire:target="updatePassword" class="flex items-center gap-2">
                <span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                {{ __('در حال ذخیره...') }}
            </span>
        </button>
    </div>
</form>
