<?php

namespace Modules\Booking\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Morilog\Jalali\Jalalian;
use Modules\Booking\Entities\BookingAvailabilityException;

class GlobalExceptionController extends Controller
{
    public function index(Request $request)
    {
        $exceptions = BookingAvailabilityException::query()
            ->where('scope_type', BookingAvailabilityException::SCOPE_GLOBAL)
            ->whereNull('scope_id')
            ->orderBy('local_date')
            ->paginate(30);

        return view('booking::user.settings.global_exceptions', compact('exceptions'));
    }

    public function store(Request $request)
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
            'scope_type' => BookingAvailabilityException::SCOPE_GLOBAL,
            'scope_id'   => null,
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
            ->where('scope_type', BookingAvailabilityException::SCOPE_GLOBAL)
            ->whereNull('scope_id')
            ->where('local_date', $payload['local_date'])
            ->first();

        if ($allEmpty) {
            if ($existing) {
                $existing->delete();
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'action' => 'deleted']);
            }

            return redirect()
                ->back()
                ->with('success', 'استثنا/تعطیلی عمومی برای این تاریخ حذف شد.');
        }

        $ex = BookingAvailabilityException::query()->updateOrCreate(
            [
                'scope_type' => BookingAvailabilityException::SCOPE_GLOBAL,
                'scope_id'   => null,
                'local_date' => $payload['local_date'],
            ],
            $payload
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'exception' => $ex]);
        }

        return redirect()
            ->back()
            ->with('success', 'تعطیلی/استثنای عمومی این تاریخ با موفقیت ثبت شد.');
    }

    public function batch(Request $request)
    {
        $data = $request->validate([
            'dates'      => ['required_without_all:start_date,end_date', 'nullable', 'array'],
            'dates.*'    => ['date_format:Y-m-d'],
            'start_date' => ['required_with:end_date', 'nullable', 'date_format:Y-m-d'],
            'end_date'   => ['required_with:start_date', 'nullable', 'date_format:Y-m-d'],
            'is_closed'  => ['required', 'boolean'],
        ]);

        $datesToProcess = [];

        if (!empty($data['dates'])) {
            $datesToProcess = $data['dates'];
        } elseif (!empty($data['start_date']) && !empty($data['end_date'])) {
            $start = \Carbon\Carbon::parse($data['start_date']);
            $end   = \Carbon\Carbon::parse($data['end_date']);
            
            if ($end->lt($start)) {
                $temp = $start;
                $start = $end;
                $end = $temp;
            }

            while ($start->lte($end)) {
                $datesToProcess[] = $start->toDateString();
                $start->addDay();
            }
        }

        $isClosed = (bool) $data['is_closed'];

        foreach ($datesToProcess as $dateStr) {
            if ($isClosed) {
                BookingAvailabilityException::query()->updateOrCreate(
                    [
                        'scope_type' => BookingAvailabilityException::SCOPE_GLOBAL,
                        'scope_id'   => null,
                        'local_date' => $dateStr,
                    ],
                    [
                        'is_closed' => true,
                    ]
                );
            } else {
                BookingAvailabilityException::query()
                    ->where('scope_type', BookingAvailabilityException::SCOPE_GLOBAL)
                    ->whereNull('scope_id')
                    ->where('local_date', $dateStr)
                    ->delete();
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'count' => count($datesToProcess)]);
        }

        return redirect()
            ->back()
            ->with('success', 'تعطیلات گروهی عمومی سیستم با موفقیت بروزرسانی شد.');
    }

    public function destroy(BookingAvailabilityException $exception)
    {
        if (
            $exception->scope_type !== BookingAvailabilityException::SCOPE_GLOBAL ||
            $exception->scope_id !== null
        ) {
            abort(404);
        }

        $exception->delete();

        return redirect()
            ->back()
            ->with('success', 'استثنا/تعطیلی عمومی حذف شد.');
    }
}
