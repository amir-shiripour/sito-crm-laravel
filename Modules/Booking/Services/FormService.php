<?php

namespace Modules\Booking\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Modules\Booking\Entities\BookingForm;
use Modules\Booking\Entities\BookingSetting;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FormService
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

    public function create(User $user, array $data): BookingForm
    {
        $data['creator_id'] = $user->id;

        $form = BookingForm::query()->create($data);

        $this->audit->log('FORM_CREATED', 'booking_forms', $form->id, $user->id, null, $form->toArray());

        return $form;
    }

    public function update(User $user, BookingForm $form, array $data): BookingForm
    {
        $this->ensureCanManage($user, $form);

        $before = $form->toArray();
        $form->fill($data);
        $form->save();

        $this->audit->log('FORM_UPDATED', 'booking_forms', $form->id, $user->id, $before, $form->toArray());

        return $form;
    }

    public function delete(User $user, BookingForm $form): void
    {
        $this->ensureCanManage($user, $form);

        $before = $form->toArray();
        $form->delete();

        $this->audit->log('FORM_DELETED', 'booking_forms', $form->id, $user->id, $before, null);
    }

    private function scopedQuery(User $user): Builder
    {
        $settings = BookingSetting::current();

        $query = BookingForm::query();

        if ($this->isOwnOnlyScope($user, $settings)) {
            $query->where('creator_id', $user->id);
        }

        return $query;
    }

    private function ensureCanManage(User $user, BookingForm $form): void
    {
        if ($this->isOwnOnlyScope($user, BookingSetting::current()) && (int) $form->creator_id !== (int) $user->id) {
            throw new HttpException(403, 'Access denied (own-only).');
        }
    }

    private function isOwnOnlyScope(User $user, BookingSetting $settings): bool
    {
        return $settings->form_management_scope === 'OWN'
            && ! $user->can('booking.forms.manage')
            && ! $user->hasRole('super-admin');
    }
}
