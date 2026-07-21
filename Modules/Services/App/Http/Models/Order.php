<?php

namespace Modules\Services\App\Http\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Clients\Entities\Client;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use SoftDeletes;

    protected $table = 'service_orders';

    protected $fillable = [
        'order_number',
        'invoice_id',
        'service_id',
        'customer_id',
        'created_by',
        'status_id',
        'client_name',
        'client_phone',
        'client_email',
        'issue_date',
        'renewal_date',
        'billing_cycle',
        'first_payment_amount',
        'renewal_price',
        'renewal_price_type',
        'base_price_type',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'renewal_date' => 'date',
        'first_payment_amount' => 'integer',
        'renewal_price' => 'integer',
        'total_amount' => 'integer',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'customer_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
