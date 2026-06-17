<?php

namespace Modules\Workflows\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowNode extends Model
{
    protected $table = 'workflow_nodes';

    protected $fillable = [
        'workflow_id',
        'name',
        'type',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    // Node Types constants
    public const TYPE_START         = 'START';
    public const TYPE_END           = 'END';
    public const TYPE_ACTION        = 'ACTION';
    public const TYPE_CONDITION     = 'CONDITION';
    public const TYPE_SUB_WORKFLOW  = 'SUB_WORKFLOW';

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function outgoingEdges(): HasMany
    {
        return $this->hasMany(WorkflowEdge::class, 'source_node_id');
    }

    public function incomingEdges(): HasMany
    {
        return $this->hasMany(WorkflowEdge::class, 'target_node_id');
    }
}
