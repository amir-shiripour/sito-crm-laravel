<?php

namespace Modules\Services\App\Policies;

use App\Models\User;
use Modules\Services\App\Http\Models\Project;

class ProjectPolicy
{
    public function viewAny(User $u): bool
    {
        return $u->can('services.projects.view');
    }

    public function view(User $u, Project $p): bool
    {
        return $u->can('services.projects.view');
    }

    public function create(User $u): bool
    {
        return $u->can('services.projects.create');
    }

    public function update(User $u, Project $p): bool
    {
        return !$p->isReadonly() && $u->can('services.projects.manage');
    }

    public function delete(User $u, Project $p): bool
    {
        return $u->can('services.projects.delete');
    }
}
