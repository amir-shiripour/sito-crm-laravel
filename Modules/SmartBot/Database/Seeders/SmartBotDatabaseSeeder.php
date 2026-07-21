<?php

declare(strict_types=1);

namespace Modules\SmartBot\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\SmartBot\App\Models\BotQuestion;
use Modules\SmartBot\App\Models\BotAnswer;
use Modules\SmartBot\App\Models\BotSetting;

class SmartBotDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Initial Settings
        $settings = [
            'name' => 'دستیار هوشمند',
            'welcome_message' => 'سلام! من دستیار هوشمند شما هستم. چطور می‌توانم کمکتان کنم؟',
            'primary_color' => '#6366f1',
            'is_widget_enabled' => '1',
            'match_threshold' => '0.25',
            'fallback_response' => 'متأسفانه پاسخ مناسبی برای این سوال پیدا نکردم. می‌توانید سوال دیگری بپرسید یا با پشتیبانی تماس بگیرید.',
            'max_suggestions' => '5',
        ];

        foreach ($settings as $key => $value) {
            BotSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // Helper to query some products if Market exists
        $productIds = [];
        if (class_exists('Modules\Market\Entities\MasterProduct')) {
            $productIds = \Modules\Market\Entities\MasterProduct::where('status', 'active')
                ->limit(3)
                ->pluck('id')
                ->toArray();
        }

        // 2. Initial Q&As
        $qnas = [
            [
                'question' => 'سلام',
                'keywords' => ['سلام', 'درود', 'صبح بخیر', 'عصر بخیر', 'روز بخیر', 'hi', 'hello'],
                'category' => 'general',
                'priority' => 10,
                'answer' => 'سلام! خوش‌آمدید. چطور می‌توانم کمکتان کنم؟',
                'type' => 'text',
                'products' => null,
            ],
            [
                'question' => 'محصولات پیشنهادی و خرید',
                'keywords' => ['محصول', 'محصولات', 'خرید', 'فروشگاه', 'کالا', 'سفارش'],
                'category' => 'sales',
                'priority' => 8,
                'answer' => 'برخی از محصولات پیشنهادی ما برای شما در زیر آورده شده است. روی دکمه خرید کلیک کنید تا به سبد خریدتان اضافه شود:',
                'type' => !empty($productIds) ? 'product_list' : 'text',
                'products' => !empty($productIds) ? $productIds : null,
            ],
            [
                'question' => 'تماس با پشتیبانی',
                'keywords' => ['پشتیبانی', 'تماس', 'تلفن', 'آدرس', 'ارتباط', 'شماره'],
                'category' => 'general',
                'priority' => 5,
                'answer' => 'برای ارتباط با بخش پشتیبانی می‌توانید با شماره تلفن ۰۲۱-۱۲۳۴۵۶۷۸ تماس بگیرید یا از پنل کاربری تیکت ارسال کنید.',
                'type' => 'text',
                'products' => null,
            ],
        ];

        foreach ($qnas as $qna) {
            $question = BotQuestion::updateOrCreate(
                ['question_text' => $qna['question']],
                [
                    'keywords' => $qna['keywords'],
                    'category' => $qna['category'],
                    'priority' => $qna['priority'],
                    'is_active' => true,
                ]
            );

            BotAnswer::updateOrCreate(
                ['question_id' => $question->id, 'is_default' => true],
                [
                    'answer_text' => $qna['answer'],
                    'answer_type' => $qna['type'],
                    'entity_type' => $qna['type'] === 'product_list' ? 'market_product' : null,
                    'entity_ids' => $qna['products'],
                    'show_add_to_cart' => true,
                ]
            );
        }
    }
}
