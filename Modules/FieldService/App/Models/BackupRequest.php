<?php

namespace Modules\FieldService\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupRequest extends Model
{
    use HasFactory;

    protected $table = 'field_service_backup_requests';

    protected $fillable = [
        'order_id',
        'primary_technician_id',
        'assistant_technician_id',
        'reason',
        'notes',
        'status',
        'assistant_labor_share_amount',
        'requested_at',
        'responded_at',
    ];

    protected $casts = [
        'assistant_labor_share_amount' => 'float',
        'requested_at'                 => 'datetime',
        'responded_at'                 => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'order_id');
    }

    public function primaryTechnician(): BelongsTo
    {
        return $this->belongsTo(Technician::class, 'primary_technician_id');
    }

    public function assistantTechnician(): BelongsTo
    {
        return $this->belongsTo(Technician::class, 'assistant_technician_id');
    }
}
