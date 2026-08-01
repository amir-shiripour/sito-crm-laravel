<?php

namespace Modules\Booking\App\Observers;

use Modules\Booking\Entities\Appointment;
use Modules\Booking\Services\BookingPaymentWalletService;

class AppointmentObserver
{
    protected BookingPaymentWalletService $walletSyncService;

    public function __construct(BookingPaymentWalletService $walletSyncService)
    {
        $this->walletSyncService = $walletSyncService;
    }

    /**
     * Handle the Appointment "updated" event.
     */
    public function updated(Appointment $appointment): void
    {
        if ($appointment->isDirty('status')) {
            $oldStatus = $appointment->getOriginal('status');
            $newStatus = $appointment->status;

            $this->walletSyncService->handleAppointmentStatusChange($appointment, $oldStatus, $newStatus);
        }
    }
}
