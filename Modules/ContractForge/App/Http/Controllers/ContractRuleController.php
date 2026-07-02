<?php

namespace Modules\ContractForge\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ContractForge\App\Models\ContractRule;
use Modules\ContractForge\App\Models\ContractTemplate;

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
        $entityTypes = [
            'treatment_plan' => 'طرح درمان (نوبت‌دهی)'
        ];
        $events = [
            'created' => 'هنگام ایجاد موجودیت',
            'status_changed' => 'هنگام تغییر وضعیت موجودیت',
        ];
        
        $statuses = [];
        
        if (class_exists('\Modules\Booking\Entities\BookingSetting')) {
            $bookingSetting = \Modules\Booking\Entities\BookingSetting::current();
            $cureStatuses = $bookingSetting->cure_statuses ?? [];
            foreach ($cureStatuses as $st) {
                if (!empty($st['id']) && !empty($st['name'])) {
                    $statuses[$st['id']] = $st['name'];
                }
            }
        }
        
        if (empty($statuses)) {
            $statuses = [
                'draft' => 'پیش‌نویس',
                'confirmed' => 'تأیید شده',
            ];
        }

        $clients = class_exists('\Modules\Clients\Entities\Client') 
            ? \Modules\Clients\Entities\Client::orderBy('full_name')->pluck('full_name')->unique()->toArray() 
            : [];
            
        $paymentOptions = [];
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
                    $bankName = $acc['bank_name'] ?? '';
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

        $sysCurrency = (function_exists('get_setting') && get_setting('payment_currency', 'toman') === 'toman') ? 'تومان' : 'ریال';

        return view('contractforge::user.rules.create', compact('templates', 'entityTypes', 'events', 'statuses', 'clients', 'paymentOptions', 'sysCurrency'));
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
        $entityTypes = [
            'treatment_plan' => 'طرح درمان (نوبت‌دهی)'
        ];
        $events = [
            'created' => 'هنگام ایجاد موجودیت',
            'status_changed' => 'هنگام تغییر وضعیت موجودیت',
        ];
        
        $statuses = [];
        
        if (class_exists('\Modules\Booking\Entities\BookingSetting')) {
            $bookingSetting = \Modules\Booking\Entities\BookingSetting::current();
            $cureStatuses = $bookingSetting->cure_statuses ?? [];
            foreach ($cureStatuses as $st) {
                if (!empty($st['id']) && !empty($st['name'])) {
                    $statuses[$st['id']] = $st['name'];
                }
            }
        }
        
        if (empty($statuses)) {
            $statuses = [
                'draft' => 'پیش‌نویس',
                'confirmed' => 'تأیید شده',
            ];
        }

        $clients = class_exists('\Modules\Clients\Entities\Client') 
            ? \Modules\Clients\Entities\Client::orderBy('full_name')->pluck('full_name')->unique()->toArray() 
            : [];
            
        $paymentOptions = [];
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
                    $bankName = $acc['bank_name'] ?? '';
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

        $sysCurrency = (function_exists('get_setting') && get_setting('payment_currency', 'toman') === 'toman') ? 'تومان' : 'ریال';

        return view('contractforge::user.rules.edit', compact('rule', 'templates', 'entityTypes', 'events', 'statuses', 'clients', 'paymentOptions', 'sysCurrency'));
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
}
