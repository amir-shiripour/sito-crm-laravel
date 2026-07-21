<?php

namespace Modules\Services\App\Services;

use Modules\Services\App\Http\Models\Project;
use Modules\Services\App\Http\Models\Status;

class ProjectService
{
    public function create(array $data): Project
    {
        return Project::create($data);
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);
        return $project->fresh();
    }

    public function changeStatus(Project $project, int $statusId): Project
    {
        $newStatus = Status::findOrFail($statusId);

        if (!$project->status->canTransitionTo($newStatus)) {
            throw new \LogicException("انتقال به وضعیت «{$newStatus->name}» مجاز نیست.");
        }

        $project->update(['status_id' => $statusId]);
        return $project->fresh();
    }
}
