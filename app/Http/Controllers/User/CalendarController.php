<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\CalendarEventService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;

class CalendarController extends Controller
{
    protected CalendarEventService $eventService;

    public function __construct(CalendarEventService $eventService)
    {
        $this->middleware('auth');
        $this->eventService = $eventService;
    }

    /**
     * صفحه تقویم کامل
     */
    public function index()
    {
        $user = auth()->user();
        $sources = $this->eventService->getAvailableSources();
        $todayJalali = Jalalian::now();
        $allowEventCreation = $this->eventService->isEventCreationAllowed();

        return view('widgets.calendar.full-calendar', compact('sources', 'todayJalali', 'allowEventCreation'));
    }

    /**
     * API رویدادهای بازه زمانی مشخص (برای AJAX تقویم کامل)
     */
    public function events(Request $request): JsonResponse
    {
        $user = auth()->user();

        $sources = null;
        if ($request->has('sources')) {
            $raw = $request->query('sources');
            if (is_array($raw)) {
                $sources = array_values(array_filter($raw, fn($s) => $s !== '__none__'));
            } elseif (is_string($raw)) {
                $trimmed = trim($raw);
                if ($trimmed === '' || $trimmed === '__none__') {
                    $sources = [];
                } else {
                    $sources = array_values(array_filter(explode(',', $trimmed), fn($s) => $s !== '__none__'));
                }
            }
        }

        $viewType = $request->query('view', 'month');

        // محاسبات و رویدادهای نمای هفتگی
        if ($viewType === 'week') {
            $rawDate = $request->query('date');
            $refCarbon = Carbon::now();
            if (!empty($rawDate)) {
                try {
                    $refCarbon = Carbon::parse($rawDate);
                } catch (\Throwable $e) {
                    $refCarbon = Carbon::now();
                }
            } elseif ($request->has('jyear') && $request->has('jmonth')) {
                $jYear = (int)$request->query('jyear');
                $jMonth = (int)$request->query('jmonth');
                $jDay = (int)$request->query('jday', 1);
                try {
                    $refCarbon = (new Jalalian($jYear, $jMonth, max(1, min(31, $jDay))))->toCarbon();
                } catch (\Throwable $e) {
                    $refCarbon = Carbon::now();
                }
            }

            // شروع هفته جلالی (شنبه)
            $diffFromSaturday = ($refCarbon->dayOfWeek + 1) % 7;
            $startOfWeekCarbon = $refCarbon->copy()->subDays($diffFromSaturday)->startOfDay();
            $endOfWeekCarbon   = $startOfWeekCarbon->copy()->addDays(6)->endOfDay();

            $startJalali = Jalalian::fromCarbon($startOfWeekCarbon);
            $endJalali   = Jalalian::fromCarbon($endOfWeekCarbon);

            $weekTitle = ($startJalali->getYear() === $endJalali->getYear())
                ? ($startJalali->getMonth() === $endJalali->getMonth() 
                    ? "{$startJalali->getDay()} تا {$endJalali->getDay()} {$startJalali->format('%B')} {$startJalali->getYear()}"
                    : "{$startJalali->getDay()} {$startJalali->format('%B')} تا {$endJalali->getDay()} {$endJalali->format('%B')} {$startJalali->getYear()}")
                : "{$startJalali->format('%d %B %Y')} تا {$endJalali->format('%d %B %Y')}";

            $gFirstMonth = $startOfWeekCarbon->format('M');
            $gLastMonth  = $endOfWeekCarbon->format('M');
            $gYear       = $endOfWeekCarbon->format('Y');
            $gregorianTitle = ($gFirstMonth === $gLastMonth) 
                ? "{$startOfWeekCarbon->format('d')} - {$endOfWeekCarbon->format('d')} {$gFirstMonth} {$gYear}" 
                : "{$startOfWeekCarbon->format('d M')} - {$endOfWeekCarbon->format('d M')} {$gYear}";

            $events = $this->eventService->getEventsForRange($startOfWeekCarbon, $endOfWeekCarbon, $user, $sources);
            $eventsByDate = $events->groupBy('date_en');

            $dayNames = ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه'];
            $weekDays = [];

            for ($i = 0; $i < 7; $i++) {
                $dayCarbon = $startOfWeekCarbon->copy()->addDays($i);
                $dayJalali = Jalalian::fromCarbon($dayCarbon);
                $dateEnStr = $dayCarbon->format('Y-m-d');
                $dayEvents = $eventsByDate->get($dateEnStr, collect())->values();
                $hasHoliday = $dayEvents->contains(fn($e) => !empty($e['is_holiday']));

                $weekDays[] = [
                    'key'          => 'wday_' . $dateEnStr . '_' . substr(md5(is_array($sources) ? implode(',', $sources) : 'all'), 0, 6),
                    'day_name'     => $dayNames[$i],
                    'day'          => $dayJalali->getDay(),
                    'month'        => $dayJalali->getMonth(),
                    'month_name'   => $dayJalali->format('%B'),
                    'year'         => $dayJalali->getYear(),
                    'date_fa'      => $dayJalali->format('Y/m/d'),
                    'date_en'      => $dateEnStr,
                    'short_en'     => $dayCarbon->format('d M'),
                    'formatted_en' => $dayCarbon->format('d M Y'),
                    'is_today'     => $dayCarbon->isToday(),
                    'is_holiday'   => $hasHoliday,
                    'events'       => $dayEvents->toArray(),
                ];
            }

            return response()->json([
                'success'           => true,
                'view'              => 'week',
                'title'             => $weekTitle,
                'gregorian_range'   => $gregorianTitle,
                'reference_date'    => $refCarbon->format('Y-m-d'),
                'start_date_en'     => $startOfWeekCarbon->format('Y-m-d'),
                'end_date_en'       => $endOfWeekCarbon->format('Y-m-d'),
                'prev_week_date'    => $startOfWeekCarbon->copy()->subWeek()->format('Y-m-d'),
                'next_week_date'    => $startOfWeekCarbon->copy()->addWeek()->format('Y-m-d'),
                'current_week_date' => Carbon::now()->format('Y-m-d'),
                'days'              => $weekDays,
                'events'            => $events->values()->toArray(),
            ]);
        }

        $jYear  = (int) $request->query('jyear', Jalalian::now()->getYear());
        $jMonth = (int) $request->query('jmonth', Jalalian::now()->getMonth());

        // اعتبارسنجی سال و ماه جلالی
        if ($jMonth < 1 || $jMonth > 12 || $jYear < 1300 || $jYear > 1500) {
            $todayJ  = Jalalian::now();
            $jYear  = $todayJ->getYear();
            $jMonth = $todayJ->getMonth();
        }

        // محاسبات روزهای ماه و شروع روز هفته جلالی (0=شنبه ... 6=جمعه)
        $firstDayJalali = new Jalalian($jYear, $jMonth, 1);
        $daysInMonth    = $firstDayJalali->getMonthDays();
        $firstDayCarbon = $firstDayJalali->toCarbon();
        $lastDayCarbon  = (new Jalalian($jYear, $jMonth, $daysInMonth))->toCarbon();
        $startDow       = ($firstDayCarbon->dayOfWeek + 1) % 7;

        $monthTitle = $firstDayJalali->format('%B %Y');

        // عنوان بازه میلادی (مثلاً Jul - Aug 2026)
        $gFirstMonth = $firstDayCarbon->format('M');
        $gLastMonth  = $lastDayCarbon->format('M');
        $gYear       = $lastDayCarbon->format('Y');
        $gregorianTitle = ($gFirstMonth === $gLastMonth) 
            ? "{$gFirstMonth} {$gYear}" 
            : "{$gFirstMonth} - {$gLastMonth} {$gYear}";

        $from = $firstDayCarbon->copy()->startOfDay();
        $to   = $lastDayCarbon->copy()->endOfDay();

        // نقشه معادل کامل تاریخ‌های میلادی برای تمام روزهای ماه
        $daysMap = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $cDate = (new Jalalian($jYear, $jMonth, $d))->toCarbon();
            $daysMap[$d] = [
                'day_fa'       => $d,
                'day_en'       => (int) $cDate->format('d'),
                'month_en'     => $cDate->format('M'),
                'year_en'      => $cDate->format('Y'),
                'date_en'      => $cDate->format('Y-m-d'),
                'short_en'     => $cDate->format('d M'),
                'formatted_en' => $cDate->format('d M Y'),
            ];
        }

        $events = $this->eventService->getEventsForRange($from, $to, $user, $sources);
        $eventsByDay = $events->groupBy('day');

        $todayJalali     = Jalalian::now();
        $isCurrentJYear  = ($todayJalali->getYear() === $jYear);
        $isCurrentJMonth = ($todayJalali->getMonth() === $jMonth);
        $currentJDay     = $todayJalali->getDay();

        $cells = [];
        $srcTag = substr(md5(is_array($sources) ? implode(',', $sources) : 'all'), 0, 6);

        // 1. خانه‌های خالی قبل از روز اول ماه
        for ($i = 0; $i < $startDow; $i++) {
            $cells[] = [
                'key'   => 'empty_' . $jYear . '_' . $jMonth . '_' . $i . '_' . $srcTag,
                'day'   => null,
                'events'=> [],
            ];
        }

        // 2. خانه‌های روزهای ماه
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dayEvents = $eventsByDay->get($d, collect())->values();
            $isToday   = ($isCurrentJYear && $isCurrentJMonth && $d === $currentJDay);
            $hasOfficialHoliday = $dayEvents->contains(fn($e) => !empty($e['is_holiday']));
            $dayInfo   = $daysMap[$d] ?? [];

            $cells[] = [
                'key'          => 'day_' . $jYear . '_' . $jMonth . '_' . $d . '_' . $srcTag,
                'day'          => $d,
                'shortEn'      => $dayInfo['short_en'] ?? '',
                'formattedEn'  => $dayInfo['formatted_en'] ?? '',
                'isToday'      => $isToday,
                'isHoliday'    => $hasOfficialHoliday,
                'events'       => $dayEvents->toArray(),
            ];
        }

        return response()->json([
            'success'         => true,
            'jyear'           => $jYear,
            'jmonth'          => $jMonth,
            'month_title'     => $monthTitle,
            'gregorian_range' => $gregorianTitle,
            'days_in_month'   => $daysInMonth,
            'start_dow'       => $startDow,
            'cells'           => $cells,
            'events'          => $events->values()->toArray(),
        ]);
    }

    /**
     * API داده‌های روز جاری برای ویجت داشبورد
     */
    public function widgetData(Request $request): JsonResponse
    {
        $user = auth()->user();
        $today = Carbon::now();

        $events = $this->eventService->getEventsForDate($today, $user);

        return response()->json([
            'success' => true,
            'events'  => $events,
        ]);
    }

    /**
     * ثبت رویداد جدید در تقویم
     */
    public function storeEvent(Request $request): JsonResponse
    {
        if (!$this->eventService->isEventCreationAllowed()) {
            return response()->json([
                'success' => false,
                'message' => 'امکان ثبت رویداد جدید در تنظیمات سیستم غیرفعال شده است.',
            ], 403);
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'date_fa'     => 'nullable|string',
            'date_en'     => 'nullable|date',
            'start_time'  => 'nullable|string',
            'end_time'    => 'nullable|string',
            'is_all_day'  => 'nullable|boolean',
            'color'       => 'nullable|string|max:30',
            'description' => 'nullable|string|max:2000',
            'location'    => 'nullable|string|max:255',
        ], [
            'title.required' => 'عنوان رویداد الزامی است.',
            'title.max'      => 'عنوان رویداد حداکثر ۲۵۵ کاراکتر است.',
        ]);

        $user = auth()->user();
        [$startDateTime, $endDateTime, $isAllDay] = $this->parseEventDateTimes($request);

        $color = $request->input('color', '#4f46e5');
        if (!preg_match('/^#[a-fA-F0-9]{3,8}$/', $color)) {
            $color = '#4f46e5';
        }

        $event = \App\Models\CalendarEvent::create([
            'user_id'     => $user->id,
            'created_by'  => $user->id,
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'location'    => $validated['location'] ?? null,
            'color'       => $color,
            'start_time'  => $startDateTime,
            'end_time'    => $endDateTime,
            'is_all_day'  => $isAllDay,
            'status'      => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'رویداد با موفقیت در تقویم ثبت شد.',
            'event'   => [
                'id'       => 'custom_ev_' . $event->id,
                'raw_id'   => $event->id,
                'title'    => $event->title,
                'color'    => $event->color,
                'date_fa'  => $event->jalali_date,
                'time'     => $event->formatted_time,
            ],
        ]);
    }

    /**
     * ویرایش رویداد تقویم
     */
    public function updateEvent(Request $request, $id): JsonResponse
    {
        $user = auth()->user();
        $event = \App\Models\CalendarEvent::findOrFail($id);

        $canEdit = ($event->created_by === $user->id || $event->user_id === $user->id || (method_exists($user, 'hasRole') && $user->hasRole('super-admin')));
        if (!$canEdit) {
            return response()->json([
                'success' => false,
                'message' => 'شما دسترسی لازم برای ویرایش این رویداد را ندارید.',
            ], 403);
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'date_fa'     => 'nullable|string',
            'date_en'     => 'nullable|date',
            'start_time'  => 'nullable|string',
            'end_time'    => 'nullable|string',
            'is_all_day'  => 'nullable|boolean',
            'color'       => 'nullable|string|max:30',
            'description' => 'nullable|string|max:2000',
            'location'    => 'nullable|string|max:255',
        ]);

        [$startDateTime, $endDateTime, $isAllDay] = $this->parseEventDateTimes($request);

        $color = $request->input('color', $event->color ?: '#4f46e5');
        if (!preg_match('/^#[a-fA-F0-9]{3,8}$/', $color)) {
            $color = $event->color ?: '#4f46e5';
        }

        $event->update([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'location'    => $validated['location'] ?? null,
            'color'       => $color,
            'start_time'  => $startDateTime,
            'end_time'    => $endDateTime,
            'is_all_day'  => $isAllDay,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'رویداد با موفقیت ویرایش شد.',
            'event'   => [
                'id'       => 'custom_ev_' . $event->id,
                'raw_id'   => $event->id,
                'title'    => $event->title,
                'color'    => $event->color,
                'date_fa'  => $event->jalali_date,
                'time'     => $event->formatted_time,
            ],
        ]);
    }

    /**
     * حذف رویداد تقویم
     */
    public function deleteEvent(Request $request, $id): JsonResponse
    {
        $user = auth()->user();
        $event = \App\Models\CalendarEvent::findOrFail($id);

        $canDelete = ($event->created_by === $user->id || $event->user_id === $user->id || (method_exists($user, 'hasRole') && $user->hasRole('super-admin')));
        if (!$canDelete) {
            return response()->json([
                'success' => false,
                'message' => 'شما دسترسی لازم برای حذف این رویداد را ندارید.',
            ], 403);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'رویداد با موفقیت از تقویم حذف شد.',
        ]);
    }

    /**
     * تبدیل ورودی‌های فرم به Carbon DateTimes
     */
    protected function parseEventDateTimes(Request $request): array
    {
        $isAllDay = $request->boolean('is_all_day');
        $dateCarbon = null;

        if ($request->filled('date_en')) {
            try {
                $dateCarbon = Carbon::parse($request->input('date_en'));
            } catch (\Throwable $e) {}
        }

        if (!$dateCarbon && $request->filled('date_fa')) {
            try {
                $raw = trim($request->input('date_fa'));
                $parts = explode('/', str_replace('-', '/', $raw));
                if (count($parts) === 3) {
                    $jYear = (int)$parts[0];
                    $jMonth = (int)$parts[1];
                    $jDay = (int)$parts[2];
                    $dateCarbon = (new Jalalian($jYear, $jMonth, $jDay))->toCarbon();
                }
            } catch (\Throwable $e) {}
        }

        if (!$dateCarbon) {
            $dateCarbon = Carbon::today();
        }

        if ($isAllDay) {
            $startDateTime = $dateCarbon->copy()->startOfDay();
            $endDateTime   = $dateCarbon->copy()->endOfDay();
        } else {
            $startTime = $request->input('start_time', '09:00');
            $endTime   = $request->input('end_time', '10:00');

            $startParts = explode(':', $startTime);
            $startH = (int)($startParts[0] ?? 9);
            $startM = (int)($startParts[1] ?? 0);
            $startDateTime = $dateCarbon->copy()->setTime($startH, $startM, 0);

            if (!empty($endTime)) {
                $endParts = explode(':', $endTime);
                $endH = (int)($endParts[0] ?? ($startH + 1));
                $endM = (int)($endParts[1] ?? 0);
                $endDateTime = $dateCarbon->copy()->setTime($endH, $endM, 0);
                if ($endDateTime->lessThanOrEqualTo($startDateTime)) {
                    $endDateTime = $startDateTime->copy()->addHour();
                }
            } else {
                $endDateTime = $startDateTime->copy()->addHour();
            }
        }

        return [$startDateTime, $endDateTime, $isAllDay];
    }
}
