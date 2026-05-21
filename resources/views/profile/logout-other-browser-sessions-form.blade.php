@php
    $cardClass = "bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm mb-6";
    $headerClass = "px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4";
    $titleClass = "text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2";
    $descClass = "text-xs text-gray-500 dark:text-gray-400 mt-1";
    $inputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-600";
    $btnPrimaryClass = "inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:shadow-indigo-500/50 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed";
    $btnSecondaryClass = "inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white border border-gray-200 text-sm font-bold text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white";
    $footerClass = "px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-start gap-4";
@endphp

<div class="{{ $cardClass }}">
    {{-- هدر --}}
    <div class="{{ $headerClass }}">
        <div>
            <h2 class="{{ $titleClass }}">
                <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                {{ __('نشست‌های فعال مرورگر') }}
            </h2>
            <p class="{{ $descClass }}">
                {{ __('نشست‌های فعال خود در مرورگرها و دستگاه‌های دیگر را مدیریت کرده و از آن‌ها خارج شوید.') }}
            </p>
        </div>
    </div>

    {{-- محتوا --}}
    <div class="p-6">
        <div class="max-w-full text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
            {{ __('در صورت لزوم، می‌توانید از تمام نشست‌های فعال خود در دستگاه‌های دیگر خارج شوید. برخی از نشست‌های اخیر شما در زیر فهرست شده‌اند؛ با این حال، این فهرست ممکن است کامل نباشد. اگر احساس می‌کنید حساب شما در خطر است، باید رمز عبور خود را نیز تغییر دهید.') }}
        </div>

        @if (count($this->sessions) > 0)
            <div class="space-y-4 mb-6">
                <!-- Other Browser Sessions -->
                @foreach ($this->sessions as $session)
                    <div class="flex flex-wrap sm:flex-nowrap items-center gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/50 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800/50">
                        <div class="shrink-0">
                            @if ($session->agent->isDesktop())
                                <div class="p-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 text-cyan-600 dark:text-cyan-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                                    </svg>
                                </div>
                            @else
                                <div class="p-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 text-cyan-600 dark:text-cyan-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                {{ $session->agent->platform() ? $session->agent->platform() : __('نامشخص') }} - {{ $session->agent->browser() ? $session->agent->browser() : __('نامشخص') }}
                            </div>

                            <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                <span class="dir-ltr font-mono bg-white dark:bg-gray-800 px-2 py-1 rounded-md border border-gray-200 dark:border-gray-700 shadow-sm">{{ $session->ip_address }}</span>

                                @if ($session->is_current_device)
                                    <span class="inline-flex items-center gap-1 text-emerald-700 dark:text-emerald-300 font-bold bg-emerald-100 dark:bg-emerald-900/50 px-2.5 py-1 rounded-md border border-emerald-200 dark:border-emerald-800/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        {{ __('همین دستگاه') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> {{ __('آخرین فعالیت:') }} <span class="dir-ltr inline-block">{{ $session->last_active }}</span></span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="flex items-center gap-4">
            <button type="button" wire:click="confirmLogout" wire:loading.attr="disabled" class="{{ $btnPrimaryClass }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                {{ __('خروج از سایر نشست‌ها') }}
            </button>

            <x-action-message class="text-sm font-bold text-emerald-600 dark:text-emerald-400" on="loggedOut">
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ __('انجام شد.') }}
                </span>
            </x-action-message>
        </div>
    </div>

    <!-- Log Out Other Devices Confirmation Modal -->
    <x-dialog-modal wire:model.live="confirmingLogout">
        <x-slot name="title">
            <div class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                {{ __('خروج از سایر نشست‌های مرورگر') }}
            </div>
        </x-slot>

        <x-slot name="content">
            <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed mb-4">
                {{ __('لطفاً برای تأیید خروج از تمامی نشست‌های فعال خود در دستگاه‌های دیگر، رمز عبور خود را وارد کنید.') }}
            </p>

            <div x-data="{}" x-on:confirming-logout-other-browser-sessions.window="setTimeout(() => $refs.password.focus(), 250)">
                <input type="password" class="{{ $inputClass }} dir-ltr text-left font-mono"
                       autocomplete="current-password"
                       placeholder="{{ __('رمز عبور') }}"
                       x-ref="password"
                       wire:model="password"
                       wire:keydown.enter="logoutOtherBrowserSessions" />

                <x-input-error for="password" class="mt-2 text-xs text-red-500 font-bold" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <button type="button" wire:click="$toggle('confirmingLogout')" wire:loading.attr="disabled" class="{{ $btnSecondaryClass }}">
                {{ __('انصراف') }}
            </button>

            <button type="button" wire:click="logoutOtherBrowserSessions" wire:loading.attr="disabled" class="{{ $btnPrimaryClass }} ms-3 bg-cyan-600 hover:bg-cyan-700 shadow-cyan-500/30">
                {{ __('خروج از سایر نشست‌ها') }}
            </button>
        </x-slot>
    </x-dialog-modal>
</div>
