<?php

namespace Modules\Sales\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class SalesFollowUp extends Model
{
    protected $table = 'sales_follow_ups';

    protected $fillable = [
        'client_id', 'campaign_id', 'call_id', 'user_id',
        'title', 'description', 'status', 'priority',
        'due_date', 'reminder_at', 'completed_at', 'tags'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'reminder_at' => 'datetime',
        'completed_at' => 'datetime',
        'tags' => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function call(): BelongsTo
    {
        return $this->belongsTo(SalesCall::class, 'call_id');
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
