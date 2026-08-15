<?php

namespace Modules\Accounting\App\Listeners;

use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Services\AccountingEngine;

class CrossModuleTransactionListener
{
    protected AccountingEngine $engine;

    public function __construct(AccountingEngine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * ثبت سند هنگام پرداخت فاکتور در ماژول Services
     */
    public function handleServiceInvoicePaid(object $invoice): void
    {
        // جلوگیری از ثبت تکراری
        $exists = DB::table('accounting_source_documents')
            ->where('sourceable_type', get_class($invoice))
            ->where('sourceable_id', $invoice->id)
            ->where('event_type', 'invoice_paid')
            ->exists();

        if ($exists) {
            return;
        }

        $this->engine->recordFromServiceInvoice($invoice);
    }

    /**
     * ثبت سند هنگام پرداخت نوبت در ماژول Booking
     */
    public function handleBookingPaymentConfirmed(object $payment): void
    {
        $exists = DB::table('accounting_source_documents')
            ->where('sourceable_type', get_class($payment))
            ->where('sourceable_id', $payment->id)
            ->where('event_type', 'payment_confirmed')
            ->exists();

        if ($exists) {
            return;
        }

        $appointment = method_exists($payment, 'appointment') ? $payment->appointment : null;
        $this->engine->recordBookingPayment($payment, $appointment);
    }

    /**
     * ثبت سند هنگام تکمیل سفارش در ماژول Market
     */
    public function handleMarketOrderPaid(object $order): void
    {
        // اگر سفارش از فاکتور Services ایجاد شده، از ثبت تکراری جلوگیری می‌شود
        if (!empty($order->source_invoice_id)) {
            return;
        }

        $exists = DB::table('accounting_source_documents')
            ->where('sourceable_type', get_class($order))
            ->where('sourceable_id', $order->id)
            ->where('event_type', 'order_paid')
            ->exists();

        if ($exists) {
            return;
        }

        $this->engine->recordMarketOrder($order);
    }

    /**
     * ثبت سند هنگام ایجاد تراکنش کیف پول در ماژول Wallet
     */
    public function handleWalletTransactionCreated(object $event): void
    {
        $transaction = $event instanceof \Modules\Wallet\App\Events\WalletTransactionCreated
            ? $event->transaction
            : $event;

        if ($transaction instanceof \Modules\Wallet\App\Models\WalletTransaction) {
            $this->engine->recordWalletTransaction($transaction);
        }
    }
}
