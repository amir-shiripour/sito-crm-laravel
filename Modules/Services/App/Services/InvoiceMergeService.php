<?php

namespace Modules\Services\App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Services\App\Http\Models\Invoice;
use Modules\Services\App\Http\Models\InvoiceItem;
use Modules\Services\App\Http\Models\Order;
use Modules\Services\App\Http\Models\Payment;
use Modules\Services\App\Http\Models\Status;
use Modules\Settings\Entities\Setting;
use Nwidart\Modules\Facades\Module;
use Throwable;

class InvoiceMergeService
{
    /**
     * Merge multiple invoices into a single comprehensive invoice.
     *
     * @param array<int> $invoiceIds
     * @param int|null $actorId
     * @return Invoice|null
     * @throws Exception
     */
    public function mergeInvoices(array $invoiceIds, ?int $actorId = null): ?Invoice
    {
        $invoiceIds = array_values(array_unique(array_filter(array_map('intval', $invoiceIds))));

        if (count($invoiceIds) < 2) {
            throw new Exception('برای ادغام حداقل دو فاکتور معتبر نیاز است.');
        }

        $invoices = Invoice::with(['items', 'status'])->whereIn('id', $invoiceIds)->get();

        if ($invoices->count() < 2) {
            throw new Exception('تعداد فاکتورهای یافت‌شده برای ادغام کافی نیست.');
        }

        // Validate all invoices belong to the same customer
        $customerIds = $invoices->pluck('customer_id')->filter()->unique();
        if ($customerIds->count() > 1) {
            throw new Exception('فقط فاکتورهای مربوط به یک مشتری قابل ادغام هستند.');
        }

        // Validate none are canceled or already merged
        foreach ($invoices as $inv) {
            if ($inv->isCanceled()) {
                throw new Exception("فاکتور شماره {$inv->invoice_number} لغو شده است و قابل ادغام نیست.");
            }
            if ($inv->isMerged()) {
                throw new Exception("فاکتور شماره {$inv->invoice_number} قبلاً ادغام شده است.");
            }
            if ($inv->isPaid()) {
                throw new Exception("فاکتور شماره {$inv->invoice_number} قبلاً پرداخت شده است.");
            }
        }

        $firstInvoice = $invoices->first();
        $customerId = $firstInvoice->customer_id;
        $clientName = $firstInvoice->client_name;
        $clientPhone = $firstInvoice->client_phone;
        $clientEmail = $firstInvoice->client_email;
        $currency = $firstInvoice->currency ?? Setting::where('key', 'currency')->value('value') ?? 'toman';

        // Issue date and Due date determination
        $issueDate = $invoices->pluck('issue_date')->filter()->min() ?? now();
        $dueDate = $invoices->pluck('due_date')->filter()->min() ?? $firstInvoice->due_date;

        $pendingStatus = Status::where('type', 'payment')->where('name', 'در انتظار پرداخت')->first()
            ?? Status::where('type', 'invoice')->where('name', 'در انتظار پرداخت')->first()
            ?? Status::where('type', 'payment')->first();

        $mergedStatus = Status::where('type', 'invoice')->where('name', 'ادغام شده')->first()
            ?? Status::where('type', 'payment')->where('name', 'ادغام شده')->first();

        $preparedItems = [];
        $subtotal = 0;
        $totalDiscount = 0;
        $totalTax = 0;

        foreach ($invoices as $inv) {
            $invNumber = $inv->invoice_number ?: (string)$inv->id;
            foreach ($inv->items as $item) {
                $itemMeta = is_array($item->meta) ? $item->meta : (json_decode($item->meta, true) ?? []);
                $packageGroupId = $itemMeta['_packageGroupId'] ?? $itemMeta['package_group_id'] ?? ('merged_inv_' . $inv->id);
                $packageTitle = $itemMeta['_packageTitle'] ?? $itemMeta['package_title'] ?? ('اقلام فاکتور ' . $invNumber);

                $itemMeta['_isMerged'] = true;
                $itemMeta['_packageGroupId'] = $packageGroupId;
                $itemMeta['_packageTitle'] = $packageTitle;
                $itemMeta['original_invoice_id'] = $inv->id;
                $itemMeta['original_item_id'] = $item->id;

                $itemSubtotal = ($item->unit_price * (float)$item->quantity);
                $itemDiscount = (int)$item->discount;
                $itemTax = (int)$item->tax_amount;
                $itemTotal = (int)$item->total ?: max(0, $itemSubtotal - $itemDiscount + $itemTax);

                $subtotal += $itemSubtotal;
                $totalDiscount += $itemDiscount;
                $totalTax += $itemTax;

                $preparedItems[] = [
                    'service_id' => $item->service_id,
                    'custom_service_name' => $item->custom_service_name,
                    'description' => $item->description,
                    'unit' => $item->unit ?? 'عدد',
                    'quantity' => (float)$item->quantity,
                    'unit_price' => (int)$item->unit_price,
                    'discount' => $itemDiscount,
                    'tax_percent' => (float)$item->tax_percent,
                    'tax_amount' => $itemTax,
                    'total' => $itemTotal,
                    'meta' => $itemMeta,
                ];
            }
        }

        $grandTotal = max(0, $subtotal + $totalTax - $totalDiscount);

        // Apply rounding settings if configured
        $settings = Setting::pluck('value', 'key')->toArray();
        $roundingMode = $settings['services_rounding_mode'] ?? 'none';
        $roundingFactor = (int)($settings['services_rounding_factor'] ?? 1000);
        $roundedGrandTotal = $grandTotal;
        $roundingMeta = null;

        if ($roundingMode !== 'none' && $roundingFactor > 0) {
            $diff = 0;
            switch ($roundingMode) {
                case 'nearest':
                    $roundedGrandTotal = (int)(round($grandTotal / $roundingFactor) * $roundingFactor);
                    $diff = $roundedGrandTotal - $grandTotal;
                    break;
                case 'up':
                    $roundedGrandTotal = (int)(ceil($grandTotal / $roundingFactor) * $roundingFactor);
                    $diff = $roundedGrandTotal - $grandTotal;
                    break;
                case 'down':
                    $roundedGrandTotal = (int)(floor($grandTotal / $roundingFactor) * $roundingFactor);
                    $diff = $roundedGrandTotal - $grandTotal;
                    break;
            }
            $roundingMeta = [
                'mode' => $roundingMode,
                'factor' => $roundingFactor,
                'original_total' => $grandTotal,
                'difference' => $diff,
            ];
        }

        $newInvoice = null;

        DB::transaction(function () use (
            &$newInvoice, $customerId, $firstInvoice, $pendingStatus, $mergedStatus,
            $clientName, $clientPhone, $clientEmail, $issueDate, $dueDate,
            $subtotal, $totalDiscount, $totalTax, $roundedGrandTotal, $currency,
            $actorId, $preparedItems, $invoices, $invoiceIds, $roundingMeta
        ) {
            $meta = [
                'is_merged_invoice' => true,
                'merged_from_invoice_ids' => $invoiceIds,
                'auto_merged' => true,
            ];
            if ($roundingMeta) {
                $meta['rounding'] = $roundingMeta;
            }

            $notesList = $invoices->pluck('invoice_number')->filter()->implode(', ');
            $notes = "فاکتور تجمیعی صادر شده از ادغام فاکتورهای: " . ($notesList ?: implode(', ', $invoiceIds));

            $newInvoice = Invoice::create([
                'customer_id' => $customerId,
                'service_id' => $firstInvoice->service_id,
                'status_id' => $pendingStatus?->id ?? $firstInvoice->status_id,
                'client_name' => $clientName,
                'client_phone' => $clientPhone,
                'client_email' => $clientEmail,
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $totalTax,
                'total' => $roundedGrandTotal,
                'paid_amount' => 0,
                'currency' => $currency,
                'created_by' => $actorId ?: (Auth::id() ?: ($firstInvoice->created_by ?: 1)),
                'notes' => $notes,
                'meta' => $meta,
            ]);

            // Save merged items
            foreach ($preparedItems as $itemData) {
                $itemData['invoice_id'] = $newInvoice->id;
                InvoiceItem::create($itemData);
            }

            // Move payments & orders from source invoices to the new merged invoice
            Payment::whereIn('invoice_id', $invoiceIds)->update(['invoice_id' => $newInvoice->id]);
            Order::whereIn('invoice_id', $invoiceIds)->update(['invoice_id' => $newInvoice->id]);

            if (Module::has('Market') && Module::isEnabled('Market')) {
                if (Schema::hasTable('market_orders') && Schema::hasColumn('market_orders', 'source_invoice_id')) {
                    \Modules\Market\Entities\MarketOrder::whereIn('source_invoice_id', $invoiceIds)
                        ->update(['source_invoice_id' => $newInvoice->id]);
                }
            }

            // Update source invoices status and meta
            foreach ($invoices as $sourceInv) {
                $sMeta = is_array($sourceInv->meta) ? $sourceInv->meta : (json_decode($sourceInv->meta, true) ?? []);
                unset($sMeta['is_merged_invoice']);
                $sMeta['was_merged_into'] = $newInvoice->id;
                $sourceInv->update([
                    'status_id' => $mergedStatus?->id ?? $sourceInv->status_id,
                    'meta' => $sMeta,
                ]);
            }

            // Recalculate payment status of new invoice if payments were transferred
            $newInvoice->updatePaymentStatus(false);
        });

        Log::info("[InvoiceMergeService] Successfully merged invoices [" . implode(',', $invoiceIds) . "] into new Invoice #{$newInvoice->id} ({$newInvoice->invoice_number})");

        // Trigger workflow event for the newly created merged invoice
        if (class_exists(\Modules\Workflows\Services\WorkflowEngine::class)) {
            try {
                app(\Modules\Workflows\Services\WorkflowEngine::class)->start('invoice_created', 'INVOICE', $newInvoice->id, [
                    'is_merged' => true,
                    'merged_from' => $invoiceIds,
                ]);
            } catch (Throwable $e) {
                Log::error("[InvoiceMergeService] Error triggering workflow for merged invoice #{$newInvoice->id}: " . $e->getMessage());
            }
        }

        return $newInvoice;
    }

    /**
     * Check if auto-merging is enabled and if concurrent invoices exist for the same customer.
     * If so, merge them immediately into one comprehensive invoice.
     *
     * @param Invoice $triggerInvoice
     * @return Invoice|null
     */
    public function checkAndAutoMergeConcurrentInvoices(Invoice $triggerInvoice): ?Invoice
    {
        try {
            // 1. Check if auto merge setting is enabled
            $autoMergeEnabled = Setting::where('key', 'services_auto_merge_concurrent_invoices')->value('value');
            if ($autoMergeEnabled !== '1' && $autoMergeEnabled !== 1 && $autoMergeEnabled !== true) {
                return null;
            }

            // Skip if trigger invoice is proforma or already merged or canceled
            if ($triggerInvoice->proforma_invoice_number || $triggerInvoice->isMerged() || $triggerInvoice->isCanceled()) {
                return null;
            }

            if (!$triggerInvoice->customer_id) {
                return null;
            }

            $issueDate = $triggerInvoice->issue_date ? Carbon::parse($triggerInvoice->issue_date)->toDateString() : null;
            $dueDate = $triggerInvoice->due_date ? Carbon::parse($triggerInvoice->due_date)->toDateString() : null;

            if (!$issueDate || !$dueDate) {
                return null;
            }

            // 2. Find eligible concurrent invoices for the same customer
            // Must have matching issue_date and due_date, non-proforma, not paid, not canceled, not merged
            $query = Invoice::where('customer_id', $triggerInvoice->customer_id)
                ->whereNull('proforma_invoice_number')
                ->whereDate('issue_date', $issueDate)
                ->whereDate('due_date', $dueDate)
                ->where(function ($q) {
                    $q->whereNull('paid_at')
                      ->orWhere('paid_amount', '<', DB::raw('total'));
                });

            $eligibleInvoices = $query->get()->filter(function (Invoice $inv) {
                return !$inv->isCanceled() && !$inv->isMerged() && !$inv->isPaid();
            });

            if ($eligibleInvoices->count() < 2) {
                return null;
            }

            $invoiceIdsToMerge = $eligibleInvoices->pluck('id')->all();

            Log::info("[InvoiceMergeService] Found {$eligibleInvoices->count()} concurrent invoices for customer #{$triggerInvoice->customer_id} on date {$issueDate} & due {$dueDate}. Merging: [" . implode(',', $invoiceIdsToMerge) . "]");

            return $this->mergeInvoices($invoiceIdsToMerge, $triggerInvoice->created_by);
        } catch (Throwable $e) {
            Log::error("[InvoiceMergeService] Auto-merge failed for invoice #{$triggerInvoice->id}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            return null;
        }
    }
}
