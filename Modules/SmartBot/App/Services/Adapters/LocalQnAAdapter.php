<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Services\Adapters;

use Modules\SmartBot\App\Services\IntentMatcherService;
use Modules\SmartBot\App\Models\BotSetting;

final class LocalQnAAdapter implements AiAdapterInterface
{
    public function __construct(
        private readonly IntentMatcherService $matcher
    ) {}

    public function getResponse(string $message, array $context = []): array
    {
        $match = $this->matcher->match($message);
        $threshold = (float) BotSetting::getValue('match_threshold', 0.25);

        if ($match['question'] && $match['score'] >= $threshold) {
            $question = $match['question'];
            $answer = $question->defaultAnswer();

            if ($answer) {
                return [
                    'answer_text' => $answer->answer_text,
                    'answer_type' => $answer->answer_type,
                    'entity_type' => $answer->entity_type,
                    'entity_ids' => $answer->entity_ids,
                    'confidence' => (float) $match['score'],
                    'matched_question_id' => (int) $question->id,
                    'matched_answer_id' => (int) $answer->id,
                ];
            }
        }

        // Return fallback response
        return [
            'answer_text' => (string) BotSetting::getValue('fallback_response', 'متأسفانه پاسخ مناسبی برای این سوال پیدا نکردم.'),
            'answer_type' => 'text',
            'entity_type' => null,
            'entity_ids' => null,
            'confidence' => 0.0,
            'matched_question_id' => null,
            'matched_answer_id' => null,
        ];
    }
}
