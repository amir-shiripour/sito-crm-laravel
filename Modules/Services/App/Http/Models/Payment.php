<?php

namespace Modules\Services\App\Http\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $table = 'service_invoice_payments';

    protected $fillable = [
        'invoice_id',
        'user_id',
        'amount',
        'method',
        'gateway',
        'paid_at',
        'transaction_id',
        'notes',
        'status',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
