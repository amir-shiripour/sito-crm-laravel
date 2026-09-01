<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Morilog\Jalali\Jalalian;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $table = 'calendar_events';

    protected $fillable = [
        'user_id',
        'created_by',
        'title',
        'description',
        'location',
        'color',
        'start_time',
        'end_time',
        'is_all_day',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
        'is_all_day' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * تاریخ شمسی شروع
     */
    public function getJalaliDateAttribute(): ?string
    {
        return $this->start_time ? Jalalian::fromCarbon($this->start_time)->format('Y/m/d') : null;
    }

    /**
     * زمان شروع و پایان قالب‌بندی شده
     */
    public function getFormattedTimeAttribute(): string
    {
        if ($this->is_all_day) {
            return 'تمام روز';
        }

        $start = $this->start_time ? $this->start_time->format('H:i') : '';
        $end   = $this->end_time ? $this->end_time->format('H:i') : '';

        if ($start && $end) {
            return "{$start} - {$end}";
        }

        return $start ?: '';
    }
}
