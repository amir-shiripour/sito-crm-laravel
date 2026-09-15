<?php

namespace Modules\Notifications\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationUserSetting extends Model
{
    protected $table = 'notification_user_settings';

    protected $fillable = [
        'user_id',
        'sound_enabled',
        'quiet_hours_enabled',
        'quiet_hours_start',
        'quiet_hours_end',
    ];

    protected $casts = [
        'sound_enabled'       => 'boolean',
        'quiet_hours_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if currently within quiet hours for this user
     */
    public function isInQuietHours(): bool
    {
        if (! $this->quiet_hours_enabled || ! $this->quiet_hours_start || ! $this->quiet_hours_end) {
            return false;
        }

        $now = now()->format('H:i:s');
        $start = $this->quiet_hours_start;
        $end = $this->quiet_hours_end;

        if ($start <= $end) {
            // e.g. 01:00 to 06:00
            return $now >= $start && $now <= $end;
        } else {
            // overnight e.g. 22:00 to 08:00
            return $now >= $start || $now <= $end;
        }
    }
}
