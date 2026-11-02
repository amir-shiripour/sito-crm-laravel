<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Sms\Entities\SmsGatewaySetting;
use Modules\Sms\Entities\SmsMessage;
use Modules\Sms\Entities\SmsOtp;
use Modules\Sms\Services\SmsManager;

class OtpLoginController extends Controller
{
    public const CONTEXT = 'login_user';

    /**
     * Check user identity and determine login flow (SMS OTP vs Password).
     */
    public function checkIdentity(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => ['required', 'string'],
        ]);

        $identifier = trim($request->input('identifier'));
        $user = $this->findUserByIdentifier($identifier);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'حساب کاربری با این مشخصات یافت نشد.',
            ], 404);
        }

        $smsEnabled = function_exists('get_setting') && get_setting('auth_sms_enabled', '0') === '1';
        $passwordEnabled = function_exists('get_setting') && get_setting('auth_password_enabled', '1') === '1';
        $priorityMethod = function_exists('get_setting') ? get_setting('auth_priority_method', 'password') : 'password';
        $otpTtl = (int) (function_exists('get_setting') ? get_setting('auth_otp_ttl', 3) : 3);
        $resendInterval = (int) (function_exists('get_setting') ? get_setting('auth_otp_resend_interval', 60) : 60);

        $hasMobile = !empty($user->mobile);
        $canSms = $smsEnabled && $hasMobile;
        // در صورتی که ورود پیامکی غیرفعال باشد یا کاربر شماره موبایل نداشته باشد، ورود با رمز حتماً فعال است
        $canPassword = $passwordEnabled || !$canSms;

        $step = 'password';
        $otpSent = false;
        $otpMessage = null;
        $resendIn = 0;

        // اگر ورود پیامکی مجاز است و اولویت با پیامک است (یا رمز عبور کلاً خاموش است)
        if ($canSms && (!$canPassword || $priorityMethod === 'sms')) {
            $step = 'otp';
            $sendResult = $this->dispatchOtpToUser($user, $request);
            $otpSent = $sendResult['success'];
            $resendIn = $sendResult['resend_in'] ?? $resendInterval;
            $otpMessage = $sendResult['message'] ?? null;
        }

        return response()->json([
            'success' => true,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'masked_phone' => $this->maskPhoneNumber($user->mobile),
                'has_mobile' => $hasMobile,
            ],
            'can_sms' => $canSms,
            'can_password' => $canPassword,
            'step' => $step,
            'otp_sent' => $otpSent,
            'otp_message' => $otpMessage,
            'resend_in' => $resendIn,
            'expires_in' => $otpTtl * 60,
        ]);
    }

    /**
     * Send or resend OTP to the user's mobile number.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => ['required', 'string'],
        ]);

        $identifier = trim($request->input('identifier'));
        $user = $this->findUserByIdentifier($identifier);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'کاربر یافت نشد.',
            ], 404);
        }

        $smsEnabled = function_exists('get_setting') && get_setting('auth_sms_enabled', '0') === '1';
        if (!$smsEnabled || empty($user->mobile)) {
            return response()->json([
                'success' => false,
                'message' => 'امکان ارسال پیامک برای این حساب کاربری فعال نیست.',
            ], 422);
        }

        $result = $this->dispatchOtpToUser($user, $request);

        return response()->json($result, $result['success'] ? 200 : 429);
    }

    /**
     * Verify submitted OTP code and authenticate the user.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => ['required', 'string'],
            'code' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $identifier = trim($request->input('identifier'));
        $rawCode = trim($request->input('code'));
        $code = self::toEnglishDigits($rawCode);

        $user = $this->findUserByIdentifier($identifier);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'حساب کاربری یافت نشد.',
            ], 404);
        }

        $phone = self::normalizePhoneNumber($user->mobile);

        // یافتن آخرین کد OTP فعال برای این کاربر
        $otp = SmsOtp::query()
            ->where('context', self::CONTEXT)
            ->where(function ($q) use ($user, $phone) {
                $q->where('user_id', $user->id);
                if (!empty($phone)) {
                    $q->orWhere('phone', $phone);
                }
            })
            ->latest()
            ->first();

        if (!$otp || $otp->code !== $code || $otp->isExpired() || $otp->isUsed()) {
            return response()->json([
                'success' => false,
                'message' => 'کد تایید وارد شده نادرست است یا منقضی شده است.',
            ], 422);
        }

        // علامت‌گذاری به عنوان مصرف‌شده
        $otp->update([
            'used_at' => now(),
        ]);

        // لاگین کردن کاربر
        $remember = $request->boolean('remember');
        Auth::login($user, $remember);
        $request->session()->regenerate();

        // تعیین آدرس ریدایرکت
        $redirectUrl = $this->resolveRedirectUrl($user);

        return response()->json([
            'success' => true,
            'message' => 'ورود با موفقیت انجام شد.',
            'redirect_url' => $redirectUrl,
        ]);
    }

    /**
     * Dispatch OTP using SmsManager and persist record in sms_otps.
     */
    protected function dispatchOtpToUser(User $user, Request $request): array
    {
        $phone = self::normalizePhoneNumber($user->mobile);
        if (strlen($phone) !== 11 || !str_starts_with($phone, '09')) {
            return [
                'success' => false,
                'message' => 'شماره موبایل ثبت‌شده برای این کاربر معتبر نیست.',
            ];
        }

        $otpLength = (int) (function_exists('get_setting') ? get_setting('auth_otp_length', 5) : 5);
        $otpLength = max(4, min(8, $otpLength));

        $otpTtl = (int) (function_exists('get_setting') ? get_setting('auth_otp_ttl', 3) : 3);
        $otpTtl = max(1, min(15, $otpTtl));

        $resendInterval = (int) (function_exists('get_setting') ? get_setting('auth_otp_resend_interval', 60) : 60);
        $resendInterval = max(15, min(300, $resendInterval));

        // ۱. بررسی وقفه ارسال مجدد (Cooldown)
        $last = SmsOtp::query()
            ->where('phone', $phone)
            ->where('context', self::CONTEXT)
            ->latest()
            ->first();

        if ($last && $last->created_at && now()->diffInSeconds($last->created_at) < $resendInterval) {
            $remain = $resendInterval - now()->diffInSeconds($last->created_at);
            return [
                'success' => false,
                'message' => "لطفاً {$remain} ثانیه قبل از درخواست مجدد کد صبر کنید.",
                'resend_in' => $remain,
            ];
        }

        // ۲. تولید کد ایمن
        $code = (string) random_int(10 ** ($otpLength - 1), (10 ** $otpLength) - 1);

        // ۳. ارسال از طریق SmsManager
        $smsManager = app()->bound(SmsManager::class) ? app(SmsManager::class) : null;
        if ($smsManager) {
            try {
                // بررسی پترن پیامک در تنظیمات ورود یا تنظیمات عمومی پیامک
                $patternId = function_exists('get_setting') ? get_setting('auth_sms_pattern', '') : '';
                if (empty($patternId) && class_exists(SmsGatewaySetting::class)) {
                    $globalSetting = SmsGatewaySetting::query()->whereNull('user_id')->orderByDesc('id')->first()
                        ?: SmsGatewaySetting::query()->orderByDesc('id')->first();
                    $patternId = data_get($globalSetting, 'config.client_otp_pattern');
                }

                $options = [
                    'type' => SmsMessage::TYPE_OTP,
                    'related_type' => 'USER',
                    'related_id' => $user->id,
                    'meta' => [
                        'context' => self::CONTEXT,
                        'otp' => $code,
                        'user_id' => $user->id,
                    ],
                ];

                if (!empty($patternId)) {
                    $smsManager->sendPattern($phone, (string) $patternId, [$code], $options);
                } else {
                    $smsManager->sendText($phone, "کد ورود شما به سامانه: {$code}", $options);
                }
            } catch (\Throwable $e) {
                Log::error('[OtpLoginController] SMS send error: ' . $e->getMessage(), [
                    'phone' => $phone,
                    'user_id' => $user->id,
                ]);
            }
        }

        // ۴. ثبت در جدول sms_otps
        SmsOtp::create([
            'phone' => $phone,
            'code' => $code,
            'context' => self::CONTEXT,
            'user_id' => $user->id,
            'expires_at' => now()->addMinutes($otpTtl),
            'meta' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        ]);

        return [
            'success' => true,
            'message' => 'کد تایید پیامکی با موفقیت ارسال شد.',
            'resend_in' => $resendInterval,
            'expires_in' => $otpTtl * 60,
        ];
    }

    /**
     * Find a user by email, normalized mobile, or raw input.
     */
    protected function findUserByIdentifier(string $rawIdentifier): ?User
    {
        $clean = self::toEnglishDigits($rawIdentifier);
        $phone = self::normalizePhoneNumber($clean);

        $query = User::query()->where('email', $clean);

        if (!empty($phone)) {
            $query->orWhere('mobile', $phone);

            // اگر ۹ رقم انتهایی بود (بدون پیش‌شماره)
            if (strlen($phone) === 11) {
                $last9 = substr($phone, -9);
                $query->orWhere('mobile', 'like', "%{$last9}");
            }
        }

        // همچنین جستجوی مستقیم در نام کاربری یا موبایل
        $query->orWhere('mobile', $clean);

        return $query->first();
    }

    /**
     * Determine post-login redirect URL.
     */
    protected function resolveRedirectUrl(User $user): string
    {
        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            $default = '/admin/dashboard';
        } else {
            $default = '/user/dashboard';
        }

        $intended = session()->pull('url.intended', $default);

        if (!method_exists($user, 'hasRole') || !$user->hasRole('super-admin')) {
            if (str_contains($intended, '/admin/')) {
                return '/user/dashboard';
            }
        }

        return $intended;
    }

    /**
     * Mask phone number (e.g. 0912***4567).
     */
    protected function maskPhoneNumber(?string $number): string
    {
        $normalized = self::normalizePhoneNumber($number);
        if (strlen($normalized) === 11 && str_starts_with($normalized, '09')) {
            return substr($normalized, 0, 4) . '***' . substr($normalized, -4);
        }
        return $number ? substr($number, 0, 3) . '***' . substr($number, -3) : '';
    }

    /**
     * Convert Persian & Arabic numbers to English.
     */
    public static function toEnglishDigits(?string $str): string
    {
        if (empty($str)) {
            return '';
        }
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $str = str_replace($persian, $english, $str);
        return str_replace($arabic, $english, $str);
    }

    /**
     * Normalize Iranian mobile numbers to 09XXXXXXXXX.
     */
    public static function normalizePhoneNumber(?string $number): string
    {
        if (empty($number)) {
            return '';
        }

        $digits = preg_replace('/[^\d]/', '', self::toEnglishDigits($number));

        if (str_starts_with($digits, '0098')) {
            $digits = substr($digits, 4);
        } elseif (str_starts_with($digits, '98')) {
            $digits = substr($digits, 2);
        }

        if (!str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = '0' . $digits;
        }

        return $digits;
    }
}
