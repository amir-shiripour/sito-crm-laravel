<?php

namespace Modules\Services\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Services\App\Http\Models\Status;

class ServicesDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            // ================= Projects =================
            ['name' => 'در انتظار بررسی', 'color' => '#eff264', 'type' => 'project', 'is_final' => 0, 'is_default' => 0, 'is_readonly' => 1, 'allowed_roles' => null, 'allowed_users' => null],

            // ================= Orders =================
            ['name' => 'در انتظار', 'color' => '#f59e0b', 'type' => 'order', 'is_final' => 0, 'is_default' => 1, 'is_readonly' => 0, 'allowed_roles' => null, 'allowed_users' => null],
            ['name' => 'فعال', 'color' => '#10b981', 'type' => 'order', 'is_final' => 1, 'is_default' => 0, 'is_readonly' => 0, 'allowed_roles' => null, 'allowed_users' => null],
            ['name' => 'غیر فعال', 'color' => '#6b7280', 'type' => 'order', 'is_final' => 1, 'is_default' => 0, 'is_readonly' => 0, 'allowed_roles' => null, 'allowed_users' => null],
            ['name' => 'لغو شده', 'color' => '#ef4444', 'type' => 'order', 'is_final' => 1, 'is_default' => 0, 'is_readonly' => 1, 'allowed_roles' => null, 'allowed_users' => null],

            // ================= Services =================
            ['name' => 'فعال', 'color' => '#10b981', 'type' => 'service', 'is_final' => 1, 'is_default' => 1, 'is_readonly' => 0, 'allowed_roles' => null, 'allowed_users' => null],
            ['name' => 'غیر فعال', 'color' => '#6b7280', 'type' => 'service', 'is_final' => 1, 'is_default' => 0, 'is_readonly' => 0, 'allowed_roles' => null, 'allowed_users' => null],

            // ================= Invoices =================
            ['name' => 'فاکتور', 'color' => '#6366f1', 'type' => 'invoice', 'is_final' => 1, 'is_default' => 0, 'is_readonly' => 0, 'allowed_roles' => ['admin'], 'allowed_users' => null],
            ['name' => 'پیش فاکتور', 'color' => '#ff8040', 'type' => 'invoice', 'is_final' => 1, 'is_default' => 0, 'is_readonly' => 0, 'allowed_roles' => ['admin'], 'allowed_users' => null],
            ['name' => 'لغو شده', 'color' => '#ff0000', 'type' => 'invoice', 'is_final' => 1, 'is_default' => 0, 'is_readonly' => 0, 'allowed_roles' => ['admin'], 'allowed_users' => null],

            // ================= Payments =================
            ['name' => 'در انتظار پرداخت', 'color' => '#ff8000', 'type' => 'payment', 'is_final' => 1, 'is_default' => 0, 'is_readonly' => 0, 'allowed_roles' => null, 'allowed_users' => null],
            ['name' => 'پرداخت شده', 'color' => '#00ff40', 'type' => 'payment', 'is_final' => 1, 'is_default' => 0, 'is_readonly' => 0, 'allowed_roles' => null, 'allowed_users' => null],
            ['name' => 'معوقه', 'color' => '#ff8000', 'type' => 'payment', 'is_final' => 1, 'is_default' => 0, 'is_readonly' => 0, 'allowed_roles' => null, 'allowed_users' => ['1']],
            ['name' => 'لغو شده', 'color' => '#ff0000', 'type' => 'payment', 'is_final' => 1, 'is_default' => 0, 'is_readonly' => 0, 'allowed_roles' => null, 'allowed_users' => null],
        ];

        foreach ($statuses as $index => $status) {
            $status['sort_order'] = $index;
            Status::updateOrCreate(
                ['name' => $status['name'], 'type' => $status['type']],
                $status
            );
        }
    }
}
