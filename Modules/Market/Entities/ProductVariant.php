<?php
namespace Modules\Market\Entities;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model {
    protected $table = 'market_product_variants';
    protected $fillable = ['master_product_id', 'variant_code', 'variant_attributes', 'is_active'];
    protected $casts = ['variant_attributes' => 'array'];

    public function masterProduct() { return $this->belongsTo(MasterProduct::class, 'master_product_id'); }
    public function vendorProducts() { return $this->hasMany(VendorProduct::class, 'product_variant_id'); }
}
