<?php

namespace Modules\FieldService\App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\FieldService\App\Models\Building;
use Modules\FieldService\App\Models\BuildingEquipment;
use Modules\FieldService\App\Models\SlaContract;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\Tasks\Entities\Task;

class BuildingMaintenanceSchedulerService
{
    protected FieldServiceWorkflowBridgeService $workflowBridge;

    public function __construct(FieldServiceWorkflowBridgeService $workflowBridge)
    {
        $this->workflowBridge = $workflowBridge;
    }

    /**
     * Find all equipment whose next_service_due date is approaching (within $days) or overdue.
     */
    public function getDueEquipments(int $daysAhead = 7): Collection
    {
        return BuildingEquipment::with(['building.client'])
            ->whereNotNull('next_service_due')
            ->whereDate('next_service_due', '<=', Carbon::now()->addDays($daysAhead))
            ->get();
    }

    /**
     * Get active SLA contracts that require periodic monthly visits.
     */
    public function getActiveSlaContracts(): Collection
    {
        return SlaContract::with(['building.client'])
            ->where('status', 'active')
            ->whereDate('start_date', '<=', Carbon::now())
            ->whereDate('end_date', '>=', Carbon::now())
            ->get();
    }

    /**
     * Automatically create follow-up tasks or service reminders for due equipment.
     */
    public function scheduleMaintenanceAlerts(): int
    {
        $dueEquipments = $this->getDueEquipments(7);
        $count = 0;

        foreach ($dueEquipments as $equipment) {
            $building = $equipment->building;
            if (!$building) {
                continue;
            }

            try {
                // If Tasks module is present, create a preventive maintenance task
                if (class_exists(Task::class)) {
                    $taskTitle = "سرویس دوره‌ای {$equipment->equipment_type_label} در مجتمع {$building->name}";
                    $existingTask = Task::where('title', 'like', "%{$equipment->title}%")
                        ->whereDate('created_at', '>=', Carbon::now()->subDays(15))
                        ->first();

                    if (!$existingTask) {
                        Task::create([
                            'title'        => $taskTitle,
                            'description'  => "تجهیز: {$equipment->title} ({$equipment->brand_model})\nساختمان: {$building->name} - مدیر: {$building->manager_name} ({$building->manager_phone})\nموعد سرویس: {$equipment->next_service_due}",
                            'status'       => 'pending',
                            'priority'     => 'medium',
                            'due_date'     => $equipment->next_service_due ? Carbon::parse($equipment->next_service_due) : Carbon::now()->addDays(3),
                            'related_type' => 'client',
                            'related_id'   => $building->client_id,
                        ]);
                        $count++;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("[BuildingMaintenance] Could not create task for equipment {$equipment->id}: " . $e->getMessage());
            }
        }

        return $count;
    }
}
