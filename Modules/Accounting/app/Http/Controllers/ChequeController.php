<?php

namespace Modules\Accounting\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use Modules\Accounting\App\Http\Requests\ReconcileChequeRequest;
use Modules\Accounting\App\Http\Requests\StoreChequeRequest;
use Modules\Accounting\App\Http\Requests\UpdateChequeRequest;
use Modules\Accounting\App\Models\Bank;
use Modules\Accounting\App\Models\Cheque;
use Modules\Accounting\App\Models\AccountingSetting;
use Modules\Accounting\App\Models\Invoice;
use Modules\Clients\Entities\Client;

class ChequeController extends Controller
{
    // ... other methods ...

    public function index()
    {
        $cheques = Cheque::with('client')->latest()->paginate(20);
        return view('accounting::cheques.index', compact('cheques'));
    }

    public function create()
    {
        $clients = Module::isEnabled('Clients') ? Client::select('id', 'full_name', 'username')->get() : collect();
        $banks = Bank::all();
        return view('accounting::cheques.create', compact('clients', 'banks'));
    }

    public function store(StoreChequeRequest $request)
    {
        Cheque::create($request->validated());
        return redirect()->route('admin.accounting.cheques.index')->with('success', 'چک جدید با موفقیت ثبت شد.');
    }

    public function edit(Cheque $cheque)
    {
        if ($cheque->isReconciled()) {
            return redirect()->route('admin.accounting.cheques.index')->with('error', 'امکان ویرایش چک وصول شده وجود ندارد.');
        }
        $clients = Module::isEnabled('Clients') ? Client::select('id', 'full_name', 'username')->get() : collect();
        $banks = Bank::all();
        return view('accounting::cheques.edit', compact('cheque', 'clients', 'banks'));
    }

    public function update(UpdateChequeRequest $request, Cheque $cheque)
    {
        if ($cheque->isReconciled()) {
            return redirect()->route('admin.accounting.cheques.index')->with('error', 'امکان ویرایش چک وصول شده وجود ندارد.');
        }

        // The validated data will now be correctly handled by the UpdateChequeRequest
        $cheque->update($request->validated());

        return redirect()->route('admin.accounting.cheques.index')->with('success', 'اطلاعات چک با موفقیت ویرایش شد.');
    }

    public function destroy(Cheque $cheque)
    {
        if ($cheque->isReconciled()) {
            return redirect()->route('admin.accounting.cheques.index')->with('error', 'امکان حذف چک وصول شده وجود ندارد.');
        }
        $cheque->delete();
        return redirect()->route('admin.accounting.cheques.index')->with('success', 'چک با موفقیت حذف شد.');
    }

    /**
     * Show the form for reconciling a cheque.
     */
    public function showReconcileForm(Cheque $cheque)
    {
        if ($cheque->isReconciled()) {
            return redirect()->route('admin.accounting.cheques.index')->with('error', 'این چک قبلا وصول شده است.');
        }
        $banks = Bank::all();
        return view('accounting::cheques.reconcile', compact('cheque', 'banks'));
    }

    /**
     * Reconcile a cheque and update bank balance.
     */
    public function reconcile(ReconcileChequeRequest $request, Cheque $cheque)
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($cheque, $data) {
                $bank = Bank::findOrFail($data['bank_id']);

                if ($data['status'] === 'passed') {
                    if ($cheque->type === 'received') {
                        $bank->balance += $cheque->amount;
                    } elseif ($cheque->type === 'issued') {
                        $allowNegative = (bool) AccountingSetting::getValue('banking.allow_negative_balance', false);
                        if (!$allowNegative && $bank->balance < $cheque->amount) {
                            throw new \Exception('موجودی حساب (' . number_format($bank->balance) . ' ریال) برای وصول این چک پرداختی کافی نمی‌باشد.');
                        }
                        $bank->balance -= $cheque->amount;
                    }
                    $bank->save();
                }

                $cheque->update([
                    'status' => $data['status'],
                    'reconciliation_date' => $data['reconciliation_date'],
                    'reconciled_bank_id' => $bank->id, // Store the bank used for reconciliation
                    'description' => $cheque->description . "\n" . "یادداشت وصول: " . ($data['description'] ?? ''),
                ]);

                // Update linked invoice status if applicable
                if ($cheque->invoice_id) {
                    $invoice = Invoice::find($cheque->invoice_id);
                    if ($invoice) {
                        if ($data['status'] === 'passed') {
                            $totalPassedCheques = Cheque::where('invoice_id', $invoice->id)->where('status', 'passed')->sum('amount');
                            $totalDocuments = $invoice->documents()->where('type', 'income')->sum('amount');

                            if ($totalPassedCheques + $totalDocuments >= $invoice->total_amount) {
                                $invoice->status = 'paid';
                            } else {
                                $invoice->status = 'partially_paid';
                            }
                        } elseif ($data['status'] === 'returned') {
                            $invoice->status = 'unpaid';
                        }
                        $invoice->save();
                    }
                }
            });

            return redirect()->route('admin.accounting.cheques.index')->with('success', 'وضعیت چک با موفقیت به‌روزرسانی شد.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    /**
     * Cancel the reconciliation of a cheque and revert financial effects.
     */
    public function cancelReconcile(Cheque $cheque)
    {
        // Ensure the cheque is actually reconciled before attempting to cancel
        if (!$cheque->isReconciled()) {
            return redirect()->route('admin.accounting.cheques.index')
                ->with('error', 'این چک وصول نشده است که بخواهید لغو وصول کنید.');
        }

        // Find the bank associated with the cheque's reconciliation
        if (!$cheque->reconciled_bank_id) {
            return redirect()->route('admin.accounting.cheques.index')
                ->with('error', 'اطلاعات حساب بانکی مرتبط با این چک برای لغو وصول موجود نیست.');
        }

        try {
            DB::transaction(function () use ($cheque) {
                $bank = Bank::findOrFail($cheque->reconciled_bank_id);

                if ($cheque->type === 'received') {
                    $allowNegative = (bool) AccountingSetting::getValue('banking.allow_negative_balance', false);
                    if (!$allowNegative && $bank->balance < $cheque->amount) {
                        throw new \Exception('موجودی حساب (' . number_format($bank->balance) . ' ریال) برای لغو وصول این چک دریافتی کافی نمی‌باشد.');
                    }
                    $bank->balance -= $cheque->amount;
                } elseif ($cheque->type === 'issued') {
                    $bank->balance += $cheque->amount;
                }
                $bank->save();

                // Revert cheque status and reconciliation date
                $cheque->update([
                    'status' => ($cheque->type === 'received' ? 'registered' : 'issued'), // Revert to initial status
                    'reconciliation_date' => null,
                    'reconciled_bank_id' => null,
                    'description' => str_replace("یادداشت وصول: ", "", $cheque->description), // Remove reconciliation note
                ]);

                // Update linked invoice status if applicable
                if ($cheque->invoice_id) {
                    $invoice = Invoice::find($cheque->invoice_id);
                    if ($invoice) {
                        $invoice->status = 'pending_review';
                        $invoice->save();
                    }
                }
            });

            return redirect()->route('admin.accounting.cheques.index')
                ->with('success', 'عملیات لغو وصول چک با موفقیت انجام شد.');
        } catch (\Exception $e) {
            return back()->withErrors(['msg' => $e->getMessage()]);
        }
    }
}
