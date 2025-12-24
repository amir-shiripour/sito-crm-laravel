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
    public function showLoginForm()
    {
        $mode        = ClientSetting::getValue('auth.mode', 'password');        // password|otp|both
        $defaultLogin= ClientSetting::getValue('auth.default', 'password');     // password|otp

        $otpLength         = (int) ClientSetting::getValue('auth.otp_length', 5);
        $otpTtl            = (int) ClientSetting::getValue('auth.otp_ttl', 5);
        $otpResendInterval = (int) ClientSetting::getValue('auth.otp_resend_interval', 60);
        $otpMaxRequests    = (int) ClientSetting::getValue('auth.otp_max_requests', 3);

        // اگر sms نیست، اجباراً password
        $smsAvailable = class_exists(\Modules\Sms\Services\SmsManager::class);
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

        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'نام کاربری الزامی است.',
            'password.required' => 'رمز عبور الزامی است.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('client')->attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ], $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('client.dashboard'));
        }

        return back()
            ->withErrors(['username' => 'نام کاربری یا رمز عبور نادرست است.'])
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
        $data = $request->validate([
            'username' => ['required', 'string'],
        ]);

        $client = Client::query()
            ->where('username', $data['username'])
            ->first();

        if (! $client || empty($client->phone)) {
            return response()->json([
                'success' => false,
                'message' => 'نام کاربری معتبر نیست یا شماره موبایل ثبت نشده است.',
            ], 422);
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
        $phone   = $client->phone;

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
        if (class_exists(SmsGatewaySetting::class)) {
            $globalSetting = SmsGatewaySetting::query()->orderByDesc('id')->first();
            $patternId = data_get($globalSetting, 'config.client_otp_pattern');
        }

        $options = [
            'type'        => SmsMessage::TYPE_OTP,
            'related_type'=> 'CLIENT',
            'related_id'  => $client->id,
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
            'client_id'  => $client->id,
            'expires_at' => now()->addMinutes($otpTtl),
            'meta'       => [
                'username' => $client->username,
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
        $data = $request->validate([
            'username' => ['required', 'string'],
            'code'     => ['required', 'string', 'max:20'],
        ]);

        $client = Client::query()
            ->where('username', $data['username'])
            ->first();

        if (! $client || empty($client->phone)) {
            return response()->json([
                'success' => false,
                'message' => 'نام کاربری معتبر نیست.',
            ], 422);
        }

        $context = 'login_client';

        $otp = SmsOtp::query()
            ->where('phone', $client->phone)
            ->where('client_id', $client->id)
            ->where('context', $context)
            ->where('code', $data['code'])
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
}
