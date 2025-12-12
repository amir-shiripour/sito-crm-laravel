@php
    $otpSent      = (bool) ($otpSent ?? false);
    $otpUsername  = $otpUsername ?? old('username', '');
    $otpResendIn  = (int) ($otpResendIn ?? 60);
@endphp

{{-- پیام‌های خطای otp --}}
@if($errors->has('code') || $errors->has('username'))
    <div class="rounded-xl bg-red-50 p-3 text-xs font-medium text-red-600 dark:bg-red-900/10 dark:text-red-400 border border-red-100 dark:border-red-900/20 mb-4 flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ $errors->first('code') ?: $errors->first('username') }}</span>
    </div>
@endif

@if(session('otp_sent'))
    <div class="rounded-xl bg-emerald-50 p-3 text-xs font-medium text-emerald-700 dark:bg-emerald-900/10 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-900/20 mb-4">
        کد ورود ارسال شد. لطفاً کد را وارد کنید.
    </div>
@endif

{{-- مرحله ۱: ارسال کد --}}
@if(! $otpSent)
    <form method="POST" action="{{ route('client.otp.send') }}" class="space-y-5">
        @csrf

        <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نام کاربری</label>
            <div class="relative">
                <input type="text"
                       name="username"
                       value="{{ old('username') }}"
                       required
                       autofocus
                       class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 pl-10 pr-4 text-sm text-gray-900
                              placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20
                              transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900 dir-ltr"
                       placeholder="Username">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                کد یکبار مصرف به شماره موبایل ثبت‌شده برای این کاربر ارسال می‌شود.
            </p>
        </div>

        <button type="submit"
                class="group relative flex w-full justify-center rounded-xl bg-indigo-600 py-3 px-4 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 hover:shadow-indigo-600/40 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:ring-offset-2 transition-all active:scale-[0.98]">
            ارسال کد ورود
            <svg class="mr-2 h-4 w-4 opacity-70 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" />
            </svg>
        </button>

        <div class="pt-4 mt-2 border-t border-dashed border-gray-200 dark:border-gray-700 text-center">
            <p class="text-[10px] text-gray-400 dark:text-gray-500">
                اگر پیامک دریافت نکردید، بعد از محدودیت زمانی تعیین‌شده دوباره تلاش کنید.
            </p>
        </div>
    </form>

@else
    {{-- مرحله ۲: تایید کد --}}
    <form method="POST" action="{{ route('client.otp.verify') }}" class="space-y-5">
        @csrf

        <input type="hidden" name="username" value="{{ $otpUsername }}">

        <div class="rounded-xl bg-gray-50 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-700 p-3 text-xs text-gray-600 dark:text-gray-300">
            نام کاربری:
            <span class="font-mono dir-ltr">{{ $otpUsername }}</span>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">کد پیامکی</label>
            <input type="text"
                   name="code"
                   inputmode="numeric"
                   autocomplete="one-time-code"
                   required
                   class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 px-4 text-sm text-gray-900
                          placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20
                          transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900 dir-ltr"
                   placeholder="مثلاً 12345">
            <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                اگر کد منقضی شد، می‌توانید ارسال مجدد انجام دهید.
            </p>
        </div>

        <button type="submit"
                class="group relative flex w-full justify-center rounded-xl bg-indigo-600 py-3 px-4 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 hover:shadow-indigo-600/40 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:ring-offset-2 transition-all active:scale-[0.98]">
            ورود با کد
        </button>
    </form>

    {{-- ارسال مجدد (فرم جدا، خارج از فرم verify) --}}
    <form method="POST" action="{{ route('client.otp.send') }}" class="mt-3">
        @csrf
        <input type="hidden" name="username" value="{{ $otpUsername }}">

        <button type="submit"
                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 py-2 text-xs text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900/40 transition">
            ارسال مجدد کد (طبق محدودیت {{ $otpResendIn }} ثانیه)
        </button>
    </form>
@endif
