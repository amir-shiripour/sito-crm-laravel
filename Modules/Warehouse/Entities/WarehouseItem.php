<?php

namespace Modules\Warehouse\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Warehouse\App\Services\WarehouseCodeService;
use Modules\Warehouse\Traits\HasCreator;

class WarehouseItem extends Model
{
    use SoftDeletes;
    use HasCreator;

    protected $table = 'warehouse_items';

    protected $fillable = [
        'system_code',
        'code',
        'name',
        'category_id',
        'subcategory_id',
        'brand',
        'brand_code',
        'item_type',
        'item_type_code',
        'model_spec',
        'unit',
        'min_stock',
        'reorder_point',
        'current_price',
        'last_purchase_price',
        'default_warehouse_id',
        'default_location',
        'barcode',
        'has_expiry',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'min_stock' => 'decimal:2',
        'reorder_point' => 'decimal:2',
        'current_price' => 'decimal:2',
        'last_purchase_price' => 'decimal:2',
        'has_expiry' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->system_code)) {
                $item->system_code = app(WarehouseCodeService::class)->generateSystemCode();
            }
            if (empty($item->code)) {
                $item->code = app(WarehouseCodeService::class)->generateSuggestedSku(
                    $item->category?->code,
                    $item->subcategory?->code,
                    $item->item_type_code,
                    $item->brand_code,
                    $item->model_spec
                ) ?: $item->system_code;
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(WarehouseCategory::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(WarehouseCategory::class, 'subcategory_id');
    }

    public function defaultWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'default_warehouse_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(WarehouseItemStock::class, 'item_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(WarehouseItemBatch::class, 'item_id')
            ->orderBy('purchase_date')
            ->orderBy('id');
    }

    public function activeBatches(): HasMany
    {
        return $this->hasMany(WarehouseItemBatch::class, 'item_id')
            ->where('remaining_quantity', '>', 0)
            ->orderBy('purchase_date')
            ->orderBy('id');
    }

    /**
     * Total physical inventory currently in stock across all batches
     */
    public function getTotalStockAttribute(): float
    {
        if ($this->relationLoaded('activeBatches')) {
            return (float)$this->activeBatches->sum('remaining_quantity');
        }
        return (float)$this->batches()->where('remaining_quantity', '>', 0)->sum('remaining_quantity');
    }

    /**
     * Total cost value of currently held stock based on exact purchase prices (FIFO)
     */
    public function getTotalCostValueAttribute(): float
    {
        $batches = $this->relationLoaded('activeBatches')
            ? $this->activeBatches
            : $this->batches()->where('remaining_quantity', '>', 0)->get();

        return (float)$batches->sum(function ($b) {
            return (float)$b->remaining_quantity * (float)$b->unit_purchase_price;
        });
    }

    /**
     * Total market / replacement value based on current price
     */
    public function getTotalMarketValueAttribute(): float
    {
        $price = (float)($this->current_price ?? $this->average_purchase_price);
        return $this->total_stock * $price;
    }

    /**
     * Unrealized gross margin / gain
     */
    public function getUnrealizedProfitAttribute(): float
    {
        return $this->total_market_value - $this->total_cost_value;
    }

    /**
     * Weighted average purchase price of remaining units
     */
    public function getAveragePurchasePriceAttribute(): float
    {
        $stock = $this->total_stock;
        if ($stock <= 0) {
            return (float)($this->last_purchase_price ?? $this->current_price ?? 0);
        }
        return round($this->total_cost_value / $stock, 2);
    }

    /**
     * Stock status checks
     */
    public function getIsBelowMinStockAttribute(): bool
    {
        return (float)$this->min_stock > 0 && $this->total_stock <= (float)$this->min_stock;
    }

    public function getIsBelowReorderPointAttribute(): bool
    {
        return (float)$this->reorder_point > 0 && $this->total_stock <= (float)$this->reorder_point;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->is_below_min_stock) {
            return 'critical'; // کسری بحرانی
        }
        if ($this->is_below_reorder_point) {
            return 'reorder'; // نیاز به سفارش
        }
        return 'adequate'; // موجودی کافی
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
