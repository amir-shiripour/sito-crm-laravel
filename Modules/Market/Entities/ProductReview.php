<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Clients\Entities\Client;

class ProductReview extends Model
{
    protected $table = 'market_product_reviews';

    protected $fillable = [
        'master_product_id',
        'client_id',
        'vendor_product_id',
        'rating',
        'comment',
        'status',
        'rejection_reason',
    ];

    public function masterProduct()
    {
        return $this->belongsTo(MasterProduct::class, 'master_product_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function vendorProduct()
    {
        return $this->belongsTo(VendorProduct::class, 'vendor_product_id');
    }
}
