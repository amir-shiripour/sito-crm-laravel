<?php

namespace App\Services;

use App\Models\User;
use App\Models\HolidayEvent;
use App\Models\GoogleCalendarToken;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Morilog\Jalali\Jalalian;
use Nwidart\Modules\Facades\Module;

class CalendarEventService
{
    /**
     * بررسی دسترسی و فعال بودن یک ماژول در سیستم
     */
    public function isModuleAvailable(string $moduleName, string $entityClass, string $tableName): bool
    {
        if (!class_exists($entityClass)) {
            return false;
        }

        if (!Schema::hasTable($tableName)) {
            return false;
        }

        if (class_exists(Module::class) && Module::has($moduleName) && !Module::isEnabled($moduleName)) {
            return false;
        }

        return true;
    }

    /**
     * بررسی فعال بودن یک منبع رویداد در تنظیمات عمومی سیستم
     */
    public function isSourceEnabled(string $sourceKey): bool
    {
        if (class_exists(\Modules\Settings\Entities\Setting::class) && Schema::hasTable('settings')) {
            $raw = \Modules\Settings\Entities\Setting::where('key', 'widget_calendar_enabled_sources')->value('value');
            if ($raw !== null) {
                $enabled = is_string($raw) ? json_decode($raw, true) : $raw;
                if (is_array($enabled)) {
                    return in_array($sourceKey, $enabled, true);
                }
            }
        }
        return true; // به صورت پیش‌فرض فعال است
    }

    /**
     * دریافت سطح دسترسی/نمایش Google Calendar (همه کاربران یا فقط مدیران)
     */
    public function canUserViewGoogleCalendar(User $user): bool
    {
        $visibility = 'all';
        if (class_exists(\Modules\Settings\Entities\Setting::class) && Schema::hasTable('settings')) {
            $val = \Modules\Settings\Entities\Setting::where('key', 'google_calendar_visibility')->value('value');
            if (!empty($val)) {
                $visibility = $val;
            }
        }

        if ($visibility === 'all') {
            return true;
        }

        // حالت admin_only
        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return true;
        }
        if (method_exists($user, 'can') && ($user->can('settings.manage') || $user->can('admin.view'))) {
            return true;
        }

        return false;
    }

    /**
     * دریافت کلیه رویدادهای یک روز خاص برای کاربر
     */
    public function getEventsForDate(Carbon $date, User $user, ?array $sourceFilter = null): Collection
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay   = $date->copy()->endOfDay();

        return $this->getEventsForRange($startOfDay, $endOfDay, $user, $sourceFilter);
    }

    /**
     * دریافت رویدادهای یک بازه زمانی مشخص برای کاربر
     */
    public function getEventsForRange(Carbon $from, Carbon $to, User $user, ?array $sourceFilter = null): Collection
    {
        $events = collect();

        $shouldInclude = function (string $key) use ($sourceFilter): bool {
            if (!$this->isSourceEnabled($key)) {
                return false;
            }
            if ($sourceFilter === null) {
                return true;
            }
            return in_array($key, $sourceFilter, true);
        };

        // 1. ماژول نوبت‌دهی (Booking)
        if ($shouldInclude('booking')) {
            $events = $events->concat($this->getBookingEvents($from, $to, $user));
        }

        // 2. ماژول وظایف (Tasks)
        if ($shouldInclude('tasks')) {
            $events = $events->concat($this->getTaskEvents($from, $to, $user));
        }

        // 3. ماژول یادآوری‌ها (Reminders)
        if ($shouldInclude('reminders')) {
            $events = $events->concat($this->getReminderEvents($from, $to, $user));
        }

        // 4. مناسبت‌ها و تعطیلات شمسی (Jalali Holidays)
        if ($shouldInclude('jalali_holidays')) {
            $events = $events->concat($this->getJalaliHolidayEvents($from, $to, $user));
        }

        // 5. گوگل کلندر (Google Calendar)
        if ($shouldInclude('google_calendar')) {
            if ($this->canUserViewGoogleCalendar($user)) {
                $events = $events->concat($this->getGoogleCalendarEvents($from, $to, $user));
            }
        }

        // مرتب‌سازی رویدادها بر اساس زمان شروع
        return $events->sortBy('datetime')->values();
    }

    /**
     * لیست منابع فعال رویداد برای فیلتر در UI
     */
    public function getAvailableSources(): array
    {
        $sources = [];

        if ($this->isSourceEnabled('booking') && $this->isModuleAvailable('Booking', \Modules\Booking\Entities\Appointment::class, 'appointments')) {
            $sources[] = [
                'key'   => 'booking',
                'label' => 'نوبت‌ها',
                'color' => 'blue',
            ];
        }

        if ($this->isSourceEnabled('tasks') && $this->isModuleAvailable('Tasks', \Modules\Tasks\Entities\Task::class, 'tasks')) {
            $sources[] = [
                'key'   => 'tasks',
                'label' => 'وظایف',
                'color' => 'amber',
            ];
        }

        if ($this->isSourceEnabled('reminders') && $this->isModuleAvailable('Reminders', \Modules\Reminders\Entities\Reminder::class, 'reminders')) {
            $sources[] = [
                'key'   => 'reminders',
                'label' => 'یادآوری‌ها',
                'color' => 'purple',
            ];
        }

        if ($this->isSourceEnabled('jalali_holidays') && Schema::hasTable('holiday_events')) {
            $sources[] = [
                'key'   => 'jalali_holidays',
                'label' => 'مناسبت‌ها & تعطیلات',
                'color' => 'rose',
            ];
        }

        if ($this->isSourceEnabled('google_calendar')) {
            $hasActiveToken = Schema::hasTable('google_calendar_tokens') && GoogleCalendarToken::where('is_active', true)->exists();
            $hasImported    = Schema::hasTable('google_calendar_imported_events') && \App\Models\GoogleCalendarImportedEvent::exists();

            if ($hasActiveToken || $hasImported) {
                $sources[] = [
                    'key'   => 'google_calendar',
                    'label' => 'گوگل کلندر',
                    'color' => 'teal',
                ];
            }
        }

        return $sources;
    }

    /**
     * دریافت رویدادها از حساب Google Calendar (همزمان آنلاین API + ایمپورت دستی فایل iCal/ICS)
     */
    protected function getGoogleCalendarEvents(Carbon $from, Carbon $to, User $user): Collection
    {
        $events = collect();

        // 1. رویدادهای آنلاین همگام‌شده با API گوگل
        if (Schema::hasTable('google_calendar_tokens')) {
            try {
                $token = GoogleCalendarToken::where('is_active', true)->latest()->first();
                if ($token) {
                    $googleService = app(GoogleCalendarService::class);
                    $rawEvents = $googleService->getEvents($token, $from, $to);

                    $apiEvents = $rawEvents->map(function ($ev) {
                        $startDt    = $ev['start'] ?? null;
                        $endDt      = $ev['end'] ?? null;
                        $jalaliDate = $startDt ? Jalalian::fromCarbon($startDt) : null;

                        $isAllDay = !empty($ev['is_all_day']);
                        if ($startDt && $endDt && $startDt->format('H:i:s') === '00:00:00' && ($endDt->format('H:i:s') === '00:00:00' || $endDt->diffInHours($startDt) >= 24)) {
                            $isAllDay = true;
                        }

                        $startMinute = ($startDt && !$isAllDay) ? ($startDt->hour * 60 + $startDt->minute) : 0;
                        $endMinute   = ($endDt && !$isAllDay) ? ($endDt->hour * 60 + $endDt->minute) : ($startMinute + 60);
                        if ($endMinute <= $startMinute) {
                            $endMinute = $startMinute + 60;
                        }
                        $duration = $isAllDay ? 1440 : max(15, $endMinute - $startMinute);

                        $startTimeStr = ($startDt && !$isAllDay) ? $startDt->format('H:i') : '';
                        $endTimeStr   = ($endDt && !$isAllDay) ? $endDt->format('H:i') : '';
                        $timeStr      = $isAllDay ? 'تمام روز' : ($startTimeStr . ($endTimeStr ? ' - ' . $endTimeStr : ''));

                        return [
                            'id'               => 'gcal_api_' . ($ev['id'] ?? uniqid()),
                            'raw_id'           => $ev['id'] ?? '',
                            'title'            => '📅 ' . ($ev['summary'] ?? 'رویداد گوگل'),
                            'description'      => trim(($ev['description'] ?? '') . "\n" . ($ev['location'] ?? '')),
                            'datetime'         => $startDt ? $startDt->toIso8601String() : null,
                            'time'             => $timeStr,
                            'start_time'       => $startTimeStr,
                            'end_time'         => $endTimeStr,
                            'start_minute'     => $startMinute,
                            'end_minute'       => $endMinute,
                            'duration_minutes' => $duration,
                            'is_all_day'       => $isAllDay,
                            'date_fa'          => $jalaliDate ? $jalaliDate->format('Y/m/d') : '',
                            'date_en'          => $startDt ? $startDt->format('Y-m-d') : '',
                            'day'              => $jalaliDate ? $jalaliDate->getDay() : null,
                            'month'            => $jalaliDate ? $jalaliDate->getMonth() : null,
                            'year'             => $jalaliDate ? $jalaliDate->getYear() : null,
                            'source'           => 'google_calendar',
                            'source_label'     => 'گوگل کلندر (API)',
                            'color'            => 'teal',
                            'is_holiday'       => false,
                            'url'              => $ev['html_link'] ?? '#',
                        ];
                    });
                    $events = $events->concat($apiEvents);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('CalendarEventService Google Calendar API fetch error: ' . $e->getMessage());
            }
        }

        // 2. رویدادهای ایمپورت شده از فایل iCal / ICS
        if (Schema::hasTable('google_calendar_imported_events')) {
            try {
                $importedRecords = \App\Models\GoogleCalendarImportedEvent::whereBetween('start_at', [$from, $to])->get();

                $importedEvents = $importedRecords->map(function ($rec) {
                    $startDt    = $rec->start_at;
                    $endDt      = $rec->end_at;
                    $jalaliDate = $startDt ? Jalalian::fromCarbon($startDt) : null;
                    $isAllDay   = (bool)$rec->is_all_day;

                    $startMinute = ($startDt && !$isAllDay) ? ($startDt->hour * 60 + $startDt->minute) : 0;
                    $endMinute   = ($endDt && !$isAllDay) ? ($endDt->hour * 60 + $endDt->minute) : ($startMinute + 60);
                    if ($endMinute <= $startMinute) {
                        $endMinute = $startMinute + 60;
                    }
                    $duration = $isAllDay ? 1440 : max(15, $endMinute - $startMinute);

                    $startTimeStr = ($startDt && !$isAllDay) ? $startDt->format('H:i') : '';
                    $endTimeStr   = ($endDt && !$isAllDay) ? $endDt->format('H:i') : '';
                    $timeStr      = $isAllDay ? 'تمام روز' : ($startTimeStr . ($endTimeStr ? ' - ' . $endTimeStr : ''));

                    return [
                        'id'               => 'gcal_imp_' . $rec->id,
                        'raw_id'           => $rec->id,
                        'title'            => '📅 ' . ($rec->summary ?? 'رویداد گوگل'),
                        'description'      => trim(($rec->description ?? '') . ($rec->location ? "\nمکان: " . $rec->location : '')),
                        'datetime'         => $startDt ? $startDt->toIso8601String() : null,
                        'time'             => $timeStr,
                        'start_time'       => $startTimeStr,
                        'end_time'         => $endTimeStr,
                        'start_minute'     => $startMinute,
                        'end_minute'       => $endMinute,
                        'duration_minutes' => $duration,
                        'is_all_day'       => $isAllDay,
                        'date_fa'          => $jalaliDate ? $jalaliDate->format('Y/m/d') : '',
                        'date_en'          => $startDt ? $startDt->format('Y-m-d') : '',
                        'day'              => $jalaliDate ? $jalaliDate->getDay() : null,
                        'month'            => $jalaliDate ? $jalaliDate->getMonth() : null,
                        'year'             => $jalaliDate ? $jalaliDate->getYear() : null,
                        'source'           => 'google_calendar',
                        'source_label'     => 'گوگل کلندر (ایمپورت)',
                        'color'            => 'teal',
                        'is_holiday'       => false,
                        'url'              => '#',
                    ];
                });

                $events = $events->concat($importedEvents);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('CalendarEventService Google Calendar imported events fetch error: ' . $e->getMessage());
            }
        }

        return $events;
    }

    /**
     * دریافت مناسبت‌ها و تعطیلات ملی شمسی از دیتابیس محلی
     */
    protected function getJalaliHolidayEvents(Carbon $from, Carbon $to, User $user): Collection
    {
        if (!Schema::hasTable('holiday_events')) {
            return collect();
        }

        try {
            $holidayService = app(JalaliHolidayService::class);

            // اگر دیتابیس خالی است، یک‌بار به‌صورت خودکار همگام‌سازی سال جاری و بعد انجام شود
            if (HolidayEvent::count() === 0) {
                $holidayService->syncCurrentAndNextYears();
            }

            $holidays = $holidayService->getHolidaysForRange($from, $to);

            return $holidays->map(function ($h) {
                $timeStr = $h->is_holiday ? 'تعطیل رسمی' : 'مناسبت';

                return [
                    'id'               => 'holiday_' . $h->id,
                    'raw_id'           => $h->id,
                    'title'            => ($h->is_holiday ? '🔴 ' : '📌 ') . $h->title,
                    'description'      => $h->is_holiday ? "تعطیل رسمی: {$h->title}" : "مناسبت: {$h->title}",
                    'datetime'         => $h->gregorian_date ? $h->gregorian_date->startOfDay()->toIso8601String() : null,
                    'time'             => $timeStr,
                    'start_time'       => '',
                    'end_time'         => '',
                    'start_minute'     => 0,
                    'end_minute'       => 1440,
                    'duration_minutes' => 1440,
                    'is_all_day'       => true,
                    'date_fa'          => str_replace('-', '/', $h->jalali_date),
                    'date_en'          => $h->gregorian_date ? $h->gregorian_date->format('Y-m-d') : '',
                    'day'              => $h->jalali_day,
                    'month'            => $h->jalali_month,
                    'year'             => $h->jalali_year,
                    'source'           => 'jalali_holidays',
                    'source_label'     => $h->is_holiday ? 'تعطیل رسمی' : 'مناسبت',
                    'color'            => $h->is_holiday ? 'rose' : 'emerald',
                    'is_holiday'       => (bool)$h->is_holiday,
                    'url'              => '#',
                ];
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('CalendarEventService holiday fetch error: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * دریافت نوبت‌های رزرو شده
     */
    protected function getBookingEvents(Carbon $from, Carbon $to, User $user): Collection
    {
        if (!$this->isModuleAvailable('Booking', \Modules\Booking\Entities\Appointment::class, 'appointments')) {
            return collect();
        }

        try {
            $query = \Modules\Booking\Entities\Appointment::query()
                ->with(['service', 'client', 'provider']);

            // بررسی دسترسی کاربر به مشاهده همه نوبت‌ها
            $canViewAll = false;
            if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
                $canViewAll = true;
            } elseif (method_exists($user, 'can')) {
                if ($user->can('booking.appointments.view.all') || $user->can('booking.appointments.manage') || $user->can('booking.manage')) {
                    $canViewAll = true;
                }
            }

            if (!$canViewAll) {
                $query->where(function ($q) use ($user) {
                    $q->where('provider_user_id', $user->id)
                      ->orWhere('created_by_user_id', $user->id);
                });
            }

            // فیلتر بر اساس بازه زمانی UTC
            $fromUtc = $from->copy()->setTimezone('UTC');
            $toUtc   = $to->copy()->setTimezone('UTC');

            $appointments = $query->whereBetween('start_at_utc', [$fromUtc, $toUtc])
                ->whereNotIn('status', [\Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_CLIENT, \Modules\Booking\Entities\Appointment::STATUS_CANCELED_BY_ADMIN])
                ->get();

            return $appointments->map(function ($app) {
                $appTimezone = config('app.timezone', 'Asia/Tehran');
                $startLocal  = $app->start_at_utc ? $app->start_at_utc->copy()->setTimezone($appTimezone) : null;
                $endLocal    = $app->end_at_utc ? $app->end_at_utc->copy()->setTimezone($appTimezone) : null;
                if ($startLocal && !$endLocal) {
                    $endLocal = $startLocal->copy()->addHour();
                }

                $jalaliDate  = $startLocal ? Jalalian::fromCarbon($startLocal) : null;

                $clientLabel = config('clients.labels.singular', 'مشتری');

                $client = $app->client;
                $clientName = '';
                if ($client) {
                    $clientName = trim($client->full_name ?? '');
                    if (empty($clientName)) {
                        $clientName = trim($client->name ?? '');
                    }
                    if (empty($clientName)) {
                        $clientName = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));
                    }
                    if (empty($clientName)) {
                        $clientName = trim($client->username ?? '');
                    }
                    if (empty($clientName)) {
                        $clientName = trim($client->phone ?? '');
                    }
                }

                if (empty($clientName)) {
                    $clientName = $clientLabel . ' نامشخص';
                }

                $serviceName  = $app->service->name ?? 'نوبت';
                $serviceColor = $app->service->color ?? null;
                $providerName = $app->provider->name ?? '-';

                $startMinute = $startLocal ? ($startLocal->hour * 60 + $startLocal->minute) : 0;
                $endMinute   = $endLocal ? ($endLocal->hour * 60 + $endLocal->minute) : ($startMinute + 60);
                if ($endMinute <= $startMinute) {
                    $endMinute = $startMinute + 60;
                }
                $duration = max(15, $endMinute - $startMinute);

                $startTimeStr = $startLocal ? $startLocal->format('H:i') : '';
                $endTimeStr   = $endLocal ? $endLocal->format('H:i') : '';
                $timeStr      = $startTimeStr . ($endTimeStr ? ' - ' . $endTimeStr : '');

                return [
                    'id'               => 'booking_' . $app->id,
                    'raw_id'           => $app->id,
                    'title'            => "نوبت: {$serviceName} - {$clientName}",
                    'service_name'     => $serviceName,
                    'service_color'    => $serviceColor,
                    'client_name'      => $clientName,
                    'provider_name'    => $providerName,
                    'description'      => "{$clientLabel}: {$clientName} | ارائه دهنده: {$providerName}",
                    'datetime'         => $startLocal ? $startLocal->toIso8601String() : null,
                    'time'             => $timeStr,
                    'start_time'       => $startTimeStr,
                    'end_time'         => $endTimeStr,
                    'start_minute'     => $startMinute,
                    'end_minute'       => $endMinute,
                    'duration_minutes' => $duration,
                    'is_all_day'       => false,
                    'date_fa'          => $jalaliDate ? $jalaliDate->format('Y/m/d') : '',
                    'date_en'          => $startLocal ? $startLocal->format('Y-m-d') : '',
                    'day'              => $jalaliDate ? $jalaliDate->getDay() : null,
                    'month'            => $jalaliDate ? $jalaliDate->getMonth() : null,
                    'year'             => $jalaliDate ? $jalaliDate->getYear() : null,
                    'source'           => 'booking',
                    'source_label'     => 'نوبت‌دهی',
                    'color'            => $serviceColor ?: 'blue',
                    'status'           => $app->status,
                    'url'              => route('home'),
                ];
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('CalendarEventService booking fetch error: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * دریافت وظایف کاربری
     */
    protected function getTaskEvents(Carbon $from, Carbon $to, User $user): Collection
    {
        if (!$this->isModuleAvailable('Tasks', \Modules\Tasks\Entities\Task::class, 'tasks')) {
            return collect();
        }

        try {
            $query = \Modules\Tasks\Entities\Task::query()
                ->whereNotNull('due_at');

            $canViewAll = false;
            if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
                $canViewAll = true;
            } elseif (method_exists($user, 'can')) {
                if ($user->can('tasks.view.all') || $user->can('tasks.manage')) {
                    $canViewAll = true;
                }
            }

            if (!$canViewAll) {
                $query->where(function ($q) use ($user) {
                    $q->where('assignee_id', $user->id)
                      ->orWhere('creator_id', $user->id);
                });
            }

            $tasks = $query->whereBetween('due_at', [$from, $to])
                ->where('status', '!=', \Modules\Tasks\Entities\Task::STATUS_CANCELED)
                ->get();

            return $tasks->map(function ($task) {
                $due        = $task->due_at;
                $jalaliDate = $due ? Jalalian::fromCarbon($due) : null;

                $isAllDay = false;
                $startMinute = $due ? ($due->hour * 60 + $due->minute) : 0;
                if ($due && $due->format('H:i:s') === '00:00:00') {
                    $isAllDay = true;
                    $duration = 1440;
                    $timeStr = 'تمام روز';
                    $startTimeStr = '';
                    $endTimeStr = '';
                    $endMinute = 1440;
                } else {
                    $duration = 60;
                    $endMinute = min(1440, $startMinute + 60);
                    $startTimeStr = $due ? $due->format('H:i') : '';
                    $endTimeStr = $due ? $due->copy()->addHour()->format('H:i') : '';
                    $timeStr = $startTimeStr;
                }

                return [
                    'id'               => 'task_' . $task->id,
                    'raw_id'           => $task->id,
                    'title'            => "وظیفه: {$task->title}",
                    'description'      => $task->description ?? '',
                    'datetime'         => $due ? $due->toIso8601String() : null,
                    'time'             => $timeStr,
                    'start_time'       => $startTimeStr,
                    'end_time'         => $endTimeStr,
                    'start_minute'     => $startMinute,
                    'end_minute'       => $endMinute,
                    'duration_minutes' => $duration,
                    'is_all_day'       => $isAllDay,
                    'date_fa'          => $jalaliDate ? $jalaliDate->format('Y/m/d') : '',
                    'date_en'          => $due ? $due->format('Y-m-d') : '',
                    'day'              => $jalaliDate ? $jalaliDate->getDay() : null,
                    'month'            => $jalaliDate ? $jalaliDate->getMonth() : null,
                    'year'             => $jalaliDate ? $jalaliDate->getYear() : null,
                    'source'           => 'tasks',
                    'source_label'     => 'وظایف',
                    'color'            => 'amber',
                    'status'           => $task->status,
                    'url'              => route('home'),
                ];
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('CalendarEventService tasks fetch error: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * دریافت یادآوری‌ها
     */
    protected function getReminderEvents(Carbon $from, Carbon $to, User $user): Collection
    {
        if (!$this->isModuleAvailable('Reminders', \Modules\Reminders\Entities\Reminder::class, 'reminders')) {
            return collect();
        }

        try {
            $query = \Modules\Reminders\Entities\Reminder::query()
                ->whereNotNull('remind_at')
                ->where('channel', '!=', \Modules\Reminders\Entities\Reminder::CHANNEL_WORKFLOW)
                ->forTasks();

            $canViewAll = false;
            if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
                $canViewAll = true;
            } elseif (method_exists($user, 'can')) {
                if ($user->can('reminders.view.all') || $user->can('reminders.manage')) {
                    $canViewAll = true;
                }
            }

            if (!$canViewAll) {
                $query->where('user_id', $user->id);
            }

            $reminders = $query->whereBetween('remind_at', [$from, $to])
                ->where('status', '!=', \Modules\Reminders\Entities\Reminder::STATUS_CANCELED)
                ->with(['task', 'user'])
                ->get();

            return $reminders->map(function ($rem) {
                $remindAt   = $rem->remind_at;
                $jalaliDate = $remindAt ? Jalalian::fromCarbon($remindAt) : null;
                $title      = $rem->relatedTitle();

                $startMinute = $remindAt ? ($remindAt->hour * 60 + $remindAt->minute) : 0;
                $duration = 45;
                $endMinute = min(1440, $startMinute + 45);
                $startTimeStr = $remindAt ? $remindAt->format('H:i') : '';
                $endTimeStr = $remindAt ? $remindAt->copy()->addMinutes(45)->format('H:i') : '';

                return [
                    'id'               => 'reminder_' . $rem->id,
                    'raw_id'           => $rem->id,
                    'title'            => "یادآوری: " . \Illuminate\Support\Str::limit($title, 40),
                    'description'      => $rem->message ?: $title,
                    'datetime'         => $remindAt ? $remindAt->toIso8601String() : null,
                    'time'             => $startTimeStr,
                    'start_time'       => $startTimeStr,
                    'end_time'         => $endTimeStr,
                    'start_minute'     => $startMinute,
                    'end_minute'       => $endMinute,
                    'duration_minutes' => $duration,
                    'is_all_day'       => false,
                    'date_fa'          => $jalaliDate ? $jalaliDate->format('Y/m/d') : '',
                    'date_en'          => $remindAt ? $remindAt->format('Y-m-d') : '',
                    'day'              => $jalaliDate ? $jalaliDate->getDay() : null,
                    'month'            => $jalaliDate ? $jalaliDate->getMonth() : null,
                    'year'             => $jalaliDate ? $jalaliDate->getYear() : null,
                    'source'           => 'reminders',
                    'source_label'     => 'یادآوری‌ها',
                    'color'            => 'purple',
                    'status'           => $rem->status,
                    'url'              => $rem->relatedUrl() ?: (\Illuminate\Support\Facades\Route::has('user.reminders.index') ? route('user.reminders.index') : route('home')),
                ];
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('CalendarEventService reminders fetch error: ' . $e->getMessage());
            return collect();
        }
    }
}
