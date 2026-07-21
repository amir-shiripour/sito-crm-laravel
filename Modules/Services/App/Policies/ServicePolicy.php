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
        return $u->can('services.view');
    }

    public function view(User $u, Service $s): bool
    {
        return $u->can('services.view');
    }

    public function create(User $u): bool
    {
        return $u->can('services.create');
    }

    public function update(User $u, Service $s): bool
    {
        return $u->can('services.edit');
    }

    public function delete(User $u, Service $s): bool
    {
        return $u->can('services.delete');
    }

    public function duplicate(User $u, Service $s): bool
    {
        return $u->can('services.duplicate');
    }
}
