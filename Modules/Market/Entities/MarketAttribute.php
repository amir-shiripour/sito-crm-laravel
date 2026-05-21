<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;

class MarketAttribute extends Model
{
    protected $table = 'market_attributes';

    // 💡 NEW: 'unit' اضافه شد
    protected $fillable = ['name', 'type', 'unit'];

    public function values()
    {
        return $this->hasMany(MarketAttributeValue::class, 'attribute_id');
    }
}
