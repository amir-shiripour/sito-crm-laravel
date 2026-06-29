<?php

namespace Modules\ContractForge\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractRule extends Model
{
    protected $table = 'contract_rules';

    protected $fillable = [
        'template_id',
        'name',
        'entity_type',
        'trigger_event',
        'trigger_statuses',
        'conditions',
        'priority',
        'is_active',
        'auto_create',
        'prevent_duplicate',
    ];

    protected $casts = [
        'trigger_statuses' => 'array',
        'conditions' => 'array',
        'is_active' => 'boolean',
        'auto_create' => 'boolean',
        'prevent_duplicate' => 'boolean',
        'priority' => 'integer',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ContractTemplate::class, 'template_id');
    }
}
