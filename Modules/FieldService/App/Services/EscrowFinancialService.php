<?php

namespace Modules\FieldService\App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Enums\PaymentStatus;
use Modules\FieldService\App\Models\ServiceInvoice;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\Wallet\App\Services\WalletService;

class EscrowFinancialService
{
    protected FieldServiceWorkflowBridgeService $workflowBridge;

    public function __construct(FieldServiceWorkflowBridgeService $workflowBridge)
    {
        $this->workflowBridge = $workflowBridge;
    }

    /**
     * Generate or update a digital invoice for a service order.
     */
    public function generateInvoice(ServiceOrder $order, array $items = [], float $travelFee = 60000, float $discount = 0): ServiceInvoice
    {
        return DB::transaction(function () use ($order, $items, $travelFee, $discount) {
            $invoice = ServiceInvoice::firstOrNew(['order_id' => $order->id]);
            if (! $invoice->exists) {
                $invoice->invoice_number = ServiceInvoice::generateInvoiceNumber();
                $invoice->payment_status = PaymentStatus::PENDING;
            }

            $invoice->travel_fee = $travelFee;
            $invoice->discount_amount = $discount;
            $invoice->platform_commission_percent = (float) config('fieldservice.platform_commission_percent', 15.0);
            $invoice->save();

            if (!empty($items)) {
                // Remove existing items if regenerating
                $invoice->items()->delete();

                foreach ($items as $itemData) {
                    $qty = $itemData['quantity'] ?? 1;
                    $unitPrice = $itemData['unit_price'] ?? 0;
                    $invoice->items()->create([
                        'item_type'          => $itemData['type'] ?? 'labor',
                        'title'              => $itemData['title'] ?? 'شرح خدمات / قطعه',
                        'unit_price'         => $unitPrice,
                        'quantity'           => $qty,
                        'total_price'        => $unitPrice * $qty,
                        'warranty_months'    => $itemData['warranty_months'] ?? 3,
                        'part_serial_number' => $itemData['part_serial_number'] ?? null,
                    ]);
                }
            }

            $invoice->load('items');
            $invoice->recalculate();

            return $invoice;
        });
    }

    /**
     * Mark invoice as paid into Escrow (حساب امانی).
     */
    public function lockInEscrow(ServiceInvoice $invoice, string $method = 'online', ?string $trackingNumber = null): bool
    {
        return DB::transaction(function () use ($invoice, $method, $trackingNumber) {
            $invoice->payment_method  = $method;
            $invoice->payment_status  = PaymentStatus::ESCROW_HELD;
            $invoice->tracking_number = $trackingNumber ?: 'TRX-' . time();
            $invoice->paid_at         = now();
            
            // Set 90-day warranty until
            $warrantyDays = (int) config('fieldservice.default_warranty_days', 90);
            $invoice->warranty_until  = Carbon::now()->addDays($warrantyDays);
            $invoice->save();

            $order = $invoice->order;
            if ($order) {
                $order->status = OrderStatus::INVOICE_APPROVED;
                $order->save();
            }

            Log::info("[EscrowService] Amount {$invoice->total_amount} locked in Escrow for Invoice #{$invoice->invoice_number} (Order #{$order?->order_number}).");

            $this->workflowBridge->triggerInvoicePaid($invoice);
            return true;
        });
    }

    /**
     * Release Escrow funds to the technician's wallet upon order satisfaction/completion.
     */
    public function releaseEscrowToTechnician(ServiceInvoice $invoice): bool
    {
        if ($invoice->payment_status !== PaymentStatus::ESCROW_HELD) {
            Log::warning("[EscrowService] Cannot release invoice #{$invoice->invoice_number}: payment status is {$invoice->payment_status?->value}.");
            return false;
        }

        return DB::transaction(function () use ($invoice) {
            $order = $invoice->order;
            $technician = $order?->technician;

            if ($technician && class_exists(WalletService::class)) {
                try {
                    $walletService = app(WalletService::class);
                    $netAmount = (float) $invoice->technician_net_amount;

                    if ($netAmount > 0) {
                        $walletService->deposit(
                            holder: $technician,
                            amount: $netAmount,
                            type: 'field_service_payout',
                            payable: $invoice,
                            description: "تسویه امانی سفارش خدمات #{$order->order_number} (فاکتور {$invoice->invoice_number})",
                            meta: [
                                'order_id'       => $order->id,
                                'order_number'   => $order->order_number,
                                'invoice_number' => $invoice->invoice_number,
                                'labor_amount'   => $invoice->labor_amount,
                                'parts_amount'   => $invoice->parts_amount,
                                'travel_fee'     => $invoice->travel_fee,
                                'commission'     => $invoice->platform_commission_amount,
                            ]
                        );

                        Log::info("[EscrowService] Credited {$netAmount} to technician {$technician->full_name}'s wallet.");
                    }

                    // Also check if order was referred by a vendor store partner
                    if ($order->vendor && $order->vendor->referral_commission_percent > 0) {
                        $vendorCut = round(($invoice->labor_amount * $order->vendor->referral_commission_percent) / 100);
                        if ($vendorCut > 0) {
                            $walletService->deposit(
                                holder: $order->vendor,
                                amount: $vendorCut,
                                type: 'referral_bonus',
                                payable: $order,
                                description: "پورسانت معرفی و ثبت سفارش #{$order->order_number} فروشگاه",
                                meta: ['order_id' => $order->id]
                            );
                            Log::info("[EscrowService] Credited {$vendorCut} referral commission to vendor {$order->vendor->store_name}.");
                        }
                    }
                } catch (\Throwable $e) {
                    Log::error("[EscrowService] Failed to deposit funds to wallet: " . $e->getMessage());
                }
            }

            $invoice->payment_status = PaymentStatus::RELEASED;
            $invoice->escrow_released_at = now();
            $invoice->save();

            if ($order) {
                $order->status = OrderStatus::COMPLETED;
                $order->completed_at = now();
                $order->save();

                if ($technician) {
                    $technician->is_busy = false;
                    $technician->completed_orders_count = ($technician->completed_orders_count ?: 0) + 1;
                    $technician->save();
                }

                $this->workflowBridge->triggerOrderCompleted($order);
            }

            Log::info("[EscrowService] Escrow released successfully for Invoice #{$invoice->invoice_number}.");
            return true;
        });
    }
}
