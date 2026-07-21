<?php

namespace Modules\Market\Traits;

use Modules\Market\Entities\Vendor;

trait HasMarketVendor
{
    public function marketVendor()
    {
        return $this->hasOne(Vendor::class, 'user_id');
    }

    public function marketVendors()
    {
        return $this->belongsToMany(Vendor::class, 'market_vendor_user', 'user_id', 'vendor_id');
    }

    public function getMarketVendorAttribute()
    {
        $primary = $this->getRelationValue('marketVendor');
        if ($primary) {
            return $primary;
        }

        $vendor = $this->marketVendor()->first();
        if ($vendor) {
            return $vendor;
        }

        $fallback = $this->marketVendors()->first();
        if ($fallback) {
            return $fallback;
        }

        if (\Modules\Market\Entities\MarketSetting::getValue('system.store_type', 'multi') === 'single') {
            return \Modules\Market\Entities\Vendor::where('status', 'active')->first() 
                ?? \Modules\Market\Entities\Vendor::first();
        }

        return null;
    }
}
