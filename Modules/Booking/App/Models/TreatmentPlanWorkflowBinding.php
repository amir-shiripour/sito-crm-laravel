<?php

namespace Modules\Booking\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Workflows\Entities\Workflow;
use Modules\Workflows\Entities\WorkflowInstance;

class TreatmentPlanWorkflowBinding extends Model
{
    protected $table = 'treatment_plan_workflow_bindings';

    protected $fillable = [
        'treatment_plan_id',
        'workflow_id',
        'scope',
        'item_key',
        'tooth',
        'trigger_statuses',
        'previous_status',
        'min_amount',
        'auto_trigger',
        'is_active',
    ];

    protected $casts = [
        'trigger_statuses' => 'array',
        'auto_trigger' => 'boolean',
        'is_active' => 'boolean',
        'min_amount' => 'decimal:2',
    ];

    public function treatmentPlan(): BelongsTo
    {
        return $this->belongsTo(TreatmentPlan::class, 'treatment_plan_id');
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class, 'binding_id');
    }
}
