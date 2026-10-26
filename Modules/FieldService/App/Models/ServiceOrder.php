<?php

namespace Modules\FieldService\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Clients\Entities\Client;
use Modules\FieldService\App\Enums\ApplianceType;
use Modules\FieldService\App\Enums\OrderStatus;

class ServiceOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'field_service_orders';

    protected $fillable = [
        'order_number',
        'client_id',
        'technician_id',
        'category_id',
        'building_id',
        'vendor_id',
        'appliance_type',
        'appliance_brand_model',
        'serial_number',
        'problem_description',
        'status',
        'priority',
        'is_emergency',
        'province',
        'city',
        'district',
        'address',
        'lat',
        'lng',
        'customer_name',
        'customer_phone',
        'search_radius_km',
        'search_expanded_count',
        'dispatched_at',
        'dispatch_timeout_seconds',
        'accepted_at',
        'en_route_at',
        'arrived_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
        'notes',
        'meta',
    ];

    protected $casts = [
        'status'                   => OrderStatus::class,
        'appliance_type'           => ApplianceType::class,
        'is_emergency'             => 'boolean',
        'lat'                      => 'float',
        'lng'                      => 'float',
        'search_radius_km'         => 'integer',
        'search_expanded_count'    => 'integer',
        'dispatch_timeout_seconds' => 'integer',
        'dispatched_at'            => 'datetime',
        'accepted_at'              => 'datetime',
        'en_route_at'              => 'datetime',
        'arrived_at'               => 'datetime',
        'started_at'               => 'datetime',
        'completed_at'             => 'datetime',
        'cancelled_at'             => 'datetime',
        'meta'                     => 'array',
    ];

    /**
     * Strictly belongs to CRM Client.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class, 'technician_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(VendorPartner::class, 'vendor_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(ServiceInvoice::class, 'order_id');
    }

    public function backupRequests(): HasMany
    {
        return $this->hasMany(BackupRequest::class, 'order_id');
    }

    public function rating(): HasOne
    {
        return $this->hasOne(ServiceRating::class, 'order_id');
    }

    public static function getActiveStatuses(): array
    {
        return [
            OrderStatus::PENDING,
            OrderStatus::SEARCHING,
            OrderStatus::DISPATCHED,
            OrderStatus::ACCEPTED,
            OrderStatus::EN_ROUTE,
            OrderStatus::ARRIVED,
            OrderStatus::IN_PROGRESS,
            OrderStatus::INVOICE_PENDING,
            OrderStatus::INVOICE_APPROVED,
        ];
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::getActiveStatuses(), true);
    }

    public static function resolveActiveForClient(?int $clientId = null, ?int $sessionOrderId = null, ?int $queryOrderId = null): ?self
    {
        if ($queryOrderId) {
            $order = self::with(['technician', 'category', 'invoice.items'])->find($queryOrderId);
            if ($order && $order->isActive()) {
                return $order;
            }
        }

        if ($clientId) {
            $order = self::with(['technician', 'category', 'invoice.items'])
                ->where('client_id', $clientId)
                ->whereIn('status', self::getActiveStatuses())
                ->latest()
                ->first();
            if ($order) {
                return $order;
            }
        }

        if ($sessionOrderId) {
            $order = self::with(['technician', 'category', 'invoice.items'])->find($sessionOrderId);
            if ($order && $order->isActive()) {
                return $order;
            }
        }

        return null;
    }

    /**
     * Generate unique sequential order number NB-1405-XXXX.
     */
    public static function generateOrderNumber(): string
    {
        $year = function_exists('jdate') ? jdate()->format('Y') : date('Y');
        $random = str_pad((string) mt_rand(1000, 99999), 5, '0', STR_PAD_LEFT);
        return "NB-{$year}-{$random}";
    }

    public function canAccept(): bool
    {
        return in_array($this->status, [OrderStatus::SEARCHING, OrderStatus::DISPATCHED]);
    }

    public function canSubmitInvoice(): bool
    {
        return in_array($this->status, [OrderStatus::IN_PROGRESS, OrderStatus::ARRIVED]);
    }

    public function canCustomerCancel(): bool
    {
        return in_array($this->status, [
            OrderStatus::PENDING,
            OrderStatus::SEARCHING,
            OrderStatus::DISPATCHED,
            OrderStatus::ACCEPTED,
            OrderStatus::EN_ROUTE,
        ]);
    }

    public function canTechnicianCancel(): bool
    {
        return in_array($this->status, [
            OrderStatus::ACCEPTED,
            OrderStatus::EN_ROUTE,
        ]);
    }

    public static function customerCancellationReasons(): array
    {
        return [
            'schedule_changed'   => 'تغییر برنامه زمانی یا عدم حضور در منزل',
            'problem_resolved'   => 'حل خودبه‌خود مشکل یا تعمیر توسط شخص دیگر',
            'technician_delayed' => 'طولانی شدن زمان اعزام و تاخیر تکنسین',
            'wrong_details'      => 'اشتباه در ثبت آدرس یا انتخاب نوع دستگاه',
            'technician_issue'   => 'عدم پاسخگویی یا ناهماهنگی تکنسین',
            'other'              => 'سایر موارد (توضیحات در کادر زیر)',
        ];
    }

    public static function technicianCancellationReasons(): array
    {
        return [
            'vehicle_breakdown'      => 'نقص فنی وسیله نقلیه / تصادف در مسیر',
            'customer_unreachable'   => 'عدم پاسخگویی تلفن مشتری پس از چند بار تماس',
            'wrong_address'          => 'آدرس اشتباه، بن‌بست یا خارج از محدوده تردد',
            'special_tools_needed'   => 'نیاز به تجهیزات یا قطعات خاص خارج از ابزار همراه',
            'emergency'              => 'شرایط جوی اضطراری یا بیماری ناگهانی',
            'other'                  => 'سایر موارد موجه',
        ];
    }
}
