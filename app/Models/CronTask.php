<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Morilog\Jalali\Jalalian;
use Nwidart\Modules\Facades\Module;

class CronTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'module',
        'command',
        'name',
        'description',
        'expression',
        'is_active',
        'prevent_overlap',
        'run_in_background',
        'last_run_at',
        'next_run_at',
        'last_status',
        'last_duration_ms',
        'last_error_message',
        'is_system',
        'parameters',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'prevent_overlap' => 'boolean',
        'run_in_background' => 'boolean',
        'is_system' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'last_duration_ms' => 'integer',
        'parameters' => 'array',
    ];

    /**
     * Get logs for this task.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(CronTaskLog::class, 'cron_task_id')->orderByDesc('id');
    }

    /**
     * Get latest log for this task.
     */
    public function latestLog(): HasOne
    {
        return $this->hasOne(CronTaskLog::class, 'cron_task_id')->latestOfMany();
    }

    /**
     * Scope active tasks.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if parent module is enabled.
     */
    public function isModuleActive(): bool
    {
        if (empty($this->module) || in_array(strtolower($this->module), ['core', 'system', 'app'])) {
            return true;
        }

        if (class_exists(Module::class)) {
            $mod = Module::find($this->module);
            return $mod ? $mod->isEnabled() : false;
        }

        return true;
    }

    /**
     * Get Jalali formatted last run.
     */
    public function getJalaliLastRunAttribute(): ?string
    {
        if (!$this->last_run_at) {
            return null;
        }

        try {
            return Jalalian::fromCarbon($this->last_run_at)->format('Y/m/d H:i:s');
        } catch (\Throwable $e) {
            return $this->last_run_at->toDateTimeString();
        }
    }

    /**
     * Get human friendly relative last run.
     */
    public function getRelativeLastRunAttribute(): string
    {
        if (!$this->last_run_at) {
            return 'تاکنون اجرا نشده';
        }

        $diffInSeconds = now()->diffInSeconds($this->last_run_at);
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

    /**
     * Get Jalali formatted next run.
     */
    public function getJalaliNextRunAttribute(): ?string
    {
        if (!$this->next_run_at) {
            return null;
        }

        try {
            return Jalalian::fromCarbon($this->next_run_at)->format('Y/m/d H:i:s');
        } catch (\Throwable $e) {
            return $this->next_run_at->toDateTimeString();
        }
    }

    /**
     * Get Persian human-readable frequency description.
     */
    public function getHumanExpressionAttribute(): string
    {
        return match ($this->expression) {
            'everyMinute', '* * * * *' => 'هر دقیقه',
            'everyTwoMinutes', '*/2 * * * *' => 'هر ۲ دقیقه',
            'everyThreeMinutes', '*/3 * * * *' => 'هر ۳ دقیقه',
            'everyFiveMinutes', '*/5 * * * *' => 'هر ۵ دقیقه',
            'everyTenMinutes', '*/10 * * * *' => 'هر ۱۰ دقیقه',
            'everyFifteenMinutes', '*/15 * * * *' => 'هر ۱۵ دقیقه',
            'everyThirtyMinutes', '*/30 * * * *' => 'هر ۳۰ دقیقه',
            'hourly', '0 * * * *' => 'هر یک ساعت',
            'everyTwoHours', '0 */2 * * *' => 'هر ۲ ساعت',
            'everyThreeHours', '0 */3 * * *' => 'هر ۳ ساعت',
            'everySixHours', '0 */6 * * *' => 'هر ۶ ساعت',
            'daily', '0 0 * * *' => 'روزانه (ساعت ۰۰:۰۰)',
            'weekly', '0 0 * * 0' => 'هفتگی (یکشنبه‌ها)',
            'monthly', '0 0 1 * *' => 'ماهانه (روز اول ماه)',
            default => $this->expression,
        };
    }
}
