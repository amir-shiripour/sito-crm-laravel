<?php

namespace Modules\Notifications\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $table = 'notification_preferences';

    protected $fillable = [
        'user_id',
        'category',
        'channel_database',
        'channel_sms',
        'channel_mail',
    ];

    protected $casts = [
        'channel_database' => 'boolean',
        'channel_sms'      => 'boolean',
        'channel_mail'     => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
