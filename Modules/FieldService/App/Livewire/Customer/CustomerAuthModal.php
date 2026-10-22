<?php

namespace Modules\FieldService\App\Livewire\Customer;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Modules\Clients\Entities\Client;
use Modules\Clients\Entities\ClientForm;
use Modules\Clients\Entities\ClientSetting;
use Modules\Clients\Entities\ClientStatus;
use Modules\Sms\Entities\SmsGatewaySetting;
use Modules\Sms\Entities\SmsMessage;
use Modules\Sms\Entities\SmsOtp;
use Modules\Sms\Services\SmsManager;

class CustomerAuthModal extends Component
{
    // Mode & Tab
    public string $mode = 'both'; // password | otp | both
    public string $tab = 'otp';   // password | otp
    public bool $isOpen = false;

    // Password Login State
    public string $username = '';
    public string $password = '';
    public bool $remember = true;
    public int $pwdStep = 1; // 1: username, 2: password

    // OTP Login State
    public string $otpUsername = '';
    public string $otpCode = '';
    public bool $otpSent = false;
    public int $otpResendIn = 60;

    // Registration State (when user not found and register enabled)
    public bool $isRegister = false;
    public string $registerFullName = '';
    public string $registerPhone = '';
    public string $registerPassword = '';
    public bool $viaOtp = false;

    // Alerts
    public ?string $errorMessage = null;
    public ?string $successMessage = null;

    protected $listeners = [
        'open-customer-auth'  => 'openModal',
        'close-customer-auth' => 'closeModal',
    ];

    public function mount()
    {
        $this->mode = ClientSetting::getValue('auth.mode', 'otp');
        $defaultLogin = ClientSetting::getValue('auth.default', 'otp');

        if (!in_array($this->mode, ['password', 'otp', 'both'], true)) {
            $this->mode = 'otp';
        }

        $this->tab = ($this->mode === 'password') ? 'password' : 'otp';

        // When user is not authenticated, open modal immediately on load
        if (!Auth::guard('client')->check()) {
            $this->isOpen = true;
        }
    }

    public function openModal()
    {
        $this->isOpen = true;
        $this->clearMessages();
    }

    public function closeModal()
    {
        if (Auth::guard('client')->check()) {
            $this->isOpen = false;
            $this->clearMessages();
        }
    }

    public function clearMessages()
    {
        $this->errorMessage = null;
        $this->successMessage = null;
    }

    public function switchTab(string $newTab)
    {
        $this->tab = $newTab;
        $this->clearMessages();
        $this->isRegister = false;
    }

    public static function toEnglishNumbers(?string $string): string
    {
        if ($string === null || $string === '') return '';
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $converted = str_replace($persian, $english, $string);
        return str_replace($arabic, $english, $converted);
    }

    public static function normalizePhoneNumber(?string $phone): string
    {
        if ($phone === null || $phone === '') return '';
        $phone = self::toEnglishNumbers($phone);
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (empty($digits)) return '';

        if (str_starts_with($digits, '0098') && strlen($digits) === 14) {
            $digits = '0' . substr($digits, 4);
        } elseif (str_starts_with($digits, '98') && strlen($digits) === 12) {
            $digits = '0' . substr($digits, 2);
        } elseif (str_starts_with($digits, '9') && strlen($digits) === 10) {
            $digits = '0' . $digits;
        }

        return $digits;
    }

    // =========================================================================
    // OTP WORKFLOW
    // =========================================================================

    public function sendOtp()
    {
        $this->clearMessages();
        $rawUsername = trim(self::toEnglishNumbers($this->otpUsername));

        if (empty($rawUsername)) {
            $this->errorMessage = 'لطفاً شماره موبایل خود را وارد کنید.';
            return;
        }

        $normalizedPhone = self::normalizePhoneNumber($rawUsername);
        $client = Client::where('username', $rawUsername)
            ->orWhere('phone', $rawUsername)
            ->orWhere('phone', $normalizedPhone)
            ->first();

        $registerEnabled = (bool) ClientSetting::getValue('auth.register_enabled', true);

        if (!$client) {
            if (!$registerEnabled) {
                $this->errorMessage = 'حساب کاربری یافت نشد و ثبت‌نام در حال حاضر غیرفعال است.';
                return;
            }

            if (strlen($normalizedPhone) !== 11 || !str_starts_with($normalizedPhone, '09')) {
                $this->errorMessage = 'شماره موبایل نامعتبر است. لطفاً یک شماره موبایل معتبر ایران (مثال: 0911xxxxxxx) وارد کنید.';
                return;
            }

            $this->isRegister = true;
            $this->registerPhone = $normalizedPhone;
            $phone = $normalizedPhone;
        } else {
            $this->isRegister = false;
            $phone = self::normalizePhoneNumber($client->phone) ?: $normalizedPhone;
        }

        $otpLength = (int) ClientSetting::getValue('auth.otp_length', 5);
        $otpTtl = (int) ClientSetting::getValue('auth.otp_ttl', 5);
        $this->otpResendIn = (int) ClientSetting::getValue('auth.otp_resend_interval', 60);
        $otpMaxRequests = (int) ClientSetting::getValue('auth.otp_max_requests', 3);

        $context = 'login_client';

        // Check rate limit
        $recentCount = SmsOtp::query()
            ->where('phone', $phone)
            ->where('context', $context)
            ->where('created_at', '>=', now()->subMinutes($otpTtl))
            ->count();

        if ($recentCount >= $otpMaxRequests) {
            $this->errorMessage = "تعداد درخواست‌های کد بیش از حد مجاز است. لطفاً {$otpTtl} دقیقه دیگر تلاش کنید.";
            return;
        }

        // Generate Code
        $code = (string) random_int(10 ** ($otpLength - 1), (10 ** $otpLength) - 1);

        // Send SMS via SmsManager if available
        if (class_exists(SmsManager::class)) {
            try {
                $sms = app(SmsManager::class);
                $patternId = null;
                if (class_exists(SmsGatewaySetting::class) && \Illuminate\Support\Facades\Schema::hasTable('sms_gateway_settings')) {
                    $globalSetting = SmsGatewaySetting::query()->orderByDesc('id')->first();
                    $patternId = data_get($globalSetting, 'config.client_otp_pattern');
                }

                $options = [
                    'type'         => SmsMessage::TYPE_OTP,
                    'related_type' => 'CLIENT',
                    'related_id'   => $client ? $client->id : null,
                    'meta'         => ['context' => $context, 'otp' => $code],
                ];

                if (!empty($patternId)) {
                    $sms->sendPattern($phone, (string) $patternId, [$code], $options);
                } else {
                    $sms->sendText($phone, "کد ورود نصاب‌باشی: {$code}", $options);
                }
            } catch (\Throwable $e) {
                \Log::warning('SMS sending error: ' . $e->getMessage());
            }
        }

        SmsOtp::create([
            'phone'      => $phone,
            'code'       => $code,
            'context'    => $context,
            'client_id'  => $client ? $client->id : null,
            'expires_at' => now()->addMinutes($otpTtl),
            'meta'       => [
                'username'        => $client ? $client->username : $phone,
                'is_registration' => $this->isRegister,
            ],
        ]);

        $this->otpSent = true;
        $this->viaOtp = true;
        $this->successMessage = "کد تایید یکبار مصرف به شماره {$phone} ارسال گردید.";
    }

    public function verifyOtp()
    {
        $this->clearMessages();
        $code = trim(self::toEnglishNumbers($this->otpCode));
        $rawUsername = trim(self::toEnglishNumbers($this->otpUsername));
        $normalizedPhone = self::normalizePhoneNumber($rawUsername);

        if (empty($code)) {
            $this->errorMessage = 'کد پیامکی را وارد کنید.';
            return;
        }

        $context = 'login_client';

        if ($this->isRegister) {
            // Validate code for new registration
            $otp = SmsOtp::query()
                ->where('phone', $this->registerPhone ?: $normalizedPhone)
                ->whereNull('client_id')
                ->where('context', $context)
                ->where('code', $code)
                ->latest()
                ->first();

            if (!$otp || $otp->isExpired() || $otp->isUsed()) {
                $this->errorMessage = 'کد تایید نامعتبر است یا منقضی شده است.';
                return;
            }

            // If user has not yet entered full_name, prompt them
            if (empty(trim($this->registerFullName))) {
                $this->errorMessage = 'لطفاً نام و نام خانوادگی خود را برای تکمیل ثبت‌نام وارد فرمایید.';
                return;
            }

            $otp->update(['used_at' => now()]);

            // Create new Client
            $client = Client::create([
                'username'  => $this->registerPhone,
                'full_name' => trim($this->registerFullName),
                'phone'     => $this->registerPhone,
                'password'  => Hash::make(Str::random(12)),
                'status_id' => ClientStatus::active()->orderBy('sort_order')->first()?->id,
            ]);

            Auth::guard('client')->login($client, true);
            $this->finishLogin($client);
            return;
        }

        // Existing client login
        $client = Client::where('username', $rawUsername)
            ->orWhere('phone', $rawUsername)
            ->orWhere('phone', $normalizedPhone)
            ->first();

        if (!$client) {
            $this->errorMessage = 'حساب کاربری یافت نشد.';
            return;
        }

        $clientPhone = self::normalizePhoneNumber($client->phone) ?: $normalizedPhone;
        $otp = SmsOtp::query()
            ->where('phone', $clientPhone)
            ->where('client_id', $client->id)
            ->where('context', $context)
            ->where('code', $code)
            ->latest()
            ->first();

        if (!$otp || $otp->isExpired() || $otp->isUsed()) {
            $this->errorMessage = 'کد تایید نادرست یا منقضی شده است.';
            return;
        }

        $otp->update(['used_at' => now()]);

        Auth::guard('client')->login($client, true);
        $this->finishLogin($client);
    }

    public function resetOtp()
    {
        $this->otpSent = false;
        $this->otpCode = '';
        $this->clearMessages();
    }

    // =========================================================================
    // PASSWORD WORKFLOW
    // =========================================================================

    public function checkPasswordStep()
    {
        $this->clearMessages();
        $rawUsername = trim(self::toEnglishNumbers($this->username));

        if (empty($rawUsername)) {
            $this->errorMessage = 'نام کاربری یا شماره همراه را وارد کنید.';
            return;
        }

        $normalizedPhone = self::normalizePhoneNumber($rawUsername);
        $client = Client::where('username', $rawUsername)
            ->orWhere('email', $rawUsername)
            ->orWhere('phone', $rawUsername)
            ->orWhere('phone', $normalizedPhone)
            ->first();

        if (!$client) {
            $registerEnabled = (bool) ClientSetting::getValue('auth.register_enabled', true);
            if ($registerEnabled) {
                // Switch to OTP registration for convenience
                $this->tab = 'otp';
                $this->otpUsername = $rawUsername;
                $this->sendOtp();
                return;
            } else {
                $this->errorMessage = 'حساب کاربری با این مشخصات یافت نشد.';
                return;
            }
        }

        $this->pwdStep = 2;
    }

    public function loginWithPassword()
    {
        $this->clearMessages();
        $rawUsername = trim(self::toEnglishNumbers($this->username));
        $normalizedPhone = self::normalizePhoneNumber($rawUsername);

        $client = Client::where('username', $rawUsername)
            ->orWhere('email', $rawUsername)
            ->orWhere('phone', $rawUsername)
            ->orWhere('phone', $normalizedPhone)
            ->first();

        if (!$client || !Hash::check($this->password, $client->password)) {
            $this->errorMessage = 'نام کاربری یا رمز عبور اشتباه است.';
            return;
        }

        Auth::guard('client')->login($client, $this->remember);
        $this->finishLogin($client);
    }

    protected function finishLogin(Client $client)
    {
        $this->isOpen = false;
        $this->clearMessages();
        $this->dispatch('client-authenticated', [
            'id'        => $client->id,
            'full_name' => $client->full_name,
            'phone'     => $client->phone,
        ]);
        $this->dispatch('refresh-customer-header');
    }

    public function logout()
    {
        Auth::guard('client')->logout();
        $this->dispatch('client-logged-out');
        $this->dispatch('refresh-customer-header');
    }

    public function render()
    {
        $client = Auth::guard('client')->user();

        return view('fieldservice::livewire.customer.customer-auth-modal', [
            'client' => $client,
        ]);
    }
}
