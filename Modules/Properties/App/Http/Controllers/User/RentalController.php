<?php

namespace Modules\Properties\App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Properties\Entities\Property;
use Modules\Properties\Entities\PropertyRentalConfig;
use Modules\Properties\Entities\PropertySetting;

use Morilog\Jalali\Jalalian;

class RentalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * بررسی دسترسی کاربر به این ملک
     */
    protected function checkAccess(Property $property)
    {
        $user = auth()->user();
        if ($user->hasRole(['super-admin', 'admin']) || $user->can('properties.manage') || $user->can('properties.rental.manage')) {
            return true;
        }

        // اگر سازنده ملک است یا مشاور منتسب است یا میزبان ملک است
        $isCreator = $property->created_by === $user->id;
        $isAgent = $property->agent_id === $user->id;
        $isHost = $property->host && $property->host->user_id === $user->id;

        if (!$isCreator && !$isAgent && !$isHost) {
            abort(403, 'شما دسترسی لازم برای ویرایش این اقامتگاه را ندارید.');
        }

        return true;
    }

    /**
     * صفحه تنظیمات اقامتگاه (ظرفیت، قیمت شبانه، قوانین و امکانات)
     */
    public function config(Property $property)
    {
        $this->checkAccess($property);

        $currency = PropertySetting::get('currency', 'toman');
        $rentalConfig = $property->rentalConfig ?? new PropertyRentalConfig(['property_id' => $property->id]);

        // نقشه روزهای هفته (۰: شنبه تا ۶: جمعه)
        $weekDays = [
            '0' => 'شنبه',
            '1' => 'یک‌شنبه',
            '2' => 'دوشنبه',
            '3' => 'سه‌شنبه',
            '4' => 'چهارشنبه',
            '5' => 'پنج‌شنبه',
            '6' => 'جمعه',
        ];

        // قیمت‌های ویژه تاریخ‌های خاص برای این اقامتگاه
        $specialPrices = $property->seasonalPrices()->orderBy('start_date')->get()->map(function ($p) {
            $isSingle = $p->start_date->format('Y-m-d') === $p->end_date->format('Y-m-d');
            return [
                'id' => $p->id,
                'title' => $p->title ?? '',
                'type' => $isSingle ? 'single' : 'range',
                'start_date' => Jalalian::fromCarbon($p->start_date)->format('Y/m/d'),
                'end_date' => Jalalian::fromCarbon($p->end_date)->format('Y/m/d'),
                'price' => number_format((float) $p->price_per_night),
            ];
        });

        // قوانین پیش‌فرض اقامتگاه
        $defaultRules = [
            'no_smoking' => 'استعمال دخانیات ممنوع',
            'no_pets' => 'ورود حیوانات خانگی ممنوع',
            'no_party' => 'برگزاری جشن و مهمانی ممنوع',
            'national_card_required' => 'ارائه کارت ملی هوشمند الزامی است',
            'quiet_hours' => 'رعایت آرامش و سکوت بعد از ساعت ۱۲ شب',
            'couple_rules' => 'پذیرش گروه‌های مجردی با هماهنگی قبلی',
        ];

        // تفکیک قوانین پیش‌فرض و قوانین سفارشی کاربر
        $allRules = $rentalConfig->house_rules ?? [];
        $defaultRuleKeys = array_keys($defaultRules);
        $selectedDefaultRules = array_values(array_intersect($allRules, $defaultRuleKeys));
        $existingCustomRules = array_values(array_diff($allRules, $defaultRuleKeys));

        return view('properties::user.rental.config', compact(
            'property',
            'rentalConfig',
            'currency',
            'defaultRules',
            'selectedDefaultRules',
            'existingCustomRules',
            'weekDays',
            'specialPrices'
        ));
    }

    /**
     * به‌روزرسانی تنظیمات اقامتگاه
     */
    public function updateConfig(Request $request, Property $property)
    {
        $this->checkAccess($property);

        // تمیز کردن مقادیر عددی قیمت
        $priceFields = ['price_per_night', 'price_weekend', 'price_holiday', 'extra_guest_fee', 'cleaning_fee'];
        foreach ($priceFields as $field) {
            if ($request->has($field)) {
                $val = $request->input($field);
                $request->merge([
                    $field => (is_null($val) || $val === '') ? null : str_replace(',', '', $val)
                ]);
            }
        }

        $validated = $request->validate([
            'base_guests' => 'required|integer|min:1|max:50',
            'max_guests' => 'required|integer|min:1|max:100|gte:base_guests',
            'weekend_days' => 'nullable|array',
            'weekend_days.*' => 'in:0,1,2,3,4,5,6',
            'price_per_night' => 'required|numeric|min:0',
            'price_weekend' => 'nullable|numeric|min:0',
            'price_holiday' => 'nullable|numeric|min:0',
            'extra_guest_fee' => 'nullable|numeric|min:0',
            'cleaning_fee' => 'nullable|numeric|min:0',
            'check_in_time' => 'nullable|string',
            'check_out_time' => 'nullable|string',
            'min_stay_nights' => 'required|integer|min:1|max:30',
            'instant_booking' => 'nullable|boolean',
            'house_rules' => 'nullable|array',
            'custom_rules' => 'nullable|array',
            'custom_rules.*' => 'nullable|string|max:255',
            'special_prices' => 'nullable|array',
        ]);

        $validated['instant_booking'] = $request->has('instant_booking');
        $validated['weekend_days'] = $request->input('weekend_days', ['4', '5']);

        // ادغام قوانین پیش‌فرض تیک‌خورده با قوانین سفارشی ثبت‌شده
        $defaultSelected = (array) $request->input('house_rules', []);
        $customRules = array_filter(array_map('trim', (array) $request->input('custom_rules', [])));
        $validated['house_rules'] = array_values(array_unique(array_merge($defaultSelected, $customRules)));
        unset($validated['custom_rules']);

        $specialPricesData = $request->input('special_prices', []);
        unset($validated['special_prices']);

        // به‌روزرسانی یا ایجاد
        PropertyRentalConfig::updateOrCreate(
            ['property_id' => $property->id],
            $validated
        );

        // مدیریت و ذخیره‌سازی تاریخ‌های خاص (تکی و چندتایی)
        $property->seasonalPrices()->delete();
        if (is_array($specialPricesData)) {
            foreach ($specialPricesData as $item) {
                if (empty($item['start_date']) || empty($item['price'])) {
                    continue;
                }
                $cleanPrice = (float) str_replace(',', '', $item['price']);
                if ($cleanPrice <= 0) {
                    continue;
                }

                try {
                    $startDateCarbon = Jalalian::fromFormat('Y/m/d', trim($item['start_date']))->toCarbon();
                    $type = $item['type'] ?? 'single';
                    if ($type === 'single' || empty($item['end_date'])) {
                        $endDateCarbon = $startDateCarbon;
                    } else {
                        $endDateCarbon = Jalalian::fromFormat('Y/m/d', trim($item['end_date']))->toCarbon();
                    }

                    if ($startDateCarbon->gt($endDateCarbon)) {
                        [$startDateCarbon, $endDateCarbon] = [$endDateCarbon, $startDateCarbon];
                    }

                    $property->seasonalPrices()->create([
                        'title' => !empty($item['title']) ? trim($item['title']) : null,
                        'start_date' => $startDateCarbon->format('Y-m-d'),
                        'end_date' => $endDateCarbon->format('Y-m-d'),
                        'price_per_night' => $cleanPrice,
                    ]);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Error saving rental special price: ' . $e->getMessage());
                }
            }
        }

        // هماهنگ‌سازی قیمت پایه با فیلد price جدول properties جهت نمایش در لیست‌ها
        $property->update([
            'price' => $validated['price_per_night'],
        ]);

        return redirect()->route('user.properties.rental.calendar', $property)->with('success', 'مشخصات و قیمت‌های اقامتگاه با موفقیت ذخیره شد. اکنون تقویم دسترسی را مشاهده کنید.');
    }

    /**
     * تایید یا رد اقامتگاه توسط مدیر پلتفرم
     */
    public function reviewStatus(Request $request, Property $property)
    {
        $user = auth()->user();
        if (!$user->hasRole(['super-admin', 'admin']) && !$user->can('properties.manage')) {
            abort(403);
        }

        $request->validate([
            'approval_status' => 'required|in:approved,rejected',
            'rejection_reason' => 'nullable|required_if:approval_status,rejected|string|max:500',
        ]);

        $property->update([
            'approval_status' => $request->approval_status,
            'rejection_reason' => $request->approval_status === 'rejected' ? $request->rejection_reason : null,
        ]);

        $statusText = $request->approval_status === 'approved' ? 'تأیید شد' : 'رد شد';
        return back()->with('success', "وضعیت اقامتگاه با موفقیت به «{$statusText}» تغییر یافت.");
    }
}
