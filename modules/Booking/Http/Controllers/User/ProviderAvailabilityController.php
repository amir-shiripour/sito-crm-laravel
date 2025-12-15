<?php

namespace Modules\Booking\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
            $weekdayInt = (int) $weekday;
            if ($weekdayInt < 0 || $weekdayInt > 6) {
                continue;
            }

            $payload = [
                'scope_type' => BookingAvailabilityRule::SCOPE_SERVICE_PROVIDER,
                'scope_id'   => $provider->id,
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
