<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Modules\Settings\Entities\Setting;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $gateway;
    protected $merchantId;
    protected $terminalId;
    protected $username;
    protected $password;
    protected $sandbox;
    protected $callbackUrl;
    protected $currency;

    public function __construct(?string $gateway = 'zarinpal')
    {
        $this->gateway = $gateway ?: 'zarinpal';
        $this->loadGatewaySettings();
    }

    protected function loadGatewaySettings()
    {
        // Load settings from the database
        // نکته برای آینده: بهتر است این قسمت Cache شود تا پرفورمنس دیتابیس افت نکند
        $settings = Setting::all()->pluck('value', 'key');

        $this->currency = $settings['payment_currency'] ?? 'toman';

        if ($this->gateway === 'zarinpal') {
            $this->merchantId = $settings['zarinpal_merchant_id'] ?? null;
            $this->sandbox = filter_var($settings['zarinpal_sandbox'] ?? false, FILTER_VALIDATE_BOOLEAN);
            // This callback URL should be dynamic based on the actual payment initiation
            // For now, we'll use a placeholder and expect it to be passed during payment request
            $this->callbackUrl = route('settings.payment.verify', ['gateway' => 'zarinpal']);
        } elseif ($this->gateway === 'zibal') {
            $this->merchantId = $settings['zibal_merchant_id'] ?? null;
            // Zibal uses 'zibal' as merchant ID for sandbox, no separate sandbox flag needed
            $this->sandbox = ($this->merchantId === 'zibal'); // Set sandbox true if merchantId is 'zibal'
            $this->callbackUrl = route('settings.payment.verify', ['gateway' => 'zibal']);
        } elseif ($this->gateway === 'behpardakht') {
            $this->terminalId = $settings['behpardakht_terminal_id'] ?? null;
            $this->username = $settings['behpardakht_username'] ?? null;
            $this->password = $settings['behpardakht_password'] ?? null;
            $this->callbackUrl = route('settings.payment.verify', ['gateway' => 'behpardakht']);
        }
        // Add other gateways here
    }

    /**
     * Get the amount in Rials. Zarinpal and Zibal expect Rials.
     */
    protected function getAmountInRials(float $amount): int
    {
        // If system currency is toman, multiply by 10 to get rials.
        // If it's already rials, return as is.
        if ($this->currency === 'toman') {
            return (int) ($amount * 10);
        }

        return (int) $amount;
    }

    public function requestPayment(float $amount, string $description, string $userEmail = null, string $userMobile = null, string $callbackUrl = null)
    {
        if ($this->gateway === 'zarinpal') {
            return $this->requestZarinpalPayment($amount, $description, $userEmail, $userMobile, $callbackUrl);
        } elseif ($this->gateway === 'zibal') {
            return $this->requestZibalPayment($amount, $description, $userEmail, $userMobile, $callbackUrl);
        } elseif ($this->gateway === 'behpardakht') {
            return $this->requestBehpardakhtPayment($amount, $description, $userEmail, $userMobile, $callbackUrl);
        }
        // Add other gateways here
        throw new \Exception("Payment gateway {$this->gateway} not supported.");
    }

    /**
     * مبلغ از قبل به ریال است - بدون تبدیل مجدد ارسال می‌شود.
     * این متد برای ماژول Booking که مبلغ را از پیش به ریال ذخیره کرده استفاده می‌شود.
     */
    public function requestPaymentInRials(int $amountInRials, string $description, string $userEmail = null, string $userMobile = null, string $callbackUrl = null)
    {
        if ($this->gateway === 'zarinpal') {
            return $this->requestZarinpalPaymentRaw($amountInRials, $description, $userEmail, $userMobile, $callbackUrl);
        } elseif ($this->gateway === 'zibal') {
            return $this->requestZibalPaymentRaw($amountInRials, $description, $userEmail, $userMobile, $callbackUrl);
        } elseif ($this->gateway === 'behpardakht') {
            return $this->requestBehpardakhtPaymentRaw($amountInRials, $description, $userEmail, $userMobile, $callbackUrl);
        }
        throw new \Exception("Payment gateway {$this->gateway} not supported.");
    }

    public function verifyPayment(array $data)
    {
        if ($this->gateway === 'zarinpal') {
            return $this->verifyZarinpalPayment($data);
        } elseif ($this->gateway === 'zibal') {
            return $this->verifyZibalPayment($data);
        } elseif ($this->gateway === 'behpardakht') {
            return $this->verifyBehpardakhtPayment($data);
        }
        // Add other gateways here
        throw new \Exception("Payment gateway {$this->gateway} not supported.");
    }

    protected function requestZarinpalPayment(float $amount, string $description, string $userEmail = null, string $userMobile = null, string $callbackUrl = null, bool $alreadyInRials = false)
    {
        if (!$this->merchantId) {
            Log::error('Zarinpal Error: Merchant ID is empty or not set in settings.');
            throw new \Exception("کد مرچنت زرین‌پال تنظیم نشده است.");
        }

        // Zarinpal v4 API URLs
        $url = $this->sandbox
            ? 'https://sandbox.zarinpal.com/pg/v4/payment/request.json'
            : 'https://api.zarinpal.com/pg/v4/payment/request.json';

        // اگر مبلغ از قبل به ریال است، تبدیل نکن
        $amountInRials = $alreadyInRials ? (int) $amount : $this->getAmountInRials($amount);

        $data = [
            'merchant_id'  => $this->merchantId,
            'amount'       => $amountInRials,
            'description'  => $description,
            'callback_url' => $callbackUrl ?? $this->callbackUrl,
            'metadata'     => [
                'email'  => $userEmail ?? 'info@example.com', // fallback email to avoid validation errors if empty
                'mobile' => $userMobile ?? '09120000000', // fallback mobile
            ]
        ];

        Log::info('Zarinpal Request URL: ' . $url);
        Log::info('Zarinpal Request Data:', $data);

        try {
            // اضافه شدن timeout(30) و connectTimeout(15) برای جلوگیری از ارور تایم‌اوت زودرس
            $response = Http::timeout(30)
                ->connectTimeout(15)
                ->withHeaders([
                    'accept' => 'application/json',
                    'content-type' => 'application/json'
                ])->post($url, $data);

            Log::info('Zarinpal Response Status: ' . $response->status());
            Log::info('Zarinpal Response Body: ' . $response->body());

            $result = $response->json();

            if (isset($result['data']['code']) && $result['data']['code'] == 100) {
                $authority = $result['data']['authority'];

                $paymentUrl = $this->sandbox
                    ? "https://sandbox.zarinpal.com/pg/StartPay/{$authority}"
                    : "https://www.zarinpal.com/pg/StartPay/{$authority}";

                Log::info('Zarinpal Request Success. Authority: ' . $authority);

                return [
                    'success'   => true,
                    'authority' => $authority,
                    'payment_url' => $paymentUrl,
                    'message'   => 'Payment request successful.'
                ];
            } else {
                $errorCode = $result['errors']['code'] ?? ($result['data']['code'] ?? -1);
                $errorMessage = $result['errors']['message'] ?? $this->getZarinpalErrorMessage($errorCode);

                Log::error('Zarinpal Request Failed. Code: ' . $errorCode . ', Message: ' . $errorMessage);

                return [
                    'success' => false,
                    'code'    => $errorCode,
                    'message' => $errorMessage,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Zarinpal Request Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'code'    => -52,
                'message' => 'خطای ارتباطی با سرور زرین‌پال: ' . $e->getMessage(),
            ];
        }
    }

    protected function requestZarinpalPaymentRaw(int $amountInRials, string $description, string $userEmail = null, string $userMobile = null, string $callbackUrl = null)
    {
        return $this->requestZarinpalPayment($amountInRials, $description, $userEmail, $userMobile, $callbackUrl, true);
    }

    protected function verifyZarinpalPayment(array $data)
    {
        if (!$this->merchantId) {
            throw new \Exception("کد مرچنت زرین‌پال تنظیم نشده است.");
        }

        $authority = $data['Authority'] ?? null;
        $status = $data['Status'] ?? null;
        $amount = $data['Amount'] ?? null; // Amount in system currency (toman or rial)

        if ($status !== 'OK' || !$authority || !$amount) {
            Log::error('Zarinpal Verify Error: Invalid callback data.', $data);
            return [
                'success' => false,
                'message' => 'پرداخت موفقیت‌آمیز نبود یا اطلاعات ناقص است.',
                'status'  => $status,
            ];
        }

        $url = $this->sandbox
            ? 'https://sandbox.zarinpal.com/pg/v4/payment/verify.json'
            : 'https://api.zarinpal.com/pg/v4/payment/verify.json';

        $amountInRials = $this->getAmountInRials($amount);

        $verifyData = [
            'merchant_id' => $this->merchantId,
            'authority'  => $authority,
            'amount'     => $amountInRials,
        ];

        Log::info('Zarinpal Verify URL: ' . $url);
        Log::info('Zarinpal Verify Data:', $verifyData);

        try {
            // اضافه شدن timeout(30) و connectTimeout(15)
            $response = Http::timeout(30)
                ->connectTimeout(15)
                ->withHeaders([
                    'accept' => 'application/json',
                    'content-type' => 'application/json'
                ])->post($url, $verifyData);

            Log::info('Zarinpal Verify Response Status: ' . $response->status());
            Log::info('Zarinpal Verify Response Body: ' . $response->body());

            $result = $response->json();

            if (isset($result['data']['code']) && ($result['data']['code'] == 100 || $result['data']['code'] == 101)) {
                return [
                    'success'   => true,
                    'ref_id'    => $result['data']['ref_id'],
                    'authority' => $authority,
                    'message'   => 'Payment successfully verified.',
                ];
            } else {
                $errorCode = $result['errors']['code'] ?? ($result['data']['code'] ?? -1);
                $errorMessage = $result['errors']['message'] ?? $this->getZarinpalErrorMessage($errorCode);

                Log::error('Zarinpal Verify Failed. Code: ' . $errorCode . ', Message: ' . $errorMessage);

                return [
                    'success' => false,
                    'code'    => $errorCode,
                    'message' => $errorMessage,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Zarinpal Verify Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'code'    => -52,
                'message' => 'خطای ارتباطی با سرور زرین‌پال هنگام تایید.',
            ];
        }
    }

    protected function requestZibalPayment(float $amount, string $description, string $userEmail = null, string $userMobile = null, string $callbackUrl = null, bool $alreadyInRials = false)
    {
        if (!$this->merchantId) {
            Log::error('Zibal Error: Merchant ID is empty or not set in settings.');
            throw new \Exception("کد مرچنت زیبال تنظیم نشده است.");
        }

        $url = 'https://gateway.zibal.ir/v1/request';

        // اگر مبلغ از قبل به ریال است، تبدیل نکن
        $amountInRials = $alreadyInRials ? (int) $amount : $this->getAmountInRials($amount);

        $data = [
            'merchant'     => $this->merchantId,
            'amount'       => $amountInRials,
            'description'  => $description,
            'callbackUrl'  => $callbackUrl ?? $this->callbackUrl,
            'mobile'       => $userMobile,
            // 'orderId' => $orderId, // Optional, can be added if needed
        ];

        Log::info('Zibal Request URL: ' . $url);
        Log::info('Zibal Request Data:', $data);

        try {
            $response = Http::timeout(30)
                ->connectTimeout(15)
                ->withHeaders([
                    'accept' => 'application/json',
                    'content-type' => 'application/json'
                ])->post($url, $data);

            Log::info('Zibal Response Status: ' . $response->status());
            Log::info('Zibal Response Body: ' . $response->body());

            $result = $response->json();

            if (isset($result['result']) && $result['result'] == 100) {
                $trackId = $result['trackId'];
                $paymentUrl = "https://gateway.zibal.ir/start/{$trackId}";

                Log::info('Zibal Request Success. TrackId: ' . $trackId);

                return [
                    'success'   => true,
                    'authority' => $trackId, // Zibal uses trackId as authority
                    'payment_url' => $paymentUrl,
                    'message'   => 'Payment request successful.'
                ];
            } else {
                $errorCode = $result['result'] ?? -1;
                $errorMessage = $result['message'] ?? $this->getZibalErrorMessage($errorCode);

                Log::error('Zibal Request Failed. Code: ' . $errorCode . ', Message: ' . $errorMessage);

                return [
                    'success' => false,
                    'code'    => $errorCode,
                    'message' => $errorMessage,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Zibal Request Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'code'    => -52,
                'message' => 'خطای ارتباطی با سرور زیبال: ' . $e->getMessage(),
            ];
        }
    }

    protected function requestZibalPaymentRaw(int $amountInRials, string $description, string $userEmail = null, string $userMobile = null, string $callbackUrl = null)
    {
        return $this->requestZibalPayment($amountInRials, $description, $userEmail, $userMobile, $callbackUrl, true);
    }

    protected function verifyZibalPayment(array $data)
    {
        if (!$this->merchantId) {
            throw new \Exception("کد مرچنت زیبال تنظیم نشده است.");
        }

        $trackId = $data['trackId'] ?? null;
        $success = $data['success'] ?? null; // Zibal sends 'success' (1 or 0) in callback
        $amount = $data['Amount'] ?? null; // Amount is not directly in Zibal callback, need to rely on stored payment amount

        if ($success != 1 || !$trackId) {
            Log::error('Zibal Verify Error: Invalid callback data or unsuccessful payment.', $data);
            return [
                'valid' => false,
                'success' => false,
                'message' => 'پرداخت موفقیت‌آمیز نبود یا اطلاعات ناقص است.',
                'status'  => $success,
            ];
        }

        $url = 'https://gateway.zibal.ir/v1/verify';

        $verifyData = [
            'merchant' => $this->merchantId,
            'trackId'  => (int) $trackId,
        ];

        Log::info('Zibal Verify URL: ' . $url);
        Log::info('Zibal Verify Data:', $verifyData);

        try {
            $response = Http::timeout(30)
                ->connectTimeout(15)
                ->withHeaders([
                    'accept' => 'application/json',
                    'content-type' => 'application/json'
                ])->post($url, $verifyData);

            Log::info('Zibal Verify Response Status: ' . $response->status());
            Log::info('Zibal Verify Response Body: ' . $response->body());

            $result = $response->json();

            if (isset($result['result']) && ($result['result'] == 100 || $result['result'] == 101)) {
                return [
                    'success'   => true,
                    'ref_id'    => !empty($result['refNumber']) ? $result['refNumber'] : ($result['trackId'] ?? $trackId),
                    'authority' => $trackId,
                    'message'   => 'Payment successfully verified.',
                ];
            } else {
                $errorCode = $result['result'] ?? -1;
                $errorMessage = $result['message'] ?? $this->getZibalErrorMessage($errorCode);

                Log::error('Zibal Verify Failed. Code: ' . $errorCode . ', Message: ' . $errorMessage);

                return [
                    'success' => false,
                    'code'    => $errorCode,
                    'message' => $errorMessage,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Zibal Verify Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'code'    => -52,
                'message' => 'خطای ارتباطی با سرور زیبال هنگام تایید.',
            ];
        }
    }

    protected function getZarinpalErrorMessage(int $statusCode): string
    {
        // V4 Error Codes
        switch ($statusCode) {
            case -9: return 'خطای اعتبار سنجی اطلاعات ارسالی (احتمالا Merchant ID نامعتبر است یا طول آن ۳۶ کاراکتر نیست)';
            case -10: return 'ای پی و يا مرچنت كد پذيرنده صحيح نيست';
            case -11: return 'مرچنت کد فعال نیست لطفا با تیم پشتیبانی در تماس باشید';
            case -12: return 'تلاش بیش از حد در یک بازه زمانی کوتاه.';
            case -15: return 'ترمینال شما به حالت تعلیق در آمده با تیم پشتیبانی در تماس باشید';
            case -16: return 'سطح تاييد كاربری پايين تر از سطح نقره ای است.';
            case -30: return 'اجازه دسترسی به تسویه اشتراکی ممانعت شده است';
            case -31: return 'حساب بانکی تسویه را به پنل اضافه کنید مقادیر وارد شده واسه تسهیم درست نیست';
            case -32: return 'مبلغ وارد شده از مبلغ کل تراکنش بیشتر است';
            case -33: return 'درصدهای وارد شده واسه تسهیم درست نیست';
            case -34: return 'مبلغ از کل تراکنش بیشتر است';
            case -35: return 'تعداد افراد دریافت کننده تسهیم بیش از حد مجاز است';
            case -40: return 'خطا در اطلاعات ارسالی. مقادیر وارد شده واسه تسهیم درست نیست';
            case -50: return 'مبلغ پرداخت شده با مقدار مبلغ در وریفای متفاوت است';
            case -51: return 'پرداخت ناموفق';
            case -52: return 'خطای غير منتظره با خطای داخلی سرور';
            case -53: return 'اتوریتی برای این مرچنت کد نیست';
            case -54: return 'اتوریتی نامعتبر است';
            case 100: return 'عملیات موفق';
            case 101: return 'تراکنش وریفای شده';
            default: return 'خطای ناشناخته در ارتباط با زرین‌پال. (کد: ' . $statusCode . ')';
        }
    }

    protected function getZibalErrorMessage(int $statusCode): string
    {
        // Zibal Error Codes from documentation
        switch ($statusCode) {
            case 100: return 'با موفقیت تایید شد.';
            case 102: return 'merchant یافت نشد.';
            case 103: return 'merchant غیرفعال / عدم امضا قرارداد درگاه مربوطه';
            case 104: return 'merchant نامعتبر';
            case 105: return 'amount بایستی بزرگتر از 1,000 ریال باشد.';
            case 106: return 'callbackUrl نامعتبر می‌باشد.';
            case 107: return 'percentMode نامعتبر می‌باشد.';
            case 108: return 'یک یا چند ذی‌نفع در multiplexingInfos نامعتبر می‌باشند.';
            case 109: return 'یک یا چند ذی‌نفع در multiplexingInfos غیرفعال می‌باشند.';
            case 110: return 'id = self در multiplexingInfos وجود ندارد.';
            case 111: return 'amount با مجموع سهم‌ها در multiplexingInfos برابر نمی‌باشد.';
            case 112: return 'موجودی کیف پول کارمزد جهت کسر کارمزد کافی نیست.';
            case 113: return 'amount مبلغ تراکنش از سقف میزان تراکنش بیشتر است.';
            case 114: return 'کدملی ارسالی نامعتبر است.';
            case 115: return 'ip شما در پنل کاربری ثبت نشده است.';
            case 201: return 'قبلا تایید شده.';
            case 202: return 'سفارش پرداخت نشده یا ناموفق بوده است.';
            case 203: return 'trackId نامعتبر می‌باشد.';
            case -1: return 'در انتظار پرداخت';
            case -2: return 'خطای داخلی';
            case 1: return 'پرداخت شده - تاییدشده';
            case 2: return 'پرداخت شده - تاییدنشده';
            case 3: return 'لغوشده توسط کاربر';
            case 4: return 'شماره کارت نامعتبر می‌باشد.';
            case 5: return 'موجودی حساب کافی نمی‌باشد.';
            case 6: return 'رمز واردشده اشتباه می‌باشد.';
            case 7: return 'تعداد درخواست‌ها بیش از حد مجاز می‌باشد.';
            case 8: return 'تعداد پرداخت اینترنتی روزانه بیش از حد مجاز می‌باشد.';
            case 9: return 'مبلغ پرداخت اینترنتی روزانه بیش از حد مجاز می‌باشد.';
            case 10: return 'صادرکننده‌ی کارت نامعتبر می‌باشد.';
            case 11: return 'خطای سوییچ';
            case 12: return 'کارت قابل دسترسی نمی‌باشد.';
            case 15: return 'تراکنش استرداد شده';
            case 16: return 'تراکنش در حال استرداد';
            case 18: return 'تراکنش ریورس شده';
            case 21: return 'پذیرنده نامعتبر است';
            default: return 'خطای ناشناخته در ارتباط با زیبال. (کد: ' . $statusCode . ')';
        }
    }

    protected function requestBehpardakhtPayment(float $amount, string $description, string $userEmail = null, string $userMobile = null, string $callbackUrl = null, bool $alreadyInRials = false, ?int $orderId = null)
    {
        if (!$this->terminalId || !$this->username || !$this->password) {
            Log::error('Behpardakht Error: Terminal ID, Username or Password is empty or not set in settings.');
            throw new \Exception("اطلاعات شماره ترمینال، نام کاربری یا رمز عبور به‌پرداخت ملت در تنظیمات سیستم ثبت نشده است.");
        }

        $amountInRials = $alreadyInRials ? (int) $amount : $this->getAmountInRials($amount);
        $orderId = $orderId ?: (int) (time() . rand(100, 999));
        $effectiveCallbackUrl = $callbackUrl ?? $this->callbackUrl;

        $params = [
            'terminalId'     => (int) $this->terminalId,
            'userName'       => (string) $this->username,
            'userPassword'   => (string) $this->password,
            'orderId'        => (int) $orderId,
            'amount'         => (int) $amountInRials,
            'localDate'      => date('Ymd'),
            'localTime'      => date('His'),
            'additionalData' => (string) ($description ?: ''),
            'callBackUrl'    => (string) $effectiveCallbackUrl,
            'payerId'        => 0,
        ];

        if (!empty($userMobile)) {
            $mobileClean = preg_replace('/[^0-9]/', '', $userMobile);
            if (strlen($mobileClean) >= 10) {
                $params['mobileNo'] = $mobileClean;
            }
        }

        Log::info('Behpardakht bpPayRequest Data:', array_merge($params, ['userPassword' => '******']));

        try {
            $rawResponse = $this->callMellatSoap('bpPayRequest', $params);
            Log::info('Behpardakht bpPayRequest Response: ' . $rawResponse);

            $parts = explode(',', trim($rawResponse));
            $resCode = $parts[0] ?? '-1';

            if ($resCode === '0' && isset($parts[1]) && !empty($parts[1])) {
                $refId = trim($parts[1]);
                $redirectUrl = route('settings.payment.behpardakht.redirect', [
                    'ref_id' => $refId,
                    'mobile' => $userMobile,
                ]);

                Log::info('Behpardakht Request Success. RefId: ' . $refId . ', OrderId: ' . $orderId);

                return [
                    'success'         => true,
                    'authority'       => $refId,
                    'order_id'        => $orderId,
                    'payment_url'     => $redirectUrl,
                    'redirect_method' => 'POST',
                    'redirect_params' => [
                        'RefId'    => $refId,
                        'MobileNo' => $userMobile,
                    ],
                    'message'         => 'Payment request successful.'
                ];
            } else {
                $errorMessage = $this->getBehpardakhtErrorMessage($resCode);
                Log::error('Behpardakht Request Failed. Code: ' . $resCode . ', Message: ' . $errorMessage);

                return [
                    'success' => false,
                    'code'    => (int) $resCode,
                    'message' => $errorMessage,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Behpardakht Request Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'code'    => -52,
                'message' => 'خطای ارتباط با سرور به‌پرداخت ملت: ' . $e->getMessage(),
            ];
        }
    }

    protected function requestBehpardakhtPaymentRaw(int $amountInRials, string $description, string $userEmail = null, string $userMobile = null, string $callbackUrl = null, ?int $orderId = null)
    {
        return $this->requestBehpardakhtPayment($amountInRials, $description, $userEmail, $userMobile, $callbackUrl, true, $orderId);
    }

    protected function verifyBehpardakhtPayment(array $data)
    {
        if (!$this->terminalId || !$this->username || !$this->password) {
            throw new \Exception("اطلاعات شماره ترمینال، نام کاربری یا رمز عبور به‌پرداخت ملت در تنظیمات سیستم ثبت نشده است.");
        }

        $refId           = $data['RefId'] ?? null;
        $resCode         = (string) ($data['ResCode'] ?? '-1');
        $saleOrderId     = $data['SaleOrderId'] ?? ($data['order_id'] ?? null);
        $saleReferenceId = $data['SaleReferenceId'] ?? null;

        Log::info('Behpardakht Verify Callback Data:', $data);

        // If gateway ResCode is not 0, user canceled or payment failed on gateway
        if ($resCode !== '0' && $resCode !== '00') {
            $errorMessage = $this->getBehpardakhtErrorMessage($resCode);
            Log::error('Behpardakht Verify Failed at gateway. ResCode: ' . $resCode . ', Message: ' . $errorMessage);
            return [
                'success' => false,
                'code'    => (int) $resCode,
                'message' => $errorMessage,
            ];
        }

        if (!$saleOrderId || !$saleReferenceId) {
            return [
                'success' => false,
                'code'    => -1,
                'message' => 'اطلاعات تراکنش (SaleOrderId یا SaleReferenceId) ناقص است.',
            ];
        }

        // 1. bpVerifyRequest
        $verifyParams = [
            'terminalId'      => (int) $this->terminalId,
            'userName'        => (string) $this->username,
            'userPassword'    => (string) $this->password,
            'orderId'         => (int) $saleOrderId,
            'saleOrderId'     => (int) $saleOrderId,
            'saleReferenceId' => (int) $saleReferenceId,
        ];

        Log::info('Behpardakht bpVerifyRequest Data:', array_merge($verifyParams, ['userPassword' => '******']));

        try {
            $verifyResponse = trim($this->callMellatSoap('bpVerifyRequest', $verifyParams));
            Log::info('Behpardakht bpVerifyRequest Response: ' . $verifyResponse);

            // 0 = Success, 43 = Transaction already verified
            if ($verifyResponse === '0' || $verifyResponse === '43') {
                // 2. bpSettleRequest (finalize the settlement)
                try {
                    $settleResponse = trim($this->callMellatSoap('bpSettleRequest', $verifyParams));
                    Log::info('Behpardakht bpSettleRequest Response: ' . $settleResponse);
                } catch (\Exception $se) {
                    Log::warning('Behpardakht Settle Warning: ' . $se->getMessage());
                }

                return [
                    'success'   => true,
                    'ref_id'    => (string) $saleReferenceId,
                    'authority' => $refId,
                    'order_id'  => $saleOrderId,
                    'message'   => 'Payment successfully verified.',
                ];
            } else {
                $errorMessage = $this->getBehpardakhtErrorMessage($verifyResponse);
                Log::error('Behpardakht bpVerifyRequest Failed. Code: ' . $verifyResponse . ', Message: ' . $errorMessage);

                // Attempt reversal if verify failed
                try {
                    $this->callMellatSoap('bpReversalRequest', $verifyParams);
                } catch (\Exception $re) {
                    Log::warning('Behpardakht Reversal Attempt Warning: ' . $re->getMessage());
                }

                return [
                    'success' => false,
                    'code'    => (int) $verifyResponse,
                    'message' => $errorMessage,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Behpardakht Verify Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'code'    => -52,
                'message' => 'خطای ارتباط با سرور به‌پرداخت ملت هنگام تایید: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send raw SOAP XML request to Mellat Behpardakht Web Service without requiring ext-soap.
     */
    protected function callMellatSoap(string $method, array $params): string
    {
        $endpoint = 'https://bpm.shaparak.ir/pgwchannel/services/pgw';
        $namespace = 'http://interfaces.core.sw.bps.com/';

        $innerXml = '';
        foreach ($params as $key => $val) {
            $safeVal = htmlspecialchars((string) $val, ENT_XML1, 'UTF-8');
            $innerXml .= "<{$key}>{$safeVal}</{$key}>";
        }

        $soapEnvelope = '<?xml version="1.0" encoding="UTF-8"?>' .
            '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:int="' . $namespace . '">' .
            '<soapenv:Header/>' .
            '<soapenv:Body>' .
            "<int:{$method}>" .
            $innerXml .
            "</int:{$method}>" .
            '</soapenv:Body>' .
            '</soapenv:Envelope>';

        $response = Http::timeout(30)
            ->connectTimeout(15)
            ->withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction'   => '""',
            ])
            ->send('POST', $endpoint, [
                'body' => $soapEnvelope,
            ]);

        $body = $response->body();

        if (preg_match('/<return>(.*?)<\/return>/s', $body, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/<faultstring>(.*?)<\/faultstring>/s', $body, $matches)) {
            throw new \Exception('SOAP Fault: ' . trim($matches[1]));
        }

        return trim(strip_tags($body));
    }

    protected function getBehpardakhtErrorMessage($statusCode): string
    {
        $code = (int) $statusCode;
        switch ($code) {
            case 0: return 'تراکنش با موفقیت انجام شد.';
            case 11: return 'شماره کارت نامعتبر است.';
            case 12: return 'موجودی کافی نیست.';
            case 13: return 'رمز نادرست است.';
            case 14: return 'تعداد دفعات وارد کردن رمز بیش از حد مجاز است.';
            case 15: return 'کارت نامعتبر است.';
            case 16: return 'دفعات برداشت وجه بیش از حد مجاز است.';
            case 17: return 'کاربر از انجام تراکنش منصرف شده است.';
            case 18: return 'تاریخ انقضای کارت گذشته است.';
            case 19: return 'مبلغ برداشت وجه بیش از حد مجاز است.';
            case 111: return 'صادر کننده کارت نامعتبر است.';
            case 112: return 'خطای سوییچ صادر کننده کارت.';
            case 113: return 'پاسخی از صادر کننده کارت دریافت نشد.';
            case 114: return 'دارنده کارت مجاز به انجام این تراکنش نیست.';
            case 21: return 'پذیرنده نامعتبر است (سرویس مربوطه برای پذیرنده فعال نمی‌باشد).';
            case 23: return 'خطای امنیتی رخ داده است.';
            case 24: return 'اطلاعات کاربری پذیرنده نامعتبر است.';
            case 25: return 'مبلغ نامعتبر است.';
            case 31: return 'پاسخ نامعتبر است.';
            case 32: return 'فرمت اطلاعات وارد شده صحیح نمی‌باشد.';
            case 33: return 'حساب نامعتبر است.';
            case 34: return 'خطای سیستمی در سرور بانک.';
            case 35: return 'تاریخ نامعتبر است.';
            case 41: return 'شماره درخواست تکراری است.';
            case 42: return 'تراکنش Sale یافت نشد.';
            case 43: return 'قبلا درخواست Verify داده شده است.';
            case 44: return 'درخواست Verify یافت نشد.';
            case 45: return 'تراکنش Settle شده است.';
            case 46: return 'تراکنش Settle نشده است.';
            case 47: return 'تراکنش Settle یافت نشد.';
            case 48: return 'تراکنش Reverse شده است.';
            case 412: return 'شناسه قبض نادرست است.';
            case 413: return 'شناسه پرداخت نادرست است.';
            case 414: return 'سازمان صادر کننده قبض نامعتبر است.';
            case 415: return 'زمان جلسه کاری به پایان رسیده است.';
            case 416: return 'خطا در ثبت اطلاعات.';
            case 417: return 'شناسه پرداخت کننده نامعتبر است.';
            case 418: return 'اشکال در تعریف اطلاعات مشتری.';
            case 419: return 'تعداد دفعات ورود اطلاعات از حد مجاز گذشته است.';
            case 421: return 'IP سرور پذیرنده معتبر نیست (پیشتر به سامانه به‌پرداخت اعلام نشده است).';
            case 51: return 'تراکنش تکراری است.';
            case 54: return 'تراکنش مرجع موجود نیست.';
            case 55: return 'تراکنش نامعتبر است.';
            case 61: return 'خطا در واریز وجه.';
            case 62: return 'مسیر بازگشت به سایت در دامنه ثبت شده برای پذیرنده قرار ندارد.';
            case 98: return 'سقف استفاده از رمز ایستا به پایان رسیده است.';
            case 995: return 'تعلق کارت بانکی به مشتری احراز نشد.';
            default: return 'خطای ناشناخته در ارتباط با به‌پرداخت ملت. (کد: ' . $statusCode . ')';
        }
    }
}
