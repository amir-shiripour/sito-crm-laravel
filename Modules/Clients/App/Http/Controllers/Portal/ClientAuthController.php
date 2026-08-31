<?php

namespace Modules\Clients\App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Clients\Entities\Client;
use Modules\Clients\Entities\ClientSetting;
use Modules\Sms\Entities\SmsOtp;
use Modules\Sms\Services\SmsManager;
use Modules\Sms\Entities\SmsGatewaySetting;
use Modules\Sms\Entities\SmsMessage;

class ClientAuthController extends Controller
{
    public static function toEnglishNumbers(?string $string): string
    {
        if ($string === null || $string === '') {
            return '';
        }

        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $converted = str_replace($persian, $english, $string);
        return str_replace($arabic, $english, $converted);
    }

    public static function normalizePhoneNumber(?string $phone): string
    {
        if ($phone === null || $phone === '') {
            return '';
        }

        $phone = self::toEnglishNumbers($phone);
        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (empty($digits)) {
            return '';
        }

        if (str_starts_with($digits, '0098') && strlen($digits) === 14) {
            $digits = '0' . substr($digits, 4);
        } elseif (str_starts_with($digits, '98') && strlen($digits) === 12) {
            $digits = '0' . substr($digits, 2);
        } elseif (str_starts_with($digits, '9') && strlen($digits) === 10) {
            $digits = '0' . $digits;
        }

        return $digits;
    }

    public function showLoginForm()
    {
        $mode        = ClientSetting::getValue('auth.mode', 'password');        // password|otp|both
        $defaultLogin= ClientSetting::getValue('auth.default', 'password');     // password|otp

        $otpLength         = (int) ClientSetting::getValue('auth.otp_length', 5);
        $otpTtl            = (int) ClientSetting::getValue('auth.otp_ttl', 5);
        $otpResendInterval = (int) ClientSetting::getValue('auth.otp_resend_interval', 60);
        $otpMaxRequests    = (int) ClientSetting::getValue('auth.otp_max_requests', 3);

        // اگر sms نیست، اجباراً password
        $smsAvailable = class_exists(\Modules\Sms\Services\SmsManager::class)
            && \Illuminate\Support\Facades\Schema::hasTable('sms_gateway_settings');
        if (! $smsAvailable) {
            $mode = 'password';
            $defaultLogin = 'password';
        }

        return view('clients::portal.auth.login', compact(
            'mode',
            'defaultLogin',
            'otpLength',
            'otpTtl',
            'otpResendInterval',
            'otpMaxRequests',
            'smsAvailable'
        ));
    }

    public function login(Request $request)
    {
        $mode = ClientSetting::getValue('auth.mode', 'password');
        if ($mode === 'otp') {
            return back()->withErrors(['username' => 'ورود با رمز عبور غیرفعال است. لطفاً ورود با OTP را انتخاب کنید.'])
                ->onlyInput('username');
        }

        $strategy = ClientSetting::getValue('username_strategy', 'email_local');
        $usernameLabel = match ($strategy) {
            'mobile' => 'شماره موبایل',
            'email_local', 'email' => 'ایمیل',
            'national_code' => 'کد ملی',
            default => 'نام کاربری',
        };

        if ($request->has('username')) {
            $request->merge([
                'username' => trim(self::toEnglishNumbers($request->input('username'))),
            ]);
        }

        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => "{$usernameLabel} الزامی است.",
            'password.required' => 'رمز عبور الزامی است.',
        ]);

        $remember = $request->boolean('remember');
        $cleanUsername = $credentials['username'];
        $normalizedPhone = self::normalizePhoneNumber($cleanUsername);

        // بررسی اینکه آیا کاربر وجود دارد (با یوزرنیم، ایمیل، موبایل یا کدملی)
        $clientQuery = Client::query()
            ->where('username', $cleanUsername)
            ->orWhere('email', $cleanUsername)
            ->orWhere('phone', $cleanUsername)
            ->orWhere('national_code', $cleanUsername);

        if (strlen($normalizedPhone) === 11 && str_starts_with($normalizedPhone, '09')) {
            $clientQuery->orWhere('phone', $normalizedPhone)
                        ->orWhere('username', $normalizedPhone);
        }

        $client = $clientQuery->first();

        if ($client) {
            if (Auth::guard('client')->attempt([
                'username' => $client->username,
                'password' => $credentials['password'],
            ], $remember)) {
                $request->session()->regenerate();
                return redirect()->intended(route('client.dashboard'));
            }

            return back()
                ->withErrors(['username' => "{$usernameLabel} یا رمز عبور نادرست است."])
                ->onlyInput('username');
        }

        // اگر کاربر وجود نداشت و ثبت‌نام فعال بود
        $registerEnabled = (bool) ClientSetting::getValue('auth.register_enabled', false);
        if ($registerEnabled) {
            return back()
                ->with('register_mode', true)
                ->with('register_username', $credentials['username'])
                ->with('register_password', $credentials['password'])
                ->with('register_alert', 'حساب کاربری یافت نشد. می‌توانید با تکمیل اطلاعات زیر ثبت‌نام کنید.')
                ->withInput();
        }

        return back()
            ->withErrors(['username' => "{$usernameLabel} یا رمز عبور نادرست است."])
            ->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::guard('client')->logout();

//        $request->session()->invalidate();
//        $request->session()->regenerateToken();

        return redirect()->route('client.login');
    }

    public function autoLoginFromAdmin(Request $request, Client $client)
    {
        // اختیاری: چک دسترسی ادمین
        // $this->authorize('view', $client);

        Auth::guard('client')->logout();
        Auth::guard('client')->login($client);

        return redirect()->route('client.dashboard');
    }

    public function sendOtp(Request $request, SmsManager $sms)
    {
        if ($request->has('username')) {
            $request->merge([
                'username' => trim(self::toEnglishNumbers($request->input('username'))),
            ]);
        }

        $data = $request->validate([
            'username' => ['required', 'string'],
        ]);

        $cleanUsername = $data['username'];
        $normalizedPhone = self::normalizePhoneNumber($cleanUsername);

        // بررسی اینکه آیا کاربر وجود دارد (با یوزرنیم، ایمیل، موبایل یا کدملی)
        $clientQuery = Client::query()
            ->where('username', $cleanUsername)
            ->orWhere('email', $cleanUsername)
            ->orWhere('phone', $cleanUsername)
            ->orWhere('national_code', $cleanUsername);

        if (strlen($normalizedPhone) === 11 && str_starts_with($normalizedPhone, '09')) {
            $clientQuery->orWhere('phone', $normalizedPhone)
                        ->orWhere('username', $normalizedPhone);
        }

        $client = $clientQuery->first();

        $strategy = ClientSetting::getValue('username_strategy', 'email_local');
        $usernameLabel = match ($strategy) {
            'mobile' => 'شماره موبایل',
            'email_local', 'email' => 'ایمیل',
            'national_code' => 'کد ملی',
            default => 'نام کاربری',
        };

        $registerEnabled = (bool) ClientSetting::getValue('auth.register_enabled', false);
        $isRegister = false;

        if (! $client) {
            if (!$registerEnabled) {
                return response()->json([
                    'success' => false,
                    'message' => "{$usernameLabel} معتبر نیست یا شماره موبایل ثبت نشده است.",
                ], 422);
            }

            // بررسی شماره تلفن معتبر در ایران
            $phone = $normalizedPhone;

            if (strlen($phone) !== 11 || !str_starts_with($phone, '09')) {
                return response()->json([
                    'success' => false,
                    'message' => 'حساب کاربری یافت نشد. برای ثبت‌نام با پیامک، لطفا شماره موبایل معتبر خود را وارد کنید.',
                ], 422);
            }

            if (Client::where('phone', $phone)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'کاربری با این شماره موبایل قبلاً ثبت‌نام شده است.',
                ], 422);
            }

            $isRegister = true;
        } else {
            $phone = self::normalizePhoneNumber($client->phone);
            if (empty($phone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'شماره موبایل برای این حساب ثبت نشده است.',
                ], 422);
            }
        }

        $otpLength         = (int) ClientSetting::getValue('auth.otp_length', 5);
        $otpTtl            = (int) ClientSetting::getValue('auth.otp_ttl', 5);
        $otpResendInterval = (int) ClientSetting::getValue('auth.otp_resend_interval', 60);
        $otpMaxRequests    = (int) ClientSetting::getValue('auth.otp_max_requests', 3);

        $otpLength = max(3, min(10, $otpLength));
        $otpTtl = max(1, min(60, $otpTtl));
        $otpResendInterval = max(10, min(600, $otpResendInterval));
        $otpMaxRequests = max(1, min(10, $otpMaxRequests));

        $context = 'login_client';

        // 1) محدودیت ارسال مجدد (cooldown)
        $last = SmsOtp::query()
            ->where('phone', $phone)
            ->where('context', $context)
            ->latest()
            ->first();

        if ($last && $last->created_at && now()->diffInSeconds($last->created_at) < $otpResendInterval) {
            $remain = $otpResendInterval - now()->diffInSeconds($last->created_at);
            return response()->json([
                'success' => false,
                'message' => "برای ارسال مجدد، {$remain} ثانیه صبر کنید.",
                'resend_in' => $remain,
            ], 429);
        }

        // 2) محدودیت تعداد درخواست‌ها (در یک بازه کوتاه)
        $windowMinutes = max(5, $otpTtl);
        $recentCount = SmsOtp::query()
            ->where('phone', $phone)
            ->where('context', $context)
            ->where('created_at', '>=', now()->subMinutes($windowMinutes))
            ->count();

        if ($recentCount >= $otpMaxRequests) {
            return response()->json([
                'success' => false,
                'message' => 'تعداد درخواست‌های ارسال کد بیش از حد مجاز است. کمی بعد دوباره تلاش کنید.',
            ], 429);
        }

        // تولید کد
        $code = (string) random_int(10 ** ($otpLength - 1), (10 ** $otpLength) - 1);

        // پترن OTP کلاینت از تنظیمات SMS (آخرین رکورد)
        $patternId = null;
        if (class_exists(SmsGatewaySetting::class) && \Illuminate\Support\Facades\Schema::hasTable('sms_gateway_settings')) {
            $globalSetting = SmsGatewaySetting::query()->orderByDesc('id')->first();
            $patternId = data_get($globalSetting, 'config.client_otp_pattern');
        }

        $options = [
            'type'        => SmsMessage::TYPE_OTP,
            'related_type'=> 'CLIENT',
            'related_id'  => $client ? $client->id : null,
            'meta'        => [
                'context' => $context,
                'otp'     => $code,
            ],
        ];

        // اگر پترن ست شده → ارسال پترنی (ReplaceToken = [code])
        if (!empty($patternId)) {
            $sms->sendPattern($phone, (string) $patternId, [$code], $options);
        } else {
            // بدون پترن → متن ساده
            $sms->sendText($phone, "کد ورود شما: {$code}", $options);
        }

        SmsOtp::create([
            'phone'      => $phone,
            'code'       => $code,
            'context'    => $context,
            'client_id'  => $client ? $client->id : null,
            'expires_at' => now()->addMinutes($otpTtl),
            'meta'       => [
                'username' => $client ? $client->username : $phone,
                'is_registration' => $isRegister,
            ],
        ]);

        return response()->json([
            'success' => true,
            'expires_in' => $otpTtl * 60,
            'resend_in'  => $otpResendInterval,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        if ($request->has('username')) {
            $request->merge([
                'username' => trim(self::toEnglishNumbers($request->input('username'))),
            ]);
        }
        if ($request->has('code')) {
            $request->merge([
                'code' => trim(self::toEnglishNumbers($request->input('code'))),
            ]);
        }

        $data = $request->validate([
            'username' => ['required', 'string'],
            'code'     => ['required', 'string', 'max:20'],
        ]);

        $cleanUsername = $data['username'];
        $cleanCode = $data['code'];
        $normalizedPhone = self::normalizePhoneNumber($cleanUsername);

        $clientQuery = Client::query()
            ->where('username', $cleanUsername)
            ->orWhere('email', $cleanUsername)
            ->orWhere('phone', $cleanUsername)
            ->orWhere('national_code', $cleanUsername);

        if (strlen($normalizedPhone) === 11 && str_starts_with($normalizedPhone, '09')) {
            $clientQuery->orWhere('phone', $normalizedPhone)
                        ->orWhere('username', $normalizedPhone);
        }

        $client = $clientQuery->first();

        $strategy = ClientSetting::getValue('username_strategy', 'email_local');
        $usernameLabel = match ($strategy) {
            'mobile' => 'شماره موبایل',
            'email_local', 'email' => 'ایمیل',
            'national_code' => 'کد ملی',
            default => 'نام کاربری',
        };

        $registerEnabled = (bool) ClientSetting::getValue('auth.register_enabled', false);

        if (! $client) {
            if (!$registerEnabled) {
                return response()->json([
                    'success' => false,
                    'message' => "{$usernameLabel} معتبر نیست.",
                ], 422);
            }

            $phone = $normalizedPhone;

            $context = 'login_client';

            $otp = SmsOtp::query()
                ->where('phone', $phone)
                ->whereNull('client_id')
                ->where('context', $context)
                ->where('code', $cleanCode)
                ->latest()
                ->first();

            if (! $otp || $otp->isExpired() || $otp->isUsed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'کد نامعتبر است یا منقضی شده است.',
                ], 422);
            }

            $otp->update(['used_at' => now()]);

            return response()->json([
                'success' => true,
                'register_mode' => true,
                'phone' => $phone,
                'username' => $phone,
            ]);
        }

        $context = 'login_client';
        $clientPhone = self::normalizePhoneNumber($client->phone);

        $otp = SmsOtp::query()
            ->where('phone', $clientPhone)
            ->where('client_id', $client->id)
            ->where('context', $context)
            ->where('code', $cleanCode)
            ->latest()
            ->first();

        if (! $otp || $otp->isExpired() || $otp->isUsed()) {
            return response()->json([
                'success' => false,
                'message' => 'کد نامعتبر است یا منقضی شده است.',
            ], 422);
        }

        $otp->update(['used_at' => now()]);

        Auth::guard('client')->login($client);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'redirect' => route('client.dashboard'),
        ]);
    }

    public function register(Request $request)
    {
        $registerEnabled = (bool) ClientSetting::getValue('auth.register_enabled', false);
        if (!$registerEnabled) {
            abort(403, 'ثبت‌نام غیرفعال است.');
        }

        // Convert Persian/Arabic digits in request inputs before validation
        $inputsToConvert = ['username', 'phone', 'national_code', 'case_number'];
        $merged = [];
        foreach ($inputsToConvert as $key) {
            if ($request->has($key)) {
                $merged[$key] = trim(self::toEnglishNumbers($request->input($key)));
            }
        }
        if (!empty($merged)) {
            $request->merge($merged);
        }

        $activeForm = \Modules\Clients\Entities\ClientForm::active();
        $fields = $activeForm ? ($activeForm->schema['fields'] ?? []) : [];
        $regFields = collect($fields)->where('show_in_registration', true);

        $rules = [
            'username' => ['required', 'string'],
        ];
        
        $messages = [
            'username.required' => 'نام کاربری الزامی است.',
        ];

        $viaOtp = $request->boolean('via_otp');

        if ($viaOtp) {
            $rules['phone'] = ['required', 'string'];
        } else {
            $rules['password'] = ['required', 'string', 'min:6'];
            $messages['password.required'] = 'رمز عبور الزامی است.';
            $messages['password.min'] = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
        }

        foreach ($regFields as $field) {
            $fid = $field['id'];
            if ($fid === 'username' || $fid === 'password' || ($viaOtp && $fid === 'phone')) {
                continue;
            }

            $fieldRules = [];
            if (!empty($field['required'])) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            if ($field['type'] === 'email') {
                $fieldRules[] = 'email';
                if ($fid === 'email') {
                    $fieldRules[] = 'unique:clients,email';
                }
            } elseif ($field['type'] === 'number') {
                $fieldRules[] = 'numeric';
            }

            if ($fid === 'phone') {
                $fieldRules[] = 'unique:clients,phone';
            } elseif ($fid === 'national_code') {
                $fieldRules[] = 'unique:clients,national_code';
            }

            $rules[$fid] = $fieldRules;
            $messages["{$fid}.required"] = "فیلد {$field['label']} الزامی است.";
            $messages["{$fid}.unique"] = "این {$field['label']} قبلاً در سیستم ثبت شده است.";
        }

        $validated = $request->validate($rules, $messages);

        $strategy = ClientSetting::getValue('username_strategy', 'email_local');
        
        $phoneVal = $validated['phone'] ?? $request->input('phone');
        $emailVal = $validated['email'] ?? $request->input('email');
        $nationalCodeVal = $validated['national_code'] ?? $request->input('national_code');

        if (!empty($phoneVal)) {
            $phoneVal = self::normalizePhoneNumber($phoneVal);
        }
        if (!empty($nationalCodeVal)) {
            $nationalCodeVal = trim(self::toEnglishNumbers($nationalCodeVal));
        }

        // مپ خودکار نام کاربری به فیلد اصلی استراتژی در صورت خالی بودن
        if ($strategy === 'mobile' && empty($phoneVal)) {
            $phoneVal = self::normalizePhoneNumber($request->input('username'));
        }
        if (($strategy === 'email_local' || $strategy === 'email') && empty($emailVal)) {
            $emailVal = $request->input('username');
        }
        if ($strategy === 'national_code' && empty($nationalCodeVal)) {
            $nationalCodeVal = trim(self::toEnglishNumbers($request->input('username')));
        }

        if ($strategy === 'mobile' && empty($phoneVal)) {
            return back()->withErrors(['phone' => 'شماره موبایل برای ثبت‌نام الزامی است.'])->withInput();
        }
        if (($strategy === 'email_local' || $strategy === 'email') && empty($emailVal)) {
            return back()->withErrors(['email' => 'ایمیل برای ثبت‌نام الزامی است.'])->withInput();
        }
        if ($strategy === 'national_code' && empty($nationalCodeVal)) {
            return back()->withErrors(['national_code' => 'کد ملی برای ثبت‌نام الزامی است.'])->withInput();
        }

        $username = $this->generateUsername([
            'phone' => $phoneVal,
            'email' => $emailVal,
            'national_code' => $nationalCodeVal,
        ]);

        if (Client::where('username', $username)->exists()) {
            return back()->withErrors(['username' => 'نام کاربری قبلاً انتخاب شده است.'])->withInput();
        }

        $clientData = [
            'username' => $username,
            'full_name' => $validated['full_name'] ?? $request->input('full_name') ?? 'کاربر ثبت‌نامی',
            'email' => $emailVal,
            'phone' => $phoneVal,
            'national_code' => $nationalCodeVal,
            'case_number' => $validated['case_number'] ?? $request->input('case_number'),
            'notes' => $validated['notes'] ?? $request->input('notes'),
            'status_id' => \Modules\Clients\Entities\ClientStatus::active()->orderBy('sort_order')->first()?->id,
            'created_by' => null,
        ];

        if ($viaOtp) {
            $clientData['password'] = Hash::make(\Illuminate\Support\Str::random(12));
        } else {
            $clientData['password'] = Hash::make($validated['password']);
        }

        $meta = [];
        foreach ($regFields as $field) {
            $fid = $field['id'];
            if (\Modules\Clients\Entities\ClientForm::isSystemFieldId($fid)) {
                continue;
            }
            if ($request->has($fid)) {
                $meta[$fid] = $request->input($fid);
            }
        }
        $clientData['meta'] = $meta;

        $client = Client::create($clientData);

        Auth::guard('client')->login($client);
        $request->session()->regenerate();

        return redirect()->route('client.dashboard');
    }

    public function checkUsername(Request $request)
    {
        if ($request->has('username')) {
            $request->merge([
                'username' => trim(self::toEnglishNumbers($request->input('username'))),
            ]);
        }

        $data = $request->validate([
            'username' => ['required', 'string'],
        ]);

        $username = $data['username'];
        $normalizedPhone = self::normalizePhoneNumber($username);

        $clientQuery = Client::query()
            ->where('username', $username)
            ->orWhere('email', $username)
            ->orWhere('phone', $username)
            ->orWhere('national_code', $username);

        if (strlen($normalizedPhone) === 11 && str_starts_with($normalizedPhone, '09')) {
            $clientQuery->orWhere('phone', $normalizedPhone)
                        ->orWhere('username', $normalizedPhone);
        }

        $client = $clientQuery->first();
        $registerEnabled = (bool) ClientSetting::getValue('auth.register_enabled', false);

        if ($client) {
            return response()->json([
                'success' => true,
                'exists' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'exists' => false,
            'register_enabled' => $registerEnabled,
        ]);
    }

    protected function generateUsername(array $data): string
    {
        $strategy = ClientSetting::getValue('username_strategy', 'email_local');
        $base = '';
        switch ($strategy) {
            case 'mobile': $base = $data['phone'] ?? ''; break;
            case 'national_code': $base = $data['national_code'] ?? ''; break;
            case 'email_local': $base = explode('@', $data['email'] ?? '')[0]; break;
            default: $base = 'user_' . Str::random(6);
        }
        $base = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $base);
        if (empty($base)) $base = 'user_' . Str::random(8);
        if ($strategy === 'mobile') return $base;
        $username = $base;
        $counter = 1;
        while (Client::where('username', $username)->exists()) {
            $username = $base . '_' . $counter++;
        }
        return $username;
    }
}
