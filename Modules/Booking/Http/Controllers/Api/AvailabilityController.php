<?php

namespace Modules\Booking\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Booking\Entities\BookingAvailabilityException;
use Modules\Booking\Entities\BookingAvailabilityRule;
use Modules\Booking\Services\BookingEngine;
use Modules\Booking\Services\AuditLogger;

class AvailabilityController extends Controller
{
    public function __construct(protected BookingEngine $engine, protected AuditLogger $audit)
    {
    }

    public function slots(Request $request)
    {
        $data = $request->validate([
            'service_id' => ['required', 'integer'],
            'provider_id' => ['required', 'integer'],
            'from_local_date' => ['required', 'date_format:Y-m-d'],
            'to_local_date' => ['required', 'date_format:Y-m-d'],
            'viewer_timezone' => ['nullable', 'string', 'max:64'],
        ]);

        $slots = $this->engine->generateSlots(
            (int) $data['service_id'],
            (int) $data['provider_id'],
            $data['from_local_date'],
            $data['to_local_date'],
            $data['viewer_timezone'] ?? null
        );

        return response()->json(['data' => $slots]);
    }

    public function storeRule(Request $request)
    {
        $data = $request->validate([
            'scope_type' => ['required', Rule::in([BookingAvailabilityRule::SCOPE_GLOBAL, BookingAvailabilityRule::SCOPE_SERVICE, BookingAvailabilityRule::SCOPE_SERVICE_PROVIDER])],
            'scope_id' => ['nullable', 'integer'],
            'weekday' => ['required', 'integer', 'min:0', 'max:6'],

            'is_closed' => ['required', 'boolean'],
            'work_start_local' => ['nullable', 'date_format:H:i'],
            'work_end_local' => ['nullable', 'date_format:H:i'],
            'breaks_json' => ['nullable', 'array'],
            'breaks_json.*.start_local' => ['required_with:breaks_json', 'date_format:H:i'],
            'breaks_json.*.end_local' => ['required_with:breaks_json', 'date_format:H:i'],

            'slot_duration_minutes' => ['nullable', 'integer', 'min:5', 'max:720'],
            'capacity_per_slot' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'capacity_per_day' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]);

        // For GLOBAL scope, force scope_id null
        if ($data['scope_type'] === BookingAvailabilityRule::SCOPE_GLOBAL) {
            $data['scope_id'] = null;
        }

        $rule = BookingAvailabilityRule::query()->updateOrCreate([
            'scope_type' => $data['scope_type'],
            'scope_id' => $data['scope_id'],
            'weekday' => (int) $data['weekday'],
        ], $data);

        $this->audit->log('AVAILABILITY_RULE_UPSERT', 'booking_availability_rules', $rule->id, null, $rule->toArray());

        return response()->json(['data' => $rule], 201);
    }

    public function storeException(Request $request)
    {
        $data = $request->validate([
            'scope_type' => ['required', Rule::in([BookingAvailabilityException::SCOPE_GLOBAL, BookingAvailabilityException::SCOPE_SERVICE, BookingAvailabilityException::SCOPE_SERVICE_PROVIDER])],
            'scope_id' => ['nullable', 'integer'],
            'local_date' => ['required', 'date_format:Y-m-d'],

            'is_closed' => ['required', 'boolean'],
            'override_work_windows_json' => ['nullable', 'array'],
            'override_work_windows_json.*.start_local' => ['required_with:override_work_windows_json', 'date_format:H:i'],
            'override_work_windows_json.*.end_local' => ['required_with:override_work_windows_json', 'date_format:H:i'],

            'override_breaks_json' => ['nullable', 'array'],
            'override_breaks_json.*.start_local' => ['required_with:override_breaks_json', 'date_format:H:i'],
            'override_breaks_json.*.end_local' => ['required_with:override_breaks_json', 'date_format:H:i'],

            'override_capacity_per_slot' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'override_capacity_per_day' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]);

        if ($data['scope_type'] === BookingAvailabilityException::SCOPE_GLOBAL) {
            $data['scope_id'] = null;
        }

        $ex = BookingAvailabilityException::query()->updateOrCreate([
            'scope_type' => $data['scope_type'],
            'scope_id' => $data['scope_id'],
            'local_date' => $data['local_date'],
        ], $data);

        $this->audit->log('AVAILABILITY_EXCEPTION_UPSERT', 'booking_availability_exceptions', $ex->id, null, $ex->toArray());

        return response()->json(['data' => $ex], 201);
    }
}
