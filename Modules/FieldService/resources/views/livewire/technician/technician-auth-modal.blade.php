<div x-data="{
        isOpen: @entangle('isOpen'),
        allowClose: @entangle('allowClose'),
        step: @entangle('step'),
        authFlow: @entangle('authFlow'),
        resendTimer: @entangle('otpResendIn'),
        selectedProvince: @entangle('province'),
        selectedCity: @entangle('city'),
        allData: @js($allData),
        isAuthenticated: {{ (Auth::check() && Auth::user()->hasRole('technician') && \Modules\FieldService\App\Models\Technician::where('user_id', Auth::id())->exists()) ? 'true' : 'false' }},
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
     @keydown.escape.window="if (allowClose || isAuthenticated) isOpen = false">

    {{-- Backdrop & Overlay --}}
    <div x-show="isOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-950/70 dark:bg-slate-950/85 backdrop-blur-xs overflow-y-auto">

        {{-- Dialog Box --}}
        <div @click.outside="if (allowClose || isAuthenticated) isOpen = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-6 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-6 scale-95"
             :class="step <= 2 ? 'max-w-md' : 'max-w-xl'"
             class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl overflow-hidden font-sans relative my-auto transition-all duration-300">

            {{-- Close Button --}}
            <template x-if="allowClose || isAuthenticated">
                <button type="button" @click="isOpen = false"
                        class="absolute left-4 top-4 w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition z-20 shadow-xs cursor-pointer"
                        title="بستن پنجره">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </template>

            {{-- Header Section --}}
            <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800/80 bg-slate-50 dark:bg-slate-950/40">
                <div class="flex items-center gap-3.5">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-600/15 border border-indigo-200 dark:border-indigo-500/30 text-indigo-600 dark:text-indigo-400 text-2xl shrink-0">
                        👷‍♂️
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-base font-bold text-slate-800 dark:text-white truncate">
                                <span x-show="step === 1">ورود به ناوگان {{ $platformName ?? '' }}</span>
                                <span x-show="step === 2 && authFlow === 'login'">تایید ورود تکنسین</span>
                                <span x-show="step === 2 && authFlow === 'register'">احراز هویت شماره</span>
                                <span x-show="step === 3">تکمیل ثبت‌نام تکنسین</span>
                            </h3>
                        </div>

                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 truncate">
                            <span x-show="step === 1">جهت ورود یا ثبت‌نام، شماره موبایل خود را وارد نمایید.</span>
                            <span x-show="step === 2">کد تایید ۵ رقمی پیامک‌شده را وارد کنید.</span>
                            <span x-show="step === 3">اطلاعات هویتی و کاری اولیه را وارد کنید.</span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Alerts & Notifications --}}
            @if($errorMessage || $successMessage)
            <div class="px-6 pt-5">
                @if($errorMessage)
                <div class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-500/15 border border-rose-200 dark:border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs font-bold flex items-center gap-2.5">
                    <svg class="w-5 h-5 flex-shrink-0 text-rose-500 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ $errorMessage }}</span>
                </div>
                @endif

                @if($successMessage)
                <div class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs font-bold flex items-center gap-2.5">
                    <svg class="w-5 h-5 flex-shrink-0 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $successMessage }}</span>
                </div>
                @endif
            </div>
            @endif

            {{-- Body Content --}}
            <div class="px-6 py-6">

                {{-- ============================================================= --}}
                {{-- STEP 1: PHONE NUMBER INPUT & DETECTION                        --}}
                {{-- ============================================================= --}}
                @if($step === 1)
                <div class="space-y-6" x-transition.opacity.duration.250ms>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                            شماره تلفن همراه تکنسین
                            <span class="text-indigo-600 dark:text-indigo-400 mr-1">*</span>
                        </label>
                        
                        <div class="flex flex-row items-center rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700/90 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/20 transition overflow-hidden" dir="ltr">
                            <div class="px-3.5 py-3.5 bg-slate-100 dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 flex items-center gap-1.5 select-none shrink-0">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <span class="text-xs font-bold font-sans text-slate-600 dark:text-slate-300">+98</span>
                            </div>

                            <input type="tel" wire:model="phone" placeholder="09112345678" maxlength="11" autofocus
                                   @keydown.enter.prevent="$wire.submitPhone()"
                                   class="flex-1 h-12 px-4 bg-transparent text-slate-800 dark:text-white font-sans text-base tracking-wider outline-none text-left placeholder:text-slate-400 dark:placeholder:text-slate-600 placeholder:tracking-normal">
                        </div>
                    </div>

                    <button type="button" wire:click="submitPhone" wire:loading.attr="disabled"
                            class="w-full h-12 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-75 disabled:cursor-wait text-white font-bold text-sm rounded-2xl shadow-md transition active:scale-98 flex items-center justify-center">
                        <div wire:loading.remove wire:target="submitPhone" class="flex items-center justify-center gap-2">
                            <span>ادامه و بررسی شماره</span>
                            <span class="text-base leading-none">←</span>
                        </div>
                        <div wire:loading.flex wire:target="submitPhone" class="items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>در حال بررسی...</span>
                        </div>
                    </button>
                </div>
                @endif

                {{-- ============================================================= --}}
                {{-- STEP 2: OTP VERIFICATION                                      --}}
                {{-- ============================================================= --}}
                @if($step === 2)
                <div class="space-y-6" x-transition.opacity.duration.250ms>
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full {{ $authFlow === 'login' ? 'bg-emerald-500' : 'bg-indigo-500' }}"></span>
                            <div>
                                <span class="text-slate-500 dark:text-slate-400 font-medium">
                                    {{ $authFlow === 'login' ? 'ورود به حساب:' : 'ثبت‌نام شماره:' }}
                                </span>
                                <span class="font-bold text-slate-800 dark:text-white font-sans mr-1" dir="ltr">{{ $phone }}</span>
                                @if($recognizedName)
                                    <span class="text-[11px] text-indigo-600 dark:text-indigo-400 font-bold mr-1">({{ $recognizedName }})</span>
                                @endif
                            </div>
                        </div>
                        <button type="button" wire:click="changePhone"
                                class="px-2.5 py-1 rounded-xl bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-[11px] transition active:scale-95">
                            تغییر شماره
                        </button>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">کد تایید ۵ رقمی پیامک شده</label>
                        <input type="text" wire:model="otpCode" placeholder="• • • • •" dir="ltr" inputmode="numeric" maxlength="6" autofocus
                               @keydown.enter.prevent="$wire.verifyOtp()"
                               class="w-full h-12 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700/90 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-800 dark:text-white font-sans text-xl tracking-[0.4em] text-center outline-none transition placeholder:text-slate-400 dark:placeholder:text-slate-700">
                    </div>

                    <button type="button" wire:click="verifyOtp" wire:loading.attr="disabled"
                            class="w-full h-12 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-75 disabled:cursor-wait text-white font-bold text-sm rounded-2xl shadow-md transition active:scale-98 flex items-center justify-center">
                        <div wire:loading.remove wire:target="verifyOtp" class="flex items-center justify-center gap-2">
                            <span>تایید کد و ادامه</span>
                            <span class="text-base leading-none">←</span>
                        </div>
                        <div wire:loading.flex wire:target="verifyOtp" class="items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>در حال بررسی کد...</span>
                        </div>
                    </button>

                    <div class="text-center pt-1">
                        <button type="button" wire:click="resendOtp"
                                :disabled="resendTimer > 0"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-950 hover:bg-slate-200 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 text-xs font-bold text-indigo-600 dark:text-indigo-400 disabled:opacity-50 disabled:cursor-not-allowed transition active:scale-95">
                            <span x-show="resendTimer <= 0">ارسال مجدد کد پیامکی</span>
                            <span x-show="resendTimer > 0">ارسال مجدد تا <span x-text="resendTimer" class="font-bold"></span> ثانیه دیگر</span>
                        </button>
                    </div>
                </div>
                @endif

                {{-- ============================================================= --}}
                {{-- STEP 3: REGISTRATION - PERSONAL & LOCATION DETAILS            --}}
                {{-- ============================================================= --}}
                @if($step === 3)
                <div class="space-y-4" x-transition.opacity.duration.250ms>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">نام <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="firstName" placeholder="مثال: رضا"
                                   class="w-full h-11 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-xs text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                            @error('firstName') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">نام خانوادگی <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="lastName" placeholder="مثال: حسینی"
                                   class="w-full h-11 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-xs text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                            @error('lastName') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    @if($registerFields->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            @foreach($registerFields as $field)
                                @include('fieldservice::livewire.technician.partials.custom-field-input', [
                                    'field'         => $field,
                                    'customFiles'   => $customFiles,
                                    'existingFiles' => [],
                                    'idPrefix'      => 'reg',
                                ])
                            @endforeach
                        </div>
                    @endif

                    <div class="pt-2">
                        <button type="button" wire:click="submitRegistration" wire:loading.attr="disabled"
                                class="w-full h-12 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-75 disabled:cursor-wait text-white font-bold text-sm rounded-2xl shadow-md transition active:scale-98 flex items-center justify-center">
                            <div wire:loading.remove wire:target="submitRegistration" class="flex items-center justify-center gap-2">
                                <span>تکمیل ثبت‌نام و ورود</span>
                                <span class="text-base leading-none">✓</span>
                            </div>
                            <div wire:loading.flex wire:target="submitRegistration" class="items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>در حال ایجاد حساب...</span>
                            </div>
                        </button>
                    </div>
                </div>
                @endif

            </div>

            {{-- Footer Branding --}}
            <div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800/60 bg-slate-50 dark:bg-slate-950/60 text-center">
                <span class="text-[11px] text-slate-500 font-sans">
                    ناوگان تخصصی خدمات فنی و اعزام فوری تکنسین
                </span>
            </div>

        </div>
    </div>
</div>
