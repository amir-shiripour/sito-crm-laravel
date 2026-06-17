<?php

namespace Modules\Market\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class OrderMeta extends Model
{
    protected $table = 'market_order_meta';

    protected $fillable = ['order_id', 'key', 'value'];

    public $timestamps = false;

    public function scopeByKeys(Builder $query, array $keys): Builder
    {
        return $query->whereIn('key', $keys);
    }
}
