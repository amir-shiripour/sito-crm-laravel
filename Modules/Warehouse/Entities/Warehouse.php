<?php

namespace Modules\Warehouse\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Warehouse\Traits\HasCreator;

class Warehouse extends Model
{
    use SoftDeletes;
    use HasCreator;

    protected $table = 'warehouse_warehouses';

    protected $fillable = [
        'name',
        'type',
        'parent_id',
        'is_main',
        'is_active',
        'color',
        'code',
        'location',
        'manager',
        'phone',
        'description',
        'created_by',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($warehouse) {
            if (empty($warehouse->code)) {
                $warehouse->code = self::generateUniqueCode($warehouse->type, $warehouse->is_main);
            }
            if (empty($warehouse->color)) {
                $warehouse->color = $warehouse->type === 'inventory' ? '#4f46e5' : '#0d9488';
            }
        });
    }

    public static function generateUniqueCode(string $type, bool $isMain = false): string
    {
        $prefix = $type === 'inventory' ? 'WH-INV-' : 'WH-AST-';
        if ($isMain) {
            $prefix .= 'MAIN-';
        }

        $latest = self::where('code', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $counter = 1;
        if ($latest && preg_match('/(\d+)$/', $latest->code, $matches)) {
            $counter = (int) $matches[1] + 1;
        }

        $code = $prefix . str_pad((string)$counter, 2, '0', STR_PAD_LEFT);
        while (self::where('code', $code)->exists()) {
            $counter++;
            $code = $prefix . str_pad((string)$counter, 2, '0', STR_PAD_LEFT);
        }

        return $code;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function marketLinks(): HasMany
    {
        return $this->hasMany(WarehouseMarketLink::class, 'warehouse_id');
    }

    public function managers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\User::class, 'warehouse_managers', 'warehouse_id', 'user_id')
            ->withTimestamps();
    }

    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeInventory($query)
    {
        return $query->where('type', 'inventory');
    }

    public function scopeAsset($query)
    {
        return $query->where('type', 'asset');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'inventory' ? 'انبار موجودی' : 'مدیریت اموال';
    }

    public function getEffectiveColorAttribute(): string
    {
        if (!empty($this->color)) {
            return $this->color;
        }
        return $this->type === 'inventory' ? '#4f46e5' : '#0d9488';
    }
}
