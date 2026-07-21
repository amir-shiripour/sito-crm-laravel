<?php

namespace Modules\Services\App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    use SoftDeletes;

    protected $table = 'service_categories';
    protected $fillable = [
        'name',
        'slug',
        'color',
        'icon',
        'description',
        'status',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->slug ??= Str::slug($m->name));
    }

    public function services(): Builder|HasMany|ServiceCategory
    {
        return $this->hasMany(Service::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
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
