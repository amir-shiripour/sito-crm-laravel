<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'active',
        'installed',
        'installed_at',
        'is_core',
    ];

    protected $casts = [
        'active' => 'boolean',
        'installed' => 'boolean',
        'installed_at' => 'datetime',
        'is_core' => 'boolean',
    ];

    public function requiredByThemes(): BelongsToMany
    {
        return $this->belongsToMany(Theme::class, 'theme_module_requirements');
    }

    public function isInstalled(): bool
    {
        return (bool) $this->installed;
    }

    public function isEnabled(): bool
    {
        return (bool) $this->active;
    }
}
