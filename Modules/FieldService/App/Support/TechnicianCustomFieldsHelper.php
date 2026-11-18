<?php

namespace Modules\FieldService\App\Support;

use App\Helpers\ProvinceCity;
use App\Models\CustomUserField;
use App\Models\User;
use App\Models\UserCustomValue;
use Illuminate\Support\Collection;
use Modules\FieldService\App\Enums\TechnicianStatus;
use Modules\FieldService\App\Models\Technician;

class TechnicianCustomFieldsHelper
{
    /**
     * Determine whether the user is a standalone technician (not an admin/operator).
     */
    public static function isTechnicianOnly(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->hasAnyRole(['super-admin', 'admin', 'Super Admin', 'مدیر سیستم', 'مدیر کل']) || $user->can('fieldservice.manage')) {
            return false;
        }

        return $user->hasRole('technician');
    }

    /**
     * Determine whether a technician still needs to complete their profile/verification in /user/profile.
     */
    public static function needsVerification(?User $user, ?Technician $technician = null): bool
    {
        if (!self::isTechnicianOnly($user)) {
            return false;
        }

        $technician = $technician ?? Technician::where('user_id', $user->id)->first();
        if (!$technician) {
            return false;
        }

        // If already submitted verification or already active, no longer restricted
        if (!empty($technician->meta['verification_submitted']) || $technician->status === TechnicianStatus::ACTIVE) {
            return false;
        }

        $profileFields = self::getFields('profile');
        $existing = self::loadExistingValues($user, $technician, $profileFields);

        foreach ($profileFields as $field) {
            if ($field->is_required) {
                $val = $existing[$field->field_name] ?? null;
                if ($val === null || $val === '' || (is_array($val) && empty($val))) {
                    return true;
                }
            }
        }

        if ($technician->skills()->count() === 0) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether a technician-only user is still unapproved (not ACTIVE)
     * and therefore must remain restricted to /user/profile.
     */
    public static function isRestrictedUnapprovedTechnician(?User $user, ?Technician $technician = null): bool
    {
        if (!self::isTechnicianOnly($user)) {
            return false;
        }

        $technician = $technician ?? Technician::where('user_id', $user->id)->first();
        if (!$technician) {
            return true;
        }

        if ($technician->status !== TechnicianStatus::ACTIVE) {
            return true;
        }

        // Also restrict if technician has an active (unsigned) contract awaiting signature
        return $technician->hasPendingContract();
    }

    /**
     * Fetch CustomUserField definitions for the 'technician' role by scope ('register' | 'profile').
     */
    public static function getFields(string $scope): Collection
    {
        $query = CustomUserField::where('role_name', 'technician')->orderBy('id');

        if ($scope === 'register') {
            $query->where('show_in_register', true);
        } elseif ($scope === 'profile') {
            $query->where('show_in_profile', true);
        }

        return $query->get();
    }

    /**
     * Load existing custom field values for a user/technician, keyed by field_name.
     */
    public static function loadExistingValues(?User $user, ?Technician $technician, Collection $fields): array
    {
        $values = [];
        $stored = $user
            ? UserCustomValue::where('user_id', $user->id)->get()->keyBy('field_name')
            : collect();

        foreach ($fields as $field) {
            $name = $field->field_name;
            $type = strtolower($field->field_type ?? 'text');
            $val = null;

            if ($stored->has($name) && $stored->get($name)->value !== null && $stored->get($name)->value !== '') {
                $raw = $stored->get($name)->value;
                if ($type === 'checkbox') {
                    $decoded = json_decode($raw, true);
                    $val = is_array($decoded) ? $decoded : (bool) $raw;
                } else {
                    $val = $raw;
                }
            } elseif ($technician && ($technician->status === TechnicianStatus::ACTIVE || !empty($technician->meta['verification_submitted']))) {
                $val = match ($name) {
                    'national_code'       => $technician->national_code,
                    'shaba_number'        => $technician->shaba_number ? ltrim((string) $technician->shaba_number, 'IRir') : '',
                    'province'            => $technician->province,
                    'city'                => $technician->city,
                    'address'             => $technician->address,
                    'national_card_image' => $technician->meta['national_card_image'] ?? null,
                    default               => $technician->meta['custom_fields'][$name] ?? null,
                };
            }

            if ($name === 'shaba_number' && is_string($val)) {
                $val = preg_replace('/^IR/i', '', $val);
            }

            if ($val === null) {
                $val = ($type === 'checkbox' && !empty(self::extractOptions($field))) ? [] : '';
            }

            $values[$name] = $val;
        }

        return $values;
    }

    /**
     * Extract selectable options from field meta if available.
     */
    public static function extractOptions(CustomUserField $field): array
    {
        $meta = is_array($field->meta ?? null)
            ? $field->meta
            : (is_string($field->meta ?? null) ? json_decode($field->meta, true) : []);

        $options = $meta['options'] ?? [];
        return is_array($options) ? array_values($options) : [];
    }

    /**
     * Build Livewire validation rules and Persian error messages for dynamic fields.
     */
    public static function buildValidation(
        Collection $fields,
        array $existingFiles = [],
        bool $onlyNonFile = false
    ): array {
        $rules = [];
        $messages = [];

        foreach ($fields as $field) {
            $name = $field->field_name;
            $type = strtolower($field->field_type ?? 'text');
            $label = $field->label ?: $name;
            $isRequired = (bool) $field->is_required;

            if ($onlyNonFile && $type === 'file') {
                continue;
            }

            if ($type === 'file') {
                $key = "customFiles.{$name}";
                $hasExisting = !empty($existingFiles[$name]);
                $fieldRules = [($isRequired && !$hasExisting) ? 'required' : 'nullable', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:5120'];

                $rules[$key] = $fieldRules;
                $messages["{$key}.required"] = "بارگذاری فایل «{$label}» الزامی است.";
                $messages["{$key}.file"] = "فایل انتخابی برای «{$label}» معتبر نیست.";
                $messages["{$key}.mimes"] = "فرمت فایل «{$label}» باید JPG، PNG، WEBP یا PDF باشد.";
                $messages["{$key}.max"] = "حجم فایل «{$label}» حداکثر می‌تواند ۵ مگابایت باشد.";
                continue;
            }

            $key = "customFields.{$name}";
            $fieldRules = [$isRequired ? 'required' : 'nullable'];

            if ($name === 'national_code') {
                $fieldRules[] = 'string';
                $fieldRules[] = 'size:10';
                $messages["{$key}.size"] = "فیلد «{$label}» باید دقیقاً ۱۰ رقم باشد.";
            } elseif ($name === 'shaba_number') {
                $fieldRules[] = 'string';
                $fieldRules[] = 'max:34';
            } else {
                switch ($type) {
                    case 'number':
                        $fieldRules[] = 'numeric';
                        $messages["{$key}.numeric"] = "فیلد «{$label}» باید به صورت عددی وارد شود.";
                        break;
                    case 'email':
                        $fieldRules[] = 'email';
                        $messages["{$key}.email"] = "فرمت ایمیل در فیلد «{$label}» معتبر نیست.";
                        break;
                    case 'date':
                        $fieldRules[] = 'date';
                        $messages["{$key}.date"] = "فرمت تاریخ در فیلد «{$label}» معتبر نیست.";
                        break;
                    case 'checkbox':
                        $opts = self::extractOptions($field);
                        if (!empty($opts)) {
                            $fieldRules = [$isRequired ? 'required' : 'nullable', 'array'];
                            if ($isRequired) {
                                $fieldRules[] = 'min:1';
                            }
                        } else {
                            $fieldRules = [$isRequired ? 'accepted' : 'nullable', 'boolean'];
                            if ($isRequired) {
                                $messages["{$key}.accepted"] = "تایید گزینه «{$label}» الزامی است.";
                            }
                        }
                        break;
                    default:
                        $fieldRules[] = 'string';
                        $fieldRules[] = 'max:1000';
                }
            }

            $rules[$key] = $fieldRules;
            $messages["{$key}.required"] = "تکمیل فیلد «{$label}» الزامی است.";
            $messages["{$key}.min"] = "انتخاب حداقل یک مورد برای «{$label}» الزامی است.";
        }

        return ['rules' => $rules, 'messages' => $messages];
    }

    /**
     * Persist dynamic custom fields to UserCustomValue and sync Technician attributes/meta.
     */
    public static function syncValues(
        User $user,
        Technician $technician,
        Collection $fields,
        array $customFields,
        array $customFiles = []
    ): array {
        $savedValues = [];
        $techMeta = is_array($technician->meta) ? $technician->meta : [];
        $metaCustom = is_array($techMeta['custom_fields'] ?? null) ? $techMeta['custom_fields'] : [];

        foreach ($fields as $field) {
            $name = $field->field_name;
            $type = strtolower($field->field_type ?? 'text');

            if ($type === 'file') {
                $uploaded = $customFiles[$name] ?? null;
                if ($uploaded) {
                    $path = $uploaded->store('uploads/custom_fields', 'public');
                    $savedValues[$name] = $path;
                    UserCustomValue::updateOrCreate(
                        ['user_id' => $user->id, 'field_name' => $name],
                        ['value' => $path]
                    );
                    if ($name === 'national_card_image') {
                        $techMeta['national_card_image'] = $path;
                    }
                    $metaCustom[$name] = $path;
                }
                continue;
            }

            $val = $customFields[$name] ?? null;

            if ($name === 'shaba_number' && !empty($val)) {
                $clean = preg_replace('/[^0-9]/', '', (string) $val);
                $val = $clean !== '' ? ('IR' . $clean) : null;
            }

            if (is_array($val)) {
                $serialized = json_encode(array_values($val), JSON_UNESCAPED_UNICODE);
            } elseif (is_bool($val)) {
                $serialized = $val ? '1' : '0';
            } else {
                $serialized = $val !== null ? trim((string) $val) : null;
            }

            $savedValues[$name] = $val;

            if ($serialized !== null && $serialized !== '') {
                UserCustomValue::updateOrCreate(
                    ['user_id' => $user->id, 'field_name' => $name],
                    ['value' => $serialized]
                );
            } else {
                UserCustomValue::where('user_id', $user->id)
                    ->where('field_name', $name)
                    ->delete();
            }

            // Sync direct columns on Technician if applicable
            if (in_array($name, ['national_code', 'shaba_number', 'province', 'city', 'address'], true)) {
                $technician->{$name} = ($serialized !== '' ? $serialized : null);
            } else {
                $metaCustom[$name] = $val;
            }
        }

        if (!empty($metaCustom)) {
            $techMeta['custom_fields'] = $metaCustom;
        }
        $technician->meta = !empty($techMeta) ? $techMeta : $technician->meta;

        // If province/city updated, ensure coordinates are sensible if missing
        if (empty($technician->lat) || empty($technician->lng)) {
            $cities = ProvinceCity::getProvinces();
            if (!empty($technician->province) && in_array($technician->province, $cities, true)) {
                $technician->lat = 36.5444;
                $technician->lng = 52.6780;
            }
        }

        $technician->save();

        return $savedValues;
    }
}
