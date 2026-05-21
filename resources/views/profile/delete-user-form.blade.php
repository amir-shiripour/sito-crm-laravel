@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm mb-6";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4";
    $titleClass = "text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2";
    $descClass = "text-xs text-gray-500 dark:text-gray-400 mt-1";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $btnDangerClass = "inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl bg-red-600 text-white font-bold text-sm shadow-lg shadow-red-500/30 hover:bg-red-700 hover:shadow-red-500/50 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed";
    $btnSecondaryClass = "inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white border border-gray-200 text-sm font-bold text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white";
@endphp

<div class="{{ $cardClass }} border-red-200 dark:border-red-900/50 overflow-hidden">
    {{-- هدر --}}
    <div class="{{ $headerClass }} bg-red-50/50 dark:bg-red-900/10 border-red-100 dark:border-red-900/50">
        <div>
            <h2 class="{{ $titleClass }} text-red-700 dark:text-red-400">
                <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                {{ __('حذف حساب کاربری') }}
            </h2>
            <p class="{{ $descClass }} text-red-500/80 dark:text-red-400/80">
                {{ __('حذف دائمی حساب کاربری شما.') }}
            </p>
        </div>
    </div>

    {{-- محتوا --}}
    <div class="p-6">
        <div class="max-w-full text-sm text-red-800 dark:text-red-300 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 leading-relaxed font-medium mb-6">
            <svg class="w-5 h-5 inline-block rtl:ml-1 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            {{ __('پس از حذف حساب کاربری شما، تمام منابع و داده‌های آن به‌طور دائم حذف خواهند شد. قبل از حذف حساب، لطفاً هرگونه داده یا اطلاعاتی را که مایل به نگهداری آن هستید، دانلود کنید.') }}
        </div>

        <div>
            <button type="button" wire:click="confirmUserDeletion" wire:loading.attr="disabled" class="{{ $btnDangerClass }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                {{ __('حذف دائمی حساب کاربری') }}
            </button>
        </div>

        <!-- Delete User Confirmation Modal -->
        <x-dialog-modal wire:model.live="confirmingUserDeletion">
            <x-slot name="title">
                <div class="font-bold text-red-600 dark:text-red-400 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    {{ __('تایید حذف حساب کاربری') }}
                </div>
            </x-slot>

            <x-slot name="content">
                <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed font-medium mb-4">
                    {{ __('آیا از حذف حساب کاربری خود اطمینان دارید؟ این عملیات غیرقابل بازگشت است و تمام اطلاعات شما برای همیشه حذف خواهد شد. لطفاً برای تأیید، رمز عبور خود را وارد کنید.') }}
                </p>

                <div x-data="{}" x-on:confirming-delete-user.window="setTimeout(() => $refs.password.focus(), 250)">
                    <input type="password" class="{{ $inputClass }} dir-ltr text-left font-mono focus:border-red-500 focus:ring-red-500/20"
                           autocomplete="current-password"
                           placeholder="{{ __('رمز عبور خود را وارد کنید...') }}"
                           x-ref="password"
                           wire:model="password"
                           wire:keydown.enter="deleteUser" />

                    <x-input-error for="password" class="mt-2 text-xs text-red-500 font-bold" />
                </div>
            </x-slot>

            <x-slot name="footer">
                <button type="button" wire:click="$toggle('confirmingUserDeletion')" wire:loading.attr="disabled" class="{{ $btnSecondaryClass }}">
                    {{ __('انصراف و بازگشت') }}
                </button>

                <button type="button" wire:click="deleteUser" wire:loading.attr="disabled" class="{{ $btnDangerClass }} ms-3">
                    {{ __('حذف دائمی حساب') }}
                </button>
            </x-slot>
        </x-dialog-modal>
    </div>
</div>
