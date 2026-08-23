<?php

namespace Modules\Projects\App\Traits;

use Morilog\Jalali\CalendarUtils;

trait HandlesJalaliDate
{

    protected function convertJalaliDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $latin = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $date = str_replace($persian, $latin, trim($date));

        if (preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})/', $date, $matches)) {
            $y = (int)$matches[1];
            $m = (int)$matches[2];
            $d = (int)$matches[3];

            if ($y >= 1300 && $y <= 1500) {
                try {
                    [$gy, $gm, $gd] = CalendarUtils::toGregorian($y, $m, $d);
                    return sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
                } catch (\Throwable) {
                    return null;
                }
            }

            if ($y >= 1700) {
                return sprintf('%04d-%02d-%02d', $y, $m, $d);
            }
        }

        return null;
    }
}
