<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Settings\Entities\Setting;

class PageController extends Controller
{
    /**
     * نمایش صفحه اصلی سایت (فرانت).
     */
    public function home()
    {
        // خواندن تنظیمات نحوه نمایش سایت
        $siteDisplayType = Setting::where('key', 'site_display_type')->value('value') ?? 'landing';

        if ($siteDisplayType === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // خواندن قالب انتخاب شده از تنظیمات
        $appTheme = Setting::where('key', 'app_theme')->value('value') ?? 'default';

        $data = compact('appTheme');

        // [Architecture Note]: واکشی دیتای واقعی برای تم‌هایی که نیاز به لیست سرویس‌های رزرو دارند
        // استفاده از class_exists برای جلوگیری از خطای سیستمی در صورت غیرفعال بودن ماژول Booking
        if (in_array($appTheme, ['booking', 'default'])) {
            if (class_exists(\Modules\Booking\Entities\BookingService::class)) {

                $engine = app(\Modules\Booking\Services\BookingEngine::class);
                $settings = \Modules\Booking\Entities\BookingSetting::current();

                $services = \Modules\Booking\Entities\BookingService::query()
                    ->where('status', \Modules\Booking\Entities\BookingService::STATUS_ACTIVE)
                    ->with(['serviceProviders' => function ($query) {
                        $query->where('is_active', true)->with('provider');
                    }])
                    ->orderBy('name')
                    ->get();

                // فیلتر کردن سرویس‌هایی که قابلیت رزرو آنلاین دارند
                $availableServices = $services->filter(function ($service) use ($engine, $settings) {
                    if (!$settings->global_online_booking_enabled) {
                        return false;
                    }
                    foreach ($service->serviceProviders as $sp) {
                        if ($sp->is_active && $engine->isOnlineBookingEnabled($service->id, $sp->provider_user_id)) {
                            return true;
                        }
                    }
                    return false;
                });

                // منطق محاسبه مالیات (مانند کنترلر OnlineBookingController)
                $applyTax = function($price) use ($settings) {
                    if (!$settings->tax_enabled || empty($price)) return $price;
                    $amount = (float) $settings->tax_amount;
                    if ($settings->tax_type === 'PERCENT') return $price + ($price * $amount / 100);
                    return $price + $amount;
                };

                // محاسبه قیمت نهایی برای همه سرویس‌ها
                $availableServices->transform(function ($service) use ($applyTax) {
                    $service->final_price = $applyTax($service->base_price);
                    return $service;
                });

                // بررسی جریان ثبت نوبت کاربر
                $flow = $settings->user_appointment_flow ?? 'SERVICE_FIRST';
                $data['flow'] = $flow;
                $data['bookingSettings'] = $settings;

                if ($flow === 'PROVIDER_FIRST') {
                    $providers = collect();
                    foreach ($availableServices as $service) {
                        foreach ($service->serviceProviders as $sp) {
                            if ($sp->is_active && $engine->isOnlineBookingEnabled($service->id, $sp->provider_user_id)) {
                                $prov = $sp->provider;
                                if ($prov && !$providers->contains('id', $prov->id)) {
                                    $prov->min_price = $service->final_price;
                                    $providers->push($prov);
                                } else if ($prov) {
                                    $existingProv = $providers->firstWhere('id', $prov->id);
                                    $existingProv->min_price = min($existingProv->min_price, $service->final_price);
                                }
                            }
                        }
                    }
                    $data['bookingItems'] = $providers;
                } else {
                    $data['bookingItems'] = $availableServices;
                }

            } else {
                $data['bookingItems'] = collect();
                $data['flow'] = 'SERVICE_FIRST';
            }
        }

        // [Architecture Note]: واکشی دیتای واقعی برای تم املاک (Properties)
        if ($appTheme === 'properties') {
            if (class_exists(\Modules\Properties\Entities\Property::class)) {
                // دریافت ۶ ملک آخر برای نمایش در صفحه اصلی
                // با اضافه کردن attributeValues.attribute از N+1 Query جلوگیری می‌کنیم
                $data['latestProperties'] = \Modules\Properties\Entities\Property::query()
                    ->with(['status', 'category', 'attributeValues.attribute'])
                    ->latest()
                    ->take(6)
                    ->get();

                // واکشی امن تنظیمات دسترسی برای جلوگیری از ارور در View
                $getSetting = function($key) {
                    $val = \Modules\Properties\Entities\PropertySetting::get($key);
                    return $val ? json_decode($val, true) : [];
                };

                $data['priceRoles'] = $getSetting('visibility_price_info');
                $data['coverRoles'] = $getSetting('visibility_cover_image');
                $data['mapRoles'] = $getSetting('visibility_map_info');
                $data['showFeaturesInCard'] = \Modules\Properties\Entities\PropertySetting::get('show_features_in_card', 1);

                // تنظیمات جستجوی هوشمند را برای ویو ارسال می‌کنیم
                $data['aiSearchEnabled'] = \Modules\Properties\Entities\PropertySetting::get('ai_property_search', 0);
            } else {
                $data['latestProperties'] = collect();
                $data['priceRoles'] = [];
                $data['coverRoles'] = [];
                $data['mapRoles'] = [];
                $data['showFeaturesInCard'] = 1;
                $data['aiSearchEnabled'] = 0;
            }
        }

        if ($siteDisplayType === 'theme') {
            if ($appTheme === 'booking') {
                return redirect()->route('booking.public.index');
            } elseif ($appTheme === 'properties') {
                return redirect()->route('properties.index');
            } else {
                // شرکتی، فروشگاه و سایر مواردی که هنوز تعریف نشده‌اند
                return redirect()->route('admin.dashboard');
            }
        }

        // --- اینجا رفتار قبلی برای 'landing' حفظ می‌شود ---
        if (!view()->exists("themes.{$appTheme}.index")) {
            $appTheme = 'default';
        }

        return view("themes.{$appTheme}.index", $data);
    }
}
