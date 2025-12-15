<?php

namespace Modules\Booking\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Booking\Entities\BookingAvailabilityRule;
use Modules\Booking\Entities\BookingService;

class ServiceAvailabilityController extends Controller
{
    public function edit(BookingService $service)
    {
        $rules = BookingAvailabilityRule::query()
            ->where('scope_type', BookingAvailabilityRule::SCOPE_SERVICE)
            ->where('scope_id', $service->id)
            ->get()
            ->keyBy('weekday');

        return view('booking::user.services.availability', compact('service', 'rules'));
    }

    public function update(Request $request, BookingService $service)
    {
        $data = $request->validate([
            'rules' => ['required', 'array'],

            'rules.*.is_closed' => ['required', Rule::in(['0', '1'])],
            'rules.*.work_start_local' => ['nullable', 'date_format:H:i'],
            'rules.*.work_end_local'   => ['nullable', 'date_format:H:i'],

            'rules.*.slot_duration_minutes' => ['nullable', 'integer', 'min:5', 'max:720'],
            'rules.*.capacity_per_slot'     => ['nullable', 'integer', 'min:1', 'max:1000'],
            'rules.*.capacity_per_day'      => ['nullable', 'integer', 'min:1', 'max:10000'],

            'rules.*.breaks' => ['nullable', 'array'],
            'rules.*.breaks.*.start_local' => ['required_with:rules.*.breaks', 'date_format:H:i'],
            'rules.*.breaks.*.end_local'   => ['required_with:rules.*.breaks', 'date_format:H:i'],
        ]);

        foreach ($data['rules'] as $weekday => $ruleRow) {
            $weekdayInt = (int)$weekday;
            if ($weekdayInt < 0 || $weekdayInt > 6) {
                continue;
            }

            $payload = [
                'scope_type' => BookingAvailabilityRule::SCOPE_SERVICE,
                'scope_id'   => $service->id,
                'weekday'    => $weekdayInt,

                'is_closed'        => (bool)($ruleRow['is_closed'] ?? false),
                'work_start_local' => $ruleRow['work_start_local'] ?: null,
                'work_end_local'   => $ruleRow['work_end_local'] ?: null,

                'slot_duration_minutes' => $ruleRow['slot_duration_minutes'] ?: null,
                'capacity_per_slot'     => $ruleRow['capacity_per_slot'] ?: null,
                'capacity_per_day'      => $ruleRow['capacity_per_day'] ?: null,

                'breaks_json' => isset($ruleRow['breaks']) ? array_values($ruleRow['breaks']) : null,
            ];

            $allNull =
                !$payload['is_closed'] &&
                !$payload['work_start_local'] &&
                !$payload['work_end_local'] &&
                !$payload['slot_duration_minutes'] &&
                !$payload['capacity_per_slot'] &&
                !$payload['capacity_per_day'] &&
                (empty($payload['breaks_json']) || $payload['breaks_json'] === []);

            $existing = BookingAvailabilityRule::query()
                ->where('scope_type', BookingAvailabilityRule::SCOPE_SERVICE)
                ->where('scope_id', $service->id)
                ->where('weekday', $weekdayInt)
                ->first();

            if ($allNull) {
                if ($existing) {
                    $existing->delete();
                }
                continue;
            }

            BookingAvailabilityRule::query()->updateOrCreate(
                [
                    'scope_type' => BookingAvailabilityRule::SCOPE_SERVICE,
                    'scope_id'   => $service->id,
                    'weekday'    => $weekdayInt,
                ],
                $payload
            );
        }

        return redirect()
            ->route('user.booking.services.availability.edit', $service)
            ->with('success', 'برنامه زمانی سرویس با موفقیت ذخیره شد.');
    }
}
