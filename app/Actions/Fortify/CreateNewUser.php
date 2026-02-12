<?php

namespace App\Actions\Fortify;

use App\Models\CustomUserField;
use App\Models\User;
use App\Models\UserCustomValue;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'mobile' => ['nullable', 'string', 'max:20', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        // ولیدیشن داینامیک براساس نقش انتخاب شده
        $roleName = $input['role'];
        $fields = CustomUserField::where('role_name', $roleName)->get();

        $dynamicRules = [];
        foreach ($fields as $f) {
            $key = "custom.{$f->field_name}";
            $rulz = [];
            if ($f->is_required) $rulz[] = 'required';
            // حداقل قواعد پایه براساس نوع
            $rulz[] = match ($f->field_type) {
                'number' => 'numeric',
                'date'   => 'date',
                'email'  => 'email',
                default  => 'string',
            };
            // قواعد اضافه از ستون rules
            if (!empty($f->rules)) $rulz = array_merge($rulz, $f->rules);
            $dynamicRules[$key] = $rulz;
        }
        Validator::make($input, $dynamicRules)->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'mobile' => $input['mobile'] ?? null,
            'password' => bcrypt($input['password']),
        ]);

        // انتساب نقش (Spatie)
        if (method_exists($user, 'assignRole')) {
            $user->assignRole($roleName);
        }

        // ذخیره مقادیر فیلدهای سفارشی
        foreach ($fields as $f) {
            UserCustomValue::updateOrCreate(
                ['user_id' => $user->id, 'field_name' => $f->field_name],
                ['value' => $input['custom'][$f->field_name] ?? null],
            );
        }
        return $user;
    }
}
