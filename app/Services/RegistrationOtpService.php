<?php

namespace App\Services;

use Modules\Sms\Entities\SmsOtp;
use Modules\Sms\Entities\SmsMessage;
use Modules\Sms\Entities\SmsGatewaySetting;
use Modules\Sms\Services\SmsManager;
use Illuminate\Support\Facades\Log;

class RegistrationOtpService
{
    public const CONTEXT = 'register_otp';

    /**
     * Normalize Iranian phone numbers (e.g. +98912..., 0098912..., 912... -> 0912...)
     */
    public static function normalizePhoneNumber(?string $number): string
    {
        if (empty($number)) {
            return '';
        }

        // Convert Persian/Arabic digits to English
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $number = str_replace($persian, $english, $number);
        $number = str_replace($arabic, $english, $number);

        // Strip non-digits
        $digits = preg_replace('/[^\d]/', '', $number);

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

    /**
     * Send OTP to the mobile number using SmsManager with Limo/Pattern support
     */
    public static function sendOtp(string $rawPhone, ?SmsManager $smsManager = null): array
    {
        $phone = self::normalizePhoneNumber($rawPhone);

        if (strlen($phone) !== 11 || !str_starts_with($phone, '09')) {
            return [
                'success' => false,
                'message' => 'شماره موبایل وارد شده معتبر نیست. لطفاً یک شماره موبایل معتبر (مانند ۰۹۱۲۳۴۵۶۷۸۹) وارد کنید.',
            ];
        }

        $otpLength = (int) config('sms.otp.length', 5);
        $otpTtl = (int) config('sms.otp.ttl', 5);
        $otpResendInterval = 60; // 60 seconds cooldown

        // 1. Check cooldown
        $last = SmsOtp::query()
            ->where('phone', $phone)
            ->where('context', self::CONTEXT)
            ->latest()
            ->first();

        if ($last && $last->created_at && now()->diffInSeconds($last->created_at) < $otpResendInterval) {
            $remain = $otpResendInterval - now()->diffInSeconds($last->created_at);
            return [
                'success' => false,
                'message' => "برای درخواست مجدد کد، لطفاً {$remain} ثانیه دیگر صبر کنید.",
                'resend_in' => $remain,
            ];
        }

        // 2. Generate secure numeric code
        $code = (string) random_int(10 ** ($otpLength - 1), (10 ** $otpLength) - 1);

        // 3. Dispatch via SmsManager
        if (!$smsManager && app()->bound(SmsManager::class)) {
            $smsManager = app(SmsManager::class);
        }

        if ($smsManager) {
            try {
                // Check if OTP pattern is configured in SmsGatewaySetting
                $setting = SmsGatewaySetting::query()->whereNull('user_id')->orderByDesc('id')->first();
                if (!$setting) {
                    $setting = SmsGatewaySetting::query()->orderByDesc('id')->first();
                }

                $patternId = data_get($setting, 'config.client_otp_pattern');

                $options = [
                    'type'        => SmsMessage::TYPE_OTP,
                    'related_type'=> 'USER_REGISTRATION',
                    'meta'        => [
                        'context' => self::CONTEXT,
                        'otp'     => $code,
                    ],
                ];

                if (!empty($patternId)) {
                    $smsManager->sendPattern($phone, (string) $patternId, [$code], $options);
                } else {
                    $smsManager->sendText($phone, "کد تایید ثبت‌نام شما: {$code}", $options);
                }
            } catch (\Throwable $e) {
                Log::error('[RegistrationOtpService] SMS send error: ' . $e->getMessage(), [
                    'phone' => $phone,
                ]);
            }
        }

        // 4. Save to sms_otps
        SmsOtp::create([
            'phone'      => $phone,
            'code'       => $code,
            'context'    => self::CONTEXT,
            'expires_at' => now()->addMinutes($otpTtl),
            'meta'       => [
                'purpose' => 'user_registration',
            ],
        ]);

        return [
            'success'   => true,
            'message'   => 'کد تایید با موفقیت ارسال شد.',
            'resend_in' => $otpResendInterval,
            'phone'     => $phone,
        ];
    }

    /**
     * Verify submitted OTP code for the mobile number
     */
    public static function verifyOtp(string $rawPhone, string $rawCode): array
    {
        $phone = self::normalizePhoneNumber($rawPhone);
        $code = trim(self::normalizePhoneNumber($rawCode)); // Normalize digits

        if (empty($phone) || empty($code)) {
            return [
                'success' => false,
                'message' => 'شماره موبایل و کد تایید الزامی است.',
            ];
        }

        $otp = SmsOtp::query()
            ->where('phone', $phone)
            ->where('code', $code)
            ->where('context', self::CONTEXT)
            ->latest()
            ->first();

        if (!$otp || $otp->isExpired() || $otp->isUsed()) {
            return [
                'success' => false,
                'message' => 'کد تایید وارد شده نادرست است یا منقضی شده است.',
            ];
        }

        $otp->update([
            'used_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'شماره موبایل با موفقیت تایید شد.',
            'phone'   => $phone,
        ];
    }
}
