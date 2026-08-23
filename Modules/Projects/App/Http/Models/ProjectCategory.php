<?php

namespace Modules\Projects\App\Http\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectCategory extends Model
{
    use SoftDeletes;

    protected $table = 'projects_categories';

    protected $fillable = [
        'name',
        'slug',
        'color',
        'icon',
        'description',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name) . '-' . Str::random(4);
            }
        });
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Public URL for the uploaded icon image, or null if none is set.
     */
    public function getIconUrlAttribute(): ?string
    {
        if (!$this->icon) {
            return null;
        }

        if (!str_contains($this->icon, '/') && !preg_match('/\.(png|jpe?g|gif|svg|webp)$/i', $this->icon)) {
            return null;
        }

        return Storage::disk('public')->exists($this->icon)
            ? Storage::disk('public')->url($this->icon)
            : null;
    }
}
