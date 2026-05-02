<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Modules\Settings\Entities\Setting;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $gateway;
    protected $merchantId;
    protected $sandbox;
    protected $callbackUrl;
    protected $currency;

    public function __construct(string $gateway)
    {
        $this->gateway = $gateway;
        $this->loadGatewaySettings();
    }

    protected function loadGatewaySettings()
    {
        // Load settings from the database
        $settings = Setting::all()->pluck('value', 'key');

        $this->currency = $settings['payment_currency'] ?? 'toman';

        if ($this->gateway === 'zarinpal') {
            $this->merchantId = $settings['zarinpal_merchant_id'] ?? null;
            $this->sandbox = ($settings['zarinpal_sandbox'] ?? '0') === '1';
            // This callback URL should be dynamic based on the actual payment initiation
            // For now, we'll use a placeholder and expect it to be passed during payment request
            $this->callbackUrl = route('settings.payment.verify', ['gateway' => 'zarinpal']);
        }
        // Add other gateways here
    }

    /**
     * Get the amount in Rials. Zarinpal expects Rials.
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
        }
        // Add other gateways here
        throw new \Exception("Payment gateway {$this->gateway} not supported.");
    }

    public function verifyPayment(array $data)
    {
        if ($this->gateway === 'zarinpal') {
            return $this->verifyZarinpalPayment($data);
        }
        // Add other gateways here
        throw new \Exception("Payment gateway {$this->gateway} not supported.");
    }

    protected function requestZarinpalPayment(float $amount, string $description, string $userEmail = null, string $userMobile = null, string $callbackUrl = null)
    {
        if (!$this->merchantId) {
            Log::error('Zarinpal Error: Merchant ID is empty or not set in settings.');
            throw new \Exception("کد مرچنت زرین‌پال تنظیم نشده است.");
        }

        // Zarinpal v4 API URLs
        $url = $this->sandbox
            ? 'https://sandbox.zarinpal.com/pg/v4/payment/request.json'
            : 'https://api.zarinpal.com/pg/v4/payment/request.json';

        $amountInRials = $this->getAmountInRials($amount);

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
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json'
            ])->post($url, $data);

            Log::info('Zarinpal Response Status: ' . $response->status());
            Log::info('Zarinpal Response Body: ' . $response->body());

            $result = $response->json();

            if (isset($result['data']['code']) && $result['data']['code'] == 100) {
                $authority = $result['data']['authority'];

                // For v4, sandbox StartPay URL is different from production StartPay URL in some older docs,
                // but generally StartPay URL is the same, just the authority dictates sandbox vs production.
                // Let's use the standard StartPay URL.
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
            $response = Http::withHeaders([
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
}
