<?php

namespace Modules\ContractForge\Services;

use Illuminate\Support\Str;
use Morilog\Jalali\Jalalian;
use Modules\ContractForge\Contracts\ContractableEntity;
use Modules\ContractForge\App\Models\Contract;
use Modules\ContractForge\App\Models\ContractTemplate;
use Modules\ContractForge\App\Models\ContractRule;
use Modules\ContractForge\App\Models\ContractSetting;

class ContractEngine
{
    /**
     * Trigger auto-creation of contracts based on rules.
     */
    public static function autoTrigger(string $entityType, object $entity, string $event, ?string $previousStatus = null): int
    {
        $rules = ContractRule::where('entity_type', $entityType)
            ->where('trigger_event', $event)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();

        $generatedCount = 0;

        foreach ($rules as $rule) {
            // Check status if filter is defined
            if (!empty($rule->trigger_statuses)) {
                $status = $entity->status ?? null;
                if (!in_array($status, $rule->trigger_statuses)) {
                    continue;
                }
            }

            // Evaluate conditions
            if (!ConditionEvaluator::evaluate($rule->conditions, $entity)) {
                continue;
            }

            // Prevent duplicate contracts from the same rule
            if ($rule->prevent_duplicate) {
                $exists = Contract::where('rule_id', $rule->id)
                    ->where('contractable_type', get_class($entity))
                    ->where('contractable_id', $entity->id)
                    ->exists();
                if ($exists) {
                    continue;
                }
            }

            // Generate
            self::generate($rule->template, $entity, $rule->id);
            $generatedCount++;
        }

        return $generatedCount;
    }

    /**
     * Generate a new contract from a template and entity.
     */
    public static function generate(ContractTemplate $template, object $entity, ?int $ruleId = null, ?int $userId = null): Contract
    {
        $blocks = $template->blocks ?: [];
        
        if (!empty($blocks)) {
            $renderedBody = self::renderBlocks($blocks, $entity);
        } else {
            $renderedBody = TokenResolver::resolve($template->body ?? '', $entity);
        }

        // Add template css if exists
        if (!empty($template->css_style)) {
            $renderedBody = "<style>{$template->css_style}</style>\n" . $renderedBody;
        }

        $number = self::generateNextContractNumber();

        $clientId = method_exists($entity, 'getContractClientId') ? $entity->getContractClientId() : null;
        $title = $template->name . ' - ' . (method_exists($entity, 'getContractTitle') ? $entity->getContractTitle() : '');

        $contract = Contract::create([
            'contract_number' => $number,
            'template_id' => $template->id,
            'rule_id' => $ruleId,
            'contractable_type' => get_class($entity),
            'contractable_id' => $entity->id,
            'client_id' => $clientId,
            'user_id' => $userId ?: auth()->id() ?: 1, // Fallback to 1 (admin)
            'title' => $title,
            'blocks_data' => $blocks,
            'rendered_body' => $renderedBody,
            'status' => 'draft',
        ]);

        return $contract;
    }

    /**
     * Render blocks with tokens substituted.
     */
    public static function renderBlocks(array $blocks, object $entity): string
    {
        $html = '<div class="contract-container" style="direction: rtl; text-align: right; font-family: inherit;">';
        foreach ($blocks as $block) {
            $type = $block['type'] ?? 'text';
            switch ($type) {
                case 'header':
                    $title = TokenResolver::resolve($block['title'] ?? '', $entity);
                    $html .= "<div class='contract-header text-center mb-6' style='text-align: center; margin-bottom: 24px;'><h2 class='text-xl font-bold' style='font-size: 20px; font-weight: bold;'>{$title}</h2></div>";
                    break;
                case 'text':
                    $content = TokenResolver::resolve($block['content'] ?? '', $entity);
                    $html .= "<div class='contract-text mb-4 leading-relaxed text-justify' style='margin-bottom: 16px; line-height: 1.8; text-align: justify; white-space: pre-wrap;'>" . $content . "</div>";
                    break;
                case 'table':
                    $tokenKey = $block['content'] ?? '';
                    $tokens = method_exists($entity, 'getContractTokens') ? $entity->getContractTokens() : [];
                    $tableHtml = $tokens[$tokenKey] ?? '';
                    $html .= "<div class='contract-table my-4' style='margin: 16px 0;'>{$tableHtml}</div>";
                    break;
                case 'page_break':
                    $html .= "<div class='page-break' style='page-break-after: always;'></div>";
                    break;
                case 'footer':
                    $content = TokenResolver::resolve($block['content'] ?? '', $entity);
                    $html .= "<div class='contract-footer mt-8 border-t pt-4 text-sm text-gray-500' style='margin-top: 32px; border-top: 1px solid #e5e7eb; padding-top: 16px; font-size: 14px; color: #6b7280; white-space: pre-wrap;'>{$content}</div>";
                    break;
            }
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * Generate the next contract number based on settings.
     */
    public static function generateNextContractNumber(): string
    {
        $format = ContractSetting::get('number_format', 'CON-{YEAR}{MONTH}{DAY}-{COUNTER}');
        $counter = (int) ContractSetting::get('number_counter', 1);
        $length = (int) ContractSetting::get('number_counter_length', 5);

        $now = Jalalian::now();
        $year = $now->format('Y');
        $month = $now->format('m');
        $day = $now->format('d');

        $formattedCounter = str_pad((string) $counter, $length, '0', STR_PAD_LEFT);

        $number = str_replace(
            ['{YEAR}', '{MONTH}', '{DAY}', '{COUNTER}'],
            [$year, $month, $day, $formattedCounter],
            $format
        );

        // Update counter
        ContractSetting::set('number_counter', $counter + 1);

        return $number;
    }

    /**
     * Find a matching template based on active rules.
     */
    public static function findMatchingTemplate(string $entityType, object $entity): ?ContractTemplate
    {
        // Get all active rules for this entity type, ordered by priority
        $rules = ContractRule::where('entity_type', $entityType)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();

        foreach ($rules as $rule) {
            // Check status if filter is defined
            if (!empty($rule->trigger_statuses)) {
                $status = $entity->status ?? null;
                if (!in_array($status, $rule->trigger_statuses)) {
                    continue;
                }
            }

            // Evaluate conditions
            if (ConditionEvaluator::evaluate($rule->conditions, $entity)) {
                // Load the template relationship
                return $rule->template;
            }
        }

        // Fallback: If no matching rule, check if rules exist.
        // If rules exist but none match, we should return null (i.e. do not issue).
        // If NO rules exist at all, we can fallback to the first active template.
        $hasRules = ContractRule::where('entity_type', $entityType)->where('is_active', true)->exists();
        if ($hasRules) {
            return null;
        }

        return ContractTemplate::where('entity_type', $entityType)
            ->where('is_active', true)
            ->first();
    }
}
