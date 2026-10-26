<div x-data="{
        isOpen: @entangle('isOpen'),
        resendTimer: @entangle('otpResendIn'),
        isAuthenticated: {{ Auth::guard('client')->check() ? 'true' : 'false' }},
        timerInterval: null,
        startTimer() {
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerInterval = setInterval(() => {
                if (this.resendTimer > 0) {
                    this.resendTimer--;
                } else {
                    clearInterval(this.timerInterval);
                }
            }, 1000);
        }
     }"
     x-init="$watch('$wire.otpSent', val => { if(val) startTimer(); })"
     @keydown.escape.window="if (isAuthenticated) isOpen = false">

    {{-- Backdrop & Modal --}}
    <div x-show="isOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-md">

        {{-- Dialog Box --}}
        <div @click.outside="if (isAuthenticated) isOpen = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-6 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-6 scale-95"
             class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden font-sans relative">

            {{-- Close Button (Only for authenticated users) --}}
            @if(Auth::guard('client')->check())
            <button type="button" @click="isOpen = false"
                    class="absolute left-4 top-4 w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 flex items-center justify-center transition">
                ✕
            </button>
            @endif

            {{-- Header --}}
            <div class="px-6 pt-6 pb-4 text-center border-b border-slate-100 dark:border-slate-800/80">
                <h3 class="text-base font-black text-slate-900 dark:text-white">
                    {{ $isRegister ? 'تکمیل ثبت‌نام مشتری' : ('ورود / ثبت‌نام در ' . ($platformName ?? 'سامانه')) }}
                </h3>
                @if($isRegister)
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    شماره موبایل تایید شد، نام خود را وارد فرمایید.
                </p>
                @endif

                {{-- Mode Switch Tabs (If mode === 'both' and not in registration) --}}
                @if($mode === 'both' && !$isRegister)
                <div class="flex items-center justify-center p-1 bg-slate-100 dark:bg-slate-800 rounded-2xl mt-4 max-w-xs mx-auto">
                    <button type="button" wire:click="switchTab('otp')"
                            class="flex-1 py-1.5 text-xs font-bold rounded-xl transition {{ $tab === 'otp' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-300 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
                        ورود با پیامک (OTP)
                    </button>
                    <button type="button" wire:click="switchTab('password')"
                            class="flex-1 py-1.5 text-xs font-bold rounded-xl transition {{ $tab === 'password' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-300 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
                        ورود با رمز عبور
                    </button>
                </div>
                @endif
            </div>

            {{-- Alerts --}}
            <div class="px-6 pt-3">
                @if($errorMessage)
                <div class="p-3 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-bold flex items-center gap-2">
                    <span>⚠️</span>
                    <span>{{ $errorMessage }}</span>
                </div>
                @endif

                @if($successMessage)
                <div class="p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-bold flex items-center gap-2">
                    <span>✓</span>
                    <span>{{ $successMessage }}</span>
                </div>
                @endif
            </div>

            {{-- Body Content --}}
            <div class="p-6">
                {{-- ------------------------------------------------------------- --}}
                {{-- 1. OTP WORKFLOW                                               --}}
                {{-- ------------------------------------------------------------- --}}
                @if($tab === 'otp')
                    @if(!$otpSent)
                        {{-- Step 1: Request Phone Number --}}
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">شماره همراه</label>
                                <input type="tel" wire:model="otpUsername" placeholder="0911xxxxxxx" dir="ltr"
                                       class="w-full h-12 px-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-sans text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1.5">
                                    کد ورود ۴ یا ۵ رقمی به این شماره پیامک می‌شود.
                                </p>
                            </div>

                            <button type="button" wire:click="sendOtp"
                                    class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-2xl shadow-lg shadow-indigo-600/25 transition active:scale-95 flex items-center justify-center gap-2">
                                <span>ارسال کد ورود</span>
                                <span>←</span>
                            </button>
                        </div>
                    @else
                        {{-- Step 2: Verify OTP Code & Full Name if registering --}}
                        <div class="space-y-4">
                            {{-- Phone confirmation bar --}}
                            <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700 flex items-center justify-between text-xs">
                                <div>
                                    <span class="text-slate-500 dark:text-slate-400 font-medium">شماره موبایل:</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-100 font-sans mr-1" dir="ltr">{{ $otpUsername }}</span>
                                </div>
                                <button type="button" wire:click="resetOtp"
                                        class="px-2.5 py-1 rounded-xl bg-slate-200/80 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-650 text-slate-700 dark:text-slate-200 font-bold text-[11px] transition active:scale-95">
                                    ویرایش شماره
                                </button>
                            </div>

                            {{-- Code input --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">کد تایید پیامکی</label>
                                <input type="text" wire:model="otpCode" placeholder="کد پیامک شده" dir="ltr" inputmode="numeric"
                                       class="w-full h-12 text-center text-lg tracking-widest font-black rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans">
                            </div>

                            {{-- If new user: Ask for Full Name --}}
                            @if($isRegister)
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">نام و نام خانوادگی کارفرما *</label>
                                <input type="text" wire:model="registerFullName" placeholder="مثال: علی حسینی"
                                       class="w-full h-12 px-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-sans text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            @endif

                            <button type="button" wire:click="verifyOtp"
                                    class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-2xl shadow-lg shadow-emerald-600/25 transition active:scale-95 flex items-center justify-center gap-2">
                                <span>{{ $isRegister ? 'تکمیل ثبت‌نام و ورود' : 'تایید و ورود' }}</span>
                                <span>✓</span>
                            </button>

                            {{-- Resend button with countdown timer --}}
                            <div class="text-center pt-1">
                                <button type="button" wire:click="sendOtp"
                                        :disabled="resendTimer > 0"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-xs font-bold text-indigo-600 dark:text-indigo-400 disabled:opacity-50 disabled:hover:bg-slate-100 dark:disabled:hover:bg-slate-800 disabled:cursor-not-allowed transition active:scale-95 shadow-xs">
                                    <span x-show="resendTimer <= 0">ارسال مجدد کد پیامکی</span>
                                    <span x-show="resendTimer > 0">ارسال مجدد تا <span x-text="resendTimer"></span> ثانیه دیگر</span>
                                </button>
                            </div>
                        </div>
                    @endif
                @endif

                {{-- ------------------------------------------------------------- --}}
                {{-- 2. PASSWORD WORKFLOW                                          --}}
                {{-- ------------------------------------------------------------- --}}
                @if($tab === 'password')
                    <div class="space-y-4">
                        @if($pwdStep === 1)
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">شماره همراه یا نام کاربری</label>
                                <input type="text" wire:model="username" placeholder="0911xxxxxxx یا نام کاربری" dir="ltr"
                                       class="w-full h-12 px-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-sans text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <button type="button" wire:click="checkPasswordStep"
                                    class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-2xl shadow-lg shadow-indigo-600/25 transition active:scale-95 flex items-center justify-center gap-2">
                                <span>مرحله بعد</span>
                                <span>←</span>
                            </button>
                        @else
                            <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700 flex items-center justify-between text-xs">
                                <div>
                                    <span class="text-slate-500 dark:text-slate-400">کاربر:</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-100 font-sans mr-1" dir="ltr">{{ $username }}</span>
                                </div>
                                <button type="button" wire:click="$set('pwdStep', 1)"
                                        class="px-2.5 py-1 rounded-xl bg-slate-200/80 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-650 text-slate-700 dark:text-slate-200 font-bold text-[11px] transition active:scale-95">
                                    تغییر کاربر
                                </button>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">رمز عبور</label>
                                <input type="password" wire:model="password" placeholder="••••••••" dir="ltr"
                                       class="w-full h-12 px-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-sans text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <button type="button" wire:click="loginWithPassword"
                                    class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-2xl shadow-lg shadow-indigo-600/25 transition active:scale-95 flex items-center justify-center gap-2">
                                <span>ورود با رمز عبور</span>
                                <span>✓</span>
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Footer Trust Note --}}
            <div class="px-6 py-3 bg-slate-50/80 dark:bg-slate-850 border-t border-slate-100 dark:border-slate-800 text-center text-[11px] text-slate-400">
                <span>امنیت اطلاعات و حریم خصوصی تضمین شده {{ $platformName ?? '' }}</span>
            </div>

        </div>
    </div>
</div>
