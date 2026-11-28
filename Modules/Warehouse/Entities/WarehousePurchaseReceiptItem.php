<?php

namespace Modules\Warehouse\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehousePurchaseReceiptItem extends Model
{
    protected $table = 'warehouse_purchase_receipt_items';

    protected $fillable = [
        'purchase_receipt_id',
        'item_id',
        'warehouse_id',
        'batch_id',
        'quantity',
        'unit_price',
        'total_price',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function purchaseReceipt(): BelongsTo
    {
        return $this->belongsTo(WarehousePurchaseReceipt::class, 'purchase_receipt_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(WarehouseItem::class, 'item_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(WarehouseItemBatch::class, 'batch_id');
    }
}
