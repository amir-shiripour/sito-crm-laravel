<?php

namespace Modules\Warehouse\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Warehouse\App\Services\WarehouseCodeService;
use Modules\Warehouse\Support\WarehouseUserVisibility;
use Modules\Warehouse\Traits\HasCreator;

class WarehouseTransferRequest extends Model
{
    use SoftDeletes;
    use HasCreator;

    protected $table = 'warehouse_transfer_requests';

    protected $fillable = [
        'transfer_number',
        'request_number',
        'request_date',
        'needed_date',
        'source_warehouse_id',
        'destination_warehouse_id',
        'requester_user_id',
        'requester_name',
        'approved_by',
        'delivered_at',
        'total_items_count',
        'total_requested_quantity',
        'total_delivered_quantity',
        'total_cost_value',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'request_date' => 'date',
        'needed_date' => 'date',
        'delivered_at' => 'datetime',
        'total_items_count' => 'integer',
        'total_requested_quantity' => 'decimal:2',
        'total_delivered_quantity' => 'decimal:2',
        'total_cost_value' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transfer) {
            $codeService = app(WarehouseCodeService::class);
            if (empty($transfer->transfer_number)) {
                $transfer->transfer_number = $codeService->generateTransferRequestCode();
            }
            if (empty($transfer->request_number)) {
                $transfer->request_number = $codeService->generateRequestNumber();
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(WarehouseTransferRequestItem::class, 'transfer_request_id')->orderBy('id');
    }

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function requesterUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function transferredBatches(): HasMany
    {
        return $this->hasMany(WarehouseItemBatch::class, 'transfer_request_id');
    }

    public function getHasConsumedTransferredBatchesAttribute(): bool
    {
        if ($this->relationLoaded('transferredBatches')) {
            return $this->transferredBatches->contains(function ($batch) {
                return (float)$batch->remaining_quantity < (float)$batch->initial_quantity;
            });
        }

        return $this->transferredBatches()
            ->whereColumn('remaining_quantity', '<', 'initial_quantity')
            ->exists();
    }

    public function getCanFulfillAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function getCanCancelAttribute(): bool
    {
        if ($this->status === 'pending') {
            return true;
        }
        if ($this->status === 'delivered') {
            return !$this->has_consumed_transferred_batches;
        }
        return false;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'در انتظار تایید و تحویل',
            'delivered' => 'تحویل و منتقل‌شده',
            'rejected' => 'ردشده',
            'cancelled' => 'ابطال‌شده',
            default => $this->status,
        };
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function getEffectiveRequesterNameAttribute(): string
    {
        if ($this->requesterUser) {
            return WarehouseUserVisibility::displayUserName($this->requesterUser, null, '—');
        }

        return $this->requester_name ?: '—';
    }

    public function getEffectiveApproverNameAttribute(): string
    {
        return WarehouseUserVisibility::displayUserName($this->approver, null, '—');
    }
}
