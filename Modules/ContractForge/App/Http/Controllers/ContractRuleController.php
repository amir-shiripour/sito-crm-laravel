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
        $statuses = [
            'draft' => 'پیش‌نویس',
            'confirmed' => 'تأیید شده',
        ];

        return view('contractforge::user.rules.create', compact('templates', 'entityTypes', 'events', 'statuses'));
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
        $statuses = [
            'draft' => 'پیش‌نویس',
            'confirmed' => 'تأیید شده',
        ];

        return view('contractforge::user.rules.edit', compact('rule', 'templates', 'entityTypes', 'events', 'statuses'));
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
}
