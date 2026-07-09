<?php

declare(strict_types=1);

namespace Modules\Sales\App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateFollowupTaskJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * مدل تماس (ClientCall یا SalesCall)
     */
    public mixed $call;

    /**
     * ایجاد نمونه جدید جاب
     */
    public function __construct(mixed $call)
    {
        $this->call = $call;
    }

    /**
     * اجرای منطق ایجاد تسک در پس‌زمینه صف
     */
    public function handle(): void
    {
        app(\Modules\Sales\App\Services\SalesAutomationService::class)->handleCallLogged($this->call);
    }
}
