<?php

namespace Modules\Services\App\Services;

use Modules\Services\App\Http\Models\Status;

class StatusBuilderService
{
    /**
     * Return all statuses grouped by type for the index view.
     * Passes variables $project, $invoice, $payment, $service to the blade.
     */
    public function allGrouped(): array
    {
        $all = Status::orderBy('sort_order')->get()->groupBy('type');

        return [
            'project' => $all->get('project', collect()),
            'order' => $all->get('order', collect()),
            'service' => $all->get('service', collect()),
            'invoice' => $all->get('invoice', collect()),
            'payment' => $all->get('payment', collect()),
        ];
    }

    /**
     * Create a new status, enforcing only one default per type.
     */
    public function create(array $data): Status
    {
        if (!empty($data['is_default'])) {
            Status::where('type', $data['type'])->update(['is_default' => false]);
        }

        return Status::create($this->prepareStatusData($data));
    }

    /**
     * Update an existing status, enforcing only one default per type.
     */
    public function update(Status $status, array $data): Status
    {
        if (!empty($data['is_default'])) {
            Status::where('type', $status->type)
                ->where('id', '!=', $status->id)
                ->update(['is_default' => false]);
        }

        $status->update($this->prepareStatusData($data));

        return $status->fresh();
    }

    /**
     * Prepare data for creating or updating a status.
     */
    private function prepareStatusData(array $data): array
    {
        $attributes = [];
        $attributeKeys = ['converts_to_invoice', 'locks_invoice', 'allows_payment', 'is_successful_payment', 'is_failed_payment'];

        foreach ($attributeKeys as $key) {
            if (!empty($data[$key])) {
                $attributes[$key] = true;
            }
        }

        return [
            'name' => $data['name'],
            'color' => $data['color'],
            'icon' => $data['icon'] ?? null,
            'type' => $data['type'],
            'is_final' => !empty($data['is_final']),
            'is_default' => !empty($data['is_default']),
            'is_readonly' => !empty($data['is_readonly']),
            'attributes' => $attributes,
            'allowed_roles' => $data['allowed_roles'] ?? null,
            'allowed_users' => $data['allowed_users'] ?? null,
        ];
    }

    /**
     * Reorder statuses by the provided array of IDs.
     */
    public function reorder(array $ids): void
    {
        foreach ($ids as $position => $id) {
            Status::where('id', $id)->update(['sort_order' => $position]);
        }
    }
}
