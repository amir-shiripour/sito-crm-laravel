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
        if (empty($dirtyAttributes) && empty($additionalMeta['gallery_added']) && empty($additionalMeta['gallery_removed'])) {
            return null;
        }

        $oldData = [];
        $newData = [];
        $summary = [];

        $labels = [
            'title' => 'عنوان اقامتگاه',
            'description' => 'توضیحات اقامتگاه',
            'property_type' => 'نوع ملک',
            'address' => 'آدرس اقامتگاه',
            'area' => 'متراژ اقامتگاه (متر مربع)',
            'latitude' => 'موقعیت نقشه (عرض جغرافیایی)',
            'longitude' => 'موقعیت نقشه (طول جغرافیایی)',
            'cover_image' => 'تصویر کاور اقامتگاه',
            'video' => 'ویدیو اقامتگاه',
            'category_id' => 'دسته‌بندی',
        ];

        foreach ($dirtyAttributes as $key => $newValue) {
            // فیلدهای سیستمی را نادیده بگیریم
            if (in_array($key, ['updated_at', 'created_at', 'id', 'approval_status', 'rejection_reason'])) {
                continue;
            }

            $oldValue = $property->getOriginal($key);

            $oldData[$key] = $oldValue;
            $newData[$key] = $newValue;

            $label = $labels[$key] ?? $key;
            $type = 'text';

            if ($key === 'cover_image') {
                $type = 'image';
            } elseif ($key === 'area') {
                $oldValue = $oldValue ? (rtrim(rtrim((string)$oldValue, '0'), '.') . ' متر') : '—';
                $newValue = $newValue ? (rtrim(rtrim((string)$newValue, '0'), '.') . ' متر') : '—';
            } elseif ($key === 'property_type') {
                $typesMap = [
                    'apartment' => 'خانه و آپارتمان',
                    'villa' => 'ویلا و باغچه',
                    'land' => 'زمین و کلنگی',
                    'office' => 'اداری و تجاری',
                ];
                $oldValue = $typesMap[$oldValue] ?? $oldValue;
                $newValue = $typesMap[$newValue] ?? $newValue;
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
    public function recordRentalConfigChanges(Property $property, PropertyRentalConfig $config, array $dirtyConfig, User $user): ?PropertyRevision
    {
        if (empty($dirtyConfig)) {
            return null;
        }

        $currency = PropertySetting::get('currency', 'toman') === 'toman' ? 'تومان' : 'ریال';

        $labels = [
            'base_price_per_night' => 'قیمت هر شب وسط هفته',
            'weekend_price_per_night' => 'قیمت هر شب آخر هفته',
            'peak_price_per_night' => 'قیمت ایام پیک و تعطیلات',
            'base_guests' => 'ظرفیت مهمان پایه',
            'max_guests' => 'حداکثر ظرفیت مهمان',
            'extra_guest_fee' => 'هزینه هر نفر اضافه',
            'cleaning_fee' => 'هزینه نظافت',
            'min_stay_nights' => 'حداقل مدت اقامت',
            'checkin_time' => 'ساعت تحویل (ورود)',
            'checkout_time' => 'ساعت تخلیه (خروج)',
            'cancellation_policy' => 'مقررات لغو رزرو',
            'house_rules' => 'قوانین اقامتگاه',
            'amenities' => 'امکانات اقامتگاه',
        ];

        $oldData = [];
        $newData = [];
        $summary = [];

        foreach ($dirtyConfig as $key => $newValue) {
            if (in_array($key, ['updated_at', 'created_at', 'id', 'property_id'])) {
                continue;
            }

            $oldValue = $config->getOriginal($key);

            $oldData[$key] = $oldValue;
            $newData[$key] = $newValue;

            $label = $labels[$key] ?? $key;

            // فرمت قیمت‌ها و اعداد
            if (in_array($key, ['base_price_per_night', 'weekend_price_per_night', 'peak_price_per_night', 'extra_guest_fee', 'cleaning_fee'])) {
                $oldFormatted = $oldValue ? (number_format((float)$oldValue) . ' ' . $currency) : '—';
                $newFormatted = $newValue ? (number_format((float)$newValue) . ' ' . $currency) : '—';
            } elseif (in_array($key, ['base_guests', 'max_guests'])) {
                $oldFormatted = $oldValue ? ($oldValue . ' نفر') : '—';
                $newFormatted = $newValue ? ($newValue . ' نفر') : '—';
            } elseif ($key === 'min_stay_nights') {
                $oldFormatted = $oldValue ? ($oldValue . ' شب') : '—';
                $newFormatted = $newValue ? ($newValue . ' شب') : '—';
            } elseif (in_array($key, ['checkin_time', 'checkout_time'])) {
                $oldFormatted = $oldValue ? substr((string)$oldValue, 0, 5) : '—';
                $newFormatted = $newValue ? substr((string)$newValue, 0, 5) : '—';
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
     * تایید نسخه ویرایش و انتشار ملک
     */
    public function approveRevision(PropertyRevision $revision, User $reviewer): void
    {
        $revision->update([
            'status' => 'approved',
            'reviewer_id' => $reviewer->id,
            'reviewed_at' => now(),
            'notes' => 'تأیید شد توسط ' . $reviewer->name,
        ]);

        $revision->property()->update([
            'approval_status' => 'approved',
            'publication_status' => 'published',
            'rejection_reason' => null,
        ]);
    }

    /**
     * رد نسخه ویرایش با ذکر دلیل
     */
    public function rejectRevision(PropertyRevision $revision, User $reviewer, string $reason): void
    {
        $revision->update([
            'status' => 'rejected',
            'reviewer_id' => $reviewer->id,
            'reviewed_at' => now(),
            'notes' => $reason,
        ]);

        $revision->property()->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }
}
