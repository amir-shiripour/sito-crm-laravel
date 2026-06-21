<?php

namespace Modules\Workflows\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowLog extends Model
{
    protected $table = 'workflow_logs';

    protected $fillable = [
        'instance_id',
        'stage_id',
        'from_node_id',
        'to_node_id',
        'transition_type',
        'action_type',
        'data',
        'run_at',
        'user_id',
    ];

    protected $casts = [
        'data'   => 'array',
        'run_at' => 'datetime',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'instance_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class, 'stage_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
