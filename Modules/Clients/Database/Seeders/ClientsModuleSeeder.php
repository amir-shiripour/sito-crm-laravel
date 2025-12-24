<?php

namespace Modules\Clients\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Modules\Clients\Entities\ClientStatus;
use Modules\Clients\Entities\ClientForm;
use Illuminate\Support\Facades\Schema;

class ClientsModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedStatuses();
        $this->seedDefaultForm();
    }

    protected function seedStatuses(): void
    {
        if (!Schema::hasTable('client_statuses')) {
            return;
        }
        // اگر قبلاً وضعیت‌هایی داری، دوباره نساز
        if (ClientStatus::query()->exists()) {
            return;
        }

        $statuses = [
            [
                'key'           => 'new',
                'label'         => 'جدید',
                'color'         => '#3b82f6',   // آبی
                'is_system'     => true,
                'is_active'     => true,
                'show_in_quick' => true,
                'sort_order'    => 10,
                'allowed_from'  => null,        // از همه وضعیت‌ها قابل انتخاب
            ],
            [
                'key'           => 'active',
                'label'         => 'فعال',
                'color'         => '#22c55e',   // سبز
                'is_system'     => true,
                'is_active'     => true,
                'show_in_quick' => true,
                'sort_order'    => 20,
                'allowed_from'  => ['new', 'pending'],
            ],
            [
                'key'           => 'pending',
                'label'         => 'در انتظار',
                'color'         => '#eab308',   // زرد
                'is_system'     => true,
                'is_active'     => true,
                'show_in_quick' => true,
                'sort_order'    => 30,
                'allowed_from'  => ['new', 'active'],
            ],
            [
                'key'           => 'canceled',
                'label'         => 'لغو شده',
                'color'         => '#ef4444',   // قرمز
                'is_system'     => true,
                'is_active'     => true,
                'show_in_quick' => true,
                'sort_order'    => 40,
                'allowed_from'  => ['active', 'pending'],
            ],
        ];

        foreach ($statuses as $st) {
            ClientStatus::updateOrCreate(
                ['key' => $st['key']],
                Arr::only($st, [
                    'label',
                    'color',
                    'is_system',
                    'is_active',
                    'show_in_quick',
                    'sort_order',
                    'allowed_from',
                ])
            );
        }
    }

    protected function seedDefaultForm(): void
    {
        if (!Schema::hasTable('client_forms')) {
            return;
        }
        // اگر از قبل فرم داری، دوباره نساز
        if (ClientForm::query()->exists()) {
            return;
        }

        // استفاده از systemFieldDefaults() در مدل ClientForm
        $schema = [
            'fields' => array_values(ClientForm::systemFieldDefaults()),
        ];

        ClientForm::create([
            'name'      => 'فرم پیش‌فرض پرونده',
            'key'       => 'default',
            'is_active' => true,
            'schema'    => $schema,
        ]);
    }
}
