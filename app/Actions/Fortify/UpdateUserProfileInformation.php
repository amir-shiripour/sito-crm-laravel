<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\UserCustomValue;
use App\Models\CustomUserField;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'mobile' => [
                'nullable',
                'string',
                'max:30', // همگام با سایز دیتابیس
                Rule::unique('users')->ignore($user->id),
            ],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ])->validateWithBag('updateProfileInformation');

        // Dynamic validation and saving for custom fields
        $roleNames = $user->roles->pluck('name')->toArray();
        $fields = CustomUserField::whereIn('role_name', $roleNames)->orderBy('id')->get()->unique('field_name');

        $customRules = [];
        foreach ($fields as $f) {
            $key = 'custom.' . $f->field_name;
            $type = strtolower($f->field_type ?? 'text');
            $meta = is_array($f->meta ?? null) ? $f->meta : (is_string($f->meta ?? null) ? json_decode($f->meta, true) : []);
            $meta = $meta ?: [];

            $base = ($f->is_required ?? false) ? ['required'] : ['nullable'];

            switch ($type) {
                case 'number': $base[] = 'numeric'; break;
                case 'date': $base[] = 'date'; break;
                case 'email': $base[] = 'email'; break;
                case 'file':
                    $base[] = 'file';
                    // Since it's a Livewire form, files might be passed differently,
                    // but we apply rules for safety. Livewire handles file uploads via temporary URLs.
                    break;
                case 'checkbox':
                    $base[] = 'array';
                    if (!empty($meta['options']) && is_array($meta['options'])) {
                        $base['custom.' . $f->field_name . '.*'] = ['in:' . implode(',', array_values($meta['options']))];
                    }
                    break;
                case 'select':
                case 'radio':
                    if (!empty($meta['options']) && is_array($meta['options'])) {
                        $base[] = 'in:' . implode(',', array_values($meta['options']));
                    } else {
                        $base[] = 'string';
                    }
                    break;
                default:
                    $base[] = 'string';
            }
            $customRules[$key] = $base;
        }

        if (!empty($customRules)) {
            Validator::make($input, $customRules)->validateWithBag('updateProfileInformation');
        }

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
                'mobile' => $input['mobile'] ?? null,
            ])->save();
        }

        // Save Custom Fields
        if (isset($input['custom']) && is_array($input['custom'])) {
            foreach ($fields as $f) {
                $type = strtolower($f->field_type ?? 'text');

                // در Livewire چون فایل‌ها به صورت مستقیم به این آرایه نمی‌آیند،
                // فعلا مدیریت آپلود فایل فیلد کاستوم در صفحه پروفایل را نادیده می‌گیریم یا فقط حالت متنی را ذخیره می‌کنیم
                if ($type === 'file') {
                    continue;
                }

                if (array_key_exists($f->field_name, $input['custom'])) {
                    $raw = $input['custom'][$f->field_name];
                    if ($type === 'checkbox' && is_array($raw)) {
                        $value = json_encode(array_values($raw), JSON_UNESCAPED_UNICODE);
                    } else {
                        $value = $raw;
                    }

                    UserCustomValue::updateOrCreate(
                        ['user_id' => $user->id, 'field_name' => $f->field_name],
                        ['value' => $value],
                    );
                }
            }
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
            'mobile' => $input['mobile'] ?? null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
