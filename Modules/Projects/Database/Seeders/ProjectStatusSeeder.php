<?php

namespace Modules\Projects\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Projects\App\Http\Models\ProjectStatus;

class ProjectStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            // Project Statuses
            [
                'name' => 'در صف',
                'color' => '#f59e0b',
                'type' => 'project',
                'is_default' => true,
                'is_final' => false,
                'is_readonly' => false,
                'attributes' => ['is_queued' => true],
            ],
            [
                'name' => 'در حال انجام',
                'color' => '#3b82f6',
                'type' => 'project',
                'is_default' => false,
                'is_final' => false,
                'is_readonly' => false,
                'attributes' => ['is_in_progress' => true],
            ],
            [
                'name' => 'تکمیل شده',
                'color' => '#10b981',
                'type' => 'project',
                'is_default' => false,
                'is_final' => true,
                'is_readonly' => false,
                'attributes' => ['is_completed' => true],
            ],
            [
                'name' => 'لغو شده',
                'color' => '#ef4444',
                'type' => 'project',
                'is_default' => false,
                'is_final' => true,
                'is_readonly' => true,
                'attributes' => ['is_canceled' => true],
            ],
            [
                'name' => 'تعویق',
                'color' => '#f97316',
                'type' => 'project',
                'is_default' => false,
                'is_final' => false,
                'is_readonly' => false,
                'attributes' => ['is_delayed' => true],
            ],

            // Task Statuses
            [
                'name' => 'در صف',
                'color' => '#f59e0b',
                'type' => 'task',
                'is_default' => true,
                'is_final' => false,
                'is_readonly' => false,
                'attributes' => ['is_queued' => true],
            ],
            [
                'name' => 'در حال انجام',
                'color' => '#3b82f6',
                'type' => 'task',
                'is_default' => false,
                'is_final' => false,
                'is_readonly' => false,
                'attributes' => ['is_in_progress' => true],
            ],
            [
                'name' => 'تکمیل شده',
                'color' => '#10b981',
                'type' => 'task',
                'is_default' => false,
                'is_final' => true,
                'is_readonly' => false,
                'attributes' => ['is_completed' => true],
            ],
            [
                'name' => 'لغو شده',
                'color' => '#ef4444',
                'type' => 'task',
                'is_default' => false,
                'is_final' => true,
                'is_readonly' => true,
                'attributes' => ['is_canceled' => true],
            ],
            [
                'name' => 'تعویق',
                'color' => '#f97316',
                'type' => 'task',
                'is_default' => false,
                'is_final' => false,
                'is_readonly' => false,
                'attributes' => ['is_delayed' => true],
            ],

            // Checklist Statuses
            [
                'name' => 'در حال انجام',
                'color' => '#3b82f6',
                'type' => 'checklist',
                'is_default' => true,
                'is_final' => false,
                'is_readonly' => false,
                'attributes' => ['is_in_progress' => true],
            ],
            [
                'name' => 'تکمیل شده',
                'color' => '#10b981',
                'type' => 'checklist',
                'is_default' => false,
                'is_final' => true,
                'is_readonly' => false,
                'attributes' => ['is_completed' => true],
            ],
            [
                'name' => 'تعویق',
                'color' => '#f97316',
                'type' => 'checklist',
                'is_default' => false,
                'is_final' => false,
                'is_readonly' => false,
                'attributes' => ['is_delayed' => true],
            ],
            [
                'name' => 'لغو شده',
                'color' => '#ef4444',
                'type' => 'checklist',
                'is_default' => false,
                'is_final' => false,
                'is_readonly' => true,
                'attributes' => ['is_canceled' => true],
            ],
        ];

        foreach ($statuses as $index => $status) {
            $status['sort_order'] = $index;
            ProjectStatus::updateOrCreate(
                [
                    'name' => $status['name'],
                    'type' => $status['type'],
                ],
                $status
            );
        }
    }
}
