<?php

namespace Modules\Sales\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class CampaignResult extends Model
{
    protected $table = 'sales_campaign_results';

    protected $fillable = [
        'campaign_id', 'contact_id', 'result_type', 'converted',
        'revenue', 'cost', 'notes', 'handled_by', 'occurred_at'
    ];

    protected $casts = [
        'converted' => 'boolean',
        'occurred_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CampaignContact::class, 'contact_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
