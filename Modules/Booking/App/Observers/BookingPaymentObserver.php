<?php

namespace Modules\Booking\App\Observers;

use Modules\Booking\Entities\BookingPayment;
use Modules\Booking\Services\BookingPaymentWalletService;

class BookingPaymentObserver
{
    protected BookingPaymentWalletService $walletSyncService;

    public function __construct(BookingPaymentWalletService $walletSyncService)
    {
        $this->walletSyncService = $walletSyncService;
    }

    /**
     * Handle the BookingPayment "updated" event.
     */
    public function updated(BookingPayment $payment): void
    {
        if ($payment->isDirty('status')) {
            $oldStatus = $payment->getOriginal('status');
            $newStatus = $payment->status;

            $this->walletSyncService->handlePaymentStatusChange($payment, $oldStatus, $newStatus);
        }
    }

    /**
     * Handle the BookingPayment "created" event.
     */
    public function created(BookingPayment $payment): void
    {
        if ($payment->status === BookingPayment::STATUS_PAID) {
            $this->walletSyncService->handlePaymentStatusChange($payment, null, $payment->status);
        }
    }
}
