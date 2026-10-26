<?php

namespace Modules\Booking\App\Livewire\Widgets;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Services\AppointmentService;
use Modules\Booking\Services\ClinicMonitoringService;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;

class DailyAppointmentsWidget extends Component
{
    use WithPagination;

    public string $activeTab = 'all';

    public ?int $selectedProviderId = null;

    public string $search = '';

    public string $selectedDate = '';

    public string $selectedDateJalali = '';

    public string $timeSort = 'asc'; // 'asc' = کمترین ساعت (زودترین)، 'desc' = بیشترین ساعت (دیرترین)

    public function mount(ClinicMonitoringService $monitoringService): void
    {
        $tz = $monitoringService->getScheduleTimezone();
        $today = Carbon::today($tz);
        $this->selectedDate = $today->format('Y-m-d');
        $this->selectedDateJalali = Jalalian::fromDateTime($today)->format('Y/m/d');

        $user = Auth::user();
        if ($user && ! $monitoringService->canViewAllAppointments($user) && $monitoringService->userIsProvider($user)) {
            $this->selectedProviderId = (int) $user->id;
        }
    }

    public function selectDate(?string $jalaliDate = null): void
    {
        if (empty($jalaliDate)) {
            $this->goToToday();
            return;
        }

        $clean = str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            trim($jalaliDate)
        );

        $gregorian = $this->convertJalaliToGregorian($clean);
        if ($gregorian) {
            $tz = app(ClinicMonitoringService::class)->getScheduleTimezone();
            $carbon = Carbon::parse($gregorian, $tz);
            $this->setDateFromCarbon($carbon);
            $this->resetPage();
        }
    }

    public function updatedSelectedDateJalali(string $val): void
    {
        $this->selectDate($val);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedProviderId(): void
    {
        $this->resetPage();
    }

    public function updatedTimeSort(): void
    {
        $this->resetPage();
    }

    public function toggleTimeSort(): void
    {
        $this->timeSort = ($this->timeSort === 'asc') ? 'desc' : 'asc';
        $this->resetPage();
    }

    public function setTimeSort(string $sort): void
    {
        $this->timeSort = in_array($sort, ['asc', 'desc'], true) ? $sort : 'asc';
        $this->resetPage();
    }

    public function nextDay(): void
    {
        $tz = app(ClinicMonitoringService::class)->getScheduleTimezone();
        $carbon = Carbon::parse($this->selectedDate, $tz)->addDay();
        $this->setDateFromCarbon($carbon);
        $this->resetPage();
    }

    public function previousDay(): void
    {
        $tz = app(ClinicMonitoringService::class)->getScheduleTimezone();
        $carbon = Carbon::parse($this->selectedDate, $tz)->subDay();
        $this->setDateFromCarbon($carbon);
        $this->resetPage();
    }

    public function goToToday(): void
    {
        $tz = app(ClinicMonitoringService::class)->getScheduleTimezone();
        $carbon = Carbon::today($tz);
        $this->setDateFromCarbon($carbon);
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->activeTab = 'all';
        $this->timeSort = 'asc';
        $user = Auth::user();
        $monitoringService = app(ClinicMonitoringService::class);
        if ($user && $monitoringService->canViewAllAppointments($user)) {
            $this->selectedProviderId = null;
        }
        $this->resetPage();
    }

    protected function setDateFromCarbon(Carbon $carbon): void
    {
        $this->selectedDate = $carbon->format('Y-m-d');
        $this->selectedDateJalali = Jalalian::fromDateTime($carbon)->format('Y/m/d');
    }

    protected function convertJalaliToGregorian(string $jalaliDate): ?string
    {
        $clean = str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            trim($jalaliDate)
        );

        if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $clean, $m)) {
            $y = (int) $m[1];
            $month = (int) $m[2];
            $day = (int) $m[3];

            if ($y >= 1300 && $y <= 1500) {
                $g = CalendarUtils::toGregorian($y, $month, $day);
                return sprintf('%04d-%02d-%02d', $g[0], $g[1], $g[2]);
            }

            if ($y >= 1900 && $y <= 2100) {
                return sprintf('%04d-%02d-%02d', $y, $month, $day);
            }
        }

        return null;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['all', 'waiting', 'in_progress', 'done'], true) ? $tab : 'all';
        $this->resetPage();
    }

    public function checkIn(int $appointmentId): void
    {
        $this->authorizeAction($appointmentId);

        $appointment = Appointment::query()->find($appointmentId);
        if (! $appointment) {
            $this->dispatch('notify', type: 'error', text: 'نوبت مورد نظر یافت نشد.');
            return;
        }

        $appointment->entry_at_utc = now('UTC');
        $appointment->save();

        $this->dispatch('notify', type: 'success', text: 'ورود مراجعه‌کننده با موفقیت ثبت شد.');
    }

    public function startVisit(int $appointmentId): void
    {
        $this->authorizeAction($appointmentId);

        $appointment = Appointment::query()->find($appointmentId);
        if (! $appointment) {
            $this->dispatch('notify', type: 'error', text: 'نوبت مورد نظر یافت نشد.');
            return;
        }

        if (empty($appointment->entry_at_utc)) {
            $appointment->entry_at_utc = now('UTC');
        }

        $previousStatus = $appointment->status;
        $appointment->status = Appointment::STATUS_IN_PROGRESS;
        $appointment->save();

        if ($previousStatus !== Appointment::STATUS_IN_PROGRESS) {
            try {
                app(AppointmentService::class)->triggerStatusWorkflows($appointment, $previousStatus);
            } catch (\Throwable $e) {
                Log::warning('Workflow trigger failed in DailyAppointmentsWidget startVisit: ' . $e->getMessage());
            }
        }

        $this->dispatch('notify', type: 'success', text: 'ویزیت بیمار آغاز گردید (در حال انجام).');
    }

    public function finishVisit(int $appointmentId): void
    {
        $this->authorizeAction($appointmentId);

        $appointment = Appointment::query()->find($appointmentId);
        if (! $appointment) {
            $this->dispatch('notify', type: 'error', text: 'نوبت مورد نظر یافت نشد.');
            return;
        }

        $previousStatus = $appointment->status;
        $appointment->status = Appointment::STATUS_DONE;
        if (empty($appointment->exit_at_utc)) {
            $appointment->exit_at_utc = now('UTC');
        }
        $appointment->save();

        if ($previousStatus !== Appointment::STATUS_DONE) {
            try {
                app(AppointmentService::class)->triggerStatusWorkflows($appointment, $previousStatus);
            } catch (\Throwable $e) {
                Log::warning('Workflow trigger failed in DailyAppointmentsWidget finishVisit: ' . $e->getMessage());
            }
        }

        $this->dispatch('notify', type: 'success', text: 'ویزیت نوبت به پایان رسید و ثبت شد.');
    }

    public function quickChangeStatus(int $appointmentId, string $newStatus): void
    {
        $this->authorizeAction($appointmentId);

        $validStatuses = array_keys(Appointment::statusesList());
        if (! in_array($newStatus, $validStatuses, true)) {
            $this->dispatch('notify', type: 'error', text: 'وضعیت انتخاب شده نامعتبر است.');
            return;
        }

        $appointment = Appointment::query()->find($appointmentId);
        if (! $appointment) {
            $this->dispatch('notify', type: 'error', text: 'نوبت مورد نظر یافت نشد.');
            return;
        }

        $previousStatus = $appointment->status;
        $appointment->status = $newStatus;

        if ($newStatus === Appointment::STATUS_DONE && empty($appointment->exit_at_utc)) {
            $appointment->exit_at_utc = now('UTC');
        } elseif (in_array($newStatus, [Appointment::STATUS_CANCELED_BY_ADMIN, Appointment::STATUS_CANCELED_BY_CLIENT], true)) {
            if (empty($appointment->cancel_reason)) {
                $appointment->cancel_reason = 'لغو سریع از پیشخوان';
            }
        }

        $appointment->save();

        if ($previousStatus !== $newStatus) {
            try {
                app(AppointmentService::class)->triggerStatusWorkflows($appointment, $previousStatus);
            } catch (\Throwable $e) {
                Log::warning('Workflow trigger failed in DailyAppointmentsWidget quickChangeStatus: ' . $e->getMessage());
            }
        }

        $this->dispatch('notify', type: 'success', text: 'وضعیت نوبت به «' . $appointment->status_label . '» تغییر یافت.');
    }

    public function markNoShow(int $appointmentId): void
    {
        $this->quickChangeStatus($appointmentId, Appointment::STATUS_NO_SHOW);
    }

    protected function authorizeAction(int $appointmentId): void
    {
        $user = Auth::user();
        $monitoringService = app(ClinicMonitoringService::class);

        if (! $user || (! $monitoringService->isAdminUser($user) && ! $user->can('booking.appointments.edit') && ! $user->can('booking.appointments.manage'))) {
            abort(403, 'شما مجوز تغییر وضعیت نوبت‌ها را ندارید.');
        }

        if (! $monitoringService->canViewAllAppointments($user) && $monitoringService->userIsProvider($user)) {
            $appointment = Appointment::query()->find($appointmentId);
            if ($appointment && (int) $appointment->provider_user_id !== (int) $user->id) {
                abort(403, 'شما فقط مجاز به مدیریت نوبت‌های اختصاصی خود هستید.');
            }
        }
    }

    public function render(ClinicMonitoringService $monitoringService)
    {
        // Safe check for DB tables
        if (! Schema::hasTable('appointments') || ! Schema::hasTable('booking_settings')) {
            return view('booking::livewire.widgets.daily-appointments-empty');
        }

        $user = Auth::user();
        $tz = $monitoringService->getScheduleTimezone();
        $today = Carbon::today($tz);
        $todayGregorian = $today->format('Y-m-d');
        $todayJalaliShort = Jalalian::fromDateTime($today)->format('Y/m/d');

        // Dynamic labels configured via /admin/booking/settings/label
        $labelProvider  = BookingSetting::getValue('label_provider', config('booking.labels.provider', 'ارائه‌دهنده'));
        $labelProviders = BookingSetting::getValue('label_providers', config('booking.labels.providers', 'ارائه‌دهندگان'));
        $labelService   = BookingSetting::getValue('label_service', config('booking.labels.service', 'سرویس'));
        $labelServices  = BookingSetting::getValue('label_services', config('booking.labels.services', 'سرویس‌ها'));

        if (empty($this->selectedDate)) {
            $this->selectedDate = $todayGregorian;
            $this->selectedDateJalali = $todayJalaliShort;
        }

        $isToday = ($this->selectedDate === $todayGregorian);

        try {
            $currentCarbon = Carbon::parse($this->selectedDate, $tz);
            $jalaliObj = Jalalian::fromDateTime($currentCarbon);
            $currentJalaliLong = $jalaliObj->format('%A، %d %B %Y');
            $dayOfWeekName = $jalaliObj->format('%A');
            $currentJalaliFormatted = $jalaliObj->format('%d %B %Y');
        } catch (\Throwable $e) {
            $currentJalaliLong = $this->selectedDateJalali;
            $dayOfWeekName = '';
            $currentJalaliFormatted = $this->selectedDateJalali;
        }

        $canViewAll = $monitoringService->canViewAllAppointments($user);
        $providers = $canViewAll ? $monitoringService->getAccessibleProviders($user) : collect();

        // 1. Base query for the selected date
        $baseQuery = $monitoringService->buildBaseQuery($this->selectedDate, $user);

        if ($this->selectedProviderId && $canViewAll) {
            $baseQuery->where('provider_user_id', $this->selectedProviderId);
        }

        // 2. Count metrics for the selected day
        $allDayAppointments = (clone $baseQuery)->get(['id', 'status', 'entry_at_utc', 'exit_at_utc']);
        $stats = [
            'total'       => $allDayAppointments->count(),
            'waiting'     => $allDayAppointments->whereIn('status', [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_PENDING_PAYMENT,
                Appointment::STATUS_CONFIRMED,
            ])->count(),
            'in_progress' => $allDayAppointments->where('status', Appointment::STATUS_IN_PROGRESS)->count(),
            'done'        => $allDayAppointments->where('status', Appointment::STATUS_DONE)->count(),
            'canceled'    => $allDayAppointments->whereIn('status', [
                Appointment::STATUS_CANCELED_BY_ADMIN,
                Appointment::STATUS_CANCELED_BY_CLIENT,
                Appointment::STATUS_NO_SHOW,
            ])->count(),
        ];

        // 3. Filter by tab
        $listQuery = clone $baseQuery;
        match ($this->activeTab) {
            'waiting'     => $listQuery->whereIn('status', [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_PENDING_PAYMENT,
                Appointment::STATUS_CONFIRMED,
            ]),
            'in_progress' => $listQuery->where('status', Appointment::STATUS_IN_PROGRESS),
            'done'        => $listQuery->where('status', Appointment::STATUS_DONE),
            default       => null,
        };

        // 4. Filter by search
        if (! empty(trim($this->search))) {
            $term = '%' . trim($this->search) . '%';
            $listQuery->where(function ($q) use ($term) {
                $q->whereHas('client', function ($cq) use ($term) {
                    $cq->where('first_name', 'like', $term)
                       ->orWhere('last_name', 'like', $term)
                       ->orWhere('phone', 'like', $term)
                       ->orWhere('national_code', 'like', $term);
                })->orWhereHas('service', function ($sq) use ($term) {
                    $sq->where('name', 'like', $term);
                });
            });
        }

        $appointments = $listQuery
            ->with(['client', 'service', 'provider'])
            ->orderBy('start_at_utc', $this->timeSort === 'desc' ? 'desc' : 'asc')
            ->paginate(10);

        $canEdit = $user && ($monitoringService->isAdminUser($user) || $user->can('booking.appointments.edit') || $user->can('booking.appointments.manage'));
        $canCreate = $user && ($monitoringService->isAdminUser($user) || $user->can('booking.appointments.create'));

        return view('booking::livewire.widgets.daily-appointments-widget', [
            'appointments'           => $appointments,
            'stats'                  => $stats,
            'currentJalaliLong'      => $currentJalaliLong,
            'currentJalaliFormatted' => $currentJalaliFormatted,
            'dayOfWeekName'          => $dayOfWeekName,
            'todayJalaliShort'       => $todayJalaliShort,
            'isToday'                => $isToday,
            'canViewAll'             => $canViewAll,
            'providers'              => $providers,
            'canEdit'                => $canEdit,
            'canCreate'              => $canCreate,
            'scheduleTz'             => $tz,
            'labelProvider'          => $labelProvider,
            'labelProviders'         => $labelProviders,
            'labelService'           => $labelService,
            'labelServices'          => $labelServices,
            'timeSort'               => $this->timeSort,
        ]);
    }
}
