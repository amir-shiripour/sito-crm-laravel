<?php

namespace Modules\Workflows\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Workflows\Entities\Workflow;
use Modules\Workflows\Entities\WorkflowStage;
use Modules\Workflows\Entities\WorkflowAction;

class WorkflowsSeeder extends Seeder
{
    public function run(): void
    {
        if (Workflow::query()->exists()) {
            return;
        }

        $sales = Workflow::create([
            'name'        => 'گردش کار فروش',
            'key'         => 'sales_workflow',
            'description' => 'از شناسایی نیاز مشتری تا بستن معامله.',
            'is_active'   => true,
        ]);

        $s1 = WorkflowStage::create([
            'workflow_id' => $sales->id,
            'name'        => 'شناسایی نیاز مشتری',
            'description' => 'بررسی و ارزیابی نیازهای مشتری.',
            'sort_order'  => 10,
            'is_initial'  => true,
            'is_final'    => false,
        ]);

        WorkflowAction::create([
            'stage_id'    => $s1->id,
            'action_type' => WorkflowAction::TYPE_CREATE_TASK,
            'sort_order'  => 10,
            'config'      => [
                'title'        => 'ارزیابی نیازهای مشتری',
                'task_type'    => 'GENERAL',
                'status'       => 'TODO',
                'priority'     => 'MEDIUM',
                'offset_days'  => 0,
            ],
        ]);

        WorkflowAction::create([
            'stage_id'    => $s1->id,
            'action_type' => WorkflowAction::TYPE_CREATE_REMINDER,
            'sort_order'  => 20,
            'config'      => [
                'offset_minutes' => -24 * 60,
                'channel'        => 'IN_APP',
                'message'        => 'یادآوری برای ارزیابی نیازهای مشتری',
            ],
        ]);

        $s2 = WorkflowStage::create([
            'workflow_id' => $sales->id,
            'name'        => 'ارسال پیشنهاد',
            'description' => 'ارسال پروپوزال و پیگیری برای دریافت بازخورد.',
            'sort_order'  => 20,
            'is_initial'  => false,
            'is_final'    => false,
        ]);

        WorkflowAction::create([
            'stage_id'    => $s2->id,
            'action_type' => WorkflowAction::TYPE_CREATE_TASK,
            'sort_order'  => 10,
            'config'      => [
                'title'       => 'ارسال پیشنهاد به مشتری',
                'task_type'   => 'GENERAL',
                'status'      => 'TODO',
                'priority'    => 'HIGH',
                'offset_days' => 0,
            ],
        ]);

        WorkflowAction::create([
            'stage_id'    => $s2->id,
            'action_type' => WorkflowAction::TYPE_CREATE_FOLLOWUP,
            'sort_order'  => 20,
            'config'      => [
                'title'       => 'پیگیری پیشنهاد ارسال شده',
                'status'      => 'TODO',
                'priority'    => 'HIGH',
                'offset_days' => 3,
            ],
        ]);

        WorkflowAction::create([
            'stage_id'    => $s2->id,
            'action_type' => WorkflowAction::TYPE_CREATE_REMINDER,
            'sort_order'  => 30,
            'config'      => [
                'offset_minutes' => 2 * 24 * 60,
                'channel'        => 'EMAIL',
                'message'        => 'یادآوری برای پیگیری پیشنهاد ارسال‌شده.',
            ],
        ]);

        $s3 = WorkflowStage::create([
            'workflow_id' => $sales->id,
            'name'        => 'بستن قرارداد',
            'description' => 'نهایی کردن توافق با مشتری و عقد قرارداد.',
            'sort_order'  => 30,
            'is_initial'  => false,
            'is_final'    => true,
        ]);

        WorkflowAction::create([
            'stage_id'    => $s3->id,
            'action_type' => WorkflowAction::TYPE_CREATE_TASK,
            'sort_order'  => 10,
            'config'      => [
                'title'       => 'نهایی کردن قرارداد',
                'task_type'   => 'GENERAL',
                'status'      => 'TODO',
                'priority'    => 'HIGH',
                'offset_days' => 0,
            ],
        ]);

        WorkflowAction::create([
            'stage_id'    => $s3->id,
            'action_type' => WorkflowAction::TYPE_CREATE_REMINDER,
            'sort_order'  => 20,
            'config'      => [
                'offset_minutes' => -24 * 60,
                'channel'        => 'IN_APP',
                'message'        => 'یادآوری برای نهایی کردن قرارداد.',
            ],
        ]);
    }
}
