<?php

namespace Modules\Sales\App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesPipeline extends Model
{
    protected $table = 'sales_pipelines';

    protected $fillable = [
        'name', 'color', 'order', 'is_won', 'is_lost'
    ];

    protected $casts = [
        'is_won' => 'boolean',
        'is_lost' => 'boolean',
    ];
}
