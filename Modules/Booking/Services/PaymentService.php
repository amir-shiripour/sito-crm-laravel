<?php

namespace Modules\Booking\Services;

use Modules\Booking\Entities\BookingPayment;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingServiceProvider;
use Modules\Booking\Entities\BookingSetting;
use App\Services\PaymentService as GlobalPaymentService;
use Illuminate\Support\Facades\Log;
use Modules\Settings\Entities\Setting;
use Modules\Booking\Services\AppointmentService;

class PaymentService
{
    public function calculateAmount(BookingService $service, BookingServiceProvider $serviceProvider): float
    {
        $price = (float) $serviceProvider->effectivePrice();

        $type = $service->payment_amount_type;
        $val  = $service->payment_amount_value;

        $baseAmount = 0.0;

        if ($service->payment_mode !== BookingService::PAYMENT_MODE_NONE) {
            if (!$type || $type === BookingService::PAYMENT_AMOUNT_FULL) {
                $baseAmount = max(0.0, $price);
            } elseif ($type === BookingService::PAYMENT_AMOUNT_FIXED) {
                $baseAmount = max(0.0, (float) ($val ?? 0));
            } elseif ($type === BookingService::PAYMENT_AMOUNT_DEPOSIT) {
                $p = (float) ($val ?? 0);

                // If user stored as fraction (0..1), convert
                if ($p > 0 && $p <= 1) {
                    $p = $p * 100;
                }

                $p = max(0.0, min(100.0, $p));
                $baseAmount = round(($price * $p) / 100.0, 2);
            } else {
                $baseAmount = max(0.0, $price);
            }
        }

        $taxAmount = 0.0;
        $settings = BookingSetting::current();

        if ($settings->tax_enabled) {
            $taxValue = (float) ($settings->tax_amount ?? 0);
            if ($settings->tax_type === 'FIXED') {
                $taxAmount = max(0.0, $taxValue);
            } else {
                // PERCENT
                // Calculate tax based on the full price of the service
                $taxAmount = round(($price * $taxValue) / 100.0, 2);
            }
        }

        return $baseAmount + $taxAmount;
    }

    public function createPendingPayment(int $appointmentId, int $clientId, float $amount, string $currencyUnit): BookingPayment
    {
        // مبلغ را به ریال تبدیل کرده و در دیتابیس ذخیره می‌کنیم
        // GlobalPaymentService.startGateway() مبلغ را به ریال انتظار دارد
        // پس باید مبلغ ذخیره‌شده همان ریال باشد تا از تبدیل مضاعف جلوگیری شود
        $amountInRial = $amount;
        if (strtoupper($currencyUnit) === 'IRT') {
            $amountInRial = $amount * 10;
        }

        return BookingPayment::query()->create([
            'appointment_id' => $appointmentId,
            'client_id' => $clientId,
            'type' => 'booking',
            'amount' => $amountInRial,
            'currency_unit' => 'IRR', // Always store in IRR (Rial)
            'status' => BookingPayment::STATUS_PENDING,
        ]);
    }

    /**
     * Integrate with the global payment gateway.
     * Return array {payment_url?:string, gateway_ref?:string}
     * مبلغ payment->amount همیشه به ریال (IRR) در دیتابیس ذخیره شده،
     * پس باید مطمئن شویم GlobalPaymentService دوباره آن را تبدیل نمی‌کند.
     */
    public function startGateway(BookingPayment $payment): array
    {
        $settings = Setting::all()->pluck('value', 'key');
        $defaultGateway = $settings['default_payment_gateway'] ?? null;

        if (!$defaultGateway) {
            Log::error('No default payment gateway is configured.');
            return ['payment_url' => null, 'gateway_ref' => null];
        }

        try {
            $globalPaymentService = new GlobalPaymentService($defaultGateway);

            $appointment = $payment->appointment;
            $service = $appointment->service;
            $client = $appointment->client;

            $description = "پرداخت برای رزرو سرویس: " . $service->name;

            $callbackUrl = route('booking.payment.verify', [
                'gateway' => $defaultGateway,
                'payment' => $payment->id
            ]);

            // مبلغ payment->amount همیشه به ریال (IRR) ذخیره شده
            // برای جلوگیری از تبدیل مضاعف در GlobalPaymentService.getAmountInRials()
            // باید مطمئن شویم currency_unit را به‌عنوان IRR ارسال می‌کنیم.
            // چون GlobalPaymentService فقط وقتی currency="toman" باشد ضرب‌در-۱۰ می‌کند،
            // کافی است payment_currency را موقتاً روی 'rial' تنظیم کنیم.
            // بهترین راه: متد جداگانه‌ای با mAmount که از قبل به ریال است
            $result = $globalPaymentService->requestPaymentInRials(
                (int) $payment->amount, // دقیقاً به ریال - بدون تبدیل مجدد
                $description,
                $client->email,
                $client->phone,
                $callbackUrl
            );

            if ($result['success']) {
                $payment->update(['gateway_ref' => $result['authority']]);

                return [
                    'payment_url' => $result['payment_url'],
                    'gateway_ref' => $result['authority'],
                ];
            } else {
                Log::error('Gateway request failed', ['result' => $result]);
                return ['payment_url' => null, 'gateway_ref' => null];
            }
        } catch (\Exception $e) {
            Log::error('Error starting gateway payment', ['exception' => $e->getMessage()]);
            return ['payment_url' => null, 'gateway_ref' => null];
        }
    }

    /**
     * Verify the payment response from the gateway.
     * پشتیبانی از زیبال و زرین‌پال با پارامترهای callback متفاوت
     */
    public function verifyGatewayPayment(BookingPayment $payment, string $gateway, array $requestData, AppointmentService $appointmentService): array
    {
        // --- استخراج پارامترها بر اساس درگاه ---
        if ($gateway === 'zibal') {
            // زیبال: trackId + success
            $authority = $requestData['trackId'] ?? null;
            $status    = ($requestData['success'] ?? null) == 1 ? 'OK' : 'NOK';
        } else {
            // زرین‌پال و سایر درگاه‌ها: Authority + Status
            $authority = $requestData['Authority'] ?? null;
            $status    = $requestData['Status'] ?? null;
        }

        if (!$authority || !$payment) {
            return ['valid' => false, 'success' => false, 'message' => 'اطلاعات پرداخت معتبر نیست.'];
        }

        if ($payment->gateway_ref !== (string) $authority) {
            return ['valid' => false, 'success' => false, 'message' => 'تراکنش نامعتبر است.'];
        }

        if ($status === 'NOK') {
            $payment->update(['status' => BookingPayment::STATUS_CANCELLED]);
            return ['valid' => true, 'success' => false, 'message' => 'پرداخت توسط کاربر لغو شد.'];
        }

        try {
            $globalPaymentService = new GlobalPaymentService($gateway);

            // داده‌هایی که به verifyPayment پاس می‌دیم - برای هر درگاه فرمت خاص خود را دارد
            if ($gateway === 'zibal') {
                $dataToVerify = [
                    'trackId' => $authority,
                    'success' => 1,
                    'Amount'  => $payment->amount,
                ];
            } else {
                $dataToVerify = [
                    'Authority' => $authority,
                    'Status'    => $status,
                    'Amount'    => $payment->amount,
                ];
            }

            $result = $globalPaymentService->verifyPayment($dataToVerify);

            if ($result['success']) {
                $appointmentService->markPaymentPaid($payment->id, $result['ref_id']);
                return [
                    'valid'   => true,
                    'success' => true,
                    'message' => 'پرداخت با موفقیت انجام شد و نوبت شما تایید شد. کد پیگیری: ' . $result['ref_id']
                ];
            } else {
                $payment->update(['status' => BookingPayment::STATUS_FAILED]);
                return [
                    'valid'   => true,
                    'success' => false,
                    'message' => 'خطا در تایید پرداخت: ' . $result['message']
                ];
            }
        } catch (\Exception $e) {
            Log::error('Payment verification failed for booking', ['exception' => $e->getMessage()]);
            return ['valid' => true, 'success' => false, 'message' => 'خطا در سیستم تایید پرداخت.'];
        }
    }
}
