<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;

class MarketAttributeValue extends Model
{
    protected $table = 'market_attribute_values';
    protected $fillable = ['attribute_id', 'value', 'meta_value']; // meta_value برای نگهداری کد رنگ (Hex)

    public function attribute()
    {
        return $this->belongsTo(MarketAttribute::class, 'attribute_id');
    }
}
