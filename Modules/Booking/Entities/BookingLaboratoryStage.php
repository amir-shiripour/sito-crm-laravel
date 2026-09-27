<?php

namespace Modules\Booking\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;

class BookingLaboratoryStage extends Model
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_DUE_TODAY = 'due_today';
    public const STATUS_OVERDUE   = 'overdue';
    public const STATUS_COMPLETED = 'completed';

    protected $table = 'booking_laboratory_stages';

    protected $fillable = [
        'order_id',
        'stage_key',
        'stage_title',
        'offset_value',
        'offset_unit',
        'sort_order',
        'due_at',
        'is_completed',
        'completed_at',
        'completed_by_user_id',
        'is_receive_stage',
        'status',
        'notes',
    ];

    protected $casts = [
        'offset_value'     => 'integer',
        'sort_order'       => 'integer',
        'due_at'           => 'datetime',
        'is_completed'     => 'boolean',
        'completed_at'     => 'datetime',
        'is_receive_stage' => 'boolean',
    ];

    /* ---------------- Relationships ---------------- */

    public function order(): BelongsTo
    {
        return $this->belongsTo(BookingLaboratoryOrder::class, 'order_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    public function dailyLogs(): HasMany
    {
        return $this->hasMany(BookingLaboratoryDailyLog::class, 'stage_id');
    }

    /* ---------------- State & Helpers ---------------- */

    public function isOverdue(): bool
    {
        if ($this->is_completed) {
            return false;
        }
        return $this->due_at && $this->due_at->isPast() && !$this->isDueToday();
    }

    public function isDueToday(): bool
    {
        if ($this->is_completed || !$this->due_at) {
            return false;
        }
        return $this->due_at->isToday();
    }

    public function getDueAtJalaliAttribute(): string
    {
        if (!$this->due_at) return '';
        try {
            if ($this->offset_unit === 'hours') {
                return Jalalian::fromCarbon($this->due_at)->format('Y/m/d H:i');
            }
            return Jalalian::fromCarbon($this->due_at)->format('Y/m/d');
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function getDueDayJalaliAttribute(): string
    {
        if (!$this->due_at) return '';
        try {
            return Jalalian::fromCarbon($this->due_at)->format('l');
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function markCompleted(?string $note = null, ?int $userId = null): void
    {
        $this->update([
            'is_completed'         => true,
            'completed_at'         => now(),
            'completed_by_user_id' => $userId ?: auth()->id(),
            'status'               => self::STATUS_COMPLETED,
            'notes'                => $note ?: $this->notes,
        ]);

        // If this is the receive stage, also mark the parent order as received
        if ($this->is_receive_stage && $this->order) {
            $this->order->update([
                'status'      => BookingLaboratoryOrder::STATUS_RECEIVED,
                'received_at' => now(),
            ]);
        }
    }
}
