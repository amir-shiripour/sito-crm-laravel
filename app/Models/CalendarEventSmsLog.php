<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEventSmsLog extends Model
{
    protected $table = 'calendar_event_sms_logs';

    protected $fillable = [
        'calendar_event_id',
        'occurrence_date',
        'rule_index',
        'recipient_phone',
        'sms_message_id',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'occurrence_date' => 'date',
        'rule_index'      => 'integer',
        'sms_message_id'  => 'integer',
        'sent_at'         => 'datetime',
    ];

    public function calendarEvent(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class, 'calendar_event_id');
    }

    public function smsMessage(): BelongsTo
    {
        return $this->belongsTo(\Modules\Sms\Entities\SmsMessage::class, 'sms_message_id');
    }
}
