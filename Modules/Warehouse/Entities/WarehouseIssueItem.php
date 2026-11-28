<?php

namespace Modules\Warehouse\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Warehouse\Traits\HasCreator;

class WarehouseIssueItem extends Model
{
    use HasCreator;

    protected $table = 'warehouse_issue_items';

    protected $fillable = [
        'issue_document_id',
        'item_id',
        'warehouse_id',
        'quantity',
        'unit_cost_avg',
        'total_cost',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost_avg' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(WarehouseIssueDocument::class, 'issue_document_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(WarehouseItem::class, 'item_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function batchDeductions(): HasMany
    {
        return $this->hasMany(WarehouseIssueItemBatch::class, 'issue_item_id');
    }
}
