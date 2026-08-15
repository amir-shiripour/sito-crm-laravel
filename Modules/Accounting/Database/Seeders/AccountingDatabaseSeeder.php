<?php

namespace Modules\Accounting\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\App\Models\FundAccount;

class AccountingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            PermissionsSeeder::class,
            AccountingPermissionsSeeder::class,
            AccountingCategorySeeder::class,
        ]);

        // ساخت سرفصل‌های سیستمی
        $categories = [
            [
                'title' => 'درآمد خدمات نوبت‌دهی',
                'type' => 'income',
                'is_system' => true,
                'status' => true,
            ],
            [
                'title' => 'هزینه کارمزد بانکی',
                'type' => 'expense',
                'is_system' => true,
                'status' => true,
            ],
            [
                'title' => 'موجودی صندوق مطب',
                'type' => 'asset',
                'is_system' => true,
                'status' => true,
            ],
            [
                'title' => 'اسناد دریافتنی / چک‌های موجود',
                'type' => 'asset',
                'is_system' => true,
                'status' => true,
            ],
            [
                'title' => 'موجودی حساب‌های بانکی',
                'type' => 'asset',
                'is_system' => true,
                'status' => true,
            ],
            // New categories for Invoice module
            [
                'title' => 'حساب‌های دریافتنی',
                'type' => 'asset',
                'is_system' => true,
                'status' => true,
            ],
            [
                'title' => 'مالیات فروش پرداختنی',
                'type' => 'liability',
                'is_system' => true,
                'status' => true,
            ],
            [
                'title' => 'تخفیفات فروش',
                'type' => 'income', // Or expense, depending on how discounts are treated
                'is_system' => true,
                'status' => true,
            ],
            // New categories for Cheque module (Matching ChequeService exactly)
            [
                'title' => 'اسناد دریافتنی',
                'type' => 'asset',
                'is_system' => true,
                'status' => true,
            ],
            [
                'title' => 'اسناد پرداختنی',
                'type' => 'liability',
                'is_system' => true,
                'status' => true,
            ],
            [
                'title' => 'اسناد در جریان وصول',
                'type' => 'asset',
                'is_system' => true,
                'status' => true,
            ],
            [
                'title' => 'حساب‌های پرداختنی',
                'type' => 'liability',
                'is_system' => true,
                'status' => true,
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['title' => $categoryData['title']],
                $categoryData
            );
        }

        // ساخت حساب‌های خزانه‌داری
        $fundAccounts = [
            [
                'name' => 'درگاه اینترنتی زرین‌پال',
                'type' => 'gateway',
                'status' => true,
            ],
            [
                'name' => 'صندوق نقدی کلینیک',
                'type' => 'cash',
                'status' => true,
            ]
        ];

        foreach ($fundAccounts as $accountData) {
            FundAccount::firstOrCreate(
                ['name' => $accountData['name']],
                $accountData
            );
        }
    }
}
