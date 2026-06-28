<?php

namespace Modules\Settings\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ApiKey extends Model
{
    protected $fillable = [
        'name',
        'key',
        'docs_token',
        'module',
        'filters',
        'permissions',
        'is_active',
        'rate_limit_per_hour',
        'last_used_at',
        'expires_at',
        'usage_count',
        'created_by',
    ];

    protected $casts = [
        'filters' => 'array',
        'permissions' => 'array',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user who created the API key.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if the API key is currently valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Record usage of the API key.
     */
    public function recordUsage(): void
    {
        $this->timestamps = false;
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }
}
