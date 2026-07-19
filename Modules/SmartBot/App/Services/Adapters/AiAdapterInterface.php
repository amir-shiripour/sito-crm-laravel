<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Services\Adapters;

interface AiAdapterInterface
{
    /**
     * Get response for user message.
     * 
     * @param string $message
     * @param array $context
     * @return array{
     *     answer_text: string,
     *     answer_type: string,
     *     entity_type: string|null,
     *     entity_ids: array|null,
     *     confidence: float,
     *     matched_question_id: int|null,
     *     matched_answer_id: int|null
     * }
     */
    public function getResponse(string $message, array $context = []): array;
}
