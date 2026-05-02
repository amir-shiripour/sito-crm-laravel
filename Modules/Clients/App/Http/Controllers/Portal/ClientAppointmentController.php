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
        $appointment->load(['service', 'provider', 'payments']);

        return view('clients::portal.appointments.show', compact('appointment'));
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
