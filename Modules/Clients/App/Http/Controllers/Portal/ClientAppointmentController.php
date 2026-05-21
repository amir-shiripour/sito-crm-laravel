<?php

namespace Modules\Clients\App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Booking\Entities\Appointment;

class ClientAppointmentController extends Controller
{
    public function show(Appointment $appointment)
    {
        // Ensure the client can only view their own appointments
        if ($appointment->client_id !== auth('client')->id()) {
            abort(403);
        }

        // Load necessary relations
        $appointment->load(['service.appointmentForm', 'provider', 'payments']);

        $rawFormResponses = $appointment->appointment_form_response_json ?? [];
        $formResponses = [];

        if (!empty($rawFormResponses) && $appointment->service && $appointment->service->appointmentForm) {
            $form = $appointment->service->appointmentForm;
            $formSchema = $form->schema_json;
            $fieldMeta = [];

            if (isset($formSchema['fields']) && is_array($formSchema['fields'])) {
                foreach ($formSchema['fields'] as $field) {
                    if (isset($field['name'])) {
                        $fieldMeta[$field['name']] = [
                            'label' => $field['label'] ?? $field['name'],
                        ];
                    }
                }
            }

            foreach ($rawFormResponses as $key => $value) {
                $meta = $fieldMeta[$key] ?? ['label' => $key];
                $formResponses[] = [
                    'label' => $meta['label'],
                    'value' => $value,
                ];
            }
        } else if (!empty($rawFormResponses)) {
            // Fallback if form is not available, just use keys
            foreach ($rawFormResponses as $key => $value) {
                $formResponses[] = [
                    'label' => $key,
                    'value' => $value,
                ];
            }
        }

        return view('clients::portal.appointments.show', compact('appointment', 'formResponses'));
    }

    public function cancel(Appointment $appointment)
    {
        if ($appointment->client_id !== auth('client')->id()) {
            abort(403);
        }

        if (in_array($appointment->status, [Appointment::STATUS_CONFIRMED, Appointment::STATUS_PENDING, Appointment::STATUS_PENDING_PAYMENT, Appointment::STATUS_RESCHEDULED])) {
            $appointment->status = Appointment::STATUS_CANCELED_BY_CLIENT;
            $appointment->save();

            return back()->with('success', 'نوبت شما با موفقیت لغو شد.');
        }

        return back()->with('error', 'امکان لغو این نوبت وجود ندارد.');
    }
}
