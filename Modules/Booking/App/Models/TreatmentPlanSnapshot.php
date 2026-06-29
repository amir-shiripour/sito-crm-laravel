<?php

namespace Modules\Booking\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class TreatmentPlanSnapshot extends Model
{
    use HasFactory;

    protected $table = 'treatment_plan_snapshots';

    protected $fillable = [
        'treatment_plan_id',
        'status_from',
        'status_to',
        'data',
        'changed_by',
    ];

    protected $casts = [
        'data' => 'array',
        'treatment_plan_id' => 'integer',
        'changed_by' => 'integer',
    ];

    public function treatmentPlan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TreatmentPlan::class, 'treatment_plan_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
