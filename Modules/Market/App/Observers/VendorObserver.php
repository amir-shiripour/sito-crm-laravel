<?php

namespace Modules\Market\App\Observers;

use Modules\Market\Entities\MarketSetting;
use Modules\Market\Entities\Vendor;
use Modules\Market\Entities\Warehouse;

class VendorObserver
{
    /**
     * Handle the Vendor "created" event.
     */
    public function created(Vendor $vendor): void
    {
        $this->autoCreateVendorWarehouse($vendor);
    }

    /**
     * Handle the Vendor "updated" event.
     */
    public function updated(Vendor $vendor): void
    {
        // اگر وضعیت فروشنده از غیرفعال به فعال تغییر کرد
        if ($vendor->isDirty('status') && $vendor->status === 'active') {
            $this->autoCreateVendorWarehouse($vendor);
        }
    }

    /**
     * Automatically creates a default warehouse for the vendor if WMS is enabled and no warehouse exists.
     */
    protected function autoCreateVendorWarehouse(Vendor $vendor): void
    {
        // این منطق فقط در صورتی اجرا می‌شود که WMS فعال باشد و فروشنده هم فعال باشد
        if (MarketSetting::getValue('wms.enabled', false) && $vendor->status === 'active') {
            // بررسی می‌کنیم که آیا این فروشنده از قبل انباری دارد یا خیر
            $existingWarehouse = Warehouse::where('vendor_id', $vendor->id)->first();

            if (!$existingWarehouse) {
                // اگر انباری نداشت، یک انبار پیش‌فرض برایش ایجاد می‌کنیم
                Warehouse::create([
                    'vendor_id' => $vendor->id,
                    'name' => 'انبار اصلی ' . $vendor->store_name,
                    'is_active' => true,
                ]);
            }
        }
    }
}
