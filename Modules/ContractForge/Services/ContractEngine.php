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
                $rawStatus = $entity->status ?? null;
                $status = $rawStatus instanceof \BackedEnum ? $rawStatus->value : $rawStatus;
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

            // Generate (technician contracts are issued directly as 'active' so technician can sign them)
            $initialStatus = $entityType === 'field_service_technician' ? 'active' : 'draft';
            self::generate($rule->template, $entity, $rule->id, null, $initialStatus);
            $generatedCount++;
        }

        return $generatedCount;
    }

    /**
     * Generate a new contract from a template and entity.
     */
    public static function generate(ContractTemplate $template, object $entity, ?int $ruleId = null, ?int $userId = null, string $initialStatus = 'draft'): Contract
    {
        $blocks = $template->blocks ?: [];
        $number = self::generateNextContractNumber();
        $extraTokens = ['contract_number' => $number];
        
        if (!empty($blocks)) {
            $renderedBody = self::renderBlocks($blocks, $entity, $extraTokens);
        } else {
            $renderedBody = TokenResolver::resolve($template->body ?? '', $entity, $extraTokens);
        }

        // Add template css if exists
        if (!empty($template->css_style)) {
            $renderedBody = "<style>{$template->css_style}</style>\n" . $renderedBody;
        }

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
            'status' => in_array($initialStatus, ['draft', 'active'], true) ? $initialStatus : 'draft',
        ]);

        return $contract;
    }

    /**
     * Render blocks with tokens substituted.
     */
    public static function renderBlocks(array $blocks, object $entity, array $extraTokens = []): string
    {
        $html = '<div class="contract-container" style="direction: rtl; text-align: right; font-family: inherit;">';
        foreach ($blocks as $block) {
            $type = $block['type'] ?? 'text';
            switch ($type) {
                case 'header':
                    $title = TokenResolver::resolve($block['title'] ?? '', $entity, $extraTokens);
                    $subtitle = TokenResolver::resolve($block['subtitle'] ?? '', $entity, $extraTokens);
                    $align = in_array($block['align'] ?? '', ['right', 'left', 'center']) ? $block['align'] : 'center';
                    $hasBorder = !isset($block['show_border']) || filter_var($block['show_border'], FILTER_VALIDATE_BOOLEAN) || $block['show_border'] === '1' || $block['show_border'] === 1;
                    $borderClass = $hasBorder ? 'border-b border-gray-200 dark:border-gray-700 pb-3' : 'pb-1';
                    $borderStyle = $hasBorder ? 'border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;' : 'padding-bottom: 4px;';
                    $subtitleHtml = !empty($subtitle)
                        ? "<div class='contract-header-subtitle mt-1.5 text-sm text-gray-600 dark:text-gray-400' style='margin-top: 6px; font-size: 13px; color: #64748b; font-weight: normal; text-align: {$align};'>{$subtitle}</div>"
                        : '';
                    $html .= "<div class='contract-header mb-6 {$borderClass}' style='text-align: {$align}; margin-bottom: 24px; {$borderStyle}'>
                        <h2 class='text-xl font-bold text-gray-900 dark:text-gray-100' style='font-size: 20px; font-weight: bold; margin: 0;'>{$title}</h2>
                        {$subtitleHtml}
                    </div>";
                    break;
                case 'heading':
                    $title = TokenResolver::resolve($block['title'] ?? '', $entity, $extraTokens);
                    $level = in_array($block['level'] ?? '', ['h2', 'h3']) ? $block['level'] : 'h2';
                    $align = in_array($block['align'] ?? '', ['right', 'center', 'left']) ? $block['align'] : 'right';
                    $showBorder = !isset($block['show_border']) || filter_var($block['show_border'], FILTER_VALIDATE_BOOLEAN) || $block['show_border'] === '1' || $block['show_border'] === 1;
                    $fontSize = $level === 'h2' ? '16px' : '14px';
                    $borderStyle = '';
                    if ($showBorder) {
                        $borderStyle = $level === 'h2'
                            ? 'border-right: 4px solid #4f46e5; padding-right: 8px;'
                            : 'border-right: 3px solid #6366f1; padding-right: 6px;';
                    }
                    $html .= "<div class='contract-heading my-4' style='text-align: {$align}; margin: 18px 0 10px;'>
                        <{$level} class='font-bold text-gray-900 dark:text-gray-100' style='font-size: {$fontSize}; font-weight: bold; {$borderStyle} margin: 0; display: inline-block;'>{$title}</{$level}>
                    </div>";
                    break;
                case 'text':
                    $content = TokenResolver::resolve($block['content'] ?? '', $entity, $extraTokens);
                    $align = in_array($block['align'] ?? '', ['right', 'center', 'left', 'justify']) ? $block['align'] : 'justify';
                    $html .= "<div class='contract-text mb-4 leading-relaxed' style='margin-bottom: 16px; line-height: 1.8; text-align: {$align}; white-space: pre-wrap;'>" . $content . "</div>";
                    break;
                case 'table':
                    $tokenKey = $block['content'] ?? '';
                    if ($tokenKey === 'technician_signatures_table') {
                        $tableHtml = self::renderTechnicianSignaturesBox($entity, $extraTokens);
                    } else {
                        $tokens = method_exists($entity, 'getContractTokens') ? $entity->getContractTokens() : [];
                        if (!empty($extraTokens)) {
                            $tokens = array_merge($tokens, $extraTokens);
                        }
                        $tableHtml = $tokens[$tokenKey] ?? '';
                    }
                    $html .= "<div class='contract-table my-4' style='margin: 16px 0;'>{$tableHtml}</div>";
                    break;
                case 'page_break':
                    $html .= "<div class='page-break' style='page-break-after: always;'></div>";
                    break;
                case 'footer':
                    $content = TokenResolver::resolve($block['content'] ?? '', $entity, $extraTokens);
                    $align = in_array($block['align'] ?? '', ['right', 'center', 'left', 'justify']) ? $block['align'] : 'center';
                    $html .= "<div class='contract-footer mt-8 border-t pt-4 text-sm text-gray-600 dark:text-gray-400' style='margin-top: 32px; border-top: 1px solid #e5e7eb; padding-top: 16px; font-size: 14px; text-align: {$align}; white-space: pre-wrap;'>{$content}</div>";
                    break;
            }
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * Render a structured two-column signature box matching the technician PDF layout.
     */
    public static function renderTechnicianSignaturesBox(object $entity, array $extraTokens = []): string
    {
        $techName = e(TokenResolver::resolve('{technician_name}', $entity, $extraTokens));

        return <<<HTML
<table class="signature-grid-table" style="width: 100%; border-collapse: separate; border-spacing: 12px 0; margin: 12px 0; page-break-inside: avoid; direction: rtl;">
    <tr>
        <td style="width: 50%; vertical-align: top; border: 1px solid #cbd5e1; padding: 14px 16px; text-align: right; line-height: 2;">
            <div style="text-align: center; font-weight: bold; margin-bottom: 12px; font-size: 13px;">تکنسین</div>
            <div style="margin-bottom: 10px;">نام و نام خانوادگی: {$techName}</div>
            <div style="margin-bottom: 12px;">امضا: ........................................</div>
            <div style="margin-top: 8px;">
                <span style="display: inline-block; vertical-align: bottom;">اثر انگشت:</span>
                <span style="display: inline-block; width: 66px; height: 82px; border: 1px dashed #9ca3af; vertical-align: middle; margin-right: 6px;"></span>
            </div>
        </td>
        <td style="width: 50%; vertical-align: top; border: 1px solid #cbd5e1; padding: 14px 16px; text-align: right; line-height: 2;">
            <div style="text-align: center; font-weight: bold; margin-bottom: 14px; font-size: 13px;">شرکت زانیار آزادیار سهند</div>
            <div style="display: table; width: 100%; margin-top: 8px;">
                <div style="display: table-cell; width: 50%; vertical-align: top; text-align: right; padding-left: 8px;">
                    <div>آقای برزو ناظری‌راد</div>
                    <div style="margin-top: 34px;">امضا و مهر:</div>
                </div>
                <div style="display: table-cell; width: 50%; vertical-align: top; text-align: right; padding-right: 8px;">
                    <div>آقای محمد براری تنکابنی</div>
                    <div style="margin-top: 34px;">امضا و مهر:</div>
                </div>
            </div>
        </td>
    </tr>
</table>
HTML;
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
                $rawStatus = $entity->status ?? null;
                $status = $rawStatus instanceof \BackedEnum ? $rawStatus->value : $rawStatus;
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
