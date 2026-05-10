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

    public function createPendingPayment(int $appointmentId, string $mode, float $amount, string $currencyUnit): BookingPayment
    {
        return BookingPayment::query()->create([
            'appointment_id' => $appointmentId,
            'mode' => $mode,
            'amount' => $amount,
            'currency_unit' => $currencyUnit,
            'status' => BookingPayment::STATUS_PENDING,
        ]);
    }

    /**
     * Integrate with the global payment gateway.
     * Return array {payment_url?:string, gateway_ref?:string}
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

            $result = $globalPaymentService->requestPayment(
                $payment->amount,
                $description,
                $client->email,
                $client->phone,
                $callbackUrl
            );

            if ($result['success']) {
                // Update the local booking payment record with the authority from the gateway
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
     * سازماندهی شده: این منطق از کنترلر به اینجا منتقل شد تا کنترلر سبک بماند
     */
    public function verifyGatewayPayment(BookingPayment $payment, string $gateway, array $requestData, AppointmentService $appointmentService): array
    {
        $authority = $requestData['Authority'] ?? null;
        $status = $requestData['Status'] ?? null;

        if (!$authority || !$payment) {
            return ['valid' => false, 'success' => false, 'message' => 'اطلاعات پرداخت معتبر نیست.'];
        }

        if ($payment->gateway_ref !== $authority) {
            return ['valid' => false, 'success' => false, 'message' => 'تراکنش نامعتبر است.'];
        }

        if ($status === 'NOK') {
            $payment->update(['status' => BookingPayment::STATUS_CANCELLED]);
            return ['valid' => true, 'success' => false, 'message' => 'پرداخت توسط کاربر لغو شد.'];
        }

        try {
            $globalPaymentService = new GlobalPaymentService($gateway);

            $dataToVerify = [
                'Authority' => $authority,
                'Status'    => $status,
                'Amount'    => $payment->amount,
            ];

            $result = $globalPaymentService->verifyPayment($dataToVerify);

            if ($result['success']) {
                $appointmentService->markPaymentPaid($payment->id, $result['ref_id']);
                return [
                    'valid' => true,
                    'success' => true,
                    'message' => 'پرداخت با موفقیت انجام شد و نوبت شما تایید شد. کد پیگیری: ' . $result['ref_id']
                ];
            } else {
                $payment->update(['status' => BookingPayment::STATUS_FAILED]);
                return [
                    'valid' => true,
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
