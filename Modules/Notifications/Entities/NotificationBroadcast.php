<?php

namespace Modules\Notifications\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationBroadcast extends Model
{
    protected $table = 'notification_broadcasts';

    protected $fillable = [
        'created_by',
        'title',
        'message',
        'category',
        'priority',
        'severity',
        'target_type',
        'target_values',
        'channels',
        'action_url',
        'recipients_count',
        'sent_at',
    ];

    protected $casts = [
        'target_values' => 'array',
        'channels'      => 'array',
        'sent_at'       => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
