<?php

namespace Modules\Projects\App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProjectsUserVisibilityService
{
    public const SUPER_ADMIN_ROLES = ['super-admin', 'superadmin'];

    /**
     * Check if a user has a super-admin role.
     */
    public static function isSuperAdmin($user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole(self::SUPER_ADMIN_ROLES);
        }

        if (method_exists($user, 'hasRole')) {
            foreach (self::SUPER_ADMIN_ROLES as $role) {
                if ($user->hasRole($role)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Scope an Eloquent query for User model to hide pure super-admin users
     * unless the viewer is also a super-admin or the target user has multiple roles.
     */
    public static function applyScope(Builder $query, $viewer = null): Builder
    {
        $viewer = $viewer ?? auth()->user();

        // If the viewer is a super-admin, no restriction applies.
        if (self::isSuperAdmin($viewer)) {
            return $query;
        }

        $superAdminRoles = self::SUPER_ADMIN_ROLES;

        // Target user is kept if:
        // 1. Target user does NOT have any super-admin role
        // OR
        // 2. Target user has at least one role that is NOT a super-admin role (multiple roles)
        return $query->where(function (Builder $subQuery) use ($superAdminRoles) {
            $subQuery->whereDoesntHave('roles', function (Builder $rq) use ($superAdminRoles) {
                $rq->whereIn('name', $superAdminRoles);
            })->orWhereHas('roles', function (Builder $rq) use ($superAdminRoles) {
                $rq->whereNotIn('name', $superAdminRoles);
            });
        });
    }

    /**
     * Determine if a target user can be viewed/selected by the viewer.
     */
    public static function canViewUser($targetUser, $viewer = null): bool
    {
        if (!$targetUser) {
            return false;
        }

        $viewer = $viewer ?? auth()->user();

        // If the viewer is a super-admin, they can view all users.
        if (self::isSuperAdmin($viewer)) {
            return true;
        }

        // If the target user is not a super-admin, they are visible to everyone.
        if (!self::isSuperAdmin($targetUser)) {
            return true;
        }

        // Target user is a super-admin:
        // They are visible ONLY if they have at least one non-super-admin role.
        $superAdminRoles = self::SUPER_ADMIN_ROLES;

        if ($targetUser->relationLoaded('roles')) {
            return $targetUser->roles->contains(function ($role) use ($superAdminRoles) {
                return !in_array($role->name, $superAdminRoles, true);
            });
        }

        if (method_exists($targetUser, 'roles')) {
            return $targetUser->roles()->whereNotIn('name', $superAdminRoles)->exists();
        }

        return false;
    }

    /**
     * Retrieve all users visible to the viewer.
     */
    public static function getVisibleUsers(array $columns = ['*'], $viewer = null): Collection
    {
        $query = User::query();
        self::applyScope($query, $viewer);

        return $query->select($columns)->orderBy('name')->get();
    }

    /**
     * Filter a Collection of ProjectMember models to only include members whose user
     * can be viewed/selected by the current viewer.
     */
    public static function filterMembersCollection(Collection $members, $viewer = null): Collection
    {
        $viewer = $viewer ?? auth()->user();
        if (self::isSuperAdmin($viewer)) {
            return $members;
        }

        // Preload roles on users that haven't loaded them yet to prevent N+1 queries
        $usersToLoad = $members->pluck('user')->filter(function ($u) {
            return $u && !$u->relationLoaded('roles');
        });

        if ($usersToLoad->isNotEmpty()) {
            $usersToLoad->load('roles');
        }

        return $members->filter(function ($member) use ($viewer) {
            $user = $member->user;
            if (!$user) {
                return true;
            }

            return self::canViewUser($user, $viewer);
        })->values();
    }
}
