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
    public bool $showEmptySlots = true; // Show empty slots in weekly view

    // Quick Creation Modal State
    public bool $showModal = false;
    public int $modalStep = 1; // Step 1: Basic & Client, Step 2: Custom Service Form
    public ?int $modalProviderId = null;
    public ?int $modalServiceId = null;
    public ?int $modalClientId = null;
    public ?int $modalWaitlistId = null;
    public string $modalClientTab = 'search'; // 'search', 'waitlist', 'new'
    public string $modalClientSearch = '';
    public string $modalStartTime = '';
    public string $modalEndTime = '';
    public string $modalStatus = Appointment::STATUS_CONFIRMED;
    public string $modalNotes = '';
    public string $modalError = '';

    // Dynamic Service Form State for Quick Booking Modal
    public ?array $modalFormSchema = null;
    public ?string $modalFormType = null;
    public ?string $modalFormName = null;
    public array $modalFormResponses = [];

    // Quick Details Modal State
    public bool $showDetailsModal = false;
    public ?array $detailsAppointment = null;

    // Alert / Notification State
    public ?string $toastSuccess = null;
    public ?string $toastError = null;

    protected $listeners = [
        'dateSelected' => 'onDateSelected',
        'rescheduleAppointment' => 'rescheduleAppointment',
        'client-quick-saved' => 'onClientQuickSaved',
    ];

    public function mount(): void
    {
        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
        $now = now($scheduleTz);

        if (empty($this->selectedDateJalali)) {
            $this->selectedDateJalali = Jalalian::fromDateTime($now)->format('Y/m/d');
        }

        // Restore view preferences from session (persists across refreshes)
        $savedCalendarView = session('schedule_calendar_view');
        if ($savedCalendarView && in_array($savedCalendarView, ['day', 'week', 'month'], true)) {
            $this->calendarView = $savedCalendarView;
        }

        $savedViewMode = session('schedule_view_mode');
        if ($savedViewMode && in_array($savedViewMode, ['grid', 'timeline'], true)) {
            $this->viewMode = $savedViewMode;
        }

        if (session()->has('schedule_show_empty_slots')) {
            $this->showEmptySlots = (bool) session('schedule_show_empty_slots');
        }

        $this->modalStatus = $this->resolveDefaultModalStatus($this->selectedServiceId);

        $this->evaluateStepLock();

        // Support direct opening via URL query parameters
        if (request()->filled('waitlist_id')) {
            $this->selectWaitlistEntry((int) request('waitlist_id'));
            $this->showModal = true;
        } elseif (request()->filled('client_id')) {
            $this->modalClientId = (int) request('client_id');
            if (request()->filled('service_id')) {
                $this->modalServiceId = (int) request('service_id');
            }
            $this->showModal = true;
        }
    }

    public function updatedModalServiceId($value): void
    {
        $this->modalStatus = $this->resolveDefaultModalStatus($value ? (int)$value : null);
        $this->loadModalServiceForm($value ? (int)$value : null);

        if (!empty($this->modalStartTime) && $this->modalProviderId && $value) {
            $bookingEngine = new BookingEngine();
            $localDate = $this->getGregorianCarbon() ?? now();
            $policy = $bookingEngine->resolveDayPolicy((int)$value, $this->modalProviderId, $localDate);
            $dur = max(5, (int)($policy['slot_duration_minutes'] ?? 30));
            [$h, $m] = explode(':', $this->modalStartTime);
            $slotStart = $localDate->copy()->setTime((int)$h, (int)$m);
            $slotEnd = $slotStart->copy()->addMinutes($dur);
            $this->modalEndTime = $slotEnd->format('H:i');

            // [SYNC FIX F] Re-validate sync conflict when service changes inside modal
            $startUtc = $slotStart->copy()->timezone('UTC');
            $endUtc   = $slotEnd->copy()->timezone('UTC');

            $syncService = app(\Modules\Booking\Services\ServiceSyncService::class);
            $statuses = (array) config('booking.capacity_consuming_statuses', [
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_PENDING_PAYMENT,
                Appointment::STATUS_PENDING,
                Appointment::STATUS_DRAFT,
                Appointment::STATUS_DONE,
            ]);

            if ($syncService->isSyncBlocked((int)$value, $this->modalProviderId, $startUtc, $endUtc, $statuses)) {
                $this->modalError = 'سرویس انتخاب‌شده در این ساعت با یک سرویس هماهنگ‌شده تداخل دارد.';
            } else {
                $this->modalError = '';
            }
        }
    }

    protected function loadModalServiceForm(?int $serviceId, ?array $initialResponses = null): void
    {
        if (!$serviceId) {
            $this->modalFormSchema = null;
            $this->modalFormType = null;
            $this->modalFormName = null;
            $this->modalFormResponses = [];
            return;
        }

        $service = BookingService::with('appointmentForm')->find($serviceId);
        if ($service && $service->appointment_form_id && $service->appointmentForm && $service->appointmentForm->status === \Modules\Booking\Entities\BookingForm::STATUS_ACTIVE) {
            $this->modalFormName = $service->appointmentForm->name;
            $this->modalFormType = $service->appointmentForm->form_type;
            $schema = $service->appointmentForm->schema_json ?? [];

            if (isset($schema['fields']) && is_array($schema['fields'])) {
                foreach ($schema['fields'] as &$field) {
                    if (($field['type'] ?? '') === 'select-user-by-role') {
                        $roleName = $field['role'] ?? null;
                        $usersQ = User::query();
                        if ($roleName) {
                            $usersQ->whereHas('roles', fn($r) => $r->where('name', $roleName));
                        }
                        $field['user_options'] = $usersQ->orderBy('name')->get(['id', 'name'])->toArray();
                    }
                }
            }

            $this->modalFormSchema = $schema;

            if ($initialResponses !== null) {
                $this->modalFormResponses = $initialResponses;
            } else {
                $this->modalFormResponses = [];
                foreach ($schema['fields'] ?? [] as $field) {
                    $fName = $field['name'] ?? '';
                    if ($fName) {
                        if (in_array($field['type'] ?? '', ['checkbox', 'tooth_number']) || !empty($field['multiple'])) {
                            $this->modalFormResponses[$fName] = [];
                        } else {
                            $this->modalFormResponses[$fName] = '';
                        }
                    }
                }
            }
        } else {
            $this->modalFormSchema = null;
            $this->modalFormType = null;
            $this->modalFormName = null;
            $this->modalFormResponses = [];
        }
    }

    public function updatedShowEmptySlots($val): void
    {
        $this->showEmptySlots = (bool) $val;
        session(['schedule_show_empty_slots' => $this->showEmptySlots]);
        $this->dispatch('schedule-pref-changed', key: 'showEmptySlots', value: $this->showEmptySlots);
    }

    public function toggleShowEmptySlots(): void
    {
        $this->showEmptySlots = !$this->showEmptySlots;
        session(['schedule_show_empty_slots' => $this->showEmptySlots]);
        $this->dispatch('schedule-pref-changed', key: 'showEmptySlots', value: $this->showEmptySlots);
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
                $providerId = $this->selectedProviderId ?? Auth::id() ?? 1;
                $policy = $bookingEngine->resolveDayPolicy($service->id, $providerId, $localDate);
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
        if (in_array($mode, ['grid', 'timeline'], true)) {
            $this->viewMode = $mode;
            session(['schedule_view_mode' => $mode]);
            $this->dispatch('schedule-pref-changed', key: 'viewMode', value: $mode);
        }
    }

    public function updatedSelectedDateJalali(): void
    {
        $this->toastSuccess = null;
        $this->toastError = null;
    }

    public function setCalendarView(string $view): void
    {
        if (in_array($view, ['day', 'week', 'month'], true)) {
            $this->calendarView = $view;
            session(['schedule_calendar_view' => $view]);
            $this->dispatch('schedule-pref-changed', key: 'calendarView', value: $view);
        }
    }

    public function goToDay(string $dateJalali): void
    {
        $this->selectedDateJalali = $dateJalali;
        $this->calendarView = 'day';
        session(['schedule_calendar_view' => 'day']);
        $this->dispatch('schedule-pref-changed', key: 'calendarView', value: 'day');
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

    public function resetFilters(): void
    {
        $this->selectedProviderId = null;
        $this->selectedServiceId = null;
        $this->statusFilter = '';
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

            $appointmentService = app(AppointmentService::class);
            try {
                $appointmentService->validateSlotAvailableForUpdate(
                    serviceId: $appointment->service_id,
                    providerUserId: $newProviderId,
                    localDate: $localDate->toDateString(),
                    startUtc: $newStartUtc,
                    endUtc: $newEndUtc,
                    excludeAppointmentId: $appointment->id
                );
            } catch (\RuntimeException $re) {
                $msgMap = [
                    'This day is closed.' => 'ارائه‌دهنده انتخابی در این روز برنامه کاری ندارد.',
                    'Time range is outside work windows.' => 'ساعت انتخابی خارج از ساعات کاری ارائه‌دهنده در این روز است.',
                    'Time range overlaps with a break.' => 'ساعت انتخابی با زمان استراحت ارائه‌دهنده تداخل دارد.',
                    'Slot capacity is full.' => 'ظرفیت نوبت‌دهی (با احتساب زمان استراحت قبل/بعد نوبت) در این ساعت تکمیل است.',
                    'Day capacity is full.' => 'ظرفیت کل روزانه ارائه‌دهنده تکمیل است.',
                ];
                $this->toastError = $msgMap[$re->getMessage()] ?? ('خطا در جابجایی: ' . $re->getMessage());
                return;
            }

            $appointment->update([
                'provider_user_id' => $newProviderId,
                'start_at_utc' => $newStartUtc,
                'end_at_utc' => $newEndUtc,
            ]);

            $newProvider = User::find($newProviderId);
            $newProviderName = $newProvider?->name ?? 'ارائه‌دهنده جدید';

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

    public function openCreateModal(?int $providerId = null, string $startTimeStr = '', ?string $jalaliDate = null): void
    {
        if (!empty($jalaliDate)) {
            $this->selectedDateJalali = $jalaliDate;
        }

        $this->modalProviderId = $providerId ?? $this->selectedProviderId;
        $this->modalServiceId = $this->selectedServiceId;

        // Check if selected time slot is outside official work windows or during breaks
        if (!empty($startTimeStr) && $this->modalProviderId) {
            $bookingEngine = new BookingEngine();
            $localDate = $this->getGregorianCarbon() ?? now();
            $policy = $bookingEngine->resolveDayPolicy($this->modalServiceId, $this->modalProviderId, $localDate);

            if ($policy['is_closed']) {
                $this->toastError = 'ثبت نوبت در این تاریخ به دلیل تعطیلی یا غیرفعال بودن امکان‌پذیر نیست.';
                return;
            }

            [$h, $m] = explode(':', $startTimeStr);
            $slotStart = $localDate->copy()->setTime((int)$h, (int)$m);

            // Check if slot time is in the past
            $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
            $nowLocal = now($scheduleTz);
            if ($slotStart->lt($nowLocal)) {
                $this->toastError = 'امکان ثبت نوبت برای زمان‌های گذشته وجود ندارد.';
                return;
            }

            $wWindows = $policy['work_windows'] ?? [];

            $inWorkWindow = false;
            foreach ($wWindows as $w) {
                $wS = Carbon::createFromFormat('Y-m-d H:i', $localDate->format('Y-m-d') . ' ' . $w['start'], $slotStart->getTimezone());
                $wE = Carbon::createFromFormat('Y-m-d H:i', $localDate->format('Y-m-d') . ' ' . $w['end'], $slotStart->getTimezone());
                if ($slotStart->gte($wS) && $slotStart->lt($wE)) {
                    $inWorkWindow = true;
                    break;
                }
            }

            if (!empty($wWindows) && !$inWorkWindow) {
                $this->toastError = 'امکان ثبت نوبت جدید خارج از ساعات کاری رسمی ' . config('booking.labels.provider') . ' وجود ندارد.';
                return;
            }

            if ($this->modalServiceId) {
                $dur = max(5, (int)($policy['slot_duration_minutes'] ?? 30));
                $slotEnd = $slotStart->copy()->addMinutes($dur);
                $startUtc = $slotStart->copy()->timezone('UTC');
                $endUtc = $slotEnd->copy()->timezone('UTC');

                $syncService = app(\Modules\Booking\Services\ServiceSyncService::class);
                $statuses = (array) config('booking.capacity_consuming_statuses', [
                    Appointment::STATUS_CONFIRMED,
                    Appointment::STATUS_PENDING_PAYMENT,
                    Appointment::STATUS_PENDING,
                    Appointment::STATUS_DRAFT,
                    Appointment::STATUS_DONE,
                ]);

                if ($syncService->isSyncBlocked($this->modalServiceId, $this->modalProviderId, $startUtc, $endUtc, $statuses)) {
                    $this->toastError = 'امکان ثبت نوبت در این ساعت به دلیل رزرو بودن سرویس هماهنگ‌شده وجود ندارد.';
                    return;
                }
            }
        }

        $this->modalStep = 1;
        $this->modalClientId = null;
        $this->modalWaitlistId = null;
        $this->modalClientTab = 'search';
        $this->modalClientSearch = '';
        $this->modalStartTime = $startTimeStr;
        $this->modalStatus = $this->resolveDefaultModalStatus($this->modalServiceId);
        $this->loadModalServiceForm($this->modalServiceId);

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

    public function goToModalStep(int $step): void
    {
        $this->modalError = '';
        if ($step === 2) {
            if (!$this->modalServiceId) {
                $this->modalError = 'لطفاً ابتدا نوع سرویس را انتخاب کنید.';
                return;
            }
            if (!$this->modalProviderId) {
                $this->modalError = 'لطفاً ' . config('booking.labels.provider') . ' را انتخاب کنید.';
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
        }
        $this->modalStep = $step;
    }

    public function selectModalClient(int $clientId): void
    {
        $this->modalClientId = $clientId;
        $this->modalWaitlistId = null;
        $this->loadModalServiceForm($this->modalServiceId);
    }

    public function clearModalClient(): void
    {
        $this->modalClientId = null;
        $this->modalWaitlistId = null;
        $this->modalClientTab = 'search';
        $this->loadModalServiceForm($this->modalServiceId);
    }

    public function setModalClientTab(string $tab): void
    {
        $isQueueEnabled = class_exists(\Modules\Booking\Entities\BookingWaitlist::class) && BookingSetting::isQueueEnabled();
        if ($tab === 'waitlist' && !$isQueueEnabled) {
            $tab = 'search';
        }
        if (in_array($tab, ['search', 'waitlist', 'new'], true)) {
            $this->modalClientTab = $tab;
        }
    }

    public function selectWaitlistEntry(int $waitlistId): void
    {
        if (!class_exists(\Modules\Booking\Entities\BookingWaitlist::class)) {
            return;
        }

        $entry = \Modules\Booking\Entities\BookingWaitlist::with(['client', 'service', 'provider'])->find($waitlistId);
        if (!$entry) {
            return;
        }

        $this->modalWaitlistId = $entry->id;
        $this->modalClientId = $entry->client_id;

        if ($entry->service_id) {
            $this->modalServiceId = $entry->service_id;
            $this->updatedModalServiceId($entry->service_id);
        }

        if ($entry->provider_user_id) {
            $this->modalProviderId = $entry->provider_user_id;
        }

        // Load custom form responses from waitlist entry if present
        $targetServiceId = $entry->service_id ? (int)$entry->service_id : ($this->modalServiceId ? (int)$this->modalServiceId : null);
        $this->loadModalServiceForm($targetServiceId, $entry->appointment_form_response_json ?: null);

        if (!empty($entry->notes) && empty($this->modalNotes)) {
            $this->modalNotes = $entry->notes;
        }

        if (!empty($entry->duration_minutes) && !empty($this->modalStartTime)) {
            [$h, $m] = explode(':', $this->modalStartTime);
            $startCb = Carbon::createFromTime((int)$h, (int)$m)->addMinutes((int)$entry->duration_minutes);
            $this->modalEndTime = $startCb->format('H:i');
        }
    }

    public function onClientQuickSaved($clientId = null, $clientName = null): void
    {
        if ($clientId) {
            $this->modalClientId = (int) $clientId;
            $this->modalWaitlistId = null;
            $this->modalClientTab = 'search';
            $this->loadModalServiceForm($this->modalServiceId);
            $clientLabel = config('clients.labels.singular', 'مشتری');
            $this->toastSuccess = ($clientName ? "{$clientLabel} «{$clientName}»" : "{$clientLabel} جدید") . ' با موفقیت ایجاد و انتخاب شد.';
        }
    }

    public function resolveDefaultModalStatus(?int $serviceId = null): string
    {
        $availableStatuses = $this->modalAvailableStatuses;
        $validStatusIds = array_column($availableStatuses, 'id');

        if (empty($validStatusIds)) {
            return Appointment::STATUS_CONFIRMED;
        }

        $targetStatus = Appointment::STATUS_CONFIRMED;

        $targetServiceId = $serviceId ?? $this->modalServiceId ?? $this->selectedServiceId;
        if ($targetServiceId) {
            $service = BookingService::find($targetServiceId);
            if ($service && $service->payment_mode === BookingService::PAYMENT_MODE_REQUIRED) {
                $targetStatus = Appointment::STATUS_PENDING_PAYMENT;
            } else {
                $targetStatus = Appointment::STATUS_CONFIRMED;
            }
        }

        if (in_array($targetStatus, $validStatusIds, true)) {
            return $targetStatus;
        }

        if (in_array(Appointment::STATUS_CONFIRMED, $validStatusIds, true)) {
            return Appointment::STATUS_CONFIRMED;
        }
        if (in_array(Appointment::STATUS_DRAFT, $validStatusIds, true)) {
            return Appointment::STATUS_DRAFT;
        }
        if (in_array(Appointment::STATUS_PENDING, $validStatusIds, true)) {
            return Appointment::STATUS_PENDING;
        }

        return $validStatusIds[0];
    }

    public function getModalAvailableStatusesProperty(): array
    {
        $settings = BookingSetting::current();
        $statuses = $settings->appointment_statuses ?? [];
        if (empty($statuses) || !is_array($statuses)) {
            $statuses = BookingSetting::defaultAppointmentStatuses();
        }

        return array_values(array_filter($statuses, function ($st) {
            return !empty($st['schedule_booking_enabled']);
        }));
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->modalStep = 1;
        $this->modalError = '';
        $this->modalWaitlistId = null;
        $this->modalClientId = null;
        $this->modalClientTab = 'search';
        $this->modalFormSchema = null;
        $this->modalFormType = null;
        $this->modalFormName = null;
        $this->modalFormResponses = [];
    }

    public function saveNewAppointment(AppointmentService $appointmentService): void
    {
        $this->modalError = '';

        if (!$this->modalServiceId) {
            $this->modalError = 'لطفاً سرویس را انتخاب کنید.';
            return;
        }

        if (!$this->modalProviderId) {
            $this->modalError = 'لطفاً ' . config('booking.labels.provider') . ' را انتخاب کنید.';
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

        // Validate custom service dynamic form required fields
        if ($this->modalFormSchema && !empty($this->modalFormSchema['fields'])) {
            foreach ($this->modalFormSchema['fields'] as $field) {
                $fName = $field['name'] ?? '';
                $fLabel = $field['label'] ?? $fName;
                $isRequired = !empty($field['required']);
                if ($isRequired && $fName) {
                    $val = $this->modalFormResponses[$fName] ?? null;
                    $isEmpty = is_null($val) || $val === '' || (is_array($val) && empty($val));
                    if ($isEmpty) {
                        $this->modalError = sprintf('تکمیل فیلد «%s» در فرم اختصاصی الزامی است.', $fLabel);
                        return;
                    }
                }
            }
        }

        try {
            $localDate = $this->getGregorianCarbon();
            if (!$localDate) {
                $this->modalError = 'تاریخ نامعتبر است.';
                return;
            }

            [$sH, $sM] = explode(':', $this->modalStartTime);
            $startLocal = $localDate->copy()->setTime((int)$sH, (int)$sM, 0);

            $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');
            $nowLocal = now($scheduleTz);
            if ($startLocal->lt($nowLocal)) {
                $this->modalError = 'امکان ثبت نوبت برای زمان‌های گذشته وجود ندارد.';
                return;
            }

            if (!empty($this->modalEndTime)) {
                [$eH, $eM] = explode(':', $this->modalEndTime);
                $endLocal = $localDate->copy()->setTime((int)$eH, (int)$eM, 0);
            } else {
                $bookingEngine = new BookingEngine();
                $policy = $bookingEngine->resolveDayPolicy($this->modalServiceId, $this->modalProviderId, $localDate);
                $dur = max(5, (int)($policy['slot_duration_minutes'] ?? 30));
                $endLocal = $startLocal->copy()->addMinutes($dur);
            }

            // Validate against provider work windows
            $bookingEngine = new BookingEngine();
            $policy = $bookingEngine->resolveDayPolicy($this->modalServiceId, $this->modalProviderId, $localDate);

            if ($policy['is_closed']) {
                $this->modalError = 'ثبت نوبت در این تاریخ به دلیل تعطیلی ' . config('booking.labels.provider') . ' امکان‌پذیر نیست.';
                return;
            }

            $wWindows = $policy['work_windows'] ?? [];
            if (!empty($wWindows)) {
                $inWorkWindow = false;
                foreach ($wWindows as $w) {
                    $wS = Carbon::createFromFormat('Y-m-d H:i', $localDate->format('Y-m-d') . ' ' . $w['start'], $startLocal->getTimezone());
                    $wE = Carbon::createFromFormat('Y-m-d H:i', $localDate->format('Y-m-d') . ' ' . $w['end'], $startLocal->getTimezone());
                    if ($startLocal->gte($wS) && $endLocal->lte($wE)) {
                        $inWorkWindow = true;
                        break;
                    }
                }

                if (!$inWorkWindow) {
                    $this->modalError = 'ثبت نوبت در این ساعت به دلیل قرارگیری خارج از ساعات کاری رسمی ' . config('booking.labels.provider') . ' امکان‌پذیر نیست.';
                    return;
                }
            }

            $startUtc = $startLocal->copy()->timezone('UTC');
            $endUtc   = $endLocal->copy()->timezone('UTC');

            $syncService = app(\Modules\Booking\Services\ServiceSyncService::class);
            $statuses = (array) config('booking.capacity_consuming_statuses', [
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_PENDING_PAYMENT,
                Appointment::STATUS_PENDING,
                Appointment::STATUS_DRAFT,
                Appointment::STATUS_DONE,
            ]);

            if ($syncService->isSyncBlocked($this->modalServiceId, $this->modalProviderId, $startUtc, $endUtc, $statuses)) {
                $this->modalError = 'ثبت نوبت در این زمان امکان‌پذیر نیست؛ یک سرویس هماهنگ‌شده قبلاً در این بازه زمانی رزرو شده است.';
                return;
            }

            $status = !empty($this->modalStatus) ? $this->modalStatus : null;

            $formResponse = !empty($this->modalFormResponses) ? $this->modalFormResponses : null;
            if (!$formResponse && $this->modalWaitlistId && class_exists(\Modules\Booking\Entities\BookingWaitlist::class)) {
                $wl = \Modules\Booking\Entities\BookingWaitlist::find($this->modalWaitlistId);
                if ($wl && !empty($wl->appointment_form_response_json)) {
                    $formResponse = $wl->appointment_form_response_json;
                }
            }

            $appointmentService->createAppointmentByOperator(
                serviceId: $this->modalServiceId,
                providerUserId: $this->modalProviderId,
                clientId: $this->modalClientId,
                startAtUtcIso: $startUtc->toIso8601String(),
                endAtUtcIso: $endUtc->toIso8601String(),
                createdByUserId: Auth::id(),
                notes: $this->modalNotes,
                appointmentFormResponse: $formResponse,
                status: $status
            );

            // 🔸 به‌روزرسانی وضعیت در صف انتظار به تبدیل‌شده (Converted)
            if ($this->modalWaitlistId && class_exists(\Modules\Booking\Entities\BookingWaitlist::class)) {
                try {
                    $waitlistEntry = \Modules\Booking\Entities\BookingWaitlist::find($this->modalWaitlistId);
                    if ($waitlistEntry) {
                        $waitlistEntry->update([
                            'status' => \Modules\Booking\Entities\BookingWaitlist::STATUS_CONVERTED,
                            'converted_at' => now(),
                        ]);
                    }
                } catch (\Throwable $we) {
                    Log::error('[ScheduleManager] Failed to mark waitlist as converted: ' . $we->getMessage());
                }
            }

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
                $providerIdForStep = $this->selectedProviderId ?? Auth::id() ?? 1;
                $pServicePolicy = $bookingEngine->resolveDayPolicy($activeService->id, $providerIdForStep, $localDate);
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

        $baseProvidersQuery = User::query();

        if (!empty($roleIds)) {
            $baseProvidersQuery->whereHas('roles', fn($r) => $r->whereIn('id', $roleIds));
        } else {
            $baseProvidersQuery->whereIn('id', function ($q) {
                $q->select('provider_user_id')->from('booking_service_providers')->where('is_active', true);
            });
        }

        if (!$this->isAdminUser($user) && !$user->can('booking.appointments.view.all')) {
            $baseProvidersQuery->where('id', $user->id);
        }

        $filterProviders = (clone $baseProvidersQuery)->orderBy('name')->get();

        $providersQuery = clone $baseProvidersQuery;
        if ($this->selectedServiceId) {
            $providersQuery->whereIn('id', function ($q) {
                $q->select('provider_user_id')
                  ->from('booking_service_providers')
                  ->where('service_id', $this->selectedServiceId)
                  ->where('is_active', true);
            });
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
                ->where('end_at_utc', '>=', $weekStartUtc);

            $allWeekAppointments = $weekAppointmentsQuery->get();

            $nonCanceledWeekAppts = $allWeekAppointments->whereNotIn('status', [
                Appointment::STATUS_CANCELED_BY_ADMIN,
                Appointment::STATUS_CANCELED_BY_CLIENT,
            ]);

            $totalAppointmentsCount = $nonCanceledWeekAppts->count();
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

                    // Filter appointments for display based on selectedServiceId and statusFilter
                    $displayDayAppts = $dayAppts->filter(function ($apt) {
                        if ($this->selectedServiceId && (int)$apt->service_id !== (int)$this->selectedServiceId) {
                            return false;
                        }
                        if ($this->statusFilter) {
                            return $apt->status === $this->statusFilter;
                        }
                        return !in_array($apt->status, [
                            Appointment::STATUS_CANCELED_BY_ADMIN,
                            Appointment::STATUS_CANCELED_BY_CLIENT,
                        ]);
                    });

                    foreach ($displayDayAppts as $apt) {
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

                    $emptySlots = [];
                    $nowLocal = now($scheduleTz);
                    $isDayInPast = $dayCarbon->copy()->endOfDay()->lt($nowLocal);

                    if (!$isClosed && $this->showEmptySlots && !$isDayInPast) {
                        if ($isStepLocked) {
                            $effectiveSlotDur = max(5, (int)($policy['slot_duration_minutes'] ?? 30));
                        } else {
                            $effectiveSlotDur = max(5, (int)($this->timeStepMinutes > 0 ? $this->timeStepMinutes : ($policy['slot_duration_minutes'] ?? 30)));
                        }
                        $capacityPerSlot = (int)($policy['capacity_per_slot'] ?? 1);
                        $bufBefore = (int)($policy['buffer_before_minutes'] ?? 0);
                        $bufAfter = (int)($policy['buffer_after_minutes'] ?? 0);
                        $stepMinutes = max(5, $effectiveSlotDur + $bufAfter);
                        $syncService = app(\Modules\Booking\Services\ServiceSyncService::class);
                        $capacityConsumingStatuses = (array) config('booking.capacity_consuming_statuses', [
                            Appointment::STATUS_CONFIRMED,
                            Appointment::STATUS_PENDING_PAYMENT,
                            Appointment::STATUS_PENDING,
                            Appointment::STATUS_DRAFT,
                            Appointment::STATUS_DONE,
                        ]);

                        foreach ($workWindows as $win) {
                            if (empty($win['start']) || empty($win['end'])) continue;
                            [$wSH, $wSM] = explode(':', $win['start']);
                            [$wEH, $wEM] = explode(':', $win['end']);

                            $wStart = $dayCarbon->copy()->setTime((int)$wSH, (int)$wSM, 0);
                            $wEnd = $dayCarbon->copy()->setTime((int)$wEH, (int)$wEM, 0);

                            $cursor = $wStart->copy();
                            while ($cursor->copy()->addMinutes($effectiveSlotDur)->lte($wEnd)) {
                                $slotStart = $cursor->copy();
                                $slotEnd = $cursor->copy()->addMinutes($effectiveSlotDur);

                                // Check if slot is in the past: if slot start/end has passed
                                $isPast = $slotEnd->lte($nowLocal) || ($slotStart->lt($nowLocal) && $dayCarbon->isToday());
                                if ($isPast) {
                                    $cursor->addMinutes($stepMinutes);
                                    continue;
                                }

                                // Check break
                                $ovBreak = $bookingEngine->getOverlappingBreak($slotStart, $slotEnd, $breaks);
                                if ($ovBreak) {
                                    if ($ovBreak['end']->gt($cursor)) {
                                        $cursor = $ovBreak['end']->copy();
                                    } else {
                                        $cursor->addMinutes($stepMinutes);
                                    }
                                    continue;
                                }

                                $slotStartUtc = $slotStart->copy()->timezone('UTC');
                                $slotEndUtc = $slotEnd->copy()->timezone('UTC');
                                $reqStartWithBuf = $slotStartUtc->copy()->subMinutes($bufBefore);
                                $reqEndWithBuf = $slotEndUtc->copy()->addMinutes($bufAfter);

                                // [SYNC FIX D] Check service sync blocked
                                if ($serviceId) {
                                    $isBlocked = $syncService->isSyncBlocked($serviceId, (int)$provider->id, $slotStartUtc, $slotEndUtc, $capacityConsumingStatuses);
                                } else {
                                    // When no specific service is selected, an empty slot is only valid if no appointment exists in this slot window across all services
                                    $anyApptInSlot = $dayAppts->filter(function ($apt) use ($reqStartWithBuf, $reqEndWithBuf) {
                                        if (in_array($apt->status, [Appointment::STATUS_CANCELED_BY_ADMIN, Appointment::STATUS_CANCELED_BY_CLIENT], true)) {
                                            return false;
                                        }
                                        return $apt->start_at_utc < $reqEndWithBuf && $apt->end_at_utc > $reqStartWithBuf;
                                    })->isNotEmpty();
                                    $isBlocked = $anyApptInSlot;
                                }
                                if ($isBlocked) {
                                    $cursor->addMinutes($stepMinutes);
                                    continue;
                                }

                                // Count booked appointments in this slot
                                $slotOverlappingAppts = $dayAppts->filter(function ($apt) use ($reqStartWithBuf, $reqEndWithBuf) {
                                    if (in_array($apt->status, [Appointment::STATUS_CANCELED_BY_ADMIN, Appointment::STATUS_CANCELED_BY_CLIENT], true)) {
                                        return false;
                                    }
                                    return $apt->start_at_utc < $reqEndWithBuf && $apt->end_at_utc > $reqStartWithBuf;
                                });

                                $bookedCount = $slotOverlappingAppts->count();
                                $remainingCap = max(0, $capacityPerSlot - $bookedCount);

                                if ($remainingCap > 0) {
                                    $emptySlots[] = [
                                        'start_time' => $slotStart->format('H:i'),
                                        'end_time' => $slotEnd->format('H:i'),
                                        'capacity' => $capacityPerSlot,
                                        'booked_count' => $bookedCount,
                                        'remaining_capacity' => $remainingCap,
                                        'is_past' => false,
                                    ];
                                }

                                $cursor->addMinutes($stepMinutes);
                            }
                        }
                    }

                    $dayItems = [];
                    foreach ($formattedDayAppts as $apt) {
                        $dayItems[] = [
                            'type' => 'appointment',
                            'time_sort' => $apt['start_time'],
                            'data' => $apt,
                        ];
                    }
                    foreach ($emptySlots as $eSlot) {
                        $dayItems[] = [
                            'type' => 'empty_slot',
                            'time_sort' => $eSlot['start_time'],
                            'data' => $eSlot,
                        ];
                    }
                    usort($dayItems, fn($a, $b) => strcmp($a['time_sort'], $b['time_sort']));

                    $providerDays[] = [
                        'jalali_date' => $wDay['jalali_date'],
                        'day_name' => $wDay['day_name'],
                        'day_num' => $wDay['day_num'],
                        'is_today' => $wDay['is_today'],
                        'is_past' => $isDayInPast,
                        'is_closed' => $isClosed,
                        'policy' => $policy,
                        'capacity_per_day' => $capacityPerDay,
                        'daily_booked' => $dailyBookedTotal,
                        'daily_remaining' => $dailyRemaining,
                        'appointments' => $formattedDayAppts,
                        'empty_slots' => $emptySlots,
                        'items' => $dayItems,
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
                // [SYNC FIX E] Include sibling service appointments in month view query
                $syncServiceMonth = app(\Modules\Booking\Services\ServiceSyncService::class);
                $siblingServiceIds = $syncServiceMonth->getSiblingServiceIds(
                    (int)$this->selectedServiceId,
                    $this->selectedProviderId ? (int)$this->selectedProviderId : null
                );
                if (!empty($siblingServiceIds)) {
                    $monthAppointmentsQuery->whereIn('service_id', array_merge([(int)$this->selectedServiceId], $siblingServiceIds));
                } else {
                    $monthAppointmentsQuery->where('service_id', $this->selectedServiceId);
                }
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
                ->where('end_at_utc', '>=', $startUtc);

            $allAppointments = $appointmentsQuery->get();

        // Dashboard Metrics (Calculated on all non-canceled appointments)
        $nonCanceledDayAppts = $allAppointments->whereNotIn('status', [
            Appointment::STATUS_CANCELED_BY_ADMIN,
            Appointment::STATUS_CANCELED_BY_CLIENT,
        ]);

        $totalAppointmentsCount = $nonCanceledDayAppts->count();
        $confirmedCount = $allAppointments->where('status', Appointment::STATUS_CONFIRMED)->count();
        $draftCount = $allAppointments->where('status', Appointment::STATUS_DRAFT)->count();
        $pendingCount = $allAppointments->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_PENDING_PAYMENT])->count();
        $doneCount = $allAppointments->where('status', Appointment::STATUS_DONE)->count();

        // Global Timeline bounds (Calculated dynamically based on work schedule hierarchy)
        $foundMinMinutes = null;
        $foundMaxMinutes = null;

        foreach ($providers as $p) {
            $svcId = $this->selectedServiceId;
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
            $bufBefore = (int)($policy['buffer_before_minutes'] ?? 0);
            $bufAfter = (int)($policy['buffer_after_minutes'] ?? 0);
            $stepMinutes = max(5, $effectiveSlotDuration + $bufAfter);
            $isClosed = $policy['is_closed'] || empty($workWindows);

            // Total booked appointments for this provider and service today
            $providerAppointmentsTodayQuery = $allAppointments
                ->where('provider_user_id', $provider->id);
            if ($serviceId) {
                $providerAppointmentsTodayQuery = $providerAppointmentsTodayQuery->where('service_id', $serviceId);
            }
            $dailyBookedTotal = $providerAppointmentsTodayQuery->count();
            $dailyRemaining = ($capacityPerDay !== null && (int)$capacityPerDay > 0)
                ? max(0, (int)$capacityPerDay - $dailyBookedTotal)
                : null;

            // [SYNC PRE-FETCH] Sibling service IDs and their appointments for sync enforcement
            $syncService = app(\Modules\Booking\Services\ServiceSyncService::class);
            $siblingServiceIds = ($serviceId && $provider)
                ? $syncService->getSiblingServiceIds((int)$serviceId, (int)$provider->id)
                : [];

            $siblingAppts = (!empty($siblingServiceIds))
                ? $allAppointments->where('provider_user_id', $provider->id)->whereIn('service_id', $siblingServiceIds)->whereNotIn('status', [
                    Appointment::STATUS_CANCELED_BY_ADMIN,
                    Appointment::STATUS_CANCELED_BY_CLIENT,
                ])
                : collect();

            // Dynamic Slots for Grid View
            $providerSlots = [];
            $placedApptIds = [];

            // Calculate effective work windows merging official work windows and any appointments today
            $rawWindows = $workWindows;
            $providerApptsForWindows = $allAppointments->where('provider_user_id', $provider->id);
            if ($this->selectedServiceId) {
                $providerApptsForWindows = $providerApptsForWindows->where('service_id', $this->selectedServiceId);
            }

            foreach ($providerApptsForWindows as $apt) {
                if (in_array($apt->status, [Appointment::STATUS_CANCELED_BY_ADMIN, Appointment::STATUS_CANCELED_BY_CLIENT])) {
                    continue;
                }
                $aptStartLocal = $apt->start_at_utc->copy()->timezone($scheduleTz);
                $aptEndLocal = $apt->end_at_utc->copy()->timezone($scheduleTz);
                $rawWindows[] = [
                    'start' => $aptStartLocal->format('H:i'),
                    'end' => $aptEndLocal->format('H:i'),
                ];
            }

            usort($rawWindows, fn($a, $b) => strcmp($a['start'], $b['start']));
            $mergedWindows = [];
            foreach ($rawWindows as $w) {
                if (empty($w['start']) || empty($w['end'])) continue;
                if ($w['end'] <= $w['start']) continue;

                if (empty($mergedWindows)) {
                    $mergedWindows[] = $w;
                } else {
                    $lastIdx = count($mergedWindows) - 1;
                    if ($w['start'] <= $mergedWindows[$lastIdx]['end']) {
                        $mergedWindows[$lastIdx]['end'] = max($mergedWindows[$lastIdx]['end'], $w['end']);
                    } else {
                        $mergedWindows[] = $w;
                    }
                }
            }

            $effectiveWorkWindows = !empty($mergedWindows) ? $mergedWindows : $workWindows;
            $hasEffectiveWindows = !empty($effectiveWorkWindows);

            if ($hasEffectiveWindows) {
                $isSlotBased = $serviceId ? !($policy['custom_schedule_enabled'] ?? false) : false;
                if ($isSlotBased) {
                    $engineSlots = $bookingEngine->generateSlots(
                        serviceId: $serviceId,
                        providerUserId: (int)$provider->id,
                        fromLocalDate: $localDate->toDateString(),
                        toLocalDate: $localDate->toDateString(),
                        viewerTimezone: $scheduleTz,
                        includePast: true
                    );

                    foreach ($engineSlots as $eSlot) {
                        $slotStart = Carbon::parse($eSlot['start_at_view'], $scheduleTz);
                        $slotEnd = Carbon::parse($eSlot['end_at_view'], $scheduleTz);
                        $slotStartUtc = Carbon::parse($eSlot['start_at_utc']);
                        $slotEndUtc = Carbon::parse($eSlot['end_at_utc']);

                        $nowLocal = now($scheduleTz);
                        $isPast = $slotEnd->lte($nowLocal) || ($slotStart->lt($nowLocal) && $localDate->isToday());

                        $inWorkWindow = false;
                        if (!$isClosed && !empty($workWindows)) {
                            foreach ($workWindows as $w) {
                                $wStart = Carbon::createFromFormat('Y-m-d H:i', $localDate->format('Y-m-d') . ' ' . $w['start'], $scheduleTz);
                                $wEnd = Carbon::createFromFormat('Y-m-d H:i', $localDate->format('Y-m-d') . ' ' . $w['end'], $scheduleTz);
                                if ($slotStart->gte($wStart) && $slotEnd->lte($wEnd)) {
                                    $inWorkWindow = true;
                                    break;
                                }
                            }
                        }

                        $inBreak = $bookingEngine->isInBreak($slotStart, $slotEnd, $breaks);

                        $reqStartWithBuf = $slotStartUtc->copy()->subMinutes((int)($eSlot['buffer_before_minutes'] ?? 0));
                        $reqEndWithBuf = $slotEndUtc->copy()->addMinutes((int)($eSlot['buffer_after_minutes'] ?? 0));

                        $slotStartingAppts = $allAppointments->where('provider_user_id', $provider->id)
                            ->filter(function ($apt) use ($slotStartUtc, $slotEndUtc) {
                                return $apt->start_at_utc >= $slotStartUtc && $apt->start_at_utc < $slotEndUtc;
                            });

                        $slotOverlappingAppts = $allAppointments->where('provider_user_id', $provider->id)
                            ->filter(function ($apt) use ($reqStartWithBuf, $reqEndWithBuf) {
                                return $apt->start_at_utc < $reqEndWithBuf && $apt->end_at_utc > $reqStartWithBuf;
                            });

                        $busySlotAppts = $slotOverlappingAppts->filter(function ($apt) use ($serviceId) {
                            if ((int)$apt->service_id !== (int)$serviceId) {
                                return false;
                            }
                            return !in_array($apt->status, [
                                Appointment::STATUS_CANCELED_BY_ADMIN,
                                Appointment::STATUS_CANCELED_BY_CLIENT,
                            ]);
                        });

                        $displaySlotAppts = $slotOverlappingAppts->filter(function ($apt) {
                            if ($this->selectedServiceId && (int)$apt->service_id !== (int)$this->selectedServiceId) {
                                return false;
                            }
                            if ($this->statusFilter) {
                                return $apt->status === $this->statusFilter;
                            }
                            return !in_array($apt->status, [
                                Appointment::STATUS_CANCELED_BY_ADMIN,
                                Appointment::STATUS_CANCELED_BY_CLIENT,
                            ]);
                        });

                        $formattedSlotAppts = [];
                        foreach ($busySlotAppts as $apt) {
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

                        $capacityPerSlot = (int)($eSlot['capacity_per_slot'] ?? 1);
                        $bookedCount = count($formattedSlotAppts);

                        if (!$inWorkWindow || $inBreak || $isPast) {
                            $slotRemaining = 0;
                        } else {
                            $slotRemaining = $eSlot['remaining_capacity'];
                            if ($dailyRemaining !== null) {
                                if ($slotRemaining === null) {
                                    $slotRemaining = $dailyRemaining;
                                } else {
                                    $slotRemaining = min($slotRemaining, $dailyRemaining);
                                }
                            }
                        }

                        $freeSegments = ($inWorkWindow && !$inBreak && !$isPast)
                            ? $this->calculateFreeSegments($slotStart, $slotEnd, $formattedSlotAppts, $breaks)
                            : [];
                        $totalFreeMinsInSlot = array_sum(array_column($freeSegments, 'duration_minutes'));

                        if (!$inWorkWindow || $inBreak || $isPast) {
                            $isFull = true;
                        } elseif ($capacityPerSlot > 1) {
                            $isFull = ($bookedCount >= $capacityPerSlot)
                                || ($eSlot['capacity_per_day_remaining'] !== null && $eSlot['capacity_per_day_remaining'] <= 0);
                        } else {
                            $isFull = ($capacityPerSlot > 0 && $bookedCount >= $capacityPerSlot)
                                || ($eSlot['capacity_per_day_remaining'] !== null && $eSlot['capacity_per_day_remaining'] <= 0)
                                || ($totalFreeMinsInSlot <= 0 && !empty($formattedSlotAppts));
                        }

                        // [SYNC FIX B] BookingEngine already set sync_blocked; enforce it in $isFull
                        if (!empty($eSlot['sync_blocked'])) {
                            $isFull = true;
                        }

                        if ($inWorkWindow && !$inBreak && !$isPast && $slotRemaining !== null) {
                            $totalRemainingCapacitySum += $slotRemaining;
                        }

                        $slotItems = [];
                        if ($capacityPerSlot <= 1) {
                            if ($busySlotAppts->isEmpty() && $displaySlotAppts->isEmpty()) {
                                if ($inWorkWindow && !$inBreak && !$isPast) {
                                    if (!empty($eSlot['sync_blocked']) || ($eSlot['remaining_capacity'] !== null && $eSlot['remaining_capacity'] <= 0)) {
                                        $slotItems[] = [
                                            'type' => 'closed_slot',
                                            'start_time' => $slotStart->format('H:i'),
                                            'end_time' => $slotEnd->format('H:i'),
                                            'label' => !empty($eSlot['sync_blocked']) ? 'تکمیل ظرفیت (هماهنگ‌شده)' : 'تکمیل ظرفیت',
                                        ];
                                    } else {
                                        $slotItems[] = [
                                            'type' => 'empty_slot',
                                            'start_time' => $slotStart->format('H:i'),
                                            'end_time' => $slotEnd->format('H:i'),
                                        ];
                                    }
                                } else {
                                    $label = $isPast ? 'زمان گذشته' : ($inBreak ? 'زمان استراحت' : 'خارج از ساعات کاری');
                                    $slotItems[] = [
                                        'type' => 'closed_slot',
                                        'start_time' => $slotStart->format('H:i'),
                                        'end_time' => $slotEnd->format('H:i'),
                                        'label' => $label,
                                    ];
                                }
                            } else {
                                foreach ($displaySlotAppts as $apt) {
                                    if (in_array($apt->id, $placedApptIds)) {
                                        continue;
                                    }
                                    $placedApptIds[] = $apt->id;

                                    $aptStartLocal = $apt->start_at_utc->copy()->timezone($scheduleTz);
                                    $aptEndLocal = $apt->end_at_utc->copy()->timezone($scheduleTz);
                                    $slotItems[] = [
                                        'type' => 'appointment',
                                        'start_time' => $aptStartLocal->format('H:i'),
                                        'end_time' => $aptEndLocal->format('H:i'),
                                        'data' => [
                                            'id' => $apt->id,
                                            'client_name' => $apt->client?->full_name ?? 'بیمار جدید',
                                            'client_phone' => $apt->client?->phone ?? '',
                                            'service_name' => $apt->service?->name ?? 'خدمت عمومی',
                                            'status' => $apt->status,
                                            'start_time' => $aptStartLocal->format('H:i'),
                                            'end_time' => $aptEndLocal->format('H:i'),
                                            'notes' => $apt->notes,
                                        ],
                                    ];
                                }

                                if ($inWorkWindow && !$inBreak && !$isPast) {
                                    foreach ($freeSegments as $seg) {
                                        $slotItems[] = [
                                            'type' => 'free_segment',
                                            'start_time' => $seg['start_time'],
                                            'end_time' => $seg['end_time'],
                                            'data' => $seg,
                                        ];
                                    }
                                }
                            }
                        }

                        usort($slotItems, function ($a, $b) {
                            return strcmp($a['start_time'], $b['start_time']);
                        });

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
                            'items' => $slotItems,
                        ];
                    }
                } else {
                    $addedBreakKeys = [];
                    foreach ($effectiveWorkWindows as $win) {
                        $winStart = Carbon::createFromFormat('Y-m-d H:i', $localDate->format('Y-m-d') . ' ' . $win['start'], $scheduleTz);
                        $winEnd = Carbon::createFromFormat('Y-m-d H:i', $localDate->format('Y-m-d') . ' ' . $win['end'], $scheduleTz);

                        if ($winEnd->lte($winStart)) continue;

                        $cursor = $winStart->copy();
                        while ($cursor->copy()->addMinutes($effectiveSlotDuration)->lte($winEnd)) {
                            $slotStart = $cursor->copy();
                            $slotEnd = $cursor->copy()->addMinutes($effectiveSlotDuration);

                            // Smart Break Check: if candidate slot collides with a break
                            $ovBreak = $bookingEngine->getOverlappingBreak($slotStart, $slotEnd, $breaks);
                            if ($ovBreak) {
                                $bStartLocal = $ovBreak['start']->format('H:i');
                                $bEndLocal = $ovBreak['end']->format('H:i');
                                $breakKey = "{$bStartLocal}_{$bEndLocal}";

                                if (!in_array($breakKey, $addedBreakKeys)) {
                                    $addedBreakKeys[] = $breakKey;
                                    $providerSlots[] = [
                                        'start_time' => $bStartLocal,
                                        'end_time' => $bEndLocal,
                                        'in_break' => true,
                                        'capacity' => $capacityPerSlot,
                                        'booked_count' => 0,
                                        'remaining_capacity' => 0,
                                        'total_free_minutes' => 0,
                                        'free_segments' => [],
                                        'is_full' => true,
                                        'appointments' => [],
                                        'items' => [
                                            [
                                                'type' => 'closed_slot',
                                                'start_time' => $bStartLocal,
                                                'end_time' => $bEndLocal,
                                                'label' => 'زمان استراحت',
                                            ]
                                        ],
                                    ];
                                }

                                if ($ovBreak['end']->gt($cursor)) {
                                    $cursor = $ovBreak['end']->copy();
                                } else {
                                    $cursor->addMinutes($stepMinutes);
                                }
                                continue;
                            }

                            $inWorkWindow = false;
                            if (!$isClosed && !empty($workWindows)) {
                                foreach ($workWindows as $w) {
                                    $wStart = Carbon::createFromFormat('Y-m-d H:i', $localDate->format('Y-m-d') . ' ' . $w['start'], $scheduleTz);
                                    $wEnd = Carbon::createFromFormat('Y-m-d H:i', $localDate->format('Y-m-d') . ' ' . $w['end'], $scheduleTz);
                                    if ($slotStart->gte($wStart) && $slotEnd->lte($wEnd)) {
                                        $inWorkWindow = true;
                                        break;
                                    }
                                }
                            }

                            $inBreak = false;

                            $slotStartUtc = $slotStart->copy()->timezone('UTC');
                            $slotEndUtc = $slotEnd->copy()->timezone('UTC');
                            $reqStartWithBuf = $slotStartUtc->copy()->subMinutes($bufBefore);
                            $reqEndWithBuf = $slotEndUtc->copy()->addMinutes($bufAfter);

                            $slotStartingAppts = $allAppointments->where('provider_user_id', $provider->id)
                                ->filter(function ($apt) use ($slotStartUtc, $slotEndUtc) {
                                    return $apt->start_at_utc >= $slotStartUtc && $apt->start_at_utc < $slotEndUtc;
                                });

                            $slotOverlappingAppts = $allAppointments->where('provider_user_id', $provider->id)
                                ->filter(function ($apt) use ($reqStartWithBuf, $reqEndWithBuf) {
                                    return $apt->start_at_utc < $reqEndWithBuf && $apt->end_at_utc > $reqStartWithBuf;
                                });

                            $busySlotAppts = $slotOverlappingAppts->filter(function ($apt) use ($serviceId) {
                                if ($serviceId && (int)$apt->service_id !== (int)$serviceId) {
                                    return false;
                                }
                                return !in_array($apt->status, [
                                    Appointment::STATUS_CANCELED_BY_ADMIN,
                                    Appointment::STATUS_CANCELED_BY_CLIENT,
                                ]);
                            });

                            // [SYNC FIX A] Check if any sibling sync service has an appointment in this slot
                            $isSyncBlocked = false;
                            if (!empty($siblingServiceIds) && $siblingAppts->isNotEmpty()) {
                                $isSyncBlocked = $siblingAppts->contains(function ($apt) use ($reqStartWithBuf, $reqEndWithBuf) {
                                    return $apt->start_at_utc < $reqEndWithBuf && $apt->end_at_utc > $reqStartWithBuf;
                                });
                            }

                            $displaySlotAppts = $slotOverlappingAppts->filter(function ($apt) {
                                if ($this->selectedServiceId && (int)$apt->service_id !== (int)$this->selectedServiceId) {
                                    return false;
                                }
                                if ($this->statusFilter) {
                                    return $apt->status === $this->statusFilter;
                                }
                                return !in_array($apt->status, [
                                    Appointment::STATUS_CANCELED_BY_ADMIN,
                                    Appointment::STATUS_CANCELED_BY_CLIENT,
                                ]);
                            });

                            $formattedSlotAppts = [];
                            foreach ($busySlotAppts as $apt) {
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
                            $nowLocal = now($scheduleTz);
                            $isPast = $slotEnd->lte($nowLocal) || ($slotStart->lt($nowLocal) && $localDate->isToday());

                            if (!$inWorkWindow || $inBreak || $isPast || $isSyncBlocked) {
                                $slotRemaining = 0;
                            } else {
                                $slotRemaining = $capacityPerSlot > 0 ? max(0, $capacityPerSlot - $bookedCount) : null;
                                if ($dailyRemaining !== null) {
                                    if ($slotRemaining === null) {
                                        $slotRemaining = $dailyRemaining;
                                    } else {
                                        $slotRemaining = min($slotRemaining, $dailyRemaining);
                                    }
                                }
                            }

                            $freeSegments = ($inWorkWindow && !$inBreak && !$isPast)
                                ? $this->calculateFreeSegments($slotStart, $slotEnd, $formattedSlotAppts, $breaks)
                                : [];
                            $totalFreeMinsInSlot = array_sum(array_column($freeSegments, 'duration_minutes'));

                            if (!$inWorkWindow || $inBreak || $isPast || $isSyncBlocked) {
                                $isFull = true;
                            } elseif ($capacityPerSlot > 1) {
                                $isFull = ($bookedCount >= $capacityPerSlot)
                                    || ($dailyRemaining !== null && $dailyRemaining <= 0);
                            } else {
                                $isFull = ($capacityPerSlot > 0 && $bookedCount >= $capacityPerSlot)
                                    || ($dailyRemaining !== null && $dailyRemaining <= 0)
                                    || ($totalFreeMinsInSlot <= 0 && !empty($formattedSlotAppts));
                            }

                            if ($inWorkWindow && !$inBreak && !$isPast && $slotRemaining !== null) {
                                $totalRemainingCapacitySum += $slotRemaining;
                            }

                            $slotItems = [];
                            if ($capacityPerSlot <= 1) {
                                if ($busySlotAppts->isEmpty() && $displaySlotAppts->isEmpty()) {
                                    if ($inWorkWindow && !$inBreak && !$isPast) {
                                        if ($isSyncBlocked) {
                                            $slotItems[] = [
                                                'type' => 'closed_slot',
                                                'start_time' => $slotStart->format('H:i'),
                                                'end_time' => $slotEnd->format('H:i'),
                                                'label' => 'تکمیل ظرفیت (هماهنگ‌شده)',
                                            ];
                                        } else {
                                            $slotItems[] = [
                                                'type' => 'empty_slot',
                                                'start_time' => $slotStart->format('H:i'),
                                                'end_time' => $slotEnd->format('H:i'),
                                            ];
                                        }
                                    } else {
                                        $label = $isPast ? 'زمان گذشته' : ($inBreak ? 'زمان استراحت' : 'خارج از ساعات کاری');
                                        $slotItems[] = [
                                            'type' => 'closed_slot',
                                            'start_time' => $slotStart->format('H:i'),
                                            'end_time' => $slotEnd->format('H:i'),
                                            'label' => $label,
                                        ];
                                    }
                                } else {
                                    foreach ($displaySlotAppts as $apt) {
                                        if (in_array($apt->id, $placedApptIds)) {
                                            continue;
                                        }
                                        $placedApptIds[] = $apt->id;

                                        $aptStartLocal = $apt->start_at_utc->copy()->timezone($scheduleTz);
                                        $aptEndLocal = $apt->end_at_utc->copy()->timezone($scheduleTz);
                                        $slotItems[] = [
                                            'type' => 'appointment',
                                            'start_time' => $aptStartLocal->format('H:i'),
                                            'end_time' => $aptEndLocal->format('H:i'),
                                            'data' => [
                                                'id' => $apt->id,
                                                'client_name' => $apt->client?->full_name ?? 'بیمار جدید',
                                                'client_phone' => $apt->client?->phone ?? '',
                                                'service_name' => $apt->service?->name ?? 'خدمت عمومی',
                                                'status' => $apt->status,
                                                'start_time' => $aptStartLocal->format('H:i'),
                                                'end_time' => $aptEndLocal->format('H:i'),
                                                'notes' => $apt->notes,
                                            ],
                                        ];
                                    }

                                    if ($inWorkWindow && !$inBreak && !$isPast) {
                                        foreach ($freeSegments as $seg) {
                                            $slotItems[] = [
                                                'type' => 'free_segment',
                                                'start_time' => $seg['start_time'],
                                                'end_time' => $seg['end_time'],
                                                'data' => $seg,
                                            ];
                                        }
                                    }
                                }
                            }

                            usort($slotItems, function ($a, $b) {
                                return strcmp($a['start_time'], $b['start_time']);
                            });

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
                                'items' => $slotItems,
                            ];

                            $cursor->addMinutes($stepMinutes);
                        }
                    }
                }
            }

            // Safety catch: Ensure all active appointments for this provider are placed in a slot
            $unplacedAppts = $allAppointments->where('provider_user_id', $provider->id)->filter(function ($apt) use ($placedApptIds) {
                if ($this->selectedServiceId && (int)$apt->service_id !== (int)$this->selectedServiceId) {
                    return false;
                }
                if ($this->statusFilter && $apt->status !== $this->statusFilter) {
                    return false;
                }
                if (in_array($apt->status, [Appointment::STATUS_CANCELED_BY_ADMIN, Appointment::STATUS_CANCELED_BY_CLIENT])) {
                    return false;
                }
                return !in_array($apt->id, $placedApptIds);
            });

            foreach ($unplacedAppts as $apt) {
                $aptStartLocal = $apt->start_at_utc->copy()->timezone($scheduleTz);
                $aptEndLocal = $apt->end_at_utc->copy()->timezone($scheduleTz);
                $placedApptIds[] = $apt->id;

                $formattedUnplaced = [[
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
                    'appointments' => $formattedUnplaced,
                    'items' => [[
                        'type' => 'appointment',
                        'start_time' => $aptStartLocal->format('H:i'),
                        'end_time' => $aptEndLocal->format('H:i'),
                        'data' => [
                            'id' => $apt->id,
                            'client_name' => $apt->client?->full_name ?? 'بیمار جدید',
                            'client_phone' => $apt->client?->phone ?? '',
                            'service_name' => $apt->service?->name ?? 'خدمت عمومی',
                            'status' => $apt->status,
                            'start_time' => $aptStartLocal->format('H:i'),
                            'end_time' => $aptEndLocal->format('H:i'),
                            'notes' => $apt->notes,
                        ],
                    ]],
                ];
            }

            // Always sort provider slots chronologically by start_time
            usort($providerSlots, function ($a, $b) {
                return strcmp($a['start_time'], $b['start_time']);
            });

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

                        // [SYNC FIX C] Skip drop target if slot is blocked by a sibling sync service
                        $slotStartUtcDrop = $slotStart->copy()->timezone('UTC');
                        $slotEndUtcDrop   = $slotEnd->copy()->timezone('UTC');

                        $isDropSyncBlocked = (!empty($siblingServiceIds) && $siblingAppts->isNotEmpty())
                            ? $siblingAppts->contains(function ($apt) use ($slotStartUtcDrop, $slotEndUtcDrop) {
                                return $apt->start_at_utc < $slotEndUtcDrop && $apt->end_at_utc > $slotStartUtcDrop;
                            })
                            : false;

                        if (!$isDropSyncBlocked) {
                            $sMins = ($slotStart->hour * 60) + $slotStart->minute;
                            $leftPercent = (($sMins - $gridStartMinutes) / $totalGridMinutes) * 100;
                            $widthPercent = ($effectiveSlotDuration / $totalGridMinutes) * 100;

                            $slotDropTargets[] = [
                                'start_time' => $slotStart->format('H:i'),
                                'end_time' => $slotEnd->format('H:i'),
                                'left_percent' => $leftPercent,
                                'width_percent' => $widthPercent,
                            ];
                        }

                        $cursor->addMinutes($stepMinutes);
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
        if ($this->showModal) {
            $clientsQuery = Client::query()->visibleForUser(auth()->user());
            if (!empty($this->modalClientSearch)) {
                $q = $this->modalClientSearch;
                $clientsQuery->where(function ($query) use ($q) {
                    $query->where('full_name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('national_code', 'like', "%{$q}%")
                        ->orWhere('case_number', 'like', "%{$q}%");
                });
                $clientsForModal = $clientsQuery->orderByDesc('id')->limit(15)->get();
            } else {
                $clientsForModal = $clientsQuery->orderByDesc('id')->limit(3)->get();
            }
        }

        $selectedModalClient = null;
        if ($this->showModal && $this->modalClientId) {
            $selectedModalClient = Client::find($this->modalClientId);
        }

        // 🔸 بررسی وضعیت فعال بودن صف انتظار و بارگذاری برای مودال ثبت سریع
        $isQueueEnabled = class_exists(\Modules\Booking\Entities\BookingWaitlist::class) && BookingSetting::isQueueEnabled();
        $waitlistForModal = collect();
        $waitlistCount = 0;
        $selectedWaitlistEntry = null;

        if ($this->showModal && $isQueueEnabled) {
            $waitlistQuery = \Modules\Booking\Entities\BookingWaitlist::query()
                ->whereIn('status', [
                    \Modules\Booking\Entities\BookingWaitlist::STATUS_WAITING,
                    \Modules\Booking\Entities\BookingWaitlist::STATUS_NOTIFIED,
                    \Modules\Booking\Entities\BookingWaitlist::STATUS_IN_PROGRESS
                ])
                ->with(['client', 'service', 'provider']);

            $srvId = $this->modalServiceId ? (int)$this->modalServiceId : 0;
            $prvId = $this->modalProviderId ? (int)$this->modalProviderId : 0;

            $waitlistForModal = $waitlistQuery->get()->sort(function ($a, $b) use ($srvId, $prvId) {
                // امتیاز اولویت:
                // ۴۰: تطابق کامل سرویس و ارائه‌دهنده
                // ۳۰: تطابق با سرویس انتخاب‌شده
                // ۲۰: تطابق با ارائه‌دهنده انتخاب‌شده
                // ۱۰: صف عمومی (بدون سرویس و بدون ارائه‌دهنده)
                $scoreA = 0;
                if ($srvId && $a->service_id == $srvId && $prvId && $a->provider_user_id == $prvId) {
                    $scoreA = 40;
                } elseif ($srvId && $a->service_id == $srvId) {
                    $scoreA = 30;
                } elseif ($prvId && $a->provider_user_id == $prvId) {
                    $scoreA = 20;
                } elseif (!$a->service_id && !$a->provider_user_id) {
                    $scoreA = 10;
                }

                $scoreB = 0;
                if ($srvId && $b->service_id == $srvId && $prvId && $b->provider_user_id == $prvId) {
                    $scoreB = 40;
                } elseif ($srvId && $b->service_id == $srvId) {
                    $scoreB = 30;
                } elseif ($prvId && $b->provider_user_id == $prvId) {
                    $scoreB = 20;
                } elseif (!$b->service_id && !$b->provider_user_id) {
                    $scoreB = 10;
                }

                if ($scoreA !== $scoreB) {
                    return $scoreB <=> $scoreA; // امتیاز بالاتر اول
                }

                // در صورت یکسان بودن اولویت تطابق، بر اساس رتبه / شماره صف
                return ($a->queue_rank ?? $a->position ?? 0) <=> ($b->queue_rank ?? $b->position ?? 0);
            })->values();

            $waitlistCount = $waitlistForModal->count();

            if ($this->modalWaitlistId) {
                $selectedWaitlistEntry = \Modules\Booking\Entities\BookingWaitlist::with(['service', 'provider'])->find($this->modalWaitlistId);
            }
        }

        \Illuminate\Support\Facades\Log::info('[ScheduleManager::render]', [
            'calendarView' => $this->calendarView,
            'viewMode' => $this->viewMode,
            'selectedServiceId' => $this->selectedServiceId,
            'selectedProviderId' => $this->selectedProviderId,
            'timeStepMinutes' => $this->timeStepMinutes,
            'isStepLocked' => $isStepLocked,
            'activeService' => $activeService?->name,
            'providerSchedulesCount' => count($providerSchedules),
            'schedules' => array_map(function ($p) {
                return [
                    'provider' => $p['provider']->name,
                    'policy' => [
                        'slot_duration_minutes' => $p['policy']['slot_duration_minutes'] ?? null,
                        'buffer_before_minutes' => $p['policy']['buffer_before_minutes'] ?? null,
                        'buffer_after_minutes' => $p['policy']['buffer_after_minutes'] ?? null,
                    ],
                    'slots' => array_map(function ($s) {
                        return [
                            'start_time' => $s['start_time'],
                            'end_time' => $s['end_time'],
                            'items' => array_map(fn($it) => $it['type'] . ' (' . $it['start_time'] . '-' . $it['end_time'] . ')', $s['items'] ?? []),
                        ];
                    }, $p['slots'] ?? []),
                ];
            }, $providerSchedules ?? []),
        ]);

        return view('booking::livewire.user.schedule-manager', [
            'localDate' => $localDate,
            'dayOfWeekJalali' => $dayOfWeekJalali,
            'calendarView' => $this->calendarView,
            'weekDays' => $weekDays,
            'weekProviderSchedules' => $weekProviderSchedules,
            'monthData' => $monthData,
            'services' => $services,
            'providers' => $providers,
            'filterProviders' => $filterProviders,
            'timelineHeaders' => $timelineHeaders,
            'providerSchedules' => $providerSchedules,
            'clientsForModal' => $clientsForModal,
            'selectedModalClient' => $selectedModalClient,
            'isQueueEnabled' => $isQueueEnabled,
            'waitlistForModal' => $waitlistForModal,
            'waitlistCount' => $waitlistCount,
            'selectedWaitlistEntry' => $selectedWaitlistEntry,
            'clientLabel' => config('clients.labels.singular', 'مشتری'),
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
