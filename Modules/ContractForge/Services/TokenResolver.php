<?php

namespace Modules\ContractForge\Services;

use Modules\ContractForge\Contracts\ContractableEntity;

class TokenResolver
{
    /**
     * Resolve all tokens in a given text/template for the entity.
     */
    public static function resolve(string $text, object $entity): string
    {
        $tokens = method_exists($entity, 'getContractTokens') ? $entity->getContractTokens() : [];

        foreach ($tokens as $key => $value) {
            $text = str_replace('{' . $key . '}', (string) ($value ?? ''), $text);
        }

        return $text;
    }

    /**
     * Resolve tokens in blocks list (block-based builder).
     */
    public static function resolveBlocks(array $blocks, object $entity): array
    {
        foreach ($blocks as &$block) {
            if (isset($block['type']) && $block['type'] === 'text' && isset($block['content'])) {
                $block['content'] = self::resolve($block['content'], $entity);
            }
        }
        return $blocks;
    }
}
