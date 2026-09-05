<?php

namespace Modules\Booking\App\Livewire\User;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Services\AppointmentService;
use Modules\Booking\Services\ClinicMonitoringService;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;

#[Layout('layouts.user', ['title' => 'مانیتورینگ زنده'])]
#[Title('مانیتورینگ زنده')]
class ClinicLiveMonitoring extends Component
{
    public ?int $selectedProviderId = null;

    public ?int $selectedServiceId = null;

    public string $selectedStatus = '';

    public string $selectedDate = '';

    public string $selectedDateJalali = '';

    public string $searchQuery = '';

    public int $refreshInterval = 15;

    public bool $quickStatusEnabled = false;

    public ?string $toastSuccess = null;

    public ?string $toastError = null;

    public function boot(ClinicMonitoringService $monitoringService): void
    {
        $user = Auth::user();
        if ($user && ! $monitoringService->canViewAllAppointments($user)) {
            $this->selectedProviderId = (int) $user->id;
        }
    }

    public function booted(ClinicMonitoringService $monitoringService): void
    {
        $user = Auth::user();
        if ($user && ! $monitoringService->canViewAllAppointments($user)) {
            $this->selectedProviderId = (int) $user->id;
        }
    }

    public function mount(ClinicMonitoringService $monitoringService): void
    {
        $user = Auth::user();
        if (! $user || ! $user->can('booking.appointments.view')) {
            abort(403, 'شما مجوز مشاهده نوبت‌ها را ندارید.');
        }

        $settings = BookingSetting::current();
        $this->refreshInterval = $settings->getMonitoringRefreshInterval();
        $this->quickStatusEnabled = $settings->isMonitoringQuickStatusEnabled();

        $tz = $monitoringService->getScheduleTimezone();
        $today = Carbon::today($tz);
        $todayJalali = Jalalian::fromDateTime($today)->format('Y/m/d');

        $savedJalali = session('clinic_monitoring_date_jalali');
        $savedDate = session('clinic_monitoring_date') ?? session('monitoring_filter_date');

        if ($savedJalali && $this->isValidJalaliDate($savedJalali)) {
            $this->selectedDateJalali = $savedJalali;
            $this->selectedDate = $this->convertJalaliToGregorian($savedJalali) ?? $today->format('Y-m-d');
        } elseif ($savedDate) {
            $this->selectedDate = $savedDate;
            try {
                $this->selectedDateJalali = Jalalian::fromDateTime(Carbon::parse($savedDate, $tz))->format('Y/m/d');
            } catch (\Throwable) {
                $this->selectedDate = $today->format('Y-m-d');
                $this->selectedDateJalali = $todayJalali;
            }
        } else {
            $this->selectedDate = $today->format('Y-m-d');
            $this->selectedDateJalali = $todayJalali;
        }

        // Scoping: If user cannot view all, lock to own provider ID
        if (! $monitoringService->canViewAllAppointments($user)) {
            $this->selectedProviderId = (int) $user->id;
        } else {
            $sessProv = session('clinic_monitoring_provider_id') ?? session('monitoring_filter_provider');
            if ($sessProv !== null && $sessProv !== 'all' && $sessProv !== '') {
                $this->selectedProviderId = (int) $sessProv;
            } else {
                $this->selectedProviderId = null;
            }
        }

        $sessServ = session('clinic_monitoring_service_id') ?? session('monitoring_filter_service');
        if ($sessServ !== null && $sessServ !== 'all' && $sessServ !== '') {
            $this->selectedServiceId = (int) $sessServ;
        } else {
            $this->selectedServiceId = null;
        }

        $this->selectedStatus = (string) (session('clinic_monitoring_status_filter')
            ?? session('monitoring_filter_status')
            ?? '');
    }

    public function updatedSelectedProviderId($val): void
    {
        $user = Auth::user();
        $monitoringService = app(ClinicMonitoringService::class);

        if ($user && ! $monitoringService->canViewAllAppointments($user)) {
            $this->selectedProviderId = (int) $user->id;
        } elseif ($val === '' || $val === 'all') {
            $this->selectedProviderId = null;
        } else {
            $this->selectedProviderId = (int) $val;
        }

        session([
            'clinic_monitoring_provider_id' => $this->selectedProviderId,
            'monitoring_filter_provider' => $this->selectedProviderId,
        ]);
    }

    public function selectProvider(?int $providerId): void
    {
        $this->updatedSelectedProviderId($providerId);
    }

    public function updatedSelectedServiceId($val): void
    {
        if ($val === '' || $val === 'all') {
            $this->selectedServiceId = null;
        } else {
            $this->selectedServiceId = (int) $val;
        }

        session([
            'clinic_monitoring_service_id' => $this->selectedServiceId,
            'monitoring_filter_service' => $this->selectedServiceId,
        ]);
    }

    public function updatedSelectedStatus(string $val): void
    {
        $this->selectedStatus = $val;
        session([
            'clinic_monitoring_status_filter' => $val,
            'monitoring_filter_status' => $val,
        ]);
    }

    public function updatedSelectedDateJalali(string $val): void
    {
        $this->selectedDateJalali = trim($val);
        $gregorian = $this->convertJalaliToGregorian($this->selectedDateJalali);
        if ($gregorian) {
            $this->selectedDate = $gregorian;
        }

        session([
            'clinic_monitoring_date_jalali' => $this->selectedDateJalali,
            'clinic_monitoring_date' => $this->selectedDate,
            'monitoring_filter_date' => $this->selectedDate,
        ]);
    }

    public function updatedSelectedDate(string $val): void
    {
        $this->selectedDate = trim($val);
        try {
            $tz = app(ClinicMonitoringService::class)->getScheduleTimezone();
            $this->selectedDateJalali = Jalalian::fromDateTime(Carbon::parse($val, $tz))->format('Y/m/d');
        } catch (\Throwable) {
            // Keep current jalali if parsing fails
        }

        session([
            'clinic_monitoring_date' => $this->selectedDate,
            'monitoring_filter_date' => $this->selectedDate,
            'clinic_monitoring_date_jalali' => $this->selectedDateJalali,
        ]);
    }

    public function resetFilters(ClinicMonitoringService $monitoringService): void
    {
        $user = Auth::user();
        $monitoringService->clearSessionFilters();
        session()->forget('clinic_monitoring_date_jalali');

        $tz = $monitoringService->getScheduleTimezone();
        $today = Carbon::today($tz);
        $this->selectedDate = $today->format('Y-m-d');
        $this->selectedDateJalali = Jalalian::fromDateTime($today)->format('Y/m/d');
        $this->selectedServiceId = null;
        $this->selectedStatus = '';
        $this->searchQuery = '';

        if ($user && ! $monitoringService->isAdminUser($user) && ! $user->can('booking.appointments.view.all')) {
            $this->selectedProviderId = $user->id;
        } else {
            $this->selectedProviderId = null;
        }

        $this->toastSuccess = 'فیلترها با موفقیت به حالت پیش‌فرض بازگردانده شدند.';
        $this->dispatch('notify', ['type' => 'success', 'text' => $this->toastSuccess]);
    }

    protected function convertJalaliToGregorian(string $jalaliDate): ?string
    {
        $clean = str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            trim($jalaliDate)
        );

        if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $clean, $m)) {
            $g = CalendarUtils::toGregorian((int) $m[1], (int) $m[2], (int) $m[3]);
            return sprintf('%04d-%02d-%02d', $g[0], $g[1], $g[2]);
        }

        return null;
    }

    protected function isValidJalaliDate(string $jalaliDate): bool
    {
        return $this->convertJalaliToGregorian($jalaliDate) !== null;
    }

    public function refreshMonitoring(): void
    {
        // Triggers re-render on demand
    }

    public function changeStatus(int $appointmentId, string $newStatus): void
    {
        $settings = BookingSetting::current();
        if (! $settings->isMonitoringQuickStatusEnabled()) {
            $this->toastError = 'قابلیت تغییر وضعیت سریع در تنظیمات غیرفعال است.';
            $this->dispatch('notify', ['type' => 'error', 'text' => $this->toastError]);

            return;
        }

        $user = Auth::user();
        $monitoringService = app(ClinicMonitoringService::class);

        if (! $user || (! $monitoringService->isAdminUser($user) && ! $user->can('booking.appointments.edit'))) {
            $this->toastError = 'شما مجوز تغییر وضعیت نوبت را ندارید.';
            $this->dispatch('notify', ['type' => 'error', 'text' => $this->toastError]);

            return;
        }

        $appointment = Appointment::query()->find($appointmentId);
        if (! $appointment) {
            $this->toastError = 'نوبت مورد نظر یافت نشد.';
            $this->dispatch('notify', ['type' => 'error', 'text' => $this->toastError]);

            return;
        }

        // Ownership enforcement for restricted doctors
        if (! $monitoringService->canViewAllAppointments($user)) {
            if ((int) $appointment->provider_user_id !== (int) $user->id) {
                $this->toastError = 'شما فقط مجاز به ویرایش نوبت‌های خود هستید.';
                $this->dispatch('notify', ['type' => 'error', 'text' => $this->toastError]);

                return;
            }
        }

        $validStatuses = array_keys(Appointment::statusesList());
        if (! in_array($newStatus, $validStatuses)) {
            $this->toastError = 'وضعیت انتخاب شده نامعتبر است.';
            $this->dispatch('notify', ['type' => 'error', 'text' => $this->toastError]);

            return;
        }

        $previousStatus = $appointment->status;
        $appointment->status = $newStatus;

        if ($newStatus === Appointment::STATUS_DONE) {
            if ($settings->allow_appointment_entry_exit_times && empty($appointment->exit_at_utc)) {
                $appointment->exit_at_utc = now('UTC');
            }
        } elseif (in_array($newStatus, [Appointment::STATUS_CANCELED_BY_ADMIN, Appointment::STATUS_CANCELED_BY_CLIENT])) {
            if (empty($appointment->cancel_reason)) {
                $appointment->cancel_reason = $newStatus === Appointment::STATUS_CANCELED_BY_ADMIN
                    ? 'لغو سریع توسط ادمین در مانیتورینگ زنده'
                    : 'لغو سریع در مانیتورینگ زنده';
            }
        }

        $appointment->save();

        if ($previousStatus !== $newStatus) {
            try {
                app(AppointmentService::class)->triggerStatusWorkflows($appointment, $previousStatus);
            } catch (\Throwable $e) {
                Log::warning('Workflow trigger failed in live monitoring: '.$e->getMessage());
            }
        }

        $this->toastSuccess = "وضعیت نوبت #{$appointment->id} به «{$appointment->status_label}» تغییر یافت.";
        $this->dispatch('notify', ['type' => 'success', 'text' => $this->toastSuccess]);
    }

    public function quickChangeStatus(int $appointmentId, string $newStatus): void
    {
        $this->changeStatus($appointmentId, $newStatus);
    }

    public function checkIn(int $appointmentId): void
    {
        $user = Auth::user();
        $monitoringService = app(ClinicMonitoringService::class);

        if (! $user || (! $monitoringService->isAdminUser($user) && ! $user->can('booking.appointments.edit'))) {
            $this->toastError = 'شما مجوز ثبت ورود بیمار را ندارید.';
            $this->dispatch('notify', ['type' => 'error', 'text' => $this->toastError]);

            return;
        }

        $appointment = Appointment::query()->find($appointmentId);
        if (! $appointment) {
            return;
        }

        if (! $monitoringService->canViewAllAppointments($user)) {
            if ((int) $appointment->provider_user_id !== (int) $user->id) {
                $this->toastError = 'شما فقط مجاز به ویرایش نوبت‌های خود هستید.';
                $this->dispatch('notify', ['type' => 'error', 'text' => $this->toastError]);

                return;
            }
        }

        $appointment->entry_at_utc = now('UTC');
        $appointment->save();

        $this->toastSuccess = 'ورود مراجع با موفقیت در سیستم ثبت گردید.';
        $this->dispatch('notify', ['type' => 'success', 'text' => $this->toastSuccess]);
    }

    public function startVisit(int $appointmentId): void
    {
        $user = Auth::user();
        $monitoringService = app(ClinicMonitoringService::class);

        if (! $user || (! $monitoringService->isAdminUser($user) && ! $user->can('booking.appointments.edit'))) {
            $this->toastError = 'شما مجوز مدیریت ویزیت را ندارید.';
            $this->dispatch('notify', ['type' => 'error', 'text' => $this->toastError]);

            return;
        }

        $appointment = Appointment::query()->find($appointmentId);
        if (! $appointment) {
            return;
        }

        if (! $monitoringService->canViewAllAppointments($user)) {
            if ((int) $appointment->provider_user_id !== (int) $user->id) {
                $this->toastError = 'شما فقط مجاز به ویرایش نوبت‌های خود هستید.';
                $this->dispatch('notify', ['type' => 'error', 'text' => $this->toastError]);

                return;
            }
        }

        if (empty($appointment->entry_at_utc)) {
            $appointment->entry_at_utc = now('UTC');
        }
        if (in_array($appointment->status, [Appointment::STATUS_PENDING, Appointment::STATUS_PENDING_PAYMENT, Appointment::STATUS_DRAFT])) {
            $appointment->status = Appointment::STATUS_CONFIRMED;
        }
        $appointment->save();

        $this->toastSuccess = 'ویزیت مراجع با موفقیت آغاز شد.';
        $this->dispatch('notify', ['type' => 'success', 'text' => $this->toastSuccess]);
    }

    public function finishVisit(int $appointmentId): void
    {
        $this->changeStatus($appointmentId, Appointment::STATUS_DONE);
    }

    public function render(ClinicMonitoringService $monitoringService)
    {
        $settings = BookingSetting::current();
        $this->refreshInterval = $settings->getMonitoringRefreshInterval();
        $this->quickStatusEnabled = $settings->isMonitoringQuickStatusEnabled();

        $tz = $monitoringService->getScheduleTimezone();
        $today = Carbon::today($tz);
        if (empty($this->selectedDateJalali)) {
            $this->selectedDateJalali = Jalalian::fromDateTime($today)->format('Y/m/d');
            $this->selectedDate = $today->format('Y-m-d');
        } elseif (empty($this->selectedDate)) {
            $this->selectedDate = $this->convertJalaliToGregorian($this->selectedDateJalali) ?? $today->format('Y-m-d');
        }

        $baseQuery = $monitoringService->buildBaseQuery($this->selectedDate);
        $statusCounts = $monitoringService->getStatusCounts($baseQuery);
        $providers = $monitoringService->getAccessibleProviders();
        $services = $monitoringService->getActiveServices();
        $statusesList = Appointment::statusesList();

        $filteredQuery = clone $baseQuery;
        $monitoringService->applyFilters(
            $filteredQuery,
            $this->selectedProviderId,
            $this->selectedServiceId,
            $this->selectedStatus
        );

        // Providers Live Overview Matrix (when viewing all providers)
        $providersSummary = null;
        if (empty($this->selectedProviderId)) {
            $providersSummary = $monitoringService->getProvidersLiveSummary($baseQuery, $providers);
        }

        // Waiting Lobby Queue
        $lobbyPatients = $monitoringService->getLobbyWaitingPatients($baseQuery, $this->selectedProviderId);

        // Active patient & next in queue
        $activePatient = $monitoringService->resolveActivePatient($filteredQuery);
        $nextInQueue = $monitoringService->resolveNextInQueue($filteredQuery, $activePatient?->id);

        // Table Query with Search Query filter
        $appointmentsQuery = (clone $filteredQuery)
            ->with([
                'service:id,name,color,base_price',
                'client:id,full_name,phone,national_code,case_number',
                'provider:id,name',
            ]);

        if (! empty(trim($this->searchQuery))) {
            $search = trim($this->searchQuery);
            $appointmentsQuery->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($cq) use ($search) {
                        $cq->where('full_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('national_code', 'like', "%{$search}%")
                            ->orWhere('case_number', 'like', "%{$search}%");
                    });
            });
        }

        $appointments = $appointmentsQuery
            ->orderBy('start_at_utc', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $labelProvider = config('booking.labels.provider', 'ارائه‌دهنده');
        $labelProviders = config('booking.labels.providers', 'ارائه‌دهندگان');
        $labelService = config('booking.labels.service', 'سرویس');
        $labelServices = config('booking.labels.services', 'سرویس‌ها');

        return view('booking::livewire.user.clinic-live-monitoring', [
            'activePatient' => $activePatient,
            'nextInQueue' => $nextInQueue,
            'statusCounts' => $statusCounts,
            'appointments' => $appointments,
            'providers' => $providers,
            'providersSummary' => $providersSummary,
            'lobbyPatients' => $lobbyPatients,
            'services' => $services,
            'statusesList' => $statusesList,
            'pollInterval' => $this->refreshInterval,
            'quickStatusEnabled' => $this->quickStatusEnabled,
            'labelProvider' => $labelProvider,
            'labelProviders' => $labelProviders,
            'labelService' => $labelService,
            'labelServices' => $labelServices,
        ])->layout('layouts.user', ['title' => 'مانیتورینگ زنده'])->title('مانیتورینگ زنده');
    }
}
