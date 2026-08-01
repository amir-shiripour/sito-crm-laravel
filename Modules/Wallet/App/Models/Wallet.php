<?php

namespace Modules\Wallet\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Wallet extends Model
{
    protected $table = 'wallets';

    protected $fillable = [
        'holder_type',
        'holder_id',
        'slug',
        'name',
        'balance',
        'currency',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'balance' => 'decimal:4',
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    public function holder(): MorphTo
    {
        return $this->morphTo();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_id');
    }
}
