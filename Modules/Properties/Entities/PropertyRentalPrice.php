<?php

namespace Modules\Properties\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertyRentalPrice extends Model
{
    use HasFactory;

    protected $table = 'property_rental_prices';

    protected $fillable = [
        'property_id',
        'start_date',
        'end_date',
        'price_per_night',
        'title',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price_per_night' => 'decimal:0',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}
