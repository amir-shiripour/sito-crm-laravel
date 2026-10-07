<?php

namespace Modules\Properties\App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Properties\Entities\Property;
use Modules\Properties\Entities\PropertyRentalBlock;
use Modules\Properties\Entities\PropertyRentalPrice;
use Modules\Properties\Entities\PropertySetting;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;

class RentalCalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function checkAccess(Property $property)
    {
        $user = auth()->user();
        if ($user->hasRole(['super-admin', 'admin']) || $user->can('properties.manage') || $user->can('properties.rental.calendar')) {
            return true;
        }

        $isCreator = $property->created_by === $user->id;
        $isAgent = $property->agent_id === $user->id;
        $isHost = $property->host && $property->host->user_id === $user->id;

        if (!$isCreator && !$isAgent && !$isHost) {
            abort(403, 'شما دسترسی لازم برای تقویم این اقامتگاه را ندارید.');
        }

        // اگر کاربر میزبان این اقامتگاه است، وضعیت حسابش بررسی شود
        if ($isHost && $property->host && $property->host->status !== 'active') {
            abort(403, 'حساب میزبانی شما هنوز فعال نشده است. مدیریت تقویم و قیمت‌گذاری پس از تأیید حساب توسط مدیریت امکان‌پذیر خواهد بود.');
        }

        return true;
    }

    /**
     * نمایش تقویم اقامتگاه
     */
    public function calendar(Request $request, Property $property)
    {
        $this->checkAccess($property);

        $property->load('rentalConfig', 'seasonalPrices', 'rentalBlocks');
        $currency = PropertySetting::get('currency', 'toman');

        // بازه پیش‌فرض: ماه جاری شمسی و ماه بعد
        $nowJalali = Jalalian::now();
        $currentYear = (int) $request->input('year', $nowJalali->getYear());
        $currentMonth = (int) $request->input('month', $nowJalali->getMonth());

        // تولید روزهای ماه شمسی انتخاب شده
        $startOfMonthJalali = new Jalalian($currentYear, $currentMonth, 1);
        $daysInMonth = $startOfMonthJalali->isLeapYear() && $currentMonth == 12 ? 30 : ($currentMonth <= 6 ? 31 : ($currentMonth < 12 ? 30 : 29));
        
        $monthDays = [];
        $config = $property->rentalConfig;

        // بلاک‌های موجود
        $blocks = $property->rentalBlocks()->get();
        // قیمت‌های فصلی موجود
        $seasonalPrices = $property->seasonalPrices()->latest()->get();

        // بازه زمانی ماه جاری جهت استعلام مناسبت‌های رسمی از دیتابیس محلی تقویم شمسی
        $startCarbon = (new Jalalian($currentYear, $currentMonth, 1))->toCarbon()->subDays(2);
        $endCarbon = (new Jalalian($currentYear, $currentMonth, $daysInMonth))->toCarbon()->addDays(3);
        
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

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $jDate = new Jalalian($currentYear, $currentMonth, $day);
            $cDate = $jDate->toCarbon();
            $dateStr = $cDate->format('Y-m-d');

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

            // تعیین قیمت مؤثر بر اساس سلسله‌مراتب اولویت‌ها:
            // ۱. قیمت ویژه تاریخ خاص | ۲. ایام پیک و تعطیلات | ۳. آخر هفته | ۴. نرخ پایه عادی
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

            // بررسی بلاک بودن
            $isBlocked = $blocks->contains(function ($b) use ($dateStr) {
                return $dateStr >= $b->start_date->format('Y-m-d') && $dateStr <= $b->end_date->format('Y-m-d');
            });

            $monthDays[] = [
                'day' => $day,
                'jalali_date' => $jDate->format('Y/m/d'),
                'carbon_date' => $dateStr,
                'day_of_week' => $rateInfo['day_of_week'],
                'is_weekend' => $rateInfo['is_weekend'],
                'is_holiday' => $rateInfo['is_holiday_or_peak'],
                'is_eve_of_holiday' => $rateInfo['is_eve_of_holiday'],
                'holiday_title' => $rateInfo['holiday_title'],
                'is_blocked' => $isBlocked,
                'price' => $effectivePrice,
                'price_type' => $priceType,
                'custom_title' => $customPrice ? $customPrice->title : null,
                'is_past' => $cDate->isPast() && !$cDate->isToday(),
            ];
        }

        // نام ماه‌های شمسی
        $monthNames = [
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد',
            4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
            7 => 'مهر', 8 => 'آبان', 9 => 'آذر',
            10 => 'دی', 11 => 'بهمن', 12 => 'اسفند'
        ];

        return view('properties::user.rental.calendar', compact(
            'property',
            'monthDays',
            'currentYear',
            'currentMonth',
            'monthNames',
            'currency',
            'startOfMonthJalali'
        ));
    }

    /**
     * سوئیچ بلاک/آنبلاک کردن یک روز (Ajax)
     */
    public function toggleBlock(Request $request, Property $property)
    {
        $this->checkAccess($property);

        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'reason' => 'nullable|string|max:100',
        ]);

        $dateStr = $request->date;

        // جستجوی بلاک موجود در این تاریخ
        $existingBlock = PropertyRentalBlock::where('property_id', $property->id)
            ->where('start_date', '<=', $dateStr)
            ->where('end_date', '>=', $dateStr)
            ->first();

        if ($existingBlock) {
            $existingBlock->delete();
            $blocked = false;
            $msg = 'تاریخ مورد نظر با موفقیت آزاد شد.';
        } else {
            PropertyRentalBlock::create([
                'property_id' => $property->id,
                'start_date' => $dateStr,
                'end_date' => $dateStr,
                'reason' => $request->input('reason', 'مسدود شده توسط کاربر'),
            ]);
            $blocked = true;
            $msg = 'تاریخ مورد نظر مسدود (غیرقابل رزرو) شد.';
        }

        return response()->json([
            'success' => true,
            'blocked' => $blocked,
            'message' => $msg,
        ]);
    }

    /**
     * مسدودسازی دسته‌ای یک بازه زمانی
     */
    public function batchBlock(Request $request, Property $property)
    {
        $this->checkAccess($property);

        $request->validate([
            'start_date_jalali' => 'required|string',
            'end_date_jalali' => 'required|string',
            'reason' => 'nullable|string|max:100',
        ]);

        try {
            $startDate = Jalalian::fromFormat('Y/m/d', $request->start_date_jalali)->toCarbon()->format('Y-m-d');
            $endDate = Jalalian::fromFormat('Y/m/d', $request->end_date_jalali)->toCarbon()->format('Y-m-d');
        } catch (\Exception $e) {
            return back()->with('error', 'فرمت تاریخ‌های وارد شده نامعتبر است.');
        }

        if ($startDate > $endDate) {
            return back()->with('error', 'تاریخ پایان نمی‌تواند قبل از تاریخ شروع باشد.');
        }

        PropertyRentalBlock::create([
            'property_id' => $property->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => $request->input('reason', 'بازه مسدود شده'),
        ]);

        return back()->with('success', 'بازه زمانی با موفقیت مسدود شد.');
    }
}
