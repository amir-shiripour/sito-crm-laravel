<?php

namespace Modules\FieldService\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Clients\Entities\Client;

class ServiceRating extends Model
{
    use HasFactory;

    protected $table = 'field_service_ratings';

    protected $fillable = [
        'order_id',
        'client_id',
        'technician_id',
        'score',
        'feedback_tags',
        'comment',
        'is_published',
    ];

    protected $casts = [
        'score'         => 'integer',
        'feedback_tags' => 'array',
        'is_published'  => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'order_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class, 'technician_id');
    }
}
