<?php

namespace Modules\Services\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Settings\Entities\Setting;
use Modules\Clients\Entities\ClientForm;
use Modules\Clients\Entities\ClientSetting;

class ServicesSettingsController extends Controller
{
    private const KEYS = [
        'currency',
        'services_invoice_auto_numbering',
        'services_invoice_auto',
        'services_invoice_prefix',
        'services_invoice_middle_prefix',
        'services_invoice_suffix',
        'services_invoice_padding',
        'services_invoice_reset',
        'services_invoice_template',
        'services_proforma_invoice_prefix',
        'services_proforma_invoice_middle_prefix',
        'services_proforma_invoice_suffix',
        'services_proforma_invoice_padding',
        'services_proforma_invoice_auto',
        'services_default_tax_rate',
        'services_auto_create_invoice',
        'services_auto_renewal_invoice',
        'services_auto_send_reminders',
        'services_notify_email',
        'services_notify_sms',
        'services_notify_internal',
        'services_use_global_payment_settings',
        'services_invoice_footer_note',
        'services_print_mode',
        'services_invoice_client_fields',
        'services_tax_mode',
        'services_tax_apply_custom_fields',
        'services_official_invoice_orientation',
    ];

    private const BOOLEANS = [
        'services_invoice_auto_numbering',
        'services_invoice_auto',
        'services_proforma_invoice_auto',
        'services_auto_create_invoice',
        'services_auto_renewal_invoice',
        'services_auto_send_reminders',
        'services_notify_email',
        'services_notify_sms',
        'services_notify_internal',
        'services_use_global_payment_settings',
        'services_tax_apply_custom_fields',
    ];

    private const DEFAULT_SELECTED_CLIENT_FIELDS = [
        'full_name',
        'phone',
        'email',
        'national_code',
        'case_number',
    ];

    public function index()
    {
        $this->authorize('services.settings.manage');
        $raw = Setting::whereIn('key', self::KEYS)->pluck('value', 'key')->toArray();

        $clientFormFields = $this->availableClientFields();

        // خواندن فیلدهای ذخیره شده در دیتابیس
        $savedClientFields = json_decode($raw['services_invoice_client_fields'] ?? '[]', true);
        if (!is_array($savedClientFields)) {
            $savedClientFields = [];
        }

        // اگر تنظیمات هنوز در دیتابیس ثبت نشده، فیلدهای دیفالت را تیک می‌زنیم
        if (!array_key_exists('services_invoice_client_fields', $raw)) {
            $selectedClientFields = self::DEFAULT_SELECTED_CLIENT_FIELDS;
        } else {
            // اگر قبلاً ذخیره شده اما فیلدهای سیستمی فراموش شده‌اند، آنها را اضافه می‌کنیم
            $selectedClientFields = array_unique(array_merge($savedClientFields, self::DEFAULT_SELECTED_CLIENT_FIELDS));
        }

        return view('services::settings.index', compact('raw', 'clientFormFields', 'selectedClientFields'));
    }

    protected function availableClientFields(): array
    {
        $excludedTypes = ['password', 'file', 'profile-photo'];
        $excludedIds   = ['password'];

        $systemFields = collect(ClientForm::systemFieldDefaults())
            ->reject(function ($f, $id) use ($excludedIds, $excludedTypes) {
                return in_array($id, $excludedIds, true)
                    || in_array($f['type'] ?? '', $excludedTypes, true);
            })
            ->map(function ($f, $id) {
                return [
                    'id'        => $id,
                    'label'     => $f['label'] ?? $id,
                    'group'     => $f['group'] ?? 'اطلاعات هویتی',
                    'is_system' => true,
                ];
            })
            ->values();

        $customFields = collect([]);
        $form = ClientForm::active(ClientSetting::getValue('default_form_key'));
        if ($form) {
            $customFields = collect($form->schema['fields'] ?? [])
                ->reject(function ($f) use ($excludedIds, $excludedTypes) {
                    $id = $f['id'] ?? '';
                    return in_array($id, $excludedIds, true)
                        || in_array($f['type'] ?? '', $excludedTypes, true)
                        || !empty($f['is_system']);
                })
                ->map(function ($f) {
                    return [
                        'id'        => $f['id'],
                        'label'     => $f['label'] ?? $f['id'],
                        'group'     => $f['group'] ?? 'سایر',
                        'is_system' => false,
                    ];
                })
                ->values();
        }

        return $systemFields->merge($customFields)->all();
    }

    public function update(Request $request)
    {
        $this->authorize('services.settings.manage');

        $request->validate([
            'currency' => 'required|in:toman,rial',
            'services_invoice_prefix' => 'nullable|string|max:20',
            'services_invoice_middle_prefix' => 'nullable|string|max:20',
            'services_invoice_suffix' => 'nullable|string|max:20',
            'services_invoice_padding' => 'nullable|integer|min:1|max:10',
            'services_invoice_reset' => 'nullable|in:never,yearly,monthly',
            'services_invoice_template' => 'nullable|in:standard,official',
            'services_proforma_invoice_prefix' => 'nullable|string|max:20',
            'services_proforma_invoice_middle_prefix' => 'nullable|string|max:20',
            'services_proforma_invoice_suffix' => 'nullable|string|max:20',
            'services_proforma_invoice_padding' => 'nullable|integer|min:1|max:10',
            'services_default_tax_rate' => 'nullable|numeric|min:0|max:100',
            'services_invoice_footer_note' => 'nullable|string|max:1000',
            'services_print_mode' => 'required|in:standard,official',
            'services_invoice_client_fields' => 'nullable|array',
            'services_invoice_client_fields.*' => 'string|max:191',
            'services_tax_mode' => 'required|in:invoice,item',
            'services_official_invoice_orientation' => 'nullable|in:portrait,landscape',
        ]);

        foreach (self::KEYS as $key) {
            if ($key === 'services_invoice_client_fields') {
                $value = json_encode(
                    array_values($request->input('services_invoice_client_fields', [])),
                    JSON_UNESCAPED_UNICODE
                );
            } else {
                $value = in_array($key, self::BOOLEANS)
                    ? ($request->boolean($key) ? '1' : '0')
                    : $request->input($key, '');
            }

            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'تنظیمات ذخیره شد.')->with('active_tab', $request->input('active_tab'));
    }

    public function previewNumber(Request $request)
    {
        $prefix = $request->input('prefix', 'SRV-');
        $middle = $request->input('middle', now()->format('Y'));
        $suffix = $request->input('suffix', '');
        $padding = max(1, (int)$request->input('padding', 4));

        return response()->json([
            'preview' => $prefix . $middle . '-' . str_pad(1, $padding, '0', STR_PAD_LEFT) . $suffix,
        ]);
    }

    public function seedWorkflows()
    {
        $this->authorize('services.settings.manage');

        try {
            \Illuminate\Support\Facades\Artisan::call('services:seed-workflows');
            return back()->with('success', 'گردش کارهای پیش‌فرض با موفقیت نصب/بروزرسانی شدند.')->with('active_tab', 'automation');
        } catch (\Exception $e) {
            return back()->with('error', 'خطا در نصب گردش کارها: ' . $e->getMessage())->with('active_tab', 'automation');
        }
    }
}
