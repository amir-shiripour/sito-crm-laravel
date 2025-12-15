<?php

namespace Modules\Booking\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Services\AppointmentService;
use App\Models\User;
use Modules\Clients\Entities\Client;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function __construct(protected AppointmentService $service)
    {
    }

    public function index(Request $request)
    {
        $appointments = Appointment::query()
            ->with(['service', 'provider', 'client'])
            ->orderByDesc('start_at_utc')
            ->paginate(25);

        return view('booking::user.appointments.index', compact('appointments'));
    }

    public function create()
    {
        $services = BookingService::query()
            ->where('status', BookingService::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        // فقط یوزرهایی که نقش‌شان داخل allowed_roles است
        $settings = BookingSetting::current();
        $roleIds  = (array) ($settings->allowed_roles ?? []);

        $providersQuery = User::query();

        if (!empty($roleIds)) {
            $providersQuery->whereHas('roles', function ($q) use ($roleIds) {
                $q->whereIn('id', $roleIds);
            });
        }

        $providers = $providersQuery->orderBy('name')->get();

        $clients = Client::query()->orderByDesc('id')->limit(50)->get();

        return view('booking::user.appointments.create', compact('services', 'providers', 'clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_id'        => ['required', 'integer', 'exists:booking_services,id'],
            'provider_user_id'  => ['required', 'integer', 'exists:users,id'],
            'client_id'         => ['required', 'integer', 'exists:clients,id'],

            // تاریخ/ساعت شمسی از فرانت
            'start_at_jalali'   => ['required', 'string'],
            'end_at_jalali'     => ['required', 'string'],

            'notes'             => ['nullable', 'string'],
        ]);

        // اطمینان از این‌که provider انتخابی، جزو نقش‌های مجاز است
        $settings = BookingSetting::current();
        $roleIds  = (array) ($settings->allowed_roles ?? []);

        if (!empty($roleIds)) {
            $isValidProvider = User::query()
                ->where('id', $data['provider_user_id'])
                ->whereHas('roles', function ($q) use ($roleIds) {
                    $q->whereIn('id', $roleIds);
                })
                ->exists();

            if (!$isValidProvider) {
                return back()
                    ->withErrors(['provider_user_id' => 'ارائه‌دهنده انتخاب‌شده مجاز نیست.'])
                    ->withInput();
            }
        }

        // فرمت ورودی jalaliDatepicker: مثلا 1404/09/15 14:30
        $format = 'Y/m/d H:i';

        try {
            $startLocal = Jalalian::fromFormat($format, $data['start_at_jalali'])->toCarbon();
            $endLocal   = Jalalian::fromFormat($format, $data['end_at_jalali'])->toCarbon();
        } catch (\Throwable $e) {
            return back()
                ->withErrors(['start_at_jalali' => 'فرمت تاریخ/زمان نامعتبر است.'])
                ->withInput();
        }

        // تبدیل به UTC برای ذخیره در DB
        $startUtc = $startLocal->clone()->setTimezone('UTC');
        $endUtc   = $endLocal->clone()->setTimezone('UTC');

        $this->service->createAppointmentByOperator(
            (int) $data['service_id'],
            (int) $data['provider_user_id'],
            (int) $data['client_id'],
            $startUtc,
            $endUtc,
            createdByUserId: $request->user()->id,
            notes: $data['notes'] ?? null,
            appointmentFormResponse: null
        );

        return redirect()
            ->route('user.booking.appointments.index')
            ->with('success', 'نوبت با موفقیت ثبت شد.');
    }
}
