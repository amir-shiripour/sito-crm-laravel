<?php

namespace Modules\Sms\Entities;

use Illuminate\Database\Eloquent\Model;

class SmsSetting extends Model
{
    protected $table = 'sms_settings';

    protected $fillable = [
        'driver',
        'sender_number',
        'api_key',
        'api_secret',
        'config',
        'balance_cached',
        'balance_last_checked_at',
    ];

    protected $casts = [
        'config'                  => 'array',
        'balance_last_checked_at' => 'datetime',
    ];
}
