<?php

namespace Modules\ContractForge\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ContractForge\App\Models\Contract;
use Modules\ContractForge\App\Models\ContractTemplate;
use Modules\ContractForge\Services\ContractEngine;
use Modules\ContractForge\Services\ContractModuleHelper;
use Modules\ContractForge\Services\TokenResolver;
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

    protected function authorizeContractRead(Contract $contract): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        if ($user->can('contractforge.view')) {
            return;
        }

        $entity = $contract->contractable;
        if ($entity && isset($entity->user_id) && (int) $entity->user_id === (int) $user->id && in_array($contract->status, ['active', 'signed'], true)) {
            return;
        }

        abort(403, 'شما دسترسی مشاهده این قرارداد را ندارید.');
    }

    public function print(Contract $contract)
    {
        $this->authorizeContractRead($contract);
        return view('contractforge::user.contracts.print', compact('contract'));
    }

    public function pdf(Contract $contract)
    {
        $this->authorizeContractRead($contract);
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

    public function sendToSign(Contract $contract)
    {
        if ($contract->status === 'signed' || $contract->status === 'cancelled') {
            return back()->with('error', 'وضعیت فعلی قرارداد امکان ارسال برای امضا را ندارد.');
        }

        $contract->update([
            'status' => 'active',
        ]);

        return back()->with('success', 'قرارداد با وضعیت «در انتظار امضا» برای طرف قرارداد ارسال شد.');
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
        $availableTypes = array_keys(ContractModuleHelper::getAvailableEntityTypes());

        $request->validate([
            'template_id'    => 'required|exists:contract_templates,id',
            'entity_id'      => 'required|integer',
            'entity_type'    => 'required|string|in:' . implode(',', $availableTypes ?: ['none']),
            'initial_status' => 'nullable|in:draft,active',
        ]);

        $template = ContractTemplate::findOrFail($request->template_id);

        if ($request->entity_type === 'treatment_plan' && ContractModuleHelper::isModuleActive('Booking')) {
            $entity = \Modules\Booking\App\Models\TreatmentPlan::findOrFail($request->entity_id);
        } elseif ($request->entity_type === 'field_service_technician' && ContractModuleHelper::isModuleActive('FieldService')) {
            $entity = \Modules\FieldService\App\Models\Technician::findOrFail($request->entity_id);
        } else {
            return back()->with('error', 'موجودیت نامعتبر است یا ماژول مرتبط غیرفعال می‌باشد.');
        }

        $initialStatus = $request->input(
            'initial_status',
            $request->entity_type === 'field_service_technician' ? 'active' : 'draft'
        );

        try {
            $contract = ContractEngine::generate($template, $entity, null, null, $initialStatus);
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

            return back()->with('success', 'محتوای قرارداد بر اساس آخرین تغییرات اطلاعات موجودیت و قالب قرارداد بروزرسانی شد.');
        } catch (\Exception $e) {
            return back()->with('error', 'خطا در بروزرسانی قرارداد: ' . $e->getMessage());
        }
    }
}
