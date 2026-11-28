<?php

namespace Modules\Warehouse\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Warehouse\App\Services\WarehouseCodeService;
use Modules\Warehouse\Traits\HasCreator;

class WarehousePurchaseReceipt extends Model
{
    use SoftDeletes;
    use HasCreator;

    protected $table = 'warehouse_purchase_receipts';

    protected $fillable = [
        'receipt_number',
        'invoice_number',
        'purchase_date',
        'warehouse_id',
        'supplier_name',
        'total_items_count',
        'total_quantity',
        'total_amount',
        'update_current_prices',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total_items_count' => 'integer',
        'total_quantity' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'update_current_prices' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($receipt) {
            if (empty($receipt->receipt_number)) {
                $receipt->receipt_number = app(WarehouseCodeService::class)->generatePurchaseReceiptCode();
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(WarehousePurchaseReceiptItem::class, 'purchase_receipt_id')->orderBy('id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(WarehouseItemBatch::class, 'purchase_receipt_id');
    }

    /**
     * Check whether any batch linked to this receipt has had stock consumed.
     */
    public function getHasConsumedBatchesAttribute(): bool
    {
        if ($this->relationLoaded('batches')) {
            return $this->batches->contains(function ($batch) {
                return (float)$batch->remaining_quantity < (float)$batch->initial_quantity;
            });
        }

        return $this->batches()
            ->whereColumn('remaining_quantity', '<', 'initial_quantity')
            ->exists();
    }

    public function getCanModifyAttribute(): bool
    {
        return $this->status !== 'cancelled' && !$this->has_consumed_batches;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'registered' => 'ثبت‌شده در انبار',
            'cancelled' => 'ابطال‌شده',
            default => $this->status,
        };
    }

    public function scopeRegistered($query)
    {
        return $query->where('status', 'registered');
    }
}
