<?php

namespace Modules\Accounting\App\Listeners;

use Modules\Accounting\App\Events\PaymentRecorded;
use Modules\Accounting\App\Services\AccountingEngine;
use Modules\Accounting\App\Models\Category;

class RecordPaymentInAccounting
{
    protected AccountingEngine $engine;

    /**
     * Create the event listener.
     */
    public function __construct(AccountingEngine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentRecorded $event): void
    {
        // در اینجا فرض می‌کنیم پرداخت بابت نوبت‌دهی است. در یک سیستم واقعی‌تر،
        // شاید نیاز باشد نوع درآمد را از خود Event دریافت کنیم.
        $incomeCategory = Category::where('title', 'درآمد خدمات نوبت‌دهی')->first();
        $bankCategory = Category::where('title', 'موجودی حساب‌های بانکی')->first();

        if ($incomeCategory && $bankCategory) {
            $this->engine->recordJournalEntry(
                $event->documentable,
                $event->amount,
                $bankCategory->id,    // بدهکار: موجودی بانک
                $incomeCategory->id,  // بستانکار: درآمد
                $event->fundAccountId,
                $event->description ?: 'ثبت خودکار پرداخت'
            );
        }
    }
}
