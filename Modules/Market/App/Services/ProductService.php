<?php
namespace Modules\Market\App\Services;

use Modules\Market\Entities\MasterProduct;
use Modules\Market\Entities\MarketSetting;
use Modules\Market\Entities\Brand;
use Modules\Market\Entities\Category;

class ProductService
{
    public function generateCrmCode(int $brandId, int $categoryId): string
    {
        $prefix = MarketSetting::getValue('system.product_prefix', 'SIT');

        $brand = Brand::find($brandId);
        $category = Category::find($categoryId);

        if (!$brand || !$category) return 'نامشخص';

        // تبدیل به عدد برای اطمینان از محاسبات ریاضی
        $brandCode = (int) $brand->code_prefix; // مثلا 1000
        $categoryOffset = (int) $category->code_offset; // مثلا 100000 یا 200000

        // محاسبه بدنه کد به صورت ریاضی
        // با این کار 1000 و 100000 با هم جمع شده و می شوند 101000
        $codeBodyNumeric = $brandCode + $categoryOffset;

        // پیدا کردن آخرین محصولی که با این الگوی عددی شروع می‌شود
        $lastProduct = MasterProduct::where('crm_code', 'like', "{$prefix}-{$codeBodyNumeric}%")
            ->latest('id')
            ->first();

        if ($lastProduct) {
            // استخراج 5 رقم آخر (بخش سریال)
            $lastSerial = (int) substr($lastProduct->crm_code, -5);
            $newSerialNum = $lastSerial + 1;
        } else {
            $newSerialNum = 1;
        }

        // فرمت نهایی: SIT-10100000001 (سریال با 5 رقم ثابت)
        $newSerial = str_pad($newSerialNum, 5, '0', STR_PAD_LEFT);

        return "{$prefix}-{$codeBodyNumeric}{$newSerial}";
    }
}
