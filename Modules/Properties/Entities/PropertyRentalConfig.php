<?php

namespace Modules\Properties\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertyRentalConfig extends Model
{
    use HasFactory;

    protected $table = 'property_rental_configs';

    protected $attributes = [
        'base_guests' => 2,
        'max_guests' => 4,
        'min_stay_nights' => 1,
        'check_in_time' => '14:00:00',
        'check_out_time' => '12:00:00',
        'has_discount' => false,
        'discount_type' => 'percent',
        'discount_value' => null,
        'discount_percent' => null,
        'old_daily_rate' => null,
        'instant_booking' => false,
    ];

    protected $fillable = [
        'property_id',
        'base_guests',
        'max_guests',
        'weekend_days',
        'price_per_night',
        'price_weekend',
        'price_holiday',
        'has_discount',
        'discount_type',
        'discount_value',
        'discount_percent',
        'old_daily_rate',
        'extra_guest_fee',
        'cleaning_fee',
        'check_in_time',
        'check_out_time',
        'min_stay_nights',
        'house_rules',
        'cancellation_policy',
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
        'has_discount' => 'boolean',
        'discount_type' => 'string',
        'discount_value' => 'decimal:0',
        'discount_percent' => 'integer',
        'old_daily_rate' => 'decimal:0',
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

    /**
     * بررسی اینکه آیا تاریخ مشخص‌شده در بازه ۴۸ ساعته تخفیف لحظه آخری (امروز و فردا از ساعت ۰۰:۰۰) قرار دارد یا خیر
     *
     * @param \Carbon\CarbonInterface|string $date
     * @return bool
     */
    public static function isDateInLastMinuteWindow($date): bool
    {
        $cDate = $date instanceof \Carbon\CarbonInterface 
            ? $date->copy()->startOfDay() 
            : \Carbon\Carbon::parse($date)->startOfDay();

        $today = \Carbon\Carbon::today();
        $tomorrow = \Carbon\Carbon::tomorrow();

        return $cDate->equalTo($today) || $cDate->equalTo($tomorrow);
    }

    /**
     * بررسی اینکه آیا یک شب معین مشمول تخفیف لحظه آخری است یا خیر
     * (تخفیف اقامتگاه فعال باشد، تاریخ در بازه ۴۸ ساعته امروز یا فردا باشد و روز مسدود یا در گذشته نباشد)
     *
     * @param \Carbon\CarbonInterface|string $date
     * @param bool $isBlocked
     * @return bool
     */
    public function isDateEligibleForDiscount($date, bool $isBlocked = false): bool
    {
        if (!$this->has_discount || empty($this->discount_value) || (float)$this->discount_value <= 0) {
            return false;
        }

        if ($isBlocked) {
            return false;
        }

        $cDate = $date instanceof \Carbon\CarbonInterface 
            ? $date->copy()->startOfDay() 
            : \Carbon\Carbon::parse($date)->startOfDay();

        // روزهای گذشته مشمول تخفیف نیستند
        if ($cDate->isPast() && !$cDate->isToday()) {
            return false;
        }

        return self::isDateInLastMinuteWindow($cDate);
    }

    /**
     * بررسی دسترسی‌پذیری روزهای بازه ۴۸ ساعته (امروز و فردا)
     * در صورتی که حداقل یکی از این دو روز خالی (غیرمسدود) باشد، پیشنهاد لحظه آخری معتبر است.
     *
     * @param mixed $property
     * @param mixed $blocks
     * @return array{has_deal: bool, today_available: bool, tomorrow_available: bool}
     */
    public function checkLastMinuteAvailability($property = null, $blocks = null): array
    {
        if (!$this->has_discount || empty($this->discount_value) || (float)$this->discount_value <= 0) {
            return [
                'has_deal' => false,
                'today_available' => false,
                'tomorrow_available' => false,
            ];
        }

        $todayStr = \Carbon\Carbon::today()->format('Y-m-d');
        $tomorrowStr = \Carbon\Carbon::tomorrow()->format('Y-m-d');

        $prop = $property ?? ($this->relationLoaded('property') ? $this->property : null);
        $blocksCollection = $blocks ?? ($prop && $prop->relationLoaded('rentalBlocks') ? $prop->rentalBlocks : ($prop ? $prop->rentalBlocks()->get() : collect()));

        $isDateBlocked = function ($dateStr) use ($blocksCollection) {
            if (!$blocksCollection || $blocksCollection->isEmpty()) {
                return false;
            }
            return $blocksCollection->contains(function ($b) use ($dateStr) {
                $s = $b->start_date instanceof \Carbon\CarbonInterface ? $b->start_date->format('Y-m-d') : substr((string)$b->start_date, 0, 10);
                $e = $b->end_date instanceof \Carbon\CarbonInterface ? $b->end_date->format('Y-m-d') : substr((string)$b->end_date, 0, 10);
                return $dateStr >= $s && $dateStr <= $e;
            });
        };

        $todayAvailable = !$isDateBlocked($todayStr);
        $tomorrowAvailable = !$isDateBlocked($tomorrowStr);

        return [
            'has_deal' => $todayAvailable || $tomorrowAvailable,
            'today_available' => $todayAvailable,
            'tomorrow_available' => $tomorrowAvailable,
        ];
    }

    /**
     * دریافت اطلاعات جامع قیمت‌گذاری و نشان تخفیف لحظه آخری برای نمایش در کارت‌ها و هدر اقامتگاه
     *
     * @param mixed $property
     * @param mixed $blocks
     * @return array{
     *     is_active: bool,
     *     today_available: bool,
     *     tomorrow_available: bool,
     *     original_price: float,
     *     discounted_price: float,
     *     discount_percent: int,
     *     discount_amount: float,
     *     discount_type: string,
     *     badge_text: ?string
     * }
     */
    public function getLastMinutePricingInfo($property = null, $blocks = null): array
    {
        $availability = $this->checkLastMinuteAvailability($property, $blocks);

        $prop = $property ?? ($this->relationLoaded('property') ? $this->property : null);
        $baseNightPrice = (float) ($this->price_per_night ?: ($prop?->rent_price ?: ($prop?->price ?: 0)));

        if (!$availability['has_deal'] || $baseNightPrice <= 0) {
            return [
                'is_active' => false,
                'today_available' => $availability['today_available'],
                'tomorrow_available' => $availability['tomorrow_available'],
                'original_price' => $baseNightPrice,
                'discounted_price' => $baseNightPrice,
                'discount_percent' => 0,
                'discount_amount' => 0,
                'discount_type' => $this->discount_type ?? 'percent',
                'badge_text' => null,
            ];
        }

        // اولویت تعیین نرخ: اگر امروز خالی باشد، نرخ امروز؛ در غیر این صورت نرخ فردا
        $targetDate = $availability['today_available'] ? \Carbon\Carbon::today() : \Carbon\Carbon::tomorrow();
        $targetRate = $baseNightPrice;

        // بررسی آیا روز هدف جزء روزهای آخر هفته اقامتگاه است
        if ($this->price_weekend > 0) {
            $jDate = \Morilog\Jalali\Jalalian::fromCarbon($targetDate);
            $dayOfWeek = $jDate->getDayOfWeek(); // ۰ تا ۶
            if ($this->isWeekendDay($dayOfWeek)) {
                $targetRate = (float) $this->price_weekend;
            }
        }

        $discountAmount = $this->calculateDiscountAmount($targetRate);
        $discountedPrice = max(0, $targetRate - $discountAmount);

        $percent = 0;
        if ($this->discount_type === 'percent' && (float)$this->discount_value > 0) {
            $percent = (int) $this->discount_value;
        } elseif ($targetRate > 0) {
            $percent = (int) round(($discountAmount / $targetRate) * 100);
        }

        $badgeText = $percent > 0 ? "{$percent}٪ تخفیف لحظه آخری" : "تخفیف لحظه آخری";

        return [
            'is_active' => true,
            'today_available' => $availability['today_available'],
            'tomorrow_available' => $availability['tomorrow_available'],
            'original_price' => $targetRate,
            'discounted_price' => $discountedPrice,
            'discount_percent' => $percent,
            'discount_amount' => $discountAmount,
            'discount_type' => $this->discount_type ?? 'percent',
            'badge_text' => $badgeText,
        ];
    }

    /**
     * محاسبه مبلغ تخفیف برای یک نرخ مشخص
     */
    public function calculateDiscountAmount(float $originalPrice): float
    {
        if (!$this->has_discount || empty($this->discount_value) || (float)$this->discount_value <= 0) {
            return 0;
        }

        if ($this->discount_type === 'fixed') {
            return min($originalPrice, (float) $this->discount_value);
        }

        // درصدی (پیش‌فرض)
        $percent = min(100, max(0, (float) $this->discount_value));
        return round($originalPrice * ($percent / 100));
    }

    /**
     * محاسبه قیمت نهایی پس از کسر تخفیف
     */
    public function getDiscountedPrice(float $originalPrice): float
    {
        $discountAmount = $this->calculateDiscountAmount($originalPrice);
        return max(0, $originalPrice - $discountAmount);
    }
}

