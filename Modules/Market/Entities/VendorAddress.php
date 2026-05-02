<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;

class VendorAddress extends Model
{
    protected $table = 'market_vendor_addresses';

    protected $fillable = [
        'vendor_id', 'type', 'province', 'city', 'address', 'postal_code', 'latitude', 'longitude', 'is_default'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}
