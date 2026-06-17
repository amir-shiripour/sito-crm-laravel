<?php

namespace Modules\Market\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MarketOrderStatus extends Model
{
    use HasFactory;

    protected $table = 'market_order_statuses';

    protected $fillable = [
        'admin_label',
        'client_label',
        'color_class',
        'system_type',
        'show_to_client',
        'show_in_client_stepper',
        'show_in_admin_stepper',
        'sort_order',
        'is_active',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'market_order_status_id');
    }
}
