<?php

namespace Modules\Sales\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class SalesCall extends Model
{
    protected $table = 'sales_calls';

    protected $fillable = [
        'client_id', 'campaign_id', 'deal_id', 'user_id', 'call_date',
        'call_time', 'duration_seconds', 'direction', 'status',
        'reason', 'result', 'next_action', 'next_action_date',
        'contact_phone', 'notes'
    ];

    protected $casts = [
        'call_date' => 'date',
        'next_action_date' => 'date',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(SalesDeal::class, 'deal_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Dynamic relationship if Clients module is available
    public function client()
    {
        if (class_exists(\Modules\Clients\Entities\Client::class)) {
            return $this->belongsTo(\Modules\Clients\Entities\Client::class, 'client_id');
        }
        return null;
    }
}
