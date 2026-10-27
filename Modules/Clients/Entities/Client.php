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
use Modules\Clients\Entities\ClientTermsAcceptance;
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
        'vision_code',
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


    public function termsAcceptances()
    {
        return $this->hasMany(ClientTermsAcceptance::class, 'client_id');
    }

    public function hasAcceptedTerms(string $version): bool
    {
        return $this->termsAcceptances()->where('version', $version)->exists();
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

    public function laboratoryOrders()
    {
        $class = '\Modules\Booking\Entities\BookingLaboratoryOrder';
        if (class_exists($class) && \Schema::hasTable('booking_laboratory_orders')) {
            return $this->hasMany($class, 'client_id')->latest('sent_at');
        }
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
            $fType = $fieldDef['type'] ?? '';

            if ($fType === 'select-user-by-role' && ($value === null || $value === '' || (is_array($value) && empty($value)))) {
                $roleName = $fieldDef['role'] ?? null;
                $uq = $this->users();
                if (!empty($roleName)) {
                    $uq->whereHas('roles', fn($q) => $q->where('name', $roleName)->orWhere('id', $roleName));
                }
                $uIds = $uq->pluck('users.id')->toArray();
                if (!empty($uIds)) {
                    $userNames = \App\Models\User::whereIn('id', $uIds)->pluck('name')->toArray();
                    $value = !empty($userNames) ? implode('، ', $userNames) : null;
                }
            }

            if ($value !== null && $value !== '') {
                if ($fType === 'select-user-by-role') {
                    $uIds = [];
                    if (is_array($value)) {
                        $uIds = $value;
                    } elseif (is_string($value) && str_starts_with(trim($value), '[') && str_ends_with(trim($value), ']')) {
                        $decoded = json_decode($value, true);
                        $uIds = is_array($decoded) ? $decoded : [$value];
                    } elseif (!empty($value)) {
                        $uIds = [$value];
                    }
                    $uIds = array_filter(array_map('intval', (array)$uIds));
                    if (!empty($uIds)) {
                        $userNames = \App\Models\User::whereIn('id', $uIds)->pluck('name')->toArray();
                        $value = !empty($userNames) ? implode('، ', $userNames) : $value;
                    }
                } elseif ($fType === 'select-province-city') {
                    $decoded = is_array($value) ? $value : json_decode($value, true);
                    if (is_array($decoded)) {
                        $p = trim($decoded['province'] ?? '');
                        $c = trim($decoded['city'] ?? '');
                        $value = array_filter([$p, $c]) ? implode(' - ', array_filter([$p, $c])) : $value;
                    }
                } elseif (!empty($fieldDef['options_json'])) {
                    $opts = [];
                    $rawOpts = $fieldDef['options_json'];
                    if (is_array($rawOpts)) {
                        $opts = $rawOpts;
                    } elseif (is_string($rawOpts)) {
                        $decoded = json_decode($rawOpts, true);
                        if (is_array($decoded)) {
                            $opts = $decoded;
                        } else {
                            $lines = array_filter(array_map('trim', explode("\n", $rawOpts)));
                            foreach ($lines as $line) {
                                if (str_contains($line, ':')) {
                                    [$okey, $oval] = array_map('trim', explode(':', $line, 2));
                                    $opts[$okey] = $oval;
                                } else {
                                    $opts[$line] = $line;
                                }
                            }
                        }
                    }
                    if (!empty($opts)) {
                        if (is_array($value)) {
                            $value = array_map(function ($v) use ($opts) {
                                $vStr = (string)$v;
                                return $opts[$vStr] ?? $v;
                            }, $value);
                        } else {
                            $vStr = (string)$value;
                            if (isset($opts[$vStr])) {
                                $value = $opts[$vStr];
                            }
                        }
                    }
                }
            }
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

    public function rentalBookings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\Properties\Entities\PropertyRentalBooking::class, 'client_id');
    }

    public function weeklySchedule()
    {
        $class = '\Modules\Booking\Entities\BookingClientSchedule';
        if (class_exists($class) && \Illuminate\Support\Facades\Schema::hasTable('booking_client_schedules')) {
            return $this->hasOne($class, 'client_id');
        }
        return $this->hasOne(Client::class, 'id', 'id')->whereRaw('1 = 0');
    }
}

