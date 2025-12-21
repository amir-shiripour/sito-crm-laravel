<?php

namespace Modules\Booking\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Modules\Booking\Entities\BookingAvailabilityRule;
use Modules\Booking\Entities\BookingSetting;

class ProviderAvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));

        $settings = BookingSetting::current();
        $roleIds  = (array) ($settings->allowed_roles ?? []);

        $providersQuery = User::query();

        // فقط نقش‌های مجاز به‌عنوان ارائه‌دهنده
        if (!empty($roleIds)) {
            $providersQuery->whereHas('roles', function ($query) use ($roleIds) {
                $query->whereIn('id', $roleIds);
            });
        }

        if ($q !== '') {
            $providersQuery->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $providers = $providersQuery
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('booking::user.providers.availability_index', compact('providers', 'q'));
    }

    public function edit(User $provider)
    {
        // جلوگیری از دسترسی به یوزرهای غیر Provider
        $settings = BookingSetting::current();
        $roleIds  = (array) ($settings->allowed_roles ?? []);

        if (!empty($roleIds)) {
            $isProvider = $provider->roles()
                ->whereIn('id', $roleIds)
                ->exists();

            if (!$isProvider) {
                abort(404);
            }
        }

        $rules = BookingAvailabilityRule::query()
            ->where('scope_type', BookingAvailabilityRule::SCOPE_SERVICE_PROVIDER)
            ->where('scope_id', $provider->id)
            ->get()
            ->keyBy('weekday');

        return view('booking::user.providers.availability', compact('provider', 'rules'));
    }

    public function update(Request $request, User $provider)
    {
        // جلوگیری از دسترسی به یوزرهای غیر Provider
        $settings = BookingSetting::current();
        $roleIds  = (array) ($settings->allowed_roles ?? []);

        if (!empty($roleIds)) {
            $isProvider = $provider->roles()
                ->whereIn('id', $roleIds)
                ->exists();

            if (!$isProvider) {
                abort(404);
            }
        }

        $rulesInput = (array) $request->input('rules', []);

        foreach ($rulesInput as $day => $row) {
            foreach (['work_start_local', 'work_end_local'] as $f) {
                if (array_key_exists($f, $row)) {
                    $v = trim((string) $row[$f]);
                    $rulesInput[$day][$f] = ($v === '') ? null : substr($v, 0, 5);
                }
            }

            if (isset($row['breaks']) && is_array($row['breaks'])) {
                foreach ($row['breaks'] as $i => $br) {
                    foreach (['start_local', 'end_local'] as $bf) {
                        if (array_key_exists($bf, $br)) {
                            $v = trim((string) $br[$bf]);
                            $rulesInput[$day]['breaks'][$i][$bf] = ($v === '') ? null : substr($v, 0, 5);
                        }
                    }
                }
            }
        }

        $request->merge(['rules' => $rulesInput]);

        $data = $request->validate([
            'rules' => ['required', 'array'],

            'rules.*.weekday' => ['nullable', 'integer', 'min:0', 'max:6'],
            'rules.*.is_closed' => ['required', Rule::in(['0', '1'])],
            'rules.*.work_start_local' => ['nullable', 'date_format:H:i'],
            'rules.*.work_end_local'   => ['nullable', 'date_format:H:i'],

            'rules.*.slot_duration_minutes' => ['nullable', 'integer', 'min:5', 'max:720'],
            'rules.*.capacity_per_slot'     => ['nullable', 'integer', 'min:0', 'max:1000'],
            'rules.*.capacity_per_day'      => ['nullable', 'integer', 'min:0', 'max:10000'],

            'rules.*.breaks' => ['nullable', 'array'],
            'rules.*.breaks.*.start_local' => ['required_with:rules.*.breaks', 'date_format:H:i'],
            'rules.*.breaks.*.end_local'   => ['required_with:rules.*.breaks', 'date_format:H:i'],
        ]);

        foreach ($data['rules'] as $weekday => $ruleRow) {
            $weekdayInt = (int) Arr::get($ruleRow, 'weekday', $weekday);
            if ($weekdayInt < 0 || $weekdayInt > 6) {
                continue;
            }

            $isClosed = ((string) Arr::get($ruleRow, 'is_closed', '0') === '1');
            $workStart = Arr::get($ruleRow, 'work_start_local');
            $workEnd = Arr::get($ruleRow, 'work_end_local');

            $slotDur = Arr::get($ruleRow, 'slot_duration_minutes');
            $capSlot = Arr::get($ruleRow, 'capacity_per_slot');
            $capDay = Arr::get($ruleRow, 'capacity_per_day');

            $slotDur = ($slotDur === '' || $slotDur === null) ? null : (int) $slotDur;
            $capSlot = ($capSlot === '' || $capSlot === null) ? null : (int) $capSlot;
            $capDay = ($capDay === '' || $capDay === null) ? null : (int) $capDay;

            $payload = [
                'scope_type' => BookingAvailabilityRule::SCOPE_SERVICE_PROVIDER,
                'scope_id'   => $provider->id,
                'weekday'    => $weekdayInt,

                'is_closed'        => $isClosed,
                'work_start_local' => ($workStart === '' ? null : $workStart),
                'work_end_local'   => ($workEnd === '' ? null : $workEnd),

                'slot_duration_minutes' => $slotDur,
                'capacity_per_slot'     => $capSlot,
                'capacity_per_day'      => $capDay,

                'breaks_json' => isset($ruleRow['breaks'])
                    ? array_values($ruleRow['breaks'] ?? [])
                    : null,
            ];

            if ($payload['is_closed']) {
                $payload['work_start_local'] = null;
                $payload['work_end_local'] = null;
                $payload['breaks_json'] = [];
                $payload['slot_duration_minutes'] = null;
                $payload['capacity_per_slot'] = null;
                $payload['capacity_per_day'] = null;
            }

            $allNull =
                $payload['is_closed'] === false &&
                $payload['work_start_local'] === null &&
                $payload['work_end_local'] === null &&
                $payload['slot_duration_minutes'] === null &&
                $payload['capacity_per_slot'] === null &&
                $payload['capacity_per_day'] === null &&
                $payload['breaks_json'] === null;

            $existing = BookingAvailabilityRule::query()
                ->where('scope_type', BookingAvailabilityRule::SCOPE_SERVICE_PROVIDER)
                ->where('scope_id', $provider->id)
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
                    'scope_type' => BookingAvailabilityRule::SCOPE_SERVICE_PROVIDER,
                    'scope_id'   => $provider->id,
                    'weekday'    => $weekdayInt,
                ],
                $payload
            );
        }

        return redirect()
            ->route('user.booking.providers.availability.edit', $provider)
            ->with('success', 'برنامه زمانی ارائه‌دهنده با موفقیت ذخیره شد.');
    }
}
