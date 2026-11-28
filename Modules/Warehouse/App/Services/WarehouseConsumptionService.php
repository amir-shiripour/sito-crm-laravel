<?php

namespace Modules\Warehouse\App\Services;

use Illuminate\Support\Facades\DB;
use Modules\Clients\Entities\Client;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseIssueDocument;
use Modules\Warehouse\Entities\WarehouseIssueItem;
use Modules\Warehouse\Entities\WarehouseIssueItemBatch;
use Modules\Warehouse\Entities\WarehouseItem;

class WarehouseConsumptionService
{
    protected WarehouseCodeService $codeService;
    protected WarehouseValuationService $valuationService;
    protected WarehousePurchaseService $purchaseService;

    public function __construct(
        WarehouseCodeService $codeService,
        WarehouseValuationService $valuationService,
        WarehousePurchaseService $purchaseService
    ) {
        $this->codeService = $codeService;
        $this->valuationService = $valuationService;
        $this->purchaseService = $purchaseService;
    }

    /**
     * Create a multi-item goods issue / consumption document with exact FIFO batch deduction.
     */
    public function createIssue(array $headerData, array $lines): WarehouseIssueDocument
    {
        if (empty($lines)) {
            throw new \InvalidArgumentException('حداقل یک قلم کالا باید در سند خروج و مصرف ثبت شود.');
        }

        return DB::transaction(function () use ($headerData, $lines) {
            $issueDate = $this->parseDate($headerData['issue_date'] ?? null) ?? now()->toDateString();
            $issueType = (string)($headerData['issue_type'] ?? WarehouseIssueDocument::TYPE_PATIENT_CONSUMPTION);
            $defaultWarehouseId = !empty($headerData['source_warehouse_id']) ? (int)$headerData['source_warehouse_id'] : null;

            $clientId = !empty($headerData['client_id']) ? (int)$headerData['client_id'] : null;
            $patientName = !empty($headerData['patient_name']) ? trim((string)$headerData['patient_name']) : null;
            $patientCaseNumber = !empty($headerData['patient_case_number']) ? trim((string)$headerData['patient_case_number']) : null;

            if ($clientId) {
                $client = Client::find($clientId);
                if ($client) {
                    $patientName = $patientName ?: $client->full_name;
                    $patientCaseNumber = $patientCaseNumber ?: $client->case_number;
                }
            }

            $document = WarehouseIssueDocument::create([
                'issue_number' => $this->codeService->generateIssueNumber(),
                'issue_date' => $issueDate,
                'issue_type' => $issueType,
                'source_warehouse_id' => $defaultWarehouseId,
                'client_id' => $clientId,
                'patient_name' => $patientName,
                'patient_case_number' => $patientCaseNumber,
                'consumer_user_id' => !empty($headerData['consumer_user_id']) ? (int)$headerData['consumer_user_id'] : null,
                'consumer_name' => !empty($headerData['consumer_name']) ? trim((string)$headerData['consumer_name']) : null,
                'status' => 'registered',
                'notes' => !empty($headerData['notes']) ? trim((string)$headerData['notes']) : null,
                'created_by' => $headerData['created_by'] ?? auth()->id(),
            ]);

            $itemsCount = 0;
            $totalQuantity = 0.0;
            $totalCostValue = 0.0;

            foreach ($lines as $line) {
                $itemId = (int)($line['item_id'] ?? 0);
                $qty = (float)($line['quantity'] ?? 0);
                $lineWarehouseId = !empty($line['warehouse_id']) ? (int)$line['warehouse_id'] : $defaultWarehouseId;

                if ($itemId <= 0 || $qty <= 0) {
                    continue;
                }

                if (!$lineWarehouseId) {
                    throw new \InvalidArgumentException('انتخاب انبار یا انبارک مبدأ برای تمامی ردیف‌ها الزامی است.');
                }

                $item = WarehouseItem::findOrFail($itemId);
                $warehouse = Warehouse::inventory()->active()->findOrFail($lineWarehouseId);

                $fifoResult = $this->valuationService->dischargeStockFifo($item, $warehouse->id, $qty);
                if ((float)$fifoResult['unfulfilled'] > 0.0001) {
                    $available = (float)$fifoResult['total_discharged'];
                    throw new \RuntimeException(
                        "موجودی کالای «{$item->name}» در انبار «{$warehouse->name}» کافی نیست (موجودی قابل برداشت: {$available}، مقدار درخواستی: {$qty})."
                    );
                }

                $lineCost = 0.0;
                foreach ($fifoResult['discharged_batches'] as $b) {
                    $lineCost += (float)$b['cost_basis'];
                }
                $unitCostAvg = $qty > 0 ? round($lineCost / $qty, 2) : 0.0;

                $issueItem = WarehouseIssueItem::create([
                    'issue_document_id' => $document->id,
                    'item_id' => $item->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => $qty,
                    'unit_cost_avg' => $unitCostAvg,
                    'total_cost' => round($lineCost, 2),
                    'notes' => !empty($line['notes']) ? trim((string)$line['notes']) : null,
                    'created_by' => $document->created_by,
                ]);

                foreach ($fifoResult['discharged_batches'] as $b) {
                    WarehouseIssueItemBatch::create([
                        'issue_item_id' => $issueItem->id,
                        'batch_id' => $b['batch_id'],
                        'quantity_deducted' => $b['quantity_taken'],
                        'unit_purchase_price' => $b['unit_purchase_price'],
                        'total_cost' => round((float)$b['cost_basis'], 2),
                    ]);
                }

                $itemsCount++;
                $totalQuantity += $qty;
                $totalCostValue += $lineCost;
            }

            if ($itemsCount === 0) {
                throw new \InvalidArgumentException('هیچ ردیف کالای معتبری برای ثبت خروج یافت نشد.');
            }

            $document->update([
                'items_count' => $itemsCount,
                'total_quantity' => $totalQuantity,
                'total_cost_value' => round($totalCostValue, 2),
            ]);

            return $document->fresh([
                'sourceWarehouse',
                'client',
                'consumerUser',
                'creator',
                'items.item',
                'items.warehouse',
                'items.batchDeductions.batch',
            ]);
        });
    }

    /**
     * Cancel an issue/consumption document and restore exact deducted quantities back to original FIFO batches.
     */
    public function cancelIssue(WarehouseIssueDocument $document): WarehouseIssueDocument
    {
        return DB::transaction(function () use ($document) {
            $locked = WarehouseIssueDocument::where('id', $document->id)
                ->with('items.batchDeductions')
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === 'cancelled') {
                throw new \RuntimeException('این سند قبلاً ابطال و موجودی آن بازگردانده شده است.');
            }

            foreach ($locked->items as $line) {
                $deductions = $line->batchDeductions->map(fn($d) => [
                    'batch_id' => $d->batch_id,
                    'quantity_deducted' => (float)$d->quantity_deducted,
                ])->all();

                $this->valuationService->reverseIssuedBatches($deductions, (int)$line->warehouse_id, (int)$line->item_id);
            }

            $locked->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
            ]);

            return $locked->fresh();
        });
    }

    public function parseDate(?string $dateInput): ?string
    {
        return $this->purchaseService->parseDate($dateInput);
    }
}
