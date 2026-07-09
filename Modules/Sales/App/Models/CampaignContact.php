<?php

namespace Modules\Sales\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignContact extends Model
{
    protected $table = 'sales_campaign_contacts';

    protected $fillable = [
        'campaign_id', 'client_id', 'assigned_to', 'assigned_role', 'name', 'phone', 'email',
        'status', 'source', 'added_at'
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
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
