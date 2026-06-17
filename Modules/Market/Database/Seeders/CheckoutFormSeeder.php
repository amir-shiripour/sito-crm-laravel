<?php

namespace Modules\Market\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Market\App\Models\CheckoutForm;

class CheckoutFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the existing form or create a new one
        $form = CheckoutForm::firstOrNew(['key' => 'default_checkout']);

        // Update or fill the attributes
        $form->fill([
            'name' => 'فرم تسویه حساب پیش‌فرض',
            'is_active' => true,
            'product_id' => null,
            'category_id' => null,
            'schema' => [
                'groups' => [
                    ['id' => 'group_info', 'name' => 'اطلاعات گیرنده'],
                    ['id' => 'group_address', 'name' => 'آدرس'],
                    ['id' => 'group_notes', 'name' => 'جزئیات سفارش'],
                ],
                'fields' => [
                    [
                        'id' => 'full_name',
                        'type' => 'text',
                        'label' => 'نام و نام خانوادگی گیرنده',
                        'placeholder' => 'مثلاً: علی محمدی',
                        'width' => '1/2',
                        'required' => true,
                        'is_system' => true,
                        'group' => 'group_info',
                        'source' => 'client.full_name',
                    ],
                    [
                        'id' => 'phone',
                        'type' => 'text',
                        'label' => 'شماره تماس گیرنده',
                        'placeholder' => '0912...',
                        'width' => '1/2',
                        'required' => true,
                        'is_system' => true,
                        'group' => 'group_info',
                        'source' => 'client.phone',
                    ],
                    [
                        'id' => 'province_city',
                        'type' => 'select-province-city',
                        'label' => 'استان و شهر',
                        'width' => 'full',
                        'required' => true,
                        'group' => 'group_address',
                    ],
                    [
                        'id' => 'address',
                        'type' => 'textarea',
                        'label' => 'آدرس دقیق پستی',
                        'placeholder' => 'خیابان، کوچه، پلاک، واحد',
                        'width' => 'full',
                        'required' => true,
                        'group' => 'group_address',
                    ],
                    [
                        'id' => 'customer_notes',
                        'type' => 'textarea',
                        'label' => 'یادداشت شما برای سفارش',
                        'placeholder' => 'توضیحات اضافی که لازم است فروشنده بداند...',
                        'width' => 'full',
                        'required' => false,
                        'group' => 'group_notes',
                    ],
                ]
            ]
        ]);

        $form->save();

        // Sync default checkout form key with MarketSetting
        \Modules\Market\Entities\MarketSetting::setValue('checkout.default_form_key', 'default_checkout');
    }
}
