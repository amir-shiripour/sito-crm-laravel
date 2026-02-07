<?php

namespace Modules\Sms\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sms\Entities\SmsGatewaySetting;
use Modules\Sms\Services\SmsManager;

class SmsSettingsController extends Controller
{
    public function index(Request $request, SmsManager $sms)
    {
        // دریافت تنظیمات سراسری (بدون وابستگی به کاربر)
        $setting = SmsGatewaySetting::query()
            ->whereNull('user_id')
            ->orderByDesc('id')
            ->first();

        // اگر تنظیمات سراسری نبود، برای جلوگیری از خالی بودن فرم، آخرین تنظیمات موجود را می‌گیریم (اختیاری)
        if (! $setting) {
            $setting = SmsGatewaySetting::query()
                ->orderByDesc('id')
                ->first();
        }

        $balance = null;

        if ($setting && $setting->driver) {
            try {
                // برای گرفتن موجودی، باید مطمئن شویم درایور با این تنظیمات لود می‌شود
                // اما متد driver() در SmsManager الان طوری تنظیم شده که آخرین تنظیمات را می‌خواند.
                // اگر $setting فعلی همان آخرین تنظیمات باشد، درست کار می‌کند.
                $balance = $sms->driver($setting->driver)->fetchBalance();
            } catch (\Throwable $e) {
                $balance = null;
            }
        }

        // آیا ماژول کلاینت نصب است؟
        $clientsModuleInstalled = class_exists(\Modules\Clients\Entities\Client::class);

        // پترن OTP مخصوص ورود کلاینت‌ها
        $clientOtpPattern = data_get($setting, 'config.client_otp_pattern');

        return view('sms::user.settings.index', [
            'setting'             => $setting,
            'balance'             => $balance,
            'clientsModuleInstalled' => $clientsModuleInstalled,
            'clientOtpPattern'    => $clientOtpPattern,
        ]);
    }

    public function store(Request $request)
    {
        // اعتبارسنجی داده‌ها
        $data = $request->validate([
            'driver'             => ['required', 'string', 'max:100'],
            'sender'             => ['nullable', 'string', 'max:191'],
            'api_key'            => ['nullable', 'string', 'max:191'],
            'base_url'           => ['nullable', 'string', 'max:191'],
            // پترن OTP برای ورود مشتریان (OtpId لیمو)
            'client_otp_pattern' => ['nullable', 'string', 'max:191'],
            'config'             => ['array'],
        ]);

        $config = [
                'api_key'            => $data['api_key'] ?? null,
                'base_url'           => $data['base_url'] ?? null,
                'client_otp_pattern' => $data['client_otp_pattern'] ?? null,
            ] + ($data['config'] ?? []);

        // ذخیره به صورت سراسری (user_id = null)
        $setting = SmsGatewaySetting::updateOrCreate(
            ['user_id' => null],
            [
                'driver' => $data['driver'],
                'sender' => $data['sender'] ?? null,
                'config' => $config,
            ]
        );

        return redirect()
            ->route('sms.settings.index')
            ->with('status', 'تنظیمات پیامک با موفقیت ذخیره شد.');
    }
}
