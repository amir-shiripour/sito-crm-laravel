<?php

namespace Modules\Market\Services;

use Modules\Market\Entities\Order;
use Modules\Market\Entities\OrderItem;
use Modules\Market\Entities\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;
use Modules\Market\Entities\ProductVariant;
use Modules\Market\Entities\Vendor;
use Modules\Market\Entities\VendorProduct;
use Modules\Market\Entities\Warehouse;

class OrderService
{
    protected $commissionService;
    protected $warehouseStockService;

    public function __construct(CommissionService $commissionService, WarehouseStockService $warehouseStockService)
    {
        $this->commissionService = $commissionService;
        $this->warehouseStockService = $warehouseStockService;
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
            $wmsActive = $this->warehouseStockService->isWmsActive();

            // 1. بررسی و پردازش اقلام سبد خرید
            foreach ($cartItems as $item) {
                $variant = ProductVariant::with('product.vendor')->find($item['variant_id']);
                $vendorProduct = isset($item['vendor_product_id']) ? VendorProduct::find($item['vendor_product_id']) : null;

                if (!$variant) {
                    throw new Exception("Product variant {$item['variant_id']} not found.");
                }

                $product = $variant->product;
                $vendor = $vendorProduct ? $vendorProduct->vendor : $product->vendor;

                // موجودی را با سرویس جدید چک کن
                $availableStock = $this->warehouseStockService->getAvailableStock($variant->id, $vendorProduct->id ?? null);
                if ($availableStock < $item['quantity']) {
                    throw new Exception("Not enough stock for product '{$product->title}'.");
                }

                // محاسبه قیمت
                $unitPrice = $vendorProduct->price ?? $variant->price;
                $unitTax = 0; // You can add tax calculation logic here if needed
                $totalPrice = $unitPrice * $item['quantity'];

                $totalItemsPrice += $totalPrice;
                $totalTax += ($unitTax * $item['quantity']);

                $commissionData = $this->commissionService->calculate($vendor, $totalPrice);

                $orderItemsData[] = [
                    'product_variant_id'     => $variant->id,
                    'vendor_product_id'      => $vendorProduct->id ?? null,
                    'vendor_id'              => $vendor->id,
                    'product_title'          => $product->title . ' (' . $variant->name . ')',
                    'quantity'               => $item['quantity'],
                    'unit_price'             => $unitPrice,
                    'unit_tax'               => $unitTax,
                    'total_price'            => $totalPrice,
                    'vendor_commission_rate' => $commissionData['commission_rate']
                ];

                // اگر WMS فعال است، موجودی را رزرو کن، در غیر این صورت از انبار سنتی کم کن
                if ($wmsActive) {
                    $warehouse = $this->findWarehouseForReservation($vendor);
                    $this->warehouseStockService->reserveStock($warehouse->id, $variant->id, $item['quantity'], $vendorProduct->id ?? null);
                } else {
                    if ($vendorProduct) {
                        $vendorProduct->decrement('stock', $item['quantity']);
                    } else {
                        $variant->decrement('stock', $item['quantity']);
                    }
                }
            }

            // 2. محاسبه مبالغ کل
            $shippingCost = $shippingData['cost'] ?? 0;
            $totalDiscount = $shippingData['discount'] ?? 0;
            $grandTotal = ($totalItemsPrice + $totalTax + $shippingCost) - $totalDiscount;

            // 3. ایجاد رکورد اصلی سفارش
            $order = Order::create([
                'user_id'               => $user->id,
                'status'                => 'pending', // وضعیت اولیه
                'total_items_price'     => $totalItemsPrice,
                'total_shipping_cost'   => $shippingCost,
                'total_tax'             => $totalTax,
                'total_discount'        => $totalDiscount,
                'grand_total'           => $grandTotal,
                'shipping_address_json' => json_encode($shippingData['address'] ?? null),
                'shipping_method'       => $shippingData['method'] ?? null,
            ]);

            // 4. ذخیره اقلام سفارش
            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            return $order;
        });
    }

    /**
     * لغو سفارش و بازگرداندن موجودی
     */
    public function cancelOrder(Order $order)
    {
        if ($order->status !== 'pending' && $order->status !== 'processing') {
            throw new Exception("Order cannot be canceled at its current state.");
        }

        return DB::transaction(function () use ($order) {
            if ($this->warehouseStockService->isWmsActive()) {
                foreach ($order->items as $item) {
                    $vendor = $item->vendor;
                    $warehouse = $this->findWarehouseForReservation($vendor);
                    $this->warehouseStockService->releaseReservedStock($warehouse->id, $item->product_variant_id, $item->quantity, $item->vendor_product_id, $order);
                }
            } else {
                // بازگرداندن موجودی به روش سنتی
                foreach ($order->items as $item) {
                    if ($item->vendor_product_id) {
                        VendorProduct::find($item->vendor_product_id)->increment('stock', $item->quantity);
                    } else {
                        ProductVariant::find($item->product_variant_id)->increment('stock', $item->quantity);
                    }
                }
            }

            $order->update(['status' => 'canceled']);
            return $order;
        });
    }

    /**
     * نهایی کردن ارسال و خروج قطعی از انبار
     */
    public function shipOrder(Order $order)
    {
        if ($order->status !== 'processing') {
            throw new Exception("Order is not ready for shipping.");
        }

        return DB::transaction(function () use ($order) {
            if ($this->warehouseStockService->isWmsActive()) {
                foreach ($order->items as $item) {
                    $vendor = $item->vendor;
                    $warehouse = $this->findWarehouseForReservation($vendor);
                    $this->warehouseStockService->finalizeShipment($warehouse->id, $item->product_variant_id, $item->quantity, $item->vendor_product_id, $order);
                }
            }
            // در حالت سنتی، موجودی قبلا در زمان ثبت سفارش کم شده است و کار دیگری لازم نیست

            $order->update(['status' => 'shipped']);
            return $order;
        });
    }

    /**
     * انبار مناسب برای رزرو کالا را پیدا می‌کند (انبار فروشنده یا انبار مرکزی)
     */
    private function findWarehouseForReservation(Vendor $vendor): Warehouse
    {
        // اگر فروشنده انبار اختصاصی و فعال دارد
        $vendorWarehouse = Warehouse::where('vendor_id', $vendor->id)->where('is_active', true)->first();
        if ($vendorWarehouse) {
            return $vendorWarehouse;
        }

        // در غیر این صورت، انبار مرکزی را برمی‌گرداند
        $centralWarehouse = Warehouse::whereNull('vendor_id')->where('is_active', true)->first();
        if (!$centralWarehouse) {
            throw new Exception("No active central warehouse found.");
        }
        return $centralWarehouse;
    }
}
