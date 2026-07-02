<?php

namespace Modules\ContractForge\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ContractForge\App\Models\ContractTemplate;

class ContractTemplateController extends Controller
{
    public function index()
    {
        $templates = ContractTemplate::orderBy('created_at', 'desc')->get();
        return view('contractforge::user.templates.index', compact('templates'));
    }

    public function create()
    {
        $entityTypes = [
            'treatment_plan' => 'طرح درمان (نوبت‌دهی)'
        ];
        $tokens = $this->getAvailableTokens('treatment_plan');

        return view('contractforge::user.templates.create', compact('entityTypes', 'tokens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'entity_type' => 'required|string',
            'blocks' => 'nullable|array',
            'css_style' => 'nullable|string',
        ]);

        // Default block structure if none provided
        $blocks = $request->blocks ? array_values($request->blocks) : [
            ['type' => 'header', 'title' => 'قرارداد درمان'],
            ['type' => 'text', 'content' => "این قرارداد فی‌مابین طرفین منعقد گردید.\nنام بیمار: {patient_name}\nتاریخ: {today_jalali}"],
            ['type' => 'table', 'content' => 'plan_items_table'],
            ['type' => 'footer', 'content' => 'مهر و امضای پزشک / امضای بیمار']
        ];

        ContractTemplate::create([
            'name' => $request->name,
            'entity_type' => $request->entity_type,
            'blocks' => $blocks,
            'css_style' => $request->css_style,
            'created_by' => auth()->id() ?: 1,
            'is_active' => true,
        ]);

        return redirect()->route('user.contracts.templates.index')
            ->with('success', 'قالب قرارداد با موفقیت ایجاد شد.');
    }

    public function edit(ContractTemplate $template)
    {
        $entityTypes = [
            'treatment_plan' => 'طرح درمان (نوبت‌دهی)'
        ];
        $tokens = $this->getAvailableTokens($template->entity_type);

        return view('contractforge::user.templates.edit', compact('template', 'entityTypes', 'tokens'));
    }

    public function update(Request $request, ContractTemplate $template)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'entity_type' => 'required|string',
            'blocks' => 'nullable|array',
            'css_style' => 'nullable|string',
        ]);

        $template->update([
            'name' => $request->name,
            'entity_type' => $request->entity_type,
            'blocks' => $request->blocks ? array_values($request->blocks) : [],
            'css_style' => $request->css_style,
        ]);

        return redirect()->route('user.contracts.templates.index')
            ->with('success', 'قالب قرارداد با موفقیت ویرایش شد.');
    }

    public function destroy(ContractTemplate $template)
    {
        $template->delete();
        return redirect()->route('user.contracts.templates.index')
            ->with('success', 'قالب قرارداد با موفقیت حذف شد.');
    }

    protected function getAvailableTokens(string $entityType): array
    {
        if ($entityType === 'treatment_plan') {
            return [
                'patient_name' => 'نام بیمار',
                'plan_id' => 'شناسه طرح درمان',
                'plan_status' => 'وضعیت طرح درمان',
                'plan_total' => 'مبلغ کل طرح درمان',
                'plan_final_payable' => 'مبلغ نهایی قابل پرداخت',
                'plan_discount' => 'مبلغ تخفیف',
                'plan_tax' => 'مبلغ مالیات',
                'plan_notes' => 'یادداشت‌های طرح درمان',
                'today_jalali' => 'تاریخ امروز (جلالی)',
                'system_currency' => 'واحد پول سیستم (تومان/ریال)',
                'total_cheques' => 'تعداد چک‌های دریافتی',
                'total_installment_stages' => 'تعداد مراحل پرداخت',
                'installment_option_title' => 'عنوان روش پرداخت اقساطی',
                'installment_down_payment' => 'مبلغ پیش‌پرداخت',
                'installment_monthly_amount' => 'مبلغ اقساط ماهیانه',
                'installment_months' => 'تعداد ماه‌های اقساط',
                'installment_due_day' => 'روز سررسید اقساط',
                'installment_start_date' => 'تاریخ شروع اقساط',
                'plan_items_table' => 'جدول آیتم‌های طرح درمان (HTML)',
                'installment_breakdown_table' => 'جدول اقساط طرح درمان (HTML)',
                'cheques_table' => 'جدول چک‌های دریافتی (HTML)',
            ];
        }
        return [];
    }
}
