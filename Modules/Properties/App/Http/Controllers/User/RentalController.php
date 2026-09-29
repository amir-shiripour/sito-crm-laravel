<?php

namespace Modules\Properties\App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Properties\Entities\Property;
use Modules\Properties\Entities\PropertyRentalConfig;
use Modules\Properties\Entities\PropertySetting;

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

        // فهرست پیش‌فرض امکانات اقامتگاه
        $defaultAmenities = [
            'pool_outdoor' => 'استخر روباز',
            'pool_indoor' => 'استخر سرپوشیده آبگرم',
            'jacuzzi' => 'جکوزی',
            'bbq' => 'باربیکیو / کباب‌پز',
            'billiard' => 'میز بیلیارد',
            'foosball' => 'فوتبال دستی',
            'wifi' => 'اینترنت وای‌فای',
            'parking' => 'پارکینگ اختصاصی',
            'air_conditioner' => 'کولر گازی / اسپلیت',
            'heating' => 'سیستم گرمایشی مطلوب',
            'yard' => 'حیاط و فضای سبز',
            'view' => 'چشم‌انداز طبیعت / دریا',
            'kitchen_ware' => 'تجهیزات کامل آشپزخانه',
            'washing_machine' => 'ماشین لباسشویی',
            'tv' => 'تلویزیون و سیستم صوتی',
        ];

        // قوانین پیش‌فرض اقامتگاه
        $defaultRules = [
            'no_smoking' => 'استعمال دخانیات ممنوع',
            'no_pets' => 'ورود حیوانات خانگی ممنوع',
            'no_party' => 'برگزاری جشن و مهمانی ممنوع',
            'national_card_required' => 'ارائه کارت ملی هوشمند الزامی است',
            'quiet_hours' => 'رعایت آرامش و سکوت بعد از ساعت ۱۲ شب',
            'couple_rules' => 'پذیرش گروه‌های مجردی با هماهنگی قبلی',
        ];

        return view('properties::user.rental.config', compact(
            'property',
            'rentalConfig',
            'currency',
            'defaultAmenities',
            'defaultRules'
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
            'bedrooms' => 'required|integer|min:0|max:20',
            'double_beds' => 'nullable|integer|min:0|max:20',
            'single_beds' => 'nullable|integer|min:0|max:20',
            'bathrooms' => 'required|integer|min:1|max:10',
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
            'rental_amenities' => 'nullable|array',
        ]);

        $validated['instant_booking'] = $request->has('instant_booking');

        // به‌روزرسانی یا ایجاد
        PropertyRentalConfig::updateOrCreate(
            ['property_id' => $property->id],
            $validated
        );

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
