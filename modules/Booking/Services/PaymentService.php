<?php

namespace Modules\Booking\Services;

use Modules\Booking\Entities\BookingPayment;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingServiceProvider;

class PaymentService
{
    public function calculateAmount(BookingService $service, BookingServiceProvider $serviceProvider): float
    {
        $price = (float) $serviceProvider->effectivePrice();

        $type = $service->payment_amount_type;
        $val  = $service->payment_amount_value;

        if ($service->payment_mode === BookingService::PAYMENT_MODE_NONE) {
            return 0.0;
        }

        if (!$type || $type === BookingService::PAYMENT_AMOUNT_FULL) {
            return max(0.0, $price);
        }

        if ($type === BookingService::PAYMENT_AMOUNT_FIXED) {
            return max(0.0, (float) ($val ?? 0));
        }

        // DEPOSIT: interpret payment_amount_value as percentage (0..100) by default.
        if ($type === BookingService::PAYMENT_AMOUNT_DEPOSIT) {
            $p = (float) ($val ?? 0);

            // If user stored as fraction (0..1), convert
            if ($p > 0 && $p <= 1) {
                $p = $p * 100;
            }

            $p = max(0.0, min(100.0, $p));
            return round(($price * $p) / 100.0, 2);
        }

        return max(0.0, $price);
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
     * Stub for gateway integration.
     * Return array {payment_url?:string, gateway_ref?:string}
     */
    public function startGateway(BookingPayment $payment): array
    {
        // You can integrate with your gateway here and update gateway_ref.
        return [
            'payment_url' => null,
            'gateway_ref' => null,
        ];
    }
}
