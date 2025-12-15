<?php

namespace Modules\Booking\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Booking\Entities\BookingForm;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Services\AuditLogger;

class FormController extends Controller
{
    public function __construct(protected AuditLogger $audit)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $settings = BookingSetting::current();

        $q = BookingForm::query();

        if ($settings->form_management_scope === 'OWN' && !$user->can('booking.forms.manage') && !$user->hasRole('super-admin')) {
            $q->where('creator_id', $user->id);
        }

        return response()->json(['data' => $q->orderByDesc('id')->paginate((int) ($request->query('per_page', 50)))]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([BookingForm::STATUS_ACTIVE, BookingForm::STATUS_INACTIVE])],
            'schema_json' => ['required', 'array'],
        ]);

        $data['creator_id'] = $user->id;

        $form = BookingForm::query()->create($data);

        $this->audit->log('FORM_CREATED', 'booking_forms', $form->id, null, $form->toArray());

        return response()->json(['data' => $form], 201);
    }

    public function update(Request $request, BookingForm $form)
    {
        $user = $request->user();
        $settings = BookingSetting::current();

        if ($settings->form_management_scope === 'OWN' && !$user->can('booking.forms.manage') && !$user->hasRole('super-admin')) {
            if ((int) $form->creator_id !== (int) $user->id) {
                return response()->json(['message' => 'Access denied (own-only).'], 403);
            }
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in([BookingForm::STATUS_ACTIVE, BookingForm::STATUS_INACTIVE])],
            'schema_json' => ['sometimes', 'array'],
        ]);

        $before = $form->toArray();
        $form->fill($data);
        $form->save();
        $this->audit->log('FORM_UPDATED', 'booking_forms', $form->id, $before, $form->toArray());

        return response()->json(['data' => $form]);
    }

    public function destroy(Request $request, BookingForm $form)
    {
        $user = $request->user();
        $settings = BookingSetting::current();

        if ($settings->form_management_scope === 'OWN' && !$user->can('booking.forms.manage') && !$user->hasRole('super-admin')) {
            if ((int) $form->creator_id !== (int) $user->id) {
                return response()->json(['message' => 'Access denied (own-only).'], 403);
            }
        }

        $before = $form->toArray();
        $form->delete();
        $this->audit->log('FORM_DELETED', 'booking_forms', $form->id, $before, null);
        return response()->json(['ok' => true]);
    }
}
