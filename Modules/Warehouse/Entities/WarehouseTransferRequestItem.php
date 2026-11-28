<?php

namespace Modules\Warehouse\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseTransferRequestItem extends Model
{
    protected $table = 'warehouse_transfer_request_items';

    protected $fillable = [
        'transfer_request_id',
        'item_id',
        'requested_quantity',
        'delivered_quantity',
        'total_cost_value',
        'notes',
    ];

    protected $casts = [
        'requested_quantity' => 'decimal:2',
        'delivered_quantity' => 'decimal:2',
        'total_cost_value' => 'decimal:2',
    ];

    public function transferRequest(): BelongsTo
    {
        return $this->belongsTo(WarehouseTransferRequest::class, 'transfer_request_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(WarehouseItem::class, 'item_id');
    }
}
