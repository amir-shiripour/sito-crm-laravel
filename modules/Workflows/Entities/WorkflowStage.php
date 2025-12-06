<?php

namespace Modules\Workflows\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowStage extends Model
{
    protected $table = 'workflow_stages';

    protected $fillable = [
        'workflow_id',
        'name',
        'description',
        'sort_order',
        'is_initial',
        'is_final',
    ];

    protected $casts = [
        'is_initial' => 'bool',
        'is_final'   => 'bool',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(WorkflowAction::class, 'stage_id')->orderBy('sort_order');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class, 'current_stage_id');
    }
}
