<?php

namespace Modules\ContractForge\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ContractForge\App\Models\ContractRule;
use Modules\ContractForge\App\Models\ContractTemplate;
use Modules\ContractForge\Services\ContractModuleHelper;

class ContractRuleController extends Controller
{
    public function index()
    {
        $rules = ContractRule::with('template')->orderBy('priority', 'desc')->get();
        return view('contractforge::user.rules.index', compact('rules'));
    }

    public function create()
    {
        $templates = ContractTemplate::where('is_active', true)->get();
        $entityTypes = ContractModuleHelper::getAvailableEntityTypes();
        $events = [
            'created' => 'هنگام ایجاد موجودیت',
            'status_changed' => 'هنگام تغییر وضعیت موجودیت',
        ];
        
        $statuses = [];
        
        if (ContractModuleHelper::isModuleActive('Booking') && class_exists('\Modules\Booking\Entities\BookingSetting')) {
            $bookingSetting = \Modules\Booking\Entities\BookingSetting::current();
            $cureStatuses = $bookingSetting->cure_statuses ?? [];
            foreach ($cureStatuses as $st) {
                if (!empty($st['id']) && !empty($st['name'])) {
                    $statuses[$st['id']] = $st['name'];
                }
            }
        }

        if (ContractModuleHelper::isModuleActive('FieldService') && enum_exists('\Modules\FieldService\App\Enums\TechnicianStatus')) {
            foreach (\Modules\FieldService\App\Enums\TechnicianStatus::cases() as $techStatus) {
                $statuses[$techStatus->value] = 'تکنسین: ' . $techStatus->label();
            }
        }
        
        if (empty($statuses)) {
            $statuses = [
                'draft' => 'پیش‌نویس',
                'confirmed' => 'تأیید شده',
            ];
        }

        $bookingActive = ContractModuleHelper::isModuleActive('Booking');
        $fieldServiceActive = ContractModuleHelper::isModuleActive('FieldService');

        $clients = ($bookingActive && class_exists('\Modules\Clients\Entities\Client'))
            ? \Modules\Clients\Entities\Client::orderBy('full_name')->pluck('full_name')->unique()->toArray()
            : [];

        $paymentOptions = [];
        if ($bookingActive) {
            $activeMethods = function_exists('get_setting') ? json_decode(get_setting('active_payment_methods', '[]'), true) : [];
            if (is_array($activeMethods) && in_array('online', $activeMethods)) {
                $paymentOptions[] = 'پرداخت آنلاین';
            }

            if (function_exists('get_setting') && get_setting('pos_status') === 'active') {
                $posDevices = json_decode(get_setting('pos_devices', '[]'), true) ?: [];
                if (!empty($posDevices)) {
                    foreach ($posDevices as $pos) {
                        $name = $pos['name'] ?? ($pos['device_name'] ?? '');
                        if ($name) {
                            $paymentOptions[] = 'کارتخوان - ' . $name;
                        }
                    }
                } else {
                    $paymentOptions[] = 'کارتخوان';
                }
            }

            if (function_exists('get_setting') && get_setting('bank_transfer_status') === 'active') {
                $accounts = json_decode(get_setting('bank_transfer_accounts', '[]'), true) ?: [];
                if (!empty($accounts)) {
                    foreach ($accounts as $acc) {
                        $bankName = $acc['bank_name'] ?? $acc['name'] ?? '';
                        $accNum = $acc['account_number'] ?? '';
                        if ($bankName) {
                            $paymentOptions[] = 'کارت به کارت - ' . $bankName . ($accNum ? " ($accNum)" : "");
                        }
                    }
                } else {
                    $paymentOptions[] = 'حواله / کارت به کارت';
                }
            }

            if (function_exists('get_setting') && (get_setting('cod_status') === 'active' || true)) {
                $paymentOptions[] = 'نقدی';
            }

            $installmentTypesJson = function_exists('get_setting') ? get_setting('installment_types', '[]') : '[]';
            $installmentTypes = json_decode($installmentTypesJson, true) ?: [];
            foreach ($installmentTypes as $type) {
                if (!empty($type['title'])) {
                    $paymentOptions[] = 'اقساطی - ' . $type['title'];
                }
            }
            $paymentOptions = array_values(array_unique($paymentOptions));
        }

        $conditionFieldsByEntityType = $this->getConditionFieldsByEntityType($bookingActive, $fieldServiceActive);
        $sysCurrency = (function_exists('get_setting') && get_setting('payment_currency', 'toman') === 'toman') ? 'تومان' : 'ریال';

        return view('contractforge::user.rules.create', compact('templates', 'entityTypes', 'events', 'statuses', 'clients', 'paymentOptions', 'sysCurrency', 'conditionFieldsByEntityType'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'template_id' => 'required|exists:contract_templates,id',
            'entity_type' => 'required|string',
            'trigger_event' => 'required|string',
            'trigger_statuses' => 'nullable|array',
            'conditions' => 'nullable|array',
            'priority' => 'required|integer',
            'prevent_duplicate' => 'boolean',
        ]);

        ContractRule::create([
            'name' => $request->name,
            'template_id' => $request->template_id,
            'entity_type' => $request->entity_type,
            'trigger_event' => $request->trigger_event,
            'trigger_statuses' => $request->trigger_statuses ?: [],
            'conditions' => $request->conditions ?: ['operator' => 'AND', 'rules' => []],
            'priority' => $request->priority,
            'prevent_duplicate' => $request->has('prevent_duplicate'),
            'is_active' => true,
        ]);

        return redirect()->route('user.contracts.rules.index')
            ->with('success', 'قانون قرارداد ساز با موفقیت ایجاد شد.');
    }

    public function edit(ContractRule $rule)
    {
        $templates = ContractTemplate::where('is_active', true)->get();
        $entityTypes = ContractModuleHelper::getAvailableEntityTypes();
        $events = [
            'created' => 'هنگام ایجاد موجودیت',
            'status_changed' => 'هنگام تغییر وضعیت موجودیت',
        ];
        
        $statuses = [];
        
        if (ContractModuleHelper::isModuleActive('Booking') && class_exists('\Modules\Booking\Entities\BookingSetting')) {
            $bookingSetting = \Modules\Booking\Entities\BookingSetting::current();
            $cureStatuses = $bookingSetting->cure_statuses ?? [];
            foreach ($cureStatuses as $st) {
                if (!empty($st['id']) && !empty($st['name'])) {
                    $statuses[$st['id']] = $st['name'];
                }
            }
        }

        if (ContractModuleHelper::isModuleActive('FieldService') && enum_exists('\Modules\FieldService\App\Enums\TechnicianStatus')) {
            foreach (\Modules\FieldService\App\Enums\TechnicianStatus::cases() as $techStatus) {
                $statuses[$techStatus->value] = 'تکنسین: ' . $techStatus->label();
            }
        }
        
        if (empty($statuses)) {
            $statuses = [
                'draft' => 'پیش‌نویس',
                'confirmed' => 'تأیید شده',
            ];
        }

        $bookingActive = ContractModuleHelper::isModuleActive('Booking');
        $fieldServiceActive = ContractModuleHelper::isModuleActive('FieldService');

        $clients = ($bookingActive && class_exists('\Modules\Clients\Entities\Client'))
            ? \Modules\Clients\Entities\Client::orderBy('full_name')->pluck('full_name')->unique()->toArray()
            : [];

        $paymentOptions = [];
        if ($bookingActive) {
            $activeMethods = function_exists('get_setting') ? json_decode(get_setting('active_payment_methods', '[]'), true) : [];
            if (is_array($activeMethods) && in_array('online', $activeMethods)) {
                $paymentOptions[] = 'پرداخت آنلاین';
            }

            if (function_exists('get_setting') && get_setting('pos_status') === 'active') {
                $posDevices = json_decode(get_setting('pos_devices', '[]'), true) ?: [];
                if (!empty($posDevices)) {
                    foreach ($posDevices as $pos) {
                        $name = $pos['name'] ?? ($pos['device_name'] ?? '');
                        if ($name) {
                            $paymentOptions[] = 'کارتخوان - ' . $name;
                        }
                    }
                } else {
                    $paymentOptions[] = 'کارتخوان';
                }
            }

            if (function_exists('get_setting') && get_setting('bank_transfer_status') === 'active') {
                $accounts = json_decode(get_setting('bank_transfer_accounts', '[]'), true) ?: [];
                if (!empty($accounts)) {
                    foreach ($accounts as $acc) {
                        $bankName = $acc['bank_name'] ?? $acc['name'] ?? '';
                        $accNum = $acc['account_number'] ?? '';
                        if ($bankName) {
                            $paymentOptions[] = 'کارت به کارت - ' . $bankName . ($accNum ? " ($accNum)" : "");
                        }
                    }
                } else {
                    $paymentOptions[] = 'حواله / کارت به کارت';
                }
            }

            if (function_exists('get_setting') && (get_setting('cod_status') === 'active' || true)) {
                $paymentOptions[] = 'نقدی';
            }

            $installmentTypesJson = function_exists('get_setting') ? get_setting('installment_types', '[]') : '[]';
            $installmentTypes = json_decode($installmentTypesJson, true) ?: [];
            foreach ($installmentTypes as $type) {
                if (!empty($type['title'])) {
                    $paymentOptions[] = 'اقساطی - ' . $type['title'];
                }
            }
            $paymentOptions = array_values(array_unique($paymentOptions));
        }

        $conditionFieldsByEntityType = $this->getConditionFieldsByEntityType($bookingActive, $fieldServiceActive);
        $sysCurrency = (function_exists('get_setting') && get_setting('payment_currency', 'toman') === 'toman') ? 'تومان' : 'ریال';

        return view('contractforge::user.rules.edit', compact('rule', 'templates', 'entityTypes', 'events', 'statuses', 'clients', 'paymentOptions', 'sysCurrency', 'conditionFieldsByEntityType'));
    }

    public function update(Request $request, ContractRule $rule)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'template_id' => 'required|exists:contract_templates,id',
            'entity_type' => 'required|string',
            'trigger_event' => 'required|string',
            'trigger_statuses' => 'nullable|array',
            'conditions' => 'nullable|array',
            'priority' => 'required|integer',
            'prevent_duplicate' => 'boolean',
        ]);

        $rule->update([
            'name' => $request->name,
            'template_id' => $request->template_id,
            'entity_type' => $request->entity_type,
            'trigger_event' => $request->trigger_event,
            'trigger_statuses' => $request->trigger_statuses ?: [],
            'conditions' => $request->conditions ?: ['operator' => 'AND', 'rules' => []],
            'priority' => $request->priority,
            'prevent_duplicate' => $request->has('prevent_duplicate'),
        ]);

        return redirect()->route('user.contracts.rules.index')
            ->with('success', 'قانون قرارداد ساز با موفقیت ویرایش شد.');
    }

    public function destroy(ContractRule $rule)
    {
        $rule->delete();
        return redirect()->route('user.contracts.rules.index')
            ->with('success', 'قانون قرارداد ساز با موفقیت حذف شد.');
    }

    public function searchClients(Request $request)
    {
        $q = $request->q;
        if (empty($q)) {
            $clients = class_exists('\Modules\Clients\Entities\Client')
                ? \Modules\Clients\Entities\Client::limit(10)->get(['full_name', 'username', 'phone', 'case_number', 'national_code'])
                : collect();
            return response()->json($clients);
        }

        $clients = class_exists('\Modules\Clients\Entities\Client')
            ? \Modules\Clients\Entities\Client::where('full_name', 'like', "%{$q}%")
                ->orWhere('username', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
                ->orWhere('case_number', 'like', "%{$q}%")
                ->orWhere('national_code', 'like', "%{$q}%")
                ->limit(20)
                ->get(['full_name', 'username', 'phone', 'case_number', 'national_code'])
            : collect();
            
        return response()->json($clients);
    }

    protected function getConditionFieldsByEntityType(bool $bookingActive, bool $fieldServiceActive): array
    {
        $map = [];

        if ($bookingActive) {
            $map['treatment_plan'] = [
                'patient_name'             => 'نام بیمار',
                'plan_status'              => 'وضعیت طرح درمان',
                'installment_option_title' => 'روش پرداخت (installment_option_title)',
                'plan_total'               => 'مبلغ کل طرح درمان',
                'plan_final_payable'       => 'مبلغ نهایی قابل پرداخت',
                'installment_months'       => 'تعداد ماه‌های اقساط',
            ];
        }

        if ($fieldServiceActive) {
            $techFields = [
                'technician_name'        => 'نام تکنسین',
                'technician_mobile'      => 'موبایل تکنسین',
                'technician_tier'        => 'سطح تکنسین',
                'technician_status'      => 'وضعیت تکنسین',
                'technician_radius_km'   => 'شعاع فعالیت (کیلومتر)',
                'technician_skills_list' => 'تخصص‌های تکنسین',
            ];

            if (class_exists(\App\Models\CustomUserField::class)) {
                $customFields = \App\Models\CustomUserField::where('role_name', 'technician')->orderBy('id')->get();
                foreach ($customFields as $cf) {
                    $key = array_key_exists($cf->field_name, $techFields) ? 'cf_' . $cf->field_name : $cf->field_name;
                    $techFields[$key] = $cf->label ?: $cf->field_name;
                }
            }

            $map['field_service_technician'] = $techFields;
        }

        return $map;
    }
}
