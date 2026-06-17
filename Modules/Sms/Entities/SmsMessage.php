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

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function getWorkflow(): ?array
    {
        if ($this->type !== self::TYPE_SYSTEM) {
            return null;
        }

        // 1. Check if stored in meta
        if (is_array($this->meta) && isset($this->meta['workflow_name'])) {
            return [
                'id' => $this->meta['workflow_id'] ?? null,
                'name' => $this->meta['workflow_name'],
            ];
        }

        // 2. Fallback to querying WorkflowLog for legacy messages
        if (class_exists('\Modules\Workflows\Entities\WorkflowLog')) {
            $log = \Modules\Workflows\Entities\WorkflowLog::query()
                ->where('action_type', 'SEND_SMS')
                ->where(function($query) {
                    $query->where('data->result->sms_id', $this->id)
                          ->orWhere('data->sms_id', $this->id);
                })
                ->with('instance.workflow')
                ->first();

            if ($log && $log->instance && $log->instance->workflow) {
                return [
                    'id' => $log->instance->workflow->id,
                    'name' => $log->instance->workflow->name,
                ];
            }
        }

        return null;
    }

    public function getSenderDetails(): array
    {
        if ($this->type === self::TYPE_SYSTEM) {
            $wf = $this->getWorkflow();
            if ($wf) {
                return [
                    'type' => 'workflow',
                    'label' => $wf['name'],
                    'id' => $wf['id'],
                ];
            }
            return [
                'type' => 'system',
                'label' => 'سیستم',
            ];
        }

        if ($this->type === self::TYPE_MANUAL && $this->created_by) {
            $user = $this->creator;
            if ($user) {
                return [
                    'type' => 'user',
                    'label' => $user->name,
                ];
            }
        }

        return [
            'type' => 'other',
            'label' => match($this->type) {
                self::TYPE_OTP => 'رمز یکبار مصرف',
                self::TYPE_SCHEDULED => 'زمان‌بندی شده',
                default => 'سیستم',
            }
        ];
    }

    public function getFinalMessageAttribute(): ?string
    {
        if ($this->message) {
            return $this->message;
        }

        if (!$this->template_key) {
            return null;
        }

        $template = \Modules\Sms\Entities\SmsTemplate::query()
            ->where('key', $this->template_key)
            ->orWhere('provider_pattern', $this->template_key)
            ->first();

        if ($template && $template->body) {
            $body = $template->body;
            $params = $this->params ?? [];
            if (is_array($params)) {
                foreach ($params as $key => $value) {
                    $body = str_replace('{' . $key . '}', (string) $value, $body);
                }
            }
            return $body;
        }

        if (is_array($this->params) && !empty($this->params)) {
            return 'پارامترها: ' . implode(', ', $this->params);
        }

        return null;
    }
}

