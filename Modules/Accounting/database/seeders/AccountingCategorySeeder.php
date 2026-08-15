<?php

namespace Modules\Accounting\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\App\Models\AccountingSetting;

class AccountingCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Assets (دارایی‌ها)
        $asset = Category::firstOrCreate(
            ['title' => 'دارایی‌ها', 'parent_id' => null],
            ['type' => 'asset', 'is_system' => true, 'status' => true, 'level' => 1, 'account_code' => '1000']
        );
        
        $currentAsset = Category::firstOrCreate(
            ['title' => 'دارایی‌های جاری', 'parent_id' => $asset->id],
            ['type' => 'asset', 'is_system' => true, 'status' => true, 'level' => 2, 'account_code' => '1100']
        );
        
        $cashBank = Category::firstOrCreate(
            ['title' => 'موجودی نقد و بانک', 'parent_id' => $currentAsset->id],
            ['type' => 'asset', 'is_system' => true, 'status' => true, 'level' => 3, 'account_code' => '1101']
        );
        
        $cash = Category::firstOrCreate(
            ['title' => 'صندوق', 'parent_id' => $cashBank->id],
            ['type' => 'asset', 'is_system' => true, 'status' => true, 'level' => 4, 'is_treasury_related' => true, 'account_code' => '110101']
        );
        $bank = Category::firstOrCreate(
            ['title' => 'موجودی حساب‌های بانکی', 'parent_id' => $cashBank->id],
            ['type' => 'asset', 'is_system' => true, 'status' => true, 'level' => 4, 'is_treasury_related' => true, 'account_code' => '110102']
        );
        
        $chequesReceivable = Category::firstOrCreate(
            ['title' => 'اسناد دریافتنی', 'parent_id' => $currentAsset->id],
            ['type' => 'asset', 'is_system' => true, 'status' => true, 'level' => 3, 'account_code' => '1102']
        );
        
        $chequesInTransit = Category::firstOrCreate(
            ['title' => 'اسناد در جریان وصول', 'parent_id' => $currentAsset->id],
            ['type' => 'asset', 'is_system' => true, 'status' => true, 'level' => 3, 'account_code' => '1103']
        );
        
        // عنوان دقیق برای داشبورد
        $receivables = Category::firstOrCreate(
            ['title' => 'حساب‌های دریافتنی', 'parent_id' => $currentAsset->id],
            ['type' => 'asset', 'is_system' => true, 'status' => true, 'level' => 3, 'account_code' => '1104']
        );

        // 2. Liabilities (بدهی‌ها)
        $liability = Category::firstOrCreate(
            ['title' => 'بدهی‌ها', 'parent_id' => null],
            ['type' => 'liability', 'is_system' => true, 'status' => true, 'level' => 1, 'account_code' => '2000']
        );
        $currentLiability = Category::firstOrCreate(
            ['title' => 'بدهی‌های جاری', 'parent_id' => $liability->id],
            ['type' => 'liability', 'is_system' => true, 'status' => true, 'level' => 2, 'account_code' => '2100']
        );
        
        $chequesPayable = Category::firstOrCreate(
            ['title' => 'اسناد پرداختنی', 'parent_id' => $currentLiability->id],
            ['type' => 'liability', 'is_system' => true, 'status' => true, 'level' => 3, 'account_code' => '2101']
        );
        
        // عنوان دقیق برای داشبورد
        $payables = Category::firstOrCreate(
            ['title' => 'حساب‌های پرداختنی', 'parent_id' => $currentLiability->id],
            ['type' => 'liability', 'is_system' => true, 'status' => true, 'level' => 3, 'account_code' => '2102']
        );
        
        $salesTax = Category::firstOrCreate(
            ['title' => 'مالیات بر ارزش افزوده', 'parent_id' => $currentLiability->id],
            ['type' => 'liability', 'is_system' => true, 'status' => true, 'level' => 3, 'account_code' => '2103']
        );

        // 3. Equity (سرمایه)
        $equity = Category::firstOrCreate(
            ['title' => 'حقوق صاحبان سهام', 'parent_id' => null],
            ['type' => 'equity', 'is_system' => true, 'status' => true, 'level' => 1, 'account_code' => '3000']
        );
        $capital = Category::firstOrCreate(
            ['title' => 'سرمایه', 'parent_id' => $equity->id],
            ['type' => 'equity', 'is_system' => true, 'status' => true, 'level' => 2, 'account_code' => '3100']
        );

        // 4. Income (درآمد)
        $income = Category::firstOrCreate(
            ['title' => 'درآمدها', 'parent_id' => null],
            ['type' => 'income', 'is_system' => true, 'status' => true, 'level' => 1, 'account_code' => '4000']
        );
        $operatingIncome = Category::firstOrCreate(
            ['title' => 'درآمدهای عملیاتی', 'parent_id' => $income->id],
            ['type' => 'income', 'is_system' => true, 'status' => true, 'level' => 2, 'account_code' => '4100']
        );
        $salesIncome = Category::firstOrCreate(
            ['title' => 'درآمد فروش', 'parent_id' => $operatingIncome->id],
            ['type' => 'income', 'is_system' => true, 'status' => true, 'level' => 3, 'account_code' => '4101']
        );
        $serviceIncome = Category::firstOrCreate(
            ['title' => 'درآمد خدمات', 'parent_id' => $operatingIncome->id],
            ['type' => 'income', 'is_system' => true, 'status' => true, 'level' => 3, 'account_code' => '4102']
        );

        // 5. Expense (هزینه)
        $expense = Category::firstOrCreate(
            ['title' => 'هزینه‌ها', 'parent_id' => null],
            ['type' => 'expense', 'is_system' => true, 'status' => true, 'level' => 1, 'account_code' => '5000']
        );
        $salesDiscount = Category::firstOrCreate(
            ['title' => 'تخفیفات نقدی فروش', 'parent_id' => $expense->id],
            ['type' => 'expense', 'is_system' => true, 'status' => true, 'level' => 2, 'account_code' => '5101']
        );
        
        $generalExpense = Category::firstOrCreate(
            ['title' => 'هزینه‌های عمومی و اداری', 'parent_id' => $expense->id],
            ['type' => 'expense', 'is_system' => true, 'status' => true, 'level' => 2, 'account_code' => '5200']
        );
        $salaryExpense = Category::firstOrCreate(
            ['title' => 'هزینه حقوق و دستمزد', 'parent_id' => $generalExpense->id],
            ['type' => 'expense', 'is_system' => true, 'status' => true, 'level' => 3, 'account_code' => '5201']
        );
        $rentExpense = Category::firstOrCreate(
            ['title' => 'هزینه اجاره', 'parent_id' => $generalExpense->id],
            ['type' => 'expense', 'is_system' => true, 'status' => true, 'level' => 3, 'account_code' => '5202']
        );

        // تنظیم سرفصل‌های پیش‌فرض در تنظیمات سیستم حسابداری
        $defaults = [
            'sales_income_category_id' => $salesIncome->id,
            'receivables_category_id' => $receivables->id,
            'sales_tax_category_id' => $salesTax->id,
            'sales_discount_category_id' => $salesDiscount->id,
            'payables_category_id' => $payables->id,
            'cheques_receivable_category_id' => $chequesReceivable->id,
            'cheques_payable_category_id' => $chequesPayable->id,
            'cheques_in_transit_category_id' => $chequesInTransit->id,
        ];

        // در صورتی که کلاس Setting وجود داشته باشد مقادیر را ذخیره می‌کنیم
        if (class_exists(AccountingSetting::class)) {
            AccountingSetting::setValue('defaults', $defaults);

            $general = AccountingSetting::get('general', []);
            if (!isset($general['check_cheque_due_dates'])) {
                $general['check_cheque_due_dates'] = true;
                AccountingSetting::setValue('general', $general);
            }
        }
    }
}
