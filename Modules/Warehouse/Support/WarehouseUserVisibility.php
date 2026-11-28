<?php

namespace Modules\Warehouse\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class WarehouseUserVisibility
{
    public const MASKED_LABEL = 'سیستم';

    /**
     * Determine whether the given (or authenticated) viewer has the super-admin role.
     */
    public static function isSuperAdmin(?User $viewer = null): bool
    {
        $viewer ??= auth()->user();
        if (!$viewer) {
            return false;
        }

        return method_exists($viewer, 'hasRole') && $viewer->hasRole('super-admin');
    }

    /**
     * Only super-admin and admin users may select another user as transfer requester.
     */
    public static function canSelectOtherRequester(?User $viewer = null): bool
    {
        $viewer ??= auth()->user();
        if (!$viewer) {
            return false;
        }

        return method_exists($viewer, 'hasAnyRole') && $viewer->hasAnyRole(['super-admin', 'admin']);
    }

    /**
     * Determine whether a target user holds the super-admin role.
     */
    public static function targetIsSuperAdmin(?User $targetUser): bool
    {
        if (!$targetUser) {
            return false;
        }

        return method_exists($targetUser, 'hasRole') && $targetUser->hasRole('super-admin');
    }

    /**
     * Determine whether viewer is allowed to see target user's identity.
     */
    public static function canSeeUser(?User $targetUser, ?User $viewer = null): bool
    {
        if (!$targetUser) {
            return true;
        }

        if (static::isSuperAdmin($viewer)) {
            return true;
        }

        return !static::targetIsSuperAdmin($targetUser);
    }

    /**
     * Exclude super-admin users from Eloquent queries when viewer is not super-admin.
     */
    public static function scopeVisibleUsers(Builder $query, ?User $viewer = null): Builder
    {
        if (static::isSuperAdmin($viewer)) {
            return $query;
        }

        return $query->whereDoesntHave('roles', fn($r) => $r->where('name', 'super-admin'));
    }

    /**
     * Filter an array of user IDs so non-super-admins cannot attach or assign super-admin users.
     */
    public static function sanitizeSelectableUserIds(array $userIds, ?User $viewer = null): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $userIds), fn($id) => $id > 0)));
        if (empty($ids) || static::isSuperAdmin($viewer)) {
            return $ids;
        }

        return User::query()
            ->whereIn('id', $ids)
            ->whereDoesntHave('roles', fn($r) => $r->where('name', 'super-admin'))
            ->pluck('id')
            ->map(fn($id) => (int)$id)
            ->all();
    }

    /**
     * Filter a collection/iterable of warehouse managers so super-admins stay hidden from non-super-admins.
     */
    public static function filterVisibleManagers(iterable $managers, ?User $viewer = null): Collection
    {
        $collection = collect($managers);
        if (static::isSuperAdmin($viewer)) {
            return $collection->values();
        }

        return $collection
            ->filter(fn($user) => $user instanceof User ? !static::targetIsSuperAdmin($user) : true)
            ->values();
    }

    /**
     * Display a user's name or neutral fallback ("سیستم") if the target is a hidden super-admin.
     */
    public static function displayUserName(?User $targetUser, ?User $viewer = null, string $fallback = self::MASKED_LABEL): string
    {
        if (!$targetUser) {
            return $fallback;
        }

        if (!static::canSeeUser($targetUser, $viewer)) {
            return self::MASKED_LABEL;
        }

        return $targetUser->name ?: $fallback;
    }

    /**
     * Format a user object safely for autocomplete chips.
     */
    public static function formatUserOption(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email ?? '',
            'mobile' => $user->mobile ?? '',
            'initial' => mb_substr(trim($user->name ?: 'ک'), 0, 1, 'utf-8'),
            'avatar' => $user->profile_photo_url ?? null,
        ];
    }
}
