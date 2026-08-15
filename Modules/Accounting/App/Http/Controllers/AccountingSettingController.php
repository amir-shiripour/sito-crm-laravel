<?php

namespace Modules\Accounting\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB; // اضافه شدن کلاس دیتابیس برای دور زدن مدل
use Modules\Accounting\App\Models\AccountingSetting;
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\App\Models\FundAccount;

class AccountingSettingController extends Controller
{
    public function edit()
    {
        // خواندن داده‌ها از دیتابیس
        $settingsData = AccountingSetting::all()->pluck('value', 'key')->toArray();

        // ساخت یک کلاس مترجم و اختصاصی برای اینکه فایل blade بتواند
        // تنظیمات تو در تو (مثل general.currency) را به درستی بخواند و نمایش دهد
        $settings = new class($settingsData) {
            private $data;
            public function __construct($data) {
                $this->data = $data;
            }
            public function get($key, $default = null) {
                return Arr::get($this->data, $key, $default);
            }
        };

        $allCategories = Category::all();
        $incomeCategories = $allCategories->whereIn('type', ['income', 'revenue']);
        $expenseCategories = $allCategories->where('type', 'expense');
        $assetCategories = $allCategories->where('type', 'asset');
        $liabilityCategories = $allCategories->where('type', 'liability');
        $fundAccounts = FundAccount::all();

        // خواندن اطلاعات هویتی از ماژول تنظیمات (Settings)
        $identitySettings = [
            'name'                => get_setting('identity_name', ''),
            'national_id'         => get_setting('identity_national_id', ''),
            'economic_code'       => get_setting('identity_economic_code', ''),
            'registration_number' => get_setting('identity_registration_number', ''),
            'phone_fax'           => get_setting('identity_phone_fax', ''),
            'full_address'        => get_setting('identity_full_address', ''),
            'seal_signature'      => get_setting('identity_seal_signature', null),
        ];

        // مسیر روت تنظیمات (تب هویتی)
        $identityTabRoute = null;
        foreach (['settings.identity.index', 'settings.identity', 'settings.index'] as $candidateRoute) {
            if (\Illuminate\Support\Facades\Route::has($candidateRoute)) {
                $identityTabRoute = route($candidateRoute);
                break;
            }
        }

        return view('accounting::settings.index', compact(
            'settings',
            'incomeCategories',
            'expenseCategories',
            'assetCategories',
            'liabilityCategories',
            'fundAccounts',
            'identitySettings',
            'identityTabRoute'
        ));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method', 'remove_stamp_signature']);

        // خواندن امنِ تنظیمات ظاهری مستقیما از دیتابیس (برای جلوگیری از تداخل مدل)
        $appearanceRecord = DB::table('accounting_settings')->where('key', 'appearance')->first();
        $appearanceValue = $appearanceRecord && $appearanceRecord->value ? json_decode($appearanceRecord->value, true) : [];

        // اطمینان صد درصدی از اینکه دیتای قبلی حتماً آرایه باشد
        if (!is_array($appearanceValue)) {
            $appearanceValue = [];
        }

        // مدیریت آپلود فایل مهر و امضا
        if ($request->hasFile('appearance.stamp_signature_image_file')) {
            $oldImage = $appearanceValue['stamp_signature_image'] ?? null;
            if ($oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
            $path = $request->file('appearance.stamp_signature_image_file')->store('settings', 'public');
            Arr::set($data, 'appearance.stamp_signature_image', $path);
        }
        Arr::forget($data, 'appearance.stamp_signature_image_file');

        // مدیریت حذف تصویر
        if ($request->input('remove_stamp_signature') == '1') {
            $oldImage = $appearanceValue['stamp_signature_image'] ?? null;
            if ($oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
            Arr::set($data, 'appearance.stamp_signature_image', null);
        }

        // بررسی دسترسی سوپر ادمین برای تغییر واحد مالی
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->hasRole('super-admin') || $user->hasRole('superadmin'));
        if (!$isSuperAdmin && isset($data['general']['currency'])) {
            unset($data['general']['currency']);
        }

        if (!isset($data['banking'])) $data['banking'] = [];
        $data['banking']['allow_negative_balance'] = (bool)($data['banking']['allow_negative_balance'] ?? false);

        if (!isset($data['general'])) $data['general'] = [];
        $data['general']['check_cheque_due_dates'] = (bool)($data['general']['check_cheque_due_dates'] ?? false);

        if (!isset($data['numbering'])) $data['numbering'] = [];
        $data['numbering']['include_year'] = (bool)($data['numbering']['include_year'] ?? false);

        if (!isset($data['proforma'])) $data['proforma'] = [];
        $data['proforma']['numbering_include_year'] = (bool)($data['proforma']['numbering_include_year'] ?? false);

        if (!isset($data['tax'])) $data['tax'] = [];
        $data['tax']['enabled'] = (bool)($data['tax']['enabled'] ?? false);

        // حلقه اصلی برای ذخیره گروه‌های تنظیمات با کوئری مستقیم
        foreach ($data as $key => $value) {
            if (is_array($value)) {

                // پاکسازی آرایه‌های خالی و داینامیک
                if ($key === 'units' && isset($value['list'])) {
                    $value['list'] = array_values(array_filter($value['list']));
                }
                if ($key === 'appearance' && isset($value['custom_fields'])) {
                    $value['custom_fields'] = array_values(array_filter($value['custom_fields'], fn($field) => !empty($field['key'])));
                }

                // خواندن مستقیم دیتای قبلی از خود دیتابیس برای ترکیب کردن
                $existingRecord = DB::table('accounting_settings')->where('key', $key)->first();
                $existingValue = $existingRecord && $existingRecord->value ? json_decode($existingRecord->value, true) : [];

                if (!is_array($existingValue)) {
                    $existingValue = [];
                }

                // ترکیب عمیق (Deep Merge)
                $mergedValue = array_replace_recursive($existingValue, $value);

                // درج یا آپدیت مستقیم در جدول بدون استفاده از کلاس مدل و بدون تاریخ آپدیت
                DB::table('accounting_settings')->updateOrInsert(
                    ['key' => $key],
                    [
                        'value' => json_encode($mergedValue, JSON_UNESCAPED_UNICODE)
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }
}
