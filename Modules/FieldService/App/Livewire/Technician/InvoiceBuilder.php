<?php

namespace Modules\FieldService\App\Livewire\Technician;

use Livewire\Component;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Enums\PaymentStatus;
use Modules\FieldService\App\Models\ServiceInvoice;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Models\ServiceOrderItem;
use Modules\FieldService\App\Services\FieldServiceWorkflowBridgeService;

class InvoiceBuilder extends Component
{
    public int $orderId;
    public ?int $invoiceId = null;

    public array $items = [];
    public float $travel_fee = 600000; // 60,000 Tomans base
    public float $discount_amount = 0;

    public string $new_item_type = 'labor';
    public string $new_title = '';
    public float $new_unit_price = 0;
    public int $new_quantity = 1;
    public int $new_warranty_months = 3;

    public function mount(int $orderId)
    {
        $this->orderId = $orderId;
        $order = ServiceOrder::with('invoice.items')->find($orderId);

        if ($order && $order->invoice) {
            $this->invoiceId = $order->invoice->id;
            $this->travel_fee = (float) $order->invoice->travel_fee;
            $this->discount_amount = (float) $order->invoice->discount_amount;
            $this->items = $order->invoice->items->toArray();
        } else {
            // Default first item from order category base labor fee
            $baseFee = $order?->category?->base_labor_fee ?: 1500000;
            $this->items[] = [
                'item_type'       => 'labor',
                'title'           => 'اجرت عیب‌یابی و تعمیر تخصصی دستگاه',
                'unit_price'      => $baseFee,
                'quantity'        => 1,
                'total_price'     => $baseFee,
                'warranty_months' => 3,
            ];
        }
    }

    public function addItem()
    {
        $this->validate([
            'new_title'      => 'required|string|max:150',
            'new_unit_price' => 'required|numeric|min:0',
            'new_quantity'   => 'required|integer|min:1',
        ]);

        $this->items[] = [
            'item_type'       => $this->new_item_type,
            'title'           => $this->new_title,
            'unit_price'      => $this->new_unit_price,
            'quantity'        => $this->new_quantity,
            'total_price'     => $this->new_unit_price * $this->new_quantity,
            'warranty_months' => $this->new_warranty_months,
        ];

        $this->reset(['new_title', 'new_unit_price', 'new_quantity']);
    }

    public function removeItem(int $index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function submitInvoice()
    {
        $order = ServiceOrder::find($this->orderId);
        if (!$order) return;

        $invoice = ServiceInvoice::updateOrCreate(
            ['order_id' => $order->id],
            [
                'invoice_number'              => ServiceInvoice::generateInvoiceNumber(),
                'travel_fee'                  => $this->travel_fee,
                'discount_amount'             => $this->discount_amount,
                'platform_commission_percent' => 15.00,
                'payment_status'              => PaymentStatus::PENDING,
            ]
        );

        $invoice->items()->delete();
        foreach ($this->items as $item) {
            ServiceOrderItem::create([
                'invoice_id'      => $invoice->id,
                'item_type'       => $item['item_type'],
                'title'           => $item['title'],
                'unit_price'      => $item['unit_price'],
                'quantity'        => $item['quantity'],
                'total_price'     => $item['total_price'],
                'warranty_months' => $item['warranty_months'] ?? 3,
            ]);
        }

        $invoice->recalculate();

        $order->status = OrderStatus::INVOICE_PENDING;
        $order->save();

        app(FieldServiceWorkflowBridgeService::class)->triggerInvoiceSubmitted($invoice);

        $this->dispatch('notify', [
            'type' => 'success',
            'text' => "فاکتور تفکیکی به شماره {$invoice->invoice_number} صادر و جهت تایید و پرداخت به مشتری ارسال شد.",
        ]);
    }

    public function render()
    {
        $laborTotal = 0;
        $partsTotal = 0;

        foreach ($this->items as $item) {
            if ($item['item_type'] === 'labor') {
                $laborTotal += $item['total_price'];
            } else {
                $partsTotal += $item['total_price'];
            }
        }

        $grandTotal = max(0, ($laborTotal + $partsTotal + $this->travel_fee) - $this->discount_amount);
        $commission = round(($laborTotal * 15) / 100);
        $netIncome  = ($laborTotal - $commission) + $partsTotal + $this->travel_fee;

        return view('fieldservice::livewire.technician.invoice-builder', [
            'laborTotal' => $laborTotal,
            'partsTotal' => $partsTotal,
            'grandTotal' => $grandTotal,
            'commission' => $commission,
            'netIncome'  => $netIncome,
        ]);
    }
}
