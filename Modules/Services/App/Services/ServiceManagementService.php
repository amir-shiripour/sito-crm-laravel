<?php

namespace Modules\Services\App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Services\App\Http\Models\CustomField;
use Modules\Services\App\Http\Models\Service;

class ServiceManagementService
{
    /**
     * Create a new service with optional custom fields.
     */
    public function create(array $data, array $customFields = []): Service
    {
        $service = Service::create($this->prepareData($data));

        $this->syncCustomFields($service, $customFields);

        return $service;
    }

    /**
     * Update an existing service with optional custom fields.
     */
    public function update(Service $service, array $data, array $customFields = []): Service
    {
        $service->update($this->prepareData($data));

        $this->syncCustomFields($service, $customFields);

        return $service->fresh();
    }

    /**
     * Duplicate a service (clone with a new name and code).
     */
    public function duplicate(Service $service): Service
    {
        $service->load('customFields');

        $clone = $service->replicate(['code']);
        $clone->name = $service->name . ' (کپی)';
        $clone->code = null;
        $clone->sort_order = $service->sort_order + 1;
        $clone->save();

        // Duplicate custom fields
        foreach ($service->customFields as $field) {
            $newField = $field->replicate();
            $clone->customFields()->save($newField);
        }

        return $clone;
    }

    /**
     * Prepare and clean incoming data before save.
     */
    private function prepareData(array $data): array
    {
        unset($data['custom_fields']);

        // لاجیک مربوط به فروش واحدی
        $data['has_unit_pricing'] = !empty($data['has_unit_pricing']);
        if (!$data['has_unit_pricing']) {
            $data['unit_name'] = null;
            $data['unit_price'] = null;
        }

        if (!isset($data['billing_type']) || empty($data['billing_type'])) {
            $data['billing_type'] = 'one_time';
        }
        if (($data['billing_type'] ?? '') !== 'recurring') {
            $data['renewal_reminder_days'] = $data['renewal_reminder_days'] ?? 7;
            $data['renewal_prices'] = null;
        }

        return $data;
    }

    private function syncCustomFields(Service $service, array $customFields): void
    {
        $existingIds = $service->customFields()->pluck('id')->toArray();

        if (empty($customFields) && !empty($existingIds)) {
            return;
        }

        DB::transaction(function () use ($service, $customFields, $existingIds) {
            $submittedIds = [];

            foreach ($customFields as $index => $fieldData) {
                if (empty($fieldData['label'])) {
                    continue;
                }

                $payload = [
                    'label' => $fieldData['label'],
                    'key' => Str::slug($fieldData['label'], '_') . '_' . $index,
                    'type' => $fieldData['type'] ?? 'text',
                    'options' => $fieldData['options'] ?? null,
                    'is_required' => !empty($fieldData['is_required']),
                    'has_pricing' => !empty($fieldData['has_pricing']),
                    'pricing_type' => $fieldData['pricing_type'] ?? null,
                    'pricing_amount' => (int)($fieldData['pricing_amount'] ?? 0),
                    'show_in_invoice' => !empty($fieldData['show_in_invoice']),
                    'sort_order' => $index,
                ];

                if (!empty($fieldData['id'])) {
                    $field = $service->customFields()->find($fieldData['id']);
                    if ($field) {
                        $field->update($payload);
                        $submittedIds[] = $field->id;
                        continue;
                    }
                }

                $newField = $service->customFields()->create($payload);
                $submittedIds[] = $newField->id;
            }

            $toDelete = array_diff($existingIds, $submittedIds);
            if (!empty($toDelete)) {
                $service->customFields()->whereIn('id', $toDelete)->delete();
            }
        });
    }

    public function syncCustomFieldsPublic(Service $service, array $customFields): void
    {
        $this->syncCustomFields($service, $customFields);
    }
}
