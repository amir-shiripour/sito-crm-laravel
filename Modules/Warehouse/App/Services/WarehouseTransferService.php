<?php

namespace Modules\Warehouse\App\Services;

use Illuminate\Support\Facades\DB;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseItem;
use Modules\Warehouse\Entities\WarehouseTransferRequest;
use Modules\Warehouse\Entities\WarehouseTransferRequestItem;

class WarehouseTransferService
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
     * Create a warehouse transfer / requisition (immediate FIFO transfer or pending request).
     */
    public function createTransfer(array $headerData, array $lines, bool $deliverImmediately = true): WarehouseTransferRequest
    {
        if (empty($lines)) {
            throw new \InvalidArgumentException('حداقل یک قلم کالا باید در فرم درخواست ثبت شود.');
        }

        return DB::transaction(function () use ($headerData, $lines, $deliverImmediately) {
            $requestDate = $this->purchaseService->parseDate($headerData['request_date'] ?? null) ?? now()->toDateString();
            $neededDate = $this->purchaseService->parseDate($headerData['needed_date'] ?? null);

            $sourceWarehouseId = !empty($headerData['source_warehouse_id'])
                ? (int)$headerData['source_warehouse_id']
                : Warehouse::inventory()->main()->active()->value('id');

            $destWarehouseId = (int)($headerData['destination_warehouse_id'] ?? 0);

            if (!$destWarehouseId) {
                throw new \InvalidArgumentException('انتخاب انبارک / انبار مقصد الزامی است.');
            }
            if (!$sourceWarehouseId) {
                throw new \RuntimeException('انبار مبدأ در سیستم یافت نشد.');
            }
            if ($sourceWarehouseId === $destWarehouseId) {
                throw new \InvalidArgumentException('انبار مبدأ و انبار مقصد نمی‌توانند یکسان باشند.');
            }

            $transfer = WarehouseTransferRequest::create([
                'transfer_number' => !empty($headerData['transfer_number'])
                    ? trim((string)$headerData['transfer_number'])
                    : $this->codeService->generateTransferRequestCode(),
                'request_number' => !empty($headerData['request_number']) ? trim((string)$headerData['request_number']) : null,
                'request_date' => $requestDate,
                'needed_date' => $neededDate,
                'source_warehouse_id' => $sourceWarehouseId,
                'destination_warehouse_id' => $destWarehouseId,
                'requester_user_id' => !empty($headerData['requester_user_id']) ? (int)$headerData['requester_user_id'] : null,
                'requester_name' => !empty($headerData['requester_name']) ? trim((string)$headerData['requester_name']) : null,
                'approved_by' => $deliverImmediately ? ($headerData['approved_by'] ?? auth()->id()) : null,
                'delivered_at' => $deliverImmediately ? now() : null,
                'status' => $deliverImmediately ? 'delivered' : 'pending',
                'notes' => !empty($headerData['notes']) ? trim((string)$headerData['notes']) : null,
                'created_by' => $headerData['created_by'] ?? auth()->id(),
            ]);

            $itemsCount = 0;
            $totalReqQty = 0.0;
            $totalDelivQty = 0.0;
            $totalCostValue = 0.0;

            foreach ($lines as $line) {
                $itemId = (int)($line['item_id'] ?? 0);
                $reqQty = (float)($line['quantity'] ?? $line['requested_quantity'] ?? 0);
                $lineNotes = !empty($line['notes']) ? trim((string)$line['notes']) : null;

                if ($itemId <= 0 || $reqQty <= 0) {
                    continue;
                }

                $item = WarehouseItem::findOrFail($itemId);
                $deliveredQty = 0.0;
                $lineCost = 0.0;

                if ($deliverImmediately) {
                    $transferRes = $this->valuationService->transferStockFifo(
                        $item,
                        $sourceWarehouseId,
                        $destWarehouseId,
                        $reqQty,
                        $transfer->id,
                        $transfer->transfer_number,
                        $lineNotes
                    );
                    $deliveredQty = (float)$transferRes['total_transferred'];
                    $lineCost = (float)$transferRes['total_cost_value'];
                }

                WarehouseTransferRequestItem::create([
                    'transfer_request_id' => $transfer->id,
                    'item_id' => $item->id,
                    'requested_quantity' => $reqQty,
                    'delivered_quantity' => $deliveredQty,
                    'total_cost_value' => $lineCost,
                    'notes' => $lineNotes,
                ]);

                $itemsCount++;
                $totalReqQty += $reqQty;
                $totalDelivQty += $deliveredQty;
                $totalCostValue += $lineCost;
            }

            if ($itemsCount === 0) {
                throw new \InvalidArgumentException('هیچ ردیف معتبری با تعداد بیشتر از صفر برای ثبت وجود نداشت.');
            }

            $transfer->update([
                'total_items_count' => $itemsCount,
                'total_requested_quantity' => $totalReqQty,
                'total_delivered_quantity' => $totalDelivQty,
                'total_cost_value' => $totalCostValue,
            ]);

            return $transfer->fresh(['items.item', 'sourceWarehouse', 'destinationWarehouse']);
        });
    }

    /**
     * Fulfill a pending transfer request using FIFO batch movement.
     */
    public function fulfillPendingTransfer(WarehouseTransferRequest $transfer, array $deliveredQuantities, ?string $notes = null): WarehouseTransferRequest
    {
        return DB::transaction(function () use ($transfer, $deliveredQuantities, $notes) {
            $lockedTransfer = WarehouseTransferRequest::where('id', $transfer->id)->lockForUpdate()->firstOrFail();

            if ($lockedTransfer->status !== 'pending') {
                throw new \RuntimeException('فقط درخواست‌های در وضعیت «در انتظار تایید و تحویل» قابل تحویل هستند.');
            }

            $sourceId = (int)$lockedTransfer->source_warehouse_id;
            $destId = (int)$lockedTransfer->destination_warehouse_id;

            if (!$sourceId || !$destId || $sourceId === $destId) {
                throw new \RuntimeException('انبار مبدأ یا مقصد این درخواست معتبر نیست.');
            }

            $lines = WarehouseTransferRequestItem::where('transfer_request_id', $lockedTransfer->id)->with('item')->get();
            $totalDelivered = 0.0;
            $totalCost = 0.0;

            foreach ($lines as $line) {
                $qtyToDeliver = isset($deliveredQuantities[$line->id])
                    ? (float)$deliveredQuantities[$line->id]
                    : (float)$line->requested_quantity;

                if ($qtyToDeliver <= 0) {
                    $line->update(['delivered_quantity' => 0, 'total_cost_value' => 0]);
                    continue;
                }

                $res = $this->valuationService->transferStockFifo(
                    $line->item,
                    $sourceId,
                    $destId,
                    $qtyToDeliver,
                    $lockedTransfer->id,
                    $lockedTransfer->transfer_number,
                    $line->notes
                );

                $line->update([
                    'delivered_quantity' => $res['total_transferred'],
                    'total_cost_value' => $res['total_cost_value'],
                ]);

                $totalDelivered += (float)$res['total_transferred'];
                $totalCost += (float)$res['total_cost_value'];
            }

            if ($totalDelivered <= 0) {
                throw new \InvalidArgumentException('تعداد تحویلی حداقل یک قلم کالا باید بیشتر از صفر باشد.');
            }

            $lockedTransfer->update([
                'status' => 'delivered',
                'approved_by' => auth()->id(),
                'delivered_at' => now(),
                'total_delivered_quantity' => $totalDelivered,
                'total_cost_value' => $totalCost,
                'notes' => $notes !== null && trim($notes) !== '' ? trim($notes) : $lockedTransfer->notes,
            ]);

            return $lockedTransfer->fresh(['items.item', 'sourceWarehouse', 'destinationWarehouse']);
        });
    }

    /**
     * Reject a pending transfer request without affecting stock.
     */
    public function rejectPendingTransfer(WarehouseTransferRequest $transfer, ?string $reason = null): WarehouseTransferRequest
    {
        return DB::transaction(function () use ($transfer, $reason) {
            $lockedTransfer = WarehouseTransferRequest::where('id', $transfer->id)->lockForUpdate()->firstOrFail();

            if ($lockedTransfer->status !== 'pending') {
                throw new \RuntimeException('فقط درخواست‌های در انتظار قابل رد کردن هستند.');
            }

            $newNotes = $lockedTransfer->notes;
            if (!empty($reason)) {
                $newNotes = trim(($newNotes ? $newNotes . ' | ' : '') . 'دلیل رد: ' . trim($reason));
            }

            $lockedTransfer->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'notes' => $newNotes,
            ]);

            return $lockedTransfer;
        });
    }

    /**
     * Cancel/reverse a transfer (if pending, marks cancelled; if delivered, reverses FIFO batches safely).
     */
    public function cancelTransfer(WarehouseTransferRequest $transfer, bool $deleteRecord = false): void
    {
        DB::transaction(function () use ($transfer, $deleteRecord) {
            $lockedTransfer = WarehouseTransferRequest::where('id', $transfer->id)->lockForUpdate()->firstOrFail();

            if ($lockedTransfer->status === 'cancelled') {
                if ($deleteRecord) {
                    $lockedTransfer->delete();
                }
                return;
            }

            if ($lockedTransfer->status === 'delivered') {
                $this->valuationService->reverseTransferredBatches($lockedTransfer->id);
            }

            $lockedTransfer->update([
                'status' => 'cancelled',
            ]);

            if ($deleteRecord) {
                $lockedTransfer->delete();
            }
        });
    }
}
