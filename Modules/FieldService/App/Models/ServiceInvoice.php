<?php

namespace Modules\FieldService\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\FieldService\App\Enums\PaymentStatus;

class ServiceInvoice extends Model
{
    use HasFactory;

    protected $table = 'field_service_invoices';

    protected $fillable = [
        'order_id',
        'invoice_number',
        'labor_amount',
        'parts_amount',
        'travel_fee',
        'discount_amount',
        'total_amount',
        'platform_commission_percent',
        'platform_commission_amount',
        'technician_net_amount',
        'payment_method',
        'payment_status',
        'tracking_number',
        'paid_at',
        'escrow_released_at',
        'warranty_until',
        'notes',
    ];

    protected $casts = [
        'labor_amount'                => 'float',
        'parts_amount'                => 'float',
        'travel_fee'                  => 'float',
        'discount_amount'             => 'float',
        'total_amount'                => 'float',
        'platform_commission_percent' => 'float',
        'platform_commission_amount'  => 'float',
        'technician_net_amount'       => 'float',
        'payment_status'              => PaymentStatus::class,
        'paid_at'                     => 'datetime',
        'escrow_released_at'          => 'datetime',
        'warranty_until'              => 'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceOrderItem::class, 'invoice_id');
    }

    public static function generateInvoiceNumber(): string
    {
        $year = function_exists('jdate') ? jdate()->format('Y') : date('Y');
        $random = str_pad((string) mt_rand(1000, 99999), 5, '0', STR_PAD_LEFT);
        return "INV-NB-{$year}-{$random}";
    }

    /**
     * Recalculate totals based on items, base travel fee, and 15% platform commission.
     */
    public function recalculate(): void
    {
        $labor = 0;
        $parts = 0;

        foreach ($this->items as $item) {
            if ($item->item_type === 'labor') {
                $labor += $item->total_price;
            } elseif ($item->item_type === 'part') {
                $parts += $item->total_price;
            }
        }

        $this->labor_amount = $labor;
        $this->parts_amount = $parts;
        $this->total_amount = max(0, ($labor + $parts + $this->travel_fee) - $this->discount_amount);

        // 15% platform cut is applied on labor and services
        $commPercent = $this->platform_commission_percent > 0 ? $this->platform_commission_percent : 15.0;
        $this->platform_commission_amount = round(($labor * $commPercent) / 100);
        
        // Technician gets: (labor - commission) + full parts cost + full travel fee
        $this->technician_net_amount = ($labor - $this->platform_commission_amount) + $parts + $this->travel_fee;
        $this->save();
    }
}
