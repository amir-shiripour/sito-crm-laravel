<?php

namespace Modules\Clients\App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Clients\App\Services\ClientFormService;
use Modules\Clients\Entities\ClientSetting;

class ClientProfileController extends Controller
{
    protected $clientFormService;

    public function __construct(ClientFormService $clientFormService)
    {
        $this->clientFormService = $clientFormService;
    }

    /**
     * Display the client's profile editing form.
     */
    public function show()
    {
        $client = auth('client')->user();
        $formFields = $this->clientFormService->getFormFields();

        // 1. منطق استراتژی نام کاربری برای قفل کردن فیلدها
        $usernameStrategy = ClientSetting::getValue('username.strategy') ?? ClientSetting::getValue('username_strategy', 'email_local');

        $lockedFields = [];
        if ($usernameStrategy === 'email' || $usernameStrategy === 'email_local') {
            $lockedFields[] = 'email';
        } elseif ($usernameStrategy === 'mobile') {
            $lockedFields[] = 'phone';
        } elseif ($usernameStrategy === 'national_code') {
            $lockedFields[] = 'national_code';
        }

        // 2. منطق بررسی اینکه آیا شماره تماس به صورت موقت قابل ویرایش است (نیاز به OTP دارد)
        $authMode = ClientSetting::getValue('auth.mode', 'password');
        $smsModuleAvailable = class_exists(\Modules\Sms\Entities\SmsOtp::class);

        if (in_array($authMode, ['otp', 'both']) && $smsModuleAvailable && !in_array('phone', $lockedFields)) {
            $lockedFields[] = 'phone';
            $phoneRequiresOtp = true;
        } else {
            $phoneRequiresOtp = false;
        }

        // 3. گروه‌بندی فیلدها برای نمایش در تب‌ها
        $groupedFields = [];
        $unauthFields = [];

        foreach ($formFields as $field) {
            // حذف فیلدهای وضعیت و رمز عبور از فرم پروفایل عمومی
            if (in_array($field['id'], ['password', 'status_id'])) continue;

            // اگر فیلدی از نوع status هست هم نادیده بگیر
            if (($field['type'] ?? '') === 'status') continue;

            // بررسی احراز هویت کاربری (client_auth)
            $isClientAuth = $field['client_auth'] ?? false;

            if (!$isClientAuth) {
                // اگر فیلد دارای تیک احراز هویت نیست، قفل می‌شود و الزامی بودن آن برداشته می‌شود
                $lockedFields[] = $field['id'];
                $unauthFields[] = $field['id'];
                $field['required'] = false;
            }

            $groupName = !empty($field['group']) ? $field['group'] : 'اطلاعات کاربری';
            if (!isset($groupedFields[$groupName])) {
                $groupedFields[$groupName] = [];
            }
            $groupedFields[$groupName][] = $field;
        }

        return view('clients::portal.profile.edit', compact('client', 'groupedFields', 'lockedFields', 'unauthFields', 'phoneRequiresOtp'));
    }

    /**
     * Update the client's profile information.
     */
    public function update(Request $request)
    {
        $client = auth('client')->user();

        // 1. بررسی استراتژی نام کاربری و قفل بودن فیلدها
        $usernameStrategy = ClientSetting::getValue('username.strategy') ?? ClientSetting::getValue('username_strategy', 'email_local');
        $lockedFields = [];
        if ($usernameStrategy === 'email' || $usernameStrategy === 'email_local') {
            $lockedFields[] = 'email';
        } elseif ($usernameStrategy === 'mobile') {
            $lockedFields[] = 'phone';
        } elseif ($usernameStrategy === 'national_code') {
            $lockedFields[] = 'national_code';
        }

        $authMode = ClientSetting::getValue('auth.mode', 'password');
        $smsModuleAvailable = class_exists(\Modules\Sms\Entities\SmsOtp::class);
        if (in_array($authMode, ['otp', 'both']) && $smsModuleAvailable && !in_array('phone', $lockedFields)) {
            $lockedFields[] = 'phone';
        }

        // Get validation rules
        $rules = $this->clientFormService->getValidationRules(true, $client->id);

        // فیلتر کردن فیلدهای قفل شده به دلیل عدم دسترسی client_auth
        $formFields = $this->clientFormService->getFormFields();
        foreach ($formFields as $field) {
            $isClientAuth = $field['client_auth'] ?? false;
            if (!$isClientAuth && !in_array($field['id'], $lockedFields)) {
                $lockedFields[] = $field['id'];
            }
        }

        // حذف رول‌های فیلدهای قفل شده و فیلدهایی که نباید توسط کاربر تغییر کنند
        $ignoredFields = array_merge($lockedFields, ['status_id']);
        foreach ($ignoredFields as $ignoredField) {
            if (isset($rules[$ignoredField])) {
                unset($rules[$ignoredField]);
            }
        }

        $customRules = [
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'current_password' => ['nullable', 'required_with:password', function ($attribute, $value, $fail) use ($client) {
                if (!Hash::check($value, $client->password)) {
                    $fail('رمز عبور فعلی صحیح نیست.');
                }
            }],
        ];

        foreach ($customRules as $field => $fieldRules) {
            if (isset($rules[$field])) {
                $rules[$field] = array_merge((array)$rules[$field], (array)$fieldRules);
            } else {
                $rules[$field] = $fieldRules;
            }
        }

        $validatedData = $request->validate($rules);

        // جلوگیری از تغییر فیلدهای قفل شده و غیر مجاز
        foreach ($ignoredFields as $ignoredField) {
            if (array_key_exists($ignoredField, $validatedData)) {
                unset($validatedData[$ignoredField]);
            }
        }

        if (empty($validatedData['password'])) {
            unset($validatedData['password']);
            unset($validatedData['password_confirmation']);
        } else {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }
        unset($validatedData['current_password']);

        // Handle meta fields vs normal columns
        $clientColumns = \Illuminate\Support\Facades\Schema::getColumnListing('clients');
        $metaData = $client->meta ?? [];

        foreach ($validatedData as $key => $value) {
            // مقادیر آرایه‌ای مثل select multiple باید json شوند تا در دیتابیس به صورت رشته ذخیره شوند.
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }

            if ($key === 'password') {
                $client->password = $value;
            } elseif (in_array($key, $clientColumns)) {
                $client->{$key} = $value;
            } else {
                $metaData[$key] = $value;
            }
        }

        $client->meta = $metaData;
        $client->save();

        // Save new dynamic select options globally if configured
        $this->clientFormService->saveNewOptionsFromPayload($metaData);

        return redirect()->route('client.profile.show')->with('success', 'اطلاعات پروفایل با موفقیت به‌روزرسانی شد.');
    }
}
