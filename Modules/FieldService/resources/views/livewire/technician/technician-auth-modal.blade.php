<div x-data="{
        isOpen: @entangle('isOpen'),
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
     @keydown.escape.window="if (isAuthenticated) isOpen = false">

    {{-- Backdrop & Overlay --}}
    <div x-show="isOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-950/85 backdrop-blur-md overflow-y-auto">

        {{-- Dialog Box: Proportional Width & Balanced Padding --}}
        <div @click.outside="if (isAuthenticated) isOpen = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-6 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-6 scale-95"
             :class="step <= 2 ? 'max-w-md' : 'max-w-xl'"
             class="w-full bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden font-sans relative my-auto transition-all duration-300">

            {{-- Close Button (Only if already authenticated as a technician) --}}
            <template x-if="isAuthenticated">
                <button type="button" @click="isOpen = false"
                        class="absolute left-4 top-4 w-9 h-9 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition z-20 shadow-sm"
                        title="بستن پنجره">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </template>

            {{-- Header Section --}}
            <div class="px-6 py-5 border-b border-slate-800/80 bg-slate-950/40">
                <div class="flex items-center gap-3.5">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600/15 border border-indigo-500/30 text-indigo-400 text-2xl shadow-inner shrink-0">
                        👷‍♂️
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-base font-bold text-white truncate">
                                <span x-show="step === 1">ورود به ناوگان {{ $platformName ?? '' }}</span>
                                <span x-show="step === 2 && authFlow === 'login'">تایید ورود تکنسین</span>
                                <span x-show="step === 2 && authFlow === 'register'">احراز هویت شماره</span>
                                <span x-show="step === 3">مشخصات هویتی و محل فعالیت</span>
                                <span x-show="step === 4">انتخاب تخصص‌ها و بارگذاری مدارک</span>
                            </h3>

                            {{-- Step Pill for Registration --}}
                            <div x-show="step >= 3" x-cloak class="shrink-0 px-2.5 py-1 rounded-xl bg-indigo-500/15 border border-indigo-500/30 text-[11px] font-bold text-indigo-400">
                                <span x-text="step === 3 ? 'گام ۱ از ۲' : 'گام ۲ از ۲'"></span>
                            </div>
                        </div>

                        <p class="text-xs text-slate-400 mt-1 truncate">
                            <span x-show="step === 1">جهت ورود یا ثبت‌نام، شماره موبایل خود را وارد نمایید.</span>
                            <span x-show="step === 2">کد تایید ۵ رقمی پیامک‌شده را وارد کنید.</span>
                            <span x-show="step === 3">اطلاعات فردی و منطقه فعالیت خود را تکمیل فرمایید.</span>
                            <span x-show="step === 4">رسته‌های کاری و مدارک خود را ثبت کنید.</span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Alerts & Notifications --}}
            @if($errorMessage || $successMessage)
            <div class="px-6 pt-5">
                @if($errorMessage)
                <div class="p-3.5 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs font-bold flex items-center gap-2.5 animate-in fade-in">
                    <svg class="w-5 h-5 flex-shrink-0 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ $errorMessage }}</span>
                </div>
                @endif

                @if($successMessage)
                <div class="p-3.5 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs font-bold flex items-center gap-2.5 animate-in fade-in">
                    <svg class="w-5 h-5 flex-shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $successMessage }}</span>
                </div>
                @endif
            </div>
            @endif

            {{-- Body Content: Symmetrical Vertical Padding (py-6 px-6) --}}
            <div class="px-6 py-6">

                {{-- ============================================================= --}}
                {{-- STEP 1: PHONE NUMBER INPUT & DETECTION                        --}}
                {{-- ============================================================= --}}
                @if($step === 1)
                <div class="space-y-6" x-transition.opacity.duration.250ms>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-2">
                            شماره تلفن همراه تکنسین
                            <span class="text-indigo-400 mr-1">*</span>
                        </label>
                        
                        {{-- Phone Input Group: Standard LTR Container --}}
                        <div class="flex flex-row items-center rounded-2xl bg-slate-950 border border-slate-700/90 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/25 transition overflow-hidden" dir="ltr">
                            {{-- Prefix Badge (+98) on the Left --}}
                            <div class="px-3.5 py-3.5 bg-slate-900 border-r border-slate-800 text-slate-400 flex items-center gap-1.5 select-none shrink-0">
                                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <span class="text-xs font-bold font-sans text-slate-300">+98</span>
                            </div>

                            {{-- Phone Number Input --}}
                            <input type="tel" wire:model="phone" placeholder="09112345678" maxlength="11" autofocus
                                   @keydown.enter.prevent="$wire.submitPhone()"
                                   class="flex-1 h-12 px-4 bg-transparent text-white font-sans text-base tracking-wider outline-none text-left placeholder:text-slate-600 placeholder:tracking-normal">
                        </div>
                    </div>

                    <button type="button" wire:click="submitPhone" wire:loading.attr="disabled"
                            class="w-full h-12 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-75 disabled:cursor-wait text-white font-bold text-sm rounded-2xl shadow-lg shadow-indigo-600/30 transition active:scale-98 flex items-center justify-center">
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
                    {{-- Phone Status Banner --}}
                    <div class="p-3.5 rounded-2xl bg-slate-950/80 border border-slate-800 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full {{ $authFlow === 'login' ? 'bg-emerald-400' : 'bg-indigo-400' }}"></span>
                            <div>
                                <span class="text-slate-400 font-medium">
                                    {{ $authFlow === 'login' ? 'ورود به حساب:' : 'ثبت‌نام شماره:' }}
                                </span>
                                <span class="font-bold text-white font-sans mr-1" dir="ltr">{{ $phone }}</span>
                                @if($recognizedName)
                                    <span class="text-[11px] text-indigo-400 font-bold mr-1">({{ $recognizedName }})</span>
                                @endif
                            </div>
                        </div>
                        <button type="button" wire:click="changePhone"
                                class="px-2.5 py-1 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-[11px] transition active:scale-95">
                            تغییر شماره
                        </button>
                    </div>

                    {{-- 5-digit OTP Code Input --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-2">کد تایید ۵ رقمی پیامک شده</label>
                        <input type="text" wire:model="otpCode" placeholder="• • • • •" dir="ltr" inputmode="numeric" maxlength="6" autofocus
                               @keydown.enter.prevent="$wire.verifyOtp()"
                               class="w-full h-12 px-4 rounded-2xl bg-slate-950 border border-slate-700/90 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/25 text-white font-sans text-xl tracking-[0.4em] text-center outline-none transition placeholder:text-slate-700">
                    </div>

                    <button type="button" wire:click="verifyOtp" wire:loading.attr="disabled"
                            class="w-full h-12 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-75 disabled:cursor-wait text-white font-bold text-sm rounded-2xl shadow-lg shadow-indigo-600/30 transition active:scale-98 flex items-center justify-center">
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

                    {{-- Resend Timer Button --}}
                    <div class="text-center pt-1">
                        <button type="button" wire:click="resendOtp"
                                :disabled="resendTimer > 0"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-slate-950 hover:bg-slate-800 border border-slate-800 text-xs font-bold text-indigo-400 disabled:opacity-50 disabled:hover:bg-slate-950 disabled:cursor-not-allowed transition active:scale-95">
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
                    {{-- Name Fields --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">نام <span class="text-rose-400">*</span></label>
                            <input type="text" wire:model="firstName" placeholder="مثال: رضا"
                                   class="w-full h-11 px-3.5 rounded-xl bg-slate-950 border border-slate-700/90 text-xs text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                            @error('firstName') <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">نام خانوادگی <span class="text-rose-400">*</span></label>
                            <input type="text" wire:model="lastName" placeholder="مثال: حسینی"
                                   class="w-full h-11 px-3.5 rounded-xl bg-slate-950 border border-slate-700/90 text-xs text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                            @error('lastName') <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- National Code & SHABA --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">کد ملی (۱۰ رقم) <span class="text-rose-400">*</span></label>
                            <input type="text" wire:model="nationalCode" placeholder="2050000000" dir="ltr" maxlength="10"
                                   class="w-full h-11 px-3.5 rounded-xl bg-slate-950 border border-slate-700/90 text-xs text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                            @error('nationalCode') <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">شماره شبا (اختیاری)</label>
                            <div class="flex items-center rounded-xl bg-slate-950 border border-slate-700/90 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 transition overflow-hidden" dir="ltr">
                                <span class="px-2.5 py-2.5 bg-slate-900 border-r border-slate-800 text-xs font-bold text-slate-400 select-none">IR</span>
                                <input type="text" wire:model="shabaNumber" placeholder="120170000000..." maxlength="26"
                                       class="flex-1 h-11 px-3 bg-transparent text-xs text-white outline-none text-left">
                            </div>
                            @error('shabaNumber') <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Searchable Modern Province & City Selectors (Alpine Custom Dropdowns) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        
                        {{-- Searchable Province Dropdown --}}
                        <div x-data="{
                                open: false,
                                search: '',
                                get provinces() {
                                    const list = Object.keys(allData || {});
                                    if (!this.search.trim()) return list;
                                    return list.filter(p => p.includes(this.search.trim()));
                                },
                                select(prov) {
                                    $wire.set('province', prov);
                                    this.open = false;
                                    this.search = '';
                                }
                             }"
                             @click.outside="open = false"
                             class="relative">
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">استان فعالیت <span class="text-rose-400">*</span></label>
                            
                            {{-- Dropdown Toggle Button --}}
                            <button type="button" @click="open = !open"
                                    class="w-full h-11 px-3.5 rounded-xl bg-slate-950 border border-slate-700/90 text-xs text-white flex items-center justify-between hover:border-indigo-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                                <span x-text="selectedProvince || 'انتخاب استان...'" class="truncate font-sans font-medium"></span>
                                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-indigo-400' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            {{-- Search Popover --}}
                            <div x-show="open" x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                                 class="absolute z-50 mt-1.5 w-full rounded-2xl bg-slate-900 border border-slate-700 shadow-2xl shadow-black/80 overflow-hidden">
                                
                                {{-- Search Input inside Dropdown --}}
                                <div class="p-2 border-b border-slate-800 bg-slate-950/70">
                                    <div class="relative">
                                        <input type="text" x-model="search" placeholder="جستجوی استان..."
                                               @keydown.escape="open = false"
                                               class="w-full h-9 pr-8 pl-3 rounded-lg bg-slate-900 border border-slate-800 text-xs text-white placeholder:text-slate-500 outline-none focus:border-indigo-500">
                                        <svg class="w-4 h-4 text-slate-500 absolute right-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                </div>

                                {{-- Options List --}}
                                <div class="max-h-48 overflow-y-auto py-1 custom-scrollbar">
                                    <template x-for="p in provinces" :key="p">
                                        <button type="button" @click="select(p)"
                                                class="w-full px-3.5 py-2 text-right text-xs hover:bg-slate-800 transition flex items-center justify-between"
                                                :class="selectedProvince === p ? 'text-indigo-400 font-bold bg-indigo-950/30' : 'text-slate-200'">
                                            <span x-text="p"></span>
                                            <span x-show="selectedProvince === p" class="text-indigo-400 text-xs">✓</span>
                                        </button>
                                    </template>
                                    <div x-show="provinces.length === 0" class="p-3 text-center text-xs text-slate-500">
                                        استانی یافت نشد.
                                    </div>
                                </div>
                            </div>
                            @error('province') <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Searchable City Dropdown --}}
                        <div x-data="{
                                open: false,
                                search: '',
                                get cities() {
                                    const list = (allData && allData[selectedProvince]) ? allData[selectedProvince] : [];
                                    if (!this.search.trim()) return list;
                                    return list.filter(c => c.includes(this.search.trim()));
                                },
                                select(city) {
                                    $wire.set('city', city);
                                    this.open = false;
                                    this.search = '';
                                }
                             }"
                             @click.outside="open = false"
                             class="relative">
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">شهر فعالیت <span class="text-rose-400">*</span></label>
                            
                            {{-- Dropdown Toggle Button --}}
                            <button type="button" @click="open = !open"
                                    class="w-full h-11 px-3.5 rounded-xl bg-slate-950 border border-slate-700/90 text-xs text-white flex items-center justify-between hover:border-indigo-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                                <span x-text="selectedCity || 'انتخاب شهر...'" class="truncate font-sans font-medium"></span>
                                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-indigo-400' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            {{-- Search Popover --}}
                            <div x-show="open" x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                                 class="absolute z-50 mt-1.5 w-full rounded-2xl bg-slate-900 border border-slate-700 shadow-2xl shadow-black/80 overflow-hidden">
                                
                                {{-- Search Input inside Dropdown --}}
                                <div class="p-2 border-b border-slate-800 bg-slate-950/70">
                                    <div class="relative">
                                        <input type="text" x-model="search" placeholder="جستجوی شهر..."
                                               @keydown.escape="open = false"
                                               class="w-full h-9 pr-8 pl-3 rounded-lg bg-slate-900 border border-slate-800 text-xs text-white placeholder:text-slate-500 outline-none focus:border-indigo-500">
                                        <svg class="w-4 h-4 text-slate-500 absolute right-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                </div>

                                {{-- Options List --}}
                                <div class="max-h-48 overflow-y-auto py-1 custom-scrollbar">
                                    <template x-for="c in cities" :key="c">
                                        <button type="button" @click="select(c)"
                                                class="w-full px-3.5 py-2 text-right text-xs hover:bg-slate-800 transition flex items-center justify-between"
                                                :class="selectedCity === c ? 'text-indigo-400 font-bold bg-indigo-950/30' : 'text-slate-200'">
                                            <span x-text="c"></span>
                                            <span x-show="selectedCity === c" class="text-indigo-400 text-xs">✓</span>
                                        </button>
                                    </template>
                                    <div x-show="cities.length === 0" class="p-3 text-center text-xs text-slate-500">
                                        شهری یافت نشد.
                                    </div>
                                </div>
                            </div>
                            @error('city') <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                    </div>

                    {{-- Address --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">آدرس کارگاه یا محدوده استقرار (اختیاری)</label>
                        <input type="text" wire:model="address" placeholder="شهر، خیابان اصلی، میدان، کوچه..."
                               class="w-full h-11 px-3.5 rounded-xl bg-slate-950 border border-slate-700/90 text-xs text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                    </div>

                    {{-- Action Button --}}
                    <div class="pt-2">
                        <button type="button" wire:click="goToStep4" wire:loading.attr="disabled"
                                class="w-full h-12 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-75 disabled:cursor-wait text-white font-bold text-sm rounded-2xl shadow-lg shadow-indigo-600/30 transition active:scale-98 flex items-center justify-center">
                            <div wire:loading.remove wire:target="goToStep4" class="flex items-center justify-center gap-2">
                                <span>مرحله بعد: تخصص‌ها و بارگذاری مدارک</span>
                                <span class="text-base leading-none">←</span>
                            </div>
                            <div wire:loading.flex wire:target="goToStep4" class="items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>در حال بررسی اطلاعات...</span>
                            </div>
                        </button>
                    </div>
                </div>
                @endif

                {{-- ============================================================= --}}
                {{-- STEP 4: REGISTRATION - SKILLS & DOCUMENTS UPLOAD              --}}
                {{-- ============================================================= --}}
                @if($step === 4)
                <div class="space-y-4" x-transition.opacity.duration.250ms>

                    {{-- Technical Categories & Skills (Interactive Chip Cards) --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold text-slate-300">
                                تخصص‌ها و حوزه‌های کاری شما <span class="text-rose-400">*</span>
                            </label>
                            <span class="text-[11px] text-slate-400">حداقل یک مورد را انتخاب کنید</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5">
                            @foreach($categories as $cat)
                                @php
                                    $isSelected = in_array($cat->id, $selectedCategories);
                                @endphp
                                <label class="relative flex items-center gap-2.5 p-3 rounded-2xl cursor-pointer border transition-all {{ $isSelected ? 'bg-indigo-950/40 border-indigo-500/60 shadow-sm shadow-indigo-600/20 text-white' : 'bg-slate-950/70 border-slate-800 text-slate-300 hover:border-slate-700 hover:bg-slate-950' }}">
                                    <input type="checkbox" wire:model.live="selectedCategories" value="{{ $cat->id }}" class="sr-only">
                                    
                                    {{-- Custom Check Indicator --}}
                                    <div class="w-5 h-5 rounded-lg flex items-center justify-center border shrink-0 transition-colors {{ $isSelected ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-slate-700 bg-slate-900 text-transparent' }}">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>

                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="text-base shrink-0">{{ $cat->icon }}</span>
                                        <span class="text-xs font-bold truncate">{{ $cat->name }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('selectedCategories') <span class="text-[11px] text-rose-400 mt-1.5 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- National Card Image Upload Component with Live Upload Loader --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">
                            تصویر کارت ملی یا مدرک فنی (اختیاری)
                        </label>
                        <div class="relative w-full rounded-2xl overflow-hidden">
                            
                            {{-- Live Upload Spinner Overlay during file transfer --}}
                            <div wire:loading.flex wire:target="nationalCardImage"
                                 class="absolute inset-0 z-30 bg-slate-950/95 backdrop-blur-sm flex-col items-center justify-center gap-2.5 border-2 border-indigo-500 rounded-2xl">
                                <svg class="animate-spin h-7 w-7 text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span class="text-xs font-bold text-indigo-300 font-sans tracking-wide">در حال بارگذاری و پردازش تصویر...</span>
                            </div>

                            <label for="tech_national_card"
                                   class="relative flex flex-col items-center justify-center w-full min-h-[92px] py-3.5 px-4 transition-all bg-slate-950/80 border-2 border-slate-700/80 border-dashed rounded-2xl cursor-pointer hover:border-indigo-500 hover:bg-slate-950 group">
                                
                                @if(!$nationalCardImage)
                                <div class="flex flex-col items-center space-y-1 text-center">
                                    <div class="p-2 bg-indigo-900/30 rounded-xl text-indigo-400 group-hover:scale-105 transition-transform">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                    </div>
                                    <span class="font-bold text-xs text-indigo-400">برای بارگذاری تصویر کلیک فرمایید</span>
                                    <span class="text-[10px] text-slate-500">JPG یا PNG حداکثر ۵ مگابایت</span>
                                </div>
                                @else
                                <div class="flex items-center gap-3 w-full">
                                    <div class="w-12 h-12 rounded-xl overflow-hidden border border-slate-700 shrink-0 bg-slate-900">
                                        @if(method_exists($nationalCardImage, 'temporaryUrl'))
                                            <img src="{{ $nationalCardImage->temporaryUrl() }}" class="object-cover w-full h-full" alt="Preview">
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1 text-right">
                                        <div class="text-xs font-bold text-white truncate">{{ $nationalCardImage->getClientOriginalName() }}</div>
                                        <div class="text-[10px] text-emerald-400 font-medium mt-0.5">تصویر با موفقیت بارگذاری شد</div>
                                    </div>
                                </div>
                                @endif
                            </label>

                            <input id="tech_national_card" type="file" wire:model="nationalCardImage" class="hidden" accept="image/*">

                            @if($nationalCardImage)
                            <button type="button" wire:click="$set('nationalCardImage', null)"
                                    class="absolute top-2 left-2 p-1.5 bg-rose-900/40 text-rose-300 hover:bg-rose-900/70 rounded-full transition-colors z-20 shadow-sm border border-rose-800" title="حذف فایل">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            @endif
                        </div>
                        @error('nationalCardImage') <span class="text-[11px] text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Actions: Previous Step & Submit Button --}}
                    <div class="pt-2 flex items-center gap-3">
                        {{-- Back Button with live loader --}}
                        <button type="button" wire:click="goToStep3" wire:loading.attr="disabled"
                                class="h-12 px-5 rounded-2xl bg-slate-800 hover:bg-slate-700 disabled:opacity-75 disabled:cursor-wait text-slate-300 hover:text-white font-bold text-xs transition active:scale-95 flex items-center justify-center shrink-0">
                            <div wire:loading.remove wire:target="goToStep3" class="flex items-center gap-1.5">
                                <span>بازگشت</span>
                            </div>
                            <div wire:loading.flex wire:target="goToStep3" class="items-center gap-1.5">
                                <svg class="animate-spin h-3.5 w-3.5 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>صبر کنید...</span>
                            </div>
                        </button>

                        {{-- Submit Button with live loader --}}
                        <button type="button" wire:click="submitRegistration" wire:loading.attr="disabled"
                                class="flex-1 h-12 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-75 disabled:cursor-wait text-white font-bold text-sm rounded-2xl shadow-xl shadow-indigo-600/30 transition active:scale-98 flex items-center justify-center">
                            <div wire:loading.remove wire:target="submitRegistration" class="flex items-center justify-center gap-2">
                                <span>تکمیل ثبت‌نام و ورود به ناوگان</span>
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
            <div class="px-6 py-3 border-t border-slate-800/60 bg-slate-950/60 text-center">
                <span class="text-[11px] text-slate-500 font-sans">
                    ناوگان تخصصی خدمات فنی و اعزام فوری تکنسین
                </span>
            </div>

        </div>
    </div>
</div>
