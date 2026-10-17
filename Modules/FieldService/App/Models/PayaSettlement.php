<?php

namespace Modules\FieldService\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\FieldService\App\Enums\SettlementStatus;

class PayaSettlement extends Model
{
    use HasFactory;

    protected $table = 'field_service_paya_settlements';

    protected $fillable = [
        'technician_id',
        'tracking_code',
        'amount',
        'shaba_number',
        'account_owner_name',
        'status',
        'batch_reference',
        'requested_at',
        'processed_at',
        'approved_by',
        'notes',
    ];

    protected $casts = [
        'amount'       => 'float',
        'status'       => SettlementStatus::class,
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class, 'technician_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function generateTrackingCode(): string
    {
        return 'PAYA-' . date('ymd') . '-' . mt_rand(10000, 99999);
    }
}
