<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Services;

use Modules\SmartBot\App\Models\BotQuestion;

final class IntentMatcherService
{
    /**
     * Clean and tokenize string for comparison.
     */
    private function tokenize(string $text): array
    {
        $text = mb_strtolower($text, 'UTF-8');
        // Remove common punctuation marks
        $text = preg_replace('/[?[\]().,!;:؟،؟]/u', ' ', $text);
        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        return array_unique($words);
    }

    /**
     * Match user query against active questions.
     * Returns array: [question => BotQuestion|null, score => float]
     */
    public function match(string $query): array
    {
        $queryWords = $this->tokenize($query);
        if (empty($queryWords)) {
            return ['question' => null, 'score' => 0.0];
        }

        $questions = BotQuestion::where('is_active', true)
            ->with(['answers'])
            ->get();

        $bestMatch = null;
        $bestScore = 0.0;

        foreach ($questions as $question) {
            $score = 0.0;
            
            // Heuristic 1: Exact question match
            $cleanQuestionText = mb_strtolower($question->question_text, 'UTF-8');
            $cleanQueryText = mb_strtolower($query, 'UTF-8');
            
            if ($cleanQuestionText === $cleanQueryText) {
                $score = 1.0;
            } elseif (mb_strpos($cleanQueryText, $cleanQuestionText) !== false || mb_strpos($cleanQuestionText, $cleanQueryText) !== false) {
                $score = 0.8;
            } else {
                // Heuristic 2: Keyword overlap
                $keywords = $question->keywords ?? [];
                if (!empty($keywords)) {
                    $matchedCount = 0;
                    foreach ($keywords as $keyword) {
                        $keywordLower = mb_strtolower($keyword, 'UTF-8');
                        if (mb_strpos($cleanQueryText, $keywordLower) !== false) {
                            $matchedCount++;
                        }
                    }
                    $keywordScore = $matchedCount / count($keywords);
                    $score = $keywordScore * 0.7; // Scale it
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $question;
            }
        }

        return [
            'question' => $bestMatch,
            'score' => $bestScore,
        ];
    }
}
