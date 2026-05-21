<?php

namespace Modules\Accounting\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $table = 'invoice_items';

    protected $fillable = [
        'invoice_id',
        'item_code',
        'description',
        'quantity',
        'unit_type',
        'unit_price',
        'total_price',
        'discount', // Add discount to fillable
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'discount' => 'decimal:2', // Add discount to casts
    ];

    /**
     * Get the invoice that owns the item.
     * هر ردیف متعلق به یک صورت حساب است.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
