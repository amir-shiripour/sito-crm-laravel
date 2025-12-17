<?php

namespace Modules\Booking\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Booking\Entities\BookingAvailabilityException;
use Modules\Booking\Entities\BookingService;

class ServiceExceptionController extends Controller
{
    public function index(BookingService $service)
    {
        $exceptions = BookingAvailabilityException::query()
            ->where('scope_type', BookingAvailabilityException::SCOPE_SERVICE)
            ->where('scope_id', $service->id)
            ->orderBy('local_date')
            ->paginate(30);

        return view('booking::user.services.exceptions', compact('service', 'exceptions'));
    }

    public function store(Request $request, BookingService $service)
    {
        $data = $request->validate([
            'local_date' => ['required', 'date_format:Y-m-d'], // فرض: jalaliDatepicker مقدار گرگوری در value می‌نویسه
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
            'scope_type' => BookingAvailabilityException::SCOPE_SERVICE,
            'scope_id'   => $service->id,
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

        // اگر همه‌چیز خالی و is_closed هم false باشد = حذف استثنا (fallback به تنظیمات بالادستی)
        $allEmpty =
            !$payload['is_closed'] &&
            empty($payload['override_work_windows_json']) &&
            empty($payload['override_breaks_json']) &&
            !$payload['override_capacity_per_slot'] &&
            !$payload['override_capacity_per_day'];

        $existing = BookingAvailabilityException::query()
            ->where('scope_type', BookingAvailabilityException::SCOPE_SERVICE)
            ->where('scope_id', $service->id)
            ->where('local_date', $payload['local_date'])
            ->first();

        if ($allEmpty) {
            if ($existing) {
                $existing->delete();
            }

            return redirect()
                ->route('user.booking.services.exceptions.index', $service)
                ->with('success', 'استثنا برای این تاریخ حذف شد (بازگشت به تنظیمات عادی).');
        }

        BookingAvailabilityException::query()->updateOrCreate(
            [
                'scope_type' => BookingAvailabilityException::SCOPE_SERVICE,
                'scope_id'   => $service->id,
                'local_date' => $payload['local_date'],
            ],
            $payload
        );

        return redirect()
            ->route('user.booking.services.exceptions.index', $service)
            ->with('success', 'استثنای این تاریخ با موفقیت ثبت شد.');
    }

    public function destroy(BookingService $service, BookingAvailabilityException $exception)
    {
        if (
            $exception->scope_type !== BookingAvailabilityException::SCOPE_SERVICE ||
            (int) $exception->scope_id !== (int) $service->id
        ) {
            abort(404);
        }

        $exception->delete();

        return redirect()
            ->route('user.booking.services.exceptions.index', $service)
            ->with('success', 'استثنا حذف شد.');
    }
}
