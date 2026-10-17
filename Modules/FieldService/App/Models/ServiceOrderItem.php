<?php

namespace Modules\FieldService\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrderItem extends Model
{
    use HasFactory;

    protected $table = 'field_service_order_items';

    protected $fillable = [
        'invoice_id',
        'item_type',
        'title',
        'unit_price',
        'quantity',
        'total_price',
        'warranty_months',
        'part_serial_number',
        'notes',
    ];

    protected $casts = [
        'unit_price'      => 'float',
        'quantity'        => 'integer',
        'total_price'     => 'float',
        'warranty_months' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ServiceInvoice::class, 'invoice_id');
    }
}
