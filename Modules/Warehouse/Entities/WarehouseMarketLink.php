<?php

namespace Modules\Warehouse\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseMarketLink extends Model
{
    protected $table = 'warehouse_market_links';

    protected $fillable = [
        'warehouse_id',
        'market_warehouse_id',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Get the related Market warehouse entity if Market module is installed and loaded.
     */
    public function getMarketWarehouseAttribute()
    {
        if (class_exists(\Modules\Market\Entities\Warehouse::class)) {
            return \Modules\Market\Entities\Warehouse::with('vendor')->find($this->market_warehouse_id);
        }
        return null;
    }
}
