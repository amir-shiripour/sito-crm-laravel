<?php

namespace Modules\Properties\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertyRentalConfig extends Model
{
    use HasFactory;

    protected $table = 'property_rental_configs';

    protected $fillable = [
        'property_id',
        'base_guests',
        'max_guests',
        'weekend_days',
        'price_per_night',
        'price_weekend',
        'price_holiday',
        'extra_guest_fee',
        'cleaning_fee',
        'check_in_time',
        'check_out_time',
        'min_stay_nights',
        'house_rules',
        'instant_booking',
    ];

    protected $casts = [
        'base_guests' => 'integer',
        'max_guests' => 'integer',
        'weekend_days' => 'array',
        'min_stay_nights' => 'integer',
        'price_per_night' => 'decimal:0',
        'price_weekend' => 'decimal:0',
        'price_holiday' => 'decimal:0',
        'extra_guest_fee' => 'decimal:0',
        'cleaning_fee' => 'decimal:0',
        'house_rules' => 'array',
        'instant_booking' => 'boolean',
    ];

    /**
     * روزهای آخر هفته منتخب (پیش‌فرض: ۴=چهارشنبه، ۵=پنج‌شنبه)
     */
    public function getWeekendDaysAttribute($value)
    {
        if (empty($value)) {
            return ['4', '5'];
        }

        $decoded = $value;
        while (is_string($decoded)) {
            $parsed = json_decode($decoded, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $decoded = $parsed;
            } else {
                break;
            }
        }

        return !empty($decoded) && is_array($decoded) ? array_map('strval', $decoded) : ['4', '5'];
    }

    /**
     * بررسی اینکه آیا روز هفته مشخص‌شده جزء روزهای آخر هفته این اقامتگاه است یا خیر
     */
    public function isWeekendDay(int $dayOfWeek): bool
    {
        $days = $this->weekend_days;
        return in_array((string) $dayOfWeek, $days, true);
    }

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    /**
     * محاسبه هزینه برای تعداد شب و مهمان در یک تاریخ مشخص
     */
    public function calculateStayPrice(int $nights = 1, int $guests = 1, bool $isWeekend = false, bool $isHoliday = false): float
    {
        $pricePerNight = (float) $this->price_per_night;

        if ($isHoliday && (float)$this->price_holiday > 0) {
            $pricePerNight = (float) $this->price_holiday;
        } elseif ($isWeekend && (float)$this->price_weekend > 0) {
            $pricePerNight = (float) $this->price_weekend;
        }

        $totalBase = $pricePerNight * $nights;

        // محاسبه نفر اضافه
        $extraGuests = max(0, $guests - (int) $this->base_guests);
        $totalExtra = 0;
        if ($extraGuests > 0 && (float)$this->extra_guest_fee > 0) {
            $totalExtra = $extraGuests * (float) $this->extra_guest_fee * $nights;
        }

        $cleaning = (float) $this->cleaning_fee;

        return $totalBase + $totalExtra + $cleaning;
    }

    /**
     * تشخیص هوشمند رده قیمت و مناسبت برای شب اقامت یک تاریخ معین (منطبق با استانداردهای جاجیگا، جاباما و شب)
     *
     * @param \Carbon\CarbonInterface $cDate تاریخ شب اقامت
     * @param array<string, string> $officialHolidays نقشه تاریخ‌های تعطیل رسمی ملی تقویم ['YYYY-MM-DD' => 'عنوان مناسبت']
     * @return array{
     *     price_type: string,
     *     holiday_title: ?string,
     *     is_holiday_or_peak: bool,
     *     is_eve_of_holiday: bool,
     *     is_weekend: bool,
     *     day_of_week: int
     * }
     */
    public function resolveNightRateCategory(\Carbon\CarbonInterface $cDate, array $officialHolidays = []): array
    {
        $dateStr = $cDate->format('Y-m-d');
        $nextDateStr = $cDate->copy()->addDay()->format('Y-m-d');

        $jDate = class_exists(\Morilog\Jalali\Jalalian::class)
            ? \Morilog\Jalali\Jalalian::fromCarbon($cDate)
            : null;
        $dayOfWeek = $jDate ? $jDate->getDayOfWeek() : (($cDate->dayOfWeek + 1) % 7);
        $nextDayOfWeek = ($dayOfWeek + 1) % 7;

        // بررسی اینکه آیا امروز یا فردا در تقویم ملی مناسبت تعطیل رسمی ثبت شده دارند
        $isTodayOfficialHoliday = isset($officialHolidays[$dateStr]);
        $isTomorrowOfficialHoliday = isset($officialHolidays[$nextDateStr]);

        // ۱. شبِ قبل از تعطیل رسمی (Eve of Official Holiday)
        // این شرط شامل جمعه‌شب‌هایی که شنبه پس از آن تعطیل رسمی است نیز می‌شود
        $isEveOfHoliday = $isTomorrowOfficialHoliday;

        // ۲. شب‌های تعطیلات پیوسته (Continuous Holiday Period)
        // اگر امروز تعطیل رسمی است و فردا هم تعطیل رسمی یا جمعه (تعطیل هفتگی) است:
        // مسافران همچنان در سفر هستند و فردا تعطیلند، بنابراین شب امروز نیز نرخ پیک دارد.
        // اما اگر امروز تعطیل رسمی باشد و فردا روز کاری عادی باشد (و آخر هفته هم نباشد):
        // مسافران عصر امروز ویلا را تخلیه کرده و بازمی‌گردند؛ پس شب منتهی به روز کاری نرخ پیک نخواهد داشت.
        $isContinuousHoliday = $isTodayOfficialHoliday && ($isTomorrowOfficialHoliday || $nextDayOfWeek === 6);

        // جمع‌بندی: آیا شب اقامت مشمول نرخ ایام پیک و تعطیلات است؟
        $isHolidayOrPeak = $isEveOfHoliday || $isContinuousHoliday;

        // بررسی آخر هفته بر اساس روزهای انتخابی میزبان (پیش‌فرض: ۴=چهارشنبه، ۵=پنج‌شنبه)
        $isWeekend = $this->isWeekendDay($dayOfWeek);

        // تعیین نوع قیمت و عنوان مناسبت
        $priceType = 'normal';
        $holidayTitle = null;

        if ($isHolidayOrPeak && (float)$this->price_holiday > 0) {
            $priceType = 'holiday';
            if ($isEveOfHoliday) {
                $tomorrowTitle = $officialHolidays[$nextDateStr] ?? 'تعطیل رسمی';
                $holidayTitle = 'شب ' . $tomorrowTitle;
            } elseif ($isTodayOfficialHoliday) {
                $holidayTitle = $officialHolidays[$dateStr] ?? 'ایام پیک';
            } else {
                $holidayTitle = 'ایام پیک';
            }
        } elseif ($isWeekend && (float)$this->price_weekend > 0) {
            $priceType = 'weekend';
            if ($isTodayOfficialHoliday) {
                $holidayTitle = $officialHolidays[$dateStr];
            }
        } else {
            $priceType = 'normal';
            if ($isTodayOfficialHoliday) {
                $holidayTitle = $officialHolidays[$dateStr];
            }
        }

        return [
            'price_type' => $priceType,
            'holiday_title' => $holidayTitle,
            'is_holiday_or_peak' => $isHolidayOrPeak,
            'is_eve_of_holiday' => $isEveOfHoliday,
            'is_weekend' => $isWeekend,
            'day_of_week' => $dayOfWeek,
        ];
    }

    /**
     * دریافت لیست عناوین کامل مقررات و قوانین اقامتگاه (شامل پیش‌فرض‌ها و موارد سفارشی)
     */
    public function getFormattedHouseRulesAttribute(): array
    {
        $defaultMap = [
            'no_smoking' => 'استعمال دخانیات ممنوع',
            'no_pets' => 'ورود حیوانات خانگی ممنوع',
            'no_party' => 'برگزاری جشن و مهمانی ممنوع',
            'national_card_required' => 'ارائه کارت ملی هوشمند الزامی است',
            'quiet_hours' => 'رعایت آرامش و سکوت بعد از ساعت ۱۲ شب',
            'couple_rules' => 'پذیرش گروه‌های مجردی با هماهنگی قبلی',
        ];

        $rules = $this->house_rules ?? [];
        $formatted = [];
        foreach ($rules as $r) {
            $formatted[] = $defaultMap[$r] ?? $r;
        }
        return $formatted;
    }
}

