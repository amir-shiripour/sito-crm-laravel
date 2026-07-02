<?php

namespace Modules\ContractForge\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ContractForge\App\Models\Contract;
use Modules\ContractForge\App\Models\ContractTemplate;
use Modules\ContractForge\Services\ContractEngine;
use Modules\ContractForge\Services\TokenResolver;
use Modules\Booking\App\Models\TreatmentPlan;
use Spatie\Browsershot\Browsershot;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $query = Contract::with(['template', 'client', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $contracts = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('contractforge::user.contracts.index', compact('contracts'));
    }

    public function show(Contract $contract)
    {
        return view('contractforge::user.contracts.show', compact('contract'));
    }

    public function print(Contract $contract)
    {
        return view('contractforge::user.contracts.print', compact('contract'));
    }

    public function pdf(Contract $contract)
    {
        // Add minimal print container styles to body
        $html = view('contractforge::user.contracts.pdf', compact('contract'))->render();

        try {
            $browsershot = Browsershot::html($html);

            // Match exact core statement PDF settings
            if (PHP_OS_FAMILY === 'Windows') {
                $browsershot->setNodeBinary('C:\\Program Files\\nodejs\\node.exe')
                    ->setNpmBinary('C:\\Program Files\\nodejs\\npm.cmd')
                    ->setChromePath('C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe');
            } else {
                $browsershot->noSandbox()
                    ->setOption('args', ['--disable-web-security'])
                    ->setNodeModulePath(base_path('node_modules'))
                    ->setChromePath('/usr/bin/google-chrome');
            }

            $pdf = $browsershot
                ->format('A4')
                ->margins(10, 10, 10, 10)
                ->showBackground()
                ->waitUntilNetworkIdle()
                ->pdf();

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="contract_' . $contract->contract_number . '.pdf"',
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'خطا در تولید PDF: ' . $e->getMessage());
        }
    }

    public function sign(Contract $contract)
    {
        $contract->update([
            'status' => 'signed',
            'signed_at' => now(),
        ]);

        return back()->with('success', 'قرارداد با موفقیت به عنوان امضا شده ثبت شد.');
    }

    public function cancel(Contract $contract)
    {
        $contract->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', 'قرارداد با موفقیت لغو شد.');
    }

    public function destroy(Contract $contract)
    {
        $contract->delete();
        return redirect()->route('user.contracts.index')
            ->with('success', 'قرارداد با موفقیت حذف شد.');
    }

    public function generateManual(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:contract_templates,id',
            'entity_id' => 'required|integer',
            'entity_type' => 'required|string|in:treatment_plan',
        ]);

        $template = ContractTemplate::findOrFail($request->template_id);

        if ($request->entity_type === 'treatment_plan') {
            $entity = TreatmentPlan::findOrFail($request->entity_id);
        } else {
            return back()->with('error', 'موجودیت نامعتبر است.');
        }

        try {
            $contract = ContractEngine::generate($template, $entity);
            return redirect()->route('user.contracts.show', $contract->id)
                ->with('success', 'قرارداد جدید با موفقیت ایجاد شد.');
        } catch (\Exception $e) {
            return back()->with('error', 'خطا در ایجاد قرارداد: ' . $e->getMessage());
        }
    }

    public function regenerate(Contract $contract)
    {
        if ($contract->status === 'signed') {
            return back()->with('error', 'قرارداد امضا شده قابل بروزرسانی نیست.');
        }

        $template = $contract->template;
        $entity = $contract->contractable;

        if (!$template || !$entity) {
            return back()->with('error', 'قالب قرارداد یا موجودیت مرتبط یافت نشد.');
        }

        try {
            $blocks = $template->blocks ?: [];

            if (!empty($blocks)) {
                $renderedBody = ContractEngine::renderBlocks($blocks, $entity);
            } else {
                $renderedBody = TokenResolver::resolve($template->body ?? '', $entity);
            }

            if (!empty($template->css_style)) {
                $renderedBody = "<style>{$template->css_style}</style>\n" . $renderedBody;
            }

            $title = $template->name . ' - ' . (method_exists($entity, 'getContractTitle') ? $entity->getContractTitle() : '');

            $contract->update([
                'blocks_data' => $blocks,
                'rendered_body' => $renderedBody,
                'title' => $title,
            ]);

            return back()->with('success', 'محتوای قرارداد بر اساس آخرین تغییرات طرح درمان و قالب قرارداد بروزرسانی شد.');
        } catch (\Exception $e) {
            return back()->with('error', 'خطا در بروزرسانی قرارداد: ' . $e->getMessage());
        }
    }
}
