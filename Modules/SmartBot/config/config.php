<?php
return [
    'name' => 'SmartBot',
    'welcome_message' => 'سلام! من دستیار هوشمند شما هستم. چطور می‌توانم کمکتان کنم؟',
    'primary_color' => '#6366f1', // Indigo from dmd.md
    'is_widget_enabled' => true,
    'widget_pages' => ['*'], // '*' means all client/public pages
    'match_threshold' => 0.25, // 25% confidence threshold for local Q&A match
    'fallback_response' => 'متأسفانه پاسخ مناسبی برای این سوال پیدا نکردم. می‌توانید سوال دیگری بپرسید یا با پشتیبانی تماس بگیرید.',
    'max_suggestions' => 5,
];
