<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class BotSession extends Model
{
    protected $fillable = [
        'session_uuid',
        'visitor_type',
        'visitor_id',
        'page_url',
        'metadata',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'started_at' => 'immutable_datetime',
        'ended_at' => 'immutable_datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(BotMessage::class, 'session_id');
    }
}
