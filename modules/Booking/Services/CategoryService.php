<?php

namespace Modules\Booking\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Modules\Booking\Entities\BookingCategory;
use Modules\Booking\Entities\BookingSetting;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CategoryService
{
    public function __construct(protected AuditLogger $audit)
    {
    }

    public function paginate(User $user, int $perPage = 50): LengthAwarePaginator
    {
        return $this->scopedQuery($user)
            ->with('creator')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function create(User $user, array $data): BookingCategory
    {
        $data['creator_id'] = $user->id;

        $category = BookingCategory::query()->create($data);

        $this->audit->log('CATEGORY_CREATED', 'booking_categories', $category->id, null, $category->toArray());

        return $category;
    }

    public function update(User $user, BookingCategory $category, array $data): BookingCategory
    {
        $this->ensureCanManage($user, $category);

        $before = $category->toArray();
        $category->fill($data);
        $category->save();

        $this->audit->log('CATEGORY_UPDATED', 'booking_categories', $category->id, $before, $category->toArray());

        return $category;
    }

    public function delete(User $user, BookingCategory $category): void
    {
        $this->ensureCanManage($user, $category);

        $before = $category->toArray();
        $category->delete();

        $this->audit->log('CATEGORY_DELETED', 'booking_categories', $category->id, $before, null);
    }

    private function scopedQuery(User $user): Builder
    {
        $settings = BookingSetting::current();

        $query = BookingCategory::query();

        if ($this->isOwnOnlyScope($user, $settings)) {
            $query->where('creator_id', $user->id);
        }

        return $query;
    }

    private function ensureCanManage(User $user, BookingCategory $category): void
    {
        if ($this->isOwnOnlyScope($user, BookingSetting::current()) && (int) $category->creator_id !== (int) $user->id) {
            throw new HttpException(403, 'Access denied (own-only).');
        }
    }

    private function isOwnOnlyScope(User $user, BookingSetting $settings): bool
    {
        return $settings->category_management_scope === 'OWN'
            && !$user->can('booking.categories.manage')
            && !$user->hasRole('super-admin');
    }
}
