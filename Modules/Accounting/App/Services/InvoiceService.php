<?php

namespace Modules\Accounting\App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Entities\Invoice;
use Modules\Accounting\App\Models\AccountingSetting;
use Morilog\Jalali\Jalalian;

class InvoiceService
{
    protected AccountingEngine $accountingEngine;

    public function __construct(AccountingEngine $accountingEngine)
    {
        $this->accountingEngine = $accountingEngine;
    }

    /**
     * Store a new invoice based on system settings.
     *
     * @param array $data
     * @return Invoice
     * @throws Exception
     */
    public function store(array $data): Invoice
    {
        $defaultStatus = AccountingSetting::get('invoice.default_status_on_create', 'draft');

        DB::beginTransaction();
        try {
            $invoiceData = collect($data)->except(['items', 'invoice_number'])->toArray();
            $invoiceData['status'] = $defaultStatus; // Set status from settings

            if (!isset($invoiceData['client_id'])) {
                throw new Exception("Client ID is missing.");
            }

            $invoice = Invoice::create($invoiceData);

            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $itemData['total'] = ($itemData['quantity'] ?? 0) * ($itemData['price'] ?? 0);
                    $invoice->items()->create($itemData);
                }
            }

            // If the default is to approve immediately, run the approval logic.
            if ($defaultStatus === 'approved') {
                $this->approve($invoice);
            }

            DB::commit();
            return $invoice;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing draft invoice.
     *
     * @param Invoice $invoice
     * @param array $data
     * @return Invoice
     * @throws Exception
     */
    public function update(Invoice $invoice, array $data): Invoice
    {
        if ($invoice->status !== 'draft') {
            throw new Exception('فاکتور تأیید شده قابل ویرایش نیست.');
        }

        DB::beginTransaction();
        try {
            $invoiceData = collect($data)->except(['items'])->toArray();
            $invoice->update($invoiceData);

            if (isset($data['items']) && is_array($data['items'])) {
                $invoice->items()->delete();
                foreach ($data['items'] as $itemData) {
                    $itemData['total'] = ($itemData['quantity'] ?? 0) * ($itemData['price'] ?? 0);
                    $invoice->items()->create($itemData);
                }
            }

            DB::commit();
            return $invoice->fresh();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Approve a draft invoice, assign a final number, and record the financial document.
     *
     * @param Invoice $invoice
     * @return Invoice
     * @throws Exception
     */
    public function approve(Invoice $invoice): Invoice
    {
        if ($invoice->status !== 'draft') {
            throw new Exception('فقط پیش‌فاکتورها قابل تایید نهایی هستند.');
        }

        // 1. Generate final invoice number using a more robust method
        $prefix = AccountingSetting::get('numbering.prefix', 'INV');
        $separator = AccountingSetting::get('numbering.separator', '-');
        $length = (int)AccountingSetting::get('numbering.length', 4);
        $includeYear = (bool)AccountingSetting::get('numbering.include_year', true);

        // Find the latest invoice with an official number to determine the next ID
        $lastOfficialInvoice = Invoice::whereNotNull('invoice_number')->orderBy('id', 'desc')->first();
        $nextId = 1;
        if ($lastOfficialInvoice && $lastOfficialInvoice->invoice_number) {
            // Safely extract the last numeric part using regex
            if (preg_match('/(\d+)$/', $lastOfficialInvoice->invoice_number, $matches)) {
                $nextId = (int)$matches[1] + 1;
            } else {
                // Fallback if regex fails (e.g., old format without numbers at the end)
                // This is a safe fallback to prevent errors.
                $nextId = Invoice::whereNotNull('invoice_number')->count() + 1;
            }
        }

        $paddedNumber = str_pad($nextId, $length, '0', STR_PAD_LEFT);
        $year = $includeYear ? Jalalian::fromCarbon($invoice->issue_date)->getYear() . $separator : '';
        $finalInvoiceNumber = $prefix . $separator . $year . $paddedNumber;

        // 2. Update status and final number
        $invoice->update([
            'status' => 'approved',
            'invoice_number' => $finalInvoiceNumber,
        ]);

        // 3. Record journal entry
        $this->recordFinancialDocument($invoice, $finalInvoiceNumber);

        return $invoice;
    }

    /**
     * Records the financial document for an approved invoice.
     *
     * @param Invoice $invoice
     * @param string $invoiceNumber The newly generated invoice number
     * @throws Exception
     */
    public function recordFinancialDocument(Invoice $invoice, string $invoiceNumber): void
    {
        $receivableCatId = AccountingSetting::get('defaults.receivables_category_id');
        $incomeCatId = AccountingSetting::get('defaults.sales_income_category_id');
        $taxCatId = AccountingSetting::get('defaults.sales_tax_category_id');
        $discountCatId = AccountingSetting::get('defaults.sales_discount_category_id');

        if (!$receivableCatId || !$incomeCatId) {
            throw new Exception('سرفصل‌های پیش‌فرض برای درآمد یا حساب‌های دریافتنی در تنظیمات مشخص نشده است.');
        }

        $taxableAmount = $invoice->subtotal - $invoice->discount;

        $rows = [];
        // 1. Debit Accounts Receivable (total amount customer owes)
        $rows[] = ['category_id' => $receivableCatId, 'debit' => $invoice->total, 'credit' => 0, 'description' => 'بدهی مشتری بابت فاکتور ' . $invoiceNumber];

        // 2. Debit Sales Discount (if applicable)
        if ($invoice->discount > 0) {
            if (!$discountCatId) {
                throw new Exception('سرفصل پیش‌فرض تخفیفات نقدی فروش در تنظیمات مشخص نشده است.');
            }
            $rows[] = ['category_id' => $discountCatId, 'debit' => $invoice->discount, 'credit' => 0, 'description' => 'تخفیف اعطایی فاکتور ' . $invoiceNumber];
        }

        // 3. Credit Sales Income (Gross Amount)
        $rows[] = ['category_id' => $incomeCatId, 'debit' => 0, 'credit' => $invoice->subtotal, 'description' => 'درآمد حاصل از فروش/خدمات (ناخالص)'];

        // Calculate actual monetary tax amount (tax stores percentage or amount)
        $taxAmount = $invoice->tax_amount ?? (($taxableAmount * $invoice->tax) / 100);

        // 4. Credit Sales Tax Payable
        if ($taxAmount > 0) {
            if (!$taxCatId) {
                throw new Exception('سرفصل پیش‌فرض مالیات بر ارزش افزوده فروش در تنظیمات مشخص نشده است.');
            }
            $rows[] = ['category_id' => $taxCatId, 'debit' => 0, 'credit' => $taxAmount, 'description' => 'مالیات بر ارزش افزوده'];
        }

        $this->accountingEngine->recordMultiLineDocument(
            $rows,
            'صدور فاکتور فروش ' . $invoiceNumber,
            $invoice,
            $invoice->issue_date->format('Y-m-d')
        );
    }

    /**
     * Update the status of an invoice based on its payments.
     *
     * @param Invoice $invoice
     */
    public function updateInvoiceStatus(Invoice $invoice): void
    {
        $invoice->load('document.transactions');

        if(!$invoice->document) {
            // No document, so no payments yet.
            return;
        }

        $totalPaid = $invoice->document->transactions()
            ->where('credit', '>', 0)
            ->sum('credit');

        $newStatus = 'approved'; // Default status for unpaid/approved invoices

        // Use a small tolerance for float comparisons
        if ($totalPaid >= ($invoice->total - 0.001)) {
            $newStatus = 'paid';
        } elseif ($totalPaid > 0.001) {
            $newStatus = 'partially_paid';
        }

        if ($invoice->status !== $newStatus) {
            $invoice->update(['status' => $newStatus]);
        }
    }
}
