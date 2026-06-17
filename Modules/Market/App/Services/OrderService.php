<?php

namespace Modules\Market\App\Services;

use Illuminate\Support\Facades\DB;
use Modules\Market\App\Models\Order;
use Modules\Market\App\Models\OrderItem;
use Modules\Market\Entities\VendorProduct; // 💡 برای واکشی اطلاعات فروشنده

class OrderService
{
    /**
     * Create a new order and its items.
     *
     * @param array $data
     * @return Order
     */
    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            // 💡 ایجاد سفارش با فیلدهای هماهنگ شده با دیتابیس
            $order = Order::create([
                'client_id'             => $data['client_id'],
                'shipping_address_json' => $data['shipping_address_json'],
                'payment_method'        => $data['payment_method'],
                'payment_status'        => 'unpaid', // وضعیت اولیه پرداخت
                'delivery_status'       => 'processing', // وضعیت اولیه ارسال
                'total_items_price'     => $data['total_items_price'],
                'grand_total'           => $data['grand_total'],
                'customer_notes'        => $data['customer_notes'] ?? null,
                // سایر فیلدها مثل tax و discount در صورت نیاز اینجا اضافه شوند
            ]);

            foreach ($data['items'] as $cartItem) {
                // برای ثبت آیتم، نیاز به اطلاعات فروشنده داریم
                // فرض می‌کنیم variant_id در $cartItem['id'] پاس داده شده
                $vendorProduct = VendorProduct::where('product_variant_id', $cartItem['id'])
                                    ->orderBy('price', 'asc') // یا هر منطق دیگری برای انتخاب فروشنده
                                    ->first();

                if (!$vendorProduct) {
                    // این اتفاق نباید بیفتد اگر موجودی قبلا چک شده باشد
                    throw new \Exception("فروشنده‌ای برای محصول {$cartItem['name']} یافت نشد.");
                }

                // 💡 ایجاد آیتم سفارش با فیلدهای هماهنگ شده با دیتابیس
                OrderItem::create([
                    'order_id'          => $order->id,
                    'vendor_product_id' => $vendorProduct->id,
                    'vendor_id'         => $vendorProduct->vendor_id,
                    'product_title'     => $cartItem['name'],
                    'quantity'          => $cartItem['quantity'],
                    'unit_price'        => $cartItem['price'],
                    'total_price'       => $cartItem['quantity'] * $cartItem['price'],
                ]);
            }

            return $order;
        });
    }
}
