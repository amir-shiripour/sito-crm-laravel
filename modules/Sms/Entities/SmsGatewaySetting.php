<?php

namespace Modules\Sms\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsGatewaySetting extends Model
{
    use HasFactory;

    protected $table = 'sms_gateway_settings';

    protected $guarded = [];

    protected $casts = [
        'config'          => 'array',
        'balance_checked_at' => 'datetime',
    ];
}
