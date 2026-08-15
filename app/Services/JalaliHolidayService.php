<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\HolidayEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Morilog\Jalali\Jalalian;
use Modules\Settings\Entities\Setting;

final class JalaliHolidayService
{
    /**
     * مسیر فایل محلی بانک اطلاعاتی مناسبت‌های time.ir
     */
    private function getLocalDataPath(): string
    {
        return database_path('data/persian_holidays.json');
    }

    /**
     * همگام‌سازی داده‌های مناسبت‌ها برای یک سال خاص و ذخیره در دیتابیس محلی
     */
    public function syncYear(int $jYear): int
    {
        $eventsData = $this->getEventsForYearFromDataset($jYear);

        if (empty($eventsData)) {
            Log::warning("JalaliHolidayService: No events found for year {$jYear}");
            return 0;
        }

        $count = 0;
        $appTz = config('app.timezone', 'Asia/Tehran');

        foreach ($eventsData as $ev) {
            if (empty($ev['title']) || empty($ev['jalali_date'])) {
                continue;
            }

            $jalaliDateStr = (string)$ev['jalali_date'];
            $dateParts     = explode('-', $jalaliDateStr);

            if (count($dateParts) !== 3) {
                continue;
            }

            $y = (int)($ev['year'] ?? $dateParts[0]);
            $m = (int)($ev['month'] ?? $dateParts[1]);
            $d = (int)($ev['day'] ?? $dateParts[2]);

            $title     = trim((string)$ev['title']);
            $isHoliday = (bool)($ev['is_holiday'] ?? false);

            // محاسبه دقیق تاریخ معادل میلادی
            $gregorianDateStr = null;
            try {
                $cDate = (new Jalalian($y, $m, $d))->toCarbon();
                $gregorianDateStr = $cDate->setTimezone($appTz)->format('Y-m-d');
            } catch (\Throwable $e) {
                Log::warning("JalaliHolidayService: Invalid Jalali date {$jalaliDateStr}: " . $e->getMessage());
            }

            HolidayEvent::updateOrCreate(
                [
                    'jalali_date' => $jalaliDateStr,
                    'title'       => $title,
                ],
                [
                    'jalali_year'    => $y,
                    'jalali_month'   => $m,
                    'jalali_day'     => $d,
                    'gregorian_date' => $gregorianDateStr,
                    'is_holiday'     => $isHoliday,
                ]
            );

            $count++;
        }

        return $count;
    }

    /**
     * استخراج مناسبت‌های یک سال از فایل JSON محلی یا منبع رزرو
     */
    protected function getEventsForYearFromDataset(int $jYear): array
    {
        $localPath = $this->getLocalDataPath();

        if (file_exists($localPath)) {
            $json = json_decode(file_get_contents($localPath), true);
            $yearStr = (string)$jYear;
            if (isset($json[$yearStr]) && is_array($json[$yearStr])) {
                return $json[$yearStr];
            }
        }

        // منبع رزرو آنلاین (در صورت عدم وجود سال در دیتابیس محلی)
        try {
            $url = "https://raw.githubusercontent.com/hasan-ahani/shamsi-holidays/main/holidays/{$jYear}.json";
            $response = Http::timeout(10)->get($url);

            if ($response->successful() && is_array($response->json())) {
                $rawDays = $response->json();
                $result  = [];

                foreach ($rawDays as $dayData) {
                    if (empty($dayData['date']) || empty($dayData['events']) || !is_array($dayData['events'])) {
                        continue;
                    }

                    $dateStr = (string)$dayData['date'];
                    [$y, $m, $d] = array_map('intval', explode('-', $dateStr));

                    foreach ($dayData['events'] as $evItem) {
                        if (empty($evItem['description'])) {
                            continue;
                        }
                        $result[] = [
                            'jalali_date' => $dateStr,
                            'year'        => $y,
                            'month'       => $m,
                            'day'         => $d,
                            'title'       => trim((string)$evItem['description']),
                            'is_holiday'  => (bool)($evItem['is_holiday'] ?? false),
                        ];
                    }
                }

                return $result;
            }
        } catch (\Throwable $e) {
            Log::error("JalaliHolidayService fallback error for year {$jYear}: " . $e->getMessage());
        }

        return [];
    }

    /**
     * همگام‌سازی سال جاری و سال بعد (بدون حذف داده‌های سال‌های قبل)
     */
    public function syncCurrentAndNextYears(): array
    {
        $currentYear = Jalalian::now()->getYear();
        $nextYear    = $currentYear + 1;

        $syncedYears = [];
        $totalEvents = 0;

        foreach ([$currentYear, $nextYear] as $yr) {
            $count = $this->syncYear($yr);
            $syncedYears[$yr] = $count;
            $totalEvents += $count;
        }

        // ثبت تاریخ آخرین بروزرسانی در تنظیمات
        $lastSyncFormatted = Jalalian::now()->format('Y/m/d H:i');
        if (class_exists(Setting::class)) {
            Setting::updateOrCreate(
                ['key' => 'widget_calendar_holidays_last_sync'],
                ['value' => $lastSyncFormatted]
            );
        }

        return [
            'success'      => true,
            'total_events' => $totalEvents,
            'years'        => $syncedYears,
            'last_sync'    => $lastSyncFormatted,
        ];
    }

    /**
     * دریافت مناسبت‌ها از پایگاه داده محلی برای بازه زمانی مشخص (بدون درخواست خارجی)
     */
    public function getHolidaysForRange(Carbon $from, Carbon $to): Collection
    {
        $appTz   = config('app.timezone', 'Asia/Tehran');
        $fromStr = $from->copy()->setTimezone($appTz)->format('Y-m-d');
        $toStr   = $to->copy()->setTimezone($appTz)->format('Y-m-d');

        return HolidayEvent::query()
            ->whereBetween('gregorian_date', [$fromStr, $toStr])
            ->orderBy('gregorian_date')
            ->get();
    }

    /**
     * آخرین زمان بروزرسانی داده‌های مناسبت‌ها
     */
    public function getLastSyncTime(): ?string
    {
        if (class_exists(Setting::class)) {
            return Setting::where('key', 'widget_calendar_holidays_last_sync')->value('value');
        }

        return null;
    }

    /**
     * آمار داده‌های ذخیره‌شده محلی
     */
    public function getStoredStats(): array
    {
        $years = HolidayEvent::query()
            ->selectRaw('jalali_year, COUNT(*) as count')
            ->groupBy('jalali_year')
            ->pluck('count', 'jalali_year')
            ->toArray();

        return [
            'total_count' => HolidayEvent::count(),
            'years'       => $years,
            'last_sync'   => $this->getLastSyncTime(),
        ];
    }
}
