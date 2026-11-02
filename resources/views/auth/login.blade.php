<x-guest-layout>
    @php
        $smsEnabled = function_exists('get_setting') && get_setting('auth_sms_enabled', '0') === '1';
        $passwordEnabled = function_exists('get_setting') && get_setting('auth_password_enabled', '1') === '1';
        $priorityMethod = function_exists('get_setting') ? get_setting('auth_priority_method', 'password') : 'password';
        $otpTtl = (int) (function_exists('get_setting') ? get_setting('auth_otp_ttl', 3) : 3);
        $otpResendInterval = (int) (function_exists('get_setting') ? get_setting('auth_otp_resend_interval', 60) : 60);
    @endphp

    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 p-6 dark:bg-gray-950 font-sans">

        {{-- لوگو و عنوان --}}
        <div class="mb-8 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/20">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h2 class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">ورود به حساب کاربری</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">برای دسترسی به پنل مدیریت و داشبورد خود وارد شوید</p>
        </div>

        {{-- کارت فرم احراز هویت هوشمند دو مرحله‌ای --}}
        <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl shadow-gray-200/50 dark:bg-gray-900 dark:shadow-none border border-gray-100 dark:border-gray-800"
             x-data="loginFlow({
                 checkUrl: '{{ route('login.check-identity') }}',
                 sendOtpUrl: '{{ route('login.send-otp') }}',
                 verifyOtpUrl: '{{ route('login.verify-otp') }}',
                 loginUrl: '{{ route('login') }}',
                 csrf: '{{ csrf_token() }}',
                 oldIdentifier: '{{ old('email', '') }}',
                 defaultPriority: '{{ $priorityMethod }}',
                 smsGloballyEnabled: {{ $smsEnabled ? 'true' : 'false' }},
                 passwordGloballyEnabled: {{ $passwordEnabled ? 'true' : 'false' }},
                 defaultResendInterval: {{ $otpResendInterval }}
             })"
             x-cloak>

            {{-- پیام وضعیت سشن --}}
            @if (session('status'))
                <div class="mb-6 rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30 flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- خطاهای سیستمی ولیدیشن عمومی --}}
            <x-validation-errors class="mb-6 rounded-xl bg-red-50 p-4 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400 border border-red-100 dark:border-red-900/30" />

            {{-- پیام هشدار داینامیک رابط کاربری --}}
            <div x-show="alertMessage"
                 x-transition
                 :class="{
                     'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400 border-red-100 dark:border-red-900/30': alertType === 'error',
                     'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30': alertType === 'success',
                     'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400 border-indigo-100 dark:border-indigo-900/30': alertType === 'info'
                 }"
                 class="mb-6 rounded-xl p-4 text-sm border flex items-start gap-2.5">
                <svg x-show="alertType === 'error'" class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg x-show="alertType === 'success'" class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg x-show="alertType === 'info'" class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span x-text="alertMessage" class="leading-relaxed"></span>
            </div>

            {{-- ======================================================== --}}
            {{-- مرحله اول: ورود اطلاعات کاربری (ایمیل یا شماره موبایل)   --}}
            {{-- ======================================================== --}}
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2">
                <form @submit.prevent="submitIdentity" class="space-y-6">
                    <div>
                        <label for="identifier" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            ایمیل یا شماره موبایل
                        </label>
                        <div class="relative">
                            <input id="identifier"
                                   type="text"
                                   x-model="identifier"
                                   required
                                   autofocus
                                   autocomplete="username"
                                   :disabled="loading"
                                   class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 pl-10 pr-4 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 dir-ltr text-left font-sans"
                                   placeholder="user@example.com یا 0912..." />
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            شماره موبایل یا پست الکترونیکی متصل به حساب خود را وارد نمایید.
                        </p>
                    </div>

                    <button type="submit"
                            :disabled="loading || !identifier.trim()"
                            class="group relative flex w-full justify-center items-center rounded-xl bg-indigo-600 py-3 px-4 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-500 hover:shadow-indigo-600/40 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:ring-offset-2 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading" class="flex items-center gap-2">
                            <span>ادامه</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </span>
                        <span x-show="loading" class="flex items-center gap-2" style="display: none;">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>در حال بررسی...</span>
                        </span>
                    </button>
                </form>
            </div>

            {{-- ======================================================== --}}
            {{-- مرحله دوم: تایید هویت (ورود پیامکی OTP یا رمز عبور)       --}}
            {{-- ======================================================== --}}
            <div x-show="step === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" style="display: none;">

                {{-- کادر مشخصات کاربر و دکمه ویرایش هویت --}}
                <div class="mb-6 flex items-center justify-between rounded-xl bg-gray-50/80 dark:bg-gray-800/60 p-3 border border-gray-100 dark:border-gray-700/60">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-bold text-sm">
                            <span x-text="userName ? userName.charAt(0) : 'U'"></span>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-900 dark:text-white" x-text="userName || 'کاربر سیستم'"></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 dir-ltr text-right font-sans" x-text="userIdentifier"></div>
                        </div>
                    </div>
                    <button type="button"
                            @click="goBackToStep1"
                            class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 px-2.5 py-1.5 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                        تغییر حساب
                    </button>
                </div>

                {{-- حالت الف: فرم ورود پیامکی (OTP) --}}
                <div x-show="authMode === 'otp'" x-transition>
                    <form @submit.prevent="submitVerifyOtp" class="space-y-6">
                        <div>
                            <label for="otp_code" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                کد تایید پیامکی (OTP)
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                کد ارسال‌شده به شماره
                                <span class="font-bold text-gray-800 dark:text-gray-200 dir-ltr inline-block font-sans" x-text="userMaskedPhone || userIdentifier"></span>
                                را وارد کنید:
                            </p>

                            <div class="relative">
                                <input id="otp_code"
                                       type="text"
                                       x-model="otpCode"
                                       required
                                       autofocus
                                       maxlength="8"
                                       autocomplete="one-time-code"
                                       :disabled="loading"
                                       class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 px-4 text-center text-lg font-bold tracking-widest text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 dir-ltr font-sans"
                                       placeholder="• • • • •" />
                            </div>
                        </div>

                        {{-- تایمر معکوس و ارسال مجدد کد --}}
                        <div class="flex items-center justify-between text-xs">
                            <div class="text-gray-500 dark:text-gray-400">
                                <span x-show="resendTimer > 0">
                                    ارسال مجدد کد پس از:
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400 font-sans" x-text="formatTime(resendTimer)"></span>
                                </span>
                                <span x-show="resendTimer <= 0" class="text-emerald-600 dark:text-emerald-400 font-medium">
                                    امکان ارسال مجدد پیامک فعال است.
                                </span>
                            </div>

                            <button type="button"
                                    @click="resendOtp"
                                    :disabled="loading || resendTimer > 0"
                                    class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                                ارسال مجدد کد
                            </button>
                        </div>

                        {{-- چک باکس مرا به خاطر بسپار --}}
                        <div class="flex items-center justify-between">
                            <label for="remember_me_otp" class="flex items-center cursor-pointer group">
                                <input id="remember_me_otp"
                                       type="checkbox"
                                       x-model="rememberMe"
                                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 cursor-pointer" />
                                <span class="mr-2 text-sm text-gray-600 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-gray-300 select-none">
                                    مرا به خاطر بسپار
                                </span>
                            </label>
                        </div>

                        {{-- دکمه تایید و ورود --}}
                        <button type="submit"
                                :disabled="loading || !otpCode.trim()"
                                class="group relative flex w-full justify-center items-center rounded-xl bg-indigo-600 py-3 px-4 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-500 hover:shadow-indigo-600/40 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:ring-offset-2 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!loading" class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-indigo-200 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" />
                                </svg>
                                <span>تایید و ورود به سیستم</span>
                            </span>
                            <span x-show="loading" class="flex items-center gap-2" style="display: none;">
                                <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>در حال اعتبارسنجی...</span>
                            </span>
                        </button>
                    </form>

                    {{-- سوییچ به ورود با رمز عبور --}}
                    <div x-show="canPassword" class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800 text-center">
                        <button type="button"
                                @click="switchToPassword"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            <span>ورود با رمز عبور</span>
                        </button>
                    </div>
                </div>

                {{-- حالت ب: فرم ورود با رمز عبور --}}
                <div x-show="authMode === 'password'" x-transition>
                    <form method="POST" action="{{ route('login') }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="email" :value="userIdentifier">

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password" class="block text-sm font-bold text-gray-700 dark:text-gray-300">رمز عبور</label>
                            </div>
                            <div class="relative" x-data="{ showPass: false }">
                                <input id="password"
                                       :type="showPass ? 'text' : 'password'"
                                       name="password"
                                       required
                                       autocomplete="current-password"
                                       class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 pl-10 pr-10 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800 dir-ltr font-sans text-left"
                                       placeholder="••••••••" />
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <button type="button"
                                        @click="showPass = !showPass"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg x-show="!showPass" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="showPass" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- یادآوری و فراموشی رمز --}}
                        <div class="flex items-center justify-between">
                            <label for="remember_me_pass" class="flex items-center cursor-pointer group">
                                <input id="remember_me_pass"
                                       type="checkbox"
                                       name="remember"
                                       x-model="rememberMe"
                                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 cursor-pointer" />
                                <span class="mr-2 text-sm text-gray-600 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-gray-300 select-none">
                                    مرا به خاطر بسپار
                                </span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors" href="{{ route('password.request') }}">
                                    فراموشی رمز عبور؟
                                </a>
                            @endif
                        </div>

                        {{-- دکمه ورود با رمز --}}
                        <button type="submit"
                                class="group relative flex w-full justify-center items-center rounded-xl bg-indigo-600 py-3 px-4 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-500 hover:shadow-indigo-600/40 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:ring-offset-2 transition-all active:scale-[0.98]">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-indigo-200 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" />
                                </svg>
                            </span>
                            <span>ورود به سیستم</span>
                        </button>
                    </form>

                    {{-- سوییچ به ورود پیامکی --}}
                    <div x-show="canSms" class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800 text-center">
                        <button type="button"
                                @click="switchToOtp"
                                :disabled="loading"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors inline-flex items-center gap-1.5 disabled:opacity-50">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span>ورود با پیامک (کد یکبار مصرف OTP)</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- فوتر لاگین --}}
        <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400 font-sans">
            &copy; {{ date('Y') }} تمامی حقوق محفوظ است.
        </p>
    </div>

    {{-- منطق جاوااسکریپت ورود دو مرحله‌ای --}}
    <script>
        function loginFlow(config) {
            return {
                step: 1, // 1: وارد کردن هویت | 2: احراز هویت (رمز یا OTP)
                authMode: 'password', // 'otp' | 'password'
                identifier: config.oldIdentifier || '',
                userIdentifier: '',
                userName: '',
                userMaskedPhone: '',
                canSms: false,
                canPassword: true,
                otpCode: '',
                rememberMe: true,
                loading: false,
                alertMessage: '',
                alertType: 'info',
                resendTimer: 0,
                timerInterval: null,

                setAlert(type, message) {
                    this.alertType = type;
                    this.alertMessage = message;
                },

                clearAlert() {
                    this.alertMessage = '';
                },

                toEnglishDigits(str) {
                    if (!str) return '';
                    const persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                    const arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
                    return str.toString()
                        .replace(/[۰-۹]/g, d => persian.indexOf(d))
                        .replace(/[٠-٩]/g, d => arabic.indexOf(d));
                },

                formatTime(seconds) {
                    const m = Math.floor(seconds / 60);
                    const s = seconds % 60;
                    return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                },

                startResendTimer(seconds) {
                    if (this.timerInterval) {
                        clearInterval(this.timerInterval);
                    }
                    this.resendTimer = seconds;
                    this.timerInterval = setInterval(() => {
                        if (this.resendTimer > 0) {
                            this.resendTimer--;
                        } else {
                            clearInterval(this.timerInterval);
                        }
                    }, 1000);
                },

                async submitIdentity() {
                    this.clearAlert();
                    const cleanIdentifier = this.toEnglishDigits(this.identifier).trim();
                    if (!cleanIdentifier) {
                        this.setAlert('error', 'لطفاً ایمیل یا شماره موبایل خود را وارد نمایید.');
                        return;
                    }

                    this.loading = true;
                    try {
                        const res = await fetch(config.checkUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': config.csrf,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ identifier: cleanIdentifier })
                        });

                        const data = await res.json().catch(() => ({}));

                        if (!res.ok || !data.success) {
                            this.setAlert('error', data.message || 'حساب کاربری با این مشخصات یافت نشد.');
                            return;
                        }

                        // دریافت مشخصات کاربر و نحوه ورود
                        this.userIdentifier = cleanIdentifier;
                        this.userName = data.user?.name || '';
                        this.userMaskedPhone = data.user?.masked_phone || '';
                        this.canSms = !!data.can_sms;
                        this.canPassword = !!data.can_password;
                        this.authMode = data.step || 'password';

                        // رفتن به مرحله ۲
                        this.step = 2;

                        if (this.authMode === 'otp') {
                            if (data.otp_sent) {
                                this.setAlert('success', data.otp_message || 'کد تایید پیامکی ارسال شد.');
                                this.startResendTimer(data.resend_in || config.defaultResendInterval || 60);
                            } else if (data.otp_message) {
                                this.setAlert('error', data.otp_message);
                            }
                        }
                    } catch (e) {
                        this.setAlert('error', 'خطا در برقراری ارتباط با سرور. لطفاً مجدداً تلاش کنید.');
                    } finally {
                        this.loading = false;
                    }
                },

                async switchToOtp() {
                    this.clearAlert();
                    this.authMode = 'otp';
                    await this.sendOtpRequest();
                },

                switchToPassword() {
                    this.clearAlert();
                    this.authMode = 'password';
                },

                async resendOtp() {
                    if (this.resendTimer > 0) return;
                    await this.sendOtpRequest();
                },

                async sendOtpRequest() {
                    this.loading = true;
                    try {
                        const res = await fetch(config.sendOtpUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': config.csrf,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ identifier: this.userIdentifier })
                        });

                        const data = await res.json().catch(() => ({}));

                        if (!res.ok || !data.success) {
                            if (data.resend_in) {
                                this.startResendTimer(data.resend_in);
                            }
                            this.setAlert('error', data.message || 'خطا در ارسال پیامک.');
                            return;
                        }

                        this.setAlert('success', data.message || 'کد تایید ارسال شد.');
                        this.startResendTimer(data.resend_in || config.defaultResendInterval || 60);
                    } catch (e) {
                        this.setAlert('error', 'خطا در ارتباط با سرور هنگام ارسال کد.');
                    } finally {
                        this.loading = false;
                    }
                },

                async submitVerifyOtp() {
                    this.clearAlert();
                    const cleanCode = this.toEnglishDigits(this.otpCode).trim();
                    if (!cleanCode) {
                        this.setAlert('error', 'لطفاً کد تایید پیامکی را وارد کنید.');
                        return;
                    }

                    this.loading = true;
                    try {
                        const res = await fetch(config.verifyOtpUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': config.csrf,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                identifier: this.userIdentifier,
                                code: cleanCode,
                                remember: this.rememberMe
                            })
                        });

                        const data = await res.json().catch(() => ({}));

                        if (!res.ok || !data.success) {
                            this.setAlert('error', data.message || 'کد تایید وارد شده نادرست یا منقضی است.');
                            return;
                        }

                        this.setAlert('success', 'ورود موفقیت‌آمیز بود. در حال انتقال به پنل...');
                        window.location.href = data.redirect_url || '/user/dashboard';
                    } catch (e) {
                        this.setAlert('error', 'خطای شبکه در تایید کد. لطفاً دوباره تلاش کنید.');
                    } finally {
                        this.loading = false;
                    }
                },

                goBackToStep1() {
                    this.clearAlert();
                    this.step = 1;
                    this.otpCode = '';
                    if (this.timerInterval) {
                        clearInterval(this.timerInterval);
                    }
                }
            };
        }
    </script>
</x-guest-layout>
