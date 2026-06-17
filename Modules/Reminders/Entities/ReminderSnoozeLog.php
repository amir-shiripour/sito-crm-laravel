<?php

namespace Modules\Reminders\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderSnoozeLog extends Model
{
    // این جدول فقط برای درج است، بنابراین نیازی به updated_at نیست
    const UPDATED_AT = null;

    protected $table = 'reminder_snooze_logs';

    protected $fillable = [
        'reminder_id',
        'user_id',
        'original_remind_at',
        'snoozed_to',
        'duration_key',
        'duration_minutes',
        'reason',
        'snooze_sequence',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'original_remind_at' => 'datetime',
        'snoozed_to'         => 'datetime',
        'created_at'         => 'datetime',
    ];

    /**
     * یادآوری مربوط به این لاگ تعویق.
     */
    public function reminder(): BelongsTo
    {
        return $this->belongsTo(Reminder::class);
    }

    /**
     * کاربری که یادآوری را به تعویق انداخته است.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
