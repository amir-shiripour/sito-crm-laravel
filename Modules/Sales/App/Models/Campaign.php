<?php

namespace Modules\Sales\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Campaign extends Model
{
    protected $table = 'sales_campaigns';

    protected $fillable = [
        'name', 'type', 'status', 'goal', 'target_audience',
        'budget', 'actual_cost', 'start_date', 'end_date',
        'description', 'settings', 'created_by', 'assigned_to'
    ];

    protected $casts = [
        'target_audience' => 'array',
        'settings' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CampaignContact::class, 'campaign_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(CampaignResult::class, 'campaign_id');
    }

    public function calls(): HasMany
    {
        return $this->hasMany(SalesCall::class, 'campaign_id');
    }
}
