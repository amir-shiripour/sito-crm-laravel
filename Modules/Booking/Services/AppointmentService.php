<?php

namespace Modules\Booking\Services;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingDayLock;
use Modules\Booking\Entities\BookingPayment;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingServiceProvider;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Entities\BookingSlotHold;
use Modules\Booking\Entities\BookingSlotLock;
use Modules\Workflows\Entities\Workflow;
use Modules\Workflows\Entities\WorkflowTrigger;

class AppointmentService
{
    public function __construct(
        protected BookingEngine $engine,
        protected ClientProfileService $clientProfileService,
        protected PaymentService $paymentService,
        protected AuditLogger $audit,
    ) {
    }
    /**
     * لیست تریگرهای قابل انتخاب برای ماژول Workflows
     */
    public static function workflowTriggerOptions(): array
    {
        return [
            // --- رویدادهای کلی ایجاد ---
            'appointment_created'          => 'ایجاد نوبت (کلی - هر روشی)',

            // --- رویدادهای دقیق ایجاد (بر اساس منبع و وضعیت اولیه) ---
            'created_online_pending'       => 'رزرو آنلاین: در انتظار تایید (نیاز به بررسی)',
            'created_online_confirmed'     => 'رزرو آنلاین: تایید شده (اتوماتیک)',
            'created_operator_confirmed'   => 'ثبت توسط اپراتور/ادمین (تایید شده)',

            // --- تغییر وضعیت‌ها (Status Changes) ---
            'appointment_status_changed'   => 'تغییر وضعیت (کلی - هر تغییری)',
            'status_pending'               => 'تغییر وضعیت به: در انتظار تایید',
            'status_pending_payment'       => 'تغییر وضعیت به: در انتظار پرداخت',
            'status_confirmed'             => 'تغییر وضعیت به: تایید شده',
            'status_done'                  => 'تغییر وضعیت به: انجام شده',
            'status_no_show'               => 'تغییر وضعیت به: عدم حضور',
            'status_canceled'              => 'تغییر وضعیت به: لغو شده (کلی)',
            'status_rescheduled'           => 'تغییر وضعیت به: جابجا شده',

            // --- یادآوری‌های زمان‌دار ---
            'appointment_reminder_1_hour_before'  => 'یادآوری: ۱ ساعت قبل از نوبت',
            'appointment_reminder_2_hours_before' => 'یادآوری: ۲ ساعت قبل از نوبت',
            'appointment_reminder_1_day_before'   => 'یادآوری: ۱ روز قبل از نوبت',
            'appointment_reminder_2_days_before'  => 'یادآوری: ۲ روز قبل از نوبت',
            'appointment_reminder_3_days_before'  => 'یادآوری: ۳ روز قبل از نوبت',
            'appointment_reminder_7_days_before'  => 'یادآوری: ۷ روز قبل از نوبت',
        ];
    }

    /**
     * Create a TTL slot hold (online booking).
     */
    public function startOnlineHold(
        int $serviceId,
        int $providerUserId,
        string $startAtUtcIso,
        string $endAtUtcIso,
        ?string $clientTempKey = null
    ): BookingSlotHold {
        if (!$this->engine->isOnlineBookingEnabled($serviceId, $providerUserId)) {
            throw new \RuntimeException('Online booking is disabled for this service/provider.');
        }

        $startUtc = Carbon::parse($startAtUtcIso, 'UTC');
        $endUtc   = Carbon::parse($endAtUtcIso, 'UTC');

        if ($endUtc->lte($startUtc)) {
            throw new \InvalidArgumentException('Invalid slot time range.');
        }

        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
        $localDate  = $startUtc->copy()->timezone($scheduleTz)->toDateString();

        $ttl = (int) config('booking.slot_hold_ttl_minutes', 10);

        return DB::transaction(function () use ($serviceId, $providerUserId, $startUtc, $endUtc, $localDate, $ttl, $clientTempKey) {
            $this->lockDayAndSlot($serviceId, $providerUserId, $localDate, $startUtc, $endUtc);
            $this->assertCapacityAvailable($serviceId, $providerUserId, $localDate, $startUtc, $endUtc);

            return BookingSlotHold::query()->create([
                'service_id' => $serviceId,
                'provider_user_id' => $providerUserId,
                'client_temp_key' => $clientTempKey,
                'start_at_utc' => $startUtc,
                'end_at_utc' => $endUtc,
                'expires_at_utc' => now()->addMinutes($ttl),
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Confirm an online booking by consuming a hold.
     */
    public function confirmOnlineHold(
        int $holdId,
        array $clientInput,
        ?array $appointmentFormResponse = null,
        bool $payNow = true
    ): array {
        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');

        return DB::transaction(function () use ($holdId, $clientInput, $appointmentFormResponse, $payNow, $scheduleTz) {
            /** @var BookingSlotHold $hold */
            $hold = BookingSlotHold::query()->whereKey($holdId)->lockForUpdate()->firstOrFail();

            if ($hold->isExpired()) {
                throw new \RuntimeException('Slot hold expired.');
            }

            $startUtc = Carbon::parse($hold->getRawOriginal('start_at_utc'), 'UTC');
            $endUtc   = Carbon::parse($hold->getRawOriginal('end_at_utc'), 'UTC');
            $localDate = $startUtc->copy()->timezone($scheduleTz)->toDateString();

            $this->lockDayAndSlot($hold->service_id, $hold->provider_user_id, $localDate, $startUtc, $endUtc);

            $this->assertCapacityAvailable(
                $hold->service_id,
                $hold->provider_user_id,
                $localDate,
                $startUtc,
                $endUtc,
                excludeHoldId: $hold->id
            );

            $client = $this->clientProfileService->resolveOrCreateClient(
                $clientInput,
                $clientInput['client_id'] ?? null,
                $hold->provider_user_id
            );

            $service = BookingService::query()->findOrFail($hold->service_id);
            $sp = $this->engine->getServiceProvider($hold->service_id, $hold->provider_user_id);
            if (!$sp) {
                throw new \RuntimeException('Provider is not attached to service.');
            }

            $settings = BookingSetting::current();
            $amount = $this->paymentService->calculateAmount($service, $sp);
            $needsPayment = ($service->payment_mode !== BookingService::PAYMENT_MODE_NONE) && $amount > 0;
            $autoConfirm = $sp->effectiveAutoConfirm();

            $status = $autoConfirm ? Appointment::STATUS_CONFIRMED : Appointment::STATUS_PENDING;

            if ($needsPayment && $service->payment_mode === BookingService::PAYMENT_MODE_REQUIRED) {
                $status = Appointment::STATUS_PENDING_PAYMENT;
            }

            $appointment = Appointment::query()->create([
                'service_id' => $hold->service_id,
                'provider_user_id' => $hold->provider_user_id,
                'client_id' => $client->id,
                'status' => $status,
                'start_at_utc' => $startUtc,
                'end_at_utc' => $endUtc,
                'created_by_type' => Appointment::CREATED_BY_CLIENT_ONLINE,
                'created_by_user_id' => null,
                'notes' => $clientInput['notes'] ?? null,
                'appointment_form_response_json' => $appointmentFormResponse,
            ]);

            $payment = null;
            $gateway = [];

            if ($needsPayment && ($service->payment_mode === BookingService::PAYMENT_MODE_REQUIRED || $payNow)) {
                $payment = $this->paymentService->createPendingPayment(
                    $appointment->id,
                    $service->payment_mode,
                    $amount,
                    $settings->currency_unit ?? config('booking.defaults.currency_unit', 'IRR')
                );
                $gateway = $this->paymentService->startGateway($payment);
            }

            $hold->delete();

            // --- Triggers ---
            $this->triggerWorkflow('appointment_created', $appointment);

            // تریگرهای اختصاصی بر اساس وضعیت اولیه رزرو آنلاین
            if ($appointment->status === Appointment::STATUS_CONFIRMED) {
                $this->triggerWorkflow('created_online_confirmed', $appointment);
                $this->onAppointmentConfirmed($appointment);
            } elseif ($appointment->status === Appointment::STATUS_PENDING) {
                $this->triggerWorkflow('created_online_pending', $appointment);
            }

            $this->audit->log(
                action: 'APPOINTMENT_CREATED_ONLINE',
                entityType: 'APPOINTMENT',
                entityId: $appointment->id,
                userId: null,
                before: null,
                after: $appointment->toArray(),
                meta: ['hold_id' => $holdId]
            );

            return [
                'appointment' => $appointment->fresh(['service', 'provider', 'client']),
                'payment' => $payment,
                'gateway' => $gateway,
            ];
        });
    }

    /**
     * Mark a payment as PAID and confirm appointment if it was pending.
     */
    public function markPaymentPaid(int $paymentId, ?string $gatewayRef = null): BookingPayment
    {
        return DB::transaction(function () use ($paymentId, $gatewayRef) {
            $payment = BookingPayment::query()->whereKey($paymentId)->lockForUpdate()->firstOrFail();

            if ($payment->status === BookingPayment::STATUS_PAID) {
                return $payment;
            }

            $payment->status = BookingPayment::STATUS_PAID;
            $payment->gateway_ref = $gatewayRef ?: $payment->gateway_ref;
            $payment->paid_at = now();
            $payment->save();

            $appt = Appointment::query()->whereKey($payment->appointment_id)->lockForUpdate()->first();
            if ($appt && $appt->status === Appointment::STATUS_PENDING_PAYMENT) {
                $appt->status = Appointment::STATUS_CONFIRMED;
                $appt->save();

                $this->triggerStatusWorkflows($appt, Appointment::STATUS_PENDING_PAYMENT);
                $this->onAppointmentConfirmed($appt);

                $this->audit->log(
                    action: 'PAYMENT_PAID_AND_APPOINTMENT_CONFIRMED',
                    entityType: 'APPOINTMENT',
                    entityId: $appt->id,
                    userId: null,
                    before: null,
                    after: $appt->toArray(),
                    meta: ['payment_id' => $paymentId]
                );
            }

            return $payment;
        });
    }

    /**
     * Operator/Admin creates appointment without hold.
     */
    public function createAppointmentByOperator(
        int $serviceId,
        int $providerUserId,
        int $clientId,
        string $startAtUtcIso,
        string $endAtUtcIso,
        ?int $createdByUserId = null,
        ?string $notes = null,
        ?array $appointmentFormResponse = null
    ): Appointment {
        $startUtc = Carbon::parse($startAtUtcIso, 'UTC');
        $endUtc   = Carbon::parse($endAtUtcIso, 'UTC');

        if ($endUtc->lte($startUtc)) {
            throw new \InvalidArgumentException('Invalid slot time range.');
        }

        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
        $localDate  = $startUtc->copy()->timezone($scheduleTz)->toDateString();

        return DB::transaction(function () use ($serviceId, $providerUserId, $clientId, $startUtc, $endUtc, $localDate, $createdByUserId, $notes, $appointmentFormResponse) {
            $this->lockDayAndSlot($serviceId, $providerUserId, $localDate, $startUtc, $endUtc);
            $this->assertCapacityAvailable($serviceId, $providerUserId, $localDate, $startUtc, $endUtc);

            $appointment = Appointment::query()->create([
                'service_id' => $serviceId,
                'provider_user_id' => $providerUserId,
                'client_id' => $clientId,
                'status' => Appointment::STATUS_CONFIRMED,
                'start_at_utc' => $startUtc,
                'end_at_utc' => $endUtc,
                'created_by_type' => Appointment::CREATED_BY_OPERATOR,
                'created_by_user_id' => $createdByUserId,
                'notes' => $notes,
                'appointment_form_response_json' => $appointmentFormResponse,
            ]);

            // --- Triggers ---
            $this->triggerWorkflow('appointment_created', $appointment);
            $this->triggerWorkflow('created_operator_confirmed', $appointment); // تریگر اختصاصی اپراتور

            $this->onAppointmentConfirmed($appointment);

            $this->audit->log(
                action: 'APPOINTMENT_CREATED_OPERATOR',
                entityType: 'APPOINTMENT',
                entityId: $appointment->id,
                userId: $createdByUserId,
                before: null,
                after: $appointment->toArray(),
                meta: null
            );

            return $appointment->fresh(['service', 'provider', 'client']);
        });
    }

    /**
     * Cancel appointment.
     */
    public function cancelAppointment(Appointment $appointment, string $cancelStatus, ?string $reason, ?int $authUserId): Appointment
    {
        return DB::transaction(function () use ($appointment, $cancelStatus, $reason, $authUserId) {
            $appointment = Appointment::query()->whereKey($appointment->id)->lockForUpdate()->firstOrFail();
            $before = $appointment->toArray();
            $previousStatus = $appointment->status;

            $appointment->status = $cancelStatus;
            $appointment->cancel_reason = $reason;
            $appointment->save();

            $this->triggerStatusWorkflows($appointment, $previousStatus);
            $this->cancelFutureReminders($appointment);

            if (config('booking.integrations.tasks.enabled', true) && config('booking.integrations.tasks.create_followup_on_cancel', false)) {
                $this->createFollowUpTask($appointment, $authUserId, 'لغو نوبت - پیگیری با مشتری');
            }

            $this->audit->log(
                action: 'APPOINTMENT_CANCELED',
                entityType: 'APPOINTMENT',
                entityId: $appointment->id,
                userId: $authUserId,
                before: $before,
                after: $appointment->toArray(),
                meta: null
            );

            return $appointment;
        });
    }

    /**
     * Mark as no-show.
     */
    public function markNoShow(Appointment $appointment, ?int $authUserId): Appointment
    {
        return DB::transaction(function () use ($appointment, $authUserId) {
            $appointment = Appointment::query()->whereKey($appointment->id)->lockForUpdate()->firstOrFail();
            $before = $appointment->toArray();
            $previousStatus = $appointment->status;

            $appointment->status = Appointment::STATUS_NO_SHOW;
            $appointment->save();

            $this->triggerStatusWorkflows($appointment, $previousStatus);

            if (config('booking.integrations.tasks.enabled', true) && config('booking.integrations.tasks.create_followup_on_no_show', true)) {
                $this->createFollowUpTask($appointment, $authUserId, 'عدم حضور - پیگیری با مشتری');
            }

            $this->audit->log(
                action: 'APPOINTMENT_NO_SHOW',
                entityType: 'APPOINTMENT',
                entityId: $appointment->id,
                userId: $authUserId,
                before: $before,
                after: $appointment->toArray(),
                meta: null
            );

            return $appointment;
        });
    }

    /**
     * Reschedule.
     */
    public function rescheduleAppointment(
        Appointment $appointment,
        string $newStartAtUtcIso,
        string $newEndAtUtcIso,
        ?int $authUserId
    ): Appointment {
        $newStartUtc = Carbon::parse($newStartAtUtcIso, 'UTC');
        $newEndUtc   = Carbon::parse($newEndAtUtcIso, 'UTC');

        if ($newEndUtc->lte($newStartUtc)) {
            throw new \InvalidArgumentException('Invalid slot time range.');
        }

        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
        $localDate  = $newStartUtc->copy()->timezone($scheduleTz)->toDateString();

        return DB::transaction(function () use ($appointment, $newStartUtc, $newEndUtc, $localDate, $authUserId) {
            $appointment = Appointment::query()->whereKey($appointment->id)->lockForUpdate()->firstOrFail();

            $this->lockDayAndSlot($appointment->service_id, $appointment->provider_user_id, $localDate, $newStartUtc, $newEndUtc);
            $this->assertCapacityAvailable($appointment->service_id, $appointment->provider_user_id, $localDate, $newStartUtc, $newEndUtc);

            $before = $appointment->toArray();
            $previousStatus = $appointment->status;

            $new = Appointment::query()->create([
                'service_id' => $appointment->service_id,
                'provider_user_id' => $appointment->provider_user_id,
                'client_id' => $appointment->client_id,
                'status' => Appointment::STATUS_CONFIRMED,
                'start_at_utc' => $newStartUtc,
                'end_at_utc' => $newEndUtc,
                'created_by_type' => Appointment::CREATED_BY_OPERATOR,
                'created_by_user_id' => $authUserId,
                'notes' => $appointment->notes,
                'appointment_form_response_json' => $appointment->appointment_form_response_json,
                'rescheduled_from_appointment_id' => $appointment->id,
            ]);

            $appointment->status = Appointment::STATUS_RESCHEDULED;
            $appointment->save();

            $this->cancelFutureReminders($appointment);
            $this->onAppointmentConfirmed($new);

            $this->triggerStatusWorkflows($appointment, $previousStatus);
            $this->triggerWorkflow('appointment_created', $new);
            $this->triggerWorkflow('created_operator_confirmed', $new); // فرض بر این است که جابجایی توسط اپراتور انجام شده

            $this->audit->log(
                action: 'APPOINTMENT_RESCHEDULED',
                entityType: 'APPOINTMENT',
                entityId: $new->id,
                userId: $authUserId,
                before: $before,
                after: $new->toArray(),
                meta: ['old_appointment_id' => $appointment->id]
            );

            return $new->fresh(['service', 'provider', 'client']);
        });
    }

    // ... (Lock and Capacity methods remain unchanged) ...
    protected function lockDayAndSlot(int $serviceId, int $providerUserId, string $localDate, Carbon $startUtc, Carbon $endUtc): void
    {
        try {
            BookingDayLock::query()->create([
                'service_id' => $serviceId,
                'provider_user_id' => $providerUserId,
                'local_date' => $localDate,
            ]);
        } catch (QueryException $e) {}

        BookingDayLock::query()
            ->where('service_id', $serviceId)
            ->where('provider_user_id', $providerUserId)
            ->whereDate('local_date', $localDate)
            ->lockForUpdate()
            ->first();

        try {
            BookingSlotLock::query()->create([
                'service_id' => $serviceId,
                'provider_user_id' => $providerUserId,
                'start_at_utc' => $startUtc,
                'end_at_utc' => $endUtc,
            ]);
        } catch (QueryException $e) {}

        BookingSlotLock::query()
            ->where('service_id', $serviceId)
            ->where('provider_user_id', $providerUserId)
            ->where('start_at_utc', $startUtc)
            ->where('end_at_utc', $endUtc)
            ->lockForUpdate()
            ->first();
    }

    protected function assertCapacityAvailable(int $serviceId, int $providerUserId, string $localDate, Carbon $startUtc, Carbon $endUtc, ?int $excludeHoldId = null): void
    {
        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
        $day = Carbon::createFromFormat('Y-m-d', $localDate, $scheduleTz)->startOfDay();
        $policy = $this->engine->resolveDayPolicy($serviceId, $providerUserId, $day);

        if ($policy['is_closed']) {
            throw new \RuntimeException('This day is closed.');
        }

        $this->assertTimeWithinPolicy($localDate, $scheduleTz, $startUtc, $endUtc, $policy);

        $capSlot = (int) ($policy['capacity_per_slot'] ?? 0);
        $capDay  = $policy['capacity_per_day'] !== null ? (int) $policy['capacity_per_day'] : null;
        $statuses = (array) config('booking.capacity_consuming_statuses', []);

        $slotBooked = Appointment::query()
            ->where('service_id', $serviceId)
            ->where('provider_user_id', $providerUserId)
            ->whereIn('status', $statuses)
            ->where('start_at_utc', '<', $endUtc)
            ->where('end_at_utc', '>', $startUtc)
            ->count();

        $slotHeldQ = BookingSlotHold::query()
            ->where('service_id', $serviceId)
            ->where('provider_user_id', $providerUserId)
            ->where('expires_at_utc', '>', now('UTC'))
            ->where('start_at_utc', '<', $endUtc)
            ->where('end_at_utc', '>', $startUtc);

        if ($excludeHoldId) {
            $slotHeldQ->where('id', '!=', $excludeHoldId);
        }
        $slotHeld = $slotHeldQ->count();

        if ($capSlot > 0 && ($slotBooked + $slotHeld) >= $capSlot) {
            throw new \RuntimeException('Slot capacity is full.');
        }

        if ($capDay !== null && $capDay > 0) {
            $dayStartUtc = $day->copy()->timezone('UTC');
            $dayEndUtc   = $day->copy()->addDay()->timezone('UTC');

            $dayBooked = Appointment::query()
                ->where('service_id', $serviceId)
                ->where('provider_user_id', $providerUserId)
                ->whereIn('status', $statuses)
                ->where('start_at_utc', '>=', $dayStartUtc)
                ->where('start_at_utc', '<', $dayEndUtc)
                ->count();

            $dayHeldQ = BookingSlotHold::query()
                ->where('service_id', $serviceId)
                ->where('provider_user_id', $providerUserId)
                ->where('expires_at_utc', '>', now('UTC'))
                ->where('start_at_utc', '>=', $dayStartUtc)
                ->where('start_at_utc', '<', $dayEndUtc);

            if ($excludeHoldId) {
                $dayHeldQ->where('id', '!=', $excludeHoldId);
            }
            $dayHeld = $dayHeldQ->count();

            if (($dayBooked + $dayHeld) >= $capDay) {
                throw new \RuntimeException('Day capacity is full.');
            }
        }
    }

    public function validateSlotAvailableForUpdate(int $serviceId, int $providerUserId, string $localDate, Carbon $startUtc, Carbon $endUtc, ?int $excludeAppointmentId = null): void
    {
        $this->assertCapacityAvailable($serviceId, $providerUserId, $localDate, $startUtc, $endUtc, null);
    }

    protected function assertTimeWithinPolicy(string $localDate, string $scheduleTz, Carbon $startUtc, Carbon $endUtc, array $policy): void
    {
        $startLocal = $startUtc->copy()->timezone($scheduleTz);
        $endLocal   = $endUtc->copy()->timezone($scheduleTz);

        if ($startLocal->toDateString() !== $localDate || $endLocal->toDateString() !== $localDate) {
            throw new \RuntimeException('Slot crosses day boundary.');
        }

        $windows = $policy['work_windows'] ?? [];
        $withinWindow = false;
        foreach ($windows as $win) {
            $startLocalWindow = Carbon::createFromFormat('Y-m-d H:i', "{$localDate} {$win['start']}", $scheduleTz);
            $endLocalWindow   = Carbon::createFromFormat('Y-m-d H:i', "{$localDate} {$win['end']}", $scheduleTz);
            if ($startLocal->gte($startLocalWindow) && $endLocal->lte($endLocalWindow)) {
                $withinWindow = true;
                break;
            }
        }
        if (!$withinWindow) {
            throw new \RuntimeException('Slot is outside work windows.');
        }

        foreach (($policy['breaks'] ?? []) as $break) {
            if (empty($break['start_local']) || empty($break['end_local'])) continue;
            $breakStart = Carbon::createFromFormat('Y-m-d H:i', "{$localDate} {$break['start_local']}", $scheduleTz);
            $breakEnd   = Carbon::createFromFormat('Y-m-d H:i', "{$localDate} {$break['end_local']}", $scheduleTz);
            if ($startLocal->lt($breakEnd) && $endLocal->gt($breakStart)) {
                throw new \RuntimeException('Slot overlaps with break.');
            }
        }
    }

    protected function onAppointmentConfirmed(Appointment $appointment): void
    {
        $this->syncReminders($appointment);
        $this->triggerWorkflow('appointment_confirmed', $appointment);
        $this->triggerWorkflow('status_confirmed', $appointment); // تریگر وضعیت تایید شده
    }

    protected function syncReminders(Appointment $appointment): void
    {
        if (!config('booking.integrations.reminders.enabled', true)) return;

        if (class_exists('Modules\\Reminders\\Entities\\Reminder') && class_exists('Modules\\Workflows\\Entities\\Workflow')) {
            $Reminder = \Modules\Reminders\Entities\Reminder::class;
            $Workflow = \Modules\Workflows\Entities\Workflow::class;

            $Reminder::query()
                ->where('related_type', 'APPOINTMENT')
                ->where('related_id', $appointment->id)
                ->where('channel', 'WORKFLOW')
                ->where('status', $Reminder::STATUS_OPEN)
                ->delete();

            $activeWorkflows = $Workflow::query()
                ->where('is_active', true)
                ->where('key', 'like', 'appointment_reminder_%')
                ->get();

            $offsetMap = [
                'appointment_reminder_1_hour_before'  => -60,
                'appointment_reminder_2_hours_before' => -120,
                'appointment_reminder_1_day_before'   => -1440,
                'appointment_reminder_2_days_before'  => -2880,
                'appointment_reminder_3_days_before'  => -4320,
                'appointment_reminder_7_days_before'  => -10080,
            ];

            foreach ($activeWorkflows as $wf) {
                $offset = $offsetMap[$wf->key] ?? null;
                if ($offset !== null) {
                    $remindAt = $appointment->start_at_utc->copy()->addMinutes($offset);
                    if ($remindAt->gt(now())) {
                        $Reminder::query()->create([
                            'user_id'      => $appointment->provider_user_id,
                            'related_type' => 'APPOINTMENT',
                            'related_id'   => $appointment->id,
                            'remind_at'    => $remindAt,
                            'channel'      => 'WORKFLOW',
                            'message'      => $wf->key,
                            'status'       => $Reminder::STATUS_OPEN,
                            'is_sent'      => false,
                        ]);
                    }
                }
            }
        }
    }

    protected function buildReminderMessage(Appointment $appointment, string $target): string
    {
        $scheduleTz = config('booking.timezones.display_default', 'Asia/Tehran');
        $dt = $appointment->start_at_utc->copy()->timezone($scheduleTz)->format('Y-m-d H:i');
        $serviceName = $appointment->service?->name ?? 'سرویس';
        $clientName = $appointment->client?->full_name ?? 'مشتری';
        if ($target === 'PROVIDER') return "یادآوری نوبت: {$serviceName} برای {$clientName} در {$dt}";
        return "یادآوری نوبت شما: {$serviceName} در {$dt}";
    }

    protected function cancelFutureReminders(Appointment $appointment): void
    {
        if (class_exists('Modules\\Reminders\\Entities\\Reminder')) {
            \Modules\Reminders\Entities\Reminder::query()
                ->where('related_type', 'APPOINTMENT')
                ->where('related_id', $appointment->id)
                ->where('is_sent', false)
                ->where('remind_at', '>', now())
                ->delete();
        }
    }

    protected function createFollowUpTask(Appointment $appointment, ?int $creatorId, string $title): void
    {
        if (!class_exists('Modules\\Tasks\\Entities\\Task')) return;
        $Task = \Modules\Tasks\Entities\Task::class;
        $assigneeId = $creatorId ?: $appointment->provider_user_id;
        $Task::query()->create([
            'title' => $title,
            'description' => $this->buildReminderMessage($appointment, 'CLIENT'),
            'task_type' => $Task::TYPE_FOLLOW_UP,
            'assignee_id' => $assigneeId,
            'creator_id' => $creatorId,
            'status' => $Task::STATUS_TODO,
            'priority' => $Task::PRIORITY_MEDIUM,
            'due_at' => now()->addHours(1),
            'related_type' => 'APPOINTMENT',
            'related_id' => $appointment->id,
            'meta' => [],
        ]);
    }

    public function triggerWorkflow(string $key, Appointment $appointment): void
    {
        if (!config('booking.integrations.workflows.enabled', true)) return;

        if (class_exists('Modules\\Workflows\\Entities\\Workflow')) {
            $eventWorkflows = Workflow::query()
                ->where('is_active', true)
                ->whereHas('triggers', function ($q) use ($key) {
                    $q->where('type', WorkflowTrigger::TYPE_EVENT)
                      ->whereJsonContains('config->event_key', $key);
                })
                ->get();

            if ($eventWorkflows->isNotEmpty()) {
                $engine = app(\Modules\Workflows\Services\WorkflowEngine::class);
                foreach ($eventWorkflows as $wf) {
                    Log::info("[Booking] Triggering EVENT workflow '{$wf->name}' for key '$key'");
                    $engine->startWorkflow($wf, 'APPOINTMENT', $appointment->id);
                }
            }
        }
    }

    public function triggerStatusWorkflows(Appointment $appointment, ?string $previousStatus = null): void
    {
        // 1. تریگر کلی تغییر وضعیت
        $this->triggerWorkflow('appointment_status_changed', $appointment);

        // 2. تریگر اختصاصی برای وضعیت جدید (مثلاً status_pending, status_confirmed)
        $this->triggerWorkflow('status_' . $appointment->status, $appointment);

        // 3. تریگرهای گروهی (برای سازگاری با قبل یا گروه‌بندی منطقی)
        if (in_array($appointment->status, [Appointment::STATUS_CANCELED_BY_ADMIN, Appointment::STATUS_CANCELED_BY_CLIENT])) {
            $this->triggerWorkflow('status_canceled', $appointment);
            $this->triggerWorkflow('appointment_canceled', $appointment); // Legacy
        }

        if ($appointment->status === Appointment::STATUS_NO_SHOW) {
            $this->triggerWorkflow('appointment_no_show', $appointment); // Legacy
        }

        if ($appointment->status === Appointment::STATUS_DONE) {
            $this->triggerWorkflow('appointment_done', $appointment); // Legacy
        }

        if ($appointment->status === Appointment::STATUS_RESCHEDULED) {
            $this->triggerWorkflow('appointment_rescheduled', $appointment); // Legacy
        }
    }
}
