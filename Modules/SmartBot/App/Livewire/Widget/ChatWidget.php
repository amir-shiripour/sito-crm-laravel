<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Livewire\Widget;

use Livewire\Component;
use Modules\SmartBot\App\Services\BotEngineService;
use Modules\SmartBot\App\Services\EntityResolverService;
use Modules\SmartBot\App\Models\BotSetting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Clients\Entities\Client;
use Modules\Clients\Entities\ClientSetting;
use Modules\Clients\Entities\ClientForm;
use Modules\Clients\Entities\ClientStatus;

class ChatWidget extends Component
{
    public string $uuid = '';
    public string $userMessage = '';
    public array $messages = [];
    public array $suggestions = [];
    public string $botName = 'SmartBot';
    public string $primaryColor = '#6366f1';
    public string $primaryColorRgb = '99, 102, 241';
    public ?string $botIcon = null;
    public ?string $botIconSvg = null;
    public bool $isWidgetOpen = false;
    public bool $isThinking = false;
    public bool $isStandalone = false;
    public string $lastUserMessage = '';
    public ?int $selectedMenuItemId = null;
    public ?string $selectedMenuItemLabel = null;
    public bool $allowCustomTyping = true;
    public int $cartItemCount = 0;

    // Auth Restriction Properties
    public bool $requireClientAuth = false;
    public bool $isClientLoggedIn = false;
    public bool $showAuthPanel = false;

    public string $authStep = 'identifier'; // identifier | password | otp | register
    public string $authUsername = '';
    public string $authPassword = '';
    public string $authOtp = '';
    public string $authError = '';
    public string $authSuccessMsg = '';

    public string $usernameStrategy = 'email_local';
    public string $usernameLabel = 'شناسه کاربری';
    public bool $registerEnabled = false;
    public string $authMode = 'password'; // password | otp | both
    public string $authDefault = 'password'; // password | otp
    public array $regFormFields = [];
    public array $regInputs = [];
    public array $pendingRegistrationData = [];

    // Assistant Level 2 Properties
    public int $assistantLevel = 1;
    public array $expandedVariants = [];
    public array $openVariantProductIds = [];
    public array $selectedProductAttributes = [];
    public array $variantCardOpenedForProducts = [];

    protected $listeners = [
        'cartUpdated' => 'updateCartCount',
        'resetSession' => 'resetSession',
    ];

    public function updateCartCount()
    {
        $cart = session()->get('market_cart', []);
        $this->cartItemCount = (int) array_sum(array_column($cart, 'quantity'));

        // Automatically remove variant card when its product is added to cart
        $messagesToRemove = [];
        foreach ($this->messages as $msg) {
            if (($msg['answer_type'] ?? '') === 'variant_card') {
                $productId = !empty($msg['products']) ? ($msg['products'][0]['id'] ?? null) : null;
                if ($productId) {
                    $selectedVariantId = $this->expandedVariants[$productId]['selected_variant_id'] ?? null;
                    if ($selectedVariantId) {
                        $vendorProductId = null;
                        if (!empty($this->expandedVariants[$productId]['variants'])) {
                            $variant = collect($this->expandedVariants[$productId]['variants'])->firstWhere('variant_id', $selectedVariantId);
                            if ($variant) {
                                $vendorProductId = $variant['vendor_product_id'] ?? null;
                            }
                        }
                        
                        if ($vendorProductId) {
                            $cartKey = $selectedVariantId . '_' . $vendorProductId;
                            if (isset($cart[$cartKey]) && $cart[$cartKey]['quantity'] > 0) {
                                $messagesToRemove[] = $msg['id'];
                            }
                        }
                    }
                }
            }
        }

        foreach ($messagesToRemove as $msgId) {
            $this->removeMessage($msgId);
        }
    }

    public function mount(?string $sessionUuid = null)
    {
        $this->botName = (string) BotSetting::getValue('name', 'SmartBot');
        $this->primaryColor = (string) BotSetting::getValue('primary_color', '#6366f1');
        $this->assistantLevel = (int) BotSetting::getValue('assistant_level', 1);
        
        $hex = str_replace('#', '', $this->primaryColor);
        if (strlen($hex) === 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2) ?: '99');
            $g = hexdec(substr($hex, 2, 2) ?: '102');
            $b = hexdec(substr($hex, 4, 2) ?: '241');
        }
        $this->primaryColorRgb = "$r, $g, $b";

        $iconSetting = BotSetting::getValue('bot_icon');
        if ($iconSetting) {
            $path = storage_path('app/public/' . $iconSetting);
            if (file_exists($path) && str_ends_with(strtolower($path), '.svg')) {
                $svgContent = file_get_contents($path);
                $svgContent = preg_replace('/<\?xml.*\?>/i', '', $svgContent);
                $svgContent = preg_replace('/<svg/i', '<svg class="custom-bot-icon"', $svgContent);
                $this->botIconSvg = $svgContent;
            } else {
                $this->botIcon = asset('storage/' . $iconSetting);
            }
        }

        $this->allowCustomTyping = filter_var(BotSetting::getValue('allow_custom_typing', true), FILTER_VALIDATE_BOOLEAN);
        $this->updateCartCount();

        // Auth Restriction Setup
        $this->requireClientAuth = filter_var(BotSetting::getValue('require_client_auth', false), FILTER_VALIDATE_BOOLEAN);
        $this->isClientLoggedIn = Auth::guard('client')->check();
        $this->showAuthPanel = $this->requireClientAuth && !$this->isClientLoggedIn;

        if (class_exists(ClientSetting::class)) {
            $this->usernameStrategy = (string) ClientSetting::getValue('username_strategy', 'email_local');
            $this->authMode = (string) ClientSetting::getValue('auth.mode', 'password');
            $this->authDefault = (string) ClientSetting::getValue('auth.default', 'password');
            $this->registerEnabled = (bool) ClientSetting::getValue('auth.register_enabled', false);

            $this->usernameLabel = match ($this->usernameStrategy) {
                'mobile' => 'شماره موبایل',
                'email_local', 'email' => 'ایمیل',
                'national_code' => 'کد ملی',
                default => 'نام کاربری / ایمیل / شماره موبایل',
            };
        }

        $this->uuid = $sessionUuid ?? (string) Session::get('smartbot_session_uuid');
        if (!$this->uuid) {
            $this->uuid = Str::uuid()->toString();
            Session::put('smartbot_session_uuid', $this->uuid);
        }

        if ($this->isStandalone) {
            $this->isWidgetOpen = true;
        }

        $engine = app(BotEngineService::class);
        $session = $engine->getOrCreateSession($this->uuid, request()->fullUrl(), [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->loadMessages($session);
        $this->suggestions = $engine->getSuggestedQuestions((int) BotSetting::getValue('max_suggestions', 5));
    }

    // --- Authentication Flow Methods ---

    public function checkIdentifier(): void
    {
        $this->authError = '';
        $this->authUsername = trim($this->authUsername);

        if (empty($this->authUsername)) {
            $this->authError = "لطفاً {$this->usernameLabel} خود را وارد کنید.";
            return;
        }

        $normalizedPhone = preg_replace('/[^0-9]/', '', $this->authUsername);
        if (strlen($normalizedPhone) === 10 && str_starts_with($normalizedPhone, '9')) {
            $normalizedPhone = '0' . $normalizedPhone;
        }

        if (!class_exists(Client::class)) {
            $this->authError = 'ماژول کلاینت‌ها فعال نیست.';
            return;
        }

        $clientQuery = Client::query()
            ->where('username', $this->authUsername)
            ->orWhere('email', $this->authUsername)
            ->orWhere('phone', $this->authUsername)
            ->orWhere('national_code', $this->authUsername);

        if (strlen($normalizedPhone) === 11 && str_starts_with($normalizedPhone, '09')) {
            $clientQuery->orWhere('phone', $normalizedPhone)
                        ->orWhere('username', $normalizedPhone);
        }

        $client = $clientQuery->first();

        if ($client) {
            // اگر کاربر از قبل عضو بود، بر اساس تنظیمات ورود هدایت می‌شود
            $shouldUseOtp = ($this->authMode === 'otp' || ($this->authMode === 'both' && $this->authDefault === 'otp'));
            if ($shouldUseOtp) {
                $this->sendOtpCode();
            } else {
                $this->authStep = 'password';
            }
        } else {
            // اگر کاربر عضو نبود، مستقیماً به فرم ثبت‌نام می‌رود (کد تایید در مرحله اول ارسال نمی‌شود)
            if ($this->registerEnabled) {
                $this->prepareRegistrationForm();
            } else {
                $this->authError = "حساب کاربری با این {$this->usernameLabel} یافت نشد و ثبت‌نام غیرفعال است.";
            }
        }
    }

    public function attemptLogin(): void
    {
        $this->authError = '';

        if (empty($this->authPassword)) {
            $this->authError = 'لطفاً رمز عبور را وارد کنید.';
            return;
        }

        $client = Client::query()
            ->where('username', $this->authUsername)
            ->orWhere('email', $this->authUsername)
            ->orWhere('phone', $this->authUsername)
            ->orWhere('national_code', $this->authUsername)
            ->first();

        if ($client && Auth::guard('client')->attempt([
            'username' => $client->username,
            'password' => $this->authPassword,
        ])) {
            $this->afterAuthSuccess();
        } else {
            $this->authError = "{$this->usernameLabel} یا رمز عبور نادرست است.";
        }
    }

    public function prepareRegistrationForm(): void
    {
        $this->authStep = 'register';
        $this->regInputs = [];

        $fields = [];
        if (class_exists(ClientForm::class)) {
            $activeForm = ClientForm::active();
            $fields = $activeForm ? ($activeForm->schema['fields'] ?? []) : [];
        }

        // Strictly filter fields where show_in_registration is true in form builder
        $this->regFormFields = collect($fields)
            ->filter(fn($f) => !empty($f['show_in_registration']))
            ->values()
            ->toArray();

        foreach ($this->regFormFields as $field) {
            $fid = $field['id'];
            $this->regInputs[$fid] = '';

            if ($fid === 'phone') {
                $normalizedPhone = preg_replace('/[^0-9]/', '', $this->authUsername);
                if (strlen($normalizedPhone) === 10 && str_starts_with($normalizedPhone, '9')) {
                    $normalizedPhone = '0' . $normalizedPhone;
                }
                $this->regInputs['phone'] = (strlen($normalizedPhone) === 11 && str_starts_with($normalizedPhone, '09')) ? $normalizedPhone : ($this->usernameStrategy === 'mobile' ? $this->authUsername : '');
            } elseif ($fid === 'email' && ($this->usernameStrategy === 'email_local' || $this->usernameStrategy === 'email')) {
                $this->regInputs['email'] = str_contains($this->authUsername, '@') ? $this->authUsername : '';
            } elseif ($fid === 'national_code' && $this->usernameStrategy === 'national_code') {
                $this->regInputs['national_code'] = $this->authUsername;
            }
        }
    }

    public function attemptRegister(): void
    {
        $this->authError = '';

        $fullName = trim((string)($this->regInputs['full_name'] ?? ''));
        $password = (string)($this->regInputs['password'] ?? '');
        $phone = trim((string)($this->regInputs['phone'] ?? ''));
        $email = trim((string)($this->regInputs['email'] ?? ''));
        $nationalCode = trim((string)($this->regInputs['national_code'] ?? ''));

        // اگر استراتژی موبایل است و فیلد شماره خالی است، از authUsername استفاده شود
        if ($this->usernameStrategy === 'mobile' && empty($phone)) {
            $normalized = preg_replace('/[^0-9]/', '', $this->authUsername);
            if (strlen($normalized) === 10 && str_starts_with($normalized, '9')) {
                $normalized = '0' . $normalized;
            }
            if (strlen($normalized) === 11 && str_starts_with($normalized, '09')) {
                $phone = $normalized;
                $this->regInputs['phone'] = $phone;
            }
        }

        // Validate only fields present in regFormFields
        foreach ($this->regFormFields as $field) {
            $fid = $field['id'];
            $label = $field['label'] ?? $fid;
            $isRequired = !empty($field['required']);
            $val = trim((string)($this->regInputs[$fid] ?? ''));

            if ($fid === 'password' && $this->authMode === 'otp') {
                continue;
            }

            if ($isRequired && empty($val)) {
                $this->authError = "فیلد {$label} الزامی است.";
                return;
            }

            if ($fid === 'password' && !empty($val) && strlen($val) < 6) {
                $this->authError = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
                return;
            }

            if (($field['type'] ?? '') === 'email' && !empty($val) && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
                $this->authError = "فرمت {$label} نامعتبر است.";
                return;
            }
        }

        if ($phone && Client::where('phone', $phone)->exists()) {
            $this->authError = 'این شماره موبایل قبلاً در سیستم ثبت شده است.';
            return;
        }

        if ($email && Client::where('email', $email)->exists()) {
            $this->authError = 'این ایمیل قبلاً در سیستم ثبت شده است.';
            return;
        }

        if ($nationalCode && Client::where('national_code', $nationalCode)->exists()) {
            $this->authError = 'این کد ملی قبلاً در سیستم ثبت شده است.';
            return;
        }

        $base = match ($this->usernameStrategy) {
            'mobile' => $phone ?: $this->authUsername,
            'national_code' => $nationalCode ?: $this->authUsername,
            'email_local', 'email' => explode('@', $email ?: $this->authUsername)[0] ?? '',
            default => 'user_' . Str::random(6),
        };

        $base = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', (string)$base);
        if (empty($base)) {
            $base = 'user_' . Str::random(8);
        }

        $username = $base;
        $counter = 1;
        while (Client::where('username', $username)->exists()) {
            $username = $base . '_' . $counter++;
        }

        $activeStatus = class_exists(ClientStatus::class) ? ClientStatus::active()->orderBy('sort_order')->first()?->id : null;

        $systemFieldKeys = ['full_name', 'phone', 'email', 'national_code', 'notes', 'status_id', 'password', 'username', 'case_number'];
        $meta = [];
        foreach ($this->regFormFields as $field) {
            $fid = $field['id'];
            if (!in_array($fid, $systemFieldKeys, true)) {
                $meta[$fid] = $this->regInputs[$fid] ?? null;
            }
        }

        $clientData = [
            'username' => $username,
            'full_name' => $fullName ?: ($this->authUsername ?: 'کاربر ثبت‌نامی'),
            'email' => $email ?: null,
            'phone' => $phone ?: null,
            'national_code' => $nationalCode ?: null,
            'case_number' => $this->regInputs['case_number'] ?? null,
            'notes' => $this->regInputs['notes'] ?? null,
            'password' => Hash::make($password ?: Str::random(12)),
            'status_id' => $activeStatus,
            'meta' => $meta,
        ];

        // پس از تکمیل اطلاعات، اگر در تنظیمات ورود، حالت OTP فعال بود در مرحله آخر کد تایید ارسال می‌شود
        $needsOtp = ($this->authMode === 'otp' || ($this->authMode === 'both' && $this->authDefault === 'otp'));

        if ($needsOtp) {
            $regPhone = $phone;
            if (empty($regPhone)) {
                $regPhone = preg_replace('/[^0-9]/', '', $this->authUsername);
                if (strlen($regPhone) === 10 && str_starts_with($regPhone, '9')) {
                    $regPhone = '0' . $regPhone;
                }
            }

            if (empty($regPhone) || strlen($regPhone) !== 11 || !str_starts_with($regPhone, '09')) {
                $this->authError = 'برای تأیید حساب، وارد کردن شماره موبایل معتبر (۱۱ رقمی) الزامی است.';
                return;
            }

            $clientData['phone'] = $regPhone;
            $this->pendingRegistrationData = $clientData;
            $this->sendOtpCode($regPhone);
            return;
        }

        $client = Client::create($clientData);

        Auth::guard('client')->login($client);
        $this->afterAuthSuccess();
    }

    public function sendOtpCode(?string $targetPhone = null): void
    {
        $this->authError = '';
        if (!class_exists(\Modules\Sms\Services\SmsManager::class)) {
            $this->authError = 'سیستم ارسال پیامک فعال نیست.';
            return;
        }

        $phone = $targetPhone;

        if (empty($phone) && !empty($this->pendingRegistrationData['phone'])) {
            $phone = $this->pendingRegistrationData['phone'];
        }

        $client = null;

        if (empty($phone)) {
            $phone = preg_replace('/[^0-9]/', '', $this->authUsername);
            if (strlen($phone) === 10 && str_starts_with($phone, '9')) {
                $phone = '0' . $phone;
            }

            $client = Client::query()
                ->where('username', $this->authUsername)
                ->orWhere('email', $this->authUsername)
                ->orWhere('phone', $this->authUsername)
                ->orWhere('national_code', $this->authUsername);

            if (strlen($phone) === 11 && str_starts_with($phone, '09')) {
                $client->orWhere('phone', $phone)
                       ->orWhere('username', $phone);
            }

            $client = $client->first();

            if ($client && !empty($client->phone)) {
                $phone = $client->phone;
            }
        }

        if (empty($phone) || strlen($phone) !== 11 || !str_starts_with($phone, '09')) {
            $this->authError = 'شماره موبایل معتبر جهت ارسال کد پیامکی یافت نشد. لطفاً شماره موبایل خود را وارد کنید.';
            return;
        }

        $otpLength         = (int) ClientSetting::getValue('auth.otp_length', 5);
        $otpTtl            = (int) ClientSetting::getValue('auth.otp_ttl', 5);
        $otpResendInterval = (int) ClientSetting::getValue('auth.otp_resend_interval', 60);
        $otpMaxRequests    = (int) ClientSetting::getValue('auth.otp_max_requests', 3);

        $otpLength         = max(3, min(10, $otpLength));
        $otpTtl            = max(1, min(60, $otpTtl));
        $otpResendInterval = max(10, min(600, $otpResendInterval));
        $otpMaxRequests    = max(1, min(10, $otpMaxRequests));

        $context = 'login_client';

        if (class_exists(\Modules\Sms\Entities\SmsOtp::class)) {
            // 1) Cooldown check
            $last = \Modules\Sms\Entities\SmsOtp::query()
                ->where('phone', $phone)
                ->where('context', $context)
                ->latest()
                ->first();

            if ($last && $last->created_at && now()->diffInSeconds($last->created_at) < $otpResendInterval) {
                $remain = $otpResendInterval - now()->diffInSeconds($last->created_at);
                $this->authError = "برای ارسال مجدد، {$remain} ثانیه صبر کنید.";
                return;
            }

            // 2) Max requests check
            $windowMinutes = max(5, $otpTtl);
            $recentCount = \Modules\Sms\Entities\SmsOtp::query()
                ->where('phone', $phone)
                ->where('context', $context)
                ->where('created_at', '>=', now()->subMinutes($windowMinutes))
                ->count();

            if ($recentCount >= $otpMaxRequests) {
                $this->authError = 'تعداد درخواست‌های ارسال کد بیش از حد مجاز است. لطفاً کمی بعد دوباره تلاش کنید.';
                return;
            }
        }

        $code = (string) random_int(10 ** ($otpLength - 1), (10 ** $otpLength) - 1);

        if (class_exists(\Modules\Sms\Entities\SmsOtp::class)) {
            \Modules\Sms\Entities\SmsOtp::create([
                'phone'      => $phone,
                'code'       => $code,
                'context'    => $context,
                'client_id'  => $client ? $client->id : null,
                'expires_at' => now()->addMinutes($otpTtl),
                'meta'       => [
                    'username' => $client ? $client->username : (!empty($this->pendingRegistrationData['username']) ? $this->pendingRegistrationData['username'] : $phone),
                    'is_registration' => !empty($this->pendingRegistrationData) || !$client,
                ],
            ]);
        }

        // پترن OTP کلاینت از تنظیمات SMS (سراسری یا آخرین رکورد)
        $patternId = null;
        if (class_exists(\Modules\Sms\Entities\SmsGatewaySetting::class) && \Illuminate\Support\Facades\Schema::hasTable('sms_gateway_settings')) {
            $globalSetting = \Modules\Sms\Entities\SmsGatewaySetting::query()
                ->whereNull('user_id')
                ->whereNotNull('driver')
                ->orderByDesc('id')
                ->first();

            if (! $globalSetting) {
                $globalSetting = \Modules\Sms\Entities\SmsGatewaySetting::query()
                    ->whereNotNull('driver')
                    ->orderByDesc('id')
                    ->first();
            }

            $patternId = data_get($globalSetting, 'config.client_otp_pattern');
        }

        $options = [
            'type'        => \Modules\Sms\Entities\SmsMessage::TYPE_OTP,
            'related_type'=> 'CLIENT',
            'related_id'  => $client ? $client->id : null,
            'meta'        => [
                'context' => $context,
                'otp'     => $code,
            ],
        ];

        $sms = app(\Modules\Sms\Services\SmsManager::class);
        if (!empty($patternId)) {
            $sms->sendPattern($phone, (string) $patternId, [$code], $options);
        } else {
            $sms->sendText($phone, "کد ورود شما: {$code}", $options);
        }

        $this->authStep = 'otp';
        $this->authSuccessMsg = "کد تأیید به شماره {$phone} ارسال شد.";
    }

    public function verifyOtpCode(): void
    {
        $this->authError = '';
        if (empty($this->authOtp)) {
            $this->authError = 'لطفاً کد تأیید را وارد کنید.';
            return;
        }

        $phone = '';
        if (!empty($this->pendingRegistrationData['phone'])) {
            $phone = $this->pendingRegistrationData['phone'];
        } else {
            $phone = preg_replace('/[^0-9]/', '', $this->authUsername);
            if (strlen($phone) === 10 && str_starts_with($phone, '9')) {
                $phone = '0' . $phone;
            }
        }

        $client = null;
        if (empty($this->pendingRegistrationData)) {
            $client = Client::query()
                ->where('username', $this->authUsername)
                ->orWhere('email', $this->authUsername)
                ->orWhere('phone', $this->authUsername)
                ->orWhere('national_code', $this->authUsername);

            if (strlen($phone) === 11 && str_starts_with($phone, '09')) {
                $client->orWhere('phone', $phone)
                       ->orWhere('username', $phone);
            }

            $client = $client->first();

            if ($client && !empty($client->phone)) {
                $phone = $client->phone;
            }
        }

        if (class_exists(\Modules\Sms\Entities\SmsOtp::class)) {
            $otp = \Modules\Sms\Entities\SmsOtp::query()
                ->where('phone', $phone)
                ->where('code', trim($this->authOtp))
                ->where('context', 'login_client')
                ->latest()
                ->first();

            if (!$otp || $otp->isExpired() || $otp->isUsed()) {
                $this->authError = 'کد وارد شده نامعتبر یا منقضی شده است.';
                return;
            }

            $otp->update(['used_at' => now()]);
        }

        if (!empty($this->pendingRegistrationData)) {
            $client = Client::create($this->pendingRegistrationData);
            $this->pendingRegistrationData = [];
            Auth::guard('client')->login($client);
            $this->afterAuthSuccess();
        } elseif ($client) {
            Auth::guard('client')->login($client);
            $this->afterAuthSuccess();
        } else {
            $this->prepareRegistrationForm();
            $this->regInputs['phone'] = $phone;
            $this->authStep = 'register';
        }
    }

    public function resetAuthStep(): void
    {
        $this->pendingRegistrationData = [];
        $this->authStep = 'identifier';
        $this->authError = '';
        $this->authSuccessMsg = '';
    }

    public function backFromOtp(): void
    {
        if (!empty($this->pendingRegistrationData)) {
            $this->authStep = 'register';
            $this->authError = '';
            $this->authSuccessMsg = '';
        } else {
            $this->resetAuthStep();
        }
    }

    public function afterAuthSuccess(): void
    {
        $this->isClientLoggedIn = true;
        $this->showAuthPanel = false;
        $this->authError = '';
        $this->authSuccessMsg = '';

        $engine = app(BotEngineService::class);
        $session = $engine->getOrCreateSession($this->uuid, request()->fullUrl(), [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $session->update([
            'visitor_type' => 'client',
            'visitor_id' => Auth::guard('client')->id(),
        ]);

        $this->loadMessages($session);
        $this->dispatch('chatScrollToBottom');
    }

    // --- End Auth Methods ---

    private function loadMessages($session): void
    {
        $this->messages = [];

        $welcomeMsg = trim((string) BotSetting::getValue('welcome_message', ''));

        // Add welcome message if conversation is empty and welcome message is configured
        if ($session->messages()->count() === 0) {
            if (!empty($welcomeMsg)) {
                $botMsg = $session->messages()->create([
                    'role' => 'bot',
                    'content' => $welcomeMsg,
                    'resolved' => true,
                ]);

                $this->messages[] = [
                    'id' => $botMsg->id,
                    'role' => 'bot',
                    'content' => $welcomeMsg,
                    'answer_type' => 'text',
                    'products' => [],
                    'smart_attachments' => [],
                    'menu_items' => [],
                    'url' => null,
                    'created_at' => $botMsg->created_at->toIso8601String(),
                ];
            }
        } else {
            $resolver = app(EntityResolverService::class);
            foreach ($session->messages()->orderBy('id', 'asc')->get() as $msg) {
                $products = [];
                $menuItems = $msg->metadata['menu_items'] ?? [];
                $smartAttachments = $msg->metadata['smart_attachments'] ?? ($msg->answer ? ($msg->answer->smart_attachments ?? []) : []);
                $url = $msg->metadata['url'] ?? null;
                $answerType = $msg->metadata['answer_type'] ?? ($msg->answer ? $msg->answer->answer_type : 'text');

                if ($answerType === 'product_list') {
                    $entityIds = $msg->answer ? $msg->answer->entity_ids : ($msg->metadata['entity_ids'] ?? []);
                    if (!empty($entityIds)) {
                        $products = $resolver->resolveProducts($entityIds);
                    }
                } elseif ($answerType === 'menu_items' && empty($menuItems) && $msg->answer) {
                    $menuItems = $msg->answer->activeRootMenuItems()->get()->toArray();
                }

                $this->messages[] = [
                    'id' => $msg->id,
                    'role' => $msg->role,
                    'content' => $msg->content,
                    'answer_type' => $answerType,
                    'products' => $products,
                    'smart_attachments' => $smartAttachments,
                    'menu_items' => $menuItems,
                    'url' => $url,
                    'created_at' => $msg->created_at->toIso8601String(),
                ];
            }
        }
    }

    public function sendMessage(?string $overrideText = null): void
    {
        $text = trim($overrideText ?? $this->userMessage);
        if (!$text) return;

        $this->selectedMenuItemId = null;
        $this->selectedMenuItemLabel = null;
        $this->lastUserMessage = $text;

        // Add user message to state instantly
        $this->messages[] = [
            'role' => 'user',
            'content' => $text,
            'answer_type' => 'text',
            'products' => [],
            'menu_items' => [],
            'created_at' => now()->toIso8601String(),
        ];

        $this->userMessage = '';
        $this->isThinking = true;
    }

    public function clickMenuItem(int $menuItemId, string $label): void
    {
        if ($this->isThinking) return;

        $this->selectedMenuItemId = $menuItemId;
        $this->selectedMenuItemLabel = $label;
        $this->lastUserMessage = '';

        // Add user message with clicked button label (Option A)
        $this->messages[] = [
            'role' => 'user',
            'content' => $label,
            'answer_type' => 'text',
            'products' => [],
            'menu_items' => [],
            'created_at' => now()->toIso8601String(),
        ];

        $this->isThinking = true;
    }

    // Handles processing message after user sees thinking state
    public function processMessage(): void
    {
        $engine = app(BotEngineService::class);
        $session = $engine->getOrCreateSession($this->uuid);

        if ($this->selectedMenuItemId && $this->selectedMenuItemLabel) {
            $menuItemId = $this->selectedMenuItemId;
            $userLabel = $this->selectedMenuItemLabel;
            $this->selectedMenuItemId = null;
            $this->selectedMenuItemLabel = null;

            $botReply = $engine->processMenuItemClick($session, $menuItemId, $userLabel);
            if (isset($botReply['user_message_id']) && !empty($this->messages)) {
                $lastIndex = count($this->messages) - 1;
                if (($this->messages[$lastIndex]['role'] ?? '') === 'user') {
                    $this->messages[$lastIndex]['id'] = $botReply['user_message_id'];
                }
            }
            $this->messages[] = $botReply;
        } else {
            $text = $this->lastUserMessage;
            if (!$text) {
                $this->isThinking = false;
                return;
            }

            $botReply = $engine->sendMessage($session, $text);
            if (isset($botReply['user_message_id']) && !empty($this->messages)) {
                $lastIndex = count($this->messages) - 1;
                if (($this->messages[$lastIndex]['role'] ?? '') === 'user') {
                    $this->messages[$lastIndex]['id'] = $botReply['user_message_id'];
                }
            }
            $this->messages[] = $botReply;
            $this->lastUserMessage = '';
        }

        $this->isThinking = false;
        $this->dispatch('chatScrollToBottom');
    }

    public function getProductUrl(array $product, ?array $activeVariant = null): string
    {
        $slug = $product['slug'] ?? null;
        if (!$slug) {
            return '#';
        }

        $variantId = $activeVariant ? ($activeVariant['variant_id'] ?? null) : ($product['variant_id'] ?? null);
        $params = array_filter(['slug' => $slug, 'variant' => $variantId]);

        try {
            if (\Illuminate\Support\Facades\Route::has('market.public.product.show')) {
                return route('market.public.product.show', $params);
            }

            if (\Illuminate\Support\Facades\Route::has('market.product.show')) {
                return route('market.product.show', $params);
            }
        } catch (\Throwable $e) {
            // Fallback
        }

        return url('/shop/product/' . $slug . ($variantId ? '?variant=' . $variantId : ''));
    }

    public function addToCart(int $productId): void
    {
        $resolver = app(EntityResolverService::class);
        $params = $resolver->getAddToCartParams($productId);

        if ($params) {
            // Dispatch event to Market's CartManager
            $this->dispatch('addToCart', variantId: $params['variant_id'], vendorProductId: $params['vendor_product_id'], quantity: 1);
            
            // Mark session as converted in metadata
            $engine = app(BotEngineService::class);
            $session = $engine->getOrCreateSession($this->uuid);
            $meta = $session->metadata ?? [];
            $meta['added_to_cart'] = true;
            $session->update(['metadata' => $meta]);

            $this->dispatch('notify', type: 'success', text: 'محصول با موفقیت به سبد خرید اضافه شد.');
        } else {
            $this->dispatch('notify', type: 'error', text: 'این محصول در حال حاضر غیرفعال یا ناموجود است.');
        }
    }

    public function toggleWidget(): void
    {
        $this->isWidgetOpen = !$this->isWidgetOpen;
        if ($this->isWidgetOpen) {
            $this->dispatch('chatScrollToBottom');
        }
    }

    public function resetSession(): void
    {
        $this->uuid = \Illuminate\Support\Str::uuid()->toString();
        \Illuminate\Support\Facades\Session::put('smartbot_session_uuid', $this->uuid);
        
        $engine = app(BotEngineService::class);
        $session = $engine->getOrCreateSession($this->uuid, request()->fullUrl(), [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->loadMessages($session);
        $this->userMessage = '';
        $this->isThinking = false;
        
        $this->dispatch('chatScrollToBottom');
    }

    public function showVariantCard(int $productId, string $productTitle, ?string $messageKey = null): void
    {
        if ($this->assistantLevel !== 2) {
            return;
        }

        $openedKey = $messageKey ? ($messageKey . '-' . $productId) : (string)$productId;

        if (!empty($this->variantCardOpenedForProducts[$openedKey])) {
            $this->dispatch('chatScrollToBottom');
            return;
        }

        if (empty($this->expandedVariants[$productId])) {
            $this->autoResolveProductVariations([
                ['id' => $productId, 'has_variations' => true]
            ]);
        }

        $productData = $this->findProductInMessages($productId);

        $this->messages[] = [
            'id' => 'variant-card-' . $productId . '-' . time(),
            'role' => 'bot',
            'content' => "تنوع‌های موجود برای «{$productTitle}» را انتخاب کنید:",
            'answer_type' => 'variant_card',
            'products' => $productData ? [$productData] : [],
            'created_at' => now()->toIso8601String(),
            'parent_message_key' => $messageKey,
        ];

        $this->variantCardOpenedForProducts[$openedKey] = true;
        $this->dispatch('chatScrollToBottom');
    }

    public function removeMessage(string $messageId): void
    {
        $parentKey = null;
        $productId = null;

        foreach ($this->messages as $msg) {
            if (($msg['id'] ?? '') === $messageId) {
                $parentKey = $msg['parent_message_key'] ?? null;
                $productId = !empty($msg['products']) ? ($msg['products'][0]['id'] ?? null) : null;
                break;
            }
        }

        if ($parentKey !== null && $productId !== null) {
            $openedKey = $parentKey . '-' . $productId;
            unset($this->variantCardOpenedForProducts[$openedKey]);
        }

        $this->messages = array_values(array_filter($this->messages, function($msg) use ($messageId) {
            return ($msg['id'] ?? '') !== $messageId;
        }));
    }

    public function goBackStep(mixed $messageId): void
    {
        $targetIndex = null;
        foreach ($this->messages as $index => $msg) {
            if ((string) ($msg['id'] ?? '') === (string) $messageId || $index == $messageId) {
                $targetIndex = $index;
                break;
            }
        }

        if ($targetIndex !== null) {
            // Target message and its preceding user message (if any) + all messages after targetIndex should be removed
            $startDeleteIndex = $targetIndex;
            if ($targetIndex > 0 && ($this->messages[$targetIndex - 1]['role'] ?? '') === 'user') {
                $startDeleteIndex = $targetIndex - 1;
            }

            $messagesToDelete = array_slice($this->messages, $startDeleteIndex);
            $dbIdsToDelete = [];
            foreach ($messagesToDelete as $m) {
                if (!empty($m['id']) && is_numeric($m['id'])) {
                    $dbIdsToDelete[] = (int) $m['id'];
                }
            }

            // Remove from Database if session exists
            if (!empty($this->uuid)) {
                $session = \Modules\SmartBot\App\Models\BotSession::where('session_uuid', $this->uuid)->first();
                if ($session) {
                    if (!empty($dbIdsToDelete)) {
                        \Modules\SmartBot\App\Models\BotMessage::where('session_id', $session->id)
                            ->whereIn('id', $dbIdsToDelete)
                            ->delete();
                    }

                    // Fallback to guarantee no orphaned records remain if DB ID was missed
                    $targetDbId = $this->messages[$targetIndex]['id'] ?? null;
                    if ($targetDbId && is_numeric($targetDbId)) {
                        \Modules\SmartBot\App\Models\BotMessage::where('session_id', $session->id)
                            ->where('id', '>=', (int) $targetDbId - 1)
                            ->delete();
                    }
                }
            }

            $this->messages = array_slice($this->messages, 0, $startDeleteIndex);
        }

        // If no user messages left or empty, reset session so home state is clean
        $hasRemainingUserMessages = false;
        foreach ($this->messages as $m) {
            if (($m['role'] ?? '') === 'user') {
                $hasRemainingUserMessages = true;
                break;
            }
        }

        if (!$hasRemainingUserMessages) {
            if (!empty($this->uuid)) {
                $session = \Modules\SmartBot\App\Models\BotSession::where('session_uuid', $this->uuid)->first();
                if ($session) {
                    $session->messages()->delete();
                }
            }
            $this->resetSession();
            return;
        }

        $this->dispatch('chatScrollToBottom');
    }

    private function findProductInMessages(int $productId): ?array
    {
        foreach (array_reverse($this->messages) as $msg) {
            foreach ($msg['products'] ?? [] as $product) {
                if ((int) ($product['id'] ?? 0) === $productId) {
                    return $product;
                }
            }
        }
        return null;
    }

    public function autoResolveProductVariations(array $products): void
    {
        foreach ($products as $product) {
            $productId = (int) $product['id'];
            if (!empty($product['has_variations']) && empty($this->expandedVariants[$productId])) {
                $resolver = app(EntityResolverService::class);
                $variants = $resolver->resolveProductVariants($productId);
                
                // Get available attributes
                $availableAttributes = [];
                foreach ($variants as $variant) {
                    $attrs = $variant['attributes'] ?? [];
                    foreach ($attrs as $key => $val) {
                        if (in_array($key, ['name', 'نام']) && $val === 'استاندارد') {
                            continue;
                        }
                        if (!isset($availableAttributes[$key])) {
                            $availableAttributes[$key] = [];
                        }
                        if (!in_array($val, $availableAttributes[$key])) {
                            $availableAttributes[$key][] = $val;
                        }
                    }
                }

                // Default selection: cheapest variant with stock, or just first variant
                $defaultVariant = collect($variants)->firstWhere('has_stock', true) ?? collect($variants)->first();
                $defaultSelection = [];
                if ($defaultVariant && !empty($defaultVariant['attributes'])) {
                    foreach ($defaultVariant['attributes'] as $k => $v) {
                        $defaultSelection[$k] = $v;
                    }
                }

                $this->expandedVariants[$productId] = [
                    'variants' => $variants,
                    'available_attributes' => $availableAttributes,
                    'selected_variant_id' => $defaultVariant ? $defaultVariant['variant_id'] : null,
                ];

                $this->selectedProductAttributes[$productId] = $defaultSelection;
            }
        }
    }

    public function selectAttribute(int $productId, string $attrKey, string $value): void
    {
        if ($this->assistantLevel !== 2) {
            return;
        }

        // Update selection
        $this->selectedProductAttributes[$productId][$attrKey] = $value;

        // Try to match the selected combination with available variants
        $selection = $this->selectedProductAttributes[$productId];
        $variants = $this->expandedVariants[$productId]['variants'] ?? [];

        $matchedVariant = null;
        foreach ($variants as $variant) {
            $attrs = $variant['attributes'] ?? [];
            $allMatch = true;
            foreach ($selection as $k => $v) {
                if (($attrs[$k] ?? null) !== $v) {
                    $allMatch = false;
                    break;
                }
            }
            if ($allMatch) {
                $matchedVariant = $variant;
                break;
            }
        }

        if ($matchedVariant) {
            $this->expandedVariants[$productId]['selected_variant_id'] = $matchedVariant['variant_id'];
        }
    }

    public function render()
    {
        $attributeDictionary = collect();
        if (class_exists('Modules\Market\Entities\MarketAttribute')) {
            $attributeDictionary = \Modules\Market\Entities\MarketAttribute::with('values')->get();
        }

        return view('smartbot::livewire.widget.chat-widget', [
            'attributeDictionary' => $attributeDictionary,
        ]);
    }
}
