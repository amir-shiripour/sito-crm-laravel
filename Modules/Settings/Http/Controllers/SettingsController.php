<?php

namespace Modules\Settings\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Booking\Entities\BookingCategory;
use Modules\Properties\Entities\PropertyCategory;
use Modules\Properties\Entities\PropertyStatus;
use Modules\Settings\Entities\ApiKey;
use Modules\Settings\Entities\Setting;
use App\Services\GapGPTService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module as NModule;

class SettingsController extends Controller
{
    public function index()
    {
        $settingsCollection = Setting::all()->pluck('value', 'key');
        $settings = $settingsCollection->toArray(); // Convert to array

        // JSON keys that need decoding
        $jsonKeys = [
            'registration',
            'installment_types',
            'pos_devices',
            'bank_transfer_accounts',
            'active_payment_methods',
            'theme_colors',
            'installment_due_days'
        ];

        foreach ($jsonKeys as $key) {
            if (isset($settings[$key]) && is_string($settings[$key])) {
                $decoded = json_decode($settings[$key], true);
                $settings[$key] = is_array($decoded) ? $decoded : [];
            }
        }

        // Extra safety for installment_due_days
        if (!isset($settings['installment_due_days']) || !is_array($settings['installment_due_days'])) {
            $settings['installment_due_days'] = [];
        }

        $isAccountingActive = NModule::has('Accounting') && NModule::isEnabled('Accounting');

        $banks = collect([]);
        if ($isAccountingActive && Schema::hasTable('accounting_fund_accounts')) {
            $banks = DB::table('accounting_fund_accounts')
                ->where('type', 'bank')
                ->select('id', 'name')
                ->get();
        }

        if (isset($settings['bank_transfer_accounts']) && is_array($settings['bank_transfer_accounts'])) {
            $banksMap = $banks->pluck('name', 'id')->all();
            foreach ($settings['bank_transfer_accounts'] as &$acc) {
                if (is_array($acc)) {
                    if (!empty($acc['bank_id']) && isset($banksMap[$acc['bank_id']])) {
                        $acc['bank_name'] = $banksMap[$acc['bank_id']];
                    }
                    $owner = $acc['owner_name'] ?? (!empty($acc['name']) && $acc['name'] !== ($acc['bank_name'] ?? '') ? $acc['name'] : '');
                    $acc['owner_name'] = $owner;
                    $acc['name'] = $owner;
                    if (!isset($acc['is_active'])) {
                        $acc['is_active'] = true;
                    }
                }
            }
            unset($acc);
        }

        $availableServices = [];
        if (Schema::hasTable('booking_services') && Schema::hasColumn('booking_services', 'custom_prices')) {
            $servicesWithPrices = DB::table('booking_services')
                ->whereNotNull('custom_prices')
                ->select('id', 'name', 'custom_prices')
                ->get();

            foreach ($servicesWithPrices as $service) {
                $cp = is_string($service->custom_prices)
                    ? json_decode($service->custom_prices, true)
                    : $service->custom_prices;

                if (!isset($cp['tabs']) || !is_array($cp['tabs'])) continue;

                $tabs = [];
                foreach ($cp['tabs'] as $tab) {
                    $sections = [];
                    if (isset($tab['sections']) && is_array($tab['sections'])) {
                        foreach ($tab['sections'] as $section) {
                            $brands = [];
                            if (isset($section['brands']) && is_array($section['brands'])) {
                                foreach ($section['brands'] as $brand) {
                                    if (!empty($brand['name'])) {
                                        $brands[] = [
                                            'name' => $brand['name'],
                                            'price' => $brand['price'] ?? 0,
                                            'is_installment' => $brand['is_installment'] ?? false,
                                        ];
                                    }
                                }
                            }
                            $sections[] = [
                                'title' => $section['title'] ?? '',
                                'type' => $section['type'] ?? '',
                                'brands' => $brands,
                            ];
                        }
                    }
                    $tabs[] = [
                        'title' => $tab['title'] ?? 'بدون عنوان',
                        'sections' => $sections,
                    ];
                }

                $availableServices[] = [
                    'id' => $service->id,
                    'name' => $service->name,
                    'tabs' => $tabs,
                ];
            }
        }

        $apiKeys = ApiKey::with('creator')->latest()->get();

        $isPropertiesActive = NModule::has('Properties') && NModule::isEnabled('Properties');
        $propertyStatuses = collect();
        $propertyCategories = collect();

        if ($isPropertiesActive) {
            if (Schema::hasTable('property_statuses')) {
                $propertyStatuses = PropertyStatus::all();
            }
            if (Schema::hasTable('property_categories')) {
                $propertyCategories = PropertyCategory::all();
            }
        }

        $isBookingActive = NModule::has('Booking') && NModule::isEnabled('Booking');
        $bookingCategories = collect();

        if ($isBookingActive) {
            if (Schema::hasTable('booking_categories')) {
                $bookingCategories = BookingCategory::all();
            }
        }

        // واکشی پویای موجودیت‌های محتوا جهت انتخاب قالب داینامیک
        $isContentForgeActive = NModule::has('ContentForge') && NModule::isEnabled('ContentForge');
        $contentEntities = collect();
        if ($isContentForgeActive && class_exists(\Modules\ContentForge\App\Models\ContentEntity::class) && Schema::hasTable('content_entities')) {
            $contentEntities = \Modules\ContentForge\App\Models\ContentEntity::where('is_active', true)->get();
        }

        return view('settings::index', compact(
            'settings',
            'banks',
            'isAccountingActive',
            'isPropertiesActive',
            'isBookingActive',
            'isContentForgeActive',
            'contentEntities',
            'availableServices',
            'apiKeys',
            'propertyStatuses',
            'propertyCategories',
            'bookingCategories'
        ));
    }
    public function update(Request $request)
    {
        $request->validate([
            'identity_seal_signature' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ], [
            'identity_seal_signature.image' => 'فایل انتخاب‌شده برای مهر و امضا باید یک تصویر معتبر باشد.',
            'identity_seal_signature.mimes' => 'فرمت تصویر مهر و امضا باید یکی از png, jpg, jpeg, webp باشد.',
            'identity_seal_signature.max' => 'حجم تصویر مهر و امضا نباید بیشتر از ۵ مگابایت باشد.',
        ]);

        $activeTab = $request->input('active_tab');
        $data = $request->except(['_token', 'active_tab']);
        $this->handleIdentitySealUpload($request);
        unset($data['identity_seal_signature']);

        $nullableArrayKeys = [
            'installment_types',
            'pos_devices',
            'bank_transfer_accounts',
            'active_payment_methods',
            'installment_due_days',
            'widget_calendar_enabled_sources'
        ];
        foreach ($nullableArrayKeys as $key) {
            if (!$request->has($key)) {
                $data[$key] = [];
            }
        }

        // Validate installment types before processing
        if (isset($data['installment_types']) && is_array($data['installment_types'])) {
            $errors = $this->validateInstallmentTypes($data['installment_types']);
            if (!empty($errors)) {
                return redirect()->back()->withErrors($errors)->withInput();
            }
        }

        foreach ($data as $key => $value) {

            if ($key === 'installment_rounding_mode' && is_string($value)) {
                $value = strtolower(trim($value));
                if (!in_array($value, ['none', 'up', 'down'], true)) {
                    $value = 'none';
                }
            }

            // Handle file uploads
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/settings'), $filename);
                $value = 'uploads/settings/' . $filename;
            }

            // Special handling for installment_due_days
            if ($key === 'installment_due_days' && is_array($value)) {
                $value = array_map('intval', array_filter($value));
                $value = json_encode($value);
            }

            // Clean installment_types (keep only active brand configs)
            if ($key === 'installment_types' && is_array($value)) {
                foreach ($value as $index => $type) {
                    if (isset($type['brand_configs']) && is_array($type['brand_configs'])) {
                        $cleanConfigs = [];
                        foreach ($type['brand_configs'] as $brandKey => $config) {
                            if (isset($config['active']) && $config['active']) {
                                $cleanConfigs[$brandKey] = $config;
                            }
                        }
                        $value[$index]['brand_configs'] = $cleanConfigs;
                    }
                }
                $value = array_values($value);
            }

            // Clean array fields & enrich bank_transfer_accounts
            if (in_array($key, ['pos_devices', 'bank_transfer_accounts']) && is_array($value)) {
                $value = array_values($value);

                if ($key === 'bank_transfer_accounts') {
                    $isAccountingActive = NModule::has('Accounting') && NModule::isEnabled('Accounting');
                    $banksMap = [];
                    if ($isAccountingActive && Schema::hasTable('accounting_fund_accounts')) {
                        $banksMap = DB::table('accounting_fund_accounts')
                            ->where('type', 'bank')
                            ->pluck('name', 'id')
                            ->all();
                    }
                    foreach ($value as &$accItem) {
                        if (is_array($accItem)) {
                            if (!empty($accItem['bank_id']) && isset($banksMap[$accItem['bank_id']])) {
                                $accItem['bank_name'] = $banksMap[$accItem['bank_id']];
                            }
                            $owner = $accItem['owner_name'] ?? ($accItem['name'] ?? '');
                            $accItem['owner_name'] = $owner;
                            $accItem['name'] = $owner;
                            if (isset($accItem['is_active'])) {
                                $accItem['is_active'] = (bool) $accItem['is_active'];
                            } else {
                                $accItem['is_active'] = true;
                            }
                        }
                    }
                    unset($accItem);
                }
            }

            // Convert to JSON if array
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        $redirect = redirect()->route('settings.index')->with('success', 'تنظیمات با موفقیت ذخیره شد.');
        if ($activeTab) {
            $redirect->withFragment($activeTab);
        }

        return $redirect;
    }

    private function handleIdentitySealUpload(Request $request): void
    {
        $key = 'identity_seal_signature';
        Log::info('handleIdentitySealUpload: Method called.');

        if (!$request->hasFile($key)) {
            Log::info('handleIdentitySealUpload: No file found for key: ' . $key);
            return;
        }
        Log::info('handleIdentitySealUpload: File found for key: ' . $key);

        $file = $request->file($key);

        if (!$file->isValid()) {
            Log::error('handleIdentitySealUpload: File is not valid.', ['file_error' => $file->getErrorMessage()]);
            return;
        }
        Log::info('handleIdentitySealUpload: File is valid.');

        try {
            $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'png';
            $filename = time() . '_' . Str::random(12) . '.' . $extension;
            $destinationPath = public_path('uploads/settings');

            Log::info('handleIdentitySealUpload: Attempting to move file.', ['filename' => $filename, 'destination' => $destinationPath]);
            $file->move($destinationPath, $filename);

            $newPath = 'uploads/settings/' . $filename;
            Log::info('handleIdentitySealUpload: File moved successfully.', ['path' => $newPath]);

            $oldPath = Setting::where('key', $key)->value('value');
            if ($oldPath && file_exists(public_path($oldPath))) {
                @unlink(public_path($oldPath));
                Log::info('handleIdentitySealUpload: Old file deleted.', ['path' => $oldPath]);
            }

            Setting::updateOrCreate(['key' => $key], ['value' => $newPath]);
            Log::info('handleIdentitySealUpload: Database updated successfully.');

        } catch (\Exception $e) {
            Log::error('handleIdentitySealUpload: An exception occurred during file upload.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function validateInstallmentTypes(array $installmentTypes): array
    {
        $errors = [];
        $toNum = function ($val) {
            if ($val === null || $val === '') return null;
            return is_numeric($val) ? (float) $val : null;
        };

        $isInvalidNumeric = function ($val) {
            return $val !== null && $val !== '' && !is_numeric($val);
        };

        foreach ($installmentTypes as $index => $type) {
            $title = trim($type['title'] ?? '') !== '' ? $type['title'] : ('طرح شماره ' . ($index + 1));
            $prefix = "installment_types.{$index}";

            $rangeFieldsRaw = [
                'down_payment_min', 'down_payment_max', 'payment_stages_min',
                'payment_stages_max', 'fee_percent_min', 'fee_percent_max', 'months_limit',
                'annual_fee_percent'
            ];

            foreach ($rangeFieldsRaw as $field) {
                if ($isInvalidNumeric($type[$field] ?? null)) {
                    $errors["{$prefix}.{$field}"] = "مقدار وارد شده برای «{$field}» در طرح «{$title}» باید عدد باشد.";
                }
            }

            // (Rest of your validation code stays exactly the same)
            $dpMin = $toNum($type['down_payment_min'] ?? null);
            $dpMax = $toNum($type['down_payment_max'] ?? null);
            $psMin = $toNum($type['payment_stages_min'] ?? null);
            $psMax = $toNum($type['payment_stages_max'] ?? null);
            $feeMin = $toNum($type['fee_percent_min'] ?? null);
            $feeMax = $toNum($type['fee_percent_max'] ?? null);
            $monthsLimit = $toNum($type['months_limit'] ?? null);


            // Brand validation (unchanged)
            if (isset($type['brand_configs']) && is_array($type['brand_configs'])) {
                foreach ($type['brand_configs'] as $brandKey => $config) {
                    if (!isset($config['active']) || !$config['active']) continue;
                }
            }
        }

        return $errors;
    }

    private function extractBrandLabel(string $brandKey): string
    {
        $parts = explode('__', $brandKey);
        $label = end($parts);
        return $label !== false && $label !== '' ? $label : $brandKey;
    }

    public function testGapGPT(Request $request)
    {
        $request->validate([
            'gapgpt_api_key' => 'required|string',
            'gapgpt_base_url' => 'required|url',
        ]);

        $config = [
            'gapgpt_api_key' => $request->gapgpt_api_key,
            'gapgpt_base_url' => $request->gapgpt_base_url,
            'gapgpt_timeout'  => 10,
        ];

        $service = new GapGPTService($config);
        $models = $service->getModels();

        if ($models && isset($models['data'])) {
            return response()->json([
                'success' => true,
                'message' => 'اتصال با موفقیت برقرار شد. تعداد مدل‌های یافت شده: ' . count($models['data']),
                'models'  => array_slice($models['data'], 0, 5),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'خطا در برقرار ارتباط. لطفاً کلید API و آدرس پایه را بررسی کنید.',
        ], 400);
    }

    public function syncHolidays(Request $request, \App\Services\JalaliHolidayService $holidayService)
    {
        try {
            $result = $holidayService->syncCurrentAndNextYears();
            $yearsStr = implode(' و ', array_keys($result['years']));
            return redirect()->back()->with('success', "مناسبت‌ها و تعطیلات سال‌های {$yearsStr} با موفقیت در پایگاه داده محلی به‌روزرسانی شد. (مجموع کل: {$result['total_events']} مورد)");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'خطا در به‌روزرسانی داده‌های مناسبت‌ها: ' . $e->getMessage());
        }
    }
}
