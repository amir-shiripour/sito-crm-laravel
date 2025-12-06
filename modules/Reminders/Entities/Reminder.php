<?php

namespace Modules\Reminders\Entities;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    public const CHANNEL_IN_APP = 'IN_APP';
    public const CHANNEL_EMAIL  = 'EMAIL';
    public const CHANNEL_SMS    = 'SMS';
    public const CHANNEL_PUSH   = 'PUSH';

    protected $table = 'reminders';

    protected $fillable = [
        'user_id',
        'related_type',
        'related_id',
        'remind_at',
        'channel',
        'message',
        'is_sent',
        'sent_at',
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'sent_at'   => 'datetime',
        'is_sent'   => 'bool',
    ];
}
