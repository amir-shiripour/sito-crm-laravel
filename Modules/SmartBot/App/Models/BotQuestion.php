<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

final class BotQuestion extends Model
{
    protected $fillable = [
        'question_text',
        'keywords',
        'category',
        'priority',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'keywords' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(BotAnswer::class, 'question_id');
    }

    // Default active answer
    public function defaultAnswer()
    {
        return $this->answers()->where('is_default', true)->first();
    }
}
