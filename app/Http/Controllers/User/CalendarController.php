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

        return view('widgets.calendar.full-calendar', compact('sources', 'todayJalali'));
    }

    /**
     * API رویدادهای بازه زمانی مشخص (برای AJAX تقویم کامل)
     */
    public function events(Request $request): JsonResponse
    {
        $user = auth()->user();

        $sources = $request->query('sources', []);
        if (is_string($sources)) {
            $sources = array_filter(explode(',', $sources));
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

        // 1. خانه‌های خالی قبل از روز اول ماه
        for ($i = 0; $i < $startDow; $i++) {
            $cells[] = [
                'key'   => 'empty_' . $i,
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
                'key'          => 'day_' . $d,
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
}
