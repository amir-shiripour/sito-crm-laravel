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
    public string $calendarView = 'day'; // 'day', 'week', 'month'

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

        if ($val === '' || $val === null) {
            return;
        }

        $v = (int)$val;
        if ($v >= 5 && $v <= 480) {
            $this->timeStepMinutes = $v;
        } elseif ($v > 480) {
            $this->timeStepMinutes = 480;
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
        $this->timeStepMinutes = max(5, min(480, $step));
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

    public function setCalendarView(string $view): void
    {
        if (in_array($view, ['day', 'week', 'month'])) {
            $this->calendarView = $view;
        }
    }

    public function goToDay(string $dateJalali): void
    {
        $this->selectedDateJalali = $dateJalali;
        $this->calendarView = 'day';
    }

    public function previousPeriod(): void
    {
        if ($this->calendarView === 'week') {
            $this->previousWeek();
        } elseif ($this->calendarView === 'month') {
            $this->previousMonth();
        } else {
            $this->previousDay();
        }
    }

    public function nextPeriod(): void
    {
        if ($this->calendarView === 'week') {
            $this->nextWeek();
        } elseif ($this->calendarView === 'month') {
            $this->nextMonth();
        } else {
            $this->nextDay();
        }
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

    public function previousWeek(): void
    {
        $gDate = $this->getGregorianCarbon();
        if ($gDate) {
            $gDate->subDays(7);
            $this->selectedDateJalali = Jalalian::fromDateTime($gDate)->format('Y/m/d');
        }
    }

    public function nextWeek(): void
    {
        $gDate = $this->getGregorianCarbon();
        if ($gDate) {
            $gDate->addDays(7);
            $this->selectedDateJalali = Jalalian::fromDateTime($gDate)->format('Y/m/d');
        }
    }

    public function previousMonth(): void
    {
        $gDate = $this->getGregorianCarbon();
        if ($gDate) {
            $j = Jalalian::fromDateTime($gDate);
            $m = $j->getMonth() - 1;
            $y = $j->getYear();
            if ($m < 1) {
                $m = 12;
                $y--;
            }
            $d = min($j->getDay(), 28);
            $g = CalendarUtils::toGregorian($y, $m, $d);
            $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
            $newDate = Carbon::createFromDate($g[0], $g[1], $g[2], $scheduleTz);
            $this->selectedDateJalali = Jalalian::fromDateTime($newDate)->format('Y/m/d');
        }
    }

    public function nextMonth(): void
    {
        $gDate = $this->getGregorianCarbon();
        if ($gDate) {
            $j = Jalalian::fromDateTime($gDate);
            $m = $j->getMonth() + 1;
            $y = $j->getYear();
            if ($m > 12) {
                $m = 1;
                $y++;
            }
            $d = min($j->getDay(), 28);
            $g = CalendarUtils::toGregorian($y, $m, $d);
            $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
            $newDate = Carbon::createFromDate($g[0], $g[1], $g[2], $scheduleTz);
            $this->selectedDateJalali = Jalalian::fromDateTime($newDate)->format('Y/m/d');
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

    protected function calculateFreeSegments(Carbon $slotStart, Carbon $slotEnd, array $appointments, array $breaks): array
    {
        $slotStartMins = ($slotStart->hour * 60) + $slotStart->minute;
        $slotEndMins = ($slotEnd->hour * 60) + $slotEnd->minute;

        $busyIntervals = [];

        foreach ($breaks as $b) {
            $bStartStr = $b['start_local'] ?? null;
            $bEndStr = $b['end_local'] ?? null;
            if ($bStartStr && $bEndStr) {
                [$bSH, $bSM] = explode(':', $bStartStr);
                [$bEH, $bEM] = explode(':', $bEndStr);
                $bS = ((int)$bSH * 60) + (int)$bSM;
                $bE = ((int)$bEH * 60) + (int)$bEM;
                $s = max($slotStartMins, $bS);
                $e = min($slotEndMins, $bE);
                if ($s < $e) {
                    $busyIntervals[] = ['start' => $s, 'end' => $e];
                }
            }
        }

        foreach ($appointments as $apt) {
            $startCarbon = $apt['start_time_carbon'] ?? null;
            $endCarbon = $apt['end_time_carbon'] ?? null;
            if ($startCarbon && $endCarbon) {
                $s = ($startCarbon->hour * 60) + $startCarbon->minute;
                $e = ($endCarbon->hour * 60) + $endCarbon->minute;
                $s = max($slotStartMins, $s);
                $e = min($slotEndMins, $e);
                if ($s < $e) {
                    $busyIntervals[] = ['start' => $s, 'end' => $e];
                }
            }
        }

        if (empty($busyIntervals)) {
            $dur = $slotEndMins - $slotStartMins;
            return [[
                'start_time' => $slotStart->format('H:i'),
                'end_time' => $slotEnd->format('H:i'),
                'duration_minutes' => $dur,
                'formatted_duration' => $this->formatMinutesToHuman($dur),
            ]];
        }

        usort($busyIntervals, fn($a, $b) => $a['start'] <=> $b['start']);

        $mergedBusy = [];
        foreach ($busyIntervals as $cur) {
            if (empty($mergedBusy)) {
                $mergedBusy[] = $cur;
            } else {
                $lastIdx = count($mergedBusy) - 1;
                if ($cur['start'] <= $mergedBusy[$lastIdx]['end']) {
                    $mergedBusy[$lastIdx]['end'] = max($mergedBusy[$lastIdx]['end'], $cur['end']);
                } else {
                    $mergedBusy[] = $cur;
                }
            }
        }

        $freeSegments = [];
        $cursor = $slotStartMins;

        foreach ($mergedBusy as $busy) {
            if ($busy['start'] > $cursor) {
                $dur = $busy['start'] - $cursor;
                if ($dur >= 5) {
                    $hS = (int)floor($cursor / 60);
                    $mS = $cursor % 60;
                    $hE = (int)floor($busy['start'] / 60);
                    $mE = $busy['start'] % 60;
                    $freeSegments[] = [
                        'start_time' => sprintf('%02d:%02d', $hS, $mS),
                        'end_time' => sprintf('%02d:%02d', $hE, $mE),
                        'duration_minutes' => $dur,
                        'formatted_duration' => $this->formatMinutesToHuman($dur),
                    ];
                }
            }
            $cursor = max($cursor, $busy['end']);
        }

        if ($cursor < $slotEndMins) {
            $dur = $slotEndMins - $cursor;
            if ($dur >= 5) {
                $hS = (int)floor($cursor / 60);
                $mS = $cursor % 60;
                $hE = (int)floor($slotEndMins / 60);
                $mE = $slotEndMins % 60;
                $freeSegments[] = [
                    'start_time' => sprintf('%02d:%02d', $hS, $mS),
                    'end_time' => sprintf('%02d:%02d', $hE, $mE),
                    'duration_minutes' => $dur,
                    'formatted_duration' => $this->formatMinutesToHuman($dur),
                ];
            }
        }

        return $freeSegments;
    }

    protected function formatMinutesToHuman(int $minutes): string
    {
        if ($minutes < 60) {
            return sprintf('%d دقیقه', $minutes);
        }
        $hours = (int)floor($minutes / 60);
        $remMins = $minutes % 60;
        if ($remMins === 0) {
            return sprintf('%d ساعت', $hours);
        }
        return sprintf('%d ساعت و %d دقیقه', $hours, $remMins);
    }

    protected function getJalaliWeekRange(Carbon $gDate): array
    {
        $date = $gDate->copy()->startOfDay();
        $offset = ($date->dayOfWeek + 1) % 7;
        $saturday = $date->copy()->subDays($offset);

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $dayCarbon = $saturday->copy()->addDays($i);
            $j = Jalalian::fromDateTime($dayCarbon);
            $days[] = [
                'carbon' => $dayCarbon,
                'jalali_date' => $j->format('Y/m/d'),
                'day_name' => $j->format('l'),
                'day_num' => $j->format('d'),
                'month_name' => $j->format('F'),
                'is_today' => $dayCarbon->isToday(),
            ];
        }

        return $days;
    }

    protected function getJalaliMonthGrid(Carbon $gDate): array
    {
        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
        $j = Jalalian::fromDateTime($gDate);
        $year = $j->getYear();
        $month = $j->getMonth();
        $monthLength = $j->getMonthDays();

        $gFirst = CalendarUtils::toGregorian($year, $month, 1);
        $firstDayCarbon = Carbon::createFromDate($gFirst[0], $gFirst[1], $gFirst[2], $scheduleTz)->startOfDay();

        $leadingEmptyCount = ($firstDayCarbon->dayOfWeek + 1) % 7;

        $grid = [];
        $currentWeek = [];

        for ($i = 0; $i < $leadingEmptyCount; $i++) {
            $currentWeek[] = null;
        }

        for ($d = 1; $d <= $monthLength; $d++) {
            $gDay = CalendarUtils::toGregorian($year, $month, $d);
            $dayCarbon = Carbon::createFromDate($gDay[0], $gDay[1], $gDay[2], $scheduleTz)->startOfDay();
            $jalaliStr = sprintf('%04d/%02d/%02d', $year, $month, $d);

            $currentWeek[] = [
                'day_num' => $d,
                'jalali_date' => $jalaliStr,
                'carbon' => $dayCarbon,
                'is_today' => $dayCarbon->isToday(),
            ];

            if (count($currentWeek) === 7) {
                $grid[] = $currentWeek;
                $currentWeek = [];
            }
        }

        if (!empty($currentWeek)) {
            while (count($currentWeek) < 7) {
                $currentWeek[] = null;
            }
            $grid[] = $currentWeek;
        }

        return [
            'year' => $year,
            'month' => $month,
            'month_name' => $j->format('F'),
            'year_month_title' => $j->format('F Y'),
            'grid' => $grid,
        ];
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
                $pServicePolicy = $bookingEngine->resolveDayPolicy($activeService->id, Auth::id() ?? 1, $localDate);
                $this->timeStepMinutes = max(5, (int)($pServicePolicy['slot_duration_minutes'] ?? 30));
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

        $weekDays = [];
        $weekProviderSchedules = [];
        $monthData = [];

        if ($this->calendarView === 'week') {
            $weekDays = $this->getJalaliWeekRange($localDate);
            $weekStartCarbon = $weekDays[0]['carbon']->copy()->startOfDay();
            $weekEndCarbon = $weekDays[6]['carbon']->copy()->endOfDay();

            $weekStartUtc = $weekStartCarbon->copy()->timezone('UTC');
            $weekEndUtc = $weekEndCarbon->copy()->timezone('UTC');

            $weekAppointmentsQuery = Appointment::query()
                ->with(['service', 'provider', 'client'])
                ->where('start_at_utc', '<=', $weekEndUtc)
                ->where('end_at_utc', '>=', $weekStartUtc)
                ->whereNotIn('status', [
                    Appointment::STATUS_CANCELED_BY_ADMIN,
                    Appointment::STATUS_CANCELED_BY_CLIENT,
                ]);

            if ($this->selectedServiceId) {
                $weekAppointmentsQuery->where('service_id', $this->selectedServiceId);
            }
            if ($this->statusFilter) {
                $weekAppointmentsQuery->where('status', $this->statusFilter);
            }

            $allWeekAppointments = $weekAppointmentsQuery->get();

            $totalAppointmentsCount = $allWeekAppointments->count();
            $confirmedCount = $allWeekAppointments->where('status', Appointment::STATUS_CONFIRMED)->count();
            $draftCount = $allWeekAppointments->where('status', Appointment::STATUS_DRAFT)->count();
            $pendingCount = $allWeekAppointments->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_PENDING_PAYMENT])->count();
            $doneCount = $allWeekAppointments->where('status', Appointment::STATUS_DONE)->count();
            $totalRemainingCapacitySum = 0;

            foreach ($providers as $provider) {
                $providerDays = [];

                foreach ($weekDays as $wDay) {
                    $dayCarbon = $wDay['carbon'];
                    $serviceId = $this->selectedServiceId;
                    if (!$serviceId) {
                        $spRow = BookingServiceProvider::where('provider_user_id', $provider->id)->where('is_active', true)->first();
                        $serviceId = $spRow?->service_id ?? ($services->first()?->id ?? 1);
                    }

                    $policy = $bookingEngine->resolveDayPolicy($serviceId, (int)$provider->id, $dayCarbon);
                    $workWindows = $policy['work_windows'] ?? [];
                    $breaks = $policy['breaks'] ?? [];
                    $isClosed = $policy['is_closed'] || empty($workWindows);

                    $dStartUtc = $dayCarbon->copy()->startOfDay()->timezone('UTC');
                    $dEndUtc = $dayCarbon->copy()->endOfDay()->timezone('UTC');

                    $dayAppts = $allWeekAppointments->where('provider_user_id', $provider->id)
                        ->filter(function ($apt) use ($dStartUtc, $dEndUtc) {
                            return $apt->start_at_utc <= $dEndUtc && $apt->end_at_utc >= $dStartUtc;
                        });

                    $capacityPerDay = $policy['capacity_per_day'] ?? null;
                    $dailyBookedTotal = $dayAppts->count();
                    $dailyRemaining = ($capacityPerDay !== null && (int)$capacityPerDay > 0)
                        ? max(0, (int)$capacityPerDay - $dailyBookedTotal)
                        : null;

                    if (!$isClosed && $dailyRemaining !== null) {
                        $totalRemainingCapacitySum += $dailyRemaining;
                    }

                    $trackStartMins = 8 * 60;
                    $trackTotalMins = 12 * 60; // 08:00 - 20:00
                    $formattedDayAppts = [];

                    foreach ($dayAppts as $apt) {
                        $aptStartLocal = $apt->start_at_utc->copy()->timezone($scheduleTz);
                        $aptEndLocal = $apt->end_at_utc->copy()->timezone($scheduleTz);

                        $sMins = ($aptStartLocal->hour * 60) + $aptStartLocal->minute;
                        $eMins = ($aptEndLocal->hour * 60) + $aptEndLocal->minute;

                        $leftPercent = max(0, min(100, (($sMins - $trackStartMins) / $trackTotalMins) * 100));
                        $widthPercent = max(2, min(100, (($eMins - $sMins) / $trackTotalMins) * 100));

                        $formattedDayAppts[] = [
                            'id' => $apt->id,
                            'client_name' => $apt->client?->full_name ?? 'بیمار جدید',
                            'service_name' => $apt->service?->name ?? '',
                            'status' => $apt->status,
                            'start_time' => $aptStartLocal->format('H:i'),
                            'end_time' => $aptEndLocal->format('H:i'),
                            'left_percent' => $leftPercent,
                            'width_percent' => $widthPercent,
                        ];
                    }

                    $providerDays[] = [
                        'jalali_date' => $wDay['jalali_date'],
                        'day_name' => $wDay['day_name'],
                        'day_num' => $wDay['day_num'],
                        'is_today' => $wDay['is_today'],
                        'is_closed' => $isClosed,
                        'policy' => $policy,
                        'capacity_per_day' => $capacityPerDay,
                        'daily_booked' => $dailyBookedTotal,
                        'daily_remaining' => $dailyRemaining,
                        'appointments' => $formattedDayAppts,
                    ];
                }

                $weekProviderSchedules[] = [
                    'provider' => $provider,
                    'days' => $providerDays,
                ];
            }

            $timelineHeaders = [];
            $providerSchedules = [];
            $gridStartHour = 8;
            $gridEndHour = 20;

        } elseif ($this->calendarView === 'month') {
            $monthInfo = $this->getJalaliMonthGrid($localDate);

            $j = Jalalian::fromDateTime($localDate);
            $gStartArr = CalendarUtils::toGregorian($j->getYear(), $j->getMonth(), 1);
            $gEndArr = CalendarUtils::toGregorian($j->getYear(), $j->getMonth(), $j->getMonthDays());

            $mStartCarbon = Carbon::createFromDate($gStartArr[0], $gStartArr[1], $gStartArr[2], $scheduleTz)->startOfDay();
            $mEndCarbon = Carbon::createFromDate($gEndArr[0], $gEndArr[1], $gEndArr[2], $scheduleTz)->endOfDay();

            $mStartUtc = $mStartCarbon->copy()->timezone('UTC');
            $mEndUtc = $mEndCarbon->copy()->timezone('UTC');

            $monthAppointmentsQuery = Appointment::query()
                ->with(['service', 'provider', 'client'])
                ->where('start_at_utc', '<=', $mEndUtc)
                ->where('end_at_utc', '>=', $mStartUtc)
                ->whereNotIn('status', [
                    Appointment::STATUS_CANCELED_BY_ADMIN,
                    Appointment::STATUS_CANCELED_BY_CLIENT,
                ]);

            if ($this->selectedServiceId) {
                $monthAppointmentsQuery->where('service_id', $this->selectedServiceId);
            }
            if ($this->statusFilter) {
                $monthAppointmentsQuery->where('status', $this->statusFilter);
            }
            if ($this->selectedProviderId) {
                $monthAppointmentsQuery->where('provider_user_id', $this->selectedProviderId);
            }

            $allMonthAppointments = $monthAppointmentsQuery->get();

            $totalAppointmentsCount = $allMonthAppointments->count();
            $confirmedCount = $allMonthAppointments->where('status', Appointment::STATUS_CONFIRMED)->count();
            $draftCount = $allMonthAppointments->where('status', Appointment::STATUS_DRAFT)->count();
            $pendingCount = $allMonthAppointments->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_PENDING_PAYMENT])->count();
            $doneCount = $allMonthAppointments->where('status', Appointment::STATUS_DONE)->count();
            $totalRemainingCapacitySum = 0;

            $enrichedMonthGrid = [];
            foreach ($monthInfo['grid'] as $weekRow) {
                $enrichedRow = [];
                foreach ($weekRow as $cell) {
                    if ($cell === null) {
                        $enrichedRow[] = null;
                        continue;
                    }

                    $dayCarbon = $cell['carbon'];
                    $dStartUtc = $dayCarbon->copy()->startOfDay()->timezone('UTC');
                    $dEndUtc = $dayCarbon->copy()->endOfDay()->timezone('UTC');

                    $dayAppts = $allMonthAppointments->filter(function ($apt) use ($dStartUtc, $dEndUtc) {
                        return $apt->start_at_utc <= $dEndUtc && $apt->end_at_utc >= $dStartUtc;
                    });

                    $cell['total_appts'] = $dayAppts->count();
                    $cell['confirmed_count'] = $dayAppts->where('status', Appointment::STATUS_CONFIRMED)->count();
                    $cell['draft_count'] = $dayAppts->where('status', Appointment::STATUS_DRAFT)->count();
                    $cell['pending_count'] = $dayAppts->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_PENDING_PAYMENT])->count();
                    $cell['done_count'] = $dayAppts->where('status', Appointment::STATUS_DONE)->count();

                    $enrichedRow[] = $cell;
                }
                $enrichedMonthGrid[] = $enrichedRow;
            }

            $monthData = [
                'year' => $monthInfo['year'],
                'month' => $monthInfo['month'],
                'month_name' => $monthInfo['month_name'],
                'year_month_title' => $monthInfo['year_month_title'],
                'grid' => $enrichedMonthGrid,
            ];

            $timelineHeaders = [];
            $providerSchedules = [];
            $gridStartHour = 8;
            $gridEndHour = 20;

        } else {
            // Day View
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

        // Global Timeline bounds (Calculated dynamically based on work schedule hierarchy)
        $foundMinMinutes = null;
        $foundMaxMinutes = null;

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
                    $wStartMins = ((int)$wSH * 60) + (int)$wSM;
                    $wEndMins = ((int)$wEH * 60) + (int)$wEM;

                    $foundMinMinutes = ($foundMinMinutes === null) ? $wStartMins : min($foundMinMinutes, $wStartMins);
                    $foundMaxMinutes = ($foundMaxMinutes === null) ? $wEndMins : max($foundMaxMinutes, $wEndMins);
                }
            }

            $pAppts = $allAppointments->where('provider_user_id', $p->id);
            foreach ($pAppts as $apt) {
                $aptStartLocal = $apt->start_at_utc->copy()->timezone($scheduleTz);
                $aptEndLocal = $apt->end_at_utc->copy()->timezone($scheduleTz);
                $aS = ($aptStartLocal->hour * 60) + $aptStartLocal->minute;
                $aE = ($aptEndLocal->hour * 60) + $aptEndLocal->minute;

                $foundMinMinutes = ($foundMinMinutes === null) ? $aS : min($foundMinMinutes, $aS);
                $foundMaxMinutes = ($foundMaxMinutes === null) ? $aE : max($foundMaxMinutes, $aE);
            }
        }

        // Fallback if no work windows or closed
        if ($foundMinMinutes === null || $foundMaxMinutes === null) {
            $foundMinMinutes = 8 * 60;  // 08:00
            $foundMaxMinutes = 20 * 60; // 20:00
        }

        $gridStartHour = (int)floor($foundMinMinutes / 60);
        $gridEndHour = (int)ceil($foundMaxMinutes / 60);
        if ($gridEndHour <= $gridStartHour) {
            $gridEndHour = $gridStartHour + 10;
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
                                'start_time_carbon' => $aptStartLocal,
                                'end_time_carbon' => $aptEndLocal,
                                'duration_minutes' => $aptStartLocal->diffInMinutes($aptEndLocal),
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

                        // Calculate free time segments inside this slot
                        $freeSegments = $this->calculateFreeSegments($slotStart, $slotEnd, $formattedSlotAppts, $breaks);
                        $totalFreeMinsInSlot = array_sum(array_column($freeSegments, 'duration_minutes'));

                        $isFull = ($capacityPerSlot > 0 && $bookedCount >= $capacityPerSlot)
                            || ($dailyRemaining !== null && $dailyRemaining <= 0)
                            || ($totalFreeMinsInSlot <= 0 && !empty($formattedSlotAppts));

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
                            'total_free_minutes' => $totalFreeMinsInSlot,
                            'free_segments' => $freeSegments,
                            'is_full' => $isFull,
                            'appointments' => $formattedSlotAppts,
                        ];

                        $cursor->addMinutes($effectiveSlotDuration);
                    }
                }
            } elseif ($providerAppointmentsToday->count() > 0) {
                foreach ($providerAppointmentsToday as $apt) {
                    $aptStartLocal = $apt->start_at_utc->copy()->timezone($scheduleTz);
                    $aptEndLocal = $apt->end_at_utc->copy()->timezone($scheduleTz);

                    $formattedSlotAppts = [[
                        'id' => $apt->id,
                        'client_name' => $apt->client?->full_name ?? 'بیمار جدید',
                        'client_phone' => $apt->client?->phone ?? '',
                        'service_name' => $apt->service?->name ?? 'خدمت عمومی',
                        'status' => $apt->status,
                        'start_time' => $aptStartLocal->format('H:i'),
                        'end_time' => $aptEndLocal->format('H:i'),
                        'start_time_carbon' => $aptStartLocal,
                        'end_time_carbon' => $aptEndLocal,
                        'duration_minutes' => $aptStartLocal->diffInMinutes($aptEndLocal),
                        'notes' => $apt->notes,
                    ]];

                    $providerSlots[] = [
                        'start_time' => $aptStartLocal->format('H:i'),
                        'end_time' => $aptEndLocal->format('H:i'),
                        'in_break' => false,
                        'capacity' => 1,
                        'booked_count' => 1,
                        'remaining_capacity' => 0,
                        'total_free_minutes' => 0,
                        'free_segments' => [],
                        'is_full' => true,
                        'appointments' => $formattedSlotAppts,
                    ];
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
                    's_mins' => $sMins,
                    'e_mins' => $eMins,
                    'notes' => $apt->notes,
                ];
            }

            // Calculate overlap columns so appointments never collide or stack on top of each other
            usort($formattedAppointments, function ($a, $b) {
                return $a['s_mins'] <=> $b['s_mins'];
            });

            $columns = [];
            foreach ($formattedAppointments as &$aptItem) {
                $placed = false;
                foreach ($columns as $cIdx => &$colAppts) {
                    $lastAppt = end($colAppts);
                    if ($lastAppt['e_mins'] <= $aptItem['s_mins']) {
                        $colAppts[] = &$aptItem;
                        $aptItem['col_index'] = $cIdx;
                        $placed = true;
                        break;
                    }
                }
                if (!$placed) {
                    $cIdx = count($columns);
                    $columns[$cIdx] = [&$aptItem];
                    $aptItem['col_index'] = $cIdx;
                }
            }
            unset($aptItem, $colAppts);

            $maxCols = max(1, count($columns));
            foreach ($formattedAppointments as &$aptItem) {
                $aptItem['max_cols'] = $maxCols;
            }
            unset($aptItem);

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
        } // End of Day View else block

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
            'calendarView' => $this->calendarView,
            'weekDays' => $weekDays,
            'weekProviderSchedules' => $weekProviderSchedules,
            'monthData' => $monthData,
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
