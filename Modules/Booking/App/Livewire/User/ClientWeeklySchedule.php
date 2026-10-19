<?php

namespace Modules\Booking\App\Livewire\User;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\Booking\Entities\BookingClientSchedule;
use Modules\Clients\Entities\Client;
use Morilog\Jalali\Jalalian;

class ClientWeeklySchedule extends Component
{
    public int $clientId;
    public ?int $scheduleId = null;

    /**
     * Structure: [0 => ['day_name' => 'شنبه', 'status' => 'custom', 'slots' => [...], 'note' => ''], ...]
     */
    public array $schedule = [];

    public array $preferredDays = [];
    public string $preferredShift = 'any';
    public ?int $maxAppointmentsPerWeek = null;

    public bool $tempAbsenceActive = false;
    public ?string $tempAbsenceStartJalali = null;
    public ?string $tempAbsenceEndJalali = null;
    public ?string $tempAbsenceReason = null;

    public ?string $generalNotes = null;
    public ?string $lastUpdatedText = null;
    public string $activePreset = '';

    public array $slotTypeLabels = [
        'preferred' => ['label' => 'ایده‌آل / ترجیحی', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60'],
        'available' => ['label' => 'امکان‌پذیر / عادی', 'class' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800/60'],
        'conditional' => ['label' => 'مشروط / با هماهنگی', 'class' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/60'],
        'busy' => ['label' => 'مشغول / عدم امکان', 'class' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/60'],
    ];

    public function mount(int $clientId): void
    {
        $this->clientId = $clientId;
        $this->loadSchedule();
    }

    public function loadSchedule(): void
    {
        $record = BookingClientSchedule::with(['creator', 'updater'])->where('client_id', $this->clientId)->first();

        if ($record) {
            $this->scheduleId = $record->id;
            $this->schedule = $record->weekly_schedule ?? BookingClientSchedule::getDefaultWeekSchedule();
            $this->preferredDays = $record->preferred_days ?? [];
            $this->preferredShift = $record->preferred_shift ?? 'any';
            $this->maxAppointmentsPerWeek = $record->max_appointments_per_week;
            $this->tempAbsenceActive = (bool) $record->temp_absence_active;
            $this->tempAbsenceStartJalali = $record->temp_absence_start ? Jalalian::fromCarbon(Carbon::parse($record->temp_absence_start))->format('Y/m/d') : null;
            $this->tempAbsenceEndJalali = $record->temp_absence_end ? Jalalian::fromCarbon(Carbon::parse($record->temp_absence_end))->format('Y/m/d') : null;
            $this->tempAbsenceReason = $record->temp_absence_reason;
            $this->generalNotes = $record->general_notes;

            $updaterName = $record->updater?->name ?? $record->creator?->name ?? 'کاربر سیستم';
            $date = $record->updated_at ? Jalalian::fromDateTime($record->updated_at)->format('Y/m/d ساعت H:i') : null;
            $this->lastUpdatedText = $date ? "آخرین تغییر توسط {$updaterName} در {$date}" : null;
        } else {
            $this->schedule = BookingClientSchedule::getDefaultWeekSchedule();
            $this->preferredShift = 'any';
            $this->preferredDays = [];
            $this->lastUpdatedText = null;
        }
    }

    public function applyPreset(string $presetKey): void
    {
        $this->schedule = BookingClientSchedule::getPresetSchedule($presetKey);
        $this->activePreset = $presetKey;

        if ($presetKey === 'employee' || $presetKey === 'evening_only') {
            $this->preferredShift = 'evening';
        } elseif ($presetKey === 'morning_only') {
            $this->preferredShift = 'morning';
        } elseif ($presetKey === 'even_days') {
            $this->preferredDays = [0, 2, 4];
        } elseif ($presetKey === 'odd_days') {
            $this->preferredDays = [1, 3, 5];
        }

        $this->dispatch('notify', [
            'type' => 'info',
            'text' => 'الگوی آماده روی تقویم هفتگی پیاده شد. برای نهایی‌سازی دکمه «ذخیره تغییرات» را بزنید.',
        ]);
    }

    public function setDayStatus(int $dayIndex, string $status): void
    {
        if (!isset($this->schedule[$dayIndex])) {
            return;
        }

        $this->schedule[$dayIndex]['status'] = $status;

        if ($status === 'custom' && empty($this->schedule[$dayIndex]['slots'])) {
            $this->schedule[$dayIndex]['slots'] = [
                ['start' => '16:00', 'end' => '20:30', 'type' => 'preferred', 'note' => '']
            ];
        }
    }

    public function addSlot(int $dayIndex): void
    {
        if (!isset($this->schedule[$dayIndex])) {
            return;
        }

        $this->schedule[$dayIndex]['status'] = 'custom';
        $slots = $this->schedule[$dayIndex]['slots'] ?? [];
        $slots[] = [
            'start' => '09:00',
            'end' => '13:00',
            'type' => 'available',
            'note' => '',
        ];
        $this->schedule[$dayIndex]['slots'] = array_values($slots);
    }

    public function removeSlot(int $dayIndex, int $slotIndex): void
    {
        if (!isset($this->schedule[$dayIndex]['slots'][$slotIndex])) {
            return;
        }

        unset($this->schedule[$dayIndex]['slots'][$slotIndex]);
        $this->schedule[$dayIndex]['slots'] = array_values($this->schedule[$dayIndex]['slots']);

        if (empty($this->schedule[$dayIndex]['slots'])) {
            $this->schedule[$dayIndex]['status'] = 'all_day_busy';
        }
    }

    public function copyDayTo(int $sourceDayIndex, string $targetGroup): void
    {
        if (!isset($this->schedule[$sourceDayIndex])) {
            return;
        }

        $sourceData = $this->schedule[$sourceDayIndex];

        $targetIndices = [];
        switch ($targetGroup) {
            case 'work_days': // شنبه تا چهارشنبه (0 تا 4)
                $targetIndices = [0, 1, 2, 3, 4];
                break;
            case 'even_days': // شنبه، دوشنبه، چهارشنبه
                $targetIndices = [0, 2, 4];
                break;
            case 'odd_days': // یکشنبه، سه‌شنبه، پنجشنبه
                $targetIndices = [1, 3, 5];
                break;
            case 'all_week': // کل هفته
                $targetIndices = [0, 1, 2, 3, 4, 5, 6];
                break;
        }

        foreach ($targetIndices as $idx) {
            if ($idx === $sourceDayIndex) {
                continue;
            }
            $this->schedule[$idx]['status'] = $sourceData['status'];
            $this->schedule[$idx]['slots'] = $sourceData['slots'];
            $this->schedule[$idx]['note'] = $sourceData['note'];
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'text' => 'برنامه زمانی روز انتخابی با موفقیت به روزهای مشخص شده کپی شد.',
        ]);
    }

    public function resetSchedule(): void
    {
        $this->schedule = BookingClientSchedule::getDefaultWeekSchedule();
        $this->preferredDays = [];
        $this->preferredShift = 'any';
        $this->activePreset = '';

        $this->dispatch('notify', [
            'type' => 'info',
            'text' => 'جدول برنامه هفتگی بازنشانی شد. جهت اعمال نهایی روی دکمه ذخیره کلیک کنید.',
        ]);
    }

    public function save(): void
    {
        $user = Auth::user();
        if (!$user || (!$user->can('clients.edit') && !$user->canAny(['booking.manage', 'booking.appointments.manage', 'booking.appointments.create']))) {
            $this->dispatch('notify', [
                'type' => 'error',
                'text' => 'شما مجوز ویرایش برنامه زمانی این پرونده را ندارید.',
            ]);
            return;
        }

        // Validate time format and ordering for slots
        foreach ($this->schedule as $dayIdx => $day) {
            if (($day['status'] ?? '') === 'custom' && !empty($day['slots'])) {
                foreach ($day['slots'] as $sIdx => $slot) {
                    $startRaw = trim($slot['start'] ?? '');
                    $endRaw = trim($slot['end'] ?? '');

                    if (empty($startRaw) || empty($endRaw)) {
                        $this->dispatch('notify', [
                            'type' => 'error',
                            'text' => 'لطفاً ساعت شروع و پایان را در ' . ($day['day_name'] ?? 'روز انتخابی') . ' وارد نمایید.',
                        ]);
                        return;
                    }

                    // Convert any Persian numerals to English digits
                    $start = \Morilog\Jalali\CalendarUtils::convertNumbers($startRaw);
                    $end = \Morilog\Jalali\CalendarUtils::convertNumbers($endRaw);

                    if (preg_match('/^(\d{1,2}):(\d{1,2})$/', $start, $m1)) {
                        $start = sprintf('%02d:%02d', $m1[1], $m1[2]);
                    }
                    if (preg_match('/^(\d{1,2}):(\d{1,2})$/', $end, $m2)) {
                        $end = sprintf('%02d:%02d', $m2[1], $m2[2]);
                    }

                    if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $start) || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $end)) {
                        $this->dispatch('notify', [
                            'type' => 'error',
                            'text' => 'فرمت ساعت در ' . ($day['day_name'] ?? 'روز انتخابی') . ' نامعتبر است (مثال معتبر: ۱۶:۳۰).',
                        ]);
                        return;
                    }

                    if ($start >= $end) {
                        $this->dispatch('notify', [
                            'type' => 'error',
                            'text' => 'در ' . ($day['day_name'] ?? 'روز انتخابی') . '، ساعت شروع باید قبل از ساعت پایان باشد.',
                        ]);
                        return;
                    }

                    // Save normalized time back to schedule array
                    $this->schedule[$dayIdx]['slots'][$sIdx]['start'] = $start;
                    $this->schedule[$dayIdx]['slots'][$sIdx]['end'] = $end;
                }
            }
        }

        // Convert Jalali Absence dates to Gregorian
        $gregorianStart = null;
        if (!empty($this->tempAbsenceStartJalali)) {
            $cleanStart = \Morilog\Jalali\CalendarUtils::convertNumbers(trim($this->tempAbsenceStartJalali));
            try {
                $gregorianStart = Jalalian::fromFormat('Y/m/d', $cleanStart)->toCarbon()->toDateString();
            } catch (\Throwable $e) {
                try {
                    $gregorianStart = Carbon::parse($cleanStart)->toDateString();
                } catch (\Throwable $e2) {
                    $gregorianStart = null;
                }
            }
        }

        $gregorianEnd = null;
        if (!empty($this->tempAbsenceEndJalali)) {
            $cleanEnd = \Morilog\Jalali\CalendarUtils::convertNumbers(trim($this->tempAbsenceEndJalali));
            try {
                $gregorianEnd = Jalalian::fromFormat('Y/m/d', $cleanEnd)->toCarbon()->toDateString();
            } catch (\Throwable $e) {
                try {
                    $gregorianEnd = Carbon::parse($cleanEnd)->toDateString();
                } catch (\Throwable $e2) {
                    $gregorianEnd = null;
                }
            }
        }

        $record = BookingClientSchedule::firstOrNew(['client_id' => $this->clientId]);

        $record->weekly_schedule = $this->schedule;
        $record->preferred_days = $this->preferredDays;
        $record->preferred_shift = $this->preferredShift ?: 'any';
        $record->max_appointments_per_week = $this->maxAppointmentsPerWeek ?: null;
        $record->temp_absence_active = $this->tempAbsenceActive;
        $record->temp_absence_start = $gregorianStart;
        $record->temp_absence_end = $gregorianEnd;
        $record->temp_absence_reason = $this->tempAbsenceReason ?: null;
        $record->general_notes = $this->generalNotes ?: null;

        if (!$record->exists) {
            $record->created_by_user_id = $user->id;
        }
        $record->updated_by_user_id = $user->id;

        $record->save();

        $this->scheduleId = $record->id;
        $date = Jalalian::fromDateTime($record->updated_at)->format('Y/m/d ساعت H:i');
        $this->lastUpdatedText = "آخرین تغییر توسط {$user->name} در {$date}";

        $this->dispatch('notify', [
            'type' => 'success',
            'text' => 'برنامه زمانی و ترجیحات هفتگی بیمار با موفقیت ذخیره شد.',
        ]);
    }

    public function render()
    {
        $daysMeta = BookingClientSchedule::DAYS;
        $shiftsList = BookingClientSchedule::SHIFTS;
        $client = Client::find($this->clientId);

        return view('booking::livewire.user.client-weekly-schedule', [
            'daysMeta' => $daysMeta,
            'shiftsList' => $shiftsList,
            'client' => $client,
        ]);
    }
}
