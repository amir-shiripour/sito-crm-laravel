<?php

namespace Modules\Sms\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    use HasFactory;

    protected $table = 'sms_templates';

    protected $guarded = [];

    protected $casts = [
        'meta'   => 'array',
        'params' => 'array',
    ];

    public const TYPE_GENERIC = 'generic';
    public const TYPE_OTP     = 'otp';
    public const TYPE_SYSTEM  = 'system';

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
