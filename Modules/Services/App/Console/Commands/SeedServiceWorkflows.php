<?php

namespace Modules\Services\App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Workflows\Entities\Workflow;

class SeedServiceWorkflows extends Command
{
    protected $signature = 'services:seed-workflows';
    protected $description = 'Seed default workflows for services and invoices';

    public function handle()
    {
        $this->info('Seeding default workflows for services...');

        // 1. Cancel Invoice -> Cancel Invoice, Payments, Orders
        $wf1 = Workflow::updateOrCreate(
            ['key' => 'system_cancel_invoice_order'],
            ['name' => 'لغو سفارش و فاکتور با لغو فاکتور', 'is_active' => true, 'created_by' => 1]
        );
        $wf1->triggers()->delete();
        $wf1->triggers()->create(['type' => 'EVENT', 'config' => ['event_key' => ['invoice_cancelled']]]);

        $wf1->stages()->delete();
        $wf1->nodes()->delete();
        $wf1->edges()->delete();
        $stage1 = $wf1->stages()->create(['name' => 'عملیات لغو', 'sort_order' => 1]);

        $stage1->actions()->create([
            'action_type' => 'CHANGE_SERVICE_STATUS',
            'sort_order' => 10,
            'config' => ['entity_type' => 'invoice', 'status_name' => 'لغو شده', 'status_type' => 'payment']
        ]);
        $stage1->actions()->create([
            'action_type' => 'CHANGE_SERVICE_STATUS',
            'sort_order' => 20,
            'config' => ['entity_type' => 'payment', 'status_name' => 'لغو شده', 'status_type' => 'payment']
        ]);
        $stage1->actions()->create([
            'action_type' => 'CHANGE_SERVICE_STATUS',
            'sort_order' => 30,
            'config' => ['entity_type' => 'order', 'status_name' => 'لغو شده', 'status_type' => 'order']
        ]);


        // 2. Overdue Invoice Daily
        $wf2 = Workflow::updateOrCreate(
            ['key' => 'system_overdue_invoice_daily'],
            ['name' => 'معوقه شدن فاکتور و غیرفعال شدن سفارش', 'is_active' => true, 'created_by' => 1]
        );
        $wf2->triggers()->delete();
        $wf2->triggers()->create([
            'type' => 'INVOICE_REMINDER',
            'config' => [
                'offset_days' => 1,
                'run_at_time' => '08:00',
                'payment_statuses' => ['در انتظار پرداخت']
            ]
        ]);

        $wf2->stages()->delete();
        $wf2->nodes()->delete();
        $wf2->edges()->delete();
        $stage2 = $wf2->stages()->create(['name' => 'معوقه شدن', 'sort_order' => 1]);
        $stage2->actions()->create([
            'action_type' => 'CHANGE_SERVICE_STATUS',
            'sort_order' => 10,
            'config' => ['entity_type' => 'invoice', 'status_name' => 'معوقه', 'status_type' => 'payment']
        ]);
        $stage2->actions()->create([
            'action_type' => 'CHANGE_SERVICE_STATUS',
            'sort_order' => 20,
            'config' => ['entity_type' => 'order', 'status_name' => 'غیر فعال', 'status_type' => 'order']
        ]);


        // 3. Invoice Paid -> Order Active
        $wf3 = Workflow::updateOrCreate(
            ['key' => 'system_invoice_paid'],
            ['name' => 'تکمیل پرداخت فاکتور و فعال‌سازی سفارش', 'is_active' => true, 'created_by' => 1]
        );
        $wf3->triggers()->delete();
        $wf3->triggers()->create(['type' => 'EVENT', 'config' => ['event_key' => ['invoice_paid'], 'payment_statuses' => ['پرداخت شده']]]);

        $wf3->stages()->delete();
        $wf3->nodes()->delete();
        $wf3->edges()->delete();
        $stage3 = $wf3->stages()->create(['name' => 'تکمیل پرداخت', 'sort_order' => 1]);
        $stage3->actions()->create([
            'action_type' => 'CHANGE_SERVICE_STATUS',
            'sort_order' => 10,
            'config' => ['entity_type' => 'order', 'status_name' => 'فعال', 'status_type' => 'order']
        ]);


        // 4. Invoice Unpaid -> Order Pending
        $wf4 = Workflow::updateOrCreate(
            ['key' => 'system_invoice_unpaid'],
            ['name' => 'لغو پرداخت فاکتور و تعلیق سفارش', 'is_active' => true, 'created_by' => 1]
        );
        $wf4->triggers()->delete();
        $wf4->triggers()->create(['type' => 'EVENT', 'config' => ['event_key' => ['invoice_unpaid'], 'payment_statuses' => ['در انتظار پرداخت', 'معوقه']]]);

        $wf4->stages()->delete();
        $wf4->nodes()->delete();
        $wf4->edges()->delete();
        $stage4 = $wf4->stages()->create(['name' => 'تعلیق سفارش', 'sort_order' => 1]);
        $stage4->actions()->create([
            'action_type' => 'CHANGE_SERVICE_STATUS',
            'sort_order' => 10,
            'config' => ['entity_type' => 'order', 'status_name' => 'در انتظار', 'status_type' => 'order']
        ]);


        // 5. Invoice Created -> Order Pending
        $wf5 = Workflow::updateOrCreate(
            ['key' => 'system_invoice_created'],
            ['name' => 'ثبت فاکتور و انتظار سفارش', 'is_active' => true, 'created_by' => 1]
        );
        $wf5->triggers()->delete();
        $wf5->triggers()->create(['type' => 'EVENT', 'config' => ['event_key' => ['invoice_created'], 'payment_statuses' => ['در انتظار پرداخت']]]);

        $wf5->stages()->delete();
        $wf5->nodes()->delete();
        $wf5->edges()->delete();
        $stage5 = $wf5->stages()->create(['name' => 'در انتظار پرداخت', 'sort_order' => 1]);
        $stage5->actions()->create([
            'action_type' => 'CHANGE_SERVICE_STATUS',
            'sort_order' => 10,
            'config' => ['entity_type' => 'order', 'status_name' => 'در انتظار', 'status_type' => 'order']
        ]);


        // 6. Overdue 7 days -> Cancel Order
        $wf6 = Workflow::updateOrCreate(
            ['key' => 'system_overdue_7days_cancel_order'],
            ['name' => 'لغو سفارش به دلیل عدم پرداخت طولانی (۷ روز)', 'is_active' => true, 'created_by' => 1]
        );
        $wf6->triggers()->delete();
        $wf6->triggers()->create([
            'type' => 'INVOICE_REMINDER',
            'config' => [
                'offset_days' => 7,
                'run_at_time' => '08:00',
                'payment_statuses' => ['معوقه']
            ]
        ]);

        $wf6->stages()->delete();
        $wf6->nodes()->delete();
        $wf6->edges()->delete();
        $stage6 = $wf6->stages()->create(['name' => 'لغو خودکار', 'sort_order' => 1]);
        $stage6->actions()->create([
            'action_type' => 'CHANGE_SERVICE_STATUS',
            'sort_order' => 10,
            'config' => ['entity_type' => 'order', 'status_name' => 'لغو شده', 'status_type' => 'order']
        ]);
        // 7. Revert Invoice Status to Overdue on Payment Cancelled
        $wf7 = Workflow::updateOrCreate(
            ['key' => 'system_invoice_unpaid_revert_overdue'],
            ['name' => 'بازگشت وضعیت فاکتور به معوقه در صورت لغو پرداخت', 'is_active' => true, 'created_by' => 1]
        );
        $wf7->triggers()->delete();
        $wf7->triggers()->create(['type' => 'EVENT', 'config' => ['event_key' => ['invoice_unpaid'], 'payment_statuses' => ['معوقه']]]);

        $wf7->stages()->delete();
        $wf7->nodes()->delete();
        $wf7->edges()->delete();
        $stage7 = $wf7->stages()->create(['name' => 'بازگشت وضعیت معوقه', 'sort_order' => 1]);
        $stage7->actions()->create([
            'action_type' => 'CHANGE_SERVICE_STATUS',
            'sort_order' => 10,
            'config' => ['entity_type' => 'invoice', 'status_name' => 'معوقه', 'status_type' => 'payment']
        ]);

        // 8. Revert Invoice Status to Pending on Payment Cancelled
        $wf8 = Workflow::updateOrCreate(
            ['key' => 'system_invoice_unpaid_revert_pending'],
            ['name' => 'بازگشت وضعیت فاکتور به در انتظار پرداخت در صورت لغو پرداخت', 'is_active' => true, 'created_by' => 1]
        );
        $wf8->triggers()->delete();
        $wf8->triggers()->create(['type' => 'EVENT', 'config' => ['event_key' => ['invoice_unpaid'], 'payment_statuses' => ['در انتظار پرداخت']]]);

        $wf8->stages()->delete();
        $wf8->nodes()->delete();
        $wf8->edges()->delete();
        $stage8 = $wf8->stages()->create(['name' => 'بازگشت وضعیت در انتظار', 'sort_order' => 1]);
        $stage8->actions()->create([
            'action_type' => 'CHANGE_SERVICE_STATUS',
            'sort_order' => 10,
            'config' => ['entity_type' => 'invoice', 'status_name' => 'در انتظار پرداخت', 'status_type' => 'payment']
        ]);

        // 9. Auto Create Invoice on Order Renewal Date
        $wf9 = Workflow::updateOrCreate(
            ['key' => 'system_order_renewal_auto_invoice'],
            ['name' => 'ساخت خودکار فاکتور با فرارسیدن تاریخ تمدید سفارش', 'is_active' => true, 'created_by' => 1]
        );
        $wf9->triggers()->delete();
        $wf9->triggers()->create([
            'type' => 'ORDER_RENEWAL_REMINDER',
            'config' => [
                'offset_days' => 0,
                'run_at_time' => '08:00',
                'order_statuses' => ['فعال'],
            ]
        ]);

        $wf9->stages()->delete();
        $wf9->nodes()->delete();
        $wf9->edges()->delete();
        $stage9 = $wf9->stages()->create(['name' => 'ساخت فاکتور تمدید', 'sort_order' => 1]);
        $stage9->actions()->create([
            'action_type' => 'CREATE_INVOICE',
            'sort_order' => 10,
            'config' => []
        ]);

        $this->info('Successfully seeded new Workflows for Services!');
    }
}
