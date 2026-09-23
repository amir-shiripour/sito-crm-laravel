<?php

namespace Modules\ContractForge\Services;

use Modules\ContractForge\Contracts\ContractableEntity;

class TokenResolver
{
    /**
     * Resolve all tokens in a given text/template for the entity.
     */
    public static function resolve(string $text, object $entity, array $extraTokens = []): string
    {
        $tokens = method_exists($entity, 'getContractTokens') ? $entity->getContractTokens() : [];
        if (!empty($extraTokens)) {
            $tokens = array_merge($tokens, $extraTokens);
        }

        foreach ($tokens as $key => $value) {
            $text = str_replace('{' . $key . '}', (string) ($value ?? ''), $text);
        }

        return $text;
    }

    /**
     * Resolve tokens in blocks list (block-based builder).
     */
    public static function resolveBlocks(array $blocks, object $entity, array $extraTokens = []): array
    {
        foreach ($blocks as &$block) {
            if (isset($block['type']) && in_array($block['type'], ['text', 'footer']) && isset($block['content'])) {
                $block['content'] = self::resolve($block['content'], $entity, $extraTokens);
            }
            if (isset($block['type']) && in_array($block['type'], ['header', 'heading']) && isset($block['title'])) {
                $block['title'] = self::resolve($block['title'], $entity, $extraTokens);
            }
            if (isset($block['type']) && $block['type'] === 'header' && isset($block['subtitle'])) {
                $block['subtitle'] = self::resolve($block['subtitle'], $entity, $extraTokens);
            }
        }
        return $blocks;
    }
}
