<?php

namespace Modules\ContractForge\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\User;
use Modules\Clients\Entities\Client;

class Contract extends Model
{
    protected $table = 'contracts';

    protected $fillable = [
        'contract_number',
        'template_id',
        'rule_id',
        'contractable_type',
        'contractable_id',
        'client_id',
        'user_id',
        'title',
        'blocks_data',
        'rendered_body',
        'status',
        'signed_at',
        'meta',
        'notes',
    ];

    protected $casts = [
        'blocks_data' => 'array',
        'meta' => 'array',
        'signed_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ContractTemplate::class, 'template_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(ContractRule::class, 'rule_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function contractable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get a user-friendly status badge markup or class.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'پیش‌نویس',
            'active' => 'فعال / در انتظار امضا',
            'signed' => 'امضا شده',
            'cancelled' => 'لغو شده',
            default => $this->status,
        };
    }
}
