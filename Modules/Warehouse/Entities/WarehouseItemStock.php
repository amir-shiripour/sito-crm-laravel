<?php

namespace Modules\Warehouse\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseItemStock extends Model
{
    protected $table = 'warehouse_item_stocks';

    protected $fillable = [
        'warehouse_id',
        'item_id',
        'quantity',
        'location_in_warehouse',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(WarehouseItem::class, 'item_id');
    }
}
