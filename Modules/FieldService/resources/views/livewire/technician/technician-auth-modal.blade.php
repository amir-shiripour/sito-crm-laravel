<div x-data="{
        isOpen: @entangle('isOpen'),
        resendTimer: @entangle('otpResendIn'),
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
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/85 backdrop-blur-md overflow-y-auto">

        {{-- Dialog Box (Dark Fleet Driver Cockpit Design) --}}
        <div @click.outside="if (isAuthenticated) isOpen = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-6 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-6 scale-95"
             class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden font-sans relative my-auto">

            {{-- Close Button (Only if already authenticated as a technician) --}}
            <template x-if="isAuthenticated">
                <button type="button" @click="isOpen = false"
                        class="absolute left-4 top-4 w-8 h-8 rounded-full bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center transition">
                    ✕
                </button>
            </template>

            {{-- Top Branding Header --}}
            <div class="px-6 pt-6 pb-4 text-center border-b border-slate-800/80 bg-slate-950/40">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600/20 border border-indigo-500/30 text-indigo-400 text-2xl shadow-inner mb-3">
                    👷‍♂️
                </div>
                <h3 class="text-base font-black text-white">
                    {{ $isRegister ? 'تکمیل عضویت در ناوگان نصاب‌باشی' : 'ورود سفیران و تکنسین‌های فنی' }}
                </h3>
                <p class="text-xs text-slate-400 mt-1">
                    @if($isRegister)
                    مشخصات فنی و شهر فعالیت خود را جهت شروع کار ثبت فرمایید.
                    @else
                    سامانه هوشمند اعزام و مدیریت ماموریت‌های استانی
                    @endif
                </p>

                {{-- Mode Switch Tabs (If not in registration form) --}}
                @if(!$isRegister)
                <div class="flex items-center justify-center p-1 bg-slate-800/80 rounded-2xl mt-4 max-w-xs mx-auto border border-slate-700/50">
                    <button type="button" wire:click="switchTab('otp')"
                            class="flex-1 py-1.5 text-xs font-bold rounded-xl transition {{ $tab === 'otp' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200' }}">
                        ورود با پیامک (OTP)
                    </button>
                    <button type="button" wire:click="switchTab('password')"
                            class="flex-1 py-1.5 text-xs font-bold rounded-xl transition {{ $tab === 'password' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200' }}">
                        ورود با رمز عبور
                    </button>
                </div>
                @endif
            </div>

            {{-- Status Messages & Error Alerts --}}
            <div class="px-6 pt-3">
                @if($errorMessage)
                <div class="p-3 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs font-bold flex items-center gap-2">
                    <span class="shrink-0">⚠️</span>
                    <span>{{ $errorMessage }}</span>
                </div>
                @endif

                @if($successMessage)
                <div class="p-3 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs font-bold flex items-center gap-2">
                    <span class="shrink-0">✓</span>
                    <span>{{ $successMessage }}</span>
                </div>
                @endif
            </div>

            {{-- Modal Body --}}
            <div class="p-6">
                {{-- ============================================================= --}}
                {{-- 1. OTP WORKFLOW                                               --}}
                {{-- ============================================================= --}}
                @if($tab === 'otp')
                    @if(!$otpSent)
                        {{-- Step 1: Request Phone Number --}}
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-300 mb-1.5">شماره همراه تکنسین</label>
                                <input type="tel" wire:model="otpUsername" placeholder="09112223344" dir="ltr"
                                       class="w-full h-12 px-4 rounded-2xl bg-slate-950 border border-slate-700 text-sm font-sans text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-left">
                                <p class="text-[11px] text-slate-400 mt-1.5">
                                    کد ورود به این شماره پیامک می‌شود. (شماره‌های فعال ثبت‌شده در ناوگان بلافاصله شناسایی می‌شوند)
                                </p>
                            </div>

                            <button type="button" wire:click="sendOtp"
                                    class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm rounded-2xl shadow-lg shadow-indigo-600/30 transition active:scale-95 flex items-center justify-center gap-2">
                                <span>ارسال کد ورود</span>
                                <span>←</span>
                            </button>
                        </div>
                    @else
                        {{-- Step 2: Verify OTP Code & Register Form if new --}}
                        <div class="space-y-4">
                            {{-- Phone indicator bar --}}
                            <div class="p-3 rounded-2xl bg-slate-950/80 border border-slate-800 flex items-center justify-between text-xs">
                                <div>
                                    <span class="text-slate-400 font-medium">شماره همراه:</span>
                                    <span class="font-bold text-indigo-400 font-sans mr-1" dir="ltr">{{ $otpUsername }}</span>
                                </div>
                                <button type="button" wire:click="resetOtp" class="text-indigo-400 font-bold hover:underline">
                                    تغییر شماره
                                </button>
                            </div>

                            {{-- Code input --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-300 mb-1.5">کد تایید ۵ رقمی پیامک شده</label>
                                <input type="text" wire:model="otpCode" placeholder="کد پیامک شده" dir="ltr" inputmode="numeric"
                                       class="w-full h-12 text-center text-lg tracking-widest font-black rounded-2xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-sans">
                            </div>

                            {{-- If New Technician Registration: Collect Details --}}
                            @if($isRegister)
                            <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800 space-y-3">
                                <div class="text-[11px] font-bold text-indigo-300 border-b border-slate-800 pb-2">
                                    مشخصات فردی و تخصص‌های شما:
                                </div>

                                <div class="grid grid-cols-2 gap-2.5">
                                    <div>
                                        <label class="block text-[11px] text-slate-400 mb-1">نام *</label>
                                        <input type="text" wire:model="registerFirstName" placeholder="مثال: محمد"
                                               class="w-full h-10 px-3 rounded-xl bg-slate-900 border border-slate-700 text-xs font-sans text-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-slate-400 mb-1">نام خانوادگی *</label>
                                        <input type="text" wire:model="registerLastName" placeholder="مثال: قاسمی"
                                               class="w-full h-10 px-3 rounded-xl bg-slate-900 border border-slate-700 text-xs font-sans text-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2.5">
                                    <div>
                                        <label class="block text-[11px] text-slate-400 mb-1">کد ملی</label>
                                        <input type="text" wire:model="registerNationalCode" placeholder="۱۰ رقمی" dir="ltr"
                                               class="w-full h-10 px-3 rounded-xl bg-slate-900 border border-slate-700 text-xs font-sans text-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-slate-400 mb-1">شهر فعالیت *</label>
                                        <select wire:model="registerCity"
                                                class="w-full h-10 px-3 rounded-xl bg-slate-900 border border-slate-700 text-xs font-sans text-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            @foreach($coveredCities as $cityName => $cityInfo)
                                                <option value="{{ $cityName }}">{{ $cityName }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] text-slate-400 mb-1.5">تخصص‌ها و حوزه‌های کاری شما *</label>
                                    <div class="grid grid-cols-2 gap-1.5 max-h-32 overflow-y-auto p-1 bg-slate-900 rounded-xl border border-slate-800">
                                        @foreach($categories as $cat)
                                            <label class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-800 text-[11px] text-slate-200 cursor-pointer">
                                                <input type="checkbox" wire:model="registerCategories" value="{{ $cat->id }}"
                                                       class="w-3.5 h-3.5 rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                                                <span class="truncate">{{ $cat->icon }} {{ $cat->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            <button type="button" wire:click="verifyOtp"
                                    class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm rounded-2xl shadow-lg shadow-emerald-600/30 transition active:scale-95 flex items-center justify-center gap-2">
                                <span>{{ $isRegister ? 'تکمیل ثبت‌نام و ورود به کنسول' : 'تایید کد و شروع به کار' }}</span>
                                <span>✓</span>
                            </button>

                            {{-- Resend timer --}}
                            <div class="text-center pt-1">
                                <button type="button" wire:click="sendOtp"
                                        :disabled="resendTimer > 0"
                                        class="text-xs font-bold text-indigo-400 disabled:text-slate-500 disabled:cursor-not-allowed hover:underline transition">
                                    <span x-show="resendTimer <= 0">ارسال مجدد کد پیامکی</span>
                                    <span x-show="resendTimer > 0">ارسال مجدد تا <span x-text="resendTimer"></span> ثانیه دیگر</span>
                                </button>
                            </div>
                        </div>
                    @endif
                @endif

                {{-- ============================================================= --}}
                {{-- 2. PASSWORD WORKFLOW                                          --}}
                {{-- ============================================================= --}}
                @if($tab === 'password')
                    <div class="space-y-4">
                        @if($pwdStep === 1)
                            <div>
                                <label class="block text-xs font-bold text-slate-300 mb-1.5">شماره همراه یا نام کاربری</label>
                                <input type="text" wire:model="username" placeholder="0911xxxxxxx یا نام کاربری" dir="ltr"
                                       class="w-full h-12 px-4 rounded-2xl bg-slate-950 border border-slate-700 text-sm font-sans text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-left">
                            </div>

                            <button type="button" wire:click="checkPasswordStep"
                                    class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm rounded-2xl shadow-lg shadow-indigo-600/30 transition active:scale-95 flex items-center justify-center gap-2">
                                <span>مرحله بعد</span>
                                <span>←</span>
                            </button>
                        @else
                            <div class="p-3 rounded-2xl bg-slate-950/80 border border-slate-800 flex items-center justify-between text-xs">
                                <div>
                                    <span class="text-slate-400">شناسه تکنسین:</span>
                                    <span class="font-bold text-indigo-400 font-sans mr-1" dir="ltr">{{ $username }}</span>
                                </div>
                                <button type="button" wire:click="$set('pwdStep', 1)" class="text-indigo-400 font-bold hover:underline">
                                    تغییر
                                </button>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-300 mb-1.5">رمز عبور</label>
                                <input type="password" wire:model="password" placeholder="••••••••" dir="ltr"
                                       class="w-full h-12 px-4 rounded-2xl bg-slate-950 border border-slate-700 text-sm font-sans text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-left">
                            </div>

                            <button type="button" wire:click="loginWithPassword"
                                    class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm rounded-2xl shadow-lg shadow-indigo-600/30 transition active:scale-95 flex items-center justify-center gap-2">
                                <span>ورود به کنسول سفیران</span>
                                <span>✓</span>
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Footer Trust Badge --}}
            <div class="px-6 py-3 bg-slate-950 border-t border-slate-800/80 text-center text-[11px] text-slate-400">
                <span>ناوگان تخصصی خدمات فنی و اعزام فوری نصاب‌باشی | استان مازندران</span>
            </div>

        </div>
    </div>
</div>
