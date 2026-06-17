<?php
namespace Modules\Market\Entities;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\Entities\WarehouseStock;

class VendorProduct extends Model {
    use SoftDeletes; // بر اساس ساختار دیتابیستون این جدول deleted_at داره

    protected $table = 'market_vendor_products';

    protected $fillable = [
        'vendor_id',
        'product_variant_id',
        'sku_extension',
        'price',
        'discount_price',
        'discount_start_date', // 💡 NEW
        'discount_end_date', // 💡 NEW
        'discount_stock', // 💡 NEW
        'max_discount_purchase_qty', // 💡 NEW
        'stock',
        'reorder_point',
        'min_purchase_qty',
        'max_purchase_qty',
        'cart_amount_step',
        'purchase_step',
        'status',
        'rejection_reason'
    ];

    protected $casts = [
        'discount_start_date' => 'datetime',
        'discount_end_date' => 'datetime',
    ];

    public function masterProduct() {
        // چون این مدل مستقیما master_product_id نداره، از طریق variant بهش وصل میشیم
        // اگر فیلد master_product_id توی جدول نیست، این رابطه اشتباهه. بر اساس ساختار دیتابیس شما اصلاحش کردم:
        return $this->hasOneThrough(
            MasterProduct::class,
            ProductVariant::class,
            'id', // کلید خارجی در جدول واسط (ProductVariant)
            'id', // کلید خارجی در جدول هدف (MasterProduct)
            'product_variant_id', // کلید محلی در جدول مبدا (VendorProduct)
            'master_product_id' // کلید محلی در جدول واسط (ProductVariant)
        );
    }

    public function variant() {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    // 💡 رفع ارور: ارتباط با جدول Vendors
    public function vendor() {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    /**
     * Get stock value. If WMS is active, dynamically sum available stock from WMS active warehouses.
     */
    public function getStockAttribute($value)
    {
        $isWmsActive = (bool) MarketSetting::getValue('wms.enabled', false);
        if ($isWmsActive) {
            return (int) app(\Modules\Market\App\Services\WarehouseStockService::class)
                ->getAvailableStock($this->product_variant_id, $this->vendor_id);
        }
        return $value;
    }
}
