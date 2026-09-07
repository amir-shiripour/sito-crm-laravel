<?php

namespace Modules\Services\App\Policies;

use App\Models\User;
use Modules\Services\App\Http\Models\Order;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any orders.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('services.orders.view')
            || $user->can('services.orders.view.all')
            || $user->can('services.orders.manage');
    }

    /**
     * Determine whether the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->can('services.orders.view.all') || $user->can('services.orders.manage')) {
            return true;
        }

        return $user->can('services.orders.view') && (int)$order->created_by === (int)$user->id;
    }

    /**
     * Determine whether the user can create orders (orders are generated automatically).
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the order (manage status / renewals).
     */
    public function update(User $user, Order $order): bool
    {
        return $user->can('services.orders.manage');
    }

    /**
     * Determine whether the user can delete the order (orders cannot be deleted).
     */
    public function delete(User $user, Order $order): bool
    {
        return false;
    }
}
