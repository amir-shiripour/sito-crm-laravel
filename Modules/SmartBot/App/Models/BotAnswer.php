<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BotAnswer extends Model
{
    protected $fillable = [
        'question_id',
        'answer_text',
        'answer_type',
        'entity_type',
        'entity_ids',
        'show_add_to_cart',
        'is_default',
    ];

    protected $casts = [
        'entity_ids' => 'array',
        'show_add_to_cart' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(BotQuestion::class, 'question_id');
    }
}
