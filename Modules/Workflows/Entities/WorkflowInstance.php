<?php

namespace Modules\Workflows\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowInstance extends Model
{
    protected $table = 'workflow_instances';

    protected $fillable = [
        'workflow_id',
        'parent_instance_id',
        'related_type',
        'related_id',
        'current_stage_id',
        'current_node_id',
        'status',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const STATUS_ACTIVE    = 'ACTIVE';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_CANCELED  = 'CANCELED';

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function parentInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'parent_instance_id');
    }

    public function childInstances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class, 'parent_instance_id');
    }

    public function currentNode(): BelongsTo
    {
        return $this->belongsTo(WorkflowNode::class, 'current_node_id');
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class, 'current_stage_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WorkflowLog::class, 'instance_id');
    }
}
