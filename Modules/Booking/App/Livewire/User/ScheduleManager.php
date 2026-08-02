<?php

namespace Modules\Booking\App\Livewire\User;

use Livewire\Component;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;
use Morilog\Jalali\CalendarUtils;
use App\Models\User;
use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingService;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Entities\BookingServiceProvider;
use Modules\Booking\Services\BookingEngine;
use Modules\Booking\Services\AppointmentService;
use Modules\Clients\Entities\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ScheduleManager extends Component
{
    public string $selectedDateJalali = '';
    public ?int $selectedProviderId = null;
    public ?int $selectedServiceId = null;
    public string $statusFilter = '';
    public int $timeStepMinutes = 30; // Custom inputtable step
    public string $viewMode = 'grid'; // 'grid' or 'timeline'

    // Quick Creation Modal State
    public bool $showModal = false;
    public ?int $modalProviderId = null;
    public ?int $modalServiceId = null;
    public ?int $modalClientId = null;
    public string $modalClientSearch = '';
    public string $modalStartTime = '';
    public string $modalEndTime = '';
    public string $modalStatus = Appointment::STATUS_CONFIRMED;
    public string $modalNotes = '';
    public string $modalError = '';

    // Quick Details Modal State
    public bool $showDetailsModal = false;
    public ?array $detailsAppointment = null;

    // Alert / Notification State
    public ?string $toastSuccess = null;
    public ?string $toastError = null;

    protected $listeners = [
        'dateSelected' => 'onDateSelected',
        'rescheduleAppointment' => 'rescheduleAppointment',
    ];

    public function mount(): void
    {
        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
        $now = now($scheduleTz);

        if (empty($this->selectedDateJalali)) {
            $this->selectedDateJalali = Jalalian::fromDateTime($now)->format('Y/m/d');
        }

        $this->evaluateStepLock();
    }

    public function updatedSelectedServiceId(): void
    {
        $this->evaluateStepLock();
    }

    protected function evaluateStepLock(): void
    {
        if ($this->selectedServiceId) {
            $service = BookingService::find($this->selectedServiceId);
            if ($service) {
                $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
                $localDate = $this->getGregorianCarbon() ?? now($scheduleTz)->startOfDay();
                $bookingEngine = new BookingEngine();
                $policy = $bookingEngine->resolveDayPolicy($service->id, Auth::id() ?? 1, $localDate);
                if (!empty($policy['slot_duration_minutes'])) {
                    $this->timeStepMinutes = max(5, (int)$policy['slot_duration_minutes']);
                }
            }
        }
    }

    public function updatedTimeStepMinutes($val): void
    {
        if ($this->selectedServiceId) {
            $service = BookingService::find($this->selectedServiceId);
            if ($service && !$service->custom_schedule_enabled) {
                $this->toastError = 'امکان تغییر گام زمانی برای این سرویس وجود ندارد (سرویس سفارشی نیست).';
                return;
            }
        }

        $v = (int)$val;
        if ($v < 5) {
            $this->timeStepMinutes = 5;
        } elseif ($v > 240) {
            $this->timeStepMinutes = 240;
        }
    }

    public function setStep(int $step): void
    {
        if ($this->selectedServiceId) {
            $service = BookingService::find($this->selectedServiceId);
            if ($service && !$service->custom_schedule_enabled) {
                $this->toastError = 'امکان تغییر گام زمانی برای این سرویس وجود ندارد (سرویس سفارشی نیست).';
                return;
            }
        }
        $this->timeStepMinutes = max(5, min(240, $step));
    }

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['grid', 'timeline'])) {
            $this->viewMode = $mode;
        }
    }

    public function updatedSelectedDateJalali(): void
    {
        $this->toastSuccess = null;
        $this->toastError = null;
    }

    public function previousDay(): void
    {
        $gDate = $this->getGregorianCarbon();
        if ($gDate) {
            $gDate->subDay();
            $this->selectedDateJalali = Jalalian::fromDateTime($gDate)->format('Y/m/d');
        }
    }

    public function nextDay(): void
    {
        $gDate = $this->getGregorianCarbon();
        if ($gDate) {
            $gDate->addDay();
            $this->selectedDateJalali = Jalalian::fromDateTime($gDate)->format('Y/m/d');
        }
    }

    public function today(): void
    {
        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
        $now = now($scheduleTz);
        $this->selectedDateJalali = Jalalian::fromDateTime($now)->format('Y/m/d');
    }

    public function onDateSelected(string $dateJalali): void
    {
        $this->selectedDateJalali = $dateJalali;
    }

    public function confirmAllDrafts(): void
    {
        $localDate = $this->getGregorianCarbon();
        if (!$localDate) return;

        $startUtc = $localDate->copy()->timezone('UTC');
        $endUtc = $localDate->copy()->endOfDay()->timezone('UTC');

        $count = Appointment::query()
            ->where('start_at_utc', '<=', $endUtc)
            ->where('end_at_utc', '>=', $startUtc)
            ->where('status', Appointment::STATUS_DRAFT)
            ->update(['status' => Appointment::STATUS_CONFIRMED]);

        if ($count > 0) {
            $this->toastSuccess = sprintf('%d نوبت پیش‌نویس با موفقیت تایید قطعی شدند.', $count);
        } else {
            $this->toastError = 'هیچ نوبت پیش‌نویسی برای تایید یافت نشد.';
        }
    }

    public function rescheduleAppointment(int $appointmentId, int $newProviderId, string $newStartTimeStr): void
    {
        $this->toastSuccess = null;
        $this->toastError = null;

        try {
            $appointment = Appointment::with(['service', 'provider'])->find($appointmentId);
            if (!$appointment) {
                $this->toastError = 'نوبت مورد نظر یافت نشد.';
                return;
            }

            $user = Auth::user();

            if (!$this->isAdminUser($user)) {
                if ($appointment->provider_user_id !== $user->id && $appointment->created_by_user_id !== $user->id) {
                    $this->toastError = 'شما دسترسی لازم برای جابجایی این نوبت را ندارید.';
                    return;
                }
            }

            $localDate = $this->getGregorianCarbon();
            if (!$localDate) {
                $this->toastError = 'تاریخ انتخابی معتبر نیست.';
                return;
            }

            $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');

            $origStart = $appointment->start_at_utc->copy()->timezone($scheduleTz);
            $origEnd = $appointment->end_at_utc->copy()->timezone($scheduleTz);
            $durationMinutes = max(15, $origStart->diffInMinutes($origEnd));

            [$hours, $minutes] = explode(':', $newStartTimeStr);
            $newStartLocal = $localDate->copy()->setTime((int)$hours, (int)$minutes, 0);
            $newEndLocal = $newStartLocal->copy()->addMinutes($durationMinutes);

            $newStartUtc = $newStartLocal->copy()->timezone('UTC');
            $newEndUtc = $newEndLocal->copy()->timezone('UTC');

            $bookingEngine = new BookingEngine();
            $policy = $bookingEngine->resolveDayPolicy($appointment->service_id, $newProviderId, $localDate);

            if ($policy['is_closed']) {
                $this->toastError = 'پزشک انتخابی در این روز برنامه کاری ندارد.';
                return;
            }

            // Check if within work windows
            if (!empty($policy['work_windows'])) {
                $inWorkWindow = false;
                foreach ($policy['work_windows'] as $win) {
                    [$wSH, $wSM] = explode(':', $win['start']);
                    [$wEH, $wEM] = explode(':', $win['end']);
                    $winStart = $localDate->copy()->setTime((int)$wSH, (int)$wSM, 0);
                    $winEnd = $localDate->copy()->setTime((int)$wEH, (int)$wEM, 0);
                    if ($newStartLocal->gte($winStart) && $newEndLocal->lte($winEnd)) {
                        $inWorkWindow = true;
                        break;
                    }
                }
                if (!$inWorkWindow) {
                    $this->toastError = 'ساعت انتخابی خارج از ساعات کاری پزشک در این روز است.';
                    return;
                }
            }

            // Check if in break
            if (!empty($policy['breaks'])) {
                foreach ($policy['breaks'] as $b) {
                    $bStartStr = $b['start_local'] ?? null;
                    $bEndStr = $b['end_local'] ?? null;
                    if ($bStartStr && $bEndStr) {
                        [$bSH, $bSM] = explode(':', $bStartStr);
                        [$bEH, $bEM] = explode(':', $bEndStr);
                        $bStart = $localDate->copy()->setTime((int)$bSH, (int)$bSM, 0);
                        $bEnd = $localDate->copy()->setTime((int)$bEH, (int)$bEM, 0);
                        if ($newStartLocal->lt($bEnd) && $newEndLocal->gt($bStart)) {
                            $this->toastError = 'ساعت انتخابی با زمان استراحت پزشک تداخل دارد.';
                            return;
                        }
                    }
                }
            }

            $capacityPerSlot = (int)($policy['capacity_per_slot'] ?? 0);
            $capacityPerDay = $policy['capacity_per_day'] ?? null;

            if ($capacityPerDay !== null && (int)$capacityPerDay > 0) {
                $startUtcOfDay = $localDate->copy()->timezone('UTC');
                $endUtcOfDay = $localDate->copy()->endOfDay()->timezone('UTC');
                $dailyBookedCount = Appointment::query()
                    ->where('provider_user_id', $newProviderId)
                    ->where('id', '!=', $appointmentId)
                    ->whereNotIn('status', [
                        Appointment::STATUS_CANCELED_BY_ADMIN,
                        Appointment::STATUS_CANCELED_BY_CLIENT,
                    ])
                    ->where('start_at_utc', '<=', $endUtcOfDay)
                    ->where('end_at_utc', '>=', $startUtcOfDay)
                    ->count();

                if ($dailyBookedCount >= (int)$capacityPerDay) {
                    $this->toastError = sprintf('ظرفیت کل روزانه پزشک تکمیل است (حداکثر %d نوبت در روز).', (int)$capacityPerDay);
                    return;
                }
            }

            $existingOverlapCount = Appointment::query()
                ->where('provider_user_id', $newProviderId)
                ->where('id', '!=', $appointmentId)
                ->whereNotIn('status', [
                    Appointment::STATUS_CANCELED_BY_ADMIN,
                    Appointment::STATUS_CANCELED_BY_CLIENT,
                ])
                ->where(function ($q) use ($newStartUtc, $newEndUtc) {
                    $q->where(function ($sub) use ($newStartUtc, $newEndUtc) {
                        $sub->where('start_at_utc', '<', $newEndUtc)
                            ->where('end_at_utc', '>', $newStartUtc);
                    });
                })->count();

            if ($capacityPerSlot > 0 && $existingOverlapCount >= $capacityPerSlot) {
                $this->toastError = sprintf('ظرفیت نوبت‌دهی پزشک در این ساعت تکمیل است (حداکثر %d نوبت).', $capacityPerSlot);
                return;
            }

            $appointment->update([
                'provider_user_id' => $newProviderId,
                'start_at_utc' => $newStartUtc,
                'end_at_utc' => $newEndUtc,
            ]);

            $newProvider = User::find($newProviderId);
            $newProviderName = $newProvider?->name ?? 'پزشک جدید';

            $this->toastSuccess = sprintf(
                'زمان نوبت با موفقیت به ساعت %s (%s) منتقل شد.',
                $newStartTimeStr,
                $newProviderName
            );
        } catch (\Throwable $e) {
            Log::error('Reschedule appointment error: ' . $e->getMessage(), ['exception' => $e]);
            $this->toastError = 'خطا در جابجایی نوبت: ' . $e->getMessage();
        }
    }

    public function updateStatus(int $appointmentId, string $newStatus): void
    {
        $appointment = Appointment::find($appointmentId);
        if ($appointment) {
            $appointment->update(['status' => $newStatus]);
            $this->toastSuccess = 'وضعیت نوبت به‌روزرسانی شد.';
        }
    }

    public function openCreateModal(?int $providerId = null, string $startTimeStr = ''): void
    {
        $this->modalProviderId = $providerId ?? $this->selectedProviderId;
        $this->modalServiceId = $this->selectedServiceId;
        $this->modalClientId = null;
        $this->modalClientSearch = '';
        $this->modalStartTime = $startTimeStr;
        $this->modalStatus = Appointment::STATUS_CONFIRMED;

        if (!empty($startTimeStr)) {
            [$h, $m] = explode(':', $startTimeStr);
            $dur = 30;
            if ($this->modalServiceId && $this->modalProviderId) {
                $bookingEngine = new BookingEngine();
                $localDate = $this->getGregorianCarbon() ?? now();
                $pol = $bookingEngine->resolveDayPolicy($this->modalServiceId, $this->modalProviderId, $localDate);
                $dur = max(5, (int)($pol['slot_duration_minutes'] ?? 30));
            } elseif ($this->timeStepMinutes > 0) {
                $dur = $this->timeStepMinutes;
            }
            $startCb = Carbon::createFromTime((int)$h, (int)$m)->addMinutes($dur);
            $this->modalEndTime = $startCb->format('H:i');
        } else {
            $this->modalEndTime = '';
        }

        $this->modalNotes = '';
        $this->modalError = '';
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->modalError = '';
    }

    public function saveNewAppointment(AppointmentService $appointmentService): void
    {
        $this->modalError = '';

        if (!$this->modalServiceId) {
            $this->modalError = 'لطفاً سرویس را انتخاب کنید.';
            return;
        }

        if (!$this->modalProviderId) {
            $this->modalError = 'لطفاً پزشک/ارائه‌دهنده را انتخاب کنید.';
            return;
        }

        if (!$this->modalClientId) {
            $this->modalError = 'لطفاً بیمار/مشتری را انتخاب کنید.';
            return;
        }

        if (empty($this->modalStartTime)) {
            $this->modalError = 'ساعت شروع الزامی است.';
            return;
        }

        try {
            $localDate = $this->getGregorianCarbon();
            if (!$localDate) {
                $this->modalError = 'تاریخ نامعتبر است.';
                return;
            }

            $bookingEngine = new BookingEngine();
            $policy = $bookingEngine->resolveDayPolicy($this->modalServiceId, $this->modalProviderId, $localDate);

            if ($policy['is_closed']) {
                $this->modalError = 'پزشک انتخابی در این روز برنامه کاری ندارد.';
                return;
            }

            // Check capacity per day
            $capacityPerDay = $policy['capacity_per_day'] ?? null;
            if ($capacityPerDay !== null && (int)$capacityPerDay > 0) {
                $startUtcOfDay = $localDate->copy()->timezone('UTC');
                $endUtcOfDay = $localDate->copy()->endOfDay()->timezone('UTC');
                $dailyBookedCount = Appointment::query()
                    ->where('provider_user_id', $this->modalProviderId)
                    ->whereNotIn('status', [
                        Appointment::STATUS_CANCELED_BY_ADMIN,
                        Appointment::STATUS_CANCELED_BY_CLIENT,
                    ])
                    ->where('start_at_utc', '<=', $endUtcOfDay)
                    ->where('end_at_utc', '>=', $startUtcOfDay)
                    ->count();

                if ($dailyBookedCount >= (int)$capacityPerDay) {
                    $this->modalError = sprintf('ظرفیت کل روزانه پزشک تکمیل است (حداکثر %d نوبت در روز).', (int)$capacityPerDay);
                    return;
                }
            }

            [$sH, $sM] = explode(':', $this->modalStartTime);
            $startLocal = $localDate->copy()->setTime((int)$sH, (int)$sM, 0);

            if (!empty($this->modalEndTime)) {
                [$eH, $eM] = explode(':', $this->modalEndTime);
                $endLocal = $localDate->copy()->setTime((int)$eH, (int)$eM, 0);
            } else {
                $dur = max(5, (int)($policy['slot_duration_minutes'] ?? 30));
                $endLocal = $startLocal->copy()->addMinutes($dur);
            }

            $startUtc = $startLocal->copy()->timezone('UTC');
            $endUtc = $endLocal->copy()->timezone('UTC');

            $appointment = Appointment::create([
                'service_id' => $this->modalServiceId,
                'provider_user_id' => $this->modalProviderId,
                'client_id' => $this->modalClientId,
                'status' => $this->modalStatus ?: Appointment::STATUS_CONFIRMED,
                'start_at_utc' => $startUtc,
                'end_at_utc' => $endUtc,
                'created_by_type' => Appointment::CREATED_BY_OPERATOR,
                'created_by_user_id' => Auth::id(),
                'notes' => $this->modalNotes,
            ]);

            $this->closeModal();
            $this->toastSuccess = 'نوبت با موفقیت ثبت شد.';
        } catch (\Throwable $e) {
            Log::error('Create appointment error: ' . $e->getMessage(), ['exception' => $e]);
            $this->modalError = 'خطا در ثبت نوبت: ' . $e->getMessage();
        }
    }

    public function cancelAppointment(int $appointmentId): void
    {
        $appointment = Appointment::find($appointmentId);
        if ($appointment) {
            $appointment->update([
                'status' => Appointment::STATUS_CANCELED_BY_ADMIN,
                'cancel_reason' => 'لغو توسط اپراتور از تقویم زمان‌بندی',
            ]);
            $this->toastSuccess = 'نوبت لغو شد.';
            if ($this->showDetailsModal && isset($this->detailsAppointment['id']) && $this->detailsAppointment['id'] === $appointmentId) {
                $this->closeDetailsModal();
            }
        }
    }

    public function openDetailsModal(int $appointmentId): void
    {
        $apt = Appointment::with(['service', 'provider', 'client', 'payments'])->find($appointmentId);
        if (!$apt) {
            $this->toastError = 'نوبت مورد نظر یافت نشد.';
            return;
        }

        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
        $startLocal = $apt->start_at_utc?->copy()->timezone($scheduleTz);
        $endLocal = $apt->end_at_utc?->copy()->timezone($scheduleTz);
        $entryLocal = $apt->entry_at_utc?->copy()->timezone($scheduleTz);
        $exitLocal = $apt->exit_at_utc?->copy()->timezone($scheduleTz);

        $dateJalali = $startLocal ? Jalalian::fromDateTime($startLocal)->format('Y/m/d (l)') : '—';
        $startTime = $startLocal ? $startLocal->format('H:i') : '—';
        $endTime = $endLocal ? $endLocal->format('H:i') : '—';

        $statusMap = [
            Appointment::STATUS_DRAFT => ['label' => 'پیش‌نویس', 'badge' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-300'],
            Appointment::STATUS_PENDING => ['label' => 'در انتظار تایید', 'badge' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 border-amber-300'],
            Appointment::STATUS_PENDING_PAYMENT => ['label' => 'در انتظار پرداخت', 'badge' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300 border-yellow-300'],
            Appointment::STATUS_CONFIRMED => ['label' => 'قطعی / تایید شده', 'badge' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-300'],
            Appointment::STATUS_DONE => ['label' => 'انجام شده', 'badge' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border-blue-300'],
            Appointment::STATUS_NO_SHOW => ['label' => 'عدم حضور', 'badge' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-400'],
            Appointment::STATUS_CANCELED_BY_ADMIN => ['label' => 'لغو شده (ادمین)', 'badge' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 border-rose-300'],
            Appointment::STATUS_CANCELED_BY_CLIENT => ['label' => 'لغو شده (مشتری)', 'badge' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 border-rose-300'],
            Appointment::STATUS_RESCHEDULED => ['label' => 'جابجا شده', 'badge' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 border-purple-300'],
        ];

        $statusInfo = $statusMap[$apt->status] ?? ['label' => $apt->status, 'badge' => 'bg-gray-100 text-gray-700'];

        $this->detailsAppointment = [
            'id' => $apt->id,
            'client_name' => $apt->client?->full_name ?? 'بیمار جدید',
            'client_phone' => $apt->client?->phone ?? '—',
            'client_national_code' => $apt->client?->national_code ?? '—',
            'service_name' => $apt->service?->name ?? 'خدمت عمومی',
            'provider_name' => $apt->provider?->name ?? '—',
            'status' => $apt->status,
            'status_label' => $statusInfo['label'],
            'status_badge' => $statusInfo['badge'],
            'date_jalali' => $dateJalali,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'entry_time' => $entryLocal ? $entryLocal->format('H:i') : null,
            'exit_time' => $exitLocal ? $exitLocal->format('H:i') : null,
            'notes' => $apt->notes ?? '',
            'cancel_reason' => $apt->cancel_reason ?? null,
            'created_at' => $apt->created_at ? Jalalian::fromDateTime($apt->created_at)->format('Y/m/d H:i') : '—',
            'show_url' => route('user.booking.appointments.show', $apt->id),
            'edit_url' => route('user.booking.appointments.edit', $apt->id),
        ];

        $this->showDetailsModal = true;
    }

    public function closeDetailsModal(): void
    {
        $this->showDetailsModal = false;
        $this->detailsAppointment = null;
    }

    protected function getGregorianCarbon(): ?Carbon
    {
        if (empty($this->selectedDateJalali)) {
            return null;
        }

        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');

        if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', trim($this->selectedDateJalali), $m)) {
            $gArr = CalendarUtils::toGregorian((int)$m[1], (int)$m[2], (int)$m[3]);
            return Carbon::createFromDate($gArr[0], $gArr[1], $gArr[2], $scheduleTz)->startOfDay();
        }

        return null;
    }

    protected function isAdminUser($user): bool
    {
        return $user && ($user->hasRole('super-admin') || $user->hasRole('admin') || $user->can('booking.admin'));
    }

    public function render()
    {
        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
        $user = Auth::user();
        $settings = BookingSetting::current();
        $bookingEngine = new BookingEngine();

        $localDate = $this->getGregorianCarbon() ?? now($scheduleTz)->startOfDay();
        $dayOfWeekJalali = Jalalian::fromDateTime($localDate)->format('l (Y/m/d)');

        // Check if step editing should be locked
        $isStepLocked = false;
        $activeService = null;
        if ($this->selectedServiceId) {
            $activeService = BookingService::find($this->selectedServiceId);
            if ($activeService && !$activeService->custom_schedule_enabled) {
                $isStepLocked = true;
                $this->timeStepMinutes = max(5, (int)($activeService->duration_minutes ?? 30));
            }
        }

        // Fetch Services
        $services = BookingService::query()
            ->where('status', BookingService::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        // Fetch Providers
        $roleIds = array_values(array_filter(
            array_map('intval', (array) ($settings->allowed_roles ?? [])),
            fn($v) => $v > 0
        ));

        $providersQuery = User::query();

        if (!empty($roleIds)) {
            $providersQuery->whereHas('roles', fn($r) => $r->whereIn('id', $roleIds));
        } else {
            $providersQuery->whereIn('id', function ($q) {
                $q->select('provider_user_id')->from('booking_service_providers')->where('is_active', true);
            });
        }

        if (!$this->isAdminUser($user) && !$user->can('booking.appointments.view.all')) {
            $providersQuery->where('id', $user->id);
        }

        if ($this->selectedProviderId) {
            $providersQuery->where('id', $this->selectedProviderId);
        }

        $providers = $providersQuery->orderBy('name')->get();

        // Fetch Appointments for selected date
        $startUtc = $localDate->copy()->timezone('UTC');
        $endUtc = $localDate->copy()->endOfDay()->timezone('UTC');

        $appointmentsQuery = Appointment::query()
            ->with(['service', 'provider', 'client'])
            ->where('start_at_utc', '<=', $endUtc)
            ->where('end_at_utc', '>=', $startUtc)
            ->whereNotIn('status', [
                Appointment::STATUS_CANCELED_BY_ADMIN,
                Appointment::STATUS_CANCELED_BY_CLIENT,
            ]);

        if ($this->selectedServiceId) {
            $appointmentsQuery->where('service_id', $this->selectedServiceId);
        }

        if ($this->statusFilter) {
            $appointmentsQuery->where('status', $this->statusFilter);
        }

        $allAppointments = $appointmentsQuery->get();

        // Dashboard Metrics
        $totalAppointmentsCount = $allAppointments->count();
        $confirmedCount = $allAppointments->where('status', Appointment::STATUS_CONFIRMED)->count();
        $draftCount = $allAppointments->where('status', Appointment::STATUS_DRAFT)->count();
        $pendingCount = $allAppointments->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_PENDING_PAYMENT])->count();
        $doneCount = $allAppointments->where('status', Appointment::STATUS_DONE)->count();

        // Global Timeline bounds (default 08:00 to 20:00)
        $minStartMinutes = 8 * 60;
        $maxEndMinutes = 20 * 60;

        foreach ($providers as $p) {
            $svcId = $this->selectedServiceId;
            if (!$svcId) {
                $spRow = BookingServiceProvider::where('provider_user_id', $p->id)->where('is_active', true)->first();
                $svcId = $spRow?->service_id ?? ($services->first()?->id ?? 1);
            }
            $pPol = $bookingEngine->resolveDayPolicy($svcId, (int)$p->id, $localDate);
            if (!$pPol['is_closed'] && !empty($pPol['work_windows'])) {
                foreach ($pPol['work_windows'] as $win) {
                    [$wSH, $wSM] = explode(':', $win['start']);
                    [$wEH, $wEM] = explode(':', $win['end']);
                    $minStartMinutes = min($minStartMinutes, ((int)$wSH * 60) + (int)$wSM);
                    $maxEndMinutes = max($maxEndMinutes, ((int)$wEH * 60) + (int)$wEM);
                }
            }

            $pAppts = $allAppointments->where('provider_user_id', $p->id);
            foreach ($pAppts as $apt) {
                $aptStartLocal = $apt->start_at_utc->copy()->timezone($scheduleTz);
                $aptEndLocal = $apt->end_at_utc->copy()->timezone($scheduleTz);
                $minStartMinutes = min($minStartMinutes, ($aptStartLocal->hour * 60) + $aptStartLocal->minute);
                $maxEndMinutes = max($maxEndMinutes, ($aptEndLocal->hour * 60) + $aptEndLocal->minute);
            }
        }

        $gridStartHour = (int)floor($minStartMinutes / 60);
        $gridEndHour = (int)ceil($maxEndMinutes / 60);
        if ($gridEndHour <= $gridStartHour) {
            $gridEndHour = $gridStartHour + 12;
        }

        $totalGridMinutes = ($gridEndHour - $gridStartHour) * 60;
        $gridStartMinutes = $gridStartHour * 60;

        // Active Step
        $stepMins = max(5, $this->timeStepMinutes);

        // Generate Time Header Columns for Gantt Timeline
        $timelineHeaders = [];
        $curr = $gridStartMinutes;
        while ($curr < $gridEndHour * 60) {
            $h = (int)floor($curr / 60);
            $m = $curr % 60;
            $timelineHeaders[] = [
                'time_str' => sprintf('%02d:%02d', $h, $m),
                'left_percent' => (($curr - $gridStartMinutes) / $totalGridMinutes) * 100,
                'width_percent' => ($stepMins / $totalGridMinutes) * 100,
            ];
            $curr += $stepMins;
        }

        // Build Provider Schedules
        $providerSchedules = [];
        $totalRemainingCapacitySum = 0;

        foreach ($providers as $provider) {
            $serviceId = $this->selectedServiceId;
            if (!$serviceId) {
                $spRow = BookingServiceProvider::where('provider_user_id', $provider->id)->where('is_active', true)->first();
                $serviceId = $spRow?->service_id ?? ($services->first()?->id ?? 1);
            }

            $policy = $bookingEngine->resolveDayPolicy($serviceId, (int)$provider->id, $localDate);

            // Effective Slot Duration: use timeStepMinutes when unlocked so UI toolbar step changes take immediate effect
            if ($isStepLocked) {
                $effectiveSlotDuration = max(5, (int)($policy['slot_duration_minutes'] ?? 30));
            } else {
                $effectiveSlotDuration = max(5, (int)($this->timeStepMinutes > 0 ? $this->timeStepMinutes : ($policy['slot_duration_minutes'] ?? 30)));
            }

            $capacityPerSlot = (int)($policy['capacity_per_slot'] ?? 1);
            $capacityPerDay = $policy['capacity_per_day'] ?? null;
            $workWindows = $policy['work_windows'] ?? [];
            $breaks = $policy['breaks'] ?? [];
            $isClosed = $policy['is_closed'] || empty($workWindows);

            // Total booked appointments for this provider today
            $providerAppointmentsToday = $allAppointments->where('provider_user_id', $provider->id);
            $dailyBookedTotal = $providerAppointmentsToday->count();
            $dailyRemaining = ($capacityPerDay !== null && (int)$capacityPerDay > 0)
                ? max(0, (int)$capacityPerDay - $dailyBookedTotal)
                : null;

            // Dynamic Slots for Grid View
            $providerSlots = [];
            if (!$isClosed) {
                foreach ($workWindows as $win) {
                    $winStart = Carbon::createFromFormat('Y-m-d H:i', $localDate->format('Y-m-d') . ' ' . $win['start'], $scheduleTz);
                    $winEnd = Carbon::createFromFormat('Y-m-d H:i', $localDate->format('Y-m-d') . ' ' . $win['end'], $scheduleTz);

                    if ($winEnd->lte($winStart)) continue;

                    $cursor = $winStart->copy();
                    while ($cursor->copy()->addMinutes($effectiveSlotDuration)->lte($winEnd)) {
                        $slotStart = $cursor->copy();
                        $slotEnd = $cursor->copy()->addMinutes($effectiveSlotDuration);

                        $inBreak = false;
                        foreach ($breaks as $b) {
                            $bStartStr = $b['start_local'] ?? null;
                            $bEndStr = $b['end_local'] ?? null;
                            if ($bStartStr && $bEndStr) {
                                $bStart = Carbon::createFromFormat('Y-m-d H:i', $localDate->format('Y-m-d') . ' ' . $bStartStr, $scheduleTz);
                                $bEnd = Carbon::createFromFormat('Y-m-d H:i', $localDate->format('Y-m-d') . ' ' . $bEndStr, $scheduleTz);
                                if ($slotStart->lt($bEnd) && $slotEnd->gt($bStart)) {
                                    $inBreak = true;
                                    break;
                                }
                            }
                        }

                        $slotStartUtc = $slotStart->copy()->timezone('UTC');
                        $slotEndUtc = $slotEnd->copy()->timezone('UTC');

                        $slotAppts = $allAppointments->where('provider_user_id', $provider->id)
                            ->filter(function ($apt) use ($slotStartUtc, $slotEndUtc) {
                                return $apt->start_at_utc < $slotEndUtc && $apt->end_at_utc > $slotStartUtc;
                            });

                        $formattedSlotAppts = [];
                        foreach ($slotAppts as $apt) {
                            $aptStartLocal = $apt->start_at_utc->copy()->timezone($scheduleTz);
                            $aptEndLocal = $apt->end_at_utc->copy()->timezone($scheduleTz);

                            $formattedSlotAppts[] = [
                                'id' => $apt->id,
                                'client_name' => $apt->client?->full_name ?? 'بیمار جدید',
                                'client_phone' => $apt->client?->phone ?? '',
                                'service_name' => $apt->service?->name ?? 'خدمت عمومی',
                                'status' => $apt->status,
                                'start_time' => $aptStartLocal->format('H:i'),
                                'end_time' => $aptEndLocal->format('H:i'),
                                'notes' => $apt->notes,
                            ];
                        }

                        $bookedCount = count($formattedSlotAppts);
                        $slotRemaining = $capacityPerSlot > 0 ? max(0, $capacityPerSlot - $bookedCount) : null;

                        if ($dailyRemaining !== null) {
                            if ($slotRemaining === null) {
                                $slotRemaining = $dailyRemaining;
                            } else {
                                $slotRemaining = min($slotRemaining, $dailyRemaining);
                            }
                        }

                        $isFull = ($capacityPerSlot > 0 && $bookedCount >= $capacityPerSlot) || ($dailyRemaining !== null && $dailyRemaining <= 0);

                        if (!$inBreak && $slotRemaining !== null) {
                            $totalRemainingCapacitySum += $slotRemaining;
                        }

                        $providerSlots[] = [
                            'start_time' => $slotStart->format('H:i'),
                            'end_time' => $slotEnd->format('H:i'),
                            'in_break' => $inBreak,
                            'capacity' => $capacityPerSlot,
                            'booked_count' => $bookedCount,
                            'remaining_capacity' => $slotRemaining,
                            'is_full' => $isFull,
                            'appointments' => $formattedSlotAppts,
                        ];

                        $cursor->addMinutes($effectiveSlotDuration);
                    }
                }
            }

            // Mapped Appointments for Gantt Timeline Track
            $providerAppointments = $allAppointments->where('provider_user_id', $provider->id);

            $formattedAppointments = [];
            foreach ($providerAppointments as $apt) {
                $aptStartLocal = $apt->start_at_utc->copy()->timezone($scheduleTz);
                $aptEndLocal = $apt->end_at_utc->copy()->timezone($scheduleTz);

                $sMins = ($aptStartLocal->hour * 60) + $aptStartLocal->minute;
                $eMins = ($aptEndLocal->hour * 60) + $aptEndLocal->minute;

                $leftPercent = max(0, (($sMins - $gridStartMinutes) / $totalGridMinutes) * 100);
                $widthPercent = max(1.5, ((min($eMins, $gridEndHour * 60) - max($sMins, $gridStartMinutes)) / $totalGridMinutes) * 100);

                $formattedAppointments[] = [
                    'id' => $apt->id,
                    'client_name' => $apt->client?->full_name ?? 'بیمار جدید',
                    'client_phone' => $apt->client?->phone ?? '',
                    'service_name' => $apt->service?->name ?? 'خدمت عمومی',
                    'status' => $apt->status,
                    'start_time' => $aptStartLocal->format('H:i'),
                    'end_time' => $aptEndLocal->format('H:i'),
                    'left_percent' => $leftPercent,
                    'width_percent' => $widthPercent,
                    'notes' => $apt->notes,
                ];
            }

            // Mapped Breaks for Gantt Timeline Track
            $formattedBreaks = [];
            foreach ($breaks as $b) {
                $bStartStr = $b['start_local'] ?? null;
                $bEndStr = $b['end_local'] ?? null;
                if ($bStartStr && $bEndStr) {
                    [$bSH, $bSM] = explode(':', $bStartStr);
                    [$bEH, $bEM] = explode(':', $bEndStr);
                    $bSMins = ((int)$bSH * 60) + (int)$bSM;
                    $bEMins = ((int)$bEH * 60) + (int)$bEM;

                    $leftPercent = max(0, (($bSMins - $gridStartMinutes) / $totalGridMinutes) * 100);
                    $widthPercent = max(1.0, (($bEMins - $bSMins) / $totalGridMinutes) * 100);

                    $formattedBreaks[] = [
                        'start_time' => $bStartStr,
                        'end_time' => $bEndStr,
                        'left_percent' => $leftPercent,
                        'width_percent' => $widthPercent,
                    ];
                }
            }

            // Slot Drop Targets for Gantt Timeline Track
            $slotDropTargets = [];
            if (!$isClosed) {
                foreach ($workWindows as $win) {
                    $winStart = Carbon::createFromFormat('Y-m-d H:i', $localDate->format('Y-m-d') . ' ' . $win['start'], $scheduleTz);
                    $winEnd = Carbon::createFromFormat('Y-m-d H:i', $localDate->format('Y-m-d') . ' ' . $win['end'], $scheduleTz);

                    if ($winEnd->lte($winStart)) continue;

                    $cursor = $winStart->copy();
                    while ($cursor->copy()->addMinutes($effectiveSlotDuration)->lte($winEnd)) {
                        $slotStart = $cursor->copy();
                        $slotEnd = $cursor->copy()->addMinutes($effectiveSlotDuration);

                        $sMins = ($slotStart->hour * 60) + $slotStart->minute;
                        $leftPercent = (($sMins - $gridStartMinutes) / $totalGridMinutes) * 100;
                        $widthPercent = ($effectiveSlotDuration / $totalGridMinutes) * 100;

                        $slotDropTargets[] = [
                            'start_time' => $slotStart->format('H:i'),
                            'end_time' => $slotEnd->format('H:i'),
                            'left_percent' => $leftPercent,
                            'width_percent' => $widthPercent,
                        ];

                        $cursor->addMinutes($effectiveSlotDuration);
                    }
                }
            }

            $providerSchedules[] = [
                'provider' => $provider,
                'policy' => $policy,
                'capacity_per_day' => $capacityPerDay,
                'daily_booked' => $dailyBookedTotal,
                'daily_remaining' => $dailyRemaining,
                'effective_slot_duration' => $effectiveSlotDuration,
                'slots' => $providerSlots,
                'appointments' => $formattedAppointments,
                'breaks' => $formattedBreaks,
                'slotDropTargets' => $slotDropTargets,
            ];
        }

        // Search Clients for Modal
        $clientsForModal = [];
        if ($this->showModal && !empty($this->modalClientSearch)) {
            $q = $this->modalClientSearch;
            $clientsForModal = Client::query()
                ->where('full_name', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
                ->orWhere('national_code', 'like', "%{$q}%")
                ->limit(10)
                ->get();
        }

        return view('booking::livewire.user.schedule-manager', [
            'localDate' => $localDate,
            'dayOfWeekJalali' => $dayOfWeekJalali,
            'services' => $services,
            'providers' => $providers,
            'timelineHeaders' => $timelineHeaders,
            'providerSchedules' => $providerSchedules,
            'clientsForModal' => $clientsForModal,
            'gridStartHour' => $gridStartHour,
            'gridEndHour' => $gridEndHour,
            'isStepLocked' => $isStepLocked,
            'activeService' => $activeService,
            'totalAppointmentsCount' => $totalAppointmentsCount,
            'confirmedCount' => $confirmedCount,
            'draftCount' => $draftCount,
            'pendingCount' => $pendingCount,
            'doneCount' => $doneCount,
            'totalRemainingCapacitySum' => $totalRemainingCapacitySum,
        ]);
    }
}
