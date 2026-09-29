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

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $jDate = new Jalalian($currentYear, $currentMonth, $day);
            $cDate = $jDate->toCarbon();
            $dateStr = $cDate->format('Y-m-d');
            $dayOfWeek = $jDate->getDayOfWeek(); // 0: شنبه تا 6: جمعه

            // بررسی آخر هفته (چهارشنبه=4، پنجشنبه=5 یا جمعه=6)
            $isWeekend = ($dayOfWeek == 4 || $dayOfWeek == 5);

            // بررسی قیمت ویژه
            $customPrice = $seasonalPrices->first(function ($p) use ($dateStr) {
                return $dateStr >= $p->start_date->format('Y-m-d') && $dateStr <= $p->end_date->format('Y-m-d');
            });

            // تعیین قیمت مؤثر
            $effectivePrice = 0;
            if ($customPrice) {
                $effectivePrice = (float) $customPrice->price_per_night;
            } elseif ($config) {
                if ($isWeekend && $config->price_weekend > 0) {
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
                'day_of_week' => $dayOfWeek,
                'is_weekend' => $isWeekend,
                'is_blocked' => $isBlocked,
                'price' => $effectivePrice,
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
            'startOfMonthJalali',
            'seasonalPrices'
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

    /**
     * ثبت قیمت‌گذاری فصلی / مناسبتی
     */
    public function storeSeasonalPrice(Request $request, Property $property)
    {
        $this->checkAccess($property);

        $priceVal = str_replace(',', '', $request->price_per_night);
        $request->merge(['price_per_night' => $priceVal]);

        $request->validate([
            'start_date_jalali' => 'required|string',
            'end_date_jalali' => 'required|string',
            'price_per_night' => 'required|numeric|min:0',
            'title' => 'required|string|max:100',
        ]);

        try {
            $startDate = Jalalian::fromFormat('Y/m/d', $request->start_date_jalali)->toCarbon()->format('Y-m-d');
            $endDate = Jalalian::fromFormat('Y/m/d', $request->end_date_jalali)->toCarbon()->format('Y-m-d');
        } catch (\Exception $e) {
            return back()->with('error', 'فرمت تاریخ‌های وارد شده صحیح نیست.');
        }

        if ($startDate > $endDate) {
            return back()->with('error', 'تاریخ پایان نمی‌تواند پیش از تاریخ شروع باشد.');
        }

        PropertyRentalPrice::create([
            'property_id' => $property->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'price_per_night' => $request->price_per_night,
            'title' => $request->title,
        ]);

        return back()->with('success', 'قیمت ویژه برای بازه مشخص با موفقیت ثبت شد.');
    }

    /**
     * حذف قیمت‌گذاری فصلی
     */
    public function deleteSeasonalPrice(PropertyRentalPrice $price)
    {
        $property = $price->property;
        $this->checkAccess($property);

        $price->delete();
        return back()->with('success', 'قیمت‌گذاری فصلی حذف شد.');
    }
}
