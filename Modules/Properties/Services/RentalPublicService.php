<?php

namespace Modules\Properties\Services;

use Modules\Properties\Entities\Property;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;

class RentalPublicService
{
    /**
     * نام ماه‌های شمسی
     */
    const MONTH_NAMES = [
        1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد',
        4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
        7 => 'مهر', 8 => 'آبان', 9 => 'آذر',
        10 => 'دی', 11 => 'بهمن', 12 => 'اسفند'
    ];

    /**
     * تولید داده‌های تقویم و نرخ‌های روزانه برای ماه‌های جاری و آینده
     *
     * @param Property $property
     * @param int $monthsCount
     * @return array
     */
    public function getCalendarData(Property $property, int $monthsCount = 2): array
    {
        $property->loadMissing(['rentalConfig', 'seasonalPrices', 'rentalBlocks']);
        $config = $property->rentalConfig;
        $blocks = $property->rentalBlocks;
        $seasonalPrices = $property->seasonalPrices;

        $nowJalali = Jalalian::now();
        $startYear = $nowJalali->getYear();
        $startMonth = $nowJalali->getMonth();

        // بازه زمانی کلی استعلام
        $startDateJalali = new Jalalian($startYear, $startMonth, 1);
        $startCarbon = $startDateJalali->toCarbon()->subDays(2);
        
        // برآورد انتهای بازه استعلام
        $endCarbon = $startCarbon->copy()->addMonths($monthsCount + 1);

        // نقشه مناسبت‌های ملی و تعطیلات رسمی
        $officialHolidayMap = [];
        if (class_exists(\App\Models\HolidayEvent::class)) {
            $holidayRecords = \App\Models\HolidayEvent::where('is_holiday', true)
                ->whereBetween('gregorian_date', [$startCarbon->format('Y-m-d 00:00:00'), $endCarbon->format('Y-m-d 23:59:59')])
                ->get();
            foreach ($holidayRecords as $hr) {
                $gDate = substr((string) $hr->gregorian_date, 0, 10);
                $officialHolidayMap[$gDate] = $hr->title;
            }
        }

        $months = [];
        $flatDates = [];
        $currY = $startYear;
        $currM = $startMonth;

        for ($mIndex = 0; $mIndex < $monthsCount; $mIndex++) {
            $monthObj = new Jalalian($currY, $currM, 1);
            $daysInMonth = $monthObj->isLeapYear() && $currM == 12 
                ? 30 
                : ($currM <= 6 ? 31 : ($currM < 12 ? 30 : 29));
            $firstDayOfWeek = $monthObj->getDayOfWeek(); // ۰ (شنبه) تا ۶ (جمعه)

            $monthDays = [];

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $jDate = new Jalalian($currY, $currM, $day);
                $cDate = $jDate->toCarbon();
                $dateStr = $cDate->format('Y-m-d');
                $jalaliStr = $jDate->format('Y/m/d');

                // تشخیص هوشمند رده نرخ بر اساس استاندارد جاجیگا و جاباما
                $rateInfo = $config
                    ? $config->resolveNightRateCategory($cDate, $officialHolidayMap)
                    : [
                        'price_type' => 'normal',
                        'holiday_title' => $officialHolidayMap[$dateStr] ?? null,
                        'is_holiday_or_peak' => false,
                        'is_eve_of_holiday' => false,
                        'is_weekend' => ($jDate->getDayOfWeek() == 4 || $jDate->getDayOfWeek() == 5),
                        'day_of_week' => $jDate->getDayOfWeek(),
                    ];

                // بررسی قیمت ویژه تاریخ خاص (بالاترین اولویت)
                $customPrice = $seasonalPrices->first(function ($p) use ($dateStr) {
                    return $dateStr >= $p->start_date->format('Y-m-d') && $dateStr <= $p->end_date->format('Y-m-d');
                });

                $effectivePrice = 0;
                $priceType = $rateInfo['price_type'];

                if ($customPrice) {
                    $effectivePrice = (float) $customPrice->price_per_night;
                    $priceType = 'custom';
                } elseif ($config) {
                    if ($priceType === 'holiday') {
                        $effectivePrice = (float) $config->price_holiday;
                    } elseif ($priceType === 'weekend') {
                        $effectivePrice = (float) $config->price_weekend;
                    } else {
                        $effectivePrice = (float) $config->price_per_night;
                    }
                }

                // بررسی مسدود بودن روز
                $isBlocked = $blocks->contains(function ($b) use ($dateStr) {
                    return $dateStr >= $b->start_date->format('Y-m-d') && $dateStr <= $b->end_date->format('Y-m-d');
                });

                $isPast = $cDate->isPast() && !$cDate->isToday();

                $dayData = [
                    'day' => $day,
                    'jalali_date' => $jalaliStr,
                    'carbon_date' => $dateStr,
                    'day_of_week' => $rateInfo['day_of_week'],
                    'is_weekend' => $rateInfo['is_weekend'],
                    'is_holiday' => $rateInfo['is_holiday_or_peak'],
                    'is_eve_of_holiday' => $rateInfo['is_eve_of_holiday'],
                    'holiday_title' => !empty($rateInfo['holiday_title']) ? trim(preg_replace('/\[.*?\]/', '', $rateInfo['holiday_title'])) : null,
                    'is_blocked' => $isBlocked,
                    'price' => $effectivePrice,
                    'price_type' => $priceType,
                    'custom_title' => $customPrice ? $customPrice->title : null,
                    'is_past' => $isPast,
                ];

                $monthDays[] = $dayData;
                $flatDates[$jalaliStr] = $dayData;
                $flatDates[$dateStr] = $dayData;
            }

            $months[] = [
                'year' => $currY,
                'month' => $currM,
                'month_name' => self::MONTH_NAMES[$currM] ?? '',
                'first_day_of_week' => $firstDayOfWeek,
                'days' => $monthDays,
            ];

            // انتقال به ماه بعد
            if ($currM == 12) {
                $currM = 1;
                $currY++;
            } else {
                $currM++;
            }
        }

        return [
            'months' => $months,
            'flatDates' => $flatDates,
            'officialHolidays' => $officialHolidayMap,
        ];
    }
}
