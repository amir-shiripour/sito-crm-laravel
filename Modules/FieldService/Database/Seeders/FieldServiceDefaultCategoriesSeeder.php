<?php

namespace Modules\FieldService\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FieldService\App\Enums\ApplianceType;
use Modules\FieldService\App\Models\Category;

class FieldServiceDefaultCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'           => 'پکیج و رادیاتور',
                'slug'           => 'package-radiator',
                'icon'           => '🔥',
                'appliance_type' => ApplianceType::PACKAGE,
                'base_labor_fee' => 3500000, // 350,000 Tomans
                'warranty_days'  => 90,
                'sort_order'     => 1,
                'description'    => 'عیب‌یابی، رسوب‌زدایی، سرویس سالیانه و تعمیر برد پکیج‌های دیواری و زمینی',
            ],
            [
                'name'           => 'کولر گازی و اسپلیت',
                'slug'           => 'air-conditioner-split',
                'icon'           => '❄️',
                'appliance_type' => ApplianceType::AIR_CONDITIONER,
                'base_labor_fee' => 4500000,
                'warranty_days'  => 90,
                'sort_order'     => 2,
                'description'    => 'نصب استاندارد، شارژ گاز R410 و R22، شستشوی پنل و کندانسور و نشت‌یابی',
            ],
            [
                'name'           => 'یخچال و فریزر',
                'slug'           => 'refrigerator-freezer',
                'icon'           => '🧊',
                'appliance_type' => ApplianceType::REFRIGERATOR,
                'base_labor_fee' => 4000000,
                'warranty_days'  => 90,
                'sort_order'     => 3,
                'description'    => 'تعویض موتور اینورتر، رفع یخ‌زدگی نوفراست، شارژ گاز و تعویض لاستیک دور درب',
            ],
            [
                'name'           => 'ماشین لباسشویی و ظرفشویی',
                'slug'           => 'washing-machine-dishwasher',
                'icon'           => '🫧',
                'appliance_type' => ApplianceType::WASHING_MACHINE,
                'base_labor_fee' => 3000000,
                'warranty_days'  => 90,
                'sort_order'     => 4,
                'description'    => 'تعویض بلبرینگ، کاسه نمد، پمپ تخلیه، رفع لرزش و کدهای خطای دیجیتال',
            ],
            [
                'name'           => 'موتورخانه مرکزی و مشعل',
                'slug'           => 'boiler-room-facilities',
                'icon'           => '🛢️',
                'appliance_type' => ApplianceType::BOILER,
                'base_labor_fee' => 8000000,
                'warranty_days'  => 180,
                'sort_order'     => 5,
                'description'    => 'تنظیم مشعل گازی، دیگ چدنی، کلکتورهای حرارتی و نگهداری دوره‌ای مجتمع‌ها',
            ],
            [
                'name'           => 'چیلر و تهویه صنعتی',
                'slug'           => 'chiller-industrial-hvac',
                'icon'           => '🏭',
                'appliance_type' => ApplianceType::CHILLER,
                'base_labor_fee' => 12000000,
                'warranty_days'  => 180,
                'sort_order'     => 6,
                'description'    => 'سرویس و راه‌اندازی چیلرهای جذبی و تراکمی، فن‌کویل‌ها و برج‌های خنک‌کننده',
            ],
            [
                'name'           => 'بوستر پمپ و آبرسانی',
                'slug'           => 'booster-pump-water',
                'icon'           => '💧',
                'appliance_type' => ApplianceType::BOOSTER_PUMP,
                'base_labor_fee' => 3500000,
                'warranty_days'  => 90,
                'sort_order'     => 7,
                'description'    => 'تعویض مخزن تحت فشار، تنظیم کلید اتوماتیک پمپ، پروانه و فلوتر',
            ],
            [
                'name'           => 'آسانسور و بالابر',
                'slug'           => 'elevator-lift',
                'icon'           => '🛗',
                'appliance_type' => ApplianceType::ELEVATOR,
                'base_labor_fee' => 6000000,
                'warranty_days'  => 30,
                'sort_order'     => 8,
                'description'    => 'بیمه، بازرسی استاندارد، تعویض سیم بکسل، روغن‌کاری گیربکس و عیب‌یابی تابلو',
            ],
            [
                'name'           => 'برق و روشنایی ساختمان',
                'slug'           => 'electrical-wiring',
                'icon'           => '⚡',
                'appliance_type' => ApplianceType::ELECTRICAL,
                'base_labor_fee' => 2500000,
                'warranty_days'  => 60,
                'sort_order'     => 9,
                'description'    => 'رفع اتصالی، سیم‌کشی توکار، تابلو فیوز مینیاتوری و سیستم‌های اعلام حریق',
            ],
            [
                'name'           => 'تاسیسات لوله‌کشی و فاضلاب',
                'slug'           => 'plumbing-pipes',
                'icon'           => '🔧',
                'appliance_type' => ApplianceType::PLUMBING,
                'base_labor_fee' => 3000000,
                'warranty_days'  => 90,
                'sort_order'     => 10,
                'description'    => 'تشخیص ترکیدگی لوله با دستگاه التراسونیک، لوله‌کشی پنج‌لایه و نصب شیرآلات',
            ],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
