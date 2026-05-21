<?php

namespace Modules\Accounting\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Http\Requests\StoreInvoiceRequest;
use Modules\Accounting\App\Models\AccountingSetting;
use Modules\Accounting\App\Models\Bank;
use Modules\Accounting\App\Models\Invoice;
use Modules\Accounting\App\Models\Cheque;
use Modules\Clients\Entities\Client;
use Illuminate\Support\Facades\Storage;
use Morilog\Jalali\Jalalian;

class InvoiceController extends Controller
{
    public function __construct()
    {
        // Permissions will be checked via middleware on the route
    }

    public function index()
    {
        $invoices = Invoice::with('client')
            ->latest('issue_date')
            ->latest('id')
            ->paginate(20);

        $banks = Bank::where('status', 1)->get();

        return view('accounting::invoices.index', compact('invoices', 'banks'));
    }

    public function create()
    {
        $clients = Client::select('id', 'full_name', 'username')->get();

        // ۱. خواندن تنظیمات شماره‌گذاری
        $numberingMode = AccountingSetting::getValue('numbering.mode', 'auto');
        $nextInvoiceNumber = ($numberingMode === 'auto') ? Invoice::getNextInvoiceNumber() : '';

        // ۲. خواندن تنظیمات مالیات و گرد کردن
        $taxEnabled = (bool) AccountingSetting::getValue('tax.enabled', false);
        $defaultTaxRate = $taxEnabled ? AccountingSetting::getValue('tax.percentage', 0) : 0;

        $roundingMode = AccountingSetting::getValue('tax.rounding_mode', 'none');
        $roundingAmount = AccountingSetting::getValue('tax.rounding_amount', 1000);

        // ۳. خواندن لیست واحدها
        $units = AccountingSetting::getValue('units.list', ['عدد', 'کیلوگرم', 'متر', 'ساعت']);

        return view('accounting::invoices.create', compact('clients', 'numberingMode', 'nextInvoiceNumber', 'defaultTaxRate', 'units', 'roundingMode', 'roundingAmount'));
    }

    private function applyRounding($amount)
    {
        $roundingMode = AccountingSetting::getValue('tax.rounding_mode', 'none');
        $roundingAmount = (float) AccountingSetting::getValue('tax.rounding_amount', 1000);

        if ($roundingMode === 'none' || $roundingAmount <= 0) {
            return $amount;
        }

        if ($roundingMode === 'up') {
            return ceil($amount / $roundingAmount) * $roundingAmount;
        } elseif ($roundingMode === 'down') {
            return floor($amount / $roundingAmount) * $roundingAmount;
        }

        return $amount;
    }

    public function store(StoreInvoiceRequest $request)
    {
        // Generate invoice number before validation if mode is auto
        $numberingMode = AccountingSetting::getValue('numbering.mode', 'auto');
        if ($numberingMode === 'auto') {
            $request->merge(['invoice_number' => Invoice::getNextInvoiceNumber()]);
        }

        $validatedData = $request->validated();

        try {
            DB::transaction(function () use ($validatedData, $request) {
                $subtotal = 0;
                $totalDiscount = 0;
                $itemsWithTotalPrice = [];

                foreach ($validatedData['items'] as $item) {
                    $quantity = $item['quantity'];
                    $unitPrice = $item['unit_price'];
                    $discount = $item['discount'] ?? 0;

                    $totalPrice = $quantity * $unitPrice;
                    $subtotal += $totalPrice;
                    $totalDiscount += $discount;

                    $itemsWithTotalPrice[] = array_merge($item, ['total_price' => $totalPrice]);
                }

                $tax = $validatedData['tax'] ?? 0;

                $taxableAmount = $subtotal - $totalDiscount;
                $taxAmount = ($taxableAmount * $tax) / 100;
                $raw_total_amount = $taxableAmount + $taxAmount;

                $total_amount = $this->applyRounding($raw_total_amount);

                $invoice = Invoice::create([
                    'client_id' => $validatedData['client_id'],
                    'invoice_number' => $validatedData['invoice_number'],
                    'issue_date' => $validatedData['issue_date'],
                    'due_date' => $validatedData['due_date'] ?? null,
                    'subtotal' => $subtotal,
                    'discount' => $totalDiscount,
                    'tax' => $tax,
                    'total_amount' => $total_amount,
                    'status' => 'unpaid',
                    'notes' => $request->input('notes'),
                ]);

                $invoice->items()->createMany($itemsWithTotalPrice);
            });

            return redirect()->route('admin.accounting.invoices.index')
                ->with('success', 'صورت حساب با موفقیت صادر شد.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'خطا در صدور صورت حساب: ' . $e->getMessage()]);
        }
    }

    public function edit(Invoice $invoice)
    {
        if (in_array($invoice->status, ['paid', 'partially_paid', 'pending_review'])) {
            return redirect()->route('admin.accounting.invoices.index')->with('error', 'فاکتورهای پرداخت شده یا در جریان قابل ویرایش نیستند. ابتدا پرداخت را لغو کنید.');
        }

        $invoice->load('items');
        $clients = Client::select('id', 'full_name', 'username')->get();
        $units = AccountingSetting::getValue('units.list', ['عدد', 'کیلوگرم', 'متر', 'ساعت']);

        return view('accounting::invoices.edit', compact('invoice', 'clients', 'units'));
    }

    public function update(StoreInvoiceRequest $request, Invoice $invoice)
    {
        if (in_array($invoice->status, ['paid', 'partially_paid', 'pending_review'])) {
            return redirect()->route('admin.accounting.invoices.index')->with('error', 'فاکتورهای پرداخت شده یا در جریان قابل ویرایش نیستند.');
        }

        $validatedData = $request->validated();

        try {
            DB::transaction(function () use ($validatedData, $request, $invoice) {
                $subtotal = 0;
                $totalDiscount = 0;
                $itemsWithTotalPrice = [];

                foreach ($validatedData['items'] as $item) {
                    $quantity = $item['quantity'];
                    $unitPrice = $item['unit_price'];
                    $discount = $item['discount'] ?? 0;

                    $totalPrice = $quantity * $unitPrice;
                    $subtotal += $totalPrice;
                    $totalDiscount += $discount;

                    $itemsWithTotalPrice[] = array_merge($item, ['total_price' => $totalPrice]);
                }

                $tax = $validatedData['tax'] ?? 0;

                $taxableAmount = $subtotal - $totalDiscount;
                $taxAmount = ($taxableAmount * $tax) / 100;
                $raw_total_amount = $taxableAmount + $taxAmount;

                $total_amount = $this->applyRounding($raw_total_amount);

                $invoice->update([
                    'client_id' => $validatedData['client_id'],
                    'invoice_number' => $validatedData['invoice_number'],
                    'issue_date' => $validatedData['issue_date'],
                    'due_date' => $validatedData['due_date'] ?? null,
                    'subtotal' => $subtotal,
                    'discount' => $totalDiscount,
                    'tax' => $tax,
                    'total_amount' => $total_amount,
                    'notes' => $request->input('notes'),
                ]);

                // Delete old items and insert new ones
                $invoice->items()->delete();
                $invoice->items()->createMany($itemsWithTotalPrice);
            });

            return redirect()->route('admin.accounting.invoices.index')
                ->with('success', 'صورت حساب با موفقیت ویرایش شد.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'خطا در ویرایش صورت حساب: ' . $e->getMessage()]);
        }
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'documents.bank', 'cheques']);

        $sellerInfo = [
            'name' => AccountingSetting::getValue('appearance.seller_name', ''),
            'economic_number' => AccountingSetting::getValue('appearance.economic_number', ''),
            'registration_number' => AccountingSetting::getValue('appearance.registration_number', ''),
            'national_id' => AccountingSetting::getValue('appearance.national_id', ''),
            'province_city' => AccountingSetting::getValue('appearance.province_city', ''),
            'address' => AccountingSetting::getValue('appearance.address', ''),
            'postal_code' => AccountingSetting::getValue('appearance.postal_code', ''),
            'phone_fax' => AccountingSetting::getValue('appearance.phone_fax', ''),
            'custom_fields' => AccountingSetting::getValue('appearance.custom_fields', []),
        ];

        return view('accounting::invoices.show', compact('invoice', 'sellerInfo'));
    }

    public function print(Invoice $invoice)
    {
        $invoice->load(['client', 'items']);

        $sellerInfo = [
            'name' => AccountingSetting::getValue('appearance.seller_name', ''),
            'economic_number' => AccountingSetting::getValue('appearance.economic_number', ''),
            'registration_number' => AccountingSetting::getValue('appearance.registration_number', ''),
            'national_id' => AccountingSetting::getValue('appearance.national_id', ''),
            'province_city' => AccountingSetting::getValue('appearance.province_city', ''),
            'address' => AccountingSetting::getValue('appearance.address', ''),
            'postal_code' => AccountingSetting::getValue('appearance.postal_code', ''),
            'phone_fax' => AccountingSetting::getValue('appearance.phone_fax', ''),
            'custom_fields' => AccountingSetting::getValue('appearance.custom_fields', []),
            'stamp_signature_image' => AccountingSetting::getValue('appearance.stamp_signature_image', ''),
            'stamp_signature_width' => AccountingSetting::getValue('appearance.stamp_signature_width', null),
        ];

        $template = AccountingSetting::getValue('appearance.invoice_template', 'standard');

        if ($template === 'official') {
            return view('accounting::invoices.print_official', compact('invoice', 'sellerInfo'));
        }

        return view('accounting::invoices.print', compact('invoice', 'sellerInfo'));
    }

    public function pay(Request $request, Invoice $invoice)
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        // Sanitize amount before validation
        $amount = $request->input('amount');
        if ($amount) {
            $amount = str_replace($persian, $english, $amount);
            $amount = str_replace(',', '', $amount);
            $request->merge(['amount' => $amount]);
        }

        // Sanitize cheque_due_date before validation
        $chequeDueDate = $request->input('cheque_due_date');
        if ($chequeDueDate) {
            $chequeDueDate = str_replace($persian, $english, $chequeDueDate);
            $request->merge(['cheque_due_date' => $chequeDueDate]);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $invoice->total_amount,
            'payment_method' => 'required|string|in:cash,card,transfer,cheque',
            // Fields for standard payment
            'bank_id' => 'required_unless:payment_method,cheque|nullable|exists:accounting_banks,id',
            'reference_number' => 'nullable|string|max:255',
            'attachment' => 'nullable|image|max:2048', // 2MB Max
            // Fields for cheque payment (Fixed table name from accounting_cheques to cheques)
            'cheque_number' => 'required_if:payment_method,cheque|nullable|string|max:255|unique:cheques,cheque_number',
            'cheque_bank_name' => 'required_if:payment_method,cheque|nullable|string|max:255',
            'cheque_payee_name' => 'required_if:payment_method,cheque|nullable|string|max:255', // Added
            'cheque_due_date' => 'required_if:payment_method,cheque|nullable|date_format:Y/m/d',
            'cheque_sayyad_id' => 'nullable|string|size:16',
        ]);

        if ($invoice->status === 'paid') {
            return back()->withErrors(['msg' => 'این صورت حساب قبلا پرداخت شده است.']);
        }

        try {
            DB::transaction(function () use ($validated, $invoice, $request) {
                if ($validated['payment_method'] === 'cheque') {
                    // Create a cheque record
                    Cheque::create([
                        'invoice_id' => $invoice->id,
                        'client_id' => $invoice->client_id, // Assigned automatically from invoice
                        'type' => 'received', // Auto-filled
                        'amount' => $validated['amount'],
                        'issue_date' => now()->format('Y-m-d'), // Auto-filled with today's date
                        'due_date' => Jalalian::fromFormat('Y/m/d', $validated['cheque_due_date'])->toCarbon()->format('Y-m-d'),
                        'cheque_number' => $validated['cheque_number'],
                        'bank_name' => $validated['cheque_bank_name'],
                        'payee_name' => $validated['cheque_payee_name'], // Taken from user input
                        'sayyad_id' => $validated['cheque_sayyad_id'] ?? null,
                        'status' => 'registered', // Initial status
                        'description' => 'بابت فاکتور شماره ' . $invoice->invoice_number,
                    ]);

                    // Update invoice status to pending review or partially paid if we consider registered cheques as partial payment
                    $invoice->status = 'pending_review';
                    $invoice->save();

                } else {
                    // Standard payment (cash, card, transfer)
                    $attachmentPath = null;
                    if ($request->hasFile('attachment')) {
                        $attachmentPath = $request->file('attachment')->store('invoice_payments', 'public');
                    }

                    // Create a document for this payment
                    \Modules\Accounting\App\Models\Document::create([
                        'bank_id' => $validated['bank_id'],
                        'type' => 'income',
                        'amount' => $validated['amount'],
                        'document_date' => now(),
                        'description' => 'پرداخت برای صورت حساب شماره ' . $invoice->invoice_number,
                        'payment_method' => $validated['payment_method'],
                        'reference_number' => $validated['reference_number'] ?? null,
                        'attachment' => $attachmentPath,
                        'documentable_id' => $invoice->id,
                        'documentable_type' => Invoice::class,
                    ]);

                    // Update invoice status
                    $totalPaid = $invoice->documents()->where('type', 'income')->sum('amount');
                    if ($totalPaid >= $invoice->total_amount) {
                        $invoice->status = 'paid';
                    } else {
                        $invoice->status = 'partially_paid';
                    }
                    $invoice->save();

                    // Update bank balance
                    Bank::where('id', $validated['bank_id'])->increment('balance', $validated['amount']);
                }
            });

            if ($validated['payment_method'] === 'cheque') {
                return redirect()->route('admin.accounting.invoices.index')
                    ->with('success', 'چک دریافتی برای صورت حساب شماره ' . $invoice->invoice_number . ' ثبت شد و پس از وصول اعمال می‌گردد.');
            } else {
                return redirect()->route('admin.accounting.invoices.index')
                    ->with('success', 'پرداخت برای صورت حساب شماره ' . $invoice->invoice_number . ' با موفقیت ثبت شد.');
            }

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['msg' => 'خطا در ثبت پرداخت: ' . $e->getMessage()]);
        }
    }

    public function revertPayment(Invoice $invoice)
    {
        try {
            DB::transaction(function () use ($invoice) {
                $allowNegative = (bool) AccountingSetting::getValue('banking.allow_negative_balance', false);

                // 1. Revert standard document payments
                $documents = $invoice->documents()->where('type', 'income')->get();
                foreach ($documents as $doc) {
                    $bank = Bank::lockForUpdate()->find($doc->bank_id);
                    if ($bank) {
                        if (!$allowNegative && $bank->balance < $doc->amount) {
                            throw new \Exception("موجودی حساب {$bank->bank_name} برای لغو این پرداخت کافی نیست و امکان منفی شدن موجودی غیرفعال است.");
                        }
                        $bank->decrement('balance', $doc->amount);
                    }

                    if ($doc->attachment) {
                        Storage::disk('public')->delete($doc->attachment);
                    }
                    $doc->delete();
                }

                // 2. Revert Cheques
                $cheques = Cheque::where('invoice_id', $invoice->id)->get();
                foreach ($cheques as $cheque) {
                    if ($cheque->status === 'passed') {
                        $bank = Bank::lockForUpdate()->find($cheque->reconciled_bank_id);
                        if ($bank) {
                            if (!$allowNegative && $bank->balance < $cheque->amount) {
                                throw new \Exception("موجودی حساب {$bank->bank_name} برای لغو وصول چک مرتبط کافی نیست و امکان منفی شدن موجودی غیرفعال است.");
                            }
                            $bank->decrement('balance', $cheque->amount);
                        }
                    }
                    $cheque->delete();
                }

                // 3. Reset invoice status
                $invoice->status = 'unpaid';
                $invoice->save();
            });

            return redirect()->route('admin.accounting.invoices.index')
                ->with('success', 'عملیات پرداخت فاکتور با موفقیت لغو شد.');

        } catch (\Exception $e) {
            return back()->withErrors(['msg' => 'خطا در لغو پرداخت: ' . $e->getMessage()]);
        }
    }

    public function destroy(Invoice $invoice)
    {
        if (in_array($invoice->status, ['paid', 'partially_paid', 'pending_review'])) {
            return back()->withErrors(['msg' => 'فاکتورهای پرداخت شده یا در جریان قابل حذف نیستند. ابتدا پرداخت را لغو کنید.']);
        }

        try {
            DB::transaction(function () use ($invoice) {
                $invoice->items()->delete();
                $invoice->delete();
            });
            return redirect()->route('admin.accounting.invoices.index')
                ->with('success', 'فاکتور با موفقیت حذف شد.');
        } catch (\Exception $e) {
            return back()->withErrors(['msg' => 'خطا در حذف فاکتور: ' . $e->getMessage()]);
        }
    }

    public function quickStoreClient(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|unique:clients,phone',
            'username' => 'required|string|unique:clients,username',
        ]);

        $client = Client::create([
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'username' => $validated['username'],
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'مشتری جدید با موفقیت افزوده شد.',
            'client' => [
                'id' => $client->id,
                'full_name' => $client->full_name,
            ]
        ]);
    }
}
