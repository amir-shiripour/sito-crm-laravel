<?php

namespace Modules\Warehouse\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Clients\Entities\Client;
use Modules\Warehouse\App\Services\WarehouseCodeService;
use Modules\Warehouse\Support\WarehouseUserVisibility;
use Modules\Warehouse\Traits\HasCreator;

class WarehouseIssueDocument extends Model
{
    use SoftDeletes;
    use HasCreator;

    protected $table = 'warehouse_issue_documents';

    public const TYPE_PATIENT_CONSUMPTION = 'patient_consumption';
    public const TYPE_DEPARTMENT_CONSUMPTION = 'department_consumption';
    public const TYPE_WASTE_EXPIRED = 'waste_expired';
    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'issue_number',
        'issue_date',
        'issue_type',
        'source_warehouse_id',
        'client_id',
        'patient_name',
        'patient_case_number',
        'consumer_user_id',
        'consumer_name',
        'items_count',
        'total_quantity',
        'total_cost_value',
        'status',
        'cancelled_at',
        'cancelled_by',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'items_count' => 'integer',
        'total_quantity' => 'decimal:2',
        'total_cost_value' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $doc) {
            if (empty($doc->issue_number)) {
                $doc->issue_number = app(WarehouseCodeService::class)->generateIssueNumber();
            }
        });
    }

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function consumerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consumer_user_id');
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WarehouseIssueItem::class, 'issue_document_id')->orderBy('id');
    }

    public function getEffectivePatientNameAttribute(): ?string
    {
        return $this->client?->full_name ?: $this->patient_name;
    }

    public function getEffectiveCaseNumberAttribute(): ?string
    {
        return $this->client?->case_number ?: $this->patient_case_number;
    }

    public function getEffectiveConsumerNameAttribute(): ?string
    {
        if ($this->consumerUser) {
            return WarehouseUserVisibility::displayUserName($this->consumerUser);
        }

        return $this->consumer_name;
    }

    public function getIssueTypeLabelAttribute(): string
    {
        return match ($this->issue_type) {
            self::TYPE_PATIENT_CONSUMPTION => 'مصرف بیمار / پرونده',
            self::TYPE_DEPARTMENT_CONSUMPTION => 'مصرف داخلی بخش',
            self::TYPE_WASTE_EXPIRED => 'ضایعات / منقضی / آسیب‌دیده',
            default => 'سایر خروجی‌ها',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'cancelled' ? 'ابطال‌شده (برگشت موجودی)' : 'ثبت‌شده و کسرشده';
    }
}
