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

    protected static function booted(): void
    {
        static::creating(function (SmsTemplate $template) {
            if (empty($template->key)) {
                $base = \Illuminate\Support\Str::slug($template->title ?? 'template');
                $base = $base ? substr($base, 0, 30) : 'template';
                $template->key = $base . '-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(6));
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
