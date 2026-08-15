<?php

namespace Modules\Accounting\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProformaItem extends Model
{
    use HasFactory;

    protected $table = 'accounting_proforma_items';

    protected $fillable = [
        'proforma_id',
        'description',
        'quantity',
        'unit_price',
        'discount',
        'total_price',
        'item_code',
        'unit_type',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'discount' => 'integer',
        'total_price' => 'integer',
    ];

    /**
     * Get the proforma that owns the item.
     */
    public function proforma(): BelongsTo
    {
        return $this->belongsTo(Proforma::class);
    }
}
