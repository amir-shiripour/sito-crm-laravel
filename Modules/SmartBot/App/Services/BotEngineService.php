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

        // Save bot message
        $botMsg = BotMessage::create([
            'session_id' => $session->id,
            'role' => 'bot',
            'content' => $response['answer_text'],
            'question_id' => $response['matched_question_id'],
            'answer_id' => $response['matched_answer_id'],
            'resolved' => $response['confidence'] > 0,
            'confidence_score' => $response['confidence'],
        ]);

        return [
            'id' => $botMsg->id,
            'role' => 'bot',
            'content' => $response['answer_text'],
            'answer_type' => $response['answer_type'],
            'products' => $products,
            'confidence' => $response['confidence'],
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
