<?php

namespace Modules\Booking\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Morilog\Jalali\Jalalian;

class BookingLaboratoryDailyLog extends Model
{
    protected $table = 'booking_laboratory_daily_logs';

    protected $fillable = [
        'order_id',
        'stage_id',
        'log_date',
        'needs_followup',
        'followup_result',
        'operator_user_id',
    ];

    protected $casts = [
        'log_date'       => 'date',
        'needs_followup' => 'boolean',
    ];

    /* ---------------- Relationships ---------------- */

    public function order(): BelongsTo
    {
        return $this->belongsTo(BookingLaboratoryOrder::class, 'order_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(BookingLaboratoryStage::class, 'stage_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_user_id');
    }

    public function getLogDateJalaliAttribute(): string
    {
        if (!$this->log_date) return '';
        try {
            return Jalalian::fromCarbon($this->log_date)->format('Y/m/d');
        } catch (\Throwable $e) {
            return '';
        }
    }
}
