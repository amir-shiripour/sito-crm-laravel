<?php

namespace Modules\Settings\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Settings\Entities\Setting;
use App\Services\GapGPTService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module as NModule;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');

        $jsonKeys = [
            'registration',
            'installment_types',
            'pos_devices',
            'bank_transfer_accounts',
            'active_payment_methods',
            'theme_colors'
        ];

        foreach ($jsonKeys as $key) {
            if (isset($settings[$key]) && is_string($settings[$key])) {
                $settings[$key] = json_decode($settings[$key], true);
            }
        }

        $isAccountingActive = NModule::has('Accounting') && NModule::isEnabled('Accounting');

        $banks = collect([]);
        if ($isAccountingActive && Schema::hasTable('accounting_fund_accounts')) {
            $banks = DB::table('accounting_fund_accounts')
                ->where('type', 'bank')
                ->select('id', 'name')
                ->get();
        }

        $availableServices = [];
        if (Schema::hasTable('booking_services')) {
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
                                            'name'          => $brand['name'],
                                            'price'         => $brand['price'] ?? 0,
                                            'is_installment'=> $brand['is_installment'] ?? false,
                                        ];
                                    }
                                }
                            }
                            $sections[] = [
                                'title'  => $section['title'] ?? '',
                                'type'   => $section['type'] ?? '',
                                'brands' => $brands,
                            ];
                        }
                    }
                    $tabs[] = [
                        'title'    => $tab['title'] ?? 'بدون عنوان',
                        'sections' => $sections,
                    ];
                }

                $availableServices[] = [
                    'id'   => $service->id,
                    'name' => $service->name,
                    'tabs' => $tabs,
                ];
            }
        }

        return view('settings::index', compact(
            'settings',
            'banks',
            'isAccountingActive',
            'availableServices'
        ));
    }

    public function update(Request $request)
    {
        $data = $request->except('_token');

        // ولیدیشن بازه‌های طرح قسطی و برندهای مشمول آن، قبل از هرگونه پردازش/ذخیره
        if (isset($data['installment_types']) && is_array($data['installment_types'])) {
            $errors = $this->validateInstallmentTypes($data['installment_types']);
            if (!empty($errors)) {
                return redirect()->back()->withErrors($errors)->withInput();
            }
        }

        foreach ($data as $key => $value) {
            // Handle file uploads
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/settings'), $filename);
                $value = 'uploads/settings/' . $filename;
            }

            if ($key === 'installment_types' && is_array($value)) {
                foreach ($value as $index => $type) {
                    if (isset($type['brand_configs']) && is_array($type['brand_configs'])) {
                        // Only keep brand_configs that are actually checked (active = true)
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

            if (in_array($key, ['pos_devices', 'bank_transfer_accounts']) && is_array($value)) {
                $value = array_values($value);
            }

            if (in_array($key, ['installment_min_total_threshold', 'installment_amount_step'])) {
                $value = (int) $value;
            }

            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    /**
     * بررسی می‌کند که بازه‌های سطح طرح معتبر هستند (min <= max) و مقادیر هر برند فعال
     * داخل بازه‌ی تعیین‌شده در سطح همان طرح قرار دارند. در صورت خطا، آرایه‌ای از
     * پیام‌ها با کلید نقطه‌دار (برای استفاده در withErrors) برمی‌گرداند.
     */
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

            // اعتبارسنجی فرمت عددی فیلدهای رنج سطح طرح
            $rangeFieldsRaw = [
                'down_payment_min', 'down_payment_max',
                'payment_stages_min', 'payment_stages_max',
                'fee_percent_min', 'fee_percent_max',
                'months_limit',
            ];
            foreach ($rangeFieldsRaw as $field) {
                if ($isInvalidNumeric($type[$field] ?? null)) {
                    $errors["{$prefix}.{$field}"] = "مقدار وارد شده برای «{$field}» در طرح «{$title}» باید عدد باشد.";
                }
            }

            $dpMin = $toNum($type['down_payment_min'] ?? null);
            $dpMax = $toNum($type['down_payment_max'] ?? null);
            $psMin = $toNum($type['payment_stages_min'] ?? null);
            $psMax = $toNum($type['payment_stages_max'] ?? null);
            $feeMin = $toNum($type['fee_percent_min'] ?? null);
            $feeMax = $toNum($type['fee_percent_max'] ?? null);
            $monthsLimit = $toNum($type['months_limit'] ?? null);

            // پیش‌پرداخت: بازه ۰ تا ۱۰۰ و min <= max
            if ($dpMin !== null && ($dpMin < 0 || $dpMin > 100)) {
                $errors["{$prefix}.down_payment_min"] = "حداقل پیش‌پرداخت طرح «{$title}» باید بین ۰ تا ۱۰۰ درصد باشد.";
            }
            if ($dpMax !== null && ($dpMax < 0 || $dpMax > 100)) {
                $errors["{$prefix}.down_payment_max"] = "حداکثر پیش‌پرداخت طرح «{$title}» باید بین ۰ تا ۱۰۰ درصد باشد.";
            }
            if ($dpMin !== null && $dpMax !== null && $dpMin > $dpMax) {
                $errors["{$prefix}.down_payment_min"] = "حداقل پیش‌پرداخت طرح «{$title}» نمی‌تواند بیشتر از حداکثر آن باشد.";
            }

            // مراحل پرداخت: حداقل ۱ و min <= max
            if ($psMin !== null && $psMin < 1) {
                $errors["{$prefix}.payment_stages_min"] = "حداقل مراحل پرداخت طرح «{$title}» باید حداقل ۱ باشد.";
            }
            if ($psMax !== null && $psMax < 1) {
                $errors["{$prefix}.payment_stages_max"] = "حداکثر مراحل پرداخت طرح «{$title}» باید حداقل ۱ باشد.";
            }
            if ($psMin !== null && $psMax !== null && $psMin > $psMax) {
                $errors["{$prefix}.payment_stages_min"] = "حداقل مراحل پرداخت طرح «{$title}» نمی‌تواند بیشتر از حداکثر آن باشد.";
            }

            // کارمزد: نباید منفی باشد و min <= max
            if ($feeMin !== null && $feeMin < 0) {
                $errors["{$prefix}.fee_percent_min"] = "حداقل کارمزد طرح «{$title}» نمی‌تواند منفی باشد.";
            }
            if ($feeMax !== null && $feeMax < 0) {
                $errors["{$prefix}.fee_percent_max"] = "حداکثر کارمزد طرح «{$title}» نمی‌تواند منفی باشد.";
            }
            if ($feeMin !== null && $feeMax !== null && $feeMin > $feeMax) {
                $errors["{$prefix}.fee_percent_min"] = "حداقل کارمزد طرح «{$title}» نمی‌تواند بیشتر از حداکثر آن باشد.";
            }

            // حداکثر اقساط طرح: حداقل ۱ ماه
            if ($monthsLimit !== null && $monthsLimit < 1) {
                $errors["{$prefix}.months_limit"] = "حداکثر اقساط طرح «{$title}» باید حداقل ۱ ماه باشد.";
            }

            // اعتبارسنجی برندهای فعال داخل برندهای مشمول این طرح
            if (isset($type['brand_configs']) && is_array($type['brand_configs'])) {
                foreach ($type['brand_configs'] as $brandKey => $config) {
                    if (!isset($config['active']) || !$config['active']) continue;

                    $brandLabel = $this->extractBrandLabel((string) $brandKey);
                    $fieldPrefix = "{$prefix}.brand_configs.{$brandKey}";

                    $brandFieldsRaw = ['down_payment', 'payment_stages', 'fee_percent', 'months_limit'];
                    foreach ($brandFieldsRaw as $field) {
                        if ($isInvalidNumeric($config[$field] ?? null)) {
                            $errors["{$fieldPrefix}.{$field}"] = "مقدار «{$field}» برای برند «{$brandLabel}» در طرح «{$title}» باید عدد باشد.";
                        }
                    }

                    $brandDp = $toNum($config['down_payment'] ?? null);
                    $brandPs = $toNum($config['payment_stages'] ?? null);
                    $brandFee = $toNum($config['fee_percent'] ?? null);
                    $brandMonths = $toNum($config['months_limit'] ?? null);

                    if ($brandDp !== null) {
                        $min = $dpMin ?? 0;
                        $max = $dpMax ?? 100;
                        if ($brandDp < $min || $brandDp > $max) {
                            $errors["{$fieldPrefix}.down_payment"] = "پیش‌پرداخت برند «{$brandLabel}» در طرح «{$title}» باید بین {$min} تا {$max} درصد باشد.";
                        }
                    }

                    if ($brandPs !== null) {
                        $min = $psMin ?? 1;
                        if ($brandPs < $min) {
                            $errors["{$fieldPrefix}.payment_stages"] = "مراحل پرداخت برند «{$brandLabel}» در طرح «{$title}» نباید کمتر از {$min} باشد.";
                        } elseif ($psMax !== null && $brandPs > $psMax) {
                            $errors["{$fieldPrefix}.payment_stages"] = "مراحل پرداخت برند «{$brandLabel}» در طرح «{$title}» نباید بیشتر از {$psMax} باشد.";
                        }
                    }

                    if ($brandFee !== null) {
                        $min = $feeMin ?? 0;
                        if ($brandFee < $min) {
                            $errors["{$fieldPrefix}.fee_percent"] = "کارمزد برند «{$brandLabel}» در طرح «{$title}» نباید کمتر از {$min} درصد باشد.";
                        } elseif ($feeMax !== null && $brandFee > $feeMax) {
                            $errors["{$fieldPrefix}.fee_percent"] = "کارمزد برند «{$brandLabel}» در طرح «{$title}» نباید بیشتر از {$feeMax} درصد باشد.";
                        }
                    }

                    if ($brandMonths !== null) {
                        if ($brandMonths < 1) {
                            $errors["{$fieldPrefix}.months_limit"] = "اقساط برند «{$brandLabel}» در طرح «{$title}» باید حداقل ۱ ماه باشد.";
                        } elseif ($monthsLimit !== null && $brandMonths > $monthsLimit) {
                            $errors["{$fieldPrefix}.months_limit"] = "اقساط برند «{$brandLabel}» در طرح «{$title}» نمی‌تواند بیشتر از {$monthsLimit} ماه (حداکثر اقساط طرح) باشد.";
                        }
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * از کلید برند (مثلاً serviceId__tab__section__brandName) فقط نام نمایشی برند را استخراج می‌کند.
     */
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
}
