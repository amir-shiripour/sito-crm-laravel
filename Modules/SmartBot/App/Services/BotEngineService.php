<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Services;

use Modules\SmartBot\App\Models\BotSession;
use Modules\SmartBot\App\Models\BotMessage;
use Modules\SmartBot\App\Models\BotQuestion;
use Modules\SmartBot\App\Models\BotSetting;
use Modules\SmartBot\App\Services\Adapters\AiAdapterInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

final class BotEngineService
{
    public function __construct(
        private readonly AiAdapterInterface $adapter,
        private readonly EntityResolverService $resolver
    ) {}

    /**
     * Get or create a session.
     */
    public function getOrCreateSession(string $uuid, ?string $pageUrl = null, array $metadata = []): BotSession
    {
        $session = BotSession::where('session_uuid', $uuid)->first();

        if (!$session) {
            $visitorType = 'guest';
            $visitorId = null;

            if (Auth::guard('client')->check()) {
                $visitorType = 'client';
                $visitorId = Auth::guard('client')->id();
            } elseif (Auth::check()) {
                $visitorType = 'user';
                $visitorId = Auth::id();
            }

            $session = BotSession::create([
                'session_uuid' => $uuid,
                'visitor_type' => $visitorType,
                'visitor_id' => $visitorId,
                'page_url' => $pageUrl,
                'metadata' => $metadata,
                'started_at' => now(),
            ]);
        }

        return $session;
    }

    /**
     * Send a message to the bot.
     * Returns array representation of the bot's message.
     */
    public function sendMessage(BotSession $session, string $text): array
    {
        // Save user message
        BotMessage::create([
            'session_id' => $session->id,
            'role' => 'user',
            'content' => $text,
            'resolved' => true,
        ]);

        // Get response from adapter
        $response = $this->adapter->getResponse($text);

        // Resolve products if matching type
        $products = [];
        if ($response['answer_type'] === 'product_list' && !empty($response['entity_ids'])) {
            $products = $this->resolver->resolveProducts($response['entity_ids']);
        }

        $menuItems = $response['menu_items'] ?? [];
        $smartAttachments = $response['smart_attachments'] ?? [];
        $metadata = [];
        if (!empty($menuItems)) {
            $metadata['menu_items'] = $menuItems;
        }
        if (!empty($smartAttachments)) {
            $metadata['smart_attachments'] = $smartAttachments;
        }

        // Save bot message
        $botMsg = BotMessage::create([
            'session_id' => $session->id,
            'role' => 'bot',
            'content' => $response['answer_text'],
            'question_id' => $response['matched_question_id'],
            'answer_id' => $response['matched_answer_id'],
            'resolved' => $response['confidence'] > 0,
            'confidence_score' => $response['confidence'],
            'metadata' => $metadata,
        ]);

        return [
            'id' => $botMsg->id,
            'role' => 'bot',
            'content' => $response['answer_text'],
            'answer_type' => $response['answer_type'],
            'products' => $products,
            'smart_attachments' => $smartAttachments,
            'menu_items' => $menuItems,
            'confidence' => $response['confidence'],
            'created_at' => $botMsg->created_at->toIso8601String(),
        ];
    }

    /**
     * Process selection of a menu item (Option A: User prompt + bot response).
     */
    public function processMenuItemClick(BotSession $session, int $menuItemId, string $userLabel): array
    {
        $item = \Modules\SmartBot\App\Models\BotMenuItem::with('activeChildren')->findOrFail($menuItemId);

        // 1. Save user message with item's label in DB
        BotMessage::create([
            'session_id' => $session->id,
            'role' => 'user',
            'content' => $userLabel,
            'resolved' => true,
        ]);

        // 2. Prepare response based on item's response_type
        $answerText = $item->response_text ?? $item->label;
        $answerType = $item->response_type ?? 'text';
        $products = [];
        $menuItems = [];
        $url = null;
        $smartAttachments = $item->smart_attachments ?? [];

        if ($answerType === 'product_list' && !empty($item->response_entity_ids)) {
            $products = $this->resolver->resolveProducts($item->response_entity_ids);
        } elseif ($answerType === 'menu_items') {
            $menuItems = $item->activeChildren->toArray();
        } elseif ($answerType === 'url') {
            $url = $item->response_url;
        }

        $metadata = [
            'answer_type' => $answerType,
        ];
        if (!empty($menuItems)) {
            $metadata['menu_items'] = $menuItems;
        }
        if (!empty($smartAttachments)) {
            $metadata['smart_attachments'] = $smartAttachments;
        }
        if (!empty($url)) {
            $metadata['url'] = $url;
        }
        if (!empty($item->response_entity_ids)) {
            $metadata['entity_ids'] = $item->response_entity_ids;
        }

        // 3. Save bot message in DB
        $botMsg = BotMessage::create([
            'session_id' => $session->id,
            'role' => 'bot',
            'content' => $answerText,
            'answer_id' => $item->answer_id,
            'resolved' => true,
            'confidence_score' => 1.0,
            'metadata' => $metadata,
        ]);

        return [
            'id' => $botMsg->id,
            'role' => 'bot',
            'content' => $answerText,
            'answer_type' => $answerType,
            'products' => $products,
            'smart_attachments' => $smartAttachments,
            'menu_items' => $menuItems,
            'url' => $url,
            'confidence' => 1.0,
            'created_at' => $botMsg->created_at->toIso8601String(),
        ];
    }

    /**
     * Get welcome message.
     */
    public function getWelcomeMessage(): string
    {
        return BotSetting::getValue('welcome_message', 'سلام! چطور می‌توانم کمکتان کنم؟');
    }

    /**
     * Get suggested questions (quick replies).
     */
    public function getSuggestedQuestions(int $limit = 5): array
    {
        return BotQuestion::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->pluck('question_text')
            ->toArray();
    }
}
