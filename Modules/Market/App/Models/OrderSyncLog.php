<?php

namespace Modules\Market\App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Modules\Clients\Entities\Client;

class OrderSyncLog extends Model
{
    protected $table = 'order_sync_logs';

    protected $fillable = [
        'order_id',
        'client_id',
        'field_key',
        'old_value',
        'new_value',
        'source',
        'status',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
