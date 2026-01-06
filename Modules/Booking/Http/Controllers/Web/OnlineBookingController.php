<?php

namespace Modules\Booking\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Services\AppointmentService;
use Modules\Booking\Services\BookingEngine;
use Modules\Clients\Entities\ClientSetting;
use Morilog\Jalali\CalendarUtils;
use Carbon\Carbon;

class OnlineBookingController extends Controller
{
    public function index(BookingEngine $engine)
    {
        $settings = BookingSetting::current();

        // فقط سرویس‌های فعال را بگیر
        $services = BookingService::query()
            ->where('status', BookingService::STATUS_ACTIVE)
            ->with(['serviceProviders' => function ($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('name')
            ->get();

        // فیلتر کردن سرویس‌هایی که حداقل یک provider فعال دارند و امکان رزرو آنلاین دارند
        $availableServices = $services->filter(function ($service) use ($engine, $settings) {
            // اگر رزرو آنلاین در سطح global غیرفعال است
            if (!$settings->global_online_booking_enabled) {
                return false;
            }

            // بررسی اینکه آیا حداقل یک provider فعال دارد که امکان رزرو آنلاین دارد
            foreach ($service->serviceProviders as $sp) {
                if ($sp->is_active && $engine->isOnlineBookingEnabled($service->id, $sp->provider_user_id)) {
                    return true;
                }
            }

            return false;
        });

        return view('booking::web.index', ['services' => $availableServices]);
    }

    public function service(BookingService $service)
    {
        $service->load(['serviceProviders.provider', 'appointmentForm']);
        $settings = BookingSetting::current();

        $now = Carbon::now();
        $jDate = CalendarUtils::toJalali($now->year, $now->month, $now->day);
        $currentJalali = [
            'year' => $jDate[0],
            'month' => $jDate[1],
            'day' => $jDate[2],
        ];

        return view('booking::web.service', compact('service', 'settings', 'currentJalali'));
    }

    /**
     * Public Calendar endpoint:
     * Returns month days in format expected by frontend:
     * [
     *   { local_date: "YYYY-MM-DD", is_closed: bool, has_available_slots: bool }
     * ]
     *
     * IMPORTANT:
     * - local_date is returned in Gregorian ISO (Y-m-d) so JS Date parsing works.
     * - input "year/month" can be Gregorian or Jalali. We auto-detect by year.
     */
    public function calendar(Request $request, BookingService $service, BookingEngine $engine)
    {
        $settings = BookingSetting::current();
        if (! $settings->global_online_booking_enabled) {
            return response()->json(['data' => []], 200);
        }

        $data = $request->validate([
            'provider_user_id' => ['required', 'integer'],
            'year' => ['required', 'integer'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $providerId = (int) $data['provider_user_id'];

        // اگر آنلاین بوکینگ برای این سرویس/ارائه‌دهنده فعال نیست، کل ماه رو خالی برگردون
        if (! $engine->isOnlineBookingEnabled($service->id, $providerId)) {
            return response()->json(['data' => []], 200);
        }

        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
        $viewerTz = config('booking.timezones.display_default', $scheduleTz);

        $year = (int) $data['year'];
        $month = (int) $data['month'];

        // تشخیص خودکار نوع تقویم:
        // - اگر سال بزرگ (>=1700) بود => میلادی
        // - اگر سال کوچک‌تر بود => جلالی (معمولاً 13xx/14xx)
        $isGregorian = $year >= 1700;

        // بازه ماه را به Carbon (میلادی) تبدیل می‌کنیم
        if ($isGregorian) {
            $start = Carbon::create($year, $month, 1, 0, 0, 0, $scheduleTz);
            if (! $start) {
                return response()->json(['data' => []], 200);
            }
            $end = $start->copy()->endOfMonth();
        } else {
            // Jalali year/month -> Gregorian start/end
            $start = $this->parseFlexibleLocalDate(sprintf('%04d-%02d-01', $year, $month), $scheduleTz);
            if (! $start) {
                return response()->json(['data' => []], 200);
            }

            // طول ماه جلالی را محاسبه می‌کنیم با تبدیل روز 1..31 تا وقتی ماه عوض شود
            // (این روش مطمئن است حتی اگر متد طول ماه در لایبرری شما متفاوت باشد)
            $end = null;
            for ($d = 1; $d <= 31; $d++) {
                $tmp = $this->parseFlexibleLocalDate(sprintf('%04d-%02d-%02d', $year, $month, $d), $scheduleTz);
                if (! $tmp) {
                    break;
                }
                $end = $tmp;
            }
            if (! $end) {
                return response()->json(['data' => []], 200);
            }
        }

        Log::info('[Booking][OnlineCalendar] request', [
            'service_id' => $service->id,
            'provider_user_id' => $providerId,
            'year' => $year,
            'month' => $month,
            'calendar' => $isGregorian ? 'gregorian' : 'jalali',
            'range_start' => $start->toDateString(),
            'range_end' => $end->toDateString(),
        ]);

        // روز به روز بررسی می‌کنیم که اسلات دارد یا نه
        $days = [];
        $cursor = $start->copy()->startOfDay();
        $endDay = $end->copy()->startOfDay();

        // برای جلوگیری از لوپ بی‌نهایت
        $safety = 0;

        while ($cursor->lte($endDay) && $safety < 40) {
            $dateStr = $cursor->toDateString(); // Gregorian ISO Y-m-d (برای JS عالی)

            try {
                $slots = $engine->generateSlots(
                    $service->id,
                    $providerId,
                    $dateStr,
                    $dateStr,
                    viewerTimezone: $viewerTz
                );
            } catch (\Throwable $e) {
                Log::warning('[Booking][OnlineCalendar] generateSlots failed', [
                    'service_id' => $service->id,
                    'provider_user_id' => $providerId,
                    'date' => $dateStr,
                    'error' => $e->getMessage(),
                ]);
                $slots = [];
            }

            $has = ! empty($slots);

            $days[] = [
                'local_date' => $dateStr,
                'is_closed' => ! $has,
                'has_available_slots' => $has,
            ];

            $cursor->addDay();
            $safety++;
        }

        return response()->json([
            'data' => $days,
            'meta' => [
                'calendar' => $isGregorian ? 'gregorian' : 'jalali',
                'schedule_tz' => $scheduleTz,
                'viewer_tz' => $viewerTz,
            ],
        ], 200);
    }

    public function slots(Request $request, BookingService $service, BookingEngine $engine)
    {
        $settings = BookingSetting::current();
        if (! $settings->global_online_booking_enabled) {
            return response()->json(['data' => []], 200);
        }

        $data = $request->validate([
            'provider_user_id' => ['required', 'integer'],
            'date_local' => ['required', 'string'],
        ]);

        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');

        // ✅ حالا هم جلالی رو قبول می‌کنه هم میلادی
        $localDate = $this->parseFlexibleLocalDate($data['date_local'], $scheduleTz);
        if (! $localDate) {
            return response()->json(['data' => []], 200);
        }

        $providerId = (int) $data['provider_user_id'];

        Log::info('[Booking][OnlineSlots] request', [
            'service_id' => $service->id,
            'provider_user_id' => $providerId,
            'date_local' => $data['date_local'],
            'date_gregorian' => $localDate->toDateString(),
        ]);

        $slots = $engine->generateSlots(
            $service->id,
            $providerId,
            $localDate->toDateString(),
            $localDate->toDateString(),
            viewerTimezone: config('booking.timezones.display_default', $scheduleTz)
        );

        return response()->json(['data' => $slots]);
    }

    public function book(Request $request, BookingService $service, AppointmentService $appointmentService, BookingEngine $engine)
    {
        $settings = BookingSetting::current();
        if (! $settings->global_online_booking_enabled) {
            return back()->withErrors(['service_id' => 'رزرو آنلاین در حال حاضر غیرفعال است.']);
        }

        $clientMode = ClientSetting::getValue('auth.mode', 'password');
        $client = Auth::guard('client')->user();

        $rules = [
            'provider_user_id' => ['required', 'integer'],
            'date_local' => ['required', 'string'],
            'start_at_utc' => ['required', 'date'],
            'end_at_utc' => ['required', 'date'],
        ];

        if (! $client) {
            $rules['full_name'] = ['required', 'string', 'max:255'];
            $rules['phone'] = ['required', 'string', 'max:50'];
            if ($clientMode === 'password') {
                $rules['password'] = ['required', 'string', 'min:6'];
            }
        }

        $data = $request->validate($rules);

        $providerId = (int) $data['provider_user_id'];
        if (! $engine->isOnlineBookingEnabled($service->id, $providerId)) {
            return back()->withErrors(['provider_user_id' => 'رزرو آنلاین برای این سرویس/ارائه‌دهنده فعال نیست.']);
        }

        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');

        // ✅ حالا هم جلالی رو قبول می‌کنه هم میلادی
        $localDate = $this->parseFlexibleLocalDate($data['date_local'], $scheduleTz);
        if (! $localDate) {
            return back()->withErrors(['date_local' => 'تاریخ وارد شده معتبر نیست.'])->withInput();
        }

        $startUtc = Carbon::parse($data['start_at_utc'], 'UTC');
        $endUtc = Carbon::parse($data['end_at_utc'], 'UTC');

        $slots = $engine->generateSlots(
            $service->id,
            $providerId,
            $localDate->toDateString(),
            $localDate->toDateString(),
            viewerTimezone: config('booking.timezones.display_default', $scheduleTz)
        );

        $slotMatched = collect($slots)->first(function ($slot) use ($startUtc, $endUtc) {
            return ($slot['start_at_utc'] ?? null) === $startUtc->toIso8601String()
                && ($slot['end_at_utc'] ?? null) === $endUtc->toIso8601String();
        });

        if (! $slotMatched) {
            Log::warning('[Booking][OnlineBooking] slot mismatch', [
                'service_id' => $service->id,
                'provider_user_id' => $providerId,
                'date_local' => $data['date_local'],
                'date_gregorian' => $localDate->toDateString(),
                'start_at_utc' => $startUtc->toIso8601String(),
                'end_at_utc' => $endUtc->toIso8601String(),
            ]);

            return back()
                ->withErrors(['start_at_utc' => 'اسلات انتخاب‌شده معتبر نیست یا ظرفیت ندارد.'])
                ->withInput();
        }

        // Validate Form Data
        $service->load('appointmentForm');
        $formResponse = null;
        if ($service->appointmentForm && is_array($service->appointmentForm->schema_json)) {
            $formData = $request->input('form_data', []);
            $errors = [];
            $fields = $service->appointmentForm->schema_json['fields'] ?? [];

            // فقط فیلدهایی که collect_from_online دارند را بررسی می‌کنیم
            foreach ($fields as $field) {
                $name = $field['name'] ?? null;
                $collectFromOnline = !empty($field['collect_from_online']);

                // اگر فیلد collect_from_online ندارد، از validation رد می‌شود
                if (!$collectFromOnline) {
                    continue;
                }

                $label = $field['label'] ?? $name;
                $required = $field['required'] ?? false;

                if ($name && $required && empty($formData[$name])) {
                    $errors["form_data.{$name}"] = "فیلد {$label} الزامی است.";
                }
            }

            if (!empty($errors)) {
                return back()->withErrors($errors)->withInput();
            }
            $formResponse = $formData;
        }

        try {
            $hold = $appointmentService->startOnlineHold(
                $service->id,
                $providerId,
                $startUtc->toIso8601String(),
                $endUtc->toIso8601String(),
                $request->session()->getId()
            );
        } catch (\RuntimeException $e) {
            $message = match ($e->getMessage()) {
                'Slot capacity is full.' => 'ظرفیت این بازه زمانی تکمیل است.',
                'Day capacity is full.' => 'ظرفیت روز تکمیل است.',
                'This day is closed.' => 'این روز بسته است.',
                'Slot is outside work windows.' => 'این بازه خارج از ساعات کاری است.',
                'Slot overlaps with break.' => 'این بازه با زمان استراحت تداخل دارد.',
                'Slot crosses day boundary.' => 'بازه انتخابی باید داخل همان روز باشد.',
                default => 'امکان رزرو در این بازه وجود ندارد.',
            };

            Log::warning('[Booking][OnlineBooking] hold failed', [
                'service_id' => $service->id,
                'provider_user_id' => $providerId,
                'date_local' => $data['date_local'],
                'date_gregorian' => $localDate->toDateString(),
                'start_at_utc' => $startUtc->toIso8601String(),
                'end_at_utc' => $endUtc->toIso8601String(),
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['start_at_utc' => $message])->withInput();
        }

        $clientInput = ['notes' => null];

        if ($client) {
            $clientInput['client_id'] = $client->id;
        } else {
            $clientInput['full_name'] = $data['full_name'];
            $clientInput['phone'] = $data['phone'];
            if (! empty($data['password'])) {
                $clientInput['password'] = $data['password'];
            }
        }

        try {
            $result = $appointmentService->confirmOnlineHold(
                $hold->id,
                $clientInput,
                appointmentFormResponse: $formResponse,
                payNow: true
            );
        } catch (\Throwable $e) {
            Log::error('[Booking][OnlineBooking] confirm failed', [
                'hold_id' => $hold->id,
                'service_id' => $service->id,
                'provider_user_id' => $providerId,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return back()->withErrors(['start_at_utc' => 'خطا در ثبت نوبت. لطفاً دوباره تلاش کنید.'])->withInput();
        }

        if (!empty($result['gateway']['payment_url'])) {
            return redirect($result['gateway']['payment_url']);
        }

        Log::info('[Booking][OnlineBooking] appointment confirmed', [
            'appointment_id' => $result['appointment']->id ?? null,
            'service_id' => $service->id,
            'provider_user_id' => $providerId,
            'client_id' => $result['appointment']->client_id ?? $client?->id,
            'start_at_utc' => $startUtc->toIso8601String(),
            'end_at_utc' => $endUtc->toIso8601String(),
        ]);

        return redirect()
            ->route('booking.public.service', $service)
            ->with('success', 'نوبت شما با موفقیت ثبت شد.');
    }

    /**
     * Parses date_local that may be:
     * - Gregorian ISO: 2025-12-23
     * - Jalali: 1404-10-02 (or with / separators)
     *
     * Auto-detect based on year.
     * Returns Carbon in $tz at 00:00:00.
     */
    protected function parseFlexibleLocalDate(string $value, string $tz): ?Carbon
    {
        $value = trim($value);

        $datePieces = preg_split('/[^\d]+/', $value);
        if (count($datePieces) < 3) {
            return null;
        }

        [$y, $m, $d] = array_map('intval', array_slice($datePieces, 0, 3));

        // Basic guard
        if ($m < 1 || $m > 12 || $d < 1 || $d > 31) {
            return null;
        }

        try {
            // Gregorian
            if ($y >= 1700) {
                return Carbon::create($y, $m, $d, 0, 0, 0, $tz);
            }

            // Jalali -> Gregorian
            [$gy, $gm, $gd] = CalendarUtils::toGregorian($y, $m, $d);
            return Carbon::create($gy, $gm, $gd, 0, 0, 0, $tz);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function combineLocalDateAndTime(?Carbon $date, string $time): ?Carbon
    {
        if (! $date || empty($time)) {
            return null;
        }

        $timePieces = preg_split('/[^\d]+/', trim($time));
        if (count($timePieces) < 2) {
            return null;
        }

        $hour = min(max((int) $timePieces[0], 0), 23);
        $minute = min(max((int) $timePieces[1], 0), 59);

        return $date->copy()->setTime($hour, $minute, 0);
    }
}
