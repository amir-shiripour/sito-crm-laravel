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
    public function index()
    {
        $services = BookingService::query()
            ->where('status', BookingService::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        return view('booking::web.index', compact('services'));
    }

    public function service(BookingService $service)
    {
        $service->load(['serviceProviders.provider']);
        $settings = BookingSetting::current();

        return view('booking::web.service', compact('service', 'settings'));
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
        $localDate = $this->convertJalaliDateToLocal($data['date_local'], $scheduleTz);
        if (! $localDate) {
            return response()->json(['data' => []], 200);
        }

        $providerId = (int) $data['provider_user_id'];

        Log::info('[Booking][OnlineSlots] request', [
            'service_id' => $service->id,
            'provider_user_id' => $providerId,
            'date_local' => $data['date_local'],
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
        $localDate = $this->convertJalaliDateToLocal($data['date_local'], $scheduleTz);
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
            return $slot['start_at_utc'] === $startUtc->toIso8601String()
                && $slot['end_at_utc'] === $endUtc->toIso8601String();
        });

        if (! $slotMatched) {
            Log::warning('[Booking][OnlineBooking] slot mismatch', [
                'service_id' => $service->id,
                'provider_user_id' => $providerId,
                'date_local' => $data['date_local'],
                'start_at_utc' => $startUtc->toIso8601String(),
                'end_at_utc' => $endUtc->toIso8601String(),
            ]);
            return back()
                ->withErrors(['start_at_utc' => 'اسلات انتخاب‌شده معتبر نیست یا ظرفیت ندارد.'])
                ->withInput();
        }

        $startLocal = $startUtc->copy()->timezone($scheduleTz);
        $endLocal = $endUtc->copy()->timezone($scheduleTz);

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
                'start_at_utc' => $startUtc->toIso8601String(),
                'end_at_utc' => $endUtc->toIso8601String(),
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['start_at_utc' => $message])->withInput();
        }

        $clientInput = [
            'notes' => null,
        ];

        if ($client) {
            $clientInput['client_id'] = $client->id;
        } else {
            $clientInput['full_name'] = $data['full_name'];
            $clientInput['phone'] = $data['phone'];
            if (!empty($data['password'])) {
                $clientInput['password'] = $data['password'];
            }
        }

        try {
            $result = $appointmentService->confirmOnlineHold(
                $hold->id,
                $clientInput,
                appointmentFormResponse: null,
                payNow: true
            );
        } catch (\RuntimeException $e) {
            Log::error('[Booking][OnlineBooking] confirm failed', [
                'hold_id' => $hold->id,
                'service_id' => $service->id,
                'provider_user_id' => $providerId,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['start_at_utc' => 'خطا در ثبت نوبت. لطفاً دوباره تلاش کنید.'])->withInput();
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

    protected function convertJalaliDateToLocal(string $value, string $tz): ?Carbon
    {
        $datePieces = preg_split('/[^\d]+/', trim($value));
        if (count($datePieces) < 3) {
            return null;
        }

        [$jy, $jm, $jd] = array_map('intval', array_slice($datePieces, 0, 3));
        [$gy, $gm, $gd] = CalendarUtils::toGregorian($jy, $jm, $jd);

        return Carbon::create($gy, $gm, $gd, 0, 0, 0, $tz);
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
