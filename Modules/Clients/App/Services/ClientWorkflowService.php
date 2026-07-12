<?php

namespace Modules\Clients\App\Services;

class ClientWorkflowService
{
    public static function workflowTriggerOptions(): array
    {
        return [
            'client_created' => 'ایجاد پرونده کلاینت جدید',
            'client_updated' => 'به‌روزرسانی اطلاعات کلاینت',
            'client_status_changed' => 'تغییر وضعیت کلاینت',
        ];
    }
}
