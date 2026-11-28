<?php

namespace Modules\Warehouse\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Warehouse\Traits\HasCreator;

class WarehouseItemBatch extends Model
{
    use HasCreator;

    protected $table = 'warehouse_item_batches';

    protected $fillable = [
        'item_id',
        'warehouse_id',
        'purchase_receipt_id',
        'transfer_request_id',
        'source_batch_id',
        'batch_code',
        'purchase_date',
        'unit_purchase_price',
        'initial_quantity',
        'remaining_quantity',
        'expiry_date',
        'supplier',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'expiry_date' => 'date',
        'unit_purchase_price' => 'decimal:2',
        'initial_quantity' => 'decimal:2',
        'remaining_quantity' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(WarehouseItem::class, 'item_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function purchaseReceipt(): BelongsTo
    {
        return $this->belongsTo(WarehousePurchaseReceipt::class, 'purchase_receipt_id');
    }

    public function transferRequest(): BelongsTo
    {
        return $this->belongsTo(WarehouseTransferRequest::class, 'transfer_request_id');
    }

    public function sourceBatch(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_batch_id');
    }

    public function getConsumedQuantityAttribute(): float
    {
        return max(0, (float)$this->initial_quantity - (float)$this->remaining_quantity);
    }

    /**
     * Total cost basis of remaining stock in this batch
     */
    public function getCostValueAttribute(): float
    {
        return (float)$this->remaining_quantity * (float)$this->unit_purchase_price;
    }

    /**
     * Total current market value of remaining stock in this batch
     */
    public function getMarketValueAttribute(): float
    {
        $currentPrice = (float)($this->item->current_price ?? $this->unit_purchase_price);
        return (float)$this->remaining_quantity * $currentPrice;
    }

    /**
     * Unrealized profit margin of this batch
     */
    public function getProfitMarginAttribute(): float
    {
        return $this->market_value - $this->cost_value;
    }

    /**
     * Profit margin percentage
     */
    public function getProfitPercentageAttribute(): float
    {
        $buyPrice = (float)$this->unit_purchase_price;
        if ($buyPrice <= 0) {
            return 0.0;
        }
        $currentPrice = (float)($this->item->current_price ?? $buyPrice);
        return round((($currentPrice - $buyPrice) / $buyPrice) * 100, 1);
    }
}
