<?php

namespace Modules\ContractForge\Services;

use App\Models\Module as ModuleModel;
use Nwidart\Modules\Facades\Module;

class ContractModuleHelper
{
    /**
     * Check if a module is both installed and active in the system.
     */
    public static function isModuleActive(string $name): bool
    {
        if (! class_exists(Module::class) || ! Module::has($name) || ! Module::isEnabled($name)) {
            return false;
        }

        if (class_exists(ModuleModel::class)) {
            return ModuleModel::where('name', $name)
                ->where('active', true)
                ->where('installed', true)
                ->exists();
        }

        return true;
    }

    /**
     * Get available contractable entity types based on currently active modules.
     */
    public static function getAvailableEntityTypes(): array
    {
        $types = [];

        if (self::isModuleActive('Booking') && class_exists(\Modules\Booking\App\Models\TreatmentPlan::class)) {
            $types['treatment_plan'] = 'طرح درمان (نوبت‌دهی)';
        }

        if (self::isModuleActive('FieldService') && class_exists(\Modules\FieldService\App\Models\Technician::class)) {
            $types['field_service_technician'] = 'تکنسین فنی (خدمات در محل)';
        }

        return $types;
    }

    /**
     * Get human-readable label for a contractable entity type.
     */
    public static function getEntityTypeLabel(?string $entityType): string
    {
        $types = self::getAvailableEntityTypes();
        if ($entityType && isset($types[$entityType])) {
            return $types[$entityType];
        }

        return match ($entityType) {
            'treatment_plan'           => 'طرح درمان',
            'field_service_technician' => 'تکنسین فنی',
            default                    => 'قرارداد',
        };
    }
}
