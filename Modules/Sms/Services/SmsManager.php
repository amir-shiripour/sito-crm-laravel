<?php

namespace Modules\Sms\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Sms\Entities\SmsGatewaySetting;
use Modules\Sms\Entities\SmsMessage;
use Modules\Sms\Services\Contracts\SmsSender;
use Modules\Sms\Services\Drivers\DriverInterface;
use Illuminate\Support\Str;

class SmsManager implements SmsSender
{
    protected Application $app;

    /**
     * @var array<string, DriverInterface>
     */
    protected array $drivers = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * گرفتن یک درایور مشخص
     */
    public function driver(?string $name = null): DriverInterface
    {
        $name = $name ?: $this->getActiveDriverName();

        if (! isset($this->drivers[$name])) {
            $this->drivers[$name] = $this->resolveDriver($name);
        }

        return $this->drivers[$name];
    }

    /**
     * تشخیص اسم درایور فعال
     * طبق درخواست: تنظیمات به صورت سراسری خوانده می‌شود و وابسته به کاربر لاگین شده نیست.
     */
    protected function getActiveDriverName(): string
    {
        // همیشه آخرین تنظیمات ذخیره شده در سیستم را بررسی می‌کنیم
        $globalSetting = SmsGatewaySetting::query()
            ->whereNotNull('driver')
            ->orderByDesc('id')
            ->first();

        if ($globalSetting && $globalSetting->driver) {
            return $globalSetting->driver;
        }

        // در نهایت اگر هیچ تنظیمی نبود، برو سراغ کانفیگ
        return $this->getDefaultDriver();
    }

    /**
     * اسم درایور پیش‌فرض از کانفیگ
     */
    public function getDefaultDriver(): string
    {
        return config('sms.default_driver', 'null');
    }

    /**
     * ساخت instance درایور + ادغام کانفیگ‌ها
     */
    protected function resolveDriver(string $name): DriverInterface
    {
        $drivers = config('sms.drivers', []);
        $class   = $drivers[$name] ?? null;

        if (! $class) {
            throw new \InvalidArgumentException("SMS driver [$name] is not defined.");
        }

        // ۱) کانفیگ پایه از فایل sms.php
        $config = config("sms.driver_config.$name", []);

        // ۲) دریافت تنظیمات از دیتابیس (بدون توجه به کاربر لاگین شده - سراسری)
        $setting = SmsGatewaySetting::query()
            ->where('driver', $name)
            ->orderByDesc('id')
            ->first();

        if ($setting) {
            $dbConfig = $setting->config ?? [];

            // اگر sender در config نیامده ولی ستون sender پر است، اضافه‌اش کن
            if (! isset($dbConfig['sender']) && $setting->sender) {
                $dbConfig['sender'] = $setting->sender;
            }

            // ادغام تنظیمات دیتابیس روی کانفیگ پایه
            $config = array_merge($config, $dbConfig);
        }

        // Force check env if sender is missing
        if (empty($config['sender']) && $name === 'limosms') {
            $config['sender'] = env('LIMOSMS_SENDER');
        }

        Log::debug('[SmsManager] resolveDriver', [
            'driver'     => $name,
            'has_apiKey' => ! empty($config['api_key'] ?? null),
            'sender'     => $config['sender'] ?? 'NULL',
            'config_keys'=> array_keys($config),
        ]);

        return new $class($config);
    }


    /**
     * ساخت مدل پیامک از روی داده‌ها
     */
    protected function createMessageModel(
        string $driverName,
        string $to,
        ?string $body,
        array $options = []
    ): SmsMessage {
        $payload = [
            'to'           => $to,
            'from'         => $options['from'] ?? null,
            'message'      => $body,
            'type'         => $options['type'] ?? SmsMessage::TYPE_MANUAL,
            'channel'      => SmsMessage::CHANNEL_SMS,
            'status'       => SmsMessage::STATUS_PENDING,
            'driver'       => $driverName,
            'template_key' => $options['template_key'] ?? null,
            'params'       => $options['params'] ?? null,
            'related_type' => $options['related_type'] ?? null,
            'related_id'   => $options['related_id'] ?? null,
            'scheduled_at' => $options['scheduled_at'] ?? null,
            'created_by'   => $options['created_by'] ?? Auth::id(),
            'meta'         => $options['meta'] ?? [],
        ];

        return SmsMessage::createFromPayload($payload);
    }

    /**
     * ارسال پیامک متنی معمولی
     */
    public function sendText(string $to, string $message, array $options = []): SmsMessage
    {
        $driverName = $options['driver'] ?? $this->getActiveDriverName();
        $driver     = $this->driver($driverName);

        $sms = $this->createMessageModel($driverName, $to, $message, $options);

        // فعلاً ارسال زمان‌بندی‌شده را هم همان لحظه می‌فرستیم؛
        // بعداً می‌تونیم بفرستیم تو صف
        if (empty($sms->scheduled_at) || $sms->scheduled_at <= now()) {
            $driver->sendText($sms);
        }

        return $sms;
    }

    /**
     * ارسال پیامک پترنی
     */
    public function sendPattern(string $to, string $patternKey, array $params = [], array $options = []): SmsMessage
    {
        $driverName = $options['driver'] ?? $this->getActiveDriverName();
        $driver     = $this->driver($driverName);

        $options['template_key'] = $patternKey;
        $options['params']       = $params;

        $sms = $this->createMessageModel($driverName, $to, null, $options);

        if (empty($sms->scheduled_at) || $sms->scheduled_at <= now()) {
            $driver->sendPattern($sms, $params);
        }

        return $sms;
    }


    public function sendOtp(string $to, string $context = 'login', array $options = []): SmsMessage
    {
        $driverName = $options['driver'] ?? $this->getActiveDriverName();
        $driver     = $this->driver($driverName);

        // این دو تا را از تنظیمات کلاینت هم می‌تونیم override کنیم
        $otpLength = (int) ($options['otp_length'] ?? config('sms.otp.length', 5));
        $ttl       = (int) ($options['otp_ttl'] ?? config('sms.otp.ttl', 5));

        $code = (string) random_int(10 ** ($otpLength - 1), (10 ** $otpLength) - 1);

        $options['type'] = SmsMessage::TYPE_OTP;
        $options['meta'] = array_merge($options['meta'] ?? [], [
            'context'    => $context,
            'otp'        => $code,
            'ttl'        => $ttl,
            'expires_at' => now()->addMinutes($ttl)->toIso8601String(),
        ]);

        // اگر OTP برای کلاینت است و پترن تعریف شده، با پترن بفرست
        $otpPatternId = null;
        if ($context === 'login_client') {
            // استفاده از تنظیمات سراسری برای پیدا کردن پترن OTP
            $setting = \Modules\Sms\Entities\SmsGatewaySetting::query()
                ->whereNotNull('driver')
                ->orderByDesc('id')
                ->first();

            $otpPatternId = data_get($setting, 'config.client_otp_pattern');
        }

        // پیام را ذخیره می‌کنیم (code داخل message بماند)
        $sms = $this->createMessageModel($driverName, $to, $code, $options);

        if (!empty($otpPatternId)) {
            // برای لیمو: ReplaceToken باید آرایه باشد. {0} = code
            $sms->template_key = (string) $otpPatternId;
            $sms->params = [$code];
            $sms->save();

            $driver->sendPattern($sms, [$code]);
            return $sms;
        }

        // fallback: ارسال متنی (اگر پترن ست نبود)
        $driver->sendOtp($sms);
        return $sms;
    }


}
