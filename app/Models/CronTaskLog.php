<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Morilog\Jalali\Jalalian;

class CronTaskLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'cron_task_id',
        'status',
        'started_at',
        'finished_at',
        'duration_ms',
        'output',
        'error_message',
        'triggered_by',
        'created_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration_ms' => 'integer',
        'created_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(CronTask::class, 'cron_task_id');
    }

    public function getJalaliCreatedAtAttribute(): string
    {
        if (!$this->created_at) {
            return '';
        }

        try {
            return Jalalian::fromCarbon($this->created_at)->format('Y/m/d H:i:s');
        } catch (\Throwable $e) {
            return (string) $this->created_at;
        }
    }

    public function getRelativeTimeAttribute(): string
    {
        if (!$this->created_at) {
            return '';
        }

        $diffInSeconds = now()->diffInSeconds($this->created_at);
        if ($diffInSeconds < 60) {
            return 'لحظاتی پیش';
        }
        if ($diffInSeconds < 3600) {
            return round($diffInSeconds / 60) . ' دقیقه پیش';
        }
        if ($diffInSeconds < 86400) {
            return round($diffInSeconds / 3600) . ' ساعت پیش';
        }

        return round($diffInSeconds / 86400) . ' روز پیش';
    }
}
