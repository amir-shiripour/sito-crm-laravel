<?php

namespace Modules\Workflows\Services;

class ConditionEvaluator
{
    /**
     * Evaluate a condition string or structure against a given context.
     */
    public function evaluate(?string $condition, array $context, ?\Modules\Workflows\Entities\WorkflowNode $currentNode = null): bool
    {
        if (empty($condition)) {
            return true; // No condition means it's an unconditional transition
        }

        // Handle predefined Yes/No conditions from designer UI
        if ($condition === 'بله' || $condition === 'خیر') {
            $varName = 'condition_result';
            if ($currentNode && $currentNode->type === \Modules\Workflows\Entities\WorkflowNode::TYPE_CONDITION) {
                $expr = $currentNode->config['condition_expression'] ?? '';
                if (str_contains($expr, '=')) {
                    $varName = trim(explode('=', $expr, 2)[0]);
                }
            }
            $userChoice = $this->resolveValue($varName, $context);
            // Treat missing values as strictly not matching yet (should pause)
            if ($userChoice === null) return false;
            
            $isTruthy = in_array($userChoice, [true, 'true', 1, '1'], true);
            return $condition === 'بله' ? $isTruthy : !$isTruthy;
        }

        // Support simple format: key=value (e.g. needs_surgery=true)
        if (str_contains($condition, '=')) {
            [$key, $val] = explode('=', $condition, 2);
            $key = trim($key);
            $val = trim($val);

            $contextValue = $this->resolveValue($key, $context);

            // Cast string true/false to boolean
            if ($val === 'true') $val = true;
            if ($val === 'false') $val = false;
            if ($contextValue === 'true') $contextValue = true;
            if ($contextValue === 'false') $contextValue = false;

            return $contextValue == $val;
        }

        // Support custom array/json condition structure if passed as json
        $decoded = json_decode($condition, true);
        if (is_array($decoded)) {
            return $this->evaluateArrayCondition($decoded, $context);
        }

        return false;
    }

    protected function evaluateArrayCondition(array $rules, array $context): bool
    {
        $field = $rules['field'] ?? null;
        $operator = $rules['operator'] ?? '=';
        $expected = $rules['value'] ?? null;

        if (!$field) {
            return true;
        }

        $actual = $this->resolveValue($field, $context);

        switch ($operator) {
            case '=':
            case '==':
                return $actual == $expected;
            case '!=':
            case '<>':
                return $actual != $expected;
            case '>':
                return $actual > $expected;
            case '<':
                return $actual < $expected;
            case '>=':
                return $actual >= $expected;
            case '<=':
                return $actual <= $expected;
            default:
                return $actual == $expected;
        }
    }

    public function resolveValue(string $key, array $context)
    {
        if (array_key_exists($key, $context)) {
            return $context[$key];
        }

        if (isset($context['tokens']) && is_array($context['tokens']) && array_key_exists($key, $context['tokens'])) {
            return $context['tokens'][$key];
        }

        // Support dot notation (e.g. appointment.status)
        $parts = explode('.', $key);
        $current = $context;
        foreach ($parts as $part) {
            if (is_array($current) && array_key_exists($part, $current)) {
                $current = $current[$part];
            } elseif (is_object($current) && isset($current->$part)) {
                $current = $current->$part;
            } else {
                return null;
            }
        }

        return $current;
    }
}
