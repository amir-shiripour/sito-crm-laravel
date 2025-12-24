<?php

namespace Modules\Booking\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Booking\Entities\BookingAvailabilityException;

class ProviderExceptionController extends Controller
{
    public function index(User $provider)
    {
        $exceptions = BookingAvailabilityException::query()
            ->where('scope_type', BookingAvailabilityException::SCOPE_SERVICE_PROVIDER)
            ->where('scope_id', $provider->id)
            ->orderBy('local_date')
            ->paginate(30);

        return view('booking::user.providers.exceptions', compact('provider', 'exceptions'));
    }

    public function store(Request $request, User $provider)
    {
        $data = $request->validate([
            'local_date' => ['required', 'date_format:Y-m-d'],
            'is_closed'  => ['required', 'boolean'],

            'override_work_windows' => ['nullable', 'array'],
            'override_work_windows.*.start_local' => ['required_with:override_work_windows', 'date_format:H:i'],
            'override_work_windows.*.end_local'   => ['required_with:override_work_windows', 'date_format:H:i'],

            'override_breaks' => ['nullable', 'array'],
            'override_breaks.*.start_local' => ['required_with:override_breaks', 'date_format:H:i'],
            'override_breaks.*.end_local'   => ['required_with:override_breaks', 'date_format:H:i'],

            'override_capacity_per_slot' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'override_capacity_per_day'  => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]);

        $payload = [
            'scope_type' => BookingAvailabilityException::SCOPE_SERVICE_PROVIDER,
            'scope_id'   => $provider->id,
            'local_date' => $data['local_date'],
            'is_closed'  => (bool) $data['is_closed'],

            'override_work_windows_json' => isset($data['override_work_windows'])
                ? array_values($data['override_work_windows'])
                : null,

            'override_breaks_json' => isset($data['override_breaks'])
                ? array_values($data['override_breaks'])
                : null,

            'override_capacity_per_slot' => $data['override_capacity_per_slot'] ?? null,
            'override_capacity_per_day'  => $data['override_capacity_per_day'] ?? null,
        ];

        $allEmpty =
            !$payload['is_closed'] &&
            empty($payload['override_work_windows_json']) &&
            empty($payload['override_breaks_json']) &&
            !$payload['override_capacity_per_slot'] &&
            !$payload['override_capacity_per_day'];

        $existing = BookingAvailabilityException::query()
            ->where('scope_type', BookingAvailabilityException::SCOPE_SERVICE_PROVIDER)
            ->where('scope_id', $provider->id)
            ->where('local_date', $payload['local_date'])
            ->first();

        if ($allEmpty) {
            if ($existing) {
                $existing->delete();
            }

            return redirect()
                ->route('user.booking.providers.exceptions.index', $provider)
                ->with('success', 'استثنا برای این تاریخ حذف شد (بازگشت به تنظیمات عادی).');
        }

        BookingAvailabilityException::query()->updateOrCreate(
            [
                'scope_type' => BookingAvailabilityException::SCOPE_SERVICE_PROVIDER,
                'scope_id'   => $provider->id,
                'local_date' => $payload['local_date'],
            ],
            $payload
        );

        return redirect()
            ->route('user.booking.providers.exceptions.index', $provider)
            ->with('success', 'استثنای این تاریخ با موفقیت ثبت شد.');
    }

    public function destroy(User $provider, BookingAvailabilityException $exception)
    {
        if (
            $exception->scope_type !== BookingAvailabilityException::SCOPE_SERVICE_PROVIDER ||
            (int) $exception->scope_id !== (int) $provider->id
        ) {
            abort(404);
        }

        $exception->delete();

        return redirect()
            ->route('user.booking.providers.exceptions.index', $provider)
            ->with('success', 'استثنا حذف شد.');
    }
}
