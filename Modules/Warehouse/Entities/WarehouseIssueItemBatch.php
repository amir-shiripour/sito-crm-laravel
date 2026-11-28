<?php

namespace Modules\Warehouse\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseIssueItemBatch extends Model
{
    protected $table = 'warehouse_issue_item_batches';

    protected $fillable = [
        'issue_item_id',
        'batch_id',
        'quantity_deducted',
        'unit_purchase_price',
        'total_cost',
    ];

    protected $casts = [
        'quantity_deducted' => 'decimal:2',
        'unit_purchase_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function issueItem(): BelongsTo
    {
        return $this->belongsTo(WarehouseIssueItem::class, 'issue_item_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(WarehouseItemBatch::class, 'batch_id');
    }
}
