<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BotMessage extends Model
{
    protected $fillable = [
        'session_id',
        'role',
        'content',
        'question_id',
        'answer_id',
        'resolved',
        'confidence_score',
    ];

    protected $casts = [
        'resolved' => 'boolean',
        'confidence_score' => 'float',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(BotSession::class, 'session_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(BotQuestion::class, 'question_id');
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(BotAnswer::class, 'answer_id');
    }
}
