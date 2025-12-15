<?php

namespace Modules\Booking\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingPayment;
use Modules\Booking\Services\AppointmentService;
use Modules\Booking\Services\AuditLogger;

class AppointmentController extends Controller
{
    public function __construct(protected AppointmentService $service, protected AuditLogger $audit)
    {
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_id' => ['required', 'integer'],
            'provider_user_id' => ['required', 'integer', 'exists:users,id'],
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'start_at_utc' => ['required', 'date'],
            'end_at_utc' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'appointment_form_response_json' => ['nullable', 'array'],
        ]);

        $appt = $this->service->createAppointmentByOperator(
            (int) $data['service_id'],
            (int) $data['provider_user_id'],
            (int) $data['client_id'],
            $data['start_at_utc'],
            $data['end_at_utc'],
            createdByUserId: $request->user()?->id,
            notes: $data['notes'] ?? null,
            appointmentFormResponse: $data['appointment_form_response_json'] ?? null,
        );

        return response()->json(['data' => $appt], 201);
    }

    public function onlineStart(Request $request)
    {
        $data = $request->validate([
            'service_id' => ['required', 'integer'],
            'provider_user_id' => ['required', 'integer', 'exists:users,id'],
            'start_at_utc' => ['required', 'date'],
            'end_at_utc' => ['required', 'date'],
            'client_temp_key' => ['nullable', 'string', 'max:128'],
        ]);

        $hold = $this->service->startOnlineHold(
            (int) $data['service_id'],
            (int) $data['provider_user_id'],
            $data['start_at_utc'],
            $data['end_at_utc'],
            $data['client_temp_key'] ?? null,
        );

        return response()->json([
            'data' => [
                'hold_id' => $hold->id,
                'expires_at_utc' => $hold->expires_at_utc?->toIso8601String(),
            ],
        ], 201);
    }

    public function onlineConfirm(Request $request)
    {
        $data = $request->validate([
            'hold_id' => ['required', 'integer'],
            'client' => ['required', 'array'],
            'client.full_name' => ['required', 'string', 'max:255'],
            'client.phone' => ['nullable', 'string', 'max:50'],
            'client.email' => ['nullable', 'email', 'max:255'],
            'client.national_code' => ['nullable', 'string', 'max:50'],
            'client.meta' => ['nullable', 'array'],

            'appointment_form_response_json' => ['nullable', 'array'],
            'pay_now' => ['nullable', 'boolean'],
        ]);

        $result = $this->service->confirmOnlineHold(
            (int) $data['hold_id'],
            $data['client'],
            $data['appointment_form_response_json'] ?? null,
            (bool) ($data['pay_now'] ?? true),
        );

        return response()->json([
            'data' => [
                'appointment' => $result['appointment'],
                'payment' => $result['payment'],
                'gateway' => $result['gateway'],
            ]
        ]);
    }

    public function patch(Request $request, Appointment $appointment)
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['cancel', 'reschedule', 'no_show', 'done'])],
            'cancel_status' => ['nullable', Rule::in([Appointment::STATUS_CANCELED_BY_ADMIN, Appointment::STATUS_CANCELED_BY_CLIENT])],
            'cancel_reason' => ['nullable', 'string', 'max:255'],

            'new_start_at_utc' => ['nullable', 'date'],
            'new_end_at_utc' => ['nullable', 'date'],
        ]);

        $authId = $request->user()?->id;

        if ($data['action'] === 'cancel') {
            $status = $data['cancel_status'] ?? Appointment::STATUS_CANCELED_BY_ADMIN;
            $appt = $this->service->cancelAppointment($appointment, $status, $data['cancel_reason'] ?? null, $authId);
            return response()->json(['data' => $appt]);
        }

        if ($data['action'] === 'reschedule') {
            $appt = $this->service->rescheduleAppointment($appointment, $data['new_start_at_utc'], $data['new_end_at_utc'], $authId);
            return response()->json(['data' => $appt]);
        }

        if ($data['action'] === 'no_show') {
            $appt = $this->service->markNoShow($appointment, $authId);
            return response()->json(['data' => $appt]);
        }

        if ($data['action'] === 'done') {
            $before = $appointment->toArray();
            $appointment->status = Appointment::STATUS_DONE;
            $appointment->save();
            $this->audit->log('APPOINTMENT_DONE', 'appointments', $appointment->id, $before, $appointment->toArray());
            return response()->json(['data' => $appointment]);
        }

        return response()->json(['message' => 'Unsupported action.'], 400);
    }

    public function markPaymentPaid(Request $request, BookingPayment $payment)
    {
        $data = $request->validate([
            'gateway_ref' => ['nullable', 'string', 'max:255'],
        ]);

        $p = $this->service->markPaymentPaid($payment->id, $data['gateway_ref'] ?? null);

        return response()->json(['data' => $p->fresh()]);
    }
}
