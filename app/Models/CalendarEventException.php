<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEventException extends Model
{
    use HasFactory;

    protected $table = 'calendar_event_exceptions';

    protected $fillable = [
        'calendar_event_id',
        'exception_date',
        'action',
        'title',
        'description',
        'location',
        'color',
        'start_time',
        'end_time',
        'is_all_day',
    ];

    protected $casts = [
        'exception_date' => 'date',
        'start_time'     => 'datetime',
        'end_time'       => 'datetime',
        'is_all_day'     => 'boolean',
    ];

    public function parentEvent(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class, 'calendar_event_id');
    }
}
