<?php

namespace Modules\Sms\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsMessage extends Model
{
    use HasFactory;

    protected $table = 'sms_messages';

    protected $guarded = [];

    protected $casts = [
        'meta'         => 'array',
        'params'       => 'array',
        'scheduled_at' => 'datetime',
        'sent_at'      => 'datetime',
        'failed_at'    => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT    = 'sent';
    public const STATUS_FAILED  = 'failed';

    public const TYPE_SYSTEM    = 'system';
    public const TYPE_SCHEDULED = 'scheduled';
    public const TYPE_MANUAL    = 'manual';
    public const TYPE_OTP       = 'otp';

    public const CHANNEL_SMS    = 'sms';

    public static function createFromPayload(array $data): self
    {
        $data['status'] = $data['status'] ?? self::STATUS_PENDING;
        $data['driver'] = $data['driver'] ?? null;

        return static::create($data);
    }

    public function markAsSent(?string $driver = null, ?array $meta = null): void
    {
        $this->status   = self::STATUS_SENT;
        $this->sent_at  = now();
        $this->driver   = $driver ?: $this->driver;

        if ($meta) {
            $this->meta = array_merge($this->meta ?? [], [
                'driver'   => $driver,
                'response' => $meta,
            ]);
        }

        $this->save();
    }

    public function markAsFailed(?string $driver = null, ?string $error = null, ?array $meta = null): void
    {
        $this->status    = self::STATUS_FAILED;
        $this->failed_at = now();
        $this->driver    = $driver ?: $this->driver;
        $this->error     = $error;

        if ($meta) {
            $this->meta = array_merge($this->meta ?? [], [
                'driver' => $driver,
                'error'  => $meta,
            ]);
        }

        $this->save();
    }
}
