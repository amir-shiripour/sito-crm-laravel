<?php

namespace Modules\Services\App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\User;
use Modules\Services\App\Http\Models\Service;

class ServicePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $u): bool
    {
        return $u->can('services.view') || $u->can('services.manage');
    }

    public function view(User $u, mixed $s = null): bool
    {
        return $u->can('services.view') || $u->can('services.manage');
    }

    public function create(User $u): bool
    {
        return $u->can('services.create') || $u->can('services.manage');
    }

    public function update(User $u, mixed $s = null): bool
    {
        return $u->can('services.edit') || $u->can('services.manage');
    }

    public function delete(User $u, mixed $s = null): bool
    {
        return $u->can('services.delete') || $u->can('services.manage');
    }

    public function duplicate(User $u, mixed $s = null): bool
    {
        return $u->can('services.duplicate') || $u->can('services.manage');
    }
}
