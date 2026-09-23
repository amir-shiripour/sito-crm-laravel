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

    public function create(Request $request)
    {
        $entityTypes = [
            'treatment_plan' => 'طرح درمان (نوبت‌دهی)'
        ];
        $tokens = $this->getAvailableTokens('treatment_plan');

        $sourceTemplate = null;
        if ($request->filled('duplicate_id')) {
            $sourceTemplate = ContractTemplate::find($request->duplicate_id);
        }

        return view('contractforge::user.templates.create', compact('entityTypes', 'tokens', 'sourceTemplate'));
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
                'contract_number' => 'شماره یکتای قرارداد',
                'patient_name' => 'نام بیمار',
                'patient_phone' => 'شماره تماس بیمار',
                'patient_national_code' => 'کد ملی بیمار',
                'patient_case_number' => 'شماره پرونده بیمار',
                'patient_email' => 'ایمیل بیمار',
                'plan_id' => 'شناسه طرح درمان',
                'plan_status' => 'وضعیت طرح درمان',
                'plan_date' => 'تاریخ طرح درمان (جلالی)',
                'today_jalali' => 'تاریخ امروز (جلالی)',
                'system_currency' => 'واحد پول سیستم (تومان/ریال)',
                'clinic_name' => 'نام کلینیک / مجموعه',
                'clinic_phone' => 'شماره تماس کلینیک',
                'clinic_address' => 'آدرس کلینیک',
                
                // Monetary amounts in numbers
                'plan_total' => 'مبلغ کل طرح درمان (عدد)',
                'plan_final_payable' => 'مبلغ نهایی قابل پرداخت (عدد)',
                'plan_discount' => 'مبلغ تخفیف (عدد)',
                'plan_tax' => 'مبلغ مالیات (عدد)',
                
                // Monetary amounts in words
                'plan_total_in_words' => 'مبلغ کل به حروف (مثال: سیصد میلیون تومان)',
                'plan_final_payable_in_words' => 'مبلغ نهایی قابل پرداخت به حروف',
                'plan_discount_in_words' => 'مبلغ تخفیف به حروف',
                'plan_tax_in_words' => 'مبلغ مالیات به حروف',
                'plan_notes' => 'یادداشت‌های طرح درمان',
                
                // Installment tokens
                'installment_option_title' => 'عنوان روش پرداخت اقساطی',
                'installment_down_payment' => 'مبلغ پیش‌پرداخت (عدد)',
                'installment_down_payment_in_words' => 'مبلغ پیش‌پرداخت به حروف',
                'installment_down_payment_percent' => 'درصد پیش‌پرداخت (فقط عدد، مثال: 20)',
                'installment_down_payment_percent_label' => 'درصد پیش‌پرداخت با پسوند (مثال: ۲۰ درصد)',
                'installment_fee_value' => 'مبلغ سود / کارمزد اقساط (عدد)',
                'installment_fee_value_in_words' => 'مبلغ سود / کارمزد اقساط به حروف',
                'installment_fee_percent' => 'درصد سود / کارمزد اقساط (فقط عدد، مثال: 4)',
                'installment_fee_percent_label' => 'درصد سود / کارمزد اقساط با پسوند (مثال: ۴ درصد)',
                'installment_profit_amount' => 'مبلغ سود اقساط (مترادف fee_value)',
                'installment_profit_in_words' => 'مبلغ سود اقساط به حروف',
                'installment_profit_percent' => 'درصد سود اقساط (فقط عدد)',
                'installment_profit_percent_label' => 'درصد سود اقساط با پسوند (مثال: ۴ درصد)',
                'installment_monthly_amount' => 'مبلغ هر قسط ماهیانه (عدد)',
                'installment_monthly_amount_in_words' => 'مبلغ هر قسط به حروف',
                'installment_remaining_amount' => 'مبلغ باقیمانده اقساط (عدد)',
                'installment_remaining_amount_in_words' => 'مبلغ باقیمانده اقساط به حروف',
                'installment_months' => 'تعداد ماه‌های اقساط',
                'installment_due_day' => 'روز سررسید اقساط',
                'installment_start_date' => 'تاریخ شروع اقساط',
                'total_cheques' => 'تعداد چک‌های دریافتی',
                'total_installment_stages' => 'تعداد مراحل پرداخت',
                
                // Tables
                'plan_items_table' => 'جدول خدمات و ایمپلنت‌ها (HTML)',
                'cheques_table' => 'جدول چک‌های دریافتی (HTML)',
                'installment_breakdown_table' => 'جدول اقساط و مراحل پرداخت (HTML)',
            ];
        }
        return [];
    }
}
