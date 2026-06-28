<?php

namespace Modules\Settings\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Settings\Entities\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module as NModule;

class UserPaymentSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit()
    {
        // Require permission
        abort_unless(auth()->user()->can('settings.payment.manage'), 403, 'دسترسی غیرمجاز');

        $settingsCollection = Setting::all()->pluck('value', 'key');
        $settings = $settingsCollection->toArray();

        $jsonKeys = [
            'installment_types',
            'pos_devices',
            'bank_transfer_accounts',
            'active_payment_methods',
            'installment_due_days'
        ];

        foreach ($jsonKeys as $key) {
            if (isset($settings[$key]) && is_string($settings[$key])) {
                $decoded = json_decode($settings[$key], true);
                $settings[$key] = is_array($decoded) ? $decoded : [];
            }
        }

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

        $cardClass = 'bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-200 hover:shadow-md';
        $headerClass = 'px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3 bg-gray-50/50 dark:bg-gray-900/30 rounded-t-2xl';
        $labelClass = 'block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2';
        $inputClass = 'w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800';

        return view('settings::user.settings.payment', compact(
            'settings',
            'banks',
            'isAccountingActive',
            'availableServices',
            'cardClass',
            'headerClass',
            'labelClass',
            'inputClass'
        ));
    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->can('settings.payment.manage'), 403, 'دسترسی غیرمجاز');
        
        $data = $request->except('_token');

        $nullableArrayKeys = [
            'installment_types',
            'pos_devices',
            'bank_transfer_accounts',
            'active_payment_methods',
            'installment_due_days'
        ];
        
        foreach ($nullableArrayKeys as $key) {
            if (!$request->has($key)) {
                $data[$key] = [];
            }
        }

        foreach ($data as $key => $value) {
            if ($key === 'installment_rounding_mode' && is_string($value)) {
                $value = strtolower(trim($value));
                if (!in_array($value, ['none', 'up', 'down'], true)) {
                    $value = 'none';
                }
            }
            if ($key === 'installment_rounding_factor') {
                $value = max(0, (int)$value);
            }

            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
            
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return back()->with('success', 'تنظیمات مالی و پرداخت با موفقیت بروزرسانی شد.');
    }
}
