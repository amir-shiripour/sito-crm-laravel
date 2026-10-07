<?php

namespace Modules\Properties\Services;

use App\Models\User;
use Modules\Properties\Entities\Property;
use Modules\Properties\Entities\PropertyRentalConfig;
use Modules\Properties\Entities\PropertyRevision;
use Modules\Properties\Entities\PropertySetting;

class PropertyRevisionService
{
    /**
     * بررسی اینکه آیا کاربر دسترسی مدیریتی برای مدیریت انتشار و فیلدهای حساس را دارد یا خیر
     */
    public static function canManagePublication(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->hasRole(['super-admin', 'admin'])
            || $user->can('properties.manage')
            || $user->can('properties.edit.all')
            || $user->can('properties.hosts.manage');
    }

    /**
     * ثبت تغییرات فیلدهای اصلی ملک توسط میزبان
     */
    public function recordPropertyChanges(Property $property, array $dirtyAttributes, User $user, array $additionalMeta = []): ?PropertyRevision
    {
        if (empty($dirtyAttributes)
            && empty($additionalMeta['gallery_added'])
            && empty($additionalMeta['gallery_removed'])
            && empty($additionalMeta['attributes'])
            && empty($additionalMeta['features'])
            && empty($additionalMeta['meta_details'])
            && empty($additionalMeta['meta_features'])
        ) {
            return null;
        }

        $oldData = [];
        $newData = [];
        $summary = [];

        $labels = [
            'title' => 'عنوان اقامتگاه',
            'description' => 'توضیحات اقامتگاه',
            'property_type' => 'نوع ملک',
            'listing_type' => 'نوع معامله',
            'address' => 'آدرس اقامتگاه',
            'area' => 'متراژ اقامتگاه (متر مربع)',
            'latitude' => 'موقعیت نقشه (عرض جغرافیایی)',
            'longitude' => 'موقعیت نقشه (طول جغرافیایی)',
            'cover_image' => 'تصویر کاور اقامتگاه',
            'video' => 'ویدیو اقامتگاه',
            'category_id' => 'دسته‌بندی',
            'rooms' => 'تعداد اتاق',
            'bedrooms' => 'تعداد اتاق خواب',
            'bathrooms' => 'سرویس بهداشتی و حمام',
            'floors' => 'تعداد کل طبقات',
            'floor' => 'طبقه واحد',
            'build_year' => 'سال ساخت',
            'parking' => 'پارکینگ',
            'warehouse' => 'انباری',
            'elevator' => 'آسانسور',
            'balcony' => 'بالکن',
            'city_id' => 'شهر',
            'province_id' => 'استان',
            'neighborhood' => 'محله / منطقه',
            'price' => 'قیمت پایه / هر شب',
        ];

        foreach ($dirtyAttributes as $key => $newValue) {
            // فیلدهای سیستمی را نادیده بگیریم
            if (in_array($key, ['updated_at', 'created_at', 'id', 'approval_status', 'rejection_reason', 'code'])) {
                continue;
            }

            $oldValue = $property->getOriginal($key);

            // نادیده گرفتن مقادیر یکسان عددی یا رشته‌ای
            if (is_numeric($oldValue) && is_numeric($newValue) && (float)$oldValue == (float)$newValue) {
                continue;
            }
            if ((is_null($oldValue) || $oldValue === '') && (is_null($newValue) || $newValue === '')) {
                continue;
            }

            $oldData[$key] = $oldValue;
            $newData[$key] = $newValue;

            $label = $labels[$key] ?? $key;
            $type = 'text';

            if ($key === 'cover_image') {
                $type = 'image';
            } elseif ($key === 'area') {
                $oldValue = $oldValue ? (rtrim(rtrim((string)$oldValue, '0'), '.') . ' متر مربع') : '—';
                $newValue = $newValue ? (rtrim(rtrim((string)$newValue, '0'), '.') . ' متر مربع') : '—';
            } elseif ($key === 'price') {
                $currency = PropertySetting::get('currency', 'toman') === 'toman' ? 'تومان' : 'ریال';
                $oldValue = $oldValue ? (number_format((float)$oldValue) . ' ' . $currency) : '—';
                $newValue = $newValue ? (number_format((float)$newValue) . ' ' . $currency) : '—';
            } elseif ($key === 'property_type') {
                $typesMap = [
                    'apartment' => 'خانه و آپارتمان',
                    'villa' => 'ویلا و باغچه',
                    'land' => 'زمین و کلنگی',
                    'office' => 'اداری و تجاری',
                ];
                $oldValue = $typesMap[$oldValue] ?? $oldValue;
                $newValue = $typesMap[$newValue] ?? $newValue;
            } elseif ($key === 'listing_type') {
                $listingMap = [
                    'daily_rental' => 'اجاره روزانه اقامتگاه',
                    'sale' => 'فروش',
                    'rent' => 'رهن و اجاره',
                    'presale' => 'پیش‌فروش',
                ];
                $oldValue = $listingMap[$oldValue] ?? $oldValue;
                $newValue = $listingMap[$newValue] ?? $newValue;
            } elseif (in_array($key, ['parking', 'warehouse', 'elevator', 'balcony'])) {
                $oldValue = $oldValue ? 'دارد' : 'ندارد';
                $newValue = $newValue ? 'دارد' : 'ندارد';
            }

            $summary[] = [
                'field' => $key,
                'label' => $label,
                'type' => $type,
                'old' => $oldValue ?? '—',
                'new' => $newValue ?? '—',
            ];
        }

        // بررسی تصاویر گالری اضافه یا حذف شده
        if (!empty($additionalMeta['gallery_added'])) {
            $summary[] = [
                'field' => 'gallery_images',
                'label' => 'تصاویر گالری جدید',
                'type' => 'text',
                'old' => '—',
                'new' => count($additionalMeta['gallery_added']) . ' تصویر جدید اضافه شد',
            ];
            $newData['gallery_added'] = $additionalMeta['gallery_added'];
        }

        if (!empty($additionalMeta['gallery_removed'])) {
            $summary[] = [
                'field' => 'gallery_removed',
                'label' => 'تصاویر حذف شده از گالری',
                'type' => 'text',
                'old' => count($additionalMeta['gallery_removed']) . ' تصویر حذف شد',
                'new' => '—',
            ];
            $oldData['gallery_removed'] = $additionalMeta['gallery_removed'];
        }

        // بررسی اطلاعات تکمیلی و مشخصات پایه (Dynamic Attributes)
        if (isset($additionalMeta['attributes'])) {
            $newAttrs = (array) $additionalMeta['attributes'];
            $currentAttributeValues = \Modules\Properties\Entities\PropertyAttributeValue::where('property_id', $property->id)
                ->pluck('value', 'attribute_id')
                ->toArray();

            $allAttrIds = array_unique(array_merge(array_keys($currentAttributeValues), array_keys($newAttrs)));
            if (!empty($allAttrIds)) {
                $attributesList = \Modules\Properties\Entities\PropertyAttribute::whereIn('id', $allAttrIds)->get()->keyBy('id');

                $oldAttributesData = [];
                $newAttributesData = [];

                foreach ($allAttrIds as $attrId) {
                    $attrModel = $attributesList->get($attrId);
                    if (!$attrModel || $attrModel->section === 'features') {
                        continue;
                    }

                    $oldVal = trim((string)($currentAttributeValues[$attrId] ?? ''));
                    $newVal = trim((string)($newAttrs[$attrId] ?? ''));

                    if ($oldVal !== $newVal) {
                        $oldAttributesData[$attrId] = $oldVal;
                        $newAttributesData[$attrId] = $newVal;

                        $summary[] = [
                            'field' => 'attribute_' . $attrId,
                            'label' => $attrModel->name ?? ('مشخصه ' . $attrId),
                            'type' => 'text',
                            'old' => $oldVal !== '' ? $oldVal : '—',
                            'new' => $newVal !== '' ? $newVal : '—',
                        ];
                    }
                }

                if (!empty($newAttributesData)) {
                    $oldData['attributes'] = $oldAttributesData;
                    $newData['attributes'] = $newAttrs;
                }
            }
        }

        // بررسی امکانات اقامتگاه (Features)
        if (isset($additionalMeta['features'])) {
            $newFeatureIds = array_map('intval', (array) $additionalMeta['features']);
            $currentFeatureIds = \Modules\Properties\Entities\PropertyAttributeValue::where('property_id', $property->id)
                ->whereHas('attribute', fn($q) => $q->where('section', 'features'))
                ->pluck('attribute_id')
                ->map(fn($v) => (int)$v)
                ->toArray();

            sort($newFeatureIds);
            sort($currentFeatureIds);

            if ($newFeatureIds !== $currentFeatureIds) {
                $allFeatureIds = array_unique(array_merge($currentFeatureIds, $newFeatureIds));
                $featuresList = \Modules\Properties\Entities\PropertyAttribute::whereIn('id', $allFeatureIds)->pluck('name', 'id')->toArray();

                $oldFeatureNames = array_map(fn($id) => $featuresList[$id] ?? ('امکان ' . $id), $currentFeatureIds);
                $newFeatureNames = array_map(fn($id) => $featuresList[$id] ?? ('امکان ' . $id), $newFeatureIds);

                $summary[] = [
                    'field' => 'features',
                    'label' => 'امکانات و تجهیزات اقامتگاه',
                    'type' => 'text',
                    'old' => !empty($oldFeatureNames) ? implode('، ', $oldFeatureNames) : '—',
                    'new' => !empty($newFeatureNames) ? implode('، ', $newFeatureNames) : '—',
                ];

                $oldData['features'] = $currentFeatureIds;
                $newData['features'] = $newFeatureIds;
            }
        }

        // بررسی ویژگی‌های سفارشی (Custom Meta Details)
        if (isset($additionalMeta['meta_details'])) {
            $currentMetaDetails = $property->meta['details'] ?? [];
            $newMetaDetails = $additionalMeta['meta_details'];

            if ($currentMetaDetails !== $newMetaDetails) {
                $oldDetailsStr = [];
                foreach ($currentMetaDetails as $k => $v) {
                    $oldDetailsStr[] = $k . ': ' . $v;
                }
                $newDetailsStr = [];
                foreach ($newMetaDetails as $k => $v) {
                    $newDetailsStr[] = $k . ': ' . $v;
                }

                $summary[] = [
                    'field' => 'meta_details',
                    'label' => 'ویژگی‌های سفارشی اقامتگاه',
                    'type' => 'text',
                    'old' => !empty($oldDetailsStr) ? implode(' | ', $oldDetailsStr) : '—',
                    'new' => !empty($newDetailsStr) ? implode(' | ', $newDetailsStr) : '—',
                ];

                $oldData['meta_details'] = $currentMetaDetails;
                $newData['meta_details'] = $newMetaDetails;
            }
        }

        // بررسی امکانات سفارشی (Custom Meta Features)
        if (isset($additionalMeta['meta_features'])) {
            $currentMetaFeatures = (array) ($property->meta['features'] ?? []);
            $newMetaFeatures = (array) $additionalMeta['meta_features'];

            $tempOld = array_values(array_filter($currentMetaFeatures));
            $tempNew = array_values(array_filter($newMetaFeatures));
            sort($tempOld);
            sort($tempNew);

            if ($tempOld !== $tempNew) {
                $summary[] = [
                    'field' => 'meta_features',
                    'label' => 'امکانات سفارشی اقامتگاه',
                    'type' => 'text',
                    'old' => !empty($currentMetaFeatures) ? implode('، ', $currentMetaFeatures) : '—',
                    'new' => !empty($newMetaFeatures) ? implode('، ', $newMetaFeatures) : '—',
                ];

                $oldData['meta_features'] = $currentMetaFeatures;
                $newData['meta_features'] = $newMetaFeatures;
            }
        }

        if (empty($summary)) {
            return null;
        }

        $revision = PropertyRevision::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'type' => 'property_update',
            'status' => 'pending',
            'old_data' => $oldData,
            'new_data' => $newData,
            'changes_summary' => $summary,
        ]);

        $property->update([
            'approval_status' => 'pending_review',
        ]);

        return $revision;
    }

    /**
     * ثبت تغییرات نرخ‌ها و شرایط اقامتگاه توسط میزبان
     */
    public function recordRentalConfigChanges(Property $property, PropertyRentalConfig|array $configOrDirty, $dirtyOrUser = null, $userOrMeta = null, array $additionalMeta = [], array $originalAttributes = []): ?PropertyRevision
    {
        if ($configOrDirty instanceof PropertyRentalConfig) {
            $config = $configOrDirty;
            $dirtyConfig = is_array($dirtyOrUser) ? $dirtyOrUser : [];
            $user = $userOrMeta instanceof User ? $userOrMeta : auth()->user();
        } else {
            // حالتی که آرایه dirtyConfig به عنوان پارامتر دوم ارسال شده باشد
            $dirtyConfig = is_array($configOrDirty) ? $configOrDirty : [];
            $config = $property->rentalConfig ?? new PropertyRentalConfig(['property_id' => $property->id]);
            $user = $dirtyOrUser instanceof User ? $dirtyOrUser : auth()->user();
            if (is_array($userOrMeta)) {
                $additionalMeta = $userOrMeta;
            }
        }

        if (empty($dirtyConfig) && empty($additionalMeta['special_prices_count'])) {
            return null;
        }

        $currency = PropertySetting::get('currency', 'toman') === 'toman' ? 'تومان' : 'ریال';

        $labels = [
            'price_per_night' => 'قیمت هر شب وسط هفته / پایه',
            'price_weekend' => 'قیمت هر شب آخر هفته',
            'price_holiday' => 'قیمت ایام پیک و تعطیلات',
            'base_guests' => 'ظرفیت مهمان پایه',
            'max_guests' => 'حداکثر ظرفیت مهمان',
            'extra_guest_fee' => 'هزینه هر نفر اضافه',
            'cleaning_fee' => 'هزینه نظافت',
            'min_stay_nights' => 'حداقل مدت اقامت',
            'check_in_time' => 'ساعت تحویل (ورود)',
            'check_out_time' => 'ساعت تخلیه (خروج)',
            'weekend_days' => 'روزهای آخر هفته منتخب',
            'house_rules' => 'قوانین اقامتگاه',
            'instant_booking' => 'رزرو قطعی آنی',
            'base_price_per_night' => 'قیمت هر شب وسط هفته',
            'weekend_price_per_night' => 'قیمت هر شب آخر هفته',
            'peak_price_per_night' => 'قیمت ایام پیک و تعطیلات',
            'checkin_time' => 'ساعت تحویل (ورود)',
            'checkout_time' => 'ساعت تخلیه (خروج)',
            'cancellation_policy' => 'مقررات لغو رزرو',
            'amenities' => 'امکانات اقامتگاه',
        ];

        $persianWeekDays = [
            '0' => 'شنبه',
            '1' => 'یک‌شنبه',
            '2' => 'دوشنبه',
            '3' => 'سه‌شنبه',
            '4' => 'چهارشنبه',
            '5' => 'پنج‌شنبه',
            '6' => 'جمعه',
        ];

        $defaultRuleNames = [
            'no_smoking' => 'استعمال دخانیات ممنوع',
            'no_pets' => 'ورود حیوانات خانگی ممنوع',
            'no_party' => 'برگزاری جشن و مهمانی ممنوع',
            'national_card_required' => 'ارائه کارت ملی هوشمند الزامی است',
            'quiet_hours' => 'رعایت آرامش و سکوت بعد از ساعت ۱۲ شب',
            'couple_rules' => 'پذیرش گروه‌های مجردی با هماهنگی قبلی',
        ];

        $oldData = [];
        $newData = [];
        $summary = [];

        foreach ($dirtyConfig as $key => $newValue) {
            if (in_array($key, ['updated_at', 'created_at', 'id', 'property_id'])) {
                continue;
            }

            // استخراج مقدار قبلی واقعی (در صورت ارسال originalAttributes از آن استفاده می‌کنیم)
            $oldValue = array_key_exists($key, $originalAttributes)
                ? $originalAttributes[$key]
                : $config->getOriginal($key);

            // ۱. بررسی و فیلتر تغییرات کاذب زمان ورود و خروج
            if (in_array($key, ['check_in_time', 'check_out_time', 'checkin_time', 'checkout_time'])) {
                $oldNorm = $oldValue ? substr((string)$oldValue, 0, 5) : '';
                $newNorm = $newValue ? substr((string)$newValue, 0, 5) : '';
                $defaultTime = in_array($key, ['check_in_time', 'checkin_time']) ? '14:00' : '12:00';

                // اگر فرمت‌های نرمال‌شده H:i یکسان باشند یا مقدار پیش‌فرض دست‌نخورده باقی مانده باشد، تغییر تلقی نشود
                if ($oldNorm === $newNorm || (empty($oldNorm) && $newNorm === $defaultTime)) {
                    continue;
                }
            }

            // ۲. بررسی و فیلتر مقادیر عددی یکسان (مثلاً 500000.00 و 500000)
            if (is_numeric($oldValue) && is_numeric($newValue) && (float)$oldValue == (float)$newValue) {
                continue;
            }
            if ((is_null($oldValue) || $oldValue === '') && (is_null($newValue) || $newValue === '')) {
                continue;
            }

            // ۳. نرمال‌سازی امن و کامل مقادیر آرایه‌ای (حتی در صورت کدگذاری تو در تو JSON)
            $oldValue = $this->unwrapArray($oldValue);
            $newValue = $this->unwrapArray($newValue);

            // ۴. بررسی و فیلتر آرایه‌های یکسان
            if (is_array($oldValue) && is_array($newValue)) {
                $tempOld = array_map('strval', $oldValue);
                $tempNew = array_map('strval', $newValue);
                sort($tempOld);
                sort($tempNew);
                if ($tempOld === $tempNew) {
                    continue;
                }
            }

            // ۵. روزهای آخر هفته: اگر مقدار قبلی خالی بوده و جدید همان پیش‌فرض ['4', '5'] است
            if ($key === 'weekend_days') {
                $oldDays = is_array($oldValue) ? $oldValue : (empty($oldValue) ? [] : (array)$oldValue);
                $newDays = is_array($newValue) ? $newValue : (empty($newValue) ? [] : (array)$newValue);
                if (empty($oldDays) && ($newDays === ['4', '5'] || $newDays === [4, 5])) {
                    continue;
                }
            }

            // ۶. رزرو قطعی آنی: در صورتی که وضعیت بولی تغییری نکرده باشد
            if ($key === 'instant_booking' && (bool)$oldValue === (bool)$newValue) {
                continue;
            }

            $oldData[$key] = $oldValue;
            $newData[$key] = $newValue;

            $label = $labels[$key] ?? $key;

            // فرمت‌بندی خوانا برای مدیر پلتفرم
            if (in_array($key, ['price_per_night', 'price_weekend', 'price_holiday', 'base_price_per_night', 'weekend_price_per_night', 'peak_price_per_night', 'extra_guest_fee', 'cleaning_fee'])) {
                $oldFormatted = ($oldValue && (float)$oldValue > 0) ? (number_format((float)$oldValue) . ' ' . $currency) : '—';
                $newFormatted = ($newValue && (float)$newValue > 0) ? (number_format((float)$newValue) . ' ' . $currency) : '—';
            } elseif (in_array($key, ['base_guests', 'max_guests'])) {
                $oldFormatted = $oldValue ? ($oldValue . ' نفر') : '—';
                $newFormatted = $newValue ? ($newValue . ' نفر') : '—';
            } elseif ($key === 'min_stay_nights') {
                $oldFormatted = $oldValue ? ($oldValue . ' شب') : '—';
                $newFormatted = $newValue ? ($newValue . ' شب') : '—';
            } elseif (in_array($key, ['check_in_time', 'check_out_time', 'checkin_time', 'checkout_time'])) {
                $oldFormatted = $oldValue ? substr((string)$oldValue, 0, 5) : '—';
                $newFormatted = $newValue ? substr((string)$newValue, 0, 5) : '—';
            } elseif ($key === 'weekend_days') {
                $oldDays = is_array($oldValue) ? $oldValue : [];
                $newDays = is_array($newValue) ? $newValue : [];
                $oldNames = array_map(fn($d) => $persianWeekDays[(string)$d] ?? (string)$d, $oldDays);
                $newNames = array_map(fn($d) => $persianWeekDays[(string)$d] ?? (string)$d, $newDays);
                $oldFormatted = !empty($oldNames) ? implode('، ', $oldNames) : '—';
                $newFormatted = !empty($newNames) ? implode('، ', $newNames) : '—';
            } elseif ($key === 'house_rules') {
                $oldRules = is_array($oldValue) ? $oldValue : [];
                $newRules = is_array($newValue) ? $newValue : [];
                $oldMapped = array_map(fn($r) => $defaultRuleNames[(string)$r] ?? (string)$r, $oldRules);
                $newMapped = array_map(fn($r) => $defaultRuleNames[(string)$r] ?? (string)$r, $newRules);
                $oldFormatted = !empty($oldMapped) ? implode('، ', $oldMapped) : '—';
                $newFormatted = !empty($newMapped) ? implode('، ', $newMapped) : '—';
            } elseif ($key === 'instant_booking') {
                $oldFormatted = $oldValue ? 'فعال (رزرو آنی بدون تایید)' : 'غیرفعال (نیاز به هماهنگی)';
                $newFormatted = $newValue ? 'فعال (رزرو آنی بدون تایید)' : 'غیرفعال (نیاز به هماهنگی)';
            } elseif (is_array($oldValue) || is_array($newValue)) {
                $oldFormatted = is_array($oldValue) ? implode('، ', $oldValue) : ($oldValue ?? '—');
                $newFormatted = is_array($newValue) ? implode('، ', $newValue) : ($newValue ?? '—');
            } else {
                $oldFormatted = (string)($oldValue ?? '—');
                $newFormatted = (string)($newValue ?? '—');
            }

            $summary[] = [
                'field' => $key,
                'label' => $label,
                'type' => 'text',
                'old' => $oldFormatted,
                'new' => $newFormatted,
            ];
        }

        // افزودن تاریخ‌های خاص در صورت وجود تغییرات
        if (!empty($additionalMeta['special_prices_count']) && $additionalMeta['special_prices_count'] > 0) {
            $summary[] = [
                'field' => 'special_prices',
                'label' => 'نرخ‌های اختصاصی تقویم',
                'type' => 'text',
                'old' => '—',
                'new' => $additionalMeta['special_prices_count'] . ' بازه / روز با نرخ اختصاصی ثبت شد',
            ];
            if (isset($additionalMeta['special_prices'])) {
                $newData['special_prices'] = $additionalMeta['special_prices'];
            }
        }

        if (empty($summary)) {
            return null;
        }

        $revision = PropertyRevision::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'type' => 'rental_config_update',
            'status' => 'pending',
            'old_data' => $oldData,
            'new_data' => $newData,
            'changes_summary' => $summary,
        ]);

        $property->update([
            'approval_status' => 'pending_review',
        ]);

        return $revision;
    }

    /**
     * تایید نسخه ویرایش و اعمال تغییرات به نسخه زنده و عمومی اقامتگاه
     */
    public function approveRevision(PropertyRevision $revision, User $reviewer): void
    {
        $property = $revision->property;
        $newData = is_array($revision->new_data) ? $revision->new_data : [];

        if ($property && !empty($newData)) {
            if ($revision->type === 'rental_config_update') {
                // ۱. اعمال تغییرات تنظیمات اقامتگاه (PropertyRentalConfig)
                $config = $property->rentalConfig ?? new PropertyRentalConfig(['property_id' => $property->id]);
                $configData = [];
                $validConfigKeys = [
                    'base_guests', 'max_guests', 'weekend_days', 'price_per_night',
                    'price_weekend', 'price_holiday', 'extra_guest_fee', 'cleaning_fee',
                    'check_in_time', 'check_out_time', 'min_stay_nights', 'instant_booking',
                    'house_rules'
                ];

                foreach ($validConfigKeys as $key) {
                    if (array_key_exists($key, $newData)) {
                        $val = $newData[$key];
                        if (in_array($key, ['weekend_days', 'house_rules'])) {
                            $val = $this->unwrapArray($val);
                        }
                        $configData[$key] = $val;
                    }
                }

                if (!empty($configData)) {
                    $config->fill($configData);
                    $config->save();
                }

                // ۲. به‌روزرسانی قیمت پایه ملک
                if (isset($newData['price_per_night'])) {
                    $property->price = $newData['price_per_night'];
                    $property->save();
                }

                // ۳. اعمال تاریخ‌های ویژه تقویم (Special / Seasonal Prices) در صورت وجود
                if (isset($newData['special_prices']) && is_array($newData['special_prices'])) {
                    $property->seasonalPrices()->delete();
                    foreach ($newData['special_prices'] as $sp) {
                        if (empty($sp['start_date']) || empty($sp['price'])) {
                            continue;
                        }
                        $cleanPrice = (float) str_replace(',', '', (string)$sp['price']);
                        if ($cleanPrice <= 0) {
                            continue;
                        }

                        try {
                            $startDateCarbon = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', trim($sp['start_date']))->toCarbon();
                            $type = $sp['type'] ?? 'single';
                            if ($type === 'single' || empty($sp['end_date'])) {
                                $endDateCarbon = $startDateCarbon;
                            } else {
                                $endDateCarbon = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', trim($sp['end_date']))->toCarbon();
                            }

                            if ($startDateCarbon->gt($endDateCarbon)) {
                                [$startDateCarbon, $endDateCarbon] = [$endDateCarbon, $startDateCarbon];
                            }

                            $property->seasonalPrices()->create([
                                'title' => !empty($sp['title']) ? trim($sp['title']) : null,
                                'start_date' => $startDateCarbon->format('Y-m-d'),
                                'end_date' => $endDateCarbon->format('Y-m-d'),
                                'price_per_night' => $cleanPrice,
                            ]);
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::warning('Error applying approved special price: ' . $e->getMessage());
                        }
                    }
                }
            } elseif ($revision->type === 'property_update') {
                // اعمال تغییرات مشخصات اصلی ملک (Property)
                $propertyAttributes = [];
                $reservedKeys = ['gallery_added', 'gallery_removed', 'attributes', 'features', 'meta_details', 'meta_features'];

                foreach ($newData as $key => $value) {
                    if (!in_array($key, $reservedKeys) && \Illuminate\Support\Facades\Schema::hasColumn('properties', $key)) {
                        $propertyAttributes[$key] = $value;
                    }
                }

                if (!empty($propertyAttributes)) {
                    $property->fill($propertyAttributes);
                }

                // اعمال ویژگی‌ها یا امکانات در صورت وجود
                if (isset($newData['attributes']) && is_array($newData['attributes'])) {
                    foreach ($newData['attributes'] as $attrId => $val) {
                        if (!empty($val)) {
                            \Modules\Properties\Entities\PropertyAttributeValue::updateOrCreate(
                                ['property_id' => $property->id, 'attribute_id' => $attrId],
                                ['value' => $val]
                            );
                        } else {
                            \Modules\Properties\Entities\PropertyAttributeValue::where('property_id', $property->id)
                                ->where('attribute_id', $attrId)
                                ->delete();
                        }
                    }
                }

                if (isset($newData['features']) && is_array($newData['features'])) {
                    $featureIds = \Modules\Properties\Entities\PropertyAttribute::where('section', 'features')->pluck('id');
                    \Modules\Properties\Entities\PropertyAttributeValue::where('property_id', $property->id)
                        ->whereIn('attribute_id', $featureIds)
                        ->delete();

                    foreach ($newData['features'] as $fId) {
                        \Modules\Properties\Entities\PropertyAttributeValue::create([
                            'property_id' => $property->id,
                            'attribute_id' => $fId,
                            'value' => '1',
                        ]);
                    }
                }

                if (isset($newData['meta_details']) || isset($newData['meta_features'])) {
                    $meta = $property->meta ?? [];
                    if (isset($newData['meta_details'])) {
                        $meta['details'] = $newData['meta_details'];
                    }
                    if (isset($newData['meta_features'])) {
                        $meta['features'] = $newData['meta_features'];
                    }
                    $property->meta = $meta;
                }

                $property->save();
            }
        }

        $revision->update([
            'status' => 'approved',
            'reviewer_id' => $reviewer->id,
            'reviewed_at' => now(),
            'notes' => 'تأیید شد توسط ' . $reviewer->name,
        ]);

        if ($property) {
            $property->update([
                'approval_status' => 'approved',
                'publication_status' => 'published',
                'rejection_reason' => null,
            ]);
        }
    }

    /**
     * رد نسخه ویرایش با ذکر دلیل
     */
    public function rejectRevision(PropertyRevision $revision, User $reviewer, string $reason): void
    {
        $property = $revision->property;

        // در صورت رد ویرایش، تغییرات جدید در صف کنار گذاشته شده و نسخه زنده فعلی دست‌نخورده باقی می‌ماند
        $revision->update([
            'status' => 'rejected',
            'reviewer_id' => $reviewer->id,
            'reviewed_at' => now(),
            'notes' => $reason,
        ]);

        if ($property) {
            $property->update([
                'approval_status' => 'rejected',
                'rejection_reason' => $reason,
            ]);
        }
    }

    /**
     * بازگشایی امن و بازگشتی مقادیر آرایه‌ای که ممکن است به صورت JSON چندلایه ذخیره شده باشند
     */
    private function unwrapArray(mixed $value): mixed
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $current = trim($value);
            while (is_string($current)) {
                $decoded = json_decode($current, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $current = $decoded;
                } else {
                    break;
                }
            }

            if (is_array($current)) {
                return $current;
            }
        }

        return $value;
    }
}
