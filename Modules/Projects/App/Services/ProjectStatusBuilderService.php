<?php

namespace Modules\Projects\App\Services;

use Modules\Projects\App\Http\Models\ProjectStatus;

class ProjectStatusBuilderService
{
    public function allGrouped(): array
    {
        $all = ProjectStatus::orderBy('sort_order')->get()->groupBy('type');

        return [
            'project' => $all->get('project', collect()),
            'task' => $all->get('task', collect()),
            'checklist' => $all->get('checklist', collect()),
        ];
    }

    public function create(array $data): ProjectStatus
    {
        if (!empty($data['is_default'])) {
            ProjectStatus::where('type', $data['type'])->update(['is_default' => false]);
        }

        return ProjectStatus::create($data);
    }

    public function update(ProjectStatus $status, array $data): ProjectStatus
    {
        if (!empty($data['is_default'])) {
            ProjectStatus::where('type', $status->type)
                ->where('id', '!=', $status->id)
                ->update(['is_default' => false]);
        }

        $status->update($data);

        return $status->fresh();
    }

    public function reorder(array $ids): void
    {
        foreach ($ids as $position => $id) {
            ProjectStatus::where('id', $id)->update(['sort_order' => $position]);
        }
    }
}
