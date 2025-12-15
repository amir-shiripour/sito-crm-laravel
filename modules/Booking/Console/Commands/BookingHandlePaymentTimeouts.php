<?php

namespace Modules\Booking\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingPayment;
use Modules\Booking\Services\AppointmentService;

class BookingHandlePaymentTimeouts extends Command
{
    protected $signature = 'booking:handle-payment-timeouts {--dry-run : Do not update records, only report}';
    protected $description = 'Auto-cancel appointments stuck in PENDING_PAYMENT beyond configured timeout.';

    public function handle(): int
    {
        $timeoutMinutes = (int) config('booking.payment_timeout_minutes', 20);
        $cutoff = now()->subMinutes($timeoutMinutes);

        $q = Appointment::query()
            ->where('status', Appointment::STATUS_PENDING_PAYMENT)
            ->where('created_at', '<=', $cutoff);

        $count = $q->count();
        $this->info("Found {$count} appointments timed-out (PENDING_PAYMENT <= {$cutoff->toDateTimeString()})");

        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }

        /** @var AppointmentService $service */
        $service = app(AppointmentService::class);

        $processed = 0;
        $q->chunkById(50, function ($appointments) use (&$processed, $service) {
            foreach ($appointments as $appt) {
                DB::transaction(function () use ($service, $appt) {
                    $service->cancelAppointment(
                        $appt,
                        Appointment::STATUS_CANCELED_BY_ADMIN,
                        'Timeout پرداخت (auto-cancel)',
                        authUserId: null
                    );

                    // Mark related payment as canceled (if exists)
                    BookingPayment::query()
                        ->where('appointment_id', $appt->id)
                        ->where('status', BookingPayment::STATUS_PENDING)
                        ->update(['status' => BookingPayment::STATUS_CANCELED]);
                });

                $processed++;
            }
        });

        $this->info("Processed {$processed} timed-out appointments.");

        return self::SUCCESS;
    }
}
