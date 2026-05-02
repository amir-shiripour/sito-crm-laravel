<?php

namespace Modules\Market\Services;

use Modules\Market\Entities\Order;
use Modules\Market\Entities\OrderItem;
use Modules\Market\Entities\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * ثبت سفارش جدید
     *
     * @param User $user
     * @param array $cartItems (آرایه‌ای از محصولات همراه با تعداد)
     * @param array $shippingData (اطلاعات ارسال)
     * @return Order
     * @throws Exception
     */
    public function placeOrder(User $user, array $cartItems, array $shippingData): Order
    {
        return DB::transaction(function () use ($user, $cartItems, $shippingData) {

            $totalItemsPrice = 0;
            $totalTax = 0;
            $orderItemsData = [];

            // 1. بررسی و پردازش اقلام سبد خرید
            foreach ($cartItems as $item) {
                // استفاده از قفل گذاری (lockForUpdate) برای جلوگیری از تداخل خرید همزمان دو نفر
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->first();

                if (!$product || $product->status !== 'published') {
                    throw new Exception("محصول {$item['product_id']} در دسترس نیست.");
                }

                if ($product->stock < $item['quantity']) {
                    throw new Exception("موجودی محصول '{$product->title}' کافی نیست.");
                }

                // محاسبه قیمت (با احتساب تخفیف اگر دارد)
                $unitPrice = $product->discount_price ?? $product->price;
                $unitTax = ($unitPrice * $product->tax_percent) / 100;
                $totalPrice = ($unitPrice + $unitTax) * $item['quantity'];

                $totalItemsPrice += ($unitPrice * $item['quantity']);
                $totalTax += ($unitTax * $item['quantity']);

                // دریافت درصد کمیسیون در لحظه خرید
                $commissionData = $this->commissionService->calculate($product->vendor, $totalPrice);

                $orderItemsData[] = [
                    'product_id'             => $product->id,
                    'vendor_id'              => $product->vendor_id,
                    'product_title'          => $product->title,
                    'quantity'               => $item['quantity'],
                    'unit_price'             => $unitPrice,
                    'unit_tax'               => $unitTax,
                    'total_price'            => $totalPrice,
                    'vendor_commission_rate' => $commissionData['commission_rate']
                ];

                // کسر از موجودی انبار
                $product->decrement('stock', $item['quantity']);
            }

            // 2. محاسبه مبالغ کل
            $shippingCost = $shippingData['cost'] ?? 0;
            $totalDiscount = $shippingData['discount'] ?? 0;
            $grandTotal = ($totalItemsPrice + $totalTax + $shippingCost) - $totalDiscount;

            // 3. ایجاد رکورد اصلی سفارش
            $order = Order::create([
                'user_id'               => $user->id,
                'total_items_price'     => $totalItemsPrice,
                'total_shipping_cost'   => $shippingCost,
                'total_tax'             => $totalTax,
                'total_discount'        => $totalDiscount,
                'grand_total'           => $grandTotal,
                'shipping_address_json' => $shippingData['address'] ?? null,
                'shipping_method'       => $shippingData['method'] ?? null,
            ]);

            // 4. ذخیره اقلام سفارش
            foreach ($orderItemsData as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            return $order;
        });
    }
}
