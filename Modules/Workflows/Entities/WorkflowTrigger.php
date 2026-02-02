<?php

namespace Modules\Workflows\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowTrigger extends Model
{
    protected $table = 'workflow_triggers';

    protected $fillable = [
        'workflow_id',
        'type',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public const TYPE_EVENT = 'EVENT';
    public const TYPE_SCHEDULE = 'SCHEDULE';
    public const TYPE_APPOINTMENT_STATUS = 'APPOINTMENT_STATUS';
    public const TYPE_APPOINTMENT_REMINDER = 'APPOINTMENT_REMINDER';

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }
}
