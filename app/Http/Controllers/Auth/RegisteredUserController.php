<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RegistrationRequest;
use App\Models\CustomUserField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;
use Modules\Settings\Entities\Setting;

class RegisteredUserController extends Controller
{
    private function getRegistrationSettings()
    {
        $setting = Setting::where('key', 'registration')->first();
        if ($setting && $setting->value) {
            return json_decode($setting->value, true) ?: [];
        }
        return [];
    }

    /**
     * Display the registration view.
     */
    public function create(Request $request)
    {
        $settings = $this->getRegistrationSettings();

        // پیدا کردن نقش‌هایی که ثبت‌نام برای آنها فعال است
        $enabledRoles = collect($settings)->filter(function($setting) {
            return isset($setting['enabled']) && $setting['enabled'] == '1';
        })->keys();

        $roles = Role::whereIn('id', $enabledRoles)->get();

        if ($roles->isEmpty()) {
            abort(404, 'Registration is currently disabled.');
        }

        // اگر نقشی در URL مشخص شده باشد و ثبت‌نام برای آن فعال باشد
        $selectedRole = null;
        if ($request->has('role') && $roles->contains('name', $request->role)) {
            $selectedRole = Role::where('name', $request->role)->first();
        } elseif ($roles->count() == 1) {
            $selectedRole = $roles->first();
        }

        $customFields = collect();
        if ($selectedRole) {
            $customFields = CustomUserField::where('role_name', $selectedRole->name)->get();
        }

        return view('auth.register', compact('roles', 'selectedRole', 'customFields'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $settings = $this->getRegistrationSettings();

        $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                'unique:'.User::class,
                // ولیدیشن کاستوم: اگر درخواست فعالی دارد اخطار بده
                function ($attribute, $value, $fail) {
                    $exists = RegistrationRequest::where('email', $value)->where('status', 'pending')->exists();
                    if ($exists) {
                        $fail('شما قبلاً با این ایمیل یک درخواست ثبت‌نام ارسال کرده‌اید که در انتظار بررسی است.');
                    }
                }
            ],
            'mobile' => [
                'nullable', 'string', 'max:20',
                'unique:'.User::class,
                // ولیدیشن کاستوم: بررسی تکراری بودن موبایل در درخواست‌های در انتظار بررسی
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        $exists = RegistrationRequest::where('mobile', $value)->where('status', 'pending')->exists();
                        if ($exists) {
                            $fail('شما قبلاً با این شماره موبایل یک درخواست ثبت‌نام ارسال کرده‌اید که در انتظار بررسی است.');
                        }
                    }
                }
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            // ترجمه خطاهای متداول مستقیما در کنترلر برای جلوگیری از مشکل زبان سیستم
            'email.unique' => 'این ایمیل قبلاً در سیستم ثبت شده است و امکان ثبت‌نام مجدد با آن وجود ندارد.',
            'mobile.unique' => 'این شماره موبایل قبلاً در سیستم ثبت شده است.',
            'email.required' => 'وارد کردن ایمیل الزامی است.',
            'email.email' => 'فرمت ایمیل نامعتبر است.',
            'name.required' => 'نام و نام خانوادگی خود را وارد کنید.',
            'password.required' => 'رمز عبور الزامی است.',
            'password.confirmed' => 'رمز عبور و تکرار آن با هم مطابقت ندارند.',
        ]);

        $role = Role::where('name', $request->role)->first();

        // بررسی اینکه آیا ثبت‌نام برای این نقش فعال است
        if (!isset($settings[$role->id]) || $settings[$role->id]['enabled'] != '1') {
            abort(403, 'Registration is disabled for this role.');
        }

        // اعتبارسنجی فیلدهای سفارشی
        $customFields = CustomUserField::where('role_name', $role->name)->get();
        $customFieldRules = [];
        $customFieldAttributes = []; // آرایه برای پاس دادن لیبل فارسی فیلدها
        $customFieldMessages = []; // آرایه برای پیام‌های خطای فیلدهای سفارشی

        foreach ($customFields as $field) {
            if ($field->is_required) {
                $customFieldRules['custom_fields.'.$field->field_name] = ['required'];
                // ایجاد پیام فارسی داینامیک با استفاده از لیبل همان فیلد
                $fieldLabel = $field->label ?? $field->field_name;
                $customFieldMessages['custom_fields.'.$field->field_name.'.required'] = "تکمیل فیلد «{$fieldLabel}» الزامی است.";
            } else {
                $customFieldRules['custom_fields.'.$field->field_name] = ['nullable'];
            }

            // اگر رول خاصی برای فیلد تعریف شده است (مثلا mimes:jpg,png)، آن را اضافه کنید
            if ($field->rules) {
                $customFieldRules['custom_fields.'.$field->field_name] = array_merge($customFieldRules['custom_fields.'.$field->field_name], $field->rules);
            }

            // اختصاص نام فارسی فیلد به اتریبیوت لاراول برای نمایش در ارورهای استاندارد
            $customFieldAttributes['custom_fields.'.$field->field_name] = $field->label ?? $field->field_name;
        }

        if (!empty($customFieldRules)) {
            // پاس دادن پیام‌ها و اتریبیوت‌ها به صورت دستی برای جلوگیری از مشکل زبان سیستم
            $request->validate($customFieldRules, $customFieldMessages, $customFieldAttributes);
        }

        // =========================================================================
        // پردازش فیلدهای سفارشی و آپلود فایل‌ها
        // =========================================================================
        $processedCustomFields = [];
        if ($request->has('custom_fields')) {
            foreach ($request->custom_fields as $fieldName => $value) {
                // اگر مقدار ارسال شده یک فایل آپلودی باشد
                if ($request->hasFile("custom_fields.$fieldName")) {
                    // ذخیره فایل در پوشه storage/app/public/uploads/custom_fields
                    $filePath = $request->file("custom_fields.$fieldName")->store('uploads/custom_fields', 'public');
                    $processedCustomFields[$fieldName] = $filePath;
                } else {
                    // اگر متن ساده، عدد یا آرایه باشد
                    $processedCustomFields[$fieldName] = $value;
                }
            }
        }

        $approvalType = $settings[$role->id]['approval'] ?? 'manual';

        if ($approvalType === 'automatic') {
            // ثبت‌نام مستقیم
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole($role);

            // ذخیره مقادیر فیلدهای سفارشی (از آرایه پردازش شده استفاده میکنیم)
            if (!empty($processedCustomFields)) {
                foreach ($processedCustomFields as $fieldName => $value) {
                    $user->customValues()->create([
                        'field_name' => $fieldName,
                        'field_value' => is_array($value) ? json_encode($value) : $value,
                    ]);
                }
            }

            Auth::login($user);
            return redirect('/dashboard')->with('success', 'ثبت‌نام شما با موفقیت انجام شد.');
        } else {
            // ثبت درخواست برای تایید دستی (بررسی همزمان ایمیل و موبایل)
            $existingRequest = RegistrationRequest::where(function ($query) use ($request) {
                $query->where('email', $request->email);
                if ($request->filled('mobile')) {
                    $query->orWhere('mobile', $request->mobile);
                }
            })->first();

            // برای جلوگیری از ارور دیتابیس (SQL 1062) اگر رکوردی از قبل رد شده بود، همان را آپدیت می‌کنیم
            if ($existingRequest) {
                $existingRequest->update([
                    'role_id' => $role->id,
                    'name' => $request->name,
                    'mobile' => $request->mobile,
                    'password' => Hash::make($request->password),
                    'custom_fields' => $processedCustomFields, // استفاده از دیتای حاوی مسیر فایل
                    'status' => 'pending',
                    'rejection_reason' => null, // ریست کردن دلیل رد قبلی
                ]);
            } else {
                RegistrationRequest::create([
                    'role_id' => $role->id,
                    'name' => $request->name,
                    'email' => $request->email,
                    'mobile' => $request->mobile,
                    'password' => Hash::make($request->password),
                    'custom_fields' => $processedCustomFields, // استفاده از دیتای حاوی مسیر فایل
                    'status' => 'pending',
                ]);
            }

            // پیام فارسی به کاربر نمایش داده می‌شود (متن‌های Flash Session نیازی به فایل زبان ندارند مگر اینکه چندزبانه باشید)
            return redirect('/login')->with('status', 'درخواست ثبت‌نام شما با موفقیت ارسال شد و در انتظار بررسی مدیریت است.');
        }
    }
}
