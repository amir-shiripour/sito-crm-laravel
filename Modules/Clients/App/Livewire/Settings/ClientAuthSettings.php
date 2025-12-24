<?php

namespace Modules\Clients\App\Livewire\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Modules\Clients\Entities\ClientSetting;

#[Layout('layouts.user')]
class ClientAuthSettings extends Component
{
    /**
     * حالت ورود:
     * password | otp | both
     */
    public string $mode = 'password';

    /**
     * وقتی mode = both باشد، کدام حالت پیش‌فرض است؟
     * password | otp
     */
    public string $defaultLogin = 'password';

    /**
     * طول کد OTP (تعداد رقم)
     */
    public int $otpLength = 5;

    /**
     * مدت اعتبار کد (دقیقه)
     */
    public int $otpTtl = 5;

    /**
     * فاصله زمانی بین دو ارسال مجدد (ثانیه)
     */
    public int $otpResendInterval = 60;

    /**
     * حداکثر تعداد درخواست ارسال کد پشت سر هم (در بازه کوتاه)
     */
    public int $otpMaxRequests = 3;

    public bool $smsModuleAvailable = false;
    public ?string $smsClientOtpPattern = null;

    public function mount()
    {
        // آیا ماژول SMS نصب است؟
        $this->smsModuleAvailable = class_exists(\Modules\Sms\Services\SmsManager::class);

        if ($this->smsModuleAvailable && class_exists(\Modules\Sms\Entities\SmsGatewaySetting::class)) {
            $user = auth()->user();
            if ($user) {
                $setting = \Modules\Sms\Entities\SmsGatewaySetting::query()
                    ->where('user_id', $user->id)
                    ->first();

                $this->smsClientOtpPattern = data_get($setting, 'config.client_otp_pattern');
            }
        }

        // خواندن تنظیمات ذخیره شده
        $this->mode = ClientSetting::getValue('auth.mode', 'password');
        $this->defaultLogin = ClientSetting::getValue('auth.default', 'password');

        $this->otpLength = (int) ClientSetting::getValue('auth.otp_length', config('sms.otp.length', 5));
        $this->otpTtl = (int) ClientSetting::getValue('auth.otp_ttl', config('sms.otp.ttl', 5));
        $this->otpResendInterval = (int) ClientSetting::getValue('auth.otp_resend_interval', 60);
        $this->otpMaxRequests = (int) ClientSetting::getValue('auth.otp_max_requests', 3);

        // اگر ماژول SMS در دسترس نیست، اجازه انتخاب otp را نده
        if (! $this->smsModuleAvailable && $this->mode !== 'password') {
            $this->mode = 'password';
            $this->defaultLogin = 'password';
        }
    }

    public function save()
    {
        // اگر SMS در دسترس نیست، فقط password
        if (! $this->smsModuleAvailable) {
            $this->mode = 'password';
            $this->defaultLogin = 'password';
        }

        // نرمال‌سازی مقادیر
        if (! in_array($this->mode, ['password', 'otp', 'both'], true)) {
            $this->mode = 'password';
        }

        if (! in_array($this->defaultLogin, ['password', 'otp'], true)) {
            $this->defaultLogin = 'password';
        }

        if ($this->mode === 'password') {
            $this->defaultLogin = 'password';
        }

        $this->otpLength = max(3, min(10, (int) $this->otpLength));
        $this->otpTtl = max(1, min(60, (int) $this->otpTtl));
        $this->otpResendInterval = max(10, min(600, (int) $this->otpResendInterval));
        $this->otpMaxRequests = max(1, min(10, (int) $this->otpMaxRequests));

        ClientSetting::setValue('auth.mode', $this->mode);
        ClientSetting::setValue('auth.default', $this->defaultLogin);

        ClientSetting::setValue('auth.otp_length', $this->otpLength);
        ClientSetting::setValue('auth.otp_ttl', $this->otpTtl);
        ClientSetting::setValue('auth.otp_resend_interval', $this->otpResendInterval);
        ClientSetting::setValue('auth.otp_max_requests', $this->otpMaxRequests);

        // در صورت تمایل می‌تونی هم‌زمان config('sms.otp') رو هم در همین ریکوئست ست کنی:
        config([
            'sms.otp.length' => $this->otpLength,
            'sms.otp.ttl'    => $this->otpTtl,
        ]);

        session()->flash('success', 'تنظیمات ورود مشتریان ذخیره شد.');
    }

    public function render()
    {
        return view('clients::user.settings.auth');
    }
}
