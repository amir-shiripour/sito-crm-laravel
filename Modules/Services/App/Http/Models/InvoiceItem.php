<?php

namespace Modules\Services\App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Services\App\Http\Models\Service;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $table = 'service_invoice_items';

    public $timestamps  = false;

    protected $fillable = [
        'invoice_id',
        'service_id',
        'custom_service_name',
        'description',
        'unit',
        'quantity',
        'unit_price',
        'discount',
        'tax_percent',
        'tax_amount',
        'total',
        'meta',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'integer',
        'discount' => 'integer',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'integer',
        'total' => 'integer',
        'meta' => 'array',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
