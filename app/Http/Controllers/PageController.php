<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Settings\Entities\Setting;
use Illuminate\Support\Facades\Route;

class PageController extends Controller
{
    /**
     * نمایش صفحه اصلی سایت (فرانت).
     */
    public function home()
    {
        $siteDisplayType = 'landing';
        $appTheme = 'default';

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $siteDisplayType = Setting::where('key', 'site_display_type')->value('value') ?? 'landing';
                $appTheme = Setting::where('key', 'app_theme')->value('value') ?? 'default';
            }
        } catch (\Throwable $e) {
            $siteDisplayType = 'landing';
            $appTheme = 'default';
        }

        if ($siteDisplayType === 'admin') {
            return $this->redirectToDashboard();
        }

        $appThemeLower = strtolower($appTheme);

        $data = [
            'appTheme' => $appThemeLower,
        ];

        // [Architecture Note]: واکشی دیتای واقعی برای تم‌هایی که نیاز به لیست سرویس‌های رزرو دارند
        // استفاده از class_exists برای جلوگیری از خطای سیستمی در صورت غیرفعال بودن ماژول Booking
        if (in_array($appThemeLower, ['booking', 'default'])) {
            if (class_exists(\Modules\Booking\Entities\BookingService::class) && \App\Services\Modules\BaseModuleInstaller::isInstalled('Booking') && \Illuminate\Support\Facades\Schema::hasTable('booking_settings')) {

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

        // [Architecture Note]: واکشی دیتای واقعی برای تم املاک (Properties) و تم اختصاصی مستر ویلا لفور (MrVilaLafoor)
        if (in_array($appThemeLower, ['properties', 'mrvilalafoor'])) {
            if (class_exists(\Modules\Properties\Entities\Property::class)) {
                // دریافت املاک و اقامتگاه‌ها برای نمایش در صفحه اصلی با تمام روابط اجاره روزانه و چندمیزبانی
                $data['latestProperties'] = \Modules\Properties\Entities\Property::query()
                    ->with(['status', 'category', 'attributeValues.attribute', 'rentalConfig', 'host.user', 'images'])
                    ->latest()
                    ->take(16)
                    ->get();

                $data['properties'] = $data['latestProperties'];

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

                // تنظیمات اختصاصی اجاره روزانه و بازار چندمیزبانی
                $data['rentalModeEnabled'] = (bool) \Modules\Properties\Entities\PropertySetting::get('rental_mode_enabled', 1);
                $data['rentalMultiHostEnabled'] = (bool) \Modules\Properties\Entities\PropertySetting::get('rental_multi_host_enabled', 0);

                // مقاصد در دسترس (استان‌ها و شهرهایی که اقامتگاه فعال و قابل رزرو دارند)
                $data['availableDestinations'] = \Modules\Properties\Entities\Property::query()
                    ->where('publication_status', 'published')
                    ->where('listing_type', 'daily_rental')
                    ->whereNotNull('province')
                    ->select('province', 'city')
                    ->distinct()
                    ->get()
                    ->groupBy('province')
                    ->map(function ($items, $province) {
                        return [
                            'province' => $province,
                            'cities' => $items->pluck('city')->filter()->unique()->values()->all()
                        ];
                    })
                    ->values()
                    ->all();
                $data['rentalDisplaySettings'] = [
                    'show_host_box' => \Modules\Properties\Entities\PropertySetting::get('rental_show_host_box', 'all'),
                    'host_show_avatar' => (bool) \Modules\Properties\Entities\PropertySetting::get('rental_host_show_avatar', 1),
                    'host_show_badge' => (bool) \Modules\Properties\Entities\PropertySetting::get('rental_host_show_badge', 1),
                    'host_show_about' => (bool) \Modules\Properties\Entities\PropertySetting::get('rental_host_show_about', 1),
                    'host_show_phone' => \Modules\Properties\Entities\PropertySetting::get('rental_host_show_phone', 'confirmed_booking'),
                    'show_address' => \Modules\Properties\Entities\PropertySetting::get('rental_show_address', 'all'),
                    'show_map' => \Modules\Properties\Entities\PropertySetting::get('rental_show_map', 'all'),
                    'map_display_type' => \Modules\Properties\Entities\PropertySetting::get('rental_map_display_type', 'circle_then_exact'),
                    'map_circle_radius' => (int) \Modules\Properties\Entities\PropertySetting::get('rental_map_circle_radius', 300),
                    'show_direct_call_btn' => \Modules\Properties\Entities\PropertySetting::get('rental_show_direct_call_btn', 'all'),
                ];
            } else {
                $data['latestProperties'] = collect();
                $data['properties'] = collect();
                $data['priceRoles'] = [];
                $data['coverRoles'] = [];
                $data['mapRoles'] = [];
                $data['showFeaturesInCard'] = 1;
                $data['aiSearchEnabled'] = 0;
                $data['rentalModeEnabled'] = true;
                $data['rentalMultiHostEnabled'] = false;
                $data['rentalDisplaySettings'] = [];
            }
        }

        // [Architecture Note]: واکشی دیتای واقعی برای تم نصاب‌باشی (FieldService)
        if ($appThemeLower === 'fieldservice') {
            if (class_exists(\Modules\FieldService\App\Models\Category::class) && \Illuminate\Support\Facades\Schema::hasTable('field_service_categories')) {
                $data['fsCategories'] = \Modules\FieldService\App\Models\Category::where('is_active', true)->get();
                $data['activeTechCount'] = \Modules\FieldService\App\Models\Technician::where('is_online', true)->count();
                $data['baseTravelFee'] = (float) config('fieldservice.default_travel_fee', 60000);
                $data['defaultWarrantyDays'] = (int) config('fieldservice.default_warranty_days', 90);
                $data['completedOrdersCount'] = \Modules\FieldService\App\Models\ServiceOrder::where('status', \Modules\FieldService\App\Enums\OrderStatus::COMPLETED)->count();
                $data['landingAnnouncement'] = \Modules\FieldService\App\Models\FieldServiceSetting::get('landing_announcement_text', 'پوشش سراسری اعزام تکنسین در کلیه شهرستان‌های مازندران');
                $data['supportPhone'] = \Modules\FieldService\App\Models\FieldServiceSetting::get('landing_support_phone', '۰۱۱-۳۲۲۲۴۴۵۵');
            } else {
                $data['fsCategories'] = collect();
                $data['activeTechCount'] = 0;
                $data['baseTravelFee'] = 60000;
                $data['defaultWarrantyDays'] = 90;
                $data['completedOrdersCount'] = 0;
                $data['landingAnnouncement'] = 'پوشش سراسری اعزام تکنسین در کلیه شهرستان‌های مازندران';
                $data['supportPhone'] = '۰۱۱-۳۲۲۲۴۴۵۵';
            }
        }

        if ($siteDisplayType === 'theme') {
            if ($appThemeLower === 'booking') {
                return redirect()->route('booking.public.index');
            } elseif ($appThemeLower === 'properties') {
                return redirect()->route('properties.index');
            } elseif ($appThemeLower === 'market' || $appThemeLower === 'shop') {
                if (Route::has('market.public.index')) {
                    return redirect()->route('market.public.index');
                }
                return $this->redirectToDashboard();
            } elseif ($appThemeLower === 'fieldservice') {
                return redirect()->route('fieldservice.customer');
            } else {
                // شرکتی و سایر مواردی که هنوز تعریف نشده‌اند
                return $this->redirectToDashboard();
            }
        }

        // بررسی و رندرینگ پویای قالب از طریق ThemeManager
        $themeManager = app(\App\Services\ThemeManager::class);
        if ($themeManager->isSpaTheme()) {
            return $themeManager->handleSpaResponse();
        }

        $viewPath = $themeManager->resolveView('index', "themes.{$appThemeLower}.index");

        return view($viewPath, $data);
    }

    /**
     * هدایت کاربر به داشبورد مربوطه بر اساس نقش کاربر
     */
    protected function redirectToDashboard()
    {
        if (auth()->check()) {
            $user = auth()->user();
            if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('user.dashboard');
        }

        return redirect()->route('admin.dashboard');
    }
}
