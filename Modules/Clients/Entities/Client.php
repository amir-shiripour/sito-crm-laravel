<?php

namespace Modules\Clients\Entities;

use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Modules\Market\App\Models\Order;
use Modules\Clients\Entities\ClientForm;
use Modules\Clients\Entities\ClientSetting;
use Modules\Wallet\App\Traits\HasWallet;

class Client extends Authenticatable
{
    use SoftDeletes, HasFactory, Notifiable, HasWallet;

    protected $table = 'clients';

    protected $fillable = [
        'username',
        'full_name',
        'email',
        'phone',
        'national_code',
        'case_number',
        'notes',
        'status_id',
        'meta',
        'created_by',
        'password',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'client_user', 'client_id', 'user_id');
    }


    public function orders()
    {
        return $this->hasMany(Order::class, 'client_id');
    }


    public function addresses()
    {
        return $this->hasMany(ClientAddress::class, 'client_id');
    }

    public function scopeVisibleForUser(Builder $query, User $user): Builder
    {
        if (! $user->can('clients.view')) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('super-admin')) {
            return $query;
        }

        if ($user->can('clients.view.all') || $user->can('clients.manage')) {
            return $query;
        }

        $superiorIds = $user->superiors()->pluck('users.id')->toArray();
        $userIds = array_merge([$user->id], $superiorIds);
        if ($user->can('clients.view.assigned')) {
            return $query->where(function (Builder $q) use ($userIds) {
                $q->whereIn('created_by', $userIds)
                    ->orWhereHas('users', function (Builder $sub) use ($userIds) {
                        $sub->whereIn('users.id', $userIds);
                    });
            });
        }

        if ($user->can('clients.view.own')) {
            return $query->whereIn('created_by', $userIds);
        }

        return $query->whereIn('created_by', $userIds);
    }

    public function isVisibleFor(User $user): bool
    {
        return static::withTrashed()
            ->visibleForUser($user)
            ->whereKey($this->getKey())
            ->exists();
    }

    public function status()
    {
        return $this->belongsTo(ClientStatus::class, 'status_id');
    }

    public function calls()
    {
        $class = '\Modules\ClientCalls\Entities\ClientCall';
        if (class_exists($class) && \Schema::hasTable('client_calls')) {
            return $this->hasMany($class, 'client_id')
                ->orderByDesc('call_date')
                ->orderByDesc('call_time');
        }
        // Fallback relation if class or table doesn't exist to prevent crash
        return $this->hasMany(Client::class, 'id', 'id')->whereRaw('1 = 0');
    }

    public function followUps()
    {
        $class = '\Modules\FollowUps\Entities\FollowUp';
        $taskClass = '\Modules\Tasks\Entities\Task';
        if (class_exists($class) && class_exists($taskClass) && \Schema::hasTable('tasks')) {
            return $this->hasMany($class, 'related_id')
                ->where('related_type', $taskClass::RELATED_TYPE_CLIENT)
                ->orderByDesc('due_at')
                ->orderByDesc('created_at');
        }
        // Fallback relation
        return $this->hasMany(Client::class, 'id', 'id')->whereRaw('1 = 0');
    }

    public function getFormFieldValue(string $fieldId): ?array
    {
        if ($fieldId === 'password') {
            return null;
        }

        $systemFields = ClientForm::getSystemFields();

        if (isset($systemFields[$fieldId])) {
            $column = $systemFields[$fieldId]['column'];
            $label = $systemFields[$fieldId]['label'];

            $value = $column === 'status_id'
                ? ($this->status->name ?? null)
                : ($this->{$column} ?? null);
        } else {
            $form = ClientForm::active(ClientSetting::getValue('default_form_key'));
            $fieldDef = $form?->field($fieldId);

            if (!$fieldDef) {
                return null;
            }

            $label = $fieldDef['label'] ?? $fieldId;
            $value = ($this->meta ?? [])[$fieldId] ?? null;
        }

        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            return null;
        }

        if (is_array($value)) {
            $value = implode('، ', $value);
        }

        return ['id' => $fieldId, 'label' => $label, 'value' => (string) $value];
    }

    /**
     * @param string[] $fieldIds
     * @return array<int, array{id: string, label: string, value: string}>
     */
    public function getFormFieldValues(array $fieldIds): array
    {
        return collect($fieldIds)
            ->map(fn (string $id) => $this->getFormFieldValue($id))
            ->filter()
            ->values()
            ->all();
    }
}
