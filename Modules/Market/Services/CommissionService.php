<?php

namespace Modules\Market\Services;

use Modules\Market\Entities\Vendor;
use Illuminate\Support\Facades\Config;

class CommissionService
{
    /**
     * محاسبه سهم فروشنده و سهم سایت از یک مبلغ مشخص
     *
     * @param Vendor $vendor
     * @param float $amount
     * @return array
     */
    public function calculate(Vendor $vendor, float $amount): array
    {
        // اگر فروشنده درصد اختصاصی نداشت، درصد پیش‌فرض سیستم را می‌گیریم (مثلاً 10 درصد)
        // بعداً می‌توانیم این مقدار را از دیتابیس تنظیمات سیستم شما (جدول settings) بخوانیم
        $rate = $vendor->commission_rate ?? Config::get('market.default_commission_rate', 10);

        // محاسبه سهم سایت (کمیسیون)
        $siteShare = ($amount * $rate) / 100;

        // محاسبه سهم خالص فروشنده
        $vendorShare = $amount - $siteShare;

        return [
            'commission_rate' => $rate,
            'site_share'      => $siteShare,
            'vendor_share'    => $vendorShare,
        ];
    }
}
