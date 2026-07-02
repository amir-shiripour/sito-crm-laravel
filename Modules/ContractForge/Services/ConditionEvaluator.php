<?php

namespace Modules\ContractForge\Services;

class ConditionEvaluator
{
    /**
     * Evaluate conditions against an entity.
     */
    public static function evaluate(?array $conditions, $entity): bool
    {
        if (empty($conditions)) {
            return true;
        }

        $operator = strtoupper($conditions['operator'] ?? 'AND');
        $rules = $conditions['rules'] ?? [];

        if (empty($rules)) {
            return true;
        }

        $results = [];

        foreach ($rules as $rule) {
            if (isset($rule['operator'])) {
                // Nested group
                $results[] = self::evaluate($rule, $entity);
            } else {
                // Single leaf condition
                $results[] = self::evaluateSingleRule($rule, $entity);
            }
        }

        if ($operator === 'OR') {
            return in_array(true, $results, true);
        }

        // Default to AND
        return !in_array(false, $results, true);
    }

    /**
     * Evaluate a single condition rule.
     */
    protected static function evaluateSingleRule(array $rule, $entity): bool
    {
        $field = $rule['field'] ?? '';
        $op = $rule['op'] ?? 'equals';
        $targetValue = $rule['value'] ?? null;

        // Resolve value from the entity
        $actualValue = null;
        if (is_object($entity)) {
            // First check if there is an attribute or property
            if (isset($entity->{$field})) {
                $actualValue = $entity->{$field};
            } elseif (method_exists($entity, $field)) {
                $actualValue = $entity->{$field}();
            } else {
                // Fallback to getContractTokens
                $tokens = method_exists($entity, 'getContractTokens') ? $entity->getContractTokens() : [];
                if (array_key_exists($field, $tokens)) {
                    $actualValue = $tokens[$field];
                }
            }
        } elseif (is_array($entity)) {
            $actualValue = $entity[$field] ?? null;
        }

        // Normalize installment/payment option comparison if target contains standard prefixes
        if ($field === 'installment_option_title') {
            $prefixes = ['اقساطی - ', 'کارتخوان - ', 'کارت به کارت - '];
            foreach ($prefixes as $prefix) {
                if ($targetValue && str_starts_with($targetValue, $prefix)) {
                    $cleanTarget = mb_substr($targetValue, mb_strlen($prefix));
                    if ($actualValue == $cleanTarget) {
                        $actualValue = $targetValue;
                        break;
                    }
                }
            }
        }

        // Compare
        switch ($op) {
            case 'equals':
                return $actualValue == $targetValue;
            case 'not_equals':
                return $actualValue != $targetValue;
            case 'gt':
                return $actualValue > $targetValue;
            case 'gte':
                return $actualValue >= $targetValue;
            case 'lt':
                return $actualValue < $targetValue;
            case 'lte':
                return $actualValue <= $targetValue;
            case 'contains':
                return str_contains((string)$actualValue, (string)$targetValue);
            case 'in':
                $list = is_array($targetValue) ? $targetValue : array_map('trim', explode(',', $targetValue));
                return in_array($actualValue, $list);
            case 'not_in':
                $list = is_array($targetValue) ? $targetValue : array_map('trim', explode(',', $targetValue));
                return !in_array($actualValue, $list);
            case 'is_null':
                return is_null($actualValue) || $actualValue === '' || $actualValue === [];
            case 'is_not_null':
                return !is_null($actualValue) && $actualValue !== '' && $actualValue !== [];
            default:
                return false;
        }
    }
}
