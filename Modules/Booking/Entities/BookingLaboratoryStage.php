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
    public const STATUS_SKIPPED   = 'skipped';

    public const TYPE_STANDARD  = 'standard';
    public const TYPE_SELECTION = 'selection';
    public const TYPE_DECISION  = 'decision';

    protected $table = 'booking_laboratory_stages';

    protected $fillable = [
        'order_id',
        'parent_stage_id',
        'layer_depth',
        'revision_round',
        'branch_key',
        'branch_label',
        'stage_key',
        'stage_title',
        'stage_type',
        'offset_value',
        'offset_unit',
        'sort_order',
        'due_at',
        'is_completed',
        'is_skipped',
        'was_completed_late',
        'delay_minutes',
        'completed_at',
        'completed_by_user_id',
        'is_receive_stage',
        'config_snapshot',
        'selected_option_key',
        'selected_option_label',
        'selected_meta',
        'status',
        'notes',
    ];

    protected $casts = [
        'parent_stage_id'  => 'integer',
        'layer_depth'      => 'integer',
        'revision_round'   => 'integer',
        'offset_value'     => 'integer',
        'sort_order'       => 'integer',
        'due_at'             => 'datetime',
        'is_completed'       => 'boolean',
        'is_skipped'         => 'boolean',
        'was_completed_late' => 'boolean',
        'delay_minutes'      => 'integer',
        'completed_at'       => 'datetime',
        'is_receive_stage' => 'boolean',
        'config_snapshot'  => 'array',
        'selected_meta'    => 'array',
    ];

    /* ---------------- Relationships ---------------- */

    public function order(): BelongsTo
    {
        return $this->belongsTo(BookingLaboratoryOrder::class, 'order_id');
    }

    public function parentStage(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_stage_id');
    }

    public function childStages(): HasMany
    {
        return $this->hasMany(self::class, 'parent_stage_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    public function dailyLogs(): HasMany
    {
        return $this->hasMany(BookingLaboratoryDailyLog::class, 'stage_id');
    }

    /* ---------------- Stage Type & Multi-Layer Helpers ---------------- */

    public function isSelectionStage(): bool
    {
        return ($this->stage_type ?: self::TYPE_STANDARD) === self::TYPE_SELECTION;
    }

    public function isDecisionStage(): bool
    {
        return ($this->stage_type ?: self::TYPE_STANDARD) === self::TYPE_DECISION;
    }

    public function isStandardStage(): bool
    {
        return !$this->isSelectionStage() && !$this->isDecisionStage();
    }

    public function isOptionalStage(): bool
    {
        $cfg = is_array($this->config_snapshot) ? $this->config_snapshot : [];
        return !empty($cfg['is_optional']);
    }

    /**
     * Resolve selectable options for a selection stage.
     * Supports 'partners' source from BookingSetting or custom snapshot options.
     */
    public function getAvailableOptions(): array
    {
        $cfg = is_array($this->config_snapshot) ? $this->config_snapshot : [];
        return BookingSetting::resolveLaboratoryStageOptions($cfg);
    }

    /**
     * Resolve decision outcomes for a decision stage.
     */
    public function getAvailableOutcomes(): array
    {
        $cfg = is_array($this->config_snapshot) ? $this->config_snapshot : [];
        $outcomes = $cfg['outcomes'] ?? [];
        return is_array($outcomes) ? array_values($outcomes) : [];
    }

    public function getCompletedAtJalaliAttribute(): ?string
    {
        if (!$this->completed_at) {
            return null;
        }
        try {
            return Jalalian::fromCarbon($this->completed_at)->format('Y/m/d H:i');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /* ---------------- State, Delay & Helpers ---------------- */

    public function isOverdue(): bool
    {
        if ($this->is_completed || $this->is_skipped || !$this->due_at) {
            return false;
        }
        if ($this->offset_unit === 'hours') {
            return $this->due_at->isPast();
        }
        return $this->due_at->copy()->startOfDay()->lt(now()->startOfDay());
    }

    public function isDueToday(): bool
    {
        if ($this->is_completed || $this->is_skipped || !$this->due_at) {
            return false;
        }
        if ($this->offset_unit === 'hours') {
            return $this->due_at->isToday() && !$this->due_at->isPast();
        }
        return $this->due_at->isToday();
    }

    public function calculateDelayMinutes(?Carbon $at = null): int
    {
        if (!$this->due_at) {
            return 0;
        }

        $ref = $at ? $at->copy() : now();
        if ($this->offset_unit === 'hours') {
            if ($ref->lte($this->due_at)) {
                return 0;
            }
            return max(1, (int) $this->due_at->diffInMinutes($ref));
        }

        $dueDay = $this->due_at->copy()->startOfDay();
        $refDay = $ref->copy()->startOfDay();
        if ($refDay->lte($dueDay)) {
            return 0;
        }

        return ((int) $dueDay->diffInDays($refDay)) * 1440;
    }

    public static function formatDelayDuration(int $minutes, string $unit = 'days'): string
    {
        if ($minutes <= 0) {
            return '';
        }

        if ($unit === 'days') {
            $days = max(1, (int) round($minutes / 1440));
            return "{$days} روز";
        }

        if ($minutes < 60) {
            return "{$minutes} دقیقه";
        }

        $totalHours = intdiv($minutes, 60);
        if ($totalHours < 24) {
            return "{$totalHours} ساعت";
        }

        $days = intdiv($totalHours, 24);
        $remHours = $totalHours % 24;

        return $remHours > 0 ? "{$days} روز و {$remHours} ساعت" : "{$days} روز";
    }

    public function wasCompletedLate(): bool
    {
        if (!$this->is_completed || $this->is_skipped) {
            return false;
        }
        if ($this->was_completed_late) {
            return true;
        }
        return $this->completed_at ? $this->calculateDelayMinutes($this->completed_at) > 0 : false;
    }

    public function getCompletedDelayMinutesAttribute(): int
    {
        if (!$this->is_completed || $this->is_skipped) {
            return 0;
        }
        if (!is_null($this->delay_minutes)) {
            return (int) $this->delay_minutes;
        }
        return $this->completed_at ? $this->calculateDelayMinutes($this->completed_at) : 0;
    }

    public function getCompletedDelayLabelAttribute(): ?string
    {
        $minutes = $this->completed_delay_minutes;
        if ($minutes <= 0) {
            return null;
        }
        return self::formatDelayDuration($minutes, $this->offset_unit ?: 'days');
    }

    public function getLiveDelayLabelAttribute(): ?string
    {
        if (!$this->isOverdue()) {
            return null;
        }
        $minutes = $this->calculateDelayMinutes(now());
        if ($minutes <= 0) {
            return null;
        }
        return self::formatDelayDuration($minutes, $this->offset_unit ?: 'days');
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
        $now = now();
        $delayMinutes = $this->calculateDelayMinutes($now);
        $wasLate = $delayMinutes > 0;

        $this->update([
            'is_completed'         => true,
            'is_skipped'           => false,
            'was_completed_late'   => $wasLate,
            'delay_minutes'        => $delayMinutes,
            'completed_at'         => $now,
            'completed_by_user_id' => $userId ?: auth()->id(),
            'status'               => self::STATUS_COMPLETED,
            'notes'                => $note ?: $this->notes,
        ]);

        // If this is the receive stage, also mark the parent order as received
        if ($this->is_receive_stage && $this->order) {
            $this->order->update([
                'status'      => BookingLaboratoryOrder::STATUS_RECEIVED,
                'received_at' => $now,
            ]);
        }
    }

    public function markSkipped(?string $note = null, ?int $userId = null): void
    {
        $this->update([
            'is_completed'         => true,
            'is_skipped'           => true,
            'was_completed_late'   => false,
            'delay_minutes'        => 0,
            'completed_at'         => now(),
            'completed_by_user_id' => $userId ?: auth()->id(),
            'status'               => self::STATUS_SKIPPED,
            'notes'                => $note ?: $this->notes,
        ]);
    }

    public function markPending(?string $note = null): void
    {
        $this->update([
            'is_completed'         => false,
            'is_skipped'           => false,
            'was_completed_late'   => false,
            'delay_minutes'        => null,
            'completed_at'         => null,
            'completed_by_user_id' => null,
            'status'               => self::STATUS_PENDING,
            'notes'                => $note ?: $this->notes,
        ]);

        // If the parent order was marked as received, revert it back to in_progress
        if ($this->order && $this->order->isReceived()) {
            $this->order->update([
                'status'      => BookingLaboratoryOrder::STATUS_IN_PROGRESS,
                'received_at' => null,
            ]);
        }
    }
}
