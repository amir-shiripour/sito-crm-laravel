<?php

namespace Modules\FieldService\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\FieldService\App\Models\Category;

class FieldServiceDefaultCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'           => 'Ù¾Ú©ÛŒØ¬ Ùˆ Ø±Ø§Ø¯ÛŒØ§ØªÙˆØ±',
                'slug'           => 'package-radiator',
                'icon'           => 'ðŸ”¥',
                'base_labor_fee' => 3500000, // 350,000 Tomans
                'warranty_days'  => 90,
                'sort_order'     => 1,
                'description'    => 'Ø¹ÛŒØ¨â€ŒÛŒØ§Ø¨ÛŒØŒ Ø±Ø³ÙˆØ¨â€ŒØ²Ø¯Ø§ÛŒÛŒØŒ Ø³Ø±ÙˆÛŒØ³ Ø³Ø§Ù„ÛŒØ§Ù†Ù‡ Ùˆ ØªØ¹Ù…ÛŒØ± Ø¨Ø±Ø¯ Ù¾Ú©ÛŒØ¬â€ŒÙ‡Ø§ÛŒ Ø¯ÛŒÙˆØ§Ø±ÛŒ Ùˆ Ø²Ù…ÛŒÙ†ÛŒ',
            ],
            [
                'name'           => 'Ú©ÙˆÙ„Ø± Ú¯Ø§Ø²ÛŒ Ùˆ Ø§Ø³Ù¾Ù„ÛŒØª',
                'slug'           => 'air-conditioner-split',
                'icon'           => 'â„ï¸',
                'base_labor_fee' => 4500000,
                'warranty_days'  => 90,
                'sort_order'     => 2,
                'description'    => 'Ù†ØµØ¨ Ø§Ø³ØªØ§Ù†Ø¯Ø§Ø±Ø¯ØŒ Ø´Ø§Ø±Ú˜ Ú¯Ø§Ø² R410 Ùˆ R22ØŒ Ø´Ø³ØªØ´ÙˆÛŒ Ù¾Ù†Ù„ Ùˆ Ú©Ù†Ø¯Ø§Ù†Ø³ÙˆØ± Ùˆ Ù†Ø´Øªâ€ŒÛŒØ§Ø¨ÛŒ',
            ],
            [
                'name'           => 'ÛŒØ®Ú†Ø§Ù„ Ùˆ ÙØ±ÛŒØ²Ø±',
                'slug'           => 'refrigerator-freezer',
                'icon'           => 'ðŸ§Š',
                'base_labor_fee' => 4000000,
                'warranty_days'  => 90,
                'sort_order'     => 3,
                'description'    => 'ØªØ¹ÙˆÛŒØ¶ Ù…ÙˆØªÙˆØ± Ø§ÛŒÙ†ÙˆØ±ØªØ±ØŒ Ø±ÙØ¹ ÛŒØ®â€ŒØ²Ø¯Ú¯ÛŒ Ù†ÙˆÙØ±Ø§Ø³ØªØŒ Ø´Ø§Ø±Ú˜ Ú¯Ø§Ø² Ùˆ ØªØ¹ÙˆÛŒØ¶ Ù„Ø§Ø³ØªÛŒÚ© Ø¯ÙˆØ± Ø¯Ø±Ø¨',
            ],
            [
                'name'           => 'Ù…Ø§Ø´ÛŒÙ† Ù„Ø¨Ø§Ø³Ø´ÙˆÛŒÛŒ Ùˆ Ø¸Ø±ÙØ´ÙˆÛŒÛŒ',
                'slug'           => 'washing-machine-dishwasher',
                'icon'           => 'ðŸ«§',
                'base_labor_fee' => 3000000,
                'warranty_days'  => 90,
                'sort_order'     => 4,
                'description'    => 'ØªØ¹ÙˆÛŒØ¶ Ø¨Ù„Ø¨Ø±ÛŒÙ†Ú¯ØŒ Ú©Ø§Ø³Ù‡ Ù†Ù…Ø¯ØŒ Ù¾Ù…Ù¾ ØªØ®Ù„ÛŒÙ‡ØŒ Ø±ÙØ¹ Ù„Ø±Ø²Ø´ Ùˆ Ú©Ø¯Ù‡Ø§ÛŒ Ø®Ø·Ø§ÛŒ Ø¯ÛŒØ¬ÛŒØªØ§Ù„',
            ],
            [
                'name'           => 'Ù…ÙˆØªÙˆØ±Ø®Ø§Ù†Ù‡ Ù…Ø±Ú©Ø²ÛŒ Ùˆ Ù…Ø´Ø¹Ù„',
                'slug'           => 'boiler-room-facilities',
                'icon'           => 'ðŸ›¢ï¸',
                'base_labor_fee' => 8000000,
                'warranty_days'  => 180,
                'sort_order'     => 5,
                'description'    => 'ØªÙ†Ø¸ÛŒÙ… Ù…Ø´Ø¹Ù„ Ú¯Ø§Ø²ÛŒØŒ Ø¯ÛŒÚ¯ Ú†Ø¯Ù†ÛŒØŒ Ú©Ù„Ú©ØªÙˆØ±Ù‡Ø§ÛŒ Ø­Ø±Ø§Ø±ØªÛŒ Ùˆ Ù†Ú¯Ù‡Ø¯Ø§Ø±ÛŒ Ø¯ÙˆØ±Ù‡â€ŒØ§ÛŒ Ù…Ø¬ØªÙ…Ø¹â€ŒÙ‡Ø§',
            ],
            [
                'name'           => 'Ú†ÛŒÙ„Ø± Ùˆ ØªÙ‡ÙˆÛŒÙ‡ ØµÙ†Ø¹ØªÛŒ',
                'slug'           => 'chiller-industrial-hvac',
                'icon'           => 'ðŸ­',
                'base_labor_fee' => 12000000,
                'warranty_days'  => 180,
                'sort_order'     => 6,
                'description'    => 'Ø³Ø±ÙˆÛŒØ³ Ùˆ Ø±Ø§Ù‡â€ŒØ§Ù†Ø¯Ø§Ø²ÛŒ Ú†ÛŒÙ„Ø±Ù‡Ø§ÛŒ Ø¬Ø°Ø¨ÛŒ Ùˆ ØªØ±Ø§Ú©Ù…ÛŒØŒ ÙÙ†â€ŒÚ©ÙˆÛŒÙ„â€ŒÙ‡Ø§ Ùˆ Ø¨Ø±Ø¬â€ŒÙ‡Ø§ÛŒ Ø®Ù†Ú©â€ŒÚ©Ù†Ù†Ø¯Ù‡',
            ],
            [
                'name'           => 'Ø¨ÙˆØ³ØªØ± Ù¾Ù…Ù¾ Ùˆ Ø¢Ø¨Ø±Ø³Ø§Ù†ÛŒ',
                'slug'           => 'booster-pump-water',
                'icon'           => 'ðŸ’§',
                'base_labor_fee' => 3500000,
                'warranty_days'  => 90,
                'sort_order'     => 7,
                'description'    => 'ØªØ¹ÙˆÛŒØ¶ Ù…Ø®Ø²Ù† ØªØ­Øª ÙØ´Ø§Ø±ØŒ ØªÙ†Ø¸ÛŒÙ… Ú©Ù„ÛŒØ¯ Ø§ØªÙˆÙ…Ø§ØªÛŒÚ© Ù¾Ù…Ù¾ØŒ Ù¾Ø±ÙˆØ§Ù†Ù‡ Ùˆ ÙÙ„ÙˆØªØ±',
            ],
            [
                'name'           => 'Ø¢Ø³Ø§Ù†Ø³ÙˆØ± Ùˆ Ø¨Ø§Ù„Ø§Ø¨Ø±',
                'slug'           => 'elevator-lift',
                'icon'           => 'ðŸ›—',
                'base_labor_fee' => 6000000,
                'warranty_days'  => 30,
                'sort_order'     => 8,
                'description'    => 'Ø¨ÛŒÙ…Ù‡ØŒ Ø¨Ø§Ø²Ø±Ø³ÛŒ Ø§Ø³ØªØ§Ù†Ø¯Ø§Ø±Ø¯ØŒ ØªØ¹ÙˆÛŒØ¶ Ø³ÛŒÙ… Ø¨Ú©Ø³Ù„ØŒ Ø±ÙˆØºÙ†â€ŒÚ©Ø§Ø±ÛŒ Ú¯ÛŒØ±Ø¨Ú©Ø³ Ùˆ Ø¹ÛŒØ¨â€ŒÛŒØ§Ø¨ÛŒ ØªØ§Ø¨Ù„Ùˆ',
            ],
            [
                'name'           => 'Ø¨Ø±Ù‚ Ùˆ Ø±ÙˆØ´Ù†Ø§ÛŒÛŒ Ø³Ø§Ø®ØªÙ…Ø§Ù†',
                'slug'           => 'electrical-wiring',
                'icon'           => 'âš¡',
                'base_labor_fee' => 2500000,
                'warranty_days'  => 60,
                'sort_order'     => 9,
                'description'    => 'Ø±ÙØ¹ Ø§ØªØµØ§Ù„ÛŒØŒ Ø³ÛŒÙ…â€ŒÚ©Ø´ÛŒ ØªÙˆÚ©Ø§Ø±ØŒ ØªØ§Ø¨Ù„Ùˆ ÙÛŒÙˆØ² Ù…ÛŒÙ†ÛŒØ§ØªÙˆØ±ÛŒ Ùˆ Ø³ÛŒØ³ØªÙ…â€ŒÙ‡Ø§ÛŒ Ø§Ø¹Ù„Ø§Ù… Ø­Ø±ÛŒÙ‚',
            ],
            [
                'name'           => 'ØªØ§Ø³ÛŒØ³Ø§Øª Ù„ÙˆÙ„Ù‡â€ŒÚ©Ø´ÛŒ Ùˆ ÙØ§Ø¶Ù„Ø§Ø¨',
                'slug'           => 'plumbing-pipes',
                'icon'           => 'ðŸ”§',
                'base_labor_fee' => 3000000,
                'warranty_days'  => 90,
                'sort_order'     => 10,
                'description'    => 'ØªØ´Ø®ÛŒØµ ØªØ±Ú©ÛŒØ¯Ú¯ÛŒ Ù„ÙˆÙ„Ù‡ Ø¨Ø§ Ø¯Ø³ØªÚ¯Ø§Ù‡ Ø§Ù„ØªØ±Ø§Ø³ÙˆÙ†ÛŒÚ©ØŒ Ù„ÙˆÙ„Ù‡â€ŒÚ©Ø´ÛŒ Ù¾Ù†Ø¬â€ŒÙ„Ø§ÛŒÙ‡ Ùˆ Ù†ØµØ¨ Ø´ÛŒØ±Ø¢Ù„Ø§Øª',
            ],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
