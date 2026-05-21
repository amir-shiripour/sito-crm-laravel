<?php

namespace Modules\Accounting\App\Http\Controllers;

use Nwidart\Modules\Facades\Module; // فقط همین یک Module باید اینجا باشد
use Illuminate\Routing\Controller;
use Modules\Accounting\App\Http\Requests\StoreExpenseRequest;
use Modules\Accounting\App\Http\Requests\UpdateExpenseRequest;
use Modules\Accounting\App\Models\Bank;
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\App\Models\Document;
use Modules\Accounting\App\Services\DocumentService;
use Illuminate\Http\Request;
use Modules\Clients\Entities\Client;

class ExpenseController extends Controller
{
    protected $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
        $this->middleware('can:accounting.expenses.view')->only('index');
        $this->middleware('can:accounting.expenses.create')->only(['create', 'store']);
        $this->middleware('can:accounting.expenses.edit')->only(['edit', 'update']);
        $this->middleware('can:accounting.expenses.delete')->only('destroy');
    }

    public function index()
    {
        $documents = Document::where('type', 'expense')
            ->with(['bank', 'category', 'client'])
            ->latest('document_date')
            ->latest('id')
            ->paginate(20);

        return view('accounting::expenses.index', compact('documents'));
    }

    public function create()
    {
        $banks = Bank::where('status', 1)->get();
        $categories = Category::where('type', 'expense')->where('status', 1)->get();
        $customers = Module::isEnabled('Clients') ? Client::select('id', 'full_name', 'username', 'national_code')->get() : collect();

        return view('accounting::expenses.create', compact('banks', 'categories', 'customers'));
    }

    public function store(StoreExpenseRequest $request)
    {
        $data = $request->validated();
        $data['type'] = 'expense';

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('public/expense_attachments');
        }

        // The 'client_id' is now part of validated data if the request is correct.
        // The DocumentService should handle the creation.
        try {
            $this->documentService->store($data);
            return redirect()->route('admin.accounting.expenses.index')
                ->with('success', 'سند هزینه با موفقیت ثبت شد.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'خطا در ثبت هزینه: ' . $e->getMessage()]);
        }
    }

    public function edit(Document $expense)
    {
        $banks = Bank::where('status', 1)->get();
        $categories = Category::where('type', 'expense')->where('status', 1)->get();
        $customers = Module::isEnabled('Clients') ? Client::select('id', 'full_name', 'username', 'national_code')->get() : collect();

        return view('accounting::expenses.edit', compact('expense', 'banks', 'categories', 'customers'));
    }

    public function update(UpdateExpenseRequest $request, Document $expense)
    {
        $data = $request->validated();

        if ($request->hasFile('attachment')) {
            if ($expense->attachment) {
                \Storage::delete($expense->attachment);
            }
            $data['attachment'] = $request->file('attachment')->store('public/expense_attachments');
        } elseif ($request->boolean('delete_attachment')) {
            if ($expense->attachment) {
                \Storage::delete($expense->attachment);
                $data['attachment'] = null;
            }
        }

        try {
            $this->documentService->update($expense, $data);
            return redirect()->route('admin.accounting.expenses.index')
                ->with('success', 'سند هزینه با موفقیت ویرایش شد.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'خطا در ویرایش هزینه: ' . $e->getMessage()]);
        }
    }

    public function destroy(Document $expense)
    {
        if ($expense->type !== 'expense') {
            return back()->withErrors(['msg' => 'این سند از نوع هزینه نمی باشد.']);
        }

        try {
            $this->documentService->destroy($expense->id);
            return redirect()->route('admin.accounting.expenses.index')
                ->with('success', 'سند هزینه با موفقیت حذف شد.');
        } catch (\Exception $e) {
            return back()->withErrors(['msg' => 'خطا در حذف هزینه: ' . $e->getMessage()]);
        }
    }
}
