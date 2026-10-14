<?php

namespace Modules\Market\App\Services\Vision;

use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Market\App\Models\Order;
use Modules\Market\App\Models\OrderMeta;
use Modules\Market\App\Models\OrderSyncLog;
use Modules\Market\Entities\VendorVisionSetting;

class VisionOrderDispatchService
{
    /**
     * Dispatch paid order to Vision ERP for vendors who have active Vision settings.
     *
     * @param Order $order
     * @return array Array of dispatch results keyed by vendor_id
     */
    public function dispatchOrder(Order $order): array
    {
        // Load items with relations
        $order->loadMissing(['items.vendorProduct.productVariant.masterProduct', 'client']);

        $results = [];

        // Group order items by vendor_id
        $itemsByVendor = $order->items->groupBy('vendor_id');

        foreach ($itemsByVendor as $vendorId => $items) {
            $setting = VendorVisionSetting::where('vendor_id', $vendorId)
                ->where('is_active', true)
                ->first();

            if (!$setting) {
                continue; // This vendor does not use Vision
            }

            $client = new VisionSoapClient($setting);
            $results[$vendorId] = $this->dispatchForVendor($order, $items, $setting, $client);
        }

        return $results;
    }

    /**
     * Dispatch vendor specific portion of order to Vision ERP
     */
    protected function dispatchForVendor(Order $order, $items, VendorVisionSetting $setting, VisionSoapClient $client): array
    {
        try {
            // Check if already dispatched
            $existingMeta = OrderMeta::where('order_id', $order->id)
                ->where('key', 'vision_factor_number_vendor_' . $setting->vendor_id)
                ->first();

            if ($existingMeta) {
                return [
                    'success' => true,
                    'vendor_id' => $setting->vendor_id,
                    'factor_number' => $existingMeta->value,
                    'message' => 'این سفارش پیش‌تر به ویژن ارسال شده است.',
                ];
            }

            // Customer Code: customer's vision_code or fallback to general default_customer_code
            $customerCode = $order->client?->vision_code ?: $setting->default_customer_code;
            if (empty($customerCode)) {
                $customerCode = '1'; // Ultimate fallback
            }

            // Visitor & Warehouse codes
            $visitorCode = $setting->default_visitor_code ?: '1';
            $warehouseCode = $setting->default_warehouse_code ?: '1';

            // Prepare items payload
            $factorItems = [];
            foreach ($items as $item) {
                $variant = $item->vendorProduct?->productVariant;
                $master = $variant?->masterProduct;

                // Priority: crm_code on master product, or SKU, or barcode (gtin)
                $goodCode = $master?->crm_code ?: ($variant?->sku ?: $master?->gtin);

                $factorItems[] = [
                    'GoodCode' => (string)($goodCode ?: $item->vendor_product_id),
                    'GoodName' => (string)$item->product_title,
                    'Qty' => (float)$item->quantity,
                    'Price' => (float)$item->unit_price,
                    'TotalPrice' => (float)$item->total_price,
                    'Tax' => (float)($item->unit_tax * $item->quantity),
                ];
            }

            // Prepare factor header & body data according to Vision SaveOrderOrFactor
            $factorData = [
                'OrderNo' => (string)$order->id,
                'OrderDate' => now()->format('Y-m-d H:i:s'),
                'CustomerCode' => (string)$customerCode,
                'VisitorCode' => (string)$visitorCode,
                'WarehouseCode' => (string)$warehouseCode,
                'TotalAmount' => (float)$items->sum('total_price'),
                'Description' => 'سفارش سایت #' . $order->id . ' - خریدار: ' . ($order->client?->full_name ?: 'مهمان'),
                'Items' => $factorItems,
            ];

            // Send to Vision via SaveOrderOrFactor
            $response = $client->callAction('SaveOrderOrFactor', [
                'data' => json_encode($factorData, JSON_UNESCAPED_UNICODE),
            ]);

            if (!$response['success']) {
                $this->logDispatchError($order, $setting, $response['message']);
                return [
                    'success' => false,
                    'vendor_id' => $setting->vendor_id,
                    'message' => $response['message'],
                ];
            }

            $resData = $response['data'];
            $factorNumber = null;

            if (is_array($resData)) {
                $factorNumber = $resData['FactorNo'] ?? $resData['OrderNo'] ?? $resData['id'] ?? $resData['Id'] ?? null;
            } elseif (is_numeric($resData) || is_string($resData)) {
                $factorNumber = (string)$resData;
            }

            $factorNumber = $factorNumber ?: ('VIS-' . $order->id);

            // Record in order meta
            OrderMeta::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'key' => 'vision_factor_number_vendor_' . $setting->vendor_id,
                ],
                [
                    'value' => (string)$factorNumber,
                ]
            );

            // Also keep standard vision_factor_number for single vendor / primary vendor
            OrderMeta::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'key' => 'vision_factor_number',
                ],
                [
                    'value' => (string)$factorNumber,
                ]
            );

            // Log in order_sync_logs
            OrderSyncLog::create([
                'order_id' => $order->id,
                'client_id' => $order->client_id,
                'field_key' => 'vision_factor_number',
                'old_value' => null,
                'new_value' => (string)$factorNumber,
                'source' => 'vision_erp',
                'status' => 'auto_applied',
                'reviewed_at' => now(),
            ]);

            return [
                'success' => true,
                'vendor_id' => $setting->vendor_id,
                'factor_number' => $factorNumber,
                'message' => 'فاکتور با موفقیت در ویژن ثبت گردید (شماره: ' . $factorNumber . ')',
            ];

        } catch (Exception $e) {
            Log::error('Vision Order Dispatch Exception: ' . $e->getMessage(), ['order_id' => $order->id, 'vendor_id' => $setting->vendor_id]);
            $this->logDispatchError($order, $setting, $e->getMessage());

            return [
                'success' => false,
                'vendor_id' => $setting->vendor_id,
                'message' => 'خطا در ارسال سفارش: ' . $e->getMessage(),
            ];
        }
    }

    protected function logDispatchError(Order $order, VendorVisionSetting $setting, string $errorMsg): void
    {
        OrderSyncLog::create([
            'order_id' => $order->id,
            'client_id' => $order->client_id,
            'field_key' => 'vision_factor_error',
            'old_value' => null,
            'new_value' => substr($errorMsg, 0, 255),
            'source' => 'vision_erp',
            'status' => 'rejected',
            'reviewed_at' => now(),
        ]);
    }
}
