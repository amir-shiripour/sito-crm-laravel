@extends('clients::layouts.client')

@php
    use Illuminate\Support\Facades\Route;
    use Modules\Clients\Entities\ClientSetting;

    $title = 'ورود به پنل مشتریان';

    $mode         = ClientSetting::getValue('auth.mode', 'password'); // password | otp | both
    $defaultLogin = ClientSetting::getValue('auth.default', 'password'); // password | otp

    if (!in_array($mode, ['password','otp','both'], true)) $mode = 'password';
    if (!in_array($defaultLogin, ['password','otp'], true)) $defaultLogin = 'password';

    // اگر mode=otp باشد، پیش‌فرض هم otp
    if ($mode === 'otp') $defaultLogin = 'otp';
    if ($mode === 'password') $defaultLogin = 'password';

    // اگر OTP ارسال شده باشد (از Controller)
    $otpSent      = (bool) session('otp_sent', false);
    $otpUsername  = (string) session('otp_username', old('username', ''));
    $otpResendIn  = (int) session('otp_resend_in', 60);

    // تب فعال: اگر کد ارسال شده → otp، وگرنه طبق default
    $initialTab = $otpSent ? 'otp' : $defaultLogin;

    // مسیرهای OTP اگر وجود داشته باشند
    $otpSendUrl   = Route::has('client.otp.send') ? route('client.otp.send') : null;
    $otpVerifyUrl = Route::has('client.otp.verify') ? route('client.otp.verify') : null;
@endphp

@section('content')
    <div class="flex min-h-[calc(100vh-140px)] flex-col items-center justify-center py-10 px-4">

        <div class="w-full max-w-md animate-in fade-in slide-in-from-bottom-4 duration-700">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl shadow-gray-200/50 dark:shadow-none overflow-hidden">

                {{-- Header --}}
                <div class="bg-gray-50/50 dark:bg-gray-900/30 px-6 py-6 border-b border-gray-100 dark:border-gray-700/60 text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                        ورود به پنل کاربری
                    </h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs mx-auto leading-relaxed">
                        جهت دسترسی به داشبورد و پیگیری درخواست‌ها وارد شوید.
                    </p>
                </div>

                {{-- Alpine / JS Wrapper (برای both و otp) --}}
                @if($mode === 'both' || $mode === 'otp')
                    <style>[x-cloak]{display:none!important}</style>

                    <div class="{{ $mode === 'both' ? 'px-6 pt-5' : 'px-6 py-8' }}"
                         x-data="clientPortalLogin({
                            mode: @js($mode),
                            initialTab: @js($initialTab),
                            otpSent: @js($otpSent),
                            otpUsername: @js($otpUsername),
                            otpResendIn: @js($otpResendIn),
                            otpSendUrl: @js($otpSendUrl),
                            otpVerifyUrl: @js($otpVerifyUrl),
                            csrf: @js(csrf_token()),
                            dashboardUrl: @js(route('client.dashboard')),
                         })"
                         x-init="init()"
                    >
                        {{-- Tabs (فقط اگر both) --}}
                        @if($mode === 'both')
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <button type="button"
                                        @click="tab='password'; clearAlert()"
                                        class="px-3 py-2 rounded-xl border transition"
                                        :class="tab==='password'
                                            ? 'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-200'
                                            : 'border-gray-200 text-gray-600 dark:border-gray-700 dark:text-gray-300'">
                                    ورود با رمز
                                </button>

                                <button type="button"
                                        @click="tab='otp'; clearAlert()"
                                        class="px-3 py-2 rounded-xl border transition"
                                        :class="tab==='otp'
                                            ? 'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-200'
                                            : 'border-gray-200 text-gray-600 dark:border-gray-700 dark:text-gray-300'">
                                    ورود با کد پیامکی
                                </button>
                            </div>
                        @endif

                        {{-- Alert مشترک (برای OTP) --}}
                        <template x-if="alert.message">
                            <div class="mt-4 rounded-xl p-3 text-xs font-medium border flex items-center gap-2"
                                 :class="alert.type==='error'
                                    ? 'bg-red-50 text-red-600 border-red-100 dark:bg-red-900/10 dark:text-red-400 dark:border-red-900/20'
                                    : 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/10 dark:text-emerald-300 dark:border-emerald-900/20'">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span x-text="alert.message"></span>
                            </div>
                        </template>

                        {{-- ---------- PASSWORD FORM (داخل both) ---------- --}}
                        @if($mode === 'both')
                            <div class="mt-4" x-show="tab==='password'" x-cloak>
                                <form method="POST" action="{{ route('client.login.submit') }}" class="px-0 py-6 space-y-5">
                                    @csrf

                                    {{-- خطای مربوط به password --}}
                                    @if($errors->has('username') || $errors->has('password'))
                                        <div class="rounded-xl bg-red-50 p-3 text-xs font-medium text-red-600 dark:bg-red-900/10 dark:text-red-400 border border-red-100 dark:border-red-900/20 flex items-center gap-2">
                                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>{{ $errors->first('username') ?: $errors->first('password') }}</span>
                                        </div>
                                    @endif

                                    {{-- username --}}
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نام کاربری</label>
                                        <div class="relative">
                                            <input type="text" name="username" value="{{ old('username') }}"
                                                   autocomplete="username" required
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
                                    </div>

                                    {{-- password --}}
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">رمز عبور</label>
                                        <div class="relative">
                                            <input type="password" name="password" autocomplete="current-password" required
                                                   class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 pl-10 pr-4 text-sm text-gray-900
                                                          placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20
                                                          transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900 dir-ltr"
                                                   placeholder="••••••••">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <label class="flex items-center cursor-pointer group">
                                            <div class="relative flex items-center">
                                                <input type="checkbox" name="remember"
                                                       class="peer h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 cursor-pointer" />
                                            </div>
                                            <span class="mr-2 text-xs font-medium text-gray-600 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-gray-300 select-none transition-colors">مرا به خاطر بسپار</span>
                                        </label>
                                    </div>

                                    <button type="submit"
                                            class="group relative flex w-full justify-center rounded-xl bg-indigo-600 py-3 px-4 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 hover:shadow-indigo-600/40 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:ring-offset-2 transition-all active:scale-[0.98]">
                                        ورود با رمز
                                        <svg class="mr-2 h-4 w-4 opacity-70 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" />
                                        </svg>
                                    </button>

                                    <div class="pt-4 mt-2 border-t border-dashed border-gray-200 dark:border-gray-700 text-center">
                                        <p class="text-[10px] text-gray-400 dark:text-gray-500">
                                            در صورت فراموشی رمز عبور، با پشتیبانی تماس بگیرید.
                                        </p>
                                    </div>
                                </form>
                            </div>
                        @endif

                        {{-- ---------- OTP UI (برای otp-only و both) ---------- --}}
                        <div class="{{ $mode === 'both' ? 'mt-4' : '' }}"
                             x-show="tab==='otp'"
                             x-cloak
                        >
                            {{-- اگر route ها موجود نیستند --}}
                            @if(! $otpSendUrl || ! $otpVerifyUrl)
                                <div class="rounded-xl bg-amber-50 p-3 text-xs font-medium text-amber-800 dark:bg-amber-900/10 dark:text-amber-300 border border-amber-100 dark:border-amber-900/20 flex items-center gap-2">
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
                                    </svg>
                                    <span>
                                        مسیرهای OTP (client.otp.send / client.otp.verify) تعریف نشده‌اند؛
                                        برای فعال شدن OTP بدون رفرش، این route ها باید اضافه شوند.
                                    </span>
                                </div>
                            @endif

                            {{-- مرحله ۱: ارسال کد --}}
                            <div x-show="!otp.sent" class="py-6 space-y-5">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نام کاربری</label>
                                    <div class="relative">
                                        <input type="text"
                                               x-model="otp.username"
                                               autocomplete="username"
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
                                    <p class="mt-2 text-[10px] text-gray-400 dark:text-gray-500">
                                        کد به شماره موبایل ثبت‌شده ارسال می‌شود.
                                    </p>
                                </div>

                                <button type="button"
                                        @click="sendOtp()"
                                        :disabled="otp.loading || !otp.username || !otpSendUrl"
                                        class="group relative flex w-full justify-center rounded-xl bg-indigo-600 py-3 px-4 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 hover:shadow-indigo-600/40 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:ring-offset-2 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span x-show="!otp.loading">ارسال کد پیامکی</span>
                                    <span x-show="otp.loading">در حال ارسال...</span>
                                    <svg class="mr-2 h-4 w-4 opacity-70 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" />
                                    </svg>
                                </button>

                                <div class="pt-4 mt-2 border-t border-dashed border-gray-200 dark:border-gray-700 text-center">
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500">
                                        در صورت مشکل در دریافت پیامک، با پشتیبانی تماس بگیرید.
                                    </p>
                                </div>
                            </div>

                            {{-- مرحله ۲: تایید کد --}}
                            <div x-show="otp.sent" class="py-6 space-y-5">
                                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/30 px-4 py-3 text-xs text-gray-600 dark:text-gray-300">
                                    نام کاربری:
                                    <span class="font-mono dir-ltr" x-text="otp.username"></span>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">کد پیامکی</label>
                                    <div class="relative">
                                        <input type="text"
                                               x-model="otp.code"
                                               inputmode="numeric"
                                               autocomplete="one-time-code"
                                               class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 pl-10 pr-4 text-sm text-gray-900
                                                      placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20
                                                      transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900 dir-ltr"
                                               placeholder="مثلاً 12345">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <button type="button"
                                        @click="verifyOtp()"
                                        :disabled="otp.loading || !otp.code || !otpVerifyUrl"
                                        class="group relative flex w-full justify-center rounded-xl bg-indigo-600 py-3 px-4 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 hover:shadow-indigo-600/40 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:ring-offset-2 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span x-show="!otp.loading">تایید و ورود</span>
                                    <span x-show="otp.loading">در حال بررسی...</span>
                                    <svg class="mr-2 h-4 w-4 opacity-70 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" />
                                    </svg>
                                </button>

                                {{-- Resend با تایمر --}}
                                <button type="button"
                                        @click="resendOtp()"
                                        :disabled="otp.loading || otp.resendRemaining > 0 || !otpSendUrl"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 py-2 text-xs text-gray-700 dark:text-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                    <template x-if="otp.resendRemaining <= 0">
                                        <span>ارسال مجدد کد</span>
                                    </template>
                                    <template x-if="otp.resendRemaining > 0">
                                        <span>ارسال مجدد تا <span x-text="otp.resendRemaining"></span> ثانیه دیگر</span>
                                    </template>
                                </button>

                                <button type="button"
                                        @click="resetOtp()"
                                        class="w-full text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition">
                                    تغییر نام کاربری
                                </button>

                                <div class="pt-4 mt-2 border-t border-dashed border-gray-200 dark:border-gray-700 text-center">
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500">
                                        در صورت مشکل در دریافت پیامک، با پشتیبانی تماس بگیرید.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- اگر Alpine لود نشد، x-cloak را بردار تا چیزی مخفی نماند --}}
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            if (!window.Alpine) {
                                document.querySelectorAll('[x-cloak]').forEach(el => el.removeAttribute('x-cloak'));
                            }
                        });
                    </script>

                    <script>
                        function clientPortalLogin(cfg) {
                            return {
                                mode: cfg.mode,
                                tab: (cfg.mode === 'both') ? (cfg.initialTab || 'password') : 'otp',

                                otpSendUrl: cfg.otpSendUrl,
                                otpVerifyUrl: cfg.otpVerifyUrl,
                                csrf: cfg.csrf,
                                dashboardUrl: cfg.dashboardUrl,

                                alert: { type: null, message: '' },

                                otp: {
                                    username: cfg.otpUsername || '',
                                    code: '',
                                    sent: !!cfg.otpSent,
                                    loading: false,
                                    resendRemaining: 0,
                                    _timer: null,
                                },

                                init() {
                                    // در otp-only همیشه otp
                                    if (this.mode === 'otp') this.tab = 'otp';
                                    if (this.mode === 'password') this.tab = 'password';

                                    // اگر از قبل otp_sent آمده باشد، تایمر را فعال کن
                                    if (this.otp.sent) {
                                        const s = parseInt(cfg.otpResendIn || 0, 10);
                                        if (s > 0) this.startResendTimer(s);
                                    }
                                },

                                clearAlert() {
                                    this.alert.type = null;
                                    this.alert.message = '';
                                },

                                setAlert(type, message) {
                                    this.alert.type = type;
                                    this.alert.message = message || '';
                                },

                                startResendTimer(seconds) {
                                    const s = parseInt(seconds || 0, 10);
                                    this.otp.resendRemaining = isNaN(s) ? 0 : s;

                                    if (this.otp._timer) clearInterval(this.otp._timer);

                                    if (this.otp.resendRemaining > 0) {
                                        this.otp._timer = setInterval(() => {
                                            this.otp.resendRemaining = Math.max(0, this.otp.resendRemaining - 1);
                                            if (this.otp.resendRemaining <= 0) {
                                                clearInterval(this.otp._timer);
                                                this.otp._timer = null;
                                            }
                                        }, 1000);
                                    }
                                },

                                async sendOtp() {
                                    this.clearAlert();

                                    if (!this.otpSendUrl) {
                                        this.setAlert('error', 'مسیر ارسال OTP تنظیم نشده است.');
                                        return;
                                    }

                                    if (!this.otp.username) {
                                        this.setAlert('error', 'نام کاربری را وارد کنید.');
                                        return;
                                    }

                                    this.otp.loading = true;

                                    try {
                                        const res = await fetch(this.otpSendUrl, {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': this.csrf,
                                                'Accept': 'application/json',
                                            },
                                            body: JSON.stringify({ username: this.otp.username }),
                                        });

                                        const json = await res.json().catch(() => ({}));

                                        if (!res.ok || !json.success) {
                                            // اگر سرور resend_in داد، تایمر را ست کن
                                            if (json.resend_in) this.startResendTimer(json.resend_in);

                                            this.setAlert('error', json.message || 'خطا در ارسال کد');
                                            return;
                                        }

                                        this.otp.sent = true;
                                        this.otp.code = '';
                                        this.setAlert('success', 'کد ارسال شد. لطفاً کد را وارد کنید.');

                                        // اولویت: resend_in از سرور
                                        this.startResendTimer(json.resend_in || cfg.otpResendIn || 60);

                                    } catch (e) {
                                        this.setAlert('error', 'خطای شبکه در ارسال کد');
                                    } finally {
                                        this.otp.loading = false;
                                    }
                                },

                                async resendOtp() {
                                    // همان sendOtp ولی مرحله ۲
                                    return this.sendOtp();
                                },

                                async verifyOtp() {
                                    this.clearAlert();

                                    if (!this.otpVerifyUrl) {
                                        this.setAlert('error', 'مسیر تایید OTP تنظیم نشده است.');
                                        return;
                                    }

                                    if (!this.otp.username) {
                                        this.setAlert('error', 'نام کاربری را وارد کنید.');
                                        return;
                                    }

                                    if (!this.otp.code) {
                                        this.setAlert('error', 'کد پیامکی را وارد کنید.');
                                        return;
                                    }

                                    this.otp.loading = true;

                                    try {
                                        const res = await fetch(this.otpVerifyUrl, {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': this.csrf,
                                                'Accept': 'application/json',
                                            },
                                            body: JSON.stringify({ username: this.otp.username, code: this.otp.code }),
                                        });

                                        const json = await res.json().catch(() => ({}));

                                        if (!res.ok || !json.success) {
                                            this.setAlert('error', json.message || 'کد نامعتبر است یا منقضی شده است.');
                                            return;
                                        }

                                        // ریدایرکت
                                        window.location.href = json.redirect || this.dashboardUrl;

                                    } catch (e) {
                                        this.setAlert('error', 'خطای شبکه در تایید کد');
                                    } finally {
                                        this.otp.loading = false;
                                    }
                                },

                                resetOtp() {
                                    this.clearAlert();
                                    this.otp.sent = false;
                                    this.otp.code = '';

                                    if (this.otp._timer) {
                                        clearInterval(this.otp._timer);
                                        this.otp._timer = null;
                                    }
                                    this.otp.resendRemaining = 0;
                                },
                            };
                        }
                    </script>
                @endif

                {{-- اگر فقط password --}}
                @if($mode === 'password')
                    <form method="POST" action="{{ route('client.login.submit') }}" class="px-6 py-8 space-y-5">
                        @csrf

                        @if($errors->any())
                            <div class="rounded-xl bg-red-50 p-3 text-xs font-medium text-red-600 dark:bg-red-900/10 dark:text-red-400 border border-red-100 dark:border-red-900/20 mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>{{ $errors->first() }}</span>
                            </div>
                        @endif

                        {{-- username --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">نام کاربری</label>
                            <div class="relative">
                                <input type="text" name="username" value="{{ old('username') }}"
                                       autocomplete="username" required autofocus
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
                        </div>

                        {{-- password --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">رمز عبور</label>
                            <div class="relative">
                                <input type="password" name="password" autocomplete="current-password" required
                                       class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 pl-10 pr-4 text-sm text-gray-900
                                              placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20
                                              transition-all dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900 dir-ltr"
                                       placeholder="••••••••">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" name="remember"
                                           class="peer h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 cursor-pointer" />
                                </div>
                                <span class="mr-2 text-xs font-medium text-gray-600 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-gray-300 select-none transition-colors">مرا به خاطر بسپار</span>
                            </label>
                        </div>

                        <button type="submit"
                                class="group relative flex w-full justify-center rounded-xl bg-indigo-600 py-3 px-4 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 hover:shadow-indigo-600/40 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:ring-offset-2 transition-all active:scale-[0.98]">
                            ورود به پنل
                            <svg class="mr-2 h-4 w-4 opacity-70 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" />
                            </svg>
                        </button>

                        <div class="pt-4 mt-2 border-t border-dashed border-gray-200 dark:border-gray-700 text-center">
                            <p class="text-[10px] text-gray-400 dark:text-gray-500">
                                در صورت فراموشی رمز عبور، با پشتیبانی تماس بگیرید.
                            </p>
                        </div>
                    </form>
                @endif

            </div>
        </div>
    </div>
@endsection
