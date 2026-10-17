<?php

namespace Modules\FieldService\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Clients\Entities\Client;
use Modules\FieldService\App\Enums\ApplianceType;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Enums\PaymentStatus;
use Modules\FieldService\App\Enums\TechnicianStatus;
use Modules\FieldService\App\Enums\TechnicianTier;
use Modules\FieldService\App\Models\Building;
use Modules\FieldService\App\Models\Category;
use Modules\FieldService\App\Models\FieldServiceSetting;
use Modules\FieldService\App\Models\ServiceInvoice;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Models\ServiceOrderItem;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Models\VendorPartner;

class FieldServiceDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(FieldServiceDefaultCategoriesSeeder::class);

        // Seed default settings
        FieldServiceSetting::set('map_provider', 'osm', 'map');
        FieldServiceSetting::set('map_default_lat', '36.5444', 'map');
        FieldServiceSetting::set('map_default_lng', '52.6780', 'map');
        FieldServiceSetting::set('map_default_zoom', '13', 'map');
        FieldServiceSetting::set('default_commission_percent', '15', 'financial');
        FieldServiceSetting::set('base_travel_fee', '600000', 'financial');
        FieldServiceSetting::set('dispatch_timeout_seconds', '45', 'dispatch');
        FieldServiceSetting::set('radius_expansion_km', [3, 7, 15, 30, 60], 'dispatch');

        // Sample Technicians in Mazandaran (Babol, Sari, Qaemshahr, Amol)
        $techs = [
            [
                'first_name'             => 'محمد',
                'last_name'              => 'قاسمی',
                'mobile'                 => '09111112233',
                'national_code'          => '2050112233',
                'shaba_number'           => 'IR120170000000112233445566',
                'city'                   => 'بابل',
                'address'                => 'بابل، میدان باغ فردوس، کوچه شهید بهشتی',
                'lat'                    => 36.5512,
                'lng'                    => 52.6841,
                'is_online'              => true,
                'is_busy'                => false,
                'status'                 => TechnicianStatus::ACTIVE,
                'tier'                   => TechnicianTier::MASTER,
                'rating_avg'             => 4.95,
                'rating_count'           => 142,
                'completed_orders_count' => 138,
            ],
            [
                'first_name'             => 'علی',
                'last_name'              => 'نجفی',
                'mobile'                 => '09112223344',
                'national_code'          => '2050223344',
                'shaba_number'           => 'IR120170000000223344556677',
                'city'                   => 'بابل',
                'address'                => 'بابل، چهارراه فرهنگ، نبش کوچه یاسمن',
                'lat'                    => 36.5398,
                'lng'                    => 52.6710,
                'is_online'              => true,
                'is_busy'                => false,
                'status'                 => TechnicianStatus::ACTIVE,
                'tier'                   => TechnicianTier::GOLD,
                'rating_avg'             => 4.88,
                'rating_count'           => 89,
                'completed_orders_count' => 84,
            ],
            [
                'first_name'             => 'رضا',
                'last_name'              => 'حسینی',
                'mobile'                 => '09113334455',
                'national_code'          => '2050334455',
                'shaba_number'           => 'IR120170000000334455667788',
                'city'                   => 'قائمشهر',
                'address'                => 'قائمشهر، خیابان بابل، کوچه آزادی',
                'lat'                    => 36.4650,
                'lng'                    => 52.8590,
                'is_online'              => true,
                'is_busy'                => false,
                'status'                 => TechnicianStatus::ACTIVE,
                'tier'                   => TechnicianTier::SILVER,
                'rating_avg'             => 4.70,
                'rating_count'           => 34,
                'completed_orders_count' => 31,
            ],
            [
                'first_name'             => 'سعید',
                'last_name'              => 'مرادی',
                'mobile'                 => '09114445566',
                'national_code'          => '2050445566',
                'shaba_number'           => 'IR120170000000445566778899',
                'city'                   => 'ساری',
                'address'                => 'ساری، بلوار طالقانی، کوچه بهار',
                'lat'                    => 36.5659,
                'lng'                    => 53.0586,
                'is_online'              => true,
                'is_busy'                => false,
                'status'                 => TechnicianStatus::ACTIVE,
                'tier'                   => TechnicianTier::GOLD,
                'rating_avg'             => 4.90,
                'rating_count'           => 76,
                'completed_orders_count' => 70,
            ],
            [
                'first_name'             => 'امیر',
                'last_name'              => 'صالحی',
                'mobile'                 => '09115556677',
                'national_code'          => '2050556677',
                'shaba_number'           => 'IR120170000000556677889900',
                'city'                   => 'آمل',
                'address'                => 'آمل، میدان هفده شهریور، خیابان نور',
                'lat'                    => 36.4690,
                'lng'                    => 52.3580,
                'is_online'              => true,
                'is_busy'                => false,
                'status'                 => TechnicianStatus::ACTIVE,
                'tier'                   => TechnicianTier::MASTER,
                'rating_avg'             => 4.97,
                'rating_count'           => 110,
                'completed_orders_count' => 108,
            ],
        ];

        $packageCat = Category::where('slug', 'package-radiator')->first();
        $acCat = Category::where('slug', 'air-conditioner-split')->first();

        foreach ($techs as $tData) {
            $tech = Technician::updateOrCreate(['mobile' => $tData['mobile']], $tData);
            if ($packageCat) {
                $tech->categories()->syncWithoutDetaching([$packageCat->id => ['is_verified' => true, 'experience_years' => 5]]);
            }
            if ($acCat) {
                $tech->categories()->syncWithoutDetaching([$acCat->id => ['is_verified' => true, 'experience_years' => 4]]);
            }
        }

        // Ensure at least one Client exists in CRM Clients module for sample orders
        $client = Client::first();
        if (!$client) {
            $client = Client::create([
                'full_name'     => 'مهندس احمد رضایی',
                'phone'         => '09111199887',
                'national_code' => '2059998877',
                'notes'         => 'مشتری تست سامانه نصاب‌باشی',
            ]);
        }

        // Sample B2B Building
        $building = Building::updateOrCreate(
            ['name' => 'برج مسکونی یاسمن بابل'],
            [
                'client_id'     => $client->id,
                'building_type' => 'complex',
                'units_count'   => 24,
                'floors_count'  => 8,
                'manager_name'  => $client->full_name,
                'manager_phone' => $client->phone,
                'city'          => 'بابل',
                'address'       => 'بابل، خیابان شریعتی، مجتمع مسکونی یاسمن',
                'lat'           => 36.5480,
                'lng'           => 52.6805,
            ]
        );

        // Sample B2B Vendor
        VendorPartner::updateOrCreate(
            ['mobile' => '09117778899'],
            [
                'store_name'                 => 'فروشگاه تاسیسات و پکیج البرز',
                'owner_name'                 => 'حاج محمود البرزی',
                'city'                       => 'بابل',
                'address'                    => 'بابل، خیابان مدرس، روبروی بانک ملی',
                'referral_commission_percent' => 5.00,
                'status'                     => 'active',
            ]
        );

        // Sample Active Order
        $sampleTech = Technician::where('mobile', '09111112233')->first();
        $order = ServiceOrder::firstOrCreate(
            ['order_number' => 'NB-1405-00101'],
            [
                'client_id'             => $client->id,
                'technician_id'         => $sampleTech?->id,
                'category_id'           => $packageCat?->id,
                'building_id'           => $building->id,
                'appliance_type'        => ApplianceType::PACKAGE,
                'appliance_brand_model' => 'پکیج ۲۴۰۰۰ بوتان Parma',
                'problem_description'   => 'افت شدید فشار آب گرم و خاموش شدن مشعل با کد خطای E01',
                'status'                => OrderStatus::IN_PROGRESS,
                'priority'              => 'normal',
                'province'              => 'مازندران',
                'city'                  => 'بابل',
                'district'              => 'شریعتی',
                'address'               => 'بابل، خیابان شریعتی، کوچه معلم ۶، پلاک ۱۲',
                'lat'                   => 36.5450,
                'lng'                   => 52.6790,
                'customer_name'         => $client->full_name,
                'customer_phone'        => $client->phone,
                'search_radius_km'      => 3,
                'dispatched_at'         => now()->subMinutes(40),
                'accepted_at'           => now()->subMinutes(38),
                'en_route_at'           => now()->subMinutes(35),
                'arrived_at'            => now()->subMinutes(20),
                'started_at'            => now()->subMinutes(18),
            ]
        );

        // Sample Invoice for this order
        if ($order && !$order->invoice) {
            $invoice = ServiceInvoice::create([
                'order_id'                    => $order->id,
                'invoice_number'              => 'INV-NB-1405-00101',
                'labor_amount'                => 1500000,
                'parts_amount'                => 850000,
                'travel_fee'                  => 600000,
                'discount_amount'             => 0,
                'total_amount'                => 2950000,
                'platform_commission_percent' => 15.00,
                'platform_commission_amount'  => 225000,
                'technician_net_amount'       => 2725000,
                'payment_method'              => 'online',
                'payment_status'              => PaymentStatus::ESCROW_HELD,
                'paid_at'                     => now()->subMinutes(15),
            ]);

            ServiceOrderItem::create([
                'invoice_id'         => $invoice->id,
                'item_type'          => 'labor',
                'title'              => 'رسوب‌زدایی مبدل ثانویه و تست پرشر سوئیچ دود',
                'unit_price'         => 1500000,
                'quantity'           => 1,
                'total_price'        => 1500000,
                'warranty_months'    => 3,
            ]);

            ServiceOrderItem::create([
                'invoice_id'         => $invoice->id,
                'item_type'          => 'part',
                'title'              => 'ان‌تی‌سی (NTC) آب گرم مصرفی شرکتی بوتان',
                'unit_price'         => 850000,
                'quantity'           => 1,
                'total_price'        => 850000,
                'warranty_months'    => 6,
                'part_serial_number' => 'BTN-NTC-9941',
            ]);
        }
    }
}
