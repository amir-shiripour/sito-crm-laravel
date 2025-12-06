<?php

namespace Modules\Workflows\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowAction extends Model
{
    protected $table = 'workflow_actions';

    protected $fillable = [
        'stage_id',
        'action_type',
        'config',
        'sort_order',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public const TYPE_CREATE_TASK       = 'CREATE_TASK';
    public const TYPE_CREATE_FOLLOWUP   = 'CREATE_FOLLOW_UP';
    public const TYPE_CREATE_REMINDER   = 'CREATE_REMINDER';
    public const TYPE_SEND_NOTIFICATION = 'SEND_NOTIFICATION';

    public function stage(): BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class, 'stage_id');
    }
}
